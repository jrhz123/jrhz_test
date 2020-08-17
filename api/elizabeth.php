<?php
require '../source/class/class_core.php';
$discuz = & discuz_core::instance();
$discuz->init();

/*$uid = 347299;
loaducenter();
echo uc_user_synlogin($uid);*/

//echo 'finished';

$uid = '';
$postcount = 0;
loadcache('posttable_info');
if(!empty($_G['cache']['posttable_info']) && is_array($_G['cache']['posttable_info'])) {
    foreach($_G['cache']['posttable_info'] as $key => $value) {
        $postcount += C::t('forum_post')->count_by_authorid($key, $uid);
    }
} else {
    $postcount += C::t('forum_post')->count_by_authorid(0, $uid);
}
$postcount += C::t('forum_postcomment')->count_by_authorid($uid);
$threadcount = C::t('forum_thread')->count_by_authorid($uid);
foreach($threadtableids as $tableid) {
    if(!$tableid) {
        continue;
    }
    $threadcount += C::t('forum_thread')->count_by_authorid($uid, $tableid);
}
C::t('common_member_count')->update($uid, array('posts' => $postcount, 'threads' => $threadcount));

?>
