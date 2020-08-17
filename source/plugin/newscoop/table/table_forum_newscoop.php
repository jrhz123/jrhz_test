<?php

/**
 *      $ Id: newscoop.class.php UTF-8 2015-03-28 00:43:10Z Enicn_d $
 */
 
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_forum_threadserialize extends discuz_table {

	public function __construct() {
		$this->_table = 'forum_newscoop';
		$this->_pk = 'tid';

		parent::__construct();
	}
}

?>