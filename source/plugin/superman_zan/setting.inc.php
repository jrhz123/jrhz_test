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

if (submitcheck('settingsubmit')) {
	sc_superman_zan_setting::submit();
}
sc_superman_zan_setting::show();	
exit;

////////////////////////////////////////////////////////////////////////////
class sc_superman_zan_setting
{
	public static function show()
	{
		global $_G, $pluginid;
		$mylang = lang('plugin/superman_zan');

		loadcache('superman_zan');
		$setting = $_G['cache']['superman_zan'];
		$visitor_radio = $setting['visitor_radio']?1:0;
		$zan_self_radio = $setting['zan_self_radio']?1:0;
        $zan_user_num = intval($setting['zan_user_num'])?intval($setting['zan_user_num']):13;
        $custom_title = trim($setting['custom_title'])?trim($setting['custom_title']):$mylang['zan_user'];
		//$zan_user_count = intval($setting['zan_user_count']) ? intval($setting['zan_user_count']) : 1;
		//$zan_author_count = intval($setting['zan_author_count']) ? intval($setting['zan_author_count']) : 1;
		

		showformheader("plugins&operation=manage&do=$pluginid&identifier=superman_zan&pmod=setting");
		showtableheader($mylang['param_setting']);
		
		showsetting($mylang['vistor_radio_title'], 'visitor_radio', $visitor_radio, 'radio', '',   0, $mylang['vistor_radio_comment'],   '',   '',   true);
		showsetting($mylang['zan_self_radio'], 'zan_self_radio', $zan_self_radio, 'radio', '',   0, $mylang['zan_self_radio_comment'],   '',   '',   true);
		showsetting($mylang['avatar_num'], 'zan_user_num', $zan_user_num, 'text', '',   0, $mylang['num_explain'],   '',   '',   true);
		showsetting($mylang['custom_title'], 'custom_title', $custom_title, 'text', '',   0, '',   '',   '',   true);
		//showsetting($mylang['zan_user_count'], 'zan_user_count', $zan_user_count, 'text', '',   0, $mylang['zan_user_count_comment'],   '',   '',   true);
		//showsetting($mylang['zan_author_count'], 'zan_author_count', $zan_author_count, 'text', '',   0, $mylang['zan_author_count_comment'],   '',   '',   true);

		showtablerow(
			'',	//trstryle
			array('colspan="2"'), //tdstyle
			array(
				'<button class="btn" type="submit" name="settingsubmit" onclick="this.value=1" value="0">'.$mylang['btn_save'].'</button>',
			)	//tdtext
		);
		showtablefooter();
		showformfooter();
	}

	public static function submit()
	{
		global $_G, $pluginid;
		$mylang = lang('plugin/superman_zan');

		loadcache('superman_zan');
		$setting = $_G['cache']['superman_zan'];

		$setting['visitor_radio'] = $_GET['visitor_radio']?1:0;
		$setting['zan_self_radio'] = $_GET['zan_self_radio']?1:0;
        $setting['zan_user_num'] = intval($_GET['zan_user_num'])?intval($_GET['zan_user_num']):13;
        $setting['custom_title'] = trim($_GET['custom_title']);
		//$setting['zan_user_count'] = trim($_GET['zan_user_count']);
		//$setting['zan_author_count'] = trim($_GET['zan_author_count']);

		save_syscache('superman_zan', $setting);
		cpmsg($mylang['save_succeed'], 'action=plugins&operation=config&identifier=superman_zan&pmod=setting', 'succeed');
	}
}
