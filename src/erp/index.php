<?php
/**
 *
 *
 * @汉购网提供技术支持 授权请购买shopnc授权
 * @license    http://www.hangowa.com
 * @link       交流群号：
 */
define('APP_ID', 'erp');
define('BASE_PATH',str_replace('\\','/',dirname(__FILE__)));

require __DIR__ . '/../shopwwi.php';

define('ERP_TEMPLATES_URL', ERP_SITE_URL.'/templates/'.TPL_MEMBER_NAME);

if (!@include(BASE_PATH.'/control/control.php')) exit('control.php isn\'t exists!');
Base::run();
