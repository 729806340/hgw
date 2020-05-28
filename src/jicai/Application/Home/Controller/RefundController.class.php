<?php

namespace Home\Controller;

use Think\Controller;

class RefundController extends AuthController {
	public function refundList() {
		$this->display ( 'order/refundorder' );
	}
	public function ajax() {
		if (IS_AJAX) {
			
			$action = I ( 'post.action', '', 'htmlspecialchars' );
			switch ($action) {
				case 'refundlist' :
					$pagesize = 10;
					$page = I ( 'post.page', '', 'htmlspecialchars' );
					$oid = trim ( I ( 'post.oid', '', 'htmlspecialchars' ) );
					$order = D ( 'RefundReturn' );
					$result = $order->getRefundList ( $pagesize, $page, $oid );
					if (! ($result [0] > 0))
						$this->ajaxReturn ( array (
								'status' => '0',
								'msg' => '暂无数据！' 
						) );
					$data ['total_num'] = $result [0];
					$data ['list'] = $result [1];
					$data ['page_total_num'] = $result [2];
					$data ['page_size'] = $pagesize;
					$this->ajaxReturn ( array (
							'status' => '1',
							'msg' => $data 
					) );
					
					break;
				
				case 'loglist' :	
					$pagesize = 10;
					$page = I ( 'post.page', '', 'htmlspecialchars' );
					$oid = trim ( I ( 'post.oid', '', 'htmlspecialchars' ) );
					$logtype = I ( 'post.logtype', '', 'htmlspecialchars' );
					$order = D ( 'B2cOrderFenxiaoError' );
					$result = $order->getLogList ( $pagesize, $page, $oid, $logtype );
					
					if (! ($result [0] > 0))
						$this->ajaxReturn ( array (
								'status' => '0',
								'msg' => '暂无数据！' 
						) );
					$data ['total_num'] = $result [0];
					$data ['list'] = $result [1];
					$data ['page_total_num'] = $result [2];
					$data ['page_size'] = $pagesize;
					$this->ajaxReturn ( array (
							'status' => '1',
							'msg' => $data 
					) );
					break;
					
				
			}
		}
	}
	
	// 分销导入订单，退款等错误日志
	function errorlog() {
		$this->display ( 'order/errorlog' );
	}
}
