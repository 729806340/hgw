<?php

//接口文档地址:
//http://open.beibei.com/document/index.html
class BdianCron
{
    private $_scope = 'order_list,order_info,send_goods,aftersale_list';
    private $app_id = 'evej';
    private $secret = '64f6073fc3bc916f6c8a0b8b82a4f413';
    private $session = '868585db987159005ad989d2f10d7';  //正式
    private $gateway = 'http://api.open.beibei.com/outer_api/out_gateway/route.html';
    /** @var  FenxiaoService */
    private $_service;
    public static $source = "Bdian";
    public static $onlineDate = "2017-03-29 15:00:00"; // 上线日期，不保存上线日之前的订单
    public static $logicNames = array();
    const NO_ERROR = 20000;
    private $_errorCode = self::NO_ERROR;
    public function __construct($getRel = 1)
    {
        import('Curl');
        $this->timestamp = TIMESTAMP;
        $model_member = TModel("Member");
        $conditon = array();
        $condition = array(
            "member_name" => self::$source
        );
        $row = $model_member->where($condition)->find();
        $this->member_id = $row['member_id'];
        $model_member->execute("set wait_timeout=1000");
        // 商品映射
        if ($getRel) {
            $this->rel = $this->getGoodsRel();
            $this->oldRel = array();
        }
    }

    protected function gen_sign(array $params)
    {
        ksort($params);
        $sign_str = $this->secret;
        foreach ($params as $key => $value) {
            $sign_str .= $key . $value;
        }
        $sign_str .= $this->secret;
        return strtoupper(md5($sign_str));
    }

    protected function http_exec($request, $method)
    {
        $url = $this->gateway;
        return strtoupper($method) == 'POST' ? $this->http_post($url, $request) : $this->http_get($url, $request);
    }

    protected function http_get($url, $request = '')
    {
        $url .= '?' . $request;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_3) AppleWebKit/537.36 (KHTML, like Gecko)');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $res = curl_exec($ch);
        curl_close($ch);
        return $res;
    }

    protected function http_post($url, $request = '')
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_3) AppleWebKit/537.36 (KHTML, like Gecko)');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $res = curl_exec($ch);
        curl_close($ch);
        return $res;
    }


    private function _sendRequest($param, $authorize = false)
    {
        $sys_params = array(
            'app_id' => $this->app_id,
            'session' => $this->session,
            'timestamp' => TIMESTAMP
        );
        $sign_params = array_merge($param, $sys_params);
        $invoke_params = array_merge($sign_params,array('sign'=>$this->gen_sign($sign_params)));

        $url = $this->gateway;
        $curl = new Curl();
        $curl->setJsonDecoder(function ($response) {
            return json_decode($response, true);
        });
        $curl->setOpt(CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        $curl->post($url, $invoke_params);
        $method = $param['method'];
        if ($curl->error) {
            Log::record('贝贝熊 HTTP 请求失败! method:' . $method . ';Error:' . $curl->errorCode . ': ' . $curl->errorMessage);
            throw new Exception('贝贝熊 HTTP 请求失败! method:' . $method . ';Error:' . $curl->errorCode . ': ' . $curl->errorMessage);
        }
        $res = $curl->response;
        if ($res['success'] == false && !empty($res['data'])) {
            Log::record('贝贝熊返回错误! method:' . $method . '; Info:' . $res['message']);
        }
        return $res;
    }

    private function _getOrders($start_time = null, $end_time = null, $page = 1, $status)
    {
        $param = array(
            'method'=>'beibei.outer.trade.order.get',
            'start_time' => $start_time,
            'end_time' => $end_time,
            'status' => $status,
            'page_size' => 100,
            'page_no' => $page,
            'time_range'=>'pay_time',
        );

        $res = $this->_sendRequest($param);
        if (is_array($res['data'])) {
            if (isset($res['count']) && $res['count'] > $page * 100) $res['next'] = $page + 1;
            return $res;
        }
        return array('next' => false, 'count' => 0);
    }

    /**
     * 获取订单列表--导入订单至系统
     * @param array $params
     */
    public function orderlist($params = array())
    {
        $service = $params['service'] ;
        $start_time = isset($params['begin']) ? strtotime($params['begin']) : TIMESTAMP - 7 * 24 * 3600;
        $start_time = $start_time < strtotime(self::$onlineDate) ? strtotime(self::$onlineDate) : $start_time;

//        $start_time = '2017-10-05 00:00:00';$end_time = TIMESTAMP;
        $end_time =isset($params['end']) ? strtotime($params['end']):TIMESTAMP;
        $status = 1;
        $p = 1;
        do {
            $res = $this->_getOrders($start_time,$end_time,$p,$status);
            if (!empty($res)) {
                $sns = $service->getSavedidByApiorderno(array_column($res['data'], 'oid'));
                $items = array();
                foreach ($res['data'] as $order) {
                    if (in_array($order['oid'], $sns)) {
                        //
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

        $param = array(
            'page_no' => $page_no,
            'page_size' => $page_size,
            'method'=>'beibei.outer.item.warehouse.get'
        );
        $res = $this->_sendRequest($param);
        $data_out = array();
        foreach($res['data'] as $k => $v){
            $title = $v['title'];
            $item['goods_name'] = $title;
            $item['source'] = 'beibeiwang';
            foreach($v['sku'] as $k => $v){
                $sku_id = $v['id'];
                $item['sku_id'] = $sku_id;
            }
            $data_out[] = $item;
        }
        return $data_out;
    }

    public function  skuList($page_size,$page_no){
        $param = array(
            'page_no' => $page_no,
            'page_size' => $page_size,
            'method'=>'beibei.outer.item.warehouse.get'
        );
        $res = $this->_sendRequest($param);
        return $res;
    }

    private function _getRefundDetail($refund_id)
    {
        $param = array(
            'refund_id' => $refund_id,
            'method'=>'beibei.outer.refund.get'
        );
        $res = $this->_sendRequest($param);
        return $res['data']['0']['sku_id'];
    }

    private function _getAfterSaleOrders($page = 1, $start_time,$end_time, $status = -1)
    {
        $param = array(
            'start_time' => $start_time,
            'end_time' => $end_time,
            'page_no' => $page,
            'page_size' => 10,
            'method'=>'beibei.outer.refunds.get'
        );

        if ($status !== - 1)
            $param['status'] = $status;
        $res = $this->_sendRequest($param);

        if (is_array($res['data']) && $this->_errorCode == self::NO_ERROR) {
            if (isset($res['count']) && $res['count'] > $page * 10)
                $res['next'] = $page + 1;
            return $res;
        }

        return array(
            'next' => false,
            'count' => 0
        );
    }

    private function _prepareOrder($source)
    {

        // TODO 检查订单是否存在，若存在直接返回false，否则准备数据，并返回数据;
        $goodsList = $source['item'];
        $items = array();
        $hasError = false;

        foreach ($goodsList as $goods) {
            if (isset($this->rel[$goods['sku_id']]) && $this->rel[$goods['sku_id']]) {
                $goods_id = $this->rel[$goods['sku_id']];
            } else {
                $this->_error($source['oid'], "分销商品 {$goods['title']} 没有映射");
                $hasError = true;
                continue;
            }
            $items[] = array(
                'goods_id' => $goods_id,
                'name' => $goods['title'],
                'num' => $goods['num'],
                'price' => $goods['price'],
                'fxpid' => $goods['sku_id'],
                'oid' => isset($goods['iid']) ? $goods['iid'] : $source['oid']
            );
        }

        if (empty($items) || $hasError)
            return false;

        if(!isset($source['province']) || empty($source['province'])){
            $this->_error($source['oid'], "分销订单 ({$source['oid']}) 的收货地址省份信息获取失败，地区数据");
        }

        if(!isset($source['city']) || empty($source['city'])){
            $this->_error($source['oid'], "分销订单 ({$source['oid']}) 的收货地址城市信息获取失败，地区数据");
        }

        if(!isset($source['county']) || empty($source['county'])){
            $this->_error($source['oid'], "分销订单 ({$source['oid']}) 的收货地址县/市/区信息获取失败，地区数据");
        }


        $detail = array();
        $detail['order_sn'] = $source['oid']; // 分销系统订单编号
        $detail['buy_id'] = $this->member_id; // 分销商用户编号
        $detail['receiver'] = $source['receiver_name']; // 收件人
        $detail['provine'] = $source['province'];
        $detail['city'] = $source['city'];
        $detail['area'] = $source['county'];
        $detail['address'] = $source['address'];
        $detail['mobile'] = $source['receiver_phone']; // 手机号码
        $detail['remark'] = $source['remark'];
        $detail['payment_code'] = 'fenxiao';
        $detail['order_time'] = strtotime($source['createtime']); // 下单时间，时间戳
        $detail['item'] = $items;
        $detail['amount'] = $source['payment'];
        $detail['shipping_fee']=$source['shipping_fee'];
//        $detail['discount'] = $source['discount'];
        $detail['platform'] = 'new';
        return $detail;
    }

    // 获取订单详情
    public function getOrderDetail($orderSn)
    {
        $param = array(
            'method'=>'beibei.outer.trade.order.detail.get',
            'oid' => $orderSn
        );
        $res = $this->_sendRequest($param);
        return $this->_errorCode == self::NO_ERROR ? $res['data'] : false;
    }

    //获取物流列表
    public function getLogisticsList(){
        $param = array(
            'method'=>'beibei.outer.trade.logistics.get'
        );
        $res = $this->_sendRequest($param);
        $data = $res['data'];
        return $this->_errorCode == self::NO_ERROR ? $res['data'] : false;
    }

    function bbwLogiCode( $name )
    {
        $data = array(
            'AOL澳通速递' => 'aolau',
            '今枫国际快运' => 'jinfeng',
            '八达通' => 'bdatong',
            '中外速运' => 'zhongaosu',
            'ZENY增益海淘' => 'zengyisudi',
            '德邦物流' => 'debangwuliu', //
            '快捷速递' => 'kuaijiesudi', //
            'BPOST' 	 => 'bpost',
            '优速快递' => 'youshuwuliu', //
            '环球速运' => 'huanqiu',
            'EMS国际' => 'emsguoji', //
            '威时沛运'   => 'wtdchina',
            '中铁快运' => 'zhongtie', //--
            '安鲜达' => 'exfresh',
            '信联通' => 'sinatone',
            '飞洋快递' => 'shipgce',
            '一号仓' => 'onehcang',
            '国通快递' => 'guotongkuaidi',//
            '易达通' => 'yidatong',
            '九曳供应链' => 'jiuyescm',
            '心怡物流' => 'alog',
            'UEQ' => 'ueq',
            'HanBon汉邦国际' => 'handboy',
            'Sufast' => 'sufast',
            'PeakMore骏丰国际' => 'junfengguoji',
            'PCAExpress' => 'pcaexpress',
            'CNPEx中邮快递' => 'cnpex',
            'EWE全球快递' => 'ewe',
            '申通快递' => 'shentong', //
            '包裹平邮' => 'youzhengguonei',//
            '顺丰快递' => 'shunfeng',//
            '中华邮政' => 'youzhengguoji',
            'EMS' => 'ems',//
            '韵达快递' => 'yunda',//
            'EFS快递' => 'efs',
            '中通快递' => 'zhongtong',//
            'PANEX泛捷国际速递' => 'epanex',
            '圆通快递' => 'yuantong',//
            '安能物流' => 'annengwuliu',//
            '天天快递' => 'tiantian',//
            '百世汇通' => 'huitongkuaidi',//
            '佳成快递' => 'jiacheng',
            '邮政包裹'=>'youzhengguonei',
        ) ;
        return $data[$name] ? $data[$name] : 'ems' ;
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
                'orderSn' => $orderDetail['oid'],
                'orderStatus' => $orderDetail['status'] == 3 ? 3 : 0,
                'refundStatus' => 0
            );
            $res['orderStatus']['list'][] = $item;
        }
        $res['orderStatus']['result'] = 1;
        return $res;
    }

    // 获取商品映射
    private function getGoodsRel()
    {
        $result = TModel("B2cCategory")->where(array(
            'uid' => $this->member_id
        ))->select();
        $rel = $result ? array_column($result, 'pid', 'fxpid') : array();

        return $rel;
    }

    // 保存错误信息到日志table
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

    // 订单发货
    public function push_ship($params = array())
    {
        $param = array(
            'method'=>'beibei.outer.trade.logistics.ship',
            'oid' => trim($params['orderno']),
            'company' =>  $this -> bbwLogiCode( $params['logi_name'] ),
            'out_sid' => trim($params['logi_no'])
        );

        $res = $this->_sendRequest($param);
        if ($res['success'] == true) {
            $res = json_encode(array(
                'succ' => '1',
                'msg' => '发货成功'
            ));
        } else {
            $res = json_encode(array(
                'succ' => '0',
                'msg' => $res['message']
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

        for ($i = $limit; $i >= 1; $i --) {
            $b_time = TIMESTAMP - $i * 24 * 3600;
            $e_time = $b_time + 24 * 3600;
            $params['begin'] = date('Y-m-d H:i:s', $b_time);
            $params['end'] = date('Y-m-d H:i:s', $e_time);
            $this->orderlist($params);
        }
    }

    //同步退款数据到退款表
    public function getRefundOrder($service)
    {
        $p = 1;
        $begin = TIMESTAMP - 7 * 24 * 3600;
        $begin = $begin < strtotime(self::$onlineDate) ? strtotime(self::$onlineDate) : $begin;

//        $begin = '2017-03-05 00:00:00';
        $end_time = TIMESTAMP;
        $this->_service = $service;
        do {
            $res = $this->_getAfterSaleOrders($p,$begin,$end_time);
            if (! empty($res)) {
                $items = $this->_prepareRefund($this->_filterRefunds($res['data']));
                if (! empty($items))
                    $this->_service->createRefund(array(
                        'new' => $items
                    ));
            }
        } while ($p = $res['next']);
        return true;
    }

    /**
     * 过滤不需要处理的退款
     *
     * @param $items array
     * @return array
     */
    private function _filterRefunds($items)
    {
        $refunds = array();
        /**
         * 若订单未发货，但是部分退款，剔除
         */
        $fxIds = array_column($items, 'oid');
        /** @var orderModel $orderModel */
        $orderModel = Model('order');
        $payOrders = $orderModel->getOrderList(array(
            'fx_order_id' => array(
                'in',
                $fxIds
            ),
            'order_state' => ORDER_STATE_PAY
        ));
        $orders = $orderModel->getOrderList(array(
            'fx_order_id' => array(
                'in',
                $fxIds
            )
        ));

        $rel = array_column($payOrders, 'order_amount', 'fx_order_id');
        $orderIdRel = array_column($orders, 'order_id', 'fx_order_id');
        /**
         * 将退款格式转换未二级格式【order=》【goods=》【】】】
         */
        foreach ($items as $item) {
            //更新sku_id
            $sku_id = $this->_getRefundDetail($item['id']);
            $item['sku_id'] = $sku_id;

            // 处理商品映射关系
            if (! isset($this->rel[$item['sku_id']]) || empty($this->rel[$item['sku_id']])) {
                $this->_error($item['oid'], "分销商品 ({$item['title']}) 没有配置商品映射，无法生成退款");
                continue;
            }
            $item['goods_id'] = $this->rel[$item['sku_id']];
            $item['order_id'] = $orderIdRel[$item['oid']];
            if (! isset($refunds[$item['oid']]))
                $refunds[$item['oid']] = array();
            $refunds[$item['oid']][$item['goods_id']] = $item;
        }

        if (! empty($payOrders)) {
            foreach ($rel as $fxOrderId => $order_amount) {
                echo "过滤{$fxOrderId}\n";

                $refund_total = array_sum(array_column($refunds[$fxOrderId], 'price'));
                echo "退款金额{$refund_total}\n";
                echo "订单金额{$order_amount}\n";

                if (ncPriceFormat($refund_total) != ncPriceFormat($order_amount)) {
                    unset($refunds[$fxOrderId]);
                    $this->_error($fxOrderId, "未发货分销订单不是全额退款，无法生成退款");
                } else {
                    // 全额退款商品有多个时，只提交一次退款
                    if (count($refunds[$fxOrderId]) > 1) {
                        $tmp_key = current(array_keys($refunds[$fxOrderId]));
                        $tmp_value = current(array_values($refunds[$fxOrderId]));
                        $refunds[$fxOrderId] = array(
                            $tmp_key => $tmp_value
                        );
                    }
                }
            }
        }
        return $refunds;
    }

    /**
     * 准备退款数据
     *
     * @param $source array
     * @return bool|array
     */
    private function _prepareRefund($items)
    {
        // 过滤掉非全额退款订单，不做处理
        if (empty($items))
            return array();
        $order_sns = array_keys($items);

        $new_fsmodel = TModel("B2cOrderFenxiaoSub");
        $condition['orderno'] = array(
            'in',
            $order_sns
        );
        $re = $new_fsmodel->where($condition)->select();
        $result = $re ? $re : array();

        $newRefund = array();
        $returnModel = TModel('RefundReturn');

        foreach ($result as $suborder) {
            $orderno = $suborder['orderno'];
            $goods_id = $suborder['product_id'];

            // 匹配未付款子订单
            if (isset($items[$orderno][$goods_id])) {

                $ordersn = $this->_service->_getFxorderSn($orderno, $goods_id);

                if (! $ordersn)
                    continue;
                // 检查子订单是否已申请退款或取消订单
                $filter = array();
                $filter['order_sn'] = $ordersn;
                $filter['goods_id'] = array(
                    'in',
                    array(
                        0,
                        $goods_id
                    )
                );
                if ($returnModel->where($filter)->count() > 0) {
                    // echo "商品已申请<br>";
                    continue;
                }
                $data = array();
                $data['reason_id'] = 100; // 退款退货理由 整型
                $data['refund_type'] = 3; // 申请类型 1. 退款 2.退货
//                $data['return_type'] = $items[$orderno][$goods_id]['type'] == 2 ? 2 : 1; // 退货情况 1. 不用退货 2.需要退货
                $data['return_type'] = 3; // 退货情况 1. 不用退货 2.需要退货
                $data['seller_state'] = 1; // 卖家处理状态:1为待审核,2为同意,3为不同意,默认为1
                $data['refund_amount'] = $items[$orderno][$goods_id]['refund_fee']; // 退款金额
                $data['goods_num'] = isset($items[$orderno][$goods_id]['num']) ? $items[$orderno][$goods_id]['num'] : $items[$orderno][$goods_id]['num']; // 商品数量
                $data['buyer_message'] = $items[$orderno][$goods_id]['reason']; // 用户留言信息
                $data['ordersn'] = $ordersn; // 汉购网订单编号
                $data['goods_id'] = $suborder['product_id']; // 商品编号
                $data['create_time'] = strtotime($items[$orderno][$goods_id]['create_time']);
                $newRefund[] = $data;
            }
        }
        return $newRefund;
    }

    /**
     * 跟踪退款单状态
     * afterSalesStatus 1.买家申请退款，待商家处理;4.商家同意退款，退款中；9.商家驳回退款，待买家处理;
     * 12.买家逾期未处理，退款失败;3.平台处理中;4.平台同意退款，退款中;7.平台拒绝退款，退款关闭;5.退款成功;
     * 6.用户撤销了退款申请
     *
     * @param $service FenxiaoService
     * @return boolean
     */
    public function traceRefund($service)
    {
        $p = 1;
        $begin = TIMESTAMP - 7 * 24 * 3600;
        $begin = $begin < strtotime(self::$onlineDate) ? strtotime(self::$onlineDate) : $begin;

        $begin = TIMESTAMP - 7 * 24 * 3600;
        $end_time = TIMESTAMP;

        $this->_service = $service;
        do {
            $res = $this->_getAfterSaleOrders($p, $begin, $end_time);

            if (! empty($res)) {
                $items = $this->_filterTraceRefunds($res['data']);

                if (! empty($items))
                    $this->_updateRefund($items);
            }
        } while ($p = $res['next']);
        return true;
    }

    private function _updateRefund($items)
    {

        // 查找未完结的卷皮退款订单
        $refundModel = TModel("RefundReturn");

        /** @var RefundService $refundService */
        $refundService = Service("Refund");
        /** @var Model $model */
        $model = Model();
        // 根据退款状态做相应处理，处理取消退款以及退款完成的订单，其他状态保持不变不做处理
        foreach ($items as $orderId => $refunds) {
            foreach ($refunds as $item) {
                $refund = $refundModel->where(array(
                    'order_id' => $item['order_id'],
                    'goods_id' => $item['goods_id']
                ))->find();

                $params = array(
                    'refund_id' => $refund['refund_id'],
                    'op_id' => $this->member_id,
                    'op_name' => self::$source
                );
                $msg = "";

                //贝贝网退款中
//                if ($item['status'] == 1) {
//                    $params['seller_state'] = 1; //卖家待审核
//                    $method = 'edit_refund';
//                }

                // 退款成功
                if ($item['status'] == 2) {
                    v($params, 0);
                    $method = 'confirm_refund';
                }

                try {
                    $model->beginTransaction();
                    $res = $refundService->$method($params, $msg);
                    if (! $res) {
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
     *
     * @param
     *            $items
     * @return array
     */
    private function _filterTraceRefunds($items)
    {
        $refunds = array();
        /**
         * 若订单未发货，但是部分退款，剔除
         */
        $fxIds = array_column($items, 'oid');
        /** @var orderModel $orderModel */
        $orderModel = Model('order');
        $payOrders = $orderModel->getOrderList(array(
            'fx_order_id' => array(
                'in',
                $fxIds
            ),
            'order_state' => ORDER_STATE_PAY
        ));
        $orders = $orderModel->getOrderList(array(
            'fx_order_id' => array(
                'in',
                $fxIds
            )
        ));

        $rel = array_column($payOrders, 'order_amount', 'fx_order_id');
        $orderIdRel = array_column($orders, 'order_id', 'fx_order_id');

        /**
         * 将退款格式转换未二级格式【order=》【goods=》【】】】
         */
        foreach ($items as $item) {
            $sku_id = $this->_getRefundDetail($item['id']);
            $item['sku_id'] = $sku_id;
            // 处理商品映射关系
            if (! isset($this->rel[$item['sku_id']]) || empty($this->rel[$item['sku_id']])) {
                $this->_error($item['sku_id'], "分销商品 ({$item['title']}) 没有配置商品映射，无法生成退款");
                continue;
            }
            if ($item['reason'] == '买家申请退款，等待卖家处理')
                continue;
            $item['goods_id'] = $this->rel[$item['sku_id']];
            $item['order_id'] = $orderIdRel[$item['oid']];
            if (! isset($refunds[$item['oid']]))
                $refunds[$item['oid']] = array();
            $refunds[$item['oid']][$item['goods_id']] = $item;
        }
        return $refunds;
    }

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
            if (! $orderDetail)
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
}