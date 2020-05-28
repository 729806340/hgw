<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/6/8 0008
 * Time: 上午 9:06/*接口文档地址https://github.com/gegejia/ggj-open-api/wiki/获取订单
 */
class gegejia1Cron
{
    private  $partner='GGJ_WHYX01';
    private  $key='0b543ac237734e5883a1f94403e1b6f6';
    private  $gateway='http://openapi.gegejia.com:8902/api';
    public static $source = "gegejia1";
    public static $onlineDate = "2017-07-06 12:00:00";//上线的时间

    public function  __construct($getRel = 1)
    {
        $this->timestamp = TIMESTAMP;
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

    private function _sendRequest($params,$url,$authorize = false)
    {
        $endTime=date('Y-m-d H:i:s');
        $json = '{
			"partner":"'.$this->partner.'",
			"timestamp":"'.$endTime.'",
			"params":{
				'.$params.',
		    }
			}';
        $newurl=$this->gateway.$url;
        $md5key=strtoupper(md5($this->key.$json.$this->key));
        $curl = new Curl();
        $curl->setJsonDecoder(function ($response) {
            return json_decode($response, true);
        });
        $curl->setOpt(CURLOPT_HTTPHEADER, array("Content-Type: application/json","sign:".$md5key));
        $curl->post($newurl,$json);
        if ($curl->error) {
            Log::record('格格家 HTTP 请求失败! method:' . $url . ';Error:' . $curl->errorCode . ': ' . $curl->errorMessage);
            throw new Exception('格格家 HTTP 请求失败! method:' . $url . ';Error:' . $curl->errorCode . ': ' . $curl->errorMessage);
        }
        $res = $curl->response;
        if (!$res['success']) {
//            Log::record('格格家返回错误! method:' . $url . '; Info:' . $res['errMsg']);
        }
        return $res;
    }
    //public function  getdeliver(){
    // $res=$this->_sendRequest($params='',$url="/express/names");
    //  return $res;
    // }
    public function chgLogiCode($name)
    {
        $data = array(
            '安信达'=>'安信达',
            '包裹平邮'=>'包裹/平邮',
            /*'CCES'=>'',*/
            '传喜物流'=>'传喜物流',
            'DHL快递'=>'DHL',
            '大田物流'=>'大田物流',
            '德邦物流'=>'德邦物流',
            'EMS'=>'EMS',
            'EMS国际'=>'EMS国际',
            '飞康达'=>'飞康达',
            'FedEx(国际)'=>'FedEx-国际',
            /*'凡客如风达'=>'',*/
            '港中能达'=>'港中能达',
            /*'挂号信'=>'',*/
            '共速达'=>'共速达',
            /*'百世汇通'=>'',*/
            '华宇物流'=>'天地华宇',
            '佳吉快运'=>'佳吉快运',
            '佳怡物流'=>'佳怡物流',
            '急先达'=>'急先达',
            '快捷速递'=>'快捷速递',
            '龙邦快递'=>'龙邦物流',
            '联邦快递'=>'联邦快递',
            '联昊通'=>'联昊通',
            '全一快递'=>'全一快递',
            '全峰快递'=>'全峰快递',
            '全日通'=>'全日通',
            '申通快递'=>'申通快递',
            '顺丰快递'=>'顺丰速运',
            '速尔快递'=>'速尔快递',
            'TNT快递'=>'TNT',
            '天天快递'=>'天天快递',
            '天地华宇'=>'天地华宇',
            'UPS快递'=>'UPS',
            'USPS'=>'USPS',
            '新邦物流'=>'新邦物流',
            '信丰物流'=>'信丰物流',
            '希伊艾斯'=>'希伊艾斯',
            //'新蛋物流'=>'新蛋奥硕',
            '圆通快递'=>'圆通速递',
            '韵达快递'=>'韵达快运',
            '邮政包裹'=>'邮政快递包裹',
            '优速快递'=>'优速物流',
            '中通快递'=>'中通速递',
            '中铁快运'=>'中铁快运',
            '宅急送'=>'宅急送',
            '中邮物流'=>'中邮物流',
            '国通快递'=>'国通快递',
            '京东快递'=>'京东快递',
            '安能物流'=>'安能物流'
        ) ;
        return $data[$name] ? $data[$name] : 'EMS' ;
    }

    /*获取订单*/
    private function _getOrders($start_time = null, $end_time = null, $page = 1, $status='2')
    {
        $params = '"status":'.$status.',"startTime":"'.$start_time.'","endTime":"'.$end_time.'","page":'.$page.',"pageSize":100';
        $res=$this->_sendRequest($params,$url="/order/findOrders");
        if(is_array($res)) {
            if (isset($res['totalCount']) && $res['totalCount'] > $page * 100)  $res['next'] = $page + 1;
            return $res;
        }
        return array('next' => false, 'count' => 0);
    }

    public function  orderlist($params=array()){
        $service = $params['service'] ;
        $start_time = isset($params['begin']) ? strtotime($params['begin']) : TIMESTAMP - 7 * 24 * 3600;
        $start_time = $start_time < strtotime(self::$onlineDate) ? date('Y-m-d H:i:s',strtotime(self::$onlineDate)) : date('Y-m-d H:i:s',$start_time);
        $end_time = isset($params['end']) ? $params['end']:date('Y-m-d H:i:s',TIMESTAMP);
        $p = 1;
        do{
            $res=$this->_getOrders($start_time,$end_time,$p);
            if($res['success']){
                $sns = $service->getSavedidByApiorderno(array_column($res['orders'], 'number'));
                $items=array();
                foreach($res['orders'] as $order){
                    if(in_array($order['number'],$sns)){
                        continue;
                    }
                    if($item=$this->_prepareOrder($order)) $items[]=$item;
                }
                if(!empty($items)) $createRes=$service->docreateOrder($items);
            }
        }while($p=$res['next']);
    }

    private function _prepareOrder($source)
    {
        // TODO 检查订单是否存在，若存在直接返回false，否则准备数据，并返回数据
        $goodsList = $source['items'];
        $items = array();
        $hasError = false;

        /* 处理异常商品 */
        $specialItem = array();
        foreach ($goodsList as $k => $goods){
            if (in_array($goods['itemCode'],array('1000101'))){
                if(!isset($specialItem[$goods['itemCode']])){
                    $specialItem[$goods['itemCode']]= $goods;
                }else{
                    /** 售价以凭据售价为准 */
                    $specialItem[$goods['itemCode']]['salesPrice'] +=($goods['salesPrice']*$goods['itemCount']+$specialItem[$goods['itemCode']]['itemCount']*$specialItem[$goods['itemCode']]['salesPrice'])/($goods['itemCount']+$specialItem[$goods['itemCode']]['itemCount']);
                    $specialItem[$goods['itemCode']]['itemCount'] +=$goods['itemCount'];
                }
                unset($goodsList[$k]);
            }
        }
        $goodsList = array_merge($goodsList,array_values($specialItem));
        /* 处理异常商品结束 */

        foreach ($goodsList as $goods) {
            if (isset($this->rel[$goods['itemCode']]) && $this->rel[$goods['itemCode']]) {
                $goods_id = $this->rel[$goods['itemCode']];
            } else {
                $this->_error($source['number'], "分销商品 {$goods['itemName']} 没有映射");
                $hasError = true;
                continue;
            }
            $items[] = array(
                'goods_id' => $goods_id,
                'name' => $goods['itemName'],
                'num' => $goods['itemCount'],
                'price' => $goods['salesPrice'],
                'fxpid' => $goods['itemCode'],
                'oid' => isset($goods['itemCode']) ? $goods['itemCode'] : $source['number']
            );
        }
        if (empty($items) || $hasError)
            return false;
        if(!isset($source['receiver']['provinceName']) || empty($source['receiver']['provinceName'])){
            $this->_error($source['number'], "分销订单 ({$source['number']}) 的收货地址省份信息获取失败，地区数据");
        }
        if(!isset($source['receiver']['cityName']) || empty($source['receiver']['cityName'])){
            $this->_error($source['number'], "分销订单 ({$source['number']}) 的收货地址城市信息获取失败，地区数据");
        }
        if(!isset($source['receiver']['districtName']) || empty($source['receiver']['districtName'])){
            $this->_error($source['number'], "分销订单 ({$source['number']}) 的收货地址县/市/区信息获取失败，地区数据");
        }
        $detail = array();
        $detail['order_sn'] = $source['number']; // 分销系统订单编号
        $detail['buy_id'] = $this->member_id; // 分销商用户编号
        $detail['receiver'] = $source['receiver']['receiverName']; // 收件人
        $detail['provine'] = $source['receiver']['provinceName'];
        $detail['city'] = $source['receiver']['cityName'];
        $detail['area'] = $source['receiver']['districtName'];
        $detail['address'] = $source['receiver']['detailAddress'];
        $detail['mobile'] = $source['receiver']['receiverMobile']; // 手机号码
        $detail['remark'] = $source['remark'];
        $detail['payment_code'] = 'fenxiao';
        $detail['order_time'] = strtotime($source['payTime']); // 下单时间，时间戳
        $detail['item'] = $items;
        $detail['amount'] = $source['realPrice'];
        $detail['platform'] = 'new';
        $detail['distribution_channel']=$this->getOrderType($source['type']);
        $detail['shipping_fee']=$source['freight'];//运费
        return $detail;
    }

    function getOrderType($type){
        switch($type){
            case 0:
                $name="渠道订单";
                break;
            case 1:
                $name="格格家订单,联系订单：4001603602";
                break;
            case 2:
                $name="格格团订单,联系订单：4001603602";
                break;
            case 3:
                $name="格格团全球购订单,联系订单：4001603602";
                break;
            case 4:
                $name="环球捕手订单,联系电话:4007667517";
                break;
            case 5:
                $name="燕网订单";
                break;
            case 6:
                $name="b2b订单";
                break;
            case 7:
                $name="手q";
                break;
            case 8:
                $name="脉宝云店,联系电话:4001116789";
                break;
            default:
                $name="";
        }
        return $name;
    }

    function checkUnshipOrder()
    {
        $updateTime = time() - 3600*3  ;
        if( date('G') < 3 ) {
            $updateTime = time() - 24*3600*3  ;
        }
        $comm_where = array();
        $comm_where['shipping_time'] = array('gt', $updateTime) ;
        $comm_where['shipping_express_id'] = array('gt', 0) ;
        $result = Model('order_common') -> where ($comm_where) -> select () ;
        if( !$result ) die('no result') ;
        $oids = array_column($result, 'order_id') ;
        $oid_expressid_rels = array_column($result, 'shipping_express_id', 'order_id');
        //属于本渠道的订单
        $where = array() ;
        $where['order_id'] = array('in', $oids) ;
        $where['buyer_id'] = $this->member_id ;
        $orders = TModel('orders')->where($where)->select() ;
        if( !$orders ) die('no orders') ;
        //本渠道未发货列表
        $beginTime = $this->timestamp - 86400*30;    //30天内未发货监测
        $endTime = $this->timestamp ;
        $order_list = $this->_order_list($beginTime, $endTime) ;
        $order_nos = array();
        foreach ($order_list as $k => $v) {
            $order_nos[] = $v['number'];
        }
        unset($order_list);
        $express = rkcache('express', true) ;
        foreach ($orders as $order) {
            if( !in_array($order['fx_order_id'], $order_nos) ) continue ;
            $express_id = $oid_expressid_rels[ $order['order_id'] ] ;
            $data = array();
            $data['orderno'] = $order['fx_order_id'];
            $data['logi_no'] = $order['shipping_code'];
            $data['logi_name'] = $express[$express_id]['e_name'];
            if( $data['logi_no'] ) {
                $this -> push_ship($data) ;
            }
        }
    }

    //未发货列表
    function _order_list($beginTime, $endTime)
    {
        $page_no = 1;
        $flag = true ;
        $list = array();
        while( $flag )
        {
            $res = $this->getOrderList(date('Y-m-d H:i:s',$beginTime),date('Y-m-d H:i:s',$endTime),$page_no) ;
            if( empty($res[1]) ) die ;
            list( $total_page, $order_list ) = $res ;
            foreach ($order_list as $order) {
                $list[] = $order ;
            }
            if( $total_page == $page_no ) $flag = false ;
            $page_no++;
        }
        return $list ;
    }

    // 获取订单列表
    function getOrderList($start_time,$end_time,$page) {
        $params = '"status":2,"startTime":"'.$start_time.'","endTime":"'.$end_time.'","page":'.$page.',"pageSize":100';
        $result=$this->_sendRequest($params,$url="/order/findOrders");
        $order_data = array();
        $total_page = 1;
        if ($result['success'] && $result['maxPage']!='0') {
            foreach ($result['orders'] as $k => $detail) {
                // 不处理上线日期之前的订单
                if (strtotime($detail['payTime']) < strtotime($this->onlineDate)) {
                    continue;
                }
                if ($detail['status'] != 2) {
                    continue;
                }
                $order_data[] = $detail;
            }
            $total_page = $result['maxPage'];
        }
        return array(
            $total_page,
            $order_data
        );
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

    /*发货接口*/
    public function push_ship($params = array())
    {
        $params = '"type":1,"orderNumber":"'.$params['orderno'].'","expressName":"'.$this->chgLogiCode(trim($params['logi_name'])).'","expressNo":"'.trim($params['logi_no']).'"';
        $res=$this->_sendRequest($params,$url="/order/sendOrder");
        if ($res['success']) {
            $res = json_encode(array(
                'succ' => '1',
                'msg' => '发货成功'
            ));
        }else {
            $res = json_encode(array(
                'succ' => '0',
                'msg' => $res['errMsg'].':'.$res['detail']
            ));
        }
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