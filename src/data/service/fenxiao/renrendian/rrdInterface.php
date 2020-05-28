<?php

class rrdInterface {
	
    //测试
	/*static $appid = "af1b913cec3c039b";
	static $secret = "9addfb49af1b913cec3c039b2d6fdbe5" ;
	static $router = "http://api.weiba05.com/router/rest" ;
	static $domain = "http://api.weiba05.com/";
	static $refresh_token = "22bbacb5354a618eda366a240a2e4ae2";*/
	//生产
	static $appid = "5356714791a4f20a";
	static $secret = "3f935b4b5356714791a4f20a934aa5d9" ;
	static $router = "http://apis.wxrrd.com/router/rest" ;
	static $domain = "http://apis.wxrrd.com/";
	static $refresh_token = "b8fe232b9cb801a3c83150f699d1854e";
	
	//跳转到授权页面，获取人人店接口token
	function getToken()
	{
		$url = self::$domain . "authorize";
		$params = array(
			'appid' => self::$appid,
			'response_type' => 'code',
			'redirect_uri' => 'http://www.hangowa.com/callback-rrd.html'
		) ;
		$url .= "?" . http_build_query( $params ) ;
		header("Location:" . $url);
	}
	
	//发货
	function pushShip( $data )
	{
		$result = $re = "" ;
		if( $this -> readToken( $re ) ) 
		{
			$params = array(
				'appid' => self::$appid ,
				'secret' => self::$secret,
				'method' => 'weiba.wxrrd.trade.send',
				'timestamp' => date("Y-m-d H:i:s"),
				'access_token' => $re['access_token'],
				'order_sn' => $data['orderno'],
				'logis_no' => $data['logi_no'],
				'sub_order_info' => '[{"oid":"'.$data['oid'].'","quantity":'.$data['num'].'}]',
				'logis_code' => $data['logi_code']
			) ;
			$params = self::getSign( $params ) ;
			log::selflog("push params:" . var_export($params, true), 'renrendian') ;
			$result = self::curlPost(self::$router, $params) ;
		}
		
		return $result ;
	}
	
	//获取订单列表
	function getOrderList( $type = 'tosend', $begin="", $end="" )
	{
		//return $this -> demoList($type) ;
		$re = "" ;
		$limit = 100 ;
		if( $this -> readToken( $re ) ) 
		{
			$params = array(
				'appid' => self::$appid ,
				'secret' => self::$secret,
				'method' => 'weiba.wxrrd.trade.lists',
				'timestamp' => date("Y-m-d H:i:s"),
				'access_token' => $re['access_token'],
				'type' => $type,
				'limit' => $limit
			) ;
			if( $begin && $end ){
				$params['created_at_start'] = $begin;
				$params['created_at_end'] = $end;
			}

			$flag = 1;
			$page_no = 0;
			$order_data = array();
			while( $flag ) {

				if( isset($params['sign']) ) unset($params['sign']) ;
				$params['offset'] = $page_no * $limit ;
				$params = self::getSign( $params ) ;
				//$result = self::curlPost(self::$router, $params) ;
				$result = self::curlGetTrue(self::$router, $params) ;
				$result = json_decode($result,true);
				$total_page = ceil( $result['_count'] / $limit );
				foreach ($result['data'] as $k => $value) {
					# code...
					$detail = $this -> getOrderDetail( $value['order_sn'] );
					if( !empty($detail) ) {
						$order_data[] = $detail;
					}
				}

				$page_no ++ ;
				if( $page_no >= $total_page ) {
					$flag = 0;
				}
			}
		}

		return $order_data ;
	}
	
	//获取订单详情
	function getOrderDetail( $order_sn )
	{	
		//return $this->demoDetail($order_sn) ;
		$re = "" ;
		if( $this -> readToken( $re ) ) 
		{
			$params = array(
				'appid' => self::$appid ,
				'secret' => self::$secret,
				'method' => 'weiba.wxrrd.trade.details',
				'timestamp' => date("Y-m-d H:i:s"),
				'access_token' => $re['access_token'],
				'order_sn' => $order_sn
			) ;
			$params = self::getSign( $params ) ;
			//$result = self::curlPost(self::$router, $params) ;
			$result = self::curlGetTrue(self::$router, $params) ;
			$re = json_decode($result, true) ;
			return $re['errCode'] == 0 ? $re['data'] : array() ;
		}
	}
	
	//物流公司列表
	function getShippingCompany()
	{
		$re = "" ;
		if( $this -> readToken( $re ) ) 
		{
			$params = array(
				'appid' => self::$appid ,
				'secret' => self::$secret,
				'method' => 'weiba.wxrrd.shipping.company',
				'timestamp' => date("Y-m-d H:i:s"),
				'access_token' => $re['access_token']
			) ;
			$params = self::getSign( $params ) ;
			
			$result = self::curlPost(self::$router, $params) ;
			var_dump($result) ;
		}
	}
	
	//计算接口sign，并返回带sign的params
	private static function getSign( $params )
	{
		ksort( $params ) ;
		$str = "";
		foreach ( $params as $k => $v ) {
			$str .= $k."=".$v."&";
		}
		$str = substr($str,0,-1) ;
		$sign = strtoupper( md5( $str ) ) ;
		$params['sign'] = $sign ;
		return $params ;
	}

	function freshToken( $refresh_token )
	{
		$url = self::$domain . "token" ;
		$params = array(
			'appid' => self::$appid ,
			'secret' => self::$secret,
			'grant_type' => 'refresh_token',
			'refresh_token' => self::$refresh_token,
			'redirect_uri' => 'http://www.hangowa.com/callback-rrd.html'
		);
		$url .= "?" . http_build_query( $params ) ;
		$result = file_get_contents($url);
		return $result ;
	}
	
	//从data目录读取token
	function readToken( &$re )
	{
		$retmsgSavePath = BASE_DATA_PATH.'/log/' ;
		$filename = $retmsgSavePath . "/rrd_token.txt";
		$data = file_get_contents ( $filename ) ;
		$re = json_decode( $data , true ) ;
		if( is_array($re) && isset( $re['access_token'] ) ) {
			
			//token超过1天刷新token
			if( time() - $re['addtime'] > 24*3600 ) {
				$result = $this -> freshToken( $re['access_token'] ) ;
				if( $this -> saveToken ( $result ) ) {
					$re = json_decode( $result, true ) ;
					return true ;
				}
				return false ;
			}
			
			return true ;
			
		} 
			
		return false ;
	}
	
	//保存token到data目录
	function saveToken( $json )
	{
		$re = json_decode( $json, true ) ;
		if( is_array( $re ) && isset( $re['access_token'] ) ) {
			$re['addtime'] = time();
			
			$retmsgSavePath = BASE_DATA_PATH.'/log/' ;
			$filename = $retmsgSavePath . "/rrd_token.txt";
			touch($filename);
			$handle = fopen($filename, 'w');
			$data = json_encode( $re ) ;
			fwrite($handle, $data);
			fclose($handle);
			@chmod && @chmod($filename, 0744);
			return true ;
		}
		return false ;
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
		
		$errno = curl_errno( $ch );
		$info  = curl_getinfo( $ch );
		$info['errno'] = $errno;
		log::selflog(json_encode($info), 'renrendian') ;
		
		curl_close ( $ch );

		return $return ;
	}

	//10秒内多次尝试获取接口数据
	static function curlGetTrue($url, $params, $timeout=10) {
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

				if( $output || time() - $begin > 15 ) {
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
	
	function demoList( $type='tosend' )
	{
		$data = array(
					$this -> demoDetail( 'E2016082316204741523' ),
				);
		return $data ;
	}
	function demoDetail($ordersn)
	{
		$data = array(
				'E2016081714474741523' => array(
						'id' => '104057064',
						'amount' => '31.90',
						'goods_amount' => '31.90',
						'trade_sn' => "2016080221001004460260115306",
						'created_at' => "2016-08-17 14:47:56",
						"shipment_fee" => '0.00',
						'memo' => '',
						'order_sn' => 'E2016081714474741523',
						'memo' => '不要加二维码',
						'updated_at' => "2016-08-17 14:47:56",
						'coupon_amount_details' => array(),
						'order_consigner_addr' => array(
								'country_name' => '中国',
								'province_name' => '湖北省',
								'city_name' => '武汉市',
								'district_name' => '硚口区',
								'address' => '冶金街现代花园D区23门301',
								'consignee' => '哈哈',
								'mobile' => '15337134271',
						),
						"order_goods" => array(
								array(
										'id' => '4965905',
										'order_id' => 201608121414,
										'goods_id' => 100960669,
										'goods_name' => "【特价包邮】湖北宜城松花皮蛋20枚 Q弹爽滑",
										'quantity' => 1,
										'price' => "31.90",
								),
						),
						'order_refund' => array(
								array(
										'0' => array(
												'goods_id' => '100960669',
												'status' => '10',
												'amount' => '31.90',
												'reason' => '买/卖双方协商一致'
										),
								),
						)
				),
				/*'E2016081213395602002' => array(
						'id' => '104057064',
						'amount' => '31.90',
						'goods_amount' => '31.90',
						'trade_sn' => "2016080221001004460260115306",
						'created_at' => "2016-08-12 14:14:56",
						"shipment_fee" => '0.00',
						'memo' => '',
						'order_sn' => 'E2016081213395602002',
						'memo' => '不要加二维码',
						'updated_at' => "2016-08-12 14:14:56",
						'coupon_amount_details' => array(),
						'order_consigner_addr' => array(
								'country_name' => '中国',
								'province_name' => '湖北省',
								'city_name' => '武汉市',
								'district_name' => '硚口区',
								'address' => '冶金街现代花园D区23门301',
								'consignee' => '哈哈',
								'mobile' => '15337134271',
						),
						"order_goods" => array(
								array(
										'id' => '4965905',
										'order_id' => 201608121414,
										'goods_id' => 100960669,
										'goods_name' => "【特价包邮】湖北宜城松花皮蛋20枚 Q弹爽滑",
										'quantity' => 1,
										'price' => "31.90",
										'refund_status' => '41'
								),
						),
						'order_refund' => array(
								array(
										'0' => array(
												'goods_id' => '100960669',
												'status' => '10',
												'amount' => '31.90',
												'reason' => '买/卖双方协商一致'
										),
								),
						)
				),*/
				'E2016082316204741523' => array(
						'id' => '201608151459',
						'amount' => '57.80',
						'goods_amount' => '57.80',
						'trade_sn' => "2016080221001004460260115306",
						'created_at' => "2016-08-23 16:20:47",
						"shipment_fee" => '0.00',
						'memo' => '',
						'order_sn' => 'E2016082316204741523',
						'order_consigner_addr' => array(
								'country_name' => '中国',
								'province_name' => '湖北省',
								'city_name' => '武汉市',
								'district_name' => '硚口区',
								'address' => '冶金街现代花园D区23门301',
								'consignee' => '哈哈',
								'mobile' => '15337134271',
						),
						'coupon_amount_details' => array(),
						"order_goods" => array(
								array(
										'id' => '08151325',
										'order_id' => 201608151459,
										'goods_id' => 123,
										'goods_name' => "大汉口 热干面920g 8连包 老武汉的传统美味 非油炸 更鲜美",
										'quantity' => 1,
										'price' => "27.90",
										'refund_status' => '31'
								),
								array(
										'id' => '08151326',
										'order_id' => 201608151459,
										'goods_id' => 456,
										'goods_name' => "宜味淘新鲜散养土家鸡蛋30枚*1盒",
										'quantity' => 1,
										'price' => "29.90",
										'refund_status' => '31'
								),
						),
						"package" => array(
								/*array(
										'logis_code' => 'ems',
										'order' => array(
												array(
														'id' => '08151325',
														'order_id' => 201608151459,
														'goods_id' => 123,
														'goods_name' => "宜味淘新鲜散养土家鸡蛋30枚*1盒",
														'quantity' => 1,
														'price' => "27.90",
														'refund_status' => '31'
												),	
												array(
														'id' => '08151326',
														'order_id' => 201608151459,
														'goods_id' => 456,
														'goods_name' => "宜味淘新鲜散养土家鸡蛋30枚*1盒",
														'quantity' => 1,
														'price' => "29.90",
														'refund_status' => '41'
												),
										),
								),*/
						),
						'order_refund' => array(
								array(
										'0' => array(
												'goods_id' => '123',
												'status' => '31',
												'amount' => '27.90',
												'reason' => '买/卖双方协商一致'
										),
								),
								array(
										'0' => array(
												'goods_id' => '456',
												'status' => '31',
												'amount' => '29.90',
												'reason' => '买/卖双方协商一致'
										),
								),
						)
				),
		) ;
		return isset($data[$ordersn]) ? $data[$ordersn] : array() ;
	}
}

?>