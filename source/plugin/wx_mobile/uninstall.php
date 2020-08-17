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
EOF;

runquery($sql);

$finish = TRUE;

?>