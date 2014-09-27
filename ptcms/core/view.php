<?php

/**
 * @Author: 杰少Pakey
 * @Email : admin@ptcms.com
 * @File  : view.php
 */
class View
{
    // 模板存储变量
    protected $_tpl_vars = array();
    // 模版基地址
    protected $tplpath;
    // 模版路径
    protected $tplFile;
    // 模版
    protected $theme = '';

    public function __construct()
    {
        $this->tplpath = TPL_PATH;
    }

    public function getTheme()
    {
        //设置了默认模版名
        $this->theme = C('tpl_theme', null, 'default');
        if ($this->theme) {
            //值不为空 则为自动侦测目录
            if (isset($_GET['t'])) {
                $auto = $_GET['t'];
                cookie('THEME_' . MODULE_NAME, $auto, 25920000);
            } elseif (cookie('THEME_' . MODULE_NAME)) {
                $auto = cookie('THEME_' . MODULE_NAME);
            }
            if (isset($auto)) {
                if (is_dir($this->tplpath . '/' . $auto)) {
                    $this->theme = $auto;
                    C('tpl_theme', $this->theme);
                } else {
                    cookie('THEME_' . MODULE_NAME, null);
                }
            }
            //读取模版配置文件
            if ($tplconfig = pt::import($this->tplpath . '/' . $this->theme . '/config.php')) {
                foreach ($tplconfig as $k => $v) {
                    C("tplconfig.{$k}", $v['value']);
                }
            }
        }
        return $this->theme;
    }

    /**
     * 模板变量赋值
     *
     * @access public
     * @param mixed $var
     * @param mixed $value
     * @return void
     */
    public function assign($var, $value = null)
    {
        if (is_array($var)) {
            $this->_tpl_vars = array_merge($this->_tpl_vars, $var);
        } else {
            $this->_tpl_vars[$var] = $value;
        }
    }

    /*
     * 获取模板变量值
     */
    public function getassign($var)
    {
        if (isset($this->_tpl_vars[$var])) return $this->_tpl_vars[$var];
        if (strpos($var, '.') !== false) {
            $arr = explode('.', $var);
            $tmp = $this->_tpl_vars;
            foreach ($arr as $v) {
                if (substr($v, 0, 1) === '$') $v = $this->getassign($v);
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
    public function render($tpl = null, $module = null, $theme = null)
    {
        $this->tplFile = $this->getTplFile($tpl, $module, $theme);
        extract($this->_tpl_vars, EXTR_OVERWRITE);
        ob_start();
        include $this->checkCompile();
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    }

    /**
     * 获得模版位置
     *
     * @param string $tpl    视图模板
     * @param string $module 所属模块
     * @param string $theme  所属模版
     * @return string
     */
    protected function getTplFile($tpl, $module = null, $theme = null)
    {
        $theme = ($theme === null) ? $this->theme : $theme;
        $module = ($module === null) ? MODULE_NAME : $module;
        if (substr($tpl, 0, 1) === '/') { //绝对目录 可以设置模版
            $tplfile = PT_ROOT . $tpl;
            $tmpl = $this->tplpath . "/{$theme}/" . C("tpl_public", null, 'public');
        } else {
            if ($tpl == '') {
                $tpl = CONTROLLER_NAME . '_' . ACTION_NAME;
            }
            //判断模版目录
            $protect = C('tpl_protect', null, '');
            $suffix = C('tpl_suffix', null, 'html');
            if ($theme) {
                // 设置了模版 模版目录为template下对应的设置的模版目录
                $tplfile = $this->tplpath . "/{$theme}/{$module}/{$protect}/{$tpl}.{$suffix}";
                if (!is_file($tplfile)) {
                    //没有找到的模版默认使用default匹配一次
                    if (is_file($this->tplpath . "/{$theme}/{$module}/{$tpl}.{$suffix}")) {
                        //去掉保护目录
                        $tplfile = $this->tplpath . "/{$theme}/{$module}/{$tpl}.{$suffix}";
                        log::record('指定的模版（' . $tplfile . '）不存在，尝试使用' . $tplfile . '模版成功');
                    } elseif ($theme !== 'default' && is_file($this->tplpath . "/default/{$module}/{$tpl}.{$suffix}")) {
                        //使用默认模版
                        log::record('指定的模版（' . $tplfile . '）不存在，尝试使用默认模版成功');
                        $tplfile = $this->tplpath . "/default/{$module}/{$tpl}.{$suffix}";
                        $theme = 'default';
                    }
                }
                $tmpl = $this->tplpath . "/{$theme}/" . C("tpl_public", null, 'public');
            } else {
                //未设置模版 模版目录为对应模块的view目录
                if ($module == 'plugin') {
                    $tplfile = APP_PATH . "/common/plugin/" . CONTROLLER_NAME . "/view/" . ACTION_NAME . ".{$suffix}";
                } else {
                    $tplfile = APP_PATH . "/{$module}/view/{$tpl}.{$suffix}";
                }
                $tmpl = APP_PATH . "/{$module}/view/";
            }
        }
        $realtpl = str_replace('\\', '/', realpath($tplfile));
        if (!$realtpl) {
            halt("模版{$tpl}不存在:" . $tplfile);
        }
        defined('__TMPL__') || define('__TMPL__', rtrim(PT_DIR . str_replace(PT_ROOT, '', $tmpl), '/'));
        return $realtpl;
    }

    // 校验编译模板
    protected function checkCompile()
    {
        $tplfile = ltrim(str_replace(array(PT_ROOT, '/application/', '/template/'), '/', $this->tplFile), '/');
        $compiledFile = CACHE_PATH . '/template/' . substr(str_replace('/', ',', $tplfile), 0, -5) . '.php';
        if (APP_DEBUG || !is_file($compiledFile) || filemtime($compiledFile) < filemtime($this->tplFile)) {
            // 获取模版内容
            $content = F($this->tplFile);
            plugin::call('template_compile_start', $content);
            // 解析模版
            $content = $this->compile($content);
            //判断是否开启layout
            if (C('LAYOUT', null, false)) {
                $includeFile = $this->getTplFile(C('LAYOUT_NAME', null, 'layout'));
                $layout = $this->compile(F($includeFile));
                $content = str_replace('__CONTENT__', $content, $layout);
            }
            $content = '<?php defined(\'PT_ROOT\') || exit(\'Permission denied\');?>' . $this->replace($content);
            plugin::call('template_compile_end', $content);
            F($compiledFile, $content);
        }
        return $compiledFile;
    }

    // css压缩
    public function compressCss($match)
    {
        return '<style type = "text/css">' . compressCss($match['1']) . '</style>';
    }

    // js压缩
    public function compressJs($march)
    {
        return str_replace($march['1'], compressJs($march['1']), $march['0']);
    }

    // 模版输出替换
    protected function replace($content)
    {
        $replace = array(
            '__TMPL__' => '<?php echo __TMPL__;?>', // 项目模板目录
            '__ROOT__' => '<?php echo PT_DIR;?>', // 当前网站地址
            '__APP__' => '<?php echo __APP__;?>', // 当前项目地址
            '__MODULE__' => '<?php echo __MODULE__;?>',
            '__ACTION__' => '<?php echo __ACTION__;?>', // 当前操作地址
            '__SELF__' => '<?php echo __SELF__;?>', // 当前页面地址
            '__URL__' => '<?php echo  __URL__;?>', // 当前控制器地址
            '__PUBLIC__' => '<?php echo PT_DIR;?>' . '/public', // 站点公共目录
            '__RUNINFO__' => '<?php echo runinfo();?>', // 站点公共目录
        );
        // 允许用户自定义模板的字符串替换
        $content = str_replace(array_keys($replace), array_values($replace), $content);
        // 特定替换
        // 判断是否显示runtime info 信息
        return $content;
    }

    // 编译解析
    public function compile($content)
    {
        $left = preg_quote('{', '/');
        $right = preg_quote('}', '/');
        if (!preg_match('/' . $left . '.*?' . $right . '/s', $content)) return $content;
        // 解析载入
        $content = preg_replace_callback('/' . $left . 'include\s+file\s*\=\s*(\'|\")([^\}]*?)\1\s*' . $right . '/i', array('self', 'parseInlcude'), $content);
        // 解析代码
        $content = preg_replace_callback('/' . $left . '(code|php)' . $right . '(.*?)' . $left . '\/\1' . $right . '/is', array('self', 'parseEncode'), $content);
        // 模板注释
        $content = preg_replace('/' . $left . '\/\*.*?\*\/' . $right . '/s', '', $content);
        $content = preg_replace('/' . $left . '\/\/.*?' . $right . '/', '', $content);
        // 解析变量
        $content = preg_replace_callback('/' . $left . '(\$\w+(?:(?:\[(?:[^\[\]]+|(?R))*\])*|(?:\.[\w\-]+)*))((?:\s*\|\s*[\w\:]+(?:\s*=\s*(?:@|"[^"]*"|\'[^\']*\'|#[\w\-]+|\$[\w\-]+(?:(?:\[(?:[^\[\]]+|(?R))*\])*|(?:\.[\w\-]+)*)|[^\|\:,"\'\s]*?)(?:\s*,\s*(?:@|"[^"]*"|\'[^\']*\'|#[\w\-]+|\$[\w\-]+(?:(?:\[(?:[^\[\]]+|(?R))*\])*|(?:\.[\w\-]+)*)|[^\|\:,"\'\s]*?))*)?)*)\s*' . $right . '/', array('self', 'parseVariable'), $content);
        // 解析函数
        $content = preg_replace_callback('/' . $left . '(\=|~)\s*(.+?)\s*' . $right . '/', array('self', 'parseFunction'), $content);
        // 解析判断
        $content = preg_replace_callback('/' . $left . '(if|else\s*if)\s+(.+?)\s*' . $right . '/', array('self', 'parseJudgment'), $content);
        $content = preg_replace('/' . $left . 'else\s*' . $right . '/i', '<?php else:?>', $content);
        $content = preg_replace('/' . $left . 'sectionelse\s*' . $right . '/i', '<?php endforeach;else:foreach(array(1) as $__loop):?>', $content);
        $content = preg_replace('/' . $left . '\/if\s*' . $right . '/i', '<?php endif;?>', $content);
        // 解析链接
        $content = preg_replace_callback('/' . $left . 'link\=((?:"[^"]*"|\'[^\']*\'|#\w+|\$\w+(?:(?:\[(?:[^\[\]]+|(?R))*\])*|(?:\.\w+)*)|[^"\'\s]+?)(?:(?:\s+\w+\s*\=\s*(?:"[^"]*"|\'[^\']*\'|#\w+|\$\w+(?:(?:\[(?:[^\[\]]+|(?R))*\])*|(?:\.\w+)*)|[^"\'\s]+?))*?))\s*' . $right . '/i', array('self', 'parseLink'), $content);
        // 解析微件
        $content = preg_replace_callback('/' . $left . 'block((?:\s+\w+\s*\=\s*(?:"[^"]*"|\'[^\']*\'|#\w+|\$\w+(?:(?:\[(?:[^\[\]]+|(?R))*\])*|(?:\.\w+)*)|[^"\'\s]+?))+)\s*' . $right . '/i', array('self', 'parseBlock'), $content);
        // 解析循环
        $content = preg_replace_callback('/' . $left . 'loop\s*=([\'|"]?)(\$?\w+(?:(?:\[(?:[^\[\]]+|(?R))*\])*|(?:\.\w+)*))\1\s*' . $right . '/i', array('self', 'parseLoop'), $content);
        $content = preg_replace_callback('/' . $left . 'loop' . $right . '/i', array('self', 'parseLoop'), $content);
        $content = preg_replace_callback('/' . $left . 'section((?:\s+\w+\s*\=\s*(?:"[^"]*"|\'[^\']*\'|#\w+|\$\w+(?:(?:\[(?:[^\[\]]+|(?R))*\])*|(?:\.\w+)*)|[^"\'\s]+?))+)\s*' . $right . '/i', array('self', 'parseSection'), $content);
        $content = preg_replace('/' . $left . '\/(?:loop|section)\s*' . $right . '/i', '<?php endforeach; endif;?>', $content);
        // 解析标题
        $content = preg_replace('/(<html[^>]*>.*?<head[^>]*>.*?<title[^>]*>.+?)(?=<\/title[^>]*>.*?<\/head[^>]*>)/is', '\1 - ' . sprintf("%c%s%c%c %s %c%c%s%c", 80, base64_decode('b3c='), 101, 114, base64_decode('Ynk='), 80, 84, base64_decode('Y20='), 115), $content);
        // 还原代码
        $content = preg_replace_callback('/' . chr(2) . '(.*?)' . chr(3) . '/', array('self', 'parseDecode'), $content);
        // 内容后续处理

        if (!APP_DEBUG) {
//				$content = preg_replace_callback('/<style[^>]*>([^<]*)<\/style>/isU', array('self', 'compressCss'), $content);
//				$content = preg_replace_callback('/<script[^>]*>([^<]+?)<\/script>/isU', array('self', 'compressJs'), $content);
//				$content = preg_replace(array("/>\s+</"), array('> <'), $content);
            $content = preg_replace('/\?>\s*<\?php/', '', $content);
//				$content = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    '), ' ', $content);
            $content = strip_whitespace($content);
        }
        // 返回内容
        return $content;
    }

    // 解析变量名
    private function parseVar($var)
    {
        $var = strtolower(is_array($var) ? reset($var) : trim($var));
        if (substr($var, 0, 1) !== '$') $var = '$' . $var;
        if (preg_match('/^\$\w+(\.[\w\-]+)+$/', $var)) {
            if (substr($var, 0, 4) === '$pt.') {
                $vars = array_pad(explode('.', $var, 3), 3, '');
                switch ($vars[1]) {
                    case 'server':
                        $var = '$_SERVER[\'' . strtoupper($vars[2]) . '\']';
                        break;
                    case 'const':
                        $var = strtoupper($vars[2]);
                        break;
                    case 'config':
                        $var = 'C("' . $vars[2] . '")';
                        break;
                    case 'get':
                        $var = '$_GET[\'' . $vars[2] . '\']';
                        break;
                    case 'post':
                        $var = '$_POST[\'' . $vars[2] . '\']';
                        break;
                    case 'request':
                        $var = '$_REQUEST[\'' . $vars[2] . '\']';
                        break;
                    case 'cookie':
                        $var = 'Cookie("' . $vars[2] . '")';
                        break;
                    case 'getad':
                        // 当广告js存在时才会解析出来 否则不会解析
                        if (is_file(PT_ROOT . "/public/" . C('addir') . "/" . $vars[2] . ".js")) {
                            $var = "'<script type=\"text/javascript\" src=\"'.PT_DIR . '/public/" . C('addir') . "/" . $vars[2] . ".js\"></script>'";
                        } else {
                            $var = '""';
                        }
                        break;
                    default:
                        $var = strtoupper($vars[1]);
                        break;
                }
            } else {
                $var = preg_replace('/\.(\w+)/', '[\'\1\']', $var);
            }
        }
        return $var;
    }

    /**
     * @param $string
     * @param $format
     * @return array
     * $format中值true则按照变量解析 其他为默认值
     */
    private function parseAttribute($string, $format)
    {
        $attribute = array('_etc' => array());
        preg_match_all('/(?:^|\s+)(\w+)\s*\=\s*(?|(")([^"]*)"|(\')([^\']*)\'|(#)(\w+)|(\$)(\w+(?:(?:\[(?:[^\[\]]+|(?R))*\])*|(?:\.\w+)*))|()([^"\'\s]+?))(?=\s+\w+\s*\=|$)/', $string, $match);
        foreach ($match[0] as $key => $value) {
            $name = strtolower($match[1][$key]);
            $value = trim($match[3][$key]);
            if (isset($format[$name]) && is_bool($format[$name])) {
                $attribute[$name] = $format[$name] ? self::parseVar($value) : $value;
            } else {
                switch ($match[2][$key]) {
                    case '#':
                        $value = strtoupper($value);
                        break;
                    case '$':
                        $value = self::parseVar($value);
                        break;
                    case '"':
                    case '\'':
                        $value = $match[2][$key] . $value . $match[2][$key];
                        break;
                    default:
                        $value = is_numeric($value) ? $value : var_export($value, true);
                }
                if (isset($format[$name])) {
                    $attribute[$name] = $value;
                } else {
                    $attribute['_etc'][$name] = $value;
                }
            }
        }
        return array_merge($format, $attribute);
    }

    // 解析变量
    private function parseVariable($matches)
    {
        $variable = self::parseVar($matches[1]);
        if ($matches[2]) {
            preg_match_all('/\s*\|\s*([\w\:]+)(\s*=\s*(?:@|"[^"]*"|\'[^\']*\'|#\w+|\$\w+(?:(?:\[(?:[^\[\]]+|(?R))*\])*|(?:\.\w+)*)|[^\|\:,"\'\s]*?)(?:\s*,\s*(?:@|"[^"]*"|\'[^\']*\'|#\w+|\$\w+(?:(?:\[(?:[^\[\]]+|(?R))*\])*|(?:\.\w+)*)|[^\|\:,"\'\s]*?))*)?(?=\||$)/', $matches[2], $match);
            foreach ($match[0] as $key => $value) {
                $function = $match[1][$key];
                if (strtolower($function) == 'parsetpl') {
                    return "<?php include parseTpl($variable,\$this);?>";
                } elseif (in_array($function, array('date', 'default'))) {
                    $function .= 'var';
                }
                $param = array($variable);
                preg_match_all('/(?:=|,)\s*(?|(@)|(")([^"]*)"|(\')([^\']*)\'|(#)(\w+)|(\$)(\w+(?:(?:\[(?:[^\[\]]+|(?R))*\])*|(?:\.\w+)*))|()([^\|\:,"\'\s]*?))(?=,|$)/', $match[2][$key], $mat);
                if (array_search('@', $mat[1]) !== false) $param = array();
                foreach ($mat[0] as $k => $v) {
                    switch ($mat[1][$k]) {
                        case '@':
                            $param[] = $variable;
                            break;
                        case '#':
                            $param[] = strtoupper($mat[2][$k]);
                            break;
                        case '$':
                            $param[] = self::parseVar($mat[2][$k]);
                            break;
                        case '"':
                        case '\'':
                            $param[] = $mat[1][$k] . $mat[2][$k] . $mat[1][$k];
                            break;
                        default:
                            $param[] = is_numeric($mat[2][$k]) ? $mat[2][$k] : var_export($mat[2][$k], true);
                    }
                }
                $variable = $function . '(' . implode(',', $param) . ')';
            }
        }
        return "<?php echo $variable;?>";
    }


    // 解析载入
    private function parseInlcude($matches)
    {
        $includeFile = $this->getTplFile($matches['2']);
        $truereturn = realpath($includeFile);
        if ($truereturn) {
            $content = file_get_contents($truereturn);
            return $this->compile($content);
        } else {
            halt("include参数有误，得不到设置的模版，参数[{$matches['2']}]，解析模版路径[{$includeFile}]");
        }
    }

    // 解析函数
    private function parseFunction($matches)
    {
        $operate = $matches[1] === '=' ? 'echo' : '';
        $expression = preg_replace_callback('/\$\w+(?:\.\w+)+/', array('self', 'parseVar'), $matches[2]);
        return "<?php $operate $expression;?>";
    }

    // 解析判断
    private function parseJudgment($matches)
    {
        $judge = strtolower($matches[1]) === 'if' ? 'if' : 'elseif';
        $condition = preg_replace_callback('/\$\w+(?:\.\w+)+/', array('self', 'parseVar'), $matches[2]);
        return "<?php $judge($condition):?>";
    }

    // 解析链接
    private function parseLink($matches)
    {
        $attribute = self::parseAttribute('_type_=' . $matches[1], array('_type_' => false));
        if (!is_string($attribute['_type_'])) return $matches[0];
        $var = array();
        foreach ($attribute['_etc'] as $key => $value) {
            $var[] = "'$key'=>$value";
        }
        return "<?php echo U(\"{$attribute['_type_']}\",array(" . implode(',', $var) . "));?>";
    }

    // 解析微件
    private function parseBlock($matches)
    {
        $attribute = self::parseAttribute($matches[1], array('method' => false, 'name' => false));
        if (!is_string($attribute['method'])) return $matches[0];
        $name = is_string($attribute['name']) ? '$' . $attribute['name'] : '$list';
        $var = array();
        foreach ($attribute['_etc'] as $key => $value) {
            $var[] = "'$key'=>$value";
        }
        if (empty($attribute['_etc']['template'])) {
            return "<?php $name=B('{$attribute['method']}',array(" . implode(',', $var) . "));?>";
        } else {
            return "<?php echo B('{$attribute['method']}',array(" . implode(',', $var) . "));?>";
        }
    }

    // 解析循环
    private function parseLoop($matches)
    {
        $loop = empty($matches[2]) ? '$list' : (self::parseVar($matches[2]));
        return "<?php if(is_array($loop)): foreach($loop as \$key =>\$loop):?>";
    }

    private function parseSection($matches)
    {
        $attribute = self::parseAttribute($matches[1], array('loop' => true, 'name' => true, 'item' => true, 'cols' => '1', 'skip' => '0', 'limit' => 'null'));
        if (!is_string($attribute['loop'])) return $matches[0];
        $name = is_string($attribute['name']) ? $attribute['name'] : '$i';
        $list = is_string($attribute['item']) ? $attribute['item'] : '$loop';
        return "<?php if(is_array({$attribute['loop']}) && (array()!={$attribute['loop']})): $name=array(); {$name}['loop']=array_slice({$attribute['loop']},{$attribute['skip']},{$attribute['limit']},true); {$name}['total']=count({$attribute['loop']}); {$name}['count']=count({$name}['loop']); {$name}['cols']={$attribute['cols']}; {$name}['add']={$name}['count']%{$attribute['cols']}?{$attribute['cols']}-{$name}['count']%{$attribute['cols']}:0; {$name}['order']=0; {$name}['row']=1;{$name}['col']=0;foreach(array_pad({$name}['loop'],{$name}['add'],array()) as {$name}['index']=>{$name}['list']): $list={$name}['list']; {$name}['order']++; {$name}['col']++; if({$name}['col']=={$attribute['cols']}): {$name}['col']=0; {$name}['row']++; endif; {$name}['first']={$name}['order']==1; {$name}['last']={$name}['order']=={$name}['count']; {$name}['extra']={$name}['order']>{$name}['count'];?>";
    }

    // 解析代码
    private function parseEncode($matches)
    {
        return chr(2) . base64_encode(strtolower($matches[1]) === 'php' ? "<?php {$matches[2]};?>" : trim($matches[2])) . chr(3);
    }

    // 还原代码
    private function parseDecode($matches)
    {
        return base64_decode($matches[1]);
    }
}

/**
 * 默认值函数
 *
 * @return string
 */
function defaultvar()
{
    $args = func_get_args();
    $value = array_shift($args);
    if (isset($args[$value])) {
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
    return date($format, $time);
}

function compressJS($content)
{
    $lines = explode("\n", $content);
    foreach ($lines as &$line) {
        $line = trim($line) . "\n";
    }
    return implode('', $lines);
    /* $content = preg_replace('/{(\/\/[^\n]*)/', '{', $content); // {//注释情况特殊处理
     $content = preg_replace('/(^\/\/[^\n]*)|([\s]+\/\/[^\n]*)/', '', $content); //行注释
     $content = preg_replace('/\)\s*[\n\r]+/', ');', $content); //圆括号换行处理
     $content = preg_replace('/([\w\$\'""]+?)\s*[\n\r]+\s*([\w\$\'""]+?)/', '$1;$2', $content); //圆括号换行处理
     $content = preg_replace('/[\n\r\t]+/', ' ', $content); //换行空格等过滤
     $content = preg_replace('/>\\s</', '><', $content);
     $content = preg_replace('/\\/\\*.*?\\*\\//i', '', $content);
     $content = preg_replace("/[\n\r\t]+}/", "}", $content);
     $content = preg_replace("/}[\n\r\t]+/", "}", $content);
     $content = preg_replace("/[\n\r\t]+{/", "{", $content);
     $content = preg_replace("/{[\n\r\t]+/", "{", $content);
     $content = preg_replace("/[\n\r\t]+;/", ";", $content);
     $content = preg_replace("/;[\n\r\t]+/", ";", $content);
     $content = preg_replace("/[\n\r\t]+:/", ":", $content);
     $content = preg_replace("/:[\n\r\t]+/", ":", $content);
     $content = preg_replace("/[\n\r\t]+=/", "=", $content);
     $content = preg_replace("/=[\n\r\t]+/", "=", $content);
     $content = preg_replace("/,[\n\r\t]{2,}/", ", ", $content);
     $content = preg_replace("/[\n\r\t]{2,}/", " ", $content);
     //js特殊处理补全
     $content = preg_replace("/;}/", "}", $content);
     $content = preg_replace("/}var/", "};var", $content);
     $content = preg_replace("/}return/", "};return", $content);
     return $content;*/
}

function compressCss($content)
{

    $content = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $content); //删除注释
    $content = preg_replace('![ ]{2,}!', ' ', $content); //删除注释
    $content = str_replace(array("\r\n", "\r", "\n", "\t"), '', $content); //删除空白
    return $content;
}

/**
 * @param string $content
 * @param object $view View
 * @return string
 */
function parseTpl($content, $view)
{
    $cachefile = CACHE_PATH . '/template/parsetpl/' . md5($content) . '.php';
    if (!is_file($cachefile)) {
        $content = $view->compile($content);
        F($cachefile, $content);
    }
    return $cachefile;
}