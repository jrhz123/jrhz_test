<?php
/**
 *	[超级活动(xj_event.{modulename})] (C)2012-2099 Powered by 逍遥工作室.
 *	Version: 1.0
 *	Date: 2012-9-15 10:27
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$tid = intval($_GET['tid']);

//权限验证
$thread = DB::fetch_first("SELECT A.authorid,B.setting FROM ".DB::table('forum_thread')." A,".DB::table('xj_event')." B WHERE A.tid='$tid' and A.tid = B.tid");
$setting = unserialize($thread['setting']);
//判断是不是管理团队
$event_admin = false;
if(in_array($_G['username'],$setting['event_admin'])){
	$event_admin = true;
}
if($_G['groupid']>1 && $_G['uid']!=$thread['authorid'] && !$event_admin){
	showmessage('quickclear_noperm');
}




if($_GET['action']=='save'){
	if($_GET['formhash'] != $_G['formhash']){
		showmessage('submit_invalid');
	}

	//投票
	$vote['openvote'] = intval($_GET['openvote']); 
	$vote['votestarttime'] = strtotime($_GET['votestarttime']); 
	$vote['voteendtime'] = strtotime($_GET['voteendtime']); 
	$vote['votecheckhour'] = intval($_GET['votecheckhour']);
	$vote['votechecknumber'] = intval($_GET['votechecknumber']);
	
	
	$vote['ipcheck'] = intval($_GET['ipcheck']);
	$vote['usercheck'] = intval($_GET['usercheck']);
	$setting['vote'] = $vote;
	//报名
	$setting['noverify'] = intval($_GET['noverify']);
	$setting['canceljoin'] = intval($_GET['canceljoin']);
	//邀请报名
	$setting['invitation']['open'] = intval($_GET['invitationcodeopen']);
	$setting['invitation']['code'] = intval($_GET['invitationcode']);
	//活动签到
	if(file_exists(DISCUZ_ROOT.'./source/plugin/xj_event/module/signed/wsq_signed.php')) {
		$setting['signed']['open'] = intval($_GET['signed_open']);
	}
	
	
	$setting_str = serialize($setting);
	DB::query("UPDATE ".DB::table('xj_event')." set setting = '$setting_str' WHERE tid = '$tid'");
	showmessage(lang('plugin/xj_event', 'bccg'), 'forum.php?mod=viewthread&tid='.$tid);
}


$items = DB::fetch_first("SELECT setting FROM ".DB::table('xj_event')." WHERE tid='$tid'");
$setting = unserialize($items['setting']);



if($setting['vote']['votestarttime'] and $setting['vote']['voteendtime']){
	$votestarttime = dgmdate($setting['vote']['votestarttime'],'dt');
	$voteendtime = dgmdate($setting['vote']['voteendtime'],'dt');
	$votecheckhour = $setting['vote']['votecheckhour'];
	$votechecknumber = $setting['vote']['votechecknumber'];
}else{
	$votestarttime =dgmdate($_G['timestamp'],'dt');
	$voteendtime = dgmdate($_G['timestamp'],'dt');
	$votecheckhour = 1;
	$votechecknumber = 1;
}


//活动签到
if(file_exists(DISCUZ_ROOT.'./source/plugin/xj_event/module/signed/wsq_signed.php')) {
	$signed_enable = true; 
}


include template('xj_event:event_setting');

?>