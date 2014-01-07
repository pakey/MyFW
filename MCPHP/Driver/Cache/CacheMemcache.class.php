<?php

/***************************************************************
 *   $Program: MCPHP FarmeWork (A Open PHP Application FarmeWrok) $
 *    $Author: pakey $
 *     $Email: Pakey@ptcms.com $
 * $Copyright: 2009 - 2012 Ptcms Studio $
 *      $Link: http://www.ptcms.com $
 *   $License: http://www.ptcms.com/service/license.html $
 *      $Date: 2013-04-25 20:54:02 +0800 (星期四, 25 四月 2013) $
 *      $File: CacheMemcache.class.php $
 *  $Revision: 4 $
 *      $Desc:
 **************************************************************/

defined('MC_PATH') || exit('Permission denied');

/**
 * Memcache缓存驱动
 * @category   Extend
 * @package  Extend
 * @subpackage  Driver.Cache
 * @author    liu21st <liu21st@gmail.com>
 */
class CacheMemcache extends Cache {

    /**
     * 架构函数
     * @param array $options 缓存参数
     * @access public
     */
    function __construct($options=array()) {
        if (!extension_loaded('memcache')) {
            halt('Memcache扩展未加载');
        }
        $memServer=C('MEMCACHE_SERVER');
        if (empty($options)) {
            $options = array(
                'host' => isset($memServer['0']['0']) ? $memServer['0']['0'] : '127.0.0.1',
                'port' => isset($memServer['0']['1']) ? $memServer['0']['1'] : 11211,
                'timeout' => C('CACHE_TIMEOUT') ? C('CACHE_TIMEOUT') : false,
                'persistent' => false,
                'expire' => C('CACHE_TIME'),
                'length' => 0,
                'prefix' => C('DATA_CACHE_PREFIX'),
            );
        }
        $this->options = $options;
        $func = $options['persistent'] ? 'pconnect' : 'connect';
        $this->handler = new memcache;
        $this->connected = $options['timeout'] === false ?
            $this->handler->$func($options['host'], $options['port']) :
            $this->handler->$func($options['host'], $options['port'], $options['timeout']);
        //增加
        $memServerCount=count($memServer);
        if ($memServerCount>1){
            for($i=1;$i<$memServerCount;$i++){
                 $this->handler->addServer($memServer[$i]['0'],$memServer[$i]['1']);
            }
        }
    }

    /**
     * 读取缓存
     * @access public
     * @param string $name 缓存变量名
     * @return mixed
     */
    public function get($name) {
        ++$GLOBALS['_cacheRead'];
        return $this->handler->get($this->options['prefix'].$name);
    }

    /**
     * 写入缓存
     * @access public
     * @param string $name 缓存变量名
     * @param mixed $value  存储数据
     * @param integer $expire  有效时间（秒）
     * @return boolen
     */
    public function set($name, $value, $expire = null) {
        ++$GLOBALS['_cacheWrite'];
        if(is_null($expire)) {
            $expire  =  $this->options['expire'];
        }
        $name   =   $this->options['prefix'].$name;
        if($this->handler->set($name, $value, 0, $expire)) {
            if($this->options['length']>0) {
                // 记录缓存队列
                $this->queue($name);
            }
            return true;
        }
        return false;
    }

    /**
     * 删除缓存
     * @access public
     * @param string $name 缓存变量名
     * @param $ttl 是否直接删除
     * @return boolen
     */
    public function rm($name, $ttl = false) {
        $name   =   $this->options['prefix'].$name;
        if ($ttl === false)
            return $this->handler->delete($name);
        else
            return $this->handler->delete($name, $ttl);
    }

    /**
     * 清除缓存
     * @access public
     * @return boolen
     */
    public function clear() {
        return $this->handler->flush();
    }
}