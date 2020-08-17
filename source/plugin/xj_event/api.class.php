<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: install.php 34718 2014-07-14 08:56:39Z nemohou $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class xj_event_api {

	function forumdisplay_threadBottom() {
		global $_G;
		$return = array();
		foreach($GLOBALS['threadlist'] as $thread) {

			if($_G['cache']['plugin']['xj_robfloor']){
				$xj_event = DB::result_first("SELECT * FROM ".DB::table('xj_robfloor')." WHERE tid = ".$thread['tid']);
				if($xj_event){
					$return[$thread['tid']] = '<span style=" padding:2px 8px; color:#fff; background-color:#0eaef8; -moz-border-radius: 3px; -webkit-border-radius: 3px; border-radius:3px; ">抢楼</span>';
				}else{
					$return[$thread['tid']] = '';
				}
			}
			if(!$return[$thread['tid']]){
				$xj_event = DB::result_first("SELECT * FROM ".DB::table('xj_event')." WHERE tid = ".$thread['tid']);
				if($xj_event){
					$return[$thread['tid']] = '<span style=" padding:2px 8px; color:#fff; background-color:#799edc; -moz-border-radius: 3px; -webkit-border-radius: 3px; border-radius:3px; ">'.lang('plugin/xj_event', 'huodong').'</span>';
				}else{
					$return[$thread['tid']] = '';
				}
			}
		}
		return $return;
	}

	function viewthread_threadTop() {
		require_once DISCUZ_ROOT.'./source/plugin/wechat/wechat.lib.class.php';
		global $_G;
		$return = '';
		$timestamp = time();
		$tid = $_GET['tid'];
		$siteid = $_G['wechat']['setting']['wsq_siteid'];
		$items = DB::fetch(DB::query("SELECT * FROM ".DB::table('xj_event')." WHERE tid = '$tid'"));
		if($items && $items['onlineclass']<101){
			//二级地区分类调用
			if($_G['cache']['plugin']['xj_event']['event_city']){
				if($items['citys']){
					$upid = DB::result_first("SELECT upid FROM ".DB::table('common_district')." WHERE name = '".$items['citys']."'");
					$upid = intval($upid);
					$items['province'] = DB::result_first("SELECT name FROM ".DB::table('common_district')." WHERE id = $upid");
				}
			}


		  $extcredits = $_G['setting']['extcredits'];
		  $setting = unserialize($items['setting']);
		  if($items['postclass']==1){
			  $postclass = lang('plugin/xj_event', 'xxhd');
			  $tmp = explode("\n",$_G['cache']['plugin']['xj_event']['event_offline_class']);
			  foreach($tmp as $key=>$value){
				  $eventclass = explode("|",$value);
				  if($eventclass[0] == $items['offlineclass']){
					  break;
				  }
			  }
		  }else{
			  $postclass = lang('plugin/xj_event', 'xshd');
			  $tmp = explode("\n",$_G['cache']['plugin']['xj_event']['event_online_class']);
			  foreach($tmp as $key=>$value){
				  $eventclass = explode("|",$value);
				  if($eventclass[0] == $items['onlineclass']){
					  break;
				  }
			  }
		  }
		  foreach($extcredits as $key=>$value){
			  if($key == $items['use_extcredits']){
				  $extcredit_title = $value['title'];
			  }
		  }
		  $citys = $items['citys'];
		  $starttime = dgmdate($items['starttime'],'dt');
		  $endtime = dgmdate($items['endtime'],'dt');
		  $activitybegin = dgmdate($items['activitybegin'],'dt');
		  $activityexpiration = dgmdate($items['activityexpiration'],'dt');
		  if(!$items['activityaid'] and $items['activityaid_url']){
			  $imgurl = $items['activityaid_url'];
		  }else{
			  //$imgurl = $this->_getpicurl($items['activityaid'],$tid);
			  $imgurl = getforumimg($items['activityaid'],0,360,230);
		  }
		  if(!$items['activityaid'] && !$items['activityaid_url']){
		  	$imgurl = 'static/image/common/nophoto.gif';
		  }

		  //活动管理列表
		  $event_adminlist = implode(',',$setting['event_admin']);
		  //报名审核状态
		  $apply = DB::fetch_first("SELECT applyid,pay_state,verify,seccode FROM ".DB::table('xj_eventapply')." WHERE tid='$tid' and uid=".$_G['uid']);
		  $verify = $apply['verify'];
		  $pay_state = $apply['pay_state'];



		  $return .= '<img src="'.$_G['siteurl'].$imgurl.'" width="100%"><br>';
		  $return .= '<span style="color:#799edc;">'.lang('plugin/xj_event', 'huodongfs').'</span>:'.$postclass.'<br>';
		  $return .= '<span style="color:#799edc;">'.lang('plugin/xj_event', 'huodonglx').'</span>:'.$eventclass[1].'<br>';
		  $return .= '<span style="color:#799edc;">'.lang('plugin/xj_event', 'huodongdq').'</span>:'.$items['province'].$citys.'<br>';
		  $return .= '<span style="color:#799edc;">'.lang('plugin/xj_event', 'kashisj').'</span>:'.$starttime.'<br>';
		  $return .= '<span style="color:#799edc;">'.lang('plugin/xj_event', 'jieshusj').'</span>:'.$endtime.'<br>';
		  $return .= '<span style="color:#799edc;">'.lang('plugin/xj_event', 'huodongdd').'</span>:'.$items['event_address'].'<br>';
		  $return .= '<span style="color:#799edc;">'.lang('plugin/xj_event', 'huodonggl').'</span>:'.$event_adminlist.'<br>';

		  if($items[event_number]==0){
			  $cjme = lang('plugin/xj_event', 'buxian');
		  }else{
			  $cjme = $items['event_number'].lang('plugin/xj_event', 'ren');
		  }
		  $return .= '<span style="color:#799edc;">'.lang('plugin/xj_event', 'canjiame').'</span>:'.$cjme.'<br>';

		  //报名通过总人数
		  $applycountnumber = DB::result_first("SELECT SUM(applynumber) FROM ".DB::table('xj_eventapply')." WHERE tid='$tid' and verify=1");
		  $applycountnumber = !$applycountnumber?0:$applycountnumber;
		  $applycountnumberd = DB::result_first("SELECT SUM(applynumber) FROM ".DB::table('xj_eventapply')." WHERE tid='$tid' and verify=0");
		  $applycountnumberd = !$applycountnumberd?0:$applycountnumberd;
		  $return .= '<span style="color:#799edc;">'.lang('plugin/xj_event', 'baomingrs').'</span>:'.$applycountnumber.lang('plugin/xj_event', 'rendaish').$applycountnumberd.lang('plugin/xj_event', 'ren').'<br>';


		  //参加费用
		  if($setting['eventaa']){
		  	$cjfy = 'AA';
			if($items['use_cost']>0){
				$cjfy .= ' '.$items['use_cost'].lang('plugin/xj_event', 'yuanzy');
			}
		  }else{
		  	$cjfy = $items['use_cost'].lang('plugin/xj_event', 'yuan');
		  }
		  $return .= '<span style="color:#799edc;">'.lang('plugin/xj_event', 'canjiafy').'</span>:'.$cjfy.'<br>';
		  $return .= '<span style="color:#799edc;">'.lang('plugin/xj_event', 'baomingks').'</span>:'.$activitybegin.'<br>';
		  $return .= '<span style="color:#799edc;">'.lang('plugin/xj_event', 'baominjz').'</span>:'.$activityexpiration.'<br>';

		  foreach($setting['moreitem'] as $value){
			   $return .= '<span style="color:#799edc;">'.$value['itemname'].'</span>:'.$value['itemcontent'].'<br>';
		  }


		  $bmurl = WeChatHook::getPluginUrl('xj_event:wsq_join', array('tid' => $tid));
		  $bmurl = $_G['siteurl']."plugin.php?id=xj_event:wsq_join&tid=$tid";
		  $qxbmurl = $_G['siteurl']."plugin.php?id=xj_event:wsq_join_save&action=cannel&tid=$tid&&formhash=".$_G['formhash'];
		  $xgbmurl = $_G['siteurl']."plugin.php?id=xj_event:event_joinmodify&s=wsq&applyid=".$apply['applyid'];
		  $zffyurl = $_G['siteurl']."plugin.php?id=xj_event:event_pay&tid=$tid";
		  //发作业URL
		  if($setting['eventzy_fid']){
			  $fbzyurl = $_G['siteurl']."forum.php?mod=post&action=newthread&fid=".$setting['eventzy_fid']."&eid=".$items['eid'];
		  }else{
		  	  $fbzyurl = $_G['siteurl']."forum.php?mod=post&action=newthread&fid=".$_G['fid']."&eid=".$items['eid'];
		  }
			//回复才可以报名
			$bmbtnshow = true;
				if($setting['reply']){
				$replys = DB::result_first("SELECT count(*) FROM ".DB::table('forum_post')." WHERE tid='$tid' AND first<>1 AND invisible>=0 AND authorid = ".$_G['uid']);
				if($replys<1){
					$bmbtnshow = false;
				}
			}

		  //线下活动报名按钮
		  if($items['postclass']==1){
			//线下活动按钮-->
			//条件当前时间小于（活动结束+30天）的时间
			if($_G['timestamp']<($items['endtime']+2592000)){
				if($items['activityexpiration']<=$_G['timestamp'] and $verify==null){
					$return .= '<a href="javascript:" style=" margin-top:5px; text-align:center; color:#fff; font-size:14px;width:120px; background-color:#ccc;padding:5px 15px;display:block; -moz-border-radius: 3px; -webkit-border-radius: 3px; border-radius:3px;">'.lang('plugin/xj_event', 'baominjz').'</a>';
				}else{
					if(!$_G['uid']){
						$return .= '<a href="http://wsq.discuz.com/?c=index&a=viewthread&f=wx&tid='.$tid.'&siteid='.$siteid.'&_bpage=1&siteuid=0&login=yes" style="margin-top:5px; text-align:center; color:#fff; font-size:14px;width:120px; background-color:#ccc;padding:5px 15px;display:block; -moz-border-radius: 3px; -webkit-border-radius: 3px; border-radius:3px;">'.lang('plugin/xj_event', 'woycjqdl').'</a>';
					}else{
						if($items['activitybegin']<$_G['timestamp'] and $items['activityexpiration']>$timestamp and $verify==null){
							if($bmbtnshow){
								$return .= '<a href="'.$bmurl.'" style="margin-top:5px; text-align:center; color:#fff; font-size:14px;width:120px; background-color:#ff5400;padding:5px 15px;display:block; -moz-border-radius: 3px; -webkit-border-radius: 3px; border-radius:3px;">'.lang('plugin/xj_event', 'woyaocj').'</a>';
							}
							if($setting[reply] && $replys<1){
								$return .= '<a href="javascript:" style="margin-top:5px; text-align:center; color:#fff; font-size:14px;width:120px; background-color:#ccc;padding:5px 15px;display:block; -moz-border-radius: 3px; -webkit-border-radius: 3px; border-radius:3px;">'.lang('plugin/xj_event', 'hfhkybm').'</a>';
							}
						}elseif($items['activitybegin']>$timestamp and $verify==null){
							$return .= '<a href="javascript:" style="margin-top:5px; text-align:center; color:#fff; font-size:14px;width:120px; background-color:#ccc;padding:5px 15px;display:block; -moz-border-radius: 3px; -webkit-border-radius: 3px; border-radius:3px;">'.lang('plugin/xj_event', 'baomhwks').'</a>';
						}else{
							if($verify==0){
								if(!$pay_state){
									$return .= '<a href="'.$qxbmurl.'" style="margin-top:5px; text-align:center; color:#fff; font-size:14px;width:120px; background-color:#ccc;padding:5px 15px;display:block; -moz-border-radius: 3px; -webkit-border-radius: 3px; border-radius:3px;">'.lang('plugin/xj_event', 'quxiaobm').'</a>';
								}
								if($setting['eventpay']){
									if($pay_state){
										$return .= '<a href="javascript:" style="margin-top:5px; text-align:center; color:#fff; font-size:14px;width:120px; background-color:#ccc;padding:5px 15px;display:block; -moz-border-radius: 3px; -webkit-border-radius: 3px; border-radius:3px;">'.lang('plugin/xj_event', 'fyyfdsh').'</a>';
									}else{
										$return .= '<a href="'.$zffyurl.'" style="margin-top:5px; text-align:center; color:#fff; font-size:14px;width:120px; background-color:#ccc;padding:5px 15px;display:block; -moz-border-radius: 3px; -webkit-border-radius: 3px; border-radius:3px;">'.lang('plugin/xj_event', 'zhifufy').'</a>';
									}
								}
							}else{
								if($setting['eventzy_enable']){
									$return .= '<a href="'.$fbzyurl.'" style="margin-top:5px; text-align:center; color:#fff; font-size:14px;width:120px; background-color:#8bd911;padding:5px 15px;display:block; -moz-border-radius: 3px; -webkit-border-radius: 3px; border-radius:3px;">'.lang('plugin/xj_event', 'fabiao').$setting['eventzy_name'].'</a>';
								}
								if($setting['canceljoin']==1 && $items['activityexpiration']>$timestamp){
									$return .= '<a href="'.$qxbmurl.'" style="margin-top:5px; text-align:center; color:#fff; font-size:14px;width:120px; background-color:#ccc;padding:5px 15px;display:block; -moz-border-radius: 3px; -webkit-border-radius: 3px; border-radius:3px;">'.lang('plugin/xj_event', 'quxiaobm').'</a>';
								}
								if($setting['seccode']){
									$return .= '<div>'.lang('plugin/xj_event', 'ndbmyzm').':'.$apply['seccode'].'</div>';
								}
							}
							if($_G['timestamp'] < $items['activityexpiration']){
								$return .= '<a href="'.$xgbmurl.'" style="margin-top:5px; text-align:center; color:#fff; font-size:14px;width:120px; background-color:#ff5400;padding:5px 15px;display:block; -moz-border-radius: 3px; -webkit-border-radius: 3px; border-radius:3px;">'.lang('plugin/xj_event', 'xgbmzl').'</a>';
							}
						}
					}
				}
			}
		  }

		  //线上活动报名按钮
		  if($items['postclass']==2){
			//线下活动按钮-->
			//条件当前时间小于（活动结束+30天）的时间
			if($_G['timestamp']<($items['endtime'])){
				if($items['activityexpiration']<=$_G['timestamp'] and $verify==null){
					$return .= '<a href="javascript:" style="margin-top:5px; text-align:center; color:#fff; font-size:14px;width:120px; background-color:#ccc;padding:5px 15px;display:block; -moz-border-radius: 3px; -webkit-border-radius: 3px; border-radius:3px;">'.lang('plugin/xj_event', 'baominjz').'</a>';
				}else{
					if(!$_G['uid']){
						$return .= '<a href="http://wsq.discuz.com/?c=index&a=viewthread&f=wx&tid='.$tid.'&siteid='.$siteid.'&_bpage=1&siteuid=0&login=yes" style="margin-top:5px; text-align:center; color:#fff; font-size:14px;width:120px; background-color:#ccc;padding:5px 15px;display:block; -moz-border-radius: 3px; -webkit-border-radius: 3px; border-radius:3px;">'.lang('plugin/xj_event', 'woycjqdl').'</a>';
					}else{
						if($items['activitybegin']<$_G['timestamp'] and $items['activityexpiration']>$timestamp and $verify==null){
							if($bmbtnshow){
								$return .= '<a href="'.$bmurl.'" style="margin-top:5px; text-align:center; color:#fff; font-size:14px;width:120px; background-color:#ff5400;padding:5px 15px;display:block; -moz-border-radius: 3px; -webkit-border-radius: 3px; border-radius:3px;">'.lang('plugin/xj_event', 'woyaocj').'</a>';
							}
							if($setting[reply] && $replys<1){
								$return .= '<a href="javascript:" style="margin-top:5px; text-align:center; color:#fff; font-size:14px;width:120px; background-color:#ccc;padding:5px 15px;display:block; -moz-border-radius: 3px; -webkit-border-radius: 3px; border-radius:3px;">'.lang('plugin/xj_event', 'hfhkybm').'</a>';
							}
						}elseif($items['activitybegin']>$timestamp and $verify==null){
							$return .= '<a href="javascript:" style="margin-top:5px; text-align:center; color:#fff; font-size:14px;width:120px; background-color:#ccc;padding:5px 15px;display:block; -moz-border-radius: 3px; -webkit-border-radius: 3px; border-radius:3px;">'.lang('plugin/xj_event', 'baomhwks').'</a>';
						}else{
							if($verify==0){
								if(!$pay_state){
									$return .= '<a href="'.$qxbmurl.'" style=" margin-top:5px;text-align:center; color:#fff; font-size:14px;width:120px; background-color:#ccc;padding:5px 15px;display:block; -moz-border-radius: 3px; -webkit-border-radius: 3px; border-radius:3px;">'.lang('plugin/xj_event', 'quxiaobm').'</a>';
								}
								if($setting['eventpay']){
									if($pay_state){
										$return .= '<a href="javascript:" style=" margin-top:5px;text-align:center; color:#fff; font-size:14px;width:120px; background-color:#ccc;padding:5px 15px;display:block; -moz-border-radius: 3px; -webkit-border-radius: 3px; border-radius:3px;">'.lang('plugin/xj_event', 'fyyfdsh').'</a>';
									}else{
										$return .= '<a href="'.$zffyurl.'" style=" margin-top:5px;text-align:center; color:#fff; font-size:14px;width:120px; background-color:#ccc;padding:5px 15px;display:block; -moz-border-radius: 3px; -webkit-border-radius: 3px; border-radius:3px;">'.lang('plugin/xj_event', 'zhifufy').'</a>';
									}
								}
							}else{
								if($setting['eventzy_enable']){
									$return .= '<a href="'.$fbzyurl.'" style=" margin-top:5px;text-align:center; color:#fff; font-size:14px;width:120px; background-color:#8bd911;padding:5px 15px;display:block; -moz-border-radius: 3px; -webkit-border-radius: 3px; border-radius:3px;">'.lang('plugin/xj_event', 'fabiao').$setting['eventzy_name'].'</a>';
								}
								if($setting['canceljoin']==1 && $items['activityexpiration']>$timestamp){
									$return .= '<a href="'.$qxbmurl.'" style=" margin-top:5px;text-align:center; color:#fff; font-size:14px;width:120px; background-color:#ccc;padding:5px 15px;display:block; -moz-border-radius: 3px; -webkit-border-radius: 3px; border-radius:3px;">'.lang('plugin/xj_event', 'quxiaobm').'</a>';
								}
								if($setting['seccode']){
									$return .= '<div>'.lang('plugin/xj_event', 'ndbmyzm').':'.$apply['seccode'].'</div>';
								}
							}
							if($_G['timestamp'] < $items['activityexpiration']){
								$return .= '<a href="'.$xgbmurl.'" style="margin-top:5px; text-align:center; color:#fff; font-size:14px;width:120px; background-color:#ff5400;padding:5px 15px;display:block; -moz-border-radius: 3px; -webkit-border-radius: 3px; border-radius:3px;">'.lang('plugin/xj_event', 'xgbmzl').'</a>';
							}
						}
					}
				}
			}
		  }

		}

		return $return;
	}


	function forumdisplay_sideBar(){
		require_once DISCUZ_ROOT.'./source/plugin/wechat/wechat.lib.class.php';
		$eventcenterurl = WeChatHook::getPluginUrl('xj_event:wsq_event_center', array());
		$myeventurl = WeChatHook::getPluginUrl('xj_event:wsq_my_event', array('act'=>'cj'));
		$return = '<h3 class="sideTit">'.lang('plugin/xj_event', 'huodong').'</h3><ul><li><a href="'.$eventcenterurl.'">'.lang('plugin/xj_event', 'huodzx').'</a></li><li><a href="'.$myeventurl.'">'.lang('plugin/xj_event', 'myevent').'</a></li></ul>';
		return $return;
	}

	function viewthread_sideBar(){
		require_once DISCUZ_ROOT.'./source/plugin/wechat/wechat.lib.class.php';
		$eventcenterurl = WeChatHook::getPluginUrl('xj_event:wsq_event_center', array());
		$myeventurl = WeChatHook::getPluginUrl('xj_event:wsq_my_event', array('act'=>'cj'));
		$return = '<h3 class="sideTit">'.lang('plugin/xj_event', 'huodong').'</h3><ul><li><a href="'.$eventcenterurl.'">'.lang('plugin/xj_event', 'huodzx').'</a></li><li><a href="'.$myeventurl.'">'.lang('plugin/xj_event', 'myevent').'</a></li></ul>';
		return $return;
	}

}

?>
