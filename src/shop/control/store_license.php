<?php
/**
 * 店铺营业执照
 *
 *
 *
 * * @汉购网 (c) 2015-2018 ShopWWI Inc. (http://www.hangowa.com)
 * @license    http://www.shopwwi.c om
 * @link       交流群号：
 * @since      汉购网提供技术支持 授权请购买shopnc授权
 */



defined('ByShopWWI') or exit('Access Invalid!');

class store_licenseControl extends BaseHomeControl {
    public function __construct(){
        parent::__construct();
    }
    public function indexOp(){
        $store_id = intval($_GET['store_id']);

        if ($_POST){
            if (!checkSeccode($_POST['nchash'], $_POST['captcha'])) {
                setNcCookie('seccode' . $_POST['nchash'], '', -3600);
                showDialog('验证码错误!');
            }
            setNcCookie('seccode' . $_POST['nchash'], '', -3600);
            $store_id = intval($_POST['store_id']);
            $store_info = Model('store')->getStoreInfo(array('store_id' => $store_id));
            if (empty($store_info)) {
                showDialog('没有该店铺的记录或已关店!');
            }
            $store_joinin = Model('store_joinin')->getOne(array('member_id' => $store_info['member_id']));
            if (empty($store_joinin)) {
                showDialog('没有该店铺的记录或已关店!');
            }
            if (empty($store_joinin)) {
                showDialog('没有该店铺的记录或已关店!');
            }
            Tpl::output('store_joinin',$store_joinin);
            Tpl::showpage('store_license.view');
        }

        Tpl::output('store_id',$store_id);
        Tpl::showpage('store_license');
    }
}
