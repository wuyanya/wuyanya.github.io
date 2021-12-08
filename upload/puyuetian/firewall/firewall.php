<?php
if (!defined('puyuetian'))
	exit('403');

require $_G['SYSTEM']['PATH'] . 'puyuetian/firewall/config.php';

if (!HADSKY_FIREWALL_SWITCH) {
	return;
}

if (HADSKY_FIREWALL_WHITELIST_UID && InArray(HADSKY_FIREWALL_WHITELIST_UID, $_SESSION['HS_UID'])) {
	return;
}

if (HADSKY_FIREWALL_WHITELIST_UGID && InArray(HADSKY_FIREWALL_WHITELIST_UGID, $_SESSION['HS_UGID'])) {
	return;
}

function _firewallWriteLog($type, $rule, $text) {
	global $_G;
	if ($type == 'cookie') {
		foreach ($_COOKIE as $k => $v) {
			setcookie($k, null);
		}
	}
	if (HADSKY_FIREWALL_WRITELOG) {
		$path = $_G['SYSTEM']['PATH'] . 'logs/firewall/' . date('Ymd') . '/';
		if (file_exists($path) || mkdir($path, 0777, true)) {
			$path .= date('His') . '.log';
			$txt = "请求类型：{$type}
触发规则：{$rule}
具体内容：" . print_r($text, TRUE) . "
访问路径：" . $_G['SYSTEM']['LOCATION'] . "
网络地址：" . getClientInfos('ip') . "
操作用户：" . Cnum($_SESSION['HS_UID']) . "
拦截时间：" . date('Y-m-d H:i:s') . "
其他信息：" . getClientInfos();
			file_put_contents($path, $txt, FILE_APPEND);
		}
	}
	$txt = '您的请求包含非法内容已被系统防火墙拦截，如果您是管理员请前往网站后台 - 实验室 - 防火墙查看配置';
	$txt = array('state' => 'no', 'msg' => $txt, 'datas' => array('msg' => $txt));
	header('Content-type:application/json');
	exit(json_encode($txt, JSON_UNESCAPED_UNICODE));
}

$txt = '风险操作';
$rules = array();
$path = $_G['SYSTEM']['PATH'] . 'puyuetian/firewall/rules/';
$a = scandir($path);
foreach ($a as $v) {
	if (filetype($path . $v) == 'file') {
		$b = file_get_contents($path . $v);
		$b = str_replace("\r", '', $b);
		$rules[current(explode('.', $v))] = explode("\n", $b);
	}
}

if (InArray(HADSKY_FIREWALL_RANGE, 'uri') && $rules['uri']) {
	foreach ($rules['uri'] as $v) {
		if ($v && preg_match('#' . $v . '#i', urldecode(urldecode($_SERVER['REQUEST_URI'])))) {
			_firewallWriteLog('uri', $v, $_SERVER['REQUEST_URI']);
		}
	}
}
if (InArray(HADSKY_FIREWALL_RANGE, 'get') && $rules['get']) {
	foreach ($rules['get'] as $v) {
		foreach ($_GET as $v2) {
			if ($v && preg_match('#' . $v . '#i', $v2, $m)) {
				_firewallWriteLog('get', $v, $_GET);
			}
		}
	}
}
if (InArray(HADSKY_FIREWALL_RANGE, 'post') && $rules['post']) {
	foreach ($rules['post'] as $v) {
		foreach ($_POST as $v2) {
			if ($v && preg_match('#' . $v . '#i', $v2)) {
				_firewallWriteLog('post', $v, $_POST);
			}
		}
	}
}
if (InArray(HADSKY_FIREWALL_RANGE, 'cookie') && $rules['cookie']) {
	foreach ($rules['cookie'] as $v) {
		foreach ($_COOKIE as $v2) {
			if ($v && preg_match('#' . $v . '#i', $v2)) {
				_firewallWriteLog('cookie', $v, $_COOKIE);
			}
		}
	}
}
if (InArray(HADSKY_FIREWALL_RANGE, 'user-agent') && $rules['user-agent']) {
	foreach ($rules['user-agent'] as $v) {
		if ($v && preg_match('#' . $v . '#i', $_SERVER['HTTP_USER_AGENT'])) {
			_firewallWriteLog('user-agent', $v, $_SERVER['HTTP_USER_AGENT']);
		}
	}
}

unset($a, $b, $v, $v2, $path, $rules);
