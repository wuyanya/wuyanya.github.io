var djs, $app_puyuetian_sms_verifycode = '';
$('#app-puyuetian_sms #getverifycode').on('click', function() {
	if(form_sms.phone.value.length != 11) {
		ppp({
			type: 3,
			icon: 2,
			content: '请输入正确的手机号码',
			close: function() {
				$('form[name="form_sms"] input[name="phone"]').focus();
			}
		});
		return false;
	}
	if($xyyzm == "1") {
		ppp({
			type: 2,
			title: '请输入验证码',
			noclose: 1,
			complete: function(_id) {
				var o = $('#pkpopup_' + _id);
				o.find('.pk-popup-body').css({
					position: 'relative'
				}).find('.pk-popup-input').after('<img src="index.php?c=app&a=verifycode:index&type=sms&rnd=' + Math.random() + '" onclick="this.src=\'index.php?c=app&a=verifycode:index&type=sms&rnd=\'+Math.random()" title="点击刷新" style="position:absolute;top:10px;right:10px;height:36px;cursor:pointer">');
			},
			submit: function(_id, _v) {
				if(!_v) {
					$('#pkpopup_' + _id + ' .pk-popup-input').focus();
					return;
				}
				$app_puyuetian_sms_verifycode = _v;
				sendsms();
				pkpopup.close(_id);
			}
		});
	} else {
		sendsms();
	}
});

function sendsms() {
	var pid = ppp({
		type: 4,
		shade: 1,
		content: '发送中...'
	});
	$.getJSON('index.php?c=app&a=hadskycloudserver:index&s=sms_send', {
		'phonenumber': form_sms.phone.value,
		'verifycode': $app_puyuetian_sms_verifycode,
		'chkcsrfval': $_USER['CHKCSRFVAL']
	}, function(data) {
		pkpopup.close(pid);
		if(data['state'] == 'ok') {
			$('#app-puyuetian_sms #getverifycode').prop('disabled', true);
			$('#app-puyuetian_sms #getverifycode span:eq(0)').html('(');
			$('#app-puyuetian_sms #getverifycode span:eq(1)').html('60');
			$('#app-puyuetian_sms #getverifycode span:eq(2)').html('s)');
			djs = setInterval(function() {
				var sj = parseInt($('#app-puyuetian_sms #getverifycode span:eq(1)').html()) || 0;
				if(sj <= 0) {
					clearInterval(djs);
					$('#app-puyuetian_sms #getverifycode').prop('disabled', false);
					$('#app-puyuetian_sms #getverifycode span:eq(0)').html('');
					$('#app-puyuetian_sms #getverifycode span:eq(1)').html('');
					$('#app-puyuetian_sms #getverifycode span:eq(2)').html('');
				} else {
					sj--;
					$('#app-puyuetian_sms #getverifycode span:eq(1)').html(sj);
				}
			}, 1000);
		} else {
			ppp({
				icon: 2,
				content: data['datas']['msg'] || '未知错误'
			});
		}
	});
}