<?php
if (!defined('puyuetian'))
	exit('403');
$_G['SQL']['TYPE'] = 'sqlite';
$_G['SQL']['LOCATION'] = '';
$_G['SQL']['USERNAME'] = 'root';
$_G['SQL']['PASSWORD'] = '';
$_G['SQL']['DATABASE'] = '';
$_G['SQL']['CHARSET'] = 'set names utf8';
$_G['SQL']['PREFIX'] = 'pk_';
$_G['MYSQL']['LOCATION'] = $_G['SQL']['LOCATION'];
$_G['MYSQL']['USERNAME'] = $_G['SQL']['USERNAME'];
$_G['MYSQL']['PASSWORD'] = $_G['SQL']['PASSWORD'];
$_G['MYSQL']['DATABASE'] = $_G['SQL']['DATABASE'];
$_G['MYSQL']['CHARSET'] = $_G['SQL']['CHARSET'];
$_G['MYSQL']['PREFIX'] = $_G['SQL']['PREFIX'];