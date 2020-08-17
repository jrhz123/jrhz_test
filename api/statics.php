<?php
require '../source/class/class_core.php';
$discuz = & discuz_core::instance();
$discuz->init();
//global $_G;
$action = $_GET['action'];
switch ($action) {
	case 'bumen':
		date_default_timezone_set('Asia/Shanghai');
		$hoursecs = 60*60;
		$daysecs = $hoursecs*24;
		$weeksecs = $daysecs*7;
		$weeks = floor(time() / $weeksecs);
		$current_monday = ($weeks * $weeksecs) - ($daysecs * 3) - ($hoursecs * 8);
		$current_sunday = ($weeks * $weeksecs) + ($daysecs * 3) + ($hoursecs * 16);
		$condition = "AND `dateline` > $current_monday AND `dateline` < $current_sunday";

		$plugin_id = 'replies_checking';
	 	if(!isset($_G['cache']['plugin'])) {
 			loadcache('plugin');
 		}
		$C = $_G['cache']['plugin'][$plugin_id];
		$target_forum = $C['target_forum'];
		$groupids = '';
		$group_uids = '';
		$target_groups = unserialize($C['target_groups']);
		$groupids = implode(',', $target_groups);
		$g_uids = DB::fetch_all("SELECT uid FROM ".DB::table('common_member')." WHERE `groupid` IN ($groupids)");
		if (is_array($g_uids)) {
			foreach ($g_uids as $key => $value) {
				$group_uids .= $value['uid'].',';
			}
		}
		$group_uids = substr($group_uids, 0, -1);
		$query_string = "SELECT `authorid`,`author`,count(*) AS `posts_count`
							FROM ".DB::table('forum_post')."
							WHERE `authorid` IN ($group_uids) 
								AND `fid`=$target_forum 
								$condition
							GROUP BY `authorid` 
							ORDER BY `posts_count` DESC 
							LIMIT 10";
		$threadlist = DB::fetch_all($query_string);
		$li_part = '';
		foreach ($threadlist as $thread) {
			$authorid = $thread['authorid'];
			$author = $thread['author'];
			$li_part .=	"<li><a href=\"http://sns.huizhou.cn/home.php?mod=space&uid=$authorid&do=thread&from=space&type=reply\" target=\"_blank\">$author</a></li>";
		}
		echo "document.write('<ul>$li_part</ul>')";
		break;
	default:
		echo 'Page not found.';
		break;
}

?>