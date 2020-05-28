<?php
/**
 * 微信支付 小程序
 *
 */
namespace Common\Service\Payment\Wxpay;

use Common\Logic\OrderLogic;
use Common\Logic\PaymentLogic;
use Common\Util\QueueClient;

class SmallProgram{


    protected $small_wx_config = array();
    /**
     * 支付信息初始化
     */
    public function __construct() {
        //后面写配置
        $this->small_wx_config = C('small_wx');
        define('WXN_APPID', $this->small_wx_config['app_id']);
        define('WXN_MCHID', $this->small_wx_config['mch_id']);
        define('WXN_KEY', $this->small_wx_config['key']);
        require_once __DIR__ . '/lib/WxPay.Api.php';
        require_once __DIR__ . '/log.php';
        $logHandler= new \CLogFileHandler(RUNTIME_PATH. 'Logs/wxpay/'.date('Y-m-d').'.log');
        \Lag::Init($logHandler, 15);
    }

    public function unified_order($pay_sn, $open_id, $total_pay_money = 0, $attach = 'r')
    {
        //得到支付金额
        //$order_pay_info = D('order')->getOrderPayInfo(array('pay_sn'=> $pay_sn));
        /*if(empty($order_pay_info)){
             return array('status' => 0, 'message' => '非法操作');
        }*/

        //$condition = array();
        //$condition['pay_sn'] = $pay_sn;
        //$condition['order_state'] = ORDER_STATE_NEW;
        //$order_info = D('order')->getOrderInfo($condition,array(),'sum(order_amount-rcb_amount-pd_amount) as order_amount');

        //统一下单
        $input = new \WxPayUnifiedOrder();
        $input->SetBody($pay_sn.'订单');
        $input->SetAttach($attach);
        $input->SetOut_trade_no($pay_sn);
        $input->SetTotal_fee($total_pay_money*100);
        //$input->SetTotal_fee(1);
        $input->SetTime_start(date("YmdHis"));
        $input->SetTime_expire(date("YmdHis", time() + 7200));
        $input->SetGoods_tag('');
        $input->SetNotify_url(U('ApiUser/notify/js_api_notify'));
        $input->SetTrade_type("JSAPI");
        $input->SetOpenid($open_id);
        $input->SetProduct_id($pay_sn);
        $result = \WxPayApi::unifiedOrder($input);
        \Lag::DEBUG("unifiedorder:" . json_encode($result));
        return $result;
    }

    public function notify_back() {

        $xml = file_get_contents('php://input');
 		\Lag::DEBUG("HTTP_RAW_POST_DATA:".var_export($xml,true));
        //如果返回成功则验证签名
        $data = array();
        try {
            $data = \WxPayResults::Init($xml);
        } catch (\WxPayException $e){
            \Lag::DEBUG("获取微信支付回调失败:". $e->errorMessage());
            self::return_weixin();
        }

        \Lag::DEBUG("call back:" . json_encode($data));

        if(!array_key_exists("transaction_id", $data)){
            \Lag::DEBUG("微信小程序回调:输入参数不正确");
            self::return_weixin();
        }
        //查询订单，判断订单真实性
        if(!$this->query_order($data["transaction_id"])){
            \Lag::DEBUG("微信小程序回调:订单查询失败");
            self::return_weixin();
        }

        if (!in_array($data['attach'], array('r', 'offline') )) {
            \Lag::DEBUG("微信小程序回调:异常1");
            self::return_weixin();
        }


        $logic_payment = new PaymentLogic();
        if ($data['attach'] == 'r') {
            $result = $logic_payment->getRealOrderInfo($data['out_trade_no']);
            if(!$result['state']) {
                \Lag::DEBUG("微信小程序回调:异常2");
                self::return_weixin();
            }
            if ($result['data']['api_pay_state']) {
                \Lag::DEBUG("微信小程序回调:异常3");
                self::return_weixin();
            }
            //todo 回调状态 验证金额
            $order_list = $result['data']['order_list'];
            $result = $logic_payment->updateRealOrder($data['out_trade_no'], 'wx_jsapi', $order_list, $data["transaction_id"]);
            if (!$result['state']) {
                \Lag::DEBUG("微信小程序回调:异常4");
                self::return_weixin();
            }
            $api_pay_amount = 0;
            if (!empty($order_list)) {
                foreach ($order_list as $order_info) {
                    $api_pay_amount += $order_info['order_amount'] - $order_info['pd_amount'] - $order_info['rcb_amount'];
                }
            }

            //记录消费日志
            $log_buyer_id = $order_list[0]['buyer_id'];
            $log_buyer_name = $order_list[0]['buyer_name'];
            $log_desc = '实物订单使用微信扫码成功支付，支付单号：'.$data['out_trade_no'];
        } else {
            $order_info = D('order')->getOrderInfo(array('pay_sn' => $data['out_trade_no']));
            //todo 回调状态 验证金额
            $result = $logic_payment->updateOfflineOrder($order_info, 'wx_jsapi', $data["transaction_id"]);
            if (!$result['state']) {
                \Lag::DEBUG("微信小程序回调:异常5");
                self::return_weixin();
            }

            //确认收货
            $logic_order = new OrderLogic();
            $res = $logic_order->changeOrderStateReceive($order_info, 'buyer', $order_info['buyer_name'], '签收了货物');
            if (!$res['state']) {
                \Lag::DEBUG("微信小程序回调:异常6");
                self::return_weixin();
            }

            $log_buyer_id = $order_info['buyer_id'];
            $log_buyer_name = $order_info['buyer_name'];
            $api_pay_amount = $order_info['order_amount'] - $order_info['pd_amount'] - $order_info['rcb_amount'];
            $log_desc = '实物订单使用微信扫码成功支付，支付单号：'.$data['out_trade_no'];
        }



        QueueClient::push('addConsume', array('member_id'=>$log_buyer_id,'member_name'=>$log_buyer_name, 'consume_amount'=>ncPriceFormat($api_pay_amount),'consume_time'=>TIMESTAMP,'consume_remark'=>$log_desc));
        return true;
    }

    //查询订单
    public function query_order($transaction_id)
    {
        $input = new \WxPayOrderQuery();
        $input->SetTransaction_id($transaction_id);
        $result = \WxPayApi::orderQuery($input);
        \Lag::DEBUG("query:" . json_encode($result));
        if(array_key_exists("return_code", $result)
            && array_key_exists("result_code", $result)
            && $result["return_code"] == "SUCCESS"
            && $result["result_code"] == "SUCCESS")
        {
            return true;
        }
        return false;
    }

    /**
     *
     */

    /**
     * 作用：array转xml
     *
     * @param $arr
     * @return string
     */
    private static function arrayToXml($arr)
    {
        $xml = "<xml>";
        foreach ($arr as $key=>$val)
        {
            if (is_numeric($val))
            {
                $xml.="<".$key.">".$val."</".$key.">";

            }
            else
                $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
        }
        $xml.="</xml>";
        return $xml;
    }

    private static function return_weixin() {
        $returnWeiData = array(
            'return_code' => 'SUCCESS',//SUCCESS/FAIL
            'return_msg' => 'OK'
        );
        echo self::arrayToXml($returnWeiData);
        exit;
    }

    public function getConfig() {
        return array(
            'app_id' => $this->small_wx_config['app_id'],
            'secret' => $this->small_wx_config['secret'],
        );
    }

    //生成小程序 支付pay_info
    public function small_pay($prepay_id) {

        $pay_config = $this->small_wx_config;

        $pay_info = array(
            'timeStamp' => time(),
            'nonceStr'  => \WxPayApi::getNonceStr(),
            'package'   => "prepay_id=". $prepay_id,
            'signType'  => 'MD5',
        );

        $signData = array(
            'appId' => $pay_config['app_id'],
            'timeStamp' => $pay_info['timeStamp'],
            'nonceStr' => $pay_info['nonceStr'],
            'package'  => "prepay_id=". $prepay_id,
            'signType' => $pay_info['signType'],
        );
        ksort($signData);
        $string = "";
        foreach ($signData as $k => $v)
        {
            if($k != "sign" && $v != "" && !is_array($v)){
                $string .= $k . "=" . $v . "&";
            }
        }
        $string = trim($string, '&');
        //签名步骤二：在string后加入KEY
        $string = $string . "&key=".$pay_config['key'];
        //签名步骤三：MD5加密
        $string = MD5($string);
        //签名步骤四：所有字符转为大写
        $paySign = strtoupper($string);
        $pay_info['paySign'] = $paySign;
        return array('status' => 1, 'pay_info' => $pay_info);
    }


}
