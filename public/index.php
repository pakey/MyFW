<?php
ini_set('display_errors', 'on');
error_reporting(E_ALL);

//yaconf
//yac
//swoole
$t=microtime(true);
define('PT_ROOT', dirname(__DIR__));

include PT_ROOT.'/kuxin/kuxin.php';

var_dump(get_included_files());
echo number_format(microtime(true) - $t, 5);
