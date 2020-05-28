<?php
//sap接口相关配置
//只允许105服务器，向正式sap环境推送数据
if ($_SERVER['HTTP_HOST'] == '192.168.11.113') {
    $api_host =  'http://b1.hansap.com/hgwb2b';//正式环境
} else {
//    $api_host =  'http://192.168.11.126:10018';//测试环境
    $api_host =  'http://172.16.90.113:9101';//测试环境
}


return array(
    //SAP提供的接口地址
    'api' => array(
        'host' => $api_host,//正式环境
        'sapb2b101' => '/items/add',
        'sapb2b102' => '/items/update',
        'sapb2b201' => '/business_partners/add',
        'sapb2b202' => '/business_partners/update',
        'sapb2b301' => '/invoices/add',
        'sapb2b401' => '/payment/add',
//    	'sapb2b501' => '/hgw-sap/apreview/purchase/invoice/add'
    ),
    //code map 对应service/sap里的文件及方法
    'method' => array(
        'sapb2b101' => 'goods.add',//添加商品到SAP
        'sapb2b102' => 'goods.edit',//更新商品
        'sapb2b201' => 'supplier.add',//添加商家到SAP
        'sapb2b202' => 'supplier.edit',//更新商家
        'sapb2b301' => 'order.add',//(已收货的订单)交货单
        'sapb2b401' => 'payment.make',//收款
//    	'sapb2b501' => 'purchase.order',//应付-采购订单接口
    ),
    //其它设置内容
    'setting' => array(
        'log' => true,//日志开关
        'limit' => array( //设置每次推送数据条数
            'default' => 100,//默认
            'sapb2b301' => 150,
            'sapb2b401' => 200,
            'sapb2b406' => 200,
        	'sapb2b501' => 10,    //结算账单数
            'sapb2b501_item' => 100,    //每个结算账单取记录数
        	'sapb2b502' => 50,
            'sapb2b502_item' => 100,    //每个结算账单取记录数
        	'sapb2b404' => 100
        ),
        'notice' => array(//通知信息设置
            'send' => true,//发送开关
            'email' => '',//多个用 , 隔开
        ),
    ),
);
