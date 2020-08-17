<?php

/**
 *      $Id: qrcode.inc.php 34716 2015-07-31 17:52 Stellar $
 */
if (!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$dir = DISCUZ_ROOT.'./data/cache/qrcode/';

if($_GET['access']) {
	dheader('Expires: '.gmdate('D, d M Y H:i:s', TIMESTAMP + 86400).' GMT');

	if($_GET['threadqr']) {
		$tid = dintval($_GET['threadqr']);
		include_once template('wx_mobile:wx_mobile_threadqr');
	} elseif($_GET['tid']) {
		$qrsize = !empty($_GET['qrsize']) ? $_GET['qrsize'] : 4;

		$tid = dintval($_GET['tid']);
		$dtid = sprintf("%09d", $tid);
		$dir1 = substr($dtid, 0, 3);
		$dir2 = substr($dtid, 3, 2);
		$dir3 = substr($dtid, 5, 2);
		$dir = $dir.$dir1.'/'.$dir2.'/'.$dir3.'/';
		$file = $dir.'/qr_t'.$tid.'.jpg';
		if(!file_exists($file) || !filesize($file)) {
			if(!C::t('forum_thread')->fetch($tid)) {
				exit;
			}
			dmkdir($dir);
			require_once DISCUZ_ROOT.'source/plugin/mobile/qrcode.class.php';
			QRcode::png($_G['siteurl'].'forum.php?mod=viewthread&tid='.$_GET['tid'], $file, QR_ECLEVEL_Q, $qrsize);
		}
		dheader('Content-Disposition: inline; filename=qrcode_t'.$tid.'.jpg');
		dheader('Content-Type: image/pjpeg');
		@readfile($file);
	}
	exit;
}