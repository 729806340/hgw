<?php
/**
 * 交易管理
 *
 * @汉购网提供技术支持 授权请购买shopnc授权
 * @license    http://www.hangowa.com
 * @link       交流群号：
 */
defined('ByShopWWI') or exit('Access Invalid!');
class orderControl extends SystemControl{
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
        //显示支付接口列表(搜索)
        $payment_list = Model('payment')->getPaymentOpenList();
        $payment_list['wxpay'] = array(
            'payment_code' => 'wxpay',
            'payment_name' => '微信支付'
        );
        $payment_list['fenxiao'] = array(
        		'payment_code' => 'fenxiao',
        		'payment_name' => '分销平台支付'
        );

         //会员名查询
        $buyer_name = '';
        if (!empty($_GET['buyer_name'])) {
            $buyer_name = strip_tags($_GET['buyer_name']);
        }
        Tpl::output('buyer_name',$buyer_name);

        Tpl::output('payment_list',$payment_list);
		Tpl::setDirquna('shequ');
        Tpl::showpage('order.index');
    }

    public function get_xmlOp(){
        $model_order = Model('order');
        $condition  = array();
        $this->_get_condition($condition);
        /*echo '<pre>';
        print_r($condition);exit;*/
        $sort_fields = array('buyer_name','store_name','order_id','payment_code','order_state','order_amount','order_from','pay_sn','rcb_amount','pd_amount','payment_time','finnshed_time','evaluation_state','refund_amount','buyer_id','store_id');
        if ($_POST['sortorder'] != '' && in_array($_POST['sortname'],$sort_fields)) {
            $order = $_POST['sortname'].' '.$_POST['sortorder'];
        }
        //$condition = array('order_sn' => '160803174274154001');
        /** @var orderModel $model_order */
        if($_GET['qtype_time']=="shipping_time"){
            $cond['shipping_time']=$condition['shipping_time'];
            $order_common=$model_order->getOrderCommonList($cond);
//            echo $model_order->getLastSql();exit();
            $condition['order_id']=array('in',array_column($order_common,'order_id'));
            unset($condition['shipping_time']);
            $order_list=$model_order->getOrderList($condition,$_POST['rp'],'*',$order,'',array('order_common'));
        }else{
            $order_list = $model_order->getOrderList($condition,$_POST['rp'],'*',$order,'',array('order_common'));
        }
        $data = array();
        $data['now_page'] = $model_order->shownowpage();
        $data['total_num'] = $model_order->gettotalnum();
        foreach ($order_list as $order_id => $order_info) {
            $order_info['if_system_cancel'] = $model_order->getOrderOperateState('system_cancel',$order_info);
            $order_info['if_system_receive_pay'] = $model_order->getOrderOperateState('system_receive_pay',$order_info);
            $order_info['state_desc'] = orderState($order_info);

            //取得订单其它扩展信息
            $model_order->getOrderExtendInfo($order_info);

            $list = array();$operation_detail = '';
            $list['operation'] = "<a class=\"btn green\" target=\"_blank\" href=\"index.php?act=order&op=show_order&order_id={$order_info['order_id']}\"><i class=\"fa fa-list-alt\"></i>查看</a>";
            if ($order_info['if_system_cancel']) {
                $operation_detail .= "<li><a href=\"javascript:void(0);\" onclick=\"fg_cancel({$order_info['order_id']})\">取消订单</a></li>";
            }
            if ($order_info['if_system_receive_pay']) {
                $op_name = $order_info['system_receive_pay_op_name'] ? $order_info['system_receive_pay_op_name'] : '收到货款';
                if( $order_info['payment_code'] == 'jicai' ) {
                	if( in_array($order_info['order_state'], array(0,10)) ) {
                		$operation_detail .= "<li><a target=\"_blank\" href=\"index.php?act=order&op=change_state&state_type=jicai_pay&order_id={$order_info['order_id']}\">集采确认待发货</a></li>";
                	}
                	if( in_array($order_info['order_state'], array(30,40)) ) {
                		$operation_detail .= "<li><a target=\"_blank\" href=\"index.php?act=order&op=add_trade_sn&order_id={$order_info['order_id']}\">集采补充流水号</a></li>";
                	}
                } elseif ( $order_info['payment_code'] == 'b2b' ) {
                	if( in_array($order_info['order_state'], array(0,10)) ) {
                		$operation_detail .= "<li><a href=\"javascript:void(0);\" onclick=\"opb2b({$order_info['order_id']}, 'b2b_pay', 'b2b收到货款')\">b2b收到货款</a></li>";
                	} elseif ($order_info['order_state']==20){
                		$operation_detail .= "<li><a href=\"javascript:void(0);\" onclick=\"opb2b({$order_info['order_id']}, 'b2b_finish', 'b2b确认完成')\">b2b确认完成</a></li>";
                	}
            	} else {
                	$operation_detail .= "<li><a target=\"_blank\" href=\"index.php?act=order&op=change_state&state_type=receive_pay&order_id={$order_info['order_id']}\">{$op_name}</a></li>";
                }
            }
            //退款操作  //社区团订单不能退款
            if($order_info['shequ_tuan_id']=='0'){
                if( !in_array($order_info['order_state'], array('0', '10')) && $order_info['refund_state'] != '2' && $order_info['refund_amount'] == '0.00' ) {
                    $operation_detail .= "<li><a target=\"_blank\" href=\"index.php?act=refund&op=go_refund&order_id={$order_info['order_id']}\">退款</a></li>";
                }
            }
            if( in_array($order_info['order_state'], array(40,30)) && $order_info['refund_state'] != '2' && $order_info['refund_amount'] == '0.00' ) {
                $operation_detail .= "<li><a target=\"_blank\" href=\"index.php?act=return&op=go_return&order_id={$order_info['order_id']}\">退货</a></li>";
            }
            if( in_array($order_info['order_state'],array(20,30,40))&&$order_info['send_sap'] == 0 ) {
                $operation_detail .= "<li><a target=\"_blank\" href=\"javascript:ajax_form('edit-bill-data','调整结算信息','index.php?act=order&op=bill_data&order_id={$order_info['order_id']}',1020,0)\">调整账单数据</a></li>";
            }
            if( in_array($order_info['order_state'],array(0,20,30,40))) {
                $operation_detail .= "<li><a target=\"_blank\" href=\"javascript:ajax_form('service-fee','调整服务费','index.php?act=order&op=service_fee&order_id={$order_info['order_id']}',1020,0)\">调整服务费</a></li>";
            }
            if( $order_info['order_from'] == '3'){
                $operation_detail .= "<li><a target=\"_blank\" href=\"javascript:ajax_form('edit-bill-data','分销订单罚款','index.php?act=order&op=punish&order_id={$order_info['order_id']}',1020,0)\">分销订单罚款</a></li>";
            }
            if( $order_info['order_from'] == '3' && $order_info['order_state'] == '20' ){
                $operation_detail .= "<li><a href=\"javascript:fg_cancel({$order_info['order_id']}, 1)\">分销订单取消</a></li>";
            	$operation_detail .= "<li><a target=\"_blank\" href=\"index.php?act=order&op=rc_order&order_id={$order_info['order_id']}\">分销订单重建</a></li>";
            }
            if ($operation_detail) {
                $list['operation'] .= "<span class='btn'><em><i class='fa fa-cog'></i>设置 <i class='arrow'></i></em><ul>{$operation_detail}</ul>";
            }
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
            $list['shipping_code'] = $order_info['shipping_code'];
            $list['refund_amount'] = ncPriceFormat($order_info['refund_amount']);
			$list['finnshed_time'] = !empty($order_info['finnshed_time']) ? date('Y-m-d H:i:s',$order_info['finnshed_time']) : '';
			$list['evaluation_state'] = str_replace(array(0,1,2), array('未评价','已评价','未评价'),$order_info['evaluation_state']);
			$list['store_id'] = $order_info['store_id'];
			$list['store_name'] = $order_info['store_name'];
			$list['buyer_id'] = $order_info['buyer_id'];
			$list['buyer_name'] = $order_info['buyer_name'];
            $list['send_sap'] = $order_info['send_sap'];
            $list['purchase_sap'] = $order_info['purchase_sap'];
			$list['fx_order_id'] = $order_info['fx_order_id'];
            $list['shipping_time']= $order_info['extend_order_common']['shipping_time']==0 ? '':date('Y-m-d H:i:s',$order_info['extend_order_common']['shipping_time']);
			$data['list'][$order_info['order_id']] = $list;
        }
        exit(Tpl::flexigridXML($data));
    }

    public function punishOp()
    {
        $ob_id = intval($_GET['ob_id']);
        $order_id = intval($_GET['order_id']);
        if($order_id <= 0 ){
            showMessage(L('miss_order_number'));
        }
        $model_order    = Model('order');
        $order_info = $model_order->getOrderInfo(array('order_id'=>$order_id),array('order_goods','order_common','store'));
        $order_list = array($order_id=>$order_info);
        $model_refund_return = Model('refund_return');
        $order_list = $model_refund_return->getGoodsRefundList($order_list,1);//订单商品的退款退货显示
        $order_info = $order_list[$order_id];

        foreach ($order_info['extend_order_goods'] as $value) {
            $value['image_60_url'] = cthumb($value['goods_image'], 60, $value['store_id']);
            $value['image_240_url'] = cthumb($value['goods_image'], 240, $value['store_id']);
            $value['goods_type_cn'] = orderGoodsType($value['goods_type']);
            $value['goods_url'] = urlShop('goods','index',array('goods_id'=>$value['goods_id']));
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
        $store_info = Model('store')->getStoreInfo(array('store_id'=>$order_info['store_id']));
        Tpl::output('store_info',$store_info);

        Tpl::output('ob_id',$ob_id);
        Tpl::output('order_info',$order_info);
        Tpl::setDirquna('shequ');
        Tpl::showpage('order.punish','null_layout');
    }

    public function add_punishOp()
    {
        $order_sn = $_POST['order_sn'];
        $cost_price = $_POST['cost_price'];
        $cost_remark = $_POST['cost_remark'];
        /** @var orderModel $orderModel */
        $orderModel = Model('order');
        $order = $orderModel->getOrderInfo(array('order_sn'=>$order_sn));
        if(empty($order)){
            exit(json_encode(array('state'=>false,'msg'=>'订单不存在')));
        }
        /** @var store_costModel $costModel */
        $costModel = Model('store_cost');
        /** @var sellerModel $sellerModel */
        $sellerModel = Model('seller');
        $seller = $sellerModel->getSellerInfo(array('store_id'=>$order['store_id']));
        $data = array(
            'order_sn'=>$order_sn,
            'cost_store_id'=>$order['store_id'],
            'fx_order_id'=>$order['fx_order_id'],
            'channel_id'=>$order['fx_order_id']?$order['buyer_id']:0,
            'channel_name'=>$order['fx_order_id']?$order['buyer_name']:'0',
            'cost_seller_id'=>$seller['seller_id'],
            'cost_price'=>$cost_price,
            'cost_remark'=>$cost_remark,
            'type'=>10,
            'cost_time'=>time(),
        );
        $res = $costModel->insert($data);
        if($res) exit(json_encode(array('state'=>true,'msg'=>'罚款添加成功')));
        exit(json_encode(array('state'=>false,'msg'=>'罚款添加失败')));
    }


    public function bill_dataOp()
    {

        $ob_id = intval($_GET['ob_id']);
        $order_id = intval($_GET['order_id']);
        if($order_id <= 0 ){
            showMessage(L('miss_order_number'));
        }
        $model_order    = Model('order');
        $order_info = $model_order->getOrderInfo(array('order_id'=>$order_id),array('order_goods','order_common','store'));
        $order_list = array($order_id=>$order_info);
        $model_refund_return = Model('refund_return');
        $order_list = $model_refund_return->getGoodsRefundList($order_list,1);//订单商品的退款退货显示
        $order_info = $order_list[$order_id];

        foreach ($order_info['extend_order_goods'] as $value) {
            $value['image_60_url'] = cthumb($value['goods_image'], 60, $value['store_id']);
            $value['image_240_url'] = cthumb($value['goods_image'], 240, $value['store_id']);
            $value['goods_type_cn'] = orderGoodsType($value['goods_type']);
            $value['goods_url'] = urlShop('goods','index',array('goods_id'=>$value['goods_id']));
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
        $store_info = Model('store')->getStoreInfo(array('store_id'=>$order_info['store_id']));
        Tpl::output('store_info',$store_info);

        Tpl::output('ob_id',$ob_id);
        Tpl::output('order_info',$order_info);
        Tpl::setDirquna('shequ');
        Tpl::showpage('order.bill','null_layout');
    }
    public function edit_bill_dataOp()
    {

        $ob_id = intval($_GET['ob_id']);
        $rec_id = intval($_GET['rec_id']);
        if($rec_id <= 0 ){
            showMessage('缺少记录ID');
        }
        /** @var orderModel $model_order */
        $model_order    = Model('order');
        $orderGoodsInfo = $model_order->getOrderGoodsInfo(array('rec_id'=>$rec_id));
        $order_id = $orderGoodsInfo['order_id'];
        $order_info = $model_order->getOrderInfo(array('order_id'=>$order_id),array('order_goods','order_common','store'));
        //取得订单其它扩展信息
        $model_order->getOrderExtendInfo($order_info);
        $oldValue = array(
            'goods_pay_price'=>$orderGoodsInfo['goods_pay_price'],
            'goods_cost'=>$orderGoodsInfo['goods_cost'],
            'commis_rate'=>$orderGoodsInfo['commis_rate'],
            'rpt_bill'=>$orderGoodsInfo['rpt_bill'],
        );

        if(1 == intval($_POST['opinion'])){
            $newValue = array();
            if(''!==$_POST['goods_pay_price']) $newValue['goods_pay_price'] = $_POST['goods_pay_price'];
            if(''!==$_POST['goods_cost']&&$order_info['manage_type']=='co_construct') $newValue['goods_cost'] = $_POST['goods_cost'];
            if(''!==$_POST['commis_rate']&&$order_info['manage_type']=='platform') $newValue['commis_rate'] = $_POST['commis_rate'];
            if(''!==$_POST['rpt_bill']) $newValue['rpt_bill'] = $_POST['rpt_bill'];
            /** @var WorkflowService $service */
            $service = Service('Workflow');
            $service->init(null,$this->admin_info['name'],$this->admin_info['gname']);
            try{
                $res =$service->launch($service::TYPE_ORDER_BILL_EDIT,$rec_id,$newValue,$oldValue);
            }catch (Exception $e){
                die(JSON(array('state'=>false,'msg'=>$e->getMessage())));
            }
            if($res){
                die(JSON(array('state'=>true)));
            }
            die(JSON(array('state'=>false,'msg'=>'提交失败')));
        }

        //商家信息
        $store_info = Model('store')->getStoreInfo(array('store_id'=>$order_info['store_id']));
        Tpl::output('store_info',$store_info);

        Tpl::output('ob_id',$ob_id);
        Tpl::output('order_info',$order_info);
        Tpl::output('order_goods_info',$orderGoodsInfo);


        /** @var WorkflowService $service */
        $service = Service('Workflow');
        $service->init(array('type'=>$service::TYPE_ORDER_BILL_EDIT,'new_value'=>$oldValue),$this->admin_info['name'],$this->admin_info['gname']);
        try{
            $form = $service->getForm(true);
        }catch (Exception $e){
            $form = '您所在的用户组不允许调整订单数据！';
        }
        Tpl::output('form' , $form);
        Tpl::output('view' , $service->getView());
        Tpl::output('attributes' , array_under_reset($service->getAttributes(),'name'));

        Tpl::setDirquna('shequ');
        Tpl::showpage('order.bill.edit');
    }


    /**
     * 平台订单状态操作
     *
     */
    public function change_stateOp() {
        $order_id = intval($_GET['order_id']);
        $is_fx = intval($_GET['is_fx']);
        if($order_id <= 0){
            showMessage(L('miss_order_number'),$_POST['ref_url'],'html','error');
        }
        $model_order = Model('order');

        //获取订单详细
        $condition = array();
        $condition['order_id'] = $order_id;
        $order_info = $model_order->getOrderInfo($condition);

        //取得其它订单类型的信息
        $model_order->getOrderExtendInfo($order_info);

        if ($_GET['state_type'] == 'cancel') {
            $result = $this->_order_cancel($order_info, $is_fx);
        } elseif ($_GET['state_type'] == 'receive_pay') {
            $result = $this->_order_receive_pay($order_info,$_POST);
        } elseif ($_GET['state_type'] == 'jicai_pay') {
        	$result = $this->_jicai_pay($order_info,$_POST);
        } elseif ($_GET['state_type'] == 'b2b_pay') {
        	$result = $this->_b2b_pay($order_info);
        } elseif ($_GET['state_type'] == 'b2b_finish') {
        	$result = $this->_b2b_finish($order_info);
        }
        if (!$result['state']) {
            showMessage($result['msg'],$_POST['ref_url'],'html','error');
        } else {
            showMessage($result['msg'],$_POST['ref_url']);
        }
    }
    public function add_trade_snOp() {
        $order_id = intval($_GET['order_id']);
        if($order_id <= 0){
            showMessage(L('miss_order_number'),$_POST['ref_url'],'html','error');
        }
        /** @var orderModel $model_order */
        $model_order = Model('order');

        //获取订单详细
        $condition = array();
        $condition['order_id'] = $order_id;
        $order_info = $model_order->getOrderInfo($condition);

        //取得其它订单类型的信息
        $model_order->getOrderExtendInfo($order_info);

        //订单商品
        $goods_list = $model_order -> getOrderGoodsList (
            array('order_id' => $order_id)
        ) ;

        if (!chksubmit()) {
            Tpl::output('order_info',$order_info);
            Tpl::output('goods_list',$goods_list);

            Tpl::setDirquna('shequ');
            Tpl::showpage('order.add_trade_sn');
            exit();
        }

        $model_order->beginTransaction();
        try {
            $res = $model_order -> editOrder(
                array('trade_no'=>$_POST['trade_no']),
                array('order_id' => $order_id)
            ) ;
            if( !$res ) {
                throw new Exception( '补充订单流水号失败' );
            }

            $model_order->commit();
        } catch (Exception $e) {
            $model_order->rollback();
            $_POST['ref_url'] = 'index.php?act=order&op=add_trade_sn&order_id='.$order_id;
            showMessage($e->getMessage(),$_POST['ref_url'],'html','error');
        }

        $result = array() ;
        $result['state'] = true ;
        $result['msg'] = '补充流水单号成功' ;

        if (!$result['state']) {
            showMessage($result['msg'],$_POST['ref_url'],'html','error');
        } else {
            showMessage($result['msg'],$_POST['ref_url']);
        }
    }

    /**
     * 系统取消订单
     */
    private function _order_cancel($order_info, $is_fx=0) {
        $order_id = $order_info['order_id'];
        $model_order = Model('order');
        $logic_order = Logic('order');
        $if_allow = $model_order->getOrderOperateState('system_cancel',$order_info);
        if (!$if_allow) {
            return callback(false,'无权操作');
        }
        if (TIMESTAMP - 86400 < $order_info['api_pay_time']) {
            $_hour = ceil(($order_info['api_pay_time']+86400-TIMESTAMP)/3600);
            exit(json_encode(array('state'=>false,'msg'=>'该订单曾尝试使用第三方支付平台支付，须在'.$_hour.'小时以后才可取消')));
        }
        if ($order_info['order_type'] == 2) {
            //预定订单
            $result = Logic('order_book')->changeOrderStateCancel($order_info, 'admin', $this->admin_info['name']);
        } else {
            $cancel_condition = array();
            if ($order_info['payment_code'] != 'offline') {
                $cancel_condition['order_state'] = ORDER_STATE_NEW;
            }
            if($order_info['payment_code'] == 'fenxiao'){
                $cancel_condition['order_state'] = ORDER_STATE_PAY;
            }
            $result =  $logic_order->changeOrderStateCancel($order_info,'admin', $this->admin_info['name'],'',true,$cancel_condition, $is_fx);
        }
        if ($result['state']) {
            $this->log(L('order_log_cancel').','.L('order_number').':'.$order_info['order_sn'],1);
        }
        if ($result['state']) {
            exit(json_encode(array('state'=>true,'msg'=>'取消成功')));
        } else {
            exit(json_encode(array('state'=>false,'msg'=>'取消失败')));
        }
    }

    /**
     * 系统收到货款
     * @throws Exception
     */
    private function _order_receive_pay($order_info, $post) {
        $order_id = $order_info['order_id'];
        $model_order = Model('order');
        $logic_order = Logic('order');
        $order_info['if_system_receive_pay'] = $model_order->getOrderOperateState('system_receive_pay',$order_info);

        if (!$order_info['if_system_receive_pay']) {
            return callback(false,'无权操作');
        }

        if (!chksubmit()) {
            Tpl::output('order_info',$order_info);
            //显示支付接口列表
            $payment_list = Model('payment')->getPaymentOpenList();
            //去掉预存款和货到付款
            foreach ($payment_list as $key => $value){
                if ($value['payment_code'] == 'predeposit' || $value['payment_code'] == 'offline') {
                   unset($payment_list[$key]);
                }
            }
            Tpl::output('payment_list',$payment_list);
			Tpl::setDirquna('shequ');
            Tpl::showpage('order.receive_pay');
            exit();
        }
        //预定支付尾款时需要用到已经支付的状态
        $order_list = $model_order->getOrderList(array('pay_sn'=>$order_info['pay_sn'],'order_state'=>array('in',array(ORDER_STATE_NEW,ORDER_STATE_PAY))));

        //取订单其它扩展信息
        $result = Logic('payment')->getOrderExtendList($order_list,'admin');
        if (!$result['state']) {
            return $result;
        }
        $result = $logic_order->changeOrderReceivePay($order_list,'admin',$this->admin_info['name'],$post);
        if ($result['state']) {
            $this->log('将订单改为已收款状态,'.L('order_number').':'.$order_info['order_sn'],1);
            //记录消费日志
            $api_pay_amount = $order_info['order_amount'] - $order_info['pd_amount'] - $order_info['rcb_amount'];
            QueueClient::push('addConsume', array('member_id'=>$order_info['buyer_id'],'member_name'=>$order_info['buyer_name'],
            'consume_amount'=>$api_pay_amount,'consume_time'=>TIMESTAMP,'consume_remark'=>'管理员更改订单为已收款状态，订单号：'.$order_info['order_sn']));
        }
        return $result;
    }
    /**
     * B2B订单收到货款
     */
    private function _b2b_pay($order_info) {
    	$order_id = $order_info['order_id'];
    	$model_order = Model('order');
    	$logic_order = Logic('order');
    	$order_info['if_system_receive_pay'] = $model_order->getOrderOperateState('system_receive_pay',$order_info);
    	
    	if (!$order_info['if_system_receive_pay']) {
    		return callback(false,'无权操作');
    	}
    	
    	$res = Model('order') -> editOrder(
    			array( 'order_state' => ORDER_STATE_PAY, 'payment_time' => time() ),
    			array( 'order_id' => $order_id )
    			) ;
    	
    	$result = array() ;
    	$result['state'] = $res ? true : false ;
    	 
    	if ($result['state']) {
    		$this->log('将订单改为已收款状态,'.L('order_number').':'.$order_info['order_sn'],1);
    		//记录消费日志
    		$api_pay_amount = $order_info['order_amount'] - $order_info['pd_amount'] - $order_info['rcb_amount'];
    		QueueClient::push('addConsume', array('member_id'=>$order_info['buyer_id'],'member_name'=>$order_info['buyer_name'],
    				'consume_amount'=>$api_pay_amount,'consume_time'=>TIMESTAMP,'consume_remark'=>'管理员更改订单为已收款状态，订单号：'.$order_info['order_sn']));
    	}
    	
    	if ($result['state']) {
            exit(json_encode(array('state'=>true,'msg'=>'b2b付款成功')));
        } else {
            exit(json_encode(array('state'=>false,'msg'=>'b2b付款失败')));
        }
    }
    /**
     * B2B订单确认完成
     */
    private function _b2b_finish($order_info) {
    	$order_id = $order_info['order_id'];
    	$model_order = Model('order');
    	$logic_order = Logic('order');
    	$order_info['if_system_receive_pay'] = $model_order->getOrderOperateState('system_receive_pay',$order_info);
    	 
    	if (!$order_info['if_system_receive_pay']) {
    		return callback(false,'无权操作');
    	}
    	 
    	$admin = unserialize(decrypt(cookie('sys_key'),MD5_KEY));
    	$res = Logic('order')->changeOrderStateReceive($order_info,'admin',$admin['name'],'超期未收货系统自动完成订单');
    	 
    	$result = array() ;
    	$result['state'] = $res ? true : false ;
    
    	if ($result['state']) {
    		$this->log('将订单改为确认已完成状态,'.L('order_number').':'.$order_info['order_sn'],1);
    		//记录消费日志
    		$api_pay_amount = $order_info['order_amount'] - $order_info['pd_amount'] - $order_info['rcb_amount'];
    		QueueClient::push('addConsume', array('member_id'=>$order_info['buyer_id'],'member_name'=>$order_info['buyer_name'],
    				'consume_amount'=>$api_pay_amount,'consume_time'=>TIMESTAMP,'consume_remark'=>'管理员更改订单为已收款状态，订单号：'.$order_info['order_sn']));
    	}
    	 
    	if ($result['state']) {
    		exit(json_encode(array('state'=>true,'msg'=>'B2B订单确认完成成功')));
    	} else {
    		exit(json_encode(array('state'=>false,'msg'=>'B2B订单确认完成失败')));
    	}
    }
    /**
     * 集采订单收到货款
     * @throws Exception
     */
    private function _jicai_pay($order_info,$post) {
    	$order_id = $order_info['order_id'];
    	$model_order = Model('order');
    	$logic_order = Logic('order');
    	$order_info['if_system_receive_pay'] = $model_order->getOrderOperateState('system_receive_pay',$order_info);
    
    	if (!$order_info['if_system_receive_pay']) {
    		return callback(false,'无权操作');
    	}
    	
    	//订单商品
    	$goods_list = Model('order') -> getOrderGoodsList (
		    				array('order_id' => $order_id)
		    			) ;
    	
    	if (!chksubmit()) {
    		Tpl::output('order_info',$order_info);
    		Tpl::output('goods_list',$goods_list);

    		Tpl::setDirquna('shequ');
    		Tpl::showpage('order.jicai_pay');
    		exit();
    	}
    	
    	// foreach ($_POST as $key => $val) {
    	// 	if ( strpos($key, 'jicai_price') !== false ) {
    	// 		$goods_id = str_replace('jicai_price', '', $key) ;
    	// 		$jicaiArr[$goods_id] = $val ;
    	// 	}
    	// }
    	$model_order->beginTransaction();
    	try {
	    	//$diff = 0 ;
            $cost_amount = 0;//订单总成本
            $goods_amount = 0;
            foreach ($goods_list as $goods) {
	    		$goods_id = $goods['goods_id'];
	    		$jicai_price = $_POST['jicai_price'][$goods_id] ;
	    		$price = $goods['goods_price'] ;
	    		$new_payAmount = $jicai_price * $goods['goods_num'];
	    		//$diff += ( $price - $jicai_price ) * $goods['goods_num'] ;
                $goods_amount += $jicai_price * $goods['goods_num'] ;
                // $new_goods_cost = $_POST['goods_cost'][$goods_id] * $goods['goods_num'];
                // $cost_amount += $new_goods_cost;

	    		$res = $model_order->editOrderGoods(
	    					array('goods_price' => $jicai_price, 'goods_pay_price' => $new_payAmount),
	    					array('rec_id'=>$goods['rec_id'])
	    				);
	    		if( !$res ) {
	    			throw new Exception( '更新订单商品失败' );
	    		}
	    	}
            $diff = $order_info['goods_amount'] - $goods_amount;
	    	 
	    	$new_orderAmount = $order_info['order_amount'] - $diff ;
	    	
	    	$res = $model_order -> editOrder( 
	    				array('order_state' => ORDER_STATE_PAY, 'payment_time' => time(), 'order_amount' => ncPriceFormat($new_orderAmount),'trade_no'=>$post['trade_no']),
                        // array('order_state' => ORDER_STATE_PAY, 'payment_time' => time(), 'order_amount' => ncPriceFormat($new_orderAmount), 'cost_amount' => $cost_amount),
	    				array('order_id' => $order_id)
	    			) ;
	    	if( !$res ) {
	    		throw new Exception( '更新订单金额失败' );
	    	}
	    	
	    	$model_order->commit();
    	} catch (Exception $e) {
            $model_order->rollback();
            $_POST['ref_url'] = 'index.php?act=order&op=change_state&state_type=jicai_pay&order_id='.$order_id;
            $result = array() ;
	    	$result['state'] = false ;
	    	$result['msg'] = $e->getMessage() ;
	    	return $result ;
        }
        
    	$result = array() ;
    	$result['state'] = true ;
    	$result['msg'] = '修改付款成功' ;
    	
    	if ($result['state']) {
    		$this->log('将订单改为已收款状态,'.L('order_number').':'.$order_info['order_sn'],1);
    		//记录消费日志
    		$api_pay_amount = $order_info['order_amount'] - $order_info['pd_amount'] - $order_info['rcb_amount'];
    		QueueClient::push('addConsume', array('member_id'=>$order_info['buyer_id'],'member_name'=>$order_info['buyer_name'],
    				'consume_amount'=>$api_pay_amount,'consume_time'=>TIMESTAMP,'consume_remark'=>'管理员更改订单为已收款状态，订单号：'.$order_info['order_sn']));
    	}

    	return $result ;
    }
    
    /**
     * ajax计算集采单修改单价后的订单总额
     */
    public function jicai_totalOp()
    {
    	$order_id = intval($_GET['order_id']);

    	if(!$order_id) die(json_encode(array('status'=>'0','msg'=>'no orderid'))) ;

    	$condition = array('order_id' => $order_id);
    	$order_info = Model('order') -> getOrderInfo($condition, array('order_goods') );
    	// foreach ($_GET as $key => $val) {
    	// 	if ( strpos($key, 'jicai_price') !== false ) {
    	// 		$goods_id = str_replace('jicai_price', '', $key) ;
    	// 		$jicaiArr[$goods_id] = $val ;
    	// 	}
    	// }
    	
    	$diff = 0 ;
    	$goods_amount = 0;
    	foreach ($order_info['extend_order_goods'] as $goods) {
    		$goods_id = $goods['goods_id'];
    		$jicai_price = $_GET['jicai_price'][$goods_id] ;
            $goods_amount += $jicai_price * $goods['goods_num'] ;
    	}
    	$diff = $order_info['goods_amount'] - $goods_amount;
    	$new_orderAmount = $order_info['order_amount'] - $diff ;
    	die(json_encode(array('status'=>'1','msg'=>$new_orderAmount))) ;
    }
    
    /**
     * 集采订单收到货款
     * @throws Exception
     */
    private function _jicai_ship($order_info) {
    	$order_id = $order_info['order_id'];
    	$model_order = Model('order');
    	//$logic_order = Logic('order');
    	$order_info['if_system_receive_pay'] = $model_order->getOrderOperateState('system_receive_pay',$order_info);
    
    	if (!$order_info['if_system_receive_pay']) {
    		return callback(false,'无权操作');
    	}
    
    	$res = $model_order -> editOrder(
    			array('order_state' => ORDER_STATE_SEND),
    			array('order_id' => $order_id)
    			) ;
    	$result = array() ;
    	$result['state'] = $res ? true : false ;
    	$result['msg'] = $res ? '修改发货成功' : '修改发货失败' ;
    	 
    	if ($result['state']) {
    		$this->log('将订单改为已发货状态,'.L('order_number').':'.$order_info['order_sn'],1);
    	}
    	return $result;
    }

    /**
     * 查看订单
     *
     */
    public function show_orderOp(){
        $order_id = intval($_GET['order_id']);
        /** @var orderModel $model_order */
        $model_order    = Model('order');
        if($order_id <= 0 ){
            $rec_id = intval($_GET['rec_id']);
            if($rec_id<=0){
                showMessage(L('miss_order_number'));
            }else{
                $orderGoodsInfo = $model_order->getOrderGoodsInfo(array('rec_id'=>$rec_id));
                if(!empty($orderGoodsInfo)) $order_id = $orderGoodsInfo['order_id'];
                else showMessage(L('miss_order_number'));
            }

        }
        
        $order_info = $model_order->getOrderInfo(array('order_id'=>$order_id),array('order_goods','order_common','store'));
        $order_list = array($order_id=>$order_info);
        $model_refund_return = Model('refund_return');
        $order_list = $model_refund_return->getGoodsRefundList($order_list,1);//订单商品的退款退货显示
        $order_info = $order_list[$order_id];
        
        /** 客服记录日志  **/
        if( isset($_POST['addlog']) && $_POST['addlog'] == 1 ){
        	if( empty($_POST['log_msg']) ) {
        		showMessage('日志信息不能为空');
        		exit;
        	}
        	$log = array();
        	$log['order_id'] = $order_id;
        	$log['log_msg'] = trim($_POST['log_msg']);
        	$log['log_time'] = time();
        	$log['log_role'] = 'admin';
        	$log['log_user'] = $this->admin_info['name'];
        	$log['log_orderstate'] = $order_info['order_state'];
        	$model_order->addOrderLog($log);
        	showMessage('日志保存成功');
        	exit;
        }

		/** 客服记录日志  **/
        if( isset($_POST['addlog']) && $_POST['addlog'] == 1 ){
        	if( empty($_POST['log_msg']) ) {
        		showMessage('日志信息不能为空');
        		exit;
        	}
        	$log = array();
        	$log['order_id'] = $order_id;
        	$log['log_msg'] = trim($_POST['log_msg']);
        	$log['log_time'] = time();
        	$log['log_role'] = 'admin';
        	$log['log_user'] = $this->admin_info['name'];
        	$log['log_orderstate'] = $order_info['order_state'];
        	$model_order->addOrderLog($log);
        	showMessage('日志保存成功');
        	exit;
        }
		
        $refund_all = $order_info['refund_list'][0];
        if (!empty($refund_all) && $refund_all['seller_state'] < 3) {//订单全部退款商家审核状态:1为待审核,2为同意,3为不同意
        	$order_info['refund_all'] = $refund_all;
        }
		
        
        $order_info = getOrderGoodsRefundStep( $order_info ) ;

        foreach ($order_info['extend_order_goods'] as $value) {
            $value['image_60_url'] = cthumb($value['goods_image'], 60, $value['store_id']);
            $value['image_240_url'] = cthumb($value['goods_image'], 240, $value['store_id']);
            $value['goods_type_cn'] = orderGoodsType($value['goods_type']);
            $value['goods_url'] = urlShop('goods','index',array('goods_id'=>$value['goods_id']));
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

        //订单变更日志
        $log_list   = $model_order->getOrderLogList(array('order_id'=>$order_info['order_id']));
        Tpl::output('order_log',$log_list);

        //退款退货信息
        $model_refund = Model('refund_return');
        $condition = array();
        $condition['order_id'] = $order_info['order_id'];
        $condition['seller_state'] = 2;
        $condition['admin_time'] = array('gt',0);
        $return_list = $model_refund->getReturnList($condition);
        Tpl::output('return_list',$return_list);

        //退款信息
        $refund_list = $model_refund->getRefundList($condition);
        Tpl::output('refund_list',$refund_list);

        //商家信息
        $store_info = Model('store')->getStoreInfo(array('store_id'=>$order_info['store_id']));
        Tpl::output('store_info',$store_info);

        /** @var shequ_tuanModel $tuanModel */
        $tuanModel = Model('shequ_tuan');
        $tuan_info = $tuanModel->getOne(array('config_id'=>$order_info['shequ_tuan_id'],'tz_id'=>$order_info['shequ_tz_id']));
        Tpl::output('tuan_info',$tuan_info);
        /** @var shequ_tuanzhangModel $tuanzhangModel */
        $tuanzhangModel = Model('shequ_tuanzhang');
        $tuanzhang_info = $tuanzhangModel->getOne(array('id'=>$order_info['shequ_tz_id']));
        Tpl::output('tuanzhang_info',$tuanzhang_info);
        /** @var shequ_tuan_configModel $tuan_configModel */
        $tuan_configModel = Model('shequ_tuan_config');
        $tuan_config_info = $tuan_configModel->getTuanConfigInfo(array('config_tuan_id'=>$order_info['shequ_tuan_id']));
        Tpl::output('tuan_config_info',$tuan_config_info);

        $tuan_address_info = array();
        if ($tuan_info['address_id']>0){
            /** @var shequ_addressModel $shequ_addressModel */
            $shequ_addressModel = Model('shequ_address');
            $tuan_address_info = $shequ_addressModel->getOne(array('id'=>$tuan_info['address_id']));
        }
        Tpl::output('tuan_address_info',$tuan_address_info);

        //商家发货信息
        if (!empty($order_info['extend_order_common']['daddress_id'])) {
            $daddress_info = Model('daddress')->getAddressInfo(array('address_id'=>$order_info['extend_order_common']['daddress_id']));
            Tpl::output('daddress_info',$daddress_info);
        }

        //显示快递信息
        if ($order_info['shipping_code'] != '') {
            $express = rkcache('express',true);
            $order_info['express_info']['e_code'] = $express[$order_info['extend_order_common']['shipping_express_id']]['e_code'];
            $order_info['express_info']['e_name'] = $express[$order_info['extend_order_common']['shipping_express_id']]['e_name'];
            $order_info['express_info']['e_url'] = $express[$order_info['extend_order_common']['shipping_express_id']]['e_url'];
            if ($order_info['order_from'] != 3) {
                $shipping_code = trim($order_info['shipping_code']);
                if (strpos($shipping_code, ',') !== false) {
                    $shipping_code2 = explode(',', $shipping_code);
                    if (!empty($shipping_code2) && isset($shipping_code2[0])) {
                        $shipping_code = $shipping_code2[0];
                    }
                }
                $order_info['express_info']['e_info'] = Model('express')->get_express($order_info['express_info']['e_code'],$shipping_code);
            }
        }

        //如果订单已取消，取得取消原因、时间，操作人
        if ($order_info['order_state'] == ORDER_STATE_CANCEL) {
            $order_info['close_info'] = $model_order->getOrderLogInfo(array('order_id'=>$order_info['order_id'],'log_orderstate'=>ORDER_STATE_CANCEL),'log_id desc');
        }

        //如果订单已支付，取支付日志信息(主要是第三方平台支付单号)
        if ($order_info['order_state'] == ORDER_STATE_PAY) {
            $order_info['pay_info'] = $model_order->getOrderLogInfo(array('order_id'=>$order_info['order_id'],'log_orderstate'=>ORDER_STATE_PAY),'log_id desc');
        }
        Tpl::output('order_info',$order_info);
		Tpl::setDirquna('shequ');
        Tpl::showpage('order.view');
    }

    /**
     * 导出
     *
     */
    public function export_step1Op(){
        $lang   = Language::getLangContent();
        error_reporting(E_ALL);
        set_time_limit(1800);
        //ini_set('memory_limit','4G');
        /** @var orderModel $model_order */
        $model_order = Model('order');
        $condition  = array();
        if (preg_match('/^[\d,]+$/', $_GET['order_id'])) {
            $_GET['order_id'] = explode(',',trim($_GET['order_id'],','));
            $condition['order_id'] = array('in',$_GET['order_id']);
        }
        $this->_get_condition($condition);
        $sort_fields = array('buyer_name','store_name','order_id','payment_code','order_state','order_amount','order_from','pay_sn','rcb_amount','pd_amount','payment_time','finnshed_time','evaluation_state','refund_amount','buyer_id','store_id');
        if ($_POST['sortorder'] != '' && in_array($_POST['sortname'],$sort_fields)) {
            $order = $_POST['sortname'].' '.$_POST['sortorder'];
        } else {
            $order = 'order_id desc';
        }

        $export_size = 5000;
        $extend_tables = array('order_common','order_goods');

        /*if (!empty($_GET['order_id']) || !empty($_GET['export_goods'])) {
            $extend_tables = array('order_common','order_goods');
        } else {
            $extend_tables = array('order_common');
        }*/
        if (!is_numeric($_GET['curpage'])){
            $count = $model_order->getOrderCount($condition);
            $array = array();

            if ($count > $export_size ){   //显示下载链接
                $page = ceil($count/$export_size);
                for ($i=1;$i<=$page;$i++){
                    $limit1 = ($i-1)*$export_size + 1;
                    $limit2 = $i*$export_size > $count ? $count : $i*$export_size;
                    $array[$i] = $limit1.' ~ '.$limit2 ;
                }
                Tpl::output('list',$array);
                Tpl::output('murl','index.php?act=order&op=index');

                Tpl::setDirquna('shequ');
                Tpl::showpage('export.excel');
            }else{  //如果数量小，直接下载
                $data = $model_order->getOrderList($condition,'','*',$order,$export_size, $extend_tables);
                $this->createExcel($data);
            }
        }else{  //下载
            $limit1 = ($_GET['curpage']-1) * $export_size;
            $limit2 = $export_size;
            $data = $model_order->getOrderList($condition,'','*',$order,"{$limit1},{$limit2}", $extend_tables);
            $this->createExcel($data);
        }
    }

    /**
     * 生成excel
     *
     * @param array $data
     */
    private function createExcel($data = array()){
        Language::read('export');
        import('libraries.excel');
        $express_list = Model('express')->getExpressList();
        $order_ids = array_column($data, 'order_id');
        $refund_list = Model('refund_return')->getRefundReturnList2(array('order_id' => array('in', $order_ids)));
        $refund_list = array_under_reset($refund_list, 'order_id');

        $excel_obj = new Excel();
        $excel_data = array();
        //设置样式
        $excel_obj->setStyle(array('id'=>'s_title','Font'=>array('FontName'=>'宋体','Size'=>'12','Bold'=>'1')));
        //header
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'订单编号');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'分销订单号');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'订单来源');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'下单时间');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'订单金额(元)');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'订单状态');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'是否退款');
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
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'买家姓名');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'买家手机');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'买家地址');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'发货物流单号');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'物流公司');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'发货时间');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'发货备注');

        //扩展商品信息导出
        $first = current($data);
        //if (!empty($first['extend_order_goods'])) {
            $excel_data[0][] = array('styleid'=>'s_title','data'=>'商品ID(注:多个间以,分隔)');
            $excel_data[0][] = array('styleid'=>'s_title','data'=>'商品名');
            $excel_data[0][] = array('styleid'=>'s_title','data'=>'商品单价');
            $excel_data[0][] = array('styleid'=>'s_title','data'=>'商品成本');
            $excel_data[0][] = array('styleid'=>'s_title','data'=>'商品价格');
            $excel_data[0][] = array('styleid'=>'s_title','data'=>'商品数量');
            $excel_data[0][] = array('styleid'=>'s_title','data'=>'平台分佣百分比');
            $excel_data[0][] = array('styleid'=>'s_title','data'=>'商品税率');
        //}

        //data
        foreach ((array)$data as $k=>$order_info){
            $order_info['state_desc'] = orderState($order_info);
            $list = array();
            $orderGoods = array();
            $list['order_sn'] = $order_info['order_sn'].str_replace(array(1,2,3), array(null,' [预定]','[门店自提]'), $order_info['order_type']);
            $list['fx_order_id'] = $order_info['fx_order_id'];
            $list['order_from'] = orderFrom($order_info['order_from'], $order_info['buyer_name']);
            $list['add_time'] = date('Y-m-d H:i:s',$order_info['add_time']);
            $list['order_amount'] = ncPriceFormat($order_info['order_amount']);
            if ($order_info['shipping_fee']) {
                $list['order_amount'] .= '(含运费'.ncPriceFormat($order_info['shipping_fee']).')';
            }
            $list['order_state'] = $order_info['state_desc'];
            $list['has_refund'] = '';
            if (isset($refund_list[$order_info['order_id']]) && !empty($refund_list[$order_info['order_id']])) {
                $list['has_refund'] = '退款';
                $refund = $refund_list[$order_info['order_id']];
                if ($refund['seller_state'] == 1) {
                    $list['has_refund'] .= '（商家待审核）';
                } elseif ($refund['seller_state'] == 2) {
                    $list['has_refund'] .= '（商家已同意）';
                } else {
                    $list['has_refund'] .= '（商家已拒绝）';
                }
            }
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
            $list['reciver_name'] = $order_info['extend_order_common']['reciver_name'];
            $list['buyer_phone'] = $order_info['extend_order_common']['reciver_info']['phone'];
            $list['address'] = htmlspecialchars($order_info['extend_order_common']['reciver_info']['address']);
            $list['shipping_code'] = $order_info['shipping_code'];
            $list['shipping_express_name'] = $order_info['extend_order_common']['shipping_express_id'] ? $express_list[$order_info['extend_order_common']['shipping_express_id']]['e_name'] : '';
            $list['shipping_time'] = empty($order_info['extend_order_common']['shipping_time']) ? '' : date('Y-m-d H:i:s',$order_info['extend_order_common']['shipping_time']);
            $list['deliver_explain'] = $order_info['extend_order_common']['deliver_explain'];
            //扩展商品信息导出
            if (!empty($order_info['extend_order_goods'])) {
                foreach ($order_info['extend_order_goods'] as $key => $value) {
                    $goodsItem = array();
                    /*$list['goods_id'] = $value['goods_id'] . ',';
                    $list['goods_name'] = $value['goods_name'] . ',';
                    $list['goods_price'] = $value['goods_price'] . ',';
                    $list['goods_pay_price'] = $value['goods_pay_price'] . ',';
                    $list['goods_num'] = $value['goods_num'] . ',';
                    $list['commis_rate'] = $value['commis_rate'] . ',';
                    $list['tax_input'] = '200.000' == $value['tax_input'] ? 0 : $value['tax_input'] . ',';*/
                    $goodsItem['goods_id'] = $value['goods_id'];
                    $goodsItem['goods_name'] = $value['goods_name'];
                    $goodsItem['goods_price'] = $value['goods_price'];
                    $goodsItem['goods_cost'] = $value['goods_cost'];
                    $goodsItem['goods_pay_price'] = $value['goods_pay_price'];
                    $goodsItem['goods_num'] = $value['goods_num'];
                    $goodsItem['commis_rate'] = $value['commis_rate'];
                    $goodsItem['tax_input'] = '200.000' == $value['tax_input'] ? 0 : $value['tax_input'];
                    $orderGoods[] = $goodsItem;
                }
                /*$list['goods_id'] = rtrim($list['goods_id'], ',');
                $list['goods_name'] = rtrim($list['goods_name'], ',');
                $list['goods_price'] = rtrim($list['goods_price'], ',');
                $list['goods_pay_price'] = rtrim($list['goods_pay_price'], ',');
                $list['goods_num'] = rtrim($list['goods_num'], ',');;
                $list['commis_rate'] = rtrim($list['commis_rate'], ',');
                $list['tax_input'] = rtrim($list['tax_input'], ',');*/
            }else{
                $orderGoods[] = array(
                    'goods_id'=>'',
                    'goods_name'=>'',
                    'goods_price'=>'',
                    'goods_pay_price'=>'',
                    'goods_num'=>'',
                    'commis_rate'=>'',
                    'tax_input'=>'',
                );
            }

            foreach ($orderGoods as $key => $goodsItem) {
                $tmp = array();
                if ($key > 0) {
                    $tmp[] = $tmp[] = $tmp[] = $tmp[] = $tmp[] = $tmp[] = $tmp[] = $tmp[] = $tmp[] = $tmp[] = $tmp[] = $tmp[] = $tmp[] = $tmp[] = $tmp[] = $tmp[] = $tmp[] = $tmp[] = $tmp[] = $tmp[] = $tmp[] = $tmp[] = $tmp[] = $tmp[] = $tmp[] = $tmp[] = $tmp[] = $tmp[] = '';
                }else{
                    $tmp[] = array('data' => $list['order_sn']);
                    $tmp[] = array('data' => $list['fx_order_id']);
                    $tmp[] = array('data' => $list['order_from']);
                    $tmp[] = array('data' => $list['add_time']);
                    $tmp[] = array('data' => $list['order_amount']);
                    $tmp[] = array('data' => $list['order_state']);
                    $tmp[] = array('data' => $list['has_refund']);
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
                    $tmp[] = array('data' => str_replace('>', '', $list['reciver_name']));
                    $tmp[] = array('data' => $list['buyer_phone']);
                    $tmp[] = array('data' => $list['address']);
                    $tmp[] = array('data' => $list['shipping_code']);
                    $tmp[] = array('data' => $list['shipping_express_name']);
                    $tmp[] = array('data' => $list['shipping_time']);
                    $tmp[] = array('data' => $list['deliver_explain']);
                }
                $tmp[] = array('data'=>$goodsItem['goods_id']);
                $tmp[] = array('data'=>$goodsItem['goods_name']);
                $tmp[] = array('data'=>$goodsItem['goods_price']);
                $tmp[] = array('data'=>$goodsItem['goods_cost']);
                $tmp[] = array('data'=>$goodsItem['goods_pay_price']);
                $tmp[] = array('data'=>$goodsItem['goods_num']);
                $tmp[] = array('data'=>$goodsItem['commis_rate']);
                $tmp[] = array('data'=>$goodsItem['tax_input']);
                $excel_data[] = $tmp;
            }
            /*if (!empty($first['extend_order_goods'])) {
                $tmp[] = array('data'=>$list['goods_id']);
                $tmp[] = array('data'=>$list['goods_name']);
                $tmp[] = array('data'=>$list['goods_price']);
                $tmp[] = array('data'=>$list['goods_pay_price']);
                $tmp[] = array('data'=>$list['goods_num']);
                $tmp[] = array('data'=>$list['commis_rate']);
                $tmp[] = array('data'=>$list['tax_input']);
            }*/
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
         if(isset($_REQUEST['shequ_tuan_id']) && ($_REQUEST['shequ_tuan_id'] != '')){
             $condition['shequ_tuan_id'] = $_REQUEST['shequ_tuan_id'];
         }
        if(isset($_REQUEST['shequ_tz_id']) && ($_REQUEST['shequ_tz_id'] != '')){
            $condition['shequ_tz_id'] = $_REQUEST['shequ_tz_id'];
        }
        if ($_REQUEST['query'] != '' && in_array($_REQUEST['qtype'],array('name','phone'))) {
            /** @var shequ_tuanzhangModel $shequ_tuanzhangModel */
            $shequ_tuanzhangModel = Model('shequ_tuanzhang');
            if($_REQUEST['qtype']=='name'){
                $tuangzhang = $shequ_tuanzhangModel->getOne(array('name'=>$_REQUEST['query']));
            }elseif ($_REQUEST['qtype']=='phone'){
                $tuangzhang = $shequ_tuanzhangModel->getOne(array('phone'=>$_REQUEST['query']));
            }
            $condition['shequ_tz_id'] = $tuangzhang['id'];
        }
        if ($_REQUEST['query'] != '' && in_array($_REQUEST['qtype'],array('order_sn','store_name','buyer_name','buyer_phone','pay_sn'))) {
            $condition[$_REQUEST['qtype']] = array('like',"%{$_REQUEST['query']}%");
        }
        if ($_REQUEST['query'] != '' && in_array($_REQUEST['qtype'],array('order_id','purchase_sap','send_sap'))) {
            $condition[$_REQUEST['qtype']] = array('eq',"{$_REQUEST['query']}");
        }
        if ($_GET['keyword'] != '' && in_array($_GET['keyword_type'],array('order_sn','store_name','buyer_name','pay_sn','shipping_code'))) {
            if ($_GET['jq_query']) {
                $condition[$_GET['keyword_type']] = $_GET['keyword'];
            } else {
                $condition[$_GET['keyword_type']] = array('like',"%{$_GET['keyword']}%");
            }
        }
        //团购时间处理
        if (in_array($_GET['qtype_time'],array('config_start_time','config_end_time','send_product_date'))) {
            $if_tuang_start_time = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_start_date']);
            $if_tuang_end_time = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_end_date']);
            $start_tuang_unixtime = $if_tuang_start_time ? strtotime($_GET['query_start_date']) : null;
            $end_tuang_unixtime = $if_tuang_end_time ? strtotime($_GET['query_end_date']): null;
            if ($_GET['qtype_time'] && ($start_tuang_unixtime || $end_tuang_unixtime)) {
                $condition_tuang[$_GET['qtype_time']] = array('time',array($start_tuang_unixtime,$end_tuang_unixtime));
            }
            /** @var shequ_tuan_configModel $shequ_tuan_configModel */
            $shequ_tuan_configModel = Model('shequ_tuan_config');
            $tuang_id_list = $shequ_tuan_configModel->getTuanConfigList($condition_tuang);
            if(!empty($tuang_id_list)){
                $condition['shequ_tuan_id']=array('in',array_column($tuang_id_list,'config_tuan_id'));
            }else{
                $condition['shequ_tuan_id'] = null;
            }
        }else{
            if($_GET['shequ_tuan_id']){
                $condition['shequ_tuan_id'] = trim($_GET['shequ_tuan_id']);
            } else {
                $condition['shequ_tuan_id'] = array('gt', 0);
                //$condition['chain_code'] = array('gt', 0);
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

        if(in_array($_GET['non_deliver'],array('1','-1','0','10'))){
            $condition['non_delivery'] = $_GET['non_deliver'];
        }
        if(in_array($_GET['purchase_sap'],array('1','2','0'))){
            $condition['purchase_sap'] = $_GET['purchase_sap'];
        }
        if(in_array($_GET['send_sap'],array('1','2','0'))){
            $condition['send_sap'] = $_GET['send_sap'];
        }
        if($_REQUEST['qtype']=='fx_order_id' && $_REQUEST['query'] != ''){
        	$condition['fx_order_id'] =  $_REQUEST['query'];
//        	$condition['fx_order_id'] =  array('like','%'.$_REQUEST['query'].'%');
        }

    }
    
    /**
     * 重新生成订单
     */
    public function rc_orderOp(){
    	$order_id = intval($_GET['order_id']);
    	if($order_id <= 0 ){
    		showMessage(L('miss_order_number'));
    	}
    	$model_order    = Model('order');
    	$order_info = $model_order->getOrderInfo(array('order_id'=>$order_id),array('order_goods','order_common','store'));
    
    	if( !$order_info['fx_order_id'] ) {
    		showMessage('不是分销订单');
    	}
    	
    	foreach ($order_info['extend_order_goods'] as $value) {
    		$value['image_60_url'] = cthumb($value['goods_image'], 60, $value['store_id']);
    		$value['image_240_url'] = cthumb($value['goods_image'], 240, $value['store_id']);
    		$value['goods_type_cn'] = orderGoodsType($value['goods_type']);
    		$value['goods_url'] = urlShop('goods','index',array('goods_id'=>$value['goods_id']));
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
    
    	//退款退货信息
    	$model_refund = Model('refund_return');
    	$condition = array();
    	$condition['order_id'] = $order_info['order_id'];
    	$condition['seller_state'] = 2;
    	$condition['admin_time'] = array('gt',0);
    	$return_list = $model_refund->getReturnList($condition);
    	if(!empty($return_list)){
    		showMessage('订单存在退款记录');
    	}

    	if( $_POST ) {
    		if( empty($_POST['gids']) ) {
    			showMessage('没有选择商品', 'index.php?act=order&op=rc_order&order_id='.$order_id);
    		}
    		 
    		if( $_POST['total_goods'] == count($_POST['gids']) ){
    			showMessage('商品不能全选', 'index.php?act=order&op=rc_order&order_id='.$order_id);
    		}
    		
    		$service = Service('Refund') ;
    		$params = array(
    				'refund_amount' => $_POST['refund_amount'],
    				'gids' => $_POST['gids'],
    				'op_name' => $this -> admin_info['name']
    		);
    		$res = $service -> reCreateOrder( $order_info, $params, $msg ) ;
    		showMessage($msg, 'index.php?act=order');
    	}
    
    	Tpl::output('order_info',$order_info);
    	Tpl::setDirquna('shequ');
    	Tpl::showpage('rcorder.view');
    }
    
    /**
     * 修改订单中的收货人地址信息
     */
    function edit_addressOp()
    {
    	$order_id = intval($_GET['order_id']);
    	if($order_id <= 0 ){
    		showMessage(L('miss_order_number'));
    	}
    	$model_order    = Model('order');
    	$order_info = $model_order->getOrderInfo(array('order_id'=>$order_id),array('order_common'));
    	Tpl::output('order_info',$order_info);
    	
    	if( $_POST ){
      		$receive_info = $order_info['extend_order_common']['reciver_info'] ;
      		$receive_info['area'] = trim($_POST['area']);
      		$receive_info['street'] = trim($_POST['streat']);
      		$receive_info['address'] = trim($_POST['area']) . " " . trim($_POST['streat']);
      		$receive_info['phone'] = trim($_POST['mobile']) ;
      		$receive_info['mob_phone'] = trim($_POST['mobile']) ;
      		
      		$status = 'true' ;
      		$res = Model('order_common')
      				->where( array('order_id' => $order_info['order_id']) )
      				->update( array('reciver_info' => serialize($receive_info), 'reciver_name' => $_POST['name']) ) ;
      		
      		if( !$res ) $status = 'false' ;

      		if (trim($_POST['mobile']) != $order_info['buyer_phone']) {
                $res = Model('orders')->where(array('order_id' => $order_info['order_id']))
                    ->update(array('buyer_phone' => trim($_POST['mobile'])));
                if( !$res ) $status = 'false' ;
            }
      		
      		$return = array(
      				'status' => $status ,
      				'msg' => $status ? "更新成功" : "更新失败" ,
      		) ;
      		die( json_encode($return) ) ;
    	}
    	
    	Tpl::setDirquna('shequ');
    	Tpl::showpage('order.edit.address','null_layout');
    }
    
    /**
     * 重置结算单中的 订单和退款单中的sap推送状态
     */
    function resetSapState4BillOp()
    {
        if (empty($_GET['ob_id'])) {
            exit('empty ob_id');
        }
    
        $ob_ids = $_GET['ob_id'];
        $model_bill = Model('order_bill');
        $ob_ids = explode(',', $ob_ids);
        $ob_condition['ob_id'] = array('in', $ob_ids) ;
    
        $order_bills = $model_bill->where($ob_condition)->select() ;
        $model_order = Model('order');
        $model_refund = Model('refund_return');
        $bill = Service('Bill') ;
        foreach ($order_bills as $bill_info) {
            /** 重置SAP推送状态 **/
            $bill->resetBillSapSatus( $bill_info , 3) ; //重置推送状态，并将审核标记改为3（平台已审核）
        }
    }
        
    /**
     * 更新sap的订单301推送标志，以用于做修正重新推送sap应收发票数据
     */
    function logSapRepushStateOp()
    {
    	header("Content-type:text/html;charset=utf-8");
        $order_id = $_GET['order_id'];
        $ob_id = intval($_GET['ob_id']);
        $sap_svc = Service('Sap');
        $model_order = Model('order');
        $model_refund = Model('refund_return');
        //整个账单全部重新修正重新推送
        if (!empty($ob_id)) {
            $bill_info = Model('order_bill')->field('ob_store_id,ob_start_date,ob_end_date')->where(array('ob_id' => $ob_id))->find();
            if (!empty($bill_info['ob_store_id'])) {
                //找出这个账单对应的所有订单号
                $order_condition = array();
                $order_condition['order_state'] = ORDER_STATE_SUCCESS;
                $order_condition['finnshed_time'] = array('between',"{$bill_info['ob_start_date']},{$bill_info['ob_end_date']}");
                $order_condition['store_id'] = $bill_info['ob_store_id'];
                $order_condition['send_sap'] = array('in', array('1', '2')) ;
                $order_list = $model_order->getOrderList($order_condition,'','order_id','order_id ASC', 500);
                $order_id = array_column($order_list, 'order_id');
                
                //找出这个账单对应的所有退款单号
                $refund_condition = array();
                $refund_condition['seller_state'] = '2';
                $refund_condition['admin_time'] = array('between',"{$bill_info['ob_start_date']},{$bill_info['ob_end_date']}");
                $refund_condition['store_id'] = $bill_info['ob_store_id'];
                //$refund_condition['purchase_sap'] = array('in', array('1', '2')) ;
                $refund_condition['sap_return_credit'] = array('in', array('1', '2')) ;
                $refund_list = $model_refund->getRefundReturnList($refund_condition, '', 'refund_id', 500);
                $refund_id = array_column($refund_list, 'refund_id');
            } else {
                $ob_id = 0;
            }
        }
        if($order_id <= 0 && $ob_id <= 0){
            showMessage(L('miss_order_number'));
        }
        $res = $sap_svc->writeoffUnbillOrders(false, $order_id);
        v($res,0);
        
        if( $ob_id > 0 && is_array($refund_id) && !empty($refund_id) ) {
        	//$res = $sap_svc->writeoffRepushRefunds($ob_id, $refund_id, false);
        	//v($res);
        }
    }
    
	function edit_deliverOp()
    {
    	$order_id = intval($_GET['order_id']);
    	if($order_id <= 0 ){
    		showMessage(L('miss_order_number'));
    	}
    	$model_order    = Model('order');
    	$order_info = $model_order->getOrderInfo(array('order_id'=>$order_id),array('order_common'));
    	Tpl::output('order_info',$order_info);
    	
    	$express_list  = rkcache('express',true);
    	
    	if( $_POST ){
      		$shipping_code = trim( $_POST['shipping_code'] ) ;
    		$express_id = intval($_POST['express_id']) ;
    		if( !preg_match("/^[0-9a-zA-Z]+$/i",   $shipping_code ) ) {
    			die( json_encode(array('status' => 'false', 'msg' => '快递单号只能包含字母和数字')) ) ;
    		}
    		
    		$status = 'true' ;
    		$res = Model()->table('orders')->where(array('order_id' => $order_id))->update(array('shipping_code' => $shipping_code));
    		if( !$res ) $status = 'false' ;
    		$res = Model()->table('order_common')->where(array('order_id' => $order_id))->update( array('shipping_express_id' => $express_id) ) ;
    		if( !$res ) $status = 'false' ;
    		
    		//推送分销
    		$model_order -> setOrderSend($order_info , $express_id , $shipping_code) ;
    		
    		$return = array(
    				'status' => $status,
    				'e_name' => "<a href=\"{$express_list[$express_id]['e_url']}\" target=\"_blank\">{$express_list[$express_id]['e_name']}</a>"
    		) ;
    		die( json_encode($return) ) ;
    	}
    	
    	Tpl::output('express_list',$express_list);
    	Tpl::output('order_id', $_GET['order_id']);
    	Tpl::setDirquna('shequ');
    	Tpl::showpage('order.edit.deliver','null_layout');
    }
    
    function show_goods_columnOp()
    {
    	$order_id = intval($_GET['order_id']);
    	$goods_id = intval($_GET['goods_id']);
    	if($order_id <= 0 || $goods_id <= 0 ){
    		die("参数错误") ;
    	}
    	
    	$condition['order_id'] = $order_id ;
    	$condition['goods_id'] = $goods_id ;
    	$order_goods_info = Model('order')->getOrderGoodsInfo( $condition ) ;
        if(($order_goods_info['tax_input'] == 200)||($order_goods_info['tax_output'] == 200)){
            $goods_info = Model('goods')->getGoodsInfoByID($goods_id);
            $data = array();
            $data['tax_input'] = $goods_info['tax_input'];
            $data['tax_output'] = $goods_info['tax_output'];
            Model('order')->editOrderGoods($data,$condition);
            $order_goods_info = Model('order')->getOrderGoodsInfo( $condition ) ;
        }
    	Tpl::output('goods_info',$order_goods_info);
		Tpl::setDirquna('shequ');
        Tpl::showpage('order.goods.view');
    }
    /** 导入已结算订单号，更新历史表结算状态 */
    function import_pddOp()
    {
    	set_time_limit(0);
    	if( $_FILES ) {
	    	vendor('PHPExcel/Reader/Excel2007');
	    	vendor('PHPExcel/Reader/Excel5');
	    	$PHPReader = new PHPExcel_Reader_Excel2007();        //建立reader对象
	    	$filePath = $_FILES['orders']['tmp_name'] ;
	    	if (!$PHPReader->canRead($filePath)) {
	    		$PHPReader = new PHPExcel_Reader_Excel5();
	    		if (!$PHPReader->canRead($filePath)) {
	    			return false;
	    		}
	    	}
	    	
	    	$PHPExcel = $PHPReader->load($filePath);
	    	$currentSheet = $PHPExcel->getSheet(0);            //读取excel文件中的指定工作表
	    	//$allColumn = $currentSheet->getHighestColumn();         //*取得最大的列号
	    	$allRow = $currentSheet->getHighestRow();               //取得一共有多少行
	    	
	    	$ordernos = array();
	    	for ($rowIndex = 1; $rowIndex <= $allRow; $rowIndex++) {        //循环读取每个单元格的内容。注意行从第1行开始，列从A开始
	    		$addr = "A" . $rowIndex;
	    		$cell = $currentSheet->getCell($addr)->getValue();
	    		$ordernos[] = $cell;
	    	}
	    	
	    	if( !empty($ordernos) ) {
	    		$condition['orderno'] = array('in', $ordernos) ;
	    		Model('b2c_fxhistory')->where($condition)->update(array('purchase_status' => 1));
	    	}
	    	
    	} else {
	    	Tpl::setDirquna('shequ');
	    	Tpl::showpage('import.pdd');
    	}
    }








    private function _excelToArray($filePath = '', $sheet = 0)
    {
        if (empty($filePath) or !file_exists($filePath)) {
            return false;
        }
        $fileType = explode('.',$filePath);
        $fileType = $fileType[count($fileType)-1];

        //csv类型直接str_getcsv转换
        if(strtolower($fileType) == 'csv'){
            $lines = array_map('str_getcsv', file($filePath));;
            $result = array();
            for ($i = 0; $i < count($lines); $i++) {        //循环读取每行内容注意行从第1行开始($i=0)
                $obj = $lines[$i];
                foreach ($obj as $k => $v) {
                    $result[$i][] = mb_convert_encoding($v, 'UTF-8', 'gbk');
                }
            }
            return $result;
        }

        //excel类型 PHPExcel类库转换
        vendor('PHPExcel/Reader/Excel2007');
        vendor('PHPExcel/Reader/Excel5');
        $PHPReader = new PHPExcel_Reader_Excel2007();        //建立reader对象
        if (!$PHPReader->canRead($filePath)) {
            $PHPReader = new PHPExcel_Reader_Excel5();
            if (!$PHPReader->canRead($filePath)) {
                return false;
            }
        }
        $PHPExcel = $PHPReader->load($filePath);
        $currentSheet = $PHPExcel->getSheet($sheet);            //读取excel文件中的指定工作表
        $allColumn = $currentSheet->getHighestColumn();         //*取得最大的列号
        $allRow = $currentSheet->getHighestRow();               //取得一共有多少行
        $data = array();
        for ($rowIndex = 1; $rowIndex <= $allRow; $rowIndex++) {        //循环读取每个单元格的内容。注意行从第1行开始，列从A开始
            for ($colIndex = 'A'; $colIndex <= $allColumn; $colIndex++) {
                $addr = $colIndex . $rowIndex;
                $cell = $currentSheet->getCell($addr)->getValue();
                if ($cell instanceof PHPExcel_RichText) {       //转换字符串
                    $cell = $cell->__toString();
                }
                $data[$rowIndex-1][] = $cell;
            }
        }
        return $data;
    }


}
