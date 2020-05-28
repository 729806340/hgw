<?php
error_reporting(7);
$_GET['act']	= 'payment';
$_GET['op']		= 'bestpay_notify';
$_GET['payment_code'] = 'bestpay';
require_once(dirname(__FILE__).'/../../../index.php');
?>