<?php
/**
 * 数据同步接口
 *
 * @copyright  Copyright (c) 2007-2015 ShopNC Inc. (http://www.shopnc.net)
 * @license    http://www.shopnc.net
 * @link       http://www.shopnc.net
 * @since      File available since Release v1.1
 */
define('BASE_PATH',str_replace('\\','/',dirname(__FILE__)));
require __DIR__ . '/../shopwwi.php';
require BASE_PATH . '/framework/function/function.php'; // 框架扩展
require BASE_PATH . '/control/control.php'; // 框架扩展
Base::run();