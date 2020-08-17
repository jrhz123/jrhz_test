<?php
/**
 *	[超级活动(xj_event.{modulename})] (C)2012-2099 Powered by 逍遥工作室.
 *	Version: 1.0
 *	Date: 2012-9-15 10:27
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$nowtime = time();

if($_GET['action']=='all'){
	if($_GET['choose']=='new'){
		$query = DB::query("SELECT * FROM ".DB::table('xj_event')." A,".DB::table('forum_thread')." B WHERE A.tid=B.tid ORDER BY A.eid DESC LIMIT 0,10"); 
	}elseif($_GET['choose']=='today'){
		$query = DB::query("SELECT * FROM ".DB::table('xj_event')." A,".DB::table('forum_thread')." B WHERE A.tid=B.tid AND A.starttime<$nowtime AND A.endtime>$nowtime ORDER BY A.eid DESC LIMIT 0,10"); 
	}elseif($_GET['choose']=='soon'){
		$query = DB::query("SELECT * FROM ".DB::table('xj_event')." A,".DB::table('forum_thread')." B WHERE A.tid=B.tid AND A.starttime>$nowtime ORDER BY A.eid DESC LIMIT 0,10"); 
	}
}
if($_GET['action']=='official'){
	if($_GET['choose']=='new'){
		$query = DB::query("SELECT * FROM ".DB::table('xj_event')." A,".DB::table('forum_thread')." B WHERE A.tid=B.tid AND A.verify=1 ORDER BY A.eid DESC LIMIT 0,10"); 
	}elseif($_GET['choose']=='today'){
		$query = DB::query("SELECT * FROM ".DB::table('xj_event')." A,".DB::table('forum_thread')." B WHERE A.tid=B.tid AND A.verify=1 AND A.starttime<$nowtime AND A.endtime>$nowtime ORDER BY A.eid DESC LIMIT 0,10"); 
	}elseif($_GET['choose']=='soon'){
		$query = DB::query("SELECT * FROM ".DB::table('xj_event')." A,".DB::table('forum_thread')." B WHERE A.tid=B.tid AND A.verify=1 AND A.starttime>$nowtime ORDER BY A.eid DESC LIMIT 0,10"); 
	}
}
if($_GET['action']=='offline'){
	if($_GET['choose']=='new'){
		$query = DB::query("SELECT * FROM ".DB::table('xj_event')." A,".DB::table('forum_thread')." B WHERE A.tid=B.tid AND A.postclass=1 ORDER BY A.eid DESC LIMIT 0,10"); 
	}elseif($_GET['choose']=='today'){
		$query = DB::query("SELECT * FROM ".DB::table('xj_event')." A,".DB::table('forum_thread')." B WHERE A.tid=B.tid AND A.postclass=1 AND A.starttime<$nowtime AND A.endtime>$nowtime ORDER BY A.eid DESC LIMIT 0,10"); 
	}elseif($_GET['choose']=='soon'){
		$query = DB::query("SELECT * FROM ".DB::table('xj_event')." A,".DB::table('forum_thread')." B WHERE A.tid=B.tid AND A.postclass=1 AND A.starttime>$nowtime ORDER BY A.eid DESC LIMIT 0,10"); 
	}
}
if($_GET['action']=='online'){
	if($_GET['choose']=='new'){
		$query = DB::query("SELECT * FROM ".DB::table('xj_event')." A,".DB::table('forum_thread')." B WHERE A.tid=B.tid AND A.postclass=2 ORDER BY A.eid DESC LIMIT 0,10"); 
	}elseif($_GET['choose']=='today'){
		$query = DB::query("SELECT * FROM ".DB::table('xj_event')." A,".DB::table('forum_thread')." B WHERE A.tid=B.tid AND A.postclass=2 AND A.starttime<$nowtime AND A.endtime>$nowtime ORDER BY A.eid DESC LIMIT 0,10"); 
	}elseif($_GET['choose']=='soon'){
		$query = DB::query("SELECT * FROM ".DB::table('xj_event')." A,".DB::table('forum_thread')." B WHERE A.tid=B.tid AND A.postclass=2 AND A.starttime>$nowtime ORDER BY A.eid DESC LIMIT 0,10"); 
	}
}

$toplist = array();
while($value = DB::fetch($query)){
	//获取报名人数
	$value['applynumber'] = DB::result_first("SELECT SUM(applynumber) FROM ".DB::table('xj_eventapply')." WHERE tid=".$value['tid']." and verify=1");
	$value['applynumber'] = $value['applynumber']?$value['applynumber']:0;
	if($value['activityaid']){
		$value[activityaid_url] = getpicurl($value['activityaid'],$value['tid']);
	}
	$toplist[] = $value;
}

include template('xj_event:center_top');

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