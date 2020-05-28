<?php
/**
 * Author: Shen.L
 * Email: shen@shenl.com
 * Date: 2016/7/19
 * Time: 17:37
 */
require_once('CpsUnion.php');

class CpsYiqifa extends CpsUnion
{
    private $_config = array(
        'id'=>'yiqifa',
        'campaign_id' => '18343', // 默认活动编号(cid)
        'channel' => 'cps', // 默认活动类型
        'charset' => 'UTF-8',
        'limit_ip' => false, // 是否进行IP限制,true限制ip，false无限制ip
        'ip_list' => "127.0.0.3,127.0.0.2", // 允许访问的ip地址
        'is_sign' => false, // 是否进行签名验证,true签名验证，false不进行签名验证
        // 每一个接口都会存在这么一个值，需要跟相关的技术跟你沟通要得【此interId为测试用，正式有活动ID后需要重新配置】
        'interId' => "55cc57e57c5cd0b77aa207ee",
        'api_pre' => "http://o.yiqifa.com/servlet/handleCpsInterIn"
    );

    public function formatRequest()
    {
        $data = array(
            'unionid' => $this->_config['id'],
            'source' => empty($_GET['source']) ? '' : trim($_GET['source']),
            'channel' => empty($_GET['channel']) ? $this->_config['channel'] : trim($_GET['channel']),
            'cid' => !is_numeric($_GET['cid']) ? $this->_config['campaign_id'] : trim($_GET['cid']),
            'wi' => empty($_GET['source']) ? '' : trim($_GET['wi']),
        );
        $cps['euid'] = $data['cid'];
        return $data;
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
        $data['interId'] = $this->_config['interId'];
        $data['json'] = "{\"orders\":[" . json_encode($item) . "]}";
        $data['encoding'] = $this->_config['charset'];
        import('Curl');
        $curl = new Curl();
        $curl->setOpt(CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        $curl->post($this->_config['api_pre'].'?'.http_build_query($data));
        if($curl->error){
            Log::record('推送CPS：'.$cps['id'].'推送URL：'.$this->_config['api_pre'].'?'.http_build_query($data).'Error: ' . $curl->errorCode . ': ' . $curl->errorMessage);
            return false;
        }
        return $curl->response=='0';
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
            $res[] = $this->renderItem($order,true);
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
            'types' => 'yiqifa',
        );
        if($campaignId) $map['cid'] = $campaignId;
        //if($startTime) $map['createtime'] = array('gt',$startTime);
        if(!$endTime||$endTime<$startTime) $endTime = time();
        $map['createtime'] = array('between',"$startTime,$endTime");
        if($status) $map['orderstatus'] = $status;
        $cps = Model('cps')->where($map)->limit('0,1000')->select();
        /** @var orderModel $orderModel */
        $orderModel = Model('order');
        $orders = $orderModel->getOrderList(array('order_id'=>array('in',array_column($cps,'order_id'))));
        $orders = array_under_reset($orders,'order_id');
        foreach ($cps as $k=>$v) {
            $cps[$k]['orderInfo'] = $orders[$v['order_id']];
        }
        //var_dump(Model('cps')->getLastSql());
        return $cps;
    }

    /**
     * 组装单个数据
     * @param $cps array
     * @param $allStatus boolean
     * @return array
     */
    protected function renderItem($cps,$allStatus=false)
    {
        $status = $cps['orderstatus'];
        $res = array();
        $res['orderNo'] = $cps['order_sn'];
        $res['campaignId'] = $cps['cid'];
        $res['feedback'] = $cps['wi'];
        $res['paymentType'] = '支付宝';
        $res['orderTime'] = date('Y-m-d H:i:s', $cps['createtime']);
        if (ORDER_STATE_PAY == $status) {
            $res['updateTime'] = date('Y-m-d H:i:s', $cps['paytime']);
            $res['orderStatus'] = 'active';
            $res['paymentStatus'] = '1';

            $res['fare'] = '8';
            $res['favorable'] = '8';
            $res['favorableCode'] = '';
        } else {
            $res['paymentStatus'] = '0';
        }
        if($allStatus){
            $statusArr = array(
                0=>'已取消',
                10=>'已下单',
                20=>'已支付',
                30=>'已发货',
                40=>'已完成',
            );
            if(isset($cps['orderInfo'],$cps['orderInfo']['order_state']))
            $res['orderStatus'] = $statusArr[$cps['orderInfo']['order_state']];
        }

        $goods_ids_arr = explode('|', rtrim($cps['goodsid'], '|'));
        $goods_names_arr = explode('|', rtrim($cps['goodname'], '|'));
        $goods_prices_arr = explode('|', rtrim($cps['goodsprice'], '|'));
        $goods_nums_arr = explode('|', rtrim($cps['goodsint'], '|'));

        $products = array();
        foreach ($goods_ids_arr as $k => $v) {
            $cat = $this->getCategory(trim($v));
            $products[] = array(
                'productNo' => $goods_ids_arr[$k],
                'name' => $goods_names_arr[$k],
                'amount' => $goods_nums_arr[$k],
                'price' => floatval($goods_prices_arr[$k]),
                'category' => $cat >0 ? $cat : 9,
                'commissionType' => ''
            );
        }
        $res['Products'] = $products;
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