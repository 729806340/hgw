<?php
/**
 * 会员店铺
 *
 *
 *
 * * @汉购网 (c) 2015-2018 ShopWWI Inc. (http://www.hangowa.com)
 * @license    http://www.shopwwi.c om
 * @link       交流群号：
 * @since      汉购网提供技术支持 授权请购买shopnc授权
 */



defined('ByShopWWI') or exit('Access Invalid!');

class show_chainControl extends BaseChainControl {
    public function __construct(){
        parent::__construct();
    }
    /**
     * 展示门店
     */
    public function indexOp() {
        $chain_id = intval($_GET['chain_id']);
        $chain_info = Model('chain')->getChainInfo(array('chain_id' => $chain_id));
        Tpl::output('chain_info', $chain_info);
        Tpl::showpage('show_chain');
    }
}
