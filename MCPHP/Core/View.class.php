<?php
/***************************************************************
 *  $Program: MCPHP FarmeWork $
 *  $Author: pakey $
 *  $Email: Pakey@ptcms.com $
 *  $Copyright: 2009 - 2012 Ptcms Studio $
 *  $Link: http://www.ptcms.com $
 *  $License: http://www.ptcms.com/service/license.html $
 *  $Date: 2013-04-28 16:02:12 +0800 (星期日, 28 四月 2013) $
 *  $File: View.class.php $
 *  $Revision: 78 $
 *  $Desc:
 **************************************************************/

defined('MC_PATH') || exit('Permission denied');

class View
{
    // 模板存储变量
    protected static $_var = array();

    /**
     * 模板变量赋值
     * @access public
     * @param mixed $var
     * @param mixed $value
     * @return void
     */
    public function assign($var, $value = null)
    {
        if (is_array($var)){
            self::$_var=array_merge(self::$_var, $var);
        }else{
            self::$_var[$var]=$value;
        }
    }

    /*
     * 获取模板变量值
     */
    public function get_assign($var)
    {
        if (isset(self::$_var[$var])) return self::$_var[$var];
        if (strpos($var,'.')!==false){
            $arr=explode('.',$var);
            $tmp=self::$_var;
            foreach($arr as $v){
                if (substr($v,0,1)==='$') $v= $this->get_assign($v);
                $tmp=$tmp[$v];
            }
            if (!empty($tmp)){
                return $tmp;
            }
        }
        return '';
    }

    /**
     * 加载并视图片段文件内容
     * @access public
     * @param string  $tpl  视图片段文件名称
	 * @param array $data  自定义数据输出
     * @return string
     */
    public function fetch($tpl = null,$data=array())
    {
		$tplFile = $this->getTplFile($tpl);
		if (empty($data)){
			extract(array_change_key_case(self::$_var, CASE_LOWER), EXTR_OVERWRITE);
		}else{
			extract(array_change_key_case($data, CASE_LOWER), EXTR_OVERWRITE);
		}
        ob_start();
        include $this->compiled($tplFile);
        $content = ob_get_contents();
        ob_end_clean();
        return $this->replaceRuninfo($content);
    }

    /**
     * 输出视图内容
     * @access public
     * @param string $content   输出内容
     * @param string $mimeType  MIME类型
     * @return void
     */
    public function show($content, $mimeType = 'text/html')
    {
        if (C('OUTPUT_ENCODE')) {
            $zlib = ini_get('zlib.output_compression');
            if (empty($zlib)) ob_start('ob_gzhandler');
        }
        header("Content-Type: $mimeType; charset=utf-8"); //设置系统的输出字符为utf-8
        header("Cache-control: private"); //支持页面回跳
        header("X-Powered-By: MCPHP Framework (www.mcphp.com)");
        echo $content;
    }

    /**
     * 显示当前页面的视图内容
     * @access public
     * @param string $tpl  视图模板
	 * @param array $data  自定义数据输出
     * @return void
     */
    public function display($tpl = null,$data=array())
    {
        $content = $this->fetch($tpl,$data);
        $this->show($content);
    }

    /**
     * 获得模版位置
     * display() 读取当前的方法的
     * display('t') 读取t方法的
     * display('a_b') 读取a控制器b方法的
     * display(':a/b_c) 读取a模块b控制器c方法
     * display('#***') 读取相对于模版目录的
     * display('@****') 绝对目录
     *
     * @param $tpl
     * @return string
     */
    protected function getTplFile($tpl)
    {
        //获得风格名
        $theme = C('THEME_NAME') ;
        //获得分组
        if (isset($_GET['m'])) {
            $module = MODULE_NAME . '/';
        } else {
            $module = '';
        }
        //拼装模版
        $depr = C('TPL_FILEDEPR');
        if ($tpl == '') {
            $tplFile = $theme .'/'. $module . CONTROLLER_NAME . $depr . ACTION_NAME;
        } elseif (substr($tpl, 0, 1) == ':') {
            $tplFile = $theme .'/'. substr($tpl, 1);
        } elseif (substr($tpl, 0, 1) == '#') {
            $tplFile = $theme .'/'. $module.substr($tpl, 1);
        } elseif (strpos($tpl,C('TPL_SUFFIX'))!==false) {
            $tplFile = $theme .'/'. $module.$tpl;
        } elseif (substr($tpl, 0, 1) == '@') {
            $tplFile = substr($tpl, 1);
        } elseif (strpos($tpl, $depr) === false) {
            $tplFile = $theme .'/'. $module . CONTROLLER_NAME . $depr . $tpl;
        } else {
            $tplFile = $theme .'/'. $module . $tpl;
        }

        //模版常量定义
        $pathinfo = pathinfo($tplFile);
        $tplFile=str_replace($pathinfo['basename'],ucfirst($pathinfo['basename']),$tplFile);
        if (!isset($pathinfo['extension'])) {
            $tplFile = $tplFile . C('TPL_SUFFIX');
        }
        if (strpos($tplFile, MC_ROOT) === false) {
            $tplFileName = TPL_PATH . $tplFile;
        } else {
            $tplFileName = $tplFile;
        }
        if (!is_file($tplFileName)) {
            $newTplFileName = str_replace(TPL_PATH.$theme, TPL_PATH.'default', $tplFileName);
            if (!is_file($newTplFileName)) {
                halt($tplFile . '模版不存在');
            } else {
                $tplFileName = $newTplFileName;
            }
        }
        defined('__TMPL__') || define('__TMPL__', ROOT . '/' . str_replace(MC_ROOT, '', dirname($tplFileName)));
        return $tplFileName;
    }

    public function getTheme()
    {
        /* 获取模板主题名称 */
        $tmplStyle = C('DEFAULT_THEME');
        if (C('TPL_DETECT_THEME')) { // 自动侦测模板主题
            if (isset($_GET['t'])) {
                $tmplStyle = $_GET['t'];
                cookie('THEME', $tmplStyle,2592000);
            } elseif (cookie('THEME')) {
                $tmplStyle = cookie('THEME');
            }
            if (!is_dir(TPL_PATH . $tmplStyle)) {
                $tmplStyle = C('DEFAULT_THEME');
                cookie('THEME', $tmplStyle,2592000);
            }
        }
        /* 模板相关目录常量 */
        C('THEME_NAME', $tmplStyle); // 当前模板主题名称
    }

    // 校验编译模板
    protected function compiled($tplFile)
    {
        /*
         * 存内存时解析会出错 待查
        $cacheType = C('DATA_CACHE_TYPE');
        if ($cacheType !== 'File') {
            $ini = ini_get_all();
            if ($ini['allow_url_include']['local_value'] == 0 || APP_DEBUG) $cacheType = 'File';
        }
        */
        $cacheType='File';
        $compiledFile = CACHE_PATH . 'template/' . md5($tplFile) . '.php';
        $compile = true;
        if (!APP_DEBUG) {
            if ($cacheType == 'File') {
                if (is_file($compiledFile) && filemtime($compiledFile) > filemtime($tplFile)) $compile = false;
            } else {
                $tmp = S(md5($compiledFile));
                if (isset($tmp) && $tmp == filemtime($tplFile)) $compile = false;
            }
        }
        if ($compile) {
            /*
             * 调用子类解析模板
             */
            $class = 'Tpl' . ucfirst(C('TPL_DRIVER'));
            if (class_exists($class)){
                $tplDriver = new $class();
            }else{
                halt('加载模版解析引擎失败！');
            }
            $content=$this->_loadTpl($tplFile);
            $content = $tplDriver->compile($content);
			$content = $this->replace($content);
            if ($cacheType == 'File') {
                F($compiledFile, $content);
            } else {
                S(md5($compiledFile), filemtime($tplFile));
                S(md5($tplFile), $content);
            }
        }
        return $cacheType == 'File' ? $compiledFile : 'data:,' . S(md5($tplFile));
    }

    // 模版输出替换
    protected function replace($content)
    {
        $replace = array(
            '__TMPL__' => __TMPL__, // 项目模板目录
            '__ROOT__' => ROOT, // 当前网站地址
            '__APP__' => __APP__, // 当前项目地址
            '__MODULE__' => isset($_GET['m']) ? __MODULE__ : __APP__,
            '__ACTION__' => __ACTION__, // 当前操作地址
            '__SELF__' => __SELF__, // 当前页面地址
            '__URL__' => __URL__, // 当前控制器地址
            '__PUBLIC__' => PUBLIC_URL, // 站点公共目录
        );
        // 允许用户自定义模板的字符串替换
        if (is_array(C('TPL_PARSE_STRING')))
            $replace = array_merge($replace, C('TPL_PARSE_STRING'));
        $content = str_replace(array_keys($replace), array_values($replace), $content);
        // 特定替换
        // 判断是否显示runtime info 信息
        return $content;
    }

	private function replaceRuninfo($content){
		if (strpos($content, '__RUNINFO__') !== false) {
			$time = Debug::useTime();
			$mem = number_format((memory_get_usage() - $GLOBALS['_startUseMems']) / 1024);
			$sql = count(Debug::$sqls);
			$file = count(get_included_files());
			$runtimeinfo = str_replace(array('{time}', '{mem}', '{sql}', '{file}'), array($time, $mem, $sql, $file), C('RUNTIME_INFO'));
			$content = str_replace('__RUNINFO__', $runtimeinfo, $content);
		}
		return $content;
	}

    /*
     * 读取模板内容
     */
    private function _loadTpl($tplFile)
    {
        $content = is_file($tplFile) ? file_get_contents($tplFile) : false;
        if (!is_string($content)) halt("无法读取模板文件 {$tplFile} ，请检查文件是否正确！");
        if(preg_match_all('/<include\s+file\s*=\s*[\'"]?(.+?)[\'"]?\s*[\/]?\s*>/i', $content, $match)) {
            foreach($match[0] as $key => $value) {
                $content = preg_replace('/' . preg_quote($value, '/') . '/', $this->_loadTpl($this->getTplFile($match[1][$key])), $content, 1);
            }
        }
        return $content;
    }
}