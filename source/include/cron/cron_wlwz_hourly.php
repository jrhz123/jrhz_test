<?php

/**
 *      $Id: cron_wlwz_daily.php 2015-03-28 05:50:25Z enicn_d $
 */
//全部Webservice调用传3个参数都为String字符串类型，第一个用户名，第二个密码，第三个xml格式的字符串
//http://www.huizhou.gov.cn/services/WlwzService?wsdl
//用户：wlwzuser  密码：wlwzadmin 密码需用MD5加密
//error_reporting(E_ALL);

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

define('LD_ID','78340');
define('LD_NAME','cyw');
define('LD_FULL_NAME','陈奕威');

date_default_timezone_set('Asia/Shanghai');

class transData
{
	protected $tarIP;
	protected $url;
	protected $ws;
	protected $client;
	protected $rawPass;
	protected $username;
	protected $pass;

	public function __construct()
	{
		$this->tarIP = 'http://www.huizhou.gov.cn/';
		$this->url = 'services/WlwzService?wsdl';
		$this->ws = $this->tarIP.$this->url;
		try {
			$this->client = new SoapClient($this->ws);
		} catch(SoapFault $fault) {
			$this->client = false;
		}
		$this->rawPass = "wlwz_jrhz_admin";
		$this->username = "jrhz_sjpt";
		$this->pass = md5($this->rawPass);
	}

	public function setAct($type,$string) {
		if ($type == 1) {
			return $this->transLetter($string);
		} else{
			return $this->replyLetter($string);
		}
	}

	public function transLetter($string){
		if ($this->client === false) {
			return false;
		} else {
			$param = array('in0'=>$this->username, 'in1'=>$this->pass, 'in2'=>$string);
			return simplexml_load_string($this->client->exchangeWlwzLetter($param)->out);
		}
	}

	public function replyLetter($string){
		if ($this->client === false) {
			return false;
		} else {
			$param = array('in0'=>$this->username,'in1'=>$this->pass,'in2'=>$string);
			return simplexml_load_string($this->client->letterReplyOperation($param)->out);
		}
	}
}

class SimpleXMLExtended extends SimpleXMLElement
{
  	public function addCData($cdata_text)
	{
	    $node = dom_import_simplexml($this);
	    $no   = $node->ownerDocument;
	    $node->appendChild($no->createCDATASection($cdata_text));
    }
}

abstract class AbstractXml
{
	protected $xml = array();

	public function initialize($init = array())
	{
		foreach ($this->xml_keys as $value) {
			if (!empty($init[$value]) || $init[$value]==='0') {
				$this->$value = $init[$value];
			}
		}
	}

	public function getXml()
	{
        $xml = new SimpleXMLExtended('<?xml version="1.0" encoding="UTF-8"?><root/>');
		foreach ($this->xml_keys as $xml_key) {
	        if (!empty($this->$xml_key) || $this->$xml_key==='0') {
				if (in_array($xml_key, array('content','topic','replyContent','ldps'))) {
	            	$xml->addChild($xml_key);
	            	$xml->$xml_key =  NULL;
	            	$xml->$xml_key->addCData($this->$xml_key);
	        	} else {
	            	$xml->addChild($xml_key, $this->$xml_key);
	        	}
			} else {
				$xml->addChild($xml_key, '');
			}
		}
	    return $xml->asXML();
	}

	public function transferXml($xml = '')
	{
		if (empty($xml)) {
			$xml = $this->getXml();
		}
		$transData = new transData();
		//var_dump($xml);
		//$transfer_status = 0;
		$transfer_status = $transData->setAct($this->dataType, $xml);
		return $transfer_status;
	}
}

class exchangeWlwzLetter extends AbstractXml
{
	protected $dataType = 1;
	protected $exchangeNo;
	protected $tid;
	protected $organUID;
	protected $organNo;//：(必填)受文单位编号，长度32位；从提供的单位关联表读取相应的单位编号； 多个单位回复的，规则定义为以第一个回复单位作为牵头单位；
	protected $organName;//：(必填)职能局名称；从提供的单位关联表读取相应的单位名称;
	protected $organNumber;//: (必填)职能局编号；从提供的单位关联表读取相应的单位编号;
	protected $topic;//：(必填)信件主题；长度100个字母或50个中文字;
	protected $security;//：(必填)信件是否公开；而且只能填写“Y”或者“N”,“Y”代表公开，“N代表不公开”;
	protected $kind;//：(必填)写信类型；只能填写“咨询”、“建议”、“表扬”、“投诉”、“其它”;
	protected $content;//: (必填)信件内容，纯字符内容，不要带html格式，有格式需过滤掉；若有图片或者附件，则附在内容后面，
						//例如：内容xxx<br/><a href=”http://xxx/a.jpg>图片名称1</a><br/><a href=”http://xxx/b.jpg>图片名称2</a>；
	protected $author;//：(必填)写信人姓名；长度25个字母，12个中文字;
	protected $phone;//：(选填)手机号码；长度11个数字；
	protected $email;//：(必填)电子邮箱；长度50位；
	protected $tdate;//：(必填)来信时间，格式2014-8-01 15:30:19；
	protected $replyDate;//：(必填)职能局回复时间，格式2014-8-01 15:30:19；
	protected $replyContent;//：(必填)职能局回复信件内容，纯字符内容，不要带html格式，有格式需过滤掉；若有图片或者附件，则附在内容后面，
						//例如：内容xxx<br/><a href=”http://xxx/a.jpg>图片名称1</a><br/><a href=”http://xxx/b.jpg>图片名称2</a>；
	protected $ip;//：(必填)来信人ip；
	protected $ldps;//：(选填)领导批示内容；纯字符内容，不要带html格式，有格式需过滤掉；如果职能局回复后，书记批示没有内容，则下面选填，有批示内容，则一起交换过来；
	protected $ldName;//：(选填)批示领导拼音简拼；例如 陈奕威为 cyw；
	protected $ldFullName;//：(选填)批示领导中文名；例如 陈奕威；
	protected $ldpsDate;//：(选填)领导批示时间；
	protected $transfer;//传送状态
	protected $xml_keys = array('organNo','organName','organNumber','topic','security',
						'kind','content','author','phone','email','tdate','replyDate',
						'replyContent','ip','dealType','ldps','ldName','ldFullName','ldpsDate');

	public function __construct($init = array())
	{
		$this->initialize($init);
	}

	public function setTheRestValue($rest_arr)
	{
		$this->exchangeNo = $rest_arr['exchangeNo'];
		$this->tid = $rest_arr['tid'];
		$this->pid = $rest_arr['pid'];
		$this->organUID = $rest_arr['organUID'];
		$this->transfer = $rest_arr['transfer'];
		$this->error = $rest_arr['error'];
	}

	public function saveToDB()
	{
		$topic_table_keys = array('exchangeNo','tid','pid','organUID','topic','security',
					'kind','content','author','email','tdate','replyDate','replyContent','dealType',
					'ip','ldps','ldName','ldFullName','ldpsDate','transfer','error',);
		$columns = '';
		$values = '';
		foreach ($topic_table_keys as $value) {
			$columns .= $value.',';
			$values .= '\''.$this->$value.'\',';
		}
		$columns = substr($columns, 0, -1);
		$values = substr($values, 0, -1);
		$transfer_query = "REPLACE INTO wlwz_topics ($columns) VALUES ($values)";
		$transfer_result = DB::query($transfer_query);
	}
}

class letterReplyOperation extends AbstractXml
{
	protected $dataType = 2;
	protected $tid;
	protected $pid;
	protected $uid;
	protected $exchangeNo;//：(必填)信件交换ID，为上一接口返回；
	protected $type;//：(必填) 0表示新职能局回复；1 表示修改网友内容；2 表示修改职能局内容；3 表示修改领导批示内容，
					//如果第一次交换，没有书记批示内容，当有书记批示内容时，也调用该接口，当做修改把书记的批示内容交换过来；
	protected $oldOrganReplyDate;//：(选填)原职能局回复时间；当type填2时，表示修改职能局回复内容，则必须填写对应职能局当时回复的日期，
					//格式2014-8-01 15:30:19;type填其他数字时，则无需填写；
	protected $ldName;//：(选填)若type填3时填写领导拼音简称；
	protected $ldFullName;//：(选填) 若type填3时填写领导中文全名；
	protected $security;//：(选填)信件是否公开；而且只能填写“Y”或者“N”,“Y”代表公开，“N代表不公开”;当type填1时，该节点生效，可选填是否同时修改信件隐私；
	protected $content;//：(必填)新增或者修改内容；纯字符内容，不要带html格式，有格式需过滤掉；若有图片或者附件，则附在内容后面，
					//例如：内容xxx<br/><a href=”http://xxx/a.jpg>图片名称1</a><br/><a href=”http://xxx/b.jpg>图片名称2</a>；
	protected $tDate;//：(必填)新增或修改的日期，格式2014-8-01 15:30:19;
	protected $transfer;
	protected $xml_keys = array('exchangeNo','type','organNo','organName','oldOrganReplyDate',
					'ldName','ldFullName','security','content','tDate');

	public function __construct($init = array())
	{
		$this->initialize($init);
	}

	public function setTheRestValue($rest_arr)
	{
		$this->tid = $rest_arr['tid'];
		$this->pid = $rest_arr['pid'];
		$this->uid = $rest_arr['uid'];
		$this->transfer = $rest_arr['transfer'];
		$this->error = $rest_arr['error'];
	}

	public function saveToDB()
	{
		$reply_table_keys = array('topic_id','tid','pid','uid','exchangeNo','type','organNo','organName',
						'oldOrganReplyDate','ldName','ldFullName','security','content','tDate','transfer','error');
		$columns = '';
		$values = '';
		foreach ($reply_table_keys as $value) {
			$columns .= $value.',';
			$values .= '\''.$this->$value.'\',';
		}
		$columns = substr($columns, 0, -1);
		$values = substr($values, 0, -1);
		$transfer_query = "REPLACE INTO wlwz_replies ($columns) VALUES ($values)";
		$transfer_result = DB::query($transfer_query);
	}
}

function cutstr_html($string)
{
	$string = strip_tags($string);
	$string = preg_replace('/\[table=100%\].+\[\/table\]/', '', $string);
	$string = preg_replace('/\n/is', '', $string);
	$string = preg_replace('/ |　/is', '', $string);
	$string = preg_replace('/&[a-z]*;/is', '', $string);
	$string = preg_replace('/\[\/?([A-Za-z0-9_=#,:\-\/\.\%\?]*|font=.{0,45}|backcolor=.{0,45}|color=rgb([\d,]+))\]/', '', $string); //清除论坛格式
	$string = preg_replace('/【[\[\]\w\:\/\.\?&=]*回(帖|复)详文】/', '', $string);
	$string = preg_replace('/attachment\//', 'http://sns.huizhou.cn/attachment/', $string);
	$img_pattern = '/http:\/\/[A-Za-z0-9_\/\.]+\.(jpg|jpeg|htm|html|shtml)/';
	preg_match_all($img_pattern, $string, $img);
	$string = preg_replace($img_pattern, '', $string);
	$i = 1;
	if (!empty($img[0])) {
		$string .= '【（请拷贝链接下载查看附件）';
		foreach ($img[0] as $key => $value) {
			$string .= ' 附件' . $i++ . '：' . $value;
		}
	}
	return $string;
}

function getEmail($uid)
{
	$email_query = "SELECT `email` FROM ".DB::table('common_member')." WHERE `uid` = '$uid'";
	$email_data = DB::fetch_first($email_query);
	return $email_data ? $email_data['email'] : '';
}

function getTopic($tid)
{
	$query = "SELECT * FROM `wlwz_topics` WHERE `tid` = '$tid'";
	return DB::fetch_first($query);
}

function getReply($pid)
{
	$query = "SELECT * FROM `wlwz_replies` WHERE `pid` = '$pid' ORDER BY `tdate` DESC";
	return DB::fetch_first($query);
}

function getThread($tid)
{
	$query = "SELECT * FROM `".DB::table('forum_thread')."` WHERE `tid` = '$tid'";
	return DB::fetch_first($query);
}

function getPost($tid)
{
	$query = "SELECT `useip`, `message` FROM `".DB::table('forum_post')."` WHERE `tid` = '$tid'"; //获得帖子内容及作者IP
	return DB::fetch_first($query);
}

function getFirstPost($tid)
{
	$query = "SELECT `useip`, `message` FROM `".DB::table('forum_post')."` WHERE `tid` = '$tid' AND `first` = 1"; //获得帖子内容及作者IP
	return DB::fetch_first($query);
}

function getDealType($tid, $uid)
{
    $query = "SELECT `deal_type` FROM `wlwz_deal` WHERE `tid` = '$tid' AND `uid` = '$uid'";
    $result = DB::fetch_first($query);
    if ($result) {
        $deal_type = $result['deal_type'];
    } else {
        $deal_type = 1;
    }
    return $deal_type;
}

$unit_list = array(); //保存单位列表
$unit_info = array(); //保存单位详细信息
$unit_query = 'SELECT * FROM wlwz_units'; //取出全部单位代码
$unit_result = DB::query($unit_query);
while ($rt = DB::fetch($unit_result)) {
	array_push($unit_list, $rt['uid']);
	$unit_info[$rt['uid']] = array('sid'=>$rt['sid'], 'name'=>$rt['name'], 'code'=>$rt['code']);
}
$posts_table = DB::table('forum_post');
$thread_table = DB::table('forum_thread');
$fid = 863;
$head_time = time() - 5400;
$post_query = "SELECT * FROM `$posts_table` WHERE `fid` = $fid AND `dateline` > $head_time AND `first` = 0 ORDER BY `dateline` ASC"; //取出前一个半小时的帖子回复
$post_result = DB::query($post_query);
while ($rt = DB::fetch($post_result)) { //逐条读出
	if (in_array($rt['authorid'], $unit_list) || $rt['authorid'] == LD_ID) { //属于部门回复或领导回复
		if ($topic_data = getTopic($rt['tid'])) { //此主题已获取过
			$organNo = '';
			$organName = '';
			$oldOrganReplyDate  = '';
			$ldName = '';
			$ldFullName = '';
			$content = cutstr_html($rt['message']);

			if ($rt['pid'] == $topic_data['pid']) {
				if (date('Y-m-d h:i:s', $rt['dateline']) != $topic_data['replyDate']) {
					$type = 2;
					$unit_detail = $unit_info[$rt['authorid']];
					$organNo = $unit_detail['sid'];
					$organName = $unit_detail['name'];
					$oldOrganReplyDate = $topic_data['replyDate'];
				} else {
					$type = -1;
				}
			} else {
				if ($reply_data = getReply($rt['pid'])) { //判断此回复是否已获取过
					if (date('Y-m-d h:i:s', $rt['dateline']) != $reply_data['tdate']) { //判断回复是否有修改
						if ($rt['authorid'] == LD_ID) { //判断作者ID是否为领导
							$type = 4;
							$ldName = LD_NAME;
							$ldFullName = LD_FULL_NAME;
						} else {
							$type = 2;
							$unit_detail = $unit_info[$rt['authorid']];
							$organNo = $unit_detail['sid'];
							$organName = $unit_detail['name'];
						}
						$oldOrganReplyDate = $reply_data['tdate'];
					} else {
						$type = -1;
					}
				} else {
					if ($rt['authorid'] == LD_ID) { //判断作者ID是否为领导
						if (empty($topic_data['ldpsDate'])) {
							$type = 3;
							$ldName = LD_NAME;
							$ldFullName = LD_FULL_NAME;
						} elseif (date('Y-m-d h:i:s', $rt['dateline']) != $topic_data['ldpsDate']) {
							$type = 4;
							$ldName = LD_NAME;
							$ldFullName = LD_FULL_NAME;
							$oldOrganReplyDate = $topic_data['ldpsDate'];
						} else {
							$type = -1;
						}
					} else {
						$type = 0;
						$unit_detail = $unit_info[$rt['authorid']];
						$organNo = $unit_detail['sid'];
						$organName = $unit_detail['name'];
					}
				}
			}

			if ($type !== -1) {
				$reply_arr = array();
				$reply_arr['exchangeNo']		= $topic_data['exchangeNo'];
				$reply_arr['type']				= $type;
				$reply_arr['organNo']			= $organNo; //部门编码
				$reply_arr['organName']			= $organName; //部门名称
				$reply_arr['oldOrganReplyDate'] = $oldOrganReplyDate;
				$reply_arr['ldName']			= $ldName;
				$reply_arr['ldFullName']		= $ldFullName;
				$reply_arr['security']			= 'Y';
				$reply_arr['content']			= $content;
				$reply_arr['tDate']				= date('Y-m-d h:i:s', $rt['dateline']);
				$reply_arr['topic_id']			= $topic_data['topic_id'];
				$reply_arr['uid']				= $rt['authorid'];
				$reply_arr['tid']				= $rt['tid'];
				$reply_arr['pid']				= $rt['pid'];
				$reply_arr['authorid']			= $rt['authorid'];

				$reply = new letterReplyOperation($reply_arr);
				$transfer_status = $reply->transferXml();
				if ($transfer_status->flag == '0') {
					$reply_arr['transfer'] = 1;
					$reply_arr['error'] = '';
				} else {
					$reply_arr['transfer'] = 0;
					$reply_arr['error'] = $transfer_status->errorCode;
				}
				$reply->setTheRestValue($reply_arr);//设置剩余参数
				$reply->saveToDB();
			}

		} elseif ($rt['authorid'] != LD_ID) { //若未记录，则新建帖子（信件）；书记回复不另辟新主题
			$thread_data = getThread($rt['tid']); //获取相应的帖子（信件）数据
			if (!in_array($thread_data['authorid'], $unit_list) && $thread_data['authorid'] != LD_ID) { //不属于部门或领导发表的主题
				$unit_detail = $unit_info[$rt['authorid']];
				$ld_id = LD_ID; //获取领导ID
				$ldps_query = "SELECT * FROM `$posts_table` WHERE `tid` = '$rt[tid]' AND `authorid` = '$ld_id' LIMIT 1"; //获取领导批示
				if ($ldps_data = DB::fetch_first($ldps_query)) { //判断有无批示，无则留空
					$ldps = cutstr_html($ldps_data['message']);
					$ldName = LD_NAME;
					$ldFullName = LD_FULL_NAME;
					$ldpsDate = date('Y-m-d h:i:s', $ldps_data['dateline']);
				} else {
					$ldps = '';
					$ldName = '';
					$ldFullName = '';
					$ldpsDate = '';
				}
				$first_data = getFirstPost($rt['tid']);

				$letter_arr = array();
				$letter_arr['organNo']		= $unit_detail['sid']; //部门编码
				$letter_arr['organName']	= $unit_detail['name']; //部门名称
				$letter_arr['organNumber']	= $unit_detail['code']; //部门编号
				$letter_arr['topic']		= cutstr_html($thread_data['subject']); //帖子（信件）主题
				$letter_arr['security']		= 'Y'; //密级，Y为公开，N为保密
				$letter_arr['kind']			= '建议'; //写信类型；只能填写“咨询”、“建议”、“表扬”、“投诉”、“其它”;
				$letter_arr['content']		= cutstr_html($first_data['message']); //帖子（信件）内容
				$letter_arr['author']		= $thread_data['author']; //帖子（信件）作者
				$letter_arr['phone']		= ''; //作者联系电话
				$letter_arr['email']		= getEmail($thread_data['authorid']); //作者联系邮箱
				$letter_arr['tdate']		= date('Y-m-d h:i:s', $thread_data['dateline']); //帖子（信件）发表时间
				$letter_arr['replyDate']	= date('Y-m-d h:i:s', $rt['dateline']); //职能部门回复时间
				$letter_arr['replyContent']	= cutstr_html($rt['message']); //职能部门回复内容
                $letter_arr['dealType']     = getDealType($rt['tid'], $rt['authorid']);
				$letter_arr['ip']			= $first_data['useip'];//$thread_data['ip']; //帖子（信件）作者IP
				$letter_arr['ldps']			= $ldps; //领导批示
				$letter_arr['ldName']		= $ldName; //领导姓名缩写
				$letter_arr['ldFullName']	= $ldFullName; //领导姓名
				$letter_arr['ldpsDate']		= $ldpsDate; //领导批示时间
				$letter_arr['tid']			= $rt['tid'];
				$letter_arr['organUID']		= $rt['authorid'];
				$letter_arr['pid']			= $rt['pid'];
				$letter = new exchangeWlwzLetter($letter_arr);
				$transfer_status = $letter->transferXml();

				if ($transfer_status->flag == '0') {
					$letter_arr['exchangeNo'] = $transfer_status->exchangeNo;
					$letter_arr['transfer'] = 1;
					$letter_arr['error'] = '';
				} else {
					$letter_arr['exchangeNo'] = 0;
					$letter_arr['transfer'] = 0;
					$letter_arr['error'] = $transfer_status->errorCode;
				}
				$letter->setTheRestValue($letter_arr);//设置
				$letter->saveToDB();
			}
		}
	}
}
/**检查主题是否有修改**/
$first_query = "SELECT * FROM `$posts_table` WHERE `fid` = $fid AND `dateline` > $head_time AND `first` = 1 ORDER BY `dateline` ASC"; //取出前一个半小时的帖子回复
$first_result = DB::query($first_query);
while ($rt = DB::fetch($first_result)) {
	if($topic_data = getTopic($rt['tid'])) { //获取相应的帖子（信件）数据
		if (date('Y-m-d h:i:s', $rt['dateline']) != $topic_data['tdate']) { //判断主题是否有修改
			$content = cutstr_html($rt['message']);

			$reply_arr = array();
			$reply_arr['exchangeNo']		= $topic_data['exchangeNo'];
			$reply_arr['type']				= 1;
			$reply_arr['organNo']			= '';
			$reply_arr['organName']			= '';
			$reply_arr['oldOrganReplyDate'] = '';
			$reply_arr['ldName']			= '';
			$reply_arr['ldFullName']		= '';
			$reply_arr['security']			= 'Y';
			$reply_arr['content']			= $content;
			$reply_arr['tDate']				= date('Y-m-d h:i:s', $rt['dateline']);
			$reply_arr['tid']				= $rt['tid'];
			$reply_arr['pid']				= $rt['pid'];
			$reply_arr['uid']				= $rt['authorid'];

			$reply = new letterReplyOperation($reply_arr);
			$transfer_status = $reply->transferXml();
			if ($transfer_status->flag == '0') {
				$reply_arr['transfer'] = 1;
				$reply_arr['error'] = '';
			} else {
				$reply_arr['transfer'] = 0;
				$reply_arr['error'] = $transfer_status->errorCode;
			}
			$reply->setTheRestValue($reply_arr);//设置补充值
			$reply->saveToDB();
		}
	}
}

/*$checkTopicFailure = "SELECT * FROM `wlwz_topics` WHERE `transfer` = 0";
$check_result = DB::query($checkTopicFailure);
while ($letter_arr = DB::fetch($check_result)) {
	$unit_detail = $unit_info[$letter_arr['organUID']];
	$letter_arr['organNo']		= $unit_detail['sid'];
	$letter_arr['organName']	= $unit_detail['name'];
	$letter_arr['organNumber']	= $unit_detail['code'];

	$letter = new exchangeWlwzLetter($letter_arr);
	$transfer_status = $letter->transferXml();
	if ($transfer_status->flag == '0') {
		$letter_arr['exchangeNo'] = $transfer_status->exchangeNo;
		$letter_arr['transfer'] = 1;
		$letter_arr['error'] = '';
		$letter->setTheRestValue($letter_arr);//设置剩余参数
		$letter->saveToDB();
	} else {
		$letter_arr['error'] = $transfer_status->errorCode;
		$letter->setTheRestValue($letter_arr);//设置剩余参数
		$letter->saveToDB();
	}
}

$checkReplyFailure = "SELECT * FROM `wlwz_replies` WHERE `transfer` = 0";
$check_result = DB::query($checkReplyFailure);
while ($reply_arr = DB::fetch($check_result)) {
	$reply = new letterReplyOperation($reply_arr);
	$transfer_status = $reply->transferXml();
	if ($transfer_status->flag == '0') {
		$reply_arr['transfer'] = 1;
		$reply_arr['error'] = '';
		$reply->setTheRestValue($reply_arr);//设置剩余参数
		$reply->saveToDB();
	} else {
		$reply_arr['error'] = $transfer_status->errorCode;
		$reply->setTheRestValue($reply_arr);//设置剩余参数
		$reply->saveToDB();
	}
}*/


?>
