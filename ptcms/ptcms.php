<?php
/**
 * @Author: 杰少Pakey
 * @Email : admin@ptcms.com
 * @File  : PTCMS.php
 */

// 设置基本参数

//设置时区（中国）
date_default_timezone_set("PRC");
// 记录开始运行时间
$GLOBALS['_startTime'] = microtime(TRUE);
// 记录sql执行次数
$GLOBALS['_sqlNum'] = 0;
// 缓存读取次数
$GLOBALS['_cacheRead'] = 0;
// 缓存写入次数
$GLOBALS['_cacheWrite'] = 0;
// 记录内存初始使用
$GLOBALS['_startUseMems'] = memory_get_usage();
// 记录网络请求
$GLOBALS['_api']=array();
// 框架版本号
define('PTCMS_VERSION', '3.0.8 20140820');
// debug信息 是否开启当前项目debug模式 默认 不开启
defined('APP_DEBUG') || define('APP_DEBUG', false);
if (APP_DEBUG) {
    // 开启错误输出
    ini_set('display_errors', 'on');
    // 设置错误输出级别
    error_reporting(E_ALL);
} else {
    // 开启错误输出
    ini_set('display_errors', 'off');
    // 设置错误输出级别
    error_reporting(0);
}
//项目名
if (PHP_SAPI == 'cli') {
    $_root = '/';
} else {
    $_root = str_replace('\\', '/', dirname(rtrim(str_replace('\\', '/', $_SERVER['SCRIPT_NAME']), '/')));
}
//网站内部地址 子目录地址
defined('PT_DIR') || define('PT_DIR', rtrim($_root, '/'));
if ($pos = strpos($_SERVER['HTTP_HOST'], ':')) {
    $host = substr($_SERVER['HTTP_HOST'], 0, $pos);
} else {
    $host = $_SERVER['HTTP_HOST'];
}
defined('PT_URL') || define('PT_URL', 'http://' . $host . (($_SERVER['SERVER_PORT'] == 80) ? '' : ':' . $_SERVER['SERVER_PORT']) . PT_DIR); // 网站访问域名 不包括入口文件及参数

//项目根目录
defined('PT_ROOT') || define('PT_ROOT', str_replace('\\', '/', dirname($_SERVER['SCRIPT_FILENAME'])));
//PTCMS根目录
defined('PT_PATH') || define('PT_PATH', dirname(__FILE__));

// 项目目录
defined('APP_PATH') || define('APP_PATH', PT_ROOT . '/application');
//缓存目录
defined('CACHE_PATH') || define('CACHE_PATH', PT_ROOT . '/cache');
//数据目录/
defined('DATA_PATH') || define('DATA_PATH', PT_ROOT . '/data');
//模版目录
defined('TPL_PATH') || define('TPL_PATH', PT_ROOT . '/template');
// 其他常量
define('NOW_TIME', $_SERVER['REQUEST_TIME']);
define('IS_GET', $_SERVER['REQUEST_METHOD'] === 'GET' ? true : false);
define('IS_POST', $_SERVER['REQUEST_METHOD'] === 'POST' ? true : false);
define('IS_AJAX', ((isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') || !empty($_POST['isajax']) || !empty($_GET['isajax'])) ? true : false);
// 判断是否有html缓存
if (IS_GET && is_file(PT_ROOT.$_SERVER['REQUEST_URI'])){
    pt::show();
    readfile(PT_ROOT.$_SERVER['REQUEST_URI']);
    exit;
}
// 自动识别SAE环境
if (function_exists('saeAutoLoader') or function_exists('sae_auto_load')) {
    // sae
    defined('APP_MODE') or define('APP_MODE', 'sae');
} else {
    // 普通模式
    defined('APP_MODE') or define('APP_MODE', 'common');
}

if (!empty($_GET['backRun'])) {
    //生成html
    if (function_exists('fastcgi_finish_request')) {
        fastcgi_finish_request();
    } else {
        ob_flush();
        flush();
    }
}

pt::start();

class pt
{
    /**
     * 框架开始调用
     */
    public static function start()
    {
        //初始化加载
        self::init();
		plugin::call('app_init_start');
        // 加载公共配置文件
        C(self::import(APP_PATH . '/common/config.php'));
		//加载站点配置文件
        //C(self::import(APP_PATH . '/common/' . pt::getSiteCode() . '.config.php'));
        // 路由解析
		plugin::call('dispatcher_start');
        self::dispatcher();
		plugin::call('dispatcher_end');
        // 加载模块文件
        C(self::import(APP_PATH . '/' . MODULE_NAME . '/config.php'));
        // 加载函数
        self::import(APP_PATH . '/common/function.php');
        self::import(APP_PATH . '/' . MODULE_NAME . '/function.php');
        // 注册插件
        Plugin::register(C('tag', null, array()));
        // 控制器调用
        self::app();
    }

    protected static function app()
    {
		plugin::call('controller_start');
        $controllerFile = APP_PATH . '/' . MODULE_NAME . '/controller/' . CONTROLLER_NAME . '.php';
        if (is_file($controllerFile)) {
            include $controllerFile;
            $classname = CONTROLLER_NAME . 'Controller';
            $actionname = ACTION_NAME . 'Action';
            if (class_exists($classname, false)) {
                $app = new $classname();
                //加载init方法
                if (method_exists($app, 'init')) {
                    $app->init();
                }
                // 加载action
                if (method_exists($app, $actionname)) {
                    $app->$actionname();
					plugin::call('controller_end');
                } else {
                    halt('控制器' . CONTROLLER_NAME . '对应的文件中未找到方法' . $actionname, __FILE__, __LINE__ - 3);
                }
            } else {
                halt('控制器' . CONTROLLER_NAME . '对应的文件中未找到类' . $classname, __FILE__, __LINE__ - 13);
            }
        } else {
            halt('找不到' . MODULE_NAME . '模块下的控制器' . CONTROLLER_NAME . ' 文件不存在：' . $controllerFile, __FILE__, __LINE__ - 20);
        }
    }

    public static function import($filename)
    {
        static $_importFiles = array();
        if (!isset($_importFiles[$filename])) {
            if (is_file($filename)) {
                $_importFiles[$filename] = include $filename;
            } else {
                $_importFiles[$filename] = false;
            }
        }
        return $_importFiles[$filename];
    }


    protected static function dispatcher()
    {
        dispatcher::run();
        // 获取分组 模块和操作名称
        define('MODULE_NAME', strtolower($_GET['m']));
        define('CONTROLLER_NAME', strtolower($_GET['c']));
        define('ACTION_NAME', strtolower($_GET['a']));

        define('__SELF__', strip_tags($_SERVER['REQUEST_URI']));
        define('__APP__', rtrim($_SERVER['SCRIPT_NAME'], '/'));
        // 当前模块和分组地址
        define('__MODULE__', __APP__ . '?s=' . MODULE_NAME);
        define('__URL__', __MODULE__ . '/' . CONTROLLER_NAME);
        // 当前操作地址
        define('__ACTION__', __URL__ . '/' . ACTION_NAME);
    }

    /**
     * 注册autoload等操作
     */
    protected static function init()
    {
        // 设定错误和异常处理
        register_shutdown_function(array(__CLASS__, 'shutdown'));
        //set_error_handler(array(__CLASS__, 'error'));
        set_exception_handler(array(__CLASS__, 'exception'));
        // 注册AUTOLOAD方法
        spl_autoload_register(array(__CLASS__, 'autoload'));
        // 取消对GPC的自动处理
        if (version_compare(PHP_VERSION, '5.4.0', '<')) {
            ini_set('magic_quotes_runtime', 0);
            if (get_magic_quotes_gpc()) {
                function stripslashes_deep($value)
                {
                    $value = is_array($value) ? array_map('stripslashes_deep', $value) : (isset($value) ? stripslashes($value) : null);
                    return $value;
                }

                $_POST = stripslashes_deep($_POST);
                $_GET = stripslashes_deep($_GET);
                $_COOKIE = stripslashes_deep($_COOKIE);
            }
        }
    }

    // 自动加载
    public static function autoload($class)
    {
        $classfile = strtolower(str_replace('_', '/', $class));
        if (in_array($classfile, array('controller','view','dispatcher', 'cache', 'model', 'plugin', 'storage', 'widget', 'log'))) {
            pt::import(PT_PATH . '/core/' . $classfile . '.php');
        } elseif (substr($classfile, 0, 6) == 'driver') {
            pt::import(PT_PATH . '/' . $classfile . '.php');
        } elseif (substr($classfile, -10) == 'controller') {
            if (!pt::import(APP_PATH . '/' . MODULE_NAME . '/controller/' . substr($classfile, 0, -10) . '.php')) {
                pt::import(APP_PATH . '/common/controller/' . substr($classfile, 0, -10) . '.php');
            }
        } elseif (substr($classfile, -5) == 'model') {
            if (!pt::import(APP_PATH . '/common/model/' . substr($classfile, 0, -5) . '.php')) {
                pt::import(APP_PATH . '/' . MODULE_NAME . '/model/' . substr($classfile, 0, -5) . '.php');
            }
        } elseif (substr($classfile, -6) == 'widget') {
            if (!pt::import(APP_PATH . '/common/widget/' . substr($classfile, 0, -6) . '.php')) {
                pt::import(APP_PATH . '/' . MODULE_NAME . '/widget/' . substr($classfile, 0, -6) . '.php');
            }
        } elseif (substr($classfile, -6) == 'plugin') {
            pt::import(APP_PATH . '/common/plugin/' . substr($classfile, 0, -6) . '.php');
        } else {
            (pt::import(PT_PATH . '/library/' . $classfile . '.php')) or (pt::import(APP_PATH . '/common/library/' . $classfile . '.php')) or (pt::import(APP_PATH . '/' . MODULE_NAME . 'library/' . $classfile . '.php'));
        }
    }

    // 中止操作
    public static function shutdown()
    {
        // 判断是否有错误
        if ($e = error_get_last()) {
            if (in_array($e['type'], array(1, 4))) {
                halt($e['message'], $e['file'], $e['line']);
            }
        }
        //如果开启日志 则记录日志
        if (C('log', null, false)) log::build();
        // 如果自定义了close函数 则进行调用
        if (function_exists('pt_close')) {
            pt_close();
        }
    }

    // 异常处理
    public static function exception(Exception $e)
    {
        halt($e->getmessage(), $e->getFile(), $e->getLine());
    }

    // 错误处理
    public static function error($errno, $errstr, $errfile, $errline)
    {
        switch ($errno) {
            case E_ERROR:
            case E_PARSE:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
                halt($errstr, $errfile, $errline);
                break;
            case E_USER_ERROR:
            case E_STRICT:
            case E_USER_WARNING:
            case E_USER_NOTICE:
            default:
                break;
        }
    }

    public static function err404($msg = '找不到指定的页面')
    {
        $file = PT_ROOT . C('404file', null, '/404.html');
        if (is_file($file)) {
            $content = F($file);
            $content = str_replace(array('{$sitename}', '{$siteurl}', '{$msg}'), array(C('sitename'), C('siteurl'), $msg), $content);
            exit($content);
        } else {
            exit('页面出现错误，如需自定义此错误，请创建文件：' . $file);
        }
    }

    /**
     * 获取host
     **/
    public static function getSiteCode()
    {

        $domain = str_replace('-', '_', $_SERVER['HTTP_HOST']); // Replace '-' by '_'.
        if (strpos($domain, ':') !== false) $domain = substr($domain, 0, strpos($domain, ':')); // Remove port from domain.
        return $domain;
    }

    /**
     * 输出视图内容
     *
     * @access public
     * @param string $content  输出内容
     * @param string $mimeType MIME类型
     * @return void
     */
    public static function show($content='', $mimeType = 'text/html')
    {
        if (C('gzip_encode',null,false)) {
            $zlib = ini_get('zlib.output_compression');
            if (empty($zlib)) ob_start('ob_gzhandler');
        }
        if (!headers_sent()){
            header("Content-Type: $mimeType; charset=utf-8"); //设置系统的输出字符为utf-8
            header("Cache-control: private"); //支持页面回跳
            header("X-Powered-By: ptcms studio (www.ptcms.com)");
        }
        echo $content;
    }
}


/**
 * 获取和设置配置参数 支持批量定义
 *
 * @param string|array $name 配置变量
 * @param mixed $value       配置值
 * @param mixed $default     默认值
 * @return mixed
 */
function C($name = null, $value = null, $default = null)
{
    static $_config = array();
    // 无参数时获取所有
    if (empty($name)) {
        return $_config;
    }
    // 优先执行设置获取或赋值
    if (is_string($name)) {
        $name = strtolower($name);
        if (!strpos($name, '.')) {
            if (is_null($value))
                return $_config[$name] = isset($_config[$name]) ? $_config[$name] : $default;
            $_config[$name] = $value;
            return true;
        }
        // 二维数组设置和获取支持
        $name = explode('.', $name);
        if (is_null($value)) {
            $value = $_config;
            foreach ($name as $n) {
                if (isset($value[$n])) {
                    $value = $value[$n];
                } else {
                    $value = $default;
                    break;
                }
            }
            return $value;
        }
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
 * Cookie 设置、获取、删除
 *
 * @param string $name   cookies名称
 * @param string $value  cookie值
 * @param string $option cookie参数
 * @return mixed
 */
function cookie($name, $value = '', $option = null)
{
    static $_config = null;
    if (!$_config) {
        // 默认设置
        $_config = array(
            'prefix' => C('COOKIE_PREFIX', null, 'PTCMS_'), // cookie 名称前缀
            'expire' => intval(C('COOKIE_EXPIRE', null, 2592000)), // cookie 保存时间
            'path' => C('COOKIE_PATH', null, '/'), // cookie 保存路径
            'domain' => C('COOKIE_DOMAIN'), // cookie 有效域名
        );
    }
    // 参数设置(会覆盖黙认设置)
    if (!is_null($option)) {
        if (is_numeric($option))
            $option = array('expire' => $option);
        elseif (is_string($option))
            parse_str($option, $option);
        $config = array_merge($_config, array_change_key_case($option));
    } else {
        $config = $_config;
    }
    // 清除指定前缀的所有cookie
    if (is_null($name)) {
        if (empty($_COOKIE))
            return true;
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
        return true;
    }
    $name = $config['prefix'] . $name;
    if ('' === $value) {
        if (isset($_COOKIE[$name])) {
            return $_COOKIE[$name];
        } else {
            return null;
        }
    } else {
        if (is_null($value)) {
            setcookie($name, '', time() - 3600, $config['path'], $config['domain']);
            unset($_COOKIE[$name]); // 删除指定cookie
        } else {
            // 设置cookie
            $expire = !empty($config['expire']) ? time() + $config['expire'] : 0;
            setcookie($name, $value, $expire, $config['path'], $config['domain']);
            $_COOKIE[$name] = $value;
        }
    }
    return null;
}

/**
 * 文件函数
 *
 * @param string $file  需要写入的文件，系统的绝对路径加文件名
 * @param bool $content 不填写 读取 null 删除 其他 写入
 * @param string $mod   写入模式，默认为wb，wb清空写入  ab末尾插入
 * @return mixed
 */
function F($file, $content = false, $mod = '')
{
    if ($content === false) {
        return is_file($file) ? file_get_contents($file) : false;
    } elseif ($content === null) {
        if (is_file($file)) return unlink($file); //删除文件
        elseif (is_dir($file)) { //删除目录
            $handle = opendir($file);
            while (($filename = readdir($handle)) !== false) {
                if ($filename !== '.' && $filename !== '..') F($file . '/' . $filename, null);
            }
            closedir($handle);
            return @rmdir($file);
        }
    } else {
        if (!strpos($file, '://') && !is_dir(dirname($file))) {
            mkdir(dirname($file), 0755, true);
        }
        if (is_array($content)) {
            if (APP_DEBUG) {
                $content = str_replace('\\\\', '\\', '<?php' . PHP_EOL . 'return ' . var_export($content, true) . ';');
            } else {
                $content = str_replace('\\\\', '\\', strip_whitespace('<?php' . PHP_EOL . 'return ' . var_export($content, true) . ';'));
            }

        }
        if ($mod) {
            return file_put_contents($file, strval($content), $mod);
        } else {
            return file_put_contents($file, strval($content));
        }
    }
    return false;
}

/**
 * M函数用于实例化Model
 *
 * @param string $name  Model库名
 * @param string $layer Model分层
 * @return object
 */
function M($name = '', $layer = '')
{
    static $_model = array();
    if ($layer === '') {
        $layer = strtolower(C('DEFAULT_MODEL_LAYER', null, 'model'));
    }
    if (!empty($_model[$name])) {
        return $_model[$name];
    }
    if ($name) {
        $classname = "{$name}{$layer}";
        if (!class_exists($classname)) $classname = ucfirst($layer); //才用自动加载加载类 不存在则加载默认类
    } else {
        $classname = ucfirst($layer);
    }
    $_model[$name] = new $classname($name);
    return $_model[$name];
}

function halt($msg, $file = '', $line = '')
{
    if (APP_DEBUG) {
        $e['message'] = $msg;
        $e['file'] = $file;
        $e['line'] = $line;
        include PT_PATH . '/error.tpl';
    } else {
        PT::err404($msg);
    }
}

/**
 * 获取输入参数 支持过滤和默认值
 *
 * @param string $name   变量的名称 支持指定类型
 * @param mixed $default 不存在的时候默认值
 * @param mixed $filter  参数过滤方法
 * @return mixed
 */
function I($name, $filter = 'int', $default = null)
{
    if (strpos($name, '.')) { // 指定参数来源
        list($method, $name) = explode('.', $name, 2);
    } else { // 默认为post
        $method = 'post';
    }
    switch (strtolower($method)) {
        case 'get'     :
            $input = $_GET;
            break;
        case 'post'    :
            $input = $_POST;
            break;
        case 'put'     :
            parse_str(file_get_contents('php://input'), $input);
            break;
        case 'request' :
            $input = $_REQUEST;
            break;
        case 'session' :
            $input = $_SESSION;
            break;
        case 'cookie'  :
            $input = $_COOKIE;
            break;
        case 'server'  :
            $input = $_SERVER;
            break;
        case 'globals' :
            $input = $GLOBALS;
            break;
        default:
            return NULL;
    }
    $value = isset($input[$name]) ? $input[$name] : null;
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
            return empty($value) ? $default : (regex($value, $filter) ? $value : $default);
    }
}


/**
 * regex
 * 使用正则验证数据
 *
 * @param string $value 要验证的数据
 * @param string $rule  验证规则
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
        'username' => '/^(?!_)(?!.*?_$)[a-zA-Z0-9_\x{4e00}-\x{9fa5}]{3,15}$/u', //用户名
        'en' => '/^[a-zA-Z0-9_\s\-\.]+$/', //英文字符
        'cn' => '/^[\w\s\-\x{4e00}-\x{9fa5}]+$/u', //中文字符
        'safestring' => '/^[^\$\?]+$/'
    );
    // 检查是否有内置的正则表达式
    if (isset($validate[strtolower($rule)])) $rule = $validate[strtolower($rule)];
    return preg_match($rule, strval($value)) === 1;
}

/**
 * 链接生成
 *
 * @param string $method 对应方法
 * @param array $args    参数
 * @param array $ignores 忽略参数
 * @return string
 */
function U($method = '', $args = array(), $ignores = array())
{
    static $rules = null, $_method = array(),$_map=array();
    if ($rules === null) {
        $rules = C('URL_RULES');
        $_map=C('map_module');
    }
    //忽视args中的部分参数
    if (!empty($ignores)) {
        foreach ($ignores as $key => $var) {
            if (isset($args[$key]) && $args[$key] == $var) unset($args[$key]);
        }
    }
    //清除args中page参数
    if (isset($args['page']) && $args['page'] == 1) unset($args['page']);
    if (empty($_method[$method])) {
        if (substr_count($method, '.') == 1) {
            $_method[$method] = MODULE_NAME . '.' . $method;
        } elseif ($method === '') {
            $_method[$method] = MODULE_NAME . '.' . CONTROLLER_NAME . '.' . ACTION_NAME;
        } elseif (substr_count($method, '.') == 0) {
            $_method[$method] = MODULE_NAME . '.' . CONTROLLER_NAME . '.' . $method;
        } else {
            $_method[$method] = $method;
        }
        $_method[$method] = strtolower($_method[$method]);
    }
    $method = $_method[$method];
    if (!empty($rules[$method])) {
        $keys = array();
        $rule = $rules[$method];
        foreach ($args as $key => $arg) {
            $keys[] = '{' . $key . '}';
        }
        $url = clearUrl(str_replace($keys, $args, $rule));
		if (strpos($url,']')){
			$url=strtr($url,array('[' => '', ']' => ''));
		}
		return PT_DIR.$url;
    } else {
        list($param['m'], $param['c'], $param['a']) = explode('.', $method);
        krsort($param); //调整顺序为m c a
        $param = array_merge($param, $args);
        if (isset($_map[$param['m']])) $param['m']=$_map[$param['m']];
        return __APP__ . '?' . http_build_query($param);
    }
}

function clearUrl($url)
{
	$i=0;
	while(preg_match('#\[[^\[\]]*?\{\w+\}[^\[\]]*?\]#', $url,$match)){
		$url = str_replace($match['0'], '', $url);
		$i++;
		if ($i==10){
			var_dump($url);exit;
		}
	}
    return $url;
}


/**
 * 去除代码中的空白和注释
 *
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
                    $stripStr .= "<<<ptcms\n";
                    break;
                case T_END_HEREDOC:
                    $stripStr .= "ptcms;\n";
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
 * widget调用函数
 *
 * @param string $class widget名称
 * @param array $param  widget参数
 * @return mixed
 */
function W($class, $param)
{
    static $_class;
    $classname = ucfirst(strtolower($class));
    $class = $classname . 'Widget';
    if (empty($_class[$class])) {
        if (class_exists($class)) {
            $_class[$class] = new $class();
        } else {
            halt('不存在的widget：' . $classname, __FILE__, __LINE__ - 4);
        }
    }
    return $_class[$class]->run($param);
}