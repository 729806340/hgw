<?php
/**
 * 我的小店
 *
 */
defined('ByShopWWI') or exit('Access Invalid!');

class pyramid_sellingControl extends mobileMemberControl
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 我的小店
     */
    public function my_shopOp()
    {
        $member_id = $this->member_info['member_id'];
        /** @var memberModel $member_model */
        $member_model = Model('member');
        $this->member_info = $member_model->getMemberInfo(array('member_id' => $member_id));
        $is_pyramid = 0;
        $invite_goods_list = array();
        if (!empty($this->member_info['invite_shop_name'])) {
            $is_pyramid = 1;
        }
        /** @var retail_goodsModel $retail_goodsModel */
        $retail_goodsModel = Model('retail_goods');
        $retail_goods_list = $retail_goodsModel->getRetailGoodsList(array());
        $retail_goods_list = array_under_reset($retail_goods_list, 'retail_goods_id');
        /** @var goodsModel $goodsModel */
        $goodsModel = Model('goods');
        $condition = array(
            'goods_id' => array('in', array_keys($retail_goods_list))
        );
        $page_num = $this->page;
        $goods_list = $goodsModel->getGoodsOnlineList($condition, $field = '*', $page_num, $order = 'goods_id desc');
        foreach ($goods_list as $goods) {
            $retail_goods_info = $retail_goods_list[$goods['goods_id']];
            $invite_goods_list[] = array(
                'invite_one' => $member_id,
                'goods_id' => $goods['goods_id'],
                'goods_image' => cthumb($goods['goods_image'], 360, $goods['store_id']),
                'goods_name' => $goods['goods_name'],
                'goods_price' => $goods['goods_price'],
                'retail_money' => (empty($retail_goods_info) && $is_pyramid == 1) ? 0 : $retail_goods_info['retail_one_return']
            );
        }
        $page_count = $goodsModel->gettotalpage();
        /** @var PyramidService $PyramidService */
        $PyramidService = Service("Pyramid");
        $pyramid_amount = $PyramidService->getMemberPyramidAmount($member_id);
        output_data(array(
            'is_pyramid' => $is_pyramid,
            'invite_amount' => $pyramid_amount['available_amount'] + $pyramid_amount['freeze_amount'],
            'invite_available_amount' => $pyramid_amount['available_amount'],
            'invite_goods_list' => $invite_goods_list,
        ), mobile_page($page_count));
    }


    /**
     * 我的小店
     */
    public function my_shop_newOp()
    {
        $member_id = $this->member_info['member_id'];
        /** @var memberModel $member_model */
        $member_model = Model('member');
        $this->member_info = $member_model->getMemberInfo(array('member_id' => $member_id));
        $is_pyramid = 0;
        if (!empty($this->member_info['invite_shop_name'])) {
            $is_pyramid = 1;
        }
        /** @var retail_goodsModel $retail_goodsModel */
        $retail_goodsModel = Model('retail_goods');
        $retail_goods_list = $retail_goodsModel->getRetailGoodsList(array());
        $retail_goods_list = array_under_reset($retail_goods_list, 'retail_goods_id');
        /** @var goodsModel $goodsModel */
        $goodsModel = Model('goods');
        $condition = array(
            'goods_id' => array('in', array_keys($retail_goods_list))
        );
        $goods_list = $goodsModel->getGoodsOnlineList($condition, $field = 'gc_id_1');
        $goods_class_ids = array_column($goods_list, 'gc_id_1');
        $goods_class_list = array();
        /** @var goods_classModel $goods_class_model */
        $goods_class_model = Model('goods_class');
        if (!empty($goods_class_ids)) {
            $goods_class_array = $goods_class_model->getGoodsClassList(array('gc_id' => array('in', $goods_class_ids)));
            foreach ($goods_class_array as $goods_class) {
                $goods_class_list[] = array(
                    'gc_id' => $goods_class['gc_id'],
                    'gc_name' => $goods_class['gc_name']
                );
            }
        }

        array_unshift($goods_class_list, array('gc_id' => 0, 'gc_name' => '今日推荐'));
        /** @var PyramidService $PyramidService */
        $PyramidService = Service("Pyramid");
        $pyramid_amount = $PyramidService->getMemberPyramidAmount($member_id);
        output_data(array(
            'is_pyramid' => $is_pyramid,
            'invite_amount' => ncPriceFormat($pyramid_amount['available_amount'] + $pyramid_amount['freeze_amount']),
            'invite_available_amount' => $pyramid_amount['available_amount'],
            'goods_class_list' => $goods_class_list,
        ));
    }

    public function get_invite_goods_listOp()
    {
        $member_id = $this->member_info['member_id'];
        $gc_id = intval($_POST['gc_id']);
        $is_pyramid = 0;
        $invite_goods_list = array();
        if (!empty($this->member_info['invite_shop_name'])) {
            $is_pyramid = 1;
        }
        /** @var retail_goodsModel $retail_goodsModel */
        $retail_goodsModel = Model('retail_goods');
        if ($gc_id > 0) {
            $retail_goods_list = $retail_goodsModel->getRetailGoodsList(array());
        } else {
            $retail_goods_list = $retail_goodsModel->getRetailGoodsList(array('retail_show_time' => array('gt', TIMESTAMP)));
        }

        $retail_goods_list = array_under_reset($retail_goods_list, 'retail_goods_id');
        /** @var goodsModel $goodsModel */
        $goodsModel = Model('goods');
        $condition = array(
            'goods_id' => array('in', array_keys($retail_goods_list))
        );
        if ($gc_id > 0) {
            $condition['gc_id_1'] = $gc_id;
        }
        $page_num = $this->page;
        $goods_list = $goodsModel->getGoodsOnlineList($condition, $field = '*', $page_num, $order = 'goods_id desc');
        foreach ($goods_list as $goods) {
            $retail_goods_info = $retail_goods_list[$goods['goods_id']];
            $invite_goods_list[] = array(
                'invite_one' => $member_id,
                'goods_id' => $goods['goods_id'],
                'goods_image' => cthumb($goods['goods_image'], 360, $goods['store_id']),
                'goods_name' => $goods['goods_name'],
                'goods_price' => $goods['goods_price'],
                'retail_money' => (empty($retail_goods_info) && $is_pyramid == 1) ? 0 : $retail_goods_info['retail_one_return']
            );
        }
        $page_count = $goodsModel->gettotalpage();
        output_data(array(
            'invite_goods_list' => $invite_goods_list,
        ), mobile_page($page_count));

    }

    /**
     * 成为分销商
     */
    public function be_winnerOp()
    {
        $member_id = $this->member_info['member_id'];
        $shop_name = trim($_POST['shop_name']);
        /** @var memberModel $member_model */
        $member_model = Model('member');
        $this->member_info = $member_model->getMemberInfo(array('member_id' => $member_id));
        if (empty($shop_name)) {
            output_error('店名不能为空');
        }

        if (mb_strlen($shop_name) > 10) {
            output_error('店名不能太长');
        }
        if (!empty($this->member_info['invite_shop_name'])) {
            output_error('非法操作');
        }
        $member_model->editMember(array('member_id' => $member_id),  array('invite_shop_name' => $shop_name));
        /** @var PyramidService $PyramidService */
        $PyramidService = Service("Pyramid");
        $PyramidService->giveSweet($member_id);
        output_data('成功');
    }

    /**
     * 我的分销订单
     */
    public function sell_ordersOp()
    {
        /** @var pyramid_order_logModel $pyramid_order_logModel */
        $pyramid_order_logModel = Model('pyramid_order_log');
        /** @var orderModel $orderModel */
        $orderModel = Model('order');
        $order_list = array();
        $member_id = $this->member_info['member_id'];
        $state_type = $_POST['state_type']; //state_new || state_finish || state_refund
        if (!in_array($state_type, array('state_new', 'state_finish', 'state_refund'))) {
            $state_type = 'state_new';
        }
        $condition = array(
            'lg_member_id' => $member_id,
            'lg_status' => array('gt', 10),
        );
        if ($state_type == 'state_new' || $state_type == 'state_refund') {
            $condition['real_add_time'] = 0;
        } elseif ($state_type == 'state_finish') {
            $condition['real_add_time'] = array('gt', 0);
        }

        $pyramid_order_list = $pyramid_order_logModel->getPyramidOrderLogList($condition);
        $pyramid_order_list = array_under_reset($pyramid_order_list, 'order_id');
        $order_condition = array(
            'order_state' => array('egt', ORDER_STATE_NEW),
            'order_id' => array('in', array_keys($pyramid_order_list))
        );
        if ($state_type == 'state_refund') {
            $order_condition['lock_state'] = array('gt', 0);
        } else {
            $order_condition['lock_state'] = 0;
        }
        $order_data = $orderModel->getOrderList($order_condition, $this->page);
        foreach ($order_data as $order) {
            $pyramid_order = $pyramid_order_list[$order['order_id']];
            $return_money = $pyramid_order['return_money'];
            $return_money = $state_type == 'state_finish' ? $pyramid_order['real_return_money'] : $return_money;
            $order_list[] = array(
                'order_sn' => $order['order_sn'],
                'invite_member_name' => $pyramid_order['invite_member_name'],
                'add_time' => date('Y-m-d H:i', $pyramid_order['add_time']),
                'return_money' => $return_money
            );
        }
        $page_count = $orderModel->gettotalpage();
        /** @var PyramidService $PyramidService */
        $PyramidService = Service("Pyramid");
        $pyramid_amount = $PyramidService->getMemberPyramidAmount($member_id);
        output_data(array(
            'able_invite_amount' => $pyramid_amount['available_amount'],
            'total_invite_amount' => $pyramid_amount['all_return_money'],
            'order_list' => $order_list,
        ), mobile_page($page_count));
    }

    /**
     * 我的提现页面
     */
    public function crash_outOp()
    {
        /** @var PyramidService $PyramidService */
        $PyramidService = Service("Pyramid");
        $pyramid_amount = $PyramidService->getMemberPyramidAmount($this->member_info['member_id']);
        output_data(array(
            'able_invite_amount' => $pyramid_amount['available_amount']
        ));
    }

    /**
     * 提现申请
     */
    public function crash_out_applyOp()
    {
        $apply_money = $_POST['apply_money'];
        if ($apply_money <= 0) {
            output_error('参数错误');
        }
        /** @var PyramidService $PyramidService */
        $PyramidService = Service("Pyramid");
        $pyramid_amount = $PyramidService->getMemberPyramidAmount($this->member_info['member_id']);
        if ($apply_money > $pyramid_amount['available_amount']) {
            output_error('超出了可提现金额');
        }
        /** @var PyramidService $PyramidService */
        $PyramidService = Service("Pyramid");
        $result = $PyramidService->out_cash_start($this->member_info['member_id'], $apply_money);
        if (!$result['state']) {
            output_error($result['msg']);
        }
        output_data('成功');

    }

    /**
     * 我的提现记录
     */
    public function crash_out_listOp()
    {
        /** @var pyramid_out_crashModel $pyramid_out_crashModel */
        $pyramid_out_crashModel = Model('pyramid_out_crash');
        $log_list = array();
        $condition = array(
            'own_member_id' => $this->member_info['member_id']
        );
        $crash_out_data = $pyramid_out_crashModel->getPyramidOutCrashList($condition, $this->page);
        foreach ($crash_out_data as $crash_out) {
            $state_msg = "";
            if ($crash_out['cash_check_state'] == 1) {
                $state_msg = "提现中";
            } elseif ($crash_out['cash_check_state'] == 2) {
                $state_msg = "提现失败";
            } else {
                if ($crash_out['pay_state'] == 3) {
                    $state_msg = "提现成功";
                } elseif ($crash_out['pay_state'] == 4) {
                    $state_msg = "领取中";
                } else {
                    $state_msg = "领取红包";
                }
            }
            //$state_msg = $crash_out['cash_check_state'] == 1 ? "提现中" : ($crash_out['cash_check_state'] == 2 ? "提现失败" : "提现成功");
            $log_list[] = array(
                'crash_out_id' => $crash_out['crash_out_id'],
                'msg' => "提现到微信红包",
                'add_time' => date('Y-m-d H:i', $crash_out['add_time']),
                'crash_out_money' => $crash_out['out_crash_money'],
                'state_msg' => $state_msg,
                'cash_check_state' => $crash_out['cash_check_state'],
                'pay_state' => $crash_out['pay_state'], //为1，2时可以发起领取 4可以问客服 3领取成功
            );
        }
        $page_count = $pyramid_out_crashModel->gettotalpage();
        output_data(array(
            'log_list' => $log_list,
        ), mobile_page($page_count));
    }

    //领取红包
    public function get_crashOp() {
        $out_crash_id = intval($_POST['out_crash_id']);
        /** @var pyramid_out_crashModel $pyramid_out_crashModel */
        $pyramid_out_crashModel = Model('pyramid_out_crash');
        $out_crash_data = $pyramid_out_crashModel->getPyramidOutCrashInfo(array('out_crash_id' => $out_crash_id));
        if (empty($out_crash_data)) {
            output_error('非法操作');
        }

        //检查状态
        if ($out_crash_data['cash_check_state'] != 3 || !in_array($out_crash_data['pay_state'], array(1,2))) {
            output_error('非法操作');
        }

        $open_id = $this->member_info['openid'];
        if (empty($open_id)) {
            output_error('请联系客服');
        }
        $inc_file = BASE_PATH.DS.'api'.DS.'payment'.DS.'wxpay_jsapi'.DS.'wxpay_jsapi'.'.php';
        if(!is_file($inc_file)){
            output_error('支付接口不存在');
        }
        require($inc_file);
        $wxPay = new wxpay_jsapi();
        $small_config = C('small_wx');
        $wxPay->setConfigs(array(
            'apiKey' => $small_config['key']
        ));
        $params = array(
            'mch_id' => $small_config['mch_id'],
            'app_id' => $small_config['app_id'],
            'mch_billno' => $out_crash_data['out_crash_number'],
            'open_id' => $open_id,
            'total_amount' => $out_crash_data['out_crash_money'],
        );
        try {
            $package = $wxPay->sendMiniProgramHb($params);
        } catch (Exception $ex) {
            output_error($ex->getMessage());
        }
        $result = $pyramid_out_crashModel->editPyramidOutCrash(array('pay_state' => 4), array(array('out_crash_id' => $out_crash_id)));
        if (!$result) {
            output_error('失败');
        }
        output_data($package);
    }


    public function test_get_crashOP() {
        $open_id = $_POST['open_id'];
        $mch_billno = date('YmdHis', TIMESTAMP).mt_rand(111111, 999999);
        $inc_file = BASE_PATH.DS.'api'.DS.'payment'.DS.'wxpay_jsapi'.DS.'wxpay_jsapi'.'.php';
        if(!is_file($inc_file)){
            output_error('支付接口不存在');
        }
        require($inc_file);
        $wxPay = new wxpay_jsapi();
        $small_config = C('small_wx');
        $wxPay->setConfigs(array(
            'apiKey' => $small_config['key']
        ));
        $params = array(
            'mch_id' => $small_config['mch_id'],
            'app_id' => $small_config['app_id'],
            'mch_billno' => $mch_billno,
            'open_id' => $open_id,
            'total_amount' => 0.01,
        );
        try {
            $package = $wxPay->sendMiniProgramHb($params);
        } catch (Exception $ex) {
            output_error($ex->getMessage());
        }
        output_data($package);
    }

    public function test_small_changeOp() {
        $open_id = $_POST['open_id'];
        $mch_billno = date('YmdHis', TIMESTAMP).mt_rand(111111, 999999);
        $inc_file = BASE_PATH.DS.'api'.DS.'payment'.DS.'wxpay_jsapi'.DS.'wxpay_jsapi'.'.php';
        if(!is_file($inc_file)){
            output_error('支付接口不存在');
        }
        require($inc_file);
        $wxPay = new wxpay_jsapi();
        $small_config = C('small_wx');
        $wxPay->setConfigs(array(
            'apiKey' => $small_config['key']
        ));
        $params = array(
            'mch_id' => $small_config['mch_id'],
            'app_id' => $small_config['app_id'],
            'partner_trade_no' => $mch_billno,
            'open_id' => $open_id,
            'amount' => 100,
        );
        $package = array();
        try {
            $package = $wxPay->giveSmallChange($params);
        } catch (Exception $ex) {
            output_error($ex->getMessage());
        }
        output_data($package);
    }



    //更新领取红包状态
    public function edit_get_crash_stateOp() {
        $out_crash_id = intval($_POST['out_crash_id']);
        $pay_state = intval($_POST['pay_state']);
        if (!in_array($pay_state, array(2, 3))) {
            //必须记录日志
            output_error('非法操作');
        }
        /** @var pyramid_out_crashModel $pyramid_out_crashModel */
        $pyramid_out_crashModel = Model('pyramid_out_crash');
        $out_crash_data = $pyramid_out_crashModel->getPyramidOutCrashInfo(array('out_crash_id' => $out_crash_id));
        if (empty($out_crash_data)) {
            //必须记录日志
            output_error('非法操作');
        }
        //检查状态
        if ($out_crash_data['cash_check_state'] != 3 || $out_crash_data['pay_state'] != 4) {
            //必须记录日志
            output_error('非法操作');
        }
        $result = $pyramid_out_crashModel->editPyramidOutCrash(array('pay_state' => $pay_state), array(array('out_crash_id' => $out_crash_id)));
        if (!$result) {
            //必须记录日志
            output_error('更新失败');
        }
        output_data(array());
    }


}
