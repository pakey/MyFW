<?php

namespace ptcms;
class Jsonp
{
    
    public static function encode($data, $format = 0)
    {
        $callback = Input::get(Config::get('jsonp_callback'), 'en', 'ptcms_jsonp');
        return $callback . '(' . Json::encode($data, $format) . ');';
    }
}