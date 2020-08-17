<?php
/**
 *	[超级活动(xj_event.{modulename})] (C)2012-2099 Powered by 逍遥工作室.
 *	Version: 1.0
 *	Date: 2012-9-15 10:27
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
$eid=intval($_GET['eid']);
$tid=intval($_GET['tid']);
$fid=intval($_GET['fid']);

if($_GET['formhash'] != $_G['formhash']){
		showmessage('submit_invalid');
}

if($_GET['act']==2){
	DB::query("DELETE FROM ".DB::table('xj_eventthread')." WHERE eid='$eid' and tid='$tid'");
}else{
	$coverurl = '';
	$sort = 0;
	$post = DB::fetch_first("SELECT pid,aid FROM ".DB::table('forum_attachment')." WHERE tid=$tid ORDER BY aid");
	if($post){
		if(eventsetthreadcover($post['pid'],$tid,$post['aid'],0,'',1,$fid)){
			$basedir = !$_G['setting']['attachurl'] ? ('data/attachment/') : $_G['setting']['attachurl'];
			$coverdir = 'threadcover/'.substr(md5($tid), 0, 2).'/'.substr(md5($tid), 2, 2).'/';
			$coverurl = $basedir.'forum/'.$coverdir.$tid.'-event.jpg';
		}
	}
	DB::query("INSERT INTO ".DB::table('xj_eventthread')." (eid,tid,fid,coverurl,sort) VALUES ('$eid','$tid','$fid','$coverurl','$sort')");
}


function eventsetthreadcover($pid, $tid = 0, $aid = 0, $countimg = 0, $imgurl = '',$imgtype = 1,$fid) { 
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