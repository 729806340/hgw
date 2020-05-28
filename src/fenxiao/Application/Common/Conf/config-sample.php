<?php
//shopnc 常量
/**
 * 商品图片
 */
define('BASE_ROOT_PATH',str_replace('\\','/',dirname(dirname(__FILE__))));
define('BASE_CORE_PATH',BASE_ROOT_PATH.'/core');
define('BASE_DATA_PATH',BASE_ROOT_PATH.'/data');
define("BASE_UPLOAD_PATH", BASE_ROOT_PATH . "/data/upload");
define('GOODS_IMAGES_WIDTH', '60,240,360,1280');
define('GOODS_IMAGES_HEIGHT', '60,240,360,12800');
define('GOODS_IMAGES_EXT', '_60,_240,_360,_1280');
define('UPLOAD_SITE_URL','/data/upload');
define('ATTACH_GOODS','shop/store/goods');
define('ATTACH_COMMON','shop/common');
define('DS','/');
return array(
    //'配置项'=>'配置值'
    'MODULE_ALLOW_LIST' => array('Home', 'Admin', 'User'), //设置模块分组
    'DEFAULT_MODULE' => 'Home', // 默认模块
    'URL_CASE_INSENSITIVE'  =>  true, //设置不区分大小写
    'URL_MODEL' => '2',
    
    //数据库配置信息
//    'DB_TYPE'   => 'mysql', // 数据库类型
//    'DB_HOST'   => '61.183.247.80', // 服务器地址
//    'DB_NAME'   => 'distribution', // 数据库名
//    'DB_USER'   => 'root', // 用户名
//    'DB_PWD'    => 'hanmowa123', // 密码
//    'DB_PORT'   => 3306, // 端口
//    'DB_PREFIX' => 'hg_', // 数据库表前缀 
//    'DB_CHARSET'=> 'utf8', // 字符集
    
        //数据库配置信息
    'DB_TYPE'   => 'mysql', // 数据库类型
    'DB_HOST'   => '192.168.11.98', // 服务器地址
    'DB_NAME'   => 'hango_b2b2c', // 数据库名
    'DB_USER'   => 'hango', // 用户名
    'DB_PWD'    => 'hango#@!', // 密码
    'DB_PORT'   => 3306, // 端口
    'DB_PREFIX' => 'shopwwi_', // 数据库表前缀 
    'DB_CHARSET'=> 'utf8', // 字符集
    
    //模板选项
    'TMPL_TEMPLATE_SUFFIX' => '.php',
    
    'AUTOLOAD_NAMESPACE' => array('Lib'=> APP_PATH.'Lib',),
    
    'BASE_URL'=>'http://www2.hangowa.com/',
//    'BASE_URL'=>'http://localhost/hangou/index.php/',
//    'SHOW_PAGE_TRACE' =>true, //开启调试模式
    
//    'ACTION_SUFFIX' => 'Action',
);
