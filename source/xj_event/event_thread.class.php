<?php
/**
 *	[疯狂活动(xj_event.{modulename})] (C)2012-2099 Powered by 逍遥设计.
 *	Version: 1.0
 *	Date: 2012-9-11 12:03
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
define('EVENT_NAME', lang('plugin/xj_event', 'fabuhd'));
define('EVENT_BUTTONTEXT', lang('plugin/xj_event', 'fabuhd'));
define('EVENT_ICON', 'images/event.gif');
loadcache('plugin');


class threadplugin_xj_event {
	var $name = EVENT_NAME;			//主题类型名称
	var $iconfile = EVENT_ICON;		//发布主题链接中的前缀图标
	var $buttontext = EVENT_BUTTONTEXT;	//发帖时按钮文字
	function newthread($fid) {
	    	global $_G;
			$extcredits = $_G['setting']['extcredits'];
			$citys = explode("\r\n",$_G['cache']['plugin']['xj_event']['city']);
			$tmp = explode("\n",$_G['cache']['plugin']['xj_event']['event_offline_class']);
			$offlineclass = array();
			foreach($tmp as $key=>$value){
				$offlineclass[] = explode("|",$value);
			}
			$tmp = explode("\n",$_G['cache']['plugin']['xj_event']['event_online_class']);
			$onlineclass = array();
			foreach($tmp as $key=>$value){
				$onlineclass[] = explode("|",$value);
			}
			$userfield = unserialize($_G['setting']['activityfield']);
			$activity['ufield']['userfield'] = array('realname','mobile');

			//获取模板
			$tmp = explode("\n",$_G['cache']['plugin']['xj_event']['event_template']);
			$event_template = array();
			foreach($tmp as $key=>$value){
				$ctmp = array();
				$ctmp = explode("|",$value);
				$ctmp[0] = str_replace("\r",'',$ctmp[0]);
				$ctmp[1] = str_replace("\r",'',$ctmp[1]);
				$event_template[] = $ctmp;
			}
			$forumlist = $this->forumselect(FALSE, 0, 0, TRUE);




			//二级地区分类调用
			if($_G['cache']['plugin']['xj_event']['event_city']){
				$lang_province = lang('spacecp', 'district_level_1');
				$lang_city = lang('spacecp', 'district_level_2');
				$province = DB::fetch_all("SELECT * FROM ".DB::table('common_district')." WHERE level = 1");
				$city = DB::fetch_all("SELECT * FROM ".DB::table('common_district')." WHERE level = 2 order by upid");
				$i = 0;
				$tempid = 1;
				foreach($city as $key=>$value){
					if($value['upid']<> $tempid){
						$i = 0;
						$tempid = $value['upid'];
					}
					$city[$key]['displayorder'] = $i;
					$i++;
				}
			}
;





    		include template('xj_event:post_event');
    		return $return;
	}
	function newthread_submit($fid, $tid) {
		global $_G;
		if(!in_array('mobile',$_GET['userfield']) && $_GET['seccode']==1){
			showmessage(lang('plugin/xj_event', 'qylbmdxbtzlxbx'));
		}
	}
	function newthread_submit_end($fid, $tid, $pid) {
		global $_G;
		$items = DB::fetch(DB::query("SELECT * FROM ".DB::table('forum_post')." WHERE tid = '$tid'"));
		$pid = $items['pid'];
		$postclass = intval($_GET['postclass']);
		$starttime = @strtotime($_GET['starttime']);
		$endtime = @strtotime($_GET['endtime']);
		$offlineclass = intval($_GET['offlineclass']);
		$onlineclass = intval($_GET['onlineclass']);
		$citys = addslashes($_GET['citys']);
		$event_address = addslashes($_GET['event_address']);
		$event_number = intval($_GET['event_number']);
		$event_number_man = intval($_GET['event_number_man']);
		$event_number_woman = intval($_GET['event_number_woman']);
		$event_number_max = intval($_GET['event_number_max']);
		$use_extcredits_num = intval($_GET['use_extcredits_num']);
		$use_extcredits = intval($_GET['use_extcredits']);
		$use_cost = doubleval($_GET['use_cost']);
		$reserve_cost = doubleval($_GET['reserve_cost']); //Enicn_d 2015-11-03 added reserve_cost
		$verify_by_pay = intval($_GET['verify_by_pay']); //Enicn_d 2015-11-03 added verify_by_pay
		$activityexpiration = @strtotime($_GET['activityexpiration']); //截止时间
		$activitybegin = @strtotime($_GET['activitybegin']); //截止时间
		foreach($_GET['userfield'] as $value){
			$tmp[] = addslashes($value);
		}
		$userfield = serialize($tmp);
		$setting['event_admin'] = explode(',',$_GET['event_admin']);
		$setting['noverify'] = intval($_GET['noverify']);
		$setting['statements'] = intval($_GET['statements']);
		$setting['eventzy_enable'] = intval($_GET['eventzy_enable']);
		$setting['eventzy_name'] = addslashes($_GET['eventzy_name']);
		$setting['eventzy_fid'] = intval($_GET['eventzy_fid']);
		$setting['eventzy_xz'] = intval($_GET['eventzy_xz']);
		$setting['eventpay'] = intval($_GET['eventpay']);
		$setting['eventaa'] = intval($_GET['eventaa']);
		$setting['seccode'] = intval($_GET['seccode']);
		$setting['reply'] = intval($_GET['reply']);
		//活动场次
		if($_GET['sessionstr']){
			$tmparray = explode("\r\n",$_GET['sessionstr']);
			$sessionarray = array();
			foreach($tmparray as $value){
				$tmpvalue = explode("=",$value);
				$sessionarray[$tmpvalue[0]] = $tmpvalue[1];
			}
			$setting['session'] = $sessionarray;
		}
		//更多选项
		foreach($_GET['setting_itemname'] as $key => $value){
			$tmparray = array();
			if($value){
				$tmparray['itemname'] = addslashes($value);
				$tmparray['itemcontent'] = addslashes($_GET['setting_itemcontent'][$key]);
				$setting['moreitem'][] = $tmparray;
			}
		}




		$setting_str = serialize($setting);

		$activityaid = intval($_GET['activityaid']);
		convertunusedattach($activityaid,$tid,$pid);
		$activityaid_url = addslashes($_GET['activityaid_url']);
		DB::query("INSERT INTO ".DB::table('xj_event')."
		(tid, postclass, starttime, endtime, offlineclass, onlineclass, citys, event_address, event_number, event_number_man, event_number_woman, event_number_max, use_extcredits_num, use_extcredits, use_cost, reserve_cost, activitybegin, activityexpiration, userfield, activityaid, activityaid_url,setting ,verify_by_pay)
		VALUES
		('$tid', '$postclass', '$starttime', '$endtime', '$offlineclass', '$onlineclass', '$citys', '$event_address', '$event_number', '$event_number_man', '$event_number_woman', '$event_number_max', '$use_extcredits_num', '$use_extcredits', '$use_cost', '$reserve_cost', '$activitybegin' , '$activityexpiration' ,'$userfield','$activityaid' ,'$activityaid_url','$setting_str' ,'$verify_by_pay') "); //Enicn_d 2015-11-03 added reserve_cost
		/**
		Enicn_D modified 2015-03-31 18:02
		*/
		$extcredits = $_G['setting']['extcredits'];
		$credit_rule = array_shift(C::t('common_credit_rule')->fetch_all_by_action('activity'));
		foreach ($extcredits as $extkey => $extval) {
			$extcredits_name = 'extcredits'.$extkey;
			$extcredits_value = $credit_rule[$extcredits_name];
			C::t('common_member_count')->increase(array($_G['uid']),array($extcredits_name => $extcredits_value));
		}
		require_once DISCUZ_ROOT.'./source/class/class_credit.php';
		credit::countcredit($_G['uid'],'update');
		/**
		Enicn_D modified 2015-03-31 18:02
		*/
	}
	function editpost($fid, $tid) {
		global $_G;
		$items = DB::fetch(DB::query("SELECT * FROM ".DB::table('xj_event')." WHERE tid = '$tid'"));
		$extcredits = $_G['setting']['extcredits'];
		$citys = explode("\r\n",$_G['cache']['plugin']['xj_event']['city']);
		$tmp = explode("\n",$_G['cache']['plugin']['xj_event']['event_offline_class']);
		$offlineclass = array();
		foreach($tmp as $key=>$value){
			$offlineclass[] = explode("|",$value);
		}
		$tmp = explode("\n",$_G['cache']['plugin']['xj_event']['event_online_class']);
		$onlineclass = array();
		foreach($tmp as $key=>$value){
			$onlineclass[] = explode("|",$value);
		}
		$starttime = dgmdate($items['starttime'],'dt');
		$endtime = dgmdate($items['endtime'],'dt');
		$activitybegin = dgmdate($items['activitybegin'],'dt');
		$activityexpiration = dgmdate($items['activityexpiration'],'dt');
		$selectuserfield = unserialize($items['userfield']);
		$userfield = unserialize($_G['setting']['activityfield']);
		$setting = unserialize($items['setting']);
		$setting['event_admin'] = implode(',',$setting['event_admin']);
		//活动场次
		$sessionarray = array();
		foreach($setting['session'] as $key=>$value){
			$sessionarray[] = $key.'='.$value;
		}
		$sessionstr = implode("\r\n",$sessionarray);

		if(!$items['activityaid'] and $items['activityaid_url']){
			$imgurl = $items['activityaid_url'];
		}else{
			$imgurl = getforumimg($items['activityaid'],0,360,230,'fixnone');
		}
		$forumlist = $this->forumselect(FALSE, 0, $setting['eventzy_fid'], TRUE);



		//二级地区分类调用
		if($_G['cache']['plugin']['xj_event']['event_city']){
			$lang_province = lang('spacecp', 'district_level_1');
			$lang_city = lang('spacecp', 'district_level_2');
			$province = DB::fetch_all("SELECT * FROM ".DB::table('common_district')." WHERE level = 1");
			$city = DB::fetch_all("SELECT * FROM ".DB::table('common_district')." WHERE level = 2 order by upid");
			$i = 0;
			$tempid = 1;

			if($items['citys']){
				$upid = intval(DB::result_first("SELECT upid FROM ".DB::table('common_district')." WHERE name = '".$items['citys']."'"));
				$items['province'] = DB::result_first("SELECT name FROM ".DB::table('common_district')." WHERE id = $upid");
			}



			foreach($city as $key=>$value){
				if($value['upid']<> $tempid){
					$i = 0;
					$tempid = $value['upid'];
				}
				$city[$key]['displayorder'] = $i;
				$i++;
			}
		}
		include template('xj_event:edit_event');
		return $return;
	}

	function editpost_submit($fid, $tid){
		global $_G;
		if(!in_array('mobile',$_GET['userfield']) && $_GET['seccode']==1){
			showmessage(lang('plugin/xj_event', 'qylbmdxbtzlxbx'));
		}
	}

	function editpost_submit_end($fid, $tid) {
		global $_G;
		$items = DB::fetch(DB::query("SELECT * FROM ".DB::table('forum_post')." WHERE tid = '$tid'"));
		$pid = $items['pid'];
		$postclass = intval($_GET['postclass']);
		$starttime = strtotime($_GET['starttime']);
		$endtime = strtotime($_GET['endtime']);
		$offlineclass = intval($_GET['offlineclass']);
		$onlineclass = intval($_GET['onlineclass']);
		$citys = addslashes($_GET['citys']);
		$event_address = addslashes($_GET['event_address']);
		$event_number = intval($_GET['event_number']);
		if($_GET['sexxz']){
			$event_number_man = intval($_GET['event_number_man']);
			$event_number_woman = intval($_GET['event_number_woman']);
		}else{
			$event_number_man = 0;
			$event_number_woman = 0;
		}
		;
		$event_number_max = intval($_GET['event_number_max']);
		$use_extcredits_num = intval($_GET['use_extcredits_num']);
		$use_extcredits = intval($_GET['use_extcredits']);
		$use_cost = doubleval($_GET['use_cost']);
		$reserve_cost = doubleval($_GET['reserve_cost']); //Enicn_d 2015-11-03 added reserve_cost
		$verify_by_pay = intval($_GET['verify_by_pay']); //Enicn_d 2015-11-03 added verify_by_pay
		$activitybegin = strtotime($_GET['activitybegin']);
		$activityexpiration = strtotime($_GET['activityexpiration']); //截止时间
		foreach($_GET['userfield'] as $value){
			$tmp[] = addslashes($value);
		}
		$userfield = serialize($tmp);
		//获取当前的设置
		$event = DB::fetch_first("SELECT setting FROM ".DB::table('xj_event')." WHERE tid='$tid'");
		$setting = unserialize($event['setting']);
		$setting['event_admin'] = explode(',',$_GET['event_admin']);
		$setting['noverify'] = intval($_GET['noverify']);
		$setting['statements'] = intval($_GET['statements']);
		$setting['eventzy_enable'] = intval($_GET['eventzy_enable']);
		$setting['eventzy_name'] = addslashes($_GET['eventzy_name']);
		$setting['eventzy_fid'] = intval($_GET['eventzy_fid']);
		$setting['eventzy_xz'] = intval($_GET['eventzy_xz']);
		$setting['eventpay'] = intval($_GET['eventpay']);
		$setting['eventaa'] = intval($_GET['eventaa']);
		$setting['seccode'] = intval($_GET['seccode']);
		$setting['reply'] = intval($_GET['reply']);
		//活动场次
		if($_GET['sessionstr']){
			$tmparray = explode("\r\n",$_GET['sessionstr']);
			$sessionarray = array();
			foreach($tmparray as $value){
				$tmpvalue = explode("=",$value);
				$sessionarray[$tmpvalue[0]] = $tmpvalue[1];
			}
			$setting['session'] = $sessionarray;
		}else{
			$setting['session'] = '';
		}
		//更多选项
		$setting['moreitem'] = array();
		foreach($_GET['setting_itemname'] as $key => $value){
			$tmparray = array();
			if($value){
				$tmparray['itemname'] = addslashes($value);
				$tmparray['itemcontent'] = addslashes($_GET['setting_itemcontent'][$key]);
				$setting['moreitem'][] = $tmparray;
			}
		}



		$setting_str = serialize($setting);
		//活动图片编辑处理
		$activity = DB::fetch(DB::query("SELECT activityaid FROM ".DB::table('xj_event')." WHERE tid = '$tid'"));
		$activityaid = $activity['activityaid'];
		if($activityaid && $activityaid != $_GET['activityaid']) {
				$attach = C::t('forum_attachment_n')->fetch('tid:'.$_G['tid'], $activityaid);
				C::t('forum_attachment')->delete($activityaid);
				C::t('forum_attachment_n')->delete('tid:'.$_G['tid'], $activityaid);
				dunlink($attach);
		}
		if($_GET['activityaid']) {
			$threadimageaid = intval($_GET['activityaid']);
			$activityaid = intval($_GET['activityaid']);
			convertunusedattach($_GET['activityaid'], $tid, $pid);
		}
		if(strpos($_GET['activityaid_url'],'ttp://')>0){
			$activityaid = 0;
		}

		$activityaid_url = addslashes($_GET['activityaid_url']);
		DB::query("UPDATE ".DB::table('xj_event')." set tid='$tid', postclass='$postclass', starttime='$starttime', endtime='$endtime', offlineclass='$offlineclass', onlineclass='$onlineclass', citys='$citys', event_address='$event_address', event_number='$event_number', event_number_man='$event_number_man', event_number_woman='$event_number_woman', event_number_max='$event_number_max', use_extcredits_num='$use_extcredits_num', use_extcredits='$use_extcredits', use_cost='$use_cost', reserve_cost='$reserve_cost', activitybegin='$activitybegin', activityexpiration='$activityexpiration', userfield='$userfield',activityaid='$activityaid' , activityaid_url='$activityaid_url',setting = '$setting_str', verify_by_pay='$verify_by_pay' WHERE tid = '{$tid}'");
	}


	function viewthread($tid) {
		global $_G;
		$timestamp = time();
		$extcredits = $_G['setting']['extcredits'];
		$items = DB::fetch(DB::query("SELECT * FROM ".DB::table('xj_event')." WHERE tid = '$tid'"));
		$setting = unserialize($items['setting']);
		//活动专题跳转判断
		if(!checkmobile()){
		  if($setting['project']['openproject'] && (($_G['groupid']>1 && $_G['uid'] != $items['uid']) || !$_G['uid'])){
			  if($setting['project']['openprojectauto']){
				  header("Location: plugin.php?id=xj_event:project_show&tid=$tid");
			  }
		  }
		}



		if($items['postclass']==1){
			$postclass = lang('plugin/xj_event', 'xxhd');
			$tmp = explode("\n",$_G['cache']['plugin']['xj_event']['event_offline_class']);
			foreach($tmp as $key=>$value){
				$eventclass = explode("|",$value);
				if($eventclass[0] == $items['offlineclass']){
					break;
				}
			}
		}else{
			$postclass = lang('plugin/xj_event', 'xshd');
			$tmp = explode("\n",$_G['cache']['plugin']['xj_event']['event_online_class']);
			foreach($tmp as $key=>$value){
				$eventclass = explode("|",$value);
				if($eventclass[0] == $items['onlineclass']){
					break;
				}
			}
		}
		foreach($extcredits as $key=>$value){
			if($key == $items['use_extcredits']){
				$extcredit_title = $value['title'];
			}
		}
		$citys = $items['citys'];
		$starttime = dgmdate($items['starttime'],'dt');
		$endtime = dgmdate($items['endtime'],'dt');
		$activityexpiration = dgmdate($items['activityexpiration'],'dt');
		if(!$items['activityaid'] and $items['activityaid_url']){
			$imgurl = $items['activityaid_url'];
		}else{
			//$imgurl = $this->_getpicurl($items['activityaid'],$tid);
			$imgurl = getforumimg($items['activityaid'],0,360,230,'fixnone');
		}
		$userfield = unserialize($items['userfield']);
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
								$ufielddata['userfield']['birthyear'] =  $memberprofile['birthyear'];
								$ufielddata['userfield']['birthmonth'] =  $memberprofile['birthmonth'];
							}
							$ufielddata['userfield'][$val] = $memberprofile[$val];
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





		$hg = DB::fetch_first("SELECT * FROM ".DB::table('xj_eventthread')." WHERE eid=".intval($items['eid'])." and sort=1");
		//报名通过总人数
		$applycountnumber = DB::result_first("SELECT SUM(applynumber) FROM ".DB::table('xj_eventapply')." WHERE tid='$tid' and verify=1");
		$applycountnumber = !$applycountnumber?0:$applycountnumber;
		$applycountnumberd = DB::result_first("SELECT SUM(applynumber) FROM ".DB::table('xj_eventapply')." WHERE tid='$tid' and verify=0");
		$applycountnumberd = !$applycountnumberd?0:$applycountnumberd;


		//报名时可能选择的人数
		$items['event_number_max'] = $items['event_number_max']>0?$items['event_number_max']:1;
		$applynumber = array();
		for($i=1;$i<=$items['event_number_max'];$i++){
			$applynumber[] = $i;
		}
		//报名审核状态
		$apply = DB::fetch_first("SELECT applyid,pay_state,verify,seccode FROM ".DB::table('xj_eventapply')." WHERE tid='$tid' and uid=".$_G['uid']);
		$verify = $apply['verify'];
		$pay_state = $apply['pay_state'];


		//判断是不是管理团队
		$event_admin = false;
		if($_G['username']){
		  if(in_array($_G['username'],$setting['event_admin'])){
			  $event_admin = true;
		  }
		}
		//活动管理列表
		$event_adminlist = implode(',',$setting['event_admin']);

		//回复才可以报名
		$bmbtnshow = true;
		if($setting['reply']){
			$replys = DB::result_first("SELECT count(*) FROM ".DB::table('forum_post')." WHERE tid='$tid' AND first<>1 AND invisible>=0 AND authorid = ".$_G['uid']);
			if($replys<1){
				$bmbtnshow = false;
			}
		}



		//二级地区分类调用
		if($_G['cache']['plugin']['xj_event']['event_city']){
			$lang_province = lang('spacecp', 'district_level_1');
			$lang_city = lang('spacecp', 'district_level_2');
			if($items['citys']){
				$upid = intval(DB::result_first("SELECT upid FROM ".DB::table('common_district')." WHERE name = '".$items['citys']."'"));
				if($upid){
					$items['province'] = DB::result_first("SELECT name FROM ".DB::table('common_district')." WHERE id = $upid");
				}
			}
		}



		include template('xj_event:viewthread_event');
    	return $return;
	}


	/**
	 * 通过aid获取图片链接
	 */
	 function _getpicurl($aid,$tid){
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


	function forumselect($groupselectable = FALSE, $arrayformat = 0, $selectedfid = 0, $showhide = FALSE, $evalue = FALSE, $special = 0) {
	global $_G;
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
				$forumlist .= '<option value="'.($evalue ? 'fid_' : '').$forum['fid'].'"'.$selected.'>'.$forum['name'].'</option>';
			}
			$visible[$forum['fid']] = true;
		} elseif($forum['type'] == 'sub' && isset($visible[$forum['fup']]) && (!$forum['viewperm'] || ($forum['viewperm'] && forumperm($forum['viewperm'])) || strstr($forum['users'], "\t$_G[uid]\t")) && (!$special || substr($forum['allowpostspecial'], -$special, 1))) {
			if($arrayformat) {
				$forumlist[$forumcache[$forum['fup']]['fup']]['child'][$forum['fup']][$forum['fid']] = $forum['name'];
			} else {
				$forumlist .= '<option value="'.($evalue ? 'fid_' : '').$forum['fid'].'"'.$selected.'>&nbsp; &nbsp; &nbsp; '.$forum['name'].'</option>';
			}
		}
	}
	if(!$arrayformat) {
		$forumlist .= '</optgroup>';
		$forumlist = str_replace('<optgroup label="&nbsp;"></optgroup>', '', $forumlist);
	}
	return $forumlist;
}

}

/*


*/
?>
