<?php
/**
 * Created by CharlesChen
 * Date: 2018/1/29
 * Time: 15:44
 * File name:grscConf.php
 */
class pinduoduoConf extends Common{
    private $client_id = '2e69fabb07364ccca583cb8db044246c';
    private $client_secret = "ca97e4d04edd61c82dd716955d0481bb4d822aae";
    private $api_url = 'http://gw-api.pinduoduo.com/api/router';
    public $mall_id = 8;
    public  $data_type='JSON';
    public function getConf(){
        return array(
            'orderlist' => array(
                //用于统一时间控件名
                "startTime"=>'start_time',
                "endTime"=>'end_time',
                'comment' => '不支持时间段筛选,暂时只开放待收货订单',
                //接口请求配置：method代表方法名，curl_type代表请求发送方式:0代表post,1代表get;para_is_json代表参数数组是否需要转换为json格式:0代表不需要，1代表需要
                'request' => array("method"=>"pdd.order.number.list.get","curl_type"=>0,"para_is_json"=>0),
                'form' => array(
                    //type:1为int,2为string；
                    //item_type:1为时间控件,0为普通input框,2为下拉选框;
                    //is_null：1为可选项，0为必选项
                    array('label'=>'订单状态','item_type'=>0,'name'=>'order_status','is_null'=>0,'type'=>1,'comment'=>'(订单状态，订单状态默认返回所有,1:待发货,2:已发货代签收,3:已完成,5:全部)'),
                    array('label'=>'订单页码','item_type'=>0,'name'=>'page','is_null'=>0,'type'=>1,'comment'=>'(列表页码 默认值：1)'),
                    array('label'=>'单页大小','item_type'=>0,'name'=>'page_size','is_null'=>0,'type'=>1,'comment'=>'(每页显示数量 默认值：100，最大值：100 )'),
                )
            ),
            'getOrderDetail'=>array(
                'comment'=>'',
                //接口请求配置：method代表方法名，curl_type代表请求发送方式:0代表post,1代表get;para_is_json代表参数数组是否需要转换为json格式:0代表不需要，1代表需要
                'request' =>array("method"=>"pdd.order.information.get","curl_type"=>0,"para_is_json"=>0),
                'form'=>array(
                    array('label'=>'订单单号','item_type'=>0,'name'=>'order_sn','is_null'=>0,'type'=>2,'comment'=>""),
                )
            ),
            'getSku'=>array(
                'comment'=>'商品sku为在售商品',
                //接口请求配置：method代表方法名，curl_type代表请求发送方式:0代表post,1代表get;para_is_json代表参数数组是否需要转换为json格式:0代表不需要，1代表需要
                'request' => array("method"=>"pdd.goods.list.get","curl_type"=>0,"para_is_json"=>0),
                'form'=>array(
                    array('label'=>'列表页码','item_type'=>0,'name'=>'page','is_null'=>0,'type'=>1,'comment'=>'(列表页码 默认值：1)'),
                    array('label'=>'单页大小','item_type'=>0,'name'=>'page_size','is_null'=>0,'type'=>1,'comment'=>'(每页显示数量 默认值：100，最大值：100 )'))
            ),
            'getRefund'=>array(
                //用于统一时间控件名
                "startTime"=>'start_updated_at',
                "endTime"=>'end_updated_at',
                'comment'=>'筛选时间为售后单创建时间,开始时间与结束时间不超过30分钟',
                //接口请求配置：method代表方法名，curl_type代表请求发送方式:0代表post,1代表get;para_is_json代表参数数组是否需要转换为json格式:0代表不需要，1代表需要
                'request' => array("method"=>"pdd.refund.list.increment.get","curl_type"=>0,"para_is_json"=>0),
                'form'=>array(
                    array('label'=>'开始时间','item_type'=>1,'name'=>'start_updated_at','is_null'=>0,'type'=>2,'comment'=>''),
                    array('label'=>'结束时间','item_type'=>1,'name'=>'end_updated_at','is_null'=>0,'type'=>2,'comment'=>''),
                    array('label'=>'订单页码','item_type'=>0,'name'=>'page','is_null'=>0,'type'=>1,'comment'=>'(列表页码 默认值：1)'),
                    array('label'=>'单页大小','item_type'=>0,'name'=>'page_size','is_null'=>0,'type'=>1,'comment'=>'(每页显示数量 默认值：100，最大值：100)'),
                )
            ),
            'pushShip'=>array(
                'comment'=>'',
                //接口请求配置：method代表方法名，curl_type代表请求发送方式:0代表post,1代表get;para_is_json代表参数数组是否需要转换为json格式:0代表不需要，1代表需要
                'request' => array("method"=>"pdd.logistics.online.send","curl_type"=>0,"para_is_json"=>0),
                'form'=>array(
                    array('label'=>'订单单号','item_type'=>0,'name'=>'order_sn','is_null'=>0,'type'=>2,'comment'=>""),
                    array('label'=>'物流公司','item_type'=>0,'name'=>'logistics_id','is_null'=>0,'type'=>2,'comment'=>"(目前支持：安信达,包裹平邮,德邦物流,EMS,EMS国际,凡客如风达,华宇物流,龙邦快递,联邦快递,全一快递,全峰快递,申通快递,顺丰快递,速尔快递,天天快递,天地华宇,USPS,新邦物流,圆通快递,韵达快递,邮政包裹,优速快递,中通快递,中铁快运,宅急送,中邮物流,国通快递,京东快递,百世汇通,安能物流)"),
                    array('label'=>'物流单号','item_type'=>0,'name'=>'tracking_number','is_null'=>0,'type'=>2,'comment'=>""),
                )
            ),
            //接口下拉框配置
            'selectItem' => array(
                'orderlist' => "批量获取订单",
                'getOrderDetail' => "获取订单详情",
                'getSku' => '获取商品sku',
                'getRefund' => '批量获取售后订单',
                'pushShip' => '订单发货接口'),
        );
    }
    public function dealWithParam($param){
        $after=array('request'=>array('url'=>$this->api_url,'curl_type'=>$param['curl_type'],'para_is_json'=>$param['para_is_json']));
        foreach ($param as $k=>$v){
            $k=="method"&&$param['type']=$v;
            $k=="start_updated_at"&&$param['start_updated_at']=strtotime($v);
            $k=="end_updated_at"&&$param['end_updated_at']=strtotime($v);
            if(in_array($k,array('curl_type','para_is_json','channel','api','method'))){
                unset($param[$k]);
            }
            if($k=="method"&&strpos($v,'send')!==false){
                $param['logistics_id']=$this->getShipId( $param['logistics_id']);
            }
            if($k=="method"&&strpos($v,'refund')!==false){
                $param['after_sales_status']=1;
                $param['after_sales_type']=1;
            }
        }
        $config = Model('pddtoken')->where(array('owner_id' => $this->mall_id))->limit(1)->select();
        $param['access_token']=$config[0]['access_token'];
        $param['client_id']=$this->client_id;
        $param['data_type']=$this->data_type;
        $param['timestamp']=time();
        $param['version']='V1';
        $param['sign']=$this->generateSign($param);
        $after['param']=$param;
        return $after;
    }
    public function getShipId($shipname){
        $data=array(
            '安信达'=>'148',
            '包裹平邮'=>'132',
            '德邦物流'=>'131',
            'EMS'=>'118',
            'EMS国际'=>'213',
            '凡客如风达'=>'130',
            '华宇物流'=>'210',
            '龙邦快递'=>'133',
            '联邦快递'=>'135',
            '全一快递'=>'201',
            '全峰快递'=>'116',
            '申通快递'=>'1',
            '顺丰快递'=>'44',
            '速尔快递'=>'155',
            '天天快递'=>'119',
            '天地华宇'=>'210',
            'USPS'=>'186',
            '新邦物流'=>'216',
            '圆通快递'=>'85',
            '韵达快递'=>'121',
            '邮政包裹'=>'132',
            '优速快递'=>'117',
            '中通快递'=>'115',
            '中铁快运'=>'214',
            '宅急送'=>'129',
            '中邮物流'=>'211',
            '国通快递'=>'124',
            '京东快递' => '120',
            '百世汇通' => '3',
            '安能物流' => '208'
        );
        return !empty($data[$shipname]) ? $data[$shipname]:$data["EMS"];
    }
    protected function generateSign($params)
    {
        ksort($params);
        $stringToBeSigned =$this->client_secret;
        foreach ($params as $k => $v)
        {
            if("@" != substr($v, 0, 1))
            {
                $stringToBeSigned .= "$k$v";
            }
        }
        unset($k, $v);
        $stringToBeSigned .=$this->client_secret;
        return strtoupper(md5($stringToBeSigned));
    }
}