<?php
if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

include DISCUZ_ROOT . './source/plugin/xj_event/include/sms_func.php';



if($_GET['act']=='save'){
	$receivenumber = array();
	$receivenumber[] = $_GET['receivenumber'];
	$sendcontent = $_GET['sendcontent'];
	$message = xjsendsms($receivenumber,$sendcontent,lang('plugin/xj_event', 'test'));
	if($message == 'ok'){
		$message = lang('plugin/xj_event', 'fascg');
	}
	cpmsg($message,'action=plugins&operation=config&do='.$pluginid.'&identifier=xj_event&pmod=admin_sms','succeed');
}

if($_GET['act'] == 'regsave'){
	$username = addslashes($_POST['username']);
	$password = addslashes($_POST['password']);
	$realname = addslashes($_POST['realname']);
	$mobile = addslashes($_POST['mobile']);
	$email = addslashes($_POST['email']);
	$message = regsms();
	if($message == 'ok'){
		$message = lang('plugin/xj_event', 'zccgqzcjszl');
	}elseif($message == 'repeat'){
		$message = lang('plugin/xj_event', 'qinwcfzc');
	}else{
		$message = lang('plugin/xj_event', 'zcsbqjcxxsftx');
	}
	
	cpmsg($message,'action=plugins&operation=config&do='.$pluginid.'&identifier=xj_event&pmod=admin_sms&menu=reg','succeed');
}
if($_GET['act'] == 'setsave'){
	if($_GET['formhash'] != $_G['formhash']) {
		cpmsg('parameters_error','action=plugins&operation=config&do='.$pluginid.'&identifier=xj_event&pmod=admin_sms','succeed');
	}
	$username = addslashes($_POST['username']);
	$password = addslashes($_POST['password']);
	$signature = addslashes($_POST['signature']);
	
	writetocache('xjsms',getcachevars(array('xjsms'=>array('username'=>$username,'password'=>$password,'signature'=>$signature))));
	$message = lang('plugin/xj_event', 'bccg');
	
	cpmsg($message,'action=plugins&operation=config&do='.$pluginid.'&identifier=xj_event&pmod=admin_sms&menu=set','succeed');
}



shownav('plugin', lang('plugin/xj_event', 'chaojhd'), lang('plugin/xj_event', 'duanxsz'));
showsubmenu(lang('plugin/xj_event', 'duanxsz'), array(
	array(lang('plugin/xj_event', 'fasongceshi'), 'plugins&operation=config&do='.$pluginid.'&identifier=xj_event&pmod=admin_sms', empty($_GET['menu'])?1:0),
	array(lang('plugin/xj_event', 'fasongjilu'), 'plugins&operation=config&do='.$pluginid.'&identifier=xj_event&pmod=admin_sms&menu=log', $_GET['menu']=='log'?1:0),
	array(lang('plugin/xj_event', 'duanxinyue'), 'plugins&operation=config&do='.$pluginid.'&identifier=xj_event&pmod=admin_sms&menu=overage', $_GET['menu']=='overage'?1:0),
	array(lang('plugin/xj_event', 'zhanghaoshezhi'), 'plugins&operation=config&do='.$pluginid.'&identifier=xj_event&pmod=admin_sms&menu=set', $_GET['menu']=='set'?1:0),
	array(lang('plugin/xj_event', 'pintaizhuce'), 'plugins&operation=config&do='.$pluginid.'&identifier=xj_event&pmod=admin_sms&menu=reg', $_GET['menu']=='reg'?1:0),
));


if($_GET['menu'] == 'test' || empty($_GET['menu'])){

showformheader('plugins&operation=config&do='.$pluginid.'&identifier=xj_event&pmod=admin_sms&act=save');
showtableheader(lang('plugin/xj_event', 'qzcjszlszhdxpt'));
showsetting(lang('plugin/xj_event', 'jieshouhaoma'), 'receivenumber', lang('plugin/xj_event', 'sursjh'), 'text');
showsetting(lang('plugin/xj_event', 'fasongleirong'), 'sendcontent', lang('plugin/xj_event', 'zhesytcsdx'), 'textarea');
showsubmit('submit',lang('plugin/xj_event', 'ceshifasong'));
showtablefooter();
showformfooter();

}elseif($_GET['menu'] == 'log'){
	
showtableheader(lang('plugin/xj_event', 'fasongjilu'));
showtablerow('', array('class="td25"', 'class="td28"'), array(lang('plugin/xj_event', 'select'),lang('plugin/xj_event', 'leixin'),lang('plugin/xj_event', 'haomashu'),lang('plugin/xj_event', 'fasongshijian'),lang('plugin/xj_event', 'fasongleirong'),lang('plugin/xj_event', 'state'),lang('plugin/xj_event', 'action')));
$ppp = 15;   //每天数量
$page = $_GET['page']?intval($_GET['page']):1;
$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('xj_event_sms_log'));
$query = DB::query("SELECT * FROM ".DB::table('xj_event_sms_log')." ORDER BY sendtime DESC LIMIT ".(($page - 1) * $ppp).",$ppp");
while($value = DB::fetch($query)) {
	$value['sendtime'] = dgmdate($value['sendtime']);
	showtablerow('', array('class="td25"', 'class="td28"'), array('<input type="checkbox" class="checkbox" name="delete[]" value="'.$value['id'].'" />',
	$value['sendtype'],$value['sendcount'],$value['sendtime'],$value['sendcontent'],$value['sendstate'],""));
}
showtablefooter();
echo multi($count, $ppp, $page, ADMINSCRIPT."?action=plugins&operation=config&do=$pluginid&identifier=xj_event&pmod=admin_sms&menu=log$extra");
	
}elseif($_GET['menu'] == 'overage'){
	$overage = getsmsoverage();
	echo lang('plugin/xj_event', 'ningddxye')." <span style='color:#f00;'>$overage</span> ".lang('plugin/xj_event', 'tiao')."  [<a href='http://www.my8888.cn/plugin.php?id=xj_sms:recharge' target='_blank'>".lang('plugin/xj_event', 'lijichongzhi')."</a>]";
}elseif($_GET['menu'] == 'reg'){
	showtableheader(lang('plugin/xj_event', 'duanxptzc'));
	showformheader('plugins&operation=config&do='.$pluginid.'&identifier=xj_event&pmod=admin_sms&act=regsave');
	showsetting(lang('plugin/xj_event', 'yonghuming'), 'username', '', 'text','','',lang('plugin/xj_event', 'dxptzcyhmqyyw'));
	showsetting(lang('plugin/xj_event', 'password'), 'password', '', 'password','','',lang('plugin/xj_event', 'duanxptmm'));
	showsetting(lang('plugin/xj_event', 'zhengshixinmin'), 'realname', '', 'text','','',lang('plugin/xj_event', 'srndzsxmfbhnlx'));
	showsetting(lang('plugin/xj_event', 'shoujihaoma'), 'mobile', '', 'text','','',lang('plugin/xj_event', 'lianxysjhm'));
	showsetting(lang('plugin/xj_event', 'email'), 'email', '', 'text','','',lang('plugin/xj_event', 'lianxyyx'));
	showsubmit('submit',lang('plugin/xj_event', 'reg'));
	showtablefooter();
	showformfooter();
}elseif($_GET['menu'] == 'set'){
	showtableheader(lang('plugin/xj_event', 'duanxptsz'));
	showformheader('plugins&operation=config&do='.$pluginid.'&identifier=xj_event&pmod=admin_sms&act=setsave');
	showsetting(lang('plugin/xj_event', 'yonghuming'), 'username', $xjsms['username'], 'text','','',lang('plugin/xj_event', 'duanxptyhm'));
	showsetting(lang('plugin/xj_event', 'password'), 'password', $xjsms['password'], 'password','','',lang('plugin/xj_event', 'duanxptmm'));
	showsetting(lang('plugin/xj_event', 'duanxqm'), 'signature', $xjsms['signature'], 'text','','',lang('plugin/xj_event', 'rtbwqmbahfs'));
	showsubmit('submit',lang('plugin/xj_event', 'save'));
	showtablefooter();
	showformfooter();
}

?>