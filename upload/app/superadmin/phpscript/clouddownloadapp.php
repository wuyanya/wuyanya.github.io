<?php
if (!defined('puyuetian'))
	exit('403');

$_G['TEMPLATE']['HEAD'] = $_G['TEMPLATE']['FOOT'] = 'null';
$contenthtml = template('superadmin:clouddownloadapp', TRUE);
