<?php
/**
 * 第三方账号登录行为
 *
 * @copyright  Copyright (c) 2007-2015 ShopWWI Inc. (http://www.shopwwi.com)
 * @license    http://www.shopwwi.com
 * @link       http://www.shopwwi.com
 * @since      File available since Release v1.1
 */
defined('ByShopWWI') or exit('Access Invalid!');
class connect_apiLogic {

    /**
     * 手机注册
     * @param array $order_info
     * @param string $phone 手机号码
     * @param string $password 密码
     * @return array
     */
    public function smsRegister($phone, $captcha, $password, $client) {
		if ($this->check_captcha($phone,$captcha)){
			if(C('sms_register') != 1) {
                //output_error('系统没有开启手机注册功能');
				return array('state'=>0,'msg'=>'系统没有开启手机注册功能');
            }
            /** @var memberModel $model_member */
			$model_member = Model('member');
            $member_name = $phone;
            $member = $model_member->getMemberInfo(array('member_name'=> $member_name));//检查重名
            if(!empty($member)) {
                //output_error('用户名已被注册');
				return array('state'=>0,'msg'=>'用户名已被注册');
            }
            $member = $model_member->getMemberInfo(array('member_mobile'=> $phone));//检查手机号是否已被注册
            if(!empty($member)) {
                //output_error('手机号已被注册');
				return array('state'=>0,'msg'=>'手机号已被注册');
            }	
			$member = array();
            $member['member_name'] = $member_name;
            $member['member_passwd'] = $password;
            $member['member_mobile'] = $phone;
			$member['member_email']     = '';
            $member['member_mobile_bind'] = 1;
            $member['source'] = $client;
            $result = $model_member->addMember($member);
            if($result) {
                //$member = $model_member->getMemberInfo(array('member_mobile'=> $phone));
                $member['member_id'] = $result;
				//$key =$this->_get_token($member['member_id'],$member['member_name'],$client);
				return array('state'=>1,'username'=>$member['member_name'] ,'msg'=>'注册成功' ,'userid'=>$member['member_id']);
            } else {
				return array('state'=>0,'msg'=>'注册失败',$member);
            }
		}
       
    }

    /**
     * 小程序手机注册
     * @param $phone
     * @param $captcha
     * @param $wei_info
     * @return array
     */
    public function smsWeiRegister($phone, $captcha, $wei_info) {

        if ($this->check_captcha($phone,$captcha)){
            /** @var memberModel $model_member */
            $model_member = Model('member');
            $member_name = $phone;
            $member = $model_member->getMemberInfo(array('member_name'=> $member_name));//检查重名
            if(!empty($member)) {
                //output_error('用户名已被注册');
                return array('state'=>0,'msg'=>'用户名已被注册');
            }
            $member = $model_member->getMemberInfo(array('member_mobile'=> $phone));//检查手机号是否已被注册
            if(!empty($member)) {
                //output_error('手机号已被注册');
                return array('state'=>0,'msg'=>'手机号已被注册');
            }

            if (!isset($wei_info['unionid'])) {
                return array('state'=>0,'msg'=>'注册失败！！');
            }

            $member = array();
            $member['member_name'] = $member_name;
            $member['member_passwd'] = '123456';
            $member['member_mobile'] = $phone;
            $member['member_email']     = '';
            $member['member_mobile_bind'] = 1;
            $member['weixin_unionid'] = $wei_info['unionid'];
            $member['weixin_info'] = serialize($wei_info);
            $member['source'] = 'wxlogin';
            $result = $model_member->addMember($member);
            if($result) {
                //$member = $model_member->getMemberInfo(array('member_mobile'=> $phone));
                $member['member_id'] = $result;
                return array('state'=>1,'username'=>$member['member_name'] ,'msg'=>'注册成功' ,'userid'=>$member['member_id']);
            } else {
                return array('state'=>0,'msg'=>'注册失败',$member);
            }
        }
    }

	/**
     * 手机找回密码
     * @param array $order_info
     * @param string $phone 手机号码
     * @param string $password 密码
     * @return array
     */
    public function smsPassword($phone, $captcha, $password, $client) {
		if ($this->check_captcha($phone,$captcha,3)){
			if(C('sms_password') != 1) {
                //output_error('系统没有开启手机找回密码功能');
				return array('state'=>0,'msg'=>'系统没有开启手机找回密码功能');
            }
            $condition = array();
            $condition['log_phone'] = $phone;
            $condition['log_captcha'] = $captcha;
            $condition['log_type'] = 3;
            /** @var sms_logModel $model_sms_log */
            $model_sms_log = Model('sms_log');
            $sms_log = $model_sms_log->getSmsInfo($condition);
            if(empty($sms_log) || ($sms_log['add_time'] < TIMESTAMP-1800)) {//半小时内进行验证为有效
                //output_error('动态码错误或已过期，重新输入');
				return array('state'=>0,'msg'=>'动态码错误或已过期，重新输入');
            }
            /** @var memberModel $model_member */
            $model_member = Model('member');
            $member = $model_member->getMemberInfo(array('member_mobile'=> $phone));//检查手机号是否已被注册
            if(empty($member)&&C('OLD_STATUS')==true)
                $member = $model_member->getOldMemberInfo(array('login_account'=>$phone));
            if(!empty($member)) {
                $new_password = passwordHash(
                    $_POST['password'],
                    $member['password_salt'],
                    $member['password_account'],
                    $member['member_time']
                );
                //$new_password = md5($password);
                $model_member->editMember(array('member_id'=> $member['member_id']),array('member_passwd'=> $new_password));
                $model_member->createSession($member);//自动登录
                //output_data('密码修改成功');
				return array('state'=>1,'msg'=>'密码修改成功');
            }
		}
		else{
			output_error('验证错误!');
		}
	}

	public function getStateInfo(){
		if(C('sms_register') == 1) {
			return array('connect_sms_reg'=>1);
		}else{
			return array('connect_sms_reg'=>0);
		}
	}
	/**
     * 手机验证码验证
     */
    protected function check_captcha($phone,$captcha,$type='1'){
        if (strlen($phone) == 11 && strlen($captcha) == 6){
            $condition = array();
            $condition['log_phone'] = $phone;
            $condition['log_captcha'] = $captcha;
            $condition['log_type'] = $type;
            /** @var sms_logModel $model_sms_log */
            $model_sms_log = Model('sms_log');
            $sms_log = $model_sms_log->getSmsInfo($condition);
            if(empty($sms_log)) {//半小时内进行验证为有效
                $state = '动态码错误或已过期，重新输入';
				output_error($state);
            }
			return true;
        }
		output_error("动态码错误或已过期");
        return false;
    }
	/**
     * AJAX验证
     *
     */
	protected function check(){
        if (checkSeccode($_GET["sec_key"],$_GET["sec_val"])){
            return true;
        }else{
            return false;
        }
    }

}
