<?php
//sap接口相关配置
//只允许105服务器，向正式sap环境推送数据
if ($_SERVER['HTTP_HOST'] == '192.168.11.113') {
    $api_host =  'http://61.183.247.113:10018';//正式环境
} else {
    $api_host =  'http://192.168.11.73';//测试环境
}


return array(
    //SAP提供的接口地址
    'api' => array(
        //'host' => 'http://192.168.11.72:8022',//测试环境
        'host' => $api_host,//正式环境
        'sap101' => '/hgw-sap/items/add',
        'sap102' => '/hgw-sap/items/update',
        'sap201' => '/hgw-sap/business_partners/add',
        'sap202' => '/hgw-sap/business_partners/update',
        'sap301' => '/hgw-sap/invoices/add',
        'sap401' => '/hgw-sap/payment/add',
        'sap402' => '/hgw-sap/payment/add_refund',
    	'sap404' => '/hgw-sap/credit_note/add',
    	'sap405' => '/hgw-sap/purchase/return', //店铺扣款
    	'sap501' => '/hgw-sap/apreview/purchase/invoice/add', 
    	'sap502' => '/hgw-sap/apreview/purchase/credit_note/add', //应付-应付贷项凭证接口
    	'sap503' => '/hgw-sap/apreview/purchase/done', //结算单推送结束通知
    	'sap601' => '/hgw-sap/invoices/writeoff', //错误修正，应收发票
    	'sap603' => '/hgw-sap/credit_note/cancel',
    	'sap701' => '/hgw-sap/prepaidCard/dealMessages'  //汉购卡相关接口
    ),
    //code map 对应service/sap里的文件及方法
    'method' => array(
        'sap101' => 'goods.add',//添加商品到SAP
        'sap102' => 'goods.edit',//更新商品
        'sap201' => 'store.add',//添加商家到SAP
        'sap202' => 'store.edit',//更新商家
        'sap301' => 'order.add',//(已收货的订单)交货单
        'sap401' => 'payment.make',//收款
        'sap402' => 'payment.refund',//退款
        'sap403' => 'payment.end_refund',//完结退款
    	'sap404' => 'payment.credit',//退款应收贷项凭证
    	'sap405' => 'payment.storecost',//应付-采购订单接口
//    	'sap406' => 'payment.check',//退款比对
//    	'sap407' => 'payment.check_result',//退款比对结果
    	'sap501' => 'purchase.order',//应付-采购订单接口
    	'sap502' => 'purchase.refund',//应付-采购订单接口
    	'sap406' => 'purchase.refund_check',//应付-采购订单接口
    	'sap407' => 'purchase.refund_check_result',//应付-采购订单接口
    	'sap408' => 'purchase.refund_ids',//退款tid列表
    	'sap503' => 'purchase.done',//结算单推送结束通知
    	'sap504' => 'purchase.check',//自动比对订单
    	'sap505' => 'purchase.check_result',//自动比对订单返回结果处理
    	'sap506' => 'purchase.check_pre_sum',//预先结果
    	'sap507' => 'purchase.order_ids',//订单tid列表
    	'sap508' => 'purchase.order_reset',//订单tid列表
        'sap509' => 'purchase.generate_result',//手动触发结算
    	'sap601' => 'writeoff.order',//错误修正，应收发票
//    	'sap602' => 'writeoff.purchase', //账单审核打回，callback
    	'sap603' => 'writeoff.refund',//错误修正退款
    	'sap701' => 'prepaidCard.deal',//汉购卡相关接口
    ),
    //其它设置内容
    'setting' => array(
        'log' => true,//日志开关
        'limit' => array( //设置每次推送数据条数
            'default' => 100,//默认
            'sap301' => 150,
            'sap401' => 200,
            'sap406' => 200,
        	'sap501' => 10,    //结算账单数
            'sap501_item' => 100,    //每个结算账单取记录数
        	'sap502' => 50,
            'sap502_item' => 100,    //每个结算账单取记录数
        	'sap404' => 100
        ),
        'notice' => array(//通知信息设置
            'send' => true,//发送开关
            'email' => '',//多个用 , 隔开
        ),
    ),
);
