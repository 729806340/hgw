<?php
/**
 * Author: Shen.L
 * Email: shen@shenl.com
 * Date: 2016/7/19
 * Time: 17:37
 */
require_once('CpsUnion.php');

class CpsZhongmin extends CpsUnion
{
    private $_config = array(
        'id' => 'zhongmin',
        'charset' => 'UTF-8',
        'limit_ip' => false, // 是否进行IP限制,true限制ip，false无限制ip
        'ip_list' => "127.0.0.3,127.0.0.2", // 允许访问的ip地址
        'is_sign' => false, // 是否进行签名验证,true签名验证，false不进行签名验证
        // 每一个接口都会存在这么一个值，需要跟相关的技术跟你沟通要得【此key为测试用，正式有活动ID后需要重新配置】
        'site_id' => "10050",
        'key' => "656290198bf73173772e5a18fd206b00",
        //'api_pre' => "http://www.zm123.com/comm/doTest.do",
        'api_pre' => "https://service.zm123.com/comm/doorder.ashx",
        'commissionRate' => array(
            // 格式 分类id=>比例
            1058 => 4.50,
            4 => 3.50,
            5 => 1.50,
            6 => 1.50,
            7 => 3.50,
            8 => 3.50,
            9 => 3.50,
            10 => 4.50,
            11 => 3.50,
            1057 => 3.50,
            1059 => 1.50,
            1060 => 1.50,
            1137 => 2.00,
            1138 => 2.00,
            1139 => 2.00,
            1140 => 2.00,
            1141 => 2.00,
            1142 => 2.00,
            1143 => 2.00,
            1061 => 9.00,
            1062 => 9.00,
            1110 => 4.50,
            1111 => 3.50,
            1112 => 1.50,
            1113 => 3.50,

        )
    );

    /**
     * 着陆页http://www.hangowa.com/index.php?act=cps&unionid=zhongmin&euid={euid}&target={target}
     * @return array
     */
    public function formatRequest()
    {
        $data = array(
            'unionid' => $this->_config['id'],
            'source' => '',
            'channel' => '',
            'cid' => '',
            'wi' => empty($_GET['euid']) ? '' : trim($_GET['euid']),
            'euid' => '',
        );
        return $data;
    }

    public function getConfig()
    {
        return $this->_config;
    }

    public function push($id)
    {
        if (is_array($id)) {
            $cps = $id;
        } else {
            $cps = Model('cps')->find($id);
        }
        $item = $this->renderItem($cps);
        import('Curl');
        $curl = new Curl();
        $curl->setOpt(CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        $curl->post($this->_config['api_pre'], $item);
        if ($curl->error) {
            Log::record('推送CPS：' . $cps['id'] . '推送URL：' . $this->_config['api_pre'] . "\n推送数据：" . json_encode($item) . 'Error: ' . $curl->errorCode . ';ErrorMessage: ' . $curl->errorMessage . "\n返回数据：" . $curl->response);
            return false;
        }
        return $curl->response!='-1';
    }

    public function access()
    {
        $userIp = $_SERVER["REMOTE_ADDR"];
        $config = $this->_config;
        if ($config['limit_ip']) {
            $arr = explode(',', $config['ip_list']);
            if (!in_array($userIp, $arr)) {
                return 'ip is limited!';
            }
        }
        if ($config['is_sign'] && $this->verifyRequest() == false) {
            return "sign is error!";
        }
        return 'pass';
    }

    public function getOrders()
    {
        $res = array();
        $orders = $this->getOrdersByRequest();
        foreach ($orders as $order) {
            $res[] = $this->renderItem($order);
        }
        return json_encode(array('orders' => $res));
    }

    /**
     * 根据请求获取订单数组
     * @return array
     */
    private function getOrdersByRequest()
    {
        $campaignId = strip_tags($_GET["cid"]);  // 活动id
        $startTime = intval($_GET["orderStartTime"]); // 下单起始时间
        $endTime = intval($_GET["orderEndTime"]); // 下单终止时间
        $status = intval($_GET["pay_status"]); // 下单终止时间
        $map = array(
            'types' => 'zhongmin',
        );
        if ($campaignId) $map['cid'] = $campaignId;
        if ($startTime) $map['createtime'] = array('gt', $startTime);
        if ($endTime) $map['createtime'] = array('lt', $startTime);
        if ($status) $map['orderstatus'] = $status;
        $orders = Model('cps')->where($map)->limit('0,1000')->select();
        //var_dump(Model('cps')->getLastSql());
        return $orders;
    }

    /**
     * 组装单个数据
     * @param $cps
     * @return array
     */
    protected function renderItem($cps)
    {
        $status = $cps['orderstatus'];
        $res = array();
        $res['orderid'] = $cps['order_sn'];
        $res['siteid'] = $this->_config['site_id'];
        $res['euid'] = $cps['wi'];
        $res['orderdate'] = date('Y-m-d H:i:s', $cps['createtime']);;
        $res['totalprice'] = $cps['order_money'];

        if (ORDER_STATE_PAY == $status || ORDER_STATE_SEND == $status || ORDER_STATE_SUCCESS == $status) {
            $res['status'] = 3;
        } else if (ORDER_STATE_CANCEL == $status) {
            $res['status'] = -1;
        } else {
            $res['status'] = 1;
        }

        $res['sign'] = strtolower(md5($res['orderid'] . $this->_config['site_id'] . $this->_config['key']));
        $goods_ids_arr = explode('|', rtrim($cps['goodsid'], '|'));
        //$goods_names_arr = explode('|', rtrim($cps['goodname'], '|'));
        $goods_prices_arr = explode('|', rtrim($cps['goodsprice'], '|'));
        $goods_nums_arr = explode('|', rtrim($cps['goodsint'], '|'));

        $comm = 0;
        foreach ($goods_ids_arr as $k => $v) {
            $cat = $this->getCategory(trim($v));
            // 根据cat计算佣金
            $comm += isset($this->_config['commissionRate'][$cat]) && $this->_config['commissionRate'][$cat] > 0
                ? ($this->_config['commissionRate'][$cat] * $goods_nums_arr[$k] * floatval($goods_prices_arr[$k]) / 100) : 0;
            /*$products[] = array(
                'productNo' => $goods_ids_arr[$k],
                'name' => $goods_names_arr[$k],
                'amount' => $goods_nums_arr[$k],
                'price' => floatval($goods_prices_arr[$k]),
                'category' => $cat > 0 ? $cat : 9,
                'commissionType' => ''
            );*/
        }
        $res['commission'] = $comm;
        $res['commissionRate'] = number_format($comm * 100 / $res['totalprice'], 2) . '%';
        //$res['Products'] = $products;
        return $res;
    }

    protected function renderItems()
    {

    }

    private function verifyRequest()
    {
        $arr = $_GET;
        unset($arr['mid']);
        ksort($arr);
        $get = '';
        foreach ($arr as $k => $v) {
            $get .= $k . '=' . $v . '&';
        }
        $get = substr($get, 0, -1);
        return $_GET['mid'] == md5($get);
    }


}