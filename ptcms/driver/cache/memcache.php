<?php

/**
 * @Author: 杰少Pakey
 * @Email : admin@ptcms.com
 * @File  : Memcache.php
 */
class Driver_Cache_Memcache {

    protected static $handler = null;
    protected static $prefix = null;

    public function __construct($option = array()) {
        self::$handler = new Memcache();
        self::$handler->connect(C('memcache_host', null, '127.0.0.1'), C('memcache_port', null, '11211'));
        self::$prefix = C('cache_prefix', null, substr(md5(PT_URL), 0, 3) . '_');
    }

    public function set($key, $value, $time = 0) {
        return self::$handler->set(self::$prefix . $key, $value, MEMCACHE_COMPRESSED, $time);
    }

    public function get($key) {
        if ($return=self::$handler->get(self::$prefix . $key)){
            return $return;
        }
        return null;
    }

    public function rm($key) {
        return self::$handler->delete(self::$prefix . $key);
    }

    public function clear() {
        self::$handler->flush();
    }
}