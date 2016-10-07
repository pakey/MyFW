<?php

namespace ptcms;

class ptcms
{
    
    public static function start()
    {
        //初始化
        self::init();
        //路由分发
        self::route();
        //调用控制器
        self::controller();
    }
    
    public static function init()
    {
        Plugin::hook('before_init');
        //定义系统根路径
        defined('PTCMS_ROOT') || define('PTCMS_ROOT', dirname(str_replace(strtr('/ptcms/framework/src/ptcms.php', '/', DIRECTORY_SEPARATOR), '', __FILE__)));
        //定义项目路径
        defined('APP_PATH') || define('APP_PATH', PTCMS_ROOT . '/application');
        defined('CACHE_PATH') || define('CACHE_PATH', PTCMS_ROOT . '/runtime');
        defined('PUBLIC_PATH') || define('PUBLIC_PATH', PTCMS_ROOT . '/public');
        defined('PUBLIC_URL') || define('PUBLIC_URL', '/');
        //基础参数
        // 记录开始运行时间
        Registry::set('_startTime', microtime(true));
        // 记录sql执行次数
        Registry::set('_sql', []);
        Registry::set('_sqlnum', 0);
        // 缓存读取次数
        Registry::set('_cacheRead', 0);
        // 缓存写入次数
        Registry::set('_cacheWrite', 0);
        // 记录内存初始使用
        Registry::set('_startUseMems', memory_get_usage());
        //加载项目配置文件
        Config::load(APP_PATH . '/config/app.php');
        
        //判断设备
        if(Config::get('app.mobileswitch',false)){
            $host=$_SERVER['HTTP_HOST'];
            if(Request::isMobile()){
                //手机端
                if(parse_url(Config::get('siteurl.web'),PHP_URL_HOST)==$host){
                    //电脑端 跳转
                    Response::redirect(Config::get('siteurl.mobile').$_SERVER['REQUEST_URI']);
                }
                Config::set('app.mode','mobile');
            }else{
                //其他设备
                if(parse_url(Config::get('siteurl.mobile'),PHP_URL_HOST)==$host){
                    //手机端 跳转到pc
                    Response::redirect(Config::get('siteurl.web').$_SERVER['REQUEST_URI']);
                }
            }
        }
        
        //设置时区（中国）
        date_default_timezone_set(Config::get('app.timezone', 'PRC'));
        if (Config::get('app.debug', false)) {
            // 开启错误输出
            ini_set('display_errors', 'on');
            // 设置错误输出级别
            error_reporting(E_ALL);
        } else {
            //隐藏php版本
            ini_set('expose_php', 0);
            // 开启错误输出
            ini_set('display_errors', 'off');
            // 设置错误输出级别
            error_reporting(0);
        }
        Plugin::hook('after_init');
    }
    
    public static function route()
    {
        Plugin::hook('before_route');
        Config::load(APP_PATH . '/config/route.php', 'route');
        Route::resolve();
        Plugin::hook('after_route');
    }
    
    public static function controller()
    {
        Plugin::hook('before_controller');
        $controllerName = 'controller\\' . Route::$controller;
        $action         = Route::$action;
        $controllerFile = APP_PATH . '/' . str_replace('\\', DIRECTORY_SEPARATOR, $controllerName) . '.php';
        if (is_file($controllerFile)) {
            Loader::import($controllerFile);
            $controller = Loader::instance($controllerName);
            if (method_exists($controller, 'init')) {
                $controller->init();
            }
            if (method_exists($controller, $action)) {
                $return = $controller->$action();
                if (Response::isAutoRender()) {
                    switch (Response::type()) {
                        case 'json':
                            Response::setBody(Json::encode($return));
                            break;
                        case 'jsonp':
                            Response::setBody(Jsonp::encode($return));
                            break;
                        case 'xml':
                            Response::setBody(Xml::encode($return));
                            break;
                        default:
                            if (is_string($return)) {
                                Response::setBody($return);
                            } elseif (is_array($return)) {
                                Response::setBody(View::make(null, $return));
                            }
                            break;
                    }
                } else {
                    Response::setBody($return);
                }
            } else {
                
                Response::error('控制器[' . $controllerName . ']对应的方法[' . $action . ']不存在');
            }
        } else {
            Response::error('控制器对应的文件[' . $controllerFile . ']不存在');
        }
        Plugin::hook('after_controller');
    }
}