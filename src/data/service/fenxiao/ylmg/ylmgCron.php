<?php

class ylmgCron
{
    private $app_id = 's11c9frmb0';
    private $secret = 'Gnr0ogLpxKUK49WNd7qrqgav31hH@TpS';
    private $gateway = 'http://s.yunlianmeigou.com/hangowa/support/api';
//    private  $gateway='http://112.74.27.159:8090';
    public static $source = "ylmg";
    public static $onlineDate = "2017-12-18 16:00:00";//上线的时间
    public $service;

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
    private function _sendRequest($param, $post = 'get')
    {
        $sys_params = array(
            'appid' => $this->app_id,
            'appsecret' => $this->secret,
            'date_type' => 1
        );
        $url = $this->gateway;
        $sign_params = array_merge($param, $sys_params);
        $invoke_params = array_merge($sign_params, array('sign' => $this->gen_sign($sign_params)));
        $curl = new Curl();
        $curl->setOpt(CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        $sys_params['begin_time'] = date("Y-m-d H:i:s", time());
        if ($post == 'get') {
            $curl->get($url, $invoke_params);
        } else {
            $url.="?method=".$invoke_params['method'];
            unset($invoke_params['method']);
            $curl->post($url, $invoke_params);
        }
        $method = $param['method'];
        if ($curl->response->code) {
            Log::record('云联美购返回错误! HTTP 请求失败! method:' . $method . ';Error:' . $curl->response->code . ': ' . $curl->response->description);
            try {
                throw new Exception('云联美购返回错误! HTTP 请求失败! method:' . $method . ';Error:' . $curl->response->code . ': ' . $curl->response->description);
            }catch(Exception $e){
                echo $e->getMessage();
            }

        }
        $res = json_decode(json_encode($curl->response), true);
        return $res;
    }

    /*获取签名*/
    protected function gen_sign(array $params)
    {
        ksort($params);
        $sign_str = "";
        foreach ($params as $key => $value) {
            $sign_str .= $key . "=" . $value . "&";
        }
//        $sign_str=substr($sign_str,0,strlen($sign_str)-1);
        $sign_str .= $this->secret;
        return sha1($sign_str);
    }

    /*获取订单详情*/
    private function getOrderDetail($orderSn)
    {
        $param = array(
            'method' => 'singleQueryOrder',
            'flow_id' => $orderSn
        );
//        Log::record("当前获取订单详情的订单为：".$orderSn);
        $res = $this->_sendRequest($param);
        return $res['code'] == '0' ? $res['data'] : false;
    }

    /**
     * 获取云联美购商品sku
     * @param array $params
     * @return array
     * @throws Exception
     */
    public function getSkuList($params = array())
    {
        $page_no = $params['page_no'];
        $page_size = $params['page_size'];
        $param = array(
            'method' => 'batchQueryGoods',
            'page_no' => $page_no,
            'page_size' => $page_size,
        );
        $res = $this->_sendRequest($param);
        $data_out = array();
        foreach ($res['data'] as $v) {
            $goods_name = $v['title'];
            foreach ($v['products'] as $v1) {
                $item['goods_name'] = $goods_name . '--' . $v1['sku_name'];
                $item['sku_id'] = $v1['sku'];
                $item['source'] = 'ylmg';
                $data_out[] = $item;
            }

        }
        return $data_out;
    }

    /*批量获取云联美购订单
    WAIT_SELLER_SEND_GOODS:等待卖家发货 /  WAIT_BUYER_CONFIRM_GOODS :卖家已发货,
    TRADE_BUYER_SIGNED:买家已签收      /  TRADE_FINISHED:交易成功,
    TRADE_CLOSED:取消订单。拼接。默认值：WAIT_SELLER_SEND_GOODS
    */
    private function _getOrders($start_time = null, $end_time = null, $page = 1, $status = 'WAIT_SELLER_SEND_GOODS')
    {
        $param = array(
            'method' => 'batchQueryOrder',
            'start_time' => $start_time,
            'end_time' => $end_time,
            'page_no' => $page,
            'page_size' => 100,
            'out_status' => $status,
            'pay_status'=>'PAYED'
        );
        $res = $this->_sendRequest($param);
        if (is_array($res)) {
            if (isset($res['totalCount']) && $res['totalCount'] > $page * 100) $res['next'] = $page + 1;
            return $res;
        }
        return array('next' => false, 'count' => 0);
    }


    public function orderlist($params = array())
    {
        $this->service = $params['service'];
        $start_time = isset($params['begin']) ? strtotime($params['begin']) : TIMESTAMP - 7 * 24 * 3600;
        $start_time = $start_time < strtotime(self::$onlineDate) ? date('Y-m-d H:i:s', strtotime(self::$onlineDate)) : date('Y-m-d H:i:s', $start_time);
        $end_time = isset($params['end']) ? $params['end'] : date('Y-m-d H:i:s', TIMESTAMP);    //$end_time = date('Y-m-d H:i:s',TIMESTAMP);
        $p = 1;
//        $start_time="2018-01-07 00:41:00";
//        $end_time="2018-01-07 00:43:00";
        do {
            $res = $this->_getOrders($start_time, $end_time, $p);
//            v($res);
            if ($res['code'] == '0') {
//                $sns = $service->getSavedidByApiorderno(array_column($res['data'], 'order_items'));
                $items = array();
                foreach ($res['data'] as $order) {
//                    if (in_array($order['flow_id'], $sns)) continue;
                    if ($item = $this->_prepareOrder($order,$this->service))
                        $items=array_merge($items,$item);

                }
                if (!empty($items)) $createRes = $this->service->doCreateOrder($items);
            }
        } while ($p = $res['next']);
    }

    private function _prepareOrder($source,$service)
    {
        // TODO 检查订单是否存在，若存在直接返回false，否则准备数据，并返回数据
        $goodsList = $source['order_items'];
        $items = array();
        $hasError = false;
        $detail=array();
        foreach ($goodsList as $k=>$goods) {
            //获取已存在的分销订单，返回的订单号中存在该订单号则跳过，不再重复写入
            $is_exist=$service->getSavedidByApiorderno(array($goods['item_id']));
//            $is_exist=Model('orders')->where(array('fx_order_id'=>$goods['item_id']))->limit(1)->count();
            if(!empty($is_exist)) continue;
            if (isset($this->rel[$goods['goods_id']]) && $this->rel[$goods['goods_id']]) {
                $goods_id = $this->rel[$goods['goods_id']];
            } else {
                $this->_error($source['flow_id'], "分销商品 {$goods['name']} 没有映射");
                $hasError = true;
                continue;
            }
            $items= array(
                array('goods_id' => $goods_id,
                'name' => $goods['name'],
                'num' => $goods['nums'],
                'price' => $goods['price'],
                'fxpid' => $goods['goods_id'],
                'oid' => isset($goods['goods_id']) ? $goods['goods_id'] : $source['flow_id'])
            );
            if (empty($items) || $hasError)
                return false;
            if (!isset($source['province']) || empty($source['province'])) {
                $this->_error($source['flow_id'], "分销订单 ({$source['flow_id']}) 的收货地址省份信息获取失败，地区数据");
            }
            if (!isset($source['city']) || empty($source['city'])) {
                $this->_error($source['flow_id'], "分销订单 ({$source['flow_id']}) 的收货地址城市信息获取失败，地区数据");
            }
            if (!isset($source['district']) || empty($source['district'])) {
                $this->_error($source['flow_id'], "分销订单 ({$source['flow_id']}) 的收货地址县/市/区信息获取失败，地区数据");
            }

            $detail[$k]['order_sn'] = $goods['item_id']; // 分销系统订单编号
            $detail[$k]['buy_id'] = $this->member_id; // 分销商用户编号
            $detail[$k]['receiver'] = $source['consignee_name']; // 收件人
            $detail[$k]['provine'] = $source['province'];
            $detail[$k]['city'] = $source['city'];
            $detail[$k]['area'] = $source['district'];
            $detail[$k]['address'] = $source['address'];
            $detail[$k]['mobile'] = $source['consignee_mobile']; // 手机号码
            $detail[$k]['remark'] = $source['memo'];//买家留言
            $detail[$k]['payment_code'] = 'fenxiao';
            $detail[$k]['order_time'] = strtotime($source['created']); // 下单时间，时间戳
            $detail[$k]['item'] = $items;
            $detail[$k]['amount'] = $source['final_amount'];
            $detail[$k]['platform'] = 'new';
            $detail[$k]['shipping_fee'] = $source['ship_fee'];//运费
        }
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
        if (!$result)
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
        if (!$orders)
            die('no orders');

        foreach ($orders as $order) {
            $fx_order_id = $order['fx_order_id'];
            $goodsWhere = array();
            $goodsWhere['order_id'] = $order['order_id'];
            $order_items = TModel('order_goods')->where($goodsWhere)->select();
            if (!$order_items)
                continue;

            //查出的待发货订单
            $orderDetail = $this->getOrderDetail($fx_order_id);
            if ($orderDetail['ship_status'] != 'NOT_SHIP')
                continue;
            $express = rkcache('express', true);

            $express_id = $oid_expressid_rels[$order['order_id']];
            $data = array();
            $data['orderno'] = $fx_order_id;
            $data['logi_no'] = $order['shipping_code'];
            $data['logi_name'] = $express[$express_id]['e_name'];
//            Log::record('发货单号为：'.$fx_order_id.',date:'.date('Y-m-d H:i:s',time()));
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
        for ($i = $limit; $i >= 1; $i--) {
            $b_time = TIMESTAMP - $i * 24 * 3600;
            $e_time = $b_time + 24 * 3600;
            $params['begin'] = date('Y-m-d H:i:s', $b_time);
            $params['end'] = date('Y-m-d H:i:s', $e_time);
            $this->orderlist($params);
        }
    }

    function rrypLogiCode($name)
    {
        $data = array(
            '快捷快递' => 'kuaijiesudi',
            '邮政包裹' => 'youzhengguonei',
            '德邦物流' => 'debangwuliu',
            'EMS' => 'ems',
            'EMS国际' => 'emsguoji',
            '凡客如风达' => 'rufengda',
            '龙邦快递' => 'longbanwuliu',
            '联邦快递' => 'lianbangkuaidi',
            '全一快递' => 'quanyikuaidi',
            '全峰快递' => 'quanfengkuaidi',
            '申通快递' => 'shentong',
            '顺丰快递' => 'shunfeng',
            '速尔快递' => 'suer',
            '天天快递' => 'tiantian',
            '天地华宇' => 'tiandihuayu',
            'USPS' => 'usps',
            '新邦物流' => 'xinbangwuliu',
            '圆通快递' => 'yuantong',
            '韵达快递' => 'yunda',
            '优速快递' => 'youshuwuliu',
            '中通快递' => 'zhongtong',
            '中铁快运' => 'zhongtiewuliu',
            '宅急送' => 'zhaijisong',
            '中邮物流' => 'zhongyouwuliu',
            '国通快递' => 'guotongkuaidi',
            '京东快递' => 'jd',
            '百世汇通' => 'huitongkuaidi',
            '安能物流' => 'ane66'
        );
        return $data[$name] ? $data[$name] : 'ems';
    }

    /*筛选快递*/
    public function checkExpresses($logi_name = "")
    {
        $param = array(
            'method' => 'batchQueryExpresses',
        );
        $res = $this->_sendRequest($param);
        if ($res['code'] != '0') {
            return false;
        }
        foreach ($res['data'] as $v) {
            if ($v['name'] == $logi_name) {
                $expre_no = $v['code'];
                break;
            }
        }
        return isset($expre_no) && $expre_no != '' ? $expre_no : 'other';
    }

    /*商品进行发货*/
    public function push_ship($params = array())
    {
        $param = array(
            'method' => 'deliverOrder',
            'flow_id' => trim($params['orderno']),
//            'flow_id' =>'2017121728eb5faf0361',
            'express_code' => $this->rrypLogiCode($params['logi_name']),
//            'express_code' => $express_array,
//            'express_no' => '9891791303991'
            'express_no' => trim($params['logi_no'])
        );

        $res = $this->_sendRequest($param, 'post');
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
                'orderSn' => $fx_order_id,
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

    /*获取退货的订单*/
    public function getRefundOrder($service)
    {
        $page = '1';
        $begin = isset($_GET['begin']) ? strtotime($_GET['begin']):time() - 7 * 24 * 3600;
        $begin = $begin < strtotime(self::$onlineDate) ? strtotime(self::$onlineDate) : $begin;
//        $begin = 1484841600;
        $end = isset($_GET['end']) ? strtotime($_GET['end']) : time();
        $createTime = $begin . '|' . $end;
        $this->service = $service;
        do {
            $res = $this->_getReturnOrder($createTime, $page);
            if (!empty($res['data'])) {
                $items = $this->_prepareRefund($this->_filterRefunds($res['data']));
                if (!empty($items)) $this->service->createRefund(array('new' => $items));
            }
        } while ($page = $res['next']);
        return true;
    }

    /*获取退货订单*/
    public function _getReturnOrder($modified_time = null, $page = 1)
    {
        $create_time = explode('|', $modified_time);
        $param = array(
            'method' => 'queryRefund',
            'start_time' => date('Y-m-d H:i:s', $create_time[0]),
            'end_time' => date('Y-m-d H:i:s', $create_time[1]),
            'pageNo' => "{$page}",
            'pageSize' => '30'
        );
        $res = $this->_sendRequest($param);
        if ($res['code']==0) {
            if (isset($res['totalCount']) && $res['totalCount'] > $page * 30) $res['next'] = $page + 1;
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
            $item['goods_id'] = $this->rel[$item['productCode']];
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
            $ordersn = $this->service->_getFxorderSn($orderno, $goods_id);
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
            if ($items[$orderno][$goods_id]['refundtype'] == "") { //为空代表退货
                $data['return_type'] = 2;
                $data['refund_type'] = 2;
            } elseif ($items[$orderno][$goods_id]['refundtype'] == "1") { //为1代表仅退款
                $data['return_type'] = 1;
                $data['refund_type'] = 1;
            }
            $data['seller_state'] = 1; //卖家处理状态:1为待审核,2为同意,3为不同意,默认为1
            $data['refund_amount'] = $items[$orderno][$goods_id]['returnMoney'];//退款金额
            $data['goods_num'] = 1;//商品数量
            $data['buyer_message'] = $items[$orderno][$goods_id]['returnReason'] == "" ? "无" : $items[$orderno][$goods_id]['returnReason'];  //申请原因
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
        $p = '1';
        $begin = isset($_GET['begin']) ? strtotime($_GET['begin']) : time() - 7 * 24 * 3600;
        $begin = $begin < strtotime(self::$onlineDate) ? strtotime(self::$onlineDate) : $begin;
        $end = isset($_GET['end']) ? strtotime($_GET['end']) : time();
        $createTime = $begin . '|' . $end;
        $this->service = $service;
        do {
            $res = $this->_getReturnOrder($createTime, $p);
            if (!empty($res['data'])) {
                $items = $this->_filterTraceRefunds($res['data']);
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
                    if (!isset($method) || $method == "") {
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
            $item['goods_id'] = $this->rel[$item['productCode']];
            $item['order_id'] = $orderIdRel["{$item['orderCode']}"];
            if (!isset($refunds["{$item['orderCode']}"])) $refunds["{$item['orderCode']}"] = array();
            $refunds["{$item['orderCode']}"][$item['goods_id']] = $item;
        }
        return $refunds;
    }

}