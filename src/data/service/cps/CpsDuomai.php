<?php
/**
 * Author: Shen.L
 * Email: shen@shenl.com
 * Date: 2016/7/19
 * Time: 17:37
 */
require_once('CpsUnion.php');

class CpsDuomai extends CpsUnion
{
    private $_config = array(
        'id' => 'duomai',
        'campaign_id' => '1', // 默认活动编号(cid)
        'channel' => 'cps', // 默认活动类型
        'charset' => 'UTF-8',
        'limit_ip' => false, // 是否进行IP限制,true限制ip，false无限制ip
        'ip_list' => "127.0.0.3,127.0.0.2", // 允许访问的ip地址
        'is_sign' => false, // 是否进行签名验证,true签名验证，false不进行签名验证
        // 每一个接口都会存在这么一个值，需要跟相关的技术跟你沟通要得【此interId为测试用，正式有活动ID后需要重新配置】
        'hash' => "de96e21d11393c5e3d6002e1b9fcac3f",
        'api_pre' => "http://www.duomai.com/api/order.php?"
    );

    public function formatRequest()
    {
        $data = array(
            'unionid' => $this->_config['id'],
            'euid' => empty($_GET['euid']) ? '' : trim($_GET['euid']),
            'mid' => empty($_GET['mid']) ? '' : trim($_GET['mid']),
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
        $goodsId = substr($cps['goodsid'], 0, strlen($cps['goodsid']) - 1);
        $arr_goods = explode('|', $goodsId);
        /** 组装catid */
        $cat_id = '';
        foreach ($arr_goods as $k=>$v){
            if ($v != '|' && $v != '') {
                $tmp_id = $this->getCategory(trim($v));
                $cat_id .= $tmp_id . '|';
            }
        }
        $goods_cate = substr($cat_id, 0, strlen($cat_id) - 1);
        $goods_cate = ($goods_cate != '') ? $goods_cate : '1';

        // 可选参数
        /*$data['promotion_code'] = '';
        $data['is_new_custom'] = 0;
        $data['channel'] = 0;
        $data['rate'] = '';
        $data['commission'] = '';
        $data['commission_type'] = '';*/

        $url_data = 'hash=' . $this->_config['hash']
            . '&euid=' . $cps['euid']
            . '&order_sn=' . $cps['order_sn']
            . '&order_time=' . rawurlencode(date('Y-m-d H:i:s', $cps['createtime']))
            . '&orders_prices=' . $cps['order_money']
            . '&goods_id=' . $goodsId
            . '&goods_name=' . rawurlencode(substr($cps['goodname'], 0, strlen($cps['goodname']) - 1))
            . '&goods_price=' . substr($cps['goodsprice'], 0, strlen($cps['goodsprice']) - 1)
            . '&goods_ta=' . substr($cps['goodsint'], 0, strlen($cps['goodsint']) - 1)
            . '&goods_cate=' . $goods_cate
            //. '&test=1' /* 测试模式 */
            . '&status=' . $cps['orderstatus'];
        import('Curl');
        $curl = new Curl();
        $curl->setOpt(CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        $curl->get($this->_config['api_pre'] . $url_data);
        if($curl->error){
            Log::record('推送CPS：'.$cps['id'].'推送URL：'.$this->_config['api_pre'] . $url_data.' Error: ' . $curl->errorCode . ': ' . $curl->errorMessage);
            return false;
        }
        return $curl->response=='推送成功';
    }

    public function access()
    {
        return 'denied';
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
            'types' => 'yqf',
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
        $res['orderNo'] = $cps['order_sn'];
        $res['campaignId'] = $cps['cid'];
        $res['feedback'] = $cps['wi'];
        $res['paymentType'] = '支付宝';
        if (ORDER_STATE_PAY == $status) {
            $res['updateTime'] = date('Y-m-d H:i:s', $cps['paytime']);
            $res['orderStatus'] = 'active';
            $res['paymentStatus'] = '1';

            $res['fare'] = '8';
            $res['favorable'] = '8';
            $res['favorableCode'] = '';
        } else {
            $res['orderTime'] = date('Y-m-d H:i:s', $cps['createtime']);
            $res['paymentStatus'] = '0';
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
                'category' => $cat > 0 ? $cat : 9,
                'commissionType' => ''
            );
        }
        $res['Products'] = $products;
        return $res;
    }

    protected function renderItems()
    {

    }

}