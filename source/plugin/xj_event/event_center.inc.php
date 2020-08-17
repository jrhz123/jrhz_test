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
	if(file_exists(DISCUZ_ROOT.'./source/plugin/xj_event/module/mobilecenter/center.php')) {
		@include 'module/mobilecenter/center.php';
		include template('center',0,'source/plugin/xj_event/module/mobilecenter/template');
	}else{
		$nowtime = time();

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
		$sqlstr = $sqlstr." AND B.fid <> 151 AND B.displayorder >= 0"; //Enicn_d modified 2016-05-18 Add forum and displayorder filter
		require_once libfile('function/post');

		$perpage = 10; //每页数
		$listcount = DB::result_first("SELECT count(*) FROM ".DB::table('xj_event')." A,".DB::table('forum_thread')." B WHERE A.tid=B.tid ".$sqlstr." ORDER BY A.eid");
		$page = $_GET['page']?$_GET['page']:1;
		if(@ceil($listcount/$perpage) < $page) {
			$page = 1;
		}
		$start_limit = ($page - 1) * $perpage;
		$multipage = multi($listcount,$perpage,$page,"plugin.php?id=xj_event:event_center&pc=".$_GET['pc']."&".($_GET['offlineclass']?"offlineclass=".$_GET['offlineclass']:"onlineclass=".$_GET['onlineclass'])."&city=".$_GET['city'],0,10,false,true);
		$multipage = str_replace('class="pg"','class="jlpg"',$multipage);

		$query = DB::query("SELECT * FROM ".DB::table('xj_event')." A,".DB::table('forum_thread')." B WHERE A.tid=B.tid ".$sqlstr." ORDER BY A.eid DESC LIMIT $start_limit,$perpage");
		$toplist = array();
		while($value = DB::fetch($query)){
			//获取报名人数
			$value['zynumber'] = DB::result_first("SELECT count(*) FROM ".DB::table('xj_eventthread')." WHERE eid=".$value['eid']);
			$value['applynumber'] = DB::result_first("SELECT SUM(applynumber) FROM ".DB::table('xj_eventapply')." WHERE tid=".$value['tid']." and verify=1");
			$value['applynumber'] = $value['applynumber']?$value['applynumber']:0;
			if($value['activityaid']){
				$value[activityaid_url] = getpicurl($value['activityaid'],$value['tid']);
				//$value[activityaid_url] = $_G['setting']['attachurl'].'forum/'.$value['activityaid_url'];
			}
			$value['starttime'] = date('Y-m-d',$value['starttime']);
			$value['message'] = DB::result_first("SELECT message FROM ".DB::table('forum_post')." WHERE tid=".$value['tid']);
			$value['message'] = messagecutstr($value['message'],50);
			$value['setting'] = unserialize($value['setting']);
			$toplist[] = $value;
		}
		include template('xj_event:center');
	}
}


$allowthreadplugin = $_G['setting']['threadplugins'] ? C::t('common_setting')->fetch('allowthreadplugin', true) : array();
$mythread = $allowthreadplugin[$_G['groupid']];
if(!in_array('xj_event',$mythread)){
	$forumlist = false;
}
$forumlist = forumselect(FALSE, 0, $fid, TRUE);

$metakeywords = $navtitle = lang('plugin/xj_event', 'huodzx');
$nowtime=$_G['timestamp'];
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
$tmp = explode("\r\n",$_G['cache']['plugin']['xj_event']['city']);
$cityclass = array();
foreach($tmp as $key=>$value){
	$ctmp = array();
	$ctmp[1] = trim($value);
	$ctmp[2] = DB::result_first("SELECT COUNT(*) FROM ".DB::table('xj_event')." WHERE citys='".$ctmp[1]."'");
	$cityclass[] = $ctmp;
}


if($_G['cache']['plugin']['xj_event']['event_sysevent']){
	require_once libfile('function/post');
	$query = DB::query("SELECT A.tid,A.aid,A.starttimefrom,A.expiration,B.message,B.subject FROM ".DB::table('forum_activity')." A,".DB::table('forum_post')." B,".DB::table('forum_thread')." C WHERE A.tid = B.tid AND B.tid=C.tid AND C.displayorder >= 0 AND B.first=1 AND C.fid <> 151 ORDER BY A.starttimefrom DESC LIMIT 0,12");//Enicn_d modified 2016-05-18 Added forum and displayorder filter
	$syseventlist = array();
	while($value = DB::fetch($query)){
		$value['message'] = messagecutstr($value['message'],50);
		$value['m'] = date("Y-m",$value['starttimefrom']);
		$value['d'] = date("d",$value['starttimefrom']);
		$value['expiration'] = $value['expiration']?date("Y-m-d h:i",$value['expiration']):lang('plugin/xj_event', 'wu');
		$value['starttimefrom'] = dgmdate($value['starttimefrom'],'dt');
		$mainpicatt = DB::fetch_first("SELECT aid,remote,attachment,thumb,width FROM ".DB::table(getattachtablebytid($value['tid']))." WHERE aid=$value[aid]");
		if($mainpicatt['remote']) {
			$mainpicatt['attachment'] = $_G['setting']['ftp']['attachurl'].'forum/'.$mainpicatt['attachment'];
			$mainpicatt['attachment'] = substr($mainpicatt['attachment'], 0, 7) != 'http://' ? 'http://'.$mainpicatt['attachment'] : $mainpicatt['attachment'];
		} else {
			$mainpicatt['attachment'] = $_G['setting']['attachurl'].'forum/'.$mainpicatt['attachment'];
		}
		$value['pic'] = $mainpicatt['attachment'];
		$syseventlist[] = $value;
	}
}







if($_G['cache']['plugin']['xj_event']['event_centerstyle']==2){
	$query = DB::query("SELECT A.tid,B.subject,B.message,C.attachment FROM ".DB::table('xj_eventthread')." A,".DB::table('forum_post')." B,".DB::table('forum_threadimage')." C WHERE A.tid = B.tid AND  A.tid = C.tid AND B.first=1 AND A.sort=1 ORDER BY B.dateline DESC LIMIT 0,3");
	$wqlist = array();
	require_once libfile('function/post');
	while($value = DB::fetch($query)){
		$value['message'] = messagecutstr($value['message'],50);
		$value['pic'] = $value['attachment'] ? 'data/attachment/forum/'.$value['attachment'] : STATICURL.'image/common/nophoto.gif';
		$wqlist[] = $value;
	}
	$eventcount = 6; //调用的活动总数
	$query = DB::query("SELECT A.tid,A.starttime,A.endtime,A.use_cost,A.event_address,A.activityaid,A.activityaid_url,A.activityexpiration,B.subject,B.message FROM ".DB::table('xj_event')." A,".DB::table('forum_post')." B,".DB::table('forum_thread')." C WHERE A.tid = B.tid AND B.tid = C.tid AND C.displayorder >= 0 AND B.first=1 AND A.activitybegin<$nowtime AND A.endtime>$nowtime AND C.fid <> 151 ORDER BY A.activityexpiration DESC"); //Enicn_d modified 2016-05-18 Added forum and displayorder filter
	$eventlist = array();
	while($value = DB::fetch($query)){
		$value['applynumber'] = DB::result_first("SELECT SUM(applynumber) FROM ".DB::table('xj_eventapply')." WHERE tid=".$value['tid']);
		$value['applynumber'] = $value['applynumber']?$value['applynumber']:0;

		$value['message'] = messagecutstr($value['message'],50);
		$value['m'] = date("Y-m",$value['starttime']);
		$value['d'] = date("d",$value['starttime']);
		if($value['endtime']<$nowtime){
			$value['close'] = 1;
		}
		$value['endtime'] = dgmdate($value['endtime'],'dt');
		$value['starttime'] = dgmdate($value['starttime'],'dt');

		if(!$value['activityaid'] and $value['activityaid_url']){
			$value['activityaid_url'] = $value['activityaid_url'];
		}elseif($value['activityaid']){
			$value['activityaid_url'] = getforumimg($value['activityaid'],0,444,261);
			//$value['activityaid_url'] = getforumimg($value['activityaid'],0,444,261,'fixnone');
		}
		/*
		if($value['activityaid']){
			$value[activityaid_url] = $_G['setting']['attachurl'].'forum/'.$value['activityaid_url'];
		}
		*/
		$value['activityaid_url'] = $value['activityaid_url'] ? $value['activityaid_url'] : STATICURL.'image/common/nophoto.gif';
		//$value['pic'] = $value['activityaid_url'] ? 'data/attachment/forum/'.$value['activityaid_url'] : STATICURL.'image/common/nophoto.gif';
		$eventlist[] = $value;
		$eventcount = $eventcount -1;
	}
	if($eventcount>0){
		$query = DB::query("SELECT A.tid,A.starttime,A.endtime,A.use_cost,A.event_address,A.activityaid,A.activityaid_url,A.activityexpiration,B.subject,B.message FROM ".DB::table('xj_event')." A,".DB::table('forum_post')." B WHERE A.tid = B.tid AND B.first=1 AND A.endtime<$nowtime AND B.fid <> 151 ORDER BY A.activityexpiration DESC LIMIT 0,$eventcount");
		while($value = DB::fetch($query)){
			$value['applynumber'] = DB::result_first("SELECT SUM(applynumber) FROM ".DB::table('xj_eventapply')." WHERE tid=".$value['tid']." and verify=1");
			$value['applynumber'] = $value['applynumber']?$value['applynumber']:0;

			$value['message'] = messagecutstr($value['message'],50);
			$value['m'] = date("Y-m",$value['starttime']);
			$value['d'] = date("d",$value['starttime']);
			if($value['endtime']<$nowtime){
				$value['close'] = 1;
			}
			$value['endtime'] = dgmdate($value['endtime'],'dt');
			$value['starttime'] = dgmdate($value['starttime'],'dt');
			if(!$value['activityaid'] and $value['activityaid_url']){
				$value['activityaid_url'] = $value['activityaid_url'];
			}elseif($value['activityaid']){
				$value['activityaid_url'] = getforumimg($value['activityaid'],0,444,261);
			}
			$value['activityaid_url'] = $value['activityaid_url'] ? $value['activityaid_url'] : STATICURL.'image/common/nophoto.gif';
			//$value['pic'] = $value['activityaid_url'] ? 'data/attachment/forum/'.$value['activityaid_url'] : STATICURL.'image/common/nophoto.gif';
			$eventlist[] = $value;
			$eventcount = $eventcount -1;
		}
	}
     /* 史漫漫 2015年4月3日修改*/
	$query = DB::query("SELECT A.tid,A.starttime,A.endtime,A.use_cost,A.event_address,A.activityaid,A.activityaid_url,B.subject,B.message,A.activitybegin,A.activityexpiration FROM ".DB::table('xj_event')." A,".DB::table('forum_post')." B,".DB::table('forum_thread')." C WHERE A.tid = B.tid AND B.tid = C.tid AND C.displayorder >= 0 AND B.first=1 AND A.activitybegin>$nowtime AND C.fid <> 151 ORDER BY eid DESC"); //Enicn_d modified 2016-05-18 Added forum and displayorder filter
	$ygeventlist = array();
	while($value = DB::fetch($query)){

		$value['message'] = messagecutstr($value['message'],50);
		$value['m'] = date("Y-m",$value['starttime']);
		$value['d'] = date("d",$value['starttime']);
		if($value['endtime']<$nowtime){
			$value['close'] = 1;
		}
		$value['endtime'] = dgmdate($value['endtime'],'dt');
		$value['starttime'] = dgmdate($value['starttime'],'dt');
		$value['activitybegin'] = dgmdate($value['activitybegin'],'dt');
		 $value['activityexpiration '] = dgmdate($value['activityexpiration '],'dt');/* 史漫漫 2015年4月3日修改*/
		if($value['activityaid']){
			$value['activityaid_url'] = getforumimg($value['activityaid'],0,200,150);
			//$value[activityaid_url] = $_G['setting']['attachurl'].'forum/'.$value['activityaid_url'];
		}
		$value['activityaid_url'] = $value['activityaid_url'] ? $value['activityaid_url'] : STATICURL.'image/common/nophoto.gif';
		//$value['pic'] = $value['activityaid_url'] ? 'data/attachment/forum/'.$value['activityaid_url'] : STATICURL.'image/common/nophoto.gif';
		$ygeventlist[] = $value;
	}
	$ygeventcount = count($ygeventlist);

	//include template('xj_event:center2');
	include template('diy:center2',0,'source/plugin/xj_event/template');


}elseif($_G['cache']['plugin']['xj_event']['event_centerstyle']==3){
	require_once libfile('function/post');

	$sqlstr = "";
	if($_GET['pc']){
		$postclass = intval($_GET['pc']);
		$sqlstr = $sqlstr." AND A.postclass = $postclass";
	}
	if($_GET['offlineclass']){
		$gofflineclass = intval($_GET['offlineclass']);
		$sqlstr = $sqlstr." AND A.offlineclass = $gofflineclass";
	}
	if($_GET['onlineclass']){
		$gonlineclass = intval($_GET['onlineclass']);
		$sqlstr = $sqlstr." AND A.onlineclass = $gonlineclass";
	}
	if($_GET['city']){
		$selectcity = daddslashes($_GET['city']);
		$sqlstr = $sqlstr." AND A.citys = '$selectcity'";
	}
	if($_GET['choose']=='today'){
		$sqlstr = $sqlstr." AND A.starttime<$nowtime AND A.endtime>$nowtime";
	}
	if($_GET['choose']=='soon'){
		$sqlstr = $sqlstr." AND A.starttime>$nowtime";
	}
	$sqlstr = $sqlstr." AND B.fid <> 151 AND B.invisible >= 0"; //Enicn_d modified 2016-05-18 add invisible filter

	$perpage = 6; //每页数
	$listcount = DB::result_first("SELECT count(*) FROM ".DB::table('xj_event')." A,".DB::table('forum_post')." B WHERE A.tid=B.tid AND B.first=1 ".$sqlstr." ORDER BY A.eid");
	$page = $_GET['page']?$_GET['page']:1;
	if(@ceil($listcount/$perpage) < $page) {
		$page = 1;
	}
	$start_limit = ($page - 1) * $perpage;


	$multipage = multi($listcount,$perpage,$page,"plugin.php?id=xj_event:event_center&pc=$postclass".($_GET['city']?'&city='.urlencode($_GET['city']):'')."&offlineclass=".$_GET['offlineclass']."&onlineclass=".$_GET['onlineclass'],0,10,false,true);
	//$multipage = str_replace('class="pg"','class="jlpg"',$multipage);

	/* 史漫漫 2015年4月3日修改*/
	$query = DB::query("SELECT A.tid,A.starttime,A.endtime,A.use_cost,A.event_address,A.activityaid,A.activityaid_url,B.subject,B.message,A.activitybegin,A.activityexpiration FROM ".DB::table('xj_event')." A,".DB::table('forum_post')." B WHERE A.tid=B.tid AND B.first=1 ".$sqlstr." ORDER BY A.eid DESC LIMIT $start_limit,$perpage");
	$ygeventlist = array();
	while($value = DB::fetch($query)){
		$value['message'] = messagecutstr($value['message'],50);
		$value['m'] = date("Y-m",$value['starttime']);
		$value['d'] = date("d",$value['starttime']);
		if($value['endtime']<$nowtime){
			$value['close'] = 1;
		}
		//$value['endtime'] = date("Y-m-d h:i",$value['endtime']);
		//$value['starttime'] = date("Y-m-d h:i",$value['starttime']);
		$value['activitybegin'] = dgmdate($value['activitybegin'],'dt');
                $value['activityexpiration'] = dgmdate($value['activityexpiration'],'dt');/* 史漫漫 2015年4月3日修改*/
		if($value['activityaid']){
			$value['activityaid_url'] = getforumimg($value['activityaid'],0,282,180);
		}
		$value['activityaid_url'] = $value['activityaid_url'] ? $value['activityaid_url'] : STATICURL.'image/common/nophoto.gif';
		//$value['pic'] = $value['activityaid_url'] ? 'data/attachment/forum/'.$value['activityaid_url'] : STATICURL.'image/common/nophoto.gif';
		$ygeventlist[] = $value;
	}
	//include template('xj_event:center3');
	include template('diy:center3',0,'source/plugin/xj_event/template');
}elseif($_G['cache']['plugin']['xj_event']['event_centerstyle']==4){
	if(file_exists(DISCUZ_ROOT.'./source/plugin/xj_event/module/center4/center.php')) {
		@include 'module/center4/center.php';
		include template('diy:center',0,'source/plugin/xj_event/module/center4/template');
	}else{
		showmessage('Please purchase module');
	}
}elseif($_G['cache']['plugin']['xj_event']['event_centerstyle']==5){
	if(file_exists(DISCUZ_ROOT.'./source/plugin/xj_event/module/center5/center.php')) {
		@include 'module/center5/center.php';
		include template('diy:center',0,'source/plugin/xj_event/module/center5/template');
	}else{
		showmessage('Please purchase module');
	}
}elseif($_G['cache']['plugin']['xj_event']['event_centerstyle']==6){
	if(file_exists(DISCUZ_ROOT.'./source/plugin/xj_event/module/center6/center.php')) {
		@include 'module/center6/center.php';
		include template('diy:center',0,'source/plugin/xj_event/module/center6/template');

	}else{
		showmessage('Please purchase module');
	}
}else{
	include template('xj_event:center');
}


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

/**
* 获取板块
*/
function forumselect($groupselectable = FALSE, $arrayformat = 0, $selectedfid = 0, $showhide = FALSE, $evalue = FALSE, $special = 0) {
	global $_G;
	$items = array();
	$query = DB::query("select A.fid FROM ".DB::table('forum_forum')." A,".DB::table('forum_forumfield')." B WHERE A.fid = B.fid and A.type<>'group' and A.status=1 and B.threadplugin like '%xj_event%'");
	while($value = DB::fetch($query)){
		$items[] = $value['fid'];
	}


	if(!isset($_G['cache']['forums'])) {
		loadcache('forums');
	}
	$forumcache = &$_G['cache']['forums'];
	$forumlist = $arrayformat ? array() : '<optgroup label="&nbsp;">';
	foreach($forumcache as $forum) {
		if(!$forum['status'] && !$showhide) {
			continue;
		}
		if($selectedfid) {
			if(!is_array($selectedfid)) {
				$selected = $selectedfid == $forum['fid'] ? ' selected' : '';
			} else {
				$selected = in_array($forum['fid'], $selectedfid) ? ' selected' : '';
			}
		}
		if($forum['type'] == 'group') {
			if($arrayformat) {
				$forumlist[$forum['fid']]['name'] = $forum['name'];
			} else {
				$forumlist .= $groupselectable ? '<option value="'.($evalue ? 'gid_' : '').$forum['fid'].'" class="bold">--'.$forum['name'].'</option>' : '</optgroup><optgroup label="--'.$forum['name'].'">';
			}
			$visible[$forum['fid']] = true;
		} elseif($forum['type'] == 'forum' && isset($visible[$forum['fup']]) && (!$forum['viewperm'] || ($forum['viewperm'] && forumperm($forum['viewperm'])) || strstr($forum['users'], "\t$_G[uid]\t")) && (!$special || (substr($forum['allowpostspecial'], -$special, 1)))) {
			if($arrayformat) {
				$forumlist[$forum['fup']]['sub'][$forum['fid']] = $forum['name'];
			} else {
				if(in_array($forum['fid'],$items)){
					$forumlist .= '<option value="'.($evalue ? 'fid_' : '').$forum['fid'].'"'.$selected.'>'.$forum['name'].'</option>';
				}
			}
			$visible[$forum['fid']] = true;
		} elseif($forum['type'] == 'sub' && isset($visible[$forum['fup']]) && (!$forum['viewperm'] || ($forum['viewperm'] && forumperm($forum['viewperm'])) || strstr($forum['users'], "\t$_G[uid]\t")) && (!$special || substr($forum['allowpostspecial'], -$special, 1))) {
			if($arrayformat) {
				$forumlist[$forumcache[$forum['fup']]['fup']]['child'][$forum['fup']][$forum['fid']] = $forum['name'];
			} else {
				if(in_array($forum['fid'],$items)){
					$forumlist .= '<option value="'.($evalue ? 'fid_' : '').$forum['fid'].'"'.$selected.'>&nbsp; &nbsp; &nbsp; '.$forum['name'].'</option>';
				}
			}
		}
	}
	if(!$arrayformat) {
		$forumlist .= '</optgroup>';
		$forumlist = str_replace('<optgroup label="&nbsp;"></optgroup>', '', $forumlist);
	}
	return $forumlist;
}
?>
