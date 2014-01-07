<?php

/***************************************************************
 *   $Program: MCPHP FarmeWork (A Open PHP Application FarmeWrok) $
 *    $Author: pakey $
 *     $Email: Pakey@ptcms.com $
 * $Copyright: 2009 - 2012 Ptcms Studio $
 *      $Link: http://www.ptcms.com $
 *   $License: http://www.ptcms.com/service/license.html $
 *      $Date: 2013-11-16 10:36:44 +0800 (周六, 2013-11-16) $
 *      $File: MCPHP.class.php $
 *  $Revision: 164 $
 *      $Desc:
 **************************************************************/

defined('MC_PATH') || exit('Permission denied');

class MCPHP
{
    static public function init()
    {
        // 配置文件
        //[RUNTIME]
        C(include MC_PATH . 'Config/convention.php');
        alias_import(array(
            'Dispatcher' => MC_PATH . 'Core/Dispatcher.class.php',
            'App' => MC_PATH . 'Core/App.class.php',
            'Controller' => MC_PATH . 'Core/Controller.class.php',
            'Model' => MC_PATH . 'Core/Model.class.php',
            'Db' => MC_PATH . 'Core/Db.class.php',
            'Widget' => MC_PATH . 'Core/Widget.class.php',
            'View' => MC_PATH . 'Core/View.class.php',
            'Debug' => MC_PATH . 'Core/Debug.class.php',
            'Cache' => MC_PATH . 'Core/Cache.class.php',
        ));
        //[/RUNTIME]
        // 设定错误和异常处理
        register_shutdown_function(array(__CLASS__, 'fatalError'));
        set_error_handler(array(__CLASS__, 'appError'));
        set_exception_handler(array(__CLASS__, 'appException'));
        // 注册AUTOLOAD方法
        spl_autoload_register(array(__CLASS__, 'autoLoad'));
        //检测目录存在情况
        //[RUNTIME]
        MCPHP::checkAppDir();
        MCPHP::checkCacheDir();
        //[/RUNTIME]
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
        //[RUNTIME]
        /* 检测是否需要部署模式
        */
        if (!APP_DEBUG and !is_file(COMPILE_FILE)) {
            MCPHP::compile();
        }
        //[/RUNTIME]
    }

    //[RUNTIME]
    // 检查缓存目录(Runtime) 如果不存在则自动创建
    static private function checkCacheDir()
    {
        if (!is_dir(CACHE_PATH)) {
            mkdir(CACHE_PATH);
        } elseif (!is_writeable(CACHE_PATH)) {
            header('Content-Type:text/html; charset=utf-8');
            exit('目录 [ ' . CACHE_PATH . ' ] 不可写！');
        }
        if (!is_dir(CACHE_PATH . 'template/')) mkdir(CACHE_PATH . 'template/'); // 模板缓存目录
        if (!is_dir(CACHE_PATH . 'log/')) mkdir(CACHE_PATH . 'log/'); // 日志目录
        if (!is_dir(CACHE_PATH . 'dbfield/')) mkdir(CACHE_PATH . 'dbfield/'); // 字段缓存目录
        if (!is_dir(CACHE_PATH . 'data/')) mkdir(CACHE_PATH . 'data/'); // 数据缓存目录
        if ((C('SESSION_TYPE') == 'File' or C('SESSION_TYPE') == '') && !is_dir(CACHE_PATH . 'session/')) mkdir(CACHE_PATH . 'session/'); // session缓存目录
        return true;
    }

    // 创建项目目录结构
    static private function checkAppDir()
    {
        // 没有创建项目目录的话自动创建
        if (is_dir(APP_PATH)) {
            //已创建 跳出
            return;
        } else {
            mkdir(APP_PATH, 0755, true);
        }
        if (is_writeable(APP_PATH)) {
            $dirs = array(
                COMMON_PATH,
                CONTROLLER_PATH,
                MODEL_PATH,
                CONFIG_PATH,
                CONFIG_PATH,
                LIBRARY_PATH,
                WIDGET_PATH,
                TPL_PATH,
                CACHE_PATH,
                PUBLIC_PATH,
                DATA_PATH,
            );
            //分组模式
            if (C('APP_MODULE_MODE')) {
                $modulelist = C('APP_MODULE_LIST');
                if (empty($modulelist)) $modulelist = 'Default';
                $module = explode(',', $modulelist);
                foreach ($module as $v) {
                    $dirs[] = CONTROLLER_PATH . $v . '/';
                    $dirs[] = CONFIG_PATH . $v . '/';
                }
            }
            foreach ($dirs as $dir) {
                if (!is_dir($dir)) mkdir($dir, 0755, true);
            }
            // 写入目录安全文件
            MCPHP::buildDirSecure($dirs);
            // 写入初始配置文件
            if (!is_file(CONFIG_PATH . 'config.php'))
                F(CONFIG_PATH . 'config.php', "<?php\nreturn array(\n\t//'配置项'=>'配置值'\n);\n?>");
            // 写入测试Action
            $indexActionFile = C('APP_MODULE_MODE') ? CONTROLLER_PATH . C('DEFAULT_MODULE') . '/IndexController.class.php' : CONTROLLER_PATH . 'IndexController.class.php';
            if (!is_file($indexActionFile)) {
                $content = file_get_contents(MC_PATH . 'Tpl/defaultcontroller.tpl');
                F($indexActionFile, $content);
            }
        } else {
            header('Content-Type:text/html; charset=utf-8');
            exit('项目目录不可写，目录无法自动生成！<BR>请手动生成项目目录~');
        }
    }

    // 生成目录安全文件
    static private function buildDirSecure($dirs)
    {
        if (C('BUILD_DIR_SECURE')) {
            $files = explode(',', C('DIR_SECURE_FILENAME'));
            $content = C('DIR_SECURE_CONTENT');
            // 自动写入目录安全文件
            foreach ($files as $filename) {
                foreach ($dirs as $dir)
                    F($dir . $filename, $content);
            }
        }
    }

    //[/RUNTIME]

    /**
     * 系统自动加载ThinkPHP类库
     * 并且支持配置自动加载路径
     *
     * @param string $class 对象类名
     * @return bool
     */
    static public function autoLoad($class)
    {

        if (alias_import($class)) return;
        $module = intval(C('APP_MODULE_MODE'));
        $file = $class . '.class.php';
        if (substr($class, -10) == 'Controller') { // 加载控制器
			require_cache(CONTROLLER_PATH . $module . $file);
			return require_array(array(
                CONTROLLER_PATH . $file,
                CONTROLLER_PATH . $module . $file), true);
        } elseif (substr($class, -5) == 'Model') { // 加载模型
			return require_array(array(
                MODEL_PATH . $file), true);
        } elseif (substr($class, 0, 2) == 'Db') { // 加载数据库驱动
			return require_array(array(
                MC_PATH . 'Extend/Driver/Db/' . $file,
                MC_PATH . 'Driver/Db/' . $file), true);
        } elseif (substr($class, 0, 3) == 'Tpl') { // 加载数据库驱动
			return require_array(array(
                MC_PATH . 'Extend/Driver/Tpl/' . $file,
                MC_PATH . 'Driver/Tpl/' . $file), true);
        } elseif (substr($class, 0, 5) == 'Cache') { // 加载缓存驱动
			return  require_array(array(
                MC_PATH . 'Extend/Driver/Cache/' . $file,
                MC_PATH . 'Driver/Cache/' . $file), true);
        } elseif (substr($class, -6) == 'Widget') { // 加载缓存驱动
			return require_array(array(
                WIDGET_PATH . $file), true);
        } else {
			return require_array(array(
                LIBRARY_PATH . $file), true);
        }
    }

    /**
     * 自定义异常处理
     * @access public
     * @param mixed $e 异常对象
     */
    static public function appException($e)
    {
        halt($e->__toString());
    }

    /**
     * 自定义错误处理
     * @access public
     * @param int $errno      错误类型
     * @param string $errstr  错误信息
     * @param string $errfile 错误文件
     * @param int $errline    错误行数
     * @return void
     */
    static public function appError($errno, $errstr, $errfile, $errline)
    {
        switch ($errno) {
            case E_ERROR:
            case E_PARSE:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
            case E_USER_ERROR:
                $errorStr = "$errstr " . $errfile . " 第 $errline 行.";
                Debug::addMsg($errorStr, 'usererror');
                halt($errorStr);
                break;
            case E_STRICT:
            case E_USER_WARNING:
            case E_USER_NOTICE:
            default:
                $errorStr = "[$errno] $errstr " . $errfile . " 第 $errline 行.";
                Debug::addMsg($errorStr, 'usererror');
                break;
        }
    }

    // 致命错误捕获
    static public function fatalError()
    {
        if ($e = error_get_last()) {
            MCPHP::appError($e['type'], $e['message'], $e['file'], $e['line']);
        }
    }

    /**
     * 取得对象实例 支持调用类的静态方法
     * @param string $name   类名
     * @param string $method 方法名，如果为空则返回实例化对象
     * @param array $args    调用参数
     * @return object
     */
    static public function getInstanceOf($name, $method = '', $args = array())
    {
        static $_instance = array();
        $identify = empty($args) ? $name . $method : $name . $method . MCPHP::toGuidString($args);
        if (!isset($_instance[$identify])) {
            if (class_exists($name)) {
                $o = new $name();
                if (method_exists($o, $method)) {
                    if (!empty($args)) {
                        $_instance[$identify] = call_user_func_array(array(&$o, $method), $args);
                    } else {
                        $_instance[$identify] = $o->$method();
                    }
                } else
                    $_instance[$identify] = $o;
            } else
                halt('实例化对象失败，找不到对应的类:' . $name);
        }
        return $_instance[$identify];
    }

    /**
     * 根据PHP各种类型变量生成唯一标识号
     *
     * @param mixed $mix 变量
     * @return string
     */
    static public function toGuidString($mix)
    {
        if (is_object($mix) && function_exists('spl_object_hash')) {
            return spl_object_hash($mix);
        } elseif (is_resource($mix)) {
            $mix = get_resource_type($mix) . strval($mix);
        } else {
            $mix = serialize($mix);
        }
        return md5($mix);
    }

    //[RUNTIME]
    /* 编译项目*/
    static protected function compile()
    {
        $content = "<?php ";
        $list = array(
            MC_PATH . 'Common/common.php',
            MC_PATH . 'Core/MCPHP.class.php',
            MC_PATH . 'Core/Dispatcher.class.php',
            MC_PATH . 'Core/App.class.php',
            MC_PATH . 'Core/Controller.class.php',
            MC_PATH . 'Core/Model.class.php',
            MC_PATH . 'Core/Db.class.php',
            MC_PATH . 'Core/Widget.class.php',
            MC_PATH . 'Core/View.class.php',
            MC_PATH . 'Core/Debug.class.php',
            MC_PATH . 'Core/Cache.class.php',
            MC_PATH . 'Driver/Tpl/Tpl' . C('TPL_DRIVER') . '.class.php',
            MC_PATH . 'Driver/Cache/Cache' . C('DATA_CACHE_TYPE') . '.class.php',
            MC_PATH . 'Driver/Db/Db' . C('DB_TYPE') . '.class.php',
        );
        foreach ($list as $file) {
            $content .= MCPHP::getCompileFileContent($file);
        }
        $alias = include MC_PATH . 'Config/alias.php';
        $content .= 'alias_import(' . var_export($alias, true) . ');';
        $content .= "C(" . var_export(C(), true) . ');';
        //$content=strip_whitespace(str_replace("defined('MC_PATH') || exit('Permission denied');",' ',$content));
        $content = (str_replace("defined('MC_PATH') || exit('Permission denied');", ' ', $content));
        F(COMPILE_FILE, strip_whitespace($content));
    }

    static protected function getCompileFileContent($filename)
    {
        $content = F($filename);
        // 替换预编译指令
        $content = preg_replace('/\/\/\[RUNTIME\](.*?)\/\/\[\/RUNTIME\]/s', '', $content);
        $content = substr(trim($content), 5);
        if ('?>' == substr($content, -2))
            $content = substr($content, 0, -2);
        return $content;
    }
    //[/RUNTIME]
}