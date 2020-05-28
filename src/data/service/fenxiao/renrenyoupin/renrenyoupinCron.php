<?php

/*接口文档地址:https://github.com/renren-erp/erpapi/wiki/人人优品-第三方ERP对接API文档
1.每次获取提取订单表以后，请调取订单拣货接口(method = pickOrder)
*/
class renrenyoupinCron
{
    private  $app_id='vip_nongguxian';
    private  $session='829D7D50E794640F3CA27A828CC18748';
    private  $secret ='9E4957A5D954C3935D865DD3A7E54922';
    private  $gateway='http://erpapi.renrenyoupin.com';
//    private  $gateway='http://112.74.27.159:8090';
    public static $source = "renrenyoupin";
    public static $onlineDate = "2017-05-26 12:00:00";//上线的时间
//    public static $onlineDate = "2017-05-16 12:00:00";//上线的时间

    public function __construct($getRel = 1)
    {
        import('Curl');
        $this->timestamp = TIMESTAMP;
        $model_member = TModel("Member");
        $condition = array(
            "member_name" => self::$source
        );
        $row = $model_member->where($condition)->find();
        $this->member_id = $row['member_id'];
        $model_member->execute("set wait_timeout=1000");
        /*商品映射*/
        if ($getRel) {
            $this->rel = $this->getGoodsRel();
            $this->oldRel = array();
        }
    }

    /*获取商品映射*/
    private function getGoodsRel()
    {
        $result = TModel("B2cCategory")->where(array('uid' => $this->member_id))->select();
        $rel = $result ? array_column($result, 'pid', 'fxpid') : array();
        return $rel;
    }

    /*发送请求*/
    private function _sendRequest($param, $authorize = false)
    {
        $sys_params = array(
            'appid' => $this->app_id,
            'session_key' => $this->session,
            'format'=>'json',
            'v'=>'2.0',
            'timestamp' => date("Y-m-d H:i:s",time())
        );
        $url = $this->gateway.$param['sub_url'];
        unset($param['sub_url']);
        $sign_params = array_merge($param, $sys_params);
        $invoke_params = array_merge($sign_params,array('sign'=>$this->gen_sign($sign_params)));
        $curl = new Curl();
        $curl->setJsonDecoder(function ($response) {
            return json_decode($response, true);
        });
        $curl->setOpt(CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        $sys_params['begin_time'] = date("Y-m-d H:i:s",time());
        $curl->post($url,$invoke_params);
        $method = $param['method'];

        if ($curl->error) {
            Log::record('人人优品 HTTP 请求失败! method:' . $method . ';Error:' . $curl->errorCode . ': ' . $curl->errorMessage);
            throw new Exception('人人优品 HTTP 请求失败! method:' . $method . ';Error:' . $curl->errorCode . ': ' . $curl->errorMessage);
        }
        $res = $curl->response;
        if ($res['code'] !='0') {
            Log::record('人人优品返回错误! method:' . $method . '; Info:' . $res['description']. '; params:'.json_encode($invoke_params));
        }
        return $res;
    }

    /*获取签名*/
    protected function gen_sign(array $params)
    {
        ksort($params);
        $sign_str =$this->secret;
        foreach ($params as $key => $value) {
            $sign_str .= $key ."=". $value."&";
        }
        $sign_str=substr($sign_str,0,strlen($sign_str)-1);
        $sign_str .=$this->secret;
        return strtolower(md5($sign_str));
    }

    /*获取订单详情*/
    private  function  getOrderDetail($orderSn){
        $param = array(
            'method'=>'singleQueryOrder',
            'sub_url'=>'/orders/singleQueryOrder',
            'flow_id' =>$orderSn
        );
        $res = $this->_sendRequest($param);
        return $res['code']=='0' ? $res['data']:false;
    }

    /*订单拣货接口*/
    private function _pickOrder($flow_id){
     $param=array(
         'method'=>'pickOrder',
         'sub_url'=>'/orders/pickOrder',
         'flow_id'=>$flow_id,
     );
     $res=$this->_sendRequest($param);
     if($res['code']!="0"){
         $this->_error($flow_id,'人人优品拣货接口错误:'.$res['description']);
         return fasle;
     }
     return true;
    }

    public function getSkuList($params = array()){
        $page_no = $params['page_no'];
        $page_size = $params['page_size'];
        $param = array(
            'method'=>'batchQueryGoods',
            'page_no' => $page_no,
            'page_size' => $page_size,
            'sub_url'=>'/goods/batchQueryGoods'
        );
        $res = $this->_sendRequest($param);

        $data_out = array();
        foreach($res['data'] as $v){
            $goods_name = $v['title'];
            foreach($v['products'] as $v1){
                $item['goods_name'] = $goods_name.'--'.$v1['sku_name'];
                $item['sku_id'] = $v1['product_id'];
                $item['source'] = 'renrenyoupin';
                $data_out[] = $item;
            }

        }
        return $data_out;
    }
     /*获取人人优品批量订单
     WAIT_SELLER_SEND_GOODS:等待卖家发货 /  WAIT_BUYER_CONFIRM_GOODS :卖家已发货,
     TRADE_BUYER_SIGNED:买家已签收      /  TRADE_FINISHED:交易成功,
     TRADE_CLOSED:取消订单。拼接。默认值：WAIT_SELLER_SEND_GOODS
     */
    private function _getOrders($start_time = null, $end_time = null, $page = 1, $status='WAIT_SELLER_SEND_GOODS')
    {
        $param = array(
            'method'=>'batchQueryOrder',
            'start_time' =>$start_time,
            'end_time' => $end_time,
            'page_no' => $page,
            'page_size' => 100,
            'out_status'=>$status,
            'sub_url'=>'/orders/batchQueryOrder'
        );
        $res = $this->_sendRequest($param);
        if(is_array($res)) {
         if (isset($res['totalCount']) && $res['totalCount'] > $page * 100) $res['next'] = $page + 1;
          return $res;
        }
        return array('next' => false, 'count' => 0);
    }


     public function  orderlist($params=array()){
        $service = $params['service'] ;
        $start_time = isset($params['begin']) ? strtotime($params['begin']) : TIMESTAMP - 7 * 24 * 3600;
        $start_time = $start_time < strtotime(self::$onlineDate) ? date('Y-m-d H:i:s',strtotime(self::$onlineDate)) : date('Y-m-d H:i:s',$start_time);
        $end_time = isset($params['end']) ? $params['end'] : date('Y-m-d H:i:s',TIMESTAMP);    //$end_time = date('Y-m-d H:i:s',TIMESTAMP);
        $p = 1;
        do{
            $res=$this->_getOrders($start_time,$end_time,$p);
         if($res['code']=='0'){
                $sns = $service->getSavedidByApiorderno(array_column($res['data'], 'flow_id'));
                $items=array();
                foreach($res['data'] as $order){
                    if(in_array($order['flow_id'],$sns)) continue;

                    if($item=$this->_prepareOrder($order)){                        
                        $items[]=$item;
                        $goods_num=0;
                        foreach($item['item'] as $v){
                            if(intval($v['fxpid'])==953221) $goods_num=$v['num'];
                        }
                        if($goods_num!==0){
                            $item['item']=array();
                            $item['item'][0]['goods_id'] = '103416';
                            $item['item'][0]['num'] = $goods_num;
                            $item['item'][0]['price'] = '1';
                            $item['item'][0]['name'] = '厨道 花生调和油 5L';
                            $item['item'][0]['fxpid'] = '1';
                            $item['item'][0]['oid'] = '1';
                            $item['order_sn'] .= '-1';
                            $items[] = $item;
                        }
                        $this->_pickOrder($order['flow_id']);
                 }
                }
                if(!empty($items)) $createRes=$service->doCreateOrder($items);
        }
        }while($p=$res['next']);
    }

    private function _prepareOrder($source)
    {
        // TODO 检查订单是否存在，若存在直接返回false，否则准备数据，并返回数据
        $goodsList = $source['order_items'];
        $items = array();
        $hasError = false;

        foreach ($goodsList as $goods) {
            if (isset($this->rel[$goods['product_id']]) && $this->rel[$goods['product_id']]) {
                $goods_id = $this->rel[$goods['product_id']];
            } else {
                $this->_error($source['flow_id'], "分销商品 {$goods['name']} 没有映射");
                $hasError = true;
                continue;
            }
            $items[] = array(
                'goods_id' => $goods_id,
                'name' => $goods['name'],
                'num' => $goods['nums'],
                'price' => $goods['price'],
                'fxpid' => $goods['product_id'],
                'oid' => isset($goods['product_id']) ? $goods['product_id'] : $source['flow_id']
            );
        }

        if (empty($items) || $hasError)
            return false;
        if(!isset($source['province']) || empty($source['province'])){
            $this->_error($source['flow_id'], "分销订单 ({$source['flow_id']}) 的收货地址省份信息获取失败，地区数据");
        }
        if(!isset($source['city']) || empty($source['city'])){
            $this->_error($source['flow_id'], "分销订单 ({$source['flow_id']}) 的收货地址城市信息获取失败，地区数据");
        }
        if(!isset($source['district']) || empty($source['district'])){
            $this->_error($source['flow_id'], "分销订单 ({$source['flow_id']}) 的收货地址县/市/区信息获取失败，地区数据");
        }
        $detail = array();
        $detail['order_sn'] = $source['flow_id']; // 分销系统订单编号
        $detail['buy_id'] = $this->member_id; // 分销商用户编号
        $detail['receiver'] = $source['consignee_name']; // 收件人
        $detail['provine'] = $source['province'];
        $detail['city'] = $source['city'];
        $detail['area'] = $source['district'];
        $detail['address'] = $source['address'];
        $detail['mobile'] = $source['consignee_mobile']; // 手机号码
        $detail['remark'] = $source['memo'];
        $detail['payment_code'] = 'fenxiao';
        $detail['order_time'] = strtotime($source['created']); // 下单时间，时间戳
        $detail['item'] = $items;
        $detail['amount'] = $source['final_amount'];
        $detail['platform'] = 'new';
        $detail['shipping_fee']=$source['ship_fee'];//运费
        return $detail;
    }

    /*检查订单是否已经发货*/
    public function checkUnshipOrder()
   {
    $hour = date('G');
    // 凌晨检测最近3天，其他时间检测最近3小时
    $updateTime = $hour >= 6 ? TIMESTAMP - 3600 * 3 : TIMESTAMP - 3600 * 24 * 3;
    $comm_where = array();
    $comm_where['shipping_time'] = array(
        'gt',
        $updateTime
    );
    $result = Model('order_common')->where($comm_where)->select();
    if (! $result)
        die('no result');
    $oids = array_column($result, 'order_id');
    $oid_expressid_rels = array_column($result, 'shipping_express_id', 'order_id');
    $where = array();
    $where['order_id'] = array(
        'in',
        $oids
    );
    $where['buyer_id'] = $this->member_id;
    //在系统中已经发货
    $orders = TModel('orders')->where($where)->select();
    if (! $orders)
        die('no orders');

    foreach ($orders as $order) {
        $fx_order_id = $order['fx_order_id'];
        $goodsWhere = array();
        $goodsWhere['order_id'] = $order['order_id'];
        $order_items = TModel('order_goods')->where($goodsWhere)->select();
        if (! $order_items)
            continue;

        //查出的待发货订单
        $orderDetail = $this->getOrderDetail($fx_order_id);
        if ($orderDetail['ship_status']!='NOT_SHIP')
            continue;
        $express = rkcache('express', true);
        $express_id = $oid_expressid_rels[$order['order_id']];
        $data = array();
        $data['orderno'] = $fx_order_id;
        $data['logi_no'] = $order['shipping_code'];
        $data['logi_name'] = $express[$express_id]['e_name'];
        $this->push_ship($data);
    }
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
        for ($i = $limit; $i >= 1; $i --) {
            $b_time = TIMESTAMP - $i * 24 * 3600;
            $e_time = $b_time + 24 * 3600;
            $params['begin'] = date('Y-m-d H:i:s', $b_time);
            $params['end'] = date('Y-m-d H:i:s', $e_time);
            $this->orderlist($params);
        }
    }

    function rrypLogiCode( $name )
    {
        $data = array(
            '德邦物流' => 'debangwuliu',
            '申通快递' => 'shentong',
            '顺丰快递' => 'shunfeng',
            'EMS' => 'ems',
            '韵达快递' => 'yunda',
            '中通快递' => 'zhongtong',
            '圆通快递' => 'yuantong',
            '天天快递' => 'tiantian',
            '百世汇通' => 'huitongkuaidi',
            '全峰快递' => 'quanfengkuaidi',
            '宅急送' => 'zhaijisong',
            '邮政包裹' => 'youzhengguonei',
            '包裹平邮'=>'youzhenguonei',
            '优速快递'=>'youshuwuliu',
        ) ;
        return $data[$name] ? $data[$name] : 'ems' ;
    }

    /*筛选快递*/
    public  function  checkExpresses($logi_name){
     $param=array(
         'method'=>'batchQueryExpresses',
         'sub_url'=>'/base/batchQueryExpresses',
     );
      $res=$this->_sendRequest($param);
     if($res['code']!='0'){
       return false;
     }
        foreach($res['data'] as $v){
            if($v['name'] == $logi_name)   {
                $expre_no=$v['code'];
                break;
            }
        }
        return isset($expre_no)&&$expre_no!='' ? $expre_no:'other';
    }

    /*商品进行发货*/
    public function push_ship($params = array())
    {
        $param = array(
            'method'=>'deliverOrder',
            'sub_url'=>'/orders/deliverOrder',
            'flow_id' => trim($params['orderno']),
            'express_code' =>  $this -> rrypLogiCode( $params['logi_name'] ),
            'express_no' => trim($params['logi_no'])
        );

        $res = $this->_sendRequest($param);

        if ($res['code'] == 0) {
            $res = json_encode(array(
                'succ' => '1',
                'msg' => '发货成功'
            ));
        } else {
            $res = json_encode(array(
                'succ' => '0',
                'msg' => $res['description']
            ));
        }

        return $res;
    }

    /**
     * 获得订单完成状态
     *
     * @param string $orderSNs
     *            批量请用半角逗号分开
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
            // 查询接口
            $orderDetail = $this->getOrderDetail($fx_order_id);
            // 组装所有分销渠道该接口的统一的返回数据格式
            $item = array(
                'orderSn' => $orderDetail['flow_id'],
                'orderStatus' => $orderDetail['out_status'] == 'TRADE_FINISHED' ? 3 : 0,
                'refundStatus' => 0
            );
            $res['orderStatus']['list'][] = $item;
        }
        $res['orderStatus']['result'] = 1;
        return $res;
    }

    /*保存错误信息到日志table*/
    public function _error($orderno, $errorinfo, $log_type = 'order')
    {
        $model = Model("b2c_order_fenxiao_error");
        $where = array(
            'orderno' => $orderno,
            'error' => $errorinfo
        );
        if ($model->where($where)->count() > 0)
            return;

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
