<?php

namespace Kuxin\Cache;

class Memcache
{
    
    /**
     * @var Memcache
     */
    protected static $handler;
    protected static $prefix;
    
    public function __construct($option)
    {
        self::$handler = new \Memcache();
        self::$handler->connect($option['host'] ?? '127.0..1', $option['port'] ?? '11211');
        self::$prefix = $option['prefix'] ?? '';
    }
    
    public function set($key, $value, $time = 0)
    {
        return self::$handler->set(self::$prefix . $key, $value, MEMCACHE_COMPRESSED, $time);
    }
    
    public function get($key)
    {
        $return = self::$handler->get(self::$prefix . $key);
        if ($return === false) return null;
        return $return;
    }
    
    public function remove($key)
    {
        return self::$handler->delete(self::$prefix . $key);
    }
    
    public function inc($key, $num = 1)
    {
        return self::$handler->increment(self::$prefix . $key, $num);
    }
    
    public function dec($key, $num = 1)
    {
        return self::$handler->decrement(self::$prefix . $key, $num);
    }
    
    public function clear()
    {
        self::$handler->flush();
    }
}