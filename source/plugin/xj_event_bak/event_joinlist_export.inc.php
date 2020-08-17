<?php
/**
 *	[超级活动(xj_event.{modulename})] (C)2012-2099 Powered by 逍遥工作室.
 *	Version: 1.0
 *	Date: 2012-9-15 10:27
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$filename = date('Ymd', TIMESTAMP).'.csv';
header('Content-Encoding: none');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename='.$filename);
header('Pragma: no-cache');
header('Expires: 0');





include DISCUZ_ROOT . './source/plugin/xj_event/include/func.php';
$tid = intval($_GET['tid']);

//权限限制
$thread = DB::fetch_first("SELECT authorid,userfield,setting FROM ".DB::table('forum_thread')." A,".DB::table('xj_event')." B WHERE A.tid='$tid' and A.tid = B.tid");
$setting = unserialize($thread['setting']);
if($_G['groupid']>1 && $_G['uid']!=$thread['authorid']){
	showmessage('quickclear_noperm');
}









//活动报名字段
$selectuserfield = unserialize($thread['userfield']);
$sysuserfield = unserialize($_G['setting']['activityfield']);


$query = DB::query("SELECT B.username,A.applynumber,A.verify,A.ufielddata,A.bmmessage,A.dateline,A.session FROM ".DB::table('xj_eventapply')." A,".DB::table('common_member')." B WHERE A.uid = B.uid and A.tid = '$tid' ORDER BY A.verify,A.dateline DESC");
require_once libfile('function/profile');
loadcache('profilesetting');
$i=1;

$detail = lang('plugin/xj_event', 'xuhao').",".lang('plugin/xj_event', 'yonghuming').",";
if($setting['session']){
	$detail = $detail.lang('plugin/xj_event', 'huodongcc').",";
}

foreach($selectuserfield as $val){
	if($sysuserfield[$val]){
		$detail = $detail.$sysuserfield[$val].",";
	}
}
$detail = $detail.lang('plugin/xj_event', 'baomingrs').",".'message'.",".lang('plugin/xj_event', 'baomingsj')."\n";



while($value = DB::fetch($query)){
	$value['No'] = $i;
	$value['dateline'] = date('Y-m-d H:i:s',$value['dateline']);
	
	$detail = $detail.$value['No'].",".$value['username'].",";
	if($setting['session']){
		$detail = $detail.$setting['session'][$value['session']].",";
	}
	
	$value['ufielddata'] = unserialize($value['ufielddata']);
	$data = '';
	$ufielddata = array();
	foreach($value['ufielddata'] as $key => $fieldid) {
		$data = profile_show($key, $value['ufielddata']);
		if($_G['cache']['profilesetting'][$key]['formtype'] == 'file') {
			$data = '<a href="'.$data.'" target="_blank" onclick="zoom(this, this.href, 0, 0, 0); return false;">'.lang('forum/misc', 'activity_viewimg').'</a>';
		}
		if($key=='birthday'){
			$data = $fieldid;
		}
		if($key=='qq'){
			$data = $fieldid;
		}
		$ufielddata[$key]['value'] = $data;
		$value[$key] = str_replace(',',' ',$data);
$data = str_replace("\r",'',$data);
$data = str_replace("\n",'',$data);
		$detail = $detail."\t".str_replace(',',' ',$data).",";
	}
	//print_r($ufielddata);
	//$value['ufielddata'] = $ufielddata;
	$detail = $detail.$value['applynumber'].",".$value['bmmessage'].",";
	$detail = $detail.$value['dateline'];

	$detail = $detail."\n";
	$i++;
}



if($_G['charset'] != 'gbk') {
	$detail = diconv($detail, $_G['charset'], 'GBK');
}



echo $detail;
exit();

?>