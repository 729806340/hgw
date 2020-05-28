<?php
defined('ByShopWWI') or exit('Access Invalid!');
class channelfillControl extends SystemControl
{
    private static $order_host = 'http://api.open.hangowa.com/order/index';
    private static $auth_host = 'http://api.open.hangowa.com/auth/index';
    private static $token = 'e0d1decdb60f5d1a2f19342d14fd9220';
    private static $secret = '5aa9e72e85274';
    private static $client_id = 19;
    private static $access_token = '';
    private static $page_size = 30;
    private static $page_count = 1;
    private static $goods_maps = array();
    private static $member_maps = array();
    private static $store_maps = array (
        'juanpi'        => 1, //卷皮
        'pinduoduo'     => 7, //拼多多-粮油店
        'beibeiwang'    => 9, //贝贝网
        'chuchujie'     => 10,//楚楚街
        'dangdang11'    => 11,//当当网
        'gegejia'       => 12,//格格家
        'huiguo'        => 13,//会过
        'jingdongfx'    => 14,//京东
        'mbyd1'         => 15,//麦宝云店
        'mengdian'      => 16,//萌店
        'renrenyoupin'  => 19,//人人优品
        'suningnonggu'  => 20,//苏宁
        'ylmg'          => 21,//云联美购
        'grsc'          => 22,//拼多多-果然商场
        'chuchutong'    => 23,//楚楚通
        'gegejia1'      => 24,//格格家-恩施
        'huiguogr'      => 25,//会过果然
        'mbyd2'         => 26,//脉宝2
        'mbyd3'         => 27,//脉宝3
        'Bdian'         => 29,//贝贝网2
        'linggou'       => 30,//零购
        'bdtm'          => 31,//贝店特卖
        'ccjhfhzyd'     => 32,//楚楚街-火凤凰专营店
        'pddcy'         => 33,//拼多多-火凤凰食品鲜专营店
        'sgsx'          => 34,//拼多多-易行九州水果生鲜专营店
        'qbz'           => 35,//楚楚帮
        'mengtui'       => 36,//萌推
        'mtsx'          => 37,//萌推生鲜
    );

    public function __construct(){
        parent::__construct();
        ini_set('max_execution_time', '0');
        import('Curl');
        self::$access_token = self::get_access_token();
        $member_fenxiao = $this->channels();
        tpl::output('member_fenxiao',$member_fenxiao);
        foreach ($member_fenxiao as $v) {
            self::$store_maps[$v['identifier']] = $v['id'];
        }
        self::$goods_maps = self::_get_goods_mapped();
        self::$member_maps =self::_get_member_mapped();
    }

    /*
     * 获取token
     */
    private static function get_access_token($force=false) {
        $access_token = rkcache('access_token_'. self::$client_id);
        if ($access_token&&!$force) {
            return $access_token;
        }
        $host = self::$auth_host;
        $param = array(
            'token' => self::$token,
            'secret' => self::$secret,
            'client_id' => self::$client_id,
        );
        $Curl = new Curl();
        $Curl->setJsonDecoder(function ($response) {
            return json_decode($response, true);
        });

        $Curl->setOpt(CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        $Curl->post($host , $param);
        if ($Curl->error) {
            Log::record('curl错误' . $host. ';Error:' . $Curl->errorCode . ': ' . $Curl->errorMessage);
            throw new Exception('curl错误' . $host . ';Error:' . $Curl->errorCode . ': ' . $Curl->errorMessage);
        }
        $res = $Curl->response;

        if ($res['errorCode'] != 1000) {
            Log::record('获取access_token错误:' . $host. ';Error:' . $Curl->errorCode . ': ' . $Curl->errorMessage);
            throw new Exception('获取access_token错误:' . $host . ';Error:' . $Curl->errorCode . ': ' . $Curl->errorMessage);
        }

        wkcache('access_token_'. self::$client_id, $res['access_token'], 24 * 60 * 60);
        return $res['access_token'];
    }

    //获取商品映射
    private static function _get_goods_mapped()
    {
        //todo 后面使用array_column 优化
        $result = array();
        $res = Model("b2c_category")->field('pid,fxpid,uid')->limit(false)->select();
        foreach( $res as $val ){
            $result[$val['uid']][$val['fxpid']] = $val['pid'];
        }
        return $result;
    }

    //shop_id => member_id
    private static function _get_member_mapped() {

        $res = rkcache('source_'. self::$client_id);
        if (empty($res)) {
            $model_member = Model("member");
            $condition = array(
                "member_name" => array('in', array_keys(self::$store_maps))
            );
            $r = $model_member->field('member_id,member_name')->where($condition)->select();
            $res = array_column($r, 'member_id', 'member_name');
            wkcache('source_'. self::$client_id, $res, 30*60);
        }

        $result = array();
        foreach ($res as $k=>$v) {
            $result[self::$store_maps[$k]] = $v;
        }
        return $result;

    }

    public function indexOp(){
        tpl::output('client_id',self::$client_id);
        tpl::output('distributor_trades','http://api.open.hangowa.com/distributor/trades');
        tpl::output('distributor_orders','http://api.open.hangowa.com/distributor/distributor_order_count');
        Tpl::setDirquna('shop');
        Tpl::showpage('channelfill.list');
    }

    public function get_channelorderOp()
    {
        $start_time = $_GET['start_time'] ? $_GET['start_time'] : date('Y-m-d H:i:s', time()-2*3600 );
        $end_time = $_GET['end_time'] ? $_GET['end_time'] : date('Y-m-d H:i:s', time());
        $id = $_GET['id'];

        $page = self::$page_count;
        $service = Service("Fenxiao");
        $result = array();
        //获取所有的店铺
        do {
            $res = $this->_getOrders($start_time, $end_time, $page, $id);
            if (isset($res['errorCode']) && $res['errorCode'] != 1000) {
                echo json_encode($res);die;
            }
            if (!empty($res['list'])) {
                $sns = $service->getSavedidByApiorderno(array_column($res['list'], 'distributor_order_sn'));
                $items = array();
                foreach ($res['list']  as $order) {
                    if (in_array($order['distributor_order_sn'], $sns)) {
                        continue;
                    }
                    if ($item = $this->_prepareOrder($order)) {
                        $items[] = $item;
                    }
                }

                if (!empty($items)) {
                    $res2 = $service->doCreateOrder($items, 0, true);
                    if (!empty($res2)) {
                        $result = array_merge($res2, $result);
                    }
                }
            }
        } while ($page = $res['next']);
        if (empty($result)) {
            $result = array('res' => '');
        } else {
            $count = count($result);
            array_unshift($result, "本次抓取到{$count}个漏单");
        }
        echo json_encode($result);die;
    }

    private function _getOrders($start_time, $end_time, $page, $id) {
        $host = self::$order_host;
        $curl = new Curl();
        $param = array(
            'access_token' => self::$access_token,
            'client_id' => self::$client_id,
            'start_time' => $start_time,//'2018-4-2 16:34:10',
            'end_time' => $end_time,//'2018-4-10 16:34:10',
            'status' => 20,
            'pagecount' => $page,
            'pagesize' => self::$page_size,
            'id' => $id,
        );
        $curl->setJsonDecoder(function ($response) {
            return json_decode($response, true);
        });
        $curl->setTimeout(60);//超时
        $curl->setOpt(CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        $curl->post($host , $param);
        if ($curl->error) {
            Log::record('curl错误' . $host. ';Error:' . $curl->errorCode . ': ' . $curl->errorMessage);
            return $curl->errorCode;
        }
        $res = $curl->response;
        if($res['errorCode'] == 1002&&$this->_forceCount<4){
            // 强制刷新Token
            $this->_forceCount +=1;
            static::get_access_token(true);
            return $this->_getOrders($start_time, $end_time, $page);
        }
        if ($res['errorCode'] != 1000) {
            Log::record('获取订单列表错误:' . $host. ';Error:' . $res['errorCode'] . ': ' . $res['msg']);
            return $res;
        }

        $result = array(
            'next' => ($res['order_info']['currentNum'] == self::$page_size) ? ($page + 1) : false,
        );
        unset($res['order_info']['currentNum']);
        $result['list'] = $res['order_info'];
        return $result;
    }

    //预处理订单
    private function _prepareOrder($source) {

        $goodsList = $source['goods_list'];
        $hasError = false;

        /* 处理异常商品结束 */
        $source = $this->_doBefore($source);

        $distributor_store_id = $source['distributor_store_id'];
        $member_id = self::$member_maps[$distributor_store_id];

        $goods_map = self::$goods_maps[$member_id];
        $items = array();
        $source_arr = array_flip(self::$store_maps);

        foreach ($goodsList as $goods) {
            if (isset($goods_map[$goods['distributor_sku_sn']]) && $goods_map[$goods['distributor_sku_sn']]) {
                $goods_id = $goods_map[$goods['distributor_sku_sn']];
            } else {
                //$this->_error($source['distributor_order_sn'], "分销商品:{$goods['sku_name']}，SKUID：{$goods['distributor_sku_sn']}没有映射", $member_id, $source_arr[$distributor_store_id]);
                $hasError = true;
                continue;
            }
            $items[] = array(
                'goods_id' => $goods_id,
                'name' => $goods['sku_name'],
                'num' => $goods['sku_num'],
                'price' => $goods['price'],
                'fxpid' => $goods['distributor_sku_sn'],
                'oid' => isset($goods['distributor_sku_sn']) ? $goods['distributor_sku_sn'] : $source['distributor_order_sn'],

            );
        }

        if (empty($items) || $hasError){
            return false;
        }


        //区域匹配
        if(!isset($source['receive_info']['provinceName']) || empty($source['receive_info']['provinceName'])){
            //$this->_error($source['distributor_order_sn'], "分销订单 ({$source['distributor_order_sn']}) 的收货地址省份信息获取失败，地区数据", $member_id, $source_arr[$distributor_store_id]);
            return false;
        }
        if(!isset($source['receive_info']['cityName']) || empty($source['receive_info']['cityName'])){
            //$this->_error($source['distributor_order_sn'], "分销订单 ({$source['distributor_order_sn']}) 的收货地址城市信息获取失败，地区数据", $member_id, $source_arr[$distributor_store_id]);
            return false;
        }

        $detail = array();
        $detail['order_sn'] = $source['distributor_order_sn']; // 分销系统订单编号
        $detail['buy_id'] = $member_id; // 分销商用户编号
        $detail['receiver'] = $source['receive_info']['receiver_name']; // 收件人
        $detail['provine'] = $source['receive_info']['provinceName'];
        $detail['city'] = $source['receive_info']['cityName'];
        $detail['area'] = $source['receive_info']['districtName'];
        $detail['address'] = $source['receive_info']['receiver_address'];
        $detail['mobile'] = $source['receive_info']['receiver_phone']; // 手机号码
        $detail['remark'] = $source['receive_info']['order_remark'];
        $detail['payment_code'] = 'fenxiao';
        $detail['order_time'] = $source['pay_time']; // 下单时间，时间戳
        $detail['item'] = $items;
        $detail['amount'] = ncPriceFormat($source['payable_amount']);
        $detail['platform'] = 'new';
        $detail['shipping_fee']=$source['freight'];//运费
        $detail = $this->_doAfter($source , $detail);
        return $detail;
    }

    private function _doAfter($source , $detail){
        //处理格格家分销子渠道
        if($source['distributor_id'] == 6){
            $detail['distribution_channel'] = $this->_getOrderType($source['distribution_channel']);
        }
        return $detail;
    }


    private function _doBefore($source){

        //处理详细地址包含省市区
        $area = explode('|', $source['receive_info']['receiver_district']);
//        if (count($area) != 3 || !$area[0] || !$area[1] || !$area[2]) {
//            $source['receive_info']['provinceName'] = '';
//            $source['receive_info']['cityName'] = '';
//            $source['receive_info']['districtName'] = '';
//            return $source;
//        }
        $source['receive_info']['provinceName'] = $area[0];
        $source['receive_info']['cityName'] = $area[1];
        $source['receive_info']['districtName'] = $area[2];
        $source['receive_info']['receiver_address']  = str_replace($source['receive_info']['provinceName'] ,'',$source['receive_info']['receiver_address']);
        $source['receive_info']['receiver_address']  = str_replace($source['receive_info']['cityName'] ,'',$source['receive_info']['receiver_address']);
        $source['receive_info']['receiver_address']  = str_replace($source['receive_info']['districtName'] ,'',$source['receive_info']['receiver_address']);
        return $source;
    }

    public function channels(){
        $host = 'http://api.open.hangowa.com/distributor/index';
        $curl  = new Curl();
        $param = array(
            'access_token' => self::$access_token,
            'client_id' => self::$client_id,
        );
        $curl->setJsonDecoder(function ($response) {
            return json_decode($response, true);
        });
        $curl->setTimeout(60);//超时
        $curl->setOpt(CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        $curl->post($host , $param);
        if ($curl->error) {
            Log::record('curl错误' . $host. ';Error:' . $curl->errorCode . ': ' . $curl->errorMessage);
            throw new Exception('curl错误' . $host . ';Error:' . $curl->errorCode . ': ' . $curl->errorMessage);
        }
        $res = $curl->response;
        if($res['errorCode'] == 1002&&$this->_forceCount<4){
            // 强制刷新Token
            $this->_forceCount +=1;
            static::get_access_token(true);
            return $this->channels();
        }
        if ($res['errorCode'] != 1000) {
            Log::record('获取店铺错误:' . $host. ';Error:' . $res['errorCode'] . ': ' . $res['msg']);
            throw new Exception('获取店铺错误:' . $host . ';Error:' . $res['errorCode'] . ': ' . $res['msg']);
        }
        return $res['distributor'];
    }
}