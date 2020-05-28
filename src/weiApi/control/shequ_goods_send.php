<?php
/**
 * 送货预告
 *
 * @copyright  Copyright (c) 2007-2015 ShopNC Inc. (http://www.shopnc.net)
 * @license    http://www.shopnc.net
 * @link       http://www.shopnc.net
 * @since      File available since Release v1.1
 */
defined('ByShopWWI') or exit('Access Invalid!');

class shequ_goods_sendControl extends mobileMemberTuanControl {

    public function __construct(){
        parent::__construct();
    }

    //头部商品数量
    public function get_notice_dataOp() {
        $today_order_goods_condition = $this->_get_condition('today_notice');
        $wait_order_goods_condition = $this->_get_condition('wait_notice');
        /** @var orderModel $orderModel */
        $orderModel = Model('order');
        $today_order_goods_list = $orderModel->getOrderGoodsList($today_order_goods_condition, 'rec_id');
        $wait_order_goods_list = $orderModel->getOrderGoodsList($wait_order_goods_condition, 'rec_id');
        $result = array(
            'today_num' => count($today_order_goods_list),
            'wait_num' => count($wait_order_goods_list),
        );
        output_data($result);
    }

    public function indexOp() {
        $notice_type = $_POST['notice_type'];
//        if ($notice_type != 'today_notice') {
//            $notice_type = 'wait_notice';
//        }
        $order_goods_condition = $this->_get_condition($notice_type);
        /** @var orderModel $orderGoodsModel */
        $orderGoodsModel = Model('order');
        $order_goods_list = $orderGoodsModel->getOrderGoodsList($order_goods_condition, '*','', $this->page);
        $page_count = $orderGoodsModel->gettotalpage();
        $goods_list = array();
        foreach ($order_goods_list as $order_goods) {
            $order_goods['goods_image_url'] = cthumb($order_goods);
            $goods_list[] = $order_goods;
        }
        if (intval($_POST['curpage']) > $page_count) $goods_list = array();
        output_data(array('goods_list' => $goods_list), mobile_page($page_count));
    }

    private function _get_condition($notice_type = 'today_notice')
    {
        $tuanzhang_id = $this->member_info['tuanzhang_id'];
        $tuan_condition = array(
            'tz_id' => $tuanzhang_id,
            'fetch_goods_state' => 10
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
        if (empty($order_list)) {
            $order_goods_condition = array(
                'order_id' => 0,
            );
        } else {
            $order_goods_condition = array(
                'order_id' => array('in', array_column($order_list, 'order_id')),
            );
        }
        return $order_goods_condition;
    }



}

