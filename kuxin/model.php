<?php

namespace Kuxin;

use Kuxin\Helper\Json;

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
    protected $table = null;
    
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
    protected $prefix = '';
    
    /**
     * SQL语句容器，用于存放SQL语句，为SQL语句组装函数提供SQL语句片段的存放空间。
     *
     * @var array
     */
    protected $data = [];
    
    /**
     * SQL语句容器，用于存放SQL语句，为SQL语句组装函数提供SQL语句片段的存放空间。
     *
     * @var array
     */
    protected $bindParams = [];
    
    /**
     * 错误信息
     *
     * @var string
     */
    protected $errorinfo = null;
    
    /**
     * @var \Kuxin\Db\Mysql
     */
    protected $db = null;
    
    public function __construct()
    {
        $this->prefix = Config::get('database.prefix');
    }
    
    /**
     * @return \Kuxin\Db\Mysql
     */
    public function db()
    {
        if (!$this->db) {
            $this->db = DI::DB();
        }
        return $this->db;
    }
    
    /**
     * 实例化 单例
     *
     * @return static
     */
    public static function I()
    {
        $class = static::class;
        return Loader::instance($class);
    }
    
    public function __call($method, $args)
    {
        trigger_error('不具备的Model操作' . $method);
    }
    
    public function sum($value = '*')
    {
        $this->data['field'] = "sum({$value}) as kx_num";
        return $this->getField('kx_num');
    }
    
    public function avg($value = '*')
    {
        $this->data['field'] = "avg({$value}) as kx_num";
        return $this->getField('kx_num');
    }
    
    public function min($value = '*')
    {
        $this->data['field'] = "min({$value}) as kx_num";
        return $this->getField('kx_num');
    }
    
    public function max($value = '*')
    {
        $this->data['field'] = "max({$value}) as kx_num";
        return $this->getField('kx_num');
    }
    
    public function count($value = '*')
    {
        $this->data['field'] = "count({$value}) as kx_num";
        return $this->getField('kx_num');
    }
    
    public function where($data, array $option = [])
    {
        if (is_string($data)) {
            $this->data['where'][] = ['_string' => $data];
            $this->bindParams      = array_merge($this->bindParams, $option);
        } elseif (is_array($data)) {
            foreach ($data as $field => $var) {
                //where条件
                $this->data['where'][] = [$field => $var];
            }
        }
        return $this;
    }
    
    public function database($value)
    {
        $this->data['database'] = $value;
        return $this;
    }
    
    public function config($value)
    {
        $this->data['config'] = $value;
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
    
    public function getTableName()
    {
        if (!$this->table) {
            trigger_error('请设置表名', E_USER_ERROR);
        }
        return $this->prefix . $this->table;
    }
    
    public function getTableField($tablename)
    {
        if (!$tablename) {
            trigger_error('您必须设置表名后才可以使用该方法');
        }
        return DI::Cache()->debugGet('tablefield_' . $tablename, function () use ($tablename) {
            $fields = [];
            if ($tableInfo = $this->db()->fetchAll("SHOW FIELDS FROM {$tablename}")) {
                foreach ($tableInfo as $v) {
                    if ($v['Key'] == 'PRI') $pks[] = strtolower($v['Field']);
                    $fields[strtolower($v['Field'])] = strpos($v['Type'], 'int') !== false;
                }
                DI::Cache()->set('tablefield_' . $tablename, $fields, Config::get('cache_time_m'));
                return $fields;
            } else {
                trigger_error('获取表' . $tablename . '信息发送错误 ', E_USER_ERROR);
                return false;
            }
        });
    }
    
    public function getPk()
    {
        return $this->pk;
    }
    
    /**
     * @param array $data
     * @param bool  $replace
     * @return mixed
     */
    public function insert(array $data, $replace = false)
    {
        if ($data) {
            $insertData = [];
            $tablename  = $this->parseTable();
            $fields     = $this->getTableField($tablename);
            foreach ($data as $k => $v) { // 过滤参数
                if (isset($fields[$k])) {
                    //写入数据
                    $insertData[$this->parseKey($k)] = ':' . $k;
                    //参数绑定
                    $this->bindParams[':' . $k] = $this->parseBindValue($v);
                }
            }
            $sql = ($replace ? 'REPLACE' : 'INSERT') . ' INTO ' . $tablename . ' (' . implode(',', array_keys($insertData)) . ') VALUES (' . implode(',', $insertData) . ');';
            
            $result = $this->db()->execute($sql, $this->bindParams);
            
            $this->free();
            
            if (true === $result) {
                return $this->db()->lastInsertId();
            } else {
                return false;
            }
        } else {
            trigger_error('你的数据呢？', E_USER_WARNING);
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
    public function insertAll(array $datas, $replace = false)
    {
        if ($datas) {
            $values    = [];
            $tablename = $this->parseTable();
            $fields    = $this->getTableField($tablename);
            foreach ($datas as $data) {
                $value = [];
                foreach ($data as $key => $val) {
                    if (in_array($key, $fields)) {
                        $value[$key] = $this->parseValue($val);
                    }
                }
                $values[] = '(' . implode(',', $value) . ')';
            }
            $fields = array_map([$this, 'parseKey'], array_keys($datas[0]));
            $sql    = ($replace ? 'REPLACE' : 'INSERT') . ' INTO ' . $this->parseTable() . ' (' . implode(',', $fields) . ')  VALUES ' . implode(',', $values);
            $result = $this->db()->execute($sql);
            $this->free();
            if (true === $result) {
                return $this->db()->lastInsertId();
            } else {
                return false;
            }
        } else {
            trigger_error('你的数据呢？', E_USER_WARNING);
            return false;
        }
    }
    
    
    /**
     * @param array $data
     * @return bool|int
     */
    public function update(array $data)
    {
        if ($data) {
            $sets      = [];
            $tablename = $this->parseTable();
            $fields    = $this->getTableField($tablename);
            if (isset($data[$this->pk])) {
                $this->where([$this->pk => $data[$this->pk]]);
                unset($data[$this->pk]);
            }
            foreach ($data as $k => $v) { // 数据解析
                if (in_array($k, $fields)) {
                    $sets[] = $this->parseKey($k) . '= :' . $k;
                    //参数绑定
                    $this->bindParams[':' . $k] = $this->parseBindValue($v);
                }
            }
            $sql = 'UPDATE ' . $this->parseTable() . ' SET ' . implode(',', $sets)
                . $this->parseWhere()
                . $this->parseOrder()
                . $this->parseLimit();
            
            $result = $this->db()->execute($sql, $this->bindParams);
            
            $this->free();
            
            if (true === $result) {
                return $this->db()->rowCount();
            } else {
                return false;
            }
        } else {
            trigger_error('你的数据呢？', E_USER_WARNING);
            return false;
        }
    }
    
    public function delete()
    {
        $sql = 'DELETE' . ' FROM ' . $this->parseTable()
            . $this->parseWhere()
            . $this->parseOrder()
            . $this->parseLimit();
        var_dump($sql, $this->bindParams);
        $this->data = [];
        $result     = $this->db()->execute($sql, $this->bindParams);
        
        $this->free();
        
        if (true === $result) {
            return $this->db()->rowCount();
        } else {
            return false;
        }
    }
    
    public function find($id = null)
    {
        if ($id) {
            $this->data['where'][] = [$this->pk => $id];
        }
        $this->data['limit'] = 1;
        $sql                 = "SELECT " . $this->parseField() . ' FROM '
            . $this->parseTable()
            . $this->parseJoin()
            . $this->parseWhere()
            . $this->parseGroup()
            . $this->parseHaving()
            . $this->parseOrder()
            . $this->parseLimit()
            . $this->parseUnion();
        //清空存储
        $this->data = [];
        return $this->fetch($sql, $this->bindParams);
    }
    
    /**
     * @return array|bool
     */
    public function select()
    {
        $sql             = "SELECT " . $this->parseField() . ' FROM '
            . $this->parseTable()
            . $this->parseJoin()
            . $this->parseWhere()
            . $this->parseGroup()
            . $this->parseHaving()
            . $this->parseOrder()
            . $this->parseLimit()
            . $this->parseUnion();
        $this->data      = [];
        $this->errorinfo = ''; //清空存储
        return $this->fetchAll($sql, $this->bindParams);
    }
    
    /**
     * 获取具体字段的值
     *
     * @param      $field
     * @param bool $multi 是否返回数组
     * @return mixed|null|string
     */
    public function getField($field, $multi = false)
    {
        if (empty($this->data['field'])) {
            $this->data['field'] = $field;
        }
        if ($multi) {
            $result = $this->select();
            if ($result === false) {
                return false;
            } elseif ($result) {
                if (strpos($field, ',')) {
                    $field = explode(',', $field, 2)[0];
                    return array_column($result, null, $field);
                } else {
                    return array_column($result, $field);
                }
            } else {
                return null;
            }
        } else {
            $result = $this->find();
            if ($result === false) {
                return false;
            } elseif (isset($result[$field])) {
                return $result[$field];
            } else {
                return null;
            }
        }
    }
    
    /**
     * 设置某个字段的值
     *
     * @param $field
     * @param $data
     * @return bool|int
     */
    public function setField($field, $data)
    {
        return $this->update([$field => $data]);
    }
    
    /**
     * 增加数据库中某个字段值
     *
     * @param     $field
     * @param int $step
     */
    public function setInc($field, $step = 1)
    {
        $this->setField($field, ['exp', "{$field} + {$step}"]);
    }
    
    /**
     * 减少数据库中某个字段值
     *
     * @param     $field
     * @param int $step
     */
    public function setDec($field, $step = 1)
    {
        $this->setField($field, ['exp', "{$field} - {$step}"]);
    }
    
    /**
     * 字段和表名处理添加`
     *
     * @access protected
     * @param string $key
     * @return string
     */
    protected function parseKey($key)
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
    protected function parseBindValue($value)
    {
        if (isset($value[0]) && is_string($value[0]) && strtolower($value[0]) == 'exp') {
            $value = $value[1];
        } elseif (is_array($value)) {
            $value = Json::encode($value);
        } elseif (is_bool($value)) {
            $value = $value ? 1 : 0;
        } elseif (is_null($value)) {
            $value = null;
        }
        return $value;
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
            $value = $this->db()->quote($value);
        }
        if (isset($value[0]) && is_string($value[0]) && strtolower($value[0]) == 'exp') {
            $value = $value[1];
        } elseif (is_array($value)) {
            $value = $this->db()->quote(Json::encode($value));
        } elseif (is_bool($value)) {
            $value = $value ? '1' : '0';
        } elseif (is_null($value)) {
            $value = 'null';
        }
        return $value;
    }
    
    public function getLastSql()
    {
        return $this->db()->lastSql();
    }
    
    public function getError()
    {
        return $this->db()->errorInfo();
    }
    
    protected function parseWhere()
    {
        if (empty($this->data['where'])) {
            return ' WHERE 1';
        } else {
            return ' WHERE ' . $this->parseWhereCondition($this->data['where']);
        }
    }
    
    protected function parseWhereCondition($condition, $logic = 'AND')
    {
        $wheres    = [];
        $tablename = $this->parseTable();
        $fields    = $this->getTableField($tablename);
        foreach ($condition as $var) {
            $k = key($var);
            $v = current($var);
            if (isset($fields[$k])) {
                if (empty($this->data['join'])) {
                    $wheres[] = '(' . $this->parseWhereItem($k, $v) . ')';
                } else {
                    $wheres[] = '(' . $this->parseWhereItem($this->parseKey($tablename) . '.' . $k, $v) . ')';
                }
            } elseif (is_array($v) && in_array(strtolower($k), ['or', 'and', 'xor'])) {
                $where[] = $this->parseWhereCondition($v, $k);
            } elseif ($k == '_logic' && in_array(strtolower($v), ['or', 'and', 'xor'])) {
                $logic = ' ' . strtoupper($v) . ' ';
            } elseif ($k == '_string') {
                $wheres[] = '(' . $v . ')';
            } else {
            }
        }
        return ($wheres === []) ? 1 : implode(" {$logic} ", $wheres);
    }
    
    /**
     * @param $field
     * @param $var
     * @return mixed
     */
    protected function parseWhereItem($field, $var)
    {
        //参数绑定key
        $bindkey = ':' . $field . '_' . count($this->bindParams);
        $field   = $this->parseKey($field);
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
                    //参数绑定存储
                    $this->bindParams[$bindkey] = $this->parseBindValue($var['1']);
                    return $field . ' ' . $var['0'] . ' ' . $bindkey;
                case 'in':
                case 'not in':
                    if (empty($var['1'])) return '1';
                    if (is_array($var['1'])) {
                        $inBindVar = [];
                        foreach ($var['1'] as $num => $inval) {
                            $inBindKey   = $bindkey . '_' . $num;
                            $inBindVar[] = $inBindKey;
                            //
                            $this->bindParams[$inBindKey] = $this->parseBindValue($inval);
                        }
                        $var['1'] = implode(',', $inBindVar);
                    }
                    return "{$field} {$var['0']} ( {$var['1']} )";
                case 'between':
                case 'not between':
                    if (is_string($var['1'])) {
                        $var['1'] = explode(',', $var['1']);
                    }
                    $this->bindParams[$bindkey . '_0'] = $this->parseBindValue($var['1']['0']);
                    $this->bindParams[$bindkey . '_1'] = $this->parseBindValue($var['1']['1']);
                    return "{$field} {$var['0']} {$bindkey}_0 and {$bindkey}_1";
                case 'exp':
                    return "{$var['1']}";
                default:
                    return '1';
            }
        } else {
            //参数绑定存储
            $this->bindParams[$bindkey] = $this->parseBindValue($var);
            return $field . ' = ' . $bindkey;
        }
    }
    
    protected function parseOrder()
    {
        if (!empty($this->data['order'])) {
            if (is_string($this->data['order'])) {
                return ' ORDER BY ' . $this->data['order'];
            }
        }
        return '';
    }
    
    protected function parseGroup()
    {
        if (!empty($this->data['group'])) {
            if (is_string($this->data['group'])) {
                return ' GROUP BY ' . $this->parseKey($this->data['group']);
            } elseif (is_array($this->data['group'])) {
                array_walk($this->data['group'], [$this, 'parseKey']);
                return ' GROUP BY ' . implode(',', $this->data['group']);
            }
        }
        return '';
    }
    
    protected function parseHaving()
    {
        if (empty($this->data['having'])) return '';
        return ' HAVING ' . $this->parseWhereCondition($this->data['having']);
    }
    
    protected function parseLimit()
    {
        if (isset($this->data['page'])) {
            // 根据页数计算limit
            if (strpos($this->data['page'], ',')) {
                list($page, $listRows) = explode(',', $this->data['page']);
            } else {
                $page = $this->data['page'];
            }
            $page     = $page ? $page : 1;
            $listRows = isset($listRows) ? $listRows : (is_numeric($this->data['limit']) ? $this->data['limit'] : 20);
            $offset   = $listRows * ((int)$page - 1);
            return ' LIMIT ' . $offset . ',' . $listRows;
        } elseif (!empty($this->data['limit'])) {
            return ' LIMIT ' . $this->data['limit'];
        } else {
            return '';
        }
    }
    
    protected function parseUnion()
    {
        
    }
    
    protected function parseJoin()
    {
        if (empty($this->data['join'])) return '';
        $table = $this->data['join']['table'];
        $type  = $this->data['join']['type'];
        $on    = $this->data['join']['on'];
        if (empty($table)) {
            return '';
        } elseif (strpos($table, $this->prefix) === false) {
            $table = $this->prefix . $table;
        }
        if (empty($on)) {
            $on = 'a.' . $this->pk . ' = b.id';
        }
        return ' ' . $type . ' JOIN ' . $table . ' as b ON ' . $on;
    }
    
    protected function parseField()
    {
        if (empty($this->data['field'])) {
            return '*';
        } else {
            if (is_string($this->data['field'])) {
                $this->data['field'] = explode(',', $this->data['field']);
            }
            array_walk($this->data['field'], [$this, 'parseKey']);
            return implode(',', $this->data['field']);
        }
    }
    
    protected function parseTable()
    {
        if (empty($this->data['table'])) {
            return $this->getTableName();
        } else {
            $table = strtolower(strpos($this->data['table'], $this->prefix) === false) ? $this->prefix . $this->data['table'] : $this->data['table'];
        }
        $table = $this->parseKey($table);
        //判断是否带数据库
        return ((empty($this->data['database'])) ? $table : $this->parseKey($this->data['db']) . '.' . $table);
    }
    
    protected function parseDistinct()
    {
        return $this->data['distinct'] ? ' DISTINCT ' : '';
    }
    
    public function parseCount($method)
    {
        $this->data['field'] = "{$method}({$this->data['field']}) as kx_num";
        return $this->getField('kx_num');
    }
    
    public function startTrans()
    {
        $this->db()->startTrans();
    }
    
    public function commit()
    {
        $this->db()->commit();
    }
    
    public function rollback()
    {
        $this->db()->rollback();
    }
    
    public function fetch($sql, $bindParams = [])
    {
        $result = $this->db()->fetch($sql, $bindParams);
        $this->free();
        return $result;
    }
    
    public function fetchAll($sql, $bindParams = [])
    {
        $result = $this->db()->fetchAll($sql, $bindParams);
        $this->free();
        return $result;
    }
    
    public function execute($sql, $bindParams = [])
    {
        $result = $this->db()->execute($sql, $bindParams);
        $this->free();
        return $result;
    }
    
    protected function free()
    {
        $this->data       = [];
        $this->bindParams = [];
    }
}