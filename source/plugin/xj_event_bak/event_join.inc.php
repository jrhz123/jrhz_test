<?php
/**
 *	[超级活动(xj_event.{modulename})] (C)2012-2099 Powered by 逍遥工作室.
 *	Version: 1.0
 *	Date: 2012-9-15 10:27
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
if(!$_G['uid']) {
	showmessage('not_loggedin', NULL, array(), array('login' => 1));
}
$tid = intval($_GET['tid']);
$uid = intval($_G['uid']);
include 'include/sms_func.php';


if($_GET['action']=='cannel'){
	if($_GET['formhash'] != $_G['formhash']){
			showmessage('submit_invalid');
	}
	$items = DB::fetch_first("SELECT * FROM ".DB::table('xj_event')." A,".DB::table('forum_thread')." B WHERE A.tid=B.tid AND A.tid = '$tid'");
	if($items['activityexpiration']>$timestamp){
		$count = DB::result_first("SELECT count(*) FROM ".DB::table('xj_eventapply')." WHERE tid = '$tid' and uid = ".$_G['uid']);
		if($count < 1){
			exit('Access Denied');
		}
	
		DB::query("DELETE FROM ".DB::table('xj_eventapply')." WHERE tid = '$tid' and uid = ".$_G['uid']);
		//积分操作
		if($items['use_extcredits_num']>0){
			updatemembercount($_G['uid'],array($items['use_extcredits']=>$items['use_extcredits_num']));
		}
		//notification_add($items['authorid'], 'system',$_G['username'].' 取消了活动 <a href="forum.php?mod=viewthread&tid='.$tid.'" target="_blank">'.$items['subject'].'</a> 报名',array(),0);
		notification_add($items['authorid'], 'activity', 'activity_cancel', array(
				'tid' => $tid,
				'subject' => $items['subject'],
				'reason' => $message
			));
		showmessage(lang('plugin/xj_event', 'cgqxbm'));
	}else{
		exit('Access Denied');
	}
}
if($_GET['action'] == 'checkseccode'){
	if($_GET['formhash'] != $_G['formhash']){
			showmessage('submit_invalid');
	}
	$eid = intval($_GET['eid']);
	if(!$_GET['seccode']){
		showmessage(lang('plugin/xj_event', 'yzmwxhzyjsy'));
	}
	$seccode = dhtmlspecialchars(trim($_GET['seccode']));
	$apply = DB::fetch_first("SELECT applyid,uid FROM ".DB::table('xj_eventapply')." WHERE eid='$eid' AND secstate=0 AND seccode=$seccode");
	$applyid = $apply['applyid'];
	if($applyid){
		DB::update('xj_eventapply',array('secstate'=>1,'sectime'=>$_G['timestamp']),"applyid=$applyid");
		notification_add($apply['uid'], 'system',lang('plugin/xj_event', 'ndhdbmyzm')." $seccode ".lang('plugin/xj_event', 'yishiyong'),array(),1);
		showmessage(lang('plugin/xj_event', 'yzmyxyzcg'),"forum.php?mod=viewthread&tid=$tid");
	}else{
		showmessage(lang('plugin/xj_event', 'yzmwxhzyjsy'));
	}
}
if($_GET['formhash'] != $_G['formhash']){
		showmessage('submit_invalid');
}



//验证人数
$applynumber = intval($_GET['applynumber']);  //本次验证人数
$applynum = DB::result_first("SELECT SUM(applynumber) FROM ".DB::table('xj_eventapply')." WHERE tid='$tid' and verify=1"); //已报名人数
$applycountnum = DB::result_first("SELECT event_number FROM ".DB::table('xj_event')." WHERE tid='$tid'"); //活动总人数

if($applycountnum>0){
	if($applynumber > ($applycountnum-$applynum)){
		showmessage(lang('plugin/xj_event', 'baomrsym'));
	}
}
//验证是否重复报名
$myapplynum = DB::result_first("SELECT count(*) FROM ".DB::table('xj_eventapply')." WHERE tid='$tid' and uid=$uid");
if($myapplynum > 0){
	showmessage(lang('plugin/xj_event', 'qinwcfbm'));	
} 





//获取活动组织人的ID
$item = DB::fetch_first("SELECT authorid,subject FROM ".DB::table('forum_thread')." WHERE tid = '$tid'");
$event_uid = $item['authorid'];
$event_title = $item['subject'];





$member = DB::fetch_first("SELECT extcredits1,extcredits2,extcredits3,extcredits4,extcredits5,extcredits6,extcredits7,extcredits8 FROM ".DB::table('common_member_count')." WHERE uid = ".$_G['uid']);
$items = DB::fetch_first("SELECT starttime,userfield,setting,use_extcredits_num,use_extcredits FROM ".DB::table('xj_event')." WHERE tid = '$tid'");
$event_starttime = dgmdate($items['starttime'],'dt');

//判断积分够不够
if($member['extcredits'.$items['use_extcredits']]<$items['use_extcredits_num']){
	showmessage($_G['setting']['extcredits'][$items['use_extcredits']]['title'].lang('plugin/xj_event', 'bgwfcj'));
}




$userfield = unserialize($items['userfield']);
$setting = unserialize($items['setting']);
foreach($_POST as $key => $value) {
	if(empty($value) && $key != 'message') {
		showmessage('activity_exile_field');
	}
}



$dateline = $_G['timestamp'];
$eid = intval($_GET['eid']);
$uid = $_G['uid'];
$realname = addslashes($_GET['realname']);
$mobile = addslashes($_GET['mobile']);
$qq = addslashes($_GET['qq']);
$bmmessage = addslashes(strip_tags($_GET['message']));


$ufielddata = array();
$selectuserfield = unserialize($items['userfield']);
$sysuserfield = unserialize($_G['setting']['activityfield']);
foreach($sysuserfield as $key => $value){
	if(in_array($key,$selectuserfield)){
		if(is_array($_GET[$key])){
			$ufielddata[$key] = implode(',',$_GET[$key]);
		}elseif($key=='birthday'){
			$ufielddata[$key] = $_GET['birthyear'].'-'.$_GET['birthmonth'].'-'.$_GET['birthday'];
		}elseif($key=='residecity'){
			$ufielddata[$key] = $_GET['resideprovince'].$_GET['residecity'].$_GET['residedist'].$_GET['residecommunity'];
		}else{
			$ufielddata[$key] = $_GET[$key];
		}
	}
}
$ufielddata = serialize($ufielddata);


$eventapply = array();
$eventapply['tid'] = $tid;
$eventapply['eid'] = $eid;
$eventapply['uid'] = $uid;
$eventapply['realname'] = $realname;
$eventapply['mobile'] = $mobile;
$eventapply['qq'] = $qq;
$eventapply['bmmessage'] = $bmmessage;
$eventapply['dateline'] = $dateline;
$eventapply['applynumber'] = $applynumber;
$eventapply['ufielddata'] = $ufielddata;
$eventapply['seccode'] = random(8,1);
$eventapply['session'] = intval($_GET['session']);

DB::insert('xj_eventapply',$eventapply);

$num = DB::result_first("SELECT count(*) FROM ".DB::table('xj_event_member_info')." WHERE uid = '$uid'");
if($num==0){
	DB::query("INSERT INTO ".DB::table('xj_event_member_info')." (uid) VALUES ('$uid')");
}
//积分操作
if($items['use_extcredits_num']>0){
	updatemembercount($_G['uid'],array($items['use_extcredits']=>-$items['use_extcredits_num']));
}



if($setting['noverify']==1){
	if($setting['eventpay']){
		showmessage(lang('plugin/xj_event', 'bmzltjcgxzzrzfym'), "plugin.php?id=xj_event:event_pay&tid=$tid", array(), array('showdialog' => 1, 'showmsg' => true, 'locationtime' => true, 'alert' => 'right'));
	}else{	
		DB::query("update ".DB::table('xj_eventapply')." set verify=1 where tid='$tid' and uid=".$_G['uid']); //自动审核
		//短信操作
		if($setting['seccode'] == 1){
			$message = cutstr($event_title,30).lang('plugin/xj_event', 'hdbmcgrs').':'.$applynumber.lang('plugin/xj_event', 'renyanzhengma').':'.$eventapply['seccode'].' '.lang('plugin/xj_event', 'huodongshijian').':'.$event_starttime;
			xjsendsms(array($eventapply['mobile']),$message,lang('plugin/xj_event', 'maomyzmdx'));
			sendpm($eventapply['uid'],'',$message,$event_uid);
		}
		notification_add($event_uid, 'activity', $_G['username'].' '.lang('plugin/xj_event', 'bmcjlnd').' <a href="forum.php?mod=viewthread&tid='.$tid.'" target="_blank">'.$event_title.'</a> '.lang('plugin/xj_event', 'hdxtyzdsh'),array(),0);
		//notification_add($event_uid, 'system',$_G['username'].' '.lang('plugin/xj_event', 'bmcjlnd').' <a href="forum.php?mod=viewthread&tid='.$tid.'" target="_blank">'.$event_title.'</a> '.lang('plugin/xj_event', 'hdxtyzdsh'),array(),0);
		showmessage(lang('plugin/xj_event', 'gxnbmcg'), "forum.php?mod=viewthread&tid=$tid", array(), array('showdialog' => 1, 'showmsg' => true, 'locationtime' => true, 'alert' => 'right'));
	}
}else{
	if($setting['eventpay']){
		showmessage(lang('plugin/xj_event', 'bmzltjcgxzzrzfym'), "plugin.php?id=xj_event:event_pay&tid=$tid", array(), array('showdialog' => 1, 'showmsg' => true, 'locationtime' => true, 'alert' => 'right'));
	}
	notification_add($event_uid, 'activity', 'activity_notice', array(
				'tid' => $tid,
				'subject' => $event_title,
			));
	//notification_add($event_uid, 'system',$_G['username'].' '.lang('plugin/xj_event', 'bmcjlnd').' <a href="forum.php?mod=viewthread&tid='.$tid.'" target="_blank">'.$event_title.'</a> '.lang('plugin/xj_event', 'hdqsh'),array(),0);
	showmessage(lang('plugin/xj_event', 'bmxxtjcgqddsh'), "forum.php?mod=viewthread&tid=$tid", array(), array('showdialog' => 1, 'showmsg' => true, 'locationtime' => true, 'alert' => 'right'));
}


?>