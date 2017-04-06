<?php
namespace Kuxin\Helper;

class Json
{
    public static function encode($data, $format = JSON_UNESCAPED_UNICODE)
    {
        return json_encode($data, $format);
    }
    
    public static function decode($data, $assoc = true)
    {
        return json_decode($data, $assoc);
    }
}