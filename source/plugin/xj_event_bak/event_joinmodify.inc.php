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
$uid = intval($_G['uid']);
$applyid = intval($_GET['applyid']);

if($_GET['action'] == 'save'){
	$tid = intval($_GET['tid']);
	$siteid = $_G['wechat']['setting']['wsq_siteid'];
	if(submitcheck('joinmodifysubmit')){
		$apply = DB::fetch_first("SELECT eid,applynumber FROM ".DB::table('xj_eventapply')." WHERE applyid=$applyid");
		$items = DB::fetch_first("SELECT * FROM ".DB::table('xj_event')." A,".DB::table('forum_thread')." B WHERE A.tid=B.tid AND A.eid=".$apply['eid']);
		//活动已成功报名人总数
		$ybnumber = DB::result_first("SELECT sum(applynumber) FROM ".DB::table('xj_eventapply')." WHERE eid=".$apply['eid']." AND verify=1");
		if($items['event_number']>0 && intval($_GET['applynumber'])>($items['event_number']-($ybnumber-$apply['applynumber']))){
			showmessage(lang('plugin/xj_event','baomrsym'));
		}
		
	
		$applys = array();
		$applys['realname'] =  addslashes($_GET['realname']);
		$applys['mobile'] =  addslashes($_GET['mobile']);
		$applys['qq'] =  addslashes($_GET['qq']);
		$applys['bmmessage'] =  addslashes($_GET['message']);
		$applys['applynumber'] = intval($_GET['applynumber']);
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
		$applys['ufielddata'] = serialize($ufielddata);
		DB::update('xj_eventapply',$applys,"applyid=$applyid");
		//发提醒
		notification_add($items['authorid'], 'activity', $_G['username'].' '.lang('plugin/xj_event','xglhd').' '.$items['subject'].' '.lang('plugin/xj_event','baominzl').'，<a href="forum.php?mod=viewthread&tid='.$items['tid'].'" target="_blank">'.lang('plugin/xj_event','view').'</a>', array());
		if($_GET['s']=='wsq'){
			showmessage(lang('plugin/xj_event', 'bmzlxgcg'),"http://wsq.discuz.qq.com/?c=index&a=viewthread&f=wx&tid={$tid}&siteid={$siteid}&_bpage=1");
		}else{
			showmessage(lang('plugin/xj_event', 'bmzlxgcg'),"forum.php?mod=viewthread&tid=$tid");
		}
	}else{
		showmessage('submit_invalid');
	}

}






$apply = DB::fetch_first("SELECT * FROM ".DB::table('xj_eventapply')." WHERE applyid=$applyid");
$items = DB::fetch_first("SELECT * FROM ".DB::table('xj_event')." WHERE eid=".$apply['eid']);
$items['setting'] = unserialize($items['setting']);
if($apply['verify']==1 && !$items['setting']['noverify']){
	showmessage('quickclear_noperm');
}




$apply['ufielddata'] = unserialize($apply['ufielddata']);
$userfield = unserialize($items['userfield']);
$tid = intval($items['tid']);
$selectuserfield = unserialize($items['userfield']);


if($selectuserfield) {
	if($selectuserfield) {
		$htmls = $settings = array();
		require_once libfile('function/profile');
		foreach($selectuserfield as $fieldid) {
			if(empty($ufielddata['userfield'])) {
				$memberprofile = C::t('common_member_profile')->fetch($_G['uid']);
				foreach($selectuserfield as $val) {
					if($val == 'birthday'){
						$tmp = explode('-',$apply['ufielddata']['birthday']);
						$ufielddata['userfield']['birthyear'] =  $tmp[0];
						$ufielddata['userfield']['birthmonth'] =  $tmp[1];
						$ufielddata['userfield']['birthday'] =  $tmp[2];	
					}elseif($val == 'realname'){
						$ufielddata['userfield']['realname'] = $apply['realname'];
					}elseif($val == 'mobile'){
						$ufielddata['userfield']['mobile'] = $apply['mobile'];
					}else{
						$ufielddata['userfield'][$val] = $apply['ufielddata'][$val];//$memberprofile[$val];
					}
				}
				unset($memberprofile);
			}



			
			$html = profile_setting($fieldid, $ufielddata['userfield'], false, true);
			if($html) {
				$settings[$fieldid] = $_G['cache']['profilesetting'][$fieldid];
				$htmls[$fieldid] = $html;
			}

		}

		
	}
} else {
	$selectuserfield = '';
}


//报名时可能选择的人数
$items['event_number_max'] = $items['event_number_max']>0?$items['event_number_max']:1;
$applynumber = array();
for($i=1;$i<=$items['event_number_max'];$i++){
	$applynumber[] = $i;
}
$thread =  DB::fetch(DB::query("SELECT subject FROM ".DB::table('forum_thread')." WHERE tid = '$tid'"));

include template('xj_event:event_joinmodify');


?>