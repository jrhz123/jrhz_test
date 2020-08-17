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
$pagenum=20;
$page=max(1,intval($_GET['page']));
$mjid = intval($_GET['mjid']);
if ($mjid) {
	$count=DB::result_first("select count(*) from ".DB::table("nimba_majia")." WHERE `mjid` = $mjid ");
	$data=DB::fetch_all("select * from ".DB::table("nimba_majia")." WHERE `mjid` = $mjid order by `mjid` asc limit ".($page-1)*$pagenum.",$pagenum");
}else {
	$count=DB::result_first("select count(*) from ".DB::table("nimba_majia")." ");
	$data=DB::fetch_all("select * from ".DB::table("nimba_majia")." order by `mjid` asc limit ".($page-1)*$pagenum.",$pagenum");
}
showtableheader(lang('plugin/nimba_majia', 'appname'));
showsubtitle(array('','马甲序列号',lang('plugin/nimba_majia', 'repeat'),''));
foreach($data as $item) {
	showtablerow('', array('class="td25"', 'class="td_k"', 'class="td_l"'), array(
		'',
		'<a href="admin.php?action=plugins&operation=config&do=16&identifier=nimba_majia&pmod=data&mjid='.$item['mjid'].'" target="_blank">'.$item['mjid'].'</a>',
		'<a href="home.php?mod=space&uid='.$item['useruid'].'" target="_blank">'.$item['username'].'</a>',
		'',
	));
			
}
showtablefooter();
echo multi($count,$pagenum,$page,ADMINSCRIPT."?action=plugins&operation=config&do=$pluginid&identifier=nimba_majia&pmod=data&mjid=$mjid");

?>