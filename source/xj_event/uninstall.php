<?php
/**
 *	[超级活动(xj_event.uninstall)] (C)2012-2099 Powered by 逍遥设计.
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
DROP TABLE IF EXISTS $plugin_xj_event;
DROP TABLE IF EXISTS $plugin_xj_eventapply;
DROP TABLE IF EXISTS $plugin_xj_eventthread;
DROP TABLE IF EXISTS $plugin_xj_event_member_info;
DROP TABLE IF EXISTS $plugin_xj_event_vote_log;
DROP TABLE IF EXISTS $plugin_xj_eventpay_log;
DROP TABLE IF EXISTS $plugin_xj_event_sms_log;
DROP TABLE IF EXISTS $plugin_xj_event_signed;
EOF;
runquery($sql);

//删除微社区嵌入点
$pluginid = 'xj_event';
require_once DISCUZ_ROOT.'./source/plugin/wechat/wechat.lib.class.php';
WeChatHook::delAPIHook($pluginid)



$finish = true;
?>