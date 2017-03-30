<?php

namespace Kuxin;


class View
{
    
    // 模板存储变量
    protected static $_tpl_vars = [];
    // 模版基地址
    protected static $tplpath = '';
    // 模版文件名
    protected static $tplfile = null;
    // 模版全路径
    protected static $tplfilepath = '';
    // 模版
    protected static $theme = '';
    //
    protected static $mobileswitch = false;
    
    
    public static function setFile($file)
    {
        self::$tplfile = $file;
    }
    
    public static function setPath($path)
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
            self::$_tpl_vars = array_merge(self::$_tpl_vars, $var);
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
     * @param string $tpl  视图片段文件名称
     * @param array  $data 附加数据
     * @return string
     */
    public static function make($tpl = null, array $data = [])
    {
        //获取风格
        self::getTheme();
        //复制参数
        if ($data) {
            self::set($data);
        }
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
     * 获取风格
     *
     * @return mixed|string
     */
    public static function getTheme()
    {
        
        if (self::$theme = Config::get('tpl_theme', 'default')) {
            //设置了默认模版名
            //值不为空 则为自动侦测目录
            if (Input::has('t')) {
                $auto = Input::get('t', 'str', '');
                if ($auto == 'clear') {
                    Cookie::remove('THEME');
                } else {
                    Cookie::set('THEME', $auto, 25920000);
                }
            } elseif (Cookie::get('THEME')) {
                $auto = Cookie::get('THEME');
            }
            if (isset($auto)) {
                if (is_dir(self::$tplpath . '/' . $auto)) {
                    self::$theme = $auto;
                    Config::set('tpl_theme', self::$theme);
                } else {
                    Cookie::remove('THEME');
                }
            }
            //读取模版配置文件
            if ($tplconfig = Loader::import(self::$tplpath . '/' . self::$theme . '/config.php')) {
                foreach ($tplconfig as $k => $v) {
                    Config::set("tplconfig.{$k}", $v['value']);
                }
            }
        }
        return self::$theme;
    }
    
    
    /**
     * 获得模版位置
     *
     * @param string $tpl 视图模板
     * @return string
     */
    protected static function getTplFile($tpl)
    {
        $tpl     = ($tpl === null) ? self::$tplfile : $tpl;
        $theme   = self::$theme;
        $module  = MODULE_NAME;
        $protect = Config::get('tpl_protect', '');
        if (substr($tpl, 0, 1) === '/') { //绝对目录 可以设置模版
            $tplfile = PT_ROOT . $tpl;
            $tmpl    = self::$tplpath . "/{$theme}/public";
        } elseif (substr($tpl, 0, 1) == '@') {
            $tplfile = self::$tplpath . "/{$theme}/" . ($protect ? ($protect . '/') : '') . substr($tpl, 1);
            $tmpl    = self::$tplpath . "/{$theme}/public";
        } else {
            if (!$tpl) {
                $tpl = CONTROLLER_NAME . '_' . ACTION_NAME;
            }
            //判断模版目录
            $suffix = Config::get('tpl_suffix', 'html');
            if ($theme) {
                // 设置了模版 模版目录为template下对应的设置的模版目录
                $tplfile = rtrim(self::$tplpath . ($protect ? "/{$theme}/{$protect}/{$module}" : "/{$theme}/{$module}"), '/') . "/{$tpl}.{$suffix}";
                if (!is_file($tplfile)) {
                    //没有找到的模版默认使用default匹配一次
                    if (is_file(self::$tplpath . "/{$theme}/{$module}/{$tpl}.{$suffix}")) {
                        //去掉保护目录
                        $tplfile = self::$tplpath . "/{$theme}/{$module}/{$tpl}.{$suffix}";
                        Log::debug('指定的模版（' . $tplfile . '）不存在，去除模板保护目录成功');
                    } elseif ($theme !== 'default' && is_file(self::$tplpath . "/default/{$module}/{$tpl}.{$suffix}")) {
                        //使用默认模版
                        $tplfile     = self::$tplpath . "/default/{$module}/{$tpl}.{$suffix}";
                        self::$theme = $theme = 'default';
                        Log::debug('指定的模版（' . $tplfile . '）不存在，尝试使用默认模版成功');
                    }
                }
                $tmpl = self::$tplpath . "/{$theme}/public";
            } else {
                //未设置模版 模版目录为对应模块的view目录 后台专属
                $tplfile = APP_PATH . "/{$module}/view/{$tpl}.{$suffix}";
                $tmpl    = APP_PATH . "/{$module}/view/";
            }
        }
        $realtpl = str_replace('\\', '/', realpath($tplfile));
        if (!$realtpl) {
            trigger_error("模版{$tpl}不存在:" . $tplfile, E_USER_ERROR);
        }
        defined('__TMPL__') || define('__TMPL__', rtrim(PT_DIR . str_replace(PT_ROOT, '', $tmpl), '/'));
        return $realtpl;
    }
    
    /**
     * @return string
     */
    protected static function checkCompile()
    {
        $tplfile      = ltrim(str_replace([PT_ROOT, '/application/', '/template/'], '/', self::$tplfilepath), '/');
        $compiledFile = CACHE_PATH . '/template/' . substr(str_replace('/', ',', $tplfile), 0, -5) . '.php';
        if (APP_DEBUG || !is_file($compiledFile) || filemtime($compiledFile) < filemtime(self::$tplfilepath)) {
            // 获取模版内容
            $content = F(self::$tplfilepath);
            Plugin::call('template_compile_start', $content);
            $driverclass = 'Driver_View_' . Config::get('view_driver', 'Mc');
            /* @var $driver Driver_view_MC */
            $driver = new $driverclass();
            // 解析模版
            $content = $driver->compile($content);
            //判断是否开启layout
            if (Config::get('layout', false)) {
                $includeFile = self::getTplFile(Config::get('layout_name', 'layout'));
                $layout      = $driver->compile(F($includeFile));
                $content     = str_replace('__CONTENT__', $content, $layout);
            }
            $content = '<?php defined(\'PT_ROOT\') || exit(\'Permission denied\');?>' . self::replace($content);
            Plugin::call('template_compile_end', $content);
            F($compiledFile, $content);
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
            '__PUBLIC__'  => '<?php echo PT_DIR;?>' . '/public', // 站点公共目录
            '__RUNINFO__' => '<?php echo Response::runinfo();?>', // 站点公共目录
        ];
        $content = strtr($content, $replace);
        // 判断是否显示runtime info 信息
        return $content;
    }
    
}