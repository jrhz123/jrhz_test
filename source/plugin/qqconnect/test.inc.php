<?php

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$conopenid = authcode($_G['cookie']['con_auth_hash']);
$connectGuest = C::t('#qqconnect#common_connect_guest')->fetch($conopenid);
$conuin = $connectGuest['conuin'];
$conuinsecret = $connectGuest['conuinsecret'];
$conuintoken = $connectGuest['conuintoken'];

$connectOAuthClient = Cloud::loadClass('Service_Client_ConnectOAuth');
$connectUserInfo = $connectOAuthClient->connectGetUserInfo_V2($conopenid, $conuintoken);

var_dump($connectUserInfo);

?>