<?php
if (!defined('puyuetian'))
	exit('403');

global $regarray;

if ($_G['USER']['ID']) {
	ExitGourl('index.php?from=sms_reg');
}

if ($_G['GET']['SUBMIT'] == 'yes') {
	header('Content-type:application/json');
	if ($_G['SET']['USERNAMEEVERYCHARS']) {
		preg_match('/^[\x{4e00}-\x{9fa5}A-Za-z0-9_]+$/u', $_POST['username']) ? $username = $_POST['username'] : $username = FALSE;
		if (strlen($username) > 24 || strlen($username) < 3) {
			$username = FALSE;
		}
	} else {
		$username = Cstr($_POST['username'], FALSE, TRUE, 3, 24);
	}
	if (Cnum($username)) {
		$username = FALSE;
	}
	$phone = Cstr($_POST['phone'], FALSE, $_G['STRING']['NUMERICAL'], 11, 11);
	if (substr($phone, 0, 1) != 1 || !$phone) {
		ExitJson('手机号不正确');
	}
	if ($_G['TABLE']['USER'] -> getId(array('phone' => $phone))) {
		ExitJson('手机号已被注册');
	}
	if (!$username) {
		ExitJson('用户名不合法');
	}
	//检测用户信息是否包含违禁词
	$wjcs = $_G['SET']['BANREGWORDS'];
	if ($wjcs) {
		$wjcs = explode(',', $wjcs);
		foreach ($wjcs as $w) {
			if ($w) {
				if (strpos($username, $w) !== FALSE) {
					ExitJson('用户名或昵称包含违禁词，请修改后再注册');
				}
			}
		}
	}
	if ($_G['TABLE']['USER'] -> getId('username', $username)) {
		ExitJson('用户名已存在');
	}
	$email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
	if (!$email) {
		ExitJson('邮箱地址不合法');
	}
	if ($_G['TABLE']['USER'] -> getId('email', $email)) {
		ExitJson('邮箱已存在');
	}
	$password = Cstr($_POST['password'], FALSE, FALSE, 5, 16);
	if (!$password) {
		ExitJson('密码5-16位');
	}
	if ($_SESSION['APP_PUYUETIAN_SMS_PHONE'] != $phone || !$phone) {
		ExitJson('接收短信手机号与提交的手机号不一致');
	}
	if ($_SESSION['APP_PUYUETIAN_SMS_CODE'] != $_POST['code'] || !$_POST['code']) {
		ExitJson('短信验证码错误');
	}
	$regarray['nickname'] = $regarray['username'] = $username;
	$regarray['password'] = md5($password);
	$regarray['email'] = $email;
	$regarray['phone'] = $phone;
	$regarray['sex'] = 's';
	$regarray['regip'] = getClientInfos('ip');
	$regarray['regtime'] = time();
	$regarray['groupid'] = Cnum($_G['SET']['REGUSERGROUPID'], 0, TRUE, 0);
	$regarray['readlevel'] = Cnum($_G['SET']['REGREADLEVEL'], 10, TRUE);
	$regarray['quanxian'] = $_G['SET']['REGUSERQUANXIAN'];
	$regarray['jifen'] = Cnum($_G['SET']['REGJIFEN']);
	$regarray['tiandou'] = Cnum($_G['SET']['REGTIANDOU']);
	$regarray['friends'] = $_G['SET']['REGFRIENDS'];
	$r = $_G['TABLE']['USER'] -> newData($regarray);
	if (!$r) {
		ExitJson(sqlError());
	}
	$_SESSION['APP_PUYUETIAN_SMS_PHONE'] = $_SESSION['APP_PUYUETIAN_SMS_CODE'] = '';
	UserLogin(array('username' => $username, 'password' => md5($password)));
	ExitJson('注册成功', TRUE);
}
if ($_G['SET']['USERNAMEEVERYCHARS']) {
	$_G['TEMP']['HANZI'] = '可由汉字、字母、数字及下划线组成，不能为纯数字且最多8个汉字';
} else {
	$_G['TEMP']['HANZI'] = '仅由字母、数字及下划线组成，不能为纯数字且3-24位';
}

$_G['SET']['WEBTITLE'] = '用手机号注册';
$_G['TEMPLATE']['BODY'] = 'hadskycloudserver:sms_reg';
