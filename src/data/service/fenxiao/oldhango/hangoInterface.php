<?php

class hangoInterface {
	
	private $router = "" ;
	private $shipapi = "" ;
	private $onlineDate = "2016-08-29" ;//上线日期
	
	function __construct()
	{
		$this -> router = C('EC_API_HOST') . 'method=b2c.order.b2b2c.index&sign=eff90f9f07d591ac969dfc4750674ce2' ;
		$this -> shipapi = C('EC_API_HOST') . 'method=b2c.order.b2b2c.ship_update&sign=eff90f9f07d591ac969dfc4750674ce2' ;
	}

	//获取订单列表
	function getOrderList( $begin="", $end="" )
	{
		$params = array(
			'start_time' => $begin,
			'end_time'	=> $end
		) ;

		$flag = 1;
		$page_no = 1;
		$order_data = array();
		while( $flag ) {

			$params['page_no'] = $page_no ;
			$result = self::curlGet($this -> router, $params) ;
			$result = json_decode($result,true);
			
			if( $result['rsp'] == 'succ' && !empty($result['data']['trades']) )
			{
				foreach ($result['data']['trades'] as $k => $detail) {
					//不处理上线日期之前的订单
					if( $detail['order_time'] < strtotime( $this -> onlineDate )  ) continue ;
					
					$order_data[] = $detail;
				}
			}

			$page_no ++ ;
			if( $result['data']['has_next'] != 'true' ) {
				$flag = 0;
			}
		}

		return $order_data ;
	}
	
	//订单发货
	function pushShip( $params )
	{
		$data = array(
				'order_sn' => $params['oid'],
				'logi_no' => $params['logi_no'],
				'logi_name' => $params['logi_name'],
		) ;
		$result = self::curlGet($this -> shipapi, $data) ;
		return $result ;
	}
	
	static function curlPost($url, $data)
	{
		$ch = curl_init ();
		curl_setopt ( $ch, CURLOPT_URL, $url );
		curl_setopt ( $ch, CURLOPT_POST, 1 );
		curl_setopt ( $ch, CURLOPT_HEADER, 0 );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt ( $ch, CURLOPT_POSTFIELDS, $data );
		$return = curl_exec ( $ch );
		curl_close ( $ch );

		return $return ;
	}
	static function curlGet($url, $data)
	{
		$url .= "&".http_build_query( $data ) ;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);

		$output = curl_exec($ch);
		curl_close($ch);

		return $output ;
	}

	//10秒内多次尝试获取接口数据
	static function curlGetTrue($url, $params, $timeout=3) {
		$url .= "?".http_build_query( $params ) ;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);

		$output = curl_exec($ch);
		curl_close($ch);

		if( !$output ){
			$flag = 1 ;
			$begin = time() ;
			$i = 1;
			while($flag){
				//logger::selflog( "curlget 第{$i}次尝试" , 'renrendian') ;
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_HEADER, 0);

				$output = curl_exec($ch);
				curl_close($ch);

				if( $output || time() - $begin > 10 ) {
					$flag = 0 ;
				}
				$i ++ ;
				sleep(1) ;
			}
		}

		return $output;
	}

	function curlPostTrue($url, $params, $timeout=6) {
		$ch = curl_init ();
		curl_setopt ( $ch, CURLOPT_URL, $url );
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
		curl_setopt ( $ch, CURLOPT_POST, 1 );
		curl_setopt ( $ch, CURLOPT_HEADER, 0 );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt ( $ch, CURLOPT_POSTFIELDS, $params );
		$return = curl_exec ( $ch );
		curl_close ( $ch );

		if( !$return ){
			$flag = 1 ;
			$begin = time() ;
			$i = 1;
			while($flag){
				//logger::selflog( "curlpost 第{$i}次尝试" , 'renrendian') ;
				$ch = curl_init ();
				curl_setopt ( $ch, CURLOPT_URL, $url );
				curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
				curl_setopt ( $ch, CURLOPT_POST, 1 );
				curl_setopt ( $ch, CURLOPT_HEADER, 0 );
				curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
				curl_setopt ( $ch, CURLOPT_POSTFIELDS, $params );
				$return = curl_exec ( $ch );
				curl_close ( $ch );

				if( $return || time() - $begin > 10 ) {
					$flag = 0 ;
				}
				$i ++ ;
				sleep(1) ;
			}
		}

		return $return;
	}
}

?>