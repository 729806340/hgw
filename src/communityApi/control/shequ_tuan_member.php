<?php
/**
 * 地区
 *
 *
 *
 * @copyright  Copyright (c) 2007-2015 ShopNC Inc. (http://www.shopnc.net)
 * @license    http://www.shopnc.net
 * @link       http://www.shopnc.net
 * @since      File available since Release v1.1
 */


defined('ByShopWWI') or exit('Access Invalid!');

class shequ_tuan_memberControl extends mobileHomeControl
{

    public function __construct()
    {
        parent::__construct();
        if($_GET['op']=='change_password'){
            $this->checkLogin();
        }
    }


    protected function checkLogin(){
        $access=MD5($_REQUEST['member_id']."654123");
        if($access != $_REQUEST['access_token']){
            output_error('access_token错误');
        };
    }


    public function indexOp()
    {

    }

    //登录
    public function tuan_loginOp()
    {
        $obj_validate = new Validate();
        $user_name = $_POST['user_name'];
        $password = $_POST['password'];
        $obj_validate->validateparam = array(
            array("input" => $user_name, "require" => "true", "message" => "用户名必须输入"),
            array("input" => $password, "require" => "true", "message" => "密码必须输入"),
        );
        $error = $obj_validate->validate();
        if ($error != '') {
            output_error($error);
        }
        $model_member = Model('member');
        /** @var memberModel $model_member */
        $member_info = $model_member->getMemberInfo(array('member_name' => $user_name));
        $passwordHash = passwordHash($password, $member_info['password_salt'], $member_info['password_account'], $member_info['member_time']);
        if ($passwordHash != $member_info['member_passwd'] && preg_match('/^0?(13|15|17|18|14)[0-9]{9}$/i', $user_name)) {
            $member_info = $model_member->getMemberInfo(array('member_mobile' => $user_name));
            $passwordHash = passwordHash($password, $member_info['password_salt'], $member_info['password_account'], $member_info['member_time']);
        }
        if ($passwordHash != $member_info['member_passwd'] && (strpos($user_name, '@') > 0)) {
            $member_info = $model_member->getMemberInfo(array('member_email' => $user_name));
            $passwordHash = passwordHash($password, $member_info['password_salt'], $member_info['password_account'], $member_info['member_time']);
        }
        if (is_array($member_info) && $passwordHash == $member_info['member_passwd']) {
            $member_info = $model_member->getMemberInfo(array('member_id' => $member_info['member_id']));
            if (!$member_info['member_state']) {
                output_error('账号被停用');
            }
            $tuan_acc = $this->makeAcc($member_info);
            setNcCookie('member_id', $member_info['member_id'], 365 * 24 * 60 * 60);
            setNcCookie('member_name', $member_info['member_name'], 365 * 24 * 60 * 60);
            setNcCookie('member_turename', $member_info['member_turename'], 365 * 24 * 60 * 60);
            setNcCookie('tuan_access', $tuan_acc);
            $_SESSION['tuan_access'] = $tuan_acc;
            $_SESSION['member_id'] = $member_info['member_id'];
            $data['member_id'] = $member_info['member_id'];
            $data['access_token'] = $tuan_acc;
            output_data('登录成功', $data);
        } else {
//            process::addprocess('login');
            //showDialog($lang['login_index_login_fail'],'','error',$script);
            // showDialog(empty($member_info)?'用户名不存在':'密码错误','','error',$script);
            output_error(empty($member_info) ? '用户名不存在' : '密码错误');
        }
    }


    //极验证
    public function geetestOp()
    {

        require_once BASE_CORE_PATH . '/lib/geetest/class.geetestlib.php';
        require_once BASE_CORE_PATH . '/lib/geetest/config.php';
        $GtSdk = new GeetestLib(CAPTCHA_ID, PRIVATE_KEY);

        $data = array(
            "user_id" => "test", # 网站用户id
            "client_type" => "web", #web:电脑上的浏览器；h5:手机上的浏览器，包括移动应用内完全内置的web_view；native：通过原生SDK植入APP应用的方式
            "ip_address" => "127.0.0.1" # 请在此处传输用户请求验证时所携带的IP
        );

        $status = $GtSdk->pre_process($data, 1);
        $_SESSION['gtserver'] = $status;
        $_SESSION['user_id'] = $data['user_id'];
        echo $GtSdk->get_response_str();
        exit;
    }

    //发送验证码
    public function sendSmsOp()
    {
        //先验证极验证是否通过
        $geetest_challenge = $_POST['geetest_challenge'];
        $geetest_validate =  $_POST['geetest_validate'];
        $geetest_seccode = $_POST['geetest_seccode'];
        $log_type = $_POST['type'];
        $phone = $_POST['phone'];
        require_once BASE_CORE_PATH.'/lib/geetest/class.geetestlib.php';
        require_once BASE_CORE_PATH.'/lib/geetest/config.php';
        $GtSdk = new GeetestLib(CAPTCHA_ID, PRIVATE_KEY);
        $data = array(
            "user_id" => 'test', # 网站用户id
            "client_type" => "web", #web:电脑上的浏览器；h5:手机上的浏览器，包括移动应用内完全内置的web_view；native：通过原生SDK植入APP应用的方式
            "ip_address" => "127.0.0.1" # 请在此处传输用户请求验证时所携带的IP
        );
        $result = $GtSdk->success_validate($geetest_challenge, $geetest_validate, $geetest_seccode, $data);
        if (!$result) {
              output_error("极验证失败");
        }
        $model_sms_log = Model('sms_log');
        $condition = array();
        $condition['log_ip'] = getIp();
        $condition['log_type'] = $log_type;
        $sms_log = $model_sms_log->getSmsInfo($condition);
        if (!empty($sms_log) && ($sms_log['add_time'] > TIMESTAMP - 600)) {//同一IP1分钟内只能发一条短信
//            $state = '同一IP地址1分钟内，请勿多次获取动态码！';
            output_error('同一IP地址10分钟内，请勿多次获取动态码！');
        } else {
            $state = 'true';
            $log_array = array();
            $model_member = Model('member');
            $member = $model_member->getMemberInfo(array('member_mobile' => $phone));
            $captcha = rand(100000, 999999);
            $model_sms_log = Model('sms_log');
            $log_msg = '您于' . date("Y-m-d");
            switch ($log_type) {
                case '1':
                    if (!empty($member)) {//检查手机号是否已被注册
                        $state = '当前手机号已被注册，请更换其他号码。';
                    }
                    $log_msg .= '申请注册会员，动态码：' . $captcha . '。';
                    break;
                case '2':
                    if (empty($member)) {//检查手机号是否已绑定会员
                        $state = '当前手机号未注册，请检查号码是否正确。';
                    }
                    $log_msg .= '申请登录，动态码：' . $captcha . '。';
                    $log_array['member_id'] = $member['member_id'];
                    $log_array['member_name'] = $member['member_name'];
                    break;
                case '3':
                    if (empty($member)) {//检查手机号是否已绑定会员
                        $state = '当前手机号未注册，请检查号码是否正确。';
                    }
                    $log_msg .= '申请重置登录密码，动态码：' . $captcha . '。';
                    $log_array['member_id'] = $member['member_id'];
                    $log_array['member_name'] = $member['member_name'];
                    break;
                default:
                    $state = '参数错误';
                    break;
            }
            if ($state == 'true') {
                $sms = new Sms();
                $result = $sms->send($phone, $log_msg);
                if ($result) {
                    $log_array['log_phone'] = $phone;
                    $log_array['log_captcha'] = $captcha;
                    $log_array['log_ip'] = getIp();
                    $log_array['log_msg'] = $log_msg;
                    $log_array['log_type'] = $log_type;
                    $log_array['add_time'] = time();
                    $model_sms_log->addSms($log_array);
                    output_data('短信发送成功');
                } else {
                    output_error('手机短信发送失败');
                }
            } else {
                output_error($state);
            }
        }
    }

    //注册
    public function registOp()
    {
        /** @var  $model_member */
        $model_member = Model('member');
        $phone = $_POST['phone'];
        $password = trim($_POST['password']);
        $captcha = $_POST['captcha'];

       $condition['member_name'] = $phone;
        $where['member_mobile'] = $phone;
       $has_phone = $model_member->getMemberInfo($condition);
       $has_model_phone = $model_member->getMemberInfo($where);
        if($has_phone||$has_model_phone){
            output_error('手机号已注册');
        }

        $res = $this->check_captcha($phone, $captcha);
        if ($res !== 'true') {
            output_error($res);
        }

//        $condition = array();
//        $condition['log_phone'] = $phone;
//        $condition['log_captcha'] = $captcha;
//        $condition['log_type'] = 1;
//        $model_sms_log = Model('sms_log');
//        $sms_log = $model_sms_log->getSmsInfo($condition);
//        if(empty($sms_log) || ($sms_log['add_time'] < TIMESTAMP-600)) {//半小时内进行验证为有效
//            output_error('验证码错误或验证码过期');
//        }
        $member = array();
        $member['member_name'] = $phone;
        $member['member_passwd'] = $_POST['password'];
        $member['member_mobile'] = $phone;
        $member['member_mobile_bind'] = 1;
        $member['crm_member_id'] = 0;
        $result = $model_member->addMember($member);
        if($result){
            $member = $model_member->getMemberInfo(array('member_name'=> $member_name));
            $model_member->createSession($member,true);//自动登录
            output_data('注册成功');
        }else{
            output_error('注册失败');
        }

    }

    //重置密码
    public function resetOp(){
        $phone = $_POST['phone'];
        $captcha = $_POST['captcha'];
        $condition = array();
        $condition['log_phone'] = $phone;
        //$condition['log_captcha'] = $captcha;
        $condition['log_type'] = 3;
        $model_sms_log = Model('sms_log');
        $sms_log = $model_sms_log->getSmsInfo($condition);
        $cacheKey = 'sms_log_verify_count_'.$sms_log['log_id'];
       // $count = rkcache($cacheKey);
        if(empty($sms_log) || ($sms_log['add_time'] < TIMESTAMP - 600)) {//10分钟时期
            output_error('动态码已过期，请重新发送');
        }
        if($sms_log['log_captcha'] != $captcha){
//            wkcache($cacheKey,$count>0?$count+1:1);
            output_error('动态码输入错误,重新输入');
        }
        $model_member = Model('member');
        $member = $model_member->getMemberInfo(array('member_mobile'=> $phone));//检查手机号是否已被注册
        if(!empty($member)) {
            $new_password = passwordHash($_POST['password'],$member['password_salt'],
                $member['password_account'],
                $member['member_time']
            );
            //md5($_POST['password']);
            $model_member->editMember(array('member_id'=> $member['member_id']),array('member_passwd'=> $new_password));
            $model_member->createSession($member);//自动登录
            output_data('密码重置成功');
//            showDialog('密码修改成功',urlMember('member_information', 'member'),'succ');
        }else{
            output_error('手机号未注册');
        }

    }
    //修改密码
    public function change_passwordOp(){
        $member_id = $_POST['member_id'];
        $password  = trim($_POST['password']);
        $new_password  = trim($_POST['new_password']);
        $model_member  = Model('member');
        if($password==$new_password){
            output_error('新旧密码相同');
        }
        $info  = $model_member->getMemberInfo(array('member_id'=>$member_id));
        if(!$info){
            output_error('参数错误');
        }
        if(passwordHash($password,$info['password_salt'],$info['password_account'],$info['member_time'])!=$info['member_passwd']){
            output_error('原密码错误');
        }
        $new_password_hash  = passwordHash($new_password,$info['password_salt'],$info['password_account'],$info['member_time']);
        $condition['member_id'] = $member_id;
        $update['member_passwd'] = $new_password_hash;
        $res  = $model_member->editMember($condition,$update);
        if($res){
            output_data('修改成功');
        }else{
            output_error('修改失败');
        }
     }

    /**
     * 验证注册动态码
     */
    protected function check_captcha($phone, $captcha)
    {

        $state = 'true';
        $condition = array();
        $condition['log_phone'] = $phone;
        $condition['log_captcha'] = $captcha;
        $condition['log_type'] = 1;
        $model_sms_log = Model('sms_log');
        $sms_log = $model_sms_log->getSmsInfo($condition);
        if (empty($sms_log) || ($sms_log['add_time'] < TIMESTAMP - 600)) {//半小时内进行验证为有效
            $state = '动态码错误或已过期，重新输入';
        }
        return $state;
    }

    /**
     * 生成密钥
     * @param $member_info
     * @return string
     */
    private function makeAcc($member_info)
    {
        $access = MD5($member_info['member_id'] . "654123");
        return $access;
    }
}
