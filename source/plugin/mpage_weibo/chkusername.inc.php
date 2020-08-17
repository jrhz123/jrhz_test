<?php

if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

/**
Enicn_d modified 2016-01-26 added quick register of Weibo login
*/
loaducenter();
$username = $_POST['username'];
echo uc_user_checkname($username);
/**

*/
?>
