<?php
/*
 * Ö÷Ò³£ºhttp://addon.discuz.com/?@ailab
 * ÈË¹¤ÖÇÄÜÊµÑéÊÒ£ºDiscuz!Ó¦ÓÃÖÐÐÄÊ®´óÓÅÐã¿ª·¢Õß£¡
 * ²å¼þ¶¨ÖÆ ÁªÏµQQ594941227
 * From www.ailab.cn
 */
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
error_reporting(0);
loadcache('plugin');
$groups = unserialize($_G['cache']['plugin']['nimba_majia']['groups']);
$nm_table = DB::table('nimba_majia');
$member_table = DB::table('common_member');
$mjid = DB::result_first("SELECT `mjid` FROM $nm_table WHERE useruid='$_G[uid]'");

if((!$_G['uid']||!in_array($_G['groupid'], $groups))&&empty($mjid)) {
	showmessage('nimba_majia:usergroup_disabled');
}
if($_GET['pluginop'] == 'add' && submitcheck('adduser')) {
	loaducenter();
	$_GET['usernamenew']=addslashes($_GET['usernamenew']);
	$_GET['passwordnew']=addslashes($_GET['passwordnew']);
	if ($_GET['questionidnew']) {$_GET['questionidnew']=daddslashes($_GET['questionidnew']);}
	$ucresult = $_GET['questionidnew'] ? uc_user_login($_GET['usernamenew'], $_GET['passwordnew'],0,1,$_GET['questionidnew'],$_GET['answernew']) : uc_user_login($_GET['usernamenew'], $_GET['passwordnew']);
	if(empty($_GET['passwordnew']) || ($_GET['questionidnew'] && empty($_GET['answernew'])) || $ucresult[0]<=0) {
		showmessage('nimba_majia:adduser_fail',"javascript:history.back()", array('usernamenew' => $usernamenew));
	}
	$useruid=intval(DB::result_first("SELECT uid FROM ".DB::table('common_member')." WHERE username='".$_GET['usernamenew']."'"));
	$check_exist = DB::result_first("SELECT COUNT(*) FROM $nm_table WHERE `useruid` = $useruid");
	if ($check_exist) {
		showmessage('nimba_majia:adduser_exist', 'home.php?mod=spacecp&ac=plugin&id=nimba_majia:admincp', array('usernamenew' => $usernamenew));
	}else {
		if (!$mjid) {
			$mjid = DB::result_first("SELECT `mjid` FROM $nm_table ORDER BY `mjid` DESC") + 1;
			$currentdata = DB::fetch_first("SELECT * FROM `$member_table` WHERE `uid` = '$_G[uid]'");
			DB::insert('nimba_majia',array('mjid'=>$mjid,'username'=>$_G['username'],'useruid'=>$_G['uid'],'password'=>$currentdata['password']));
		}
		$usernamenew = strip_tags($_GET['usernamenew']);
		$usernamenew = stripslashes($usernamenew);
		$userdata = DB::fetch_first("SELECT * FROM `$member_table` WHERE `uid` = '$useruid'");
		DB::insert('nimba_majia',array('mjid'=>$mjid,'username'=>$usernamenew,'useruid'=>$useruid,'password'=>$userdata['password']));
		showmessage('nimba_majia:adduser_succeed', 'home.php?mod=spacecp&ac=plugin&id=nimba_majia:admincp', array('usernamenew' => $usernamenew));
	}
	
}elseif(!empty($_GET['delete'])&&$_GET['formhash'] == FORMHASH) {
	$_GET['delete']=daddslashes($_GET['delete']);
	$uids=implode(',',$_GET['delete']);
	DB::query("DELETE FROM $nm_table WHERE `mjid`='$mjid' AND `useruid` IN ($uids)");
	showmessage('nimba_majia:updateuser_succeed', 'home.php?mod=spacecp&ac=plugin&id=nimba_majia:admincp', array('usernamenew' => $usernamenew));
}
//Âí¼×ÁÐ±í
if ($mjid) {
	$repeatusers = DB::fetch_all("SELECT * FROM $nm_table WHERE `mjid`='$mjid' AND `useruid`<>'$_G[uid]'");
	foreach ($repeatusers as $key => $ru) {
		$userdata = DB::fetch_first("SELECT * FROM `$member_table` WHERE `uid` = '$ru[useruid]'");
		//Enicn_d modified 2015-05-20 11:20 forbidden administrator swtich from outlan.
		if (!preg_match('/^(192\.168\.).+/',$_G['clientip'])) {
			if ($userdata['adminid']==1 || in_array($userdata['groupid'], array(1,2,3))) {
				unset($repeatusers[$key]);
			}
		}
		if ($userdata['password']!=$ru['password']) {
			DB::delete('nimba_majia', '`useruid`='.$ru['useruid']);
			unset($repeatusers[$key]);
		}
	}
} else {
	$repeatusers = array();
}

?>