<?php
/**
 * 实物订单结算
 * * @汉购网 (c) 2015-2018 ShopWWI Inc. (http://www.hangowa.com)
 * @license    http://www.shopwwi.c om
 * @link       交流群号：
 * @since      汉购网提供技术支持 授权请购买shopnc授权
 */



defined('ByShopWWI') or exit('Access Invalid!');
class store_billControl extends BaseSellerControl {
    /**
     * 每次导出多少条记录
     * @var integer
     */
    const EXPORT_SIZE = 5000;
    private $_bill_info;

    public function __construct() {
        parent::__construct() ;
        Language::read('member_layout');
    }

    /**
     * 结算列表
     *
     */
    public function indexOp() {
        $model_bill = Model('bill');
        $condition = array();
        $condition['ob_store_id'] = $_SESSION['store_id'];
        if (preg_match('/^\d+$/',$_GET['ob_id'])) {
            $condition['ob_id'] = intval($_GET['ob_id']);
        }
        if (is_numeric($_GET['bill_state'])) {
            $condition['ob_state'] = intval($_GET['bill_state']);
        }
        $bill_list = $model_bill->getOrderBillList($condition,'*',12,'ob_state asc,ob_id desc');

        Tpl::output('bill_list',$bill_list);
        Tpl::output('show_page',$model_bill->showpage());

        $model_store_ext = Model('store_extend');
        $ext_info = $model_store_ext->getStoreExtendInfo(array('store_id'=>$_SESSION['store_id']));
        Tpl::output('bill_cycle',$ext_info['bill_cycle'] ? $ext_info['bill_cycle'].'天' : '1个月');

        $this->profile_menu('list','list');
        Tpl::showpage('store_bill.index');
    }

    /**
     * 查看结算单详细
     *
     */
    public function show_billOp(){
        if (!preg_match('/^\d+$/',$_GET['ob_id'])) {
            showMessage('参数错误','','html','error');
        }
        $model_bill = Model('bill');
		$condition = array();
		$condition['ob_id'] = intval($_GET['ob_id']);
		$condition['ob_store_id'] = $_SESSION['store_id'];
        $bill_info = $model_bill->getOrderBillInfo($condition);
        if (!$bill_info){
            showMessage('参数错误','','html','error');
        }
        $this->_bill_info = $bill_info;

        $order_condition = array();
        $order_condition['order_state'] = ORDER_STATE_SUCCESS;
        $order_condition['store_id'] = $bill_info['ob_store_id'];
        $order_condition['filter_status']='0';
        $if_start_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_start_date']);
        $if_end_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_end_date']);
        $start_unixtime = $if_start_date ? strtotime($_GET['query_start_date']) : null;
        $end_unixtime = $if_end_date ? strtotime($_GET['query_end_date']) : null;
        if ($if_start_date || $if_end_date) {
            $order_condition['finnshed_time'] = array('time',array($start_unixtime,$end_unixtime));
        } else {
            $order_condition['finnshed_time'] = array('between',"{$bill_info['ob_start_date']},{$bill_info['ob_end_date']}");
        }

        if ($_GET['type'] =='refund'){
            if (preg_match('/^\d{8,20}$/',$_GET['query_order_no'])) {
                $order_condition['refund_sn'] = $_GET['query_order_no'];
            }

            //退款订单列表
            $model_refund = Model('refund_return');
            $refund_condition = array();
            $refund_condition['seller_state'] = 2;
            $refund_condition['store_id'] = $bill_info['ob_store_id'];
            $refund_condition['goods_id'] = array('gt',0);
            $refund_condition['filter_status']='0';
            $refund_condition['admin_time'] = $order_condition['finnshed_time'];
            if (preg_match('/^\d{8,20}$/',$_GET['query_order_no'])) {
                $refund_condition['refund_sn'] = $_GET['query_order_no'];
            }

            if(!empty($_GET['check_result'])){
                if($_GET['check_result'] == '已结算'){
                    $post_query = array('in','1,2,3');
                } else if($_GET['check_result'] == '第1次结算'){
                    $post_query = array('in','-1,1');
                } else if($_GET['check_result'] == '第2次结算'){
                    $post_query = array('in','-2,2');
                } else if($_GET['check_result'] == '第3次结算'){
                    $post_query = array('in','-3,3');
                } else if($_GET['check_result'] == '未结算'){
                    $post_query = array('in','-1,-2,-3');
                } else if($_GET['check_result'] == '未对账'){
                    $post_query = 0;
                }
                $refund_condition['check_result'] = $post_query;
            }

            $field = $bill_info['ob_store_manage_type']=='platform'?
                C('tablepre').'refund_return.*,ROUND(refund_amount*commis_rate/100,2) as commis_amount':
                C('tablepre').'refund_return.*,ROUND(refund_amount*cost_rate*commis_rate/10000,2) as commis_amount';
            $field = '*';
                $refund_list = $model_refund->getRefundReturnList($refund_condition,20,$field);

            $order_id_array = array_column($refund_list,'order_id');
            /** @var orderModel $orderGoodsModel */
            $orderGoodsModel = Model('order');
            $orderGoodsList = $orderGoodsModel->getOrderGoodsList(array('order_id'=>array('in',$order_id_array)));
            $orderGoodsList = array_under_reset($orderGoodsList,'order_id',2);

            if (is_array($refund_list) && count($refund_list) == 1 && $refund_list[0]['refund_id'] == '') {
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
            foreach ($refund_list as $k =>$v){
                $recList = $orderGoodsList[$v['order_id']];
                $orderGoods = array(
                    'goods_cost'=>0,
                    'commis_rate'=>0,
                    'goods_pay_price'=>1,
                );
                foreach ($recList as $rec){
                    if($rec['goods_id'] == $v['goods_id'])
                    {
                        $orderGoods = $rec;
                        break;
                    }
                }
                $refundAmount = $v['refund_amount_bill'] == -1?$v['refund_amount']:$v['refund_amount_bill'];

                $refund_list[$k]['refund_amount'] = $bill_info['ob_store_manage_type']=='platform'?
                    ncPriceFormat($refundAmount):
                    ncPriceFormat($refundAmount*$orderGoods['goods_cost']/$orderGoods['goods_pay_price']);
                $refund_list[$k]['commis_amount'] = $bill_info['ob_store_manage_type']=='platform'?
                    ncPriceFormat($refundAmount*$orderGoods['commis_rate']/100):0;
                if ($bill_info['ob_store_manage_type']=='platform' && $k['refund_amount'] == $orderGoods['goods_pay_price']) {
                    $refund_list[$k]['rpt_bill'] = $orderGoods['rpt_bill']; // 调整退款红包为平台承担部分
                } else {
                    $refund_list[$k]['rpt_bill'] = 0;
                }
                $refund_list[$k]['check_result'] = $check_result_array[$v['check_result']];
            }
            //取返还佣金
            Tpl::output('refund_list',$refund_list);
            Tpl::output('show_page',$model_refund->showpage());
            $sub_tpl_name = 'store_bill.show.refund_list';
            $this->profile_menu('show','refund_list');
        }
        elseif ($_GET['type'] == 'cost') {
            //店铺费用
            /** @var store_costModel $model_store_cost */
            $model_store_cost = Model('store_cost');
            $cost_condition = array();
            $cost_condition['cost_store_id'] = $bill_info['ob_store_id'];
            $cost_condition['cost_time'] = $order_condition['finnshed_time'];

            if(!empty($_GET['check_result'])){
                if($_GET['check_result'] == '已结算'){
                    $post_query = array('in','1,2,3');
                } else if($_GET['check_result'] == '第1次结算'){
                    $post_query = array('in','-1,1');
                } else if($_GET['check_result'] == '第2次结算'){
                    $post_query = array('in','-2,2');
                } else if($_GET['check_result'] == '第3次结算'){
                    $post_query = array('in','-3,3');
                } else if($_GET['check_result'] == '未结算'){
                    $post_query = array('in','-1,-2,-3');
                } else if($_GET['check_result'] == '未对账'){
                    $post_query = 0;
                }
                $cost_condition['check_result'] = $post_query;
            }
            $store_cost_list = $model_store_cost->getStoreCostList($cost_condition,20);

            //取得店铺名字
            $store_info = Model('store')->getStoreInfoByID($bill_info['ob_store_id']);
            $check_result_array = array(
                '1' => '第一次结算',
                '2' => '第二次结算',
                '3' => '第三次结算',
                '-1' => '第一次异常',
                '-2' => '第二次异常',
                '-3' => '第三次异常',
                '0' => '未对账'
            );
            foreach($store_cost_list as $k => $v){
                $store_cost_list[$k]['check_result'] = $check_result_array[$v['check_result']];
            }
            Tpl::output('cost_list',$store_cost_list);
            Tpl::output('store_info',$store_info);
            Tpl::output('show_page',$model_store_cost->showpage());
            $sub_tpl_name = 'store_bill.show.cost_list';
            $this->profile_menu('show','cost_list');
        
        }
        elseif ($_GET['type'] == 'book') {
            $condition = array();
            //被取消的预定订单列表
            $model_order = Model('order');
            if (preg_match('/^\d{8,20}$/',$_GET['query_order_no'])) {
                $order_info = $model_order->getOrderInfo(array('order_sn'=> $_GET['query_order_no']));
                if ($order_info) {
                    $condition['book_order_id'] = $order_info['order_id'];
                } else {
                    $condition['book_order_id'] = 0;
                }                
            }

            $model_order_book = Model('order_book');

            $condition['book_store_id'] = $bill_info['ob_store_id'];
            $condition['book_cancel_time'] = $order_condition['finnshed_time'];
            $order_book_list = $model_order_book->getOrderBookList($condition,$_POST['rp'],'book_id desc','*');
            
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
            $order_list = $model_order->getOrderList(array('order_id'=>array('in',$order_id_array)));
            Tpl::output('deposit_list',$tmp_book);
            Tpl::output('order_list',$order_list);
            Tpl::output('show_page',$model_order->showpage());
            $sub_tpl_name = 'store_bill.show.order_book_list';
            $this->profile_menu('show','book_list');
        }
        elseif ($_GET['type'] == 'log') {
            $condition = array();
            //被取消的预定订单列表
            /** @var billModel $model_bill */
            $model_bill = Model('order');
            $condition['ob_id'] = $bill_info['ob_id'];
            $condition['log_type'] = 'data';
            if($_GET['query_log_role'] === '1')
                $condition['log_role'] = 1;
            if (preg_match('/^\d{8,20}$/',$_GET['query_order_no'])) {
                $condition['order_sn'] = $_GET['query_order_no'];
            }
            $log_list = $model_bill->table('bill_log')->where($condition)->page(20)->order('log_id desc')->select();
//            echo '<pre>';
//            echo $model_bill->getLastSql();
//            var_dump($log_list);
//            die;

            Tpl::output('log_list',$log_list);
            Tpl::output('show_page',$model_bill->showpage());
            $sub_tpl_name = 'store_bill.show.log_list';
            $this->profile_menu('show','log');
        }
        elseif ($_GET['type'] == 'pay_log') {
            $condition = array();
            //被取消的预定订单列表
            /** @var billModel $model_bill */
            $model_order_bill_log = Model('order_bill_log');
            $condition['obl_ob_id'] = $bill_info['ob_id'];
            $log_list = $model_order_bill_log->table('order_bill_log')->where($condition)->page(20)->order('obl_id desc')->select();

            Tpl::output('log_list',$log_list);
            Tpl::output('show_page',$model_bill->showpage());
            $sub_tpl_name = 'store_bill.show.pay_log_list';
            $this->profile_menu('show','pay_log');
        }
        else {

            if (preg_match('/^\d{8,20}$/',$_GET['query_order_no'])) {
                $order_condition['order_sn'] = $_GET['query_order_no'];
            }

            if(!empty($_GET['check_result'])){
                if($_GET['check_result'] == '已结算'){
                    $post_query = array('in','1,2,3');
                } else if($_GET['check_result'] == '第1次结算'){
                    $post_query = array('in','-1,1');
                } else if($_GET['check_result'] == '第2次结算'){
                    $post_query = array('in','-2,2');
                } else if($_GET['check_result'] == '第3次结算'){
                    $post_query = array('in','-3,3');
                } else if($_GET['check_result'] == '未结算'){
                    $post_query = array('in','-1,-2,-3');
                } else if($_GET['check_result'] == '未对账'){
                    $post_query = 0;
                }
                $order_condition['check_result'] = $post_query;
            }

            //订单列表
            /** @var orderModel $model_order */
            $model_order = Model('order');
            $order_list = $model_order->getOrderList($order_condition,20);

            //然后取订单商品佣金
            $order_id_array = array();
            if (is_array($order_list)) {
                foreach ($order_list as $order_info) {
                    $order_id_array[] = $order_info['order_id'];
                }
            }

            $orderGoodsList = $model_order->getOrderGoodsList(array('order_id'=>array('in',$order_id_array)));
            $orderGoodsList = array_under_reset($orderGoodsList,'order_id',2);
            $check_result_array = array(
                '1' => '第一次结算',
                '2' => '第二次结算',
                '3' => '第三次结算',
                '-1' => '第一次异常',
                '-2' => '第二次异常',
                '-3' => '第三次异常',
                '0' => '未对账'
            );
            foreach ($order_list as $k=>$order){
                $commis = 1;////////////$this->getOrderCommis($order,$orderGoodsList[$order['order_id']]);
                $order_list[$k]['commis_amount'] = $commis;
                $order_list[$k]['check_result'] = $check_result_array[$order['check_result']];
            }

            Tpl::output('order_list',$order_list);
            Tpl::output('show_page',$model_order->showpage());
            $sub_tpl_name = 'store_bill.show.order_list';
            $this->profile_menu('show','order_list');
        }

        Tpl::output('sub_tpl_name',$sub_tpl_name);
        Tpl::output('bill_info',$bill_info);
        Tpl::showpage('store_bill.show');
    }


    public function edit_orderOp()
    {
        //exit('<h1 style="padding: 30px;font-size: 24px;">调整功能临时关闭</h1>');
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
        //Tpl::setDirquna('shop');/*网 店 运 维shop wwi.com*/
        Tpl::showpage('store_bill.edit_order','null_layout');
    }
    public function edit_recOp()
    {
        $ob_id = intval($_GET['ob_id']);
        $order_id = intval($_GET['order_id']);
        $rec_id = intval($_GET['rec_id']);
        $field = trim($_GET['field']);
        $value = trim($_GET['value']);
        if($order_id <= 0 ){
            showMessage(L('miss_order_number'));
        }
        if(!in_array($field,array('goods_cost','commis_rate','rpt_bill'))){
            showMessage('修改字段错误！');
        }
        /** @var orderModel $model_order */
        $model_order    = Model('order');
        $order_info = $model_order->getOrderInfo(array('order_id'=>$order_id));
        if($order_info['store_id']!=$this->store_info['store_id']){
            die( json_encode(array('error'=>1,'msg'=>'非法操作！')) ) ;
        }
        if($order_info['purchase_sap']){
            die( json_encode(array('error'=>1,'msg'=>'订单已推送SAP，不允许修改！')) ) ;
        }
        /** @var billModel $billModel */
        $billModel = Model('bill');
        $billInfo = $billModel->getOrderBillInfo(array('ob_id'=>$ob_id));
        if($billInfo['ob_state'] != BILL_STATE_CREATE){
            die( json_encode(array('error'=>1,'msg'=>'当前账单状态不允许修改基础数据！')) ) ;
        }
        // TODO 根据rec_id查找订单商品，然后修改成本/佣金/红包，并修改订单成本，退单成本比例？
        /** @var orderModel $orderModel */
        $orderModel = Model('order');
        $orderGoods = $orderModel->getOrderGoodsInfo(array('rec_id'=>$rec_id));
        $updateArray = array($field =>$value);
        $res = array('msg'=>'success','data'=>$orderGoods);
        if($field == 'commis_rate'){
            if($orderGoods['manage_type']=='platform'){
                $res['commis'] = number_format(($orderGoods['goods_pay_price']+$orderGoods['rpt_bill'])*$value/100,2);
                $updateArray['goods_cost'] = $orderGoods['goods_pay_price']-$res['commis']+$orderGoods['rpt_bill'];
                $res['cost'] = $updateArray['goods_cost'];
            }else{
                $res['commis'] = number_format($orderGoods['goods_cost']*$value/100,2);
            }
        }
        $orderModel->beginTransaction();
        $updateRes = $orderModel->table('order_goods')->where(array('rec_id'=>$rec_id))->update($updateArray);
        if(!$updateRes){
            $orderModel->rollback();
            die( json_encode(array('error'=>1,'msg'=>'修改失败！')) ) ;
        }
        $attArray = array('goods_cost'=>'成本','commis_rate'=>'佣金比例','rpt_bill'=>'平台红包');
        // 添加操作日志
        $logArray = array(
            'ob_id'=>$ob_id,
            'rec_id'=>$orderGoods['rec_id'],
            'order_id'=>$orderGoods['order_id'],
            'order_sn'=>$order_info['order_sn'],
            'log_model'=>'order_goods',
            'log_attribute'=>$field,
            'old_value'=>$orderGoods[$field],
            'new_value'=>$value,
            'log_msg'=>"将订单【{$order_info['order_sn']}】的商品【{$orderGoods['goods_name']}】的【{$attArray[$field]}】从【{$orderGoods[$field]}】调整为【{$value}】",
            'log_time'=>TIMESTAMP,
            'log_role'=>0,
            'log_user'=>$this->store_info['store_name'],
        );
        $res['user'] = $this->store_info;
        $addRes = $orderModel->table('bill_log')->insert($logArray);
        if(!$addRes){
            $orderModel->rollback();
            die( json_encode(array('error'=>1,'msg'=>'修改失败！')) ) ;
        }
        if($billInfo['ob_state'] == BILL_STATE_CREATE){
            if(!$updateRes){
                $orderModel->rollback();
                die( json_encode(array('error'=>1,'msg'=>'修改失败！')) ) ;
            }
        }
        $orderModel->commit();
        // 更新订单成本金额&红包金额
        if(isset($updateArray['goods_cost'])||isset($updateArray['rpt_bill']))
        {
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


    public function batch_editOp()
    {
        //exit("<h2>批量修改功能已关闭！</h2>");
        $ob_id = intval($_GET['ob_id']);
        if ($ob_id <= 0) {
            showMessage('参数错误', '', 'html', 'error');
        }
        /** @var billModel $billModel */
        $billModel = Model('bill');
        $billInfo = $billModel->getOrderBillInfo(array('ob_id' => $ob_id));
        if (chksubmit()) {
            error_reporting(E_ALL);
            set_time_limit(1800);
            ini_set('memory_limit','4G');
            setlocale(LC_ALL, 'zh_CN');
            if (!empty($billInfo)) {
                if (!in_array($billInfo['ob_state'], array(BILL_STATE_CREATE))) {
                    showMessage('账单正在审核中，暂不允许修改！');
                }
                $ob_id = $billInfo['ob_id'];
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
                    if(empty($data[0])) break;
                    $resItem = array();
                    for ($i=0;$i<5;$i++){
                        $resItem[] = $data[$i];
                    }
                    $order = $orderModel->getOrderInfo(array('order_sn' => trim($data[0])));
                    if ($order['store_id'] != $billInfo['ob_store_id'] || $order['store_id'] != $this->store_info['store_id'] || $order['finnshed_time'] < $billInfo['ob_start_date'] || $order['finnshed_time'] > $billInfo['ob_end_date'] || $order['purchase_sap']) {
                        $resItem[] = $resItem[] = $resItem[] = '账单ID错误或修改订单不在当前结算期内';
                        $error=true;
                        $res[] = $resItem;
                        continue;
                    }

                    $orderGoods = $orderModel->getOrderGoodsInfo(array('order_id' => $order['order_id'], 'goods_id' => $data[1]));
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

                    if ($orderGoods['manage_type'] == 'platform' && $orderGoods['rpt_bill'] != $item['rptBill'] && is_numeric($item['rptBill'])) {
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

                    if ($orderGoods['manage_type'] == 'platform' &&$orderGoods['commis_rate'] != $item['commisRate'] && is_numeric($item['commisRate'])) {
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

                    if (!empty($orderGoods) && $orderGoods['manage_type'] != 'platform'&&$orderGoods['goods_cost'] != $item['goodsCost'] && is_numeric($item['goodsCost'])) {
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
                    // $resItem[] = $resItem[] = '未处理';
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
        Tpl::showpage('store_bill.batch_edit', 'null_layout');
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
            'log_role' => 0,
            'log_user' => $this->store_info['store_name'],
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
            'log_role' => 0,
            'log_user' => $this->store_info['store_name'],
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
SET a.cost_amount = b.goods_cost
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
            'log_msg' => "将订单【{$order['order_sn']}】的商品【{$orderGoods['goods_name']}】的【佣金比例金额】从【{$orderGoods['commis_rate']}】调整为【{$value}】",
            'log_time' => TIMESTAMP,
            'log_role' => 0,
            'log_user' => $this->store_info['store_name'],
        );
        $addRes = $orderModel->table('bill_log')->insert($logArray);
        if (!$addRes) {
            throw new Exception('日志记录失败');
        }
        return true;
    }

    /**
     * 打印结算单
     *
     */
    public function bill_printOp(){
        if (!preg_match('/^\d+$/',$_GET['ob_id'])) {
            showMessage('参数错误','','html','error');
        }
        $model_bill = Model('bill');
        $condition = array();
        $condition['ob_id'] = intval($_GET['ob_id']);
        $condition['ob_state'] = BILL_STATE_SUCCESS;
		$condition['ob_store_id'] = intval($_SESSION['store_id']);
        $bill_info = $model_bill->getOrderBillInfo($condition);
        if (!$bill_info){
            showMessage('参数错误','','html','error');
        }

        Tpl::output('bill_info',$bill_info);
        Tpl::showpage('store_bill.print','null_layout');
    }

    /**
     * 店铺确认出账单
     *
     */
    public function confirm_billOp(){
        if (!preg_match('/^\d+$/',$_GET['ob_id'])) {
            showDialog('参数错误','','error');
        }
        /** @var billModel $model_bill */
        $model_bill = Model('bill');
        $condition = array();
        $condition['ob_id'] = intval($_GET['ob_id']);
        $condition['ob_store_id'] = $_SESSION['store_id'];
        $condition['ob_state'] = BILL_STATE_CREATE;
        $billInfo = $model_bill->getOrderBillInfo($condition);
        if(empty($billInfo)){
            showDialog('账单基础数据已经修改，请确认调整并完成发送客服重建账单','reload','error');
        }
        $update = $model_bill->editOrderBill(array('ob_state'=>BILL_STATE_STORE_COFIRM),$condition);
        if ($update>0){
            showDialog('确认成功','reload','succ');
        }else{
            showDialog(L('nc_common_op_fail'),'reload','error');
        }
    }
    public function rebuild_billOp(){
        if (!preg_match('/^\d+$/',$_GET['ob_id'])) {
            showDialog('参数错误','','error');
        }
        /** @var billModel $model_bill */
        $model_bill = Model('bill');
        $condition = array();
        $condition['ob_id'] = intval($_GET['ob_id']);
        $condition['ob_store_id'] = $_SESSION['store_id'];
        $condition['ob_state'] = BILL_STATE_CREATE;
        $billInfo = $model_bill->getOrderBillInfo($condition);
        if(empty($billInfo)){
            showDialog(L('nc_common_op_fail'),'reload','error');
        }
        $update = $model_bill->editOrderBill(array('ob_state'=>BILL_STATE_HANGO),$condition);
        // 添加操作日志
        $logArray = array(
            'ob_id'=>$_GET['ob_id'],
            'log_type'=>'bill',
            'rec_id'=>0,
            'order_id'=>0,
            'order_sn'=>0,
            'log_model'=>'order_bill',
            'log_attribute'=>'ob_state',
            'old_value'=>BILL_STATE_CREATE,
            'new_value'=>BILL_STATE_HANGO,
            'log_msg'=>"商家确认账单",
            'log_time'=>TIMESTAMP,
            'log_role'=>0,
            'log_user'=>$this->store_info['store_name'],
        );
        $res['user'] = $this->store_info;
        $addRes = $model_bill->table('bill_log')->insert($logArray);
        if ($update){
            showDialog('发送成功','reload','succ');
        }else{
            showDialog(L('nc_common_op_fail'),'reload','error');
        }
    }

    /**
     * 导出结算订单明细CSV
     *
     */
    public function export_orderOp(){
        if (!preg_match('/^\d+$/',$_GET['ob_id'])) {
            showMessage('参数错误','','html','error');
        }
        ini_set('memory_limit','4G');

        /** @var billModel $model_bill */
        $model_bill = Model('bill');
        $bill_info = $model_bill->getOrderBillInfo(array('ob_id'=>intval($_GET['ob_id']),'ob_store_id'=>$_SESSION['store_id']));
        if (!$bill_info){
            showMessage('参数错误','','html','error');
        }

        /** @var orderModel $model_order */
        $model_order = Model('order');
        $condition = array();
        if (preg_match('/^\d{8,20}$/',$_GET['query_order_no'])) {
            $condition['order_sn'] = $_GET['query_order_no'];
        }
        $condition['order_state'] = ORDER_STATE_SUCCESS;
        $condition['store_id'] = $_SESSION['store_id'];
        $condition['filter_status']='0';
        $if_start_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_start_date']);
        $if_end_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_end_date']);
        $start_unixtime = $if_start_date ? strtotime($_GET['query_start_date']) : null;
        $end_unixtime = $if_end_date ? strtotime($_GET['query_end_date']) : null;
        if ($if_start_date || $if_end_date) {
            $condition['finnshed_time'] = array('time',array($start_unixtime,$end_unixtime));
        } else {
            $condition['finnshed_time'] = array('between',"{$bill_info['ob_start_date']},{$bill_info['ob_end_date']}");
        }
        if (!is_numeric($_GET['curpage'])){
            $count = $model_order->getOrderCount($condition);
            $array = array();
            if ($count > self::EXPORT_SIZE ){
                //显示下载链接
                $page = ceil($count/self::EXPORT_SIZE);
                for ($i=1;$i<=$page;$i++){
                    $limit1 = ($i-1)*self::EXPORT_SIZE + 1;
                    $limit2 = $i*self::EXPORT_SIZE > $count ? $count : $i*self::EXPORT_SIZE;
                    $array[$i] = $limit1.' ~ '.$limit2 ;
                }
                Tpl::output('list',$array);
                Tpl::output('murl','index.php?act=store_bill&op=show_bill&ob_id='.$_GET['ob_id']);
                Tpl::showpage('store_export.excel');
                exit();
            }else{
                //如果数量小，直接下载
                $data = $model_order->getOrderList($condition,'','*','order_id desc',self::EXPORT_SIZE,array('order_goods'));
            }
        }else{
            //下载
            $limit1 = ($_GET['curpage']-1) * self::EXPORT_SIZE;
            $limit2 = self::EXPORT_SIZE;
            set_time_limit(600);
            $data = $model_order->getOrderList($condition,'','*','order_id desc',"{$limit1},{$limit2}",array('order_goods'));
        }
        $hango = in_array($_SESSION['store_id'],array(10,15,48,138,171,174,213));

        if($hango){
            $fxModel = Model('b2c_order_fenxiao');
            $fxLogs = $fxModel->where(array('orderno'=>array('in',array_filter(array_column($data,'fx_order_id')))))->limit(999999)->select();
            $fxLogs = array_under_reset($fxLogs,'orderno');
        }

        //订单商品表查询条件
        $order_id_array = array();
        if (is_array($data)) {
            foreach ($data as $order_info) {
                $order_id_array[] = $order_info['order_id'];
            }
        }
        $order_goods_condition = array();
        $order_goods_condition['order_id'] = array('in',$order_id_array);

        $export_data = array();
        $export_data[0] = array('订单编号','分销订单号','订单金额','运费','佣金',
            '下单日期','成交日期','商家','商家编号','买家','买家编号','订单红包',
            '商品ID','商品名','单价','数量','实际支付','佣金比例','商品行佣金','商品行成本','商品行红包','商品行成本单价',
        );

        if($hango){
            $export_data[0][] = '分销渠道';
            $export_data[0][] = '分销订单号';
            $export_data[0][] = '导入时间';
        }

        $rpt_totals = 0;
        $order_totals = 0;
        $shipping_totals = 0;
        $commis_totals = 0;
        $k = 0;
        foreach ($data as $v) {
            //该订单算佣金
            //$field = 'SUM(ROUND(goods_pay_price*commis_rate/100,2)) as commis_amount,order_id';
            /*$field = $bill_info['ob_store_manage_type']=='platform'?
                ($bill_info['ob_ver']==1
                    ?'SUM(ROUND((goods_pay_price+rpt_bill)*commis_rate/100,2)) as commis_amount,order_id'
                    :'SUM(ROUND(goods_pay_price*commis_rate/100,2)) as commis_amount,order_id'):
                //'SUM(ROUND((goods_pay_price+rpt_bill)*commis_rate/100,2)) as commis_amount,order_id':
                'SUM(ROUND(goods_cost*commis_rate/100,2)) as commis_amount,order_id';
            $commis_list = $model_order->getOrderGoodsList($order_goods_condition,$field,null,null,'','order_id','order_id');*/
            $commis = $this->getOrderCommis($v,$v['extend_order_goods']);

            $rpt_totals += $v['rpt_bill'];
            $order_totals += $v['order_amount'];
            $shipping_totals += $v['shipping_fee'];
            $commis_totals += $commis;
            $item_shipping_fee = $v['shipping_fee']/count($v['extend_order_goods']);

            if($hango) {
                $logTime = '';
                if(isset($fxLogs[$v['fx_order_id']])) $logTime = date('Y-m-d H:i:s',$fxLogs[$v['fx_order_id']]['log_time']);
            }

            //$goods_string = '';
            $goodsCount =0;
            if (is_array($v['extend_order_goods'])) {
                foreach ($v['extend_order_goods'] as $v1) {
                    /*if($goodsCount>0){
                        $export_data[$k+1][]=$export_data[$k+1][]=$export_data[$k+1][]=$export_data[$k+1][]=
                        $export_data[$k+1][]=$export_data[$k+1][]= $export_data[$k+1][]=$export_data[$k+1][]=
                        $export_data[$k+1][]=$export_data[$k+1][]=$export_data[$k+1][]=$export_data[$k+1][]='';
                    };*/

                    // 订单基本信息
                    $export_data[$k+1][] = $v['order_sn']."\t";
                    $export_data[$k+1][] = $v['fx_order_id']."\t";
                    $export_data[$k+1][] = $v['order_amount'];
                    $export_data[$k+1][] = $v['shipping_fee'];
                    $export_data[$k+1][] = $commis;
                    $export_data[$k+1][] = date('Y-m-d',$v['add_time']);
                    $export_data[$k+1][] = date('Y-m-d',$v['finnshed_time']);
                    $export_data[$k+1][] = $v['store_name'];
                    $export_data[$k+1][] = $v['store_id'];
                    $export_data[$k+1][] = htmlspecialchars($v['buyer_name'])."\t";
                    $export_data[$k+1][] = $v['buyer_id'];
                    $export_data[$k+1][] = $v['rpt_bill'];

                    // 订单商品信息
                    $export_data[$k+1][] = $v1['goods_id'];
                    $export_data[$k+1][] = $v1['goods_name'];
                    $export_data[$k+1][] = $v1['goods_price'];
                    $export_data[$k+1][] = $v1['goods_num'];
                    $export_data[$k+1][] = ncPriceFormat($v1['goods_pay_price']);
                    /** @var BillService $billService */
                    $billService = Service('Bill');
                    $ver1 = $billService->getCommVer1Time();
                    if($v['manage_type'] == 'platform'){
                        $export_data[$k+1][] = $v1['commis_rate'];
                        $comprice = $export_data[$k+1][] = $v['finnshed_time']>$ver1?
                            ncPriceFormat(($v1['goods_pay_price']+ $v1['rpt_bill'])* $v1['commis_rate'] / 100):
                            ncPriceFormat($v1['goods_pay_price']* $v1['commis_rate'] / 100);
                        $export_data[$k+1][] =  ncPriceFormat($v1['goods_pay_price'] + $item_shipping_fee - $comprice + $v1['rpt_bill']);
                    }else{
                        $export_data[$k+1][] = 0;
                        $export_data[$k+1][] = 0;
                        $export_data[$k+1][] = ncPriceFormat($v1['goods_cost']);
                    }
                    $export_data[$k+1][] = ncPriceFormat($v1['rpt_bill']);
                    $export_data[$k+1][] = ncPriceFormat($v1['goods_cost'] / $v1['goods_num'], 4);//$v1['goods_cost']/$;

                    if($hango) {
                        $export_data[$k+1][] = orderFrom($v['order_from'],$v['buyer_name']);
                        $export_data[$k+1][] = $v['fx_order_id']."\t";
                        $export_data[$k+1][] = $logTime."\t";
                    }

                    //$goods_string .= $v['goods_name'].'|单价:'.$v['goods_price'].'|数量:'.$v['goods_num'].'|实际支付:'.$v['goods_pay_price'].'|佣金比例:'.$v['commis_rate'].'%';
                    $k++;$goodsCount++;
                }

            }else{$k++;}

            /*if (is_array($v['extend_order_goods'])) {
                foreach ($v['extend_order_goods'] as $v) {
                    $goods_string .= $v['goods_name'].'|单价:'.$v['goods_price'].'|数量:'.$v['goods_num'].'|实际支付:'.$v['goods_pay_price'].'|佣金比例:'.$v['commis_rate'].'%';
                }
            }
            $export_data[$k+1][] = $goods_string;
            $k++;*/
        }
        $count = count($export_data);
        $export_data[$count][] = '合计';
        $export_data[$count][] = "\t";
        $export_data[$count][] = '';
        $export_data[$count][] = '';
        $export_data[$count][] = $order_totals;
        $export_data[$count][] = $shipping_totals;
        $export_data[$count][] = $commis_totals;
        $export_data[$count][] = $rpt_totals;
        $csv = new Csv();
        $export_data = $csv->charset($export_data,CHARSET,'gbk');
        $csv->filename = 'order-detail';
        $csv->export($export_data);
    }


    public function export_costOp(){
        if (!preg_match('/^\d+$/',$_GET['ob_id'])) {
            showMessage('参数错误','','html','error');
        }

        /** @var billModel $model_bill */
        $model_bill = Model('bill');
        $bill_info = $model_bill->getOrderBillInfo(array('ob_id'=>intval($_GET['ob_id']),'ob_store_id'=>$_SESSION['store_id']));
        if (!$bill_info){
            showMessage('参数错误','','html','error');
        }

        /** @var store_costModel $model_store_cost */
        $model_store_cost = Model('store_cost');
        $condition = array();
        $condition['cost_time'] = array('between',"{$bill_info['ob_start_date']},{$bill_info['ob_end_date']}");
        $condition['cost_store_id'] = $bill_info['ob_store_id'];
        $store_cost_list = $model_store_cost->getStoreCostList($condition);
        $pageSize = 10000;
        //$pageSize = self::EXPORT_SIZE;
        if (!is_numeric($_GET['curpage'])){
            $count = $model_store_cost->where($condition)->count();
            $array = array();
            if ($count > $pageSize ){
                //显示下载链接
                $page = ceil($count/$pageSize);
                for ($i=1;$i<=$page;$i++){
                    $limit1 = ($i-1)*$pageSize + 1;
                    $limit2 = $i*$pageSize > $count ? $count : $i*$pageSize;
                    $array[$i] = $limit1.' ~ '.$limit2 ;
                }
                Tpl::output('list',$array);
                Tpl::output('murl','index.php?act=store_bill&op=show_bill&ob_id='.$_GET['ob_id']);
                Tpl::showpage('store_export.excel');
                exit();
            }else{
                //如果数量小，直接下载
                $data = $model_store_cost->where($condition)->limit('0,'.$pageSize)->select();//getStoreCostList($condition,'','cost_id desc');
            }
        }else{
            //下载
            $limit1 = ($_GET['curpage']-1) * $pageSize;
            $limit2 = $pageSize;
            $data = $model_store_cost->where($condition)->limit("{$limit1},{$limit2}")->select();//getStoreCostList($condition,'','cost_id desc');
        }

        $fx_order_id_array = array_column($data, 'fx_order_id');

        /** @var orderModel $orderModel */
        $orderModel = Model('order');
        $orderList = $orderModel->getOrderList(array('fx_order_id' => array('in', $fx_order_id_array)),9999999,'*','order_id desc','',array('order_goods','order_common'));
        $orders = array();
        foreach ($orderList as $order){
            if($order['store_id'] == $bill_info['ob_store_id']) $orders[$order['fx_order_id']] = $order;
        }
        //订单商品表查询条件
        $order_id_array = array();
        $fenxiao_id_array = array();
        if (is_array($data)) {
            foreach ($data as $order_info) {
                $order_id_array[] = $order_info['order_id'];
                $fenxiao_id_array[] = $order_info['fx_order_id'];
            }
        }
        $order_goods_condition = array();
        $order_goods_condition['order_id'] = array('in',$order_id_array);

        $mbof = Model('b2c_order_fenxiao');
        $fenxiao_goods_condition = array();
        $fenxiao_goods_condition['orderno'] = array('in',$fenxiao_id_array);
        $log_time = array();
        $fenxiao_list = $mbof -> where( $fenxiao_goods_condition )->select();
        foreach($fenxiao_list as $v){
            $log_time[$v['orderno']] = $v['log_time'];
        }

        $export_data = array();
        $export_data[0] = array('促销名称','促销费用','分销订单号','申请日期','订单编号','订单备注','渠道名称','下单时间','发货时间','超时时间','导入时间');

        $cost_total = 0;
        $k = 0;
        foreach ($data as $v) {

            $order = false;
            if($v['fx_order_id'] && isset($orders[$v['fx_order_id']]))
                $order = $orders[$v['fx_order_id']];

            //v($order);
            $export_data[$k+1][] = $v['cost_remark']."\t";
            $cost_total += $export_data[$k+1][] = $v['cost_price'];
            $export_data[$k+1][] = $v['fx_order_id']."\t";
            $export_data[$k+1][] = date('Y-m-d',$v['cost_time']);
            $export_data[$k+1][] = $order['order_sn']."\t";
            $export_data[$k+1][] = $order['extend_order_common']['bill_remark'];


            if($v['fx_order_id'] && isset($orders[$v['fx_order_id']])){
                $export_data[$k+1][] = $order['buyer_name'];
                $export_data[$k+1][] = date('Y-m-d H:i:s',$order['add_time']);
                $export_data[$k+1][] = date('Y-m-d H:i:s',$order['extend_order_common']['shipping_time']);
                $time1 = strtotime(date('Y-m-d H:i:s',$order['add_time']));
                $time2 = strtotime(date('Y-m-d H:i:s',$order['extend_order_common']['shipping_time']));
                $time_out = $time2 - $time1 - 48*3600;
                if( $time_out > 0 ){
                    $export_data[$k+1][] = date('Y-m-d H:i:s',$time_out);
                } else {
                    $export_data[$k+1][] = '未超时';
                }
                $export_data[$k+1][] = date('Y-m-d H:i:s',$log_time[$v['fx_order_id']]);
            }
            $k++;
        }
        $count = count($export_data);
        $export_data[$count][] = '合计';
        $export_data[$count][] = $cost_total;
        $csv = new Csv();
        $export_data = $csv->charset($export_data,CHARSET,'gbk');
        $csv->filename = 'cost-detail';
        $csv->export($export_data);
    }

    /**
     * 导出结算未退定金预定订单明细CSV
     *
     */
    public function export_bookOp(){
        if (!preg_match('/^\d+$/',$_GET['ob_id'])) {
            showMessage('参数错误','','html','error');
        }

        $model_bill = Model('bill');
        $bill_info = $model_bill->getOrderBillInfo(array('ob_id'=>intval($_GET['ob_id']),'ob_store_id'=>$_SESSION['store_id']));
        if (!$bill_info){
            showMessage('参数错误','','html','error');
        }
    
        $model_order = Model('order');
        $model_order_book = Model('order_book');
        $condition = array();
        if (preg_match('/^\d{8,20}$/',$_GET['query_order_no'])) {
            $order_info = $model_order->getOrderInfo(array('order_sn'=>$_GET['query_order_no']));
            if ($order_info) {
                $condition['book_order_id'] = $order_info['order_id'];
            } else {
                $condition['book_order_id'] = 0;
            }
        }
        $condition['book_store_id'] = $_SESSION['store_id'];
        $condition['book_cancel_time'] = array('between',"{$bill_info['ob_start_date']},{$bill_info['ob_end_date']}");

        if (!is_numeric($_GET['curpage'])){
            $count = $model_order_book->getOrderBookCount($condition);
            $array = array();
            if ($count > self::EXPORT_SIZE ){
                //显示下载链接
                $page = ceil($count/self::EXPORT_SIZE);
                for ($i=1;$i<=$page;$i++){
                    $limit1 = ($i-1)*self::EXPORT_SIZE + 1;
                    $limit2 = $i*self::EXPORT_SIZE > $count ? $count : $i*self::EXPORT_SIZE;
                    $array[$i] = $limit1.' ~ '.$limit2 ;
                }
                Tpl::output('list',$array);
                Tpl::output('murl','index.php?act=store_bill&op=show_bill&ob_id='.$_GET['ob_id']);
                Tpl::showpage('store_export.excel');
                exit();
            }
            $limit = false;
        }else{
            //下载
            $limit1 = ($_GET['curpage']-1) * self::EXPORT_SIZE;
            $limit2 = self::EXPORT_SIZE;
            $limit = "{$limit1},{$limit2}";
        }

        $order_book_list = $model_order_book->getOrderBookList($condition,'','book_id desc','*',$limit);

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
        $data = $model_order->getOrderList(array('order_id'=>array('in',$order_id_array)),'','*','order_id desc');
        
        $export_data = array();
        $export_data[0] = array('订单编号','下单时间','取消时间','订单金额','运费','未退定金','商家','商家编号','买家','买家编号');
        $order_amount = 0;
        $deposit_amount = 0;
        $k = 0;
        foreach ($data as $v) {
            //该订单算佣金
            $export_data[$k+1][] = $v['order_sn']."\t";
            $export_data[$k+1][] = date('Y-m-d',$v['add_time']);
            $export_data[$k+1][] = date('Y-m-d',$tmp_book[$v['order_id']]['book_cancel_time']);
            $order_amount += $export_data[$k+1][] = $v['order_amount'];
            $export_data[$k+1][] = $v['shipping_fee'];
            $deposit_amount += $export_data[$k+1][] = ncPriceFormat($tmp_book[$v['order_id']]['book_real_pay']);
            $export_data[$k+1][] = $v['store_name'];
            $export_data[$k+1][] = $v['store_id'];
            $export_data[$k+1][] = $v['buyer_name'];
            $export_data[$k+1][] = $v['buyer_id'];
            $k++;
        }
        $count = count($export_data);
        $export_data[$count][] = '合计';
        $export_data[$count][] = '';
        $export_data[$count][] = $order_amount;
        $export_data[$count][] = '';
        $export_data[$count][] = '';
        $export_data[$count][] = $deposit_amount;
        $csv = new Csv();
        $export_data = $csv->charset($export_data,CHARSET,'gbk');
        $csv->filename = 'order-book-list';
        $csv->export($export_data);
    }

    /**
     * 导出结算退单明细CSV
     *
     */
    public function export_refund_orderOp(){
        if (!preg_match('/^\d+$/',$_GET['ob_id'])) {
            showMessage('参数错误','','html','error');
        }
        $model_bill = Model('bill');
        $bill_info = $model_bill->getOrderBillInfo(array('ob_id'=>intval($_GET['ob_id']),'ob_store_id'=>$_SESSION['store_id']));
        if (!$bill_info){
            showMessage('参数错误','','html','error');
        }
        $hango = in_array($_SESSION['store_id'],array(10,15,48,138,171,174,213));

        $model_refund = Model('refund_return');
        $condition = array();
        $condition['seller_state'] = 2;
        $condition['store_id'] = $_SESSION['store_id'];
        $condition['goods_id'] = array('gt',0);
        $condition['filter_status']='0';
        $if_start_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_start_date']);
        $if_end_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_end_date']);
        $start_unixtime = $if_start_date ? strtotime($_GET['query_start_date']) : null;
        $end_unixtime = $if_end_date ? strtotime($_GET['query_end_date']) : null;
        if ($if_start_date || $if_end_date) {
            $condition['admin_time'] = array('time',array($start_unixtime,$end_unixtime));
        } else {
            $condition['admin_time'] = array('between',"{$bill_info['ob_start_date']},{$bill_info['ob_end_date']}");
        }

        if (!is_numeric($_GET['curpage'])){
            $count = $model_refund->getRefundReturn($condition);
            $array = array();
            if ($count > self::EXPORT_SIZE ){   //显示下载链接
                $page = ceil($count/self::EXPORT_SIZE);
                for ($i=1;$i<=$page;$i++){
                    $limit1 = ($i-1)*self::EXPORT_SIZE + 1;
                    $limit2 = $i*self::EXPORT_SIZE > $count ? $count : $i*self::EXPORT_SIZE;
                    $array[$i] = $limit1.' ~ '.$limit2 ;
                }
                Tpl::output('list',$array);
                Tpl::output('murl','index.php?act=store_bill&op=show_bill&query_type=refund&ob_id='.$_GET['ob_id']);
                Tpl::showpage('store_export.excel');
                exit();
            }else{
                //如果数量小，直接下载
				//$field = C('tablepre').'refund_return.*,ROUND(refund_amount*commis_rate/100,2) as commis_amount';
                $field = $bill_info['ob_store_manage_type']=='platform'?
                    C('tablepre').'refund_return.*,ROUND(refund_amount*commis_rate/100,2) as commis_amount':
                    C('tablepre').'refund_return.*,ROUND(refund_amount*cost_rate*commis_rate/10000,2) as commis_amount';
                $data = $model_refund->getRefundReturnList($condition,'',$field,self::EXPORT_SIZE);
            }
        }else{
            //下载
            $limit1 = ($_GET['curpage']-1) * self::EXPORT_SIZE;
            $limit2 = self::EXPORT_SIZE;
			//$field = C('tablepre').'refund_return.*,ROUND(refund_amount*commis_rate/100,2) as commis_amount';
            $field = $bill_info['ob_store_manage_type']=='platform'?
                C('tablepre').'refund_return.*,ROUND(refund_amount*commis_rate/100,2) as commis_amount':
                C('tablepre').'refund_return.*,ROUND(refund_amount*cost_rate*commis_rate/10000,2) as commis_amount';
            $data = $model_refund->getRefundReturnList(condition,'',$field ,"{$limit1},{$limit2}");
        }
        $hango = in_array($_SESSION['store_id'],array(10,15,48,138,171,174,213));
        $order_id_array = array_column($data,'order_id');
        /** @var orderModel $orderGoodsModel */
        $orderGoodsModel = Model('order');
        $orderList = $orderGoodsModel->getOrderList(array('order_id'=>array('in',$order_id_array)),999999,'*');
        $orderList = array_under_reset($orderList,'order_id');
        $orderGoodsList = $orderGoodsModel->getOrderGoodsList(array('order_id'=>array('in',$order_id_array)),'*',999999);
        $orderGoodsList = array_under_reset($orderGoodsList,'order_id',2);
        if (is_array($data) && count($data) == 1 && $data[0]['refund_id'] == '') {
            $refund_list = array();
        }
        $export_data = array();
        $export_data[0] = array('结算单号','退单编号','订单编号','类型','退款原因','备注','退款类别','退款日期','商家','商家编号','买家','买家编号','退单商品','商品编号','退单商品数','退单金额','退还佣金','退还红包','退还成本');
        if($hango){
            $export_data[0][] = '导入时间';
            $export_data[0][] = '分销订单号';
        }
        $refund_amount = 0;
        $commis_totals = $rpt_totals = $cost_totals = 0;
        $rpt_amount = 0;
        $k = 0;
        foreach ($data as $v) {

            $recList = $orderGoodsList[$v['order_id']];
            $orderGoods = array(
                'goods_cost'=>0,
                'commis_rate'=>0,
                'goods_pay_price'=>1,
            );
            foreach ($recList as $rec){
                if($rec['goods_id'] == $v['goods_id'])
                {
                    $orderGoods = $rec;
                    break;
                }
            }
            $order = $orderList[$v['order_id']];

            $refundAmount = $v['refund_amount_bill'] == -1?$v['refund_amount']:$v['refund_amount_bill'];

            /** @var integer $refund_price */
            $refund_price = $bill_info['ob_store_manage_type']=='platform'?
                ncPriceFormat($refundAmount):
                ncPriceFormat($refundAmount*$orderGoods['goods_cost']/$orderGoods['goods_pay_price']);
            /** @var integer $commis_amount */
            $commis_amount = $bill_info['ob_store_manage_type']=='platform'?
                ncPriceFormat($refundAmount*$orderGoods['commis_rate']/100):0;

            //退还的红包值(平台模式，且全额退款，红包才全额退还)
            // 期望方式，但目前不是这样计算的 ：sprintf("%.2f", ($refund_amount/$ogInfo['goods_pay_price'])*$ogInfo['rpt_amount'] );
            if ($bill_info['ob_store_manage_type']=='platform' && $refundAmount == $orderGoods['goods_pay_price']) {
                $rpt_amount = $orderGoods['rpt_bill'];
            } else {
                $rpt_amount = 0;
            }

            //平台和共建/自营 的 最终成本价计算
            if ($bill_info['ob_store_manage_type']=='platform') {
                $cost = $refundAmount - $commis_amount + $rpt_amount ; //总退还值
            } else {
                $cost =  ($refundAmount/$orderGoods['goods_pay_price'])*$orderGoods['goods_cost']; //总退还值
            }
            $cost = ncPriceFormat($cost);
            $export_data[$k+1][] = $bill_info['ob_id']."\t";
            $export_data[$k+1][] = $v['refund_sn']."\t";
            $export_data[$k+1][] = $v['order_sn']."\t";
            $export_data[$k+1][] = str_replace(array(1,2),array('退款','退货'),$v['refund_type']);
            $export_data[$k+1][] = $v['reason_seller_info'];
            $export_data[$k+1][] = preg_replace('/[,\r\n]+/i','',$v['seller_message']);   //备注
            $export_data[$k+1][] = ($v['order_lock'] == 2)?'售前退款':'售后退款';
            $export_data[$k+1][] = date('Y-m-d',$v['admin_time']);
            $export_data[$k+1][] = $v['store_name'];
            $export_data[$k+1][] = $v['store_id'];
            $export_data[$k+1][] = htmlspecialchars($v['buyer_name']);
            $export_data[$k+1][] = $v['buyer_id'];

            //退单商品
            $export_data[$k+1][] = $orderGoods['goods_name'];
            //商品编号
            $export_data[$k+1][] = $orderGoods['goods_id'];
            //退单商品数
            $export_data[$k+1][] = $v['goods_num'];
            //退单金额
            $refund_amount += $export_data[$k+1][] = $refundAmount;
            //退还佣金
            $commis_totals += ncPriceFormat($export_data[$k+1][] = $commis_amount);
            //退还红包
            $rpt_totals += ncPriceFormat($export_data[$k+1][] = $rpt_amount);
            //退还成本
            $cost_totals += $export_data[$k+1][] = ncPriceFormat($cost);
            if($hango){
                $export_data[$k+1][] =  $v['fenxiao_time']?date('Y-m-d H:i:s',$v['fenxiao_time']):' - ';
                $export_data[$k+1][] =  $order['fx_order_id']."\t";
            }
            $k++;
        }
        $count = count($export_data);
        $export_data[$count][] = '';
        $export_data[$count][] = '合计';
        $export_data[$count][] = $export_data[$count][] = $export_data[$count][] = $export_data[$count][] =
        $export_data[$count][] = $export_data[$count][] = $export_data[$count][] =
        $export_data[$count][] = $export_data[$count][] = $export_data[$count][] =
        $export_data[$count][] = $export_data[$count][] = "\t";
        $export_data[$count][] = $refund_amount;
        $export_data[$count][] = $commis_totals;
        $export_data[$count][] = $rpt_amount;
        $csv = new Csv();
        $export_data = $csv->charset($export_data,CHARSET,'gbk');
        $csv->filename = 'order-refund-detail';
        $csv->export($export_data);
    }

    /**
     * 用户中心右边，小导航
     *
     * @param string    $menu_type  导航类型
     * @param string    $menu_key   当前导航的menu_key
     * @return
     */
    private function profile_menu($menu_type,$menu_key='') {
        $menu_array = array();
        switch ($menu_type) {
            case 'list':
                $menu_array = array(
                1=>array('menu_key'=>'list','menu_name'=>'实物订单结算', 'menu_url'=>'index.php?act=bill&op=list'),
                );
                break;
            case 'show':
                $menu_array = array(
                array('menu_key'=>'order_list','menu_name'=>'订单列表', 'menu_url'=>'index.php?act=store_bill&op=show_bill&ob_id='.$_GET['ob_id']),
                array('menu_key'=>'refund_list','menu_name'=>'退款订单','menu_url'=>'index.php?act=store_bill&op=show_bill&type=refund&ob_id='.$_GET['ob_id']),
                array('menu_key'=>'cost_list','menu_name'=>'促销费用','menu_url'=>'index.php?act=store_bill&op=show_bill&type=cost&ob_id='.$_GET['ob_id']),
                array('menu_key'=>'log','menu_name'=>'调整记录','menu_url'=>'index.php?act=store_bill&op=show_bill&type=log&ob_id='.$_GET['ob_id']),
                array('menu_key'=>'pay_log','menu_name'=>'结算记录','menu_url'=>'index.php?act=store_bill&op=show_bill&type=pay_log&ob_id='.$_GET['ob_id']),
                );
                if (floatval($this->_bill_info['ob_order_book_totals']) > 0) {
                    array_push($menu_array,array('menu_key'=>'book_list','menu_name'=>'未退定金', 'menu_url'=>'index.php?act=store_bill&op=show_bill&type=book&ob_id='.$_GET['ob_id']));
                }
                break;
        }
        Tpl::output('member_menu',$menu_array);
        Tpl::output('menu_key',$menu_key);
    }



    /**
     * @param $order  array
     * @param $orderGoodsList array
     * @return int
     */
    private function getOrderCommis($order,$orderGoodsList)
    {
        $commis = 0;
        /** @var BillService $billService */
        $billService = Service('Bill');

        $ver1 = $billService->getCommVer1Time();
        foreach ($orderGoodsList as $orderGoods) {
            $commis += $order['finnshed_time']<$ver1?
                $orderGoods['goods_pay_price'] * $orderGoods['commis_rate']:
                ($orderGoods['goods_pay_price'] + $orderGoods['rpt_bill'])* $orderGoods['commis_rate']
            ;
        }
        return ncPriceFormat($commis/100);
    }
}
