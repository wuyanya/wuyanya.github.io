<?php
if (!defined('puyuetian'))
	exit('403');

global $readdata, $readuserdata, $readuserhtml;
$readuserhtml = '';
if ($readdata['high']) {
	$readdata['title'] = '<span title="精华" class="fa fa-diamond pk-text-primary"></span> ' . $readdata['title'];
}
if ($readdata['top']) {
	$readdata['title'] = '<span title="置顶" class="fa fa-arrow-up pk-text-danger"></span> ' . $readdata['title'];
}
if ($readuserdata['sex'] == 'b' || $readuserdata['sex'] == '男') {
	$readuserhtml .= '&nbsp;<span class="fa fa-mars pk-text-secondary" title="Boy"></span>';
} elseif ($readuserdata['sex'] == 'g' || $readuserdata['sex'] == '女') {
	$readuserhtml .= '&nbsp;<span class="fa fa-venus pk-text-danger" title="Girl"></span>';
} else {
	$readuserhtml .= '&nbsp;<span class="fa fa-intersex pk-text-default" title="Demon"></span>';
}
if ($readuserdata['id'] == 1) {
	$readuserhtml .= '&nbsp;<span class="fa fa-user-secret pk-text-warning" title="创始人"></span>';
}
if (InArray(getUserQX($readuserdata['id']), 'admin,superadmin')) {
	$readuserhtml .= '&nbsp;<span class="fa fa-user pk-text-success" title="管理员"></span>';
}