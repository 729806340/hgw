<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/9/6 0006
 * Time: 上午 9:50
 */

defined('ByShopWWI') or exit('Access Invalid!');
class channel_billControl extends SystemControl
{
    /**
     * 每次导出订单数量
     * @var int
     */
    const EXPORT_SIZE = 1000;

    private $links = array(
        array('url' => 'act=bill&op=index', 'lang' => 'nc_manage'),
    );

    public function __construct()
    {
        parent::__construct();
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
        Tpl::showpage('channel_bill.index');
    }

    public function get_bill_xmlOp()
    {
        $gname = trim($_GET['gname']);
        /** @var billModel $model_bill */
        $model_bill = Model('channel_bill');
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
        $bill_list = $model_bill->getOrderBillList($condition, '*', $_POST['rp'], $order);
        $data = array();
        $data['now_page'] = $model_bill->shownowpage();
        $data['total_num'] = $model_bill->gettotalnum();
        foreach ($bill_list as $bill_info) {
            $list = array();
            $list['operation'] = "<a target=\"_blank\" class=\"btn green\" href=\"index.php?act=channel_bill&op=show_bill&ob_id={$bill_info['ob_id']}\"><i class=\"fa fa-list-alt\"></i>查看</a>";
            $list['ob_id'] = $bill_info['ob_id'];
            $list['ob_order_totals'] = ncPriceFormat($bill_info['ob_order_totals']);
            $list['ob_shipping_totals'] = ncPriceFormat($bill_info['ob_shipping_totals']);
            $list['ob_order_return_totals'] = ncPriceFormat($bill_info['ob_order_return_totals']);
            $list['ob_result_totals'] = ncPriceFormat($bill_info['ob_result_totals']);
            $list['ob_create_date'] = date('Y-m-d', $bill_info['ob_create_date']);
            $list['ob_channel_name'] = $bill_info['ob_channel_name'];
            $list['ob_start_date'] = date('Y-m-d', $bill_info['ob_start_date']);
            $list['ob_end_date'] = date('Y-m-d', $bill_info['ob_end_date']);
            $list['ob_channel_id'] = $bill_info['ob_channel_id'];
            $list['ob_remark'] = "<span title=\"可编辑\" column_id=\"{$bill_info['ob_id']}\" fieldname=\"gc_name\" nc_type=\"inline_edit\" class=\"editable \">{$bill_info['ob_remark']}</span>";
            $data['list'][$bill_info['ob_id']] = $list;
        }
        exit(Tpl::flexigridXML($data));
    }

    /**
     * 合并相同代码
     */
    private function _get_bill_condition($condition)
    {
        if ($_GET['query_year'] && $_GET['query_month']) {
            $_GET['os_month'] = intval($_GET['query_year'] . $_GET['query_month']);
        } elseif ($_GET['query_year']) {
            $condition['os_month'] = array('between', $_GET['query_year'] . '01,' . $_GET['query_year'] . '12');
        }
        if (!empty($_GET['os_month'])) {
            $condition['os_month'] = intval($_GET['os_month']);
        }
        if ($_REQUEST['query'] != '' && in_array($_REQUEST['qtype'], array('ob_no', 'ob_id', 'ob_channel_name'))) {
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
        if ($_GET['ob_channel_name'] != '') {
            if ($_GET['jq_query']) {
                $condition['ob_channel_name'] = $_GET['ob_channel_name'];
            } else {
                $condition['ob_channel_name'] = array('like', "%{$_GET['ob_channel_name']}%");
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
     * 某店铺某月订单列表
     *
     */
    public function show_billOp()
    {
        $ob_id = intval($_GET['ob_id']);
        if ($ob_id <= 0) {
            showMessage('参数错误', '', 'html', 'error');
        }
        $model_bill = Model('channel_bill');
        $bill_info = $model_bill->getOrderBillInfo(array('ob_id' => $ob_id));
        if (!$bill_info) {
            showMessage('参数错误', '', 'html', 'error');
        }
        $order_condition = array();
        $order_condition['order_state'] = ORDER_STATE_SUCCESS;
        $order_condition['buyer_id'] = $bill_info['ob_channel_id'];
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
            $sub_tpl_name = 'channel_order_bill.show.refund_list';
        }
        else {
            //订单列表
            $sub_tpl_name = 'channel_order_bill.show.order_list';
        }

        Tpl::output('tpl_name', $sub_tpl_name);
        Tpl::output('gname', $this->admin_info['gname']);
        Tpl::output('bill_info', $bill_info);
        //网 店 运 维shop wwi.com
        Tpl::setDirquna('shop');
        Tpl::showpage('channel_order_bill.show');
    }

    public function get_bill_info_xmlOp()
    {
        $ob_id = intval($_GET['ob_id']);
        if ($ob_id <= 0) {
            exit();
        }
        $model_bill = Model('channel_bill');
        $bill_info = $model_bill->getOrderBillInfo(array('ob_id' => $ob_id));
        if (!$bill_info) {
            exit();
        }
        $order_condition = array();
        $order_condition['order_state'] = ORDER_STATE_SUCCESS;
        $order_condition['buyer_id'] = $bill_info['ob_channel_id'];
        $order_condition['filter_status']='0';
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
            //退款订单列表
            /** @var refund_returnModel $model_refund */
            $model_refund = Model('refund_return');
            $refund_condition = array();
            $refund_condition['seller_state'] = 2;
            $refund_condition['buyer_id'] = $bill_info['ob_channel_id'];
            $refund_condition['goods_id'] = array('gt', 0);
            $refund_condition['admin_time'] = $order_condition['finnshed_time'];
            if ($_POST['query'] != '' && in_array($_POST['qtype'], array('refund_sn', 'order_sn', 'store_name'))) {
                $refund_condition[$_POST['qtype']] = array('like', "%{$_POST['query']}%");
            }
            if ($_POST['query'] != '' && in_array($_POST['qtype'], array('check_status','send_sap', 'purchase_sap', 'sap_return_credit'))) {
                $refund_condition[$_POST['qtype']] = array('eq', "{$_POST['query']}");
            }
            $sort_fields = array('refund_amount', 'refund_type', 'admin_time', 'buyer_id', 'store_id','goods_name','goods_num','order_amount','cost_amount','rpt_bill');
            if (in_array($_POST['sortorder'], array('asc', 'desc')) && in_array($_POST['sortname'], $sort_fields)) {
                $order = $_POST['sortname'] . ' ' . $_POST['sortorder'];
            }
            $field = '*';
            $refund_list = $model_refund->getRefundReturnList($refund_condition, $_POST['rp'], $field, '', $order);
            if (is_array($refund_list) && count($refund_list) == 1 && $refund_list[0]['refund_id'] == '') {
                $refund_list = array();
            }
            $order_id_array = array_column($refund_list, 'order_id');
            /** @var orderModel $orderGoodsModel */
            $orderGoodsModel = Model('order');
            $orderGoodsList = $orderGoodsModel->getOrderGoodsList(array('order_id' => array('in', $order_id_array)));
            $orderGoodsList = array_under_reset($orderGoodsList, 'order_id', 2);
            $data = array();
            $data['now_page'] = $model_refund->shownowpage();
            $data['total_num'] = $model_refund->gettotalnum();
            foreach ($refund_list as $refund_info) {
                $recList = $orderGoodsList[$refund_info['order_id']];
                $orderGoods = array(
                    'goods_cost' => 0,
                    'commis_rate' => 0,
                    'goods_pay_price' => 1,
                );
                foreach ($recList as $rec) {
                    if ($rec['goods_id'] == $refund_info['goods_id']) {
                        $orderGoods = $rec;
                        break;
                    }
                }
                $list = array();
                if ($refund_info['refund_type'] == 1) {
                    $list['operation'] = "<a target=\"_blank\" class=\"btn green\" href=\"index.php?act=refund&op=view&refund_id={$refund_info['refund_id']}\"><i class=\"fa fa-list-alt\"></i>查看</a>";
                } else {
                    $list['operation'] = "<a target=\"_blank\" class=\"btn green\" href=\"index.php?act=return&op=view&return_id={$refund_info['refund_id']}\"><i class=\"fa fa-list-alt\"></i>查看</a>";
                }
                if (C('ON_DEV') || ($bill_info['ob_state'] == BILL_STATE_HANGO && $this->admin_info['gname'] == '汉购网商务')||
                    ($bill_info['ob_state'] == BILL_STATE_PART_PAY && in_array($refund_info['check_result'],array(-1,-2,-3)) && $this->admin_info['gname'] == '汉购网商务')
                ) {
                    $list['operation'] .= "<a class=\"btn green\" href=\"javascript:;\" onclick=\"ajax_form('edit-bill-data','调整退款信息','index.php?act=bill&op=edit_refund&ob_id={$ob_id}&refund_id={$refund_info['refund_id']}',1020,0);\"><i class=\"fa fa-gavel\"></i>调整</a>";
                }
                $list['refund_sn'] = $refund_info['refund_sn'];
                $list['order_sn'] = $refund_info['order_sn'];
                //$list['order_amount'] = $bill_info['ob_store_manage_type'] == 'platform' ? ncPriceFormat($refund_info['order_amount']) : ncPriceFormat($refund_info['cost_amount']);
                $refundAmount = $refund_info['refund_amount_bill'] == -1 ? $refund_info['refund_amount'] : $refund_info['refund_amount_bill'];
                $list['refund_amount'] = $bill_info['ob_store_manage_type'] == 'platform' ?
                    ncPriceFormat($refundAmount) :
                    ncPriceFormat($refundAmount * $orderGoods['goods_cost'] / $orderGoods['goods_pay_price']);
                $list['commis_amount'] = $bill_info['ob_store_manage_type'] == 'platform' ?
                    ncPriceFormat($refundAmount * $orderGoods['commis_rate'] / 100) : 0;
                //全部退款时的红包
                //$list['rpt_bill'] = ncPriceFormat($refund_info['rpt_bill']);
                // 期望方式，但目前不是这样计算的 ：sprintf("%.2f", ($refund_amount/$ogInfo['goods_pay_price'])*$ogInfo['rpt_amount'] );
                if ($bill_info['ob_store_manage_type'] == 'platform' && $refundAmount == $orderGoods['goods_pay_price']) {
                    $list['rpt_bill'] = $orderGoods['rpt_bill']; // 调整退款红包为平台承担部分
                } else {
                    $list['rpt_bill'] = 0;
                }

                $list['buy_num'] = $orderGoods['goods_num'];
                $list['refund_num'] = $refund_info['goods_num'];
                $list['refund_type'] = str_replace(array(1, 2), array('退款 ', '退货'), $refund_info['refund_type']);
                $list['admin_time'] = date('Y-m-d', $refund_info['admin_time']);
                $list['buyer_name'] = $refund_info['buyer_name'];
                $list['buyer_id'] = $refund_info['buyer_id'];
                $list['store_name'] = $refund_info['store_name'];
                $list['store_id'] = $refund_info['store_id'];
                $data['list'][$refund_info['refund_id']] = $list;
            }
            exit(Tpl::flexigridXML($data));
        }
        else {
            //订单列表
            $model_order = Model('order');
            if ($_POST['query'] != '' && in_array($_POST['qtype'], array('order_sn', 'store_name'))) {
                $order_condition[$_POST['qtype']] = array('like', "%{$_POST['query']}%");
            }
            if ($_GET['order_sn'] != '') {
                $order_condition['order_sn'] = array('like', "%{$_GET['order_sn']}%");
            }
            if ($_GET['store_name'] != '') {
                if ($_GET['jq_query']) {
                    $order_condition['store_name'] = $_GET['store_name'];
                } else {
                    $order_condition['store_name'] = array('like', "%{$_GET['store_name']}%");
                }
            }
            $sort_fields = array('order_amount', 'shipping_fee', 'commis_amount', 'add_time', 'finnshed_time', 'buyer_id', 'store_id', 'store_id','rpt_bill');
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
                $commis = $this->getOrderCommis($order_info, $orderGoodsList[$order_info['order_id']]);
                $list = array();
                $list['operation'] = "<a target=\"_blank\" class=\"btn green\" href=\"index.php?act=order&op=show_order&order_id={$order_info['order_id']}\"><i class=\"fa fa-list-alt\"></i>查看</a>";
                if (
                    ($bill_info['ob_state'] == BILL_STATE_HANGO && $this->admin_info['gname'] == '汉购网商务') ||
                    ($bill_info['ob_state'] == BILL_STATE_PART_PAY && in_array($order_info['check_result'],array(-1,-2,-3)) && $this->admin_info['gname'] == '汉购网商务') ||
                    ($bill_info['ob_state'] == BILL_STATE_FIRE_PHONIX && $this->admin_info['gname'] == '公司商务')
                )
                {
                    $list['operation'] .= "<a class=\"btn green\" href=\"javascript:;\" onclick=\"ajax_form('edit-bill-data','调整结算信息','index.php?act=bill&op=edit_order&ob_id={$ob_id}&order_id={$order_info['order_id']}',1020,0);\"><i class=\"fa fa-gavel\"></i>调整</a>";
                }
                $list['order_sn'] = $order_info['order_sn'];
                $list['order_amount'] = ncPriceFormat($order_info['order_amount']);
                $list['order_amount'] = $bill_info['ob_store_manage_type'] == 'platform' ? ncPriceFormat($order_info['order_amount']) : ncPriceFormat($order_info['cost_amount']);
                $list['shipping_fee'] = ncPriceFormat($order_info['shipping_fee']);
                $list['commis_amount'] = $commis;
                $list['rpt_bill'] = ncPriceFormat($order_info['rpt_bill']);
                $list['add_time'] = date('Y-m-d', $order_info['add_time']);
                $list['finnshed_time'] = date('Y-m-d', $order_info['finnshed_time']);
                $list['push_time'] = date('Y-m-d', $order_info['extend_order_common']['shipping_time']);
                $list['buyer_name'] = $order_info['buyer_name'];
                $list['buyer_id'] = $order_info['buyer_id'];
                $list['store_name'] = $order_info['store_name'];
                $list['store_id'] = $order_info['store_id'];
                $data['list'][$order_info['order_id']] = $list;
            }
            exit(Tpl::flexigridXML($data));
        }
    }

    /**
     * 导出平台月出账单表
     *
     */
    public function export_billOp()
    {
        $model_bill = Model('channel_bill');
        $condition = array();
        if (preg_match('/^[\d,]+$/', $_GET['ob_id'])) {
            $_GET['ob_id'] = explode(',', trim($_GET['ob_id'], ','));
            $condition['ob_id'] = array('in', $_GET['ob_id']);
        }
        list($condition, $order) = $this->_get_bill_condition($condition);

        if (!is_numeric($_GET['curpage'])) {
            $count = $model_bill->getOrderBillCount($condition);
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
        $data = $model_bill->getOrderBillList($condition, '*', '', 'ob_id desc', $limit);

        $export_data = array();
        $export_data[0] = array('账单编号', '开始日期', '结束日期', '订单金额', '运费','退款金额', '退还佣金', '本期应结', '出账日期', '渠道名称', '渠道ID','毛利','毛利率','备注');
        $ob_order_totals = 0;
        $ob_shipping_totals = 0;
        $ob_commis_totals = 0;
        $ob_order_return_totals = 0;
        $ob_commis_return_totals = 0;
        $ob_store_cost_totals = 0;
        $ob_result_totals = 0;
        foreach ($data as $k => $v) {
            $profit = '-';
            $profitRate = '-';
            if($v['ob_sales']>0){
                $profit = ncPriceFormat($v['ob_sales']-$v['ob_result_totals']);
                $profitRate = ncPriceFormat(100*$profit/$v['ob_sales']).'%';
            }
            $export_data[$k + 1][] = $v['ob_id'];
            $export_data[$k + 1][] = date('Y-m-d', $v['ob_start_date']);
            $export_data[$k + 1][] = date('Y-m-d', $v['ob_end_date']);
            $ob_order_totals += $export_data[$k + 1][] = $v['ob_order_totals'];
            $ob_shipping_totals += $export_data[$k + 1][] = $v['ob_shipping_totals'];
            $ob_order_return_totals += $export_data[$k + 1][] = $v['ob_order_return_totals'];
            $ob_commis_return_totals += $export_data[$k + 1][] = $v['ob_commis_return_totals'];
            //$ob_store_cost_totals += $export_data[$k + 1][] = $v['ob_store_cost_totals'];
            $ob_result_totals += $export_data[$k + 1][] = $v['ob_result_totals'];
            $export_data[$k + 1][] = date('Y-m-d', $v['ob_create_date']);
            $export_data[$k + 1][] = $v['ob_channel_name'];
            $export_data[$k + 1][] = $v['ob_channel_id'];
            $export_data[$k + 1][] = $profit;
            $export_data[$k + 1][] = $profitRate."\t";
            $export_data[$k + 1][] = $v['ob_remark'];
        }
        $count = count($export_data);
        $export_data[$count][] = '';
        $export_data[$count][] = '';
        $export_data[$count][] = '合计';
        $export_data[$count][] = $ob_order_totals;
        $export_data[$count][] = $ob_shipping_totals;
        $export_data[$count][] = $ob_commis_totals;
        $export_data[$count][] = $ob_order_return_totals;
        $export_data[$count][] = $ob_commis_return_totals;
        $export_data[$count][] = $ob_store_cost_totals;
        $export_data[$count][] = $ob_result_totals;
        $csv = new Csv();
        $export_data = $csv->charset($export_data, CHARSET, 'gbk');
        $csv->filename = 'channel_bill';
        $csv->export($export_data);
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

    public function export_profitOp()
    {
        $ob_id = intval($_GET['ob_id']);
        if ($ob_id <= 0) {
            exit();
        }
        /** @var billModel $model_bill */
        $model_bill = Model('channel_bill');
        $bill_info = $model_bill->getOrderBillInfo(array('ob_id' => $ob_id));
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
        $condition['buyer_id'] = $bill_info['ob_channel_id'];
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
                Tpl::output('murl', 'index.php?act=channel_bill&op=show_bill&ob_id=' . $ob_id);
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

    public function ajaxOp(){
        switch($_GET['branch']){
            /**
             * 更新备注
             */
            case 'gc_name':
                $model_bill = Model('channel_bill');
                $condition['ob_remark'] = trim($_GET['value']);
                $condition['ob_id'] = intval($_GET['id']);
                $bill = $model_bill->getOrderBillInfo($condition);
                if(empty($bill)){
                    $where = array('ob_id' => intval($_GET['id']));
                    $update_array = array();
                    $update_array['ob_remark'] = trim($_GET['value']);
                    $model_bill->editOrderBill($update_array,$where);
                    $return = true;
                } else {
                    $return = false;
                }
                exit(json_encode(array('result'=>$return)));
                break;
        }
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
        $model_bill = Model('channel_bill');
        $bill_info = $model_bill->getOrderBillInfo(array('ob_id' => $ob_id));
        if (!$bill_info) {
            exit();
        }

        /** @var orderModel $model_order */
        $model_order = Model('order');
        $condition = array();
        $condition['order_state'] = ORDER_STATE_SUCCESS;
        $condition['buyer_id'] = $bill_info['ob_channel_id'];
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
                Tpl::output('murl', 'index.php?act=bill&op=show_bill&ob_id=' . $ob_id);
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
        $export_data[0] = array('订单编号', '分销订单号', '订单金额','订单成本','是否结算', '运费', '佣金',
            '下单日期', '成交日期','发货日期', '商家', '商家编号', '买家', '买家编号', '订单红包',
            '商品ID', '商品', '单价', '数量', '实际支付', '佣金比例', '商品行佣金', '商品行成本', '商品行红包', '商品行成本单价','进项税','销项税',
        );
        $order_totals = 0;
        $order_cost_totals = 0;
        $shipping_totals = 0;
        $commis_totals = 0;
        $k = 0;
        $check_result_array = array(
            '1' => '第一次结算',
            '2' => '第二次结算',
            '3' => '第三次结算',
            '-1' => '第一次异常',
            '-2' => '第二次异常',
            '-3' => '第三次异常',
            '0' => '未对账'
        );
        foreach ($data as $v) {
            //该订单算佣金
            /*$field = $bill_info['ob_ver']==1
                    ?'SUM(ROUND((goods_pay_price+rpt_bill)*commis_rate/100,2)) as commis_amount,order_id'
                    :'SUM(ROUND(goods_pay_price*commis_rate/100,2)) as commis_amount,order_id';
            //'SUM(ROUND((goods_pay_price+rpt_bill)*commis_rate/100,2)) as commis_amount,order_id';
            $commis_list = $model_order->getOrderGoodsList($order_goods_condition,$field,null,null,'','order_id','order_id');*/
            $commis = $this->getOrderCommis($v, $v['extend_order_goods']);
            $export_data[$k + 1][] = $v['order_sn'] . "\t";
            $export_data[$k + 1][] = $v['fx_order_id'] . "\t";
            $order_totals += $export_data[$k + 1][] = $v['order_amount'];
            $order_cost_totals += $export_data[$k + 1][] = floatval($v['cost_amount']);
            $export_data[$k + 1][] = $check_result_array[$v['check_result']];
            $shipping_totals += $export_data[$k + 1][] = $v['shipping_fee'];
            $commis_totals += $export_data[$k + 1][] = $commis;
            $export_data[$k + 1][] = date('Y-m-d', $v['add_time']);
            $export_data[$k + 1][] = date('Y-m-d', $v['finnshed_time']);
            $export_data[$k + 1][] = date('Y-m-d', $v['extend_order_common']['shipping_time']);
            $export_data[$k + 1][] = $v['store_name'];
            $export_data[$k + 1][] = $v['store_id'];
            $export_data[$k + 1][] = htmlspecialchars($v['buyer_name']) . "\t";
            $export_data[$k + 1][] = $v['buyer_id'];
            $export_data[$k + 1][] = $v['rpt_bill'];
            $item_shipping_fee = $v['shipping_fee'] / count($v['extend_order_goods']);

            //$goods_string = '';
            $goodsCount = 0;
            if (is_array($v['extend_order_goods'])) {
                foreach ($v['extend_order_goods'] as $v1) {
                    if ($goodsCount > 0) {
                        $export_data[$k + 1][] = $export_data[$k + 1][] = $export_data[$k + 1][] = $export_data[$k + 1][] =
                        $export_data[$k + 1][] = $export_data[$k + 1][] = $export_data[$k + 1][] = $export_data[$k + 1][] =
                        $export_data[$k + 1][] = $export_data[$k + 1][] = $export_data[$k + 1][] =
                        $export_data[$k + 1][] = $export_data[$k + 1][] = $export_data[$k + 1][] = $export_data[$k + 1][] = '';
                    };
                    $export_data[$k + 1][] = $v1['goods_id'];
                    $export_data[$k + 1][] = $v1['goods_name'];
                    $export_data[$k + 1][] = $v1['goods_price'];
                    $export_data[$k + 1][] = $v1['goods_num'];
                    $export_data[$k + 1][] = ncPriceFormat($v1['goods_pay_price']);
                    /** @var BillService $billService */
                    $billService = Service('Bill');
                    $ver1 = $billService->getCommVer1Time();
                    if ($v['manage_type'] == 'platform') {
                        $export_data[$k + 1][] = $v1['commis_rate'];
                        $comprice = $export_data[$k + 1][] = $v['finnshed_time'] > $ver1 ?
                            ncPriceFormat(($v1['goods_pay_price'] + $v1['rpt_bill']) * $v1['commis_rate'] / 100) :
                            ncPriceFormat($v1['goods_pay_price'] * $v1['commis_rate'] / 100);
                        $export_data[$k + 1][] = ncPriceFormat($v1['goods_pay_price'] + $item_shipping_fee - $comprice + $v1['rpt_bill']);
                    } else {
                        $export_data[$k + 1][] = 0;
                        $export_data[$k + 1][] = 0;
                        $export_data[$k + 1][] = ncPriceFormat($v1['goods_cost']);
                    }
                    $export_data[$k + 1][] = ncPriceFormat($v1['rpt_bill']);
                    $export_data[$k + 1][] = ncPriceFormat($v1['goods_cost'] / $v1['goods_num'], 4);//$v1['goods_cost']/$;
                    $export_data[$k + 1][]=$v1['tax_input'];
                    $export_data[$k + 1][]=$v1['tax_output'];
                    //$goods_string .= $v['goods_name'].'|单价:'.$v['goods_price'].'|数量:'.$v['goods_num'].'|实际支付:'.$v['goods_pay_price'].'|佣金比例:'.$v['commis_rate'].'%';
                    $k++;
                    $goodsCount++;
                }
            } else {
                $k++;
            }
            //$export_data[$k+1][] = $goods_string;
        }
        $count = count($export_data);
        $export_data[$count][] = '合计';
        $export_data[$count][] = "\t";
        $export_data[$count][] = $order_totals;
        $export_data[$count][] = $order_cost_totals;
        $export_data[$count][] = $shipping_totals;
        $export_data[$count][] = $commis_totals;
        $export_data[$count][] = '所有订单总成本:'.floatval($order_info_all['cost_amount']);
        $csv = new Csv();
        $export_data = $csv->charset($export_data, CHARSET, 'gbk');
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
        $model_bill = Model('channel_bill');
        $bill_info = $model_bill->getOrderBillInfo(array('ob_id' => $ob_id));
        if (!$bill_info) {
            exit();
        }

        /** @var refund_returnModel $model_refund */
        $model_refund = Model('refund_return');
        $condition = array();
        $condition['seller_state'] = 2;
        $condition['buyer_id'] = $bill_info['ob_channel_id'];
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
                Tpl::output('murl', 'index.php?act=bill&op=show_bill&query_type=refund&ob_id=' . $ob_id);
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
            '退款原因','退款说明','凭证上传','审核结果','处理备注','处理时间','平台确认','处理备注','处理时间','支付方式','在线退款金额','预存款金额','充值卡金额','导入时间');
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

}