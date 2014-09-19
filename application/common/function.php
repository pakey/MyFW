<?php
/**
 * @Author: 杰少Pakey
 * @Email : admin@ptcms.com
 * @File  : common.php
 */

/**
 * 去除各种空白
 * @param $str
 * @return string
 */
function clearformat($str)
{
	$str=strip_tags($str);
	$str=str_replace(array("\r","\n","\t",'"',"'"),' ',$str);
	while(strpos($str,'  ')!==false){
		$str=str_replace('  ',' ',$str);
	}
	return trim($str);
}

/**
 * 人民币格式化
 * @param $num
 * @param $deadline
 * @return string
 */
function rmbFormat($num,$deadline=''){
	if($num==0 ) return '免费';
	if ($deadline==''){
		return '￥'.number_format($num,0);
	}else{
		return '￥'.number_format($num,0).'/'.$deadline;
	}

}

/**
 * 取子id
 * @param $id
 * @return float
 */
function subid($id){
	return floor($id/1000);
}


/**
 * 字符串截取，支持中文和其他编码
 *
 * @param string $string 需要转换的字符串
 * @param string $length 截取长度
 * @param string $suffix 截断显示字符
 * @param int $start     开始位置
 * @return string
 */
function truncate($string, $length, $suffix = '', $start = 0)
{
	if (empty($string) or empty($length) or strlen($string) < $length) return $string;
	if (function_exists('mb_substr')) {
		$slice = mb_substr($string, $start, $length, 'utf-8');
	} elseif (function_exists('iconv_substr')) {
		$slice = iconv_substr($string, $start, $length, 'utf-8');
	} else {
		preg_match_all('/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/', $string, $match);
		$slice = implode('', array_slice(reset($match), $start, $length));
	}
	return $slice . $suffix;
}

//输出安全的文本
function safetext($text, $tags = null) {
	$text	=	trim($text);
	//完全过滤注释
	$text	=	preg_replace('/<!--?.*-->/','',$text);
	//完全过滤动态代码
	$text	=	preg_replace('/<\?|\?'.'>/','',$text);
	//完全过滤js
	$text	=	preg_replace('/<script?.*\/script>/','',$text);

	$text	=	str_replace('[','&#091;',$text);
	$text	=	str_replace(']','&#093;',$text);
	$text	=	str_replace('|','&#124;',$text);
	//br
	$text	=	preg_replace('/<br(\s\/)?'.'>/i','[br]',$text);
	$text	=	preg_replace('/<p(\s\/)?'.'>/i','[br]',$text);
	$text	=	preg_replace('/(\[br\]\s*){10,}/i','[br]',$text);
	//过滤危险的属性，如：过滤on事件lang js
	while(preg_match('/(<[^><]+)( lang|on|action|background|codebase|dynsrc|lowsrc)[^><]+/i',$text,$mat)){
		$text=str_replace($mat[0],$mat[1],$text);
	}
	while(preg_match('/(<[^><]+)(window\.|javascript:|js:|about:|file:|document\.|vbs:|cookie)([^><]*)/i',$text,$mat)){
		$text=str_replace($mat[0],$mat[1].$mat[3],$text);
	}
	if(empty($tags)) {
		$tags = 'br';
	}
	//允许的HTML标签
	$text	=	preg_replace('/<('.$tags.')( [^><\[\]]*)>/i','[\1\2]',$text);
	$text = preg_replace('/<\/('.$tags.')>/Ui','[/\1]',$text);
	//过滤多余html
	$text	=	preg_replace('/<\/?(html|head|meta|link|base|basefont|body|bgsound|title|style|script|form|iframe|frame|frameset|applet|id|ilayer|layer|name|script|style|xml|table|td|th|tr|i|u|strong|img|p|br|div|strong|em|ul|ol|li|dl|dd|dt|a)[^><]*>/i','',$text);
	//过滤合法的html标签
	while(preg_match('/<([a-z]+)[^><\[\]]*>[^><]*<\/\1>/i',$text,$mat)){
		$text=str_replace($mat[0],str_replace('>',']',str_replace('<','[',$mat[0])),$text);
	}
	//转换引号
	while(preg_match('/(\[[^\[\]]*=\s*)(\"|\')([^\2=\[\]]+)\2([^\[\]]*\])/i',$text,$mat)){
		$text=str_replace($mat[0],$mat[1].'|'.$mat[3].'|'.$mat[4],$text);
	}
	//过滤错误的单个引号
	while(preg_match('/\[[^\[\]]*(\"|\')[^\[\]]*\]/i',$text,$mat)){
		$text=str_replace($mat[0],str_replace($mat[1],'',$mat[0]),$text);
	}
	//转换其它所有不合法的 < >
	$text	=	str_replace('<','&lt;',$text);
	$text	=	str_replace('>','&gt;',$text);
	$text	=	str_replace('"','&quot;',$text);
	//反转换
	$text	=	str_replace('[','<',$text);
	$text	=	str_replace(']','>',$text);
	$text	=	str_replace('|','"',$text);
	//过滤多余空格
	$text	=	str_replace('  ',' ',$text);
	return $text;
}

function formattext($content){
	// 去除<br />
	$content = str_ireplace(array('<br/>', '<br />', '<br>','&#10;'), "\n", $content);
	$content = str_replace("\r","\n",$content);
	do{
		$content=str_replace("\n\n","\n",$content);
	}while(strpos($content,"\n\n")!==false);
	// 去除空格
	$content = trim(str_replace(array('　', '&nbsp;'), ' ', $content));
	// 去除其他html
	$content=strip_tags(safetext($content));
	// 加换行及空格
	$content = str_replace("\n", "\r\n", trim($content));
	return $content;
}