<?php
/**
 *	[³¬¼¶»î¶¯(xj_event.{modulename})] (C)2012-2099 Powered by åÐÒ£¹¤×÷ÊÒ.
 *	Version: 1.0
 *	Date: 2012-9-15 10:27
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(!$_G['uid']) {
	showmessage('not_loggedin', NULL, array(), array('login' => 1));
}
if($_GET['action'] == 'paysucceed'){
	showmessage(lang('plugin/xj_event', 'hdfyzfwc'),'plugin.php?id=xj_event:event_center');
}

if($_GET['bank_type'] === '0'){
	$_GET['bank_type'] = 'tenpay';
}

if(!$_GET['bank_type']){
	$tid = intval($_GET['tid']);
	$item = DB::fetch_first("SELECT A.use_cost,A.reserve_cost,B.subject FROM ".DB::table('xj_event')." A,".DB::table('forum_thread')." B WHERE A.tid = '$tid' and A.tid=B.tid"); //Enicn_d 2015-11-03 added reserve_cost
	$subject = $item['subject'];
	$use_cost = $item['use_cost'];
	$reserve_cost = $item['reserve_cost']; //Enicn_d 2015-11-03 added reserve_cost
	$item = DB::fetch_first("SELECT applyid,applynumber FROM ".DB::table('xj_eventapply')." WHERE tid = '$tid' and uid=".$_G['uid']);
	$applynumber = $item['applynumber'];
	$totalprice = number_format($use_cost*$applynumber,2);
	$applyid = $item['applyid'];
	include template('xj_event:event_pay');
}else{
	if(!submitcheck('paysubmit')){
		showmessage('submit_invalid');
	}
	$applyid = intval($_GET['applyid']);
	$uid = intval($_G['uid']);
	$item = DB::fetch_first("SELECT tid,applyid,applynumber FROM ".DB::table('xj_eventapply')." WHERE applyid = $applyid and uid=".$_G['uid']);
	$tid = $item['tid'];
	$applyid = $item['applyid'];
	$applynumber =  $item['applynumber'];
	$costtype = trim($_POST['costtype']);
	if ($costtype == 'reserve_cost') {
		$item = DB::fetch_first("SELECT A.reserve_cost,B.subject FROM ".DB::table('xj_event')." A,".DB::table('forum_thread')." B WHERE A.tid = '$tid' and A.tid=B.tid");
		$cost = $item['reserve_cost'];
	} else {
		$item = DB::fetch_first("SELECT A.use_cost,B.subject FROM ".DB::table('xj_event')." A,".DB::table('forum_thread')." B WHERE A.tid = '$tid' and A.tid=B.tid");
		$cost = $item['use_cost'];
	}
	$subject = $item['subject'];
	$price = number_format($cost*$applynumber,2);
	//$paytype = addslashes($_GET['bank_type']);
	$paytype = is_numeric($_GET['bank_type']) ? 'tenpay' : $_GET['bank_type'];
	$create_time = $_G['timestamp'];
	if(empty($uid) || empty($tid) || empty($price)){
		exit('Access Denied');
	}


	$item = DB::fetch_first("SELECT orderid FROM ".DB::table('xj_eventpay_log')." WHERE applyid = $applyid");
	$orderid = dgmdate(TIMESTAMP, 'YmdHis').random(18);  //´´½¨Ö§¸¶¶©µ¥ºÅ
	DB::query("INSERT INTO ".DB::table('xj_eventpay_log')."
	(applyid, uid, tid, orderid, paytype, costtype, subject, price, total_fee, create_time)
	VALUES
	('$applyid', '$uid', '$tid', '$orderid', '$paytype', '$costtype', '$subject', '$use_cost', '$price', '$create_time')"); //Enicn_d 2015-11-03 added costtype
}


if($paytype=='alipay'){
	list($ec_contract, $ec_securitycode, $ec_partner, $ec_creditdirectpay) = explode("\t", authcode($_G['setting']['ec_contract'], 'DECODE', $_G['config']['security']['authkey']));
	define('DISCUZ_PARTNER', $ec_partner);
	define('DISCUZ_SECURITYCODE', $ec_securitycode);
	define('DISCUZ_DIRECTPAY', $ec_creditdirectpay);
	define('STATUS_SELLER_SEND', 4);
	define('STATUS_WAIT_BUYER', 5);
	define('STATUS_TRADE_SUCCESS', 7);
	define('STATUS_REFUND_CLOSE', 17);

	$args = array(
	  'subject'     => $_G['setting']['bbname'].' '.$_G['member']['username'].' '.$subject.' '.lang('plugin/xj_event', 'huodongbm'),
	  'body'      => lang('plugin/xj_event', 'hdbmfk').' '.$price.lang('plugin/xj_event', 'yuan').$_G['clientip'],
	  'service'     => 'trade_create_by_buyer',
	  'partner'     => DISCUZ_PARTNER,
	  'notify_url'    => $_G['siteurl'].'source/plugin/xj_event/event_pay_notify.php',
	  'return_url'    => $_G['siteurl'].'source/plugin/xj_event/event_pay_notify.php',
	  'show_url'    => $_G['siteurl'],
	  '_input_charset'  => CHARSET,
	  'out_trade_no'    => $orderid,
	  'price'     => $price,
	  'quantity'    => 1,
	  'seller_email'    => $_G['setting']['ec_account'],
	);
	if(DISCUZ_DIRECTPAY) {
	  $args['service'] = 'create_direct_pay_by_user';
	  $args['payment_type'] = '1';
	} else {
	  $args['logistics_type'] = 'EXPRESS';
	  $args['logistics_fee'] = 0;
	  $args['logistics_payment'] = 'SELLER_PAY';
	  $args['payment_type'] = 1;
	}
	ksort($args);
	$urlstr = $sign = '';
	foreach($args as $key => $val) {
		$sign .= '&'.$key.'='.$val;
		$urlstr .= $key.'='.rawurlencode($val).'&';
	}
	$sign = substr($sign, 1);
	$sign = md5($sign.DISCUZ_SECURITYCODE);
	if (!defined('IN_MOBILE')) {
		header('Location: https://www.alipay.com/cooperate/gateway.do?'.$urlstr.'sign='.$sign.'&sign_type=MD5');
	} else {
		echo 'https://www.alipay.com/cooperate/gateway.do?'.$urlstr.'sign='.$sign.'&sign_type=MD5';
	}
}elseif($paytype=='tenpay'){
	if($_GET['bank_type'] == 'tenpay'){
		$_GET['bank_type'] = '0';
	}
	$bank = $_GET['bank_type'];

	define('DISCUZ_PARTNER', $_G['setting']['ec_tenpay_bargainor']);
	define('DISCUZ_SECURITYCODE', $_G['setting']['ec_tenpay_key']);
	define('DISCUZ_AGENTID', '1204737401');

	define('DISCUZ_TENPAY_OPENTRANS_CHNID', $_G['setting']['ec_tenpay_opentrans_chnid']);
	define('DISCUZ_TENPAY_OPENTRANS_KEY', $_G['setting']['ec_tenpay_opentrans_key']);

	define('STATUS_SELLER_SEND', 3);
	define('STATUS_WAIT_BUYER', 4);
	define('STATUS_TRADE_SUCCESS', 5);
	define('STATUS_REFUND_CLOSE', 9);
	include_once DISCUZ_ROOT . './source/class/class_chinese.php';
	include_once DISCUZ_ROOT . './api/trade/api_tenpay.php';
	$date = dgmdate(TIMESTAMP, 'YmdHis');
	$suffix = dgmdate(TIMESTAMP, 'His').rand(1000, 9999);
	$transaction_id = DISCUZ_PARTNER.$date.$suffix;
	$chinese = new Chinese(strtoupper(CHARSET), 'GBK');

	$subject = $chinese->Convert($_G['setting']['bbname'].' - '.$_G['member']['username'].' - '.$subject.' - '.lang('plugin/xj_event', 'huodongbm'));
	$subject = cutstr($subject,32,'');

	$reqHandler = new RequestHandler();
	$reqHandler->setGateURL("https://gw.tenpay.com/gateway/pay.htm");

	$reqHandler->init();
	$reqHandler->setKey(DISCUZ_SECURITYCODE);

	$reqHandler->setParameter("partner", DISCUZ_PARTNER);
	$reqHandler->setParameter("out_trade_no", $orderid);
	$reqHandler->setParameter("total_fee", $price * 100);
	$reqHandler->setParameter("return_url", $_G['siteurl'].'plugin.php?id=xj_event:event_pay&action=paysucceed');
	$reqHandler->setParameter("notify_url", $_G['siteurl'].'source/plugin/xj_event/event_pay_notify.php');
	$reqHandler->setParameter("body", $subject);
	$reqHandler->setParameter("bank_type", $bank);

	$reqHandler->setParameter("spbill_create_ip", $_G['clientip']);
	$reqHandler->setParameter("fee_type", "1");
	$reqHandler->setParameter("subject", $subject);

	$reqHandler->setParameter("sign_type", "MD5");
	$reqHandler->setParameter("service_version", "1.0");
	$reqHandler->setParameter("input_charset", "GBK");
	$reqHandler->setParameter("sign_key_index", "1");

	$reqHandler->setParameter("attach", "tenpay");
	$reqHandler->setParameter("time_start", $date);
	$reqHandler->setParameter("trade_mode","1");
	$reqHandler->setParameter("trans_type","1");
	$reqHandler->setParameter("agentid", DISCUZ_AGENTID);
	$reqHandler->setParameter("agent_type","2");

	$reqUrl = $reqHandler->getRequestURL();

	header('Location: '.$reqUrl);
}




?>
