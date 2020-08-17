<?php
/*
 * Ö÷Ò³£ºhttp://addon.discuz.com/?@ailab
 * ÈË¹¤ÖÇÄÜÊµÑéÊÒ£ºDiscuz!Ó¦ÓÃÖÐÐÄÊ®´óÓÅÐã¿ª·¢Õß£¡
 * ²å¼þ¶¨ÖÆ ÁªÏµQQ594941227
 * From www.ailab.cn
 */
if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}
echo '<script type="text/javascript" src="static/js/calendar.js"></script>';
$langvar=lang('plugin/nimba_repliescp');
if($_GET['formhash']==formhash()&&!$_POST['delete']){
?>
	<div class="itemtitle">
		<ul class="tab1" style="margin-right:10px"></ul>
		<ul class="stepstat">
			<li id="step1"><?php echo lang('plugin/nimba_repliescp', 'step1');?></li>
			<li class="current" id="step2"><?php echo lang('plugin/nimba_repliescp', 'step2');?></li>
		</ul>
		<ul class="tab1"></ul>
	</div>
<?php
	$page=max(1,intval($_GET['page']));
	$perpage=max(20,intval($_GET['perpage']));
	$inforum=empty($_GET['inforum'])? null:intval($_GET['inforum']);
	if($_GET['style']=='page'){
		$starttime=empty($_GET['starttime'])? 0:intval($_GET['starttime']);
		$endtime=empty($_GET['endtime'])? $_G['timestamp']:intval($_GET['endtime']);
	}else{
		$starttime=strtotime($_POST['starttime'].':00');
		$endtime=strtotime($_POST['endtime'].':00');
	}
	$author=empty($_GET['author'])? null:addslashes(trim($_GET['author']));
	$keywords=empty($_GET['keywords'])? null:addslashes(trim($_GET['keywords']));
	if($keywords) $words=explode(',',$keywords);
	$first=0;
	$count=C::t('forum_post')->count_by_search(0, null,$keywords,null,$inforum,null,$author,$starttime,$endtime, null,$first);
	$result=C::t('forum_post')->fetch_all_by_search(0, null,$keywords,null,$inforum,null,$author,$starttime,$endtime, null,$first,($page-1)*$perpage,$perpage);
	$fromurl='plugins&operation=config&do=20&identifier=nimba_repliescp&pmod=repliescp&style=page&starttime='.$starttime.'&endtime='.$endtime.'&author='.$author.'&keywords='.$keywords.'&inforum='.$inforum.'&perpage='.$perpage;
	showformheader($fromurl.'&page='.$page);
	echo "<input type=\"hidden\" name=\"style\" value=\"page\">\n
	<input type=\"hidden\" name=\"starttime\" value=\"$starttime\">\n
	<input type=\"hidden\" name=\"endtime\" value=\"$endtime\">\n
	<input type=\"hidden\" name=\"author\" value=\"$author\">\n
	<input type=\"hidden\" name=\"keywords\" value=\"$keywords\">\n
	<input type=\"hidden\" name=\"inforum\" value=\"$inforum\">\n
	<input type=\"hidden\" name=\"perpage\" value=\"$perpage\">\n
	";
	showtableheader('admin', 'fixpadding');
	showsubtitle(array('', '链接','时间',lang('plugin/nimba_repliescp','repliesmessage'),'回复状态',lang('plugin/nimba_repliescp', 'edit'),lang('plugin/nimba_repliescp', 'author')));
	$invisible_status = array('0'=>'','-1'=>'回收站','-2'=>'待审核','-3'=>'审核忽略','-4'=>'','-5'=>'回收站');
	foreach($result as $k=>$post){
		if($words) $post['message']=wordcolor($post['message'],$words);
		showtablerow('', array('class="td25"'), array(
			"<input type=\"checkbox\" class=\"checkbox\" name=\"delete[]\" value=\"$post[pid]\">",
			"<a href=\"forum.php?mod=redirect&goto=findpost&ptid=$post[tid]&pid=$post[pid]\" target=\"_blank\">跳到该回复</a>",
			date('m-d H:i:s',$post['dateline']),
			//'<div style="overflow: auto; overflow-x: hidden; width:650px; margin-right:5px;">'.$post['message'].'</div>',
			'<div style="width:650px">'.$post['message'].'</div>',
			$invisible_status[$post['invisible']],
			"<a href=\"forum.php?mod=post&action=edit&fid=$post[fid]&tid=$post[tid]&pid=$post[pid]\" target=\"_blank\">".lang('plugin/nimba_repliescp', 'edit')."</a>",
			"<a href=\"".ADMINSCRIPT."?action=members&operation=ban&uid=$post[authorid]\">$post[author]</a>",
		));
	}
	showsubmit('forumsticksubmit', 'delete', 'del');
	showtablefooter();
	showformfooter();
	echo multi($count,$perpage, $page, ADMINSCRIPT.'?action='.$fromurl.'&searchsubmit=yes&formhash='.FORMHASH);
}elseif(submitcheck('forumsticksubmit')&&$_POST['delete']){
	$page=max(1,intval($_GET['page']));
	$perpage=max(10,intval($_GET['perpage']));
	$inforum=empty($_GET['inforum'])? null:intval($_GET['inforum']);
	if($_GET['style']=='page'){
		$starttime=empty($_GET['starttime'])? 0:intval($_GET['starttime']);
		$endtime=empty($_GET['endtime'])? $_G['timestamp']:intval($_GET['endtime']);
	}else{
		$starttime=strtotime($_POST['starttime'].':00');
		$endtime=strtotime($_POST['endtime'].':00');
	}
	$author=empty($_GET['users'])? null:addslashes(trim($_GET['users']));
	$keywords=empty($_GET['keywords'])? null:addslashes(trim($_GET['keywords']));
	require_once libfile('function/delete');
	foreach($_POST['delete'] as $k=>$pid){
		if($pid) $pids[]=intval($pid);
	}
	if($pids){
		//Enicn_d modified 2015-05-08 13:29 changed the delete action to update
		$query = 'UPDATE '.DB::table('forum_post').' SET `invisible` = -5 WHERE `pid` IN ('.implode(',', $pids).')';
		DB::query($query);
		$num = DB::affected_rows();
		cpmsg(lang('plugin/nimba_repliescp','deleted1').'<font color="red">'.$num.'</font>'.lang('plugin/nimba_repliescp','deleted2'),'action=plugins&operation=config&do=20&identifier=nimba_repliescp&pmod=repliescp&style=page&starttime='.$starttime.'&endtime='.$endtime.'&author='.$author.'&keywords='.$keywords.'&inforum='.$inforum.'&perpage='.$perpage.'&page='.$page.'&searchsubmit=yes&formhash='.FORMHASH,'succeed');
	}
}else{
$version=$_G['setting']['plugins']['version']['nimba_repliescp'];
?>
<div class="itemtitle">
	<ul class="tab1" style="margin-right:10px"></ul>
	<ul class="stepstat">
		<li class="current" id="step1"><?php echo lang('plugin/nimba_repliescp', 'step1');?></li>
		<li id="step2"><?php echo lang('plugin/nimba_repliescp', 'step2');?></li>
	</ul>
	<ul class="tab1"></ul>
</div>
<table class="tb tb2 " id="tips">
	<tbody>
		<tr>
			<th class="partition"><?php echo lang('plugin/nimba_repliescp', 'tips');?></th>
		</tr>
		<tr>
			<td class="tipsblock" s="1">
				<ul id="tipslis">
					<li><?php echo lang('plugin/nimba_repliescp', 'tip1');?></li>
					<li><?php echo lang('plugin/nimba_repliescp', 'tip2');?></li>
					<?php
						if(substr($version,-1)!=2){
					?>
					<li><?php echo lang('plugin/nimba_repliescp', 'tip3');?></li>.
					<?php
						}
					?>
				</ul>
			</td>
		</tr>
	</tbody>
</table>
<?php
	showformheader("plugins&do=20&operation=config&identifier=nimba_repliescp&pmod=repliescp");
	showtableheader($langvar['appname'], 'nobottom');
	showsetting($langvar['tip16'], array('inforum',forumSelect()),0, 'select');
	showsetting($langvar['tip4'], array('perpage',numSelect()),20, 'select');
	showsetting($langvar['tip6'], 'starttime',date('Y-m',time()).'-01 00:00', 'calendar', '', 0,$langvar['tip7'], 1);
	showsetting($langvar['tip8'], 'endtime',date('Y-m-d H:i',time()+600), 'calendar', '', 0,$langvar['tip9'], 1);
	showsetting($langvar['tip10'], 'users','', 'text','',0,$langvar['tip11']);
	showsetting($langvar['tip12'], 'keywords','', 'text','',0,$langvar['tip13']);
	showsubmit('searchsubmit');
	showtablefooter();
	showformfooter();
}
?>
<script type="text/JavaScript">
//Enicn_d modifed 2015-05-08 9:35 added deletion alert
var chkall_obj = document.getElementsByName("chkall");
var cpform_obj = document.getElementById("cpform");

if (chkall_obj.length){
	chkall_obj[0].onclick=function(){
		if(this.checked){
			if(!confirm("是否确定全部选择？")){
				this.checked=false;
				return 0;
			}
		}
		checkAll('prefix', this.form, 'delete');
	}
}
if (cpform_obj){
	cpform_obj.onsubmit=function(){
		if (chkall_obj.length){
			if (chkall_obj[0].checked==true) {
				if(!confirm("是否确定全部删除？")){
					if(event.preventDefault){
						event.preventDefault();
					}else{
						event.returnValue=false;
					}
				}
			}
		}
	}
}
</script>
<?php
function wordcolor($message,$words){
	foreach($words as $k=>$word){
		$message=str_replace($word,'<font color="red">'.$word.'</font>',$message);
	}
	return $message;
}
function forumSelect(){
	loadcache('plugin');
	global $_G,$langvar;
	$forums =C::t('forum_forum')->fetch_all_fids();
	$arr=array(array(0,$langvar['allforums']));
	foreach($forums as $k=>$forum){
		if($forum['fid']) $arr[]=array($forum['fid'],$forum['name']);
	}
	return $arr;
}

function numSelect(){
	loadcache('plugin');
	global $langvar;
	$arr[]=array(20,$langvar['tip4_20']);
	$arr[]=array(50,$langvar['tip4_50']);
	$arr[]=array(100,$langvar['tip4_100']);
	return $arr;
}
?>
