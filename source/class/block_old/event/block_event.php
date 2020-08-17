<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: block_event.php 25525 2011-11-14 04:39:11Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
class block_event extends discuz_block {
	var $setting = array();

	function block_event() {
		$this->setting = array(
			'tids' => array(
				'title' => 'threadlist_tids',
				'type' => 'text'
			),
			'uids' => array(
				'title' => 'threadlist_uids',
				'type' => 'text'
			),
			'keyword' => array(
				'title' => 'threadlist_keyword',
				'type' => 'text'
			),
			'tagkeyword' => array(
				'title' => 'threadlist_tagkeyword',
				'type' => 'text'
			),
			'recommend' => array(
				'title' => 'threadlist_recommend',
				'type' => 'radio'
			),
			'fids'	=> array(
				'title' => 'activitylist_fids',
				'type' => 'mselect',
				'value' => array()
			),
			'verify' => array(
				'title' => '活动认证',
				'type' => 'select',
				'value' => array(
					array('0', '全部'),
					array('1', '认证'),
				)
			),
			'titlelength' => array(
				'title' => '活动标题长度',
				'type' => 'text',
				'default' => 40
			),
			'summarylength'	=> array(
				'title' => '活动简介长度',
				'type' => 'text',
				'default' => 80
			),
		);
	}

	function name() {
		return '活动';
	}

	function blockclass() {
		return array('event', '活动模块');
	}

	function fields() {
		return array(
					'id' => array('name' => 'ID', 'formtype' => 'text', 'datatype' => 'int'),
					'url' => array('name' => '活动链接', 'formtype' => 'text', 'datatype' => 'string'),
					'title' => array('name' => '活动标题', 'formtype' => 'title', 'datatype' => 'title'),
					'pic' => array('name' => '活动图片', 'formtype' => 'pic', 'datatype' => 'pic'),
					'status' => array('name' => '活动状态', 'formtype' => 'text', 'datatype' => 'string'),
					'status_style' => array('name' => '活动状态样式', 'formtype' => 'text', 'datatype' => 'string'),
					'summary' => array('name' => '活动简介', 'formtype' => 'summary', 'datatype' => 'summary'),
					'starttime' => array('name' => '开始时间', 'formtype' => 'text', 'datatype' => 'string'),
					'endtime' => array('name' => '结束时间', 'formtype' => 'text', 'datatype' => 'string'),
					'applybegin' => array('name' => '报名开始时间', 'formtype' => 'text', 'datatype' => 'string'),
					'applyend' => array('name' => '报名结束时间', 'formtype' => 'text', 'datatype' => 'string'),
					'zy' => array('name' => '作业数', 'formtype' => 'text', 'datatype' => 'string'),
					'bm' => array('name' => '报名人数', 'formtype' => 'text', 'datatype' => 'string'),
					'author' => array('name' => '发贴人', 'formtype' => 'text', 'datatype' => 'string'),
					'authorid' => array('name' => '发贴人ID', 'formtype' => 'text', 'datatype' => 'int'),
					'postclass' => array('name' => '活动方式', 'formtype' => 'text', 'datatype' => 'string'),
					'eventclass' => array('name' => '活动类型', 'formtype' => 'text', 'datatype' => 'string'),
					'address' => array('name' => '活动地点', 'formtype' => 'text', 'datatype' => 'string'),
					'cost' => array('name' => '活动费用', 'formtype' => 'text', 'datatype' => 'string'),
					'eventnumber' => array('name' => '活动人数', 'formtype' => 'text', 'datatype' => 'string'),
				);
	}
	
	function fieldsconvert() {
		return array(
				'portal_article' => array(
					'name' => lang('blockclass', 'blockclass_portal_article'),
					'script' => 'article',
					'searchkeys' => array('author', 'authorid', 'forumurl', 'forumname', 'posts', 'views', 'replies'),
					'replacekeys' => array('username', 'uid', 'caturl', 'catname', 'articles', 'viewnum', 'commentnum'),
				),
				'space_blog' => array(
					'name' => lang('blockclass', 'blockclass_space_blog'),
					'script' => 'blog',
					'searchkeys' => array('author', 'authorid', 'views', 'replies'),
					'replacekeys' => array('username', 'uid', 'viewnum', 'replynum'),
				),
				'group_thread' => array(
					'name' => lang('blockclass', 'blockclass_group_thread'),
					'script' => 'groupthread',
					'searchkeys' => array('forumname', 'forumurl'),
					'replacekeys' => array('groupname', 'groupurl'),
				),
			);
	}
	
	function getsetting() {
		global $_G;
		$settings = $this->setting;
		if($settings['fids']) {
			loadcache('forums');
			$settings['fids']['value'][] = array(0, lang('portalcp', 'block_all_forum'));
			foreach($_G['cache']['forums'] as $fid => $forum) {
				$settings['fids']['value'][] = array($fid, ($forum['type'] == 'forum' ? str_repeat('&nbsp;', 4) : ($forum['type'] == 'sub' ? str_repeat('&nbsp;', 8) : '')).$forum['name']);
			}
		}
		return $settings;
	}

	function getdata($style, $parameter) {
		global $_G;

		require_once libfile('function/post');
		$parameter = $this->cookparameter($parameter);
		//var_dump($parameter);
		$items = !empty($parameter['items']) ? intval($parameter['items']) : 10;
		$verify = intval($parameter['verify']);
		$titlelength = isset($parameter['titlelength']) ? intval($parameter['titlelength']) : 40;
		$summarylength = isset($parameter['summarylength']) ? intval($parameter['summarylength']) : 80;

		$fids = array();
		if(!empty($parameter['fids'])) {
			if($parameter['fids'][0] == '0') {
				unset($parameter['fids'][0]);
			}
			$fids = $parameter['fids'];
		}
		
		$bannedids = !empty($parameter['bannedids']) ? explode(',', $parameter['bannedids']) : array();
		
		$sql = ($fids ? ' AND B.fid IN ('.dimplode($fids).')' : '').($bannedids ? ' AND B.tid NOT IN ('.dimplode($bannedids).')' : '');
		
		$tids		= !empty($parameter['tids']) ? explode(',', $parameter['tids']) : array();
		$joinmethod = empty($tids) ? 'INNER' : 'LEFT';
		$recommend	= !empty($parameter['recommend']) ? 1 : 0;
		if($recommend) {
			$join_part = " $joinmethod JOIN `".DB::table('forum_forumrecommend')."` fc ON B.tid=fc.tid";
		} else {
			$join_part = '';
		}
		
		$list = array();
		$query = DB::query("SELECT * FROM ".DB::table('xj_event')." A INNER JOIN ".DB::table('forum_thread')." B ON A.tid=B.tid $join_part WHERE A.verify>=$verify $sql ORDER BY A.eid DESC LIMIT 0,$items"); 
		while($data = DB::fetch($query)){
			$data['message'] = DB::result_first("SELECT message FROM ".DB::table('forum_post')." WHERE tid=".$data['tid']);
			if($data['activityaid_url']){
				if(strpos($data['activityaid_url'],'ttp://')>0){
					$data['activityaid_url'] = $data['activityaid_url'];
				}else{
					$data['activityaid_url'] = 'data/attachment/forum/'.$data['activityaid_url'];
				}
			}else{
				$data['activityaid_url'] = 'source/plugin/xj_event/images/nopic.jpg';
			}
			//设定活动状态
			if ($_G['timestamp']<$data['activitybegin']) {
				$data['status'] = '等待召集';
				$data['status_style'] = 'incons4';
			}elseif ($_G['timestamp']<$data['activityexpiration']) {
				$data['status'] = '召集中';
				$data['status_style'] = 'incons3';
			}elseif($_G['timestamp']<$data['starttime']) {
				$data['status'] = '截止报名';
				$data['status_style'] = 'incons4';
			}elseif($_G['timestamp']<$data['endtime']) {
				$data['status'] = '进行中';
				$data['status_style'] = 'incons2';
			} else {
				$data['status'] = '已结束';
				$data['status_style'] = 'incons4';
			}

			//获取报名人数
			$data['zy'] = DB::result_first("SELECT count(*) FROM ".DB::table('xj_eventthread')." WHERE eid=".$data['eid']);
			$data['bm'] = DB::result_first("SELECT SUM(applynumber) FROM ".DB::table('xj_eventapply')." WHERE tid=".$data['tid']);
			$data['bm'] = $data['bm']?$data['bm']:0;
			//活动类型
			if($data['postclass'] == 1){
				$tmp = explode("\n",$_G['cache']['plugin']['xj_event']['event_offline_class']);
				foreach($tmp as $key=>$value){
					$eventclass = explode("|",$value);
					if($eventclass[0] == $data['offlineclass']){
						break;
					}
				}
				$data['eventclass'] = $eventclass[1];
			}else{
				$tmp = explode("\n",$_G['cache']['plugin']['xj_event']['event_online_class']);
				foreach($tmp as $key=>$value){
					$eventclass = explode("|",$value);
					if($eventclass[0] == $data['onlineclass']){
						break;
					}
				}
				$data['eventclass'] = $eventclass[1];
			}
			$data['event_number'] = $data['event_number']==0?'不限':$data['event_number'];
			
			$list[] = array(
				'id' => $data['tid'],
				'idtype' => 'tid',
				'title' => cutstr($data['subject'],$titlelength),
				'url' => 'forum.php?mod=viewthread&tid='.$data['tid'],
				'pic' => $data['activityaid_url'],
				'summary' => messagecutstr($data['message'],$summarylength),
				'fields' => array(
				  'status' => $data['status'],
				  'status_style' => $data['status_style'],
				  'starttime' => dgmdate($data['starttime'],'d'),
				  'endtime' => dgmdate($data['endtime'],'d'),
				  'applybegin' => dgmdate($data['activitybegin'],'d'),
				  'applyend' => dgmdate($data['activityexpiration'],'d'),
				  'zy' => $data['zy'],
				  'bm' => $data['bm'],
				  'author' => $data['author'],
				  'authorid' => $data['authorid'],
				  'postclass' => $data['postclass']==1?'线下活动':'线上活动',
				  'eventclass' => $data['eventclass'],
				  'address' => $data['event_address'],
				  'cost' => $data['use_cost']>0?$data['use_cost'].'元':'免费',
				  'eventnumber' => $data['event_number'],
				)
			);
		}
		return array('html' => '', 'data' => $list);
	}
}

?>