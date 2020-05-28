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

class member_inviteControl extends mobileMemberControl {

    public function __construct(){
        parent::__construct();
    }
	
    /**
     * 获取一级会员佣金列表
     */
    public function inviteoneOp() {
		 //查询佣金日志列表
		$member_model = Model('member');
		$page = new Page();
		$memberid = $this->member_info['member_id'];
		$condition = array();
		$condition['invite_one'] = $memberid ;
        $list = $member_model->getMembersList($condition,$page);
		if($list){

		//计算用户的累计返利金额
		foreach($list as $key => $val)
		{
			//获取佣金订单数量
			$invite_num = $member_model->getOrderInviteCount($memberid,$val['member_id']);
			if($invite_num>0){
				$list[$key]['invite_num']=$invite_num;
			}else{
				$list[$key]['invite_num']=0;
					}
			//获取佣金总金额
		    $invite_amount = $member_model->getOrderInviteamount($memberid,$val['member_id']);
			if($invite_amount>0){
				$list[$key]['invite_amount']=$invite_amount;
			}else{
				$list[$key]['invite_amount']=0;
					}
		}}
		
		$page_count = $member_model->gettotalpage();
        output_data(array('list' => $list),mobile_page($page_count));
    }
   /**
     * 获取二级会员佣金列表
     */
    public function invitetwoOp() {
		 //查询佣金日志列表
		$member_model = Model('member');
		$page = new Page();
		$memberid = $this->member_info['member_id'];
		$condition = array();
		$condition['invite_two'] = $memberid ;
        $list = $member_model->getMembersList($condition,$page);
		if($list){

		//计算用户的累计返利金额
		foreach($list as $key => $val)
		{
			//获取佣金订单数量
			$invite_num = $member_model->getOrderInviteCount($memberid,$val['member_id']);
			if($invite_num>0){
				$list[$key]['invite_num']=$invite_num;
			}else{
				$list[$key]['invite_num']=0;
					}
			//获取佣金总金额
		    $invite_amount = $member_model->getOrderInviteamount($memberid,$val['member_id']);
			if($invite_amount>0){
				$list[$key]['invite_amount']=$invite_amount;
			}else{
				$list[$key]['invite_amount']=0;
					}
		}}
		
		$page_count = $member_model->gettotalpage();
        output_data(array('list' => $list),mobile_page($page_count));
    }
	
  /**
     * 获取三级会员佣金列表
     */
    public function invitethirOp() {
		 //查询佣金日志列表
		$member_model = Model('member');
		$page = new Page();
		$memberid = $this->member_info['member_id'];
		$condition = array();
		$condition['invite_three'] = $memberid ;
        $list = $member_model->getMembersList($condition,$page);
		if($list){

		//计算用户的累计返利金额
		foreach($list as $key => $val)
		{
			//获取佣金订单数量
			$invite_num = $member_model->getOrderInviteCount($memberid,$val['member_id']);
			if($invite_num>0){
				$list[$key]['invite_num']=$invite_num;
			}else{
				$list[$key]['invite_num']=0;
					}
			//获取佣金总金额
		    $invite_amount = $member_model->getOrderInviteamount($memberid,$val['member_id']);
			if($invite_amount>0){
				$list[$key]['invite_amount']=$invite_amount;
			}else{
				$list[$key]['invite_amount']=0;
					}
		}}
		
		$page_count = $member_model->gettotalpage();
        output_data(array('list' => $list),mobile_page($page_count));
    }

}
