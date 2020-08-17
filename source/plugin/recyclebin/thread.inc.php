<?php

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

if ($_GET['formhash']==formhash() && $_POST['threadlist']) {
	require_once libfile('function/post');
	$threadlist = $_POST['threadlist'];
	$num = undeletethreads($threadlist);
	cpmsg('已成功恢复 <font color="red">'.$num.'</font> 个主题。','action=plugins&operation=config&identifier=recyclebin&pmod=thread&formhash='.FORMHASH,'succeed');
} else {
	require_once libfile('function/discuzcode');
	$inforum = $_GET['inforum'];
	$authors_string = $_GET['authors'];
	$keywords = $_GET['keywords'];
	$admins_string = $_GET['admins'];
	$pstarttime = $_GET['pstarttime'];
	$pendtime = $_GET['pendtime'];
	$mstarttime = $_GET['mstarttime'];
	$mendtime = $_GET['mendtime'];
	$isgroup = $fid = 0;
	if($inforum == 'groupthread') {
		$isgroup = 1;
	} else {
		$fid = $inforum ? $inforum : 0;
	}
	$authors = $authors_string != ''		? explode(' ', $authors_string) : '';
	$admins = $admins_string != ''			? explode(' ', $admins_string) : '';
	$pstarttime = $pstarttime != ''	? strtotime($pstarttime) : '';
	$pendtime = $pendtime != ''		? strtotime($pendtime) : '';
	$mstarttime = $mstarttime != ''	? strtotime($mstarttime) : '';
	$mendtime = $mendtime != ''		? strtotime($mendtime) : '';

	$page = max(1,intval($_GET['page']));
	$perpage = max(20,intval($_GET['perpage']));
	$offset = ($page-1) * $perpage;

	$table_thread = DB::table('forum_thread');
    $table_forum = DB::table('forum_forum');

	$where_sql = where_sql($fid, $isgroup, $authors, $admins, $pstarttime, $pendtime, $mstarttime, $mendtime, $keywords);
	$count = DB::result_first("SELECT COUNT(*) FROM `$table_thread` t $where_sql[0]");
    $result = DB::fetch_all("SELECT t.*, f.`name` AS `forumname` FROM `$table_thread` t LEFT JOIN `$table_forum` f ON t.`fid` = f.`fid` $where_sql[0] ORDER BY t.`dateline` DESC LIMIT $offset, $perpage");

    echo "<style>
        .thread_subject {
            cursor: pointer;
        }
        .thread_message {
            overflow: auto;
            overflow-x: hidden;
            width:700px;
            word-break: break-all;
            display: none;
        }
    </style>";

	showformheader('plugins&operation=config&identifier=recyclebin&pmod=thread&formhash='.FORMHASH);
	showtableheader('帖子', 'fixpadding');
	showsubtitle(array('', 'thread', 'recyclebin_list_thread', 'recyclebin_list_author', 'recyclebin_list_status', 'recyclebin_list_lastpost', 'recyclebin_list_operation', 'reason'));
	foreach($result as $key => $thread){
		$threadmod = C::t('forum_threadmod')->fetch_all_by_tid($thread['tid']);
		$tid = $thread['tid'];
		$table_post = DB::table('forum_post');
		$post = DB::fetch_first("SELECT * FROM `$table_post` WHERE `tid` = $tid AND `first` = 1 LIMIT 1");
		$post['message'] = discuzcode($post['message'], $post['smileyoff'], $post['bbcodeoff'], sprintf('%00b', $post['htmlon']), $forumlist[$post['fid']]['allowsmilies'], $forumlist[$post['fid']]['allowbbcode'], $forumlist[$post['fid']]['allowimgcode'], $forumlist[$post['fid']]['allowhtml']);
		showtablerow('', array('class="td25"', '', '', 'class="td28"', 'class="td28"'), array(
			"<input type='checkbox' class='checkbox' name='threadlist[]' value='$thread[tid]'>",
			'<h3 class="thread_subject" onClick="toggleMessage(\''.$thread['tid'].'\')">'.$thread['subject'].'</h3><div class="thread_message" id="thread_message_'.$thread['tid'].'">'.$post['message'].'</div>',
			'<a href="forum.php?mod=forumdisplay&fid='.$thread['fid'].'" target="_blank">'.$thread['forumname'].'</a>',
			'<a href="home.php?mod=space&uid='.$thread['authorid'].'" target="_blank">'.$thread['author'].'</a><br /><em style="font-size:9px;color:#999999;">'.dgmdate($thread['dateline'], 'd').'</em>',
			$thread['replies'].' / '.$thread['views'],
			$thread['lastposter'].'<br /><em style="font-size:9px;color:#999999;">'.dgmdate($thread['lastpost'], 'd').'</em>',
			$threadmod[0]['username'] ? $threadmod[0]['username'].'<br /><em style="font-size:9px;color:#999999;">'.dgmdate($threadmod[0]['dateline'], 'd').'</em>' : '',
			$threadmod[0]['reason']
		));
	}
	showsubmit('undelete', 'undelete');
	showtablefooter();
	showformfooter();
	$fromurl = "action=plugins&operation=config&identifier=recyclebin&pmod=thread&page=$page&perpage=$perpage&formhash=".FORMHASH;
	$multi = multi($count, $perpage, $page, ADMINSCRIPT.'?'.$fromurl);
	$multi = preg_replace("/href=\"".ADMINSCRIPT."\?$fromurl&amp;page=(\d+)\"/", "href=\"javascript:page(\\1)\"", $multi);
	//$multi = str_replace("window.location='".ADMINSCRIPT."?action=threads&amp;page='+this.value", "page(this.value)", $multi);
	echo $multi;

		require_once libfile('function/forumlist');

		$forumselect = '<select name="inforum"><option value="">&nbsp;&nbsp;> '.$lang['select'].'</option>'.
			'<option value="">&nbsp;</option><option value="groupthread">'.$lang['group_thread'].'</option>'.forumselect(FALSE, 0, 0, TRUE).'</select>';

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

function toggleMessage(id) {
    var tmi = $('thread_message_'+id);
    if (tmi.style.display == 'block') {
        tmi.style.display = 'none';
    } else {
        tmi.style.display = 'block';
    }
}
</script>
EOT;
		showformheader('plugins&operation=config&identifier=recyclebin&pmod=thread&formhash='.FORMHASH, '', 'rbsearchform');
		showhiddenfields(array('page' => $page));
		showtableheader('recyclebin_search');
		showsetting('recyclebin_search_forum', '', '', $forumselect);
		showsetting('recyclebin_search_author', 'authors', $authors_string, 'text');
		showsetting('recyclebin_search_keyword', 'keywords', $keywords, 'text');
		showsetting('recyclebin_search_admin', 'admins', $admins_string, 'text');
		showsetting('recyclebin_search_post_time', array('pstarttime', 'pendtime'), array($pstarttime, $pendtime), 'daterange');
		showsetting('recyclebin_search_mod_time', array('mstarttime', 'mendtime'), array($mstarttime, $mendtime), 'daterange');
		showsubmit('searchsubmit');
		showtablefooter();
		showformfooter();
}

function where_sql($fid, $isgroup, $authors, $admins, $pstarttime, $pendtime, $mstarttime, $mendtime, $keywords) {
		$parameter = array();
		$wherearr = array('t.displayorder=-1');
		if($fid) {
			$fid = dintval($fid, true);
			$parameter[] = $fid;
			$wherearr[] = is_array($fid) ? "t.fid IN ('".implode("','", $fid)."')" : "t.fid = $fid";
		}
		if($isgroup) {
			$wherearr[] = 't.isgroup=1';
		}
		if(!empty($authors)) {
			$parameter[] = $authors;
			$wherearr[] = is_array($authors) && $authors ? "t.author IN ('".implode("','", $authors)."')" : "t.author = $authors";
		}

		if($pstarttime) {
			$parameter[] = $pstarttime;
			$wherearr[] = "t.dateline >= $pstarttime";
		}
		if($pendtime) {
			$parameter[] = $pendtime;
			$wherearr[] = "t.dateline < $pendtime";
		}
		if($keywords) {
			$keysql = array();
			foreach(explode(',', str_replace(' ', '', $keywords)) as $keyword) {
				$parameter[] = '%'.$keyword.'%';
				$keysql[] = "t.subject LIKE '%$keyword%'";
			}
			if($keysql) {
				$wherearr[] = '('.implode(' OR ', $keysql).')';
			}
		}
		/*if(!empty($username)) {
			$parameter[] = $username;
			$wherearr[] = is_array($username) && $username ? 'tm.username IN(%n)' : 'tm.username=%s';
		}
		if($mstarttime) {
			$parameter[] = $mstarttime;
			$wherearr[] = 'tm.dateline>=%d';
		}
		if($mendtime) {
			$parameter[] = $mendtime;
			$wherearr[] = 'tm.dateline<%d';
		}*/
		$wheresql = !empty($wherearr) && is_array($wherearr) ? ' WHERE '.implode(' AND ', $wherearr) : '';
		return array($wheresql, $parameter);
}

?>
