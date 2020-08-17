<?php
/**
 *  [【超人】点赞(superman_zan.{modulename})] (C)2012-2099 Powered by 时创科技.
 *  Version: $VERSION
 *  Date: $DATE
 */
if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

$sql = <<<EOF
CREATE TABLE `pre_plugin_superman_zan` (
  `pid` int(11) NOT NULL,
  `tid` int(11) NOT NULL DEFAULT '0',
  `count` int(11) NOT NULL DEFAULT '0',
  `first` tinyint(4) NOT NULL,
  UNIQUE KEY `pid` (`pid`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
EOF;
runquery($sql);

$sql = <<<EOF
CREATE TABLE `pre_plugin_superman_zan_ref` (
  `pid` int(11) NOT NULL DEFAULT '0',
  `uid` int(11) NOT NULL DEFAULT '0',
  `dateline` int(10) NOT NULL DEFAULT '0',
  UNIQUE KEY `pid_uid_UNIQUE` (`pid`,`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
EOF;
runquery($sql);

save_syscache('superman_zan', array());
$finish = TRUE;
