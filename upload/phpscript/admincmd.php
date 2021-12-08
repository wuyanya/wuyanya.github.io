<?php
if (!defined('puyuetian')) {
	exit('403');
}

if (!$_G['USER']['ID']) {
	if ($_G['GET']['JSON']) {
		ExitJson('请登录');
	} else {
		exit('please login');
	}
}

$table = $_G['GET']['TABLE'];
$field = $_G['GET']['FIELD'];
$id = Cnum($_G['GET']['ID'], false, true, 1);
$value = $_GET['value'];

if (!InArray('read,reply', $table) || !InArray('top,high,sortid,locked,activetop', $field) || !$id) {
	if ($_G['GET']['JSON']) {
		ExitJson('参数非法');
	} else {
		exit('illegal parameter');
	}
}

//动态置顶,仅创始人可以操作
if ($field == 'activetop') {
	if ($_G['USER']['ID'] != 1) {
		ExitJson('动态置顶仅创始人可以操作');
	}
	$a = explode(',', $_G['SET']['ACTIVETOPREADIDS']);
	$b = '';
	$c = true;
	foreach ($a as $v) {
		if ($v == $id) {
			unset($a);
			$c = false;
		} else {
			$b .= ',' . $v;
		}
	}
	if ($c) {
		$b .= ',' . $id;
	}
	$b = substr($b, 1);
	$id = $_G['TABLE']['SET'] -> getId(array('setname' => 'activetopreadids'));
	$_G['TABLE']['SET'] -> newData(array('id' => $id, 'setname' => 'activetopreadids', 'setvalue' => $b));
	ExitJson('动态置顶' . ($c ? '置顶' : '取消') . '成功', true);
}

$data = $_G['TABLE'][strtoupper($table)] -> getData($id);

if (!$data) {
	if ($_G['GET']['JSON']) {
		ExitJson('不存在的id');
	} else {
		exit('Does not exist ID');
	}
}

if ($value == 'auto') {
	$value = $data[$field] ? 0 : 1;
}

if ($table == 'reply') {
	$readdata = $_G['TABLE']['READ'] -> getData($data['rid']);
	if (!$readdata) {
		ExitJson('不存在的文章');
	}
	$sortid = $readdata['sortid'];
}
$sortid = $data['sortid'];
$bkdata = $_G['TABLE']['READSORT'] -> getData($sortid);
$cando = FALSE;
if (InArray($bkdata['adminuids'], $_G['USER']['ID'])) {
	$cando = true;
}
if ($_G['USER']['ID'] == 1 || InArray(getUserQX(), 'superman')) {
	$cando = true;
}
if ($table == 'reply' && $field == 'top' && ($_G['USER']['ID'] == $readdata['uid'] || InArray(getUserQX(), 'admin'))) {
	$cando = true;
}

if (!$cando) {
	if ($_G['GET']['JSON']) {
		ExitJson('无权操作');
	} else {
		exit('Unauthorized operation');
	}
}
$_G['TABLE'][strtoupper($table)] -> newData(array('id' => $id, $field => $value));
if ($_G['GET']['JSON']) {
	ExitJson('操作成功', true);
} else {
	exit('ok');
}
