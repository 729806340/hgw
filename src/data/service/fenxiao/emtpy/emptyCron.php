<?php

class emptyCron
{
    
	static $uCode = 8;
	static $Secret = '';
	static $source = "" ;
	static $onlineDate = "2016-10-09 00:00:00" ; //上线日期，不保存上线日之前的订单
	//在拼多多平台添加了映射的物流名称
	static $logicNames = array(
		'邮政包裹','申通快递','圆通速递','顺丰速运','韵达快递',
		'中通速递','宅急送','天天快递','龙邦快递','汇通速递',
		'快捷速递','德邦物流','全峰快递','国通快递','优速快递',
		'中通快递','韵达速递','圆通快递','EMS','顺丰速递','优速物流','快捷快递'
	);
	
	function __construct( $getRel = 1 ){
    }
    
    //老平台商品映射
    function getOldGoodsRel()
    {return array();
    	$result = ecModel("B2cCategory") -> where ( array('uid' => $this->member_id) ) -> select () ;
    	$rel = $result ? array_column($result, 'pid', 'fxpid') : array() ;
    	return $rel ;
    }
    
    /** 模拟登录方式获取订单 **/
    function orderlist( $params = array() )
    {
    }
    
    public function push_ship( $params = array() )
    {
        return ;
    }
    
    //获取商品映射
    function getGoodsRel()
    {
        return ;
    }
}