<?php
if (!defined('puyuetian'))
	exit('403');

/*
 * AppName:Filesmanger
 * Author:Puyuetian
 * QQ:632827168
 */

$suffixs = "html,htm,hst,php,js,css,txt,log";
$type = $_G['GET']['TYPE'];

if ($_G['USER']['ID'] != 1) {
	if (!InArray('edit,save,del,mkdir,mkfile', $type) && !$_G['GET']['JSON']) {
		PkPopup('{content:"无权操作",icon:2,shade:1,hideclose:1,submit:function(){location.href="index.php?c=app&a=filesmanager:index&path="}}');
	}
	ExitJson('无权操作');
}

$spath = realpath($_G['SYSTEM']['PATH']);
$path = realpath($_GET['path']);
if (!$path) {
	if (!InArray('edit,save,del,mkdir,mkfile', $type) && !$_G['GET']['JSON']) {
		PkPopup('{content:"不存在的路径，请求路径：' . $_GET['path'] . '",icon:2,shade:1,hideclose:1,submit:function(){location.href="index.php?c=app&a=filesmanager:index&path="}}');
	}
	ExitJson('不存在的路径，请求路径：' . $_GET['path']);
}
$_G['TEMP']['PATH'] = iconv('GBK', 'UTF-8//IGNORE', $path);
if (strpos($path, $spath) !== 0) {
	if (!InArray('edit,save,del,mkdir,mkfile', $type) && !$_G['GET']['JSON']) {
		PkPopup('{content:"越权操作，请求路径：' . $_GET['path'] . '",icon:2,shade:1,hideclose:1,submit:function(){location.href="index.php?c=app&a=filesmanager:index&path="}}');
	}
	ExitJson('越权操作，请求路径：' . $_GET['path']);
}

switch ($type) {
	case 'edit' :
		if (filetype($path) != 'file') {
			if ($_G['GET']['JSON']) {
				ExitJson('不存在的文件');
			}
			PkPopup('{content:"不存在的文件",icon:2,shade:1,hideclose:1,submit:function(){location.href="index.php?c=app&a=filesmanager:index&path="}}');
		}
		$suffix = substr($path, strrpos($path, '.') + 1);
		if (!InArray($suffixs, $suffix)) {
			if ($_G['GET']['JSON']) {
				ExitJson('不支持的文件格式');
			}
			PkPopup('{content:"不支持的文件格式",icon:2,shade:1,hideclose:1,submit:function(){location.href="index.php?c=app&a=filesmanager:index&path="}}');
		}
		$filecontent1 = file_get_contents($path);
		$filecontent = htmlspecialchars($filecontent1, ENT_QUOTES);
		if ($filecontent1 && !$filecontent) {
			if ($_G['GET']['JSON']) {
				ExitJson('不支持该文件编码，仅支持UTF-8');
			}
			PkPopup('{content:"不支持该文件编码，仅支持UTF-8",icon:2,shade:1,hideclose:1,submit:function(){location.href="index.php?c=app&a=filesmanager:index&path="}}');
		}
		if ($_G['GET']['JSON']) {
			ExitJson($filecontent1, TRUE);
		}
		$path = str_replace('\\', '/', $path);
		$paths = explode('/', $path);
		$path = '';
		for ($i = 0; $i < count($paths); $i++) {
			if ($i == count($paths) - 1) {
				$filename = $paths[$i];
			} else {
				$path .= $paths[$i] . '/';
			}
		}
		ExitGourl('index.php?c=app&a=filesmanager:index&path=' . urlencode(realpath($path)) . '&editbtn=' . md5($filename));
		break;
	case 'save' :
		if (filetype($path) != 'file') {
			ExitJson('不存在的文件');
		}
		$suffix = substr($path, strrpos($path, '.') + 1);
		if (!InArray($suffixs, $suffix)) {
			ExitJson('不支持的文件格式');
		}
		$r = file_put_contents($path, $_POST['filecontent']);
		ExitJson('保存失败：' . $path, $r);
		break;
	case 'del' :
		$r = unlink($path);
		ExitJson('操作完成', $r);
		break;
	case 'mkdir' :
	case 'mkfile' :
		$mkname = $_GET['mkname'];
		if (!$mkname) {
			ExitJson('请输入目录或文件的名称');
		}
		if ($type == 'mkdir') {
			if (file_exists($path . "/{$mkname}")) {
				ExitJson('目录已存在');
			}
			$r = mkdir($path . "/{$mkname}");
		} else {
			if (file_exists($path . "/{$mkname}")) {
				ExitJson('文件已存在');
			}
			$r = file_put_contents($path . "/{$mkname}", '');
		}
		ExitJson('操作完成', $r === FALSE ? FALSE : TRUE);
		break;
}

$files = scandir($path);
foreach ($files as $file) {
	$pathfile = realpath($path . '/' . $file);
	$file = iconv('GBK', 'UTF-8//IGNORE', $file);
	if (filetype($pathfile) == 'dir') {
		if ($file == '..' && $path != $spath) {
			$syjml = substr($path, 0, strrpos(str_replace('\\', '/', $path), '/'));
			$filelist_dir .= '<tr><td colspan="4" style="border-right:0"><a class="pk-text-warning pk-hover-underline" href="index.php?c=app&a=filesmanager:index&path=' . urlencode($syjml) . '#workarea">上一级目录</a></td></tr>';
		}
		if ($file != '.' && $file != '..') {
			$filelist_dir .= '<tr><td><span class="fa fa-folder-o pk-text-warning fa-fw" title="文件夹"></span><a class="pk-text-primary pk-hover-underline" href="index.php?c=app&a=filesmanager:index&path=' . urlencode($pathfile) . '#workarea">' . $file . '</a></td><td colspan="3" style="border-right:0"></td></tr>';
		}
	} else {
		$filelist_file .= '<tr><td><span class="fa fa-fw fa-file-o" title="文件"></span>' . $file . '</td><td class="pk-text-center">' . date('Y-m-d H:i:s', filemtime($pathfile)) . '</td><td class="pk-text-center">' . number_format((filesize($pathfile) / 1024), 2) . 'K</td><td class="pk-text-center"><a id="' . md5($file) . '" class="pk-text-success pk-hover-underline" href="javascript:" onclick="_edit(\'' . str_replace('\\', '\\\\', $pathfile) . '\',\'' . $file . '\')">编辑</a>&nbsp;<a class="pk-text-danger pk-hover-underline" href="javascript:" onclick="delFile(\'' . str_replace('\\', '\\\\', $pathfile) . '\',this)">删除</a></td></tr>';
	}
}

$_G['TEMPLATE']['BODY'] = 'filesmanager:index';
$filelist = $filelist_dir . $filelist_file;
$_G['SET']['WEBTITLE'] = '文件在线管理器';
$_G['HTMLCODE']['OUTPUT'] .= template('filesmanager:index', TRUE);
