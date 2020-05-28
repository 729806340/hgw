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

        //得到购买数据
        $result = $logic_buy->buyStep1($cart_id, $_POST['ifcart'], $this->member_info['member_id'], $this->member_info['store_id'], null, $member_discount, $member_level);

        //print_R($result);
        if (!$result['state']) {
            output_error($result['msg']);
        } else {
            $result = $result['data'];
        }

        //整理数据
        $store_cart_list = array();
        $sum = 0;
        foreach ($result['store_cart_list'] as $key => $value) {
            $store_cart_list[$key]['goods_list'] = array();
            foreach ($value as $k => $v) {
                if (empty($v['xianshi_info'])) $v['xianshi_info'] = array('xianshi_name' => '');
                $store_cart_list[$key]['goods_list'][] = $v;
            }
            //$store_cart_list[$key]['goods_list'] = $value;
            $store_cart_list[$key]['store_goods_total'] = $result['store_goods_total'][$key];
            if (!empty($result['store_premiums_list'][$key])) {
                $result['store_premiums_list'][$key][0]['premiums'] = true;
                $result['store_premiums_list'][$key][0]['goods_total'] = 0.00;
                $store_cart_list[$key]['goods_list'][] = $result['store_premiums_list'][$key][0];
            }
            $store_cart_list[$key]['store_mansong_rule_list'] = $result['store_mansong_rule_list'][$key];
            $store_cart_list[$key]['store_voucher_list'] = $result['store_voucher_list'][$key];
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
        $buy_list['available_predeposit'] = $result['available_predeposit'];
        $buy_list['available_rc_balance'] = $result['available_rc_balance'];
        if (is_array($result['rpt_list']) && !empty($result['rpt_list'])) {
            foreach ($result['rpt_list'] as $k => $v) {
                unset($result['rpt_list'][$k]['rpacket_id']);
                unset($result['rpt_list'][$k]['rpacket_end_date']);
                unset($result['rpt_list'][$k]['rpacket_owner_id']);
                unset($result['rpt_list'][$k]['rpacket_code']);
            }
        }
        $buy_list['rpt_list'] = $result['rpt_list'] ? $result['rpt_list'] : array();
        $buy_list['zk_list'] = $result['zk_list'];
        $buy_list['order_amount'] = $sum;
        $buy_list['rpt_info'] = '';
        $buy_list['address_api'] = $logic_buy->changeAddr($result['freight_list'], '1', '1', $this->member_info['member_id']);
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
        output_data($buy_list);
    }

    /**
     * 购物车、直接购买第二步:保存订单入库，产生订单号，开始选择支付方式
     *
     */
    public function step2Op()
    {
        output_error('系统维护中');
        exit;

        $test=array(
            'app'=>'step2',
            'post'=>$_POST,
            //'out'=>array('pay_sn' => $result['data']['pay_sn']),
        );
        $this->testLog($test);
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
        $param['order_from'] = 2;
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

    private function _rbc_pay($order_list, $post)
    {
        if (empty($post['password'])) {
            return $order_list;
        }
        $model_member = Model('member');
        $buyer_info = $model_member->getMemberInfoByID($this->member_info['member_id']);
        if ($buyer_info['member_paypwd'] == '' || $buyer_info['member_paypwd'] != md5($post['password'])) {
            return $order_list;
        }

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

}
