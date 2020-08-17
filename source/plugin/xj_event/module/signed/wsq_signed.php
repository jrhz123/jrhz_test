<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$tid = intval($_GET['tid']);

	$validation = $_GET['validation'];
	$thread =  DB::fetch_first("SELECT * FROM ".DB::table('forum_thread')." WHERE tid='$tid'");
	$count = DB::result_first("SELECT count(*) FROM ".DB::table('xj_event_signed')." WHERE tid='$tid' AND uid=".$_G['uid']);
	$apply = DB::fetch_first("SELECT * FROM ".DB::table('xj_eventapply')." WHERE tid='$tid' AND verify = 1 AND uid=".$_G['uid']);
if(!$apply && $_G['uid']>0){
	showmessage(lang('plugin/xj_event', 'nmybmbhdhz'));
}
if($count>0){
	showmessage(lang('plugin/xj_event', 'nyjqgdl'));
}

?>
<script type="text/javascript" src="http://wsq.discuz.com/cdn/discuz/js/openjs.js"></script>
<script>
        var menu = new Array();
		menu.push({name:"活动中心", pluginid:'xj_event:wsq_event_center', param:"a=1&b=2"});
        WSQ.initBtmBar(menu);
        WSQ.showBtmBar();
        WSQ.initPlugin({name:'<?php echo $thread['subject'].' '.lang('plugin/xj_event', 'huodongqd'); ?>'});
</script>
<?php
if($_G['uid']<1){ ?>
<script>
var referer = {
	pluginid: 'xj_event:wsq_signed',
	param: 'tid=<?php echo $tid; ?>'
};
WSQ.login(referer);
</script>
<?php
exit();
}
?>



<style type="text/css">
    body {height:100%;}
</style>

</head>
<body>

<?php
$sign = array();
$sign['tid'] = $tid;
$sign['uid'] = $_G['uid'];
$sign['dateline'] = $_G['timestamp'];
DB::insert('xj_event_signed',$sign);
?>

<div style="background-color:#FFF;-moz-border-radius: 6px; -webkit-border-radius: 6px; border-radius:6px; margin:10px;">
	<div style="line-height:22px; padding:10px 10px 5px 10px;">
    <?php echo lang('plugin/xj_event', 'tishi'); ?>:
	</div>
    <div style="border-top:1px solid #ececec; padding:10px; line-height:42px; font-size:20px; color:#e6424b; text-align:center; font-weight:bold;">
		<?php echo lang('plugin/xj_event', 'qdcggxndcy'); ?>
    </div>
</div>



</body>
</html>
