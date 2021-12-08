<?php
if (!defined('puyuetian'))
	exit('403');

$c = $_G['GET']['C'];
if ($c == 'preview') {
	$c = 'read';
}
if (InArray('edit,read', $c) && $_G['GET']['PYTEDITORLOAD'] != 'no') {
	$_G['TEMP']['PC'] = $_G['SET']['APP_PUYUETIANEDITOR_PC' . strtoupper($c) . 'CONFIG'];
	$_G['TEMP']['PHONE'] = $_G['SET']['APP_PUYUETIANEDITOR_PHONE' . strtoupper($c) . 'CONFIG'];
	$_G['TEMP']['CONFIG'] = $_G['SET']['APP_PUYUETIANEDITOR_' . strtoupper($c) . 'CONFIG'];
	$_G['SET']['EMBED_FOOT'] .= template('puyuetianeditor:embed', TRUE);
	unset($_G['TEMP']['PC'], $_G['TEMP']['PHONE'], $_G['TEMP']['CONFIG']);
}
