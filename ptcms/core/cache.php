<?php
/**
 * @Author: 杰少Pakey
 * @Email : admin@ptcms.com
 * @File  : Cache.php
 */

class Cache{
	protected static $handler=null;

	static public function getInstance($type='File',$options=array()) {
		if (self::$handler===null){
			$class  =   'Driver_Cache_'.$type;
			self::$handler = new $class($options);
		}
		return self::$handler;
	}

	static public function __callstatic($method,$args){
		if (self::$handler===null){
			Cache::getInstance(C('CACHE_DRIVER',null,'memcache'));
		}
		//调用缓存驱动的方法
		if(method_exists(self::$handler, $method)){
			return call_user_func_array(array(self::$handler,$method), $args);
		}
	}
}