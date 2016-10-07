<?php
namespace ptcms;

use ptcms\driver\view\Mc;

class View
{
    
    // 模板存储变量
    protected static $_tpl_vars = [];
    // 模版基地址
    protected static $tplpath = APP_PATH . DIRECTORY_SEPARATOR . 'view';
    // 模版文件名
    protected static $tplfile = null;
    // 模版全路径
    protected static $tplfilepath = '';
    // 模版
    protected static $theme = '';
    
    public function setFile($file)
    {
        self::$tplfile = $file;
    }
    
    public function setPath($path)
    {
        self::$tplpath = $path;
    }
    
    /**
     * 模板变量赋值,支持连贯操作
     *
     * @access public
     * @param mixed $var
     * @param mixed $value
     */
    public static function set($var, $value = null)
    {
        if (is_array($var)) {
            self::$_tpl_vars = array_merge_recursive(self::$_tpl_vars, $var);
        } else {
            self::$_tpl_vars[$var] = $value;
        }
    }
    
    /*
     * 获取模板变量值
     */
    public static function get($var = '')
    {
        if ($var == '') return self::$_tpl_vars;
        if (isset(self::$_tpl_vars[$var])) return self::$_tpl_vars[$var];
        if (strpos($var, '.') !== false) {
            $arr = explode('.', $var);
            $tmp = self::$_tpl_vars;
            foreach ($arr as $v) {
                if (substr($v, 0, 1) === '$') $v = self::get($v);
                $tmp = $tmp[$v];
            }
            if (!empty($tmp)) {
                return $tmp;
            }
        }
        return null;
    }
    
    /**
     * 加载并视图片段文件内容
     *
     * @access public
     * @param string $tpl    视图片段文件名称
     * @param string $module 所属模块
     * @param string $theme  所属模版
     * @return string
     */
    public static function make($tpl = null, $data = [])
    {
        //复制参数
        self::set($data);
        //获取模板wenjian
        $tpl = ($tpl === null) ? self::$tplfile : $tpl;
        //获取模板
        self::$tplfilepath = self::getTplFile($tpl);
        extract(self::$_tpl_vars, EXTR_OVERWRITE);
        ob_start();
        include self::checkCompile();
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    }
    
    /**
     * 获得模版位置
     *
     * @param string $tpl 视图模板
     * @return string
     */
    protected static function getTplFile($tpl)
    {
        if (Config::get('app.mobileswitch', false)) {
            if (Request::isMobile()) {
                self::$tplpath .= DIRECTORY_SEPARATOR . 'mobile';
            }else{
                self::$tplpath .= DIRECTORY_SEPARATOR . 'web';
            }
        }
        if ($tpl === null) {
            //默认地址
            $tplfilepath = self::$tplpath . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, Route::$controller) . DIRECTORY_SEPARATOR . Route::$action . '.html';
        } elseif (substr($tpl, 0, 1) === '//') {
            //绝对目录 可以设置模版
            $tplfilepath = PTCMS_ROOT . substr($tpl, 1);
        } elseif (substr($tpl, 0, 1) === '/') {
            //绝对目录 可以设置模版
            $tplfilepath = self::$tplpath . $tpl;
        } else {
            //相对目录 从控制器目录开始算
            $tplfilepath = self::$tplpath . str_replace('\\', DIRECTORY_SEPARATOR, Route::$controller) . DIRECTORY_SEPARATOR . $tpl;
        }
        if (!is_file($tplfilepath)) {
            Response::error('模板文件[' . $tplfilepath . ']不存在,参数[' . $tpl . ']');
        }
        return $tplfilepath;
    }
    
    /**
     * @return string
     */
    protected static function checkCompile()
    {
        Storage::init();
        $tplfile      = ltrim(str_replace([PTCMS_ROOT, '/application/', '/template/'], '/', self::$tplfilepath), '/');
        $compiledFile = CACHE_PATH . '/template/' . substr(str_replace('/', ',', $tplfile), 0, -5) . '.php';
        if (Config::get('app.debug') || !is_file($compiledFile) || filemtime($compiledFile) < filemtime(self::$tplfilepath)) {
            // 获取模版内容
            $content = Storage::read(self::$tplfilepath);
            Plugin::hook('template_compile_start', $content);
            $driverclass = 'ptcms\driver\view\\' . Config::get('view.driver', 'Mc');
            /* @var $driver Mc */
            $driver = new $driverclass();
            // 解析模版
            $content = $driver->compile($content);
            //判断是否开启layout
            if (Config::get('view.layout', false)) {
                $includeFile = self::getTplFile(Config::get('view.layout_name', 'layout'));
                $layout      = $driver->compile(Storage::read($includeFile));
                $content     = str_replace('__CONTENT__', $content, $layout);
            }
            $content = '<?php defined(\'PTCMS_ROOT\') || exit(\'Permission denied\');?>' . self::replace($content);
            Plugin::hook('template_compile_end', $content);
            Storage::write($compiledFile, $content);
        }
        return $compiledFile;
    }

// 模版输出替换
    protected static function replace($content)
    {
        $replace = [
            '__TMPL__'    => '<?php echo __TMPL__;?>', // 项目模板目录
            '__ROOT__'    => '<?php echo PT_DIR;?>', // 当前网站地址
            '__APP__'     => '<?php echo __APP__;?>', // 当前项目地址
            '__MODULE__'  => '<?php echo __MODULE__;?>',
            '__ACTION__'  => '<?php echo __ACTION__;?>', // 当前操作地址
            '__SELF__'    => '<?php echo __SELF__;?>', // 当前页面地址
            '__URL__'     => '<?php echo  __URL__;?>', // 当前控制器地址
            '__DIR__'     => '<?php echo PT_DIR;?>', // 站点公共目录
            '__PUBLIC__'  => PUBLIC_URL, // 站点公共目录
            '__RUNINFO__' => '<?php echo \ptcms\Response::runinfo();?>', // 站点公共目录
        ];
        $content = strtr($content, $replace);
        // 判断是否显示runtime info 信息
        return $content;
    }
    
}


/**
 * 默认值函数
 *
 * @return string
 */
function defaultvar()
{
    $args  = func_get_args();
    $value = array_shift($args);
    if (!is_numeric($value)) {
        return $value;
    } elseif (isset($args[$value])) {
        return $args[$value];
    } else {
        return '';
    }
}


/**
 * 时间函数优化
 *
 * @param $time
 * @param $format
 * @return mixed
 */
function datevar($time, $format)
{
    if ($time == '0') return '';
    return date($format, $time);
}

/**
 * @param string $content
 * @return string
 */
function parseTpl($content)
{
    if ($content == '') return '';
    $cachefile = CACHE_PATH . '/template/parsetpl/' . md5($content) . '.php';
    if (!is_file($cachefile)) {
        $driverclass = 'Driver_View_' . PT_Base::getInstance()->config->get('view_driver', 'Mc');
        /* @var $driver Driver_view_MC */
        $driver  = new $driverclass();
        $content = $driver->compile($content);
        F($cachefile, $content);
    }
    return $cachefile;
}