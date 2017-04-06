<?php
namespace Kuxin;


use Kuxin\Config;
class Session
{
    
    public function init($name = [])
    {
        $name = array_merge(Config::get('session', []), $name);
        //ini_set("session.save_handler", "memcache");
        //ini_set("session.save_path", "tcp://127.0.0.1:11211");
        session_start();
    }
    
    public function __set($name, $value)
    {
        return $this->set($name, $value);
    }
    
    public function __get($name)
    {
        return $this->get($name);
    }
    
    public function get($name = '', $default = null)
    {
        if ($name == '') return $_SESSION;
        //数组模式 找到返回
        if (strpos($name, '.')) {
            //数组模式 找到返回
            $c      = $_SESSION;
            $fields = explode('.', $name);
            foreach ($fields as $field) {
                if (!isset($c[$field])) return (is_callable($default) ? $default($name) : $default);
                $c = $c[$field];
            }
            return $c;
        } elseif (isset($_SESSION[$name])) {
            return $_SESSION[$name];
        } else {
            return (is_callable($default) ? $default($name) : $default);
        }
    }
    
    public function set($key, $value = '')
    {
        $_SESSION[$key] = $value;
        return true;
    }
    
    public function rm($key)
    {
        if (!isset($_SESSION[$key])) {
            return false;
        }
        
        unset($_SESSION[$key]);
        
        return true;
    }
    
    /**
     * 清空session值
     *
     * @access public
     * @return void
     */
    public static function clear()
    {
        
        $_SESSION = [];
    }
    
    /**
     * 注销session
     *
     * @access public
     * @return void
     */
    public static function destory()
    {
        
        if (session_id()) {
            unset($_SESSION);
            session_destroy();
        }
    }
    
    /**
     * 当浏览器关闭时,session将停止写入
     *
     * @access public
     * @return void
     */
    public static function close()
    {
        
        if (session_id()) {
            session_write_close();
        }
    }
}