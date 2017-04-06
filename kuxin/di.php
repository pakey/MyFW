<?php

namespace Kuxin;

class DI
{
    
    public static function cache($node = 'common')
    {
        $hanlder=Registry::get("cache.{$node}");
        if(!$hanlder){
            $config=Config::get("app.cache.{$node}");
            if($config){
                $hanlder=Loader::instance('\\Kuxin\Cache\\'.$config['driver'],$config['option']);
                if($hanlder){
                    Registry::set("cache.{$node}",$hanlder);
                }
            }else{
                trigger_error("缓存节点配置[{$node}]不存在");
            }
        }
        return $hanlder;
    }
    
    public static function storage($node = 'common')
    {
        $hanlder=Registry::get("storage.{$node}");
        if(!$hanlder){
            $config=Config::get("app.storage.{$node}");
            if($config){
                $hanlder=Loader::instance('\\Kuxin\Storage\\'.$config['driver'],$config['option']);
                if($hanlder){
                    Registry::set("storage.{$node}",$hanlder);
                }
            }else{
                trigger_error("Storage节点配置[{$node}]不存在");
            }
        }
        return $hanlder;
    }
    
    public static function db($node = 'master')
    {
    
    }
}