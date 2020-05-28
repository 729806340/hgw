<?php
/**
 *退款接口控制器
 *@author ljq
 *@date 2016-8-1
 */
class api_refund_editControl extends Control {
	
	public function indexOp() {
		header ( 'Content-Type:application/json; charset=utf-8' );
		header ( "Expires: Mon, 26 Jul 1997 05:00:00 GMT" );
		header ( "Cache-Control: no-cache" );
		header ( "Pragma: no-cache" );
		$params = file_get_contents ( "php://input" );
		$params = json_decode($params);
		
		$refund = Service("Refund");
		if( $params->status == '同意' ) {
			
			$_p['refund_id'] = $params->refund_id;
			$_p['op_name'] = 'fenxiao';
			$status = $refund->confirm_refund($_p, $msg) ;
			
		} else if( $params->status == '拒绝' ) {
			
			$_p['refund_id'] = $params->refund_id;
			$_p['seller_state'] = 3;
			$_p['op_name'] = 'fenxiao';
			$status = $refund->edit_refund($_p, $msg) ;
			
		}
		
		if( $status ) {
			die( json_encode( array('errorno' => 1000, 'msg' => $msg) ) ) ;
		} else {
			die( json_encode( array('errorno' => 1001, 'msg' => $msg) ) ) ;
		}
	}
}