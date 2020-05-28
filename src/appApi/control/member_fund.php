<?php
/**
 * 我的余额
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

class member_fundControl extends mobileMemberControl {

    public function __construct(){
        parent::__construct();
    }
	
	
	/**
     * 充值列表
     */
    public function indexOp(){
        $condition = array();
        $condition['pdr_member_id'] = $this->member_info['member_id'];
        if (!empty($_GET['pdr_sn'])) {
            $condition['pdr_sn'] = $_GET['pdr_sn'];
        }

        $model_pd = Model('predeposit');
        $list = $model_pd->getPdRechargeList($condition,20,'*','pdr_id desc');
		foreach($list as $key=>$value){
			$list[$key]['pdr_add_time_text'] = date('Y-m-d H:i:s',$value['pdr_add_time']);
		}
		$page_count = $model_pd->gettotalpage();
        output_data(array('list' => $list),mobile_page($page_count));
    }
    /**
     * 余额记录
     */
    public function predepositlogOp() {
		$model_pd = Model('predeposit');
		$page = new Page();
        $condition = array();
        $condition['lg_member_id'] = $this->member_info['member_id'];
        $list = $model_pd->getPdLogList($condition,20,'*','lg_id desc');
		$page_count = $model_pd->gettotalpage();
        output_data(array('list' => $list),mobile_page($page_count));
    }

	/**
     * 体现记录
     */
    public function pdcashlistOp() {
		$condition = array();
        $condition['pdc_member_id'] =  $this->member_info['member_id'];
        if (preg_match('/^\d+$/',$_GET['sn_search'])) {
            $condition['pdc_sn'] = $_GET['sn_search'];
        }
        if (isset($_GET['paystate_search'])){
            $condition['pdc_payment_state'] = intval($_GET['paystate_search']);
        }
        $model_pd = Model('predeposit');
        $cash_list = $model_pd->getPdCashList($condition,30,'*','pdc_id desc');
		

		$page_count = $model_pd->gettotalpage();
        output_data(array('list' => $cash_list),mobile_page($page_count));

	}
	


	/**
     * 充值卡充值
     */
	public function rechargecard_addOp() {
		if($this->member_info['member_id']){
            if(!$this->check()){
				output_error('验证码错误！');
			}
       

			$sn = (string) $_POST['rc_sn'];
			if (!$sn || strlen($sn) > 50) {
				output_error('平台充值卡卡号不能为空且长度不能大于50');
				exit;
			}

            $pwd = (string) $_POST['pwd'];
            if (!$sn || strlen($pwd) > 50) {
                output_error('平台充值卡密码不能为空且长度不能大于50');
                exit;
            }

			try {
                /** @var predepositModel $model */
                $model = Model('predeposit');
                $model->addRechargeCard($sn, $pwd, $this->member_info);
				output_data('平台充值卡使用成功');
			} catch (Exception $e) {
				output_error($e->getMessage());
				exit;
			}
		}else{
			output_error('请登录！');
		} 
	}
		

		
	/**
     * 充值卡记录列表
     */
	public function rcblogOp() {
		$model = Model();
        $list = $model->table('rcb_log')->where(array('member_id' => $this->member_info['member_id']))->page(20)->order('id desc')->select();

		$page_count = $model->gettotalpage();
        output_data(array('log_list' => $list),mobile_page($page_count));
	}

	/**
     * 我的积分 我的余额
     */
    public function my_assetOp() {
		$point = $this->member_info['member_points'];
		output_data(array('point' => $point));
	}
	
	protected function getMemberAndGradeInfo($is_return = false){
        $member_info = array();
        //会员详情及会员级别处理
        if($this->member_info['member_id']) {
            $model_member = Model('member');
            $member_info = $model_member->getMemberInfoByID($this->member_info['member_id']);
            if ($member_info){
                $member_gradeinfo = $model_member->getOneMemberGrade(intval($member_info['member_exppoints']));
                $member_info = array_merge($member_info,$member_gradeinfo);
                $member_info['security_level'] = $model_member->getMemberSecurityLevel($member_info);
            }
        }
        if ($is_return == true){//返回会员信息
            return $member_info;
        } else {//输出会员信息
            Tpl::output('member_info',$member_info);
        }
    }
	
	/**
     * AJAX验证
     *
     */
	protected function check(){
        if (checkSeccode($_POST['nchash'],$_POST['captcha'])){
            return true;
        }else{
            return false;
        }
    }
}
