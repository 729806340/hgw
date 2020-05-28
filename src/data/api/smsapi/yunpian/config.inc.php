<?php
defined('ByShopWWI') or exit('Access Invalid!');
/*
 * 配置文件
 */
$options = array();
$options['apikey'] = C('shopwwi_sms_key'); //apikey
$options['signature'] =  C('shopwwi_sms_signature'); //签名
return $options;
?>