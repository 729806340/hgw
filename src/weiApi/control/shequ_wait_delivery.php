<?php
/**
 * 待提货订单
 *
 * @copyright  Copyright (c) 2007-2015 ShopNC Inc. (http://www.shopnc.net)
 * @license    http://www.shopnc.net
 * @link       http://www.shopnc.net
 * @since      File available since Release v1.1
 */
defined('ByShopWWI') or exit('Access Invalid!');

class shequ_wait_deliveryControl extends mobileMemberTuanControl {

    public function __construct(){
        parent::__construct();
    }

    public function indexOp() {
        $take_type = $_POST['take_type'];
        $condition = $this->_get_condition($take_type);
        /** @var orderModel $orderModel */
        $orderModel = Model('order');
        $order_list = $orderModel->getOrderList($condition, $this->page, '*', 'order_id desc', '', array('order_goods', 'order_common','member'));
        $return = array();
        foreach ($order_list as $order) {
            $order_goods = array();
            foreach ($order['extend_order_goods'] as $goods) {
                $order_goods[] = array(
                    'goods_image_url' => cthumb($goods['goods_image']),
                    'goods_name' => $goods['goods_name'],
                    'goods_spec' => $goods['goods_spec'],
                    'goods_num' => $goods['goods_num'],
                    'send_date' => $order['extend_order_common']['shipping_time'] ? date('Y-m-d H:i:s', $order['extend_order_common']['shipping_time']) : '',
                );
            }
            $return[] = array(
                'add_time' => date('Y-m-d H:i:s', $order['add_time']),
                'order_sn' => $order['order_sn'],
                'order_goods' => $order_goods,
                'member_info' => array(
                    'wx_nick_name' => $order['extend_member']['wx_nick_name'],
                    'wx_user_avatar' => $order['extend_member']['wx_user_avatar'],
                    'buyer_phone' => $order['buyer_phone'],
                    'reciver_name' => $order['extend_order_common']['reciver_name'],
                ),
            );
        }
        $page_count = $orderModel->gettotalpage();
        if (intval($_POST['curpage']) > $page_count) $return = array();
        output_data($return, mobile_page($page_count));
    }

    private function _get_condition($take_type = 'wait_take')
    {
        $tuanzhang_id = $this->member_info['tuanzhang_id'];
        $condition = array(
            'shequ_tuan_id' => array('gt', 0),
            'shequ_tz_id' => $tuanzhang_id,
            'refund_amount' => 0
        );
        if ($take_type == 'wait_take') {
            $condition['shequ_goods_state'] = 10;
            $condition['order_state'] = ORDER_STATE_SEND;
        } else {
            $condition['shequ_goods_state'] = 20;
            $condition['_string'] ="((order_state=".ORDER_STATE_SUCCESS.")"."OR"."(order_state=".ORDER_STATE_SEND."))" ;
        }
        $search_key = $_POST['search_key'];
        $search_value = $_POST['search_value'];
        if ($search_key && $search_value) {
            if ($search_key == 'nick_name') {
                /** @var memberModel $member_model */
                $member_model = Model('member');
                $member_info = $member_model->getMemberInfo(array('wx_nick_name' => $search_value));
                if (empty($member_info)) {
                    $condition['buyer_id'] = -1;
                } else {
                    $condition['buyer_id'] = $member_info['member_id'];
                }
            }

            if ($search_key == 'phone') {
                $condition['buyer_phone'] = $search_value;
            }
            if ($search_key == 'name') {
                $link_condition = $condition;
                $link_condition['reciver_name'] = $search_value;
                $order_list_new = Model()->table('orders,order_common')->join('inner')->on('orders.order_id=order_common.order_id')->where($link_condition)->field('orders.order_id')->select();
                if (empty($order_list_new)) {
                    $condition['order_id'] = -1;
                } else {
                    $condition['order_id'] = array('in', array_column($order_list_new, 'order_id'));
                }
            }
        }
        return $condition;

    }
}

