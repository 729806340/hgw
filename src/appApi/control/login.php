<?php
/**
 * 前台登录 退出操作
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

class loginControl extends mobileHomeControl
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 登录
     */
    public function indexOp()
    {
        //$_POST = array('username'=>'13517237062' ,'password'=>'ljqfln528208' ,'client'=>'ios');
        if (empty($_POST['username']) || empty($_POST['password']) || !in_array($_POST['client'], $this->client_type_array)) {
            output_error('登录失败');
        }

        /** @var memberModel $model_member */
        $model_member = Model('member');
        $member_info = $model_member->getMemberInfo(array('member_name' => $_POST['username']));
        $passwordHash = passwordHash($_POST['password'], $member_info['password_salt'], $member_info['password_account'], $member_info['member_time']);

        if ($passwordHash != $member_info['member_passwd'] && preg_match('/^0?(13|15|17|18|14)[0-9]{9}$/i', $_POST['username'])) {
            $member_info = $model_member->getMemberInfo(array('member_mobile' => $_POST['username']));
            $passwordHash = passwordHash($_POST['password'], $member_info['password_salt'], $member_info['password_account'], $member_info['member_time']);
        }
        if ($passwordHash != $member_info['member_passwd'] && (strpos($_POST['username'], '@') > 0)) {
            $member_info = $model_member->getMemberInfo(array('member_email' => $_POST['username']));
            $passwordHash = passwordHash($_POST['password'], $member_info['password_salt'], $member_info['password_account'], $member_info['member_time']);

        }

        /** 获取到用户数据为空*/
        if (C('OLD_STATUS') == true) {
            $ec_member_info = $model_member->getOldMemberInfo(array('login_account' => $_POST['username']));
            if ($ec_member_info) { // 若旧平台存在对应用户
                $passwordHash = passwordHash($_POST['password'], $ec_member_info['password_salt'], $ec_member_info['password_account'], $ec_member_info['member_time']);
                if ($passwordHash == $ec_member_info['member_passwd']) {
                    $model_member->updateMemberByOld($ec_member_info);
                }
                $member_info = $model_member->getMemberInfo(array('member_id' => $ec_member_info['member_id']));
            } else { // 若旧平台不存在对应用户，则返回失败
                output_error(empty($member_info) ? '用户名不存在' : '账户同步错误；请联系管理员！');
            }
        }

        if (!empty($member_info) && $passwordHash == $member_info['member_passwd']) {
            $token = $this->_get_token($member_info['member_id'], $member_info['member_name'], $_POST['client']);
            if ($token) {
                //添加会员积分
                $model_member->addPoint($member_info);
                //添加会员经验值
                $model_member->addExppoint($member_info);
                //修改登录时间
                $update_info    = array(
                    'member_login_num'=> ($member_info['member_login_num']+1),
                    'member_login_time'=> TIMESTAMP,
                    'member_old_login_time'=> $member_info['member_login_time'],
                    'member_login_ip'=> getIp(),
                    'member_old_login_ip'=> $member_info['member_login_ip']
                );
                $model_member->editMember(array('member_id'=>$member_info['member_id']),$update_info);
                output_data(array('username' => $member_info['member_name'], 'userid' => $member_info['member_id'], 'key' => $token));
            } else {
                output_error('登录失败');
            }
        } else {
            output_error(empty($member_info) ? '用户名不存在' : '密码错误');
        }
    }

    /**
     * 登录生成token
     */
    private function _get_token($member_id, $member_name, $client)
    {
        $model_mb_user_token = Model('mb_user_token');

        //重新登录后以前的令牌失效
        //暂时停用
        //$condition = array();
        //$condition['member_id'] = $member_id;
        //$condition['client_type'] = $client;
        //$model_mb_user_token->delMbUserToken($condition);

        //生成新的token
        $mb_user_token_info = array();
        $token = md5($member_name . strval(TIMESTAMP) . strval(rand(0, 999999)));
        $mb_user_token_info['member_id'] = $member_id;
        $mb_user_token_info['member_name'] = $member_name;
        $mb_user_token_info['token'] = $token;
        $mb_user_token_info['login_time'] = TIMESTAMP;
        $mb_user_token_info['client_type'] = $client;

        $result = $model_mb_user_token->addMbUserToken($mb_user_token_info);

        if ($result) {
            return $token;
        } else {
            return null;
        }

    }

    /**
     * 注册
     */
    public function registerOp()
    {
        /** @var memberModel $model_member */

        $model_member = Model('member');

        $register_info = array();
        $register_info['username'] = $_POST['username'];
        $register_info['password'] = $_POST['password'];
        $register_info['password_confirm'] = $_POST['password_confirm'];
        $register_info['email'] = $_POST['email'];
        $member_info = $model_member->register($register_info);
        if (!isset($member_info['error'])) {
            $token = $this->_get_token($member_info['member_id'], $member_info['member_name'], $_POST['client']);
            if ($token) {
                output_data(array('username' => $member_info['member_name'], 'userid' => $member_info['member_id'], 'key' => $token));
            } else {
                output_error('注册失败');
            }
        } else {
            output_error($member_info['error']);
        }

    }
}
