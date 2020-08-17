<?php

/**
 *      $Id: cron_shuji_poststick.php 26812 2015-04-03 16:21:29Z Enicn_d $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$group_uids = '';
$gid = '48,54';
$fid = 863;
$stick_table = DB::table('forum_poststick');
$g_uids = DB::fetch_all("SELECT uid FROM ".DB::table('common_member')." WHERE `groupid` IN ($gid)");
if (is_array($g_uids)) {
	foreach ($g_uids as $key => $value) {
		$group_uids .= $value['uid'].',';
	}
}
$group_uids = substr($group_uids, 0, -1);
$time = $_G['timestamp'] - 2000;
$pdata = DB::fetch_all("SELECT `tid`, `pid`, `position` FROM ".DB::table('forum_post')." WHERE `authorid` IN ($group_uids) AND `fid` = $fid AND `dateline` > $time");
foreach ($pdata as $p) {
	$p['dateline'] = $_G['timestamp'];
	C::t('forum_poststick')->insert($p, false, true);
	C::t('forum_thread')->update($p['tid'], array('moderated'=>1, 'stickreply'=>1));
}

?>