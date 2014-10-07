<?php

/**
 * @Author: 杰少Pakey
 * @Email : admin@ptcms.com
 * @File  : Storage.php
 */
class Storage {

    protected static $handler = null;

    /**
     * @return Driver_Storage_File
     */
    static public function getInstance() {
        $key = C('storage_type', null, 'file') . '_' . C('storage_path', null, 'storage');
        if (empty(self::$handler[$key])) {
            $class = 'Driver_Storage_' . C('storage_type');
            self::$handler[$key] = new $class(C('storage_option', null, array()));
        }
        return self::$handler[$key];
    }

    public static function exist($file) {
        return self::getInstance()->exist($file);
    }

    public static function write($file, $content) {
        if ($content !== false)
            return self::getInstance()->write($file, $content);
        return false;
    }

    public static function read($file) {
        return self::getInstance()->read($file);
    }

    public static function append($file, $content) {
        if ($content !== false)
            return self::getInstance()->read($file, $content);
        return false;
    }

    public static function remove($file) {
        return self::getInstance()->remove($file);
    }

    public static function getUrl($file) {
        return self::getInstance()->getUrl($file);
    }

    public static function error() {
        return self::getInstance()->error();
    }
}