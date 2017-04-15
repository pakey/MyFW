<?php
return [
    'app' => [
        'debug' => true,
        'mode'   => PHP_SAPI == 'cli' ? 'cli' : 'web',
    ],
    
    'rewrite'=>[
        'power'=>0,
    ],
    
    'log' => [
        'power'     => true,
        'buildtype' => ['kx', 'debug', 'console'],
    ],
    
    'view' => [
        'driver' => 'mc',
    ],
    
    'cache' => [
        'prefix' => 'kx_',
        'common' => [
            'driver' => 'memcached',
            'option' => [
                'host' => '127.0.0.1',
                'port' => '11211',
            ],
        ],
        
        'other' => [
            'driver' => 'memcached',
            'option' => [
                'host' => '127.0.0.1',
                'port' => '11211',
            ],
        ],
        'redis' => [
            'driver' => 'redis',
            'option' => [
                'host'     => '127.0.0.1',
                'port'     => '6379',
                'password' => null,
                'database' => 0,
            ],
        ],
    ],
    
    'database' => [
        'prefix' => 'kx_',
        'common' => [
            'driver' => 'mysql',
            'option' => [
                'host' => '127.0.0.1',
                'port' => '3306',
                'user' => 'root',
                'pwd'  => 'root',
                'name' => 'kuxin',
            ],
        ],
    ],
    
    'storage' => [
        'runtime'  => [
            'driver' => 'file',
            'option' => [
                'path' => PT_ROOT . '/storage/runtime',
            ],
        ],
        'log'      => [
            'driver' => 'file',
            'option' => [
                'path' => PT_ROOT . '/storage/log',
            ],
        ],
        'template' => [
            'driver' => 'file',
            'option' => [
                'path' => PT_ROOT . '/storage/template',
            ],
        ],
        'upload'   => [
            'driver' => 'file',
            'option' => [
                'path' => PT_ROOT . '/public/upload',
                'url'  => '/public/upload',
            ],
        ],
        'cover'    => [
            'driver' => 'file',
            'option' => [
                'path' => PT_ROOT . '/public/cover',
                'url'  => '/public/cover',
            ],
        ],
        'ftp'      => [
            'driver' => 'ftp',
            'option' => [
                'host'     => '',
                'port'     => '',
                'username' => '',
                'password' => '',
                'path'     => '',
            ],
        ],
        'mongodb'  => [
            'driver' => 'mongodb',
            'option' => [
                'host'     => '',
                'port'     => '',
                'username' => '',
                'password' => '',
                'database' => '',
            ],
        ],
    ],
    
    'coookie' => [
        'prefix'   => 'PTCMS_',
        // cookie 保存时间
        'expire'   => 2592000,
        // cookie 保存路径
        'path'     => '/',
        // cookie 有效域名
        'domain'   => '',
        //  cookie 启用安全传输
        'secure'   => false,
        // httponly设置
        'httponly' => '',
    ],
    
    'session' => [
        'handler' => '',
        'path'    => '',
        'host'    => '',
        'port'    => '',
    ],

];