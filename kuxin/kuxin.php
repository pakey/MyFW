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
        // 设定错误和异常处理
        //register_shutdown_function([__CLASS__, 'shutdown']);
        //set_error_handler(array(__CLASS__, 'error'));
        //set_exception_handler([__CLASS__, 'exception']);
        // 注册AUTOLOAD方法
        spl_autoload_register([__CLASS__, 'autoload']);
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
                switch (Response::type()) {
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
                            Response::type('json');
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
        $file = PT_ROOT . '/' . strtr(strtolower($classname), '\\', '/') . '.php';
        Loader::import($file);
    }
    
    
}


include __DIR__ . '/loader.php';

date_default_timezone_set('PRC');
//项目根目录
defined('PT_ROOT') || define('PT_ROOT', str_replace('\\', '/', dirname(dirname($_SERVER['SCRIPT_FILENAME']))));

Kuxin::start();