<?php
if (!defined('puyuetian'))
	exit('403');

$uid = $_G['USER']['ID'];
if (InArray(getUserQX(), 'admin,superadmin')) {
	$uid = Cnum($_POST['uid'], $_G['USER']['ID'], TRUE, 1);
}
$readdata = array();
$readdata['uid'] = $uid;
$readuserdata = $_G['TABLE']['USER'] -> getData($readdata['uid']);
$readdata['id'] = 0;
$readdata['sortid'] = Cnum($_POST['sortid'], FALSE, TRUE, 1);
$readdata['label'] = htmlspecialchars($_POST['label']);
$readdata['title'] = htmlspecialchars(strip_tags(trim(Cstr($_POST['title'], FALSE, FALSE, Cnum($_G['SET']['READTITLEMIN'], 3), Cnum($_G['SET']['READTITLEMAX'], 255))), ''), ENT_QUOTES);
$readdata['content'] = newBBcode(trim(Cstr($_POST['content'], FALSE, FALSE, Cnum($_G['SET']['READCONTENTMIN'], 10), Cnum($_G['SET']['READCONTENTMAX'], 25000))), 'read', $readuserdata['quanxian'], $readuserdata['data'], '');
$readdata['posttime'] = time();
$readdata['looknum'] = $readdata['zannum'] = $readdata['fs'] = 1;
$bkdata = $_G['TABLE']['READSORT'] -> getData($readdata['sortid']);
if ($bkdata) {
	$readdata['content'] = '<div style="padding:10px 0;font-weight:bold"><i class="fa fa-fw fa-hand-o-right"></i>该文章发布于“<a target="_blank" href="' . ReWriteURL('forum', "id={$bkdata['id']}") . '">' . $bkdata['title'] . '</a>”版块</div>' . $readdata['content'];
}

$_G['SET']['WEBKEYWORDS'] = strip_tags($readdata['title']);
$_G['SET']['WEBDESCRIPTION'] = "{$_G['SET']['WEBKEYWORDS']}，{$_G['SET']['WEBADDEDWORDS']}";
$_G['SET']['WEBTITLE'] = "{$_G['SET']['WEBKEYWORDS']} - 文章预览模式 - {$bkdata['title']} - {$_G['SET']['WEBADDEDWORDS']}";

$_G['HTMLCODE']['OUTPUT'] .= template('read-1', TRUE);
$_G['HTMLCODE']['OUTPUT'] .= template('read-3', TRUE);
