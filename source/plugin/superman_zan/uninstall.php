<?php
/**
 *  [【超人】点赞(superman_zan.{modulename})] (C)2012-2099 Powered by 时创科技.
 *  Version: $VERSION
 *  Date: $DATE
 */
if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

$sql =<<<EOF
DROP TABLE `cdb_plugin_superman_zan`;
DROP TABLE `cdb_plugin_superman_zan_ref`;
EOF;
runquery($sql);

$finish = TRUE;
