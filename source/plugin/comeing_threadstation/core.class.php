<?php
/*
	[Comeing!] (C)2013-2099 Comeing.cn.
	This is NOT a freeware, use is subject to license terms
*/
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class plugin_comeing_threadstation {
}


class plugin_comeing_threadstation_forum{
	
	function viewthread_top() {
		global $_G;
		if($_G['forum_thread']){
			loadcache('plugin');
			$comeing_setting=$_G['cache']['plugin']['comeing_threadstation'];
			$comeing_setting['subs']=$comeing_setting['subs']?$comeing_setting['subs']:6;
			$comeing_setting['order']=$comeing_setting['order']?$comeing_setting['order']:1;
			
			include_once 'cert.php';
			if(md5(implode('', $comeing['cert']))!= $comeing['sn']){
				return;
			}
			$forums=dunserialize($comeing_setting['forum']);
			if(!$forums || in_array($_G['fid'],$forums)){
				$fid=$forums?$_G['fid']:'0';
				$cache=DB::fetch_first("SELECT * FROM ".DB::table('comeing_threadstation')." WHERE fid='$fid'");
				
				if(!$cache || $cache['dateline']+$comeing_setting['cachetime']<$_G['timestamp']){
					$_G['forum_colorarray'] = array('', '#EE1B2E', '#EE5023', '#996600', '#3C9D40', '#2897C5', '#2B65B7', '#8F2A90', '#EC1282');
					if($comeing_setting['message']>0){
						require_once libfile('function/post');
					}
					$sql = array();
					$sql['select'] = 'SELECT t.*';
					$sql['from'] = 'FROM '.DB::table('forum_thread').' t';
					
					$wherearr = array();
					$wherearr[] = 't.displayorder >=0';
					/*ÊÇ·ñÖ»µ÷ÓÃµ±Ç°°å¿é*/
					if($comeing_setting['form']==1){
						$fid_wherearr[] = "t.fid =$_G[fid]";
					}
					
					/*ÊÇ·ñµ÷ÓÃÌû×ÓËõÂÔÎÄ×Ö*/
					if($comeing_setting['message']>0){
						$sql['select'] .= ',p.message';
						$sql['from'] .=','.DB::table('forum_post').' p';
						$wherearr[] = 'p.tid =t.tid';
						$wherearr[] = "p.first ='1'";
					}
					
					/*ÊÇ·ñµ÷ÓÃÍ¼Æ¬*/
					if($comeing_setting['pics']>0){
						$pic_wherearr[] = 't.attachment =2';
						$pic_limit = "LIMIT 0,$comeing_setting[pics]";
					}
					
					/*ÅÅÐò*/
					if($comeing_setting['order']=='1' || !$comeing_setting['order']){
						$sql['order'] = 'ORDER BY t.dateline DESC';
					}elseif($comeing_setting['order']=='2'){
						$sql['order'] = 'ORDER BY t.lastpost DESC';
					}elseif($comeing_setting['order']=='3'){
						$sql['order'] = 'ORDER BY t.views DESC';
					}elseif($comeing_setting['order']=='4'){
						$sql['order'] = 'ORDER BY t.replies DESC';
					}
					
					$sub_limit = "LIMIT 0,$comeing_setting[subs]";
				 
					//ÆÕÍ¨Ìû
					$where = array_merge($wherearr, $fid_wherearr);
					if(!empty($where)){$sql['where'] = ' WHERE '.implode(' AND ', $where);}
					$sqlstring = $sql['select'].' '.$sql['from'].' '.$sql['where'].' '.$sql['order'].' '.$sub_limit;
					
					$query = DB::query($sqlstring);
					while($value = DB::fetch($query)) {
						$value['dateline']=dgmdate($value['dateline'],'m-d');
						$value['lastpost']=dgmdate($value['lastpost'],'m-d');
						if($comeing_setting['message']>0){
							$value['message']=messagecutstr($value['message'], $comeing_setting['message'], '...');
						}
						if($value['highlight']) {
							$string = sprintf('%02d', $value['highlight']);
							$stylestr = sprintf('%03b', $string[0]);
					
							$value['highlight'] = ' style="';
							$value['highlight'] .= $stylestr[0] ? 'font-weight: bold;' : '';
							$value['highlight'] .= $stylestr[1] ? 'font-style: italic;' : '';
							$value['highlight'] .= $stylestr[2] ? 'text-decoration: underline;' : '';
							$value['highlight'] .= $string[1] ? 'color: '.$_G['forum_colorarray'][$string[1]].';' : '';
							if($value['bgcolor']) {
								$value['highlight'] .= "background-color: $thread[bgcolor];";
							}
							$value['highlight'] .= '"';
						} else {
							$value['highlight'] = '';
						}
						$comeing_threadstation['subs'][$value['tid']]=$value;
						$subtids[]=$value['tid'];
					}
					
					/*Í¼Æ¬Ìù*/
					if($comeing_setting['pics']>0){
						$where = array_merge($wherearr, $fid_wherearr,$pic_wherearr);
						if(!empty($where)){$sql['where'] = ' WHERE '.implode(' AND ', $where);}
						$sqlstring = $sql['select'].' '.$sql['from'].' '.$sql['where'].' '.$sql['order'].' '.$pic_limit;
					 
						$query = DB::query($sqlstring);
						while($value = DB::fetch($query)) {
							$table='forum_attachment_'.substr($value['tid'], -1);
							$value['aid'] = DB::result_first("SELECT aid FROM ".DB::table($table)." WHERE tid='$value[tid]' AND isimage!=0 ORDER BY `dateline` ASC");
							$value['pic']=getforumimg($value['aid'],0,$comeing_setting['width'],$comeing_setting['height']);
							$value['dateline']=dgmdate($value['dateline'],'m-d');
							$value['lastpost']=dgmdate($value['lastpost'],'m-d');
	
							if($comeing_setting['message']>0){
								$value['message']=messagecutstr($value['message'], $comeing_setting['message'], '...');
							}
							$comeing_threadstation['pics'][$value['tid']]=$value;
							$pictids[]=$value['tid'];
						}
					}
					
					/*²¹ÆëÌû×Ó*/
					if(count($subtids)<$comeing_setting['subs'] && $comeing_setting['form']==1 && $comeing_setting['add']!=1){
						if($subtids){
							$subtid=implode(',',$subtids);
						}
						$num=$comeing_setting['subs']-count($subtids);
						$subnot_in = " AND t.tid NOT IN($subtid) ";
						$sub_limit = "LIMIT 0,$num";
						if(!empty($wherearr)){$sql['where'] = ' WHERE '.implode(' AND ', $wherearr);}
						
						/*ÅÅÐò*/
						if($comeing_setting['add']=='2'){
							$sql['order'] = 'ORDER BY t.dateline DESC';
						}elseif($comeing_setting['order']=='3'){
							$sql['order'] = 'ORDER BY t.lastpost DESC';
						}elseif($comeing_setting['order']=='4'){
							$sql['order'] = 'ORDER BY t.replies DESC';
						}elseif($comeing_setting['order']=='5'){
							$sql['order'] = 'ORDER BY t.views DESC';
						}elseif($comeing_setting['order']=='6'){
							$sql['order'] = 'ORDER BY t.displayorder DESC';
						}
						$sqlstring = $sql['select'].' '.$sql['from'].' '.$sql['where'].' '.$subnot_in.' '.$sql['order'].' '.$sub_limit;
	
						$query = DB::query($sqlstring);
						while($value = DB::fetch($query)) {
							$value['dateline']=dgmdate($value['dateline'],'m-d');
							$value['lastpost']=dgmdate($value['lastpost'],'m-d');
							if($comeing_setting['message']>0){
								$value['message']=messagecutstr($value['message'], $comeing_setting['message'], '...');
							}
							if($value['highlight']) {
								$string = sprintf('%02d', $value['highlight']);
								$stylestr = sprintf('%03b', $string[0]);
						
								$value['highlight'] = ' style="';
								$value['highlight'] .= $stylestr[0] ? 'font-weight: bold;' : '';
								$value['highlight'] .= $stylestr[1] ? 'font-style: italic;' : '';
								$value['highlight'] .= $stylestr[2] ? 'text-decoration: underline;' : '';
								$value['highlight'] .= $string[1] ? 'color: '.$_G['forum_colorarray'][$string[1]].';' : '';
								if($value['bgcolor']) {
									$value['highlight'] .= "background-color: $thread[bgcolor];";
								}
								$value['highlight'] .= '"';
							} else {
								$value['highlight'] = '';
							}
							$comeing_threadstation['extendsubs'][$value['tid']]=$value;
						}
					}
					/*Í¼Æ¬²¹Æë*/
					if(count($pictids)<$comeing_setting['pics'] && $comeing_setting['form']==1 && $comeing_setting['add']!=1){
						if($pictids){
							$pictid=implode(',',$pictids);
						}
						$num=$comeing_setting['pics']-count($pictids);
						if($pictid){
							$picnot_in = " AND t.tid NOT IN($pictid) ";
						}
						
						$pic_limit = "LIMIT 0,$num";
						$where = array_merge($wherearr,$pic_wherearr);
						
						if(!empty($where)){$sql['where'] = ' WHERE '.implode(' AND ', $where);}
						
						/*ÅÅÐò*/
						if($comeing_setting['add']=='2'){
							$sql['order'] = 'ORDER BY t.dateline DESC';
						}elseif($comeing_setting['order']=='3'){
							$sql['order'] = 'ORDER BY t.lastpost DESC';
						}elseif($comeing_setting['order']=='4'){
							$sql['order'] = 'ORDER BY t.replies DESC';
						}elseif($comeing_setting['order']=='5'){
							$sql['order'] = 'ORDER BY t.views DESC';
						}elseif($comeing_setting['order']=='6'){
							$sql['order'] = 'ORDER BY t.displayorder DESC';
						}
						
	
						$sqlstring = $sql['select'].' '.$sql['from'].' '.$sql['where'].' '.$picnot_in.' '.$sql['order'].' '.$pic_limit;
						
						$query = DB::query($sqlstring);
						while($value = DB::fetch($query)) {
							$table='forum_attachment_'.substr($value['tid'], -1);
							$value['aid'] = DB::result_first("SELECT aid FROM ".DB::table($table)." WHERE tid='$value[tid]' AND isimage!=0 ORDER BY `dateline` ASC");
							$value['pic']=getforumimg($value['aid'],0,$comeing_setting['width'],$comeing_setting['height']);
							$value['dateline']=dgmdate($value['dateline'],'m-d');
							$value['lastpost']=dgmdate($value['lastpost'],'m-d');
							if($comeing_setting['message']>0){
								$value['message']=messagecutstr($value['message'], $comeing_setting['message'], '...');
							}
							$comeing_threadstation['extendpics'][$value['tid']]=$value;
						}
					}
					$cachevalue=addslashes(serialize($comeing_threadstation));
					if($cache){
						DB::query("UPDATE ".DB::table('comeing_threadstation')." SET `cache` = '$cachevalue',`dateline`='$_G[timestamp]' WHERE `fid` = '$fid'");
					}else{
						DB::query("INSERT INTO ".DB::table('comeing_threadstation')." (`fid`,`cache`,`dateline`) VALUES ('$fid','$cachevalue','$_G[timestamp]')");
					}
				}else{
					$comeing_threadstation=dunserialize($cache['cache']);
				}
				$width=30+($comeing_setting['width']*3);
				$picwidth=$width+70;
				include template('comeing_threadstation:view');
				return $return;
			}			
		}

	}
}

?>