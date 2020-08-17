<?php

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}
require_once libfile('function/delete');

$thread_table = DB::table('forum_thread');
$post_table = DB::table('forum_post');
$days = 30;
$timestamp = daysago($days);

$type = $_GET['type'];
if ($type == 'thread') :
	$tids = array();
	$query = "SELECT `tid` FROM $thread_table WHERE `dateline` < $timestamp AND `displayorder` = -1";
	$threadlist = DB::fetch_all($query);
	foreach ($threadlist as $key => $value) {
		$tids[] = $value['tid'];
	}
	$num = deletethread($tids, TRUE, TRUE);
	cpmsg('已成功删除 <font color="red">'.$num.'</font> 条主题。','action=plugins&operation=config&identifier=recyclebin&pmod=clean&formhash='.FORMHASH,'succeed');
elseif ($type == 'post') :
	$pids = array();
	$query = "SELECT `pid` FROM $post_table WHERE `dateline` < $timestamp AND `invisible` = -5";
	$postlist = DB::fetch_all($query);
	foreach ($postlist as $key => $value) {
		$pids[] = $value['pid'];
	}
	$num = deletepost($pids);
	cpmsg('已成功删除 <font color="red">'.$num.'</font> 条回复。','action=plugins&operation=config&identifier=recyclebin&pmod=clean&formhash='.FORMHASH,'succeed');
else :

$cpgroupid = C::t('common_admincp_member')->fetch($_G['uid']);
if (($cpgroupid['cpgroupid']==6 || $cpgroupid['cpgroupid']==0) && $cpgroupid['uid'] != 0) :
$thead_count = DB::result_first("SELECT COUNT(*) FROM $thread_table WHERE `dateline` < $timestamp AND `displayorder` = -1");
$post_count = DB::result_first("SELECT COUNT(*) FROM $post_table WHERE `dateline` < $timestamp AND `invisible` = -5");
showtableheader('清理', 'fixpadding');
?>

<tr>
	<td><button id="cleanthread">清空 <?=$days?> 天前的<strong>主题</strong></button>
		<p>回收站中 <?=$days?> 天前的主题数：<?=$thead_count?></p>
	</td>
	<td><button id="cleanpost">清空 <?=$days?> 天前的<strong>回复</strong></button>
		<p>回收站中 <?=$days?> 天前的回复数：<?=$post_count?></p>
	</td>
</tr>

<?php
showtablefooter();
?>

<script type="text/javascript">
	var warning = '【注意】此操作将彻底删除数据，不可还原，是否确定？【注意】'
	document.getElementById('cleanthread').onclick=function(){
		if (confirm('是否确定清空回收站中 <?=$days?> 天前的主题？')) {
			if (confirm(warning)) {
				location.href='<?=ADMINSCRIPT?>?action=plugins&operation=config&identifier=recyclebin&pmod=clean&type=thread';
			}
		}
	}
	document.getElementById('cleanpost').onclick=function(){
		if (confirm('是否确定清空回收站中 <?=$days?> 天前的回复？')) {
			if (confirm(warning)) {
				location.href='<?=ADMINSCRIPT?>?action=plugins&operation=config&identifier=recyclebin&pmod=clean&type=post';
			}
		}
	}
</script>

<?php
endif;
endif;

function daysago($days){
	return TIMESTAMP-(86400*$days);
}

?>