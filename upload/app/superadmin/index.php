<?php
if (!defined('puyuetian'))
	exit('403');

require $_G['SYSTEM']['PATH'] . 'app/superadmin/function.php';

if (!$_G['USER']['ID'] || ($_G['USER']['ID'] != 1 && !InArray($_G['SET']['APP_SUPERADMIN_ADMINUIDS'], $_G['USER']['ID']))) {
	if ($_G['GET']['S']) {
		echo '<script>top!=self?top.location.reload(true):location.href="index.php?c=login&referer=' . urlencode('index.php?c=app&a=superadmin:index') . '"</script>';
	} else {
		header('Location:index.php?c=login&referer=' . urlencode($_G['SYSTEM']['LOCATION']));
	}
	exit();
}

if ($_G['SET']['APP_SUPERADMIN_PASSWORD']) {
	//开启了安全验证
	if ($_G['GET']['HADSKY_SUPERADMIN_CHKPASSWORD']) {
		$ps = $_POST['hadsky_superadmin_password'];
		$_SESSION['APP_SUPERADMIN_PASSWORD'] = $ps;
		ExitJson('校验完成', $ps == $_G['SET']['APP_SUPERADMIN_PASSWORD']);
	}
	if ($_SESSION['APP_SUPERADMIN_PASSWORD'] != $_G['SET']['APP_SUPERADMIN_PASSWORD']) {
		PkPopup('{
			title:"请输入后台密码",
			type:2,
			shade:1,
			nomove:1,
			noclose:1,
			hideclose:1,
			submit:function(_id,_value){
				if(_value==""){
					$("#pkpopup_"+_id+" .pk-popup-input").focus();
					return false;
				}
				var pid=ppp({type:4,shade:1,content:"正在校验..."});
				$.post("index.php?c=app&a=superadmin&hadsky_superadmin_chkpassword=1",{
					hadsky_superadmin_password:_value
				},function(data){
					pkpopup.close(pid);
					if(data["state"]=="ok"){
						location.reload();
					}else{
						ppp({type:3,icon:2,content:"密码错误",close:function(){
							$("#pkpopup_"+_id+" .pk-popup-input").val("").focus();
						}});
					}
				});
			},
			complete:function(_id){
				$("#pkpopup_"+_id+" .pk-popup-input").on("keydown",function(e){
					if(e.keyCode==13){
						$("#pkpopup_"+_id+" .pk-popup-submit").click();
					}
				}).attr("type","password");
				$("#pkpopup_"+_id+" .pk-popup-submit").html("进入");
				$("#pkpopup_"+_id+" .pk-popup-cancel").html("关闭");
			},
			cancel:function(){
				window.close();
			}
		}');
	}
}

$wd = date('H:i:s') . "\t{$_G['USER']['ID']}\t{$_G['SYSTEM']['LOCATION']}\r\n";
$fp = "{$_G['SYSTEM']['PATH']}app/superadmin/logs/" . date('Y-m-d') . '_' . substr(md5($_G['SET']['KEY']), 0, 7) . '.txt';
if (!file_exists($fp)) {
	file_put_contents($fp, "OperatingTime\tUID\tRequestURI\r\n", FILE_APPEND);
}
//记录日志
file_put_contents($fp, $wd, FILE_APPEND);
unset($wd, $fp);

$_G['TEMP']['UID'] = $_G['USER']['ID'];
$_G['USER']['ID'] = 1;

//安全版本检测码
$nowversion = explode('.', HADSKY_VERSION);
$nowversion2 = '';
foreach ($nowversion as $value) {
	if ($value < 10) {
		$value = '0' . $value;
	}
	$nowversion2 .= $value;
}
$nowversion = $nowversion2;
//云通讯检验码生成
$yccc = TRUE;
$YCCP = $_G['SYSTEM']['PATH'] . '/yuncheckcode.htm';
if (file_exists($YCCP)) {
	$lscc = json_decode(file_get_contents($YCCP), TRUE);
	if ($lscc['deadline'] > time()) {
		$YUNCHECKCODE = $lscc['yuncheckcode'];
		$yccc = FALSE;
	}
}
if ($yccc) {
	$YUNCHECKCODE = CreateRandomString(16);
	file_put_contents($YCCP, json_encode(array('yuncheckcode' => $YUNCHECKCODE, 'deadline' => (time() + 300))));
}

$_G['TEMPLATE']['HEAD'] = 'null';
$_G['TEMPLATE']['FOOT'] = 'null';

$safepages = 'save,delete,savecache,saveips,moveread';
$currentscriptname = strtolower($_G['GET']['S']);
if ($currentscriptname) {
	$PP = $_G['SYSTEM']['PATH'] . '/app/superadmin/phpscript/' . $currentscriptname . '.php';

	if (file_exists($PP)) {
		if (InArray($safepages, $currentscriptname) && $_G['GET']['CHKCSRF']) {
			if ($_G['CHKCSRFVAL'] != $_POST['chkcsrfval'] && $_G['CHKCSRFVAL'] != $_GET['chkcsrfval']) {
				exit('Has been blocked by HadSky security mechanism, the reason: suspected CSRF attacks');
			}
		}
		$_G['TEMPLATE']['MAIN'] = 'superadmin:main-output';
		require $PP;
	} else {
		RunError("\"{$PP}\" doesn't exist");
	}
} else {
	$_G['TEMPLATE']['BODY'] = 'superadmin:main';
}
