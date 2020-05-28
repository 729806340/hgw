<?php
defined('ByShopWWI') or exit('Access Invalid!');
require_once ('phpseclib1.0.1/Math/BigInteger.php');
require_once ('phpseclib1.0.1/Crypt/Hash.php');
require_once ('phpseclib1.0.1/Crypt/Base.php');
require_once ('phpseclib1.0.1/Crypt/Rijndael.php');
require_once ('phpseclib1.0.1/Crypt/AES.php');
require_once ('phpseclib1.0.1/Crypt/RSA.php');
class bestpay {
    private  $merchantId = '02420103030584361';
    private  $key = '9B71EFB45AF5BC4E56DBE45AF1C3027D1109E59DD6E21B25';
    private  $merchantPwd = '206562';
    private  $call_back_url;

    public function __construct()
    {
        $this->call_back_url = MOBILE_SITE_URL.'/api/payment/bestpay/call_back_url.php';
    }

    /***
     * 翼支付支付跳转
     * @param $param
     * @return string
     */
    public function submit($param){
        /**
         * 系统下单
         */
        $result = $this->submitOrder($param);
        $result = explode("&", $result);
        if($result[0] !='00'){
            //return false;
        }
        /**
         * 获取公文秘钥
         */
        $interface = $this->getOrderInterface();
        $interface = json_decode($interface ,true);
        if($interface['success'] != true){
            return false;
        }
        $keyIndex = $interface['result']['keyIndex'];
        $pubKey   = $interface['result']['pubKey'];
        $pay_params=array(
            'SERVICE' => 'mobile.securitypay.pay',
            'MERCHANTID' => $this->merchantId,
            'MERCHANTPWD' => $this->merchantPwd,
            'BEFOREMERCHANTURL' => $this->call_back_url,
            'BACKMERCHANTURL' => $this->call_back_url,
            'ORDERSEQ'=>$param['pay_sn'],
            'ORDERREQTRANSEQ'=>$param['order_sn'],
            'ORDERTIME'=>date('YmdHis',$param['order_date']),
            'CURTYPE'=>'RMB',
            'ORDERAMOUNT'=>ncPriceFormat($param['order_amount']),
            'SUBJECT'=>$param['subject'],
            'PRODUCTID'=>'04',
            'SIGNTYPE'=>'MD5',
            'PRODUCTDESC'=>$param['subject'],
            'PRODUCTAMOUNT'=>ncPriceFormat($param['order_amount']),
            'ATTACHAMOUNT'=>'0',
            'CUSTOMERID'=>'1',
            'BUSITYPE'=>'04',
            'SWTICHACC'=>'false'
        );
        $paramsJoined = array();
        foreach($pay_params as $par => $value) {
            $paramsJoined[] = "$par=$value";
        }
        $pay_paramData = implode('&', $paramsJoined);

        $sign_params=array(
            'SERVICE' => 'mobile.securitypay.pay',
            'MERCHANTID' => $this->merchantId,
            'MERCHANTPWD' => $this->merchantPwd,
            'SUBMERCHANTID' => '',
            'BACKMERCHANTURL'=>$this->call_back_url,
            'ORDERSEQ'=>$param['pay_sn'],
            'ORDERREQTRANSEQ'=>$param['order_sn'],
            'ORDERTIME'=> date('YmdHis',$param['order_date']),
            'ORDERVALIDITYTIME'=>'',
            'CURTYPE'=>'RMB',
            'ORDERAMOUNT'=>ncPriceFormat($param['order_amount']),
            'SUBJECT'=>$param['subject'],
            'PRODUCTID'=>'04',
            'PRODUCTDESC'=>$param['subject'],
            'CUSTOMERID'=>'1',
            'SWTICHACC'=>'false',
            'KEY'=>$this->key
        );

        $paramsJoined = array();
        foreach($sign_params as $param => $value) {
            $paramsJoined[] = "$param=$value";
        }
        $sign_paramData = implode('&', $paramsJoined);

        $sign = strtoupper(md5($sign_paramData));

        $pay_paramData.="&SIGN=".$sign;

        $random_key=md5(mt_rand());
        $cipher = new Crypt_AES();
        $cipher->setKey($random_key);
        $crypttext = base64_encode($cipher->encrypt($pay_paramData));

        $rsa = new Crypt_RSA();
        $rsa->loadKey($pubKey);

        $rsa->setEncryptionMode(CRYPT_RSA_ENCRYPTION_PKCS1);
        $encrypted = base64_encode($rsa->encrypt($random_key));


        $webUrl = "https://capi.bestpay.com.cn/gateway.pay?platform=wap_3.0&encryStr=".$crypttext."&keyIndex=".$keyIndex."&encryKey=".$encrypted;
        $webUrl = str_replace("+","%2B",$webUrl);
        return $webUrl;
    }

    /**
     * 第一步下单
     * @param $param
     */
    private function submitOrder($param){
        $risk = $this->getOrderRiskControlInfo($param);
        $mac = "MERCHANTID=".$this->merchantId."&ORDERSEQ=".$param['pay_sn']."&ORDERREQTRANSEQ=".$param['order_sn']."&ORDERREQTIME=".date('YmdHis',$param['order_date'])."&RISKCONTROLINFO=".$risk."&KEY=".$this->key;
        $mac = strtoupper(md5($mac));
        $data = array(
            'MERCHANTID'                     => $this->merchantId,
            'ORDERSEQ'                       => $param['pay_sn'],
            'ORDERREQTRANSEQ'                 => $param['order_sn'],
            'ORDERREQTIME'                   => date('YmdHis',$param['order_date']),
            'TRANSCODE'                      => '01',
            'ORDERAMT'                       => (int)(((float)$param['order_amount'])*100),
            'PRODUCTID'                      => '04',
            'PRODUCTDESC'                    => $param['subject'],
            'ENCODETYPE'                     => 1,
            'MAC'                            => $mac,
            'REQUESTSYSTEM'                  => 1,
            'RISKCONTROLINFO'                => $risk,
            'PGURL'                          => $this->call_back_url,
            'BGURL'                          => $this->call_back_url
        );
        $paramsJoined = array();
        foreach($data as $k => $v) {
            $paramsJoined[] = $k."=".$v;
        }
        $paramData = implode('&', $paramsJoined);
        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL                =>  "https://webpaywg.bestpay.com.cn/order.action",
            CURLOPT_POST               =>  1,
            CURLOPT_SSL_VERIFYPEER     =>  FALSE,
            CURLOPT_SSL_VERIFYHOST     =>  FALSE,
            CURLOPT_RETURNTRANSFER     =>  1,
            CURLOPT_POSTFIELDS         =>  $paramData
        ));
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    private function getOrderRiskControlInfo($param){
        $goods_count = 1;
        $data = array(
            'service_identify'=>103,
            'subject' => $param['subject'],
            'product_type' => 1,
            'boby' => '汉购网农特产品',
            'goods_count'=> $goods_count,
            'show_url'=>'http://www.hangowa.com',
            'comm_type'=>'01',
            'comm_name'=>'汉购网',
            'shipping_way'=>'02',
            'shipping_area'=>'',
            'shipping_city'=>'',
            'shipping_address'=>'',
            'shipping_name'=>'',
            'shipping_phone'=>'',
        );
        $data = $this->JSON($data);
        return $data;
    }

    private function getOrderInterface()
    {
        $interface = array(
            'Indexkey' => '',
            'encryKey' => '',
            'encryStr' => '',
            'interCode' => 'INTER.SYSTEM.001',
        );
        $interface = json_encode($interface);
        $ch = curl_init();
        $header =  array('Content-Type: application/json');
        curl_setopt_array($ch, array(
            CURLOPT_URL                =>  "https://capi.bestpay.com.cn/common/interface",
            CURLOPT_HTTPHEADER         =>  $header,
            CURLOPT_POST               =>  1,
            CURLOPT_SSL_VERIFYPEER     =>  FALSE,
            CURLOPT_SSL_VERIFYHOST     =>  FALSE,
            CURLOPT_RETURNTRANSFER     =>  1,
            CURLOPT_POSTFIELDS         =>  $interface
        ));
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }


    public function getReturnInfo(){
        if($_GET['resultCode']){
            $resultCode = $_GET['resultCode'];
            $orderSeq   = $_GET['orderSeq'];
            if($resultCode != -1){
                return false;
            }
        }elseif($_REQUEST['UPTRANSEQ']){
            $orderSeq = $_REQUEST['ORDERSEQ'];
            if($_REQUEST['RETNCODE'] !='0000'){
                return false;
            }
        }

        $paymentInfo = Logic('payment')->getRealOrderInfo($orderSeq);
        if(!$paymentInfo['state']){
            return false;
        }
        $paymentInfo  = $paymentInfo['data'];
        $param = array(
            'order_sn' => $paymentInfo['order_no'],
            'pay_sn'   => $orderSeq,
            'order_date' => date('YmdHis',$paymentInfo['order_list'][0]['add_time']),
        );
        $datas = $this->getPayQueryOrder($param);
        $datas = json_decode($datas ,true);
        if($datas['result']['ourTransNo'] ==''){
            return false;
        }
        return array(
            //商户订单号
            'out_trade_no' => $datas['result']['orderNo'],
            //支付宝交易号
            'trade_no' => $datas['result']['ourTransNo'],
        );

    }

    private function getPayQueryOrder( $param ){
        $mac = "MERCHANTID=".$this->merchantId."&ORDERNO=".$param['pay_sn']."&ORDERREQNO=".$param['order_sn']."&ORDERDATE=".$param['order_date']."&KEY=".$this->key;
        $mac = strtoupper(md5($mac));
        $data = array(
            'merchantId'=> $this->merchantId,
            'orderNo'   => $param['pay_sn'],
            'orderReqNo' => $param['order_sn'],
            'orderDate' => $param['order_date'],
            'mac'       => $mac
        );
        $paramsJoined = array();
        foreach($data as $k => $v) {
            $paramsJoined[] = $k."=".$v;
        }
        $paramData = implode('&', $paramsJoined);
        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL                =>  "https://webpaywg.bestpay.com.cn/query/queryOrder",
            CURLOPT_POST               =>  1,
            CURLOPT_SSL_VERIFYPEER     =>  FALSE,
            CURLOPT_SSL_VERIFYHOST     =>  FALSE,
            CURLOPT_RETURNTRANSFER     =>  1,
            CURLOPT_POSTFIELDS         =>  $paramData
        ));
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    private function arrayRecursive(&$array, $function, $apply_to_keys_also = false)
    {
        static $recursive_counter = 0;
        if (++ $recursive_counter > 10000) {
            die('possible deep recursion attack');
        }
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                arrayRecursive($array[$key], $function, $apply_to_keys_also);
            } else {
                $array[$key] = $function($value);
            }
            if ($apply_to_keys_also && is_string($key)) {
                $new_key = $function($key);
                if ($new_key != $key) {
                    $array[$new_key] = $array[$key];
                    unset($array[$key]);
                }
            }
        }
        $recursive_counter --;
    }

    private function JSON($array)
    {
        $this->arrayRecursive($array, 'urlencode', true);
        $json = json_encode($array);
        return urldecode($json);
    }
}