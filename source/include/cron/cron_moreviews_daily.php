<?php

/**
 *      $Id: cron_moreviews_daily.php 26812 2015-04-03 16:21:29Z Enicn_d $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$last_days = time() - (60*60*24*5);
$thread_table = DB::table('forum_thread');
$blog_table = DB::table('home_blog');

$thread_query = "UPDATE LOW_PRIORITY $thread_table SET `views` = `views` * 1.02 WHERE `lastpost` > $last_days AND `views` < 5000";
DB::query($thread_query);

$thread_query_2 = "UPDATE LOW_PRIORITY $thread_table SET `views` = `views` * 1.01 WHERE `lastpost` > $last_days AND `views` >= 5000 AND `views` < 10000";
DB::query($thread_query_2);

$blog_query = "UPDATE LOW_PRIORITY $blog_table SET `viewnum` = `viewnum` * 1.02 WHERE `dateline` > $last_days AND `viewnum` < 5000";
DB::query($blog_query);

$blog_query_2 = "UPDATE LOW_PRIORITY $blog_table SET `viewnum` = `viewnum` * 1.01 WHERE `dateline` > $last_days AND `viewnum` >=5000 AND `viewnum` < 10000";
DB::query($blog_query_2);

?>
