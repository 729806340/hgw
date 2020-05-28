<?php
/**
 * 任务计划 - 天执行的任务
 *
 * 
 *
 *
 * @汉购网提供技术支持 授权请购买shopnc授权
 * @license    http://www.hangowa.com
 * @link       交流群号：
 */
defined('ByShopWWI') or exit('Access Invalid!');

class shequControl extends BaseCronControl {

    public $area_tuan_threshold = array(
        11113=>100,
    );

    public function __construct()
    {
        parent::__construct();
        Model('bill');
    }

    // 社区团购定时任务
    public function buildBillsOp(){
        // 批量修改订单数据
        /** @var orderModel $orderModel */
        $orderModel = model('order');
        $length = C('shequ_refund_time');
        $orderModel->editOrder(array('shequ_bill_time'=>array('exp','finnshed_time + '.$length)),array('shequ_bill_time'=>0,'shequ_tz_id'=>array('gt',0),'finnshed_time'=>array('gt',0),'order_state'=>ORDER_STATE_SUCCESS));
        // 读取团长信息，循环处理
        // 根据读取账单起始时间，
        /** @var shequ_tuanzhangModel $tuanzhangModel */
        $tuanzhangModel = model('shequ_tuanzhang');
        $tzList = $tuanzhangModel->getList(array(),'','id ASC','*',999999);
        foreach ($tzList as $tz){
            $this->_buildTzBills($tz);
        }
    }
    private function _buildTzBills($tz){

        /** @var shequ_billModel $billModel */
        $billModel = model('shequ_bill');
        // 根据团长信息获取账单起始日期，循环处理
        $start = $this->_getStartDate($tz);
        if ($start == 0 ) return;
        $current_time = strtotime(date('Y-m-d 00:00:00',TIMESTAMP));
        while ($start<$current_time){
            $billModel->beginTransaction();
            try {
                $this->_createBill($tz,$start);
                $billModel->commit();
            } catch (Exception $e) {
                $billModel->rollback();
                return;
            }
            $start += 24*3600;
        }
    }

    private function _createBill($tz,$start){
        /** @var shequ_billModel $billModel */
        $billModel = model('shequ_bill');
        $data = array(
            'ob_start_date'=>$start,
            'ob_end_date'=>$start+24*3600-1,
            'ob_state'=>1,
            'ob_store_name'=>$tz['name'],
            'ob_create_date'=>TIMESTAMP,
            'ob_store_id'=>$tz['id'],
            );
        $has = $billModel->getOne($data);
        if($has) return;
        $insert = $billModel->addItem($data);

        if (!$insert) {
            throw new Exception('生成团长账单失败');
        }
        $data['ob_id'] = $insert;
        $calc = $this->_calcBill($data);
        if (!$calc) {
            throw new Exception('更新团长账单失败');
        }
    }

    private function _calcBill($bill){
        // 查找结束时间前未结算的订单，循环订单，计算

        /** @var orderModel $orderModel */
        $orderModel = model('order');
        /** @var shequ_billModel $billModel */
        $billModel = model('shequ_bill');
        /** @var refund_returnModel $refundModel */
        $refundModel = model('refund_return');
        $condition = array(
            'shequ_bill_time'=>array('elt',$bill['ob_end_date']),
            'shequ_tz_bill_id'=>0,
            'shequ_tz_id'=>$bill['ob_store_id'],
            'order_state'=>ORDER_STATE_SUCCESS
        );
        $orders = $orderModel->getOrderList($condition,999999,'*');
        // 查找退款
        if (!$orders){
            return $billModel->edit(array('ob_id'=>$bill['ob_id']),array('ob_state'=>BILL_STATE_SUCCESS));
        }
        $orderIds = array_column($orders,'order_id');
        $refunds = $refundModel->getRefundReturnList(array('order_id'=>array('in',$orderIds),'seller_state'=>array('in',array(1,2))));
        $refunds = array_column($refunds,null,'order_id');
        $total = 0;
        $refundTotal = 0;
        $billOrderIds = array();
        foreach ($orders as $order){
            $order_id = $order['order_id'];
            if (isset($refunds[$order_id])){
                $refund = $refunds[$order_id];
                if ($refund['refund_state'] <3){
                    continue;
                }
                $refundTotal += $refund['shequ_return_amount'];
            }
            $billOrderIds[] = $order_id;
            $total += $order['shequ_return_amount'];
        }
        if (count($billOrderIds)>0){
            $orderModel->editOrder(array('shequ_tz_bill_id'=>$bill['ob_id']),array('order_id'=>array('in',$billOrderIds)));
            $refundModel->editRefundReturn(array('order_id'=>array('in',$billOrderIds)),array('shequ_tz_bill_id'=>$bill['ob_id']));
        }
        if ($total==0)
            return $billModel->edit(array('ob_id'=>$bill['ob_id']),array('ob_state'=>BILL_STATE_SUCCESS));
        return $billModel->edit(array('ob_id'=>$bill['ob_id']),
            array(
                'ob_result_totals'=>$total-$refundTotal,
                'ob_commis_totals'=>$total,
                'ob_commis_return_totals'=>$refundTotal
            ));
    }

    /**
     * 获取团长结算单起始时间
     * @param $tz array 团长信息
     * @return false|int
     */
    private function _getStartDate($tz){
        // 获取最后的结算单
        /** @var shequ_billModel $billModel */
        $billModel = model('shequ_bill');

        /** @var orderModel $orderModel */
        $orderModel = model('order');

        $lastBill = $billModel->getOne(array('ob_store_id'=>$tz['id']),'max(ob_end_date) as stime');
        $startUnixTime = 0;
        if ($lastBill['stime']){
            $startUnixTime =$lastBill['stime']+1;
        }else{
            $condition = array();
            $condition['order_state'] = ORDER_STATE_SUCCESS;
            $condition['shequ_tz_id'] = $tz['id'];
            $condition['shequ_bill_time'] = array('gt',0);
            $order_info = $orderModel->getOrderInfo($condition,array(),'min(finnshed_time) as stime');
            if ($order_info['stime']) {
                $startUnixTime = $order_info['stime'];
            }
            if ($startUnixTime) {
                $startUnixTime = strtotime(date('Y-m-d 00:00:00', $startUnixTime));
            }
        }
        return $startUnixTime;
    }
    public function pushBillsOp(){
        // 获取状态为12的结算单
        /** @var shequ_billModel $billModel */
        $billModel = model('shequ_bill');
        do{
            $bill = $billModel->getOne(array('ob_state'=>BILL_STATE_FIRE_PHONIX));
            if (!$bill) break;
            // 推送
            if ($this->_pushBill($bill)){
                $billModel->edit(
                    array('ob_id'=>$bill['ob_id']),
                    array('ob_state'=>BILL_STATE_CEO)
                );
            }
            // 更新
        }while($bill);
    }

    private static $TzList = array();
    private function _pushBill($bill){

        if (!isset(self::$TzList[$bill['ob_store_id']])){
            /** @var shequ_tuanzhangModel $tuanzhangModel */
            $tuanzhangModel = model('shequ_tuanzhang');
            $tz = $tuanzhangModel->getOne(array('id'=>$bill['ob_store_id']));
            if (!$tz) return false;
            self::$TzList[$bill['ob_store_id']] = $tz;
        }
        $tz = self::$TzList[$bill['ob_store_id']];
        $host = 'https://apisupplier.hangomart.com';
        if (C('ON_DEV')) $host = 'http://api.supplier.com';
        $url = $host.'/payable/tuanzhang/';
        import("Curl");
        $curl = new Curl();
        $curl->setJsonDecoder(function ($response){
            $json_obj = json_decode($response, true);
            if (!($json_obj === null)) {
                $response = $json_obj;
            }
            return $response;
        });
        $data = array(
            'api_key'=>'c1dca569396ba260fe6a7d552b6b7d75',
            'amount'=>$bill['ob_result_totals'],
            'platform_id'=>1,
            'date'=>$bill['ob_start_date'],
            'shequ_bill_id'=>$bill['ob_id'],
            'bank_name'=>$tz['bank_name'],
            'bank_ren'=>$tz['bank_ren'],
            'bank_sn'=>$tz['bank_sn'],
            'tz_name'=>$tz['name'],
            'tz_phone'=>$tz['phone'],
            'tz_sn'=>$tz['sn'],
            'tz_type'=>$tz['type'],
            'tz_category'=>$tz['category'],
            'tz_store_name'=>$tz['store_name'],
            'tz_zhandui'=>$tz['zhandui'],
            //'memo'=>$tz['memo'],
        );
        $res = $curl->post($url,$data);
        return $res['code']==200;
    }
    public function getPayLogOp(){}


    /**
     * 更新账单付款信息
     */
    public function updatePayOp(){
        /** @var shequ_billModel $billModel */
        $billModel = Model("shequ_bill");

        $bills = $billModel->getList(
            array(
                'ob_state' => array('in', array(BILL_STATE_FIRE_PHONIX, BILL_STATE_HANGO, BILL_STATE_CEO,BILL_STATE_PART_PAY,BILL_STATE_PAYING)),//11,10,12,5,13
                'pay_update_time' => array('lt', time() - 3600),
            ), '', 'ob_id ASC','*', 1000);
        if (empty($bills)) return true;
        $obIds = array_column($bills, 'ob_id');
        $billModel->edit(
            array('ob_id' => array('in', $obIds)),
            array('pay_update_time' => time())
        );
        // 查询100个结算单的付款状态
        foreach ($bills as $bill){
            $this->_updateBillTraffic($bill);
        }
        return true;
    }

    private function _updateBillTraffic($bill){

        $billTraffic = $this->_getBillTraffic($bill['ob_id']);
        $traffics = $billTraffic['datas'];
        if (empty($traffics)) return;
        $paidAmount = 0;
        foreach ($traffics as $traffic){
            $paidAmount += $traffic['amount'];
            $this->_addTraffic($bill,$traffic);
        }

        //根据流水判断是否支付完成
        /** @var shequ_billModel $billModel */
        $billModel = Model("shequ_bill");
        $fee = 0;
        /*if($bill['ob_commis_totals']<=0){
            $fee = $bill['ob_result_totals']*0.006;
        }*/
        $total = $bill['ob_result_totals']-$fee;
        if ($paidAmount>=$total&&$bill['ob_state']!=BILL_STATE_SUCCESS){
            $billModel->edit(array('ob_id'=>$bill['ob_id']),
                array(
                'ob_state'=>BILL_STATE_SUCCESS,
                'paid_amount'=>$paidAmount,
                'ob_pay_date'=>time(),
                'ob_pay_content'=>'供应链资金管理支付完成，明细详见付款记录',
            ));
            // 增加余额
            //avaliable_commission
            /** @var shequ_tuanzhangModel $tuanzhangModel */
            $tuanzhangModel = Model('shequ_tuanzhang');
            $tuanzhang = $tuanzhangModel->getOne(array('id'=>$bill['ob_store_id']));
            /** @var memberModel $memberModel */
            $memberModel = Model('member');
            $member = $memberModel->getMemberInfo(array('member_id'=>$tuanzhang['member_id']));
            $res = $memberModel->editMember(array('member_id'=>$tuanzhang['member_id']),array('avaliable_commission'=>array('exp', 'avaliable_commission + '.$paidAmount)));
            /** @var shequ_bill_payModel $trafficModel */
            $trafficModel = Model("shequ_bill_pay");

            $traffic = array(
                'obl_ob_id'=>$bill['ob_id'],
                'obl_pay_date'=>time(),
                //'obl_err_amount'=>$traffic['amount'],
                'obl_success_amount'=>$paidAmount,
                'obl_pay_content'=>'增加用户可提现余额',
                'traffic_id'=>0,
                'attachment'=>'',
                'payment_sn'=>'',
            );
            $trafficModel->addItem($traffic);

        }elseif ($paidAmount>0&&$bill['ob_state']!=BILL_STATE_PART_PAY){
            $billModel->edit(array('ob_id'=>$bill['ob_id']),
                array(
                'ob_state'=>BILL_STATE_PART_PAY,
                'paid_amount'=>$paidAmount,
                'ob_pay_date'=>time(),
                'ob_pay_content'=>'供应链资金管理部分支付，明细详见付款记录',
            ));
        }
        return true;
    }

    private function _getBillTraffic($id){
        $host = 'https://apisupplier.hangomart.com';
        if (C('ON_DEV')) $host = 'http://api.supplier.com';
        $url = $host.'/payable/tuanzhangTraffic/';
        import("Curl");
        $curl = new Curl();
        $curl->setJsonDecoder(function ($response){
            $json_obj = json_decode($response, true);
            if (!($json_obj === null)) {
                $response = $json_obj;
            }
            return $response;
        });
        $res = $curl->get($url,array('bill_id'=>$id,'api_key'=>'c1dca569396ba260fe6a7d552b6b7d75'));
        return $res;
    }

    private function _addTraffic($bill,$traffic){
        if($traffic['amount']<=0) return;
        /** @var shequ_bill_payModel $trafficModel */
        $trafficModel = Model("shequ_bill_pay");
        $hasTraffic = $trafficModel->getCount(array('traffic_id'=>$traffic['id']));
        if ($hasTraffic>0) return true;
        $traffic = array(
            'obl_ob_id'=>$bill['ob_id'],
            'obl_pay_date'=>$traffic['created_at'],
            //'obl_err_amount'=>$traffic['amount'],
            'obl_success_amount'=>$traffic['amount'],
            'obl_pay_content'=>$traffic['memo'],
            'traffic_id'=>$traffic['id'],
            'attachment'=>$traffic['attachment'],
            'payment_sn'=>$traffic['payment_sn'],
            //'purchase_sn'=>$purchase['purchase_sn'],
            //'supplier_name'=>$purchase['supplier_name'],
        );
        $res = $trafficModel->addItem($traffic);
        return $res;
    }


    /**
     * 计算配送单
     */
    public function handleTuanConfigsOp(){
        // 查找当前已经完成的团购，
        /** @var shequ_tuan_configModel $configModel */
        $configModel = Model('shequ_tuan_config');
        // 查找全部已截团的
        $configList = $configModel->getTuanConfigList(array('state'=>shequ_tuan_configModel::STATE_CREATED,'config_end_time'=>array('lt',time()+ORDER_AUTO_CANCEL_TIME*60*60)));
        if (empty($configList)) return;
        foreach ($configList as $config){
            $configModel->edit(array('config_tuan_id'=>$config['config_tuan_id']),array('state'=>shequ_tuan_configModel::STATE_SUCCESS));
            $this->_handleTuanConfig($config);
        }
    }

    private function _handleTuanConfig($config){
        // 获取订单，取消订单，计算配送单和提货单
        /** @var orderModel $orderModel */
        $orderModel = Model('order');
        /** @var shequ_tuanModel $tuanModel */
        $tuanModel = Model('shequ_tuan');
        /** @var shequ_tuan_config_goodsModel $configGoodsModel */
        $configGoodsModel = Model('shequ_tuan_config_goods');
        /** @var shequ_peisongdanModel $psdGoodsModel */
        $psdGoodsModel = Model('shequ_peisongdan');
        /** @var shequ_tihuodanModel $thdGoodsModel */
        $thdGoodsModel = Model('shequ_tihuodan');
        // 取消未达标订单
        $thresholds = $this->area_tuan_threshold;
        // 根据设置，自动退款
        foreach ($thresholds as $area_id => $threshold){
            $condition = array(
                'shequ_tuan_id'=>$config['config_tuan_id'],
                'shequ_area_id'=>$area_id,
                'order_state'=>array('gt',10),
                'order_from'=>8,
            );
            $orderInfo = $orderModel->getOrderGoodsInfo($condition,'SUM(order_amount) as amount');
            if ($orderInfo['amount']<$threshold){
                // 循环退款
                $orders = $orderModel->getOrderList($condition,'','order_id ASC',999999);
                foreach ($orders as $order){
                    $this->_refundTuan($order);
                }
            }
        }
        // 获取团长列表
        $tuanList = $tuanModel->getList(array('config_id'=>$config['config_tuan_id']),'','id ASC','*',99999);
        $configGoodsList = $configGoodsModel->getTuanConfigGoodsList(array('tuan_config_id'=>$config['config_tuan_id']),'','tuan_config_id ASC','*',99999);
        $configGoodsList = array_column($configGoodsList,null,'goods_id');
        $thdGoodsList = array();
        foreach ($tuanList as $tuan){
            $condition = array(
                'shequ_tuan_id'=>$config['config_tuan_id'],
                'shequ_tz_id'=>$tuan['tz_id'],
                'order_state'=>array('gt',10),
                'order_from'=>8,
            );
            // 统计每个团的配送单
            $orders = $orderModel->getOrderList($condition,'','*','order_id ASC',999999);
            if (empty($orders)){
                // 标记成团失败
                $tuanModel->edit(array('id'=>$tuan['id']),array('state'=>shequ_tuanModel::STATE_FAILED));
                continue;
            }
            $tuanModel->edit(array('id'=>$tuan['id']),array('state'=>shequ_tuanModel::STATE_SUCCESS));
            // 根据orders统计商品信息
            $orderIds = array_column($orders,'order_id');
            $orderGoodsList = $orderModel->getOrderGoodsList(array('order_id'=>array('in',$orderIds)));

            $psdGoodsList = array();
            foreach ($orderGoodsList as $orderGoods){
                $goodsId = $orderGoods['goods_id'];
                $configGoods = $configGoodsList[$goodsId];
                if (!$configGoods) continue;

                if (!isset($psdGoodsList[$goodsId])) {
                    $configGoods['cost_price'] = 0;
                    $configGoods['goods_num'] = 0;
                    $configGoods['goods_amount'] = 0;
                    $configGoods['tz_id'] = $tuan['tz_id'];
                    $configGoods['tuan_id'] = $tuan['id'];
                    $psdGoodsList[$goodsId] = $configGoods;
                }
                $psdGoodsList[$goodsId]['goods_num']+=$orderGoods['goods_num'];
                $psdGoodsList[$goodsId]['goods_amount']+=$orderGoods['goods_pay_price'];
                $psdGoodsList[$goodsId]['cost_price']=$orderGoods['goods_cost'];
                if (!isset($thdGoodsList[$goodsId])) {
                    $configGoods['cost_price'] = 0;
                    $configGoods['goods_num'] = 0;
                    $configGoods['goods_amount'] = 0;
                    $thdGoodsList[$goodsId] = $configGoods;
                }
                $thdGoodsList[$goodsId]['goods_num']+=$orderGoods['goods_num'];
                $thdGoodsList[$goodsId]['goods_amount']+=$orderGoods['goods_pay_price'];
                $thdGoodsList[$goodsId]['cost_price']=$orderGoods['goods_cost'];
            }

            foreach ($psdGoodsList as $psdGoods){
                unset($psdGoods['tuan_config_goods_id'],$psdGoods['state'],$psdGoods['is_xianshi']);
                $psdGoodsModel->add($psdGoods);
            }
        }
        // 循环增加提货单数据
        foreach ($thdGoodsList as $thdGoods){
            unset($thdGoods['tuan_config_goods_id'],$thdGoods['state'],$thdGoods['is_xianshi'],$thdGoods['tz_id'],$thdGoods['tuan_id']);
            $thdGoodsModel->add($thdGoods);
        }

    }

    private function _refundTuan($order){
        /** @var RefundService $service */
        $service = Service('Refund');
        /** @var orderModel $orderModel */
        $orderModel = model('order');
        /** @var refund_returnModel $refundModel */
        $refundModel = model('refund_return');
        $refund_array = array ();
        $refund_array ['refund_type'] = '1';  // 类型:1为退款,2为退货
        $refund_array ['seller_state'] = '2'; // 状态:1为待审核,2为同意,3为不同意
        $refund_array ['refund_state'] = '2'; // 状态:1为待审核,2为处理中,3为处理完成
        $refund_array ['order_lock'] = '1'; // 锁定类型:1为不用锁定,2为需要锁定
        $refund_array ['goods_id'] = '0';
        $refund_array ['order_goods_id'] = '0';
        $refund_array ['reason_id'] = '0';
        $refund_array ['reason_info'] = '社区拼团失败，系统自动全额退款';
        $refund_array ['goods_name'] = '订单商品全部退款';
        $refund_array ['refund_amount'] = ncPriceFormat ( $order['order_amount'] );
        $refund_array ['buyer_message'] = '社区拼团失败，系统自动全额退款';
        $refund_array ['seller_message'] = '社区拼团失败，系统自动同意退款';
        $refund_array ['admin_message'] = '社区拼团失败，系统自动处理退款';
        $refund_array['admin_name'] = '系统';
        $refund_array ['refund_way'] = in_array($order['payment_code'],array('wxpay','wx_jsapi','wx_saoma','alipay')) ? $order['payment_code'] : 'predeposit';
        $refund_array ['add_time'] =  $refund_array ['seller_time'] =  $refund_array ['admin_time'] = time ();
        $refund_array['operation_type']=3;
        $refund_array ['pic_info'] = '';
        $refund_id = $refundModel->addRefundReturn($refund_array,$order);
        $refundInfo = $refundModel->getRefundReturnInfo(array('refund_id'=>$refund_id));
        $detailDetail = $refundModel->getDetailInfo(array('refund_id'=>$refund_id));

        if (empty($detailDetail)) {
            $refundModel->addDetail($refundInfo,$order);
            $detailDetail = $refundModel->getDetailInfo(array('refund_id'=>$refund_id));
        }
        if (in_array($order['payment_code'],array('wxpay','wx_jsapi','wx_saoma','alipay'))) {
            try {
                $detailDetail = $service->apiRefund($detailDetail);
            } catch (Exception $e) {
                $this->log('自动退款失败_'. $e->getMessage());
                return;
            }
        }
        $refund['pay_amount'] = $detailDetail['pay_amount'];
        $res = $refundModel->editOrderRefund($refundInfo,'系统');
        if($res){
            $refund_array = array();
            $refund_array['refund_state'] = 3;
            $refundModel->editRefundReturn(array('refund_id'=>$refund_id), $refund_array);

            // 发送买家消息
            $param = array();
            $param['code'] = 'refund_return_notice';
            $param['member_id'] = $refund['buyer_id'];
            $param['param'] = array(
                'refund_url' => urlShop('member_refund', 'view', array('refund_id' => $refund['refund_id'])),
                'refund_sn' => $refund['refund_sn']
            );
            QueueClient::push('sendMemberMsg', $param);
            $this->log('退款确认，退款编号'.$refund['refund_sn']);
        }
    }
}