<?php
/**
 * 网银在线自动对账文件
 *
 * * @汉购网 (c) 2015-2018 ShopWWI Inc. (http://www.hangowa.com)
 * @license    http://www.shopwwi.c om
 * @link       交流群号：
 * @since      汉购网提供技术支持 授权请购买shopnc授权
 */
error_reporting(7);
$_GET['act']	= 'payment';
$_GET['op']		= 'notify';
$_GET['payment_code'] = 'chinabank';

//赋值，方便后面合并使用支付宝验证方法
$_POST['out_trade_no'] = $_POST['v_oid'];
$_POST['extra_common_param'] = $_POST['remark1'];
$_POST['trade_no'] = $_POST['v_idx'];

require_once(dirname(__FILE__).'/../../../index.php');
?>