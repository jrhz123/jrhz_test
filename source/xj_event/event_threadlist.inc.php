<?php
/**
 *	[超级活动(xj_event.{modulename})] (C)2012-2099 Powered by 逍遥工作室.
 *	Version: 1.0
 *	Date: 2012-9-15 10:27
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$eid = intval($_GET['eid']);
$fid = intval($_GET['fid']);
$perpage = 10; //每页数


$eventinfo = DB::fetch_first("SELECT tid,setting FROM ".DB::table('xj_event')." WHERE eid='$eid'");
$tid = $eventinfo['tid'];
$setting = unserialize($eventinfo['setting']);
$nowtime = time();


$myapply = DB::result_first("SELECT count(*) FROM ".DB::table('xj_eventapply')." WHERE eid='$eid' AND uid=$_G[uid] AND verify=1");
$applynumber = DB::result_first("SELECT SUM(applynumber) FROM ".DB::table('xj_eventapply')." WHERE eid='$eid'");
$listcount_thread = DB::result_first("SELECT COUNT(*) FROM ".DB::table('xj_eventthread')." A,".DB::table('forum_thread')." B WHERE A.tid = B.tid and A.eid = '$eid' AND A.fid != 0 AND B.displayorder>=0");
$listcount_blog = DB::result_first("SELECT COUNT(*) FROM ".DB::table('xj_eventthread')." A,".DB::table('home_blog')." B WHERE A.tid = B.blogid and A.eid = '$eid' AND A.fid = 0 AND B.status>=0");
$listcount = $listcount_thread + $listcount_blog;
$page = $_GET['page']?$_GET['page']:1;
if(@ceil($listcount/$perpage) < $page) {
	$page = 1;
}
$start_limit = ($page - 1) * $perpage;
$multipage = multi($listcount,$perpage,$page,"plugin.php?id=xj_event:event_threadlist&eid=$eid&order=".$_GET['order'],0,10,false,true);
$multipage = str_replace('class="pg"','class="jlpg"',$multipage);

//Enicn_d modified 2015-05-04 17:48 added blog support
$sqlorder = 'B.lastpost DESC';
$sqlorder_blog = 'B.dateline DESC';
if($_GET['order']=='tp'){
	$sqlorder = 'A.votes DESC';
	$sqlorder_blog = 'A.votes DESC';
}elseif($_GET['order']=='dj'){
	$sqlorder = 'B.views DESC';
	$sqlorder_blog = 'B.viewnum DESC';
}elseif($_GET['order']=='hf'){
	$sqlorder = 'B.replies DESC';
	$sqlorder_blog = 'B.replynum DESC';
}elseif($_GET['order']=='sj'){
	$sqlorder = 'B.dateline DESC';
	$sqlorder_blog = 'B.dateline DESC';
}

$threadlist = array();
$thread_left = $listcount_thread-$start_limit;
if ($thread_left > 0) {
	$query = DB::query("SELECT * FROM ".DB::table('xj_eventthread')." A,".DB::table('forum_thread')." B WHERE A.tid = B.tid and A.eid = '$eid' AND A.fid != 0 AND B.displayorder>=0 ORDER BY ".$sqlorder." LIMIT $start_limit,$perpage");
	while($value = DB::fetch($query)){
		$value['avatar'] = '<img src="'.avatar($value[authorid], 'middle', true, false, true).'?random='.random(2).'" onerror="this.onerror=null;this.src=\''.$_G['setting']['ucenterurl'].'/images/noavatar_middle.gif\'" />';
		$value['strdateline'] = dgmdate($value['dateline'],'dt');
		$threadlist[] = $value;
	}
	if ($thread_left<$perpage) {
		$perpage = $perpage-($listcount_thread-$start_limit);
		$read_blog = TRUE;
	}
} else {
	$read_blog = TRUE;
}

if ($read_blog) {
	$start_limit -= $listcount_thread;
	$start_limit = $start_limit>=0 ? $start_limit : 0;
	$query = DB::query("SELECT * FROM ".DB::table('xj_eventthread')." A,".DB::table('home_blog')." B WHERE A.tid = B.blogid and A.eid = '$eid' AND A.fid = 0 AND B.status>=0 ORDER BY ".$sqlorder_blog." LIMIT $start_limit,$perpage");
	while($value = DB::fetch($query)){
		$value['avatar'] = '<img src="'.avatar($value[uid], 'middle', true, false, true).'?random='.random(2).'" onerror="this.onerror=null;this.src=\''.$_G['setting']['ucenterurl'].'/images/noavatar_middle.gif\'" />';
		$value['strdateline'] = dgmdate($value['dateline'],'dt');
		$value['views'] = $value['viewnum'];
		$value['replies'] = $value['replynum'];
		$value['authorid'] = $value['uid'];
		$value['author'] = $value['username'];
		$threadlist[] = $value;
	}
}


include template('xj_event:threadlist');

?>