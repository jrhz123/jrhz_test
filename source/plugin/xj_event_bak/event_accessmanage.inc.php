<?php
/**
 *	[超级活动(xj_event.{modulename})] (C)2012-2099 Powered by 逍遥工作室.
 *	Version: 1.0
 *	Date: 2012-9-15 10:27
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
include DISCUZ_ROOT . './source/plugin/xj_event/include/func.php';
$tid = intval($_GET['tid']);

//权限验证
$thread = DB::fetch_first("SELECT authorid,setting FROM ".DB::table('forum_thread')." A,".DB::table('xj_event')." B WHERE A.tid='$tid' and A.tid = B.tid");
$setting = unserialize($thread['setting']);
//判断是不是管理团队
$event_admin = false;
if(in_array($_G['username'],$setting['event_admin'])){
	$event_admin = true;
}
if($_G['groupid']>1 && $_G['uid']!=$thread['authorid'] && !$event_admin){
	showmessage('quickclear_noperm');
}



if($_GET['action']=='access' and $_GET['applyid']){
	if(!submitcheck('alsubmit')){
		showmessage('submit_invalid');	
	}
	$applyids = implode(',',$_GET['applyid']);
	$access = intval($_GET['accessaction']);
	DB::query("UPDATE ".DB::table('xj_eventapply')." SET pj = $access WHERE applyid in($applyids)");
	$query = DB::query("SELECT uid FROM ".DB::table('xj_eventapply')." WHERE applyid in($applyids)");
	/**
	Enicn_D modified 2015-04-01 10:25
	*/
	$extcredits = $_G['setting']['extcredits'];
	while($value = DB::fetch($query)){
		if($_GET['accessaction']==1){
			$action_type = 'good';
		}elseif($_GET['accessaction']==2){
			$action_type = 'common';
		}elseif($_GET['accessaction']==3){
			$action_type = 'bad';
		}elseif($_GET['accessaction']==4){
			$action_type = 'plane';
		}
		DB::query("UPDATE ".DB::table('xj_event_member_info')." SET $action_type = $action_type + 1 WHERE uid=".$value['uid']);
		$credit_rule = array_shift(C::t('common_credit_rule')->fetch_all_by_action($action_type));
		foreach ($extcredits as $extkey => $extval) {
			$extcredits_name = 'extcredits'.$extkey;
			$extcredits_value = $credit_rule[$extcredits_name];
			C::t('common_member_count')->increase(array($_G['uid']),array($extcredits_name => $extcredits_value));
		}
	}
	require_once DISCUZ_ROOT.'./source/class/class_credit.php';
	credit::countcredit($_G['uid'],'update');
	/**
	Enicn_D modified 2015-04-01 10:25
	*/
}


$listcount = DB::result_first("SELECT COUNT(*) FROM ".DB::table('xj_eventapply')." WHERE tid='$tid' and verify=1");
$perpage = 10; //每页数
$page = $_GET['page']?$_GET['page']:1;
if(@ceil($listcount/$perpage) < $page) {
	$page = 1;
}
$start_limit = ($page - 1) * $perpage;
$multipage = mymulti($listcount,$perpage,$page,"plugin.php?id=xj_event:event_accessmanage&tid=$tid",0,10,false,true,false,'accesslist_display');
$multipage = str_replace('class="pg"','class="jlpg"',$multipage);

$query = DB::query("SELECT * FROM ".DB::table('xj_eventapply')." A,".DB::table('common_member')." B WHERE A.uid = B.uid and A.tid = '$tid' and A.verify=1 ORDER BY A.verify,A.dateline LIMIT $start_limit,$perpage");
$accesslist = array();
while($value = DB::fetch($query)){
	$value['dateline'] = date('Y-m-d H:i:s',$value['dateline']);
	$value['sharenum'] = DB::result_first("SELECT count(*) FROM ".DB::table('xj_eventthread')." A,".DB::table('forum_thread')." B WHERE A.tid=B.tid and A.eid=".$value['eid']." and B.authorid=".$value['uid']);
	$accesslist[] = $value;
}

include template('xj_event:access_manage');

?>