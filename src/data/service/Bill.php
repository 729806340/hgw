<?php
/**
 * Author: Shen.L
 * Date: 2016/7/19
 * Time: 16:25
 */


/**
 * Class BillService
 */
class BillService
{

    /** @var storeModel */
    public $storeMode;
    /** @var store_costModel */
    public $storeCostMode;
    /** @var billModel */
    public $billMode;
    /** @var orderModel */
    public $orderMode;

    public function __construct()
    {
        ini_set('memory_limit','4G');
        $this->billMode = Model('bill');
        $this->storeMode = Model('store');
        $this->orderMode = Model('order');
        $this->storeCostMode = Model('store_cost');
    }

    /**
     * @param $store array
     * @param $cost boolean 是否包含店铺成本
     * @return array
     */
    public function getBillStart($store,$cost = true)
    {
        $bill_info = $this->billMode->getOrderBillInfo(array('ob_store_id'=>$store['store_id']),'max(ob_end_date) as stime');
        $start_unixtime = 0;
        if ($bill_info['stime']){
            $start_unixtime = $bill_info['stime']+1;
        } else {
            $condition = array();
            $condition['order_state'] = ORDER_STATE_SUCCESS;
            $condition['store_id'] = $store['store_id'];
            $condition['finnshed_time'] = array('gt',0);
            $order_info = $this->orderMode->getOrderInfo($condition,array(),'min(finnshed_time) as stime');
            $condition = array();
            $condition['cost_store_id'] = $store['store_id'];
            $condition['cost_state'] = 0;
            $condition['cost_time'] = array('gt',0);
            $cost_info = $this->storeCostMode->getStoreCostInfo($condition,'min(cost_time) as stime');

            if ($order_info['stime']) {
                if ($cost&&$cost_info['stime']) {
                    $start_unixtime = $order_info['stime'] < $cost_info['stime'] ? $order_info['stime'] : $cost_info['stime'];
                } else {
                    $start_unixtime = $order_info['stime'];
                }
            } else {
                if ($cost&&$cost_info['stime']) {
                    $start_unixtime = $cost_info['stime'];
                }
            }
            if ($start_unixtime) {
                $start_unixtime = strtotime(date('Y-m-d 00:00:00', $start_unixtime));
            }
        }
        return $start_unixtime;
    }

    public function getBillInfo($id)
    {
        $bill =  $this->billMode->table('order_bill')->where(array('ob_id'=>$id))->find();
        return $bill;
    }

    /**
     * @return false|int
     */
    public function getCommVer1Time()
    {
        return strtotime('2016-10-31 14:10');
    }

    /**
     * 计算账单
     * @param $bill
     * @return bool
     */
    public function calcRealBill($bill)
    {
        // 从订单获取结算周期店铺类型，若无默认未 platform
        $order_condition = array();
        $order_condition['order_state'] = ORDER_STATE_SUCCESS;
        $order_condition['store_id'] = $bill['ob_store_id'];
        $order_condition['finnshed_time'] = array('between',"{$bill['ob_start_date']},{$bill['ob_end_date']}");
        if(isset($bill['ob_store_manage_type'])&&!empty($bill['ob_store_manage_type'])){
            $manageType = $bill['ob_store_manage_type'];
        }else{
            $order = $this->orderMode->getOrderInfo($order_condition);
            $manageType = isset($order['manage_type'])?$order['manage_type']:'platform';
        }

        if(method_exists($this,'_calcRealBill'.$manageType)){
            return call_user_func(array($this,'_calcRealBill'.$manageType),$bill);
        };
        return false;
    }

    //计算共建商家
    public function _calcRealBillCo_construct($bill){
        $order_condition = array();
        $order_condition['order_state'] = ORDER_STATE_SUCCESS;
        $order_condition['store_id'] = $bill['ob_store_id'];
        $order_condition['finnshed_time'] = array('between',"{$bill['ob_start_date']},{$bill['ob_end_date']}");

        /*9月1号，过滤苏宁易购，人人优品的荆州门店==15*/
        $order_condition['filter_status']='0';
        $update = array();

        //订单金额
        $fields = 'sum(order_amount) as order_amount,sum(cost_amount) as cost_amount,sum(rpt_bill) as rpt_bill,sum(shipping_fee) as shipping_amount,min(store_name) as store_name';
        /** 获取结算期间的订单汇总信息 */
        $order_info =  $this->orderMode->getOrderInfo($order_condition,array(),$fields);
        $update['ob_order_totals'] = floatval($order_info['cost_amount']); // 成本
        $update['ob_sales'] = floatval($order_info['order_amount']);
        //红包
        //$update['ob_rpt_amount'] = floatval($order_info['rpt_bill']);
//        $update['ob_rpt_amount'] = 0;

        //运费
        //$update['ob_shipping_totals'] = floatval($order_info['shipping_amount']);
        $update['ob_shipping_totals'] = 0;
        //店铺名字
        $store_info = $this->storeMode->getStoreInfoByID($bill['ob_store_id']);
        $update['ob_store_name'] = $store_info['store_name'];

        //佣金金额，查订单商品表，用商品成本*佣金比例
        /*$order_info =  $this->orderMode->getOrderInfo($order_condition,array(),'count(DISTINCT order_id) as count');
        $order_count = $order_info['count'];
        $commis_rate_totals_array = array();
        //分批计算佣金，最后取总和
        for ($i = 0; $i <= $order_count; $i = $i + 300){
            $order_list = $this->orderMode->getOrderList($order_condition,'','order_id','',"{$i},300");
            $order_id_array = array();
            foreach ($order_list as $order_info) {
                $order_id_array[] = $order_info['order_id'];
            }
            if (!empty($order_id_array)){
                $order_goods_condition = array();
                $order_goods_condition['order_id'] = array('in',$order_id_array);
                $field = 'SUM(ROUND(goods_cost*commis_rate/100,2)) as commis_amount';
                $order_goods_info = $this->orderMode->getOrderGoodsInfo($order_goods_condition,$field);
                $commis_rate_totals_array[] = $order_goods_info['commis_amount'];
            }else{
                $commis_rate_totals_array[] = 0;
            }
        }
        $update['ob_commis_totals'] = floatval(array_sum($commis_rate_totals_array));*/
//        $update['ob_commis_totals'] = 0;

        //退款总额
        /** @var refund_returnModel $model_refund */
        $model_refund = Model('refund_return');
        $refund_condition = array();
        $refund_condition['seller_state'] = 2;
        $refund_condition['store_id'] = $bill['ob_store_id'];
        $refund_condition['goods_id'] = array('gt',0);
        $refund_condition['filter_status']='0';
        $refund_condition['admin_time'] = array(array('egt',$bill['ob_start_date']),array('elt',$bill['ob_end_date']),'and');
        /*$refund_info = $model_refund->getRefundReturnInfo(
            $refund_condition,
            'sum(ROUND(refund_amount*cost_rate/100,2)) as refund_amount,sum(rpt_bill) as rpt_bill,sum(ROUND(refund_amount*cost_rate*commis_rate/10000,2)) as commis_amount'
        );*/
        //$update['ob_order_return_totals'] = floatval($refund_info['refund_amount']);
        $refund_list = $model_refund->getRefundReturnList($refund_condition,'','*',999999) ;
        $oids = array_column($refund_list, 'order_id') ;
        $order_goods = $this -> getOrderGoods($oids) ;
        $refund_total = 0 ;
        // 计算退款金额
        foreach ($refund_list as $refund) {
        	$ogInfo 		= 	$order_goods[ $refund['order_id'] ][ $refund['goods_id'] ] ;
        	$cost_rate		=	$ogInfo['goods_cost'] / $ogInfo['goods_pay_price'] ;
        	$refund_amount = $refund['refund_amount_bill'] == -1?$refund['refund_amount']:$refund['refund_amount_bill'];
        	$refund_total 	+= 	round( $refund_amount * $cost_rate, 2 ) ;
        }
        $update['ob_order_return_totals'] = floatval($refund_total);

        //全部退款时的红包
        //$update['ob_rf_rpt_amount'] = floatval($refund_info['rpt_bill']);
//        $update['ob_rf_rpt_amount'] = 0;

        //退款佣金
        //$refund  =  $model_refund->getRefundReturnInfo($refund_condition,'sum(ROUND(refund_amount*commis_rate/100,2)) as amount');
        /*if ($refund_info['commis_amount']) {
            $update['ob_commis_return_totals'] = floatval($refund_info['commis_amount']);
        } else {
            $update['ob_commis_return_totals'] = 0;
        }*/
//        $update['ob_commis_return_totals'] = 0 ;

        //店铺活动费用
        /** @var store_costModel $model_store_cost */
        $model_store_cost = Model('store_cost');
        $cost_condition = array();
        $cost_condition['cost_store_id'] = $bill['ob_store_id'];
        $cost_condition['cost_state'] = 0;
        $cost_condition['cost_time'] = array(array('egt',$bill['ob_start_date']),array('elt',$bill['ob_end_date']),'and');
        $cost_info = $model_store_cost->getStoreCostInfo($cost_condition,'sum(cost_price) as cost_amount');
        $update['ob_store_cost_totals'] = floatval($cost_info['cost_amount']);

        //已经被取消的预定订单但未退还定金金额
        /** @var order_bookModel $model_order_book */
        $model_order_book = Model('order_book');
        $condition = array();
        $condition['book_store_id'] = $bill['ob_store_id'];
        $condition['book_cancel_time'] = array('between',"{$bill['ob_start_date']},{$bill['ob_end_date']}");
        $order_book_info = $model_order_book->getOrderBookInfo($condition,'sum(book_real_pay) as pay_amount');
        $update['ob_order_book_totals'] = floatval($order_book_info['pay_amount']);

        //本期应结
        $update['ob_result_totals'] = $update['ob_order_totals'] //成本金额
//            + $update['ob_rpt_amount'] // 红包金额
            + $update['ob_order_book_totals'] //定金
            - $update['ob_order_return_totals'] // 退款成本
//            + $update['ob_commis_totals'] // 佣金
//            - $update['ob_commis_return_totals'] // 退款佣金
//            - $update['ob_rf_rpt_amount'] // 退款红包
            - $update['ob_store_cost_totals']; //店铺活动费用
        //$update['ob_store_cost_totals'] ;
        $update['ob_create_date'] = TIMESTAMP;
        $update['ob_state'] = $bill['ob_state']?:1;
        if($update['ob_result_totals'] == 0){
            $update['ob_state'] = BILL_STATE_SUCCESS;
        }
        $update['ob_store_manage_type'] = 'co_construct';
        $update['os_month'] = date('Ym',$bill['ob_end_date']+1);
        return $this->billMode->editOrderBill($update,array('ob_id'=>$bill['ob_id']));
    }

    //计算平台商家
    public function _calcRealBillPlatform($bill){
        $order_condition = array();
        $order_condition['order_state'] = ORDER_STATE_SUCCESS;
        $order_condition['store_id'] = $bill['ob_store_id'];
        $order_condition['finnshed_time'] = array('between',"{$bill['ob_start_date']},{$bill['ob_end_date']}");

        /*9月1号，过滤苏宁易购，人人优品的荆州门店==15*/
        $order_condition['filter_status']='0';
        $update = array();
        //订单金额
        $fields = 'sum(order_amount) as order_amount,sum(rpt_bill) as rpt_bill,sum(shipping_fee) as shipping_amount,min(store_name) as store_name';
        $order_info =  $this->orderMode->getOrderInfo($order_condition,array(),$fields);
        $update['ob_order_totals'] = floatval($order_info['order_amount']);
        $update['ob_sales'] = floatval($order_info['order_amount']);

        //红包
        $update['ob_rpt_amount'] = floatval($order_info['rpt_bill']);

        //运费
        $update['ob_shipping_totals'] = floatval($order_info['shipping_amount']);
        //店铺名字
        $store_info = $this->storeMode->getStoreInfoByID($bill['ob_store_id']);
        $update['ob_store_name'] = $store_info['store_name'];

        //佣金金额
        $order_info =  $this->orderMode->getOrderInfo($order_condition,array(),'count(DISTINCT order_id) as count');
        $order_count = $order_info['count'];
        $commis_rate_totals_array = array();
        //分批计算佣金，最后取总和
        $ver1 = $this->getCommVer1Time();
        for ($i = 0; $i <= $order_count; $i = $i + 300){
            $order_list = $this->orderMode->getOrderList($order_condition,'','order_id','',"{$i},300");
            $order_id_array = array_column($order_list,'order_id');
            if($bill['ob_start_date']<$ver1){ // 如果存在账单起始时间小于区间时间的
                if($bill['ob_end_date']<$ver1){
                    $order_id_array_v0 = array_column($order_list,'order_id');
                    $order_list = array();
                }else{
                    $map = array(
                        'order_id'=>array('in',$order_id_array),
                        'finnshed_time'=>array('lt',$ver1)
                    );
                    $order_list_v0 = $this->orderMode->getOrderList($map,'','order_id');
                    $order_id_array_v0 = array_column($order_list_v0,'order_id');
                    $map = array(
                        'order_id'=>array('in',$order_id_array),
                        'finnshed_time'=>array('gt',$ver1)
                    );
                    $order_list = $this->orderMode->getOrderList($map,'','order_id');
                }
                if (!empty($order_id_array_v0)){
                    $order_goods_condition = array();
                    $order_goods_condition['order_id'] = array('in',$order_id_array_v0);
                    $field = 'SUM(ROUND(goods_pay_price*commis_rate/100,2)) as commis_amount';
                    $order_goods_info = $this->orderMode->getOrderGoodsInfo($order_goods_condition,$field);
                    $commis_rate_totals_array[] = $order_goods_info['commis_amount'];
                }else{
                    $commis_rate_totals_array[] = 0;
                }
            }
            $order_id_array = array_column($order_list,'order_id');

            if (!empty($order_id_array)){
                $order_goods_condition = array();
                $order_goods_condition['order_id'] = array('in',$order_id_array);
                $field = 'SUM(ROUND((goods_pay_price+rpt_bill)*commis_rate/100,2)) as commis_amount';
                $order_goods_info = $this->orderMode->getOrderGoodsInfo($order_goods_condition,$field);
                $commis_rate_totals_array[] = $order_goods_info['commis_amount'];
            }else{
                $commis_rate_totals_array[] = 0;
            }
        }

        $update['ob_commis_totals'] = floatval(array_sum($commis_rate_totals_array));

        //退款总额
        /** @var refund_returnModel $model_refund */
        $model_refund = Model('refund_return');
        $refund_condition = array();
        $refund_condition['seller_state'] = 2;
        $refund_condition['store_id'] = $bill['ob_store_id'];
        $refund_condition['goods_id'] = array('gt',0);
        $refund_condition['filter_status']='0';
        $refund_condition['admin_time'] = array(array('egt',$bill['ob_start_date']),array('elt',$bill['ob_end_date']),'and');

        $refund_list = $model_refund->getRefundReturnList($refund_condition,'','*',999999) ;
        $oids = array_column($refund_list, 'order_id') ;
        $order_goods = $this -> getOrderGoods($oids) ;
        $refund_total = 0 ;
        // 计算退款金额
        //$refund_info = $model_refund->getRefundReturnInfo($refund_condition,'sum(refund_amount) as refund_amount,sum(rpt_bill) as rpt_bill');
        //$update['ob_order_return_totals'] = floatval($refund_info['refund_amount']);

        //退款佣金
        /*$refund  =  $model_refund->getRefundReturnInfo($refund_condition,'sum(ROUND(refund_amount*commis_rate/100,2)) as amount');
        if ($refund) {
            $update['ob_commis_return_totals'] = floatval($refund['amount']);
        } else {
            $update['ob_commis_return_totals'] = 0;
        }*/
        //退款佣金
        $refund_list = $model_refund->getRefundReturnList($refund_condition,'','*',999999) ;
        $oids = array_column($refund_list, 'order_id') ;
        $order_goods = $this -> getOrderGoods($oids) ;
        $commis_return_totals = $rpt_bill_totals = 0 ;
        foreach ($refund_list as $refund) {
            $refund_amount = $refund['refund_amount_bill'] == -1?$refund['refund_amount']:$refund['refund_amount_bill'];
            $refund_total 	+= 	$refund_amount ;
            $ogInfo 		= 	$order_goods[ $refund['order_id'] ][ $refund['goods_id'] ] ;
            $commis_return_totals 	+= 	round( $refund_amount * $ogInfo['commis_rate'] / 100, 2 ) ;
            $rpt_bill_totals += $refund_amount == $ogInfo['goods_pay_price'] ? floatval($ogInfo['rpt_bill']) : 0;
        }
        $update['ob_order_return_totals'] = $refund_total;
        $update['ob_commis_return_totals'] = $commis_return_totals ;

        //全部退款时的红包
        //$update['ob_rf_rpt_amount'] = floatval($refund_info['rpt_bill']);
        // 期望方式，但目前不是这样计算的 ：sprintf("%.2f", ($refund_amount/$ogInfo['goods_pay_price'])*$ogInfo['rpt_amount'] );
        $update['ob_rf_rpt_amount'] = $rpt_bill_totals;
        
        //店铺活动费用
        /** @var store_costModel $model_store_cost */
        $model_store_cost = Model('store_cost');
        $cost_condition = array();
        $cost_condition['cost_store_id'] = $bill['ob_store_id'];
        $cost_condition['cost_state'] = 0;
        $cost_condition['cost_time'] = array(array('egt',$bill['ob_start_date']),array('elt',$bill['ob_end_date']),'and');
        $cost_info = $model_store_cost->getStoreCostInfo($cost_condition,'sum(cost_price) as cost_amount');
        $update['ob_store_cost_totals'] = floatval($cost_info['cost_amount']);

        //已经被取消的预定订单但未退还定金金额
        /** @var order_bookModel $model_order_book */
        $model_order_book = Model('order_book');
        $condition = array();
        $condition['book_store_id'] = $bill['ob_store_id'];
        $condition['book_cancel_time'] = array('between',"{$bill['ob_start_date']},{$bill['ob_end_date']}");
        $order_book_info = $model_order_book->getOrderBookInfo($condition,'sum(book_real_pay) as pay_amount');
        $update['ob_order_book_totals'] = floatval($order_book_info['pay_amount']);

        //本期应结
        $update['ob_result_totals'] =
            $update['ob_order_totals'] // 订单总金额
            + $update['ob_rpt_amount'] // 下单时使用的平台红包
            + $update['ob_order_book_totals'] //定金订单中的未退定金
            - $update['ob_order_return_totals'] // 退单金额
            - $update['ob_commis_totals'] // 佣金金额
            + $update['ob_commis_return_totals'] // 退还佣金
            - $update['ob_rf_rpt_amount'] //全部退款时应扣除的平台红包
            - $update['ob_store_cost_totals']; //店铺促销费用
        $update['ob_store_cost_totals'] ;
        $update['ob_create_date'] = TIMESTAMP;
        $update['ob_state'] = $bill['ob_state']?:1;
        if($update['ob_result_totals'] == 0){
            $update['ob_state'] = BILL_STATE_SUCCESS;
            $update['ob_pay_date'] = TIMESTAMP;
            $update['ob_pay_content'] = '0元账单，自动完成';
        }
        $update['ob_store_manage_type'] = 'platform';
        $update['os_month'] = date('Ym',$bill['ob_end_date']+1);
        $update['ob_build'] = $bill['ob_build']+1;
        $update['ob_ver'] = 1;
        return $this->billMode->editOrderBill($update,array('ob_id'=>$bill['ob_id']));
    }


    private function getOrderGoods($order_ids)
    {
        /** @var orderModel $orderModel */
        $orderModel = Model('order');
        $order_goods_condition = array();
        $order_goods_condition['order_id'] = array('in',$order_ids);
        $field = '*';
        $order_goods_list = $orderModel->getOrderGoodsList($order_goods_condition,$field,9999999);

        $return = array() ;
        foreach ($order_goods_list as $order_goods)
        {
            $return[ $order_goods['order_id'] ][ $order_goods['goods_id'] ] = $order_goods ;
        }

        return $return ;
    }
	
    /** 重置order_bill及订单数据为待推送状态 **/
	function resetBillSapSatus($bill_info, $ob_state=3)
    {
        $ob_id = $bill_info['ob_id'];
        if( !$ob_id ) return false;
        $update = array();
        $update['ob_state'] = empty($ob_state) ? 10 : $ob_state;//10商务审核,3 平台已审核
        $update['ob_sap_order'] = 0;
        $update['ob_sap_refund'] = 0;
        $update['ob_sap_storecost'] = 0;
        
        $this->billMode->editOrderBill($update,array('ob_id'=>$ob_id));
          
        $model_order = Model('order');
        $order_condition = array();
        $order_condition['order_state'] = ORDER_STATE_SUCCESS;
        $order_condition['store_id'] = $bill_info['ob_store_id'];
        $order_condition['finnshed_time'] = array('between',"{$bill_info['ob_start_date']},{$bill_info['ob_end_date']}");
        $order_data = array();
        $order_data['send_sap'] = 0 ;
        $order_data['purchase_sap'] = 0 ;
        $model_order->editOrder($order_data, $order_condition) ;
        
        $model_refund = Model('refund_return');
        $refund_condition = array();
        $refund_condition['seller_state'] = 2;
        $refund_condition['store_id'] = $bill_info['ob_store_id'];
        $refund_condition['goods_id'] = array('gt',0);
        $refund_condition['admin_time'] = array('between',"{$bill_info['ob_start_date']},{$bill_info['ob_end_date']}");
        $refund_data = array();
        $refund_data['purchase_sap'] = 0 ;
        $refund_data['sap_return_credit'] = 0 ;
        $model_refund->editRefundReturn($refund_condition, $refund_data);
        
        $model_store_cost = Model('store_cost');
        $refund_condition = array();
        $refund_condition['cost_state'] = 0;
        $refund_condition['cost_store_id'] = $bill_info['ob_store_id'];
        $refund_condition['cost_price'] = array('gt',0);
        $refund_condition['cost_time'] = array('between',"{$bill_info['ob_start_date']},{$bill_info['ob_end_date']}");
        $refund_data = array();
        $refund_data['purchase_sap'] = 0 ;
        $model_store_cost->where($refund_condition)->update($refund_data);
        $model_bill_log = Model('bill_log');
        $log_condition = array();
        $log_condition['ob_id'] = $ob_id;
        $log_data = array();
        $log_data['send_sap'] = 0 ;
        $log_data['refund_sap'] = 0 ;
        $log_data['purchase_refund_sap'] = 0 ;
        $model_bill_log->where($log_condition)->update($log_data);
        return true ;
    }

    /*计算各个渠道的订单总价格*/
    public function _calChannelBill($bill){
        $this->orderMode = Model('order');
        $this->memberFenxiao=Model('member_fenxiao');
        $this->channelBill=Model('channel_bill');
        $order_condition = array();
        $order_condition['order_state'] = ORDER_STATE_SUCCESS;
        $order_condition['buyer_id'] = $bill['ob_channel_id'];
        $order_condition['finnshed_time'] = array('between',"{$bill['ob_start_date']},{$bill['ob_end_date']}");
        $order_condition['filter_status']='0';
        $order_condition['add_time']=array('gt',strtotime("2017-09-01"));
        $update = array();
        //订单金额
        $fields = 'sum(order_amount) as order_amount,sum(cost_amount) as cost_amount,sum(rpt_bill) as rpt_bill,sum(shipping_fee) as shipping_amount,min(store_name) as store_name';
        /** 获取结算期间的订单汇总信息 */
        $order_info =  $this->orderMode->getOrderInfo($order_condition,array(),$fields);
        $update['ob_order_totals'] = floatval($order_info['cost_amount']); // 成本
        $update['ob_sales'] = floatval($order_info['order_amount']);
        $update['ob_shipping_totals'] = 0;
        //渠道名字
        $channel_info = $this->memberFenxiao->getMembeFenxiaoList(array('member_id'=>$bill['ob_channel_id']),'1');
        $update['ob_channel_name'] = $channel_info[0]['member_cn_code'];
        //退款总额
        /** @var refund_returnModel $model_refund */
        $model_refund = Model('refund_return');
        $refund_condition = array();
        $refund_condition['seller_state'] = 2;
        $refund_condition['buyer_id'] = $bill['ob_channel_id'];
        $refund_condition['goods_id'] = array('gt',0);
        $refund_condition['admin_time'] = array(array('egt',$bill['ob_start_date']),array('elt',$bill['ob_end_date']),'and');
        $refund_condition['filter_status']='0';
        $refund_condition['add_time']=array('gt',strtotime("2017-09-01"));
        $refund_list = $model_refund->getRefundReturnList($refund_condition,'','*',999999) ;
        $oids = array_column($refund_list, 'order_id') ;
        $order_goods = $this -> getOrderGoods($oids) ;
        $refund_total = 0 ;
        // 计算退款金额
        foreach ($refund_list as $refund) {
            $ogInfo 		= 	$order_goods[ $refund['order_id'] ][ $refund['goods_id'] ] ;
            $cost_rate		=	$ogInfo['goods_cost'] / $ogInfo['goods_pay_price'] ;
            $refund_amount = $refund['refund_amount_bill'] == -1 ? $refund['refund_amount']:$refund['refund_amount_bill'];
            $refund_total 	+= 	round( $refund_amount * $cost_rate, 2 ) ;
        }
        $update['ob_order_return_totals'] = floatval($refund_total);
        //本期应结
        $update['ob_result_totals'] = $update['ob_order_totals']//成本金额
        - $update['ob_order_return_totals']; // 退款成本
        $update['ob_create_date'] =time();
        $update['ob_state'] = $bill['ob_state']?:1;
        if($update['ob_result_totals'] == 0){
            $update['ob_state'] = BILL_STATE_SUCCESS;
        }
        $update['os_month'] = date('Ym',$bill['ob_end_date']+1);
        return $this->channelBill->editOrderBill($update,array('ob_id'=>$bill['ob_id']));
    }

}