<?php
/**
 * Created by CharlesChen
 * Date: 2018/1/25
 * Time: 14:41
 * File name:beibeiwangConf.php
 */
require_once('Common.php');
class ylmgConf extends Common{
    private $app_id = 's11c9frmb0';
    private $secret = 'Gnr0ogLpxKUK49WNd7qrqgav31hH@TpS';
    private $gateway = 'http://s.yunlianmeigou.com/hangowa/support/api';
    public function getConf(){
        return array(
            'orderlist' => array(
                //用于统一时间控件名
                "startTime"=>'start_time',
                "endTime"=>'end_time',
                'comment' => '',
                //接口请求配置：method代表方法名，curl_type代表请求发送方式:0代表post,1代表get;para_is_json代表参数数组是否需要转换为json格式:0代表不需要，1代表需要
                'request' => array("method"=>"batchQueryOrder","curl_type"=>1,"para_is_json"=>0),
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
                'request' =>array("method"=>"singleQueryOrder","curl_type"=>1,"para_is_json"=>0),
                'form'=>array(
                    array('label'=>'订单单号','item_type'=>0,'name'=>'flow_id','is_null'=>0,'type'=>2,'comment'=>""),
                )
            ),
            'getSku'=>array(
                'comment'=>'商品sku为在售商品',
                //接口请求配置：method代表方法名，curl_type代表请求发送方式:0代表post,1代表get;para_is_json代表参数数组是否需要转换为json格式:0代表不需要，1代表需要
                'request' => array("method"=>"batchQueryGoods","curl_type"=>1,"para_is_json"=>0),
                'form'=>array(
                    array('label'=>'列表页码','item_type'=>0,'name'=>'page_no','is_null'=>0,'type'=>1,'comment'=>'(列表页码 默认值：1)'),
                    array('label'=>'单页大小','item_type'=>0,'name'=>'page_size','is_null'=>0,'type'=>1,'comment'=>'(每页显示数量 默认值：40，最大值：300 超过300返回用默认40条)'),
                )
            ),
            'getRefund'=>array(
                //用于统一时间控件名
                "startTime"=>'start_time',
                "endTime"=>'end_time',
                'comment'=>'筛选时间为售后单创建时间',
                //接口请求配置：method代表方法名，curl_type代表请求发送方式:0代表post,1代表get;para_is_json代表参数数组是否需要转换为json格式:0代表不需要，1代表需要
                'request' => array("method"=>"queryRefund","curl_type"=>1,"para_is_json"=>0),
                'form'=>array(
                    array('label'=>'开始时间','item_type'=>1,'name'=>'start_time','is_null'=>0,'type'=>2,'comment'=>''),
                    array('label'=>'结束时间','item_type'=>1,'name'=>'end_time','is_null'=>0,'type'=>2,'comment'=>''),
                    array('label'=>'订单页码','item_type'=>0,'name'=>'pageNo','is_null'=>1,'type'=>1,'comment'=>'(列表页码 默认值：1)'),
                    array('label'=>'单页大小','item_type'=>0,'name'=>'pageSize','is_null'=>1,'type'=>1,'comment'=>'(每页显示数量)'),
                )
            ),
            'pushShip'=>array(
                'comment'=>'',
                //接口请求配置：method代表方法名，curl_type代表请求发送方式:0代表post,1代表get;para_is_json代表参数数组是否需要转换为json格式:0代表不需要，1代表需要
                'request' => array("method"=>"deliverOrder","curl_type"=>0,"para_is_json"=>1),
                'form'=>array(
                    array('label'=>'订单单号','item_type'=>0,'name'=>'flow_id','is_null'=>0,'type'=>2,'comment'=>""),
                    array('label'=>'物流公司','item_type'=>0,'name'=>'express_code','is_null'=>0,'type'=>2,'comment'=>"(目前支持：快捷快递,邮政包裹,德邦物流,EMS,EMS国际,凡客如风达,龙邦快递,联邦快递,全一快递,全峰快递,申通快递,顺丰快递,速尔快递,天天快递,天地华宇,USPS,新邦物流,圆通快递,韵达快递,优速快递,中通快递,中铁快运,宅急送,中邮物流,国通快递,京东快递,百世汇通,安能物流)"),
                    array('label'=>'物流单号','item_type'=>0,'name'=>'express_no','is_null'=>0,'type'=>2,'comment'=>""),
                )
            ),
            //接口下拉框配置
            'selectItem' => array(
                'orderlist' => "批量获取订单",
                'getOrderDetail' => "获取订单详情",
                'getSku' => '获取商品sku',
                'getRefund' => '售后接口',
                'pushShip' => '订单发货接口'),
        );
    }
    public function dealWithParam($param){
        $after=array('request'=>array('url'=>$this->gateway,'curl_type'=>$param['curl_type'],'para_is_json'=>$param['para_is_json']));
        if($after['request']['curl_type']==0){
            $after['request']['url']=$this->gateway."?method=".$param['method'];
            unset($param['method']);
        }
        foreach ($param as $k=>$v){
            if(in_array($k,array('curl_type','para_is_json','channel','api'))){
                unset($param[$k]);
            }
            if($k=="method"&&$v=="deliverOrder"){
                $param['express_code']=$this->rrypLogiCode( $param['express_code']);
            }
            if($k=="method"&&$v=="batchQueryOrder"){
                $param['pay_status']='PAYED';
            }
        }
        $sys_params = array(
            'appid' => $this->app_id,
            'appsecret' => $this->secret,
            'date_type' => 1
        );
        $sign_params = array_merge($param, $sys_params);
        $after['param'] = array_merge($sign_params, array('sign' => $this->gen_sign($sign_params)));
        return $after;
    }
    /*获取签名*/
    protected function gen_sign(array $params)
    {
        ksort($params);
        $sign_str = "";
        foreach ($params as $key => $value) {
            $sign_str .= $key . "=" . $value . "&";
        }
//        $sign_str=substr($sign_str,0,strlen($sign_str)-1);
        $sign_str .= $this->secret;
        return sha1($sign_str);
    }
    function rrypLogiCode($name)
    {
        $data = array(
            '快捷快递' => 'kuaijiesudi',
            '邮政包裹' => 'youzhengguonei',
            '德邦物流' => 'debangwuliu',
            'EMS' => 'ems',
            'EMS国际' => 'emsguoji',
            '凡客如风达' => 'rufengda',
            '龙邦快递' => 'longbanwuliu',
            '联邦快递' => 'lianbangkuaidi',
            '全一快递' => 'quanyikuaidi',
            '全峰快递' => 'quanfengkuaidi',
            '申通快递' => 'shentong',
            '顺丰快递' => 'shunfeng',
            '速尔快递' => 'suer',
            '天天快递' => 'tiantian',
            '天地华宇' => 'tiandihuayu',
            'USPS' => 'usps',
            '新邦物流' => 'xinbangwuliu',
            '圆通快递' => 'yuantong',
            '韵达快递' => 'yunda',
            '优速快递' => 'youshuwuliu',
            '中通快递' => 'zhongtong',
            '中铁快运' => 'zhongtiewuliu',
            '宅急送' => 'zhaijisong',
            '中邮物流' => 'zhongyouwuliu',
            '国通快递' => 'guotongkuaidi',
            '京东快递' => 'jd',
            '百世汇通' => 'huitongkuaidi',
            '安能物流' => 'ane66'
        );
        return $data[$name] ? $data[$name] : 'ems';
    }
}