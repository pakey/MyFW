<?php
/**
 * @Author: 杰少Pakey
 * @Email : Pakey@qq.com
 * @File  : loader.php
 */

namespace ptcms;

use ReflectionClass;

class Loader
{

    public static function import($filename)
    {
        static $_importFiles = [];
        if (!isset($_importFiles[$filename])) {
            if (is_file($filename)) {
                $_importFiles[$filename] = include $filename;
            } else {
                $_importFiles[$filename] = false;
            }
        }
        return $_importFiles[$filename];
    }
    
    public static function instance($class, $args=[])
    {
        static $_class;
        $key=md5($class.'_'.serialize($args));
        if(empty($_class[$key])){
            $_class[$key]=(new ReflectionClass($class))->newInstanceArgs($args);;
        }
        return $_class[$key];
    }
}