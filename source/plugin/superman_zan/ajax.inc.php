<?php
/**
 *  [【超人】点赞(superman_zan.{modulename})] (C)2012-2099 Powered by 时创科技.
 *  Version: $VERSION
 *  Date: $DATE
 */
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
define('SC_DEBUG', false);

ob_end_clean();
ob_start();
@header("Expires: -1");
@header("Cache-Control: no-store, private, post-check=0, pre-check=0, max-age=0", FALSE);
@header("Pragma: no-cache");
@header("Content-type: text/xml; charset=".CHARSET);

$pid = intval($_GET['pid'])>0?intval($_GET['pid']):0;
if (!$pid) {
    _sc_exit('not found pid');
}

$tmp_result = DB::fetch_first('SELECT `tid`,`authorid`,`first` FROM '.DB::table('forum_post').' WHERE `pid`='.$pid);
$authorid = intval($tmp_result['authorid']);
$tid = intval($tmp_result['tid']);
$first = intval($tmp_result['first']);

loadcache('plugin');	
if (!isset($_G['cache']['plugin']['superman_zan'])) {
	_sc_exit('loadcache(plugin) null');
}
$config = $_G['cache']['plugin']['superman_zan'];
if (!$config['open']) {
	_sc_exit('plugin closed');
}

loadcache('superman_zan');
if (!isset($_G['cache']['superman_zan'])) {
	_sc_exit('loadcache(superman_zan) null');
}
$mylang = lang('plugin/superman_zan');
$setting		= $_G['cache']['superman_zan'];
$visitor_radio	= $setting['visitor_radio']?1:0;
$zan_self_radio	= $setting['zan_self_radio']?1:0;
if (submitcheck('formhash', 1)) {
    if (!$visitor_radio && !$_G['uid']) {
        echo '<?xml version="1.0" encoding="'.CHARSET.'"?><root><![CDATA[-1]]></root>';
        exit;
    }
	if (!$zan_self_radio && ($authorid==$_G['uid'])) {
        echo '<?xml version="1.0" encoding="'.CHARSET.'"?><root><![CDATA[-2]]></root>';
        exit;
    }
    $ref	= DB::result_first('SELECT COUNT(*) FROM '.DB::table('plugin_superman_zan_ref').' WHERE `pid` = '.$pid.' AND `uid` ='.$_G['uid']);
    $count	= DB::result_first('SELECT `count` FROM '.DB::table('plugin_superman_zan').' WHERE `pid` = '.$pid);

    if ($count=='') {
        DB::insert('plugin_superman_zan',array('pid'=>$pid,'tid'=>$tid,'count'=>0,'first'=>$first));
        $count = 0;
    }
    if ($_G['uid']) {
        global $_G;
        if (empty($ref)) {
            DB::insert('plugin_superman_zan_ref',array('pid'=>$pid,'uid'=>$_G['uid'],'dateline'=>$_G['timestamp']));
            DB::update('plugin_superman_zan',array('count'=>$count+1),array('pid'=>$pid));
            if($_G['uid'] != $authorid) {
				$suject = DB::result_first('SELECT `subject` FROM ' . DB::table('forum_thread') . ' WHERE `tid` = ' . $tid);
				if($first == 1){
					$zan_message = $mylang['dianzan_noticeauthor_reply'];
					$zan_message = str_replace('{tid}', $tid, $zan_message);
					$zan_message = str_replace('{subject}', $suject, $zan_message);
					notification_add($authorid, 'post', $zan_message);
				} else {
					$zan_message = $mylang['dianzan_noticeauthor_reply_reply'];
					$zan_message = str_replace('{tid}', $tid, $zan_message);
					$zan_message = str_replace('{subject}', $suject, $zan_message);
					notification_add($authorid, 'post', $zan_message);
				}

				updatecreditbyaction('dztz', $_G['uid']);
				updatecreditbyaction('tzbzjjf', $authorid);
				//updatemembercount($_G['uid'], array($_G['setting']['creditstransextra'][1] => $setting['zan_user_count']), 1, '你赞了'.$authorid.'的帖子');
				//updatemembercount($authorid, array($_G['setting']['creditstransextra'][1] => $setting['zan_author_count']), 1, '你的帖子被'.$_G['uid'].'赞了');
            }
            echo '<?xml version="1.0" encoding="'.CHARSET.'"?><root><![CDATA[1]]></root>';
        } else {
            echo '<?xml version="1.0" encoding="'.CHARSET.'"?><root><![CDATA[0]]></root>';
        }
    } else {
        DB::update('plugin_superman_zan',array('count'=>$count+1),array('pid'=>$pid));
        dsetcookie('pid_'.$pid, 1, 31536000);
        echo '<?xml version="1.0" encoding="'.CHARSET.'"?><root><![CDATA[1]]></root>';
    }
}
exit;
////////////////////////////////////////////////////////////////////////
function _sc_exit($msg, $exit = true)
{
	global $_G;
	if (SC_DEBUG && $_GET['_x'] == 10000) {
		echo '<pre>';
		print_r($msg);
		echo '</pre>';
	}
	if ($exit) exit;
}
