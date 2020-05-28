<?php
/**
 * Created by CharlesChen
 * Date: 2018/1/26
 * Time: 13:14
 * File name:chuchujieConf.php
 */
class chuchujieConf extends Common{
    static $orgname = "JZDYiX15";
    static $appid = "948b4dea388cb101f92dfde4839060c8";
    static $secret = "c9c1d036742805f9260f77ad256f4151fd9f893d";
    private $apiUri = "https://parter.api.chuchujie.com/sqe/Order/";
    public function getConf(){
        return array(
            'orderlist' => array(
                //用于统一时间控件名
                "startTime"=>'ctime_start',
                "endTime"=>'ctime_end',
                'comment' => '请求区间最多7天。结束时间-开始时间≤7天，展示全部数据。结束时间-开始时间＞7天，展示开始时间往后7天的数据。有开始时间，无结束时间，展示开始时间往后7天的数据。	无开始时间，有结束时间，时间无效。',
                //接口请求配置：method代表方法名，curl_type代表请求发送方式:0代表post,1代表get;para_is_json代表参数数组是否需要转换为json格式:0代表不需要，1代表需要
                'request' => array("method"=>"get_order_list_v2","curl_type"=>1,"para_is_json"=>0),
                'form' => array(
                    //type:1为int,2为string；
                    //item_type:1为时间控件,0为普通input框,2为下拉选框;
                    //is_null：1为可选项，0为必选项
                    array('label'=>'开始时间','item_type'=>1,'name'=>'ctime_start','is_null'=>0,'type'=>2,'comment'=>''),
                    array('label'=>'结束时间','item_type'=>1,'name'=>'ctime_end','is_null'=>0,'type'=>2,'comment'=>''),
                    array('label'=>'订单状态','item_type'=>0,'name'=>'status','is_null'=>1,'type'=>1,'comment'=>'(订单状态，0:所有;1:等待付款;2:等待发货(可以发货);3:等待确认收货;4:交易成功;5:交易关闭(退款完成);6:交易取消(未付款);7:订单冻结(未发货时申请退款，未取消退款、未退款成功))'),
                    array('label'=>'订单页码','item_type'=>0,'name'=>'page','is_null'=>1,'type'=>1,'comment'=>'(页码，起始为0)'),
                    array('label'=>'单页大小','item_type'=>0,'name'=>'page_size','is_null'=>1,'type'=>1,'comment'=>'(每页大小，默认50)'),
                )
            ),
            'getOrderDetail'=>array(
                'comment'=>'',
                'request' => array("method"=>"get_order_list_v2","curl_type"=>1,"para_is_json"=>0),
                'form'=>array(
                    array('label'=>'订单单号','item_type'=>0,'name'=>'order_id','is_null'=>0,'type'=>2,'comment'=>""),
                )
            ),
            'getSku'=>array(
                'comment'=>'商品sku为在售商品',
                'request' => array("method"=>"get_goodsinfo_for_key","curl_type"=>1,"para_is_json"=>0),
                'form'=>array(
                    array('label'=>'列表页码','item_type'=>0,'name'=>'page','is_null'=>1,'type'=>1,'comment'=>'(页码，起始为0)'),
                    array('label'=>'单页大小','item_type'=>0,'name'=>'page_size','is_null'=>1,'type'=>1,'comment'=>'(每页大小，默认50)'),
                )
            ),
            'pushShip'=>array(
                'comment'=>'',
                'request' => array("method"=>"api_order_shipping_v2","curl_type"=>1,"para_is_json"=>0),
                'form'=>array(
                    array('label'=>'订单单号','item_type'=>0,'name'=>'oid','is_null'=>0,'type'=>2,'comment'=>""),
                    array('label'=>'物流公司','item_type'=>0,'name'=>'express_company','is_null'=>0,'type'=>2,'comment'=>"(目前支持：佳吉快运,国通快递,中通快递,韵达快递,百世汇通,圆通快递,申通快递,EMS,顺丰快递,优速快递,天天快递,宅急送,快捷速递,全峰快递,城际快递,邮政包裹,包裹平邮,共速达,安能物流)"),
                    array('label'=>'物流单号','item_type'=>0,'name'=>'express_no','is_null'=>0,'type'=>2,'comment'=>""),
                )
            ),
            'selectItem' => array(
                'orderlist' => "批量获取订单",
                'getOrderDetail' => "获取订单详情",
                'getSku' => '获取商品sku',
                'pushShip' => '订单发货接口')
        );
    }
    public function dealWithParam($param){
        $after=array('request'=>array('url'=>$this->apiUri.$param['method'],'curl_type'=>$param['curl_type'],'para_is_json'=>$param['para_is_json']));
        foreach ($param as $k=>$v){
            if(in_array($k,array('method','curl_type','para_is_json','channel','api'))){
                unset($param[$k]);
            }
            if($k=="method"&&$param['method']=='api_order_shipping_v2'){
                $param['express_company']=$this->chgLogiCode( $param['express_company']);
            }
        }
        $rand=rand(10000, 999999);
        $time=time();
        $after['param']['header']['Timestamp']=time();
        $after['param']['header'][]='Org-Name:'.self::$orgname;
        $after['param']['header'][]='App-Key:'.self::$appid;
        $after['param']['header'][]='Nonce:'.$rand;
        $after['param']['header'][]='Timestamp:'.$time;
        $after['param']['header'][]='Signature:'.sha1($rand . self::$secret .$time);
        $after['param']['param']=http_build_query($param);
        return $after;
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
}