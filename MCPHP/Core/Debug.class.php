<?php

/***************************************************************
 *   $Program: MCPHP FarmeWork (A Open PHP Application FarmeWrok) $
 *    $Author: pakey $
 *     $Email: Pakey@ptcms.com $
 * $Copyright: 2009 - 2012 Ptcms Studio $
 *      $Link: http://www.ptcms.com $
 *   $License: http://www.ptcms.com/service/license.html $
 *      $Date: 2013-04-25 20:54:02 +0800 (星期四, 25 四月 2013) $
 *      $File: Debug.class.php $
 *  $Revision: 4 $
 *      $Desc:
 **************************************************************/

defined('MC_PATH') || exit('Permission denied');

class Debug{
    //SQL语句
    static $sqls=array();
    //错误信息
    static $errorInfo=array();
    //错误类型
    static $errorTypes = array(
        E_WARNING=>'运行时警告',
        E_NOTICE=>'运行时提醒',
        E_STRICT=>'编码标准化警告',
        E_USER_ERROR=>'自定义错误',
        E_USER_WARNING=>'自定义警告',
        E_USER_NOTICE=>'自定义提醒',
        'Unkown '=>'未知错误'
    );

    /**
     * addMsg
     * 调试时记录消息
     * 类型[msg:普通消息,sql:记录sql语句,default:其他信息]
     * @param mixed $msg 消息
     * @param string $type 消息类型
     * @static
     * @access public
     * @return mixed
     */
    static function addMsg($msg,$type='msg'){
        //如果开启了DEBUG
        if(APP_DEBUG){
            switch (strtolower($type)){
                case 'sql'://运行的SQL语句
                    self::$sqls[]='<p class="sql">SQl语句:'.$msg.'</p>';
                    break;
                case 'msg'://其它消息
                default:
                    self::$errorInfo[]=$msg;
            }
        }else{
            self::Log();
        }
    }


    /**
     * Log
     * 写入日志
     * @static
     * @access public
     * @return mixed
     */
    static public function Log(){
        ini_set('display_errors', 'Off'); 		//屏蔽错误输出
        ini_set('log_errors', 'On');            //开启错误日志，将错误报告写入到日志
        ini_set('error_log', CACHE_PATH.'log/log'.date('Ymd').'.log'); //指定错误日志文件
    }

    /**
     * useTime
     * 获取运行时间节点信息
     * @static
     * @access public
     * @return float 返回保留4位小数的运行时间
     */
    static function useTime(){
        return round(microtime(true)-$GLOBALS['_startTime'],4);
    }


    /**
     * message
     * 显示调试信息
     * @static
     * @access public
     * @return mixed
     */
    static function message(){
        $mcphpVersion=MCPHP_VERSION;
        $runTime=self::useTime();//耗时
        $reqTime=date('Y-m-d H:i:s',$_SERVER['REQUEST_TIME']);//请求时间
        $browser=$_SERVER["HTTP_USER_AGENT"];//浏览器信息
        $protocol=$_SERVER["SERVER_PROTOCOL"];//请求协议
        $currentFile=$_SERVER['SCRIPT_FILENAME'];//当前页面
        $errorInfo='';
        if(empty(self::$errorInfo)){
            $errorInfo='系统运行正常...';
        }else{
            foreach(self::$errorInfo as $key=>$value){
                $errorInfo.='<p>['.$key.']:=>'.$value."</p>\n\t\t\t";
                if(!$GLOBALS['_debug'])//如果没有开启Debug,则记录日志
                    self::Log($value);
            }
        }
        $sqlNum=count(self::$sqls);
        if(empty(self::$sqls)){
            $sqls='暂无sql语句运行...';
        }else{
            $sqls='';
            foreach(self::$sqls as $key=>$value){
                $sqls.='<p>['.$key.']:=>'.$value."</p>\n\t\t\t";
                if(!$GLOBALS['_debug'])//如果没有开启Debug,则记录日志
                    self::Log($value);
            }
        }

        // 文件加载
        $includedFiles=get_included_files();
        $includedFile='';
        foreach ($includedFiles as $key=>$value){
            if (strlen($value)>200) {
				unset($includedFiles[$key]);
				continue;
			}
            $includedFile.='<p>['.$key.']=>'.$value."</p>\n\t\t\t";
        }
        $includedFileNum=count($includedFiles);
        $useMems=number_format((memory_get_usage() - $GLOBALS['_startUseMems'])/1024).' kb';
        //输出信息
        include MC_PATH.'Tpl/debug.tpl';
    }
}
