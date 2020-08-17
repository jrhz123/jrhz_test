<?php
/**
 *  [【超人】点赞(superman_zan.{modulename})] (C)2012-2099 Powered by 时创科技.
 *  Version: $VERSION
 *  Date: $DATE
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

$fromversion = $_GET['fromversion'];
if ($fromversion < 4.1) {
	$sql = <<<EOF
ALTER TABLE `cdb_plugin_superman_zan_ref` ADD `dateline` INT(10) NOT NULL DEFAULT '0' AFTER `uid`;
EOF;
	runquery($sql);
}

$finish = true;
