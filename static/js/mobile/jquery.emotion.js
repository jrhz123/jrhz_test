/**
 * @author 夏の寒风
 * @time 2012-12-14
 */

var emotions = new Array();
var categorys = new Array();// 分组
// 初始化缓存，页面仅仅加载一次就可以了
$(function() {
	$.getScript("/data/cache/common_smilies_var.js?" + Math.random(), function(data, textStatus, jqxhr) {
		if(textStatus != 'success'){
			alert('不能加载表情文件！');
		}
	});
});

(function($){
	$.fn.SinaEmotion = function(target){
		var cat_current;
		$(this).click(function(event){
			event.stopPropagation();

			var eTop = target.offset().top + target.height() + 15;
			var eLeft = target.offset().left - 1;

			if($('#emotions .categorys')[0]){
				$('#emotions').css({top: eTop, left: eLeft});
				$('#emotions').toggle();
				return;
			}
			$('body').append('<div id="emotions"></div>');
			$('#emotions').css({top: eTop, left: eLeft});
			$('#emotions').html('<div>正在加载，请稍候...</div>');
			$('#emotions').click(function(event){
				event.stopPropagation();
			});
			
			$('#emotions').html('<div class="categorys"></div><div class="container"></div><div class="page"></div>');
			showCategorys();
			showEmotions();
			
		});
		$('body').click(function(){
			$('#emotions').remove();
		});
		$.fn.insertText = function(text){
			this.each(function() {
				if(this.tagName !== 'INPUT' && this.tagName !== 'TEXTAREA') {return;}
				if (document.selection) {
					this.focus();
					var cr = document.selection.createRange();
					cr.text = text;
					cr.collapse();
					cr.select();
				}else if (this.selectionStart || this.selectionStart == '0') {
					var 
					start = this.selectionStart,
					end = this.selectionEnd;
					this.value = this.value.substring(0, start)+ text+ this.value.substring(end, this.value.length);
					this.selectionStart = this.selectionEnd = start+text.length;
				}else {
					this.value += text;
				}
			});        
			return this;
		}
		function showCategorys(){
			$('#emotions .categorys').html('');
			for(var key in smilies_type){
				$('#emotions .categorys').append($('<a href="javascript:void(0);" value='+ smilies_type[key][1] + '@' + key + '>' + smilies_type[key][0] + '</a>'));
			}
			$('#emotions .categorys a').click(function(){
				showEmotions($(this).text(),$(this).attr('value'));
			});
			$('#emotions .categorys a').each(function(){
				if($(this).text() == cat_current){
					$(this).addClass('current');
				}
			});
		}
		function showEmotions(){
			var category = arguments[0] ? arguments[0]:'旺旺';
			var value = arguments[1] ? arguments[1]:'taobao@_500';
			var page = arguments[2] ? arguments[2]:1;
			var value_arr = value.split('@');
			var dir = value_arr[0];
			var id = value_arr[1].substr(1);
			var total_page = smilies_array[id].length;
			$('#emotions .container').html('');
			$('#emotions .page').html('');
			cat_current = category;
			for(var i=0;i<smilies_array[id][page].length; i++){
				$('#emotions .container').append($('<a href="javascript:void(0);" title="' + smilies_array[id][page][i][0] + '" value="' + smilies_array[id][page][i][1] + '"><img src="static/image/smiley/'+ dir + '/' + smilies_array[id][page][i][2] + '" alt="' + smilies_array[id][page][i][1] + '" width="25" height="25" /></a>'));
			}
			$('#emotions .container a').click(function(){
				target.insertText($(this).attr('value'));
				$('#emotions').remove();
			});
			for(var i = 1; i < total_page; i++){
				$('#emotions .page').append($('<a href="javascript:void(0);"' + (i == page ? ' class="current"':'') + '>' + i + '</a>'));
			}
			$('#emotions .page a').click(function(){
				showEmotions(category, value,$(this).text());
			});
			$('#emotions .categorys a.current').removeClass('current');
			$('#emotions .categorys a').each(function(){
				if($(this).text() == category){
					$(this).addClass('current');
				}
			});
		}
	}
})(jQuery);
