<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/8/7 0007
 * Time: 下午 4:09
 * 果然商城
 */
class grscCron
{
    public static $pdd_header = array("Host:mms.pinduoduo.com");
    public static $pdd_header_nocookie = array("Host:mms.pinduoduo.com","Content-Type: application/json");
    private $client_id='2e69fabb07364ccca583cb8db044246c';
    private $client_secret="ca97e4d04edd61c82dd716955d0481bb4d822aae";
    private $api_url='http://gw-api.pinduoduo.com/api/router';
    private $refresh_url="http://open-api.pinduoduo.com/oauth/token";
	public  $mall_id=782;
	public  $data_type='JSON';
	public  static $onlineDate="2017-08-11 11:00:00";
	public  static $source = "grsc";
    private $access_token='';
    private $refresh_token="";
    private $update_time="";
    public static $cookie_file = "";

    public function __construct($getRel = 1)
    {
        import('Curl');
        self::$cookie_file = BASE_DATA_PATH . "/log/pdd_cookie.txt";
        $config = Model('pddtoken')->where(array('owner_id' => $this->mall_id))->limit(1)->select();
        if (empty($config)) {
            Log::record("数据库中果然商城暂无token相关配置，请手动获取token值");
            exit();
        }
        $this->access_token = $config[0]['access_token'];
        $this->refresh_token = $config[0]['refresh_token'];
        $this->update_time=$config[0]['update_time'];
        $this->refreshToken();
		$model_member = TModel("Member");
		$condition = array("member_name" => self::$source);
		$row = $model_member->where($condition)->find();
		$this->member_id = $row['member_id'];
		$model_member->execute("set wait_timeout=1000");
		//商品映射
		if ($getRel) {
			$this->rel = $this->getGoodsRel();
			$this->oldRel = array();
		}
	}

	//获取商品映射
	private function getGoodsRel()
	{
		$result = TModel("B2cCategory")->where(array('uid' => $this->member_id))->select();
		$rel = $result ? array_column($result, 'pid', 'fxpid') : array();
		return $rel;
	}

	/*全量获取订单*/
	public function _getOrders($page,$status=1){
		$param=array(
				'type'=>'pdd.order.number.list.get',
				'order_status'=>$status,
				'page'=>$page,
				'page_size'=>100
		);
		$res=$this->_sendRequest($param);
		if($res['order_sn_list_get_response']['total_count']>0){
			$res['next'] = $page + 1;
			return $res;
		}
		return array('next' => false, 'count' => 0);
	}

	public function orderlist($params = array())
	{
        //模拟登陆订单补抓，需要传补抓时间，以及istest标记：http://www.hangowa.com/shop/index.php?act=autotask&op=orderlist&source=pindoduo&istest=1&begin="2018-01-01 00:00:00"&end="2018-01-07 23:59:59"
        $service = $params['service'];
        //istest存在时，走模拟登陆获取订单接口
        $istest=isset($_GET['istest'])?1:0;
        $page = 1;
        $total=0;
        $status = isset($_GET['status']) ? $_GET['status'] : '1';
        do {
            if($istest){
                $begin=isset($_GET['begin'])?$_GET['begin']:"2018-01-01 00:00:00";
                $end=isset($_GET['end'])?$_GET['end']:"2018-01-21 23:59:59";
                echo "开始抓取第".$page.'页';
                $where['begin']=$begin;
                $where['end']=$end;
                $status=3;//3已签收,2已发货,1待发货，4全部
                $res=$this->getAll($status,$where,$page,$total);
                $total=$res['total'];
            }else {
                $res = $this->_getOrders($page, $status);
            }
			if (!empty($res['order_sn_list_get_response']['order_sn_list'])) {
				$sns = $service->getSavedidByApiorderno(array_column($res['order_sn_list_get_response']['order_sn_list'],'order_sn'));
				$items = array();
				foreach ($res['order_sn_list_get_response']['order_sn_list']  as $orders =>$order) {
					if (in_array($order['order_sn'], $sns)) {
						continue;
					}
					$order_detail=$this->getOneOrderDetail($order['order_sn']);
					if ($item = $this->_prepareOrder($order_detail)) $items[] = $item;
				}
				if (!empty($items)) $createRes = $service->doCreateOrder($items);
			}
		} while ($page = $res['next']);
	}

	public function getOneOrderDetail($order_sn){
		$param=array(
				'type'=>'pdd.order.information.get',
				'order_sn'=>"{$order_sn}"
		);
		$res=$this->_sendRequest($param);
		return $res['order_info_get_response']['order_info'];
	}

	private function _prepareOrder($source)
	{
		// TODO 检查订单是否存在，若存在直接返回false，否则准备数据，并返回数据
		$goodsList = $source['item_list'];
		$items = array();
		$hasError = false;
		foreach ($goodsList as $goods) {
			if (isset($this->rel[$goods['sku_id']]) && $this->rel[$goods['sku_id']]) {
				$goods_id = $this->rel[$goods['sku_id']];
			} else {
				$this->_error($source['order_sn'], "分销商品 {$goods['goods_name']} ({$goods['sku_id']}) 没有映射");
				$hasError = true;
				continue;
			}
			$items[] = array(
					'goods_id' => $goods_id,
					'name' => $goods['goods_name'],
					'num' => $goods['goods_count'],
					'price' => $goods['goods_price'],
                    'fxpid' => $goods['sku_id'],
                    'oid' => isset($goods['sku_id']) ? $goods['sku_id']:$source['order_sn'],//必须添加否则fenxiao_sub无法查询
			);
		}
		if (empty($items) || $hasError) return false;
		if(!isset($source['province']) || empty($source['province'])){
			$this->_error($source['order_sn'], "分销订单 ({$source['order_sn']}) 的收货地址省份信息获取失败，地区数据：{$source['province']}");
		}else if(!isset($source['city']) || empty($source['city'])){
			$this->_error($source['order_sn'], "分销订单 ({$source['order_sn']}) 的收货地址城市信息获取失败，地区数据：{$source['city']}");
		}else if(!isset($source['town']) || empty($source['town'])){
			$this->_error($source['order_sn'], "分销订单 ({$source['order_sn']}) 的收货地址县/市/区信息获取失败，地区数据：{$source['town']}");
		}
		$detail = array();
		$detail['order_sn'] = $source['order_sn']; //分销系统订单编号
		$detail['buy_id'] = $this->member_id; //分销商用户编号
		$detail['receiver'] = $source['receiver_name'];//收件人
		$detail['provine'] = $source['province'];
		$detail['city'] = $source['city'];
		$detail['area'] = $source['town'];
        $address=str_replace(array($source['province'],$source['city'],$source['town']),'',$source['address']);
        $detail['address'] = $address;
		$detail['mobile'] = $source['receiver_phone']; //手机号码
		$detail['remark'] =!empty($source['remark']) ? $source['remark']:"无";//用户留言
		$detail['payment_code'] = 'fenxiao';
		$detail['order_time'] = strtotime($source['confirm_time']);//下单时间，时间戳
		$detail['item'] = $items;
		$detail['amount'] =$source['pay_amount'];//订单最后价格
		$detail['discount'] = $source['discount_amount'];
		$detail['platform'] = 'new';
		$detail['shipping_fee']=$source['postage'];//运费
		return $detail;
	}

	/**
	 * 获得订单完成状态
	 * @param string $orderSNs 批量请用半角逗号分开
	 */
	function getOrderStatus($orderSNs)
	{
		$orderSNs = explode(',', $orderSNs);
		$res = array(
				'orderStatus' => array(
						'result' => 1,
						'list' => array()
				)
		);
		foreach ($orderSNs as $fx_order_id) {
			//查询接口
			$orderDetail = $this->getOneOrderDetail($fx_order_id);
			//组装所有分销渠道该接口的统一的返回数据格式
			$item = array(
					'orderSn' => $orderDetail['order_sn'],
					'orderStatus' => $orderDetail['order_status'] == '3' ? 3 : 0,
					'refundStatus' => 0,
			);
			$res['orderStatus']['list'][] = $item;

		}
		$res['orderStatus']['result'] = 1;
		return $res;
	}

	//获取商品sku列表
	public function getSkuList($param = array()){
		$page_no = $param['page_no'];
		$page_size = $param['page_size'];
		$params=array(
				'type'=>'pdd.goods.list.get',
				'is_onsale'=>'1',//是否上架
				'page_size'=>"{$page_size}",
				'page'=>"{$page_no}"
		);
		$data=$this->_sendRequest($params);
		$data_out=array();
		foreach($data['goods_list_get_response']['goods_list'] as $key=>$goods){
		        $goods_info=$this->getGoodsInfo($goods['goods_id']);
		        $sku_list=$goods_info["goods_info_get_response"]["goods_info"]["sku_list"];
		        foreach($sku_list as $i){
		            if($i['is_sku_onsale']=='0') continue;//过滤下架产品
                    $item['goods_name'] = $goods['goods_name'] . $i['spec'];
                    $item['sku_id'] = $i['sku_id'];
                    $item['source'] = self::$source;
                    $data_out[] = $item;
                }
		}
		return $data_out;
	}

    /**
     * 获取商品的详细信息
     * @param $goods_id
     * @return mixed
     * @throws Exception
     */
	public function getGoodsInfo($goods_id){
             return $this->_sendRequest(array('type'=>'pdd.goods.information.get','goods_id'=>$goods_id));
    }

	/*private function _getOrders2($create_time=null,$page = 1,$status=null)
	{
		$create_time = explode('|',$create_time);
		$param=array(
				'type'=>'pdd.order.number.list.increment.get',
				'is_lucky_flag'=>'1',
				'order_status'=>"{$status}",
				'refund_status'=>'1',
				'start_updated_at'=>$create_time[0],
				'end_updated_at'=>$create_time[1],
				'page' => "{$page}",
				'page_size'=>"100",
		);
		$res = $this->_sendRequest($param);
		if (is_array($res['order_sn_increment_get_response']['order_sn_list'])) {
			if ($res['order_sn_increment_get_response']['total_count']>$page*100) $res['next'] = $page + 1;
			return $res;
		}
		return array('next' => false, 'count' => 0);
	}*/

	protected function generateSign($params)
	{
        ksort($params);
        $stringToBeSigned =$this->client_secret;
        foreach ($params as $k => $v)
        {
            if("@" != substr($v, 0, 1))
            {
                $stringToBeSigned .= "$k$v";
            }
        }
        unset($k, $v);
        $stringToBeSigned .=$this->client_secret;
        return strtoupper(md5($stringToBeSigned));
	}

	private function _sendRequest($param,$flag=0)
	{
        $param['access_token']=$this->access_token;
        $param['client_id']=$this->client_id;
        $param['data_type']=$this->data_type;
        $param['timestamp']=time();
        $param['version']='V1';
        $param['sign']=$this->generateSign($param);
        $res=array();
        $flag++;
        $curl = new Curl();
        $curl->setOpt(CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        $curl->post($this->api_url, $param);
        $res=$curl->response;
        if ($res->error_response) {
            Log::record('果然商城 HTTP 请求失败! Error:' . $res->error_response->error_code . ': ' . $res->error_response->error_message);
            if($res->error_response->error_code==20000){
                $return=$this->refreshToken(true);//强制刷新token
                if($return){
                    if($flag==2){
                        exit();
                    }
                    $this->_sendRequest($param,$flag);
                }/*else{
                    $messege = new Sms();
                    $log_time = date('Y-m-d H:i:s', time());
                    $messege->send('15527487239', "果然商城token更新失败提醒：", <<<"HTML"
<p>手动更新地址：www.hangowa.com/shop/api/pddtoken/gettoken.php</p>
<p>账号：果然商城  密码：Hangowa321</p>
<p>错误日志：$log_time</p>
HTML
                    );
                }*/
            }
        }
        $res = json_decode(json_encode($curl->response),true);
        return $res;
	}
    /**
     * 刷新token
     * @param $flag 为真时，强制刷新token
     * @return mixed
     */
    protected  function refreshToken($flag=false)
    {
        //更新时间小于一小时，暂时不更新
        if(!$flag&&time()-$this->update_time<3600){
            return true;
        }
        if($flag||in_array(date('H'),array('11','17'))||time()-$this->update_time) {
            //加入获取不成功时重复请求token机制
            for ($i = 0; $i <= 19; $i++) {
                $param['client_id'] = $this->client_id;
                $param['client_secret'] = $this->client_secret;
                $param['grant_type'] = 'refresh_token';
                $param['refresh_token'] = $this->refresh_token;
                $param['state'] = 1000;
                $curl = new Curl();
                $curl->setOpt(CURLOPT_HTTPHEADER, array("Content-Type:application/json"));
                $curl->post($this->refresh_url, json_encode($param));
                $res = $curl->response;
                if ($curl->httpStatusCode == 200) {
                    if ($res->error_response) {
                        Log::record('果然商城 HTTP 请求失败! Error:' . $res->error_response->error_code . ': ' . $res->error_response->error_message . ' 相关参数为:' . json_encode($param) . " 请求执行了" . ($i + 1) . '次');
                        try {
                            throw new Exception('果然商城 HTTP 请求失败! Error:' . $res->error_response->error_code . ': ' . $res->error_response->error_message);
                        } catch (Exception $e) {
                            echo $e->getMessage();
                        }
                        continue;
                    }
                    if (!empty($res->access_token)) {
                        //        $data['id'] = 1;
                        $this->refresh_token=$res->refresh_token;
                        $this->access_token=$res->access_token;
                        $this->update_time=time();
                        $data['access_token'] = $res->access_token;
                        $data['refresh_token'] = $res->refresh_token;
                        $data['expires_in'] = $res->expires_in;
                        $data['refresh_token'] = $res->refresh_token;
                        $data['owner_id'] = intval($res->owner_id);
                        $data['owner_name'] = $res->owner_name;
                        $data['update_time'] = time();
                        $result = Model('pddtoken')->where(array('owner_id' => $this->mall_id))->update($data);
                        return true;
                    } /*else {
                        $messege = new Sms();
                        $log_time = date('Y-m-d H:i:s', time());
                        $messege->send('15527487239', "果然商城token更新失败提醒：", <<<"HTML"
<p>手动更新地址：www.hangowa.com/shop/api/pddtoken/gettoken.php</p>
<p>账号：果然商城  密码：Hangowa321</p>
<p>错误日志：$log_time</p>
HTML
                        );
                    }*/
                } else {
                    Log::record('果然商城 HTTP 中curl请求失败! Error:' . $curl->httpStatusCode);
                }
            }
        }
    }

	public function getShipId($shipname){
		$data=array(
				'安信达'=>'148',
				'包裹平邮'=>'132',
				'德邦物流'=>'131',
				'EMS'=>'118',
				'EMS国际'=>'213',
				'凡客如风达'=>'130',
				'华宇物流'=>'210',
				'龙邦快递'=>'133',
				'联邦快递'=>'135',
				'全一快递'=>'201',
				'全峰快递'=>'116',
				'申通快递'=>'1',
				'顺丰快递'=>'44',
				'速尔快递'=>'155',
				'天天快递'=>'119',
				'天地华宇'=>'210',
				'USPS'=>'186',
				'新邦物流'=>'216',
				'圆通快递'=>'85',
				'韵达快递'=>'121',
				'邮政包裹'=>'132',
				'优速快递'=>'117',
				'中通快递'=>'115',
				'中铁快运'=>'214',
				'宅急送'=>'129',
				'中邮物流'=>'211',
				'国通快递'=>'124',
                '京东快递' => '120',
                '百世汇通' => '3',
                '安能物流' => '208'
		);
		return !empty($data[$shipname]) ? $data[$shipname]:$data["EMS"];
	}

	/*获取物流公司*/
	public function get_company(){
		$param=array(
				'type'=>'pdd.logistics.companies.get',
		);
		$data=$this->_sendRequest($param);
		$data_out='';
		foreach($data['logistics_companies_get_response']['logistics_companies'] as  $item){
			$data_out.=$item['id'].":".$item['logistics_company']."<br/>";
		}
		print_r($data_out);
	}

	public function push_ship($params){
		$param=array(
				'type'=>'pdd.logistics.online.send',
				'order_sn'=>"{$params['orderno']}",
				'logistics_id'=>"{$this->getShipId($params['logi_name'])}",
				'tracking_number'=>"{$params['logi_no']}",
		);
		$res=$this->_sendRequest($param);
		if($res['logistics_online_send_response']['is_success']=='1'){
			$res = json_encode(array(
					'succ' => '1',
					'msg' => '发货成功'
			));
		}else{
			$res = json_encode(array(
					'succ' => '0',
					'msg' => $res['error_response']['error_code'].":".$res['error_response']['error_msg']
			));
		}
		return $res;
	}

	/*检查是否发货*/
	public function checkUnshipOrder()
	{
		$hour = date('G');
		//凌晨检测最近3天，其他时间检测最近3小时
		$updateTime = $hour >= 6 ? TIMESTAMP - 3600 * 3 : TIMESTAMP - 3600 * 24 * 3;
		$comm_where = array();
		$comm_where['shipping_time'] = array('gt', $updateTime);
		$result = Model('order_common')->where($comm_where)->select();
		if (!$result) die('no result');
		$oids = array_column($result, 'order_id');
		$oid_expressid_rels = array_column($result, 'shipping_express_id', 'order_id');
		$where = array();
		$where['order_id'] = array('in', $oids);
		$where['buyer_id'] = $this->member_id;
		$orders = TModel('orders')->where($where)->select();
		if (!$orders) die('no orders');
		foreach ($orders as $order) {
			$fx_order_id = $order['fx_order_id'];
			$goodsWhere = array();
			$goodsWhere['order_id'] = $order['order_id'];
			$order_items = TModel('order_goods')->where($goodsWhere)->select();
			if (!$order_items) continue;
			//汉购网发货,不推送到拼多多
			if($order_items[0]['goods_id']=="102787" || $order_items[0]['goods_id']=="102877"){
				continue;
			}
			$orderDetail = $this->getOneOrderDetail($fx_order_id);
			if ($orderDetail['order_status']!="1") continue;
			$express = rkcache('express', true);
			/** 判断当前商品是否需要重新发货 */
			$express_id = $oid_expressid_rels[$order['order_id']];
			$data = array();
			$data['orderno'] = $fx_order_id;
			$data['logi_no'] = $order['shipping_code'];
			$data['logi_name'] = $express[$express_id]['e_name'];
			if(!empty($data['logi_no'])){
				$this->push_ship($data);
			}
		}
	}

	/*获取退货的订单*/
	public function getRefundOrder($service)
	{
		$page ='1';
		$begin=time()>strtotime(self::$onlineDate) ? time()-1600:strtotime(self::$onlineDate)-1600;
		$end=$begin+1600;
		$createTime = $begin.'|'.$end;
		$this->_service = $service;
		do{
			$res=$this->_getReturnOrder($createTime,$page);
			if (!empty($res['refund_increment_get_response']['refund_list'])) {
				$items = $this->_prepareRefund($this->_filterRefunds($res['refund_increment_get_response']['refund_list']));
				if (!empty($items)) $this->_service->createRefund(array('new' => $items));
			}
		}while($page=$res['next']);
		return true;
	}

	/*获取退货订单*/
	public function _getReturnOrder($modified_time=null,$page=1){
		$create_time=explode('|',$modified_time);
		$param=array(
				'type'=>'pdd.refund.list.increment.get',
				'after_sales_status'=>'1',
				'after_sales_type'=>'1',
				'start_updated_at'=>$create_time[0],
				'end_updated_at'=>$create_time[1],
				'page'=>"{$page}",
				'page_size'=>'100'
		);
		$res=$this->_sendRequest($param);
		if (is_array($res['refund_increment_get_response']['refund_list'])) {
			if (isset($res['refund_increment_get_response']['total_count']) && $res['refund_increment_get_response']['total_count'] > $page*100) $res['next'] = $page + 1;
			return $res;
		}
		return array('next' => false, 'count' => 0);
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
		$fxIds = array_column($items, 'order_sn');
		/** @var orderModel $orderModel */
		$orderModel = Model('order');
		$payOrders = $orderModel->getOrderList(array('fx_order_id' => array('in', $fxIds), 'order_state' => ORDER_STATE_PAY));
		$orders = $orderModel->getOrderList(array('fx_order_id' => array('in', $fxIds)));
		$rel = array_column($payOrders, 'order_amount', 'fx_order_id');
		$orderIdRel = array_column($orders, 'order_id', 'fx_order_id');
		/** 将退款格式转换未二级格式【order=》【goods=》【】】】 */
		foreach ($items as $item) {
			// 处理商品映射关系
			if (!isset($this->rel[$item['sku_id']]) || empty($this->rel[$item['sku_id']])) {
				$this->_error($item['order_sn'], "_filterRefunds,分销商品 ({$item['goods_name']}) 没有配置商品映射，无法生成退款");
				continue;
			}
			$item['goods_id'] =$this->rel[$item['sku_id']];
			$item['order_id'] = $orderIdRel["{$item['order_sn']}"];
			if (!isset($refunds["{$item['order_sn']}"])) $refunds["{$item['order_sn']}"] = array();
			$refunds["{$item['order_sn']}"][$item['goods_id']] = $item;
		}
		if (!empty($payOrders)) {
			foreach ($rel as $fxOrderId => $order_amount) {
				echo "过滤{$fxOrderId}\n";
				$refund_total = array_sum(array_column($refunds[$fxOrderId], 'refund_amount'));
				echo "退款金额{$refund_total}\n";
				echo "订单金额{$order_amount}\n";
				if (ncPriceFormat($refund_total) != ncPriceFormat($order_amount)) {
					unset($refunds[$fxOrderId]);
					$this->_error($fxOrderId, "未发货分销订单不是全额退款，无法生成退款");
				} else { /*全额退款商品有多个时，只提交一次退款*/
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
	 * @return bool|array
	 */
	private function _prepareRefund($items)
	{
		//过滤掉非全额退款订单，不做处理
		if (empty($items)) return array();
		$order_sns = array_keys($items);
		$new_fsmodel = TModel("B2cOrderFenxiaoSub");
		$condition['orderno'] = array('in', $order_sns);
		$re = $new_fsmodel->where($condition)->select();
		$result = $re ? $re : array();
		$newRefund = array();
		$returnModel = TModel('RefundReturn');
		foreach ($result as $suborder) {
			$orderno = $suborder['orderno'];
			$goods_id = $suborder['product_id'];
			//匹配未付款子订单
			$ordersn = $this->_service->_getFxorderSn($orderno, $goods_id);
			if (!$ordersn) continue;
			//检查子订单是否已申请退款或取消订单
			$filter = array();
			$filter['order_sn'] = $ordersn;
			$filter['goods_id'] = array('in', array(0, $goods_id));
			if ($returnModel->where($filter)->count() > 0) {
				continue;
			}
			$data = array();
			$data['reason_id'] = 100; //退款退货理由 整型
			$data['refund_type'] = 3;
			$data['return_type'] = 3;
			if($items[$orderno][$goods_id]['after_sales_type']=="2"){ //为1代表仅退款，2为退后退款
				$data['return_type']=1;
				$data['refund_type']= 1;
			}
			$data['seller_state'] = 1; //卖家处理状态:1为待审核,2为同意,3为不同意,默认为1
			$data['refund_amount'] = $items[$orderno][$goods_id]['refund_amount'];//退款金额
			$data['goods_num'] =$items[$orderno][$goods_id]['goods_number'];//商品数量
			$data['buyer_message'] = $items[$orderno][$goods_id]['after_sale_reason']=="" ? "无":$items[$orderno][$goods_id]['after_sale_reason'];  //申请原因
			$data['ordersn'] = $ordersn;  //汉购网订单编号
			$data['goods_id'] = $suborder['product_id']; //商品编号
			$data['create_time'] = strtotime($items[$orderno][$goods_id]['created_time']);  //售后订单产生时间
			$newRefund[] = $data;
		}
		return $newRefund;
	}

	/**
	 * 跟踪退款单状态
	 * afterSalesStatus 1.买家申请退款，待商家处理;4.商家同意退款，退款中；9.商家驳回退款，待买家处理;
	 * 12.买家逾期未处理，退款失败;3.平台处理中;4.平台同意退款，退款中;7.平台拒绝退款，退款关闭;5.退款成功;
	 * 6.用户撤销了退款申请
	 * @param $service FenxiaoService
	 * @return boolean
	 */
	public function traceRefund($service)
	{
		$p ='1';
		$begin=time()>strtotime(self::$onlineDate) ? time()-1600:strtotime(self::$onlineDate)-1600;
		$end=$begin+1600;
		$createTime = $begin.'|'.$end;
		$this->_service = $service;
		do {
			$res = $this->_getReturnOrder($createTime,$p);
			if (!empty($res['refund_increment_get_response']['refund_list'])) {
				$items = $this->_filterTraceRefunds($res['refund_increment_get_response']['refund_list']);
				if (!empty($items)) $this->_updateRefund($items);
			}
		} while ($p = $res['next']);
		return true;
	}

	/**
	 * 过滤退款跟踪数据
	 * @param $items
	 * @return array
	 */
	private function _filterTraceRefunds($items)
	{
		$refunds = array();
		/** 若订单未发货，但是部分退款，剔除 */
		$fxIds = array_column($items, 'order_sn');
		/** @var orderModel $orderModel */
		$orderModel = Model('order');
		$payOrders = $orderModel->getOrderList(array('fx_order_id' => array('in', $fxIds),
				'order_state' => ORDER_STATE_PAY));
		$orders = $orderModel->getOrderList(array('fx_order_id' => array('in', $fxIds)));
		$rel = array_column($payOrders, 'order_amount', 'fx_order_id');
		$orderIdRel = array_column($orders, 'order_id', 'fx_order_id');
		/** 将退款格式转换未二级格式【order=》【goods=》【】】】 */
		foreach ($items as $item) {
			// 处理商品映射关系
			if (!isset($this->rel[$item['sku_id']]) || empty($this->rel[$item['sku_id']])) {
				$this->_error($item['order_sn'], "分销商品 ({$item['goods_name']}) 没有配置商品映射，无法生成退款");
				continue;
			}
			if ($item['after_sales_status'] == '2') continue;
			$item['goods_id'] =$this->rel[$item['sku_id']];
			$item['order_id'] = $orderIdRel["{$item['order_sn']}"];
			if (!isset($refunds["{$item['order_sn']}"])) $refunds["{$item['order_sn']}"] = array();
			$refunds["{$item['order_sn']}"][$item['goods_id']] = $item;
		}
		return $refunds;
	}

	private function _updateRefund($items)
	{
		//查找未完结的卷皮退款订单
		$refundModel = TModel("RefundReturn");
		$refundService = Service("Refund");
		$model = Model();
		//根据退款状态做相应处理，处理取消退款以及退款完成的订单，其他状态保持不变不做处理
		foreach ($items as $orderId => $refunds) {
			foreach ($refunds as $item) {
				$refund = $refundModel->where(array('order_id' => $item['order_id'], 'goods_id' => $item['goods_id']))->find();
				$params = array(
						'refund_id' => $refund['refund_id'],
						'op_id' => $this->member_id,
						'op_name' => self::$source
				);
				$msg = "";
				//退款完成
				if ($item['after_sales_status'] == '10') {
					v($params, 0);
					$method = 'confirm_refund';
				}
				try {
					$model->beginTransaction();
					if(!isset($method) || $method==""){
						continue;
					}
					$res = $refundService->$method($params, $msg);
					if (!$res) {
						try {
                            throw new Exception($msg);
                        }catch(Exception $e){
						    echo $e->getMessage();
                        }
					}
					$model->commit();
				} catch (Exception $e) {
					$model->rollback();
					$msg = $e->getMessage();
				}
				v($msg, 0);
			}
		}
	}

	/*保存错误信息到日志table*/
	public function _error($orderno, $errorinfo, $log_type = 'order')
	{
		$model = Model("b2c_order_fenxiao_error");
		$where = array(
				'orderno' => $orderno,
				'error' => $errorinfo
		);
		if ($model->where($where)->count() > 0) return;
		$data = array(
				'orderno' => $orderno,
				'error' => $errorinfo,
				'order_time' => 0,
				'log_time' => time(),
				'sourceid' => $this->member_id,
				'source' => self::$source,
				'log_type' => $log_type
		);
		$model->insert($data);
	}
    //验证登陆状态，如未登录，则重新登录，如在登陆过程中要输入验证码，则打码输入后在提交验证码页面再次登陆
    public static function has_logged() {
        $res = self::list_orders(1,1,array());
        //cookie记录的登陆状态，已经失效，重新登录
        if (is_array($res) && isset($res['error_code']) ) {
            $login_res = self::login_pdd();

            if (isset($login_res['error_code'])) {
                log::selflog("模拟登录失败", 'pinduoduo') ;
                return false;
            }

        }

        return true;
    }

    //拼多多后台登陆，记录cookie信息
    public static function login_pdd($captcha='') {
        $login_url = 'http://mms.pinduoduo.com/auth';

        $post_fields['username'] = '果然商城';
        $post_fields['password'] = 'Hangowa321';

        $s = self::curlPost($login_url, $post_fields);
        return json_decode($s, true);
    }

    /**
     * 根据查询条件获取 订单列表
     */
    public static function list_orders($pageNumber=1, $status = 1, $where, $pageSize = 30) {

        $filter = array(
            'confirmStartTime' => strtotime($where['begin']),
            'isLucky' => -1,
            'confirmEndTime' => strtotime($where['end']),
            'orderType'=>$status,
            'afterSaleType'=>'1',
            'pageNumber'=> $pageNumber,
            'pageSize' => $pageSize,
            'remarkStatus'=>-1
        );
        if( $status == 4 ) unset($filter['orderType']) ;

        //$url = "http://mms.yangkeduo.com/malls/8/orders3?" . http_build_query($filter);
        //$url = "http://mms.yangkeduo.com/malls/8/orders3?confirmStartTime=1473585740&confirmEndTime=1476177740&isLucky=0&orderType=1&afterSaleType=1&pageNumber=1&pageSize=30";
        //$s = self::curlGet($url);
        $url = "http://mms.pinduoduo.com/mars/shop/orderList";
        $s = self::curlPostNoCookie($url , json_encode($filter));
        $res = json_decode($s, true);
        if( !$res['result'] ) {
            self::login_pdd() ;
        }

        return $res ;
    }

    /**
     * 获取历史订单
     * @params $begin,$end 日期2016-05-05
     */
    function getAll($status, $where,$page=1,$getTotal=0)
    {
        if( self::has_logged() ) {
            $flag = 1 ;
            $pageNumber = $page ;
            $pageSize = 50;
            $res=array();
            $response = self::list_orders($pageNumber, $status, $where, $pageSize); //1为未发货 。 2为已发货。3为已签收

            $total = $response['result']['totalItemNum'];
            $savedOrderIds = $order_nos = array();
            foreach ($response['result']['pageItems'] as $order) {
                $res['order_sn_list_get_response']['order_sn_list'][]['order_sn'] = $order['order_sn'];
            }
            if ($getTotal <= $total) {
                $getTotal += 50;
                $res['total']=$getTotal;
                $res['next'] =++$page;
                return $res;
            }
            return array('next' => false, 'count' => 0,'total'=>0);
        }
    }
//    function composeOrder($order){
//        $detail=$this->get_refund_good_num($order['order_sn']);
//        $item = $detail['result'];
//        $new_order = array("order_sn" => $item['order_sn'],
//            "confirm_time" => $item['confirm_time'],
//            "receiver_name" => $item['receive_name'],
//            "created_time" => $item['order_time'],
//            "country" => '中国',
//            "province" => $item['province_name'],
//            "city" => $item['city_name'],
//            "town" => $item['district_name'],
//            "address" => $item['shipping_address'],
//            "receiver_phone" => $item['buyer_mobile'],
//            "pay_amount" => $item['order_amount'],
//            "goods_amount" => $item['goods_amount'],
//            "discount_amount" => $item['discount_amount'],
//            "postage" => $item['shipping_amount'],
//            "id_card_num" => "",
//            "id_card_name" => "",
//            "logistics_id" => 0,
//            "tracking_number" => $item['tracking_number'],
//            "shipping_time" => $item['shipping_time'],
//            "order_status" => $item['order_status'],
//            "is_lucky_flag" => $item['lucky_status'],
//            "refund_status" => $item['after_sales_status'],
//            "item_list" =>
//                array(
//                    0 =>
//                        array(
//                            "goods_id" => $item['goods_id'],
//                            "goods_sku"=>$item['goods_id'],
//                            "goods_name" => $item['goods_name'],
//                            "goods_price" => $item['goods_price'],
//                            "goods_spec" => "",
//                            "goods_count" => $item['goods_number'],
//                            "goods_img" => $item['thumb_url'],
//                        )
//                ),
//        );
//        return $new_order;
//
//    }

    function get_refund_good_num($order_sn){
        if( !self::has_logged() ) return array();
        $data = array(
            'orderSn'=>$order_sn
        );
        $url = "http://mms.pinduoduo.com/mars/shop/orderDetail";
        $s = self::curlPostNoCookie($url , json_encode($data));
        return json_decode($s, true);
    }
    //模拟登录获取拼多多后台退款列表接口数据，只获取上线后同步到汉购网的订单退款单
    function list_refund_order($onlineDate=null)
    {
        //return $this -> demoRefundData();
        $onlineDate = time() - 10*24*3600;
        if( !self::has_logged() ) return array();

        $hasnext = true ;
        $page = 1 ;
        $size = 100 ;
        $result = array() ;
        while ( $hasnext )
        {
            $filter = array(
                //'beginTime' => date('Y-m-d H:i:s', time()-3*3600 ),
                'beginTime' => $onlineDate,
                'endTime' => time(),
                'mallId' => '8',
                'offset' => ($page-1)*$size,
                'pageSize' => $size,
                //'statusList[]' => '1'
            );

            $url = "http://mms.pinduoduo.com/mercury/afterSales/list";
            $res = self::curlPostNoCookie($url , json_encode($filter));
            $s = json_decode($res, true);
            if( $s['success'] )
            {
                $total = $s['result']['total'] ;
                foreach( $s['result']['afterSalesList'] as $refund ){
                    //过滤上线日期以前成团的订单
                    if( $refund['createdTime'] < $onlineDate )
                        continue ;

                    $result[] = $refund ;
                }
                if( $filter['offset']+$filter['size'] > $total ) {
                    $hasnext = false ;
                }
                $page ++ ;
            } else {
                $hasnext = false ;
            }
        }
        return $result ;
    }

    function demoRefundData()
    {
        $data[] = array(
            'id'	=> '1008145',
            'orderSn' => '160801-05982016917',
            'refundAmount' => "2190",
            'goodsId' => '3274410',
            'goodsName' => '商品1'
        ) ;
        $data[] = array(
            'id'	=> '1008145',
            'orderSn' => '160802-39126136224',
            'refundAmount' => "12750",
            'goodsId' => '13829',
            'goodsName' => '商品2'
        ) ;
        $data[] = array(
            'id'	=> '1008145',
            'orderSn' => '160802-36448653800',
            'refundAmount' => "2390",
            'goodsId' => '327441',
            'goodsName' => '商品3'
        ) ;
        return $data ;
    }

    /**
     * @param $id 退款sn号
     * @return 退款明细
     */
    public function get_refund_detail( $id )
    {
        $filter = array(
            'id' => $id
        );

        $url = "http://mms.yangkeduo.com/malls/8/after/sales?" . http_build_query($filter);
        $res = self::curlGet($url);
        $s = json_decode($res, true);

        return $s ;
    }

    /**
     * 获取退款SN号码
     */
    public function get_refund_sn( $orderno )
    {
        $url = "http://mms.yangkeduo.com/refund/" . $orderno;
        $res = self::curlGet($url);
        $s = json_decode($res, true);

        return $s[0]['refund_sn'] ? $s[0]['refund_sn'] : 0 ;
    }

    /**
     * 根据拼多多订单号获取退款详情
     */
    public function get_refunddetail_byono( $refund_id )
    {
        if( !self::has_logged() || !$refund_id ) return array();

        $detail = $this -> get_refund_detail( $refund_id ) ;
        return $detail ;
    }

    /**
     * 获取登录验证码数据，用于image/png类型的header后输出
     */
    public static function get_captcha_data() {
        $captcha_url = 'http://mch.wxrrd.com/captcha';
        $httpheader = array("Host: mch.wxrrd.com");
        $s = self::curlGet($captcha_url, $httpheader);
        return $s;
    }

    public static function curlGet($url, $timeout=6) {
        $ch = curl_init();
        // 设置选项，包括URL
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//禁止直接显示获取的内容 重要
        curl_setopt($ch, CURLOPT_HEADER, 0);
        // curl_setopt($ch, CURLOPT_REFERER, 'http://www.ickd.cn/auto.html');
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:47.0) Gecko/20100101 Firefox/47.0');

        $header = self::$pdd_header;
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        //curl_setopt($ch, CURLOPT_COOKIE, "laravel_session=eyJpdiI6IkJnOWdqOXVHaGNJbzlDMUs5Z2tXU0E9PSIsInZhbHVlIjoidGMzWUt2UGlxOUJ5NmdqTzBwS2xCenZPNWs2S0hyb2hIb3JEZVFGampqZDgwcndtaEFvVnQ1WmV6Q212WHh5aytuaFlHOUhRRndjcDVrSlJWb2FRV0E9PSIsIm1hYyI6ImUwNTJjNGYwYzJkMmUwZTNmMDJlOWExMDhhNDM0NDNkNDA1ZGYyMWI3ZjU1Nzg1Mjg3ZjI0MmJkNWIyOTYyMDIifQ%3D%3D");

        $cookie_file = self::$cookie_file;
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);

        // 执行并获取HTML文档内容
        $output = curl_exec($ch);
        // 释放curl句柄
        curl_close($ch);
        return $output;
    }

    public static function curlPost_detail($url, $data, $timeout=6) {
        $ch = curl_init();
        // 设置选项，包括URL
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.118 Safari/537.36');

        $header = self::$pdd_header;
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

        $cookie_file = self::$cookie_file;
        curl_setopt($ch, CURLOPT_COOKIEFILE , $cookie_file);

        // 执行并获取HTML文档内容
        $output = curl_exec($ch);
        // 释放curl句柄
        curl_close($ch);
        return $output;
    }

    public static function curlPost($url, $data, $timeout=6) {
        $ch = curl_init();
        // 设置选项，包括URL
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.118 Safari/537.36');

        $header = self::$pdd_header;
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

        $cookie_file = self::$cookie_file;
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);

        // 执行并获取HTML文档内容
        $output = curl_exec($ch);
        // 释放curl句柄
        curl_close($ch);
        return $output;
    }

    public static function curlPostNoCookie($url, $data, $timeout=6) {
        $ch = curl_init();
        // 设置选项，包括URL
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.118 Safari/537.36');

        $header = self::$pdd_header_nocookie;
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

        $cookie_file = self::$cookie_file;
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);

        // 执行并获取HTML文档内容
        $output = curl_exec($ch);
        // 释放curl句柄
        curl_close($ch);
        return $output;
    }

    public function list_chargeback($where = array())
    {
        $hasnext = true ;
        $page = 1 ;
        $size = 100 ;
        $result = array() ;
        $getTotal = 0;

        while ( $hasnext ) {
            $filter = array(
                "orderSn" => "",
                "startTime" => $where['begin'],
                "endTime" => $where['end'],
                "pageNumber" => $page,
                "pageSize" => $size,
            ) ;

            $url = "http://mms.yangkeduo.com/malls/8/unshipChargebacks?" . http_build_query($filter);
            $res = self::curlGet($url);
            $s = json_decode($res, true);

            if( $s['totalCount'] > 0 )
            {
                $total = $s['totalCount'] ;
                foreach ($s['records'] as $cb) {
                    $data=array();
                    $data['orderno'] = $cb['orderSn'];
                    $data['cost_price'] = $cb['chargebackAmount'];
                    $data['reason'] = $cb['chargebackReason'];
                    $result[] = $data ;
                    $getTotal+=1;
                }

                if( $getTotal >= $total ) {
                    $hasnext = false;
                } else {
                    $page += 1 ;
                }

            } else {
                $hasnext = false ;
            }
        }

        return $result;
    }

}