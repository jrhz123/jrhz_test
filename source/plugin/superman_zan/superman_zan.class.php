<?php
/**
 *  [【超人】点赞(superman_zan.{modulename})] (C)2012-2099 Powered by 时创科技.
 *  Version: $VERSION
 *  Date: $DATE
 */
if(!defined('IN_DISCUZ')) {
  exit('Access Denied');
}

class plugin_superman_zan
{
  protected $setting;
  protected $config;
  protected $group;

  public function plugin_superman_zan()
  {
    global $_G;
    if (!$_G['cache']['plugin']['superman_zan']) {
      loadcache('plugin');  
    }
    $this->config = $_G['cache']['plugin']['superman_zan'];
    $this->open = $this->config['open'];
    $this->group = $this->config['zan_reply_groups'];
    
    if (!$_G['cache']['superman_zan']) {
      loadcache('superman_zan');
    }
    $this->setting = $_G['cache']['superman_zan'];
  }

}

class plugin_superman_zan_forum extends plugin_superman_zan
{ 
        
  public function forumdisplay_thread_subject_output()
    {
    if(!$this->open){
      return array();
    }
    global $threadlist;
    $mylang = lang('plugin/superman_zan');
    $ret = array();

    foreach($threadlist as $num => $thread){
      $z_count = DB::result_first('SELECT `count` FROM '.DB::table('plugin_superman_zan').' WHERE `tid`='.$thread[tid]);
      if ($z_count) {
        //$ret[$num] = '<span style="color:#f00">['.$z_count.$mylang['zan_title'].']</span>&nbsp;';
        $ret[$num] = '<span style="color: #e2ac2d;margin-left: 2px;white-space: nowrap;font-size:14px;" title="'.$z_count.$mylang['zan_title'].'"><img src="static/image/common/ding.png" align="absmiddle" alt="'.$mylang['zan_title'].'" title="'.$mylang['zan_title'].'">&nbsp;+'.$z_count.'</span>&nbsp;';
      }
    }

        return $ret;
    }

  public function viewthread_postsightmlafter_output(){

    if(!$this->open){
      return array();
    }

    global $_G, $postlist, $threadlist;
    $mylang = lang('plugin/superman_zan');

    $ret = array();
    //$new_postlist = $postlist;
    //$post = array_shift($new_postlist);
    if (!$_G['tid']) {//if (!$_G['tid'] || !$post['first']) {  修改
      return $ret;
    }
    //unset($new_postlist, $post);

    $visitor_radio = $this->setting['visitor_radio']?1:0;
        $formhash = FORMHASH;

    $sty_js =<<<EOF
<script>
function _sc_superman_zan(pid) {
  if (!$visitor_radio && !{$_G['uid']}) {
    showWindow('login', 'member.php?mod=logging&action=login');
    return false;
  }
  if (getcookie('pid_'+pid) && !{$_G['uid']}) {
    showError('{$mylang['zan_again']}');
    return false;
  }
  var x = new Ajax();
  x.get('plugin.php?id=superman_zan:ajax&formhash=$formhash&pid='+pid+'&_r='+Math.random(), function(s){
    if (s == 1) {
      showPrompt(null, null, '<span style="color:#fff">{$mylang['zan_title']} +1</span>', 2000); //Enicn_d modified 2017-03-05 Ajax 更新点赞数
      var count = $('zanCount').innerText >> 0;
      count++;
      $('zanCount').innerHTML = count;
      $('zanNumber').style.display = 'block';
      $('zanAvatar').style.display = 'inline-block';
      //showPrompt(null, null, '<span style="color:#fff">{$mylang['zan_title']} +1,{$mylang['refresh']}</span>', 2000);
      //var num = parseInt($('_sc_zannumber').innerHTML) + 1;
      //$('_sc_zannumber').innerHTML = num;
      //$('_sc_zannumber_a').title = num+' {$mylang['zan_count']}';
    } else if (s == -1) {
      showWindow('login', 'member.php?mod=logging&action=login');
    } else if (s == 0){
      showError('{$mylang['zan_again']}');
    } else if (s == -2){
      showError('{$mylang['zan_self']}');
    } else {
      showError('{$mylang['sys_error']}');
    }
  });
  setTimeout("location.reload(true)",3000);
  return true;
}

function _skip(){
  var url = window.location.pathname;
  window.location.href = url + "#fastposteditor"; 
}

</script>

<script>
  window._bd_share_config = {
    "common":{
      "bdSnsKey":{},
      "bdText":"",
      "bdMini":"2",
      "bdMiniList":["weixin","tsina","qzone","sqq","tqq","renren"],
      "bdPic":"",
      "bdStyle":"1",
      "bdSize":"24"
    },
    "share":{},
    };
    with(document)0[(getElementsByTagName('head')[0]||body).appendChild(createElement('script')).src='http://bdimg.share.baidu.com/static/api/js/share.js?v=89860593.js?cdnversion='+~(-new Date()/36e5)];
</script>

<style>
.sc_img img{margin:0px 5px 2px 5px;border-radius:7px;display:block;}
.sc_img h1{font-size:12px;background-color:#EFEFEF;padding:3px 8px;color:#666;font-weight:normal;}
.sc_img_height{height:80px;overflow:hidden;margin-top:10px;}

.read-option{width:310px;margin:10px auto 30px;}
.read-option a.btn-option,.read-option .bdsharebuttonbox a.btn-option{text-indent:-999px;overflow: hidden;width:65px;height:65px;margin-right:15px;background-color:#fafafa;display:block;float:left;text-align:center;font-size:12px;font-family:"Arial";color:#666;}
.read-option a:hover{text-decoration:none}
.read-option a,.read-option .bdsharebuttonbox a.btn-option {display:block;width:36px;height:36px;background:url(http://js.jxft.net/template/tiboo/read2016/images/btn_b.png) no-repeat;background-position:-210px -70px;}
.read-option a.nograybg{background:#fafafa;overflow:inherit;}
.read-option a.nograybg span{margin:14px 0 0 14px;background:url(http://js.jxft.net/template/tiboo/read2016/images/btn_b.png) no-repeat;}
.read-option a.nograybg span.btn-zan{background-position:0px -83px;}
.read-option a.nograybg span.btn-zan,.read-option a.nograybg span.btn-zan-on{display:block;width:36px;height:36px;}
.read-option a.nograybg span.btn-zan-on,.read-option a.nograybg:hover span.btn-zan{background-position:0 0;}
.read-option a.btn-reply {background-position:-59px -69px;}
.read-option a.btn-reply:hover{background-position:-59px 14px;}
.read-option a.btn-favor{background-position:-135px  -70px;}
.read-option a.btn-favor-on,.read-option a.btn-favor:hover{background-position:-135px  15px;}
.read-option .bdsharebuttonbox a.btn-option{width:65px;height:65px;}
.read-option .bdsharebuttonbox{display:block;width:65px;height:65px;overflow:hidden;background-color:#fafafa;}
.read-option .bdsharebuttonbox a.btn-share{margin:0px;}
.read-option a, .read-option .bdsharebuttonbox a.btn-option:hover {background-position:-210px 14px}
.score {
    border: none;
    width: 600px;
    margin: 0 auto;
    text-align: center;
}
.zantip {
    text-align: center;
    margin-bottom: 20px;
    font-size: 16px;
    color: #999;
}.scoreTop {
    border: none;
    background: none;
    padding: 0 0 5px;
    color: #666;
    line-height: 3;
}.scoreTop a img {
    width: 38px;
    height: 38px;
    margin-right: 12px;
    border: 1px solid #f0f0f0;
}
.scoreTop a:hover {
  text-decoration:underline;
  color:#1B7FC6;
}
</style>

EOF;


    $zan_user_num = intval($this->setting['zan_user_num']) ? intval($this->setting['zan_user_num']) : 13;
    $group = unserialize($this->group);
    $i = 0;

    foreach($postlist as $p){
      if($p['first'] || in_array($p['groupid'],$group)){
        $sql = 'SELECT `count` FROM '.DB::table('plugin_superman_zan').' WHERE `pid`='.$p['pid'];
        $count = DB::result_first($sql);
        if (empty($count)) {$count = 0;}
        $uids = DB::fetch_all('SELECT `uid` FROM '.DB::table('plugin_superman_zan_ref').' WHERE `pid`='.$p['pid'].' ORDER BY dateline ASC LIMIT 0,'.$zan_user_num, array(), 'uid');

        $sty_f = '<div class="read-option"><a class="btn-option nograybg" href="javascript:;" onclick="return _sc_superman_zan('.$p['pid'].')" title="'.$mylang['mobile_zan'].'"><span class="btn-zan"></span></a><a href="javascript:_skip()" class="btn-option btn-reply" title="'.$mylang['reply'].'"></a><span><a class="btn-option btn-favor" href="home.php?mod=spacecp&ac=favorite&type=thread&id='.$_G['tid'].'" id="k_favorite" onclick="showWindow(this.id, this.href, get, 0);"  title="'.$mylang['favor'].'"><i><img src="static/image/common/fav.gif" alt="收藏" data-bd-imgshare-binded="1">收藏<span id="favoritenumber" style="display:none">0</span></i></a></span><span class="bdsharebuttonbox bdshare-button-style0-16" data-bd-bind="1472195922133"><a href="javascript:;" class="btn-option btn-share bds_more" data-cmd="more"><span></span>分享</a></span></div>';

        if($i == 0){
          $ret[$i] = $sty_js.$sty_f;
        }else{
          $ret[$i] = $sty_f;
        }
        
        //Enicn_d modified 2017-03-05 Ajax 更新点赞数
        $custom_title = trim($this->setting['custom_title'])?trim($this->setting['custom_title']):$mylang['zan_user'];
        if($uids){
          $mylang = lang('plugin/superman_zan');
          $avatar = '';
          $namelist = C::t('common_member')->fetch_all_username_by_uid(array_keys($uids));
          $zaned = false;
          foreach($uids as $v){
            if ($v['uid'] === $_G['uid']) {
              $zaned = true;
            }
            $username = $namelist[$v['uid']];
            $avatar .= '<a target="_blank" style="display:inline-block;" href="home.php?mod=space&uid='.$v['uid'].'" title="'.$username.'">'.avatar($v['uid'], 'small').'<div style="width:48px;text-overflow:ellipsis;color:#1B7FC6; white-space:nowrap; overflow:hidden;margin-left:-5px;margin-top:-7px;" title="'.$username.'">'.$username.'</div></a>';
          }
          if (!$zaned) {
            $avatar .= '<a id="zanAvatar" target="_blank" style="display:none;" href="home.php?mod=space&uid='.$_G['uid'].'" title="'.$_G['username'].'">'.avatar($_G['uid'], 'small').'<div style="width:48px;text-overflow:ellipsis;color:#1B7FC6; white-space:nowrap; overflow:hidden;margin-left:-5px;margin-top:-7px;" title="'.$_G['username'].'">'.$_G['username'].'</div></a>';
          }
          $ret[$i] .= '<div class="score"><p class="zantip"><strong id="zanCount">'.$count.'</strong>'.$custom_title.'</p><div class="scoreTop" id="zanList">'.$avatar.'</div></div>';
        } else {
          $ret[$i] .= '<div class="score"><p class="zantip" id="zanNumber" style="display: none;"><strong id="zanCount">0</strong>'.$custom_title.'</p><div class="scoreTop" id="zanList"><a id="zanAvatar" target="_blank" style="display:none;" href="home.php?mod=space&uid='.$_G['uid'].'" title="'.$_G['username'].'">'.avatar($_G['uid'], 'small').'<div style="width:48px;text-overflow:ellipsis;color:#1B7FC6; white-space:nowrap; overflow:hidden;margin-left:-5px;margin-top:-7px;" title="'.$_G['username'].'">'.$_G['username'].'</div></a></div></div>';
        }
        //Enicn_d modified 2017-03-05 Ajax 更新点赞数
        
      } else {
        if($i == 0){
          $ret[$i] = $sty_js;
        }else{
          $ret[$i] = "";
        }
      }
      $i++;
    }
    
    return $ret;
  }

}
