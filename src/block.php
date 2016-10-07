<?php
namespace ptcms;

class Block{
    
    public static function getData($type,$args=[])
    {
        
        $key=md5($type,serialize($args));
        
        $data=Cache::get($key,function() use ($key,$type,$args){
            $classname='\\Block\\'.ucfirst(strtolower($type));
            $block=Loader::instance($classname);
            $data=$block->run($args);
            Cache::set($key,$data,Config::get('cache.time',1800));
            return $data;
        });
        
        return $data;
        
    }
    
    public function run($param)
    {
        
    }
}