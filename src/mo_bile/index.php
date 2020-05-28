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
error_reporting(0);
define('APP_ID','mobile');
define('IGNORE_EXCEPTION', true);
define('BASE_PATH',str_replace('\\','/',dirname(__FILE__)));

require __DIR__ . '/../data/service/PageCache.php';

/** @var PageCacheService $pageCache */
$pageCache = new PageCacheService();
$pageCache->setRedisPrefix('mob_page_');
//$pageCache->setRedis(array()); //若需要修改Redis配置可以使用此方法
$pageCache->setAllowed(array(
    /** 允许缓存操作列表，格式为ctl/op（动态模式）或者uri（伪静态模式），*为通配符 */
    'index/*',
    'goods/index',
    'item/*',
	//'store/*', //基本都带有post参数，先不加缓存
    'goods/goods_detail',
    'goods/goods_body',
    'goods/goods_evaluate',
    'goods_class/*',
    'brand/recommend_list',
));
$GLOBALS['pageCache'] = $pageCache;
$pageCacheContent = $pageCache->get();
if($pageCacheContent) exit($pageCacheContent);
register_shutdown_function(function (){
    $pageCache= $GLOBALS['pageCache'];
    $pageCache->save();
});

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

//框架扩展
require(BASE_PATH.'/framework/function/function.php');

if (!@include(BASE_PATH.'/control/control.php')) exit('control.php isn\'t exists!');

Base::run();

