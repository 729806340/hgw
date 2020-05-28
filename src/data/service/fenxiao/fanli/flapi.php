<?php

class flapi {
	
	private $onlineDate = "2017-04-01 00:00:00" ;//上线日期
	//沙箱地址
//static $router = "http://sandbox.open.shzyfl.cn:8080/";
//static $appkey = "OUFFQzFGMUE0NDlCNkUyRg==";
//static $secret = "13d004efc54605508909c27f10f1a01b";
//static $grantkey = "NzBjM2MyZWM0NTlmODAzMTUxMWJhYmExNjA2N2M1MTY=";
//static $grantsecret = "4510ee557900772f3e65b41008279292";
	//正式地址
	static $router = "http://open.shzyfl.cn/";
    static $appkey = "Q0VFQkE1OEU1Q0FEMDM3MQ==";
    static $secret = "55f9e420cbae16c8dc2c38fdf1995244";
    static $grantkey = "YTU1MDc1MmVkODIxNDBjMzBkZDc2NDU1NmRhNDQ5ODg=";
    static $grantsecret = "9ac8711959a5dee4d41359b14591edc3";
	function __construct()
	{
		
	}

	//获取订单列表
	function getOrderList( $condition=array() )
	{
		$params = array(
			'startCreated' => date('Y-m-d H:i:s', $condition['begin']) ,
			'endCreated'	=> date('Y-m-d H:i:s', $condition['end']) ,
			'page' => $condition['page_no'],
			'count' => $condition['page_size'],
			'orderStatus' => '1'
		) ;

		$res = $this->getApi('api/1/order/trade/getlist', json_encode($params)) ;
        log::selflog('返利抓取日志! method:api/1/order/trade/getlist; params:' . json_encode($params).'; response:'.$res,'fanli');
        $result = json_decode($res,true);
		
		$order_data = array();
		$hasNext = 0 ;
		if( $result['success'] && $result['responseCode'] == 0 && !empty($result['result']) )
		{
			foreach ($result['result'] as $k => $detail) {
				//不处理上线日期之前的订单
				if( strtotime($detail['orderTime']) < strtotime( $this -> onlineDate )  ) continue ;
		
				$order_data[] = $detail ;
			}
				
			$hasNext = $result['hasNext'] ? 1 : 0 ;
		}
		
		return array($hasNext, $order_data) ;
	}
	
	function getOrderDetail($orderno, $condition=array())
	{
		$detail_params = array();
		$detail_params['orderCode'] = $orderno;
		empty($condition['begin']) && $condition['begin'] = time() - 86400*88;
		empty($condition['end']) && $condition['end'] = time();

		$detail_params['startCreated'] = date('Y-m-d H:i:s', $condition['begin']) ;
		$detail_params['endCreated'] = date('Y-m-d H:i:s', $condition['end']) ;
		return $this->getApi('api/1/order/trade/getone', json_encode($detail_params))  ;
	}
	function getSkuList($params){
		$detail_params = array();
		$detail_params['count'] = $params['page_size'];
		$detail_params['page'] = $params['page_no'];
		return $this->getApi('api/1/product/search/list', json_encode($detail_params), 'get')  ;
	}
	
	//订单发货
	function pushShip( $params )
	{
		$detail_params = array();
		$detail_params['deliveryItem']['orderCode'] = $params['orderno'];//订单号
		$detail_params['deliveryItem']['expressNo'] = $params['logi_no'];//物流单号
		$detail_params['deliveryItem']['expressCode'] = $params['logi_code'];//物流代码
		$detail_params['deliveryItem']['expressCompany'] = $params['logi_name'];//物流公司
		
		return $this->getApi('api/1/order/delivery/add', json_encode($detail_params), 'post')  ;
	}
	
	//维权订单列表
	function getRefundList( $condition = array() )
	{
		if( $condition['begin'] < strtotime( $this -> onlineDate ) ) $condition['begin'] = strtotime($this -> onlineDate) ;
		$params = array(
				'startCreated' => date('Y-m-d H:i:s', $condition['begin']) ,
				'endCreated'	=> date('Y-m-d H:i:s', $condition['end']) ,
				'page' => $condition['page_no'],
				'count' => $condition['page_size']
		) ;

		$res = $this->getApi('api/1/order/refund/getlist', json_encode($params)) ;
		$result = json_decode($res,true);
		
		$order_data = array();
		$hasNext = 1 ;
		if( $result['success'] && $result['responseCode'] == 0 && !empty($result['result']) )
		{
			foreach ($result['result'] as $k => $detail) {
				$order_data[] = $detail;
			}
				
			$hasNext = $result['hasNext'] ? 1 : 0 ;
		}
		
		return array($hasNext, $order_data) ;
	}
	
	function getRefundDetail($orderno, $refundno, $condition=array())
	{

		empty($condition['begin']) && $condition['begin'] = time() - 86400*88;
		empty($condition['end']) && $condition['end'] = time();
		
		$detail_params = array();
		$detail_params['orderCode'] = $orderno;
		$detail_params['exOrderCode'] = $refundno;
		$detail_params['startCreated'] = date('Y-m-d H:i:s', $condition['begin']) ;
		$detail_params['endCreated'] = date('Y-m-d H:i:s', $condition['end']) ;
		
		return $this->getApi('api/1/order/refund/get', json_encode($detail_params))  ;
	}
	
	function getApi($method, $params, $http='get')
	{
		$data = array();
		$time=time();
		$data['timestamp'] =$time;
		$data['appKey'] = self::$appkey ;
		$data['userKey'] = self::$grantkey;
		$data['params'] = $params ;
		$data['sign'] = md5('appKey'.self::$appkey.'params'.$params.'timestamp'.$time.'userKey'.self::$grantkey.self::$secret.self::$grantsecret);
		if( $http=='post' ) {
			$res = self::curlPost(self::$router.$method, $data) ;
		} else {
			$res = self::curlGet(self::$router.$method, $data) ;
		}
		
		return $res ;
	}
	
	function getToken()
	{
		$data['appid'] = self::$appid;
		$data['secret'] = self::$secret;
		$res = self::curlGet($this->grantUrl, $data) ;
		return $res ;
	}
	
	function setToken($token)
	{
		$this->token = $token ;
	}
	
	//从data目录读取token
	function readToken( &$re )
	{
		$retmsgSavePath = BASE_DATA_PATH.'/log/' ;
		$filename = $retmsgSavePath . "/mengdian_token.txt";
		$data = file_get_contents ( $filename ) ;
		$re = json_decode( $data , true ) ;
		if( is_array($re) && isset( $re['data']['access_token'] ) ) {
			
			//距过期时间还有半小时刷新token
			if( time() - $re['addtime'] < ($re['data']['expire_in']-30*60) ) {
				$this->setToken($re['data']['access_token']) ;
				return true ;
			}
			
		} 
		
		$result = $this -> getToken() ;
		if( $this -> saveToken ( $result ) ) {
			$re = json_decode( $result, true ) ;
			if( is_array($re) && isset( $re['data']['access_token'] ) ) {
				$this->setToken($re['data']['access_token']) ;
				return true ;
			} 
		}
			
		return false ;
	}
	
	//保存token到data目录
	function saveToken( $json )
	{
		$re = json_decode( $json, true ) ;
		if( is_array( $re ) && isset( $re['data']['access_token'] ) ) {
			$re['addtime'] = time();
				
			$retmsgSavePath = BASE_DATA_PATH.'/log/' ;
			$filename = $retmsgSavePath . "/mengdian_token.txt";
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
		log::selflog(var_export($data,true),'fanli');

		return $return ;
	}
	static function curlGet($url, $data)
	{
		$url .= "?".http_build_query( $data ) ;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

		$output = curl_exec($ch);
		curl_close($ch);

		return $output ;
	}
}

?>