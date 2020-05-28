<?php
/**
 * Created by CharlesChen
 * Date: 2018/3/8
 * Time: 11:54
 * File name:dangdang11Cron.php
 * 文档地址：http://open.dangdang.com/
 */
class dangdang11Cron{
    static $source="dangdang11";
    private $member_id;
    private $app_secret="5AC2F75CAC541AF7B27251EC389AAC56";
    private $app_key="2100007011";
    private $code="0E32B57E429DBF52D6DB03EEFCBEF869";
    private $request_url="http://oauth.dangdang.com/default.jsp";
    private $token="2DD79A0DDDF7293A23854E23B7408287FA6EEFE28B3AC8667426CE1D300FAAB5";
    private $api_host="http://api.open.dangdang.com/openapi/rest";
    public static $onlineDate = "2018-03-13 12:00:00";//上线的时间
    public static $response = array(
        601 => "不允许修改COD订单状态",
        100 => "没有上传xml文件",
        103 => "上传文件错误",
        102 => "上传文件不是xml格式",
        101 => "上传文件过大",
        500 => "系统异常",
        41 => "发货商品错误",
        42 => "发货商品数量错误",
        605 => "订单状态决定该订单不能进行操作",
        39 => "发货数量全为0",
        40 => "物流公司、电话或物流单号出错",
        618 => "所属大商家分类不允许部分发货",
        51 => "订单状态错误",
        410 => "gshopID为空",
        411 => "gshopID不存在",
        1203 => "计算分摊失败",
        1204 => "配货完成失败",
        1205 => "发货商品编号或者促销编号错误",
    );

    public function  __construct($getRel = 1)
    {
        import('Curl');
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
    public function orderlist($params=array()){
        $service = $params['service'] ;
        $start_time = isset($params['osd']) ? strtotime($params['osd']) : TIMESTAMP - 7 * 24 * 3600;
        $start_time = $start_time < strtotime(self::$onlineDate) ? date('Y-m-d H:i:s',strtotime(self::$onlineDate)) : date('Y-m-d H:i:s',$start_time);
        $end_time = isset($params['oed']) ? $params['oed'] : date('Y-m-d H:i:s',TIMESTAMP);    //$end_time = date('Y-m-d H:i:s',TIMESTAMP);
        $p = 1;
        do{
            $res=$this->_getOrders($start_time,$end_time,$p);
                $sns = $service->getSavedidByApiorderno(array_column($res['OrdersList'], 'orderID'));
                $items=array();
                if($res['totalInfo']['orderCount']==1){
                    $res['OrdersList']['OrderInfo']=array($res['OrdersList']['OrderInfo']);
                }
                foreach($res['OrdersList']['OrderInfo'] as $order){
                    if(in_array($order['orderID'],$sns)) continue;
                    if($item=$this->_prepareOrder($order))
                        $items[]=$item;
                }
                if(!empty($items)) $createRes=$service->doCreateOrder($items);
        }while($p=$res['next']);
    }
    /**
     * 批量获取当当网订单
     *订单状态为以下其中一种：9999:全部;100:等待到款;101:等待发货(预设值)，商家后台页面中显示为“等待配货”的订单也能够被查询出来
     *300:已发货;400:已送达;1000:交易成功;-100:取消;1100:交易失败;-200:已拆单;50:等待审核;
     *
    */
    private function _getOrders($start_time = null, $end_time = null, $page = 1, $status='101')
    {
        $param = array(
            'method'=>'dangdang.orders.list.get',
            'osd' =>$start_time,
            'oed' => $end_time,
            'p' => $page,
            'pageSize' => 20,
            'os'=>$status,
        );
        $res = $this->_sendRequest($param);
        if(is_array($res)) {
            if (isset($res['totalInfo']['orderCount']) && $res['totalInfo']['orderCount'] > $page * 20) $res['next'] = $page + 1;
            return $res;
        }
        return array('next' => false, 'count' => 0);
    }

    /**
     * 获取订单详情
     * @param $order_sn
     * @return bool|mixed
     *
     */
    public function getOrderDetail($order_sn){
        $param = array(
            'method'=>'dangdang.order.details.get',
            'o' =>$order_sn,
        );
        $res = $this->_sendRequest($param);
        return $res;
    }
    private function _prepareOrder($source)
    {
        $order=$this->getOrderDetail($source['orderID']);
        // TODO 检查订单是否存在，若存在直接返回false，否则准备数据，并返回数据
        $items = array();
        $hasError = false;
        $itemInfo=isset($order['ItemsList']['ItemInfo'][0])?$order['ItemsList']['ItemInfo']:$order['ItemsList'];
        foreach ($itemInfo as $goods) {
            if (isset($this->rel[$goods['itemID']]) && $this->rel[$goods['itemID']]) {
                $goods_id = $this->rel[$goods['itemID']];
            } else {
                $this->_error($source['orderID'], "分销商品 {$goods['itemName']} ({$goods['itemID']}) 没有映射");
                $hasError = true;
                continue;
            }
            $items[] = array(
                'goods_id' => $goods_id,
                'name' => $goods['itemName'],
                'num' => $goods['orderCount'],
                'price' => $goods['unitPrice'],
                'fxpid' => $goods['itemID'],
                'oid' => isset($goods['itemID']) ? $goods['itemID'] : $source['orderID'],//必须添加否则fenxiao_sub无法查询
            );
        }
        if (empty($items) || $hasError) return false;
        if (!isset($order['sendGoodsInfo']['consigneeAddr_Province']) || empty($order['sendGoodsInfo']['consigneeAddr_Province'])) {
            $this->_error($source['orderID'], "分销订单 ({$source['orderID']}) 的收货地址省份信息获取失败，地区数据：{$order['sendGoodsInfo']['consigneeAddr_Province']}");
        } else if (!isset($order['sendGoodsInfo']['consigneeAddr_City']) || empty($order['sendGoodsInfo']['consigneeAddr_City'])) {
            $this->_error($source['orderID'], "分销订单 ({$source['orderID']}) 的收货地址城市信息获取失败，地区数据：{$order['sendGoodsInfo']['consigneeAddr_City']}");
        } else if (!isset($order['sendGoodsInfo']['consigneeAddr_Area']) || empty($order['sendGoodsInfo']['consigneeAddr_Area'])) {
            $this->_error($source['orderID'], "分销订单 ({$source['orderID']}) 的收货地址县/市/区信息获取失败，地区数据：{$order['sendGoodsInfo']['consigneeAddr_Area']}");
        }
        $detail = array();
        $detail['order_sn'] = $source['orderID']; //分销系统订单编号
        $detail['buy_id'] = $this->member_id; //分销商用户编号
        $detail['receiver'] = $source['consigneeName'];//收件人
        $detail['provine'] = $order['sendGoodsInfo']['consigneeAddr_Province'];
        $detail['city'] = $order['sendGoodsInfo']['consigneeAddr_City'];
        $detail['area'] = $order['sendGoodsInfo']['consigneeAddr_Area'];
        $detail['address'] = $source['consigneeAddr'];
        $detail['mobile'] = $source['consigneeMobileTel']; //手机号码
        $detail['remark'] = !empty($order['message']) ? $order['message'] : '无';//用户留言
        $detail['payment_code'] = 'fenxiao';
        $detail['order_time'] = strtotime($source['paymentDate']);//下单时间，时间戳
        $detail['item'] = $items;
        $detail['amount'] = $order['orderBalance'];//订单最后价格
        $detail['discount'] =0;
        $detail['platform'] = 'new';
        $detail['shipping_fee'] = $order['buyerInfo']['postage'];//运费
        return $detail;
    }
    public function _sendRequest($param){
        $params=array();
        $params['method']=$param['method'];
        $params['app_key']=$this->app_key;
        $params['timestamp']=date("Y-m-d H:i:s",time());
        $params['format']='xml';
        $params['v']=isset($param['v'])?$param['v']:'1.0';
        $params['sign_method']='md5';
        $params['session']=$this->token;
        $sign=$this->generateSign($params);
        $params['sign']=$sign;
        $param=array_merge($param,$params);
        $curl = new Curl();
        $curl->setOpt(CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        //发货时用post方法
        if($params['method']=="dangdang.order.goods.sendxml"){
            $curl->post($this->api_host,$param);
        }else {
            $curl->get($this->api_host, $param);
        }
        $res=$curl->response;
        if ($curl->httpStatusCode !== 200) {
            Log::record('当当网 HTTP 请求失败! HTTP 中curl请求失败! Error:' . $curl->httpStatusCode);
        }
        return json_decode(json_encode($res),true);
    }
    public function generateSign($param){
        ksort($param);
        $str="";
        foreach($param as $k=>$v){
            $str.=$k.$v;
        }
        $sign=strtoupper(md5($this->app_secret.$str.$this->app_secret));
        return $sign;
    }
    /**
     * 获得订单完成状态
     * @param string $orderSNs 批量请用半角逗号分开
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
            $orderDetail = $this->getOrderDetail($fx_order_id);
            //组装所有分销渠道该接口的统一的返回数据格式
            $item = array(
                'orderSn' => $orderDetail['orderID'],
                'orderStatus' => $orderDetail['order_status'] == '300' ? 3 : 0,
                'refundStatus' => 0,
            );
            $res['orderStatus']['list'][] = $item;

        }
        $res['orderStatus']['result'] = 1;
        return $res;
    }
    /*获取退货的订单*/
    public function getRefundOrder($service)
    {
        $page = '1';
        $begin = time() > strtotime(self::$onlineDate) ? time() - 3600*24*7 : strtotime(self::$onlineDate) - 3600*24*7;
        $end = time();
        $createTime = $begin . '|' . $end;
        $this->_service = $service;
        $res = $this->_getReturnOrder($createTime, $page);
        do {
            if (!empty($res['OrdersList'])) {
                $items = $this->_prepareRefund($this->_filterRefunds($res['refundInfos']));
                if (!empty($items)) $this->_service->createRefund(array('new' => $items));
            }
        } while ($page = $res['next']);
        return true;
    }

    /*获取退货订单*/
    public function _getReturnOrder($modified_time = null, $page = 1)
    {
        $create_time = explode('|', $modified_time);
        $param = array(
            'method' => 'dangdang.orders.refund.list',
            'refundApp_Status' => 9999,
            'osd' => date('Y-m-d H:i:s',$create_time[0]),
            'oed' => date('Y-m-d H:i:s',$create_time[1]),
            'pageSize' => 100,
            'pageIndex'=>"{$page}"
        );
        $res = $this->_sendRequest($param);
        if (is_array($res['refundInfos'])) {
            if (isset($res['refundInfos']['totalSize']) && $res['refundInfos']['totalSize'] > $page ) $res['next'] = $page + 1;
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
        $fxIds = array_column($items['refundInfoList'], 'orderID');
        /** @var orderModel $orderModel */
        $orderModel = Model('order');
        $payOrders = $orderModel->getOrderList(array('fx_order_id' => array('in', $fxIds), 'order_state' => ORDER_STATE_PAY));
        $orders = $orderModel->getOrderList(array('fx_order_id' => array('in', $fxIds)));
        $rel = array_column($payOrders, 'order_amount', 'fx_order_id');
        $orderIdRel = array_column($orders, 'order_id', 'fx_order_id');
        /** 将退款格式转换未二级格式【order=》【goods=》【】】】 */
        foreach($items['refundInfoList'] as $refund) {
            foreach ($refund['itemsList'] as $item) {
                // 处理商品映射关系
                if (!isset($this->rel[$item['itemID']]) || empty($this->rel[$item['itemID']])) {
                    $this->_error($refund['orderID'], "_filterRefunds,分销商品 ({$item['itemName']}) 没有配置商品映射，无法生成退款");
                    continue;
                }
                $item['goods_id'] = $this->rel[$item['itemID']];
                $item['order_id'] = $orderIdRel["{$refund['orderID']}"];
                if (!isset($refunds["{$refund['orderID']}"])) $refunds["{$refund['orderID']}"] = array();
                $refunds["{$refund['orderID']}"][$item['goods_id']] = $item;
            }
        }
        if (!empty($payOrders)) {
            foreach ($rel as $fxOrderId => $order_amount) {
                echo "过滤{$fxOrderId}\n";
                $refund_total = array_sum(array_column($refunds[$fxOrderId], 'refund_amount'));
                echo "退款金额{$refund_total}\n";
                echo "订单金额{$order_amount}\n";
                if (ncPriceFormat($refund_total) != ncPriceFormat($order_amount)) {
                    unset($refunds[$fxOrderId]);
                    $this->_error($fxOrderId, "未发货分销订单不是全额退款，无法生成退款");
                } else { /*全额退款商品有多个时，只提交一次退款*/
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
            if ($items[$orderno][$goods_id]['after_sales_type'] == "2") { //为1代表仅退款，2为退后退款
                $data['return_type'] = 1;
                $data['refund_type'] = 1;
            }
            $data['seller_state'] = 1; //卖家处理状态:1为待审核,2为同意,3为不同意,默认为1
            $data['refund_amount'] = $items[$orderno][$goods_id]['refund_amount'];//退款金额
            $data['goods_num'] = $items[$orderno][$goods_id]['goods_number'];//商品数量
            $data['buyer_message'] = $items[$orderno][$goods_id]['after_sale_reason'] == "" ? "无" : $items[$orderno][$goods_id]['after_sale_reason'];  //申请原因
            $data['ordersn'] = $ordersn;  //汉购网订单编号
            $data['goods_id'] = $suborder['product_id']; //商品编号
            $data['create_time'] = strtotime($items[$orderno][$goods_id]['created_time']);  //售后订单产生时间
            $newRefund[] = $data;
        }
        return $newRefund;
    }
    public function getShipId($shipname)
    {
        $data = array(
            'EMS' => array('e_name'=>'EMS','tel'=>'11185'),
            '邮政包裹' => array('e_name'=>'POST','tel'=>'11183'),
            '中通快递' => array('e_name'=>'ZTO','tel'=>'400-827-0270'),
            '百世汇通' => array('e_name'=>'BEST','tel'=>'400-956-5656'),
            '安能物流' => array('e_name'=>'ANE56','tel'=>'400-104-0088'),
        );
        return !empty($data[$shipname]) ? $data[$shipname] : $data["EMS"];
    }
    public function getXml($params){
        $logi=$this->getShipId($params['logi_name']);
        $xml="<?xml version=\"1.0\" encoding=\"GBK\"?>";
        $xml.="<request>";
        $xml.="<functionID>dangdang.order.goods.send</functionID>";
        $xml.="<time>".date('Y-m-d H:i:s',time())."</time>";
        $xml.="<OrdersList>";
        $xml.="<OrderInfo><orderID>".$params['order']['orderID']."</orderID>";
        $xml.="<logisticsName>".$params['logi_name']."</logisticsName><logisticsTel>".$logi['tel']."</logisticsTel><logisticsOrderID>".$params['logi_no']."</logisticsOrderID>";
        $xml.="<SendGoodsList>";
        if(!empty($params['order']['ItemsList'])) {
            foreach ($params['order']['ItemsList'] as $item) {
                $xml .= "<ItemInfo><itemID>" . $item['itemID'] . "</itemID><sendGoodsCount>" . $item['sendGoodsCount'] . "</sendGoodsCount><belongProductsPromoID>" . $item['belongProductsPromoID'] . "</belongProductsPromoID><productItemId>" . $item['productItemId'] . "</productItemId></ItemInfo>";
            }
        }
        $xml.="</SendGoodsList>";
        $xml.="</OrderInfo>";
        $xml.="</OrdersList></request>";
        return $xml;
    }
    public function push_ship($params)
    {
        $params['order'] = $this->getOrderDetail($params['orderno']);
        $param = array(
            'method' => 'dangdang.order.goods.sendxml',
            'sendGoods' => $this->getXml($params),
        );
        $res = $this->_sendRequest($param);
        if (empty($res['Result']['operCode'])&&!empty($res['Result']['OrdersList'])) {
            $res = json_encode(array(
                'succ' => '1',
                'msg' => '发货成功'
            ));
        } else {
            if(!empty($res['Result']['operation'])){
                $msg=$res['Result']['operation'];
            }else{
                $msg='接口反馈错误';
            }
            $res = json_encode(array(
                'succ' => '0',
                'msg' => "当当网发货错误：".$msg
            ));
        }
        return $res;
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
            $orderDetail = $this->getOrderDetail($fx_order_id);
            if ($orderDetail['orderState'] != "101") continue;
            $express = rkcache('express', true);
            /** 判断当前商品是否需要重新发货 */
            $express_id = $oid_expressid_rels[$order['order_id']];
            $data = array();
            $data['orderno'] = $fx_order_id;
            $data['logi_no'] = $order['shipping_code'];
            $data['logi_name'] = $express[$express_id]['e_name'];
            if (!empty($data['logi_no'])) {
                $this->push_ship($data);
            }
        }
    }
}
