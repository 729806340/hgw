<?php

class purchase extends commons
{
	public $orderMode;
	public function done(){
		$data = array();
		$data['tid'] = 'PU'.time();
		if( !empty($_GET['ob_id']) ) {
			$ob_id = $_GET['ob_id'];
			$data['U_OPOR_PAYNUMBER'] =  $ob_id;
		}
		return $data;
	}

	/**
	 * @return false|int
	 */
	public function getCommVer1Time()
	{
		return strtotime('2016-10-31 14:10');
	}




	private function get_all_counts($bill_info,$pay){
		//本次已核对退款数
		$all_counts = array();
		$model_refund = Model('refund_return');
		$refund_condition = $this->get_refund_condition($bill_info);
		$refund_condition = $this->get_checked_condition($refund_condition,$pay);
		$all_counts['checked_refund_count'] = $model_refund->where($refund_condition)->count();

		//本次所有退款数
		$refund_condition = $this->get_refund_condition($bill_info);
		$refund_condition = $this->get_all_condition($refund_condition,$pay);
		$all_counts['all_refund_count'] = $model_refund->where($refund_condition)->count();

		//本次已核对店铺费用数
		$model_storecost = Model('store_cost');
		$cost_condition = $this->get_cost_condition($bill_info);
		$cost_condition = $this->get_checked_condition($cost_condition,$pay);
		$all_counts['checked_store_cost_count'] = $model_storecost->where($cost_condition)->count();

		//本次所有店铺费用数
		$cost_condition = $this->get_cost_condition($bill_info);
		$cost_condition = $this->get_all_condition($cost_condition,$pay);
		$all_counts['all_store_cost_count'] = $model_storecost->where($cost_condition)->count();

		//所有订单数
		$order_condition = $this->get_order_condition($bill_info);
		$order_condition = $this->get_all_condition($order_condition,$pay);
		$all_counts['all_order_count'] = Model('orders')->where($order_condition)->count();

		//已核对订单数
		$order_condition = $this->get_order_condition($bill_info);
		$order_condition = $this->get_checked_condition($order_condition,$pay);
		$all_counts['checked_order_count'] = Model('orders')->where($order_condition)->count();

		return $all_counts;
	}

	private function get_checked_condition($condition,$pay){
		if($pay == 1){
			$condition['check_result'] = array('in','-1,1');
		} else if($pay == 2){
			$condition['check_result'] = array('in','-2,2');
		} else if($pay == 3){
			$condition['check_result'] = array('in','-3,3');
		}
		return $condition;
	}

	private function get_all_condition($condition,$pay){
		if($pay == 1){
			$condition['check_result'] = array('in','-1,1,0');
		} else if($pay == 2){
			$condition['check_result'] = array('in','-2,2,-1');
		} else if($pay == 3){
			$condition['check_result'] = array('in','-3,3,-2');
		}
		return $condition;
	}

	private function get_err_condition($condition,$pay){
		if($pay == 1){
			$condition['check_result'] = -1;
		} else if($pay == 2){
			$condition['check_result'] = -2;
		} else if($pay == 3){
			$condition['check_result'] = -3;
		}
		return $condition;
	}

	private function get_suc_condition($condition,$pay){
		if($pay == 1){
			$condition['check_result'] = 1;
		} else if($pay == 2){
			$condition['check_result'] = 2;
		} else if($pay == 3){
			$condition['check_result'] = 3;
		}
		return $condition;
	}

	private function get_cost_condition($bill_info){
		$cost_condition = array();
		$cost_condition['cost_store_id'] = $bill_info['ob_store_id'];
		$cost_condition['cost_price'] = array('gt', 0) ;
		$cost_condition['cost_time'] = array(array('egt',$bill_info['ob_start_date']),array('elt',$bill_info['ob_end_date']),'and');
		$cost_condition['fx_order_id'] = array('gt', 0) ;
		return $cost_condition;
	}

	private function get_refund_condition($bill_info){
		$refund_condition = array();
		$refund_condition['goods_id'] = array('gt', 0);
		$refund_condition['seller_state'] = 2;
		$refund_condition['store_id'] = $bill_info['ob_store_id'];
		$refund_condition['admin_time'] = array('between',"{$bill_info['ob_start_date']},{$bill_info['ob_end_date']}");
		return $refund_condition;
	}

	private function get_order_condition($bill_info){
		$order_condition = array();
		$order_condition['order_state'] = ORDER_STATE_SUCCESS;
		$order_condition['store_id'] = $bill_info['ob_store_id'];
		$order_condition['finnshed_time'] = array('between',"{$bill_info['ob_start_date']},{$bill_info['ob_end_date']}");
		return $order_condition;
	}

	//商家退款结算  calulate_return_receiveable
	private function calulate_return_receiveable($bill_info,$refund_condition){
		if($bill_info['ob_store_manage_type'] == 'platform'){
			return $this->calulate_return_receiveable_platform($refund_condition);
		} else if($bill_info['ob_store_manage_type'] == 'co_construct'){
			return $this->calulate_return_receiveable_co_construct($refund_condition);
		}
	}

	//共建商家退款结算
	private function calulate_return_receiveable_co_construct($refund_condition){
		$model_refund = Model('refund_return');
		$refund_list = $model_refund->getRefundReturnList($refund_condition,'','*',999999) ;
		$oids = array_column($refund_list, 'order_id') ;
		$order_goods = $this -> getOrderGoods($oids) ;
		$refund_total = 0 ;
		// 计算退款金额
		foreach ($refund_list as $refund) {
			$ogInfo 		= 	$order_goods[ $refund['order_id'] ][ $refund['goods_id'] ] ;
			$cost_rate		=	$ogInfo['goods_cost'] / $ogInfo['goods_pay_price'] ;
			$refund_amount = $refund['refund_amount_bill'] == -1?$refund['refund_amount']:$refund['refund_amount_bill'];
			$refund_total 	+= 	round( $refund_amount * $cost_rate, 2 ) ;
		}
		$ob_order_return_totals = floatval($refund_total);
		$refund_count = count($refund_list);
		if(in_array($refund_condition['check_result'],array(-1,-2,-3))){
			echo '异常退单数量:'.$refund_count.',退单成本:'.$ob_order_return_totals.';--';
		} else {
			echo '正常退单数量:'.$refund_count.',退单成本:'.$ob_order_return_totals.';--';
		}
		return $ob_order_return_totals;
	}

	//平台商家退款结算
	private function calulate_return_receiveable_platform($refund_condition){
		$model_refund = Model('refund_return');
		$refund_list = $model_refund->getRefundReturnList($refund_condition,'','*',999999) ;
		$oids = array_column($refund_list, 'order_id') ;
		$order_goods = $this -> getOrderGoods($oids) ;
		$commis_return_totals = $rpt_bill_totals = 0 ;
		$refund_total = 0 ;
		foreach ($refund_list as $refund) {
			$refund_amount = $refund['refund_amount_bill'] == -1?$refund['refund_amount']:$refund['refund_amount_bill'];
			$refund_total 	+= 	$refund_amount ;
			$ogInfo 		= 	$order_goods[ $refund['order_id'] ][ $refund['goods_id'] ] ;
			$commis_return_totals 	+= 	round( $refund_amount * $ogInfo['commis_rate'] / 100, 2 ) ;
			$rpt_bill_totals += $refund_amount == $ogInfo['goods_pay_price'] ? floatval($ogInfo['rpt_bill']) : 0;
		}
		  //退款总额  $refund_total
		  //退款总佣金 $commis_return_totals
		  //退总红包 $rpt_bill_totals

		//平台应收
		$paltform_receiveable =  $refund_total - $commis_return_totals + $rpt_bill_totals;
		$refund_count = count($refund_list);
		if(in_array($refund_condition['check_result'],array(-1,-2,-3))){
			echo '异常退单数量:'.$refund_count.',退单总额:'.$refund_total.',佣金:'.$commis_return_totals.',红包:'.$rpt_bill_totals.';--';
		} else {
			echo '正常退单数量:'.$refund_count.',退单总额:'.$refund_total.',佣金:'.$commis_return_totals.',红包:'.$rpt_bill_totals.';--';
		}
		return $paltform_receiveable;
	}



	//平台商家订单结算
	private function calulate_order_payable_platform($bill_info,$order_condition){
		$model_order = Model('order');
		$fields = 'sum(order_amount) as order_amount,sum(rpt_bill) as rpt_bill,sum(shipping_fee) as shipping_amount,min(store_name) as store_name';
		$order_info =  $model_order->getOrderInfo($order_condition,array(),$fields);
		$ob_rpt_amount = floatval($order_info['rpt_bill']);  //计算正常红包
		$success_amount = floatval($order_info['order_amount']);  //订单总金额

		//正常佣金金额
		$order_info =  $model_order->getOrderInfo($order_condition,array(),'count(DISTINCT order_id) as count');
		$order_count = $order_info['count'];
		$success_count = $order_count;

		$commis_rate_totals_array = array();
		//分批计算佣金，最后取总和
		$ver1 = $this->getCommVer1Time();
		for ($i = 0; $i <= $order_count; $i = $i + 300){
			$order_list = $model_order->getOrderList($order_condition,'','order_id','',"{$i},300");
			$order_id_array = array_column($order_list,'order_id');
			if($bill_info['ob_start_date']<$ver1){ // 如果存在账单起始时间小于区间时间的
				if($bill_info['ob_end_date']<$ver1){
					$order_id_array_v0 = array_column($order_list,'order_id');
					$order_list = array();
				}else{
					$map = array(
						'order_id'=>array('in',$order_id_array),
						'finnshed_time'=>array('lt',$ver1)
					);
					$order_list_v0 = $model_order->getOrderList($map,'','order_id');
					$order_id_array_v0 = array_column($order_list_v0,'order_id');
					$map = array(
						'order_id'=>array('in',$order_id_array),
						'finnshed_time'=>array('gt',$ver1)
					);
					$order_list = $model_order->getOrderList($map,'','order_id');
				}
				if (!empty($order_id_array_v0)){
					$order_goods_condition = array();
					$order_goods_condition['order_id'] = array('in',$order_id_array_v0);
					$field = 'SUM(ROUND(goods_pay_price*commis_rate/100,2)) as commis_amount';
					$order_goods_info = $model_order->getOrderGoodsInfo($order_goods_condition,$field);
					$commis_rate_totals_array[] = $order_goods_info['commis_amount'];
				}else{
					$commis_rate_totals_array[] = 0;
				}
			}
			$order_id_array = array_column($order_list,'order_id');

			if (!empty($order_id_array)){
				$order_goods_condition = array();
				$order_goods_condition['order_id'] = array('in',$order_id_array);
				$field = 'SUM(ROUND((goods_pay_price+rpt_bill)*commis_rate/100,2)) as commis_amount';
				$order_goods_info = $model_order->getOrderGoodsInfo($order_goods_condition,$field);
				$commis_rate_totals_array[] = $order_goods_info['commis_amount'];
			}else{
				$commis_rate_totals_array[] = 0;
			}
		}
		$ob_commis_totals = floatval(array_sum($commis_rate_totals_array));
		$paltform_payable =  $success_amount - $ob_commis_totals + $ob_rpt_amount;

		if(in_array($order_condition['check_result'],array(-1,-2,-3))){
			echo '异常订单数量:'.$success_count.',异常订单总额:'.$success_amount.',佣金:'.$ob_commis_totals.',红包:'.$ob_rpt_amount.';--';
		} else {
			echo '正常订单数量:'.$success_count.',正常订单总额:'.$success_amount.',佣金:'.$ob_commis_totals.',红包:'.$ob_rpt_amount.';--';
		}
		return $paltform_payable;
	}

	//共建商家订单结算
	private function calulate_order_payable_co_construct($bill_info,$order_condition){
		$model_order = Model('order');
		//订单金额
		$fields = 'sum(order_amount) as order_amount,sum(cost_amount) as cost_amount,sum(rpt_bill) as rpt_bill,sum(shipping_fee) as shipping_amount,min(store_name) as store_name';
		/** 获取结算期间的订单汇总信息 */
		$order_info =  $model_order->getOrderInfo($order_condition,array(),$fields);
		$ob_order_totals = floatval($order_info['cost_amount']); // 成本

		$order_info =  $model_order->getOrderInfo($order_condition,array(),'count(DISTINCT order_id) as count');
		$order_count = $order_info['count'];

		if(in_array($order_condition['check_result'],array(-1,-2,-3))){
			echo '异常订单数量:'.$order_count.',异常订单总成本:'.$ob_order_totals.';--';
		} else {
			echo '正常订单数量:'.$order_count.',正常订单总成本:'.$ob_order_totals.';--';
		}
		return $ob_order_totals;
	}

	//商家订单结算
	//平台商家结算  订单金额 - 佣金 + 平台红包
	//共建商家结算  订单成本
	private function calulate_order_payable($bill_info,$order_condition){
		if($bill_info['ob_store_manage_type'] == 'platform'){
			return $this->calulate_order_payable_platform($bill_info,$order_condition);
		} else if($bill_info['ob_store_manage_type'] == 'co_construct'){
			return $this->calulate_order_payable_co_construct($bill_info,$order_condition);
		}
	}

	//获取所有结算数据
	private function get_all_accounts_data($bill_info){
		//结算cost  内部已做共建、平台区分
		$order_condition = $this->get_order_condition($bill_info);
		$all_order_payable = $this->calulate_order_payable($bill_info,$order_condition);

		//退款cost 内部已做共建、平台区分
		$refund_condition = $this->get_refund_condition($bill_info);
		$all_return_totals =  $this->calulate_return_receiveable($bill_info,$refund_condition);

		//本次正常店铺费用
		$cost_condition = $this->get_cost_condition($bill_info);
		$model_storecost = Model('store_cost');
		$cost_info = $model_storecost->getStoreCostInfo($cost_condition,'sum(cost_price) as cost_amount');
		$suc_store_cost_totals = floatval($cost_info['cost_amount']);

		$data = array();
		$data['all_order_payable'] = $all_order_payable;
		$data['all_return_totals'] = $all_return_totals;
		$data['suc_store_cost_totals'] = $suc_store_cost_totals;
		return $data;
	}

	//获取结算数据
	//商家
	private function write_accounts_log($bill_info,$pay){

		//已结算订单金额
		$order_condition = $this->get_order_condition($bill_info);
		$order_condition = $this->get_suc_condition($order_condition,$pay);
		$suc_order_payable = $this->calulate_order_payable($bill_info,$order_condition);

		//未结算订单金额
		$order_condition = $this->get_order_condition($bill_info);
		$order_condition = $this->get_err_condition($order_condition,$pay);
		$err_order_payable = $this->calulate_order_payable($bill_info,$order_condition);

		//本次正常退款
		$refund_condition = $this->get_refund_condition($bill_info);
		$refund_condition = $this->get_suc_condition($refund_condition,$pay);
		$suc_return_totals =  $this->calulate_return_receiveable($bill_info,$refund_condition);

		//本次异常退款
		$refund_condition = $this->get_refund_condition($bill_info);
		$refund_condition = $this->get_err_condition($refund_condition,$pay);
		$err_return_totals =  $this->calulate_return_receiveable($bill_info,$refund_condition);

		//本次正常店铺费用
		$cost_condition = $this->get_cost_condition($bill_info);
		$cost_condition = $this->get_suc_condition($cost_condition,$pay);
		$model_storecost = Model('store_cost');
		$cost_info = $model_storecost->getStoreCostInfo($cost_condition,'sum(cost_price) as cost_amount');
		$suc_store_cost_totals = floatval($cost_info['cost_amount']);

		//本次异常店铺费用
		$cost_condition = $this->get_cost_condition($bill_info);
		$cost_condition = $this->get_err_condition($cost_condition,$pay);
		$cost_info = $model_storecost->getStoreCostInfo($cost_condition,'sum(cost_price) as cost_amount');
		$err_store_cost_totals = floatval($cost_info['cost_amount']);

		//正常平台应付
		//订单异常  钱先放在 平台，解决异常后再支付给商家
		//退单异常  正常退款: 直接应收 异常退款: 先付给商家，解决异常后再应收  故直接收取
		//店铺费用异常  正常费用: 直接应收 异常费用: 先付给商家，解决异常后再应收  故直接收取

		$platform_suc_payable = $suc_order_payable - $suc_return_totals  - $suc_store_cost_totals;
//		if($pay == 1){
//			//第一次把退款全部收取
//			$platform_suc_payable = $suc_order_payable - ($suc_return_totals + $err_return_totals) - ($suc_store_cost_totals+$err_store_cost_totals);
//		} else {
//			$platform_suc_payable = $suc_order_payable  - ($suc_store_cost_totals+$err_store_cost_totals);
//		}

		$platform_err_payable = $err_order_payable - $err_return_totals - $err_store_cost_totals;

		//第一次结算时结算定金--定金订单中的未退定金
		if($pay == 1){
			$model_order_book = Model('order_book');
			$condition = array();
			$condition['book_store_id'] = $bill_info['ob_store_id'];
			$condition['book_cancel_time'] = array('between',"{$bill_info['ob_start_date']},{$bill_info['ob_end_date']}");
			$order_book_info = $model_order_book->getOrderBookInfo($condition,'sum(book_real_pay) as pay_amount');
			$ob_order_book_totals = floatval($order_book_info['pay_amount']);
			$platform_suc_payable += $ob_order_book_totals;
		}

		$debug_data = array();
		$debug_data['suc_order_payable'] = $suc_order_payable;
		$debug_data['suc_return_totals'] = $suc_return_totals;
		$debug_data['suc_store_cost_totals'] = $suc_store_cost_totals;

		$debug_data['err_order_payable'] = $err_order_payable;
		$debug_data['err_return_totals'] = $err_return_totals;
		$debug_data['err_store_cost_totals'] = $err_store_cost_totals;

		$data = array(
			'obl_ob_id' => $bill_info['ob_id'],
			'pay' => $pay,
			'obl_pay_date' => time(),
			'obl_pay_content' => '第'.$pay.'次 平台应付金额='.$platform_suc_payable.'元， 剩余平台应付金额='.$platform_err_payable.'元；'
		);
		echo '__第'.$pay.'次 平台应付金额='.$platform_suc_payable.'元， 剩余平台应付金额='.$platform_err_payable.'元；';
		if($platform_err_payable != 0){
			$order_bill_data = array();
			$order_bill_data['ob_state'] = 5;
			$order_bill_data['ob_sap_order'] = 0;
			$order_bill_data['ob_sap_refund'] = 0;
			$order_bill_data['ob_sap_storecost'] = 0;
			$billModel = Model('bill');
			$condition = array();
			$condition['ob_id'] = $bill_info['ob_id'];
			$billModel->editOrderBill($order_bill_data,$condition);
		}

		$condition = array();
		$condition['pay'] = $pay;
		$condition['obl_ob_id'] = $bill_info['ob_id'];
		$res = Model('order_bill_log')->where($condition)->find();
		if($res){
			$res = Model('order_bill_log')->where($condition)->update($data);
		} else {
			$res = Model('order_bill_log')->insert($data);
		}
	}

	//重置第二次状态
	public function order_reset_callback($rel){

		$ob_id = $rel['ob_id'];
		$model_bill = Model('order_bill');
		$ob_condition['ob_id'] = $rel['ob_id'];
		$bill_info = $model_bill->where($ob_condition)->find() ;

		//订单重置
		$order_condition = $this->get_order_condition($bill_info);
		$order_condition = $this->get_all_condition($order_condition,1);

		//解决839
//		$data = array();
//		$data['check_result'] = 1;
//		$order_condition['check_result'] = 0;
//		Model('orders')->where($order_condition)->update($data);


		$data = array();
		$data['purchase_sap'] = 0;
		$data['check_result'] = 0;
		Model('orders')->where($order_condition)->update($data);


		//退款重置
//		$model_refund = Model('refund_return');
//		$refund_condition = array();
//		$refund_condition['seller_state'] = 2;
//		$refund_condition['store_id'] = $bill_info['ob_store_id'];
//		$refund_condition['goods_id'] = array('gt',0);
//		$refund_condition['admin_time'] = array('between',"{$bill_info['ob_start_date']},{$bill_info['ob_end_date']}");
//		$order = 'admin_time ASC' ;
//		$field = $bill_info['ob_store_manage_type']=='platform'?
//			C('tablepre').'refund_return.*,ROUND(refund_amount*commis_rate/100,2) as commis_amount':
//			C('tablepre').'refund_return.*,ROUND(refund_amount*cost_rate*commis_rate/10000,2) as commis_amount';
//
//		$data = array();
//		$data['check_result'] = -2;
//		$data['purchase_sap'] = 0;
//		$model_refund->where($refund_condition)->update($data);

		//费用重置
//		$model_storecost = Model('store_cost');
//		$cost_condition['cost_store_id'] = $bill_info['ob_store_id'];
//		$cost_condition['cost_price'] = array('gt', 0) ;
//
//		$cost_condition['cost_time'] = array(array('egt',$bill_info['ob_start_date']),array('elt',$bill_info['ob_end_date']),'and');
//		$cost_condition['fx_order_id'] = array('gt', 0) ;
//
//		$data = array();
//		$data['purchase_sap'] = 0;
//		$list = $model_storecost->where($cost_condition)->update($data);
	}

	//507
	public function order_ids_callback($rel){

//		$limit1 = ($rel['curpage'] - 1) * $rel['pagesize'];
//		$limit2 = $rel['pagesize'];
//		$limit = "{$limit1},{$limit2}";

		$pagesize = 1000;
		$ob_id = $rel['ob_id'];
		$model_bill = Model('order_bill');
		$ob_condition['ob_id'] = $rel['ob_id'];
		$bill_info = $model_bill->where($ob_condition)->find() ;

		$model_order = Model('order');
		$order_condition = array();
		$order_condition['order_state'] = ORDER_STATE_SUCCESS;
		$order_condition['store_id'] = $bill_info['ob_store_id'];
		$order_condition['finnshed_time'] = array('between', "{$bill_info['ob_start_date']},{$bill_info['ob_end_date']}");
		$order_list = $model_order->getOrderList($order_condition,$pagesize,'*','order_id ASC');

		$order_ids = array_column($order_list,'order_id');
		$order_data = array();
//		foreach($order_ids as $v){
//			$order_data[] = 'sap501_'.$ob_id.'_'.$v;
//		}
		$suc_data = array();
//		$suc_data['order'] = $order_data;
//		$suc_data['order_num'] = count($order_data);
//		$suc_data['order_page'] = $model_order->shownowpage();
		$suc_data['order_num'] = $model_order->gettotalnum();

		$model_refund = Model('refund_return');
		$refund_condition = array();
		$refund_condition['seller_state'] = 2;
		$refund_condition['store_id'] = $bill_info['ob_store_id'];
		$refund_condition['goods_id'] = array('gt',0);
		$refund_condition['admin_time'] = array('between',"{$bill_info['ob_start_date']},{$bill_info['ob_end_date']}");
		$order = 'admin_time ASC' ;
		$field = $bill_info['ob_store_manage_type']=='platform'?
			C('tablepre').'refund_return.*,ROUND(refund_amount*commis_rate/100,2) as commis_amount':
			C('tablepre').'refund_return.*,ROUND(refund_amount*cost_rate*commis_rate/10000,2) as commis_amount';
		$refund_list = $model_refund->getRefundReturnList($refund_condition,$pagesize,$field,'',$order);
		$refund_ids = array_column($refund_list,'refund_id');
		$refund_data = array();
//		foreach($refund_ids as $v){
//			$refund_data[] = 'sap502_'.$ob_id.'_'.$v.'_refund';
//		}
//		$suc_data['refund'] = $refund_data;
//		$suc_data['refund_num'] = count($refund_data);
		$suc_data['refund_num'] = $model_refund->gettotalnum();

		$model_storecost = Model('store_cost');
		$cost_condition['cost_store_id'] = $bill_info['ob_store_id'];
		$cost_condition['cost_price'] = array('gt', 0) ;
//		$cost_condition['cost_state'] = 0;
		$cost_condition['cost_time'] = array(array('egt',$bill_info['ob_start_date']),array('elt',$bill_info['ob_end_date']),'and');
//		$cost_condition['fx_order_id'] = array('gt', 0) ;
		$cost_list = $model_storecost->where($cost_condition)->page($pagesize)->select();
		$cost_ids = array_column($cost_list,'cost_id');
		$cost_data = array();
//		foreach($cost_ids as $v){
//			$cost_data[] = 'sap502_'.$ob_id.'_'.$v.'_storecost';
//		}
//		$suc_data['cost'] = $cost_data;
//		$suc_data['cost_num'] = count($cost_data);
		$suc_data['cost_num'] = $model_storecost->gettotalnum();
		return $suc_data;
	}

	//获取退款502列表
	//408
	public function refund_ids_callback($rel){

		$limit1 = ($rel['curpage'] - 1) * $rel['pagesize'];
		$limit2 = $rel['pagesize'];
		$limit = "{$limit1},{$limit2}";

		$ob_id = $rel['ob_id'];
		$model_bill = Model('order_bill');
		$ob_condition['ob_id'] = $rel['ob_id'];
		$bill_info = $model_bill->where($ob_condition)->find() ;

		$model_refund = Model('refund_return');
		$refund_condition = array();
		$refund_condition['seller_state'] = 2;
		$refund_condition['store_id'] = $bill_info['ob_store_id'];
		$refund_condition['goods_id'] = array('gt',0);
		$refund_condition['admin_time'] = array('between',"{$bill_info['ob_start_date']},{$bill_info['ob_end_date']}");
		$order = 'admin_time ASC' ;
		$field = $bill_info['ob_store_manage_type']=='platform'?
			C('tablepre').'refund_return.*,ROUND(refund_amount*commis_rate/100,2) as commis_amount':
			C('tablepre').'refund_return.*,ROUND(refund_amount*cost_rate*commis_rate/10000,2) as commis_amount';
		$refund_list = $model_refund->getRefundReturnList($refund_condition,'',$field,$limit,$order);
		$refund_ids = array_column($refund_list,'refund_id');
		$data = array();
		foreach($refund_ids as $v){
			$data[] = 'sap502_'.$ob_id.'_'.$v.'_refund';
		}
		$suc_data = array();
		$suc_data['content'] = $data;
		$suc_data['data_num'] = count($data);
		return $suc_data;
	}

	//407
	public function refund_check_result_callback($rel){
		$ob_id = $rel['ob_id'];
		$pay = $rel['pay'];
		$data = array();
		$refund_array = array();
		$store_cost_array = array();
		if(!empty($rel['err_list'])){
			//回传数据分割
			foreach ((array)$rel['err_list'] as $row) {
				if (empty($row['tid'])) continue;
				list($code, $ob_id,$type_id,$type) = explode('_', $row['tid']);
				if($type == 'refund'){
					$refund_array[$type_id]['check_result'] = $row['check_result'] ;
					$refund_array[$type_id]['errInf'] = $row['errInf'] ;
				} else if($type == 'storecost'){
					$store_cost_array[$type_id]['check_result'] = $row['check_result'] ;
					$store_cost_array[$type_id]['errInf'] = $row['errInf'] ;
				}
			}

			//更新退款比对结果
			$err_state = 0;
			foreach($refund_array as $refund_id => $refund_err_info){
				$condition = array();
				$condition['refund_id'] = $refund_id;
				$order_data = array();
				$order_data['check_result'] = $refund_err_info['check_result'];
				$order_data['errInf'] = $refund_err_info['errInf'];
				if($refund_err_info['check_result'] == -1 || $refund_err_info['check_result'] == -2 || $refund_err_info['check_result'] == -3){
					$order_data['purchase_sap']= 0;
					$order_data['sap_return_credit']= 0;
					$err_state = -1;
				}
				if(abs($order_data['check_result']) == $pay){
					Model()->table('refund_return')->where($condition)->update($order_data);
				} else {
					return 'return pay不正确';
				}
			}

			//更新店铺费用比对结果
			foreach($store_cost_array as $store_cost_id => $store_cost_err_info){
				$condition = array();
				$condition['cost_id'] = $store_cost_id;
				$order_data = array();
				$order_data['check_result'] = $store_cost_err_info['check_result'];
				$order_data['errInf'] = $store_cost_err_info['errInf'];
				if($store_cost_err_info['check_result'] == -1 || $store_cost_err_info['check_result'] == -2 ||$store_cost_err_info['check_result'] == -3){
					$order_data['purchase_sap']= 0;
					$err_state = -1;
				}
				if(abs($order_data['check_result']) == $pay){
					Model('store_cost')->where($condition)->update($order_data);
				} else {
					return 'store_cost pay不正确';
				}
			}

			//只要退款单、店铺费用有错误，账单变为部分结算状态
//			if($err_state == -1){
//				$order_bill_data = array();
//				$order_bill_data['ob_state'] = BILL_STATE_PART_PAY;
//				$billModel = Model('bill');
//				$condition = array();
//				$condition['ob_id'] = $ob_id;
//				$billModel->editOrderBill($order_bill_data,$condition);
//			}

		}

		$model_bill = Model('order_bill');
		$ob_condition['ob_id'] = $rel['ob_id'];
		$bill_info = $model_bill->where($ob_condition)->find() ;

		$all_counts = $this->get_all_counts($bill_info,$pay);
		//核对正确后进行费用计算
		if(
			($all_counts['checked_refund_count'] == $all_counts['all_refund_count']) &&
			($all_counts['checked_store_cost_count'] == $all_counts['all_store_cost_count']) &&
			($all_counts['checked_order_count'] == $all_counts['all_order_count'])
		){
			$this->write_accounts_log($bill_info,$pay);
			echo '407_pay'.$pay.'_over';
		} else {
			echo '407_pay'.$pay.'_continue';
		}
		return true;
	}

	//生成结果
	public function generate_result_callback($rel){
		$ob_id = $rel['ob_id'];
		$pay = $rel['pay'];
		$model_bill = Model('order_bill');
		$ob_condition['ob_id'] = $ob_id;
		$bill_info = $model_bill->where($ob_condition)->find() ;
		$all_counts = $this->get_all_counts($bill_info,$pay);
		//核对正确后进行费用计算
		if(
			($all_counts['checked_refund_count'] == $all_counts['all_refund_count']) &&
			($all_counts['checked_store_cost_count'] == $all_counts['all_store_cost_count']) &&
			($all_counts['checked_order_count'] == $all_counts['all_order_count'])
		){
			$this->write_accounts_log($bill_info,$pay);
			echo 'generate_result'.$pay.'_over';
		} else {
			echo 'generate_result'.$pay.'_continue';
		}
	}

	//返回核对不正确的  505
	public function check_result_callback($rel){
		$ob_id = $rel['ob_id'];
		$pay = $rel['pay'];
		$data = array();

		if(!empty($rel['err_list'])){
			foreach ((array)$rel['err_list'] as $row) {
				if (empty($row['tid'])) continue;
				list($code, $ob_id, $id) = explode('_', $row['tid']);
				$data[$ob_id][$id]['check_result'] = $row['check_result'] ;
				$data[$ob_id][$id]['errInf'] = $row['errInf'] ;
			}

			$err_state = 0;
			//更新订单比对结果
			foreach($data[$ob_id] as $order_id => $check_info){
				$condition['order_id'] = $order_id;
				$order_data['check_result'] = $check_info['check_result'];
				$order_data['errInf'] = $check_info['errInf'];
				if($check_info['check_result'] == -1 || $check_info['check_result'] == -2 || $check_info['check_result'] == -3){
					$order_data['order_state'] = ORDER_STATE_SUCCESS;
					$order_data['send_sap'] = 0;
					$order_data['purchase_sap'] = 0;
					$err_state = -1;
				}
				if(abs($order_data['check_result']) == $pay){
					Model()->table('orders')->where($condition)->update($order_data);
				} else {
					return 'order pay不正确';
				}
			}

			//订单有错误，把结算单标为部分结算
//			if($err_state == -1){
//				$order_bill_data = array();
//				$order_bill_data['ob_state'] = BILL_STATE_PART_PAY;
//				$billModel = Model('bill');
//				$condition = array();
//				$condition['ob_id'] = $ob_id;
//				$billModel->editOrderBill($order_bill_data,$condition);
//			}
		}

		//检查是否已全部回传
		$model_bill = Model('order_bill');
		$ob_condition['ob_id'] = $ob_id;
		$bill_info = $model_bill->where($ob_condition)->find() ;

		$all_counts = $this->get_all_counts($bill_info,$pay);

		//全部比对完毕，开始结算
		//订单、退款单、店铺费用单全部比对完毕，开始结算
		if(
			($all_counts['checked_refund_count'] == $all_counts['all_refund_count']) &&
			($all_counts['checked_store_cost_count'] == $all_counts['all_store_cost_count']) &&
			($all_counts['checked_order_count'] == $all_counts['all_order_count'])
		){
			$this->write_accounts_log($bill_info,$pay);
			echo '505_pay'.$pay.'_over';
		} else {
			echo '505_pay'.$pay.'_continue';
		}
		return true;
	}

	public function get_order_ids_callback($rel){
		$ob_id = $rel['ob_id'];
		$condition['ob_id'] = $ob_id;
		$bill_info = Model('order_bill')->where($condition)->find();
	}

	//506
	public function check_pre_sum_callback($rel){
		$ob_id = $rel['ob_id'];
		$condition['ob_id'] = $ob_id;
		$bill_info = Model('order_bill')->where($condition)->find();

		$accounts_data = $this -> get_all_accounts_data( $bill_info) ;
		return $accounts_data;
	}

	//504
	public function check_callback($rel){
		$check = $rel['check'];
		$ob_id = $rel['ob_id'];
		$pay = $rel['pay'];
		$condition['ob_id'] = $ob_id;
		$bill_info = Model('order_bill')->where($condition)->find();

		$bill_orders = $this -> _get_bill_info( $bill_info, 'order',$check,$pay) ;
		$bill_orders_out = array();
		foreach($bill_orders as $v){
			$bill_orders_out[] = $v;
		}
		if($bill_orders_out){
			return $bill_orders_out;
		} else {
			return true;
		}
	}

    //sap501 应付-采购订单接口
    public function order()
    {
    	$code = $this->getCode(__CLASS__, __FUNCTION__);
    	$condition['ob_order_totals'] = array('gt', 0) ;
        $condition['ob_sap_order'] = '0' ;
        $condition['ob_state'] = array('in','3,5');

        //指定订单重新推送,通过get参数传递
        if( !empty($_GET['ob_id']) ) {
            $ob_id = explode(',', $_GET['ob_id']);
            $condition['ob_id'] = array('in', $ob_id) ;
        }

		//指定处理平台的
		if( !empty($_GET['ob_type']) ) {
			$ob_type = trim($_GET['ob_type']);
			$condition['ob_store_manage_type'] = $ob_type ;
		}

        //同时拉5个账单
        $list = Model('order_bill')->where($condition)->limit($this->getLimit('sap501'))->select();
        $data = array() ;

		$ob_ids = array_column($list,'ob_id');

		/** @var billModel $ob_model */
		$ob_model = Model('bill') ;
		$ob_model -> checkOrderStatus($ob_ids) ;

        foreach ( $list as $bill_info ) {
        	$bill_orders = $this -> _get_bill_info( $bill_info, 'order' ) ;
        	$data = array_merge($data, $bill_orders) ;
        }
        return $data ;
    }
    
    /**
     * 正常订单tid格式: sap501_billid_orderid
     * 退款修复补款tid格式：sap501_billid_orderid_writeoff
     */
    public function order_after($success, $error, $exist='')
    {
    	if (!empty($success)) {
	    	$succ_oids = $succ_writeoff_oids = array() ;
	    	foreach ($success as $tid) {
	    		$tmp = explode("_", $tid);
	    		if( count($tmp) == 2 ) { //正常订单
	    			$succ_oids[] = $tmp[1] ;
	    		} else if( count($tmp) == 3 ) { //退款修复补款
	    			$succ_writeoff_oids[] = $tmp[1] ;
	    		}
	    	}
	    	
	    	$this->updateSendState($succ_oids, 1, 'orders');//成功的标志改为1
	    	if( count($succ_writeoff_oids) > 0 ) {
	    		Model('bill_log')->where( array('order_id'=>array('in', $succ_writeoff_oids)) )->update( array('purchase_add_sap' => '1') );
	    	}
    	}

    	//已传送过标志改为2
    	if (!empty($exist)) {
    		$ext_oids = $ext_writeoff_oids = array() ;
    		foreach ($exist as $tid) {
    			$tmp = explode("_", $tid);
    			if( count($tmp) == 2 ) { //正常订单
    				$ext_oids[] = $tmp[1] ;
    			} else if( count($tmp) == 3 ) { //退款修复补款
    				$ext_writeoff_oids[] = $tmp[1] ;
    			}
    		}

    	    $this->updateSendState($ext_oids, 2, 'orders');
    	}

        //及时返回信息，如果提示错误的。标志状态置为1。当做临时成功处理（可定时重置状态反复推送）
        if (!empty($error)) {
            $succ_oids = $succ_writeoff_oids = array() ;
            foreach ($error as $tid) {
                $tmp = explode("_", $tid);
                if( count($tmp) == 2 ) { //正常订单
                    $succ_oids[] = $tmp[1] ;
                } else if( count($tmp) == 3 ) { //退款修复补款
                    $succ_writeoff_oids[] = $tmp[1] ;
                }
            }
            
            $this->updateSendState($succ_oids, 1, 'orders');//成功的标志改为1
            // if( count($succ_writeoff_oids) > 0 ) {
            //     Model('bill_log')->where( array('order_id'=>array('in', $succ_writeoff_oids)) )->update( array('purchase_add_sap' => '1') );
            // }
        }
    	return true;
    }
    /**
     * 正常订单tid格式: sap501_billid_orderid
     * 退款修复补款tid格式：sap501_billid_orderid_writeoff
     */
    public function order_callback($success, $error, $exist='')
    {
    	if (!empty($success)) {
    		$succ_billids = $succ_oids = $succ_writeoff_oids = array() ;
    		foreach ($success as $tid) {
    			$tmp = explode("_", $tid);
    			if( count($tmp) == 2 ) { //正常订单
    				$succ_oids[] = $tmp[1] ;
    				$succ_billids[] = $tmp[0] ;
    			} else if( count($tmp) == 3 ) { //退款修复补款
    				$succ_writeoff_oids[] = $tmp[1] ;
    			}
    		}
    	
    		$this->updateSendState($succ_oids, 2, 'orders');//成功的标志改为1
    		if( count($succ_writeoff_oids) > 0 ) {
    			Model('bill_log')->where( array('order_id'=>array('in', $succ_writeoff_oids)) )->update( array('purchase_add_sap' => '2') );
    		}
    	}
    	
    	if (!empty($error)) {
    		$err_oids = $err_writeoff_oids = array();
    		foreach ($error as $tid) {
    			$tmp = explode("_", $tid);
    			if( count($tmp) == 2 ) { //正常订单
    				$err_oids[] = $tmp[1] ;
    			} else if( count($tmp) == 3 ) { //退款修复补款
    				$err_writeoff_oids[] = $tmp[1] ;
    			}
    		}
    		$this->updateSendState($err_oids, 0, 'orders');//失败的标志改为0 重新推
    	}

    	return true;
    }

	//406 应收贷项凭证
	public function refund_check_callback($rel)
	{
		$ob_id = $rel['ob_id'];
		$check = $rel['check'];
		$pay = $rel['pay'];
		$check_type = $rel['check_type']?$rel['check_type']:'';
		$condition['ob_id'] = $ob_id;
		$bill_info = Model('order_bill')->where($condition)->limit($this->getLimit('sap502'))->find();
		$data = array() ;

		if($check_type == 'refund'){
			$bill_refund = $this -> _get_bill_info( $bill_info, 'refund',$check,$pay) ;
			$data = array_merge($data, $bill_refund) ;
		} else if($check_type == 'store_cost'){
			$store_cost = $this -> storecost_check($ob_id,$check,$pay) ;
			foreach ($store_cost as $_sc) {
				$cost_id = $_sc['userFields']['U_ORPD_RETURN_NUMBER'] ;
				if(!empty($_sc)){
					$data[] = $_sc;
				}
			}
		} else {
			$bill_refund = $this -> _get_bill_info( $bill_info, 'refund',$check,$pay) ;
			$data = array_merge($data, $bill_refund) ;

			$store_cost = $this -> storecost_check($ob_id,$check,$pay) ;
			foreach ($store_cost as $_sc) {
				$cost_id = $_sc['userFields']['U_ORPD_RETURN_NUMBER'] ;
				if(!empty($_sc)){
					$data[] = $_sc;
				}
			}
		}


		//退款修正，商家扣款
		$list = $this->refundWriteoff() ;
		$writeoff = array();
		foreach ( $list as $bill_info ) {
			$bill_refund = $this -> _get_bill_info( $bill_info, 'refund' ) ;
			$writeoff = array_merge($writeoff, $bill_refund) ;
		}
		//比对ob_id数据，全部已推送的更新bill_log表purchase_refund_sap状态为2
		$hasRefund = array();
		foreach ($writeoff as $refund) {
			$bill_id = $refund['userFields']['U_OPOR_PAYNUMBER'];
			if( !in_array($bill_id, $hasRefund) ) {
				$hasRefund[] = $bill_id ;
			}
		}
		$noRefund = array_diff($this->writeoff_obids, $hasRefund) ;
		if( is_array($noRefund) && !empty($noRefund) ) {
			$condition = array();
			$condition['log_status'] = 1;
			$condition['ob_id'] = array('in', array_values($noRefund) ) ;
			$condition['purchase_refund_sap'] = '0';
			$update = array();
			$update['purchase_refund_sap'] = '2';
			Model('bill_log')->where($condition)->update($update) ;
		}

		$data = array_merge($data, $writeoff) ;
		return $data ;
	}




    //502 应收贷项凭证
    public function refund()
    {
    	$condition['ob_order_return_totals'] = array('gt', 0) ;
        $condition['ob_sap_refund'] = '0' ;
		$condition['ob_state'] = array('in','3,5');
        //指定订单重新推送,通过get参数传递
        if( !empty($_GET['ob_id']) ) {
            $ob_id = explode(',', $_GET['ob_id']);
            $condition['ob_id'] = array('in', $ob_id) ;
        }
        $list = Model('order_bill')->where($condition)->limit($this->getLimit('sap502'))->select();

        $data = array() ;
        foreach ( $list as $bill_info ) {
        	$bill_refund = $this -> _get_bill_info( $bill_info, 'refund' ) ;
        	$data = array_merge($data, $bill_refund) ;
        }
        
        $store_cost = $this -> storecost() ;
        foreach ($store_cost as $_sc) {
        	$cost_id = $_sc['userFields']['U_ORPD_RETURN_NUMBER'] ;
        	$data[] = $_sc;
        }
        
        //退款修正，商家扣款
        $list = $this->refundWriteoff() ;
        $writeoff = array();
    	foreach ( $list as $bill_info ) {
        	$bill_refund = $this -> _get_bill_info( $bill_info, 'refund' ) ;
        	$writeoff = array_merge($writeoff, $bill_refund) ;
        }
        //比对ob_id数据，全部已推送的更新bill_log表purchase_refund_sap状态为2
        $hasRefund = array();
        foreach ($writeoff as $refund) {
        	$bill_id = $refund['userFields']['U_OPOR_PAYNUMBER'];
        	if( !in_array($bill_id, $hasRefund) ) {
        		$hasRefund[] = $bill_id ;
        	}
        }
       	$noRefund = array_diff($this->writeoff_obids, $hasRefund) ;
       	if( is_array($noRefund) && !empty($noRefund) ) {
       		$condition = array();
       		$condition['log_status'] = 1;
       		$condition['ob_id'] = array('in', array_values($noRefund) ) ;
       		$condition['purchase_refund_sap'] = '0';
       		$update = array();
       		$update['purchase_refund_sap'] = '2';
       		Model('bill_log')->where($condition)->update($update) ;
       	}
        
        $data = array_merge($data, $writeoff) ;
        return $data ;
    }
    /**
     * 退款单tid格式: sap502_billid_refundid_refund
     * 店铺费用tid格式：sap502_billid_costid_storecost
     */
    public function refund_after($success, $error, $exist='')
    {
    	if( !empty($success) ) {
    		$ids = array() ;
    		foreach ($success as $tid) {
    			list($bill_id, $id, $type) = explode("_", $tid) ;
    			$ids[$type][] = $id ;
    		}
    		if( is_array($ids['storecost']) && !empty($ids['storecost']) ) {
    			$this->updateSendState($ids['storecost'], 1, 'store_cost');
    		}
    		if( is_array($ids['refund']) && !empty($ids['refund']) ) {
    			$this->updateSendState($ids['refund'], 1, 'refund_return');
    		}
    	}
    	
    	//已传送过标志改为2
    	if (!empty($exist)) {
    		$ids = array() ;
    		foreach ($exist as $tid) {
    			list($bill_id, $id, $type) = explode("_", $tid) ;
    			$ids[$type][] = $id ;
    		}
    		if( is_array($ids['storecost']) && !empty($ids['storecost']) ) {
    			$this->updateSendState($ids['storecost'], 2, 'store_cost');
    		}
    		if( is_array($ids['refund']) && !empty($ids['refund']) ) {
    			$this->updateSendState($ids['refund'], 2, 'refund_return');
    		}
    	}

        //及时返回信息，如果提示错误的。标志状态置为1。当做临时成功处理（可定时重置状态反复推送）
        if( !empty($error) ) {
            $ids = array() ;
            foreach ($error as $tid) {
                list($bill_id, $id, $type) = explode("_", $tid) ;
                $ids[$type][] = $id ;
            }
            if( is_array($ids['storecost']) && !empty($ids['storecost']) ) {
                $this->updateSendState($ids['storecost'], 1, 'store_cost');
            }
            if( is_array($ids['refund']) && !empty($ids['refund']) ) {
                $this->updateSendState($ids['refund'], 1, 'refund_return');
            }
        }
    	return true;
    }
    /**
     * 退款单tid格式: sap502_billid_refundid_refund
     * 店铺费用tid格式：sap502_billid_costid_storecost
     * 退款修正扣款tid格式：sap502_billid_orderid_writeoff
     */
    public function refund_callback($success, $error, $exist='')
    {
    	$ob_model = Model('bill') ;
    	if( !empty($success) ) {
    		$billids = $ids = array() ;
    		foreach ($success as $tid) {
    			list($bill_id, $id, $type) = explode("_", $tid) ;
    			$ids[$type][] = $id ;
    			$billids[$type][] = $bill_id ;
    		}
    		if( is_array($ids['storecost']) && !empty($ids['storecost']) ) {
    			$this->updateSendState($ids['storecost'], 2, 'store_cost');
    			$ob_model -> checkStorecostStatus($billids['storecost']) ;
    		}
    		if( is_array($ids['refund']) && !empty($ids['refund']) ) {
    			$this->updateSendState($ids['refund'], 2, 'refund_return');
    			$ob_model -> checkRefundStatus($billids['refund']) ;
    		}
    	}
    	
    	if( !empty($error) ) {
    		$ids = array() ;
    		foreach ($error as $tid) {
    			list($bill_id, $id, $type) = explode("_", $tid) ;
    			$ids[$type][] = $id ;
    		}
    		if( is_array($ids['storecost']) && !empty($ids['storecost']) ) {
    			$this->updateSendState($ids['storecost'], 0, 'store_cost');
    		}
    		if( is_array($ids['refund']) && !empty($ids['refund']) ) {
    			$this->updateSendState($ids['refund'], 0, 'refund_return');//失败修改为0重新推
    		}
    	}

    	return true;
    }
    
    /**
     * 获取结算数据
     * @param unknown $ob_id order_bill 主键
     * @param unknown $type 结算单类型：订单，退款单，店铺费用
     */
    private function _get_bill_info( $bill_info, $type='order',$check_status = 0,$pay = 0 )
    {
    	$order_condition = array();

        $order_condition['order_state'] = ORDER_STATE_SUCCESS;
    	$order_condition['store_id'] = $bill_info['ob_store_id'];

    	$data = array();
    	if( $type == 'order' ) {
			$order_condition['filter_status']='0';
			if($check_status > 0){
				$data = $this -> _getOrderListCheck($order_condition, $bill_info,$check_status,$pay) ;
			} else {
				$data = $this -> _getOrderList($order_condition, $bill_info,$check_status) ;
			}

    	} elseif ($type == 'refund') {
			$order_condition['filter_status']='0';
			if($check_status > 0){
				$data = $this->_getRefundListCheck($order_condition, $bill_info,$check_status,$pay) ;
			} else {
				$data = $this -> _getRefundList($order_condition, $bill_info,$check_status) ;
			}

    	} elseif ( $type == 'storecost' ) {
			if($check_status > 0){
				$data = $this->_getStorecostListCheck($order_condition, $bill_info,$check_status,$pay) ;
			} else {
				$data = $this -> _getStorecostList($order_condition, $bill_info,$check_status) ;
			}
    	}
    	return $data ;
    }

	/**
	 * 获取结算单的订单列表
	 */
	private function _getOrderListCheck($order_condition, $bill_info,$check_status = 0,$pay = 0)
	{
		//订单列表
		$model_order = Model('order');

		if($check_status > 0){
			$order_condition['check_status'] = array('neq',$check_status);
		} else {
			$order_condition['purchase_sap'] = '0' ;
		}
//		$pay_array = array(
//			'1' => '0',
//			'2' => '-1',
//			'3' => '-2'
//		);

		$pay_array = array(
			'1' => array('0'),
			'2' => array('0','-1'),
			'3' => array('-1','-2')
		);

		if($pay > 0){
//			$order_condition['check_result'] = $pay_array[$pay] ;
			$order_condition['check_result'] = array('in',$pay_array[$pay]);
		}

		$order_condition['finnshed_time'] = array('between', "{$bill_info['ob_start_date']},{$bill_info['ob_end_date']}");
		$order_list = $model_order->getOrderList($order_condition,'','*','order_id ASC', $this->getLimit('sap501_item'));

		//然后取订单商品佣金
		$order_id_array = array();
		$orders = array();
		if (is_array($order_list)) {
			foreach ($order_list as $order_info) {
				$orders[$order_info['order_id']]=$order_info;
				$order_id_array[] = $order_info['order_id'];
			}
		}

		if( count($order_id_array) < 1 ) return array() ;

		if($check_status > 0){
			$order_check_condition = array();
			$order_check_condition['order_id'] = array('in',$order_id_array);
			$model_order->setCheck($order_check_condition,$check_status);
		}

		$order_goods_condition = array();
		$order_goods_condition['order_id'] = array('in',$order_id_array);

		$goods_list = $model_order->getOrderGoodsList($order_goods_condition);
		$commis_cost = $goods_data = $tax_rate_null = array() ;

		//查询商品原始成本
		$gids = array_column($goods_list, 'goods_id') ;
		//$g = Model('goods')->where(array('goods_id' => array('in', $gids)))->key('goods_id')->select();

		foreach ($goods_list as $goods)
		{
			$orderInfo = $orders[$goods['order_id']];
			$order_id 	= $goods['order_id'] ;
			$goods_id	= $goods['goods_id'] ;
			$scale 		= $goods['commis_rate']/100 ;

			$item_shipping_fee = $orderInfo['shipping_fee']/count($orderInfo['extend_order_goods']);

			$comprice = 0;
			/** @var BillService $billService */
			$billService = Service('Bill');
			$ver1 = $billService->getCommVer1Time();
			if ($goods['manage_type']=='platform') {
				$comprice = $orderInfo['finnshed_time']>$ver1?
					ncPriceFormat(($goods['goods_pay_price']+$goods['rpt_bill']) * $goods['commis_rate'] / 100):
					ncPriceFormat($goods['goods_pay_price'] * $goods['commis_rate'] / 100);

				//订单商品行成本：如果是平台的加上红包，且goods_cost字段与结算单成本计算一致。
				//不使用原本数据库的googs_cost字段
				// $item['goods_cost'] =  $item['goods_cost'] + $item['rpt_amount']; //错误写法
				$goods['goods_cost'] =  ncPriceFormat($goods['goods_pay_price']+ $item_shipping_fee - $comprice + $goods['rpt_bill']);

				$goods['tax_output'] = $goods['tax_input'] = 0;   //平台的税率为0
			}

			if( !in_array($order_id, $tax_rate_null) &&  ($goods['tax_input'] == '200.000' || $goods['tax_output'] == '200.000') && $goods['manage_type'] == 'co_construct' ) {
				$tax_rate_null[] = $order_id ;
			}

			$price = ncPriceFormat($goods['goods_pay_price']/$goods['goods_num']) ;

			$items = array() ;
			$items['itemCode'] 						= 	$goods_id;
			$items['priceAfVat']					=	ncPriceFormat($price);
			$items['quantity'] 						= 	$goods['goods_num'] ;
			//$items['LineTotal'] 					= 	$lineTotal ;
			$items['userFields']['U_INV1_COMSCALE']	=	$scale;
			$items['userFields']['U_INV1_COMPRICE']	=	ncPriceFormat($comprice);
			$items['userFields']['U_INV1_OITM_PUR']	=	ncPriceFormat($goods['goods_cost'] / $goods['goods_num'], 4) ;
			$items['userFields']['U_TAX_RATE']		=	$goods['tax_input']/100;

			$items['userFields']['U_INV1_REDPACK']  =   $goods['rpt_bill'];//行红包总计
			$items['userFields']['U_INV1_COUPON']   =   '0'; //行优惠券总计

			// $items['userFields']['VatGroupPu'] 		= 	inputTax( $goods['tax_input'] ) ;
			// $items['userFields']['vatGourpSa'] 		= 	outputTax( $goods['tax_output'] ) ;
			$items['vatGroup']        =   inputTax( $goods['tax_input'] ) ;
			$goods_data[$order_id][] 				= 	$items ;
			$commis_cost[$order_id]['commis'] 		+= 	ncPriceFormat($comprice);
			$commis_cost[$order_id]['cost'] 		+= 	$goods['goods_cost'];
			$commis_cost[$order_id]['order_amount'] +=	$goods['goods_cost'];
		}

		$data = array();
		foreach ($order_list as $order_info) {
			if( in_array($order_info['order_id'], $tax_rate_null) ) continue ;

			$list = array();
			$list['tid'] = 'sap501_'.$bill_info['ob_id'] . "_" . $order_info['order_id'] ;
			// $list['docDate'] = date('Y-m-d H:i:s', $bill_info['ob_create_date']);//确认完成时间

			$list['cardCode'] = $order_info['store_id'] ;
			$list['cardName'] = $order_info['store_name'] ;
			//$list['docTotal'] = ncPriceFormat($commis_cost[$order_info['order_id']]['order_amount']);
			$list['userFields']['U_OINV_ENUMBER'] = $order_info['order_sn'];
			$list['userFields']['U_ALL_Chorge'] = ncPriceFormat($commis_cost[$order_info['order_id']]['commis']);
			$list['userFields']['U_OPCH_REDPACK'] = ncPriceFormat($order_info['rpt_bill']);
			$list['userFields']['U_OPCH_COST'] = ncPriceFormat($commis_cost[$order_info['order_id']]['cost']);
			$list['userFields']['U_OPOR_PAYNUMBER'] = $bill_info['ob_id'] ;
			$list['userFields']['U_OCRD_PARTNER_TYPE'] = $this -> get_manage_type( $order_info['manage_type'] ) ;
			$list['userFields']['U_PAY_DATE'] = date('Y-m-d H:i:s',$order_info['add_time']);
			$list['userFields']['U_COMPLETE_DATE'] = date('Y-m-d H:i:s',$order_info['finnshed_time']);
			$list['userFields']['U_OINV_YF'] = $order_info['shipping_fee'];
			$list['docLines'] = $goods_data[$order_info['order_id']] ;

			$data[$order_info['order_id']] = $list;
		}
		return $data ;
	}

    /**
     * 获取结算单的订单列表
     */
    private function _getOrderList($order_condition, $bill_info,$check_status)
    {
    	//订单列表
    	$model_order = Model('order');

		if($check_status > 0){
			$order_condition['check_status'] = array('neq',$check_status);
		} else {
			$order_condition['purchase_sap'] = '0' ;
		}
		$order_condition['finnshed_time'] = array('between', "{$bill_info['ob_start_date']},{$bill_info['ob_end_date']}");
    	$order_list = $model_order->getOrderList($order_condition,'','*','order_id ASC', $this->getLimit('sap501_item'));

    	//然后取订单商品佣金
    	$order_id_array = array();
        $orders = array();
    	if (is_array($order_list)) {
    		foreach ($order_list as $order_info) {
                $orders[$order_info['order_id']]=$order_info;
    			$order_id_array[] = $order_info['order_id'];
    		}
    	}
    	
    	if( count($order_id_array) < 1 ) return array() ;

		if($check_status > 0){
			$order_check_condition = array();
			$order_check_condition['order_id'] = array('in',$order_id_array);
			$model_order->setCheck($order_check_condition,$check_status);
		}
    	
    	$order_goods_condition = array();
    	$order_goods_condition['order_id'] = array('in',$order_id_array);

    	$goods_list = $model_order->getOrderGoodsList($order_goods_condition);
    	$commis_cost = $goods_data = $tax_rate_null = array() ;
    	
    	//查询商品原始成本
    	$gids = array_column($goods_list, 'goods_id') ;
    	//$g = Model('goods')->where(array('goods_id' => array('in', $gids)))->key('goods_id')->select();
    	
    	foreach ($goods_list as $goods)
    	{
            $orderInfo = $orders[$goods['order_id']];
    		$order_id 	= $goods['order_id'] ;
    		$goods_id	= $goods['goods_id'] ;
    		$scale 		= $goods['commis_rate']/100 ;

            $item_shipping_fee = $orderInfo['shipping_fee']/count($orderInfo['extend_order_goods']);

            $comprice = 0;
            /** @var BillService $billService */
            $billService = Service('Bill');
            $ver1 = $billService->getCommVer1Time();
            if ($goods['manage_type']=='platform') {
                $comprice = $orderInfo['finnshed_time']>$ver1?
                    ncPriceFormat(($goods['goods_pay_price']+$goods['rpt_bill']) * $goods['commis_rate'] / 100):
                    ncPriceFormat($goods['goods_pay_price'] * $goods['commis_rate'] / 100);

                //订单商品行成本：如果是平台的加上红包，且goods_cost字段与结算单成本计算一致。
                //不使用原本数据库的googs_cost字段    
                // $item['goods_cost'] =  $item['goods_cost'] + $item['rpt_amount']; //错误写法
                $goods['goods_cost'] =  ncPriceFormat($goods['goods_pay_price']+ $item_shipping_fee - $comprice + $goods['rpt_bill']);
                $goods['tax_output'] = $goods['tax_input'] = 0;   //平台的税率为0
            }

    		if( !in_array($order_id, $tax_rate_null) &&  ($goods['tax_input'] == '200.000' || $goods['tax_output'] == '200.000') && $goods['manage_type'] == 'co_construct' ) {
				//刷新订单商品税率
				$goods_info = Model('goods')->getGoodsInfoByID($goods_id);
				$data = array();
				$data['tax_input'] = $goods_info['tax_input'];
				$data['tax_output'] = $goods_info['tax_output'];

				$condition = array();
				$condition['order_id'] = $order_id ;
				$condition['goods_id'] = $goods_id ;
				Model('order')->editOrderGoods($data,$condition);
				$order_goods_info = Model('order')->getOrderGoodsInfo( $condition ) ;
				if($order_goods_info['tax_input'] == 200 || $order_goods_info['tax_output'] == 200){
					$tax_rate_null[] = $order_id ;
				}
    		}
    		
    		$price = ncPriceFormat($goods['goods_pay_price']/$goods['goods_num']) ;
    		
    		$items = array() ;
    		$items['itemCode'] 						= 	$goods_id;
    		$items['priceAfVat']					=	ncPriceFormat($price);
    		$items['quantity'] 						= 	$goods['goods_num'] ;
    		//$items['LineTotal'] 					= 	$lineTotal ;
    		$items['userFields']['U_INV1_COMSCALE']	=	$scale;
    		$items['userFields']['U_INV1_COMPRICE']	=	ncPriceFormat($comprice);
    		$items['userFields']['U_INV1_OITM_PUR']	=	ncPriceFormat($goods['goods_cost'] / $goods['goods_num'], 4) ;
    		$items['userFields']['U_TAX_RATE']		=	$goods['tax_input']/100;

            $items['userFields']['U_INV1_REDPACK']  =   $goods['rpt_bill'];//行红包总计
            $items['userFields']['U_INV1_COUPON']   =   '0'; //行优惠券总计

			// $items['userFields']['VatGroupPu'] 		= 	inputTax( $goods['tax_input'] ) ;
			// $items['userFields']['vatGourpSa'] 		= 	outputTax( $goods['tax_output'] ) ;
            $items['vatGroup']        =   inputTax( $goods['tax_input'] ) ;
    		$goods_data[$order_id][] 				= 	$items ;
    		$commis_cost[$order_id]['commis'] 		+= 	ncPriceFormat($comprice);
    		$commis_cost[$order_id]['cost'] 		+= 	$goods['goods_cost'];
    		$commis_cost[$order_id]['order_amount'] +=	$goods['goods_cost'];
    	}
    	
    	$data = array();
    	foreach ($order_list as $order_info) {
    		if( in_array($order_info['order_id'], $tax_rate_null) ) continue ;
    		
    		$list = array();
    		$list['tid'] = 'sap501_'.$bill_info['ob_id'] . "_" . $order_info['order_id'] ;
            // $list['docDate'] = date('Y-m-d H:i:s', $bill_info['ob_create_date']);//确认完成时间

    		$list['cardCode'] = $order_info['store_id'] ;
    		$list['cardName'] = $order_info['store_name'] ;
    		//$list['docTotal'] = ncPriceFormat($commis_cost[$order_info['order_id']]['order_amount']);
    		$list['userFields']['U_OINV_ENUMBER'] = $order_info['order_sn'];
    		$list['userFields']['U_ALL_Chorge'] = ncPriceFormat($commis_cost[$order_info['order_id']]['commis']);
    		$list['userFields']['U_OPCH_REDPACK'] = ncPriceFormat($order_info['rpt_bill']);
    		$list['userFields']['U_OPCH_COST'] = ncPriceFormat($commis_cost[$order_info['order_id']]['cost']);
    		$list['userFields']['U_OPOR_PAYNUMBER'] = $bill_info['ob_id'] ;
    		$list['userFields']['U_OCRD_PARTNER_TYPE'] = $this -> get_manage_type( $order_info['manage_type'] ) ;
    		$list['userFields']['U_PAY_DATE'] = date('Y-m-d H:i:s',$order_info['add_time']);
    		$list['userFields']['U_COMPLETE_DATE'] = date('Y-m-d H:i:s',$order_info['finnshed_time']);
            $list['userFields']['U_OINV_YF'] = $order_info['shipping_fee'];
            $list['docLines'] = $goods_data[$order_info['order_id']] ;
    		 
    		$data[$order_info['order_id']] = $list;
    	}

    	// TODO 增加修改数据处理
        // 查询修改日志记录

    	return $data ;
    }

	/**
	 * 获取结算单的退款单列表
	 */
	private function _getRefundListCheck($order_condition, $bill_info,$check_status = 0,$pay = 0)
	{
		//退款订单列表
		$model_refund = Model('refund_return');
		$refund_condition = array();
		$refund_condition['seller_state'] = 2;
		$refund_condition['store_id'] = $bill_info['ob_store_id'];
		$refund_condition['goods_id'] = array('gt',0);
		$pay_array = array(
			'1' => '0',
			'2' => '-1',
			'3' => '-2'
		);
		if($pay > 0){
			$refund_condition['check_result'] = $pay_array[$pay] ;
		}
		$refund_condition['admin_time'] = array('between',"{$bill_info['ob_start_date']},{$bill_info['ob_end_date']}");
//		$refund_condition['admin_time'] = $order_condition['finnshed_time'];
//		$refund_condition['purchase_sap'] = 0 ;
		$order = 'admin_time ASC' ;
		if($check_status > 0){
			$refund_condition['check_status'] = array('neq',$check_status);
		}

		$field = $bill_info['ob_store_manage_type']=='platform'?
			C('tablepre').'refund_return.*,ROUND(refund_amount*commis_rate/100,2) as commis_amount':
			C('tablepre').'refund_return.*,ROUND(refund_amount*cost_rate*commis_rate/10000,2) as commis_amount';
		$refund_list = $model_refund->getRefundReturnList($refund_condition,'',$field,$this->getLimit('sap502_item'),$order);
		if (is_array($refund_list) && count($refund_list) == 1 && $refund_list[0]['refund_id'] == '') {
			$refund_list = array();
		}

		$data = array();
		if( count($refund_list) > 0 ) {

			$refund_id_array = array();
			if (is_array($refund_list)) {
				foreach ($refund_list as $refund_info) {
					$refund_id_array[] = $refund_info['refund_id'];
				}
			}
			if($check_status > 0){
				$order_check_condition = array();
				$order_check_condition['refund_id'] = array('in',$refund_id_array);
				$model_refund_return = Model('refund_return');
				$model_refund_return->setCheck($order_check_condition,$check_status);
			}


			//查询商品原始成本
			$gids = array_column($refund_list, 'goods_id') ;
			//$g = Model('goods')->where(array('goods_id' => array('in', $gids)))->key('goods_id')->select();
			//查询订单商品
			$oids = array_column($refund_list, 'order_id') ;
			$order_goods = $this -> getOrderGoods( $oids ) ;


			$order_model = Model('order') ;
			foreach ($refund_list as $refund_info) {
				$goods_id = $refund_info['goods_id'] ;
				$order_id = $refund_info['order_id'] ;
				$ogInfo = $order_goods[ $order_id ][ $goods_id ] ;

				if( ($ogInfo['tax_input'] == '200.00' || $ogInfo['tax_output'] == '200.00')
					&& $ogInfo['manage_type'] == 'co_construct') continue ;


				$refund_amount = $refund_info['refund_amount_bill'] == -1?$refund_info['refund_amount']:$refund_info['refund_amount_bill'];

				//退换佣金值
				$commis_amount = $bill_info['ob_store_manage_type']=='platform' ?
					ncPriceFormat($refund_amount*$ogInfo['commis_rate']/100 ) : 0 ;

				//退还的红包值(平台模式，且全额退款，红包才全额退还)
				// 期望方式，但目前不是这样计算的 ：sprintf("%.2f", ($refund_amount/$ogInfo['goods_pay_price'])*$ogInfo['rpt_amount'] );
				if ($bill_info['ob_store_manage_type']=='platform' && $refund_amount == $ogInfo['goods_pay_price']) {
					$rpt_amount = $ogInfo['rpt_bill'];
				} else {
					$rpt_amount = 0;
				}

				//平台和共建/自营 的 最终成本价计算
				if ($bill_info['ob_store_manage_type']=='platform') {
					$ogInfo['tax_input'] = $ogInfo['tax_output'] = 0;
					$cost = $refund_amount - $commis_amount + $rpt_amount ; //总退还值
				} else {
					$cost =  ($refund_amount/$ogInfo['goods_pay_price'])*$ogInfo['goods_cost']; //总退还值
				}
				$cost = ncPriceFormat($cost);

				$items = array() ;
				$items['itemCode'] = $refund_info['refund_type'] == 1 ? 444444 : $goods_id ;
				$items['LineTotal'] = $refund_amount ;
				$items['quantity'] = $items['itemCode']==444444 ? 1 : $refund_info['goods_num'] ;
				$items['priceAfVat'] = ncPriceFormat($cost / $items['quantity'] , 4)  ;//平台最终与供应商结算单品含税单价
				$items['userFields']['U_INV1_COMSCALE'] = $refund_info['commis_rate'] ;
				$items['userFields']['U_INV1_COMPRICE'] = ncPriceFormat($commis_amount);
				$items['userFields']['U_INV1_OITM_PUR'] = ncPriceFormat($items['priceAfVat']);//平台最终与供应商结算单品含税单价，值与毛价相等。
				$items['userFields']['U_TAX_RATE'] = $ogInfo['tax_input']/100 ;
				$items['vatGroup'] = inputTax( $ogInfo['tax_input'] ) ;

				$list = array();
				$list['tid']		=	'sap502_'.$bill_info['ob_id'] . "_" . $refund_info['refund_id'] . "_refund" ;
				// $list['docDate'] = empty($refund_info['admin_time']) ?
				// date('Y-m-d H:i:s', $refund_info['add_time']) : date('Y-m-d H:i:s', $refund_info['admin_time']);//退款完成时间

				$list['cardCode'] 	= $refund_info['store_id'] ;
				$list['cardName'] 	= $refund_info['store_name'] ;
				$list['userFields']['U_OINV_ENUMBER'] = $refund_info['order_sn'] ;
				$list['userFields']['U_OPOR_PAYNUMBER'] = $bill_info['ob_id'] ;
				$list['userFields']['U_ALL_Chorge'] = ncPriceFormat($commis_amount);
				$list['userFields']['U_OPCH_REDPACK'] = ncPriceFormat($rpt_amount);
				$list['userFields']['U_ORPD_RETURN_NUMBER'] = $refund_info['refund_id'] ;
				$list['userFields']['U_PAY_DATE'] = date('Y-m-d H:i:s',$refund_info['add_time']); //支付时间
				$list['userFields']['U_COMPLETE_DATE'] = date('Y-m-d H:i:s',$refund_info['admin_time']);
				$list['userFields']['U_OPCH_COST'] = ncPriceFormat($cost) ;
				$list['docLines'][] = $items;

				$data[$refund_info['refund_id']] = $list;
			}

		}

		return $data ;
	}
    /**
     * 获取结算单的退款单列表
     */
    private function _getRefundList($order_condition, $bill_info,$check_status)
    {
    	//退款订单列表
    	$model_refund = Model('refund_return');
    	$refund_condition = array();
    	$refund_condition['seller_state'] = 2;
    	$refund_condition['store_id'] = $bill_info['ob_store_id'];
    	$refund_condition['goods_id'] = array('gt',0);
		$refund_condition['admin_time'] = array('between',"{$bill_info['ob_start_date']},{$bill_info['ob_end_date']}");
    	$refund_condition['purchase_sap'] = 0 ;
//    	$refund_condition['check_result'] = array('in',array('-1','-2','-3'));
    	$order = 'admin_time ASC' ;
		/*9月1号，过滤苏宁易购，人人优品的荆州门店==15*/
		$refund_condition['filter_status']='0';

    	$field = $bill_info['ob_store_manage_type']=='platform'?
    	C('tablepre').'refund_return.*,ROUND(refund_amount*commis_rate/100,2) as commis_amount':
    	C('tablepre').'refund_return.*,ROUND(refund_amount*cost_rate*commis_rate/10000,2) as commis_amount';
    	$refund_list = $model_refund->getRefundReturnList($refund_condition,'',$field,$this->getLimit('sap502_item'),$order);
    	if (is_array($refund_list) && count($refund_list) == 1 && $refund_list[0]['refund_id'] == '') {
    		$refund_list = array();
    	}
    	
    	$data = array();
    	if( count($refund_list) > 0 ) {
	    	//查询商品原始成本
	    	$gids = array_column($refund_list, 'goods_id') ;
	    	//$g = Model('goods')->where(array('goods_id' => array('in', $gids)))->key('goods_id')->select();
	    	//查询订单商品
	    	$oids = array_column($refund_list, 'order_id') ;
	    	$order_goods = $this -> getOrderGoods( $oids ) ;
	    	$order_model = Model('order') ;
	    	foreach ($refund_list as $refund_info) {
	    		$goods_id = $refund_info['goods_id'] ;
	    		$order_id = $refund_info['order_id'] ;
	    		$ogInfo = $order_goods[ $order_id ][ $goods_id ] ;
	    		
	    		if( ($ogInfo['tax_input'] == '200.00' || $ogInfo['tax_output'] == '200.00')
	    				&& $ogInfo['manage_type'] == 'co_construct') continue ;
	    		
	    		
	    		$refund_amount = $refund_info['refund_amount_bill'] == -1?$refund_info['refund_amount']:$refund_info['refund_amount_bill'];

				//退换佣金值
				$commis_amount = $bill_info['ob_store_manage_type']=='platform' ?
					ncPriceFormat($refund_amount*$ogInfo['commis_rate']/100 ) : 0 ;
	    		
	    		//退还的红包值(平台模式，且全额退款，红包才全额退还)
	    		// 期望方式，但目前不是这样计算的 ：sprintf("%.2f", ($refund_amount/$ogInfo['goods_pay_price'])*$ogInfo['rpt_amount'] );
	    		if ($bill_info['ob_store_manage_type']=='platform' && $refund_amount == $ogInfo['goods_pay_price']) {
	    		    $rpt_amount = $ogInfo['rpt_bill'];
	    		} else {
	    		    $rpt_amount = 0;
	    		}
	    		
	    		//平台和共建/自营 的 最终成本价计算
	    		if ($bill_info['ob_store_manage_type']=='platform') {
	    		    $ogInfo['tax_input'] = $ogInfo['tax_output'] = 0;
	    		    $cost = $refund_amount - $commis_amount + $rpt_amount ; //总退还值
	    		} else {
	    		    $cost =  ($refund_amount/$ogInfo['goods_pay_price'])*$ogInfo['goods_cost']; //总退还值
	    		}
	    		$cost = ncPriceFormat($cost);
	    		        
	    		$items = array() ;
	    		$items['itemCode'] = $refund_info['refund_type'] == 1 ? 444444 : $goods_id ;
	    		$items['LineTotal'] = $refund_amount ;
	    		$items['quantity'] = $items['itemCode']==444444 ? 1 : $refund_info['goods_num'] ;
				$items['priceAfVat'] = ncPriceFormat($cost / $items['quantity'] , 4)  ;//平台最终与供应商结算单品含税单价
	    		$items['userFields']['U_INV1_COMSCALE'] = $refund_info['commis_rate'] ;
	    		$items['userFields']['U_INV1_COMPRICE'] = ncPriceFormat($commis_amount);
	    		$items['userFields']['U_INV1_OITM_PUR'] = ncPriceFormat($items['priceAfVat']);//平台最终与供应商结算单品含税单价，值与毛价相等。
	    		$items['userFields']['U_TAX_RATE'] = $ogInfo['tax_input']/100 ;
	            $items['vatGroup'] = inputTax( $ogInfo['tax_input'] ) ;
	    		
	    		$list = array();
	    		$list['tid']		=	'sap502_'.$bill_info['ob_id'] . "_" . $refund_info['refund_id'] . "_refund" ;
	            // $list['docDate'] = empty($refund_info['admin_time']) ? 
	                // date('Y-m-d H:i:s', $refund_info['add_time']) : date('Y-m-d H:i:s', $refund_info['admin_time']);//退款完成时间
	
	    		$list['cardCode'] 	= $refund_info['store_id'] ;
	    		$list['cardName'] 	= $refund_info['store_name'] ;
	    		$list['userFields']['U_OINV_ENUMBER'] = $refund_info['order_sn'] ;
	    		$list['userFields']['U_OPOR_PAYNUMBER'] = $bill_info['ob_id'] ;
	    		$list['userFields']['U_ALL_Chorge'] = ncPriceFormat($commis_amount);
	    		$list['userFields']['U_OPCH_REDPACK'] = ncPriceFormat($rpt_amount);
	            $list['userFields']['U_ORPD_RETURN_NUMBER'] = $refund_info['refund_id'] ;
	            $list['userFields']['U_PAY_DATE'] = date('Y-m-d H:i:s',$refund_info['add_time']); //支付时间
	            $list['userFields']['U_COMPLETE_DATE'] = date('Y-m-d H:i:s',$refund_info['admin_time']);
	    		$list['userFields']['U_OPCH_COST'] = ncPriceFormat($cost) ;
				$list['docLines'][] = $items;
	
	    		$data[$refund_info['refund_id']] = $list;
	    	}
	    	
    	}

    	return $data ;
    }
    
    private function get_manage_type($type)
    {
    	return str_replace(array('platform','co_construct','hango','b2b'), 
    			array('平台商家', '共建商家', '自营', '3C商家'), $type) ;
    }
    
    //修改推送标志
    private function updateSendState($ids, $state, $table)
    {
    	if (empty($ids)) return true;
    	$collumn = $table == 'orders' ? 'order_id' : 'refund_id' ;
    	$collumn = $table == "store_cost" ? "cost_id" : $collumn ;
    	$where[$collumn] = array('in', $ids);
    	switch ($state) {
    		case 0:
    			$where['purchase_sap'] = '1';
    			break;
    		case 1:
    			$where['purchase_sap'] = '0';
    			break;
    		case 2:
    			// $where['purchase_sap'] = '1';
    			break;
    		default:
    			return true;
    	}
    	Model($table)->where($where)->update(array('purchase_sap' => $state));
    	return true;
    }
    
    //查询订单商品
    private function getOrderGoods($order_ids)
    {
    	$order_goods_condition = array();
    	$order_goods_condition['order_id'] = array('in',$order_ids);
    	$field = '*';
    	$order_goods_list = Model('order')->getOrderGoodsList($order_goods_condition,$field);
    	
    	$return = array() ;
    	foreach ($order_goods_list as $order_goods)
    	{
    		$return[ $order_goods['order_id'] ][ $order_goods['goods_id'] ] = $order_goods ;
    	}
    	
    	return $return ;
    }
    
    
    private function parseTids( $tids, $nums = 2 )
    {
    	$ids = $obids = array() ;
    	
    	if( $nums == 2 ){
	    	
    		foreach ($tids as $tid) {
	    		list($obids[], $ids[] ) = explode("_", $tid) ;
	    	}
	    	return array($obids, $ids) ;
	    	
    	} else if( $nums == 3 ) {
    		
    		foreach ($tids as $tid) {
    			list($obids[], $ids[], $type ) = explode("_", $tid) ;
    		}
    		return array($obids, $ids, $type) ;
    		
    	}
    }

	function storecost_check($ob_id,$check_status = 0,$pay = 0)
	{
		$condition['ob_id'] = $ob_id;
		$bill_info = Model('order_bill')->where($condition)->limit(100)->find();
		$bill_orders = $this -> _get_bill_info( $bill_info, 'storecost',$check_status,$pay ) ;
		return $bill_orders ;
	}

    function storecost()
    {
    	//$code = $this->getCode(__CLASS__, __FUNCTION__);
    	$condition['ob_store_cost_totals'] = array('gt', 0) ;
    	$condition['ob_sap_storecost'] = '0' ;
    	$condition['ob_state'] = '3' ;
    	$list = Model('order_bill')->where($condition)->limit(100)->select();
    	$data = array() ;
    	foreach ( $list as $bill_info ) {
    		$bill_orders = $this -> _get_bill_info( $bill_info, 'storecost' ) ;
    		$data = array_merge($data, $bill_orders) ;
    	}

    	return $data ;
    }

	/**
	 * 获取结算单的订单列表
	 */
	private function _getStorecostListCheck($order_condition, $bill_info,$check_status = 0,$pay = 0)
	{

		$model_storecost = Model('store_cost');
		$cost_condition['cost_store_id'] = $order_condition['store_id'];
		$cost_condition['cost_price'] = array('gt', 0) ;
		$cost_condition['cost_time'] = array(array('egt',$bill_info['ob_start_date']),array('elt',$bill_info['ob_end_date']),'and');
		$cost_condition['fx_order_id'] = array('gt', 0) ;
		if($check_status > 0){
			$cost_condition['check_status'] = array('neq',$check_status);
		}
		if($pay > 0){
			$pay_array = array(
				'1' => '0',
				'2' => '-1',
				'3' => '-2'
			);
			$cost_condition['check_result'] = $pay_array[$pay] ;
		}
		$list = $model_storecost->where($cost_condition)->limit(20)->select();
        $cost_condition['fx_order_id'] = 0 ;
        $cost_condition['type'] = 1 ;

        $list1 = $model_storecost->where($cost_condition)->limit(20)->select();
        $list = array_merge($list,$list1);

		$store_cost_id_array = array();
		if (is_array($list)) {
			foreach ($list as $storecost_info) {
				$store_cost_id_array[] = $storecost_info['cost_id'];
			}
		}

		$order_check_condition = array();
		$order_check_condition['cost_id'] = array('in',$store_cost_id_array);

		$data = array();
		$data['check_status'] = $check_status;
		$model_storecost->where($order_check_condition)->update($data);

		$store_ids = array_unique( array_column($list, 'cost_store_id') ) ;
		/** @var storeModel $model_store **/
		$model_store = Model('store') ;
		$store_condition = array();
		$store_condition['store_id'] = array('in', $store_ids) ;
		$stores = $model_store->field("store_name,store_id")->where( $store_condition )->key('store_id')->select();
		$fx_oids = array_unique( array_column($list, 'fx_order_id') ) ;
		/** @var orderModel $model_order **/
		$model_order = Model("orders");
		$order_where = array();
		$order_where['fx_order_id'] = array('in',$fx_oids);
		$orders = $model_order->field("order_sn,fx_order_id")->where($order_where)->key('fx_order_id')->select();

		$data = array();
		foreach ($list as $storecost)
		{
			if( !isset($stores[$storecost['cost_store_id']])) continue;

			$items = array() ;
			$items['itemCode'] = 'KK00001' ;
			$items['LineTotal'] = $storecost['cost_price'] ;
			$items['quantity'] = 1 ;
			$items['priceAfVat'] = $storecost['cost_price']  ;//平台最终与供应商结算单品含税单价
			//$items['userFields']['U_INV1_COMSCALE'] = '0' ;
			//$items['userFields']['U_INV1_COMPRICE'] = '0';
			//$items['userFields']['U_INV1_OITM_PUR'] = '0';//平台最终与供应商结算单品含税单价，值与毛价相等。
			//$items['userFields']['U_TAX_RATE'] = '0' ;

			$list = array();
			$list['tid'] = 'sap502_'.$bill_info['ob_id'] . "_" . $storecost['cost_id'] . "_storecost" ;
			$list['cardCode'] 	= $storecost['cost_store_id'] ;
			$list['cardName'] 	= $stores[$storecost['cost_store_id']]['store_name'] ;
			$list['userFields']['U_OINV_ENUMBER'] =  empty($storecost['fx_order_id'])?'':$orders[$storecost['fx_order_id']]['order_sn'];
			$list['userFields']['U_OPOR_PAYNUMBER'] = $bill_info['ob_id'] ;
			//$list['userFields']['U_ALL_Chorge'] = '0';
			//$list['userFields']['U_OPCH_REDPACK'] = '0';
			$list['userFields']['U_ORPD_RETURN_NUMBER'] = "sc".$storecost['cost_id'] ;
			$list['userFields']['U_PAY_DATE'] = date('Y-m-d H:i:s',$storecost['cost_time']);
			$list['userFields']['U_COMPLETE_DATE'] = date('Y-m-d H:i:s',$storecost['cost_time']);
			//$list['userFields']['U_OPCH_COST'] = '0' ;
			$list['docLines'][] = $items;

			$data[] = $list ;
		}
		return $data ;
	}

    /**
     * 获取结算单的订单列表
     */
    private function _getStorecostList($order_condition, $bill_info)
    {

    	$model_storecost = Model('store_cost');
    	$cost_condition['cost_store_id'] = $order_condition['store_id'];
    	$cost_condition['cost_price'] = array('gt', 0) ;
    	$cost_condition['cost_state'] = 0;
    	$cost_condition['cost_time'] = array(array('egt',$bill_info['ob_start_date']),array('elt',$bill_info['ob_end_date']),'and');
    	$cost_condition['fx_order_id'] = array('gt', 0) ;
    	$cost_condition['purchase_sap'] = 0;
    	$list = $model_storecost->where($cost_condition)->limit(20)->select();
        $cost_condition['fx_order_id'] = 0 ;
        $cost_condition['type'] = 1 ;

        $list1 = $model_storecost->where($cost_condition)->limit(20)->select();
        $list = array_merge($list,$list1);
    	$store_ids = array_unique( array_column($list, 'cost_store_id') ) ;
    	/** @var storeModel $model_store **/
    	$model_store = Model('store') ;
		$store_condition = array();
    	$store_condition['store_id'] = array('in', $store_ids) ;
    	$stores = $model_store->field("store_name,store_id")->where( $store_condition )->key('store_id')->select();
    	$fx_oids = array_unique( array_column($list, 'fx_order_id') ) ;
    	/** @var orderModel $model_order **/
    	$model_order = Model("orders");
		$order_where = array();
    	$order_where['fx_order_id'] = array('in',$fx_oids);
    	$orders = $model_order->field("order_sn,fx_order_id")->where($order_where)->key('fx_order_id')->select();
    	
    	$data = array();
    	foreach ($list as $storecost) 
    	{
    		if( !isset($stores[$storecost['cost_store_id']])) continue;


            $items = array() ;
            $items['itemCode'] = 'KK00001' ;
            $items['LineTotal'] = $storecost['cost_price'] ;
            $items['quantity'] = 1 ;
            $items['priceAfVat'] = $storecost['cost_price']  ;//平台最终与供应商结算单品含税单价
    		/*if(isset($orders[$storecost['fx_order_id']]) ){
            }else{

            }*/

            //$items['userFields']['U_INV1_COMSCALE'] = '0' ;
    		//$items['userFields']['U_INV1_COMPRICE'] = '0';
    		//$items['userFields']['U_INV1_OITM_PUR'] = '0';//平台最终与供应商结算单品含税单价，值与毛价相等。
    		//$items['userFields']['U_TAX_RATE'] = '0' ;
    		
    		$list = array();
    		$list['tid'] = 'sap502_'.$bill_info['ob_id'] . "_" . $storecost['cost_id'] . "_storecost" ;
    		$list['cardCode'] 	= $storecost['cost_store_id'] ;
    		$list['cardName'] 	= $stores[$storecost['cost_store_id']]['store_name'] ;
    		$list['userFields']['U_OINV_ENUMBER'] = empty($storecost['fx_order_id'])?'':$orders[$storecost['fx_order_id']]['order_sn'] ;
    		$list['userFields']['U_OPOR_PAYNUMBER'] = $bill_info['ob_id'] ;
    		//$list['userFields']['U_ALL_Chorge'] = '0';
    		//$list['userFields']['U_OPCH_REDPACK'] = '0';
    		$list['userFields']['U_ORPD_RETURN_NUMBER'] = "sc".$storecost['cost_id'] ;
    		$list['userFields']['U_PAY_DATE'] = date('Y-m-d H:i:s',$storecost['cost_time']);
    		$list['userFields']['U_COMPLETE_DATE'] = date('Y-m-d H:i:s',$storecost['cost_time']);
    		//$list['userFields']['U_OPCH_COST'] = '0' ;
    		$list['docLines'][] = $items;
    		
    		$data[] = $list ;
    	}
    	return $data ;
    }

    //退款数据修正重新推送
    function refundWriteoff()
    {
    	$log_condition = array();
    	$log_condition['log_status'] = '1';
    	$log_condition['log_type'] = array('in', array('refund','data') );
    	$log_condition['refund_sap'] = '2';
    	$log_condition['purchase_refund_sap'] = '0';
    	//$log_condition['log_model'] = 'refund_return';
    	$log_condition['order_id'] = array('gt', 0);
    	$log_condition['ob_id'] = array('gt', 0);
    	//$log_condition['rec_id'] = array('gt', 0);
    	$res = Model('bill_log')->where($log_condition)->order('log_id asc')->group('ob_id')->select();
    	$return = array();
    	if( is_array($res) && !empty($res) ){
    		$ob_ids = array_unique( array_column($res, 'ob_id') ) ;
    		$condition = array();
    		$condition['ob_id'] = array('in',$ob_ids) ;
    		$return = Model('order_bill')->where($condition)->limit($this->getLimit('sap502'))->select();
    		$this->writeoff_obids = $ob_ids ;
    	}
    	
    	return $return;
    }
}