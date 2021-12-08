<?php
if (!defined('puyuetian'))
	exit('403');

$id = $_G['GET']['ID'];

if (!$_G['USER']['ID'] && Cnum($_G['GET']['UID']) && Cnum($_G['GET']['CREATETIME']) + 3600 > time()) {
	$ud = $_G['TABLE']['USER'] -> getData($_G['GET']['UID']);
	if ($ud) {
		$chkcode = md5($id . $ud['id'] . md5($ud['session_token']) . $_G['GET']['CREATETIME']);
		if ($chkcode == $_G['GET']['CHKCODE']) {
			standardArray($ud);
			$_G['USER'] = $ud;
			if ($_G['USER']['GROUPID']) {
				$_G['USERGROUP'] = $_G['TABLE']['USERGROUP'] -> getData($_G['USER']['GROUPID']);
				if ($_G['USERGROUP']) {
					standardArray($_G['USERGROUP']);
					$_G['USER']['USERGROUPNAME'] = $_G['USERGROUP']['USERGROUPNAME'];
					$_G['USER']['READLEVEL'] = $_G['USERGROUP']['READLEVEL'];
					$_G['USER']['USERGROUPQUANXIAN'] = $_G['USERGROUP']['QUANXIAN'];
					$data = json_decode($_G['USERGROUP']['DATA'], TRUE);
					foreach ($data as $key => $value) {
						$_G['USER']['DATA'] = JsonData($_G['USER']['DATA'], $key, $value);
					}
				}
			} else {
				$_G['USERGROUP'] = FALSE;
			}
		}
	}
	unset($ud);
}

$chkr = FALSE;
if ((!InArray($_G['USERGROUP']['QUANXIAN'], 'download') && $_G['USERGROUP']['ID']) || (!InArray($_G['USER']['QUANXIAN'], 'download') && !$_G['USERGROUP']['ID'])) {
	$chkr = '您无权下载文件';
}

if (!$chkr && !$id) {
	$chkr = '非法的下载ID';
}

if (!$chkr) {
	$downloaddata = $_G['TABLE']['UPLOAD'] -> getData($id);
	if (!$downloaddata)
		$chkr = '不存在的下载记录';
}

//判断是否为旧版数据
if (!json_decode($downloaddata['downloadeduids']) && $downloaddata['downloadeduids']) {
	//旧数据转为新数据并保存
	$olddata = explode('__', substr($downloaddata['downloadeduids'], 1, strlen($downloaddata['downloadeduids']) - 2));
	$downloaddata['downloadeduids'] = array();
	foreach ($olddata as $key => $value) {
		$downloaddata['downloadeduids']["uid_{$value}"] = time();
	}
	$downloaddata['downloadeduids'] = json_encode($downloaddata['downloadeduids']);
	$_G['TABLE']['UPLOAD'] -> newData(array('id' => $downloaddata['id'], 'downloadeduids' => $downloaddata['downloadeduids']));
}

$filepath = $_G['SYSTEM']['PATH'] . "/uploadfiles/{$downloaddata['target']}s/{$downloaddata['uid']}/" . substr($downloaddata['datetime'], 0, 8) . "/" . substr($downloaddata['datetime'], 8) . "_{$downloaddata['rand']}.{$downloaddata['suffix']}";

if (!$chkr && !file_exists($filepath) && $downloaddata['target'] != 'remote') {
	$chkr = '不存在的文件';
}

if (!$chkr && (Cnum($_G['USER']['JIFEN']) < $downloaddata['jifen'] || Cnum($_G['USER']['TIANDOU']) < $downloaddata['tiandou']) && $_SESSION['APP_PUYUETIANEDITOR_DOWNLOADSESSION_' . $_G['USER']['ID'] . '_' . $downloaddata['id']] != 'ok' && ((!OrderDownloadAttachment(array('attachmentdata' => $downloaddata)) && $_G['SET']['DOWNLOADEDRECORD']) || !$_G['SET']['DOWNLOADEDRECORD'])) {
	$chkr = '账户余额不足，无法下载';
}

//用户下载记录
$downloadarray = array();
if ($_G['TABLE']['DOWNLOAD_RECORD']) {
	$downloadarray['downloadid'] = $id;
	$downloadarray['uid'] = $_G['USER']['ID'];
	$downloadarray['datetime'] = date('YmdHis');
	$downloadarray['tiandou'] = 0;
}

if ($chkr) {
	PkPopup('{icon:2,content:"' . $chkr . '",shade:1,hideclose:1,submit:function(){window.close()}}');
}

//下载积分扣除
if ($_SESSION['APP_PUYUETIANEDITOR_DOWNLOADSESSION_' . $_G['USER']['ID'] . '_' . $downloaddata['id']] != 'ok' && ($downloaddata['tiandou'] || $downloaddata['jifen']) && ((!OrderDownloadAttachment(array('attachmentdata' => $downloaddata)) && $_G['SET']['DOWNLOADEDRECORD']) || !$_G['SET']['DOWNLOADEDRECORD'])) {
	//下载人扣除积分
	UserDataChange(array('tiandou' => $downloaddata['tiandou'], 'jifen' => $downloaddata['jifen']), $_G['USER']['ID'], '-');
	//上传人增加积分
	UserDataChange(array('tiandou' => $downloaddata['tiandou'], 'jifen' => $downloaddata['jifen']), $downloaddata['uid']);
	$downloadarray['tiandou'] = $downloaddata['tiandou'];
	//记录本客户端已下载过
	$_SESSION['APP_PUYUETIANEDITOR_DOWNLOADSESSION_' . $_G['USER']['ID'] . '_' . $downloaddata['id']] = 'ok';
}
//记录该用户下载过
if ($_G['TABLE']['DOWNLOAD_RECORD']) {
	$_G['TABLE']['DOWNLOAD_RECORD'] -> newData($downloadarray);
}
if ($_G['SET']['DOWNLOADEDRECORD'] && !OrderDownloadAttachment(array('attachmentdata' => $downloaddata))) {
	$_savedata = array('id' => $downloaddata['id'], 'downloadcount' => Cnum($downloaddata['downloadcount']) + 1, 'downloadeduids' => SaveDownloadRecord(array('attachmentdata' => $downloaddata)));
} else {
	$_savedata = array('id' => $downloaddata['id'], 'downloadcount' => Cnum($downloaddata['downloadcount']) + 1);
}
$_G['TABLE']['UPLOAD'] -> newData($_savedata);
$filename = preg_replace('#[\/\\\:\*\?\"\|\<\>]+#', '', substr($downloaddata['name'], 0, 128));
if (!$filename) {
	$filename = date('YmdHis');
}
if ($downloaddata['target'] == 'remote' && $downloaddata['url']) {
	ExitGourl($downloaddata['url']);
}
if (InArray('pdf,txt', $downloaddata['suffix']) && $_G['GET']['PREVIEW'] && $_G['SET']['DOWNLOADFILEPREVIEW']) {
	switch ($downloaddata['suffix']) {
		case 'pdf' :
			header('Content-type: application/pdf');
			break;
		case 'txt' :
			header('Content-type: text/plain');
			break;
	}
	echo file_get_contents($filepath);
	exit();
}

file_download($filepath, $filename);

exit();
