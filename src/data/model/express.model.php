<?php
/**
 * 快递模型
 *
 *
 *
 * @汉购网 (c) 2015-2018 ShopWWI Inc. (http://www.hangowa.com)
 * @license    http://www.shopwwi.c om
 * @link       交流群号：
 * @since      汉购网提供技术支持 授权请购买shopnc授权
 */
defined('ByShopWWI') or exit('Access Invalid!');
defined('EBusinessID') or define('EBusinessID', '1278543');
//电商加密私钥，快递鸟提供，注意保管，不要泄漏
defined('')  or define('AppKey', '73817b5c-2052-4054-8c87-f66ebf273d4b');
defined('ReqURL')  or define('ReqURL', 'http://api.kdniao.com/Ebusiness/EbusinessOrderHandle.aspx');
//defined('PushURL') or define('PushURL' ,'http://testapi.kdniao.cc:8081/api/eorderservice');
defined('PushURL') or define('PushURL' ,'http://api.kdniao.com/api/EOrderService');

class expressModel extends Model {

    //映射快递鸟物流公司编码
    public $ship_code = array(
        'anxindakuaixi'=>'AXD',
        'youzhengguonei'=>'',
        'cces'=>'CCES',
        'chuanxiwuliu'=>'',
        'dhl'=>'DHL',
        'datianwuliu'=>'DTWL',
        'debangwuliu'=>'DBL',
        'ems'=>'EMS',
        'emsguoji'=>'GJYZ',
        'feikangda'=>'FKD',
        'fedex'=>'FEDEX_GJ',
        'rufengda'=>'RFD',
        'ganzhongnengda'=>'NEDA',
        'youzhengguonei'=>'',
        'gongsuda'=>'GSD',
        'huitongkuaidi'=>'HTKY',
        'tiandihuayu'=>'',
        'jiajiwuliu'=>'JJKY',
        'jiayiwuliu'=>'JYWL',
        'jixianda'=>'JXD',
        'kuaijiesudi'=>'FAST',
        'longbanwuliu'=>'LB',
        'lianbangkuaidi'=>'FEDEX',
        'lianhaowuliu'=>'LHT',
        'quanyikuaidi'=>'UAPEX',
        'quanfengkuaidi'=>'QFKD',
        'quanritongkuaidi'=>'QRT',
        'shentong'=>'STO',
        'shunfeng'=>'SF',
        'suer'=>'SURE',
        'tnt'=>'TNT',
        'tiantian'=>'HHTT',
        'tiandihuayu'=>'HOAU',
        'ups'=>'UPS',
        'usps'=>'USPS',
        'xinbangwuliu'=>'XBWL',
        'xinfengwuliu'=>'XFEX',
        'cces'=>'CCES',
        'neweggozzo'=>'',
        'yuantong'=>'YTO',
        'yunda'=>'YD',
        'youzhengguonei'=>'YZPY',
        'youshuwuliu'=>'UC',
        'zhongtong'=>'ZTO',
        'zhongtiewuliu'=>'ZTKY',
        'zhaijisong'=>'ZJS',
        'zhongyouwuliu'=>'ZYWL',
        'guotong'=>'GTO',
        'jingdong'=>'JD',
        'anneng'=>'ANE',
    );
    public function __construct(){
        parent::__construct('express');
    }

    /**
     * 获取可自动发货的物流公司
     */
    public function getPushExpress()
    {
        return array(
            'youzhengguonei'=>array(
                'kdncode'=>'YZPY',
                'hgwname'=>'邮政包裹',
                'hgwid'=>42,
            ),
            'shunfeng' => array(
                'kdncode'=>'SF',
                'hgwname'=>'顺丰快递',
                'hgwid' => 29,
            ),
            'kuaijiesudi'=>array(
                'kdncode'=>'FAST',
                'hgwname'=>'快捷速递',
                'hgwid'=>21,
            ),
        );
    }

    /**
     * 查询快递列表
     *
     * @param string $id 指定快递编号
     * @return array
     */
    public function getExpressList() {
        return rkcache('express', true);
    }

    /**
     * 根据编号查询快递列表
     */
    public function getExpressListByID($id = null) {
        $express_list = rkcache('express', true);

        if(!empty($id)) {
            $id_array = explode(',', $id);
            foreach ($express_list as $key => $value) {
                if(!in_array($key, $id_array)) {
                    unset($express_list[$key]);
                }
            }
            return $express_list;
        } else {
            return array();
        }
    }

    /**
     * 查询详细信息
     */
    public function getExpressInfo($id) {
        $express_list = $this->getExpressList();
        return $express_list[$id];
    }
    /**
     * 根据快递公司ecode获得快递公司信息
     * @param $ecode string 快递公司编号
     * @return array 快递公司详情
     */
    public function getExpressInfoByECode($ecode){
        $ecode = trim($ecode);
        if (!$ecode){
            return array('state'=>false,'msg'=>'参数错误');
        }
        $express_list = $this->getExpressList();
        $express_info = array();
        if ($express_list){
            foreach ($express_list as $v){
                if ($v['e_code'] == $ecode){
                    $express_info = $v;
                }
            }
        }
        if (!$express_info){
            return array('state'=>false,'msg'=>'快递公司信息错误');
        } else {
            return array('state'=>true,'data'=>array('express_info'=>$express_info));
        }
    }

    /**
     *  post提交数据
     * @param  string $url 请求Url
     * @param  array $datas 提交的数据
     * @return url响应返回的html
     */
    function sendPost($url, $datas) {
        $temps = array();
        foreach ($datas as $key => $value) {
            $temps[] = sprintf('%s=%s', $key, $value);
        }
        $post_data = implode('&', $temps);
        $url_info = parse_url($url);
        if(empty($url_info['port']))
        {
            $url_info['port']=80;
        }
        $httpheader = "POST " . $url_info['path'] . " HTTP/1.0\r\n";
        $httpheader.= "Host:" . $url_info['host'] . "\r\n";
        $httpheader.= "Content-Type:application/x-www-form-urlencoded\r\n";
        $httpheader.= "Content-Length:" . strlen($post_data) . "\r\n";
        $httpheader.= "Connection:close\r\n\r\n";
        $httpheader.= $post_data;
        $fd = fsockopen($url_info['host'], $url_info['port'], $errno, $errstr, 10);
        fwrite($fd, $httpheader);
        $gets = "";
        $headerFlag = true;
        if ($fd) {
            while (!feof($fd)) {
                if (($header = @fgets($fd)) && ($header == "\r\n" || $header == "\n")) {
                    break;
                }
            }
            while (!feof($fd)) {
                $gets.= fread($fd, 128);
            }
            fclose($fd);
        }

        return $gets;
    }


    /**
     * Json方式 查询订单物流轨迹
     */
    function get_express($e_code , $shipping_code){
        //$true_code = $this->ship_code[$e_code];
        $cacheKey = 'express_list_'.$e_code.'.sn'.$shipping_code;
        $res = rkcache($cacheKey);
        if(!empty($res)) {
            return $res;
        }
        $shipping_codes = explode(',',$shipping_code);
        /*if($true_code ==''){
            return array();
        }*/
        $content = $this->queryTencent($e_code,trim($shipping_codes[0]));
        /*$requestData= "{'OrderCode':'','ShipperCode':'{$true_code}','LogisticCode':'{$shipping_code}'}";
        $datas = array(
            'EBusinessID' => EBusinessID,
            'RequestType' => '1002',
            'RequestData' => urlencode($requestData) ,
            'DataType' => '2',
        );
        //$datas['DataSign'] = $this->encrypt($requestData, AppKey);
        $datas['DataSign'] = urlencode(base64_encode(md5($requestData.AppKey)));
        $result=$this->sendPost(ReqURL, $datas);*/
        //$content = json_decode($result,true);
        if ($content['code'] != 'OK' || !is_array($content['list'])) {
              return array();
        }
        $traces = array();
        foreach($content['list'] as $k=>$v){
            $traces[$k]['time'] = $v['time'];
            $traces[$k]['context'] = $v['content'];
        }
        //已签收，永久缓存
        $expires = '3' == $content['state'] ? null : 4*60;
        wkcache($cacheKey,$traces,$expires);
        return $traces;
    }
    function get_express_bak($e_code , $shipping_code){
        $true_code = $this->ship_code[$e_code];
        $cacheKey = 'express'.$e_code.'.sn'.$shipping_code;
        $res = rcache($cacheKey);
        if(!empty($res)) return $res;
        if($true_code ==''){
            return array();
        }
        $requestData= "{'OrderCode':'','ShipperCode':'{$true_code}','LogisticCode':'{$shipping_code}'}";
        $datas = array(
            'EBusinessID' => EBusinessID,
            'RequestType' => '1002',
            'RequestData' => urlencode($requestData) ,
            'DataType' => '2',
        );
        //$datas['DataSign'] = $this->encrypt($requestData, AppKey);
        $datas['DataSign'] = urlencode(base64_encode(md5($requestData.AppKey)));
        $result=$this->sendPost(ReqURL, $datas);
        $content = json_decode($result,true);
        if ($content['Success'] != 'true' || !is_array($content['Traces'])) {
              return array();
        }
        $traces = array();
        foreach($content['Traces'] as $k=>$v){
            $traces[$k]['time'] = $v['AcceptTime'];
            $traces[$k]['context'] = $v['AcceptStation'];
        }
        //已签收，永久缓存
        $expires = '3' == $content['State'] ? 86400*30 : 0;
        wcache($cacheKey,$traces,'express',$expires);
        return array_reverse($traces);
    }
    function get_express_pro($e_code , $shipping_code){
        return $this->get_express($e_code , $shipping_code);
    }
    function get_express_pro_bak($e_code , $shipping_code){
        $true_code = $this->ship_code[$e_code];
        $shipping_code = trim($shipping_code);
        $cacheKey = 'express'.$e_code.'.sn'.$shipping_code;
        $res = rcache($cacheKey);
        if(!empty($res)) return $res;
        if($true_code ==''){
            return array();
        }
        $requestData= "{'OrderCode':'','ShipperCode':'{$true_code}','LogisticCode':'{$shipping_code}'}";
        $datas = array(
            'EBusinessID' => EBusinessID,
            'RequestType' => '8001',
            'RequestData' => urlencode($requestData) ,
            'DataType' => '2',
        );
        //$datas['DataSign'] = $this->encrypt($requestData, AppKey);
        $datas['DataSign'] = urlencode(base64_encode(md5($requestData.AppKey)));
        $result=$this->sendPost(ReqURL, $datas);
        $content = json_decode($result,true);
        if ($content['Success'] != 'true' || !is_array($content['Traces'])) {
              return array();
        }
        $traces = array();
        foreach($content['Traces'] as $k=>$v){
            $traces[$k]['time'] = $v['AcceptTime'];
            $traces[$k]['context'] = $v['AcceptStation'];
        }
        //已签收，永久缓存
        $expires = '3' == $content['State'] ? 86400*30 : 0;
        wcache($cacheKey,$traces,'express',$expires);
        return array_reverse($traces);
    }

    public function queryTencent($e_code , $shipping_code){
        $cacheKey = 'express_tencent_'.$e_code.'.sn'.$shipping_code;
        $res = rkcache($cacheKey);
        if(!empty($res)) {
            return $res;
        }

        // 云市场分配的密钥Id
        $secretId = 'AKIDhwb663ti474dr1l4sfdUxlv23rq5kcz0pzyx';
        // 云市场分配的密钥Key
        $secretKey = 'lISi5Z4W8S9gdIP53MYlixZB1oB4hmC8Xd7W44Up';
        $source = 'market';

        // 签名
        $datetime = gmdate('D, d M Y H:i:s T');
        $signStr = sprintf("x-date: %s\nx-source: %s", $datetime, $source);
        $sign = base64_encode(hash_hmac('sha1', $signStr, $secretKey, true));
        $auth = sprintf('hmac id="%s", algorithm="hmac-sha1", headers="x-date x-source", signature="%s"', $secretId, $sign);

        // 请求方法
        $method = 'GET';
        // 请求头
        $headers = array(
            'X-Source' => $source,
            'X-Date' => $datetime,
            'Authorization' => $auth,
        );
        // 查询参数
        $queryParams = array(
            'com' => '',
            'num' => $shipping_code,
        );
        // body参数（POST方法下）
        $bodyParams = array();
        // url参数拼接
        $url = 'https://service-ohohpvok-1300683954.gz.apigw.tencentcs.com/release/express';
        if (count($queryParams) > 0) {
            $url .= '?' . http_build_query($queryParams);
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array_map(function ($v, $k) {
            return $k . ': ' . $v;
        }, array_values($headers), array_keys($headers)));
        if (in_array($method, array('POST', 'PUT', 'PATCH'), true)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($bodyParams));
        }

        $error = false;
        $data = curl_exec($ch);
        if (curl_errno($ch)) {
            $error = curl_error($ch);
        }
        curl_close($ch);
        if ($error){
            return false;
        }
        $content = json_decode($data,true);
        $expires = '3' == $content['state'] ? null : 4*60;
        wkcache($cacheKey,$content,$expires);
        return $content;
    }

    /**
     * 电商Sign签名生成
     * @param data 内容
     * @param appkey Appkey
     * @return DataSign签名
     */
    function encrypt($data, $config) {
        return urlencode(base64_encode(md5($data.$config['userKey'])));
    }

    /**
     *
     * 根据快递公司id 和 快递单号，查询实时物流信息
     *
     */
    function get_express_from_ckd8($e_code, $logi_no) {
        $map = array();
        $e_code = empty($map[$e_code]) ? $e_code : $map[$e_code];
        if (empty($e_code)) {
            return false;
        }
        $time = time();
        $clientIp = '220.249.' . rand(1,254) . '.' . rand(1,254);
        $url = 'http://www.ckd8.com/open.php?tmp=0.5452092755585909';
        import('Curl');
        $curl = new Curl();
        $curl->setHeader('CLIENT-IP',$clientIp);
        $curl->setHeader('X-FORWARDED-FOR',$clientIp);
        $curl->setHeader('Host','www.ckd8.com');
        $curl->setHeader('Referer','http://www.ckd8.com/yundaex/?wd=');
        $curl->setHeader('X-Requested-With','XMLHttpRequest');
        $curl->setUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.106 Safari/537.36');
        $curl->post($url, $data);
        $content = $curl->response;
        $info = json_decode($content, true);
        $ret = array(
            'data' => array(),
            'usetime' => array(),
        );
        if ('1' == $info['status']) {
            $ttl = 1800;
            if ('3' == $info['state']) {
                //已签收，永久缓存
                $ttl = 86400*90;
            }
            $ret['data'] = $info['data'];
            foreach ($ret['data'] as $key => $value) {
                # code...
                $ret['data'][$key]['time'] = date('Y-m-d H:i:s', $value['time']);
            }
            $ret['usetime'] = $info['usetime'];
            $cacheObj->store($md5key, $ret, $ttl);
        }

        return $ret;
    }


    public function push_ship( $postData , $config ){
        $datas = array(
            'EBusinessID' => $config['userId'],
            'RequestType' => '1007',
            'RequestData' => urlencode($postData) ,
            'DataType' => '2'
        );
        $datas['DataSign'] = $this->encrypt($postData, $config);
        $ret = $this->sendPost(PushURL , $datas);
        return $ret;
    }

    public function trace_ship( $postData ){
        $url = "http://api.kdniao.com/api/dist";
        $datas = array(
            'EBusinessID' => EBusinessID ,
            'RequestType' => '8008',
            'RequestData' => urlencode($postData) ,
            'DataType' => '2'
        );
        $datas['DataSign'] = urlencode(base64_encode(md5($postData.AppKey)));
        $ret = $this->sendPost($url , $datas);
        return $ret;

    }

}
