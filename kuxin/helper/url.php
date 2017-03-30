<?php

namespace Kuxin;

class Url{
    
    /**
     * 获取当前URL
     *
     * @return string
     */
    public static function weixin()
    {
        $url=self::current();
        if(strpos($url,'#')){
            $url=explode('#',$url)['0'];
        }
        return $url;
    }
    
    public static function current() {
        if(PHP_SAPI=='cli'){
            return 'cli';
        }
        if(strpos($_SERVER['REQUEST_URI'],'http://')===0){
            return $_SERVER['REQUEST_URI'];
        }
        $protocol = (!empty($_SERVER['HTTPS'])
            && $_SERVER['HTTPS'] !== 'off'
            || $_SERVER['SERVER_PORT'] === 443) ? 'https://' : 'http://';
        
        $host=isset($_SERVER['HTTP_X_FORWARDED_HOST'])?$_SERVER['HTTP_X_FORWARDED_HOST']:$_SERVER['HTTP_HOST'];
        $uri=isset($_SERVER['HTTP_X_REAL_URI'])?$_SERVER['HTTP_X_REAL_URI']:$_SERVER['REQUEST_URI'];
        return $protocol.$host.$uri;
    }
    
    public static function build()
    {
        
    }
}