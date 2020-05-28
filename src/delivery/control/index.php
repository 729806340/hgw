<?php
/**
 * 物流自提服务站首页
 *
 * * @汉购网 (c) 2015-2018 ShopWWI Inc. (http://www.hangowa.com)
 * @license    http://www.shopwwi.c om
 * @link       交流群号：
 * @since      汉购网提供技术支持 授权请购买shopnc授权
 */



defined('ByShopWWI') or exit('Access Invalid!');

class indexControl extends BaseDeliveryControl{
    public function __construct(){
        parent::__construct();
        @header('location: index.php?act=login');die;
    }
}
