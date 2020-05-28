<?php
/**
 * 支付行为
 *
 * @汉购网提供技术支持 授权请购买shopnc授权
 * @license    http://www.hangowa.com
 * @link       交流群号：
 */
defined('ByShopWWI') or exit('Access Invalid!');
class b2b_paymentLogic {

    /**
     * 取得实物订单所需支付金额等信息
     * @param int $pay_sn
     * @param int $member_id
     * @return array
     */
    public function getRealOrderInfo($pay_sn, $member_id = null) {

        //验证订单信息
        /** @var orderModel $model_order */
        $model_order = Model('b2b_order');
        $condition = array();
        $condition['pay_sn'] = $pay_sn;
        if (!empty($member_id)) {
            $condition['buyer_id'] = $member_id;
        }
        $order_pay_info = $model_order->getOrderPayInfo($condition,true);
        if(empty($order_pay_info)){
            return callback(false,'该支付单不存在');
        }

        $order_pay_info['subject'] = '汉购网B2B'.$order_pay_info['pay_sn'];
        $order_pay_info['order_type'] = 'real_order';

        $condition = array();
        $condition['pay_sn'] = $pay_sn;

        //同步异步通知时,预定支付尾款时需要用到已经支付状态
        $condition['order_state'] = array('in',array(ORDER_STATE_NEW,ORDER_STATE_PAY));
        $order_list = $model_order->getOrderList($condition,'','*','','',array(),true);

        //取订单其它扩展信息
        $result = $this->getOrderExtendList($order_list);
        if (!$result['state']) {
            return $result;
        }

        $order_pay_info['order_list'] = $order_list;
        $order_pay_info['if_buyer_repay'] = $result['data']['if_buyer_repay'];

        return callback(true,'',$order_pay_info);
    }

    /**
     * 取得订单其它扩展信息
     * @param array $order_list
     * @param string $role 操作角色 目前只用admin时需要传入
     * @return array
     */
    public function getOrderExtendList(& $order_list,$role = '') {

        //预定订单
        if ($order_list[0]['order_type'] == 2) {
            $order_info = $order_list[0];
            $result = Logic('order_book')->getOrderBookInfo($order_info);
            if (!$result['data']['if_buyer_pay'] && $role != 'admin') {
                return callback(false,'未找到需要支付的订单');
            }
            $order_list[0] = $result['data'];
            $order_list[0]['order_amount'] = $order_list[0]['pay_amount'];
            
            //如果是支付尾款，则把订单状态更改为未支付状态，方便执行统一支付程序
            if ($result['data']['if_buyer_repay']) {
                $order_list[0]['order_state'] = ORDER_STATE_NEW;
            }

            //当以下情况时不需要清除数据pd_amount,rcb_amount：
            //如果第2次支付尾款，并且已经锁定了站内款
            //当以下情形时清除站内余额数据pd_amount,rcb_amount：
            //如果第1次支付，两个均为空，如果第1.5次支付，不会POST扣款标识不会重复扣站内款，不需要该值，所以可以清空
            //如果第2次支付尾款，如果第一次选择站内支付，也需要清空原来的支付定金的金额
            if (!$order_list[0]['if_buyer_pay_lock']) {
                $order_list[0]['pd_amount'] = $order_list[0]['rcb_amount'] = 0;
            }
        }
        return callback(true);
    }

    /**
     *
     * 取得所使用支付方式信息
     * @param string $payment_code
     * @param $payment_code
     * @return mixed
     */
    public function getPaymentInfo($payment_code) {
        if (in_array($payment_code,array('offline','predeposit')) || empty($payment_code)) {
            return callback(false,'系统不支持选定的支付方式');
        }
        /** @var b2b_paymentModel $model_payment */
        $model_payment = Model('b2b_payment');
        $condition = array();
        $condition['payment_code'] = $payment_code;
        $payment_info = $model_payment->getPaymentOpenInfo($condition);
        if(empty($payment_info)) {
            return callback(false,'系统不支持选定的支付方式');
        }

        $inc_file = BASE_PATH.DS.'api'.DS.'payment'.DS.$payment_info['payment_code'].DS.$payment_info['payment_code'].'.php';
        if(!file_exists($inc_file)){
            return callback(false,'系统不支持选定的支付方式');
        }
        require_once($inc_file);
        $payment_info['payment_config'] = unserialize($payment_info['payment_config']);

        return callback(true,'',$payment_info);
    }

    /**
     * 支付成功后修改实物订单状态
     */
    public function updateRealOrder($out_trade_no, $payment_code, $order_list, $trade_no) {
        $post['payment_code'] = $payment_code;
        $post['trade_no'] = $trade_no;
        return Logic('b2b_order')->changeOrderReceivePay($order_list, 'system', '系统', $post);
    }

}
