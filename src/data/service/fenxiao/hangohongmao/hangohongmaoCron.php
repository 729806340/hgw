<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/6/14 0014
 * Time: 下午 2:01
 * 汉购红猫
 */
class hangohongmaoCron
{
    private $gateway="http://hm.hansap.com/web/api.php?uniacid=1";
    public  static $onlineDate="2018-1-5 17:00:00";
    public static $source="hangohongmao";

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

    public function getOrderDetail($orderSn){
        $params=array(
            'op'=>'detail',
            'ordersn'=>$orderSn
        );
        $res=$this->_sendRequest($params);
        return  $res['status'] ? $res['data']:false;
    }

    public function  _sendRequest($params){
        $str="";
        foreach($params as $key=>$item){
            $str.="&".$key."=" .$item;
        }
        $url=$this->gateway.$str;
        $curl=new Curl();
        $curl->setJsonDecoder(function($response){
            return json_decode($response,true);
        });
        $curl->post($url);
        if($curl->error){
            Log::record('汉购红锚 HTTP 请求失败! method:' . $url . ';Error:' . $curl->errorCode . ': ' . $curl->errorMessage);
            throw new Exception('汉购红猫 HTTP 请求失败! method:' . $url . ';Error:' . $curl->errorCode . ': ' . $curl->errorMessage);
        }
        $res=$curl->response;
        if(!$res['status']){
            Log::record('汉购红锚返回错误! method:' . $url . '; Info:' . $res['message']);
        }
        return $res;
    }

    public function _getOrders($start_time=null,$end_time=null,$page){
        $params=array(
            'op'=>'orderlist',
            'page_no'=>$page,
            'page_size'=>50,
            'status'=>1,
            'searchtime'=>'paytime',
            'starttime'=>$start_time,
            'endtime'=>$end_time,
        );
        $res=$this->_sendRequest($params);
        if(isset($res['data']['orderlist']) && count($res['data']['orderlist'])>0){
            if(intval($res['data']['count'])>= $page * 50)  $res['next']=$page+1;
            return $res;
        }
        return array('next' => false, 'count' => 0);
    }

    function _prepareOrder($source){
        $goodsList=$source['items'];
        $items=array();
        $hasError=false;
        foreach($goodsList as $goods){
            $get_goodsid=$goods['optionid']=="0" ? $goods['goodsid']:$goods['optionid'];/*optionid:规格id,判读如果商品没有规格就使用goods_id为分销对应的id*/
            if(isset($this->rel[$get_goodsid])&&$this->rel[$get_goodsid]){
                $goods_id=$this->rel[$get_goodsid];
            }
            else{
                $this->_error($source['ordersn'],"分销{$goods['title']}没有映射");
                $hasError=true;
                continue;
            }
            $items[]=array(
                'goods_id'=>$goods_id,
                'name'=>$goods['title'],
                'num'=>$goods['total'],
                'price'=>$goods['price'],
                'fxpid' => $get_goodsid,
                'oid'=>isset($get_goodsid) ? $get_goodsid:$source['ordersn']
            );
        }
        if(empty($items) || $hasError)  return false;
        if(!isset($source['address']['province']) || empty($source['address']['province'])){
            $this->_error($source['ordersn'], "分销订单 ({$source['ordersn']}) 的收货地址省份信息获取失败，地区数据");
        }
        if(!isset($source['address']['city']) || empty($source['address']['city'])){
            $this->_error($source['ordersn'], "分销订单 ({$source['ordersn']}) 的收货地址城市信息获取失败，地区数据");
        }
        if(!isset($source['address']['area']) || empty($source['address']['area'])){
            $this->_error($source['ordersn'], "分销订单 ({$source['ordersn']}) 的收货地址县/市/区信息获取失败，地区数据");
        }
        $detail = array();
        $detail['order_sn'] = $source['ordersn']; // 分销系统订单编号
        $detail['buy_id'] = $this->member_id; // 分销商用户编号
        $detail['receiver'] = $source['address']['realname']; // 收件人
        $detail['provine'] = $source['address']['province'];
        $detail['city'] = $source['address']['city'];
        $detail['area'] = $source['address']['area'];
        $detail['address'] = $source['address']['address'];
        $detail['mobile'] = $source['address']['mobile']; // 手机号码
        $detail['remark'] = $source['remark'];
        $detail['payment_code'] = 'fenxiao';
        $detail['order_time'] = $source['paytime']; // 下单时间，时间戳
        $detail['amount'] = $source['price'];
        $detail['discount']=$source['discountprice'];
        $detail['platform'] = 'new';
        $detail['item'] = $items;
        $detail['shipping_fee']=$source['dispatchcprice'];//运费
        return $detail;
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
        if( !$orders ) die('no orders');
        foreach ($orders as $order) {
            $fx_order_id = $order['fx_order_id'];
            $goodsWhere = array();
            $goodsWhere['order_id'] = $order['order_id'];
            $order_items = TModel('order_goods')->where($goodsWhere)->select();
            if (!$order_items) continue;
            $orderDetail = $this->getOrderDetail($fx_order_id);
            if ($orderDetail['status']!="1") continue;
            $express = rkcache('express', true);
            /** 判断当前商品是否需要重新发货 */
            $express_id = $oid_expressid_rels[$order['order_id']];
            $data = array();
            $data['orderno'] = $fx_order_id;
            $data['logi_no'] = $order['shipping_code'];
            $data['logi_name'] = $express[$express_id]['e_name'];
            if( $data['logi_no'] ) {
                $this -> push_ship($data) ;
            }
        }
    }

    public function push_ship($params=array()){
        $logi_name=explode(',',$this->chgLogiCode($params['logi_name']));
        $sys_params=array(
            'op'=>'delivery',
            'ordersn'=>$params['orderno'],
            'expresscom'=>$logi_name[0],
            'expresssn'=>$params['logi_no'],
            'express'=>$logi_name[1],
            'sendtime'=>time()
        );
        $res=$this->_sendRequest($sys_params);
        if($res['status']){
            $res = json_encode(array(
                'succ' => '1',
                'msg' => '发货成功'
            ));
        }else {
            $res = json_encode(array(
                'succ' => '0',
                'msg' => $res['message']
            ));
        }
        return $res;
    }

    public function chgLogiCode($name)
    {
        $data = array(
            '安信达'=>'安信达快递,anxindakuaixi',
            'DHL快递'=>'dhl,dhl',
            '大田物流'=>'大田物流,datianwuliu',
            '德邦物流'=>'德邦物流,debangwuliu',
            'EMS'=>'ems快递,ems',
            '飞康达'=>'飞康达物流,feikangda',
            'FedEx(国际)'=>'fedex（国外),fedex',
            '港中能达'=>'港中能达物流,ganzhongnengda',
            '挂号信'=>'邮政包裹挂号信,youzhengguonei',
            '共速达'=>'共速达,gongsuda',
            '佳吉快运'=>'佳吉物流,jjwl',
            '佳怡物流'=>'佳怡物流,jiayiwuliu',
            '急先达'=>'急先达,jixianda',
            '快捷速递'=>'快捷速递,kuaijiesudi',
            '龙邦快递'=>'龙邦物流,longbanwuliu',
            '联邦快递'=>'联邦快递（国内),lianb',
            '联昊通'=>'联昊通物流,lianhaowuliu',
            '全一快递'=>'全一快递,quanyikuaidi',
            '全峰快递'=>'全峰快递,quanfengkuaidi',
            '全日通'=>'全日通快递,quanritongkuaidi',
            '申通快递'=>'申通,shentong',
            '顺丰快递'=>'顺丰,shunfeng',
            '速尔快递'=>'速尔物流,sue',
            'TNT快递'=>'tnt,tnt',
            '天天快递'=>'天天快递,tiantian',
            '天地华宇'=>'天地华宇,tiandihuayu',
            'UPS快递'=>'ups,ups',
            '新邦物流'=>'新邦物流,xinbangwuliu',
            '信丰物流'=>'信丰物流,xinfengwuliu',
            '圆通快递'=>'圆通速递,yuantong',
            '韵达快递'=>'韵达快运,yunda',
            '邮政包裹'=>'邮政包裹挂号信,youzhengguonei',
            '优速快递'=>'优速物流,youshuwuliu',
            '中通快递'=>'中通速递,zhongtong',
            '中铁快运'=>'中铁快运,zhongtiekuaiyun',
            '宅急送'=>'宅急送,zhaijisong',
            '中邮物流'=>'中邮物流,zhongyouwuliu',
            '国通快递'=>'国通快递,guotongkuaidi',
            '安能物流'=>'安能物流,annengwuliu'
        ) ;
        return $data[$name] ? $data[$name] : 'ems快递,ems' ;
    }

    //获取商品sku列表
    public function getSkuList($params = array()){
        $param = array(
            'op'=>'goods',
        );
        $res = $this->_sendRequest($param);
        $data_out = array();
        if(!$res['status']) return  $data_out;
        foreach($res['data'] as $k => $v){
            if(count($v['options'])>0){
                foreach($v['options'] as $option){
                    $data['goods_name']=$v['title']."【".$option['title']."】";
                    $data['source'] = self::$source;
                    $data['sku_id'] = $option['id'];
                    $data_out[] = $data;
                }
             }else {
                $title = $v['title'];
                $item['goods_name'] = $title;
                $item['source'] = self::$source;
                $item['sku_id'] = $v['id'];
                $data_out[] = $item;
            }
        }
        return $data_out;
    }

    public function orderlist($params=array()){
        $service = $params['service'] ;
        $start_time = isset($params['begin']) ? strtotime($params['begin']):time() - 7 * 24 * 3600;
        $start_time = $start_time < strtotime(self::$onlineDate) ? strtotime(self::$onlineDate):$start_time;
        $end_time =isset($params['end']) ? strtotime($params['end']):time();
        $page=1;
        do{
            $res=$this->_getOrders($start_time,$end_time,$page);
            if($res['status']){
               $sns = $service->getSavedidByApiorderno(array_column($res['data']['orderlist'], 'ordersn'));
                $items=array();
                foreach($res['data']['orderlist'] as $order){
                    if(in_array($order['ordersn'],$sns)){
                        continue;
                    }
                    if($item=$this->_prepareOrder($order)) $items[]=$item;
                }
                if(!empty($items))  $createRes=$service->docreateOrder($items);
            }
        }while($page=$res['next']);
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
                'orderSn' => $orderDetail['ordersn'],
                'orderStatus' => $orderDetail['status'] == 3 ? 3 : 0,
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
            'log_time' => time(),
            'sourceid' => $this->member_id,
            'source' => self::$source,
            'log_type' => $log_type
        );
        $model->insert($data);
    }
}