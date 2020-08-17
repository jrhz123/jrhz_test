<?php

/**
 *      HuiZhou Media Group (C)2008-2015.
 *
 *      $Id: install.php 8889 2015-07-28 16:04 Created by Stellar $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$sql = <<<EOF

DROP TABLE IF EXISTS `pre_wx_mobile`;
CREATE TABLE IF NOT EXISTS pre_wx_mobile (
  `uid` int(10) unsigned NOT NULL,
  `openid` char(32) NOT NULL default '',
  `status` tinyint(1) NOT NULL DEFAULT 0,
  `unionid` char(32) NOT NULL default '',
  `headimgurl` varchar(128) NOT NULL default '',
  `nickname` char(32) NOT NULL default '',
  `dateline` int(10) unsigned NOT NULL,
  PRIMARY KEY (`uid`),
  KEY `openid` (`openid`)
) ENGINE=MYISAM;

EOF;

runquery($sql);

$finish = TRUE;

?>