<?php

namespace Kuxin;

class Plugin
{
    
    /**
     * 调用插件
     *
     * @param      $tag
     * @param null $param
     * @return mixed
     */
    public static function call($tag, $param = null)
    {
        $methods = Config::get('plugin.' . $tag);
        foreach ($methods as $method) {
            $class = Loader::instance($method);
            $param = $class->run($param);
        }
        return $param;
    }
}