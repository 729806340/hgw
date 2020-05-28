<?php

require_once 'flapi.php';

class fanliCron
{

	static $source = "fanli" ;
	
	function __construct( $getRel = 1 ){

		$model_member = TModel("Member") ;
		$conditon = array() ;
		$condition = array("member_name" => self::$source) ;
		$row = $model_member -> where( $condition ) -> find() ;
		$this->member_id = $row['member_id'] ;
		$model_member->execute("set wait_timeout=1000") ;
		
		//商品映射
		if( $getRel ) {
			$this -> rel = $this -> getGoodsRel() ;
			//$this -> oldRel = $this -> getOldGoodsRel() ;
		}
    }
	
	//获取商品映射
	function getGoodsRel()
	{
		$result = TModel("B2cCategory") -> where ( array('uid' => $this->member_id) ) -> select () ;
		$rel = $result ? array_column($result, 'pid', 'fxpid') : array() ;

		return $rel ;
	}

	function getSkuList($params = array()){
		$flapi = new flapi() ;
		$res1 = $flapi->getSkuList($params);
		$res = json_decode($res1,true);
		if($res['success'] == false) return false;
		$product_list = $res['result'];
		$data_out = array();
		foreach($product_list as $k => $v1){
			$productName =  $v1['productName'];
			$productCode =  $v1['productCode'];
			foreach($v1['skuItems'] as $v2){
				if(!empty($v2['skuName'])){
					$item['goods_name'] = $productName.'--'.$v2['skuName'];
				} else {
					$item['goods_name'] = $productName;
				}
				$item['source'] = 'fanli';
				if(!empty($v2['skuId'])) {
					$item['sku_id'] = $v2['skuId'];
				} else {
					$item['sku_id'] = $productCode;
				}
				$data_out[] = $item;
			}
		}
		return $data_out;
	}
	//获取萌店订单列表
	function orderlist( $params = array() ) 
	{
		$service = $params['service'] ;

		$beginTime = $_GET['begin'] ? strtotime($_GET['begin']) : TIMESTAMP - 7*24*3600;
		$endTime = $_GET['end'] ? strtotime($_GET['end']) : TIMESTAMP ;
		$page_no = 1;
		$page_size = 100;
		$flag = true ;

		$flapi = new flapi() ;

		while( $flag ) 
		{
			$condition = array();
			$condition['begin'] = $beginTime;
			$condition['end'] = $endTime ;
			$condition['page_size'] = $page_size;
			$condition['page_no'] = $page_no;
			$res = $flapi->getOrderList( $condition ) ;
			//v($res);
			if( empty($res[1]) ) die ;
			list( $hasNext, $order_list ) = $res ;
			$apiOrdernos = array_column($order_list, 'orderCode') ;
			log::selflog("fanli api：".count($order_list) . " | ". json_encode( $apiOrdernos ), self::$source) ;
	
			$savedOrderIds = $service -> getSavedidByApiorderno( $apiOrdernos ) ;
			log::selflog("saved orders:". date('Y-m-d H:i:s', $beginTime) ." - " . date('Y-m-d H:i:s', $endTime) . json_encode($savedOrderIds), self::$source) ;
		
			$order_data = array() ;
			foreach ( $order_list as $order ) {
				
				if( in_array($order['orderCode'], $savedOrderIds) ) continue ;
				
				$apiDetail = $flapi->getOrderDetail($order['orderCode'], $condition) ;
				$apiDetail = json_decode($apiDetail, true) ;
				if( $apiDetail['success'] && $apiDetail['responseCode'] == '00000' && !empty($apiDetail['result']) ){
					$order_data[] = $apiDetail['result'] ;
				}
			}
		//v($order_data);
			$datas = array() ;
			foreach($order_data as $order) 
			{
				$continue = 0;
				$order_detail = array();
				foreach ($order['orderItems'] as $k => $_item ) {
					$goods = array();
					if(!empty($_item['skuId'])){
						$fx_goods_id = $_item['skuId'];
					} else {
						$fx_goods_id = $_item['productCode'];
					}
					$goods['name'] = $_item['productName'];
					$goods['num'] = $_item['quantity'];
					$goods['price'] = $_item['originPrice'];
					$goods['fxpid'] = $fx_goods_id;
					$goods['oid'] = $_item['orderCode'];
					if( isset( $this -> rel[ $fx_goods_id ] ) && $this -> rel[ $fx_goods_id ] ) {
						$goods['goods_id'] = $this -> rel[ $fx_goods_id ] ;
						$goods['platform'] = 'new' ;
						$new++;
					} else {
						$this -> _error( $order['orderCode'], "分销商品 ".$goods['name']." id({$fx_goods_id}) 找不到对应的汉购商品" );
						$continue = 1 ;
					}
	
					$order_detail['item'][$k] = $goods;
				}
				if( $continue ) continue ;
	
				$order_detail['platform'] = 'new' ;
				$order_detail['order_sn'] = $order['orderCode'];
				$order_detail['buy_id'] = $this -> member_id ;
				$order_detail['receiver'] = $order['receiverName'];
				$order_detail['provine'] = $order['receiverProvince'];
				$order_detail['city'] = $order['receiverCity'];
				$order_detail['area'] = $order['receiverDistrict'];
				$order_detail['address'] = $order['receiverAddress'];
				$order_detail['mobile'] = $order['receiverMoblie'];
				$order_detail['remark'] = $order['remark'];
				$order_detail['amount'] = $order['orderPayment'];
				$order_detail['payment_code'] = 'fenxiao';
				$order_detail['order_time'] = strtotime( $order['paySuccessTime'] );
				$order_detail['shipping_fee']=$order['expressFee'];
				$datas[] = $order_detail ;
			}

			$service -> doCreateOrder( $datas ) ;
			
			if( $hasNext == 0 ) $flag = false ;
			
			$page_no++;
		}
	}
	
//保存错误信息到日志table
	function _error($orderno, $errorinfo, $log_type='order')
	{
		$model = Model("b2c_order_fenxiao_error");
		$where = array(
				'orderno' => $orderno,
				'error' => $errorinfo
		) ;
		if( $model -> where ( $where ) -> count() > 0 ) return ;
		
		$data = array(
				'orderno' => $orderno,
				'error' => $errorinfo,
				'order_time' => 0,
				'log_time' => time(),
				'sourceid' => $this -> member_id,
				'source' => self::$source,
				'log_type' => $log_type
		) ;
	
		$model -> insert ( $data ) ;
	}
	
	function push_ship( $params = array() )
	{
		log::selflog(var_export($params,true), self::$source) ;
		$data = array(
			'orderno' => $params['orderno'],
			'logi_no' => $params['logi_no'],
			'logi_code' => $this -> chgLogiCode( $params['logi_name'] ),
			'logi_name' => $params['logi_name']
		) ;
		$flapi = new flapi() ;
		$result = $flapi -> pushShip( $data );
		
		log::selflog($result, self::$source) ;
		if( !$result || strpos($result, 'DOCTYPE html') !== false ) {
			$this->_error($params['orderno'], json_encode($params), 'unship') ;
			$res = array('succ' => '0', 'msg' => '发货失败')  ;
		} else {
			$re = json_decode($result, true) ;
			if( $re['success'] && $re['responseCode'] == '00000' ) {
				$res = array('succ' => '1', 'msg' => '发货测试成功')  ;
			} else {
				$msg = $re['responseDesc'] ;
				$res = array('succ' => '0', 'msg' => $msg) ;
				
				if( $msg == '订单状态非法，不能发货' ) {
					$res = array('succ' => '1', 'msg' => '发货测试成功')  ;
				}
				
				if( $res['succ'] == 0 ) {
					$message = "订单号：{$params['orderno']}，原因：".$msg . "，推送参数：" . json_encode($data)."。<br>返回结果：" . $result ;
					$emailObj = new Email ();
					$emailObj->send_sys_email ( 'zenxiangjie@hansap.com', "返利发货错误提醒", $message );
				}
			}
		}
		
		return json_encode($res) ;
	}
	
	function chgLogiCode( $name )
	{
		$data = array(
				'中通快递' => '10007',
				'韵达快递' => '10011',
				'百世汇通' => '10005',
				'圆通快递' => '10003',
				'EMS' 	 => '10017',
				'优速快递' => '10019',
				'天天快递' => '10027',
				'优速快递' => '10019',
				'德邦物流' => '10205',
				'国通快递' => '10147',
				'安能物流' => '10289',
		) ;
		return $data[$name] ? $data[$name] : '10017' ;
	}
	
	/**
	 * 漏单检测，凌晨检测前3天的未发货订单是否已保存为汉购网订单
	 */
	function checkUnsaveOrder( $params )
	{
		$hour = date('G');
		if( $hour >= 6  ) {
			die;
		}
		log::selflog("check unsave order begin", self::$source ) ;

		$params['begin'] = date('Y-m-d 00:00:00',strtotime('-3days'));
		$params['end'] = date("Y-m-d 00:00:00") ;
	
		$this -> orderlist( $params ) ;
	}


	
	function getRefundOrder( $service )
	{
		$this -> service = $service ;

        $beginTime = $_GET['begin'] ? strtotime($_GET['begin']) : TIMESTAMP - 7*24*3600;
        $endTime = $_GET['end'] ? strtotime($_GET['end']) : TIMESTAMP ;
		$page_no = 1;
		$page_size = 50;
		$flag = true ;
		
		$flapi = new flapi() ;

		$model_fenxiao_sub = Model('b2c_order_fenxiao_sub') ;
		while( $flag )
		{
			$condition = array();
			$condition['begin'] = $beginTime;
			$condition['end'] = $endTime ;
			$condition['page_size'] = $page_size;
			$condition['page_no'] = $page_no;
			$res = $flapi->getRefundList( $condition ) ;
			//v($res);
			if( empty($res[1]) ) die ;
			list( $hasNext, $api_refund_list ) = $res ;
			
			$ordernos = array_column($api_refund_list, 'orderCode') ;
			if( empty($ordernos) ) die ;
			$sub_condition = array();
			$sub_condition['orderno'] = array('in', $ordernos) ;
			$fxSubs = $model_fenxiao_sub->where($sub_condition)->select();
			$sub_goods = $paysns = array();
			foreach ($fxSubs as $sub) {
				$sub_goods[$sub['orderno']][$sub['oid']] = $sub ;
			}
			
			$refundData = array();
			$model_refund_return = Model('refund_return') ;
			foreach ($api_refund_list as $refund)
			{
				$ordersn = $this->service->_getFxorderSn($refund['orderCode']);
				if (!$ordersn) continue;

				$filter=array();
				$filter['order_sn'] = $ordersn ;
				if( $model_refund_return->where( $filter )-> count() > 0) continue ;

				$refund_condition = array();
				$refund_condition['begin'] = $condition['begin'] ;
				$refund_condition['end'] = $condition['end'] ;
				$apiDetail = $flapi->getRefundDetail($refund['orderCode'], $refund['exOrderCode'], $refund_condition) ;
				$apiDetail = json_decode($apiDetail, true) ;
				if( !$apiDetail['success'] || $apiDetail['responseCode'] != '00000' || empty($apiDetail['result']) ){
					continue ;
				}
				
				$fx_goods_id = $apiDetail['result']['orderRefundItems'][0]['productCode'] ;
				if (!isset($this->rel[$fx_goods_id]) || empty($this->rel[$fx_goods_id])) {
					$this->_error($refund['orderCode'], "分销商品 ({$fx_goods_id}) 没有配置商品映射，无法生成退款");
					continue;
				}
				
				$goods_id = $this->rel[$fx_goods_id] ;
				$ordersn = $this->service->_getFxorderSn($refund['orderCode'], $goods_id);
                if (!$ordersn) continue;
				
				//检查子订单是否已申请退款或取消订单
				$filter=array();
				$filter['order_sn'] = $ordersn ;
				$filter['goods_id'] = array('in', array(0,$goods_id)) ;
				if( $model_refund_return->where( $filter )-> count() > 0) continue ;
				
				$data = array() ;
				$data['reason_id'] = 100; //退款退货理由 整型
				$data['refund_type'] = 3; //申请类型 1. 退款  2.退货
				$data['return_type'] = 3; //退货情况 1. 不用退货  2.需要退货

				if(in_array($refund['status'], array('2','31','32','42','3')) ){
					$data['refund_type'] = 1;
					$data['return_type'] = 1;
				} else if(in_array($refund['status'], array('41'))){
					$data['refund_type'] = 2;
					$data['return_type'] = 2;
				}
				
				$data['seller_state'] = 1; //卖家处理状态:1为待审核,2为同意,3为不同意,默认为1
				$data['refund_amount'] = $apiDetail['result']['orderRefundItems'][0]['applyAmount'];//退款金额
				$data['goods_num'] = $apiDetail['result']['orderRefundItems'][0]['exNum'];//商品数量
				$data['buyer_message'] = '买/卖双方协商一致';  //用户留言信息
				$data['ordersn'] = $ordersn;  //汉购网订单编号
				$data['goods_id'] = $goods_id; //商品编号
				
				$refundData[] = $data ;
			}
			//v($refundData);
			$this -> service -> createRefund( array('new' => $refundData) ) ;
				
			if( $hasNext == 0 ) $flag = false ;
				
			$page_no++;
		}
	}
	
	/** 1：等待卖家审核；2：等待卖家退款；3：退换货成功；41：卖家不同意退货；42：卖家不同意退款；43：退货超时；5：客服仲裁；6：取消退款申请；7：财务退款成功；31：财务退款成功； **/
	function traceRefund( $service )
	{
		$this -> service = $service ;
		
		$beginTime = TIMESTAMP - 30*24*3600 ;
		$endTime = TIMESTAMP ;
		$page_no = 1;
		$page_size = 50;
		$flag = true ;
		
		$flapi = new flapi() ;
		
		$model_fenxiao = Model('b2c_order_fenxiao') ;
		$model_order = Model('orders');
		$model_refund = Model('refund_return');
		while( $flag )
		{
			$condition = array();
			$condition['begin'] = $beginTime;
			$condition['end'] = $endTime ;
			$condition['page_size'] = $page_size;
			$condition['page_no'] = $page_no;
			$res = $flapi->getRefundList( $condition ) ;
			//v($res);
			if( empty($res[1]) ) die ;
			list( $hasNext, $api_refund_list ) = $res ;
				
			$ordernos = array_column($api_refund_list, 'orderCode') ;
			if( empty($ordernos) ) die ;
			$fx_condition = array();
			$fx_condition['orderno'] = array('in', $ordernos);
			$fx = $model_fenxiao->where($fx_condition)->select();
			$paysns = array_column($fx, 'pay_sn') ;
			
			$order_condition = array();
			$order_condition['pay_sn'] = array('in', $paysns);
			$orders = $model_order->where($order_condition)->key('order_id')->select();
			$oids = array_column(array_values($orders), 'order_id') ;
			
			//查找对应的汉购网的退款单，获取需要更新的退款列表
			$refund_condition = array() ;
			$refund_condition['order_id'] = array('in', $oids) ;
			$refund = $model_refund->where($refund_condition)->select();

			foreach ($refund as $_item) {
				//商家已同意的不处理
				$fx_order_id = $orders[$_item['order_id']]['fx_order_id'];
				if(!$fx_order_id) continue ;
				$preUpdateRefund[$fx_order_id] = $_item ;
			}

			foreach($api_refund_list as $api_item){
				$fx_order_id = $api_item['orderCode'] ;
				$refund_detail = $preUpdateRefund[$fx_order_id];

				//退款类型判定
				$params = array();
				if(in_array($api_item['status'], array('2','31','32','42','3')) ){
					$params['refund_type'] = 1;
					$params['return_type'] = 1;
				} else if(in_array($api_item['status'], array('41'))){
					$params['refund_type'] = 2;
					$params['return_type'] = 2;
				}
				$refund_condition = array() ;
				$refund_condition['refund_id'] = $refund_detail['refund_id'];
				$model_refund->where($refund_condition)->update($params);
			}

			$preUpdateRefund = array();
			foreach ($refund as $_item) {
				//商家已同意的不处理
				if( $_item['seller_state'] == 2 ) continue;
				
				$fx_order_id = $orders[$_item['order_id']]['fx_order_id'];
				if(!$fx_order_id) continue ;
				$preUpdateRefund[$fx_order_id] = $_item ;
			}
			
			//循环api退款列表，更新最新状态
			foreach ($api_refund_list as $api_item) {
				$fx_order_id = $api_item['orderCode'] ;
				$refund_detail = $preUpdateRefund[$fx_order_id];

				if(!$refund_detail) continue ;
				
				$api_status = $api_item['status'] ;
				$refundMoney = $api_item['return_amount'] ;
				//最新退款状态，默认待审核
				$refund_status = '1' ;
				//用户撤销、拼多多拒绝
				if( in_array($api_status, array('41', '42', '6')) ) {//41：卖家不同意退货；42：卖家不同意退款；6：取消退款申请；
					$refund_status = "5"; //取消锁定订单
				}
				//同意退款、退款成功
				if( in_array($api_status, array('31')) ) {//7：财务退款成功(返利接口调整为了31，2017.02.08)
					$refund_status = "3"; //完成
				}
				//有用户撤销退款、商家拒绝退款历史记录，但是后来又重新退款成功
				if( $refund_detail['seller_state'] == 3 && $refund_status == '3' ){
					$model_refund->where(array('refund_id'=>$refund_detail['refund_id']))->update(array('seller_state'=>2, 'refund_state'=>2,'seller_message'=>'同意'));
				}
				if( $refund_detail['seller_state'] == 3 && $refund_status == '5' ) {
					continue;
				}
				
				$params = array(
						'refund_id' => $refund_detail['refund_id'],
						'refund_status' => $refund_status,
						'update_time' => time(),
						'is_ship' => $refund_detail['goods_id'] == 0 ? 0 : 1,
						'op_id' => $this->member_id,
						'op_name' => self::$source
				) ;

				$this -> service -> doByNewRefundStatus( $params ) ;
			}
			
			if( $hasNext == 0 ) $flag = false ;
			
			$page_no++;
		}
	}
	
	function checkUnshipOrder()
	{
		$updateTime = time() - 3600*23  ;
		if( date('G') < 3 ) {
			$updateTime = time() - 24*3600*3  ;
		}
	
		$comm_where = array();
		$comm_where['shipping_time'] = array('gt', $updateTime) ;
		$comm_where['shipping_express_id'] = array('gt', 0) ;
		$result = Model('order_common') -> where ( $comm_where ) -> select () ;
		if( !$result ) die('no result') ;
	
		$oids = array_column($result, 'order_id') ;
		$oid_expressid_rels = array_column($result, 'shipping_express_id', 'order_id');
		//属于返利网的订单
		$where = array() ;
		$where['order_id'] = array('in', $oids) ;
		$where['buyer_id'] = $this->member_id ;
		$orders = TModel('orders')->where($where)->select() ;
		if( !$orders ) die('no orders') ;
	
		//返利网未发货列表
		$beginTime = TIMESTAMP - 86400*30 ;    //30天内未发货监测
		$endTime = TIMESTAMP ;
		$order_list = $this->_order_list($beginTime, $endTime) ;
		$order_nos = array_column($order_list, 'order_no') ;
	
		$express = rkcache('express', true) ;
		foreach ($orders as $order) {
				
			if( !in_array($order['fx_order_id'], $order_nos) ) continue ;
			
			$express_id = $oid_expressid_rels[ $order['order_id'] ] ;
			$data = array();
			$data['orderno'] = $order['fx_order_id'];
			$data['logi_no'] = $order['shipping_code'];
			$data['logi_name'] = $express[$express_id]['e_name'];
			if( $data['logi_no'] ) {
				$this -> push_ship($data) ;
			}
				
		}
	}
	
	//返利网未发货列表
	function _order_list($beginTime, $endTime)
	{
		//$beginTime = $_GET['begin'] ? strtotime($_GET['begin']) : TIMESTAMP - 24*3600 ;
		//$endTime = $_GET['end'] ? strtotime($_GET['end']) : TIMESTAMP ;
		$page_no = 1;
		$page_size = 100;
		$flag = true ;
		
		$flapi = new flapi() ;
		
		$list = array();
		while( $flag )
		{
			$condition = array();
			$condition['begin'] = $beginTime;
			$condition['end'] = $endTime ;
			$condition['page_size'] = $page_size;
			$condition['page_no'] = $page_no;
			$res = $flapi->getOrderList( $condition ) ;
			//v($res);
			if( empty($res[1]) ) return $list ;
			list( $hasNext, $order_list ) = $res ;
			
			foreach ($order_list as $order) {
				$list[] = $order ;
			}
			
			if( $hasNext == 0 ) $flag = false ;
				
			$page_no++;
		}
		return $list ;
	}
	
	/**
	 * 获得订单状态和售后
	 *
	 * @param unknown $orderSNs 批量请用半角逗号分开
	 */
	function getOrderStatus($orderSNs)
	{
		$flapi = new flapi() ;
		$orderSNs = explode(',', $orderSNs);
		$return = array();
		foreach ($orderSNs as $fx_order_id) {
			$orderDetail = $flapi -> getOrderDetail( $fx_order_id ) ;
			$orderDetail = json_decode($orderDetail, true) ;
			//组装所有分销渠道该接口的统一的返回数据格式
			$item = array(
					'orderSn' => $orderDetail['result']['orderCode'],
					'orderStatus' => $orderDetail['result']['orderStatus'] == 5 ? 3: 0,
					'refundStatus' => 0,
			);
			$fenxiaoList['orderStatus']['list'][] = $item;
			 
		}
		$fenxiaoList['orderStatus']['result'] = 1;
		return $fenxiaoList;
		 
	}
}