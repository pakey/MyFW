<?php

namespace Kuxin;

class Router
{
    static $module='index';
    static $controller='index';
    static $action='index';
    
    public static function dispatcher()
    {
        if(!empty($_GET['s'])){
            if (strpos($_GET['s'], '.')) {
                $param     = explode('.', $_GET['s'], 2);
                Response::type($param['1']);
                $param     = explode('/', $param['0']);
            } else {
                $param = explode('/', $_GET['s']);
            }
            $var['m'] = isset($param['0']) ? array_shift($param) : 'index';
            $var['c'] = isset($param['0']) ? array_shift($param) : 'index';
            $var['a'] = isset($param['0']) ? array_shift($param) : 'index';
            while ($k = each($param)) {
                $var[$k['value']] = current($param);
                next($param);
            };
            unset($_GET['s']);
        }
    }
    
}