<?php
/**
 * @Author: 杰少Pakey
 * @Email : admin@ptcms.com
 * @File  : Memcache.php
 */


class Driver_Cache_Memcache{
	protected static $handler=null;
	public function __construct($option=array())
	{
	}

	public function set($key, $value, $time=0)
	{
		$file=self::key2file($key);
		$data['data']=$value;
		$data['time']=($time==0)?0:(NOW_TIME+$time);
		F($file,serialize($data));
	}

	public function get($key)
	{
		$file=self::key2file($key);
		if (is_file($file)){
			$data=unserialize(F($file));
			if ($data['time']>0 && $data['time']<NOW_TIME){
				self::rm($key);
				return null;
			}
			return $data['data'];
		}else{
			return null;
		}
	}

	public function rm($key)
	{
		$file=self::key2file($key);
		if (is_file($file))
			return unlink($file);
		return null;
	}

	public function key2file($key)
	{
		$key=md5($key);
		$file=CACHE_PATH.'/data/'.substr($key,0,1).'/'.substr($key,1,2).'/'.$key.'.ptc';
		return $file;
	}

	public function clear($path=CACHE_PATH)
	{
		if (!file_exists($path)) return true;
		if (!is_dir($path)) return unlink($path);
		$handle = opendir($path);
		while (($file = readdir($handle)) !== false) {
			if ($file !== '.' and $file !== '..') self::clear($path . '/' . $file);
		}
		closedir($handle);
		return rmdir($path);
	}
}