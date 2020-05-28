<?php
/**
 * 控制台
 * @汉购网提供技术支持 授权请购买shopnc授权
 * @license    http://www.hangowa.com
 * @link       交流群号：
 */
defined('ByShopWWI') or exit('Access Invalid!');

class orderControl extends control
{
    /**
     * ### 订单列表（含详情）
     * 此接口查询订单列表数据
     *
     * @param string $start_time 订单创建起始时间戳（支付完成）
     * @param string $end_time   订单创建结束时间戳
     * @param int $state         订单状态0已取消20已支付
     * @return json
     */
    public function indexOp()
    {
        $pageSize = isset($_POST['pagesize']) ? $_POST['pagesize'] : 100;

        $condition['store_id'] = $this->storeId;

        $start = isset($_POST['start_time']) ? $_POST['start_time'] : '0';
        $end = isset($_POST['end_time']) ? $_POST['end_time'] : time();
        $condition['add_time'] = array('between', array($start, $end));

        if (isset($_POST['state'])) {
            $condition['order_state'] = $_POST['state'];
        }

        $fields =  "`order_id`,
                    `order_sn`,
                    `order_amount` as 'amount',
                    `shipping_fee` as 'freight',
                    `rpt_amount` as 'rpt',
                    `add_time` as 'create_time',
                    `shipping_time` as 'shipping_time',
                    `order_state` as 'state',
                    `buyer_name` as 'channel',
                    `fx_order_id`,
                    `shipping_code`
                    ";

        $orders = Model('order')->getOrderList($condition, $pageSize, $fields, 'order_id desc', '', array('order_goods', 'order_common'));
        $currentPage = Model('order')->shownowpage(); // 当前页数
        $totalPage = Model('order')->gettotalpage(); // 总页数

        // 获取物流公司名称
        $shippingExpressIds = array();
        foreach ($orders as $key => $value) {
            if ($value['extend_order_common']['shipping_express_id']) {
                $shippingExpressIds[] = $value['extend_order_common']['shipping_express_id'];
            }
        }
        if ($shippingExpressIds) {
            $express = Model('express')->where(array('id' => array('in', $shippingExpressIds)))->select();
        }
        foreach ($express as $key => $value) {
            $expressArr[$value['id']] = $value['e_name'];
        }

        // 按接口要求重组数据
        foreach ($orders as $key => $value) {
            // 收货人信息
            $orders[$key]['channel']          = $value['fx_order_id'] ? $value['channel'] : 'hangowa';
            $orders[$key]['receiver_name']    = $value['extend_order_common']['reciver_name'];
            $orders[$key]['receiver_phone']   = $value['extend_order_common']['reciver_info']['phone'];
            $orders[$key]['receiver_address'] = $value['extend_order_common']['reciver_info']['address'];
            $orders[$key]['shipping_name']    = isset($expressArr[$value['extend_order_common']['shipping_express_id']]) ? $expressArr[$value['extend_order_common']['shipping_express_id']] : '';
            unset($orders[$key]['extend_order_common']);

            // goods_list
            foreach ($value['extend_order_goods'] as $k => $v) {
                $goods[$key][] = array(
                    'rec_id'      => $v['rec_id'],
                    'goods_id'    => $v['goods_id'],
                    'goods_name'  => $v['goods_name'],
                    'goods_num'   => $v['goods_num'],
                    'goods_price' => $v['goods_price'],
                );
            }
            $orders[$key]['goods_list'] = $goods[$key];
            unset($orders[$key]['extend_order_goods']);
        }

        $data = array('curpage' => $currentPage, 'totalpage' => $totalPage, 'orders' => $orders);
        jsonReturn('200', $data);
    }

    /**
     * ### 订单详情
     * 此接口提供订单详情
     *
     * @param int $order_sn   订单编号
     * @return json
     */
    public function infoOp()
    {
        if (!isset($_POST['order_sn']) || empty($_POST['order_sn'])) {
            jsonReturn(400, '缺少参数 order_sn');
        }

        $condition['order_sn'] = $_POST['order_sn'];
        $condition['store_id'] = $this->storeId;
        $fields =  "`order_id`,
                    `order_sn`,
                    `order_amount` as 'amount',
                    `shipping_fee` as 'freight',
                    `rpt_amount` as 'rpt',
                    `add_time` as 'create_time',
                    `shipping_time` as 'shipping_time',
                    `order_state` as 'state',
                    `buyer_name` as 'channel',
                    `fx_order_id`,
                    `shipping_code`
                    ";

        $order = Model('order')->getOrderInfo($condition, array('order_goods', 'order_common'), $fields);
        if (empty($order)) {
            jsonReturn(400, '该订单不存在');
        }

        // 获取物流公司名称
        $order['shipping_name'] = '';
        if ($order['extend_order_common']['shipping_express_id']) {
            $express = Model('express')->where(array('id' => $order['extend_order_common']['shipping_express_id']))->field('e_name')->find();
            $order['shipping_name'] = $express['e_name'];
        }
        unset($order['extend_order_common']);

        // goods_list
        foreach ($order['extend_order_goods'] as $k => $v) {
            $order['goods_list'][] = array(
                'rec_id'      => $v['rec_id'],
                'goods_id'    => $v['goods_id'],
                'goods_name'  => $v['goods_name'],
                'goods_num'   => $v['goods_num'],
                'goods_price' => $v['goods_price'],
            );
        }
        unset($order['extend_order_goods']);

        jsonReturn(200, $order);
    }

    /**
     * ### 发货
     * 此接口提供订单发货功能
     *
     * @param string express_id 快递公司ID
     * @param string express_sn 快递单号
     * @param string order_id   订单编号
     * @return json
     */
    public function shipOp()
    {
        if (empty($_POST['express_id'])) {
            jsonReturn(400, '缺少参数 express_id');
        }

        if (empty($_POST['express_sn'])) {
            jsonReturn(400, '缺少参数 express_sn');
        }

        if (empty($_POST['order_id'])) {
            jsonReturn(400, '缺少参数 order_id');
        }

        $orderInfo = Model('order')->getOrderInfo(array('order_sn' => $_POST['order_id']));
        if ($orderInfo['store_id'] != $this->storeId) {
            jsonReturn(400, '订单不存在');
        }

        $daddress_id = Model('daddress')->where(array('store_id' => $this->storeId))->getfield('address_id');

        $post = array(
            'reciver_name'        => '',
            'reciver_info'        => '',
            'deliver_explain'     => '',
            'daddress_id'         => $daddress_id[0],
            'shipping_code'       => $_POST['express_sn'],
            'shipping_express_id' => $_POST['express_id'],
        );

        $result = logic('order')->changeOrderSend($orderInfo, 'seller', $this->storeId, $post);

        if (!$result['state']) {
            jsonReturn(400, $result['msg']);
        }

        jsonReturn(200, $result['msg']);
    }

    /**
     * ### 退款列表（含详情）
     * 此接口查询退单列表数据
     *
     * @param string start_time   退单创建起始时间戳
     * @param string end_time     退单创建结束时间戳
     * @param string refund_state 退单状态1处理中/2待管理员处理/3已完成
     * @param string seller_state 退单状态1待审核/2同意/3不同意
     * @param string refund_type  售后类型1为退款,2为退货
     * @return json
     */
    public function refundOp()
    {
        $pageSize = isset($_POST['pagesize']) ? $_POST['pagesize'] : 100;

        $condition['store_id'] = $this->storeId;

        $start = isset($_POST['start_time']) ? $_POST['start_time'] : '0';
        $end = isset($_POST['end_time']) ? $_POST['end_time'] : time();
        $condition['add_time'] = array('between', array($start, $end));

        if (isset($_POST['refund_state'])) {
            $condition['refund_state'] = $_POST['refund_state'];
        }

        if (isset($_POST['seller_state'])) {
            $condition['seller_state'] = $_POST['seller_state'];
        }

        if (isset($_POST['refund_type'])) {
            $condition['refund_type'] = $_POST['refund_type'];
        }

        $fields =  "`refund_id`,
                    `refund_sn`,
                    `order_id`,
                    `order_sn`,
                    `goods_id`,
                    `order_goods_id`,
                    `goods_name`,
                    `goods_num`,
                    `refund_amount`,
                    `add_time`,
                    `buyer_message`,
                    `express_id`,
                    `invoice_no` as 'express_sn',
                    `ship_time`,
                    `refund_state`,
                    `seller_state`,
                    `refund_type`";

        $refundReturn = Model('refund_return')->getRefundReturnList($condition, $pageSize, $fields);
        $currentPage = Model('refund_return')->shownowpage(); // 当前页数
        $totalPage = Model('refund_return')->gettotalpage(); // 总页数

        $data = array('curpage' => $currentPage, 'totalpage' => $totalPage, 'refund_return' => $refundReturn);
        jsonReturn(200, $data);
    }
}
