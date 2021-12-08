<?php
if (!defined('puyuetian'))
	exit('403');

$type = $_G['GET']['TYPE'];
$tsetpath = 'superadmin:app';
//==========================安装应用======================================
if ($_G['GET']['ML'] == 'install') {
	$r = installAT($type, $_G['GET']['T']);
	if ($r !== TRUE) {
		ExitJson($r);
	}
	ExitJson('操作成功', TRUE);
	//==========================卸载应用======================================
} elseif ($_G['GET']['ML'] == 'uninstall') {
	$r = uninstallAT($type, $_G['GET']['T']);
	if ($r !== TRUE) {
		ExitJson($r);
	}
	ExitJson('操作成功', TRUE);
	//==========================导出模板数据======================================
} elseif ($_G['GET']['ML'] == 'json') {
	$jsonarray = array();
	$perfix = $type . '_' . $_G['GET']['T'] . '_';
	foreach ($_G['SET'] as $key => $value) {
		if (substr($key, 0, strlen(strtoupper($perfix))) != strtoupper($perfix)) {
			continue;
		}
		$jsonarray[strtolower($key)] = $value;
	}
	header('Content-type:text/json');
	exit(json_encode($jsonarray));
	//==========================加载应用设置======================================
} elseif ($_G['GET']['ML'] == 'setting') {
	$tsetpath = "{$_G['SYSTEM']['PATH']}{$type}/{$_G['GET']['T']}/setting.hst";
} elseif ($_G['GET']['T'] == 'development') {
	if ($_G['GET']['SUBMIT']) {
		if (!$_POST['logobase64']) {
			ExitJson('请设置logo');
		}
		$array = array();
		$array['type'] = $_POST['type'];
		if (!InArray('app,template', $array['type'])) {
			ExitJson('应用类型错误');
		}
		$array['title'] = $_POST['title'];
		$array['dir'] = Cstr($_POST['dir'], FALSE, TRUE, 3, 255);
		if (!$array['dir']) {
			ExitJson('目录格式非法');
		}
		$array['version'] = $_POST['version'];
		$array['description'] = $_POST['description'];
		$path = $_G['SYSTEM']['PATH'] . $array['type'] . '/' . $array['dir'] . '/';
		if (file_exists($path)) {
			ExitJson('该目录已经存在');
		}
		//创建公有资源
		//创建应用脚本目录
		mkdir($path . 'phpscript/', 0777, TRUE);
		//config.json
		file_put_contents($path . 'config.json', json_encode($array, JSON_UNESCAPED_UNICODE));
		//setting.hst
		$d = '<form name="form_save" method="post" action="index.php?c=app&a=superadmin&s=save&table=set">
	<div class="pk-row pk-padding-bottom-15 pk-margin-bottom-15" style="border-bottom:solid 1px #458fce">
		<label class="pk-w-sm-3 _labeltext pk-text-primary">' . $array['title'] . ' ' . ($array['type'] == 'app' ? '插件' : '模板') . '设置</label>
		<div class="pk-w-sm-8"></div>
	</div>
';
		if ($array['type'] == 'app') {
			$d .= '
	<div class="pk-row pk-padding-bottom-15">
		<label class="pk-w-sm-3 _labeltext">应用开关</label>
		<div class="pk-w-sm-8">
			<select name="app_' . $array['dir'] . '_load" class="pk-textbox" data-value="{$_G[\'SET\'][\'APP_' . strtoupper($array['dir']) . '_LOAD\']}">
				<option value="0">关闭</option>
				<option value="1">开启</option>
			</select>
		</div>
	</div>
';
		}
		$d .= '
	<div class="pk-row pk-padding-bottom-15">
		<label class="pk-w-sm-3 _labeltext">&nbsp;</label>
		<div class="pk-w-sm-8">
			<button id="SubmitBtn" type="button">保存</button>
		</div>
	</div>
</form>';
		file_put_contents($path . 'setting.hst', $d);
		//logo.png
		file_put_contents($path . 'logo.png', base64_decode(substr($_POST['logobase64'], 22)));
		if ($array['type'] == 'app') {
			mkdir($path . 'template/phpscript/', 0777, TRUE);
			file_put_contents($path . 'index.php', '<?php
if (!defined(\'puyuetian\'))
	exit(\'403\');

LoadAppScript();');
			file_put_contents($path . 'template/default.hst', '插件默认页面');
			file_put_contents($path . 'phpscript/default.php', '<?php
if (!defined(\'puyuetian\'))
	exit(\'403\');

$_G[\'HTMLCODE\'][\'OUTPUT\'] .= template(\'' . $array['dir'] . ':default\',TRUE);');
		} else {
			mkdir($path . 'img', 0777);
			mkdir($path . 'css', 0777);
			mkdir($path . 'js', 0777);
		}
		ExitJson($path, TRUE);
	}
	$tsetpath = 'superadmin:app-development';
} elseif ($_G['GET']['T'] == 'uploadapp') {
	if ($_G['GET']['SUBMIT']) {
		//原文件名
		$file_name = $_FILES['file']['name'];
		//服务器上临时文件名
		$tmp_name = $_FILES['file']['tmp_name'];
		$suffix = strtolower(end(explode('.', $_FILES['file']['name'])));
		if (!InArray('hsa,zip', $suffix)) {
			ExitJson('仅允许上传hsa和zip后缀文件');
		}
		$rnd = CreateRandomString(32);
		$lj = $_G['SYSTEM']['PATH'] . "app/superadmin/appzip/{$rnd}.zip";
		if (!move_uploaded_file($_FILES['file']['tmp_name'], $lj)) {
			ExitJson('上传失败');
		}
		if ($suffix == 'hsa') {
			$content = file_get_contents($lj);
			file_put_contents($lj, base64_decode($content));
		}
		$zip = new ZipArchive;
		$res = $zip -> open($lj);
		if ($res !== TRUE) {
			unlink($lj);
			ExitJson('解压失败：' . $res);
		}
		$zip -> extractTo($_G['SYSTEM']['PATH']);
		$zip -> close();
		unlink($lj);
		ExitJson('上传成功', TRUE);
	}
	$tsetpath = 'superadmin:app-upload';
} else {
	//==========================获取本地插件信息======================================
	$_G['TEMP']['DATA'] = htmlspecialchars(json_encode(getAT($type)), ENT_QUOTES);
}

$contenthtml = template($tsetpath, TRUE);
