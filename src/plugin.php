<?php
namespace ptcms;
class Plugin{
    //子类hook点
    public static $_tags = array();

    /**
     * 调用插件
     *
     * @param $tag
     * @param null $param
     */
    public static function hook($tag, &$param = null) {
        if (isset(self::$_tags[$tag])) {
            foreach (self::$_tags[$tag] as $name) {
                $classname = $name ;
                $handler = new $classname();
                $handler->run($param);
            }
        }
    }
    
    /**
     * 注册插件方法
     *
     * @param array $data
     */
    public static function register(array $data) {
        foreach ($data as $tag => $var) {
            self::add($tag, $var);
        }
    }
    
    /**
     * 添加插件方法
     *
     * @param $tag
     * @param $var
     */
    public static function add($tag, $var) {
        if (!is_array($var)) $var = array($var);
        if (isset(self::$_tags[$tag])) {
            self::$_tags[$tag] = array_unique(array_merge(self::$_tags[$tag], $var));
        } else {
            self::$_tags[$tag] = $var;
        }
    }
    
    /**
     * 删除插件方法
     *
     * @param $tag
     * @param $var
     */
    public static function del($tag, $var) {
        if (isset(self::$_tags[$tag])) {
            $key = array_search($var, self::$_tags[$tag]);
            if ($key !== false) {
                unset(self::$_tags[$tag][$key]);
            }
            if (empty(self::$_tags[$tag])) unset(self::$_tags[$tag]);
        }
    }
    
    /**
     * 获取插件列表
     *
     * @param string $tag
     * @return array
     */
    public static function get($tag = '') {
        if (empty($tag)) return self::$_tags;
        if (isset(self::$_tags[$tag])) {
            return self::$_tags[$tag];
        } else {
            return array();
        }
    }
    
    /**
     * 获取开启的所有的插件
     * @return array
     */
    public static function getlist() {
        $list = array();
        foreach (self::$_tags as $v) {
            $list = array_merge($list, $v);
        }
        return array_unique($list);
    }
}