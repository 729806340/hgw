<?php
/**
 * 会员中心——账户概览
 *
 *
 *
 * * @汉购网 (c) 2015-2018 ShopWWI Inc. (http://www.hangowa.com)
 * @license    http://www.shopwwi.c om
 * @link       交流群号：
 * @since      汉购网提供技术支持 授权请购买shopnc授权
 */



defined('ByShopWWI') or exit('Access Invalid!');

class memberControl extends BaseMemberControl{

    /**
     * 采购商中心
     */
    public function homeOp() {
        $model_consume = Model('consume');
        $consume_list = $model_consume->getConsumeList(array('member_id' => $_SESSION['member_id']), '*', 0, 10);

        //采购商信息
        $condition = array();
        $condition['member_id'] = $_SESSION['member_id'];
        $purchaser_info = Model('b2b_purchaser')->getAddressInfo($condition);

        if (empty($purchaser_info)){
             showMessage('您不是采购商','index.php','html','error');
         }

        Tpl::output('purchaser_info', $purchaser_info);
        Tpl::output('consume_list', $consume_list);
        Tpl::showpage('member_home');
    }
}
