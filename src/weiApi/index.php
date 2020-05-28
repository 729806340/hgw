<?php
/**
 * 手机接口初始化文件
 *
 *
 * @copyright  Copyright (c) 2007-2015 ShopNC Inc. (http://www.shopnc.net)
 * @license    http://www.shopnc.net
 * @link       http://www.shopnc.net
 * @since      File available since Release v1.1
 */
//error_reporting(E_ALL);
error_reporting(0);
define('APP_ID','mobile');
define('IGNORE_EXCEPTION', true);
define('BASE_PATH',str_replace('\\','/',dirname(__FILE__)));
require __DIR__ . '/../shopwwi.php';
define('MOBILE_RESOURCE_SITE_URL',MOBILE_SITE_URL.DS.'resource');

if (!is_null($_GET['key']) && !is_string($_GET['key'])) {
    $_GET['key'] = null;
}
if (!is_null($_POST['key']) && !is_string($_POST['key'])) {
    $_POST['key'] = null;
}
if (!is_null($_REQUEST['key']) && !is_string($_REQUEST['key'])) {
    $_REQUEST['key'] = null;
}


$method = explode('.', $_GET['method']);

$_GET['act'] = $method[0];
$_GET['op'] = $method[1];

define('app_debug' , true);   //生产环境请设置为flase
define('secret' ,'testhgwapi');
define('apikey','c1dca569396ba260fe6a7d552b6b7d75');

//框架扩展
require(BASE_PATH.'/framework/function/function.php');

if (!@include(BASE_PATH.'/control/control.php')) exit('control.php isn\'t exists!');

Base::run();