<?php
/**
 *	[超级活动(xj_event.upgrade)] (C)2012-2099 Powered by 逍遥设计.
 *	Version: 1.1
 *	Date: 2012-11-5 08:25
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
$plugin_xj_event = DB::table('xj_event');
$plugin_xj_eventthread = DB::table('xj_eventthread');
$plugin_xj_eventapply = DB::table('xj_eventapply');
$plugin_xj_eventpay_log = DB::table('xj_eventpay_log');
$plugin_xj_event_sms_log = DB::table('xj_event_sms_log');
$plugin_xj_event_signed = DB::table('xj_event_signed');


if($_GET['fromversion']=='1.1' or $_GET['fromversion']=='1.2' or $_GET['fromversion']=='1.3'){
$sql = <<<EOF
ALTER TABLE $plugin_xj_eventthread ADD `sort` SMALLINT(1) NOT NULL;
EOF;
runquery($sql);
}


if($_GET['fromversion']<'1.4'){
$sql = <<<EOF
ALTER TABLE $plugin_xj_eventapply ADD `ufielddata` TEXT NOT NULL;
EOF;
runquery($sql);

	$query = DB::query("SELECT * FROM ".DB::table('xj_eventapply')." WHERE ufielddata =''");
	while($value = DB::fetch($query)){
		$ufielddata = array();
		$ufielddata['realname'] = $value['realname'];
		$ufielddata['mobile'] = $value['mobile'];
		$ufielddata['qq'] = $value['qq'];
		$ufielddata = serialize($ufielddata);
		DB::query("UPDATE ".DB::table('xj_eventapply')." SET ufielddata = '$ufielddata' WHERE applyid = ".$value['applyid']);
	}
	

}

if($_GET['fromversion']<'1.8'){
$sql = <<<EOF
ALTER TABLE $plugin_xj_event ADD `activitybegin` int(10) NOT NULL;
EOF;
runquery($sql);
$query = DB::query("SELECT * FROM ".DB::table('xj_event'));
while($value = DB::fetch($query)){
	$setting = unserialize($value['setting']);
	if(!$setting['eventzy_enable']){
		$setting['eventzy_enable']=1;
		$setting['eventzy_name']=lang('plugin/xj_event', 'zuoye');
		$setting['eventzy_fid']=0;
		$settingstr = serialize($setting);
		DB::query("UPDATE ".DB::table('xj_event')." SET setting = '$settingstr' WHERE eid = ".$value['eid']);
	}
}
}
if($_GET['fromversion']<'2.0'){
$sql = <<<EOF
ALTER TABLE $plugin_xj_event CHANGE `use_cost` `use_cost` DECIMAL( 9, 2 ) NOT NULL;
ALTER TABLE $plugin_xj_eventapply ADD `pay_state` smallint(1) NOT NULL;

CREATE TABLE IF NOT EXISTS $plugin_xj_eventpay_log (
  `applyid` mediumint(8) NOT NULL,
  `uid` mediumint(8) NOT NULL,
  `tid` mediumint(8) NOT NULL,
  `orderid` varchar(32) NOT NULL,
  `tradeno` varchar(32) NOT NULL,
  `paytype` varchar(12) NOT NULL,
  `paystate` smallint(1) NOT NULL,
  `trade_status` varchar(50) NOT NULL,
  `subject` varchar(100) NOT NULL,
  `price` decimal(9,2) NOT NULL,
  `buyer_email` varchar(60) NOT NULL,
  `total_fee` decimal(9,2) NOT NULL,
  `create_time` int(10) NOT NULL,
  `pay_time` int(10) NOT NULL,
  `notify_time` int(10) NOT NULL,
  KEY `applyid` (`applyid`,`uid`),
  KEY `orderid` (`orderid`)
) ENGINE=MyISAM;
EOF;
runquery($sql);
}
if($_GET['fromversion']<'3.0'){
$sql = <<<EOF
ALTER TABLE $plugin_xj_eventapply ADD `seccode` VARCHAR( 8 ) NOT NULL;
ALTER TABLE $plugin_xj_eventapply ADD  `secstate` SMALLINT( 1 ) NOT NULL;
ALTER TABLE $plugin_xj_eventapply ADD  `sectime` INT( 10 ) NOT NULL;

CREATE TABLE IF NOT EXISTS $plugin_xj_event_sms_log (
  `id` mediumint(8) NOT NULL auto_increment,
  `uid` mediumint(8) NOT NULL,
  `sendtype` varchar(20) NOT NULL,
  `sendcount` mediumint(8) NOT NULL,
  `sendtime` int(10) NOT NULL,
  `sendcontent` varchar(100) NOT NULL,
  `sendnumber` mediumtext NOT NULL,
  `sendstate` varchar(50) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;
EOF;
runquery($sql);	
}

if($_GET['fromversion']<'3.2'){
$sql = <<<EOF
ALTER TABLE $plugin_xj_eventapply ADD `session` SMALLINT(3) NOT NULL,ADD INDEX(`session`);
EOF;
runquery($sql);	
}

if($_GET['fromversion']<'3.55'){
$sql = <<<EOF
CREATE TABLE IF NOT EXISTS $plugin_xj_event_signed (
  `tid` mediumint(8) NOT NULL,
  `uid` mediumint(8) NOT NULL,
  `dateline` int(10) NOT NULL
) ENGINE=MyISAM;
EOF;
runquery($sql);	
}




$pluginid = 'xj_event';
//请根据您的实际需要增减下方的嵌入点
$Hooks = array(
	'forumdisplay_threadBottom','viewthread_threadTop','forumdisplay_sideBar','viewthread_sideBar',
);
$data = array();
foreach($Hooks as $Hook) {
	$data[] = array($Hook => array('plugin' => $pluginid, 'include' => 'api.class.php', 'class' => $pluginid.'_api', 'method' => $Hook));
}
require_once DISCUZ_ROOT.'./source/plugin/wechat/wechat.lib.class.php';
WeChatHook::updateAPIHook($data);


$finish = TRUE;
/*
if($_GET['fromversion']=='1.1' or $_GET['fromversion']=='1.2' or $_GET['fromversion']=='1.3'){
	$sql = <<<EOF
	ALTER TABLE $plugin_xj_eventthread ADD `sort` SMALLINT(1) NOT NULL;
	EOF;
	runquery($sql);
}
if($_GET['fromversion']=='1.1' or $_GET['fromversion']=='1.2' or $_GET['fromversion']=='1.3' or $_GET['fromversion']==1.34){
	$sql = <<<EOF
	ALTER TABLE $plugin_xj_eventapply ADD `ufielddata` TEXT NOT NULL 
	EOF;
	runquery($sql);
	
	$query = DB::query("SELECT * FROM ".DB::table('xj_eventapply')." WHERE ufielddata =''");
	while($value = DB::fetch($query)){
		$ufielddata = array();
		$ufielddata['realname'] = $value['realname'];
		$ufielddata['mobile'] = $value['mobile'];
		$ufielddata['qq'] = $value['qq'];
		$ufielddata = serialize($ufielddata);
		DB::query("UPDATE ".DB::table('xj_eventapply')." SET ufielddata = '$ufielddata' WHERE applyid = ".$value['applyid']);
	}
	cpmsg('tasks_installed', 'action=tasks&operation=type', 'succeed');
}
*/


?>