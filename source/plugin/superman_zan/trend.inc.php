<?php
/**
 *  [【超人】点赞(superman_zan.{modulename})] (C)2012-2099 Powered by 时创科技.
 *  Version: $VERSION
 *  Date: $DATE
 */
 
if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

//error_reporting(E_ALL);
if (submitcheck('add_zan_submit')) {
	sc_superman_zan_trend::add_zan();
}
sc_superman_zan_trend::show();	
exit;

class sc_superman_zan_trend
{
	public function show()
	{
		global $_G, $pluginid;
		$mylang = lang('plugin/superman_zan');

		//showtagheader('div', 'threadlist', TRUE);
		//showformheader("plugins&operation=config&do=$pluginid&identifier=superman_zan&pmod=trend");
		
		$tzan_count = DB::result_first('SELECT COUNT(*) FROM '.DB::table('plugin_superman_zan'));

		showtableheader($mylang['zan_trend']);
		
		if (!$tzan_count) {
			showtablerow('', 'colspan="3"', $mylang['nobody_zan']);
		} else {
			loadcache('forums');
		
			$_G['setting']['zanperpage'] = 25;
			$page   = max(1, $_GET['page']);
			$start  = ($page - 1) * $_G['setting']['zanperpage'];
			$sql = "SELECT t.*,f.* FROM ".DB::table('forum_thread')." f, ".DB::table('plugin_superman_zan')." t WHERE t.tid=f.tid ORDER BY t.count DESC LIMIT $start, {$_G['setting']['zanperpage']}";
			$query = DB::query($sql);
			if($query) {
				while($value = DB::fetch($query)) {
					$tid_zans[] = $value;
				}
			}
			showsubtitle(array('subject','forum',$mylang['zans_count']));
			if($tid_zans) {
				foreach($tid_zans as $tid_zan) {
					showtablerow('', array('width=450px','width=200px','width=50px'), array(
						"<a href=\"forum.php?mod=viewthread&tid=$tid_zan[tid]\" target=\"_blank\">$tid_zan[subject]</a>",
						$_G['cache']['forums'][$tid_zan['fid']]['name'],
						$tid_zan['count'],
						'<form action="admin.php?action=plugins&operation=config&do='.$pluginid.'&identifier=superman_zan&pmod=trend"  method="post"><input type="text" name="add_zan_count" placeholder="'.$mylang['input_add_num'].'" />&nbsp;<button class="btn" value="0" onclick="this.value='.$tid_zan[tid].'" name="add_zan_submit" type="submit">'.$mylang['add_zan'].'</button><input type="hidden" value="'.FORMHASH.'" name="formhash"></form>')
					);
				}
			}
			
			$multipage = multi(
				$tzan_count, 
				$_G['setting']['zanperpage'], 
				$page, 
				ADMINSCRIPT."?action=plugins&operation=config&do=$pluginid&identifier=superman_zan&pmod=trend"
			);
			showtablerow(
				'',		//trstryle
				array('colspan="4"'),	//tdstyle
				array(
					'<div class="cuspages right">'.$multipage.'</div>',
				)	//tdtext
			);
			showformfooter();
			
		}
	}


	public function add_zan()
	{	
		global $_G, $pluginid;
		$mylang = lang('plugin/superman_zan');
	
		$tid = $_GET['add_zan_submit'];
		$add_zan = dintval($_GET['add_zan_count']);
		$count	= DB::result_first('SELECT `count` FROM '.DB::table('plugin_superman_zan').' WHERE `tid` = '.$tid);
		
		if (empty($add_zan) || empty($tid)) {
			cpmsg($mylang['no_change'],'','error');
		} else {
			DB::update('plugin_superman_zan',array('count'=>$count+$add_zan),array('tid'=>$tid));
			cpmsg($mylang['add_success'],'action=plugins&operation=config&identifier=superman_zan&pmod=trend', 'succeed');
		}
	}
}