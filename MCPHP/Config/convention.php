<?php
return array(
    // 目录安全文件
    'BUILD_DIR_SECURE' => true, //是否创建目录安全文件
    'DIR_SECURE_FILENAME' => 'index.html', //目录安全文件名 支持多个 逗号分割
    'DIR_SECURE_CONTENT' => 'MCPHP Warning:Permission denied!', //目录安全文件内容
    // 多模块
    'APP_MODULE_MODE' => 1, // 多模块模式 0不启用 1普通分组 2独立分组
    'APP_MODULE_LIST' => 'Home,Admin', // 项目多模块设定,多个模块之间用逗号分隔,例如'Home,Admin'
    // url
    'URL_MODE' => 0, // URL访问模式,可选参数0、1：
    // 0 (正常模式); 1 (兼容模式)?s=a/b/c
    'URL_PATHINFO_DEPR' => '/', // PATHINFO的各参数之间的分割符号
    'URL_PATHINFO_FETCH' => 'ORIG_PATH_INFO,REDIRECT_PATH_INFO,REDIRECT_URL', // 用于兼容判断PATH_INFO 参数的SERVER替代变量列表
    'URL_ROUTER_MODE' => 0, //路由开关
    'URL_404_PAGE' => '', //404页面

    // 默认值
    'DEFAULT_MODULE' => 'Home', // 默认模块
    'DEFAULT_CONTROLLER' => 'Index', // 默认控制器
    'DEFAULT_ACTION' => 'index', // 默认方法
    'DEFAULT_THEME' => 'default', // 默认模版

    /* Cookie设置 */
    'COOKIE_EXPIRE' => 2592000, // Coodie有效期
    'COOKIE_DOMAIN' => '', // Cookie有效域名
    'COOKIE_PATH' => '/', // Cookie路径
    'COOKIE_PREFIX' => 'MCPHP_', // Cookie前缀 避免冲突

    //模版
    'TPL_DRIVER' => 'Mc', //模版引擎
    'TPL_SUFFIX' => '.html', //模版后缀
    'TPL_FILEDEPR' => '_', //模版分隔符
    'TPL_DETECT_THEME' => true, //自动侦测主题
    'TPL_PARSE_STRING' => array(), //自定义模版替换
    'OUTPUT_ENCODE'=>false,    //开启gzip输出
    'TPL_ACTION_SUCCESS'=>MC_PATH.'Tpl/Success.tpl',
    'TPL_ACTION_ERROR'=>MC_PATH.'Tpl/Error.tpl',
    'TPL_L_DELIM'=>'{',
    'TPL_R_DELIM'=>'}',

    // 缓存配置
    'DATA_CACHE_TIME' => 0, // 数据缓存有效期 0表示永久缓存
    'DATA_CACHE_COMPRESS' => false, // 数据缓存是否压缩缓存
    'DATA_CACHE_CHECK' => false, // 数据缓存是否校验缓存
    'DATA_CACHE_PREFIX' => '', // 缓存前缀
    'DATA_CACHE_TYPE' => 'File', // 数据缓存类型,支持:File|Db|Apc|Memcache|Shmop|Sqlite|Xcache|Apachenote|Eaccelerator
    'DATA_CACHE_PATH' => CACHE_PATH . 'data/cache/', // 缓存路径设置 (仅对File方式缓存有效)
    'DATA_CACHE_SUBDIR' => false, // 使用子目录缓存 (自动根据缓存标识的哈希创建子目录)
    'DATA_PATH_LEVEL' => 1, // 子目录缓存级别

    /* 错误设置 */
    'ERROR_MESSAGE' => '您浏览的页面暂时发生了错误！请稍后再试～', //错误显示信息,非调试模式有效
    'ERROR_PAGE' => '', // 错误定向页面
    'SHOW_ERROR_MSG' => false, // 显示错误信息

    'RUNTIME_INFO' => 'Power by MCPHP from PTcms.com. Processed in {time}(s), Memory usage: {mem}KB, SQL Query: {sql}, Include File:{file}.', //runtime信息显示 time时间 mem内存 file引入文件 sql查询次数

    'MEMCACHE_SERVER' => array(
        array("127.0.0.1", '11211'),
    ),


    /* 数据库设置 */
    'DB_TYPE'               => 'mysql',     // 数据库类型
    'DB_HOST'               => 'localhost', // 服务器地址
    'DB_NAME'               => 'test',          // 数据库名
    'DB_USER'               => 'root',      // 用户名
    'DB_PWD'                => '',          // 密码
    'DB_PORT'               => '3306',        // 端口
    'DB_PREFIX'             => 'mc_',    // 数据库表前缀
    'DB_MULTI'              => false, //是否启用多库
    'DB_FIELDTYPE_CHECK'    => false,       // 是否进行字段类型检查
    'DB_FIELDS_CACHE'       => true,        // 启用字段缓存
    'DB_DEPLOY_TYPE'        => 0, // 数据库部署方式:0 集中式(单一服务器),1 分布式(主从服务器)
    'DB_RW_SEPARATE'        => false,       // 数据库读写是否分离 主从式有效
    'DB_MASTER_NUM'         => 1, // 读写分离后 主服务器数量
    'DB_SLAVE_NO'           => '', // 指定从服务器序号
    'DB_SQL_BUILD_CACHE'    => false, // 数据库查询的SQL创建缓存
    'DB_SQL_BUILD_QUEUE'    => 'file',   // SQL缓存队列的缓存方式 支持 file xcache和apc
    'DB_SQL_BUILD_LENGTH'   => 20, // SQL缓存的队列长度
    'DB_SQL_LOG'            => false, // SQL执行日志记录
);