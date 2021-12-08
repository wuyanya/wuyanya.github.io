<?php
if (!defined('puyuetian'))
	exit('403');

$_G['TEMP']['BODY'] = '';
$_G['TEMP']['ZBS'] = $_G['TEMP']['ZJL'] = $_G['TEMP']['ZDX'] = 0;
foreach ($_G['TABLE'] as $key => $value) {
	$_G['TEMP']['ZBS']++;
	switch ($_G['SQL']['TYPE']) {
		case 'sqlite' :
			PkPopup('{icon:2,shade:1,content:"抱歉，SQLite数据库暂不支持该功能",complete:function(_id){$("#pkpopup_"+_id+" .pk-popup-submit").addClass("pk-hide")},hideclose:1,nomove:1}');
			$data = current(sqlQuery("select * from `sqlite_master` where `type`='table' and `name`='" . $_G['SQL']['PREFIX'] . strtolower($key) . "'", 2));
			break;
		default :
			$data = current(sqlQuery('select * from information_schema.tables where table_schema=\'' . $_G['SQL']['DATABASE'] . '\' and table_name=\'' . $_G['SQL']['PREFIX'] . strtolower($key) . '\'', 2));
			break;
	}
	switch ($key) {
		case 'USER' :
			$desc = '用户信息数据表';
			break;
		case 'READ' :
			$desc = '文章信息数据表';
			break;
		case 'REPLY' :
			$desc = '回复信息数据表';
			break;
		case 'READSORT' :
			$desc = '版块信息数据表';
			break;
		case 'UPLOAD' :
			$desc = '上传的附件信息数据表';
			break;
		case 'SET' :
			$desc = '系统设置数据表';
			break;
		case 'DOWNLOAD_RECORD' :
			$desc = '附件下载记录数据表';
			break;
		case 'USER_MESSAGE' :
			$desc = '站内消息数据表，若数据量过大此表可以被清空';
			break;
		case 'USERGROUP' :
			$desc = '用户组信息数据表';
			break;
		default :
			$desc = strpos($key, 'APP_') === 0 ? '应用（插件或模板）数据表' : '未知的数据表';
			break;
	}
	$desc = $desc == '未知的数据表' ? '<span class="pk-text-danger">' . $desc . '</span>' : '<span class="pk-text-secondary">' . $desc . '</span>';
	$_G['TEMP']['BODY'] .= '
<tr class="infotr">
	<td>' . strtolower($key) . '</td>
	<td>' . $data['ENGINE'] . '</td>
	<td>' . $data['TABLE_COLLATION'] . '</td>
	<td>' . $data['TABLE_ROWS'] . '</td>
	<td>' . round($data['DATA_LENGTH'] / 1024, 2) . 'Kb</td>
	<td>' . $data['CREATE_TIME'] . '</td>
	<td>' . $data['UPDATE_TIME'] . '</td>
</tr>
<tr>
	<td class="pk-text-xs" colspan="99">' . $desc . '</td>
</tr>
';
	$_G['TEMP']['ZJL'] += $data['TABLE_ROWS'];
	$_G['TEMP']['ZDX'] += $data['DATA_LENGTH'];
}
$_G['TEMP']['ZDX'] = round($_G['TEMP']['ZDX'] / 1024 / 1024, 2);
