<?php

/**
 *      Stellar (C)2015-07-28 HuiZhou Media.
 *
 *      $Id: mobile.class.php 35165 2015-07-28 15:01 Created by stellar $
 */

if (!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class plugin_wx_mobile {

	function plugin_wx_mobile(){
		global $_G;
		include_once template('wx_mobile:module');
		$this->var = $_G['cache']['plugin']['wx_mobile'];
		$this->tar_group = ($this->var['wx_group'] != 0) ? $this->var['wx_group'] : 8;
	}

	function deletemember($param) {
		$uids = $param['param'][0];
		$step = $param['step'];
		if ($step == 'check' && $uids && is_array($uids)) {
			foreach($uids as $uid) {
				C::t('#wx_mobile#wx_mobile')->user_delete($uid);
			}
		}
	}

}

class plugin_wx_mobile_member extends plugin_wx_mobile {

	function logging_method() {
		global $_G;
		if($this->var['wx_start']){
			return wx_mobile_tpl_login_bar();
		}
	}

	function register_logging_method() {
		global $_G;
		if($this->var['wx_start']){
			return wx_mobile_tpl_login_bar();
		}
	}

}

/*class plugin_wx_mobile_forum extends plugin_wx_mobile {

	function viewthread_share_method_output() {
		global $_G;
		if($this->var['wx_start']) {
			return wx_mobile_tpl_share();
		}
	}
} --因点赞的样式统一了分享，故这里做修改 modified by stellar*/


?>