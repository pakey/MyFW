<?php

// todo f函数改为storage
class log
{
	protected static $logstr=array();

	/**
	 * 直接写日志
	 * @param $str
	 * @param string $type
	 */
	public static function write($str,$type='pt')
	{
		$str="[".date('Y-m-d H:i:s')."] ".$str.PHP_EOL;
		F(CACHE_PATH.'/log/'.$type.'.log',$str,FILE_APPEND);
	}

	/**
	 * 记录日志
	 * @param $str
	 * @param string $type
	 */
	public static function record($str,$type='pt')
	{
		self::$logstr[$type][]="[".date('Y-m-d H:i:s')."] ".$str.PHP_EOL;
	}

	/**
	 * 把记录的日志写入文件
	 */
	public static function build()
	{
		foreach(self::$logstr as $type=>$str){
			F(CACHE_PATH.'/log/'.$type.'.log',$str,FILE_APPEND);
		}
	}
}