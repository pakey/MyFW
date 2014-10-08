<?php
return array(
    'super_user_id'=>1,
    'DB_MYSQL' => array(
        'DRIVER' => 'Pdo',
        'PREFIX' => 'ptcms_',
        'CHARSET' => 'utf8',
        'MASTER' => array(
            array(
                'HOST' => '10.9.1.188',
                'PORT' => '3306',
                'NAME' => 'cf_92aa4934_9075_4c43_8bc6_8c4a304f58b9',
                'USER' => 'C5M1OJxpIKbQVNlY',
                'PWD' => 'k31GzgvccXk5GITk'
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