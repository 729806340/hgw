<?php
/**
 * Created by CharlesChen
 * Date: 2018/1/25
 * Time: 14:41
 * File name:beibeiwangConf.php
 */
require_once('Common.php');
class mengdianConf extends Common{
    static $appid = "09528567279dc36121547006f2242070" ;
    static $secret = "235c5c590af1b537a809808fa556b6f1" ;
    private $grantUrl = "https://open.mengdian.com/common/token?grant_type=client_credential";//获取token的URL
    private $router = 'https://open.mengdian.com/api/mname/WE_MALL/cname/' ;
    public function getConf(){
        return array(
            'orderlist' => array(
                //用于统一时间控件名
                "startTime"=>'create_begin_time',
                "endTime"=>'create_end_time',
                'comment' => '查询的时间跨度不能超过24小时',
                //接口请求配置：method代表方法名，curl_type代表请求发送方式:0代表post,1代表get;para_is_json代表参数数组是否需要转换为json格式:0代表不需要，1代表需要
                'request' => array("method"=>"orderGetHighly","curl_type"=>0,"para_is_json"=>1),
                'form' => array(
                    //type:1为int,2为string；
                    //item_type:1为时间控件,0为普通input框,2为下拉选框;
                    //is_null：1为可选项，0为必选项
                    array('label'=>'开始时间','item_type'=>1,'name'=>'create_begin_time','is_null'=>0,'type'=>2,'comment'=>''),
                    array('label'=>'结束时间','item_type'=>1,'name'=>'create_end_time','is_null'=>0,'type'=>2,'comment'=>''),
                    array('label'=>'订单状态','item_type'=>0,'name'=>'order_status','is_null'=>0,'type'=>1,'comment'=>'(订单状态:1交易中,2交易成功,3交易关闭，null代表所有)'),
                    array('label'=>'订单页码','item_type'=>0,'name'=>'page_no','is_null'=>0,'type'=>1,'comment'=>'(列表页码 默认值：1)'),
                    array('label'=>'单页大小','item_type'=>0,'name'=>'page_size','is_null'=>0,'type'=>1,'comment'=>'(每页显示数量 1-200'),
                )
            ),
            'getOrderDetail'=>array(
                'comment'=>'',
                //接口请求配置：method代表方法名，curl_type代表请求发送方式:0代表post,1代表get;para_is_json代表参数数组是否需要转换为json格式:0代表不需要，1代表需要
                'request' =>array("method"=>"orderFullInfoGetHighly","curl_type"=>0,"para_is_json"=>1),
                'form'=>array(
                    array('label'=>'订单单号','item_type'=>0,'name'=>'order_no','is_null'=>0,'type'=>2,'comment'=>""),
                )
            ),
            'getSku'=>array(
                'comment'=>'商品sku',
                //接口请求配置：method代表方法名，curl_type代表请求发送方式:0代表post,1代表get;para_is_json代表参数数组是否需要转换为json格式:0代表不需要，1代表需要
                'request' => array("method"=>"spuFullInfoGet","curl_type"=>0,"para_is_json"=>1),
                'form'=>array(
                    array('label'=>'列表页码','item_type'=>0,'name'=>'page_no','is_null'=>0,'type'=>1,'comment'=>''),
                    array('label'=>'单页大小','item_type'=>0,'name'=>'page_size','is_null'=>1,'type'=>1,'comment'=>'(页码范围：1-200)'),
                )
            ),
            'getRefund'=>array(
                //用于统一时间控件名
                "startTime"=>'update_begin_time',
                "endTime"=>'update_end_time',
                'comment'=>'筛选时间为售后单创建时间',
                //接口请求配置：method代表方法名，curl_type代表请求发送方式:0代表post,1代表get;para_is_json代表参数数组是否需要转换为json格式:0代表不需要，1代表需要
                'request' => array("method"=>"returnorderGetPaging","curl_type"=>0,"para_is_json"=>1),
                'form'=>array(
                    array('label'=>'开始时间','item_type'=>1,'name'=>'update_begin_time','is_null'=>1,'type'=>2,'comment'=>''),
                    array('label'=>'结束时间','item_type'=>1,'name'=>'update_end_time','is_null'=>1,'type'=>2,'comment'=>''),
                    array('label'=>'订单状态','item_type'=>0,'name'=>'return_order_status','is_null'=>1,'type'=>1,'comment'=>'(维权状态(0全部，1买家发起退款申请，2买家发起退款退货申请，3商家同意退款申请，4等待买家退货，5专家已收货，7已退款退货，8已拒绝，9已取消，10微盟支付处理中))'),
                    array('label'=>'订单页码','item_type'=>0,'name'=>'page_no','is_null'=>0,'type'=>1,'comment'=>'(列表页码 默认值：1)'),
                    array('label'=>'单页大小','item_type'=>0,'name'=>'page_size','is_null'=>0,'type'=>1,'comment'=>'(每页显示数量 1-50)'),
                )
            ),
            'pushShip'=>array(
                'comment'=>'',
                //接口请求配置：method代表方法名，curl_type代表请求发送方式:0代表post,1代表get;para_is_json代表参数数组是否需要转换为json格式:0代表不需要，1代表需要
                'request' => array("method"=>"logisticsDelivery","curl_type"=>0,"para_is_json"=>1),
                'form'=>array(
                    array('label'=>'订单单号','item_type'=>0,'name'=>'order_no','is_null'=>0,'type'=>2,'comment'=>""),
                    array('label'=>'物流公司','item_type'=>0,'name'=>'carrier_name','is_null'=>0,'type'=>2,'comment'=>"(目前支持：中通快递,韵达快递,百世汇通,圆通快递,申通快递,EMS,顺丰快递,优速快递,天天快递,宅急送,快捷速递,全峰快递,安能物流)"),
                    array('label'=>'物流单号','item_type'=>0,'name'=>'express_no','is_null'=>0,'type'=>2,'comment'=>""),
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
        $after=array('request'=>array('url'=>$this->router . $param['method']. "?accesstoken=" . $this->getToken(),'curl_type'=>$param['curl_type'],'para_is_json'=>$param['para_is_json']));
        foreach ($param as $k=>$v){
            if(in_array($k,array('curl_type','para_is_json','channel','api','method'))){
                unset($param[$k]);
            }
            if($k=="method"&&$v=='logisticsDelivery'){
                $deliveries = array();
                $deliveries['order_no'] = $param['order_no'] ;
                unset($param['order_no']);
                $deliveries['need_delivery'] = true ;
                $deliveries['carrier_code'] = $this->chgLogiCode( $param['carrier_name']) ;
                $deliveries['carrier_name'] = $param['carrier_name'] ;
                unset($param['carrier_name']);
                $deliveries['express_no'] = $param['express_no'] ;
                unset($param['express_no']);
                $deliveries['remark'] = null ;
                $deliveries['sender_address'] = "湖北 武汉 江汉区 江汉经济开发区江旺路6号";
                $deliveries['sender_name'] = "汉购网";
                $deliveries['sender_tel'] = "13800138000";
                $param['deliveries'][] = $deliveries ;
            }
            if($k=="method"&&$v=='orderGetHighly'){
                $param['pay_status'] = 1; //订单支付状态(0待支付，1已支付，空值代表所有)
                $param['delivery_status'] = 0; //物流状态(0待发货，1卖家发货,2买家收货，空值代表所有)
            }
        }
        $after['param']=$param;
        return $after;
    }
    function getToken()
    {
        $data['appid'] = self::$appid;
        $data['secret'] = self::$secret;
        $url = $this->grantUrl."&".http_build_query( $data ) ;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        $output = curl_exec($ch);
        curl_close($ch);
        $res=json_decode($output,true);
        return  $res['data']['access_token'];
    }
    function chgLogiCode( $name )
    {
        $data = array(
            '中通快递' => 'zhongtong',
            '韵达快递' => 'yunda',
            '百世汇通' => 'huitongkuaidi',
            '圆通快递' => 'yuantong',
            '申通快递' => 'shentong',
            'EMS' 	 => 'EMS',
            '顺丰快递' => 'shunfeng',
            '优速快递' => 'youshuwuliu',
            '天天快递' => 'tiantian',
            '宅急送'   => 'zhaijisong',
            '快捷速递' => 'kuaijiesudi',
            '全峰快递' => 'quanfengkuaidi',
            '安能物流' => 'annengwuliu'
        ) ;
        return $data[$name] ? $data[$name] : 'EMS' ;
    }
}