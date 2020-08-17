<?php
/**
 *	[超级活动(xj_event.{modulename})] (C)2012-2099 Powered by 逍遥工作室.
 *	Version: 1.0
 *	Date: 2012-9-15 10:27
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}


$upid = intval($_GET['upid']);
if($upid){
	$city = DB::fetch_all("SELECT * FROM ".DB::table('common_district')." WHERE upid = $upid");
}else{
	$province = DB::fetch_all("SELECT * FROM ".DB::table('common_district')." WHERE level = 1 order by id");
}

include template('xj_event:city');
?>