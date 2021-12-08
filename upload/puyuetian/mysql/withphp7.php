<?php
if (!defined('puyuetian'))
	exit('403');

if (function_exists('mysql_query')) {
	PkPopup('{type:3,content:"您当前PHP版本为' . PHP_VERSION . '，但却存在PHP7已经移除的mysql_相关函数，请确认并检查主机配置是否正确，若您无法处理可先恢复到PHP5.5/5.6版本。",icon:2,shade:1,times:0}');
} else {
	function mysql_query($query, $link = false) {
		return sqlQuery($query);
	}

	function mysql_error() {
		return sqlError();
	}

}
