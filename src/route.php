<?php
namespace ptcms;


class Route
{

    protected static $superVar;

    protected static $routes;

    public static $controller;

    public static $action;

    public static function resolve()
    {
        if (empty($_GET['s'])) {
            //设置默认值
            self::$controller = Config::get('default_controller', 'index');
            self::$action     = Config::get('default_action', 'index');
        } else {
            self::$superVar = trim(Input::get('s','str'), '/');//去除左右的/防止干扰
            self::parseRule();//路由校验
            self::parseSuperVar();//解析超级变量
        }
        unset($_GET['s']);
        //todo module映射
        //过滤xss及参数前后空白
        foreach ($_GET as &$v) {
            $v = trim(strip_tags($v));
        }
        $_REQUEST = array_merge($_GET, $_POST);
    }

    // 解析超级变量
    public static function parseSuperVar()
    {
        if (strpos(self::$superVar, '.')) {
            $param = explode('.', self::$superVar, 2);
            Response::type($param['1']);
            $param = explode('/', $param['0']);
        } else {
            $param = explode('/', self::$superVar);
        }
        if (isset($param['1'])) {
            self::$action     = array_pop($param);
            self::$controller = implode('\\',$param);
        } else {
            self::$action     = Config::get('default_action', 'index');
            self::$controller = $param['0'];
        }
    }

    // 解析路由
    public static function parseRule()
    {
        // todo
        if ($router = Config::get('route.rule')) {
            foreach ($router as $rule => $url) {
                if (preg_match('{' . $rule . '}isU', $_GET['s'], $match)) {
                    unset($match['0']);
                    if (0 === strpos($url, '/') || 0 === stripos($url, 'http://')) { // 路由重定向跳转
                        header("Location: $url", true, 301);
                        exit;
                    } elseif (strpos($url, '?')) {
                        list($url, $query) = explode('?', $url);
                    }
                    self::$superVar = rtrim($url, '/');
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