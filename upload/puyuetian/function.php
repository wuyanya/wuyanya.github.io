<?php
if (!defined('puyuetian')) {
	exit('Not Found puyuetian!Please contact QQ632827168');
}
/*
 * puyuetianPHP轻框架 核心函数
 * 作者：蒲乐天（puyuetian）
 * QQ：632827168
 * 官网：http://www.puyuetian.com
 *
 * 作者允许您转载和使用，但必须注明来自puyuetianPHP轻框架。
 */

/********************************************
 puyuetianPHP框架基础防火墙部分，因部分插件脱离puyuetian.php文件，所以在hs中将防火墙文件移至function.php文件中
 *******************************************/
require $_G['SYSTEM']['PATH'] . 'puyuetian/firewall/firewall.php';

//执行当前数据库语句，$returnFormat输出格式：0默认返回pdo对象，1仅返回找到的第一个值，2输出所有数据为数组
function sqlQuery($sql, $returnFormat = 0) {
	global $_G;
	$r = $_G['PDO'] -> query($sql);
	if (!$returnFormat) {
		return $r;
	}
	if (!$r) {
		return false;
	}
	$array = array();
	$index = 0;
	while ($querya = $r -> fetch(PDO::FETCH_ASSOC)) {
		if (InArray('1,onlyone', $returnFormat)) {
			foreach ($querya as $value) {
				return $value;
			}
		}
		$array[$index] = $querya;
		$index++;
	}
	if (InArray('2,array', $returnFormat)) {
		return $array;
	}
	return false;
}

function sqlError() {
	global $_G;
	$array = $_G['PDO'] -> errorInfo();
	return $array[2];
}

//返回整数或小数数字，$str待处理字符，$return若非法返回的值，$int是否为整数（默认整数），$min数字最小值，$max数字最大值
function Cnum($str, $return = 0, $int = true, $min = false, $max = false) {
	if (is_numeric($str)) {
		if ($int) {
			if (function_exists('bcadd')) {
				$str = bcadd($str, 0, 0);
			} else {
				$str = number_format($str, 0, '.', '');
			}
		}
	} else {
		$str = $return;
	}
	if ($min !== false) {
		if ($str < $min) {
			$str = $return;
		}
	}
	if ($max !== false) {
		if ($str > $max) {
			$str = $return;
		}
	}

	return $str;
}

//返回符合条件的字符串，$str待处理字符，$return若非法返回值，$cstr字符处理白名单，$minlen最小长度，$maxlen最大长度
function Cstr($str, $return = false, $cstr = true, $minlen = 3, $maxlen = 15) {
	if (!is_string($str)) {
		return $return;
	}
	if ($cstr === true) {
		global $_G;
		$cstr = $_G['STRING']['SAFECHARS'];
	}
	$len = strlen($str);
	if ($cstr) {
		for ($i = 0; $i < $len; $i++) {
			$chk = strpos($cstr, substr($str, $i, 1));
			if ($chk === false) {
				return $return;
			}
		}
	}
	if ($minlen < $maxlen) {
		if ($len < $minlen) {
			return $return;
		}
		if ($len > $maxlen) {
			return $return;
		}
	} elseif ($minlen == $maxlen) {
		if ($len != $maxlen) {
			return $return;
		}
	}
	return $str;
}

//bbcode函数,若数据库设置了过滤标签则使用数据库的标签，否则使用系统默认标签，$marks保留的标签，$attrs保留的标签属性
function BBcode($str, $marks = false, $attrs = false) {
	global $_G;
	if ($marks === false) {
		$_G['SET']['BBCODEMARKS'] ? $marks = $_G['SET']['BBCODEMARKS'] : $marks = $_G['STRING']['BBCODEMARKS'];
	}
	//第一次过滤
	$str = strip_tags($str, $marks);
	//第二次过滤
	if ($marks) {
		if (preg_match_all('/\<([\s\S]+?)\>/', $str, $match)) {
			foreach ($match[1] as $value2) {
				//获取当前标签名
				$bqn = substr($value2, 0, strpos($value2, ' '));
				//去除标签名
				$value2 = substr($value2, strlen($bqn));
				//处理前的初始化数据，转小写，去空格，去/
				$value2 = str_replace(' ', '', strtolower($value2));
				//$value2 = str_replace('/', '', $value2);
				//检测是否为无元素标签
				if (strpos($marks, '<' . str_replace('/', '', $value2) . '>') === false) {
					if ($attrs === false) {
						$_G['SET']['BBCODEATTRS'] ? $attrs = $_G['SET']['BBCODEATTRS'] : $attrs = $_G['STRING']['BBCODEATTRS'];
					}
					$wms = explode(',', $attrs);
					$chkstr = $value2;
					//清除白名单数据
					foreach ($wms as $wm) {
						if ($wm == 'href') {
							//a标签特殊检测
							if (preg_match_all('/href="(.*?)"/', $chkstr, $hrefs)) {
								foreach ($hrefs[1] as $href) {
									//安全检测
									if (strpos(str_replace(array(" ", "　", "\r", "\n", "\t"), '', strtolower($href)), 'javascript:') !== false) {
										$str = htmlspecialchars($str);
										break 3;
									}
								}
							}
						}
						$chkstr = preg_replace('/' . $wm . '=".*?"/', '', $chkstr);
					}
					//处理后的检测
					if ($chkstr && $chkstr != '/') {
						$str = htmlspecialchars($str);
						break;
					}
				}
			}
		}
	}
	return $str;
}

//html静态模板加载函数,$filename模板名称或路径，$return是则返回/否则输出，$htmlcode模板的内容，$isreplace是否解析模板,loadphpscript是否加载模板对应的php脚本,readphpscript是否读取模板嵌入的php代码(如果存在对应的脚本文件，默认不读取)
function template($templatevar_filename, $templatevar_return = false, $templatevar_htmlcode = false, $templatevar_isreplace = true, $templatevar_loadphpscript = true, $templatevar_readphpscript = null) {
	global $_G;
	$templatevar_filename = str_replace('\\', '/', $templatevar_filename);
	$_G['SYSTEM']['LOADTEMPLATENAME'] = $templatevar_filename;
	$templatevar_templatename = $_G['SET']['TEMPLATENAME'];
	$templatevar_lj = str_replace('\\', '/', $_G['SYSTEM']['PATH']);
	$templatevar_cachecontent = '';
	//获取模板的绝对路径
	if (strpos($templatevar_filename, '/') !== false && substr($templatevar_filename, strrpos($templatevar_filename, '.') + 1) != 'php') {
		//完整路径
		$templatevar_templatefullpath = $templatevar_filename;
	} elseif (strpos($templatevar_filename, ':')) {
		//应用模板加载
		$templatevar_plugname = explode(':', $templatevar_filename);
		if (count($templatevar_plugname) != 2) {
			RunError('插件模板必须遵循“插件目录:模板文件”规则，出错值：' . $templatevar_plugname);
		}
		$templatevar_templatefullpath = $templatevar_lj . "app/{$templatevar_plugname[0]}/template/{$templatevar_plugname[1]}.hst";
		if (!file_exists($templatevar_templatefullpath)) {
			$templatevar_templatefullpath = $templatevar_lj . "app/{$templatevar_plugname[0]}/template/{$templatevar_plugname[1]}.html";
		}
	} else {
		//一般模板加载
		$templatevar_templatefullpath = $templatevar_lj . "template/{$templatevar_templatename}/{$templatevar_filename}.hst";
		if (!file_exists($templatevar_templatefullpath)) {
			$templatevar_templatefullpath = $templatevar_lj . "template/{$templatevar_templatename}/{$templatevar_filename}.html";
		}
		if (!file_exists($templatevar_templatefullpath)) {
			if ($_G['SET']['DEFAULTTEMPLATES']) {
				$templatevar_trs = explode(',', $_G['SET']['DEFAULTTEMPLATES']);
				foreach ($templatevar_trs as $templatevar_tr) {
					if (file_exists($templatevar_lj . "template/{$templatevar_tr}/{$templatevar_filename}.hst")) {
						$templatevar_templatefullpath = $templatevar_lj . "template/{$templatevar_tr}/{$templatevar_filename}.hst";
						break;
					} elseif (file_exists($templatevar_lj . "template/{$templatevar_tr}/{$templatevar_filename}.html")) {
						$templatevar_templatefullpath = $templatevar_lj . "template/{$templatevar_tr}/{$templatevar_filename}.html";
						break;
					}
				}
			}
		}
	}

	//======================php模板文件缓存,是否开启了该功能,并存在相应的缓存文件,并且缓存文件未过期 1==============================
	$templatevar_cache_open = $_G['SET']['PHPCACHE_OPEN'];
	if ($templatevar_htmlcode !== false) {
		$templatevar_cache_open = 0;
	}
	if ($templatevar_cache_open) {
		$templatevar_cachefilename = $_G['SYSTEM']['PATH'] . 'cache/php/' . md5($templatevar_templatefullpath) . '.php';
		//是否存在缓存,若存在则加载缓存文件
		if (file_exists($templatevar_cachefilename)) {
			$templatevar_a = Cnum($_G['SET']['PHPCACHE_TIMEOUT'], 0, true, 0) * 60;
			$templatevar_b = 1;
			if ($templatevar_a) {
				$templatevar_c = filemtime($templatevar_cachefilename);
				if ($templatevar_c + $templatevar_a > time()) {
					//缓存过期,需要重新创建缓存
					$templatevar_b = 0;
				}
			}
			if ($templatevar_b) {
				if ($templatevar_return) {
					//打开缓冲区
					ob_start();
					$templatevar_a =
					include ($templatevar_cachefilename);
					$templatevar_a = ob_get_contents();
					ob_end_clean();
					return $templatevar_a;
				}
				require $templatevar_cachefilename;
				return;
			}
		}
	}
	//======================php模板文件缓存,是否开启了该功能,并存在相应的缓存文件,并且缓存文件未过期 2==============================

	if ($templatevar_htmlcode === false) {
		if (file_exists($templatevar_templatefullpath)) {
			$templatevar_htmlcode = file_get_contents($templatevar_templatefullpath);
		} else {
			RunError("不存在该模板文件：\"{$templatevar_templatefullpath}\"");
		}
	}

	$templatevar_cache_htmlcode = $templatevar_htmlcode;

	if ($templatevar_isreplace) {
		//加载脚本文件
		$templatevar_templatefullphpscriptpath = '';
		if ($templatevar_loadphpscript) {
			//获取脚本文件名称
			$templatevar_a = explode('/', $templatevar_templatefullpath);
			for ($templatevar_b = 0; $templatevar_b < count($templatevar_a); $templatevar_b++) {
				if ($templatevar_b == (count($templatevar_a) - 1)) {
					$templatevar_templatefullphpscriptpath .= '/phpscript';
					$templatevar_templatefullphpscriptpath .= '/' . current(explode('.', $templatevar_a[$templatevar_b])) . '.php';
				} else {
					$templatevar_templatefullphpscriptpath .= '/' . $templatevar_a[$templatevar_b];
				}
			}
			$templatevar_templatefullphpscriptpath = substr($templatevar_templatefullphpscriptpath, 1);
			if (file_exists($templatevar_templatefullphpscriptpath)) {
				require $templatevar_templatefullphpscriptpath;
				//模板缓存写入
				if ($templatevar_cache_open) {
					$templatevar_cachecontent .= '<?php include \'' . $templatevar_templatefullphpscriptpath . '\'; ?>' . "\n";
				}
				if (!$templatevar_readphpscript) {
					$templatevar_readphpscript = false;
				}
			}
		}
		//模板内PHP脚本的执行
		if (($templatevar_readphpscript || $templatevar_readphpscript === null) && preg_match_all('#<\?php[\s\S]+?\?>#', $templatevar_htmlcode, $templatevar_match)) {
			foreach ($templatevar_match[0] as $templatevar_value2) {
				$templatevar_bl = substr($templatevar_value2, 5, strlen($templatevar_value2) - 7);
				eval("global \$_G;{$templatevar_bl}");
				$templatevar_htmlcode = str_replace($templatevar_value2, '', $templatevar_htmlcode);
				//生成缓存文件
				if ($templatevar_cache_open) {
					$templatevar_cache_htmlcode = str_replace($templatevar_value2, '<?php ' . $templatevar_bl . ' ?>', $templatevar_cache_htmlcode);
				}
			}
		}
		//模板内变量的显示
		if (preg_match_all('#\{\$[A-Za-z0-9_\-\[\]\'\"]+?\}#', $templatevar_htmlcode, $templatevar_match)) {
			foreach ($templatevar_match[0] as $templatevar_value2) {
				$templatevar_bl = substr($templatevar_value2, 2, strlen($templatevar_value2) - 3);
				$templatevar_cache_bl = $templatevar_bl;
				if (strpos($templatevar_bl, '[')) {//防止数组被global
					$templatevar_globalbl = substr($templatevar_bl, 0, strpos($templatevar_bl, '['));
				} else {
					$templatevar_globalbl = $templatevar_bl;
				}
				eval("global \$" . $templatevar_globalbl . ";");
				eval("\$templatevar_bl=\$" . $templatevar_bl . ";");
				//防止PHP函数执行漏洞
				$templatevar_bl = str_replace('{', '&#123;', $templatevar_bl);
				$templatevar_bl = str_replace('}', '&#125;', $templatevar_bl);
				$templatevar_htmlcode = str_replace($templatevar_value2, $templatevar_bl, $templatevar_htmlcode);
				//生成缓存文件
				if ($templatevar_cache_open) {
					$templatevar_cache_htmlcode = str_replace($templatevar_value2, '<?php global $' . $templatevar_globalbl . ';echo $' . $templatevar_cache_bl . '; ?>', $templatevar_cache_htmlcode);
				}
			}
		}
		//模板内函数的显示
		if (preg_match_all('#\{[\S ]+?\}#', $templatevar_htmlcode, $templatevar_match)) {
			foreach ($templatevar_match[0] as $templatevar_value2) {
				$templatevar_bl = substr($templatevar_value2, 1, strlen($templatevar_value2) - 2);
				$templatevar_cache_bl = $templatevar_bl;
				$templatevar_a = strpos($templatevar_bl, '(');
				if (function_exists(substr($templatevar_bl, 0, $templatevar_a))) {
					$templatevar_b = '';
					$templatevar_c = '';
					if (preg_match_all('#\$([A-Za-z_]+)#', $templatevar_bl, $templatevar_b)) {
						foreach ($templatevar_b[1] as $templatevar__v) {
							eval('global $' . $templatevar__v . ';');
							$templatevar_c .= 'global $' . $templatevar__v . ';';
						}
					}
					if (substr($templatevar_bl, $templatevar_a + 1) == '$') {
					}
					eval("\$templatevar_bl=" . $templatevar_bl . ";");
					$templatevar_htmlcode = str_replace($templatevar_value2, $templatevar_bl, $templatevar_htmlcode);
					//生成缓存文件
					if ($templatevar_cache_open) {
						$templatevar_cache_htmlcode = str_replace($templatevar_value2, '<?php ' . $templatevar_c . 'echo ' . $templatevar_cache_bl . '; ?>', $templatevar_cache_htmlcode);
					}
				}
			}
		}
		//还原被过滤的字符
		$templatevar_htmlcode = str_replace('&#123;', '{', $templatevar_htmlcode);
		$templatevar_htmlcode = str_replace('&#125;', '}', $templatevar_htmlcode);

		//存储缓存模板文件
		if ($templatevar_cache_open) {
			$templatevar_cachecontent .= $templatevar_cache_htmlcode;
			file_put_contents($templatevar_cachefilename, $templatevar_cachecontent);
		}
	}

	if ($templatevar_return) {
		return $templatevar_htmlcode;
	}
	echo $templatevar_htmlcode;
}

//mysql数据库转义,待过滤字符串，是否添加''，''两边添加的字符，是否强制添加''（false数字不添加）
function mysqlstr($str, $quto = true, $bwf = '', $must = true) {
	global $_G;
	$str = $_G['PDO'] -> quote($str);
	$str = substr($str, 1, strlen($str) - 2);
	if ($quto && !is_numeric($str) || $must) {
		$str = "'{$bwf}{$str}{$bwf}'";
	}
	return $str;
}

//获取当前文件的名称
function getFilename($hz = false) {
	$url = $_SERVER['SCRIPT_NAME'];
	$filename = end(explode('/', $url));
	if (!$hz) {
		$pos = strripos($filename, '.');
		$filename = substr($filename, 0, $pos);
	}
	return $filename;
}

function fun_cunzai($funname) {
	$pos = strpos($funname, "(");
	if ($pos) {
		$name = substr($funname, 0, $pos);
		if (function_exists($name)) {
			return true;
		} else {
			return false;
		}
	} else {
		return false;
	}
}

function getClientInfos($info = 'all') {
	if ($info == 'all') {
		$infos = '浏览器标示：' . $_SERVER['HTTP_USER_AGENT'] . ' <br>
';
		$infos .= '客户端语言：' . $_SERVER['HTTP_ACCEPT_LANGUAGE'] . '
<br>
';
		$infos .= '客户端IP地址：' . $_SERVER['REMOTE_ADDR'];
	} elseif ($info == 'ip') {
		$IPaddress = '';
		if (isset($_SERVER)) {
			if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
				$IPaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
			} elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
				$IPaddress = $_SERVER['HTTP_CLIENT_IP'];
			} else {
				$IPaddress = $_SERVER['REMOTE_ADDR'];
			}
		} else {
			if (getenv('HTTP_X_FORWARDED_FOR')) {
				$IPaddress = getenv('HTTP_X_FORWARDED_FOR');
			} elseif (getenv('HTTP_CLIENT_IP')) {
				$IPaddress = getenv('HTTP_CLIENT_IP');
			} else {
				$IPaddress = getenv('REMOTE_ADDR');
			}
		}
		if (strpos($IPaddress, ',') !== false) {
			$IPaddress = explode(',', $IPaddress);
			$IPaddress = $IPaddress[0];
		}
		$infos = $IPaddress;
	} else {
		$infos = $_SERVER[$info];
	}
	return htmlspecialchars(strip_tags($infos), ENT_QUOTES);
}

function chkuploadfilesuffix($suffix) {
	if (!$suffix) {
		return false;
	}
	global $_G;
	return in_array($suffix, explode("|", $_G['SET']['UPLOADFILETYPES']));
}

//生成伪静态URL，$name为c参数，$parmas为生成的参数，$addparmas为动态参数添加，$delimiter为分割符，$suffix为生成的后缀
function ReWriteURL($name, $parmas = '', $addparmas = '', $delimiter = '-', $suffix = '.html') {
	global $_G;
	if ($_G['SET']['REWRITEURL'] && (!$_G['USER']['ID'] || $_G['SET']['MUSTREWRITEURL'])) {
		if ($parmas) {
			$parmas = explode('&', $parmas);
			$url = $name;
			foreach ($parmas as $value) {
				$value = explode('=', $value);
				$url .= $delimiter . $value[1];
			}
		} else {
			$url = $name;
		}
		$addparmas ? $url .= "{$suffix}?{$addparmas}" : $url .= $suffix;
	} else {
		$addparmas ? $url = "index.php?c={$name}&{$parmas}&{$addparmas}" : $url .= "index.php?c={$name}&{$parmas}";
	}
	return $url;
}

function __unset() {
	foreach ($GLOBALS as $key => $value) {
		if (substr($key, 0, 2) == "__") {
			unset($GLOBALS[$key]);
		}
	}
}

function getShuXiang($datetime) {
	$year = date('Y', $datetime);
	if ($year) {
		//1900年是鼠年
		$data = array('鼠', '牛', '虎', '兔', '龙', '蛇', '马', '羊', '猴', '鸡', '狗', '猪');
		$index = ($year - 1900) % 12;
		return $data[$index];
	} else {
		return false;
	}
}

function getXingZuo($datetime) {
	$date = (int)date('nd', $datetime);
	if ($date) {
		switch ($date) {
			case (120<=$date&&218>=$date) :
				$xz = "水瓶";
				break;
			case (219<=$date&&320>=$date) :
				$xz = "双鱼";
				break;
			case (321<=$date&&419>=$date) :
				$xz = "白羊";
				break;
			case (420<=$date&&520>=$date) :
				$xz = "金牛";
				break;
			case (521<=$date&&621>=$date) :
				$xz = "双子";
				break;
			case (622<=$date&&722>=$date) :
				$xz = "巨蟹";
				break;
			case (723<=$date&&822>=$date) :
				$xz = "狮子";
				break;
			case (823<=$date&&922>=$date) :
				$xz = "处女";
				break;
			case (923<=$date&&1023>=$date) :
				$xz = "天秤";
				break;
			case (1024<=$date&&1121>=$date) :
				$xz = "天蝎";
				break;
			case (1122<=$date&&1221>=$date) :
				$xz = "射手";
				break;
			default :
				$xz = "摩羯";
				break;
		}
		return $xz;
	} else {
		return false;
	}
}

function getNianLing($datetime) {
	return (date('Y', time()) - date('Y', $datetime));
}

function SerializeData($data, $key, $value = null) {
	$__data = unserialize($data);
	if ($value !== null) {
		//数据更新
		if (!is_array($key)) {
			$key = explode(',', $key);
		}
		if (!is_array($value)) {
			$value = explode(',', $value);
		}

		if (count($key) == count($value)) {
			foreach ($key as $k => $k2) {
				$__data[$k2] = $value[$k];
			}
		} else {
			foreach ($key as $k => $k2) {
				$__data[$k2] = $value[0];
			}
		}

		$data = serialize($__data);
		return $data;
	} else {
		//数据读取
		return $__data[$key];
	}
}

//数据转Json处理，$data为json字符串，$key为要读取或写入的键名，$value为键值
function JsonData($data, $key = null, $value = null) {
	$__data = json_decode($data, true);
	if ($value !== null) {
		//数据更新
		if (!is_array($key)) {
			$key = explode(',', $key);
		}
		if (!is_array($value)) {
			$value = explode(',', $value);
		}

		if (count($key) == count($value)) {
			foreach ($key as $k => $k2) {
				$__data[$k2] = $value[$k];
			}
		} else {
			foreach ($key as $k => $k2) {
				$__data[$k2] = $value[0];
			}
		}

		$data = json_encode($__data);
		return $data;
	} else {
		//数据读取
		if ($key === null) {
			return $__data;
		} else {
			return $__data[$key];
		}
	}
}

function HSCSArray(array $array, $flags = ENT_QUOTES, $encoding = 'UTF-8', $double_encode = true) {
	foreach ($array as $key => $value) {
		if (is_string($value)) {
			$array[$key] = htmlspecialchars($value, $flags, $encoding, $double_encode);
		}
	}
	return $array;
}

function standardArray(&$array, $uppercase = true) {
	if (is_array($array)) {
		foreach ($array as $key => $value) {
			if ($uppercase) {
				$keyTemp = strtoupper($key);
			} else {
				$keyTemp = strtolower($key);
			}
			if ($keyTemp != $key) {
				unset($array[$key]);
				$array[$keyTemp] = $value;
			}
			if (is_array($array[$keyTemp])) {
				standardArray($array[$keyTemp], $uppercase);
			}
		}
	}
}

//Unicode 汉子编码
function UnicodeEncode($str, $format = 'js') {
	$str = json_encode($str);
	$str = str_replace('\"', '"', substr($str, 1, strlen($str) - 2));
	if ($format = 'css') {
		$str = str_replace('u', '', $str);
	} elseif ($format = 'html') {
		$str = str_replace('\u', '&#x', $str);
	}
	return $str;
}

//Unicode 汉子解码
function UnicodeDecode($str) {
	return reset(json_decode('["' . str_replace('"', '\"', $str) . '"]'));
}

//$array为字符串或数组，$needle为要查找的值，若为字符串此值起作用$delimiter为分割符
function InArray($array, $needle, $delimiter = ',') {
	if (!is_array($array)) {
		$array = explode($delimiter, $array);
	}
	if (!is_array($needle)) {
		$needle = explode($delimiter, $needle);
	}
	foreach ($needle as $value) {
		if (in_array($value, $array)) {
			return true;
		}
	}
	return false;
}

function EqualReturn($str, $old, $new) {
	if ($str === $old) {
		return $new;
	} else {
		return $str;
	}
}

function RunError($err) {
	global $_G;
	if (!$_G['SET']['RUNERRORDISPLAY']) {
		$err = '未开启运行错误详情显示，若您是管理员请在后台 - 首页 - 站点状态开启';
	}
	PkPopup('{title:"出错",content:"' . str_replace(array('"', '\\'), array('&quot;', '\\\\'), $err) . '",icon:2,close:function(){location.href="index.php"},hideclose:true,shade:true}');
}

function chkVerifycode($verifycode, $page) {
	global $_G;
	if (($_G['SET']['APP_VERIFYCODE_LOAD'] && $verifycode && strtolower($verifycode) == strtolower($_SESSION['APP_VERIFYCODE_' . strtoupper($page)])) || ($_G['SET']['APP_VERIFYCODE_LOAD'] && !InArray($_G['SET']['APP_VERIFYCODE_PAGE'], $page) && InArray('chklogin,savereg,post', $_G['GET']['C'])) || !$_G['SET']['APP_VERIFYCODE_LOAD'] || InArray(getUserQX($_G['USER']['ID'], 'quanxian'), 'noverifycode')) {
		$_SESSION['APP_VERIFYCODE_' . strtoupper($page)] = '';
		return true;
	} else {
		$_SESSION['APP_VERIFYCODE_' . strtoupper($page)] = '';
		return false;
	}
}

function loadVerifycode($page, $t = '') {
	global $_G;
	$_G['APP']['VERIFYCODE']['TYPE'] = $page;
	if ($_G['SET']['APP_VERIFYCODE_LOAD'] && InArray($_G['SET']['APP_VERIFYCODE_PAGE'], $page) && !InArray(getUserQX($_G['USER']['ID'], 'quanxian'), 'noverifycode')) {
		if ($t) {
			$t = '-' . $t;
		}
		if ($_G['SET']['APP_VERIFYCODE_OPENSLIDING']) {
			return template('verifycode:sliding' . $t, true);
		} else {
			return template('verifycode:verifycode' . $t, true);
		}
	}
}

//$chkuserloginarray为检测登录的数组，$chkloginqx是否检测用户具有登录权限
function UserLogin($chkuserloginarray, $chkloginqx = true, $autologin = true) {
	global $_G;
	$userdata = $_G['TABLE']['USER'] -> getData($chkuserloginarray);
	if (!$userdata) {
		$_G['USERLOGINFAILEDINFO'] .= '用户名或密码错误';
		return false;
	}
	if (!InArray(getUserQX($userdata['id']), 'login') && $chkloginqx) {
		$_G['USERLOGINFAILEDINFO'] .= '无权登录，请联系管理员';
		return false;
	}
	//====================存入登录信息=======================
	$array['id'] = $userdata['id'];
	$array['data'] = JsonData($userdata['data'], 'logininfo', array(getClientInfos()));
	$array['loginip'] = getClientInfos('ip');
	$array['logintime'] = time();
	if (!$_G['SET']['USERMULTIPLEONLINE']) {
		//不允许多设备同时在线
		$array['session_token'] = CreateRandomString(16);
	}
	$_G['TABLE']['USER'] -> newData($array);
	//if ($_G['SET']['UIABC']) {
	//=====================cookies safe======================
	//setcookie('UIA', key_endecode($userdata['id'] . '|' . md5($userdata['password'] . $array['session_token']) . md5($array['session_token'])), time() + Cnum($_G['SET']['USERCOOKIESLIFE'], 1800, TRUE, 300));
	//} else {
	//=====================session safe======================
	$_SESSION['HS_UID'] = $array['id'];
	$_SESSION['HS_UGID'] = $userdata['groupid'];
	$_SESSION['HS_SESSION_TOKEN'] = md5($array['session_token']);
	//防止session欺骗重新生成srid
	session_regenerate_id(true);
	//}
	//==================更新数据==============================
	$userdata = $_G['TABLE']['USER'] -> getData($array['id']);
	//==================登录提示==============================
	if (function_exists('sendmail') && ($_G['SET']['USERLOGINEMAILTIP'] || ($userdata['id'] == 1 && $_G['SET']['SUPERMANLOGINEMAILTIP']))) {
		sendmail($userdata['email'], $_G['SET']['LOGOTEXT'] . ' - 账号登录提示', "亲爱的{$userdata['nickname']}（UID：{$userdata['id']}），您的账号在" . date('Y-m-d H:i:s', time()) . "登录，登录IP为" . getClientInfos('ip') . "，若非本人操作请尽快更改账号密码（{$_G['SYSTEM']['DOMAIN']}）。");
	}
	//是否设置了自动登录
	if ($autologin) {
		setcookie('UIA', base64_encode(key_endecode($userdata['id'] . '|' . md5($userdata['password'] . $userdata['session_token']) . md5($userdata['session_token']))), time() + Cnum($_G['SET']['USERCOOKIESLIFE'], 86400, true, 1800), '/', $_G['SET']['COOKIE_DOMAIN'] ? $_G['SET']['COOKIE_DOMAIN'] : null, false, true);
	}
	return $userdata;
}

function UserLogout($full = false) {
	global $_G;
	setcookie('UIA', '', time() - 3600, '/', $_G['SET']['COOKIE_DOMAIN'] ? $_G['SET']['COOKIE_DOMAIN'] : null, false, true);
	if ($full) {
		$_SESSION = array();
	} else {
		$_SESSION['HS_UID'] = $_SESSION['HS_UGID'] = $_SESSION['HS_SESSION_TOKEN'] = '';
	}
}

function UIA() {
	global $_G;
	$chkdata = false;
	if ($_COOKIE['UIA']) {
		$cookie = base64_decode($_COOKIE['UIA']);
		//自动登录检测
		$uia = explode('|', key_endecode($cookie, 'DE'));
		if (count($uia) == 2 && Cnum($uia[0]) && strlen($uia[1]) == 64) {
			//数据合法
			$uid = $uia[0];
			$upw = substr($uia[1], 0, 32);
			$ust = substr($uia[1], 32);
			$chkdata = $_G['TABLE']['USER'] -> getData($uid);
			if ($chkdata) {
				if ($upw != md5($chkdata['password'] . $chkdata['session_token']) || $ust != md5($chkdata['session_token'])) {
					//验证失败
					$chkdata = false;
				}
			}
		}
	} elseif (Cnum($_SESSION['HS_UID'])) {
		//获取session用户id
		//读取此用户信息存在$_G['USER']数组中
		$chkdata = $_G['TABLE']['USER'] -> getData($_SESSION['HS_UID']);
		if ($chkdata) {
			if ($_SESSION['HS_SESSION_TOKEN'] != md5($chkdata['session_token'])) {
				$chkdata = false;
			}
		}
	}
	//检验身份
	if ($chkdata) {
		//登录用户数据处理
		$_SESSION['HS_UID'] = $chkdata['id'];
		$_SESSION['HS_UGID'] = $chkdata['groupid'];
		$_SESSION['HS_SESSION_TOKEN'] = md5($chkdata['session_token']);
	} else {
		//清除登录信息
		UserLogout();
		//游客数据处理
		$chkdata = json_decode($_G['SET']['GUESTDATA'], true);
		$chkdata['id'] = 0;
	}
	//用户权限排序显示
	$chkdata['quanxian'] = explode(',', $chkdata['quanxian']);
	if (is_array($chkdata['quanxian'])) {
		sort($chkdata['quanxian']);
		$__quanxian2 = $chkdata['quanxian'];
		$chkdata['quanxian'] = '';
		foreach ($__quanxian2 as $value) {
			$chkdata['quanxian'] .= ',' . $value;
		}
		$chkdata['quanxian'] = substr($chkdata['quanxian'], 1);
	}
	standardArray($chkdata);
	return $chkdata;
}

function CreateRandomString($len = 4, $str = false) {
	if (!$str) {
		$str = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890';
	}
	$strlen = strlen($str);
	$nstr = '';
	if (Cnum($len) > 0) {
		for ($i = 0; $i < $len; $i++) {
			$nstr .= substr($str, rand(0, $strlen - 1), 1);
		}
	}
	return $nstr;
}

//下载文件函数
function file_download($file_url, $new_name = '') {
	if (!isset($file_url) || trim($file_url) == '') {
		exit('download file 500');
	}
	if (!file_exists($file_url)) {//检查文件是否存在
		exit('download file 404');
	}
	$file_type = explode('.', $file_url);
	$file_type = $file_type[count($file_type) - 1];
	$file_name = date('YmdHis') . '.' . $file_type;
	//basename($file_url);
	$file_name = trim($new_name == '') ? $file_name : urlencode($new_name) . '.' . $file_type;
	//打开文件
	$file_data = fopen($file_url, 'r');
	$file_size = filesize($file_url);
	//@ob_clean();
	//输入文件标签
	header("Content-type: application/octet-stream");
	header("Accept-Ranges: bytes");
	header("Content-Length: " . $file_size);
	header("Content-Disposition: attachment; filename=\"{$file_name}\"");
	//输出文件内容
	$buffer = 1024;
	$buffer_count = 0;
	while (!feof($file_data) && $file_size - $buffer_count > 0) {
		$data = fread($file_data, $buffer);
		$buffer_count += $buffer;
		echo $data;
	}
	//echo fread($file_type, filesize($file_url));
	fclose($file_data);
	exit();
}

function GetPostData($url, $post_data = '', $timeout = 10) {
	$ch = curl_init();
	if (is_array($post_data)) {
		$post_data = http_build_query($post_data);
	}
	if ($ch === false) {
		$opts = array('http' => array('method' => ($post_data ? 'POST' : 'GET'), 'content' => $post_data, 'timeout' => $timeout));
		$context = stream_context_create($opts);
		$file_contents = file_get_contents($url, false, $context);
	} else {
		curl_setopt($ch, CURLOPT_URL, $url);
		if ($post_data) {
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
		}
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_HEADER, false);
		//跳过SSL验证
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, '0');
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, '0');
		$file_contents = curl_exec($ch);
		curl_close($ch);
	}
	return $file_contents;
}

function isphonecome() {
	$_SERVER['ALL_HTTP'] = isset($_SERVER['ALL_HTTP']) ? $_SERVER['ALL_HTTP'] : '';
	$mobile_browser = '0';
	if (preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|iphone|ipad|ipod|android|xoom)/i', strtolower($_SERVER['HTTP_USER_AGENT']))) {
		$mobile_browser++;
	}
	if ((isset($_SERVER['HTTP_ACCEPT'])) and (strpos(strtolower($_SERVER['HTTP_ACCEPT']), 'application/vnd.wap.xhtml+xml') !== false)) {
		$mobile_browser++;
	}
	if (isset($_SERVER['HTTP_X_WAP_PROFILE'])) {
		$mobile_browser++;
	}
	if (isset($_SERVER['HTTP_PROFILE'])) {
		$mobile_browser++;
	}
	$mobile_ua = strtolower(substr($_SERVER['HTTP_USER_AGENT'], 0, 4));
	$mobile_agents = array('w3c ', 'acs-', 'alav', 'alca', 'amoi', 'audi', 'avan', 'benq', 'bird', 'blac', 'blaz', 'brew', 'cell', 'cldc', 'cmd-', 'dang', 'doco', 'eric', 'hipt', 'inno', 'ipaq', 'java', 'jigs', 'kddi', 'keji', 'leno', 'lg-c', 'lg-d', 'lg-g', 'lge-', 'maui', 'maxo', 'midp', 'mits', 'mmef', 'mobi', 'mot-', 'moto', 'mwbp', 'nec-', 'newt', 'noki', 'oper', 'palm', 'pana', 'pant', 'phil', 'play', 'port', 'prox', 'qwap', 'sage', 'sams', 'sany', 'sch-', 'sec-', 'send', 'seri', 'sgh-', 'shar', 'sie-', 'siem', 'smal', 'smar', 'sony', 'sph-', 'symb', 't-mo', 'teli', 'tim-', 'tosh', 'tsm-', 'upg1', 'upsi', 'vk-v', 'voda', 'wap-', 'wapa', 'wapi', 'wapp', 'wapr', 'webc', 'winw', 'winw', 'xda', 'xda-');
	if (in_array($mobile_ua, $mobile_agents)) {
		$mobile_browser++;
	}
	if (strpos(strtolower($_SERVER['ALL_HTTP']), 'operamini') !== false) {
		$mobile_browser++;
	}
	// Pre-final check to reset everything if the user is on Windows
	if (strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'windows') !== false) {
		$mobile_browser = 0;
	}
	// But WP7 is also Windows, with a slightly different characteristic
	if (strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'windows phone') !== false) {
		$mobile_browser++;
	}
	if ($mobile_browser > 0) {
		return true;
	} else {
		return false;
	}
}

function key_endecode($String, $Type = 'EN', $Key = null) {
	if (!$String) {
		return false;
	}
	global $_G;
	$return = '';
	$Key === null ? $Key = md5($_G['SET']['KEY']) : $Key = md5($Key);
	for ($i = 0; $i < strlen($String); $i++) {
		$addascii = ord(substr($Key, ($i % 32), 1)) % 16;
		if (!$addascii) {
			$addascii = 1;
		}
		$trueascii = ord(substr($String, $i, 1));
		if (strtoupper($Type) == 'EN') {
			//加密
			$return .= chr($trueascii - $addascii);
		} else {
			//解密
			$return .= chr($trueascii + $addascii);
		}
	}
	return $return;
}

function LoadAppScript($s = null, $d = 'default', $p = 'phpscript') {
	global $_G;
	if ($s === null) {
		$s = (string)$_G['GET']['S'];
	}
	if (!$s && $s !== 0) {
		$s = $d;
	}
	$fp = "{$_G['SYSTEM']['PATH']}/app/{$_G['SYSTEM']['APPDIR']}/{$p}/{$s}.php";
	if (file_exists($fp)) {
		require $fp;
	} else {
		RunError("\"{$fp}\" doesn't exist");
	}
}

function ExitJson($datas = '', $state = false, $exit = true, $mustformat = true, $unescaped = FALSE) {
	$array = array();
	$array['state'] = $state ? 'ok' : 'no';
	if ((is_string($datas) || is_numeric($datas) || (!$datas && !is_array($datas))) && $mustformat) {
		$array['msg'] = $datas;
		$array['datas'] = array('msg' => $datas);
	} else {
		$array['datas'] = $datas;
	}
	if ($unescaped) {
		$array = json_encode($array, JSON_UNESCAPED_UNICODE);
	} else {
		$array = json_encode($array);
	}
	if ($exit) {
		header('Content-type:application/json');
		exit($array);
	} else {
		return $array;
	}
}

function GetSafeCheck() {
	foreach ($_GET as $key => $value) {
		$check = array('"', '>', '<', '\'', '(', ')', 'CONTENT-TRANSFER-ENCODING');
		if (!empty($value) && !json_decode($value)) {
			$value = strtoupper($value);
			foreach ($check as $_str) {
				if (strpos($value, $_str) !== false) {
					return false;
				}
			}
		}
	}
	return true;
}

function StringSafeCheck($str) {
	$check = array('"', '>', '<', '\'', '(', ')', 'CONTENT-TRANSFER-ENCODING');
	//$str = $_SERVER['REQUEST_URI'];
	if (!empty($str)) {
		$str = strtoupper(urldecode(urldecode($str)));
		foreach ($check as $_str) {
			if (strpos($str, $_str) !== false) {
				return false;
			}
		}
	}
	return true;
}

//退出并跳转
function ExitGourl($url) {
	header('Location:' . $url);
	exit('<script>location.href="' . str_replace('"', '\\"', $url) . '"</script>');
}

//pkpopup提示框
function PkPopup($data) {
	global $_G;
	if (is_array($data)) {
		$data = json_encode($data);
	}
	if (strpos(str_replace(' ', '', $data), 'submit:function(){') === false) {
		$data = '{
			submit:function(){
				if(document.referrer) {
					var h = location.protocol + "//" + location.host;
					if(document.referrer.substr(0,h.length) == h){
						history.back();
						location.href = document.referrer;
						return false;
					}
				}
				location.href = "index.php";
			},' . substr($data, 1);
	}
	$_G['SET']['WEBTITLE'] = '提示';
	$_G['HTMLCODE']['MAIN'] = '<script>$(function(){ppp(' . $data . ')})</script>';
	template($_G['SYSTEM']['PATH'] . 'template/default/main.hst');
	exit();
}

//内容图片的读取，html读取的内容，maxn最大读取的图片数，0为所有，noalt不读取的alt值(多个,分开)，true不获取base64编码的图片
function getHtmlImages($html, $maxN = 0, $noAlt = 'emotion', $nobase64 = FALSE) {
	$array = array();
	if (!preg_match_all('#<img.*?src="(.*?)".*?\>#', $html, $marks)) {
		return $array;
	}
	$s = 0;
	foreach ($marks[0] as $value) {
		if ($maxN && $maxN <= $s) {
			break;
		}
		$value = substr($value, 5, strlen($value) - 6) . ' ';
		if (preg_match_all('/([a-z_][a-z0-9_\-]+)\=(.*?) /i', $value, $attrs)) {
			$i = count($array);
			foreach ($attrs[1] as $k => $a) {
				$a = trim(strtolower($a));
				$v = trim($attrs[2][$k]);
				//去掉"、'
				if (strpos('"\'', substr($v, 0, 1)) !== false) {
					$v = substr($v, 1);
				}
				if (strpos('"\'', substr($v, -1)) !== false) {
					$v = substr($v, 0, strlen($v) - 1);
				}
				$array[$i][$a] = $v;
			}
			if ($noAlt && InArray(strtolower($noAlt), strtolower($array[$i]['alt']))) {
				unset($array[$i]);
				break;
			}
			if ($nobase64 && strpos($array[$i]['src'], 'data:image/png;base64,') === 0) {
				unset($array[$i]);
				break;
			}
			$s++;
		}
	}
	return $array;
}

function finallyOutput($html) {
	global $_G;
	if (is_array($_G['FOFS'])) {
		foreach ($_G['FOFS'] as $function_name) {
			if (function_exists($function_name)) {
				eval("\$html = {$function_name}(\$html);");
			}
		}
	}
	exit($html);
}

//管理编辑帖子函数,dos为展示的权限
function adminEditTipbox($type, $id, $dos = '') {
	global $_G;
	if (!InArray('read,reply', strtolower($type)) || !$_G['USER']['ID']) {
		return false;
	}
	$type = strtolower($type);
	$data = $_G['TABLE'][strtoupper($type)] -> getData(Cnum($id));
	if (!$data) {
		return false;
	}
	if ($type == 'read') {
		$sortid = $data['sortid'];
	} else {
		$readdata = $_G['TABLE']['READ'] -> getData($data['rid']);
		$sortid = $readdata['sortid'];
	}
	$bkdata = $_G['TABLE']['READSORT'] -> getData($sortid);
	if (!$dos) {
		$dos = '';
		if ($type == 'read') {
			if ($_G['USER']['ID'] == 1) {
				$dos .= ',activetop';
			}
			if (InArray(getUserQX(), 'superman') || $_G['USER']['ID'] == 1) {
				$dos .= ',top,high,locked,move';
			}
		} else {
			if (InArray(getUserQX(), 'admin') || $_G['USER']['ID'] == 1 || $readdata['uid'] == $_G['USER']['ID'] || InArray($bkdata['adminuids'], $_G['USER']['ID'])) {
				$dos .= ',top';
			}
		}
		if (InArray(getUserQX(), 'admin') || $_G['USER']['ID'] == 1 || InArray($bkdata['adminuids'], $_G['USER']['ID']) || (InArray(getUserQX(), 'edit' . $type) && $data['uid'] == $_G['USER']['ID'])) {
			$dos .= ',edit';
		}
		if (InArray(getUserQX(), 'admin') || $_G['USER']['ID'] == 1 || InArray($bkdata['adminuids'], $_G['USER']['ID']) || (InArray(getUserQX(), 'del' . $type) && $data['uid'] == $_G['USER']['ID'])) {
			$dos .= ',del';
		}
		$dos = substr($dos, 1);
	}
	if (InArray($dos, 'move') && !$_G['TEMP']['adminEditTipbox_bkdatas']) {
		$_G['TEMP']['adminEditTipbox_bkdatas'] = 1;
		$bkdatas = $_G['TABLE']['READSORT'] -> getDatas(0, 0, false, false, 'id,title,pid,rank');
		$_G['SET']['EMBED_HEADMARKS'] .= '<script>var adminEditTipbox_bkdatas=' . json_encode($bkdatas) . '</script>';
	}
	if (!$dos) {
		return FALSE;
	}
	return array('type' => $type, 'id' => $id, 'dos' => $dos);
}

function adminEditTipboxHTML($type, $id, $do = false) {
	$a = adminEditTipbox($type, $id);
	if ($a) {
		if (is_string($do)) {
			$do = ',function(){' . $do . '}';
		} elseif (is_array($do)) {
			$b = ',{';
			foreach ($do as $k => $v) {
				$b .= $k . ':function(){' . $v . '},';
			}
			$b .= '}';
			$do = $b;
		}
		if (!$do) {
			$do = '';
		}
		$b = 'adminEditTipbox(' . json_encode($a) . $do . ')';
		$b = htmlspecialchars($b, ENT_QUOTES);
		$b = str_replace(array('{', '}'), array('&#123;', '&#125;'), $b);
		return '<a class="pk-text-danger" href="javascript:" onclick="' . $b . '"><i class="fa fa-fw fa-gears"></i>管理</a>&nbsp;';
	}
	return '';
}

function userheadURL($uid = 0, $default = false, $base64 = false) {
	global $_G;
	if (Cnum($uid, false, true, 1)) {
		$url = 'userhead/' . $uid . '.png';
		if (file_exists($_G['SYSTEM']['PATH'] . $url)) {
			if ($base64) {
				return 'data:image/png;base64,' . base64_encode(file_get_contents($_G['SYSTEM']['PATH'] . $url));
			}
			return $url;
		}
	} elseif (is_string($uid)) {
		$url = $uid;
		//若为远程地址则直接返回值
		if (substr($url, 0, 2) == '//' || substr($url, 0, 7) == 'http://' || substr($url, 0, 8) == 'https://') {
			return $url;
		}
		if (file_exists($_G['SYSTEM']['PATH'] . $url)) {
			if ($base64) {
				return 'data:image/png;base64,' . base64_encode(file_get_contents($_G['SYSTEM']['PATH'] . $url));
			}
			return $url;
		}
	}
	if ($default == 'rnd') {
		$url = 'template/default/img/randhead/' . rand(1, Cnum($_G['SET']['TEMPLATE_DEFAULT_RANDHEADCOUNT'], 24)) . '.png';
		if (file_exists($_G['SYSTEM']['PATH'] . $url)) {
			if ($base64) {
				return 'data:image/png;base64,' . base64_encode(file_get_contents($_G['SYSTEM']['PATH'] . $url));
			}
			return $url;
		}
	}
	if ($default) {
		$url = $default;
	}
	if (file_exists($_G['SYSTEM']['PATH'] . $url)) {
		if ($base64) {
			return 'data:image/png;base64,' . base64_encode(file_get_contents($_G['SYSTEM']['PATH'] . $url));
		}
		return $url;
	}
	$url = 'userhead/0.png';
	if (file_exists($_G['SYSTEM']['PATH'] . $url)) {
		if ($base64) {
			return 'data:image/png;base64,' . base64_encode(file_get_contents($_G['SYSTEM']['PATH'] . $url));
		}
		return $url;
	}
	return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAAC0lEQVQYV2NgAAIAAAUAAarVyFEAAAAASUVORK5CYII=';
}

function returnString($str) {
	return $str;
}
