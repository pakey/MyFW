<?php

namespace Kuxin;

use Kuxin\Ext\Json;
use Kuxin\Ext\Jsonp;
use Kuxin\Ext\Xml;
use ReflectionClass;

class Kuxin
{
    
    static $_importFiles = [];
    static $_class       = [];
    
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
        $controllerName = 'app\\controller\\' . Router::$module . '\\' . Router::$controller;
        $controller     = self::instance($controllerName);
        $actionName     = Router::$action;
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
                        if (Request::isAjax()) {
                            $body = Json::encode($return);
                        } elseif (is_array($return)) {
                            $body = View::make(null, $return);
                        } else {
                            $body = $return;
                        }
                        break;
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
        self::import($file);
    }
    
    public static function import($filename)
    {
        if (!isset(self::$_importFiles[$filename])) {
            self::$_importFiles[$filename] = include $filename;
        }
        return self::$_importFiles[$filename];
    }
    
    public static function instance($class, $args = [])
    {
        $key = md5($class . '_' . serialize($args));
        if (empty(self::$_class[$key])) {
            self::$_class[$key] = (new ReflectionClass($class))->newInstanceArgs($args);;
        }
        return self::$_class[$key];
    }
}


date_default_timezone_set('PRC');

//项目根目录
defined('PT_ROOT') || define('PT_ROOT', str_replace('\\', '/', dirname(dirname($_SERVER['SCRIPT_FILENAME']))));

Kuxin::start();