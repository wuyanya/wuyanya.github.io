<?php
if (!defined('puyuetian'))
	exit('403');

$_G['TEMP']['BKDATAS'] = $_G['TABLE']['READSORT'] -> getDatas(0, 0, 'order by `rank`');
$_G['TEMP']['BKDATAS'] = htmlspecialchars(json_encode($_G['TEMP']['BKDATAS']), ENT_QUOTES);

if (!$_G['GET']['T']) {
	$_G['GET']['T'] = 'edit';
}
if ($_G['GET']['T'] == 'edit') {
	if (Cnum($_G['GET']['ID']))
		$forumdata = HSCSArray($_G['TABLE']['READSORT'] -> getData($_G['GET']['ID']));
	$contenthtml = template('superadmin:forum-edit', TRUE);
} else {
	$contenthtml = template('superadmin:forum-' . $_G['GET']['T'], TRUE);
}
