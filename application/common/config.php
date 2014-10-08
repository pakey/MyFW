<?php
return array(
    'DB_MYSQL' => array(
        'DRIVER' => 'Pdo',
        'PREFIX' => 'ptcms_',
        'CHARSET' => 'utf8',
        'MASTER' => array(
            array(
                'HOST' => '127.0.0.1',
                'PORT' => '3306',
                'NAME' => 'ptcms',
                'USER' => 'root',
                'PWD' => 'root'
            )
        ),
        'SLAVE' => array(
        ),
    ),


    'URL_RULES' => array(
        'index.article.list' => '/{dir}[/{key}][/{page}]',
    ),

    'URL_ROUTER' => array(
        '^(news|course)$' => 'index/article/list?module',
    ),

);