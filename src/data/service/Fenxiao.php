<?php
/**
 * Author: zengxj
 */

class FenxiaoService
{
	private $objCron ;
	//创建订单接口地址
	private $order_api;
	//创建退款单接口地址
	private $refund_api;
	
	function __construct()
	{
		$site_url = str_replace("admin/modules/", "", C('admin_modules_url'));
		$this -> order_api = $site_url . "/api/fenxiao/order.php" ;
		$this -> refund_api = $site_url . "/api/fenxiao/refund.php" ;
	}
	
	function init( $source, $getRel = '1' )
	{
		if( !$source ) {
			die("no source") ;
		}
		$class_file = __DIR__ . "/fenxiao/" . $source . "/" . $source . "Cron.php" ;
		if (!file_exists($class_file)) {
		    require_once  __DIR__ . "/fenxiao/empty/emptyCron.php"  ;
		    $this -> objCron = new emptyCron();
			return ;
		}
		require_once $class_file ;
		$class_name = $source . "Cron" ;
		$this -> objCron = new $class_name($getRel) ;
	}
	
	function orderlist( $params = array() )
	{
		$params['order_api'] = $this -> order_api ;
		$params['service'] = $this ;
		$this -> objCron ->orderlist( $params ) ;
	}
	function getFormConfig($api="orderlist",$flag=0){
//        $params['order_api'] = $this -> order_api ;
//        $params['service'] = $this ;
        $form=$this -> objCron -> getFormConfig();
        if($flag){return $form;}
        return  $form[$api];
    }
	public function getApi($param){
        $api=$param['api']."Test";
	    return $this->objCron->$api($param);
    }
	function pushiship( $params = array() )
	{
		return $this -> objCron -> push_ship( $params ) ;
	}
	
	function checkUnsaveOrder($preDay=1)
	{
		$params['order_api'] = $this -> order_api ;
		$params['service'] = $this ;
		$params['preDay'] = $preDay ;
		$this -> objCron -> checkUnsaveOrder($params) ;
	}
	
	function saveorder( $num = 50 )
	{
		$params['order_api'] = $this -> order_api ;
		$params['num'] = $num ;
		$this -> objCron -> saveOrder( $params ) ;
	}

	function getSkuList($params = array())
	{
		return $this -> objCron -> getSkuList($params) ;
	}

	function getRefundOrder()
	{
		$this -> objCron -> getRefundOrder($this) ;
	}
	
	function traceRefund()
	{
		$this -> objCron -> traceRefund($this) ;
	}
	
	function checkUnshipOrder()
	{
		$this -> objCron -> checkUnshipOrder($this) ;
	}
	
	/**
	 * 获得订单状态和售后
	 *
	 * @param unknown $orderSNs 批量请用半角逗号分开
	 */
	function getOrderStatus($orderSNs) {
	    return $this -> objCron ->getOrderStatus($orderSNs);
	}

	function getOrderStatusGet($orderSNs) {
		return $this -> objCron ->getOrderStatusGet($orderSNs);
	}
	
	//创建订单操作
	function doCreateOrder( $order_data, $debug = 1, $flag = false )
	{
		if( empty( $order_data ) ) return 0;
		$fxMembers = $this -> getFenxiaoMembers() ;
		$order_new = $order_old = array() ;
		foreach ( $order_data as $order )
		{
			$order['source'] = $fxMembers[$order['buy_id']] ;
			if( $order['platform'] == 'new' ) {
				$order_new[] = $order ;
			} else if( $order['platform'] == 'old' ) {
				$order_old[] = $order ;
			} else if( $order['platform'] == 'both' ) {
				
				//拆分订单
				$discount = $order['discount'] ;
				
				$rpt = array() ;
				$platform_order_total = array('new' => '0.00', 'old' => '0.00') ;
				foreach($order['item'] as $k=>$v){
					$platform_order_total[ $v['platform'] ] += $v['num'] * $v['price'];
					$item[$v['platform']][] = $v;
				}
				if( $discount > 0 ) {
					$rpt = Logic('buy_1')->parseOrderRpt($platform_order_total, $discount);
				}
				
				$new_order_data = $old_order_data = $order;
				$new_order_data['amount'] = isset($rpt[0]['new']) && $rpt[0]['new'] ? $rpt[0]['new'] : $platform_order_total['new'];
				$new_order_data['discount'] = isset($rpt[1]['new']) && $rpt[1]['new'] ? $rpt[1]['new'] : '0.00';
				$new_order_data['item'] = $item['new'];
				$old_order_data['amount'] = isset($rpt[0]['old']) && $rpt[0]['old'] ? $rpt[0]['old'] : $platform_order_total['old'];
				$old_order_data['discount'] = isset($rpt[1]['old']) && $rpt[1]['old'] ? $rpt[1]['old'] : '0.00';
				$old_order_data['item'] = $item['old'];
				$order_new[] = $new_order_data ;
				$order_old[] = $old_order_data ;
			}
		}
		//v($order_new,0);
		//v($order_old);
		return $this -> _saveNewPlatform( $order_new, $debug, $flag );
		//$this -> _saveOldPlatform( $order_old );
		
	}
	
	function _saveNewPlatform( $order_data, $debug = 1, $flag = false )
	{
		$new_save = $new_save2 = array();
		if( !empty( $order_data ) ) {
			$index = 1;
			/** @var orderModel $orderModel */
			$orderModel = Model('order');
			foreach ( $order_data as $order ) {
				$order['order_from'] = isset($order['order_from']) && !empty($order['order_from']) ? $order['order_from'] : '3' ; //默认分销订单
				$order['key'] = C('order_create_key');
				$params = json_encode($order) ;
        		$res = $orderModel->createFxOrder( $params );
        		if( $debug == 1 ) {
        			v($res,0);
        		}
				if( $res['error'] == '1000' ){
					$new_save[] = $order['order_sn'] ;
                    $new_save2[] = '分销平台订单号：' . $order['order_sn'] . ' 本平台订单号：' . $res['ordersn'];
					$_SESSION['channelfill_ordersn'].=$order['order_sn'].",";
				} else {
					log::selflog("save error:({$order['order_sn']})" . $res['msg'] , $order_data[0]['source']) ;
				}
				$index++;
				if( $index == 5 ){
					sleep(1);
					$index = 1;
				}
			}
			log::selflog("new save orders:" . json_encode($new_save), $order_data[0]['source']) ;
		}
		$_SESSION['channelfill_num']+=count($new_save);
		$count = count($new_save);
		if ($flag == true) {
		    return $new_save2;
        }
		return $count ;
	}
	
	function _saveOldPlatform( $order_data )
	{
		$new_save = array();
		if( !empty( $order_data ) ) {
			$index = 1;
			foreach ($order_data as $data)
			{
				$coupons = array();
				if( $data['discount'] > 0 ){
					$coupons[] = array(
							'pmt_amount' => $data['discount'],
							'pmt_memo' => '订单优惠',
							'pmt_describe' => '订单优惠总金额'
					) ;
					$data['coupons'] = $coupons ;
				}
				//替换item里的goods_id => product_id
				foreach ($data['item'] as &$_item){
					$_item['product_id'] = $_item['goods_id'] ;
					unset($_item['goods_id']);
				}
				
				$url = $this -> fxurl($data) ;
				
				$res = file_get_contents( $url ) ;
				$r = json_decode($res, true) ;
				if( $r['rsp'] == 'succ' ){
					$new_save[] = $r['params']['order_no'] ;
				} else {
					log::selflog("old:create order faild:".$r['res'], $data['source']) ;
				}
				$index++;
				if( $index == 11 ){
					sleep(5);
					$index = 1;
				}
			}
			log::selflog("old:new save orders:" . json_encode($new_save), $order_data[0]['source']) ;
		}
	}
	
	function fxurl($data)
	{
		$shipping = array(
				'shipping_id' => '5',
				'is_protect' => 'false',
				'shipping_name' => '市内快递',
				'cost_shipping' => '0.00', //快递费用
				'cost_protect' => '0.00'
		);
		//付款方式
		$payinfo = array(
				'pay_app_id' => 'fenxiao',
				'cost_payment' => '0.00' //支付手续费，请都填0.00
		);
		//收货信息
		$consignee = array(
				'name' => $data['receiver'],
				'addr' => $data['address'],
				'zip' => '',
				'telephone' => '',
				'mobile' => $data['mobile'],
				'email' => NULL,
				'area' => 'mainland:'.$data['provine'].'/'.$data['city'].'/'.$data['area'].':25',
				'r_time' => '任意时间,任意时间段',
				'meta' => array()
		);

		$order_objects = $data['item'] ;
		//传递的所有参数组装
		$params = array(
				//'price'=> "",
				'poid'=> $data['order_sn'],
				//'pproductname'=> "",
				'is_fastbuy'=>false,
				'instance_pay' => 1,
				'member_id' => $data['buy_id'],
				'createtime' => $data['order_time'] ,
				'memo' => '',
				'ip' => '127.0.0.1',
				'weight' => 0,
				'itemnum' => 1,
				'cost_item' => $data['amount'], // 订单商品总价格
				'cost_tax' => 0,
				'total_amount' => $data['amount'], // 商品默认货币总值
				'cur_amount' => $data['amount'], //final_amount 订单总额, 包含支付价格,税等
				'pmt_goods' => '0.00', //商品优惠金额
				'pmt_order' => $data['discount'], //订单优惠金额
				'discount' => '0.00', //
				'source' => 'fenxiao',
				'shipping' => json_encode($shipping),
				'payinfo' => json_encode($payinfo),
				'consignee' => json_encode($consignee),
				'order_objects' => json_encode($order_objects),
				'site' => $data['source'],
				'order_no' => $data['order_sn'],
				'save_type' => $data['save_type'] == 'insert' ? 'insert' : 'save',
				'coupons' => !empty( $data['coupons'] ) ? $data['coupons'] : "",
				'memo' => $data['remark'],
		);
		$uri = C('old_platform') . "/index.php/api?method=b2c.order.basic.createfx&sign=eff90f9f07d591ac969dfc4750674ce2&";
		$uri .= http_build_query($params);
	
		return $uri ;
	}
	
	function getFenxiaoMembers()
	{
//		$fenxiao_members = array(
//				'194379' => 'pinduoduo',
//				'201917' => 'youzan',
//				'197586' => 'renrendian',
//				'207523' => 'oldhango',
//				'223221' => 'fanli',
//				'223222' => 'zhe800',
//				'223223' => 'gegejia',
//				'223224' => 'mengdian',
//				'223268' => 'taobaofx',
//				'223921' => 'juanpi',
//				'225846' => 'xiaomaolv',
//				'225909' => 'lvjingnongchang',
//				'226348' => 'chuchujie',
//				'226476' => 'hanguiren',
//				'226692' => 'chuchujiephs',
//				'226699' => 'xunshizheshuo',
//				'227579' => 'hangofx',
//				'228377' => 'yuanyenongye',
//				'228378' => 'wutongmao',
//				'232174' => 'hzwd',
//                '233280' => 'beibeiwang',
//                '233577' => 'grsc',
//		        '234114' => 'meiguo',
//		        '235365' => 'pindaojia',
//		        '235420' => 'suningyigou',
//		        '235568' => 'maidouguoyuan',
//		        '236823' => 'renrenyoupin',
//		        '237018' => 'hqyx'
//		);

		$member_fenxiao = Model('member_fenxiao')->getMemberFenxiao();
		$member_fenxiao_out = array();
		foreach($member_fenxiao as $v){
			$member_fenxiao_out[$v['member_id']] = $v['member_en_code'];
		}
		return $member_fenxiao_out ;
	}
	
	//获取新老平台在时间段内已保存的订单号
	function getSavedOrderno( $params=array() )
	{
		//新平台保存的拼多多订单
		$model_fenxiao = TModel("B2cOrderFenxiao") ;
		$condition['sourceid'] = $params['member_id'];
		$condition['order_time'] = array('between', array($params['begin'], $params['end']) );
		$result = $model_fenxiao -> where ( $condition ) -> select () ;
		$saved_pdd = $result ? array_column($result, 'orderno') : array();
		//老平台保存的拼多多订单
		$old_model_fenxiao = ecModel("B2cOrderFenxiao") ;
		$result = $old_model_fenxiao -> where ( $condition ) -> select () ;
		$old_saved_pdd = $result ? array_column($result, 'orderno') : array();
		
		return array_unique( array_merge( $saved_pdd, $old_saved_pdd ) );
	}
	
	function createRefund( $data )
	{
		//$this -> _oldPlatformRefund( $data['old'] ) ;
		$this -> _newPlatformRefund( $data['new'] ) ;
	}
	
	function _newPlatformRefund( $refundData )
	{
		if( empty( $refundData ) ) return ;
		$index = 1;
		/** @var RefundService $refundService */
		$refundService = Service("Refund") ;
		$refundModel = Model('refund_return') ;
		foreach ( $refundData as $row )
		{
			if( !$refundService -> check_order_refund( $row['ordersn'], $row, $message ) ) {
				$this -> objCron -> _error( $row['ordersn'], $message ) ;
                v($message,0);
				continue ;
            }v($row,0);
            //退款方式，统一为fenxiao
            $row['refund_way'] = 'fenxiao';
			$params = json_decode( json_encode($row) ) ;
			$params->operation_type=2;
        	$res = $refundModel->addApiRefund( $params );v($res,0);
			if( $res['errorno'] != '1000' ){
				$this -> objCron -> _error( $row['ordersn'], $res['msg'] ) ;
			}
			$index++;
			if( $index == 11 ){
				sleep(5);
				$index = 1;
			}
		}
	}

	/**
	 * 根据最新退款状态，作出取消退款或完成退款操作
	 * @params item_id 汉购网退款记录ID， refund_status 退款状态，update_time 拼多多接口返回的退款最新修改时间, is_ship 是否已发货
	 */
	function doByNewRefundStatus( $params )
	{
		$refundData = array(
				'refund_id' => $params['refund_id'] ,
				'op_id' => $params['op_id'] ,
				'op_name' => $params['op_name']
		) ;
	
		$refundService = Service("Refund") ;
		$msg = "" ;
		switch( $params['refund_status'] )
		{
			case '5':
				//已发货订单，撤销(拒绝)退款，等待2小时以上，防止为了修改退款金额再次发起退款申请。未发货撤销退款就做相关处理
				if( !$params['is_ship'] || ($params['is_ship'] && time() - $params['update_time'] > 2*3600) )
				{
					$params['seller_state'] = '3';
					$res = $refundService -> edit_refund($params, $msg) ;
					if( !$res ) {
						log::selflog("退款单({$params['refund_id']})：".$msg, $params['source']) ;
					}
				}
				break;
	
			case '3':
				$res = $refundService -> confirm_refund($params, $msg) ;
				if( !$res ) {
					log::selflog("退款单({$params['refund_id']})：".$msg, $params['source']) ;
				}
				break;
	
			default:
				break;
		}
	}
	
	function refundurl($data)
	{
		$params = array(
				'orderno' => $data['orderno'],
				'order_id' => strval($data['order_id']), //订单号，请用引号引起来
				'order_money' => '', // 订单金额，没有可不填
				'product_id' => $data['product_id'], // 货品id，（汉购网平台的货品ID）
				'product_name' => urlencode($data['product_name']), // 货品名字，随便填写，能认得出来就行
				'product_money' => $data['product_price'], // 产品金额，可不填，一般与订单金额一致
				'product_num' => '', // 退款产品数量 可不填
				'refund_money' => $data['refund_money'], // 退款金额
				'refund_way' => 'fenxiao', // 退款方式，分销渠道退款一般为offline
				'admin_acount' => $data['source'], // 操作退款管理员账号名，一般为分销商用户名
				'payee_id' => $data['sourceid'], // 被退款用户会员id，为分销商会员id
				'payee_account' => $data['source'],
				// 被退款用户会员账号，一般不用填写，如要填写则为退款渠道的账户名，如用户建设银行账号id
				'payee_name' => $data['source'],
				// 退款用户姓名，一般为真是退款用户的姓名，如产品是寄送给王小贱的，则这里填写王小贱
				'refund_type' => $data['reason'], // 退款类型（为固定的几种类型）
				'refund_comment' => $data['reason'], // 退款描述，随便写啦
				'refund_status' => $data['refund_status'] ? $data['refund_status'] : 1,
				't_begin' => time() // 用户申请退款时间
		);
		$uri = C('old_platform') . "/index.php/api?method=b2c.member.refundFx&sign=eff90f9f07d591ac969dfc4750674ce2&";
		$uri .= http_build_query($params);
		
		return $uri ;
	}

	
	//新平台根据分销订单号和商品ID获取店铺订单号
	function _getFxorderSn($orderno, $goods_id = 0)
	{
		$model = TModel("B2cOrderFenxiao") ;
		$pay_sn = $model -> where ( "orderno='{$orderno}'" ) -> getField( 'pay_sn' ) ;
		if( !$pay_sn ) return false ;
		$orders = TModel("Orders") -> where ( "pay_sn = {$pay_sn}" ) -> select() ;
		if( !$orders ) return false ;
		$order_ids = array_column($orders, 'order_id');
		$oid_rels = array_column($orders, 'order_sn', 'order_id');
		$condition['order_id'] = array('in', $order_ids) ;
		if($goods_id != 0){
			$condition['goods_id'] = intval($goods_id) ;
		}
		$order_id = TModel("OrderGoods") -> where( $condition ) -> getField( 'order_id' ) ;
		if( !$order_id ) return false ;
		return $oid_rels[ $order_id ] ;
	}
	//适合于拼多多这种只有一个商品的分销单
	function getOrdersnByPaysn( $pay_sn )
	{
		if( !$pay_sn ) return false ;
		$model = TModel("orders") ;
		$order_sn = $model -> where ( "pay_sn={$pay_sn}" ) -> getField( 'order_sn' ) ;
		return $order_sn ;
	}
	//根据API返回订单号获取已保存订单号
	function getSavedidByApiorderno( $order_nos = array() )
	{
		if( empty($order_nos) ) return array();
		
		$condition['orderno'] = array('in', $order_nos) ;
		$condition['pay_sn'] = array('gt', 0) ;
		$resultNew = TModel("B2cOrderFenxiao")->where($condition)->select() ;
		$newArr = $resultNew ? array_column($resultNew, 'orderno') : array() ;
		
		$condition['orderno'] = array('in', $order_nos) ;
		$condition['order_id'] = array('gt', 0) ;
		$resultOld = ecModel("B2cOrderFenxiao")->where($condition)->select() ;
		$oldArr = $resultOld ? array_column($resultOld, 'orderno') : array() ;
		return array_unique( array_merge( $newArr, $oldArr ) ) ;
	}
	//新平台根据order_sn获取订单商品
	function getOrderGoodsByosn($order_sn)
	{
		$where ['order_sn'] = $order_sn ;
		$result = TModel('orders') -> where ( $where ) -> find () ;
		if( !$result ) return array() ;
		
		$order_id = $result['order_id'] ;
		$goodsWhere ['order_id'] = $order_id ;
		$result = TModel('order_goods') -> where ( $goodsWhere ) -> select () ;
		
		return $result ? $result : array() ;
	}

	public function getObjCron()
    {
        return $this->objCron;
    }
}
