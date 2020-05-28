
<?php
/**
 * 分类页
 *
 * @copyright  Copyright (c) 2007-2015 ShopNC Inc. (http://www.shopnc.net)
 * @license    http://www.shopnc.net
 * @link       http://www.shopnc.net
 * @since      File available since Release v1.1
 */
defined('ByShopWWI') or exit('Access Invalid!');

class shequ_cash_outControl extends mobileMemberControl {

    public function __construct(){
        parent::__construct();
    }

    /**
     * 分类页面
     */
    public function indexOp() {
        output_data(array(
            'cash' => $this->member_info['avaliable_commission'],
        ));
    }

    public function cash_outOp() {
        if ($this->member_info['avaliable_commission'] <= 0) {
            output_error('参数错误');
        }

        if ($this->member_info['avaliable_commission'] <= 20) {
            output_error('请超过20元后再来提现');
        }

        $open_id = $_POST['open_id'];
        if (empty($open_id)) {
            output_error('参数错误!');
        }

        //查最近的一条提现记录 失败时间必须间隔1天
        /** @var shequ_cash_outModel $shequ_cash_outModel */
        $shequ_cash_outModel = Model('shequ_cash_out');
        $cash_out_info = $shequ_cash_outModel->getInfo(array('member_id' => $this->member_info['member_id']));
        if ($cash_out_info && $cash_out_info['out_state'] == 0 && ($cash_out_info['add_time'] + 86400) > TIMESTAMP) {
            output_error('请过一天后再发起提现');
        }

        $cash_no = date('YmdHis', TIMESTAMP).mt_rand(111111, 999999);
        $insert_data = array(
            'member_id' => $this->member_info['member_id'],
            'out_state' => 0,
            'notice_info' => '提现到零钱',
            'add_time' => TIMESTAMP,
            'cash_money' => $this->member_info['avaliable_commission'],
            'cash_no' => $cash_no,
        );

        $cash_id = $shequ_cash_outModel->add($insert_data);
        if (!$cash_id) {
            output_error('提现失败');
        }
        /** @var memberModel $member_model */
        $member_model = Model('member');
        $shequ_cash_outModel->beginTransaction();
        $s1 = $shequ_cash_outModel->edit(array('cash_id' => $cash_id), array('out_state' => 20));
        if (!$s1) {
            $shequ_cash_outModel->rollback();
            output_error('提现失败');
        }

        $s2 = $member_model->editMember(array('member_id' => $this->member_info['member_id']), array('avaliable_commission' => 0));
        if (!$s2) {
            $shequ_cash_outModel->rollback();
            output_error('提现失败');
        }

        $inc_file = BASE_PATH.DS.'api'.DS.'payment'.DS.'wxpay_jsapi'.DS.'wxpay_jsapi'.'.php';
        if(!is_file($inc_file)){
            $shequ_cash_outModel->rollback();
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
            'partner_trade_no' => $cash_no,
            'open_id' => $open_id,
            'amount' => $this->member_info['avaliable_commission'] * 100,
        );
        try {
            $wxPay->giveSmallChange($params);
        } catch (Exception $ex) {
            $shequ_cash_outModel->rollback();
            output_error($ex->getMessage());
        }
        $shequ_cash_outModel->commit();
        output_data('成功');
    }

    public function get_cash_out_listOp() {
        $condition = array(
            'member_id' => $this->member_info['member_id']
        );
        /** @var shequ_cash_outModel $shequ_cash_outModel */
        $shequ_cash_outModel = Model('shequ_cash_out');
        $list = $shequ_cash_outModel->getList($condition,$this->page, 'cash_id desc');
        foreach ($list as $key=>$value) {
            $list[$key]['add_time_text'] = date('Y-m-d', $value['add_time']);
            $list[$key]['out_state_text'] = $value['out_state'] == 0 ? '提现失败' : ($value['out_state'] == 20 ? '提现成功' : '');
        }
        $page_count = $shequ_cash_outModel->gettotalpage();
        if (intval($_POST['curpage']) > $page_count) $list = array();
        output_data(array('list' => $list), mobile_page($page_count));
    }





}

