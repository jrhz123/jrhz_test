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
include DISCUZ_ROOT . './source/plugin/xj_event/include/func.php';
$tid = intval($_GET['tid']);

//权限限制
$thread = DB::fetch_first("SELECT authorid,userfield,setting,subject,starttime FROM ".DB::table('forum_thread')." A,".DB::table('xj_event')." B WHERE A.tid='$tid' and A.tid = B.tid");
$setting = unserialize($thread['setting']);
$event_starttime = dgmdate($thread['starttime'],'dt');

//判断是不是管理团队
$event_admin = false;
if(in_array($_G['username'],$setting['event_admin'])){
	$event_admin = true;
}
if($_G['groupid']>1 && $_G['uid']!=$thread['authorid'] && !$event_admin){
	showmessage('quickclear_noperm');
}




if($_GET['action']=='verify' and $_GET['applyid']){
	if(!submitcheck('jmsubmit')){
		showmessage('submit_invalid');	
	}
	$applyids = implode(',',$_GET['applyid']);
	if($_GET['verifyaction']==1){
		$checknum = DB::result_first("SELECT SUM(applynumber) FROM ".DB::table('xj_eventapply')." WHERE applyid in($applyids)");  //本次验证人数
		$applynum = DB::result_first("SELECT SUM(applynumber) FROM ".DB::table('xj_eventapply')." WHERE tid='$tid' and verify=1"); //已报名人数
		$applycountnum = DB::result_first("SELECT event_number FROM ".DB::table('xj_event')." WHERE tid='$tid'"); //活动总人数
		if($checknum <= ($applycountnum-$applynum) or $applycountnum==0){
			DB::query("UPDATE ".DB::table('xj_eventapply')." SET verify = 1 WHERE applyid in($applyids)");
			//发手机短信
			if($setting['seccode'] == 1){		
				include 'include/sms_func.php';
				$selectlist = DB::fetch_all("SELECT uid,mobile,applynumber,seccode FROM ".DB::table('xj_eventapply')." WHERE applyid in($applyids)"); 
				foreach($selectlist as $value){
					$message = cutstr($thread['subject'],30).lang('plugin/xj_event', 'hdbmcgrs').':'.$value['applynumber'].lang('plugin/xj_event', 'renyanzhengma').':'.$value['seccode'].lang('plugin/xj_event', 'huodongshijian').' :'.$event_starttime;
					xjsendsms(array($value['mobile']),$message,lang('plugin/xj_event', 'maomyzmdx'));
					sendpm($value['uid'],'',$message,$_G['uid']);
				}
			}
			
			
			
			//发送通知
			$query = DB::query("SELECT uid FROM ".DB::table('xj_eventapply')." WHERE applyid in($applyids)");
			while($value = DB::fetch($query)){
				notification_add($value['uid'], 'system',lang('plugin/xj_event', 'ningbmcjd').' <a href="forum.php?mod=viewthread&tid='.$tid.'" target="_blank">'.$thread['subject'].'</a> '.lang('plugin/xj_event', 'yishenghtg'),array(),0);
			}
		}else{
			$onerror = lang('plugin/xj_event', 'mebgwfsh');
		}
	}elseif($_GET['verifyaction']==2){
		DB::query("UPDATE ".DB::table('xj_eventapply')." SET verify = 0 WHERE applyid in($applyids)");
	}elseif($_GET['verifyaction']==3){
		$items = DB::fetch_first("SELECT userfield,setting,use_extcredits_num,use_extcredits FROM ".DB::table('xj_event')." WHERE tid = '$tid'");
		if($items['use_extcredits_num']>0){
			$query = DB::query("SELECT uid FROM ".DB::table('xj_eventapply')." WHERE applyid in($applyids)");
			while($value = DB::fetch($query)){
				updatemembercount($value['uid'],array($items['use_extcredits']=>$items['use_extcredits_num']));
			}
		}
		DB::query("DELETE FROM ".DB::table('xj_eventapply')." WHERE applyid in($applyids)");
	}
}


//活动报名字段
$selectuserfield = unserialize($thread['userfield']);
$sysuserfield = unserialize($_G['setting']['activityfield']);



$listcount = DB::result_first("SELECT COUNT(*) FROM ".DB::table('xj_eventapply')." WHERE tid='$tid'");
$perpage = 15; //每页数
$page = $_GET['page']?$_GET['page']:1;
if(@ceil($listcount/$perpage) < $page) {
	$page = 1;
}
$start_limit = ($page - 1) * $perpage;
$multipage = multi($listcount,$perpage,$page,"plugin.php?id=xj_event:event_joinmanage&tid=$tid",0,10,false,true,false,'joinlist_display');

$query = DB::query("SELECT * FROM ".DB::table('xj_eventapply')." A,".DB::table('common_member')." B WHERE A.uid = B.uid and A.tid = '$tid' ORDER BY A.verify,A.dateline DESC LIMIT $start_limit,$perpage");
$joinlist = array();
require_once libfile('function/profile');
loadcache('profilesetting');
$i=1;
//签到
if(file_exists(DISCUZ_ROOT.'./source/plugin/xj_event/module/signed/wsq_signed.php')) {
	$signed_enable = true;
}

while($value = DB::fetch($query)){
	$value['dateline'] = dgmdate($value['dateline'], 'u');//date('Y-m-d H:i:s',$value['dateline']);
	$value['ufielddata'] = unserialize($value['ufielddata']);
	$data = '';
	$ufielddata = array();
	foreach($value['ufielddata'] as $key => $fieldid) {
		$data = profile_show($key, $value['ufielddata']);
		if($_G['cache']['profilesetting'][$key]['formtype'] == 'file') {
			$data = '<a href="'.$data.'" target="_blank" onclick="zoom(this, this.href, 0, 0, 0); return false;">'.lang('forum/misc', 'activity_viewimg').'</a>';
		}
		if($key=='birthday'){
			$ufielddata[$key]['value'] = $fieldid;
		}else{
			$ufielddata[$key]['value'] = $data;
		}
		if($key=='qq'){
			$ufielddata[$key]['value'] = '<a href="http://wpa.qq.com/msgrd?v=3&uin='.$fieldid.'&Site='.$_G['setting']['bbname'].'&Menu=yes&from=discuz" target="_blank" title="'.lang('spacecp', 'qq_dialog').'"><img src="'.STATICURL.'/image/common/qq.gif" alt="QQ" style="margin:0px;"/></a>'.$fieldid;
		}
	}
	$value['ufielddata'] = $ufielddata;
	$value['No'] = $i;
	
	//签到
	if($signed_enable) {
		$signed = DB::fetch_first("SELECT * FROM ".DB::table('xj_event_signed')." WHERE tid='$tid' AND uid=".$value['uid']);
		if($signed){
			$value['signed_dateline'] = dgmdate($signed['dateline']);
		}
	}
	
	
	$joinlist[] = $value;
	$i++;
}



include template('xj_event:join_manage');

?>