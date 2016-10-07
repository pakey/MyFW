<?php
namespace ptcms;

use ptcms\driver\storage\File;

class Storage
{

    /**
     * @var File
     */
    protected static $handler;

    public static function init($type = 'File',$args=[])
    {
        $type  = $type ? $type : Config::get('storage.type', 'File');
        $class = 'ptcms\driver\storage\\' . $type;
        return self::$handler=Loader::instance($class,$args);
    }

    public static function exist($file)
    {
        return self::$handler->exist($file);
    }

    public static function write($file, $content)
    {
        return self::$handler->write($file, $content);
    }

    public static function read($file)
    {
        return self::$handler->read($file);
    }

    public static function append($file, $content)
    {
        return self::$handler->append($file, $content);
    }

    public static function remove($file)
    {
        return self::$handler->remove($file);
    }

    public static function getUrl($file)
    {
        return self::$handler->getUrl($file);
    }

    public static function getPath($file)
    {
        return self::$handler->getPath($file);
    }

    public static function error()
    {
        return self::$handler->error();
    }
}