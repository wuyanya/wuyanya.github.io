<?php
if (!defined('puyuetian'))
	exit('403');

$page = Cnum($_G['GET']['PAGE'], 1, TRUE, 1);
$syy = $page - 1;
$xyy = $page + 1;
$prenum = Cnum($_G['SET']['READLISTNUM'], 10, TRUE, 1);
$spos = ($page - 1) * $prenum;
if (!$_G['USER']['ID']) {
	ExitGourl(ReWriteURL('login'));
}
if (!$_G['USER']['IDOL']) {
	PkPopup('{content:"您暂未关注任何人",icon:0,shade:1,hideclose:1,nomove:1}');
}
$idols = explode('__', substr($_G['USER']['IDOL'], 1, strlen($_G['USER']['IDOL']) - 2));
$sql = ' and (';
foreach ($idols as $v) {
	if (!Cnum($v, FALSE, TRUE, 1)) {
		continue;
	}
	$sql .= '`uid`=\'' . $v . '\' or ';
}
if ($sql == ' and (') {
	PkPopup('{content:"您暂未关注任何人2",icon:0,shade:1,hideclose:1,nomove:1}');
}
$sql = substr($sql, 0, -4) . ')';
if ($_GET['label']) {
	//安全处理
	$label = '';
	$labels = array_unique(explode(',', $_GET['label']));
	$sqllabel = ' and (';
	$i = 0;
	foreach ($labels as $value) {
		if ($value) {
			$sqllabel .= '`label` like ' . mysqlstr(preg_replace('/[\"\']+/', '', strip_tags($value)), TRUE, '%', TRUE) . ' or ';
		}
		$i++;
		if ($i > 9) {
			break;
		}
	}
	if ($sqllabel != ' and (') {
		$sqllabel = substr($sqllabel, 0, -4) . ')';
	} else {
		$sqllabel = '';
	}
}
if ($sqllabel) {
	$sql .= ' ' . $sqllabel;
}
$template = template('list-2', TRUE, FALSE, FALSE);
$normalreadhtml = '';
$readdatas = $_G['TABLE']['READ'] -> getDatas($spos, $prenum, 'where `del`=0' . $sql . ' order by `id` desc');
$readcount = $_G['TABLE']['READ'] -> getCount('where `del`=0' . $sql);
$pagecount = Cnum(ceil($readcount / $prenum), 1, TRUE, 1);
if ($page > $pagecount) {
	$page = $pagecount;
}
foreach ($readdatas as $readdata) {
	//该文章的版块信息
	if ($sortid) {
		$readsortdata = $forumdata;
	} else {
		$readsortdata = $_G['TABLE']['READSORT'] -> getData($readdata['sortid']);
	}
	//检测是否为回复查看帖
	if ($readdata['replyafterlook']) {
		if (!$_G['TABLE']['REPLY'] -> getId(array('rid' => $readdata['id'], 'uid' => $_G['USER']['ID'], 'del' => 0))) {
			$readdata['content'] = '该文章设置了回复查看，请回复后查看内容';
		}
	}
	//部分内容回复后可见
	$readdata['content'] = preg_replace('/\<p class="PytReplylook"\>[\s\S]+?\<\/p\>/', '<p>隐藏内容</p>', $readdata['content']);
	//检测阅读权限是否合法
	if ((($_G['USERGROUP']['ID'] && Cnum($readdata['readlevel']) > Cnum($_G['USERGROUP']['READLEVEL'])) || (!$_G['USERGROUP']['ID'] && Cnum($readdata['readlevel']) > Cnum($_G['USER']['READLEVEL']))) || !chkReadSortQx($sortid, 'readlevel')) {
		$readdata['content'] = '您的阅读权限太低或您的用户组不被允许';
	}
	if ($readdata['uid']) {
		$readuserdata = $_G['TABLE']['USER'] -> getData($readdata['uid']);
	} else {
		$readuserdata = JsonData($_G['SET']['GUESTDATA']);
	}
	$normalreadhtml .= template('list-2', TRUE, $template);
}
$_G['SET']['WEBTITLE'] = '我的关注动态';
$_G['HTMLCODE']['OUTPUT'] .= template('list-1', TRUE);
$_G['HTMLCODE']['OUTPUT'] .= $normalreadhtml;
$_G['HTMLCODE']['OUTPUT'] .= template('list-3', TRUE);
