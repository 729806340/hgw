<?php
/**
 * 退款退货
 *
 *
 *
 * * @汉购网 (c) 2015-2018 ShopWWI Inc. (http://www.hangowa.com)
 * 
 * @license http://www.shopwwi.c om
 * @link 交流群号：
 * @since 汉购网提供技术支持 授权请购买shopnc授权
 */
defined ( 'ByShopWWI' ) or exit ( 'Access Invalid!' );
class refund_returnModel extends Model {

    private $refund_state  = array(
        1=>'处理中',
        2=>'待管理员审核',
        3=>'已完成',
    );

    private $refund_type = array(
        1=>'退款',
        2=>'退款退货',
    );

    private $seller_state = array(
        1=>'待审',
        2=>'同意',
        3=>'不同意',
    );

	/**
	 * 取得退单数量
	 *
	 * @param unknown $condition        	
	 */
	public function getRefundReturn($condition) {
		return $this->table ( 'refund_return' )->where ( $condition )->count ();
	}
	
	/**
	 * 增加退款退货
	 *
	 * @param        	
	 *
	 * @return int
	 */
	public function addRefundReturn($refund_array, $order = array(), $goods = array()) {
		if (! empty ( $order ) && is_array ( $order )) {
			$refund_array ['order_id'] = $order ['order_id'];
			$refund_array ['order_sn'] = $order ['order_sn'];
			$refund_array ['store_id'] = $order ['store_id'];
			$refund_array ['store_name'] = $order ['store_name'];
			$refund_array ['buyer_id'] = $order ['buyer_id'];
			$refund_array ['buyer_name'] = $order ['buyer_name'];
			$refund_array ['refund_way'] = $order['payment_code'];
			$refund_array ['shequ_tuan_id'] = $order['shequ_tuan_id'];
			$refund_array ['shequ_tz_id'] = $order['shequ_tz_id'];
		}
		if (! empty ( $goods ) && is_array ( $goods )) {
		    /** @var goodsModel $goodsModel */
		    $goodsModel = Model('goods');
            $goodsInfo = $goodsModel->getGoodsInfo(array('goods_id'=>$goods['goods_id']));
			$refund_array ['goods_id'] = $goods ['goods_id'];
			$refund_array ['order_goods_id'] = $goods ['rec_id'];
			$refund_array ['order_goods_type'] = $goods ['goods_type'];
			$refund_array ['goods_name'] = $goods ['goods_name'];
			$refund_array ['cost_rate'] = ($goodsInfo['goods_cost']/$goods['goods_pay_price'])*100;
			$refund_array ['commis_rate'] = $goods ['commis_rate'];
			$refund_array ['goods_image'] = $goods ['goods_image'];
		}
		$refund_array ['refund_sn'] = $this->getRefundsn ( $refund_array ['store_id'] );

		/*过滤9月1号人人优品，苏宁易购的15店铺的订单*/
		if(time()>strtotime('2017-09-01')){
			$condition['member_id']=$refund_array['buyer_id'];
			$condition['filter_store_id']=array(array('gt',0),array('eq',$refund_array['store_id']));
			$count=$this->table('member_fenxiao')->where($condition)->count();
			if(!empty($count) && $count>0){
				$refund_array['filter_status']=1;
			}
		}
		$checked=$this->table ( 'refund_return' )->where(array('order_sn'=>$order['order_sn'],'refund_state'=>array('lt',3)))->find();
		$checked_2=$this->table('refund_return')->where(array('order_sn'=>$order['order_sn'],'refund_state'=>3,'seller_state'=>2))->find();
		//避免重复提交申请
		//if(count($checked)>=1||!empty($checked_2)){
		  //  return array('errorno' => 1001,'msg' => '重复维权');
		//}
		if(!empty($checked) || !empty($checked_2)){
			if($checked['goods_id']==$refund_array['goods_id'] || $checked_2['goods_id']==$refund_array['goods_id']){
				return array('errorno' => 1001,'msg' => '重复维权');
			}
		}


		$refund_id = $this->table ( 'refund_return' )->insert ( $refund_array );
		
		// 发送商家提醒
		$param = array ();
		if (intval ( $refund_array ['refund_type'] ) == 1) { // 退款
			$param ['code'] = 'refund';
		} else { // 退货
			$param ['code'] = 'return';
		}
		$param ['store_id'] = $order ['store_id'];
		$type = $refund_array ['order_lock'] == 2 ? '售前' : '售后';
		$param ['param'] = array ('type' => $type,'refund_sn' => $refund_array ['refund_sn'] );
		QueueClient::push ( 'sendStoreMsg', $param );
		return $refund_id;
	}
	
	/**
	 * 订单锁定
	 *
	 * @param        	
	 *
	 * @return bool
	 */
	public function editOrderLock($order_id) {
		$order_id = intval ( $order_id );
		if ($order_id > 0) {
			$condition = array ();
			$condition ['order_id'] = $order_id;
			$data = array ();
			$data ['lock_state'] = array ('exp','lock_state+1' );
            $model_order = Model ( 'order' );
			$result = $model_order->editOrder ( $data, $condition );
			return $result;
		}
		return false;
	}
	
	/**
	 * 订单解锁
	 *
	 * @param        	
	 *
	 * @return bool
	 */
	public function editOrderUnlock($order_id) {
		$order_id = intval ( $order_id );
		if ($order_id > 0) {
			$condition = array ();
			$condition ['order_id'] = $order_id;
			$condition ['lock_state'] = array ('egt','1' );
			$data = array ();
			$data ['lock_state'] = array ('exp','lock_state-1' );
			$data ['delay_time'] = time ();
			$model_order = Model ( 'order' );
			$result = $model_order->editOrder ( $data, $condition );
			return $result;
		}
		return false;
	}
	
	/**
	 * 修改记录
	 *
	 * @param        	
	 *
	 * @return bool
	 */
	public function editRefundReturn($condition, $data) {
		if (empty ( $condition )) {
			return false;
		}
		if (is_array ( $data )) {
			$result = $this->table ( 'refund_return' )->where ( $condition )->update ( $data );
			return $result;
		} else {
			return false;
		}
	}
	
	/**
	 * 平台确认退款处理
	 *
	 * @param
	 *        	$refund
	 * @param string $user
	 *        	操作人
	 * @return bool
	 */
	public function editOrderRefund($refund, $user = '') {
		$refund_id = intval ( $refund ['refund_id'] );
		if ($refund_id > 0) {
			Language::read ( 'model_lang_index' );
			$order_id = $refund ['order_id']; // 订单编号
			$field = 'order_id,buyer_id,buyer_name,store_id,order_sn,order_amount,payment_code,order_state,refund_amount,rcb_amount,pd_amount,rpt_amount,shipping_fee,order_from';
			$model_order = Model ( 'order' );
			$order = $model_order->getOrderInfo ( array ('order_id' => $order_id ), array (), $field );
			
			if( $order ['order_amount'] < $refund['refund_amount'] + $order['refund_amount'] ) {
			    /** @var refund_returnModel $model_refund */
                /*$model_refund = Model('refund_return');
                return $model_refund->editRefundReturn(array('refund_id'=>$refund['refund_id']),array('refund_amount'=>$order ['order_amount']-$order['refund_amount']));//*/
                return false ;
			}
			/** @var predepositModel $model_predeposit */
			$model_predeposit = Model ( 'predeposit' );
			try {
				$this->beginTransaction ();
				$state = true;
				$order_amount = $order ['order_amount']; // 订单金额
				$rcb_amount = $order ['rcb_amount']; // 充值卡支付金额
				$predeposit_amount = $order_amount - $order ['refund_amount'] - $rcb_amount; // 可退预存款金额
				$detail_array = array ();
				
				if( in_array($refund['refund_way'] ,array('predeposit','wxpay', 'wx_jsapi', 'wx_saoma','alipay'))) {
					if (($rcb_amount > 0) && ($refund ['refund_amount'] > $predeposit_amount)) { // 退充值卡
						$log_array = array ();
						$log_array ['member_id'] = $order ['buyer_id'];
						$log_array ['member_name'] = $order ['buyer_name'];
						$log_array ['order_sn'] = $order ['order_sn'];
						$log_array ['amount'] = $refund ['refund_amount'];
						if ($predeposit_amount > 0) {
							$log_array ['amount'] = $refund ['refund_amount'] - $predeposit_amount;
						}
						$detail_array ['rcb_amount'] = $log_array ['amount'];
						$state = $model_predeposit->changeRcb ( 'refund', $log_array ); // 增加买家可用充值卡金额
					}
					if ($predeposit_amount > 0) { // 退预存款
						$log_array = array ();
						$log_array ['member_id'] = $order ['buyer_id'];
						$log_array ['member_name'] = $order ['buyer_name'];
						$log_array ['order_sn'] = $order ['order_sn'];
						$log_array ['amount'] = $refund ['refund_amount']; // 退预存款金额
						if ($refund ['refund_amount'] > $predeposit_amount) {
							$log_array ['amount'] = $predeposit_amount;
						}
						$pay_amount = floatval ( $refund ['pay_amount'] ); // 已完成在线退款金额
						if ($pay_amount > 0) {
							$log_array ['amount'] -= $pay_amount;
						}
						if ($log_array ['amount'] > 0) {
							$detail_array ['pd_amount'] = $log_array ['amount'];
							$state = $model_predeposit->changePd ( 'refund', $log_array ); // 增加买家可用预存款金额
						}
					}
				}
				
				$order_state = $order ['order_state'];
				$model_trade = Model ( 'trade' );
				$order_paid = $model_trade->getOrderState ( 'order_paid' ); // 订单状态20:已付款
				$order_prepare = $model_trade->getOrderState ( 'order_prepare' ); // 订单状态20:已付款
				$order_tuan_paid = $model_trade->getOrderState ( 'order_tuan_paid' ); //订单状态15:拼团组团中
				if ($state && ($order_state == $order_paid || $order_state == $order_prepare || $order_state == $order_tuan_paid)) {
					Logic ( 'order' )->changeOrderStateCancel ( $order, 'system', $user, '商品全部退款完成取消订单', false );
				}
				if ($state) {
					$detail_array ['refund_state'] = '2';
					$this->editDetail ( array ('refund_id' => $refund_id ), $detail_array ); // 更新退款详细
					$order_array = array ();
					$order_amount = $order ['order_amount']; // 订单金额
					$shipping_fee = $order ['shipping_fee']; // 订单运费
					$refund_amount = $order ['refund_amount'] + $refund ['refund_amount']; // 退款金额
					$order_array ['refund_state'] = ($order_amount - $shipping_fee - $refund_amount) > 0 ? 1 : 2;
					$order_array ['refund_amount'] = ncPriceFormat ( $refund_amount );
					$order_array ['delay_time'] = time ();
					$state = $model_order->editOrder ( $order_array, array ('order_id' => $order_id ) ); // 更新订单退款
					if ($state && $order_array ['refund_state'] == 2) { // 全部退款完成时更新红包值（结算使用）
						$refund_array = array ();
						$refund_array ['rpt_amount'] = $order ['rpt_amount'];
						$refund_array ['rpt_bill'] = $order ['rpt_bill'];
						$this->editRefundReturn ( array ('refund_id' => $refund_id ), $refund_array );
					}


					$order_shipped = $model_trade->getOrderState ( 'order_shipped' ); // 订单状态30:已发货
					$order_refund_amount = $order_amount - $shipping_fee; // 订单可退款总金额
					if ($state && $order_state == $order_shipped && $refund_amount >= $order_refund_amount) {
						$order_array = array ();
						$order_array ['order_id'] = $order_id;
						$order_array ['store_id'] = $order['store_id'];
						$order_array ['buyer_id'] = 0; // 参数为0时不加积分和经验值
						$order_array ['order_amount'] = 0;
						Logic ( 'order' )->changeOrderStateReceive ( $order_array, 'system', $user, '商品全部退款，系统完成订单' );
					}
				}
				if ($state && $refund ['order_lock'] == '2') {
					$state = $this->editOrderUnlock ( $order_id ); // 订单解锁
				}
				if ($state) {
					$this->commit ();
					if( $order['order_from'] != 3 ){
						/** @var CpsService $service */
		                $service = Service('Cps');
		                $service->refundOrder($refund_id);
					}
					
				} else {
					$this->rollback();
				}
				
				return $state;
			} catch ( Exception $e ) {
				$this->rollback ();
				return false;
			}
		}
		return false;
	}
	
	/**
	 * 增加退款详细
	 *
	 * @param        	
	 *
	 * @return int
	 */
	public function addDetail($refund, $order) {
		$detail_array = array ();
		$detail_array ['refund_id'] = $refund ['refund_id'];
		$detail_array ['order_id'] = $refund ['order_id'];
		$detail_array ['batch_no'] = date ( 'YmdHis' ) . $refund ['refund_id']; // 批次号。支付宝要求格式为：当天退款日期+流水号。
		$detail_array ['refund_amount'] = ncPriceFormat ( $refund ['refund_amount'] );
		$detail_array ['refund_code'] = $refund['refund_way'];
		$detail_array ['refund_state'] = '1';
		$detail_array ['add_time'] = time ();
		if (! empty ( $order ['trade_no'] ) && in_array ( $order ['payment_code'], array ('wxpay','wx_jsapi','wx_saoma' ) )) { // 微信支付
            $detail_array ['refund_code'] = $order ['payment_code'];
            /*$api_file = BASE_PATH . DS . 'api' . DS . 'refund' . DS . 'wxpay' . DS . 'WxPay.Config.php';
			if ($order ['payment_code'] == 'wxpay') {
				$api_file = BASE_PATH . DS . 'api' . DS . 'refund' . DS . 'wxpay' . DS . 'WxPayApp.Config.php';
			}
			include $api_file;
			$apiclient_cert = WxPayConfig::SSLCERT_PATH;
			$apiclient_key = WxPayConfig::SSLKEY_PATH;
			if (! empty ( $apiclient_cert ) && ! empty ( $apiclient_key )) { // 验证商户证书路径设置
				$detail_array ['refund_code'] = $order ['payment_code'];
			}*/
		}
		if (! empty ( $order ['trade_no'] ) && $order ['payment_code'] == 'alipay') { // 支付宝
			$detail_array ['refund_code'] = 'alipay';
		}
		$result = $this->table ( 'refund_detail' )->insert ( $detail_array );
		return $result;
	}
	
	/**
	 * 增加退款退货原因
	 *
	 * @param        	
	 *
	 * @return int
	 */
	public function addReason($reason_array) {
		$reason_id = $this->table ( 'refund_reason' )->insert ( $reason_array );
		return $reason_id;
	}
	
	/**
	 * 修改退款详细记录
	 *
	 * @param        	
	 *
	 * @return bool
	 */
	public function editDetail($condition, $data) {
		if (empty ( $condition )) {
			return false;
		}
		if (is_array ( $data )) {
			$result = $this->table ( 'refund_detail' )->where ( $condition )->update ( $data );
			return $result;
		} else {
			return false;
		}
	}
	
	/**
	 * 修改退款退货原因记录
	 *
	 * @param        	
	 *
	 * @return bool
	 */
	public function editReason($condition, $data) {
		if (empty ( $condition )) {
			return false;
		}
		if (is_array ( $data )) {
			$result = $this->table ( 'refund_reason' )->where ( $condition )->update ( $data );
			return $result;
		} else {
			return false;
		}
	}
	
	/**
	 * 删除退款退货原因记录
	 *
	 * @param        	
	 *
	 * @return bool
	 */
	public function delReason($condition) {
		if (empty ( $condition )) {
			return false;
		} else {
			$result = $this->table ( 'refund_reason' )->where ( $condition )->delete ();
			return $result;
		}
	}
	
	/**
	 * 退款退货原因记录
	 *
	 * @param        	
	 *
	 * @return array
	 */
	public function getReasonList($condition = array(), $page = '', $limit = '', $fields = '*') {
		$result = $this->table ( 'refund_reason' )->field ( $fields )->where ( $condition )->page ( $page )->limit ( $limit )->order ( 'sort asc,reason_id desc' )->key ( 'reason_id' )->select ();
		return $result;
	}

	/**
	 * 退款退货商家原因记录
	 *
	 * @param
	 *
	 * @return array
	 */
	public function getSellerReasonList($condition = array(), $page = '', $limit = '', $fields = '*') {
		$result = $this->table ( 'refund_reason_seller' )->field ( $fields )->where ( $condition )->page ( $page )->limit ( $limit )->order ( 'sort asc,reason_id desc' )->key ( 'reason_id' )->select ();
		return $result;
	}
	
	/**
	 * 取退款退货记录
	 *
	 * @param        	
	 *
	 * @return array
	 */
	public function getRefundReturnList($condition = array(), $page = '', $fields = '*', $limit = '', $order = 'refund_id desc',$master=false) {
		$result = $this->table ( 'refund_return' )->field ( $fields )->where ( $condition )->page ( $page )->limit ( $limit )->order ( $order )->master($master)->select ();
		return $this->DealFxReList ( $result );
		// return $result;
	}

	public function getRefundGroupBusiness($condition = array(),$fields='*',$group='',$order = 'refund_id desc',$limit=1000) {
		$result = $this->table ( 'refund_return' )->field ( $fields )->where ( $condition )->group($group)->order($order)->limit($limit)->select ();
		 return $result;
	}
	public function getRefundReturnList2($condition = array()) {
		$result = $this->table ( 'refund_return' )->where ( $condition )->group('order_id')->order('refund_id desc')->select ();
		return $this->DealFxReList ( $result );
		// return $result;
	}

    //获取退货列表
    public function getRefundList2($conditions, $page = 10, $limit = '')
    {
        $result = $this->where($conditions)
            ->field('refund_id, refund_type, refund_state, seller_state, order_sn, refund_amount, add_time, seller_time')
            ->limit($limit)->order('add_time desc')
            ->page($page)->select();
        $overRefundIds = $overOrderIds = array();
        foreach ($result as $k => $v) {
            if($v['refund_state'] == 3){
                $overRefundIds[] = $v['refund_id'];
            }
            $overOrderIds[] = $v['order_sn'];
        }
        if(count($overRefundIds)>0){
            $array = array('refund_id'=>array('in' , $overRefundIds));
            $res = Model('refund_detail')->field('refund_id,refund_amount,pay_time')->where($array)->select();
            if(count($res)>0){
                $refundArr = array();
                foreach($res as $k=>$v){
                    $refundArr[$v['refund_id']] = $v;
                }
            }
        }
        if(count($overOrderIds)>0){
            $array1 = array('order_sn'=>array('in' , $overOrderIds));
            $rs = Model('orders')->field('order_sn , order_amount')->where($array1)->select();
            if(count($rs)>0){
                $orderArr = array();
                foreach($rs as $k=>$v){
                    $orderArr[$v['order_sn']] = $v['order_amount'];
                }
            }
        }

        foreach ($result as $k => $v) {
            $result[$k]['refund_type'] = $this->refund_type[$v['refund_type']];
            $result[$k]['refund_state'] = $this->refund_state[$v['refund_state']];
            $result[$k]['seller_state'] = $this->seller_state[$v['seller_state']];
            $result[$k]['refund_amout'] = $refundArr[$v['refund_id']]['refund_amount'];
            //$result[$k]['pay_time'] = $refundArr[$v['refund_id']]['pay_time']?date('Y-m-d H:i:s' , $refundArr[$v['refund_id']]['pay_time']):'';
            $result[$k]['pay_time'] = $refundArr[$v['refund_id']]['pay_time']?date('Y-m-d H:i:s' , $refundArr[$v['refund_id']]['pay_time']):'';
            $result[$k]['add_time'] = date('Y-m-d H:i:s' , $v['add_time']);
            $result[$k]['seller_time'] = date('Y-m-d H:i:s' , $v['seller_time']);
            $result[$k]['order_money'] = $orderArr[$v['order_sn']];
        }
        return $result;
    }

	public function getOrderRefundReturnList($condition = array(), $page = '', $fields = '*', $limit = 15, $order = 'refund_id desc',$master=false) {
	    $condition ['refund_type'] = '1'; // 类型:1为退款,2为退货
        return $this->table('refund_return,orders')->field($fields)->join('left')->on("refund_return.order_id = orders.order_id")->where($condition)->page($page)->limit($limit)->order($order)->master($master)->select();
	}


	/**
	 * 取退款记录
	 *
	 * @param        	
	 *
	 * @return array
	 */
	public function getRefundList($condition = array(), $page = '', $order = 'refund_id desc', $limit = '',$master=false) {
		$condition ['refund_type'] = '1'; // 类型:1为退款,2为退货
		$result = $this->getRefundReturnList ( $condition, $page, '*', $limit, $order,$master );
		return $result;
	}
	
	/**
	 * 处理分销退货退款显示问题
	 *
	 * @param array $result        	
	 * @return array
	 */
	private function DealFxReList($result) {
		$fenxiao_service = Service ( "Fenxiao" );
		$fx_members = $fenxiao_service->getFenxiaoMembers ();
		$refund_reason=Model('refund_reason');
		/** @var expressModel $expressModel */
		$expressModel=Model('express');
		$expresses = $expressModel->limit(false)->select();
		$expresses = array_under_reset($expresses,'id');
		/** @var orderModel $orderModel */
		$orderModel = Model('order');
		$orderIds = array_column($result,'order_id');
		$orderList = $orderModel->getOrderList(array('order_id'=>array('in',$orderIds)));
		$orders = array_under_reset($orderList,'order_id');
        $model_order = Model('order');
        $reasonIds = array_unique(array_column($result,'reason_id'));
        $is_aftersales=$refund_reason->where(array('reason_id'=>array('in',$reasonIds)))->limit(false)->select();
        $is_aftersales = array_under_reset($is_aftersales,'reason_id');
        $order_common_list = $model_order->getOrderCommonList(array('order_id'=>array('in',$orderIds)),'*','',999999);
        $order_common_list = array_under_reset($order_common_list,'order_id');
        foreach ( $result as &$item ) {
		    $order = $orders[$item ['order_id']];
			array_key_exists ( $item ['buyer_id'], $fx_members ) and $item ['buyer_name'] = '分销渠道';
			$item ['is_operate'] = 1; // 是否为可操作的维权
			$item['is_aftersale']=empty($is_aftersales[$item['reason_id']]['is_aftersale'])?"售前":"售后";
			$condition = array();
			$condition[] = $item ['order_id'];
			$orderCommon= $order_common_list[$item['order_id']];
			$item ['order_ship_time'] = $orderCommon['shipping_time'];
			if ($item ['seller_state'] != 1) {
				$item ['is_operate'] = 0;
				// 售前的分销退款不处理
            }
            $item['order_create_time'] = $order['add_time'];
            $item['express_name'] = $order['shipping_code']&&isset($expresses[$orderCommon['shipping_express_id']])?$expresses[$orderCommon['shipping_express_id']]['e_name']:'';
            $item['express_id'] = $orderCommon['shipping_express_id'];
            $item['shipping_code'] = str_replace(',','，',$order['shipping_code']);
		}
		return $result;
	}
	
	/**
	 * 取退货记录
	 *
	 * @param        	
	 *
	 * @return array
	 */
	public function getReturnList($condition = array(), $page = '', $order = 'refund_id desc', $limit = '') {
	$condition ['refund_type'] = '2'; // 类型:1为退款,2为退货
	$result = $this->getRefundReturnList ( $condition, $page, '*', $limit, $order );
	return $result;
}

	public function getPendtreatList($condition = array(), $page = '', $order = 'refund_id desc', $limit = '') {
		$condition ['refund_type'] = '3'; // 类型:1为退款,2为退货，3退货退款待处理
		$result = $this->getRefundReturnList ( $condition, $page, '*', $limit, $order );
		return $result;
	}
	
	/**
	 * 退款退货申请编号
	 *
	 * @param        	
	 *
	 * @return array
	 */
	public function getRefundsn($store_id) {
		$result = mt_rand ( 100, 999 ) . substr ( 100 + $store_id, - 3 ) . date ( 'ymdHis' );
		return $result;
	}
	
	/**
	 * 退款详细记录
	 *
	 * @param        	
	 *
	 * @return array
	 */
	public function getDetailInfo($condition = array(), $fields = '*') {
		return $this->table ( 'refund_detail' )->where ( $condition )->field ( $fields )->find ();
	}
	
	/**
	 * 订单在线退款计算
	 *
	 * @param        	
	 *
	 * @return array
	 */
	public function getPayDetailInfo($detail_array) {
		$condition = array ();
		$condition ['order_id'] = $detail_array ['order_id'];
		/** @var orderModel $model_order */
		$model_order = Model ( 'order' );
		$order = $model_order->getOrderInfo ( $condition ); // 订单详细
		$order ['pay_amount'] = ncPriceFormat ( $order ['order_amount'] - $order ['rcb_amount'] - $order ['pd_amount'] ); // 在线支付金额=订单总价格-充值卡支付金额-预存款支付金额

        $orders = $model_order->getOrderList(array('pay_sn'=>$order['pay_sn']));
        if(is_array($orders)&&count($orders)>1){
            $order['total_pay_amount'] = 0;
            foreach ($orders as $v){
                $order['total_pay_amount'] += $v['order_amount'] - $v['rcb_amount'] - $v['pd_amount'];
            }
        }else{
            $order['total_pay_amount'] = $order['pay_amount'];
        }
		$out_amount = $order ['pay_amount'] - $order ['refund_amount']; // 可在线退款金额
		
		$refund_amount = $detail_array ['refund_amount']; // 本次退款总金额
		if ($refund_amount > $out_amount) {
			$refund_amount = $out_amount;
		}
		$order ['pay_refund_amount'] = ncPriceFormat ( $refund_amount );
		$condition = array ();
		$payment_config = array ();
		$condition ['payment_code'] = $order ['payment_code'];
        $model_payment = Model ( 'mb_payment' );

		if (in_array ( $order ['payment_code'], array ('wxpay','wx_jsapi' ) )) { // 手机客户端微信支付
			if ($order ['payment_code'] == 'wx_jsapi') {
				$condition ['payment_code'] = 'wxpay_jsapi';
			}
			$payment_info = $model_payment->getMbPaymentInfo ( $condition ); // 接口参数
			$payment_info = $payment_info ['payment_config'];
			if ($order ['payment_code'] == 'wxpay') {
				$payment_config ['appid'] = $payment_info ['wxpay_appid'];
				$payment_config ['mchid'] = $payment_info ['wxpay_partnerid'];
				$payment_config ['key'] = $payment_info ['wxpay_partnerkey'];
			}
			if ($order ['payment_code'] == 'wx_jsapi') {
				$payment_config ['appid'] = $payment_info ['appId'];
				$payment_config ['mchid'] = $payment_info ['partnerId'];
				$payment_config ['key'] = $payment_info ['apiKey'];
			}
		} else if ($order['payment_code'] == 'alipay') {
            $condition ['payment_code'] = 'alipay';
            $payment_info = $model_payment->getMbPaymentInfo ( $condition ); // 接口参数
            $payment_info = $payment_info ['payment_config'];
            $payment_config ['alipay_appid'] = $payment_info ['alipay_appid'];
        } else {
			if ($order ['payment_code'] == 'wx_saoma') {
				$condition ['payment_code'] = 'wxpay';
			}
			$model_payment = Model ( 'payment' );
			$payment_info = $model_payment->getPaymentInfo ( $condition ); // 接口参数
			$payment_config = unserialize ( $payment_info ['payment_config'] );
		}
		$order ['payment_config'] = $payment_config;
		return $order;
	}
	
	/**
	 * 取一条记录
	 *
	 * @param        	
	 *
	 * @return array
	 */
	public function getRefundReturnInfo($condition = array(), $fields = '*') {
		return $this->table ( 'refund_return' )->where ( $condition )->field ( $fields )->find ();
	}
	
	/**
	 * 根据订单取商品的退款退货状态
	 *
	 * @param        	
	 *
	 * @return array
	 */
	public function getGoodsRefundList($order_list = array(), $order_refund = 0) {
		$order_ids = array (); // 订单编号数组
		$order_ids = array_keys ( $order_list );
		$model_trade = Model ( 'trade' );
		$condition = array ();
		$condition ['order_id'] = array ('in',$order_ids );
		$refund_list = $this->table ( 'refund_return' )->where ( $condition )->order ( 'refund_id desc' )->select ();
		$refund_goods = array (); // 已经提交的退款退货商品
		if (! empty ( $refund_list ) && is_array ( $refund_list )) {
			foreach ( $refund_list as $key => $value ) {
				$order_id = $value ['order_id']; // 订单编号
				$goods_id = $value ['order_goods_id']; // 订单商品表编号
				if (empty ( $refund_goods [$order_id] [$goods_id] )) {
					$refund_goods [$order_id] [$goods_id] = $value;
					if ($order_refund > 0) { // 订单下的退款退货所有记录
						$order_list [$order_id] ['refund_list'] = $refund_goods [$order_id];
					}
				}
			}
		}
		if (! empty ( $order_list ) && is_array ( $order_list )) {
			foreach ( $order_list as $key => $value ) {
				$order_id = $key;
				$goods_list = $value ['extend_order_goods']; // 订单商品
				$order_state = $value ['order_state']; // 订单状态
				$order_paid = $model_trade->getOrderState ( 'order_paid' ); // 订单状态20:已付款
				$payment_code = $value ['payment_code']; // 支付方式
				if ($order_state == $order_paid && $payment_code != 'offline') { // 已付款未发货的非货到付款订单可以申请取消
					$order_list [$order_id] ['refund'] = '1';
				} elseif ($order_state > $order_paid && ! empty ( $goods_list ) && is_array ( $goods_list )) { // 已发货后对商品操作
					$refund = $this->getRefundState( $value ); // 根据订单状态判断是否可以退款退货
					foreach ( $goods_list as $k => $v ) {
						$goods_id = $v ['rec_id']; // 订单商品表编号
						if ($v ['goods_pay_price'] > 0) { // 实际支付额大于0的可以退款
							$v ['refund'] = $refund;
						}
						if (! empty ( $refund_goods [$order_id] [$goods_id] )) {
							$seller_state = $refund_goods [$order_id] [$goods_id] ['seller_state']; // 卖家处理状态:1为待审核,2为同意,3为不同意
							if ($seller_state == 3) {
								$order_list [$order_id] ['extend_complain'] [$goods_id] = '1'; // 不同意可以发起退款投诉
							} else {
								$v ['refund'] = '0'; // 已经存在处理中或同意的商品不能再操作
							}
							$v ['extend_refund'] = $refund_goods [$order_id] [$goods_id];
						}
						$goods_list [$k] = $v;
					}
				}
				$order_list [$order_id] ['extend_order_goods'] = $goods_list;
			}
		}
		return $order_list;
	}
	
	/**
	 * 根据订单判断投诉订单商品是否可退款
	 *
	 * @param        	
	 *
	 * @return array
	 */
	public function getComplainRefundList($order, $order_goods_id = 0) {
		$list = array ();
		$refund_list = array (); // 已退或处理中商品
		$refund_goods = array (); // 可退商品
		if (! empty ( $order ) && is_array ( $order )) {
			$order_id = $order ['order_id'];
			$order_list [$order_id] = $order;
			$order_list = $this->getGoodsRefundList ( $order_list );
			$order = $order_list [$order_id];
			$goods_list = $order ['extend_order_goods'];
			$order_amount = $order ['order_amount']; // 订单金额
			$order_refund_amount = $order ['refund_amount']; // 订单退款金额
			foreach ( $goods_list as $k => $v ) {
				$goods_id = $v ['rec_id']; // 订单商品表编号
				if ($order_goods_id > 0 && $goods_id != $order_goods_id) {
					continue;
				}
				$v ['refund_state'] = 3;
				if (! empty ( $v ['extend_refund'] )) {
					$v ['refund_state'] = $v ['extend_refund'] ['seller_state']; // 卖家处理状态为3,不同意时能退款
				}
				if ($v ['refund_state'] > 2) { // 可退商品
					$goods_pay_price = $v ['goods_pay_price']; // 商品实际成交价
					if ($order_amount < ($goods_pay_price + $order_refund_amount)) {
						$goods_pay_price = $order_amount - $order_refund_amount;
						$v ['goods_pay_price'] = $goods_pay_price;
					}
					$v ['goods_refund'] = $v ['goods_pay_price'];
					$refund_goods [$goods_id] = $v;
				} else { // 已经存在处理中或同意的商品不能再退款
					$refund_list [$goods_id] = $v;
				}
			}
		}
		$list = array ('refund' => $refund_list,'goods' => $refund_goods );
		return $list;
	}
	
	/**
	 * 详细页右侧订单信息
	 *
	 * @param        	
	 *
	 * @return array
	 */
	public function getRightOrderList($order_condition, $order_goods_id = 0) {
		$model_order = Model ( 'order' );
		$order_info = $model_order->getOrderInfo ( $order_condition, array ('order_common','store' ) );
        $info=$order_info;
        $info['order_state']=$this->orderState($order_info);
		Tpl::output ( 'order', $info );
		$order_id = $order_info ['order_id'];
		
		$store = $order_info ['extend_store'];
		Tpl::output ( 'store', $store );
		$order_common = $order_info ['extend_order_common'];
		Tpl::output ( 'order_common', $order_common );
		if ($order_common ['shipping_express_id'] > 0) {
			$express = rkcache ( 'express', true );
			Tpl::output ( 'e_code', $express [$order_common ['shipping_express_id']] ['e_code'] );
			Tpl::output ( 'e_name', $express [$order_common ['shipping_express_id']] ['e_name'] );
		}
		
		$condition = array ();
		$condition ['order_id'] = $order_id;
		if ($order_goods_id > 0) {
			$condition ['rec_id'] = $order_goods_id; // 订单商品表编号
		}
		$goods_list = $model_order->getOrderGoodsList ( $condition );
		Tpl::output ( 'goods_list', $goods_list );
		$order_info ['goods_list'] = $goods_list;
		
		return $order_info;
	}
	public function getOrderByCondition($order_condition,$order_goods_id=0){
        $model_order = Model ( 'order' );
        $order_info = $model_order->getOrderInfo ( $order_condition, array ('order_common','store' ) );
        $condition = array ();
        $condition ['order_id'] = $order_info ['order_id'];
        if ($order_goods_id > 0) {
            $condition ['rec_id'] = $order_goods_id; // 订单商品表编号
        }
        $order_info['goods_list'] = $model_order->getOrderGoodsList ( $condition );
        return $order_info;
    }
    /**
     * 取得订单状态文字输出形式
     *
     * @param array $order_info 订单数组
     * @return string $order_state 描述输出
     */
    function orderState($order_info) {
        switch ($order_info['order_state']) {
            case ORDER_STATE_CANCEL:
                $order_state = "已取消";
                break;
            case ORDER_STATE_NEW:
                $order_state ="未支付";
                break;
            case ORDER_STATE_PREPARE:
                $order_state ="备货中";
                break;
            case ORDER_STATE_PAY:
                $order_state = "已支付";
                break;
            case ORDER_STATE_PART_SEND:
                $order_state = '部分发货';
                break;
            case ORDER_STATE_SEND:
                $order_state = "已发货";
                break;
            case ORDER_STATE_SUCCESS:
                $order_state = "已完成";
                break;
        }
        return $order_state;
    }
	/**
	 * 根据订单状态判断是否可以退款退货
	 *
	 * @param        	
	 *
	 * @return array
	 */
	public function getRefundState($order,$is_user=false) {
		$refund = '0'; // 默认不允许退款退货
		$order_state = $order ['order_state']; // 订单状态
        /** @var tradeModel $model_trade */
		$model_trade = Model ( 'trade' );
		$order_shipped = $model_trade->getOrderState ( 'order_shipped' ); // 30:已发货
		$order_completed = $model_trade->getOrderState ( 'order_completed' ); // 40:已收货
		switch ($order_state) {
			case $order_shipped :
				$payment_code = $order ['payment_code']; // 支付方式
				if ($payment_code != 'offline') { // 货到付款订单在没确认收货前不能退款退货
					$refund = '1';
				}
				break;
			case $order_completed :
				$order_refund = $model_trade->getMaxDay ( $is_user?'order_refund':'order_refund_1' ); // 15:收货完成后可以申请退款退货
				if ($order ['delay_time'] < $order ['finnshed_time']) {
					$order ['delay_time'] = $order ['finnshed_time'];
				}
				$delay_time = $order ['delay_time'] + 60 * 60 * 24 * $order_refund;
				if ($delay_time > time ()) {
					$refund = '1';
				}
				break;
			default :
				$refund = '0';
				break;
		}
		
		return $refund;
	}
	
	/**
	 * 向模板页面输出退款退货状态
	 *
	 * @param        	
	 *
	 * @return array
	 */
	public function getRefundStateArray($type = 'all') {
		Language::read ( 'refund' );
		$state_array = array ('1' => Language::get ( 'refund_state_confirm' ),'2' => Language::get ( 'refund_state_yes' ),'3' => Language::get ( 'refund_state_no' ),'4'=>'同意（客服仲裁）' ); // 卖家处理状态:1为待审核,2为同意,3为不同意
		Tpl::output ( 'state_array', $state_array );
		
		$admin_array = array ('1' => '处理中','2' => '待处理','3' => '已完成' ); // 确认状态:1为买家或卖家处理中,2为待平台管理员处理,3为退款退货已完成
		Tpl::output ( 'admin_array', $admin_array );
		
		$state_data = array ('seller' => $state_array,'admin' => $admin_array );
		if ($type == 'all')
			return $state_data; // 返回所有
		return $state_data [$type];
	}
	
	/**
	 * 退货退款数量
	 *
	 * @param array $condition        	
	 * @return int
	 */
	public function getRefundReturnCount($condition) {
		return $this->table ( 'refund_return' )->where ( $condition )->count ();
	}

    public function getRefundReturnAmount($condition)
    {
        return $this->table('refund_return')->where($condition)->sum('refund_amount');
    }
	
	/**
	 * 取得退款数量
	 *
	 * @param unknown $condition        	
	 */
	public function getRefundCount($condition) {
		$condition ['refund_type'] = 1;
		return $this->table ( 'refund_return' )->where ( $condition )->count ();
	}
	
	/**
	 * 取得退款退货数量
	 *
	 * @param unknown $condition        	
	 */
	public function getReturnCount($condition) {
		$condition ['refund_type'] = 2;
		return $this->table ( 'refund_return' )->where ( $condition )->count ();
	}

	/**
	 * 取得退款退货待处理数量
	 *
	 * @param unknown $condition
	 */
	public function getPendtreatCount($condition) {
		$condition ['refund_type'] = 3;
		return $this->table ( 'refund_return' )->where ( $condition )->count ();
	}

	public function setCheck($condition = array(),$check_status){
		$data = array();
		$data['check_status'] = $check_status;
		return $this->table('refund_return')->where($condition)->update($data);
	}
	
	/*
	 * 获得退货退款的店铺列表
	 * @param array $complain_list
	 * @return array
	 */
	public function getRefundStoreList($list) {
		$store_ids = array ();
		if (! empty ( $list ) && is_array ( $list )) {
			foreach ( $list as $key => $value ) {
				$store_ids [] = $value ['store_id']; // 店铺编号
			}
		}
		$field = 'store_id,store_name,member_id,member_name,seller_name,store_company_name,store_qq,store_ww,store_phone,store_domain';
		return Model ( 'store' )->getStoreMemberIDList ( $store_ids, $field );
	}
	public function addApiRefund($param) {
		$model_order = Model ( 'order' );
        /** @var tradeModel $model_trade */
		$model_trade = Model ( 'trade' );
		$refund_array = array ();
		if (! is_numeric ( $param->reason_id ) && empty ( $param->reason_id )) {
			return array ('errorno' => 1001,'msg' => '退款理由编号不能为空' );
		}

		$reason = Model ( 'refund_reason' )->field ( 'reason_info' )->where ( array ('reason_id' => $param->reason_id ) )->find ();
		if (empty ( $reason ['reason_info'] )) {
			return array ('errorno' => 1001,'msg' => '退款理由信息匹配不成功' );
		}
		
		if (! in_array ( $param->refund_type, array (1,2,3) ) || ! in_array ( $param->return_type, array (1,2,3) )) {
			return array ('errorno' => 1001,'msg' => '退款类型或者退货类型不合法' );
		}
		
		if (! in_array ( $param->seller_state, array (1,2,3,4 ) )) {
			return array ('errorno' => 1001,'msg' => '卖家处理状态非法' );
		}
		
		if (empty ( $param->buyer_message )) {
			return array ('errorno' => 1001,'msg' => '用户留言不能为空' );
		}
		
		if (empty ( $param->ordersn )) {
			return array ('errorno' => 1001,'msg' => '订单编号不能为空' );
		}
		
		$condition = array ('order_sn' => $param->ordersn );
		$fields = 'order_id,buyer_id ,order_state,order_from';
		$orders = Model ( 'order' )->getOrderInfo ( $condition, $extend = array (), $fields, $order = '', $group = '' );
		if (empty ( $orders ['order_id'] )) {
			return array ('errorno' => 1001,'msg' => '订单信息不存在' );
		}
		$order_state = $model_trade->getOrderState (); // 订单状态30:已发货
		$order_allow_state = array ($order_state ['order_tuan_paid'],$order_state ['order_paid'],$order_state ['order_prepare'],$order_state ['order_shipped'],$order_state ['order_completed'] );
		// 只有已付款和已发货的订单才能维权
		if (! in_array ( $orders ['order_state'], $order_allow_state )) {
			return array ('errorno' => 1001,'msg' => '目前订单状态无法发起维权' );
		}
        if($orders['order_from']!=3&&$orders ['order_state'] == $order_state ['order_prepare']){
            // 备货中订单后台可以发起退款
            //return array ('errorno' => 1001,'msg' => '非分销订单，在备货中状态无法发起维权' );
        }
            if ($orders ['order_state'] == $order_state ['order_paid']||$orders ['order_state'] == $order_state ['order_tuan_paid']||$orders ['order_state'] == $order_state ['order_prepare']) {
			// 全部退款
			return $this->_add_refundall ( $param, $orders ['order_id'], $orders ['buyer_id'] );
		} else {
			return $this->_add_refund ( $param, $orders ['order_id'], $orders ['buyer_id'], $reason );
		}
	}
	
	// 发货前全额退款
	private function _add_refundall($param, $order_id, $buyer_id) {
		$model_refund = Model ( 'refund_return' );
		$model_trade = Model ( 'trade' );
		$condition = array ();
		$condition ['buyer_id'] = intval ( $buyer_id );
		$condition ['order_id'] = intval ( $order_id );
		$order = $model_refund->getRightOrderList ( $condition );
		
		$lock_amount = Logic ( 'order_book' )->getDepositAmount ( $order );
		$order ['allow_refund_amount'] = $order ['order_amount'] - $lock_amount;
		$order_amount = $order ['allow_refund_amount']; // 订单金额
		
		$condition = array ();
		$condition ['buyer_id'] = $order ['buyer_id'];
		$condition ['order_id'] = $order ['order_id'];
		$condition ['goods_id'] = '0';
		$condition ['seller_state'] = array ('lt','3' );
		$refund_list = $model_refund->getRefundReturnList ( $condition );
		
		$refund = array ();
		if (! empty ( $refund_list ) && is_array ( $refund_list )) {
			$refund = $refund_list [0];
		}
		$order_paid = $model_trade->getOrderState ( 'order_paid' ); // 订单状态20:已付款
		$payment_code = $order ['payment_code']; // 支付方式
		if ( ($refund ['refund_id'] > 0 || $order ['order_state'] < $order_paid || $payment_code == 'offline') &&  $param->admin_info['gid'] != 2) { // 检查订单状态,防止页面刷新不及时造成数据错误
			return array ('errorno' => 1001,'msg' => '维权重复提交' );
		}
		$refund_array = array ();
		$refund_array ['refund_type'] = $param->refund_type;  // 类型:1为退款,2为退货
		$refund_array ['seller_state'] = '1'; // 状态:1为待审核,2为同意,3为不同意
		$refund_array ['order_lock'] = '2'; // 锁定类型:1为不用锁定,2为需要锁定
        $refund_array ['before_ship'] = '1';//发货前退款
        $refund_array ['goods_id'] = '0';
		$refund_array ['order_goods_id'] = '0';
		$refund_array ['reason_id'] = $param->reason_id?$param->reason_id:'0';
		$refund_array ['reason_info'] = $param->reason_info?$param->reason_info:'取消订单，全部退款';
		$refund_array ['goods_name'] = '订单商品全部退款';
		$refund_array ['refund_amount'] = ncPriceFormat ( $order_amount );
		$refund_array ['buyer_message'] = $param->buyer_message;
		$adminInfo = unserialize(decrypt(cookie('sys_key'),MD5_KEY));
		$adminName = isset($adminInfo['name'])?$adminInfo['name']:'';
        $refund_array['admin_name'] = isset($param->admin_info['name']) ? $param->admin_info['name'] : $adminName;
		$refund_array ['refund_way'] = $param->refund_way ? $param->refund_way : 'predeposit';
		$refund_array ['add_time'] = time ();
		$refund_array['operation_type']=$refund_array['admin_name']?1:$param->operation_type;
		if($param->refund_way == 'fenxiao'){
			$refund_array ['add_time'] = $param->create_time ? $param->create_time : time();
			$refund_array ['fenxiao_time'] = time(); //分销退款单导入系统时间
		}
		$refund_array ['pic_info'] = '';
		/*判断是否有图片上传*/
		if($param->pic_info){
			$refund_array['pic_info']=$param->pic_info;
		}
		$state = $this->addRefundReturn ( $refund_array, $order );
		if ($state) {
			$model_refund->editOrderLock ( $order_id );
			return array ('errorno' => 1000,'msg' => '维权插入成功' ,'id'=>$state);
		} else {
			return array ('errorno' => 1001,'msg' => '维权插入失败 refund_array:'.json_encode($refund_array).' order:'.json_encode($order));
		}
	}
	
	// 发货后退款维权
	private function _add_refund($param, $order_id, $buyer_id, $reason = array()) {
	    /** @var refund_returnModel $model_refund */
		$model_refund = Model ( 'refund_return' );
		$model_trade = Model ( 'trade' );
		// 发货后维权
		if (empty ( $param->refund_amount ) || floatval ( $param->refund_amount ) < 0) {
			return array ('errorno' => 1001,'msg' => '退款金额不合法' );
		}
		// if (empty ( $param->goods_id )) {
		// 	return array ('errorno' => 1001,'msg' => '商品不能为空' );
		// }
		$param->goods_num = (empty ( $param->goods_num ) || ! is_numeric ( $param->goods_num )) ? 0 : $param->goods_num;
		
		$condition = array ();
		$condition ['buyer_id'] = intval ( $buyer_id );
		$condition ['order_id'] = intval ( $order_id );
		$goods_id = intval ( $param->goods_id );
		//$order_goods = Model ( 'order' )->getOrderGoodsList ( array ('order_id' => intval ( $order_id ) ), 'rec_id', 999);
		/*if (empty ( $order_goods [0] ['rec_id'] )) {
			return array ('errorno' => 1001,'msg' => '商品ID不匹配' );
		}*/
        $order = $model_refund->getRightOrderList ($condition,0 /*$order_goods[0]['rec_id']*/);
		$order_id = $order ['order_id'];
		$order_amount = $order ['order_amount']; // 订单金额
		$order_refund_amount = $order ['refund_amount']; // 订单退款金额
		$goods_list = $order ['goods_list'];
		// 这里要根据选择的商品来添加
        $goods = $goods_list [0];
        //v($goods_list,0);
        foreach ($goods_list as $item){
            if ($item['goods_id'] == $goods_id) $goods = $item;
        }
        //v($goods);
		$goods_pay_price = $goods ['goods_pay_price']; // 商品实际成交价
		if ($order_amount < ($goods_pay_price + $order_refund_amount)) {
			$goods_pay_price = $order_amount - $order_refund_amount;
			$goods ['goods_pay_price'] = $goods_pay_price;
		}
		$goods_id = $goods ['rec_id'];
		$condition = array ();
		$condition ['buyer_id'] = $order ['buyer_id'];
		$condition ['order_id'] = $order ['order_id'];
		// $condition ['order_goods_id'] = $goods_id;
		$condition ['seller_state'] = array ('lt','3' );
		$refund_list = $model_refund->getRefundReturnList ( $condition );
		
		$refund = array ();
		if (! empty ( $refund_list ) && is_array ( $refund_list )) {
			$refund = $refund_list [0];
		}
		$refund_state = $model_refund->getRefundState( $order ); // 根据订单状态判断是否可以退款退货
		if ( ($refund ['refund_id'] > 0 || $refund_state != 1) && $param->admin_info['gid'] != 2) { // 检查订单状态,防止页面刷新不及时造成数据错误
			return array ('errorno' => 1001,'msg' => '重复维权' );
		}
		$refund_array = array ();
		$refund_amount = floatval ( $param->refund_amount ); // 退款金额
		
		if (($refund_amount < 0) || ($refund_amount > $goods_pay_price)) {
			$refund_amount = $goods_pay_price;
		}
		$goods_num = intval ( $param->goods_num ); // 退货数量
		if (($goods_num < 0) || ($goods_num > $goods ['goods_num'])) {
			$goods_num = 1;
		}
		$refund_array ['reason_info'] = $reason ['reason_info'];
		$reason_id = intval ( $param->reason_id ); // 退货退款原因
		$refund_array ['reason_id'] = $reason_id;
		$refund_array ['pic_info'] = '';
		
		$model_trade = Model ( 'trade' );
		$order_shipped = $model_trade->getOrderState ( 'order_shipped' ); // 订单状态30:已发货
		if ($order ['order_state'] == $order_shipped) {
			$refund_array ['order_lock'] = '2'; // 锁定类型:1为不用锁定,2为需要锁定
		}
		if ($order ['order_state']<ORDER_STATE_SEND){
            $refund_array['before_ship'] = '1';//发货前退款
        }
		$refund_array ['refund_type'] = $param->refund_type; // 类型:1为退款,2为退货
		$refund_array ['return_type'] = $param->return_type; // 退货类型:1为不用退货,2为需要退货
//		if ($refund_array ['refund_type'] != '2') {
//			$refund_array ['refund_type'] = '1';
//			$refund_array ['return_type'] = '1';
//		}
		if($param->pic_info){
			$refund_array['pic_info']=$param->pic_info;
		}
		$refund_array ['seller_state'] = '1'; // 状态:1为待审核,2为同意,3为不同意
		$refund_array ['refund_amount'] = ncPriceFormat ( $refund_amount );
		$refund_array ['goods_num'] = $goods_num;
		$refund_array ['buyer_message'] = $param->buyer_message;
        $adminInfo = unserialize(decrypt(cookie('sys_key'),MD5_KEY));
        $adminName = isset($adminInfo['name'])?$adminInfo['name']:'';
        $refund_array['admin_name'] = isset($param->admin_info['name']) ? $param->admin_info['name'] : $adminName;
        //$refund_array['admin_name'] = isset($param->admin_info['name']) ? $param->admin_info['name'] : '';
		$refund_array ['refund_way'] = $param->refund_way ? $param->refund_way : 'predeposit';
		$refund_array ['add_time'] = time ();
		$refund_array['operation_type']=$refund_array['admin_name']?1:$param->operation_type;
		if($param->refund_way == 'fenxiao'){
			$refund_array ['add_time'] = $param->create_time ? $param->create_time : time();
			$refund_array ['fenxiao_time'] = time(); //分销退款单导入系统时间
		}
		$state = $this->addRefundReturn ( $refund_array, $order, $goods );
		if(is_array($state)){
		    return $state;
        }
		if ($state) {
			if ($order ['order_state'] == $order_shipped) {
				$model_refund->editOrderLock ( $order_id );
			}
			return array ('errorno' => 1000,'msg' => '维权插入成功' ,'id'=>$state);
		} else {
			return array ('errorno' => 1001,'msg' => '维权插入失败 refund_array:'.json_encode($refund_array).' order:'.json_encode($order));
		}
	}
	
	// 用户发起退款（退货），短信通知管理员
	function sendNotice($params) {
		$devHost = array ('192.168.11.98','127.0.0.1','localhost' );
		if (in_array ( C ( 'DB_HOST' ), $devHost )) {
			return;
		}
		
		$mobile = '18627061179';
		$email = '843129217@qq.com';
		if ($params ['return_type'] == 1) {
			$message = "用户" . "{$params['uname']}" . "申请退款产品：" . "{$params['goods_name']}" . "，退货理由为：" . "{$params['buyer_message']}" . "，订单号为：" . "{$params['order_sn']}";
		} else {
			$message = "用户" . "{$params['uname']}" . "申请退货退款产品：" . "{$params['goods_name']}" . "，换货理由为：" . "{$params['buyer_message']}" . "，订单号为：" . "{$params['order_sn']}";
		}
		$sms = new Sms ();
		$sms->send ( $mobile, $message );
		
		// 邮件通知管理员
		$emailObj = new Email ();
		$emailObj->send_sys_email ( $email, "用户退款提醒：" . $params ['reason_info'], $message );
		
		return true;
	}
	public function getRefundListByCondition($condition = array(), $page = '', $fields = '*', $limit = '', $order = 'refund_id desc',$group='',$master=false){
	    return 	$this->table ( 'refund_return' )->field ( $fields )->where ( $condition )->page ( $page )->limit ( $limit )->group($group)->order ( $order )->master($master)->select ();
    }
}
