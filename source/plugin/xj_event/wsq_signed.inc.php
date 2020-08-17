<?php

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}


if(file_exists(DISCUZ_ROOT.'./source/plugin/xj_event/module/signed/wsq_signed.php')) {
	@include 'module/signed/wsq_signed.php';
}


?>