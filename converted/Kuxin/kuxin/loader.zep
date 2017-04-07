namespace Kuxin;

class Loader
{
    static _importFiles = [];

    static _class = [];

    /**
     * 加载文件
     * @param $filename
     * @return mixed
     */
    public static function import(filename)
    {
        if !(isset self::_importFiles[filename]) {
            let self::_importFiles[filename] = (require filename);
        }
        return self::_importFiles[filename];
    }
    
    /**
     * 初始化类
     * @param       $class
     * @param array $args
     * @return mixed
     */
    public static function instance(string classname, array args = [])
    {
        var key;
    
        let key =  md5(classname . "_" . serialize(args));
        if !isset(self::_class[key]) {
            let self::_class[key] =  (new \ReflectionClass(classname))->newInstanceArgs(args);
        }
        return self::_class[key];
    }

}