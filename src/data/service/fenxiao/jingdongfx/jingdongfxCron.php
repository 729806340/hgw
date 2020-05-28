<?php
/**
 * Created by PhpStorm.
 * User: houshanshan
 * Date: 2017/6/21
 * Time: 10:28
 */
class jingdongfxCron
{
    private $_config = array(
        'appKey' => 'D96E0865C30413538996D597A0BAC915',
        'appSecret'  => '34faad9485c54cf09655b60a3e0c6c65',
        'grant_type' => 'authorization_code',
        'response_type' =>'code',
        'url' => 'https://api.jd.com/routerjson',
        'access_token' => '3a0d5c3a-d5bb-4fe6-ba2a-291df8a769f2',
    );
    private $_service;
    public static $source = "jingdongfx";
    public static $member_id = '240571';
    public static $onlineDate = "2017-11-6 14:00:00"; //上线日期，不保存上线日之前的订单
    //获取商品映射
    private function getGoodsRel()
    {
        $result = TModel("B2cCategory")->where(array('uid' => $this->member_id))->select();
        $rel = $result ? array_column($result, 'pid', 'fxpid') : array();
        return $rel;
    }

    /* 获取签名 @param array $param 请求参数数据集合*/
    protected function generateSign($params)
    {
        ksort($params);
        $stringToBeSigned = $this->_config['appSecret'];
        foreach ($params as $k => $v)
        {
            if("@" != substr($v, 0, 1))
            {
                $stringToBeSigned .= "$k$v";
            }
        }
        unset($k, $v);
        $stringToBeSigned .= $this->_config['appSecret'];
        return strtoupper(md5($stringToBeSigned));
    }

    private function _getHeader(){
        $header = array(
            'access_token'=>$this->_config['access_token'],
            'app_key'=>$this->_config['appKey'],
            'timestamp'=>date('Y-m-d H:i:s' , time()),
            'format'=>'json',
            'v'=>'2.0'
        );
        return $header;
    }

    public function __construct($getRel = 1)
    {
        import('Curl');
        $this->timestamp = TIMESTAMP;
        $model_member = TModel("Member");
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

    public function getAccessToken(){
        $url = "https://oauth.jd.com/oauth/authorize?";
        $param_code = 'response_type='.$this->_config['response_type'].'&';
        $param_code .='client_id='.$this->_config['appKey'].'&';
        $param_code .='redirect_uri=urn:ietf:wg:oauth:2.0:oob';
        $code_url = $url.$param_code;
        header("Location:$code_url");
    }

    private function _sendRequest($param)
    {
        $url = $this->_config['url'];
        $header = $this->_getHeader();
        $method = $param['method'];
        unset($param['method']);
        $post_data = array('360buy_param_json'=>json_encode((object)$param));
        $post_data = array_merge($header,$post_data);
        $post_data['method'] = $method;
        $post_data['sign'] = $this->generateSign($post_data);
        $requestUrl = $url . "?";
        foreach ($post_data as $sysParamKey => $sysParamValue)
        {
            $requestUrl .= "$sysParamKey=" . urlencode($sysParamValue) . "&";
        }
        $res = $this->curl($requestUrl);
        $res = json_decode($res , true);
        if ($res['error_response'] > 0) {
                $res['info'] = $res['error_response']['zh_desc'];
                Log::record('京东返回错误! method:' . $post_data['method'] . '; Info:' . $res['info']);
        }
        return $res;
    }

    public function curl($url, $postFields = null)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FAILONERROR, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if ($this->readTimeout) {
            curl_setopt($ch, CURLOPT_TIMEOUT, $this->readTimeout);
        }
        if ($this->connectTimeout) {
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->connectTimeout);
        }
        //https 请求
        if(strlen($url) > 5 && strtolower(substr($url,0,5)) == "https" ) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }
        if (is_array($postFields) && 0 < count($postFields))
        {
            $postBodyString = "";
            $postMultipart = false;
            foreach ($postFields as $k => $v)
            {
                if("@" != substr($v, 0, 1))//判断是不是文件上传
                {
                    $postBodyString .= "$k=" . urlencode($v) . "&";
                }
                else//文件上传用multipart/form-data，否则用www-form-urlencoded
                {
                    $postMultipart = true;
                }
            }
            unset($k, $v);
            curl_setopt($ch, CURLOPT_POST, true);
            if ($postMultipart)
            {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
            }
            else
            {
                curl_setopt($ch, CURLOPT_POSTFIELDS, substr($postBodyString,0,-1));
            }
        }
        $reponse = curl_exec($ch);
        if (curl_errno($ch))
        {
            throw new Exception(curl_error($ch),0);
        }
        else
        {
            $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if (200 !== $httpStatusCode)
            {
                throw new Exception($reponse,$httpStatusCode);
            }
        }
        curl_close($ch);
        return $reponse;
    }

    private function _getOrders($create_time = null, $page = 1, $status)
    {
        $create_time = explode('|',$create_time);
        $param = array(
            'method' => '360buy.order.search',
            'start_date'=> date('Y-m-d H:i:s', $create_time[0]),
            'end_date'  =>date('Y-m-d H:i:s', $create_time[1] ),
            'order_state' => $status,
            'page' => $page,
            'page_size' => 100,
        );
        $res = $this->_sendRequest($param);
        log::selflog('京东抓取日志! param:' . json_encode($param).'; response:'.json_encode($res),self::$source);

        if (is_array($res)) {
            if (isset($res['totalCount']) && $res['totalCount'] > $page * 100) $res['next'] = $page + 1;
            return $res;
        }
        return array('next' => false, 'count' => 0);
    }

    private function _prepareOrder($source)
    {
        $goodsList = $source['item_info_list'];
        $items = array();
        $hasError = false;
        foreach ($goodsList as $goods) {
            if (isset($this->rel[$goods['sku_id']]) && $this->rel[$goods['sku_id']]) {
                $goods_id = $this->rel[$goods['sku_id']];
            } else {
                $this->_error($source['order_id'], "分销商品 {$goods['sku_name']} ({$goods['sku_id']}) 没有映射");
                $hasError = true;
                continue;
            }
            $items[] = array(
                'goods_id' => $goods_id,
                'name' => $goods['sku_name'],
                'num' => $goods['item_total'],
                'price' => $goods['jd_price'],
                'fxpid' => $goods['sku_id'],
                'oid' => isset($goods['sku_id']) ? $goods['sku_id']:$source['order_id'],//必须添加否则fenxiao_sub无法查询
            );
        }
        $couponList=$source['coupon_detail_list'];
        $seller_discount=0;
        /*查看是否属于店铺优惠*/
        foreach($couponList as $v){
           if($v['coupon_type']=="店铺优惠") {
               $seller_discount+=$v['coupon_price'];
           }
        }
        $order_payment=$source['order_total_price']-$seller_discount;
        if (empty($items) || $hasError) return false;
        if(!isset($source['consignee_info']['province']) || empty($source['consignee_info']['province'])){
            $this->_error($source['order_id'], "分销订单 ({$source['order_id']}) 的收货地址省份信息获取失败，地区数据：{$source['consignee_info']['province']}");
        }else if(!isset($source['consignee_info']['city']) || empty($source['consignee_info']['city'])){
            $this->_error($source['order_id'], "分销订单 ({$source['order_id']}) 的收货地址城市信息获取失败，地区数据：{$source['consignee_info']['city']}");
        }else if(!isset($source['consignee_info']['county']) || empty($source['consignee_info']['county'])){
            $this->_error($source['order_id'], "分销订单 ({$source['order_id']}) 的收货地址县/市/区信息获取失败，地区数据：{$source['consignee_info']['county']}");
        }
        $detail = array();
        $detail['order_sn'] = $source['order_id']; //分销系统订单编号
        $detail['buy_id'] = $this->member_id; //分销商用户编号
        $detail['receiver'] = $source['consignee_info']['fullname'];//收件人
        $detail['provine'] = isset($source['consignee_info']['province'])?$source['consignee_info']['province']:'';
        $detail['city'] = isset($source['consignee_info']['city'])?$source['consignee_info']['city']:'';
        $detail['area'] = isset($source['consignee_info']['county'])?$source['consignee_info']['county']:'';
        $detail['address'] = $source['consignee_info']['full_address'];
        $detail['mobile'] = !empty($source['consignee_info']['mobile']) ? $source['consignee_info']['mobile']:$source['consignee_info']['telephone']; //手机号码
        $detail['remark'] = isset($source['order_remark']) ? $source['order_remark']:'';//用户留言
        $detail['payment_code'] = 'fenxiao';
        $detail['order_time'] = strtotime($source['order_start_time']);//下单时间，时间戳
        $detail['item'] = $items;
        $detail['amount'] =$order_payment;//订单最后价格
        $detail['discount'] = $seller_discount;//折扣
        $detail['platform'] = 'new';
        return $detail;
    }

    /*获得单个订单的信息*/
    public function getOneOrderDetail($order_id =''){
        $param = array(
            'method' => '360buy.order.get',
            'order_id'=> $order_id,
        );
        $res = $this->_sendRequest($param);
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
        $status = isset($_GET['order_state']) ? $_GET['order_state'] : 'WAIT_SELLER_STOCK_OUT';
        $p = 1;
        do {
            $res = $this->_getOrders($createTime, $p, $status);
            if (!empty($res['order_search_response']['order_search']['order_info_list'])) {
                $sns = $service->getSavedidByApiorderno(array_column($res['order_search_response']['order_search']['order_info_list'],'order_id'));
                $items = array();
                foreach ($res['order_search_response']['order_search']['order_info_list'] as $orders =>$order) {
                    if (in_array($order['order_id'], $sns)) {
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
        $param = array(
            'method'=>'jingdong.ware.read.searchWare4Valid',
            'pageNo'=>$page_no,
            'pageSize'=>10
        );
        $res = $this->_sendRequest($param);
        $data_out = array();
        $data=$res['jingdong_ware_read_searchWare4Valid_responce']['page']['data'];
        if(count($data)) {
            $wareIds=array_column($data,'wareId');
            $param1 = array(
                'method' => 'jingdong.sku.read.searchSkuList',
                'skuStatuValue ' => '1',
                'wareId' =>$wareIds
            );
            $res1 = $this->_sendRequest($param1);
            $skudata=$res1['jingdong_sku_read_searchSkuList_responce']['page']['data'];
            $data=array_under_reset($data,'wareId');
            foreach($skudata  as $v){
               if($data[$v['wareId']]){
                 $item['goods_name']=$data[$v['wareId']]['title'];
                 $item['source'] = self::$source;
                 $item['sku_id'] =$v['skuId'];
                 $data_out[] = $item;
               }
            }
        }
        return $data_out;
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
                'orderSn' => $orderDetail['order_get_response']['order']['orderInfo']['order_id'],
                'orderStatus' => $orderDetail['order_get_response']['order']['orderInfo']['order_state']== 'FINISHED_L' ? 3 : 0,
                'refundStatus' => 0,
            );
            $res['orderStatus']['list'][] = $item;

        }
        $res['orderStatus']['result'] = 1;
        return $res;
    }

    /*获取商家所有得物流信息*/
    public function getdeliverys(){
        $param = array(
            'method' => '360buy.get.vender.all.delivery.company',
        );
        return $this->_sendRequest($param);
    }

    public function push_ship($params){
        $param=array(
            'method'=>'360buy.order.sop.outstorage',
            'order_id'=>$params['orderno'],
            'logistics_id'=>$this->getShipId($params['logi_name']),
            'waybill'=>$params['logi_no']
        );
        $res=$this->_sendRequest($param);
        if(!$res['error_response']){
            $res = json_encode(array(
                'succ' => '1',
                'msg' => '发货成功'
            ));
        }else{
            $res = json_encode(array(
                'succ' => '0',
                'msg' => $res['error_response']['zh_desc'].":".$res['info']
            ));
        }
        return $res;
    }

    /*物流信息*/
    public function  getShipId($logi_name){
        $data=array(
            '安能物流'=>'4832',
            '速尔快递'=>'2105',
            '中铁快运'=>'466',
            '凡客如风达'=>'313214',
            '德邦物流'=>'2130',
            '天地华宇'=>'2462',
            '佳吉快运'=>'2460',
            '新邦物流'=>'2461',
            '国通快递'=>'2465',
            '挂号信'=>'2171',
            '邮政包裹'=>'2170',
            '全一快递'=>'2100',
            '联邦快递'=>'2096',
            '快捷速递'=>'2094',
            '龙邦快递'=>'471',
            '全峰快递'=>'2016',
            '优速快递'=>'1747',
            '中通快递'=>'1499',
            '宅急送'=>'1409',
            '韵达快递'=>'1327',
            '申通快递'=>'470',
            '顺丰快递'=>'467',
            'EMS'=>'465',
            '圆通快递'=>'463'
        );
        return $data[$logi_name] ? $data[$logi_name] : $data['EMS'] ;
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
        $page =1;
        $begin = isset($_GET['begin']) ? strtotime($_GET['begin']):time() - 7 * 24 * 3600;
        $begin = $begin < strtotime(self::$onlineDate) ? strtotime(self::$onlineDate) : $begin;
        $end=isset($_GET['end']) ? strtotime($_GET['end']):time();
        $createTime = $begin.'|'.$end;
        $this->_service = $service;
        do{
            $res=$this->_getReturnOrder($createTime,$page);
            if ($res['jingdong_pop_afs_soa_refundapply_queryPageList_responce']['queryResult']['success']) {
                $items = $this->_prepareRefund($this->_filterRefunds($res['jingdong_pop_afs_soa_refundapply_queryPageList_responce']['queryResult']['result']));
               if (!empty($items)) $this->_service->createRefund(array('new' => $items));
            }
        }while($page=$res['next']);
        return true;
    }

    /*获取退货订单*/
    public function _getReturnOrder($modified_time=null,$page=1){
        $create_time=explode('|',$modified_time);
        $param=array(
            'method'=>'jingdong.pop.afs.soa.refundapply.queryPageList',
            'applyTimeStart'=>date('Y-m-d H:i:s',$create_time[0]),
            'applyTimeEnd '=>date('Y-m-d H:i:s',$create_time[1]),
            'pageIndex'=>$page,
            'pageSize'=>30,
            'status'=>0
        );
        $res=$this->_sendRequest($param);
        if($res['jingdong_pop_afs_soa_refundapply_queryPageList_responce']['queryResult']['success']){
            if($res['jingdong_pop_afs_soa_refundapply_queryPageList_responce']['queryResult']['totalCount']>$page*30)  $res['next']=$page+1;
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
        $fxIds = array_column($items, 'orderId');
        /** @var orderModel $orderModel */
        $orderModel = Model('order');
        $payOrders = $orderModel->getOrderList(array('fx_order_id' => array('in', $fxIds), 'order_state' => ORDER_STATE_PAY));
        $orders = $orderModel->getOrderList(array('fx_order_id' => array('in', $fxIds)));
        $rel = array_column($payOrders, 'order_amount', 'fx_order_id');
        $orderIdRel = array_column($orders, 'order_id', 'fx_order_id');
        /** 将退款格式转换未二级格式【order=》【goods=》【】】】 */
        foreach ($items as $item) {
            $order_data=$this->getOneOrderDetail($item['orderId']);
            $orderinfo=$order_data['order_get_response']['order']['orderInfo'];
            foreach($orderinfo['item_info_list'] as $value){
                // 处理商品映射关系
                if (!isset($this->rel[$value['sku_id']]) || empty($this->rel[$value['sku_id']])) {
                    $this->_error($item['orderId'], "_filterRefunds,分销商品 ({$value['sku_id']}) 没有配置商品映射，无法生成退款");
                    continue;
                }
                $item['goods_id'] =$this->rel[$value['sku_id']];
                $item['order_id'] = $orderIdRel["{$item['orderId']}"];
                $item['goods_num']=$value['item_total'];
                if (!isset($refunds["{$item['orderId']}"])) $refunds["{$item['orderId']}"] = array();
                $refunds["{$item['orderId']}"][$item['goods_id']] = $item;
            }
        }
        if (!empty($payOrders)) {
            foreach ($rel as $fxOrderId => $order_amount) {
                echo "过滤{$fxOrderId}\n";
                $refund_total = array_sum(array_column($refunds[$fxOrderId], 'applyRefundSum'));
                $refund_total=$refund_total/100;
                echo "退款金额{$refund_total}\n";
                echo "订单在汉购网加上平台优惠的金额交易金额{$refund_total}\n";
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
            $data['refund_type'] = 1;
            $data['return_type'] = 3;
            $data['seller_state'] = 1; //卖家处理状态:1为待审核,2为同意,3为不同意,默认为1
            $data['refund_amount'] = $items[$orderno][$goods_id]['applyRefundSum'];//退款金额
            $data['goods_num'] =$items[$orderno][$goods_id]['goods_num'];//商品数量
            $data['buyer_message'] ="无";  //申请原因
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
        $p = 1;
        $begin = isset($_GET['begin']) ? strtotime($_GET['begin']):time() - 7 * 24 * 3600;
        $begin = $begin < strtotime(self::$onlineDate) ? strtotime(self::$onlineDate) : $begin;
        $end=isset($_GET['end']) ? strtotime($_GET['end']):time();
        $createTime = $begin.'|'.$end;
        $this->_service = $service;
        do {
            $res = $this->_getReturnOrder($createTime,$p);
            if ($res['jingdong_pop_afs_soa_refundapply_queryPageList_responce']['queryResult']['success']) {
                $items = $this->_filterTraceRefunds($res['jingdong_pop_afs_soa_refundapply_queryPageList_responce']['queryResult']['result']);
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
        $fxIds = array_column($items, 'orderId');
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
            $order_data=$this->getOneOrderDetail($item['orderId']);
            $orderinfo=$order_data['order_get_response']['order']['orderInfo'];
            foreach($orderinfo['item_info_list'] as $value){
                // 处理商品映射关系
                if (!isset($this->rel[$value['sku_id']]) || empty($this->rel[$value['sku_id']])) {
                    $this->_error($item['orderId'], "_filterRefunds,分销商品 ({$value['sku_id']}) 没有配置商品映射，无法生成退款");
                    continue;
                }
                $item['goods_id'] =$this->rel[$value['sku_id']];
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
            if ($orderDetail['order_get_response']['order']['orderInfo']['order_state']!="WAIT_SELLER_STOCK_OUT") continue;
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