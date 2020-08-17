<?php
/**
 *	[超级活动(xj_event.{modulename})] (C)2012-2099 Powered by 逍遥工作室.
 *	Version: 1.0
 *	Date: 2012-9-15 10:27
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
include DISCUZ_ROOT . './source/plugin/xj_event/include/sms_func.php';
include DISCUZ_ROOT . './source/plugin/xj_event/include/func.php';
$tid = intval($_GET['tid']);


//权限限制
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
//获取剩余积分
if($_G['cache']['plugin']['xj_event']['smscredit']){
	$smscredit = $_G['cache']['plugin']['xj_event']['smscredit'];
	$mysmscredit = DB::result_first("SELECT extcredits$smscredit FROM ".DB::table('common_member_count')." WHERE uid = ".$_G['uid']);
}


if($_GET['action']=='send'){
	if(!submitcheck('smssubmit')){
		showmessage('submit_invalid');	
	}
	$item = DB::fetch_first("SELECT subject FROM ".DB::table('forum_thread')." WHERE tid = '$tid'");
	$event_title = $item['subject'];
	$mobile = array();
	if($_GET['alluser'] == 0 && $_GET['uid']){
		$uids = implode(',',$_GET['uid']);
		$query = DB::query("SELECT mobile FROM ".DB::table('xj_eventapply')." WHERE tid = $tid AND uid in($uids)");
		while($value = DB::fetch($query)){
			$mobile[] = $value['mobile'];
		}
	}elseif($_GET['alluser'] == 1){
		$query = DB::query("SELECT mobile FROM ".DB::table('xj_eventapply')." WHERE tid='$tid'");
		while($value = DB::fetch($query)){
			$mobile[] = $value['mobile'];
		}
	}elseif($_GET['alluser'] == 2){
		$query = DB::query("SELECT mobile FROM ".DB::table('xj_eventapply')." WHERE tid='$tid' AND verify=1");
		while($value = DB::fetch($query)){
			$mobile[] = $value['mobile'];
		}
	}elseif($_GET['alluser'] == 3){
		$query = DB::query("SELECT mobile FROM ".DB::table('xj_eventapply')." WHERE tid='$tid' AND verify=0");
		while($value = DB::fetch($query)){
			$mobile[] = $value['mobile'];
		}
	}

	$message_content = $_GET['message_content'];
	if($_G['cache']['plugin']['xj_event']['smscredit']){ //设置了消耗积分才操作
		$sendcount = count($mobile);
		if($mysmscredit<$sendcount){
			$onerror = lang('plugin/xj_event', 'ningde').$_G['setting']['extcredits'][$smscredit]['title'].lang('plugin/xj_event', 'bzdxfssbqcz');
		}else{
			DB::query("UPDATE ".DB::table('common_member_count')." SET extcredits".$smscredit." = extcredits".$smscredit." - ".$sendcount." WHERE uid=".$_G['uid']);
		}
	}
	//如果前面没有错误就开始群发
	if(!$onerror){
		$sendtime = '';
		if($_GET['sendtime']){
			$sendtime = $_GET['sendtime'].':00';
		}
		$sendstate = xjsendsms($mobile,$message_content,lang('plugin/xj_event', 'huodongqunfa'),$sendtime);
		if($sendstate == 'ok'){
			$onerror = lang('plugin/xj_event', 'chenggong');
		}else{
			$onerror = lang('plugin/xj_event', 'fasongshibai'); 
		}
	}
}


$listcount = DB::result_first("SELECT COUNT(*) FROM ".DB::table('xj_eventapply')." WHERE tid='$tid'");
$perpage = 10; //每页数
$page = $_GET['page']?$_GET['page']:1;
if(@ceil($listcount/$perpage) < $page) {
	$page = 1;
}
$start_limit = ($page - 1) * $perpage;

$multipage = mymulti($listcount,$perpage,$page,"plugin.php?id=xj_event:event_sms&tid=$tid",0,10,false,true,false,'sms_display');

$query = DB::query("SELECT * FROM ".DB::table('xj_eventapply')." A,".DB::table('common_member')." B WHERE A.uid = B.uid and A.tid = '$tid' ORDER BY A.verify DESC,A.dateline LIMIT $start_limit,$perpage");
$accesslist = array();
while($value = DB::fetch($query)){
	$value['dateline'] = date('Y-m-d H:i:s',$value['dateline']);
	$value['sharenum'] = DB::result_first("SELECT count(*) FROM ".DB::table('xj_eventthread')." A,".DB::table('forum_thread')." B WHERE A.tid=B.tid and A.eid=".$value['eid']." and B.authorid=".$value['uid']);
	$accesslist[] = $value;
}

include template('xj_event:event_sms');



?>