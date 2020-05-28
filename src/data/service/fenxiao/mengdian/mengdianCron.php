<?php

require_once 'mdapi.php';

class mengdianCron
{

	static $source = "mengdian" ;
	
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
//		$params['page_size'] = 10;
//		$params['page_no'] = 1;

		$mdapi = new mdapi() ;
		$res1 = $mdapi->getSkuList($params);
		$res = json_decode($res1,true);
		$data_out = array();
		foreach($res['data']['page_data'] as $k => $v1){
			$spu_name =  $v1['spu']['spu_name'];
			$spu_id =  $v1['spu']['spu_id'];
			foreach($v1['skus'] as $v2){
				$item['goods_name'] = $spu_name;
				$item['source'] = 'mengdian';
				$item['sku_id'] = $spu_id;
//				$item['spu_id'] = $spu_id;
				$data_out[] = $item;
			}
		}
		return $data_out;
	}
	
	//获取萌店订单列表
	function orderlist( $params = array() ) 
	{
		$service = $params['service'] ;
		
		$beginTime = $_GET['begin'] ? strtotime($_GET['begin']) : TIMESTAMP - 7*24*3600 ;
		$endTime = $_GET['end'] ? strtotime($_GET['end']) : TIMESTAMP ;
		$page_no = 1;
		$page_size = 100;
		$flag = true ;
		
		$mdapi = new mdapi() ;
		if( $mdapi->token == '' ) die('get access token faild') ;
		
		while( $flag ) 
		{
			$condition = array();
			$condition['begin'] = $beginTime;
			$condition['end'] = $endTime ;
			$condition['page_size'] = $page_size;
			$condition['page_no'] = $page_no;
			$res = $mdapi->getOrderList( $condition ) ;
//			v($res);
			if( empty($res[1]) ) die ;
			list( $total_page, $order_list ) = $res ;
			$apiOrdernos = array_column($order_list, 'order_no') ;
			log::selflog("rrd api：".count($order_list) . " | ". json_encode( $apiOrdernos ), self::$source) ;
	
			$savedOrderIds = $service -> getSavedidByApiorderno( $apiOrdernos ) ;
			log::selflog("saved orders:". date('Y-m-d H:i:s', $beginTime) ." - " . date('Y-m-d H:i:s', $endTime) . json_encode($savedOrderIds), self::$source) ;
		
			$order_data = array() ;
			foreach ( $order_list as $order ) {
				
				if( in_array($order['order_no'], $savedOrderIds) ) continue ;
				
				$apiDetail = $mdapi->getOrderDetail($order['order_no']) ;
				$apiDetail = json_decode($apiDetail, true) ;
				if( $apiDetail['code']['errcode'] != 0 ) continue ;
				$order_data[] = $apiDetail['data'] ;
			}
			$datas = array() ;
			foreach($order_data as $order) 
			{
				$continue = 0;
				$order_detail = array();
				foreach ($order['order_details'] as $k => $_item ) {
					$goods = array();
					$fx_goods_id = $_item['spu_id'];
					$goods['name'] = $_item['sku_name'];
					$goods['num'] = $_item['qty'];
					$goods['price'] = $_item['price'];
					$goods['fxpid'] = $fx_goods_id;
					$goods['oid'] = $_item['order_detail_id'];
					if( isset( $this -> rel[ $fx_goods_id ] ) && $this -> rel[ $fx_goods_id ] ) {
						$goods['goods_id'] = $this -> rel[ $fx_goods_id ] ;
						$goods['platform'] = 'new' ;
						$new++;
					} else {
						$this -> _error( $order['order_no'], "分销商品 ".$_item['sku_name']." id({$fx_goods_id}) 找不到对应的汉购商品" );
						$continue = 1 ;
					}
	
					$order_detail['item'][$k] = $goods;
				}
				if( $continue ) continue ;
				$order_detail['platform'] = 'new' ;
				$order_detail['order_sn'] = $order['order_no'];
				$order_detail['buy_id'] = $this -> member_id ;
				$order_detail['receiver'] = $order['receiver_name'];
				$order_detail['provine'] = $order['receiver_region']['province'];
				$order_detail['city'] = $order['receiver_region']['city'];
				$order_detail['area'] = $order['receiver_region']['district'];
				$order_detail['address'] = $order['receiver_region']['address'];
				$order_detail['mobile'] = $order['receiver_tel'];
				$order_detail['remark'] = $order['remark'];
				$order_detail['amount'] = $order['real_amount'];
				$order_detail['payment_code'] = 'fenxiao';
				$order_detail['order_time'] = strtotime( $order['create_time'] );
				$order_detail['shipping_fee']=$order['delivery_amount'];//运费
				$datas[] = $order_detail ;
			}

			$service -> doCreateOrder( $datas ) ;
			
			if( $total_page == $page_no ) $flag = false ;
			
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
		$mdapi = new mdapi() ;
		if( $mdapi->token == '' ) {
			log::selflog('get access token faild', self::$source) ;
			return json_encode( array('succ' => '0', 'msg' => 'get access token faild') )  ;
		}
		$result = $mdapi -> pushShip( $data );
		
		log::selflog($result, self::$source) ;
		if( !$result || strpos($result, 'DOCTYPE html') !== false ) {
			$this->_error($params['orderno'], json_encode($params), 'unship') ;
			$res = json_encode( array('succ' => '0', 'msg' => '发货失败') )  ;
		} else {
			$re = json_decode($result, true) ;
			$errCode = $re['code']['errcode'] ;
			if( $re['code']['errcode'] == 0 && $re['data'][0]['error_message'] == 'Success' ) {
				$res = json_encode( array('succ' => '1', 'msg' => '发货测试成功') )  ;
			} else {
				$msg = $re['code']['errcode'] != 0 ? $re['code']['errmsg'] : $re['data'][0]['error_message'] ;
				$res = json_encode( array('succ' => '0', 'msg' => $msg) ) ;
				
				if( $res['succ'] == 0 ) {
					$message = "订单号：{$params['orderno']}，原因：".$msg . "，推送参数：" . json_encode($data)."。<br>返回结果：" . $result ;
					$emailObj = new Email ();
					$emailObj->send_sys_email ( 'zenxiangjie@hansap.com', "萌店发货错误提醒", $message );
				}
			}
		}
		
		return $res ;
	}
	
	function chgLogiCode( $name )
	{
		$data = array(
				'中通快递' => 'zhongtong',
				'韵达快递' => 'yunda',
				'百世汇通' => 'huitongkuaidi',
				'圆通快递' => 'yuantong',
				'申通快递' => 'shentong',
				'EMS' 	 => 'EMS',
				'顺丰快递' => 'shunfeng',
				'优速快递' => 'youshuwuliu',
				'天天快递' => 'tiantian',
				'宅急送'   => 'zhaijisong',
				'快捷速递' => 'kuaijiesudi',
				'全峰快递' => 'quanfengkuaidi',
				'安能物流' => 'annengwuliu'
		) ;
		return $data[$name] ? $data[$name] : 'EMS' ;
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
		
		$beginTime = TIMESTAMP - 3*24*3600 ;
		$endTime = TIMESTAMP ;
		$page_no = 1;
		$page_size = 50;
		$flag = true ;
		
		$mdapi = new mdapi() ;
		if( $mdapi->token == '' ) die('get access token faild') ;

		$model_fenxiao_sub = Model('b2c_order_fenxiao_sub') ;		
		while( $flag )
		{
			$condition = array();
			$condition['begin'] = $beginTime;
			$condition['end'] = $endTime ;
			$condition['page_size'] = $page_size;
			$condition['page_no'] = $page_no;
			$res = $mdapi->getRefundList( $condition ) ;
			//v($res);
			if( empty($res[1]) ) die ;
			list( $total_page, $api_refund_list ) = $res ;
			
			$ordernos = array_column($api_refund_list, 'order_no') ;
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
				$fx_goods_id = $refund['spu_id'] ;
				if (!isset($this->rel[$fx_goods_id]) || empty($this->rel[$fx_goods_id])) {
					$this->_error($refund['order_no'], "分销商品 ({$fx_goods_id}) 没有配置商品映射，无法生成退款");
					continue;
				}
				
				$goods_id = $this->rel[$fx_goods_id] ;
				$ordersn = $this->service->_getFxorderSn($refund['order_no'], $goods_id);
                if (!$ordersn) continue;
				
				//检查子订单是否已申请退款或取消订单
				$filter=array();
				$filter['order_sn'] = $ordersn ;
				$filter['goods_id'] = array('in', array(0,$goods_id)) ;
				if( $model_refund_return->where( $filter )-> count() > 0) continue ;
				
				$data = array() ;
				$data['reason_id'] = 100; //退款退货理由 整型
				$data['refund_type'] = $refund['return_type']; //申请类型 1. 退款  2.退货
				$data['return_type'] = $refund['return_type']; //退货情况 1. 不用退货  2.需要退货
				$data['seller_state'] = 1; //卖家处理状态:1为待审核,2为同意,3为不同意,默认为1
				$data['refund_amount'] = $refund['return_amount'];//退款金额
				$data['goods_num'] = $refund['return_qty'];//商品数量
				$data['buyer_message'] = '买/卖双方协商一致';  //用户留言信息
				$data['ordersn'] = $ordersn;  //汉购网订单编号
				$data['goods_id'] = $goods_id; //商品编号 
				$data['create_time'] =  strtotime($refund['create_time']);//订单创建时间
				$refundData[] = $data ;
			}
			
			$this -> service -> createRefund( array('new' => $refundData) ) ;
				
			if( $total_page == $page_no ) $flag = false ;
				
			$page_no++;
		}	
	}
	
	/** 0全部，1买家发起退款申请，2买家发起退款退货申请，3商家同意退款申请，4等待买家退货，5专家已收货，7已退款退货，8已拒绝，9已取消，10微盟支付处理中 **/
	function traceRefund( $service )
	{
		$this -> service = $service ;
		
		$beginTime = TIMESTAMP - 30*24*3600 ;
		$endTime = TIMESTAMP ;
		$page_no = 1;
		$page_size = 50;
		$flag = true ;
		
		$mdapi = new mdapi() ;
		if( $mdapi->token == '' ) die('get access token faild') ;
		
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
			$res = $mdapi->getRefundList( $condition ) ;
			//v($res);
			if( empty($res[1]) ) die ;
			list( $total_page, $api_refund_list ) = $res ;
				
			$ordernos = array_column($api_refund_list, 'order_no') ;
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
				$fx_order_id = $api_item['order_no'] ;
				$refund_detail = $preUpdateRefund[$fx_order_id];
				if(!$refund_detail) continue ;
				
				$api_status = $api_item['return_status'] ;
				$refundMoney = $api_item['return_amount'] ;
				//最新退款状态，默认待审核
				$refund_status = '1' ;
				//用户撤销、拼多多拒绝
				if( in_array($api_status, array('8', '9')) ) {//8已拒绝，9已取消
					$refund_status = "5"; //取消锁定订单
				}
				//同意退款、退款成功
				if( in_array($api_status, array('3', '7', '10')) ) {//3商家同意退款申请，7已退款退货,10微盟支付处理中
					$refund_status = "3"; //完成
				}
				//有用户撤销退款、商家拒绝退款历史记录，但是后来又重新退款成功
				if( $refund_detail['seller_state'] == 3 && $refund_status == '3' ){
					$model_refund->where(array('refund_id'=>$refund_detail['refund_id']))->update(array('seller_state'=>2, 'refund_state'=>2,'seller_message'=>'同意'));
				}
				if( $refund_detail['seller_state'] == 3 && $refund_status == '5' ) {
					continue;
				}
				
				
				if( $refund_detail['refund_amount'] != $refundMoney  ) {
					$updateData = array(
							'refund_amount' => $refundMoney,
					);
					$where = array('refund_id' => $refund_detail['refund_id']) ;
					$res = $model_refund -> where ( $where ) -> update ( $updateData ) ;
					if( !$res ) continue ;
				}
				
				$params = array(
						'refund_id' => $refund_detail['refund_id'],
						'refund_status' => $refund_status,
						'update_time' => $api_item['update_time'],
						'is_ship' => $refund_detail['goods_id'] == 0 ? 0 : 1,
						'op_id' => $this->member_id,
						'op_name' => self::$source
				) ;
				$this -> service -> doByNewRefundStatus( $params ) ;
			}
			
			if( $total_page == $page_no ) $flag = false ;
			
			$page_no++;
		}
	}
	
	function checkUnshipOrder()
	{
		$updateTime = time() - 3600*3  ;
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
		//属于萌店的订单
		$where = array() ;
		$where['order_id'] = array('in', $oids) ;
		$where['buyer_id'] = $this->member_id ;
		$orders = TModel('orders')->where($where)->select() ;
		if( !$orders ) die('no orders') ;
	
		//萌店未发货列表
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
	
	//萌店未发货列表
	function _order_list($beginTime, $endTime)
	{
		//$beginTime = $_GET['begin'] ? strtotime($_GET['begin']) : TIMESTAMP - 24*3600 ;
		//$endTime = $_GET['end'] ? strtotime($_GET['end']) : TIMESTAMP ;
		$page_no = 1;
		$page_size = 100;
		$flag = true ;
		
		$mdapi = new mdapi() ;
		if( $mdapi->token == '' ) die('get access token faild') ;
		
		$list = array();
		while( $flag )
		{
			$condition = array();
			$condition['begin'] = $beginTime;
			$condition['end'] = $endTime ;
			$condition['page_size'] = $page_size;
			$condition['page_no'] = $page_no;
			$res = $mdapi->getOrderList( $condition ) ;
			//v($res);
			if( empty($res[1]) ) die ;
			list( $total_page, $order_list ) = $res ;
			
			foreach ($order_list as $order) {
				$list[] = $order ;
			}
			
			if( $total_page == $page_no ) $flag = false ;
				
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
		$mdapi = new mdapi() ;
		$orderSNs = explode(',', $orderSNs);
		$return = array();
		foreach ($orderSNs as $fx_order_id) {
			//查询人人店接口
			$orderDetail = $mdapi -> getOrderDetail( $fx_order_id ) ;
			$orderDetail = json_decode($orderDetail, true) ;
			//组装所有分销渠道该接口的统一的返回数据格式
			$item = array(
					'orderSn' => $orderDetail['data']['order_no'],
					'orderStatus' => in_array($orderDetail['data']['order_status'], array('2','3')) ? 3: 0,
					'refundStatus' => 0,
			);
			$fenxiaoList['orderStatus']['list'][] = $item;
			 
		}
		$fenxiaoList['orderStatus']['result'] = 1;
		return $fenxiaoList;
		 
	}
}