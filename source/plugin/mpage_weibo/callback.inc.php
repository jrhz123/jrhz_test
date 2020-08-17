<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: callback.inc.php 31649 2013-05-01 16:06:16Z mpage $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once DISCUZ_ROOT.'./source/plugin/mpage_weibo/config.php';
require_once DISCUZ_ROOT.'./source/plugin/mpage_weibo/saetv2.ex.class.php';

$o = new SaeTOAuthV2(WB_AKEY, WB_SKEY);

if (getcookie('weibo_token') && !isset($_REQUEST['code'])) {
	$token = unserialize(stripslashes(getcookie('weibo_token')));
} else {
	if(isset($_REQUEST['code'])) {
		$keys = array();
		$keys['code'] = $_REQUEST['code'];
		$keys['redirect_uri'] = WB_CALLBACK_URL;
		try {
			$token = $o->getAccessToken('code', $keys) ;
		} catch (OAuthException $e) {
		}
	}
}

if($token) {
	$c = new SaeTClientV2(WB_AKEY, WB_SKEY, $token['access_token']);
	$user_message = $c->show_user_by_id($token['uid']);
	$token['username'] = $user_message['screen_name'];
	$token['username'] = diconv($token['username'], 'UTF-8', $_G['charset']);

	dsetcookie('weibo_token', addslashes(serialize($token)), 86400);
	dsetcookie('weibojs_'.$o->client_id, http_build_query($token), 86400);

	if($_G['uid']) {
		if($bind = C::t('#mpage_weibo#mpage_weibo')->fetch($_G['uid'])) {
			C::t('#mpage_weibo#mpage_weibo')->update($_G['uid'], array(
				'sina_uid' => $token['uid'],
				'sina_username' => $token['username'],
				'token' => $token['access_token'],
				'remind_in' => $token['remind_in'],
				'expires_in' => $token['expires_in'],
				'update' => $_G['timestamp']
			));
		} else {
			C::t('#mpage_weibo#mpage_weibo')->insert(array(
				'uid' => $_G['uid'],
				'username' => $_G['username'],
				'sina_uid' => $token['uid'],
				'sina_username' => $token['username'],
				'token' => $token['access_token'],
				'remind_in' => $token['remind_in'],
				'expires_in' => $token['expires_in'],
				'thread' => 1,
				'reply' => 1,
				'follow' => 1,
				'blog' => 1,
				'doing' => 1,
				'share' => 1,
				'article' => 1,
				'dateline' => $_G['timestamp'],
				'update' => $_G['timestamp']
			));
		}

		showmessage('mpage_weibo:bind_succeed', 'home.php?mod=spacecp&ac=plugin&id=mpage_weibo:bind');
	} else {
		$bind = C::t('#mpage_weibo#mpage_weibo')->fetch_by_sina_uid($token['uid']);
		$member = getuserbyuid($bind['uid'], 1);

		if($bind && $member) {
			if(isset($member['_inarchive'])) {
				C::t('common_member_archive')->move_to_master($member['uid']);
			}

			require_once libfile('function/member');
			$cookietime = 1296000;
			setloginstatus($member, $cookietime);

			loadcache('usergroups');
			$usergroups = $_G['cache']['usergroups'][$_G['groupid']]['grouptitle'];
			$param = array('username' => $_G['member']['username'], 'usergroup' => $_G['group']['grouptitle']);

			C::t('common_member_status')->update($bind['uid'], array('lastip'=>$_G['clientip'], 'lastvisit'=>TIMESTAMP, 'lastactivity' => TIMESTAMP));
			$ucsynlogin = '';
			if($_G['setting']['allowsynlogin']) {
				loaducenter();
				$ucsynlogin = uc_user_synlogin($_G['uid']);
			}

			showmessage('login_succeed', dreferer(), $param, array('extrajs' => $ucsynlogin));
		} else {
			// $dreferer = rawurlencode(dreferer());
			// showmessage('mpage_weibo:complete_or_bind', 'member.php?mod='.$_G['setting']['regname'].'&referer='.$dreferer);
			loaducenter();
			if ($_POST['username']) {
				$username = $_POST['username'];
			} else {
				$username = $token['username'];
			}
			$check_status = uc_user_checkname($username);
			if ($check_status > 0) {
				$password = md5(random(10));
				$email = 'weibo_'.strtolower(random(10)).'@null.null';
				$groupid = 63;
				$uid = uc_user_register(addslashes($username), $password, $email, '', '', $_G['clientip']);
				if($uid <= 0) {
					if($uid == -1) {
						showmessage('profile_username_illegal');
					} elseif($uid == -2) {
						showmessage('profile_username_protect');
					} elseif($uid == -3) {
						showmessage('profile_username_duplicate');
					} elseif($uid == -4) {
						showmessage('profile_email_illegal');
					} elseif($uid == -5) {
						showmessage('profile_email_domain_illegal');
					} elseif($uid == -6) {
						showmessage('profile_email_duplicate');
					} else {
						showmessage('undefined_action');
					}
				}
				$init_arr = array('credits' => explode(',', $_G['setting']['initcredits']));
				C::t('common_member')->insert($uid, $username, $password, $email, $_G['clientip'], $groupid, $init_arr);

				if($_G['setting']['regctrl'] || $_G['setting']['regfloodctrl']) {
					C::t('common_regip')->delete_by_dateline($_G['timestamp']-($_G['setting']['regctrl'] > 72 ? $_G['setting']['regctrl'] : 72)*3600);
					if($_G['setting']['regctrl']) {
						C::t('common_regip')->insert(array('ip' => $_G['clientip'], 'count' => -1, 'dateline' => $_G['timestamp']));
					}
				}

				if($_G['setting']['regverify'] == 2) {
					C::t('common_member_validate')->insert(array(
						'uid' => $uid,
						'submitdate' => $_G['timestamp'],
						'moddate' => 0,
						'admin' => '',
						'submittimes' => 1,
						'status' => 0,
						'message' => '',
						'remark' => '',
					), false, true);
					manage_addnotify('verifyuser');
				}

				C::t('#mpage_weibo#mpage_weibo')->insert(array(
					'uid' => $uid,
					'username' => $username,
					'sina_uid' => $token['uid'],
					'sina_username' => $token['username'],
					'token' => $token['access_token'],
					'remind_in' => $token['remind_in'],
					'expires_in' => $token['expires_in'],
					'thread' => 1,
					'reply' => 1,
					'follow' => 1,
					'blog' => 1,
					'doing' => 1,
					'share' => 1,
					'article' => 1,
					'dateline' => $_G['timestamp'],
					'update' => $_G['timestamp']
				));

				C::t('common_member')->update($uid, array('emailstatus' => '1'));

				/*require DISCUZ_ROOT . './source/class/class_core.php';*/
				// $type = 'system';
				// $note = '感谢您的注册以及对东江论坛的支持。当前登录账号的初始密码为【'.$password.'】，请及时修改以免遗失。';
				// notification_add($uid, $type, $note, $notevars = array(), $system = 1, $category = 3);

				echo uc_user_synlogin($uid);
				showmessage('已成功通过 微博 快速登录。','forum.php');

			} else {

				switch ($check_status) {
					case  '-1':
						$error_message = "用户名不可使用空格、标点符号等字符，请使用汉字、数字或字母组合如“路人123”进行注册。";
						break;
					case '-2':
						$error_message = "用户名不符合论坛注册规则。";
						break;
					case '-3':
						$error_message = "您的微博昵称已经在本站注册，请填写一个新的用户名。";
						break;
					case '-4':
						//showmessage('profile_email_illegal');
						$error_message = "您的微博昵称已经在本站注册，请填写一个新的用户名。";
						break;
					case '-5':
						//showmessage('profile_email_domain_illegal');
						$error_message = "您的微博昵称已经在本站注册，请填写一个新的用户名。";
						break;
					case '-6':
						//showmessage('profile_email_duplicate');
						$error_message = "您的微博昵称已经在本站注册，请填写一个新的用户名。";
						break;
					default:
						//showmessage('undefined_action');
						$error_message = "您的微博昵称已经在本站注册，请填写一个新的用户名。";
						break;
				}
				include template('mpage_weibo:duplicate');
			}
		}
	}
} else {
	echo lang('plugin/mpage_weibo', 'reauth_fail');
}


//Enicn_d added 2016-01-26 新浪微博用户注册

?>
