<?php

namespace Kuxin;

/**
 * Class Storage
 *
 * @package Kuxin
 * @author  Pakey <pakey@qq.com>
 */
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
    
    /**
     * @param $file
     * @return bool
     */
    public function exist(string $file)
    {
        return self::$handler->exist($file);
    }
    
    /**
     * @param $file
     * @return bool|int
     */
    public function mtime(string $file)
    {
        return self::$handler->mtime($file);
    }
    
    /**
     * @param $file
     * @param $content
     * @return bool|int
     */
    public function write(string $file, string $content)
    {
        return self::$handler->write($file, $content);
    }
    
    /**
     * @param $file
     * @return bool|string
     */
    public function read(string $file)
    {
        return self::$handler->read($file);
    }
    
    /**
     * @param $file
     * @param $content
     * @return bool|int
     */
    public function append(string $file, string $content)
    {
        return self::$handler->append($file, $content);
    }
    
    /**
     * @param $file
     * @return bool
     */
    public function remove(string $file)
    {
        return self::$handler->remove($file);
    }
    
    /**
     * @param $file
     * @return string
     */
    public function getUrl(string $file)
    {
        return self::$handler->getUrl($file);
    }
    
    /**
     * @param $file
     * @return string
     */
    public function getPath(string $file)
    {
        return self::$handler->getPath($file);
    }
    
    /**
     * @return string
     */
    public function error()
    {
        return self::$handler->error();
    }
}