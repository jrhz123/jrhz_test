<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: login.inc.php 35024 2014-10-14 07:43:43Z nemohou $
 */
if (!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once DISCUZ_ROOT . './source/plugin/wx_mobile/wx_mobile.lib.class.php';
require_once DISCUZ_ROOT . './source/plugin/wx_mobile/avatar.lib.class.php';

$ac = !empty($_GET['ac']) ? $_GET['ac'] : 'login';

if($ac == 'login') {
	$request_url = $_G['cache']['plugin']['wx_mobile']['wx_request'];
	$param['appid'] = $_G['cache']['plugin']['wx_mobile']['wx_app_key'];
	$param['redirect_uri'] =  $_G['cache']['plugin']['wx_mobile']['wx_redirect'];
	$param['state'] =  $_G['cache']['plugin']['wx_mobile']['wx_state'];
	$param['response_type'] = $_G['cache']['plugin']['wx_mobile']['wx_response_type'];
	$param['scope'] = $_G['cache']['plugin']['wx_mobile']['wx_scope'];
	$url = wx_mobile::qrconnectUrl($request_url,$param);
	dheader('location: '.$url);
} elseif($ac == 'callback') {
	if(!$_GET['code']) {
		showmessage('wx_mobile:wx_mobile_member_auth_fail');
	}

	$oauth_url = $_G['cache']['plugin']['wx_mobile']['wx_oauth_url'];
	$param['appid'] = $_G['cache']['plugin']['wx_mobile']['wx_app_key'];
	$param['code'] = $_GET['code'];
	$param['secret'] = $_G['cache']['plugin']['wx_mobile']['wx_app_secret'];
	$param['grant_type'] = $_G['cache']['plugin']['wx_mobile']['wx_grant_type'];

	$oath = wx_mobile::getOATH($oauth_url,$param);
	if($oath->access_token){
		$userinfo_url = $_G['cache']['plugin']['wx_mobile']['wx_userinfo'];
		$param['access_token'] = $oath->access_token;
		$param['openid'] = $oath->openid;
		$param['lang'] = 'zh_CN';
		$userinfo = wx_mobile::getOATH($userinfo_url,$param);

		$openId = $userinfo->openid;
		$info = C::t('#wx_mobile#wx_mobile')->fetch_fields_by_openid($openId);
		require_once libfile('function/member');

		if($info && ($member=getuserbyuid($info['uid'], 1))){
			setloginstatus($member, 1296000);
			// dheader('location: '.($_GET['referer'] ? $_GET['referer'] : $_G['siteurl']));\
			if ($_GET['referer']) {
				$uri = explode('&',$_SERVER['REQUEST_URI']);
				unset($uri[0]);
				$new_url = str_replace('referer=' ,'' ,implode('&', $uri));
				$new_url = urldecode($new_url);
			} else {
				$new_url = $_G['siteurl'];
			}
			dheader('location: '.$new_url); //Enicn_d modified 2016-01-07
		} else {
			loaducenter();
			$defaultusername = wx_mobile::clear($userinfo->nickname);
			$groupid = $_G['cache']['plugin']['wx_mobile']['wx_group'];

			$sex = $userinfo->sex;
			$city = wx_mobile::clear($userinfo->city);
			$province = wx_mobile::clear($userinfo->province);
			$unionid = $userinfo->unionid;

				//$data['uid'] = $uid;
				//$data['avatar'] = $userinfo->headimgurl;
				//avatar::transfer($data);

			$check_status = uc_user_checkname($defaultusername);
			if ($check_status > 0) {
				$uid = wx_mobile::register($defaultusername, 1, $groupid);
				if(!$uid) {
					showmessage('wx_mobile:wx_mobile_member_login_fail');
				}
				C::t('common_member_profile')->update($uid, array('gender'=> $sex, 'resideprovince'=> $province, 'residecity'=> $city));
				C::t('#wx_mobile#wx_mobile')->insert(array('uid' => $uid, 'openid' => $openId, 'unionid' => $unionid, 'status' => 0, 'headimgurl' => $userinfo->headimgurl, 'nickname' => wx_mobile::clear($userinfo->nickname),'dateline' => $_G['timestamp']), false, true);

				// dheader('location: '.($_GET['referer'] ? $_GET['referer'] : $_G['siteurl']));
				if ($_GET['referer']) {
					$uri = explode('&',$_SERVER['REQUEST_URI']);
					unset($uri[0]);
					$new_url = str_replace('referer=' ,'' ,implode('&', $uri));
					$new_url = urldecode($new_url);
				} else {
					$new_url = $_G['siteurl'];
				}
				dheader('location: '.$new_url); //Enicn_d modified 2016-01-07
			} else {

				switch ($check_status) {
					case  '-1':
						$error_message = "用户名不可使用空格、标点符号等字符，请使用汉字、数字或字母组合如“路人123”进行注册。";
						break;
					case '-2':
						$error_message = "用户名不符合论坛注册规则。";
						break;
					case '-3':
						$error_message = "您的QQ昵称已经在本站注册，请填写一个新的用户名。";
						break;
					case '-4':
						//showmessage('profile_email_illegal');
						$error_message = "您的QQ昵称已经在本站注册，请填写一个新的用户名。";
						break;
					case '-5':
						//showmessage('profile_email_domain_illegal');
						$error_message = "您的QQ昵称已经在本站注册，请填写一个新的用户名。";
						break;
					case '-6':
						//showmessage('profile_email_duplicate');
						$error_message = "您的QQ昵称已经在本站注册，请填写一个新的用户名。";
						break;
					default:
						//showmessage('undefined_action');
						$error_message = "您的QQ昵称已经在本站注册，请填写一个新的用户名。";
						break;
				}
				include template('wx_mobile:duplicate');

			}
		}
	} else {
		showmessage('wx_mobile:wx_mobile_member_access_token_fail');
	}
} elseif($ac == 'bind') {
	$request_url = $_G['cache']['plugin']['wx_mobile']['wx_request'];
	$param['appid'] = $_G['cache']['plugin']['wx_mobile']['wx_app_key'];
	$param['redirect_uri'] =  $_G['cache']['plugin']['wx_mobile']['wx_redirect_bind'];
	$param['state'] =  $_G['cache']['plugin']['wx_mobile']['wx_state'];
	$param['response_type'] = $_G['cache']['plugin']['wx_mobile']['wx_response_type'];
	$param['scope'] = $_G['cache']['plugin']['wx_mobile']['wx_scope'];
	$url = wx_mobile::qrconnectUrl($request_url,$param);
	dheader('location: '.$url);
}
