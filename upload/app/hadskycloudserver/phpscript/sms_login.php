<?php
if (!defined('puyuetian'))
	exit('403');

if ($_G['USER']['ID']) {
	ExitGourl('index.php?c=center');
}

if ($_G['GET']['SUBMIT'] == 'yes') {
	$phone = Cstr($_POST['phone'], FALSE, $_G['STRING']['NUMERICAL'], 11, 11);
	if (substr($phone, 0, 1) != 1 || !$phone) {
		ExitJson('手机号不正确');
	}
	if ($_SESSION['APP_PUYUETIAN_SMS_PHONE'] != $phone) {
		ExitJson('接收短信手机号与提交的手机号不一致');
	}
	if ($_SESSION['APP_PUYUETIAN_SMS_CODE'] != $_POST['code'] || !$_POST['code']) {
		$_SESSION['APP_PUYUETIAN_SMS_CODE'] = '';
		ExitJson('短信验证码错误，请重新获取手机验证码');
	}
	$_SESSION['APP_PUYUETIAN_SMS_CODE'] = '';
	$userdata = UserLogin(array('phone' => $phone));
	if (!$userdata) {
		if (!$_G['TABLE']['USER'] -> getData(array('phone' => $phone))) {
			ExitJson('该手机号暂未注册或未绑定账号');
		} else {
			ExitJson($_G['USERLOGINFAILEDINFO']);
		}
	}
	ExitJson('登录成功', TRUE);
}
$_G['SET']['WEBTITLE'] = '用手机号登录 - ' . $_G['SET']['WEBADDEDWORDS'];
$_G['TEMPLATE']['BODY'] = 'hadskycloudserver:sms_login';
