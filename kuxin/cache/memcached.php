<?php

namespace Kuxin\Ext\Cache;

use Kuxin\Config;


class Memcached {
    
    /**
     * @var Memcache
     */
    protected static $handler ;
    protected static $prefix ;
    
    public function __construct($option = array()) {
        self::$handler = new \Memcached();
        self::$handler->addServer(Config::get('cache.memcache_host', '127.0.0.1'), Config::get('cache.memcache_port', '11211'));
        self::$prefix = Config::get('cache.prefix', substr(md5($_SERVER['HTTP_HOST']), 3, 3) . '_');
    }
    
    public function set($key, $value, $time = 0) {
        return self::$handler->set(self::$prefix . $key, $value, $time);
    }
    
    public function get($key) {
        $return = self::$handler->get(self::$prefix . $key);
        if ($return === false) return null;
        return $return;
    }
    
    public function rm($key) {
        return self::$handler->delete(self::$prefix . $key);
    }
    
    public function inc($key, $num = 1) {
        return self::$handler->increment(self::$prefix . $key, $num);
    }
    
    public function dec($key, $num = 1) {
        return self::$handler->decrement(self::$prefix . $key, $num);
    }
    
    public function clear() {
        self::$handler->flush();
    }
}