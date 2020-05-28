<?php
/**
 * 结算管理
 *
 *
 *
 *
 * @汉购网提供技术支持 授权请购买shopnc授权
 * @license    http://www.hangowa.com
 * @link       交流群号：
 */


defined('ByShopWWI') or exit('Access Invalid!');

class shequ_billControl extends SystemControl
{
    /**
     * 每次导出订单数量
     * @var int
     */
    const EXPORT_SIZE = 1000;

    private $links = array(
        array('url' => 'act=shequ_bill&op=index', 'lang' => 'nc_manage'),
    );

    public function __construct()
    {
        parent::__construct();
        model('bill');
    }

    /**
     * 结算单列表
     *
     */
    public function indexOp()
    {
        //网 店 运 维shop wwi.com
        Tpl::output('current', trim($_GET['gname']));
        Tpl::output('gname', $this->admin_info['gname']);
        Tpl::setDirquna('shop');
        Tpl::showpage('shequ_bill.index');
    }

    /**
     * 某店铺某月订单列表
     *
     */
    public function show_billOp()
    {
        $ob_id = intval($_GET['ob_id']);
        if ($ob_id <= 0) {
            showMessage('参数错误', '', 'html', 'error');
        }
        /** @var shequ_billModel $model_bill */
        $model_bill = Model('shequ_bill');
        $jdy_entryModel = Model('jdy_entry');
        $bill_info = $model_bill->getOne(array('ob_id' => $ob_id));
        if (!$bill_info) {
            showMessage('参数错误', '', 'html', 'error');
        }

        $order_condition = array();
        $order_condition['order_state'] = ORDER_STATE_SUCCESS;
        $order_condition['store_id'] = $bill_info['ob_store_id'];
        $if_start_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/', $_GET['query_start_date']);
        $if_end_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/', $_GET['query_end_date']);
        $start_unixtime = $if_start_date ? strtotime($_GET['query_start_date']) : null;
        $end_unixtime = $if_end_date ? strtotime($_GET['query_end_date']) : null;
        $end_unixtime = $if_end_date ? $end_unixtime + 86400 - 1 : null;
        if ($if_start_date || $if_end_date) {
            $order_condition['finnshed_time'] = array('between', "{$start_unixtime},{$end_unixtime}");
        } else {
            $order_condition['finnshed_time'] = array('between', "{$bill_info['ob_start_date']},{$bill_info['ob_end_date']}");
        }
        if ($_GET['query_type'] == 'refund') {
            $sub_tpl_name = 'shequ_order_bill.show.refund_list';
        }elseif ($_GET['query_type'] == 'pay_log') {
            $sub_tpl_name = 'shequ_order_bill.show.pay_log';
        }else{
            //订单列表
            $sub_tpl_name = 'shequ_order_bill.show.order_list';
        }

        /** @var orderModel $order_model */
        $order_model = Model('order');
        $orderInfo = $order_model->getOrderInfo($order_condition,array(),'SUM(shipping_fee) as shipping_fee');

        //平台应付金额、账单毛利的计算公式的HTML
        $platform_amount = $this->getPlatformAmount($bill_info);

        Tpl::output('platform_amount', $platform_amount);
        Tpl::output('shipping_fee', ncPriceFormat($orderInfo['shipping_fee']));
        Tpl::output('tpl_name', $sub_tpl_name);
        Tpl::output('gname', $this->admin_info['gname']);
        Tpl::output('bill_info', $bill_info);
        //网 店 运 维shop wwi.com
        Tpl::setDirquna('shop');
        Tpl::showpage('shequ_order_bill.show');
    }

    public function edit_rptOp()
    {
        $ob_id = intval($_GET['ob_id']);
        $order_id = intval($_GET['order_id']);
        if ($order_id <= 0) {
            showMessage(L('miss_order_number'));
        }
        $model_order = Model('order');
        $order_info = $model_order->getOrderInfo(array('order_id' => $order_id), array('order_goods', 'order_common', 'store'));
        $order_list = array($order_id => $order_info);
        $model_refund_return = Model('refund_return');
        $order_list = $model_refund_return->getGoodsRefundList($order_list, 1);//订单商品的退款退货显示
        $order_info = $order_list[$order_id];

        foreach ($order_info['extend_order_goods'] as $value) {
            $value['image_60_url'] = cthumb($value['goods_image'], 60, $value['store_id']);
            $value['image_240_url'] = cthumb($value['goods_image'], 240, $value['store_id']);
            $value['goods_type_cn'] = orderGoodsType($value['goods_type']);
            $value['goods_url'] = urlShop('goods', 'index', array('goods_id' => $value['goods_id']));
            if ($value['goods_type'] == 5) {
                $order_info['zengpin_list'][] = $value;
            } else {
                $order_info['goods_list'][] = $value;
            }
        }

        if (empty($order_info['zengpin_list'])) {
            $order_info['goods_count'] = count($order_info['goods_list']);
        } else {
            $order_info['goods_count'] = count($order_info['goods_list']) + 1;
        }

        //取得订单其它扩展信息
        $model_order->getOrderExtendInfo($order_info);

        //商家信息
        $store_info = Model('store')->getStoreInfo(array('store_id' => $order_info['store_id']));
        Tpl::output('store_info', $store_info);

        Tpl::output('ob_id', $ob_id);
        Tpl::output('order_info', $order_info);
        Tpl::setDirquna('shop');/*网 店 运 维shop wwi.com*/
        Tpl::showpage('shequ_order_bill.edit.rpt', 'null_layout');
    }

    public function edit_orderOp()
    {
        $ob_id = intval($_GET['ob_id']);
        $order_id = intval($_GET['order_id']);
        if ($order_id <= 0) {
            showMessage(L('miss_order_number'));
        }
        $model_order = Model('order');
        $order_info = $model_order->getOrderInfo(array('order_id' => $order_id), array('order_goods', 'order_common', 'store'));
        $order_list = array($order_id => $order_info);
        $model_refund_return = Model('refund_return');
        $order_list = $model_refund_return->getGoodsRefundList($order_list, 1);//订单商品的退款退货显示
        $order_info = $order_list[$order_id];

        foreach ($order_info['extend_order_goods'] as $value) {
            $value['image_60_url'] = cthumb($value['goods_image'], 60, $value['store_id']);
            $value['image_240_url'] = cthumb($value['goods_image'], 240, $value['store_id']);
            $value['goods_type_cn'] = orderGoodsType($value['goods_type']);
            $value['goods_url'] = urlShop('goods', 'index', array('goods_id' => $value['goods_id']));
            if ($value['goods_type'] == 5) {
                $order_info['zengpin_list'][] = $value;
            } else {
                $order_info['goods_list'][] = $value;
            }
        }

        if (empty($order_info['zengpin_list'])) {
            $order_info['goods_count'] = count($order_info['goods_list']);
        } else {
            $order_info['goods_count'] = count($order_info['goods_list']) + 1;
        }

        //取得订单其它扩展信息
        $model_order->getOrderExtendInfo($order_info);

        //商家信息
        $store_info = Model('store')->getStoreInfo(array('store_id' => $order_info['store_id']));
        Tpl::output('store_info', $store_info);

        Tpl::output('ob_id', $ob_id);
        Tpl::output('order_info', $order_info);
        Tpl::setDirquna('shop');/*网 店 运 维shop wwi.com*/
        Tpl::showpage('shequ_order_bill.edit', 'null_layout');
    }

    public function edit_recOp()
    {
        $ob_id = intval($_GET['ob_id']);
        $order_id = intval($_GET['order_id']);
        $rec_id = intval($_GET['rec_id']);
        $field = trim($_GET['field']);
        $value = trim($_GET['value']);
        if ($order_id <= 0) {
            showMessage(L('miss_order_number'));
        }
        if (!in_array($field, array('goods_cost', 'commis_rate', 'rpt_bill'))) {
            die(json_encode(array('error' => 1, 'msg' => '修改字段错误！')));
        }
        /** @var orderModel $model_order */
        $model_order = Model('order');
        $order_info = $model_order->getOrderInfo(array('order_id' => $order_id));
        if ($order_info['purchase_sap']) {
            die(json_encode(array('error' => 1, 'msg' => '订单已推送SAP，不允许修改！')));
        }
        // TODO 若ob_id为0，查询订单是否有对应的结算单，若有结算单，判断，结算单时候允许修改
        /** @var orderModel $orderModel */
        $orderModel = Model('order');
        $orderModel->beginTransaction();
        /** @var billModel $billModel */
        $billModel = Model('shequ_bill');
        if ($ob_id == 0) {
            $map = array(
                'ob_store_id' => $order_info['store_id'],
                'ob_start_date' => array('lt', $order_info['finnshed_time']),
                'ob_end_date' => array('gt', $order_info['finnshed_time']),
            );
        } else {
            $map = array('ob_id' => $ob_id,);
        }
        $billInfo = $billModel->getOne($map);
        if (!empty($billInfo)) {
            if (!in_array($billInfo['ob_state'], array(BILL_STATE_HANGO, BILL_STATE_FIRE_PHONIX))) {
                die(json_encode(array('error' => 1, 'msg' => '账单已经过确认，或者商家正在修改！')));
            }
            $ob_id = $billInfo['ob_id'];
            if ($billInfo['ob_state'] == BILL_STATE_CREATE) {
                $updateRes = $billModel->editOrderBill(array('ob_state' => BILL_STATE_HANGO), array('ob_id' => $ob_id));
                if (!$updateRes) {
                    $orderModel->rollback();
                    die(json_encode(array('error' => 1, 'msg' => '修改失败！')));
                }
            }
        }
        $orderGoods = $orderModel->getOrderGoodsInfo(array('rec_id' => $rec_id));
        $updateArray = array($field => $value);
        $res = array('msg' => 'success', 'data' => $orderGoods);
        if ($field == 'commis_rate') {
            if ($orderGoods['manage_type'] == 'platform') {
                $res['commis'] = number_format(($orderGoods['goods_pay_price'] + $orderGoods['rpt_bill']) * $value / 100, 2);
                $updateArray['goods_cost'] = $orderGoods['goods_pay_price'] - $res['commis'] + $orderGoods['rpt_bill'];
                $res['cost'] = $updateArray['goods_cost'];
            } else {
                $res['commis'] = number_format($orderGoods['goods_cost'] * $value / 100, 2);
            }
        }
        $updateRes = $orderModel->table('order_goods')->where(array('rec_id' => $rec_id))->update($updateArray);
        if (!$updateRes) {
            $orderModel->rollback();
            die(json_encode(array('error' => 1, 'msg' => '修改失败！')));
        }
        $attArray = array('goods_cost' => '成本', 'commis_rate' => '佣金比例', 'rpt_bill' => '平台红包');
        // 添加操作日志
        $logArray = array(
            'ob_id' => $ob_id,
            'rec_id' => $orderGoods['rec_id'],
            'order_id' => $orderGoods['order_id'],
            'order_sn' => $order_info['order_sn'],
            'log_model' => 'order_goods',
            'log_attribute' => $field,
            'old_value' => $orderGoods[$field],
            'new_value' => $value,
            'log_msg' => "将订单【{$order_info['order_sn']}】的商品【{$orderGoods['goods_name']}】的【{$attArray[$field]}】从【{$orderGoods[$field]}】调整为【{$value}】",
            'log_time' => TIMESTAMP,
            'log_role' => 1,
            'log_user' => $this->admin_info['name'],
        );
        $addRes = $orderModel->table('bill_log')->insert($logArray);
        if (!$addRes) {
            $orderModel->rollback();
            die(json_encode(array('error' => 1, 'msg' => '修改失败！')));
        }
        $orderModel->commit();
        // 更新订单成本金额&红包金额
        if (isset($updateArray['goods_cost']) || isset($updateArray['rpt_bill'])) {
            $orderModel->execute(
                "UPDATE shopwwi_orders as a INNER JOIN 
(
SELECT SUM(goods_cost)as cost_amount,SUM(rpt_bill)as rpt_bill, order_id
FROM shopwwi_order_goods
WHERE order_id = {$orderGoods['order_id']}
GROUP BY order_id
) as b
on a.order_id = b.order_id
SET a.cost_amount = b.cost_amount,a.rpt_bill = b.rpt_bill
WHERE a.order_id = {$orderGoods['order_id']};");
        }
        die(json_encode($res));
    }

    public function edit_refundOp()
    {
        $ob_id = intval($_GET['ob_id']);
        $refund_id = intval($_GET['refund_id']);
        if ($refund_id <= 0) {
            showMessage(L('miss_order_number'));
        }

        /** @var refund_returnModel $model_refund_return */
        $model_refund_return = Model('refund_return');
        $refundInfo = $model_refund_return->getRefundReturnInfo(array('refund_id' => $refund_id));

        /** @var orderModel $orderModel */
        $orderModel = Model('order');
        $recInfo = $orderModel->getOrderGoodsInfo(array('rec_id' => $refundInfo['order_goods_id']));
        //商家信息
        $store_info = Model('store')->getStoreInfo(array('store_id' => $refundInfo['store_id']));
        if (chksubmit()) {
            $field = trim($_GET['field']);
            $value = trim($_GET['value']);
            if ($refund_id <= 0 || $ob_id <= 0) {
                die(json_encode(array('error' => 1, 'msg' => '退款单ID或者账单ID错误！')));
            }
            if (!in_array($field, array('refund_amount'))) {
                die(json_encode(array('error' => 1, 'msg' => '修改字段错误！')));
            }
            $orderModel->beginTransaction();
            /** @var billModel $billModel */
            $billModel = Model('shequ_bill');
            $map = array('ob_id' => $ob_id,);
            $billInfo = $billModel->getOne($map);
            if (!empty($billInfo)) {
                if (!in_array($billInfo['ob_state'], array(BILL_STATE_HANGO, BILL_STATE_FIRE_PHONIX))) {
                    die(json_encode(array('error' => 1, 'msg' => '账单已经过确认，或者商家正在修改！')));
                }
                $ob_id = $billInfo['ob_id'];
                if ($billInfo['ob_state'] == BILL_STATE_CREATE) {
                    $updateRes = $billModel->editOrderBill(array('ob_state' => BILL_STATE_HANGO), array('ob_id' => $ob_id));
                    if (!$updateRes) {
                        $orderModel->rollback();
                        die(json_encode(array('error' => 1, 'msg' => '修改失败！')));
                    }
                }
            } else {
                die(json_encode(array('error' => 1, 'msg' => '到不到账单！')));
            }
            if ($field == 'refund_amount') $field = 'refund_amount_bill';
            $updateArray = array($field => $value);
            //产生修改动作重推
            $updateArray['purchase_sap'] = 0;
            $updateRes = $model_refund_return->editRefundReturn(array('refund_id' => $refund_id), $updateArray);
            if (!$updateRes) {
                $orderModel->rollback();
                die(json_encode(array('error' => 1, 'msg' => '修改失败！')));
            }
            $attArray = array('refund_amount_bill' => '退款金额', 'commis_rate' => '佣金比例', 'rpt_bill' => '平台红包');
            $oldValue = $field == 'refund_amount_bill' && $refundInfo[$field] == -1 ? $refundInfo['refund_amount'] : $refundInfo[$field];
            // 添加操作日志
            $logArray = array(
                'ob_id' => $ob_id,
                'log_type' => 'refund',
                'rec_id' => $refund_id,
                'order_id' => $refundInfo['order_id'],
                'order_sn' => $refundInfo['order_sn'],
                'log_model' => 'refund_return',
                'log_attribute' => $field,
                'old_value' => $oldValue,
                'new_value' => $value,
                // TODO 字段信息调整
                'log_msg' => "将退款单【{$refundInfo['refund_sn']}】的【{$attArray[$field]}】从【{$oldValue}】调整为【{$value}】",
                'log_time' => TIMESTAMP,
                'log_role' => 1,
                'log_user' => $this->admin_info['name'],
            );
            $addRes = $orderModel->table('bill_log')->insert($logArray);
            if (!$addRes) {
                $orderModel->rollback();
                die(json_encode(array('error' => 1, 'msg' => '修改失败！')));
            }
            $orderModel->commit();
            die(json_encode(array('error' => 0, 'msg' => '修改成功！')));
        }
        Tpl::output('store_info', $store_info);

        Tpl::output('ob_id', $ob_id);
        Tpl::output('refund_info', $refundInfo);
        Tpl::output('rec_info', $recInfo);
        Tpl::setDirquna('shop');/*网 店 运 维shop wwi.com*/
        Tpl::showpage('shequ_order_bill.refund.edit', 'null_layout');
    }

    public function get_bill_info_xmlOp()
    {
        $ob_id = intval($_GET['ob_id']);
        if ($ob_id <= 0) {
            exit();
        }
        $model_bill = Model('shequ_bill');
        $bill_info = $model_bill->getOne(array('ob_id' => $ob_id));
        if (!$bill_info) {
            exit();
        }
        $order_condition = array();
        $order_condition['order_state'] = ORDER_STATE_SUCCESS;
        $order_condition['shequ_tz_id'] = $bill_info['ob_store_id'];
        $if_start_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/', $_GET['query_start_date']);
        $if_end_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/', $_GET['query_end_date']);
        $start_unixtime = $if_start_date ? strtotime($_GET['query_start_date']) : null;
        $end_unixtime = $if_end_date ? strtotime($_GET['query_end_date']) : null;
        $end_unixtime = $if_end_date ? $end_unixtime + 86400 - 1 : null;
        if ($if_start_date || $if_end_date) {
            $order_condition['finnshed_time'] = array('between', "{$start_unixtime},{$end_unixtime}");
        }
        if ($_GET['query_type'] == 'pay_log') {
            /** @var shequ_bill_payModel $model_order_bill_log */
            $model_order_bill_log = Model('shequ_bill_pay');
            if ($_POST['query'] != '' && in_array($_POST['qtype'], array('obl_id'))) {
                $condition[$_POST['qtype']] = array('eq', "{$_POST['query']}");
            }
            $condition['obl_ob_id'] = $bill_info['ob_id'];
            $bill_log_list = $model_order_bill_log->where($condition)->page(20)->order('obl_id desc')->select();

            $data = array();
            $data['now_page'] = $model_order_bill_log->shownowpage();
            $data['total_num'] = $model_order_bill_log->gettotalnum();
            foreach ($bill_log_list as $log_info) {
                $list = array();
                $list['obl_id'] = $log_info['obl_id'];
                $list['obl_pay_date'] = date('Y-m-d', $log_info['obl_pay_date']);
                $list['obl_success_amount'] = ncPriceFormat($log_info['obl_success_amount']);
                $list['payment_sn'] = $log_info['payment_sn'];
                $list['attachment'] = $log_info['attachment']?"<a href='".$log_info['attachment']."' target='_blank' class='pic-thumb-tip' onMouseOut='toolTip()' onMouseOver='toolTip(\"<img src=".$log_info['attachment'].">\")'><i class='fa fa-picture-o'></i></a>":'暂无';
                $list['obl_pay_content'] = $log_info['obl_pay_content'];
                $data['list'][$log_info['obl_id']] = $list;
            }
            exit(Tpl::flexigridXML($data));
        }
        else {
            $order_condition['shequ_tuan_id'] = array('gt','0'); //使用索引
            $order_condition['refund_amount'] = 0;
            if ($_GET['query_type'] == 'refund'){
                $order_condition['refund_amount'] = array('gt',0);
            }
            $order_condition['shequ_tz_id'] = $bill_info['ob_store_id'];
            $order_condition['shequ_tz_bill_id'] = $bill_info['ob_id'];
            //订单列表
            /** @var orderModel $model_order */
            $model_order = Model('order');
            if ($_POST['query'] != '' && in_array($_POST['qtype'], array('order_sn', 'buyer_name'))) {
                $order_condition[$_POST['qtype']] = array('like', "%{$_POST['query']}%");
            }
            if ($_POST['query'] != '' && in_array($_POST['qtype'], array('check_status','purchase_sap', 'send_sap'))) {
                $order_condition[$_POST['qtype']] = array('eq', "{$_POST['query']}");
            }
            if($_GET['search_type'] != ''){
                if($_GET['search_type'] == '已结算'){
                    $post_query = array('in','1,2,3');
                } else if($_GET['search_type'] == '未对账'){
                    $post_query = 0;
                }
                $order_condition['check_result'] = $post_query;
            }

            if ($_GET['order_sn'] != '') {
                $order_condition['order_sn'] = array('like', "%{$_GET['order_sn']}%");
            }
            if ($_GET['purchase_sap'] != '') {
                $order_condition['purchase_sap'] = array('eq', "{$_GET['purchase_sap']}");
            }
            if ($_GET['buyer_name'] != '') {
                if ($_GET['jq_query']) {
                    $order_condition['buyer_name'] = $_GET['buyer_name'];
                } else {
                    $order_condition['buyer_name'] = array('like', "%{$_GET['buyer_name']}%");
                }
            }
            $sort_fields = array('order_amount', 'shipping_fee', 'commis_amount', 'add_time', 'shequ_bill_time', 'buyer_id', 'store_id', 'store_id','rpt_bill');
            if (in_array($_POST['sortorder'], array('asc', 'desc')) && in_array($_POST['sortname'], $sort_fields)) {
                $order = $_POST['sortname'] . ' ' . $_POST['sortorder'];
            }
            $order_list = $model_order->getOrderList($order_condition, $_POST['rp'], '*', $order,'',array('order_common'));
            //然后取订单商品佣金
            $order_id_array = array();
            if (is_array($order_list)) {
                foreach ($order_list as $order_info) {
                    $order_id_array[] = $order_info['order_id'];
                }
            }
            $orderGoodsList = $model_order->getOrderGoodsList(array('order_id' => array('in', $order_id_array)));
            $orderGoodsList = array_under_reset($orderGoodsList, 'order_id', 2);
            $data = array();
            $data['now_page'] = $model_order->shownowpage();
            $data['total_num'] = $model_order->gettotalnum();
            foreach ($order_list as $order_info) {
              //  $commis = $this->getOrderCommis($order_info, $orderGoodsList[$order_info['order_id']]);
                $list = array();
                $list['operation'] = "<a target=\"_blank\" class=\"btn green\" href=\"index.php?act=order&op=show_order&order_id={$order_info['order_id']}\"><i class=\"fa fa-list-alt\"></i>查看</a>";
                if (
                        ($bill_info['ob_state'] == BILL_STATE_HANGO && $this->admin_info['gname'] == '汉购网商务') ||
                        ($bill_info['ob_state'] == BILL_STATE_PART_PAY && in_array($order_info['check_result'],array(-1,-2,-3)) && $this->admin_info['gname'] == '汉购网商务') ||
                        ($bill_info['ob_state'] == BILL_STATE_FIRE_PHONIX && $this->admin_info['gname'] == '公司商务')
                    )
                {
                }
                $list['order_sn'] = $order_info['order_sn'];
                $list['order_amount'] = $bill_info['ob_store_manage_type'] == 'platform' ? ncPriceFormat($order_info['order_amount']) : ncPriceFormat($order_info['cost_amount']);
                $list['commis_amount'] = ncPriceFormat($order_info['shequ_return_amount']);
                $list['add_time'] = date('Y-m-d', $order_info['add_time']);
                $list['shequ_bill_time'] = date('Y-m-d', $order_info['shequ_bill_time']);
                $list['push_time'] = $order_info['extend_order_common']['shipping_time']?date("Y-m-d",$order_info['extend_order_common']['shipping_time']):" ";
                $list['buyer_name'] = $order_info['buyer_name'];
                $list['buyer_id'] = $order_info['buyer_id'];
                $list['store_name'] = $order_info['store_name'];
                $list['store_id'] = $order_info['store_id'];
                $data['list'][$order_info['order_id']] = $list;
            }
            exit(Tpl::flexigridXML($data));
        }
    }

    public function reset_sapOp(){
        $type=$_GET['type'];
        $id=$_GET['id'];
        $ob_id=$_GET['ob_id'];
        $order_model=Model('orders');
        $model_refund = Model('refund_return');
        $model_store_cost = Model('store_cost');
        $model_bill = Model('order_bill');
        $ecModel = ecModel();
        try {
            if (C('OLD_STATUS') == true) {
                $ecModel->startTrans();
            }
            switch ($type) {
                case 'order':
                    $order_model->where(array('order_id' => $id))->update(array('purchase_sap' => 0));
                    $name = "ob_sap_order";
                    break;
                case "refund":
                    $model_refund->where(array('refund_id' => $id))->update(array('purchase_sap' => 0));
                    $name = "ob_sap_refund";
                    break;
                case'cost':
                    $model_store_cost->where(array('cost_id' => $id))->update(array('purchase_sap' => 0));
                    $name = "ob_sap_storecost";
                    break;
            }
            $model_bill->where(array('ob_id' => $ob_id))->update(array($name => 0));
            if (C('OLD_STATUS') == true) {
                $ecModel->commit();
            }
        }catch(Exception $e) {
            if (C('OLD_STATUS') == true) {
                $ecModel->rollback();
            }
        }
        showDialog("重置成功");
    }

    public function add_attachmentOp()
    {
        $ob_id = intval($_GET['ob_id']);
        if ($ob_id <= 0) {
            showMessage('参数错误', '', 'html', 'error');
        }
        /** @var billModel $billModel */
        $billModel = Model('shequ_bill');
        if (chksubmit()) {

            if (!empty($_FILES['attachment_file']['name'])) {
                $defaultDir = ATTACH_ADMIN_ATTACHMENT . DS . date('Ym');
                /** @var UploadFile $upload */
                $upload = new UploadFile();
                $upload->set('default_dir', $defaultDir);
                $upload->set('ifremove', false);
                $result = $upload->upfile('attachment_file',false);
                if ($result) {
                    $_POST['attachment_file'] = UPLOAD_SITE_URL . DS . $defaultDir . DS . $upload->file_name;
                } else {
                    showDialog($upload->error);
                }
            } else
                showMessage('附件文件必须上传');
            // 保存数据
            $logArray = array(
                'ob_id' => $ob_id,
                'log_type' => 'attachment',
                'rec_id' => 0,
                'order_id' => 0,
                'order_sn' => 0,
                'log_model' => '',
                'log_attribute' => '',
                'old_value' => '',
                'new_value' => $_POST['attachment_file'],
                'log_msg' => $_POST['description'],
                'log_time' => TIMESTAMP,
                'log_role' => 1,
                'log_user' => $this->admin_info['name'],
            );
            $addRes = $billModel->table('bill_log')->insert($logArray);
            if ($addRes)
                showMessage('操作完成', 'index.php?act=shequ_bill&op=show_bill&query_type=attachment&ob_id=' . $ob_id, 'html', 'succ');
            else
                showMessage('附件添加失败', 'index.php?act=shequ_bill&op=show_bill&query_type=attachment&ob_id=' . $ob_id, 'html', 'error');

        }

        // TODO 上传功能 完善
        Tpl::output('ob_id', $ob_id);
        Tpl::setDirquna('shop');/*网 店 运 维shop wwi.com*/
        Tpl::showpage('shequ_order_bill.upload', 'null_layout');
    }

    public function batch_editOp()
    {
        //exit("<h2>批量修改功能已关闭！</h2>");
        $ob_id = intval($_GET['ob_id']);
        if ($ob_id <= 0) {
            showMessage('参数错误', '', 'html', 'error');
        }
        /** @var billModel $billModel */
        $billModel = Model('shequ_bill');
        $billInfo = $billModel->getOne(array('ob_id' => $ob_id));
        if (chksubmit()) {
            setlocale(LC_ALL, 'zh_CN');
            if (!empty($billInfo)) {
                if (!in_array($billInfo['ob_state'], array(BILL_STATE_HANGO, BILL_STATE_FIRE_PHONIX))) {
                    showMessage('账单已经过确认，或者商家正在修改！');
                }
                $ob_id = $billInfo['ob_id'];
                if ($billInfo['ob_state'] == BILL_STATE_CREATE) {
                    $updateRes = $billModel->editOrderBill(array('ob_state' => BILL_STATE_HANGO), array('ob_id' => $ob_id));
                    if (!$updateRes) {
                        showMessage('修改账单状态失败');
                    }
                }
            }

            if (!empty($_FILES['attachment_file']['name'])) {
                $file = $_FILES['attachment_file'];
                $handle = fopen($file['tmp_name'], "r");
                $csv_string = (file_get_contents($_FILES['attachment_file']['tmp_name']));
                $file_encoding = mb_detect_encoding($csv_string, array("ASCII", "UTF-8", "GB2312", "GBK", "BIG5"));
                $row = 0;
                /** @var orderModel $orderModel */
                $orderModel = Model('order');
                $res = array();$error=false;
                while ($data = fgetcsv($handle, 999999, ',')) {
                    $row++;
                    if ($row < 2) {
                        $res[] = array('订单编号','商品ID','商品行红包','佣金比例','商品成本单价', '红包结果', '佣金比例结果', '成本结果');
                        continue;
                    }
                    $resItem = $data;

                    $order = $orderModel->getOrderInfo(array('order_sn' => trim($data[0])));
                    if ($order['store_id'] != $billInfo['ob_store_id'] || $order['finnshed_time'] < $billInfo['ob_start_date'] || $order['finnshed_time'] > $billInfo['ob_end_date'] || $order['purchase_sap']) {
                        $resItem[] = $resItem[] = $resItem[] = '账单ID错误或修改订单不在当前结算期内';
                        $res[] = $resItem;
                        $error=true;
                        continue;
                    }
                    $orderGoods = $orderModel->getOrderGoodsInfo(array('order_id' => $order['order_id'], 'goods_id' => trim($data[1])));
                    if (empty($orderGoods['goods_id'])) {
                        $resItem[] = $resItem[] = $resItem[] = '请检查订单和商品信息是否匹配';
                        $error=true;
                        $res[] = $resItem;
                        continue;
                    }
                    $item = array(
                        'orderSn' => trim($data[0]),
                        'goodsId' => trim($data[1]),
                        'rptBill' => trim($data[2]),
                        'commisRate' => trim($data[3]),
                        'goodsCost' => trim($data[4]) * $orderGoods['goods_num'],
                    );
                    if ($orderGoods['manage_type'] == 'platform' && $orderGoods['rpt_bill'] != $item['rptBill'] && is_numeric($item['rptBill'])
                        &&$this->admin_info['gname'] == '汉购网商务'&&$billInfo['ob_state'] == BILL_STATE_HANGO) {
                        // 若红包值不同且为数字则修改该记录；
                        try {
                            $this->_modifyRptBill($order, $orderGoods, $billInfo, $item['rptBill']);
                            $rptStatus = '成功';
                        } catch (Exception $e) {
                            $error=true;
                            $rptStatus = $e->getMessage();
                        }
                    } else {
                        $rptStatus = '未修改';
                    }
                    $resItem[] = $rptStatus;

                    if ($orderGoods['manage_type'] == 'platform' &&$orderGoods['commis_rate'] != $item['commisRate'] && is_numeric($item['commisRate'])&&$this->admin_info['gname'] == '公司商务'&&$billInfo['ob_state'] == BILL_STATE_FIRE_PHONIX) {
                        // 仅当佣金比例为数字且未平台商品时修改；
                        try {
                            $this->_modifyCommisRate($order, $orderGoods, $billInfo, $item['commisRate']);
                            $rptStatus = '成功';
                        } catch (Exception $e) {
                            $error=true;
                            $rptStatus = $e->getMessage();
                        }
                    } else {
                        $rptStatus = '未修改';
                    }
                    $resItem[] = $rptStatus;

                    if (!empty($orderGoods) && $orderGoods['manage_type'] != 'platform'&&$orderGoods['goods_cost'] != $item['goodsCost'] && is_numeric($item['goodsCost'])&&$this->admin_info['gname'] == '公司商务'&&$billInfo['ob_state'] == BILL_STATE_FIRE_PHONIX) {
                        // 仅当成本值为数字，且商家类型不为平台，且新旧数值不同则修改该记录；
                        try {
                            $this->_modifyGoodsCost($order, $orderGoods, $billInfo, $item['goodsCost']);
                            $rptStatus = '成功';
                        } catch (Exception $e) {
                            $error=true;
                            $rptStatus = $e->getMessage();
                        }
                    } else {
                        $error=true;
                        $rptStatus = '未修改';
                    }
                    $resItem[] = $rptStatus;
                    $res[]=$resItem;
                }

                if($error){
                    $csv = new Csv();
                    $export_data = $csv->charset($res, CHARSET, 'gbk');
                    $csv->filename = $ob_id . '-bill-batchedit-error';
                    $csv->export($export_data);
                    exit();
                }else{
                    showMessage('全部修改成功');
                }
            } else
                showMessage('附件文件必须上传');
        }
        Tpl::output('ob_id', $ob_id);
        Tpl::setDirquna('shop');
        Tpl::showpage('shequ_order_bill.batch_edit', 'null_layout');
    }

    private function _modifyRptBill($order, $orderGoods, $bill, $value)
    {
        /** @var orderModel $orderModel */
        $orderModel = Model('order');
        $updateArray = array('rpt_bill' => $value);
        $res = array('msg' => 'success', 'data' => $orderGoods);
        $updateRes = $orderModel->table('order_goods')->where(array('rec_id' => $orderGoods['rec_id']))->update($updateArray);
        if (!$updateRes) {
            throw new Exception('数据修改失败');
        }
        // 添加操作日志
        $logArray = array(
            'ob_id' => $bill['ob_id'],
            'rec_id' => $orderGoods['rec_id'],
            'order_id' => $orderGoods['order_id'],
            'order_sn' => $order['order_sn'],
            'log_model' => 'order_goods',
            'log_attribute' => 'rpt_bill',
            'old_value' => $orderGoods['rpt_bill'],
            'new_value' => $value,
            'log_msg' => "将订单【{$order['order_sn']}】的商品【{$orderGoods['goods_name']}】的【红包金额】从【{$orderGoods['rpt_bill']}】调整为【{$value}】",
            'log_time' => TIMESTAMP,
            'log_role' => 1,
            'log_user' => $this->admin_info['name'],
        );
        $addRes = $orderModel->table('bill_log')->insert($logArray);
        if (!$addRes) {
            throw new Exception('日志记录失败');
        }
        $orderModel->execute(
            "UPDATE shopwwi_orders as a INNER JOIN 
(
SELECT SUM(rpt_bill)as rpt_bill, order_id
FROM shopwwi_order_goods
WHERE order_id = {$orderGoods['order_id']}
GROUP BY order_id
) as b
on a.order_id = b.order_id
SET a.rpt_bill = b.rpt_bill
WHERE a.order_id = {$orderGoods['order_id']};");
        return true;
    }
    private function _modifyCommisRate($order, $orderGoods, $bill, $value)
    {
        /** @var orderModel $orderModel */
        $orderModel = Model('order');
        $updateArray = array('commis_rate' => $value);
        $res = array('msg' => 'success', 'data' => $orderGoods);
        if ($orderGoods['manage_type'] == 'platform') {
            $res['commis'] = number_format(($orderGoods['goods_pay_price'] + $orderGoods['rpt_bill']) * $value / 100, 2);
            $updateArray['goods_cost'] = $orderGoods['goods_pay_price'] - $res['commis'] + $orderGoods['rpt_bill'];
            $res['cost'] = $updateArray['goods_cost'];
        } else {
            $res['commis'] = number_format($orderGoods['goods_cost'] * $value / 100, 2);
        }
        $updateRes = $orderModel->table('order_goods')->where(array('rec_id' => $orderGoods['rec_id']))->update($updateArray);
        if (!$updateRes) {
            throw new Exception('数据修改失败');
        }
        // 添加操作日志
        $logArray = array(
            'ob_id' => $bill['ob_id'],
            'rec_id' => $orderGoods['rec_id'],
            'order_id' => $orderGoods['order_id'],
            'order_sn' => $order['order_sn'],
            'log_model' => 'order_goods',
            'log_attribute' => 'commis_rate',
            'old_value' => $orderGoods['commis_rate'],
            'new_value' => $value,
            'log_msg' => "将订单【{$order['order_sn']}】的商品【{$orderGoods['goods_name']}】的【佣金比例】从【{$orderGoods['commis_rate']}】调整为【{$value}】",
            'log_time' => TIMESTAMP,
            'log_role' => 1,
            'log_user' => $this->admin_info['name'],
        );
        $addRes = $orderModel->table('bill_log')->insert($logArray);
        if (!$addRes) {
            throw new Exception('日志记录失败');
        }
        return true;
    }
    private function _modifyGoodsCost($order, $orderGoods, $bill, $value)
    {
        /** @var orderModel $orderModel */
        $orderModel = Model('order');
        $updateArray = array('goods_cost' => $value);
        $res = array('msg' => 'success', 'data' => $orderGoods);
        $updateRes = $orderModel->table('order_goods')->where(array('rec_id' => $orderGoods['rec_id']))->update($updateArray);
        if (!$updateRes) {
            throw new Exception('数据修改失败');
        }
        // 添加操作日志
        $logArray = array(
            'ob_id' => $bill['ob_id'],
            'rec_id' => $orderGoods['rec_id'],
            'order_id' => $orderGoods['order_id'],
            'order_sn' => $order['order_sn'],
            'log_model' => 'order_goods',
            'log_attribute' => 'goods_cost',
            'old_value' => $orderGoods['goods_cost'],
            'new_value' => $value,
            'log_msg' => "将订单【{$order['order_sn']}】的商品【{$orderGoods['goods_name']}】的【成本金额】从【{$orderGoods['goods_cost']}】调整为【{$value}】",
            'log_time' => TIMESTAMP,
            'log_role' => 1,
            'log_user' => $this->admin_info['name'],
        );
        $addRes = $orderModel->table('bill_log')->insert($logArray);
        if (!$addRes) {
            throw new Exception('日志记录失败');
        }
        $orderModel->execute(
            "UPDATE shopwwi_orders as a INNER JOIN 
(
SELECT SUM(goods_cost)as goods_cost, order_id
FROM shopwwi_order_goods
WHERE order_id = {$orderGoods['order_id']}
GROUP BY order_id
) as b
on a.order_id = b.order_id
SET a.goods_cost = b.goods_cost
WHERE a.order_id = {$orderGoods['order_id']};");
        return true;
    }

    public function approve_hangoOp()
    {
        $ob_id = intval($_GET['ob_id']);
        if ($ob_id <= 0) {
            showMessage('参数错误', '', 'html', 'error');
        }
        /** @var shequ_billModel $model_bill */
        $model_bill = Model('shequ_bill');
        $condition = array();
        $condition['ob_id'] = $ob_id;
        $condition['ob_state'] = BILL_STATE_HANGO;

        if ($model_bill->edit($condition,array('ob_state' => BILL_STATE_FIRE_PHONIX))) {
            showDialog('账单进入财务支付环节', 'reload', 'succ');
        } else {
            showDialog(L('nc_common_op_fail'), 'reload', 'error');
            //showMessage('重建失败','','html','error');
        }
    }

    public function reject_comOp()
    {
        $ob_id = intval($_GET['ob_id']);
        if ($ob_id <= 0) {
            showMessage('参数错误', '', 'html', 'error');
        }
        /** @var shequ_billModel $model_bill */
        $model_bill = Model('shequ_bill');
        $condition = array();
        $condition['ob_id'] = $ob_id;
        $condition['ob_state'] = BILL_STATE_FIRE_PHONIX;
        /** @var BillService $bill */
        $bill = Service('Bill');
        $billInfo = $bill->getBillInfo($ob_id);
        if ($bill->calcRealBill($billInfo) && $model_bill->editOrderBill(array('ob_state' => BILL_STATE_HANGO), $condition)) {
            $this->log('重建账单,账单号：' . $ob_id, 1);
            showDialog('打回成功，账单进入汉购网商务审核环节', 'reload', 'succ');
        } else {
            $this->log('重建账单，账单号：' . $ob_id, 0);
            showDialog(L('nc_common_op_fail'), 'reload', 'error');
            //showMessage('重建失败','','html','error');
        }
    }

    public function approve_comOp()
    {
        $ob_id = intval($_GET['ob_id']);
        if ($ob_id <= 0) {
            showMessage('参数错误', '', 'html', 'error');
        }
        /** @var shequ_billModel $model_bill */
        $model_bill = Model('shequ_bill');
        $condition = array();
        $condition['ob_id'] = $ob_id;
        $condition['ob_state'] = BILL_STATE_FIRE_PHONIX;
        /** @var BillService $bill */
        $bill = Service('Bill');
        $billInfo = $bill->getBillInfo($ob_id);
        $lastStoreLog = $model_bill->table('bill_log')->field('log_time')->where(array('ob_id' => $ob_id, 'log_status' => 0, 'log_role' => 0, 'log_type' => 'bill'))->order('log_id desc')->find();
        $lastStoreTime = empty($lastStoreLog) ? 0 : $lastStoreLog['log_time'];
        /**
         *          先行账单                |          后续账单
         *  修改订单 | 修改退款     | 影响订单 | 影响退款
         *      M         |      ---             |         N       |    Y
         *      ---         |       M             |         N       |   N
         *      M          |        M           |           N      |    N
         */
        $logs = $model_bill->table('bill_log')
            ->where(array(
                'ob_id' => $ob_id, 'log_status' => 0,
                'log_role' => 1, 'log_type' => 'data',
                'log_time' => array('gt', $lastStoreTime),
            ))
            ->select();
        if (empty($logs)) {
            /** @var refund_returnModel $refund_returnModel */
            $refund_returnModel = Model('refund_return');
            $refund_list = $refund_returnModel->getRefundReturnList(array(
                'seller_state' => 2,
                'store_id' => $billInfo['ob_store_id'],
                'goods_id' => array('gt', 0),
                'admin_time' => array('between', "{$billInfo['ob_start_date']},{$billInfo['ob_end_date']}")
            ));
            // 基础订单数据被修改
            $logs = $model_bill->table('bill_log')
                ->where(array(
                    'log_status' => 0,
                    'log_role' => 1,
                    'log_type' => 'data',
                    'order_id' => array('in', array_column($refund_list, 'order_id')),
                    'log_time' => array('gt', $lastStoreTime),
                ))
                ->select();
        }
        $update = array('ob_state' => empty($logs) ? BILL_STATE_CEO : BILL_STATE_CREATE);
        if ($bill->calcRealBill($billInfo) && $model_bill->editOrderBill($update, $condition)) {
            $this->log('重建账单,账单号：' . $ob_id, 1);
            empty($logs) ?
                showDialog('重建成功，账单进入总经理审核环节', 'reload', 'succ') :
                showDialog('重建成功，由于调整了账单数据，现在发送商家确认', 'reload', 'succ');
        } else {
            $this->log('重建账单，账单号：' . $ob_id, 0);
            showDialog(L('nc_common_op_fail'), 'reload', 'error');
            //showMessage('重建失败','','html','error');
        }
    }

    public function approve_payOp()
    {
        $ob_id = intval($_GET['ob_id']);
        if ($ob_id <= 0) {
            showMessage('参数错误', '', 'html', 'error');
        }
        /** @var shequ_billModel $model_bill */
        $model_bill = Model('shequ_bill');
        $condition = array();
        $condition['ob_id'] = $ob_id;
        $condition['ob_state'] = BILL_STATE_CEO;
        /** @var BillService $bill */
        $bill = Service('Bill');
        $billInfo = $bill->getBillInfo($ob_id);
        if ($model_bill->editOrderBill(array('ob_state' => BILL_STATE_SYSTEM_CHECK), $condition)) {
            // 修改记录状态为0->1；
            $model_bill->table('bill_log')
                ->where(array('ob_id' => $ob_id, 'log_status' => 0))
                ->update(array('log_status' => 1));
            showDialog('审核完成，账单进入财务审核环节', 'reload', 'succ');
        } else {
            showDialog(L('nc_common_op_fail'), 'reload', 'error');
            //showMessage('重建失败','','html','error');
        }
    }

    public function rebuild_billOp()
    {
        $ob_id = intval($_GET['ob_id']);
        if ($ob_id <= 0) {
            showMessage('参数错误', '', 'html', 'error');
        }
        /** @var shequ_billModel $model_bill */
        $model_bill = Model('shequ_bill');
        $condition = array();
        $condition['ob_id'] = $ob_id;
        /** @var BillService $bill */
        $bill = Service('Bill');
        $billInfo = $bill->getBillInfo($ob_id);

        $lastStoreLog = $model_bill->table('bill_log')->field('log_time')->where(array('ob_id' => $ob_id, 'log_status' => 0, 'log_role' => 0, 'log_type' => 'bill'))->order('log_id desc')->find();
        $lastStoreTime = empty($lastStoreLog) ? 0 : $lastStoreLog['log_time'];
        /**
         *          先行账单                |          后续账单
         *  修改订单 | 修改退款     | 影响订单 | 影响退款
         *      M         |      ---             |         N       |    Y
         *      ---         |       M             |         N       |   N
         *      M          |        M           |           N      |    N
         */
        $logs = $model_bill->table('bill_log')
            ->where(array(
                'ob_id' => $ob_id, 'log_status' => 0,
                'log_role' => 1, 'log_type' => 'data',
                'log_time' => array('gt', $lastStoreTime),
            ))
            ->select();
        if (empty($logs)) {
            /** @var refund_returnModel $refund_returnModel */
            $refund_returnModel = Model('refund_return');
            $refund_list = $refund_returnModel->getRefundReturnList(array(
                'seller_state' => 2,
                'store_id' => $billInfo['ob_store_id'],
                'goods_id' => array('gt', 0),
                'admin_time' => array('between', "{$billInfo['ob_start_date']},{$billInfo['ob_end_date']}")
            ));
            // 基础订单数据被修改
            $logs = $model_bill->table('bill_log')
                ->where(array(
                    'log_status' => 0,
                    'log_role' => 1,
                    'log_type' => 'data',
                    'order_id' => array('in', array_column($refund_list, 'order_id')),
                    'log_time' => array('gt', $lastStoreTime),
                ))
                ->select();
        }
        $update = array('ob_state' => BILL_STATE_CREATE);
        if ($bill->calcRealBill($billInfo)) {
            $this->log('重建账单,账单号：' . $ob_id, 1);
            if($logs&&$model_bill->editOrderBill($update, $condition)){
                showDialog('重建成功，由于调整了账单数据，现在发送商家确认', 'reload', 'succ');
            }else{
                showDialog('重建成功!', 'reload', 'succ');
            }
            //showMessage('重建成功，账单进入商家确认环节');
        } else {
            $this->log('重建账单，账单号：' . $ob_id, 0);
            showDialog(L('nc_common_op_fail'), 'reload', 'error');
            //showMessage('重建失败','','html','error');
        }
    }

    public function bill_checkOp()
    {
        $ob_id = intval($_GET['ob_id']);
        if ($ob_id <= 0) {
            showMessage('参数错误', '', 'html', 'error');
        }
        $model_bill = Model('shequ_bill');
        $condition = array();
        $condition['ob_id'] = $ob_id;
        $condition['ob_state'] = BILL_STATE_STORE_COFIRM;
        $update = $model_bill->editOrderBill(array('ob_state' => BILL_STATE_SYSTEM_CHECK), $condition);
        if ($update) {
            $this->log('审核账单,账单号：' . $ob_id, 1);
            showMessage('审核成功，账单进入付款环节');
        } else {
            $this->log('审核账单，账单号：' . $ob_id, 0);
            showMessage('审核失败', '', 'html', 'error');
        }
    }

    //部分付款
    public function bill_part_payOp()
    {
        $ob_id = intval($_GET['ob_id']);
        if ($ob_id <= 0) {
            showMessage('参数错误', '', 'html', 'error');
        }
        $model_bill = Model('shequ_bill');
        $condition = array();
        $condition['ob_id'] = $ob_id;
//        $condition['ob_state'] = BILL_STATE_SYSTEM_CHECK;
        $condition['ob_state'] = array('in','3,5');
        $bill_info = $model_bill->getOne($condition);
        if (!$bill_info) {
            showMessage('参数错误', '', 'html', 'error');
        }

        if (chksubmit()) {
            if (!preg_match('/^20\d{2}-\d{2}-\d{2}$/', $_POST['pay_date'])) {
                showMessage('参数错误', '', 'html', 'error');
            }
            $input = array();
            $input['obl_pay_content'] = $_POST['pay_content'];
            $input['obl_pay_date'] = strtotime($_POST['pay_date']);
            $input['obl_ob_id'] = $ob_id;
            $model_bill_log = Model('order_bill_log');
            $update = $model_bill_log->insert($input);

            $input = array();
            $input['ob_pay_content'] = $_POST['pay_content'];
            $input['ob_pay_date'] = strtotime($_POST['pay_date']);
            $input['ob_state'] = BILL_STATE_PART_PAY;
            $update = $model_bill->editOrderBill($input, $condition);

            if ($update) {
                $model_store_cost = Model('store_cost');
                $cost_condition = array();
                $cost_condition['cost_store_id'] = $bill_info['ob_store_id'];
                $cost_condition['cost_state'] = 0;
                $cost_condition['cost_time'] = array('between', "{$bill_info['ob_start_date']},{$bill_info['ob_end_date']}");
                $model_store_cost->editStoreCost(array('cost_state' => 1), $cost_condition);

                // 发送店铺消息
                $param = array();
                $param['code'] = 'store_bill_gathering';
                $param['store_id'] = $bill_info['ob_store_id'];
                $param['param'] = array(
                    'bill_no' => $bill_info['ob_id']
                );
                QueueClient::push('sendStoreMsg', $param);

                $this->log('账单部分付款,账单号：' . $ob_id, 1);
                showMessage('保存成功', 'index.php?act=shequ_bill');
            } else {
                $this->log('账单部分付款,账单号：' . $ob_id, 1);
                showMessage('保存失败', '', 'html', 'error');
            }
        } else {
            //网 店 运 维shop wwi.com
            Tpl::setDirquna('shop');
            Tpl::showpage('shequ_bill.part_pay');
        }
    }

    public function generate_pay(){
        echo 'generate_pay';
    }
    /**
     * 账单付款
     *
     */
    public function bill_payOp()
    {
        $ob_id = intval($_GET['ob_id']);
        if ($ob_id <= 0) {
            showMessage('参数错误', '', 'html', 'error');
        }
        $model_bill = Model('shequ_bill');
        $condition = array();
        $condition['ob_id'] = $ob_id;
        $condition['ob_state'] = BILL_STATE_SYSTEM_CHECK;
        $bill_info = $model_bill->getOne($condition);
        if (!$bill_info) {
            showMessage('参数错误', '', 'html', 'error');
        }
        if (chksubmit()) {
            if (!preg_match('/^20\d{2}-\d{2}-\d{2}$/', $_POST['pay_date'])) {
                showMessage('参数错误', '', 'html', 'error');
            }
            $input = array();
            $input['ob_pay_content'] = $_POST['pay_content'];
            $input['ob_pay_date'] = strtotime($_POST['pay_date']);
            $input['ob_state'] = BILL_STATE_SUCCESS;
            $update = $model_bill->editOrderBill($input, $condition);
            if ($update) {
                $model_store_cost = Model('store_cost');
                $cost_condition = array();
                $cost_condition['cost_store_id'] = $bill_info['ob_store_id'];
                $cost_condition['cost_state'] = 0;
                $cost_condition['cost_time'] = array('between', "{$bill_info['ob_start_date']},{$bill_info['ob_end_date']}");
                $model_store_cost->editStoreCost(array('cost_state' => 1), $cost_condition);

                // 发送店铺消息
                $param = array();
                $param['code'] = 'store_bill_gathering';
                $param['store_id'] = $bill_info['ob_store_id'];
                $param['param'] = array(
                    'bill_no' => $bill_info['ob_id']
                );
                QueueClient::push('sendStoreMsg', $param);

                $this->log('账单付款,账单号：' . $ob_id, 1);
                showMessage('保存成功', 'index.php?act=shequ_bill');
            } else {
                $this->log('账单付款,账单号：' . $ob_id, 1);
                showMessage('保存失败', '', 'html', 'error');
            }
        } else {
            //网 店 运 维shop wwi.com
            Tpl::setDirquna('shop');
            Tpl::showpage('shequ_bill.pay');
        }
    }

    /**
     * 打印结算单
     *
     */
    public function bill_printOp()
    {
        $ob_id = intval($_GET['ob_id']);
        if ($ob_id <= 0) {
            showMessage('参数错误', '', 'html', 'error');
        }
        $model_bill = Model('shequ_bill');
        $condition = array();
        $condition['ob_id'] = $ob_id;
        $condition['ob_state'] = BILL_STATE_SUCCESS;
        $bill_info = $model_bill->getOne($condition);
        if (!$bill_info) {
            showMessage('参数错误', '', 'html', 'error');
        }

        Tpl::output('bill_info', $bill_info);
        //网 店 运 维shop wwi.com
        Tpl::setDirquna('shop');
        Tpl::showpage('shequ_bill.print', 'null_layout');
    }


    /**
     * 导出团长结算单信息
     *
     */
    public function export_billOp()
    {
        /** @var shequ_billModel $model_bill */
        $model_bill = Model('shequ_bill');
        $condition = array();
        error_reporting(E_ALL);
        set_time_limit(1800);
        ini_set('memory_limit','4G');
        if (preg_match('/^[\d,]+$/', $_GET['ob_id'])) {
            $_GET['ob_id'] = explode(',', trim($_GET['ob_id'], ','));
            $condition['ob_id'] = array('in', $_GET['ob_id']);
        }
        list($condition, $order) = $this->_get_bill_condition($condition);
        if (!is_numeric($_GET['curpage'])) {
            $count = $model_bill->getShequBillCount($condition);
            $array = array();
            if ($count > self::EXPORT_SIZE) {
                //显示下载链接
                $page = ceil($count / self::EXPORT_SIZE);
                for ($i = 1; $i <= $page; $i++) {
                    $limit1 = ($i - 1) * self::EXPORT_SIZE + 1;
                    $limit2 = $i * self::EXPORT_SIZE > $count ? $count : $i * self::EXPORT_SIZE;
                    $array[$i] = $limit1 . ' ~ ' . $limit2;
                }
                Tpl::output('list', $array);
                Tpl::output('murl', 'javascript:history.back(-1)');
                Tpl::setDirquna('shop');
                Tpl::showpage('export.excel');
                exit();
            }
            $limit = false;
        } else {
            //下载
            $limit1 = ($_GET['curpage'] - 1) * self::EXPORT_SIZE;
            $limit2 = self::EXPORT_SIZE;
            $limit = "{$limit1},{$limit2}";
        }
        $data = $model_bill->getList($condition,"","ob_id desc","*",$limit);
        $export_data = array();
        $export_data[0] = array('账单编号','开始日期','结束日期','出账日期','团长名称','收取佣金','退还佣金','本期应结','账单状态','备注');
       $ob_shequ_commis_total = 0;
       $ob_shequ_commis_refund = 0;
       $ob_shequ_commis_result = 0;
       foreach($data as $k=>$v){
           $export_data[$k+1][] = $v['ob_id'];
           $export_data[$k + 1][] = date('Y-m-d', $v['ob_start_date']);
           $export_data[$k + 1][] = date('Y-m-d', $v['ob_end_date']);
           $export_data[$k+1][] = date('Y-m-d',$v['ob_create_date']);
           $export_data[$k+1][] = $v['ob_store_name'];
           $ob_shequ_commis_total+=$export_data[$k+1][] = $v['ob_commis_totals'];
           $ob_shequ_commis_refund+=$export_data[$k+1][] = $v['ob_commis_return_totals'];
           $ob_shequ_commis_result+=$export_data[$k+1][] = $v['ob_result_totals'];
           $export_data[$k+1][] = shequBillState($v['ob_state']);
           $export_data[$k+1][] = $v['ob_remark'];
       }
        $count = count($export_data);
        $export_data[$count][] = '';
        $export_data[$count][] = '';
        $export_data[$count][] = '';
        $export_data[$count][] = '';
        $export_data[$count][] = '合计';
        $export_data[$count][] = $ob_shequ_commis_total;
        $export_data[$count][] = $ob_shequ_commis_refund;
        $export_data[$count][] = $ob_shequ_commis_result;
        $csv = new Csv();
        $export_data = $csv->charset($export_data, CHARSET, 'gbk');
        $csv->filename = '社区团长结算单'.date("Y-m-d",TIMESTAMP);
        $csv->export($export_data);
    }

    /**
     * 导出结算订单明细CSV
     *
     */
    public function export_orderOp()
    {
        $ob_id = intval($_GET['ob_id']);
        if ($ob_id <= 0) {
            exit();
        }
        error_reporting(E_ALL);
        set_time_limit(1800);
       // ini_set('memory_limit','4G');
        $model_bill = Model('shequ_bill');
        $bill_info = $model_bill->getOne(array('ob_id' => $ob_id));
        if (!$bill_info) {
            exit();
        }
        /** @var orderModel $model_order */
        $model_order = Model('order');
        $condition = array();
        $condition['order_state'] = ORDER_STATE_SUCCESS;
        $condition['shequ_tz_id'] = $bill_info['ob_store_id'];
        $if_start_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/', $_GET['query_start_date']);
        $if_end_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/', $_GET['query_end_date']);
        $start_unixtime = $if_start_date ? strtotime($_GET['query_start_date']) : null;
        $end_unixtime = $if_end_date ? strtotime($_GET['query_end_date']) : null;
        $end_unixtime = $if_end_date ? $end_unixtime + 86400 - 1 : null;
        if ($if_start_date || $if_end_date) {
            $condition['finnshed_time'] = array('between', "{$start_unixtime},{$end_unixtime}");
        } else {
            $condition['finnshed_time'] = array('between', "{$bill_info['ob_start_date']},{$bill_info['ob_end_date']}");
        }
        if (preg_match('/^[\d,]+$/', $_GET['order_id'])) {
            $_GET['order_id'] = explode(',', trim($_GET['order_id'], ','));
            $condition['order_id'] = array('in', $_GET['order_id']);
        }

        if($_GET['search_type'] != ''){
            if($_GET['search_type'] == '已结算'){
                $post_query = array('in','1,2,3');
            } else if($_GET['search_type'] == '异常'){
                $post_query = array('in','-1,-2,-3');
            } else if($_GET['search_type'] == '第1次结算'){
                $post_query = array('in','-1,1');
            } else if($_GET['search_type'] == '第2次结算'){
                $post_query = array('in','-2,2');
            } else if($_GET['search_type'] == '第3次结算'){
                $post_query = array('in','-3,3');
            } else if($_GET['search_type'] == '第1次结算正常'){
                $post_query = 1;
            } else if($_GET['search_type'] == '第2次结算正常'){
                $post_query = 2;
            } else if($_GET['search_type'] == '第3次结算正常'){
                $post_query = 3;
            } else if($_GET['search_type'] == '第1次结算异常'){
                $post_query = -1;
            } else if($_GET['search_type'] == '第2次结算异常'){
                $post_query = -2;
            } else if($_GET['search_type'] == '第3次结算异常'){
                $post_query = -3;
            } else if($_GET['search_type'] == '未对账'){
                $post_query = 0;
            }
            $condition['check_result'] = $post_query;
        }

        if ($_REQUEST['query'] != '' && in_array($_REQUEST['qtype'], array('order_sn', 'buyer_name'))) {
            $condition[$_REQUEST['qtype']] = array('like', "%{$_REQUEST['query']}%");
        }
        if ($_GET['order_sn'] != '') {
            $condition['order_sn'] = array('like', "%{$_GET['order_sn']}%");
        }
        if ($_GET['buyer_name'] != '') {
            if ($_GET['jq_query']) {
                $condition['buyer_name'] = $_GET['buyer_name'];
            } else {
                $condition['buyer_name'] = array('like', "%{$_GET['buyer_name']}%");
            }
        }
        if($_GET['query_type']=='refund'){
            $condition['refund_amount'] = array('gt',0);
        }
        $sort_fields = array('order_amount', 'shipping_fee', 'commis_amount', 'add_time', 'finnshed_time', 'buyer_id', 'store_id');
        if (in_array($_POST['sortorder'], array('asc', 'desc')) && in_array($_POST['sortname'], $sort_fields)) {
            $order = $_POST['sortname'] . ' ' . $_POST['sortorder'];
        }
        $export_size = 10000;
        if (!is_numeric($_GET['curpage'])) {
            $count = $model_order->getOrderCount($condition);
            $array = array();
            if ($count > $export_size) {
                //显示下载链接
                $page = ceil($count / $export_size);
                for ($i = 1; $i <= $page; $i++) {
                    $limit1 = ($i - 1) * $export_size + 1;
                    $limit2 = $i * $export_size > $count ? $count : $i * $export_size;
                    $array[$i] = $limit1 . ' ~ ' . $limit2;
                }
                Tpl::output('list', $array);
                Tpl::output('murl', 'index.php?act=shequ_bill&op=show_bill&ob_id=' . $ob_id);
                Tpl::setDirquna('shop');
                Tpl::showpage('export.excel');
                exit();
            }
            $limit = false;
        } else {
            //下载
            $limit1 = ($_GET['curpage'] - 1) * $export_size;
            $limit2 = $export_size;
            $limit = "{$limit1},{$limit2}";
        }
        $data = $model_order->getOrderList($condition, '', '*', 'order_id desc', $limit, array('order_common','order_goods'));
        $fields = 'sum(order_amount) as order_amount,sum(cost_amount) as cost_amount,sum(rpt_bill) as rpt_bill,sum(shipping_fee) as shipping_amount,min(store_name) as store_name';
        $order_info_all =  $model_order->getOrderInfo($condition,array(),$fields);
        //订单商品表查询条件
        $order_id_array = array();
        if (is_array($data)) {
            foreach ($data as $order_info) {
                $order_id_array[] = $order_info['order_id'];
            }
        }
        $order_goods_condition = array();
        $order_goods_condition['order_id'] = array('in', $order_id_array);

        $export_data = array();
        $export_data[0] = array('订单编号','订单金额','佣金','退款金额','下单日期','出账日期','发货日期','买家','商家','商品ID','商品','单价','数量','实际支付');
//        $export_data[0] = array('订单编号', '分销订单号', '订单金额','订单成本','是否结算', '运费', '佣金',
//            '下单日期', '成交日期','发货日期', '商家', '商家编号', '买家', '买家编号', '订单红包','物流单号',
//            '商品ID', '商品', '单价', '数量', '实际支付', '佣金比例', '商品行佣金', '商品行成本', '商品行红包', '商品行成本单价','进项税','销项税',
//        );
        $order_totals = 0;
        $order_shequ_commis_total = 0;
        $k=0;
//
//        $order_totals = 0;
//        $order_cost_totals = 0;
//        $shipping_totals = 0;
//        $commis_totals = 0;
//        $k = 0;
//

//        $check_result_array = array(
//            '1' => '第一次结算',
//            '2' => '第二次结算',
//            '3' => '第三次结算',
//            '-1' => '第一次异常',
//            '-2' => '第二次异常',
//            '-3' => '第三次异常',
//            '0' => '未对账'
//        );

        foreach ($data as $v) {
            //该订单算佣金
            /*$field = $bill_info['ob_ver']==1
                    ?'SUM(ROUND((goods_pay_price+rpt_bill)*commis_rate/100,2)) as commis_amount,order_id'
                    :'SUM(ROUND(goods_pay_price*commis_rate/100,2)) as commis_amount,order_id';
            //'SUM(ROUND((goods_pay_price+rpt_bill)*commis_rate/100,2)) as commis_amount,order_id';
            $commis_list = $model_order->getOrderGoodsList($order_goods_condition,$field,null,null,'','order_id','order_id');*/

            //$commis = $this->getOrderCommis($v, $v['extend_order_goods']);

            $export_data[$k + 1][] = $v['order_sn'] . "\t";
          //  $export_data[$k + 1][] = $v['fx_order_id'] . "\t";
            $order_totals += $export_data[$k + 1][] = $v['order_amount'];
        //     $order_cost_totals += $export_data[$k + 1][] = floatval($v['cost_amount'])
        //   $export_data[$k + 1][] = $check_result_array[$v['check_result']];
       //     $shipping_totals += $export_data[$k + 1][] = $v['shipping_fee'];
      //      $commis_totals += $export_data[$k + 1][] = $commis;
            $order_shequ_commis_total += $export_data[$k+1][] = $v['shequ_return_amount'];
            $export_data[$k + 1][] =  $v['refund_amount'];
            $export_data[$k + 1][] = date('Y-m-d', $v['add_time']);
            $export_data[$k + 1][] = date('Y-m-d', $v['shequ_bill_time']);
            $export_data[$k + 1][] = $v['extend_order_common']['shipping_time']?date('Y-m-d', $v['extend_order_common']['shipping_time']):" ";
            $export_data[$k + 1][] = htmlspecialchars($v['buyer_name']) . "\t";
            $export_data[$k+1][] = $v['store_name'];
        //    $export_data[$k + 1][] = $v['buyer_id'];
       //     $export_data[$k + 1][] = $v['rpt_bill'];
      //      $export_data[$k + 1][] = $v['shipping_code'] . "\t";
        //    $item_shipping_fee = $v['shipping_fee'] / count($v['extend_order_goods']);
            $goodsCount = 0;
            if (is_array($v['extend_order_goods'])) {
                foreach ($v['extend_order_goods'] as $v1) {
                    if ($goodsCount > 0) {
                        $export_data[$k + 1][] = $export_data[$k + 1][] = $export_data[$k + 1][] = $export_data[$k + 1][] = $export_data[$k + 1][] =
                        $export_data[$k + 1][] = $export_data[$k + 1][] = $export_data[$k + 1][] = $export_data[$k + 1][] = '';
                    };
                    $export_data[$k + 1][] = $v1['goods_id'];
                    $export_data[$k + 1][] = $v1['goods_name'];
                    $export_data[$k + 1][] = $v1['goods_price'];
                    $export_data[$k + 1][] = $v1['goods_num'];
                    $export_data[$k + 1][] = ncPriceFormat($v1['goods_pay_price']);


                    /** @var BillService $billService */
                   // $billService = Service('Bill');
//                    $ver1 = $billService->getCommVer1Time();
               //     $ver1 = strtotime('2016-10-31 14:10');
//                    if ($v['manage_type'] == 'platform') {
//                        $export_data[$k + 1][] = $v1['commis_rate'];
//                        $comprice = $export_data[$k + 1][] = $v['finnshed_time'] > $ver1 ?
//                            ncPriceFormat(($v1['goods_pay_price'] + $v1['rpt_bill']) * $v1['commis_rate'] / 100) :
//                            ncPriceFormat($v1['goods_pay_price'] * $v1['commis_rate'] / 100);
//                        $export_data[$k + 1][] = ncPriceFormat($v1['goods_pay_price'] + $item_shipping_fee - $comprice + $v1['rpt_bill']);
//                    } else {
//                        $export_data[$k + 1][] = 0;
//                        $export_data[$k + 1][] = 0;
//                        $export_data[$k + 1][] = ncPriceFormat($v1['goods_cost']);
//                    }
  //                  $export_data[$k + 1][] = ncPriceFormat($v1['rpt_bill']);
   //                 $export_data[$k + 1][] = ncPriceFormat($v1['goods_cost'] / $v1['goods_num'], 4);
                    //$v1['goods_cost']/$;
//                    $export_data[$k + 1][]=$v1['tax_input'];
//                    $export_data[$k + 1][]=$v1['tax_output'];
                    //$goods_string .= $v['goods_name'].'|单价:'.$v['goods_price'].'|数量:'.$v['goods_num'].'|实际支付:'.$v['goods_pay_price'].'|佣金比例:'.$v['commis_rate'].'%';
                    $k++;
                    $goodsCount++;
                }
            } else {
                $k++;
            }
        }
        $count = count($export_data);
        $export_data[$count][] = '合计';
//        $export_data[$count][] = "\t";
        $export_data[$count][] = $order_totals;
        $export_data[$count][] = $order_shequ_commis_total;
//        $export_data[$count][] = '';
//        $export_data[$count][] = $shipping_totals;
//        $export_data[$count][] = $commis_totals;
//        $export_data[$count][] = '所有订单总成本:'.floatval($order_info_all['cost_amount']);
        $csv = new Csv();
        $export_data = $csv->charset($export_data, CHARSET, 'gbk');
        $csv->filename = "社区团长:".$bill_info['ob_store_name']. '-bill';
        $csv->export($export_data);
    }


    public function export_profitOp()
    {
        $ob_id = intval($_GET['ob_id']);
        if ($ob_id <= 0) {
            exit();
        }
        error_reporting(E_ALL);
        set_time_limit(1800);
        ini_set('memory_limit','4G');
        /** @var shequ_billModel $model_bill */
        $model_bill = Model('shequ_bill');
        $bill_info = $model_bill->getOne(array('ob_id' => $ob_id));
        if (!$bill_info) {
            exit();
        }
        /** @var goodsModel $goodsModel */
        $goodsModel = Model('goods');
        /** @var workflowModel $workflowModel */
        $workflowModel = Model('workflow');
        /** @var orderModel $model_order */
        $model_order = Model('order');
        $condition = array();
        $condition['order_state'] = ORDER_STATE_SUCCESS;
        $condition['shequ_tz_id'] = $bill_info['ob_store_id'];
        $condition['finnshed_time'] = array('between', "{$bill_info['ob_start_date']},{$bill_info['ob_end_date']}");
        $exportSize = 5000;
        if (!is_numeric($_GET['curpage'])) {
            $count = $model_order->getOrderCount($condition);
            $array = array();
            if ($count > $exportSize) {
                //显示下载链接
                $page = ceil($count / $exportSize);
                for ($i = 1; $i <= $page; $i++) {
                    $limit1 = ($i - 1) * $exportSize + 1;
                    $limit2 = $i * $exportSize > $count ? $count : $i * $exportSize;
                    $array[$i] = $limit1 . ' ~ ' . $limit2;
                }
                Tpl::output('list', $array);
                Tpl::output('murl', 'index.php?act=shequ_bill&op=show_bill&ob_id=' . $ob_id);
                Tpl::setDirquna('shop');
                Tpl::showpage('export.excel');
                exit();
            }
            $limit = false;
        } else {
            //下载
            $limit1 = ($_GET['curpage'] - 1) * $exportSize;
            $limit2 = $exportSize;
            $limit = "{$limit1},{$limit2}";
        }
        $sort_fields = array('order_amount', 'shipping_fee', 'commis_amount', 'add_time', 'finnshed_time', 'buyer_id', 'store_id');

        $export_data = array();
        $export_data[0] = array('订单编号', '分销订单号', '订单金额', '运费', '佣金',
            '下单日期', '成交日期', '商家', '商家编号', '买家', '买家编号', '订单红包',
            '商品ID', '商品', '单价', '数量', '实际支付', '佣金比例', '商品行佣金', '商品行成本', '商品行红包', '毛利', '毛利率', '相关凭证',
        );

        $order_totals = 0;
        $shipping_totals = 0;
        $commis_totals = 0;
        $k = 0;
        $cacheCount = 0;
        $signArray = array();
        $data = $model_order->getOrderList($condition, '', '*', 'order_id desc', $limit, array('order_goods'));
        //订单商品表查询条件
        $order_id_array = array();
        if (is_array($data)) {
            foreach ($data as $order_info) {
                $order_id_array[] = $order_info['order_id'];
            }
        }
        $order_goods_condition = array();
        $order_goods_condition['order_id'] = array('in', $order_id_array);
        foreach ($data as $v) {
            $row = array();
            $commis = $this->getOrderCommis($v, $v['extend_order_goods']);
            $row[] = $v['order_sn'] . "\t";
            $row[] = $v['fx_order_id'] . "\t";
            $order_totals += $row[] = $v['order_amount'];
            $shipping_totals += $row[] = $v['shipping_fee'];
            $commis_totals += $row[] = $commis;
            $row[] = date('Y-m-d', $v['add_time']);
            $row[] = date('Y-m-d', $v['finnshed_time']);
            $row[] = $v['store_name'];
            $row[] = $v['store_id'];
            $row[] = htmlspecialchars($v['buyer_name']) . "\t";
            $row[] = $v['buyer_id'];
            $row[] = $v['rpt_bill'];
            $item_shipping_fee = $v['shipping_fee'] / count($v['extend_order_goods']);
            //$goods_string = '';
            $goodsCount = 0;
            if (is_array($v['extend_order_goods'])) {
                foreach ($v['extend_order_goods'] as $v1) {
                    // 判断是否有低毛利，若有，则设置状态
                    if ($v['manage_type'] == 'platform') {
                        $comprice = ncPriceFormat(($v1['goods_pay_price'] + $v1['rpt_bill']) * $v1['commis_rate'] / 100);
                        $goodsCost = ncPriceFormat($v1['goods_pay_price'] + $item_shipping_fee - $comprice + $v1['rpt_bill']);
                    } else {
                        $comprice = 0;
                        $goodsCost = ncPriceFormat($v1['goods_cost']);
                    }
                    $profit = ($v1['goods_pay_price'] - $goodsCost);
                    $profitRate = $profit * 100 / $goodsCost;
                    if ($profitRate > 5) {
                        continue;
                    }

                    if ($goodsCount > 0) {
                        $row = array();
                        $row[] = $row[] = $row[] = $row[] =
                        $row[] = $row[] = $row[] = $row[] =
                        $row[] = $row[] = $row[] = $row[] = '';
                    };
                    $row[] = $v1['goods_id'];
                    $row[] = $v1['goods_name'];
                    $row[] = $v1['goods_price'];
                    $row[] = $v1['goods_num'];
                    $row[] = ncPriceFormat($v1['goods_pay_price']);
                    $row[] = $v1['commis_rate'];
                    $row[] = $comprice;
                    $row[] = $goodsCost;
                    $row[] = ncPriceFormat($v1['rpt_bill']);
                    $row[] = $profit;
                    $row[] = ncPriceFormat($profitRate) . '%';
                    // 针对低毛利查询凭证
                    $attachmentArray = array();
                    // 商品相关凭证
                    if (!isset($signArray['goods' . $v1['goods_id']])) {
                        $goodsInfo = $goodsModel->getGoodsInfo(array('goods_id' => $v1['goods_id']));
                        /***  SKU相关凭证 */
                        $workflowList = $workflowModel->getWorkflowList(array('model' => 'goods', 'model_id' => $v1['goods_id'], 'status' => 1));
                        $logList = $workflowModel->table('workflow_log')->where(array('workflow_id' => array('in', array_column($workflowList, 'id'))))->select();
                        $attachmentGoods = array();
                        foreach ($logList as $log) {
                            $attachment = json_decode($log['attachment'], true);
                            if (empty($attachment)) continue;
                            foreach ($attachment as $item) {
                                if (!empty($item)) $attachmentGoods[] = 'http://www.hangowa.com' . $item;
                            }
                        }
                        /** 商品相关证件 */
                        $workflowList = $workflowModel->getWorkflowList(array('model' => 'goods_common', 'model_id' => $goodsInfo['goods_commonid'], 'status' => 1));
                        $logList = $workflowModel->table('workflow_log')->where(array('workflow_id' => array('in', array_column($workflowList, 'id'))))->select();
                        foreach ($logList as $log) {
                            $attachment = json_decode($log['attachment'], true);
                            if (empty($attachment)) continue;
                            foreach ($attachment as $item) {
                                if (!empty($item)) $attachmentGoods[] = 'http://www.hangowa.com' . $item;
                            }
                        }
                        $signArray['goods' . $v1['goods_id']] = implode(' ; ', $attachmentGoods);
                    }
                    if (!empty($signArray['goods' . $v1['goods_id']])) $attachmentArray[] = $signArray['goods' . $v1['goods_id']];

                    // 订单相关凭证order_goods
                    if (!isset($signArray['rec' . $v1['rec_id']])) {
                        $workflowList = $workflowModel->getWorkflowList(array('model' => 'order_goods', 'model_id' => $v1['rec_id'], 'status' => 1));
                        $logList = $workflowModel->table('workflow_log')->where(array('workflow_id' => array('in', array_column($workflowList, 'id'))))->select();
                        $attachmentRec = array();
                        foreach ($logList as $log) {
                            $attachment = json_decode($log['attachment'], true);
                            if (empty($attachment)) continue;
                            foreach ($attachment as $item) {
                                if (!empty($item)) $attachmentRec[] = 'http://www.hangowa.com' . $item;
                            }
                        }
                        $signArray['rec' . $v1['rec_id']] = implode(' ; ', $attachmentRec);
                    }
                    if (!empty($signArray['rec' . $v1['rec_id']])) $attachmentArray[] = $signArray['rec' . $v1['rec_id']];

                    $row[] = implode(' ; ', $attachmentArray);
                    $export_data[$k + 1] = $row;
                    $k++;
                    $goodsCount++;
                }
            } else {
                $k++;
            }
        }
        $csv = new Csv();
        $export_data = $csv->charset($export_data, CHARSET, 'gbk');
        $csv->filename = $ob_id . '-profit-page-'.(isset($_GET['curpage'])?$_GET['curpage']:1);
        $csv->export($export_data);
    }

    /**
     * 导出未退定金的预定订单明细CSV
     *
     */
    public function export_bookOp()
    {
        $ob_id = intval($_GET['ob_id']);
        if ($ob_id <= 0) {
            exit();
        }
        $model_bill = Model('shequ_bill');
        $bill_info = $model_bill->getOne(array('ob_id' => $ob_id));
        if (!$bill_info) {
            exit();
        }

        $condition = array();
        //被取消的预定订单列表
        $model_order = Model('order');
        if ($_POST['query'] != '' && in_array($_POST['qtype'], array('order_sn'))) {
            $order_info = $model_order->getOrderInfo(array('order_sn' => $_POST['query']));
            if ($order_info) {
                $condition['book_order_id'] = $order_info['order_id'];
            } else {
                $condition['book_order_id'] = 0;
            }
        }
        if (preg_match('/^[\d,]+$/', $_GET['order_id'])) {
            $_GET['order_id'] = explode(',', trim($_GET['order_id'], ','));
            $condition['book_order_id'] = array('in', $_GET['order_id']);
        }
        $model_order_book = Model('order_book');

        $condition['book_store_id'] = $bill_info['ob_store_id'];
        $condition['book_cancel_time'] = array('between', "{$bill_info['ob_start_date']},{$bill_info['ob_end_date']}");
        unset($order_condition['finnshed_time']);

        if (!is_numeric($_GET['curpage'])) {
            $count = $model_order_book->getOrderBookCount($condition);
            $array = array();
            if ($count > self::EXPORT_SIZE) {
                //显示下载链接
                $page = ceil($count / self::EXPORT_SIZE);
                for ($i = 1; $i <= $page; $i++) {
                    $limit1 = ($i - 1) * self::EXPORT_SIZE + 1;
                    $limit2 = $i * self::EXPORT_SIZE > $count ? $count : $i * self::EXPORT_SIZE;
                    $array[$i] = $limit1 . ' ~ ' . $limit2;
                }
                Tpl::output('list', $array);
                Tpl::output('murl', 'index.php?act=shequ_bill&op=show_bill&ob_id=' . $ob_id);
                //网 店 运 维shop wwi.com
                Tpl::setDirquna('shop');
                Tpl::showpage('export.excel');
                exit();
            }
            $limit = false;
        } else {
            //下载
            $limit1 = ($_GET['curpage'] - 1) * self::EXPORT_SIZE;
            $limit2 = self::EXPORT_SIZE;
            $limit = "{$limit1},{$limit2}";
        }

        $order_book_list = $model_order_book->getOrderBookList($condition, '', 'book_id desc', '*', $limit);

        //然后取订单信息
        $tmp_book = array();
        $order_id_array = array();
        if (is_array($order_book_list)) {
            foreach ($order_book_list as $order_book_info) {
                $order_id_array[] = $order_book_info['book_order_id'];
                $tmp_book[$order_book_info['book_order_id']]['book_cancel_time'] = $order_book_info['book_cancel_time'];
                $tmp_book[$order_book_info['book_order_id']]['book_real_pay'] = $order_book_info['book_real_pay'];
            }
        }
        $data = $model_order->getOrderList(array('order_id' => array('in', $order_id_array)), '', '*', 'order_id desc');

        $export_data = array();
        $export_data[0] = array('订单编号', '订单金额', '运费', '未退定金', '下单日期', '取消日期', '商家', '商家编号', '买家', '买家编号');
        $deposit_amount = 0;
        $k = 0;
        foreach ($data as $v) {
            //该订单算佣金
            $export_data[$k + 1][] = $v['order_sn'] . "\t";
            $export_data[$k + 1][] = $v['order_amount'];
            $export_data[$k + 1][] = $v['shipping_fee'];
            $deposit_amount += $export_data[$k + 1][] = ncPriceFormat($tmp_book[$v['order_id']]['book_real_pay']);
            $export_data[$k + 1][] = date('Y-m-d', $v['add_time']);
            $export_data[$k + 1][] = date('Y-m-d', $tmp_book[$v['order_id']]['book_cancel_time']);
            $export_data[$k + 1][] = $v['store_name'];
            $export_data[$k + 1][] = $v['store_id'];
            $export_data[$k + 1][] = htmlspecialchars($v['buyer_name']);
            $export_data[$k + 1][] = $v['buyer_id'];
            $k++;
        }
        $count = count($export_data);
        $export_data[$count][] = '合计';
        $export_data[$count][] = '';
        $export_data[$count][] = '';
        $export_data[$count][] = $deposit_amount;
        $csv = new Csv();
        $export_data = $csv->charset($export_data, CHARSET, 'gbk');
        //期账单-未退定金预定订单列表
        $csv->filename = $ob_id . '-bill';
        $csv->export($export_data);
    }

    /**
     * 导出结算退单明细CSV
     *
     */
    public function export_refund_orderOp()
    {
        $ob_id = intval($_GET['ob_id']);
        if ($ob_id <= 0) {
            exit();
        }

        error_reporting(E_ALL);
        set_time_limit(1800);
     //   ini_set('memory_limit','4G');
        $model_bill = Model('shequ_bill');
        $bill_info = $model_bill->getOne(array('ob_id' => $ob_id));
        if (!$bill_info) {
            exit();
        }
        /** @var refund_returnModel $model_refund */
        $model_refund = Model('refund_return');
        $condition = array();
        $condition['seller_state'] = 2;
        $condition['shequ_tz_id'] = $bill_info['ob_store_id'];
        $condition['goods_id'] = array('gt', 0);
        if (preg_match('/^[\d,]+$/', $_GET['refund_id'])) {
            $_GET['refund_id'] = explode(',', trim($_GET['refund_id'], ','));
            $condition['refund_id'] = array('in', $_GET['refund_id']);
        }
        if ($_GET['query'] != '' && in_array($_GET['qtype'], array('refund_sn', 'order_sn', 'buyer_name'))) {
            $condition[$_GET['qtype']] = array('like', "%{$_GET['query']}%");
        }
        $if_start_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/', $_GET['query_start_date']);
        $if_end_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/', $_GET['query_end_date']);
        $start_unixtime = $if_start_date ? strtotime($_GET['query_start_date']) : null;
        $end_unixtime = $if_end_date ? strtotime($_GET['query_end_date']) : null;
        $end_unixtime = $if_end_date ? $end_unixtime + 86400 - 1 : null;
        if ($if_start_date || $if_end_date) {
            $condition['admin_time'] = array('between', "{$start_unixtime},{$end_unixtime}");
        } else {
            $condition['admin_time'] = array('between', "{$bill_info['ob_start_date']},{$bill_info['ob_end_date']}");
        }
        $sort_fields = array('refund_amount', 'commis_amount', 'refund_type', 'admin_time', 'buyer_id', 'store_id' ,'goods_num');
        if (in_array($_GET['sortorder'], array('asc', 'desc')) && in_array($_GET['sortname'], $sort_fields)) {
            $order = $_GET['sortname'] . ' ' . $_GET['sortorder'];
        }
        if($_GET['search_type'] != ''){
            if($_GET['search_type'] == '已结算'){
                $post_query = array('in','1,2,3');
            } else if($_GET['search_type'] == '异常'){
                $post_query = array('in','-1,-2,-3');
            } else if($_GET['search_type'] == '第1次结算'){
                $post_query = array('in','-1,1');
            } else if($_GET['search_type'] == '第2次结算'){
                $post_query = array('in','-2,2');
            } else if($_GET['search_type'] == '第3次结算'){
                $post_query = array('in','-3,3');
            } else if($_GET['search_type'] == '第1次结算正常'){
                $post_query = 1;
            } else if($_GET['search_type'] == '第2次结算正常'){
                $post_query = 2;
            } else if($_GET['search_type'] == '第3次结算正常'){
                $post_query = 3;
            } else if($_GET['search_type'] == '第1次结算异常'){
                $post_query = -1;
            } else if($_GET['search_type'] == '第2次结算异常'){
                $post_query = -2;
            } else if($_GET['search_type'] == '第3次结算异常'){
                $post_query = -3;
            } else if($_GET['search_type'] == '未对账'){
                $post_query = 0;
            }
            $condition['check_result'] = $post_query;
        }
        if (!is_numeric($_GET['curpage'])) {
            $count = $model_refund->getRefundReturn($condition);
            $array = array();
            if ($count > self::EXPORT_SIZE) {   //显示下载链接
                $page = ceil($count / self::EXPORT_SIZE);
                for ($i = 1; $i <= $page; $i++) {
                    $limit1 = ($i - 1) * self::EXPORT_SIZE + 1;
                    $limit2 = $i * self::EXPORT_SIZE > $count ? $count : $i * self::EXPORT_SIZE;
                    $array[$i] = $limit1 . ' ~ ' . $limit2;
                }
                Tpl::output('list', $array);
                Tpl::output('murl', 'index.php?act=shequ_bill&op=show_bill&query_type=refund&ob_id=' . $ob_id);
                //网 店 运 维shop wwi.com
                Tpl::setDirquna('shop');
                Tpl::showpage('export.excel');
                exit();
            }
            $limit = false;
        } else {
            //下载
            $limit1 = ($_GET['curpage'] - 1) * self::EXPORT_SIZE;
            $limit2 = self::EXPORT_SIZE;
            $limit = "{$limit1},{$limit2}";
        }
        $field = C('tablepre') . 'refund_return.*,ROUND(refund_amount*commis_rate/100,2) as commis_amount';
        $field = '*';
        $data = $model_refund->getRefundReturnList($condition, '', $field, $limit, $order);
        $order_id_array = array_column($data, 'order_id');
        /** @var orderModel $orderGoodsModel */
        $orderGoodsModel = Model('order');
        $orderGoodsList = $orderGoodsModel->getOrderGoodsList(array('order_id' => array('in', $order_id_array)),'*',9999999);
        $orderGoodsList = array_under_reset($orderGoodsList, 'order_id', 2);
        if (is_array($data) && count($data) == 1 && $data[0]['refund_id'] == '') {
            $refund_list = array();
        }

        $check_result_array = array(
            '1' => '第一次结算',
            '2' => '第二次结算',
            '3' => '第三次结算',
            '-1' => '第一次异常',
            '-2' => '第二次异常',
            '-3' => '第三次异常',
            '0' => '未对账'
        );

        $export_data = array();
        $export_data[0] = array('结算单号', '退单编号', '订单编号', '是否结算','类型', '退款日期','发货日期','商家', '商家编号', '买家', '买家编号', '退单商品', '商品编号','交易金额','购买数量','退货数量', '退单商品数', '退单金额', '退还佣金', '退还红包', '退还成本',
            '退款原因','退款说明','凭证上传','审核结果','处理备注','处理时间','平台确认','处理备注','处理时间','支付方式','在线退款金额','预存款金额','充值卡金额','导入时间','退款操作来源');
        $refund_amount = 0;
        $commis_totals = $rpt_totals = $cost_totals = 0;
        $k = 0;
        foreach ($data as $v) {
            $recList = $orderGoodsList[$v['order_id']];
            $orderGoods = array(
                'goods_cost' => 0,
                'commis_rate' => 0,
                'goods_pay_price' => 1,
            );
            foreach ($recList as $rec) {
                if ($rec['goods_id'] == $v['goods_id']) {
                    $orderGoods = $rec;
                    break;
                }
            }

            $refundAmount = $v['refund_amount_bill'] == -1 ? $v['refund_amount'] : $v['refund_amount_bill'];
            /** @var integer $refund_price */
            //退还成本
            $refund_price = $bill_info['ob_store_manage_type'] == 'platform' ?
                ncPriceFormat($refundAmount) :
                ncPriceFormat($refundAmount * $orderGoods['goods_cost'] / $orderGoods['goods_pay_price']);
            /** @var integer $commis_amount */
            $commis_amount = $bill_info['ob_store_manage_type'] == 'platform' ? ncPriceFormat($refundAmount * $orderGoods['commis_rate'] / 100) : 0;

            //退还的红包值(平台模式，且全额退款，红包才全额退还)
            // 期望方式，但目前不是这样计算的 ：sprintf("%.2f", ($refund_amount/$ogInfo['goods_pay_price'])*$ogInfo['rpt_amount'] );
            if ($bill_info['ob_store_manage_type'] == 'platform' && $refundAmount == $orderGoods['goods_pay_price']) {
                $rpt_amount = $orderGoods['rpt_bill'];
            } else {
                $rpt_amount = 0;
            }

            //平台和共建/自营 的 最终成本价计算
            if ($bill_info['ob_store_manage_type'] == 'platform') {
                $cost = $refundAmount - $commis_amount + $rpt_amount; //总退还值
            } else {
                $cost = ($refundAmount / $orderGoods['goods_pay_price']) * $orderGoods['goods_cost']; //总退还值
            }
            $cost = ncPriceFormat($cost);

            $export_data[$k + 1][] = $ob_id . "\t";
            $export_data[$k + 1][] = $v['refund_sn'] . "\t";
            $export_data[$k + 1][] = $v['order_sn'] . "\t";
            $export_data[$k + 1][] = $check_result_array[$v['check_result']];
            $export_data[$k + 1][] = str_replace(array(1, 2), array('退款', '退货'), $v['refund_type']);
            $export_data[$k + 1][] = date('Y-m-d', $v['admin_time']);
            $export_data[$k + 1][] = date('Y-m-d', $v['order_ship_time']);
            $export_data[$k + 1][] = $v['store_name'];
            $export_data[$k + 1][] = $v['store_id'];
            $export_data[$k + 1][] = htmlspecialchars($v['buyer_name']);
            $export_data[$k + 1][] = $v['buyer_id'];

            //退单商品
            $export_data[$k + 1][] = $orderGoods['goods_name'];
            //商品编号
            $export_data[$k + 1][] = $orderGoods['goods_id'];
            $export_data[$k + 1][] = ncPriceFormat($orderGoods['goods_pay_price']);
            //订单交易数
            $export_data[$k + 1][] = $orderGoods['goods_num'];
            //订单退货数
            if($v['refund_type'] == 1){
                $export_data[$k + 1][] = 0;
            } else {
                $export_data[$k + 1][] = $v['goods_num'];
            }
            //退单商品数
            $export_data[$k + 1][] = $orderGoods['goods_num'];
            //退单金额
            $refund_amount += $export_data[$k + 1][] = $refundAmount;
            //退还佣金
            $commis_totals += ncPriceFormat($export_data[$k + 1][] = $commis_amount);
            //退还红包
            $rpt_totals += ncPriceFormat($export_data[$k + 1][] = $rpt_amount);
            //退还成本
            $cost_totals += $export_data[$k + 1][] = ncPriceFormat($cost);
            $export_data[$k + 1][] = $v['reason_info'];
            $export_data[$k + 1][] = $v['buyer_message'];
            $picInfo = unserialize($v['pic_info']);
            if(isset($picInfo['buyer'])&&is_array($picInfo['buyer'])&&count($picInfo['buyer'])>0){
                $images = array();
                foreach ($picInfo['buyer'] as $image){
                    $images[] = UPLOAD_SITE_URL.'/'.ATTACH_PATH.'/refund/'.$image;
                }
                $export_data[$k + 1][] = implode(',',$images);
            }else{
                $export_data[$k + 1][] = '无';
            }
            $export_data[$k + 1][] = $v['seller_state']==2?'同意':'不同意';
            $export_data[$k + 1][] = preg_replace('/[,\r\n]+/i','',$v['seller_message']);
            //$export_data[$k + 1][] = $v['seller_message'];
            $export_data[$k + 1][] = date('Y-m-d', $v['seller_time']);
            $export_data[$k + 1][] = '已审核';
            $export_data[$k + 1][] = preg_replace('/[,\r\n]+/i','',$v['admin_message']);
            $export_data[$k + 1][] = date('Y-m-d', $v['admin_time']);
            $export_data[$k + 1][] = $v['refund_way'];
            if(in_array($v['refund_way'],array('alipay','yeepay','fenxiao'))){
                $export_data[$k + 1][] = ncPriceFormat($v['refund_amount']);
                $export_data[$k + 1][] = 0;
                $export_data[$k + 1][] = 0;
            }else if ($v['refund_way'] == 'predeposit'){
                $export_data[$k + 1][] = 0;
                $export_data[$k + 1][] = ncPriceFormat($v['refund_amount']);
                $export_data[$k + 1][] = 0;
            }else{
                $export_data[$k + 1][] = 0;
                $export_data[$k + 1][] = 0;
                $export_data[$k + 1][] = 0;
            }
            if($v['fenxiao_time']){
                $export_data[$k+1][] =  date('Y-m-d H:i:s',$v['fenxiao_time']);
            }
            switch ($v['operation_type']){
                case 0:$export_data[$k+1][]='用户申请';break;
                case 1:$export_data[$k+1][]='后台处理';break;
                case 2:$export_data[$k+1][]='渠道抓取';break;
            }
            $k++;
        }
        $count = count($export_data);
        $export_data[$count][] = '';
        $export_data[$count][] = '合计';
        $export_data[$count][] = '';
        $export_data[$count][] = '';
        $export_data[$count][] = '';
        $export_data[$count][] = '';
        $export_data[$count][] = '';
        $export_data[$count][] = '';
        $export_data[$count][] = '';
        $export_data[$count][] = '';
        $export_data[$count][] = '';
        $export_data[$count][] = '';
        $export_data[$count][] = '';
        $export_data[$count][] = '';
        $export_data[$count][] = '';
        $export_data[$count][] = '';
        $export_data[$count][] = '';
        $export_data[$count][] = ncPriceFormat($refund_amount);
        $export_data[$count][] = ncPriceFormat($commis_totals);
        $export_data[$count][] = ncPriceFormat($rpt_totals);
        $export_data[$count][] = ncPriceFormat($cost_totals);
        $csv = new Csv();
        $export_data = $csv->charset($export_data, CHARSET, 'gbk');
        $csv->filename = $ob_id . '-refund';
        $csv->export($export_data);
    }
    public function export_costOp()
    {
        $ob_id = intval($_GET['ob_id']);
        if ($ob_id <= 0) {
            exit();
        }
        $model_bill = Model('shequ_bill');
        $bill_info = $model_bill->getOne(array('ob_id' => $ob_id));
        if (!$bill_info) {
            exit();
        }

        //店铺费用
        /** @var store_costModel $model_store_cost */
        $model_store_cost = Model('store_cost');
        $cost_condition = array();
        $cost_condition['cost_store_id'] = $bill_info['ob_store_id'];
        $cost_condition['cost_time'] = array('between', "{$bill_info['ob_start_date']},{$bill_info['ob_end_date']}");
        //取得店铺名字
        $store_info = Model('store')->getStoreInfoByID($bill_info['ob_store_id']);
        if (!is_numeric($_GET['curpage'])) {
            $count = $model_store_cost->getStoreCostCount($cost_condition);
            $array = array();
            if ($count > self::EXPORT_SIZE) {   //显示下载链接
                $page = ceil($count / self::EXPORT_SIZE);
                for ($i = 1; $i <= $page; $i++) {
                    $limit1 = ($i - 1) * self::EXPORT_SIZE + 1;
                    $limit2 = $i * self::EXPORT_SIZE > $count ? $count : $i * self::EXPORT_SIZE;
                    $array[$i] = $limit1 . ' ~ ' . $limit2;
                }
                Tpl::output('list', $array);
                Tpl::output('murl', 'index.php?act=shequ_bill&op=show_bill&query_type=cost&ob_id=' . $ob_id);
                //网 店 运 维shop wwi.com
                Tpl::setDirquna('shop');
                Tpl::showpage('export.excel');
                exit();
            }
            $limit = false;
        } else {
            //下载
            /*$limit1 = ($_GET['curpage'] - 1) * self::EXPORT_SIZE;
            $limit2 = self::EXPORT_SIZE;
            $limit = "{$limit1},{$limit2}";*/
        }

        $field = '*';
        $data = $model_store_cost->getStoreCostList($cost_condition, self::EXPORT_SIZE);
        $fx_order_id_array = array_column($data, 'fx_order_id');
        /** @var orderModel $orderModel */
        $orderModel = Model('order');
        $orderList = $orderModel->getOrderList(array('fx_order_id' => array('in', $fx_order_id_array)),9999999,'*','order_id desc','',array('order_goods'));
        $orders = array();
        foreach ($orderList as $order){
            if($order['store_id'] == $store_info['store_id']) $orders[$order['fx_order_id']] = $order;
        }
        $export_data = array();
        $export_data[0] = array('结算单号','店铺名称', '店铺ID', '促销名称', '促销费用', '申请日期', '分销订单号', '订单编号'
        , '订单金额', '商品ID', '商品', '数量', '实际支付', '备注');
        $cost_totals = 0;
        $k = 0;
        foreach ($data as $v) {
            $cost_totals+= $v['cost_price'];
            $order = false;
            if($v['fx_order_id'] && isset($orders[$v['fx_order_id']]))
                $order = $orders[$v['fx_order_id']];
            $export_data[$k + 1][] = $ob_id . "\t";
            $export_data[$k + 1][] = $store_info['store_name'] . "\t";
            $export_data[$k + 1][] = $v['cost_store_id'] . "\t";
            $export_data[$k + 1][] = $v['cost_remark'];
            $export_data[$k + 1][] = ncPriceFormat($v['cost_price']);
            $export_data[$k + 1][] = date('Y-m-d', $v['cost_time']);
            $export_data[$k + 1][] = $v['fx_order_id'] . "\t";
            if($order){
                $export_data[$k + 1][] = $order['order_sn'] . "\t";
                $export_data[$k + 1][] = $order['order_amount'];
                if(isset($order['extend_order_goods'])&&is_array($order['extend_order_goods'])){
                    $export_data[$k + 1][] = implode(',',array_column($order['extend_order_goods'],'goods_id'));
                    $export_data[$k + 1][] = implode(',',array_column($order['extend_order_goods'],'goods_name'));
                    $export_data[$k + 1][] = implode(',',array_column($order['extend_order_goods'],'goods_num'));
                    $export_data[$k + 1][] = implode(',',array_column($order['extend_order_goods'],'goods_pay_price'));
                }
            }
            $k++;
        }
        $export_data[$k + 1][] = $export_data[$k + 1][] =$export_data[$k + 1][] = $export_data[$k + 1][] = '';
        $export_data[$k + 1][] = $cost_totals;
        $csv = new Csv();
        $export_data = $csv->charset($export_data, CHARSET, 'gbk');
        $csv->filename = $ob_id . '-cost';
        $csv->export($export_data);
    }
    public function export_modifyOp()
    {
        $ob_id = intval($_GET['ob_id']);
        if ($ob_id <= 0) {
            exit();
        }
        error_reporting(E_ALL);
        set_time_limit(1800);
        ini_set('memory_limit','4G');
        /** @var shequ_billModel $model_bill */
        $model_bill = Model('shequ_bill');
        $bill_info = $model_bill->getOne(array('ob_id' => $ob_id));
        if (!$bill_info) {
            exit();
        }

        // 账单日志
        $condition = array();
        $condition['ob_id'] = $ob_id;
        $condition['log_type'] = array('in',array('data','refund'));
        //取得店铺名字
        $store_info = Model('store')->getStoreInfoByID($bill_info['ob_store_id']);
        if (!is_numeric($_GET['curpage'])) {
            $count = $model_bill->table('bill_log')->where($condition)->count();
            $array = array();
            if ($count > self::EXPORT_SIZE) {   //显示下载链接
                $page = ceil($count / self::EXPORT_SIZE);
                for ($i = 1; $i <= $page; $i++) {
                    $limit1 = ($i - 1) * self::EXPORT_SIZE + 1;
                    $limit2 = $i * self::EXPORT_SIZE > $count ? $count : $i * self::EXPORT_SIZE;
                    $array[$i] = $limit1 . ' ~ ' . $limit2;
                }
                Tpl::output('list', $array);
                Tpl::output('murl', 'index.php?act=shequ_bill&op=show_bill&query_type=modify&ob_id=' . $ob_id);
                //网 店 运 维shop wwi.com
                Tpl::setDirquna('shop');
                Tpl::showpage('export.excel');
                exit();
            }
            $limit = false;
        } else {
            //下载
            $limit1 = ($_GET['curpage'] - 1) * self::EXPORT_SIZE;
            $limit2 = self::EXPORT_SIZE;
            $limit = "{$limit1},{$limit2}";
        }

        $field = '*';
        $data = $model_bill->table('bill_log')->where($condition)->limit($limit)->select();
        $order_id_array = array_column($data, 'order_id');
        /** @var orderModel $orderModel */
        $orderModel = Model('order');
        /** @var refund_returnModel $refundModel */
        $refundModel = Model('refund_return');
        $orderList = $orderModel->getOrderList(array('order_id' => array('in', $order_id_array)),9999999);
        $goodsList = $orderModel->getOrderGoodsList(array('order_id' => array('in', $order_id_array)),'*',9999999);
        $refundList = $refundModel->table('refund_return')->where(array('order_id' => array('in', $order_id_array)))->limit(9999999)->select();
        $orders = array_under_reset($orderList,'order_id');
        $goodsList = array_under_reset($goodsList,'rec_id');
        $refundList = array_under_reset($refundList,'refund_id');
        $export_data = array();
        $export_data[0] = array('结算单号','店铺名称', '店铺ID', '调整类型', '调整字段', '原值', '新值', '订单编号'
        , '订单金额', '商品ID', '商品名称', '数量');
        $cost_totals = 0;
        $k = 0;
        $attributes = array(
            'goods_cost'=>'商品成本',
            'rpt_bill'=>'红包',
            'commis_rate'=>'佣金比例',
            'refund_amount_bill'=>'退款金额',
        );
        foreach ($data as $v) {
            $cost_totals+= $v['cost_price'];
            $order = $orders[$v['order_id']];
            $goods = array();
            if($v['log_type'] == 'data') $goods = $goodsList[$v['rec_id']];
            else{
                $refund = $refundList[$v['rec_id']];
                $goods = $goodsList[$refund['order_goods_id']];
            }
            $export_data[$k + 1][] = $ob_id . "\t";
            $export_data[$k + 1][] = $store_info['store_name'] . "\t";
            $export_data[$k + 1][] = $store_info['store_id'] . "\t";
            $export_data[$k + 1][] = $v['log_type'] == 'data'?'订单商品调整':'退款数据调整';
            $export_data[$k + 1][] = $attributes[$v['log_attribute']];
            $export_data[$k + 1][] = $v['old_value'];
            $export_data[$k + 1][] = $v['new_value'];
            $export_data[$k + 1][] = $order['order_sn']. "\t";
            $export_data[$k + 1][] = ncPriceFormat($order['order_amount']);
            $export_data[$k + 1][] = $goods['goods_id'] . "\t";
            $export_data[$k + 1][] = $goods['goods_name'] . "\t";
            $export_data[$k + 1][] = $goods['goods_num'] . "\t";
            $k++;
        }
        $csv = new Csv();
        $export_data = $csv->charset($export_data, CHARSET, 'gbk');
        $csv->filename = $ob_id . '-modify';
        $csv->export($export_data);
    }

    public function get_statis_xmlOp()
    {
        $condition = array();
        if (preg_match('/^\d{4}$/', $_POST['query'])) {
            $condition['os_year'] = $_POST['query'];
        }
        $sort_fields = array('os_month', 'os_start_date', 'os_end_date', 'os_order_totals', 'os_shipping_totals', 'os_commis_totals', 'os_order_return_totals', 'os_commis_return_totals', 'os_store_cost_totals', 'os_result_totals');
        if (in_array($_POST['sortorder'], array('asc', 'desc')) && in_array($_POST['sortname'], $sort_fields)) {
            $order = $_POST['sortname'] . ' ' . $_POST['sortorder'];
        }
        $model_bill = Model('shequ_bill');
        $bill_list = $model_bill->getOrderStatisList($condition, '*', $_POST['rp'], $order);
        $data = array();
        $data['now_page'] = $model_bill->shownowpage();
        $data['total_num'] = $model_bill->gettotalnum();
        foreach ($bill_list as $bill_info) {
            $list = array();
            $list['operation'] = "<a target=\"_blank\" class=\"btn green\" href=\"index.php?act=shequ_bill&op=show_statis&os_month={$bill_info['os_month']}\"><i class=\"fa fa-list-alt\"></i>查看</a>";
            $list['os_month'] = substr($bill_info['os_month'], 0, 4) . '-' . substr($bill_info['os_month'], 4);
            $list['os_start_date'] = date('Y-m-d', $bill_info['os_start_date']);
            $list['os_end_date'] = date('Y-m-d', $bill_info['os_end_date']);
            $list['os_order_totals'] = ncPriceFormat($bill_info['os_order_totals']);
            $list['os_shipping_totals'] = ncPriceFormat($bill_info['os_shipping_totals']);
            $list['os_commis_totals'] = ncPriceFormat($bill_info['os_commis_totals']);
            $list['os_order_return_totals'] = ncPriceFormat($bill_info['os_order_return_totals']);
            $list['os_commis_return_totals'] = ncPriceFormat($bill_info['os_commis_return_totals']);
            $list['os_store_cost_totals'] = ncPriceFormat($bill_info['os_store_cost_totals']);
            $list['os_result_totals'] = ncPriceFormat($bill_info['os_result_totals']);
            $data['list'][$bill_info['os_month']] = $list;
        }
        exit(Tpl::flexigridXML($data));
    }



    public function get_bill_xmlOp()
    {
        $gname = trim($_GET['gname']);
        /** @var shequ_billModel $model_bill */
        $model_bill = Model('shequ_bill');
        $condition = array();
        list($condition, $order) = $this->_get_bill_condition($condition);
        switch ($gname) {
            case 'hgsw':
                $condition['ob_state'] = BILL_STATE_HANGO;
                break;
            case 'gssw':
                $condition['ob_state'] = BILL_STATE_FIRE_PHONIX;
                break;
            case 'ceo':
                $condition['ob_state'] = BILL_STATE_CEO;
                break;
            default:
                break;
        }
        $bill_list = $model_bill->getList($condition, $_POST['rp'], $order,'*');
        $data = array();
        $data['now_page'] = $model_bill->shownowpage();
        $data['total_num'] = $model_bill->gettotalnum();
        foreach ($bill_list as $bill_info) {
            $list = array();
            if (in_array($bill_info['ob_state'], array(2, 3, 10, 11, 12, 13))) {
                $list['operation'] = "<a target=\"_blank\" class=\"btn orange\" href=\"index.php?act=shequ_bill&op=show_bill&ob_id={$bill_info['ob_id']}\"><i class=\"fa fa-gavel\"></i>处理</a>";
            } else {
                $list['operation'] = "<a target=\"_blank\" class=\"btn green\" href=\"index.php?act=shequ_bill&op=show_bill&ob_id={$bill_info['ob_id']}\"><i class=\"fa fa-list-alt\"></i>查看</a>";
            }
//            $list['operation']='<a target=\"_blank\" class=\"btn green\" href=\"index.php?act=shequ_bill&op=show_bill&ob_id={$bill_info[\'ob_id\']}\"><i class=\"fa fa-list-alt\"></i>查看</a>';
            $list['ob_id'] = $bill_info['ob_id'];
            $list['ob_commis_totals'] = ncPriceFormat($bill_info['ob_commis_totals']);
            $list['ob_commis_return_totals'] = ncPriceFormat($bill_info['ob_commis_return_totals']);
            $list['ob_result_totals'] = ncPriceFormat($bill_info['ob_result_totals']);
            $list['ob_create_date'] = date('Y-m-d', $bill_info['ob_create_date']);
            $list['ob_state'] = shequBillState($bill_info['ob_state']);
            $list['ob_store_name'] = $bill_info['ob_store_name'];
            $list['ob_start_date'] = date('Y-m-d', $bill_info['ob_start_date']);
            $list['ob_end_date'] = date('Y-m-d', $bill_info['ob_end_date']);
            $list['ob_store_id'] = $bill_info['ob_store_id'];
            $list['ob_remark'] = "<span title=\"可编辑\" column_id=\"{$bill_info['ob_id']}\" fieldname=\"gc_name\" nc_type=\"inline_edit\" class=\"editable \">{$bill_info['ob_remark']}</span>";
            $data['list'][$bill_info['ob_id']] = $list;
        }
        exit(Tpl::flexigridXML($data));
    }

    public function ajaxOp(){
        switch($_GET['branch']){
            /**
             * 更新备注
             */
            case 'gc_name':
                /** @var shequ_billModel $model_bill */
                $model_bill = Model('shequ_bill');
                $condition['ob_remark'] = trim($_GET['value']);
                $condition['ob_id'] = intval($_GET['id']);
                $bill = $model_bill->getOne($condition);
                if(empty($bill)){
                    $where = array('ob_id' => intval($_GET['id']));
                    $update_array = array();
                    $update_array['ob_remark'] = trim($_GET['value']);
                    $model_bill->editShequBill($update_array,$where);
                    $return = true;
                } else {
                    $return = false;
                }
                exit(json_encode(array('result'=>$return)));
                break;
        }
    }

    /**
     * 合并相同代码
     */
    private function _get_bill_condition($condition)
    {
        if ($_GET['query_year'] && $_GET['query_month']) {
            $_GET['os_month'] = intval($_GET['query_year'])."-".intval($_GET['query_month']);
        } elseif ($_GET['query_year']) {
            $condition['os_month'] = array('between', $_GET['query_year'] . '01,' . $_GET['query_year'] . '12');
        }
        if (!empty($_GET['os_month'])) {
             $time_stamp_start = strtotime($_GET['os_month']);
             $time_stamp_end  = strtotime($_GET['os_month']."+1 month")-1;
            $condition['ob_create_date'] = array('between',$time_stamp_start.','.$time_stamp_end);
        }
        if ($_REQUEST['query'] != '' && in_array($_REQUEST['qtype'], array('ob_no', 'ob_id', 'ob_store_name'))) {
            $condition[$_REQUEST['qtype']] = $_REQUEST['query'];
        }
        if (is_numeric($_GET["ob_state"])) {
            $condition['ob_state'] = intval($_GET["ob_state"]);
        }
        if (is_numeric($_GET["ob_no"])) {
            $condition['ob_no'] = intval($_GET["ob_no"]);
        }
        if (is_numeric($_GET["ob_id"])) {
            $condition['ob_id'] = intval($_GET["ob_id"]);
        }
        if ($_GET['ob_store_name'] != '') {
            if ($_GET['jq_query']) {
                $condition['ob_store_name'] = $_GET['ob_store_name'];
            } else {
                $condition['ob_store_name'] = array('like', "%{$_GET['ob_store_name']}%");
            }
        }
        $sort_fields = array('ob_id', 'ob_start_date', 'ob_end_date', 'ob_order_totals', 'ob_shipping_totals', 'ob_commis_totals', 'ob_order_return_totals', 'ob_commis_return_totals', 'ob_store_cost_totals', 'ob_result_totals', 'ob_create_date', 'ob_state', 'ob_store_id');
        if (in_array($_REQUEST['sortorder'], array('asc', 'desc')) && in_array($_REQUEST['sortname'], $sort_fields)) {
            $order = $_REQUEST['sortname'] . ' ' . $_REQUEST['sortorder'];
        } else {
            $order = 'ob_id desc';
        }
        return array($condition, $order);
    }

    /**
     * @param $order  array
     * @param $orderGoodsList array
     * @return int
     */
    private function getOrderCommis($order, $orderGoodsList)
    {
        $commis = 0;
        /** @var BillService $billService */
        $billService = Service('Bill');
        $ver1 = $billService->getCommVer1Time();
        foreach ($orderGoodsList as $orderGoods) {
            $commis += $order['finnshed_time'] < $ver1 ?
                $orderGoods['goods_pay_price'] * $orderGoods['commis_rate'] :
                ($orderGoods['goods_pay_price'] + $orderGoods['rpt_bill']) * $orderGoods['commis_rate'];
        }
        return ncPriceFormat($commis / 100);
    }

    public function jdy_pushOp() {
        $billModel = Model("bill");
        $billInfo = $billModel->getOne(array('ob_id'=>$_GET['id']));
        if (empty($billInfo)){
            echo json_encode(array('state'=>0,'msg'=>'没有找到指定结算单'));die;
        }
        /*if ($billInfo['jdy_msg']=='单据编号重复！'){
            // 处理单据重复问题
        }*/
        $data = array('jdy_push_time' => 0,'jdy_msg' => '');
        $result = $billModel->editOrderBill($data,array('ob_id'=>$_GET['id']));
        if($result){
            echo json_encode(array('state'=>1,'msg'=>'标记推送成功，系统将在1分钟内推送到精斗云'));die;
        }else{
            echo json_encode(array('state'=>0,'msg'=>'标记推送失败'));die;
        }
    }
    public function jdy_push_refundOp() {
        $billModel = Model("bill");
        $billInfo = $billModel->getOne(array('ob_id'=>$_GET['id']));
        if (empty($billInfo)){
            echo json_encode(array('state'=>0,'msg'=>'没有找到指定结算单'));die;
        }
        /*if ($billInfo['jdy_msg']=='单据编号重复！'){
            // 处理单据重复问题
        }*/
        $data = array('jdy_refund_time' => 0,'jdy_msg' => '');
        $result = $billModel->editOrderBill($data,array('ob_id'=>$_GET['id']));
        if($result){
            echo json_encode(array('state'=>1,'msg'=>'标记退单成功，系统将在1分钟内推送到精斗云'));die;
        }else{
            echo json_encode(array('state'=>0,'msg'=>'标记退单失败'));die;
        }
    }

    public function jdy_remappingOp() {
        $jdyEntryModel = Model("jdy_entry");
        $condition['id'] = $_GET['id'];
        $data['map_time'] = 0;
        $data['map_msg'] = '';
        $result = $jdyEntryModel->editItem($data,$condition);
        if($result){
            echo json_encode(array('state'=>1,'msg'=>'标记重新映射成功，系统将在1分钟内进行映射'));die;
        }else{
            echo json_encode(array('state'=>0,'msg'=>'标记重新映射失败'));die;
        }
    }


    /**
     * 获得平台应付金额计算公式的HTML
     * @param $bill_info
     * @return array
     */
    private function getPlatformAmount($bill_info){
        return ['payable_amount'=>$bill_info['ob_result_totals']];
    }
}
