<?php
/**
 *	[�����(xj_event.{modulename})] (C)2012-2099 Powered by ��ң������.
 *	Version: 1.0
 *	Date: 2012-9-15 10:27
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$tid = intval($_GET['tid']);

//Ȩ����֤
$thread = DB::fetch_first("SELECT A.authorid,B.setting FROM ".DB::table('forum_thread')." A,".DB::table('xj_event')." B WHERE A.tid='$tid' and A.tid = B.tid");
$setting = unserialize($thread['setting']);
//�ж��ǲ��ǹ����Ŷ�
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

	//ͶƱ
	$vote['openvote'] = intval($_GET['openvote']); 
	$vote['votestarttime'] = strtotime($_GET['votestarttime']); 
	$vote['voteendtime'] = strtotime($_GET['voteendtime']); 
	$vote['votecheckhour'] = intval($_GET['votecheckhour']);
	$vote['votechecknumber'] = intval($_GET['votechecknumber']);
	
	
	$vote['ipcheck'] = intval($_GET['ipcheck']);
	$vote['usercheck'] = intval($_GET['usercheck']);
	$setting['vote'] = $vote;
	//����
	$setting['noverify'] = intval($_GET['noverify']);
	$setting['canceljoin'] = intval($_GET['canceljoin']);
	//���뱨��
	$setting['invitation']['open'] = intval($_GET['invitationcodeopen']);
	$setting['invitation']['code'] = intval($_GET['invitationcode']);
	//�ǩ��
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


//�ǩ��
if(file_exists(DISCUZ_ROOT.'./source/plugin/xj_event/module/signed/wsq_signed.php')) {
	$signed_enable = true; 
}


include template('xj_event:event_setting');

?>