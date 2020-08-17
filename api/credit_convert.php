<?php
require '../source/class/class_core.php';
$discuz = & discuz_core::instance();
$discuz->init();

/*$limit = 20000;
$page = 6;
$offset = $limit * $page;

//$pw_string = "SELECT `uid`,`rvrc`,`money` FROM `pw_memberdata` WHERE `rvrc` > 0 OR `money` > 1000";
//$pw_string = "SELECT `uid`,`money` FROM `pw_memberdata` WHERE `money` > 0";
//$pw_string = "SELECT `uid`,sum(`value`) AS `total` FROM `pw_membercredit` WHERE `cid` = 2 GROUP BY `uid` HAVING `total` > 0";
$pw_string = "SELECT `uid`,sum(`value`) AS `total` FROM `pw_membercredit` WHERE `cid` = 3 GROUP BY `uid` HAVING `total` > 0";
//$pw_string = "SELECT"
$pw_query = DB::query($pw_string);
while ($pw_member = DB::fetch($pw_query)) {
	$uid = $pw_member['uid'];
	//$dz_credit = ($pw_member['rvrc'] * 3) + floor($pw_member['money'] / 1000);
	//$dz_string = "UPDATE `pre_common_member_count` SET `extcredits1` = $dz_credit WHERE `uid` = $uid";
	//$dz_credit2 = $pw_member['credit'];
	//$dz_string = "UPDATE `pre_common_member_count` SET `extcredits2` = `extcredits2` + $dz_credit2 WHERE `uid` = $uid";
	$dz_credit3 = $pw_member['total'];
	$dz_string = "UPDATE `pre_common_member_count` SET `extcredits2` = `extcredits2` - $dz_credit3 WHERE `uid` = $uid";
	//$dz_credit4 = $pw_member['money'];
	//$dz_string = "UPDATE `pre_common_member_count` SET `extcredits2` = $dz_credit4 WHERE `uid` = $uid";
	DB::query($dz_string);
	//echo $dz_string.'<br/>';
}*/

/*$post_string = "SELECT `authorid`, count(*) AS `total` FROM `pre_forum_post` GROUP BY `authorid` HAVING `total` > 0";
$post_query = DB::query($post_string);
while ($post_count = DB::fetch($post_query)) {
	$uid = $post_count['authorid'];
	$total = $post_count['total'];
	$update_string = "UPDATE pre_common_member_count SET posts = $total WHERE uid = $uid";
	DB::query($update_string);
	var_dump($update_string);
}*/

/*$credit_count = "SELECT `uid`,`extcredits2` FROM `pre_common_member_count";
$credit_result = DB::query($credit_count);
while ($credit = DB::fetch($credit_result)) {
	$base_credit = $credit['extcredits2'];
	$uid = $credit['uid'];
	$dz_string = "UPDATE `pre_common_member` SET `credits` = $base_credit WHERE `uid` = $uid";
	DB::query($dz_string);
}
$blog_count = "SELECT `uid`, count(*) AS `total` FROM `pre_home_blog` GROUP BY `uid`";
$blog_result = DB::query($blog_count);
while ($blog = DB::fetch($blog_result)) {
	$base_blog = $blog['total'];
	$uid = $blog['uid'];
	$dz_string = "UPDATE `pre_common_member_count` SET `blogs` = $base_blog WHERE `uid` = $uid";
	DB::query($dz_string);
}*/
echo 'finish';

?>