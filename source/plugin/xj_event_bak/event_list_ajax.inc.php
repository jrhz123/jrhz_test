<?php
/**
 *	[�����(xj_event.{modulename})] (C)2012-2099 Powered by ��ң������.
 *	Version: 1.0
 *	Date: 2012-9-15 10:27
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
$nowtime = time();

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


$perpage = 10; //ÿҳ��
$listcount = DB::result_first("SELECT count(*) FROM ".DB::table('xj_event')." A,".DB::table('forum_thread')." B WHERE A.tid=B.tid AND B.displayorder>=0 ".$sqlstr." ORDER BY A.eid");
$page = $_GET['page']?$_GET['page']:1;
if(@ceil($listcount/$perpage) < $page) {
	$page = 1;
}
$start_limit = ($page - 1) * $perpage;
$multipage = multi($listcount,$perpage,$page,"plugin.php?id=xj_event:event_list_ajax&pc=".$_GET['pc']."&".($_GET['offlineclass']?"offlineclass=".$_GET['offlineclass']:"onlineclass=".$_GET['onlineclass'])."&city=".$_GET['city'],0,10,false,true);
$multipage = str_replace('class="pg"','class="jlpg"',$multipage);

$query = DB::query("SELECT * FROM ".DB::table('xj_event')." A,".DB::table('forum_thread')." B WHERE A.tid=B.tid AND B.displayorder>=0 ".$sqlstr." ORDER BY A.eid DESC LIMIT $start_limit,$perpage");
$toplist = array();
while($value = DB::fetch($query)){
	//��ȡ��������
	$value['zynumber'] = DB::result_first("SELECT count(*) FROM ".DB::table('xj_eventthread')." WHERE eid=".$value['eid']);
	$value['applynumber'] = DB::result_first("SELECT SUM(applynumber) FROM ".DB::table('xj_eventapply')." WHERE tid=".$value['tid']." and verify=1");
	$value['applynumber'] = $value['applynumber']?$value['applynumber']:0;
	if($value['activityaid']){
		$value[activityaid_url] = getforumimg($value['activityaid'],0,145,93);
		//$value[activityaid_url] = $_G['setting']['attachurl'].'forum/'.$value['activityaid_url'];
	}
	$value['starttimestr'] = date('Y-m-d',$value['starttime']);
	$value['message'] = DB::result_first("SELECT message FROM ".DB::table('forum_post')." WHERE tid=".$value['tid']);
	$value['message'] = cutstr(clearubb(strip_tags($value['message'])),170);
	$value['setting'] = unserialize($value['setting']);
	$toplist[] = $value;
}
function clearubb($Text) {      /// UBB����ת��
        $Text=stripslashes($Text);
		$Text=preg_replace("/\[url=(.+?)\](.+?)\[\/.+?\]/is","",$Text);
		$Text=preg_replace("/\[coverimg\](.+?)\[\/coverimg\]/is","",$Text);
		$Text=preg_replace("/\[img\](.+?)\[\/img\]/is","",$Text);
		$Text=preg_replace("/\[img=(.+?)\](.+?)\[\/img\]/is","",$Text);
		$Text=preg_replace("/\[media=(.+?)\](.+?)\[\/media\]/is","",$Text);
		$Text=preg_replace("/\[attach\](.+?)\[\/attach\]/is","",$Text);
		$Text=preg_replace("/\[audio\](.+?)\[\/audio\]/is","",$Text);
		$Text=preg_replace("/\[hide\](.+?)\[\/hide\]/is","",$Text);
		$Text=preg_replace("/\[(.+?)\]/is","",$Text);
		$Text=preg_replace("/\{:(.+?):\}/is","",$Text);
		$Text=str_replace("<br />","",$Text);
		$Text=str_replace("xj_event","",$Text);
        return $Text;
}

include template('xj_event:list_ajax');


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