<?php
/**
 * [超级活动(xj_event.{modulename})] (C)2012-2099 Powered by 逍遥工作室.
 * 	Version: 1.0
 * 	Date: 2012-9-15 10:27
 */

if (!defined('IN_DISCUZ')) {
	exit('Access Denied');
} 
if ($_GET['act'] == 'manage') {
	$tmp = explode("\n", $_G['cache']['plugin']['xj_event']['event_template']);
	$event_template = array();
	foreach($tmp as $key => $value) {
		$ctmp = array();
		$ctmp = explode("|", $value);
		$ctmp[0] = str_replace("\r", '', $ctmp[0]);
		$ctmp[1] = str_replace("\r", '', $ctmp[1]);
		$event_template[] = $ctmp;
	} 
	include template('xj_event:event_template');
} else if ($_GET['act'] == 'delete') {
	if($_GET['formhash'] != $_G['formhash']){
		showmessage('submit_invalid');
	}
	$tmp = explode("\n", $_G['cache']['plugin']['xj_event']['event_template']);
	$ctmp = explode("|", $tmp[intval($_GET['tpid'])]);
	$ctmp[0] = $_GET['tpname'];
	unset($tmp[intval($_GET['tpid'])]); 
	$filename = "./source/plugin/xj_event/etpl/" . str_replace("\r", '', $ctmp[1]);
	if (unlink($filename)) {
		$_G['cache']['plugin']['xj_event']['event_template'] = implode('\n', $tmp);
		$pluginid = DB :: result_first("SELECT pluginid FROM " . DB :: table('common_plugin') . " where identifier='xj_event'");
		DB :: query("UPDATE " . DB :: table('common_pluginvar') . " SET value = '" . $_G['cache']['plugin']['xj_event']['event_template'] . "' WHERE pluginid = $pluginid and variable = 'event_template'");
		require_once libfile('function/cache');
		updatecache(array('plugin')); //更新缓存
		showmessage(lang('plugin/xj_event', 'mbsccg'),'plugin.php?id=xj_event:event_template&act=manage');
	}


} else if ($_GET['act'] == 'add' or $_GET['act'] == 'edit') {
	if ($_GET['act'] == 'edit') { // 编辑模板时调用
		$tmp = explode("\n", $_G['cache']['plugin']['xj_event']['event_template']);
		$ctmp = explode("|", $tmp[intval($_GET['tpid'])]);
		$tpname = $ctmp[0];
		$filename = "./source/plugin/xj_event/etpl/" . str_replace("\r", '', $ctmp[1]);
		$tpcontent = file_get_contents($filename);
	} 

	$discuz = &discuz_core :: instance();
	$discuz -> cachelist = $cachelist;
	$discuz -> init();
	$editorid = 'e';
	$_G['setting']['editoroptions'] = str_pad(decbin($_G['setting']['editoroptions']), 2, 0, STR_PAD_LEFT);
	$editormode = $_G['setting']['editoroptions'] {
		0} ;
	$allowswitcheditor = $_G['setting']['editoroptions'] {
		1} ;
	$editor = array('editormode' => $editormode,
		'allowswitcheditor' => $allowswitcheditor,
		'allowhtml' => 1,
		'allowhtml' => 1,
		'allowsmilies' => 1,
		'allowbbcode' => 1,
		'allowimgcode' => 2,
		'allowcustombbcode' => 0,
		'allowresize' => 1,
		'textarea' => 'message',
		'simplemode' => !isset($_G['cookie']['editormode_' . $editorid]) ? 1 : $_G['cookie']['editormode_' . $editorid],
		);
	loadcache('bbcodes_display');
	include template('xj_event:event_template');
} else if ($_GET['act'] == 'editfull') {
	if(!submitcheck('etsubmit')){
		showmessage('submit_invalid');	
	}
	$tmp = explode("\n", $_G['cache']['plugin']['xj_event']['event_template']);
	$ctmp = explode("|", $tmp[intval($_GET['tpid'])]);
	$ctmp[0] = $_GET['tpname'];
	$filename = str_replace("\r", '', $ctmp[1]);
	$filename = "./source/plugin/xj_event/etpl/" . $filename;
	$fp = fopen("$filename", "w+"); //打开文件指针，创建文件
	if (!is_writable($filename)) {
		showmessage(lang('plugin/xj_event', 'mbwjxrsbqjc'));;
	} else {
		$tmp[intval($_GET['tpid'])] = implode("|", $ctmp);
		$_G['cache']['plugin']['xj_event']['event_template'] = implode('\n', $tmp);
		$pluginid = DB :: result_first("SELECT pluginid FROM " . DB :: table('common_plugin') . " where identifier='xj_event'");
		DB :: query("UPDATE " . DB :: table('common_pluginvar') . " SET value = '" . $_G['cache']['plugin']['xj_event']['event_template'] . "' WHERE pluginid = $pluginid and variable = 'event_template'");
		require_once libfile('function/cache');
		updatecache(array('plugin')); //更新缓存
		fwrite($fp, $_GET['message']);
		fclose($fp); //关闭指针
		showmessage(lang('plugin/xj_event', 'mbxgcg'),'plugin.php?id=xj_event:event_template&act=manage');
	} 
} else if ($_GET['act'] == 'addfull') {
	if(!submitcheck('etsubmit')){
		showmessage('submit_invalid');	
	}
	$filename = randomfile(6) . '.htm';
	$tmp = explode("\n", $_G['cache']['plugin']['xj_event']['event_template']);
	$tmp[] = $_GET['tpname'] . '|' . $filename;

	$filename = "./source/plugin/xj_event/etpl/" . $filename;
	$fp = fopen("$filename", "w+"); //打开文件指针，创建文件
	if (!is_writable($filename)) {
		showmessage(lang('plugin/xj_event', 'mbwjxrsbqjc'));;
	} else {
		$_G['cache']['plugin']['xj_event']['event_template'] = implode('\n', $tmp);
		$pluginid = DB :: result_first("SELECT pluginid FROM " . DB :: table('common_plugin') . " where identifier='xj_event'");
		DB :: query("UPDATE " . DB :: table('common_pluginvar') . " SET value = '" . $_G['cache']['plugin']['xj_event']['event_template'] . "' WHERE pluginid = $pluginid and variable = 'event_template'");
		require_once libfile('function/cache');
		updatecache(array('plugin')); //更新缓存
		fwrite($fp, $_GET['message']);
		fclose($fp); //关闭指针
		showmessage(lang('plugin/xj_event', 'mbtjcg'),'plugin.php?id=xj_event:event_template&act=manage');
	} 
} else {
	function Post($url, $post = null) {
		$context = array();
		if (is_array($post)) {
			ksort($post);
			$context['http'] = array
			('method' => 'POST',
				'content' => http_build_query($post, '', '&'),
				);
		} 
		return file_get_contents($url, false, stream_context_create($context));
	} 
	$data = array();
	/**
	 * $data = array  
	 * (  
	 * 'url' => $_POST["url"],  
	 * );
	 */
	echo Post('source/plugin/xj_event/etpl/' . $_POST["file"], $data);
}// 生成随机文件名
function randomfile($length) {
	$hash = ''; // 文件头, 可以自定义
	$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
	$max = strlen($chars) - 1;
	mt_srand((double)microtime() * 1000000);
	for($i = 0; $i < $length; $i++) {
		$hash .= $chars[mt_rand(0, $max)];
	} 
	return $hash;
}
?>  