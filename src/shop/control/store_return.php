<?php
/**
 * 卖家退货
 *
 *
 *
 * * @汉购网 (c) 2015-2018 ShopWWI Inc. (http://www.hangowa.com)
 * @license    http://www.shopwwi.c om
 * @link       交流群号：
 * @since      汉购网提供技术支持 授权请购买shopnc授权
 */



defined('ByShopWWI') or exit('Access Invalid!');

class store_returnControl extends BaseSellerControl {
    public function __construct() {
        parent::__construct();
        $model_refund = Model('refund_return');
        $model_refund->getRefundStateArray();
        Language::read('member_store_index');
    }
    /**
     * 退货记录列表页
     *
     */
    public function indexOp() {
        $model_refund = Model('refund_return');
        $condition = array();
        $condition['store_id'] = $_SESSION['store_id'];

        $keyword_type = array('order_sn','refund_sn','buyer_name');
        if (trim($_GET['key']) != '' && in_array($_GET['type'],$keyword_type)) {
            $type = $_GET['type'];
            $condition[$type] = array('like','%'.$_GET['key'].'%');
        }
        if (trim($_GET['add_time_from']) != '' || trim($_GET['add_time_to']) != '') {
            $add_time_from = strtotime(trim($_GET['add_time_from']));
            $add_time_to = strtotime(trim($_GET['add_time_to']));
            if ($add_time_from !== false || $add_time_to !== false) {
                $condition['add_time'] = array('time',array($add_time_from,$add_time_to));
            }
        }
        
        //分销渠道的搜索
        if($_GET['type']=='buyer_name' && trim($_GET['key'])=='分销渠道'){
        	$fenxiao_service = Service("Fenxiao") ;
        	$fx_members = $fenxiao_service -> getFenxiaoMembers();
        	$fx_members_keys = array_keys($fx_members);
        	unset($condition['buyer_name']);
        	$condition['buyer_id'] = array('in' , $fx_members_keys);
        }
        
        $seller_state = intval($_GET['state']);
        if ($seller_state > 0) {
            $condition['seller_state'] = $seller_state;
        }
        $order_lock = intval($_GET['lock']);
        if ($order_lock != 1) {
            $order_lock = 2;
        }
        $_GET['lock'] = $order_lock;
        $condition['order_lock'] = $order_lock;

        if ($seller_state == 1) {
            $order = 'refund_id';
        }else{
            $order = 'refund_id DESC';
        }

        $return_list = $model_refund->getReturnList($condition,10,$order);
        Tpl::output('return_list',$return_list);
        Tpl::output('show_page',$model_refund->showpage());
        self::profile_menu('return',$order_lock);
        Tpl::showpage('store_return');
    }
    /**
     * 退货审核页
     *
     */
    public function editOp() {
        $model_refund = Model('refund_return');
        $condition = array();
        $condition['store_id'] = $_SESSION['store_id'];
        $condition['refund_id'] = intval($_GET['return_id']);
        $return_list = $model_refund->getReturnList($condition);
        $reload = 'index.php?act=store_return&lock=1';
        //判断是否为分销的售前订单
        if(empty($return_list[0]['is_operate'])){
        	showDialog(Language::get('fx_not_after'),$reload,'error');
        }
        $return = $return_list[0];
        if (chksubmit()) {
            $reload = 'index.php?act=store_return&lock=1';
            if ($return['order_lock'] == '2') {
                $reload = 'index.php?act=store_return&lock=2';
            }
            if ($return['seller_state'] != '1') {//检查状态,防止页面刷新不及时造成数据错误
                showDialog(Language::get('wrong_argument'),$reload,'error');
            }
            $order_id = $return['order_id'];
            $refund_array = array();
            $refund_array['seller_time'] = time();
            $refund_array['seller_state'] = $_POST['seller_state'];//卖家处理状态:1为待审核,2为同意,3为不同意
            $refund_array['seller_message'] = $_POST['seller_message'];

            if ($refund_array['seller_state'] == '2' && empty($_POST['return_type'])) {
                $refund_array['return_type'] = '2';//退货类型:1为不用退货,2为需要退货
            } elseif ($refund_array['seller_state'] == '3') {
                $refund_array['refund_state'] = '3';//状态:1为处理中,2为待管理员处理,3为已完成
            } else {
                $refund_array['seller_state'] = '2';
                $refund_array['refund_state'] = '2';
                $refund_array['return_type'] = '1';//选择弃货
            }
            $state = $model_refund->editRefundReturn($condition, $refund_array);
            if ($state) {
                if ($refund_array['seller_state'] == '3' && $return['order_lock'] == '2') {
                    $model_refund->editOrderUnlock($order_id);//订单解锁
                }
                $this->recordSellerLog('退货处理，退货编号：'.$return['refund_sn']);

                // 发送买家消息
                $param = array();
                $param['code'] = 'refund_return_notice';
                $param['member_id'] = $return['buyer_id'];
                $param['param'] = array(
                    'refund_url' => urlShop('member_return', 'view', array('return_id' => $return['refund_id'])),
                    'refund_sn' => $return['refund_sn']
                );
                QueueClient::push('sendMemberMsg', $param);

                showDialog(Language::get('nc_common_save_succ'),$reload,'succ');
            } else {
                showDialog(Language::get('nc_common_save_fail'),$reload,'error');
            }
        }
        Tpl::output('return',$return);
        $info['buyer'] = array();
        if(!empty($return['pic_info'])) {
            $info = unserialize($return['pic_info']);
        }
        $condition = array();
        $condition['store_id'] = $_SESSION['store_id'];
        $condition['is_receipt'] = 1;
        $model_daddress = Model('daddress');
        $receipt_list = $model_daddress->getAddressList($condition);
        Tpl::output('receipt_list',$receipt_list);
        Tpl::output('pic_list',$info['buyer']);
        $model_member = Model('member');
        $member = $model_member->getMemberInfoByID($return['buyer_id']);
        Tpl::output('member',$member);
        $condition = array();
        $condition['order_id'] = $return['order_id'];
        $model_refund->getRightOrderList($condition, $return['order_goods_id']);
        Tpl::showpage('store_return_edit');
    }
    /**
     * 收货
     *
     */
    public function receiveOp() {
        $model_refund = Model('refund_return');
        $model_trade = Model('trade');
        $condition = array();
        $condition['store_id'] = $_SESSION['store_id'];
        $condition['refund_id'] = intval($_GET['return_id']);
        $return_list = $model_refund->getReturnList($condition);
        $return = $return_list[0];
        Tpl::output('return',$return);
        $return_delay = $model_trade->getMaxDay('return_delay');//发货默认5天后才能选择没收到
        $delay_time = time()-$return['delay_time']-60*60*24*$return_delay;
        Tpl::output('return_delay',$return_delay);
        Tpl::output('return_confirm',$model_trade->getMaxDay('return_confirm'));//卖家不处理收货时按同意并弃货处理
        Tpl::output('delay_time',$delay_time);
        if (chksubmit()) {
            if ($return['seller_state'] != '2' || $return['goods_state'] != '2') {//检查状态,防止页面刷新不及时造成数据错误
                showDialog(Language::get('wrong_argument'),'reload','error','CUR_DIALOG.close();');
            }
            $refund_array = array();
            if ($_POST['return_type'] == '3' && $delay_time > 0) {
                $refund_array['goods_state'] = '3';
                $refund_array['seller_state'] = '3';
                $refund_array['order_lock'] ='1';
                $model_refund->editOrderUnlock($return_list['order_sn']);//订单解锁
            } else {
                $refund_array['receive_time'] = time();
                $refund_array['receive_message'] = '确认收货完成';
                $refund_array['refund_state'] = '2';//状态:1为处理中,2为待管理员处理,3为已完成
                $refund_array['goods_state'] = '4';
            }
            $state = $model_refund->editRefundReturn($condition, $refund_array);
            if ($state) {
                $this->recordSellerLog('退货确认收货，退货编号：'.$return['refund_sn']);

                // 发送买家消息
                $param = array();
                $param['code'] = 'refund_return_notice';
                $param['member_id'] = $return['buyer_id'];
                $param['param'] = array(
                    'refund_url' => urlShop('member_return', 'view', array('return_id' => $return['refund_id'])),
                    'refund_sn' => $return['refund_sn']
                );
                QueueClient::push('sendMemberMsg', $param);

                showDialog(Language::get('nc_common_save_succ'),'reload','succ','CUR_DIALOG.close();');
            } else {
                showDialog(Language::get('nc_common_save_fail'),'reload','error','CUR_DIALOG.close();');
            }
        }
        $express_list  = rkcache('express',true);
        if ($return['express_id'] > 0 && !empty($return['invoice_no'])) {
            Tpl::output('e_name',$express_list[$return['express_id']]['e_name']);
            Tpl::output('e_code',$express_list[$return['express_id']]['e_code']);
        }
        Tpl::showpage('store_return_receive','null_layout');
    }
    /**
     * 退货记录查看页
     *
     */
    public function viewOp() {
        $model_refund = Model('refund_return');
        $condition = array();
        $condition['store_id'] = $_SESSION['store_id'];
        $condition['refund_id'] = intval($_GET['return_id']);
        $return_list = $model_refund->getReturnList($condition);
        $return = $return_list[0];
        Tpl::output('return',$return);
        $express_list  = rkcache('express',true);
        if ($return['express_id'] > 0 && !empty($return['invoice_no'])) {
            Tpl::output('e_name',$express_list[$return['express_id']]['e_name']);
            Tpl::output('e_code',$express_list[$return['express_id']]['e_code']);
        }
        $info['buyer'] = array();
        if(!empty($return['pic_info'])) {
            $info = unserialize($return['pic_info']);
        }
        Tpl::output('pic_list',$info['buyer']);
        $model_member = Model('member');
        $member = $model_member->getMemberInfoByID($return['buyer_id']);
        Tpl::output('member',$member);
        $condition = array();
        $condition['order_id'] = $return['order_id'];
        $model_refund->getRightOrderList($condition, $return['order_goods_id']);
        Tpl::showpage('store_return_view');
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
            case 'return':
                $menu_array = array(
                    array('menu_key'=>'2','menu_name'=>'售前退货',  'menu_url'=>'index.php?act=store_return&lock=2'),
                    array('menu_key'=>'1','menu_name'=>'售后退货','menu_url'=>'index.php?act=store_return&lock=1')
                );
                break;
        }
        Tpl::output('member_menu',$menu_array);
        Tpl::output('menu_key',$menu_key);
    }



    public function exportCvsOp(){
        /** @var refund_returnModel $model_refund */
        $model_refund = Model('refund_return');
        $condition = array();
        $condition['store_id'] = $_SESSION['store_id'];

        $keyword_type = array('order_sn','refund_sn','buyer_name');
        if (trim($_GET['key']) != '' && in_array($_GET['type'],$keyword_type)) {
            $type = $_GET['type'];
            $condition[$type] = array('like','%'.$_GET['key'].'%');
        }
        if (trim($_GET['add_time_from']) != '' || trim($_GET['add_time_to']) != '') {
            $add_time_from = strtotime(trim($_GET['add_time_from']));
            $add_time_to = strtotime(trim($_GET['add_time_to']));
            if ($add_time_from !== false || $add_time_to !== false) {
                $condition['add_time'] = array('time',array($add_time_from,$add_time_to));
            }
        }

        //分销渠道的搜索
        if($_GET['type']=='buyer_name' && trim($_GET['key'])=='分销渠道'){
            $fenxiao_service = Service("Fenxiao") ;
            $fx_members = $fenxiao_service -> getFenxiaoMembers();
            $fx_members_keys = array_keys($fx_members);
            unset($condition['buyer_name']);
            $condition['buyer_id'] = array('in' , $fx_members_keys);
        }

        $seller_state = intval($_GET['state']);
        if ($seller_state > 0) {
            $condition['seller_state'] = $seller_state;
        }
        $order_lock = intval($_GET['lock']);
        if ($order_lock != 1) {
            $order_lock = 2;
        }
        $_GET['lock'] = $order_lock;
        $condition['order_lock'] = $order_lock;
        $refund_list = $model_refund->getReturnList($condition);
//        if(empty($refund_list)){
//            echo json_encode(array('state'=>1,'msg'=>'所填筛选条件没有数据可供导出！'));
//            exit();
//        }
        /** 查找全额退款的订单商品 */
        $oids = array() ;//全额退款的订单号
        foreach ($refund_list as $refund_info) {
            if( $refund_info['goods_id'] > 0 ) continue ;
            $oids [] = $refund_info['order_id'] ;
        }
        $order_goods = array() ;
        if( !empty($oids) ) {
            $ogWhere['order_id'] = array('in', $oids) ;
            $list = Model('order')->getOrderGoodsList( $ogWhere );
            foreach ($list as $og) {
                $order_goods[$og['order_id']][] = $og ;
            }
        }
        $this->createCsv($refund_list, $order_goods);
    }

    /**
     * 生成csv文件
     */
    private function createCsv($refund_list, $order_goods) {
        $model_refund = Model('refund_return');
        $seller_reason=TModel('refund_reason_seller');
        $oids = $list = array();
        $pic_base_url = UPLOAD_SITE_URL.'/'.ATTACH_PATH.'/refund/';
        header("Content-Type: application/vnd.ms-excel; charset=GBK");
        foreach ($refund_list as $k => $refund_info) {
            $order_id = $refund_info['order_id'] ;
            $list[$k]['refund_sn'] = $refund_info['refund_sn']."\t";
            $list[$k]['refund_amount'] = ncPriceFormat($refund_info['refund_amount']);
            if(!empty($refund_info['pic_info'])) {
                $info = unserialize($refund_info['pic_info']);
                if (is_array($info) && !empty($info['buyer'])) {
                    foreach($info['buyer'] as $pic_name) {
                        $list[$k]['pic_info'] .= $pic_base_url.$pic_name.'|';
                    }
                    $list[$k]['pic_info'] = trim($list[$k]['pic_info'],'|');
                }
            }
            if (empty($list[$k]['pic_info'])) $list[$k]['pic_info'] = '';
            $list[$k]['is_aftersale'] = $refund_info['is_aftersale'];
            $list[$k]['buyer_message'] = $refund_info['buyer_message'];
            $list[$k]['add_times'] = date('Y-m-d H:i:s',$refund_info['add_time']);
            //$list[$k]['goods_name'] = $refund_info['goods_name'];
            $list[$k]['goods_name'] = !empty($refund_info['goods_id']) ? $refund_info['goods_name'] : implode(" ||| ", array_column($order_goods[$order_id], 'goods_name')) ;
            $state_array = $model_refund->getRefundStateArray('seller');
            $list[$k]['seller_state'] = $state_array[$refund_info['seller_state']];
            $admin_array = $model_refund->getRefundStateArray('admin');
            $list[$k]['refund_state'] = $refund_info['seller_state'] == 2 ? $admin_array[$refund_info['refund_state']]:'';
            $r=$seller_reason->where(array('reason_id'=>$refund_info['reason_seller_id']))->find();
            $list[$k]['seller_reason']=$r['reason_info'];
            $list[$k]['seller_message'] = $refund_info['seller_message'];
            $list[$k]['admin_message'] = $refund_info['admin_message'];
            $list[$k]['seller_times'] = !empty($refund_info['seller_time']) ? date('Y-m-d H:i:s',$refund_info['seller_time']) : '';
            if ($refund_info['goods_image'] != '') {
                $list[$k]['goods_image'] = thumb($refund_info,360);
            } else {
                $list[$k]['goods_image'] = '';
            }
            $list[$k]['goods_id'] = !empty($refund_info['goods_id']) ? $refund_info['goods_id'] : implode(" ||| ", array_column($order_goods[$order_id], 'goods_id'));
            $list[$k]['order_sn'] = $refund_info['order_sn']."\t";
            $list[$k]['buyer_name'] = $refund_info['buyer_name'];
            $list[$k]['buyer_id'] = $refund_info['buyer_id'];
            $list[$k]['store_name'] = $refund_info['store_name'];
            $list[$k]['store_id'] = $refund_info['store_id'];
            $list[$k]['refund_way'] = orderPaymentName($refund_info['refund_way']);
            $list[$k]['refund_name'] = $refund_info['refund_name'];
            $list[$k]['refund_account'] = $refund_info['refund_account'];
            $list[$k]['order_id'] = $refund_info['order_id'];
            $oids[] = $refund_info['order_id'];
        }

        if( !empty($oids) ) {
            $oWhere ['order_id'] = array( 'in', array_unique($oids) ) ;
            $orders = Model()->table('orders')->field('order_id, fx_order_id,')->where( $oWhere )->select() ;
            $rels = $orders ? array_column($orders, 'fx_order_id', 'order_id') : array() ;
            foreach ($list as $k => &$item) {
                $order_id = $item['order_id'] ;
                $item['fx_order_id'] = isset($rels[ $order_id ]) ? $rels[ $order_id ]."\t" : '' ;
                unset($item['order_id']) ;
            }
        }

        $header = array(
            'refund_sn' => '退单编号',
            'refund_amount' => '退款金额',
            'pic_info' => '申请图片',
            'is_aftersale'=>'类型',
            'buyer_message' => '申请原因',
            'add_times' => '申请时间',
            'goods_name' => '涉及商品',
            'seller_state' => '商家处理',
            'refund_state' => '平台处理',
            'seller_reason' => '商家处理原因',
            'seller_message' => '商家处理备注',
            'admin_message' => '平台处理备注',
            'seller_times' => '商家申核时间',
            'goods_image' => '商品图',
            'goods_id' => '商品ID',
            'order_sn' => '订单编号',
            'buyer_name' => '买家',
            'buyer_id' => '买家ID',
            'store_name' => '商家名称',
            'store_id'  => '商家ID',
            'refund_way'  => '退款方式',
            'refund_name'  => '收款姓名',
            'refund_account'  => '收款帐号',
            'fx_order_id' => '分销订单号',
        );
        array_unshift($list, $header);

        $csv = new Csv();
        $export_data = $csv->charset($list,CHARSET,'gbk');
        $csv->filename = $csv->charset('refund',CHARSET)."退货" . '-'.date('Y-m-d');
        $csv->export($export_data);
    }
}
