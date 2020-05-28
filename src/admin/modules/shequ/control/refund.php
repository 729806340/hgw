<?php
/**
 * 退款管理
 *
 *
 *
 *
 * @汉购网提供技术支持 授权请购买shopnc授权
 * @license    http://www.hangowa.com
 * @link       交流群号：
 */



defined('ByShopWWI') or exit('Access Invalid!');
class refundControl extends SystemControl{
    const EXPORT_SIZE = 1000;
    private $links = array(
            array('url'=>'act=refund&op=kefu','text'=>'待客服处理'),
    		array('url'=>'act=refund&op=caiwu','text'=>'待财务处理'),
            array('url'=>'act=refund&op=index','text'=>'所有记录'),
            array('url'=>'act=refund&op=store_reject','text'=>'商家已拒绝'),
            //array('url'=>'act=refund&op=reason','text'=>'退款退货原因'),
    		//array('url'=>'act=refund&op=fxsellerdo','text'=>'商家已处理分销退款')
    );
    
    public function __construct(){
        parent::__construct();
        $model_refund = Model('refund_return');
        $model_refund->getRefundStateArray();
        //$op = substr($_GET['op'], -2) == 'Op' ? $_GET['op'] : $_GET['op']."Op" ;
        Tpl::output('top_link',$this->sublink($this->links,$_GET['op']));
    }

    
    /**
     * 所有记录
     */
    public function indexOp() {
        Tpl::setDirquna('shequ');/*网 店 运 维shop wwi.com*/
        Tpl::showpage('refund_all.list');
    }

    public function importOp(){
        set_time_limit(900);
        ini_set("memory_limit","4G");
        if(false&&empty($_POST)){
            $data['state'] = false;
            $data['msg'] = '上传数据为空';
            die(json_encode($data));
        }
        $data = array();
        $file	= $_FILES['file'];
        /**
         * 上传错误
         */
        if ($file['error'] > 0) {
            //showMessage('文件上传出错', '', 'html', 'error');
            $data['state'] = false;
            $data['msg'] = '文件上传错误';
            echo json_encode($data);
            die();
        }
        /**
         * 上传文件存在判断
         */
        if(empty($file['name'])){
            //showMessage('请选择上传文件','','html','error');
            $data['state'] = false;
            $data['msg'] = '请选择上传文件';
            echo json_encode($data);
            die();
        }
        /**
         * 文件来源判定
         */
        if(!is_uploaded_file($file['tmp_name'])){
            //showMessage('文件不合法','','html','error');
            $data['state'] = false;
            $data['msg'] = '文件不合法';
            echo json_encode($data);
            die();
        }
        /**
         * 文件类型判定
         */
        $file_name_array	= explode('.',$file['name']);
        $curFileType = $file_name_array[count($file_name_array) - 1];
        if (!in_array(strtolower($curFileType), array('csv','xls','xlsx'))) {
            //showMessage('文件类型不合法'.$file_name_array[count($file_name_array)-1],'','html','error');
            $data['state'] = false;
            $data['msg'] = '请上传csv/xls/xlsx文件';
            echo json_encode($data);
            die();
        }
        /**
         * 文件大小判定
         */
        if($file['size'] > intval(ini_get('upload_max_filesize'))*1024*1024){
            //showMessage('文件过大','','html','error');
            $data['state'] = false;
            $data['msg'] = '文件大小不可以超过'.ini_get('upload_max_filesize')."M";
            echo json_encode($data);
            die();
        }
        /**
         * 开始上传
         */
        $dir = BASE_UPLOAD_PATH.DS.'admin'.DS.'temp'.DS;
        if(!is_dir($dir)){
            @mkdir(iconv("UTF-8", "GBK", $dir), 0777, true);
        }
        $fileName = $dir.date('Y').date('m').date('d').time().'.'.$curFileType;
        if (move_uploaded_file($file['tmp_name'], $fileName)) {
            /** @var RefundService $service */
            $service = Service('Refund');
            $result = $service->importRefunds($fileName);
            if(!empty($result['state'])){
                $data['state'] = false;
                $data['msg'] = $result['msg'];
                echo json_encode($data);
                die();
            }
            $data['state'] = true;
            $data['result'] = $result;
            die(json_encode($data));
        }
        if(false){
        }
        $data['state'] = true;
        $data['result'] = array('total'=>10,'success'=>5,'fail'=>array(123,123,123),'errorMsg'=>array(),);
        echo json_encode($data);
        die();
    }
    
    /**
     * 待客服处理
     */
    public function kefuOp() {
        Tpl::output('kefu_state','1');
        Tpl::setDirquna('shequ');/*网 店 运 维shop wwi.com*/
        Tpl::showpage('refund_manage.list');
    }
    
    /**
     * 待财务处理
     */
    public function caiwuOp() {
    	Tpl::output('kefu_state','2');
    	//Tpl::output('top_link',$this->sublink($this->links,$_GET['op']));
    	Tpl::setDirquna('shequ');/*网 店 运 维shop wwi.com*/
    	Tpl::showpage('refund_manage.list');
    }
    
    /**
     * 商家已处理的分销退款单
     */
    public function fxsellerdoOp() {
    	Tpl::output('fxsellerdo','1');
    	Tpl::setDirquna('shequ');/*网 店 运 维shop wwi.com*/
    	Tpl::showpage('refund_manage.list');
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
        
        //商家已处理分销单列表
        if( isset($_GET['fxsellerdo']) && intval($_GET['fxsellerdo']) == 1 ) {
        	/** @var FenxiaoService $fxSer **/
        	$fxSer = Service('Fenxiao');
        	$fxMembers = $fxSer -> getFenxiaoMembers() ;
        	$mids = array_keys($fxMembers);
        	$condition = array();
        	$condition['buyer_id'] = array('in', $mids);
        	$condition['seller_state'] = array('neq', 1);
        	$condition['goods_id'] = array('gt', 0);
        	
        	$_REQUEST['sortname'] = 'seller_time';
        	$_REQUEST['sortorder'] = 'desc';
        }

        list($condition,$order) = $this->_get_condition($condition);

        $refund_list = $model_refund->getRefundList($condition,$_POST['rp'],$order);
        $data = array();
        $data['now_page'] = $model_refund->shownowpage();
        $data['total_num'] = $model_refund->gettotalnum();
        $pic_base_url = UPLOAD_SITE_URL.'/'.ATTACH_PATH.'/refund/';
        foreach ($refund_list as $k => $refund_info) {
            $list = array();
            if( $refund_info['kefu_state'] == '1' ) {
            	$list['operation'] = "<a class=\"btn orange\" href=\"index.php?act=refund&op=kefu_edit&refund_id={$refund_info['refund_id']}\"><i class=\"fa fa-gavel\"></i>处理</a>";
            	$list['operation'] .= "<a class=\"btn orange\" href=\"javascript:kefu_reject({$refund_info['refund_id']})\"><i class=\"fa fa-gavel\"></i>拒绝</a>";
            } else {
            	$list['operation'] = "<a class=\"btn orange\" href=\"index.php?act=refund&op=edit&refund_id={$refund_info['refund_id']}\"><i class=\"fa fa-gavel\"></i>处理</a>";
            }
            $list['order_sn'] = $refund_info['order_sn'];
            $list['refund_sn'] = $refund_info['refund_sn'];
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
            $list['admin_name'] = $refund_info['admin_name'];
            $data['list'][$refund_info['refund_id']] = $list;
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
         /*echo '<pre>';
         print_r($condition);exit;*/
        $refund_list = $model_refund->getRefundList($condition,!empty($_POST['rp']) ? intval($_POST['rp']) : 15,$order);
        $oids = $data = array();
        $data['now_page'] = $model_refund->shownowpage();
        $data['total_num'] = $model_refund->gettotalnum();
        $pic_base_url = UPLOAD_SITE_URL.'/'.ATTACH_PATH.'/refund/';

        /** @var FenxiaoService $fxSer **/
        $fxSer = Service('Fenxiao');
        $fxMembers = $fxSer -> getFenxiaoMembers() ;
        $mids = array_keys($fxMembers);
        foreach ($refund_list as $k => $refund_info) {
            $list = array();
            if ($refund_info['refund_state'] == 2) {
                $list['operation'] = "<a class=\"btn orange\" href=\"index.php?act=refund&op=edit&refund_id={$refund_info['refund_id']}\"><i class=\"fa fa-gavel\"></i>处理</a>";
            }
            if( $refund_info['refund_state'] == 1 ) {
                $list['operation'] = "<a class=\"btn orange\" href=\"index.php?act=refund&op=seller_edit&refund_id={$refund_info['refund_id']}\"><i class=\"fa fa-gavel\"></i>商家审核</a>";
            }
            $operation_detail = "<a class=\"btn green\" href=\"index.php?act=refund&op=view&refund_id={$refund_info['refund_id']}\"><i class=\"fa fa-list-alt\"></i>查看</a>";
            // 是否是客服组
            if ($this->admin_info['gid'] == 2 || $this->admin_info['gname'] == '超级管理员' || $this->admin_info['gname'] == '管理员') {
                $operation_detail = "<li><a href=\"index.php?act=refund&op=view&refund_id={$refund_info['refund_id']}\"><i class=\"fa fa-list-alt\"></i>查看</a></li>";
                $operation_detail .= "<li><a href=\"index.php?act=refund&op=edit_refund_amount&refund_id={$refund_info['refund_id']}\"><i class=\"fa fa-list-alt\"></i>修改退款金额</a></li>";
                if (in_array($refund_info['refund_state'], array(2, 3)) && $refund_info['seller_state'] == 2 && $refund_info['kefu_state'] > 0) {
                    $operation_detail .= "<li><a href=\"index.php?act=refund&op=cancel_refund&refund_id={$refund_info['refund_id']}\"><i class=\"fa fa-list-alt\"></i>撤销退款</a></li>";
                }
                $list['operation'] .= "<span class='btn'><em><i class='fa fa-cog'></i>设置 <i class='arrow'></i></em><ul>{$operation_detail}</ul>";
            } else {
                $list['operation'] .= $operation_detail;
            }

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
            $list['is_aftersale']=$refund_info['is_aftersale'];
            $list['buyer_message'] = "<span title='{$refund_info['buyer_message']}'>{$refund_info['buyer_message']}</span>";
            $list['add_times'] = date('Y-m-d H:i:s',$refund_info['add_time']);
            $list['goods_name'] = $refund_info['goods_name'];
            if ($refund_info['goods_id'] > 0) {
                $list['goods_name'] = "<a class='open' title='{$refund_info['goods_name']}' href='". urlShop('goods', 'index', array('goods_id' => $refund_info['goods_id'])) .
                "' target='blank'>{$refund_info['goods_name']}</a>";
            }
            $state_array = $model_refund->getRefundStateArray('seller');
            $seller_state = $refund_info['seller_state']==2&&$refund_info['agree_role']==1?4:$refund_info['seller_state'];
            $list['seller_state'] = $state_array[$seller_state];

            $admin_array = $model_refund->getRefundStateArray('admin');
            $list['refund_state'] = $refund_info['seller_state'] == 2 ? $admin_array[$refund_info['refund_state']]:'';

            $list['seller_message'] = "<span title='{$refund_info['seller_message']}'>{$refund_info['seller_message']}</i>";
            $list['admin_message'] = "<span title='{$refund_info['admin_message']}'>{$refund_info['admin_message']}</span>";
            $list['seller_times'] = !empty($refund_info['seller_time']) ? date('Y-m-d H:i:s',$refund_info['seller_time']) : '';
            if ($refund_info['goods_image'] != '') {
                $list['goods_image'] = "<a href='".thumb($refund_info,360)."' target='_blank' class='pic-thumb-tip' onMouseOut='toolTip()' onMouseOver='toolTip(\"<img src=".thumb($refund_info,240).">\")'><i class='fa fa-picture-o'></i></a> ";
            } else {
                $list['goods_image'] = '';
            }
            $list['goods_id'] = !empty($refund_info['goods_id']) ? $refund_info['goods_id'] : '';
            //$list['order_sn'] = $refund_info['order_sn'];
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
            switch ($refund_info['operation_type']){
                case 0:$list['operation_type']='用户申请';break;
                case 1:$list['operation_type']='后台处理';break;
                case 2:$list['operation_type']='渠道抓取';break;
            }
            $list['admin_name'] = $refund_info['admin_name'];
            $data['list'][$refund_info['refund_id']] = $list;
            $oids[] = $refund_info['order_id'] ;
        }
        if( !empty($oids) ) {
	        $oWhere ['order_id'] = array( 'in', array_unique($oids) ) ;
	        $orders = Model()->table('orders')->field('order_id, fx_order_id,buyer_name')->where( $oWhere )->select() ;
	        $rels = $orders ? array_column($orders, 'fx_order_id', 'order_id') : array() ;
	        $list=$orders ? array_column($orders, 'buyer_name', 'order_id') : array() ;
            $member_list=Model('member_fenxiao')->getMemberFenxiao();
            //v($member_list);
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
     * 客服修改退款金额
     */
    public function edit_refund_amountOp()
    {
        $model_refund = Model('refund_return');
        $model_order = Model('order');
        $model_bill = Model('bill');
        $condition = array();
        $condition['refund_id'] = intval($_GET['refund_id']);
        $refund = $model_refund->getRefundReturnInfo($condition);
        $order = $model_order->getOrderInfo(array('order_id'=> $refund['order_id']),array());

        if (chksubmit()) {
            try{
                //开启事务
                $model_refund -> beginTransaction();

                //更新退款管理表-result
                $refund_array = array();
                $refund_array['refund_amount'] = $_POST['refund_amount'];
                $result = $model_refund->editRefundReturn($condition, $refund_array);
                if (!$result) { throw new Exception('更新退款管理表失败'); }

                //更新订单信息-result2
                $condition = array();
                $condition['seller_state'] = 2;
                $condition['refund_state'] = 3;
                $condition['order_id'] = $order['order_id'];
                $refund_new = $model_refund->getRefundList($condition);
                if (count($refund_new) < 1) {
                    $data['refund_amount'] = 0;
                } else {
                    $refund_amount = 0;
                    foreach ($refund_new as $k => $v) {
                        $refund_amount += $v['refund_amount'];
                    }
                    $data['refund_amount'] = $refund_amount;
                }
                $result2 = $model_order->editOrder($data, array('order_id'=> $order['order_id']));
                if (!$result2) { throw new Exception('更新订单信息失败'); }

                //更新商家月结账单-result3
                $admin_time = $refund['admin_time'];
                $condition2 = array();
                $condition2['ob_store_id'] = $refund['store_id']; 
                $condition2['ob_start_date'] = array('lt' , $admin_time);
                $condition2['ob_end_date'] = array('gt' , $admin_time);
                $bill_info = $model_bill->getOrderBillInfo($condition2);
                if ($bill_info) {
                    $bill = Service('Bill');
                    $result3 = $bill->calcRealBill($bill_info);
                    if (!$result3) { throw new Exception('找到商家月结账单，但更新失败'); }
                }

                //加入订单日志-result4
                $log = array();
                $log['order_id'] = $refund['order_id'];
                $log['log_msg'] = "【{$this->admin_info['name']}】处理退款金额修改，原因：{$_POST['log_msg']}";
                $log['log_time'] = time();
                $log['log_role'] = $this->admin_info['gname'];
                $log['log_user'] = $this->admin_info['name'];
                $log['log_orderstate'] = $order['order_state'];
                $result4 = $model_order->addOrderLog($log);
                if (!$result4) { throw new Exception('加入订单日志失败'); }

                $model_refund -> commit();
                showMessage('修改成功!', '', 'html', 'succ');

            }catch(Exception $e){
                $model_refund -> rollback();
                showMessage('数据异常！请刷新重试！','index.php?act=refund&op=index', 'html', 'error');
            }
        }

        Tpl::output('refund',$refund);
        Tpl::setDirquna('shequ');/*网 店 运 维shop wwi.com*/
        Tpl::showpage('refund.edit_refund_amount');
    }

    /**
     * 客服处理撤销退款（改状态为商家拒绝）
     */
    public function cancel_refundOp()
    {
        $model_refund = Model('refund_return');
        $model_order = Model('order');
        $condition = array();
        $condition['refund_id'] = intval($_GET['refund_id']);
        $refund = $model_refund->getRefundReturnInfo($condition);
        $order = $model_order->getOrderInfo(array('order_id'=> $refund['order_id']),array());

        if (chksubmit()) {
            $refund_array = $data = array();
            // 退款表
            $refund_array['seller_state'] = 3;
            $refund_array['refund_state'] = 3;
            $model_refund->editRefundReturn($condition, $refund_array);

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
            $refund_new = $model_refund->getRefundList($condition);
            if (count($refund_new) < 1) {
                $data['refund_amount'] = 0;
                $data['refund_state'] = 0;
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
            $log['order_id'] = $order['order_id'];
            $log['log_msg'] = "【{$this->admin_info['name']}】处理退款状态修改为商家拒绝，原因：{$_POST['log_msg']}";
            $log['log_time'] = time();
            $log['log_role'] = $this->admin_info['gname'];
            $log['log_user'] = $this->admin_info['name'];
            $log['log_orderstate'] = $order['order_state'];
            $model_order->addOrderLog($log);

            showMessage(Language::get('nc_common_save_succ'),'index.php?act=refund&op=index');
        }

        Tpl::output('refund',$refund);
        Tpl::setDirquna('shequ');/*网 店 运 维shop wwi.com*/
        Tpl::showpage('refund.cancel_refund');
    }

    /**
     * 退款处理页
     *
     */
    public function editOp() {
        $model_refund = Model('refund_return');
        $condition = array();
        $condition['refund_id'] = intval($_GET['refund_id']);
        $refund = $model_refund->getRefundReturnInfo($condition);
        $order_id = $refund['order_id'];
        $model_order = Model('order');
        $order = $model_order->getOrderInfo(array('order_id'=> $order_id),array());

        $detail_array = $model_refund->getDetailInfo($condition);
        if (empty($detail_array)) {
            $model_refund->addDetail($refund,$order);
            $detail_array = $model_refund->getDetailInfo($condition);
        }
//        if ($order['payment_time'] > 0) {
            $order['pay_amount'] = $order['order_amount']-$order['rcb_amount']-$order['pd_amount'];//在线支付金额=订单总价格-充值卡支付金额-预存款支付金额
            $out_amount = $order ['pay_amount'] - $order ['refund_amount']; // 可在线退款金额

            $refund_amount = $detail_array ['refund_amount']; // 本次退款总金额
            if ($refund_amount > $out_amount) {
                $refund_amount = $out_amount;
            }
            $order ['pay_refund_amount'] = ncPriceFormat ( $refund_amount );
//        }
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

            if ($refund['refund_state'] != '2') {//检查状态,防止页面刷新不及时造成数据错误
                showMessage(Language::get('nc_common_save_fail'));
            }

            $service = Service('Refund');
            // 非货到付款订单在线支付退款
            if ($order['payment_code'] != 'fenxiao' && $send_payment) {
                try {
                    $detail_array = $service->apiRefund($detail_array);
                } catch (Exception $e) {
                    showMessage($e->getMessage());
                }
            }else{
                $refund['pay_amount'] = $refund_amount;
            }

            if ($detail_array['pay_time'] > 0) {
                $refund['pay_amount'] = $detail_array['pay_amount'];//已完成在线退款金额
            }
            $state = $model_refund->editOrderRefund($refund, $this->admin_info['name']);
            if ($state) {
                $refund_array = array();
                $refund_array['admin_time'] = time();
                $refund_array['refund_state'] = '3';//状态:1为处理中,2为待管理员处理,3为已完成
                $refund_array['admin_message'] = $_POST['admin_message'];
                $model_refund->editRefundReturn($condition, $refund_array);
                //订单退款金额累加到团长退款金额里
                $tuan_condition['config_id'] = $refund['shequ_tuan_id'];
                $tuan_condition['tz_id'] = $refund['shequ_tz_id'];
                /** @var shequ_tuanModel $model_shequ_tuan */
                $model_shequ_tuan = Model('shequ_tuan');
                $tuan_info = $model_shequ_tuan->getOne($tuan_condition);
                $tuan_data['refund_amount'] = $tuan_info['refund_amount']+$refund['refund_amount'];
                $tuan_data['refund_commis_amount'] = $tuan_info['refund_commis_amount']+$refund['shequ_return_amount'];
                $tuan_where['id'] = $tuan_info['id'];
                $model_shequ_tuan->edit($tuan_where,$tuan_data);
                // 发送买家消息
                $param = array();
                $param['code'] = 'refund_return_notice';
                $param['member_id'] = $refund['buyer_id'];
                $param['param'] = array(
                    'refund_url' => urlShop('member_refund', 'view', array('refund_id' => $refund['refund_id'])),
                    'refund_sn' => $refund['refund_sn']
                );
                QueueClient::push('sendMemberMsg', $param);

                $this->log('退款确认，退款编号'.$refund['refund_sn']);
                showMessage(Language::get('nc_common_save_succ'),'index.php?act=refund&op=caiwu');
            } else {
                showMessage(Language::get('nc_common_save_fail'));
            }
        }
        Tpl::output('refund',$refund);
        $info['buyer'] = array();
        if(!empty($refund['pic_info'])) {
            $info = unserialize($refund['pic_info']);
        }
        Tpl::output('pic_list',$info['buyer']);
		Tpl::setDirquna('shequ');/*网 店 运 维shop wwi.com*/
        Tpl::showpage('refund.edit');
    }
    /**
     * 客服退款处理页
     *
     */
    public function kefu_editOp() {
    	$model_refund = Model('refund_return');
    	$condition = array();
    	$condition['refund_id'] = intval($_GET['refund_id']);
    	$refund = $model_refund->getRefundReturnInfo($condition);
    	$order_id = $refund['order_id'];
    	/** @var orderModel $model_order */
    	$model_order = Model('order');
    	$order = $model_order->getOrderInfo(array('order_id'=> $order_id),array());
    	if( !$order ) {
    		showMessage('订单不存在');
    	}
    	if ($order['payment_time'] > 0) {
    		$order['pay_amount'] = $order['order_amount']-$order['rcb_amount']-$order['pd_amount'];//在线支付金额=订单总价格-充值卡支付金额-预存款支付金额
    	}
    	Tpl::output('order',$order);
    	$commis_amount = $order['shequ_return_amount'];
        if ($refund['order_goods_id']){
            $orderGoods = $model_order->getOrderGoodsInfo(array('rec_id'=>$refund['order_goods_id']));
            $commis_amount = $orderGoods['shequ_commis_amount'];
            Tpl::output('order_goods',$orderGoods);
        }
        Tpl::output('commis_amount',$commis_amount);
    	$detail_array = $model_refund->getDetailInfo($condition);
    	if(empty($detail_array)) {
    		$model_refund->addDetail($refund,$order);
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
    		$shequ_return_amount = floatval( $_POST['shequ_return_amount'] );
    		if ($shequ_return_amount>$commis_amount){
                $shequ_return_amount=$commis_amount;
            }
    		$refund_array ['shequ_return_amount']		= $shequ_return_amount ;

    		$res = $model_refund -> editRefundReturn ( $condition, $refund_array ) ;
    		$res2 = $model_refund->editDetail($condition, array('refund_code' => $refund_array ['refund_way']));
    		
    		if( !$res || !$res2) {
    			showMessage('保存失败');
    		} 
    		
    		showMessage('保存成功','index.php?act=refund&op=caiwu');
    	}
    	
    	Tpl::output('refund',$refund);
    	$info['buyer'] = array();
    	if(!empty($refund['pic_info'])) {
    		$info = unserialize($refund['pic_info']);
    	}
    	Tpl::output('pic_list',$info['buyer']);
    	
    	$refund_way = array(
    			'predeposit' => '预存款',
    			'alipay' => '支付宝',
    			'offline' => '线下支付',
    			'yeepay' => '易宝支付',
    			'fenxiao' => '分销支付',
                'wx_jsapi' => '微信支付',
    	) ;
    	Tpl::output('refund_way',$refund_way);
    	
    	Tpl::setDirquna('shequ');/*网 店 运 维shop wwi.com*/
    	Tpl::showpage('refund.edit.kefu');
    }

    /**
     * 退款记录查看页
     *
     */
    public function viewOp() {
        $model_refund = Model('refund_return');
        $condition = array();
        $condition['refund_id'] = intval($_GET['refund_id']);
        $refund = $model_refund->getRefundReturnInfo($condition);
        $reason_list = $model_refund->getReasonList(array(),'',false);
        if(isset($reason_list[$refund['reason_id']])){
            $refund['reason_info'] = $reason_list[$refund['reason_id']]['reason_info'];
        } else{
            $refund['reason_info'] = $refund['goods_id']>0?'其他':'取消订单，全部退款';
        }

        Tpl::output('refund',$refund);
        
        $order_info = Model('order')->getOrderInfo( array('order_id' => $refund['order_id']) ) ;
        Tpl::output('order_info',$order_info);
        
        $info['buyer'] = array();
        if(!empty($refund['pic_info'])) {
            $info = unserialize($refund['pic_info']);
        }
        Tpl::output('pic_list',$info['buyer']);
        $detail_array = $model_refund->getDetailInfo($condition);
        Tpl::output('detail_array',$detail_array);
		Tpl::setDirquna('shequ');/*网 店 运 维shop wwi.com*/
        Tpl::showpage('refund.view');
    }

    /**
     * 退款记录查看关联退款页
     *
     */
    public function view_detailOp() {
        $model_refund = Model('refund_return');
        $condition = array();
        $condition['refund_id'] = intval($_GET['refund_id']);
        $refund = $model_refund->getRefundReturnInfo($condition);
        Tpl::output('refund',$refund);
        
        $order_info = Model('order')->getOrderInfo( array('order_id' => $refund['order_id']) ) ;
        Tpl::output('order_info',$order_info);
        
        $info['buyer'] = array();
        if(!empty($refund['pic_info'])) {
            $info = unserialize($refund['pic_info']);
        }
        Tpl::output('pic_list',$info['buyer']);
        $detail_array = $model_refund->getDetailInfo($condition);
        Tpl::output('detail_array',$detail_array);
        Tpl::setDirquna('shequ');/*网 店 运 维shop wwi.com*/
        Tpl::showpage('refund.view_detail');
    }

    /**
     * 退款退货原因
     */
    public function reasonOp() {
        $model_refund = Model('refund_return');
        $condition = array();

        $reason_list = $model_refund->getReasonList($condition,200);
        Tpl::output('reason_list',$reason_list);
		Tpl::setDirquna('shequ');/*网 店 运 维shop wwi.com*/

        Tpl::showpage('refund_reason.list');
    }

    /**
     * 新增退款退货原因
     *
     */
    public function add_reasonOp() {
        $model_refund = Model('refund_return');
        if (chksubmit()) {
            $reason_array = array();
            $reason_array['reason_info'] = $_POST['reason_info'];
            $reason_array['sort'] = intval($_POST['sort']);
            $reason_array['is_aftersale']=$_POST['is_aftersale'];
            $reason_array['update_time'] = time();
            $state = $model_refund->addReason($reason_array);
            if ($state) {
                $this->log('新增退款退货原因，编号'.$state);
                showMessage(Language::get('nc_common_save_succ'),'index.php?act=refund&op=reason');
            } else {
                showMessage(Language::get('nc_common_save_fail'));
            }
        }
		Tpl::setDirquna('shequ');/*网 店 运 维shop wwi.com*/
        Tpl::showpage('refund_reason.add');
    }

    /**
     * 编辑退款退货原因
     *
     */
    public function edit_reasonOp() {
        $model_refund = Model('refund_return');
        $condition = array();
        $condition['reason_id'] = intval($_GET['reason_id']);
        $reason_list = $model_refund->getReasonList($condition);
        $reason = $reason_list[$condition['reason_id']];
        if (chksubmit()) {
            $reason_array = array();
            $reason_array['reason_info'] = $_POST['reason_info'];
            $reason_array['is_aftersale']=$_POST['is_aftersale'];
            $reason_array['sort'] = intval($_POST['sort']);
            $reason_array['update_time'] = time();
            $state = $model_refund->editReason($condition, $reason_array);
            if ($state) {
                $this->log('编辑退款退货原因，编号'.$condition['reason_id']);
                showMessage(Language::get('nc_common_save_succ'),'index.php?act=refund&op=reason');
            } else {
                showMessage(Language::get('nc_common_save_fail'));
            }
        }
        Tpl::output('reason',$reason);
		Tpl::setDirquna('shequ');/*网 店 运 维shop wwi.com*/
        Tpl::showpage('refund_reason.edit');
    }

    /**
     * 删除退款退货原因
     *
     */
    public function del_reasonOp() {
        $model_refund = Model('refund_return');
        $condition = array();
        $condition['reason_id'] = intval($_GET['reason_id']);
        $state = $model_refund->delReason($condition);
        if ($state) {
            $this->log('删除退款退货原因，编号'.$condition['reason_id']);
            showMessage(Language::get('nc_common_del_succ'),'index.php?act=refund&op=reason');
        } else {
            showMessage(Language::get('nc_common_del_fail'));
        }
    }

    /**
     * 封装共有查询代码
     */
    private function _get_condition($condition) {
        $condition['shequ_tuan_id'] = array('gt',0);
        if(isset($_REQUEST['shequ_tuan_id']) && ($_REQUEST['shequ_tuan_id'] != '')){
            $condition['shequ_tuan_id'] = $_REQUEST['shequ_tuan_id'];
        }
        if(isset($_REQUEST['shequ_tz_id']) && ($_REQUEST['shequ_tz_id'] != '')){
            $condition['shequ_tz_id'] = $_REQUEST['shequ_tz_id'];
        }
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
        if( !empty($_GET['refund_state']) ) {
        	$condition['refund_state'] = intval($_GET['refund_state']) ;
        }
        $sort_fields = array('buyer_name','store_name','goods_id','refund_id','seller_time','refund_amount','buyer_id','store_id');
        if ($_REQUEST['sortorder'] != '' && in_array($_REQUEST['sortname'],$sort_fields)) {
            $order = $_REQUEST['sortname'].' '.$_REQUEST['sortorder'];
        }
        if( $_GET['fxsellerdo'] == 1 ) {
        	//$order = 'seller_time desc';
        }
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
        /** @var refund_returnModel $model_refund */
        $model_refund = Model('refund_return');
        $condition = array();
        if (preg_match('/^[\d,]+$/', $_GET['refund_id'])) {
            $_GET['refund_id'] = explode(',',trim($_GET['refund_id'],','));
            $condition['refund_id'] = array('in',$_GET['refund_id']);
        }
        list($condition,$order) = $this->_get_condition($condition);
        if (!is_numeric($_GET['curpage'])){
            $count = $model_refund->getRefundCount($condition);
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
                Tpl::setDirquna('shequ');
                Tpl::showpage('export.excel');
                exit();
            }
            $limit = false;
        } else {
            $limit1 = ($_GET['curpage']-1) * self::EXPORT_SIZE;
            $limit2 = self::EXPORT_SIZE;
            $limit = $limit1 .','. $limit2;
        }
        $refund_list = $model_refund->getRefundList($condition,'',$order,$limit);
//        echo '<pre>';
//        echo $model_refund->getLastSql();
//        var_dump($refund_list);
//        die;

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
        $oids = $list = array();
        $pic_base_url = UPLOAD_SITE_URL.'/'.ATTACH_PATH.'/refund/';
        /** @var member_fenxiaoModel $fenxiaoMemberModel */
        $fenxiaoMemberModel = Model('member_fenxiao');
        $fenxiaoMembers = $fenxiaoMemberModel->getMemberFenxiao();
        $fenxiaoMembers = array_under_reset($fenxiaoMembers,'member_id');
        $reason_list = $model_refund->getReasonList(array(),'',false);
        foreach ($refund_list as $k => $refund_info) {
        	$order_id = $refund_info['order_id'] ;
            $list[$k]['refund_sn'] = $refund_info['refund_sn']."\t";
            $list[$k]['refund_amount'] = ncPriceFormat($refund_info['refund_amount']);
            if(isset($reason_list[$refund_info['reason_id']])){
                $refund_info['reason'] = $reason_list[$refund_info['reason_id']]['reason_info'];
            } else{
                $refund_info['reason'] = $refund_info['goods_id']>0?'其他':'取消订单，全部退款';
            }
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
            $list[$k]['reason'] = str_replace("\r\n",";",$refund_info['reason']);
            $list[$k]['buyer_message'] = str_replace("\r\n",";",$refund_info['buyer_message']);
            $list[$k]['add_times'] = date('Y-m-d H:i:s',$refund_info['add_time']);
            //$list[$k]['goods_name'] = $refund_info['goods_name'];
            $list[$k]['goods_name'] = !empty($refund_info['goods_id']) ? $refund_info['goods_name'] : implode(" ||| ", array_column($order_goods[$order_id], 'goods_name')) ;
            $state_array = $model_refund->getRefundStateArray('seller');
            $seller_state = $refund_info['seller_state']==2&&$refund_info['agree_role']==1?4:$refund_info['seller_state'];
            $list[$k]['seller_state'] = $state_array[$seller_state];
            $admin_array = $model_refund->getRefundStateArray('admin');
            $list[$k]['refund_state'] = $refund_info['seller_state'] == 2 ? $admin_array[$refund_info['refund_state']]:'';
            $list[$k]['seller_message'] = preg_replace('/[,\r\n]+/i','',$refund_info['seller_message']);
            $list[$k]['admin_message'] = preg_replace('/[,\r\n]+/i','',$refund_info['admin_message']);
            $list[$k]['seller_times'] = !empty($refund_info['seller_time']) ? date('Y-m-d H:i:s',$refund_info['seller_time']) : '';
            if ($refund_info['goods_image'] != '') {
                $list[$k]['goods_image'] = thumb($refund_info,360);
            } else {
                $list[$k]['goods_image'] = '';
            }
            $list[$k]['goods_id'] = !empty($refund_info['goods_id']) ? $refund_info['goods_id'] : implode(" ||| ", array_column($order_goods[$order_id], 'goods_id'));
            $list[$k]['order_sn'] = $refund_info['order_sn']."\t";
            $list[$k]['buyer_name'] = isset($fenxiaoMembers[$refund_info['buyer_id']])?$fenxiaoMembers[$refund_info['buyer_id']]['member_cn_code']:$refund_info['buyer_name'];
            $list[$k]['buyer_id'] = $refund_info['buyer_id'];
            $list[$k]['store_name'] = $refund_info['store_name'];
            $list[$k]['store_id'] = $refund_info['store_id'];
            $list[$k]['refund_way'] = orderPaymentName($refund_info['refund_way']);
            $list[$k]['refund_name'] = $refund_info['refund_name'];
            $list[$k]['refund_account'] = $refund_info['refund_account'];
            $list[$k]['order_id'] = $refund_info['order_id'];
            $list[$k]['express_name'] = $refund_info['express_name'];
            $list[$k]['shipping_code'] = $refund_info['shipping_code']."\t";
            $list[$k]['order_create_time'] = date('Y-m-d H:i:s',$refund_info['order_create_time']);
            $list[$k]['fx_order_id'] = '';
            switch ($refund_info['operation_type']){
                case 0:$list[$k]['operation_type']='用户申请';break;
                case 1:$list[$k]['operation_type']='后台处理';break;
                case 2:$list[$k]['operation_type']='渠道抓取';break;
            }
            $list[$k]['admin_name'] = $refund_info['admin_name'];
            $oids[] = $refund_info['order_id'];
        }

        if( !empty($oids) ) {
        	$oWhere ['order_id'] = array( 'in', array_unique($oids) ) ;
        	$orders = Model()->table('orders')->field('order_id, fx_order_id')->where( $oWhere )->select() ;
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
                'reason' => '申请原因',
                'buyer_message' => '退款说明',
                'add_times' => '申请时间',
                'goods_name' => '涉及商品',
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
        		'refund_way'  => '退款方式',
        		'refund_name'  => '收款姓名',
        		'refund_account'  => '收款帐号',
        		'express_name' => '物流公司',
        		'shipping_code' => '物流单号',
        		'order_create_time' => '订单创建时间',
                'fx_order_id' => '分销订单号',
                'operation_type'=>'退款操作来源',
                'admin_name'=>'退款操作人',
        );
        array_unshift($list, $header);
        
		$csv = new Csv();
	    $export_data = $csv->charset($list,CHARSET,'gbk');
	    $csv->filename = $csv->charset('refund',CHARSET).$_GET['curpage'] . '-'.date('Y-m-d');
	    $csv->export($export_data);
    }
    
    /**
     * 新增订单退款页面
     */
    function go_refundOp()
    {
        $order_id = intval($_REQUEST['order_id']) ;
    	$model_order = Model('order');
    	$condition = array('order_id' => $order_id);
    	$order_list = $model_order->getOrderList($condition, 20, '*', 'order_id desc','', array('order_common','order_goods','store'));
    	if( empty($order_list) ) {
    		showMessage('不存在此订单信息','index.php?act=order','html','error');
    	}
    	$order_state = $order_list[$order_id]['order_state'] ;
    	if( $order_state < 20 ) {
    		showMessage('订单状态不能退款','index.php?act=order','html','error');
    	}

    	$model_refund_return = Model('refund_return');
    	$order_list = $model_refund_return->getGoodsRefundList($order_list,1);//订单商品的退款退货显示

    	$refund_all = $order_list[$order_id]['refund_list'][0];
        //客服组可以退款
    	if (!empty($refund_all) && $refund_all['seller_state'] < 3 && $this->admin_info['gid'] != 2) {//订单全部退款商家审核状态:1为待审核,2为同意,3为不同意
    		showMessage('订单已全额退款','index.php?act=order','html','error');
    	}
    	Tpl::output('order', $order_list[$order_id]);

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
    	
    	Tpl::setDirquna('shequ');
    	Tpl::showpage('refund.add');
    	
    }
    /**
     * 新增订单退款记录
     */
    function add_refundOp()
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

            if( $_POST['order_state'] == '20' ) {
                $service = Service("Refund") ;
                $params = array(
                    'order_id' => 	$_POST['order_id'],
                    'buyer_message' => $_POST['buyer_message_all'],
                ) ;
                $params['operation_type']=1;
                if(!empty($pic_info)){
                    $params['pic_info']=$pic_info;
                }
                $params['admin_name'] = $this->admin_info['name'];
                $service -> addRefundAll($params,$message) ;
                showMessage($message, 'index.php?act=refund') ;
                exit;
            }
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
                    'refund_type' => 1,
                    'return_type' => 1,
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
        showMessage("退款记录保存成功", 'index.php?act=refund') ;
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

    /**
     * 删除图片
     */
    public function delimgOp(){
        $path=$_GET['file_name'];
        unlink($path);
        echo json_encode(array('state'=>1,'msg'=>"删除成功"));
    }
    /**
     * 客服代商家审核
     *
     */
    public function seller_editOp() {
    	$model_refund = Model('refund_return');
    	$condition = array();
    	$condition['refund_id'] = intval($_GET['refund_id']);
    	$refund = $model_refund->getRefundReturnInfo($condition);
    	$order_id = $refund['order_id'];
    	$model_order = Model('order');
    	$order = $model_order->getOrderInfo(array('order_id'=> $order_id),array());
    	if ($order['payment_time'] > 0) {
    		$order['pay_amount'] = $order['order_amount']-$order['rcb_amount']-$order['pd_amount'];//在线支付金额=订单总价格-充值卡支付金额-预存款支付金额
    	}
    	Tpl::output('order',$order);
    	$detail_array = $model_refund->getDetailInfo($condition);
        $order_info = $model_order->getOrderInfo(array('order_id'=> $order_id),array());
    	if(empty($detail_array)) {
    		$model_refund->addDetail($refund,$order);
    		$detail_array = $model_refund->getDetailInfo($condition);
    	}
    	Tpl::output('detail_array',$detail_array);
    	if (chksubmit()) {
    		$reload = 'index.php?act=refund';

    		if ($refund['seller_state'] != '1') {//检查状态,防止页面刷新不及时造成数据错误
    			showDialog(Language::get('wrong_argument'),$reload,'error');
    		}
    		$order_id = $refund['order_id'];
    		$refund_array = array();
    		$refund_array['seller_time'] = time();
    		$refund_array['seller_state'] = $_POST['seller_state'];//卖家处理状态:1为待审核,2为同意,3为不同意
    		$refund_array['seller_message'] = $_POST['seller_message'];
    		if ($refund_array['seller_state'] == '3') {
    			$refund_array['refund_state'] = '3';//状态:1为处理中,2为待管理员处理,3为已完成
    		} else {
    			$refund_array['seller_state'] = '2';
    			$refund_array['refund_state'] = '2';
    		}
    		$state = $model_refund->editRefundReturn($condition, $refund_array);
    		if ($state) {
    			if ($refund_array['seller_state'] == '3' && $refund['order_lock'] == '2') {
    				$model_refund->editOrderUnlock($order_id);//订单解锁
    			}
    			$log = array();
	        	$log['order_id'] = $order_id;
	        	$log['log_msg'] = "代替商家处理退款申请，审核意见：". str_replace(array('2','3'), array('同意','不同意'), $_POST['seller_state']) ;
	        	$log['log_time'] = time();
	        	$log['log_role'] = 'admin';
	        	$log['log_user'] = $this->admin_info['name'];
	        	$log['log_orderstate'] = $order_info['order_state'];
	        	$model_order->addOrderLog($log);
    		
    			// 发送买家消息
    			$param = array();
    			$param['code'] = 'refund_return_notice';
    			$param['member_id'] = $refund['buyer_id'];
    			$param['param'] = array(
    					'refund_url'=> urlShop('member_refund', 'view', array('refund_id' => $refund['refund_id'])),
    					'refund_sn' => $refund['refund_sn']
    			);
    			QueueClient::push('sendMemberMsg', $param);
    			showDialog(Language::get('nc_common_save_succ'),$reload,'succ');
    		} else {
    			showDialog(Language::get('nc_common_save_fail'),$reload,'error');
    		}
    	}
    	Tpl::output('refund',$refund);
    	$info['buyer'] = array();
    	if(!empty($refund['pic_info'])) {
    		$info = unserialize($refund['pic_info']);
    	}
    	Tpl::output('pic_list',$info['buyer']);
    	Tpl::setDirquna('shequ');/*网 店 运 维shop wwi.com*/
    	Tpl::showpage('seller.edit');
    }

    public function store_rejectOp(){
        Tpl::setDirquna('shequ');/*网 店 运 维shop wwi.com*/
        Tpl::showpage('store_reject.index');
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
        $refund_list = $model_refund->getRefundList($condition,$_POST['rp'],$order);
        $data = array();
        $data['now_page'] = $model_refund->shownowpage();
        $data['total_num'] = $model_refund->gettotalnum();
        $pic_base_url = UPLOAD_SITE_URL.'/'.ATTACH_PATH.'/refund/';
        foreach ($refund_list as $k => $refund_info) {
            $list = array();
            $list['operation'] = "<a class=\"btn orange\" onclick='refuse_restore(".$refund_info['refund_id'].")'>恢复</a>";
            $list['operation'].= "<a class=\"btn orange\" onclick='refuse_agree(".$refund_info['refund_id'].")'><i class=\"fa fa-gavel\"></i>同意</a>";
            $list['operation'] .= "<a  class=\"btn orange\" href=\"index.php?act=refund&op=edit_refund_amount&refund_id={$refund_info['refund_id']}\"><i class=\"fa fa-list-alt\"></i>修改金额</a>";
            $list['operation'] .= "<a class=\"btn green\" href=\"index.php?act=refund&op=view&refund_id={$refund_info['refund_id']}\" target=\"_blank\">查看</a>";
            $list['operation'] .= "<a class=\"btn green\" onclick='change_dispaly_state(".$refund_info['refund_id'].",".$refund_info['display_state'].")'>隐藏</a>";
            $list['operation'] .= "<a class=\"btn green\" href=\"index.php?act=refund&op=view_detail&refund_id={$refund_info['refund_id']}\" target=\"_blank\">查看关联退款</a>";
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
            $list['admin_name'] = $refund_info['admin_name'];
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

    /*商家已拒绝的订单*/
    public  function get_view_detail_xmlOp(){
        $model_refund = Model('refund_return');
        $condition = array();
        $condition['order_sn']=$_GET['order_sn'];
        list($condition,$order) = $this->_get_condition($condition);
        $refund_list = $model_refund->getRefundList($condition,$_POST['rp'],$order);
        $data = array();
        $data['now_page'] = $model_refund->shownowpage();
        $data['total_num'] = $model_refund->gettotalnum();
        $pic_base_url = UPLOAD_SITE_URL.'/'.ATTACH_PATH.'/refund/';
        foreach ($refund_list as $k => $refund_info) {
            $list = array();
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
            $list['admin_name'] = $refund_info['admin_name'];
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
    //隐藏
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
    //批量隐藏
    public function changeDisplaysOp(){
        $refund_ids = $_POST['refund_ids'];
        if (!empty($refund_ids)) {
            foreach ($refund_ids as $id) {
                $refund_id=intval($id);
                $display_state=0;
                $result=Model('refund_return')->where(array('refund_id'=>$refund_id))->update(array('display_state'=>$display_state));
                if($result){
                    $data['msg'] .= $id.'更新成功，';
                }else{
                    $data['msg'] .= $id.'更新失败，请稍后再试，';
                }
            }
            $data['state'] = 1;
            echo json_encode($data);
        }else{
            echo json_encode(array('state'=>0,'msg'=>'未选中数据'));
        }
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

     /*批量恢复退款状态*/
    public function refuse_restoresOp(){
        $refund_ids = $_GET['refund_ids'];
        if (!empty($refund_ids)) {
            foreach ($refund_ids as $id) {
                $condition = array();
                $condition['refund_id'] = intval($id);
                $refund = Model('refund_return')->getRefundReturnInfo($condition);
                $order_id = $refund['order_id'];
                $order_sn = $refund['order_sn'];
                $order_info = Model('order')->getOrderInfo(array('order_id'=> $order_id),array());
                if($order_info['order_state']=="20"){
                    $refund_array = array();
                    $refund_array['order_lock']="2";
                    $res=Model('order')->where(array('order_id'=>$order_id))->update(array("lock_state"=>"1"));
                    if(!$res){
                        $data['msg'] .= $order_sn.'恢复失败，订单加锁操作失败，';
                        continue;
                    }
                }
                $refund_array = array();
                $refund_array['add_time'] = time();
                $refund_array['seller_state'] = '1';
                $refund_array['refund_state'] = '1';
                $state = Model('refund_return')->editRefundReturn($condition, $refund_array);
                if ($state) {
                    $log = array();
                    $log['order_id'] = $order_id;
                    $log['log_msg'] = "后台操作商家已拒绝订单状态为处理中" ;
                    $log['log_time'] = time();
                    $log['log_role'] = 'admin';
                    $log['log_user'] = $this->admin_info['name'];
                    $log['log_orderstate'] = $order_info['order_state'];
                    Model('order')->addOrderLog($log);
                    $data['msg'] .= $order_sn.'操作成功，';
                } else {
                    $data['msg'] .= $order_sn.'操作失败，';
                }
            }
            $data['state'] = 1;
            echo json_encode($data);
        }else{
            echo json_encode(array('state'=>0,'msg'=>'未选中数据'));
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
    /*批量同意退款*/
    public function refuse_agreesOp(){
        $refund_ids = $_GET['refund_ids'];
        if (!empty($refund_ids)) {
            foreach ($refund_ids as $id) {
                $condition = array();
                $condition['refund_id'] = intval($id);
                $refund = Model('refund_return')->getRefundReturnInfo($condition);
                $order_id = $refund['order_id'];
                $order_sn = $refund['order_sn'];
                $agreeRefund = Model('refund_return')->getRefundReturnInfo(array(
                    'order_id'=>$order_id,
                    'seller_state'=>array ('lt','3' ),
                ));
                if($agreeRefund){
                    $data['msg'] .= $order_sn.'同意操作失败，重复维权，';
                    continue;
                }
                $order_info = Model('order')->getOrderInfo(array('order_id'=> $order_id),array());
                if($order_info['order_state']==20){
                    $refund_array['order_lock']="2";
                    $res=Model('order')->where(array('order_id'=>$order_id))->update(array("lock_state"=>"1"));
                    if(!$res){
                        $data['msg'] .= $order_sn.'同意操作失败，订单加锁操作失败，';
                        continue;
                    }
                }
                $refund_array = array();
                $refund_array['seller_state'] = '2';
                $refund_array['refund_state'] = '2';
                $refund_array['agree_role'] = '1'; // 客服同意
                $state = Model('refund_return')->editRefundReturn($condition, $refund_array);
                if ($state) {
                    $log = array();
                    $log['order_id'] = $order_id;
                    $log['log_msg'] = "后台操作商家已拒绝订单状态由拒绝变为同意" ;
                    $log['log_time'] = time();
                    $log['log_role'] = 'admin';
                    $log['log_user'] = $this->admin_info['name'];
                    $log['log_orderstate'] = $order_info['order_state'];
                    Model('order')->addOrderLog($log);
                    $data['msg'] .= $order_sn.'操作成功，';
                } else {
                    $data['msg'] .= $order_sn.'操作失败，';
                }
            }
            $data['state'] = 1;
            echo json_encode($data);
        }else{
            echo json_encode(array('state'=>0,'msg'=>'未选中数据'));
        }
    }
    public function kefu_rejectOp(){
        /** @var refund_returnModel $model_refund */
        $model_refund = Model('refund_return');
        $condition = array();
        $condition['refund_id'] = intval($_GET['refund_id']);
        $refund = $model_refund->getRefundReturnInfo($condition);
        $order_id = $refund['order_id'];
        /** @var orderModel $model_order */
        $model_order = Model('order');
        $order_info = $model_order->getOrderInfo(array('order_id'=> $order_id),array());
        $refund_array = array();
        $refund_array['seller_state'] = '3';
        $refund_array['refund_state'] = '3';
        $refund_array['agree_role'] = '1'; // 客服同意
        $state = $model_refund->editRefundReturn($condition, $refund_array);
            if ($state) {
                $model_refund->editOrderUnlock($order_id);
                $log = array();
                $log['order_id'] = $order_id;
                $log['log_msg'] = "后台操作商家已同意订单状态由同意变为拒绝" ;
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
    public function changeRemarkOp(){
        $model_refund = Model('refund_return');
        $order_sn=$_POST['order_sn'];
        $buyer_message=$_POST['text'];
        $model_refund->where(array('order_sn'=>$order_sn))->update(array('buyer_message'=>$buyer_message));
    }


    public function upload_imgOp(){
        $refund_id = intval($_GET['refund_id']);
        Tpl::output('refund_id' , $refund_id);
        if($_GET['form_submit']=='ok'){
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
            }
        }
        if($_POST['imgs']){
            $refund_info = Model('refund_return')->getRefundReturnInfo(array('refund_id'=>intval($_POST['refund_id'])),'refund_type');
            $imgs_arr = array();
            $imgs_arr['buyer'] = explode(',' , $_POST['imgs']);
            $imgs_arr = serialize($imgs_arr);
            $data['pic_info'] = $imgs_arr;
            $condition['refund_id'] = intval($_POST['refund_id']);
            $returnUrl = '';
            if($refund_info['refund_type']==1){
                $returnUrl = 'index.php?act=refund&op=view&refund_id='.intval($_POST['refund_id']);
            }else{
                $returnUrl = 'index.php?act=return&op=view&return_id='.intval($_POST['refund_id']);
            }

            if(Model('refund_return')->editRefundReturn($condition, $data)){
                header("Location:".$returnUrl);
            }

        }
        Tpl::setDirquna('shequ');
        Tpl::showpage('refund.upload' ,'null_layout');
    }
}
