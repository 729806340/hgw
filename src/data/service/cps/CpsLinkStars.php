<?php
/**
 * Author: Shen.L
 * Email: shen@shenl.com
 * Date: 2016/7/19
 * Time: 17:37
 */
require_once('CpsUnion.php');

class CpsLinkStars extends CpsUnion
{
    private $_config = array(
        'id'=>'linkStars',
        'campaign_id' => '18343', // 默认活动编号(cid)
        'channel' => 'cps', // 默认活动类型
        'charset' => 'UTF-8',
        'limit_ip' => false, // 是否进行IP限制,true限制ip，false无限制ip
        'ip_list' => "127.0.0.3,127.0.0.2", // 允许访问的ip地址
        'is_sign' => false, // 是否进行签名验证,true签名验证，false不进行签名验证
        // 每一个接口都会存在这么一个值，需要跟相关的技术跟你沟通要得【此interId为测试用，正式有活动ID后需要重新配置】
        'secret' => "fbdb8297ce9b37683e9249178004aecd",
        'api_pre' => "https://www.linkstars.com/"
    );


    public function formatRequest()
    {
        $data = array(
            'unionid' => $this->_config['id'],
            'source' => '',
            'channel' => '',
            'cid' => '',
            'euid' => '',
            'wi' => empty($_GET['feedback']) ? '' : trim($_GET['feedback']),
        );
        return $data;
    }


    /**
     * @param bool $direct
     * @return string
     */
    public function redirect($direct=true)
    {
        $redirect = !empty($_GET['to'])?$_GET['to']:SHOP_SITE_URL;
        if ($direct) redirect($redirect);
        return $redirect;
    }

    public function getConfig()
    {
        return $this->_config;
    }
    public function push($id)
    {
        if(is_array($id)){
            $cps = $id;
        }else{
            $cps = Model('cps')->find($id);
        }
        $item = $this->renderItem($cps);
        $order = json_encode($item);
        $data['key'] = md5($this->_config['secret'].$order);
        $data['order'] = json_encode($item);

        //$data['encoding'] = $this->_config['charset'];
        import('Curl');
        $curl = new Curl();
        $curl->setOpt(CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        $curl->post($this->_config['api_pre'].'api/adv/cps/order.php',$data);
        if($curl->error){
        Log::record('推送CPS：'.$cps['id'].'推送URL：'.$this->_config['api_pre']."api/adv/cps/order.php\nPOST:".json_encode($data)."\nError: " . $curl->errorCode . ': ' . $curl->errorMessage."\nResponse:". json_encode($curl->response));
            return false;
        }
        return $curl->response->code==1;
    }
    public function access()
    {
        $userIp = $_SERVER["REMOTE_ADDR"];
        $config = $this->_config;
        if($config['limit_ip']){
            $arr = explode(',',$config['ip_list']);
            if(!in_array($userIp,$arr)){
                return 'ip is limited!';
            }
        }
        if($config['is_sign'] && $this->verifyRequest() == false){
                return "sign is error!";
        }
        return 'pass';
    }

    public function getOrders()
    {
        $res = array();
        $orders = $this->getOrdersByRequest();
        foreach ($orders as $order)
        {
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
            'types' => 'yqf',
        );
        if($campaignId) $map['cid'] = $campaignId;
        if($startTime) $map['createtime'] = array('gt',$startTime);
        if($endTime) $map['createtime'] = array('lt',$startTime);
        if($status) $map['orderstatus'] = $status;
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
        $res['feedback'] = $cps['wi'];
        $res['order_number'] = $cps['order_sn'];
        $res['order_time'] = $cps['createtime'];
        $res['order_price'] = $cps['order_money'];
        $res['order_commission_type'] = '';
        $res['coupon_number'] = '';
        if (ORDER_STATE_PAY == $status||ORDER_STATE_SEND == $status||ORDER_STATE_SUCCESS == $status) {
            $res['order_status'] = 2;
        } else if (ORDER_STATE_CANCEL == $status) {
            $res['order_status'] = -1;
        } else {
            $res['order_status'] = 0;
        }

        $goods_ids_arr = explode('|', rtrim($cps['goodsid'], '|'));
        $goods_names_arr = explode('|', rtrim($cps['goodname'], '|'));
        $goods_prices_arr = explode('|', rtrim($cps['goodsprice'], '|'));
        $goods_nums_arr = explode('|', rtrim($cps['goodsint'], '|'));
        $res['count'] = array_sum($goods_nums_arr);
        $order_commission=0;
        $products = array();
        foreach ($goods_ids_arr as $k => $v) {
            $cat = $this->getCategory(trim($v));
            $cat = $cat >0 ? $cat : 5;
            $rate = $this->getCommissionRate($cat);
            $commission = round($goods_prices_arr[$k]*$goods_nums_arr[$k]*$rate)/100;
            $order_commission += $commission;
            $products[] = array(
                'goods_id' => $goods_ids_arr[$k],
                'goods_name' => $goods_names_arr[$k],
                'goods_count' => $goods_nums_arr[$k],
                'goods_price' => floatval($goods_prices_arr[$k]),
                //'category' => $cat >0 ? $cat : 9,
                'goods_commission_type' => $cat,
                'goods_commission_rate' => $rate/100,
                'goods_commission' => $commission,
            );
        }
        $res['goods'] = $products;
        $res['order_commission'] = $order_commission;
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
        foreach($arr as $k=>$v){
            $get.= $k.'='.$v.'&';
        }
        $get = substr($get,0,-1);
        return $_GET['mid'] == md5($get);
    }


}