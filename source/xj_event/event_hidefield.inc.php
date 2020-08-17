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
$items = DB::fetch_first("SELECT setting FROM ".DB::table('xj_event')." WHERE tid='$tid'");
$setting = unserialize($items['setting']);
$fieldname = $_GET['fieldname'];
if($_GET['action']=='show'){
	$setting['hidefield'][$fieldname] = 0;
}
if($_GET['action']=='hide'){
	$setting['hidefield'][$fieldname] = 1;
}

$setting = serialize($setting);
DB::query("UPDATE ".DB::table('xj_event')." SET setting = '$setting' WHERE tid='$tid'");

?>