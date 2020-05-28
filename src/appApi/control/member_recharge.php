<?php
/**
 * 我的预存款
 *
 *
 *
 *
 * @copyright  Copyright (c) 2007-2015 ShopNC Inc. (http://www.shopnc.net)
 * @license    http://www.shopnc.net
 * @link       http://www.shopnc.net
 * @since      File available since Release v1.1
 */


defined('ByShopWWI') or exit('Access Invalid!');

class member_rechargeControl extends mobileMemberControl
{
    private $pay_payment_code = array(
        'alipay' => '支付宝',
        'weixin' => '微信支付',
    );

    public function __construct()
    {
        parent::__construct();
        Language::read('member_predeposit');
    }

    /**
     * 充值列表
     */
    public function recharge_listOp()
    {
        //die($this->member_info['member_id']);
        $model_recharge = Model('predeposit');

        $recharge_list = $model_recharge->getPdRechargeList(array('pdr_member_id' => $this->member_info['member_id']), $this->page, '*');
        foreach ($recharge_list as $k => $v) {
            $recharge_list[$k]['pdr_add_time'] = date('Y-m-d H:i:s', $v['pdr_add_time']);
            $recharge_list[$k]['pdr_payment_time'] = intval($v['pdr_payment_time']) > 0 ? date('Y-m-d H:i:s', $v['pdr_payment_time']) : $v['pdr_payment_time'];
        }
        $page_count = $model_recharge->gettotalpage();
        output_data(array('recharge_list' => $recharge_list), mobile_page($page_count));
    }

    /**
     * 余额变更列表
     */
    public function log_listOp()
    {
        $model_recharge = Model('predeposit');
        !empty($_POST['page']) and $_GET['curpage'] = intval($_POST['page']);
        $log_list = $model_recharge->getPdLogList(array('lg_member_id' => $this->member_info['member_id']), $this->page, '*');
        foreach ($log_list as $k => $v) {
            $log_list[$k]['lg_add_time'] = date('Y-m-d H:i:s', $v['lg_add_time']);
        }
        $page_count = $model_recharge->gettotalpage();
        output_data(array('log_list' => $log_list), mobile_page($page_count));
    }

    /**
     * 获取充值支付编号sn
     */
    public function get_snOp()
    {
        //$_POST['amount']= 10;
        $pdr_amount = abs(floatval($_POST['amount']));
        if ($pdr_amount <= 0) {
            output_error('充值金额输入有误');
        }
        $_SESSION['member_id'] = $this->member_info['member_id'];
        $_SESSION['member_name'] = $this->member_info['member_name'];
        $data = array();
        $model_pdr = Model('predeposit');
        $data['pdr_sn'] = $model_pdr->makeSn();
        $data['pdr_member_id'] = $_SESSION['member_id'];
        $data['pdr_member_name'] = $_SESSION['member_name'];
        $data['pdr_amount'] = $pdr_amount;
        $data['pdr_add_time'] = TIMESTAMP;
        $insert = $model_pdr->addPdRecharge($data);
        if ($insert) {
            output_data(array('pdr_sn' => $data['pdr_sn']));
        } else {
            output_error("支付码获取失败");
        }
    }

    /**
     * 平台充值卡
     */
    public function card_addOp()
    {
        $params = $_POST;
        $sn = (string)$params['rc_sn'];
        if (!$sn || strlen($sn) > 50) {
            output_error('平台充值卡卡号不能为空且长度不能大于50');
        }
        $pwd = (string)$params['pwd'];
        if (!$sn || strlen($pwd) > 50) {
            output_error('平台充值卡密码不能为空且长度不能大于50');
        }

        $_SESSION['member_id'] = $this->member_info['member_id'];
        $_SESSION['member_name'] = $this->member_info['member_name'];

        try {
            Model('predeposit')->addRechargeCard($sn, $pwd, $_SESSION);
            output_data('充值成功');
        } catch (Exception $e) {
            output_error($e->getMessage());
        }
    }
}
