<?php
require '../source/class/class_core.php';
$discuz = & discuz_core::instance();
$discuz->init();

$result = DB::fetch_all("SELECT FROM_UNIXTIME(`dateline`,'%Y-%m-%d') AS `datetime`, `message`, `tid`, `pid` FROM `pre_forum_post` WHERE `message` LIKE '%[b][url] [img]%' LIMIT 500");
// var_dump($result);
foreach ($result as $item) {
  $message = preg_replace('/\[b\]\[url\] \[img\].*\[\/b\]/', '', $item['message']);
  $query_string = "UPDATE `pre_forum_post` SET `message` = '{$message}' WHERE `pid` = {$item['pid']}";
  var_dump($query_string);
  DB::query($query_string);
}

?>
