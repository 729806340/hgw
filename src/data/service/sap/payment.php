<?php

class payment extends commons
{
    private $payment_code = array(//收款方式
        'offline' => '货到付款',
        'alipay' => '支付宝',
        'tenpay' => '财付通',
        'chinabank' => '网银在线',
        'predeposit' => '余额支付',
        'wx_jsapi' => '微信支付',
        'wxpay' => '微信支付',
        'yeepay' => '易宝支付',
        'jicai' => '线下支付',
        // 'fenxiao' => array(
        //     'youzan' => '有赞',
        //     'renrendian' => '人人店',
        //     'pinduoduo' => '拼多多',
        // 	'oldhango' => '汉购旧平台',
        // 	'fanli' => '返利',
        // 	'zhe800' => '折800',
        // 	'gegejia' => '格格家',
        // 	'mengdian' => '萌店',
        // 	'taobaofx' => '淘宝',
        // 	'juanpi' => '卷皮'
        // ),
    );

    private $refund_way = array(//退款方式
        'predeposit' => '预存款',
        'alipay' => '支付宝',
        'offline' => '线下支付',
        'yeepay' => '易宝支付',
        'fenxiao' => '分销支付',
    	'b2b' => 'b2b支付'
    );

    private $refund_list = array();//需要推送的数据
    private $refund_item = array();//记录每条退款记录的子订单数，用于比对是否全部成功
    private $make_item = array();//记录每条收款记录的子订单数，用于比对是否全部成功


    //sap401 收款
    public function make()
    {
        $code = $this->getCode(__CLASS__, __FUNCTION__);
        /*9月1号，过滤苏宁易购，人人优品的荆州门店==15*/
        $where['filter_status']='0';
        $where['payment_time'] = array('gt', '0');//已支付
        $where['make_send_time'] = '0';//未推送

        $where['order_from'] = array('neq', 3);//已支付
        $list_not_fenxiao = Model('orders')->where($where)->limit($this->getLimit($code))->select();

        $where['order_from'] = 3;//分销订单，确认后再推收款
        $where['order_state'] = '40';//分销订单，确认后再推收款
        $list_fenxiao = Model('orders')->where($where)->limit($this->getLimit($code))->select();

        $list = array_merge($list_not_fenxiao, $list_fenxiao);


        //补推定制化数据
        if ('true' == $_GET['add_push']) {
            unset($where['order_from']);
            unset($where['make_send_time']);
            $add_orders = array(
                '161009093268833001'=>1,
                '161009102077148001'=>1,
                '161009220488671001'=>1,
            );
            
            $explode_orders = array(
                '161009105008025001' => 10,
                '161009105009754001' => 10,
            );
            foreach ($explode_orders as $key => $value) {
                unset($add_orders[$key]);
            }
            $add_orders_sn = array_keys($add_orders);

            $where['order_sn'] = array('in', $add_orders_sn);
            $list_add = Model('orders')->where($where)->limit($this->getLimit($code))->select();
            $list = $list_add;
        }

        //改order_amount
        foreach ($list as $key => $value) {
            if (in_array( $value['order_sn'], $add_orders_sn)) {
                $value['order_sn'] = strval($value['order_sn']);
                if (empty($add_orders[$value['order_sn']])) {
                    v($value['order_sn'] . 'add error!', 0);
                }
                $list[$key]['order_amount'] = $add_orders[$value['order_sn']];
                $list[$key]['add_push'] = '1';
            }
        }

        $data = array();
        foreach ((array)$list as $v) {
            if (empty($v['payment_code'])) continue;
            $tid = $code . '_' . $v['order_id'] . '_';
            //公共部分
            $it['oid'] = $v['order_sn'];//订单号
            $it['payOrderNumber'] = $v['pay_sn'];//支付单号
            $it['date'] = date('Y-m-d', $v['payment_time']);//过账日期
            
            $it['customer'] = $v['buyer_name'];//店主账号	买家
            //如果是集采订单，优先用留言当购买者，否则按照发票抬头 进而  收货人来
            if (4 == $v['order_from']) {
                $order_common = Model('order_common')->field('order_message,invoice_info,reciver_name')->where(array('order_id'=>$v['order_id']))->find();
                $order_message = preg_replace('/\s/', '', $order_common['order_message']);  //去除所有空格类型字符
                
                $invoice_info = unserialize($order_common['invoice_info']);
                $invoice_title = empty($invoice_info['抬头']) || ('个人' == $invoice_info['抬头']) ? '' : $invoice_info['抬头'];

                $it['customer'] = $order_common['reciver_name'];
//                if (empty($order_message) && empty($invoice_title)) {
//                    $it['customer'] = $order_common['reciver_name'];
//                } else {
//                    $it['customer'] = $invoice_title . ' ' . $order_message ;
//                }
            }
            
            $it['cardCode'] = $v['store_id'];//商户代码	客户代码
            $it['cardName'] = $v['store_name'];//商户名称	客户名称
            $it['dischannel'] = empty($v['fx_order_id'])?$v['trade_no']:$v['fx_order_id'];
            $it['fee'] = $v['technical_fee'];//技术服务费
            $it['finFee'] = $v['financial_fee'];//金融服务费

            //common end
            $k = 0;//拆分个数
            if ($v['payment_code'] == 'fenxiao') {
                //补充推送数据
                if ('1' == $v['add_push']) {
                    $k = '110';
                }

                $it['tid'] = $tid . $k;//电商平台单号	主键
                $it['payMode'] = getFxNameByUname($v['buyer_name']);//$this->payment_code['fenxiao'][$v['buyer_name']];//支付方式
                //补充推送数据
                if ('1' == $v['add_push']) {
                    $it['payMode'] .= '-优惠券';
                }

                $it['total'] = $v['order_amount'];//销售金额	订单销售总额
                $it['dischannel'] = $v['fx_order_id'];//渠道订单号 分销才有

                $k++;
                $data[] = $it;
            } else {
                //充值卡支付
                if ($v['rcb_amount'] > 0) {
                    $it['tid'] = $tid . $k;//电商平台单号	主键
                    $it['payMode'] = '充值卡';//支付方式
                    $it['total'] = $v['rcb_amount'];//支付金额

                    $k++;
                    $data[] = $it;
                }
                //预存款支付
                if ($v['pd_amount'] > 0) {
                    $it['tid'] = $tid . $k;//电商平台单号	主键
                    $it['payMode'] = '预存款';//支付方式
                    $it['total'] = $v['pd_amount'];//支付金额

                    $k++;
                    $data[] = $it;
                }
                //其它支付方法
                $online_total = sprintf('%.2f', $v['order_amount'] - $v['rcb_amount'] - $v['pd_amount']);
                if ($online_total > 0) {
                    $it['tid'] = $tid . $k;//电商平台单号	主键
                    $it['payMode'] = $this->payment_code[$v['payment_code']];//支付方式
                    $it['total'] = $online_total;//销售金额	订单销售总额

                    $k++;
                    $data[] = $it;
                }
            }

            $this->make_item[$v['order_id']] = $k;
        }
        return $data;
    }

    //sap401 收款推送成功后更新同步时间
    public function make_after($success, $error, $exist='')
    {
        $rel = $make_ids = array();
        $success = array_merge($success, $exist);
        //计算成功的子订单个数
        foreach ((array)$success as $tid) {
            list($id, $k) = explode('_', $tid);
            if (is_null($k)) continue;
            $rel[$id] = intval($rel[$id]) + 1;
        }
        //比对
        foreach ($rel as $order_id => $num) {
            if ($this->make_item[$order_id] != $num) continue;
            $make_ids[] = $order_id;
        }
        //修改推送状态
        if (count($make_ids) > 0) {
            $where['order_id'] = array('in', $make_ids);//成功的记录更新时间
            Model('orders')->where($where)->update(array('make_send_time' => time()));
        }
        return true;
    }

    //sap
    public function check_callback($rel){
        $code = 'sap406';
        $check = $rel['check'];
        $ob_id = $rel['ob_id'];
        $condition['ob_id'] = $ob_id;

        $bill_info = Model('order_bill')->where($condition)->find();

        $where['seller_state'] = '2';//商家同意
        $where['kefu_state'] = '2';//客服已同意
        $where['seller_state'] = 2;
        $where['goods_id'] = array('gt', 0);
        $where['store_id'] = $bill_info['ob_store_id'];
        $where['admin_time'] = array('between',"{$bill_info['ob_start_date']},{$bill_info['ob_end_date']}");

        if($check > 0){
            $where['check_status'] = array('neq',$check);
        }
        $list = Model('refund_return')->where($where)->limit($this->getLimit($code))->select();

        $order_info = array();
        $order_ids = array_column($list, 'order_id');
        if (count($order_ids) > 0) {
            $orders = Model('orders')->field('order_id,order_sn,buyer_name,payment_code,order_amount,fx_order_id')
                ->where(array('order_id' => array('in', $order_ids)))->select();
            $order_info = array_under_reset($orders, 'order_id');
        }

        foreach ((array)$list as $v) {
            if (empty($v['order_id']) || empty($order_info[$v['order_id']])) continue;
            $v['order_info'] = $order_info[$v['order_id']];
//            $v['tid'] = $code . '_'.$ob_id.'_'. $v['refund_id'];
            $v['tid'] = 'sap402' . '_' . $v['refund_id'];
            if ($v['return_type'] == '1') {
                //不用退货
                $this->refundMoney($v);
            } elseif ($v['return_type'] == '2') {
                if ($v['goods_id'] == '0') {
                    //全退
                    $this->refundAll($v);
                } else {
                    //退一种商品
                    $this->refundOne($v);
                }
            }

        }

        $order_sn_array = array();
        if (is_array($this->refund_list)) {
            foreach ($this->refund_list as $refund_info) {
                $order_sn_array[] = $refund_info['oid'];
            }
        }

        if($check > 0){
            $order_check_condition = array();
            $order_check_condition['order_sn'] = array('in',$order_sn_array);
            $model_order = Model('refund_return');
            $model_order->setCheck($order_check_condition,$check);
        }
        return $this->refund_list;
    }

    public function check_result_callback_old($rel){
        $data = array();
        foreach ((array)$rel as $row) {
            if (empty($row['tid'])) continue;
            list($code, $refund_id,$zero) = explode('_', $row['tid']);
            $data[$row['ob_id']][$refund_id]['check_result'] = $row['check_result'] ;
            $data[$row['ob_id']][$refund_id]['errInf'] = $row['errInf'] ;
        }

        foreach($data as $ob_id => $order_id_array){
            foreach($order_id_array as $refund_id => $check_info){
                $condition['refund_id'] = $refund_id;
                $order_data['check_result'] = $check_info['check_result'];
                $order_data['errInf'] = $check_info['errInf'];
                Model()->table('refund_return')->where($condition)->update($order_data);
            }
            //将该账单其余的订单设为正确
            $model_bill = Model('order_bill');
            $ob_condition['ob_id'] = $ob_id;
            $bill_info = $model_bill->where($ob_condition)->find() ;

            $where['seller_state'] = '2';//商家同意
            $where['kefu_state'] = '2';//客服已同意
            $where['seller_state'] = 2;
            $where['goods_id'] = array('gt', 0);
            $where['store_id'] = $bill_info['ob_store_id'];
            $where['check_result'] = array('not in','-1,-2,-3,-4,-5');
            $where['admin_time'] = array('between',"{$bill_info['ob_start_date']},{$bill_info['ob_end_date']}");

            $order_data['check_result'] = 1;
            $order_data['errInf'] = '';
            Model('refund_return')->where($where)->update($order_data);
        };
        return true;
    }

    public function check_result_callback($rel){
        $data = array();
        foreach ((array)$rel['err_list'] as $row) {
            if (empty($row['tid'])) continue;
            list($code, $refund_id,$zero) = explode('_', $row['tid']);
            $data[$rel['ob_id']][$refund_id]['check_result'] = $row['check_result'] ;
            $data[$rel['ob_id']][$refund_id]['errInf'] = $row['errInf'] ;
        }

            foreach($data[$rel['ob_id']] as $refund_id => $check_info){
                $condition['refund_id'] = $refund_id;
                $order_data['check_result'] = $check_info['check_result'];
                $order_data['errInf'] = $check_info['errInf'];
                Model()->table('refund_return')->where($condition)->update($order_data);
            }
            //将该账单其余的订单设为正确
            $model_bill = Model('order_bill');
            $ob_condition['ob_id'] = $rel['ob_id'];
            $bill_info = $model_bill->where($ob_condition)->find() ;

            $condition = array();
            $where['goods_id'] = array('gt', 0);
            $where['store_id'] = $bill_info['ob_store_id'];
            $where['check_result'] = array('in','-1,-2,-3,-4,-5');
            $where['admin_time'] = array('between',"{$bill_info['ob_start_date']},{$bill_info['ob_end_date']}");
            $err_count = Model()->table('refund_return')->where($condition)->count();
            if($err_count == $rel['total_err_num']){
                $where['seller_state'] = '2';//商家同意
                $where['kefu_state'] = '2';//客服已同意
                $where['seller_state'] = 2;
                $where['goods_id'] = array('gt', 0);
                $where['store_id'] = $bill_info['ob_store_id'];
                $where['check_result'] = array('not in','-1,-2,-3,-4,-5');
                $where['admin_time'] = array('between',"{$bill_info['ob_start_date']},{$bill_info['ob_end_date']}");

                $order_data['check_result'] = 1;
                $order_data['errInf'] = '';
                Model('refund_return')->where($where)->update($order_data);
            }
        return true;
    }

    //sap402 退款
    public function refund()
    {
        $code = $this->getCode(__CLASS__, __FUNCTION__);
        $where['seller_state'] = '2';//商家同意
        $where['kefu_state'] = '2';//客服已同意
        $where['send_sap'] = '0';//未推送
        $where['refund_state'] = '3';//退款平台审核完成

        /*9月1号，过滤苏宁易购，人人优品的荆州门店==15*/
        $where['filter_status']='0';
        $list = Model('refund_return')->where($where)->limit($this->getLimit($code))->select();
        $order_info = array();
        $order_ids = array_column($list, 'order_id');
        if (count($order_ids) > 0) {
            $orders = Model('orders')->field('order_id,order_sn,buyer_name,payment_code,order_amount,fx_order_id')
                ->where(array('order_id' => array('in', $order_ids)))->select();
            $order_info = array_under_reset($orders, 'order_id');
        }
        foreach ((array)$list as $v) {
            if (empty($v['order_id']) || empty($order_info[$v['order_id']])) continue;
            $v['order_info'] = $order_info[$v['order_id']];
            $v['tid'] = $code . '_' . $v['refund_id'];
            if ($v['return_type'] == '1') {
                //不用退货
                $this->refundMoney($v);
            } elseif ($v['return_type'] == '2') {
                if ($v['goods_id'] == '0') {
                    //全退
                    $this->refundAll($v);
                } else {
                    //退一种商品
                    $this->refundOne($v);
                }
            }

        }
        return $this->refund_list;
    }

    //sap402 后续处理
    public function refund_after($success, $error, $exist='')
    {
        $rel = $refund_ids = array();
        //计算成功的子订单个数
        foreach ((array)$success as $tid) {
            list($id, $k) = explode('_', $tid);
            if (is_null($k)) continue;
            $rel[$id] = intval($rel[$id]) + 1;
        }
        //计算已存在的子订单个数
        foreach ((array)$exist as $tid) {
        	list($id, $k) = explode('_', $tid);
        	if (is_null($k)) continue;
        	$rel[$id] = intval($rel[$id]) + 1;
        }
        //比对
        foreach ($rel as $refund_id => $num) {
            if ($this->refund_item[$refund_id] != $num) continue;
            $refund_ids[] = $refund_id;
        }

        //修改推送状态
        if (count($refund_ids) > 0) {
            $where['refund_id'] = array('in', $refund_ids);
            Model('refund_return')->where($where)->update(array('send_sap' => '2'));
        }
        return true;
    }

    //sap403 完结退款，财务确认打款后最后一步处理
    public function end_refund_callback($rel)
    {
        $notice = array();

        foreach ((array)$rel as $row) {
            if (empty($row['tid'])) continue;
            list(, $id) = explode('_', $row['tid']);
            if (empty($id) || $row['status'] != '0') {
                $notice[] = 'TID:' . $row['tid'] . '<br>error:' . $row['errInf'];
            } else {
                try {
                    $this->doRefund($id);
                } catch (Exception $e) {
                    $notice[] = 'REFUND_ID:' . $id . '<br>error:' . $e->getMessage();
                }
            }
        }

        if (!empty($notice)) {
            $this->failed[] = array(
                'title' => '报警：来自 SAP403 报警信息',
                'msg' => implode('<br>', $notice),
            );
        }

        return true;
    }

    private function doRefund($refund_id)
    {
        $model_refund = Model('refund_return');
        $condition = array();
        $condition['refund_id'] = intval($refund_id);
        $refund = $model_refund->getRefundReturnInfo($condition);
        if (!$refund['order_id']) {
            throw new Exception('没有退款单信息');
        }

        if ($refund['seller_state'] == '2' && $refund['refund_state'] == '3') {//分销订单自动完成
            return true;
        }
        if ($refund['seller_state'] == '3') {//商家已拒绝
            throw new Exception('商家已拒绝');
        }
        if ($refund['kefu_state'] == '1') {//客服未审核
            throw new Exception('客服未审核');
        }

        $order_id = $refund['order_id'];
        $model_order = Model('order');
        $order = $model_order->getOrderInfo(array('order_id' => $order_id), array());
        if ($order['payment_time'] > 0) {
            $order['pay_amount'] = $order['order_amount'] - $order['rcb_amount'] - $order['pd_amount'];//在线支付金额=订单总价格-充值卡支付金额-预存款支付金额
        }

        $detail_array = $model_refund->getDetailInfo($condition);
        if (empty($detail_array)) {
            $model_refund->addDetail($refund, $order);
            $detail_array = $model_refund->getDetailInfo($condition);
        }

        if ($detail_array['pay_time'] > 0) {
            $refund['pay_amount'] = $detail_array['pay_amount'];//已完成在线退款金额
        }

        try {
            $model_refund->beginTransaction();
            $state = $model_refund->editOrderRefund($refund, 'sap');
            if ($state) {
                $refund_array = array();
                $refund_array['admin_time'] = time();
                $refund_array['refund_state'] = '3';//状态:1为处理中,2为待管理员处理,3为已完成
                //$refund_array['admin_message'] = '审核完成';

                if (!$model_refund->editRefundReturn($condition, $refund_array)) {
                    throw new Exception('平台确认退款记录修改失败');
                }

                $model_refund->commit();
                return true;
            } else {
                throw new Exception('平台确认修改订单状态失败');
            }
        } catch (Exception $e) {
            $model_refund->rollback();
        }
    }


    private function refundCommon($refund)
    {
        $it['oid'] = $refund['order_info']['order_sn'];//平台电商号
        $it['payMode'] = $this->refund_way[$refund['refund_way']];//退款方式
        $it['date'] = date('Y-m-d', $refund['add_time']);//过账日期
        $it['customer'] = $refund['buyer_name'];//店主账号	买家
        
        //如果是集采订单，优先用留言当购买者，否则按照发票抬头 进而  收货人来
        if (4 == $refund['order_info']['order_from']) {
            $order_common = Model('order_common')->field('order_message,invoice_info,reciver_name')->where(array('order_id'=>$refund['order_info']['order_id']))->find();
            $order_message = preg_replace('/\s/', '', $order_common['order_message']);  //去除所有空格类型字符
            
            $invoice_info = unserialize($order_common['invoice_info']);
            $invoice_title = empty($invoice_info['抬头']) || ('个人' == $invoice_info['抬头']) ? '' : $invoice_info['抬头'];
            
            
            if (empty($order_message) && empty($invoice_title)) {
                $it['customer'] = $order_common['reciver_name'];
            } else {
                $it['customer'] = $invoice_title . ' ' . $order_message ;
            }
        }
        
        
        $it['cardCode'] = $refund['store_id'];//商户代码	客户代码
        $it['cardName'] = $refund['store_name'];//商户名称	客户名称
        $it['total'] = $refund['order_info']['order_amount'];//销售金额	订单销售总额
        $it['dischannel'] = $refund['order_info']['payment_code'] == 'fenxiao' ? $refund['order_info']['fx_order_id'] : '';//渠道订单号 分销才有

        if ($refund['refund_way'] == 'fenxiao') {
            $it['payMode'] = getFxNameByUname($refund['buyer_name']);//退款支付方式
            // $this->payment_code['fenxiao'][$refund['buyer_name']];
        } else {
           1;
        }

        return $it;
    }

    //sap402 只退款 数据处理
    private function refundMoney($refund)
    {
        $it = $this->refundCommon($refund);
        $it['tid'] = $refund['tid'] . '_0';//退款单号	主键
        $it['itemCode'] = '';//物料编号
        $it['retotal'] = $refund['refund_amount'];//退款金额
        $it['quantity'] = '0';//退货：quantity>0  退款：quantity=0	退货数量

        $this->refund_list[] = $it;
        $this->refund_item[$refund['refund_id']] = 1;
    }

    //sap402 全退
    private function refundAll($refund)
    {
        $it = $this->refundCommon($refund);

        $order_goods = Model('order_goods')->field('goods_id,goods_pay_price,goods_num,tax_input,tax_output')->where(array('order_id' => $refund['order_id']))->select();
        if (empty($order_goods) || !is_array($order_goods)) return false;
        $items = array();
        //退款比例
        $ratio = 0;
        if ($refund['order_info']['order_amount'] > 0) $ratio = floatval($refund['refund_amount'] / $refund['order_info']['order_amount']);
        $sub_total = 0;
        foreach ($order_goods as $k => $v) {
        	if( $v['tax_output'] == '200.00' || $v['tax_input'] == '200.00' ) return false;
        	
            if ('platform' == $v['manage_type']) {
                $v['tax_output'] = $v['tax_output'] = 0;
            }

            $it['tid'] = $refund['tid'] . '_' . $k;//退款单号	主键
            $it['itemCode'] = $v['goods_id'];//物料编号
            $it['retotal'] = round($v['goods_pay_price'] * $ratio, 2);//退款金额
            $it['quantity'] = $v['goods_num'];//退货：quantity>0  退款：quantity=0	退货数量
            $it['userFields']['U_TAX_RATE'] = $v['tax_output']/100 ;
			// $it['userFields']['vatGroupPu'] = inputTax( $v['tax_input'] );
			// $it['userFields']['vatGourpSa'] = inputTax( $v['tax_output'] );
            $it['vatGourp']   = inputTax( $v['tax_output'] );
            $items[] = $it;
            $sub_total += $it['retotal'];
        }
        //平账
        $items[0]['retotal'] += $refund['refund_amount'] - $sub_total;

        foreach ($items as $_it) {
            $this->refund_list[] = $_it;
        }

        $this->refund_item[$refund['refund_id']] = count($items);
    }

    //sap402 退一种物料
    private function refundOne($refund)
    {
    	$tax = $this -> getGoodsTaxrate( $refund['order_id'], $refund['goods_id'] ) ;
    	if( 'platform' != $tax['manage_type'] && ($tax['tax_input'] == '200.00' || $tax['tax_output'] == '200.00') ) return false;
    	
    	$it = $this->refundCommon($refund);
        $it['tid'] = $refund['tid'] . '_0';//退款单号	主键
        $it['itemCode'] = $refund['goods_id'];//物料编号
        $it['retotal'] = $refund['refund_amount'];//退款金额
        $it['quantity'] = $refund['goods_num'];//退货：quantity>0  退款：quantity=0	退货数量
        $it['userFields']['U_TAX_RATE'] = $tax['tax_input']/100 ;
		// $it['userFields']['vatGroupPu'] = inputTax( $tax['tax_input'] ) ;
		// $it['userFields']['vatGourpSa'] = outputTax( $tax['tax_output'] ) ;
        $it['vatGourp']   = inputTax( $tax['tax_output'] );
        
        $this->refund_list[] = $it;
        $this->refund_item[$refund['refund_id']] = 1;
    }
    
    //查询商品税率
    private function getGoodsTaxrate($order_id, $goods_id)
    {
    	$condition['order_id'] = $order_id ;
    	$confition['goods_id'] = $goods_id ;
    	$row = Model('order_goods')->where($condition)->find();
        if ('platform' == $row['manage_type']) {
            $row['tax_output'] = $row['tax_output'] = 0;
        }
    	return $row ;
    }
    
    /** 404退款退货 应收贷项凭证接口 **/
    function credit($refund_ids=array())
    {
    	$code = $this->getCode(__CLASS__, __FUNCTION__);
    	$where['seller_state'] = '2';//商家同意
    	$where['refund_state'] = '3';//平台确认
    	$where['goods_id'] = array('gt',0);
    	$where['sap_return_credit'] = '0';//未推送

        //指定退款单重新推送,通过get参数传递
        if( !empty($_GET['refund_ids']) ) {
            $refund_ids = explode(',', $_GET['refund_ids']);
        }
        if( is_array($refund_ids) && !empty($refund_ids) ) {
            unset($where['sap_return_credit']);
            $where['refund_id'] = array('in', $refund_ids);
        }

    	$list = Model('refund_return')->where($where)->limit($this->getLimit($code))->select();
    	$order_info = array();
    	$order_ids = array_column($list, 'order_id');
    	if (count($order_ids) > 0) {
            $orders_cond = array(
                'order_id' => array('in', $order_ids), 
                'order_state' => array('in', array(0,40)),//已完结订单
            );
    		$orders = Model('orders')->field('order_id,manage_type,order_sn,buyer_name,payment_code,order_amount,fx_order_id,order_from')
    		->where($orders_cond)->select();
    		$order_info = array_under_reset($orders, 'order_id');
    	}
    	
    	//查询商品原始成本
    	$gids = array_column($list, 'goods_id') ;
    	//$g = Model('goods')->where(array('goods_id' => array('in', $gids)))->key('goods_id')->select();
    	//查询订单商品
    	$order_goods = $this -> getOrderGoods( $order_ids ) ;
    	
    	$refund_list = array() ;
    	foreach ((array)$list as $refund_info) {
    		if (empty($refund_info['order_id']) || empty($order_info[$refund_info['order_id']])) continue;
    		$goods_id = $refund_info['goods_id'] ;
    		$order_id = $refund_info['order_id'] ;
    		$ogInfo = $order_goods[ $order_id ][ $goods_id ] ;
    		$refund_info['order_info'] = $order_info[$refund_info['order_id']];
    		
    		$refund_amount = $refund_info['refund_amount_bill'] == -1?$refund_info['refund_amount']:$refund_info['refund_amount_bill'];
    		
            //退换佣金值
    		$commis_amount = $refund_info['order_info']['manage_type']=='platform' ?
    		  ncPriceFormat($refund_amount*$ogInfo['commis_rate']/100 ) : 0 ;
    		
            //退还的红包值(平台模式，且全额退款，红包才全额退还)
            // 期望方式，但目前不是这样计算的 ：sprintf("%.2f", ($refund_amount/$ogInfo['goods_pay_price'])*$ogInfo['rpt_amount'] );
            if ($refund_info['order_info']['manage_type']=='platform' && $refund_amount == $ogInfo['goods_pay_price']) {
                $rpt_amount = $ogInfo['rpt_bill'];
            } else {
                $rpt_amount = 0;
            }
            
            //平台模式，有税率的，异常退出
            if ($refund_info['order_info']['manage_type']=='platform' && $ogInfo['tax_output'] != 200 && $ogInfo['tax_output']>0) {
//                 continue;
            }
            //平台和共建/自营 的 最终成本价计算
            if ($refund_info['order_info']['manage_type']=='platform') {
                $ogInfo['tax_input'] = $ogInfo['tax_output'] = 0;
                $cost = $refund_amount - $commis_amount + $rpt_amount ; //总退还值
            } else {
                $cost =  ($refund_amount/$ogInfo['goods_pay_price'])*$ogInfo['goods_cost']; //总退还值
            }
            $cost = ncPriceFormat($cost);
    		
    		$item = array() ;
            //不抵扣供应商货款（平台承担），且不退货，才用虚拟物料
            $U_ORIN_PNOPAY = '1'; //目前全是抵扣供应商货款（商家承担）
            if (('0' == $U_ORIN_PNOPAY) && $refund_info['refund_type'] == 1) {
                $item['itemCode'] = 444444;
            } else {
                $item['itemCode'] = $goods_id ;
            }

    		$item['quantity'] = ($item['itemCode']==444444 || $refund_info['refund_type'] == 1)? 1 : $refund_info['goods_num'] ;
    		empty($item['quantity']) && $item['quantity'] = 1;
    		
    		$item['priceAfVat'] = ncPriceFormat($refund_amount / $item['quantity'], 4);//平台最终退款单品含税单价
    		$item['vatGroup'] = outputTax( $ogInfo['tax_output'] );//销项税码
    		$item['userFields']['U_INV1_COMSCALE'] = $refund_info['commis_rate'] ; //服务费比率

    		$item['userFields']['U_INV1_COMPRICE'] = $commis_amount; //服务费金额（含税）
            //商品结算成本（含税）
    		$item['userFields']['U_INV1_OITM_PUR'] = ncPriceFormat($cost / $item['quantity'] , 4)  ;
    		
    	//和sap对接的订单来源，决定自营仓库s
            $order_from = orderFrom($refund_info['order_info']['order_from']);
            //临时代码，集采订单的仓库对应来源
            if ('集采' == $order_from) {
                // $it['docDate'] = date('Y-m-d H:i:s', $v['add_time']);//集采订单按照拍单时间进sap
                
                //仓库对应调整
                $o2o_oids = array();
//                 $o2o_oids = array(160930154167080002,161014155158461003,161014182083812001,161014193029260001,161014194088971001,161014194426827001,161018175010249002,161018182620500003,161018184100354001,161018184746884001,161018185224413001,161019110559936001,161019111510077001,161024112405608001);
                //集采仓
                $jicai_oids = array();  
                //云创仓
                $yunchuang_oids = array();  
                //邮政仓
                $youzheng_oids = array(); 
                
                if (in_array($refund_info['order_info']['order_id'], $o2o_oids)) {
                    $order_from = 'O2O';
                } else if (in_array($refund_info['order_info']['order_id'], $jicai_oids)) {
                    $order_from = '集采';
                } else if (in_array($refund_info['order_info']['order_id'], $yunchuang_oids)) {
                    $order_from = '领导领用';
                } else {
                    $order_from = '集采';
                }

            } else if ('分销' == $order_from) {
                $order_from = '分销-' . getFxNameByUname($refund_info['order_info']['buyer_name']);
            }
    		
    		
    		$it = array() ;
    		$it['tid'] = $code . '_' . $refund_info['refund_id'];

    		$it['cardCode'] = $refund_info['store_id'] ;//客户代码
    		$it['cardName'] = $refund_info['store_name'] ;
    		$it['userFields']['U_OINV_ENUMBER'] = $refund_info['order_info']['order_sn'] ;//平台销售单号
    		$it['userFields']['U_ORPD_RETURN_NUMBER'] = $refund_info['refund_id'] ; //平台退款单号
    		$it['userFields']['U_ALL_Chorge'] = ncPriceFormat($commis_amount);  //服务费总额（含税）

            $it['userFields']['U_OINV_ONLINE'] = ncPriceFormat($refund_amount, 4); ;  //在线支付方式金额
            $it['userFields']['U_OINV_PRESTORE'] = ncPriceFormat(0) ;  //预存款支付方式金额
            $it['userFields']['U_OINV_RECHARGE'] = ncPriceFormat(0) ;  //充值卡支付方式金额
            
            $it['userFields']['U_OINV_SOURCE'] = $order_from ;  //订单来源
            
    		$it['userFields']['U_OPCH_REDPACK'] = $rpt_amount ;  //使用红包总额
    		$it['userFields']['U_OPCH_COST'] = $ogInfo['goods_cost']  ; //未用
            $it['userFields']['U_ORIN_PNOPAY'] = $U_ORIN_PNOPAY  ; //是否抵扣供应商货款
            $it['userFields']['U_OINV_ISRETURN'] = $refund_info['refund_type'] == 1 ? 'N' : 'Y'  ; //是否退货
    		$it['userFields']['U_PAY_DATE'] = date('Y-m-d H:i:s',$refund_info['add_time']);//完成支付日期
    		$it['userFields']['U_COMPLETE_DATE'] = date('Y-m-d H:i:s',$refund_info['admin_time']);//订单完成日期
    		$it['docLines'][] = $item;
    		$refund_list[] = $it ;
    	}
    	return $refund_list ;
    }

    
    function credit_after($success, $error, $exist='')
    {
    	$where['refund_id'] = array('in', $success);
    	Model('refund_return')->where($where)->update(array('sap_return_credit' => '1'));

        $where['refund_id'] = array('in', $exist);
        Model('refund_return')->where($where)->update(array('sap_return_credit' => '2'));
    	return true;
    }
    
    function credit_callback($success, $error, $exist='')
    {
    	$where['refund_id'] = array('in', $success);
    	Model('refund_return')->where($where)->update(array('sap_return_credit' => '2'));
    	 
    	//错误重推
    	$where['refund_id'] = array('in', $error);
    	Model('refund_return')->where($where)->update(array('sap_return_credit' => '0'));
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
    
    function storecost()
    {
    	$model_storecost = Model('store_cost');
    	$cost_condition['cost_price'] = array('gt', 0) ;
    	$cost_condition['cost_state'] = 0;
    	$cost_condition['fx_order_id'] = array('gt', 0) ;
    	$cost_condition['send_sap'] = 0;
    	$list = $model_storecost->where($cost_condition)->limit(20)->select();
    	
    	$store_ids = array_unique( array_column($list, 'cost_store_id') ) ;
    	/** @var storeModel $model_store **/
    	$model_store = Model('store') ;
    	$store_condition['store_id'] = array('in', $store_ids) ;
    	$stores = $model_store->field("store_name,store_id")->where( $store_condition )->key('store_id')->select();
    	$fx_oids = array_unique( array_column($list, 'fx_order_id') ) ;
    	/** @var orderModel $model_order **/
    	$model_order = Model("orders");
    	$order_condition = array();
    	$order_condition['fx_order_id'] = array('in',$fx_oids);
    	$orders = $model_order->field("order_sn,fx_order_id")->where($order_condition)->key('fx_order_id')->select();
    	 
    	$data = array();
    	foreach ($list as $storecost)
    	{
    		if( !isset($stores[$storecost['cost_store_id']]) || !isset($orders[$storecost['fx_order_id']]) ) continue;
    
    		$items = array() ;
    		$items['itemCode'] = 'KK00001' ;
    		$items['LineTotal'] = $storecost['cost_price'] ;
    		$items['quantity'] = 1 ;
    		$items['priceAfVat'] = $storecost['cost_price']  ;//平台最终与供应商结算单品含税单价
    
    		$list = array();
    		$list['tid'] = 'sap405_' . $storecost['cost_id'] ;
    		$list['cardCode'] 	= $storecost['cost_store_id'] ;
    		$list['cardName'] 	= $stores[$storecost['cost_store_id']]['store_name'] ;
    		$list['userFields']['U_OINV_ENUMBER'] = $orders[$storecost['fx_order_id']]['order_sn'] ;
    		$list['userFields']['U_ORPD_RETURN_NUMBER'] = "sc".$storecost['cost_id'] ;
    		$list['userFields']['U_PAY_DATE'] = date('Y-m-d H:i:s',$storecost['cost_time']);
    		$list['userFields']['U_COMPLETE_DATE'] = date('Y-m-d H:i:s',$storecost['cost_time']);
    		$list['docLines'][] = $items;
    
    		$data[] = $list ;
    	}
    	
    	return $data ;
    }
    
    public function storecost_after($success, $error, $exist='')
    {
    	$succ = $ext = array();
    	foreach ($success as $row) {
    		if( strpos($row, '_') === false ) { //店铺扣款回调
    			$succ['store_cost'][] = $row ;
    		} else { //退款修正回调
    			list($refund_id, $order_id) = explode("_", $row) ;
    			$succ['refund_return'][] = $refund_id;
    		}
    	}
    	foreach ($exist as $row) {
    		if( strpos($row, '_') === false ) { //店铺扣款回调
    			$ext['store_cost'][] = $row ;
    		} else { //退款修正回调
    			list($refund_id, $order_id) = explode("_", $row) ;
    			$ext['refund_return'][] = $refund_id;
    		}
    	}
    	
    	if( is_array($succ['store_cost']) && !empty($succ['store_cost']) ) {
    		$this->updateSendState($succ['store_cost'], 1, 'store_cost');//成功的标志改为1
    	}
    	if( is_array($succ['refund_return']) && !empty($succ['refund_return']) ) {
    		$this->updateSendState($succ['refund_return'], 1, 'refund_return');//成功的标志改为1
    	}
    
    	//已传送过标志改为2
    	if (!empty($exist)) {
    		if( is_array($ext['store_cost']) && !empty($ext['store_cost']) ) {
    			$this->updateSendState($ext['store_cost'], 2, 'store_cost');
    		}
    		if( is_array($ext['refund_return']) && !empty($ext['refund_return']) ) {
    			$this->updateSendState($ext['refund_return'], 2, 'refund_return');
    		}
    	}
    	return true;
    }
    
    public function storecost_callback($success, $error, $exist='')
    {
    	$succ = $err = array();
    	foreach ($success as $row) {
    		if( strpos($row, '_') === false ) { //店铺扣款回调
    			$succ['store_cost'][] = $row ;
    		} else { //退款修正回调
    			list($refund_id, $order_id) = explode("_", $row) ;
    			$succ['refund_return'][] = $refund_id;
    		}
    	}
    	foreach ($exist as $row) {
    		if( strpos($row, '_') === false ) { //店铺扣款回调
    			$err['store_cost'][] = $row ;
    		} else { //退款修正回调
    			list($refund_id, $order_id) = explode("_", $row) ;
    			$err['refund_return'][] = $refund_id;
    		}
    	}
    	
    	
    	if( is_array($succ['store_cost']) && !empty($succ['store_cost']) ) {
    		$this->updateSendState($succ['store_cost'], 2, 'store_cost');//成功的标志改为2
    	}
    	if( is_array($err['store_cost']) && !empty($err['store_cost']) ) {
    		$this->updateSendState($err['store_cost'], 0, 'store_cost');//失败的标志改为0 重新推
    	}
    	
    	if( is_array($succ['refund_return']) && !empty($succ['refund_return']) ) {
    		$this->updateSendState($succ['refund_return'], 2, 'refund_return');//成功的标志改为2
    	}
    	if( is_array($err['refund_return']) && !empty($err['refund_return']) ) {
    		$this->updateSendState($err['refund_return'], 0, 'refund_return');//失败的标志改为0 重新推
    	}
    	

    	return true;
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
    			$where['send_sap'] = '1';
    			break;
    		case 1:
    			$where['send_sap'] = '0';
    			break;
    		case 2:
    			// $where['send_sap'] = '1';
    			break;
    		default:
    			return true;
    	}
    	Model($table)->where($where)->update(array('send_sap' => $state));
    	return true;
    }
}