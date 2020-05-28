<?php

/**
 * Created by PhpStorm.
 * 开发文档地址：http://open.suning.com/ospos/apipage/toDocContent.do?menuId=23
 *'appKey' => 'f9ebbfe7100e089b18c1f85513e74678',
  'appSecret'  => '03e372c1b0c9a2440844740262cf1dd4',
  'url' => 'http://openpre.cnsuning.com/api/http/sopRequest',
 */
class suningnongguCron
{
    private $_config = array(
        'appKey'=>'fcefd0f8a36d2e1b5ea948d83bb137be',
        'appSecret'=>'8893097b802699ca0df89de12b3c945b',
        'url'=>'http://open.suning.com/api/http/sopRequest',
    );
    private $_service;
    public static $source = "suningnonggu";
    public static $onlineDate = "2017-08-04 9:30:00"; //上线日期，不保存上线日之前的订单

    public function __construct($getRel = 1)
    {
        import('Curl');
        $this->timestamp = TIMESTAMP;
        $model_member = TModel("Member");
        $conditon = array();
        $condition = array("member_name" => self::$source);
        $row = $model_member->where($condition)->find();
        $this->member_id = $row['member_id'];
        $model_member->execute("set wait_timeout=1000");
        //商品映射
        if ($getRel) {
            $this->rel = $this->getGoodsRel();
            $this->oldRel = array();
        }
    }

    //获取商品映射
    private function getGoodsRel()
    {
        $result = TModel("B2cCategory")->where(array('uid' => $this->member_id))->select();
        $rel = $result ? array_column($result, 'pid', 'fxpid') : array();
        return $rel;
    }

    private function _getOrders($create_time = null, $page = 1, $status='10')
    {
        $create_time = explode('|' , $create_time);
        $param = array(
            'method'=>'suning.custom.order.query',
            'orderStatus' => $status,
            'startTime' => date('Y-m-d H:i:s', $create_time[0]),
            'endTime' => date('Y-m-d H:i:s', $create_time[1] ),
            'pageNo' => "{$page}",
            'pageSize'=>"30",
        );
        $res = $this->_sendRequest($param);
        if (is_array($res['sn_body']['orderQuery'])) {
            if (isset($res['sn_head']['totalSize']) && $res['sn_head']['totalSize'] > $page * 30) $res['next'] = $page + 1;
            return $res;
        }
        return array('next' => false, 'count' => 0);
    }

    /* 获取签名 @param array $param 请求参数数据集合*/
    private function _getSign( $params = array() ){
        $signString = '';
        foreach($params as $k => $v){
            $signString .= $v;
        }
        unset($k, $v);
        $signString = md5($signString);
        // 组装头文件信息
        $signDataHeader = array(
            "Content-Type: text/xml; charset=utf-8",
            "AppMethod:".$params['method'],
            "AppRequestTime:" .date('Y-m-d H:i:s',time()),
            "Format:json",
            "signInfo:" . $signString,
            "AppKey:" . $params['app_key'],
            "VersionNo:".$params['api_version'],
            "User-Agent:suning-sdk-php" ,
            "Sdk-Version:suning-sdk-php-beta0.1 "
        );
        return $signDataHeader;
    }

    public function  getBizName($bizname){
        $data=array(
            'suning.custom.logisticcompany.query'=>'logisticCompany',
            'suning.custom.order.query'=>'orderQuery',
            'suning.custom.batchrejected.query'=>'batchQueryRejected',
            'suning.custom.orderdelivery.add'=>'orderDelivery',
            'suning.custom.order.get'=>'orderGet',
            'suning.custom.item.query'=>'item'
        );
        return $data[$bizname];
    }

    private function _sendRequest($param)
    {
        $url = $this->_config['url'];
        $header = array(
            'secret_key'=>$this->_config['appSecret'],
            'method'=>$param['method'],
            'date'=>date('Y-m-d H:i:s',time()),
            'app_key'=>$this->_config['appKey'],
            'api_version'=>'v1.2',
        );
        unset($param['method']);
        $paramsArray = array('sn_request' => array('sn_body' => array(
            "{$this->getBizName($header['method'])}" => $param
        )));
        $apiParams=json_encode($paramsArray);
        $header['post_field']=base64_encode($apiParams);
        $signHeader=$this->_getSign($header);
        $curl = new Curl();
        $curl->setOpt(CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        $curl->setOpt(CURLOPT_HTTPHEADER, $signHeader);
        $curl->post($url, $apiParams);
        if ($curl->error) {
            Log::record('苏宁易购  HTTP 请求失败! Error:' . $curl->errorCode . ': ' . $curl->errorMessage);
            throw new Exception('苏宁易购 HTTP 请求失败! Error:' . $curl->errorCode . ': ' . $curl->errorMessage);
        }
        $res = $curl->response;
        $res = json_decode($res ,true);
        return $res['sn_responseContent'];
    }

    private function _prepareOrder($source)
    {
        // TODO 检查订单是否存在，若存在直接返回false，否则准备数据，并返回数据
        $goodsList = $source['orderDetail'];
        $items = array();
        $hasError = false;
        $finalPrice=0;
        $discount=0;
        $voucher_price=0;
        $shipping_fee=0;
        /*表示对订单使用的券用积分类型：5998-店铺优惠券、6998-联合0元购券、7998-0元购券、8012-积分抵现、9994-优惠券、9995-优惠券、10001-云券、10002-限品类云券、10003-店铺云券、10004-易券、10005-限品类易券、10006-店铺易券、10009-无敌券*/
        /*扣除店铺承担，加上平台承担*/
        $voucher=array('6998','7998','8012','9994','9995','10001','10002','10004','10005','10009');
        foreach ($goodsList as $goods) {
            if (isset($this->rel[$goods['productCode']]) && $this->rel[$goods['productCode']]) {
                $goods_id = $this->rel[$goods['productCode']];
            } else {
                $this->_error($source['orderCode'], "分销商品 {$goods['productName']} ({$goods['productCode']}) 没有映射");
                $hasError = true;
                continue;
            }
            $items[] = array(
                'goods_id' => $goods_id,
                'name' => $goods['productName'],
                'num' => $goods['saleNum'],
                'price' => $goods['unitPrice'],
                'fxpid' => $goods['productCode'],
                'oid' => isset($goods['productCode']) ? $goods['productCode']:$source['orderCode'],//必须添加否则fenxiao_sub无法查询
            );
            foreach($goods['paymentList'] as $item){
               if(in_array($item['banktypecode'],$voucher)){
                   $voucher_price+=$item['payamount'];
               }
            }
            //实际收款金额
            $finalPrice+=$goods['payAmount']+$voucher_price;
            //优惠金额
            $discount+=$goods['unitPrice']*$goods['saleNum']-$goods['payAmount']-$voucher_price;
            $shipping_fee+=floatval($goods['transportFee']);
        }
        if (empty($items) || $hasError) return false;
        if(!isset($source['provinceName']) || empty($source['provinceName'])){
            $this->_error($source['orderCode'], "分销订单 ({$source['orderCode']}) 的收货地址省份信息获取失败，地区数据：{$source['provinceName']}");
        }else if(!isset($source['cityName']) || empty($source['cityName'])){
            $this->_error($source['orderCode'], "分销订单 ({$source['orderCode']}) 的收货地址城市信息获取失败，地区数据：{$source['cityName']}");
        }else if(!isset($source['districtName']) || empty($source['districtName'])){
            $this->_error($source['orderCode'], "分销订单 ({$source['orderCode']}) 的收货地址县/市/区信息获取失败，地区数据：{$source['districtName']}");
        }
        $detail = array();
        $detail['order_sn'] = $source['orderCode']; //分销系统订单编号
        $detail['buy_id'] = $this->member_id; //分销商用户编号
        $detail['receiver'] = $source['customerName'];//收件人
        $detail['provine'] = $source['provinceName'];
        $detail['city'] = $source['cityName'];
        $detail['area'] = $source['districtName'];
        $detail['address'] = $source['customerAddress'];
        $detail['mobile'] = $source['mobNum']; //手机号码
        $detail['remark'] =$source['buyerOrdRemark'];//用户留言
        $detail['payment_code'] = 'fenxiao';
        $detail['order_time'] = strtotime($source['orderSaleTime']);//下单时间，时间戳
        $detail['item'] = $items;
        $detail['amount'] =$finalPrice;//订单最后价格
        $detail['discount'] = $discount;
        $detail['platform'] = 'new';
        $detail['shipping_fee']=$shipping_fee;//运费
        return $detail;
    }

    /*获得单个订单的信息*/
    public function getOneOrderDetail( $orderCode =''){
        $param = array(
            'method' => 'suning.custom.order.get',
            'orderCode' => $orderCode,
        );
        $res = $this->_sendRequest($param);
        return $res;
    }

    /**
     * 获得订单完成状态
     * @param string $orderSNs 批量请用半角逗号分开
     * @return mixed
     */
    function getOrderStatus($orderSNs)
    {
        $orderSNs = explode(',', $orderSNs);
        $res = array(
            'orderStatus' => array(
                'result' => 1,
                'list' => array()
            )
        );
        foreach ($orderSNs as $fx_order_id) {
            //查询接口
            $orderDetail = $this->getOneOrderDetail($fx_order_id);
            //组装所有分销渠道该接口的统一的返回数据格式
            $item = array(
                'orderSn' => $orderDetail['sn_body']['orderGet']['orderCode'],
                'orderStatus' => $orderDetail['sn_body']['orderGet']['orderTotalStatus'] == '30' ? 3 : 0,
                'refundStatus' => 0,
            );
            $res['orderStatus']['list'][] = $item;

        }
        $res['orderStatus']['result'] = 1;
        return $res;
    }

    /**
     * 获取订单列表
     * @param array $params
     */
    public function orderlist($params = array())
    {
        $service = $params['service'];
        $begin = isset($params['begin']) ? strtotime($params['begin']) : time() - 7 * 24 * 3600;
        $begin = $begin < strtotime(self::$onlineDate) ? strtotime(self::$onlineDate) : $begin;
        $createTime = isset($params['end']) ? $begin . '|' . strtotime($params['end']) : $begin . '|' . time();
        $p = 1;
        do {
            $res = $this->_getOrders($createTime, $p);
            if (!empty($res['sn_body']['orderQuery'])) {
                $sns = $service->getSavedidByApiorderno(array_column($res['sn_body']['orderQuery'],'orderCode'));
                $items = array();
                foreach ($res['sn_body']['orderQuery']  as $orders =>$order) {
                    if (in_array($order['orderCode'], $sns)) {
                        continue;
                    }
                    if ($item = $this->_prepareOrder($order)) $items[] = $item;
                }
                if (!empty($items)) $createRes = $service->doCreateOrder($items);
            }
        } while ($p = $res['next']);
    }

    //获取商品sku列表
    public function getSkuList($params = array()){
        $page_no = $params['page_no'];
        $page_size = $params['page_size'];
        $param=array(
            'method'=>'suning.custom.item.query',
            'status'=>'2',//处理状态。1：正在处理；2：处理成功；3：处理失败；4：审核不通过。
            'pageNo'=>"{$page_no}",
            'pageSize'=>"{$page_size}",
        );
        $res= $this->_sendRequest($param);
        $data_out = array();
        foreach($res['sn_body']['item'] as $v){
                $item['goods_name'] =$v['productName'];
                $item['sku_id'] = $v['productCode'];
                $item['source'] = 'suningnonggu';
                $data_out[] = $item;
        }
        return $data_out;
    }

    /*获取发货的orderlinuber*/
    public function  getshipstring($ordercode){
        $orderdata=$this->getOneOrderDetail($ordercode);
        foreach($orderdata['sn_body']['orderGet']['orderDetail'] as $item){
            $param['sendDetail']['productCode'][]=$item['productCode'];
            $param['orderLineNumbers']['orderLineNumber'][]=$item['orderLineNumber'];
        }
        return $param;
      }

    public function push_ship($params){
        $param=array(
            'method'=>'suning.custom.orderdelivery.add',
            'orderCode'=>"{$params['orderno']}",
            'expressCompanyCode'=>"{$this->getShipId($params['logi_name'])}",
            'expressNo'=>"{$params['logi_no']}",
            'deliveryTime'=>date('Y-m-d H:i:s',time()),
        );
        $shipParam=$this->getshipstring($params['orderno']);
        $param=array_merge($param,$shipParam);
        $res=$this->_sendRequest($param);
        if($res['sn_body']['orderDelivery']['sendDetail'][0]['sendresult']=='Y'){
            $res = json_encode(array(
                'succ' => '1',
                'msg' => '发货成功'
            ));
        }else{
            $res = json_encode(array(
                'succ' => '0',
                'msg' => $res['sn_error']['error_code']
            ));
        }
        return $res;
    }

    /**
     * 漏单检测，凌晨检测前3天的未发货订单是否已保存为汉购网订单
     */
    public function checkUnsaveOrder($params)
    {
        $hour = date('G');
        if ($hour >= 9 && $params['preDay'] == 1) {
            $params['preDay'] = 1;
        }
        log::selflog("check unsave order begin", self::$source);
        $limit = $params['preDay'] == 0 ? 1 : $params['preDay'];
        for ($i = $limit; $i >= 1; $i--) {
            $b_time = TIMESTAMP - $i * 24 * 3600;
            $e_time = $b_time + 24 * 3600;
            $params['begin'] = date('Y-m-d H:i:s', $b_time);
            $params['end'] = date('Y-m-d H:i:s', $e_time);
            $this->orderlist($params);
        }
    }

    /*获取退货的订单*/
    public function getRefundOrder($service)
    {
        $page ='1';
        $begin = isset($_GET['begin']) ? strtotime($_GET['begin']):time() - 7 * 24 * 3600;
        $begin = $begin < strtotime(self::$onlineDate) ? strtotime(self::$onlineDate) : $begin;
        $end=isset($_GET['end']) ? strtotime($_GET['end']):time();
        $createTime = $begin.'|'.$end;
        $this->_service = $service;
        do{
            $res=$this->_getReturnOrder($createTime,$page);
            if (!empty($res['sn_body']['batchQueryRejected'])) {
                $items = $this->_prepareRefund($this->_filterRefunds($res['sn_body']['batchQueryRejected']));
                if (!empty($items)) $this->_service->createRefund(array('new' => $items));
            }
        }while($page=$res['next']);
        return true;
    }

    /*获取退货订单*/
    public function _getReturnOrder($modified_time=null,$page=1){
        $create_time=explode('|',$modified_time);
        $param=array(
            'method'=>'suning.custom.batchrejected.query',
            'startTime'=>date('Y-m-d H:i:s',$create_time[0]),
            'endTime'=>date('Y-m-d H:i:s',$create_time[1]),
            'pageNo'=>"{$page}",
            'pageSize'=>'30'
        );
        $res=$this->_sendRequest($param);
        if (is_array($res['sn_body']['batchQueryRejected'])) {
            if (isset($res['sn_head']['totalSize']) && $res['sn_head']['totalSize'] > $page*30) $res['next'] = $page + 1;
            return $res;
        }
        return array('next' => false, 'count' => 0);
    }

    /**
     * 过滤不需要处理的退款
     * @param $items array
     * @return array
     */
    private function _filterRefunds($items)
    {
        $refunds = array();
        /** 若订单未发货，但是部分退款，剔除 */
        $fxIds = array_column($items, 'orderCode');
        /** @var orderModel $orderModel */
        $orderModel = Model('order');
        $payOrders = $orderModel->getOrderList(array('fx_order_id' => array('in', $fxIds), 'order_state' => ORDER_STATE_PAY));
        $orders = $orderModel->getOrderList(array('fx_order_id' => array('in', $fxIds)));
        $rel = array_column($payOrders, 'order_amount', 'fx_order_id');
        $orderIdRel = array_column($orders, 'order_id', 'fx_order_id');
        /** 将退款格式转换未二级格式【order=》【goods=》【】】】 */
        foreach ($items as $item) {
            // 处理商品映射关系
                if (!isset($this->rel[$item['productCode']]) || empty($this->rel[$item['productCode']])) {
                    $this->_error($item['orderCode'], "_filterRefunds,分销商品 ({$item['productCode']}) 没有配置商品映射，无法生成退款");
                    continue;
                }
                $item['goods_id'] =$this->rel[$item['productCode']];
                $item['order_id'] = $orderIdRel["{$item['orderCode']}"];
                if (!isset($refunds["{$item['orderCode']}"])) $refunds["{$item['orderCode']}"] = array();
                $refunds["{$item['orderCode']}"][$item['goods_id']] = $item;
        }
        if (!empty($payOrders)) {
            foreach ($rel as $fxOrderId => $order_amount) {
                echo "过滤{$fxOrderId}\n";
                $refund_total = array_sum(array_column($refunds[$fxOrderId], 'returnMoney'));
                $dealMoney = array_sum(array_column($refunds[$fxOrderId], 'dealMoney'));
                echo "退款金额{$refund_total}\n";
                echo "订单在平台实际交易金额{$dealMoney}\n";
                echo "订单在汉购网加上平台优惠的金额交易金额{$dealMoney}\n";
                //if (ncPriceFormat($refund_total) != ncPriceFormat($order_amount)) {
                if (ncPriceFormat($refund_total) != ncPriceFormat($dealMoney)) {
                    unset($refunds[$fxOrderId]);
                    $this->_error($fxOrderId, "未发货分销订单不是全额退款，无法生成退款");
                } else { //全额退款商品有多个时，只提交一次退款
                    if (count($refunds[$fxOrderId]) > 1) {
                        $tmp_key = current(array_keys($refunds[$fxOrderId]));
                        $tmp_value = current(array_values($refunds[$fxOrderId]));
                        $refunds[$fxOrderId] = array($tmp_key => $tmp_value);
                    }
                }
            }
        }
        return $refunds;
    }

    /**
     * 准备退款数据
     * @param $source array
     * @return bool|array
     */
    private function _prepareRefund($items)
    {
        //过滤掉非全额退款订单，不做处理
        if (empty($items)) return array();
        $order_sns = array_keys($items);
        $new_fsmodel = TModel("B2cOrderFenxiaoSub");
        $condition['orderno'] = array('in', $order_sns);
        $re = $new_fsmodel->where($condition)->select();
        $result = $re ? $re : array();
        $newRefund = array();
        $returnModel = TModel('RefundReturn');
        foreach ($result as $suborder) {
            $orderno = $suborder['orderno'];
            $goods_id = $suborder['product_id'];
            //匹配未付款子订单
            $ordersn = $this->_service->_getFxorderSn($orderno, $goods_id);
            if (!$ordersn) continue;
            //检查子订单是否已申请退款或取消订单
            $filter = array();
            $filter['order_sn'] = $ordersn;
            $filter['goods_id'] = array('in', array(0, $goods_id));
            if ($returnModel->where($filter)->count() > 0) {
                continue;
            }
            $data = array();
            $data['reason_id'] = 100; //退款退货理由 整型
            $data['refund_type'] = 3;
            $data['return_type'] = 3;
            if($items[$orderno][$goods_id]['refundtype']==""){ //为空代表退货
                $data['return_type']=2;
                $data['refund_type'] = 2;
            }elseif($items[$orderno][$goods_id]['refundtype']=="1"){ //为1代表仅退款
                $data['return_type']=1;
                $data['refund_type'] = 1;
            }
            $data['seller_state'] = 1; //卖家处理状态:1为待审核,2为同意,3为不同意,默认为1
            $data['refund_amount'] = $items[$orderno][$goods_id]['returnMoney'];//退款金额
            $data['goods_num'] =1;//商品数量
            $data['buyer_message'] = $items[$orderno][$goods_id]['returnReason']=="" ? "无":$items[$orderno][$goods_id]['returnReason'];  //申请原因
            $data['ordersn'] = $ordersn;  //汉购网订单编号
            $data['goods_id'] = $suborder['product_id']; //商品编号
            $data['create_time'] = strtotime($items[$orderno][$goods_id]['applyTime']);  //售后订单产生时间
            $newRefund[] = $data;
        }
        return $newRefund;
    }
    /**
     * 跟踪退款单状态
     * afterSalesStatus 1.买家申请退款，待商家处理;4.商家同意退款，退款中；9.商家驳回退款，待买家处理;
     * 12.买家逾期未处理，退款失败;3.平台处理中;4.平台同意退款，退款中;7.平台拒绝退款，退款关闭;5.退款成功;
     * 6.用户撤销了退款申请
     * @param $service FenxiaoService
     * @return boolean
     */
    public function traceRefund($service)
    {
        $p ='1';
        $begin = isset($_GET['begin']) ? strtotime($_GET['begin']):time() - 7 * 24 * 3600;
        $begin = $begin < strtotime(self::$onlineDate) ? strtotime(self::$onlineDate) : $begin;
        $end=isset($_GET['end']) ? strtotime($_GET['end']):time();
        $createTime = $begin.'|'.$end;
        $this->_service = $service;
        do {
            $res = $this->_getReturnOrder($createTime,$p);
            if (!empty($res['sn_body']['batchQueryRejected'])) {
                $items = $this->_filterTraceRefunds($res['sn_body']['batchQueryRejected']);
                if (!empty($items)) $this->_updateRefund($items);
            }
        } while ($p = $res['next']);
        return true;
    }

    private function _updateRefund($items)
    {
        //查找未完结的卷皮退款订单
        $refundModel = TModel("RefundReturn");
        /** @var RefundService $refundService */
        $refundService = Service("Refund");
        /** @var Model $model */
        $model = Model();
        //根据退款状态做相应处理，处理取消退款以及退款完成的订单，其他状态保持不变不做处理
        foreach ($items as $orderId => $refunds) {
            foreach ($refunds as $item) {
                $refund = $refundModel->where(array('order_id' => $item['order_id'], 'goods_id' => $item['goods_id']))->find();
                $params = array(
                    'refund_id' => $refund['refund_id'],
                    'op_id' => $this->member_id,
                    'op_name' => self::$source
                );
                $msg = "";
                //退款完成
                if ($item['statusDesc'] == '退款成功') {
                    v($params, 0);
                    $method = 'confirm_refund';
                }
                try {
                    $model->beginTransaction();
                    if(!isset($method) || $method==""){
                        continue;
                    }
                    $res = $refundService->$method($params, $msg);
                    if (!$res) {
                        throw new Exception($msg);
                    }
                    $model->commit();
                } catch (Exception $e) {
                    $model->rollback();
                    $msg = $e->getMessage();
                }
                v($msg, 0);
            }
        }
    }

    /**
     * 过滤退款跟踪数据
     * @param $items
     * @return array
     */
    private function _filterTraceRefunds($items)
    {
        $refunds = array();
        /** 若订单未发货，但是部分退款，剔除 */
        $fxIds = array_column($items, 'orderCode');
        /** @var orderModel $orderModel */
        $orderModel = Model('order');
        $payOrders = $orderModel->getOrderList(array('fx_order_id' => array('in', $fxIds),
            'order_state' => ORDER_STATE_PAY));
        $orders = $orderModel->getOrderList(array('fx_order_id' => array('in', $fxIds)));
        $rel = array_column($payOrders, 'order_amount', 'fx_order_id');
        $orderIdRel = array_column($orders, 'order_id', 'fx_order_id');
        /** 将退款格式转换未二级格式【order=》【goods=》【】】】 */
        foreach ($items as $item) {
            // 处理商品映射关系
                if (!isset($this->rel[$item['productCode']]) || empty($this->rel[$item['productCode']])) {
                    $this->_error($item['orderCode'], "分销商品 ({$item['productCode']}) 没有配置商品映射，无法生成退款");
                    continue;
                }
                if ($item['statusDesc'] == '退款待处理') continue;
                $item['goods_id'] =$this->rel[$item['productCode']];
                $item['order_id'] = $orderIdRel["{$item['orderCode']}"];
                if (!isset($refunds["{$item['orderCode']}"])) $refunds["{$item['orderCode']}"] = array();
                $refunds["{$item['orderCode']}"][$item['goods_id']] = $item;
        }
        return $refunds;
    }

    /*检查是否发货*/
    public function checkUnshipOrder()
    {
        $hour = date('G');
        //凌晨检测最近3天，其他时间检测最近3小时
        $updateTime = $hour >= 6 ? TIMESTAMP - 3600 * 3 : TIMESTAMP - 3600 * 24 * 3;
        $comm_where = array();
        $comm_where['shipping_time'] = array('gt', $updateTime);
        $result = Model('order_common')->where($comm_where)->select();
        if (!$result) die('no result');
        $oids = array_column($result, 'order_id');
        $oid_expressid_rels = array_column($result, 'shipping_express_id', 'order_id');
        $where = array();
        $where['order_id'] = array('in', $oids);
        $where['buyer_id'] = $this->member_id;
        $orders = TModel('orders')->where($where)->select();
        if (!$orders) die('no orders');
        foreach ($orders as $order) {
            $fx_order_id = $order['fx_order_id'];
            $goodsWhere = array();
            $goodsWhere['order_id'] = $order['order_id'];
            $order_items = TModel('order_goods')->where($goodsWhere)->select();
            if (!$order_items) continue;
            $orderDetail = $this->getOneOrderDetail($fx_order_id);
            if ($orderDetail['sn_body']['orderGet']['orderTotalStatus']!="10") continue;
            $express = rkcache('express', true);
            /** 判断当前商品是否需要重新发货 */
            $express_id = $oid_expressid_rels[$order['order_id']];
            $data = array();
            $data['orderno'] = $fx_order_id;
            $data['logi_no'] = $order['shipping_code'];
            $data['logi_name'] = $express[$express_id]['e_name'];
            if(!empty($data['logi_no'])){
                $this->push_ship($data);
            }
        }
    }

    /*获取物流*/
    /*public static  $orderdata='';
     public function deliverdata($page='1'){
         $param=array(
             'method'=>'suning.custom.logisticcompany.query',
             'pageNo'=>$page,
             'pageSize'=>'50',
         );
         $data=$this->_sendRequest($param);
         if($data['sn_head']['totalSize']>(intval($page)-1)*50){
             $page=intval($page)+1;
             foreach($data['sn_body']['logisticCompany'] as $item){
                self::$orderdata.=$item['expressCompanyCode'].":".$item['expressCompanyName']."<br/>\n";
             }
             $this->deliverdata("{$page}");
         }
        print_r(self::$orderdata);
     }*/

    public function getShipId($shipname){
        $data=array(
            '包裹平邮'=>'B03',
            'DHL快递'=>'D01',
            '大田物流'=>'D03',
            '德邦物流'=>'D04',
            'EMS'=>'E01',
            '飞康达'=>'F01',
            'FedEx(国际)'=>'F02',
            '港中能达'=>'G02',
            '挂号信'=>'B03',
            '共速达'=>'G04',
            '百世汇通'=>'H01',
            '佳吉快运'=>'J01',
            '佳怡物流'=>'J02',
            '急先达'=>'J04',
            '快捷速递'=>'K01',
            '龙邦快递'=>'L01',
            '联邦快递'=>'L03',
            '联昊通'=>'L02',
            '全一快递'=>'Q01',
            '全峰快递'=>'Q03',
            '全日通'=>'Q04',
            '申通快递'=>'S01',
            '顺丰快递'=>'S02',
            '速尔快递'=>'S03',
            'TNT快递'=>'T01',
            '天天快递'=>'T02',
            '天地华宇'=>'H02',
            'UPS快递'=>'U02',
            '新邦物流'=>'X03',
            '信丰物流'=>'X04',
            '圆通快递'=>'Y01',
            '韵达快递'=>'Y02',
            '邮政包裹'=>'B03',
            '优速快递'=>'Y10',
            '中通快递'=>'Z01',
            '中铁快运'=>'Z02',
            '宅急送'=>'Z04',
            '中邮物流'=>'Z03',
            '国通快递'=>'G05',
            '安能物流'=>'B07'
        );
        return !empty($data[$shipname]) ? $data[$shipname]:$data["EMS"];
    }

    /*保存错误信息到日志table*/
    public function _error($orderno, $errorinfo, $log_type = 'order')
    {
        $model = Model("b2c_order_fenxiao_error");
        $where = array(
            'orderno' => $orderno,
            'error' => $errorinfo
        );
        if ($model->where($where)->count() > 0) return;
        $data = array(
            'orderno' => $orderno,
            'error' => $errorinfo,
            'order_time' => 0,
            'log_time' => TIMESTAMP,
            'sourceid' => $this->member_id,
            'source' => self::$source,
            'log_type' => $log_type
        );
        $model->insert($data);
    }
}