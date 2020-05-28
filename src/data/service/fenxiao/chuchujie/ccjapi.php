<?php

class ccjapi {

    static $orgname = "JZDYiX15";//"wdt";

    static $appid = "948b4dea388cb101f92dfde4839060c8"; // "c3e62dd9393d3de5a4ced3ec461c0daf"; // appkey
    static $secret = "c9c1d036742805f9260f77ad256f4151fd9f893d"; //"71fa52623bf4f90885884c2278cc30629480d5c7"; // appsecret
    private $apiUri = "https://parter.api.chuchujie.com/";

    private $onlineDate = "2016-12-26 12:00:00"; // 上线日期
    public $token = "";

    function __construct() {
        import('Curl');
        $this->token = 'xxx';
//         $this->grantUrl = "https://open.mengdian.com/common/token?grant_type=client_credential"; // 获取token的URL
//         $this->router = 'https://open.mengdian.com/api/mname/WE_MALL/cname/';
//         if (! $this->readToken($re))
//             log::selflog('get access token faild', 'chuchujie');
    }
    
    // 获取订单列表
    function getOrderList($condition = array()) {
        $params = array(
            // 'status_refund' => '0', //退款状态:0	未退款 （可以发货）1	退款中 2	退款成功 3	退款关闭 （可以发货）
            'status' => 2,
            'ctime_start' => date('Y-m-d H:i:s', $condition['ctime_start']),
            'ctime_end' => date('Y-m-d H:i:s', $condition['ctime_end']),
            'page' =>1,
            'page_size' =>10
        );
        
        $result = $this->_sendRequest('sqe/Order/get_order_list_v2', $params);
        $order_data = array();
        $total_page = 1;
        if ($result['code'] == 0 && ! empty($result['max_page'])) {
            foreach ($result['info'] as $k => $detail) {
                // 不处理上线日期之前的订单
                if (strtotime($detail['order']['ctime']) < strtotime($this->onlineDate)) {
                    continue;
                }

                if ($detail['order']['status'] != 2) {
                    continue;
                }
                
                $order_data[] = $detail;
            }
            $total_page = $result['max_page'];
        }
        
        return array(
            $total_page,
            $order_data
        );
    }
    
    // 接口文档：http://open.mengdian.com/doc/apiarticle/tag/jc_doc
    function getOrderDetail($order_no) {
        $params['order_id'] = $order_no;
        $params = array(
            'page_no' => 1,
            'page_size' => 1
        );
        
        $result = $this->_sendRequest('sqe/Order/get_order_list_v2', $params);
        $order_data = array();
        $total_page = 1;
        if ($result['code'] == 0 && ! empty($result['max_page'])) {
            return $result['info'];
        } else {
        	return array();
        }
    }
    
    
    // 订单发货
    function pushShip($params) {
        $params = array(
            'oid' => $params['orderno'],
            'express_company' => $params['logi_code'] ,
            'express_no' => $params['logi_no'],
        );
        
        return $this->_sendRequest('sqe/Order/api_order_shipping_v2', $params);
    }
    
    // 维权订单列表
    function getRefundList($condition = array()) {
        $params = array(
            'start_time' => date('Y-m-d H:i:s', $condition['begin']),
            'end_time' => date('Y-m-d H:i:s', $condition['end']),
            'page_no' => $condition['page_no'],
            'page_size' => $condition['page_size']
        );
        
        $result = $this->_apiRefundList($params);
        
        $order_data = array();
        $total_page = 1;
        
        if ($result['code'] == 0 && ! empty($result['max_page'])) {
            foreach ($result['info'] as $k => $detail) {
                // 不处理上线日期之前的订单
                if (strtotime($detail['order']['ctime']) < strtotime($this->onlineDate)) {
                    continue;
                }
        
                $order_data[] = $detail;
            }
            $total_page = $result['max_page'];
        }
        
        return array(
            $total_page,
            $order_data
        );
    }
    
    // 维权售后单数据抓取（目前楚楚街没有单独的退款接口，只能将就用退款数据抓取接口）
    function _apiRefundList($conditon) {
        $params = array(
            'status_refund' => '0', //退款状态:0	未退款 （可以发货）1	退款中 2	退款成功 3	退款关闭 （可以发货）
            'start_time' => $conditon['start_time'],
            'end_time' => $conditon['end_time'],
            'page_no' => $conditon['page_no'] ? $conditon['page_no'] : 1,
            'page_size' => $conditon['page_size'] ? $conditon['page_size'] : 50
        );
        
        $result = $this->_sendRequest('sqe/Order/get_order_list_v2', $params);
        return $result;
    }

    function getApi($method) {
        return $this->router . $method . "?accesstoken=" . $this->token;
    }

    function getToken() {
        $data['appid'] = self::$appid;
        $data['secret'] = self::$secret;
        $res = self::curlGet($this->grantUrl, $data);
        return $res;
    }

    function setToken($token) {
        $this->token = $token;
    }
    
    // 从data目录读取token
    function readToken(&$re) {
        $retmsgSavePath = BASE_DATA_PATH . '/log/';
        $filename = $retmsgSavePath . "/mengdian_token.txt";
        $data = file_get_contents($filename);
        $re = json_decode($data, true);
        if (is_array($re) && isset($re['data']['access_token'])) {
            
            // 距过期时间还有半小时刷新token
            if (time() - $re['addtime'] < ($re['data']['expire_in'] - 30 * 60)) {
                $this->setToken($re['data']['access_token']);
                return true;
            }
        }
        
        $result = $this->getToken();
        if ($this->saveToken($result)) {
            $re = json_decode($result, true);
            if (is_array($re) && isset($re['data']['access_token'])) {
                $this->setToken($re['data']['access_token']);
                return true;
            }
        }
        
        return false;
    }
    
    // 保存token到data目录
    function saveToken($json) {
        $re = json_decode($json, true);
        if (is_array($re) && isset($re['data']['access_token'])) {
            $re['addtime'] = time();
            
            $retmsgSavePath = BASE_DATA_PATH . '/log/';
            $filename = $retmsgSavePath . "/mengdian_token.txt";
            touch($filename);
            $handle = fopen($filename, 'w');
            $data = json_encode($re);
            fwrite($handle, $data);
            fclose($handle);
            @chmod && @chmod($filename, 0744);
            return true;
        }
        return false;
    }

    private function _sendRequest($url_path, $param) {
        $url = $this->apiUri . $url_path . '?' . http_build_query($param);
        $curl = new Curl();
        $header_prams = array(
            'org_name' => self::$orgname,
            'app_key' => self::$appid,
            'nonce' => rand(10000, 999999),
            'timestamp' => time()
        );
        $curl->setHeader('Org-Name', $header_prams['org_name']);
        $curl->setHeader('App-Key', $header_prams['app_key']);
        $curl->setHeader('Nonce', $header_prams['nonce']);
        $curl->setHeader('Timestamp', $header_prams['timestamp']);
        $curl->setHeader('Signature', $this->_genSign($header_prams));
        
        $curl->setOpt(CURLOPT_SSL_VERIFYPEER, false);
        $curl->setOpt(CURLOPT_SSL_VERIFYHOST , false);
        $curl->setOpt(CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        $curl->get($url);
        if ($curl->error) {
            Log::record('楚楚街 HTTP 请求失败! Path:' . $path . ';Error:' . $curl->errorCode . ': ' . $curl->errorMessage);
            throw new Exception('楚楚街 HTTP 请求失败! Path:' . $path . ';Error:' . $curl->errorCode . ': ' . $curl->errorMessage);
        }
        $res = json_decode($curl->response, true);
        return $res;
    }

    private function _genSign($header_prams) {
        //签名:sha1(Nonce + AppSecret +Timestamp)
        $sign = sha1($header_prams['nonce'] . self::$secret . $header_prams['timestamp']);
        return $sign;
    }
}

?>