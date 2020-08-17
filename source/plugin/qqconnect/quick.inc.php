<?php

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

loaducenter();

/**
Enicn_d modified 2015-04-15 added quick register of QQ login
*/
if ($_POST['username']) {
	$username = $_POST['username'];
} else {
	$username = $_G['cookie']['connect_qq_nick'];
}
$check_status = uc_user_checkname($username);
if ($check_status > 0) {

	$password = md5(random(10));
	$email = 'qq_'.strtolower(random(10)).'@null.null';
	$groupid = 18;
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

	$conispublishfeed = 0;
	$conispublisht = 0;
	$user_auth_fields = 1;

	$conopenid = authcode($_G['cookie']['con_auth_hash']);
	$connectGuest = C::t('#qqconnect#common_connect_guest')->fetch($conopenid);
	$conuin = $connectGuest['conuin'];
	$conuinsecret = $connectGuest['conuinsecret'];
	$conuintoken = $connectGuest['conuintoken'];

	C::t('#qqconnect#common_member_connect')->insert(
		array(
			'uid' => $uid, 
			'conuin' => $conuin, 
			'conuinsecret' => $conuinsecret, 
			'conuintoken' => $conuintoken, 
			'conopenid' => $conopenid, 
			'conispublishfeed' => $conispublishfeed, 
			'conispublisht' => $conispublisht, 
			'conisregister' => '0', 
			'conisqzoneavatar' => '0', 
			'conisfeed' => $user_auth_fields, 
			'conisqqshow' => '0'
		), false, true
	);
	C::t('common_member')->update($uid, array('conisbind' => '1', 'emailstatus' => '1'));
	C::t('#qqconnect#connect_memberbindlog')->insert(
		array(
			'uid' => $uid, 
			'uin' => $conopenid, 
			'type' => '1', 
			'dateline' => $_G['timestamp']
		)
	);
	C::t('#qqconnect#common_connect_guest')->delete($conopenid);

	/*require DISCUZ_ROOT . './source/class/class_core.php';*/
	$type = 'system';
	$note = '感谢您的注册以及对东江论坛的支持。当前登录账号的初始密码为【'.$password.'】，请及时修改以免遗失。';
	notification_add($uid, $type, $note, $notevars = array(), $system = 1, $category = 3);

	echo uc_user_synlogin($uid);
	showmessage('已成功通过 QQ 快速登录。','forum.php');

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

	include template('qqconnect:duplicate');

}
/**

*/
?>