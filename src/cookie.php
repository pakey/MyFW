<?php
namespace ptcms;

class Cookie
{

    protected static $option = [
        'prefix'   => 'PTCMS_',
        // cookie 保存时间
        'expire'   => 2592000,
        // cookie 保存路径
        'path'     => '/',
        // cookie 有效域名
        'domain'   => '',
        //  cookie 启用安全传输
        'secure'   => false,
        // httponly设置
        'httponly' => '',
    ];
    
    public function __construct($config = [])
    {
        $this->init($config);
    }
    
    public static function init($config = [])
    {
        $config = [
            'prefix'   => Input::param('prefix', 'string', Config::get('cookie_prefix', 'PTCMS_'), $config),
            // cookie 保存时间
            'expire'   => Input::param('expire', 'int', Config::get('cookie_expire', 2592000), $config),
            // cookie 保存路径
            'path'     => Input::param('path', 'string', Config::get('cookie_path', '/'), $config),
            // cookie 有效域名
            'domain'   => Input::param('domain', 'string', Config::get('cookie_domain', ''), $config),
            //  cookie 启用安全传输
            'secure'   => Input::param('secure', 'string', Config::get('cookie_secure', false), $config),
            // httponly设置
            'httponly' => Input::param('httponly', 'string', Config::get('cookie_httponly', ''), $config),
        ];
        if (!$config) self::$option = array_merge(self::$option, $config);
    }
    
    public static function get($name, $default = null)
    {
        $fullname = self::$option['prefix'] . $name;
        if (isset($_COOKIE[$fullname])) {
            return $_COOKIE[$fullname];
        } else {
            return (is_callable($default) ? $default($name) : $default);
        }
    }
    
    public static function set($name, $value = '', $option = null)
    {
        if (!is_null($option)) {
            if (is_numeric($option))
                $option = ['expire' => $option];
            elseif (is_string($option))
                parse_str($option, $option);
            $config = array_merge(self::$option, array_change_key_case($option));
        } else {
            $config = self::$option;
        }
        $name   = self::$option['prefix'] . $name;
        $expire = !empty($config['expire']) ? time() + $config['expire'] : 0;
        setcookie($name, $value, $expire, $config['path'], $config['domain']);
        $_COOKIE[$name] = $value;
    }
    
    public static function remove($name)
    {
        $name = self::$option['prefix'] . $name;
        setcookie($name, '', time() - 3600, self::$option['path'], self::$option['domain']);
        // 删除指定cookie
        unset($_COOKIE[$name]);
    }
    
    public static function clear()
    {
        foreach ($_COOKIE as $key => $val) {
            if (0 === stripos($key, self::$option['prefix'])) {
                setcookie($key, '', time() - 3600, self::$option['prefix']['path'], self::$option['prefix']['domain']);
                unset($_COOKIE[$key]);
            }
        }
        return true;
    }
}