<?php
/**
 * 购买流程
 * * @汉购网 (c) 2015-2018 ShopWWI Inc. (http://www.hangowa.com)
 * @license    http://www.shopwwi.c om
 * @link       交流群号：
 * @since      汉购网提供技术支持 授权请购买shopnc授权
 */



defined('ByShopWWI') or exit('Access Invalid!');
class buyControl extends BaseBuyControl {

    public function __construct() {
        parent::__construct();
        Language::read('home_cart_index');
        if (!$_SESSION['member_id']){
            redirect(urlLogin('login', 'index', array('ref_url' => request_uri())));
        }
        //验证该会员是否禁止购买
        if(!$_SESSION['is_buy']){
            showMessage(Language::get('cart_buy_noallow'),'','html','error');
        }
        Tpl::output('hidden_rtoolbar_cart', 1);
    }

    public function uploadOp(){
        if(!empty($_POST)){
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
            if (!in_array(strtolower($curFileType), array('csv', 'xls', 'xlsx'))) {
                //showMessage('文件类型不合法'.$file_name_array[count($file_name_array)-1],'','html','error');
                $data['state'] = false;
                $data['msg'] = '请上传csv、xls、xlsx文件';
                echo json_encode($data);
                die();
            }
            /**
             * 文件大小判定
             */
            if($file['size'] > intval(ini_get('upload_max_filesize'))*1024*1024){
                //showMessage('文件过大','','html','error');
                $data['state'] = false;
                $data['msg'] = '文件大小不可以超过'.ini_get(upload_max_filesize)."M";
                echo json_encode($data);
                die();
            }
            $data = $this->_excelToArray($curFileType,$file['tmp_name']);
            exit(json_encode(array('state'=>1,'data'=>$data)));
        }

        exit(json_encode(array('state'=>0,'msg'=>'文件上传错误')));
    }
    /**
     * 实物商品 购物车、直接购买第一步:选择收获地址和配送方式
     */
    public function buy_step1Op() {


        /** @var memberModel $member_model */
        $member_model = Model('member');
        if(empty($_SESSION['member_id'])){
            showMessage('用户未登录', '', 'html', 'error');
        }
        $member_info = $member_model->getMemberInfoByID($_SESSION['member_id'],'b2b_purchaser');

        if (!$member_info['b2b_purchaser']) {
            showMessage('您不是采购商', '', 'html', 'error');
        }
        //得到购买数据
        /** @var b2b_buyLogic $logic_buy */
        $logic_buy = Logic('b2b_buy');
        $result = $logic_buy->buyStep1($_POST['cart_id'], $_POST['ifcart'], $_SESSION['member_id'], $_SESSION['store_id']);
        if (!$result['state']) {
            showMessage($result['msg'], '', 'html', 'error');
        } else {
            $result = $result['data'];
        }

        //商品金额计算(分别对每个商品/优惠套装小计、每个店铺小计)
        Tpl::output('cart_list', $result['cart_list']);
        Tpl::output('goods_total', $result['goods_total']);

        //输出用户默认收货地址
        Tpl::output('address_info', $result['address_info']);

        //输出有货到付款时，在线支付和货到付款及每种支付下商品数量和详细列表
        Tpl::output('pay_goods_list', $result['pay_goods_list']);
        Tpl::output('ifshow_offpay', $result['ifshow_offpay']);
        Tpl::output('deny_edit_payment', $result['deny_edit_payment']);

        //输出是否有门店自提支付
        Tpl::output('ifshow_chainpay', $result['ifshow_chainpay']);
        Tpl::output('chain_store_id', $result['chain_store_id']);

        //不提供增值税发票时抛出true(模板使用)
        Tpl::output('vat_deny', $result['vat_deny']);

        //增值税发票哈希值(php验证使用)
        Tpl::output('vat_hash', $result['vat_hash']);

        //输出默认使用的发票信息
        Tpl::output('inv_info', $result['inv_info']);

        //删除购物车无效商品
        $logic_buy->delCart($_POST['ifcart'], $_SESSION['member_id'], $_POST['invalid_cart']);

        //标识购买流程执行步骤
        Tpl::output('buy_step','step2');

        Tpl::output('ifcart', $_POST['ifcart']);


        $current_goods_info = current($result['cart_list']);
        Tpl::output('current_goods_info',$current_goods_info[0]);

        // 小能客服系统
        Tpl::output('is_checkout', 1);
        $_list = "";
        $cartprice = 0;
        $cart_list = $current_goods_info;
        if (count($cart_list)) {
            foreach ((array) $cart_list as $val) {
                $_list[] = array(
                    'id' => $val['goods_id'],
                    'count' => (string) $val['goods_num'],
                    'name' => (string) $val['goods_name'],
                    'imageurl' => cthumb($val['goods_image'], 60, $val['store_id']),
                    'url' => (string) $val['goods_id'],
                    'siteprice' => $val['goods_price'],
                // 'sellerid'=> '',
                );
                $cartprice += $val['goods_total'];
            }
            $_carts = json_encode($_list);
        }
        Tpl::output('items', $_carts);
        Tpl::output('cartprice', $cartprice);

        Tpl::showpage('buy_step1');
    }

    /**
     * 生成订单
     *
     */
    public function buy_step2Op() {
        /** @var b2b_buyLogic $logic_buy */
        $logic_buy = Logic('b2b_buy');
        $result = $logic_buy->buyStep2($_POST, $_SESSION['member_id'], $_SESSION['member_name'], $_SESSION['member_email']);
        if(!$result['state']) {
            showMessage($result['msg'], 'index.php?act=cart', 'html', 'error');
        }

        //转向到商城支付页面
        redirect('index.php?act=buy&op=pay&pay_sn='.$result['data']['pay_sn']);
    }

    /**
     * 下单时支付页面
     */
    public function payOp() {
        $pay_sn = $_GET['pay_sn'];
        if (!preg_match('/^\d{18}$/',$pay_sn)){
            showMessage(Language::get('cart_order_pay_not_exists'),'index.php?act=member_order','html','error');
        }

        //查询支付单信息
        /** @var b2b_orderModel $model_order */
        $model_order= Model('b2b_order');
        $pay_info = $model_order->getOrderPayInfo(array('pay_sn'=>$pay_sn,'buyer_id'=>$_SESSION['member_id']),true);
        if(empty($pay_info)){
            showMessage(Language::get('cart_order_pay_not_exists'),'index.php?act=member_order','html','error');
        }

        Tpl::output('pay_info',$pay_info);

        //取子订单列表
        $condition = array();
        $condition['pay_sn'] = $pay_sn;
        $condition['order_state'] = array('in',array(ORDER_STATE_NEW,ORDER_STATE_PAY));
        $order_list = $model_order->getOrderList($condition,'','*','','',array('order_common'),true);
        if (empty($order_list)) {
            showMessage('未找到需要支付的订单','index.php?act=member_order','html','error');
        }

        //取特殊类订单信息
        $this->_getOrderExtendList($order_list);

        //定义输出数组
        $pay = array();
        //支付提示主信息
        $pay['order_remind'] = '';
        //重新计算支付金额
        $pay['pay_amount_online'] = 0;
        $pay['pay_amount_offline'] = 0;
        //订单总支付金额(不包含货到付款)
        $pay['pay_amount'] = 0;
        //充值卡支付金额(之前支付中止，余额被锁定)
        $pay['payd_rcb_amount'] = 0;
        //预存款支付金额(之前支付中止，余额被锁定)
        $pay['payd_pd_amount'] = 0;
        //还需在线支付金额(之前支付中止，余额被锁定)
        $pay['payd_diff_amount'] = 0;
        //账户可用金额
        $pay['member_pd'] = 0;
        $pay['member_rcb'] = 0;

        $logic_order = Logic('order');

        $invoice = null;
        //计算相关支付金额
        foreach ($order_list as $key => $order_info) {
            if (!in_array($order_info['payment_code'],array('offline','chain'))) {
                if ($order_info['order_state'] == ORDER_STATE_NEW) {
                    $pay['pay_amount_online'] += $order_info['order_amount'];
                    $pay['payd_rcb_amount'] += $order_info['rcb_amount'];
                    $pay['payd_pd_amount'] += $order_info['pd_amount'];
                    $pay['payd_diff_amount'] += $order_info['order_amount'] - $order_info['rcb_amount'] - $order_info['pd_amount'];
                }
                $pay['pay_amount'] += $order_info['order_amount'];
            } else {
                $pay['pay_amount_offline'] += $order_info['order_amount'];
            }
            //显示支付方式
            if ($order_info['payment_code'] == 'offline') {
                $order_list[$key]['payment_type'] = '货到付款';
            } elseif ($order_info['payment_code'] == 'chain') {
                $order_list[$key]['payment_type'] = '门店支付';
            } else {
                $order_list[$key]['payment_type'] = '在线支付';
            }
            $order_list[$key]['invoice'] = $order_info['extend_order_common']['invoice_info'];
            $invoice = empty($order_info['extend_order_common']['invoice_info'])?false:true;
        }
        if ($order_info['chain_id'] && $order_info['payment_code'] == 'chain') {
            $order_list[0]['order_remind'] = '下单成功，请在'.CHAIN_ORDER_PAYPUT_DAY.'日内前往门店提货，逾期订单将自动取消。';
            $flag_chain = 1;
        }

        Tpl::output('order_list',$order_list);
        Tpl::output('invoice',$invoice);

        //如果线上线下支付金额都为0，转到支付成功页
        if (empty($pay['pay_amount_online']) && empty($pay['pay_amount_offline'])) {
            redirect('index.php?act=buy&op=pay_ok&pay_sn='.$pay_sn.'&is_chain='.$flag_chain.'&pay_amount='.ncPriceFormat($pay_amount));
        }

        //是否显示站内余额操作(如果以前没有使用站内余额支付过且非货到付款)
        $pay['if_show_pdrcb_select'] = ($pay['pay_amount_offline'] == 0 && $pay['payd_rcb_amount'] == 0 && $pay['payd_pd_amount'] == 0);

        //输出订单描述
        if (empty($pay['pay_amount_online'])) {
            $pay['order_remind'] = '下单成功，我们会尽快为您发货，请保持电话畅通。';
        } elseif (empty($pay['pay_amount_offline'])) {
            $pay['order_remind'] = '请您在'.(ORDER_AUTO_CANCEL_TIME*60).'分钟内完成支付，逾期订单将自动取消。 ';
        } else {
            $pay['order_remind'] = '部分商品需要在线支付，请您在'.(ORDER_AUTO_CANCEL_TIME*60).'分钟内完成支付，逾期订单将自动取消。';
        }
        if (!empty($order_list[0]['order_remind'])) {
            $pay['order_remind'] = $order_list[0]['order_remind'];
        }

        if ($pay['pay_amount_online'] > 0) {
            //显示支付接口列表
            /** @var b2b_paymentModel $model_payment */
            $model_payment = Model('b2b_payment');
            $condition = array();
            $payment_list = $model_payment->getPaymentOpenList($condition);
            if (!empty($payment_list)) {
                unset($payment_list['predeposit']);
                unset($payment_list['offline']);
            }
            if (empty($payment_list)) {
                showMessage('订单提交成功，请使用线下支付！','index.php?act=member_order','html','error');
            }
            Tpl::output('payment_list',$payment_list);
        }
        if ($pay['if_show_pdrcb_select']) {
            //显示预存款、支付密码、充值卡
            $available_predeposit = $available_rc_balance = 0;
            $buyer_info = Model('member')->getMemberInfoByID($_SESSION['member_id']);
            if (floatval($buyer_info['available_predeposit']) > 0) {
                $pay['member_pd'] = $buyer_info['available_predeposit'];
            }
            if (floatval($buyer_info['available_rc_balance']) > 0) {
                $pay['member_rcb'] = $buyer_info['available_rc_balance'];
            }
            $pay['member_paypwd'] = $buyer_info['member_paypwd'] ? true : false;
        }

        Tpl::output('pay',$pay);

        //标识 购买流程执行第几步
        //Tpl::output('buy_step',$pay['pay_amount_online'] >0?'step3':'step4');
        Tpl::output('buy_step','step3');

        //360代码
        $mvq['is_order'] = 1;
        $mvq['member_id'] = isset($_SESSION['member_id']) ? $_SESSION['member_id'] : "";
        $mvq['member_name'] = isset($_SESSION['member_name']) ? $_SESSION['member_name'] : "";
        //商品列表
        $order_ids = array_column($order_list, 'order_id') ;
        $order_items = Model("order_goods") -> where ( "order_id IN (". implode(",", $order_ids) .")" ) -> select () ;
        Tpl::output('order_items', $order_items);
        Tpl::output('mvq', $mvq);
        
        // 小能客服系统
        Tpl::output('is_paycenter', 1);
        Tpl::output('orderid', $order_list[0]['order_sn']);
        Tpl::output('orderprice', $order_list[0]['order_amount']);

        Tpl::showpage('buy_step2');
    }

    /**
     * 特殊订单支付最后一步界面展示（目前只有预定）
     * @param unknown $order_list
     */
    private function _getOrderExtendList(& $order_list) {
        //预定订单
        if ($order_list[0]['order_type'] == 2) {
            $order_info = $order_list[0];
            $result = Logic('order_book')->getOrderBookInfo($order_info);
            if (!$result['data']['if_buyer_pay']) {
                showMessage('未找到需要支付的订单','index.php?act=member_order','html','error');
            }
            $order_list[0] = $result['data'];
            $order_list[0]['order_amount'] = $order_list[0]['pay_amount'];
            $order_list[0]['order_state'] = ORDER_STATE_NEW;
            if ($order_list[0]['if_buyer_repay']) {
                $order_list[0]['order_remind'] = '请您在 '.date('Y-m-d H:i',$order_list[0]['book_list'][1]['book_end_time']+1).' 之前完成支付，否则订单会被自动取消。';
            }
        }
    }

    /**
     * 预存款充值下单时支付页面
     */
    public function pd_payOp() {
        $pay_sn = $_GET['pay_sn'];
        if (!preg_match('/^\d{18}$/',$pay_sn)){
            showMessage(Language::get('para_error'),urlMember('predeposit'),'html','error');
        }

        //查询支付单信息
        $model_order= Model('predeposit');
        $pd_info = $model_order->getPdRechargeInfo(array('pdr_sn'=>$pay_sn,'pdr_member_id'=>$_SESSION['member_id']));
        if(empty($pd_info)){
            showMessage(Language::get('para_error'),'','html','error');
        }
        if (intval($pd_info['pdr_payment_state'])) {
            showMessage('您的订单已经支付，请勿重复支付',urlMember('predeposit'),'html','error');
        }
        Tpl::output('pdr_info',$pd_info);

        //显示支付接口列表
        $model_payment = Model('b2b_payment');
        $condition = array();
        $condition['payment_code'] = array('not in',array('offline','predeposit','wxpay'));
        $condition['payment_state'] = 1;
        $payment_list = $model_payment->getPaymentList($condition);
        if (empty($payment_list)) {
            showMessage('暂未找到合适的支付方式',urlMember('predeposit'),'html','error');
        }
        Tpl::output('payment_list',$payment_list);

        //标识 购买流程执行第几步
        Tpl::output('buy_step','step3');
        Tpl::showpage('predeposit_pay');
    }

    /**
     * 支付成功页面
     */
    public function pay_okOp() {
        $pay_sn = $_GET['pay_sn'];
        if (!preg_match('/^\d{18}$/',$pay_sn)){
            showMessage(Language::get('cart_order_pay_not_exists'),'index.php?act=member_order','html','error');
        }

        //查询支付单信息
        /** @var b2b_orderModel $model_order */
        $model_order= Model('b2b_order');
        $pay_info = $model_order->getOrderPayInfo(array('pay_sn'=>$pay_sn,'buyer_id'=>$_SESSION['member_id']));
        if(empty($pay_info)){
            showMessage(Language::get('cart_order_pay_not_exists'),'index.php?act=member_order','html','error');
        }
        Tpl::output('pay_info',$pay_info);

        Tpl::output('buy_step','step4');

        // 小能客服系统
        Tpl::output('is_payresult', 1);
        Tpl::output('orderid', $pay_info['order_sn']);
        
        Tpl::showpage('buy_step3');
    }

    /**
     * 加载买家收货地址
     *
     */
    public function load_addrOp() {
        /** @var b2b_addressModel $model_addr */
        $model_addr = Model('b2b_address');
        //如果传入ID，先删除再查询
        if (!empty($_GET['id']) && intval($_GET['id']) > 0) {
            $model_addr->delAddress(array('address_id'=>intval($_GET['id']),'member_id'=>$_SESSION['member_id']));
        }
        $condition = array();
        $condition['member_id'] = $_SESSION['member_id'];
        if (!C('delivery_isuse')) {
            $condition['dlyp_id'] = 0;
            $order = 'dlyp_id asc,address_id desc';
        }
        $list = $model_addr->getAddressList($condition,$order);
        Tpl::output('address_list',$list);
        Tpl::showpage('buy_address.load','null_layout');
        exit;
    }

    /**
     * 载入门店自提点
     */
    public function load_chainOp() {
        $list = Model('chain')->getChainList(array('area_id'=>intval($_GET['area_id']),'store_id'=>intval($_GET['store_id'])),
                'chain_id,chain_name,area_info,chain_address');
        echo $_GET['callback'].'('.json_encode($list).')';
    }

    /**
     * 选择不同地区时，异步处理并返回每个店铺总运费以及本地区是否能使用货到付款
     * 如果店铺统一设置了满免运费规则，则运费模板无效
     * 如果店铺未设置满免规则，且使用运费模板，按运费模板计算，如果其中有商品使用相同的运费模板，则两种商品数量相加后再应用该运费模板计算（即作为一种商品算运费）
     * 如果未找到运费模板，按免运费处理
     * 如果没有使用运费模板，商品运费按快递价格计算，运费不随购买数量增加
     */
    public function change_addrOp() {
        $logic_buy = Logic('buy');
        if (empty($_POST['city_id'])) {
            $_POST['city_id'] = $_POST['area_id'];
        }

        $data = $logic_buy->changeAddr($_POST['freight_hash'], $_POST['city_id'], $_POST['area_id'], $_SESSION['member_id']);

        if(!empty($data)) {
            exit(json_encode($data));
        } else {
            exit('error');
        }
    }

    //根据门店自提站ID计算商品库存
    public function change_chainOp() {
        $logic_buy = Logic('buy');
        $data = $logic_buy->changeChain($_POST['chain_id'],$_POST['product']);
        if(!empty($data)) {
            exit(json_encode($data));
        } else {
            exit('error');
        }
    }

     /**
      * 添加新的收货地址
      *
      */
     public function add_addrOp(){
         /** @var b2b_addressModel $model_addr */
        $model_addr = Model('b2b_address');
        if (chksubmit()){
            $count = $model_addr->getAddressCount(array('member_id'=>$_SESSION['member_id']));
            if ($count >= 20) {
                exit(json_encode(array('state'=>false,'msg'=>'最多允许添加20个有效地址')));
            }
            //验证表单信息
            $obj_validate = new Validate();
            $obj_validate->validateparam = array(
                array("input"=>$_POST["true_name"],"require"=>"true","message"=>Language::get('cart_step1_input_receiver')),
                array("input"=>$_POST["area_id"],"require"=>"true","validator"=>"Number","message"=>Language::get('cart_step1_choose_area'))
            );
            $error = $obj_validate->validate();
            if ($error != ''){
                $error = strtoupper(CHARSET) == 'GBK' ? Language::getUTF8($error) : $error;
                exit(json_encode(array('state'=>false,'msg'=>$error)));
            }
            $data = array();
            $data['member_id'] = $_SESSION['member_id'];
            $data['true_name'] = $_POST['true_name'];
            $data['area_id'] = intval($_POST['area_id']);
            $data['city_id'] = intval($_POST['city_id']);
            $data['area_info'] = $_POST['region'];
            $data['address'] = $_POST['address'];
            $data['tel_phone'] = $_POST['tel_phone'];
            $data['mob_phone'] = $_POST['mob_phone'];
            $insert_id = $model_addr->addAddress($data);
            if ($insert_id){
                exit(json_encode(array('state'=>true,'addr_id'=>$insert_id)));
            }else {
                exit(json_encode(array('state'=>false,'msg'=>'新地址添加失败')));
            }
        } else {
            Tpl::showpage('buy_address.add','null_layout');
            exit;
        }
     }

     /**
      * 添加新的门店自提点
      *
      */
     public function add_chainOp(){
         Tpl::showpage('buy_address.add_chain','null_layout');
     }

    /**
     * 加载买家发票列表，最多显示10条
     *
     */
    public function load_invOp() {
        /** @var b2b_buyLogic $logic_buy */
        $logic_buy = Logic('b2b_buy');

        $condition = array();/*
        if ($logic_buy->buyDecrypt($_GET['vat_hash'], $_SESSION['member_id']) == 'allow_vat') {
        } else {
            Tpl::output('vat_deny',true);
            $condition['inv_state'] = 1;
        }*/
        $condition['member_id'] = $_SESSION['member_id'];
        /** @var b2b_invoice $model_inv */

        $model_inv = Model('b2b_invoice');
        //如果传入ID，先删除再查询
        if (intval($_GET['del_id']) > 0) {
            $model_inv->delInv(array('inv_id'=>intval($_GET['del_id']),'member_id'=>$_SESSION['member_id']));
        }
        $list = $model_inv->getInvList($condition,10);
        if (!empty($list)) {
            foreach ($list as $key => $value) {
               if ($value['inv_state'] == 1) {
                   $list[$key]['content'] = '普通发票'.' '.$value['inv_title'].' '.$value['inv_content'];
               } else {
                   $list[$key]['content'] = '增值税发票'.' '.$value['inv_company'].' '.$value['inv_code'].' '.$value['inv_reg_addr'];
               }
            }
        }
        Tpl::output('inv_list',$list);
        Tpl::showpage('buy_invoice.load','null_layout');
    }

     /**
      * 新增发票信息
      *
      */
     public function add_invOp(){
         /** @var b2b_invoice $model_inv */
        $model_inv = Model('b2b_invoice');
        if (chksubmit()){
            //如果是增值税发票验证表单信息
            if ($_POST['invoice_type'] == 2) {
                if (empty($_POST['inv_company']) || empty($_POST['inv_code']) || empty($_POST['inv_reg_addr'])) {
                    exit(json_encode(array('state'=>false,'msg'=>Language::get('nc_common_save_fail','UTF-8'))));
                }
            }
            $data = array();
            if ($_POST['invoice_type'] == 1) {
                $data['inv_state'] = 1;
                $data['inv_title'] = $_POST['inv_title_select'] == 'person' ? '个人' : $_POST['inv_title'];
                $data['inv_content'] = $_POST['inv_content'];
            } else {
                $data['inv_state'] = 2;
                $data['inv_company'] = $_POST['inv_company'];
                $data['inv_code'] = $_POST['inv_code'];
                $data['inv_reg_addr'] = $_POST['inv_reg_addr'];
                $data['inv_reg_phone'] = $_POST['inv_reg_phone'];
                $data['inv_reg_bname'] = $_POST['inv_reg_bname'];
                $data['inv_reg_baccount'] = $_POST['inv_reg_baccount'];
                $data['inv_rec_name'] = $_POST['inv_rec_name'];
                $data['inv_rec_mobphone'] = $_POST['inv_rec_mobphone'];
                $data['inv_rec_province'] = $_POST['vregion'];
                $data['inv_goto_addr'] = $_POST['inv_goto_addr'];
            }
            $data['member_id'] = $_SESSION['member_id'];
            //转码
            $data = strtoupper(CHARSET) == 'GBK' ? Language::getGBK($data) : $data;
            $insert_id = $model_inv->addInv($data);
            if ($insert_id) {
                exit(json_encode(array('state'=>'success','id'=>$insert_id)));
            } else {
                exit(json_encode(array('state'=>'fail','msg'=>Language::get('nc_common_save_fail','UTF-8'))));
            }
        } else {
            Tpl::showpage('buy_address.add','null_layout');
        }
     }

    /**
     * AJAX验证支付密码
     */
    public function check_pd_pwdOp(){
        if (empty($_GET['password'])) exit('0');
        $buyer_info = Model('member')->getMemberInfoByID($_SESSION['member_id'],'member_paypwd');
        echo ($buyer_info['member_paypwd'] != '' && $buyer_info['member_paypwd'] === md5($_GET['password'])) ? '1' : '0';
    }

    /**
     * F码验证
     */
    public function check_fcodeOp() {
        $result = logic('buy')->checkFcode($_GET['goods_id'], $_GET['fcode']);
        echo $result['state'] ? '1' : '0';
        exit;
    }

    /**
     * 得到所购买的id和数量
     *
     */
    private function _parseItems($cart_id) {
        //存放所购商品ID和数量组成的键值对
        $buy_items = array();
        if (is_array($cart_id)) {
            foreach ($cart_id as $value) {
                if (preg_match_all('/^(\d{1,10})\|(\d{1,6})$/', $value, $match)) {
                    $buy_items[$match[1][0]] = $match[2][0];
                }
            }
        }
        return $buy_items;
    }


    private function _excelToArray($fileType,$filePath = '', $sheet = 0)
    {
        if (empty($filePath) or !file_exists($filePath)) {
            return false;
        }

        //csv类型直接str_getcsv转换
        if($fileType == 'csv'){
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
