<?php
/*
 * 主页：http://addon.discuz.com/?@ailab
 * 人工智能实验室：Discuz!应用中心十大优秀开发者！
 * 插件定制 联系QQ594941227
 * From www.ailab.cn
 */
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class plugin_nimba_majia {
	function __construct(){
		global $_G;
		loadcache('plugin');
		$this->newip=$_G['clientip'];
		$this->allow=$this->allow();
	}

	function allow(){
		loadcache('plugin');
		global $_G;
		$var= $_G['cache']['plugin']['nimba_majia'];
		$groups=unserialize($var['groups']);
		$return=in_array($_G['groupid'],$groups)? 1:0;
		return $return;
	}

    function global_usernav_extra2(){
		global $_G;
	    loadcache('plugin');
		if($this->allow) return '<span class="pipe">|</span><a href="home.php?mod=spacecp&ac=plugin&id=nimba_majia:admincp" target="_blank"><span style="font: bold Verdana; color: #f15a29;">'.lang('plugin/nimba_majia', 'appname').'</span></a>';
		else return '';
    }

    function global_myitem_extra(){
		global $_G;
	    loadcache('plugin');
	    if($this->allow) return '<a href="home.php?mod=spacecp&ac=plugin&id=nimba_majia:admincp" target="_blank">马甲</a>';
		else return '';
    }

}

class plugin_nimba_majia_forum extends plugin_nimba_majia{

	function viewthread_fastpost_side_output() {//帖子查看页，底部快速回复
		loadcache('plugin');
		global $_G;
		if($this->allow){
			$repeatusers = array();
			$nm_table = DB::table('nimba_majia');
			$mjid = DB::result_first("SELECT `mjid` FROM $nm_table WHERE `useruid`='$_G[uid]'");
			if ($mjid) {
				$query = DB::query("SELECT * FROM $nm_table WHERE `mjid`='$mjid' AND `useruid`<>'$_G[uid]'");
			}
			while($majia=DB::fetch($query)){
				$repeatusers[]=$majia;
			}
			include template('nimba_majia:viewthread_fastpost_side');
			return $return;
		}else return '';
	}

	function post_side_bottom_output() {//常规发帖页，右侧选项
		loadcache('plugin');
		global $_G;
		if($this->allow){
			$repeatusers = array();
			$nm_table = DB::table('nimba_majia');
			$mjid = DB::result_first("SELECT `mjid` FROM $nm_table WHERE `useruid`='$_G[uid]'");
			if ($mjid) {
				$query = DB::query("SELECT * FROM $nm_table WHERE `mjid`='$mjid' AND `useruid`<>'$_G[uid]'");
			}
			while($majia=DB::fetch($query)){
				$repeatusers[]=$majia;
			}
			include template('nimba_majia:post_side_bottom');
			return $return;
		}else return '';
	}

	function forumdisplay_fastpost_content(){//帖子列表页，底部快速发帖
		loadcache('plugin');
		global $_G;
		if($this->allow){
			$repeatusers = array();
			$nm_table = DB::table('nimba_majia');
			$mjid = DB::result_first("SELECT `mjid` FROM $nm_table WHERE `useruid`='$_G[uid]'");
			if ($mjid) {
				$query = DB::query("SELECT * FROM $nm_table WHERE `mjid`='$mjid' AND `useruid`<>'$_G[uid]'");
			}
			while($majia=DB::fetch($query)){
				$repeatusers[]=$majia;
			}
			include template('nimba_majia:forumdisplay_fastpost_conten');
			return $return;
		}else return '';
	}

	function post_infloat_top(){//ajax浮动窗口回复
		loadcache('plugin');
		global $_G;
		if($this->allow){
			$repeatusers = array();
			$nm_table = DB::table('nimba_majia');
			$mjid = DB::result_first("SELECT `mjid` FROM $nm_table WHERE `useruid`='$_G[uid]'");
			if ($mjid) {
				$query = DB::query("SELECT * FROM $nm_table WHERE `mjid`='$mjid' AND `useruid`<>'$_G[uid]'");
			}
			while($majia=DB::fetch($query)){
				$repeatusers[]=$majia;
			}
			include template('nimba_majia:post_infloat_top');
			return $return;
		}else return '';
	}

	function post_middle(){//>=X3.0
		loadcache('plugin');
		global $_G;
		require_once DISCUZ_ROOT.'./source/discuz_version.php';
		$right=1;
		if(strtolower(substr(DISCUZ_VERSION,0,2))=='x2') $right=0;
		if($right==1&&$this->allow){
			$repeatusers = array();
			$nm_table = DB::table('nimba_majia');
			$mjid = DB::result_first("SELECT `mjid` FROM $nm_table WHERE `useruid`='$_G[uid]'");
			if ($mjid) {
				$query = DB::query("SELECT * FROM $nm_table WHERE `mjid`='$mjid' AND `useruid`<>'$_G[uid]'");
			}
			while($majia=DB::fetch($query)){
				$repeatusers[]=$majia;
			}
			include template('nimba_majia:post_middle');
			return $return;
		}else return '';
	}

	function post_feedlog_message($var){
		global $_G,$thread,$tid,$pid;
		$tid = $var['param'][2]['tid'];
		$pid = $var['param'][2]['pid'];
		$action=$var['param'][0];

    /*
    @require_once libfile('function/cache');
    $cacheArray='';
    $cacheArray .= "\$var=".arrayeval($var).";\n";
    $cacheArray .= "\$_GET=".arrayeval($_GET).";\n";
    writetocache('m_'.$pid, $cacheArray);
    */
    $_GET['majiauid']=intval($_GET['majiauid']);
    if (!$_GET['majiauid']||($_GET['action']=='reply'&&!$pid)||!$this->allow ) {
      return '';//|| ($_GET['action']=='newthread' && !$tid)
    }
		$uid=$_GET['majiauid'];
		if ($uid!=$_G['uid']) {
			$nm_table = DB::table('nimba_majia');
			$mjid = DB::result_first("SELECT `mjid` FROM $nm_table WHERE `useruid`='$_G[uid]'");
			$check = DB::fetch_first("SELECT * FROM $nm_table WHERE `mjid`='$mjid' AND `useruid`='$uid'");
			if ($check) {
				$member_table = DB::table('common_member');
				$userdata = DB::fetch_first("SELECT * FROM `$member_table` WHERE `uid` = '$uid'");
        if (in_array($userdata['groupid'], array(4,5,6,7,8,9))) {
          return FALSE;
        }
				if ($userdata['password']==$check['password']) {

					$username = daddslashes($check['username']); //使用马甲
					$do=0;
					if($action=='post_reply_succeed' || $action=='post_reply_mod_succeed'){//new reply
						$tableid = C::t('forum_post')->getposttablebytid($tid);
            C::t('forum_post')->update($tableid, $pid, array('authorid'=>$uid,'author'=>$username,'useip'=>$this->newip));
            C::t('forum_thread')->update($tid,array('lastposter=\''.$username.'\''),false,false,0,true);
            $posts_master = DB::result_first("SELECT COUNT(*) FROM ".DB::table('forum_post')." WHERE `authorid` = {$_G['uid']}");
            $posts_slave = DB::result_first("SELECT COUNT(*) FROM ".DB::table('forum_post')." WHERE `authorid` = {$uid}");
            C::t('common_member_count')->update($_G['uid'], array('posts'=>$posts_master));  // Enicn_d 2017-01-17
            C::t('common_member_count')->update($uid, array('posts'=>$posts_slave));  // Enicn_d 2017-01-17
            // C::t('common_member_count')->increase(array($_G['uid']),array('posts'=>-1));
            // C::t('common_member_count')->increase(array($uid),array('posts'=>1));
            $subject=htmlspecialchars_decode(DB::result_first("select subject from ".DB::table('forum_thread')." where tid='$tid'"));
            $lastpost = "$tid\t$subject\t".TIMESTAMP."\t".$username;
            C::t('forum_forum')->update(array('fid'=>$_GET['fid']),array('lastpost'=>$lastpost));
						$reppid=intval($_GET['reppid']);
						if($reppid) $first=DB::result_first("select first from ".DB::table('forum_post')." where pid='$reppid'");
						else $first=1;//fastpost or fastpost's AdvanceMode has no $_GET['reppid']
						if(!$first){//reply other reply !$first||$_GET['noticetrimstr']
							$notice=DB::fetch_first("select id,note from ".DB::table('home_notification')." where new=1 and authorid='".$_G['uid']."' and type='post' and from_idtype='quote' order by dateline desc");
							$noteid=intval($notice['id']);
							$note=array();
							$note['note']=str_replace($_G['username'],$username,str_replace('uid='.$_G['uid'],'uid='.$uid,$notice['note']));
							$note['authorid']=$uid;
							$note['author']=$username;
							DB::update('home_notification',$note,array('id'=>$noteid));
						}else{//reply thread
							$notice=DB::fetch_first("select id,note from ".DB::table('home_notification')." where new=1 and authorid='".$_G['uid']."' and type='post' and from_idtype='post' order by dateline desc");
							$noteid=intval($notice['id']);
							$note=array();
							$note['note']=str_replace($_G['username'],$username,str_replace('uid='.$_G['uid'],'uid='.$uid,$notice['note']));
							$note['authorid']=$uid;
							$note['author']=$username;
							DB::update('home_notification',$note,array('id'=>$noteid));
						}
						$attach_num=DB::result_first("select count(*) from ".DB::table('forum_attachment')." where uid='".$_G['uid']."' and tid='$tid' and pid='$pid'");
						if($attach_num){//update attachment
							DB::update('forum_attachment',array('uid'=>$uid),array('uid'=>$_G['uid'],'tid'=>$tid,'pid'=>$pid));
							$tableid=intval($tid%10);
							DB::update('forum_attachment_'.$tableid,array('uid'=>$uid),array('uid'=>$_G['uid'],'tid'=>$tid,'pid'=>$pid));
						}
            /**
            Modified by Enicn_D 2015-04-08 10:46
            **/
            $extcredits = $_G['setting']['extcredits'];
            $credit_rule = array_shift(C::t('common_credit_rule')->fetch_all_by_action('reply'));
            foreach ($extcredits as $extkey => $extval) {
                $extcredits_name = 'extcredits'.$extkey;
                $extcredits_value = intval($credit_rule[$extcredits_name]);
                C::t('common_member_count')->increase(array($_G['uid']),array($extcredits_name => -1 * $extcredits_value));
                C::t('common_member_count')->increase(array($uid),array($extcredits_name => $extcredits_value));
            }
            $home_feed_table = DB::table('home_feed');
            $home_feed_update = "UPDATE $home_feed_table SET `uid` = '$uid', `username` = '$username' WHERE `id` = $tid AND `idtype` = 'tid'";
            DB::query($home_feed_update);
            if(!empty($_GET['adddynamic'])) {
                $feed_table = DB::table('home_follow_feed');
                $feed_update = "UPDATE $feed_table SET `uid` = '$uid', `username` = '$username' WHERE `tid` = $tid";
                DB::query($feed_update);
                C::t('common_member_count')->increase($_G['uid'], array('feeds'=>-1));
                C::t('common_member_count')->increase($uid, array('feeds'=>1));
            }
            /**

            */
					}elseif($action=='post_newthread_succeed' || $action=='post_newthread_mod_succeed'){//newthread
            $tableid = C::t('forum_post')->getposttablebytid($tid);
            C::t('forum_post')->update($tableid, $pid, array('authorid'=>$uid,'author'=>$username,'useip'=>$this->newip));
						C::t('forum_thread')->update($tid, array('authorid'=>$uid,'author'=>$username,'lastposter'=>$username));
            $threads_master = DB::result_first("SELECT COUNT(*) FROM ".DB::table('forum_thread')." WHERE `authorid` = {$_G['uid']}");
            $threads_slave = DB::result_first("SELECT COUNT(*) FROM ".DB::table('forum_thread')." WHERE `authorid` = {$uid}");
            $posts_master = DB::result_first("SELECT COUNT(*) FROM ".DB::table('forum_post')." WHERE `authorid` = {$_G['uid']}");
            $posts_slave = DB::result_first("SELECT COUNT(*) FROM ".DB::table('forum_post')." WHERE `authorid` = {$uid}");
            C::t('common_member_count')->update($_G['uid'], array('threads'=>$threads_master,'posts'=>$posts_master));  // Enicn_d 2017-01-17
            C::t('common_member_count')->update($uid, array('threads'=>$threads_slave,'posts'=>$posts_slave));  // Enicn_d 2017-01-17
						// C::t('common_member_count')->increase(array($_G['uid']),array('threads'=>-1,'posts'=>-1));
						// C::t('common_member_count')->increase(array($uid),array('threads'=>1,'posts'=>1));
						$subject=htmlspecialchars_decode(DB::result_first("select subject from ".DB::table('forum_thread')." where tid='$tid'"));
						$lastpost = "$tid\t$subject\t".TIMESTAMP."\t".$username;
						C::t('forum_forum')->update(array('fid'=>$_GET['fid']),array('lastpost'=>$lastpost));
						$attach_num=DB::result_first("select count(*) from ".DB::table('forum_attachment')." where uid='".$_G['uid']."' and tid='$tid' and pid='$pid'");
						if($attach_num){//update attachment
							DB::update('forum_attachment',array('uid'=>$uid),array('uid'=>$_G['uid'],'tid'=>$tid,'pid'=>$pid));
							$tableid=intval($tid%10);
							DB::update('forum_attachment_'.$tableid,array('uid'=>$uid),array('uid'=>$_G['uid'],'tid'=>$tid,'pid'=>$pid));
						}
            /**
            Modified by Enicn_D 2015-04-08 10:46
            **/
            $extcredits = $_G['setting']['extcredits'];
            $credit_rule = array_shift(C::t('common_credit_rule')->fetch_all_by_action('post'));
            foreach ($extcredits as $extkey => $extval) {
                $extcredits_name = 'extcredits'.$extkey;
                $extcredits_value = intval($credit_rule[$extcredits_name]);
                C::t('common_member_count')->increase(array($_G['uid']),array($extcredits_name => -1 * $extcredits_value));
                C::t('common_member_count')->increase(array($uid),array($extcredits_name => $extcredits_value));
            }
            if(!empty($_GET['adddynamic'])) {
                $feed_table = DB::table('home_follow_feed');
                $feedid = DB::result_first("SELECT `feedid` FROM $feed_table WHERE `tid` = $tid");
                // $feed_update = "UPDATE $feed_table SET `uid` = '$uid', `username` = '$username' WHERE `tid` = $tid";
                // DB::query($feed_update);
                C::t('home_follow_feed')->update($feedid, array('uid'=>$uid,'username'=>$username));
                C::t('common_member_count')->increase($_G['uid'], array('feeds'=>-1));
                C::t('common_member_count')->increase($uid, array('feeds'=>1));
            }
            /**

            */
					}
					require_once DISCUZ_ROOT.'./source/class/class_credit.php'; //update credit counting
					credit::countcredit($uid);
					credit::countcredit($_G['uid']);
					// DB::update('common_member_status',array('lastvisit'=>TIMESTAMP,'lastactivity'=>TIMESTAMP,'lastpost'=>TIMESTAMP),array('uid'=>$uid));
					C::t('common_member_status')->update($uid, array('lastvisit'=>TIMESTAMP,'lastactivity'=>TIMESTAMP,'lastpost'=>TIMESTAMP));

				} else {
					DB::delete('nimba_majia', '`useruid`='.$check['useruid']);
				}
			}
		}
	}
}
class plugin_nimba_majia_group extends plugin_nimba_majia_forum{
}
?>
