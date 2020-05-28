<?php
/**
 * 微信支付接口类
 * JSAPI 适用于微信内置浏览器访问WAP时支付
 *
 *
 * @copyright  Copyright (c) 2007-2015 ShopNC Inc. (http://www.shopnc.net)
 * @license    http://www.shopnc.net
 * @link       http://www.shopnc.net
 * @since      File available since Release v1.1
 */
defined('ByShopWWI') or exit('Access Invalid!');

/**
 * @todo
 */
class wxpay_jsapi
{
    const DEBUG = 0;

    protected $config;

    public function __construct()
    {
        $this->config = (object) array(
            'appId' => '',
            'appSecret' => '',
            'partnerId' => '',
            'apiKey' => '',
            'notifyUrl' => WEI_PROGRAM_URL. '/api/payment/wxpay_jsapi/notify_url.php',
            'finishedUrl' => WAP_SITE_URL . '/tmpl/member/payment_result.html?_=2&attach=_attach_',
            'undoneUrl' => WAP_SITE_URL . '/tmpl/member/payment_result_failed.html?_=2&attach=_attach_',

            'orderSn' => date('YmdHis'),
            'orderInfo' => 'Test wxpay js api',
            'orderFee' => 1,
            'orderAttach' => '_',
        );
    }

    public function setConfig($name, $value)
    {
        $this->config->$name = $value;
    }

    public function setConfigs(array $params)
    {
        foreach ($params as $name => $value) {
            $this->config->$name = $value;
        }
    }

    public function notify()
    {
        try {
            $data = $this->onNotify();
            $resultXml = $this->arrayToXml(array(
                'return_code' => 'SUCCESS',
            ));

            if (self::DEBUG) {
                file_put_contents(__DIR__ . '/log.txt', var_export($data, true), FILE_APPEND | LOCK_EX);
            }

        } catch (Exception $ex) {

            $data = null;
            $resultXml = $this->arrayToXml(array(
                'return_code' => 'FAIL',
                'return_msg' => $ex->getMessage(),
            ));

            if (self::DEBUG) {
                file_put_contents(__DIR__ . '/log_err.txt', $ex . PHP_EOL, FILE_APPEND | LOCK_EX);
            }

        }

        return array(
            $data,
            $resultXml,
        );
    }

    public function onNotify()
    {
        $d = $this->xmlToArray(file_get_contents('php://input'));

        if (empty($d)) {
            throw new Exception(__METHOD__);
        }

        if ($d['return_code'] != 'SUCCESS') {
            throw new Exception($d['return_msg']);
        }

        if ($d['result_code'] != 'SUCCESS') {
            throw new Exception("[{$d['err_code']}]{$d['err_code_des']}");
        }

        if (!$this->verify($d)) {
            throw new Exception("Invalid signature");
        }

        return $d;
    }

    public function verify(array $d)
    {
        if (empty($d['sign'])) {
            return false;
        }

        $sign = $d['sign'];
        unset($d['sign']);

        return $sign == $this->sign($d);
    }

    protected $control;

    //小程序支付
    public function paymentSmall($control, $open_id) {
        $this->control = $control;

        $prepayId = $this->getSmallPrepayId($open_id);
        $params = array();
        $params['appId'] = $this->config->appId;
        $params['timeStamp'] = '' . time();
        $params['nonceStr'] = md5(uniqid(mt_rand(), true));
        $params['package'] = 'prepay_id=' . $prepayId;
        $params['signType'] = 'MD5';

        $sign = $this->sign($params);
        $params['paySign'] = $sign;
        unset($params['appId']);
        return $params;
    }

    //h5支付 头条
    public function paymentWebSmall() {
        $data = array();
        $data['appid'] = $this->config->appId;
        $data['mch_id'] = $this->config->partnerId;
        $data['nonce_str'] = md5(uniqid(mt_rand(), true));
        $data['body'] = $this->config->orderInfo;
        $data['attach'] = $this->config->orderAttach;
        $data['out_trade_no'] = $this->config->orderSn;
        $data['total_fee'] = $this->config->orderFee;
        $data['spbill_create_ip'] = $_SERVER['REMOTE_ADDR'];
        $data['notify_url'] = $this->config->notifyUrl;
        $data['trade_type'] = 'MWEB';
        //$data['openid'] = $open_id;
        $sign = $this->sign($data);
        $data['sign'] = $sign;
        $result = $this->postXml('https://api.mch.weixin.qq.com/pay/unifiedorder', $data);

        if ($result['return_code'] != 'SUCCESS') {
            throw new Exception($result['return_msg']);
        }

        if ($result['result_code'] != 'SUCCESS') {
            throw new Exception("[{$result['err_code']}]{$result['err_code_des']}");
        }

        return $result['mweb_url'];
    }

    public function getSmallPrepayId($open_id){

        $data = array();
        $data['appid'] = $this->config->appId;
        $data['mch_id'] = $this->config->partnerId;
        $data['nonce_str'] = md5(uniqid(mt_rand(), true));
        $data['body'] = $this->config->orderInfo;
        $data['attach'] = $this->config->orderAttach;
        $data['out_trade_no'] = $this->config->orderSn;
        $data['total_fee'] = $this->config->orderFee;
        $data['spbill_create_ip'] = $_SERVER['REMOTE_ADDR'];
        $data['notify_url'] = $this->config->notifyUrl;
        $data['trade_type'] = 'JSAPI';
        $data['openid'] = $open_id;
        $sign = $this->sign($data);
        $data['sign'] = $sign;
        $result = $this->postXml('https://api.mch.weixin.qq.com/pay/unifiedorder', $data);

        if ($result['return_code'] != 'SUCCESS') {
            throw new Exception($result['return_msg']);
        }

        if ($result['result_code'] != 'SUCCESS') {
            throw new Exception("[{$result['err_code']}]{$result['err_code_des']}");
        }

        return $result['prepay_id'];
    }

    /**
     * 小程序红包
     * @param $param
     * @return array
     * @throws Exception
     */
    public function sendMiniProgramHb($param) {
        $data = array();
        $data['nonce_str'] = md5(uniqid(mt_rand(), true));
        $data['mch_billno'] = $param['mch_billno']; //"提现记录号";
        $data['mch_id'] = $param['mch_id'];
        $data['wxappid'] = $param['app_id'];
        $data['send_name'] = '汉购网分销中心';
        $data['re_openid'] = $param['open_id'];//'用户open_id';//token中获取;
        $data['total_amount'] = $param['total_amount'] * 100;//红包金额;
        $data['total_num'] = 1;
        $data['wishing'] = "分销提成";
        $data['act_name'] = "分销奖金";
        $data['remark'] = "分销提成";
        $data['notify_way'] = "MINI_PROGRAM_JSAPI";
        $data['scene_id'] = "PRODUCT_5";
        $sign = $this->sign($data);
        $data['sign'] = $sign;
        $result = $this->postXmlCurl($this->arrayToXml($data), 'https://api.mch.weixin.qq.com/mmpaymkttransfers/sendminiprogramhb', true);
        $result = $this->xmlToArray($result);
        if ($result['return_code'] != 'SUCCESS') {
            throw new Exception($result['return_msg']);
        }
        if ($result['result_code'] != 'SUCCESS') {
            throw new Exception("[{$result['err_code']}]{$result['err_code_des']}");
        }
        $params = array();
        $params['appId'] = $param['app_id'];
        $params['timeStamp'] = '' . time();
        $params['nonceStr'] = md5(uniqid(mt_rand(), true));
        $params['package'] = urlencode($result['package']);
        $params['signType'] = 'MD5';

        $sign = $this->sign($params);
        $params['paySign'] = $sign;
        unset($params['appId']);
        return $params;
    }

    //提现到零钱
    public function giveSmallChange($param) {
        $data = array();
        $data['mch_appid'] = $param['app_id'];
        $data['mchid'] = intval($param['mch_id']);
        $data['nonce_str'] = md5(uniqid(mt_rand(), true));
        $data['openid'] = $param['open_id'];
        $data['check_name'] = 'NO_CHECK';
        $data['desc'] = '社区团购佣金';
        $data['partner_trade_no'] = $param['partner_trade_no'];
        $data['amount'] = $param['amount'];
        $data['spbill_create_ip'] = $_SERVER['REMOTE_ADDR'];
        $sign = $this->sign($data);
        $data['sign'] = $sign;
        //v($data);
        $xml = $this->arrayToXml($data);
        $result = $this->postXmlCurl($xml,'https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers',true);
        $result = $this->xmlToArray($result);
        if ($result['return_code'] != 'SUCCESS') {
            throw new Exception($result['return_msg']);
        }
        if ($result['result_code'] != 'SUCCESS') {
            throw new Exception("[{$result['err_code']}]{$result['err_code_des']}");
        }
        return $result;
    }

    private static function postXmlCurl($xml, $url, $useCert = false, $second = 30)
    {
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);


        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE);
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $SSLCERT_PATH = '/home/wwwroot/hangowa.com/v2/data/api/payment/Wxpay/lib/cert/apiclient_cert.pem';
        $SSLKEY_PATH = '/home/wwwroot/hangowa.com/v2/data/api/payment/Wxpay/lib/cert/apiclient_key.pem';
        if($useCert == true){
            //设置证书
            //使用证书：cert 与 key 分别属于两个.pem文件
            curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
            curl_setopt($ch,CURLOPT_SSLCERT, $SSLCERT_PATH);
            curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
            curl_setopt($ch,CURLOPT_SSLKEY, $SSLKEY_PATH);
        }
        //post提交方式
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        //运行curl
        $data = curl_exec($ch);
        //返回结果
        if($data){
            curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
            curl_close($ch);
            throw new WxPayException("curl出错，错误码:$error");
        }
    }

    public function paymentHtml($control = null) {
        $this->control = $control;

        $prepayId = $this->getPrepayId();

        $params = array();
        $params['appId'] = $this->config->appId;
        $params['timeStamp'] = '' . time();
        $params['nonceStr'] = md5(uniqid(mt_rand(), true));
        $params['package'] = 'prepay_id=' . $prepayId;
        $params['signType'] = 'MD5';

        $sign = $this->sign($params);
        $params['paySign'] = $sign;

        // @todo timestamp
        $jsonParams = json_encode($params);

        return <<<EOB
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-type" content="text/html;charset=utf-8" />
<title>微信安全支付</title>
</head>
<body>
正在加载…
<script type="text/javascript">
function jsApiCall() {
    WeixinJSBridge.invoke(
        'getBrandWCPayRequest',
        {$jsonParams},
        function(res) {
            var h;
            if (res && res.err_msg == "get_brand_wcpay_request:ok") {
                // success;
                h = '{$this->config->finishedUrl}';
            } else {
                // fail;
                alert(res && res.err_msg);
                h = '{$this->config->undoneUrl}';
            }
            location.href = h.replace('_attach_', '{$this->config->orderAttach}');
        }
    );
}
window.onload = function() {
    if (typeof WeixinJSBridge == "undefined") {
        if (document.addEventListener) {
            document.addEventListener('WeixinJSBridgeReady', jsApiCall, false);
        } else if (document.attachEvent) {
            document.attachEvent('WeixinJSBridgeReady', jsApiCall);
            document.attachEvent('onWeixinJSBridgeReady', jsApiCall);
        }
    } else {
        jsApiCall();
    }
}
</script>
</body>
</html>
EOB;
    }

    protected function getOpenId()
    {
        if ($c = $this->control) {
            $openId = $c->getOpenId();
            if ($openId) {
                return $openId;
            }

            // through multiple requests
            $openId = $this->getOpenIdThroughMultipleRequests();
            $c->setOpenId($openId);

            return $openId;
        }

        return $this->getOpenIdThroughMultipleRequests();
    }

    public function getPrepayId(){
        // ...
        $openId = $this->getOpenId();
		
        $data = array();
        $data['appid'] = $this->config->appId;
        $data['mch_id'] = $this->config->partnerId;
        $data['nonce_str'] = md5(uniqid(mt_rand(), true));
        $data['body'] = $this->config->orderInfo;
        $data['attach'] = $this->config->orderAttach;
        $data['out_trade_no'] = $this->config->orderSn;
        $data['total_fee'] = $this->config->orderFee;
        $data['spbill_create_ip'] = $_SERVER['REMOTE_ADDR'];
        $data['notify_url'] = $this->config->notifyUrl;
        $data['trade_type'] = 'JSAPI';
        $data['openid'] = $openId;
        $sign = $this->sign($data);
        $data['sign'] = $sign;

        $result = $this->postXml('https://api.mch.weixin.qq.com/pay/unifiedorder', $data);

        if ($result['return_code'] != 'SUCCESS') {
            throw new Exception($result['return_msg']);
        }

        if ($result['result_code'] != 'SUCCESS') {
            throw new Exception("[{$result['err_code']}]{$result['err_code_des']}");
        }

        return $result['prepay_id'];
    }

    public function getOpenIdThroughMultipleRequests()
    {
        if (empty($_GET['code'])) {
            if (isset($_GET['state']) && $_GET['state'] == 'redirected') {
                throw new Exception('Auth failed');
            }

            $url = sprintf(
                'https://open.weixin.qq.com/connect/oauth2/authorize?appid=%s&redirect_uri=%s&response_type=code&scope=snsapi_base&state=redirected#wechat_redirect',
                $this->config->appId,
                urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'])
            );
            header('Location: ' . $url);
            exit;
        }

        $d = json_decode(file_get_contents(sprintf(
            'https://api.weixin.qq.com/sns/oauth2/access_token?appid=%s&secret=%s&code=%s&grant_type=authorization_code',
            $this->config->appId,
            $this->config->appSecret,
            $_GET['code']
        )), true);

        if (empty($d)) {
            throw new Exception(__METHOD__);
        }

        if (empty($d['errcode']) && isset($d['openid'])) {
            return $d['openid'];
        }

        throw new Exception(var_export($d, true));
    }

    public function sign(array $data)
    {
        ksort($data);

        $a = array();
        foreach ($data as $k => $v) {
            if ((string) $v === '') {
                continue;
            }
            $a[] = "{$k}={$v}";
        }

        $a = implode('&', $a);
        $a .= '&key=' . $this->config->apiKey;

        return strtoupper(md5($a));
    }

    public function postXml($url, array $data)
    {
        // pack xml
        $xml = $this->arrayToXml($data);

        // curl post
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        $response = curl_exec($ch);
        if (!$response) {
            throw new Exception('CURL Error: ' . curl_errno($ch));
        }
        curl_close($ch);

        // unpack xml
        return $this->xmlToArray($response);
    }

    public function arrayToXml(array $data)
    {
        $xml = "<xml>";
        foreach ($data as $k => $v) {
            if (is_numeric($v)) {
                $xml .= "<{$k}>{$v}</{$k}>";
            } else {
                $xml .= "<{$k}><![CDATA[{$v}]]></{$k}>";
            }
        }
        $xml .= "</xml>";
        return $xml;
    }

    public function xmlToArray($xml)
    {
        return json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
    }

}
