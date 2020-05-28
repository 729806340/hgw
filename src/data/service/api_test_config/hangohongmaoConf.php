<?php
/**
 * Created by CharlesChen
 * Date: 2018/1/30
 * Time: 10:04
 * File name:hangohongmaoConf.php
 */
class hangohongmaoConf extends Common{
    private $gateway="http://hm.hansap.com/web/api.php";
    public function getConf(){
        return array(
            'orderlist' => array(
                //用于统一时间控件名
                "startTime"=>'starttime',
                "endTime"=>'endtime',
                'comment' => '',
                //接口请求配置：method代表方法名，curl_type代表请求发送方式:0代表post,1代表get;para_is_json代表参数数组是否需要转换为json格式:0代表不需要，1代表需要
                'request' => array("method"=>"orderlist","curl_type"=>1,"para_is_json"=>0),
                'form' => array(
                    //type:1为int,2为string；
                    //item_type:1为时间控件,0为普通input框,2为下拉选框;
                    //is_null：1为可选项，0为必选项
                    array('label'=>'开始时间','item_type'=>1,'name'=>'starttime','is_null'=>0,'type'=>2,'comment'=>''),
                    array('label'=>'结束时间','item_type'=>1,'name'=>'endtime','is_null'=>0,'type'=>2,'comment'=>''),
                    array('label'=>'订单状态','item_type'=>0,'name'=>'status','is_null'=>1,'type'=>1,'comment'=>'(订单状态，1:待发货,2:已发货,3:已完成,4:已关闭)'),
                    array('label'=>'订单页码','item_type'=>0,'name'=>'page_no','is_null'=>0,'type'=>1,'comment'=>'(列表页码 默认值：1)'),
                    array('label'=>'单页大小','item_type'=>0,'name'=>'page_size','is_null'=>0,'type'=>1,'comment'=>'(每页显示数量 默认值：50)'),
                )
            ),
            'getOrderDetail'=>array(
                'comment'=>'',
                //接口请求配置：method代表方法名，curl_type代表请求发送方式:0代表post,1代表get;para_is_json代表参数数组是否需要转换为json格式:0代表不需要，1代表需要
                'request' =>array("method"=>"detail","curl_type"=>0,"para_is_json"=>0),
                'form'=>array(
                    array('label'=>'订单单号','item_type'=>0,'name'=>'ordersn','is_null'=>0,'type'=>2,'comment'=>""),
                )
            ),
            'getSku'=>array(
                'comment'=>'商品sku为在售商品,无需填写参数',
                //接口请求配置：method代表方法名，curl_type代表请求发送方式:0代表post,1代表get;para_is_json代表参数数组是否需要转换为json格式:0代表不需要，1代表需要
                'request' => array("method"=>"goods","curl_type"=>0,"para_is_json"=>0),
                'form'=>array(
                )
            ),
            'pushShip'=>array(
                'comment'=>'',
                //接口请求配置：method代表方法名，curl_type代表请求发送方式:0代表post,1代表get;para_is_json代表参数数组是否需要转换为json格式:0代表不需要，1代表需要
                'request' => array("method"=>"delivery","curl_type"=>0,"para_is_json"=>0),
                'form'=>array(
                    array('label'=>'订单单号','item_type'=>0,'name'=>'ordersn','is_null'=>0,'type'=>2,'comment'=>""),
                    array('label'=>'物流公司','item_type'=>0,'name'=>'expresscom','is_null'=>0,'type'=>2,'comment'=>"(目前支持：安信达,DHL快递,大田物流,德邦物流,EMS,飞康达,FedEx(国际),港中能达,挂号信,共速达,佳吉快运,佳怡物流,急先达,快捷速递,龙邦快递,联邦快递,联昊通,全一快递,全峰快递,全日通,申通快递,顺丰快递,速尔快递,TNT快递,天天快递,天地华宇,UPS快递,新邦物流,信丰物流,圆通快递,韵达快递,邮政包裹,优速快递,中通快递,中铁快运,宅急送,中邮物流,国通快递,安能物流)"),
                    array('label'=>'物流单号','item_type'=>0,'name'=>'expresssn','is_null'=>0,'type'=>2,'comment'=>""),
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
        $after=array('request'=>array('url'=>"",'curl_type'=>$param['curl_type'],'para_is_json'=>$param['para_is_json']));
        foreach ($param as $k=>$v){
            $k=='method'&&$param['op']=$v;
            if(in_array($k,array('curl_type','para_is_json','channel','api','method'))){
                unset($param[$k]);
            }
            if($k=="method"&&strpos($v,'delivery')!==false){
                $logi_name=explode(',',$this->chgLogiCode($param['expresscom']));
                $param['expresscom']=$logi_name[0];
                $param['express']=$logi_name[1];
                $param['sendtime']=time();
            }
            if($k=="method"&&$v=='orderlist'){
                $param['status']=1;
                $param['searchtime']='paytime';
            }
        }
        $param['uniacid']=1;
        $after['param']=$param;
        $after['request']['url']=$this->gateway;
        return $after;

    }
    public function chgLogiCode($name)
    {
        $data = array(
            '安信达'=>'安信达快递,anxindakuaixi',
            'DHL快递'=>'dhl,dhl',
            '大田物流'=>'大田物流,datianwuliu',
            '德邦物流'=>'德邦物流,debangwuliu',
            'EMS'=>'ems快递,ems',
            '飞康达'=>'飞康达物流,feikangda',
            'FedEx(国际)'=>'fedex（国外),fedex',
            '港中能达'=>'港中能达物流,ganzhongnengda',
            '挂号信'=>'邮政包裹挂号信,youzhengguonei',
            '共速达'=>'共速达,gongsuda',
            '佳吉快运'=>'佳吉物流,jjwl',
            '佳怡物流'=>'佳怡物流,jiayiwuliu',
            '急先达'=>'急先达,jixianda',
            '快捷速递'=>'快捷速递,kuaijiesudi',
            '龙邦快递'=>'龙邦物流,longbanwuliu',
            '联邦快递'=>'联邦快递（国内),lianb',
            '联昊通'=>'联昊通物流,lianhaowuliu',
            '全一快递'=>'全一快递,quanyikuaidi',
            '全峰快递'=>'全峰快递,quanfengkuaidi',
            '全日通'=>'全日通快递,quanritongkuaidi',
            '申通快递'=>'申通,shentong',
            '顺丰快递'=>'顺丰,shunfeng',
            '速尔快递'=>'速尔物流,sue',
            'TNT快递'=>'tnt,tnt',
            '天天快递'=>'天天快递,tiantian',
            '天地华宇'=>'天地华宇,tiandihuayu',
            'UPS快递'=>'ups,ups',
            '新邦物流'=>'新邦物流,xinbangwuliu',
            '信丰物流'=>'信丰物流,xinfengwuliu',
            '圆通快递'=>'圆通速递,yuantong',
            '韵达快递'=>'韵达快运,yunda',
            '邮政包裹'=>'邮政包裹挂号信,youzhengguonei',
            '优速快递'=>'优速物流,youshuwuliu',
            '中通快递'=>'中通速递,zhongtong',
            '中铁快运'=>'中铁快运,zhongtiekuaiyun',
            '宅急送'=>'宅急送,zhaijisong',
            '中邮物流'=>'中邮物流,zhongyouwuliu',
            '国通快递'=>'国通快递,guotongkuaidi',
            '安能物流'=>'安能物流,annengwuliu'
        ) ;
        return $data[$name] ? $data[$name] : 'ems快递,ems' ;
    }
}