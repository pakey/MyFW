<?php

/***************************************************************
 *   $Program: MCPHP FarmeWork (A Open PHP Application FarmeWrok) $
 *    $Author: pakey $
 *     $Email: Pakey@ptcms.com $
 * $Copyright: 2009 - 2012 Ptcms Studio $
 *      $Link: http://www.ptcms.com $
 *   $License: http://www.ptcms.com/service/license.html $
 *      $Date: 2013-05-05 20:04:38 +0800 (星期日, 05 五月 2013) $
 *      $File: MCPHP.php $
 *  $Revision: 150 $
 *      $Desc:
 **************************************************************/

// 设置基本参数
date_default_timezone_set("PRC");//设置时区（中国）
// 记录开始运行时间
$GLOBALS['_startTime'] = microtime(TRUE);
// 缓存写入次数
$GLOBALS['_cacheWrite'] = 0;
// 缓存读取次数
$GLOBALS['_cacheRead'] = 0;
// 记录内存初始使用
$GLOBALS['_startUseMems'] = memory_get_usage();
// 框架版本号
define('MCPHP_VERSION','MCPHP 2.1.0 Beta');
// debug信息
defined('APP_DEBUG') || define('APP_DEBUG',false);//是否开启当前项目debug模式 默认 不开启
if (APP_DEBUG){
    // 局部修正debug
    $GLOBALS['_debug']=1;
    // 开启错误输出
    ini_set('display_errors', 'on');
    // 设置输出级别
    error_reporting(E_ALL);//错误级别
}else{
    // 局部修正debug
    $GLOBALS['_debug']=0;
    // 开启错误输出
    ini_set('display_errors', 'off');
    // 设置输出级别
    error_reporting(0);//错误级别
}
//项目名
if (PHP_SAPI=='cli'){
	$_root='/';
}else{
	$_root=str_replace('\\','/',dirname(rtrim(str_replace('\\','/',$_SERVER['SCRIPT_NAME']),'/')));
}
defined('ROOT') || define('ROOT',rtrim($_root,'/'));//网站内部地址 子目录地址
if ($pos=strpos($_SERVER['HTTP_HOST'],':')){
    $host=substr($_SERVER['HTTP_HOST'],0,$pos);
}else{
    $host=$_SERVER['HTTP_HOST'];
}
defined('MC_URL') || define('MC_URL','http://'.$host.(($_SERVER['SERVER_PORT']==80)?'':':'.$_SERVER['SERVER_PORT']).ROOT);// 网站访问域名 不包括入口文件及参数

// 基本目录参数
defined('MC_ROOT') || define('MC_ROOT',str_replace('\\','/',dirname($_SERVER['SCRIPT_FILENAME']).'/')); //项目根目录
defined('MC_PATH') || define('MC_PATH',dirname(str_replace('\\','/',__FILE__)).'/'); //MCPHP根目录

// 项目目录
defined('APP_PATH') || define('APP_PATH',MC_ROOT.'Application/');
defined('CONTROLLER_PATH') || define('CONTROLLER_PATH',APP_PATH.'Controller/');
defined('COMMON_PATH') || define('COMMON_PATH',APP_PATH.'Common/');
defined('MODEL_PATH') || define('MODEL_PATH',APP_PATH.'Model/');
defined('CONFIG_PATH') || define('CONFIG_PATH',APP_PATH.'Config/');
defined('LIBRARY_PATH') || define('LIBRARY_PATH',APP_PATH.'Library/');
defined('WIDGET_PATH') || define('WIDGET_PATH',APP_PATH.'Widget/');
defined('DATA_PATH') || define('DATA_PATH',APP_PATH.'Data/');
//缓存目录
defined('CACHE_PATH') || define('CACHE_PATH',MC_ROOT.'Cache/');
//公共目录
defined('PUBLIC_PATH') || define('PUBLIC_PATH',MC_ROOT.'Public/');
defined('PUBLIC_URL') || define('PUBLIC_URL',ROOT.'/'.'Public');
// 模版目录
defined('TPL_PATH') || define('TPL_PATH',MC_ROOT.'Tpl/');

defined('COMPILE_FILE') || define('COMPILE_FILE',CACHE_PATH.'data/compile.php');
defined('COMPILE_USE') || define('COMPILE_USE',true);
// 运行

//$GLOBALS['_debug']=1;
if (!APP_DEBUG && is_file(COMPILE_FILE)){
    include COMPILE_FILE;
}else{
    include MC_PATH.'Common/common.php';
    include MC_PATH.'Core/MCPHP.class.php';
}

//框架初始化
MCPHP::init();
//运行应用
App::run();
//debug信息
if($GLOBALS['_debug']==1){
    Debug::message();
}