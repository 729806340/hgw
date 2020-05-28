<?php
/**
 * 队列
 *
 *
 * @汉购网提供技术支持 授权请购买shopnc授权
 * @license    http://www.hangowa.com
 * @link       交流群号：
 */

if (!empty($_GET['act'])) {
	$_SERVER['argv'][1] = $_GET['act'];
	@$_SERVER['argv'][2] = $_GET['op'];
}

if (empty($_SERVER['argv'][1])) exit('Access Invalid!');

define('APP_ID','crontab');
define('BASE_PATH',str_replace('\\','/',dirname(__FILE__)));
define('TRANS_MASTER',true);
require __DIR__ . '/../shopwwi.php';

if (PHP_SAPI == 'cli') {

    if(isset($_SERVER['argv'][3])||!empty($_SERVER['argv'][3])) {
        parse_str($_SERVER['argv'][3],$_GET);
    }else{
        $_GET = array();
    }
     $_GET['act'] = $_SERVER['argv'][1];
     $_GET['op'] = empty($_SERVER['argv'][2]) ? 'index' : $_SERVER['argv'][2];
}
if (!@include(BASE_PATH.'/control/control.php')) exit('control.php isn\'t exists!');
Base::run();
