<?php
/**
 *	[超级活动(xj_event.{modulename})] (C)2012-2099 Powered by 逍遥工作室.
 *	Version: 1.0
 *	Date: 2012-9-15 10:27
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
class plugin_xj_event {
	//TODO - Insert your code here
	function global_header(){
		global $_G;
		if($_GET['id'] == 'xj_event:event_center'){;
			$_G['setting']['seohead'] = $_G['setting']['seohead'].'    <script src="http://sns.huizhou.cn/source/plugin/xj_event/module/center6/js/jquery1.42.min.js" type="text/javascript"></script>
			  <script type="text/javascript">
				  var jq=jQuery.noConflict();
			  </script><script type="text/javascript" src="http://sns.huizhou.cn/source/plugin/xj_event/module/center6/js/jquery.more.js"></script> ';
		}
		return $return;
	}
	
	/*在用户登录的下拉框下添加 我的活动 */
	function global_myitem_extra(){
		global $_G;
	    return '<a href="xj_event-event_my.html" target="_blank">活动</a>';
    }

	
	function viewthread_sidetop_output(){
		global $_G,$postlist;
		$return = array();
		if($_G['cache']['plugin']['xj_event']['event_infoshow']){
			$i = 0;
			foreach($postlist as $key => $value){
				$value['authorid'] = intval($value['authorid']);
				$cjnum = DB::result_first("SELECT COUNT(*) FROM ".DB::table('xj_eventapply')." WHERE uid=".$value['authorid']);
				$zznum = DB::result_first("SELECT COUNT(*) FROM ".DB::table('xj_event')." A,".DB::table('forum_thread')." B WHERE A.tid=B.tid and B.authorid=".$value['authorid']);
				$return[$i] = '<p class="cp_pls cl">'.lang('plugin/xj_event', 'cjhd').':<span class="xi1">'.$cjnum.'</span> '.lang('plugin/xj_event', 'ci').'</p><p class="cp_pls cl">'.lang('plugin/xj_event', 'zzhd').':<span class="xi1">'.$zznum.'</span> '.lang('plugin/xj_event', 'ci').'</p>';
				$i++;
			}
		}
		return $return;
	}

	function forumdisplay_filter_extra(){
		return '<span class="pipe">|</span><a href="plugin.php?id=xj_event:event_list&fid='.$_GET['fid'].'" class="xi2">'.lang('plugin/xj_event', 'huodong').'</a>';
	}
	
	function deletethread($a){
		global $_G;
		if($a['step']=='delete'){
			$deltid = implode(',',$a['param'][0]);
			DB::query("DELETE FROM ".DB::table('xj_event')." WHERE tid in(".$deltid.")");
			DB::query("DELETE FROM ".DB::table('xj_eventthread')." WHERE tid in(".$deltid.")");
		}
		return;
	}
	
	function forumdisplay_thread_subject_output(){
		global $_G,$threadlist;
		$nowtime = time();
		$return = array();
		$items = array();
		foreach($threadlist as $key=>$value){
			$et = DB::fetch_first("SELECT * FROM ".DB::table('xj_eventthread')." WHERE tid=".$value['tid']);
			if($et){
				if($et['sort']==1){
					$items[$key] = '['.lang('plugin/xj_event', 'hdhg').']';
				}else{
					$items[$key] = '<img src="http://sns.huizhou.cn/source/plugin/xj_event/images/zy.png" alt="event_zy" title="'.lang('plugin/xj_event', 'hdzy').'" align="absmiddle" />';
				}
			}
			$event = DB::fetch_first("SELECT * FROM ".DB::table('xj_event')." WHERE tid=".$value['tid']);
			if($event){
				if($event['starttime']>$nowtime){
					$items[$key] = '<img src="http://sns.huizhou.cn/source/plugin/xj_event/images/hd_ico2.png" align="absmiddle">';
				}
				if($event['endtime']<$nowtime){
					$items[$key] = '<img src="http://sns.huizhou.cn/source/plugin/xj_event/images/hd_ico3.png" align="absmiddle">';
				}
				if($nowtime>$event['starttime'] && $nowtime<$event['endtime']){
					$items[$key] = '<img src="http://sns.huizhou.cn/source/plugin/xj_event/images/hd_ico1.png" align="absmiddle">';
				}
			
				$setting = unserialize($event['setting']);
				$applys = DB::result_first("SELECT SUM(applynumber) FROM ".DB::table('xj_eventapply')." WHERE tid=".$value['tid']);
				$applys = $applys?$applys:0;
				$items[$key] = $items[$key].' <a href="forum.php?mod=viewthread&tid='.$value['tid'].'&menu=3" class="xi2">'.lang('plugin/xj_event', 'baomin').'(<b>'.$applys.'</b>)</a>';
				if($setting['eventzy_enable']){
					$threads = DB::result_first("SELECT COUNT(*) FROM ".DB::table('xj_event')." A,".DB::table('xj_eventthread')." B WHERE A.eid=B.eid and B.sort=0 and A.tid=".$value['tid']);
					$items[$key] = $items[$key].' <a href="forum.php?mod=viewthread&tid='.$value['tid'].'&menu=2" class="xi2">'.$setting['eventzy_name'].'(<b>'.$threads.'</b>)</a>';
				}
			}
		}
		$return = $items;
		return $return;
	}
	
	function post_top(){
		global $_G;
		$return = '';
		if($_GET['specialextra']!='xj_event'){
			if($_GET['sort']==1){
				$thread = DB::fetch_first("SELECT authorid,setting FROM ".DB::table('forum_thread')." A,".DB::table('xj_event')." B WHERE B.eid=".$_GET['eid']." and A.tid = B.tid");
				$setting = unserialize($thread['setting']);
				//判断是不是管理团队
				$event_admin = false;
				if(in_array($_G['username'],$setting['event_admin'])){
					$event_admin = true;
				}
				if($_G['groupid']>1 && $_G['uid']!=$thread['authorid'] && !$event_admin){
					showmessage('quickclear_noperm');
				}
				$return = '<input type="hidden" name="et_sort" id="et_sort" value="1" /><input type="hidden" name="eid" id="eid" value="'.$_GET['eid'].'" />';
			}else{
				if($_GET['action']!='albumphoto'){
					if($_GET['action']=='edit'){
						$eid = DB::result_first("SELECT eid FROM ".DB::table('xj_eventthread')." WHERE tid=".$_GET['tid']);
					}
				
				
				
					$i = 0;
					$uid = $_G['uid'];
					$nowtime = time();
					$return = '<div style="clear:both; line-height:30px; padding-left:10px; padding-bottom:10px;">'.lang('plugin/xj_event', 'xghd').': <select name="eid" id="select"><option value=\"'.$eid.'\">'.lang('plugin/xj_event', 'qxzhd').'</option>';
					$query = DB::query("SELECT A.eid,A.setting,C.subject FROM ".DB::table('xj_event')." A,".DB::table('xj_eventapply')." B,".DB::table('forum_thread')." C WHERE C.tid=A.tid and A.tid=B.tid and B.uid={$uid} and B.verify=1 and A.postclass=1 and (A.endtime+2592000)>$nowtime");
					$return = $return."<optgroup label='".lang('plugin/xj_event', 'xxhd')."'>";
					while($value = DB::fetch($query)){
						$setting = unserialize($value['setting']);
						if($setting['eventzy_enable']){
							if($setting['eventzy_fid']==$_G['fid'] || !$setting['eventzy_fid']){
								$return = $return."<option value=\"$value[eid]\" ".($value['eid']==$_GET['eid']?"selected":"").">$value[subject]</option>";
								$i++;
							}
						}	
					}
					$query = DB::query("SELECT A.eid,A.setting,C.subject FROM ".DB::table('xj_event')." A,".DB::table('xj_eventapply')." B,".DB::table('forum_thread')." C WHERE C.tid=A.tid and A.tid=B.tid and B.uid={$uid} and B.verify=1 and A.postclass=2 and A.endtime>$nowtime");
					$return = $return."<optgroup label='".lang('plugin/xj_event', 'xshd')."'>";
					while($value = DB::fetch($query)){
						$setting = unserialize($value['setting']);
						if($setting['eventzy_enable']){
							if($setting['eventzy_fid']==$_G['fid'] || !$setting['eventzy_fid']){
							  $return = $return."<option value=\"$value[eid]\" ".($value['eid']==$_GET['eid']?"selected":"").">$value[subject]</option>";
							  $i++;
							}
						}
					}
					$return .= "</select> <span style='color:#D0D0D0;'>".lang('plugin/xj_event', 'xzncjdhdfbhdzy')."</span></div>";
					
					if($i==0){  //判断是否有加参正在进行中的活动，没有就不调用
						$return = '';
					}
				}
			}
		}
		return $return;
	}
	function viewthread_posttop(){
		global $_G;
		$return = array();
		$tid = intval($_G['tid']);
		$num = DB::result_first("SELECT COUNT(*) FROM ".DB::table('xj_eventthread')." WHERE tid=".$tid);
		if($num>0){
			$items = DB::fetch_first("SELECT A.eid,B.setting,C.tid,C.subject FROM ".DB::table('xj_eventthread')." A,".DB::table('xj_event')." B,".DB::table('forum_thread')." C WHERE A.tid=".$_G['tid']." and A.eid=B.eid and B.tid=C.tid");
			$nowtime = $_G["timestamp"];
			$setting = unserialize($items['setting']);
			$eid=$items['eid'];
			$tid=intval($_G['tid']);
			$return[0] = '<div style=" line-height:30px;">'.lang('plugin/xj_event', 'btlzyhd').':<img src="static/image/common/activitysmall.gif" align="absmiddle"> <a href="forum.php?mod=viewthread&tid='.$items['tid'].'" style=" font-weight:bold; color:#ff4200;">'.$items['subject'].'</a>';
			if($setting[vote][openvote]==1 && $nowtime>$setting['vote']['votestarttime'] && $nowtime<$setting['vote']['voteendtime']){
				$return[0] = $return[0].'<div style="margin:30px; text-align:center;"><a href="plugin.php?id=xj_event:event_vote&tid='.$tid.'&eid='.$eid.'&formhash='.$_G['formhash'].'" onclick="ajaxmenu(this, 3000, 0, 0, \'43\');return false;" style=" display:inline-block;  width:186px; height:81px; background: url(http://sns.huizhou.cn/source/plugin/xj_event/images/tp_btn.png); line-height:22px;"></a></div>';
			}
			$return[0] = $return[0].'</div>';
		}
		return $return;
	}
	
	function space_profile_extrainfo(){
		global $_G;
		$return = '';
		$uid = intval($_GET['uid']);
		$items = DB::fetch_first("SELECT * FROM ".DB::table('xj_event_member_info')." WHERE uid=$uid");
		
		include template('xj_event:event_userinfo');
		return $return;
	}
	
}

class plugin_xj_event_home extends plugin_xj_event{
	
}

class plugin_xj_event_forum extends plugin_xj_event{

	function viewthread_xj_event_output($a){
		global $_G,$postlist,$imgurl;
		$tid = intval($_GET['tid']);
		$num = DB::result_first("SELECT COUNT(*) FROM ".DB::table('xj_event')." WHERE tid=".$tid);
		if($num>0){
			$myaid = DB::result_first("SELECT activityaid FROM ".DB::table('xj_event')." WHERE tid=".$tid);
			$pids = array_keys($postlist);
			foreach($postlist[$pids[0]][imagelist] as $key => $value){
				if($postlist[$pids[0]][imagelist][$key] == $myaid){
					$postlist[$pids[0]][imagelist][$key] = 0;
				}
				
			}
		}
		//$postlist[$pids[0]][imagelist][0] = array();
		//unset($postlist[$pids[0]][imagelist][0]);
	}

	//主题列表页输出
	function forumdisplay_xj_event_output($a) {
		global $_G;
		//print_r($_G['forum_threadlist'][1]);
		foreach($_G['forum_threadlist'] as $key=>$value){
			$num = DB::result_first("SELECT COUNT(*) FROM ".DB::table('xj_event')." WHERE tid=".$value['tid']);
			if($num>0){
				$_G['forum_threadlist'][$key]['special'] = 4;
			}
		}
	}
	
	function post_xj_event(){
		global $_G;
		if(intval($_GET['eid'])>0){
			$eid = intval($_GET['eid']);
			$items = DB::fetch_first("SELECT setting FROM ".DB::table('xj_event')." WHERE eid=$eid");
			$setting = unserialize($items['setting']);
			$zycount = DB::result_first("SELECT count(*) FROM  ".DB::table('xj_eventthread')." A,".DB::table('forum_thread')." B WHERE A.tid=B.tid AND A.eid=$eid AND B.authorid=".$_G['uid']);
			if($zycount>$setting['eventzy_xz'] && $setting['eventzy_xz']){
				showmessage(lang('plugin/xj_event', 'nfbdhd').$setting['eventzy_name'].lang('plugin/xj_event', 'slyjcgxzwfzfb'));
			}	
		}
		return;
	}
	
	
	function post_xj_event_message($a) {
		global $_G;
		if($a['param']['0'] == 'post_newthread_succeed' || $a['param']['0'] == 'post_newthread_mod_succeed') {
			$tid = $a['param'][2]['tid'];
			if($_GET['eid']>0){
				$sort = intval($_GET['et_sort']);	
				$eid = intval($_GET['eid']);
				$fid = intval($_G['fid']);

				//生成缩略图
				$imgtype = 1;
				$attach = array_keys($_G['gp_attachnew']);
				foreach($_G['gp_attachnew'] as $key => $value){
					if(count($value) == 1){
						$aid = $key;
						break;
					}				
				}
				$basedir = !$_G['setting']['attachurl'] ? ('data/attachment/') : $_G['setting']['attachurl'];
		        $coverdir = 'threadcover/'.substr(md5($tid), 0, 2).'/'.substr(md5($tid), 2, 2).'/';
				if($aid){  //生成缩略图
					if(eventsetthreadcover($a['param'][2]['pid'],$a['param'][2]['tid'],$aid,0,'',$imgtype,$fid)){
						$coverurl = $basedir.'forum/'.$coverdir.$tid.'-event.jpg';
					}
				}else{
					preg_match("/\[img(.*)\](.+?)\[\/img\]/is",$_G['gp_message'],$match);
					if(eventsetthreadcover($a['param'][2]['pid'],$a['param'][2]['tid'],0,0,$match[2],$imgtype,$fid)){
						$coverurl = $basedir.'forum/'.$coverdir.$tid.'-event.jpg';
					}
				}
				DB::query("INSERT INTO ".DB::table('xj_eventthread')." (eid,tid,fid,coverurl,sort) VALUES ('$eid','$tid','$fid','$coverurl','$sort')");
			}
		}
		if($a['param']['0'] =='post_edit_succeed'){
			$tid = $a['param'][2]['tid'];
			if($_GET['eid']>0){
				//获取此贴是否已关联其它活动
				$num = DB::result_first("SELECT COUNT(*) FROM ".DB::table('xj_eventthread')." WHERE tid='$tid'");
				if($num>0){
					$eid = intval($_GET['eid']);
					DB::query("UPDATE ".DB::table('xj_eventthread')." SET eid='$eid' WHERE tid = '$tid'");
				}else{
					$sort = intval($_GET['et_sort']);	
					$eid = intval($_GET['eid']);
					$fid = intval($_G['fid']);
					//生成缩略图
					$imgtype = 1;
					$attach = array_keys($_G['gp_attachnew']);
					foreach($_G['gp_attachnew'] as $key => $value){
						if(count($value) == 1){
							$aid = $key;
							break;
						}				
					}
					$basedir = !$_G['setting']['attachurl'] ? ('data/attachment/') : $_G['setting']['attachurl'];
					$coverdir = 'threadcover/'.substr(md5($tid), 0, 2).'/'.substr(md5($tid), 2, 2).'/';
					if($aid){  //生成缩略图
						if(eventsetthreadcover($a['param'][2]['pid'],$a['param'][2]['tid'],$aid,0,'',$imgtype,$fid)){
							$coverurl = $basedir.'forum/'.$coverdir.$tid.'-event.jpg';
						}
					}else{
						preg_match("/\[img(.*)\](.+?)\[\/img\]/is",$_G['gp_message'],$match);
						if(eventsetthreadcover($a['param'][2]['pid'],$a['param'][2]['tid'],0,0,$match[2],$imgtype,$fid)){
							$coverurl = $basedir.'forum/'.$coverdir.$tid.'-event.jpg';
						}
					}
					DB::query("INSERT INTO ".DB::table('xj_eventthread')." (eid,tid,fid,coverurl,sort) VALUES ('$eid','$tid','$fid','$coverurl','$sort')");
				}
			}
		}
		
		return;
	}
}
/*
class plugin_xj_event_home extends plugin_xj_event {


	function space_profile_extrainfo(){
		global $_G;
		return '22222222222222';
	}

	function medal_nav_extra(){
		global $_G;
		echo '11111111';
		return '22222222222222';
	}

}
*/



function eventsetthreadcover($pid, $tid = 0, $aid = 0, $countimg = 0, $imgurl = '',$imgtype = 1,$fid) { 
	global $_G;
	$cover = 0;
	//图片大小
	$imgheight = 220;
	$imgwidth = 220;

	if(empty($_G['uid']) || !intval($imgheight) || !intval($imgwidth)) {
		return false;
	}

	if(($pid || $aid) && empty($countimg)) {
		if(empty($imgurl)) {
			if($aid) {
				$attachtable = 'aid:'.$aid;
				$attach = C::t('forum_attachment_n')->fetch('aid:'.$aid, $aid, array(1, -1));
			} else {
				$attachtable = 'pid:'.$pid;
				$attach = C::t('forum_attachment_n')->fetch_max_image('pid:'.$pid, 'pid', $pid);
			}
			if(!$attach) {
				return false;
			}
			if(empty($_G['forum']['ismoderator']) && $_G['uid'] != $attach['uid']) {
				return false;
			}
			$pid = empty($pid) ? $attach['pid'] : $pid;
			$tid = empty($tid) ? $attach['tid'] : $tid;
			$picsource = ($attach['remote'] ? $_G['setting']['ftp']['attachurl'] : $_G['setting']['attachurl']).'forum/'.$attach['attachment'];
		} else {
			$attachtable = 'pid:'.$pid;
			$picsource = $imgurl;
		}

		$basedir = !$_G['setting']['attachdir'] ? (DISCUZ_ROOT.'./data/attachment/') : $_G['setting']['attachdir'];
		$coverdir = 'threadcover/'.substr(md5($tid), 0, 2).'/'.substr(md5($tid), 2, 2).'/';
		dmkdir($basedir.'./forum/'.$coverdir);

		require_once libfile('class/image');
		$image = new image();
		if($image->Thumb($picsource, 'forum/'.$coverdir.$tid.'-event.jpg', $imgwidth, $imgheight, 2)) {
			return true;
		} else {
			return false;
		}
	}
}

?>