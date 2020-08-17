<?php

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(!$_G['uid']) {
	showmessage('not_loggedin', NULL, array(), array('login' => 1));
}

if($_GET['formhash'] != FORMHASH) {
	showmessage('undefined_action');
}

if(!$_GET['uid']) {
	showmessage('undefined_action');
}
$uid = $_GET['uid'];

$member_table = DB::table('common_member');
$userdata = DB::fetch_first("SELECT * FROM `$member_table` WHERE `uid` = '$uid'");

//Enicn_d modified 2015-05-20 11:20 forbidden administrator swtich from outlan.
if (!preg_match('/^(192\.168\.).+/',$_G['clientip'])) {
	if ($userdata['adminid']==1 || in_array($userdata['groupid'], array(1,2,3))) {
		showmessage('版主权限以上账号不可直接切换，请重新登陆。','forum.php');
	}
}

$nm_table = DB::table('nimba_majia');
$mjid = DB::result_first("SELECT `mjid` FROM $nm_table WHERE `useruid`='$_G[uid]'");
$check = DB::fetch_first("SELECT * FROM $nm_table WHERE `mjid`='$mjid' AND `useruid`='$uid'");
if ($check) {
	if ($userdata['password']==$check['password']) {
		/*header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');

		$cookietime = 31536000;
		if(($member = getuserbyuid($uid, 1))) {
			dsetcookie('auth', authcode("$member[password]\t$member[uid]", 'ENCODE'), $cookietime);
		}*/
		$nm_table = DB::table('nimba_majia');
		$time = $_G['timestamp'];
		DB::query("UPDATE $nm_table SET `lastswitch` = $time WHERE `useruid` = $uid");
		DB::query("UPDATE $nm_table SET `lastswitch` = $time WHERE `useruid` = $_G[uid]");
		loaducenter();
		echo uc_user_synlogin($uid);
		showmessage('nimba_majia:switchuser_succeed','forum.php');
	} else {
		DB::delete('nimba_majia', '`useruid`='.$check['useruid']);
	}
}
showmessage('nimba_majia:switchuser_error','forum.php');


?>