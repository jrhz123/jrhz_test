<?php
$Cis['style']['need']='0';
$query = DB::query("SELECT * FROM ".DB::table('common_advertisement')." WHERE available ='1' AND `targets` LIKE '%$_G[basescript]%' AND type ='subnavbanner' OR type ='headerbanner' ORDER BY `advid` DESC");
while($value = DB::fetch($query)) {
	$value['parameters']=dunserialize($value['parameters']);
	if($value['parameters']['style']=='image'){
		if($value['starttime'] && $value['starttime']<$_G['timestamp']){
			$comeing_adbanners[]=$value['parameters'];
		}elseif($value['endtime'] && $value['endtime']>$_G['timestamp']){
			$comeing_adbanners[]=$value['parameters'];
		}else{
			$comeing_adbanners[]=$value['parameters'];
		}		
	}
}
?>