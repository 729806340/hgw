<?php
/**
 * Created by CharlesChen
 * Date: 2018/1/30
 * Time: 11:34
 * File name:huiguoConf.php
 */
class juanpiConf extends Common{
    private $_scope = 'order_list,order_info,send_goods,get_express';
    private $_host='http://open.juanpi.com';
    private $_key='b3fbdd35403ac92cd6ff28e74d6eb3d0';
    private $_secret='293c13563f816184553efe6eab59c9ae';
    private $_cus='9D1FE944533567B3E664';
    public function getConf(){
        return array(
            'orderlist' => array(
                //用于统一时间控件名
                "startTime"=>'start_time',
                "endTime"=>'end_time',
                'comment' => '订单付款后会有30分钟的延迟，请控制好抓取时间范围;筛选时间依据是付款时间',
                //接口请求配置：method代表方法名，curl_type代表请求发送方式:0代表post,1代表get;para_is_json代表参数数组是否需要转换为json格式:0代表不需要，1代表需要
                'request' => array("method"=>"order_list","curl_type"=>0,"para_is_json"=>0),
                'form' => array(
                    //type:1为int,2为string；
                    //item_type:1为时间控件,0为普通input框,2为下拉选框;
                    //is_null：1为可选项，0为必选项
                    array('label'=>'开始时间','item_type'=>1,'name'=>'start_time','is_null'=>1,'type'=>2,'comment'=>''),
                    array('label'=>'结束时间','item_type'=>1,'name'=>'end_time','is_null'=>1,'type'=>2,'comment'=>''),
                    array('label'=>'订单状态','item_type'=>0,'name'=>'jOrderStatus','is_null'=>0,'type'=>1,'comment'=>'(订单状态，2:等待发货,3:已发货,5:交易成功,6:交易已关闭, 默认值为2 多个用,隔开(\',\' 需转义为%2C，否则签名不过))'),
                    array('label'=>'订单页码','item_type'=>0,'name'=>'jPage','is_null'=>0,'type'=>1,'comment'=>'(列表页码 默认值：1)'),
                    array('label'=>'单页大小','item_type'=>0,'name'=>'jPageSize','is_null'=>0,'type'=>1,'comment'=>'(每页显示数量 默认值：100)'),
                )
            ),
            'getOrderDetail'=>array(
                'comment'=>'',
                //接口请求配置：method代表方法名，curl_type代表请求发送方式:0代表post,1代表get;para_is_json代表参数数组是否需要转换为json格式:0代表不需要，1代表需要
                'request' =>array("method"=>"order_info","curl_type"=>0,"para_is_json"=>0),
                'form'=>array(
                    array('label'=>'订单单号','item_type'=>0,'name'=>'jOrderNo','is_null'=>0,'type'=>2,'comment'=>""),
                )
            ),
            'pushShip'=>array(
                'comment'=>'',
                //接口请求配置：method代表方法名，curl_type代表请求发送方式:0代表post,1代表get;para_is_json代表参数数组是否需要转换为json格式:0代表不需要，1代表需要
                'request' => array("method"=>"send_goods","curl_type"=>0,"para_is_json"=>0),
                'form'=>array(
                    array('label'=>'订单单号','item_type'=>0,'name'=>'jOrderNo','is_null'=>0,'type'=>2,'comment'=>""),
                    array('label'=>'物流公司','item_type'=>0,'name'=>'jDeliverEname','is_null'=>0,'type'=>2,'comment'=>"(目前支持：EMS经济快递,凡宇快递,联昊通,全一快递,城市100,速尔快递,圆通速递,中通快递,飞远配送,宅急送,韵达快递,天天快递,百世快递,联邦快递,德邦物流,中铁快运,中铁物流,信丰物流,顺丰速运,申通快递,快捷快递,新邦物流,佳吉快运,能达速递,优速物流,增益速递,CCES,邮政快递包裹,京广快递,UPS快递,亚风快递,大田快运,DHL代理,国际快递查询,安信达快递,越丰物流,香港进口,一统快递,一邦速递,国通快递,飞康达快运,赛澳递,全日通快递,运通中港物流,加运美速递,澳邮快运,EWE全球快递,佳怡物流,速通物流,源安达,百世物流,日日顺物流,鸿泰物流,腾达物流,宇鑫物流,平安达腾飞,如风达,恒路,万象物流,EMS标准快递,递四方,汇通天下物流,China Post(中国邮政),PCA Express（PCA快递）,鹰运国际速递,安能物流,安能物流)"),
                    array('label'=>'物流单号','item_type'=>0,'name'=>'jDeliverNo','is_null'=>0,'type'=>2,'comment'=>""),
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
        $after=array('request'=>array('url'=>$this->_host.'/erpapi/index','curl_type'=>$param['curl_type'],'para_is_json'=>$param['para_is_json']));
        foreach ($param as $k=>$v){
            $k=='method'&&$param['jType']=$v;
            if($k=="start_time"&&!empty($param['create']))$param['create']=strtotime($v);
            if($k=="end_time"&&!empty($param['create']))$param['create'].='|'.strtotime($v);
            if(in_array($k,array('curl_type','para_is_json','channel','api',"start_time","end_time",'method'))){
                unset($param[$k]);
            }
            if($k=="method"&&strpos($v,'send_goods')!==false){
                $logi = isset($this->express[$param['jDeliverEname']]) ? $this->express[$param['jDeliverEname']] : $this->express['EMS'];
                $param['jDeliverEname']=$logi['code'];
                $param['jDeliverCname']=$logi['companyname'];
            }
        }
        $param['jCusKey'] = $this->_cus;
        $param['token'] = $this->_getToken();
        $sign = $this->_genSign($param);
        $param['sign'] = $sign;
        $after['param']=$param;
        return $after;
    }
    private function _genSign($param)
    {
        ksort($param);
        $param['code'] = $this->_secret;
        $httpParam = http_build_query($param);
        return md5($httpParam);
    }
    private function _getToken()
    {
        $param = array(
            'secret' => $this->_key,
            'scope' => $this->_scope,
            'type' => 'json'
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->_host.'/erpapi/authorize');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_3) AppleWebKit/537.36 (KHTML, like Gecko)');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $res = curl_exec($ch);
        curl_close($ch);
        $res=json_decode($res,true);
        return $res['data']['token'];
    }
    public $express = array(
        "EMS" => array(
            "id" => "6",
            "companyname" => "EMS经济快递",
            "code" => "ems",
            "comurl" => "http://www.ems.com.cn/",
            "comtel" => "11185",
            "rule" => "^\\w[13)$",
            "source" => "1"
        ),
        "fanyukuaidi" => array(
            "id" => "7",
            "companyname" => "凡宇快递",
            "code" => "fanyukuaidi",
            "comurl" => "http://www.fanyu56.com.cn/",
            "comtel" => "4006-580-358",
            "rule" => "^[0-9a-zA-Z)[1,)$",
            "source" => "0"
        ),
        "lianhaowuliu" => array(
            "id" => "8",
            "companyname" => "联昊通",
            "code" => "lianhaowuliu",
            "comurl" => "http://www.lhtex.com.cn",
            "comtel" => "0769-88620000",
            "rule" => "^[0-9a-zA-Z)[1,)$",
            "source" => "2"
        ),
        "quanyikuaidi" => array(
            "id" => "10",
            "companyname" => "全一快递",
            "code" => "quanyikuaidi",
            "comurl" => "http://www.unitop-apex.com/",
            "comtel" => "400-663-1111",
            "rule" => "^[0-9a-zA-Z)[1,)$",
            "source" => "2"
        ),
        "city100" => array(
            "id" => "11",
            "companyname" => "城市100",
            "code" => "city100",
            "comurl" => "",
            "comtel" => "010-52932760",
            "rule" => "^[0-9a-zA-Z)[1,)$",
            "source" => "2"
        ),
        "suer" => array(
            "id" => "12",
            "companyname" => "速尔快递",
            "code" => "suer",
            "comurl" => "http://www.sure56.com",
            "comtel" => "400-158-9888",
            "rule" => "^[0-9a-zA-Z)[1,)$",
            "source" => "2"
        ),
        "圆通快递" => array(
            "id" => "13",
            "companyname" => "圆通速递",
            "code" => "yuantong",
            "comurl" => "http://www.yto.net.cn",
            "comtel" => "95554",
            "rule" => "^[0-9a-zA-Z)[1,)$",
            "source" => "1"
        ),
        "中通快递" => array(
            "id" => "14",
            "companyname" => "中通快递",
            "code" => "zhongtong",
            "comurl" => "http://www.zto.cn",
            "comtel" => "95311",
            "rule" => "^\\d[12,13)$",
            "source" => "1"
        ),
        "feiyuanvipshop" => array(
            "id" => "15",
            "companyname" => "飞远配送",
            "code" => "feiyuanvipshop",
            "comurl" => "http://www.fyps.cn/",
            "comtel" => "400-703-1313",
            "rule" => "^[0-9a-zA-Z)[1,)$",
            "source" => "0"
        ),
        "宅急送" => array(
            "id" => "16",
            "companyname" => "宅急送",
            "code" => "zhaijisong",
            "comurl" => "http://www.zjs.com.cn",
            "comtel" => "400-6789-000",
            "rule" => "^([\\w)[13)|[\\w)[10))$",
            "source" => "1"
        ),
        "韵达快递" => array(
            "id" => "17",
            "companyname" => "韵达快递",
            "code" => "yunda",
            "comurl" => "http://www.yundaex.com",
            "comtel" => "95546",
            "rule" => "^\\d[13)$",
            "source" => "1"
        ),
        "天天快递" => array(
            "id" => "18",
            "companyname" => "天天快递",
            "code" => "tiantian",
            "comurl" => "http://www.ttkdex.com",
            "comtel" => "400-188-8888",
            "rule" => "^\\w[12)$",
            "source" => "2"
        ),
        "百世汇通" => array(
            "id" => "20",
            "companyname" => "百世快递",
            "code" => "huitongkuaidi",
            "comurl" => "http://www.800bestex.com",
            "comtel" => "400-956-5656",
            "rule" => "^\\w[12,14)$",
            "source" => "1"
        ),
        "lianbangkuaidi" => array(
            "id" => "21",
            "companyname" => "联邦快递",
            "code" => "lianbangkuaidi",
            "comurl" => "http://cndxp.apac.fedex.com/dxp.html",
            "comtel" => "400-889-1888",
            "rule" => "^[0-9a-zA-Z)[1,)$",
            "source" => "2"
        ),
        "德邦物流" => array(
            "id" => "22",
            "companyname" => "德邦物流",
            "code" => "debangwuliu",
            "comurl" => "http://www.deppon.com",
            "comtel" => "95353",
            "rule" => "^[0-9a-zA-Z)[1,)$",
            "source" => "2"
        ),
        "zhongtiewuliu" => array(
            "id" => "23",
            "companyname" => "中铁快运",
            "code" => "zhongtiewuliu",
            "comurl" => "http://www.cre.cn",
            "comtel" => "95572",
            "rule" => "^[0-9a-zA-Z)[1,)$",
            "source" => "0"
        ),
        "ztky" => array(
            "id" => "24",
            "companyname" => "中铁物流",
            "code" => "ztky",
            "comurl" => "http://www.ztky.com",
            "comtel" => "400-000-5566",
            "rule" => "^[0-9a-zA-Z)[1,)$",
            "source" => "0"
        ),
        "xinfengwuliu" => array(
            "id" => "25",
            "companyname" => "信丰物流",
            "code" => "xinfengwuliu",
            "comurl" => "http://www.xf-express.com.cn",
            "comtel" => "400-830-6333",
            "rule" => "^[0-9a-zA-Z)[1,)$",
            "source" => "2"
        ),
        "顺丰快递" => array(
            "id" => "26",
            "companyname" => "顺丰速运",
            "code" => "shunfeng",
            "comurl" => "http://www.sf-express.com",
            "comtel" => "95338",
            "rule" => "^\\d[12)$",
            "source" => "1"
        ),
        "申通快递" => array(
            "id" => "27",
            "companyname" => "申通快递",
            "code" => "shentong",
            "comurl" => "http://www.sto.cn",
            "comtel" => "95543",
            "rule" => "^\\d[12,13)$",
            "source" => "1"
        ),
        "快捷速递" => array(
            "id" => "30",
            "companyname" => "快捷快递",
            "code" => "kuaijiesudi",
            "comurl" => "http://www.fastexpress.com.cn/",
            "comtel" => "400-830-4888",
            "rule" => "^\\d[12,14)$",
            "source" => "1"
        ),
        "xinbangwuliu" => array(
            "id" => "31",
            "companyname" => "新邦物流",
            "code" => "xinbangwuliu",
            "comurl" => "http://www.xbwl.cn",
            "comtel" => "4008-000-222",
            "rule" => "^[0-9a-zA-Z)[1,)$",
            "source" => "0"
        ),
        "jiajiwuliu" => array(
            "id" => "33",
            "companyname" => "佳吉快运",
            "code" => "jiajiwuliu",
            "comurl" => "http://www.jiaji.com/",
            "comtel" => "400-820-5566",
            "rule" => "^[0-9a-zA-Z)[1,)$",
            "source" => "2"
        ),
        "ganzhongnengda" => array(
            "id" => "34",
            "companyname" => "能达速递",
            "code" => "ganzhongnengda",
            "comurl" => "http://www.nengdaex.com",
            "comtel" => "400-6886-765",
            "rule" => "^[0-9a-zA-Z)[1,)$",
            "source" => "2"
        ),
        "优速快递" => array(
            "id" => "35",
            "companyname" => "优速物流",
            "code" => "youshuwuliu",
            "comurl" => "http://www.uc56.com",
            "comtel" => "400-1111-119",
            "rule" => "^[0-9a-zA-Z)[1,)$",
            "source" => "1"
        ),
        "zengyisudi" => array(
            "id" => "36",
            "companyname" => "增益速递",
            "code" => "zengyisudi",
            "comurl" => "http://www.zeny-express.com/",
            "comtel" => "4008-456-789",
            "rule" => "^[0-9a-zA-Z)[1,)$",
            "source" => "2"
        ),
        "cces" => array(
            "id" => "37",
            "companyname" => "CCES",
            "code" => "cces",
            "comurl" => "http://www.gto365.com",
            "comtel" => "400-111-1123",
            "rule" => "",
            "source" => "1"
        ),
        "邮政包裹" => array(
            "id" => "38",
            "companyname" => "邮政快递包裹",
            "code" => "youzhengguonei",
            "comurl" => "http://yjcx.chinapost.com.cn",
            "comtel" => "11183",
            "rule" => "^[0-9a-zA-Z)[1,)$",
            "source" => "1"
        ),
        "jinguangsudikuaijian" => array(
            "id" => "39",
            "companyname" => "京广快递",
            "code" => "jinguangsudikuaijian",
            "comurl" => "http://www.szkke.com/",
            "comtel" => "0769-88629888",
            "rule" => "^[0-9a-zA-Z)[1,)$",
            "source" => "0"
        ),
        "UPS" => array(
            "id" => "40",
            "companyname" => "UPS快递",
            "code" => "UPS",
            "comurl" => "http://www.ups.com/cn",
            "comtel" => "400-820-8388",
            "rule" => "^[0-9a-zA-Z)[1,)$",
            "source" => "0"
        ),
        "yafengsudi" => array(
            "id" => "41",
            "companyname" => "亚风快递",
            "code" => "yafengsudi",
            "comurl" => "http://www.airfex.net/",
            "comtel" => "4001-000-002",
            "rule" => "^[0-9a-zA-Z)[1,)$",
            "source" => "0"
        ),
        "datianwuliu" => array(
            "id" => "42",
            "companyname" => "大田快运",
            "code" => "datianwuliu",
            "comurl" => "http://www.dtw.com.cn/",
            "comtel" => "400-626-1166",
            "rule" => "^[0-9a-zA-Z)[1,)$",
            "source" => "0"
        ),
        "dhl" => array(
            "id" => "43",
            "companyname" => "DHL代理",
            "code" => "dhl",
            "comurl" => "http://www.cn.dhl.com/",
            "comtel" => "",
            "rule" => "^[0-9a-zA-Z)[1,)$",
            "source" => "0"
        ),
        "youzhengguoji" => array(
            "id" => "44",
            "companyname" => "国际快递查询",
            "code" => "youzhengguoji",
            "comurl" => "http://www.ems.com.cn/",
            "comtel" => "",
            "rule" => "^[0-9a-zA-Z)[1,)$",
            "source" => "0"
        ),
        "anxindakuaixi" => array(
            "id" => "45",
            "companyname" => "安信达快递",
            "code" => "anxindakuaixi",
            "comurl" => "http://www.anxinda.com/",
            "comtel" => "400-626-2356",
            "rule" => "^[0-9a-zA-Z)[1,)$",
            "source" => "0"
        ),
        "yuefengwuliu" => array(
            "id" => "46",
            "companyname" => "越丰物流",
            "code" => "yuefengwuliu",
            "comurl" => "http://www.yfexpress.com.hk",
            "comtel" => "00852-23909969",
            "rule" => "^[0-9a-zA-Z)[1,)$",
            "source" => "0"
        ),
        "hkpost" => array(
            "id" => "47",
            "companyname" => "香港进口",
            "code" => "hkpost",
            "comurl" => "http://www.xianggangjinkou.com/",
            "comtel" => "400-086-0002",
            "rule" => "^[0-9a-zA-Z)[1,)$",
            "source" => "0"
        ),
        "yitongfeihong" => array(
            "id" => "48",
            "companyname" => "一统快递",
            "code" => "yitongfeihong",
            "comurl" => "http://yitongfeihong.com",
            "comtel" => "",
            "rule" => "^[0-9a-zA-Z)[1,)$",
            "source" => "0"
        ),
        "yibangwuliu" => array(
            "id" => "49",
            "companyname" => "一邦速递",
            "code" => "yibangwuliu",
            "comurl" => "http://www.ebon-express.com",
            "comtel" => "18688486668",
            "rule" => "^[0-9a-zA-Z)[1,)$",
            "source" => "0"
        ),
        "国通快递" => array(
            "id" => "50",
            "companyname" => "国通快递",
            "code" => "guotongkuaidi",
            "comurl" => "http://www.gto365.com/",
            "comtel" => "400-111-1123",
            "rule" => "^[0-9a-zA-Z)[1,)$",
            "source" => "1"
        ),
        "Feikangda" => array(
            "id" => "51",
            "companyname" => "飞康达快运",
            "code" => "Feikangda",
            "comurl" => "",
            "comtel" => "010-84223376",
            "rule" => "^[0-9a-zA-Z)[1,)$",
            "source" => "2"
        ),
        "saiaodi" => array(
            "id" => "52",
            "companyname" => "赛澳递",
            "code" => "saiaodi",
            "comurl" => "http://www.51cod.com/",
            "comtel" => "400-034-5888",
            "rule" => "^[0-9a-zA-Z)[1,)$",
            "source" => "2"
        ),
        "quanritongkuaidi" => array(
            "id" => "53",
            "companyname" => "全日通快递",
            "code" => "quanritongkuaidi",
            "comurl" => "http://www.at-express.com/",
            "comtel" => "020-86298988",
            "rule" => "^[0-9a-zA-Z)[1,)$",
            "source" => "0"
        ),
        "yuntongkuaidi" => array(
            "id" => "54",
            "companyname" => "运通中港物流",
            "code" => "yuntongkuaidi",
            "comurl" => "http://www.ytkd168.com/",
            "comtel" => "0769-81156999",
            "rule" => "^[0-9a-zA-Z)[1,)$",
            "source" => "0"
        ),
        "jiayunmeiwuliu" => array(
            "id" => "55",
            "companyname" => "加运美速递",
            "code" => "jiayunmeiwuliu",
            "comurl" => "http://www.tms56.com/",
            "comtel" => "0769-85515555",
            "rule" => "^[0-9a-zA-Z)[1,)$",
            "source" => "0"
        ),
        "auspost" => array(
            "id" => "57",
            "companyname" => "澳邮快运",
            "code" => "auspost",
            "comurl" => "http://www.auexpress.com.au/",
            "comtel" => "130-007-9988",
            "rule" => "^[0-9a-zA-Z)[1,)$",
            "source" => "0"
        ),
        "emsguoji" => array(
            "id" => "58",
            "companyname" => "EWE全球快递",
            "code" => "emsguoji",
            "comurl" => "https://www.everfast.com.au/",
            "comtel" => "1300096655",
            "rule" => "^[0-9a-zA-Z)[1,)$",
            "source" => "0"
        ),
        "jiayiwuliu" => array(
            "id" => "59",
            "companyname" => "佳怡物流",
            "code" => "jiayiwuliu",
            "comurl" => "http://www.jiayi56.com/",
            "comtel" => "400-631-9999",
            "rule" => "^[0-9a-zA-Z)[1,)$",
            "source" => "0"
        ),
        "sutongwuliu" => array(
            "id" => "60",
            "companyname" => "速通物流",
            "code" => "sutongwuliu",
            "comurl" => "http://www.sut56.com/",
            "comtel" => "4006561185",
            "rule" => "^[0-9a-zA-Z)[1,)$",
            "source" => "0"
        ),
        "yuananda" => array(
            "id" => "61",
            "companyname" => "源安达",
            "code" => "yuananda",
            "comurl" => "http://www.yadex.com.cn/",
            "comtel" => "0769-85021875",
            "rule" => "^[0-9a-zA-Z)[1,)$",
            "source" => "0"
        ),
        "baishiwuliu" => array(
            "id" => "62",
            "companyname" => "百世物流",
            "code" => "baishiwuliu",
            "comurl" => "http://www.800best.com",
            "comtel" => "4008856561",
            "rule" => "^[0-9a-zA-Z)[1,)$",
            "source" => "0"
        ),
        "ririshunwuliu" => array(
            "id" => "63",
            "companyname" => "日日顺物流",
            "code" => "ririshunwuliu",
            "comurl" => "http://www.rrs.com/wl/",
            "comtel" => "4009999999",
            "rule" => "^[0-9a-zA-Z)[1,)$",
            "source" => "1"
        ),
        "hongtaiwuliu" => array(
            "id" => "64",
            "companyname" => "鸿泰物流",
            "code" => "hongtaiwuliu",
            "comurl" => "http://www.hnht56.com/index.html",
            "comtel" => "4008607777",
            "rule" => "^[0-9a-zA-Z)[1,)$",
            "source" => "1"
        ),
        "tengdawuliu" => array(
            "id" => "65",
            "companyname" => "腾达物流",
            "code" => "tengdawuliu",
            "comurl" => "http://www.tengdawl.com/",
            "comtel" => "4006337777",
            "rule" => "^[0-9a-zA-Z)[1,)$",
            "source" => "1"
        ),
        "yuxingwuliu" => array(
            "id" => "66",
            "companyname" => "宇鑫物流",
            "code" => "yuxingwuliu",
            "comurl" => "http://www.yx56.cn/",
            "comtel" => "4006005566",
            "rule" => "^[0-9a-zA-Z)[1,)$",
            "source" => "1"
        ),
        "pingandatengfei" => array(
            "id" => "67",
            "companyname" => "平安达腾飞",
            "code" => "pingandatengfei",
            "comurl" => "http://www.padtf.com/",
            "comtel" => "4009990988",
            "rule" => "^[0-9a-zA-Z)[1,)$",
            "source" => "0"
        ),
        "rufengda" => array(
            "id" => "69",
            "companyname" => "如风达",
            "code" => "rufengda",
            "comurl" => "http://www.rufengda.com/",
            "comtel" => "4000106660",
            "rule" => "^[0-9a-zA-Z)[1,)$",
            "source" => "0"
        ),
        "hengluwuliu" => array(
            "id" => "70",
            "companyname" => "恒路",
            "code" => "hengluwuliu",
            "comurl" => "http://www.e-henglu.com/",
            "comtel" => "4001826666",
            "rule" => "^([\\w)[13)|[\\w)[10))$",
            "source" => "0"
        ),
        "wanxiangwuliu" => array(
            "id" => "71",
            "companyname" => "万象物流",
            "code" => "wanxiangwuliu",
            "comurl" => "http://www.ewinshine.com/",
            "comtel" => "4008208088",
            "rule" => "^[\\d\\w\\-)[1,)$",
            "source" => "2"
        ),
        "emsbiaozhun" => array(
            "id" => "72",
            "companyname" => "EMS标准快递",
            "code" => "emsbiaozhun",
            "comurl" => "http://www.ems.com.cn/index.html",
            "comtel" => "11183",
            "rule" => "",
            "source" => "1"
        ),
        "disifang" => array(
            "id" => "73",
            "companyname" => "递四方",
            "code" => "disifang",
            "comurl" => "http://www.4px.com/",
            "comtel" => "0755-23508000",
            "rule" => "",
            "source" => "0"
        ),
        "汇通速递" => array(
            "id" => "74",
            "companyname" => "汇通天下物流",
            "code" => "httx56",
            "comurl" => "http://www.httx56.com/",
            "comtel" => "0755-21636332",
            "rule" => "",
            "source" => "0"
        ),
        "chinapost" => array(
            "id" => "75",
            "companyname" => "China Post(中国邮政)",
            "code" => "chinapost",
            "comurl" => "http://yjcx.chinapost.com.cn/zdxt/yjcx/",
            "comtel" => "11183",
            "rule" => "",
            "source" => "1"
        ),
        "pcaexpress" => array(
            "id" => "76",
            "companyname" => "PCA Express（PCA快递）",
            "code" => "pcaexpress",
            "comurl" => "http://www.pcaexpress.com.au/",
            "comtel" => "1800518000",
            "rule" => "",
            "source" => "0"
        ),
        "vipexpress" => array(
            "id" => "77",
            "companyname" => "鹰运国际速递",
            "code" => "vipexpress",
            "comurl" => "http://www.vip-express.com.au/",
            "comtel" => "0862614860",
            "rule" => "",
            "source" => "0"
        ),
        "anneng"=>array(
            "id"=> "32",
            "companyname"=>"安能物流",
            "companypinyin"=>"annengwuliu",
            "code"=>"annengwuliu",
            "comtel"=> "400-104-0088",
            "comurl"=> "",
            "rule" => "^[0-9a-zA-Z)[1,)$",
            "source"=>"0"
        ),
        "安能物流"=>array(
            "id"=> "32",
            "companyname"=>"安能物流",
            "companypinyin"=>"annengwuliu",
            "code"=>"annengwuliu",
            "comtel"=> "400-104-0088",
            "comurl"=> "",
            "rule" => "^[0-9a-zA-Z)[1,)$",
            "source"=>"0"
        )
    );
}