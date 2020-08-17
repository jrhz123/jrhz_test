<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: install.php 34718 2014-07-14 08:56:39Z nemohou $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(!$_G['uid']) {
	showmessage('not_loggedin', NULL, array(), array('login' => 1));
}
require_once DISCUZ_ROOT.'./source/plugin/wechat/wechat.lib.class.php';

$tid = intval($_GET['tid']);
$items = DB::fetch(DB::query("SELECT * FROM ".DB::table('xj_event')." WHERE tid = '$tid'"));
$thread =  DB::fetch(DB::query("SELECT subject FROM ".DB::table('forum_thread')." WHERE tid = '$tid'"));
$setting = unserialize($items['setting']);
$userfield = unserialize($items['userfield']);
$selectuserfield = unserialize($items['userfield']);
if($selectuserfield) {
	if($selectuserfield) {
		$htmls = $settings = array();
		require_once libfile('function/profile');
		foreach($selectuserfield as $fieldid) {
			if(empty($ufielddata['userfield'])) {
				$memberprofile = C::t('common_member_profile')->fetch($_G['uid']);
				foreach($selectuserfield as $val) {
					$ufielddata['userfield'][$val] = $memberprofile[$val];
				}
				unset($memberprofile);
			}
			$html = profile_setting($fieldid, $ufielddata['userfield'], false, true);
			if($html) {
				$settings[$fieldid] = $_G['cache']['profilesetting'][$fieldid];
				$htmls[$fieldid] = $html;
			}
		}
	}
} else {
	$selectuserfield = '';
}
$applynumber = array();
for($i=1;$i<=$items['event_number_max'];$i++){
	$applynumber[] = $i;
}

$bmurl = WeChatHook::getPluginUrl('xj_event:wsq_join_save', array('tid' => $tid));


include template('xj_event:wsq_join');

?>
