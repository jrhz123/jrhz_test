<?php

$tid = intval($_GET['tid']);
require_once DISCUZ_ROOT.'./source/plugin/wechat/wechat.lib.class.php';
//dheader('Expires: '.gmdate('D, d M Y H:i:s', TIMESTAMP + 86400).' GMT');
//$qrsize = !empty($_GET['qrsize']) ? $_GET['qrsize'] : 3;
$dir = DISCUZ_ROOT.'./data/cache/qrcode/';
$dtid = sprintf("%09d", $tid);
$dir1 = substr($dtid, 0, 3);
$dir2 = substr($dtid, 3, 2);
$dir3 = substr($dtid, 5, 2);
$dir = $dir.$dir1.'/'.$dir2.'/'.$dir3.'/';
$file = $dir.'/xj_scoupon_'.$tid.'.jpg';
//$url = str_replace("/source/plugin/xj_scoupon/","",$_G['siteurl']);
$downcouponurl = WeChatHook::getPluginUrl('xj_event:wsq_signed', $param = array('tid'=>$tid));

if(!file_exists($file) || !filesize($file)) {
	dmkdir($dir);
	require_once DISCUZ_ROOT.'source/plugin/mobile/qrcode.class.php';
	QRcode::png($downcouponurl, $file, QR_ECLEVEL_Q,12);
}
dheader('Content-Disposition: inline; filename=qrcode_index.jpg');
dheader('Content-Type: image/pjpeg');
@readfile($file);
?>