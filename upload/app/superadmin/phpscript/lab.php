<?php
if (!defined('puyuetian'))
	exit('403');

if ($_G['GET']['T']) {
	$contenthtml = template('superadmin:lab-' . $_G['GET']['T'], TRUE);
} else {
	$contenthtml = template('superadmin:lab-set', TRUE);
}
