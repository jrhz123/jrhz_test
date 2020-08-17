<?php
/**
 *  [【超人】点赞(superman_zan.{modulename})] (C)2012-2099 Powered by 时创科技.
 *  Version: $VERSION
 *  Date: $DATE
 */
if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

$url ='http://addon.discuz.com/?ac=developer&id=51152';
$content = dfsockopen($url, 0, $post, '', FALSE, '', 120);
if ($content) {
	$content = iconv('gbk', CHARSET, $content);
	$content = str_replace('resource/developer', 'http://addon.discuz.com/resource/developer', $content);
	$content = str_replace('resource/plugin', 'http://addon.discuz.com/resource/plugin', $content);
	$content = str_replace('image/scrolltop.png', 'http://addon.discuz.com/image/scrolltop.png', $content);
	$content = preg_replace('/<div class="a_wp mbm cl">.*<div class="a_wp cl">/s', '<div class="a_wp cl">', $content);
	$content = preg_replace('/<ul class="a_tb cl">.*<div id="appdiv">/s', '<div id="appdiv">', $content);
	$content = preg_replace('/<div class="mtm type">.*<div id="appdiv">/s', '<div id="appdiv">', $content);
	$content = preg_replace('/<div id="footer">.*<\/div>/s', '', $content);
	echo $content;
}
