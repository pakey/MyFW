<?php

/**
 * @Author: 杰少Pakey
 * @Email : admin@ptcms.com
 * @File  : Cache.php
 */
class Cache {

    protected static $handler = null;

    /**
     * @return Driver_Cache_File
     */
    public static function getInstance() {
        $key = C('cache_type', null, 'file');
        if (empty(self::$handler[$key])) {
            $class = 'Driver_Cache_' . C('cache_type');
            self::$handler[$key] = new $class(C('cache_option', null, array()));
        }
        return self::$handler[$key];
    }

    public static function set($key, $value, $time = 0) {
        $GLOBALS['_cacheRead']++;
        return self::getInstance()->set($key, $value, $time);
    }

    public static function get($key) {
        $GLOBALS['_cacheWrite']++;
        return self::getInstance()->get($key);
    }

    public static function rm($key) {
        return self::getInstance()->rm($key);
    }

    public static function clear() {
        self::getInstance()->clear();
    }
}