<?php

namespace Kuxin;

class Input
{
    
    public static function get($name, $type = 'int', $default = null)
    {
        return self::param($name, $type, $default, $_GET);
    }
    
    public static function post($name, $type = 'int', $default = null)
    {
        return self::param($name, $type, $default, $_POST);
    }
    
    public static function request($name, $type = 'int', $default = null)
    {
        return self::param($name, $type, $default, $_REQUEST);
    }
    
    public static function put($name, $type = 'int', $default = null)
    {
        static $input = null;
        if ($input === null) parse_str(file_get_contents('php://input'), $input);
        return self::param($name, $type, $default, $input);
    }
    
    public static function server($name, $type = 'int', $default = null)
    {
        return self::param($name, $type, $default, $_SERVER);
    }
    
    public static function globals($name, $type = 'int', $default = null)
    {
        return self::param($name, $type, $default, $GLOBALS);
    }
    
    public static function cookie($name, $type = 'int', $default = null)
    {
        return self::param(Config::get('cookie_prefix', '') . $name, $type, $default, $_COOKIE);
    }
    
    public static function session($name, $type = 'int', $default = null)
    {
        return self::param($name, $type, $default, $GLOBALS);
    }
    
    public static function files($name, $type = 'int', $default = null)
    {
        return self::param($name, $type, $default, $_FILES);
    }
    
    public static function has($name, $type = null)
    {
        $type = $type ?: $_REQUEST;
        return isset($type[$name]);
    }
    
    public static function param($name, $filter = 'int', $default = null, $param = [])
    {
        if (!isset($param[$name])) {
            $defaultVar = is_callable($default) ? $default($name) : $default;
            $value      = $defaultVar;
        } else {
            $defaultVar = null;
            $value      = $param[$name];
        }
        switch ($filter) {
            case 'int':
                $value = (int)$value;
                break;
            case 'str':
            case 'string':
                $value = (string)$value;
                break;
            case 'arr':
                $value = (array)$value;
                break;
            case 'time':
                $value = strtotime($value) ? $value : '0';
                break;
            default:
                if (!Filter::regex($value, $filter)) {
                    $value = null === $defaultVar ? (is_callable($default) ? $default($name) : $default) : $defaultVar;
                };
        }
        return $value;
    }
}