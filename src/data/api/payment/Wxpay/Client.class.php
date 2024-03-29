<?php 
/**
 * 微信扫码支付
 *
 */

namespace Common\Service\Payment\Wxpay;

class Client{

    /**
     * 存放支付订单信息
     * @var array
     */
    private $_order_info = array();

    /**
     * 支付信息初始化
     * @param array $payment_info
     * @param array $order_info
     */
    public function __construct($payment_info = array(), $order_info = array()) {
        define('WXN_APPID', $payment_info['payment_config']['appid']);
        define('WXN_MCHID', $payment_info['payment_config']['mchid']);
        define('WXN_KEY', $payment_info['payment_config']['key']);
        $this->_order_info = $order_info;
    }

    /**
     * 组装包含支付信息的url(模式1)
     */
    public function get_payurls() {
        require_once __DIR__ . '/lib/WxPay.Api.php';
        require_once __DIR__ . '/WxPay.NativePay.php';
        require_once __DIR__ . '/log.php';
        $logHandler= new \CLogFileHandler(RUNTIME_PATH. 'Logs/wxpay/'.date('Y-m-d').'.log');
        $log = \Lag::Init($logHandler, 15);
        $notify = new \NativePay();
        return $notify->GetPrePayUrl($this->_order_info['pay_sn']);
    }

    /**
     * 组装包含支付信息的url(模式2)
     */
    public function get_payurl() {
        require_once __DIR__ . '/lib/WxPay.Api.php';
        require_once __DIR__ . '/WxPay.NativePay.php';
        require_once __DIR__ . '/log.php';

        $logHandler= new \CLogFileHandler(RUNTIME_PATH. 'Logs/wxpay/'.date('Y-m-d').'.log');
        $log = \Lag::Init($logHandler, 15);

        //统一下单
        $input = new \WxPayUnifiedOrder();
        $input->SetBody($this->_order_info['pay_sn'].'订单');
//         $input->SetBody(C('site_name').'订单');
        $input->SetAttach($this->_order_info['order_type'] == 'vr_order' ? 'v' : 'r');
        $input->SetOut_trade_no($this->_order_info['pay_sn']);
        $input->SetTotal_fee($this->_order_info['api_pay_amount']*100);
        $input->SetTime_start(date("YmdHis"));
        $input->SetTime_expire(date("YmdHis", time() + 3600));
        $input->SetGoods_tag('');
        $input->SetNotify_url( U('Home/payment/wxpay_notify'));
        $input->SetTrade_type("NATIVE");
        //$input->SetOpenid($openId);
        $input->SetProduct_id($this->_order_info['pay_sn']);
        $result = \WxPayApi::unifiedOrder($input);
//         header("Content-type:text/html;charset=utf-8");
//         print_R($result);exit;
        \Lag::DEBUG("unifiedorder:" . json_encode($result));
        return $result["code_url"];
    }
}
