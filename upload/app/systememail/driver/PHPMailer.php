<?php
if (!defined('puyuetian'))
	exit('403');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require $_G['SYSTEM']['PATH'] . 'app/systememail/driver/PHPMailer/src/Exception.php';
require $_G['SYSTEM']['PATH'] . 'app/systememail/driver/PHPMailer/src/PHPMailer.php';
require $_G['SYSTEM']['PATH'] . 'app/systememail/driver/PHPMailer/src/SMTP.php';

function sendmail($mailto, $mailtitle, $mailcontent, $timeout = false) {
	if (!filter_var($mailto, FILTER_VALIDATE_EMAIL)) {
		return FALSE;
	}
	global $_G;
	//部分应用的补救方案，未开启邮件功能，但会发信
	if (!$_G['SET']['APP_SYSTEMEMAIL_LOAD']) {
		return FALSE;
	}
	//开启了超时阀值
	if (Cnum($_G['SET']['APP_SYSTEMEMAIL_TIMEOUTSECONDS'], FALSE, TRUE, 1)) {
		if (!$timeout) {
			$urls = 'http' . ($_SERVER['HTTPS'] == 'on' ? 's' : '') . "://{$_G['SYSTEM']['DOMAIN']}" . ($_G['SYSTEM']['PORT'] ? ':' . $_G['SYSTEM']['PORT'] : '') . "{$_SERVER['PHP_SELF']}";
			$urls = explode('/', $urls);
			$url = '';
			foreach ($urls as $k => $v) {
				if ($k != count($urls) - 1) {
					$url .= '/' . $v;
				}
			}
			$url = substr($url, 1) . '/index.php?c=app&a=systememail:_sendmail';
			$url .= '&safecode=' . md5($_G['SET']['KEY']);
			$json = GetPostData($url, 'mailto=' . urlencode($mailto) . '&mailtitle=' . urlencode($mailtitle) . '&mailcontent=' . urlencode($mailcontent), $_G['SET']['APP_SYSTEMEMAIL_TIMEOUTSECONDS']);
			$json = json_decode($json, TRUE);
			if ($json['state'] == 'ok') {
				return TRUE;
			} else {
				return FALSE;
			}
		}
	}
	$mail = new PHPMailer();
	try {
		$mail -> isSMTP();
		$mail -> SMTPDebug = 0;
		$mail -> Host = $_G['SET']['APP_SYSTEMEMAIL_SMTP'];
		$mail -> Port = Cnum($_G['SET']['APP_SYSTEMEMAIL_PORT']) ? $_G['SET']['APP_SYSTEMEMAIL_PORT'] : 25;
		$mail -> SMTPAuth = true;
		$mail -> Username = $_G['SET']['APP_SYSTEMEMAIL_USER'];
		$mail -> Password = $_G['SET']['APP_SYSTEMEMAIL_PASS'];
		$mail -> CharSet = 'utf-8';
		$mail -> setFrom($mail -> Username, $_G['SET']['LOGOTEXT']);
		$mail -> addAddress($mailto, '');
		$mail -> isHTML(true);
		$mail -> Subject = $mailtitle;
		$mail -> Body = $mailcontent;
		$mail -> send();
	} catch (Exception $e) {
		if ($_G['SET']['APP_SYSTEMEMAIL_DEBUG']) {
			NewMessage(1, '邮箱调试信息：' . print_r($mail -> ErrorInfo, TRUE), 0, 2);
		}
		return FALSE;
	}
	return true;
}
