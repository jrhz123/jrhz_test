<?php

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

/**
Enicn_d modified 2015-04-15 added quick register of QQ login
*/
loaducenter();
$username = $_POST['username'];
echo uc_user_checkname($username);
/**

*/
?>