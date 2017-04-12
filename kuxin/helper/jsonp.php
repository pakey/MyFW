<?php
namespace Kuxin\Helper;


use Kuxin\Config;
use Kuxin\Input;

class Jsonp
{
    
    public static function encode($data, $format = JSON_UNESCAPED_UNICODE)
    {
        $callback = Input::get(Config::get('jsonp_callback'), 'en', 'ptcms_jsonp');
        return $callback . '(' . json_encode($data, $format) . ');';
    }
    
    public static function decode($data, $assoc = true)
    {
        if(strpos($data,'(')){
            $data = explode('(', substr($data, 0, -2), 2)[1];
            return json_decode($data, $assoc);
        }else{
            return null;
        }
    }
}