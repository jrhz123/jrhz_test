<?php
/**
 *      HuiZhou Group Media (C)2008-2015
 *      $Id: bind.inc.php 2015-08-03 16:48 Stellar $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once DISCUZ_ROOT . './source/plugin/wx_mobile/wx_mobile.lib.class.php';

$ac = $_GET['ac'];

if(!$_G['uid']) {
	showmessage('not_loggedin', NULL, array(), array('login' => 1));
} else{
	if(submitcheck("unbindsubmit")) {
		C::t('#wx_mobile#wx_mobile')->user_delete($_G['uid']);
		showmessage('wx_mobile:unbind_succeed', 'home.php?mod=spacecp&ac=plugin&id=wx_mobile:bind');
	}
	if(!empty($ac) && $ac == 'bind'){
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

			$sex = $userinfo->sex;
			$city = wx_mobile::clear($userinfo->city);
			$province = wx_mobile::clear($userinfo->province);
			$unionid = $userinfo->unionid;

			if($info && ($info['uid'] != $_G['uid'])) {
				C::t('#wx_mobile#wx_mobile')->user_delete($info['uid']);
				C::t('common_member_profile')->update($_G['uid'], array('gender'=> $sex, 'resideprovince'=> $province, 'residecity'=> $city));
				C::t('#wx_mobile#wx_mobile')->insert(array('uid' => $_G['uid'], 'openid' => $openId, 'unionid' => $unionid, 'status' => 0, 'headimgurl' => $userinfo->headimgurl, 'nickname' => wx_mobile::clear($userinfo->nickname),'dateline' => $_G['timestamp']), false, true);
				showmessage('wx_mobile:sync_succeed', 'home.php?mod=spacecp&ac=plugin&id=wx_mobile:bind');
			} else {
				C::t('common_member_profile')->update($_G['uid'], array('gender'=> $sex, 'resideprovince'=> $province, 'residecity'=> $city));
				C::t('#wx_mobile#wx_mobile')->insert(array('uid' => $_G['uid'], 'openid' => $openId, 'unionid' => $unionid, 'status' => 0, 'headimgurl' => $userinfo->headimgurl, 'nickname' => wx_mobile::clear($userinfo->nickname),'dateline' => $_G['timestamp']), false, true);
				showmessage('wx_mobile:sync_succeed', 'home.php?mod=spacecp&ac=plugin&id=wx_mobile:bind');
			}

		} else {
			showmessage('wx_mobile:wx_mobile_member_access_token_fail');
		}
		
	}
	$bind = C::t('#wx_mobile#wx_mobile')->fetch_fields_by_uid($_G['uid']);
	$bind['dateline'] = dgmdate($bind['dateline']);
}


?>