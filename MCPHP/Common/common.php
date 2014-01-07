<?php

/***************************************************************
 *   $Program: MCPHP FarmeWork (A Open PHP Application FarmeWrok) $
 *    $Author: iflove@163.com $
 *     $Email: Pakey@ptcms.com $
 * $Copyright: 2009 - 2012 Ptcms Studio $
 *      $Link: http://www.ptcms.com $
 *   $License: http://www.ptcms.com/service/license.html $
 *      $Date: 2013-06-16 21:59:39 +0800 (周日, 2013-06-16) $
 *      $File: common.php $
 *  $Revision: 3 $
 *      $Desc: 常用函数库
 **************************************************************/

defined('MC_PATH') || exit('Permission denied');

/**
 * 实例化其他控制器
 * @param $controller      控制器名格式
 * @param bool $resources  是否加载资源
 * @return bool
 */
function A($controller, $resources = false)
{
	$tmp = explode('/', $controller);
	$controller = array_pop($tmp);
	$module = array_pop($tmp);
	$controller = ucfirst($controller);
	if ($module === null) {
		$file = CONTROLLER_PATH . $controller . 'Controller.class.php';
	} else {
		$module = ucfirst($module);
		$file = CONTROLLER_PATH . $module . '/' . $controller . 'Controller.class.php';
	}
	if ($resources) {
		$config_path = CONFIG_PATH . $module . '/';
		$common_path = COMMON_PATH . $module . '/';
		// 加载分组配置文件
		if (is_file($config_path . 'config.php'))
			C(require_cache($config_path . 'config.php'));
		// 加载分组别名定义
		if (is_file($config_path . 'alias.php'))
			alias_import(require_cache($config_path . 'alias.php'));
		// 加载分组函数文件
		if (is_file($common_path . 'function.php'))
			require_cache($common_path . 'function.php');
	}

	require_cache($file);
	$className = $controller . 'Controller';
	if (class_exists($className)) {
		return new $className();
	} else {
		return false;
	}
}

/**
 * 获取和设置配置参数 支持批量定义
 * @param string|array $name 配置变量
 * @param mixed $value       配置值
 * @return mixed
 */
/**
 * 获取和设置配置参数 支持批量定义
 * @param string|array $name 配置变量
 * @param mixed $value       配置值
 * @return mixed
 */
function C($name = null, $value = null)
{
    static $_config = array();
    // 无参数时获取所有
    if (empty($name)) {
        return $_config;
    }
    // 优先执行设置获取或赋值
    if (is_string($name)) {
        if (!strpos($name, '.')) {
            $name = strtolower($name);
            if (is_null($value))
                return isset($_config[$name]) ? $_config[$name] : null;
            $_config[$name] = $value;
            return true;
        }
        // 二维数组设置和获取支持
        $name = explode('.', $name);
        $name[0] = strtolower($name[0]);
        if (is_null($value))
            return isset($_config[$name[0]][$name[1]]) ? $_config[$name[0]][$name[1]] : null;
        $_config[$name[0]][$name[1]] = $value;
        return true;
    }
    // 批量设置
    if (is_array($name)) {
        $_config = array_merge($_config, array_change_key_case($name));
        return true;
    }
    return null; // 避免非法参数
}

/**
 * D函数用于实例化Model
 *
 * @param string $name Model资源地址
 * @return object
 */
function D($name = '')
{
	static $_model = array();
	if (!isset($_model[$name])) {
		if (strpos($name, '.')) {
			list($database, $table) = explode('.', $name);
			$connection = 'multidb_' . $database;
			$table = ucfirst($table);
		} else {
			$connection = '';
			$table = ucfirst($name);
		}
		$class = $table . 'Model';
		if (class_exists($class)) {
			$_model[$name] = new $class($table, $connection);
		} else {
			$_model[$name] = new Model($table, $connection);
		}
	}
	return $_model[$name];
}

/**
 * 文件函数
 * @param $file       需要写入的文件，系统的绝对路径加文件名
 * @param $content    不填写 读取 null 删除 其他 写入
 * @param string $mod 写入模式，默认为wb，wb清空写入  ab末尾插入
 * @return bool
 */
function F($file, $content = false, $mod = '')
{
	if ($content === false) {
		return file_get_contents($file);
	} elseif ($content === null) {
		return unlink($file);
	} else {
		if (!is_dir(dirname($file))) {
			mkdir(dirname($file), 0755, true);
		}
        if ($mod){
            return file_put_contents($file, strval($content),$mod);
        }else{
            return file_put_contents($file, strval($content));
        }
	}
}

// 获取数据
/**
 * 获取GPC参数
 *
 * @param string $key     - 权限表达式
 * @param mixed $data     - R $_REQUEST变量；G $_GET变量；P $_POST变量；C $_COOKIE变量
 * @param string $filter  - int 数字类型；str 字符串类型；arr 数组类型；其他字符串正则；为空不进行过滤
 * @param string $default - 不存在时默认返回值
 * @return string  返回经过过滤或者初始化的GPC变量
 */
function G($key, $data = 'R', $filter = 'int', $default = null)
{
	switch ($data) {
		case 'G':
			$var = $_GET;
			break;
		case 'P':
			$var = $_POST;
			break;
		case 'C':
			$var = $_COOKIE;
			$key = C('COOKIE_PREFIX') . $key;
			break;
		case 'R':
			$var = $_REQUEST;
			break;
		default:
			$var = $data;
	}
	$value = isset($var[$key]) ? $var[$key] : null;
	if (is_array($filter)) return in_array($value, $filter) ? $value : $default;
	if (!is_string($filter)) return $value;
	switch ($filter) {
		case 'int':
			return is_null($value) ? (is_null($default) ? 0 : $default) : intval($value);
		case 'str':
			return is_null($value) ? (is_null($default) ? '' : $default) : strval($value);
		case 'arr':
			return is_array($value) ? $value : (is_array($default) ? $default : array());
		default:
			return is_null($value) ? $default : (regex($value, $filter) ? $value : $default);
	}
}

// 打印
function P()
{
	$args = func_get_args(); //获取多个参数
	echo '<div style="width:100%;text-align:left"><pre>';
	//多个参数循环输出
	foreach ($args as $arg) {
		if (is_array($arg)) {
			print_r($arg);
			echo '<br>';
		} else if (is_string($arg)) {
			echo $arg . '<br>';
		} else {
			var_dump($arg);
			echo '<br>';
		}
	}
	echo '</pre></div>';
}

/**
 * 缓存管理
 * @param mixed $name    缓存名称，如果为数组表示进行缓存设置
 * @param mixed $value   缓存值
 * @param mixed $options 缓存参数
 * @return mixed
 */
function S($name, $value = '', $options = null)
{
	static $cache = '';
	if (is_array($options)) {
		// 缓存操作的同时初始化
		$type = isset($options['type']) ? $options['type'] : '';
		$cache = Cache::getInstance($type, $options);
	} elseif (is_array($name)) { // 缓存初始化
		$type = isset($name['type']) ? $name['type'] : '';
		$cache = Cache::getInstance($type, $name);
		return $cache;
	} elseif (empty($cache)) { // 自动初始化
		$cache = Cache::getInstance();
	}
	if ('' === $value) { // 获取缓存
		return $cache->get($name);
	} elseif (is_null($value)) { // 删除缓存
		return $cache->rm($name);
	} else { // 缓存数据
		$expire = is_numeric($options) ? $options : NULL;
		return $cache->set($name, $value, $expire);
	}
}

/**
 * // 组装url
 *
 * @param $key
 * @param array $param
 * @param $type 返回地址类型 true为url地址 false为文件路径
 * @return mixed|string
 */
function U($key, $param = array(), $type = true)
{
	static $rule = null;
	if (!isset($rule)) $rule = C('URL_RULE');
	if (!is_array($param)) return '';
	if (!isset($rule[$key])) return '';
	if ($type) {
		$url = ROOT . '/' . $rule[$key];
	} else {
		$url = MC_ROOT . $rule[$key];
	}
	foreach ($param as $k => $v) {
		$url = str_replace('{' . $k . '}', urlencode($v), $url);
	}
	return $url;
}

/**
 * 渲染输出Widget
 *
 * @param $class     名称
 * @param $var       传入的参数
 * @param $template  自定义模版
 * @param $cacheTime 数据缓存时间
 * @param $checkkey       缓存key
 * @return mixed
 */
function W($class, $var = null, $template='',$cacheTime = '',$checkkey='')
{
	static $_widget = array();
	$key = md5($class . '_' . $var);
	if (isset($_widget[$key])) {
		return $_widget[$key];
	}
	$data = Widget::getwidget($class, $var,$template, $cacheTime,$checkkey);
	$_widget[$key] = $data;
	return $data;
}

/**
 * 快速定义和导入别名 支持批量定义
 * @param string|array $alias 类库别名
 * @param string $classfile   对应类库
 * @return bool
 */
function alias_import($alias, $classfile = '')
{
	static $_alias = array();
	if (is_string($alias)) {
		$alias = strtolower($alias);
		if (isset($_alias[$alias])) {
			return require_cache($_alias[$alias]);
		} elseif ('' !== $classfile) {
			// 定义别名导入
			$_alias[$alias] = $classfile;
			return true;
		}
	} elseif (is_array($alias)) {
		$alias = array_change_key_case($alias);
		$_alias = array_merge($_alias, $alias);
		return true;
	}
	return false;
}

// 导入文件
/**
 * 导入所需的类库 同java的Import 本函数有缓存功能
 *
 * @param string $class 类库命名空间字符串
 * @param string $ext   导入的文件扩展名
 * @return boolean
 */
function import($class, $ext = '.class.php')
{
	static $_file = array();
	$class = str_replace(array('.', '%'), array('/', '.'), $class);
	if (false === strpos($class, '/')) {
		// 检查别名导入
		return alias_import($class);
	}
	if (isset($_file[$class]))
		return true;
	else
		$_file[$class] = true;
	$class_strut = explode('/', $class);
	if ($class_strut[0] === '@') {
		$baseUrl = MC_PATH . 'Extend/';
		$class = substr($class, 2);
	} elseif ($class_strut[0] === '#') {
		$baseUrl = LIBRARY_PATH;
		$class = substr($class, 2);
	} else {
		$baseUrl = MC_ROOT;
	}
	$classfile = $baseUrl . $class . $ext;
	if (!class_exists(basename($class), false)) {
		// 如果类不存在 则导入类库文件
		return require_cache($classfile);
	}
}

/**
 * 去除代码中的空白和注释
 * @param string $content 代码内容
 * @return string
 */
function strip_whitespace($content)
{
	$stripStr = '';
	//分析php源码
	$tokens = token_get_all($content);
	$last_space = false;
	for ($i = 0, $j = count($tokens); $i < $j; $i++) {
		if (is_string($tokens[$i])) {
			$last_space = false;
			$stripStr .= $tokens[$i];
		} else {
			switch ($tokens[$i][0]) {
				//过滤各种PHP注释
				case T_COMMENT:
				case T_DOC_COMMENT:
					break;
				//过滤空格
				case T_WHITESPACE:
					if (!$last_space) {
						$stripStr .= ' ';
						$last_space = true;
					}
					break;
				case T_START_HEREDOC:
					$stripStr .= "<<<MCPHP\n";
					break;
				case T_END_HEREDOC:
					$stripStr .= "MCPHP;\n";
					for ($k = $i + 1; $k < $j; $k++) {
						if (is_string($tokens[$k]) && $tokens[$k] == ';') {
							$i = $k;
							break;
						} else if ($tokens[$k][0] == T_CLOSE_TAG) {
							break;
						}
					}
					break;
				default:
					$last_space = false;
					$stripStr .= $tokens[$i][1];
			}
		}
	}
	return $stripStr;
}

/**
 * 优化的require_once
 *
 * @param string $filename 文件地址
 * @return boolean
 */
function require_cache($filename)
{
	static $_importFiles = array();
	if (!isset($_importFiles[$filename])) {
		if (file_exists($filename)) {
			$_importFiles[$filename] = require $filename;
		} else {
			$_importFiles[$filename] = false;
		}
	}
	return $_importFiles[$filename];
}

/**
 * 批量导入文件 成功则返回
 * @param array $array    文件数组
 * @param boolean $return 加载成功后是否返回
 * @return boolean
 */
function require_array($array, $return = false)
{
	foreach ($array as $file) {
		if (require_cache($file) && $return) return true;
	}
	if ($return) return false;
}

// 错误输出
/**
 * halt
 * 错误输出
 *
 * @param string $error 输出内容
 * @access public
 * @return mixed
 */
function halt($error)
{
	$e = array();
	if (APP_DEBUG) {
		//调试模式下输出错误信息
		if (!is_array($error)) {
			$trace = debug_backtrace();
			$e['message'] = htmlspecialchars($error);
			$e['file'] = $trace[0]['file'];
			$e['class'] = isset($trace[0]['class']) ? $trace[0]['class'] : '';
			$e['function'] = isset($trace[0]['function']) ? $trace[0]['function'] : '';
			$e['line'] = $trace[0]['line'];
			$traceInfo = '';
			$time = date('y-m-d H:i:m');
			foreach ($trace as $t) {
				$t['type'] = isset($t['type']) ? $t['type'] : '';
				$t['class'] = isset($t['class']) ? $t['class'] : '';
				$t['function'] = isset($t['function']) ? $t['function'] : '';
				$traceInfo .= '[' . $time . '] ' . $t['file'] . ' (' . $t['line'] . ') ';
				$traceInfo .= $t['class'] . $t['type'] . $t['function'] . '(';
				$traceInfo .= implode(', ', $t['args']);
				$traceInfo .= ')<br/>';
			}
			$e['trace'] = $traceInfo;
		} else {
			$e = $error;
		}
		// 包含异常页面模板
		require MC_PATH . 'Tpl/exception.tpl';
	} else {
		//否则定向到错误页面
		$error_page = C('ERROR_PAGE');
		if (!empty($error_page)) {
			echo '<script>location="' . $error_page . '"</script>';
		} else {
			if (C('SHOW_ERROR_MSG'))
				$e['message'] = is_array($error) ? $error['message'] : $error;
			else
				$e['message'] = C('ERROR_MESSAGE');
			// 包含异常页面模板
			require MC_PATH . 'Tpl/exception.tpl';
		}
	}
	exit();
}

/**
 * Cookie 设置、获取、删除
 *
 * @param $name         cookie名称
 * @param string $value cookie值
 * @param $option       cookie参数
 * @return mixed
 */
function cookie($name, $value = '', $option = null)
{
	// 默认设置
	$config = array(
		'prefix' => C('COOKIE_PREFIX'), // cookie 名称前缀
		'expire' => C('COOKIE_EXPIRE'), // cookie 保存时间
		'path' => C('COOKIE_PATH'), // cookie 保存路径
		'domain' => C('COOKIE_DOMAIN'), // cookie 有效域名
	);
	// 参数设置(会覆盖黙认设置)
	if (!is_null($option)) {
		if (is_numeric($option))
			$option = array('expire' => $option);
		elseif (is_string($option))
			parse_str($option, $option);
		$config = array_merge($config, array_change_key_case($option));
	}
	// 清除指定前缀的所有cookie
	if (is_null($name)) {
		if (empty($_COOKIE))
			return;
		// 要删除的cookie前缀，不指定则删除config设置的指定前缀
		$prefix = empty($value) ? $config['prefix'] : $value;
		if (!empty($prefix)) { // 如果前缀为空字符串将不作处理直接返回
			foreach ($_COOKIE as $key => $val) {
				if (0 === stripos($key, $prefix)) {
					setcookie($key, '', time() - 3600, $config['path'], $config['domain']);
					unset($_COOKIE[$key]);
				}
			}
		}
		return;
	}
	$name = $config['prefix'] . $name;
	if ('' === $value) {
		if (isset($_COOKIE[$name])) {
			$value = $_COOKIE[$name];
			if (0 === strpos($value, 'think:')) {
				$value = substr($value, 6);
				return array_map('urldecode', json_decode(MAGIC_QUOTES_GPC ? stripslashes($value) : $value, true));
			} else {
				return $value;
			}
		} else {
			return null;
		}
	} else {
		if (is_null($value)) {
			setcookie($name, '', time() - 3600, $config['path'], $config['domain']);
			unset($_COOKIE[$name]); // 删除指定cookie
		} else {
			// 设置cookie
			if (is_array($value)) {
				$value = 'ptcms:' . json_encode(array_map('urlencode', $value));
			}
			$expire = !empty($config['expire']) ? time() + intval($config['expire']) : 0;
			setcookie($name, $value, $expire, $config['path'], $config['domain']);
			$_COOKIE[$name] = $value;
		}
	}
}

/**
 * regex
 * 使用正则验证数据
 *
 * @param string $value  要验证的数据
 * @param string $rule   验证规则
 * @return mixed
 */
function regex($value, $rule)
{
	$validate = array(
		'require' => '/.+/', //必填
		'email' => '/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/', //邮箱
		'url' => '/^http:\/\/[a-zA-Z0-9]+\.[a-zA-Z0-9]+[\/=\?%\-&_~`@\[\]\':+!]*([^<>\"\"])*$/', //链接
		'currency' => '/^\d+(\.\d+)?$/', //货币
		'number' => '/^\d+$/', //数字
		'zip' => '/^[0-9]\d{5}$/', //邮编
		'tel' => '/^1[\d]{10}$/', //电话
		'integer' => '/^[-\+]?\d+$/', //整型
		'double' => '/^[-\+]?\d+(\.\d+)?$/', //带小数点
		'english' => '/^[a-zA-Z]+$/', //英文字母
		'chinese' => '/^[\x{4e00}-\x{9fa5}]+$/u', //中文汉字
		'pinyin' => '/^[a-zA-Z0-9\-\_]+$/', //拼音
		'username' => '/^(?!_)(?!.*?_$)[a-zA-Z0-9_\x{4e00}-\x{9fa5}]{1,15}$/u', //用户名
		'en' => '/^[a-zA-Z0-9_\s\-]+$/', //英文字符
		'cn' => '/^[\w\s\-\x{4e00}-\x{9fa5}]+$/u' //中文字符
	);
	// 检查是否有内置的正则表达式
	if (isset($validate[strtolower($rule)])) $rule = $validate[strtolower($rule)];
	return preg_match($rule, strval($value)) === 1;
}


/**
 * 字符串截取，支持中文和其他编码
 * @param string $string 需要转换的字符串
 * @param string $length 截取长度
 * @param string $suffix 截断显示字符
 * @param int $start     开始位置
 * @return string
 */
function truncate($string, $length, $suffix = '', $start = 0)
{
	if (empty($string) or empty($length) or strlen($string) < $length) return $string;
	if (function_exists('mb_substr')) {
		$slice = mb_substr($string, $start, $length, 'utf-8');
	} elseif (function_exists('iconv_substr')) {
		$slice = iconv_substr($string, $start, $length, 'utf-8');
	} else {
		preg_match_all('/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/', $string, $match);
		$slice = implode('', array_slice(reset($match), $start, $length));
	}
	return $slice . $suffix;
}

// html转换成js
function html2js($str)
{
	$return = '';
	$str = str_replace("\r\n", "\n", $str);
	$str = explode("\n", addcslashes($str, '\'"\\'));
	for ($i = 0; $i < count($str); $i++) {
		$return .= "document.writeln(\"" . $str[$i] . "\");\r\n";
	}
	return $return;
}

/**
 * 时间格式转换
 * @param $time 要转换的时间
 * @return string
 */
function cntime($time)
{
	$time = time() - $time;
	if ($time < 0)
		return '未发生';
	elseif ($time < 60)
		return $time . '秒前'; elseif ($time < 3600)
		return floor($time / 60) . '分钟前'; elseif ($time < 86400)
		return floor($time / 3600) . '小时前'; elseif ($time < 31536000)
		return floor($time / 86400) . '天前'; else
		return '一年前';
}

/**
 * 获取客户端IP地址
 *
 * @param integer $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
 * @return mixed
 */
function get_client_ip($type = 0)
{
	$type = $type ? 1 : 0;
	static $ip = NULL;
	if (isset($_SERVER['REMOTE_ADDR'])) {
		$ip = $_SERVER['REMOTE_ADDR'];
	}
	// IP地址合法验证
	$long = sprintf("%u", ip2long($ip));
	$ip = $long ? array($ip, $long) : array('0.0.0.0', 0);
	return $ip[$type];
}


/**
 * 默认值输出
 */
function defaultvar()
{
	$param = func_get_args();
	if (count($param) > 1) {
		if (is_numeric($param['0'])) {
			$key = intval($param['0']) + 1;
		} else {
			$key = intval(!empty($param['0'])) + 1;
		}
		if (isset($param[$key])) {
			return $param[$key];
		} else {
			return $param['0'];
		}
	}
	return '';
}

/**
 * 得字符串的长度，包括中英文。
 * @param $str
 * @param string $charset
 * @return int
 */
function mc_strlen($str, $charset = 'UTF-8') {
	if (function_exists('mb_substr')) {
		$length = mb_strlen($str, $charset);
	} elseif (function_exists('iconv_substr')) {
		$length = iconv_strlen($str, $charset);
	} else {
		preg_match_all("/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|\xe0[\xa0-\xbf][\x80-\xbf]|[\xe1-\xef][\x80-\xbf][\x80-\xbf]|\xf0[\x90-\xbf][\x80-f][\x80-\xbf]|[\xf1-\xf7][\x80-\xbf][\x80-\xbf][\x80-\xbf]/", $str, $ar);
		$length = count($ar[0]);
	}
	return $length;
}
