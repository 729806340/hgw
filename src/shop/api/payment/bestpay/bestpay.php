<?php
defined('ByShopWWI') or exit('Access Invalid!');

class bestpay{
    private  $merchantId = '02420103030584361';
    private  $key = '9B71EFB45AF5BC4E56DBE45AF1C3027D1109E59DD6E21B25';
    /** @var string 支付接口标识*/
    private $code      = 'bestpay';
    /** @var array 支付接口配置信息*/
    private $payment;
    /**@var array 订单信息*/
    private $order;
    /** @var array 发送至支付宝的参数 */
    private $parameter = array();
    private $_callbackUrl;
    /**
     * 订单类型
     * @var unknown
     */
    private $order_type;

    protected $fields = array();

    public function __construct($payment_info = array(),$order_info = array()){
        $this->_callbackUrl = SHOP_SITE_URL."/api/payment/bestpay/return_url.php";
        $this->_notifUrl = SHOP_SITE_URL."/api/payment/bestpay/notify_url.php";
        if(!empty($payment_info) and !empty($order_info)){
            $this->payment	= $payment_info;
            $this->order	= $order_info;
            $this->order_type = $order_info['order_type'];
        }
    }

    public function getPaymentInfo()
    {
        if($this->order_type=='real_order'){
            $result = $this->getRealOrderPaymentInfo();
        }else{
            $result = $this->getPdOrderPaymentInfo();
        }
        return $result;
    }

    private function getRealOrderPaymentInfo(){
        $order_sn = $this->order['order_no'];
        $pay_sn = $this->order['pay_sn'];
        $order_date = date('YmdHis', $this->order['order_list'][0]['add_time']);
        $order_amount = $this->order['api_pay_amount']*100;
        $mac = $this->getMac($order_sn , $order_date ,$order_amount);
        $res = $this->getTimeStamp($order_sn,$pay_sn);
        $res = json_decode($res , true);
        $this->logstr($order_sn, '', $mac);
        $this->add_field('merchantId',$this->merchantId);
        $this->add_field('orderNo',$order_sn);
        $this->add_field('orderSeqNo', $pay_sn);
        $this->add_field('orderDate',$order_date);
        $this->add_field('orderAmount', $order_amount);
        $this->add_field('productAmount',$order_amount);
        $this->add_field('attachAmount',0);
        $this->add_field('attach',$this->order_type);
        $this->add_field('curType','RMB');
        $this->add_field('encodeType',1);
        $this->add_field('merchantFrontUrl',$this->_callbackUrl);
        $this->add_field('merchantBackUrl',$this->_notifUrl);
        $this->add_field('busiCode','0000001');
        $this->add_field('productId','04');
        $this->add_field('customerId','18672995202');
        $this->add_field('mac', $mac);
        $this->add_field('goodsName' , $this->order['subject']);
        $this->add_field('timestamp' , $res['result']);
        $this->add_field('riskControlInfo' , $this->getOrderRiskControlInfo());
        return $this->parameter;
    }

    private function getPdOrderPaymentInfo(){
        $order_sn = $this->order['pdr_sn'];
        $pay_sn = $this->order['pay_sn'];
        $order_date = date('YmdHis', $this->order['pdr_add_time']);
        $order_amount = $this->order['api_pay_amount']*100;
        $mac = $this->getMac($order_sn , $order_date ,$order_amount);
        $res = $this->getTimeStamp($order_sn,$pay_sn);
        $res = json_decode($res , true);
        $this->logstr($order_sn, '', $mac);
        $this->add_field('merchantId',$this->merchantId);
        $this->add_field('orderNo',$order_sn);
        $this->add_field('orderSeqNo', $pay_sn);
        $this->add_field('orderDate',$order_date);
        $this->add_field('orderAmount', $order_amount);
        $this->add_field('productAmount',$order_amount);
        $this->add_field('attachAmount',0);
        $this->add_field('attach',$this->order_type);
        $this->add_field('curType','RMB');
        $this->add_field('encodeType',1);
        $this->add_field('merchantFrontUrl',$this->_callbackUrl);
        $this->add_field('merchantBackUrl',$this->_notifUrl);
        $this->add_field('busiCode','0000001');
        $this->add_field('productId','04');
        $this->add_field('customerId','18672995202');
        $this->add_field('mac', $mac);
        $this->add_field('goodsName' , $this->order['subject']);
        $this->add_field('timestamp' , $res['result']);
        $this->add_field('riskControlInfo' , $this->getOrderRiskControlInfo());
        return $this->parameter;
    }

    private function getOrderRiskControlInfo(){
        $goods_count = count($this->order['list']);
        $data = array(
            'service_identify'=>103,
            'subject' => $this->order['subject'],
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



    private function getTimeStamp($order_sn , $pay_sn ) {
        $mac = "MERCHANTID=".$this->merchantId."&ORDERSEQ=".$order_sn."&ORDERREQSEQ=".$pay_sn."&KEY=".$this->key;
        $data = array(
            'MERCHANTID' => $this->merchantId,
            'ORDERSEQ'   => $order_sn,
            'ORDERREQTRANSEQ' => $pay_sn,
            'MAC'=> strtoupper(md5($mac))
        );
        $d = '';
        foreach($data as $k=>$v){
            $d .= $k."=".$v."&";
        }
        $d = substr($d  , 0 ,-1);
        $url = "https://webpaywg.bestpay.com.cn/createTimeStamp.do";
        $result = $this->curlPost($url , $d);
        return $result;
    }

    private function curlPost($url, $data, $timeout=6) {
        $ch = curl_init();
        // 设置选项，包括URL
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }



    /**
     * 设置属性
     * @params string key
     * @params string value
     * @return null
     */
    protected function add_field($key, $value='')
    {
        $this->parameter[$key] = $value;
    }


    /**
     * 返回地址验证
     *
     * @return bool
     */
    public function return_verify() {
        $recv	= $_REQUEST;
        $RETNCODE   = $recv['RETNCODE'];
        $MERCHANTID = $this->merchantId;
        $UPTRANSEQ  = $recv['UPTRANSEQ'];
        $ORDERID    = $recv['ORDERSEQ'];
        $PAYMENT    = $recv['ORDERAMOUNT'];
        $RETNCODE   = $recv['RETNCODE'];
        $RETNINFO   = $recv['RETNINFO'];
        $PAYDATE    = $recv['TRANDATE'];
        $KEY        = $this->key;
        $SIGN       = $recv['SIGN'];
        $dataCode = "UPTRANSEQ=".$UPTRANSEQ."&MERCHANTID=".$MERCHANTID."&ORDERID=".$ORDERID."&PAYMENT=".$PAYMENT."&RETNCODE=".$RETNCODE."&RETNINFO=".$RETNINFO."&PAYDATE=".$PAYDATE."&KEY=".$KEY;
        if($RETNCODE =='0000'){
            $dataCode = strtoupper(md5($dataCode));
            if($dataCode == $SIGN){
                $ret['status'] = 'succ';
            }else{
                $ret['status'] = 'failed';
            }
        }else{
            $ret['status'] = 'failed';
        }
        return $ret;
    }

    /**
     *
     * 取得订单支付状态，成功或失败
     * @param array $param
     * @return array
     */
    public function getPayResult($param){
        return $param['trade_status'] == 'TRADE_SUCCESS';
    }

    /**
     *
     *
     * @param string $name
     * @return
     */
    public function __get($name){
        return $this->$name;
    }


    #签名函数生成签名串
    function getMac( $order_sn , $order_date , $order_amount) {
        $mac = '';
        $mac .= "MERCHANTID=".$this->merchantId."&";
        $mac .= "ORDERSEQ=".$order_sn."&";
        $mac .= "ORDERDATE=".$order_date."&";
        $mac .= "ORDERAMOUNT=".$order_amount."&";
        $mac .= "KEY=".$this->key;
        $mac = strtoupper(md5($mac));
        return $mac;
    }

    function logstr($orderid, $str, $hmac) {
        $logName= "./BestPay_HTML.log";
        $james = fopen($logName, "a+");
        fwrite($james, "\r\n" . date("Y-m-d H:i:s") . "|orderid[" . $orderid . "]|str[" . $str . "]|hmac[" . $hmac . "]");
        fclose($james);
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