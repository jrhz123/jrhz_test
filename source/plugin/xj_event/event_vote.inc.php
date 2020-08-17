<?php
/**
 *	[超级活动(xj_event.{modulename})] (C)2012-2099 Powered by 逍遥工作室.
 *	Version: 1.0
 *	Date: 2012-9-15 10:27
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
$tid = intval($_GET["tid"]);
$eid = intval($_GET["eid"]);
$uid = $_G["uid"];
$ip = $_G["clientip"];
$nowtime = $_G["timestamp"];
$eventinfo = DB::fetch_first("SELECT tid,setting FROM ".DB::table('xj_event')." WHERE eid='$eid'");
$setting = unserialize($eventinfo['setting']);
$votestarttime = dgmdate($setting['vote']['votestarttime'],'dt');
$voteendtime = dgmdate($setting['vote']['voteendtime'],'dt');
$votecheckhour = $setting['vote']['votecheckhour'];
$votechecknumber = $setting['vote']['votechecknumber'];
$votechecktime = $_G['timestamp']-3600*$votecheckhour;

if($setting[vote][openvote]!=1){
	showmessage(lang('plugin/xj_event', 'bhdwkqtp'));
}else{
	if($nowtime<$setting['vote']['votestarttime']){
		showmessage(lang('plugin/xj_event', 'tphwks'));
	}
	if($nowtime>$setting['vote']['voteendtime']){
		showmessage(lang('plugin/xj_event', 'tpyjjs'));
	}
}
if($setting['vote']['ipcheck'] == 1){
	$votes = DB::result_first("SELECT COUNT(*) FROM ".DB::table('xj_event_vote_log')." WHERE tid='$tid' and ip='$ip' AND votetime>$votechecktime");
	if($votes>=$votechecknumber){
		showmessage(lang('plugin/xj_event', 'mei').$votecheckhour.lang('plugin/xj_event', 'xsznt').$votechecknumber.lang('plugin/xj_event', 'pqshztp'));
	}
}
if($setting['vote']['usercheck'] == 1){
	if($uid==0){
		showmessage(lang('plugin/xj_event', 'ykwftpqdl'));
	}

	$votes = DB::result_first("SELECT COUNT(*) FROM ".DB::table('xj_event_vote_log')." WHERE tid='$tid' and uid='$uid' AND votetime>$votechecktime");
	if($votes>=$votechecknumber){
		showmessage(lang('plugin/xj_event', 'mei').$votecheckhour.lang('plugin/xj_event', 'xsznt').$votechecknumber.lang('plugin/xj_event', 'pqshztp'));
	}
}

if($_GET['formhash'] != $_G['formhash']){
		showmessage('submit_invalid');
}

DB::query("INSERT INTO ".DB::table('xj_event_vote_log')." (eid,tid,uid,ip,votetime) VALUES ($eid,$tid,$uid,'$ip',$nowtime)");
DB::query("UPDATE ".DB::table('xj_eventthread')." set votes = votes+1 WHERE tid = '$tid'");
$items = DB::fetch_first("SELECT votes FROM ".DB::table('xj_eventthread')." WHERE tid='$tid'");
showmessage(lang('plugin/xj_event', 'dqps').' <strong id="recommentc" class="xi1 xs2">'.$items['votes'].'</strong><br />'.lang('plugin/xj_event', 'tpcg'));

?>