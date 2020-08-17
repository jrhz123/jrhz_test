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

loaducenter();
$username = $_POST['username'];
$groupid = $_G['cache']['plugin']['wx_mobile']['wx_group'];
$defaultusername = $_POST['defaultusername'];
$sex = $_POST['sex'];
$city = $_POST['city'];
$province = $_POST['province'];
$openId = $_POST['openId'];
$unionid = $_POST['unionid'];
$headimgurl = $_POST['headimgurl'];

$check_status = uc_user_checkname($username);
if ($check_status > 0) {
	require_once libfile('function/member');
	$uid = wx_mobile::register($username, 0, $groupid);

	if(!$uid) {
		showmessage('wx_mobile:wx_mobile_member_login_fail');
	}

	C::t('common_member_profile')->update($uid, array('gender'=> $sex, 'resideprovince'=> $province, 'residecity'=> $city));
	C::t('#wx_mobile#wx_mobile')->insert(array('uid' => $uid, 'openid' => $openId, 'unionid' => $unionid, 'status' => 0, 'headimgurl' => $headimgurl, 'nickname' => $defaultusername,'dateline' => $_G['timestamp']), false, true);

	dheader('location: '.($_GET['referer'] ? $_GET['referer'] : $_G['siteurl']));

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

?>