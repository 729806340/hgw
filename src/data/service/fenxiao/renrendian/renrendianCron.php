<?php

require_once 'rrdInterface.php';

class renrendianCron
{

	static $source = "renrendian" ;
	
	function __construct( $getRel = 1 ){

		$model_member = TModel("Member") ;
		$conditon = array() ;
		$condition = array("member_name" => self::$source) ;
		$row = $model_member -> where( $condition ) -> find() ;
		$this->member_id = $row['member_id'] ;
		$model_member->execute("set wait_timeout=1000") ;
		
		//商品映射
		if( $getRel ) {
			$this -> rel = $this -> getGoodsRel() ;
			//$this -> oldRel = $this -> getOldGoodsRel() ;
		}
    }
	
	//获取商品映射
	function getGoodsRel()
	{
		$result = TModel("B2cCategory") -> where ( array('uid' => $this->member_id) ) -> select () ;
		$rel = $result ? array_column($result, 'pid', 'fxpid') : array() ;

		return $rel ;
	}
	//老平台商品映射
	/*function getOldGoodsRel()
	{
		$result = ecModel("B2cCategory") -> where ( array('uid' => $this->member_id) ) -> select () ;
		$rel = $result ? array_column($result, 'pid', 'fxpid') : array() ;
		return $rel ;
	}*/
	
	//获取人人店订单
	function orderlist( $params = array() ) 
	{
		$order_api = $params['order_api'] ;
		$service = $params['service'] ;
		if( !$order_api ) die("no order api") ;
		
		$beginTime = $params['begin'] ? strtotime($params['begin']) : $this->timestamp - 3600 ;
		$endTime = $params['end'] ? strtotime($params['end']) : $this->timestamp ;

		$urls = array() ;
		
		$rrdInterface = new rrdInterface() ;
	
		$res = $rrdInterface->getOrderList( "tosend",date('Y-m-d H:i:s', $beginTime), date('Y-m-d H:i:s', $endTime) ) ;
		//v($res);
		if( empty($res) ) return ;
		$apiOrdernos = array_column($res, 'order_sn') ;
		log::selflog("rrd api：".count($res) . " | ". json_encode( $apiOrdernos ), self::$source) ;

		$savedOrderIds = $service -> getSavedidByApiorderno( $apiOrdernos ) ;
		log::selflog("saved orders:{$filter['startDate']} - {$filter['endDate']}" . json_encode($savedOrderIds), self::$source) ;
	
		$this -> noRelNums = 0 ;
		$order_data = $this -> bns = array() ;
		foreach($res as $order){
			if( in_array($order['order_sn'], $savedOrderIds) ) continue ;
			if( !empty( $order['package'] ) ) continue ;

			$detail = $this -> getOrderDetail( $order ) ;
			if( !empty( $detail ) ) {
				$order_data[] = $detail ;
			}
		}
		
		$datas = $this -> getOrderData( $order_data ) ;
		
		$service -> doCreateOrder( $datas ) ;
	}
	
	function getOrderData( $order_data )
	{
		//查找货号对应的商品ID
		if( !empty($this -> bns) && $this -> noRelNums ) {
			//新平台
			$bns = array_unique( $this -> bns ) ;
			$condition = array();
			$condition['goods_serial'] = array('in', $this -> bns) ;
			$result = TModel('goods') -> field('goods_id, goods_serial') -> where ( $condition ) -> select () ;
			$proids_new = array_column( $result, 'goods_id', 'goods_serial' );
		}
		
		$datas = array() ;
		
		foreach($order_data as &$order) {
			$continue = 0 ;
			$new = $old = 0 ; //统计新老平台商品个数
			foreach ($order['item'] as $k => $_item ) {
				$fx_goods_id = $_item['fx_goods_id'];
				$bn = $_item['bn'];
				if( isset( $this -> rel[ $fx_goods_id ] ) && $this -> rel[ $fx_goods_id ] ) {
					$_item['goods_id'] = $this -> rel[ $fx_goods_id ] ;
					$_item['platform'] = 'new' ;
					$new++;
				} else if( isset( $this -> oldRel[ $fx_goods_id ] ) && $this -> oldRel[ $fx_goods_id ] ) {
					$_item['goods_id'] = $this -> oldRel[ $fx_goods_id ] ;
					$_item['platform'] = 'old' ;
					$old++;
				} else if( $bn && isset($proids_new[ $bn ]) && $proids_new[ $bn ] ) {
					$_item['goods_id'] = $proids_new[ $bn ] ;
					$_item['platform'] = 'new' ;
					$new++;
				} else {
					$this -> _error( $order['order_sn'], "分销商品 ".$_item['name']."({$fx_goods_id}) 找不到对应的汉购商品" );
					$continue = 1 ;
				}
				unset($_item['bn']);
				unset($_item['fx_goods_id']);
				$order['item'][$k] = $_item;
			}
			if( $continue ) continue ;
				
			if( $new == 0 && $old == 0 ) {
				continue ;
			} else if( $new == 0 ) {
				$order['platform'] = 'old' ;
			} else if( $old == 0 ) {
				$order['platform'] = 'new' ;
			} else {
				$order['platform'] = 'both' ;
			}
			$datas[] = $order ;
		}
		
		return $datas ;
	}
	
	//组装订单明细
	function getOrderDetail( $value )
	{
		if( $value['consignee'] == '代理' && $value['mobile'] == '18123456789' ) {
			return "" ;
		}
	
		$items = array();
		foreach( $value['order_goods'] as $goodsrow ) {
			$goods_id = $goodsrow['goods_id'] ;
			if( !isset( $this -> rel[ $goods_id ] ) || $this -> rel[ $goods_id ] == 0 ) {
				$this -> noRelNums++;
			}
			if($goodsrow['product_sn']){
				$this -> bns[] = $goodsrow['product_sn'] ;
			}
			$items[] = array(
					'fx_goods_id' => $goods_id,
					'name' => $goodsrow['goods_name'],
					'num' => $goodsrow['quantity'],
					'price' => $goodsrow['price'],
                    'fxpid' => $goods_id,
                    'oid' => $goodsrow['id'],
					'bn' => $goodsrow['product_sn']
			) ;
		}
	
		if( empty($items) ) return "" ;
	
		//统计余额付款
		$yue_pay = 0.00;
		foreach( $value['coupon_amount_details'] as $_cd ){
			if($_cd['type'] == 4){
				$yue_pay += $_cd['amount'] ;
			}
		}
		$amount = $value['amount'] + $yue_pay ;

		$discount = $value['goods_amount'] > $amount ? $value['goods_amount'] - $amount : '0' ;

		$detail = array() ;
		$detail['order_sn'] = $value['order_sn']; //分销系统订单编号
		$detail['buy_id'] = $this->member_id; //分销商用户编号
		$detail['receiver']= $value['order_consigner_addr']['consignee'];//收件人
		$detail['provine'] = $value['order_consigner_addr']['province_name'];
		$detail['city'] = $value['order_consigner_addr']['city_name'];
		$detail['area'] = $value['order_consigner_addr']['district_name'];
		$detail['address'] = $value['order_consigner_addr']['address'];
		$detail['mobile']= $value['order_consigner_addr']['mobile']; //手机号码
		$detail['remark'] = $value['memo'];
		$detail['amount'] = $amount;
		$detail['payment_code'] = 'fenxiao';//订单来源  fenxiao,jicai
		$detail['order_time']= strtotime( $value['created_at'] );//下单时间，时间戳
		$detail['item'] = $items ;
		$detail['discount'] = $discount ;
	    $detail['shipping_fee']=$value['shipment_fee'];//运费
		return $detail ;
	}

	function getOrderStatusGet()
	{
		$orderSNs = $_POST['order_sn'];
		$rrdInterface = new rrdInterface() ;
		$orderSNs = explode(',', $orderSNs);
		$return = array();

		foreach ($orderSNs as $fx_order_id) {
			//查询人人店接口
			$orderDetail = $rrdInterface -> getOrderDetail( $fx_order_id );

			$yue_pay = 0.00;
			foreach( $orderDetail['coupon_amount_details'] as $_cd ){
				if($_cd['type'] == 4){
					$yue_pay += $_cd['amount'] ;
				}
			}
			v($yue_pay);
			//组装所有分销渠道该接口的统一的返回数据格式
			$item = array(
				'orderSn' => $orderDetail['order_sn'],
				'orderStatus' => $orderDetail['status'] == 50 ? 3: 0,
				'refundStatus' => 0,
			);
			$fenxiaoList['orderStatus']['list'][] = $item;
		}
		$fenxiaoList['orderStatus']['result'] = 1;
		return $fenxiaoList;
	}
	/**
	 * 获得订单状态和售后
	 *
	 * @param unknown $orderSNs 批量请用半角逗号分开
	 */
	function getOrderStatus($orderSNs)
	{
	    $rrdInterface = new rrdInterface() ;
	    $orderSNs = explode(',', $orderSNs);
	    $return = array();
	    foreach ($orderSNs as $fx_order_id) {
	        //查询人人店接口
	        $orderDetail = $rrdInterface -> getOrderDetail( $fx_order_id ) ;
	        //组装所有分销渠道该接口的统一的返回数据格式
	        $item = array(
	        	'orderSn' => $orderDetail['order_sn'],
	            'orderStatus' => $orderDetail['status'] == 50 ? 3: 0,
	            'refundStatus' => 0,
	        );
	        $fenxiaoList['orderStatus']['list'][] = $item;
	        
	    }
	    $fenxiaoList['orderStatus']['result'] = 1;
	    return $fenxiaoList;
	}
	
//保存错误信息到日志table
	function _error($orderno, $errorinfo, $log_type='order')
	{
		$model = Model("b2c_order_fenxiao_error");
		$where = array(
				'orderno' => $orderno,
				'error' => $errorinfo
		) ;
		if( $model -> where ( $where ) -> count() > 0 ) return ;
		
		$data = array(
				'orderno' => $orderno,
				'error' => $errorinfo,
				'order_time' => 0,
				'log_time' => time(),
				'sourceid' => $this -> member_id,
				'source' => self::$source,
				'log_type' => $log_type
		) ;
	
		$model -> insert ( $data ) ;
	}
	
	function push_ship( $params = array() )
	{
		log::selflog(var_export($params,true), self::$source) ;
		$data = array(
			'orderno' => $params['orderno'],
			'logi_no' => $params['logi_no'],
			'oid' => $params['oid'],
			'num' => $params['num'],
			'logi_code' => $this -> chgLogiCode( $params['logi_name'] )
		) ;
		$rrdInterface = new rrdInterface() ;
		$result = $rrdInterface -> pushShip( $data );
		
		log::selflog($result, self::$source) ;
		if( !$result || strpos($result, 'DOCTYPE html') !== false ) {
			$this->_error($params['orderno'], json_encode($params), 'unship') ;
			$res = json_encode( array('succ' => '0', 'msg' => '发货失败'.json_encode($result)) )  ;
		} else {
			$error = array(
					'5011001' => '缺少order_sn订单号',
					'5011002' => '物流公司代码错误',
					'5011003' => '子订单sub_order_info非json格式',
					'5011004' => '无效order_sn订单号',
					'5011005' => '订单状态不正确',
					'5011006' => '物流信息不完整',
					'5011007' => '发货商品数量不合法',
					'5011008' => '子订单商品不存在',
					'5011009' => '发货数量超出最大可发数量'
			) ;
			$re = json_decode($result, true) ;
			$errCode = $re['errCode'] ;
			if( !in_array($errCode, array('0', '5011005')) ) {
				$res = json_encode( array('succ' => '0', 'msg' => $error[ $errCode ]) ) ;
			} else {
				$res = json_encode( array('succ' => '1', 'msg' => '发货测试成功') )  ;
			}
		}
		
		return $res ;
	}
	
	function chgLogiCode( $name )
	{
		$data = array(
				'中通快递' => 'zhongtong',
				'韵达快递' => 'yunda',
				'百世汇通' => 'huitongkuaidi',
				'圆通快递' => 'yuantong',
				'申通快递' => 'shentong',
				'EMS' => 'ems',
				'顺丰快递' => 'shunfeng',
				'优速快递' => 'youshuwuliu',
				'天天快递' => 'tiantian',
				'宅急送' => 'zhaijisong',
				'快捷速递' => 'kuaijiesudi',
				'全峰快递' => 'quanfengkuaidi',
				'安能物流' => 'annengwuliu'
		) ;
		return $data[$name] ? $data[$name] : 'ems' ;
	}
	
	/**
	 * 漏单检测，凌晨检测前3天的未发货订单是否已保存为汉购网订单
	 */
	function checkUnsaveOrder( $params )
	{
		$hour = date('G');
		if( $hour >= 9 && $params['preDay']==1  ) {
			$params['preDay'] = 1 ;
		}
		log::selflog("check unsave order begin", self::$source ) ;
	
		$limit = $params['preDay'] == 0 ? 1 : $params['preDay'] ;
		
		for ( $i=$limit; $i >=1; $i-- ) {
			$b_time = time() - $i * 24 * 3600 ;
			$e_time = $b_time + 24 * 3600 ;
			$params['begin'] = date('Y-m-d H:i:s', $b_time) ;
			$params['end'] = date('Y-m-d H:i:s', $e_time) ;
			$this -> orderlist( $params ) ;
		}
	}
	
	function getRefundOrder( $service )
	{
		$this -> service = $service ;
		$rrdInterface = new rrdInterface() ;
		$res = $rrdInterface->getOrderList( "refund" ) ;
		//v($res);
		$refundData = array();
		if( empty($res) || !is_array($res) ) exit;
		
		foreach($res as $order){
			//不处理6.1之前的售后
			if( $order['created_at'] < '2016-06-01 00:00:00' ) continue ;
		
			$order_sn = $order['order_sn'] ;
			//查找未发货商品中申请退款的子订单
			foreach($order['order_refund'] as $refund) {

				if( isset($refund[0]['goods_id']) && $refund[0]['goods_id'] ) {
					$fx_goods_id = $refund[0]['goods_id'] ;
					if( isset( $this -> rel[ $fx_goods_id ] ) && $this -> rel[ $fx_goods_id ] ) {
						$goods_id = $this -> rel[ $fx_goods_id ] ;
						$platform = "new";
					} else if( isset( $this -> oldRel[ $fx_goods_id ] ) && $this -> oldRel[ $fx_goods_id ] ) {
						$goods_id = $this -> oldRel[ $fx_goods_id ] ;
						$platform = "old";
					} else {
						$this -> _error( $order_sn, "分销商品 ({$goods_id}) 没有配置商品映射，无法生成退款" );
						continue ;
					}
		
					$refundData[$order_sn][$platform][$goods_id] = array(
									'refund_money' => $refund[0]['amount'],
									'refund_reason' => $refund[0]['reason'],
					);
				}
			}
		}
		//v($refundData);
		$this -> doRefundData( $refundData ) ;
	}
	
	//处理退款数据
	function doRefundData( $refundData )
	{
		if( !empty($refundData) ) {
			$data['new'] = $this -> _doNewPlatformRefund($refundData) ;
			//$data['old'] = $this -> _doOldPlatformRefund($refundData) ;
			//v($data) ;
			if( !empty($data['new']) ) {
				log::selflog("开始创建退款单：". var_export($data,true) , self::$source ) ;
			}
			$this -> service -> createRefund( $data ) ;
		}
	}
	//处理新平台退款
	function _doNewPlatformRefund( $refundData )
	{
		//过滤掉非全额退款订单，不做处理
		$refundData = $this -> _unsetPartrefundOrder( $refundData ) ;
		
		if( empty($refundData) ) return array();
		$order_sns = array_keys($refundData) ;

		$new_fsmodel = TModel("B2cOrderFenxiaoSub") ;
		$condition['orderno'] = array('in',$order_sns);
		$re = $new_fsmodel -> where ( $condition ) -> select () ;
		$result = $re ? $re : array() ;
		$newRefund = array() ;
		$returnModel = TModel('RefundReturn') ;
		foreach($result as $suborder){
			$orderno = $suborder['orderno'];
			$goods_id = $suborder['product_id'] ;
		
			//匹配未付款子订单
			if( isset( $refundData[$orderno]['new'][$goods_id] )  ){
		
				$ordersn = $this -> service -> _getFxorderSn($orderno, $goods_id) ;
				if( !$ordersn ) continue ;
				//检查子订单是否已申请退款或取消订单
				$filter=array();
				$filter['order_sn'] = $ordersn ;
				$filter['goods_id'] = array('in', array(0,$goods_id)) ;
				if( $returnModel->where( $filter )-> count() > 0) {
					//echo "商品已申请<br>";
					continue ;
				}
				
				$data = array() ;
				$data['reason_id'] = 100; //退款退货理由 整型
				$data['refund_type'] = 3; //申请类型 1. 退款  2.退货
				$data['return_type'] = 3; //退货情况 1. 不用退货  2.需要退货
				$data['seller_state'] = 1; //卖家处理状态:1为待审核,2为同意,3为不同意,默认为1
				$data['refund_amount'] = $refundData[$orderno]['new'][$goods_id]['refund_money'];//退款金额
				$data['goods_num'] = $suborder['num'];//商品数量
				$data['buyer_message'] = $refundData[$orderno]['new'][$goods_id]['refund_reason'];  //用户留言信息
				$data['ordersn'] = $ordersn;  //汉购网订单编号
				$data['goods_id'] = $suborder['product_id']; //商品编号
		
				$newRefund[] = $data;
			}
		}
		return $newRefund ;
	}
	//处理老平台商品退款
	function _doOldPlatformRefund( $refundData )
	{
		$order_sns = array_keys($refundData) ;
		//查找保存的子订单信息，匹配退款的子订单
		$condition['orderno'] = array('in', $order_sns) ;
		$re = ecModel("B2cOrderFenxiaoSub") -> where ( $condition ) -> select () ;
		$result = $re ? $re : array() ;
		$refund_model = ecModel("AftersalesRefundItems") ;
		$oldRefund = array() ;
		foreach($result as $suborder){
			$orderno = $suborder['orderno'];
			$product_id = $suborder['product_id'] ;
		
			//匹配未付款子订单
			if( isset( $refundData[$orderno]['old'][$product_id] )  ){
		
				//检查子订单是否已申请退款
				if( $refund_model->where( array('order_id' => $suborder['order_id'], 'product_id' => $product_id) )->count() ) {
					echo "老平台商品已申请退款<br>";
					continue ;
				}
		
				$data = array(
						'orderno' => $orderno,
						'order_id' => $suborder['order_id'],
						'product_id' => $suborder['product_id'],
						'product_name' => "",
						'product_price' => "",
						'refund_money' => $refundData[$orderno]['old'][$product_id]['refund_money'],
						'reason' => $refundData[$orderno]['old'][$product_id]['refund_reason'],
						'source' => self::$source,
						'refund_status' => '2', //审核中
						'sourceid' => $this->member_id
				) ;
				
				$oldRefund[] = $data;
			}
		}
		return $oldRefund ;
	}
	//过滤掉未发货订单，但是非全额退款订单
	function _unsetPartrefundOrder( $refundData )
	{
		$order_sns = array_keys ( $refundData ) ;
		$where ['fx_order_id'] = array('in' ,$order_sns ) ;
		$where ['order_state'] = 20 ;
		$orders = TModel('orders') -> where ( $where ) -> select () ;
		if( $orders ) {
			$rel = array_column($orders, 'order_amount', 'fx_order_id') ;
			foreach ( $rel as $fx_order_id => $order_amount ) {
				$fx_refund_goods = array_values( $refundData[$fx_order_id]['new'] );
				$refund_total = array_sum( array_column($fx_refund_goods, 'refund_money') ) ;
				if( floatval($refund_total) != floatval($order_amount) ) {
					unset($refundData[$fx_order_id]) ;
					$this -> _error( $fx_order_id, "未发货分销订单不是全额退款，无法生成退款" );
				} else {
					//全额退款商品有多个时，只提交一次退款
					if( count($refundData[$fx_order_id]['new']) > 1 ) {
						$tmp_key = current(array_keys($refundData[$fx_order_id]['new']));
						$tmp_value = current(array_values($refundData[$fx_order_id]['new']));
						$refundData[$fx_order_id]['new'] = array($tmp_key => $tmp_value);
					}
				}
			}
		}
		return $refundData ;
	}
	/**
	 * 跟踪审核中的维权订单，完成后将refund_status更新为1，推送SAP
	 * //10 申请退款中
	 * //11 再次申请退款中
	 * //20 商家同意退款，等待买家处理
	 * //21 拒绝退款
	 * //22 买家已发货，等待卖家收货
	 * //30 第三方退款中
	 * //31 已经退款，退款完成
	 * //40 已经关闭
	 * //41 用户取消退款
	 */
	function traceRefund( $service )
	{
		$this -> service = $service ;
		//查找未完结的人人店订单
		$refundModel = TModel("RefundReturn") ;
		$condition['buyer_id'] = $this -> member_id ;
		$condition['seller_state'] = 1 ;
		$uncloseRefund = $refundModel -> where ( $condition ) -> select() ;
		if(empty($uncloseRefund)) die ;
		$order_sns = array_column($uncloseRefund, 'order_sn');
		//查找订单号映射
		$ordersModel = TModel("Orders");
		$condition = array(
				'order_sn' => array('in', $order_sns)
		) ;
		$orders = $ordersModel -> where ( $condition ) -> select() ;
		$order_rels = array_column($orders, 'fx_order_id', 'order_sn');
		
		$pro_rels = array_flip( $this -> rel ) ;
		
		$data = array() ;
		foreach($uncloseRefund as $item)
		{
			$order_sn = $item['order_sn'] ;
			$goods_id = $item['goods_id'] ;
			if( isset($order_rels[$order_sn]) && $order_rels[$order_sn] )
			{
				$orderno = $order_rels[$order_sn] ;
		
				//查询人人店接口，获取订单详情
				$rrdInterface = new rrdInterface() ;
				$fxOrderDetail = $rrdInterface->getOrderDetail( $orderno );
				
				if( empty($fxOrderDetail) ) continue ;
				
				if( !empty( $fxOrderDetail['order_refund'] ) ) {
					foreach ($fxOrderDetail['order_refund'] as $order_refund) {
						foreach ($order_refund as $refund) {
							//未发货
							if( $goods_id == 0 ) {
								
								if( isset($this -> rel[ $refund['goods_id'] ]) ){
									$_goods_id = $this -> rel[ $refund['goods_id'] ] ; //对应的汉购商品ID
									$order_goods = $this -> service -> getOrderGoodsByosn ( $order_sn ) ;
									$gids = !empty($order_goods) ? array_column($order_goods, 'goods_id') : array() ;
									if( in_array($_goods_id, $gids) ) {
										//防止重复
										$refund_ids = array_column($data, 'refund_id');
										if( in_array($item['refund_id'], $refund_ids) ) continue ;
										
										$data[] = array(
												'refund_id' => $item['refund_id'],
												'refund_status' => $refund['status'],
												'orderno' => $order_sn,
												'is_ship' => 0
										);
									}
								}
								
							} else { //已发货
								
								//匹配退款条目商品 与 接口 里的商品
								if( $pro_rels[ $goods_id ] == $refund['goods_id'] )
								{
									$data[] = array(
											'refund_id' => $item['refund_id'],
											'refund_status' => $refund['status'],
											'orderno' => $order_sn,
											'is_ship' => 1
									);
								}
								
							}
							
						}
					}
				}
			}
		}
		
		if( empty($data) ) exit;
		
		$refundService = Service("Refund") ;
		$model = Model() ;
		//根据退款状态做相应处理，处理取消退款以及退款完成的订单，其他状态保持不变不做处理
		foreach($data as $row)
		{
			if( !$row['refund_id'] ) continue ;
			
			if( !in_array($row['refund_status'], array('21', '31', '41')) ) continue ;
			
			$params = array(
					'refund_id' => $row['refund_id'] ,
					'op_id' => $this -> member_id,
					'op_name' => self::$source
			) ;
			$msg = "" ;
			//分销用户取消退款，解锁订单(相当于平台商家拒绝退款)
			if( $row['refund_status'] == '41' || $row['refund_status'] == '21' ) {
				$params['seller_state'] = 3 ;
				$method = 'edit_refund' ;
			}
			//退款完成
			if( $row['refund_status'] == '31' ) {v($params,0);
				$method = 'confirm_refund' ;
			}
			
			try{
				$model->beginTransaction();
				
				$res = $refundService -> $method($params, $msg) ;
				if( !$res ) {
					throw new Exception( $msg );
				}
				
				$model->commit();
			} catch ( Exception $e ) {
				$model->rollback();
				$msg = $e->getMessage() ;
			}
			v($msg,0) ;
		}
	}

	function importOrders( $ids )
	{
		if( empty($ids) )  return 0 ; 
		
		$service = Service("Fenxiao") ;
		$savedIds = $service -> getSavedidByApiorderno( $ids ) ;
		
		$unsave = array_diff($ids, $savedIds) ;
		if( empty($unsave) ) return 0 ; 
		//v($unsave);
		$rrdInterface = new rrdInterface() ;
		
		$this -> noRelNums = 0 ;
		$order_data = $this -> bns = array() ;
		foreach ( $unsave as $order_sn )
		{
			$order = $rrdInterface -> getOrderDetail( $order_sn ) ;
			if( !empty( $order['package'] ) ) continue ;
			
			$detail = $this -> getOrderDetail( $order ) ;
			if( !empty( $detail ) ) {
				$order_data[] = $detail ;
			}
		}
		
		$datas = $this -> getOrderData( $order_data ) ;//v($datas);
		return $service -> doCreateOrder( $datas, 0 )   ;
	}
	
	/**
	 * 检测商家已发货，但发货状态未推送到人人店的订单，并补发
	 * 查出汉购网数据库中最近3天已发货人人店订单，查询人人店订单接口是否这个订单已发货
	 */
	function checkUnshipOrder()
	{
		$hour = date('G');
		//凌晨检测最近3天，其他时间检测最近3小时
		$updateTime = $hour >= 6 ? time() - 3600*3 : time() - 3600*24*3 ;
		
		$comm_where = array();
		$comm_where['shipping_time'] = array('gt', $updateTime) ;
		$result = Model('order_common') -> where ( $comm_where ) -> select () ;
		if( !$result ) die('no result') ;
		
		$oids = array_column($result, 'order_id') ;
		$oid_expressid_rels = array_column($result, 'shipping_express_id', 'order_id');
		//属于人人店的订单
		$where = array() ;
		$where['order_id'] = array('in', $oids) ;
		$where['buyer_id'] = $this->member_id ;
		$orders = TModel('orders')->where($where)->select() ;
		if( !$orders ) die('no orders') ;
		
		$rrdInterface = new rrdInterface() ;
		foreach ($orders as $order) {
			$fx_order_id = $order['fx_order_id'] ;
			
			$goodsWhere = array() ;
			$goodsWhere['order_id'] = $order['order_id'] ;
			$order_items = TModel('order_goods') -> where( $goodsWhere ) -> select () ;
			if( !$order_items ) continue ;

			//查询人人店接口
			$orderDetail = $rrdInterface -> getOrderDetail( $fx_order_id ) ;
			$express = rkcache('express', true) ;
			
			//$service = Service("Fenxiao") ;
			//$service -> init( self::$source ) ;
			
			foreach($orderDetail['order_goods'] as $unship){
				//查询fenxiao_items表，匹配分销子订单ID
				$fsubWhere = array() ;
				$fsubWhere['orderno'] = $fx_order_id ;
				$fsubWhere['product_id'] = array('in', array_column($order_items, 'goods_id') ) ;
				$fSub = TModel('b2c_order_fenxiao_sub') -> where ( $fsubWhere ) -> select () ;
				
				foreach ($fSub as $sub) {
					$canShipStatusArr = array('0', '41') ;
					if( $unship['id'] == $sub['oid'] && in_array($unship['refund_status'], $canShipStatusArr) ) {
						$express_id = $oid_expressid_rels[ $order['order_id'] ] ;
						$data = array();
						//$data['source'] = self::$source;
						//$data['sourceid'] = $this->member_id;
						$data['orderno'] = $fx_order_id;
						$data['logi_no'] = $order['shipping_code'];
						$data['logi_name'] = $express[$express_id]['e_name'];
					    $data['num'] = $sub['num'];
					    //$data['full_ship'] = 1;
					    $data['oid'] = $sub['oid'];
					    
    					$this -> push_ship($data) ;
					}
				}
				
			}
		}
	}
}