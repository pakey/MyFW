<?php

class http {

    public static function curl($url, $params = array(), $method = 'GET', $header = array()) {
        $opts = array(
            CURLOPT_TIMEOUT => C('timeout', null, 10),
            CURLOPT_CONNECTTIMEOUT => C('timeout', null, 10),
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_FOLLOWLOCATION => 1,
            CURLOPT_HEADER => false,
            CURLOPT_USERAGENT => C('user_agent', null, 'PTSingleNovel'),
            CURLOPT_REFERER => $url,
            CURLOPT_NOSIGNAL => 1,
            CURLOPT_ENCODING => 'gzip, deflate',
            CURLOPT_HTTPHEADER => $header,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        );

        if (ini_get("safe_mode") || ini_get('open_basedir')) {
            unset($opts[CURLOPT_FOLLOWLOCATION]);
        }
        /* 根据请求类型设置特定参数 */
        switch (strtoupper($method)) {
            case 'GET':
                $opts[CURLOPT_URL] = $url;
                break;
            case 'POST':
                //判断是否传输文件
                $opts[CURLOPT_URL] = $url;
                $opts[CURLOPT_POST] = 1;
                $opts[CURLOPT_POSTFIELDS] = $params;
                break;
            default:
                exit('不支持的请求方式！');
        }

        /* 初始化并执行curl请求 */
        $ch = curl_init();
        curl_setopt_array($ch, $opts);
        $data = curl_exec($ch);
        //todo safe_mode模式下需要处理的location
        $error = curl_error($ch);
        curl_close($ch);
        if ($error) return false;
        return $data;
    }

    public static function filegc($url, $params = array(), $method = 'GET') {
        $header = array("Referer: $url", "User-Agent: " . C('user_agent', null, 'PTSingleNovel'));
        $context = array(
            'http' => array(
                'method' => $method,
                'header' => implode("\r\n", $header),
                'timeout' => C('timeout', null, 10),
            )
        );
        if ($method == 'POST') {
            if (is_array($params)) $params = http_build_query($params);
            $content_length = strlen($params);
            $header[] = "Content-type: application/x-www-form-urlencoded";
            $header[] = "Content-length: $content_length";
            $context['http']['header'] = implode("\r\n", $header);
            $context['http']['content'] = $params;
        }
        $stream_context = stream_context_create($context);
        $data = file_get_contents($url, false, $stream_context);
        return $data;
    }

    public static function fsock($url, $params, $method = 'GET') {
        $urlinfo = parse_url($url);
        $port = isset($urlinfo["port"]) ? $urlinfo["port"] : 80;
        $path = $urlinfo['path'] . (!empty($urlinfo['query']) ? '?' . $urlinfo['query'] : '') . (!empty($urlinfo['fragment']) ? '#' . $urlinfo['fragment'] : '');

        $in = "{$method} {$path} HTTP/1.1\r\n";
        $in .= "Host: {$urlinfo['host']}\r\n";
        $in .= "Content-Type: application/octet-stream\r\n";
        $in .= "Connection: Close\r\n";
        $in .= "Hostname: {$urlinfo['host']}\r\n";
        $in .= "User-Agent: " . C('user_agent', null, 'PTSingleNovel') . "\r\n";
        $in .= "Referer: {$url}\r\n";
        if ($method == 'POST') {
            $params = is_array($params) ? http_build_query($params) : $params;
            $in .= "Content-Length: " . strlen($params) . "\r\n\r\n";
        }

        $address = gethostbyname($urlinfo['host']);
        $fp = fsockopen($address, $port, $err, $errstr, C('timeout', null, 10));
        if (!$fp) {
            exit ("cannot conncect to {$address} at port {$port} '{$errstr}'");
        }
        fwrite($fp, $in . $params, strlen($in . $params));

        $f_out = '';
        while ($out = fread($fp, 2048))
            $f_out .= $out;

        $tmp = explode("\r\n\r\n", $f_out);
        fclose($fp);
        return $tmp[1];
    }

    public static function get($url, $data = array()) {
        $func = C('httpmethod', null, 'curl');
        if (is_array($data)) {
            $data = http_build_query($data);
        }
        if ($data) {
            if (strpos($url, '?')) {
                $url .= '&' . $data;
            } else {
                $url .= '?' . $data;
            }
            $data = array();
        }
        $t = microtime(true);
        $res = self::$func($url, $data, 'GET');
        $GLOBALS['_api'][] = $func . ' GET ' . number_format(microtime(true) - $t, 5) . ' ' . $url;
        return $res;
    }

    public static function post($url, $data = array()) {
        $func = C('httpmethod', null, 'curl');
        $t = microtime(true);
        $res = self::$func($url, $data, 'POST');
        $GLOBALS['_api'][] = $func . ' POST ' . number_format(microtime(true) - $t, 5) . ' ' . $url . json_encode($data, 256);
        return $res;
    }

    public static function getMethod() {
        $method = array();
        if (function_exists('curl_init')) {
            $method['curl'] = 'curl函数(推荐)';
        }
        if (function_exists('fsockopen') && ini_get('allow_url_fopen')) {
            $method['fsock'] = 'fsockopen函数';
        }
        if (function_exists('file_get_contents') && ini_get('allow_url_fopen')) {
            $method['filegc'] = 'file_get_content函数';
        }
        return $method;
    }
}