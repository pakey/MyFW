<?php

/**
 * @Author: 杰少Pakey
 * @Email : admin@ptcms.com
 * @File  : Cache.php
 */
class PT_Cache{

    protected static $handler = null;
    protected $pt;

    public function __construct() {
        $this->pt=PT_Base::getInstance();
    }

    /**
     * @param string $type;
     * @return Driver_Cache_File
     */
    public function getInstance($type = '') {
        $type = $type ? $type : $this->pt->config->get('cache_driver', 'file');
        if (empty(self::$handler[$type])) {
            $class                = 'Driver_Cache_' . $this->pt->config->get('cache_driver');
            self::$handler[$type] = new $class($this->pt->config->get('cache_option', array()));
        }
        return self::$handler[$type];
    }

    public function set($key, $value, $time = 0) {
        $GLOBALS['_cacheWrite']++;
        return $this->getInstance()->set($key, $value, $time);
    }

    public function get($key) {
        $GLOBALS['_cacheRead']++;
        return $this->getInstance()->get($key);
    }

    public function rm($key) {
        return $this->getInstance()->rm($key);
    }

    public function clear() {
        $this->getInstance()->clear();
    }
}