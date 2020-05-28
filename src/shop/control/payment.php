<?php
/**
 * 支付入口
 *
 *
 * * @汉购网 (c) 2015-2018 ShopWWI Inc. (http://www.hangowa.com)
 * @license    http://www.shopwwi.c om
 * @link       交流群号：
 * @since      汉购网提供技术支持 授权请购买shopnc授权
 */


defined('ByShopWWI') or exit('Access Invalid!');

class paymentControl extends BaseHomeControl
{

    public function __construct()
    {
        Language::read('common,home_layout');
    }

    /**
     * 实物商品订单
     */
    public function real_orderOp()
    {
        $pay_sn = $_POST['pay_sn'];
        $payment_code = $_POST['payment_code'];
        $url = 'index.php?act=member_order';

        if (!preg_match('/^\d{18}$/', $pay_sn)) {
            showMessage('参数错误', '', 'html', 'error');
        }

        //线下支付处理
        if ($_POST['jicai'] == '1') {
            $update = Model('order')->editOrder(
                array('payment_code' => 'jicai', 'order_from' => 4),
                array('pay_sn' => $pay_sn)
            );
            redirect(SHOP_SITE_URL . '/index.php?act=buy&op=pay_ok&pay_sn=' . $pay_sn . '&paycode=jicai');
        }

        //取订单列表
        $logic_payment = Logic('payment');
        $order_pay_info = $logic_payment->getRealOrderInfo($pay_sn, $_SESSION['member_id']);
        if (!$order_pay_info['state']) {
            showMessage($order_pay_info['msg'], $url, 'html', 'error');
        }

        //站内余额支付
        $order_list = $this->_pd_pay($order_pay_info['data']['order_list'], $_POST);

        //计算本次需要在线支付（分别是含站内支付、纯第三方支付接口支付）的订单总金额
        $pay_amount = 0;
        $api_pay_amount = 0;
        $pay_order_id_list = array();
        if (!empty($order_list)) {
            foreach ($order_list as $order_info) {
                if ($order_info['order_state'] == ORDER_STATE_NEW) {
                    $api_pay_amount += $order_info['order_amount'] - $order_info['pd_amount'] - $order_info['rcb_amount'];
                    $pay_order_id_list[] = $order_info['order_id'];
                }
                $pay_amount += $order_info['order_amount'];
            }
        }
        if (empty($api_pay_amount)) {
            redirect(SHOP_SITE_URL . '/index.php?act=buy&op=pay_ok&pay_sn=' . $order_pay_info['data']['pay_sn'] . '&pay_amount=' . ncPriceFormat($pay_amount));
        }

        if ($order_list[0]['payment_code'] == 'jicai') { //集采订单再次选取第三方支付，更新payment_code和order_from
            $result = Model('order')->editOrder(array('api_pay_time' => TIMESTAMP, 'payment_code' => $payment_code, 'order_from' => '1'), array('order_id' => array('in', $pay_order_id_list)));
        } else {
            $result = Model('order')->editOrder(array('api_pay_time' => TIMESTAMP), array('order_id' => array('in', $pay_order_id_list)));
        }

        if (!$result) {
            showMessage('更新订单信息发生错误，请重新支付', $url, 'html', 'error');
        }

        $result = $logic_payment->getPaymentInfo($payment_code);
        if (!$result['state']) {
            showMessage($result['msg'], $url, 'html', 'error');
        }
        $payment_info = $result['data'];

        $order_pay_info['data']['api_pay_amount'] = ncPriceFormat($api_pay_amount);

        //如果是开始支付尾款，则把支付单表重置了未支付状态，因为支付接口通知时需要判断这个状态
        if ($order_pay_info['data']['if_buyer_repay']) {
            $update = Model('order')->editOrderPay(array('api_pay_state' => 0), array('pay_id' => $order_pay_info['data']['pay_id']));
            if (!$update) {
                showMessage('订单支付失败', $url, 'html', 'error');
            }
            $order_pay_info['data']['api_pay_state'] = 0;
        }
        //转到第三方API支付
        $this->_api_pay($order_pay_info['data'], $payment_info);
    }

    /**
     * 虚拟商品购买
     */
    public function vr_orderOp()
    {
        $order_sn = $_POST['order_sn'];
        $payment_code = $_POST['payment_code'];
        $url = 'index.php?act=member_vr_order';
        if (!preg_match('/^\d{18}$/', $order_sn)) {
            showMessage('参数错误', '', 'html', 'error');
        }
        //计算所需支付金额等支付单信息
        $result = Logic('payment')->getVrOrderInfo($order_sn, $_SESSION['member_id']);
        if (!$result['state']) {
            showMessage($result['msg'], $url, 'html', 'error');
        }
        //站内余额支付
        $order_info = $this->_pd_vr_pay($result['data'], $_POST);
        //计算本次需要在线支付金额
        $api_pay_amount = 0;
        if ($order_info['order_state'] == ORDER_STATE_NEW) {
            $api_pay_amount = $order_info['order_amount'] - $order_info['pd_amount'] - $order_info['rcb_amount'];
        }
        //如果所需支付金额为0，转到支付成功页
        if (empty($api_pay_amount)) {
            redirect('index.php?act=buy_virtual&op=pay_ok&order_sn=' . $order_info['order_sn'] . '&order_id=' . $order_info['order_id'] . '&order_amount=' . ncPriceFormat($order_info['order_amount']));
        }
        $result = Model('vr_order')->editOrder(array('api_pay_time' => TIMESTAMP), array('order_id' => $order_info['order_id']));
        if (!$result) {
            showMessage('更新订单信息发生错误，请重新支付', $url, 'html', 'error');
        }
        $result = Logic('payment')->getPaymentInfo($payment_code);
        if (!$result['state']) {
            showMessage($result['msg'], $url, 'html', 'error');
        }
        $payment_info = $result['data'];
        $order_info['api_pay_amount'] = ncPriceFormat($api_pay_amount);
        //转到第三方API支付
        $this->_api_pay($order_info, $payment_info);
    }

    /**
     * 预存款充值
     */
    public function pd_orderOp()
    {
        $pdr_sn = $_POST['pdr_sn'];
        $payment_code = $_POST['payment_code'];
        $url = urlMember('predeposit');

        if (!preg_match('/^\d{18}$/', $pdr_sn)) {
            showMessage('参数错误', $url, 'html', 'error');
        }

        $logic_payment = Logic('payment');
        $result = $logic_payment->getPaymentInfo($payment_code);
        if (!$result['state']) {
            showMessage($result['msg'], $url, 'html', 'error');
        }
        $payment_info = $result['data'];

        $result = $logic_payment->getPdOrderInfo($pdr_sn, $_SESSION['member_id']);
        if (!$result['state']) {
            showMessage($result['msg'], $url, 'html', 'error');
        }
        if ($result['data']['pdr_payment_state'] || empty($result['data']['api_pay_amount'])) {
            showMessage('该充值单不需要支付', $url, 'html', 'error');
        }

        //转到第三方API支付
        $this->_api_pay($result['data'], $payment_info);
    }

    /**
     * 站内余额支付(充值卡、预存款支付) 实物订单
     *
     */
    private function _pd_pay($order_list, $post)
    {
        if (empty($post['password'])) {
            return $order_list;
        }
        $model_member = Model('member');
        $buyer_info = $model_member->getMemberInfoByID($_SESSION['member_id']);
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
            $logic_buy_1 = Logic('buy_1');
            if ($_SESSION['lock_pd_pay']&&$_SESSION['lock_pd_pay']>time()-60){
                throw new \Think\Exception('请勿重复提交支付请求，请到订单列表查看支付状态');
            }
            $_SESSION['lock_pd_pay'] = time();
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
            $_SESSION['lock_pd_pay'] = 0;
        } catch (Exception $e) {
            $model_member->rollback();
            showMessage($e->getMessage(), '', 'html', 'error');
        }

        return $order_list;
    }

    /**
     * 站内余额支付(充值卡、预存款支付) 虚拟订单
     *
     */
    private function _pd_vr_pay($order_info, $post)
    {
        if (empty($post['password'])) {
            return $order_info;
        }
        $model_member = Model('member');
        $buyer_info = $model_member->getMemberInfoByID($_SESSION['member_id']);
        if ($buyer_info['member_paypwd'] == '' || $buyer_info['member_paypwd'] != md5($post['password'])) {
            return $order_info;
        }

        if ($buyer_info['available_rc_balance'] == 0) {
            $post['rcb_pay'] = null;
        }
        if ($buyer_info['available_predeposit'] == 0) {
            $post['pd_pay'] = null;
        }
        if (floatval($order_info['rcb_amount']) > 0 || floatval($order_info['pd_amount']) > 0) {
            return $order_info;
        }

        try {
            $model_member->beginTransaction();
            $logic_buy = Logic('buy_virtual');
            //使用充值卡支付
            if (!empty($post['rcb_pay'])) {
                $order_info = $logic_buy->rcbPay($order_info, $post, $buyer_info);
            }

            //使用预存款支付
            if (!empty($post['pd_pay'])) {
                $order_info = $logic_buy->pdPay($order_info, $post, $buyer_info);
            }

            $model_member->commit();
        } catch (Exception $e) {
            $model_member->rollback();
            showMessage($e->getMessage(), '', 'html', 'error');
        }

        return $order_info;
    }

    /**
     * 第三方在线支付接口
     *
     */
    private function _api_pay($order_info, $payment_info)
    {
        $payment_api = new $payment_info['payment_code']($payment_info, $order_info);
        if ($payment_info['payment_code'] == 'chinabank') {
            $payment_api->submit();
        } elseif ($payment_info['payment_code'] == 'wxpay') {
            if (!extension_loaded('curl')) {
                showMessage('系统curl扩展未加载，请检查系统配置', '', 'html', 'error');
            }
            Tpl::setDir('buy');
            Tpl::setLayout('buy_layout');
            if (array_key_exists('order_list', $order_info)) {
                Tpl::output('order_list', $order_info['order_list']);
                Tpl::output('args', 'buyer_id=' . $_SESSION['member_id'] . '&pay_id=' . $order_info['pay_id']);
            } else {
                Tpl::output('order_list', array($order_info));
                Tpl::output('args', 'buyer_id=' . $_SESSION['member_id'] . '&order_id=' . $order_info['order_id']);
            }
            Tpl::output('api_pay_amount', $order_info['api_pay_amount']);
            Tpl::output('pay_url', base64_encode(encrypt($payment_api->get_payurl(), MD5_KEY)));
            Tpl::output('nav_list', rkcache('nav', true));
            Tpl::showpage('payment.wxpay');
        } elseif ($payment_info['payment_code'] == 'yeepay') {
            // TODO 易宝支付
            Tpl::setDir('buy');
            Tpl::setLayout('buy_layout');
            $payment_info = $payment_api->getPaymentInfo();
            Tpl::output('payment_info', $payment_info);
            Tpl::showpage('payment.yeepay');
        } elseif ($payment_info['payment_code'] == 'bestpay') {
            // TODO 翼支付
            Tpl::setDir('buy');
            Tpl::setLayout('buy_layout');
            $payment_info = $payment_api->getPaymentInfo();
            Tpl::output('payment_info', $payment_info);
            Tpl::showpage('payment.bestpay');
        } else{
            @header("Location: " . $payment_api->get_payurl());
        }
        exit();
    }

    /**
     * 通知处理(支付宝异步通知和网银在线自动对账)
     *
     */
    public function notifyOp()
    {
        switch ($_GET['payment_code']) {
            case 'alipay':
                $success = 'success';
                $fail = 'fail';
                break;
            case 'chinabank':
                $success = 'ok';
                $fail = 'error';
                break;
            default:
                exit();
        }
        $order_type = $_POST['extra_common_param'];
        $out_trade_no = $_POST['out_trade_no'];
        if (empty($order_type)) {
            $order_type = $_POST['passback_params'];
            if ($order_type) {
                $out_trade_no = substr($out_trade_no, 0, 18);
            }
        }
        $trade_no = $_POST['trade_no'];

        //参数判断
        if (!preg_match('/^\d{18}$/', $out_trade_no)) exit($fail);

        $model_pd = Model('predeposit');
        /** @var paymentLogic $logic_payment */
        $logic_payment = Logic('payment');

        if ($order_type == 'real_order') {

            $result = $logic_payment->getRealOrderInfo($out_trade_no);
            if (intval($result['data']['api_pay_state'])) {
                exit($success);
            }
            $order_list = $result['data']['order_list'];
            $api_pay_amount = 0;
            if (!empty($order_list)) {
                foreach ($order_list as $order_info) {
                    $api_pay_amount += $order_info['order_amount'] - $order_info['pd_amount'] - $order_info['rcb_amount'];
                }
            }
        } elseif ($order_type == 'vr_order') {

            $result = $logic_payment->getVrOrderInfo($out_trade_no);

            //订单存在被系统自动取消的可能性
            if (!in_array($result['data']['order_state'], array(ORDER_STATE_NEW, ORDER_STATE_CANCEL))) {
                exit($success);
            }
            $api_pay_amount = $result['data']['order_amount'] - $result['data']['pd_amount'] - $result['data']['rcb_amount'];

        } elseif ($order_type == 'pd_order') {

            $result = $logic_payment->getPdOrderInfo($out_trade_no);
            if ($result['data']['pdr_payment_state'] == 1) {
                exit($success);
            }
            $api_pay_amount = $result['data']['pdr_amount'];

        } else {
            exit();
        }
        $order_pay_info = $result['data'];

        //取得支付方式
        $result = $logic_payment->getPaymentInfo($_GET['payment_code']);
        if (!$result['state']) {
            exit($fail);
        }
        $payment_info = $result['data'];

        //创建支付接口对象
        $payment_api = new $payment_info['payment_code']($payment_info, $order_pay_info);

        //对进入的参数进行远程数据判断
        if (empty($_POST['passback_params'])) {
            $verify = $payment_api->notify_verify();
            if (!$verify) {
                exit($fail);
            }
        }

        //购买商品
        if ($order_type == 'real_order') {
            $result = $logic_payment->updateRealOrder($out_trade_no, $payment_info['payment_code'], $order_list, $trade_no);
        } elseif ($order_type == 'vr_order') {
            $result = $logic_payment->updateVrOrder($out_trade_no, $payment_info['payment_code'], $order_pay_info, $trade_no);
        } elseif ($order_type == 'pd_order') {
            $result = $logic_payment->updatePdOrder($out_trade_no, $trade_no, $payment_info, $order_pay_info);
        }
        if ($result['state']) {
            //记录消费日志
            if ($order_type == 'real_order') {
                $log_buyer_id = $order_list[0]['buyer_id'];
                $log_buyer_name = $order_list[0]['buyer_name'];
                $log_desc = '实物订单使用' . orderPaymentName($payment_info['payment_code']) . '成功支付，支付单号：' . $out_trade_no;
            } else if ($order_type == 'vr_order') {
                $log_buyer_id = $order_pay_info['buyer_id'];
                $log_buyer_name = $order_pay_info['buyer_name'];
                $log_desc = '虚拟订单使用' . orderPaymentName($payment_info['payment_code']) . '成功支付，支付单号：' . $out_trade_no;
            } else if ($order_type == 'pd_order') {
                $log_buyer_id = $order_pay_info['buyer_id'];
                $log_buyer_name = $order_pay_info['buyer_name'];
                $this->_activeZuanshi($api_pay_amount,$order_pay_info);
                $log_desc = '预存款充值成功，使用' . orderPaymentName($payment_info['payment_code']) . '成功支付，充值单号：' . $out_trade_no;
            }
            QueueClient::push('addConsume', array('member_id'      => $log_buyer_id, 'member_name' => $log_buyer_name,
                                                  'consume_amount' => ncPriceFormat($api_pay_amount), 'consume_time' => TIMESTAMP, 'consume_remark' => $log_desc));
        }

        exit($result['state'] ? $success : $fail);
    }

    /**
     * 支付接口返回
     *
     */
    public function returnOp()
    {
        $order_type = $_GET['extra_common_param'];
        if ($order_type == 'real_order') {
            $act = 'member_order';
        } elseif ($order_type == 'vr_order') {
            $act = 'member_vr_order';
        } elseif ($order_type == 'pd_order') {
            $act = 'predeposit';
        } else {
            exit();
        }

        $out_trade_no = $_GET['out_trade_no'];
        $trade_no = $_GET['trade_no'];
        $url = SHOP_SITE_URL . '/index.php?act=' . $act;

        //对外部交易编号进行非空判断
        if (!preg_match('/^\d{18}$/', $out_trade_no)) {
            showMessage('参数错误', $url, '', 'html', 'error');
        }

        /** @var paymentLogic $logic_payment */
        $logic_payment = Logic('payment');

        if ($order_type == 'real_order') {

            $result = $logic_payment->getRealOrderInfo($out_trade_no);
            if (!$result['state']) {
                showMessage($result['msg'], $url, 'html', 'error');
            }
            if ($result['data']['api_pay_state']) {
                $payment_state = 'success';
            }
            $order_list = $result['data']['order_list'];

            //支付成功页面展示在线支付了多少金额
            $result['data']['api_pay_amount'] = 0;
            if (!empty($order_list)) {
                foreach ($order_list as $order_info) {
                    $result['data']['api_pay_amount'] += $order_info['order_amount'] - $order_info['pd_amount'] - $order_info['rcb_amount'];
                }
            }

        } elseif ($order_type == 'vr_order') {

            $result = $logic_payment->getVrOrderInfo($out_trade_no);
            if (!$result['state']) {
                showMessage($result['msg'], $url, 'html', 'error');
            }

            if (!in_array($result['data']['order_state'], array(ORDER_STATE_NEW))) {
                $payment_state = 'success';
            }

            //支付成功页面展示在线支付了多少金额
            $result['data']['api_pay_amount'] = $result['data']['order_amount'] - $result['data']['pd_amount'] - $result['data']['rcb_amount'];

        } elseif ($order_type == 'pd_order') {

            $result = $logic_payment->getPdOrderInfo($out_trade_no);
            if (!$result['state']) {
                showMessage($result['msg'], $url, 'html', 'error');
            }
            if ($result['data']['pdr_payment_state'] == 1) {
                $payment_state = 'success';
            }
            $result['data']['api_pay_amount'] = $result['data']['pdr_amount'];
        }
        $order_pay_info = $result['data'];
        $api_pay_amount = $result['data']['api_pay_amount'];

        if ($payment_state != 'success') {
            //取得支付方式
            $result = $logic_payment->getPaymentInfo($_GET['payment_code']);
            if (!$result['state']) {
                showMessage($result['msg'], $url, 'html', 'error');
            }
            $payment_info = $result['data'];

            //创建支付接口对象
            $payment_api = new $payment_info['payment_code']($payment_info, $order_pay_info);

            //返回参数判断
            $verify = $payment_api->return_verify();
            if (!$verify) {
                showMessage('支付数据验证失败', $url, 'html', 'error');
            }

            //取得支付结果
            $pay_result = $payment_api->getPayResult($_GET);
            if (!$pay_result) {
                showMessage('非常抱歉，您的订单支付没有成功，请您后尝试', $url, 'html', 'error');
            }

            //更改订单支付状态
            if ($order_type == 'real_order') {
                $result = $logic_payment->updateRealOrder($out_trade_no, $payment_info['payment_code'], $order_list, $trade_no);
            } else if ($order_type == 'vr_order') {
                $result = $logic_payment->updateVrOrder($out_trade_no, $payment_info['payment_code'], $order_pay_info, $trade_no);
            } else if ($order_type == 'pd_order') {
                $result = $logic_payment->updatePdOrder($out_trade_no, $trade_no, $payment_info, $order_pay_info);
            }
            if (!$result['state']) {
                showMessage('支付状态更新失败', $url, 'html', 'error');
            } else {
                //记录消费日志
                if ($order_type == 'real_order') {
                    $log_buyer_id = $order_list[0]['buyer_id'];
                    $log_buyer_name = $order_list[0]['buyer_name'];
                    $log_desc = '实物订单使用' . orderPaymentName($payment_info['payment_code']) . '成功支付，支付单号：' . $out_trade_no;
                } else if ($order_type == 'vr_order') {
                    $log_buyer_id = $order_pay_info['buyer_id'];
                    $log_buyer_name = $order_pay_info['buyer_name'];
                    $log_desc = '虚拟订单使用' . orderPaymentName($payment_info['payment_code']) . '成功支付，支付单号：' . $out_trade_no;
                } else if ($order_type == 'pd_order') {
                    $log_buyer_id = $order_pay_info['buyer_id'];
                    $log_buyer_name = $order_pay_info['buyer_name'];
                    //充值成功，开始发系统现金
                    $api_pay_amount = $order_pay_info['api_pay_amount'];

                    // TODO 开通钻石会员
                    $this->_activeZuanshi($api_pay_amount,$order_pay_info);
                    $log_desc = '预存款充值成功，使用' . orderPaymentName($payment_info['payment_code']) . '成功支付，充值单号：' . $out_trade_no;
                }
                QueueClient::push('addConsume', array('member_id'      => $log_buyer_id, 'member_name' => $log_buyer_name,
                                                      'consume_amount' => ncPriceFormat($api_pay_amount), 'consume_time' => TIMESTAMP, 'consume_remark' => $log_desc));
            }
        }

        //支付成功后跳转
        if ($order_type == 'real_order') {
            $pay_ok_url = SHOP_SITE_URL . '/index.php?act=buy&op=pay_ok&pay_sn=' . $out_trade_no . '&pay_amount=' . ncPriceFormat($api_pay_amount);
        } elseif ($order_type == 'vr_order') {
            $pay_ok_url = SHOP_SITE_URL . '/index.php?act=buy_virtual&op=pay_ok&order_sn=' . $out_trade_no . '&order_id=' . $order_pay_info['order_id'] . '&order_amount=' . ncPriceFormat($api_pay_amount);
        } elseif ($order_type == 'pd_order') {
            $pay_ok_url = urlMember('predeposit');
        }
        if ($payment_info['payment_code'] == 'tenpay') {
            showMessage('', $pay_ok_url, 'tenpay');
        } else {
            redirect($pay_ok_url);
        }
    }

    //手动发放--赠送活动金额
    public function manDiamondOp(){

        $member_id = $_GET['member_id'];
        $sys_amount = $_GET['sys_amount'];
        $member_info = Model('member')->getMemberInfoByID($member_id);

        $data_pd['available_predeposit'] = array('exp','available_predeposit+'.$sys_amount);
        $update = Model('member')->editMember(array('member_id'=>$member_id),$data_pd);

        //log::selflog('update:'.$update, 'payment');
        if (!$update) {
            throw new Exception('操作失败');
        } else {
            //系统赠送金额成功，增加系统赠送备注
            $data_log = array();
            $data_log['lg_invite_member_id'] = $member_id;
            $data_log['lg_member_id'] = $member_id;
            $data_log['lg_member_name'] = $member_info['member_name'];
            $data_log['lg_add_time'] = TIMESTAMP;
            $data_log['lg_type'] = 'recharge';
            $data_log['lg_av_amount'] = $sys_amount;
            $data_log['lg_desc'] = '系统赠送金额: '.$sys_amount;

            $model_predeposit =  Model('predeposit');
            $insert = $model_predeposit->insertPdLog($data_log);
            if (!$insert) {
                throw new Exception('操作失败');
            }
            //赠送钻石会员
            Model('member')->setDiamondMember($member_id);
            echo '操作成功';
        }

    }

    //手动发放--赠送红包
    public function manRptOp(){
        $member_id = $_GET['member_id'];
        $member_info = Model('member')->getMemberInfoByID($member_id);
        $current_time = time();
        $activity_begin_time = strtotime("2017-3-22");
        $activity_end_time = strtotime("2017-4-12");
        if(($current_time >= $activity_begin_time) and ($current_time <= $activity_end_time)){
            $model_redpacket = Model('redpacket');
            $data = $model_redpacket->getAllRptTemplate();
            foreach($data as $k => $v){
                $t_id = $v['rpacket_t_id'];
                //验证是否可领取红包
                $data = $model_redpacket->getCanChangeTemplateInfo($t_id, intval($member_id));
                try {
                    $model_redpacket->beginTransaction();
                    //添加红包信息
                    $data = $model_redpacket->exchangeRedpacket($data['info'], $member_id, $member_info['member_name']);
                    if ($data['state'] == false) {
                        throw new Exception($data['msg']);
                    }
                    $model_redpacket->commit();
                } catch (Exception $e) {
                    $model_redpacket->rollback();
                }
            }
            echo '操作成功';
        }
        //红包发放结束
    }

    private function _activeZuanshi($api_pay_amount,$order_pay_info){
        $recharge_threshold = 1000;
        $gift_money = 50;

        if( $api_pay_amount >= $recharge_threshold){
            //开始赠送金额
            $gift_num = $api_pay_amount/$recharge_threshold;
            $sys_amount = $gift_num*$gift_money;
            $member_id = $order_pay_info['pdr_member_id'];
            $data_pd['available_predeposit'] = array('exp','available_predeposit+'.$sys_amount);
            $update = Model('member')->editMember(array('member_id'=>$member_id),$data_pd);

            //log::selflog('update:'.$update, 'payment');
            if (!$update) {
                throw new Exception('操作失败');
            } else {
                //系统赠送金额成功，增加系统赠送备注
                $data_log = array();
                $data_log['lg_invite_member_id'] = $order_pay_info['pdr_member_id'];
                $data_log['lg_member_id'] = $member_id;
                $data_log['lg_member_name'] = $order_pay_info['pdr_member_name'];
                $data_log['lg_add_time'] = TIMESTAMP;
                $data_log['lg_type'] = 'recharge';
                $data_log['lg_av_amount'] = $sys_amount;
                $data_log['lg_desc'] = '系统赠送金额: '.$sys_amount;

                $model_predeposit =  Model('predeposit');
                $insert = $model_predeposit->insertPdLog($data_log);
                if (!$insert) {
                    throw new Exception('操作失败');
                }
            }

            //赠送钻石会员
            Model('member')->setDiamondMember($member_id);

            //发送充值成功短信
            $member_info = Model('member')->getMemberInfoByID($member_id);
            $time = date('H时i分',TIMESTAMP);
            $total_amount = $api_pay_amount + $sys_amount;
            $message = '温馨提示：尊贵的钻石会员用户，您于'.$time.'在汉购网充值（预存）'.$api_pay_amount.'元，活动送金额'.$sys_amount.'元，共计'.$total_amount.'元已成功充值，详情可登录查询账户余额。';
            //开始发送短信
            $sms = new Sms();
            try{
                $result = $sms->send($member_info["member_mobile"],$message);
            }catch (Exception $e){
                exit(json_encode(array('state'=>'false','msg'=>'由于短信网关的原因，验证码发送失败，请稍后重试。')));
            }

            //开始发放红包,钻石会员才送红包,钻石红包限领1次
            $member_name = $order_pay_info['pdr_member_name'];

            $current_time = time();
            $activity_begin_time = strtotime("2017-3-22");
            $activity_end_time = strtotime("2017-4-12");
            if(($current_time >= $activity_begin_time) and ($current_time <= $activity_end_time)){
                    $model_redpacket = Model('redpacket');
                    $data = $model_redpacket->getAllRptTemplate();
                    foreach($data as $k => $v){
                        $t_id = $v['rpacket_t_id'];
                        //验证是否可领取红包
                        $data = $model_redpacket->getCanChangeTemplateInfo($t_id, intval($member_id));
                        try {
                            $model_redpacket->beginTransaction();
                            //添加红包信息
                            $data = $model_redpacket->exchangeRedpacket($data['info'], $member_id, $member_name);
                            if ($data['state'] == false) {
                                throw new Exception($data['msg']);
                            }
                            $model_redpacket->commit();
                        } catch (Exception $e) {
                            $model_redpacket->rollback();
                        }
                    }
            }
            //红包发放结束

        }

    }

    public function pingppOp()
    {
        /** @var PingppService $pingpp */
        $pingpp = Service('Pingpp');
        $pingpp->verify();
        $event = json_decode(file_get_contents("php://input"), true);
        if (!isset($event['type'], $event['data'], $event['data']['object'])) {
            $pingpp->error();
        }

        switch ($event['type']) {
            case "charge.succeeded":
                $pingpp->handleCharge($event['data']['object']);
                break;
            case "refund.succeeded":
                // TODO 开发者在此处加入对退款异步通知的处理代码
                break;
            default:
                $pingpp->error();
                break;
        }
        $pingpp->error();
    }

    /**
     * 二维码显示(微信扫码支付)
     */
    public function qrcodeOp()
    {
        $data = base64_decode($_GET['data']);
        $data = decrypt($data, MD5_KEY, 30);
        require_once BASE_RESOURCE_PATH . '/phpqrcode/phpqrcode.php';
        QRcode::png($data);
    }

    /**
     * 接收微信请求，接收productid和用户的openid等参数，执行（【统一下单API】返回prepay_id交易会话标识
     */
    public function wxpay_returnOp()
    {
        $result = Logic('payment')->getPaymentInfo('wxpay');
        if (!$result['state']) {
            Log::record('wxpay not found', 'RUN');

        }
        new wxpay($result['data'], array());
        require_once BASE_PATH . '/api/payment/wxpay/native_notify.php';
    }

    /**
     * 支付成功，更新订单状态
     */
    public function wxpay_notifyOp()
    {
        /** @var paymentLogic $paymentLogic */
        $paymentLogic = Logic('payment');
        $result = $paymentLogic->getPaymentInfo('wxpay');
        if (!$result['state']) {
            Log::record('wxpay not found', 'RUN');
        }
        new wxpay($result['data'], array());
        require_once BASE_PATH . '/api/payment/wxpay/notify.php';
    }

    /**
     * yeepay 回调/通知接口
     */
    public function yeepay_callbackOp()
    {

        /** @var paymentLogic $logic_payment */
        $logic_payment = Logic('payment');
        $result = $logic_payment->getPaymentInfo('yeepay');
        $order_type = $_REQUEST['r8_MP'];
        if ($order_type == 'real_order') {
            $act = 'member_order';
        } elseif ($order_type == 'vr_order') {
            $act = 'member_vr_order';
        } elseif ($order_type == 'pd_order') {
            $act = 'predeposit';
        } else {
            exit();
        }

        $url = SHOP_SITE_URL . '/index.php?act=' . $act;
        $out_trade_no = $_REQUEST['r6_Order'];
        $trade_no = $_REQUEST['r2_TrxId'];

        //对外部交易编号进行非空判断
        if (!preg_match('/^\d{18}$/', $out_trade_no)) {
            showMessage('参数错误', $url, '', 'html', 'error');
        }


        if ($order_type == 'real_order') {

            $result = $logic_payment->getRealOrderInfo($out_trade_no);
            if (!$result['state']) {
                showMessage($result['msg'], $url, 'html', 'error');
            }
            if ($result['data']['api_pay_state']) {
                $payment_state = 'success';
            }
            $order_list = $result['data']['order_list'];

            //支付成功页面展示在线支付了多少金额
            $result['data']['api_pay_amount'] = 0;
            if (!empty($order_list)) {
                foreach ($order_list as $order_info) {
                    $result['data']['api_pay_amount'] += $order_info['order_amount'] - $order_info['pd_amount'] - $order_info['rcb_amount'];
                }
            }

        } elseif ($order_type == 'vr_order') {

            $result = $logic_payment->getVrOrderInfo($out_trade_no);
            if (!$result['state']) {
                showMessage($result['msg'], $url, 'html', 'error');
            }

            if (!in_array($result['data']['order_state'], array(ORDER_STATE_NEW))) {
                $payment_state = 'success';
            }

            //支付成功页面展示在线支付了多少金额
            $result['data']['api_pay_amount'] = $result['data']['order_amount'] - $result['data']['pd_amount'] - $result['data']['rcb_amount'];

        } elseif ($order_type == 'pd_order') {

            $result = $logic_payment->getPdOrderInfo($out_trade_no);
            if (!$result['state']) {
                showMessage($result['msg'], $url, 'html', 'error');
            }
            if ($result['data']['pdr_payment_state'] == 1) {
                $payment_state = 'success';
            }
            $result['data']['api_pay_amount'] = $result['data']['pdr_amount'];
        }
        $order_pay_info = $result['data'];
        $api_pay_amount = $result['data']['api_pay_amount'];

        if ($payment_state != 'success') {
            //取得支付方式
            if (!$result['state']) {
                showMessage($result['msg'], $url, 'html', 'error');
            }
            $payment_info = $result['data'];

            $yeepayApi = new yeepay(array(), array());
            $res = $yeepayApi->return_verify();

            if ($res['status'] !== 'succ') {
                showMessage('非常抱歉，您的订单支付没有成功，请您后尝试！（0x000001）', $url, 'html', 'error');
                return '';
            }

            //更改订单支付状态
            if ($order_type == 'real_order') {
                $result = $logic_payment->updateRealOrder($out_trade_no, 'yeepay', $order_list, $trade_no);
            } else if ($order_type == 'vr_order') {
                $result = $logic_payment->updateVrOrder($out_trade_no, 'yeepay', $order_pay_info, $trade_no);
            } else if ($order_type == 'pd_order') {
                $result = $logic_payment->updatePdOrder($out_trade_no, $trade_no, $payment_info, $order_pay_info);
            }
            if (!$result['state']) {
                showMessage('支付状态更新失败', $url, 'html', 'error');
            } else {
                //记录消费日志
                if ($order_type == 'real_order') {
                    $log_buyer_id = $order_list[0]['buyer_id'];
                    $log_buyer_name = $order_list[0]['buyer_name'];
                    $log_desc = '实物订单使用' . orderPaymentName($payment_info['payment_code']) . '成功支付，支付单号：' . $out_trade_no;
                } else if ($order_type == 'vr_order') {
                    $log_buyer_id = $order_pay_info['buyer_id'];
                    $log_buyer_name = $order_pay_info['buyer_name'];
                    $log_desc = '虚拟订单使用' . orderPaymentName($payment_info['payment_code']) . '成功支付，支付单号：' . $out_trade_no;
                } else if ($order_type == 'pd_order') {
                    $log_buyer_id = $order_pay_info['buyer_id'];
                    $log_buyer_name = $order_pay_info['buyer_name'];
                    $this->_activeZuanshi($api_pay_amount,$order_pay_info);
                    $log_desc = '预存款充值成功，使用' . orderPaymentName($payment_info['payment_code']) . '成功支付，充值单号：' . $out_trade_no;
                }
                QueueClient::push('addConsume', array('member_id'      => $log_buyer_id, 'member_name' => $log_buyer_name,
                                                      'consume_amount' => ncPriceFormat($api_pay_amount), 'consume_time' => TIMESTAMP, 'consume_remark' => $log_desc));
            }
        }

        //支付成功后跳转
        if ($order_type == 'real_order') {
            $pay_ok_url = SHOP_SITE_URL . '/index.php?act=buy&op=pay_ok&pay_sn=' . $out_trade_no . '&pay_amount=' . ncPriceFormat($api_pay_amount);
        } elseif ($order_type == 'vr_order') {
            $pay_ok_url = SHOP_SITE_URL . '/index.php?act=buy_virtual&op=pay_ok&order_sn=' . $out_trade_no . '&order_id=' . $order_pay_info['order_id'] . '&order_amount=' . ncPriceFormat($api_pay_amount);
        } elseif ($order_type == 'pd_order') {
            $pay_ok_url = urlMember('predeposit');
        }
        $_REQUEST['r9_BType'] != 2 && redirect($pay_ok_url);
    }


    public function bestpay_callbackOp()
    {
        /** @var paymentLogic $logic_payment */
        $logic_payment = Logic('payment');
        $result = $logic_payment->getPaymentInfo('bestpay');
        $order_type = $_REQUEST['ATTACH'];
        if ($order_type == 'real_order') {
            $act = 'member_order';
        } elseif ($order_type == 'vr_order') {
            $act = 'member_vr_order';
        } elseif ($order_type == 'pd_order') {
            $act = 'predeposit';
        } else {
            exit();
        }

        $url = SHOP_SITE_URL . '/index.php?act=' . $act;
        $out_trade_no = $_REQUEST['ORDERREQTRANSEQ'];
        $trade_no = $_REQUEST['UPTRANSEQ'];

        //对外部交易编号进行非空判断
        if (!preg_match('/^\d{18}$/', $out_trade_no)) {
            showMessage('参数错误', $url, '', 'html', 'error');
        }

        if ($order_type == 'real_order') {

            $result = $logic_payment->getRealOrderInfo($out_trade_no);
            if (!$result['state']) {
                showMessage($result['msg'], $url, 'html', 'error');
            }
            if ($result['data']['api_pay_state']) {
                $payment_state = 'success';
            }
            $order_list = $result['data']['order_list'];

            //支付成功页面展示在线支付了多少金额
            $result['data']['api_pay_amount'] = 0;
            if (!empty($order_list)) {
                foreach ($order_list as $order_info) {
                    $result['data']['api_pay_amount'] += $order_info['order_amount'] - $order_info['pd_amount'] - $order_info['rcb_amount'];
                }
            }
        } elseif ($order_type == 'vr_order') {

            $result = $logic_payment->getVrOrderInfo($out_trade_no);
            if (!$result['state']) {
                showMessage($result['msg'], $url, 'html', 'error');
            }

            if (!in_array($result['data']['order_state'], array(ORDER_STATE_NEW))) {
                $payment_state = 'success';
            }

            //支付成功页面展示在线支付了多少金额
            $result['data']['api_pay_amount'] = $result['data']['order_amount'] - $result['data']['pd_amount'] - $result['data']['rcb_amount'];

        } elseif ($order_type == 'pd_order') {

            $result = $logic_payment->getPdOrderInfo($out_trade_no);
            if (!$result['state']) {
                showMessage($result['msg'], $url, 'html', 'error');
            }
            if ($result['data']['pdr_payment_state'] == 1) {
                $payment_state = 'success';
            }
            $result['data']['api_pay_amount'] = $result['data']['pdr_amount'];
        }
        $order_pay_info = $result['data'];
        $api_pay_amount = $result['data']['api_pay_amount'];

        if ($payment_state != 'success') {
            //取得支付方式
            if (!$result['state']) {
                showMessage($result['msg'], $url, 'html', 'error');
            }
            $payment_info = $result['data'];

            $bestpayApi = new bestpay(array(), array());
            $res = $bestpayApi->return_verify();

            if ($res['status'] !== 'succ') {
                showMessage('非常抱歉，您的订单支付没有成功，请您后尝试！（0x000001）', $url, 'html', 'error');
                return '';
            }

            //更改订单支付状态
            if ($order_type == 'real_order') {
                $result = $logic_payment->updateRealOrder($out_trade_no, 'bestpay', $order_list, $trade_no);
            } else if ($order_type == 'vr_order') {
                $result = $logic_payment->updateVrOrder($out_trade_no, 'bestpay', $order_pay_info, $trade_no);
            } else if ($order_type == 'pd_order') {
                $result = $logic_payment->updatePdOrder($out_trade_no, $trade_no, $payment_info, $order_pay_info);
            }
            if (!$result['state']) {
                showMessage('支付状态更新失败', $url, 'html', 'error');
            } else {
                //记录消费日志
                if ($order_type == 'real_order') {
                    $log_buyer_id = $order_list[0]['buyer_id'];
                    $log_buyer_name = $order_list[0]['buyer_name'];
                    $log_desc = '实物订单使用' . orderPaymentName($payment_info['payment_code']) . '成功支付，支付单号：' . $out_trade_no;
                } else if ($order_type == 'vr_order') {
                    $log_buyer_id = $order_pay_info['buyer_id'];
                    $log_buyer_name = $order_pay_info['buyer_name'];
                    $log_desc = '虚拟订单使用' . orderPaymentName($payment_info['payment_code']) . '成功支付，支付单号：' . $out_trade_no;
                } else if ($order_type == 'pd_order') {
                    $log_buyer_id = $order_pay_info['buyer_id'];
                    $log_buyer_name = $order_pay_info['buyer_name'];
                    $this->_activeZuanshi($api_pay_amount,$order_pay_info);
                    $log_desc = '预存款充值成功，使用' . orderPaymentName($payment_info['payment_code']) . '成功支付，充值单号：' . $out_trade_no;
                }
                QueueClient::push('addConsume', array('member_id'      => $log_buyer_id, 'member_name' => $log_buyer_name,
                    'consume_amount' => ncPriceFormat($api_pay_amount), 'consume_time' => TIMESTAMP, 'consume_remark' => $log_desc));
            }
        }

        //支付成功后跳转
        if ($order_type == 'real_order') {
            $pay_ok_url = SHOP_SITE_URL . '/index.php?act=buy&op=pay_ok&pay_sn=' . $out_trade_no . '&pay_amount=' . ncPriceFormat($api_pay_amount);
        } elseif ($order_type == 'vr_order') {
            $pay_ok_url = SHOP_SITE_URL . '/index.php?act=buy_virtual&op=pay_ok&order_sn=' . $out_trade_no . '&order_id=' . $order_pay_info['order_id'] . '&order_amount=' . ncPriceFormat($api_pay_amount);
        } elseif ($order_type == 'pd_order') {
            $pay_ok_url = urlMember('predeposit');
        }
        $_REQUEST['RETNCODE']=='0000' && redirect($pay_ok_url);
    }

    public function bestpay_notifyOp(){
        /** @var paymentLogic $logic_payment */
        $logic_payment = Logic('payment');
        $result = $logic_payment->getPaymentInfo('bestpay');
        $order_type = $_REQUEST['ATTACH'];
        if ($order_type == 'real_order') {
            $act = 'member_order';
        } elseif ($order_type == 'vr_order') {
            $act = 'member_vr_order';
        } elseif ($order_type == 'pd_order') {
            $act = 'predeposit';
        } else {
            exit();
        }

        $url = SHOP_SITE_URL . '/index.php?act=' . $act;
        $out_trade_no = $_REQUEST['ORDERREQTRANSEQ'];
        $trade_no = $_REQUEST['UPTRANSEQ'];

        //对外部交易编号进行非空判断
        if (!preg_match('/^\d{18}$/', $out_trade_no)) {
            showMessage('参数错误', $url, '', 'html', 'error');
        }

        if ($order_type == 'real_order') {

            $result = $logic_payment->getRealOrderInfo($out_trade_no);
            if (!$result['state']) {
                showMessage($result['msg'], $url, 'html', 'error');
            }
            if ($result['data']['api_pay_state']) {
                $payment_state = 'success';
            }
            $order_list = $result['data']['order_list'];

            //支付成功页面展示在线支付了多少金额
            $result['data']['api_pay_amount'] = 0;
            if (!empty($order_list)) {
                foreach ($order_list as $order_info) {
                    $result['data']['api_pay_amount'] += $order_info['order_amount'] - $order_info['pd_amount'] - $order_info['rcb_amount'];
                }
            }
        } elseif ($order_type == 'vr_order') {

            $result = $logic_payment->getVrOrderInfo($out_trade_no);
            if (!$result['state']) {
                showMessage($result['msg'], $url, 'html', 'error');
            }

            if (!in_array($result['data']['order_state'], array(ORDER_STATE_NEW))) {
                $payment_state = 'success';
            }

            //支付成功页面展示在线支付了多少金额
            $result['data']['api_pay_amount'] = $result['data']['order_amount'] - $result['data']['pd_amount'] - $result['data']['rcb_amount'];

        } elseif ($order_type == 'pd_order') {

            $result = $logic_payment->getPdOrderInfo($out_trade_no);
            if (!$result['state']) {
                showMessage($result['msg'], $url, 'html', 'error');
            }
            if ($result['data']['pdr_payment_state'] == 1) {
                $payment_state = 'success';
            }
            $result['data']['api_pay_amount'] = $result['data']['pdr_amount'];
        }
        $order_pay_info = $result['data'];
        $api_pay_amount = $result['data']['api_pay_amount'];

        if ($payment_state != 'success') {
            //取得支付方式
            if (!$result['state']) {
                showMessage($result['msg'], $url, 'html', 'error');
            }
            $payment_info = $result['data'];

            $bestpayApi = new bestpay(array(), array());
            $res = $bestpayApi->return_verify();

            if ($res['status'] !== 'succ') {
                showMessage('非常抱歉，您的订单支付没有成功，请您后尝试！（0x000001）', $url, 'html', 'error');
                return '';
            }

            //更改订单支付状态
            if ($order_type == 'real_order') {
                $result = $logic_payment->updateRealOrder($out_trade_no, 'bestpay', $order_list, $trade_no);
            } else if ($order_type == 'vr_order') {
                $result = $logic_payment->updateVrOrder($out_trade_no, 'bestpay', $order_pay_info, $trade_no);
            } else if ($order_type == 'pd_order') {
                $result = $logic_payment->updatePdOrder($out_trade_no, $trade_no, $payment_info, $order_pay_info);
            }
            if (!$result['state']) {
                showMessage('支付状态更新失败', $url, 'html', 'error');
            } else {
                //记录消费日志
                if ($order_type == 'real_order') {
                    $log_buyer_id = $order_list[0]['buyer_id'];
                    $log_buyer_name = $order_list[0]['buyer_name'];
                    $log_desc = '实物订单使用' . orderPaymentName($payment_info['payment_code']) . '成功支付，支付单号：' . $out_trade_no;
                } else if ($order_type == 'vr_order') {
                    $log_buyer_id = $order_pay_info['buyer_id'];
                    $log_buyer_name = $order_pay_info['buyer_name'];
                    $log_desc = '虚拟订单使用' . orderPaymentName($payment_info['payment_code']) . '成功支付，支付单号：' . $out_trade_no;
                } else if ($order_type == 'pd_order') {
                    $log_buyer_id = $order_pay_info['buyer_id'];
                    $log_buyer_name = $order_pay_info['buyer_name'];
                    $this->_activeZuanshi($api_pay_amount,$order_pay_info);
                    $log_desc = '预存款充值成功，使用' . orderPaymentName($payment_info['payment_code']) . '成功支付，充值单号：' . $out_trade_no;
                }
                QueueClient::push('addConsume', array('member_id'      => $log_buyer_id, 'member_name' => $log_buyer_name,
                    'consume_amount' => ncPriceFormat($api_pay_amount), 'consume_time' => TIMESTAMP, 'consume_remark' => $log_desc));
            }
        }
        $_REQUEST['RETNCODE']=='0000' && die("UPTRANSEQ_".$trade_no);

    }


    public function query_stateOp()
    {
        if ($_GET['pay_id'] && intval($_GET['pay_id']) > 0) {
            $info = Model('order')->getOrderPayInfo(array('pay_id' => intval($_GET['pay_id']), 'buyer_id' => intval($_GET['buyer_id'])));
            exit(json_encode(array('state' => ($info['api_pay_state'] == '1'), 'pay_sn' => $info['pay_sn'], 'type' => 'r')));
        } elseif (intval($_GET['order_id']) > 0) {
            $info = Model('vr_order')->getOrderInfo(array('order_id' => intval($_GET['order_id']), 'buyer_id' => intval($_GET['buyer_id'])));
            exit(json_encode(array('state' => ($info['order_state'] == '20'), 'pay_sn' => $info['order_sn'], 'type' => 'v')));
        }
    }
}
