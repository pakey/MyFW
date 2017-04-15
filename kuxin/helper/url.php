<?php

namespace Kuxin\Helper;

use Kuxin\Config;

class Url{
    
    /**
     * 获取当前URL
     *
     * @return string
     */
    public static function weixin()
    {
        $url=self::current();
        if(strpos($url,'#')){
            $url=explode('#',$url)['0'];
        }
        return $url;
    }
    
    public static function current() {
        if(PHP_SAPI=='cli'){
            return 'cli';
        }
        if(strpos($_SERVER['REQUEST_URI'],'http://')===0){
            return $_SERVER['REQUEST_URI'];
        }
        $protocol = (!empty($_SERVER['HTTPS'])
            && $_SERVER['HTTPS'] !== 'off'
            || $_SERVER['SERVER_PORT'] === 443) ? 'https://' : 'http://';
        
        $host=isset($_SERVER['HTTP_X_FORWARDED_HOST'])?$_SERVER['HTTP_X_FORWARDED_HOST']:$_SERVER['HTTP_HOST'];
        $uri=isset($_SERVER['HTTP_X_REAL_URI'])?$_SERVER['HTTP_X_REAL_URI']:$_SERVER['REQUEST_URI'];
        return $protocol.$host.$uri;
    }
    
    public static function build($method = '', $args = [], $ignores = [], $type = 'html')
    {
        static $rules = null, $_method = [], $_map = [], $power = false, $rewriteargparam = false;
        if ($rules === null) {
            $rules           = Config::get('url_rules');
            $_map            = Config::get('map_module');
            $power           = Config::get('rewritepower', false);
            $rewriteargparam = Config::get('rewriteargparam', false);
            
        }
        $type = $type ? $type : Response::type();
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
        if (!empty($rules[$method]) && empty($args['_force'])) {
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
            if ($oargs && $rewriteargparam) {
                return PT_DIR . $url . (strpos($url, '?') ? '&' : '?') . http_build_query($oargs);
            } else {
                return PT_DIR . $url;
            }
        } else {
            //非正则模式
            list($param['m'], $param['c'], $param['a']) = explode('.', $method);
            if (isset($_map[$param['m']])) $param['m'] = $_map[$param['m']];
            //调整顺序为m c a
            krsort($param);
            $param = array_merge($param, $args);
            if ($power) {
                $module_domain = Config::get('module_domain', '');
                //优化url模式
                if (!empty($module_domain[$param['m']])) {
                    // 模块域名
                    $scheme = parse_url(Config::get('siteurl'), PHP_URL_SCHEME);
                    $url    = $scheme . '://' . $module_domain[$param['m']] . PT_DIR;
                } else {
                    // 非模块域名
                    $url = PT_DIR . '/' . $param['m'];
                }
                $url .= '/' . $param['c'] . '/' . $param['a'] . '.' . $type;
                unset($param['m'], $param['c'], $param['a']);
                if ($param) {
                    $url .= '?' . http_build_query($param);
                }
            } else {
                //变量模式
                $url = __APP__ . '?' . http_build_query($param);
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