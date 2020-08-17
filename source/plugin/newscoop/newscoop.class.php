<?php

/**
 *      $ Id: newscoop.class.php UTF-8 2015-03-28 00:43:10Z Enicn_d $
 */
 
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class threadplugin_newscoop {

	var $name = '报料';			//用于文字展示，下拉发帖、导航、post Tab
	var $iconfile = 'images/newscoop.png';		//基于当前目录，尺寸为 16 * 16
	var $buttontext = '报料';	//用于发表按钮

	/**
	 * 发主题时页面新增的表单项目，通过 return 返回即可输出到发帖页面中，内容出现在：POST标题栏下面，内容在DIV内
	 * @param unknown_type $fid
	 */
	public function newthread ($fid) {
		include_once template('newscoop:newthread');
		return $return;
	}
	
	/**
	 * 主题发布后的数据判断
	 * @param unknown_type $fid
	 */
	public function newthread_submit($fid) {
		$datetime = $_POST['datetime'];
		if (strtotime($datetime) <= 0) {
			showmessage('时间输入有误。');
		}
	}
	
	/**
	 * 主题发布后的数据处理
	 * @param unknown_type $fid
	 * @param unknown_type $tid
	 */
	public function newthread_submit_end($fid, $tid) {
		$insertData = array('tid' => $tid, 'datetime' => strtotime($_POST['datetime']), 'location' => $_POST['location'], 'contact' => $_POST['contact']);
		DB::insert('plugin_baoliao', $insertData);
	}
	
	/**
	 * 编辑主题时页面新增的表单项目，通过 return 返回即可输出到编辑主题页面中 
	 * @param unknown_type $fid
	 * @param unknown_type $tid
	 */
	public function editpost($fid, $tid) {
		$table_name = DB::table('plugin_baoliao');
		$scoopData = DB::fetch_first("SELECT * FROM `$table_name` WHERE `tid` = $tid");
		$datetime = date('Y-m-d H:i:s',$scoopData['datetime']);
		$location = $scoopData['location'];
		$contact = $scoopData['contact'];
		include_once template('newscoop:newthread');
		return $return;
	}
	
	/**
	 * 主题编辑后的数据判断
	 * @param unknown_type $fid
	 * @param unknown_type $tid
	 */
	public function editpost_submit($fid, $tid) {
		$datetime = $_POST['datetime'];
		if (strtotime($datetime) <= 0) {
			showmessage('时间输入有误。');
		}
	}
	
	/**
	 * 主题编辑后的数据处理
	 * @param unknown_type $fid
	 * @param unknown_type $tid
	 */
	public function editpost_submit_end($fid, $tid) {
		$updateData = array('datetime' => strtotime($_POST['datetime']), 'location' => $_POST['location'], 'contact' => $_POST['contact']);
		DB::update('plugin_baoliao', $updateData, "`tid`=$tid");
	}
	
	/**
	 * 回帖后的数据处理
	 * @param unknown_type $fid
	 * @param unknown_type $tid
	 */
	public function newreply_submit_end($fid, $tid) {

	}
	
	/**
	 * 查看主题时页面新增的内容，通过 return 返回即可输出到主题首贴页面中(靠前位置)
	 * @param unknown_type $tid
	 */
	public function viewthread($tid) {
		global $_G;
		$table_name = DB::table('plugin_baoliao');
		$scoopData = DB::fetch_first("SELECT * FROM `$table_name` WHERE `tid` = $tid");
		$datetime = date('Y-m-d H:i:s',$scoopData['datetime']);
		$location = $scoopData['location'];
		$contact = $scoopData['contact'];
		include_once template('newscoop:viewthread');
		return $return;
	}
	
	/**
	 * Get thread status
	 * @param unknown_type $tid
	 */
	private function _get_status ($tid) {

	}
	
	/**
	 * Reply avalibale
	 * @param unknown_type $tid
	 */
	private function _reply_avaliable ($tid, $ingore_login = FALSE) {

	}
}

?>