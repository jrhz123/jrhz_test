<?php
/*
 * 主页：http://addon.discuz.com/?@ailab
 * 人工智能实验室：Discuz!应用中心十大优秀开发者！
 * 插件定制 联系QQ594941227
 * From www.ailab.cn
 */
if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}
loadcache('plugin');
$langvars=lang('plugin/nimba_majia');

if(submitcheck('addsubmit')){
	$username = trim($_POST['username']);
	if($username){
		$nm_table = DB::table('nimba_majia');
		$mjid = DB::result_first("SELECT `mjid` FROM $nm_table WHERE `username` = '$username'");
		if (!$mjid) {
			$mjid = DB::result_first("SELECT `mjid` FROM $nm_table ORDER BY `mjid` DESC") + 1;
		}
		$uns = explode(",",str_replace(' ', '',$_POST['uns']));
		$users = C::t('common_member')->fetch_all_uid_by_username($uns);
		foreach($users as $un=>$useruid){
			if($un && $useruid){
				$count=DB::result_first("select count(*) from ".DB::table('nimba_majia')." where `mjid`='$mjid' and username='$un'");
				if(!$count) DB::insert('nimba_majia',array('mjid'=>$mjid,'username'=>$username,'useruid'=>$useruid));
			}
		}
		cpmsg($langvars['updateuser_succeed'],'action=plugins&operation=config&identifier=nimba_majia', 'succeed');
	}else{
		cpmsg($langvars['updateuser_error']);
	}
}else{
	showformheader("plugins&operation=config&identifier=nimba_majia&pmod=add");
	showtableheader($langvars['addcontent'], 'nobottom');	
	showsetting('目标用户名', 'username','', 'text','',0,'填写您本次要添加马甲的目标用户，马甲将关联到这一用户的马甲序列当中。');
	showsetting($langvars['uidlist'],'uids','','textarea','', 0,$langvars['uidlistinfo']);
	showsubmit('addsubmit');
	showtablefooter();
	showformfooter();
}

?>