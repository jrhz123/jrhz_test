<?php

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
@$m  = $_GET['m'] ? $_GET['m'] : 'sns';

$dir_tmp = explode('/', __DIR__);
$dir_tmp[count($dir_tmp)];
$identifier = $dir_tmp[count($dir_tmp)-1];
$plugin_static = "source/plugin/$identifier/";

//需在 /source/plugin/插件标识符/template 下创建 touch => ./ 和 mobile => ./ 两个 link链接，否则手机无法浏览
//由于调用了diy模板数据，需在 /data/diy/source/plugin/插件标识符/template 下进行同样操作

$navtitle = '今日惠州网';

//$_G['style']['tplfile'] = "$identifier/$m";
/*$_G['style']['directory'] = "./source/plugin/$identifier/template/";
$_G['style']['tpldir'] = "./source/plugin/$identifier/template/";
$_G['style']['tpldirectory'] = "./source/plugin/$identifier/template/";*/
include template("diy:$identifier/".$m);


?>