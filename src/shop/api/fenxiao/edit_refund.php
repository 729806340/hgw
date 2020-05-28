<?php
/**
 *退款退货接口-主要实现分销售后和电话售后接口功能
 *@author ljq
 *@date 2016-8-1
 */
error_reporting(7);
$_GET['act']	= 'api_refund_edit';
$_GET['op']		= 'index';
require_once(dirname(__FILE__).'/../../../index.php');