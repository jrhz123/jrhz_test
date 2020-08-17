<?php
/**
 *	[超级活动(xj_event.{modulename})] (C)2012-2099 Powered by 逍遥工作室.
 *	Version: 1.0
 *	Date: 2012-9-15 10:27
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
if(!$_G['uid']) {
	showmessage('not_loggedin', NULL, array(), array('login' => 1));
}
$tid = intval($_GET['tid']);

$items = DB::fetch(DB::query("SELECT * FROM ".DB::table('xj_event')." WHERE tid = '$tid'"));
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

include template('xj_event:event_join');

?>