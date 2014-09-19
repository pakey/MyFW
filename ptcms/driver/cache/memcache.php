<?php
/**
 * @Author: 杰少Pakey
 * @Email : admin@ptcms.com
 * @File  : Memcache.php
 */


class Driver_Cache_Memcache{
	protected static $handler=null;
	protected static $prefix=null;
	public function __construct($option=array())
	{
		self::$handler = new Memcache();
		self::$handler->connect(C('MEMCACHE_HOST',null,'127.0.0.1'),C('MEMCACHE_PORT',null,'11211'));
		self::$prefix=C('CACHE_PREFIX',null,PT_URL);
	}

	public function set($key, $value, $time=0)
	{
		return self::$handler->set(self::$prefix.$key,$value,MEMCACHE_COMPRESSED,$time);
	}

	public function get($key)
	{
		return self::$handler->get(self::$prefix.$key);
	}

	public function rm($key)
	{
		return self::$handler->delete(self::$prefix.$key);
	}

	public function mget(array $list)
	{
		$return=array();
		foreach($list as $key){
			$return[]=$this->get($key);
		}
		return $return;
	}

	public function clear()
	{
		return self::$handler->flush();
	}
}