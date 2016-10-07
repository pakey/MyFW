<?php
namespace ptcms;

class Json
{
    
    public static function encode($data, $format = 0)
    {
        if (Config::get('app.debug') && $format == 0) {
            $format = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE;
            return json_encode($data, $format);
        }
        return json_encode($data);
    }
    
    public function decode($data, $assoc = true)
    {
        return json_decode($data, $assoc);
    }
}