<?php
/**
 * 分销订单导入接口
 * @author lijingquan
 * @date 2016-07-20
 */
error_reporting(7);
$_GET['act']	= 'fxorder';
$_GET['op']		= 'index';
$_GET['payment_code'] = 'fenxiao';
require_once(dirname(__FILE__).'/../../../index.php');
?>