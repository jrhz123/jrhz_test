<?php
function mymulti($num, $perpage, $curpage, $mpurl, $maxpages = 0, $page = 10, $autogoto = FALSE, $simple = FALSE, $jsfunc = FALSE, $showid) {
		global $_G;
		$ajaxtarget = !empty($_GET['ajaxtarget']) ? " ajaxtarget=\"".dhtmlspecialchars($_GET['ajaxtarget'])."\" " : '';

		$a_name = '';
		if(strpos($mpurl, '#') !== FALSE) {
			$a_strs = explode('#', $mpurl);
			$mpurl = $a_strs[0];
			$a_name = '#'.$a_strs[1];
		}
		if($jsfunc !== FALSE) {
			$mpurl = 'javascript:'.$mpurl;
			$a_name = $jsfunc;
			$pagevar = '';
		} else {
			$pagevar = 'page=';
		}

		if(defined('IN_ADMINCP')) {
			$shownum = $showkbd = TRUE;
			$showpagejump = FALSE;
			$lang['prev'] = '&lsaquo;&lsaquo;';
			$lang['next'] = '&rsaquo;&rsaquo;';
		} else {
			$shownum = $showkbd = FALSE;
			$showpagejump = TRUE;
			if(defined('IN_MOBILE') && !defined('TPL_DEFAULT')) {
				$lang['prev'] = lang('core', 'prevpage');
				$lang['next'] = lang('core', 'nextpage');
			} else {
				$lang['prev'] = '&nbsp;&nbsp;';
				$lang['next'] = lang('core', 'nextpage');
			}
			$lang['pageunit'] = lang('core', 'pageunit');
			$lang['total'] = lang('core', 'total');
			$lang['pagejumptip'] = lang('core', 'pagejumptip');
		}
		if(defined('IN_MOBILE') && !defined('TPL_DEFAULT')) {
			$dot = '..';
			$page = intval($page) < 10 && intval($page) > 0 ? $page : 4 ;
		} else {
			$dot = '...';
		}
		$multipage = '';
		if($jsfunc === FALSE) {
			$mpurl .= strpos($mpurl, '?') !== FALSE ? '&amp;' : '?';
		}

		$realpages = 1;
		$_G['page_next'] = 0;
		$page -= strlen($curpage) - 1;
		if($page <= 0) {
			$page = 1;
		}
		if($num > $perpage) {

			$offset = floor($page * 0.5);

			$realpages = @ceil($num / $perpage);
			$curpage = $curpage > $realpages ? $realpages : $curpage;
			$pages = $maxpages && $maxpages < $realpages ? $maxpages : $realpages;

			if($page > $pages) {
				$from = 1;
				$to = $pages;
			} else {
				$from = $curpage - $offset;
				$to = $from + $page - 1;
				if($from < 1) {
					$to = $curpage + 1 - $from;
					$from = 1;
					if($to - $from < $page) {
						$to = $page;
					}
				} elseif($to > $pages) {
					$from = $pages - $page + 1;
					$to = $pages;
				}
			}
			$_G['page_next'] = $to;
			$multipage = ($curpage - $offset > 1 && $pages > $page ? '<a href="'.$mpurl.$pagevar.'1'.$a_name.'" class="first"'.$ajaxtarget.'>1 '.$dot.'</a>' : '').
			($curpage > 1 && !$simple ? '<a href="'.$mpurl.$pagevar.($curpage - 1).$a_name.'" class="prev"'.$ajaxtarget.'>'.$lang['prev'].'</a>' : '');
			for($i = $from; $i <= $to; $i++) {
				$multipage .= $i == $curpage ? '<strong>'.$i.'</strong>' :
				'<a href="javascript:" onclick="ajaxget(\''.$mpurl.$pagevar.$i.($ajaxtarget && $i == $pages && $autogoto ? '#' : $a_name).'\',\''.$showid.'\');">'.$i.'</a>';
			}
			$multipage .= ($to < $pages ? '<a href="javascript:" onclick="ajaxget(\''.$mpurl.$pagevar.$pages.$a_name.'\',\''.$showid.'\');" class="last"'.$ajaxtarget.'>'.$dot.' '.$realpages.'</a>' : '').
			($showpagejump && !$simple && !$ajaxtarget ? '<label><input type="text" name="custompage" class="px" size="2" title="'.$lang['pagejumptip'].'" value="'.$curpage.'" onkeydown="if(event.keyCode==13) {window.location=\''.$mpurl.$pagevar.'\'+this.value; doane(event);}" /><span title="'.$lang['total'].' '.$pages.' '.$lang['pageunit'].'"> / '.$pages.' '.$lang['pageunit'].'</span></label>' : '').
			($curpage < $pages && !$simple ? '<a href="'.$mpurl.$pagevar.($curpage + 1).$a_name.'" class="nxt"'.$ajaxtarget.'>'.$lang['next'].'</a>' : '').
			($showkbd && !$simple && $pages > $page && !$ajaxtarget ? '<kbd><input type="text" name="custompage" size="3" onkeydown="if(event.keyCode==13) {window.location=\''.$mpurl.$pagevar.'\'+this.value; doane(event);}" /></kbd>' : '');

			$multipage = $multipage ? '<div class="jlpg">'.($shownum && !$simple ? '<em>&nbsp;'.$num.'&nbsp;</em>' : '').$multipage.'</div>' : '';
		}
		$maxpage = $realpages;
		return $multipage;
}
?>