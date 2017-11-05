<?php

namespace Kuxin;

/**
 * Class Loader
 *
 * @package Kuxin
 * @author  Pakey <pakey@qq.com>
 */
class Loader
{

    /**
     * @var array
     */
    static $_importFiles = [];

    /**
     * @var array
     */
    static $_class = [];

    /**
     * 加载文件
     *
     * @param $filename
     * @return mixed
     */
    public static function import(string $filename)
    {
        if (!isset(self::$_importFiles[$filename])) {
            if (is_file($filename)) {
                self::$_importFiles[$filename] = require $filename;
            } else {
                trigger_error('文件不存在[ ' . $filename . ' ]', E_USER_ERROR);
                return false;
            }
        }
        return self::$_importFiles[$filename];
    }

    /**
     * 初始化类
     *
     * @param       $class
     * @param array $args
     * @return mixed
     */
    public static function instance(string $class, array $args = [])
    {
        $key = md5($class . '_' . serialize($args));
        if (empty(self::$_class[$key])) {
            self::$_class[$key] = (new \ReflectionClass($class))->newInstanceArgs($args);;
        }
        return self::$_class[$key];
    }
}