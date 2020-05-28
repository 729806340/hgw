<?php
 
class writeoff extends commons
{
    private $code = '';

    //sap601 推送错误修正已审核订单
    public function order()
    {
        $log_condition = array();
        $log_condition['log_status'] = 1;
        $log_condition['log_type'] = 'data';
        $log_condition['send_sap'] = 0;
        $log_condition['order_id'] = array('gt', 0);
        $log_condition['ob_id'] = array('neq', 642) ;
        $res = Model('bill_log')->where($log_condition)->select();
        $return = array();
        if( is_array($res) && !empty($res) ){
	        $oids = array_column($res, 'order_id');
	    	
	    	$data = $this->getOrderData( $oids );
	        
	        if(!empty($data)) {
	        	foreach ($data as $k => $v){
	        		$item = array();
	        		$item['tid'] = $v['tid'] ;
	        		$item['cardCode'] = $v['cardCode'];
	        		$item['cardName'] = $v['cardName'];
	        		$item['userFields']['U_OINV_ENUMBER'] = $v['userFields']['U_OINV_ENUMBER'];
	        		$item['userFields']['U_ORDER_STATUS'] = "2";
	        		$item['docLines'] = $v['docLines'] ;
	        		
	        		$return[$k] = $item ;
	        	}
	        }
        }
    	
    	return $return;
    }

    //sap601 推送成功后续操作
    public function order_after($success, $error, $exist='')
    {
        $this->updateSendState($success, 1);//成功的标志改为1
        //已传送过标志改为2
        if (!empty($exist)) {
            //$this->updateSendState($exist, 2);//成功的标志改为2
            $this->order_callback($exist,$error);
        }
        return true;
    }

    //sap601 回调函数
    public function order_callback($success, $error, $exist='')
    {
        $this->updateSendState($success, 2);//成功的标志改为2
        $this->updateSendState($error, 0);//失败的标志改为0 重新推
        
        //修改orders表send_sap状态，并推送301
        /** @var orderModel $order_model **/
        $data = $condition = array();
        $data['send_sap'] = '0' ;
        $condition['order_id'] = array('in',$success);
        $order_model = Model("order");
        $order_model->editOrder($data,$condition);
        
        if( !empty($success) ){
        	//$data = $this->getOrderData( $success );
        	$code = 'sap301';
        	$log['code'] = $code;
	        try {
	            list($action, $class_name, $method) = $this->instantiation($code);
	            //获取推送的数据
	            $log['data'] = $data = $this->getOrderData( $success );
	            //推送
	            $log['rel'] = $rel = $this->push($code, $data);
	            //后续操作
	            if (method_exists($action, $method . '_after')) {
	                $this->execute($action, $class_name, $method . '_after', $this->decode_json($rel, $code, false));
	            }
	        } catch (Exception $e) {
	            $log['error'] = $msg = $e->getMessage();
	            $rel = false;
	        }
	        //记录日志
	        $this->log($log);
        }
        
        return true;
    }
    
    function getOrderData($oids = array())
    {
    	list($action, $class_name, $method) = $this->instantiation('sap301');
    	$args = array();
    	$args['oids'] = $oids;
    	return $this->execute($action, $class_name, $method,$args);
    }
    
    /** 账单审核打回 **/
    function purchase_callback($rel)
    {
        set_time_limit(0);
    	$data = array();
    	foreach ($rel as $value) {
    		list($code, $ob_id, $id) = explode("_", $value['tid']) ;
    		if( $code == 'sap501' ) {
    			$data[$ob_id]['order'][] = $id ;
    		} elseif ($code == 'sap502') {
    			$data[$ob_id]['refund'][] = $id ;
    		}
    	}
    	
    	if( !empty($data) ) {
    		$model_bill = Model('order_bill');
    		$message .= "SAP返回数据：" . json_encode($rel);
    		$ob_ids = array_keys($data);
    		$ob_condition['ob_id'] = array('in', $ob_ids) ;
    		$order_bills = $model_bill->where($ob_condition)->select() ;
    		$model_order = Model('order');
    		$model_refund = Model('refund_return');
    		$bill = Service('Bill') ;
    		foreach ($order_bills as $bill_info) {
                /** 重置SAP推送状态 **/
                $bill->resetBillSapSatus( $bill_info ) ;

                //下面的count与匹配差异订单，可能比较耗时，请务必将以上充值sap推送状态语句放前面执行
    			$order_condition = array();
    			$order_condition['order_state'] = ORDER_STATE_SUCCESS;
    			$order_condition['store_id'] = $bill_info['ob_store_id'];
    			$order_condition['finnshed_time'] = array('between',"{$bill_info['ob_start_date']},{$bill_info['ob_end_date']}");
    			$count_order = $model_order->getOrderCount($order_condition);
    			
    			$refund_condition = array();
    			$refund_condition['seller_state'] = 2;
    			$refund_condition['store_id'] = $bill_info['ob_store_id'];
    			$refund_condition['goods_id'] = array('gt',0);
    			$refund_condition['admin_time'] = $order_condition['finnshed_time'];
    			$count_refund = $model_refund->getRefundReturnCount($refund_condition);
    			
    			$sap_order_count = count($data[$bill_info['ob_id']]['order']);
    			if( $sap_order_count != $count_order ) {
    				$message .= "<br>结算单号#{$bill_info['ob_id']}的订单数目不相等。SAP数目：{$sap_order_count}，汉购数目：{$count_order}<br>" ;
    				$order_list = $model_order->getOrderList($order_condition,'','order_id,order_sn','order_id ASC', $count_order);
    				$oids =  array_column($order_list, 'order_id');
    				$message .= "缺少订单：";
    				foreach ($order_list as $_order) {
    					if( !in_array($_order['order_id'], $data[$bill_info['ob_id']]['order']) ) {
    						$message .= "{$_order['order_id']}:{$_order['order_sn']},";
    					}
    				}
    				$message = substr($message, 0, -1) . "<br>";
    			}
    			$sap_refund_count = count($data[$bill_info['ob_id']]['refund']);
    			if( $sap_refund_count != $count_refund ) {
    				$message .= "<br>结算单号#{$bill_info['ob_id']}的退款数目不相等。SAP数目：{$sap_refund_count}，汉购数目：{$count_refund}<br>" ;
    				$refund_list = $model_refund->getRefundReturnList($refund_condition,'','refund_id,refund_sn', $count_refund);
    				$refund_ids = array_column($refund_list, 'refund_id');
    				$message .= "缺少退款单：";
    				foreach ($refund_list as $_refund) {
    					if( !in_array($_refund['refund_id'], $data[$bill_info['ob_id']]['refund']) ) {
    						$message .= "{$_refund['refund_id']}:{$_refund['refund_sn']},";
    					}
    				}
    				$message = substr($message, 0, -1) . "<br>";
    			}
    		}
    		
    		$emailObj = new Email ();
    		$emailObj->send_sys_email ( 'liaoyun@hansap.com', "结算单审核不通过", $message );
    		$emailObj->send_sys_email ( 'shenlei@hansap.com', "结算单审核不通过", $message );
    	}
    	
    	return true;
    }

    //修改推送标志
    private function updateSendState($ids, $state, $collum = 'send_sap')
    {
    	if (empty($ids)) return true;
    	$where['log_status'] = 1;
    	$where['order_id'] = array('in', $ids);
    	switch ($state) {
    		case 0:
    			$where[$collum] = '1';
    			break;
    		case 1:
    			$where[$collum] = '0';
    			break;
    		case 2:
    			// $where['send_sap'] = '1';
    			break;
    		default:
    			return true;
    	}
    	Model('bill_log')->where($where)->update(array($collum => $state));
    	return true;
    }
    
    //解析SAP数据格式
    private function decode_json($sap_str, $code = null, $callback=true)
    {
    	$rel = json_decode($sap_str, true);
    	//sap403数据格式与其它交易格式不同 直接返回数据到具体的方法里处理
    	if ($code == 'sap403') return array($rel);
    
    	$success = $error = $exist = $notice = array();
    	foreach ((array)$rel['results'] as $v) {
    		if (!$p = strpos($v['tid'], '_')) continue;
    		$tid = substr($v['tid'], $p + 1);
    		switch ($v['status']) {
    			case '0':
    				$success[] = $tid;
    				break;
    			case '-10':
    				$check_exist = in_array($code, array('sap101', 'sap201','sap301', 'sap401', 'sap402', 'sap501', 'sap502', 'sap404', 'sap405'));
    				if ($check_exist && $callback && strpos($v['errInf'], '重复') > 0) {
    					// -10为已存在  该状态认为推送成功
    					$success[] = $tid;
    				} else if ($check_exist && !$callback) {
    					$exist[] = $tid;
    				} else {
    					$error[] = $tid;
    					$notice[] = 'tid:' . $v['tid'] . ' error:' . $v['errInf'];
    				}
    				break;
    			default:
    				//重复订单号验证
    				if (strpos($v['errInf'], '订单号不允许重复') > 0) {
    					$success[] = $tid;
    					break;
    				}
    
    				$error[] = $tid;
    				$notice[] = 'tid:' . $v['tid'] . ' error:' . $v['errInf'];
    				break;
    		}
    	}
    	if (!empty($notice)) {
    		$this->failed[] = array(
    				'title' => '报警：来自 ' . $code . ' 报警信息',
    				'msg' => implode('<br>', $notice),
    		);
    	}
    	return array($success, $error, $exist);
    }
    
    //curl 推送数据
    private function push($code, $data)
    {
    	if (empty($data)) throw new Exception('Null data need to be pushed!');
    	$url = $this->api[$code];
    	if (empty($url)) throw new Exception('Error: ' . $code . ' url does not exist!');
    	$url = $this->api['host'] . $url;
    	import('Curl');
    	$curl = new Curl();
    	$curl->setHeader('Content-Type', 'application/json;charset=utf-8');
    	$curl->setOpt(CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
    	$curl->setOpt(CURLOPT_TIMEOUT, 120);
    
    	$curl->post($url, array('data' => $data), true);
    	if ($curl->error === true) {
    		$msg = $curl->errorMessage;
    		$this->failed[] = array(
    				'title' => '报警：来自 ' . $code . ' 报警信息',
    				'msg' => 'url:' . $url . '<br>error:' . $msg,
    		);
    		throw new Exception($msg);
    	}
    	return $curl->rawResponse;
    }
    
    /** sap603 退款修正 **/
    function refund()
    {
    	//退款数据修正
    	$log_condition = array();
    	$log_condition['log_status'] = '1';
    	$log_condition['log_type'] = 'refund';
    	$log_condition['refund_sap'] = '0';
    	$log_condition['log_model'] = 'refund_return';
    	$log_condition['order_id'] = array('gt', 0);
    	$log_condition['ob_id'] = array('gt', 0);
    	$log_condition['rec_id'] = array('gt', 0);
    	$log_condition['ob_id'] = array('not in', array(642,0)) ;
    	$res = Model('bill_log')->where($log_condition)->order('log_id asc')->group('rec_id')->select();
    	$refund_ids = array();
        if( is_array($res) && !empty($res) ){
	        $refund_ids = array_unique( array_column($res, 'rec_id') ) ;
        }
        
        //订单基础数据修正也要推送新的退款
        $log_condition = array();
        $log_condition['log_status'] = '1';
        $log_condition['log_type'] = 'data';
        $log_condition['order_id'] = array('gt', 0);
        $log_condition['ob_id'] = array('gt', 0);
        $log_condition['refund_sap'] = '0';
        $res = Model('bill_log')->where($log_condition)->order('log_id asc')->group('order_id')->select();
        if( is_array($res) && !empty($res) ){
        	$oids = array_unique( array_column($res, 'order_id') ) ;
        	$rids = $this->getRidsByOids($oids) ;
        	$refund_ids = array_merge($refund_ids, $rids) ;
        }
        
        $data = $this->getRefundData( array_unique($refund_ids) );
        $return = array();
        foreach ($data as $k => $v) {
        	$item = array();
        	$item['tid'] = str_replace("sap404", "sap603", $v['tid']);
        	$item['cardCode'] = $v['cardCode'];
        	$item['cardName'] = $v['cardName'];
        	$item['userFields']['U_OINV_ENUMBER'] = $v['userFields']['U_OINV_ENUMBER'];
        	$item['userFields']['U_ORPD_RETURN_NUMBER'] = $v['userFields']['U_ORPD_RETURN_NUMBER'];
        	$item['userFields']['U_ORDER_STATUS'] = "2";
        	$item['userFields']['U_ORIN_PNOPAY'] = '1';
        	$item['docLines'] = $v['docLines'] ;
        
        	$return[$k] = $item ;
        }
    	
    	return $return;
    }
    
    private function getRefundData($refund_ids)
    {
    	if(empty($refund_ids)) return array();
    	list($action, $class_name, $method) = $this->instantiation('sap404');
    	$args = array();
    	$args['refund_ids'] = $refund_ids;
    	return $this->execute($action, $class_name, $method,$args);
    }
    
	//sap603 推送成功后续操作
    public function refund_after($success, $error, $exist='')
    {
        $oids = $this->getOidsByRids($success);
    	$this->updateSendState($oids, 1, 'refund_sap');//成功的标志改为1
    	//已传送过标志改为2
    	if (!empty($exist)) {
    		//$this->updateSendState($exist, 2);//成功的标志改为2
    		$this->refund_callback($exist,$error);
    	}
        return true;
    }

    //sap603 回调函数
    public function refund_callback($success, $error, $exist='')
    {
    	$s_oids = $this->getOidsByRids($success);
    	$e_oids = $this->getOidsByRids($error);
        $exist_oids = $this->getOidsByRids($exist);

    	$this->updateSendState($s_oids, 2, 'refund_sap');//成功的标志改为2
        $this->updateSendState($e_oids, 0, 'refund_sap');//失败的标志改为0 重新推
        $this->updateSendState($exist_oids, 2, 'refund_sap');//存在的标志改为2
        
        //修改refund_return表send_sap状态，并推送404
        /** @var refund_returnModel $refund_model **/
        $data = $condition = array();
        $data['sap_return_credit'] = '0' ;
        $data['purchase_sap'] = '0' ;
        $condition['refund_id'] = array('in',$success);
        $refund_model = Model("refund_return");
        $refund_model->editRefundReturn($condition, $data);
        
        if( !empty($success) ){
        	$code = 'sap404';
        	$log['code'] = $code;
	        try {
	            list($action, $class_name, $method) = $this->instantiation($code);
	            //获取推送的数据
	            $log['data'] = $data = $this->getRefundData( $success );
	            //推送
	            $log['rel'] = $rel = $this->push($code, $data);
	            //后续操作
	            if (method_exists($action, $method . '_after')) {
	                $this->execute($action, $class_name, $method . '_after', $this->decode_json($rel, $code, false));
	            }
	        } catch (Exception $e) {
	            $log['error'] = $msg = $e->getMessage();
	            $rel = false;
	        }
	        //记录日志
	        $this->log($log);
        }
        
        return true;
    }
    
    private function getOidsByRids($refund_ids = array())
    {
    	$condition = array() ;
    	$condition['refund_id'] = array('in', $refund_ids) ;
    	$res = Model('refund_return') -> where( $condition ) -> select() ;
    	return array_unique( array_column($res, 'order_id') ) ;
    }
    private function getRidsByOids($oids = array())
    {
    	$condition = array() ;
    	$condition['order_id'] = array('in', $oids) ;
    	$res = Model('refund_return') -> where( $condition ) -> select() ;
    	return array_unique( array_column($res, 'refund_id') ) ;
    }
}