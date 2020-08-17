<?php

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}



$siteid = $_G['wechat']['setting']['wsq_siteid'];
require_once libfile('function/post');

if($_GET['pc']){
	$sqlstr = " AND postclass=".intval($_GET['pc']);
}

$tmp = explode("\n",$_G['cache']['plugin']['xj_event']['event_offline_class']);
$offlineclass = array();
foreach($tmp as $key=>$value){
	$eventclass = explode("|",$value);
	$offlineclass[$eventclass[0]] = $eventclass[1];
}
$tmp = explode("\n",$_G['cache']['plugin']['xj_event']['event_online_class']);
$onlineclass = array();
foreach($tmp as $key=>$value){
	$eventclass = explode("|",$value);
	$onlineclass[$eventclass[0]] = $eventclass[1];
}


$perpage = 10; //每页数
$listcount = DB::result_first("SELECT count(*) FROM ".DB::table('xj_event')." A,".DB::table('forum_thread')." B WHERE A.tid=B.tid ".$sqlstr." ORDER BY A.eid");
$page = $_GET['page']?$_GET['page']:1;
//if(@ceil($listcount/$perpage) < $page) {
//	$page = 1;
//}
$start_limit = ($page - 1) * $perpage;


$query = DB::query("SELECT * FROM ".DB::table('xj_event')." A,".DB::table('forum_thread')." B WHERE A.tid=B.tid ".$sqlstr." ORDER BY A.eid DESC LIMIT $start_limit,$perpage");
$toplist = array();
while($value = DB::fetch($query)){
	$value['subject'] = cutstr($value['subject'],50);
	//获取报名人数
	$value['zynumber'] = DB::result_first("SELECT count(*) FROM ".DB::table('xj_eventthread')." WHERE eid=".$value['eid']);
	$value['applynumber'] = DB::result_first("SELECT SUM(applynumber) FROM ".DB::table('xj_eventapply')." WHERE tid=".$value['tid']." and verify=1");
	$value['applynumber'] = $value['applynumber']?$value['applynumber']:0;
	$value['activityaid_url'] = $value['activityaid']?getforumimg($value['activityaid'],0,80,80):'static/image/common/nophoto.gif';

	$value['starttime'] = date('Y-m-d',$value['starttime']);
	$value['message'] = DB::result_first("SELECT message FROM ".DB::table('forum_post')." WHERE tid=".$value['tid']);
	$value['message'] = messagecutstr($value['message'],50);
	$value['setting'] = unserialize($value['setting']);
	$toplist[] = $value;
}

if($_GET['output']=='json'){
	foreach($toplist as $key=>$value){
		if($value['setting']['eventaa']){
			$toplist[$key]['use_cost_str'] = 'AA';
		}else{
			if($value['use_cost']>0){
				$toplist[$key]['use_cost_str'] = $value['use_cost'].lang('plugin/xj_event', 'yuan');
			}else{
				$toplist[$key]['use_cost_str'] = lang('plugin/xj_event', 'mianfei');
			}
		}
		if($value['postclass']==1){
			$toplist[$key]['postclass'] = lang('plugin/xj_event', 'xianxia');
		}else{
			$toplist[$key]['postclass'] = lang('plugin/xj_event', 'xianshan');
		}
		if($value['postclass']==1){
			$toplist[$key]['zclass'] = $offlineclass[$value['offlineclass']];
		}else{
			$toplist[$key]['zclass'] = $onlineclass[$value['onlineclass']];
		}
		if($_G['charset']=='gbk'){
			$toplist[$key]['subject'] = iconv("GBK", "UTF-8", $value['subject']);
			$toplist[$key]['zclass'] = iconv("GBK", "UTF-8", $toplist[$key]['zclass']);
			$toplist[$key]['postclass'] = iconv("GBK", "UTF-8", $toplist[$key]['postclass']);
			$toplist[$key]['use_cost_str'] = iconv("GBK", "UTF-8", $toplist[$key]['use_cost_str']);
		}
	}
	exit(json_encode($toplist));
}


?>
<script type="text/javascript" src="http://wsq.discuz.com/cdn/discuz/js/openjs.js"></script>
<script>
        var menu = new Array();
		menu.push({name:"<?php echo lang('plugin/xj_event', 'xxhd'); ?>", pluginid:'xj_event:wsq_event_center', param:"pc=1"});
        menu.push({name:"<?php echo lang('plugin/xj_event', 'xshd'); ?>", pluginid:'xj_event:wsq_event_center', param:"pc=2"});
        WSQ.initBtmBar(menu);
        WSQ.showBtmBar();
        WSQ.initPlugin({name:'<?php echo lang('plugin/xj_event', 'huodzx'); ?>'});

        var initWx = {
            'img': '<?php echo $_G['siteurl']; ?>/static/image/common/logo.png',
            'desc': '<?php echo $_G['bbsname'].lang('plugin/xj_event', 'huodzx'); ?>',
            'title': '<?php echo lang('plugin/xj_event', 'huodzx'); ?>',
	    'pluginid':'xj_event:wsq_event_center',
            'param': 'a=1&b=2'
        };
        WSQ.initShareWx(initWx);
    </script>
<script type="text/javascript" src="source/plugin/xj_event/js/jquery.js"></script>
<script type="text/javascript">
var i = 2;
$(function(){
	$("#btnload").click(function(){
		$.getJSON("plugin.php?id=xj_event:wsq_event_center&pc=<?php echo $_GET['pc']; ?>&output=json",{page:i},function(json){
			if(json){
				var str = "";
				$.each(json,function(index,array){
					var str = '<div style="background-color:#FFFFFF;-moz-border-radius: 8px;-webkit-border-radius: 8px;border-radius:8px; padding:10px; margin-bottom:8px; border-bottom:2px #ddd solid;" onClick="window.open(\'http://wsq.discuz.com/?c=index&a=viewthread&f=wx&tid='+array['tid']+'&siteid=<?php echo $siteid; ?>&_bpage=1\',\'_parent\');"><div style="float:left; width:80px; height:80px; background-color:#ddd;"><img src="'+array['activityaid_url']+'" style="width:80px;height:80px;" border="0"></div><div style="margin-left:80px; padding:2px 10px; height:80px; line-height:26px; font-size:14px;">'+array['subject']+'</div><div style="clear:both;"></div><div style="height:30px;"><div style="float:left; width:120px; padding-top:12px;"><span style=" padding:3px 8px; color:#fff; background-color:#799edc; -moz-border-radius: 3px; -webkit-border-radius: 3px; border-radius:3px; font-size:12px; ">'+array['postclass']+'</span><span style=" padding:3px 8px; color:#fff; background-color:#799edc; -moz-border-radius: 3px; -webkit-border-radius: 3px; border-radius:3px; font-size:12px; margin-left:5px; ">'+array['zclass']+'</span></div><div style="margin-left:120px; padding:2px 10px; height:30px; line-height:30px; font-size:12px; text-align:right;"><?php echo lang('plugin/xj_event', 'baomingrs'); ?>: <span style="color:#FF3300; font-weight:bold;">'+array['applynumber']+'</span> <?php echo lang('plugin/xj_event', 'ren'); ?>  <?php echo lang('plugin/xj_event', 'feiyong'); ?>:<span style="color:#FF3300; font-weight:bold;">'+array['use_cost_str']+'</span></div><div style="clear:both;"></div></div></div>';
					$("#mycontainer").append(str);
				});
				i++;
			}else{
				return false;
			}
		});
	});
});
/*
$(function(){
	var winH = $(window).height(); //页面可视区域高度
	var i = 1;
	$(window).scroll(function () {
	    var pageH = $("#mycontainer").height();
		var scrollT = $(window).scrollTop(); //滚动条top
		var aa = (pageH-winH-scrollT)/winH;
		if(aa<4.5){
			//alert(pageH);
			//alert(winH);
			//alert(scrollT);
			console.log(i);
			$.getJSON("plugin.php?id=xj_event:wsq_event_center&output=json",{page:i},function(json){
				if(json){
					var str = "";
					$.each(json,function(index,array){
						var str = '<div style="background-color:#FFFFFF;-moz-border-radius: 8px;-webkit-border-radius: 8px;border-radius:8px; padding:10px; margin-bottom:8px; border-bottom:2px #ddd solid;"><div style="float:left; width:80px; height:80px; background-color:#ddd;"><img src="'+array['activityaid_url']+'" border="0"></div><div style="margin-left:80px; padding:2px 10px; height:54px;">'+array['eid']+'</div><div style="clear:both;"></div></div>';
						$("#mycontainer").append(str);
						return false;
					});
					i++;
					//console.log(i);
				}else{
					$(".nodata").show().html("别滚动了，已经到底了。。。");
					return false;
				}
			});
		}
	});
});
*/
</script>
<style type="text/css">
    #container {margin:5px;}
    body {height:100%;}
</style>
</head>
<body style="padding-top:10px;">
<div id="mycontainer">
	<?php foreach($toplist as $value){ ?>
	<div style="background-color:#FFFFFF;-moz-border-radius: 8px;-webkit-border-radius: 8px;border-radius:8px; padding:10px; margin-bottom:8px; border-bottom:2px #ddd solid;" onClick="window.open('http://wsq.discuz.com/?c=index&a=viewthread&f=wx&tid=<?php echo $value['tid']; ?>&siteid=<?php echo $siteid; ?>&_bpage=1','_parent');">
    	<div style="float:left; width:80px; height:80px; background-color:#ddd;">
        	<img src="<?php echo $value['activityaid_url']; ?>" style="width:80px; height:80px;" border="0">
        </div>
        <div style="margin-left:80px; padding:2px 10px; height:80px; line-height:26px; font-size:14px;">
        	<?php echo $value['subject']; ?>
        </div>
		<div style="clear:both;"></div>
        <div style="height:30px;">
        	<div style="float:left; width:120px; padding-top:12px;">
        	<span style=" padding:3px 8px; color:#fff; background-color:#799edc; -moz-border-radius: 3px; -webkit-border-radius: 3px; border-radius:3px; font-size:12px; ">
            	<?php
				if($value['postclass']==1){
					echo lang('plugin/xj_event', 'xianxia');
				}else{
					echo lang('plugin/xj_event', 'xianshan');
				}
				?>
            </span>
            <span style=" padding:3px 8px; color:#fff; background-color:#799edc; -moz-border-radius: 3px; -webkit-border-radius: 3px; border-radius:3px; font-size:12px; margin-left:5px; ">
            	<?php
				if($value['postclass']==1){
					echo $offlineclass[$value['offlineclass']];
				}else{
					echo $onlineclass[$value['onlineclass']];
				}
				?>
            </span>
            </div>
            <div style="margin-left:120px; padding:2px 10px; height:30px; line-height:30px; font-size:12px; text-align:right;">
                <?php echo lang('plugin/xj_event', 'baomingrs'); ?>: <span style="color:#FF3300; font-weight:bold;"><?php echo $value['applynumber']; ?></span> <?php echo lang('plugin/xj_event', 'ren'); ?>
                <?php echo lang('plugin/xj_event', 'feiyong'); ?>:<span style="color:#FF3300; font-weight:bold;">
                <?php
                if($value['setting']['eventaa']){
                    echo 'AA';
                }else{
                    if($value['use_cost']>0){
                        echo $value['use_cost'].lang('plugin/xj_event', 'yuan');
                    }else{
                        echo lang('plugin/xj_event', 'mianfei');
                    }
                }
                ?></span>
            </div>
        </div>


        <div style="clear:both;"></div>
    </div>
    <?php } ?>
</div>
<div class="nodata"></div>
<div id="btnload" style="border:1px #ddd solid; background-color:#FFFFFF; height:40px; line-height:40px; font-size:14px; text-align:center; color:#666666; cursor:pointer; margin-bottom:30px;"><?php echo lang('plugin/xj_event', 'jzgdhd'); ?>...</div>
</body>
</html>
