<?php

namespace Kuxin;

class Router
{
    
    static $controller = 'index';
    static $action     = 'index';
    
    /**
     * 解析controller和action
     */
    public static function dispatcher()
    {
        //判断是否需要进行rewrite转换
        if (Config::get('app.power.rewrite')) {
            self::rewrite();
        }
        //解析s变量
        if (isset($_GET['s'])) {
            if (strpos($_GET['s'], '/')) {
                if (strpos($_GET['s'], '.')) {
                    $param = explode('.', $_GET['s'], 2);
                    Response::type($param['1']);
                    $param = explode('/', $param['0']);
                } else {
                    $param = explode('/', $_GET['s']);
                }
                self::$action     = array_pop($param);
                self::$controller = implode('\\', $param);
            }
            unset($_GET['s']);
        }
    }
    
    /**
     * 正则模式解析
     */
    public static function rewrite()
    {
        if ($router = Config::get('app.router.rewrite')) {
            foreach ($router as $rule => $url) {
                if (preg_match('{' . $rule . '}isU', $_GET['s'], $match)) {
                    unset($match['0']);
                    if (strpos($url, '?')) {
                        list($url, $query) = explode('?', $url);
                    }
                    $_GET['s'] = rtrim($url, '/');
                    if ($match && !empty($query)) {//组合后面的参数
                        $param = explode('&', $query);
                        if (count($param) == count($match) && $var = array_combine($param, $match)) {
                            $_GET = array_merge($_GET, $var);
                        }
                    }
                    break;
                }
            }
        }
    }
    
}