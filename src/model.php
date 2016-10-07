<?php
namespace ptcms;

use \ptcms\driver\db\Medoo;
use PDO;

/**
 * todo 暂未考虑读写分离及多主多从
 * Class Model
 * @method static mixed query() query(string $sql)
 * @method mixed exec() exec(string $sql)
 * @method mixed select() select($join, $columns = null, $where = null)
 * @method mixed insert() insert(mixed $data)
 * @method mixed update() update($data, $where = null)
 * @method mixed delete() delete($where)
 * @method mixed replace() replace($columns, $search = null, $replace = null, $where = null)
 * @method mixed get() get($join, $columns = null, $where = null)
 * @method mixed has() has($join, $where = null)
 * @method int count() count($join = null, $column = null, $where = null)
 * @method int max() max($join = null, $column = null, $where = null)
 * @method int min() min($join = null, $column = null, $where = null)
 * @method int avg() avg($join = null, $column = null, $where = null)
 * @method int sum() sum($join = null, $column = null, $where = null)
 * @method mixed debug() debug()
 * @method string error() error()
 * @method string last_query() last_query()
 * @method array info() info()
 * @method mixed log() log()
 * @method mixed begin() begin()
 * @method mixed commit() commit()
 * @method mixed rollback() rollback()
 */
class Model {

    //表名
    protected $tablename;
    protected $full_tablename;
    //表前缀
    protected $prefix = '';
    //单例
    /**
     * @var Model
     */
    protected static $_instance;
    protected static $_db;
    protected static $config;
    //使用的数据库
    protected $setting = 'default';
    /**
     * @var Medoo
     */
    protected $handler;

    /**
     * 缓存时间
     *
     * @var int
     */
    public $cachetime = 600;

    public function __construct() {
        static::$config = Config::get('db');
        if (empty(static::$config[$this->setting])) {
            Response::error("数据库配置节点['{$this->setting}']不存在");
        }
        $this->prefix = static::$config[$this->setting]['prefix'];
        if ($this->tablename) {
            $this->setTable($this->tablename);
        }else{
            Response::error("请设置需要操作的数据库");
        }
        $this->cachetime = Config::get('cache.time');
        if (!static::$_instance instanceof static) {
            static::$_instance = $this;
        }
    }

    /**
     * getInstance
     *
     * @return static
     */
    public static function getInstance() {
        return Loader::instance(static::class);
    }

    /**
     * connect
     * 连接数据库
     *
     * @param $db
     * @param $config
     * @return Medoo
     */
    protected static function connect($db, $config) {
        if(empty($config['host'])){
            trigger_error('mysql config miss',E_USER_ERROR);
        }
        return static::$_db[$db] = new \ptcms\driver\db\Medoo([
            'database_type' => 'mysql',
            'database_name' => $config['name'],
            'server'        => $config['host'],
            'username'      => $config['user'],
            'password'      => $config['pwd'],
            'charset'       => 'utf8',
            'port'          => $config['port'],
            'option'        => [
                PDO::ATTR_CASE => PDO::CASE_NATURAL
            ]
        ]);
    }

    /**
     * setTable
     *
     * @param $name
     * @return $this
     */
    public function setTable($name) {
        $this->tablename      = strtolower($name);
        $this->full_tablename = $this->prefix?((strpos($name, $this->prefix) === 0) ? $this->tablename : $this->prefix . $this->tablename):$this->tablename;
        return $this;
    }

    /**
     * __call
     * 调用medoo的方法
     *
     * @param       $method
     * @param array $args
     * @return mixed
     */
    public function __call($method, $args = array()) {
        if (!$this->handler) {
            if(empty(static::$_db[$this->setting])){
                $this->connect($this->setting, self::$config[$this->setting]['master']);
            }
            $this->handler = static::$_db[$this->setting];
        }
        if (in_array($method, array('exec', 'error', 'last_query', 'info', 'log', 'begin', 'commit', 'rollback','fetch'))) {
            return call_user_func_array(array($this->handler, $method), $args);
        } elseif (in_array($method, array('select', 'get'))) {
            if (!empty($args['0']) && is_array($args['0'])) {
                // 当join模式的时候添加表前缀
                $join_key = array_keys($args['0']);
                if (isset($join_key['0']) && strpos($join_key['0'], '[') === 0) {
                    //是join
                    $new = $table = [];
                    foreach ($args['0'] as $sub_table => $relation) {
                        if (strpos($sub_table, ']' . $this->prefix) === false) {
                            $table[]   = substr($sub_table, strpos($sub_table, ']') + 1);
                            $sub_table = str_replace(']', ']' . $this->prefix, $sub_table);
                        }
                        $new[$sub_table] = $relation;
                    }
                    if ($table) {
                        $args['0'] = $new;
                        if (is_array($args['1'])) {
                            foreach ($args['1'] as $k => $field) {
                                foreach ($table as $t) {
                                    if (strpos($field, $t . '.') === 0) {
                                        $args['1'][$k] = $this->prefix . $field;
                                    }
                                }
                            }
                        }
                    }
                }
            }
            array_unshift($args, $this->full_tablename);
            return call_user_func_array(array($this->handler, $method), $args);
        } elseif (in_array($method, array('insert', 'update', 'delete', 'replace', 'has', 'count', 'max', 'min', 'sum', 'avg'))) {
            array_unshift($args, $this->full_tablename);
            return call_user_func_array(array($this->handler, $method), $args);
        } elseif ($method == "query") {
            $args['0'] = str_replace(['!@#_'], $this->prefix, $args['0']);
            return $this->handler->query($args['0']);
        } elseif ($method == "debug") {
            $this->handler->debug();
            return $this;
        } else {
            Response::error('Model 方法['.$method.']不存在');
        }
    }
}