<?php
if (!defined('puyuetian'))
	exit('403');

if ($_G['GET']['SUBMIT']) {
	$range = Cstr($_POST['range'], FALSE, $_G['STRING']['UPPERCASE'] . $_G['STRING']['LOWERCASE'] . $_G['STRING']['NUMERICAL'] . ',-', 1, 0);
	$whiteuid = Cstr($_POST['whiteuid'], FALSE, $_G['STRING']['NUMERICAL'] . ',', 1, 0);
	$whiteugid = Cstr($_POST['whiteugid'], FALSE, $_G['STRING']['NUMERICAL'] . ',', 1, 0);
	$content = '<';
	$content .= '?';
	$content .= 'php';
	$content .= '
if (!defined(\'puyuetian\'))
	exit(\'403\');

define(\'HADSKY_FIREWALL_SWITCH\', ' . Cnum($_POST['switch']) . ');
define(\'HADSKY_FIREWALL_RANGE\', ' . ($range ? "'{$range}'" : "''") . ');
define(\'HADSKY_FIREWALL_WHITELIST_UID\', ' . ($whiteuid ? "'{$whiteuid}'" : 0) . ');
define(\'HADSKY_FIREWALL_WHITELIST_UGID\', ' . ($whiteugid ? "'{$whiteugid}'" : 0) . ');
define(\'HADSKY_FIREWALL_WRITELOG\', ' . Cnum($_POST['writelog']) . ');';
	if (!file_put_contents($_G['SYSTEM']['PATH'] . 'puyuetian/firewall/config.php', $content)) {
		ExitJson('保存失败，请检查权限或主机防火墙配置');
	}
	ExitJson('保存成功', TRUE);
}
