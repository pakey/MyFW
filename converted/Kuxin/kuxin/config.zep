namespace Kuxin;

class Config
{
    protected static _config = [];
    /**
     * 获取参数
     *
     * @param string $name       参数名
     * @param null   $defaultVar 默认值
     * @return array|null
     */
    public static function get(string name = "",  defaultVar = null)
    {
        var group, key , tmp;
    
        if name == "" {
            return self::_config;
        }
        let name =  strtolower(name);
        if strpos(name, ".") {
            //数组模式 找到返回
            let tmp = explode(".", name, 2),group = tmp[0],key = tmp[1];
            return  isset self::_config[group][key] ? self::_config[group][key]  : defaultVar;
        } else {
            return  isset self::_config[name] ? self::_config[name]  : defaultVar;
        }
    }
    
    /**
     * @param        $name
     * @param string $var
     */
    public static function set(name, string varr = "") -> void
    {
        var group, key , tmp;
    
        //数组 调用注册方法
        if is_array(name) {
            self::register(name);
        } else {
            if strpos(name, ".") {
                let tmp = explode(".", name, 2),group = tmp[0],key = tmp[1],
                self::_config[group][key] = varr;
            } else {
                let self::_config[name] = varr;
            }
        }
    }
    
    /**
     * 注册配置
     *
     * @param $config
     */
    public static function register(config) -> void
    {
        if is_array(config) {
            let self::_config =  array_merge(self::_config, config);
        }
    }

}