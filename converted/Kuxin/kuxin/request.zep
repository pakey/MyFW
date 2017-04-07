namespace Kuxin;

class Request
{
    public static function isGet()
    {
        return  _SERVER["REQUEST_METHOD"] === "GET" ? true  : false;
    }
    
    public static function isPost()
    {
        return  _SERVER["REQUEST_METHOD"] === "POST" ? true  : false;
    }
    
    public static function isAjax()
    {
        return  isset _SERVER["HTTP_X_REQUESTED_WITH"] && strtolower(_SERVER["HTTP_X_REQUESTED_WITH"]) == "xmlhttprequest" || (isset(_POST["isajax"])) || (isset(_GET["isajax"])) ? true  : false;
    }
    
    public static function isMobile()
    {
        var clientkeywords;
    
        // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
        if isset _SERVER["HTTP_X_WAP_PROFILE"] {
            return true;
        }
        // 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
        if isset _SERVER["HTTP_VIA"] {
            // 找不到为flase,否则为true
            if stristr(_SERVER["HTTP_VIA"], "wap") {
                return true;
            }
        }
        // 脑残法，判断手机发送的客户端标志,兼容性有待提高
        if isset _SERVER["HTTP_USER_AGENT"] {
            let clientkeywords =  ["nokia", "sony", "ericsson", "mot", "samsung", "htc", "sgh", "lg", "sharp", "sie-", "philips", "panasonic", "alcatel", "lenovo", "iphone", "ipod", "blackberry", "meizu", "android", "netfront", "symbian", "ucweb", "windowsce", "palm", "operamini", "operamobi", "openwave", "nexusone", "cldc", "midp", "wap", "mobile", "UCBrowser"];
            // 从HTTP_USER_AGENT中查找手机浏览器的关键字
            if preg_match("/(" . implode("|", clientkeywords) . ")/i", strtolower(_SERVER["HTTP_USER_AGENT"])) {
                return true;
            }
        }
        // 协议法，因为有可能不准确，放到最后判断
        if isset _SERVER["HTTP_ACCEPT"] {
            // 如果只支持wml并且不支持html那一定是移动设备
            // 如果支持wml和html但是wml在html之前则是移动设备
            if strpos(_SERVER["HTTP_ACCEPT"], "vnd.wap.wml") !== false && (strpos(_SERVER["HTTP_ACCEPT"], "text/html") === false || strpos(_SERVER["HTTP_ACCEPT"], "vnd.wap.wml") < strpos(_SERVER["HTTP_ACCEPT"], "text/html")) {
                return true;
            }
        }
        if _SERVER["HTTP_HOST"] == parse_url(Config::get("wap_domain"), PHP_URL_HOST) {
            return true;
        }
        return false;
    }

    public static function isSpider(var ua = null)
    {
        var spiders, spider;
        if empty(ua){
            let ua = _SERVER["HTTP_USER_AGENT"];
        }
        let ua =  strtolower(ua);
        let spiders =  ["bot", "crawl", "spider", "slurp", "sohu-search", "lycos", "robozilla"];
        for spider in spiders {
            if strpos(ua, spider) !== false {
                return true;
            }
        }
        return false;
    }

    public static function getIp(defaultIp = "0.0.0.0")
    {
        var ip, i, keys, key, ips, l;

        let ip = _SERVER["REMOTE_ADDR"];
        let i =  explode(".", ip);
        if i[0] == 10 || i[0] == 172 && i[1] > 15 && i[1] < 32 || i[0] == 192 && i[1] == 168 {
            //如果是内网ip重新获取
            let keys =  ["HTTP_X_FORWARDED_FOR", "HTTP_CLIENT_IP", "HTTP_X_REAL_IP"];
            for key in keys {
                if empty(_SERVER[key]) {
                    continue;
                }
                let ips =  explode(",", _SERVER[key], 1);
                let ip = ips[0];
                break;
            }
        }
        let l =  ip2long(ip);
        if l !== false && ip === long2ip(l) {
            return ip;
        }
        return defaultIp;
    }
}