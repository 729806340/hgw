<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/9/19 0019
 * Time: 下午 5:06
 */
class chuchutongCron
{
    static $orgname = "JZDYiX15";
    static $appid = "7dff23dcb7846d03fd2e05aa4e04cbca";
    static $secret = "5aa9c29ce63cf4bacf64a228fad113b12bbf0580";
    private $apiUri = "https://parter.chuchutong.com/sqe/Order/";
    static $onlineDate = "2017-9-25 9:00:00"; // 上线日期
    public static $source = "chuchutong";
    function __construct($getRel = 1) {
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

    function orderlist($params){
        $service = $params['service'] ;
        $beginTime = $_GET['begin'] ? strtotime($_GET['begin']) : TIMESTAMP - 7*24*3600 ;
//        $beginTime=1507392000;
        $endTime = $_GET['end'] ? strtotime($_GET['end']) : TIMESTAMP ;
        $p= 0;
        do{
            $res=$this->_getOrders($beginTime,$endTime,$p);
            if($res['code']=='0'){
                $orderdata=array_column($res['info'], 'order');
                $sns = $service->getSavedidByApiorderno(array_column($orderdata, 'order_id'));
                $items=array();
                foreach($res['info'] as $order){
                    if(in_array($order['order']['order_id'],$sns)) continue;
                    if($item=$this->_prepareOrder($order))
                        $items[]=$item;
                }
                if(!empty($items)) $createRes=$service->doCreateOrder($items);
            }
        }while($p=$res['next']);
    }

     /*订单详情接口*/
    function getOrderDetail($order_no) {
        $params = array(
            'page_no' => 0,
            'page_size' => 1,
            'order_id'=>$order_no
        );
        $result = $this->_sendRequest('get_order_list_v2', $params);
        if ($result['code'] == 0 && ! empty($result['max_page'])) {
            return $result['info'];
        } else {
            return array();
        }
    }

    /*获取订单接口*/
    function _getOrders($beginTime,$endTime,$page){
        $params = array(
            'status' => 2,
            'ctime_start' => date('Y-m-d H:i:s',$beginTime),
            'ctime_end' => date('Y-m-d H:i:s',$endTime),
            'page' => $page,
            'page_size' =>50
        );
        $res=$this->_sendRequest('get_order_list_v2',$params);
        if(is_array($res) && $res['total_num']>0) {
            if (isset($res['total_num']) && $res['total_num'] > $page * 50) $res['next'] = $page + 1;
            return $res;
        }
        return array('next' => false, 'count' => 0);
    }

    private function _sendRequest($url_path,$param){
        $url = $this->apiUri . $url_path . '?' . http_build_query($param);
        $curl = new Curl();
        $header_prams = array(
            'org_name' => self::$orgname,
            'app_key' => self::$appid,
            'nonce' => rand(10000, 999999),
            'timestamp' => time()
        );
        $curl->setHeader('Org-Name', $header_prams['org_name']);
        $curl->setHeader('App-Key', $header_prams['app_key']);
        $curl->setHeader('Nonce', $header_prams['nonce']);
        $curl->setHeader('Timestamp', $header_prams['timestamp']);
        $curl->setHeader('Signature', $this->_genSign($header_prams));
        $curl->setOpt(CURLOPT_SSL_VERIFYPEER, false);
        $curl->setOpt(CURLOPT_SSL_VERIFYHOST , false);
        $curl->setOpt(CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        $curl->get($url);
        if ($curl->error) {
            Log::record('楚楚通 HTTP 请求失败! Path:' . $url . ';Error:' . $curl->errorCode . ': ' . $curl->errorMessage);
            throw new Exception('楚楚通 HTTP 请求失败! Path:' . $url . ';Error:' . $curl->errorCode . ': ' . $curl->errorMessage);
        }
        $res = json_decode($curl->response, true);
        return $res;
    }

    private function _genSign($header_prams) {
        //签名:sha1(Nonce + AppSecret +Timestamp)
        $sign = sha1($header_prams['nonce'] . self::$secret . $header_prams['timestamp']);
        return $sign;
    }

    public function getSkuList($params = array()){
        $page_no = $params['page_no']-1;
        $page_size = $params['page_size'];
        $param = array(
            'page' => $page_no,
            'page_size' => $page_size,
            'goods_status'=>'1'
        );
        $res = $this->_sendRequest('get_goodsinfo_for_key',$param);
        $data_out = array();
        foreach($res['info'] as $v){
            $goods_name = $v['goods_title'];
            foreach($v['sku'] as $v1){
                $item['goods_name'] = $goods_name.'--'.$v1['value'];
                $item['sku_id'] = $v1['sku_id'];
                $item['source'] = self::$source;
                $data_out[] = $item;
            }
        }
        return $data_out;
    }

    private function _prepareOrder($source)
    {
        // TODO 检查订单是否存在，若存在直接返回false，否则准备数据，并返回数据
        $goodsList = $source['goods'];
        $items = array();
        $hasError = false;
        foreach ($goodsList as $goods) {
            if (isset($this->rel[$goods['propN']['sku_id']]) && $this->rel[$goods['propN']['sku_id']]) {
                $goods_id = $this->rel[$goods['propN']['sku_id']];
            } else {
                $this->_error($source['order']['order_id'], "分销商品 {$goods['goods_title']} 没有映射");
                $hasError = true;
                continue;
            }
            $items[] = array(
                'goods_id' => $goods_id,
                'name' => $goods['goods_title'],
                'num' => $goods['amount'],
                'price' => $goods['price'],
                'fxpid' => $goods['propN']['sku_id'],
                'oid' => isset($goods['propN']['sku_id']) ? $goods['propN']['sku_id'] : $source['order']['order_id']
            );
        }
        if (empty($items) || $hasError)
            return false;
        if(!isset($source['address']['province']) || empty($source['address']['province'])){
            $this->_error($source['order']['order_id'], "分销订单 ({$source['order']['order_id']}) 的收货地址省份信息获取失败，地区数据");
        }
        if(!isset($source['address']['city']) || empty($source['address']['city'])){
            $this->_error($source['order']['order_id'], "分销订单 ({$source['order']['order_id']}) 的收货地址城市信息获取失败，地区数据");
        }
        if(!isset($source['address']['district']) || empty($source['address']['district'])){
            $this->_error($source['order']['order_id'], "分销订单 ({$source['order']['order_id']}) 的收货地址县/市/区信息获取失败，地区数据");
        }
        $detail = array();
        $detail['order_sn'] = $source['order']['order_id']; // 分销系统订单编号
        $detail['buy_id'] = $this->member_id; // 分销商用户编号
        $detail['receiver'] = $source['address']['nickname']; // 收件人
        $detail['provine'] = $source['address']['province'];
        $detail['city'] = $source['address']['city'];
        $detail['area'] = $source['address']['district'];
        $detail['address'] = $source['address']['address'];
        $detail['mobile'] = $source['address']['phone']; // 手机号码
        $detail['remark'] =!empty($source['order']['comment']) ? $source['order']['comment']:'无';
        $detail['payment_code'] = 'fenxiao';
        $detail['order_time'] = strtotime($source['order']['pay_time']); // 下单时间，时间戳
        $detail['item'] = $items;
        $detail['amount'] = $source['order']['order_pay_price'];
        $detail['platform'] = 'new';
        $detail['shipping_fee']=$source['order']['express_price']; //运费
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
            if ($orderDetail[0]['order']['status']!='2')
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

    function chgLogiCode($name)
    {
        $data = array(
            '佳吉快运' => 'jiaji',
            '国通快递' => 'guotong',
            '中通快递' => 'zhongtong',
            '韵达快递' => 'yunda',
            '百世汇通' => 'huitong',
            '圆通快递' => 'yuantong',
            '申通快递' => 'shentong',
                 'EMS' => 'ems',
            '顺丰快递' => 'shunfeng',
            '优速快递' => 'youshuwuliu',
            '天天快递' => 'tiantian',
            '宅急送'   => 'zhaijisong',
            '快捷速递' => 'kuaijie',
            '全峰快递' => 'quanfengkuaidi',
            '城际快递' => 'chengji',
            '邮政包裹' => 'eyoubao',
            '包裹平邮' => 'eyoubao',
            '共速达' => 'gongsuda',
            '安能物流' => 'annengwuliu',
            '传喜物流'=>'chuanxi',
            'DHL快递'=>'dhl',
            '大田物流'=>'datian',
            '德邦物流'=>'debangwuliu',
            '飞康达'=>'fkd',
            'FedEx(国际)'=>'fedex',
            '港中能达'=>'nengda',
            '共速达'=>'gongsuda',
            '佳吉快运'=>'jiaji',
            '佳怡物流'=>'jiayi',
            '急先达'=>'jixianda',
            '龙邦快递'=>'longbang',
            '联昊通'=>'lianhaotong',
            '全一快递'=>'quanyi',
            '全峰快递'=>'quanfeng',
            '全日通'=>'quanritong',
            '申通快递'=>'shentong',
            '顺丰快递'=>'shunfeng',
            '速尔快递'=>'sure',
            'TNT快递'=>'tnt',
            '天天快递'=>'tiantian',
            '天地华宇'=>'huayu',
            'UPS快递'=>'ups',
            'USPS'=>'usps',
            '新邦物流'=>'xinbang',
            '中铁快运'=>'zhongtie',
            '中邮物流'=>'zhongyou',
            '京东快递'=>'jd',
        ) ;
        return $data[$name] ? $data[$name] : 'ems' ;
    }

    // 订单发货
    function push_ship($params) {
        $params = array(
            'oid' => trim($params['orderno']),
            'express_company' =>$this->chgLogiCode($params['logi_name']),
            'express_no'=>trim($params['logi_no']),
        );
        $res = $this->_sendRequest('api_order_shipping_v2', $params);
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
                'orderSn' => $orderDetail[0]['order']['order_id'],
                'orderStatus' => $orderDetail[0]['order']['status'] == '4' ? 3 : 0,
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