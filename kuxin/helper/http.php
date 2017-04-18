<?php
namespace Kuxin\Helper;

use Kuxin\Config;
use Kuxin\Log;

class Http
{
    
    public static function curl($url, $params = [], $method = 'GET', $header = [], $option = [])
    {
        $opts = [
            CURLOPT_TIMEOUT        => Config::get('http.timeout', 10),
            CURLOPT_CONNECTTIMEOUT => Config::get('http.timeout', 10),
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_FOLLOWLOCATION => 1,
            CURLOPT_HEADER         => 0,
            CURLOPT_USERAGENT      => Config::get('http.user_agent', 'PTCMS Framework Http Client'),
            CURLOPT_REFERER        => isset($header['referer']) ? $header['referer'] : $url,
            CURLOPT_NOSIGNAL       => 1,
            CURLOPT_ENCODING       => 'gzip, deflate',
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ];
        
        if (isset($header['cookie'])) {
            $opts[CURLOPT_COOKIE] = $header['cookie'];
            unset($header['cookie']);
        }
        
        if (isset($header['useragent'])) {
            $opts[CURLOPT_USERAGENT] = $header['useragent'];
            unset($header['useragent']);
        }
        
        if (isset($header['showheader'])) {
            $opts[CURLOPT_HEADER] = true;
            unset($header['showheader']);
        }
        
        if (!empty($header)) {
            $opts[CURLOPT_HTTPHEADER] = $header;
        }
        
        //补充配置
        foreach ($option as $k => $v) {
            $opts[$k] = $v;
        }
        /* 根据请求类型设置特定参数 */
        switch (strtoupper($method)) {
            case 'GET':
                $opts[CURLOPT_URL] = $url;
                break;
            case 'POST':
                //判断是否传输文件
                $opts[CURLOPT_URL]        = $url;
                $opts[CURLOPT_POST]       = 1;
                $opts[CURLOPT_POSTFIELDS] = $params;
                break;
            default:
                exit('不支持的请求方式！');
        }
        
        /* 初始化并执行curl请求 */
        $ch = curl_init();
        curl_setopt_array($ch, $opts);
        $data = curl_exec($ch);
        $error = curl_error($ch);
        $errno = curl_errno($ch);
        curl_close($ch);
        if ($error && $errno !== 28) {
            if (Config::get('app.debug')) {
                trigger_error('Curl获取远程内容错误！原因：' . $error . ' 地址：' . $url);
            } else {
                Log::record('Curl获取远程内容错误！原因：' . $error . ' 地址：' . $url);
            }
            return false;
        }
        return $data;
    }
    
    public static function get($url, $data = [])
    {
        if (is_array($data)) {
            $data = http_build_query($data);
        }
        if ($data) {
            if (strpos($url, '?')) {
                $url .= '&' . $data;
            } else {
                $url .= '?' . $data;
            }
            $data = [];
        }
        return self::curl($url, $data, 'GET');
    }
    
    public static function post($url, $data = [], $header = [])
    {
        return self::curl($url, $data, 'POST', $header);
    }
    
    /**
     * 触发url
     *
     * @param $url
     */
    public static function trigger($url)
    {
        if (stripos($url, 'http') === 0) {
            if (defined('CURLOPT_TIMEOUT_MS')) {
                self::curl($url, [], 'GET', [], [
                    CURLOPT_TIMEOUT_MS        => 300,
                    CURLOPT_CONNECTTIMEOUT_MS => 300,
                ]);
            } elseif (function_exists('file_get_contents')) {
                $context        = [
                    'http' => [
                        'timeout' => 0,
                    ],
                ];
                $stream_context = stream_context_create($context);
                file_get_contents($url, false, $stream_context);
            } else {
                stream_context_set_default(
                    [
                        'http' => [
                            'method' => 'HEAD',
                        ],
                    ]
                );
                get_headers($url);
            }
        }
    }
}