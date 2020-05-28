<?php
/**
 * 分销交易管理
 *
 */
defined('ByShopWWI') or exit('Access Invalid!');
class pyramid_orderControl extends SystemControl{

    /**
     * 每次导出订单数量
     * @var int
     */
    const EXPORT_SIZE = 1000;

    public function __construct(){
        parent::__construct();
        Language::read('trade');
    }

    public function indexOp(){
        /** @var paymentModel $paymentModel */
        $paymentModel = Model('payment');
        $payment_list = $paymentModel->getPaymentOpenList();
        $payment_list['wxpay'] = array(
            'payment_code' => 'wxpay',
            'payment_name' => '微信支付'
        );
        Tpl::output('payment_list',$payment_list);
        Tpl::setDirquna('shop');
        Tpl::showpage('pyramid_order.index');
    }

    public function get_xmlOp(){
        /** @var orderModel $model_order */
        $model_order = Model('order');
        $condition = array();
        $this->_get_condition($condition);
        //$order_list = $model_order->getOrderList($condition,$_POST['rp'],'*',$order,'');
        $join_on = 'pyramid_order_log.order_id = orders.order_id';
        $str_i = 1;
        $condition_str = "";
        foreach ($condition as $condition_key=>$condition_value) {
            //$new_condition['orders.'.$condition_key] = $condition_value;
            if ($str_i == 1) {
                $condition_str .= "orders.". $condition_key. " = ". $condition_value;
            } else {
                $condition_str .= " AND orders.". $condition_key. " = ". $condition_value;
            }
            $str_i ++;
        }
        $page   = new Page();
        $page->setEachNum(!empty($_POST['rp']) ? intval($_POST['rp']) : 15);
        $param = array() ;
        $param['table'] = 'pyramid_order_log,orders' ;
        $param['field'] = 'orders.*,pyramid_order_log.invite_member_id,pyramid_order_log.id,pyramid_order_log.lg_member_id,pyramid_order_log.invite_level,pyramid_order_log.real_return_money,pyramid_order_log.return_money';
        $param['join_type'] = 'LEFT JOIN';
        $param['join_on'] = array($join_on);
        $param['where'] = $condition_str;
        $param['order'] = "orders.order_id desc";
        $order_list = Db::select($param,$page);
        $data = array();
        $data['now_page'] = $page->get('now_page');
        $data['total_num'] = $page->get('total_num');
        foreach ($order_list as $order_id => $order_info) {
            $order_info['state_desc'] = orderState($order_info);
            //取得订单其它扩展信息
            $model_order->getOrderExtendInfo($order_info);
            $list = array();
            $list['operation'] = "<a class=\"btn green\" target=\"_blank\" href=\"index.php?act=order&op=show_order&order_id={$order_info['order_id']}\"><i class=\"fa fa-list-alt\"></i>查看</a>";
            $list['lg_member_id'] = $order_info['lg_member_id'];
            $list['invite_level'] = $order_info['invite_level'];
            $list['real_return_money'] = $order_info['real_return_money'];
            $list['return_money'] = ($order_info['real_return_money'] > 0) ? 0 : ($order_info['order_state'] == 0 ? 0 :  ncPriceFormat($order_info['return_money'] * ($order_info['order_amount'] - $order_info['refund_amount'])/$order_info['order_amount'], 2));
            $list['order_sn'] = $order_info['order_sn'].str_replace(array(1,2,3), array(null,' [预定]','[门店自提]'), $order_info['order_type']);
            $list['order_from'] = orderFrom( $order_info['order_from'] , $order_info['buyer_name']) ;
            $list['add_times'] = date('Y-m-d H:i:s',$order_info['add_time']);
            $list['order_amount'] = ncPriceFormat($order_info['order_amount']);
            if ($order_info['shipping_fee']) {
                $list['order_amount'] .= '(含运费'.ncPriceFormat($order_info['shipping_fee']).')';
            }
            $refundStep =  orderRefundStep( $order_info ) ;
            $list['order_state'] = $refundStep ? $order_info['state_desc'] ."({$refundStep})" : $order_info['state_desc'] ;
            $list['pay_sn'] = empty($order_info['pay_sn']) ? '' : $order_info['pay_sn'];
            $list['payment_code'] = orderPaymentName($order_info['payment_code']);
            $list['payment_time'] = !empty($order_info['payment_time']) ? (intval(date('His',$order_info['payment_time'])) ? date('Y-m-d H:i:s',$order_info['payment_time']) : date('Y-m-d',$order_info['payment_time'])) : '';
            $list['rcb_amount'] = ncPriceFormat($order_info['rcb_amount']);
            $list['pd_amount'] = ncPriceFormat($order_info['pd_amount']);
            $list['refund_amount'] = ncPriceFormat($order_info['refund_amount']);
            $list['finnshed_time'] = !empty($order_info['finnshed_time']) ? date('Y-m-d H:i:s',$order_info['finnshed_time']) : '';
            $list['evaluation_state'] = str_replace(array(0,1,2), array('未评价','已评价','未评价'),$order_info['evaluation_state']);
            $list['store_id'] = $order_info['store_id'];
            $list['store_name'] = $order_info['store_name'];
            $list['buyer_id'] = $order_info['buyer_id'];
            $list['buyer_name'] = $order_info['buyer_name'];
            $data['list'][$order_info['id']] = $list;
        }
        exit(Tpl::flexigridXML($data));
    }

    /**
     * 导出
     *
     */
    public function export_step1Op(){
        error_reporting(E_ALL);
        set_time_limit(1800);
        ini_set('memory_limit','4G');
        /** @var orderModel $model_order */
        $condition  = array();
        if (preg_match('/^[\d,]+$/', $_GET['order_id'])) {
            $_GET['order_id'] = explode(',',trim($_GET['order_id'],','));
            $condition['order_id'] = array('in',$_GET['order_id']);
        }

        $this->_get_condition($condition);
        $join_on = 'pyramid_order_log.order_id = orders.order_id';
        $str_i = 1;
        $condition_str = "";
        foreach ($condition as $condition_key=>$condition_value) {
            if ($str_i == 1) {
                $condition_str .= "orders.". $condition_key. " = ". $condition_value;
            } else {
                $condition_str .= " AND orders.". $condition_key. " = ". $condition_value;
            }
            $str_i ++;
        }
        $param = array() ;
        $param['table'] = 'pyramid_order_log,orders' ;
        $param['field'] = 'orders.*,pyramid_order_log.invite_member_id,pyramid_order_log.id,pyramid_order_log.lg_member_id,pyramid_order_log.invite_level,pyramid_order_log.real_return_money,pyramid_order_log.return_money';
        $param['join_type'] = 'LEFT JOIN';
        $param['join_on'] = array($join_on);
        $param['where'] = $condition_str;
        $param['order'] = "orders.order_id desc";
        $order_list = Db::select($param);
        $this->createExcel($order_list);


    }

    /**
     * 生成excel
     *
     * @param array $data
     */
    private function createExcel($data = array()){
        Language::read('export');
        import('libraries.excel');
        $excel_obj = new Excel();
        $excel_data = array();

        //设置样式
        $excel_obj->setStyle(array('id'=>'s_title','Font'=>array('FontName'=>'宋体','Size'=>'12','Bold'=>'1')));

        //header
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'收益会员id');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'分销级别');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'可提现金额');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'冻结金额');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'订单编号');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'订单来源');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'下单时间');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'订单金额(元)');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'订单状态');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'支付单号');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'支付方式');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'外部交易号');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'支付时间');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'充值卡支付(元)');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'预存款支付(元)');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'退款金额(元)');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'订单完成时间');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'是否评价');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'店铺ID');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'店铺名称');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'店铺类型');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'买家ID');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'买家账号');

        foreach ((array)$data as $k=>$order_info){
            $order_info['state_desc'] = orderState($order_info);
            $list = array();
            $list['lg_member_id'] = $order_info['lg_member_id'];
            $list['invite_level'] = $order_info['invite_level'];
            $list['real_return_money'] = $order_info['real_return_money'];
            $list['return_money'] = $order_info['order_state'] == 0 ? 0 :  ncPriceFormat($order_info['return_money'] * ($order_info['order_amount'] - $order_info['refund_amount'])/$order_info['order_amount'], 2);
            $list['order_sn'] = $order_info['order_sn'].str_replace(array(1,2,3), array(null,' [预定]','[门店自提]'), $order_info['order_type']);
            $list['order_from'] = orderFrom($order_info['order_from'], $order_info['buyer_name']);
            $list['add_time'] = date('Y-m-d H:i:s',$order_info['add_time']);
            $list['order_amount'] = ncPriceFormat($order_info['order_amount']);
            if ($order_info['shipping_fee']) {
                $list['order_amount'] .= '(含运费'.ncPriceFormat($order_info['shipping_fee']).')';
            }
            $list['order_state'] = $order_info['state_desc'];
            $list['pay_sn'] = empty($order_info['pay_sn']) ? '' : $order_info['pay_sn'];
            $list['payment_code'] = orderPaymentName($order_info['payment_code']);
            $list['trade_no'] = $order_info['trade_no'];
            $list['payment_time'] = !empty($order_info['payment_time']) ? (intval(date('His',$order_info['payment_time'])) ? date('Y-m-d H:i:s',$order_info['payment_time']) : date('Y-m-d',$order_info['payment_time'])) : '';
            $list['rcb_amount'] = ncPriceFormat($order_info['rcb_amount']);
            $list['pd_amount'] = ncPriceFormat($order_info['pd_amount']);
            $list['refund_amount'] = ncPriceFormat($order_info['refund_amount']);
            $list['finnshed_time'] = !empty($order_info['finnshed_time']) ? date('Y-m-d H:i:s',$order_info['finnshed_time']) : '';
            $list['evaluation_state'] = str_replace(array(0,1,2), array('未评价','已评价','未评价'),$order_info['evaluation_state']);
            $list['store_id'] = $order_info['store_id'];
            $list['store_name'] = $order_info['store_name'];
            $list['manage_type'] =$this->get_manage_type($order_info['manage_type']);
            $list['buyer_id'] = $order_info['buyer_id'];
            $list['buyer_name'] = $order_info['buyer_name'];

            $tmp = array();
            $tmp[] = array('data' => $list['lg_member_id']);
            $tmp[] = array('data' => $list['invite_level']);
            $tmp[] = array('data' => $list['real_return_money']);
            $tmp[] = array('data' => $list['return_money']);
            $tmp[] = array('data' => $list['order_sn']);
            $tmp[] = array('data' => $list['order_from']);
            $tmp[] = array('data' => $list['add_time']);
            $tmp[] = array('data' => $list['order_amount']);
            $tmp[] = array('data' => $list['order_state']);
            $tmp[] = array('data' => $list['pay_sn']);
            $tmp[] = array('data' => $list['payment_code']);
            $tmp[] = array('data' => $list['trade_no']);
            $tmp[] = array('data' => $list['payment_time']);
            $tmp[] = array('data' => $list['rcb_amount']);
            $tmp[] = array('data' => $list['pd_amount']);
            $tmp[] = array('data' => $list['refund_amount']);
            $tmp[] = array('data' => $list['finnshed_time']);
            $tmp[] = array('data' => $list['evaluation_state']);
            $tmp[] = array('data' => $list['store_id']);
            $tmp[] = array('data' => $list['store_name']);
            $tmp[] = array('data' => $list['manage_type']);
            $tmp[] = array('data' => $list['buyer_id']);
            $tmp[] = array('data' => $list['buyer_name']);
            $excel_data[] = $tmp;
        }
        $excel_data = $excel_obj->charset($excel_data,CHARSET);
        $excel_obj->addArray($excel_data);
        $excel_obj->addWorksheet($excel_obj->charset(L('exp_od_order'),CHARSET));
        $excel_obj->generateXML('order-'.$_GET['curpage'].'-'.date('Y-m-d-H',time()));
    }

    public function  get_manage_type($type){
        $data=array(
            'co_construct'=>'共建',
            'platform'=>'平台',
            'hango'=>'自营',
            'b2b'=>'b2b',
        );
        return !empty($data[$type]) ? $data[$type]:"";
    }

    /**
     * 处理搜索条件
     */
    private function _get_condition(& $condition) {
        if ($_REQUEST['query'] != '' && in_array($_REQUEST['qtype'],array('order_sn','store_name','buyer_name','buyer_phone','pay_sn'))) {
            $condition[$_REQUEST['qtype']] = array('like',"%{$_REQUEST['query']}%");
        }
        if ($_REQUEST['query'] != '' && in_array($_REQUEST['qtype'],array('order_id'))) {
            $condition[$_REQUEST['qtype']] = array('eq',"{$_REQUEST['query']}");
        }
        if ($_GET['keyword'] != '' && in_array($_GET['keyword_type'],array('order_sn','store_name','buyer_name','pay_sn','shipping_code'))) {
            if ($_GET['jq_query']) {
                $condition[$_GET['keyword_type']] = $_GET['keyword'];
            } else {
                $condition[$_GET['keyword_type']] = array('like',"%{$_GET['keyword']}%");
            }
        }
        if (!in_array($_GET['qtype_time'],array('add_time','payment_time','finnshed_time','shipping_time','import_time'))) {
            $_GET['qtype_time'] = null;
        }
        $if_start_time = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_start_date']);
        $if_end_time = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_end_date']);
        $start_unixtime = $if_start_time ? strtotime($_GET['query_start_date']) : null;
        $end_unixtime = $if_end_time ? strtotime($_GET['query_end_date']): null;
        if ($_GET['qtype_time'] && ($start_unixtime || $end_unixtime)) {
            $condition[$_GET['qtype_time']] = array('time',array($start_unixtime,$end_unixtime));
        }
        if($_GET['payment_code']) {
            if ($_GET['payment_code'] == 'wxpay') {
                $condition['payment_code'] = array('in',array('wxpay','wx_saoma','wx_jsapi'));
            } elseif ($_GET['payment_code   ']=='predeposit') {
                $condition['rcb_amount|pd_amount'] = array('gt', '0');
            } else {
                $condition['payment_code'] = $_GET['payment_code'];
            }
        }
        if(in_array($_GET['order_state'],array('0','10','15','20','21','30','40'))){
            $condition['order_state'] = $_GET['order_state'];
            //代发货搜索，排查全额退款订单
            if ('20' == $_GET['order_state']) {
                $condition['refund_state'] = array('neq','2');
            }
        }
        if(in_array($_GET['refund_state'],array('0','1','2'))){
            $condition['refund_state'] = $_GET['refund_state'];
            if($_GET['refund_state'] === '0'){
                $condition['lock_state'] = '0';
            }
        }
        if (!in_array($_GET['query_amount'],array('order_amount','shipping_fee','refund_amount'))) {
            $_GET['query_amount'] = null;
        }
        if (floatval($_GET['query_start_amount']) > 0 && floatval($_GET['query_end_amount']) > 0 && $_GET['query_amount']) {
            $condition[$_GET['query_amount']] = array('between',floatval($_GET['query_start_amount']).','.floatval($_GET['query_end_amount']));
        }
        if(in_array($_GET['order_from'],array('1','2','3','4','6'))){
            $condition['order_from'] = $_GET['order_from'];
        }
    }
}
