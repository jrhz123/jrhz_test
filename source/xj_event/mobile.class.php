<?php
/**
 * [超级活动(xj_event.{modulename})] (C)2012-2099 Powered by 逍遥工作室.
 * 	Version: 1.0
 * 	Date: 2012-9-15 10:27
 */

if (!defined('IN_DISCUZ')) {
	exit('Access Denied');
} 
class mobileplugin_xj_event {

	function viewthread_posttop_mobile(){
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
				$return[0] = $return[0].'<div style="margin:30px; text-align:center;"><a href="plugin.php?id=xj_event:event_vote&tid='.$tid.'&eid='.$eid.'" onclick="ajaxmenu(this, 3000, 0, 0, \'43\');return false;" style=" display:inline-block;  width:186px; height:81px; background: url(source/plugin/xj_event/images/tp_btn.png); line-height:22px;"></a></div>';
			}
			$return[0] = $return[0].'</div>';
		}
		return $return;
	}
	
	function post_bottom_mobile(){
		global $_G;
		if($_GET['specialextra']!='xj_event'){
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
		return $return;
	}

} 

class mobileplugin_xj_event_forum extends mobileplugin_xj_event {
		function post_xj_event_reply_output($a) {
			global $_G,$postinfo;
			if($_GET['action']=='reply'){
				$postinfo['message'] = '';
			}
		}
		
		function post_xj_event_message($a) {
				global $_G;
				if($a['param']['0'] == 'post_newthread_succeed' || $a['param']['0'] == 'post_newthread_mod_succeed') {
					$tid = $a['param'][2]['tid'];
					if($_GET['eid']>0){
						$sort = intval($_GET['et_sort']);	
						$eid = intval($_GET['eid']);
						$fid = intval($_G['fid']);
						
						$aid = DB::result_first("SELECT aid FROM ".DB::table('forum_attachment')." WHERE tid=$tid");
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
							if(mbeventsetthreadcover($a['param'][2]['pid'],$a['param'][2]['tid'],$aid,0,'',$imgtype,$fid)){
								$coverurl = $basedir.'forum/'.$coverdir.$tid.'-event.jpg';
							}
						}else{
							preg_match("/\[img(.*)\](.+?)\[\/img\]/is",$_G['gp_message'],$match);
							if(mbeventsetthreadcover($a['param'][2]['pid'],$a['param'][2]['tid'],0,0,$match[2],$imgtype,$fid)){
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
								if(mbeventsetthreadcover($a['param'][2]['pid'],$a['param'][2]['tid'],$aid,0,'',$imgtype,$fid)){
									$coverurl = $basedir.'forum/'.$coverdir.$tid.'-event.jpg';
								}
							}else{
								preg_match("/\[img(.*)\](.+?)\[\/img\]/is",$_G['gp_message'],$match);
								if(mbeventsetthreadcover($a['param'][2]['pid'],$a['param'][2]['tid'],0,0,$match[2],$imgtype,$fid)){
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



function mbeventsetthreadcover($pid, $tid = 0, $aid = 0, $countimg = 0, $imgurl = '',$imgtype = 1,$fid) { 
	global $_G;
	$cover = 0;
	//图片大小
	$imgheight = 165;
	$imgwidth = 165;

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