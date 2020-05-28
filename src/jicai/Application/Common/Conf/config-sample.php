<?php
return array(
    //'配置项'=>'配置值'
    'MODULE_ALLOW_LIST' => array('Home', 'Admin', 'User'), //设置模块分组
    'DEFAULT_MODULE' => 'Home', // 默认模块
    'URL_CASE_INSENSITIVE'  =>  true, //设置不区分大小写
    'URL_MODEL' => '2',

    //数据库配置信息
    'DB_TYPE'   => 'mysql', // 数据库类型
    'DB_HOST'   => '192.168.11.98', // 服务器地址
    'DB_NAME'   => 'hango_jicai', // 数据库名
    'DB_USER'   => 'hango', // 用户名
    'DB_PWD'    => 'hango#@!', // 密码
    'DB_PORT'   => 3306, // 端口
    'DB_PREFIX' => '', // 数据库表前缀
    'DB_CHARSET'=> 'utf8', // 字符集

    //模板选项
    'TMPL_TEMPLATE_SUFFIX' => '.php',

    'AUTOLOAD_NAMESPACE' => array('Lib'=> APP_PATH.'Lib',),

);
