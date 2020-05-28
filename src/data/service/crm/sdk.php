<?php
/**
 * 推送会员、订单数据到CRM系统 sdk文件
 */
 
class crm_sdk{
	
	static $app_id = 8 ;
	static $app_key = '5d88044ea9852920e767f153d1f59b5f' ;
	
	function getKey( $member_id )
	{
		$fxMembers = Service("Fenxiao") -> getFenxiaoMembers() ;
		$fxname = isset($fxMembers[$member_id]) ? $fxMembers[$member_id] : '' ;
		
		switch( $fxname ) 
		{
			case 'pinduoduo' :
				$app_id = 13;
				$app_key = '5db7d0f9ee1690bea6bf65ed63a3e40a';
				break;
			case 'youzan' :
				$app_id = 11;
				$app_key = '40089fc197a79f5dabb243e313e036f0';
				break;
			case 'renrendian' :
				$app_id = 12;
				$app_key = '7b4b44dd86412f0c4951891c0f31df15';
				break ;
			case 'oldhango' :
				$app_id = 17;
				$app_key = 'f4df1d9fb6bde02bb85939a521c43b17';
				break ;
			case 'fanli' :
				$app_id = 18;
				$app_key = 'cfc497df7e91c311a923937edaefa11a';
				break ;
			case 'zhe800' :
				$app_id = 19;
				$app_key = 'da7d128fc95ff88dcc7c0e576c1a3a3f';
				break ;
			case 'gegejia' :
				$app_id = 20;
				$app_key = '95c46beb942870cb8d343aa97aa835d5 ';
				break ;
			case 'mengdian' :
				$app_id = 21;
				$app_key = '4ad35e23d798c0197973894791ab2860';
				break ;
			case 'taobaofx' :
				$app_id = 22;
				$app_key = '6d3e2741c9917660e5e9495e382a1c85';
				break ;
			case 'juanpi' :
				$app_id = 23;
				$app_key = '1e3803ff7c6d37b7918ff03c3f1f3fd5';
				break ;
			case 'xiaomaolv' :
				$app_id = 24;
				$app_key = 'a4ec780c7e71a83e4874d58bf67291dd';
				break ;
			case 'lvjingnongchang' :
				$app_id = 25;
				$app_key = '81436846c446035f7ae9559b8e9b7a78';
				break ;
			case 'chuchujie' :
				$app_id = 26;
				$app_key = '9d45d1533a5999c4c4c1ebc423892c00';
				break ;
			case 'chuchujiephs' :
				$app_id = 27;
				$app_key = '73cedeca157af2208e4b3e885062f986';
				break ;
			case 'hanguiren' :
				$app_id = 28;
				$app_key = '1de4bdab03941d2b112f41f2f22ee73b';
				break ;
			case 'xunshizheshuo' :
				$app_id = 29;
				$app_key = '3d974fbc96e2ce41f5078b9081915038';
				break ;
			case 'hangofx' :
				$app_id = 30;
				$app_key = '0344e59cf31b35520967abc0540266af';
				break ;
			default :
				$app_id = 8;
				$app_key = '5d88044ea9852920e767f153d1f59b5f';
				break;
		}
		
		if( $fxname && $app_id == 8 ) die('no appid') ;
		
		return array(
			'app_id' => $app_id,
			'app_key' => $app_key
		) ;
	}
	
	/**
	* 增加会员
	*/
	function add_user( $params )
	{
		$member_id = $params['member_id'] ;
		$keys = $this -> getKey( $member_id ) ;
		
		unset($params['member_id']) ;
		$data = $params ;
		$data['address'] = mb_substr( $data['address'], 0, 35, 'utf-8' ) ;
		$data['app_id'] = $keys['app_id'];
		$data['app_key'] = $keys['app_key'];
		v($data, 0);
		$url = 'http://api.gobaowa.com/user/add';

		$output = $this -> curl_url($url, $data);
		return $output ;
	}
	
	/**
	* 编辑会员
	*/
	function edit_user( $params ){
		$data = $params ;
		$data['address'] = mb_substr( $data['address'], 0, 40, 'utf-8' ) ;
		$data['app_id'] = self::$app_id;
		$data['app_key'] = self::$app_key;
		
		$url = 'http://api.gobaowa.com/user/edit';
		
		$output = $this -> curl_url($url, $data);
		print_r($output);
	}
	
	/**
	* 添加订单
	*/
	function add_order( $params )
	{
		$member_id = $params['member_id'] ;
		$keys = $this -> getKey( $member_id ) ;
		unset($params['member_id']) ;
		$data = $params ;
		$data['app_id'] = $keys['app_id'];
		$data['app_key'] = $keys['app_key'];
		$url = 'http://api.gobaowa.com/order/add';
		var_dump($data) ;
		
		$output = $this -> curl_url($url, $data);
		return $output ;
	}
	
	/**
	* 取消订单
	*/
	function cancel_order( $order_sn ){
		$data['app_id'] = self::$app_id;
		$data['app_key'] = self::$app_key;
		$data['order_sn'] = $order_sn ;
		$url = 'http://api.hangowa.com/order/cancel';
		
		$output = $this -> curl_url($url, $data);
		print_r($output);
	}
	
	/** 
	* 会员积分操作 
	* @param int operate：1.增加 2.扣除 3.冻结
	*/
	function point(){
		$data['app_id'] = APP_ID;
		$data['app_key'] = APP_KEY;
		$data['uid'] = 17;
		$data['point'] = 20;
		$data['operate']=4;
		$data['note'] = '签到登陆qq';
		$url = 'http://api.gobaowa.com/point/add';

		$output = $this -> curl_url($url, $data);
		print_r($output);
	}
    
    
	/**
	 * 请求接口通用函数
	 *
	 * @param array $params
	 *            请求参数（有值为post请求方式）
	 * @param array $headers
	 *            头部信息
	 * @return string $ret 返回信息
	 */
	function curl_url($url, $params = array(), $headers = array())
	{
		$params = $this -> JSON($params) ;
		$ch = curl_init();
		$ssl = substr($url, 0, 8) == "https://" ? TRUE : FALSE;
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		if (! empty($params)) {
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
		}
		if (! empty($headers))
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		if ($ssl) {
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		}
		$output = curl_exec($ch);
		curl_close($ch);
		return $output;
	}

	function arrayRecursive(&$array, $function, $apply_to_keys_also = false)
	{
		static $recursive_counter = 0;
		if (++ $recursive_counter > 10000) {
			die('possible deep recursion attack');
		}
		foreach ($array as $key => $value) {
			if (is_array($value)) {
				$this->arrayRecursive($array[$key], $function, $apply_to_keys_also);
			} else {
				$array[$key] = $function($value);
			}
			if ($apply_to_keys_also && is_string($key)) {
				$new_key = $function($key);
				if ($new_key != $key) {
					$array[$new_key] = $array[$key];
					unset($array[$key]);
				}
			}
		}
		$recursive_counter --;
	}

	function JSON($array)
	{
		$this->arrayRecursive($array, 'urlencode', true);
		$json = json_encode($array);
		return urldecode($json);
	}
	
}
?>


