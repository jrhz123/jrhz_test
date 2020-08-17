<?php

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

if ($_GET['formhash']==formhash() && $_POST['postlist']) {
	require_once libfile('function/post');
	$postlist = $_POST['postlist'];
	$num = recyclebinpostundelete($postlist);
	cpmsg('已成功恢复 <font color="red">'.$num.'</font> 条回复。','action=plugins&operation=config&identifier=recyclebin&pmod=post&formhash='.FORMHASH,'succeed');
} else {
	require_once libfile('function/discuzcode');

	$inforum = $_GET['inforum'];
	$authors = $_GET['authors'];
	$keywords = $_GET['keywords'];
	$pstarttime = $_GET['pstarttime'];
	$pendtime = $_GET['pendtime'];

	$isgroup = $fid = 0;
	if($inforum == 'groupthread') {
		$isgroup = 1;
	} else {
		$fid = $inforum ? $inforum : 0;
	}
	$author = $authors != ''		? explode(' ', $authors) : '';
	$pstarttime = $pstarttime != ''	? strtotime($pstarttime) : '';
	$pendtime = $pendtime != ''		? strtotime($pendtime) : '';

	$page = max(1,intval($_GET['page']));
	$perpage = max(20,intval($_GET['perpage']));
	$offset = ($page-1) * $perpage;
	$tablename = DB::table('forum_post');
	$where_sql = where_sql($fid, $isgroup, $author, $admins, $pstarttime, $pendtime, $keywords);
	$count = DB::result_first("SELECT COUNT(*) FROM `$tablename` p $where_sql[0]");
	$result = DB::fetch_all("SELECT * FROM `$tablename` p $where_sql[0] ORDER BY `dateline` DESC LIMIT $offset, $perpage");

	showformheader("plugins&operation=config&identifier=recyclebin&pmod=post&formhash=".FORMHASH);
	showtableheader('回复', 'fixpadding');
	foreach($result as $key => $post) {
		$thread = C::t('forum_thread')->fetch_all_by_tid($post['tid']);
		$forum = C::t('forum_forum')->fetch_all_by_fid($post['fid']);
		$post['message'] = discuzcode($post['message'], $post['smileyoff'], $post['bbcodeoff'], sprintf('%00b', $post['htmlon']), $forumlist[$post['fid']]['allowsmilies'], $forumlist[$post['fid']]['allowbbcode'], $forumlist[$post['fid']]['allowimgcode'], $forumlist[$post['fid']]['allowhtml']);
		$post['dateline'] = dgmdate($post['dateline']);
		if($post['attachment']) {
			require_once libfile('function/attachment');
			foreach(C::t('forum_attachment_n')->fetch_all_by_id('tid:'.$post['tid'], 'pid', $post['pid']) as $attach) {
				$_G['setting']['attachurl'] = $attach['remote'] ? $_G['setting']['ftp']['attachurl'] : $_G['setting']['attachurl'];
				$attach['url'] = $attach['isimage']
					? " $attach[filename] (".sizecount($attach['filesize']).")<br /><br /><img src='".$_G['setting']['attachurl']."forum/$attach[attachment]' onload='if(this.width > 100) {this.resized=true; this.width=100;}'>"
					 : "<a href='".$_G['setting']['attachurl']."forum/$attach[attachment]' target='_blank'>$attach[filename]</a> (".sizecount($attach['filesize']).")";
				$post['message'] .= "<br /><br />$lang[attachment]: ".attachtype(fileext($attach['filename'])."\t").$attach['url'];
			}
		}

		showtablerow("id='mod_$post[pid]_row1'", array('rowspan="3" class="rowform threadopt" style="width:50px;"', 'class="threadtitle"'), array(
			"<input type='checkbox' class='checkbox' name='postlist[]' value='$post[pid]'>",
			"<h3><a href='forum.php?mod=forumdisplay&fid=$post[fid]' target='_blank'>".$forum[$post['fid']]['name']."</a> &raquo; <a href='forum.php?mod=viewthread&tid=$post[tid]' target='_blank'>".$thread[$post['tid']]['subject']."</a>".($post['subject'] ? ' &raquo; '.$post['subject'] : '')."</h3><p><span class='bold'>$lang[author]:</span> <a href='home.php?mod=space&uid=$post[authorid]' target='_blank'>$post[author]</a> &nbsp;&nbsp; <span class='bold'>$lang[time]:</span> $post[dateline] &nbsp;&nbsp; IP: $post[useip]</p>"
		));
		showtablerow("id='mod_$post[pid]_row2'", 'colspan="2" style="padding: 10px; line-height: 180%;"', '<div style="overflow: auto; overflow-x: hidden; max-height:120px; height:auto !important; height:120px; word-break: break-all;">'.$post['message'].'</div>');
		showtablerow("id='mod_$post[pid]_row3'", 'class="threadopt threadtitle" colspan="2"', "$lang[isanonymous]: ".($post['anonymous'] ? $lang['yes'] : $lang['no'])." &nbsp;&nbsp; $lang[ishtmlon]: ".($post['htmlon'] ? $lang['yes'] : $lang['no']));
	}
	showsubmit('undelete', 'undelete');
	showtablefooter();
	showformfooter();

	$fromurl = "action=plugins&operation=config&identifier=recyclebin&pmod=post&page=$page&perpage=$perpage&formhash=".FORMHASH;
	$multi = multi($count, $perpage, $page, ADMINSCRIPT.'?'.$fromurl);
	$multi = preg_replace("/href=\"".ADMINSCRIPT."\?$fromurl&amp;page=(\d+)\"/", "href=\"javascript:page(\\1)\"", $multi);
	//$multi = str_replace("window.location='".ADMINSCRIPT."?action=post&amp;page='+this.value", "page(this.value)", $multi);
	echo $multi;


		require_once libfile('function/forumlist');

		$forumselect = '<select name="inforum"><option value="">&nbsp;&nbsp;> '.$lang['allthread'].'</option>'.
			'<option value="">&nbsp;</option>'.forumselect(FALSE, 0, 0, TRUE).'</select>';

		if($inforum) {
			$forumselect = preg_replace("/(\<option value=\"$inforum\")(\>)/", "\\1 selected=\"selected\" \\2", $forumselect);
		}

		echo <<<EOT
<script type="text/javascript" src="static/js/calendar.js"></script>
<script type="text/JavaScript">
function page(number) {
	$('rbsearchform').page.value=number;
	$('rbsearchform').searchsubmit.click();
}
</script>
EOT;
		showformheader('plugins&operation=config&identifier=recyclebin&pmod=post&formhash='.FORMHASH, '', 'rbsearchform');
		showhiddenfields(array('page' => $page));
		showtableheader('recyclebinpost_clean');
		showsetting('recyclebinpost_search_forum', '', '', $forumselect);
		showsetting('recyclebinpost_search_author', 'authors', $authors, 'text');
		showsetting('recyclebinpost_search_keyword', 'keywords', $keywords, 'text');
		showsetting('recyclebin_search_post_time', array('pstarttime', 'pendtime'), array($pstarttime, $pendtime), 'daterange');
		showsetting('postsplit', '', '', getposttableselect());
		showsubmit('searchsubmit');
		showtablefooter();
		showformfooter();
}

function where_sql($fid, $isgroup, $author, $admins, $pstarttime, $pendtime, $keywords) {
		$parameter = array();
		$wherearr = array('p.invisible=-5');
		if($fid) {
			$fid = dintval($fid, true);
			$parameter[] = $fid;
			$wherearr[] = is_array($fid) ? "p.fid IN ('".implode("','", $fid)."')" : "p.fid = $fid";
		}
		if($isgroup) {
			$wherearr[] = 'p.isgroup=1';
		}
		if(!empty($authors)) {
			$parameter[] = $authors;
			$wherearr[] = is_array($authors) && $authors ? "p.author IN ('".implode("','", $authors)."')" : "p.author = $authors";
		}

		if($pstarttime) {
			$parameter[] = $pstarttime;
			$wherearr[] = "p.dateline >= $pstarttime";
		}
		if($pendtime) {
			$parameter[] = $pendtime;
			$wherearr[] = "p.dateline < $pendtime";
		}
		if($keywords) {
			$keysql = array();
			foreach(explode(',', str_replace(' ', '', $keywords)) as $keyword) {
				$parameter[] = '%'.$keyword.'%';
				$keysql[] = "p.subject LIKE '%$keyword%'";
			}
			if($keysql) {
				$wherearr[] = '('.implode(' OR ', $keysql).')';
			}
		}
		$wheresql = !empty($wherearr) && is_array($wherearr) ? ' WHERE '.implode(' AND ', $wherearr) : '';
		return array($wheresql, $parameter);
}


?>
