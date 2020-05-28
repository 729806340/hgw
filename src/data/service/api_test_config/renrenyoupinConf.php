<?php
/**
 * Created by CharlesChen
 * Date: 2018/1/25
 * Time: 14:41
 * File name:beibeiwangConf.php
 */
require_once('Common.php');
class renrenyoupinConf extends Common{
    private  $app_id='vip_nongguxian';
    private  $session='829D7D50E794640F3CA27A828CC18748';
    private  $secret ='9E4957A5D954C3935D865DD3A7E54922';
    private  $gateway='http://erpapi.renrenyoupin.com';
    public function getConf(){
        return array(
            'orderlist' => array(
                //用于统一时间控件名
                "startTime"=>'start_time',
                "endTime"=>'end_time',
                'comment' => '',
                //接口请求配置：method代表方法名，curl_type代表请求发送方式:0代表post,1代表get;para_is_json代表参数数组是否需要转换为json格式:0代表不需要，1代表需要
                'request' => array("method"=>"batchQueryOrder","curl_type"=>0,"para_is_json"=>1),
                'form' => array(
                    //type:1为int,2为string；
                    //item_type:1为时间控件,0为普通input框,2为下拉选框;
                    //is_null：1为可选项，0为必选项
                    array('label'=>'开始时间','item_type'=>1,'name'=>'start_time','is_null'=>0,'type'=>2,'comment'=>'(查询开始时间)'),
                    array('label'=>'结束时间','item_type'=>1,'name'=>'end_time','is_null'=>0,'type'=>2,'comment'=>'(查询结束时间)'),
                    array('label'=>'订单状态','item_type'=>0,'name'=>'out_status','is_null'=>0,'type'=>1,'comment'=>'(对外状态:WAIT_SELLER_SEND_GOODS:等待卖家发货 ,WAIT_BUYER_CONFIRM_GOODS :卖家已发货,TRADE_BUYER_SIGNED:买家已签收,TRADE_FINISHED:交易成功,TRADE_CLOSED:取消订单。多个状态可以用英文逗号","拼接。默认值：WAIT_SELLER_SEND_GOODS)'),
                    array('label'=>'订单页码','item_type'=>0,'name'=>'page_no','is_null'=>0,'type'=>1,'comment'=>'(页码，取值范围:大于零的整数。例如：10，默认值：1)'),
                    array('label'=>'单页大小','item_type'=>0,'name'=>'page_size','is_null'=>0,'type'=>1,'comment'=>'(每页记录数，取值范围:1-100的整数，例如:80,默认值：50)'),
                )
            ),
            'getOrderDetail'=>array(
                'comment'=>'',
                //接口请求配置：method代表方法名，curl_type代表请求发送方式:0代表post,1代表get;para_is_json代表参数数组是否需要转换为json格式:0代表不需要，1代表需要
                'request' =>array("method"=>"singleQueryOrder","curl_type"=>0,"para_is_json"=>1),
                'form'=>array(
                    array('label'=>'订单单号','item_type'=>0,'name'=>'flow_id','is_null'=>0,'type'=>2,'comment'=>""),
                )
            ),
            'getSku'=>array(
                'comment'=>'商品sku为在售商品',
                //接口请求配置：method代表方法名，curl_type代表请求发送方式:0代表post,1代表get;para_is_json代表参数数组是否需要转换为json格式:0代表不需要，1代表需要
                'request' => array("method"=>"batchQueryGoods","curl_type"=>0,"para_is_json"=>1),
                'form'=>array(
                    array('label'=>'列表页码','item_type'=>0,'name'=>'page_no','is_null'=>0,'type'=>1,'comment'=>'(列表页码 默认值：1)'),
                    array('label'=>'单页大小','item_type'=>0,'name'=>'page_size','is_null'=>0,'type'=>1,'comment'=>'(每页显示数量 默认值：40，最大值：300 超过300返回用默认40条)'),
                )
            ),
            'bathPickOrder'=>array(
                'comment'=>'批量将订单标记为拣货状态，供应商平台获取订单以后应该将订单标记为拣货状态',
                //接口请求配置：method代表方法名，curl_type代表请求发送方式:0代表post,1代表get;para_is_json代表参数数组是否需要转换为json格式:0代表不需要，1代表需要
                'request' =>array("method"=>"bathPickOrder","curl_type"=>0,"para_is_json"=>1),
                'form'=>array(
                    array('label'=>'订单单号','item_type'=>0,'name'=>'flow_ids','is_null'=>0,'type'=>2,'comment'=>"订单编号集合，使用\",\"进行分隔。例如：201611171645316465,201611171645316467"),
                )
            ),
            'pushShip'=>array(
                'comment'=>'',
                //接口请求配置：method代表方法名，curl_type代表请求发送方式:0代表post,1代表get;para_is_json代表参数数组是否需要转换为json格式:0代表不需要，1代表需要
                'request' => array("method"=>"deliverOrder","curl_type"=>0,"para_is_json"=>1),
                'form'=>array(
                    array('label'=>'订单单号','item_type'=>0,'name'=>'flow_id','is_null'=>0,'type'=>2,'comment'=>""),
                    array('label'=>'物流公司','item_type'=>0,'name'=>'express_code','is_null'=>0,'type'=>2,'comment'=>"(目前支持：德邦物流,申通快递,顺丰快递,EMS,韵达快递,中通快递,圆通快递,天天快递,百世汇通,全峰快递,宅急送,邮政包裹,包裹平邮,优速快递)"),
                    array('label'=>'物流单号','item_type'=>0,'name'=>'express_no','is_null'=>0,'type'=>2,'comment'=>""),
                )
            ),
            //接口下拉框配置
            'selectItem' => array(
                'orderlist' => "批量获取订单",
                'getOrderDetail' => "获取订单详情",
                'getSku' => '获取商品sku',
                'bathPickOrder' => '拣货接口',
                'pushShip' => '订单发货接口'),
        );

    }
    public function dealWithParam($param){
        $after=array('request'=>array('url'=>"",'curl_type'=>$param['curl_type'],'para_is_json'=>$param['para_is_json']));
        foreach ($param as $k=>$v) {
            if (in_array($k, array('curl_type', 'para_is_json', 'channel', 'api'))) {
                unset($param[$k]);
            }
            if ($k == "method" && $v == 'deliverOrder') {
                $param['express_code'] = $this->rrypLogiCode($param['express_code']);
            }
            if ($k == "method") {
                switch ($v) {
                    case"batchQueryOrder":
                        $url = '/orders/batchQueryOrder';
                        break;
                    case"singleQueryOrder":
                        $url = '/orders/singleQueryOrder';
                        break;
                    case"pickOrder":
                        $url = '/orders/pickOrder';
                        break;
                    case"batchQueryGoods":
                        $url = '/goods/batchQueryGoods';
                        break;
                    case"deliverOrder":
                        $url = '/orders/deliverOrder';
                        break;
                }
            }
        }
        $after['request']['url']=$this->gateway.$url;
        $sys_params = array(
            'appid' => $this->app_id,
            'session_key' => $this->session,
            'format'=>'json',
            'v'=>'2.0',
            'timestamp' => date("Y-m-d H:i:s",time())
        );
        $p=array_merge($param,$sys_params);
        $p['sign']=$this->gen_sign($p);
        $after['param']=$p;
        return $after;
    }
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
}