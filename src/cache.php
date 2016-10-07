<?php

namespace ptcms;
class Cache
{
    
    protected static $handler = null;
    
    /**
     * @param string $type ;
     * @return mixed
     */
    public static function getInstance($type = '')
    {
        $type = $type ? $type : Config::get('cache_driver', 'Memcache');
        if (empty(self::$handler[$type])) {
            $class                = '\ptcms\driver\cache\\' . $type;
            self::$handler[$type] = new $class(Config::get('cache_option', []));
        }
        return self::$handler[$type];
    }
    
    public static function set($key, $value, $time = 0)
    {
        Registry::setInc('_cacheWrite');
        return self::getInstance()->set($key, $value, $time);
    }
    
    public static function get($key, $default = null)
    {
        Registry::setInc('_cacheRead');
        $result = Config::get('app.debug') ? null : self::getInstance()->get($key, $default);
        if ($result === null) {
            return (is_callable($default) ? $default($key) : $default);
        } else {
            Registry::setInc('_cacheHit');
            return $result;
        }
    }
    
    public static function remove($key)
    {
        return self::getInstance()->rm($key);
    }
    
    public static function clear()
    {
        self::getInstance()->clear();
    }
}