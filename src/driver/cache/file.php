<?php

namespace ptcms\driver\cache;

use ptcms\Storage;

class File {

    /**
     * @var Storage
     */
    protected static $handler;

    public function __construct($option = array()) {
        self::$handler=Storage::init();
    }

    public function set($key, $value, $time = 0) {
        $file = self::key2file($key);
        $data['data'] = $value;
        $data['time'] = ($time == 0) ? 0 : ($_SERVER['REQUEST_TIME'] + $time);
        return self::$handler->write($file, serialize($data));
    }

    public function get($key) {
        $file = self::key2file($key);
        if (is_file($file)) {
            $data = unserialize(self::$handler->read($file));
            if ($data && ($data['time'] > 0 && $data['time'] < $_SERVER['REQUEST_TIME'])) {
                self::rm($key);
                return null;
            }
            return $data['data'];
        } else {
            return null;
        }
    }

    public function rm($key) {
        $file = self::key2file($key);
        if (self::$handler->exist($file))
            return self::$handler->remove($file);
        return null;
    }

    public function key2file($key) {
        if (is_array($key)) $key=serialize($key);
        $key = md5($key);
        $file = CACHE_PATH . '/data/cache/' . $key{0} . '/' . $key{1} . '/' . $key . '.php';
        return $file;
    }

    public function inc($key,$num=1){
        $data=$this->get($key);
        if ($data){
            $data+=$num;
            $this->set($key,$data);
            return $data;
        }
        return false;
    }

    public function dec($key,$num=1){
        $data=$this->get($key);
        if ($data){
            $data-=$num;
            $this->set($key,$data);
            return $data;
        }
        return false;
    }

    public function clear() {
        self::$handler->remove(CACHE_PATH . '/data');
    }
}