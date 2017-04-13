<?php

namespace Kuxin;

class Log{
    // 日志信息
    protected static $log = [];
    
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
     * 记录日志 默认为pt
     *
     * @param mixed  $msg  调试信息
     * @param string $type 信息类型
     * @return void
     */
    public static function record($msg, $type = 'pt')
    {
        self::$log[$type][] = "[" . date('Y-m-d H:i:s') . "] " . $msg . PHP_EOL;;
    }
    
    /**
     * 记录debug日志
     *
     * @param $msg
     */
    public static function debug($msg)
    {
        self::$log['debug'][] = "[" . date('Y-m-d H:i:s') . "] " . $msg . PHP_EOL;;
    }
    
    /**
     * 记录采集日志
     *
     * @param $msg
     * @param $rulename
     */
    public static function collect($msg, $rulename)
    {
        self::$log['collect'][] = "[" . date('Y-m-d H:i:s') . "] <{$rulename}> " . $msg . PHP_EOL;;
    }
    
    /**
     * 记录采集错误日志
     *
     * @param $msg
     * @param $rulename
     */
    public static function collecterror($msg, $rulename)
    {
        self::$log['collecterror'][] = "[" . date('Y-m-d H:i:s') . "] <{$rulename}> " . $msg . PHP_EOL;;
    }
    
    /**
     * 计划任务日志
     *
     * @param $msg
     */
    public static function cron($msg)
    {
        self::$log['cron'][] = "[" . date('Y-m-d H:i:s') . "] " . $msg . PHP_EOL;;
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
     * 手动写入指定日志到文件
     *
     * @param string $type
     */
    public static function write($type = 'pt')
    {
        $file = CACHE_PATH . '/log/' . $type . '_' . date('Ymd') . '.txt';
        if (isset(self::$log[$type])) {
            $log = self::$log[$type];
            F($file, implode('', $log), FILE_APPEND);
        }
    }
    
    /**
     * 自动写入指定类型日志
     */
    public static function build()
    {
        $logBuild = Config::get('logbuild', ['pt', 'collect', 'collecterror', 'cron']);
        foreach ($logBuild as $type) {
            self::write($type);
        }
    }
}