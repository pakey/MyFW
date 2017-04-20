<?php

namespace Kuxin;

use Kuxin\Helper\Json;
use Kuxin\Helper\Jsonp;
use Kuxin\Helper\Xml;
use ReflectionClass;

class Kuxin
{
    
    public static function init()
    {
        // 注册AUTOLOAD方法
        spl_autoload_register([__CLASS__, 'autoload']);
        //程序关闭
        register_shutdown_function([__CLASS__, 'shutdown']);
        // 设定错误和异常处理
        //set_error_handler(array(__CLASS__, 'error'));
        //set_exception_handler([__CLASS__, 'exception']);
        // 注册配置
        Config::register(Loader::import(KX_ROOT.'/app/config/kuxin.php'));
        // 时区
        date_default_timezone_set('PRC');
        // 记录开始运行时间
        Registry::set('_startTime', microtime(true));
        // 记录sql执行次数
        Registry::set('_sql', []);
        Registry::set('_sqlnum', 0);
        // 缓存读取次数
        Registry::set('_cacheRead', 0);
        Registry::set('_cacheHit', 0);
        // 缓存写入次数
        Registry::set('_cacheWrite', 0);
        // 记录内存初始使用
        Registry::set('_startUseMems', memory_get_usage());
        // 记录网络请求
        Registry::set('_http', []);
        Registry::set('_httpnum', 0);
        
    }
    
    public static function start()
    {
        self::init();
        Router::dispatcher();
        $controllerName = 'app\\controller\\' . Router::$controller;
        /** @var \Kuxin\Controller $controller */
        $controller = Loader::instance($controllerName);
        $actionName = Router::$action;
        $controller->init();
        if (method_exists($controller, $actionName)) {
            $return = $controller->$actionName();
            if (Response::isAutoRender()) {
                switch (Response::getType()) {
                    case 'json':
                        $body = Json::encode($return);
                        break;
                    case 'jsonp':
                        $body = Jsonp::encode($return);
                        break;
                    case 'xml':
                        $body = Xml::encode($return);
                        break;
                    default:
                        if (is_string($return)) {
                            $body = $return;
                        } else if (Request::isAjax()) {
                            Response::setType('json');
                            $body = Json::encode($return);
                        } else {
                            $body = View::make(null, $return);
                        }
                }
            } else {
                $body = $return;
            }
            //设置输出内容
            Response::setBody($body);
        } else {
            trigger_error('控制器[' . $controllerName . ']对应的方法[' . $actionName . ']不存在', E_USER_ERROR);
        }
    }
    
    protected static function autoload($classname)
    {
        $file = KX_ROOT . '/' . strtr(strtolower($classname), '\\', '/') . '.php';
        Loader::import($file);
    }
    
    public static function shutdown()
    {
        //如果开启日志 则记录日志
        if (Config::get('log.power')) {
            Log::build();
        }
    }
}
function dump($arr)
{
    echo '<pre>';
    print_r($arr);
    echo '</pre>', PHP_EOL;
}


include __DIR__ . '/loader.php';

Kuxin::start();