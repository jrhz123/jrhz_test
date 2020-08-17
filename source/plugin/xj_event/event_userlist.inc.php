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
$perpage = $_G['cache']['plugin']['xj_event']['event_showapplynum']; //每页数

$allren = DB::result_first("SELECT SUM(applynumber) FROM ".DB::table('xj_eventapply')." WHERE tid='$tid'");
$passren = DB::result_first("SELECT SUM(applynumber) FROM ".DB::table('xj_eventapply')." WHERE tid='$tid' AND verify=1");
$nopassren = DB::result_first("SELECT SUM(applynumber) FROM ".DB::table('xj_eventapply')." WHERE tid='$tid' AND verify=0");

$all = DB::result_first("SELECT COUNT(*) FROM ".DB::table('xj_eventapply')." WHERE tid='$tid'");
$pass = DB::result_first("SELECT COUNT(*) FROM ".DB::table('xj_eventapply')." WHERE tid='$tid' AND verify=1");
$nopass = DB::result_first("SELECT COUNT(*) FROM ".DB::table('xj_eventapply')." WHERE tid='$tid' AND verify=0");
$eventinfo = DB::fetch_first("SELECT * FROM ".DB::table('xj_event')." WHERE tid='$tid'");
$eventinfo['setting'] = unserialize($eventinfo['setting']);

if($_GET['action']=='nopass'){   //没通过审核的报名者

	$listcount = DB::result_first("SELECT COUNT(*) FROM ".DB::table('xj_eventapply')." WHERE tid='$tid' and verify=0");
	$page = $_GET['page']?$_GET['page']:1;
	if(@ceil($listcount/$perpage) < $page) {
		$page = 1;
	}
	$start_limit = ($page - 1) * $perpage;
	$multipage = multi($listcount,$perpage,$page,"plugin.php?id=xj_event:event_userlist&tid=$tid",0,10,false,true);
	//$multipage = str_replace('class="pg"','class="jlpg"',$multipage);
	
	$query = DB::query("SELECT A.*,B.username,C.* FROM ".DB::table('xj_eventapply')." A,".DB::table('common_member')." B,".DB::table('xj_event_member_info')." C WHERE A.uid = B.uid AND A.uid=C.uid and A.verify=0 AND A.tid = '$tid' ORDER BY A.dateline DESC LIMIT $start_limit,$perpage");
	$joinlist = array();
	while($value = DB::fetch($query)){
		//$value['avatar'] = avatar($value['uid'], 'middle');
		$value['avatar'] = '<img src="'.avatar($value[uid], 'middle', true, false, true).'?random='.random(2).'" onerror="this.onerror=null;this.src=\''.$_G['setting']['ucenterurl'].'/images/noavatar_middle.gif\'" width="26" height="26" align="absmiddle" />';
		$joinlist[] = $value;
	}

}elseif($_GET['action']=='noattend'){   //报名通过后没有参加活动的

	$listcount = DB::result_first("SELECT COUNT(*) FROM ".DB::table('xj_eventapply')." WHERE tid='$tid' and pj=4");
	$page = $_GET['page']?$_GET['page']:1;
	if(@ceil($listcount/$perpage) < $page) {
		$page = 1;
	}
	$start_limit = ($page - 1) * $perpage;
	$multipage = multi($listcount,$perpage,$page,"plugin.php?id=xj_event:event_userlist&tid=$tid",0,10,false,true);
	//$multipage = str_replace('class="pg"','class="jlpg"',$multipage);
	
	$query = DB::query("SELECT A.uid,A.applynumber,B.username FROM ".DB::table('xj_eventapply')." A,".DB::table('common_member')." B WHERE A.uid = B.uid and A.tid = '$tid' and A.pj=4 ORDER BY A.dateline LIMIT $start_limit,$perpage");
	$joinlist = array();
	while($value = DB::fetch($query)){
		//$value['avatar'] = avatar($value['uid'], 'middle');
		$value['avatar'] = '<img src="'.avatar($value[uid], 'middle', true, false, true).'?random='.random(2).'" onerror="this.onerror=null;this.src=\''.$_G['setting']['ucenterurl'].'/images/noavatar_middle.gif\'" width="26" height="26" align="absmiddle" />';
		$joinlist[] = $value;
	}

}elseif($_GET['action']=='pass'){     //通过审核的报名用户
	$listcount = DB::result_first("SELECT COUNT(*) FROM ".DB::table('xj_eventapply')." WHERE tid='$tid' and verify=1");
	$page = $_GET['page']?$_GET['page']:1;
	if(@ceil($listcount/$perpage) < $page) {
		$page = 1;
	}
	$start_limit = ($page - 1) * $perpage;
	$multipage = multi($listcount,$perpage,$page,"plugin.php?id=xj_event:event_userlist&action=".$_GET['action']."&tid=$tid",0,10,false,true);
	//$multipage = str_replace('class="pg"','class="jlpg"',$multipage);
	
	$query = DB::query("SELECT A.*,B.username,C.* FROM ".DB::table('xj_eventapply')." A,".DB::table('common_member')." B,".DB::table('xj_event_member_info')." C WHERE A.uid = B.uid AND A.uid=C.uid and A.verify=1 AND A.tid = '$tid' ORDER BY A.dateline DESC LIMIT $start_limit,$perpage");
	$joinlist = array();
	while($value = DB::fetch($query)){
		//$value['avatar'] = avatar($value['uid'], 'middle');
		$value['avatar'] = '<img src="'.avatar($value[uid], 'middle', true, false, true).'?random='.random(2).'" onerror="this.onerror=null;this.src=\''.$_G['setting']['ucenterurl'].'/images/noavatar_middle.gif\'" width="26" height="26" align="absmiddle" />';
		$joinlist[] = $value;
	}
}else{
	$listcount = DB::result_first("SELECT COUNT(*) FROM ".DB::table('xj_eventapply')." WHERE tid='$tid'");
	$page = $_GET['page']?$_GET['page']:1;
	if(@ceil($listcount/$perpage) < $page) {
		$page = 1;
	}
	$start_limit = ($page - 1) * $perpage;
	$multipage = multi($listcount,$perpage,$page,"plugin.php?id=xj_event:event_userlist&action=".$_GET['action']."&tid=$tid",0,10,false,false);
	//$multipage = str_replace('class="pg"','class="jlpg"',$multipage);
	
	$query = DB::query("SELECT A.*,B.username,C.* FROM ".DB::table('xj_eventapply')." A,".DB::table('common_member')." B,".DB::table('xj_event_member_info')." C WHERE A.uid = B.uid AND A.uid=C.uid AND A.tid = '$tid' ORDER BY A.dateline DESC LIMIT $start_limit,$perpage");
	$joinlist = array();
	while($value = DB::fetch($query)){
		$value['avatar'] = '<img src="'.avatar($value[uid], 'middle', true, false, true).'?random='.random(2).'" onerror="this.onerror=null;this.src=\''.$_G['setting']['ucenterurl'].'/images/noavatar_middle.gif\'" width="26" height="26" align="absmiddle" />';
		$joinlist[] = $value;
	}
}


include template('xj_event:userlist');

?>