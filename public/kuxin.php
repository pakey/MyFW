<?php
ini_set('display_errors', 'on');
error_reporting(E_ALL);

//yaconf
//yac
//swoole

$m=memory_get_usage();
$t=microtime(true);
define('PT_ROOT', dirname(__DIR__));

Kuxin\Kuxin::start();

echo '<pre>';
var_dump(get_included_files());
echo number_format(microtime(true) - $t, 5);
var_dump(number_format((memory_get_usage() - $m) / 1024 / 1024, 3));
