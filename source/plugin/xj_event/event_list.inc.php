<?php
/**
 *	[超级活动(xj_event.{modulename})] (C)2012-2099 Powered by 逍遥工作室.
 *	Version: 1.0
 *	Date: 2012-9-15 10:27
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}



if($_G['mobile']){
	$nowtime = $_G['timestamp'];
	
	$tmp = explode("\r\n",$_G['cache']['plugin']['xj_event']['city']);
	$cityclass = array();
	foreach($tmp as $key=>$value){
		$ctmp = array();
		$ctmp[1] = trim($value);
		$ctmp[2] = DB::result_first("SELECT COUNT(*) FROM ".DB::table('xj_event')." WHERE citys='".$ctmp[1]."'");
		$ctmp[3] = urlencode(trim($value));
		$cityclass[] = $ctmp;
	}
	
	
	
	
	
	$sqlstr = "";
	if($_GET['pc']){
		$postclass = intval($_GET['pc']);
		$sqlstr = $sqlstr." AND A.postclass = $postclass";
	}
	if($_GET['offlineclass']){
		$offlineclass = intval($_GET['offlineclass']);
		$sqlstr = $sqlstr." AND A.offlineclass = $offlineclass";
	}
	if($_GET['onlineclass']){
		$onlineclass = intval($_GET['onlineclass']);
		$sqlstr = $sqlstr." AND A.onlineclass = $onlineclass";
	}
	if($_GET['choose']=='today'){
		$sqlstr = $sqlstr." AND A.starttime<$nowtime AND A.endtime>$nowtime";
	}
	if($_GET['choose']=='soon'){
		$sqlstr = $sqlstr." AND A.starttime>$nowtime";
	}
	if($_GET['city']){
		$citys = addslashes($_GET['city']);
		$sqlstr = $sqlstr." AND A.citys = '$citys'";
	}
	
	require_once libfile('function/post');
	
	$perpage = 10; //每页数
	$listcount = DB::result_first("SELECT count(*) FROM ".DB::table('xj_event')." A,".DB::table('forum_thread')." B WHERE A.tid=B.tid ".$sqlstr." ORDER BY A.eid");
	$page = $_GET['page']?$_GET['page']:1;
	if(@ceil($listcount/$perpage) < $page) {
		$page = 1;
	}
	$start_limit = ($page - 1) * $perpage;
	$multipage = multi($listcount,$perpage,$page,"plugin.php?id=xj_event:event_list&pc=".$_GET['pc']."&".($_GET['offlineclass']?"offlineclass=".$_GET['offlineclass']:"onlineclass=".$_GET['onlineclass'])."&city=".urlencode($_GET['city']),$_G['setting']['threadmaxpages']);
	
	$query = DB::query("SELECT * FROM ".DB::table('xj_event')." A,".DB::table('forum_thread')." B WHERE A.tid=B.tid ".$sqlstr." ORDER BY A.eid DESC LIMIT $start_limit,$perpage");
	$toplist = array();
	while($value = DB::fetch($query)){
		//获取报名人数
		$value['subject'] = cutstr($value['subject'],20);
		$value['zynumber'] = DB::result_first("SELECT count(*) FROM ".DB::table('xj_eventthread')." WHERE eid=".$value['eid']);
		$value['applynumber'] = DB::result_first("SELECT SUM(applynumber) FROM ".DB::table('xj_eventapply')." WHERE tid=".$value['tid']." and verify=1");
		$value['applynumber'] = $value['applynumber']?$value['applynumber']:0;
		if($value['activityaid']){
			$value['activityaid_url'] = getforumimg($value['activityaid'],0,80,80);
		}else{
			$value['activityaid_url'] = 'source/plugin/xj_event/module/mobilecenter/images/nopic.jpg';
		}
		$value['starttimestr'] = date('Y-m-d',$value['starttime']);
		$value['message'] = DB::result_first("SELECT message FROM ".DB::table('forum_post')." WHERE tid=".$value['tid']);
		$value['message'] = messagecutstr($value['message'],50);
		$value['setting'] = unserialize($value['setting']);
		$toplist[] = $value;
	}
	
	
	include template('event_list',0,'source/plugin/xj_event/module/mobilecenter/template');
}








if(empty($_GET['choose'])){
	$_GET['choose'] = 'new';
}

$xxnum = DB::result_first("SELECT COUNT(*) FROM ".DB::table('xj_event')." WHERE postclass=1");  //线下活动数
$xsnum = DB::result_first("SELECT COUNT(*) FROM ".DB::table('xj_event')." WHERE postclass=2");  //线上活动数
//获取线上和线下活动分类
$tmp = explode("\n",$_G['cache']['plugin']['xj_event']['event_offline_class']);
$offlineclass = array();
foreach($tmp as $key=>$value){
	$ctmp = array();
	$ctmp = explode("|",$value);
	$ctmp[1] = str_replace("\r",'',$ctmp[1]);
	$ctmp[2] = DB::result_first("SELECT COUNT(*) FROM ".DB::table('xj_event')." WHERE postclass=1 and offlineclass=".$ctmp[0]);
	$offlineclass[] = $ctmp;
}
$tmp = explode("\n",$_G['cache']['plugin']['xj_event']['event_online_class']);
$onlineclass = array();
foreach($tmp as $key=>$value){
	$ctmp = array();
	$ctmp = explode("|",$value);
	$ctmp[1] = str_replace("\r",'',$ctmp[1]);
	$ctmp[2] = DB::result_first("SELECT COUNT(*) FROM ".DB::table('xj_event')." WHERE postclass=2 and onlineclass=".$ctmp[0]);
	$onlineclass[] = $ctmp;
}

$tmp = explode("\n",$_G['cache']['plugin']['xj_event']['city']);
$cityclass = array();
foreach($tmp as $key=>$value){
	$ctmp = array();
	$ctmp[1] = trim($value);
	$ctmp[2] = DB::result_first("SELECT COUNT(*) FROM ".DB::table('xj_event')." WHERE citys='".$ctmp[1]."'");
	$cityclass[] = $ctmp;
}

$navtitle = lang('plugin/xj_event','huodonglb');


include template('xj_event:list');


/**
* 通过aid获取图片链接
*/
function getpicurl($aid,$tid){
  global $_G;
  $return = '';
  if($aid) {
	  $picatt = DB::fetch_first("SELECT remote,attachment,thumb FROM ".DB::table(getattachtablebytid($tid))." WHERE aid='{$aid}'");
	  if($picatt['remote']) {
		  $picatt['attachment'] = $_G['setting']['ftp']['attachurl'].'forum/'.$picatt['attachment'];
		  $picatt['attachment'] = substr($picatt['attachment'], 0, 7) != 'http://' ? 'http://'.$picatt['attachment'] : $picatt['attachment'];
	  } else {
		  $picatt['attachment'] = $_G['setting']['attachurl'].'forum/'.$picatt['attachment'];
	  }
  }
  $return = $picatt['attachment'];
  return $return;
}
?>