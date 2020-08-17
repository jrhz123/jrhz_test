<?php
/**
 *  [【超人】点赞(superman_zan.{modulename})] (C)2012-2099 Powered by 时创科技.
 *  Version: $VERSION
 *  Date: $DATE
 */
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class mobileplugin_superman_zan
{
	protected $setting;
	protected $config;

	public function mobileplugin_superman_zan(){
		global $_G;
		if (!$_G['cache']['plugin']['superman_zan']) {
			loadcache('plugin');	
		}
		$this->config = $_G['cache']['plugin']['superman_zan'];
		$this->mobile_open = $this->config['mobile_open'];
		if (!$_G['cache']['superman_zan']) {
			loadcache('superman_zan');
		}
		$this->setting = $_G['cache']['superman_zan'];
	}
}

class mobileplugin_superman_zan_forum extends mobileplugin_superman_zan
{

	function viewthread_posttop_mobile_output(){
		if(IN_MOBILE ==1 ){
			return array();
		}
		if(!$this->mobile_open){
			return array();
		}
		global $_G;
		$mylang = lang('plugin/superman_zan');
		
		if (!$_G['tid']) {
			return;
		}		
		$visitor_radio = $this->setting['visitor_radio']?1:0;
        $formhash = FORMHASH;
		$ret =<<<EOF
<script>
function _sc_superman_zan_mobile(tid) {
	if (!$visitor_radio && !{$_G['uid']}) {
		popup.open('{$mylang['nologin_tip']}', 'confirm', 'member.php?mod=logging&action=login');
		return false;
	}
	if (getcookie('tid_'+tid) && !{$_G['uid']}) {
		popup.open('{$mylang['zan_again']}', 'alert');
		return false;
	}
	$.ajax({
		type: 'GET',
		url: 'plugin.php?id=superman_zan:ajax&formhash=$formhash&tid='+tid+'&_r='+Math.random(),
		dataType: 'xml',
		success: function(s) {
			var val = $.trim(s.lastChild.firstChild.nodeValue);

			if (val == 1) {
				popup.open("{$mylang['zan_title']} +1", 'alert');
				var num = parseInt($('_sc_zannumber').innerHTML) + 1;
				$('_sc_zannumber').innerHTML = num;
				$('_sc_zannumber_a').title = num+' {$mylang['zan_count']}';
			} 
			else if (val == -1) {
				popup.open('{$mylang["nologin_tip"]}', 'confirm', 'member.php?mod=logging&action=login');
			} 
			else if (val == 0){
				popup.open('{$mylang['zan_again']}', 'alert');
			} 
			else {
				popup.open('{$mylang['sys_error']}','alert');
			}
		}
	});
	return true;
}
</script>
<style>
.sc_img img{margin:0px 5px 20px 5px;border-radius:100px;width:35px}
.sc_img_height{height:50px;overflow:hidden;}
</style>
EOF;

		$sql = 'SELECT `count` FROM '.DB::table('plugin_superman_zan').' WHERE `tid`='.$_G['tid'];
		$count = DB::result_first($sql);
		if (empty($count)) {
			$count = 0;
		}
        $ret .= '<div class="cl"><a id="_sc_zannumber_a" title="'.$count.$mylang['zan_count'].'" onclick="return _sc_superman_zan_mobile('.$_G['tid'].')" href="javascript:void(0)" style="margin: 5px 10px 0px 10px; float:right;color:#0086CE"><i>'.$mylang['mobile_zan'].' <span id="_sc_zannumber" style="color:red;">'.'<strong>('.$count.')</strong>'.'</span></i></a></div>';
        $avatar = '';
        $zan_uid = DB::fetch_all('SELECT `uid` FROM '.DB::table('plugin_superman_zan_ref').' WHERE `tid`='.$_G['tid'].' ORDER BY dateline DESC LIMIT 0,10');
        if($zan_uid){
            foreach($zan_uid as $v){
                $avatar .= '<a target="_blank" href="home.php?mod=space&uid='.$v['uid'].'">'.avatar($v['uid'], 'small').'</a>';
            }
            $ret .='<div class="sc_img sc_img_height" style="padding:6px 0px 0px 20px;">'.$avatar.'</div>';
        }
        return array('0' => $ret);
	}
	function forumdisplay_thread_mobile_output()
	{
		if(IN_MOBILE ==1 ){
			return array();
		}
		if(!$this->mobile_open){
			return array();
		}
		global $threadlist;
		$mylang = lang('plugin/superman_zan');
		$ret = array(); 
		
		foreach($threadlist as $num => $thread){
			$z_count = DB::result_first('SELECT `count` FROM '.DB::table('plugin_superman_zan').' WHERE `tid`='.$thread[tid]);
			if ($z_count){
				$ret[$num] = '<span style="color:#0086CE;font-size:10px;margin:0px 0px 0px 10px">['.$mylang['mobile_title'].'<strong>'.$z_count.'</strong>'.$mylang['zan_title'].']</span>&nbsp;';
			}
		}
        return $ret;
	}
}
