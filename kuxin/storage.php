<?php

namespace Kuxin;


class Storage
{
    
    /**
     * @var \Kuxin\Storage\File
     */
    protected static $handler;
    
    public function __construct(array $config)
    {
        $class = 'Kuxin\\storage\\' . $config['driver'];
        return self::$handler = Loader::instance($class, [$config['option']]);
    }
    
    public function exist($file)
    {
        return self::$handler->exist($file);
    }
    public function mtime($file)
    {
        return self::$handler->mtime($file);
    }
    
    public function write($file, $content)
    {
        return self::$handler->write($file, $content);
    }
    
    public function read($file)
    {
        return self::$handler->read($file);
    }
    
    public function append($file, $content)
    {
        return self::$handler->append($file, $content);
    }
    
    public function remove($file)
    {
        return self::$handler->remove($file);
    }
    
    public function getUrl($file)
    {
        return self::$handler->getUrl($file);
    }
    
    public function getPath($file)
    {
        return self::$handler->getPath($file);
    }
    
    public function error()
    {
        return self::$handler->error();
    }
}