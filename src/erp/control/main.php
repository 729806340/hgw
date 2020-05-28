<?php
/**
 * 小能客服erp
 */



defined('ByShopWWI') or exit('Access Invalid!');

class mainControl extends BaseLoginControl {

    private $application = array('hango', 'nongmao');
    private $route = array('order', 'goods', 'refund');
//    private $param = array('order_id', 'goods_id', 'refund_id');

    public function __construct(){
        parent::__construct();
        Tpl::setLayout('erp');
    }

    /**
    * http://www.test.hangowa.com/erp/index.php?c=main
    siteid=###SITEID###
    uid=###UID###
    uname=###UNAME###
    kfid=###KFUID###&kf
    token=###KFTOKEN###
    sessionid=###SESSIONID###
    erpparam=###ERPPARAM### (应用名称（hango|nongmao）:路由（order|goods|refund）:参数名称（order_id|goods_id|refund_id）:参数值)
    settingid=###SETTINGID###
    erpparam2=###ERPPARAM2###
    devicetype=###DEVICETY
     */
    public function indexOp()
    {
        $erpparam = $_GET['erpparam'];
        $uid = intval($_GET['uid']);
        $erpparam = explode(':', $erpparam);
        if (!is_array($erpparam) || !isset($erpparam[0]) || !isset($erpparam[1]) || !isset($erpparam[2])) {
            die('<h1 style="padding-top: 30px;padding-left: 30px;">加载失败1！</h1>');
        }
        if (!in_array($erpparam[0], $this->application) || !in_array($erpparam[1], $this->route) || empty($erpparam[2])) {
            die('<h1 style="padding-top: 30px;padding-left: 30px;">加载失败2！</h1>');
        }
        return $this->$erpparam[1]($erpparam[2], $uid);
    }

    private function order($order_id, $uid)
    {
        if ($order_id <= 0 || $uid <= 0) {
            die('<h1 style="padding-top: 30px;padding-left: 30px;">参数错误！</h1>');
        }
        $model_order = Model('order');

        //获取订单详细
        $condition = array();
        /** @var storeModel $storeModel */
        $storeModel = Model('store');
        $stores = $storeModel->getStoreList(array('store_state'=>1),null,'','*',false);
        $condition['order_id'] = $order_id;
        $condition['buyer_id'] = $uid;
        $order_info = $model_order->getOrderInfo($condition, array('order_goods','order_common'));
        if (empty($order_info)) {
            die('<h1 style="padding-top: 30px;padding-left: 30px;">参数错误3！</h1>');
        }

        $order_goods_list = Model('order_goods')->getOrderGoodsList(array('order_id'=>$order_id));
        $order_info['add_time'] = date('Y-m-d H:i:s',$order_info['add_time']);
        $order_info['if_deliver'] = $model_order->getOrderOperateState('deliver', $order_info);
        if ($order_info['if_deliver']) {
            $express = rkcache('express',true);
            $order_info['express_info']['e_code'] = $express[$order_info['extend_order_common']['shipping_express_id']]['e_code'];
            $order_info['express_info']['e_name'] = $express[$order_info['extend_order_common']['shipping_express_id']]['e_name'];
            $order_info['express_info']['e_url'] = $express[$order_info['extend_order_common']['shipping_express_id']]['e_url'];
            $shipping_code = trim($order_info['shipping_code']);
            if (strpos($shipping_code, ',') !== false) {
                $shipping_code2 = explode(',', $shipping_code);
                if (!empty($shipping_code2) && isset($shipping_code2[0])) {
                    $shipping_code = $shipping_code2[0];
                }
            }
            $order_info['express_info']['e_info'] = Model('express')->get_express($order_info['express_info']['e_code'], $shipping_code);
        }
        $refundStep =  orderRefundStep( $order_info ) ;
        $order_info['order_state'] = $refundStep ? $order_info['state_desc'] ."({$refundStep})" : $order_info['state_desc'] ;
        Tpl::output('order_goods_list', $order_goods_list);
        Tpl::output('order_info', $order_info);
        Tpl::output('stores', $stores);
        Tpl::showpage('main_order');
    }

    private function goods($goods_id, $uid)
    {
        if ($goods_id <= 0) {
            die('<h1 style="padding-top: 30px;padding-left: 30px;">参数错误！</h1>');
        }
        $condition['goods_id'] = $goods_id;
        $goods_info = Model('goods')->getGoodsInfo($condition);

        if (empty($goods_info)) {
            die('<h1 style="padding-top: 30px;padding-left: 30px;">参数错误！</h1>');
        }
        Tpl::output('goods_info', $goods_info);
        Tpl::output('route_type', 'goods');
        Tpl::showpage('main_goods');
    }

    private function refund($refund_id, $uid)
    {
        if ($refund_id <= 0 || $uid <= 0) {
            die('<h1 style="padding-top: 30px;padding-left: 30px;">参数错误！</h1>');
        }
        $condition['refund_id'] = $refund_id;
        $condition['buyer_id'] = $uid;
        $refund_info = Model('refund_return')->getRefundReturnInfo($condition);

        if (empty($refund_info)) {
            die('<h1 style="padding-top: 30px;padding-left: 30px;">参数错误！</h1>');
        }
        $admin_array = Model('refund_return')->getRefundStateArray('admin');
        $refund_info['refund_state'] = $refund_info['seller_state'] == 2 ? $admin_array[$refund_info['refund_state']] : '';
        Tpl::output('refund_info', $refund_info);
        Tpl::output('route_type', 'refund');
        Tpl::showpage('main_refund');
    }

    public function testOp()
    {
        Tpl::showpage('test');
    }
}
