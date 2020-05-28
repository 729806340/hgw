<?php
/**
 * 退货管理
 *
 *
 *
 *
 * @汉购网提供技术支持 授权请购买shopnc授权
 * @license    http://www.hangowa.com
 * @link       交流群号：
 */



defined('ByShopWWI') or exit('Access Invalid!');
class returnControl extends SystemControl{
    const EXPORT_SIZE = 1000;
    private $links = array(
            array('url'=>'act=return&op=kefu_manage','text'=>'客服待处理'),
    		array('url'=>'act=return&op=caiwu_manage','text'=>'财务待处理'),
            array('url'=>'act=return&op=index','text'=>'所有记录'),
            array('url'=>'act=return&op=store_reject','text'=>'商家已拒绝'),
            array('url'=>'act=return&op=fxsellerdo','text'=>'商家已处理分销退款')
    );
    public function __construct(){
        parent::__construct();
        $model_refund = Model('refund_return');
        $model_refund->getRefundStateArray();
       // if ($_GET['op'] == 'index') $_GET['op'] = 'kefu_manage';
        Tpl::output('top_link',$this->sublink($this->links,$_GET['op']));
    }

    public function indexOp() {
        Tpl::setDirquna('shop');/*网 店 运 维shop wwi.com*/
        Tpl::showpage('return_all.list');
    }
    
    /**
     * 所有记录
     */
    public function return_allOp() {
        Tpl::setDirquna('shop');/*网 店 运 维shop wwi.com*/
        Tpl::showpage('return_all.list');
    }

    /**
     * 客服待处理列表
     */
    public function kefu_manageOp() {
    	Tpl::output('kefu_state','1');
		Tpl::setDirquna('shop');/*网 店 运 维shop wwi.com*/
        Tpl::showpage('return_manage.list');
    }
    /**
     * 财务待处理列表
     */
    public function caiwu_manageOp() {
    	Tpl::output('kefu_state','2');
    	Tpl::setDirquna('shop');/*网 店 运 维shop wwi.com*/
    	Tpl::showpage('return_manage.list');
    }

    public function store_rejectOp(){
        Tpl::setDirquna('shop');/*网 店 运 维shop wwi.com*/
        Tpl::showpage('store_reject.return.index');
    }

    /*恢复退款状态*/
    public function refuse_restoreOp(){
        $model_refund = Model('refund_return');
        $condition = array();
        $condition['refund_id'] = intval($_GET['refund_id']);
        $refund = $model_refund->getRefundReturnInfo($condition);
        $order_id = $refund['order_id'];
        $model_order = Model('order');
        $order_info = $model_order->getOrderInfo(array('order_id'=> $order_id),array());
        if($order_info['order_state']=="20"){
            $refund_array['order_lock']="2";
            $res=Model('order')->where(array('order_id'=>$order_id))->update(array("lock_state"=>"1"));
            if(!$res){
                echo json_encode(array('state'=>'0','msg'=>'恢复失败，订单加锁操作失败'));
                exit;
            }
        }
        $refund_array = array();
        $refund_array['add_time'] = time();
        $refund_array['seller_state'] = '1';
        $refund_array['refund_state'] = '1';
        $state = $model_refund->editRefundReturn($condition, $refund_array);
        if ($state) {
            $log = array();
            $log['order_id'] = $order_id;
            $log['log_msg'] = "后台操作商家已拒绝订单状态为处理中" ;
            $log['log_time'] = time();
            $log['log_role'] = 'admin';
            $log['log_user'] = $this->admin_info['name'];
            $log['log_orderstate'] = $order_info['order_state'];
            $model_order->addOrderLog($log);
            echo json_encode(array('state'=>1,'msg'=>"操作成功"));
        } else {
            echo json_encode(array('state'=>0,'msg'=>"操作失败"));
        }
    }

    /*同意退款*/
    public function refuse_agreeOp(){
        $model_refund = Model('refund_return');
        $condition = array();
        $condition['refund_id'] = intval($_GET['refund_id']);
        $refund = $model_refund->getRefundReturnInfo($condition);
        $order_id = $refund['order_id'];
        $agreeRefund = $model_refund->getRefundReturnInfo(array(
            'order_id'=>$order_id,
            'seller_state'=>array ('lt','3' ),
        ));
        if($agreeRefund){
            echo json_encode(array('state'=>'1','msg'=>'同意操作失败，重复维权。'));
            exit;
        }
        $model_order = Model('order');
        $order_info = $model_order->getOrderInfo(array('order_id'=> $order_id),array());
        if($order_info['order_state']==20){
            $refund_array['order_lock']="2";
            $res=Model('order')->where(array('order_id'=>$order_id))->update(array("lock_state"=>"1"));
            if(!$res){
                echo json_encode(array('state'=>'1','msg'=>'同意操作失败，订单加锁操作失败'));
                exit;
            }
        }
        $refund_array = array();
        $refund_array['seller_state'] = '2';
        $refund_array['refund_state'] = '2';
        $refund_array['agree_role'] = '1'; // 客服同意
        $state = $model_refund->editRefundReturn($condition, $refund_array);
        if ($state) {
            $log = array();
            $log['order_id'] = $order_id;
            $log['log_msg'] = "后台操作商家已拒绝订单状态由拒绝变为同意" ;
            $log['log_time'] = time();
            $log['log_role'] = 'admin';
            $log['log_user'] = $this->admin_info['name'];
            $log['log_orderstate'] = $order_info['order_state'];
            $model_order->addOrderLog($log);
            echo json_encode(array('state'=>1,'msg'=>"操作成功"));
        } else {
            echo json_encode(array('state'=>1,'msg'=>"操作失败"));
        }
    }

    public function changeDisplayOp(){
        $sn=intval($_POST['refund_id']);
        $display_state=empty($_POST['display_state'])?1:0;
        $model_refund = Model('refund_return');
        $result=$model_refund->where(array('refund_id'=>$sn))->update(array('display_state'=>$display_state));
        if($result){
            echo json_encode(array('state'=>1,'msg'=>'更新成功'));
            return true;
        }else{
            echo json_encode(array('state'=>0,'msg'=>"更新失败，请稍后再试"));
        }
    }

    /*商家已拒绝的订单*/
    public  function get_reject_xmlOp(){
        $model_refund = Model('refund_return');
        $condition = array();
        //状态:1为处理中,2为待管理员处理,3为已完成
        $condition['seller_state']="3";
        $condition['refund_state'] ="3";
        $condition['display_state']=1;
        list($condition,$order) = $this->_get_condition($condition);
        $refund_list = $model_refund->getReturnList($condition,$_POST['rp'],$order);
        $data = array();
        $data['now_page'] = $model_refund->shownowpage();
        $data['total_num'] = $model_refund->gettotalnum();
        $pic_base_url = UPLOAD_SITE_URL.'/'.ATTACH_PATH.'/refund/';
        foreach ($refund_list as $k => $refund_info) {
            $list = array();
            $list['operation'] = "<a class=\"btn orange\" onclick='refuse_restore(".$refund_info['refund_id'].")'>恢复</a>";
            $list['operation'].= "<a class=\"btn orange\" onclick='refuse_agree(".$refund_info['refund_id'].")'><i class=\"fa fa-gavel\"></i>同意</a>";
            $list['operation'] .= "<a class=\"btn green\" href=\"index.php?act=return&op=view&return_id={$refund_info['refund_id']}\">查看</a>";
            $list['operation'] .= "<a class=\"btn green\" onclick='change_dispaly_state(".$refund_info['refund_id'].",".$refund_info['display_state'].")'>隐藏</a>";
            $list['order_sn'] = $refund_info['order_sn'];
            $list['refund_sn'] = $refund_info['refund_sn'];
            $list['fx_name']="";
            $list['refund_amount'] = ncPriceFormat($refund_info['refund_amount']);
            if(!empty($refund_info['pic_info'])) {
                $info = unserialize($refund_info['pic_info']);
                if (is_array($info) && !empty($info['buyer'])) {
                    foreach($info['buyer'] as $pic_name) {
                        $list['pic_info'] .= "<a href='".$pic_base_url.$pic_name."' target='_blank' class='pic-thumb-tip' onMouseOut='toolTip()' onMouseOver='toolTip(\"<img src=".$pic_base_url.$pic_name.">\")'><i class='fa fa-picture-o'></i></a> ";
                    }
                    $list['pic_info'] = trim($list['pic_info']);
                }
            }
            if (empty($list['pic_info'])) $list['pic_info'] = '';
            $list['buyer_message'] = "<span title='{$refund_info['buyer_message']}'>{$refund_info['buyer_message']}</span>";
            $list['add_times'] = date('Y-m-d H:i:s',$refund_info['add_time']);
            $list['goods_name'] = $refund_info['goods_name'];
            $state_array = $model_refund->getRefundStateArray('seller');

            $seller_state = $refund_info['seller_state']==2&&$refund_info['agree_role']==1?4:$refund_info['seller_state'];
            $list['seller_state'] = $state_array[$seller_state];
            //$list['seller_state'] = $state_array[$refund_info['seller_state']];
            $admin_array = $model_refund->getRefundStateArray('admin');
            $list['refund_state'] = $refund_info['seller_state'] == 2 ? $admin_array[$refund_info['refund_state']]:'';
            $list['seller_message'] = "<span title='{$refund_info['seller_message']}'>{$refund_info['seller_message']}</span>";
            $list['admin_message'] = "<span title='{$refund_info['admin_message']}'>{$refund_info['admin_message']}</span>";
            if ($refund_info['goods_id'] > 0) {
                $list['goods_name'] = "<a class='open' title='{$refund_info['goods_name']}' href='". urlShop('goods', 'index', array('goods_id' => $refund_info['goods_id'])) .
                    "' target='blank'>{$refund_info['goods_name']}</a>";
            }
            $list['seller_message'] = $refund_info['seller_message'];
            $list['seller_times'] = !empty($refund_info['seller_time']) ? date('Y-m-d H:i:s',$refund_info['seller_time']) : '';
            if ($refund_info['goods_image'] != '') {
                $list['goods_image'] = "<a href='".thumb($refund_info,360)."' target='_blank' class='pic-thumb-tip' onMouseOut='toolTip()' onMouseOver='toolTip(\"<img src=".thumb($refund_info,240).">\")'><i class='fa fa-picture-o'></i></a> ";
            } else {
                $list['goods_image'] = '';
            }
            $list['goods_id'] = !empty($refund_info['goods_id']) ? $refund_info['goods_id'] : '';
            $list['order_sn'] = $refund_info['order_sn'];
            $buyer_name = $refund_info['buyer_name'] ;
            if( isset($fxMembers[ $refund_info['buyer_id'] ] ) && $fxMembers[ $refund_info['buyer_id'] ] ) {
                $buyer_name = $fxMembers[ $refund_info['buyer_id'] ] ;
            }
            $list['buyer_name'] = $buyer_name;
            $list['buyer_id'] = $refund_info['buyer_id'];
            $list['store_name'] = $refund_info['store_name'];
            $list['store_id'] = $refund_info['store_id'];
            $list['order_id'] = $refund_info['order_id'];
            $list['refund_way'] = orderPaymentName($refund_info['refund_way']);
            $list['refund_name'] = $refund_info['refund_name'];
            $list['refund_account'] = $refund_info['refund_account'];
            $list['fx_order_id']="";
            $data['list'][$refund_info['refund_id']] = $list;
            $oids[] = $refund_info['order_id'] ;
        }
        if( !empty($oids) ) {
            $oWhere ['order_id'] = array( 'in', array_unique($oids) ) ;
            $orders = Model()->table('orders')->field('order_id, fx_order_id,buyer_name')->where( $oWhere )->select() ;
            $rels = $orders ? array_column($orders, 'fx_order_id', 'order_id') : array() ;
            $list=$orders ? array_column($orders, 'buyer_name', 'order_id') : array() ;
            $member_list=Model('member_fenxiao')->getMemberFenxiao();
            $member=array_under_reset($member_list,'member_en_code');
            foreach ($data['list'] as $k => &$item) {
                $order_id = $item['order_id'] ;
                $item['fx_order_id'] = isset($rels[ $order_id ]) ? $rels[ $order_id ] : '' ;
                $item['fx_name']=isset($member[$list[$order_id]])?$member[$list[$order_id]]['member_cn_code']:'';
                unset($item['order_id']) ;
            }
        }
        exit(Tpl::flexigridXML($data));
    }
    /**
     * 新增订单退款记录
     */
    function add_returnOp()
    {
        $lang   = Language::getLangContent();
        if (chksubmit()){
            //未发货订单退款为全额退款（取消订单）
            //插入图片
            $pic_info='';
            Log::record('图片是否为空'.count($_POST['upload_img']));
            $arr = array(
                'buyer'=>array(),
            );
            if(!empty($_POST['upload_img'])){
                $files=$_POST['upload_img'];
                $arr_file=explode(",",$files);
                foreach($arr_file as $key=>$item){
                    $arr['buyer'][]=$item;
                }
                $pic_info=serialize($arr);
            }
            Log::record('pic_info:'.$pic_info);
            $_POST['inContent'] = 'true';
            $_POST['bank'] = '';
            $_POST['select_account'] = '-undefined';
            $_POST['account'] = '';
            $_POST['pay_account'] = '';
            $_POST['return_score'] = '0';
            $_POST['payment'] = $_POST['refund_way'];
            $sdf = $_POST;
            /*if (! $_POST['payment']) {
                // 退款金额不是从弹出的退款单里输入而来
                showMessage('退款方式不能为空') ;
            }*/

            // 非单品退款，针对整单所有商品退款
            if ($_POST['is_refund_all']) {
                $sdf['money'] = $_POST['refund_amount_all'];
            } else {
                $sdf['money'] = array_sum($_POST['refund_amount']);
            }
            $service = Service("Refund") ;
            if (! $service->check_order_refund($sdf['order_sn'], $sdf, $message)) {
                showMessage($message) ;
            }

            unset($sdf['inContent']);

            /**
             * 插入新的退款明细表
             */
            $distance_money = 0;
            if ($_POST['is_refund_all']) {
                $item_price_total = array_sum($_POST['product_money']);

                foreach ($_POST['goods_id'] as $key => $_goods_id) {

                    $_POST['refund_amount'][$key] = $_POST['refund_amount_all'] * $_POST['product_money'][$key]/ $item_price_total;
                    $_POST['refund_amount'][$key] = number_format($_POST['refund_amount'][$key], 2, '.', '');
                    $_POST['refund_type'][$key] = $_POST['refund_type_all'];
                    $_POST['buyer_message'][$key] = $_POST['buyer_message_all'];
                    $_POST['reason_id'][$key] = $_POST['reason_id_all'];
                }
                //因为整除多出的余下的金额
                if ($item_price_total - array_sum($_POST['product_money']) != 0 ) {
                    $distance_money = $item_price_total - array_sum($_POST['product_money']);
                }
            }
            $time = time();
            //$refund_obj = kernel::single('b2c_order_refund');
            foreach ($_POST['goods_id'] as $key => $_goods_id) {
                if (empty($_POST['refund_amount'][$key])) {
                    continue;
                }
                $_item = array(
                    'reason_id' => $_POST['reason_id'][$key],
                    'refund_type' => 2,
                    'return_type' => 2,
                    'seller_state' => 1,
                    'refund_amount' => $_POST['refund_amount'][$key] + $distance_money,
                    'goods_num' => 0,
                    'buyer_message' => $_POST['buyer_message'][$key],
                    'ordersn' => $_POST['order_sn'],
                    'goods_id' => $_goods_id,
                    'operation_type'=>1
                );
                if(!empty($pic_info)){
                    $_item['pic_info']=$pic_info;
                }
                //header("Content-type: text/html; charset=utf-8");
                $distance_money = 0;
                $params = json_decode( json_encode($_item) ) ;
                $params->admin_info = $this->admin_info;
                $res=Model('refund_return')->addApiRefund($params);
                if( $res['errorno'] != 1000 ) {
                    showMessage($res['msg']) ;
                }
            }
        }
        showMessage("退货记录保存成功", 'index.php?act=return') ;
    }
    /**
     * 退货页面
     */
    function go_returnOp(){
        $order_id = intval($_REQUEST['order_id']) ;
        $model_order = Model('order');
        $condition = array('order_id' => $order_id);
        $order_list = $model_order->getOrderList($condition, 20, '*', 'order_id desc','', array('order_common','order_goods','store'));
        if( empty($order_list) ) {
            showMessage('不存在此订单信息','index.php?act=order','html','error');
        }
        $model_refund_return = Model('refund_return');
        $order_list = $model_refund_return->getGoodsRefundList($order_list,1);//订单商品的退款退货显示

        $refund_all = $order_list[$order_id]['refund_list'][0];
        //客服组可以退款
        if (!empty($refund_all) && $refund_all['seller_state'] < 3 && $this->admin_info['gid'] != 2) {//订单全部退款商家审核状态:1为待审核,2为同意,3为不同意
            showMessage('订单已全额退款','index.php?act=order','html','error');
        }
        Tpl::output('order', $order_list[$order_id]);
        $order_state = $order_list[$order_id]['order_state'] ;
        //退款退货原因
        //$model_refund = Model('refund_return');
        if ($order_state == 30 || $order_state == 40) {
            $condition = array('is_aftersale' => 1);
        } else {
            $condition = array('is_aftersale' => 0);
        }
        $reason_list = $model_refund_return->getReasonList($condition);
        Tpl::output('reason_list', $reason_list);
        //退款方式
        $refund_way_list = array(
            'yeepay' => '易宝支付',
            'deposit' => '预存款支付',
            'offline' => '易宝支付',
            'alipay' => '易宝支付',
        ) ;
        Tpl::output('refund_way_list', $refund_way_list);
        //订单商品
        Tpl::output('order_goods_list', $order_list[$order_id]['extend_order_goods']);
        //var_dump($order_goods_list);exit;

        Tpl::setDirquna('shop');
        Tpl::showpage('return.add');
    }
    public function pic_uploadOp(){
        if (chksubmit()){
            //上传图片
            $upload = new UploadFile();
            $upload->set('thumb_width', 500);
            $upload->set('thumb_height',499);
            $upload->set('thumb_ext','_refund_small');
            $upload->set('max_size',C('image_max_filesize') ? C('image_max_filesize'):1024);
            $upload->set('ifremove',true);
            $imageinfo=getimagesize($_FILES['pic']['tmp_name']);
            $imgtype=explode('/',$imageinfo['mime']);
            $upload->set('new_ext',$imgtype[1]);
            $upload->set('default_dir',$_GET['uploadpath']);
            if (!empty($_FILES['pic']['tmp_name'])){
                $result = $upload->upfile('pic');
                if ($result){
                    exit(json_encode(array('status'=>1,'pic_info'=>$upload->thumb_image,'url'=>UPLOAD_SITE_URL.'/'.$_GET['uploadpath'].'/'.$upload->thumb_image)));
                }else {
                    exit(json_encode(array('status'=>0,'msg'=>$upload->error)));
                }
            }elseif(!empty($_FILES['pic1']['tmp_name'])){
                $result = $upload->upfile('pic1');
                if ($result){
                    exit(json_encode(array('status'=>1,'pic_info'=>$upload->thumb_image,'url'=>UPLOAD_SITE_URL.'/'.$_GET['uploadpath'].'/'.$upload->thumb_image)));
                }else {
                    exit(json_encode(array('status'=>0,'msg'=>$upload->error)));
                }
            }
        }
    }
    public function delimgOp(){
        $path=$_GET['file_name'];
        unlink($path);
        echo json_encode(array('state'=>1,'msg'=>"删除成功"));
    }
    /**
     * 待处理列表
     */
    public function get_manage_xmlOp() {
        $model_refund = Model('refund_return');
        $condition = array();
        //状态:1为处理中,2为待管理员处理,3为已完成
        $condition['refund_state'] = 2;
        $condition['kefu_state'] = isset($_GET['kefu_state']) ? intval($_GET['kefu_state']) : 1 ;

        list($condition,$order) = $this->_get_condition($condition);

        $return_list = $model_refund->getReturnList($condition,!empty($_POST['rp']) ? intval($_POST['rp']) : 15,$order);
        $oids = $data = array();
        $data['now_page'] = $model_refund->shownowpage();
        $data['total_num'] = $model_refund->gettotalnum();
        $fxMembers=Model('member_fenxiao')->getMemberFenxiao();
        $fxMembers = array_under_reset($fxMembers,'member_id');
        $pic_base_url = UPLOAD_SITE_URL.'/'.ATTACH_PATH.'/refund/';
        foreach ($return_list as $k => $return_info) {
            $list = array();$operation_detail = '';
            if( $return_info['kefu_state'] == '1' ) {
            	$list['operation'] = "<a class=\"btn orange\" href=\"index.php?act=return&op=kefu_edit&return_id={$return_info['refund_id']}\"><i class=\"fa fa-gavel\"></i>处理</a>";
            } else {
            	$list['operation'] = "<a class=\"btn orange\" href=\"index.php?act=return&op=edit&return_id={$return_info['refund_id']}\"><i class=\"fa fa-gavel\"></i>处理</a>";
            }
            $list['order_sn'] = $return_info['order_sn'];
            $list['refund_sn'] = $return_info['refund_sn'];
            $list['refund_amount'] = ncPriceFormat($return_info['refund_amount']);
            if(!empty($return_info['pic_info'])) {
                $info = unserialize($return_info['pic_info']);
                if (is_array($info) && !empty($info['buyer'])) {
                    foreach($info['buyer'] as $pic_name) {
                        $list['pic_info'] .= "<a href='".$pic_base_url.$pic_name."' target='_blank' class='pic-thumb-tip' onMouseOut='toolTip()' onMouseOver='toolTip(\"<img src=".$pic_base_url.$pic_name.">\")'><i class='fa fa-picture-o'></i></a> ";
                    }
                    $list['pic_info'] = trim($list['pic_info']);
                }
            }
            if (empty($list['pic_info'])) $list['pic_info'] = '';
            $list['buyer_message'] = "<span title='{$return_info['buyer_message']}'>{$return_info['buyer_message']}</span>";
            $list['add_times'] = date('Y-m-d H:i:s',$return_info['add_time']);
            $list['goods_name'] = "<a class='open' title='{$return_info['goods_name']}' href='". urlShop('goods', 'index', array('goods_id' => $return_info['goods_id'])) ."' target='blank'>{$return_info['goods_name']}</a>";
            $list['goods_num'] = $return_info['return_type'] == 2 ? $return_info['goods_num']:'';
            $state_array = $model_refund->getRefundStateArray('seller');
            $list['seller_state'] = $state_array[$return_info['seller_state']];
            $list['seller_message'] = "<span title='{$return_info['seller_message']}'>{$return_info['seller_message']}</span>";
            $list['seller_times'] = !empty($return_info['seller_time']) ? date('Y-m-d H:i:s',$return_info['seller_time']) : '';
            if ($return_info['goods_image'] != '') {
                $list['goods_image'] = "<a href='".thumb($return_info,360)."' target='_blank' class='pic-thumb-tip' onMouseOut='toolTip()' onMouseOver='toolTip(\"<img src=".thumb($return_info,240).">\")'><i class='fa fa-picture-o'></i></a> ";
            } else {
                $list['goods_image'] = '';
            }
            $list['goods_id'] = !empty($return_info['goods_id']) ? $return_info['goods_id'] : '';
            $list['buyer_name'] = $return_info['buyer_name'];
            $list['buyer_id'] = $return_info['buyer_id'];
            $list['store_name'] = $return_info['store_name'];
            $list['store_id'] = $return_info['store_id'];
            $list['refund_way'] = orderPaymentName($return_info['refund_way']);
            //$list['refund_name'] = $return_info['refund_name'];
            //$list['refund_account'] = $return_info['refund_account'];
            $list['fx_order_id']="";

            switch ($return_info['operation_type']){
                case 0:$list['operation_type']='用户申请';break;
                case 1:$list['operation_type']='后台处理';break;
                case 2:$list['operation_type']='渠道抓取';break;
            }
            $data['list'][$return_info['refund_id']] = $list;
            $oids[] = $return_info['order_id'] ;
        }

        if( !empty($oids) ) {
            $oWhere ['order_id'] = array( 'in', array_unique($oids) ) ;
            $orders = Model()->table('orders')->field('order_sn, fx_order_id,buyer_name')->where( $oWhere )->select() ;
            $rels = $orders ? array_column($orders, 'fx_order_id', 'order_sn') : array() ;
            foreach ($data['list'] as $k => $item) {
                $order_id = $item['order_sn'] ;
                $item['fx_order_id'] = isset($rels[ $order_id ]) ? $rels[ $order_id ] : '' ;
                $data['list'][$k] = $item;
            }
        }

        exit(Tpl::flexigridXML($data));
    }


    /**
     * 所有记录
     */
    public function get_all_xmlOp() {
        $model_refund = Model('refund_return');
        $condition = array();

        list($condition,$order) = $this->_get_condition($condition);
        $fxMembers=Model('member_fenxiao')->getMemberFenxiao();
        $fxMembers = array_under_reset($fxMembers,'member_id');
        $return_list = $model_refund->getReturnList($condition,!empty($_POST['rp']) ? intval($_POST['rp']) : 15,$order);
        $oids = $data = array();
        $data['now_page'] = $model_refund->shownowpage();
        $data['total_num'] = $model_refund->gettotalnum();
        $pic_base_url = UPLOAD_SITE_URL.'/'.ATTACH_PATH.'/refund/';
        foreach ($return_list as $k => $return_info) {
            $list = array();
            if ($return_info['refund_state'] == 2) {
                $list['operation'] = "<a class=\"btn orange\" href=\"index.php?act=return&op=edit&return_id={$return_info['refund_id']}\"><i class=\"fa fa-gavel\"></i>处理</a>";
            }
        	if( $return_info['seller_state'] == 1 ){
            	$list['operation'] = "<a class=\"btn orange\" href=\"index.php?act=return&op=seller_edit&return_id={$return_info['refund_id']}\"><i class=\"fa fa-gavel\"></i>商家审核</a>";
            }
            $operation_detail = "<li><a href=\"index.php?act=return&op=view&return_id={$return_info['refund_id']}\"><i class=\"fa fa-list-alt\"></i>查看</a></li>";
            if (empty($return_info['express_id']) && empty($return_info['invoice_no'])) {
                $operation_detail .= "<li><a href=\"index.php?act=return&op=ship&return_id={$return_info['refund_id']}\"><i class=\"fa fa-list-alt\"></i>设置退货物流</a></li>";
            }
            // 是否是客服组
            if ($this->admin_info['gid'] == 2 || $this->admin_info['gname'] == '超级管理员' || $this->admin_info['gname'] == '管理员') {
                $operation_detail .= "<li><a href=\"index.php?act=return&op=edit_return_amount&refund_id={$return_info['refund_id']}\"><i class=\"fa fa-list-alt\"></i>修改退款金额</a></li>";
                if (in_array($return_info['refund_state'], array(2, 3)) && $return_info['seller_state'] == 2 && $return_info['kefu_state'] > 0) {
                    $operation_detail .= "<li><a href=\"index.php?act=return&op=cancel_return&refund_id={$return_info['refund_id']}\"><i class=\"fa fa-list-alt\"></i>撤销退货</a></li>";
                }
            }
            $list['operation'] .= "<span class='btn'><em><i class='fa fa-cog'></i>设置 <i class='arrow'></i></em><ul>{$operation_detail}</ul>";


            $list['order_sn'] = $return_info['order_sn'];
            $list['refund_sn'] = $return_info['refund_sn'];
            $list['refund_amount'] = ncPriceFormat($return_info['refund_amount']);
            if(!empty($return_info['pic_info'])) {
                $info = unserialize($return_info['pic_info']);
                if (is_array($info) && !empty($info['buyer'])) {
                    foreach($info['buyer'] as $pic_name) {
                        $list['pic_info'] .= "<a href='".$pic_base_url.$pic_name."' target='_blank' class='pic-thumb-tip' onMouseOut='toolTip()' onMouseOver='toolTip(\"<img src=".$pic_base_url.$pic_name.">\")'><i class='fa fa-picture-o'></i></a> ";
                    }
                    $list['pic_info'] = trim($list['pic_info']);
                }
            }
            if (empty($list['pic_info'])) $list['pic_info'] = '';
            $list['buyer_message'] = "<span title='{$return_info['buyer_message']}'>{$return_info['buyer_message']}</span>";
            $list['add_times'] = date('Y-m-d H:i:s',$return_info['add_time']);
            $list['goods_name'] = "<a class='open' title='{$return_info['goods_name']}' href='". urlShop('goods', 'index', array('goods_id' => $return_info['goods_id'])) ."' target='blank'>{$return_info['goods_name']}</a>";
            $list['goods_num'] = $return_info['return_type'] == 2 ? $return_info['goods_num']:'';
            $state_array = $model_refund->getRefundStateArray('seller');
            $list['seller_state'] = $state_array[$return_info['seller_state']];

            $admin_array = $model_refund->getRefundStateArray('admin');
            $list['refund_state'] = $return_info['seller_state'] == 2 ? $admin_array[$return_info['refund_state']]:'';

            $list['seller_message'] = $return_info['seller_message'];
            $list['admin_message'] = $return_info['admin_message'];
            $list['seller_times'] = !empty($return_info['seller_time']) ? date('Y-m-d H:i:s',$return_info['seller_time']) : '';
            if ($return_info['goods_image'] != '') {
                $list['goods_image'] = "<a href='".thumb($return_info,360)."' target='_blank' class='pic-thumb-tip' onMouseOut='toolTip()' onMouseOver='toolTip(\"<img src=".thumb($return_info,240).">\")'><i class='fa fa-picture-o'></i></a> ";
            } else {
                $list['goods_image'] = '';
            }
            $list['goods_id'] = !empty($return_info['goods_id']) ? $return_info['goods_id'] : '';
            $list['order_sn'] = $return_info['order_sn'];
            $buyer_name = $return_info['buyer_name'] ;
            if( isset($fxMembers[ $return_info['buyer_id'] ] ) && $fxMembers[ $return_info['buyer_id'] ] ) {
                $buyer_name = $fxMembers[ $return_info['buyer_id'] ] ['member_cn_code'];
            }
            $list['buyer_name'] = $buyer_name;
            $list['buyer_id'] = $return_info['buyer_id'];
            $list['store_name'] = $return_info['store_name'];
            $list['store_id'] = $return_info['store_id'];
            $list['refund_way'] = orderPaymentName($return_info['refund_way']);
            //$list['refund_name'] = $return_info['refund_name'];
            //$list['refund_account'] = $return_info['refund_account'];
            $list['fx_order_id']="";

            switch ($return_info['operation_type']){
                case 0:$list['operation_type']='用户申请';break;
                case 1:$list['operation_type']='后台处理';break;
                case 2:$list['operation_type']='渠道抓取';break;
            }
            $data['list'][$return_info['refund_id']] = $list;
            $oids[] = $return_info['order_id'] ;
        }
        if( !empty($oids) ) {
            $oWhere ['order_id'] = array( 'in', array_unique($oids) ) ;
            $orders = Model()->table('orders')->field('order_sn, fx_order_id,buyer_name')->where( $oWhere )->select() ;
            $rels = $orders ? array_column($orders, 'fx_order_id', 'order_sn') : array() ;
            foreach ($data['list'] as $k => $item) {
                $order_id = $item['order_sn'] ;
                $item['fx_order_id'] = isset($rels[ $order_id ]) ? $rels[ $order_id ] : '' ;
                $data['list'][$k] = $item;
            }
        }
        exit(Tpl::flexigridXML($data));
    }

    /**
     * 客服修改退货金额
     */
    public function edit_return_amountOp()
    {
        $model_return = Model('refund_return');
        $model_order = Model('order');
        $condition = array();
        $condition['refund_id'] = intval($_GET['refund_id']);
        $return = $model_return->getRefundReturnInfo($condition);
        $order = $model_order->getOrderInfo(array('order_id'=> $return['order_id']),array());

        if (chksubmit()) {
            $refund_array = array();
            $refund_array['refund_amount'] = $_POST['refund_amount'];
            $model_return->editRefundReturn($condition, $refund_array);

            $condition = array();
            $condition['seller_status'] = 2;
            $condition['refund_status'] = 3;
            $condition['order_id'] = $order['order_id'];
            $refund_new = $model_return->getRefundList($condition);
            if (count($refund_new) < 1) {
                $data['refund_amount'] = 0;
            } else {
                $refund_amount = 0;
                foreach ($refund_new as $k => $v) {
                    $refund_amount += $v['refund_amount'];
                }
                $data['refund_amount'] = $refund_amount;
            }
            $model_order->editOrder($data, array('order_id'=> $order['order_id']));

            //加入订单日志
            $log = array();
            $log['order_id'] = $return['order_id'];
            $log['log_msg'] = "【{$this->admin_info['name']}】处理退货金额修改，原因：{$_POST['log_msg']}";
            $log['log_time'] = time();
            $log['log_role'] = $this->admin_info['gname'];
            $log['log_user'] = $this->admin_info['name'];
            $log['log_orderstate'] = $order['order_state'];
            $model_order->addOrderLog($log);
            showMessage(Language::get('nc_common_save_succ'),'index.php?act=return&op=index');
        }

        Tpl::output('return',$return);
        Tpl::setDirquna('shop');/*网 店 运 维shop wwi.com*/
        Tpl::showpage('return.edit_return_amount');
    }

    /**
     * 客服处理撤销退货（改状态为商家拒绝）
     */
    public function cancel_returnOp()
    {
        $model_return = Model('refund_return');
        $model_order = Model('order');
        $condition = array();
        $condition['refund_id'] = intval($_GET['refund_id']);
        $return = $model_return->getRefundReturnInfo($condition);
        $order = $model_order->getOrderInfo(array('order_id'=> $return['order_id']),array());

        if (chksubmit()) {
            $refund_array = $data = array();

            // 退款表
            $refund_array['seller_state'] = 3;
            $refund_array['refund_state'] = 3;
            $model_return->editRefundReturn($condition, $refund_array);

            // order表
            $data = array();
            if ($order['refund_status'] != 3 && $order['lock'] == 1) {
                $data['delay_time'] = time();
                $data['lock_state'] = 0;
            }
            if ($order['order_state'] == 0) {
                $data['order_state'] = 20;
            }
            $condition = array();
            $condition['seller_status'] = 2;
            $condition['refund_status'] = 3;
            $condition['order_id'] = $order['order_id'];
            $return_new = $model_return->getRefundList($condition);
            if (count($return_new) < 1) {
                $data['refund_amount'] = 0;
                $data['refund_state'] = 0;
            } else {
                $refund_amount = 0;
                foreach ($return_new as $k => $v) {
                    $refund_amount += $v['refund_amount'];
                }
                $data['refund_amount'] = $refund_amount;
            }
            $model_order->editOrder($data, array('order_id'=> $order['order_id']));

            //加入订单日志
            $log = array();
            $log['order_id'] = $order['order_id'];
            $log['log_msg'] = "【{$this->admin_info['name']}】处理退货状态修改为商家拒绝，原因：{$_POST['log_msg']}";
            $log['log_time'] = time();
            $log['log_role'] = $this->admin_info['gname'];
            $log['log_user'] = $this->admin_info['name'];
            $log['log_orderstate'] = $order['order_state'];
            $model_order->addOrderLog($log);

            showMessage(Language::get('nc_common_save_succ'),'index.php?act=return&op=index');
        }

        Tpl::output('return', $return);
        Tpl::setDirquna('shop');/*网 店 运 维shop wwi.com*/
        Tpl::showpage('return.cancel_return');
    }

    /**
     * 退货财务处理页
     *
     */
    public function editOp() {
        $model_refund = Model('refund_return');
        $condition = array();
        $condition['refund_id'] = intval($_GET['return_id']);
        $return_list = $model_refund->getReturnList($condition);
        $return = $return_list[0];
        $order_id = $return['order_id'];
        $model_order = Model('order');
        $order = $model_order->getOrderInfo(array('order_id'=> $order_id),array());

        $detail_array = $model_refund->getDetailInfo($condition);
        if(empty($detail_array)) {
            $model_refund->addDetail($return,$order);
            $detail_array = $model_refund->getDetailInfo($condition);
        }

        $order['pay_amount'] = $order['order_amount']-$order['rcb_amount']-$order['pd_amount'];//在线支付金额=订单总价格-充值卡支付金额-预存款支付金额
        $out_amount = $order ['pay_amount'] - $order ['refund_amount']; // 可在线退款金额

        $refund_amount = $detail_array ['refund_amount']; // 本次退款总金额
        if ($refund_amount > $out_amount) {
            $refund_amount = $out_amount;
        }
        $order ['pay_refund_amount'] = ncPriceFormat ( $refund_amount );

        Tpl::output('order', $order);
        Tpl::output('detail_array', $detail_array);

        if (chksubmit()) {
            // 只能是出纳审核
            if ($this->admin_info['gname'] != '出纳') {
                showMessage('只有出纳才可以操作！');
            }
            $send_payment = C('send_payment');
            if (isset($_POST['send_payment'])) {
                $send_payment = $_POST['send_payment'] == 1 ? false : true;
            }

            if ($return['refund_state'] != '2') {//检查状态,防止页面刷新不及时造成数据错误
                showMessage(Language::get('nc_common_save_fail'));
            }

            $service = Service('Refund');
            // 非货到付款订单在线支付退款
            if ($order['order_type'] != '4' && $order['payment_code'] != 'fenxiao' && $send_payment) {
                try {
                    $detail_array = $service->apiRefund($detail_array);
                } catch (Exception $e) {
                    showMessage($e->getMessage());
                }
            }else{
                $return['pay_amount'] = $refund_amount;
            }
            
            if ($detail_array['pay_time'] > 0) {
                $return['pay_amount'] = $detail_array['pay_amount'];//已完成在线退款金额
            }
            $state = $model_refund->editOrderRefund($return, $this->admin_info['name']);
            if ($state) {
                $refund_array = array();
                $refund_array['admin_time'] = time();
                $refund_array['refund_state'] = '3';//状态:1为处理中,2为待管理员处理,3为已完成
                $refund_array['admin_message'] = $_POST['admin_message'];
                $model_refund->editRefundReturn($condition, $refund_array);

                // 发送买家消息
                $param = array();
                $param['code'] = 'refund_return_notice';
                $param['member_id'] = $return['buyer_id'];
                $param['param'] = array(
                    'refund_url' => urlShop('member_refund', 'view', array('return_id' => $return['refund_id'])),
                    'refund_sn' => $return['refund_sn']
                );
                QueueClient::push('sendMemberMsg', $param);

                $this->log('退货确认，退款编号'.$return['refund_sn']);
                showMessage(Language::get('nc_common_save_succ'),'index.php?act=return&op=return_manage');
            } else {
                showMessage(Language::get('nc_common_save_fail'));
            }
        }
        Tpl::output('return',$return);
        $info['buyer'] = array();
        if(!empty($return['pic_info'])) {
            $info = unserialize($return['pic_info']);
        }
        Tpl::output('pic_list',$info['buyer']);
		Tpl::setDirquna('shop');/*网 店 运 维shop wwi.com*/
        Tpl::showpage('return.edit');
    }
    /**
     * 退货客服处理页
     *
     */
    public function kefu_editOp() {
        $model_refund = Model('refund_return');
        $condition = array();
        $condition['refund_id'] = intval($_GET['return_id']);
        $return_list = $model_refund->getReturnList($condition);
        $return = $return_list[0];
        $order_id = $return['order_id'];
        $model_order = Model('order');
        $order = $model_order->getOrderInfo(array('order_id'=> $order_id),array());
        if ($order['payment_time'] > 0) {
            $order['pay_amount'] = $order['order_amount']-$order['rcb_amount']-$order['pd_amount'];//在线支付金额=订单总价格-充值卡支付金额-预存款支付金额
        }
        Tpl::output('order',$order);
        $detail_array = $model_refund->getDetailInfo($condition);
        if(empty($detail_array)) {
            $model_refund->addDetail($return,$order);
            $detail_array = $model_refund->getDetailInfo($condition);
        }
        Tpl::output('detail_array',$detail_array);
        if (chksubmit()) {
            if( !$_POST['refund_way'] ) showMessage('退款方式不能为空');
    		
    		$refund_array = array() ;
    		$refund_array ['refund_way'] 		= trim( $_POST['refund_way'] ) ;
    		$refund_array ['refund_name'] 		= trim( $_POST['refund_name'] ) ;
    		$refund_array ['refund_account'] 	= trim( $_POST['refund_account'] ) ;
    		$refund_array ['kefu_state']		= 2 ;
    		$refund_array ['admin_message']		= trim( $_POST['admin_message'] ) ;
    		
    		$res = $model_refund -> editRefundReturn ( $condition, $refund_array ) ;
    		
    		if( !$res ) {
    			showMessage('保存失败');
    		} 
    		
    		showMessage('保存成功','index.php?act=return&op=caiwu_manage');
        }
        Tpl::output('return',$return);
        $info['buyer'] = array();
        if(!empty($return['pic_info'])) {
            $info = unserialize($return['pic_info']);
        }
        Tpl::output('pic_list',$info['buyer']);
        
        $refund_way = array(
        		'predeposit' => '预存款',
        		'alipay' => '支付宝',
        		'offline' => '线下支付',
        		'yeepay' => '易宝支付',
        		'bestpay' => '翼支付',
        		'fenxiao' => '分销支付'
        ) ;
        Tpl::output('refund_way',$refund_way);
        
		Tpl::setDirquna('shop');/*网 店 运 维shop wwi.com*/
        Tpl::showpage('return.edit.kefu');
    }

    /**
     * 发货
     *
     */
    public function shipOp(){
        $model_refund = Model('refund_return');
        $condition = array();
        $condition['refund_id'] = intval($_GET['return_id']);
        $return_list = $model_refund->getReturnList($condition);
        $return = $return_list[0];
        Tpl::output('return',$return);
        $express_list  = rkcache('express',true);
        Tpl::output('express_list', $express_list);
        if ($return['seller_state'] != '2' || $return['goods_state'] != '1') {//检查状态,防止页面刷新不及时造成数据错误
            showMessage('商家未同意或已经发货了。');
        }
        if (chksubmit()) {
            $refund_array = array();
            $refund_array['ship_time'] = time();
            $refund_array['delay_time'] = time();
            $refund_array['express_id'] = $_POST['express_id'];
            $refund_array['invoice_no'] = $_POST['invoice_no'];
            $refund_array['goods_state'] = '2';
            $state = $model_refund->editRefundReturn($condition, $refund_array);
            if ($state) {
                showMessage('保存成功','index.php?act=return&op=index','succ');
            } else {
                showMessage('保存失败');
            }
        }
        $info['buyer'] = array();
        if(!empty($return['pic_info'])) {
            $info = unserialize($return['pic_info']);
        }
        Tpl::output('pic_list',$info['buyer']);
        $condition = array();
        $condition['order_id'] = $return['order_id'];
        $model_refund->getRightOrderList($condition, $return['order_goods_id']);
        $model_trade = Model('trade');
        $return_delay = $model_trade->getMaxDay('return_delay');//发货默认5天后才能选择没收到
        Tpl::output('return_delay', $return_delay);
        Tpl::output('return_confirm', $model_trade->getMaxDay('return_confirm'));//卖家不处理收货时按同意并弃货处理
        Tpl::output('ship',1);
        Tpl::setDirquna('shop');/*网 店 运 维shop wwi.com*/
        Tpl::showpage('return.ship_view');
    }


    /**
     * 商家已处理的分销退款单
     */
    public function fxsellerdoOp() {
        Tpl::output('fxsellerdo','1');
        Tpl::setDirquna('shop');/*网 店 运 维shop wwi.com*/
        Tpl::showpage('return_manage.list');
    }

    /**
     * 退货记录查看页
     *
     */
    public function viewOp() {
        $model_refund = Model('refund_return');
        $condition = array();
        $condition['refund_id'] = intval($_GET['return_id']);
        $return_list = $model_refund->getReturnList($condition);
        $return = $return_list[0];
        Tpl::output('return',$return);
        $order_info = Model('order')->getOrderInfo( array('order_id' => $return['order_id']) ) ;
        Tpl::output('order_info',$order_info);
        $info['buyer'] = array();
        if(!empty($return['pic_info'])) {
            $info = unserialize($return['pic_info']);
        }
        Tpl::output('pic_list',$info['buyer']);
        $detail_array = $model_refund->getDetailInfo($condition);
        $express_list  = rkcache('express',true);
        Tpl::output('express_list',$express_list);
        Tpl::output('detail_array',$detail_array);
		Tpl::setDirquna('shop');/*网 店 运 维shop wwi.com*/
        Tpl::showpage('return.view');
    }
    private function _get_condition($condition) {

        /** @var member_fenxiaoModel $member_fenxiaoModel */
        $member_fenxiaoModel = Model('member_fenxiao');
        if ($_REQUEST['query'] != '' && in_array($_REQUEST['qtype'],array('order_sn','store_name','buyer_name','goods_name','refund_sn'))) {
            $condition[$_REQUEST['qtype']] = array('like',"%{$_REQUEST['query']}%");
        }

        $fx_order_id = null;
        if($_GET['keyword'] != '' && in_array($_GET['keyword_type'],array('fx_order_id'))){
            $fx_order_id = $_GET['keyword'];
        }
        if ($_REQUEST['query'] != '' && in_array($_REQUEST['qtype'],array('fx_order_id'))) {
            $fx_order_id = $_REQUEST['query'];
        }
        if ($fx_order_id !== null) {
            /** @var orderModel $orderModel */
            $orderModel = Model('order');
            $orders = $orderModel->getOrderList(array('fx_order_id'=>array('like',"%{$fx_order_id}%")),'','order_id');
            if(empty($orders)){
                $condition['order_id'] = '-1';
            }else{
                $orderIds = array_column($orders,'order_id');
                $condition['order_id'] = array('in',$orderIds);
            }
        }

        if ($_GET['keyword'] != '' && in_array($_GET['keyword_type'], array('member_fenxiao_name'))) {
            $member_fenxiao_list = $member_fenxiaoModel->getMembeFenxiaoList(array('member_cn_code' => array('like', '%' . trim($_GET['keyword']) . '%')));
            if (empty($member_fenxiao_list)) {
                $condition['buyer_id'] = '-1';
            } else {
                $member_ids = array_column($member_fenxiao_list, 'member_id');
                $condition['buyer_id'] = array('in', $member_ids);
            }
        }

        if ($_GET['keyword'] != '' && in_array($_GET['keyword_type'],array('order_sn','store_name','buyer_name','goods_name','refund_sn'))) {
            if ($_GET['jq_query']) {
                $condition[$_GET['keyword_type']] = $_GET['keyword'];
            } else {
                $condition[$_GET['keyword_type']] = array('like',"%{$_GET['keyword']}%");
            }
        }
        if (!in_array($_GET['qtype_time'],array('add_time','seller_time','admin_time'))) {
            $_GET['qtype_time'] = null;
        }
        $if_start_time = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_start_date']);
        $if_end_time = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_end_date']);
        $start_unixtime = $if_start_time ? strtotime($_GET['query_start_date']) : null;
        $end_unixtime = $if_end_time ? strtotime($_GET['query_end_date']): null;
        if ($_GET['qtype_time'] && ($start_unixtime || $end_unixtime)) {
            $condition[$_GET['qtype_time']] = array('time',array($start_unixtime,$end_unixtime));
        }
        if (floatval($_GET['query_start_amount']) > 0 && floatval($_GET['query_end_amount']) > 0) {
            $condition['refund_amount'] = array('between',floatval($_GET['query_start_amount']).','.floatval($_GET['query_end_amount']));
        }
        if ($_GET['refund_state'] == 2) {
            $condition['refund_state'] = 2;
        }
        $sort_fields = array('buyer_name','store_name','goods_num','goods_id','refund_id','seller_time','refund_amount','buyer_id','store_id');
        if ($_REQUEST['sortorder'] != '' && in_array($_REQUEST['sortname'],$sort_fields)) {
            $order = $_REQUEST['sortname'].' '.$_REQUEST['sortorder'];
        }
        $condition['shequ_tuan_id'] = 0;//过滤社区团购自提的
        if ($_REQUEST['fenxiao_type']){
            $member_fenxiao_list = $member_fenxiaoModel->getMemberFenxiao();
            $member_ids = array_column($member_fenxiao_list, 'member_id');
            $condition['buyer_id'] = array($_REQUEST['fenxiao_type']=='hango'?'not in':'in', $member_ids);
        }
        return array($condition,$order);
    }

    /**
     * csv导出
     */
    public function export_step1Op() {
        $model_refund = Model('refund_return');
        $condition = array();
        if (preg_match('/^[\d,]+$/', $_GET['refund_id'])) {
            $_GET['refund_id'] = explode(',',trim($_GET['refund_id'],','));
            $condition['refund_id'] = array('in',$_GET['refund_id']);
        }
        list($condition,$order) = $this->_get_condition($condition);
        if (!is_numeric($_GET['curpage'])){
            $count = $model_refund->getReturnCount($condition);
            if ($count > self::EXPORT_SIZE ){   //显示下载链接
                $array = array();
                $page = ceil($count/self::EXPORT_SIZE);
                for ($i=1;$i<=$page;$i++){
                    $limit1 = ($i-1)*self::EXPORT_SIZE + 1;
                    $limit2 = $i*self::EXPORT_SIZE > $count ? $count : $i*self::EXPORT_SIZE;
                    $array[$i] = $limit1.' ~ '.$limit2 ;
                }
                Tpl::output('list',$array);
                Tpl::output('murl','javascript:history.back(-1)');
                Tpl::setDirquna('shop');
                Tpl::showpage('export.excel');
                exit();
            }
            $limit = false;
        } else {
            $limit1 = ($_GET['curpage']-1) * self::EXPORT_SIZE;
            $limit2 = self::EXPORT_SIZE;
            $limit = $limit1 .','. $limit2;
        }
        $return_list = $model_refund->getReturnList($condition,'',$order,$limit);
        $this->createCsv($return_list);
    }

    /**
     * 生成csv文件
     */
    private function createCsv($return_list) {
        $model_refund = Model('refund_return');
        $pic_base_url = UPLOAD_SITE_URL.'/'.ATTACH_PATH.'/refund/';
        $data = array();
        $reason_list = $model_refund->getReasonList(array(),'',false);
        foreach ($return_list as $k => $return_info) {
            $list = array();
            $list['refund_sn'] = $return_info['refund_sn']."\t";
            $list['refund_amount'] = ncPriceFormat($return_info['refund_amount']);
            if(!empty($return_info['pic_info'])) {
                $info = unserialize($return_info['pic_info']);
                if (is_array($info) && !empty($info['buyer'])) {
                    foreach($info['buyer'] as $pic_name) {
                        $list['pic_info'] .= $pic_base_url.$pic_name.'|';
                    }
                    $list['pic_info'] = trim($list['pic_info'],'|');
                }
            }

            if (empty($list['pic_info'])) $list['pic_info'] = '';
            if(isset($reason_list[$return_info['reason_id']])){
                $return_info['reason'] = $reason_list[$return_info['reason_id']]['reason_info'];
            } else{
                $return_info['reason'] = $return_info['goods_id']>0?'其他':'取消订单，全部退款';
            }
            $list['reason'] = str_replace("\r\n",";",$return_info['reason']);
            $list['buyer_message'] = str_replace("\r\n",";",$return_info['buyer_message']);
            $list['add_times'] = date('Y-m-d H:i:s',$return_info['add_time']);
            $list['goods_name'] = $return_info['goods_name'];
            $list['goods_num'] = $return_info['return_type'] == 2 ? $return_info['goods_num']:'';
            $state_array = $model_refund->getRefundStateArray('seller');
            $list['seller_state'] = $state_array[$return_info['seller_state']];
            $admin_array = $model_refund->getRefundStateArray('admin');
            $list['refund_state'] = $return_info['seller_state'] == 2 ? $admin_array[$return_info['refund_state']]:'';
            $list['seller_message'] = preg_replace('/[,\r\n]+/i','',$return_info['seller_message']);
            $list['admin_message'] = preg_replace('/[,\r\n]+/i','',$return_info['admin_message']);
            $list['seller_times'] = !empty($return_info['seller_time']) ? date('Y-m-d H:i:s',$return_info['seller_time']) : '';
            if ($return_info['goods_image'] != '') {
                $list['goods_image'] = thumb($return_info,360);
            } else {
                $list['goods_image'] = '';
            }
            $list['goods_id'] = !empty($return_info['goods_id']) ? $return_info['goods_id'] : '';
            $list['order_sn'] = $return_info['order_sn']."\t";
            $list['buyer_name'] = $return_info['buyer_name'];
            $list['buyer_id'] = $return_info['buyer_id'];
            $list['store_name'] = $return_info['store_name'];
            $list['store_id'] = $return_info['store_id'];
            $list[$k]['express_name'] = $return_info['express_name'];
            $list[$k]['shipping_code'] = $return_info['shipping_code']."\t";
            $list[$k]['order_create_time'] = date('Y-m-d H:i:s',$return_info['order_create_time']);
            $data[] = $list;
        }
        $header = array(
                'refund_sn' => '退单编号',
                'refund_amount' => '退款金额',
                'pic_info' => '申请图片',
            'reason' => '申请原因',
            'buyer_message' => '退款说明',
                'add_times' => '申请时间',
                'goods_name' => '涉及商品',
                'goods_num' => '退货数量',
                'seller_state' => '商家处理',
                'refund_state' => '平台处理',
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
                'express_name' => '物流公司',
                'shipping_code' => '物流单号',
                'order_create_time' => '订单创建时间',
        );
        array_unshift($data, $header);
		$csv = new Csv();
	    $export_data = $csv->charset($data,CHARSET,'gbk');
	    $csv->filename = $csv->charset('return',CHARSET).$_GET['curpage'] . '-'.date('Y-m-d');
	    $csv->export($export_data);
    }
    
    /**
     * 客服代商家审核
     *
     */
	public function seller_editOp() {
        $model_refund = Model('refund_return');
        $condition = array();
        $condition['refund_id'] = intval($_GET['return_id']);
        $return_list = $model_refund->getReturnList($condition);
        $return = $return_list[0];
        $order_id = $return['order_id'];
        $model_order = Model('order');
        $order = $model_order->getOrderInfo(array('order_id'=> $order_id),array());
        if ($order['payment_time'] > 0) {
            $order['pay_amount'] = $order['order_amount']-$order['rcb_amount']-$order['pd_amount'];//在线支付金额=订单总价格-充值卡支付金额-预存款支付金额
        }
        Tpl::output('order',$order);
        $detail_array = $model_refund->getDetailInfo($condition);
        if(empty($detail_array)) {
            $model_refund->addDetail($return,$order);
            $detail_array = $model_refund->getDetailInfo($condition);
        }
        Tpl::output('detail_array',$detail_array);
        if (chksubmit()) {
        	$reload = 'index.php?act=return';
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
                $log = array();
	        	$log['order_id'] = $order_id;
	        	$log['log_msg'] = "代替商家处理退货申请，审核意见：". str_replace(array('2','3'), array('同意','不同意'), $_POST['seller_state']) ;
	        	$log['log_time'] = time();
	        	$log['log_role'] = 'admin';
	        	$log['log_user'] = $this->admin_info['name'];
	        	$log['log_orderstate'] = $order_info['order_state'];
	        	$model_order->addOrderLog($log);

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
        Tpl::output('pic_list',$info['buyer']);
		Tpl::setDirquna('shop');/*网 店 运 维shop wwi.com*/
        Tpl::showpage('return.edit.seller');
    }
}
