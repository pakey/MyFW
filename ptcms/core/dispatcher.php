<?php

/**
 * @Author: 杰少Pakey
 * @Email : admin@ptcms.com
 * @File  : dispatcher.php
 */
class dispatcher {

    // 入口文件
    public static function run() {
        if (!empty($_POST['s'])) $_GET['s'] = $_POST['s'];
        if (!empty($_POST['m'])) $_GET['m'] = $_POST['m'];
        if (!empty($_POST['c'])) $_GET['c'] = $_POST['c'];
        if (!empty($_POST['a'])) $_GET['a'] = $_POST['a'];
        if (empty($_GET['s'])) {
            //设置默认值
            $_GET['m']=empty($_GET['m'])?C('default_module', null, 'index'):$_GET['m'];
            $_GET['c']=empty($_GET['c'])?C('default_controller', null, 'index'):$_GET['c'];
            $_GET['a']=empty($_GET['a'])?C('default_action', null, 'index'):$_GET['a'];
        } else {
            $_GET['s'] = trim($_GET['s'], '/');//去除左右的/防止干扰
            self::router();//路由校验
            self::parseSuperVar();//解析超级变量
        }
        //module映射
        $mapModule = C('map_module', null, array());
        if (isset($mapModule[$_GET['m']])) {
            halt('当前模块已经改名', __FILE__, __LINE__ - 1);
        } elseif (in_array($_GET['m'], $mapModule)) {
            $_GET['_m'] = $_GET['m'];
            $_GET['m'] = array_search($_GET['m'], $mapModule);
        }
        $_REQUEST = array_merge($_GET, $_POST);
    }

    // 解析超级变量
    public static function parseSuperVar() {
        $param = explode('/', $_GET['s']);
        $var['m'] = isset($param['0']) ? array_shift($param) : C('default_module', null, 'index');
        $var['c'] = isset($param['0']) ? array_shift($param) : C('default_controller', null, 'index');
        $var['a'] = isset($param['0']) ? array_shift($param) : C('default_action', null, 'index');
        while ($k = each($param)) {
            $var[$k['value']] = current($param);
            next($param);
        };
        $_GET = array_merge($var, $_GET);
    }

    // 解析路由
    public static function router() {
        if ($router = C('url_router')) {
            foreach ($router as $rule => $url) {
                if (preg_match('{' . $rule . '}isU', $_GET['s'], $match)) {
                    unset($match['0']);
                    if (0 === strpos($url, '/') || 0 === stripos($url, 'http://')) { // 路由重定向跳转
                        header("Location: $url", true, 301);
                        exit;
                    } elseif (strpos($url, '?')) {
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