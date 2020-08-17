<?php
/*
 * Ö÷Ò³£ºhttp://addon.discuz.com/?@ailab
 * ÈË¹¤ÖÇÄÜÊµÑéÊÒ£ºDiscuz!Ó¦ÓÃÖÐÐÄÊ®´óÓÅÐã¿ª·¢Õß£¡
 * ²å¼þ¶¨ÖÆ ÁªÏµQQ594941227
 * From www.ailab.cn
 */
if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}
loadcache('plugin');
$langvars=lang('plugin/nimba_majia');
echo '<script type="text/javascript" src="static/js/calendar.js"></script>';
if ($_GET['start']) {
	$start=strtotime($_GET['start'].':00');
} else {
	$start=strtotime(date('Y-m',time()).'-1 00:00:00');
}
if ($_GET['end']) {
	$end=strtotime($_GET['end'].':00');
} else {
	$end=time();
}

$username = trim($_GET['username']);
showformheader("plugins&operation=config&identifier=nimba_majia&pmod=posts");
showtableheader($langvars['posts_title'], 'nobottom');
showsetting('相关用户名', 'username',$_GET['username'], 'text','',0,'填写您本次要查询的用户');
showsetting($langvars['posts_start'], 'start',date('Y-m-d H:i',$start), 'calendar', '', 0,'', 1);	
showsetting($langvars['posts_end'], 'end',date('Y-m-d H:i',$end), 'calendar', '', 0,'', 1);	
showsubmit('editsubmit');
showtablefooter();
showformfooter();
$where="dateline>$start and dateline<$end";

$nm_table  = DB::table("nimba_majia");
$mjid = DB::result_first("SELECT mjid FROM $nm_table WHERE `username` = '$username'");
$users = DB::fetch_all("select mjid,useruid,username from ".DB::table("nimba_majia")." where mjid='$mjid'");
showtableheader($langvars['posts_title_2']);
showsubtitle(array('序列号',$langvars['posts_majia'],$langvars['posts_thread'],$langvars['posts_post']));
foreach($users as $k=>$user) {
	$posts=DB::result_first("select count(*) as num from ".DB::table("forum_post")." where $where and authorid='".$user['useruid']."' and first=0");
	$threads=DB::result_first("select count(*) as num from ".DB::table("forum_post")." where $where and authorid='".$user['useruid']."' and first=1");
	showtablerow('', array('class="td25"', 'class="td_k"', 'class="td_l"'), array(
		$user['mjid'],
		'<a href="home.php?mod=space&uid='.$user['useruid'].'" target="_blank">'.$user['username'].'</a>',
		$threads,
		$posts
	));	
}
showtablefooter();
?>