<?php

defined('ByShopWWI') or exit('Access Invalid!');

class yeepay{
    /** 支付宝网关地址（新）*/
    private $_gateway = 'https://mapi.alipay.com/gateway.do?';
    /** @var string 消息验证地址 */
    private $_verify_url = 'https://mapi.alipay.com/gateway.do?service=notify_verify&';

    private $_mer_id = '10001126856';
    private $_mer_key = '69cl522AV6q613Ii4W6u8K6XuW8vM1N6bFgyv769220IuYe9u37N4y7rI4Pl';
    /** @var string 支付接口标识*/
    private $code      = 'yeepay';
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
        $this->_callbackUrl = SHOP_SITE_URL."/api/payment/yeepay/return_url.php";
        $this->_notifUrl = SHOP_SITE_URL."/api/payment/yeepay/notify_url.php";
        $this->_mer_id = C('yeepay.mer_id')?:$this->_mer_id;
        $this->_mer_key = C('yeepay.mer_key')?:$this->_mer_key;
        if(!empty($payment_info) and !empty($order_info)){
            $this->payment	= $payment_info;
            $this->order	= $order_info;
        }
    }

    public function getPaymentInfo()
    {
        $payment = $this->payment;
        $mer_id = $this->_mer_id;
        $mer_key = $this->_mer_key;
        # 业务类型
        # 支付请求，固定值"Buy" .
        $p0_Cmd = 'Buy';//iconv("GB2312", "UTF-8", urlencode('Buy'));

        #送货地址
        # 为"1": 需要用户将送货地址留在易宝支付系统;为"0": 不需要，默认为 "0".
        $p9_SAF = '0';//iconv("GB2312", "UTF-8", urlencode("0"));
        $p4_Cur = 'CNY';//iconv("GB2312", "UTF-8", urlencode('CNY'));
        $p2_Order = $this->order['pay_sn'];//iconv("GB2312", "UTF-8", urlencode($payment['order_id'])); //订单号
        //$p3_Amt = number_format($payment['cur_money'],2,".","");//iconv("GB2312", "UTF-8", urlencode(number_format($payment['cur_money'],2,".",""))); //订单金额
        $p3_Amt = C('ON_DEV')?0.01:$this->order['api_pay_amount'];//iconv("GB2312", "UTF-8", urlencode(number_format($payment['cur_money'],2,".",""))); //订单金额
        $p5_Pid = $this->order['subject'];//urlencode($payment['shopName']);
        $p6_Pcat = '';
        $p7_Pdesc = '';//'汉购网-网购首选(HANGO.COM.CN)正品 真实惠';//urlencode($payment['shopName']);
        $pa_MP = $this->order['order_type'];//iconv("GB2312", "UTF-8", urlencode($payment['payment_id'])); //'商户拓展信息';
        $pd_FrpId = '';   //支付通道编码
        $p8_Url = $this->_callbackUrl;//iconv("GB2312", "UTF-8", urlencode($this->_callbackUrl));
        //
        //#应答机制
        ##默认为"1": 需要应答机制;
        $pr_NeedResponse = '1';//iconv("GB2312", "UTF-8", urlencode("1"));

        //
        //易宝pay hidden
        $this->add_field('p0_Cmd', $p0_Cmd);
        $this->add_field('p1_MerId', $mer_id);
        $this->add_field('p2_Order', $p2_Order);  //订单号
        $this->add_field('p3_Amt', $p3_Amt);    //订单金额
        $this->add_field('p4_Cur', 'CNY');
        $this->add_field('p5_Pid', $p5_Pid);
        $this->add_field('p6_Pcat', $p6_Pcat);
        $this->add_field('p7_Pdesc', $p7_Pdesc);
        $this->add_field('p8_Url', $p8_Url); //接收支付成功数据的地址
        $this->add_field('p9_SAF', $p9_SAF);

        $this->add_field('pa_MP', $pa_MP); //'商户扩展信息'
        $this->add_field('pd_FrpId', $pd_FrpId);   //支付通道编码
        #应答机制
        ##默认为"1": 需要应答机制;
        $this->add_field('pr_NeedResponse', $pr_NeedResponse);   //支付通道编码

        #调用签名函数生成签名串 $p2_Order
        $hmac = $this->getReqHmacString($p2_Order,$p3_Amt,$p4_Cur,$p5_Pid,$p6_Pcat,$p7_Pdesc,$p8_Url,$pa_MP,$pd_FrpId,$pr_NeedResponse);
        $this->logstr($p2_Order, '', $hmac);
        $this->add_field('hmac', $hmac);
        //print_r($hmac);die();
        return $this->parameter;
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
        //将系统的控制参数置空，防止因为加密验证出错
        $recv['act']	= '';
        $recv['op']	= '';
        $recv['payment_code'] = '';

        $mer_id = $this->_mer_id;
        $mer_key = $this->_mer_key;

        $ret['order_type'] = $recv['r8_MP'];
        $ret['account'] = $mer_id;
        $ret['bank'] = 'yeepay';
        $ret['pay_account'] = $recv['r2_TrxId'];
        $ret['currency'] = $recv['r4_Cur'];
        $ret['money'] = $recv['r3_Amt'];
        $ret['paycost'] = '0.000';
        $ret['cur_money'] = $recv['r3_Amt'];
        $ret['trade_no'] = $recv['r2_TrxId'];
        $ret['t_payed'] = strtotime($recv['ru_Trxtime']);
        $ret['pay_app_id'] = "yeepay";
        $ret['pay_type'] = 'online';
        $ret['memo'] = '';

        $return = $this->getCallBackValue($r0_Cmd, $r1_Code, $r2_TrxId, $r3_Amt, $r4_Cur, $r5_Pid, $r6_Order, $r7_Uid, $r8_MP, $r9_BType, $hmac);

        #判断返回签名是否正确（True/False）
        $bRet = $this->CheckHmac($r0_Cmd, $r1_Code, $r2_TrxId, $r3_Amt, $r4_Cur, $r5_Pid, $r6_Order, $r7_Uid, $r8_MP, $r9_BType, $hmac);
        #以上代码和变量不需要修改.
        #校验码正确.
        if ($bRet) {
            if ($r1_Code == "1") {
                #	需要比较返回的金额与商家数据库中订单的金额是否相等，只有相等的情况下才认为是交易成功.
                #	并且需要对返回的处理进行事务控制，进行记录的排它性处理，在接收到支付结果通知后，判断是否进行过业务逻辑处理，不要重复进行业务逻辑处理，防止对同一条交易重复发货的情况发生.

                if ($r9_BType == "1") {
                    $ret['status'] = 'succ';
                } elseif ($r9_BType == "2") {
                    #如果需要应答机制则必须回写流,以success开头,大小写不敏感.
                    echo "success";
                    $ret['status'] = 'succ';
                }
            } else {
                $ret['status'] = 'failed';
            }
        } else {
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

    /**
     * 远程获取数据
     * @param $url string 指定URL完整路径地址
     * @param $time_out string 超时时间。默认值：60
     * @return string 远程输出的数据
     */
    private function getHttpResponse($url,$time_out = "60") {
        $urlarr     = parse_url($url);
        $errno      = "";
        $errstr     = "";
        $transports = "";
        $responseText = "";
        if($urlarr["scheme"] == "https") {
            $transports = "ssl://";
            $urlarr["port"] = "443";
        } else {
            $transports = "tcp://";
            $urlarr["port"] = "80";
        }
        $fp=@fsockopen($transports . $urlarr['host'],$urlarr['port'],$errno,$errstr,$time_out);
        if(!$fp) {
            die("ERROR: $errno - $errstr<br />\n");
        } else {
            if (trim(CHARSET) == '') {
                fputs($fp, "POST ".$urlarr["path"]." HTTP/1.1\r\n");
            } else {
                fputs($fp, "POST ".$urlarr["path"].'?_input_charset='.CHARSET." HTTP/1.1\r\n");
            }
            fputs($fp, "Host: ".$urlarr["host"]."\r\n");
            fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
            fputs($fp, "Content-length: ".strlen($urlarr["query"])."\r\n");
            fputs($fp, "Connection: close\r\n\r\n");
            fputs($fp, $urlarr["query"] . "\r\n\r\n");
            while(!feof($fp)) {
                $responseText .= @fgets($fp, 1024);
            }
            fclose($fp);
            $responseText = trim(stristr($responseText,"\r\n\r\n"),"\r\n");
            return $responseText;
        }
    }


    /**
     * 重新排序参数数组
     *
     * @param array $array
     * @return array
     */
    private function arg_sort($array) {
        ksort($array);
        reset($array);
        return $array;

    }

    /**
     * 实现多种字符编码方式
     */
    private function charset_encode($input,$_output_charset,$_input_charset="UTF-8") {
        $output = "";
        if(!isset($_output_charset))$_output_charset  = $this->parameter['_input_charset'];
        if($_input_charset == $_output_charset || $input == null) {
            $output = $input;
        } elseif (function_exists("mb_convert_encoding")){
            $output = mb_convert_encoding($input,$_output_charset,$_input_charset);
        } elseif(function_exists("iconv")) {
            $output = iconv($_input_charset,$_output_charset,$input);
        } else die("sorry, you have no libs support for charset change.");
        return $output;
    }




    /*     * ************************************************易宝支付***************************************** */
    #签名函数生成签名串
    function getReqHmacString($p2_Order, $p3_Amt, $p4_Cur, $p5_Pid, $p6_Pcat, $p7_Pdesc, $p8_Url, $pa_MP, $pd_FrpId, $pr_NeedResponse) {

        global $p0_Cmd;
        global $p9_SAF;

        # 业务类型
        # 支付请求，固定值"Buy" .
        $p0_Cmd = "Buy";

        #送货地址
        # 为"1": 需要用户将送货地址留在易宝支付系统;为"0": 不需要，默认为 "0".
        $p9_SAF = "0";
        //加载配置
        $p1_MerId = $this->_mer_id;
        $merchantKey = $this->_mer_key;

        //$p1_MerId = iconv("GB2312", "UTF-8", urlencode($p1_MerId));
        //$merchantKey = iconv("GB2312", "UTF-8", urlencode($merchantKey));

        //print_r('$p0_Cmd:'.$p0_Cmd.'$p9_SAF:'.$p9_SAF);die();

        //include 'merchantProperties.php';
        #进行签名处理，一定按照文档中标明的签名顺序进行
        $sbOld = "";
        #加入业务类型
        $sbOld = $sbOld . $p0_Cmd;
        #加入商户编号
        $sbOld = $sbOld . $p1_MerId;
        #加入商户订单号
        $sbOld = $sbOld . $p2_Order;
        #加入支付金额
        $sbOld = $sbOld . $p3_Amt;
        #加入交易币种
        $sbOld = $sbOld . $p4_Cur;
        #加入商品名称
        $sbOld = $sbOld . $p5_Pid;
        #加入商品分类
        $sbOld = $sbOld . $p6_Pcat;
        #加入商品描述
        $sbOld = $sbOld . $p7_Pdesc;
        #加入商户接收支付成功数据的地址
        $sbOld = $sbOld . $p8_Url;
        #加入送货地址标识
        $sbOld = $sbOld . $p9_SAF;
        #加入商户扩展信息
        $sbOld = $sbOld . $pa_MP;
        #加入支付通道编码
        $sbOld = $sbOld . $pd_FrpId;
        #加入是否需要应答机制
        $sbOld = $sbOld . $pr_NeedResponse;

        $this->logstr($p2_Order, $sbOld, $this->HmacMd5($sbOld, $merchantKey));

        //print_r($sbOld);die();
        return $this->HmacMd5($sbOld, $merchantKey);
    }

    function getCallbackHmacString($r0_Cmd, $r1_Code, $r2_TrxId, $r3_Amt, $r4_Cur, $r5_Pid, $r6_Order, $r7_Uid, $r8_MP, $r9_BType) {

        //include 'merchantProperties.php';
        //加载配置
        $mer_id = $this->_mer_id;
        $p1_MerId = $mer_id;
        $merchantKey = $this->_mer_key;

        //$p1_MerId = iconv("GB2312", "UTF-8", urlencode($p1_MerId));
        //$merchantKey = iconv("GB2312", "UTF-8", urlencode($merchantKey));

        //$this->logstr($r6_Order, $p1_MerId, $merchantKey);

        #取得加密前的字符串
        $sbOld = "";
        #加入商家ID
        $sbOld = $sbOld . $p1_MerId;
        #加入消息类型
        $sbOld = $sbOld . $r0_Cmd;
        #加入业务返回码
        $sbOld = $sbOld . $r1_Code;
        #加入交易ID
        $sbOld = $sbOld . $r2_TrxId;
        #加入交易金额
        $sbOld = $sbOld . $r3_Amt;
        #加入货币单位
        $sbOld = $sbOld . $r4_Cur;
        #加入产品Id
        $sbOld = $sbOld . $r5_Pid;
        #加入订单ID
        $sbOld = $sbOld . $r6_Order;
        #加入用户ID
        $sbOld = $sbOld . $r7_Uid;
        #加入商家扩展信息
        $sbOld = $sbOld . $r8_MP;
        #加入交易结果返回类型
        $sbOld = $sbOld . $r9_BType;
        $sbOld = iconv('GBK','UTF-8',$sbOld);
        $this->logstr($r6_Order, $sbOld, $this->HmacMd5($sbOld, $merchantKey));
        return $this->HmacMd5($sbOld, $merchantKey);
    }

    #	取得返回串中的所有参数

    function getCallBackValue(&$r0_Cmd, &$r1_Code, &$r2_TrxId, &$r3_Amt, &$r4_Cur, &$r5_Pid, &$r6_Order, &$r7_Uid, &$r8_MP, &$r9_BType, &$hmac) {
        $r0_Cmd = $_REQUEST['r0_Cmd'];
        $r1_Code = $_REQUEST['r1_Code'];
        $r2_TrxId = $_REQUEST['r2_TrxId'];
        $r3_Amt = $_REQUEST['r3_Amt'];
        $r4_Cur = $_REQUEST['r4_Cur'];
        $r5_Pid = $_REQUEST['r5_Pid'];
        $r6_Order = $_REQUEST['r6_Order'];
        $r7_Uid = $_REQUEST['r7_Uid'];
        $r8_MP = $_REQUEST['r8_MP'];
        $r9_BType = $_REQUEST['r9_BType'];
        $hmac = $_REQUEST['hmac'];

        return null;
    }

    function CheckHmac($r0_Cmd, $r1_Code, $r2_TrxId, $r3_Amt, $r4_Cur, $r5_Pid, $r6_Order, $r7_Uid, $r8_MP, $r9_BType, $hmac) {
        if ($hmac == $this->getCallbackHmacString($r0_Cmd, $r1_Code, $r2_TrxId, $r3_Amt, $r4_Cur, $r5_Pid, $r6_Order, $r7_Uid, $r8_MP, $r9_BType))
            return true;
        else
            return false;
    }

    function HmacMd5($data, $key) {
        // RFC 2104 HMAC implementation for php.
        // Creates an md5 HMAC.
        // Eliminates the need to install mhash to compute a HMAC
        // Hacked by Lance Rushing(NOTE: Hacked means written)
        //需要配置环境支持iconv，否则中文参数不能正常处理

        //$this->logstr('转码前:',$key, $data);

        //$key = iconv('UTF-8', 'GBK', $key);
        //$data = iconv('UTF-8', 'GBK', $data);

        //$this->logstr('转码后:',$key, $data);

        $b = 64; // byte length for md5
        if (strlen($key) > $b) {
            $key = pack("H*", md5($key));
        }
        $key = str_pad($key, $b, chr(0x00));
        $ipad = str_pad('', $b, chr(0x36));
        $opad = str_pad('', $b, chr(0x5c));
        $k_ipad = $key ^ $ipad;
        $k_opad = $key ^ $opad;

        return md5($k_opad . pack("H*", md5($k_ipad . $data)));
    }

    function logstr($orderid, $str, $hmac) {
        //include 'merchantProperties.php';
        //加载配置
        $p1_MerId = $this->_mer_id;
        $merchantKey = $this->_mer_key;

        $logName= "./YeePay_HTML.log";

        $james = fopen($logName, "a+");
        fwrite($james, "\r\n" . date("Y-m-d H:i:s") . "|orderid[" . $orderid . "]|str[" . $str . "]|hmac[" . $hmac . "]");
        fclose($james);
    }
}