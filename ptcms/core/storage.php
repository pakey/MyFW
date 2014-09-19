<?php
/**
 * @Author: 杰少Pakey
 * @Email : admin@ptcms.com
 * @File  : Storage.php
 */

class Storage{
	protected static $handler=null;

	static public function getInstance($type='File',$options=array()) {
		if (self::$handler===null){
			$class  =   'Driver_Storage_'.$type;
			self::$handler = new $class($options);
		}
		return self::$handler;
	}

	static public function __callstatic($method,$args){
		if (self::$handler===null){
			Storage::getInstance(C('STORAGE_DRIVER',null,'file'));
		}
		//调用缓存驱动的方法
		if(method_exists(self::$handler, $method)){
			return call_user_func_array(array(self::$handler,$method), $args);
		}
	}
}