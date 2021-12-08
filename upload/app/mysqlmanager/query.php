<?php
if (!defined('puyuetian'))
	exit('Not Found puyuetian!Please contact QQ632827168');

$table = strtolower(Cstr($_GET['table'], '', TRUE, 1, 255));
$type = $_G['GET']['TYPE'];
$page = Cnum($_G['GET']['PAGE'], 1, TRUE, 1);

if ($_G['USER']['ID'] != 1) {
	ExitJson('无权操作');
}
if (!$table) {
	ExitJson('参数错误');
}
foreach ($_G['TABLE'] as $key => $value) {
	$key = strtolower($key);
	$_G['TABLES'][] = $key;
}
if (!in_array($table, $_G['TABLES'])) {
	ExitJson('不存在的表');
}

$table = strtoupper($table);
if ($type == 'save') {
	$keys = (array)$_POST['keys'];
	$values = (array)$_POST['values'];
	$array = array_combine($keys, $values);
	$r = $_G['TABLE'][$table] -> newData($array);
	if (!$r) {
		ExitJson(sqlError());
	}
} elseif ($type == 'del') {
	$ids = (array)$_POST['ids'];
	foreach ($ids as $value) {
		if (Cnum($value)) {
			$_G['TABLE'][$table] -> delData($value);
		}
	}
} else {
	ExitJson('非法的参数');
}

ExitJson('操作成功完成', TRUE);
