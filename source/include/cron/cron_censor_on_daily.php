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

$get_forum_status = "SELECT `fid`, `modnewposts` FROM `$forum_table` WHERE `type`='forum'"; //读取所有版块的审核设置状态
$forum_query = DB::query($get_forum_status);
while ($forum = DB::fetch($forum_query)) {
	$forum_status[$forum['fid']] = $forum['modnewposts'];
}
$json_string = json_encode($forum_status); //所有版块审核设置转为json字符串
$save_forum_status = "UPDATE `$cron_table` SET `content` = '$json_string' WHERE `item` = 'modnewposts'"; //保存审核设置状态
DB::query($save_forum_status);

$turn_on = "UPDATE `$forum_table` SET `modnewposts` = 2 WHERE `type`='forum'"; //将所有版块设置为审核主题和帖子
DB::query($turn_on);

?>