<?php
defined('ByShopWWI') or exit('Access Invalid!');
class channelControl extends BaseCronControl {

    private static $order_host = 'http://api.open.hangowa.com/order/index';
    private static $refund_host = 'http://api.open.hangowa.com/refund/index';
    private static $auth_host = 'http://api.open.hangowa.com/auth/index';
    private static $store_host = 'http://api.open.hangowa.com/store/index';
    private static $ship_host =  'http://api.open.hangowa.com/shipping/';
    private static $token = 'e0d1decdb60f5d1a2f19342d14fd9220';
    private static $secret = '5aa9e72e85274';
    private static $client_id = 19;
    private static $access_token = '';
    private static $page_size = 500;
    private static $page_count = 1;
    private static $goods_maps = array();
    private static $member_maps = array();
    private static $more_pushs  = array(223223,238667,240538,241485,241718,241833,247435);  //12 24 (格格家) 13 25 (会过)
    private static $push_by_jst = array(223223,238667,240538,241485,241718,233280,239004,240571,239563,241833,247435);
    const TASK_PREFIX = 'task_fenxiao_channel_';
    const TASK_TIMEOUT = 60;
    private $_taskPid = null;

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
        'newgege'          => 38,//新格格家
        'bkyp'          => 39,//贝壳优品
        'YTYP'          => 40,//樱桃优品
        'hfhmbyd'          => 41,//火凤凰脉宝云店
        'lepingou'          => 42,//苏宁乐拼购
        'youtuan'          => 43,// 贝贝网友团
        'GZG'          => 45,// 公主购
    );

    private $_forceCount = 0; // 强制刷新Token次数

    public function __construct() {
        ini_set('memory_limit','8G');
        ini_set('max_execution_time', '0');
        import('Curl');
        parent::__construct();
        self::$access_token = self::get_access_token();
        self::$goods_maps = self::_get_goods_mapped();
        self::$member_maps =self::_get_member_mapped();

    }

    public function indexOp() {
        $this->orderlistOp();
    }

    //发货控制器
    public function shipOp(){

        if(!$this->_lock('ship')) {
            return false;
        }
        $condition['jst_status'] = 0;
        $sendorder_record = Model("sendorder_record");
        $orderdata = $sendorder_record->getsendorder($condition,null,'','*',300);
        if(!$orderdata){ return false; }
        $orderFxOrderNo = array_under_reset($orderdata ,'fx_order_id');
        $orderFxOrderNos = array_keys($orderFxOrderNo);
        $sub_condition = array(
            'orderno'=>array('in' , $orderFxOrderNos),
        );
        $order_sub = Model('b2c_order_fenxiao_sub')->field('orderno,oid,num,product_id')->where($sub_condition)->limit(false)->select();
        foreach ($order_sub as $k =>$v) {
            if(!$this->_lock('ship')) {
                return false;
            }
            $data = array();
            $data['source']     =  $orderFxOrderNo[$v['orderno']]['source'];
            $data['sourceid']   =  $orderFxOrderNo[$v['orderno']]['sourceid'];
            $data['orderno']    =  $orderFxOrderNo[$v['orderno']]['fx_order_id'];
            $data['logi_no']    =  $orderFxOrderNo[$v['orderno']]['shipping_code'];
            $data['express_id'] =  $orderFxOrderNo[$v['orderno']]['shipping_express_id'];
            $data['num']        =  $v['num'];
            $data['full_ship']  =  0;
            $data['oid'] =  $v['oid'];
            $condition = array('id'=>$orderFxOrderNo[$v['orderno']]['id']);
            $isExits = in_array($data['sourceid'] , self::$push_by_jst);
            $this->_forceCount = 0;
            if($res = $this->_sendShip($data)){
                $update = $isExits==true?array('order_status'=>1,'jst_status'=>1,'send_time'=>time()):array('jst_status'=>1,'send_time'=>time());
                Model("sendorder_record")->where($condition)->update($update);
            }else{
                $update = $isExits==true?array('order_status'=>2,'jst_status'=>2 ,'send_time'=>time()):array('jst_status'=>2,'send_time'=>time());
                Model("sendorder_record")->where($condition)->update($update);
            }
        }
        return true;

    }

    /**
     * 根据名称锁定任务，
     * @param $name
     * @return bool 锁定成功返回true，锁定失败返回false
     */
    private function _lock($name){

        // 加锁，判断系统是否允许、是否有其他锁
        $taskName = static::TASK_PREFIX;
        $taskName .= $name;
        $time = time();
        if ($this->_taskPid === null) $this->_taskPid = $time;

        //$scheduler = $this->_schedulerModel->findById($taskName);
        $scheduler = rkcache($taskName);
        if (!$scheduler) {
            return wkcache($taskName,array(
                'lock' => $time,
                'pid' => $this->_taskPid,
            ));
        }
        // 若是自己锁定的，则更新时间
        if ($scheduler['pid'] === $this->_taskPid) {
            $scheduler['lock'] = $time;
            return wkcache($taskName,$scheduler);
        }
        // 判断其他锁，则判断是否超时
        if ($time - $scheduler['lock'] < static::TASK_TIMEOUT) return false;
        return wkcache($taskName,array(
            'lock' => $time,
            'pid' => $this->_taskPid,
        ));
    }

    //老渠道发货，即将废弃
    public function  sendOrderOp(){
        $condition['order_status'] = 0;
        $sendorder_record = Model("sendorder_record");
        $condition['sourceid'] = array('not in',self::$push_by_jst);
        $orderdata = $sendorder_record->getsendorder($condition,null,'','*',100);
        if(!$orderdata){
            return false;
        }
        foreach( $orderdata as $val ){
            $orderinfo = unserialize( $val['order_info'] );
            //会过 格格家的只用新渠道
            if (in_array($orderinfo['buyer_id'], array(223223,238667,240538,241485))) {
                continue;
            }

            if( $orderinfo['buyer_id'] =="194379" || $orderinfo['buyer_id'] =="233577" ) {
                $where1['order_id'] = $orderinfo['order_id'];
                $where1['goods_id'] = array("in", '102787,102877');
                $num = Model("order_goods")->where($where1)->count();
                if ($num > 0) {
                    $where4['source']='pinduoduo';
                    $where4['id']=$val['id'];
                    Model("sendorder_record")->where($where4)->update(array('order_status'=>4));
                    continue;
                }
            }
            $val['shipping_code'] = explode(',',$val['shipping_code']);
            $val['shipping_code'] = $val['shipping_code'][0];
            $res = Model("order")->setOrderSend(unserialize($val['order_info']),$val['shipping_express_id'],$val['shipping_code']);
            if($res['state'] === false){
                $result=Model("sendorder_record")->updateErrorStatus($val['id']);
                if(!$result){
                    $this->log("_sendOrder方法修改发货错误状态失败");
                    continue;
                }
                continue;
            }
            $result=Model("sendorder_record")->updateStatus($val['id']);
            if(!$result){
                $this->log("_sendOrder方法修改发货状态失败");
                continue;
            }
        }
        return true;
    }

    public function refundOp()
    {
        // TODO 抓取退款
        /** @var FenxiaoService $service */
        $service = Service("Fenxiao");
        $p = 1;
        $start_time = $_GET['begin'] ? $_GET['begin'] : date('Y-m-d H:i:s', time()-2*3600 );
        $end_time = $_GET['end'] ? $_GET['end'] : date('Y-m-d H:i:s', time());
        //v($start_time);

        if($_GET['today']){
            $start_time = date('Y-m-d H:i:s', time()-24*3600*$_GET['today'] );
            $end_time = date('Y-m-d H:i:s', time());
        }

        do {
            $this->_forceCount = 0;
            $res = $this->_getRefunds($start_time,$end_time,$p);
            //v($res,0);
            if (!empty($res)) {
                // TODO 实现 prepare 和 filter
                $items = $this->_prepareRefund($this->_filterRefunds($res['list']),$service);
                if (!empty($items)) {
                    $service->createRefund(array('new' => $items));
                }
            }
        } while ($p = $res['next']);
        return true;
    }


    /**
     * 过滤不需要处理的退款
     * @param $items array
     * @return array
     */
    private function _filterRefunds($items)
    {
        $refunds = array();
        /** 若订单未发货，但是部分退款，剔除 */
        $fxIds = array_column($items, 'distributor_refund_sn');
        /** @var orderModel $orderModel */
        $orderModel = Model('order');
        $payOrders = $orderModel->getOrderList(array('fx_order_id' => array('in', $fxIds),
            'order_state' => ORDER_STATE_PAY));
        $orders = $orderModel->getOrderList(array('fx_order_id' => array('in', $fxIds)));
        $rel = array_column($payOrders, 'order_amount', 'fx_order_id');
        $orderIdRel = array_column($orders, 'order_id', 'fx_order_id');

        /** 将退款格式转换未二级格式【order=》【goods=》【】】】 */
        foreach ($items as $item) {

            $distributor_store_id = $item['distributor_store_id'];
            $member_id = self::$member_maps[$distributor_store_id];

            $goods_map = self::$goods_maps[$member_id];
            $source_arr = array_flip(self::$store_maps);

            // 处理商品映射关系
            if (!isset($goods_map[$item['distributor_sku_sn']]) || empty($goods_map[$item['distributor_sku_sn']])) {
                //echo "分销商品 ({$item['distributor_sku_sn']}) 没有配置商品映射，无法生成退款";
                $this->_error($item['distributor_refund_sn'], "分销商品 ({$item['distributor_sku_sn']}) 没有配置商品映射，无法生成退款");
                continue;
            }
            $item['goods_id'] = $goods_map[$item['distributor_sku_sn']];
            $item['order_id'] = $orderIdRel[$item['distributor_refund_sn']];
            if (!isset($refunds[$item['distributor_refund_sn']])) $refunds[$item['distributor_refund_sn']] = array();
            $refunds[$item['distributor_refund_sn']][$item['goods_id']] = $item;
        }

        foreach ($refunds as $refund_sn=>$refund) {
            $refundAmount = 0;
            if (!isset($rel[$refund_sn])) continue;
            foreach ($refund as $key=>$item) {
                $refundAmount += $item['refund_amount']; // 5
            }
            $refundRate = $rel[$refund_sn]/$refundAmount;  // 10/5
            foreach ($refund as $key=>$item) {
                $item['refund_amount'] = $item['refund_amount']*$refundRate; // 5*2
            }
        }
        if (!empty($payOrders)) {
            foreach ($rel as $fxOrderId => $order_amount) {
                echo "过滤{$fxOrderId}\n";

                $refund_total = array_sum(array_column($refunds[$fxOrderId], 'money'));
                echo "退款金额{$refund_total}\n";
                echo "订单金额{$order_amount}\n";

                if (ncPriceFormat($refund_total) != ncPriceFormat($order_amount)) {
                    unset($refunds[$fxOrderId]);
                    $this->_error($fxOrderId, "未发货分销订单不是全额退款，无法生成退款");
                } else {
                    //全额退款商品有多个时，只提交一次退款
                    if (count($refunds[$fxOrderId]) > 1) {
                        $tmp_key = current(array_keys($refunds[$fxOrderId]));
                        $tmp_value = current(array_values($refunds[$fxOrderId]));
                        $refunds[$fxOrderId] = array($tmp_key => $tmp_value);
                    }
                }
            }
        }

        return $refunds;
    }

    /**
     * 准备退款数据
     * @param $source array
     * @param $service FenxiaoService
     * @return bool|array
     */
    private function _prepareRefund($items,$service)
    {
        //过滤掉非全额退款订单，不做处理
        if (empty($items)) return array();
        $order_sns = array_keys($items);

        $new_fsmodel = Model("b2c_order_fenxiao_sub");
        $condition['orderno'] = array('in', $order_sns);
        $re = $new_fsmodel->where($condition)->select();
        $result = $re ? $re : array();
        $newRefund = array();
        $returnModel = Model('refund_return');
        foreach ($result as $suborder) {
            $orderno = $suborder['orderno'];
            $goods_id = $suborder['product_id'];

            //匹配未付款子订单
            if (isset($items[$orderno][$goods_id])) {

                $ordersn = $service->_getFxorderSn($orderno, $goods_id);
                if (!$ordersn) continue;
                //检查子订单是否已申请退款或取消订单
                $filter = array();
                $filter['order_sn'] = $ordersn;
                $filter['goods_id'] = array('in', array(0, $goods_id));
                if ($returnModel->where($filter)->count() > 0) {

                    //echo "商品已申请<br>";
                    continue;
                }

                $data = array();
                $data['reason_id'] = 100; //退款退货理由 整型

                $data['refund_type'] = $items[$orderno][$goods_id]['return_type']?2:1;
                $data['return_type'] = $items[$orderno][$goods_id]['return_type']?2:1;

                $data['seller_state'] = 1; //卖家处理状态:1为待审核,2为同意,3为不同意,默认为1
                $data['refund_amount'] = $items[$orderno][$goods_id]['refund_amount'];//退款金额
                $data['goods_num'] = isset($items[$orderno][$goods_id]['sku_num']) ? $items[$orderno][$goods_id]['sku_num'] : $items[$orderno][$goods_id]['sku_num'];//商品数量
                $data['buyer_message'] = $items[$orderno][$goods_id]['reason'];  //用户留言信息
                $data['ordersn'] = $ordersn;  //汉购网订单编号
                $data['goods_id'] = $suborder['product_id']; //商品编号
                $data['create_time'] = strtotime($items[$orderno][$goods_id]['create_time']);  //售后订单产生时间
                $newRefund[] = $data;
            }
        }
        return $newRefund;
    }

    //发货
    private function _sendShip($data)
    {
        $host = self::$ship_host;
        $curl = new Curl();
        $memberMaps = array_flip(self::$member_maps);
        $data = $this->_doPushBefore($data);
        $param = array(
            'access_token' => self::$access_token,
            'client_id'    => self::$client_id,
            'store_id'     => $memberMaps[$data['sourceid']],
            'sku_id'       => $data['oid'],
            'order_sn'     => $data['orderno'],
            'express_id'   => $data['express_id'],
            'express_no'   => $data['logi_no'],
        );
        $curl->setJsonDecoder(function ($response) {
            return json_decode($response, true);
        });
        $curl->setOpt(CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        $curl->post($host , $param);
        if ($curl->error) {
            Log::record('curl错误' . $host. ';Error:' . $curl->errorCode . ': ' . $curl->errorMessage);
            throw new Exception('curl错误' . $host . ';Error:' . $curl->errorCode . ': ' . $curl->errorMessage);
        }
        $result = $curl->response;

        if($result['errorCode'] == 1002&&$this->_forceCount<4){
            sleep($this->_forceCount*$this->_forceCount);
            // 强制刷新Token
            $this->_forceCount +=1;
            static::get_access_token(true);
            return $this->_sendShip($data);
        }
        if ($result['errorCode'] != 1000) {
            $arr = array();
            $arr['orderno'] = $data['orderno'];
            $arr['error'] = ($result['msg'] ? $result['msg'] : '暂无'). $result['errorCode'];
            $arr['log_time'] = time();
            $arr['sourceid'] = $data['sourceid'];
            $arr['source'] = $data['source'];
            $arr['log_type'] = 'ship';
            Model('b2c_order_fenxiao_error')->insert($arr);
            return false;
        }

        return true;

    }

    private function _doPushBefore( $data ){
        //除了会过和格格家的物流编号不处理，其它的都要处理
        /*if(!in_array($data['sourceid'],self::$more_pushs)){
            $data['logi_no'] = explode(',',$data['logi_no']);
            $data['logi_no'] = $data['logi_no'][0];
        }*/
        return $data;
    }

    /**
     * 获取渠道订单列表，并保存
     */
    public function orderlistOp() {

        $start_time = $_GET['begin'] ? $_GET['begin'] : date('Y-m-d H:i:s', time()-2*3600 );
        $end_time = $_GET['end'] ? $_GET['end'] : date('Y-m-d H:i:s', time());

        if($_GET['today']){
            $start_time = date('Y-m-d H:i:s', time()-24*3600*$_GET['today'] );
            $end_time = date('Y-m-d H:i:s', time());
        }
        /*$start_time = '2018-3-14 11:50:50';
        $end_time = '2018-4-13 11:50:50';*/

        $page = self::$page_count;
        $service = Service("Fenxiao");
        //获取所有的店铺
        do {
            $this->_forceCount = 0;
            $res = $this->_getOrders($start_time, $end_time, $page);
            if (!empty($res['list'])) {
                $sns = $service->getSavedidByApiorderno(array_column($res['list'], 'distributor_order_sn'));
                $items = array();
                foreach ($res['list']  as $order) {
                    if (in_array($order['distributor_order_sn'], $sns)) {
                        continue;
                    }
                    // 零购 2018-11-7 10:00 前的不要
                    if ($order['distributor_store_id'] == 30 && $order['create_time'] < strtotime('2018-11-7 10:00:00')) {
                        continue;
                    }
                    if ($item = $this->_prepareOrder($order)) {
                        $items[] = $item;
                    }
                }

                if (!empty($items)) {
                    $service->doCreateOrder($items);
                }
            }
        } while ($page = $res['next']);
    }

    /*
     * 获取订单
     */
    private function _getOrders($start_time, $end_time, $page) {
        $host = self::$order_host;
        $curl = new Curl();
        $param = array(
            'access_token' => self::$access_token,
            'client_id' => self::$client_id,
            'start_time' => $start_time,//'2018-4-2 16:34:10',
            'end_time' => $end_time,//'2018-4-10 16:34:10',
            'status' => 20,
            'pagecount' => $page,
            'pagesize' => self::$page_size
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
            return $this->_getOrders($start_time, $end_time, $page);
        }
        if ($res['errorCode'] != 1000) {
            Log::record('获取订单列表错误:' . $host. ';Error:' . $res['errorCode'] . ': ' . $res['msg']);
            throw new Exception('获取订单列表错误:' . $host . ';Error:' . $res['errorCode'] . ': ' . $res['msg']);
        }

        $result = array(
            'next' => ($res['order_info']['currentNum'] == self::$page_size) ? ($page + 1) : false,
        );
        unset($res['order_info']['currentNum']);
        $result['list'] = $res['order_info'];
        return $result;
    }
    private function _getRefunds($start_time, $end_time, $page) {
        $host = self::$refund_host;
        $curl = new Curl();
        $param = array(
            'access_token' => self::$access_token,
            'client_id' => self::$client_id,
            'start_time' => $start_time,//'2018-4-2 16:34:10',
            'end_time' => $end_time,//'2018-4-10 16:34:10',
            'state' => 0,
            'pagecount' => $page,
            'pagesize' => self::$page_size
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
            return $this->_getRefunds($start_time, $end_time, $page);
        }
        if ($res['errorCode'] != 1000) {
            Log::record('获取退款单列表错误:' . $host. ';Error:' . $curl->errorCode . ': ' . $curl->errorMessage);
            throw new Exception('获取退款单列表错误:' . $host . ';Error:' . $curl->errorCode . ': ' . $curl->errorMessage);
        }

        $result = array(
            'next' => ($res['currentNum'] == self::$page_size) ? ($page + 1) : false,
        );
        unset($res['currentNum']);
        $result['list'] = $res['refund_list'];
        return $result;
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

    //获取所有店铺
    private function _getAllStore() {
        $host = self::$store_host;
        $param = array(
            'access_token' => self::$access_token,
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
            Log::record('获取店铺错误:' . $host. ';Error:' . $Curl->errorCode . ': ' . $Curl->errorMessage);
            throw new Exception('获取店铺错误:' . $host . ';Error:' . $Curl->errorCode . ': ' . $Curl->errorMessage);
        }

        return $res['store_info'];
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
                $this->_error($source['distributor_order_sn'], "分销商品:{$goods['sku_name']}，SKUID：{$goods['distributor_sku_sn']}没有映射", $member_id, $source_arr[$distributor_store_id]);
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
        /*if(!isset($source['receive_info']['provinceName']) || empty($source['receive_info']['provinceName'])){
            $this->_error($source['distributor_order_sn'], "分销订单 ({$source['distributor_order_sn']}) 的收货地址省份信息获取失败，地区数据", $member_id, $source_arr[$distributor_store_id]);
            return false;
        }
        if(!isset($source['receive_info']['cityName']) || empty($source['receive_info']['cityName'])){
            $this->_error($source['distributor_order_sn'], "分销订单 ({$source['distributor_order_sn']}) 的收货地址城市信息获取失败，地区数据", $member_id, $source_arr[$distributor_store_id]);
            return false;
        }*/
//        if(!isset($source['receive_info']['districtName']) || empty($source['receive_info']['districtName'])){
//            $this->_error($source['distributor_order_sn'], "分销订单 ({$source['distributor_order_sn']}) 的收货地址县/市/区信息获取失败，地区数据", $member_id, $source_arr[$distributor_store_id]);
//            return false;
//        }

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
            $r = $model_member->field('member_id,member_name')->where($condition)->limit(false)->select();
            $res = array_column($r, 'member_id', 'member_name');
            wkcache('source_'. self::$client_id, $res, 30*60);
        }

        $result = array();
        foreach ($res as $k=>$v) {
            $result[self::$store_maps[$k]] = $v;
        }
        return $result;

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

    private function _getOrderType($type){
        switch($type){
            case 0:
                $name="渠道订单";
                break;
            case 1:
                $name="格格家订单,联系电话:4001603602";
                break;
            case 2:
                $name="格格团订单,联系电话:4001603602";
                break;
            case 3:
                $name="格格团全球购订单,联系电话:4001603602";
                break;
            case 4:
                $name="环球捕手订单,联系电话:4007667517";
                break;
            case 5:
                $name="燕网订单";
                break;
            case 6:
                $name="b2b订单";
                break;
            case 7:
                $name="手q";
                break;
            case 8:
                $name="脉宝云店,联系电话:4001116789";
                break;
            default:
                $name="";
        }
        return $name;
    }

    /*保存错误信息到日志table*/
    public function _error($orderno, $errorinfo, $member_id, $source, $log_type = 'order')
    {
        $model = Model("b2c_order_fenxiao_error");
        $where = array(
            'orderno' => $orderno,
            'error' => $errorinfo
        );
        if ($model->where($where)->count() > 0)
            return;

        $data = array(
            'orderno' => $orderno,
            'error' => $errorinfo,
            'order_time' => 0,
            'log_time' => TIMESTAMP,
            'sourceid' => $member_id,
            'source' => $source,
            'log_type' => $log_type
        );

        $model->insert($data);
    }
}