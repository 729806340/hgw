<?php
/**
 * Created by CharlesChen
 * Date: 2018/1/25
 * Time: 14:41
 * File name:beibeiwangConf.php
 */
require_once('Common.php');

class jingdongfxConf extends Common
{
    private $appKey = 'D96E0865C30413538996D597A0BAC915';
    private $appSecret = '34faad9485c54cf09655b60a3e0c6c65';
    private $grant_type = 'authorization_code';
    private $response_type = 'code';
    private $url = 'https://api.jd.com/routerjson';
    private $access_token = '3a0d5c3a-d5bb-4fe6-ba2a-291df8a769f2';
    public function getConf()
    {
        return array(
            'orderlist' => array(
                //用于统一时间控件名
                "startTime" => 'start_date',
                "endTime" => 'end_date',
                'comment' => '开始时间和结束时间，不得相差超过1个月,开始时间和结束时间均为空，默认返回前一个月的订单',
                //接口请求配置：method代表方法名，curl_type代表请求发送方式:0代表post,1代表get;para_is_json代表参数数组是否需要转换为json格式:0代表不需要，1代表需要
                'request' => array("method" => "360buy.order.search", "curl_type" => 1, "para_is_json" => 0),
                'form' => array(
                    //type:1为int,2为string；
                    //item_type:1为时间控件,0为普通input框,2为下拉选框;
                    //is_null：1为可选项，0为必选项
                    array('label' => '开始时间', 'item_type' => 1, 'name' => 'start_date', 'is_null' => 1, 'type' => 2, 'comment' => ''),
                    array('label' => '结束时间', 'item_type' => 1, 'name' => 'end_date', 'is_null' => 1, 'type' => 2, 'comment' => ''),
                    array('label' => '订单状态', 'item_type' => 0, 'name' => 'order_state', 'is_null' => 0, 'type' => 1, 'comment' => '(多订单状态可以用英文逗号隔开 1）WAIT_SELLER_STOCK_OUT 等待出库 2）SEND_TO_DISTRIBUTION_CENER 发往配送中心（只适用于LBP，SOPL商家） 3）DISTRIBUTION_CENTER_RECEIVED 配送中心已收货（只适用于LBP，SOPL商家） 4）WAIT_GOODS_RECEIVE_CONFIRM 等待确认收货 5）RECEIPTS_CONFIRM 收款确认（服务完成）（只适用于LBP，SOPL商家） 6）WAIT_SELLER_DELIVERY等待发货（只适用于海外购商家，等待境内发货 标签下的订单） 7）FINISHED_L 完成 8）TRADE_CANCELED 取消 9）LOCKED 已锁定 )'),
                    array('label' => '订单页码', 'item_type' => 0, 'name' => 'page', 'is_null' => 0, 'type' => 1, 'comment' => '(列表页码 默认值：1)'),
                    array('label' => '单页大小', 'item_type' => 0, 'name' => 'page_size', 'is_null' => 0, 'type' => 1, 'comment' => '(每页显示数量 最大100条)'),
                )
            ),
            'getOrderDetail' => array(
                'comment' => '',
                //接口请求配置：method代表方法名，curl_type代表请求发送方式:0代表post,1代表get;para_is_json代表参数数组是否需要转换为json格式:0代表不需要，1代表需要
                'request' => array("method" => "360buy.order.get", "curl_type" => 1, "para_is_json" => 0),
                'form' => array(
                    array('label' => '订单单号', 'item_type' => 0, 'name' => 'order_id', 'is_null' => 0, 'type' => 2, 'comment' => "(输入单个订单id，得到所有相关订单信息)"),
                )
            ),
            'getSku' => array(
                'comment' => '商品sku为在售商品',
                //接口请求配置：method代表方法名，curl_type代表请求发送方式:0代表post,1代表get;para_is_json代表参数数组是否需要转换为json格式:0代表不需要，1代表需要
                'request' => array("method" => "jingdong.ware.read.searchWare4Valid", "curl_type" => 1, "para_is_json" => 0),
                'form' => array(
                    array('label' => '列表页码', 'item_type' => 0, 'name' => 'pageNo', 'is_null' => 1, 'type' => 1, 'comment' => '(列表页码 默认值：1)'),
                    array('label' => '单页大小', 'item_type' => 0, 'name' => 'pageSize', 'is_null' => 1, 'type' => 1, 'comment' => '(每页显示数量)'),
                )
            ),
            'getRefund' => array(
                //用于统一时间控件名
                "startTime" => 'applyTimeStart',
                "endTime" => 'applyTimeEnd ',
                'comment' => '筛选时间为售后单创建时间',
                //接口请求配置：method代表方法名，curl_type代表请求发送方式:0代表post,1代表get;para_is_json代表参数数组是否需要转换为json格式:0代表不需要，1代表需要
                'request' => array("method" => "jingdong.pop.afs.soa.refundapply.queryPageList", "curl_type" => 1, "para_is_json" => 0),
                'form' => array(
                    array('label' => '开始时间', 'item_type' => 1, 'name' => 'applyTimeStart', 'is_null' => 1, 'type' => 2, 'comment' => ''),
                    array('label' => '结束时间', 'item_type' => 1, 'name' => 'applyTimeEnd', 'is_null' => 1, 'type' => 2, 'comment' => ''),
                    array('label' => '订单状态', 'item_type' => 0, 'name' => 'status', 'is_null' => 1, 'type' => 1, 'comment' => '(退款申请单状态 0、未审核 1、审核通过2、审核不通过 3、京东财务审核通过 4、京东财务审核不通过 5、人工审核通过 6、拦截并退款 7、青龙拦截成功 8、青龙拦截失败 9、强制关单并退款 10、物流待跟进。不传是查询全部状态 )'),
                    array('label' => '订单页码', 'item_type' => 0, 'name' => 'pageIndex', 'is_null' => 0, 'type' => 1, 'comment' => '(显示多少页，区间为1-100)'),
                    array('label' => '单页大小', 'item_type' => 0, 'name' => 'pageSize ', 'is_null' => 0, 'type' => 1, 'comment' => '(每页显示数量:区间为1-50)'),
                )
            ),
            'pushShip' => array(
                'comment' => '',
                //接口请求配置：method代表方法名，curl_type代表请求发送方式:0代表post,1代表get;para_is_json代表参数数组是否需要转换为json格式:0代表不需要，1代表需要
                'request' => array("method" => "360buy.order.sop.outstorage", "curl_type" => 1, "para_is_json" => 0),
                'form' => array(
                    array('label' => '订单单号', 'item_type' => 0, 'name' => 'order_id', 'is_null' => 0, 'type' => 2, 'comment' => ""),
                    array('label' => '物流公司', 'item_type' => 0, 'name' => 'logistics_id', 'is_null' => 0, 'type' => 2, 'comment' => "(目前支持：安能物流,速尔快递,中铁快运,凡客如风达,德邦物流,天地华宇,佳吉快运,新邦物流,国通快递,挂号信,邮政包裹,全一快递,联邦快递,快捷速递,龙邦快递,全峰快递,优速快递,中通快递,宅急送,韵达快递,申通快递,顺丰快递,EMS,圆通快递)"),
                    array('label' => '物流单号', 'item_type' => 0, 'name' => 'waybill', 'is_null' => 0, 'type' => 2, 'comment' => ""),
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

    public function dealWithParam($param)
    {
        $after = array('request' => array('url' => $this->url, 'curl_type' => $param['curl_type'], 'para_is_json' => $param['para_is_json']));
        foreach ($param as $k => $v) {
            if (in_array($k, array('curl_type', 'para_is_json', 'channel', 'api'))) {
                unset($param[$k]);
            }
            if ($k == "method" && strpos($v, 'outstorage') !== false) {
                $param['logistics_id'] = $this->getShipId($param['logistics_id']);
            }
        }
        $header =array(
            'method'=>$param['method'],
            'access_token' => $this->access_token,
            'app_key' => $this->appKey,
            'timestamp' => date('Y-m-d H:i:s', time()),
            'format' => 'json',
            'v' => '2.0'
        );
        unset($param['method']);
        $post_data = array('360buy_param_json'=>json_encode((object)$param));
        $post_data = array_merge($header,$post_data);
        $post_data['sign'] = $this->generateSign($post_data);
        $after['param'] = $post_data;
        return $after;
    }
    /* 获取签名 @param array $param 请求参数数据集合*/
    protected function generateSign($params)
    {
        ksort($params);
        $stringToBeSigned = $this->appSecret;
        foreach ($params as $k => $v)
        {
            if("@" != substr($v, 0, 1))
            {
                $stringToBeSigned .= "$k$v";
            }
        }
        unset($k, $v);
        $stringToBeSigned .= $this->appSecret;
        return strtoupper(md5($stringToBeSigned));
    }
    public function getShipId($logi_name)
    {
        $data = array(
            '安能物流' => '4832',
            '速尔快递' => '2105',
            '中铁快运' => '466',
            '凡客如风达' => '313214',
            '德邦物流' => '2130',
            '天地华宇' => '2462',
            '佳吉快运' => '2460',
            '新邦物流' => '2461',
            '国通快递' => '2465',
            '挂号信' => '2171',
            '邮政包裹' => '2170',
            '全一快递' => '2100',
            '联邦快递' => '2096',
            '快捷速递' => '2094',
            '龙邦快递' => '471',
            '全峰快递' => '2016',
            '优速快递' => '1747',
            '中通快递' => '1499',
            '宅急送' => '1409',
            '韵达快递' => '1327',
            '申通快递' => '470',
            '顺丰快递' => '467',
            'EMS' => '465',
            '圆通快递' => '463'
        );
        return $data[$logi_name] ? $data[$logi_name] : $data['EMS'];
    }
}