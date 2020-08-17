<?php
date_default_timezone_set('Asia/Shanghai');
require '../source/class/class_core.php';
$discuz = & discuz_core::instance();
$discuz->init();

$plugin_id = 'replies_checking';
if(!isset($_G['cache']['plugin'])) {
    loadcache('plugin');
}
$C = $_G['cache']['plugin'][$plugin_id];
$target_forum = $C['target_forum'];
$vice_users = $C['vice_users'];
$order_by = 'dateline';
$sort_by = 'DESC';
$offset = 0;
$limit = 5;
$query_string = "SELECT *
                    FROM ".DB::table('forum_thread')."
                    WHERE `fid`=$target_forum
                        AND `authorid` IN ($vice_users)
                        AND `displayorder` >= 0
                    ORDER BY `$order_by` $sort_by
                    LIMIT $offset, $limit";
$result = DB::fetch_all($query_string);
?>

<!DOCTYPE html>
<html>
<head>
    <title>市委督办帖子</title>
<style>
body {
    font-size: 14px;
}
.thread-list {
    list-style-type: none;
    margin: 0;
    padding-left: 1rem;
}
.thread-list li {
    line-height: 26px;
}
.thread-list li:before {
    content: '·';
    float: left;
    margin-left: -1rem;
    clear: left;
}
.thread-subject {
    display: block;
    float: left;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    width: 100%;
}
a.thread-subject, a.thread-subject:visited {
    color: black;
    text-decoration: none;
}

a.thread-subject:hover {
    color: red;
    text-decoration: underline;
}

.thread-date {
    /*float: right;*/
    display: none;
    text-align: right;
    width: 100px;
}
</style>
</head>
<body>
<!--书记论坛——督办帖子：http://sns.huizhou.cn/forum.php?mod=forumdisplay&fid=863&filter=duban-->
<?if(!empty($result)):?>
<ul class="thread-list">
<?foreach($result as $thread):?>
    <li>
        <a href="http://sns.huizhou.cn/forum.php?mod=viewthread&tid=<?=$thread['tid']?>" target="_blank" class="thread-subject"><?=$thread['subject']?></a>
    </li>
<?endforeach?>
</ul>
<?else:?>
无相关数据
<?endif?>

</body>
</html>
