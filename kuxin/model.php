<?php

namespace Kuxin;

class Model
{
    /**
     * 数据库节点信息
     *
     * @var string
     */
    protected $node = 'common';
    
    /**
     * 数据表名
     *
     * @var string
     */
    protected $tableName = null;
    
    /**
     * 数据表字段信息
     *
     * @var array
     */
    protected $fields = [];
    
    /**
     * 数据表的主键信息
     *
     * @var string
     */
    protected $pk = 'id';
    
    /**
     * model所对应的数据表名的前缀
     *
     * @var string
     */
    protected $prefix = 'ptcms_';
    
    /**
     * SQL语句容器，用于存放SQL语句，为SQL语句组装函数提供SQL语句片段的存放空间。
     *
     * @var array
     */
    protected $data = [];
    
    /**
     * 错误信息
     *
     * @var string
     */
    protected $errorinfo = null;
    
    public function __construct()
    {
        if (!$this->tableName) {
            $this->tableName = static::class;
        }
        $this->setTable($this->tableName);
    }
    
    public function __call($method, $args)
    {
        trigger_error('不具备的Model操作' . $method);
    }
    
    public function sum($value = '')
    {
        $value               = $value ?: '*';
        $this->data['field'] = "sum({$value}) as kx_num";
        return $this->getField('kx_num');
    }
    
    public function avg($value = '')
    {
        $value               = $value ?: '*';
        $this->data['field'] = "avg({$value}) as kx_num";
        return $this->getField('kx_num');
    }
    
    public function min($value = '')
    {
        $value               = $value ?: '*';
        $this->data['field'] = "min({$value}) as kx_num";
        return $this->getField('kx_num');
    }
    
    public function max($value = '')
    {
        $value               = $value ?: '*';
        $this->data['field'] = "max({$value}) as kx_num";
        return $this->getField('kx_num');
    }
    
    public function count($value = '')
    {
        $value               = $value ?: '*';
        $this->data['field'] = "count({$value}) as kx_num";
        return $this->getField('kx_num');
    }
    
    public function where($data, array $option = [])
    {
        if (is_string($data)) {
            $this->data['where'][] = ['_string' => $data];
            $this->data['bind']    = array_merge($this->data['bind'], $option);
        } elseif (is_array($data)) {
            foreach ($data as $field => $var) {
                $this->data['where'][] = [$field => ':'.$field];
                $this->data['bind'][] = [':'.$field => $var];
            }
        }
        return $this;
    }
    
    public function database($value)
    {
        $this->data['node'] = $value;
        return $this;
    }
    
    public function distinct($value)
    {
        $this->data['distinct'] = $value;
        return $this;
    }
    
    public function table($value)
    {
        $this->data['table'] = $value;
        return $this;
    }
    
    public function having($value)
    {
        $this->data['having'] = $value;
        return $this;
    }
    
    public function group($value)
    {
        $this->data['group'] = $value;
        return $this;
    }
    
    public function page($value)
    {
        $this->data['page'] = $value;
        return $this;
    }
    
    public function limit($value)
    {
        $this->data['limit'] = $value;
        return $this;
    }
    
    public function order($value)
    {
        $this->data['order'] = $value;
        return $this;
    }
    
    public function field($value)
    {
        $this->data['field'] = $value;
        return $this;
    }
    
    public function join($table, $on = [], $type = 'left')
    {
        if (is_array($table)) {
            $this->data['join'] = $table;
        } else {
            $this->data['join'] = ['table' => $table, 'on' => $on, 'type' => $type];
        }
        return $this;
    }
    
    public function setTable($tablename)
    {
        $this->tableName = $this->prefix . strtolower($tablename);
        $this->getTableField();
    }
    
    public function getTableField($tablename = '')
    {
        $tablename = empty($tablename) ? $this->tableName : $tablename;
        if (!$tablename) {
            halt('您必须设置表名后才可以使用该方法');
        }
        $data = Cache::get('tablefield_' . $tablename);
        if (isset($data['0']) && isset($data['1'])) {
            list($this->pk, $this->fields) = $data;
        } else {
            $pks = $fields = [];
            $db  = $this->slave();
            if ($tableInfo = (array)$db->fetchAll("SHOW FIELDS FROM {$tablename}")) {
                foreach ($tableInfo as $v) {
                    if ($v['Key'] == 'PRI') $pks[] = strtolower($v['Field']);
                    $fields[] = strtolower($v['Field']);
                }
                $this->pk     = empty($pks) ? 'id' : $pks['0'];
                $this->fields = $fields;
                $cacheData    = [$this->pk, $this->fields];
                Cache::set('tablefield_' . $tablename, $cacheData, Config::get('cache_time_m'));
            } else {
                halt('获取表' . $tablename . '信息发送错误 ' . $db->error());
                return false;
            }
        }
        return $this->fields;
    }
    
    public function getPk()
    {
        if ($this->pk === null)
            $this->getTableField();
        return $this->pk;
    }
    
    /**
     * @param array $data
     * @param bool  $replace
     * @return mixed
     */
    public function insert($data = [], $replace = false)
    {
        if (!empty($data)) $this->data = array_merge($this->data, array_change_key_case($data));
        if ($this->tableName || $this->parts['table']) {
            foreach ($this->data as $k => $v) { // 过滤参数
                if (in_array($k, $this->fields))
                    $this->data[$k] = $this->parseValue($v);
                else
                    unset($this->data[$k]);
            }
            $fields          = array_map([$this, 'parseKey'], array_keys($this->data));
            $this->sql       = ($replace ? 'REPLACE' : 'INSERT') . ' INTO ' . $this->parseTable() . '(' . implode(',', $fields) . ') VALUES (' . implode(',', $this->data) . ');';
            $this->data      = $this->parts = [];
            $this->errorinfo = ''; //清空存储
            $db              = $this->master();
            if ($db->execute($this->sql)) {
                return $db->insertId();
            } else {
                $this->errorinfo = $db->errno() . ':' . $db->error();
                return false;
            }
        } else {
            halt('insert操作必须设置要操作的表');
            return false;
        }
    }
    
    /**
     * 插入记录
     *
     * @access public
     * @param mixed   $datas   数据
     * @param boolean $replace 是否replace
     * @return false | integer
     */
    public function insertAll($datas, $replace = false)
    {
        if (!is_array($datas[0])) return false;
        if ($this->tableName || $this->parts['table']) {
            $values = [];
            foreach ($datas as $data) {
                $value = [];
                foreach ($data as $key => $val) {
                    if (in_array($key, $this->fields))
                        $value[$key] = $this->parseValue($val);
                }
                $values[] = '(' . implode(',', $value) . ')';
            }
            $fields = array_map([$this, 'parseKey'], array_keys($datas[0]));
            
            $this->sql       = ($replace ? 'REPLACE' : 'INSERT') . ' INTO ' . $this->parseTable() . ' (' . implode(',', $fields) . ') VALUES ' . implode(',', $values);
            $this->data      = $this->parts = [];
            $this->errorinfo = ''; //清空存储
            $db              = $this->master();
            if ($db->execute($this->sql)) {
                return $db->insertId();
            } else {
                $this->errorinfo = $db->errno() . ':' . $db->error();
                return false;
            }
        } else {
            halt('insert操作必须设置要操作的表');
            return false;
        }
    }
    
    /**
     * @param array $data
     * @return mixed
     */
    public function update($data = [])
    {
        if (!empty($data)) $this->data = array_merge($this->data, array_change_key_case($data));
        if ($this->tableName || $this->parts['table']) {
            $sets = [];
            if (!empty($this->data[$this->pk])) { //主键不允许更改 当作where条件
                $this->parts['where'][] = [$this->pk => $this->data[$this->pk]];
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
            $this->sql       = 'UPDATE ' . $this->parseTable() . ' SET ' . implode(',', $sets)
                . $this->parseWhere()
                . $this->parseOrder()
                . $this->parseLimit();
            $this->data      = $this->parts = [];
            $this->errorinfo = ''; //清空存储
            $db              = $this->master();
            $affectRow       = $db->execute($this->sql);
            if ($affectRow === false) {
                $this->errorinfo = $db->errno() . ':' . $db->error();
                return false;
            } else {
                return $affectRow;
            }
        } else {
            halt('update操作必须设置要操作的表');
            return false;
        }
    }
    
    public function delete()
    {
        if (!empty($data)) $this->data = array_merge($this->data, array_change_key_case($data));
        if ($this->tableName || $this->parts['table']) {
            $this->sql       = 'DELETE FROM' . $this->parseTable()
                . $this->parseWhere()
                . $this->parseOrder()
                . $this->parseLimit();
            $this->data      = $this->parts = [];
            $this->errorinfo = ''; //清空存储
            $db              = $this->master();
            $affectRow       = $db->execute($this->sql);
            if ($affectRow === false) {
                $this->errorinfo = $db->errno() . ':' . $db->error();
                return false;
            } else {
                return $affectRow;
            }
        } else {
            halt('update操作必须设置要操作的表');
            return false;
        }
    }
    
    public function find($id = null)
    {
        if (is_scalar($id)) {
            $this->parts['where'][] = [$this->getPk() => $id];
        }
        $this->parts['limit'] = 1;
        $this->sql            = "SELECT " . $this->parseField() . ' FROM '
            . $this->parseTable() . ' as a'
            . $this->parseJoin()
            . $this->parseWhere()
            . $this->parseGroup()
            . $this->parseHaving()
            . $this->parseOrder()
            . $this->parseLimit()
            . $this->parseUnion();
        //清空存储
        $this->data      = $this->parts = [];
        $this->errorinfo = '';
        //执行查询
        $db  = $this->slave();
        $row = $db->fetch($this->sql);
        if ($row === false) {
            $this->errorinfo = $db->errno() . ':' . $db->error();
        } else {
            if (!$row) {
                $row = null;
            }
        }
        return $row;
    }
    
    /**
     * @return array|bool
     */
    public function select()
    {
        $this->sql       = "SELECT " . $this->parseField() . ' FROM '
            . $this->parseTable() . 'as a'
            . $this->parseJoin()
            . $this->parseWhere()
            . $this->parseGroup()
            . $this->parseHaving()
            . $this->parseOrder()
            . $this->parseLimit()
            . $this->parseUnion();
        $this->data      = $this->parts = [];
        $this->errorinfo = ''; //清空存储
        $db              = $this->slave();
        $row             = $db->fetchAll($this->sql);
        if ($row === false) {
            $this->errorinfo = $db->errno() . ':' . $db->error();
        } else {
            if (!$row) {
                $row = [];
            }
        }
        return $row;
    }
    
    /**
     * 获取具体字段的值
     *
     * @param      $field
     * @param bool $isArr 是否返回数组
     * @return mixed|null|string
     */
    public function getField($field, $isArr = false)
    {
        if (empty($this->parts['field'])) $this->parts['field'] = $field;
        if ($isArr) {
            $row = $this->select();
            if ($row === false || $row === null)
                return $row;
            else {
                $res = [];
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
    
    /**
     * 设置某个字段的值
     *
     * @param $field
     * @param $data
     */
    public function setField($field, $data)
    {
        if (is_array($field)) {
            $this->data = $field;
        } elseif (is_string($field)) {
            $this->data[$field] = $data;
        }
        $this->update();
    }
    
    /**
     * 增加数据库中某个字段值
     *
     * @param     $field
     * @param int $step
     */
    public function setInc($field, $step = 1)
    {
        $this->setField($field, ['exp', "{$field}+{$step}"]);
    }
    
    /**
     * 减少数据库中某个字段值
     *
     * @param     $field
     * @param int $step
     */
    public function setDec($field, $step = 1)
    {
        $this->setField($field, ['exp', "{$field}-{$step}"]);
    }
    
    /**
     * 字段和表名处理添加`
     *
     * @access protected
     * @param string $key
     * @return string
     */
    protected function parseKey(&$key)
    {
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
    protected function parseValue($value)
    {
        if (is_string($value)) {
            $value = '\'' . $this->master()->escapeString($value) . '\'';
        } elseif (isset($value[0]) && is_string($value[0]) && strtolower($value[0]) == 'exp') {
            $value = $this->master()->escapeString($value[1]);
        } elseif (is_array($value)) {
            $value = array_map([$this, 'parseValue'], $value);
        } elseif (is_bool($value)) {
            $value = $value ? '1' : '0';
        } elseif (is_null($value)) {
            $value = 'null';
        }
        return $value;
    }
    
    public function getLastSql()
    {
        return $this->sql;
    }
    
    public function getError()
    {
        return $this->errorinfo;
    }
    
    protected function parseWhere()
    {
        if (empty($this->parts['where'])) return ' WHERE 1';
        return ' WHERE ' . $this->parseWhereCondition($this->parts['where']);
    }
    
    protected function parseWhereCondition($condition)
    {
        $logic  = ' AND ';
        $wheres = [];
        foreach ($condition as $var) {
            $k = key($var);
            $v = current($var);
            if (in_array($k, $this->fields, true)) {
                if (empty($this->parts['join'])) {
                    $wheres[] = '(' . $this->parseWhereItem($this->parseKey($k), $v) . ')';
                } else {
                    $wheres[] = '(' . $this->parseWhereItem('a.' . $this->parseKey($k), $v) . ')';
                }
            } elseif ($k == '_logic' && in_array(strtolower($v), ['or', 'and', 'xor'])) {
                $logic = ' ' . strtoupper($v) . ' ';
            } elseif ($k == '_string') {
                $wheres[] = '(' . $v . ')';
            } else {
            }
        }
        return ($wheres === []) ? 1 : implode($logic, $wheres);
    }
    
    /**
     * @param $field
     * @param $var
     * @return mixed
     */
    protected function parseWhereItem($field, $var)
    {
        if (is_array($var)) {
            if (isset($_REQUEST[$field]) && is_array($_REQUEST[$field])) {
                return $field . ' = ' . $this->parseValue(strval($var));
            }
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
                    if (empty($var['1'])) return '1';
                    if (is_array($var['1'])) {
                        $var['1'] = implode(',', $this->parseValue($var['1']));
                    }
                    return "{$field} {$var['0']} ( {$var['1']} )";
                case 'between':
                case 'not between':
                    if (is_string($var['1']))
                        $var['1'] = explode(',', $var['1']);
                    $var['1'] = $this->parseValue($var['1']);
                    return "{$field} {$var['0']} {$var['1']['0']} and {$var['1']['1']}";
                case 'exp':
                    return "{$field} {$var['1']}";
                default:
                    return '1';
            }
        } else {
            return $field . ' = ' . $this->parseValue($var);
        }
    }
    
    protected function parseOrder()
    {
        if (!empty($this->parts['order'])) {
            if (is_string($this->parts['order'])) {
                return ' ORDER BY ' . $this->parts['order'];
            }
        }
        return '';
    }
    
    protected function parseGroup()
    {
        if (!empty($this->parts['group'])) {
            if (is_string($this->parts['group'])) {
                return ' GROUP BY ' . $this->parseKey($this->parts['group']);
            } elseif (is_array($this->parts['group'])) {
                array_walk($this->parts['group'], [$this, 'parseKey']);
                return ' GROUP BY ' . implode(',', $this->parts['group']);
            }
        }
        return '';
    }
    
    protected function parseHaving()
    {
        if (empty($this->parts['having'])) return '';
        return ' HAVING ' . $this->parseWhereCondition($this->parts['having']);
    }
    
    protected function parseLimit()
    {
        if (isset($this->parts['page'])) {
            // 根据页数计算limit
            if (strpos($this->parts['page'], ',')) {
                list($page, $listRows) = explode(',', $this->parts['page']);
            } else {
                $page = $this->parts['page'];
            }
            $page     = $page ? $page : 1;
            $listRows = isset($listRows) ? $listRows : (is_numeric($this->parts['limit']) ? $this->parts['limit'] : 20);
            $offset   = $listRows * ((int)$page - 1);
            return ' LIMIT ' . $offset . ',' . $listRows;
        } elseif (!empty($this->parts['limit'])) {
            return ' LIMIT ' . $this->parts['limit'];
        } else {
            return '';
        }
    }
    
    protected function parseUnion()
    {
    
    }
    
    protected function parseJoin()
    {
        if (empty($this->parts['join'])) return '';
        $table = $this->parts['join']['table'];
        $type  = $this->parts['join']['type'];
        $on    = $this->parts['join']['on'];
        if (empty($table)) {
            return '';
        } elseif (strpos($table, self::$_config['prefix']) === false) {
            $table = self::$_config['prefix'] . $table;
        }
        if (empty($on)) {
            $on = 'a.' . $this->pk . ' = b.id';
        }
        return ' ' . $type . ' JOIN ' . $table . ' as b ON ' . $on;
    }
    
    protected function parseField()
    {
        if (empty($this->parts['field'])) {
            return '*';
        } else {
            if (is_string($this->parts['field'])) {
                $this->parts['field'] = explode(',', $this->parts['field']);
            }
            array_walk($this->parts['field'], [$this, 'parseKey']);
            return implode(',', $this->parts['field']);
        }
    }
    
    protected function parseTable()
    {
        if (empty($this->parts['table'])) {
            if ($this->tableName) {
                $table = $this->tableName;
            } else {
                trigger_error('必须设置表才可以进行此操作', E_USER_ERROR);
                return false;
            }
        } else {
            $table = strtolower(strpos($this->parts['table'], self::$_config['prefix']) === false) ? self::$_config['prefix'] . $this->parts['table'] : $this->parts['table'];
        }
        $table = $this->parseKey($table);
        //判断是否带数据库
        return ((empty($this->parts['db'])) ? $table : $this->parseKey($this->parts['db']) . '.' . $table);
    }
    
    protected function parseDistinct()
    {
        return $this->parts['distinct'] ? ' DISTINCT ' : '';
    }
    
    public function parseCount($method)
    {
        $this->parts['field'] = "{$method}({$this->parts['field']}) as kx_num";
        return $this->getField('kx_num');
    }
    
    public function start()
    {
        $this->master()->startTrans();
    }
    
    public function commit()
    {
        $this->master()->commit();
    }
    
    public function rollback()
    {
        $this->master()->rollback();
    }
    
    public function fetch($sql)
    {
        $this->errorinfo = ''; //清空存储
        $db              = $this->slave();
        $row             = $db->fetch($sql);;
        if ($row !== false) {
            if ($row) {
                return $row;
            } else {
                return null;
            }
        } else {
            $this->errorinfo = $db->errno() . ':' . $db->error();
            return false;
        }
    }
    
    public function fetchall($sql)
    {
        $this->errorinfo = ''; //清空存储
        $db              = $this->slave();
        $row             = $db->fetchAll($sql);;
        if ($row !== false) {
            if ($row) {
                return $row;
            } else {
                return null;
            }
        } else {
            $this->errorinfo = $db->errno() . ':' . $db->error();
            return false;
        }
    }
    
    public function query($sql)
    {
        $this->errorinfo = ''; //清空存储
        if (self::$_config['prefix'] != 'ptcms_' && strpos($sql, 'ptcms_')) {
            $sql = str_replace('ptcms_', self::$_config['prefix'], $sql);
        }
        $db  = $this->slave();
        $row = $db->query($sql);;
        if (!$db->error()) {
            if ($row || $row === 0) {
                return $row;
            } else {
                return null;
            }
        } else {
            $this->errorinfo = $db->errno() . ':' . $db->error();
            return false;
        }
    }
    
    public function execute($sql)
    {
        $this->errorinfo = ''; //清空存储
        if (self::$_config['prefix'] != 'ptcms_' && strpos($sql, 'ptcms_')) {
            $sql = str_replace('ptcms_', self::$_config['prefix'], $sql);
        }
        $db  = $this->master();
        $row = $db->execute($sql);;
        if (!$db->error()) {
            if ($row || $row === 0) {
                return $row;
            } else {
                return null;
            }
        } else {
            $this->errorinfo = $db->errno() . ':' . $db->error();
            return false;
        }
    }
}