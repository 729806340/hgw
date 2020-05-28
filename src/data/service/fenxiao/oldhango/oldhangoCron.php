<?php

require_once 'hangoInterface.php';

class oldhangoCron
{

	static $source = "oldhango" ;
	
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
	
	//获取人人店订单
	function orderlist( $params = array() ) 
	{
		$service = $params['service'] ;
		
		$beginTime = $params['begin'] ? strtotime($params['begin']) : $this->timestamp - 3600 ;
		$endTime = $params['end'] ? strtotime($params['end']) : $this->timestamp ;

		$urls = array() ;
		
		$hgInterface = new hangoInterface() ;
	
		$res = $hgInterface->getOrderList( date('Y-m-d H:i:s', $beginTime), date('Y-m-d H:i:s', $endTime) ) ;
		//v($res);
		if( empty($res) ) die ;
		$apiOrdernos = array_column($res, 'order_sn') ;
		log::selflog("rrd api：".count($res) . " | ". json_encode( $apiOrdernos ), self::$source) ;

		$savedOrderIds = $service -> getSavedidByApiorderno( $apiOrdernos ) ;
		log::selflog("saved orders:{$filter['startDate']} - {$filter['endDate']}" . json_encode($savedOrderIds), self::$source) ;
	
		$this -> noRelNums = 0 ;
		$order_data = $this -> bns = array() ;
		
		foreach ( $res as $order ) {
			
			if( in_array($order['order_sn'], $savedOrderIds) ) continue ;
			
			foreach ( $order['item'] as $goodsrow )
			{
				$goods_id = $goodsrow['goods_id'] ;
				if( !isset( $this -> rel[ $goods_id ] ) || $this -> rel[ $goods_id ] == 0 ) {
					$this -> noRelNums++;
				}
				$this -> bns[] = $goodsrow['bn'] ;
			}
			
			$order_data[] = $order ;
		}

		//查找货号对应的商品ID
		if( !empty($this -> bns) && $this -> noRelNums ) {
			//新平台
			$bns = array_unique( $this -> bns ) ;
			$condition = array();
			$condition['goods_serial'] = array('in', $this -> bns) ;
			$result = TModel('goods') -> field('goods_id, goods_serial') -> where ( $condition ) -> select () ;
			$proids_new = array_column( $result, 'goods_id', 'goods_serial' );
		}
	
		$datas = array() ;

		foreach($order_data as &$order) {
			
			$continue = 0 ;
			$goods_total = 0;
			foreach ($order['item'] as $k => $_item ) {
				$fx_goods_id = $_item['goods_id'];
				$bn = $_item['bn'];
				if( isset( $this -> rel[ $fx_goods_id ] ) && $this -> rel[ $fx_goods_id ] ) {
					$_item['goods_id'] = $this -> rel[ $fx_goods_id ] ;
					$_item['platform'] = 'new' ;
					$new++;
				} else if( isset($proids_new[ $bn ]) && $proids_new[ $bn ] ) {
					$_item['goods_id'] = $proids_new[ $bn ] ;
					$_item['platform'] = 'new' ;
					$new++;
				} else {
					$this -> _error( $order['order_sn'], "分销商品 ".$_item['name']." id({$fx_goods_id}) 货号 ({$bn}) 找不到对应的汉购商品" );
					$continue = 1 ;
				}
				unset($_item['bn']);
				unset($_item['fx_goods_id']);
				$order['item'][$k] = $_item;
				
				$goods_total += $_item['num'] * $_item['price'] ;
			}
			if( $continue ) continue ;

			$order['platform'] = 'new' ;
			if( floatval($goods_total) > floatval($order['amount']) ) 
			{
				$order['discount'] = $goods_total - $order['amount'] ;
			}
			//$order['payment_code'] = 'fenxiao' ;
			if( $order['payment_code'] == 'deposit' ) {
				$order['payment_code'] = 'predeposit' ;
			} else if( $order['payment_code'] == 'malipay' ) {
				$order['payment_code'] = 'alipay' ;
			}
			$order['buy_id'] = $this -> member_id ;
			$datas[] = $order ;
		}
		
		$service -> doCreateOrder( $datas ) ;
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
		$hgInterface = new hangoInterface() ;
		$result = $hgInterface -> pushShip( $params );
		log::selflog("发货结果：" . $result) ;
		if( !$result ) {
			$this->_error($params['orderno'], json_encode($params), 'unship') ;
			$res = json_encode( array('succ' => '0', 'msg' => '发货失败') )  ;
		} else {

			$re = json_decode($result, true) ;
			if( $re['rsp'] != 'succ' ){
				$res = json_encode( array('succ' => '0', 'msg' => $error[ $errCode ]) ) ;
			} else {
				$res = json_encode( array('succ' => '1', 'msg' => '发货测试成功') )  ;
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
				'EMS' => 'ems',
				'顺丰快递' => 'shunfeng',
				'优速快递' => 'youshuwuliu',
				'天天快递' => 'tiantian',
				'宅急送' => 'zhaijisong',
				'快捷速递' => 'kuaijiesudi',
				'全峰快递' => 'quanfengkuaidi'
		) ;
		return $data[$name] ? $data[$name] : 'ems' ;
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
	
	

}