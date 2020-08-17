<?php
require '../source/class/class_core.php';
$discuz = & discuz_core::instance();
$discuz->init();

$root = 'http://sns.huizhou.cn/static/image/smiley/';
$type_dicts = array('2'=>'pw_default', '409'=>'qq', '500'=>'taobao');
$type_keys = implode(',' ,array_keys($type_dicts));

$list = DB::fetch_all("SELECT `typeid`, `code`, `url` FROM `pre_common_smiley` WHERE `typeid` IN ({$type_keys})");

?>
<!DOCTYPE html>
<html>
<head>
  <title>表情列表</title>
</head>
<body>
<table>
  <tr>
    <th>表情分类</th>
    <th>表情代码</th>
    <th>表情图片</th>
  </tr>
  <?foreach($list as $item):?>
    <tr>
      <td><?=$item['typeid']?></td>
      <td><?=$item['code']?></td>
      <td><img src="<?=$root . $type_dicts[$item['typeid']] . '/' . $item['url']?>"></td>
    </tr>
  <?endforeach?>
</table>
</body>
</html>