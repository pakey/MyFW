<?php
namespace ptcms;

class Log
{

    const ERROR     = 'error';
    const WARNING   = 'warning';
    const INFO      = 'info';
    const CRITICAL  = 'critical';
    const EMERGENCY = 'emergency';
    const NOTICE    = 'notice';
    const DEBUG     = 'debug';
    const ALERT     = 'alert';
    // 日志信息
    protected static $log = [];
    // 配置参数
    protected static $config = [];
    // 日志类型
    protected static $type = ['emergency', 'alert', 'critical', 'error', 'warning', 'notice', 'info', 'debug'];
    // 日志写入驱动
    protected static $driver;
    // 当前日志授权key
    protected static $key;

    /**
     * @param array $config
     */
    public static function init($config = [])
    {
        self::$config = $config;
    }

    /**
     * 获取日志信息
     *
     * @param string $type 信息类型
     * @return array
     */
    public static function getLog($type = '')
    {
        return $type ? self::$log[$type] : self::$log;
    }

    /**
     * 记录调试信息
     *
     * @param mixed  $msg  调试信息
     * @param string $type 信息类型
     * @return void
     */
    public static function record($msg, $type = 'debug')
    {
        self::$log[$type][] = $msg;
    }

    /**
     * 清空日志信息
     *
     * @return void
     */
    public static function clear()
    {
        self::$log = [];
    }

    /**
     * 保存调试信息
     *
     * @return bool
     */
    public static function save()
    {
        if (!empty(self::$log)) {
            if (is_null(self::$driver)) {
                self::init(Config::get('log'));
            }
            if (!self::check(self::$config)) {
                // 检测日志写入权限
                return false;
            }
            if (empty(self::$config['level'])) {
                // 获取全部日志
                $log = self::$log;
            } else {
                // 记录允许级别
                $log = [];
                foreach (self::$config['level'] as $level) {
                    if (isset(self::$log[$level])) {
                        $log[$level] = self::$log[$level];
                    }
                }
            }
            $result = self::$driver->save($log);
            if ($result) {
                self::$log = [];
            }
            return $result;
        }
        return true;
    }

    /**
     * 实时写入日志信息 并支持行为
     *
     * @param mixed  $msg   调试信息
     * @param string $type  信息类型
     * @param bool   $force 是否强制写入
     * @return bool
     */
    public static function write($msg, $type = 'log', $force = false)
    {
        // 封装日志信息
        if (true === $force || empty(self::$config['level'])) {
            $log[$type][] = $msg;
        } elseif (in_array($type, self::$config['level'])) {
            $log[$type][] = $msg;
        } else {
            return false;
        }
        // 监听log_write
        Hook::listen('log_write', $log);
        if (is_null(self::$driver)) {
            self::init(Config::get('log'));
        }
        // 写入日志
        return self::$driver->save($log);
    }

    /**
     * 静态调用
     *
     * @param $method
     * @param $args
     * @return mixed
     */
    public static function __callStatic($method, $args)
    {
        if (in_array($method, self::$type)) {
            array_push($args, $method);
            return call_user_func_array('\\think\\Log::record', $args);
        }
    }
}