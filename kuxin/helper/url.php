<?php

namespace Kuxin\Helper;

use Kuxin\Config;
use Kuxin\Response;
use Kuxin\Router;

class Url
{
    
    /**
     * 获取当前URL
     *
     * @return string
     */
    public static function weixin()
    {
        $url = self::current();
        if (strpos($url, '#')) {
            $url = explode('#', $url)['0'];
        }
        return $url;
    }
    
    public static function current()
    {
        if (PHP_SAPI == 'cli') {
            return 'cli';
        }
        if (strpos($_SERVER['REQUEST_URI'], 'http://') === 0) {
            return $_SERVER['REQUEST_URI'];
        }
        $protocol = (!empty($_SERVER['HTTPS'])
            && $_SERVER['HTTPS'] !== 'off'
            || $_SERVER['SERVER_PORT'] === 443) ? 'https://' : 'http://';
        
        $host = isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : $_SERVER['HTTP_HOST'];
        $uri  = isset($_SERVER['HTTP_X_REAL_URI']) ? $_SERVER['HTTP_X_REAL_URI'] : $_SERVER['REQUEST_URI'];
        return $protocol . $host . $uri;
    }
    
    public static function build($method = '', $args = [], $ignores = [], $type = 'html')
    {
        static $rules = null, $_method = [], $power = false;
        if ($rules === null) {
            $rules = Config::get('rewrite.url_rules');
            $power = Config::get('rewrite.power', false);
            
        }
        //忽视args中的部分参数
        if (!empty($ignores)) {
            foreach ($ignores as $key => $var) {
                if (isset($args[$key]) && $args[$key] == $var) unset($args[$key]);
            }
        }
        if (empty($_method[$method])) {
            if ($method === '') {
                $_method[$method] = strtolower(Router::$controller . '.' . Router::$action);
            } elseif (substr_count($method, '.') == 0) {
                $_method[$method] = strtolower(Router::$controller . '.' . $method);
            } else {
                $_method[$method] = strtolower($method);
            }
        }
        $method = $_method[$method];
        if ($power && isset($rules[$method])) {
            $keys  = [];
            $rule  = $rules[$method];
            $oargs = $args;
            foreach ($args as $key => &$arg) {
                $keys[] = '{' . $key . '}';
                $arg    = rawurlencode(urldecode($arg));
                if (strpos($rule, '{' . $key . '}')) unset($oargs[$key]);
            }
            $url = self::clearUrl(str_replace($keys, $args, $rule));
            if (strpos($url, ']')) {
                $url = strtr($url, ['[' => '', ']' => '']);
            }
            if (strpos($url, '{page}')) $url = str_replace('{page}', 1, $url);
            return '/' . $url;
        } else {
            $type = $type ? $type : Response::getType();
            $url  = '/' . strtr($method, '.', '/') . '.' . $type;
            if ($args) {
                $url .= '?' . http_build_query($args);
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
    protected static function clearUrl($url)
    {
        while (preg_match('#\[[^\[\]]*?\{\w+\}[^\[\]]*?\]#', $url, $match)) {
            $url = str_replace($match['0'], '', $url);
        }
        return $url;
    }
}