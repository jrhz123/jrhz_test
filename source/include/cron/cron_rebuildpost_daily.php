<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cron_rebuildpost_daily.php 31920 2017-02-24 11:16:33Z stellar $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}


$threadtableids = C::t('common_setting')->fetch('threadtableids', true);
// $queryt = C::t('common_member')->range(60000, 20000);
$mj_string = 'SELECT `useruid` AS `uid` FROM `pre_nimba_majia`';
$mj_query = DB::query($mj_string);
while ($mem = DB::fetch($mj_query)) {
	$postcount = 0;

	loadcache('posttable_info');
	if(!empty($_G['cache']['posttable_info']) && is_array($_G['cache']['posttable_info'])) {
		foreach($_G['cache']['posttable_info'] as $key => $value) {
			$postcount += C::t('forum_post')->count_by_authorid($key, $mem['uid']);
		}
	} else {
		$postcount += C::t('forum_post')->count_by_authorid(0, $mem['uid']);
	}
	$postcount += C::t('forum_postcomment')->count_by_authorid($mem['uid']);
	$threadcount = C::t('forum_thread')->count_by_authorid($mem['uid']);
	foreach($threadtableids as $tableid) {
		if(!$tableid) {
			continue;
		}
		$threadcount += C::t('forum_thread')->count_by_authorid($mem['uid'], $tableid);
	}
	C::t('common_member_count')->update($mem['uid'], array('posts' => $postcount, 'threads' => $threadcount));
}
?>