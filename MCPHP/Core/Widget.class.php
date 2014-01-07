<?php

/***************************************************************
 *   $Program: MCPHP FarmeWork (A Open PHP Application FarmeWrok) $
 *    $Author: pakey $
 *     $Email: Pakey@ptcms.com $
 * $Copyright: 2009 - 2012 Ptcms Studio $
 *      $Link: http://www.ptcms.com $
 *   $License: http://www.ptcms.com/service/license.html $
 *      $Date: 2013-04-25 20:54:02 +0800 (星期四, 25 四月 2013) $
 *      $File: Widget.class.php $
 *  $Revision: 4 $
 *      $Desc:
 **************************************************************/

defined('MC_PATH') || exit('Permission denied');

class Widget extends Controller
{
	/*
	 * 解析区块变量
	 * 只解析var中变量，$开头在模板中取值，@开头在cookies中取值
	 */
	static public function parseVariable($paramvar)
	{
		if (strpos($paramvar, '$') !== false) {
			$varnum = preg_match_all('/\$(\w([\w\.\[\]]*[\w\]])?)/', $paramvar, $param);
			for ($i = 0; $i < $varnum; $i++) {
				$var = self::_initView()->get_assign($param[1][$i]);
				$val = '/' . preg_quote($param[0][$i], '/') . '/';
				$paramvar = preg_replace($val, $var, $paramvar, 1);
			}
		}
		if (strpos($paramvar, '@') !== false) {
			$varnum = preg_match_all('/\@([a-zA-Z_0-9]+)/', $paramvar, $param);
			for ($i = 0; $i < $varnum; $i++) {
				$var = cookie($param[1][$i]);
				$val = '/' . preg_quote($param[0][$i], '/') . '/';
				$paramvar = preg_replace($val, $var, $paramvar, 1);
			}
		}
		return $paramvar;
	}

	/**
	 * getwidget 获取区块内容
	 *
	 * @param $widgettype     获取类型
	 * @param $paramvar       传入的参数
	 * @param $template       自定义模版
	 * @param $cachetime      数据缓存时间
	 * @param $checkkey            缓存key
	 * @return bool|string
	 */
	static public function getWidget($widgettype, $paramvar, $template, $cachetime, $checkkey)
	{
		static $_class = array();
		$widgettype = ucfirst(strtolower($widgettype));
		$paramvar = self::parseVariable($paramvar);
		$checkkey = self::parseVariable($checkkey);
		if ($cachetime == '') {
			$cachelife = C('CACHE_TIME');
		} else {
			$cachelife = intval($cachetime);
		}
		$widgetkey = md5($widgettype . '_' . $paramvar . '_' . $cachetime);
		// 读取区块缓存
		if (!empty($checkkey)){
			$check=S($checkkey);
		}else{
			$check=true;
		}
		if ($check){
			$widgetdata = S('widget:' . $widgetkey);
		}else{
			$widgetdata=false;
		}
		// 获取缓存失败重新获取区块数据
		if (false == $widgetdata or APP_DEBUG) {
			if (!isset($_class[$widgettype])) {
				$className = Ucfirst($widgettype) . 'Widget';
				$widgetfile = WIDGET_PATH . $className . '.class.php';
				if (is_file($widgetfile)) {
					require_cache($widgetfile);
					$_class[$widgettype] = new $className;
				} else {
					halt('区块类型错误');
				}
			}
			$widgetdata = $_class[$widgettype]->render($paramvar);
			if (!empty($template)){
				$widgetdata=$this->fetch($template,$widgetdata);
			}
			S('widget:' . $widgetkey, $widgetdata, $cachelife);
			if (!empty($checkkey)){
				S($checkkey,true,$cachelife);
			}
		}
		return $widgetdata;
	}

}