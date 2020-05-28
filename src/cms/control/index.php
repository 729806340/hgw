<?php
/**
 * cms首页
 *
 *
 * * @汉购网 (c) 2015-2018 ShopWWI Inc. (http://www.hangowa.com)
 * @license    http://www.shopwwi.c om
 * @link       交流群号：
 * @since      汉购网提供技术支持 授权请购买shopnc授权
 */



defined('ByShopWWI') or exit('Access Invalid!');
class indexControl extends CMSHomeControl{

    public function __construct() {
        parent::__construct();
        Tpl::output('index_sign','index');
    }
    public function indexOp(){
        Tpl::showpage('index');
    }
}
