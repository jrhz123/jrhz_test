<?php

/**
*       [Discuz! X] (C)2001-2099 Comsenz Inc.
*       This is NOT a freeware, use is subject to license terms
*
*       $Id: forum_hook.class.php 1000 2014-10-16 09:16 Enicn_D $
*       Related core file: moudule/forum/forum_forumdisplay.php | edited by Enicn
**/

if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}
//error_reporting(E_ALL);
class plugin_replies_checking
{
    var $value = array('initial'=>'testing');

    function plugin_replies_checking()
    {
        if ($_G = $this->_check_authorization()) {

        $this->value['current_filter'] = !empty($_GET['filter']) ? $_GET['filter'] : false;
       /* 史漫漫 2018年6月15日修改 $this->value['filter_types'] = array('zhuti'=>'书记主题帖','shuji'=>'书记批帖','bumen'=>'部门回应','duban'=>'督办跟进','paotui'=>'网记跑腿','unreply'=>'未回复');*/
		 $this->value['filter_types'] = array('shuji'=>'版主批帖','bumen'=>'部门回应','paotui'=>'网记跑腿');
        $this->value['target_users'] = $_G['cache']['plugin']['replies_checking']['target_users'];

        } else { return ''; }
    }

    function generate_filter_link($filter_types, $current_filter)
    {
        global $_G;

        $fid = $_G['cache']['plugin']['replies_checking']['target_forum'];
        $links_collection = '';
        foreach ($filter_types as $filter_code => $filter_name) {
            $class = '';
            if ($filter_code == $current_filter) {
                $class = 'a';
            }
            $links_collection .= "<li class='$class'><a href='forum.php?mod=forumdisplay&fid=$fid&filter=$filter_code'>$filter_name</a></li>";
        }
        return $links_collection;
    }

    function _get_array_value($key, $array)
    {
        if (array_key_exists($key, $array)) {
            return $array[$key];
        } else {
            return false;
        }
    }

    function _check_authorization()
    {
        global $_G;

        if ($_G['fid'] == $_G['cache']['plugin']['replies_checking']['target_forum']) {
            return $_G;
        } else {
            return false;
        }
    }

    function choose_type()
    {
        global $_G;
        $tid = $_G['tid'];
        $action = $_GET['action'];
        if (in_array($_G['fid'], array($_G['cache']['plugin']['replies_checking']['target_forum'])) && $action !== 'newthread') {
            $rc_groups = unserialize($_G['cache']['plugin']['replies_checking']['target_groups']);
            $rc_allow = in_array($_G['groupid'],$rc_groups)? 1 : 0;
            if ($rc_allow) {
                $uid = $_G['uid'];
                $check = DB::result_first("SELECT `tid` FROM `wlwz_deal` WHERE `tid` = '$tid' AND `uid` = '$uid'");
                if (empty($check)) {
                    include template('replies_checking:choose_type');
                    return $return;
                } else {
                    return;
                }
            }
        } else {
            return;
        }
    }

}

class plugin_replies_checking_forum extends plugin_replies_checking
{
    function forumdisplay_filter_extra_output()
    {
        if ($_G = $this->_check_authorization()) {
            $current_filter = !empty($_GET['filter']) ? $_GET['filter'] : false;
            $links_collection = $this->generate_filter_link($this->value['filter_types'], $this->value['current_filter']);
            return $links_collection;

        } else { return ''; }
    }

    function viewthread_fastpost_side_output() {//帖子查看页，底部快速回复
        return $this->choose_type();
    }

    function forumdisplay_fastpost_content(){//帖子列表页，底部快速发帖
        return $this->choose_type();
    }

    function post_side_bottom_output() {//常规发帖页，右侧选项
        return $this->choose_type();
    }

    function post_infloat_top(){//ajax浮动窗口回复
        return $this->choose_type();
    }

    function post_middle(){//>=X3.0
        return $this->choose_type();
    }

    function post_feedlog_message($var){
        global $_G;
        $tid = $var['param'][2]['tid'];
        $pid = $var['param'][2]['pid'];
        $action = $var['param'][0];

        //网络问政处理状态插入点
        $rc_var = $_G['cache']['plugin']['replies_checking'];
        if (in_array($_G['fid'], array($rc_var['target_forum'])) && $action != 'post_newthread_succeed' && $action != 'submit_invalid') {
            $rc_groups = unserialize($rc_var['target_groups']);
            $rc_allow = in_array($_G['groupid'],$rc_groups)? true : false;
            if ($rc_allow) {
                $rc_uid = $_G['uid'];
                $check = DB::result_first("SELECT `tid` FROM `wlwz_deal` WHERE `tid` = '$tid' AND `uid` = '$rc_uid'");
                if (empty($check)) {
                    $deal_type = intval($_GET['deal_type']);
                    DB::query("INSERT INTO `wlwz_deal` (`tid`,`deal_type`,`uid`,`action`) VALUES ('$tid','$deal_type','$rc_uid','$action')");
                }
            }
        }
    }

}

class plugin_replies_checking_group extends plugin_replies_checking_forum{
}

?>
