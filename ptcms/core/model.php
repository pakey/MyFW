<?php

/**
 * @Author: 杰少Pakey
 * @Email : admin@ptcms.com
 * @File  : Model.php
 */
class Model {

    /**
     * 数据表名
     *
     * @var string
     */
    protected $tableName = null;

    /**
     * 数据库引擎
     *
     * @var string
     */
    protected $driver = null;

    /**
     * 数据表字段信息
     *
     * @var array
     */
    protected $fields = array();

    /**
     * 数据表的主键信息
     *
     * @var string
     */
    protected $pk = null;

    /**
     * model所对应的数据表名的前缀
     *
     * @var string
     */
    protected $prefix = null;

    /**
     * 数据表信息缓存文件存放目录
     *
     * @var string
     */
    protected $cachePath = null;

    /**
     * SQL语句容器，用于存放SQL语句，为SQL语句组装函数提供SQL语句片段的存放空间。
     *
     * @var array
     */
    protected $parts = array();

    /**
     * 错误信息
     *
     * @var string
     */
    protected $errorinfo = null;

    /**
     * 数据库连接参数
     *
     * @var array
     */
    protected static $config = array();

    /**
     * 主数据库实例化对象
     *
     * @var object
     */
    protected static $master = array();

    /**
     * 从数据库实例化对象
     *
     * @var object
     */
    protected static $slave = array();

    /**
     * 数据库实例化是否为单例模式
     *
     * @var boolean
     */
    protected $singleton = false;
    /**
     * 最后一条sql语句
     *
     * @var string
     */
    protected $sql = '';

    /**
     * 相关数据存储
     *
     * @var array
     */
    protected $data = array();


    public function __construct($tablename = '') {
        //定义model字段缓存文件目录
        $this->cachePath = CACHE_PATH . '/fields';
        //获取数据库连接参数
        self::$config = $this->parseConfig();

        if ($tablename) {
            $this->setTable($tablename);
        }
        return true;
    }

    public function __set($key, $value) {
        $this->data[strtolower($key)] = $value;
    }

    public function __get($key) {
        return $this->data[strtolower($key)];
    }

    public function __isset($key) {
        return isset($this->data[strtolower($key)]);
    }

    public function __unset($key) {
        unset($this->data[strtolower($key)]);
    }

    public function __call($method, $args) {
        $method = strtolower($method);
        //todo join union
        if (in_array($method, array('field', 'order', 'limit', 'page', 'group', 'having', 'table', 'distinct'))) {
            $this->parts[$method] = isset($args['0']) ? $args['0'] : '';
        } elseif ($method == 'where') {
            foreach ($args as $v) {
                if (is_string($v)) {
                    $this->parts['where'][] = array('_string' => $v);
                } elseif (is_array($v)) {
                    foreach ($v as $field => $var) {
                        $this->parts['where'][] = array($field => $var);
                    }
                }
            }
        } elseif (in_array($method, array('count', 'max', 'min', 'avg', 'sum'))) {
            $this->parts['field'] = empty($args['0']) ? '*' : $args['0'];
            return $this->parseCount($method);
        } elseif ($method == 'data') {
            $this->data = $args['0'];
        } else {
            halt('不具备的Model操作' . $method);
        }
        return $this;
    }

    public function setTable($tablename) {
        $this->tableName = $this->prefix . $tablename;
        $this->getTableField();
    }

    // 配置解析
    public function parseConfig() {
        $params = C('DB_MYSQL', null, array());

        //分析,检测配置文件内容
        if (!is_array($params)) {
            halt('数据库配置文件必须为数组');
        }
        $params = array_change_key_case($params);

        //获取数据表前缀，默认为空
        $this->prefix = (isset($params['prefix']) && $params['prefix']) ? trim($params['prefix']) : '';


        // 指定数据库链接引擎
        $this->driver = (isset($params['driver']) && $params['driver']) ? trim($params['driver']) : 'Pdo';

        //分析主数据库连接参数
        $config_params = array();
        if (isset($params['master']) && is_array($params['master'])) {
            $config_params['master'] = $params['master'];
        } else {
            halt('主库数据库配置不存在');
        }

        //分析从数据库连接参数
        if (isset($params['slave']) && $params['slave']) {
            $config_params['slave'] = $params['slave'];
        } else {
            //当没有从库连接参数时,开启单例模式
            $this->singleton = true;
            $config_params['slave'] = $config_params['master'];
        }
        //将数据库的用户名及密码及时从内存中注销，提高程序安全性
        unset($params);

        return $config_params;
    }

    /**
     * @param string $id
     * @return mixed
     */
    public function master($id = null) {
        if (self::$master) {
            return self::$master[array_rand(self::$master)];
        }
        $driverclass = 'Driver_Model_' . $this->driver;
        foreach (self::$config['master'] as $k => $v) {
            $v = array_change_key_case($v);
            self::$master[$k] = new $driverclass($v);
        }
        if ($this->singleton)
            self::$slave = self::$master;
        if ($id === null)
            return self::$master[array_rand(self::$master)];
        else
            return self::$master[$id];
    }

    /**
     * @return mixed
     */
    public function slave() {
        if (self::$slave) {
            return self::$slave[array_rand(self::$slave)];
        }
        $driverclass = 'Driver_Model_' . $this->driver;
        foreach (self::$config['slave'] as $k => $v) {
            $v = array_change_key_case($v);
            self::$slave[$k] = new $driverclass($v);
        }
        if ($this->singleton)
            self::$master = self::$slave;
        return self::$slave[array_rand(self::$slave)];
    }

    public function getTableField($tablename = '') {
        $tablename = empty($tablename) ? $this->tableName : $tablename;
        if (!$tablename) {
            halt('您必须设置表名后才可以使用该方法');
        }
        $cachefile = $this->cachePath . '/' . $tablename . '.php';
        if (!APP_DEBUG && is_file($cachefile))
            list($this->pk, $this->fields) = include $cachefile;
        else {
            $pks = $fields = array();
            if ($tableInfo = (array)$this->slave()->fetchAll("SHOW FIELDS FROM {$tablename}")) {
                foreach ($tableInfo as $v) {
                    if ($v['Key'] == 'PRI') $pks[] = strtolower($v['Field']);
                    $fields[] = strtolower($v['Field']);
                }
                $this->pk = empty($pks) ? '' : $pks['0'];
                $this->fields = $fields;
                if (!APP_DEBUG) {
                    $cacheData = array($this->pk, $this->fields);
                    F($cachefile, $cacheData);
                }
            } else {
                halt('获取表' . $tablename . '信息发送错误 ' . $this->slave()->error());
            }
        }
        return $this->fields;
    }

    public function getPk() {
        if ($this->pk === null)
            $this->getTableField();
        return $this->pk;
    }

    /**
     * @param array $data
     * @param bool $replace
     * @return mixed
     */
    public function insert($data = array(), $replace = false) {
        if (!empty($data)) $this->data = array_merge($this->data, array_change_key_case($data));
        if ($this->tableName || $this->parts['table']) {
            foreach ($this->data as $k => $v) { // 过滤参数
                if (in_array($k, $this->fields))
                    $this->data[$k] = $this->parseValue($v);
                else
                    unset($this->data[$k]);
            }
            $fields = array_map(array($this, 'parseKey'), array_keys($this->data));
            $this->sql = ($replace ? 'REPLACE' : 'INSERT') . ' INTO ' . $this->parseTable() . '(' . implode(',', $fields) . ') VALUES (' . implode(',', $this->data) . ');';
            $this->data = $this->parts = array();
            $this->errorinfo = ''; //清空存储
            if ($this->master()->execute($this->sql)) {
                return $this->master()->insertId();
            } else {
                $this->errorinfo = $this->master()->errno() . ':' . $this->master()->error();
                return false;
            }
        } else {
            halt('insert操作必须设置要操作的表');
        }
    }

    public function update($data = array()) {
        if (!empty($data)) $this->data = array_merge($this->data, array_change_key_case($data));
        if ($this->tableName || $this->parts['table']) {
            $sets = array();
            if (!empty($this->data[$this->pk])) { //主键不允许更改 当作where条件
                $this->parts['where'][] = array($this->pk => $this->data[$this->pk]);
                unset($this->data[$this->pk]);
            }
            if (empty($this->parts['field'])) { //通过field连贯操作限制更新的字段
                $fields = $this->fields;
            } else {
                $fields = is_string($this->parts['field']) ? explode(',', $this->parts['field']) : $this->parts['field'];
            }
            foreach ($this->data as $k => $v) { // 数据解析
                if (in_array($k, $fields)) {
                    $sets[] = $this->parseKey($k) . '=' . $this->parseValue($v);
                }
            }
            $this->sql = 'UPDATE ' . $this->parseTable() . ' SET ' . implode(',', $sets)
                . $this->parseWhere()
                . $this->parseOrder()
                . $this->parseLimit();
            $this->data = $this->parts = array();
            $this->errorinfo = ''; //清空存储
            $affectRow = $this->master()->execute($this->sql);
            if ($this->master()->error()) {
                $this->errorinfo = $this->master()->errno() . ':' . $this->master()->error();
                return false;
            } else {
                return $affectRow;
            }
        } else {
            halt('update操作必须设置要操作的表');
        }
    }

    public function delete() {
        if (!empty($data)) $this->data = array_merge($this->data, array_change_key_case($data));
        if ($this->tableName || $this->parts['table']) {
            $this->sql = 'DELETE FROM' . $this->parseTable()
                . $this->parseWhere()
                . $this->parseOrder()
                . $this->parseLimit();
            $this->data = $this->parts = array();
            $this->errorinfo = ''; //清空存储
            if ($affectRow = $this->master()->execute($this->sql)) {
                return $affectRow;
            } else {
                $this->errorinfo = $this->master()->errno() . ':' . $this->master()->error();
                return false;
            }
        } else {
            halt('update操作必须设置要操作的表');
        }
    }

    public function find($id = null) {
        if (is_scalar($id)) $this->parts['where'][] = array($this->pk => $id);
        $this->parts['limit'] = 1;
        $this->sql = "SELECT "
            . $this->parseField() . ' FROM '
            . $this->parseTable()
            . $this->parseJoin()
            . $this->parseWhere()
            . $this->parseGroup()
            . $this->parseHaving()
            . $this->parseOrder()
            . $this->parseLimit()
            . $this->parseUnion();
        $this->data = $this->parts = array();
        $this->errorinfo = ''; //清空存储
        $row = $this->slave()->fetch($this->sql);
        if (!$this->slave()->error()) {
            if ($row) {
                return $row;
            } else {
                return null;
            }
        } else {
            $this->errorinfo = $this->slave()->errno() . ':' . $this->slave()->error();
            return false;
        }
    }

    public function select() {
        $this->sql = "SELECT "
            . $this->parseField() . ' FROM '
            . $this->parseTable()
            . $this->parseJoin()
            . $this->parseWhere()
            . $this->parseGroup()
            . $this->parseHaving()
            . $this->parseOrder()
            . $this->parseLimit()
            . $this->parseUnion();
        $this->data = $this->parts = array();
        $this->errorinfo = ''; //清空存储
        $row = $this->slave()->fetchAll($this->sql);
        if (!$this->slave()->error()) {
            if ($row) {
                return $row;
            } else {
                return null;
            }
        } else {
            $this->errorinfo = $this->slave()->errno() . ':' . $this->slave()->error();
            return false;
        }
    }

    public function getField($field, $isArr = false) {
        if (empty($this->parts['field'])) $this->parts['field'] = $field;
        if ($isArr) {
            $row = $this->select();
            if ($row === false || $row === null)
                return $row;
            else {
                $res = array();
                if ($field !== true && strpos($field, ',') === false) {
                    foreach ($row as $v) {
                        $res[] = $v[$field];
                    }
                } else {
                    foreach ($row as $v) {
                        $res[current($v)] = (count($v) == 1) ? current($v) : $v;
                    }
                }
                return $res;
            }
        } else {
            $row = $this->find();
            if ($row === false || $row === null)
                return $row;
            elseif ($field === true)
                return current($row);
            elseif (isset($row[$field]))
                return $row[$field];
            else
                return '';
        }
    }

    public function setField($field, $data) {
        if (is_array($field)) {
            $this->data = $field;
        } elseif (is_string($field)) {
            $this->data[$field] = $data;
        }
        $this->update();
    }

    public function setInc($field, $step = 1) {
        $this->setField($field, array('exp', "{$field}+{$step}"));
    }

    public function setDec($field, $step = 1) {
        $this->setField($field, array('exp', "{$field}-{$step}"));
    }

    /**
     * 字段和表名处理添加`
     *
     * @access protected
     * @param string $key
     * @return string
     */
    protected function parseKey(&$key) {
        $key = trim($key);
        if (!preg_match('/[,\'\"\*\(\)`.\s]/', $key)) {
            $key = '`' . $key . '`';
        }
        return $key;
    }

    /**
     * value分析
     *
     * @access protected
     * @param mixed $value
     * @return string
     */
    protected function parseValue($value) {
        if (is_string($value)) {
            $value = '\'' . $this->master()->escapeString($value) . '\'';
        } elseif (isset($value[0]) && is_string($value[0]) && strtolower($value[0]) == 'exp') {
            $value = $this->master()->escapeString($value[1]);
        } elseif (is_array($value)) {
            $value = array_map(array($this, 'parseValue'), $value);
        } elseif (is_bool($value)) {
            $value = $value ? '1' : '0';
        } elseif (is_null($value)) {
            $value = 'null';
        }
        return $value;
    }

    public function getLastSql() {
        return $this->sql;
    }

    public function getError() {
        return $this->errorinfo;
    }

    protected function parseWhere() {
        if (empty($this->parts['where'])) return ' WHERE 1';
        return ' WHERE ' . $this->parseWhereCondition($this->parts['where']);
    }

    protected function parseWhereCondition($condition) {
        $logic = ' AND ';
        $wheres = array();
        foreach ($condition as $var) {
            $k = key($var);
            $v = current($var);
            if (in_array($k, $this->fields)) {
                $wheres[] = '(' . $this->parseWhereItem($this->parseKey($k), $v) . ')';
            } elseif ($k == '_logic' && in_array(strtolower($v), array('or', 'and', 'xor'))) {
                $logic = ' ' . strtoupper($v) . ' ';
            } elseif ($k == '_string') {
                $wheres[] = '(' . $v . ')';
            } else {
            }
        }
        return implode($logic, $wheres);
    }

    /**
     * @param $field
     * @param $var
     * @return mixed
     */
    protected function parseWhereItem($field, $var) {
        if (is_array($var)) {
            switch (strtolower($var['0'])) {
                case '>':
                case '<':
                case '>=':
                case '<=':
                case '=':
                case '<>':
                case 'like':
                case 'not like':
                    return $field . ' ' . $var['0'] . ' ' . $this->parseValue($var['1']);
                case 'in':
                case 'not in':
                    if (is_array($var['1']))
                        $var['1'] = implode(',', $var['1']);
                    return "{$field} {$var['0']} ( {$var['1']} )";
                case 'between':
                case 'not between':
                    if (is_string($var['1']))
                        $var['1'] = explode(',', $var['1']);
                    return "{$field} {$var['0']} {$var['1']['0']} and {$var['1']['1']}";
                case 'exp':
                    return "{$field} {$var['1']}";
                default:
                    return '';
            }
        } else {
            return $field . ' = ' . $this->parseValue($var);
        }
    }

    protected function parseOrder() {
        if (!empty($this->parts['order'])) {
            if (is_string($this->parts['order'])) {
                return ' ORDER BY ' . $this->parts['order'];
            }
        }
        return '';
    }

    protected function parseGroup() {
        if (!empty($this->parts['group'])) {
            if (is_string($this->parts['group'])) {
                return ' GROUP BY ' . $this->parseKey($this->parts['group']);
            } elseif (is_array($this->parts['group'])) {
                array_walk($this->parts['group'], array($this, 'parseKey'));
                return ' GROUP BY ' . implode(',', $this->parts['group']);
            }
        }
        return '';
    }

    protected function parseHaving() {
        if (empty($this->parts['having'])) return '';
        return ' HAVING ' . $this->parseWhereCondition($this->parts['having']);
    }

    protected function parseLimit() {
        if (isset($this->parts['page'])) {
            // 根据页数计算limit
            if (strpos($this->parts['page'], ',')) {
                list($page, $listRows) = explode(',', $this->parts['page']);
            } else {
                $page = $this->parts['page'];
            }
            $page = $page ? $page : 1;
            $listRows = isset($listRows) ? $listRows : (is_numeric($this->parts['limit']) ? $this->parts['limit'] : 20);
            $offset = $listRows * ((int)$page - 1);
            return ' LIMIT ' . $offset . ',' . $listRows;
        } elseif (!empty($this->parts['limit'])) {
            return ' LIMIT ' . $this->parts['limit'];
        } else {
            return '';
        }
    }

    protected function parseUnion() {

    }

    protected function parseJoin() {

    }

    protected function parseField() {
        if (empty($this->parts['field'])) {
            return '*';
        } else {
            if (is_string($this->parts['field'])) {
                $this->parts['field'] = explode(',', $this->parts['field']);
            }
            array_walk($this->parts['field'], array($this, 'parseKey'));
            return implode(',', $this->parts['field']);
        }
    }

    protected function parseTable() {
        if (empty($this->parts['table'])) {
            if ($this->tableName)
                return $this->parseKey($this->tableName);
            else
                halt('必须设置表才可以进行此操作');
        } else {
            return str_replace('!@#_', self::$config['prefix'], $this->parts['table']);
        }
    }

    protected function parseDistinct() {
        return $this->parts['distinct'] ? ' DISTINCT ' : '';
    }

    public function parseCount($method) {
        $this->parts['field'] = "{$method}({$this->parts['field']}) as pt_num";
        return $this->getField('pt_num');
    }

    public function start() {
        $this->master(0)->startTrans();
    }

    public function commit() {
        $this->master(0)->commit();
    }

    public function rollback() {
        $this->master(0)->rollback();
    }

    public function fetch($sql) {
        return $this->slave()->fetch($sql);
    }

    public function fetchall($sql) {
        return $this->slave()->fetchall($sql);
    }

}

