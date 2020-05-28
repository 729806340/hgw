<?php
/**
 * Author: Shen.L
 * Email: shen@shenl.com
 * Date: 2016/7/21
 * Time: 11:25
 */


// 检测PHP环境
if(version_compare(PHP_VERSION,'5.3.0','<'))  die('require PHP > 5.3.0 !');
// 开启调试模式 建议开发阶段开启 部署阶段注释或者设为false
define('APP_DEBUG',True);
define('__APP__','/migrate.php');

// 定义应用目录
define('APP_PATH','./migrate/');

// 引入ThinkPHP入口文件
require './fenxiao/ThinkPHP/ThinkPHP.php';
