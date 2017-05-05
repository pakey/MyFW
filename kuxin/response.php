<?php

namespace Kuxin;

/**
 * Class Response
 *
 * @package Kuxin
 * @author  Pakey <pakey@qq.com>
 */
class Response
{
    
    /**
     * @var
     */
    protected static $type;
    
    /**
     * @var array
     */
    protected static $types = ['html', 'json', 'xml', 'jsonp'];
    
    /**
     * @var bool
     */
    protected static $autoRender = true;
    
    /**
     * @return string
     */
    public static function getType()
    {
        if (self::$type) {
            return self::$type;
        } else if (Request::isAjax()) {
            return 'json';
        } else {
            return 'html';
        }
    }
    
    /**
     * @param $type
     * @return bool
     */
    public static function setType($type)
    {
        if (in_array($type, self::$types)) {
            return self::$type = $type;
        } else {
            return false;
        }
    }
    
    /**
     * @return string
     */
    protected static function getMime()
    {
        switch (self::$type) {
            case 'json':
                return 'text/json';
            case 'xml':
                return 'text/xml';
            case 'html':
                return 'text/html';
            default:
                return 'text/html';
        }
    }
    
    /**
     *
     */
    public static function setHeader()
    {
        if (!headers_sent()) {
            //设置系统的输出字符为utf-8
            header("Content-Type: " . self::getMime() . "; charset=utf-8");
            //支持页面回跳
            header("Cache-control: private");
            //版权标识
            header("X-Powered-By: PTcms Studio (www.ptcms.com)");
            // 跨域
            if (self::$type == 'json') {
                header('Access-Control-Allow-Origin:*');
                header('Access-Control-Allow-Headers:accept, content-type');
            }
        }
    }
    
    /**
     * @param string $content
     */
    public static function setBody($content = '')
    {
        if (!headers_sent()) {
            self::setHeader();
        }
        echo $content;
    }
    
    /**
     *
     */
    public static function disableRender()
    {
        self::$autoRender = false;
    }
    
    /**
     *
     */
    public static function enableRender()
    {
        self::$autoRender = true;
    }
    
    /**
     * @return bool
     */
    public static function isAutoRender()
    {
        return self::$autoRender;
    }
    
    /**
     * @param     $url
     * @param int $code
     */
    public static function redirect($url, $code = 302)
    {
        if (!headers_sent()) {
            if ($code == 302) {
                header('HTTP/1.1 302 Moved Temporarily');
                header('Status:302 Moved Temporarily'); // 确保FastCGI模式下正常
            } else {
                header('HTTP/1.1 301 Moved Permanently');
                header('Status:301 Moved Permanently');
            }
        }
        header('Location: ' . $url);
        exit;
    }
    
    /**
     * @return mixed|string
     */
    public function runinfo()
    {
        if (Config::get('is_gen_html')) return '';
        $tpl    = Config::get('runinfo', 'Power by PTCMS, Processed in {time}(s), Memory usage: {mem}MB.');
        $from[] = '{time}';
        $to[]   = number_format(microtime(true) - $GLOBALS['_startTime'], 3);
        $from[] = '{mem}';
        $to[]   = number_format((memory_get_usage() - $GLOBALS['_startUseMems']) / 1024 / 1024, 3);
        if (strpos($tpl, '{net}')) {
            $from[] = '{net}';
            $to[]   = $GLOBALS['_apinum'];
        }
        if (strpos($tpl, '{file}')) {
            $from[] = '{file}';
            $to[]   = count(get_included_files());
        }
        if (strpos($tpl, '{sql}')) {
            $from[] = '{sql}';
            $to[]   = $GLOBALS['_sqlnum'];
        }
        if (strpos($tpl, '{cacheread}')) {
            $from[] = '{cacheread}';
            $to[]   = $GLOBALS['_cacheRead'];
        }
        if (strpos($tpl, '{cachewrite}')) {
            $from[] = '{cachewrite}';
            $to[]   = $GLOBALS['_cacheWrite'];
        }
        $runtimeinfo = str_replace($from, $to, $tpl);
        return $runtimeinfo;
    }
    
    
    /**
     * 下载文件
     *
     * @param        $con
     * @param        $name
     * @param string $type
     */
    public function download($con, $name, $type = 'file')
    {
        $length = ($type == 'file') ? filesize($con) : strlen($con);
        header("Content-type: application/octet-stream");
        header("Accept-Ranges: bytes");
        header("Content-Length: " . $length);
        header('Pragma: cache');
        header('Cache-Control: public, must-revalidate, max-age=0');
        header('Content-Disposition: attachment; filename="' . urlencode($name) . '"; charset=utf-8'); //下载显示的名字,注意格式
        header("Content-Transfer-Encoding: binary ");
        if ($type == 'file') {
            readfile($con);
        } else {
            echo $con;
        }
    }
    
    /**
     * 屏幕输出
     *
     * @param $text
     * @param $type
     * @param $line
     * @return mixed
     */
    public static function screen($text, $type, $line = true)
    {
        switch ($type) {
            case 'success':
                $color = 'green';
                break;
            case 'error':
                $color = 'red';
                break;
            case 'warning':
                $color = "orangered";
                break;
            case 'info':
                $color = 'darkblue';
                break;
            default:
                $color = $type;
        }
        $line = $line ? '<br/>' . PHP_EOL : '';
        if ($color) {
            echo "<span style='color:{$color}'>{$text}</span>{$line}";
        } else {
            echo "<span>{$text}</span>{$line}";
        }
    }
    
    
    /**
     * 终端输出
     *
     * @param $text
     * @param $type
     * @param $line
     * @return mixed
     */
    public static function terminal($text, $type, $line = true)
    {
        $end = chr(27) . "[0m";
        switch (strtolower($type)) {
            case "success":
                $pre = chr(27) . "[32m"; //Green
                break;
            case "error":
                $pre = chr(27) . "[31m"; //Red
                break;
            case "warning":
                $pre = chr(27) . "[33m"; //Yellow
                break;
            case 'info':
                $pre = chr(27) . '[36m'; //蓝色
                break;
            default:
                $pre = "";
                $end = '';
        }
        $line = $line ? PHP_EOL : '';
        if (stripos($text, '<br')) {
            $text = str_ireplace(['<br /><br />', '<br/><br/>', '<br/>', '<br />'], PHP_EOL, $text);
        }
        $text = strip_tags($text);
        return $pre . $text . $end . $line;
    }
    
    /**
     * @param $arr
     */
    public static function debug($arr)
    {
        echo '<pre>';
        print_r($arr);
        echo '</pre>', PHP_EOL;
    }
}
