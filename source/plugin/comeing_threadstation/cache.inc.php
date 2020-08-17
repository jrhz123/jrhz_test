<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(DB::query("TRUNCATE  TABLE ".DB::table('comeing_threadstation'))){
	echo('<h1 style="font-size:18px; height:30px; lineheight:30px; color:#f00">Is OK!</h1>');
}
?>