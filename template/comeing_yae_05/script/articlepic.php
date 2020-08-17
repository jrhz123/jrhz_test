<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
$value['pic']=str_replace(array('.thumb.jpg','data/attachment/'),'',$value['pic']);
$thumbfile=$value['pic'].'_comeing.jpg';
$attachdir=$value['remote']?$_G['setting']['ftp']['attachurl']:$_G['setting']['attachdir'];
$attachurl=$value['remote']?$_G['setting']['ftp']['attachurl']:$_G['setting']['attachurl'];


if(file_exists($attachdir.$thumbfile)) {
	$value['cover'] = $attachurl.$thumbfile;
}else{
	require_once libfile('class/image');
	$img = new image;
	$filename = $attachdir.$value['pic'];
	if($img->Thumb($filename, $thumbfile, 180, 130, 'fixwr')) {		
		$value['cover'] = $attachurl.$thumbfile;
	}
}

?>