<?php
/**
 * Author: Shen.L
 * Email: shen@shenl.com
 * Date: 2016/12/12
 * Time: 16:39
 */

class PingppService
{

    private $devConfig = array(
        'key'=>'sk_test_afbP8S00mH08f1enDCyf1y1G',
        'appId'=>'app_LqD8CC88yjT4OmjL',
        'channels'=>array(
            array('channel'=>'alipay','name'=>'支付宝'),
            array('channel'=>'wx','name'=>'微信'),
        ),
        'pingppRsaPublicKeyPath'=>'pingpp/data/pingpp_rsa_public_key.pem',
    );
    private $config = array(
        'key'=>'sk_live_D8OCS0SOKqT8rbDW5CSO80yL',
        'appId'=>'app_LqD8CC88yjT4OmjL',
        'channels'=>array(
            array('channel'=>'alipay','name'=>'支付宝'),
            array('channel'=>'wx','name'=>'微信'),
        ),
        'pingppRsaPublicKeyPath'=>'pingpp/data/pingpp_rsa_public_key.pem',
    );
    public function __construct()
    {
        require_once('pingpp/init.php');
        $config = $this->getConfig();
        \Pingpp\Pingpp::setApiKey($config['key']);
        if(isset($config['privateKeyPath'])) \Pingpp\Pingpp::setPrivateKeyPath($config['privateKeyPath']);
    }

    private function getConfig()
    {
        if(C('ON_DEV')) return $this->devConfig;
        return $this->config;
    }

    public function getChannel()
    {
        $config = $this->getConfig();
        if(isset($config['channels'])) return $config['channels'];
        return array();
    }

    public function getPingppRsaPublicKeyPath()
    {
        $config = $this->getConfig();
        if(isset($config['pingppRsaPublicKeyPath']))
            return rtrim(__DIR__,'/').'/'.ltrim($config['pingppRsaPublicKeyPath'],'/');
        return null;
    }

    public function verify()
    {
        if(C('ON_DEV')) return true;
        $publicKeyPath = $this->getPingppRsaPublicKeyPath();
        $rawData = file_get_contents('php://input');
        $headers = \Pingpp\Util\Util::getRequestHeaders();
        // 签名在头部信息的 x-pingplusplus-signature 字段
        $signature = isset($headers['X-Pingplusplus-Signature']) ? $headers['X-Pingplusplus-Signature'] : NULL;
        $res = $this->_verifySignature($rawData, $signature, $publicKeyPath);
        if ($res === 1) {
            return true;
        } else {
            $this->error();
        }
        return false;
    }

    public function handleCharge($data)
    {
        $meta = $data['metadata'];
        $order_type = $meta['order_type'];
        $out_trade_no = $data['order_no'];
        $trade_no = $data['transaction_no'];

        //参数判断
        if(!preg_match('/^\d{18}$/',$out_trade_no)) {
            $this->error();
        }

        $model_pd = Model('predeposit');
        /** @var paymentLogic $logic_payment */
        $logic_payment = Logic('payment');

        if ($order_type == 'real_order') {

            $result = $logic_payment->getRealOrderInfo($out_trade_no);
            if (intval($result['data']['api_pay_state'])) {
                $this->success();
            }
            $order_list = $result['data']['order_list'];
            $api_pay_amount = 0;
            if (!empty($order_list)) {
                foreach ($order_list as $order_info) {
                    $api_pay_amount += $order_info['order_amount'] - $order_info['pd_amount'] - $order_info['rcb_amount'];
                }
            }
        } elseif ($order_type == 'vr_order'){

            $result = $logic_payment->getVrOrderInfo($out_trade_no);

            //订单存在被系统自动取消的可能性
            if (!in_array($result['data']['order_state'],array(ORDER_STATE_NEW,ORDER_STATE_CANCEL))) {
                $this->success();
            }
            $api_pay_amount = $result['data']['order_amount'] - $result['data']['pd_amount'] - $result['data']['rcb_amount'];

        } elseif ($order_type == 'pd_order') {

            $result = $logic_payment->getPdOrderInfo($out_trade_no);
            if ($result['data']['pdr_payment_state'] == 1) {
                $this->success();
            }
            $api_pay_amount = $result['data']['pdr_amount'];

        } else {
            $this->error();
        }
        // TODO 验证 支付金额是否合法
        if(ncPriceFormat($api_pay_amount) > ncPriceFormat($data['amount']/100)){
            // 实际支付金额小于应支付金额
            Log::selflog("实际支付金额小于应支付金额！支付单号:{$out_trade_no},应支付金额{$api_pay_amount}，实际支付金额{$data['amount']}",'payment','a+',0);
            $this->error();
        }
        $order_pay_info = $result['data'];

        //取得支付方式
        $result = $logic_payment->getPaymentInfo($data['channel']);
        if (!$result['state']) {
            $this->error();
        }
        $payment_info = $result['data'];


        //购买商品
        if ($order_type == 'real_order') {
            $result = $logic_payment->updateRealOrder($out_trade_no, $payment_info['payment_code'], $order_list, $trade_no);
        } elseif($order_type == 'vr_order'){
            $result = $logic_payment->updateVrOrder($out_trade_no, $payment_info['payment_code'], $order_pay_info, $trade_no);
        } elseif ($order_type == 'pd_order') {
            $result = $logic_payment->updatePdOrder($out_trade_no,$trade_no,$payment_info,$order_pay_info);
        }
        if ($result['state']) {
            //记录消费日志
            if ($order_type == 'real_order') {
                $log_buyer_id = $order_list[0]['buyer_id'];
                $log_buyer_name = $order_list[0]['buyer_name'];
                $log_desc = '实物订单使用'.orderPaymentName($payment_info['payment_code']).'成功支付，支付单号：'.$out_trade_no;
            } else if ($order_type == 'vr_order') {
                $log_buyer_id = $order_pay_info['buyer_id'];
                $log_buyer_name = $order_pay_info['buyer_name'];
                $log_desc = '虚拟订单使用'.orderPaymentName($payment_info['payment_code']).'成功支付，支付单号：'.$out_trade_no;
            } else if ($order_type == 'pd_order') {
                $log_buyer_id = $order_pay_info['buyer_id'];
                $log_buyer_name = $order_pay_info['buyer_name'];
                $log_desc = '预存款充值成功，使用'.orderPaymentName($payment_info['payment_code']).'成功支付，充值单号：'.$out_trade_no;
            }
            QueueClient::push('addConsume', array('member_id'=>$log_buyer_id,'member_name'=>$log_buyer_name,
                'consume_amount'=>ncPriceFormat($api_pay_amount),'consume_time'=>TIMESTAMP,'consume_remark'=>$log_desc));
        }
        $this->success();
    }

    public function create($params = null, $options = null)
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
        $config = $this->getConfig();
        $params['app'] = array('id'=>$config['appId']);
        $params['client_ip'] = $_SERVER['REMOTE_ADDR'];
        $params['currency'] = 'cny';
        try{
            $charge = \Pingpp\Charge::create($params, $options);
        }catch (Exception $e){
            output_error($e->getMessage());
            return false;
        }
        return $charge;
    }

    /**
     * @param string $id The ID of the charge to retrieve.
     * @param array|string|null $options
     *
     * @return \Pingpp\Charge
     */
    public static function retrieve($id, $options = null)
    {
        return \Pingpp\Charge::retrieve($id, $options);
    }

    /**
     * @param array|null $params
     * @param array|string|null $options
     *
     * @return array An array of Charges.
     */
    public static function all($params = null, $options = null)
    {
        return \Pingpp\Charge::all($params, $options);
    }


    /* *
     * 验证 webhooks 签名方法：
     * raw_data：Ping++ 请求 body 的原始数据即 event ，不能格式化；
     * signature：Ping++ 请求 header 中的 x-pingplusplus-signature 对应的 value 值；
     * pub_key_path：读取你保存的 Ping++ 公钥的路径；
     * pub_key_contents：Ping++ 公钥，获取路径：登录 [Dashboard](https://dashboard.pingxx.com)->点击管理平台右上角公司名称->开发信息-> Ping++ 公钥
     */
    private function _verifySignature($raw_data, $signature, $pub_key_path) {
        $pub_key_contents = file_get_contents($pub_key_path);
        return openssl_verify($raw_data, base64_decode($signature), $pub_key_contents, 'sha256');
    }

    public function success($message='success')
    {
        header($_SERVER['SERVER_PROTOCOL'] . ' 200 OK');
        exit($message);
    }
    public function error($message='error')
    {
        header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request');
        exit($message);
    }


}