<?php

class order extends commons
{
    private $code = '';

    //sap301 推送已确认收货的订单
    public function add($oids=array())
    {
        $this->code = $this->getCode(__CLASS__, __FUNCTION__);
        $where['send_sap'] = '0';//未推送
        $where['order_state'] = '40';//已收货

        //指定订单重新推送,通过get参数传递
        if( !empty($_GET['oids']) ) {
            $oids = explode(',', $_GET['oids']);
        }

        if( is_array($oids) && !empty($oids) ) {
        	unset($where['send_sap']);
        	$where['order_id'] = array('in', $oids);
        }
        
        //指定账单重新推送,通过get参数传递
        if( isset($_GET['ob_id']) && !empty($_GET['ob_id']) ){
        	$ob_id = explode(',', $_GET['ob_id']);
        	$condition = array();
        	$condition['ob_id'] = array('in', $ob_id) ;
        	$list = Model('order_bill')->where($condition)->limit($this->getLimit('sap502'))->select();
        	 
        	/** @var orderModel $model_order **/
        	$model_order = Model('order');
        	$oids = array() ;
        	foreach ( $list as $bill_info ) {
        		$order_condition = array();
        		$order_condition['finnshed_time'] = array('between',"{$bill_info['ob_start_date']},{$bill_info['ob_end_date']}");
        		$order_condition['store_id'] = $bill_info['ob_store_id'];
        		$order_condition['send_sap'] = '0';//未推送
        		$order_list = $model_order->getOrderList($order_condition,'','order_id','order_id ASC', $this->getLimit('sap501_item'));
        		$tmp = array_column($order_list, 'order_id') ;
        		$oids = array_merge($oids, $tmp) ;
        	}
        	$where['order_id'] = array('in', $oids);
        }

        /*9月1号，过滤苏宁易购，人人优品的荆州门店==15*/
        $where['filter_status']='0';
        $list = Model('order')->getOrderList($where, '', '*', 'order_id', $this->getLimit($this->code), array('order_common', 'order_goods'));

        return $this->conversion($this->clean_add($list));
    }

    //sap301 推送成功后续操作
    public function add_after($success, $error, $exist='')
    {
        $this->updateSendState($success, 1);//成功的标志改为1
        $this->updateSendState($error, 1);  //失败的标志改为1，在一直收不到callback时，可靠定时脚本重置状态0
        //已传送过标志改为2
        if (!empty($exist)) {
            $this->updateSendState($exist, 2);//成功的标志改为1
        }
        return true;
    } 

    //sap301 回调函数
    public function add_callback($success, $error, $exist='')
    {
        $this->updateSendState($success, 2);//成功的标志改为2
        // $this->updateSendState($error, 0);//失败的标志改为0 重新推
        $this->updateSendState($error, 10);//失败的标志改为10 不再推送
        return true;
    }

    //修改推送标志
    private function updateSendState($ids, $state)
    {
        if (empty($ids)) return true;
        $where['order_id'] = array('in', $ids);
        switch ($state) {
            //失败状态，重置推送（即反复推送）
            case 0:
                $where['send_sap'] = '1';
                break;
            //失败状态，记录失败状态，不再推送
            case 10:
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
        Model('orders')->where($where)->update(array('send_sap' => $state));
        return true;
    }

    //整理推送的数据
    private function clean_add($list)
    {
        $new = $error = array();
        foreach ((array)$list as $v) {
            foreach ((array)$v['extend_order_goods'] as $item) {
                if ($item['commis_rate'] == 200|| //剔除 分佣未更新的订单
                    ($item['goods_cost']<=0&&$item['manage_type']=='co_construct') //剔除 成本价未更新的订单
                    //($item['tax_input']<=0&&$item['tax_output']<=0) //剔除 税率未更新的订单
//                    || ($item['tax_input'] == '200.000'&&$item['manage_type']=='co_construct')
                ) {
                    $error[] = $v['order_sn'];
                    continue 2;
                }
            }
            $new[] = $v;
        }
        if (!empty($error)) {
            $this->failed[] = array(
                'title' => '报警：来自 ' . $this->code . ' 报警信息',
                'msg' => '下列订单商品的佣金比例或者成本金额未更新<br>' . implode('<br>', $error),
            );
        }
        return $new;
    }

    private function conversion($list)
    {
        $data = array();
        $time = time();
        $express = rkcache('express', true);
        foreach ((array)$list as $v) {
            $it['tid'] = $time . '_' . $v['order_id'];//操作唯一标识符

            $it['cardCode'] = $v['store_id'];//店铺ID
            $it['cardName'] = $v['store_name'];//店铺名称
            // $it['docDate'] = date('Y-m-d H:i:s', $v['finnshed_time']);//确认完成时间
            
            //和sap对接的订单来源，决定自营仓库s
            $order_from = orderFrom($v['order_from']);
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
                
                if (in_array($v['order_id'], $o2o_oids)) {
                    $order_from = 'O2O';
                } else if (in_array($v['order_id'], $jicai_oids)) {
                    $order_from = '集采';
                } else if (in_array($v['order_id'], $yunchuang_oids)) {
                    $order_from = '领导领用';
                } else {
                    $order_from = '集采';
                }

            } else if ('分销' == $order_from) {
                $order_from = '分销-' . getFxNameByUname($v['buyer_name']);
            }
            
            //$it['docTotal'] = $v['order_amount'];//订单金额（元）
            $it['userFields'] = array(
                'U_OINV_ENUMBER' => $v['order_sn'],//订单编号
                'U_PAY_DATE' => date('Y-m-d H:i:s', $v['add_time']),//支付日期
                'U_COMPLETE_DATE' => date('Y-m-d H:i:s', $v['finnshed_time']),//平台完成日期
                'U_OINV_PAYNUMBER' => $v['pay_sn'],//支付单号
                'U_OINV_SOURCE' => $order_from,//订单来源
                'U_OINV_ONLINE' => $this->get_online_amount($v),//在线支付方式金额
                'U_OINV_PRESTORE' => $v['pd_amount'],//预存款支付方式金额
                'U_OINV_RECHARGE' => $v['rcb_amount'],//充值卡支付方式金额
                'U_OINV_FAVORABLE' => $v['rpt_bill'],//使用优惠券总额
                'U_OPCH_REDPACK' => $v['rpt_bill'],//使用红包总额
                'U_OINV_PAYDATE' => $v['finnshed_time'] > 0 ? date('Y-m-d H:i:s', $v['finnshed_time']) : '',//支付时间
                'U_OINV_CUSTOMER' => $v['buyer_name'], //$v['extend_order_common']['reciver_name'],//买家
                'U_OINV_DRAWER' => !empty($v['extend_order_common']['invoice_info']['抬头']) ? $v['extend_order_common']['invoice_info']['抬头'] : '',//发票抬头
                'U_OINV_TEL' => $this->strip_mobile($v['extend_order_common']['reciver_info']['mob_phone']),//联系方式
                'U_OINV_ADDRESS' => $v['extend_order_common']['reciver_info']['address'],//收货地址
                'U_OINV_EXPCOMPANY' => $express[$v['extend_order_common']['shipping_express_id']]['e_name'],//快递公司
                'U_OINV_EXPNUMBER' => $v['shipping_code'],//物流单号
                'U_OINV_YF' => $v['shipping_fee'],//整单运费
            );
            $it['docLines'] = array();
            $commis_all = 0;
            //平台订单，要考虑运费，商品结算成本含运费
            $item_shipping_fee = $v['shipping_fee']/count($v['extend_order_goods']);
            foreach ((array)$v['extend_order_goods'] as $item) {
                // $real_pay_price = $item['goods_pay_price'] -$item['rpt_amount'];
                $comprice = 0;
                //如果是平台商品，而且是赠品商品(goods_type=5)，则商品行不传
                if ($item['manage_type']=='platform' && $item['goods_type']==5) {
                    continue;
                }

                if($item['tax_input'] == '200.000'&&$item['manage_type']=='co_construct'){
                    //刷新订单商品税率
                    $goods_info = Model('goods')->getGoodsInfoByID($item['goods_id']);
                    $tmp = array();
                    $tmp['tax_input'] = $goods_info['tax_input'];
                    $tmp['tax_output'] = $goods_info['tax_output'];

                    $condition = array();
                    $condition['order_id'] = $item['order_id'] ;
                    $condition['goods_id'] = $item['goods_id'] ;
                    Model('order')->editOrderGoods($tmp,$condition);
                    //更新商品税率，跳过这个订单
                    continue 2;
                }

                if ($item['manage_type']=='platform') {
                    // 调整佣金计算方式
                    // 确认收货时佣金计算以新的方式计算
                    $comprice = ncPriceFormat(($item['goods_pay_price']+$item['rpt_bill']) * $item['commis_rate'] / 100);

                    //订单商品行成本：如果是平台的加上红包，且goods_cost字段与结算单成本计算一致。
                    //不使用原本数据库的googs_cost字段    
                    // $item['goods_cost'] =  $item['goods_cost'] + $item['rpt_bill']; //错误写法
                    $item['goods_cost'] =  ncPriceFormat($item['goods_pay_price'] + $item_shipping_fee - $comprice + $item['rpt_bill']);
                    $item['tax_output'] = $item['tax_input'] = 0;   //平台的税率为0
                }

                $real_pay_price = $item['goods_pay_price'];
                $it['docLines'][] = array(
                    'itemCode' => $item['goods_id'],//B1物料编号
                    'priceAfVat' => ncPriceFormat(($real_pay_price+$item_shipping_fee) / $item['goods_num'], 4),//物料单价
                    'quantity' => $item['goods_num'],//物料数量
                    'sub_amount' => ncPriceFormat($real_pay_price) ,//实际支付总价
                    'vatGroup' => outputTax( $item['tax_output'] ),

                    'userFields' => array(
                        'U_INV1_COMSCALE' => $item['commis_rate'] / 100,//佣金比例
                        //收取佣金
                        'U_INV1_COMPRICE' => ncPriceFormat($comprice),
                        //商品成本单价金额
                        'U_INV1_OITM_PUR' => ncPriceFormat($item['goods_cost'] / $item['goods_num'], 4),
                    	//税码
                        'U_TAX_RATE' 	  => $item['tax_output']/100,

                        'U_INV1_REDPACK'      => $item['rpt_bill'],   //行红包总计
                        'U_INV1_COUPON'      => '0',   //行优惠券总计
                    )
                );
                $commis_all += $comprice;//收取佣金
            }

            $it['userFields']['U_ALL_Chorge'] = strval($commis_all);//整单服务费（总佣金）

            $data[] = $it;
        }
        return $data;
    }

    //计算在线支付金额
    private function get_online_amount($order)
    {
        $amount = 0;
        switch ($order['payment_code']) {
            case 'offline':
                break;
            default:
                $amount = $order['order_amount'] - $order['rcb_amount'] - $order['pd_amount'];
                break;
        }
        return $amount;
    }

    public function strip_mobile($mobile) {
        $search = array(',', '，', ';','；');
        $replace = ' ';
        $mobile = str_replace($search, $replace, trim($mobile));
        $mobile = explode(' ', $mobile);
        return $mobile[0];
    }

}