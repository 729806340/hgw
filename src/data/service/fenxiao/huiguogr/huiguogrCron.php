<?php

class huiguogrCron
{

    private $_scope = 'order_list,order_info,send_goods,get_express';
    private $_config = array(
        'host' => 'http://open.huiguo.net',
        'key' => 'c06f86216cc5c92b83c063b45b3fbe30',
        'secret' => 'e5807f10788f6b6422eae41d2512d545',
       // 'cus' => '26CE61DAD1C32E6D229A',
        'cus'=> 'hgAF0EFB7F16383F332C',
    );
//    private $_devConfig = array(
//        'host' => 'http://59.172.39.250:2016',
//        'key' => 'b3fbdd35403ac92cd6ff28e74d6eb3d0',
//        'secret' => '293c13563f816184553efe6eab59c9ae',
//    );
    const NO_ERROR = 20000;
    private $_errorCode = self::NO_ERROR;
    private $_errorMessage = array(
        10001 => '参数丢失',
        10002 => '非法请求，不在的接口',
        10003 => '不存在的SECRET或SECRET不可用',
        10004 => 'TOKEN无效，过期或者不存在',
        10005 => 'ERP权限不正确',
        10006 => 'ERP帐号被禁用',
        10007 => '没有查询到数据',
        10008 => '不存在的订单',
        10009 => '订单状态不在待发货或备货中',
        10010 => 'ERP帐号异常',
        10011 => '商家状态不可用',
        10012 => '不存在此商家',
        10013 => '商家密钥错误',
        10014 => '商家密钥过期',
        10015 => '订单已发货',
        10016 => '订单商品全部售后中',
        10017 => '发货号和快递公司不匹配',
        10018 => '订单类型不能发货',
        10019 => '库存设置保护时间(1分钟)',
        10020 => '减少库存数大于实时库库存/设置库存量错误',
        10021 => '服务化库存查询错误',
        10022 => '服务化库存设置错误',
        10023 => '入库商品不能修改库存',
        10024 => '用户与商品所属商家不一致',
        10025 => '商品未上架/已下架, 不可修改库存',
        10026 => '入库商品不可查询',
        10027 => '物流公司不存在',
        10028 => '商品数据错误, 无法同步库存',
        10029 => '订单类型不支持查询/修改/发货',
        10030 => '等待上架商品sku修改后总库存不能低于10',
        10031 => '上架商品sku修改后库存必须大于销量+50',
        10032 => '不支持该订单状态修改收货地址',
        10033 => '该商品不能被erp同步库存',
        10034 => '发货失败(当前订单不在可发货状态)',
        10035 => '发货失败(物流单号已被使用)',
        10036 => '发货失败(物流单号已有物流信息)',
        10037 => '试用商品，不支持库存设置',
        10038 => '发货商品中存在仓储商品，不可以发货',
        10040 => 'TOKEN失效',
        10041 => '签名错误',
        10042 => 'TOKEN权限不足',
        10043 => '赠品与订单不匹配或者赠品未找到',
        10044 => '批量更新库存sku数超过限制',
        10045 => '批量更新库存时，必须同时为增或者为减，且与标识一致',
        10046 => '不支持多商品同时批量更新库存',
        50000 => '服务器错误，如redis写入失败',
        50001 => 'scope为空',
        50002 => 'Ip访问频率过高受限',
        50003 => '单个用户访问频率受限',
    );
    /** @var  FenxiaoService */
    private $_service;
    public static $source = "huiguogr";
    public static $onlineDate = "2018-3-8 11:00:00"; //上线日期，不保存上线日之前的订单
    //在拼多多平台添加了映射的物流名称

    public static $logicNames = array();


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
            //$this -> oldRel = $this -> getOldGoodsRel() ;
            $this->oldRel = array();
        }
    }

    private function _getConfig()
    {
//        if (C('ON_DEV')) return $this->_devConfig;
        return $this->_config;
    }

    private function _getApiHost()
    {
        $config = $this->_getConfig();
        return $config['host'];
    }

    private function _getKey()
    {
        $config = $this->_getConfig();
        return $config['key'];
    }

    private function _getSecret()
    {
        $config = $this->_getConfig();
        return $config['secret'];
    }

    private function _getCus()
    {
        $config = $this->_getConfig();
        return $config['cus'];
    }


    private function _sendRequest($param, $authorize = false)
    {
        $host = $this->_getApiHost();
        $path = $authorize === true ? '/erpapi/authorize' : '/erpapi/index';
        if ($authorize !== true) {
            $param['jCusKey'] = $this->_getCus();
            $param['token'] = $this->_getToken();
            $sign = $this->_genSign($param);
            $param['sign'] = $sign;

        }
        $curl = new Curl();
        $curl->setJsonDecoder(function ($response) {
            return json_decode($response, true);
        });
        $curl->setOpt(CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        $curl->post($host . $path, $param);
        if ($curl->error) {
            Log::record('会过 HTTP 请求失败! Path:' . $path . ';Error:' . $curl->errorCode . ': ' . $curl->errorMessage);
            throw new Exception('会过 HTTP 请求失败! Path:' . $path . ';Error:' . $curl->errorCode . ': ' . $curl->errorMessage);
        }
        $res = $curl->response;
        if ($res['status'] == 0) {
            $this->_errorCode = $res['info'];
            if ($res['info'] == 10007) return array();
            Log::record('会过返回错误! Path:' . $path . '; Info:' . $res['info']);
            //throw new Exception('卷皮返回错误! Path:' . $path . '; Info:' . $res['info']);
        }
        return $res['data'];
    }

    private function _genSign($param)
    {
        ksort($param);
        $param['code'] = $this->_getSecret();
        $httpParam = http_build_query($param);
        return md5($httpParam);
    }

    /**
     * 获取Token
     * @return mixed
     * @throws Exception
     */
    private function _getToken()
    {
        $data = json_decode(rkcache(md5('huiguo.token')), true);
        if (isset($data['token']) && !empty($data['token']) && isset($data['expires']) && $data['expires'] - 600 > TIMESTAMP) return $data['token'];
        $param = array(
            'secret' => $this->_getKey(),
            'scope' => $this->_scope,
            'type' => 'json'
        );
        $res = $this->_sendRequest($param, true);
        if ($this->_errorCode == self::NO_ERROR) {
            wkcache(md5('huiguo.token'), json_encode($res));
            return $res['token'];
        }
        throw new Exception('获取token失败，错误信息：' . $this->_errorCode);
    }


    private function _getOrders($create_time = null, $page = 1, $status = '2,9')
    {
        $param = array(
            'jType' => 'order_list',
            'jOrderStatus' => $status,
            'jPagesize' => 100,
            'jPage' => $page,
            'show_detail' => 1
        );
        if ($create_time !== null) $param['create_time'] = $create_time;
        $res = $this->_sendRequest($param);
        if (is_array($res) && $this->_errorCode == self::NO_ERROR) {
            if (isset($res['count']) && $res['count'] > $page * 100) $res['next'] = $page + 1;
            return $res;
        }
        return array('next' => false, 'count' => 0);
    }

    private function _getAfterSaleOrders($page = 1, $modified_time = null, $status = -1)
    {
        $param = array(
            'jType' => 'aftersale_list',
            'jPagesize' => 100,
            'jPage' => $page,
        );
        if ($modified_time !== null) $param['modefied_time'] = $modified_time;
        if ($status !== -1) $param['status'] = $status;
        $res = $this->_sendRequest($param);
        if (is_array($res) && $this->_errorCode == self::NO_ERROR) {
            if (isset($res['count']) && $res['count'] > $page * 100) $res['next'] = $page + 1;
            return $res;
        }
        return array('next' => false, 'count' => 0);
    }


    private function _prepareOrder($source)
    {
        // TODO 检查订单是否存在，若存在直接返回false，否则准备数据，并返回数据
        $goodsList = $source['goodslist'];
        $items = array();
        $hasError = false;
        foreach ($goodsList as $goods) {
            if (isset($this->rel[$goods['goods_sku_id']]) && $this->rel[$goods['goods_sku_id']]) {
                $goods_id = $this->rel[$goods['goods_sku_id']];
            } else {
                $this->_error($source['orderno'], "分销商品 {$goods['goodsName']} ({$goods['goods_sku_id']}) 没有映射");
                $hasError = true;
                continue;
            }
            $items[] = array(
                'goods_id' => $goods_id,
                'name' => $goods['goodsname'],
                'num' => $goods['goodsnum'],
                'price' => $goods['goodsprice'],
                'fxpid' => $goods['goods_sku_id'],
                'oid' => isset($goods['ordergoodsid']) ? $goods['ordergoodsid'] : $source['orderno'],
            );
        }
        if (empty($items) || $hasError) return false;

        $newArea = explode('|', $source['new_area']);
        if(!isset($newArea[0])||empty($newArea[0])){
            $this->_error($source['orderno'],
                "分销订单 ({$source['orderno']}) 的收货地址省份信息获取失败，地区数据：{$source['new_area']}");
        }else{
            if(!isset($newArea[1])||empty($newArea[1])){
                $this->_error($source['orderno'],
                    "分销订单 ({$source['orderno']}) 的收货地址城市信息获取失败，地区数据：{$source['new_area']}");
            }
            else if(!isset($newArea[2])||empty($newArea[2])){
                Log::record("分销订单 ({$source['orderno']}) 的收货地址县/市/区信息获取失败，地区数据：{$source['new_area']}");
            }
        }
        $detail = array();
        $detail['order_sn'] = $source['orderno']; //分销系统订单编号
        $detail['buy_id'] = $this->member_id; //分销商用户编号
        $detail['receiver'] = $source['buyername'];//收件人
        $detail['provine'] = isset($newArea[0])?$newArea[0]:'';
        $detail['city'] = isset($newArea[1])?$newArea[1]:'';
        $detail['area'] = isset($newArea[2])?$newArea[2]:'';
        $detail['address'] = $source['buyeraddress'];
        $detail['mobile'] = $source['buyerphone']; //手机号码
        $detail['remark'] = $source['remark'];
        $detail['payment_code'] = 'fenxiao';
        $detail['order_time'] = strtotime($source['paytime']);//下单时间，时间戳
        $detail['item'] = $items;
        $detail['amount'] = $source['payamount'];
        $detail['discount'] = $source['discount'];
        $detail['platform'] = 'new';
        $detail['shipping_fee']=$source['payexpress'];//运费
        return $detail;
    }

    public function test()
    {
        dkcache('huiguo.token');
        $res = $this->_getAfterSaleOrders();
        if ($this->_errorCode != self::NO_ERROR) var_dump($this->_errorMessage[$this->_errorCode]);
        return $res;
    }

    /**
     * 获取订单列表
     * @param array $params
     */
    public function orderlist($params = array())
    {
        /** @var FenxiaoService $service */
        $service = $params['service'];
        $begin = isset($params['begin']) ? strtotime($params['begin']) : TIMESTAMP - 7*24 * 3600;
        $begin = $begin < strtotime(self::$onlineDate) ? strtotime(self::$onlineDate) : $begin;
        $createTime = isset($params['end']) ? $begin . '|' . strtotime($params['end']) : $begin . '|' . TIMESTAMP;
        $status = isset($_GET['order_state']) ? $_GET['order_state'] : '2,9';
//        $createTime='1520438400|'.TIMESTAMP;
        $p = 1;
        do {
            $res = $this->_getOrders($createTime, $p, $status);
            if (!empty($res)) {
                $sns = $service->getSavedidByApiorderno(array_column($res['lists'], 'orderno'));
                $items = array();
                foreach ($res['lists'] as $order) {
                    if (in_array($order['orderno'], $sns)) {
                        //
                        continue;
                    }
                    if ($item = $this->_prepareOrder($order)) $items[] = $item;
                }
//                v($items);
                if (!empty($items)) $createRes = $service->doCreateOrder($items);
            }
        } while ($p = $res['next']);
    }

    /**
     * 会过暂时不提供相关的商品接口
     * @param array $params
     * @return array
     * @throws Exception
     */


//    public function getSkuList($params = array()){
//        $page_no = $params['page_no'];
//        $page_size = $params['page_size'];
//        $param = array(
//            'jType' => 'sgoods_list',
//            'type' => 'json',
//            'field' => 'cgid,sgoodsid,sgoodsid_v2,cgid,title,cname',
//            'jPagesize' => $page_size,
//            'jPage' => $page_no
//        );
//        $res = $this->_sendRequest($param);
//        $data_out = array();
//        foreach($res['list'] as $k => $v){
//            $item['goods_name'] = $v['title'];
//            $item['source'] = 'juanpi';
//            $param = array(
//                'jType' => 'sgoods_info',
//                'jSgoodsId' => $v['sgoodsid'],
//                'field' => 'skuid,zid_value,fid_value'
//            );
//            $good_info = $this->_sendRequest($param);
//            foreach($good_info['list'] as $sku_info){
//                $item['sku_id'] = $sku_info['skuid'];
//                $item['goods_name']=$v['title']."【规格{$sku_info['fid_value']}】";
//                $data_out[] = $item;
//            }
//        }
//        return $data_out;
//    }


    //获取订单详情
    public function getOrderDetail($orderSn)
    {
        $param = array(
            'jType' => 'order_info',
            'jOrderNo' => $orderSn,
        );
        $res = $this->_sendRequest($param);
        return $this->_errorCode == self::NO_ERROR ? $res : false;
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
            $orderDetail = $this->getOrderDetail($fx_order_id);
            //组装所有分销渠道该接口的统一的返回数据格式
            $item = array(
                'orderSn' => $orderDetail['orderno'],
                'orderStatus' => $orderDetail['status'] == 5 ? 3 : 0,
                'refundStatus' => 0,
            );
            $res['orderStatus']['list'][] = $item;

        }
        $res['orderStatus']['result'] = 1;
        return $res;
    }

    //获取商品映射
    private function getGoodsRel()
    {
        $result = TModel("B2cCategory")->where(array('uid' => $this->member_id))->select();
//        $result=(new Model("b2c_category"))->where(array('uid' => $this->member_id))->select();
        $rel = $result ? array_column($result, 'pid', 'fxpid') : array();
        return $rel;
    }

    //保存错误信息到日志table
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

    //订单发货
    public function push_ship($params = array())
    {
        $logi = isset($this->express[$params['logi_name']]) ? $this->express[$params['logi_name']] : $this->express['EMS'];

        $param = array(
            'jType' => 'send_goods',
            'jOrderNo' => $params['orderno'],
            'jDeliverEname' => $logi['code'],
            'jDeliverCname' => $logi['companyname'],
            'jDeliverNo' => trim($params['logi_no']),
            'jIsSplit' => $params['orderno'] == $params['oid'] ? 0 : 1,
            'jOrderGoodsIds' => $params['oid'],
        );
        $this->_sendRequest($param);
        if ($this->_errorCode == self::NO_ERROR) {
            $res = json_encode(array('succ' => '1', 'msg' => '发货成功'));
        } else {
            //订单是已发货也算发货成功
            if(strpos($this->_errorMessage[$this->_errorCode],"已发货")){
                $res = json_encode(array('succ' => '1', 'msg' => '发货成功'));
                return $res;
            }
            $res = json_encode(array('succ' => '0', 'msg' => $this->_errorMessage[$this->_errorCode]));
            $message = "订单号：{$params['orderno']}，原因：" . $this->_errorMessage[$this->_errorCode] . "，推送参数：" . json_encode($param);
            $emailObj = new Email ();
            //$emailObj->send_sys_email('zenxiangjie@hansap.com', "会过发货错误提醒", $message);
            //$emailObj->send_sys_email('shenlei@hansap.com', "会过发货错误提醒", $message);
        }
        return $res;
    }

    /**
     * 漏单检测，凌晨检测前3天的未发货订单是否已保存为汉购网订单
     */
//    public function checkUnsaveOrder($params)
//    {
//        $hour = date('G');
//        if ($hour >= 9 && $params['preDay'] == 1) {
//            $params['preDay'] = 1;
//        }
//        log::selflog("check unsave order begin", self::$source);
//
//        $limit = $params['preDay'] == 0 ? 1 : $params['preDay'];
//
//        for ($i = $limit; $i >= 1; $i--) {
//            $b_time = TIMESTAMP - $i * 24 * 3600;
//            $e_time = $b_time + 24 * 3600;
//            $params['begin'] = date('Y-m-d H:i:s', $b_time);
//            $params['end'] = date('Y-m-d H:i:s', $e_time);
//            $this->orderlist($params);
//        }
//    }

//    public function getRefundOrder($service)
//    {
//        $p = 1;
//        $begin = TIMESTAMP - 7 * 24 * 3600;
//        $begin = $begin < strtotime(self::$onlineDate) ? strtotime(self::$onlineDate) : $begin;
//        $createTime = $begin . '|' . TIMESTAMP;
//        $this->_service = $service;
//        do {
//            $res = $this->_getAfterSaleOrders($p, $createTime);
//            if (!empty($res)) {
//                $items = $this->_prepareRefund($this->_filterRefunds($res['list']));
//                if (!empty($items)) $this->_service->createRefund(array('new' => $items));
//            }
//        } while ($p = $res['next']);
//        return true;
//    }

    /**
     * 过滤不需要处理的退款
     * @param $items array
     * @return array
     */
//    private function _filterRefunds($items)
//    {
//        $refunds = array();
//        /** 若订单未发货，但是部分退款，剔除 */
//        $fxIds = array_column($items, 'orderno');
//        /** @var orderModel $orderModel */
//        $orderModel = Model('order');
//        $payOrders = $orderModel->getOrderList(array('fx_order_id' => array('in', $fxIds),
//            'order_state' => ORDER_STATE_PAY));
//        $orders = $orderModel->getOrderList(array('fx_order_id' => array('in', $fxIds)));
//        $rel = array_column($payOrders, 'order_amount', 'fx_order_id');
//        $orderIdRel = array_column($orders, 'order_id', 'fx_order_id');
//        /** 将退款格式转换未二级格式【order=》【goods=》【】】】 */
//        foreach ($items as $item) {
//            // 处理商品映射关系
//            if (!isset($this->rel[$item['skuid']]) || empty($this->rel[$item['skuid']])) {
//                $this->_error($item['orderno'], "分销商品 ({$item['goodstitle']}) 没有配置商品映射，无法生成退款");
//                continue;
//            }
//            $item['goods_id'] = $this->rel[$item['skuid']];
//            $item['order_id'] = $orderIdRel[$item['orderno']];
//            if (!isset($refunds[$item['orderno']])) $refunds[$item['orderno']] = array();
//            $refunds[$item['orderno']][$item['goods_id']] = $item;
//        }
//        if (!empty($payOrders)) {
//            foreach ($rel as $fxOrderId => $order_amount) {
//                echo "过滤{$fxOrderId}\n";
//
//                $refund_total = array_sum(array_column($refunds[$fxOrderId], 'money'));
//                echo "退款金额{$refund_total}\n";
//                echo "订单金额{$order_amount}\n";
//
//                if (ncPriceFormat($refund_total) != ncPriceFormat($order_amount)) {
//                    unset($refunds[$fxOrderId]);
//                    $this->_error($fxOrderId, "未发货分销订单不是全额退款，无法生成退款");
//                } else {
//                    //全额退款商品有多个时，只提交一次退款
//                    if (count($refunds[$fxOrderId]) > 1) {
//                        $tmp_key = current(array_keys($refunds[$fxOrderId]));
//                        $tmp_value = current(array_values($refunds[$fxOrderId]));
//                        $refunds[$fxOrderId] = array($tmp_key => $tmp_value);
//                    }
//                }
//            }
//        }
//
//        return $refunds;
//    }
//
//    /**
//     * 准备退款数据
//     * @param $source array
//     * @return bool|array
//     */
//    private function _prepareRefund($items)
//    {
//        //过滤掉非全额退款订单，不做处理
//        if (empty($items)) return array();
//        $order_sns = array_keys($items);
//
//        $new_fsmodel = TModel("B2cOrderFenxiaoSub");
//        $condition['orderno'] = array('in', $order_sns);
//        $re = $new_fsmodel->where($condition)->select();
//        $result = $re ? $re : array();
//        $newRefund = array();
//        $returnModel = TModel('RefundReturn');
//        foreach ($result as $suborder) {
//            $orderno = $suborder['orderno'];
//            $goods_id = $suborder['product_id'];
//
//            //匹配未付款子订单
//            if (isset($items[$orderno][$goods_id])) {
//
//                $ordersn = $this->_service->_getFxorderSn($orderno, $goods_id);
//                if (!$ordersn) continue;
//                //检查子订单是否已申请退款或取消订单
//                $filter = array();
//                $filter['order_sn'] = $ordersn;
//                $filter['goods_id'] = array('in', array(0, $goods_id));
//                if ($returnModel->where($filter)->count() > 0) {
//                    //echo "商品已申请<br>";
//                    continue;
//                }
//
//                $data = array();
//                $data['reason_id'] = 100; //退款退货理由 整型
//
//                $data['refund_type'] = 3;
//                $data['return_type'] = 3;
//                //1.仅退款 2.退货退款 3.发货前退款 4.售后补发 5.拒收退款
//                if($items[$orderno][$goods_id]['type'] == 1 or $items[$orderno][$goods_id]['type'] == 2){
//                    $data['refund_type'] = $items[$orderno][$goods_id]['type'];
//                }
//                $data['return_type'] = $data['refund_type'];
//
//                $data['seller_state'] = 1; //卖家处理状态:1为待审核,2为同意,3为不同意,默认为1
//                $data['refund_amount'] = $items[$orderno][$goods_id]['money'];//退款金额
//                $data['goods_num'] = isset($items[$orderno][$goods_id]['goods_nums']) ? $items[$orderno][$goods_id]['goods_nums'] : $items[$orderno][$goods_id]['goodsnums'];//商品数量
//                $data['buyer_message'] = $items[$orderno][$goods_id]['reason_text'];  //用户留言信息
//                $data['ordersn'] = $ordersn;  //汉购网订单编号
//                $data['goods_id'] = $suborder['product_id']; //商品编号
//                $data['create_time'] = strtotime($items[$orderno][$goods_id]['addtime']);  //售后订单产生时间
//                $newRefund[] = $data;
//            }
//        }
//        return $newRefund;
//    }
//
//
//    /**
//     * 跟踪退款单状态
//     * afterSalesStatus 1.买家申请退款，待商家处理;4.商家同意退款，退款中；9.商家驳回退款，待买家处理;
//     * 12.买家逾期未处理，退款失败;3.平台处理中;4.平台同意退款，退款中;7.平台拒绝退款，退款关闭;5.退款成功;
//     * 6.用户撤销了退款申请
//     * @param $service FenxiaoService
//     * @return boolean
//     */
//    public function traceRefund($service)
//    {
//
//        $p = 1;
//        $this->_service = $service;
//        do {
//            $res = $this->_getAfterSaleOrders($p, null, -1);
//            if (!empty($res)) {
//                $items = $this->_filterTraceRefunds($res['list']);
//                if (!empty($items)) $this->_updateRefund($items);
//            }
//        } while ($p = $res['next']);
//        return true;
//    }
//
//    private function _updateRefund($items)
//    {
//        //查找未完结的卷皮退款订单
//        $refundModel = TModel("RefundReturn");
//
//        /** @var RefundService $refundService */
//        $refundService = Service("Refund");
//        /** @var Model $model */
//        $model = Model();
//        //根据退款状态做相应处理，处理取消退款以及退款完成的订单，其他状态保持不变不做处理
//        foreach ($items as $orderId => $refunds) {
//            foreach ($refunds as $item) {
//                if (in_array($item['dealstatus_text'], array('买家申请退款，等待卖家处理', '卖家审核通过，等待买家寄回', '卖家审核不通过', '买家已寄回，等待卖家确认收货', '维权中', '卖家同意退款，等待退款到账'))) continue;
//                $refund = $refundModel->where(array('order_id' => $item['order_id'], 'goods_id' => $item['goods_id']))->find();
//                //var_dump($refundModel->getLastSql());
//                $params = array(
//                    'refund_id' => $refund['refund_id'],
//                    'op_id' => $this->member_id,
//                    'op_name' => self::$source
//                );
//                $msg = "";
//                //分销用户取消退款，解锁订单(相当于平台商家拒绝退款)
//                if ($item['dealstatus_text'] == '卖家拒绝退款' || $item['dealstatus_text'] == '售后撤销' || $item['dealstatus_text'] == '售后关闭') {
//                    $params['seller_state'] = 3;
//                    $method = 'edit_refund';
//                }
//                //退款完成
//                if ($item['dealstatus_text'] == '退款成功') {
//                    v($params, 0);
//                    $method = 'confirm_refund';
//                }
//
//                try {
//                    $model->beginTransaction();
//                    $res = $refundService->$method($params, $msg);
//                    if (!$res) {
//                        throw new Exception($msg);
//                    }
//                    $model->commit();
//                } catch (Exception $e) {
//                    $model->rollback();
//                    $msg = $e->getMessage();
//                }
//                v($msg, 0);
//            }
//        }
//    }
//
//    /**
//     * 过滤退款跟踪数据
//     * @param $items
//     * @return array
//     */
//    private function _filterTraceRefunds($items)
//    {
//        $refunds = array();
//        /** 若订单未发货，但是部分退款，剔除 */
//        $fxIds = array_column($items, 'orderno');
//        /** @var orderModel $orderModel */
//        $orderModel = Model('order');
//        $payOrders = $orderModel->getOrderList(array('fx_order_id' => array('in', $fxIds),
//            'order_state' => ORDER_STATE_PAY));
//        $orders = $orderModel->getOrderList(array('fx_order_id' => array('in', $fxIds)));
//        $rel = array_column($payOrders, 'order_amount', 'fx_order_id');
//        $orderIdRel = array_column($orders, 'order_id', 'fx_order_id');
//        /** 将退款格式转换未二级格式【order=》【goods=》【】】】 */
//        foreach ($items as $item) {
//            // 处理商品映射关系
//            if (!isset($this->rel[$item['skuid']]) || empty($this->rel[$item['skuid']])) {
//                $this->_error($item['orderno'], "分销商品 ({$item['goodstitle']}) 没有配置商品映射，无法生成退款");
//                continue;
//            }
//            if ($item['dealstatus_text'] == '买家申请退款，等待卖家处理') continue;
//            $item['goods_id'] = $this->rel[$item['skuid']];
//            $item['order_id'] = $orderIdRel[$item['orderno']];
//            if (!isset($refunds[$item['orderno']])) $refunds[$item['orderno']] = array();
//            $refunds[$item['orderno']][$item['goods_id']] = $item;
//        }
//        return $refunds;
//    }

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
            if (!$orderDetail) continue;
            $express = rkcache('express', true);

            foreach ($orderDetail['goodslist'] as $unship) {
                //查询fenxiao_items表，匹配分销子订单ID
                $fsubWhere = array();
                $fsubWhere['orderno'] = $fx_order_id;
                $fsubWhere['product_id'] = array('in', array_column($order_items, 'goods_id'));
                $fSub = TModel('b2c_order_fenxiao_sub')->where($fsubWhere)->select();

                foreach ($fSub as $sub) {
                    /** 判断当前商品是否需要重新发货 */
                    if ($unship['ordergoodsid'] == $sub['oid'] && $unship['goodsstatus'] == 0) {
                        $express_id = $oid_expressid_rels[$order['order_id']];
                        $data = array();
                        $data['orderno'] = $fx_order_id;
                        $data['logi_no'] = $order['shipping_code'];
                        $data['logi_name'] = $express[$express_id]['e_name'];
                        $data['num'] = $sub['num'];
                        $data['oid'] = $sub['oid'];

                        $this->push_ship($data);
                    }
                }

            }
        }
    }

    public $express = array(
        "EMS" => array(
            "id" => "6",
            "companyname" => "EMS经济快递",
            "code" => "ems",
            "comurl" => "http://www.ems.com.cn/",
            "comtel" => "11185",
            "rule" => "^\\w[13)$",
            "source" => "1"
        ),
        "fanyukuaidi" => array(
            "id" => "7",
            "companyname" => "凡宇快递",
            "code" => "fanyukuaidi",
            "comurl" => "http://www.fanyu56.com.cn/",
            "comtel" => "4006-580-358",
            "rule" => "^[0-9a-zA-Z)[1,)$",
            "source" => "0"
        ),
        "lianhaowuliu" => array(
            "id" => "8",
            "companyname" => "联昊通",
            "code" => "lianhaowuliu",
            "comurl" => "http://www.lhtex.com.cn",
            "comtel" => "0769-88620000",
            "rule" => "^[0-9a-zA-Z)[1,)$",
            "source" => "2"
        ),
        "quanyikuaidi" => array(
            "id" => "10",
            "companyname" => "全一快递",
            "code" => "quanyikuaidi",
            "comurl" => "http://www.unitop-apex.com/",
            "comtel" => "400-663-1111",
            "rule" => "^[0-9a-zA-Z)[1,)$",
            "source" => "2"
        ),
        "city100" => array(
            "id" => "11",
            "companyname" => "城市100",
            "code" => "city100",
            "comurl" => "",
            "comtel" => "010-52932760",
            "rule" => "^[0-9a-zA-Z)[1,)$",
            "source" => "2"
        ),
        "suer" => array(
            "id" => "12",
            "companyname" => "速尔快递",
            "code" => "suer",
            "comurl" => "http://www.sure56.com",
            "comtel" => "400-158-9888",
            "rule" => "^[0-9a-zA-Z)[1,)$",
            "source" => "2"
        ),
        "圆通快递" => array(
            "id" => "13",
            "companyname" => "圆通速递",
            "code" => "yuantong",
            "comurl" => "http://www.yto.net.cn",
            "comtel" => "95554",
            "rule" => "^[0-9a-zA-Z)[1,)$",
            "source" => "1"
        ),
        "中通快递" => array(
            "id" => "14",
            "companyname" => "中通快递",
            "code" => "zhongtong",
            "comurl" => "http://www.zto.cn",
            "comtel" => "95311",
            "rule" => "^\\d[12,13)$",
            "source" => "1"
        ),
        "feiyuanvipshop" => array(
            "id" => "15",
            "companyname" => "飞远配送",
            "code" => "feiyuanvipshop",
            "comurl" => "http://www.fyps.cn/",
            "comtel" => "400-703-1313",
            "rule" => "^[0-9a-zA-Z)[1,)$",
            "source" => "0"
        ),
        "宅急送" => array(
            "id" => "16",
            "companyname" => "宅急送",
            "code" => "zhaijisong",
            "comurl" => "http://www.zjs.com.cn",
            "comtel" => "400-6789-000",
            "rule" => "^([\\w)[13)|[\\w)[10))$",
            "source" => "1"
        ),
        "韵达快递" => array(
            "id" => "17",
            "companyname" => "韵达快递",
            "code" => "yunda",
            "comurl" => "http://www.yundaex.com",
            "comtel" => "95546",
            "rule" => "^\\d[13)$",
            "source" => "1"
        ),
        "天天快递" => array(
            "id" => "18",
            "companyname" => "天天快递",
            "code" => "tiantian",
            "comurl" => "http://www.ttkdex.com",
            "comtel" => "400-188-8888",
            "rule" => "^\\w[12)$",
            "source" => "2"
        ),
        "百世汇通" => array(
            "id" => "20",
            "companyname" => "百世快递",
            "code" => "huitongkuaidi",
            "comurl" => "http://www.800bestex.com",
            "comtel" => "400-956-5656",
            "rule" => "^\\w[12,14)$",
            "source" => "1"
        ),
        "lianbangkuaidi" => array(
            "id" => "21",
            "companyname" => "联邦快递",
            "code" => "lianbangkuaidi",
            "comurl" => "http://cndxp.apac.fedex.com/dxp.html",
            "comtel" => "400-889-1888",
            "rule" => "^[0-9a-zA-Z)[1,)$",
            "source" => "2"
        ),
        "德邦物流" => array(
            "id" => "22",
            "companyname" => "德邦物流",
            "code" => "debangwuliu",
            "comurl" => "http://www.deppon.com",
            "comtel" => "95353",
            "rule" => "^[0-9a-zA-Z)[1,)$",
            "source" => "2"
        ),
        "zhongtiewuliu" => array(
            "id" => "23",
            "companyname" => "中铁快运",
            "code" => "zhongtiewuliu",
            "comurl" => "http://www.cre.cn",
            "comtel" => "95572",
            "rule" => "^[0-9a-zA-Z)[1,)$",
            "source" => "0"
        ),
        "ztky" => array(
            "id" => "24",
            "companyname" => "中铁物流",
            "code" => "ztky",
            "comurl" => "http://www.ztky.com",
            "comtel" => "400-000-5566",
            "rule" => "^[0-9a-zA-Z)[1,)$",
            "source" => "0"
        ),
        "xinfengwuliu" => array(
            "id" => "25",
            "companyname" => "信丰物流",
            "code" => "xinfengwuliu",
            "comurl" => "http://www.xf-express.com.cn",
            "comtel" => "400-830-6333",
            "rule" => "^[0-9a-zA-Z)[1,)$",
            "source" => "2"
        ),
        "顺丰快递" => array(
            "id" => "26",
            "companyname" => "顺丰速运",
            "code" => "shunfeng",
            "comurl" => "http://www.sf-express.com",
            "comtel" => "95338",
            "rule" => "^\\d[12)$",
            "source" => "1"
        ),
        "申通快递" => array(
            "id" => "27",
            "companyname" => "申通快递",
            "code" => "shentong",
            "comurl" => "http://www.sto.cn",
            "comtel" => "95543",
            "rule" => "^\\d[12,13)$",
            "source" => "1"
        ),
        "快捷速递" => array(
            "id" => "30",
            "companyname" => "快捷快递",
            "code" => "kuaijiesudi",
            "comurl" => "http://www.fastexpress.com.cn/",
            "comtel" => "400-830-4888",
            "rule" => "^\\d[12,14)$",
            "source" => "1"
        ),
        "xinbangwuliu" => array(
            "id" => "31",
            "companyname" => "新邦物流",
            "code" => "xinbangwuliu",
            "comurl" => "http://www.xbwl.cn",
            "comtel" => "4008-000-222",
            "rule" => "^[0-9a-zA-Z)[1,)$",
            "source" => "0"
        ),
        "jiajiwuliu" => array(
            "id" => "33",
            "companyname" => "佳吉快运",
            "code" => "jiajiwuliu",
            "comurl" => "http://www.jiaji.com/",
            "comtel" => "400-820-5566",
            "rule" => "^[0-9a-zA-Z)[1,)$",
            "source" => "2"
        ),
        "ganzhongnengda" => array(
            "id" => "34",
            "companyname" => "能达速递",
            "code" => "ganzhongnengda",
            "comurl" => "http://www.nengdaex.com",
            "comtel" => "400-6886-765",
            "rule" => "^[0-9a-zA-Z)[1,)$",
            "source" => "2"
        ),
        "优速快递" => array(
            "id" => "35",
            "companyname" => "优速物流",
            "code" => "youshuwuliu",
            "comurl" => "http://www.uc56.com",
            "comtel" => "400-1111-119",
            "rule" => "^[0-9a-zA-Z)[1,)$",
            "source" => "1"
        ),
        "zengyisudi" => array(
            "id" => "36",
            "companyname" => "增益速递",
            "code" => "zengyisudi",
            "comurl" => "http://www.zeny-express.com/",
            "comtel" => "4008-456-789",
            "rule" => "^[0-9a-zA-Z)[1,)$",
            "source" => "2"
        ),
        "cces" => array(
            "id" => "37",
            "companyname" => "CCES",
            "code" => "cces",
            "comurl" => "http://www.gto365.com",
            "comtel" => "400-111-1123",
            "rule" => "",
            "source" => "1"
        ),
        "邮政包裹" => array(
            "id" => "38",
            "companyname" => "邮政快递包裹",
            "code" => "youzhengguonei",
            "comurl" => "http://yjcx.chinapost.com.cn",
            "comtel" => "11183",
            "rule" => "^[0-9a-zA-Z)[1,)$",
            "source" => "1"
        ),
        "jinguangsudikuaijian" => array(
            "id" => "39",
            "companyname" => "京广快递",
            "code" => "jinguangsudikuaijian",
            "comurl" => "http://www.szkke.com/",
            "comtel" => "0769-88629888",
            "rule" => "^[0-9a-zA-Z)[1,)$",
            "source" => "0"
        ),
        "UPS" => array(
            "id" => "40",
            "companyname" => "UPS快递",
            "code" => "UPS",
            "comurl" => "http://www.ups.com/cn",
            "comtel" => "400-820-8388",
            "rule" => "^[0-9a-zA-Z)[1,)$",
            "source" => "0"
        ),
        "yafengsudi" => array(
            "id" => "41",
            "companyname" => "亚风快递",
            "code" => "yafengsudi",
            "comurl" => "http://www.airfex.net/",
            "comtel" => "4001-000-002",
            "rule" => "^[0-9a-zA-Z)[1,)$",
            "source" => "0"
        ),
        "datianwuliu" => array(
            "id" => "42",
            "companyname" => "大田快运",
            "code" => "datianwuliu",
            "comurl" => "http://www.dtw.com.cn/",
            "comtel" => "400-626-1166",
            "rule" => "^[0-9a-zA-Z)[1,)$",
            "source" => "0"
        ),
        "dhl" => array(
            "id" => "43",
            "companyname" => "DHL代理",
            "code" => "dhl",
            "comurl" => "http://www.cn.dhl.com/",
            "comtel" => "",
            "rule" => "^[0-9a-zA-Z)[1,)$",
            "source" => "0"
        ),
        "youzhengguoji" => array(
            "id" => "44",
            "companyname" => "国际快递查询",
            "code" => "youzhengguoji",
            "comurl" => "http://www.ems.com.cn/",
            "comtel" => "",
            "rule" => "^[0-9a-zA-Z)[1,)$",
            "source" => "0"
        ),
        "anxindakuaixi" => array(
            "id" => "45",
            "companyname" => "安信达快递",
            "code" => "anxindakuaixi",
            "comurl" => "http://www.anxinda.com/",
            "comtel" => "400-626-2356",
            "rule" => "^[0-9a-zA-Z)[1,)$",
            "source" => "0"
        ),
        "yuefengwuliu" => array(
            "id" => "46",
            "companyname" => "越丰物流",
            "code" => "yuefengwuliu",
            "comurl" => "http://www.yfexpress.com.hk",
            "comtel" => "00852-23909969",
            "rule" => "^[0-9a-zA-Z)[1,)$",
            "source" => "0"
        ),
        "hkpost" => array(
            "id" => "47",
            "companyname" => "香港进口",
            "code" => "hkpost",
            "comurl" => "http://www.xianggangjinkou.com/",
            "comtel" => "400-086-0002",
            "rule" => "^[0-9a-zA-Z)[1,)$",
            "source" => "0"
        ),
        "yitongfeihong" => array(
            "id" => "48",
            "companyname" => "一统快递",
            "code" => "yitongfeihong",
            "comurl" => "http://yitongfeihong.com",
            "comtel" => "",
            "rule" => "^[0-9a-zA-Z)[1,)$",
            "source" => "0"
        ),
        "yibangwuliu" => array(
            "id" => "49",
            "companyname" => "一邦速递",
            "code" => "yibangwuliu",
            "comurl" => "http://www.ebon-express.com",
            "comtel" => "18688486668",
            "rule" => "^[0-9a-zA-Z)[1,)$",
            "source" => "0"
        ),
        "国通快递" => array(
            "id" => "50",
            "companyname" => "国通快递",
            "code" => "guotongkuaidi",
            "comurl" => "http://www.gto365.com/",
            "comtel" => "400-111-1123",
            "rule" => "^[0-9a-zA-Z)[1,)$",
            "source" => "1"
        ),
        "Feikangda" => array(
            "id" => "51",
            "companyname" => "飞康达快运",
            "code" => "Feikangda",
            "comurl" => "",
            "comtel" => "010-84223376",
            "rule" => "^[0-9a-zA-Z)[1,)$",
            "source" => "2"
        ),
        "saiaodi" => array(
            "id" => "52",
            "companyname" => "赛澳递",
            "code" => "saiaodi",
            "comurl" => "http://www.51cod.com/",
            "comtel" => "400-034-5888",
            "rule" => "^[0-9a-zA-Z)[1,)$",
            "source" => "2"
        ),
        "quanritongkuaidi" => array(
            "id" => "53",
            "companyname" => "全日通快递",
            "code" => "quanritongkuaidi",
            "comurl" => "http://www.at-express.com/",
            "comtel" => "020-86298988",
            "rule" => "^[0-9a-zA-Z)[1,)$",
            "source" => "0"
        ),
        "yuntongkuaidi" => array(
            "id" => "54",
            "companyname" => "运通中港物流",
            "code" => "yuntongkuaidi",
            "comurl" => "http://www.ytkd168.com/",
            "comtel" => "0769-81156999",
            "rule" => "^[0-9a-zA-Z)[1,)$",
            "source" => "0"
        ),
        "jiayunmeiwuliu" => array(
            "id" => "55",
            "companyname" => "加运美速递",
            "code" => "jiayunmeiwuliu",
            "comurl" => "http://www.tms56.com/",
            "comtel" => "0769-85515555",
            "rule" => "^[0-9a-zA-Z)[1,)$",
            "source" => "0"
        ),
        "auspost" => array(
            "id" => "57",
            "companyname" => "澳邮快运",
            "code" => "auspost",
            "comurl" => "http://www.auexpress.com.au/",
            "comtel" => "130-007-9988",
            "rule" => "^[0-9a-zA-Z)[1,)$",
            "source" => "0"
        ),
        "emsguoji" => array(
            "id" => "58",
            "companyname" => "EWE全球快递",
            "code" => "emsguoji",
            "comurl" => "https://www.everfast.com.au/",
            "comtel" => "1300096655",
            "rule" => "^[0-9a-zA-Z)[1,)$",
            "source" => "0"
        ),
        "jiayiwuliu" => array(
            "id" => "59",
            "companyname" => "佳怡物流",
            "code" => "jiayiwuliu",
            "comurl" => "http://www.jiayi56.com/",
            "comtel" => "400-631-9999",
            "rule" => "^[0-9a-zA-Z)[1,)$",
            "source" => "0"
        ),
        "sutongwuliu" => array(
            "id" => "60",
            "companyname" => "速通物流",
            "code" => "sutongwuliu",
            "comurl" => "http://www.sut56.com/",
            "comtel" => "4006561185",
            "rule" => "^[0-9a-zA-Z)[1,)$",
            "source" => "0"
        ),
        "yuananda" => array(
            "id" => "61",
            "companyname" => "源安达",
            "code" => "yuananda",
            "comurl" => "http://www.yadex.com.cn/",
            "comtel" => "0769-85021875",
            "rule" => "^[0-9a-zA-Z)[1,)$",
            "source" => "0"
        ),
        "baishiwuliu" => array(
            "id" => "62",
            "companyname" => "百世物流",
            "code" => "baishiwuliu",
            "comurl" => "http://www.800best.com",
            "comtel" => "4008856561",
            "rule" => "^[0-9a-zA-Z)[1,)$",
            "source" => "0"
        ),
        "ririshunwuliu" => array(
            "id" => "63",
            "companyname" => "日日顺物流",
            "code" => "ririshunwuliu",
            "comurl" => "http://www.rrs.com/wl/",
            "comtel" => "4009999999",
            "rule" => "^[0-9a-zA-Z)[1,)$",
            "source" => "1"
        ),
        "hongtaiwuliu" => array(
            "id" => "64",
            "companyname" => "鸿泰物流",
            "code" => "hongtaiwuliu",
            "comurl" => "http://www.hnht56.com/index.html",
            "comtel" => "4008607777",
            "rule" => "^[0-9a-zA-Z)[1,)$",
            "source" => "1"
        ),
        "tengdawuliu" => array(
            "id" => "65",
            "companyname" => "腾达物流",
            "code" => "tengdawuliu",
            "comurl" => "http://www.tengdawl.com/",
            "comtel" => "4006337777",
            "rule" => "^[0-9a-zA-Z)[1,)$",
            "source" => "1"
        ),
        "yuxingwuliu" => array(
            "id" => "66",
            "companyname" => "宇鑫物流",
            "code" => "yuxingwuliu",
            "comurl" => "http://www.yx56.cn/",
            "comtel" => "4006005566",
            "rule" => "^[0-9a-zA-Z)[1,)$",
            "source" => "1"
        ),
        "pingandatengfei" => array(
            "id" => "67",
            "companyname" => "平安达腾飞",
            "code" => "pingandatengfei",
            "comurl" => "http://www.padtf.com/",
            "comtel" => "4009990988",
            "rule" => "^[0-9a-zA-Z)[1,)$",
            "source" => "0"
        ),
        "rufengda" => array(
            "id" => "69",
            "companyname" => "如风达",
            "code" => "rufengda",
            "comurl" => "http://www.rufengda.com/",
            "comtel" => "4000106660",
            "rule" => "^[0-9a-zA-Z)[1,)$",
            "source" => "0"
        ),
        "hengluwuliu" => array(
            "id" => "70",
            "companyname" => "恒路",
            "code" => "hengluwuliu",
            "comurl" => "http://www.e-henglu.com/",
            "comtel" => "4001826666",
            "rule" => "^([\\w)[13)|[\\w)[10))$",
            "source" => "0"
        ),
        "wanxiangwuliu" => array(
            "id" => "71",
            "companyname" => "万象物流",
            "code" => "wanxiangwuliu",
            "comurl" => "http://www.ewinshine.com/",
            "comtel" => "4008208088",
            "rule" => "^[\\d\\w\\-)[1,)$",
            "source" => "2"
        ),
        "emsbiaozhun" => array(
            "id" => "72",
            "companyname" => "EMS标准快递",
            "code" => "emsbiaozhun",
            "comurl" => "http://www.ems.com.cn/index.html",
            "comtel" => "11183",
            "rule" => "",
            "source" => "1"
        ),
        "disifang" => array(
            "id" => "73",
            "companyname" => "递四方",
            "code" => "disifang",
            "comurl" => "http://www.4px.com/",
            "comtel" => "0755-23508000",
            "rule" => "",
            "source" => "0"
        ),
        "汇通速递" => array(
            "id" => "74",
            "companyname" => "汇通天下物流",
            "code" => "httx56",
            "comurl" => "http://www.httx56.com/",
            "comtel" => "0755-21636332",
            "rule" => "",
            "source" => "0"
        ),
        "chinapost" => array(
            "id" => "75",
            "companyname" => "China Post(中国邮政)",
            "code" => "chinapost",
            "comurl" => "http://yjcx.chinapost.com.cn/zdxt/yjcx/",
            "comtel" => "11183",
            "rule" => "",
            "source" => "1"
        ),
        "pcaexpress" => array(
            "id" => "76",
            "companyname" => "PCA Express（PCA快递）",
            "code" => "pcaexpress",
            "comurl" => "http://www.pcaexpress.com.au/",
            "comtel" => "1800518000",
            "rule" => "",
            "source" => "0"
        ),
        "vipexpress" => array(
            "id" => "77",
            "companyname" => "鹰运国际速递",
            "code" => "vipexpress",
            "comurl" => "http://www.vip-express.com.au/",
            "comtel" => "0862614860",
            "rule" => "",
            "source" => "0"
        ),
        "anneng"=>array(
            "id"=> "32",
            "companyname"=>"安能物流",
            "companypinyin"=>"annengwuliu",
            "code"=>"annengwuliu",
            "comtel"=> "400-104-0088",
            "comurl"=> "",
            "rule" => "^[0-9a-zA-Z)[1,)$",
            "source"=>"0"
        ),
        "安能物流"=>array(
            "id"=> "32",
            "companyname"=>"安能物流",
            "companypinyin"=>"annengwuliu",
            "code"=>"annengwuliu",
            "comtel"=> "400-104-0088",
            "comurl"=> "",
            "rule" => "^[0-9a-zA-Z)[1,)$",
            "source"=>"0"
        )
    );

}