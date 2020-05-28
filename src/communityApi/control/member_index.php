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
        $member_infos = array();
		$member_info = $this->getMemberAndGradeInfo(true);
		$member_infos['id'] = $this->member_info['member_id'];
        $member_infos['user_name'] = $this->member_info['member_name'];
		$member_infos['true_name'] = $this->member_info['member_truename'];
		$member_infos['phone'] = $this->member_info['member_mobile'];
        $member_infos['avator'] = getMemberAvatar($this->member_info['member_avatar']);
        $member_infos['point'] = $this->member_info['member_points'];
        $member_infos['sex'] = $this->member_info['member_sex'];
        $member_infos['predepoit'] = ncPriceFormat($this->member_info['available_predeposit'], 0);
        $member_infos['available_rc_balance'] = ncPriceFormat($this->member_info['available_rc_balance'], 0);
		$member_infos['level_name'] = $this->member_info['level_name'];		
		$favorites_model = Model('favorites');
		$member_infos['favorites_store'] = $favorites_model->getStoreFavoritesCountByStoreId('',$this->member_info['member_id']);//店铺收藏数
		$member_infos['favorites_goods'] = $favorites_model->getGoodsFavoritesCountByGoodsId('',$this->member_info['member_id']);//商品收藏数
        //红包数量
        /** @var redpacketModel $model_redpacket */
        $model_redpacket = Model('redpacket');
        $model_redpacket->updateRedpacketExpire($this->member_info['member_id']);//更新红包过期状态
        $member_infos['rpt_num'] = $model_redpacket->getRedpacketCount(array('rpacket_owner_id' => $this->member_info['member_id'], 'rpacket_state' => 1));
        //优惠券数量
        /** @var voucherModel $model_voucher */
        $model_voucher = Model('voucher');
        $member_infos['voucher_num'] = $model_voucher->getCurrentAvailableVoucherCount($this->member_info['member_id']);
        //更新用户头像
        /** @var memberModel $member_model */
        $member_model = Model('member');
        if (empty($this->member_info['member_avatar']) && !empty(trim($_POST['member_avatar']))) {
            $member_model->editMember(array('member_id' => $this->member_info['member_id']), array('member_avatar' => trim($_POST['member_avatar'])));
        }

        //订单个数气泡
        /** @var orderModel $model_order */
        $model_order = Model('order');
        $member_infos['order_count_num'] = array(
            'new_count' => $model_order->getOrderCountByID('buyer',$this->member_info['member_id'],'NewCount'),
            'pay_count' => $model_order->getOrderCountByID('buyer',$this->member_info['member_id'],'PayCount'),
            'takes_count' => $model_order->getOrderCountByID('buyer',$this->member_info['member_id'],'TakesCount'),
            'send_count' => $model_order->getOrderCountByID('buyer',$this->member_info['member_id'],'SendCount'),
            'pin_count' => $model_order->getOrderCountByID('buyer',$this->member_info['member_id'],'PinCount'),
        );
        $member_infos['is_pyramid'] = empty($this->member_info['invite_shop_name']) ? 0 : 1;
        output_data(array('member_info' => $member_infos));
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
            /** @var memberModel $model_member */
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

    public function uploadOp()
    {
        $attaches = array(ATTACH_ARTICLE);
        $attach = reset($attaches);
        $type = $_POST['type']?$_POST['type']:0;
        //v($attaches);
        if(isset($attaches[$type])) $attach = $attaches[$type];
        $upload = new UploadFile();
        $upload->set('default_dir',$attach);
        $upload->set('max_size',1024*8);
        $thumb_width	= '320';
        $thumb_height	= '320';
        $upload->set('thumb_width', $thumb_width);
        $upload->set('thumb_height', $thumb_height);
        if ($_FILES['image']['name']) {
            $result = $upload->upfile('image');
            if ($result) {
                output_data(array('src'=>UPLOAD_SITE_URL . DS . $attach . DS . $upload->file_name));
            }
        }
        output_error('有错误发生');
    }
}
