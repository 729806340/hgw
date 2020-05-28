<?php
/**
 * 到货提醒
 *
 * @copyright  Copyright (c) 2007-2015 ShopNC Inc. (http://www.shopnc.net)
 * @license    http://www.shopnc.net
 * @link       http://www.shopnc.net
 * @since      File available since Release v1.1
 */
defined('ByShopWWI') or exit('Access Invalid!');

class shequ_goods_noticeControl extends mobileMemberControl {

    public function __construct(){
        parent::__construct();
    }

    //头部商品数量
    public function get_notice_dataOp() {
        $today_order_goods_condition_result = $this->_get_condition('today_notice');
        $wait_order_goods_condition_result = $this->_get_condition('wait_notice');
        /** @var orderModel $orderModel */
        $orderModel = Model('order');
        $today_order_goods_list = $orderModel->getOrderGoodsList($today_order_goods_condition_result[0], 'rec_id');
        $wait_order_goods_list = $orderModel->getOrderGoodsList($wait_order_goods_condition_result[0], 'rec_id');
        $result = array(
            'today_num' => count($today_order_goods_list),
            'wait_num' => count($wait_order_goods_list),
        );
        output_data($result);
    }

    //今日
    public function indexOp() {
        $notice_type = $_POST['notice_type'];
//        if ($notice_type != 'today_notice') {
//            $notice_type = 'wait_notice';
//        }
        $order_goods_condition_result = $this->_get_condition($notice_type);
        $return = array(
            'buyer_num' => $order_goods_condition_result[1],
            'goods_num' => 0,
            'goods_list' => array(),
        );
        /** @var orderModel $orderGoodsModel */
        $orderGoodsModel = Model('order');
        $order_goods_condition =  $order_goods_condition_result[0];
        $order_goods_list = $orderGoodsModel->getOrderGoodsList($order_goods_condition, '*','', $this->page);
        $page_count = $orderGoodsModel->gettotalpage();
        $return['goods_num'] = $orderGoodsModel->gettotalnum();
        foreach ($order_goods_list as $order_goods) {
            $order_goods['goods_image_url'] = cthumb($order_goods);
            $return['goods_list'][] = $order_goods;
        }
        if (intval($_POST['curpage']) > $page_count) $return['goods_list'] = array();
        output_data($return, mobile_page($page_count));
    }


    private function _get_condition($notice_type = 'today_notice')
    {
        $tuanzhang_id = $this->member_info['tuanzhang_id'];
        $tuan_condition = array(
            'tz_id' => $tuanzhang_id,
            'fetch_goods_state' => array('gt', 10)
        );
        $start_time = strtotime(date('Y-m-d', TIMESTAMP));
        $end_time = $start_time + 86400;
        if ($notice_type == 'today_notice') {
            $tuan_condition['send_product_date'] = array('between', array($start_time, $end_time));
        } else {
            $tuan_condition['send_product_date'] = array('gt',  $end_time);
        }
        /** @var shequ_tuanModel $shequ_tuan_model */
        $shequ_tuan_model = Model('shequ_tuan');
        $shequ_tuan_list = $shequ_tuan_model->getList($tuan_condition);
        if (empty($shequ_tuan_list)) {
            $condition = array(
                'order_id' => 0
            );
        } else {
            $condition = array(
                'shequ_tuan_id' => array('in', array_column($shequ_tuan_list, 'config_id')),
                'shequ_tz_id' => $tuanzhang_id,
                'order_state' => array('egt', ORDER_STATE_SEND),
                'refund_amount' => 0
            );
        }
        /** @var orderModel $orderModel */
        $orderModel = Model('order');
        $order_list = $orderModel->getOrderList($condition, '', 'order_id');
        $order_number = count($order_list);
        if (empty($order_list)) {
            $order_goods_condition = array(
                'order_id' => 0,
            );
        } else {
            $order_goods_condition = array(
                'order_id' => array('in', array_column($order_list, 'order_id')),
            );
        }
        $goods_name = $_POST['goods_name'];
        if ($goods_name) {
            $order_goods_condition['goods_name'] = array('like', '%' . $goods_name . '%');
        }
        return array($order_goods_condition, $order_number);
    }



}

