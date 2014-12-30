<?php

//兼容行定义
defined('JSON_PRETTY_PRINT') || define('JSON_PRETTY_PRINT', 128);
defined('JSON_UNESCAPED_UNICODE') || define('JSON_UNESCAPED_UNICODE', 256);

class PT_Response extends PT_Base {

    protected $autoRender = true;

    public function setHeader($mimeType = 'text/html') {
        if ($this->config->get('gzip_encode', false)) {
            $zlib = ini_get('zlib.output_compression');
            if (empty($zlib)) ob_start('ob_gzhandler');
        }
        if (!headers_sent()) {
            //设置系统的输出字符为utf-8
            header("Content-Type: $mimeType; charset=utf-8");
            //支持页面回跳
            header("Cache-control: private");
            //版权标识
            header("X-Powered-By: PTcms Studio (www.ptcms.com)");
        }
    }

    public function setBody($content = '', $mimeType = 'text/html') {
        if (!headers_sent()) {
            $this->setHeader($mimeType);
        }
        echo $content;
    }

    public function disableRender() {
        $this->autoRender = false;
    }

    public function enableRender() {
        $this->autoRender = true;
    }

    public function isAutoRender() {
        return $this->autoRender;
    }


    public function jsonEncode($data, $format = 0) {
        if (APP_DEBUG && $format == 0) {
            $format = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE;
        }
        return json_encode($data, $format);
    }

    public function jsonpEncode($data, $format = 0) {
        if (APP_DEBUG && $format == 0) {
            $format = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE;
        }
        $callback = $this->input->get($this->config->get('jsonp_callback'), 'en', 'ptcms_jsonp');
        return $callback . '(' . json_encode($data, $format) . ');';
    }

    /**
     * XML编码
     *
     * @param mixed  $data     数据
     * @param string $root     根节点名
     * @param string $attr     根节点属性
     * @param string $encoding 数据编码
     * @return string
     */
    public function xmlEncode($data, $root = 'ptcms', $attr = '', $encoding = 'utf-8') {
        if (is_array($attr)) {
            $_attr = array();
            foreach ($attr as $key => $value) {
                $_attr[] = "{$key}=\"{$value}\"";
            }
            $attr = implode(' ', $_attr);
        }
        $attr = trim($attr);
        $attr = empty($attr) ? '' : " {$attr}";
        $xml  = "<?xml version=\"1.0\" encoding=\"{$encoding}\"?>";
        $xml .= "<{$root}{$attr}>";
        $xml .= $this->dataToXml($data);
        $xml .= "</{$root}>";
        return preg_replace('/[\x00-\x1f]/', '', $xml);
    }

    public function error($msg = '找不到指定的页面', $level = 'f') {
        if (APP_DEBUG) {
            halt($msg);
        } else {
            if ($level != 'f') {
                $this->controller->error($msg, 0, 0);
            } else {
                $this->error($msg, '', 0);
                $file = PT_ROOT . '/' . $this->config->get('404file', '404.html');
                $this->log->write($msg);
                if (is_file($file)) {
                    $content = F($file);
                    $content = str_replace(array('{$sitename}', '{$siteurl}', '{$msg}'), array($this->config->get('sitename', 'PTCMS FrameWork'), $this->config->get('siteurl', PT_URL), $msg), $content);
                    exit($content);
                } else {
                    exit($msg . ' 页面出现错误，如需自定义此错误，请创建文件：' . $file);
                }
            }
        }
        exit;
    }

    public function _empty($msg) {
        if (APP_DEBUG) {
            $this->error($msg, '', 0);
        } else {
            halt($msg);
        }
    }

    public function redirect($url, $type = 302) {
        if ($type == 302) {
            header('HTTP/1.1 302 Moved Temporarily');
            header('Status:302 Moved Temporarily'); // 确保FastCGI模式下正常
        } else {
            header('HTTP/1.1 301 Moved Permanently');
            header('Status:301 Moved Permanently');
        }
        header('Location: ' . $url);
        exit;
    }

    public function runinfo() {
        if ($this->config->get('is_gen_html')) return '';
        $tpl    = $this->config->get('runinfo', 'Power by PTCMS, Processed in {time}(s), Memory usage: {mem}MB.');
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
     * 数据XML编码
     *
     * @param mixed  $data 数据
     * @param string $parentkey
     * @return string
     */
    protected function dataToXml($data, $parentkey = '') {
        $xml = '';
        foreach ($data as $key => $val) {
            if (is_numeric($key)) {
                $key = $parentkey;
            }
            $xml .= "<{$key}>";
            if (is_array($val) || is_object($val)) {
                $len = strlen("<{$key}>");
                $con = $this->dataToXml($val, $key);
                if (strpos($con, "<{$key}>") === 0) {
                    $con = substr($con, $len, -($len + 1));
                }
                $xml .= $con;
            } elseif (strlen($val) > 150 || preg_match('{[<>&\'|"]+}', $val)) {
                $xml .= '<![CDATA[' . $val . ']]>';
            } else {
                $xml .= $val;
            }
            $xml .= "</{$key}>";
        }
        return $xml;
    }

    /**
     * 下载文件
     * @param        $con
     * @param        $name
     * @param string $type
     */
    public function download($con, $name, $type = 'file') {
        $length = ($type == 'file') ? filesize($con) : strlen($con);
        header("Content-type: application/octet-stream");
        header("Accept-Ranges: bytes");
        header("Content-Length: " . $length);
        header('Pragma: cache');
        header('Cache-Control: public, must-revalidate, max-age=0');
        header('Content-Disposition: attachment; filename="' . urlencode($name) . '.txt"; charset=utf-8'); //下载显示的名字,注意格式
        header("Content-Transfer-Encoding: binary ");
        if ($type == 'file') {
            readfile($con);
        } else {
            echo $con;
        }
    }


}