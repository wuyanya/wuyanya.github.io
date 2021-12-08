<?php
if (!defined('puyuetian')) {
	exit('403');
}

global $readdata, $replyuserdata, $replydata, $imgshtml, $lgtime;
if ($readdata['high']) {
	$readdata['title'] = '<span title="精华" class="fa fa-diamond pk-text-primary"></span> ' . $readdata['title'];
}
if ($readdata['top']) {
	$readdata['title'] = '<span title="置顶" class="fa fa-arrow-up pk-text-danger"></span> ' . $readdata['title'];
}
$readdata['olddata']['content'] = $readdata['content'];
if ($replyuserdata['id'] && $_G['SET']['TEMPLATE_DEFAULT_LISTCONTENTTYPE']) {
	$readdata['content'] = "{$replyuserdata['nickname']}：" . EqualReturn(strip_tags($replydata['content'], ''), '', '[Image]');
}
//图片加载
$i = 0;
$_G['TEMP']['IMGS'] = '';
$imgshtml = array();
$noimglist = 'emotion';
if (preg_match_all('#<img.*?src="(.*?)".*?alt="(.*?)".*?\>#', $readdata['content'], $match)) {
	foreach ($match[1] as $key => $value) {
		if (!InArray($noimglist, $match[2][$key])) {
			if ($i > 2) {
				break;
			}
			$i++;
			$imgshtml[$i] = '<div class="pk-w-sm-(-i-) pk-overflow-hidden pk-text-center" style="max-height:180px"><img class="ImageLoading pk-max-width-all pk-max-height-all pk-cursor-pointer" src="' . $value . '" alt="" onclick="LookImage(this)" /></div>';
		}
	}
	$i = count($imgshtml);
	if ($i) {
		$i = 12 / $i;
		foreach ($imgshtml as $value) {
			$_G['TEMP']['IMGS'] .= str_replace('(-i-)', $i, $value);
		}
	}
}
//发表时间人性化
$readlistorder = Cstr($_G['SET']['READLISTORDER'], 'activetime', true, 1, 255);
if ($readlistorder != 'posttime') {
	$readlistorder = 'activetime';
}
$lgtime = time() - Cnum($readdata[$readlistorder]);
if ($lgtime < 60) {
	$lgtime = '刚刚';
} elseif ($lgtime < 3600) {
	$lgtime = (int)($lgtime / 60) . '分钟前';
} elseif ($lgtime < 86400) {
	$lgtime = (int)($lgtime / 3600) . '小时前';
} else {
	$lgtime = (int)($lgtime / 86400) . '天前';
}