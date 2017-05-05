<?php

namespace Kuxin\Helper;

/**
 * Class Arr
 *
 * @package Kuxin\Helper
 * @author  Pakey <pakey@qq.com>
 */
class Arr
{
    
    /**
     * 二维数组排序
     *
     * @param        $list
     * @param        $key
     * @param string $order
     * @return array
     */
    public static function msort($list, $key, $order = 'desc')
    {
        $arr = $new = [];
        foreach ($list as $k => $v) {
            $arr[$k] = $v[$key];
        }
        if ($order == 'asc') {
            asort($arr);
        } else {
            arsort($arr);
        }
        foreach ($arr as $k => $v) {
            $new[] = $list[$k];
        }
        return $new;
    }
    
    /**
     * 数组递归合并
     *
     * @param ...
     * @return bool
     */
    public static function merge_recursive()
    {
        $args = func_get_args();
        $rs   = array_shift($args);
        
        foreach ($args as $arg) {
            if (!is_array($arg)) {
                return false;
            }
            foreach ($arg as $key => $val) {
                $rs[$key] = isset($rs[$key]) ? $rs[$key] : [];
                $rs[$key] = is_array($val) ? self::merge_recursive($rs[$key], $val) : $val;
            }
        }
        return $rs;
    }
}