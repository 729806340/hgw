<?php
return array(
    //'配置项'=>'配置值'
    /**
    +-----------------------------------------------------------------------------------------
     *数据库配置
    +-----------------------------------------------------------------------------------------
     **/
    'DB_TYPE'   => 'mysql', // 数据库类型
    'DB_HOST'   => 'localhost', // 服务器地址
    'DB_NAME'   => 'shopnc_new', // 数据库名
    'DB_USER'   => 'dev', // 用户名
    'DB_PWD'    => '123456', // 密码
    'DB_PORT'   => '3306', // 端口
    'DB_PREFIX' => 'shopwwi_', // 数据库表前缀
    'DB_CONFIG1' => array(
        'db_type' => 'mysql',
        'db_user' => 'hango',
        'db_pwd' => 'hango#@!',
        'db_host' => '192.168.11.98',
        'db_port' => '3306',
        'db_name' => 'hango_b2c',
        'db_charset' => 'utf8',
    ),
    //数据库配置2
    'DB_CONFIG2' => 'mysql://dev:123456@localhost:3306/thinkphp#utf8',

    'URL_MODEL'=>0,
    'MULTI_MODULE'=>false,

    'ON_DEV'=>false,
);