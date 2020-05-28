<?php

class CrmService
{
	static $limit = 100 ;
	
	function __construct()
	{
		require_once 'crm/sdk.php';
		$this -> sdk = new crm_sdk() ;
	}
	
	function addusers()
	{
		$member_list = $this -> getMemberList() ;
	
		foreach( $member_list as $row ) {
			$member_id = $row['member_id'] ;
			if( !$member_id ) continue ;
	
			$res = $this -> sdk -> add_user( $row ) ;
			$r = json_decode( $res, true ) ;v($r,0);
			if( isset( $r['errcode'] ) && $r['errcode'] == "0" )
			{
				if( !isset( $r['uid'] ) || !$r['uid'] ) continue ;
				//更新b2c_members表CRM会员ID字段
				$filter = array('member_id' => intval($member_id)) ;
				$data = array('crm_member_id' => $r['uid']) ;
				TModel('member') -> where( $filter ) -> save( $data ) ;
			}
		}
	}
	
	/**
	 * 未同步CRM的用户
	 */
	function getMemberList()
	{
		//排除分销会员ID
		$fxuser = Service("Fenxiao") -> getFenxiaoMembers() ;
		$fxmemberid = array_keys($fxuser);

		$model_member = TModel("member");
		$condition = array() ;
		$condition['member_id'] = array('not in', $fxmemberid) ;
		$condition['_string'] = " (`crm_member_id` IS NULL OR `crm_member_id` = 0)" ;
		$b2c_members = TModel("member") -> where ( $condition ) -> limit ( self::$limit ) -> select () ;
		$member_ids = array_column($b2c_members, "member_id") ;
		if( empty($member_ids) ) return array() ;

		//会员地区
		$areaArr = $this -> getMemberAreaByMids( $member_ids ) ;
	
		$member_list = array() ;
		foreach ( $b2c_members as $row )
		{
			$member_id = $row['member_id'] ;
			$area =  isset($areaArr[ $member_id ]) ? $areaArr[ $member_id ] : array('truename'=>'','provine'=>'','city'=>'','area'=>'','address'=>'') ;

			$member_list[] = array(
					'uname' => $row['member_name'],
					'truename' => strval($area['truename']),
					'sex' => (int)$row['member_sex'] ,
					'brithday' => $row['member_birthday'] ? $row['member_birthday'] : '',
					'mobile' => strval($row['member_mobile'] ? $row['member_mobile'] : $area['mobile']),
					'email' => strval($row['member_email']),
					'avatar' => strval($row['member_avatar']),
					'contry' => '中国',
					'provine' => strval($area['provine']),
					'city' => strval($area['city']),
					'area' => strval($area['area']),
					'address' => strval($area['address']),
					'regtime' => $row['member_time'],
					'member_id' => $member_id
			) ;
		}
	
		return $member_list ;
	}
	
	/**
	 * 根据会员ID数组，获取这些会员地区信息（address表）
	 */
	function getMemberAreaByMids($ids)
	{
		$return = array() ;
		if( count($ids) > 0 )
		{
			$condition = array() ;
			$condition['member_id'] = array('in', $ids) ;
			//$condition['is_default'] = '1';
			$result = Model("address") -> where ( $condition ) -> select () ;
			
			foreach ( $result as $row ) {
				$ship_area = $row['area_info'] ;
				list($province, $city, $area) = explode( " ", $ship_area ) ;
				$member_id = $row['member_id'] ;
				
				if( in_array($member_id, $return) && $row['is_default'] == '0' ) continue ;
				
				$return[ $member_id ] = array(
					'provine' => $province ? $province : '',
					'city' => $city ? $city : '',
					'area' => $area ? $area : '',
					'address' => $row['address'],
					'truename' => $row['true_name'],
					'mobile' => $row['mob_phone'],
				) ;
			}
		}
		
		return $return ;
	}
	
	/***
	 * 订单推送
	 */
	function addorders()
	{
		$order_list = $this -> getOrderList() ;
		$orderObj = TModel('orders') ;
		foreach ( $order_list as $order )
		{
			$order_id = $order['order_sn'] ;
	
			$res = $this->sdk->add_order( $order ) ;
	
			$r = json_decode( $res, true ) ;v($r,0);
			if( isset( $r['errcode'] ) && $r['errcode'] == "0" )
			{
				//更新sync_crm状态
				$filter = array('order_sn' => $order_id) ;
				$data = array('sync_crm' => '1') ;
				TModel('orders') -> where( $filter ) -> save( $data ) ;
			}
		}
	}
	
	/**
	 * 未推送的订单列表
	 */
	function getOrderList()
	{
		$model_order = TModel("orders");
		$condition = array() ;
		$condition['order_state'] = array('gt', '10') ;
		$condition['sync_crm'] = array('eq', '0') ;
		//$condition['order_id'] = 3156;
		$b2c_orders = $model_order -> where( $condition ) -> order ('`add_time` ASC') -> limit ( self::$limit ) -> select () ;

		$oids = array_column( $b2c_orders, 'order_id' ) ;
		$member_ids = array_column( $b2c_orders, 'buyer_id' ) ;
		$mem_rels = $this -> getCrmUidByMid( $member_ids ) ; //汉购会员与CRM会员ID映射
		$items = $this -> getOrderItems( $oids ) ;
	
		$order_list = array() ;
		foreach( $b2c_orders as $orders )
		{
			if( !$orders['buyer_id'] ) continue ;
			$member_id = $orders['buyer_id'] ;
			$order_id = $orders['order_id'] ;
			if( !isset($items[ $order_id ]) || empty( $items[ $order_id ] ) ) continue ;
	
			$uid = "" ;
			if( $orders['order_from'] == '3' ) {
				//分销用户保存，并得到插入的会员ID
				$uid = $this -> getFxuserCrmuid( $orders ) ;
			} else {
				//普通用户
				$uid = $mem_rels[ $member_id ] ;
			}
	
			if( !$uid ) continue ;
			
			$discount = ($orders['goods_amount']+$orders['shipping_fee']>$orders['order_amount']) ? ($orders['goods_amount']+$orders['shipping_fee']-$orders['order_amount']) : 0 ;
	
			$order_list[] = array(
					'uid' => $uid,
					'order_sn' => $orders['order_sn'],
					'cur' => 'CNY',
					'amount' => number_format($orders['order_amount'], 2, '.', ''),
					'shiprice' => number_format($orders['shipping_fee'], 2, '.', ''),
					'discount' => number_format($discount, 2, '.', ''),
					'paytime' => $orders['payment_time'],
					'item' => $items[ $order_id ],
					'member_id' => $member_id
			) ;
		}
	
		return $order_list ;
	}
	
	/**
	 * 根据汉购网会员ID查找CRM系统UID
	 */
	function getCrmUidByMid( $member_ids )
	{
		if( !is_array($member_ids) || count($member_ids) < 1 ) return array() ;

		$model_member = TModel('member') ;
		$condition = array() ;
		$condition['member_id'] = array('in', $member_ids) ;
		$members = $model_member -> where ($condition) -> select () ;
	
		return array_column( $members, 'crm_member_id', 'member_id' ) ;
	}
	
	/**
	 * 根据订单ID数组获取订单商品信息
	 */
	function getOrderItems( $oids )
	{
		if( !is_array($oids) || count($oids) < 1 ) return array() ;
	
		//订单商品表
		$model_order_goods = TModel('order_goods') ;
		$condition = array() ;
		$condition['order_id'] = array('in', $oids) ;
		$order_items = $model_order_goods -> where ( $condition ) -> select () ;
		$goods_ids = $order_items ? array_unique( array_column( $order_items, 'goods_id' ) ) : array() ;
		$bns = $this -> getBnByGoodsids( $goods_ids ) ;
	
		$items = array() ;
		foreach ( $order_items as $row )
		{
			$order_id = $row['order_id'] ;
			$product_id = $row['goods_id'] ;
			//$goods_id = $row['goods_id'] ;
			$items[ $order_id ][] = array(
					'goods_name' => $row['goods_name'],
					'litpic' => cthumb($row['goods_image'], 60, $row['store_id']),
					'goods_id' => $product_id,
					'sku' => isset( $bns[ $product_id ] ) ? $bns[ $product_id ] : "",
					'num' => $row['goods_num'],
					'price' => $row['goods_price'],
			) ;
		}
	
		return $items ;
	}
	//获取商品货号
	function getBnByGoodsids( $gids )
	{
		if( !is_array($gids) || count($gids) < 1 ) return array() ;
		
		$model_goods = TModel('goods') ;
		$condition = array() ;
		$condition['goods_id'] = array('in', $gids) ;
		$goods = $model_goods -> field('goods_id,goods_serial') -> where ( $condition ) -> select () ;
		
		return $goods ? array_column($goods, 'goods_serial', 'goods_id') : array() ;
	}
	/**
	 * 判断订单信息中的手机号是否已保存到b2c_member_fenxiao表，已保存查找crm_member_id返回，未保存则保存，并推送到CRM获取UID
	 * return crm_member_id
	 */
	function getFxuserCrmuid( $order )
	{
		if( !$order['buyer_phone'] ) return "" ;
		$mobile = $order['buyer_phone'] ;
		$member_id = $order['buyer_id'] ;

		$model_member_fenxiao = ecModel('b2c_member_fenxiao') ;
		$condition = array() ;
		$condition['mobile'] = $mobile ;
		$condition['member_id'] = $member_id ;
		$row = $model_member_fenxiao -> where ( $condition ) -> find() ;
		if( !$row )
		{
			$condition = array() ;
			$condition['order_id'] = $order['order_id'] ;
			$order_common = TModel('order_common') -> where ( $condition ) -> find () ;
			if( !$order_common ) return "" ;
			
			$reciver_info = unserialize($order_common['reciver_info']) ;
			list($province, $city, $area) = explode(" ", $reciver_info['area']) ;
			$data = array(
				'mobile' => $order['buyer_phone'] ,
				'email' => $order['buyer_email'] ,
				'name' => $order_common['reciver_name'] ,
				'province' => $province ,
				'city' => $city?$city:"" ,
				'area' => $area?$area:"" ,
				'addr' => $reciver_info['address'] ,
				'time' => $order['add_time'] ,
				'member_id' => $member_id ,
			) ;
			$uid = "" ;
			if( $model_member_fenxiao -> data( $data ) -> add () )
			{
				$params = $this -> structFxuserData( $data ) ;
				$res = $this -> sdk -> add_user( $params ) ;
				$r = json_decode( $res, true ) ;v($r, 0);
				if( isset( $r['errcode'] ) && $r['errcode'] == "0" )
				{
					if( isset( $r['uid'] ) && $r['uid'] ) {
						//更新b2c_member_fenxiao表CRM会员ID字段(老平台)
						$filter = array('mobile' => $mobile, 'member_id' => $member_id) ;
						$data = array('crm_member_id' => $r['uid']) ;
						$model_member_fenxiao -> where( $filter ) -> save( $data ) ;
						$uid = $r['uid'];
					}
				}
			}
		}
		else
		{
			$uid = $row['crm_member_id'] ;
		}

		return $uid ;
	}
	
	function structFxuserData( $member )
	{
		return array(
				'uname' => $member['mobile'],
				'truename' => strval($member['name']),
				'sex' => 3,
				'brithday' => "",
				'mobile' => $member['mobile'],
				'email' => $member['email'],
				'avatar' => '',
				'contry' => '中国',
				'provine' => $member['province'],
				'city' => $member['city'],
				'area' => $member['area'],
				'address' => $member['address'],
				'regtime' => $member['time'],
				'member_id' => $member['member_id']
		) ;
	}
}