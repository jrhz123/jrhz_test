<?php
/**
 *	[超级活动(xj_event.{modulename})] (C)2012-2099 Powered by 逍遥工作室.
 *	Version: 1.0
 *	Date: 2012-9-15 10:27
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if($_GET['formhash'] != $_G['formhash']){
		showmessage('submit_invalid');
}

$tid = intval($_GET['tid']);
if($_GET['action']=='confirm'){
	DB::query("UPDATE ".DB::table('xj_event')." SET verify=1 WHERE tid='$tid'");
	showmessage(lang('plugin/xj_event', 'hdrzcg'), "forum.php?mod=viewthread&tid=$tid", array(), array('showdialog' => true, 'locationtime' => true));
}elseif($_GET['action']=='cancel'){
	DB::query("UPDATE ".DB::table('xj_event')." SET verify=0 WHERE tid='$tid'");
	showmessage(lang('plugin/xj_event', 'hdyqxrz'), "forum.php?mod=viewthread&tid=$tid", array(), array('showdialog' => true, 'locationtime' => true));
}


?>