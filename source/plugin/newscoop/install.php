<?php

/**
 *      $ Id: newscoop.class.php UTF-8 2015-03-28 00:43:10Z Enicn_d $
 */
 
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$sql = <<<EOF

CREATE TABLE IF NOT EXISTS pre_plugin_baoliao (
  `tid` int(10) unsigned NOT NULL DEFAULT '0',
  `datetime` int(11) unsigned NOT NULL DEFAULT '0',
  `location` varchar(255) NOT NULL DEFAULT '',
  `contact` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`tid`)
) ENGINE=MyISAM;

EOF;

runquery($sql);

updatecache('setting');
$finish = true;

?>