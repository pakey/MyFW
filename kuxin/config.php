<?php

namespace Kuxin;

class Config
{
    
    protected static $_config = [];
    
    /**
     * 获取参数
     *
     * @param string $name       参数名
     * @param null   $defaultVar 默认值
     * @return array|null
     */
    public static function get($name = '', $defaultVar = null)
    {
        if ($name == '') return self::$_config;
        $name = strtolower($name);
        if (strpos($name, '.')) {
            //数组模式 找到返回
            list($group, $name) = explode('.', $name, 2);
            return isset(self::$_config[$group][$name])?self::$_config[$group][$name]:$defaultVar;
        } else {
            return isset(self::$_config[$name])?self::$_config[$name]:$defaultVar;
        }
    }
    
    
    /**
     * @param        $name
     * @param string $var
     */
    public static function set($name, $var = '')
    {
        //数组 调用注册方法
        if (is_array($name)) {
            self::register($name);
        } else if (strpos($name, '.')) {
            list($group, $name) = explode('.', $name, 2);
            self::$_config[$group][$name] = $var;
        } else {
            self::$_config[$name] = $var;
        }
    }
    
    
    /**
     * 注册配置
     *
     * @param $config
     */
    public static function register($config)
    {
        if (is_array($config)) {
            self::$_config = array_merge(self::$_config, $config);
        }
    }
}