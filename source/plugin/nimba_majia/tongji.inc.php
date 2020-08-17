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
echo '<script type="text/javascript" src="static/js/calendar.js"></script>';
if($_GET['start']) {
	$start=strtotime($_GET['start'].':00');
} else {
	$start=strtotime(date('Y-m-d',time()-432000).' 00:00:00');
}
if($_GET['end']) {
	$end=strtotime($_GET['end'].':00');
} else {
	$end=time();
}
$uns=trim($_GET['uns']);
$uns=explode(',',$uns);
if(!$uns[0]) {unset($uns[0]);}
showformheader("plugins&operation=config&identifier=nimba_majia&pmod=tongji");
showtableheader($langvars['posts_title'], 'nobottom');
showsetting('相关用户名', 'uns',$_GET['uns'], 'text','',0,'填写您本次要查询的用户名，多个用户名之间请用英文逗号分隔！');
showsetting($langvars['posts_start'], 'start',date('Y-m-d H:i',$start), 'calendar', '', 0,'', 1);	
showsetting($langvars['posts_end'], 'end',date('Y-m-d H:i',$end), 'calendar', '', 0,'', 1);	
showsubmit('editsubmit');
showtablefooter();
showformfooter();
if(count($uns)){
	showtableheader($langvars['posts_title_2']);
	showsubtitle(array($langvars['posts_main'],$langvars['posts_time'],$langvars['posts_thread'],$langvars['posts_post'],$langvars['posts_majia_num'],$langvars['posts_majia_total']));
	$majias=array();
	$majia_total=array();
	//for($time=$start;$time<=$end;$time+=86400){
	foreach($uns as $k=>$username) {
		$username = trim($username);
		$mjid = DB::result_first("SELECT `mjid` FROM ".DB::table("nimba_majia")." WHERE `username` = '$username'");
		for($time=$start;$time<=$end;$time+=86400){
			$day = date('Y-m-d',$time);
			$data = array('threads'=>0, 'posts'=>0);
			if(isset($majias[$mjid])){
				$users=$majias[$mjid];
			}else{
				$users=DB::fetch_all("select mjid,useruid,username from ".DB::table("nimba_majia")." where mjid='$mjid'");
				$majias[$mjid]=$users;
				$majia_total[$mjid]=count($users);
			}
			$majia_num=0;
			foreach($users as $k=>$user){
				$majia_data=getDataByDay($user['useruid'],$day);
				if($majia_data['threads']||$majia_data['posts']){
					$majia_num+=1;
					$data['threads']+=$majia_data['threads'];
					$data['posts']+=$majia_data['posts'];
				}
				if ($username == $user['username']) {
					$uid = $user['useruid'];
				}
			}
			showtablerow('', array('class="td_k"', 'class="td_k"', 'class="td_l"'), array(
				'<a href="home.php?mod=space&uid='.$uid.'" target="_blank">'.$username.'</a>',
				$day,
				$data['threads'],
				$data['posts'],
				$majia_num,
				$majia_total[$mjid],
			));
					
		}
	}
	showtablefooter();
}
//$day:2014-06-19
function getDataByDay($uid,$day){
	$start=strtotime($day.' 00:00:00');
	$end=$start+86399;
	$where="dateline>$start and dateline<$end";
	$posts=DB::result_first("select count(*) as num from ".DB::table("forum_post")." where $where and authorid='".$uid."' and first=0");
	$threads=DB::result_first("select count(*) as num from ".DB::table("forum_post")." where $where and authorid='".$uid."' and first=1");
	return array('threads'=>$threads,'posts'=>$posts);
}
?>