<?php

/***************************************************************
 *   $Program: MCPHP FarmeWork (A Open PHP Application FarmeWrok) $
 *    $Author: pakey $
 *     $Email: Pakey@ptcms.com $
 * $Copyright: 2009 - 2012 Ptcms Studio $
 *      $Link: http://www.ptcms.com $
 *   $License: http://www.ptcms.com/service/license.html $
 *      $Date: 2013-06-04 15:45:11 +0800 (星期二, 04 六月 2013) $
 *      $File: Dispatcher.class.php $
 *  $Revision: 159 $
 *      $Desc: 路由分析类
 **************************************************************/

defined('MC_PATH') || exit('Permission denied');

class Dispatcher
{
    static public function init()
    {
        // 判断是否开启路由
        if (C('URL_ROUTER_MODE')) {
            Dispatcher::router();
        } else {
            // 当使用兼容模式?s=a/b/c
            //pathinfo处理
            Dispatcher::pathinfo();
        }
        // 获取分组 模块和操作名称
        if (isset($_GET['m'])) {
            define('MODULE_NAME', $_GET['m']);
        }
        define('CONTROLLER_NAME', $_GET['c']);
        define('ACTION_NAME', $_GET['a']);
        $depr = C('URL_PATHINFO_DEPR');
        // URL常量
        if (substr($_SERVER['REQUEST_URI'], -9) == 'index.php') {
            //兼容nginx
            $_SERVER['REQUEST_URI'] = substr($_SERVER['REQUEST_URI'], 0, -9);
        }
        define('__SELF__', strip_tags($_SERVER['REQUEST_URI']));
        // 当前项目地址
        if (strpos($_SERVER['REQUEST_URI'], basename($_SERVER['SCRIPT_NAME'])) !== false) {
            define('__APP__', strip_tags(rtrim($_SERVER['SCRIPT_NAME'], '/')));
        } else {
            define('__APP__', strip_tags(rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\')));

        }

        // 当前模块和分组地址
        if (isset($_GET['m'])) {
            define('__MODULE__', __APP__ . '/' . MODULE_NAME);
            define('__URL__', __MODULE__ . $depr . CONTROLLER_NAME);
        } else {
            define('__URL__', __APP__ . $depr . CONTROLLER_NAME);
        }
        // 当前操作地址
        define('__ACTION__', __URL__ . $depr . ACTION_NAME);
        //保证$_REQUEST正常取值
        $_REQUEST = array_merge($_POST, $_GET);
    }

    static private function pathinfo()
    {
        if (C('URL_MODE') == 1) {
            if (isset($_GET['s'])) {
                $_SERVER['PATH_INFO'] = $_GET['s'];
                unset($_GET['s']);
            }
        }
        if (empty($_SERVER['PATH_INFO'])) {
            $types = explode(',', C('URL_PATHINFO_FETCH'));
            foreach ($types as $type) {
                if (!empty($_SERVER[$type])) {
                    $_SERVER['PATH_INFO'] = (0 === strpos($_SERVER[$type], $_SERVER['SCRIPT_NAME'])) ?
                        substr($_SERVER[$type], strlen($_SERVER['SCRIPT_NAME'])) : $_SERVER[$type];
                    break;
                } elseif (0 === strpos($type, ':')) { // 支持函数判断
                    $_SERVER['PATH_INFO'] = call_user_func(substr($type, 1));
                    break;
                }
            }
        }
        // nginx获取pathinfo
        if (empty($_SERVER['PATH_INFO'])) {
            $key = key($_GET);
            if (isset($key) && $_GET[$key] === '') {
                $_SERVER['PATH_INFO'] = $key;
                unset($_GET[$key]);
            }
        }

        $depr = C('URL_PATHINFO_DEPR');
        if (isset($_SERVER['PATH_INFO'])) {
            $paths = explode($depr, trim($_SERVER['PATH_INFO'], '/'));
        } else {
            $paths = array();
        }
        if (C('APP_MODULE_MODE')) {
            if (!empty($paths['0']) && in_array(strtolower($paths['0']), explode(',', strtolower(C('APP_MODULE_LIST'))))) {
                $_GET['m'] = ucfirst(array_shift($paths));
            } else {
                $_GET['m'] = C('DEFAULT_MODULE');
            }
        }
        //控制器
        $_GET['c'] = Ucfirst(empty($paths[0]) ? C('DEFAULT_CONTROLLER') : array_shift($paths));
        //方法
        $_GET['a'] = strtolower(empty($paths[0]) ? C('DEFAULT_ACTION') : array_shift($paths));
        // 解析剩余的URL参数
        $var = array();
        preg_replace('@(\w+)\/([^\/]+)@e', '$var[\'\\1\']=strip_tags(\'\\2\');', implode('/', $paths));
        $_GET = array_merge($var, $_GET);
    }

    static private function router()
    {
        $root = rtrim(str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']), '/');
        $routes = C('URL_ROUTE_RULES');
        if (!empty($routes)) {
            $regx = strtolower(trim(str_replace($root, '', $_SERVER['REQUEST_URI']), '/'));
            // 分隔符替换 确保路由定义使用统一的分隔符
            foreach ($routes as $rule => $route) {
                if (0 === strpos($rule, '/') && preg_match($rule, $regx, $matches)) { // 正则路由
                    return Dispatcher::parseRegex($matches, $route, $regx);
                }
            }
            // 去除?后面内容在尝试一次
            $pos = strpos($regx, '?');
            if ($pos !== false) {
                $regx = rtrim(substr($regx, 0, $pos), '/');
                foreach ($routes as $rule => $route) {
                    if (0 === strpos($rule, '/') && preg_match($rule, $regx, $matches)) { // 正则路由
                        return Dispatcher::parseRegex($matches, $route, $regx);
                    }
                }
            }
        }
        // 正则路由失败 进行pathinfo匹配
        Dispatcher::pathinfo();
    }

    // 解析正则路由
    // '路由正则'=>'[模块/控制器/操作]?参数1=值1&参数2=值2...'
    // '路由正则'=>array('[模块/控制器/操作]?参数1=值1&参数2=值2...','额外参数1=值1&额外参数2=值2...')
    // '路由正则'=>'外部地址'
    // '路由正则'=>array('外部地址','重定向代码')
    // 参数值和外部地址中可以用动态变量 采用 :1 :2 的方式
    // '/new\/(\d+)\/(\d+)/'=>array('News/read?id=:1&page=:2&cate=1','status=1'),
    // '/new\/(\d+)/'=>array('/new.php?id=:1&page=:2&status=1','301'), 重定向
    private function parseRegex($matches, $route, $regx)
    {
        // 获取路由地址规则
        $url = is_array($route) ? $route[0] : $route;
        $url = preg_replace('/:(\d+)/e', '$matches[\\1]', $url);
        if (0 === strpos($url, '/') || 0 === strpos($url, 'http')) { // 路由重定向跳转
            header("Location: $url", true, (is_array($route) && isset($route[1])) ? $route[1] : 301);
            exit;
        } else {
            // 解析路由地址
            $var = Dispatcher::parseUrl($url);
            // 解析剩余的URL参数
            $regx = substr_replace($regx, '', 0, strlen($matches[0]));
            if ($regx) {
                preg_replace('@(\w+)\/([^,\/]+)@e', '$var[strtolower(\'\\1\')]=strip_tags(\'\\2\');', $regx);
            }
            // 解析路由自动传入参数
            if (is_array($route) && isset($route[1])) {
                parse_str($route[1], $params);
                $var = array_merge($var, $params);
            }
            $_GET = array_merge($var, $_GET);
        }
        return true;
    }

    // 解析规范的路由地址
    // 地址格式 [模块/控制器/操作?]参数1=值1&参数2=值2...
    private function parseUrl($url)
    {
        $var = array();
        if (false !== strpos($url, '?')) { // [模块/控制器/操作?]参数1=值1&参数2=值2...
            $info = parse_url($url);
            $path = explode('/', $info['path']);
            parse_str($info['query'], $var);
        } elseif (strpos($url, '/')) { // [模块/控制器/操作]
            $path = explode('/', $url);
        } else { // 参数1=值1&参数2=值2...
            parse_str($url, $var);
        }
        if (isset($path)) {
            if (!empty($path)) {
                $var['a'] = strtolower(array_pop($path));
            } else {
                $var['a'] = C('DEFAULT_ACTION');
            }

            if (!empty($path)) {
                $var['c'] = ucfirst(array_pop($path));
            } else {
                $var['c'] = C('DEFAULT_CONTROLLER');
            }
            if (!empty($path)) {
                $var['m'] = ucfirst(array_pop($path));
                if (!in_array($var['m'], explode(',', C('APP_MODULE_LIST')))) {
                    $var['m'] = C('DEFAULT_MODULE');
                }
            } else {
                $var['m'] = C('DEFAULT_MODULE');
            }
        }
        return $var;
    }
}
 