<?php
/**
 * Created by CharlesChen
 * Date: 2018/1/25
 * Time: 14:41
 * File name:beibeiwangConf.php
 */
require_once('Common.php');
class youzanConf extends Common{
    private static $appid = "d8369a9dfb36888c5f";
    private static $appsecret = "76a5f9c5871aadce83ccb6717449725d";
    private $gateway='https://open.koudaitong.com/api/entry';
    public function getConf(){
        return array(
            'orderlist' => array(
                //用于统一时间控件名
                "startTime"=>'start_created',
                "endTime"=>'end_created',
                'comment' => '',
                //接口请求配置：method代表方法名，curl_type代表请求发送方式:0代表post,1代表get;para_is_json代表参数数组是否需要转换为json格式:0代表不需要，1代表需要
                'request' => array("method"=>"kdt.trades.sold.get","curl_type"=>0,"para_is_json"=>0),
                'form' => array(
                    //type:1为int,2为string；
                    //item_type:1为时间控件,0为普通input框,2为下拉选框;
                    //is_null：1为可选项，0为必选项
                    array('label'=>'开始时间','item_type'=>1,'name'=>'start_created','is_null'=>0,'type'=>2,'comment'=>''),
                    array('label'=>'结束时间','item_type'=>1,'name'=>'end_created','is_null'=>0,'type'=>2,'comment'=>''),
                    array('label'=>'订单状态','item_type'=>0,'name'=>'status','is_null'=>0,'type'=>1,'comment'=>'(一次只能查询一种状态。 可选值： TRADE_NO_CREATE_PAY（没有创建支付交易） WAIT_BUYER_PAY（等待买家付款） WAIT_GROUP（等待成团，即：买家已付款，等待成团） WAIT_SELLER_SEND_GOODS（等待卖家发货，即：买家已付款） WAIT_BUYER_CONFIRM_GOODS（等待买家确认收货，即：卖家已发货） TRADE_BUYER_SIGNED（买家已签收） TRADE_CLOSED（已关闭订单 包括TRADE_CLOSED 和 TRADE_CLOSED_BY_USER） ALL_WAIT_PAY（包含：WAIT_BUYER_PAY、TRADE_NO_CREATE_PAY))'),
                    array('label'=>'订单页码','item_type'=>0,'name'=>'page_no','is_null'=>0,'type'=>1,'comment'=>'(列表页码 默认值：1)'),
                    array('label'=>'单页大小','item_type'=>0,'name'=>'page_size','is_null'=>0,'type'=>1,'comment'=>'(每页条数，最大不能超过100)'),
                )
            ),
            'getOrderDetail'=>array(
                'comment'=>'',
                //接口请求配置：method代表方法名，curl_type代表请求发送方式:0代表post,1代表get;para_is_json代表参数数组是否需要转换为json格式:0代表不需要，1代表需要
                'request' =>array("method"=>"kdt.trade.get","curl_type"=>0,"para_is_json"=>0),
                'form'=>array(
                    array('label'=>'订单单号','item_type'=>0,'name'=>'tid','is_null'=>0,'type'=>2,'comment'=>""),
                )
            ),
            'pushShip'=>array(
                'comment'=>'',
                //接口请求配置：method代表方法名，curl_type代表请求发送方式:0代表post,1代表get;para_is_json代表参数数组是否需要转换为json格式:0代表不需要，1代表需要
                'request' => array("method"=>"kdt.logistics.online.confirm","curl_type"=>0,"para_is_json"=>0),
                'form'=>array(
                    array('label'=>'订单单号','item_type'=>0,'name'=>'tid','is_null'=>0,'type'=>2,'comment'=>""),
                    array('label'=>'物流公司','item_type'=>0,'name'=>'out_stype','is_null'=>0,'type'=>2,'comment'=>"(目前支持：申通快递,EMS,圆通快递,顺丰快递,韵达快递,中通快递,宅急送,天天快递,龙邦快递,全一快递,快捷速递,华宇物流,中铁快运,德邦物流,大田物流,百世汇通,全峰快递,优速快递,国通快递,安能物流)"),
                    array('label'=>'物流单号','item_type'=>0,'name'=>'out_sid','is_null'=>0,'type'=>2,'comment'=>""),
                )
            ),
            //接口下拉框配置
            'selectItem' => array(
                'orderlist' => "批量获取订单",
                'getOrderDetail' => "获取订单详情",
                'pushShip' => '订单发货接口'),
        );

    }
    public function dealWithParam($param){
        $after=array('request'=>array('url'=>$this->gateway,'curl_type'=>$param['curl_type'],'para_is_json'=>$param['para_is_json']));
        foreach ($param as $k=>$v){
            if(in_array($k,array('curl_type','para_is_json','channel','api'))){
                unset($param[$k]);
            }
            if($k=="method"&&strpos($v,'confirm')!==false){
                $param['out_stype']=self::$_logics[$param['out_stype']];
            }
            if($k=='method'&&strpos($v,'sold')!==false){
                $param['use_has_next']=true;
            }
        }
        $common_param=array(
            'app_id'=>self::$appid,
            'timestamp'=>date('Y-m-d H:i:s'),
            'format'=>'json',
            'v'=>'1.0',
            'sign_method'=>'md5'
        );
        $param=array_merge($param,$common_param);
        $param['sign']=$this->sign($param);
        $after['param']=$param;
        return $after;
    }
    public function sign($params) {
        if (!is_array($params)) $params = array();

        ksort($params);
        $text = '';
        foreach ($params as $k => $v) {
            $text .= $k . $v;
        }

        return md5( self::$appsecret . $text . self::$appsecret);
    }
    public static $_logics = array(
        '申通快递' => '1', //申通
        'EMS' => '11', //邮政
        '圆通快递' => '2', //圆通
        '顺丰快递' => '7', //顺丰
        '韵达快递' => '4', //韵达
        '中通快递' => '3', //中通
        '宅急送' => '25', //宅急送
        '天天快递' => '5', //天天快递
        '龙邦快递' => '32', //龙邦快递
        '全一快递' => '18', //全一快递
        '快捷速递' => '34', //快捷速递
        '华宇物流' => '61', //华宇物流
        '中铁快运' => '30', //中铁快运
        '德邦物流' => '28', //德邦物流
        '大田物流' => '49', //大田物流
        '百世汇通' => '6', //百世汇通
        '全峰快递' => '17', //全峰快递
        '优速快递' => '38', //优速快递
        '国通快递' => '40', //国通快递
        '安能物流' => '128'
    ) ;
}