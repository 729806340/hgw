<?php
/**
 * 手机端下载地址
 *
 *
 *
 * * @汉购网 (c) 2015-2018 ShopWWI Inc. (http://www.hangowa.com)
 * @license    http://www.shopwwi.c om
 * @link       交流群号：
 * @since      汉购网提供技术支持 授权请购买shopnc授权
 */



defined('ByShopWWI') or exit('Access Invalid!');
class mb_appControl extends BaseHomeControl {
    public function __construct() {
        parent::__construct();
    }
    /**
     * 下载地址
     *
     */
    public function indexOp() {
        $mobilebrowser_list ='iphone|ipad';
        if(preg_match("/$mobilebrowser_list/i", $_SERVER['HTTP_USER_AGENT'])) {
            @header('Location: '.C('mobile_ios'));exit;
        } else {
            @header('Location: '.C('mobile_apk'));exit;
        }
    }
}
