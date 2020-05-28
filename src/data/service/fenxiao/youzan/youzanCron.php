<?php

require_once 'kdtClient.php';

class youzanCron
{

	private static $appid = "d8369a9dfb36888c5f";
	private static $appsecret = "76a5f9c5871aadce83ccb6717449725d";
	static $source = "youzan" ;
	public $client ;
	
	public static $_logics = array(
		'申通快递' => '1', //申通
		'EMS' => '11', //邮政
		'圆通快递' => '2', //圆通
		'顺丰快递' => '7', //顺丰
		'韵达快递' => '4', //韵达
		'中通快递' => '3', //中通
		'宅急送' => '25', //宅急送
		'天天快递' => '5', //天天快递
		'龙邦快递' => '32', //龙邦快递
		'全一快递' => '18', //全一快递
		'快捷速递' => '34', //快捷速递
		'华宇物流' => '61', //华宇物流
		'中铁快运' => '30', //中铁快运
		'德邦物流' => '28', //德邦物流
		'大田物流' => '49', //大田物流
		'百世汇通' => '6', //百世汇通
		'全峰快递' => '17', //全峰快递
		'优速快递' => '38', //优速快递
		'国通快递' => '40', //国通快递
		'安能物流' => '128'
	) ;
	
	function __construct( $getRel = 1 ){
		$this -> client = new kdtClient();
		$this -> client -> init(self::$appid, self::$appsecret);
		$model_member = Model("member") ;
		$conditon = array() ;
		$condition = array("member_name" => self::$source) ;
		$row = $model_member -> where( $condition ) -> find() ;
		$this->member_id = $row['member_id'] ;
		$model_member->execute("set wait_timeout=1000") ;
		//商品映射
		if( $getRel ) {
			$this -> rel = $this -> getGoodsRel() ;
			//$this -> oldRel = $this -> getOldGoodsRel() ;
			$this -> oldRel = array() ;
		}
    }
	
	//获取商品映射
	function getGoodsRel()
	{
		$conditon = array() ;
		$condition = array("uid" => $this->member_id) ;
		$result = Model("b2c_category") -> where ( array('uid' => $this->member_id) ) -> select () ;
		$rel = $result ? array_column($result, 'pid', 'fxpid') : array() ;

		return $rel ;
	}
	//老平台商品映射
	/*function getOldGoodsRel()
	{
		$result = ecModel("B2cCategory") -> where ( array('uid' => $this->member_id) ) -> select () ;
		$rel = $result ? array_column($result, 'pid', 'fxpid') : array() ;
		return $rel ;
	}*/
	
	function getUnshipOrder($params=array())
	{
		$method = "kdt.trades.sold.get" ;
		$params = array(
				'status' => 'WAIT_SELLER_SEND_GOODS',
				'start_created' => $params['begin'] ,
				'end_created' => $params['end'],
				'page_no' => 1,
				'use_has_next' => true
		) ;
		
		$flag = 1;
		$page_no = 1;
		$order_data = array();

		while( $flag ) {
			$params['page_no'] = $page_no ;
			$res = $this -> client -> post( $method, $params ) ;

			foreach ( $res['response']['trades'] as $trade ) {
				//抓取2016-07-01 12:00:00以后的订单
				if( strtotime($trade['created']) < strtotime('2016-07-01 12:00:00') ) continue ;
				$order_data[] = $trade ;
			}
			
			if( $res['response']['has_next'] == true ) {
				$page_no += 1 ;
			} else {
				$flag = 0 ;
			}
		}
		
		return $order_data ;
		
	}
	
	//获取有赞订单
	function orderlist( $params = array() ) 
	{
		$order_api = $params['order_api'] ;
		$service = $params['service'] ;
		if( !$order_api ) die("no order api") ;
		$start_update = $params['begin'] ? $params['begin'] : date("Y-m-d 00:00:00");
		$end_update = $params['end'] ? $params['end'] : date("Y-m-d H:i:s");

		$res_orders = $this -> getUnshipOrder( array('begin' => $start_update, 'end' => $end_update) ) ;

		$apiOrdernos = array_column($res_orders, 'tid') ;
		log::selflog( "api orders:" . json_encode($apiOrdernos) , self::$source ) ;
		$savedOrderIds = $service -> getSavedidByApiorderno( $apiOrdernos );
		log::selflog( $start_update ."-".$end_update."已保存的订单ID：".json_encode($savedOrderIds) , self::$source ) ;
		
		$bns = $urls = $order_data = array();
		$noRelNums = 0 ;//没有配置映射的数量
		foreach ( $res_orders as $trade )
		{
			//已保存的订单
			if( in_array($trade['tid'], $savedOrderIds) ) continue ;
			
			$item = $coupons = array();
			$continue = 0 ;
			$new = $old = 0 ; //统计新老平台商品个数
			$goods_amount = 0 ;
			//订单商品
			foreach ( $trade['orders'] as $k => $_item ) {
				//$bns[] = $_ord['outer_item_id'];
				$num_iid = $_item['num_iid'];
				if( !isset( $this -> rel[ $num_iid ] ) || $this -> rel[ $num_iid ] == 0 ) {
					$noRelNums ++ ;
				}

				$fx_goods_id = $_item['num_iid'];
				if( isset( $this -> rel[ $fx_goods_id ] ) && $this -> rel[ $fx_goods_id ] ) {
					$goods_id = $this -> rel[ $fx_goods_id ] ;
					$platform = 'new' ;
					$new++;
				} else if( isset( $this -> oldRel[ $fx_goods_id ] ) && $this -> oldRel[ $fx_goods_id ] ) {
					$goods_id = $this -> oldRel[ $fx_goods_id ] ;
					$platform = 'old' ;
					$old++;
				} else {
					$this -> _error( $trade['tid'], "分销商品 ".$_item['title']."({$fx_goods_id}) 找不到对应的汉购商品" );
					$continue = 1 ;
				}
				$item[] = array(
						'goods_id' => $goods_id,
						'name' => $_item['title'],
						'num' => $_item['num'],
						'price' => $_item['price'],
                        'fxpid' => $fx_goods_id,
                        'oid' => $_item['oid'],
						'platform' => $platform,    ``
						//'bn' => $_ord['outer_item_id']
				) ;
				$goods_amount += $_item['price'] * $_item['num'] ;
			}
			if( $continue ) continue ;
			$discount = $goods_amount > $trade['total_fee'] ? $goods_amount - $trade['total_fee'] : 0 ;

			$order = array(
					'order_sn' => $trade['tid'],
					'order_time' => strtotime($trade['created']),
					'receiver' => $trade['receiver_name'] ? $trade['receiver_name'] : $trade['sub_trades'][0]['receiver_name'],
					'provine' => $trade['receiver_state'] ? $trade['receiver_state'] : $trade['sub_trades'][0]['receiver_state'],
					'city' => $trade['receiver_city'] ? $trade['receiver_city'] : $trade['sub_trades'][0]['receiver_city'],
					'area' => $trade['receiver_district'] ? $trade['receiver_district'] : $trade['sub_trades'][0]['receiver_district'],
					'address' => $trade['receiver_address'] ? $trade['receiver_address'] : $trade['sub_trades'][0]['receiver_address'],
					'mobile' => $trade['receiver_mobile'] ? $trade['receiver_mobile'] : $trade['sub_trades'][0]['receiver_mobile'],
					'amount' => $trade['total_fee'],
					'item' => $item,
					'buy_id' => $this -> member_id,
					'payment_code' => 'fenxiao',
					'discount' => $discount,
					'remark' => $trade['buyer_message'],
					'platform' => $platform,
                    'shipping_fee'=>$trade['post_fee']
			) ;
			
			if( $new == 0 && $old == 0 ) {
				continue ;
			} else if( $new == 0 ) {
				$order['platform'] = 'old' ;
			} else if( $old == 0 ) {
				$order['platform'] = 'new' ;
			} else {
				$order['platform'] = 'both' ;
			}
			
			$order_data[] = $order ;
		}

		$service -> doCreateOrder( $order_data ) ;
	}

	//保存错误信息到日志table
	function _error($orderno, $errorinfo, $ordertime=0)
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
	
	public function push_ship( $params = array() ) 
	{
		$method = "kdt.logistics.online.confirm" ;
		$data = array(
			'tid' => $params['orderno'],
			'oids' => $params['oid'],
			'is_no_express' => 0,
			'out_stype' => self::$_logics[ $params['logi_name'] ] ? self::$_logics[ $params['logi_name'] ] : '8',
			'out_sid' => $params['logi_no'],
		) ;
		log::selflog( "发货参数：" . var_export($data, true) , self::$source ) ;

		$return = $this -> client -> post( $method, $data ) ;
		log::selflog( var_export($return,true) , self::$source ) ;
		
		if( !$return ) {
			$res = json_encode( array('succ' => '0', 'msg' => '发货失败') ) ;
		} else {
			$r = json_decode($return, true) ;
			if( $r['response']['shipping']['is_success'] != true ) {
				$res = json_encode( array('succ' => '0', 'msg' => $return['error_response']['msg'] ) ) ;
			} else {
				$res = json_encode( array('succ' => '1', 'msg' => '发货测试成功') ) ;
			}
		}
		return $res ;
	}
	
	/**
	 * 漏单检测，凌晨检测前3天的未发货订单是否已保存为汉购网订单
	 */
	function checkUnsaveOrder( $params )
	{
		$hour = date('G');
		if( $hour >= 9 && $preDay==1  ) {
			$params['preDay'] = 1 ;
		}
		log::selflog("check unsave order begin", self::$source ) ;
	
		$limit = $params['preDay'] == 0 ? 1 : $params['preDay'] ;
		
		for ( $i=$limit; $i >=1; $i-- ) {
			$b_time = time() - $i * 24 * 3600 ;
			$e_time = $b_time + 24 * 3600 ;
			$params['begin'] = date('Y-m-d H:i:s', $b_time) ;
			$params['end'] = date('Y-m-d H:i:s', $e_time) ;
			$this -> orderlist( $params ) ;
		}
	}
	
	function checkUnshipOrder()
	{
		$hour = date('G');
		//凌晨检测最近3天，其他时间检测最近3小时
		$updateTime = $hour >= 6 ? time() - 3600*3 : time() - 3600*24*3 ;
		
		$comm_where = array();
		$comm_where['shipping_time'] = array('gt', $updateTime) ;
		$result = Model('order_common') -> where ( $comm_where ) -> select () ;
		if( !$result ) die('no result') ;
		
		$oids = array_column($result, 'order_id') ;
		$oid_expressid_rels = array_column($result, 'shipping_express_id', 'order_id');
		//属于有赞的订单
		$where = array() ;
		$where['order_id'] = array('in', $oids) ;
		$where['buyer_id'] = $this->member_id ;
		$orders = TModel('orders')->where($where)->select() ;
		if( !$orders ) die('no orders') ;
		
		$field = 'MAX(add_time) AS mx, MIN(add_time) AS mi' ;
		$row = Model('orders')->field( $field )->where($where)->find();
		if( !$row['mx'] || !$row['mi'] ) {
			die('no mx,mi') ;
		}
		
		$begin = $row['mx'] == $row['mi'] ? $row['mi'] - 1 : $row['mi'] ; //开始时间结束时间一样，接口会无结果返回
		$unship_order = $this -> getUnshipOrder( array('begin' => date('Y-m-d H:i:s', $begin), 'end' => date('Y-m-d H:i:s', $row['mx'])) ) ;
		$unshiop_oids = array();
		
		if( empty($unship_order) ) die('no unship api result') ;
		
		foreach ( $unship_order as $trade ) {
			$unshiop_oids[] = $trade['tid'];
		}
		
		$express = rkcache('express', true) ;
		//循环推送物流信息
		foreach ( $orders as $order )
		{
			$fx_order_id = $order['fx_order_id'] ;
			if( !in_array($fx_order_id, $unshiop_oids) ) continue ;
		
			//查找商品信息
			$where = array();
			$where['order_id'] = $order['order_id'] ;
			$order_items = TModel('order_goods')->where($where)->select() ;
			if( !$order_items ) continue ;
			
			//分销平台子订单信息
			$fsubWhere = array() ;
			$fsubWhere = array() ;
			$fsubWhere['orderno'] = $fx_order_id ;
			$fsubWhere['product_id'] = array('in', array_column($order_items, 'goods_id') ) ;
			$fSub = TModel('b2c_order_fenxiao_sub') -> where ( $fsubWhere ) -> select () ;
			if( !$fSub ) continue ;
			
			//推送快递
			$oids = array_column($fSub, 'oid') ;
			$express_id = $oid_expressid_rels[ $order['order_id'] ] ;
			$data = array();
			$data['orderno'] = $fx_order_id;
			$data['logi_no'] = $order['shipping_code'];
			$data['logi_name'] = $express[$express_id]['e_name'];
			$data['oid'] = implode(",", $oids);
			$this -> push_ship($data) ;
		}
	}
	
	function getOrderDetail( $ordersn )
	{
		$method = "kdt.trade.get" ;
		$params = array(
				'tid' => $ordersn
		) ;
		$res = $this -> client -> post( $method, $params ) ;
		return $res ;
	}

}