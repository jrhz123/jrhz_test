<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: notify_credit.php 29236 2012-03-30 05:34:47Z chenmengshu $
 */
define('IN_API', true);
define('CURSCRIPT', 'api');





require '../../../source/class/class_core.php';
require '../../../source/function/function_forum.php';
include 'include/sms_func.php';

$discuz = C::app();
$discuz->init();






$apitype = empty($_GET['attach']) || !preg_match('/^[a-z0-9]+$/i', $_GET['attach']) ? 'alipay' : $_GET['attach'];
//$_G['siteurl'] = dhtmlspecialchars('http://'.$_SERVER['HTTP_HOST'].preg_replace("/\/+(source\/plugin\/xj_event)?\/*$/i", '', substr($PHP_SELF, 0, strrpos($PHP_SELF, '/'))).'/');
//$PHP_SELF = $_SERVER['PHP_SELF'];
$_G['siteurl'] = str_replace('source/plugin/xj_event/','',$_G['siteurl']);




if($apitype == 'alipay'){
	list($ec_contract, $ec_securitycode, $ec_partner, $ec_creditdirectpay) = explode("\t", authcode($_G['setting']['ec_contract'], 'DECODE', $_G['config']['security']['authkey']));
	define('DISCUZ_PARTNER', $ec_partner);
	define('DISCUZ_SECURITYCODE', $ec_securitycode);
	define('DISCUZ_DIRECTPAY', $ec_creditdirectpay);
	
	
	$notifydata = alipay_notifycheck();
	$orderid = $notifydata['order_no'];
	$tradeno = $notifydata['trade_no'];
	$trade_status = $notifydata['trade_status'];
	$notify_time = $_G['timestamp'];
	if($notifydata['trade_status'] == 'WAIT_BUYER_PAY'){   //等待支付
		$buyer_email = $_GET['buyer_email'];
		DB::query("UPDATE ".DB::table('xj_eventpay_log')." SET paystate=1, trade_status='$trade_status', tradeno='$tradeno', buyer_email='$buyer_email', notify_time=$notify_time  WHERE orderid = '$orderid'");
	}elseif($notifydata['trade_status'] == 'WAIT_SELLER_SEND_GOODS'){         //买家已付款，等待发货
		$pay_time = $_G['timestamp'];
		DB::query("UPDATE ".DB::table('xj_eventpay_log')." SET paystate=2, trade_status='$trade_status', pay_time=$pay_time, notify_time=$notify_time WHERE orderid = '$orderid'");
		$item = DB::fetch_first("SELECT applyid FROM ".DB::table('xj_eventpay_log')." WHERE orderid = '$orderid'");
		$applyid = $item['applyid'];
		DB::query("UPDATE ".DB::table('xj_eventapply')." SET pay_state=1 WHERE applyid = $applyid");
	}elseif($notifydata['trade_status'] == 'WAIT_BUYER_CONFIRM_GOODS'){
		DB::query("UPDATE ".DB::table('xj_eventpay_log')." SET trade_status='$trade_status', notify_time=$notify_time WHERE orderid = '$orderid'");
	}elseif($notifydata['trade_status'] == 'TRADE_FINISHED' || $notifydata['trade_status'] == 'TRADE_SUCCESS'){
		DB::query("UPDATE ".DB::table('xj_eventpay_log')." SET paystate=3, trade_status='$trade_status', notify_time=$notify_time WHERE orderid = '$orderid'");
		$item = DB::fetch_first("SELECT applyid FROM ".DB::table('xj_eventpay_log')." WHERE orderid = '$orderid'");
		$applyid = $item['applyid'];
		DB::query("UPDATE ".DB::table('xj_eventapply')." SET pay_state=1,verify=1 WHERE applyid = $applyid");
		if(!$notifydata['location']){
			paysmssend($applyid);
		}
	}elseif($notifydata['trade_status'] == 'TRADE_CLOSED'){
		DB::query("UPDATE ".DB::table('xj_eventpay_log')." SET paystate=9, trade_status='$trade_status', notify_time=$notify_time WHERE orderid = '$orderid'");
	}
}elseif($apitype == 'tenpay'){
	
	define('DISCUZ_PARTNER', $_G['setting']['ec_tenpay_bargainor']);
	define('DISCUZ_SECURITYCODE', $_G['setting']['ec_tenpay_key']);
	define('DISCUZ_AGENTID', '1204737401');
	define('DISCUZ_TENPAY_OPENTRANS_CHNID', $_G['setting']['ec_tenpay_opentrans_chnid']);
	define('DISCUZ_TENPAY_OPENTRANS_KEY', $_G['setting']['ec_tenpay_opentrans_key']);
	require '../../../api/trade/api_tenpay.php';
	
	$notifydata = tenpay_notifycheck();
	$orderid = $notifydata['order_no'];
	$tradeno = $notifydata['trade_no'];
	$trade_status = $notifydata['trade_status'];
	$notify_time = $_G['timestamp'];
	if($orderid){
	  if($trade_status == 0){
		  $trade_status = intval($trade_status);
		  DB::query("UPDATE ".DB::table('xj_eventpay_log')." SET paystate=3, trade_status='$trade_status',tradeno=$tradeno,notify_time=$notify_time WHERE orderid = '$orderid'");
		  $item = DB::fetch_first("SELECT applyid FROM ".DB::table('xj_eventpay_log')." WHERE orderid = '$orderid'");
		  $applyid = $item['applyid'];
		  DB::query("UPDATE ".DB::table('xj_eventapply')." SET pay_state=1,verify=1 WHERE applyid = $applyid");
		  paysmssend($applyid);
	  }
	}
	
}

function paysmssend($applyid){
	global $_G;
	$apply = DB::fetch_first("SELECT * FROM ".DB::table('xj_eventapply')." WHERE applyid = $applyid");
	$thread = DB::fetch_first("SELECT authorid,userfield,setting,subject,starttime FROM ".DB::table('forum_thread')." A,".DB::table('xj_event')." B WHERE A.tid='".$apply['tid']."' and A.tid = B.tid");
	$setting = unserialize($thread['setting']);
	$event_starttime = dgmdate($thread['starttime'],'dt');
	if($setting['seccode'] == 1){		
		$message = cutstr($thread['subject'],30).'活动报名成功，人数:'.$apply['applynumber'].'人 验证码:'.$apply['seccode'].' 活动时间:'.$event_starttime;
		$sendtype = '报名验证码短信';
		if($_G[charset]=='gbk'){
			$message = diconv($message,'UTF-8','GBK');
			$sendtype = diconv($sendtype,'UTF-8','GBK');
		}
		xjsendsms(array($apply['mobile']),$message,$sendtype);
		sendpm($apply['uid'],'',$message,$thread['authorid']);
	}
}



//调试记录开始
/*
$notifydatastr = print_r($notifydata,true)."-------------------notifydata  \r\n";
$notifydatastr = $notifydatastr.print_r($_GET,true)."---------GET \r\n";
$notifydatastr = $notifydatastr.print_r($_POST,true)."---------POST \r\n\r\n\r\n";
$notifydatastr = $notifydatastr.DISCUZ_PARTNER.'|'.DISCUZ_SECURITYCODE."---------常量 \r\n\r\n\r\n";;
$filename = "./pay.txt";
$fp = fopen("$filename", "a"); //打开文件指针，创建文件
fwrite($fp, $notifydatastr);
fclose($fp); //关闭指针
*/
//调试记录结束





if($notifydata['location']) {
	$url = rawurlencode('home.php?mod=spacecp&ac=credit');
	if($apitype == 'tenpay') {
		echo <<<EOS
<meta name="TENCENT_ONLINE_PAYMENT" content="China TENCENT">
<html>
<body>
<script language="javascript" type="text/javascript">
window.location.href='$_G[siteurl]plugin.php?id=xj_event:event_pay&action=paysucceed';
</script>
</body>
</html>
EOS;
	} else {
		dheader('location: '.$_G['siteurl'].'plugin.php?id=xj_event:event_pay&action=paysucceed');
	}
} else {
	exit($notifydata['notify']);
}


function alipay_notifycheck() {
	global $_G;
	if(!empty($_POST)) {
		$notify = $_POST;
		$location = FALSE;
	} elseif(!empty($_GET)) {
		$notify = $_GET;
		$location = TRUE;
	} else {
		exit('Access Denied');
	}
	

	if(dfsockopen("http://notify.alipay.com/trade/notify_query.do?partner=".DISCUZ_PARTNER."&notify_id=".$notify['notify_id'], 60) !== 'true') {
		exit('Access Denied');
	}

	
	if(!DISCUZ_SECURITYCODE) {
		exit('Access Denied');
	}
	ksort($notify);
	$sign = '';
	foreach($notify as $key => $val) {
		if($key != 'sign' && $key != 'sign_type') $sign .= "&$key=$val";
	}
	if($notify['sign'] != md5(substr($sign,1).DISCUZ_SECURITYCODE)) {
		exit('Access Denied');
	}
	return array(
    	'order_no' 	=> $notify['out_trade_no'],
        'trade_no'	=> $notify['trade_no'],
        'trade_status' 	=> $notify['trade_status'],
        'price' 	=> $notify['total_fee'],
		'notify'	=> 'success',
		'location'	=> $location
	);
	/*
	if(($type == 'credit' || $type == 'invite') && (!DISCUZ_DIRECTPAY && $notify['notify_type'] == 'trade_status_sync' && ($notify['trade_status'] == 'WAIT_SELLER_SEND_GOODS' || $notify['trade_status'] == 'TRADE_FINISHED') || DISCUZ_DIRECTPAY && ($notify['trade_status'] == 'TRADE_FINISHED' || $notify['trade_status'] == 'TRADE_SUCCESS'))
		|| $type == 'trade' && $notify['notify_type'] == 'trade_status_sync') {
		return array(
			'validator'	=> TRUE,
			'status' 	=> trade_getstatus(!empty($notify['refund_status']) ? $notify['refund_status'] : $notify['trade_status'], 1),
			'order_no' 	=> $notify['out_trade_no'],
			'price' 	=> !DISCUZ_DIRECTPAY && $notify['price'] ? $notify['price'] : $notify['total_fee'],
			'trade_no'	=> $notify['trade_no'],
			'notify'	=> 'success',
			'location'	=> $location
			);
	} else {
		return array(
			'validator'	=> FALSE,
			'notify'	=> 'fail',
			'location'	=> $location
			);
	}
	*/
}

function tenpay_notifycheck() {
	global $_G;



    if(!DISCUZ_SECURITYCODE) {
        exit('Access Denied');
    }
    
   
    $resHandler = new ResponseHandler();
    $resHandler->setKey(DISCUZ_SECURITYCODE);
    $resHandler->setParameter("bankname", "");
    if($resHandler->isTenpaySign() && DISCUZ_PARTNER == $_GET['partner']) {
        return array(
            'validator'	=> isset($_GET['trade_state']) ? !$_GET['trade_state'] : 0,
            'trade_state' => $_GET['trade_state'],
            'order_no' 	=> $_GET['out_trade_no'],
            'trade_no'	=> isset($_GET['transaction_id']) ? $_GET['transaction_id'] : '',
            'price' 	=> $_GET['total_fee'] / 100,
            'bargainor_id' => $_GET['partner'],
            'notify'	=> 'Success',
            'location'	=> false,
            );
    }
}







?>