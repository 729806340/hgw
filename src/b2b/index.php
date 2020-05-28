<?php
/**
 * 商城板块初始化文件
 *
 *
 *
 * @汉购网提供技术支持 授权请购买shopnc授权
 * @license    http://www.hangowa.com
 * @link       交流群号：
 */
define('APP_ID','shop');
define('BASE_PATH',str_replace('\\','/',dirname(__FILE__)));
require __DIR__ . '/../data/service/PageCache.php';



$agent = empty($_SERVER['HTTP_USER_AGENT']) ? '' : $_SERVER['HTTP_USER_AGENT'];
$request_url = $_SERVER['REQUEST_URI'];

if(strpos($agent,"comFront") || strpos($agent,"iPhone") || strpos($agent,"MIDP-2.0") || strpos($agent,"Opera Mini") || strpos($agent,"UCWEB") || strpos($agent,"Android") || strpos($agent,"Windows CE") || strpos($agent,"SymbianOS"))
{
    if(!strpos($request_url,"act=goods&op=index") && !strpos($request_url,"item")){
        header("Location:/wap/");
    }
}


/** @var PageCacheService $pageCache */
//$pageCache = new PageCacheService();
//$pageCacheContent = $pageCache->get();
//if($pageCacheContent) exit($pageCacheContent);

require __DIR__ . '/../shopwwi.php';
//$wapurl = WAP_SITE_URL;

define('APP_SITE_URL',B2B_SITE_URL);
define('TPL_NAME',TPL_B2B_NAME);
define('MEMBER_TEMPLATES_URL', MEMBER_SITE_URL.'/templates/'.TPL_MEMBER_NAME);
define('B2B_RESOURCE_SITE_URL',B2B_SITE_URL.DS.'resource');
define('B2B_TEMPLATES_URL',B2B_SITE_URL.'/templates/'.TPL_NAME);
define('B2B_CONGREGATE_URL',B2B_TEMPLATES_URL.'/images/shop/congregate');
define('BASE_TPL_PATH',BASE_PATH.'/templates/'.TPL_NAME);
if (!@include(BASE_PATH.'/control/control.php')) exit('control.php isn\'t exists!');
Base::run();
//$pageCache->save();
