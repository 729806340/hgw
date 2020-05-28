<?php

class purchase extends commons
{
	public $orderMode;


	/**
	 * @return false|int
	 */
	public function getCommVer1Time()
	{
		return strtotime('2016-10-31 14:10');
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
		$data = $this -> _getOrderList($order_condition, $bill_info,$check_status) ;
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
            if ($goods['manage_type']=='platform') {
                $comprice = ncPriceFormat($goods['goods_pay_price'] * $goods['commis_rate'] / 100);

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
    		$items['userFields']['U_INV1_COMSCALE']	=	''.$scale;
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
    		$list['docLines'] = $goods_data[$order_info['order_id']] ;
    		 
    		$data[$order_info['order_id']] = $list;
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


}