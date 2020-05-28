<?php
/**
 * 店铺卖家注销
 *
 *
 *
 * * @汉购网 (c) 2015-2018 ShopWWI Inc. (http://www.hangowa.com)
 * @license    http://www.shopwwi.c om
 * @link       交流群号：
 * @since      汉购网提供技术支持 授权请购买shopnc授权
 */



defined('ByShopWWI') or exit('Access Invalid!');

class seller_logoutControl extends BaseSellerControl {

    public function __construct() {
        parent::__construct();
    }

    public function indexOp() {
        $this->logoutOp();
    }

    public function logoutOp() {
        $this->recordSellerLog('注销成功');
        // 清除店铺消息数量缓存
        setNcCookie('storemsgnewnum'.$_SESSION['seller_id'],0,-3600);
        session_destroy();
        redirect('index.php?act=seller_login');
    }

}
