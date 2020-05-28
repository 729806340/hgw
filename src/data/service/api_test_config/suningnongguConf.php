<?php
/**
 * Created by CharlesChen
 * Date: 2018/1/25
 * Time: 14:41
 * File name:beibeiwangConf.php
 */
require_once('Common.php');
class suningnongguConf extends Common{
    private $_config = array(
        'appKey'=>'fcefd0f8a36d2e1b5ea948d83bb137be',
        'appSecret'=>'8893097b802699ca0df89de12b3c945b',
        'url'=>'http://open.suning.com/api/http/sopRequest',
    );
    public function getConf(){
        return array(
            'orderlist' => array(
                //用于统一时间控件名
                "startTime"=>'startTime',
                "endTime"=>'endTime',
                'comment' => '查询时间跨度不能超过30天,接口批量获取当前时间往前三个月的时间内，产生的订单信息',
                //接口请求配置：method代表方法名，curl_type代表请求发送方式:0代表post,1代表get;para_is_json代表参数数组是否需要转换为json格式:0代表不需要，1代表需要
                'request' => array("method"=>"suning.custom.order.query","curl_type"=>0,"para_is_json"=>0),
                'form' => array(
                    //type:1为int,2为string；
                    //item_type:1为时间控件,0为普通input框,2为下拉选框;
                    //is_null：1为可选项，0为必选项
                    array('label'=>'开始时间','item_type'=>1,'name'=>'startTime','is_null'=>0,'type'=>2,'comment'=>''),
                    array('label'=>'结束时间','item_type'=>1,'name'=>'endTime','is_null'=>0,'type'=>2,'comment'=>''),
                    array('label'=>'订单状态','item_type'=>0,'name'=>'orderStatus','is_null'=>1,'type'=>1,'comment'=>'(订单头状态（10：买家已付款，20：卖家已发货，21：部分发货，30：交易成功，40：交易关闭）)'),
                    array('label'=>'订单页码','item_type'=>0,'name'=>'pageNo','is_null'=>1,'type'=>1,'comment'=>'(列表页码)'),
                    array('label'=>'单页大小','item_type'=>0,'name'=>'pageSize','is_null'=>1,'type'=>1,'comment'=>'(每页显示数量)'),
                )
            ),
            'getOrderDetail'=>array(
                'comment'=>'',
                //接口请求配置：method代表方法名，curl_type代表请求发送方式:0代表post,1代表get;para_is_json代表参数数组是否需要转换为json格式:0代表不需要，1代表需要
                'request' =>array("method"=>"suning.custom.order.get","curl_type"=>0,"para_is_json"=>0),
                'form'=>array(
                    array('label'=>'订单单号','item_type'=>0,'name'=>'orderCode','is_null'=>0,'type'=>2,'comment'=>""),
                )
            ),
            'getSku'=>array(
                'comment'=>'商品sku为在售商品',
                //接口请求配置：method代表方法名，curl_type代表请求发送方式:0代表post,1代表get;para_is_json代表参数数组是否需要转换为json格式:0代表不需要，1代表需要
                'request' => array("method"=>"suning.custom.item.query","curl_type"=>0,"para_is_json"=>0),
                'form'=>array(
                    array('label'=>'列表页码','item_type'=>0,'name'=>'pageNo','is_null'=>1,'type'=>1,'comment'=>'(列表页码 默认值：1)'),
                    array('label'=>'单页大小','item_type'=>0,'name'=>'pageSize','is_null'=>1,'type'=>1,'comment'=>'(每页显示数量 默认值：40，最大值：300 超过300返回用默认40条)'),
                )
            ),
            'getRefund'=>array(
                //用于统一时间控件名
                "startTime"=>'startTime',
                "endTime"=>'endTime',
                'comment'=>'筛选时间为售后单创建时间',
                //接口请求配置：method代表方法名，curl_type代表请求发送方式:0代表post,1代表get;para_is_json代表参数数组是否需要转换为json格式:0代表不需要，1代表需要
                'request' => array("method"=>"suning.custom.batchrejected.query","curl_type"=>0,"para_is_json"=>0),
                'form'=>array(
                    array('label'=>'开始时间','item_type'=>0,'name'=>'startTime','is_null'=>0,'type'=>2,'comment'=>''),
                    array('label'=>'结束时间','item_type'=>0,'name'=>'endTime','is_null'=>0,'type'=>2,'comment'=>''),
                    array('label'=>'订单页码','item_type'=>0,'name'=>'pageNo','is_null'=>1,'type'=>1,'comment'=>'(列表页码 默认值：1)'),
                    array('label'=>'单页大小','item_type'=>0,'name'=>'pageSize','is_null'=>1,'type'=>1,'comment'=>'(每页显示数量 默认值：40)'),
                )
            ),
            'getRefundDetail'=>array(
                'comment'=>'',
                //接口请求配置：method代表方法名，curl_type代表请求发送方式:0代表post,1代表get;para_is_json代表参数数组是否需要转换为json格式:0代表不需要，1代表需要
                'request' => array("method"=>"suning.custom.singlerejected.get","curl_type"=>0,"para_is_json"=>0),
                'form'=>array(
                    array('label'=>'售后单号','item_type'=>0,'name'=>'orderCode','is_null'=>0,'type'=>2,'comment'=>""),
                )
            ),
            'pushShip'=>array(
                'comment'=>'',
                //接口请求配置：method代表方法名，curl_type代表请求发送方式:0代表post,1代表get;para_is_json代表参数数组是否需要转换为json格式:0代表不需要，1代表需要
                'request' => array("method"=>"suning.custom.orderdelivery.add","curl_type"=>0,"para_is_json"=>0),
                'form'=>array(
                    array('label'=>'订单单号','item_type'=>0,'name'=>'orderCode','is_null'=>0,'type'=>2,'comment'=>""),
                    array('label'=>'物流公司','item_type'=>0,'name'=>'expressCompanyCode','is_null'=>0,'type'=>2,'comment'=>"(目前支持：包裹平邮,DHL快递,大田物流,德邦物流,EMS,飞康达,FedEx(国际),港中能达,挂号信,共速达,百世汇通,佳吉快运,佳怡物流,急先达,快捷速递,龙邦快递,联邦快递,联昊通,全一快递,全峰快递,全日通,申通快递,顺丰快递,速尔快递,TNT快递,天天快递,天地华宇,UPS快递,新邦物流,信丰物流,圆通快递,韵达快递,邮政包裹,优速快递,中通快递,中铁快运,宅急送,中邮物流,国通快递,安能物流)"),
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
        $after=array('request'=>array('url'=>$this->_config['url'],'curl_type'=>$param['curl_type'],'para_is_json'=>$param['para_is_json']));
        foreach ($param as $k=>$v){
            if(in_array($k,array('curl_type','para_is_json','channel','api'))){
                unset($param[$k]);
            }
            if($k=="method"&&strpos($v,'orderdelivery')!==false){
                $param['expressCompanyCode']=$this->getShipId( $param['expressCompanyCode']);
            }
        }
        $header = array(
            'secret_key'=>$this->_config['appSecret'],
            'method'=>$param['method'],
            'date'=>date('Y-m-d H:i:s',time()),
            'app_key'=>$this->_config['appKey'],
            'api_version'=>'v1.2',
        );
        unset($param['method']);
        $paramsArray = array('sn_request' => array('sn_body' => array(
            "{$this->getBizName($header['method'])}" => $param
        )));
        $apiParams=json_encode($paramsArray);
        $header['post_field']=base64_encode($apiParams);
        $signHeader=$this->_getSign($header);
        $after['param']['header']=$signHeader;
        $after['param']['param']=$apiParams;
        return $after;
    }
    /* 获取签名 @param array $param 请求参数数据集合*/
    private function _getSign( $params = array() ){
        $signString = '';
        foreach($params as $k => $v){
            $signString .= $v;
        }
        unset($k, $v);
        $signString = md5($signString);
        // 组装头文件信息
        $signDataHeader = array(
            "Content-Type: text/xml; charset=utf-8",
            "AppMethod:".$params['method'],
            "AppRequestTime:" .date('Y-m-d H:i:s',time()),
            "Format:json",
            "signInfo:" . $signString,
            "AppKey:" . $params['app_key'],
            "VersionNo:".$params['api_version'],
            "User-Agent:suning-sdk-php" ,
            "Sdk-Version:suning-sdk-php-beta0.1 "
        );
        return $signDataHeader;
    }
    public function  getBizName($bizname){
        $data=array(
            'suning.custom.logisticcompany.query'=>'logisticCompany',
            'suning.custom.order.query'=>'orderQuery',
            'suning.custom.batchrejected.query'=>'batchQueryRejected',
            "suning.custom.singlerejected.get"=>'singleGetRejected',
            'suning.custom.orderdelivery.add'=>'orderDelivery',
            'suning.custom.order.get'=>'orderGet',
            'suning.custom.item.query'=>'item'
        );
        return $data[$bizname];
    }
    public function getShipId($shipname){
        $data=array(
            '包裹平邮'=>'B03',
            'DHL快递'=>'D01',
            '大田物流'=>'D03',
            '德邦物流'=>'D04',
            'EMS'=>'E01',
            '飞康达'=>'F01',
            'FedEx(国际)'=>'F02',
            '港中能达'=>'G02',
            '挂号信'=>'B03',
            '共速达'=>'G04',
            '百世汇通'=>'H01',
            '佳吉快运'=>'J01',
            '佳怡物流'=>'J02',
            '急先达'=>'J04',
            '快捷速递'=>'K01',
            '龙邦快递'=>'L01',
            '联邦快递'=>'L03',
            '联昊通'=>'L02',
            '全一快递'=>'Q01',
            '全峰快递'=>'Q03',
            '全日通'=>'Q04',
            '申通快递'=>'S01',
            '顺丰快递'=>'S02',
            '速尔快递'=>'S03',
            'TNT快递'=>'T01',
            '天天快递'=>'T02',
            '天地华宇'=>'H02',
            'UPS快递'=>'U02',
            '新邦物流'=>'X03',
            '信丰物流'=>'X04',
            '圆通快递'=>'Y01',
            '韵达快递'=>'Y02',
            '邮政包裹'=>'B03',
            '优速快递'=>'Y10',
            '中通快递'=>'Z01',
            '中铁快运'=>'Z02',
            '宅急送'=>'Z04',
            '中邮物流'=>'Z03',
            '国通快递'=>'G05',
            '安能物流'=>'B07'
        );
        return !empty($data[$shipname]) ? $data[$shipname]:$data["EMS"];
    }
}