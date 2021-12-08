<?php
if (!defined('puyuetian'))
	exit('Not Found puyuetian!Please contact QQ632827168');

if ($_G['USER']['ID'] != 1) {
	PkPopup('{content:"无权此操作",shade:1,hideclose:1,submit:function(){location.href="index.php"}}');
}

$rnum = 50;
$table = strtolower(Cstr($_GET['table'], '', TRUE, 1, 255));
$type = $_G['GET']['TYPE'];
$page = Cnum($_G['GET']['PAGE'], 1, TRUE, 1);
$id = Cnum($_G['GET']['ID']);
$tablelist = '';

foreach ($_G['TABLE'] as $key => $value) {
	$key = strtolower($key);
	$_G['TABLES'][] = $key;
	$tablelist .= "<option value='$key'>{$key}</option>";
}

if ($table && !in_array($table, $_G['TABLES'])) {
	PkPopup('{content:"不存在的表",shade:1,hideclose:1}');
}
$table = strtoupper($table);

if ($type == 'look') {
	$pos = ($page - 1) * $rnum;
	$syy = $page - 1;
	$xyy = $page + 1;
	$array = $_G['TABLE'][$table] -> getDatas($pos, $rnum, "order by `id` desc");
	$tablecontent = "
		<form name='form_del' method='post' action='index.php?c=app&a=mysqlmanager:query&type=del&table={$table}'>
		<div class='pk-padding-15'>
			<input type='checkbox' onclick='checkall(this.checked)'>全选
			<input type='button' class='pk-btn pk-btn-xs pk-btn-danger' value='删除所选项' id='_delBtn'>
			<a class='pk-btn pk-btn-xs pk-btn-white' href='index.php?c=app&a=mysqlmanager:index&type=new&table={$table}'>添加新记录</a>
		</div>
		<table class='pk-table pk-table-bordered'>
		";
	foreach ($array as $key => $array2) {
		if ($key == 0) {
			$array_keys = array_keys($array2);
			$tablecontent .= "<tr><th></th>";
			foreach ($array_keys as $name) {
				$tablecontent .= "<th style='min-width:240px;width:auto'>{$name}</th>";
			}
			$tablecontent .= "</tr>";
		}
		$tablecontent .= "
			<tr class='onclick' title='点击编辑此记录'>
			<td><input name='ids[]' type='checkbox' value='{$array2['id']}'></td>
			";
		foreach ($array2 as $value) {
			$content = htmlspecialchars($value);
			$content2 = mb_substr($content, 0, 99);
			if ($content != $content2) {
				$content2 .= '...';
			}
			$tablecontent .= "<td class='onlyoneline pk-cursor-pointer' onclick=\"window.open('index.php?c=app&a=mysqlmanager:index&id={$array2['id']}&table={$table}&type=edit','_blank')\">" . $content2 . "</td>";
		}
		$tablecontent .= "</tr>";
	}
	$tablecontent .= "</table></form>";

} elseif ($type == 'edit' && $id) {
	$array = $_G['TABLE'][$table] -> getData($id);
	$tablecontent = "
		<form name='mysql_form' method='post' action='index.php?c=app&a=mysqlmanager:query&table={$table}&type=save&id=$id'>
		<table class='pk-table pk-table-bordered'>
		";
	$_i = 0;
	foreach ($array as $key => $value) {
		$_i++;
		$_column = $_G['TABLE'][$table] -> getColumns($key);
		$_column = current($_column);
		if ($key == 'id') {
			$btx = ' readonly style="background-color:#eee"';
		} else {
			$btx = '';
		}
		$tablecontent .= "
			<tr><td>
			<div class='pk-text-left' style='padding:10px 0'>
				<input type='text' name='keys[]' value='{$key}' readonly style='border:0;background:transparent;font-weight:bold'>
				<input type='text' value='数据类型：{$_column['Type']}' readonly style='border:0;background:transparent'>
				<input type='text' id='_Null{$_i}' value='是否为空：{$_column['Null']}' readonly style='border:0;background:transparent'>
				<input type='text' id='_Default{$_i}' value='默认值：{$_column['Default']}' readonly style='border:0;background:transparent'>
				<input type='text' value='说明：{$_column['Comment']}' readonly style='border:0;background:transparent'>
				<input type='text' value='此值必填' style='color:red;display:none' readonly>
			</div>
			<textarea class='pk-textarea' name='values[]' id='_Must{$_i}' rows='12'{$btx}>" . htmlspecialchars($value) . "</textarea>
			<script>
				var _v1{$_i}=document.getElementById('_Null{$_i}').value;
				var _v2{$_i}=document.getElementById('_Default{$_i}').value;
				if({$_i}!=1&&_v1{$_i}=='是否为空：NO'&&_v2{$_i}=='默认值：'){
					document.getElementById('_Must{$_i}').placeholder='必填';
				}		
			</script>
			</td></tr>
			";
	}
	$tablecontent .= "
		<tr><td class='pk-text-center'>
			<input type='button' class='pk-btn pk-btn-primary' id='_saveBtn' value='保存'>
		</td></tr>
		</table>
		</form>
		";
} elseif ($type == 'new' && !$id) {
	$array = $_G['TABLE'][$table] -> getColumns();
	$tablecontent = "
		<form name='mysql_form' method='post' action='index.php?c=app&a=mysqlmanager:query&table={$table}&type=save'>
		<table class='pk-table pk-table-bordered'>
		";
	$_i = 0;
	foreach ($array as $value) {
		$_i++;
		$_column = $_G['TABLE'][$table] -> getColumns($value['Field']);
		$_column = current($_column);
		if ($value['Field'] == 'id') {
			$btx = ' readonly style="display:none"';
		} else {
			$btx = '';
		}
		$tablecontent .= "
			<tr><td>
			<div class='pk-text-left' style='padding:10px 0'>
				<input type='text' name='keys[]' value='{$value['Field']}' readonly style='border:0;background:transparent;font-weight:bold'>
				<input type='text' value='数据类型：{$_column['Type']}' readonly style='border:0;background:transparent'>
				<input type='text' id='_Null{$_i}' value='是否为空：{$_column['Null']}' readonly style='border:0;background:transparent'>
				<input type='text' id='_Default{$_i}' value='默认值：{$_column['Default']}' readonly style='border:0;background:transparent'>
				<input type='text' value='说明：{$_column['Comment']}' readonly style='border:0;background:transparent'>
			</div>
			<textarea id='_Must{$_i}' name='values[]' class='pk-textarea' rows='12'{$btx}></textarea>
			<script>
				var _v1{$_i}=document.getElementById('_Null{$_i}').value;
				var _v2{$_i}=document.getElementById('_Default{$_i}').value;
				if({$_i}!=1&&_v1{$_i}=='是否为空：NO'&&_v2{$_i}=='默认值：'){
					document.getElementById('_Must{$_i}').placeholder='必填';
				}		
			</script>
			</td></tr>
			";
	}
	$tablecontent .= "
		<tr><td class='pk-text-center'>
			<input type='button' class='pk-btn pk-btn-primary' id='_saveBtn' value='保存'>
		</td></tr>
		</table>
		</form>
		";
} else {
	$tablecontent = "
		<div class='pk-text-center pk-padding'>
			请选择相应的表进行操作,每页显示{$rnum}条记录
		</div>
		";
}

if (!isset($_POST['keys']) && !isset($_POST['values'])) {
	$_G['TEMPLATE']['BODY'] = 'mysqlmanager:index';
	$_G['HTMLCODE']['OUTPUT'] = template('mysqlmanager:index', true);
}
