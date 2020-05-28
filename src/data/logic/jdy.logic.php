<?php
/**
 * 购买行为
 *
 */

class jdyLogic {
    /** @var jdyLogic  */
    private static $_instance = null;
    /** @var Curl */
    private $_curl;
    private $_config;
    function __construct() {
        $this->_init();
    }

    private $sys_start_date = "2019-7-1";

    private function _init(){
        import('Curl');
        import('ArrayHelper');
        // 初始化
        $this->_curl = new Curl();
        $this->_curl->setJsonDecoder(function($response) {
            $json_obj = json_decode($response, true);
            if (!($json_obj === null)) {
                $response = $json_obj;
            }
            return $response;
        });
        $this->_config = C('jdy');
    }

    private function _getAccessToken(){
        $keyToken = md5('jdy.accessToken');
        $accessToken = rkcache($keyToken);
        if (isset($accessToken) && !empty($accessToken)&&isset($accessToken['access_token'],$accessToken['expires'])&&$accessToken['expires']>time()+600) {
            return $accessToken['access_token'];
        }

        $curl = $this->_curl;
        $curl->setOpt(CURLOPT_SSL_VERIFYPEER,false);
        $curl->setOpt(CURLOPT_SSL_VERIFYHOST,0);

        $param = array(
            'username'=>ArrayHelper::getValue($this->_config,'username'),
            'password'=>ArrayHelper::getValue($this->_config,'password'),
            'client_id'=>ArrayHelper::getValue($this->_config,'client_id'),
            'client_secret'=>ArrayHelper::getValue($this->_config,'client_secret'),
        );
        $response = $curl->get('https://api.kingdee.com/auth/user/access_token',$param);

        // 缓存令牌
        if (isset($response['errcode']) && $response['errcode']===0) {
            $accessToken = $response['data'];
            wkcache($keyToken, $accessToken);
        }
        return $accessToken['access_token'];
    }

    /**
     * @return jdyLogic
     */
    static function getInstance() {
        if (self::$_instance === null){
            self::$_instance = Logic('jdy');
        }
        return self::$_instance;
    }

    public function getSuppliers($page=1){
        $url = 'http://api.kingdee.com/jdyapi/scm/supply/list';
        return $this->httpGet($url,array('page'=>$page,'pageSize'=>500));
    }

    public function getGoods($page=1, $pageSize=200) {
        $url = 'http://api.kingdee.com/jdyapi/product/item/list';
        $postData = array(
            'order' => 'MODIFYTIME_DESC',
            'pageInfo' => json_encode(array(
                "pageIndex" => $page,
                "pageSize"  => $pageSize
            ))
        );
        return $this->httpPost($url, $postData);
    }

    /**
     * 获取全部供应商信息
     * @param bool $force
     * @return array|mixed
     * @throws Exception
     */
    public function getAllSuppliers($force=false){
        $cacheKey = 'jdy.suppliers.all';
        $suppliers = rkcache($cacheKey);
        if ($suppliers&&!$force){
            return $suppliers;
        }
        $suppliers = array();
        $page = 0;
        do{
            $page++;
            $data = $this->getSuppliers($page);
            $suppliers = array_merge($suppliers,$data['rows']);
            $hasNext = $data['totalPage']>$data['page'];
        }while($hasNext);
        wkcache($cacheKey, $suppliers);
        return $suppliers;
    }

    /**
     * 同步商品信息
     * @return bool
     */
    public function getGoodsList(){
        $page = 0;
        $pageSize = 200;
        /** @var jdy_goods_stockModel $goodsStockModel */
        $goodsStockModel = Model('jdy_goods_stock');
        do{
            $page++;
            $data = $this->getGoods($page, $pageSize);
            $hasNext = $data && ($pageSize == count($data));
            foreach ($data as $goods) {
                $goods_stock_params = array(
                    'item_code' => $goods['itemCode'],
                    'item_name' => $goods['itemName'],
                    'sku_id' => isset($goods['skuid']) ? $goods['skuid'] : 0,
                    'sku_name' => isset($goods['skuName']) ? $goods['skuName'] : '',
                    'sku_code' => isset($goods['skuCode']) ? $goods['skuCode'] : '',
                    'retail_price' => isset($goods['retailPrice']) ? $goods['retailPrice'] : 0,
                    'sale_price' => isset($goods['salePrice']) ? $goods['salePrice'] : 0,
                    'unit_number' => $goods['unitNo'],
                    'unit_name' => $goods['unitName'],
                    'unit_group_number' => isset($goods['unitGroupNo']) ? $goods['unitGroupNo'] : '',
                    'unit_group_name' => isset($goods['unitGroupName']) ? $goods['unitGroupName'] : '',
                );
                $goods_stock_data = $goodsStockModel->getItemInfo(array('item_id' => $goods['itemid']));
                if ($goods_stock_data) {
                    $goodsStockModel->editItem($goods_stock_params, array('inventory_id' => $goods_stock_data['inventory_id']));
                } else {
                    $goods_stock_params['item_id'] = $goods['itemid'];
                    $goods_stock_params['instant_num'] = 0;
                    $goods_stock_params['inv_lock_num'] = 0;
                    $goods_stock_params['real_qty'] = 0;
                    $goods_stock_params['warehouse_code'] = '';
                    $goods_stock_params['warehouse_name'] = '';
                    $goodsStockModel->addItem($goods_stock_params);
                }
            }
        }while($hasNext);
        return true;
    }

    /**
     * 同步供应商信息
     * @return bool
     */
    public function getSupplierList() {
        $supplierList = $this->getAllSuppliers(true);
        /** @var jdy_supplierModel $jdySupplierModel */
        $jdySupplierModel = Model('jdy_supplier');
        foreach ($supplierList as $key => $value) {
            if (empty($value['number']) || empty($value['name']) || empty($value['id'])) {
                continue;
            }
            $supplier_params = array(
                'supplier_number' => $value['number'],
                'supplier_name' => $value['name'],
                'supplier_catetory_id' => $value['catetoryId'],
                'supplier_catetory_name' => $value['catetoryName'],
                'supplier_link_man' => $value['linkman'],
                'supplier_link_mobile' => $value['mobile'],
            );
            $supplier_data = $jdySupplierModel->getItemInfo(array('supplier_unique_id' => $value['id']));
            if ($supplier_data) {
                $jdySupplierModel->editItem($supplier_params, array('supplier_id' => $supplier_data['supplier_id']));
            } else {
                $supplier_params['supplier_unique_id'] = $value['id'];
                $jdySupplierModel->addItem($supplier_params);
            }
        }
        return true;
    }

    /**
     * 获取接口数据通用方法
     * @param $url string
     * @param $data array
     * @return bool
     */
    private function httpGet($url, $data = [])
    {
        $token = $this->_getAccessToken();
        // 读取采购单
        $curl = $this->_curl;
        $param = array(
            'access_token' => $token,
            'dbid' => ArrayHelper::getValue($this->_config, 'dbid'),
        );
        if ($data) {
            $param = array_merge($param, $data);
        }
        $response = $curl->get($url, $param);

        if (isset($response['errcode']) && $response['errcode'] === 200) {
            $data = $response['data'];
            return $data;
        }
        return false;
    }

    /**
     * @param $url
     * @param array $data
     * @return bool
     */
    private function httpPost($url, $data = [])
    {
        $token = $this->_getAccessToken();
        // 读取采购单
        $curl = $this->_curl;
        $url .= '?access_token=' . $token . '&dbid=' . ArrayHelper::getValue($this->_config, 'dbid');
        $curl->setHeader('Content-Type', 'application/json');
        $response = $curl->post($url, $data);
        if (isset($response['errcode']) && $response['errcode'] === 200) {
            return $response['data'];
        }
        return false;
    }

    /**
     * @deprecated
     * 获取接口数据通用方法
     * @param $url string
     * @param int $post 请求方式，暂只支持get和post，默认get
     * @param array $params 请求参数
     * @return bool
     */
    private function getData($url,$post=0,$params=[]){
        $token = $this->_getAccessToken();
        // 读取采购单
        $curl = $this->_curl;
        $param = array(
            'access_token'=>$token,
            'dbid'=>ArrayHelper::getValue($this->_config,'dbid'),
        );
        if($post){
            $url .='?access_token='.$token.'&dbid='.ArrayHelper::getValue($this->_config,'dbid');
            $response = $curl->post($url, $params);
        }else {
            if($params){
                $param = array_merge($param,$params);
            }
            $response = $curl->get($url, $param);
        }

        if (isset($response['errcode']) && $response['errcode']===200) {
            $data = $response['data'];
            return $data;
        }
        return false;
    }




    /**
     * 处理账单数据，转移到entry表
     */
    public function paresBill(){
        ini_set('memory_limit','3G');
        // 查询未推送的账单，循环推送
        /** @var billModel $billModel */
        $billModel = model("bill");

        $where = [
            'ob_state' => array('in', array(BILL_STATE_CEO)),//12
            'jdy_state' => billModel::JDY_STATE_NEW,//0
            'jdy_parse_time' => array('lt', time() - 3600),//1小时以前
            'ob_create_date' => array('gt', strtotime($this->sys_start_date))//大于系统订单起始时间
        ];
        $bills = $billModel->getOrderBillList($where, '*', 100);
        if (empty($bills)) return true;

        //锁定订单时间
        $obIds = array_column($bills, 'ob_id');
        $billModel->editOrderBill(
            array('jdy_parse_time' => time()),
            array('ob_id' => array('in', $obIds))
        );

        foreach ($bills as $bill) {
            if ($bill['ob_order_totals'] <= 0) continue;//过滤订单金额
            try {
                $this->_parseEntry($bill);
            } catch (Exception $exception) {
                $error_data = [
                    'jdy_state' => billModel::JDY_STATE_PARSE_ERROR,//订单解析错误
                    'jdy_refund_state'=>billModel::JDY_REFUND_STATE_PARSE_ERROR,//退单解析错误
                    'jdy_msg' => $exception->getMessage()
                ];
                $billModel->editOrderBill($error_data,['ob_id' => $bill['ob_id']]);
                continue;
            }
            $success_data = [
                'jdy_state' => billModel::JDY_STATE_PARSED,//订单解析成功
                'jdy_refund_state'=>billModel::JDY_REFUND_STATE_PARSED//退单解析成功
            ];
            $billModel->editOrderBill($success_data,['ob_id' => $bill['ob_id']]);
        }
        return false;
    }

    /**
     * 将账单转移到entry表
     * @param $bill
     * @throws Exception
     * @return mixed
     */
    private function _parseEntry($bill)
    {
        // 根据BILL信息创建采购订单参数
        // 查询订单商品信息，循环处理供应商
        $orderModel = model("order");
        $refund_returnModel = model("refund_return");
        $jdyEntryModel = model("jdy_entry");

        //从orders查询
        $condition = array();
        $condition['order_state'] = ORDER_STATE_SUCCESS;//交易成功
        $condition['store_id'] = $bill['ob_store_id'];//bill对应的店铺
        $condition['finnshed_time'] = array('between', "{$bill['ob_start_date']},{$bill['ob_end_date']}");//订单完成时间在bill订单开始、结束时间之间的订单

        $entries = array();
        $i=0;
        do{
            $i++;
            $orders = $orderModel->getOrderList($condition,'500','*','order_id asc');
            if (empty($orders)) break;
            //循环查询、判断条件
            $hasNext = count($orders)>=500;
            $orderIds = array_column($orders, 'order_id');
            $condition['order_id'] = array('gt',max($orderIds));

            //根据order_id查order_goods列表
            $orderGoodsList = $orderModel->getOrderGoodsList(['order_id' =>['in', $orderIds]],'*',999999);
            $goodsIds = array_column($orderGoodsList, 'goods_id');
            if (empty($goodsIds)) return null;

            // 循环合并计算采购商品
            foreach ($orderGoodsList as $orderGoods) {
                $goodsId = $orderGoods['goods_id'];
                //构建entry数组，并将goods信息加入数组
                if (!isset($entries[$goodsId])) {
                    $entries[$goodsId] = $this->_newEntry($bill['ob_id'], $orderGoods['goods_id'], $orderGoods['goods_name'], $bill['ob_store_manage_type']);
                }
                $entries[$goodsId]['goods_num'] += $orderGoods['goods_num'];//商品数量
                $entries[$goodsId]['amount'] += $orderGoods['goods_pay_price'];//商品实际成交价
                $entries[$goodsId]['cost'] += $orderGoods['goods_cost'];//商品成本价
                $entries[$goodsId]['rpt_bill'] += $orderGoods['manage_type'] == 'co_construct'?0:$orderGoods['rpt_bill'];//商家结算红包金额
                $entries[$goodsId]['mj_bill'] += $orderGoods['mj_bill'];
                $entries[$goodsId]['xs_bill'] += $orderGoods['xs_bill'];

                // 此处根据情况调整商品金额
                if ($orderGoods['manage_type'] == 'co_construct') {//共建商家
                    $billAmount = $entries[$goodsId]['cost'];//商品成本价
                    $commis_bill = 0;//销售佣金
                } else {//平台商家
                    $billAmount = $entries[$goodsId]['amount']*(100-$orderGoods['commis_rate'])/100;//扣除佣金后的金额，1-佣金比例
                    $billAmount = $billAmount + $entries[$goodsId]['rpt_bill'];//加红包金额
                    //实际成交价+佣金的结果，再计算佣金
                    $commis_bill = ncPriceFormat(($orderGoods['goods_pay_price']+$orderGoods['rpt_bill'])*$orderGoods['commis_rate']/100, 2);//销售佣金
                }
                $entries[$goodsId]['commis_bill'] += $commis_bill;//销售佣金

                $num = $entries[$goodsId]['goods_num'] > 0 ? $entries[$goodsId]['goods_num'] : 1;//商品数量
                $entries[$goodsId]['price'] = $billAmount / $num;//商品单价
            }
        }while($hasNext);

        //获取退单信息
        $conditionRefund = array();
        $conditionRefund['seller_state'] = 2;//卖家同意退货
        $conditionRefund['store_id'] = $bill['ob_store_id'];//bill信息对应店铺
        $conditionRefund['goods_id'] = array('gt', 0);//部分退款[goods_id=0全部退款]
        $conditionRefund['admin_time'] = array('between', "{$bill['ob_start_date']},{$bill['ob_end_date']}");//管理员处理时间在bill开始、结束时间之间的退单
        $refund_list = $refund_returnModel->getRefundReturnList($conditionRefund, '', '*', 999999);

        $oids = array_column($refund_list, 'order_id') ;
        $order_goods = $this -> getOrderGoods($oids) ;
        if (!empty($refund_list)) {
            //合并订单对应的goods并去重
            $goodsIds = array_unique(array_merge($goodsIds, array_column($refund_list, 'goods_id')));
        }
        if (empty($goodsIds)) return null;

        foreach ($refund_list as $refund) {
            $goodsId = $refund['goods_id'];
            // 计算供应商应该退款金额
            if (!isset($entries[$goodsId])) {
                $entries[$goodsId] = $this->_newEntry($bill['ob_id'], $refund['goods_id'], $refund['goods_name'], $bill['manage_type']);
            }

//            $entries[$goodsId]['rf_mj_bill'] += $refund['mj_bill'];//退款红包返还，数据表没有该字段
//            $entries[$goodsId]['rf_xs_bill'] += $refund['xs_bill'];//数据表没有该字段
            $goods_num = ($refund['goods_num']==0?1:$refund['goods_num']);//退单商品数量
            $entries[$goodsId]['refund_qty'] += $goods_num;//退单商品数量

            $ogInfo 		= 	$order_goods[ $refund['order_id'] ][ $refund['goods_id'] ] ;
            $cost_rate		=	$ogInfo['goods_cost'] / $ogInfo['goods_pay_price'] ;//成本率=商品成本价/商品实际成交价
            $refund_amount = $refund['refund_amount_bill'] == -1?$refund['refund_amount']:$refund['refund_amount_bill'];//退单金额
            $refund_amount = $bill['ob_store_manage_type'] == 'platform' ?ncPriceFormat($refund_amount):ncPriceFormat($refund_amount * $cost_rate) ;//共建计算成本价
            //计算退单商品单价
            $refund_amount = $refund_amount/$goods_num;
            $entries[$goodsId]['refund'] += $refund_amount;

            //平台商家退款金额=实际成交价，才有红包
            $rpt_bill_totals = ($bill['ob_store_manage_type'] == 'platform' && $refund_amount == $ogInfo['goods_pay_price']) ? floatval($ogInfo['rpt_bill']) : 0;//退款红包
            $entries[$goodsId]['rf_rpt_bill'] += $rpt_bill_totals;

            //计算佣金，退单金额*佣金比例
            $commis_return_totals = $bill['ob_store_manage_type'] == 'platform' ?round( $refund_amount * $ogInfo['commis_rate'] / 100, 2 ):0 ;//退还佣金
            $entries[$goodsId]['rf_commis'] += $commis_return_totals;
        }

        // 将entry写入数据库
        $jdyEntryModel->where(array('ob_id'=>$bill['ob_id']))->delete();
        $res = $jdyEntryModel->insertAll(array_values($entries));

        if (!$res) {
            throw new Exception("批量处理结算单商品失败");
        }
    }

    private function _newEntry($ob_id, $goods_id, $goods_name, $manage_type)
    {
        return array(
            'ob_id' => $ob_id,
            "manage_type" => $manage_type === 'co_construct' ? 1 : 2,
            "goods_id" => $goods_id,
            "goods_name" => $goods_name,
            "goods_num" => 0,
            "price" => 0,
            "remark" => "",
            "cost" => 0,
            "amount" => 0,
            "rpt_bill" => 0,
            "mj_bill" => 0,
            "xs_bill" => 0,
            "commis_bill" => 0,
            "refund" => 0,
            "rf_rpt_bill" => 0,
            "rf_mj_bill" => 0,
            "rf_xs_bill" => 0,
            "rf_commis" => 0,
            "refund_qty" => 0,
        );
    }

    /**
     * 映射Entry
     */
    public function mapEntries()
    {
        /** @var jdy_entryModel $jdyEntryModel */
        $jdyEntryModel = model("jdy_entry");
        /** @var jdy_mappingModel $mappingModel */
        $mappingModel = model('jdy_mapping');
        $entries = $jdyEntryModel->getList(array(
            //待映射、映射错误的
            'state' => array('in', array(jdy_entryModel::STATE_CREATED, jdy_entryModel::STATE_MAPPING)),//0,10
            'map_time' => array('lt', time() - 3600),
        ));

        $entryIds = array_column($entries, 'id');
        if (empty($entryIds)) return;
        //时间锁定
        $jdyEntryModel->editItem(array('map_time' => time()), array('id' => array('in', $entryIds)));

        $goodsIds = array_unique(array_column($entries, 'goods_id'));
        if (empty($goodsIds)) return;
        //获取映射信息
        $mapping = $mappingModel->getList(array('goods_id' => array('in', $goodsIds)), 999999, '', '*');
        $mapping = array_under_reset($mapping, 'goods_id');//array_column [goods_id=>$v]
        foreach ($entries as $entry) {
            $this->_mapEntry($entry, $mapping);
        }
    }

    private function _mapEntry($entry, $mapping)
    {
        /** @var jdy_entryModel $jdyEntryModel */
        $jdyEntryModel = model("jdy_entry");
        /** @var jdy_mappingModel $jdyMappingModel */
        $jdyMappingModel = model("jdy_mapping");
        static $goodsCache = array();
        if (!isset($mapping[$entry['goods_id']])) {
            $jdyEntryModel->editItem(array('map_msg' => "商品{$entry['goods_name']}:{$entry['goods_id']}没有映射"), array('id' => $entry['id']));
            return false;
        }

        $map = $mapping[$entry['goods_id']];//一条映射信息
        $supplier_number = $map["supplier_number"];
        $supplier_name = $map["supplier"];

        //没有精斗云单位编号的数据
        if (empty($map['unit_no'])) {
            if (!isset($goodsCache[$entry['goods_id']])) {
                //根据jdy 商品编号获取商品信息
                $goodsCache[$entry['goods_id']] = $this->getGoodsInfo($map['item_code']);
            }
            $info = $goodsCache[$entry['goods_id']];
            //没有信息则映射错误
            if (empty($info)) {
                $jdyEntryModel->editItem(array('state' => jdy_entryModel::STATE_MAPPING), array('id' => $entry['id']));
                return false;
            }

            //根据id更新编码
            $map['unit_no'] = $info['unitNo'];
            $jdyMappingModel->edit(array('unit_no' => $info['unitNo']), array('mapping_id' => $map['mapping_id']));
        }

        $update = array(
            "state" => jdy_entryModel::STATE_MAPPED,//1
            "jdy_goods_code" => $map['item_code'],
            "unit" => $map['unit_no'],
            "unit_multiple" => $map['unit_multiple'],
            "jdy_supplier_number" => $supplier_number,
            "jdy_supplier_name" => $supplier_name,
            "jdy_purchase_number" => "",//采购订单编号：NM-结算单号-供应商编号
        );
        $jdyEntryModel->editItem($update, array('id' => $entry['id']));
        return true;
    }

    /**
     * 推送结算单，分为订单和退单
     */
    public function pushBills()
    {
        // 查询已经匹配的商品，
        // 查询未推送的账单，循环推送
        /** @var billModel $billModel */
        $billModel = model("bill");
        $where = [
            //1,10,30
            'jdy_state' => ['in',[billModel::JDY_STATE_PARSED, billModel::JDY_STATE_PUSHING,billModel::JDY_STATE_PUSH_ERROR]],
            'jdy_push_time' => ['lt', time() - 3600],
            'ob_order_totals' => ['gt', 0],//订单金额>0
            'ob_create_date' => ["gt", strtotime($this->sys_start_date)]
        ];
        $bills = $billModel->getOrderBillList($where, '*', 100);

        if (empty($bills)) return true;

        //锁定订单时间
        $billModel->editOrderBill(['jdy_push_time' => time()],['ob_id' => ['in', array_column($bills, 'ob_id')]]);

        foreach ($bills as $bill) {
            if ($bill['ob_order_totals'] <= 0) continue;
            try {
                $this->_pushBill($bill);
            } catch (Exception $exception) {
                $error_data = [
                    'jdy_state' => billModel::JDY_STATE_PUSH_ERROR,//推送失败，如果映射成功后下次可以继续推送
                    'jdy_push_time' => time(),
                    'jdy_msg' => $exception->getMessage()
                ];
                $billModel->editOrderBill($error_data,['ob_id' => $bill['ob_id']]);
                continue;
            }
        }
        return false;
    }

    /**
     * 推送订单
     * @param $bill
     */
    public function _pushBill($bill)
    {
        /** @var billModel $billModel */
        $billModel = model("bill");
        /** @var jdy_entryModel $jdyEntryModel */
        $jdyEntryModel = model("jdy_entry");
        // 构建采购参数
        $params = $this->_buildPurchaseParams($bill);

        $responseInsert = $responseUpdate = array('code' => 0);
        //调用接口
        if (!empty($params['insert'])) {
            $responseInsert = $this->addPurchaseOrders($params['insert']);
        }
        if (!empty($params['update'])) {
            $responseUpdate = $this->updatePurchaseOrders($params['update']);
        }

        //更新entry状态
        if ($responseInsert['code'] === 0) {
            $entryIds = $this->getEntryIdsFromParam($params['insert']);
            if (!empty($entryIds)) $jdyEntryModel->editItem(array('state' => jdy_entryModel::STATE_SUCCESS), array('id' => array('in', $entryIds)));
        }
        if ($responseUpdate['code'] === 0) {
            $entryIds = $this->getEntryIdsFromParam($params['update']);
            if (!empty($entryIds)) $jdyEntryModel->editItem(array('state' => jdy_entryModel::STATE_SUCCESS), array('id' => array('in', $entryIds)));
        }

        if ($responseInsert['code'] === 0 && $responseUpdate['code'] === 0) {
            // 更新推送成功的商品
            $success_data = [
                'jdy_state' => $params['error'] > 0 ? billModel::JDY_STATE_PUSHING : billModel::JDY_STATE_PUSHED,
                'jdy_push_time' => time(),
            ];
            $billModel->editOrderBill($success_data, ['ob_id' => $bill['ob_id']]);
        } else {
            // 推送出错
            $insertMsg = $this->_getItemsErrorMessage($responseInsert);
            $updateMsg = $this->_getItemsErrorMessage($responseUpdate);
            $msg = implode(',', $insertMsg);
            $msg .= implode(',', $updateMsg);
            $error_data = [
                'jdy_state' => billModel::JDY_STATE_PUSH_ERROR,
                'jdy_push_time' => time(),
                'jdy_msg' => $msg ? $msg : '推送出错',
            ];
            $billModel->editOrderBill($error_data,array('ob_id' => $bill['ob_id']));
        }
    }

    /**
     * 构建订单参数
     * @param $bill
     * @return array
     */
    private function _buildPurchaseParams($bill)
    {
        // 根据BILL信息创建采购订单参数
        // 查询订单商品信息，循环处理供应商
        /** @var jdy_entryModel $jdyEntryModel */
        $jdyEntryModel = model("jdy_entry");
        // 根据账单ID查找
        $entries = $jdyEntryModel->getList(['ob_id' => $bill['ob_id']], '', '', '*', 999999);
        $suppliers = array();
        $date = date('Y-m-d', $bill['ob_create_date']);
        $error = 0;
        $sale_amount = $sale_price = 0;
        foreach ($entries as $key => $value) {
            if ($value['state'] == jdy_entryModel::STATE_MAPPING||$value['goods_num']<=0) {//映射错误或商品数量为0跳过
                $error++;
                continue;
            }
            $supplier_number = $value["jdy_supplier_number"];
            if (empty($supplier_number)) {//没有精斗云供应商编号跳过
                $error++;
                continue;
            }

            if (!isset($suppliers[$supplier_number])) {
                $suppliers[$supplier_number] = array(
                    'push_state' => 0,
                    'number' => '',
                    'number_base' => '',
                    'date' => $date,
                    "deptId" => "571354325381808128",
                    'amount' => 0,
                    "discRate" => "0",
                    'rpt_bill' => 0,
                    'mj_bill' => 0,
                    'xs_bill' => 0,
                    'commis_bill' => 0,
                    'refund' => 0,
                    'rf_rpt_bill' => 0,
                    'rf_mj_bill' => 0,
                    'rf_xs_bill' => 0,
                    'rf_commis' => 0,
                    'supplierNumber' => $supplier_number,// 供应商编号
                    'remark' => "汉购网平台结算单：{$bill['ob_id']},结算日期:$date,供应商：{$value["jdy_supplier_name"]}。",//备注信息
                    'entries' => array(),
                );
            }

            $rpt_bill = ($value['rpt_bill']>0 && $value['manage_type'] == 2)?",红包优惠金额：".ncPriceFormat($value['rpt_bill']):'';
            if($bill['ob_store_manage_type']=="platform"){
                $amount = $value['amount']-$value['commis_bill']+$value['rpt_bill'];
                $sale_price = $value['amount'];
            }else{
                $amount = $value['cost'];
                $sale_price = $value['cost'];
            }
            $entry = array(
                "id" => $value['id'],
                "productNumber" => $value['jdy_goods_code'],
                "location" => "HGWA",
                "qty" => $value['goods_num'],
                "price" => $amount/$value['goods_num'],
                "discRate" => "0",
                "deptId" => "571354325381808128",
                "unit" => $value['unit'],
                "remark" => "商品销售金额：".ncPriceFormat($sale_price)."佣金金额：".ncPriceFormat($value['commis_bill'])."{$rpt_bill},满减优惠金额：".
                    ncPriceFormat($value['mj_bill']).",限时折扣优惠金额：".ncPriceFormat($value['xs_bill']).",平台红包：".ncPriceFormat($value['rpt_bill']),
                /*接口暂时不支持源购货订单，会报错*/
//                "sourceOrder"=>$value['jdy_purchase_number']
            );

            $supplier = $suppliers[$supplier_number];
            if ($value['state'] == jdy_entryModel::STATE_SUCCESS) {
                $supplier['push_state'] += 1;//推送成功的数量
            }
            $sale_amount += $sale_price;
            $supplier['amount'] += $amount;
            $supplier['rpt_bill'] += $value['rpt_bill'];
            $supplier['mj_bill'] += $value['mj_bill'];
            $supplier['xs_bill'] += $value['xs_bill'];
            $supplier['commis_bill'] += $value['commis_bill'];
            $supplier['refund'] += $value['refund'];
            $supplier['rf_rpt_bill'] += $value['rf_rpt_bill'];
            $supplier['rf_mj_bill'] += $value['rf_mj_bill'];
            $supplier['rf_xs_bill'] += $value['rf_xs_bill'];
            $supplier['rf_commis'] += $value['rf_commis'];
            $supplier['entries'][] = $entry;
            $suppliers[$supplier_number] = $supplier;
        }

        $res = array('error' => $error, 'insert' => array(), 'update' => array());

        foreach ($suppliers as $supplier) {
            $number = $this->getPurchaseSn($bill,$supplier);
            $supplier['number'] = $number['number'];//采购订单编号：HG-结算单号-供应商编号
            $supplier['number_base'] = $number['number_base'];//采购订单编号：结算单号-供应商编号
            if (count($supplier['entries']) <= $supplier['push_state']) continue;
            $supplier['remark'] .= "商品销售金额：".ncPriceFormat($sale_amount);
            if($supplier['commis_bill']>0 && ($bill['ob_store_manage_type']=="platform")) $supplier['remark'] .= ", 佣金：".ncPriceFormat($supplier['commis_bill']);

            if ($supplier['rpt_bill'] > 0 && ($bill['ob_store_manage_type']=="platform")) $supplier['remark'] .= ", 红包优惠金额：".ncPriceFormat($supplier['rpt_bill']);
            if ($supplier['mj_bill'] > 0) $supplier['remark'] .= ", 满减优惠金额：".ncPriceFormat($supplier['mj_bill']);
            if ($supplier['xs_bill'] > 0) $supplier['remark'] .= ", 限时折扣优惠金额：".ncPriceFormat($supplier['xs_bill']);
//            if ($supplier['refund'] > 0) $supplier['remark'] .= "; 退款金额：".ncPriceFormat($supplier['refund']);
//            if ($supplier['rf_commis'] > 0 && $supplier['manage_type'] == 2) $supplier['remark'] .= ",退还佣金：".ncPriceFormat($supplier['rf_commis']);
            $supplier['remark'] .= "; 平台红包：".ncPriceFormat($bill['ob_rpt_amount']);
//            $supplier['remark'] .= "; 全部退款时应扣除的平台红包：".ncPriceFormat($bill['ob_rf_rpt_amount']);
//            if ($supplier['rf_rpt_bill'] > 0 && $supplier['manage_type'] == 2) $supplier['remark'] .= ", 退款红包金额：".ncPriceFormat($supplier['rf_rpt_bill']);
            if ($supplier['rf_mj_bill'] > 0) $supplier['remark'] .= ", 退款满减金额：".ncPriceFormat($supplier['rf_mj_bill']);
            if ($supplier['rf_xs_bill'] > 0) $supplier['remark'] .= ", 退款限时折扣金额：".ncPriceFormat($supplier['rf_xs_bill']);

            if ($supplier['push_state'] > 0) {
                $res['update'][] = $supplier;
            } else {
                $res['insert'][] = $supplier;
            }
        }
        return $res;
    }

    public function addPurchaseOrders($items)
    {
        $url = 'http://api.kingdee.com/jdyapi/scm/purchaseOrder/add';
        $interface_info = $this->httpPost($url, array('items' => $items));
        return $interface_info;
    }

    public function updatePurchaseOrders($items)
    {
        $url = 'http://api.kingdee.com/jdyapi/scm/purchaseOrder/update';
        $interface_info = $this->httpPost($url, array('items' => $items));
        return $interface_info;
    }

    public function addPurchaseReturn($items){
        $url = 'http://api.kingdee.com/jdyapi/scm/purchase/return_add';
        $interface_info = $this->httpPost($url, array('items' => $items));
        return $interface_info;
    }

    public function updatePurchaseReturn($items){
        $url = 'http://api.kingdee.com/jdyapi/scm/purchase/return_update';
        $interface_info = $this->httpPost($url, array('items' => $items));
        return $interface_info;
    }

    /**
     * @param $items
     * @return array
     */
    private function getEntryIdsFromParam($items)
    {
        $res = array();
        foreach ($items as $item) {
            $entries = $item['entries'];
            $ids = array_column($entries, 'id');
            $res = array_merge($res, $ids);
        }
        return $res;
    }

    private function _getItemsErrorMessage($response)
    {
        $items = ArrayHelper::getValue($response, 'items', array());
        $messages = array();
        foreach ($items as $item) {
            $messages[] = ArrayHelper::getValue($item, 'msg', '');
        }
        return $messages;
    }

    public function getGoodsInfo($code)
    {
        $url = 'http://api.kingdee.com/jdyapi/product/item/list';
        $response = $this->httpPost($url, array('itemCode' => $code));
        if (isset($response[0])) return $response[0];
        return null;
    }

    /**
     * 推送退单
     * @return bool
     */
    public function purchaseReturn(){
        ini_set('memory_limit','3G');
        $billModel = model("bill");
        $where = [
            //1,10,30
            'jdy_refund_state'=>['in',[$billModel::JDY_REFUND_STATE_PARSED,$billModel::JDY_REFUND_STATE_PUSHING,$billModel::JDY_REFUND_STATE_PUSH_ERROR]],
            'jdy_refund_time' => ['lt', time() - 3600],
            'ob_order_totals' => ['gt', 0],
            'ob_create_date' => ["gt", strtotime($this->sys_start_date)]
        ];
        $bills = $billModel->getOrderBillList($where, '*', 100);
        if (empty($bills)) return true;

        //锁定时间
        $billModel->editOrderBill(['jdy_refund_time' => time()],['ob_id' => ['in', array_column($bills, 'ob_id')]]);

        foreach ($bills as $bill) {
            try {
                $this->_pushRefund($bill);
            } catch (Exception $exception) {
                $billModel->editOrderBill(
                    array(
                        'jdy_refund_state' => billModel::JDY_REFUND_STATE_PUSH_ERROR,//解析成功后下次继续推送
                        'jdy_refund_time' => time(),
                        'jdy_msg' => $exception->getMessage()
                    ),
                    array('ob_id' => $bill['ob_id']));
                continue;
            }
        }

        return false;
    }

    public function _pushRefund($bill)
    {
        $billModel = model("bill");
        $jdyEntryModel = model("jdy_entry");

        // 构建采购参数
        $params = $this->_buildPurchaseReturnParams($bill);

        $responseInsert = $responseUpdate = array('code' => 0);
        if (!empty($params['insert'])) {
            $responseInsert = $this->addPurchaseReturn($params['insert']);
        }
        if (!empty($params['update'])) {
            $responseUpdate = $this->updatePurchaseReturn($params['update']);
        }

        if ($responseInsert['code'] === 0) {
            $entryIds = $this->getEntryIdsFromParam($params['insert']);
            if (!empty($entryIds)) $jdyEntryModel->editItem(array('refund_state' => jdy_entryModel::REFUND_STATE_SUCCESS), array('id' => array('in', $entryIds)));
        }
        if ($responseUpdate['code'] === 0) {
            $entryIds = $this->getEntryIdsFromParam($params['update']);
            if (!empty($entryIds)) $jdyEntryModel->editItem(array('refund_state' => jdy_entryModel::REFUND_STATE_SUCCESS), array('id' => array('in', $entryIds)));
        }
        if ($responseInsert['code'] === 0 && $responseUpdate['code'] === 0) {
            // 更新推送成功的商品
            $billModel->editOrderBill(
                array(
                    'jdy_refund_state' => $params['error'] > 0 ? billModel::JDY_REFUND_STATE_PUSHING : billModel::JDY_REFUND_STATE_PUSHED,
                    'jdy_refund_time' => time(),
                ),
                array('ob_id' => $bill['ob_id'])
            );
        } else {
            // 推送出错
            $insertMsg = $this->_getItemsErrorMessage($responseInsert);
            $updateMsg = $this->_getItemsErrorMessage($responseUpdate);
            $msg = implode(',', $insertMsg);
            $msg .= implode(',', $updateMsg);
            $billModel->editOrderBill(
                array(
                    'jdy_refund_state' => billModel::JDY_REFUND_STATE_PUSH_ERROR,
                    'jdy_refund_time' => time(),
                    'jdy_msg' => $msg ? $msg : '退单推送出错',
                ),
                array('ob_id' => $bill['ob_id'])
            );
        }
    }

    /**
     * 构建退单参数
     * @param $bill
     * @return array
     */
    private function _buildPurchaseReturnParams($bill)
    {
        // 根据BILL信息创建采购订单参数
        // 查询订单商品信息，循环处理供应商
        /** @var jdy_entryModel $jdyEntryModel */
        $jdyEntryModel = model("jdy_entry");
        // 根据账单ID查找
        $entries = $jdyEntryModel->getList(array('ob_id' => $bill['ob_id']), '', '', '*', 999999);

        $date = date('Y-m-d', $bill['ob_create_date']);
        $error = 0;
        $refund_price = 0;
        foreach ($entries as $key => $value) {
            //过滤entry
            if($value['refund_qty'] == 0){//退单商品数量
                continue;
            }
            if ($value['refund_state'] == jdy_entryModel::REFUND_STATE_PUSH_ERROR || $value['goods_num'] <= 0) {//推送失败或者订单商品数量<0
                $error++;
                continue;
            }
            $supplier_number = $value["jdy_supplier_number"];
            if (empty($supplier_number)) {//没有映射成功
                $error++;
                continue;
            }

            $deptId = '571354325381808128';//???
            if (!isset($suppliers[$supplier_number])) {
                $suppliers[$supplier_number] = array(
                    'push_state' => 0,
                    'number' => '',
                    'number_base' => '',
                    'date' => $date,
                    "deptId" => $deptId,
                    'amount' => 0,
                    "discRate" => "0",
                    'rpt_bill' => 0,
                    'mj_bill' => 0,
                    'xs_bill' => 0,
                    'commis_bill' => 0,
                    'refund' => 0,
                    'rf_rpt_bill' => 0,
                    'rf_mj_bill' => 0,
                    'rf_xs_bill' => 0,
                    'rf_commis' => 0,
                    'supplierNumber' => $supplier_number,// 供应商编号
                    'remark' => "汉购网平台结算单：{$bill['ob_id']},结算日期:$date,供应商：{$value["jdy_supplier_name"]}。",//备注信息
                    'entries' => array(),
                );
            }

            if($bill['ob_store_manage_type']=="platform"){
                $refund = $value['refund'] - $value['rf_commis'] + $value['rf_rpt_bill'];
            }else{
                $refund = $value['refund'];
            }
            $rf_commis = $value['rf_commis']>0?"，退款佣金金额：".ncPriceFormat($value['rf_commis']):'';
            $rf_rpt_bill = $value['rf_rpt_bill']>0?"，退款红包金额：".ncPriceFormat($value['rf_rpt_bill']):'';
            $refund_price += $value['refund'];
            $entry = array(
                "id" => $value['id'],
                "productNumber" => $value['jdy_goods_code'],
                "location" => "HGWA",
                "qty" => $value['refund_qty'],
                "price" => $value['refund']/$value['refund_qty'],
                "qty" => $value['refund_qty']<0?1:$value['refund_qty'],
                "price" => ncPriceFormat($refund/$value['refund_qty']),
                "discRate" => "0",
                "deptId" => $deptId,
                "unit" => $value['unit'],
                "remark" => "退款金额：".ncPriceFormat($value['refund']).$rf_commis.$rf_rpt_bill,
                /*接口暂不支持该字段，会报错*/
//                "sourceOrder"=>$value['jdy_purchase_number']
            );
            $supplier = $suppliers[$supplier_number];
            if ($value['refund_state'] == jdy_entryModel::REFUND_STATE_SUCCESS) {
                $supplier['push_state'] += 1;//推送成功的数量
            }
            $supplier['amount'] += $value['amount'];
            $supplier['rpt_bill'] += $value['rpt_bill'];
            $supplier['mj_bill'] += $value['mj_bill'];
            $supplier['xs_bill'] += $value['xs_bill'];
            $supplier['commis_bill'] += $value['commis_bill'];
            $supplier['refund'] += $refund;
            $supplier['rf_rpt_bill'] += $value['rf_rpt_bill'];
            $supplier['rf_commis'] += $value['rf_commis'];
            $supplier['rf_mj_bill'] += $value['rf_mj_bill'];
            $supplier['rf_xs_bill'] += $value['rf_xs_bill'];
            $supplier['entries'][] = $entry;
            $suppliers[$supplier_number] = $supplier;
        }

        $res = array('error' => $error, 'insert' => array(), 'update' => array());
        foreach ($suppliers as $supplier) {
            $number = $this->getPurchaseSn($bill,$supplier);
            $supplier['number'] = $number['number'];//采购订单编号：HG-结算单号-供应商编号
            $supplier['number_base'] = $number['number_base'];//采购订单编号：结算单号-供应商编号

            if (count($supplier['entries']) < $supplier['push_state']) continue;

//            if ($supplier['rpt_bill'] > 0) $supplier['remark'] .= ", 红包优惠金额：{$supplier['rpt_bill']}";
//            if ($supplier['mj_bill'] > 0) $supplier['remark'] .= ", 满减优惠金额：{$supplier['mj_bill']}";
//            if ($supplier['xs_bill'] > 0) $supplier['remark'] .= ", 限时折扣优惠金额：{$supplier['xs_bill']}";
            if ($supplier['refund'] > 0) $supplier['remark'] .= ";退款金额：".ncPriceFormat($refund_price);
            if($bill['ob_store_manage_type']=="platform") {
                if ($supplier['rf_commis'] > 0) $supplier['remark'] .= ", 退款佣金金额：".ncPriceFormat($supplier['rf_commis']);
                if ($supplier['rf_rpt_bill'] > 0) $supplier['remark'] .= ", 退款红包金额：".ncPriceFormat($supplier['rf_rpt_bill']);
            }
            if ($supplier['rf_mj_bill'] > 0) $supplier['remark'] .= ", 退款满减金额：".ncPriceFormat($supplier['rf_mj_bill']);
            if ($supplier['rf_xs_bill'] > 0) $supplier['remark'] .= ", 退款限时折扣金额：".ncPriceFormat($supplier['rf_xs_bill']);
            if ($supplier['push_state'] > 0) {
                $res['update'][] = $supplier;
            } else {
                $res['insert'][] = $supplier;
            }
        }
        return $res;
    }

    private function getOrderGoods($order_ids)
    {
        /** @var orderModel $orderModel */
        $orderModel = Model('order');
        $order_goods_condition = array();
        $order_goods_condition['order_id'] = array('in',$order_ids);
        $field = '*';
        $order_goods_list = $orderModel->getOrderGoodsList($order_goods_condition,$field,9999999);

        $return = array() ;
        foreach ($order_goods_list as $order_goods)
        {
            $return[ $order_goods['order_id'] ][ $order_goods['goods_id'] ] = $order_goods ;
        }

        return $return ;
    }

    /**
     * 根据order_bill 和 jdy_entry 计算订单编号
     * @param $bill
     * @param $entry
     * @return array|bool
     */
    private function getPurchaseSn($bill,$entry){
        if(!$bill || !$entry){
            return ['number'=>"",'number_base'=>""];
        }
        $type = $bill['ob_store_manage_type']=="platform"?"P":'C';

        $refund_type = 'A';
        ($entry['amount']>0 && $entry['refund']<=0) && $refund_type = 'O';
        ($entry['amount']<=0 && $entry['refund']>0) && $refund_type = 'R';

        $obNumber = str_pad($bill['ob_id'], 6, '0', STR_PAD_LEFT);
        $supplier_number = $entry["supplierNumber"];

        if(!$obNumber || !$supplier_number) return false;

        $number = [
            'number'=>"HG-{$type}-{$refund_type}-{$obNumber}-{$supplier_number}",
            'number_base'=>"{$refund_type}-{$obNumber}-{$supplier_number}",
        ];
        return $number;
    }
}
