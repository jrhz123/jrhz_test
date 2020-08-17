<?php

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
@$m  = $_GET['m'] ? $_GET['m'] : 'sns';
$plugin_static = 'source/plugin/mebsite/';
$navtitle = '微网首页';

include template('diy:'.$m, 0, 'source/plugin/mebsite/template');

?>