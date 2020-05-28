<?php

defined('ByShopWWI') or exit('Access Invalid!');

/**
 *
 */
class big_data2Control extends BaseApiControl
{

    public $token = '123456';

    public function __construct()
    {
        import('ArrayHelper');
        // 校验token
        $token = $_GET['token'];
        if ($token != $this->token) {
            $this->error('Access Denied!');
        }

    }

    // 静态数据：历史销售总额、历史销量、会员总数、供应商数量、本月新增用户数量，本月销售金额
    // 近30天销量Top10
    // 近30天物流数据
    // 实时销售数据
    // 渠道数据

    // 需求：历史销售总额、历史销量、会员总数、供应商数量、本月新增用户数量，本月销售金额
    public function static_dataOp()
    {
        $bigdata_setting = rkcache('bigdata_setting');
        $big_data_rate = ArrayHelper::getValue($bigdata_setting, 'big_data_rate', 1);
        // 读取缓存，基础数据缓存
        /** @var orderModel $orderModel */
        $orderModel = model('order');
        $baseData = rkcache('static_data_static_data_base');
        $forceUpdate = ArrayHelper::getValue($_GET, 'forceUpdate', false);
        if ($forceUpdate || !$baseData || !isset($baseData['time']) || time() - $baseData['time'] > 86400) {
            // 更新基础数据
            $baseData = $this->_getBaseStaticData();
        }
        $today = strtotime(date('Y-m-d'));
        $time = time() - $today;
        $orderInfo = $orderModel->getOrderInfo(array('add_time' => array('gt', $today)), array(), 'SUM(order_amount) as totalAmount, COUNT(order_id) as orderNum, COUNT(DISTINCT buyer_phone ) as userNum');
        // 到今天0点的销售总额
        $res = array(
            'totalAmount' => ncPriceFormat(($baseData['totalAmount'] * $big_data_rate + $big_data_rate * $orderInfo['totalAmount'] + ($big_data_rate - 1) * $time * 10) / 10000),
            'orderNum' => intval($baseData['orderNum'] * $big_data_rate + $big_data_rate * $orderInfo['orderNum'] + ($big_data_rate - 1) * $time / 3),
            'userNum' => intval($baseData['userNum'] * $big_data_rate + $big_data_rate * $orderInfo['userNum'] + ($big_data_rate - 1) * $time / 4),
            'totalAmount30' => ncPriceFormat($baseData['totalAmount30'] * $big_data_rate + $big_data_rate * $orderInfo['totalAmount'] + ($big_data_rate - 1) * $time * 10),
            'orderNum30' => intval($baseData['orderNum30'] * $big_data_rate + $big_data_rate * $orderInfo['orderNum'] + ($big_data_rate - 1) * $time / 3),
            'userNum30' => intval($baseData['userNum30'] * $big_data_rate + $big_data_rate * $orderInfo['userNum'] + ($big_data_rate - 1) * $time / 4),
            'storeNum' => $big_data_rate * $baseData['storeNum'],
        );
        echo json_encode([$res]);
        exit;
        //$this->success([$res]);
    }

    // 静态数据：历史销售总额、历史销量、会员总数、供应商数量、本月新增用户数量，本月销售金额
    private function _getBaseStaticData()
    {
        ini_set('max_execution_time', 0);
        $res = array();
        /** @var orderModel $orderModel */
        $orderModel = model('order');
        /** @var storeModel $storeModel */
        $storeModel = model('store');
        $today = strtotime(date('Y-m-d'));
        //$monthDay = $today - 29 * 86400;
        $monthDay = strtotime(date('Y-m-1'));
        //$res['saleAmount'] =$orderModel->getOrderAmount(array('add_time'=>array('lt',$today)));
        //$res['saleCount'] =$orderModel->getOrderCount(array('add_time'=>array('lt',$today)));
        $res = $orderModel->getOrderInfo(array('add_time' => array('lt', $today), 'order_state' => array('gt', 0)), array(), 'SUM(order_amount) as totalAmount, COUNT(order_id) as orderNum, COUNT(DISTINCT buyer_phone ) as userNum');
        $monthOrder = $orderModel->getOrderInfo(array('add_time' => array('Between', array($monthDay, $today)), 'order_state' => array('gt', 0)), array(), 'SUM(order_amount) as totalAmount, COUNT(order_id) as orderNum, COUNT(DISTINCT buyer_phone ) as userNum');
        $res['totalAmount30'] = $monthOrder['totalAmount'];
        $res['orderNum30'] = $monthOrder['orderNum'];
        $res['userNum30'] = $monthOrder['userNum'];
        $seller = $storeModel->getStoreCount(array());
        $res['storeNum'] = $seller;
        $res['time'] = $today;
        wkcache('static_data_static_data_base', $res);
        return $res;
    }


    /**
     * 30天发货物流Top10
     */
    public function logisticsOp()
    {

        $bigdata_setting = rkcache('bigdata_setting');
        //$big_data_rate = ArrayHelper::getValue($bigdata_setting, 'big_data_rate', 1);
        $big_data_rate = ArrayHelper::getValue($bigdata_setting, 'big_data_rate_logistics', 5);
        // 读取缓存，基础数据缓存

        $today = strtotime(date('Y-m-d'));
        $time = time() - $today;
        $startTime = $today - 30 * 86400;
        /** @var orderModel $orderModel */
        $orderModel = Model('order');
        $forceUpdate = ArrayHelper::getValue($_GET, 'forceUpdate', false);
        $baseData = rkcache('static_data_logistics_base');
        if ($forceUpdate || !$baseData || !isset($baseData['time']) || time() - $baseData['time'] > 86400) {
            $baseItems = $orderModel->table('order_common')->field('COUNT(order_id) as num,shipping_express_id')->where(array('shipping_express_id'=>array('gt',0),'shipping_time' => array('between', array($startTime, $today))))->order('num DESC')->limit(10)->group('shipping_express_id')->select();
            /** @var expressModel $expressModel */
            $expressModel = Model('express');
            foreach ($baseItems as $k => $v) {
                $express = $expressModel->getExpressInfo($v['shipping_express_id']);
                $baseItems[$k]['name'] = $express['e_name'];
            }
            $baseData = array('time' => $today, 'items' => $baseItems);
            wkcache('static_data_logistics_base', $baseData);
        }
        $baseItems = $baseData['items'];
        $ids = array_column($baseItems, 'shipping_express_id');
        $items = $orderModel->table('order_common')->field('COUNT(order_id) as num,shipping_express_id')->where(array('shipping_express_id' => array('in' => $ids), 'shipping_time' => array('gt', $today)))->group('shipping_express_id')->select();
        $items = array_column($items, 'shipping_express_id', 'num');
        $res = array();
        foreach ($baseItems as $k => $v) {
            $baseValue = $v['num'];
            $todayValue = $items[$v['shipping_express_id']];
            if ($v['name']=='邮政包裹'){
                $baseValue = 101605;
                $v['num'] = intval($baseValue + $todayValue * $big_data_rate + $time * ($big_data_rate - 1) / 20);
            }else{
                $v['num'] = intval($baseValue * $big_data_rate + $todayValue * $big_data_rate + $time * (10 - $k) * ($big_data_rate - 1) / 20);
            }
            $res[] = array('x' => in_array($v['name'],array('EMS'))?$v['name']:mb_substr($v['name'], 0, 2), 'y' => $v['num']);
        }
        echo json_encode($res);
        exit;
    }

    /**
     * 30天渠道数据
     * @throws Exception
     */
    public function channelOp()
    {

        $bigdata_setting = rkcache('bigdata_setting');
        $big_data_rate = ArrayHelper::getValue($bigdata_setting, 'big_data_rate', 1);
        $big_data_rate = ArrayHelper::getValue($bigdata_setting, 'big_data_rate_channel', 1);
        // 读取缓存，基础数据缓存

        $today = strtotime(date('Y-m-d'));
        $time = time() - $today;
        $startTime = $today - 30 * 86400;
        /** @var orderModel $orderModel */
        $orderModel = Model('order');
        $forceUpdate = ArrayHelper::getValue($_GET, 'forceUpdate', false);
        $baseData = rkcache('static_data_channel_base');
        if ($forceUpdate || !$baseData || !isset($baseData['time']) || time() - $baseData['time'] > 86400) {
            /** @var member_fenxiaoModel $fenxiaoModel */
            $fenxiaoModel = Model('member_fenxiao');
            $members = $fenxiaoModel->getMemberFenxiao(true);
            $members = array_column($members, 'member_cn_code', 'member_id');
            $baseItems = $orderModel->table('orders')->field('SUM(order_amount) as amount,buyer_id')->where(array('add_time' => array('between', array($startTime, $today)), 'order_from' => 3))->order('amount DESC')->limit(10)->group('buyer_id')->select();
            foreach ($baseItems as $k => $v) {
                $baseItems[$k]['name'] = $members[$v['buyer_id']];
            }
            $baseData = array('time' => $today, 'items' => $baseItems);
            wkcache('static_data_channel_base', $baseData);
        }
        $baseItems = $baseData['items'];
        $ids = array_column($baseItems, 'buyer_id');
        $items = $orderModel->table('orders')->field('SUM(order_amount) as amount,buyer_id')->where(array('buyer_id' => array('in' => $ids), 'add_time' => array('between', array($startTime, $today))))->order('amount DESC')->limit(10)->group('buyer_id')->select();
        $items = array_column($items, 'buyer_id', 'num');
        $res = array();
        foreach ($baseItems as $k => $v) {
            $baseValue = $v['amount'];
            $todayValue = $items[$v['buyer_id']];
            $v['num'] = ncPriceFormat($baseValue * $big_data_rate + $todayValue * $big_data_rate + ($time * 29 * (10 - $k) + rand(1, 99) / 100) * ($big_data_rate - 1) / 10);
            $res[] = array('x' => $v['name'], 'y' => $v['num']);
        }
        echo json_encode($res);
        exit;
    }

    /**
     * 近30天销售商品销售
     */
    public function goods_saleOp()
    {

        ini_set('memory_limit', '4G');
        /** @var goodsModel $goodsModel */
        $goodsModel = Model('goods');
        /** @var orderModel $orderModel */
        $orderModel = Model('order');
        /** @var order_goodsModel $order_goodsModel */
        $order_goodsModel = Model('order_goods');
        /** @var jdy_mappingModel $jdy_mappingModel */
        $jdy_mappingModel = Model('jdy_mapping');


        $bigdata_setting = rkcache('bigdata_setting');
        //$big_data_rate = ArrayHelper::getValue($bigdata_setting, 'big_data_rate', 1);
        $big_data_rate = ArrayHelper::getValue($bigdata_setting, 'big_data_rate_sale', 5);
        // 读取缓存，基础数据缓存

        $today = strtotime(date('Y-m-d'));
        $time = time() - $today;
        $startTime = $today - 30 * 86400;
        $forceUpdate = ArrayHelper::getValue($_GET, 'forceUpdate', false);
        $baseData = rkcache('static_data_goods_sale_base');
        if ($forceUpdate || !$baseData || !isset($baseData['time']) || time() - $baseData['time'] > 86400) {
            // 统计销量
            $condition = array();
            $condition['add_time'] = array('between', array($startTime, $today));
            $condition['order_state'] = array('gt', 0);
            $field = "order_id";
            $minOrder = $orderModel->table('orders')->where($condition)->field($field)->order('order_id ASC')->limit(1)->select();
            $maxOrder = $orderModel->table('orders')->where($condition)->field($field)->order('order_id DESC')->limit(1)->select();
            //$orderIds = array_column($orderList, 'order_id');
            $condition = array();
            $condition['jdy_mapping.item_id'] = array('gt', 0);
            $condition['order_goods.order_id'] = array('between', array($minOrder[0]['order_id'], $maxOrder[0]['order_id']));

            $field = "SUM(order_goods.goods_num) as goods_num,SUM(order_goods.goods_pay_price) as goods_amount,MIN(jdy_mapping.item_name) as jdy_name,jdy_mapping.item_id as jdy_id";
            $groupBy = "jdy_mapping.item_id";
            $baseItems = $orderModel->table('order_goods,jdy_mapping')->join('left')->on('order_goods.goods_id=jdy_mapping.goods_id')->field($field)->where($condition)->limit(10)->order('goods_num DESC')->group($groupBy)->select();
            // 查找全部商品ID
            $jdyIds = array_column($baseItems, 'jdy_id');
            $jdyGoodsList = $jdy_mappingModel->where(array('item_id' => array('in', $jdyIds)))->limit(99999)->select();
            $jdyGoodsMap = array();
            foreach ($jdyGoodsList as $goods) {
                if (!isset($jdyGoodsMap[$goods['item_id']])) {
                    $jdyGoodsMap[$goods['item_id']] = array();
                }
                $jdyGoodsMap[$goods['item_id']][] = $goods['goods_id'];
            }
            foreach ($baseItems as $k => $v) {
                $baseItems[$k]['goods'] = isset($jdyGoodsMap[$v['jdy_id']]) ? $jdyGoodsMap[$v['jdy_id']] : array();
            }
            $baseData = array(
                'time' => $today,
                'items' => $baseItems,
                'goods_ids' => array_column($jdyGoodsList, 'goods_id'),
            );
            wkcache('static_data_goods_sale_base', $baseData);
        }
        //
        $baseItems = $baseData['items'];
        $ids = $baseData['goods_ids'];

        $condition = array();
        $condition['add_time'] = array('between', array($today, time()));
        $condition['order_state'] = array('gt', 0);
        $field = "order_id";
        $minOrder = $orderModel->table('orders')->where($condition)->field($field)->order('order_id ASC')->limit(1)->select();
        //$orderList = $orderModel->table('orders')->where($condition)->field($field)->limit(false)->select();
        //$orderIds = array_column($orderList, 'order_id');
        if ($minOrder) {
            $items = $orderModel->table('order_goods')->field('SUM(goods_num) as goods_num,SUM(goods_pay_price) as goods_amount, goods_id')->where(array('goods_id' => array('in', $ids), 'order_id' => array('gt', $minOrder[0]['order_id'])))->group('goods_id')->limit(999999)->select();
        } else {
            $items = array();
        }
        $items = array_column($items, null, 'goods_id');
        $res = array();
        foreach ($baseItems as $k => $v) {
            $price = $v['goods_amount'] / $v['goods_num'];
            $item = array('name' => $v['jdy_name'], 'goods_num' => $big_data_rate * $v['goods_num'], 'goods_amount' => $big_data_rate * $v['goods_amount']);
            foreach ($v['goods'] as $goodsId) {
                $item['goods_num'] += ($big_data_rate - 1) * $items[$goodsId]['goods_num'] + intval($time * ($big_data_rate - 1) / 100);
                $item['goods_amount'] += ($big_data_rate - 1) * $items[$goodsId]['goods_amount'] + ($time * ($big_data_rate - 1) / 100) * $price;
            }
            $item['goods_amount'] = ncPriceFormat($item['goods_amount']);
            $res[] = $item;
        }
        exit(json_encode($res));
    }

    /**
     * 今日商品销量
     * @throws Exception
     */
    public function goods_sale_todayOp()
    {

        ini_set('memory_limit', '4G');
        /** @var goodsModel $goodsModel */
        $goodsModel = Model('goods');
        /** @var orderModel $orderModel */
        $orderModel = Model('order');
        /** @var order_goodsModel $order_goodsModel */
        $order_goodsModel = Model('order_goods');
        /** @var jdy_mappingModel $jdy_mappingModel */
        $jdy_mappingModel = Model('jdy_mapping');


        $bigdata_setting = rkcache('bigdata_setting');
        $big_data_rate = ArrayHelper::getValue($bigdata_setting, 'big_data_rate', 1);
        // 读取缓存，基础数据缓存

        $today = strtotime(date('Y-m-d'));
        $time = time() - $today;
        $today = $today - 30 * 86400;
        // 统计销量
        $condition = array();
        $condition['add_time'] = array('between', array($today, time()));
        $condition['order_state'] = array('gt', 0);
        $field = "order_id";
        $minOrder = $orderModel->table('orders')->where($condition)->field($field)->order('order_id ASC')->limit(1)->select();
        if ($minOrder){

        //$orderIds = array_column($orderList, 'order_id');
        $condition = array();
        $condition['jdy_mapping.item_id'] = array('gt', 0);
        $condition['order_goods.order_id'] = array('gt', $minOrder[0]['order_id']);

        $field = "SUM(order_goods.goods_num) as goods_num,SUM(order_goods.goods_pay_price) as goods_amount,MIN(jdy_mapping.item_name) as jdy_name,jdy_mapping.item_id as jdy_id";
        $groupBy = "jdy_mapping.item_id";
        $baseItems = $orderModel->table('order_goods,jdy_mapping')->join('left')->on('order_goods.goods_id=jdy_mapping.goods_id')->field($field)->where($condition)->limit(10)->order('goods_num DESC')->group($groupBy)->select();
        }else{
            $baseItems = array();
        }

        $res = array();
        foreach ($baseItems as $k => $v) {
            $price = $v['goods_amount'] / $v['goods_num'];
            $item = array(
                'name' => $v['jdy_name'],
                'goods_num' => ($big_data_rate - 1) * $v['goods_num'] + intval($time * ($big_data_rate - 1) / 100),
                'goods_amount' => ncPriceFormat(($big_data_rate - 1) * $v['goods_amount'] + ($time * ($big_data_rate - 1) / 100) * $price)
            );
            $res[] = $item;
        }
        exit(json_encode($res));
    }

    /**
     * 近30天省份销售
     */
    public function province_saleOp(){
        //
        $bigdata_setting = rkcache('bigdata_setting');
        $big_data_rate = ArrayHelper::getValue($bigdata_setting, 'big_data_rate', 1);
        $big_data_rate = ArrayHelper::getValue($bigdata_setting, 'big_data_rate_province', 1);
        // 读取缓存，基础数据缓存

        $today = strtotime(date('Y-m-d'));
        $time = time() - $today;
        $startTime = $today - 100 * 86400;
        /** @var orderModel $orderModel */
        $orderModel = Model('order');
        $forceUpdate = ArrayHelper::getValue($_GET, 'forceUpdate', false);
        $baseData = rkcache('static_data_province_base');
        if ($forceUpdate || !$baseData || !isset($baseData['time']) || time() - $baseData['time'] > 86400) {
            /** @var areaModel $areaModel */
            $areaModel = Model('area');
            $areas = $areaModel->getTopLevelAreas();
            $baseItems = $orderModel->table('orders,order_common')->on('orders.order_id=order_common.order_id')->field('SUM(orders.order_amount) as amount,order_common.reciver_province_id as province_id')->where(array('orders.add_time' => array('between', array($startTime, $today)),'order_common.reciver_province_id'=>array('gt',0)))->order('amount DESC')->limit(10)->group('order_common.reciver_province_id')->select();
            foreach ($baseItems as $k => $v) {
                $baseItems[$k]['name'] = $areas[$v['province_id']];
            }
            $baseData = array('time' => $today, 'items' => $baseItems);
            wkcache('static_data_province_base', $baseData);
        }
        $baseItems = $baseData['items'];
        $ids = array_column($baseItems, 'province_id');
        $items = $orderModel->table('orders,order_common')->on('orders.order_id=order_common.order_id')->field('SUM(orders.order_amount) as amount,order_common.reciver_province_id as province_id')->where(array('order_common.reciver_province_id' => array('in' => $ids), 'orders.add_time' => array('between', array($startTime, $today))))->order('amount DESC')->limit(10)->group('order_common.reciver_province_id')->select();
        $items = array_column($items, 'province_id', 'num');
        $res = array();
        foreach ($baseItems as $k => $v) {
            $baseValue = $v['amount'];
            $todayValue = $items[$v['province_id']];
            $v['num'] = ncPriceFormat($baseValue * $big_data_rate + $todayValue * $big_data_rate + ($time * 29 * (10 - $k) + rand(1, 99) / 100) * ($big_data_rate - 1) / 10);
            $res[] = array('x' => $v['name'], 'y' => $v['num']);
        }
        exit(json_encode($res));
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
        ini_set('memory_limit', '4G');
        set_time_limit(900);
        // 渠道订单数据
        $startTime = strtotime(date('Y-m-d'));
        $startTime = $startTime - 365 * 86400;
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
