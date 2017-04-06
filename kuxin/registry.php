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
     * @param $default
     * @return mixed
     */
    public static function get($key, $default = null)
    {
        return isset(self::$_data[$key]) ? self::$_data[$key] : (is_callable($default) ? $default($key) : $default);
    }
    
    /**
     * 设置
     *
     * @param      $key
     * @param null $value
     */
    public static function set($key, $value)
    {
        if (is_array($key)) {
            self::$_data = array_merge(self::$_data, $key);
        } else {
            self::$_data[$key] = $value;
        }
    }
    
    /**
     * 合并信息
     *
     * @param $key
     * @param $value
     */
    public static function merge($key, $value)
    {
        $data   = (array)self::get($key);
        $data[] = $value;
        self::set($key, $data);
    }
    
    /**
     * 移除
     *
     * @param $key
     */
    public static function remove($key)
    {
        if (isset(self::$_data[$key])) {
            unset(self::$_data[$key]);
        }
    }
    
    /**
     * 对值增加
     *
     * @param     $key
     * @param int $num
     */
    public static function setInc($key, $num = 1)
    {
        if (isset(self::$_data[$key])) {
            self::$_data[$key] += $num;
        } else {
            self::$_data[$key] = $num;
        }
    }
    
    /**
     * 对值减少
     *
     * @param $key
     * @param $num
     */
    public static function setDec($key, $num)
    {
        if (isset(self::$_data[$key])) {
            self::$_data[$key] -= $num;
        } else {
            self::$_data[$key] = $num;
        }
    }
}