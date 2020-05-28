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
        /** @var memberModel $model_member */
        $model_member = Model('member');
        $member_infos = array();
		//$member_info = $this->getMemberAndGradeInfo(true);
        $this->member_info = $model_member->getMemberInfo(array('member_id' => $this->member_info['member_id']), '*', true);
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
        //$model_redpacket = Model('redpacket');
        //$model_redpacket->updateRedpacketExpire($this->member_info['member_id']);//更新红包过期状态
        //$member_infos['rpt_num'] = $model_redpacket->getRedpacketCount(array('rpacket_owner_id' => $this->member_info['member_id'], 'rpacket_state' => 1));
        //优惠券数量
        /** @var voucherModel $model_voucher */
        //$model_voucher = Model('voucher');
        //$member_infos['voucher_num'] = $model_voucher->getCurrentAvailableVoucherCount($this->member_info['member_id']);
        //更新用户头像
        if (empty($this->member_info['member_avatar']) && !empty(trim($_POST['member_avatar']))) {
            $model_member->editMember(array('member_id' => $this->member_info['member_id']), array('member_avatar' => trim($_POST['member_avatar'])));
        }

        /** @var shequ_tuanzhangModel $shequ_tuanzhang_model */
        $shequ_tuanzhang_model = Model('shequ_tuanzhang');
        $wx_nick_name = $_POST['wx_nick_name'];
        $wx_user_avatar = $_POST['wx_user_avatar'];
        if ($wx_nick_name && $wx_user_avatar) {
            $model_member->editMember(array('member_id' => $this->member_info['member_id']), array('member_avatar' => $wx_user_avatar, 'wx_nick_name' => $wx_nick_name, 'wx_user_avatar' => $wx_user_avatar));
            //如果是团长 更新团长头像
            // nick_name avatar
            if ($this->member_info['tuanzhang_id'] > 0) {
                $shequ_tuanzhang_model->edit(array('member_id' => $this->member_info['member_id']), array('nixk_name' => $wx_nick_name, 'avatar' => $wx_user_avatar));
            }
        }

        //订单个数气泡
        /** @var orderModel $model_order */
        $model_order = Model('order');
        $member_infos['order_count_num'] = array(
            'new_count' => $model_order->getOrderCountByID('buyer',$this->member_info['member_id'],'NewCount'),
            //'pay_count' => $model_order->getOrderCountByID('buyer',$this->member_info['member_id'],'PayCount'),
            //'takes_count' => $model_order->getOrderCountByID('buyer',$this->member_info['member_id'],'TakesCount'),
            //'send_count' => $model_order->getOrderCountByID('buyer',$this->member_info['member_id'],'SendCount'),
            'wait_count' => $model_order->getOrderCountByID('buyer',$this->member_info['member_id'],'WaitCount'),
            'completed_count' => $model_order->getOrderCountByID('buyer',$this->member_info['member_id'],'CompletedCount'),
            'cancel_count' => $model_order->getOrderCount(array('buyer_id' => $this->member_info['member_id'], 'order_state' => ORDER_STATE_CANCEL)),
            'after_count' => 0
            //'pin_count' => $model_order->getOrderCountByID('buyer',$this->member_info['member_id'],'PinCount'),
        );
        //$member_infos['is_pyramid'] = empty($this->member_info['invite_shop_name']) ? 0 : 1;
        $member_infos['fetch_qr'] = $this->get_fetch_qr();
        //团长申请相关
        $shequ_tuanzhang_info = $shequ_tuanzhang_model->getOne(array('member_id' => $this->member_info['member_id']));
        $member_infos['is_shequ_tuanzhang'] = empty($shequ_tuanzhang_info) ?  0 : ($shequ_tuanzhang_info['state'] == 1 ? 2 : 1);
        $member_infos['tuanzhang_id'] = $this->member_info['tuanzhang_id'];
        $member_infos['default_shequ_tuanzhang_id'] = $this->member_info['default_shequ_tuanzhang_id'];
        output_data(array('member_info' => $member_infos, 'show_charge' => 2));
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

    public function wx_pc_loginOp() {
        $wx_code = $_POST['wx_code'];
        $wx_nick_name = $_POST['wx_nick_name'];
        $wx_user_avatar = $_POST['wx_user_avatar'];
        $condition = array(
            'token' => $wx_code,
            'add_time' => array('gt', TIMESTAMP-86400)
        );

        if (empty($wx_nick_name) || empty($wx_user_avatar)) {
            output_error('失败!');
        }

        $model = Model();
        $res = $model->table('shequ_wxlogin_token')->where($condition)->find();
        if (empty($res)) {
            output_error('失败');
        }
        $result = $model->table('shequ_wxlogin_token')->where(array('token_id' => $res['token_id']))->update(array('member_id' => $this->member_info['member_id']));
        if (!$result) {
            output_error('失败');
        }
        if ($wx_nick_name && $wx_user_avatar) {
            /** @var memberModel $model_member */
            $model_member = Model('member');
            $model_member->editMember(array('member_id' => $this->member_info['member_id']), array('wx_nick_name' => $wx_nick_name, 'wx_user_avatar' => $wx_user_avatar));
            /** @var shequ_tuanzhangModel $tuanzhang_model */
            $tuanzhang_model = Model('shequ_tuanzhang');
            $tuanzhang_info = $tuanzhang_model->getOne(array('member_id' => $this->member_info['member_id']));
            if (!empty($tuanzhang_info)) {
                $tuanzhang_model->edit(array('member_id' => $this->member_info['member_id']), array('avatar' => $wx_user_avatar));
            }
        }
        output_data_new('成功');

    }


    public function get_fetch_qr() {
        if ($this->member_info['tuanzhang_id'] > 0) {
            $base64String = '';
        } else {
            /** @var wx_small_appLogic  $wx_samll_app */
            $wx_samll_app = Logic('wx_small_app');
            try {
                $base64String = $wx_samll_app->getQrHttp("pages/picking_list/picking_list", $this->member_info['member_id'], '');
            } catch (Exception $e) {
                //默认
                $base64String = '';
            }
        }
        return $base64String;
    }

    public function set_default_tuanzhangOp() {
        $tz_id = $_POST['tz_id'];
        if (!$tz_id) {
            output_error('参数错误');
        }
        if ($this->member_info['tuanzhang_id']) {
            output_error('异常');
        }
        if ($this->member_info['default_shequ_tuanzhang_id'] == $tz_id) {
            output_error('异常1');
        }
        /** @var memberModel $member_model */
        $member_model = Model('member');
        $s1 = $member_model->editMember(array('member_id' => $this->member_info['member_id']), array('default_shequ_tuanzhang_id' => $tz_id));
        if (!$s1) {
            output_error('失败啊');
        }

        /** @var shequ_tuanzhangModel $tuanzhang_model */
        $tuanzhang_model = Model('shequ_tuanzhang');
        $tuanzhang_info = $tuanzhang_model->getOne(array(
            'id' => $tz_id
        ));
        /** @var shequ_addressModel $shequ_address_model */
        $shequ_address_model = Model('shequ_address');
        $tuanzhang_address = $shequ_address_model->getOne(array(
            'tuanzhang_id' => $tz_id,
        ));
        $return = array(
            'tuanzhang_info' => array(
                'id' => $tuanzhang_info['id'],
                'avatar' => $tuanzhang_info['avatar'],
                'name' => $tuanzhang_info['name'],
            ),
            'tuanzhang_address' => array(
                'address' => $tuanzhang_address['city_name'] . $tuanzhang_address['area'] . $tuanzhang_address['street'] . $tuanzhang_address['community'] . $tuanzhang_address['address'],
                'building' => $tuanzhang_address['building'],
            ),
            'is_default' => 1,
        );
        output_data_new($return);
    }

    public function get_tuanzhang_infoOp()
    {
        $tuanzhang_id = $this->member_info['tuanzhang_id'];
        if ($tuanzhang_id <= 0) {
            output_error('异常');
        }
        /** @var shequ_tuanzhangModel $thuanzhang_model */
        $thuanzhang_model = Model('shequ_tuanzhang');
        $thuanzhang_info = $thuanzhang_model->getOne(array('id' => $tuanzhang_id));
        if (empty($thuanzhang_info)) {
            output_error('异常');
        }
        output_data_new(
            array(
                'register_time' => date('Y-m-d',$thuanzhang_info['add_time'])
            )
        );
    }
}
