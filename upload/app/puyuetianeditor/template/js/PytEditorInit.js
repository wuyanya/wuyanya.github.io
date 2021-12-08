$(function() {
	var is_easyPost = function() {
		if(Cnum($_URI['ID'])) {
			return false;
		}
		if($(window).width() < 1000) {
			if($_URI['C'] == 'edit' && PytEditorPhoneEasypostread) {
				return true;
			}
			if($_URI['C'] == 'read' && PytEditorPhoneEasypostreply) {
				return true;
			}
		} else {
			if($_URI['C'] == 'edit' && PytEditorPCEasypostread) {
				return true;
			}
			if($_URI['C'] == 'read' && PytEditorPCEasypostreply) {
				return true;
			}
		}
		return false;
	}
	var defaultEditbox = function() {
		InitPuyuetianEditor("textarea[name=content]", function() {
			//加载自定义功能按钮
			var _t = $('<div>' + $('#PytEditorScript').data('config') + '</div>').find('>a');
			for(var i = 0; i < _t.length; i++) {
				var _a = $(_t[i]);
				var _rnd = '_' + randomString(32);
				$('#PytToolbar').append('<span id="' + _rnd + '"></span>');
				$('#' + _rnd).attr({
					class: _a.attr('class'),
					title: _a.text(),
					onclick: _a.attr('onclick')
				}).css({
					backgroundImage: 'url(' + (_a.attr('href') || 'app/puyuetianeditor/template/img/toolicons/none.png') + ')'
				});
			}
			$("#PytTiandouName").html($_SET['TIANDOUNAME']);
			$("#PytJifenName").html($_SET['JIFENNAME']);
			if((PytEditorHeight == '0' || !PytEditorHeight) && !(!!window.ActiveXObject || "ActiveXObject" in window) && $_URI['C'] == 'edit') {
				//自动增高模式
				var _h = $('#PytMainContent').outerHeight();
				var _th = _h;
				var _jl = $('#PytToolbar').offset().top;
				var _postjl = $('#postbtn').offset().top;
				var _wh = $(window).outerHeight();
				//增加浮动发布按钮
				$('body').append('<a id="_kjfban" class="pk-hadsky" href="javascript:" onclick="$(\'#postbtn\').click()" style="position:fixed;left:' + _jl + 'px;bottom:10px;border-radius:50%;color:white;background-color:#5FB878;text-align:center;width:48px;height:48px;line-height:48px;padding:0">发布</a>');
				$('#PytToolbar').css({
					top: 0,
					left: $('#PytToolbar').offset().left,
					width: $('#PytEditorFrame').outerWidth() - 2,
					'z-index': 999
				});
				setInterval(function() {
					//计算编辑器的实际高度
					var n = $(PytEditor.body).find('>*'),
						h = 0;
					for(var i = 0; i < n.length; i++) {
						h += parseInt($(n[i]).outerHeight());
					}
					if(h + 100 > parseInt(_h) && h != _th) {
						h += 100;
						_th = h;
						$('#PytMainContent,#PytMainContent2').outerHeight(h);
					}
					//工具框是否悬浮
					var jl = _jl - $("body").scrollTop();
					if(jl <= 0) {
						$('#PytToolbar').css({
							position: 'fixed'
						});
					} else {
						$('#PytToolbar').css({
							position: ''
						});
					}
					//发布按钮是否隐藏
					_postjl = $('#postbtn').offset().top;
					if(_postjl > _wh + $("body").scrollTop() - $('#postbtn').outerHeight() / 2) {
						$('#_kjfban').removeClass('pk-hide');
					} else {
						$('#_kjfban').addClass('pk-hide');
					}
				}, 200);
			}
		});
	}
	//是否为简洁发帖模式
	if(is_easyPost()) {
		$('textarea[name=content]').addClass('pk-hide').after('<div id="PytEasyEditBox"><div id="PytEasyTextarea"><textarea class="pk-textarea" style="height:' + (PytEditorHeight ? PytEditorHeight : '200') + 'px"></textarea></div><div id="PytEasyImagesBox" class="pk-row"><div class="_addbtn"></div></div><div id="PytEasyDefaultBox_Abtn" class="pk-text-right"><a class="pk-text-primary pk-hover-underline _defaultbtn" href="javascript:">切换至默认编辑器</a></div><div>');
		if($(window).width() < 1000) {
			var _w = $('#PytEasyImagesBox>div:eq(0)').outerWidth();
			$('#PytEasyImagesBox').before('<style>#PytEasyImagesBox>div{height:' + _w + 'px!important}</style>');
		}
		var _easyInterval = setInterval(function() {
			var h = $('#PytEasyTextarea textarea:eq(0)').val();
			var s = $('#PytEasyImagesBox img');
			for(var i = 0; i < s.length; i++) {
				h += '<p><img src="' + $(s[i]).attr('src') + '" alt="Image"></p>';
			}
			$('textarea[name=content]').val(h);
		}, 200);
		$('#PytEasyImagesBox ._addbtn').on('click', function() {
			var id = '_' + randomString(9);
			$('body').append('<form target="pk-di" class="pk-hide" id="' + id + '" method="post" enctype="multipart/form-data" action="index.php?c=app&a=puyuetianeditor:index&s=upload&t=image&easy=1&form_id=' + id + '"><input type="file" id="' + id + '_file" name="file[]" multiple="multiple" accept="image/*"></form>');
			$('#' + id + '_file').on('change', function() {
				if(!$(this).val()) {
					ppp({
						type: 3,
						icon: 3,
						content: "未选择任何文件"
					});
					return false;
				}
				ppp({
					type: 4,
					shade: 1,
					content: '上传中...',
					complete: function(_id) {
						var obj = $('#' + id);
						var action = obj.attr('action');
						obj.attr('action', action + '&pppid=' + _id);
						obj.submit();
					}
				});
			}).click();
		});
		$('#PytEasyDefaultBox_Abtn a._defaultbtn').on('click', function() {
			$('#PytEasyEditBox').remove();
			clearInterval(_easyInterval);
			defaultEditbox();
		});
		return true;
	}
	defaultEditbox();
});