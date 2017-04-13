<?php

ini_set('display_errors', 'on');
error_reporting(E_ALL);

$m = memory_get_usage();
$t = microtime(true);
define('PT_ROOT', dirname(__DIR__));

include PT_ROOT . '/kuxin/kuxin.php';

echo '<pre>';
var_dump(get_included_files());
var_dump(number_format(microtime(true) - $t, 5));
var_dump(number_format((memory_get_usage() - $m) / 1024 / 1024, 3));
