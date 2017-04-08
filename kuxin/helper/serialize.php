<?php

namespace Kuxin\Helper;

class Serialize{
    
    /**
     * 序列化
     * @param $data
     * @return string
     */
    public static function encode($data)
    {
        if(extension_loaded('swoole_serizlize')){
            return swoole_serialize($data);
        }else{
            return serialize($data);
        }
    }
    
    /**
     * 反序列化
     * @param $data
     * @return mixed
     */
    public static function decode($data)
    {
        if(extension_loaded('swoole_serizlize')){
            return swoole_unserialize($data);
        }else{
            return unserialize($data);
        }
    }
}