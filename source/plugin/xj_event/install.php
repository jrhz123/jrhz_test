<?php
/**
 *	[超级活动(xj_event.install)] (C)2012-2099 Powered by 逍遥设计.
 *	Version: 1.0
 *	Date: 2012-10-21 20:13
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$plugin_xj_event = DB::table('xj_event');
$plugin_xj_eventapply = DB::table('xj_eventapply');
$plugin_xj_eventthread = DB::table('xj_eventthread');
$plugin_xj_event_member_info = DB::table('xj_event_member_info');
$plugin_xj_event_vote_log = DB::table('xj_event_vote_log');
$plugin_xj_eventpay_log = DB::table('xj_eventpay_log');
$plugin_xj_event_sms_log = DB::table('xj_event_sms_log');
$plugin_xj_event_signed = DB::table('xj_event_signed');



$sql = <<<EOF
CREATE TABLE IF NOT EXISTS $plugin_xj_event (
  `eid` mediumint(8) NOT NULL auto_increment,
  `tid` mediumint(8) NOT NULL,
  `postclass` tinyint(1) NOT NULL,
  `starttime` int(10) NOT NULL,
  `endtime` int(10) NOT NULL,
  `offlineclass` smallint(2) NOT NULL,
  `onlineclass` smallint(2) NOT NULL,
  `citys` varchar(20) NOT NULL,
  `area` mediumint(6) NOT NULL,
  `event_address` varchar(150) NOT NULL,
  `event_number` mediumint(6) NOT NULL,
  `event_number_man` mediumint(6) NOT NULL,
  `event_number_woman` mediumint(6) NOT NULL,
  `event_number_max` smallint(4) NOT NULL,
  `use_extcredits_num` smallint(4) NOT NULL,
  `use_extcredits` tinyint(1) NOT NULL,
  `use_cost` DECIMAL(9,2) NOT NULL,
  `activitybegin` int(10) NOT NULL,
  `activityexpiration` int(10) NOT NULL,
  `userfield` text NOT NULL,
  `activityaid` mediumint(8) NOT NULL,
  `activityaid_url` varchar(200) NOT NULL,
  `verify` tinyint(1) NOT NULL,
  `setting` text NOT NULL,
  PRIMARY KEY  (`eid`),
  KEY `eid` (`eid`),
  KEY `tid` (`tid`),
  KEY `postclass` (`postclass`),
  KEY `offlineclass` (`offlineclass`,`onlineclass`),
  KEY `area` (`area`)
) ENGINE=MyISAM AUTO_INCREMENT=8 ;

CREATE TABLE IF NOT EXISTS $plugin_xj_eventapply (
  `applyid` mediumint(8) NOT NULL auto_increment,
  `eid` mediumint(8) NOT NULL,
  `tid` mediumint(8) NOT NULL,
  `uid` int(8) NOT NULL,
  `realname` varchar(20) NOT NULL,
  `mobile` varchar(15) NOT NULL,
  `qq` varchar(15) NOT NULL,
  `applynumber` smallint(3) NOT NULL,
  `bmmessage` varchar(200) NOT NULL,
  `dateline` int(10) NOT NULL,
  `verify` tinyint(1) NOT NULL,
  `pay_state` smallint(1) NOT NULL,
  `pj` tinyint(1) NOT NULL,
  `ufielddata` text NOT NULL,
  `seccode` varchar(8) NOT NULL,
  `secstate` smallint(1) NOT NULL,
  `sectime` int(10) NOT NULL,
  `session` SMALLINT(3) NOT NULL,
  PRIMARY KEY  (`applyid`),
  KEY `applyid` (`applyid`,`tid`,`uid`),
  KEY `session` (`session`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS $plugin_xj_eventthread (
  `eid` mediumint(8) NOT NULL,
  `tid` mediumint(8) NOT NULL,
  `fid` mediumint(8) NOT NULL,
  `sort` smallint(1) NOT NULL,
  `coverurl` varchar(200) NOT NULL,
  `votes` mediumint(6) NOT NULL,
  KEY `eid` (`eid`,`tid`,`fid`)
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS $plugin_xj_event_member_info (
  `uid` mediumint(8) NOT NULL,
  `good` mediumint(8) NOT NULL,
  `common` mediumint(8) NOT NULL,
  `bad` mediumint(8) NOT NULL,
  `plane` mediumint(8) NOT NULL
) ENGINE=MyISAM;
CREATE TABLE IF NOT EXISTS $plugin_xj_event_vote_log (
  `vid` int(10) NOT NULL auto_increment,
  `eid` mediumint(8) NOT NULL,
  `tid` mediumint(8) NOT NULL,
  `uid` mediumint(8) NOT NULL,
  `ip` varchar(23) NOT NULL,
  `votetime` int(10) NOT NULL,
  PRIMARY KEY  (`vid`),
  KEY `eid` (`eid`),
  KEY `tid` (`tid`),
  KEY `uid` (`uid`),
  KEY `votetime` (`votetime`),
  KEY `vid` (`vid`)
) ENGINE=MyISAM AUTO_INCREMENT=4 ;
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
) ENGINE=MyISAM AUTO_INCREMENT=7 ;

CREATE TABLE IF NOT EXISTS $plugin_xj_event_signed (
  `tid` mediumint(8) NOT NULL,
  `uid` mediumint(8) NOT NULL,
  `dateline` int(10) NOT NULL
) ENGINE=MyISAM;

EOF;

runquery($sql);


//安装微社区嵌入
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

$finish = true;
?>