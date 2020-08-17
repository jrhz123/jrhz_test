<?php 

if(!defined('IN_DISCUZ')) {
  exit('Access Denied');
}

set_time_limit(0);
date_default_timezone_set('Asia/Shanghai');

define('APPID', 1);
define('SECRET', 'b2b3d04a7e652cfd200023a292b92693');
define('CITYSIGN', 'YUE0011');
define('WEB_CLIENT', 1);
define('API_URL', 'http://58.62.173.137:8080/index.php?r=api/cardinterface');

function simple_curl($url, $post_data) {
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
  $output = curl_exec($ch);
  curl_close($ch);
  return $output;
}

function cutstr_html($string)
{
  $string = strip_tags($string);
  $string = preg_replace('/\[table=100%\].+\[\/table\]/', '', $string);
  $string = preg_replace('/\n/is', '', $string);
  $string = preg_replace('/ |　/is', '', $string);
  $string = preg_replace('/&[a-z]*;/is', '', $string);
  $string = preg_replace('/\[\/?([A-Za-z0-9_=#,:\-\/\.\%\?]*|font=.{0,45}|backcolor=.{0,45}|color=rgb([\d,]+))\]/', '', $string); //清除论坛格式
  $string = preg_replace('/【[\[\]\w\:\/\.\?&=]*回(帖|复)详文】/', '', $string);
  $string = preg_replace('/attachment\//', 'http://sns.huizhou.cn/attachment/', $string);
  $img_pattern = '/http:\/\/[A-Za-z0-9_\/\.]+\.(jpg|jpeg|htm|html|shtml)/';
  preg_match_all($img_pattern, $string, $img);
  $string = preg_replace($img_pattern, '', $string);
  $i = 1;
  if (!empty($img[0])) {
    $string .= '【（请拷贝链接下载查看附件）';
    foreach ($img[0] as $key => $value) {
      $string .= ' 附件' . $i++ . '：' . $value;
    }
  }
  return $string;
}

function do_post($row) {
  if (empty($threadlist[$row['tid']])) {
    $query_string = "SELECT `subject`
              FROM ".DB::table('forum_thread')."
              WHERE `tid` = '{$row['tid']}'";
    $thread = DB::fetch_first($query_string);
    $threadlist[$row['tid']] = $thread;
  }
  $post_data = array(
    "appid" => APPID,
    "card_url" => "http://sns.huizhou.cn/forum.php?mod=redirect&goto=findpost&ptid={$row['tid']}&pid={$row['pid']}",
    "userid" => $row['authorid'],
    "ip" => $row['useip'],
    "send_time" => $row['dateline'],
    "card_title" => $threadlist[$row['tid']]['subject'],
    "card_id" => $row['pid'],
    "card_type" => (int)$row['first'] === 1 ? 1 : 2,
    "card_content" => cutstr_html($row['message']),
    "citysign" => CITYSIGN,
    "username" => $row['author'],
    "add_time" => time(),
    "data_type" => WEB_CLIENT
  );
  $veri_str = '';
  foreach ($post_data as $key => $value) {
    $veri_str .= $value;
  }
  $veri_str .= SECRET;
  $signature = md5($veri_str);
  $post_data['signature'] = $signature;
  $result = json_decode(simple_curl(API_URL, $post_data), 'array');
  if (!empty($result) && $result['result'] === 'success') {
    DB::query("UPDATE `awxd_records` SET `state` = 1 WHERE `pid` = {$row['pid']}");
  }
  usleep(100000);
}

// $time_0 = time() + microtime();

$threadlist = array();
$head_time = time() - 1800;
$post_query = "SELECT * FROM `pre_forum_post` WHERE `dateline` > $head_time AND `invisible` = -1 ORDER BY `dateline` ASC"; //取出前半小时的帖子回复
// $post_query = "SELECT * FROM `pre_forum_post` WHERE dateline > UNIX_TIMESTAMP('2017-01-01')";
$post_result = DB::query($post_query);
while ($row = DB::fetch($post_result)) { //逐条读出
  $check = DB::fetch_first("SELECT * FROM `awxd_records` WHERE `pid` = {$row['pid']}");
  if (empty($check)) {
    $datetime = date('Y-m-d H:i:s');
    DB::query("INSERT INTO `awxd_records` (`pid`, `datetime`) VALUES ({$row['pid']}, '{$datetime}')");
  } else if((int)$check['state'] === 1) {
    continue;
  }
  do_post($row);
}

$post_query = "SELECT * FROM `awxd_records` WHERE `state` = 0";  //读取未成功列表
$post_result = DB::query($post_query);
while ($row = DB::fetch($post_result)) { //逐条读出
  $row = DB::fetch_first("SELECT * FROM  `pre_forum_post` WHERE `pid` = {$row['pid']}");
  if (!empty($row)) {
    do_post($row);
  }
}

// $time_1 = time() + microtime();

// var_dump($time_1 - $time_0);

?>