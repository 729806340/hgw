<?php
/**
 * Created by CharlesChen
 * Date: 2018/1/29
 * Time: 13:21
 * File name:gegejiaConf.php
 */
class gegejia1Conf extends Common{
    private  $partner='GGJ_WHYX01';
    private  $key='0b543ac237734e5883a1f94403e1b6f6';
    private  $gateway='http://openapi.gegejia.com:8902/api';
    public function getConf(){
        return  array('orderlist' => array(
            //用于统一时间控件名
            "startTime"=>'startTime',
            "endTime"=>'endTime',
            'comment' => '获取商家在格格家平台产生的订单,开始时间必须小于结束时间，并且起始时间跟结束时间差不得超过30天',
            //接口请求配置：method代表方法名，curl_type代表请求发送方式:0代表post,1代表get;para_is_json代表参数数组是否需要转换为json格式:0代表不需要，1代表需要
            'request' => array("method"=>"/order/findOrders","curl_type"=>0,"para_is_json"=>0),
            'form' => array(
                //type:1为int,2为string；
                //item_type:1为时间控件,0为普通input框,2为下拉选框;
                //is_null：1为可选项，0为必选项
                array('label'=>'开始时间','item_type'=>1,'name'=>'startTime','is_null'=>0,'type'=>2,'comment'=>'(付款起始时间)'),
                array('label'=>'结束时间','item_type'=>1,'name'=>'endTime','is_null'=>0,'type'=>2,'comment'=>'(付款结束时间)'),
                array('label'=>'订单状态','item_type'=>0,'name'=>'status','is_null'=>0,'type'=>1,'comment'=>'(订单状态；1：未付款，2：待发货，3：已发货，4：交易成功，5：用户取消（待退款团购），6：超时取消（已退款团购），7：团购进行中(团购))'),
                array('label'=>'订单页码','item_type'=>0,'name'=>'page','is_null'=>0,'type'=>1,'comment'=>'(列表页码 默认值：1)'),
                array('label'=>'单页大小','item_type'=>0,'name'=>'pageSize','is_null'=>0,'type'=>1,'comment'=>'(每页数量，不传默认一页200条，每页最大200条)'),
            )
        ),
            'pushShip'=>array(
            'comment'=>'',
            //接口请求配置：method代表方法名，curl_type代表请求发送方式:0代表post,1代表get;para_is_json代表参数数组是否需要转换为json格式:0代表不需要，1代表需要
            'request' => array("method"=>"/order/sendOrder","curl_type"=>0,"para_is_json"=>0),
            'form'=>array(
                array('label'=>'订单单号','item_type'=>0,'name'=>'orderNumber','is_null'=>0,'type'=>2,'comment'=>""),
                array('label'=>'type','item_type'=>0,'name'=>'type','is_null'=>0,'type'=>2,'comment'=>"订单类型，0：渠道订单，1：格格家订单，2：格格团订单，3：格格团全球购订单，4：环球捕手订单，5：燕网订单，6：b2b订单，7：手q，8：云店，9：燕窝酵素，10：美食买手"),
                array('label'=>'物流公司','item_type'=>0,'name'=>'expressName','is_null'=>0,'type'=>2,'comment'=>"(安信达,包裹平邮,传喜物流,DHL快递,大田物流,德邦物流,EMS,EMS国际,飞康达,FedEx(国际),港中能达,共速达,华宇物流,佳吉快运,佳怡物流,急先达,快捷速递,龙邦快递,联邦快递,联昊通,全一快递,全峰快递,全日通,申通快递,顺丰快递,速尔快递,TNT快递,天天快递,天地华宇,UPS快递,USPS,新邦物流,信丰物流,希伊艾斯,圆通快递,韵达快递,邮政包裹,优速快递,中通快递,中铁快运,宅急送,中邮物流,国通快递,京东快递,安能物流)"),
                array('label'=>'物流单号','item_type'=>0,'name'=>'expressNo','is_null'=>0,'type'=>2,'comment'=>""),
            )
        ),
            //接口下拉框配置
            'selectItem' => array(
            'orderlist' => "批量获取订单",
            'pushShip' => '订单发货接口'),
        );
    }
    public function dealWithParam($param){
        $after=array('request'=>array('url'=>$this->gateway.$param['method'],'curl_type'=>$param['curl_type'],'para_is_json'=>$param['para_is_json']));
        foreach ($param as $k=>$v){
            if(in_array($k,array('curl_type','para_is_json','channel','api','method'))){
                unset($param[$k]);
            }
            if($k=="method"&&strpos($v,'sendOrder')!==false){
                $param['expressName']=$this->chgLogiCode( $param['expressName']);
            }
        }
        $after['param']['param']=json_encode(array('partner'=>$this->partner,'timestamp'=>date('Y-m-d H:i:s'),'params'=>$param));
        $after['param']['header']=array("Content-Type:application/json","sign:".strtoupper(md5($this->key.$after['param']['param'].$this->key)));
        return $after;
    }
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
}