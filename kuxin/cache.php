<?php
/**
 * @Author: 杰少Pakey
 * @Email : Pakey@qq.com
 * @File  : cache.php
 */

namespace Kuxin;


class Cache
{
    
    /**
     * @var \Kuxin\Cache\Memcache
     */
    protected $handler = null;
    
    
    /**
     * Cache constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $class         = '\\Kuxin\\Cache\\' . $config['driver'];
        $this->handler = Loader::instance($class, [$config['option']]);
    }
    
    public function set($key, $value, $time = 0)
    {
        Registry::setInc('_cacheWrite');
        $this->handler->set($key, $value, $time);
    }
    
    /**
     * @param       $key
     * @param mixed $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        Registry::setInc('_cacheRead');
        $result = $this->handler->get($key);
        if ($result === null) {
            $result = (is_callable($default) ? $default($key) : $default);
        } else {
            Registry::setInc('_cacheHit');
        }
        return $result;
    }
    
    /**
     * @param       $key
     * @param mixed $default
     * @return mixed
     */
    public function debugGet($key, $default = null)
    {
        Registry::setInc('_cacheRead');
        $result = Config::get('app.debug') ? null : $this->handler->get($key);
        if ($result === null) {
            $result = (is_callable($default) ? $default($key) : $default);
        } else {
            Registry::setInc('_cacheHit');
        }
        return $result;
    }
    
    public function remove($key)
    {
        return $this->handler->remove($key);
    }
    
    public function inc($key, $len = 1)
    {
        return $this->handler->inc($key, $len);
    }
    
    public function dec($key, $len = 1)
    {
        return $this->handler->dec($key, $len);
    }
    
    public function clear()
    {
        $this->handler->clear();
    }
    
}


 