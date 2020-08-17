<?php
/**
 *	[超级活动(xj_event.{modulename})] (C)2012-2099 Powered by 逍遥工作室.
 *	Version: 1.0
 *	Date: 2012-9-15 10:27
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$nowtime = $_G['timestamp'];

if(!$_GET['act']){
	$_GET['act']='zz';
}

if($_GET['act']=='zz'){
	$sqlstr = 'and B.authorid='.intval($_G['uid']);
	$perpage = 10; //每页数
	$listcount = DB::result_first("SELECT count(*) FROM ".DB::table('xj_event')." A,".DB::table('forum_thread')." B WHERE A.tid=B.tid ".$sqlstr." ORDER BY A.eid");
	$page = $_GET['page']?$_GET['page']:1;
	if(@ceil($listcount/$perpage) < $page) {
		$page = 1;
	}
	$start_limit = ($page - 1) * $perpage;
	$multipage = multi($listcount,$perpage,$page,"plugin.php?id=xj_event:event_my",0,10,false,true);
	$multipage = str_replace('class="pg"','class="jlpg"',$multipage);
	$query = DB::query("SELECT * FROM ".DB::table('xj_event')." A,".DB::table('forum_thread')." B WHERE A.tid=B.tid ".$sqlstr." ORDER BY A.eid DESC LIMIT $start_limit,$perpage");
}elseif($_GET['act']=='cj'){
	$sqlstr = 'and A.uid='.intval($_G['uid']);
	$perpage = 10; //每页数
	$listcount = DB::result_first("SELECT count(*) FROM ".DB::table('xj_eventapply')." A,".DB::table('xj_event')." B,".DB::table('forum_thread')." C WHERE A.tid=B.tid and B.tid=C.tid ".$sqlstr);
	$page = $_GET['page']?$_GET['page']:1;
	if(@ceil($listcount/$perpage) < $page) {
		$page = 1;
	}
	$start_limit = ($page - 1) * $perpage;
	$multipage = multi($listcount,$perpage,$page,"plugin.php?id=xj_event:event_my",0,10,false,true);
	$multipage = str_replace('class="pg"','class="jlpg"',$multipage);
	$query = DB::query("SELECT * FROM ".DB::table('xj_eventapply')." A,".DB::table('xj_event')." B,".DB::table('forum_thread')." C WHERE A.tid=B.tid and B.tid=C.tid ".$sqlstr." ORDER BY B.eid DESC LIMIT $start_limit,$perpage");
	
}

$toplist = array();
while($value = DB::fetch($query)){
	//获取报名人数
	$value['imgkey'] =  md5($value['activityaid'].'|145|120');
	$value['starttime'] = date('Y-m-d',$value['starttime']);
	$value['message'] = DB::result_first("SELECT message FROM ".DB::table('forum_post')." WHERE tid=".$value['tid']);
	$value['message'] = cutstr(clearubb(strip_tags($value['message'])),180); //messagecutstr($value['message'],250); 
	$toplist[] = $value;
}
function clearubb($Text) {      /// UBB代码转换
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



include template('xj_event:event_my');

?>