namespace Kuxin;

class Response
{
    protected static type;
    protected static types = ["html", "json", "xml", "jsonp"];
    protected static autoRender = true;
    public static function getType()
    {
        if self::type {
            return self::type;
        } else {
            if Request::isAjax() {
                return "json";
            } else {
                return "html";
            }
        }
    }
    
    public static function setType(type)
    {
        if in_array(type, self::types) {
            let self::type = type;
            return self::type;
        } else {
            return false;
        }
    }
    
    protected static function getMime()
    {
        switch (self::type) {
            case "json":
                return "text/json";
            case "xml":
                return "text/xml";
            case "html":
                return "text/html";
            default:
                return "text/html";
        }
    }
    
    public static function setHeader() -> void
    {
        if !(headers_sent()) {
            //设置系统的输出字符为utf-8
            header("Content-Type: " . self::getMime() . "; charset=utf-8");
            //支持页面回跳
            header("Cache-control: private");
            //版权标识
            header("X-Powered-By: PTcms Studio (www.ptcms.com)");
            // 跨域
            if self::type == "json" {
                header("Access-Control-Allow-Origin:*");
                header("Access-Control-Allow-Headers:accept, content-type");
            }
        }
    }
    
    public static function setBody(content = "") -> void
    {
        if !(headers_sent()) {
            self::setHeader();
        }
        echo content;
    }
    
    public static function disableRender() -> void
    {
        let self::autoRender =  false;
    }
    
    public static function enableRender() -> void
    {
        let self::autoRender =  true;
    }
    
    public static function isAutoRender()
    {
        return self::autoRender;
    }
    
    public static function redirect(url, code = 302) -> void
    {
        if !(headers_sent()) {
            if code == 302 {
                header("HTTP/1.1 302 Moved Temporarily");
                header("Status:302 Moved Temporarily");
            } else {
                header("HTTP/1.1 301 Moved Permanently");
                header("Status:301 Moved Permanently");
            }
        }
        header("Location: " . url);
        exit(1);
    }

    
    /**
     * 下载文件
     *
     * @param        $con
     * @param        $name
     * @param string $type
     */
    public function download(con, name, string type = "file") -> void
    {
        var length;
    
        let length =  type == "file" ? filesize(con)  : strlen(con);
        header("Content-type: application/octet-stream");
        header("Accept-Ranges: bytes");
        header("Content-Length: " . length);
        header("Pragma: cache");
        header("Cache-Control: public, must-revalidate, max-age=0");
        header("Content-Disposition: attachment; filename=\"" . urlencode(name) . "\"; charset=utf-8");
        //下载显示的名字,注意格式
        header("Content-Transfer-Encoding: binary ");
        if type == "file" {
            readfile(con);
        } else {
            echo con;
        }
    }

}