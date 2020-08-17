<?php

/*
	[Cis!] (C)2005-2013 comeings.com.
	This is NOT a freeware, use is subject to license terms

	$Id: uninstall.inc.php 2013-09-24 $
*/

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$sql = <<<EOF

DROP TABLE pre_comeing_threadstation;

EOF;


$finish = TRUE;