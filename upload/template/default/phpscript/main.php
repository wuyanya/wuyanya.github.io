<?php
if (!defined('puyuetian'))
	exit('403');

if ($_G['SET']['GLOBALCDN']) {
	$_G['SET']['EMBED_HEADMARKS'] = '
	<link href="https://cdn.hadsky.com/resource/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" />
	<script src="https://cdn.hadsky.com/resource/jquery/3.3.1/jquery.min.js"></script>
	<!--[if lt IE 9]>
		<script src="https://cdn.hadsky.com/resource/respond.js/1.4.2/respond.min.js"></script>
	<![endif]-->
	<link href="https://cdn.hadsky.com/resource/puyuetian/7.x.x/css/puyuetian.min.css" rel="stylesheet" />
	<script src="https://cdn.hadsky.com/resource/puyuetian/7.x.x/js/puyuetian.min.js"></script>
	' . $_G['SET']['EMBED_HEADMARKS'];
} else {
	$_G['SET']['EMBED_HEADMARKS'] = '
	<link rel="stylesheet" href="template/puyuetianUI/css/font-awesome.min.css" />
	<script src="template/puyuetianUI/js/jquery-3.3.1.min.js"></script>
	<!--[if lt IE 9]>
		<script src="template/puyuetianUI/js/respond.js"></script>
	<![endif]-->
	<link rel="stylesheet" href="template/puyuetianUI/css/puyuetian.css" />
	<script src="template/puyuetianUI/js/puyuetian.js"></script>
	' . $_G['SET']['EMBED_HEADMARKS'];
}
