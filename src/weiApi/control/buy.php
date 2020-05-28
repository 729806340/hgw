<?php
/**
 * 购买
 *
 *
 *
 *
 * @copyright  Copyright (c) 2007-2015 ShopNC Inc. (http://www.shopnc.net)
 * @license    http://www.shopnc.net
 * @link       http://www.shopnc.net
 * @since      File available since Release v1.1
 */


defined('ByShopWWI') or exit('Access Invalid!');

class buyControl extends mobileMemberControl
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 购物车、直接购买第一步:选择收获地址和配置方式
     */
    public function step1Op()
    {
        $cart_id = explode(',', $_POST['cart_id']);

        //得到会员等级
        $model_member = Model('member');
        $member_info = $model_member->getMemberInfoByID($this->member_info['member_id']);

        if (empty($member_info)) {
            output_error('请先登录');
        }
        $member_gradeinfo = $model_member->getOneMemberGrade(intval($member_info['member_exppoints']));
        $member_discount = $member_gradeinfo['orderdiscount'];
        $member_level = $member_gradeinfo['level'];

        $chain_info = array();
        //得到购买数据
        /** @var buyLogic $logic_buy */
        $logic_buy = logic('buy');
        if ($_POST['chain_id']) {
            /** @var chainModel $chain_model */
            $chain_model = Model('chain');
            $chain_info = $chain_model->getChainInfo(array('chain_id' => $_POST['chain_id']));
            if (empty($chain_info) || $chain_info['store_id'] == $this->member_info['store_id']) {
                output_error('不能购买自己店铺的商品');
            }
            $result = $logic_buy->chainBuyStep1($cart_id, $this->member_info['member_id']);
        } else {
            $result = $logic_buy->buyStep1($cart_id, $_POST['ifcart'], $this->member_info['member_id'], $this->member_info['store_id'], null, $member_discount, $member_level);
        }

        if (!$result['state']) {
            output_error($result['msg']);
        } else {
            $result = $result['data'];
        }

        //整理数据
        $store_cart_list = array();
        $sum = 0;
        $goods_rpt_list = array();
        foreach ($result['store_cart_list'] as $key => $value) {
            $store_cart_list[$key]['goods_list'] = array();
            $goods_rpt_list = $goods_rpt_list + array_under_reset($value, 'goods_id');
            foreach ($value as $k => $v) {
                if (empty($v['xianshi_info'])) $v['xianshi_info'] = array('xianshi_name' => '');
                $store_cart_list[$key]['goods_list'][] = $v;
            }
            //$store_cart_list[$key]['goods_list'] = $value;
            $store_cart_list[$key]['store_goods_total'] = $result['store_goods_total'][$key];
            $store_cart_list[$key]['store_goods_num_total'] = array_sum(array_column($value, 'goods_num'));
            if (!empty($result['store_premiums_list'][$key])) {
                $result['store_premiums_list'][$key][0]['premiums'] = true;
                $result['store_premiums_list'][$key][0]['goods_total'] = 0.00;
                $store_cart_list[$key]['goods_list'][] = $result['store_premiums_list'][$key][0];
            }
            $store_cart_list[$key]['store_mansong_rule_list'] = $result['store_mansong_rule_list'][$key];
            $store_cart_list[$key]['store_voucher_list'] = $result['store_voucher_list'][$key];
            $store_cart_list[$key]['store_voucher_list_all'] = Model('voucher')->getStoreVoucherList($key, $this->member_info['member_id'], 10, $store_cart_list[$key]['store_goods_total']);
            $store_cart_list[$key]['store_voucher_info'] = array();
            if ($store_cart_list[$key]['store_voucher_list']) {
                $store_cart_list[$key]['store_voucher_info'] = array('voucher_price' => $store_cart_list[$key]['store_voucher_list']['1']['voucher_price']);
            }
            if (!empty($result['cancel_calc_sid_list'][$key])) {
                $store_cart_list[$key]['freight'] = '0';
                $store_cart_list[$key]['freight_message'] = $result['cancel_calc_sid_list'][$key]['desc'];
            } else {
                $store_cart_list[$key]['freight'] = '1';
            }
            $store_cart_list[$key]['store_name'] = $value[0]['store_name'];
            $store_cart_list[$key]['store_id'] = $key;
            $sum += $store_cart_list[$key]['store_goods_total'];
            //总金额减去满减的金额
            if (isset($result['store_mansong_rule_list'][$key]['discount']) && $result['store_mansong_rule_list'][$key]['discount'] > 0) {
                $sum-= $result['store_mansong_rule_list'][$key]['discount'];
            }
        }

        $buy_list = array();
        $buy_list['freight_hash'] = $result['freight_list'];
        $buy_list['address_info'] = empty($result['address_info']) ? array(
            'address_id' => '0',
            "member_id"  => "",
            "true_name"  => "",
            "area_id"    => "",
            "city_id"    => "",
            "area_info"  => "",
            "address"    => "",
            "tel_phone"  => "",
            "mob_phone"  => "",
            "is_default" => "0",
            "dlyp_id"    => "0"
        ) : $result['address_info'];
        $buy_list['ifshow_offpay'] = $result['ifshow_offpay'];
        $buy_list['vat_hash'] = $result['vat_hash'];
        $buy_list['inv_info'] = $result['inv_info'];
        $buy_list['available_predeposit'] = ncPriceFormat($result['available_predeposit']);
        $buy_list['available_rc_balance'] = ncPriceFormat($result['available_rc_balance']);
        if (is_array($result['rpt_list']) && !empty($result['rpt_list'])) {
            foreach ($result['rpt_list'] as $k => $value) {
                if ($value['rpacket_range'] > 0) {
                    if(empty($value['rpacket_skus'])) {
                        unset($result['rpt_list'][$k]);
                        continue;
                    }
                    $skuArray = explode(',',$value['rpacket_skus']);
                    $rpt_limit_sum = 0;
                    foreach ($goods_rpt_list as $v1){
                        if(in_array($v1['goods_id'], $skuArray) === ($value['rpacket_range']==1)){
                            $rpt_limit_sum += $v1['goods_price']*$v1['goods_num'];
                        }
                    }
                    $rpt_limit_sum = ncPriceFormat($rpt_limit_sum);
                    if($rpt_limit_sum<$value['rpacket_limit']) {
                        unset($result['rpt_list'][$k]);
                        continue;
                    }
                }

                unset($result['rpt_list'][$k]['rpacket_id']);
                unset($result['rpt_list'][$k]['rpacket_end_date']);
                unset($result['rpt_list'][$k]['rpacket_owner_id']);
                unset($result['rpt_list'][$k]['rpacket_code']);
            }
        }
        $buy_list['rpt_list'] = $result['rpt_list'] ? array_values($result['rpt_list']) : array();
        $buy_list['zk_list'] = $result['zk_list'];
        $buy_list['order_amount'] = $sum;
        $buy_list['rpt_info'] = '';
        if (empty($chain_info)) {
            $address_city_id = $buy_list['address_info']['city_id'] ? $buy_list['address_info']['city_id'] : 1;
            $address_area_id = $buy_list['address_info']['area_id'] ? $buy_list['address_info']['area_id'] : 1;
            $buy_list['address_api'] = $logic_buy->changeAddr($result['freight_list'], $address_city_id, $address_area_id, $this->member_info['member_id']);
        } else {
            $buy_list['address_api'] = array(
                'content' => array($chain_info['store_id'] => 0),
            );
            $buy_list['address_info'] = array(
                'address_id' => '0',
                "member_id"  => $this->member_info['member_id'],
                "true_name"  => $this->member_info['member_name'],
                "area_id"    => $chain_info['area_id'],
                "city_id"    => $chain_info['area_id_2'],
                "area_info"  => $chain_info['area_info'],
                "address"    => $chain_info['chain_name'].'('.$chain_info['chain_address'].')',
                "tel_phone"  => "",
                "mob_phone"  => "",
                "is_default" => "0",
                "dlyp_id"    => "0",
                'lay_x'      => $chain_info['longitude'],
                'lay_y'      => $chain_info['latitude']
            );
        }
        $buy_list['chain_info'] = $chain_info;
        //$buy_list['store_final_total_list'] = array('1' => ncPriceFormat($sum));

        foreach ($store_cart_list as $store_id => $storeCart) {
            $store_cart_list[$store_id]['freight'] = $buy_list['address_api']['content'][$store_id];
            $buy_list['order_amount'] += $buy_list['address_api']['content'][$store_id];
        }
        $storeCartList = array();
        foreach ($store_cart_list as $store_cart) {
            $store_cart['store_voucher_list'] = array_values($store_cart['store_voucher_list']);
            $storeCartList[] = $store_cart;
        }
        $buy_list['offpay_hash'] = $buy_list['address_api']['offpay_hash'];
        $buy_list['offpay_hash_batch'] = $buy_list['address_api']['offpay_hash_batch'];
        $buy_list['store_cart_list'] = $storeCartList;

        if ($_POST['tuan_id'] > 0) {//拼团
            $tuan_id = intval($_POST['tuan_id']);
            /** @var p_pintuan_memberModel $p_pintuan_member_model */
            $p_pintuan_member_model = Model('p_pintuan_member');
            $tuan_user_list = $p_pintuan_member_model->getMemberList(array('tuan_id' => $tuan_id), null, 'pintuan_member_id asc');
            $buy_list['tuan_user_list'] = $tuan_user_list;
        }

        output_data($buy_list);
    }

    /**
     * 购物车、直接购买第二步:保存订单入库，产生订单号，开始选择支付方式
     *
     */
    public function step2Op()
    {

        $param = array();
        $param['ifcart'] = $_POST['ifcart'];
        $param['cart_id'] = is_array($_POST['cart_id']) ? $_POST['cart_id'] : explode(',', $_POST['cart_id']);
        $param['address_id'] = $_POST['address_id'];
        $param['vat_hash'] = $_POST['vat_hash'];
        $param['offpay_hash'] = $_POST['offpay_hash'];
        $param['offpay_hash_batch'] = $_POST['offpay_hash_batch'];
        //$param['pay_name'] = $_POST['pay_name'];
        $param['pay_name'] = 'online';
        $param['invoice_id'] = $_POST['invoice_id'];
        $param['rpt'] = $_POST['rpt'];
        $param['is_wei_chain'] = $_POST['is_wei_chain'];
        $param['is_pintuan'] = $_POST['is_pintuan'];
        $param['tuan_id'] = $_POST['tuan_id'];
        $param['chain'] = $_POST['chain_id'] ? array('id'=> intval($_POST['chain_id']), 'buyer_name' => '门店自提用户') : array();

        //处理代金券
        $voucher = array();
        $post_voucher = explode(',', $_POST['voucher']);
        if (!empty($post_voucher)) {
            foreach ($post_voucher as $value) {
                list($voucher_t_id, $store_id, $voucher_price) = explode('|', $value);
                $voucher[$store_id] = $value;
            }
        }
        $param['voucher'] = $voucher;

        //手机端暂时不做支付留言，页面内容太多了
        //$param['pay_message'] = json_decode($_POST['pay_message']);
        $post_pay_message = explode(',', $_POST['pay_message']);
        if (!empty($post_pay_message)) {
            foreach ($post_pay_message as $value) {
                list($store_id, $message) = explode('|', $value);
                $pay_message[$store_id] = $message;
            }
        }
        $param['pay_message'] = $pay_message;

        $param['pd_pay'] = $_POST['pd_pay'];
        $param['rcb_pay'] = $_POST['rcb_pay'];
        $param['password'] = $_POST['password'];
        $param['fcode'] = $_POST['fcode'];
        $_POST['pyramid_goods'] = $_POST['pyramid_goods'] ? html_entity_decode($_POST['pyramid_goods']) : "";
        $param['pyramid_goods'] = $_POST['pyramid_goods'] ? json_decode($_POST['pyramid_goods'], true) : array();
        $param['order_from'] = ($_POST['order_from'] == 7) ? 7 : 6;
        /** @var buyLogic $logic_buy */
        $logic_buy = logic('buy');

        //得到会员等级
        $model_member = Model('member');
        $member_info = $model_member->getMemberInfoByID($this->member_info['member_id']);
        if ($member_info) {
            $member_gradeinfo = $model_member->getOneMemberGrade(intval($member_info['member_exppoints']));
            $member_discount = $member_gradeinfo['orderdiscount'];
            $member_level = $member_gradeinfo['level'];
        } else {
            $member_discount = $member_level = 0;
        }
        $this->testLog($param);
        $result = $logic_buy->buyStep2($param, $this->member_info['member_id'], $this->member_info['member_name'], $this->member_info['member_email'], $member_discount, $member_level);
        if (!$result['state']) {
            output_error($result['msg']);
        }
        output_data(array('pay_sn' => $result['data']['pay_sn']));
    }

    /**
     * 验证密码
     */
    public function verify_passwordOp()
    {
        if (empty($_POST['password'])) {
            output_error('参数错误');
        }

        $model_member = Model('member');

        $member_info = $model_member->getMemberInfoByID($this->member_info['member_id']);
        if ($member_info['member_paypwd'] == md5($_POST['password'])) {
            output_data('1');
        } else {
            output_error('密码错误');
        }
    }

    public function rcb_payOp()
    {

        if (empty($_POST['password'])) {
            output_error('参数错误');
        }

        $pay_sn = $_POST['pay_sn'];
        if (!preg_match('/^\d{18}$/', $pay_sn)) {
            output_error('支付单号错误');
        }

        //----添加密码判断
        $buyer_info = Model('member')->getMemberInfoByID($this->member_info['member_id']);
        if ($buyer_info['member_paypwd'] == '') output_error('未设置支付密码');
        if ($buyer_info['member_paypwd'] != md5($_POST['password'])) output_error('支付密码错误');
        //----end

        $pay_info = $this->_get_real_order_info($pay_sn, array('rcb_pay' => 1, 'password' => $_POST['password']));
        if (isset($pay_info['error'])) {
            output_error($pay_info['error']);
        }
        output_data(1);
    }

    private function _get_real_order_info($pay_sn, $rcb_pd_pay = array())
    {

        $logic_payment = Logic('payment');
        //取订单信息
        $result = $logic_payment->getRealOrderInfo($pay_sn, $this->member_info['member_id']);
        if (!$result['state']) {
            return array('error' => $result['msg']);
        }

        $orderAmount = 0;
        if (!empty($result['data']['order_list'])) {
            foreach ($result['data']['order_list'] as $order_info) {
                if ($order_info['order_state'] == ORDER_STATE_NEW) {
                    $orderAmount += $order_info['order_amount'] - $order_info['pd_amount'] - $order_info['rcb_amount'];
                }
            }
        }
        //充值卡支付
        if ($rcb_pd_pay['rcb_pay']) {
            //----判断充值卡余额是否足够支付订单
            $buyer_info = Model('member')->getMemberInfoByID($this->member_info['member_id']);
            if ($buyer_info['available_rc_balance'] < $orderAmount) return array('error' => '充值卡余额不足');
            //----end
            $result['data']['order_list'] = $this->_rbc_pay($result['data']['order_list'], $rcb_pd_pay);
        }

        //计算本次需要在线支付的订单总金额
        $pay_amount = 0;
        $pay_order_id_list = array();
        if (!empty($result['data']['order_list'])) {
            foreach ($result['data']['order_list'] as $order_info) {
                if ($order_info['order_state'] == ORDER_STATE_NEW) {
                    $pay_amount += $order_info['order_amount'] - $order_info['pd_amount'] - $order_info['rcb_amount'];
                    $pay_order_id_list[] = $order_info['order_id'];
                }
            }
        }

        if ($pay_amount == 0) {
            return array();
        }

        return array('error' => '订单部分支付完成。');

    }

    //微信端不验证支付密码
    private function _rbc_pay($order_list, $post)
    {
        /*if (empty($post['password'])) {
            return $order_list;
        }*/
        /** @var memberModel $model_member */
        $model_member = Model('member');
        $buyer_info = $model_member->getMemberInfoByID($this->member_info['member_id']);
        /*if ($buyer_info['member_paypwd'] == '' || $buyer_info['member_paypwd'] != md5($post['password'])) {
            return $order_list;
        }*/

        if ($buyer_info['available_rc_balance'] == 0) {
            $post['rcb_pay'] = null;
        }
        if ($buyer_info['available_predeposit'] == 0) {
            $post['pd_pay'] = null;
        }
        if (floatval($order_list[0]['rcb_amount']) > 0 || floatval($order_list[0]['pd_amount']) > 0) {
            return $order_list;
        }

        try {
            $model_member->beginTransaction();
            /** @var buy_1Logic $logic_buy_1 */
            $logic_buy_1 = Logic('buy_1');
            //使用充值卡支付
            if (!empty($post['rcb_pay'])) {
                $order_list = $logic_buy_1->rcbPay($order_list, $post, $buyer_info);
            }

            //使用预存款支付
            if (!empty($post['pd_pay'])) {
                $order_list = $logic_buy_1->pdPay($order_list, $post, $buyer_info);
            }

            //特殊订单站内支付处理
            $logic_buy_1->extendInPay($order_list);

            $model_member->commit();
        } catch (Exception $e) {
            $model_member->rollback();
            exit($e->getMessage());
        }

        return $order_list;
    }


    /**
     * 更换收货地址
     */
    public function change_addressOp()
    {
        $logic_buy = Logic('buy');
        if (empty($_POST['city_id'])) {
            $_POST['city_id'] = $_POST['area_id'];
        }

        $data = $logic_buy->changeAddr($_POST['freight_hash'], $_POST['city_id'], $_POST['area_id'], $this->member_info['member_id']);
        if (!empty($data) && $data['state'] == 'success') {
            output_data($data);
        } else {
            output_error('地址修改失败');
        }
    }


    /**
     * 支付方式
     */
    public function payOp()
    {
        $pay_sn = $_POST['pay_sn'];
        $condition = array();
        $condition['pay_sn'] = $pay_sn;
        $order_info = Model('order')->getOrderInfo($condition);
        //$payment_list = Model('mb_payment')->getMbPaymentList(array(), 'payment_id,payment_code,payment_name,payment_state');
        /** @var PingppService $pingxx */
        $pingpp = Service('Pingpp');
        $payment_list = $pingpp->getChannel();


        $pay_info['pay_amount'] = $order_info['order_amount'];
        $pay_info['member_available_pd'] = $this->member_info['available_predeposit'];
        $pay_info['member_available_rcb'] = $this->member_info['available_rc_balance'];

        $pay_info['member_paypwd'] = true;
        if (empty($this->member_info['member_paypwd'])) {
            $pay_info['member_paypwd'] = false;
        }

        $pay_info['pay_sn'] = $order_info['pay_sn'];
        $pay_info['payed_amount'] = $order_info['pd_amount'];
        if ($pay_info['payed_amount'] > '0.00') {
            $pay_info['pay_amount'] = $pay_info['pay_amount'] - $pay_info['payed_amount'];
        }

        $pay_in["pay_info"] = $pay_info;
        $pay_in["pay_info"]["payment_list"] = $payment_list;
        output_data($pay_in);
    }

    /**
     * 获取支付对象
     */
    public function chargeOp()
    {
        /*
        \Pingpp\Charge::create(array(
            'order_no'  => '123456789',
            'amount'    => '100',
            'app'       => array('id' => 'app_1Gqj58ynP0mHeX1q'),
            'channel'   => 'upacp',
            'currency'  => 'cny',
            'client_ip' => '127.0.0.1',
            'subject'   => 'Your Subject',
            'body'      => 'Your Body'
        ));*/
        if (!isset($_POST['pay_sn'], $_POST['amount'], $_POST['channel'])) output_error('参数错误！');
        $param = array(
            'order_no' => $_POST['pay_sn'],
            'amount'   => intval(100 * $_POST['amount']),
            'channel'  => $_POST['channel'],
            'subject'  => $_POST['pay_sn'],
            'body'     => $_POST['pay_sn'],
            'metadata' => array('order_type' => 'real_order'),
        );
        /** @var PingppService $pingpp */
        $pingpp = Service('Pingpp');
        $charge = $pingpp->create($param);

        //echo json_encode(json_decode($charge,true));
        //exit();
        echo json_encode(array(
            'code'  => 200,
            'datas' => array(
                'charge' => json_decode($charge, true)
            )));
        exit();
    }


    /**
     * 获取支付状态
     */
    public function pay_stateOp()
    {
        $sn = $_POST['pay_sn'];
        if (empty($sn)) output_error('支付单号不得为空');

        /** @var orderModel $orderModel */
        $orderModel = Model('order');
        $pay = $orderModel->getOrderPayInfo(array('pay_sn' => $sn));
        if (empty($pay)) output_error('找不到支付单');
        output_data($pay);
        //return $pay;
    }

    /**
     * 支付密码确认
     */
    public function check_pd_pwdOp()
    {
        if ($this->member_info['member_paypwd'] != md5($_POST['password'])) {
            output_error('支付密码错误');
        } else {
            output_data('OK');
        }
    }

    /**
     * @return array
     */
    public function prepay_idOp() {

        $result = array(
            'pay_info' => array(),
            'pay_ok' => false,
            'order_info' => array(),
        );
        $pay_sn = $_POST['pay_sn'];
        $open_id = $_POST['open_id'];
        //$open_id = $this->member_info['open_id'];
        if (!$pay_sn || !$open_id) {
            output_error('参数错误');
        }
        /** @var orderModel $model_order */
        $model_order = Model('order');

        /** @var paymentLogic $logic_payment */
        $logic_payment = Logic('payment');
        //取订单信息
        $order_pay_info = $logic_payment->getRealOrderInfo($pay_sn, $this->member_info['member_id']);
        if (!$order_pay_info['state']) {
            return array('error' => $order_pay_info['msg']);
        }
        //$_POST rcb_pay = 1  充值卡 pd_pay  = 1 预存款
        //站内余额支付
        $order_list = $this->_rbc_pay($order_pay_info['data']['order_list'], $_POST);

        //计算本次需要在线支付（分别是含站内支付、纯第三方支付接口支付）的订单总金额
        $pay_amount = 0;
        $api_pay_amount = 0;
        $pay_order_id_list = array();
        $order_add_time =  date('Y-m-d H:i:s', time());
        $order_last_id = 0;
        if (!empty($order_list)) {
            foreach ($order_list as $order_info) {
                if ($order_info['order_state'] == ORDER_STATE_NEW) {
                    $api_pay_amount += $order_info['order_amount'] - $order_info['pd_amount'] - $order_info['rcb_amount'];
                    $pay_order_id_list[] = $order_info['order_id'];
                }
                $order_add_time = date('Y-m-d H:i:s', $order_info['add_time']);
                $order_last_id = $order_info['order_id'];
                $pay_amount += $order_info['order_amount'];
            }
        }
        $result['order_info'] = array(
            'pay_amount' => $pay_amount,
            'pay_type' => '线上支付',
            'pay_sn' => $pay_sn,
            'pay_time' => $order_add_time,
            'pay_order_id' => $order_last_id,
        );
        if (empty($api_pay_amount)) {
            $result['pay_ok'] = true;
            output_data($result);
        }

        $s1 = $model_order->editOrder(array('api_pay_time' => TIMESTAMP), array('order_id' => array('in', $pay_order_id_list)));
        if (!$s1) {
            output_error('更新订单信息发生错误，请重新支付');
        }

        //$order_pay_info['data']['api_pay_amount'] = ncPriceFormat($api_pay_amount);

        //如果是开始支付尾款，则把支付单表重置了未支付状态，因为支付接口通知时需要判断这个状态
        if ($order_pay_info['data']['if_buyer_repay']) {
            $update = $model_order->editOrderPay(array('api_pay_state' => 0), array('pay_id' => $order_pay_info['data']['pay_id']));
            if (!$update) {
                output_error('订单支付失败');
            }
        }

        $total_pay_money = ncPriceFormat($api_pay_amount);

        $inc_file = BASE_PATH.DS.'api'.DS.'payment'.DS.'wxpay_jsapi'.DS.'wxpay_jsapi'.'.php';
        if(!is_file($inc_file)){
            output_error('支付接口不存在');
        }
        require($inc_file);

        /** @var mb_paymentModel $model_mb_payment */
        $model_mb_payment = Model('mb_payment');
        $mb_payment_info = $model_mb_payment->getMbPaymentOpenInfo(array('payment_code' => 'wxpay_jsapi'));
        if(!$mb_payment_info) {
            output_error('支付方式未开启');
        }
        $param = $mb_payment_info['payment_config'];
        $param['orderSn'] = $pay_sn;
        $param['orderFee'] = (int) (bcmul(100, $total_pay_money));
        $param['orderInfo'] = C('site_name') . '商品订单' . $order_pay_info['pay_sn'];
        $param['orderAttach'] = 'r';
        $api = new wxpay_jsapi();
        $param['appId'] = 'wx66d8e5f039ce2822';
        $param['appSecret'] = '0312929405daaedda4d1956da8c247c6';
        $api->setConfigs($param);
        try {
            $result['pay_info'] = $api->paymentSmall($this, $open_id);
        } catch (Exception $ex) {
            output_error($ex->getMessage());
        }
        output_data($result);
    }

    /**
     * @return array
     */
    public function douyin_prepay_idOp() {
        output_error('暂未开启');
        $result = array(
            'pay_info' => array(),
            'pay_ok' => false,
            'order_info' => array(),
        );
        $pay_sn = $_POST['pay_sn'];
        $open_id = $_POST['open_id'];
        if (!$pay_sn || !$open_id) {
            output_error('参数错误');
        }
        /** @var orderModel $model_order */
        $model_order = Model('order');

        /** @var paymentLogic $logic_payment */
        $logic_payment = Logic('payment');
        //取订单信息
        $order_pay_info = $logic_payment->getRealOrderInfo($pay_sn, $this->member_info['member_id']);
        if (!$order_pay_info['state']) {
            return array('error' => $order_pay_info['msg']);
        }
        //$_POST rcb_pay = 1  充值卡 pd_pay  = 1 预存款
        //站内余额支付
        $order_list = $this->_rbc_pay($order_pay_info['data']['order_list'], $_POST);

        //计算本次需要在线支付（分别是含站内支付、纯第三方支付接口支付）的订单总金额
        $pay_amount = 0;
        $api_pay_amount = 0;
        $pay_order_id_list = array();
        $order_add_time =  date('Y-m-d H:i:s', time());
        $order_last_id = 0;
        if (!empty($order_list)) {
            foreach ($order_list as $order_info) {
                if ($order_info['order_state'] == ORDER_STATE_NEW) {
                    $api_pay_amount += $order_info['order_amount'] - $order_info['pd_amount'] - $order_info['rcb_amount'];
                    $pay_order_id_list[] = $order_info['order_id'];
                }
                $order_add_time = date('Y-m-d H:i:s', $order_info['add_time']);
                $order_last_id = $order_info['order_id'];
                $pay_amount += $order_info['order_amount'];
            }
        }
        $result['order_info'] = array(
            'pay_amount' => $pay_amount,
            'pay_type' => '线上支付',
            'pay_sn' => $pay_sn,
            'pay_time' => $order_add_time,
            'pay_order_id' => $order_last_id,
        );
        if (empty($api_pay_amount)) {
            $result['pay_ok'] = true;
            output_data($result);
        }

        $s1 = $model_order->editOrder(array('api_pay_time' => TIMESTAMP), array('order_id' => array('in', $pay_order_id_list)));
        if (!$s1) {
            output_error('更新订单信息发生错误，请重新支付');
        }

        //$order_pay_info['data']['api_pay_amount'] = ncPriceFormat($api_pay_amount);

        //如果是开始支付尾款，则把支付单表重置了未支付状态，因为支付接口通知时需要判断这个状态
        if ($order_pay_info['data']['if_buyer_repay']) {
            $update = $model_order->editOrderPay(array('api_pay_state' => 0), array('pay_id' => $order_pay_info['data']['pay_id']));
            if (!$update) {
                output_error('订单支付失败');
            }
        }
        $total_pay_money = ncPriceFormat($api_pay_amount);
        $douyin_config = C('douyin');
        $result['pay_info'] = array(
            'merchant_id' => $douyin_config['merchant_id'],
            'app_id' => $douyin_config['merchant_app_id'],
            'sign_type' => 'MD5',
            'timestamp' => TIMESTAMP,
            'version' => '2.0',
            'trade_type' => 'H5',
            'product_code' => 'pay',
            'payment_type' => 'direct',
            'out_order_no' => $pay_sn,
            'uid' => $open_id,
            'total_amount' => (int) (bcmul(100, $total_pay_money)),
            'currency' => 'CNY',
            'subject' => C('site_name') . '商品订单' . $order_pay_info['pay_sn'],
            'body' => '订单详情',
            'trade_time' => TIMESTAMP,
            'valid_time' => 1800,
            'notify_url' => 'http://www.hangowa.com/',
            'wx_url' => $this->getWeiUrl($pay_sn, $total_pay_money),
            'alipay_url' => $this->getAliUrl($pay_sn, $total_pay_money),
            'wx_type' => 'MWEB',
        );
        output_data($result);
    }



    private function getWeiUrl($pay_sn, $total_pay_money) {
        output_error('暂未开启');
        $inc_file = BASE_PATH.DS.'api'.DS.'payment'.DS.'wxpay_jsapi'.DS.'wxpay_jsapi'.'.php';
        if(!is_file($inc_file)){
            output_error('支付接口不存在');
        }
        require($inc_file);

        /** @var mb_paymentModel $model_mb_payment */
        $model_mb_payment = Model('mb_payment');
        $mb_payment_info = $model_mb_payment->getMbPaymentOpenInfo(array('payment_code' => 'wxpay_jsapi'));
        if(!$mb_payment_info) {
            output_error('支付方式未开启');
        }
        $param = $mb_payment_info['payment_config'];
        $param['orderSn'] = $pay_sn;
        $param['orderFee'] = (int) (bcmul(100, $total_pay_money));
        $param['orderInfo'] = C('site_name') . '商品订单' . $pay_sn;
        $param['orderAttach'] = 'r';
        $api = new wxpay_jsapi();
        $param['appId'] = 'wx66d8e5f039ce2822';
        $param['appSecret'] = '0312929405daaedda4d1956da8c247c6';
        $api->setConfigs($param);
        $wei_web_url = '';
        try {
            $wei_web_url = $api->paymentWebSmall();
        } catch (Exception $ex) {
            //output_error($ex->getMessage());
        }
        return $wei_web_url;
    }

    private function getAliUrl($pay_sn, $total_pay_money) {
        /** @var mb_paymentModel $model_mb_payment */
        $model_mb_payment = Model('mb_payment');
        $mb_payment_info = $model_mb_payment->getMbPaymentOpenInfo(array('payment_code' => 'alipay'));
        if(!$mb_payment_info) {
            output_error('支付方式未开启');
        }
        $config = C('alipay');
        $api_file = BASE_DATA_PATH.DS.'api'.DS.'payment'.DS.'Alipay'.DS.'aop'.DS.'AopClient.php';
        $api_file2 = BASE_DATA_PATH.DS.'api'.DS.'payment'.DS.'Alipay'.DS.'aop'.DS.'request'.DS.'AlipayTradeAppPayRequest.php';
        include $api_file;
        include $api_file2;
        $aop = new AopClient();
        $aop->appId = $mb_payment_info['payment_config']['alipay_appid'];
        $aop->rsaPrivateKey = $config['rsaPrivateKey'];
        $aop->alipayrsaPublicKey = $config['alipayrsaPublicKey'];
        $aop->apiVersion = '1.0';
        $aop->signType = 'RSA2';
        $aop->postCharset = 'UTF-8';
        $aop->format = 'json';
        $request = new AlipayTradeAppPayRequest ();
        $request->setNotifyUrl(SHOP_SITE_URL."/api/payment/alipay/notify_url.php");
        $send_data = array(
            'body' => C('site_name') . '商品订单' . $pay_sn,
            'subject' => '订单'. $pay_sn,
            'out_trade_no' => $pay_sn,
            'timeout_express' => '30m',
            'total_amount' => $total_pay_money,
            'product_code' => 'QUICK_MSECURITY_PAY',
            'goods_type' => 1,
            'passback_params' => 'real_order'
        );
        $request->setBizContent(json_encode($send_data));
        $result = $aop->sdkExecute($request);
        //v($result);
        return $result;
    }

    public function douyin_ali_prepay_idOp() {

        $result = array(
            'pay_info' => array(),
            'pay_ok' => false,
            'order_info' => array(),
        );
        $pay_sn = $_POST['pay_sn'];
        $open_id = $_POST['open_id'];
        if (!$pay_sn || !$open_id) {
            output_error('参数错误');
        }
        /** @var orderModel $model_order */
        $model_order = Model('order');

        /** @var paymentLogic $logic_payment */
        $logic_payment = Logic('payment');
        //取订单信息
        $order_pay_info = $logic_payment->getRealOrderInfo($pay_sn, $this->member_info['member_id']);
        if (!$order_pay_info['state']) {
            return array('error' => $order_pay_info['msg']);
        }
        //$_POST rcb_pay = 1  充值卡 pd_pay  = 1 预存款
        //站内余额支付
        $order_list = $this->_rbc_pay($order_pay_info['data']['order_list'], $_POST);

        //计算本次需要在线支付（分别是含站内支付、纯第三方支付接口支付）的订单总金额
        $pay_amount = 0;
        $api_pay_amount = 0;
        $pay_order_id_list = array();
        $order_add_time =  date('Y-m-d H:i:s', time());
        $order_last_id = 0;
        if (!empty($order_list)) {
            foreach ($order_list as $order_info) {
                if ($order_info['order_state'] == ORDER_STATE_NEW) {
                    $api_pay_amount += $order_info['order_amount'] - $order_info['pd_amount'] - $order_info['rcb_amount'];
                    $pay_order_id_list[] = $order_info['order_id'];
                }
                $order_add_time = date('Y-m-d H:i:s', $order_info['add_time']);
                $order_last_id = $order_info['order_id'];
                $pay_amount += $order_info['order_amount'];
            }
        }
        $result['order_info'] = array(
            'pay_amount' => $pay_amount,
            'pay_type' => '线上支付',
            'pay_sn' => $pay_sn,
            'pay_time' => $order_add_time,
            'pay_order_id' => $order_last_id,
        );
        if (empty($api_pay_amount)) {
            $result['pay_ok'] = true;
            output_data($result);
        }

        $s1 = $model_order->editOrder(array('api_pay_time' => TIMESTAMP), array('order_id' => array('in', $pay_order_id_list)));
        if (!$s1) {
            output_error('更新订单信息发生错误，请重新支付');
        }

        //$order_pay_info['data']['api_pay_amount'] = ncPriceFormat($api_pay_amount);

        //如果是开始支付尾款，则把支付单表重置了未支付状态，因为支付接口通知时需要判断这个状态
        if ($order_pay_info['data']['if_buyer_repay']) {
            $update = $model_order->editOrderPay(array('api_pay_state' => 0), array('pay_id' => $order_pay_info['data']['pay_id']));
            if (!$update) {
                output_error('订单支付失败');
            }
        }
        $total_pay_money = ncPriceFormat($api_pay_amount);
        $douyin_config = C('douyin');
        $pay_sn = $pay_sn. mt_rand(0,999999999);
        $pay_info = array(
            'merchant_id' => $douyin_config['merchant_id'],
            'app_id' => $douyin_config['merchant_app_id'],
            'sign_type' => 'MD5',
            'timestamp' => TIMESTAMP,
            'version' => '2.0',
            'trade_type' => 'H5',
            'product_code' => 'pay',
            'payment_type' => 'direct',
            'out_order_no' => $pay_sn,
            'uid' => $open_id,
            'total_amount' => (int) (bcmul(100, $total_pay_money)),
            'currency' => 'CNY',
            'subject' => C('site_name') . '商品订单' . $order_pay_info['pay_sn'],
            'body' => '订单详情',
            'trade_time' => TIMESTAMP,
            'valid_time' => 1800,
            'risk_info' => '',
            'notify_url' => 'http://www.hangowa.com/',
            'wx_url' => '',
            'alipay_url' => $this->getAliUrl($pay_sn, $total_pay_money),
            'wx_type' => 'MWEB',
        );

        $result['pay_info'] = $pay_info;
        ksort($pay_info);
        $a = array();
        foreach ($pay_info as $k => $v) {
            if ($k == 'risk_info' || (string) $v === '') {
                continue;
            }
            $a[] = "{$k}={$v}";
        }

        $a = implode('&', $a);
        $a .= $douyin_config['merchant_app_secret'];
        $result['pay_info']['sign'] = MD5($a);
        output_data($result);
    }
}
