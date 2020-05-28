<?php
/**
 * 实物订单行为
 *
 * @汉购网提供技术支持 授权请购买shopnc授权
 * @license    http://www.hangowa.com
 * @link       交流群号：
 */
defined('ByShopWWI') or exit('Access Invalid!');
class b2b_orderLogic {

    /**
     * 取消订单
     * @param array $order_info
     * @param string $role 操作角色 buyer、seller、admin、system 分别代表买家、商家、管理员、系统
     * @param string $user 操作人
     * @param string $msg 操作备注
     * @param boolean $if_update_account 是否变更账户金额
     * @param array $cancel_condition 订单更新条件,目前只传入订单状态，防止并发下状态已经改变
     * @return array
     */
    public function changeOrderStateCancel($order_info, $role, $user = '', $msg = '', $if_update_account = true, $cancel_condition = array()) {
    	try {
            $model_order = Model('b2b_order');
            $model_order->beginTransaction();
            $order_id = $order_info['order_id'];

            //库存销量变更(分销订单除外)
            if( $order_info['payment_code'] != 'fenxiao' ) {
	            $goods_list = $model_order->getOrderGoodsList(array('order_id'=>$order_id));
	            $data = array();
	            foreach ($goods_list as $goods) {
	                $data[$goods['goods_id']] = $goods['goods_num'];
	            }
	            $result = Logic('queue')->cancelOrderUpdateStorage($data);
	            if (!$result['state']) {
	                throw new Exception('还原库存失败');
	            }
            } else {
                //分销订单取消
                $fx_order_id = $order_info['fx_order_id'];
                Model('b2b_order')->cancelFenxiaoOrder($fx_order_id);
            }

            if ($order_info['chain_id']) {
                $result = Logic(queue)->cancelOrderUpdateChainStorage($data,$order_info['chain_id']);
                if (!$result['state']) {
                    throw new Exception('还原门店库存失败');
                }
            }

            if ($if_update_account) {
                $model_pd = Model('predeposit');
                //解冻充值卡
                $rcb_amount = floatval($order_info['rcb_amount']);
                if ($rcb_amount > 0) {
                    $data_pd = array();
                    $data_pd['member_id'] = $order_info['buyer_id'];
                    $data_pd['member_name'] = $order_info['buyer_name'];
                    $data_pd['amount'] = $rcb_amount;
                    $data_pd['order_sn'] = $order_info['order_sn'];
                    $model_pd->changeRcb('order_cancel',$data_pd);
                }

                //解冻预存款
                $pd_amount = floatval($order_info['pd_amount']);
                if ($pd_amount > 0) {
                    $data_pd = array();
                    $data_pd['member_id'] = $order_info['buyer_id'];
                    $data_pd['member_name'] = $order_info['buyer_name'];
                    $data_pd['amount'] = $pd_amount;
                    $data_pd['order_sn'] = $order_info['order_sn'];
                    $model_pd->changePd('order_cancel',$data_pd);
                }
                
                //解冻红包
                $model_redpacket = Model('redpacket');
                $res = $model_redpacket->releaseRedpacket($order_info['pay_sn'], $order_info['buyer_id']);
            }

            //更新订单信息
            $update_order = array('order_state'=>ORDER_STATE_CANCEL);
            $cancel_condition['order_id'] = $order_id;

            $update = $model_order->editOrder($update_order,$cancel_condition);
            if (!$update) {
                throw new Exception('保存失败');
            }

            //添加订单日志
            $data = array();
            $data['order_id'] = $order_id;
            $data['log_role'] = $role;
            $data['log_msg'] = '取消了订单';
            $data['log_user'] = $user;
            if ($msg) {
                $data['log_msg'] .= ' ( '.$msg.' )';
            }
            $data['log_orderstate'] = ORDER_STATE_CANCEL;
            $model_order->addOrderLog($data);
            $model_order->commit();

            return callback(true,'操作成功');

        } catch (Exception $e) {
            $model_order->rollback();
            return callback(false,'操作失败');
        }
    }

    /**
     * 收货
     * @param array $order_info
     * @param string $role 操作角色 buyer、seller、admin、system,chain 分别代表买家、商家、管理员、系统、门店
     * @param string $user 操作人
     * @param string $msg 操作备注
     * @return array
     */
    public function changeOrderStateReceive($order_info, $role, $user = '', $msg = '') {
        try {

            $order_id = $order_info['order_id'];
            /** @var orderModel $model_order */
            $model_order = Model('b2b_order');

            //更新订单状态
            $update_order = array();
            $update_order['finnshed_time'] = TIMESTAMP;
            $update_order['order_state'] = ORDER_STATE_SUCCESS;
            /** @var StoreService $storeService */
            $storeService = Service('Store');
            $update_order['manage_type'] = $storeService->getManageTypeById($order_info['store_id']);
            $update_order['cost_amount'] = 0;
            $storeInfo = $storeService->getStoreById($order_info['store_id']);
            // 更新订单商品成本/商家类型
            $orderGoods = $model_order->getOrderGoodsList(array('order_id'=>$order_info['order_id']));
            if(is_array($orderGoods)){
                /** @var store_bind_classModel $store_bind_class */
                $store_bind_class = Model('store_bind_class');
                $commis_rate_list = $store_bind_class->getStoreGcidCommisRateList($orderGoods);
                foreach ($orderGoods as $k=>$item){
                    continue;
                    // TODO 确认收货时补齐相关数据，待Goods相关模型完善后再编写相关代码
                    /** @var goodsModel $goodsModel */
                    $goodsModel = Model('b2b_goods');
                    $goods = $goodsModel->getGoodsInfo(array('goods_id'=>$item['goods_id']));
                    $goodsCommon = $goodsModel->getGoodsCommonInfoByID($goods['goods_commonid']);
                    $update_item=array(
                        'manage_type'=>$update_order['manage_type'],
                        // Shen.L 新增进项税和销项税率
                        'tax_input'=>$goodsCommon['tax_input'],
                        'tax_output'=>$goodsCommon['tax_output'],
                        /*'commis_rate'=>
                            isset($commis_rate_list[$item['store_id']][$item['gc_id']])?
                                $commis_rate_list[$item['store_id']][$item['gc_id']]:0,*/
                    );
                    /** 如果佣金值未设置，则设置佣金值 */
                    if($item['commis_rate']==200){
                        $update_item['commis_rate'] = isset($commis_rate_list[$item['store_id']][$item['gc_id']])?
                            $commis_rate_list[$item['store_id']][$item['gc_id']]:0;
                    }
                    if($update_item['manage_type']=='platform'){
                        $update_item['goods_cost'] = $item['goods_pay_price']*(100-$update_item['commis_rate'])/100;
                    }else{
                        // 确认收货时仅补充成本为0的订单商品
                        if($item['goods_cost']<=0)
                            $update_item['goods_cost'] = empty($goods)?0:$goods['goods_cost']*$item['goods_num'];
                        else
                            $update_item['goods_cost'] = $item['goods_cost'];
                    }
                    $update_order['cost_amount'] +=$update_item['goods_cost'];
                    $model_order->editOrderGoods($update_item,array('rec_id' => $item['rec_id']));
                }
            }
            // 添加商户类型
            $update = $model_order->editOrder($update_order,array('order_id'=>$order_id));
            if (!$update) {
                throw new Exception('保存失败');
            }

            //添加订单日志
            $data = array();
            $data['order_id'] = $order_id;
            $data['log_role'] = $role;
            $data['log_msg'] = $msg;
            $data['log_user'] = $user;
            $data['log_orderstate'] = ORDER_STATE_SUCCESS;
            $model_order->addOrderLog($data);

            if ($order_info['buyer_id'] > 0 && $order_info['order_amount'] > 0) {
                //添加会员积分
                if (C('points_isuse') == 1){
                    Model('points')->savePointsLog('order',array('pl_memberid'=>$order_info['buyer_id'],'pl_membername'=>$order_info['buyer_name'],'orderprice'=>$order_info['order_amount'],'order_sn'=>$order_info['order_sn'],'order_id'=>$order_info['order_id']),true);
                }
                //添加会员经验值
                Model('exppoints')->saveExppointsLog('order',array('exp_memberid'=>$order_info['buyer_id'],'exp_membername'=>$order_info['buyer_name'],'orderprice'=>$order_info['order_amount'],'order_sn'=>$order_info['order_sn'],'order_id'=>$order_info['order_id']),true);  
				
			$this->addStoreMony($order_info);
			$this->addInviteRate($order_info);
}
            return callback(true,'操作成功');
        } catch (Exception $e) {
            return callback(false,'操作失败');
        }
    }

    /**
     * 更改运费
     * @param array $order_info
     * @param string $role 操作角色 buyer、seller、admin、system 分别代表买家、商家、管理员、系统
     * @param string $user 操作人
     * @param float $price 运费
     * @return array
     */
    public function changeOrderShipPrice($order_info, $role, $user = '', $price) {
        try {

            $order_id = $order_info['order_id'];
            $model_order = Model('b2b_order');

            $data = array();
            $data['shipping_fee'] = abs(floatval($price));
            $data['order_amount'] = array('exp','goods_amount+'.$data['shipping_fee']);
            $update = $model_order->editOrder($data,array('order_id'=>$order_id));
            if (!$update) {
                throw new Exception('保存失败');
            }
            //记录订单日志
            $data = array();
            $data['order_id'] = $order_id;
            $data['log_role'] = $role;
            $data['log_user'] = $user;
            $data['log_msg'] = '修改了运费'.'( '.$price.' )';;
            $data['log_orderstate'] = $order_info['payment_code'] == 'offline' ? ORDER_STATE_PAY : ORDER_STATE_NEW;
            $model_order->addOrderLog($data);
            return callback(true,'操作成功');
        } catch (Exception $e) {
            return callback(false,'操作失败');
        }
    }
    /**
     * 更改价格
     * @param array $order_info
     * @param string $role 操作角色 buyer、seller、admin、system 分别代表买家、商家、管理员、系统
     * @param string $user 操作人
     * @param float $price 价格
     * @return array
     */
    public function changeOrderSpayPrice($order_info, $role, $user = '', $price) {
        try {

            $order_id = $order_info['order_id'];
            $model_order = Model('b2b_order');

            $data = array();
            $data['goods_amount'] = abs(floatval($price));
            $data['order_amount'] = array('exp','shipping_fee+'.$data['goods_amount']);
            $update = $model_order->editOrder($data,array('order_id'=>$order_id));
            if (!$update) {
                throw new Exception('保存失败');
            }
            //记录订单日志
            $data = array();
            $data['order_id'] = $order_id;
            $data['log_role'] = $role;
            $data['log_user'] = $user;
            $data['log_msg'] = '修改了价格'.'( '.$price.' )';;
            $data['log_orderstate'] = $order_info['payment_code'] == 'offline' ? ORDER_STATE_PAY : ORDER_STATE_NEW;
            $model_order->addOrderLog($data);
            return callback(true,'操作成功');
        } catch (Exception $e) {
            return callback(false,'操作失败');
        }
    }
    /**
     * 回收站操作（放入回收站、还原、永久删除）
     * @param array $order_info
     * @param string $role 操作角色 buyer、seller、admin、system 分别代表买家、商家、管理员、系统
     * @param string $state_type 操作类型
     * @return array
     */
    public function changeOrderStateRecycle($order_info, $role, $state_type) {
        $order_id = $order_info['order_id'];
        $model_order = Model('b2b_order');
        //更新订单删除状态
        $state = str_replace(array('delete','drop','restore'), array(ORDER_DEL_STATE_DELETE,ORDER_DEL_STATE_DROP,ORDER_DEL_STATE_DEFAULT), $state_type);
        $update = $model_order->editOrder(array('delete_state'=>$state),array('order_id'=>$order_id));
        if (!$update) {
            return callback(false,'操作失败');
        } else {
            return callback(true,'操作成功');
        }
    }

    /**
     * 发货
     * @param array $order_info
     * @param string $role 操作角色 buyer、seller、admin、system 分别代表买家、商家、管理员、系统
     * @param string $user 操作人
     * @param array $post 数据
     * @return array
     */
    public function changeOrderSend($order_info, $role, $user = '', $post = array()) {
        $order_id = $order_info['order_id'];
        /** @var b2b_orderModel $model_order */
        $model_order = Model('b2b_order');
        try {
            $model_order->beginTransaction();
            $data = array();
            if (!empty($post['reciver_name'])) {
                $data['reciver_name'] = $post['reciver_name'];
            }
            if (!empty($post['reciver_info'])) {
                $data['reciver_info'] = $post['reciver_info'];
            }
            $data['deliver_explain'] = $post['deliver_explain'];
            $data['daddress_id'] = intval($post['daddress_id']);
            $data['shipping_express_id'] = intval($post['shipping_express_id']);
            $data['shipping_time'] = TIMESTAMP;

            $condition = array();
            $condition['order_id'] = $order_id;
            $condition['store_id'] = $order_info['store_id'];
            $update = $model_order->editOrderCommon($data,$condition);
            if (!$update) {
                throw new Exception('操作失败');
            }
           
            $data = array();
            $data['shipping_code']  = $post['shipping_code'];
            $data['order_state'] = ORDER_STATE_SEND;
            $data['delay_time'] = TIMESTAMP;
            $update = $model_order->editOrder($data,$condition);
            if (!$update) {
                throw new Exception('操作失败');
            }
            $model_order->commit();
        } catch (Exception $e) {
            $model_order->rollback();
            return callback(false,$e->getMessage());
        }

		$express_info = Model('express')->getExpressInfo(intval($post['shipping_express_id']));
        //更新表发货信息
        if ($post['shipping_express_id'] && $order_info['extend_order_common']['reciver_info']['dlyp']) {
            $data = array();
            $data['shipping_code'] = $post['shipping_code'];
            $data['order_sn'] = $order_info['order_sn'];
            $data['express_code'] = $express_info['e_code'];
            $data['express_name'] = $express_info['e_name'];
            Model('delivery_order')->editDeliveryOrder($data,array('order_id' => $order_info['order_id']));
        }

        //添加订单日志
        $data = array();
        $data['order_id'] = $order_id;
        $data['log_role'] = 'seller';
        $data['log_user'] = $user;
        $data['log_msg'] = '发出货物(编辑信息)';
        $data['log_orderstate'] = ORDER_STATE_SEND;
        $model_order->addOrderLog($data);

        // 发送买家消息
        $param = array();
        $param['code'] = 'order_deliver_success';
        $param['member_id'] = $order_info['buyer_id'];
        $param['param'] = array(
            'order_sn' => $order_info['order_sn'],
            'order_url' => urlShop('member_order', 'show_order', array('order_id' => $order_id))
        );
        QueueClient::push('sendMemberMsg', $param);
		
		//发送短信
		$devHost = array('192.168.11.98', '127.0.0.1', 'localhost') ;
		/** @var FenxiaoService $service */
		$service = Service('Fenxiao');
		$fxMembers = $service -> getFenxiaoMembers() ;
		if( !in_array(C('DB_HOST'), $devHost)  // 非测试环境
			&& !array_key_exists($order_info['buyer_id'], $fxMembers) // 非分销平台
			&& $post['shipping_code'] // 有运单号
			|| $order_info['buyer_name'] == 'oldhango' ) // 或者是老平台的
		{
			$sms_message = "您的订单号：{$order_info['order_sn']} 的商品已发货，快递：{$express_info['e_name']} ( {$post['shipping_code']} )" ;
			$sms = new Sms();
			$sms->send( $order_info['buyer_phone'], $sms_message );
		}

        return callback(true,'操作成功');
    }

    /**
     * 收到货款
     * @param array $order_info
     * @param string $role 操作角色 buyer、seller、admin、system 分别代表买家、商家、管理员、系统
     * @param string $user 操作人
     * @return array
     */
    public function changeOrderReceivePay($order_list, $role, $user = '', $post = array()) {
        $model_order = Model('b2b_order');

        try {
            $model_order->beginTransaction();

            $model_pd = Model('predeposit');
            foreach($order_list as $order_info) {
                $order_id = $order_info['order_id'];
                if (!in_array($order_info['order_state'],array(ORDER_STATE_NEW))) continue;
                //下单，支付被冻结的充值卡
                $rcb_amount = floatval($order_info['rcb_amount']);
                if ($rcb_amount > 0) {
                    $data_pd = array();
                    $data_pd['member_id'] = $order_info['buyer_id'];
                    $data_pd['member_name'] = $order_info['buyer_name'];
                    $data_pd['amount'] = $rcb_amount;
                    $data_pd['order_sn'] = $order_info['order_sn'];
                    $model_pd->changeRcb('order_comb_pay',$data_pd);
                }

                //下单，支付被冻结的预存款
                $pd_amount = floatval($order_info['pd_amount']);
                if ($pd_amount > 0) {
                    $data_pd = array();
                    $data_pd['member_id'] = $order_info['buyer_id'];
                    $data_pd['member_name'] = $order_info['buyer_name'];
                    $data_pd['amount'] = $pd_amount;
                    $data_pd['order_sn'] = $order_info['order_sn'];
                    $model_pd->changePd('order_comb_pay',$data_pd);
                }

                //更新订单相关扩展信息
                $result = $this->_changeOrderReceivePayExtend($order_info,$post);
                if (!$result['state']) {
                    throw new Exception($result['msg']);
                }

                //添加订单日志
                $data = array();
                $data['order_id'] = $order_id;
                $data['log_role'] = $role;
                $data['log_user'] = $user;
                $data['log_msg'] = '收到货款(外部交易号:'.$post['trade_no'].')';
                $data['log_orderstate'] = ORDER_STATE_PAY;
                $insert = $model_order->addOrderLog($data);
                if (!$insert) {
                    throw new Exception('操作失败');
                }

                //更新订单状态
                $update_order = array();
                $update_order['order_state'] = ORDER_STATE_PAY;
                $update_order['payment_time'] = ($post['payment_time'] ? strtotime($post['payment_time']) : TIMESTAMP);
                $update_order['payment_code'] = $post['payment_code'];
                if ($post['trade_no'] != '') {
                    $update_order['trade_no'] = $post['trade_no'];
                }
                $condition = array();
                $condition['order_id'] = $order_info['order_id'];
                $condition['order_state'] = ORDER_STATE_NEW;
                $update = $model_order->editOrder($update_order,$condition);
                if (!$update) {
                    throw new Exception('操作失败');
                }
                /** @var CpsService $cpsService */
                $cpsService = Service('Cps');
                $cpsService->payOrder($order_id);
            }

            //更新支付单状态
            $data = array();
            $data['api_pay_state'] = 1;
            $update = $model_order->editOrderPay($data,array('pay_sn'=>$order_info['pay_sn']));
            if (!$update) {
                throw new Exception('更新支付单状态失败');
            }

            $model_order->commit();
        } catch (Exception $e) {
            $model_order->rollback();
            return callback(false,$e->getMessage());
        }

        foreach($order_list as $order_info) {

            $order_id = $order_info['order_id'];
            //支付成功发送买家消息
            $param = array();
            $param['code'] = 'order_payment_success';
            $param['member_id'] = $order_info['buyer_id'];
            $param['param'] = array(
                    'order_sn' => $order_info['order_sn'],
                    'order_url' => urlShop('member_order', 'show_order', array('order_id' => $order_info['order_id']))
            );
            QueueClient::push('sendMemberMsg', $param);

            //非预定订单下单或预定订单全部付款完成
            if ($order_info['order_type'] != 2 || $order_info['if_send_store_msg_pay_success']) {
                //支付成功发送店铺消息
                $param = array();
                $param['code'] = 'new_order';
                $param['store_id'] = $order_info['store_id'];
                $param['param'] = array(
                        'order_sn' => $order_info['order_sn']
                );
                QueueClient::push('sendStoreMsg', $param);
                //门店自提发送提货码
                if ($order_info['order_type'] == 3) {
                    $_code = rand(100000,999999);
                    $result = $model_order->editOrder(array('chain_code'=>$_code),array('order_id'=>$order_info['order_id']));
                    if (!$result) {
                        throw new Exception('订单更新失败');
                    }
                    $param = array();
                    $param['chain_code'] = $_code;
                    $param['order_sn'] = $order_info['order_sn'];
                    $param['buyer_phone'] = $order_info['buyer_phone'];
                    QueueClient::push('sendChainCode', $param);
                }
            }
        }

        return callback(true,'操作成功');
    }

    /**
     * 更新订单相关扩展信息
     * @param unknown $order_info
     * @return unknown
     */
    private function _changeOrderReceivePayExtend($order_info, $post) {
        //预定订单收款
        if ($order_info['order_type'] == 2) {
            $result = Logic('order_book')->changeBookOrderReceivePay($order_info, $post);
        }
        return callback(true);
    }
	
	/**添加相关预存款 by shopwwi.com**/
		public function addInviteRate($order_info){
			$model_order = Model('b2b_order');
			$invite_info=Model('member')->table('member')->where(array('member_id'=>$order_info['buyer_id']))->find();
			$invite_money=0;
			//取得拥金金额
			 $field = 'SUM(ROUND(goods_num*invite_rates)) as commis_amount';
			 $order_goods_condition['order_id'] = $order_info['order_id'];
		     $order_goods_condition['buyer_id'] = $order_info['buyer_id'];
             $order_goods_info = $model_order->getOrderGoodsInfo($order_goods_condition,$field);
             $commis_rate_totals_array[] = $order_goods_info['commis_amount'];
			 $commis_amount_sum=floatval(array_sum($commis_rate_totals_array)); 
			  
			 if($commis_amount_sum>0)
			 {
				  $invite_money=$commis_amount_sum;
				  $invite_money2 = ceil($commis_amount_sum * $GLOBALS['setting_config']['shopwwi_invite2']*0.01);
				  $invite_money3 = ceil($commis_amount_sum * $GLOBALS['setting_config']['shopwwi_invite3']*0.01);
			 }
			//检测是否货到付款方式
			$is_offline=($order_info['payment_code']=="offline");
			$model_member = Model('member');
			//取得一级推荐会员
			$invite_one_id = $model_member->table('member')->getfby_member_id($invite_info['member_id'],'invite_one');
			$invite_one_name = $model_member->table('member')->getfby_member_id($invite_one_id,'member_name');
			//取得二级推荐会员
			$invite_two_id = $model_member->table('member')->getfby_member_id($invite_info['member_id'],'invite_two');
			$invite_two_name = $model_member->table('member')->getfby_member_id($invite_two_id,'member_name');
			//取得三级推荐会员
			$invite_three_id = $model_member->table('member')->getfby_member_id($invite_info['member_id'],'invite_three');
			$invite_three_name = $model_member->table('member')->getfby_member_id($invite_three_id,'member_name');
			
		     if($invite_money>0&&$is_offline==false){
			    
				//变更会员预存款
			   $model_pd = Model('predeposit');
			   if($invite_one_id!=0){
		       $data = array();
			   $data['invite_member_id'] = $order_info['buyer_id'];
		       $data['member_id'] = $invite_one_id;
		       $data['member_name'] = $invite_one_name;
		       $data['amount'] = $invite_money;
		       $data['order_sn'] = $order_info['order_sn'];
		       $model_pd->changePd('order_invite',$data);}
			   
			   if($invite_two_id!=0){
		       $data_pd = array();
			   $data_pd['invite_member_id'] = $order_info['buyer_id'];
		       $data_pd['member_id'] = $invite_two_id;
		       $data_pd['member_name'] = $invite_two_name;
		       $data_pd['amount'] = $invite_money2;
		       $data_pd['order_sn'] = $order_info['order_sn'];
		       $model_pd->changePd('order_invite',$data_pd);}
			   
			   if($invite_three_id!=0){
		       $datas = array();
			   $datas['invite_member_id'] = $order_info['buyer_id'];
		       $datas['member_id'] = $invite_three_id;
		       $datas['member_name'] = $invite_three_name;
		       $datas['amount'] = $invite_money3;
		       $datas['order_sn'] = $order_info['order_sn'];
		       $model_pd->changePd('order_invite',$datas);}
			   
			   
			 }	 
	}
	/**写入卖家预存款账号**/
		public function addStoreMony($order_info){
			$model_order = Model('b2b_order');
			$store_info=Model('store')->table('store')->where(array('store_id'=>$order_info['store_id']))->find();
			$seller_info=Model('member')->table('member')->where(array('member_id'=>$store_info['member_id']))->find();
			$refund=Model('refund_return')->table('refund_return')->where(array('order_id'=>$order_info['order_id'],'refund_state'=>3))->find();
			$seller_money=0;
            if($refund){
                $seller_money=$order_info['order_amount']-$refund['refund_amount'];
            }else{
                $seller_money=$order_info['order_amount'];
            }
			//取得拥金金额
			 $field = 'SUM(ROUND(goods_pay_price*commis_rate/100,2)) as commis_amount';
			 $order_goods_condition['order_id'] = $order_info['order_id'];
		     $order_goods_condition['buyer_id'] = $order_info['buyer_id'];
             $order_goods_info = $model_order->getOrderGoodsInfo($order_goods_condition,$field);
             $commis_rate_totals_array[] = $order_goods_info['commis_amount'];
			 $commis_amount_sum=floatval(array_sum($commis_rate_totals_array)); 
			  
			 if($commis_amount_sum>0)
			 {
				  $seller_money=$seller_money-$commis_amount_sum;
			 }
			//检测是否货到付款方式
			$is_offline=($order_info['payment_code']=="offline");
		     if($seller_money>0&&$is_offline==false)
			 {
			    //变更会员预存款
			   $model_pd = Model('predeposit');
		       $data = array();
			   $data['msg']="";
			   if($commis_amount_sum>0)
			   {
				    $data['msg']=$commis_amount_sum;
			   }
		       $data['member_id'] = $store_info['member_id'];
		       $data['member_name'] = $store_info['member_name'];
		       $data['amount'] = $seller_money;
		       $data['pdr_sn'] = $order_info['order_sn'];
		       $model_pd->changePd('seller_money',$data);
			 }
	}
}