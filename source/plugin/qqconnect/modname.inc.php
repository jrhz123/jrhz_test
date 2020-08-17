<?php

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

/**
Enicn_d modified 2015-04-21 15:59 added qqconnect user's username changable
*/
loaducenter();

echo uc_user_synlogin($_G['uid']);
showmessage('qqconnect:quick_succeed','forum.php');
/**

*/
?>