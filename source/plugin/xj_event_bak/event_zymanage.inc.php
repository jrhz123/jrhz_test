<?php
/**
 *	[超级活动(xj_event.{modulename})] (C)2012-2099 Powered by 逍遥工作室.
 *	Version: 1.0
 *	Date: 2012-9-15 10:27
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
define ("CONTENTSEPARATE", $_G['style']['contentseparate']);
define ("COMMONBG", $_G['style']['commonbg']);
define ("MIDTEXT", $_G['style']['midtext']);
define ("LIGHTTEXT", $_G['style']['lighttext']);
define ("COMMONBORDER", $_G['style']['commonborder']);
define ("SPECIALBORDER", $_G['style']['specialborder']);
define ("IMGDIR", $_G['style']['imgdir']);
$eid = intval($_GET['eid']);
$type = $_GET['type'] ? $_GET['type'] : 'thread';
$event = DB::fetch_first("SELECT A.tid,A.setting,B.authorid FROM ".DB::table('xj_event')." A,".DB::table('forum_thread')." B WHERE A.tid=B.tid AND A.eid=$eid");
$event['setting'] = unserialize($event['setting']);
//判断是不是管理团队
$event_admin = false;
if(in_array($_G['username'],$event['setting']['event_admin'])){
	$event_admin = true;
}
if($_G['groupid']!=1 && $_G['uid'] != $event['authorid'] && !$event_admin){
	exit('Access Denied');
}
$sqlstr = "";
if($event['setting']['eventzy_enable'] && $event['setting']['eventzy_fid']>0 && $type=='thread'){
	$sqlstr = " and fid=".$event['setting']['eventzy_fid'];
}
if($_GET['subjectkey']){
	$sqlstr = " and subject like '%".addslashes($_GET['subjectkey'])."%'";
}


$perpage = 15; //每页数
$page = $_GET['page']?$_GET['page']:1;
if ($type=='thread') {
	$listcount = DB::result_first("SELECT count(*) FROM ".DB::table('forum_thread')." WHERE displayorder>=0 $sqlstr");
} else {
	$listcount = DB::result_first("SELECT count(*) FROM ".DB::table('home_blog')." WHERE status>=0 $sqlstr");
}
if(@ceil($listcount/$perpage) < $page) {
	$page = 1;
}
$start_limit = ($page - 1) * $perpage;
$multipage = multi($listcount,$perpage,$page,"plugin.php?id=xj_event:event_zymanage&eid=$eid&subjectkey=".addslashes($_GET['subjectkey'])."&type=".$type,0,10,false,true);

$threadlist = array();
//Enicn_d modified 2015-05-04 added blog support
if ($type=='thread') {
	$query = DB::query("SELECT * FROM ".DB::table('forum_thread')." WHERE displayorder>=0 $sqlstr ORDER BY dateline DESC LIMIT $start_limit,$perpage");
	while($value = DB::fetch($query)){
		$value['gl'] = DB::result_first("SELECT COUNT(*) FROM ".DB::table('xj_eventthread')." WHERE eid='$eid' and tid=".$value['tid']);
		$threadlist[] = $value;
	}
} else {
	$query = DB::query("SELECT * FROM ".DB::table('home_blog')."  WHERE status>=0 $sqlstr ORDER BY dateline DESC LIMIT $start_limit,$perpage");
	while($value = DB::fetch($query)){
		$value['gl'] = DB::result_first("SELECT COUNT(*) FROM ".DB::table('xj_eventthread')." WHERE eid='$eid' and tid=".$value['blogid']." and fid=0");
		$threadlist[] = $value;
	}
}


include template('xj_event:event_zymanage');
?>