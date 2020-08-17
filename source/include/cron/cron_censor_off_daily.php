<?php

/**
 *      $Id: cron_censor_on_daily.php 2015-03-28 05:50:25Z enicn_d $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$forum_status = array();
$forum_table = DB::table('forum_forum'); //版块信息数据表
$cron_table = DB::table('cron_temp'); //计划任务临时表

$get_cron_temp = "SELECT `content` FROM `$cron_table` WHERE `item` = 'modnewposts'"; //从临时表获取版块的审核设置状态
$cron_query = DB::fetch_first($get_cron_temp);
$forum_status = json_decode($cron_query['content'],'array');
foreach ($forum_status as $fid => $modnewposts) {
	$update = "UPDATE `$forum_table` SET `modnewposts` = $modnewposts WHERE `fid` = $fid"; //逐个版块恢复审核设置状态
	DB::query($update);
}

?>