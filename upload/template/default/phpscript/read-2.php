<?php
if (!defined('puyuetian'))
	exit('403');

global $replyuserdata, $replyuserhtml, $replydata, $bkdata;
$replyuserhtml = '';
if ($replyuserdata['sex'] == 'b' || $replyuserdata['sex'] == '男') {
	$replyuserhtml .= '&nbsp;<span class="fa fa-mars pk-text-secondary" title="Boy"></span>';
} elseif ($replyuserdata['sex'] == 'g' || $replyuserdata['sex'] == '女') {
	$replyuserhtml .= '&nbsp;<span class="fa fa-venus pk-text-danger" title="Girl"></span>';
} else {
	$replyuserhtml .= '&nbsp;<span class="fa fa-intersex pk-text-default" title="Demon"></span>';
}
if ($replyuserdata['id'] == 1) {
	$replyuserhtml .= '&nbsp;<span class="fa fa-user-secret pk-text-warning" title="创始人"></span>';
}
if (InArray(getUserQX($replyuserdata['id']), 'admin,superadmin')) {
	$replyuserhtml .= '&nbsp;<span class="fa fa-user pk-text-success" title="管理员"></span>';
}
if ($replydata['top']) {
	$replyuserhtml .= '&nbsp;<span class="pk-badge pk-background-color-danger pk-text-xs pk-radius-4 pk-cursor-default"> 置顶 </span>';
}

global $replydata, $readuserdata, $lgtime;
$lgtime = time() - Cnum($replydata['posttime']);
if ($lgtime < 60) {
	$lgtime = '刚刚';
} elseif ($lgtime < 3600) {
	$lgtime = (int)($lgtime / 60) . '分钟前';
} elseif ($lgtime < 86400) {
	$lgtime = (int)($lgtime / 3600) . '小时前';
} elseif ($lgtime < 2592000) {
	$lgtime = (int)($lgtime / 86400) . '天前';
} else {
	$lgtime = date('Y-m-d H:i:s', $replydata['posttime']);
}