<?php

defined('ByShopWWI') or exit('Access Invalid!');

/**
 *
 */
class big_data1Control extends BaseApiControl
{

    public $token = '123456';

    public function __construct()
    {
        // 校验token
        $token = $_GET['token'];
        if ($token != $this->token) {
            $this->error('Access Denied!');
        }

    }

    public function channelOp()
    {
        // 渠道订单数据
        $startTime = strtotime(date('Y-m-d'));
        //$startTime = $startTime-365*86400;
        // 查询渠道列表
        /** @var member_fenxiaoModel $member_fenxiaoModel */
        $member_fenxiaoModel = Model('member_fenxiao');
        /** @var orderModel $orderModel */
        $orderModel = Model('order');
        /** @var refund_returnModel $refundModel */
        $refundModel = Model('refund_return');
        $member_fenxiao = $member_fenxiaoModel->getMemberFenxiao();
        $member_fenxiao_ids = array_column($member_fenxiao, 'member_id');
        $member_fenxiao = array_under_reset($member_fenxiao, 'member_id');
        // 统计销量
        $condition = array();
        $condition['add_time'] = array('gt', $startTime);
        $condition['order_from'] = 3;
        $field = "SUM(order_amount) as order_amount,COUNT(order_id) as order_num,buyer_id";
        $groupBy = "buyer_id";
        $orderStat = $orderModel->table('orders')->where($condition)->field($field)->order('order_amount DESC')->group($groupBy)->select();
        // 统计发货量
        $condition = array();
        $condition['shipping_time'] = array('gt', $startTime);
        $condition['order_from'] = 3;
        $field = "SUM(order_amount) as order_amount,COUNT(order_id) as order_num,buyer_id";
        $groupBy = "buyer_id";
        $shippingStat = $orderModel->table('orders')->where($condition)->field($field)->order('order_amount DESC')->group($groupBy)->select();
        $shippingStat = array_under_reset($shippingStat, 'buyer_id');

        $condition = array();
        $condition['add_time'] = array('gt', $startTime);
        $condition['buyer_id'] = array('in', $member_fenxiao_ids);
        $field = "SUM(refund_amount) as refund_amount,COUNT(order_id) as refund_num,buyer_id";
        $groupBy = "buyer_id";
        $refundStat = $refundModel->table('refund_return')->field($field)->where($condition)->order('refund_amount DESC')->group($groupBy)->select();
        $refundStat = array_under_reset($refundStat, 'buyer_id');
        $res = array();
        foreach ($orderStat as $orderInfo) {
            if (!isset($member_fenxiao[$orderInfo['buyer_id']])) continue;
            $memberInfo = $member_fenxiao[$orderInfo['buyer_id']];
            $shippingInfo = isset($shippingStat[$orderInfo['buyer_id']]) ? $shippingStat[$orderInfo['buyer_id']] :
                array('order_amount' => 0, 'order_num' => 0);
            $refundInfo = isset($refundStat[$orderInfo['buyer_id']]) ? $refundStat[$orderInfo['buyer_id']] :
                array('refund_amount' => 0, 'refund_num' => 0);
            $statInfo = array();
            $statInfo['channel_id'] = $memberInfo['member_id'];
            $statInfo['channel_name'] = $memberInfo['member_cn_code'];
            $statInfo['channel_code'] = $memberInfo['member_en_code'];
            $statInfo['order_amount'] = $orderInfo['order_amount'];
            $statInfo['order_num'] = $orderInfo['order_num'];
            $statInfo['shipping_amount'] = $shippingInfo['order_amount'];
            $statInfo['shipping_num'] = $shippingInfo['order_num'];
            $statInfo['refund_amount'] = $refundInfo['refund_amount'];
            $statInfo['refund_num'] = $refundInfo['refund_num'];
            $res[] = $statInfo;
        }
        $this->success($res);
    }

    public function goods_saleOp()
    {
        // 渠道订单数据
        $startTime = strtotime(date('Y-m-d'));
        $startTime = $startTime - 365 * 86400;
        /** @var goodsModel $goodsModel */
        $goodsModel = Model('goods');
        /** @var orderModel $orderModel */
        $orderModel = Model('order');
        /** @var order_goodsModel $order_goodsModel */
        $order_goodsModel = Model('order_goods');
        /** @var refund_returnModel $refundModel */
        $refundModel = Model('refund_return');
        // 统计销量
        $condition = array();
        $condition['add_time'] = array('gt', $startTime);
        $field = "order_id";
        $orderList = $orderModel->table('orders')->where($condition)->field($field)->select();
        $orderIds = array_column($orderList, 'order_id');
        if ($orderIds) {
            $condition = array();
            $condition['order_id'] = array('in', $orderIds);
            $field = "SUM(goods_num) as goods_num,SUM(goods_pay_price) as goods_amount,goods_id,MIN(goods_name) as goods_name";
            $groupBy = "goods_id";
            $goodsSaleStat = $orderModel->getOrderGoodsList($condition, $field, 10, null, 'goods_num DESC', $groupBy);
        } else {
            $goodsSaleStat = array();
        }
        $this->success($goodsSaleStat);
    }

    public function goods_refundOp()
    {
        // 渠道订单数据
        $startTime = strtotime(date('Y-m-d'));
        //$startTime = $startTime - 365 * 86400;
        /** @var goodsModel $goodsModel */
        $goodsModel = Model('goods');
        /** @var orderModel $orderModel */
        $orderModel = Model('order');
        /** @var refund_returnModel $refundModel */
        $refundModel = Model('refund_return');
        // 统计销量
        $condition = array();
        $condition['add_time'] = array('gt', $startTime);
        $condition['goods_id'] = array('gt', 0);
        $field = "SUM(goods_num) as goods_num,SUM(refund_amount) as refund_amount,goods_id,MIN(goods_name) as goods_name";
        $groupBy = "goods_id";
        $goodsRefundStat = $refundModel->table('refund_return')->field($field)->where($condition)->order('goods_num DESC')->group($groupBy)->limit(10)->select();
        $this->success($goodsRefundStat);
    }

    public function store_saleOp()
    {
        // 渠道订单数据
        $startTime = strtotime(date('Y-m-d'));
        //$startTime = $startTime - 365 * 86400;
        /** @var goodsModel $goodsModel */
        $goodsModel = Model('goods');
        /** @var orderModel $orderModel */
        $orderModel = Model('order');
        /** @var refund_returnModel $refundModel */
        $refundModel = Model('refund_return');
        // 统计销量
        $condition = array();
        $condition['add_time'] = array('gt', $startTime);
        $field = "SUM(order_amount) as order_amount, COUNT(order_amount) as order_num, store_id,MIN(store_name) as store_name";
        $groupBy = "store_id";
        $res = $orderModel->table('orders')->field($field)->where($condition)->order('order_amount DESC')->group($groupBy)->limit(10)->select();
        //var_dump($orderModel->getLastSql());
        $this->success($res);
    }
    public function store_shippingOp()
    {
        // 渠道订单数据
        $startTime = strtotime(date('Y-m-d'));
        //$startTime = $startTime - 365 * 86400;
        /** @var goodsModel $goodsModel */
        $goodsModel = Model('goods');
        /** @var orderModel $orderModel */
        $orderModel = Model('order');
        /** @var refund_returnModel $refundModel */
        $refundModel = Model('refund_return');
        // 统计销量
        $condition = array();
        $condition['shipping_time'] = array('gt', $startTime);
        $field = "SUM(order_amount) as order_amount, COUNT(order_amount) as order_num, store_id,MIN(store_name) as store_name";
        $groupBy = "store_id";
        $res = $orderModel->table('orders')->field($field)->where($condition)->order('order_amount DESC')->group($groupBy)->limit(10)->select();
        //var_dump($orderModel->getLastSql());
        $this->success($res);
    }
    public function store_refundOp()
    {
        // 渠道订单数据
        $startTime = strtotime(date('Y-m-d'));
        //$startTime = $startTime - 365 * 86400;
        /** @var goodsModel $goodsModel */
        $goodsModel = Model('goods');
        /** @var orderModel $orderModel */
        $orderModel = Model('order');
        /** @var refund_returnModel $refundModel */
        $refundModel = Model('refund_return');
        // 统计销量
        $condition = array();
        $condition['add_time'] = array('gt', $startTime);
        $field = "COUNT(refund_amount) as refund_num,SUM(refund_amount) as refund_amount,store_id,MIN(store_name) as store_name";
        $groupBy = "store_id";
        $res = $refundModel->table('refund_return')->field($field)->where($condition)->order('refund_amount DESC')->group($groupBy)->limit(10)->select();
        $this->success($res);
    }
    public function areaOp()
    {
        ini_set('memory_limit','4G');
        set_time_limit(900);
        // 渠道订单数据
        $startTime = strtotime(date('Y-m-d'));
        $startTime = $startTime-365*86400;
        // 查询渠道列表
        /** @var areaModel $areaModel */
        $areaModel = Model('area');
        /** @var orderModel $orderModel */
        $orderModel = Model('order');
        /** @var refund_returnModel $refundModel */
        $refundModel = Model('refund_return');
        $areas = $areaModel->getTopLevelAreas();
        // 统计销量
        $condition = array();
        $condition['orders.add_time'] = array('gt', $startTime);
        $field = "SUM(orders.order_amount) as order_amount,COUNT(orders.order_id) as order_num,order_common.reciver_province_id as province_id";
        $groupBy = "order_common.reciver_province_id";
        $orderStat = $orderModel->table('orders,order_common')->join('left join')->on('orders.order_id = order_common.order_id')->where($condition)->field($field)->order('order_amount DESC')->group($groupBy)->select();
        // 统计发货量
        $condition = array();
        $condition['orders.add_time'] = array('gt', $startTime);
        $field = "SUM(orders.order_amount) as shipping_amount,COUNT(orders.order_id) as shipping_num,order_common.reciver_province_id as province_id";
        $groupBy = "order_common.reciver_province_id";
        $shippingStat = $orderModel->table('orders,order_common')->join('left join')->on('orders.order_id = order_common.order_id')->where($condition)->field($field)->order('shipping_amount DESC')->group($groupBy)->select();
        $shippingStat = array_under_reset($shippingStat, 'province_id');

        // 统计退货量
        $condition = array();
        $condition['refund_return.add_time'] = array('gt', $startTime);
        $field = "SUM(refund_return.refund_amount) as refund_amount,COUNT(refund_return.order_id) as refund_num,order_common.reciver_province_id as province_id";
        $groupBy = "order_common.reciver_province_id";
        $refundStat = $refundModel->table('refund_return,order_common')->join('left join')->on('refund_return.order_id = order_common.order_id')->where($condition)->field($field)->order('refund_amount DESC')->group($groupBy)->select();
        $refundStat = array_under_reset($refundStat, 'province_id');

        $res = array();
        foreach ($orderStat as $orderInfo) {
            if (!isset($areas[$orderInfo['province_id']])) continue;
            $areaInfo = $areas[$orderInfo['province_id']];
            $shippingInfo = isset($shippingStat[$orderInfo['buyer_id']]) ? $shippingStat[$orderInfo['buyer_id']] :
                array('shipping_amount' => 0, 'shipping_num' => 0);
            $refundInfo = isset($refundStat[$orderInfo['buyer_id']]) ? $refundStat[$orderInfo['buyer_id']] :
                array('refund_amount' => 0, 'refund_num' => 0);
            $statInfo = array();
            $statInfo['area_id'] = $orderInfo['province_id'];
            $statInfo['area_name'] = $areaInfo;
            $statInfo['order_amount'] = $orderInfo['order_amount'];
            $statInfo['order_num'] = $orderInfo['order_num'];
            $statInfo['shipping_amount'] = $shippingInfo['shipping_amount'];
            $statInfo['shipping_num'] = $shippingInfo['shipping_num'];
            $statInfo['refund_amount'] = $refundInfo['refund_amount'];
            $statInfo['refund_num'] = $refundInfo['refund_num'];
            $res[] = $statInfo;
        }
        $this->success($res);
    }

}
