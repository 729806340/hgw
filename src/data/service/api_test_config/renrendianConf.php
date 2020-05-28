<?php
/**
 * Created by CharlesChen
 * Date: 2018/1/25
 * Time: 14:41
 * File name:beibeiwangConf.php
 */
require_once('Common.php');
class renrendianConf extends Common{
    //测试
//    static $appid = "af1b913cec3c039b";
//    static $secret = "9addfb49af1b913cec3c039b2d6fdbe5" ;
//    static $router = "http://api.weiba05.com/router/rest" ;
//    static $domain = "http://api.weiba05.com/";
//    static $refresh_token = "22bbacb5354a618eda366a240a2e4ae2";
    //生产
    static $appid = "5356714791a4f20a";
    static $secret = "3f935b4b5356714791a4f20a934aa5d9" ;
    static $router = "http://apis.wxrrd.com/router/rest" ;
    static $domain = "http://apis.wxrrd.com/";
    static $refresh_token = "b8fe232b9cb801a3c83150f699d1854e";
    public function getConf(){
        return array(
            'orderlist' => array(
                //用于统一时间控件名
                "startTime"=>'created_at_start',
                "endTime"=>'created_at_end',
                'comment' => '接口只提供依据下单时间的查询;可根据订单状态查询售后相关数据',
                //接口请求配置：method代表方法名，curl_type代表请求发送方式:0代表post,1代表get;para_is_json代表参数数组是否需要转换为json格式:0代表不需要，1代表需要
                'request' => array("method"=>"weiba.wxrrd.trade.lists","curl_type"=>1,"para_is_json"=>0),
                'form' => array(
                    //type:1为int,2为string；
                    //item_type:1为时间控件,0为普通input框,2为下拉选框;
                    //is_null：1为可选项，0为必选项
                    array('label'=>'开始时间','item_type'=>1,'name'=>'created_at_start','is_null'=>0,'type'=>2,'comment'=>''),
                    array('label'=>'结束时间','item_type'=>1,'name'=>'created_at_end','is_null'=>0,'type'=>2,'comment'=>''),
                    array('label'=>'订单状态','item_type'=>0,'name'=>'type','is_null'=>0,'type'=>1,'comment'=>'(订单状态：topay:待付款,tosend:待发货,send:已发货,success:已完成,cancel:已关闭,refund:退款中,refunding:上门提货退款中)'),
                    array('label'=>'订单页码','item_type'=>0,'name'=>'offset','is_null'=>0,'type'=>1,'comment'=>'(列表页码 默认值：1)'),
                    array('label'=>'单页大小','item_type'=>0,'name'=>'limit','is_null'=>0,'type'=>1,'comment'=>'(每页显示数量 默认值：10)'),
                )
            ),
            'getOrderDetail'=>array(
                'comment'=>'',
                //接口请求配置：method代表方法名，curl_type代表请求发送方式:0代表post,1代表get;para_is_json代表参数数组是否需要转换为json格式:0代表不需要，1代表需要
                'request' =>array("method"=>"weiba.wxrrd.trade.details","curl_type"=>1,"para_is_json"=>0),
                'form'=>array(
                    array('label'=>'订单单号','item_type'=>0,'name'=>'order_sn','is_null'=>0,'type'=>2,'comment'=>""),
                )
            ),
            'getSku'=>array(
                'comment'=>'商品sku为在售商品',
                //接口请求配置：method代表方法名，curl_type代表请求发送方式:0代表post,1代表get;para_is_json代表参数数组是否需要转换为json格式:0代表不需要，1代表需要
                'request' => array("method"=>"weiba.wxrrd.goods.lists","curl_type"=>1,"para_is_json"=>0),
                'form'=>array(
                    array('label'=>'列表页码','item_type'=>0,'name'=>'offset','is_null'=>1,'type'=>1,'comment'=>'(列表页码 默认值：1)'),
                    array('label'=>'单页大小','item_type'=>0,'name'=>'limit','is_null'=>1,'type'=>1,'comment'=>'(每页显示数量 默认值：10)'),
                )
            ),
            'pushShip'=>array(
                'comment'=>'',
                //接口请求配置：method代表方法名，curl_type代表请求发送方式:0代表post,1代表get;para_is_json代表参数数组是否需要转换为json格式:0代表不需要，1代表需要
                'request' => array("method"=>"weiba.wxrrd.trade.send","curl_type"=>0,"para_is_json"=>0),
                'form'=>array(
                    array('label'=>'订单单号','item_type'=>0,'name'=>'order_sn','is_null'=>0,'type'=>2,'comment'=>""),
                    array('label'=>'物流公司','item_type'=>0,'name'=>'logis_code','is_null'=>0,'type'=>2,'comment'=>"(目前支持：中通快递,韵达快递,百世汇通,圆通快递,申通快递,EMS,顺丰快递,优速快递,天天快递,宅急送,快捷速递,全峰快递,安能物流)"),
                    array('label'=>'物流单号','item_type'=>0,'name'=>'logis_no','is_null'=>0,'type'=>2,'comment'=>""),
                )
            ),
            //接口下拉框配置
            'selectItem' => array(
                'orderlist' => "批量获取订单",
                'getOrderDetail' => "获取订单详情",
                'getSku' => '获取商品sku',
                'pushShip' => '订单发货接口'),
        );

    }
    public function dealWithParam($param){
        $after=array('request'=>array('url'=>self::$router,'curl_type'=>$param['curl_type'],'para_is_json'=>$param['para_is_json']));
        foreach ($param as $k=>$v){
            if(in_array($k,array('curl_type','para_is_json','channel','api'))){
                unset($param[$k]);
            }
            if($k=="method"&&strpos($v,'send')!==false){
                $param['logis_code']=$this->chgLogiCode( $param['logis_code']);
            }
            $param['appid']=self::$appid;
            $param['secret']=self::$secret;
            $param['timestamp']=date("Y-m-d H:i:s");
            $param['access_token']=$this->getToken();
        }

        $after['param']=$this->getSign($param);
        return $after;
    }
    function getToken()
    {
        $url = self::$domain . "token" ;
        $params = array(
            'appid' => self::$appid ,
            'secret' => self::$secret,
            'grant_type' => 'refresh_token',
            'refresh_token' => self::$refresh_token,
            'redirect_uri' => 'http://www.hangowa.com/callback-rrd.html'
        );
        $url .= "?" . http_build_query( $params ) ;
        $result = file_get_contents($url);
        return json_decode($result,true)['access_token'];
    }
    //计算接口sign，并返回带sign的params
    private static function getSign( $params )
    {
        ksort( $params ) ;
        $str = "";
        foreach ( $params as $k => $v ) {
            $str .= $k."=".$v."&";
        }
        $str = substr($str,0,-1) ;
        $sign = strtoupper( md5( $str ) ) ;
        $params['sign'] = $sign ;
        return $params ;
    }
    function chgLogiCode( $name )
    {
        $data = array(
            '中通快递' => 'zhongtong',
            '韵达快递' => 'yunda',
            '百世汇通' => 'huitongkuaidi',
            '圆通快递' => 'yuantong',
            '申通快递' => 'shentong',
            'EMS' => 'ems',
            '顺丰快递' => 'shunfeng',
            '优速快递' => 'youshuwuliu',
            '天天快递' => 'tiantian',
            '宅急送' => 'zhaijisong',
            '快捷速递' => 'kuaijiesudi',
            '全峰快递' => 'quanfengkuaidi',
            '安能物流' => 'annengwuliu'
        ) ;
        return $data[$name] ? $data[$name] : 'ems' ;
    }
}