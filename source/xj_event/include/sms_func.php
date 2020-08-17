<?php 

if(file_exists($xjsms = DISCUZ_ROOT.'./data/sysdata/cache_xjsms.php')) {
	@include $xjsms;
}

function regsms(){
	global $username,$password,$realname,$mobile,$email;
	$return = '';
	$message = dfsockopen("http://xjsms.sinaapp.com/reg.php?act=save",0,"username=$username&password=$password&realname=$realname&mobile=$mobile&email=$email");
	$return = $message;
	//exit($message);
	return $return;	
}



//╩Ях║╤лпесЮ╤Н
function getsmsoverage(){
	global $_G,$xjsms;
	$return = '';
	$username = $xjsms['username'];
	$password = md5($xjsms['password']);
	$message = dfsockopen("http://xjsms.sinaapp.com/sms.php",0,"action=overage&username=$username&password=$password");
	$return = $message;
	return $return;
}

function xjsendsms($mobile = array(),$content,$sendtype = 'unknown',$sendtime = ''){
	global $_G,$xjsms;
	$username = $xjsms['username'];
	$password = md5($xjsms['password']);
	$content = $content.$xjsms['signature'];

	if($_G[charset]=='gbk'){
		$sendcontent = $content;
	}else{
		$sendcontent = diconv($content,'UTF-8','GBK');
	}

	$return = '';
	if(count($mobile)>0){
		$mobilestr = implode(",",$mobile);
		$message = dfsockopen("http://xjsms.sinaapp.com/sms.php",0,"action=send&username=$username&password=$password&mobile=".$mobilestr."&sendtime=".$sendtime."&content=".$sendcontent);
		$return = $message;
	}
	$sendlog = array();
	if($return == 'ok'){
		$sendlog['sendstate'] = 'success';
	}else{
		$sendlog['sendstate'] = $return;
	}
	$sendlog['uid'] = $_G['uid'];
	$sendlog['sendtype'] = $sendtype;
	$sendlog['sendcount'] = count($mobile);
	$sendlog['sendtime'] = $_G['timestamp'];
	$sendlog['sendcontent'] = $content;
	$sendlog['sendnumber'] = implode(',',$mobile);
	DB::insert('xj_event_sms_log',$sendlog);
	
	
	return $return;
}


?>