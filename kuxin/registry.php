<?php
/**
 * @Author: 杰少Pakey
 * @Email : Pakey@qq.com
 * @File  : registry.php
 */

namespace Kuxin;

class Registry
{
    
    protected static $_data;
    
    /**
     * 获取
     *
     * @param $key
     * @param $defaultvar
     * @return mixed
     */
    public static function get($key, $defaultvar = null)
    {
        return isset(self::$_data[$key]) ? self::$_data[$key] : (is_callable($defaultvar) ? $defaultvar($key) : $defaultvar);
    }
    
    /**
     * 设置
     *
     * @param      $key
     * @param null $value
     */
    public static function set($key, $value = null)
    {
        if (is_array($key)) {
            self::$_data = array_merge(self::$_data, $key);
        } else {
            self::$_data[$key] = $value;
        }
    }
    
    public static function remove($key)
    {
        if (isset(self::$_data[$key])) {
            unset(self::$_data[$key]);
        }
    }
}