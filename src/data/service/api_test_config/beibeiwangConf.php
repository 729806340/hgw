<?php
/**
 * Created by CharlesChen
 * Date: 2018/1/25
 * Time: 14:41
 * File name:beibeiwangConf.php
 */
require_once('Common.php');
class beibeiwangConf extends Common{
    private $app_id = 'eico';
    private $secret = '486384679728c2f98229304b932da4a7';
    private $session = 'ab56f2b61639820058da335c2bc38';  //正式
    private $gateway = 'http://api.open.beibei.com/outer_api/out_gateway/route.html';
    public function getConf(){
        return array(
            'orderlist' => array(
                //用于统一时间控件名
                "startTime"=>'start_time',
                "endTime"=>'end_time',
                'comment' => '订单付款后会有30分钟的延迟，请控制好抓取时间范围;筛选时间依据是付款时间',
                //接口请求配置：method代表方法名，curl_type代表请求发送方式:0代表post,1代表get;para_is_json代表参数数组是否需要转换为json格式:0代表不需要，1代表需要
                'request' => array("method"=>"beibei.outer.trade.order.get","curl_type"=>0,"para_is_json"=>0),
                'form' => array(
                    //type:1为int,2为string；
                    //item_type:1为时间控件,0为普通input框,2为下拉选框;
                    //is_null：1为可选项，0为必选项
                    array('label'=>'开始时间','item_type'=>1,'name'=>'start_time','is_null'=>0,'type'=>2,'comment'=>'(默认取当前时间前24小时时间)'),
                    array('label'=>'结束时间','item_type'=>1,'name'=>'end_time','is_null'=>0,'type'=>2,'comment'=>'(默认取当前时间)'),
                    array('label'=>'筛选方式','item_type'=>2,"select_op"=>array('pay_time'=>array('cn'=>"支付时间",'check'=>"selected"),'modified_time'=>array('cn'=>'修改时间',"check"=>"")),"select_name"=>'time_range','is_null'=>1,'type'=>2,'comment'=>'(根据时间范围筛选。modified_time：修改时间;pay_time：支付时间，为默认值)'),
                    array('label'=>'订单状态','item_type'=>0,'name'=>'status','is_null'=>1,'type'=>1,'comment'=>'(订单状态，订单状态默认返回所有,1:待发货,2:已发货,3:已完成,4:已关闭)'),
                    array('label'=>'订单页码','item_type'=>0,'name'=>'page_no','is_null'=>1,'type'=>1,'comment'=>'(列表页码 默认值：1)'),
                    array('label'=>'单页大小','item_type'=>0,'name'=>'page_size','is_null'=>1,'type'=>1,'comment'=>'(每页显示数量 默认值：40，最大值：300 超过300返回用默认40条)'),
                )
            ),
            'getOrderDetail'=>array(
                'comment'=>'',
                //接口请求配置：method代表方法名，curl_type代表请求发送方式:0代表post,1代表get;para_is_json代表参数数组是否需要转换为json格式:0代表不需要，1代表需要
                'request' =>array("method"=>"beibei.outer.trade.order.detail.get","curl_type"=>0,"para_is_json"=>0),
                'form'=>array(
                    array('label'=>'订单单号','item_type'=>0,'name'=>'oid','is_null'=>0,'type'=>2,'comment'=>""),
                )
            ),
            'getSku'=>array(
                'comment'=>'商品sku为在售商品',
                //接口请求配置：method代表方法名，curl_type代表请求发送方式:0代表post,1代表get;para_is_json代表参数数组是否需要转换为json格式:0代表不需要，1代表需要
                'request' => array("method"=>"beibei.outer.item.warehouse.get","curl_type"=>0,"para_is_json"=>0),
                'form'=>array(
                    array('label'=>'列表页码','item_type'=>0,'name'=>'page_no','is_null'=>1,'type'=>1,'comment'=>'(列表页码 默认值：1)'),
                    array('label'=>'单页大小','item_type'=>0,'name'=>'page_size','is_null'=>1,'type'=>1,'comment'=>'(每页显示数量 默认值：40，最大值：300 超过300返回用默认40条)'),
                )
            ),
            'getRefund'=>array(
                //用于统一时间控件名
                "startTime"=>'start_time',
                "endTime"=>'end_time',
                'comment'=>'筛选时间为售后单创建时间',
                //接口请求配置：method代表方法名，curl_type代表请求发送方式:0代表post,1代表get;para_is_json代表参数数组是否需要转换为json格式:0代表不需要，1代表需要
                'request' => array("method"=>"beibei.outer.refunds.get","curl_type"=>0,"para_is_json"=>0),
                'form'=>array(
                    array('label'=>'开始时间','item_type'=>1,'name'=>'start_time','is_null'=>1,'type'=>2,'comment'=>''),
                    array('label'=>'结束时间','item_type'=>1,'name'=>'end_time','is_null'=>1,'type'=>2,'comment'=>''),
                    array('label'=>'筛选方式','item_type'=>2,"select_op"=>array('create_time'=>array('cn'=>"创建时间",'check'=>"selected"),'modified_time'=>array('cn'=>'修改时间',"check"=>"")),"select_name"=>'time_range','is_null'=>1,'type'=>2,'comment'=>'(根据时间范围筛选，modified_time：修改时间，create_time：创建时间 默认：create_time)'),
                    array('label'=>'订单状态','item_type'=>0,'name'=>'status','is_null'=>1,'type'=>1,'comment'=>'(售后状态 1:退款中,2:退款成功,3:退款关闭 默认不传值或者-1 返回所有)'),
                    array('label'=>'订单页码','item_type'=>0,'name'=>'page_no','is_null'=>1,'type'=>1,'comment'=>'(列表页码 默认值：1)'),
                    array('label'=>'单页大小','item_type'=>0,'name'=>'page_size','is_null'=>1,'type'=>1,'comment'=>'(每页显示数量 默认值：40，最大值：300 超过300返回用默认40条)'),
                )
            ),
            'getRefundDetail'=>array(
                'comment'=>'',
                //接口请求配置：method代表方法名，curl_type代表请求发送方式:0代表post,1代表get;para_is_json代表参数数组是否需要转换为json格式:0代表不需要，1代表需要
                'request' => array("method"=>"beibei.outer.refund.get","curl_type"=>0,"para_is_json"=>0),
                'form'=>array(
                    array('label'=>'售后单号','item_type'=>0,'name'=>'refund_id','is_null'=>0,'type'=>2,'comment'=>""),
                )
            ),
            'pushShip'=>array(
                'comment'=>'',
                //接口请求配置：method代表方法名，curl_type代表请求发送方式:0代表post,1代表get;para_is_json代表参数数组是否需要转换为json格式:0代表不需要，1代表需要
                'request' => array("method"=>"beibei.outer.trade.logistics.ship","curl_type"=>0,"para_is_json"=>0),
                'form'=>array(
                    array('label'=>'订单单号','item_type'=>0,'name'=>'oid','is_null'=>0,'type'=>2,'comment'=>""),
                    array('label'=>'物流公司','item_type'=>0,'name'=>'company','is_null'=>0,'type'=>2,'comment'=>"(目前支持：AOL澳通速递,今枫国际快运,八达通,中外速运,ZENY增益海淘,德邦物流,快捷速递,BPOST,优速快递,环球速运,EMS国际,威时沛运,中铁快运,安鲜达,信联通,飞洋快递,一号仓,国通快递,易达通,九曳供应链,心怡物流,UEQ,HanBon汉邦国际,Sufast,PeakMore骏丰国际,PCAExpress,CNPEx中邮快递,EWE全球快递,申通快递,包裹平邮,顺丰快递,中华邮政,EMS,韵达快递,EFS快递,中通快递,PANEX泛捷国际速递,圆通快递,安能物流,天天快递,百世汇通,佳成快递,邮政包裹)"),
                    array('label'=>'物流单号','item_type'=>0,'name'=>'out_sid','is_null'=>0,'type'=>2,'comment'=>""),
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
        $after=array('request'=>array('url'=>$this->gateway,'curl_type'=>$param['curl_type'],'para_is_json'=>$param['para_is_json']));
        foreach ($param as $k=>$v){
            if(in_array($k,array('curl_type','para_is_json','channel','api'))){
                unset($param[$k]);
            }
            if($k=="method"&&strpos($v,'ship')!==false){
                $param['company']=$this->bbwLogiCode( $param['company']);
            }
        }
        $param=array_merge($param,array(
            'app_id' => $this->app_id,
            'session' => $this->session,
            'timestamp' => TIMESTAMP
        ));
        ksort($param);
        $sign_str=$this->secret;
        foreach ($param as $key => $value) {
            $sign_str .= $key . $value;
        }
        $sign_str .= $this->secret;
        $param['sign']=strtoupper(md5($sign_str));
        $after['param']=$param;
        return $after;
    }
    public function bbwLogiCode( $name )
    {
        $data = array(
            'AOL澳通速递' => 'aolau',
            '今枫国际快运' => 'jinfeng',
            '八达通' => 'bdatong',
            '中外速运' => 'zhongaosu',
            'ZENY增益海淘' => 'zengyisudi',
            '德邦物流' => 'debangwuliu', //
            '快捷速递' => 'kuaijiesudi', //
            'BPOST' 	 => 'bpost',
            '优速快递' => 'youshuwuliu', //
            '环球速运' => 'huanqiu',
            'EMS国际' => 'emsguoji', //
            '威时沛运'   => 'wtdchina',
            '中铁快运' => 'zhongtie', //--
            '安鲜达' => 'exfresh',
            '信联通' => 'sinatone',
            '飞洋快递' => 'shipgce',
            '一号仓' => 'onehcang',
            '国通快递' => 'guotongkuaidi',//
            '易达通' => 'yidatong',
            '九曳供应链' => 'jiuyescm',
            '心怡物流' => 'alog',
            'UEQ' => 'ueq',
            'HanBon汉邦国际' => 'handboy',
            'Sufast' => 'sufast',
            'PeakMore骏丰国际' => 'junfengguoji',
            'PCAExpress' => 'pcaexpress',
            'CNPEx中邮快递' => 'cnpex',
            'EWE全球快递' => 'ewe',
            '申通快递' => 'shentong', //
            '包裹平邮' => 'youzhengguonei',//
            '顺丰快递' => 'shunfeng',//
            '中华邮政' => 'youzhengguoji',
            'EMS' => 'ems',//
            '韵达快递' => 'yunda',//
            'EFS快递' => 'efs',
            '中通快递' => 'zhongtong',//
            'PANEX泛捷国际速递' => 'epanex',
            '圆通快递' => 'yuantong',//
            '安能物流' => 'annengwuliu',//
            '天天快递' => 'tiantian',//
            '百世汇通' => 'huitongkuaidi',//
            '佳成快递' => 'jiacheng',
            '邮政包裹'=>'youzhengguonei',
        ) ;
        return $data[$name] ? $data[$name] : 'ems' ;
    }
}