<?php

/**
 * @Author: 杰少Pakey
 * @Email : admin@ptcms.com
 * @File  : dc.php
 */
class Dc {

    static $_data = array();

    // 分发处理
    public static function __callStatic($method, $args) {
        $method = strtolower($method);
        // 方法是否允许
        if (count($args) == 2) {
            list($id, $data) = $args;
        } else {
            $id = $args['0'];
            $data = false;
        }
        if (is_string($data) or $data === false) {
            // 查找
            return self::get($method, $id, $data);
        } elseif (is_array($data)) {
            // 更新
            return self::update($method, $id, $data);
        } elseif ($data === true) {
            // 刷新
            return self::refresh($method, $id);
        } elseif ($data === null) {
            // 删除
            return self::delete($method, $id);
        }
        return false;
    }

    //更新信息
    static public function update($type, $id, $data) {
        $model = M($type);
        if ($model->data($data)->where(array($model->getPk() => $id))->save()) {
            return self::refresh($type, $id);
        } else {
            return false;
        }
    }

    //删除信息
    static public function delete($type, $id) {
        Cache::rm($type . '.' . $id);
        unset(self::$_data[$type][$id]);
        return true;
    }

    //更新信息
    static public function refresh($type, $id) {
        $model = M($type);
        self::$_data[$type][$id] = $model->find($id);
        if (self::$_data[$type][$id]) {
            //其他处理 如小说的链接
            if (method_exists($model, 'dataAppend')) {
                self::$_data[$type][$id] = $model->dataAppend(self::$_data[$type][$id]);
            }
        }
        // 写入缓存
        Cache::set($type . '.' . $id, self::$_data[$type][$id],C('cache_time',null,900));
        return self::$_data[$type][$id];
    }

    // 获取数据
    static public function get($type, $id, $field = '') {
        if ($id == 0) return null;
        if (!isset(self::$_data[$type][$id])) {
            // 检索memCache，不存在则读取数据库
            self::$_data[$type][$id] = Cache::get($type . '.' . $id);
            if (self::$_data[$type][$id] === null) {
                $model = M($type);
                self::$_data[$type][$id] = $model->find($id);
                if (self::$_data[$type][$id]) {
                    //其他处理 如小说的链接
                    if (method_exists($model, 'dataAppend')) {
                        self::$_data[$type][$id] = $model->dataAppend(self::$_data[$type][$id]);
                    }
                }
                Cache::set($type . '.' . $id, self::$_data[$type][$id],C('cache_time',null,900));
            }
        }
        if ($field !== '') {
            if(strpos($field,',')){
                //多字段获取  如"novelid,novelname"
                return array_intersect_key(self::$_data[$type][$id],array_flip(explode(',',$field)));
            }else{
                //单字段
                if (isset(self::$_data[$type][$id][$field])) {
                    return self::$_data[$type][$id][$field];
                } else {
                    return null;
                }
            }
        }
        return self::$_data[$type][$id];
    }
}