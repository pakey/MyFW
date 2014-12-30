<?php
/**
 * @Author: 杰少Pakey
 * @Email : admin@ptcms.com
 * @File  : PTCMS.php
 */

//设置时区（中国）
date_default_timezone_set("PRC");
// 记录开始运行时间
$GLOBALS['_startTime'] = microtime(true);
// 记录sql执行次数
$GLOBALS['_sql']    = array();
$GLOBALS['_sqlnum'] = 0;
// 缓存读取次数
$GLOBALS['_cacheRead'] = 0;
// 缓存写入次数
$GLOBALS['_cacheWrite'] = 0;
// 记录内存初始使用
$GLOBALS['_startUseMems'] = memory_get_usage();
// 记录网络请求
$GLOBALS['_api']    = array();
$GLOBALS['_apinum'] = 0;
// 框架版本号
define('PTCMS_VERSION', '3.2.0 20141230');
// debug信息 是否开启当前项目debug模式 默认 不开启
defined('APP_DEBUG') || define('APP_DEBUG', false);

//项目名
if (PHP_SAPI == 'cli') {
    $_root = '/';
} else {
    $_root = str_replace('\\', '/', dirname(rtrim(str_replace('\\', '/', $_SERVER['SCRIPT_NAME']), '/')));
}
//网站内部地址 子目录地址
/**
 *
 */
defined('PT_DIR') || define('PT_DIR', rtrim($_root, '/'));
if ($pos = strpos($_SERVER['HTTP_HOST'], ':')) {
    $host = substr($_SERVER['HTTP_HOST'], 0, $pos);
} else {
    $host = $_SERVER['HTTP_HOST'];
}
// 网站访问域名 不包括入口文件及参数
defined('PT_URL') || define('PT_URL', 'http://' . $host . (($_SERVER['SERVER_PORT'] == 80) ? '' : ':' . $_SERVER['SERVER_PORT']) . PT_DIR);

//项目根目录
defined('PT_ROOT') || define('PT_ROOT', str_replace('\\', '/', dirname($_SERVER['SCRIPT_FILENAME'])));
//PTCMS根目录
defined('PT_PATH') || define('PT_PATH', dirname(__FILE__));

// 项目目录
defined('APP_PATH') || define('APP_PATH', PT_ROOT . '/application');
//缓存目录
defined('CACHE_PATH') || define('CACHE_PATH', PT_ROOT . '/runtime');
//数据目录/
defined('DATA_PATH') || define('DATA_PATH', APP_PATH . '/common/data');
//模版目录
defined('TPL_PATH') || define('TPL_PATH', PT_ROOT . '/template');
// 环境常量
define('NOW_TIME', $_SERVER['REQUEST_TIME']);


/**
 * Class PT_Base
 *
 * @property PT_Api $api
 * @property PT_Block $block
 * @property PT_Cache $cache
 * @property PT_Config $config
 * @property PT_Controller $controller
 * @property PT_Cookie $cookie
 * @property PT_Db $db
 * @property PT_Dispatcher $dispatcher
 * @property PT_Filter $filter
 * @property PT_Input $input
 * @property PT_Log $log
 * @property PT_Model $model
 * @property PT_Plugin $plugin
 * @property PT_Request $request
 * @property PT_Response $response
 * @property PT_Session $session
 * @property PT_Storage $storage
 * @property PT_View $view
 * @property PT_base $pt
 */
class PT_Base {

    protected static $single = null;
    protected static $_class = array();
    protected static $_model = array();


    /**
     * @return PT_Base
     */
    public static function getInstance() {
        if (!self::$single) {
            self::$single = new PT_Base();
        }
        return self::$single;
    }

    /**
     * @return PT_Base
     */
    public function getInstanceof($name) {
        if (isset(self::$_class[$name])) {
            return $this->$name = self::$_class[$name];
        }
        if ($name == 'pt') {
            return $this->pt = self::getInstance();
        }
        if (is_file(PT_PATH . "/core/{$name}.php")) {
            $classname = 'PT_' . $name;
            if (!class_exists($classname, true)) pt::import(PT_PATH . "/core/{$name}.php");
            return $this->$name = self::$_class[$name] = new $classname();
        }
        return null;
    }

    public function __get($name) {
        return $this->$name=$this->getInstanceof($name);
    }

    /**
     * @param $name
     * @return object
     */
    public function model($name) {
        $class=null;
        if (isset(self::$_model[$name])) return self::$_model[$name];
        $classname=$name.'Model';
        if (class_exists($classname)){
            return self::$_model[$name]=new $classname($name);
        }
        return $class;
    }

    /**
     * @param $name
     * @return Driver_Db_Dao
     */
    public function db($name='') {
        return $this->getInstanceof('db')->getInstance($name);
    }

    /**
     * @param $name
     * @return PT_model
     */
    public function block($name) {
        return $this->getInstanceof('block')->getInstance($name);
    }
}

class pt extends PT_Base {

    protected static $base;

    /**
     * 框架开始调用
     */
    public function start() {
        self::$base = PT_Base::getInstance();
        //初始化加载
        $this->init();
        $this->plugin->call('app_init_start');
        //加载站点配置文件
        $this->config->register(self::import(APP_PATH . '/common/' . $this->request->getSiteCode() . '.config.php'));
        // 路由解析
        $this->plugin->call('dispatcher_start');
        self::dispatcher();
        $this->plugin->call('dispatcher_end');
        if (MODULE_NAME != 'common') {
            // 加载模块文件
            $this->config->register(self::import(APP_PATH . '/' . MODULE_NAME . '/config.php'));
            // 加载函数
            self::import(APP_PATH . '/' . MODULE_NAME . '/function.php');
        }
        // 控制器调用
        $this->app();
    }


    /**
     * 注册autoload等操作
     */
    protected function init() {
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
                function stripslashes_deep($value) {
                    $value = is_array($value) ? array_map('stripslashes_deep', $value) : (isset($value) ? stripslashes($value) : null);
                    return $value;
                }

                $_POST   = stripslashes_deep($_POST);
                $_GET    = stripslashes_deep($_GET);
                $_COOKIE = stripslashes_deep($_COOKIE);
            }
        }
        // 注册插件
        $this->plugin->register($this->config->get('plugin', array()));
    }

    protected function app() {
        //加载控制器启动的插件
        $this->plugin->call('controller_start');
        //正常模式
        $controllerFile = APP_PATH . '/' . MODULE_NAME . '/controller/' . CONTROLLER_NAME . '.php';
        $classname      = CONTROLLER_NAME . 'Controller';
        $actionname     = ACTION_NAME . 'Action';
        if (MODULE_NAME == 'plugin') {
            //插件控制器
            $controllerFile = APP_PATH . '/common/plugin/' . CONTROLLER_NAME . '/manage.php';
            $classname      = 'manageController';
            $actionname     = ACTION_NAME . 'Action';
        } elseif (!in_array(MODULE_NAME, explode(',', $this->config->get('allow_module', '')))) {
            $this->response->error(MODULE_NAME . '模块不允许访问');
        }
        if (is_file($controllerFile)) {
            include $controllerFile;
            if (class_exists($classname, false)) {
                /* @var $app PT_Controller */
                $app = new $classname();
                //加载init方法
                if (method_exists($app, 'init')) {
                    $app->init();
                }
                // 加载action
                if (method_exists($app, $actionname)) {
                    $app->$actionname();
                    if ($this->response->isAutoRender()) {
                        switch ($_GET['f']) {
                            case 'json':
                                $data = $app->view->get();
                                $this->response->jsonEncode($data);
                                break;
                            case 'jsonp':
                                $data = $app->view->get();
                                $this->response->jsonpEncode($data);
                                break;
                            case 'xml':
                                $data = $app->view->get();
                                $this->response->xmlEncode($data);
                                break;
                            default:
                                $app->display();
                        }
                    }
                } else {
                    $this->response->error("当前控制器下" . get_class($app) . "找不到指定的方法 {$_GET['a']}Action");
                }
                $this->plugin->call('controller_end');
            } else {
                $this->response->error('控制器' . CONTROLLER_NAME . '对应的文件中未找到类' . $classname);
            }
        } else {
            $this->response->error(MODULE_NAME . '模块下控制器' . CONTROLLER_NAME . 'Controller对应的文件不存在');
        }
    }

    public static function import($filename) {
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


    protected static function dispatcher() {
        self::$base->dispatcher->run();
        // 获取分组 模块和操作名称
        define('MODULE_NAME', strtolower($_GET['m']));
        define('CONTROLLER_NAME', strtolower($_GET['c']));
        define('ACTION_NAME', strtolower($_GET['a']));

        define('__SELF__', strip_tags($_SERVER['REQUEST_URI']));
        define('__APP__', rtrim($_SERVER['SCRIPT_NAME'], '/'));
        // 当前模块和分组地址
        define('__MODULE__', __APP__ . '?s=' . strtolower(empty($_GET['_m']) ? $_GET['m'] : $_GET['_m']));
        define('__URL__', __MODULE__ . '/' . CONTROLLER_NAME);
        // 当前操作地址
        define('__ACTION__', __URL__ . '/' . ACTION_NAME);
    }

    // 自动加载
    public static function autoload($class) {
        $classfile = strtolower(str_replace('_', '/', $class));
        //pt_开头的类指定目录到core
        if (strpos($classfile, 'pt/') === 0) $classfile = str_replace('pt/', 'core/', $classfile);
        if (is_file(PT_PATH . '/' . $classfile . '.php')) {
            pt::import(PT_PATH . '/' . $classfile . '.php');
        } elseif (substr($classfile, -10) == 'controller') {
            if (!pt::import(APP_PATH . '/' . MODULE_NAME . '/controller/' . substr($classfile, 0, -10) . '.php')) {
                pt::import(APP_PATH . '/common/controller/' . substr($classfile, 0, -10) . '.php');
            }
        } elseif (substr($classfile, -5) == 'model') {
            //适配ptcms_a_b这样的表
            $classfile = substr(str_replace('/', '_', $classfile), 0, -5);
            if (isset($GLOBALS['_automap']['model'][$classfile])) {
                //存在这个model
                if (isset($GLOBALS['_automap']['model'][$classfile][MODULE_NAME])) {
                    $file = $GLOBALS['_automap']['model'][$classfile][MODULE_NAME];
                } elseif (isset($GLOBALS['_automap']['model'][$classfile]['common'])) {
                    $file = $GLOBALS['_automap']['model'][$classfile]['common'];
                } else {
                    $file = current(array_slice($GLOBALS['_automap']['model'][$classfile], 0, 1));
                }
                pt::import($file);
            }
        } elseif (substr($classfile, -5) == 'block') {
            $classfile = substr($classfile, 0, -5);
            if (isset($GLOBALS['_automap']['block'][$classfile])) {
                //存在这个block
                if (isset($GLOBALS['_automap']['block'][$classfile][MODULE_NAME])) {
                    $file = $GLOBALS['_automap']['block'][$classfile][MODULE_NAME];
                } elseif (isset($GLOBALS['_automap']['block'][$classfile]['common'])) {
                    $file = $GLOBALS['_automap']['block'][$classfile]['common'];
                } else {
                    $file = current(array_slice($GLOBALS['_automap']['block'][$classfile], 0, 1));
                }
                pt::import($file);
            }
        } elseif (substr($classfile, -6) == 'plugin') {
            $classname = substr($classfile, 0, -6);
            pt::import(APP_PATH . '/common/plugin/' . $classname . '/' . $classname . '.php');
        } else {
            if (!pt::import(PT_PATH . '/library/' . $classfile . '.php') && isset($GLOBALS['_automap']['library'][$classfile])) {
                if (isset($GLOBALS['_automap']['library'][$classfile][MODULE_NAME])) {
                    $file = $GLOBALS['_automap']['library'][$classfile][MODULE_NAME];
                } elseif (isset($GLOBALS['_automap']['library'][$classfile]['common'])) {
                    $file = $GLOBALS['_automap']['library'][$classfile]['common'];
                } else {
                    $file = current(array_slice($GLOBALS['_automap']['library'][$classfile], 0, 1));
                }
                pt::import($file);
            }
        }
    }

    // 中止操作
    public static function shutdown() {
        // 判断是否有错误
        if ($e = error_get_last()) {
            if (in_array($e['type'], array(1, 4))) {
                halt($e['message'], $e['file'], $e['line']);
            }
        }
        //如果开启日志 则记录日志
        if (self::$base->config->get('log', false)) self::$base->log->build();
        // 如果自定义了close函数 则进行调用
        if (function_exists('pt_close')) {
            pt_close();
        }
    }

    // 异常处理
    public static function exception(Exception $e) {
        halt($e->getmessage(), $e->getFile(), $e->getLine());
    }

    // 错误处理
    public static function error($errno, $errstr, $errfile, $errline) {
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
}


/**
 * 文件函数
 *
 * @param string $file  需要写入的文件，系统的绝对路径加文件名
 * @param bool $content 不填写 读取 null 删除 其他 写入
 * @param string $mod   写入模式，
 * @return mixed
 */
function F($file, $content = false, $mod = '') {
    if ($content === false) {
        return is_file($file) ? file_get_contents($file) : false;
    } elseif ($content === null) {
        if (is_file($file)) {
            //删除文件
            return unlink($file);
        } elseif (is_dir($file)) {
            //删除目录
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
            $content = '<?php' . PHP_EOL . 'return ' . var_export($content, true) . ';';
        }
        if ($mod) {
            return file_put_contents($file, strval($content), LOCK_EX | $mod);
        } else {
            return file_put_contents($file, strval($content), LOCK_EX);
        }
    }
    return false;
}

function halt($msg, $file = '', $line = '') {
    if (APP_DEBUG) {
        PT_Base::getInstance()->response->setHeader();
        $e['message'] = $msg;
        $e['file']    = $file;
        $e['line']    = $line;
        include PT_PATH . '/error.tpl';
        exit;
    } else {
        PT_Base::getInstance()->response->error($msg . ' [' . $file . '(' . $line . ')]');
    }
}

/**
 * 链接生成
 *
 * @param string $method 对应方法
 * @param array $args    参数
 * @param array $ignores 忽略参数
 * @return string
 */
function U($method = '', $args = array(), $ignores = array()) {
    static $rules = null, $_method = array(), $_map = array(),$power=false;
    if ($rules === null) {
        $rules = PT_Base::getInstance()->config->get('URL_RULES');
        $_map  = PT_Base::getInstance()->config->get('map_module');
        $power  = PT_Base::getInstance()->config->get('rewritepower',false);
    }
    //忽视args中的部分参数
    if (!empty($ignores)) {
        foreach ($ignores as $key => $var) {
            if (isset($args[$key]) && $args[$key] == $var) unset($args[$key]);
        }
    }
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
    if (!empty($rules[$method]) && empty($args['_force']) && count($args) >= substr_count($rules[$method], '{')) {
        $keys  = array();
        $rule  = $rules[$method];
        $oargs = $args;
        foreach ($args as $key => $arg) {
            $keys[] = '{' . $key . '}';
            if (strpos($rule, '{' . $key . '}')) unset($oargs[$key]);
        }
        $url = clearUrl(str_replace($keys, $args, $rule));
        if (strpos($url, ']')) {
            $url = strtr($url, array('[' => '', ']' => ''));
        }
        if ($oargs) {
            return PT_DIR . $url . (strpos($url, '?') ? '&' : '?') . http_build_query($oargs);
        } else {
            return PT_DIR . $url;
        }
    } else {
        list($param['m'], $param['c'], $param['a']) = explode('.', $method);
        if (isset($_map[$param['m']])) $param['m'] = $_map[$param['m']];
        //调整顺序为m c a
        krsort($param);
        $param = array_merge($param, $args);
        if ($power){
            $url   = PT_DIR . '/' . $param['m'] . '/' . $param['c'] . '/' . $param['a'] . '.' . $_GET['f'];
            unset($param['m'], $param['c'], $param['a']);
            if ($param) {
                $url .= '?' . http_build_query($param);
            }
        }else{
            $url=__APP__.'?'.http_build_query($param);
        }
        return $url;
    }
}

/**
 * 清除url中可选参数
 *
 * @param $url
 * @return mixed
 */
function clearUrl($url) {
    while (preg_match('#\[[^\[\]]*?\{\w+\}[^\[\]]*?\]#', $url, $match)) {
        $url = str_replace($match['0'], '', $url);
    }
    return $url;
}


/**
 * 去除代码中的空白和注释
 *
 * @param string $content 代码内容
 * @return string
 */
function strip_whitespace($content) {
    $stripStr = '';
    //分析php源码
    $tokens     = token_get_all($content);
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
 * 获取自动加载的目录文件
 *
 * @return array
 */
function get_auto_map() {
    $map  = array();
    $dirs = array_unique(explode(',', trim((PT_Base::getInstance()->config->get('allow_module', null, '') . ',common'), ',')));
    foreach ($dirs as $dir) {
        $path = APP_PATH . '/' . $dir;
        if (!is_dir($path)) continue;
        $handle = opendir($path);
        while (($dirname = readdir($handle)) !== false) {
            if (in_array($dirname, array('model', 'block', 'library'))) {
                $handle1 = opendir($path . '/' . $dirname);
                while (($filename = readdir($handle1)) !== false) {
                    if (substr($filename, -4) == '.php') {
                        $map[$dirname][substr($filename, 0, -4)][$dir] = $path . '/' . $dirname . '/' . $filename;
                    }
                }
                closedir($handle1);
            }
        }
        closedir($handle);
    }
    return $map;
}

// 判断是否有html缓存
if ($_SERVER['REQUEST_METHOD'] === 'GET' && strpos($_SERVER['REQUEST_URI'], '.php') === false && is_file(PT_ROOT . $_SERVER['REQUEST_URI'])) {
    readfile(PT_ROOT . $_SERVER['REQUEST_URI']);
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

//后台运行程序
if (!empty($_GET['backRun'])) {
    //生成html
    if (function_exists('fastcgi_finish_request')) {
        fastcgi_finish_request();
    } else {
        ignore_user_abort(true);
    }
}

// 加载公共配置文件
PT_Base::getInstance()->config->register(pt::import(APP_PATH . '/common/config.php'));
pt::import(APP_PATH . '/common/function.php');

// 编译模式
if (APP_DEBUG) {
    // 开启错误输出
    ini_set('display_errors', 'on');
    // 设置错误输出级别
    error_reporting(E_ALL);
    $GLOBALS['_automap'] = get_auto_map();
} else {
    // 开启错误输出
    ini_set('display_errors', 'off');
    // 设置错误输出级别
    error_reporting(0);
    // 合并核心文件
    $runtimefile = CACHE_PATH . '/pt_runtime.php';
    if (!is_file($runtimefile)) {
        $files = array(
            PT_PATH . '/core/cache.php',
            PT_PATH . '/driver/cache/' . strtolower(PT_Base::getInstance()->config->get('cache_driver', 'file')) . '.php',
            PT_PATH . '/core/controller.php',
            PT_PATH . '/core/dispatcher.php',
            PT_PATH . '/core/log.php',
            PT_PATH . '/core/plugin.php',
            PT_PATH . '/core/view.php',
            PT_PATH . '/core/block.php',
        );
        if (PT_Base::getInstance()->config->get('mysql_driver')) {
            $files[] = PT_PATH . '/core/model.php';
            $files[] = PT_PATH . '/driver/model/' . strtolower(PT_Base::getInstance()->config->get('mysql_driver', null, 'pdo')) . '.php';
        }
        $str = "<?php ";
        $str .= "\$GLOBALS['_automap']=" . var_export(get_auto_map(), true) . ';';
        foreach ($files as $file) {
            $str .= trim(substr(php_strip_whitespace($file), 5)) . PHP_EOL;
        }
        F($runtimefile, $str);
    }
    include $runtimefile;
}


$pt = new pt();
$pt->start();
