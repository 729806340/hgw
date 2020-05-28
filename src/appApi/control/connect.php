<?php
/**
 *
 * QQ,新浪微博登陆
 *
 *
 * @copyright  Copyright (c) 2007-2015 ShopNC Inc. (http://www.shopnc.net)
 * @license    http://www.shopnc.net/
 * @link       http://www.shopnc.net/
 * @since      File available since Release v1.1
 */
defined('ByShopWWI') or exit('Access Invalid!');
class connectControl extends mobileHomeControl{
	public function __construct(){
        parent::__construct();
    }
	
    /**
     * qq/微信联合登录
     */
	public function loginOp()
	{
		if( empty($_POST['openid']) ) {
			output_error("缺少参数openid");
		}
		if( empty($_POST['nickname']) ) {
			output_error("缺少参数nickname");
		}
		if( empty($_POST['appid']) ) {
			output_error("缺少参数appid");
		}
		if( empty($_POST['provider']) || !in_array($_POST['provider'], array('qq', 'weixin')) ) { //qq:qq, 微信:weixin
			output_error("缺少参数provider");
		}
		if( empty($_POST['client']) || !in_array($_POST['client'], array('ios', 'android')) ) {
			output_error("缺少参数client");
		}
		
		//根据openid判断是否已绑定openid
		$this->autologin();
		
		/** @var memberModel $model_member */
		$model_member   = Model('member');
		$connect_user_info = array();
		$connect_user_info['openid'] = $_POST['openid'];
		$connect_user_info['nickname'] = $_POST['nickname'];
		$connect_user_info['appid'] = $_POST['appid'];
		/**
		 * 会员添加
		 */
		$user_array = array();
		$user_array['member_name']      = $connect_user_info['nickname'];
		$user_array['member_passwd']    = rand(100000, 999999);
		$user_array['member_email']     = '';
		$user_array['member_qqopenid']  = $connect_user_info['openid'];//qq openid
		$user_array['member_qqinfo']    = serialize($connect_user_info);//qq 信息
		$rand = rand(100, 899);
		if(strlen($user_array['member_name']) < 3) $user_array['member_name']       = $connect_user_info['nickname'].$rand;
		$check_member_name  = $model_member->getMemberInfo(array('member_name'=>trim($user_array['member_name'])));
		if(empty($check_member_name)&&C('OLD_STATUS')) {
			$check_member_name = $model_member->getOldMemberInfo(array('login_account'=>trim($user_array['member_name'])));
		}
		
		$result = 0;
		if(empty($check_member_name)) {
			$result = $model_member->addMember($user_array);
		}else {
			for ($i = 1;$i < 999;$i++) {
				$rand += $i;
				$user_array['member_name'] = trim($connect_user_info['nickname']).$rand;
				$check_member_name  = $model_member->getMemberInfo(array('member_name'=>trim($user_array['member_name'])));
				if(empty($check_member_name)&&C('OLD_STATUS')==true)
					$check_member_name = $model_member->getOldMemberInfo(array('login_account'=>trim($user_array['member_name'])));
					if(empty($check_member_name)) {
						$result = $model_member->addMember($user_array);
						break;
					}
			}
		}
		if($result) {
			//$avatar = @copy($qquser_info['figureurl_qq_2'],BASE_UPLOAD_PATH.'/'.ATTACH_AVATAR."/avatar_$result.jpg");
			$update_info    = array();
			if($avatar) {
				$update_info['member_avatar']   = "avatar_$result.jpg";
				$model_member->editMember(array('member_id'=>$result),$update_info);
			}
			$member_info = $model_member->getMemberInfo(array('member_name'=>$user_array['member_name']));
			$token = $this->_get_token($member_info['member_id'], $member_info['member_name'], $_POST['client']);
			$state_data['key'] = $token;
			$state_data['username'] = $member_info['member_name'];
			$state_data['userid'] = $member_info['member_id'];
			if($token) {
				output_data($state_data);
			} else {
				output_error('登录失败222');
			}
		} else {
			output_error('会员注册失败');//"会员注册失败"
		}
	}
	
	/**
	 * 绑定qq后自动登录
	 */
	private function autologin(){
		//查询是否已经绑定该qq,已经绑定则直接返回
		/** @var memberModel $model_member */
		$model_member   = Model('member');
		$array  = array();
		if( $_POST['provider'] == 'qq' ) {
			$array['member_qqopenid']   = $_POST['openid'];
		} else if ( $_POST['provider'] == 'weixin' ) {
			$array['weixin_unionid']   = $_POST['openid'];
		} 
		$member_info = $model_member->getMemberInfo($array);
		if(empty($member_info)&&C('OLD_STATUS')) {
			$member_info = $model_member->getEcOpenid($_POST['openid'],$_POST['provider']);
		}
		if (is_array($member_info) && count($member_info)>0){
			if(!$member_info['member_state']){//1为启用 0 为禁用
				output_error( Language::get('nc_notallowed_login') );
			}
			$token = $this->_get_token($member_info['member_id'], $member_info['member_name'], $_POST['client']);
            if($token) {
                output_data(array('username' => $member_info['member_name'], 'userid' => $member_info['member_id'], 'key' => $token));
            } else {
                output_error('登录失败');
            }
			
		}
	}

	
	/**
     * 短信动态码
     */
    public function get_sms_captchaOp(){
        //$_POST = array('phone'=>'15871805746' , 'type'=>1);
        $state = '发送失败';
        $phone = $_POST['phone'];
        if ($this->check() && strlen($phone) == 11){
            $log_type = $_POST['type'];//短信类型:1为注册,2为登录,3为找回密码
            $model_sms_log = Model('sms_log');
            $condition = array();
            $condition['log_ip'] = getIp();
            $condition['log_type'] = $log_type;
            $sms_log = $model_sms_log->getSmsInfo($condition);
            if(!empty($sms_log) && ($sms_log['add_time'] > TIMESTAMP-10)) {//同一IP十分钟内只能发一条短信
                $state = '同一IP地址十分钟内，请勿多次获取动态码！';
            } else {
                $state = 'true';
                $log_array = array();
                $model_member = Model('member');
                $member = $model_member->getMemberInfo(array('member_mobile'=> $phone));
                $captcha = rand(100000, 999999);
                $log_msg = '您于'.date("Y-m-d");
                switch ($log_type) {
                    case '1':
                        if(C('sms_register') != 1) {
                            $state = '系统没有开启手机注册功能';
                        }
                        if(!empty($member)) {//检查手机号是否已被注册
                            $state = '当前手机号已被注册，请更换其他号码。';
                        }
                        $log_msg .= '申请注册会员，动态码：'.$captcha.'。';
                        break;
                    case '2':
                        if(C('sms_login') != 1) {
                            $state = '系统没有开启手机登录功能';
                        }
                        if(empty($member)) {//检查手机号是否已绑定会员
                            $state = '当前手机号未注册，请检查号码是否正确。';
                        }
                        $log_msg .= '申请登录，动态码：'.$captcha.'。';
                        $log_array['member_id'] = $member['member_id'];
                        $log_array['member_name'] = $member['member_name'];
                        break;
                    case '3':
                        if(C('sms_password') != 1) {
                            $state = '系统没有开启手机找回密码功能';
                        }
                        if(empty($member)) {//检查手机号是否已绑定会员
                            $state = '当前手机号未注册，请检查号码是否正确。';
                        }
                        $log_msg .= '申请重置登录密码，动态码：'.$captcha.'。';
                        $log_array['member_id'] = $member['member_id'];
                        $log_array['member_name'] = $member['member_name'];
                        break;
                    default:
                        $state = '参数错误';
                        break;
                }
                if($state == 'true'){
                    $sms = new Sms();
					
                    $result = $sms->send($phone,$log_msg);
					 
                    //if(!$result){
					if($result){
                        $log_array['log_phone'] = $phone;
                        $log_array['log_captcha'] = $captcha;
                        $log_array['log_ip'] = getIp();
                        $log_array['log_msg'] = $log_msg;
                        $log_array['log_type'] = $log_type;
                        $log_array['add_time'] = time();
                        $model_sms_log->addSms($log_array);
						
						output_data(array('sms_time'=>10,'error'=>'1'));
                    } else {
                        $state = '手机短信发送失败';
                    }
                }
            }
        } else {
            $state = '手机号码验证识别';
        }
        output_error($state);
    }
    /**
     * 验证注册动态码
     */
    public function check_sms_captchaOp(){
        //$_POST = array('phone'=>'15871805746' ,'captcha'=>'649041');
        $state = '验证失败';
        $phone = $_POST['phone'];
        $captcha = $_POST['captcha'];
		if (strlen($phone) == 11){
			//output_data("11");
            $state = 'true';
            $condition = array();
            $condition['log_phone'] = $phone;
            $condition['log_captcha'] = $captcha;
            $condition['log_type'] = 1;
            $model_sms_log = Model('sms_log');
            $sms_log = $model_sms_log->getSmsInfo($condition);
            if(empty($sms_log) || ($sms_log['add_time'] < TIMESTAMP-1800)) {//半小时内进行验证为有效
                $state = '动态码错误或已过期，重新输入';
            }
			output_data($state);
        }
        output_error($state);
    }
	

	/**
     * 找回密码
     */
    public function find_password_wwOp(){
		if(C('sms_password') != 1) {
			output_error('系统没有开启手机找回密码功能','','error');
		}
		$phone = $_POST['phone'];
		$captcha = $_POST['captcha'];
		$condition = array();
		$condition['log_phone'] = $phone;
		$condition['log_captcha'] = $captcha;
		$condition['log_type'] = 3;
		$model_sms_log = Model('sms_log');
		$sms_log = $model_sms_log->getSmsInfo($condition);
		if(empty($sms_log) || ($sms_log['add_time'] < TIMESTAMP-1800)) {//半小时内进行验证为有效
			output_error('动态码错误或已过期，重新输入','','error');
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
			
			$token = $this->_get_token($member['member_id'], $member['member_name'], $_POST['client']);
            if($token) {
                output_data(array('username' => $member_info['member_name'], 'key' => $token));
            }
		}
	
        output_error($state);
    }
	
	/**
     * 手机注册
     */
    public function sms_registerOp(){
        //$_POST = array('phone'=>'15871805746' , 'password'=>md5('ljqfln528208') ,'captcha'=>'649041' ,'client'=>'ios');
        if(empty($_POST['phone']) || empty($_POST['password']) || !in_array($_POST['client'], $this->client_type_array) || empty($_POST['captcha'])) {
            output_error('非法参数');
        }
        $phone = $_POST['phone'];
        $captcha = $_POST['captcha'];
        $password = $_POST['password'];
        $client = $_POST['client'];
        /** @var connect_apiLogic $logic_connect_api */
        $logic_connect_api = Logic('connect_api');
        $state_data = $logic_connect_api->smsRegister($phone, $captcha, $password, $client);
	    if($state_data['state']=='1'){
	        $key = $this->_get_token($state_data['userid'], $state_data['username'], $client);
	        $state_data['key'] = $key;
            output_data($state_data);
        } else {
            output_error($state_data['msg']);
        }
    }


    /**
     * 手机找回密码
     */
    public function find_passwordOp(){
        //$_POST = array('phone'=>'15871805746' , 'password'=>md5('ljqgyj528208') ,'captcha'=>'649041' ,'client'=>'ios');
        if(empty($_POST['phone']) || empty($_POST['new_pwd']) || empty($_POST['captcha'])) {
            output_error('非法参数');
        }
        $_POST['password'] = $_POST['new_pwd'];
        $phone =  $_POST['phone'];
        $captcha = $_POST['captcha'];
        $password = $_POST['password'];
        $client =  $_POST['client'];
        /** @var connect_apiLogic $logic_connect_api */
        $logic_connect_api = Logic('connect_api');
        $state_data = $logic_connect_api->smsPassword($phone, $captcha, $password, $client);
        $this->connect_output_data($state_data);
    }

	/**
     * 格式化输出数据
     */
    private function connect_output_data($state_data, $type = 0){
        if($state_data['state']){
            unset($state_data['state']);
            unset($state_data['msg']);
            if ($type == 1){
                $state_data = 1;
            }
            output_data($state_data);
        } else {
            output_error($state_data['msg']);
        }
    }

	/**
     * 登录开关状态
     */
    public function get_stateOp() {
        $logic_connect_api = Logic('connect_api');
        $state_array = $logic_connect_api->getStateInfo();
        
        $key = $_GET['t'];
        if(trim($key) != '' && array_key_exists($key,$state_array)){
            output_data($state_array[$key]);
        } else {
            output_data($state_array);
        }
    }

	/**
     * 登录生成token
     */
    private function _get_token($member_id, $member_name, $client) {
        $model_mb_user_token = Model('mb_user_token');
        //生成新的token
        $mb_user_token_info = array();
        $token = md5($member_name . strval(TIMESTAMP) . strval(rand(0,999999)));
        $mb_user_token_info['member_id'] = $member_id;
        $mb_user_token_info['member_name'] = $member_name;
        $mb_user_token_info['token'] = $token;
        $mb_user_token_info['login_time'] = TIMESTAMP;
        $mb_user_token_info['client_type'] = $client;

        $result = $model_mb_user_token->addMbUserToken($mb_user_token_info);

        if($result) {
            return $token;
        } else {
            return null;
        }

    }
	/**
     * AJAX验证
     *
     */
	protected function check(){
        if(empty($_GET["sec_val"])) return false;
        if (checkSeccode($_GET["sec_key"],$_GET["sec_val"])){
            return true;
        }else{
            return false;
        }
    }

}
