<?php

namespace Kuxin\Cache;

use Kuxin\Config;
use Kuxin\Helper\Serialize;

class Redis
{
    
    /**
     * @var \Redis
     */
    protected $handler;
    
    /**
     * @var string 缓存前缀
     */
    protected $prefix = '';
    
    public function __construct($option)
    {
        if (!extension_loaded('redis')) {
            trigger_error('您尚未安装redis扩展', E_USER_ERROR);
        }
        $this->handler = new \Redis;
        $this->handler->connect($option['host'] ?? '127.0.0.1', $option['port'] ?? '6379');
        if (isset($option['password']) && $option['password'] !== null) {
            $this->handler->auth($option['password']);
        }
        if (isset($option['password']) && $option['db']) {
            $this->handler->select($option['db']);
        }
        $this->prefix = $option['prefix'] ?? Config::get('cache.prefix', '');
    }
    
    public function set($key, $value, $expire = 0)
    {
        $value = Serialize::encode($value);
        if (is_int($expire) && $expire) {
            return $this->handler->set($this->prefix . $key, $expire, $value);
        } else {
            return $this->handler->set($this->prefix . $key, $value);
        }
    }
    
    public function get($key)
    {
        $return = Serialize::decode($this->handler->get($this->prefix . $key));
        if ($return === false) return null;
        return $return;
    }
    
    public function remove($key)
    {
        $this->handler->delete($this->prefix . $key);
    }
    
    public function inc($key, $num = 1)
    {
        return $this->handler->incrBy($this->prefix . $key, $num);
    }
    
    public function dec($key, $num = 1)
    {
        return $this->handler->decrBy($this->prefix . $key, $num);
    }
    
    public function clear()
    {
        $this->handler->flushDB();
    }
}