<?php
/**
 * 我的商城
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

class member_indexControl extends mobileMemberControl {

    public function __construct(){
        parent::__construct();
    }

    /**
     * 我的商城
     */
    public function indexOp() {
        $member_info = array();
		$member_info = $this->getMemberAndGradeInfo(true);
        $member_info['user_name'] = $this->member_info['member_name'];
		$member_info['id'] = $this->member_info['member_id'];
        $member_info['avator'] = getMemberAvatarForID($this->member_info['member_id']);
        $member_info['point'] = $this->member_info['member_points'];
        $member_info['predepoit'] = $this->member_info['available_predeposit'];
        $member_info['available_rc_balance'] = $this->member_info['available_rc_balance'];
		$member_info['level_name'] = $this->member_info['level_name'];		
		$favorites_model = Model('favorites');
		$member_info['favorites_store'] = $favorites_model->getStoreFavoritesCountByStoreId('',$this->member_info['member_id']);//店铺收藏数
		$member_info['favorites_goods'] = $favorites_model->getGoodsFavoritesCountByGoodsId('',$this->member_info['member_id']);//商品收藏数
        output_data(array('member_info' => $member_info));
    }
	
	/**
     * 我的积分
     */
    public function my_assetOp() {
		$member_info = $this->getMemberAndGradeInfo(true);
		$point = $this->member_info['member_points'];
		$predepoit = $this->member_info['available_predeposit'];
		$balance = $this->member_info['available_rc_balance'];
		$voucher =  Model('voucher')->getCurrentAvailableVoucherCount($this->member_info['member_id']); //取得当前有效代金券数量
		$redpacket =  Model('redpacket')->getCurrentAvailableRedpacketCount($this->member_info['member_id']); //取得当前有效红包数量

		if($_GET["fields"]=='predepoit'){
			output_data(array('predepoit' => $predepoit));
		}elseif($_GET["fields"]=='available_rc_balance'){
			output_data(array('available_rc_balance' => $balance));
		}else{
			output_data(array('point' => $point,'predepoit'=>$predepoit,'available_rc_balance'=>$balance,'redpacket'=>$redpacket,'voucher'=>$voucher));
		}
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
     * 现联合作跳转
     */
    public function xianlianOp() {
        $time = time();
        $sign = md5($time . 'a6993c320f27924a0934292b62a28661');
        $redirect_url = "http://act.hq-xl.com/84e6899efbf93367/bonus/cib-wuhanhangou?timestamp={$time}&sign={$sign}";

        output_data(array('url' => $redirect_url));
        die;
    }
}
