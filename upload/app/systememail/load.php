<?php
if (!defined('puyuetian'))
	exit('403');

if ($_G['GET']['C'] == 'login') {
	//写入忘记密码按钮
	$_G['SET']['EMBED_FOOT'] .= template('systememail:load', TRUE);
}

if ($_G['SET']['APP_SYSTEMEMAIL_PHPMAILER'] && PHP_VERSION_ID >= 50500) {
	require $_G['SYSTEM']['PATH'] . 'app/systememail/driver/PHPMailer.php';
} else {
	require $_G['SYSTEM']['PATH'] . 'app/systememail/driver/default.php';
}

//新用户邮箱验证
if ($_G['SET']['APP_SYSTEMEMAIL_EMAILVERIFY'] && ($_G['GET']['C'] != 'app' && $_GET['a'] != 'systememail:emailverify')) {
	if (!$_G['USER']['ID'] && $_G['GET']['C'] == 'savereg') {
		//添加验证码
		$regarray['data'] = JsonData($regarray['data'], 'systememail_emailverify', CreateRandomString(7));
	}
	$emailverify = JsonData($_G['USER']['DATA'], 'systememail_emailverify');
	if ($_G['USER']['ID'] && $emailverify) {
		$sendtime = Cnum(JsonData($_G['USER']['DATA'], 'systememail_emailverify_sendtime'), 0, TRUE, 0);
		if (time() - $sendtime > 60) {
			sendmail($_G['USER']['EMAIL'], '邮箱验证码 - ' . $_G['SET']['LOGOTEXT'], '您的验证码为：<span style="color:#f60;font-weight:bold">' . $emailverify . '</span>');
			$_G['TABLE']['USER'] -> newData(array('id' => $_G['USER']['ID'], 'data' => JsonData($_G['USER']['DATA'], 'systememail_emailverify_sendtime', time())));
		}
		$_G['TEMP']['SVF'] = md5($_G['SET']['KEY'] . $emailverify);
		$_G['HTMLCODE']['MAIN'] .= template('systememail:emailverify', TRUE);
		template($_G['TEMPLATE']['MAIN']);
		exit();
	}
}
