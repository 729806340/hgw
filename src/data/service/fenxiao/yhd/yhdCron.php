<?php
/**
 * Created by PhpStorm.
 * User: houshanshan
 * Date: 2017/6/21
 * Time: 10:28
 * 文档地址：http://open.yhd.com/doc2/apiDetail.do?apiName=yhd.logistics.deliverys.company.get
 */
class yhdCron
{
    private $_config = array(
        'appKey' => '10220017062100004508',
        'sessionKey' => '4f9b5f576ed4a668a33fa856576163e1',
        'RefreshKey' => 'f6c39926a7c30b32dd1ee4fcb9385d61',
        'appSecret'  => '64a08494c93c128386df471c3f36f92d',
        'url' => 'http://openapi.yhd.com/app/api/rest/router',
    );
    private $_service;
    public static $source = "yhd";
    public static $onlineDate = "2017-6-29 15:00:00"; //上线日期，不保存上线日之前的订单
    //获取商品映射
    private function getGoodsRel()
    {
        $result = TModel("B2cCategory")->where(array('uid' => $this->member_id))->select();
        $rel = $result ? array_column($result, 'pid', 'fxpid') : array();
        return $rel;
    }
    /* 获取签名 @param array $param 请求参数数据集合*/
    private function _getSign( $param = array() ){
        $sign = $this->_config['appSecret'];
        ksort($param);
        reset($param);
        foreach($param as $k=>$v){
            $sign .=$k.$v;
        }
        $sign .= $this->_config['appSecret'];
        $sign  = md5($sign);
        return $sign;
    }

    private function _getHeader(){
        $header = array(
            'appKey'=>$this->_config['appKey'],
            'sessionKey'=>$this->_config['sessionKey'],
            'format'=>'json',
            'ver'=>'1.0',
            'timestamp'=>date('Y-m-d H:i:s',time()),
        );
        return $header;
    }

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

    private function _sendRequest($param)
    {
        $url = $this->_config['url'];
        $header = $this->_getHeader();
        $post_data = array_merge($header,$param);
        $post_data['sign'] = $this->_getSign($post_data);
        $curl = new Curl();
        $curl->setOpt(CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        $curl->post($url, $post_data);
        if ($curl->error) {
            Log::record('一号店 HTTP 请求失败! Error:' . $curl->errorCode . ': ' . $curl->errorMessage);
            throw new Exception('一号店 HTTP 请求失败! Error:' . $curl->errorCode . ': ' . $curl->errorMessage);
        }
        $res = $curl->response;
        $res = json_decode($res ,true);
        if ($res['response']['errorCount'] > 0) {
            if($res['response']['errInfoList']['errDetailInfo'][0]['errorDes']!="订单列表信息不存在"){
                $res['info'] = $res['response']['errInfoList']['errDetailInfo'][0]['errorDes']." ".$res['response']['errInfoList']['errDetailInfo'][0]['pkInfo'];
                Log::record('一号店返回错误! method:' . $post_data['method'] . '; Info:' . $res['info']);
            }
        }
        return $res['response'];
    }

    private function _getOrders($create_time = null, $page = 1, $status)
    {
        $create_time = explode('|' , $create_time);
        $param = array(
            'method' => 'yhd.orders.get',
            'orderStatusList' => $status,
            'dateType'=>2,
            'startTime' => date('Y-m-d H:i:s', $create_time[0]),
            'endTime' => date('Y-m-d H:i:s', $create_time[1] ),
            'curPage' => $page,
            'pageRows'=>50,
        );
        $res = $this->_sendRequest($param);
        if (is_array($res)) {
            if (isset($res['totalCount']) && $res['totalCount'] > $page * 50) $res['next'] = $page + 1;
            return $res;
        }
        return array('next' => false, 'count' => 0);
    }

    private function _prepareOrder($source)
    {
        // TODO 检查订单是否存在，若存在直接返回false，否则准备数据，并返回数据
        $goodsList = $source['orderInfo']['orderItemList']['orderItem'];
        $items = array();
        $hasError = false;
        foreach ($goodsList as $goods) {
            if (isset($this->rel[$goods['productId']]) && $this->rel[$goods['productId']]) {
                $goods_id = $this->rel[$goods['productId']];
            } else {
                $this->_error($source['orderInfo']['orderDetail']['orderCode'], "分销商品 {$goods['productCName']} ({$goods['productId']}) 没有映射");
                $hasError = true;
                continue;
            }
            if(count($items[$goods_id])>0){
              $items[$goods_id]['num']+=$goods['orderItemNum'];
                continue;
            }
            $items[$goods_id] = array(
                'goods_id' => $goods_id,
                'name' => $goods['productCName'],
                'num' => $goods['orderItemNum'],
                'price' => $goods['orderItemPrice'],
                'fxpid' => $goods['productId'],
                'oid' => isset($goods['productId']) ? $goods['productId']:$source['orderInfo']['orderDetail']['orderCode'],//必须添加否则fenxiao_sub无法查询
            );
        }
        if (empty($items) || $hasError) return false;
        if(!isset($source['orderInfo']['orderDetail']['goodReceiverProvince']) || empty($source['orderInfo']['orderDetail']['goodReceiverProvince'])){
            $this->_error($source['orderInfo']['orderDetail']['orderCode'], "分销订单 ({$source['orderCode']}) 的收货地址省份信息获取失败，地区数据：{$source['orderInfo']['orderDetail']['goodReceiverProvince']}");
        }else if(!isset($source['orderInfo']['orderDetail']['goodReceiverCity']) || empty($source['orderInfo']['orderDetail']['goodReceiverCity'])){
            $this->_error($source['orderInfo']['orderDetail']['orderCode'], "分销订单 ({$source['orderCode']}) 的收货地址城市信息获取失败，地区数据：{$source['orderInfo']['orderDetail']['goodReceiverCity']}");
        }else if(!isset($source['orderInfo']['orderDetail']['goodReceiverCounty']) || empty($source['orderInfo']['orderDetail']['goodReceiverCounty'])){
            $this->_error($source['orderInfo']['orderDetail']['orderCode'], "分销订单 ({$source['orderCode']}) 的收货地址县/市/区信息获取失败，地区数据：{$source['orderInfo']['orderDetail']['goodReceiverCounty']}");
        }
        $detail = array();
        $detail['order_sn'] = $source['orderInfo']['orderDetail']['orderCode']; //分销系统订单编号
        $detail['buy_id'] = $this->member_id; //分销商用户编号
        $detail['receiver'] = $source['orderInfo']['orderDetail']['goodReceiverName'];//收件人
        $detail['provine'] = isset($source['orderInfo']['orderDetail']['goodReceiverProvince'])?$source['orderInfo']['orderDetail']['goodReceiverProvince']:'';
        $detail['city'] = isset($source['orderInfo']['orderDetail']['goodReceiverCity'])?$source['orderInfo']['orderDetail']['goodReceiverCity']:'';
        $detail['area'] = isset($source['orderInfo']['orderDetail']['goodReceiverCounty'])?$source['orderInfo']['orderDetail']['goodReceiverCounty']:'';
        $detail['address'] = $source['orderInfo']['orderDetail']['goodReceiverAddress'];
        $detail['mobile'] = !empty($source['orderInfo']['orderDetail']['goodReceiverMoblie']) ? $source['orderInfo']['orderDetail']['goodReceiverMoblie']:$source['orderInfo']['orderDetail']['goodReceiverPhone']; //手机号码
        $detail['remark'] = isset($source['orderInfo']['orderDetail']['deliveryRemark']) ? $source['orderInfo']['orderDetail']['deliveryRemark']:'';//用户留言
        $detail['payment_code'] = 'fenxiao';
        $detail['order_time'] = strtotime($source['orderInfo']['orderDetail']['orderCreateTime']);//下单时间，时间戳
        $detail['item'] = array_values($items);
        $detail['amount'] = $source['orderInfo']['orderDetail']['orderAmount'];//订单最后价格
        $detail['discount'] = $source['orderInfo']['orderDetail']['productAmount']+$source['orderInfo']['orderDetail']['orderDeliveryFee']-$source['orderInfo']['orderDetail']['orderAmount'];//折扣
        $detail['platform'] = 'new';
        return $detail;
    }

    /*获得单个订单的信息*/
    public function getOneOrderDetail( $orderCode =''){
        $param = array(
            'method' => 'yhd.order.detail.get',
            'orderCode' => $orderCode,
        );
        $res = $this->_sendRequest($param);
        return $res;
    }

    /*获取退货的信息*/
    public function getReturnOrderDetail( $refundCode =''){
        $param = array(
            'method' => 'yhd.refund.detail.get',
            'refundCode' => $refundCode,
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
                'orderSn' => $orderDetail['orderInfo']['orderDetail']['orderCode'],
                'orderStatus' => $orderDetail['orderInfo']['orderDetail']['orderStatus'] == 'ORDER_FINISH' ? 3 : 0,
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
        $status = isset($_GET['order_state']) ? $_GET['order_state'] : 'ORDER_WAIT_SEND,ORDER_PAYED';
        $p = 1;
        do {
            $res = $this->_getOrders($createTime, $p, $status);
            if (!empty($res['orderList']['order'])) {
                $sns = $service->getSavedidByApiorderno(array_column($res['orderList']['order'],'orderCode'));
                $items = array();
                foreach ($res['orderList']['order'] as $orders =>$order) {
                    if (in_array($order['orderCode'], $sns)) {
                        continue;
                    }
                    $order_info = $this->getOneOrderDetail($order['orderCode']);
                    if ($item = $this->_prepareOrder($order_info)) $items[] = $item;
                }
               if (!empty($items)) $createRes = $service->doCreateOrder($items);
            }
        } while ($p = $res['next']);
    }

    //获取商品sku列表
    public function getSkuList($params = array()){
        $page_no = $params['page_no'];
        $page_size = $params['page_size'];
        //查询普通商品
        $param = array(
            'curPage' => $page_no,
            'pageRows' => $page_size,
            'verifyFlg' => 2,
            'canSale' => 1,
            'method'=>'yhd.general.products.search'
        );
        $res1 = $this->_sendRequest($param);

        //查询系列商品
        $param = array(
            'curPage' => $page_no,
            'pageRows' => $page_size,
            'verifyFlg' => 2,
            'canSale' => 1,
            'method'=>'yhd.serial.products.search'
        );
        $res2 = $this->_sendRequest($param);

        $data_out = array();

        //处理普通产品
        foreach($res1['productList']['product'] as $k => $v){
            $title = $v['productCname'];
            $item['goods_name'] = $title;
            $item['source'] = self::$source;
            $sku_id = $v['productId'];
            $item['sku_id'] = $sku_id;
            $data_out[] = $item;
        }

        //处理系列产品
        foreach($res2['serialProductList']['serialProduct'] as $k => $v){
            //查询系列商品子品
            $param = array(
                'productId' => $v['productId'],
                'method'=>'yhd.serial.product.get'
            );
            $sku_list = $this->_sendRequest($param);
            foreach($sku_list['serialChildProdList']['serialChildProd'] as $k => $v){
                $title = $v['productCname'];
                $item['goods_name'] = $title;
                $item['source'] = self::$source;
                $sku_id = $v['productId'];
                $item['sku_id'] = $sku_id;
                $data_out[] = $item;
            }
        }
        return $data_out;
    }

    public function push_ship($params){
        $param=array(
            'method'=>'yhd.logistics.order.shipments.update',
            'orderCode'=>$params['orderno'],
            'deliverySupplierId'=>$this->getShipId($params['logi_name']),
            'expressNbr'=>$params['logi_no']
        );
        $res=$this->_sendRequest($param);
        if($res['updateCount']>0){
            $res = json_encode(array(
                'succ' => '1',
                'msg' => '发货成功'
            ));
        }else{
            $res = json_encode(array(
                'succ' => '0',
                'msg' => $res['errInfoList']['errDetailInfo'][0]['errorCode'].''.$res['errInfoList']['errDetailInfo'][0]['errorDes'].'pkInfo:'.$res['errInfoList']['errDetailInfo'][0]['pkInfo']
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

    private function _getAfterSaleOrders($page = 1, $modified_time = null, $status = -1)
    {
        $create_time = explode('|' , $modified_time);
        $param = array(
            'method' => 'yhd.orders.refund.abnormal.get',
            'pageRows' => 50,
            'curPage' => $page,
            'dateType'=>2,
            'startTime'=>date('Y-m-d H:i:s',$create_time[0]),
            'endTime'=>date('Y-m-d H:i:s',$create_time[1])
        );
        $res = $this->_sendRequest($param);
        if (is_array($res)) {
            if (isset($res['totalCount']) && $res['totalCount'] > $page * 50) $res['next'] = $page + 1;
            return $res;
        }
        return array('next' => false, 'count' => 0);
    }

    /*获取退货的订单*/
    public function getRefundOrder($service)
    {
        $page1 = 1;
        $begin = isset($_GET['begin']) ? strtotime($_GET['begin']):time() - 7 * 24 * 3600;
        $begin = $begin < strtotime(self::$onlineDate) ? strtotime(self::$onlineDate) : $begin;
        $end=isset($_GET['end']) ? strtotime($_GET['end']):time();
        $createTime = $begin.'|'.$end;
        $this->_service = $service;
        do{
           $res1=$this->_getReturnOrder($createTime,$page1);
            if ($res1['errorCount']=="0") {
                $items = $this->_prepareRefund($this->_filterRefunds($res1['refundList']['refund']));
                if (!empty($items)) $this->_service->createRefund(array('new' => $items));
            }
        }while($page1=$res1['next']);
        return true;
    }

    /*获取退货订单*/
    public function _getReturnOrder($modified_time=null,$page=1){
     $create_time=explode('|',$modified_time);
        $param=array(
            'method'=>'yhd.refund.get',
            'startTime'=>$create_time[0],
            'endTime'=>$create_time[1],
            'curPage'=>$page,
            'pageRows'=>50,
            'dateType'=>1,
            'operateType'=>0,
        );
        $res=$this->_sendRequest($param);
        if (is_array($res)) {
            if (isset($res['totalCount']) && $res['totalCount'] > $page * 50) $res['next'] = $page + 1;
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
            $return_detail=$this->getReturnOrderDetail($item['refundCode']);
            foreach($item['refundParticularList']['refundParticular'] as $value){
                if (!isset($this->rel[$value['productId']]) || empty($this->rel[$value['productId']])) {
                    $this->_error($item['orderId'], "分销商品 ({$value['productId']}) 没有配置商品映射，无法生成退款");
                    continue;
                }
                $item['goods_id'] =$this->rel[$value['productId']];
                $item['order_id'] = $orderIdRel["{$item['orderId']}"];
                $item['goods_num']=$value['productRefundNum'];
                $item['reason_text']=$return_detail['refundInfoMsg']['refundDetail']['reasonMsg']." ".$return_detail['refundInfoMsg']['refundDetail']['refundProblem'];
                if (!isset($refunds["{$item['orderId']}"])) $refunds["{$item['orderId']}"] = array();
                $refunds["{$item['orderId']}"][$item['goods_id']] = $item;
            }
        }
        if (!empty($payOrders)) {
            foreach ($rel as $fxOrderId => $order_amount) {
                echo "过滤{$fxOrderId}\n";
                $refund_total = array_sum(array_column($refunds[$fxOrderId], 'refundAmount'));
                echo "退款金额{$refund_total}\n";
                echo "订单金额{$order_amount}\n";
               if (ncPriceFormat($refund_total) != ncPriceFormat($order_amount)) {
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
                $data['refund_type'] = 2;
                $data['return_type'] = 3;
               /*退货状态(0:待审核;3:客服仲裁;4:已拒绝;11:退货中-待顾客寄回;12:退货中-待确认退款;13:换货中;27:退款完成;33:换货完成;34:已撤销;40:已关闭)  isMissingProduct==>0:实物寄回;1:无需实物寄回*/
               if(in_array($items[$orderno][$goods_id]['refundStatus'],array('4','11','12','13','27','33'))){
                   if(isset($items[$orderno][$goods_id]['isMissingProduct'])){
                       $data['return_type'] = $items[$orderno][$goods_id]['isMissingProduct']==0 ? 2:1;
                   }
                }
                $data['seller_state'] = 1; //卖家处理状态:1为待审核,2为同意,3为不同意,默认为1
                $data['refund_amount'] = $items[$orderno][$goods_id]['refundAmount'];//退款金额
                $data['goods_num'] = $items[$orderno][$goods_id]['goods_num'];//商品数量
                $data['buyer_message'] = $items[$orderno][$goods_id]['reason_text'];  //申请原因
                $data['ordersn'] = $ordersn;  //汉购网订单编号
                $data['goods_id'] = $suborder['product_id']; //商品编号
                $data['create_time'] = strtotime($items[$orderno][$goods_id]['applyDate']);  //售后订单产生时间
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
        $p = 1;
        $begin = isset($_GET['begin']) ? strtotime($_GET['begin']):time() - 7 * 24 * 3600;
        $begin = $begin < strtotime(self::$onlineDate) ? strtotime(self::$onlineDate) : $begin;
        $end=isset($_GET['end']) ? strtotime($_GET['end']):time();
        $createTime = $begin.'|'.$end;
        $this->_service = $service;
        do {
            $res = $this->_getReturnOrder($createTime,$p);
            if ($res['errorCount']=='0') {
                $items = $this->_filterTraceRefunds($res['refundList']['refund']);
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
                if ($item['refundStatus'] == '27') {
                    v($params, 0);
                    $method = 'confirm_refund';
                }
                try {
                    $model->beginTransaction();
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
            foreach($item['refundParticularList']['refundParticular'] as $value){
                if (!isset($this->rel[$value['productId']]) || empty($this->rel[$value['productId']])) {
                    $this->_error($item['orderId'], "分销商品 ({$value['productId']}) 没有配置商品映射，无法生成退款");
                    continue;
                }
                if ($item['refundStatus'] == '0') continue;
                $item['goods_id'] =$this->rel[$value['productId']];
                $item['order_id'] = $orderIdRel["{$item['orderId']}"];
                if (!isset($refunds["{$item['orderId']}"])) $refunds["{$item['orderId']}"] = array();
                $refunds["{$item['orderId']}"][$item['goods_id']] = $item;
            }
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
            if ($orderDetail['orderInfo']['orderDetail']['orderStatus']!="ORDER_TRUNED_TO_DO") continue;
            $express = rkcache('express', true);
                    /** 判断当前商品是否需要重新发货 */
                        $express_id = $oid_expressid_rels[$order['order_id']];
                        $data = array();
                        $data['orderno'] = $fx_order_id;
                        $data['logi_no'] = $order['shipping_code'];
                        $data['logi_name'] = $express[$express_id]['e_name'];
                        $this->push_ship($data);
            }
    }

    /*物流信息*/
    public function  getShipId($logi_name){
           $data=array(
            '安能物流'=>'31671',
            'EMS'=>'31665',
            '邮政包裹'=>'20304',//中国邮政（标准）
            '圆通快递'=>'1755'//圆通速递（标准）
           );
         return $data[$logi_name] ? $data[$logi_name] : $data['EMS'] ;
    }

    /*获取物流信息*/
    public function getdeliverys(){
        $param = array(
            'method' => 'yhd.logistics.deliverys.company.get',
        );
        return $this->_sendRequest($param);
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