<?php
require '../source/class/class_core.php';
$discuz = & discuz_core::instance();
$discuz->init();
$base_url = 'http://'.$_SERVER['HTTP_HOST'];
include template('common/online_status');
?>