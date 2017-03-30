<?php
ini_set('display_errors', 'on');
error_reporting(E_ALL);

//yaconf
//yac
//swoole
$GLOBALS['t']=microtime(true);
define('PT_ROOT', dirname(__DIR__));

include PT_ROOT.'/kuxin/kuxin.php';
