<?php

/**
 * @Author: 杰少Pakey
 * @Email : admin@ptcms.com
 * @File  : widget.php
 */
abstract class widget extends Controller
{
	public function run($param)
	{
		$key = $this->getKey($param);
		$cachetime = empty($param['cachetime']) ? C('CACHE_TIME',null,600) : intval($param['cachetime']);
		$data = $this->checkCache($key, $cachetime);
		if (APP_DEBUG || $data === false) {
			$data = $this->exec($param);
			if (!empty($param['template'])){
				$this->assign('widget',$data);
				$data=$this->render($param['template'],'common');
			}
			if (!APP_DEBUG) Cache::set($key,array('time'=>NOW_TIME,'data'=>$data),$cachetime);
		}
		return $data;
	}

	/**
	 * 检查缓存是否有效  false 需要更新
	 *
	 * @param $key
	 * @param $cachetime
	 * @return bool
	 */
	public function checkCache($key, $cachetime)
	{
		$data = Cache::get($key);
		if (!isset($data['time']) || ($cachetime <> 0 && $data['time'] + $cachetime < NOW_TIME)) {
			return false;
		}
		return $data['data'];
	}

	public function getKey($param)
	{
		return md5(get_class($this).serialize($param));
	}

	/**
	 * 需要实现的方法
	 *
	 * @param $param
	 * @return mixed
	 */
	abstract public function exec($param);

}