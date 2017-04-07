namespace Kuxin;

class Router
{
    static controller = "index";
    static action = "index";
    /**
     * 解析controller和action
     */
    public static function dispatcher() -> void
    {
        var superVar, param;
    
        //解析s变量
        if isset _GET["s"] {
            let superVar = _GET["s"];
            //判断是否需要进行rewrite转换
            if Config::get("power.rewrite") {
                let superVar =  self::rewrite(superVar);
            }
            if strpos(superVar, "/") {
                if strpos(superVar, ".") {
                    let param =  explode(".", superVar, 2);
                    Response::setType(param["1"]);
                    let param =  explode("/", param["0"]);
                } else {
                    let param =  explode("/", superVar);
                }
                let self::action =  array_pop(param);
                let self::controller =  implode("\\", param);
            }
            unset _GET["s"];
        
        }
    }
    
    /**
     * 正则模式解析
     */
    public static function rewrite(superVar)
    {
        var router, rule, url, query, tmpListUrlQuery, param, varr, _GET, match;
    
        let router =  Config::get("app.router.rewrite");
        if router {
            for rule, url in router {
                if preg_match("{" . rule . "}isU", superVar, match) {
                    unset match["0"];
                    
                    if strpos(url, "?") {
                        let tmpListUrlQuery = explode("?", url);
                        let url = tmpListUrlQuery[0];
                        let query = tmpListUrlQuery[1];
                    }
                    let superVar =  rtrim(url, "/");
                    if match && !(empty(query)) {
                        //组合后面的参数
                        let param =  explode("&", query);
                        let varr =  array_combine(param, match);
                        if count(param) == count(match) && varr {
                            let _GET =  array_merge(_GET, varr);
                        }
                    }
                    break;
                }
            }
        }
        return superVar;
    }

}