<?php
/**
 * 团员待提货订单
 *
 * @copyright  Copyright (c) 2007-2015 ShopNC Inc. (http://www.shopnc.net)
 * @license    http://www.shopnc.net
 * @link       http://www.shopnc.net
 * @since      File available since Release v1.1
 */
defined('ByShopWWI') or exit('Access Invalid!');

class shequ_wait_delivery_sonControl extends mobileMemberControl {

    public function __construct(){
        parent::__construct();
    }

    public function search_sonOp() {

        $tuanzhang_id = $this->member_info['tuanzhang_id'];
        $search_value = $_POST['search_value'];
        $condition_member = array();
        /** @var orderModel $order_model */
        $order_model = Model('order');
        if ($search_value) {
            if (preg_match('/^1[0-9]{10}$/', $search_value)) {
                $condition = array(
                    'shequ_tuan_id' => array('gt', 0),
                    'shequ_tz_id' => $tuanzhang_id,
                    'buyer_phone' => $search_value,
                );
                $order_info = $order_model->getOrderInfo($condition);
                if (empty($order_info)) {
                    $condition_member['member_id'] = -1;
                } else {
                    $condition_member['member_id'] = $order_info['buyer_id'];
                }
            } else  {
                $condition_member['wx_nick_name'] = $search_value;
            }
        } else {
            $condition_member['member_id'] = -1;
        }

        /** @var memberModel $member_model */
        $member_model = Model('member');
        $member_info = $member_model->getMemberInfo($condition_member);
        if (empty($member_info)) {
            output_error('暂无该用户');
        }
        $condition = array(
            'buyer_id' => $member_info['member_id'],
            'shequ_tuan_id' => array('gt', 0),
            'shequ_tz_id' => $tuanzhang_id,
        );
        $order_info = $order_model->getOrderInfo($condition);

        $return = array(
            'member_id' => $member_info['member_id'],
            'wx_nick_name' => $member_info['wx_nick_name'],
            'wx_user_avatar' => $member_info['wx_user_avatar'],
            'phone' => empty($order_info) ? '' : $order_info['buyer_phone']
        );
        output_data(array('member_info' => $return));



        /** @var memberModel $member_model */
        /*$member_model = Model('member');
        //不做默认团长限制
        $member_list = $member_model->getMemberList($condition_member,'*', $this->page);
        $buyer_ids = array_column($member_list, 'member_id');
        $order_list = array();
        if (!empty($member_list)) {
            $order_list = $order_model->getOrderGroup(array('buyer_id' => array('in', $buyer_ids)),'order_id,buyer_id,buyer_phone',$this->page, 'buyer_id');
            $order_list = array_under_reset($order_list, 'buyer_id');
        }
        $return = array();
        foreach ($member_list as $member) {
            $order_info = isset($order_list[$member['member_id']]) ? $order_list[$member['member_id']] : array();
            $return[] = array(
                'wx_nick_name' => $member['wx_nick_name'],
                'wx_user_avatar' => $member['wx_user_avatar'],
                'phone' => empty($order_info) ? '' : $order_info['buyer_phone']
            );
        }
        $page_count = $member_model->gettotalpage();
        if (intval($_POST['curpage']) > $page_count) $return = array();
        output_data(array('member_list' => $return), mobile_page($page_count));*/

    }

    public function infoOp() {
        $tuanzhang_id = $this->member_info['tuanzhang_id'];
        $member_id = $_POST['member_id'];
        if (empty($member_id)) {
            output_error('参数错误');
        }

        /** @var memberModel $member_model */
        $member_model = Model('member');
        /** @var orderModel $order_model */
        $order_model = Model('order');
        $member_info = $member_model->getMemberInfo(array('member_id' => $member_id));
        $order_info = array();
        if ($member_info) {
            $condition = array(
                'buyer_id' => $member_info['member_id'],
                'shequ_tuan_id' => array('gt', 0),
                'shequ_tz_id' => $tuanzhang_id,
            );
            $order_info = $order_model->getOrderInfo($condition);
        }
        $member_info = array(
            'member_id' => $member_id,
            'wx_nick_name' => $member_info['wx_nick_name'],
            'wx_user_avatar' => $member_info['wx_user_avatar'],
            'phone' => empty($order_info) ? '' : $order_info['buyer_phone']
        );
        output_data(array('member_info' => $member_info));
    }

    public function delivery_order_listOp() {
        $tuanzhang_id = $this->member_info['tuanzhang_id'];
        $member_id = $_POST['member_id'];
        if (empty($member_id)) {
            output_error('参数错误');
        }

        $condition = array(
            'buyer_id' => $member_id,
            'shequ_tuan_id' => array('gt', 0),
            'shequ_tz_id' => $tuanzhang_id,
            'order_state' => ORDER_STATE_SEND,
            'shequ_goods_state' => 10
        );

        /** @var orderModel $orderModel */
        $orderModel = Model('order');
        $order_list = $orderModel->getOrderList($condition, $this->page, '*', 'order_id desc', '', array('order_goods', 'order_common'));
        $return = array();
        foreach ($order_list as $order) {
            $order_goods = array();
            foreach ($order['extend_order_goods'] as $goods) {
                $order_goods[] = array(
                    'goods_image_url' => cthumb($goods),
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
            );
        }
        $page_count = $orderModel->gettotalpage();
        if (intval($_POST['curpage']) > $page_count) $return = array();
        output_data($return, mobile_page($page_count));
    }

    public function fetch_goods_outOp()
    {
        $tuanzhang_id = $this->member_info['tuanzhang_id'];
        $member_id = $_POST['member_id'];
        if (empty($member_id)) {
            output_error('参数错误');
        }

        $condition = array(
            'buyer_id' => $member_id,
            'shequ_tuan_id' => array('gt', 0),
            'shequ_tz_id' => $tuanzhang_id,
            'order_state' => ORDER_STATE_SEND,
            'shequ_goods_state' => 10
        );

        /** @var orderModel $orderModel */
        $orderModel = Model('order');
        /** @var orderLogic $orderLogic */
        $orderLogic = Logic('order');
        $order_list = $orderModel->getOrderList($condition, '', 'order_id');
        foreach ($order_list as $order) {
            $orderLogic->changeShequOrderDeliveryFetch($order);
        }
        output_data_new('成功');
    }
}

