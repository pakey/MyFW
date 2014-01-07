<?php
/***************************************************************
 *  $Program: MCPHP FarmeWork $
 *  $Author: pakey $
 *  $Email: Pakey@ptcms.com $
 *  $Copyright: 2009 - 2012 Ptcms Studio $
 *  $Link: http://www.ptcms.com $
 *  $License: http://www.ptcms.com/service/license.html $
 *  $Date: 2013-04-25 20:54:02 +0800 (星期四, 25 四月 2013) $
 *  $File: TplMC.class.php $
 *  $Revision: 4 $
 *  $Desc: 视图模版引擎处理类
 **************************************************************/

defined('MC_PATH') || exit('Permission denied');

class TplMc
{
	// 左侧内容解析定界符
	private $_left = '\{';
	// 右侧内容解析定界符
	private $_right = '\}';
	// 模板编译内容
	private $_content = '';

	/**
	 * compile 编译
	 *
	 * @param mixed $content
	 * @access public
	 * @return mixed
	 */
	public function compile($content)
	{
		$this->_left = preg_quote(C('TPL_L_DELIM'), '/');
		$this->_right = preg_quote(C('TPL_R_DELIM'), '/');
		$this->_content = $content;
		$this->parseVariable();
		$this->parseFunction();
		$this->parseWidget();
		$this->parseLoop();
		$this->parseJudge();
		$this->parseTkd();
		$this->optimize();
		return $this->_content;
	}

	/*
	 * 解析 标签属性
	 */
	private function parseAttribute($string)
	{
		$attribute = array();
		$string = preg_replace('/\s+(\w+)\s*=\s*/', chr(0) . '\1=', $string);
		foreach (explode(chr(0), $string) as $row) {
			$row = trim($row);
			if (!empty($row)) {
				$row = explode('=', $row, 2);
				$attribute[strtolower($row['0'])] = trim($row['1'], "\r\n\t '\"");
			}
		}
		return $attribute;
	}

	/*
	 * 解析 变量调节器
	 */
	private function parseRegulator($regulator, $variable)
	{
		$regulator = explode('=', $regulator, 2);
		$function = trim($regulator['0']);
		if (isset($regulator['1'])) {
			$param = explode(',', trim($regulator['1']));
		} else {
			$param = array();
		}
		if ($function == 'default') $function = 'defaultvar';
		if (array_search('@', $param) === false) in_array($function, array('date', 'sprintf')) ? $param = array(implode(',', $param), '@') : array_unshift($param, '@');
		$var = array();
		foreach ($param as $row) {
			$var[] = preg_match('/^\s*(\$[\w\[\]\.\$]+)\s*$/', $row) ? $this->parseVar(trim($row)) : (trim($row) == '@' ? $variable : $row);
		}
		return $function . '(' . implode(',', $var) . ')';
	}

	/*
	 * 解析 模板变量
	 */
	private function parseVariable()
	{
		if (preg_match_all('/' . $this->_left . '(\$[\w\[\]\.\$]+)(?:\|(.+?))?\s*' . $this->_right . '/', $this->_content, $match)) {
			foreach ($match['0'] as $key => $value) {
				if ('$MC.' === substr($match['1'][$key], 0, 4)) {
					$variable = $this->parseMcVar($match['1'][$key]);
				} else {
					$variable = $this->parseVar($match['1'][$key]);
				}
				if (!empty($match['2'][$key])) {
					$regulator = explode('|', str_replace('\|', chr(0), $match['2'][$key]));
					foreach ($regulator as $row) {
						$variable = $this->parseRegulator(str_replace(chr(0), '|', $row), $variable);
					}
				}
				$this->_content = preg_replace('/' . preg_quote($value, '/') . '/', "<?php echo $variable;?>", $this->_content, 1);
			}
		}
	}

	/*
	 * 解析 Function 函数
	 */
	private function parseFunction()
	{
		if (preg_match_all('/' . $this->_left . '(~|:)(.+?)\s*' . $this->_right . '/', $this->_content, $match)) {
			foreach ($match['0'] as $key => $value) {
				$operate = $match['1'][$key] == ':' ? ' echo' : '';
				$expression = trim($match['2'][$key]);
				if (preg_match_all('/(\$[\w\[\]\.\$]+)/', $expression, $array)) {
					foreach ($array['0'] as $row) {
						$expression = preg_replace('/' . preg_quote($row, '/') . '/', $this->parseVar($row), $expression, 1);
					}
				}
				$this->_content = preg_replace('/' . preg_quote($value, '/') . '/', "<?php$operate $expression;?>", $this->_content, 1);
			}
		}
	}

	/*
	 *解析 Widget 区块
	 */
	private function parseWidget()
	{
		if (preg_match_all('/' . $this->_left . '(?:widget|block)((?:\s+\w+\s*=\s*[\'"]?[^\'"]*?[\'"]?)+)\s*' . $this->_right . '/i', $this->_content, $match)) {
			foreach ($match['0'] as $key => $value) {
				$attribute = $this->parseAttribute($match['1'][$key]);
				if (empty($attribute['type'])) continue;
				$var = isset($attribute['var']) ? $attribute['var'] : '';
				$cachetime = isset($attribute['cachetime']) ? $attribute['cachetime'] : '';
				$key = isset($attribute['key']) ? $attribute['key'] : '';
				$template = isset($attribute['template']) ? $attribute['template'] : '';
				$widget = "W('{$attribute['type']}','$var','$template','$cachetime','$key')";
				if (!empty($attribute['name'])){
					$this->_content = preg_replace('/' . preg_quote($value, '/') . '/', "<?php \${$attribute['name']}=$widget;?>", $this->_content, 1);
				}else{
					$this->_content = preg_replace('/' . preg_quote($value, '/') . '/', "<?php echo $widget;?>", $this->_content, 1);
				}
			}
		}
	}

	/*
	 * 解析 判断 条件
	 */
	private function parseJudge()
	{
		if (preg_match_all('/' . $this->_left . '(if|else\s*if)\s+(.+?)\s*' . $this->_right . '/i', $this->_content, $match)) {
			foreach ($match['0'] as $key => $value) {
				$judge = strtolower($match['1'][$key]) == 'if' ? 'if' : '}elseif';
				$condition = trim($match['2'][$key]);
				if (preg_match_all('/(\$[\w\[\]\.\$]+)/', $condition, $array)) {
					foreach ($array['0'] as $row) {
						$condition = preg_replace('/' . preg_quote($row, '/') . '/', $this->parseVar($row), $condition, 1);
					}
				}
				$this->_content = preg_replace('/' . preg_quote($value, '/') . '/', "<?php\r\n$judge($condition){\r\n?>", $this->_content, 1);
			}
		}
		$this->_content = preg_replace('/' . $this->_left . 'else\s*' . $this->_right . '/i', "<?php\r\n}else{\r\n?>", $this->_content);
		$this->_content = preg_replace('/' . $this->_left . '\/if\s*' . $this->_right . '/i', "<?php\r\n}\r\n?>", $this->_content);
	}

	/**
	 * 解析循环
	 */
	private function parseLoop()
	{
		if (preg_match_all('/' . $this->_left . 'loop(.*)' . $this->_right . '/iU', $this->_content, $match)) {
			foreach ($match['0'] as $key => $value) {
				$param = $this->parseAttribute($match['1'][$key]);
                if (isset($param['value'])) $param['name']=$param['value'];
				$name = isset($param['name']) ? '$' . $param['name'] : '$vo';
				$id = isset($param['id']) ? '$' . $param['id'] : '$list';
				$start = isset($param['start']) ? $param['start'] : 0;
				$length = isset($param['length']) ? $param['length'] : 'null';
				$order = isset($param['order']) ? '$' . $param['order'] : '$i';
				$key = isset($param['key']) ? '$' . $param['key'] : '$key';
				if (strpos($id, '.')) {
					$tmp = explode('.', $id);
					$id = array_shift($tmp);
					foreach ($tmp as $v) {
						if (substr($v, 0, 1) == '$') {
							$id .= "[{$v}]";
						} else {
							$id .= "['{$v}']";
						}
					}
				}
				if ($start == 0 and $length === 'null') {
					$var = $id;
				} else {
					$var = "array_slice({$id}, {$start}, {$length}, true);";
				}
				$replace = "<?php if (is_array({$id})): $order = 0; \$__LIST__ = $var; if (count(\$__LIST__) == 0) : echo ''; else: foreach (\$__LIST__ as $key => $name): ++$order; ?>";
				$this->_content = preg_replace('/' . preg_quote($value, '/') . '/', $replace, $this->_content, 1);

			}
		}
		$this->_content = preg_replace('/' . $this->_left . '\/(?:loop)\s*' . $this->_right . '/i', "<?php endforeach; endif; else: echo '' ;endif;?>", $this->_content);

	}

	/*
	 * 去除模板空格与换行，优化代码
	 */
	private function optimize()
	{
		//debug模式下不取消模版中的空格
		if (!APP_DEBUG) {
			$this->_content = preg_replace(array("/>\s+</", "/>(\s+\n|\r)/"), array('><', '>'), $this->_content);
			$this->_content = preg_replace('/\?>\s*<\?php/', '', $this->_content);
			$this->_content = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    '), ' ', $this->_content);
			$this->_content = strip_whitespace($this->_content);
		}
		$this->_content = "<?php defined('MC_PATH') || exit('Permission denied');?>{$this->_content}";
	}

	/*
	 * 解析 TKD
	 */
	private function parseTkd()
	{
		$generator = base64_decode('UG93ZXJlZCBCeQ==');
		$generator .= ' ' . 'P' . 'T';
		$generator .= 'c' . 'm' . 's';
		if (preg_match('/<html[^>]*>.*<head[^>]*>.*(<title[^>]*>(.+?)<\/title[^>]*>).*<\/head[^>]*>/isU', $this->_content, $match)) {
			$title = str_replace($match[2], $match[2] . ' - ' . $generator, $match[1]);
			$head = str_replace($match[1], $title, $match[0]);
			$this->_content = str_replace($match[0], $head, $this->_content);
		}
	}

	// 解析变量名
	protected function parseVar($var)
	{
		$var = preg_replace('/\.(\w+)/', "['\\1']", $var);
		if (preg_match_all('/\[((?:[^\[\]]*|(?R))*)\]/', $var, $match)) {
			foreach ($match['1'] as $key => $value) {
				if (preg_match('/^\$[\w\[\]\$\.\'"]+$/', $value)) $var = preg_replace('/\[' . preg_quote($value, '/') . '\]/', '[' . $this->parseVar($value) . ']', $var, 1);
			}
		}
		return $var;
	}

	// 解析特殊变量
	protected function parseMcVar($varStr)
	{
		$vars = explode('.', $varStr);
		$vars['1'] = strtoupper(trim($vars['1']));
		$parseStr = '';
		if (count($vars) >= 3) {
			$vars['2'] = trim($vars['2']);
			switch ($vars['1']) {
				case 'SERVER':
					$parseStr = '$_SERVER[\'' . strtoupper($vars['2']) . '\']';
					break;
				case 'GET':
					$parseStr = '$_GET[\'' . $vars['2'] . '\']';
					break;
				case 'POST':
					$parseStr = '$_POST[\'' . $vars['2'] . '\']';
					break;
				case 'COOKIE':
					if (isset($vars['3'])) {
						$parseStr = '$_COOKIE[\'' . $vars['2'] . '\'][\'' . $vars['3'] . '\']';
					} else {
						$parseStr = '$_COOKIE[\'' . $vars['2'] . '\']';
					}
					break;
				case 'SESSION':
					if (isset($vars['3'])) {
						$parseStr = '$_SESSION[\'' . $vars['2'] . '\'][\'' . $vars['3'] . '\']';
					} else {
						$parseStr = '$_SESSION[\'' . $vars['2'] . '\']';
					}
					break;
				case 'REQUEST':
					$parseStr = '$_REQUEST[\'' . $vars['2'] . '\']';
					break;
				case 'CONST':
					$parseStr = strtoupper($vars['2']);
					break;
				case 'CONFIG':
					if (isset($vars['3'])) {
						$vars['2'] .= '.' . $vars['3'];
					}
					$parseStr = 'C("' . $vars['2'] . '")';
					break;
				case 'TMPL':
					$parseStr = '\'' . ROOT . '/' . str_replace(MC_ROOT, '', TPL_PATH) . C('THEME_NAME') . '/' . ucfirst($vars['2']) . '\'';
					break;
				default:
					break;
			}
		} else if (count($vars) == 2) {
			switch ($vars['1']) {
				case 'NOW':
					$parseStr = "time()";
					break;
				case 'VERSION':
					$parseStr = 'MCPHP_VERSION';
					break;
				default:
					if (defined($vars['1']))
						$parseStr = $vars['1'];
					else
						$parseStr = "''";
			}
		}
		return $parseStr;
	}
}
