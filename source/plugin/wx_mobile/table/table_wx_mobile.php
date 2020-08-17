<?php

/**
 *      HuiZhou Media Group (C)2008-2099.
 *
 *      $Id: table_wx_mobile.php  2015-07-30 16:15 $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_wx_mobile extends discuz_table {

	public function __construct() {
		$this->_table = 'wx_mobile';
		$this->_pk    = 'id';

		parent::__construct();
	}

	//public function fetch_by_sina_uid($sina_uid) {
		//return DB::fetch_first("SELECT * FROM %t WHERE sina_uid=%d", array($this->_table, $sina_uid));
	//}

	public function count_user_by_openid($username) {
		$wheresql = 'WHERE 1';
		$wheresql .= $username ? ' AND '.DB::field('username', $username, '=') : '';
		$wheresql .= ' AND uid > 0';

		return DB::result_first('SELECT COUNT(*) FROM %t %i', array($this->_table, $wheresql));
	}

	public function user_delete($uid) {
		return DB::delete($this->_table, ' uid ='.$uid);
	}

	public function fetch_fields_by_openid($openid) {
		return DB::fetch_first('SELECT * FROM %t WHERE openid=%s AND uid > 0', array($this->_table, $openid));
	}

	public function fetch_fields_by_uid($uid) {
		return DB::fetch_first('SELECT * FROM %t WHERE uid=%s', array($this->_table, $uid));
	}

	public function count_by_search($username) {
		$wheresql = 'WHERE 1';
		$wheresql .= $username ? ' AND '.DB::field('username', $username, 'like') : '';

		return DB::result_first('SELECT COUNT(*) FROM %t %i', array($this->_table, $wheresql));
	}

	public function fetch_all_by_search($username, $start = 0, $limit = 0, $order = 'dateline', $sort = 'DESC') {
		$ordersql = $order ? " ORDER BY $order $sort " : '';
		$wheresql = 'WHERE 1';
		$wheresql .= $username ? ' AND '.DB::field('username', $username, 'like') : '';

		return DB::fetch_all('SELECT * FROM %t %i %i '.DB::limit($start, $limit), array($this->_table, $wheresql, $ordersql));
	}

}

?>