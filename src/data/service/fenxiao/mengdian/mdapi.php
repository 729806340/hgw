<?php

class mdapi {
	
	private $router = "" ;
	private $onlineDate = "2016-11-30 09:00:00" ;//上线日期
	public $token = "" ;
	static $appid = "09528567279dc36121547006f2242070" ;
	static $secret = "235c5c590af1b537a809808fa556b6f1" ;
	
	function __construct()
	{
		$this -> grantUrl = "https://open.mengdian.com/common/token?grant_type=client_credential";//获取token的URL
		$this -> router = 'https://open.mengdian.com/api/mname/WE_MALL/cname/' ;
		if( !$this->readToken($re) ) log::selflog('get access token faild', 'mengdian') ;
	}

	//获取订单列表
	function getOrderList( $condition=array() )
	{
		$params = array(
			'start_time' => date('Y-m-d H:i:s', $condition['begin']) ,
			'end_time'	=> date('Y-m-d H:i:s', $condition['end']) ,
			'page_no' => $condition['page_no'],
			'page_size' => $condition['page_size'],
		) ;

		$result = $this->_apiOrderList($params) ;
		$result = json_decode($result,true);
		
		$order_data = array();
		$total_page = 1 ;
		if( $result['code']['errcode'] == 0 && !empty($result['data']['page_data']) )
		{
			foreach ($result['data']['page_data'] as $k => $detail) {
				//不处理上线日期之前的订单
				if( strtotime($detail['create_time']) < strtotime( $this -> onlineDate )  ) continue ;
				
				$order_data[] = $detail;
			}
			
			$total_page = $result['data']['page_count'] ;
		}

		return array($total_page, $order_data) ;
	}

	function getSkuList($params){
		$detail_params = array();
		$detail_params['page_size'] = $params['page_size'];
		$detail_params['page_no'] = $params['page_no'];
		$detail_params['is_onsale'] = 1;
		return self::curlPost($this->getApi('spuFullInfoGet'), json_encode($detail_params)) ;
	}
	
	//接口文档：http://open.mengdian.com/doc/apiarticle/tag/jc_doc
	function getOrderDetail($order_no)
	{
		$params['order_no'] = $order_no ;
		
		return self::curlPost($this->getApi('orderFullInfoGetHighly'), json_encode($params)) ;
	}
	
	//接口文档地址：http://open.mengdian.com/doc/apiarticle/tag/ic_doc
	function _apiOrderList($conditon)
	{
		$params['order_status'] = 1; //订单状态(1交易中,2交易成功,3交易关闭，空值代表所有)
		$params['pay_status'] = 1; //订单支付状态(0待支付，1已支付，空值代表所有)
		$params['delivery_status'] = 0; //物流状态(0待发货，1卖家发货,2买家收货，空值代表所有)
		$params['create_begin_time'] = $conditon['start_time'];
		$params['create_end_time'] = $conditon['end_time'];
		$params['page_size'] = $conditon['page_size'] ? $conditon['page_size'] : 100;
		$params['page_no'] = $conditon['page_no'] ? $conditon['page_no'] : 1;
		
		return self::curlPost($this->getApi('orderGetHighly'), json_encode($params)) ;
	}
	
	//订单发货
	function pushShip( $params )
	{
		$deliveries = array();
		$deliveries['order_no'] = $params['orderno'] ;
		$deliveries['need_delivery'] = true ;
		$deliveries['carrier_code'] = $params['logi_code'] ;
		$deliveries['carrier_name'] = $params['logi_name'] ;
		$deliveries['express_no'] = $params['logi_no'] ;
		$deliveries['remark'] = null ;
		$deliveries['sender_address'] = "湖北 武汉 江汉区 江汉经济开发区江旺路6号";
		$deliveries['sender_name'] = "汉购网";
		$deliveries['sender_tel'] = "13800138000";
		$json['deliveries'][] = $deliveries ;
		
		return self::curlPost($this->getApi('logisticsDelivery'), json_encode($json)) ;
	}
	
	//维权订单列表
	function getRefundList( $condition = array() )
	{
		$params = array(
				'start_time' => date('Y-m-d H:i:s', $condition['begin']) ,
				'end_time'	=> date('Y-m-d H:i:s', $condition['end']) ,
				'page_no' => $condition['page_no'],
				'page_size' => $condition['page_size'],
		) ;
		
		$result = $this->_apiRefundList($params) ;
		$result = json_decode($result,true);
		
		$order_data = array();
		$total_page = 1 ;
		if( $result['code']['errcode'] == 0 && !empty($result['data']['page_data']) )
		{
			foreach ($result['data']['page_data'] as $k => $detail) {
				//不处理上线日期之前的订单
				if( strtotime($detail['create_time']) < strtotime( $this -> onlineDate )  ) continue ;
		
				$order_data[] = $detail;
			}
				
			$total_page = $result['data']['page_count'] ;
		}
		
		return array($total_page, $order_data) ;
	}
	
	//接口文档地址：http://open.mengdian.com/doc/apiarticle/tag/qc_doc
	function _apiRefundList($conditon)
	{
		$params['return_order_status'] = 0; //0全部，1买家发起退款申请，2买家发起退款退货申请，3商家同意退款申请，4等待买家退货，5专家已收货，7已退款退货，8已拒绝，9已取消，10微盟支付处理中
		$params['update_begin_time'] = $conditon['start_time'];
		$params['update_end_time'] = $conditon['end_time'];
		$params['page_size'] = $conditon['page_size'] ? $conditon['page_size'] : 50;
		$params['page_no'] = $conditon['page_no'] ? $conditon['page_no'] : 1;
	
		return self::curlPost($this->getApi('returnorderGetPaging'), json_encode($params)) ;
	}
	
	function getApi($method)
	{
		return $this->router . $method . "?accesstoken=" . $this->token ;
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
				$result = $this -> getToken() ;
				if( $this -> saveToken ( $result ) ) {
					$re = json_decode( $result, true ) ;
					if( is_array($re) && isset( $re['data']['access_token'] ) ) {
						$this->setToken($re['data']['access_token']) ;
						return true ;
					}
				}
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
	
	static function curlPost($url, $json)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				'Content-Type: application/json',
				'Content-Length: ' . strlen($json))
		);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		$return = curl_exec ( $ch );

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
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

		$output = curl_exec($ch);
		curl_close($ch);

		return $output ;
	}
}

?>