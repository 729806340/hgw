<?php
/**
 * @require PHP>=5.3
 */
 require_once __DIR__ . '/KdtApiProtocol.php';
require_once __DIR__ . '/SimpleHttpClient.php';

class kdtClient {
	const VERSION = '1.0';
	
	private static $apiEntry = 'https://open.koudaitong.com/api/entry';
	
	private $appId;
	private $appSecret;
	private $format = 'json';
	private $signMethod = 'md5';
	private $protocol;
	private $http;
	
	public function init($appId, $appSecret) {
		if ('' == $appId || '' == $appSecret) throw new Exception('appId 和 appSecret 不能为空');
		
		$this->appId = $appId;
		$this->appSecret = $appSecret;
		
		$this->protocol = new KdtApiProtocol();
		$this->http = new SimpleHttpClient();
	}
	
	public function get($method, $params = array()) {
		return $this->parseResponse(
			$this->http->get(self::$apiEntry, $this->buildRequestParams($method, $params))
		);
	}
	
	public function post($method, $params = array(), $files = array()) {
		return $this->parseResponse(
			$this->http->post(self::$apiEntry, $this->buildRequestParams($method, $params), $files)
		);
	}
	
	
	
	public function setFormat($format) {
		if (!in_array($format, $this->protocol->allowedFormat()))
			throw new Exception('设置的数据格式错误');
		
		$this->format = $format;
		
		return $this;
	}
	
	public function setSignMethod($method) {
		if (!in_array($method, $this->protocol->allowedSignMethods()))
			throw new Exception('设置的签名方法错误');
		
		$this->signMethod = $method;
		
		return $this;
	}
	
	

	private function parseResponse($responseData) {
		$data = json_decode($responseData, true);
		if (null === $data) throw new Exception('response invalid, data: ' . $responseData);
		return $data;
	}
	
	private function buildRequestParams($method, $apiParams) {
		if (!is_array($apiParams)) $apiParams = array();
		$pairs = $this->getCommonParams($method);
		foreach ($apiParams as $k => $v) {
			if (isset($pairs[$k])) throw new Exception('参数名冲突');
			$pairs[$k] = $v;
		}
		$protocol = $this->protocol;
		$pairs[$protocol::SIGN_KEY] = $protocol->sign($this->appSecret, $pairs, $this->signMethod);
		return $pairs;
	}
	
	private function getCommonParams($method) {
		$protocol = $this->protocol;
		$params = array();
		$params[$protocol::APP_ID_KEY] = $this->appId;
		$params[$protocol::METHOD_KEY] = $method;
		$params[$protocol::TIMESTAMP_KEY] = date('Y-m-d H:i:s');
		$params[$protocol::FORMAT_KEY] = $this->format;
		$params[$protocol::SIGN_METHOD_KEY] = $this->signMethod;
		$params[$protocol::VERSION_KEY] = self::VERSION;
		return $params;
	}
}
