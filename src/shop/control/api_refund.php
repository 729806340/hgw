<?php
/**
 *退款接口控制器
 *@author ljq
 *@date 2016-8-1
 */
class api_refundControl extends Control {
	
	public function indexOp() {
		header ( 'Content-Type:application/json; charset=utf-8' );
		header ( "Expires: Mon, 26 Jul 1997 05:00:00 GMT" );
		header ( "Cache-Control: no-cache" );
		header ( "Pragma: no-cache" );
		$params = file_get_contents ( "php://input" );
		$params = json_decode($params);
		$result = model('refund_return')->addApiRefund($params);
		die(JSON($result));
	}
}