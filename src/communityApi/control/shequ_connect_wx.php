<?php
/**
 * 微信登录
 *
 *
 * * @汉购网 (c) 2015-2018 ShopWWI Inc. (http://www.hangowa.com)
 * @license    http://www.shopwwi.com
 * @link       交流群号：
 * @since      汉购网提供技术支持 授权请购买shopnc授权
 */

defined('ByShopWWI') or exit('Access Invalid!');

class shequ_connect_wxControl extends mobileHomeControl{
    public function __construct(){
        parent::__construct();
    }

    /**
     * 微信小程序码登录
     */
    public function indexOp(){
        $token = md5(mt_rand(0,999999) . strval(TIMESTAMP) . strval(rand(0,999999)));
        /** @var wx_small_appLogic $wxSmallApp */
        $wxSmallApp = Logic('wx_small_app');
        $insert_data = array(
            'token' => $token,
            'add_time' => TIMESTAMP
        );
        $model = Model();
        $model->table('shequ_wxlogin_token')->insert($insert_data);
        output_data_new(array(
            'wx_login_code' => $token,
            'wx_login_image' => str_replace(array("\r","\n"),'', $wxSmallApp->getQrBase64('pages/pc_login/pc_login',$token)),
        ));
    }

    public function wx_loginOp() {
        $wx_code = $_POST['wx_code'];
        $condition = array(
            'token' => $wx_code,
            'add_time' => array('gt', TIMESTAMP-86400)
        );
        $model = Model();
        $result = $model->table('shequ_wxlogin_token')->where($condition)->find();
        if (empty($result) || $result['member_id'] <= 0) {
            output_error('失败');
        }
        $member_id = $result['member_id'];
        $model_member = Model('member');
        /** @var memberModel $model_member */
        $member_info = $model_member->getMemberInfoByID($member_id);
        if (empty($member_info)) {
            output_error('失败');
        }
        $member_info = $model_member->getMemberInfo(array('member_id' => $member_info['member_id']));
        if (!$member_info['member_state']) {
            output_error('账号被停用');
        }
        $tuan_acc = MD5($member_info['member_id'] . "654123");
        setNcCookie('member_id', $member_info['member_id'], 365 * 24 * 60 * 60);
        setNcCookie('member_name', $member_info['member_name'], 365 * 24 * 60 * 60);
        setNcCookie('member_turename', $member_info['member_turename'], 365 * 24 * 60 * 60);
        setNcCookie('tuan_access', $tuan_acc);
        $_SESSION['tuan_access'] = $tuan_acc;
        $_SESSION['member_id'] = $member_info['member_id'];
        $data['member_id'] = $member_info['member_id'];
        $data['access_token'] = $tuan_acc;
        $data['userName'] = $member_info['wx_nick_name'] ? $member_info['wx_nick_name'] : $member_info['member_name'];
        $data['avatar'] = $member_info['wx_user_avatar'] ? $member_info['wx_user_avatar'] : $member_info['member_avatar'];
        $model->table('shequ_wxlogin_token')->where(array('token_id' => $result['token_id']))->update(array('token' => 'abc'));
        output_data('登录成功', $data);

    }
}