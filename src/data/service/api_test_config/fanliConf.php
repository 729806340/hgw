<?php
/**
 * Created by CharlesChen
 * Date: 2018/1/29
 * Time: 10:13
 * File name:fanliConf.php
 */
class fanliConf extends Common{
    static $router = "http://open.shzyfl.cn/";
    static $appkey = "Q0VFQkE1OEU1Q0FEMDM3MQ==";
    static $secret = "55f9e420cbae16c8dc2c38fdf1995244";
    static $grantkey = "YTU1MDc1MmVkODIxNDBjMzBkZDc2NDU1NmRhNDQ5ODg=";
    static $grantsecret = "9ac8711959a5dee4d41359b14591edc3";
    //沙箱地址
//static $router = "http://sandbox.open.shzyfl.cn:8080/";
//static $appkey = "OUFFQzFGMUE0NDlCNkUyRg==";
//static $secret = "13d004efc54605508909c27f10f1a01b";
//static $grantkey = "NzBjM2MyZWM0NTlmODAzMTUxMWJhYmExNjA2N2M1MTY=";
//static $grantsecret = "4510ee557900772f3e65b41008279292";
    public function getConf(){
        return array(
            'orderlist' => array(
                //用于统一时间控件名
                "startTime"=>'startCreated',
                "endTime"=>'endCreated',
                'comment' => '可获取三个月内的订单，订单根据创建时间倒叙返回',
                //接口请求配置：method代表方法名，curl_type代表请求发送方式:0代表post,1代表get;para_is_json代表参数数组是否需要转换为json格式:0代表不需要，1代表需要
                'request' => array("method"=>"api/1/order/trade/getlist","curl_type"=>1,"para_is_json"=>0),
                'form' => array(
                    //type:1为int,2为string；
                    //item_type:1为时间控件,0为普通input框,2为下拉选框;
                    //is_null：1为可选项，0为必选项
                    array('label'=>'开始时间','item_type'=>1,'name'=>'startCreated','is_null'=>0,'type'=>2,'comment'=>''),
                    array('label'=>'结束时间','item_type'=>1,'name'=>'endCreated','is_null'=>0,'type'=>2,'comment'=>''),
                    array('label'=>'订单页码','item_type'=>0,'name'=>'page','is_null'=>1,'type'=>1,'comment'=>'(列表页码 默认值：1)'),
                    array('label'=>'单页大小','item_type'=>0,'name'=>'count','is_null'=>1,'type'=>1,'comment'=>'(每页显示数量 默认值：50)'),
                    array('label'=>'订单状态','item_type'=>0,'name'=>'orderStatus','is_null'=>1,'type'=>1,'comment'=>'(订单状态，订单状态默认(不填)返回所有,1:已付款,2:已发货,3:已签收)'),
                )
            ),
            'getOrderDetail'=>array(
                'comment'=>'',
                //接口请求配置：method代表方法名，curl_type代表请求发送方式:0代表post,1代表get;para_is_json代表参数数组是否需要转换为json格式:0代表不需要，1代表需要
                'request' =>array("method"=>"api/1/order/trade/getone","curl_type"=>1,"para_is_json"=>0),
                'form'=>array(
                    array('label'=>'订单单号','item_type'=>0,'name'=>'orderCode','is_null'=>0,'type'=>2,'comment'=>""),
                    array('label'=>'开始时间','item_type'=>1,'name'=>'startCreated','is_null'=>0,'type'=>2,'comment'=>'(订单创建时间)'),
                    array('label'=>'结束时间','item_type'=>1,'name'=>'endCreated','is_null'=>0,'type'=>2,'comment'=>'(订单结束时间)'),
                )
            ),
            'getSku'=>array(
                'comment'=>'商品sku为在售商品',
                //接口请求配置：method代表方法名，curl_type代表请求发送方式:0代表post,1代表get;para_is_json代表参数数组是否需要转换为json格式:0代表不需要，1代表需要
                'request' => array("method"=>"api/1/product/search/list","curl_type"=>1,"para_is_json"=>0),
                'form'=>array(
                    array('label'=>'列表页码','item_type'=>0,'name'=>'page','is_null'=>0,'type'=>1,'comment'=>'(列表页码 默认值：1)'),
                    array('label'=>'单页大小','item_type'=>0,'name'=>'count','is_null'=>0,'type'=>1,'comment'=>'(每页显示数量 默认值：50)'),
                )
            ),
            'getRefund'=>array(
                //用于统一时间控件名
                "startTime"=>'startCreated',
                "endTime"=>'endCreated',
                'comment'=>'筛选时间为售后单创建时间',
                //接口请求配置：method代表方法名，curl_type代表请求发送方式:0代表post,1代表get;para_is_json代表参数数组是否需要转换为json格式:0代表不需要，1代表需要
                'request' => array("method"=>"api/1/order/refund/getlist","curl_type"=>1,"para_is_json"=>0),
                'form'=>array(
                    array('label'=>'开始时间','item_type'=>1,'name'=>'startCreated','is_null'=>0,'type'=>2,'comment'=>''),
                    array('label'=>'结束时间','item_type'=>1,'name'=>'endCreated','is_null'=>0,'type'=>2,'comment'=>''),
                    array('label'=>'订单页码','item_type'=>0,'name'=>'page','is_null'=>1,'type'=>1,'comment'=>'(列表页码 默认值：1)'),
                    array('label'=>'单页大小','item_type'=>0,'name'=>'count','is_null'=>1,'type'=>1,'comment'=>'(每页显示数量 默认值：50)'),
                )
            ),
            'getRefundDetail'=>array(
                'comment'=>'',
                //接口请求配置：method代表方法名，curl_type代表请求发送方式:0代表post,1代表get;para_is_json代表参数数组是否需要转换为json格式:0代表不需要，1代表需要
                'request' => array("method"=>"api/1/order/refund/get","curl_type"=>1,"para_is_json"=>0),
                'form'=>array(
                    array('label'=>'售后单号','item_type'=>0,'name'=>'exOrderCode','is_null'=>0,'type'=>2,'comment'=>""),
                    array('label'=>'订单编号','item_type'=>0,'name'=>'orderCode','is_null'=>0,'type'=>2,'comment'=>""),
                    array('label'=>'开始时间','item_type'=>1,'name'=>'startCreated','is_null'=>0,'type'=>2,'comment'=>''),
                    array('label'=>'结束时间','item_type'=>1,'name'=>'endCreated','is_null'=>0,'type'=>2,'comment'=>''),
                )
            ),
            'pushShip'=>array(
                'comment'=>'',
                //接口请求配置：method代表方法名，curl_type代表请求发送方式:0代表post,1代表get;para_is_json代表参数数组是否需要转换为json格式:0代表不需要，1代表需要
                'request' => array("method"=>"api/1/order/delivery/add","curl_type"=>0,"para_is_json"=>0),
                'form'=>array(
                    array('label'=>'订单单号','item_type'=>0,'name'=>'orderCode','is_null'=>0,'type'=>2,'comment'=>""),
                    array('label'=>'物流公司','item_type'=>0,'name'=>'expressCompany','is_null'=>0,'type'=>2,'comment'=>"(目前支持：中通快递,韵达快递,百世汇通,圆通快递,EMS,优速快递,天天快递,德邦物流,国通快递,安能物流)"),
                    array('label'=>'物流单号','item_type'=>0,'name'=>'expressNo','is_null'=>0,'type'=>2,'comment'=>""),
                )
            ),
            //接口下拉框配置
            'selectItem' => array(
                'orderlist' => "批量获取订单",
                'getOrderDetail' => "获取订单详情",
                'getSku' => '获取商品sku',
                'getRefund' => '批量获取售后订单',
                'getRefundDetail' => '获取售后订单详情',
                'pushShip' => '订单发货接口'),
        );
    }
    public function dealWithParam($param){
        $after=array('request'=>array('url'=>self::$router.$param['method'],'curl_type'=>$param['curl_type'],'para_is_json'=>$param['para_is_json']));
        foreach ($param as $k=>$v){
            if(in_array($k,array('curl_type','para_is_json','channel','api','method'))){
                unset($param[$k]);
            }
            if($k=="method"&&strpos($v,'delivery')!==false){
                $param['deliveryItem']['orderCode'] = $param['orderCode'];//订单号
                unset($param['orderCode']);
                $param['deliveryItem']['expressNo'] = $param['expressNo'];//物流单号
                unset($param['expressNo']);
                $param['deliveryItem']['expressCompany'] = $param['expressCompany'];//物流公司
                unset($param['expressCompany']);
                $param['deliveryItem']['expressCode']=$this->chgLogiCode( $param['expressCompany']);
            }
        }
        $param=json_encode($param);
        $time=time();
        $data['timestamp'] =$time;
        $data['appKey'] = self::$appkey ;
        $data['userKey'] = self::$grantkey;
        $data['params'] = $param;
        $data['sign'] = md5('appKey'.self::$appkey.'params'.$param.'timestamp'.$time.'userKey'.self::$grantkey.self::$secret.self::$grantsecret);
        $after['param']=$data;
        return $after;
    }
    function chgLogiCode( $name )
    {
        $data = array(
            '中通快递' => '10007',
            '韵达快递' => '10011',
            '百世汇通' => '10005',
            '圆通快递' => '10003',
            'EMS' 	 => '10017',
            '优速快递' => '10019',
            '天天快递' => '10027',
            '优速快递' => '10019',
            '德邦物流' => '10205',
            '国通快递' => '10147',
            '安能物流' => '10289',
        ) ;
        return $data[$name] ? $data[$name] : '10017' ;
    }
}