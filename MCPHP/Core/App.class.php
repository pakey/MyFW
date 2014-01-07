<?php

/***************************************************************
 *   $Program: MCPHP FarmeWork (A Open PHP Application FarmeWrok) $
 *    $Author: pakey $
 *     $Email: Pakey@ptcms.com $
 * $Copyright: 2009 - 2012 Ptcms Studio $
 *      $Link: http://www.ptcms.com $
 *   $License: http://www.ptcms.com/service/license.html $
 *      $Date: 2013-04-25 20:54:02 +0800 (星期四, 25 四月 2013) $
 *      $File: App.class.php $
 *  $Revision: 4 $
 *      $Desc:
 **************************************************************/

defined('MC_PATH') || exit('Permission denied');

class App
{
    // 初始化
    static public function init()
    {
        // 加载项目配置文件
        if (is_file(CONFIG_PATH . 'config.php'))
            C(require_cache(CONFIG_PATH . 'config.php'));
        // 加载项目别名定义
        if (is_file(CONFIG_PATH . 'alias.php'))
            alias_import(require_cache(CONFIG_PATH . 'alias.php'));
        // 加载项目公共文件
        if (is_file(COMMON_PATH . 'common.php'))
            require_cache(COMMON_PATH . 'common.php');
        // 加载项目函数文件
        if (is_file(COMMON_PATH . 'function.php'))
            require_cache(COMMON_PATH . 'function.php');
        // 路由分析
        Dispatcher::init();
        // 定义项目基础加载路径
        if (isset($_GET['m'])) {
            $config_path = CONFIG_PATH . MODULE_NAME . '/';
            $common_path = COMMON_PATH . MODULE_NAME . '/';
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
        App::session();
    }

    static public function execute()
    {
        $className = CONTROLLER_NAME . 'Controller';
        if (class_exists($className)) {
            $controler = new $className();
            $controler->run();
        } else {
            if (APP_DEBUG) {
                header('Content-Type:text/html; charset=utf-8');
                p($_GET);
                exit('控制器' . $className . '不存在');
            } else {
                App::err404();
            }
        }
    }

    // 运行
    static public function run()
    {
        //项目初始化
        App::init();
        //项目执行
        App::execute();
    }

    // session 初始化设置
    static protected function session()
    {
        $option = C('SESSION_OPTIONS');
        if (count($option) > 0 or C('SESSION_TYPE')) {
            if (isset($option['prefix'])) C('SESSION_PREFIX', $option['prefix']);
            if (isset($option['id'])) {
                session_id($option['id']);
            }
            ini_set('session.auto_start', 0);
            if (isset($option['name'])) session_name($option['name']);
            if (isset($option['path'])) session_save_path($option['path']);
            if (isset($option['domain'])) ini_set('session.cookie_domain', $option['domain']);
            if (isset($option['expire'])) ini_set('session.gc_maxlifetime', $option['expire']);
            if (isset($option['use_trans_sid'])) ini_set('session.use_trans_sid', $option['use_trans_sid'] ? 1 : 0);
            if (isset($option['use_cookies'])) ini_set('session.use_cookies', $option['use_cookies'] ? 1 : 0);
            if (isset($option['cache_limiter'])) session_cache_limiter($option['cache_limiter']);
            if (isset($option['cache_expire'])) session_cache_expire($option['cache_expire']);
            if (isset($option['type'])) C('SESSION_TYPE', $option['type']);
            if (C('SESSION_TYPE')) { // 读取session驱动
                $class = 'Session' . ucwords(strtolower(C('SESSION_TYPE')));
                // 检查驱动类
                if (require_cache(MC_PATH . 'Extend/Driver/Session/' . $class . '.class.php')) {
                    $hander = new $class();
                    $hander->execute();
                } else {
                    // 类没有定义
                    halt('未定义此项驱动: ' . $class);
                }
            }
        }
        if (C('SESSION_AUTO_START')) session_start();
    }

    static public function err404($_404Page = '')
    {
        header("HTTP/1.1 404 Not Found");
        if ($_404Page == '') {
            $_404Page = C('URL_404_PAGE');
            if (empty($_404Page)) {
                $_404Page = MC_ROOT . '404.html';
            }
        }
        if (is_file($_404Page)) {
            exit(readfile($_404Page));
        }
        exit('页面404配置错误，找不到您访问的页面！');
    }
}