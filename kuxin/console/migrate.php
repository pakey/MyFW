<?php

namespace Kuxin\Console;


use Kuxin\Console;
use Kuxin\DI;
use Kuxin\Loader;

class Migrate extends Console
{

    protected $path = KX_ROOT . '/app/migrate/';

    /**
     * @var \Kuxin\Db\Mysql
     */
    protected $db;

    public function init()
    {
        $this->db = DI::DB();
    }

    public function up()
    {
        if (false === $records = $this->db->fetchAll("select * from migrate")) {
            $res = $this->db->execute('CREATE TABLE `migrate` ( `name` varchar(180) NOT NULL,`time` int(11) DEFAULT NULL,PRIMARY KEY (`name`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;');
            if ($res) {
                $records = [];
            } else {
                return $this->info('初始化失败');
            }
        }
        $execed = array_column($records, 'name');
        $noExec = [];
        foreach (scandir($this->path) as $file) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            $name = substr($file, 0, -4);
            if (!in_array($name, $execed)) {
                $noExec[] = $name;
            }
        }
        if ($noExec) {
            $this->info('您将执行以下migrate');
            $this->info(PHP_EOL . implode(PHP_EOL, $noExec) . PHP_EOL);
            $input = $this->prompt('请输入 yes 来确认执行');
            if ($input == 'yes') {
                try {
                    foreach ($noExec as $name) {
                        $class = Loader::instance('App\\Migrate\\' . $name);
                        if (method_exists($class, 'up')) {
                            $class->up();
                        }
                        $this->db->execute("INSERT INTO `migrate` (`name`, `time`) VALUES ('{$name}', {$_SERVER['REQUEST_TIME']});");
                    }
                    $this->info(PHP_EOL . '本次命令执行成功', 'success');
                } catch (\Exception $e) {
                    $this->info(PHP_EOL . "执行失败: 文件[{$name}] " . $e->getMessage(), 'error');
                }
            } else {
                $this->info('您取消了本次命令执行', 'warning');
            }
        } else {
            $this->info('没有要执行的migrate', 'warning');
        }
    }

    public function down()
    {
        $maxRow = $this->db->fetch('select time from migrate order by time desc limit 1');
        if ($maxRow) {
            $maxTime = $maxRow['time'];
            $records = $this->db->fetchAll("select name from migrate where time={$maxTime}");
            $names   = array_column($records, 'name');
            $this->info('您将回滚以下migrate');
            $this->info(PHP_EOL . implode(PHP_EOL, $names) . PHP_EOL);
            $input = $this->prompt('请输入 yes 来确认执行');
            if ($input == 'yes') {
                try {
                    foreach ($names as $name) {
                        $class = Loader::instance('App\\Migrate\\' . $name);
                        if (method_exists($class, 'down')) {
                            $class->down();
                        }
                        $this->db->execute("DELETE FROM `migrate` where name='{$name}';");
                    }
                    $this->info(PHP_EOL . '本次命令执行成功', 'success');
                } catch (\Exception $e) {
                    $this->info(PHP_EOL . "执行失败: 文件[{$name}] " . $e->getMessage(), 'error');
                }
            } else {
                $this->info('您取消了本次命令执行', 'warning');
            }
        } else {
            $this->info('没有要执行的migrate', 'warning');
        }
    }

    public function create()
    {
        $tableName = trim($this->params['argv']['2']);
        if ($tableName == '') {
            return $this->info('please input migrate name', 'error');
        }

        $name      = str_replace([' ', ':', '/', '\\'], '_', $tableName);
        $filename  = 'kx_' . date('YmdHis_') . $name;
        $file      = $this->path . $filename . '.php';
        $classname = ucfirst($filename);
        $content   = <<<PHP
<?php

namespace App\Migrate;

use Kuxin\Db\Migrate;

class $classname extends Migrate
{
    // 执行修改
    public function up()
    {
        \$this->create('{$tableName}',function(){
            \$this->addComand("`id` int(10) unsigned NOT NULL AUTO_INCREMENT");
            \$this->addComand("PRIMARY KEY (`id`)");
        });
    }

    // 回滚修改
    public function down()
    {
        \$this->drop('{$tableName}');
    }
}
PHP;
        file_put_contents($file, $content);
        $this->info('创建migrate文件[ ' . $filename . ' ]成功', 'success');
    }
}