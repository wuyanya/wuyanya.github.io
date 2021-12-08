<?php
if (!defined('puyuetian'))
	exit('403');
/*
 * 有天轻论坛插件
 * 邮件群发插件v1.0
 * 作者：蒲乐天
 */

if ($_G['USER']['ID'] != 1) {
	PkPopup('{content:"您无权使用该插件",shade:1,icon:2,nomove:1,hideclose:1,submit:function(){location.href="index.php"}}');
}
if (isset($_POST['mailtotype'])) {
	$mailtotype = $_POST['mailtotype'];
	$mailto = $_POST['mailto'];
	$mailtitle = $_POST['mailtitle'];
	$mailcontent = $_POST['mailcontent'];
	$__i = 0;
	if (!$mailtitle || !$mailcontent) {
		ExitJson('标题和内容不能为空');
	}
	if ($mailtotype == 'all') {
		//发送给全站会员
		$ua = $_G['TABLE']['USER'] -> getDatas(0, 500, 'where `email`<>\'\' order by `id` desc', FALSE, 'email');
		foreach ($ua as $ua2) {
			if (sendmail($ua2['email'], $mailtitle, $mailcontent)) {
				$__i++;
			}
		}
		ExitJson("成功发送{$__i}封邮件", TRUE);
	} elseif ($mailtotype == 'more' && $mailto) {
		$__mailto = explode(',', $mailto);
		foreach ($__mailto as $__value) {
			if (sendmail($__value, $mailtitle, $mailcontent)) {
				$__i++;
			}
		}
		ExitJson("成功发送{$__i}封邮件", TRUE);
	} else {
		ExitJson('参数错误');
	}
}
$_G['SET']['WEBTITLE'] = '邮件群发应用';
$_G['HTMLCODE']['OUTPUT'] .= template('systememail:index', TRUE);
