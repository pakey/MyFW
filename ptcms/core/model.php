<?php

/**
 * Class PT_Model
 *
 * @method $this field() field(mixed $var)
 * @method $this where() where(mixed $var)
 * @method $this option() option(mixed $var)
 * @method $this data() data(mixed $var)
 * @method $this db() db(mixed $var)
 * @method $this distinct() distinct(mixed $var)
 * @method $this table() table(mixed $var)
 * @method $this having() having(mixed $var)
 * @method $this group() group(mixed $var)
 * @method $this page() page(mixed $var)
 * @method $this limit() limit(mixed $var)
 * @method $this order() order(mixed $var)
 * @method bool setTable() setTable(mixed $var)
 * @method string getPk() getPk(mixed $var)
 * @method int sum() sum(string $var)
 * @method int avg() avg(string $var)
 * @method int min() min(string $var)
 * @method int max() max(string $var)
 * @method int count() count()
 * @method array find() find()
 * @method array select() select()
 * @method int|bool insert() insert(array $id)
 * @method int|bool insertAll() insertAll(array $id)
 * @method bool update() update(array $id)
 * @method bool delete() delete()
 * @method mixed getField() getField(string $var)
 * @method mixed setField() setField(string $var)
 * @method mixed setInc() setInc(string $var)
 * @method mixed setDec() setDec(string $var)
 * @method mixed getLastSql() getLastSql()
 * @method mixed getError() getError()
 * @method mixed query() query(string $var)
 * @method mixed execute() execute(string $var)
 */
class PT_Model extends PT_Base {

    protected $table;
    protected $hasdb = false;
    protected static $_class = array();
    protected static $_data = array();
    protected $dbhand;

    public function __construct() {
        if ($this->config->get('db_type')) $this->hasdb = true;
    }

    /**
     * __call魔法方法 用于调用db相关的方法
     *
     * @param $method
     * @param $args
     * @return mixed
     */
    public function __call($method, $args) {
        if (!$this->dbhand) {
            //设置table则用table实例化db 否则是按照类名来实例化db
            $name         = $this->table ? $this->table : substr(get_class($this), 0, -5);
            $this->dbhand = $this->db($name);
        }
        if (method_exists($this->dbhand, $method)) {
            $res=call_user_func_array(array($this->dbhand, $method), $args);
            if (is_subclass_of($res,'Driver_Db_Dao')) return $this;
            return $res;
        }
        $this->response->error('未定义的model操作', $method, 'f');
        return false;
    }

    public function get($table, $id, $field = '') {
        $db = $this->db($table);
        if ($id == 0) return null;
        if (!isset(self::$_data[$table][$id])) {
            // 检索memCache，不存在则读取数据库
            self::$_data[$table][$id] = $this->cache->get($table . '.' . $id);
            if (self::$_data[$table][$id] === null) {
                self::$_data[$table][$id] = $db->find($id);
                if (self::$_data[$table][$id]) {
                    //其他处理 如小说的链接
                    $modelclass = strtr($table, '_', '') . 'model';
                    if ($this->$modelclass && method_exists($this->$modelclass, 'dataAppend')) {
                        self::$_data[$table][$id] = $this->$modelclass->dataAppend(self::$_data[$table][$id]);
                    }
                }
                $this->cache->set($table . '.' . $id, self::$_data[$table][$id], $this->config->get('cache_time', 900));
            }
        }
        if ($field !== '') {
            if (strpos($field, '.')) {
                $name  = explode('.', $field);
                $value = self::$_data[$table][$id];
                foreach ($name as $n) {
                    if (isset($value[$n])) {
                        $value = $value[$n];
                    } else {
                        return null;
                    }
                }
                return $value;
            } elseif (strpos($field, ',')) {
                //多字段获取  如"novelid,novelname"
                return array_intersect_key(self::$_data[$table][$id], array_flip(explode(',', $field)));
            } else {
                //单字段
                if (isset(self::$_data[$table][$id][$field])) {
                    return self::$_data[$table][$id][$field];
                } else {
                    return null;
                }
            }
        }
        return self::$_data[$table][$id];
    }

    public function set($table, $id, $data) {
        $db = $this->db($table);
        if ($db->where(array($db->getPk() => $id))->update($data)) {
            return $this->rm($table, $id);
        } else {
            return false;
        }
    }

    public function rm($table, $id) {
        $this->cache->rm($table . '.' . $id);
        unset(self::$_data[$table][$id]);
        return true;
    }
}