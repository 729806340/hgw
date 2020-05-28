<?php
/**
 * 我的钱
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

class member_accountControl extends mobileMemberControl {

    public function __construct(){
        parent::__construct();
    }

	
	/**
     * 我的钱
     */
    public function get_mobile_infoOp() {
		$data = array();
		$data['state'] = true;
		if($this->member_info['member_mobile_bind']==0){
			$data['state'] = false;
		}
		$data['mobile'] = $this->member_info['member_mobile'];
		output_data($data);
	}

	

	public function get_paypwd_infoOp() {		
		$data['state'] = false;
		if($this->member_info['member_paypwd']){
			$data['state'] = true;
		}
		output_data($data);
	}

	public function bind_mobile_step1Op() {
		if(!$this->check()){
			output_error('验证码错误！');
		}
		$mobile = $_POST['mobile'];
		$this->send_mobile($mobile);	
	}

	public function bind_mobile_step2Op() {
		$auth_code = $_POST['auth_code'];
		$member_id = $this->member_info['member_id'];
		$model_member = Model('member');
        $member_info = $model_member->getMemberInfoByID($member_id,'member_mobile_bind');
        if ($member_info) {
            $obj_validate = new Validate();
            $obj_validate->validateparam = array(
                array("input"=>$auth_code, "require"=>"true", 'validator'=>'number',"message"=>'请正确填写手机验证码')
            );
            $error = $obj_validate->validate();
            if ($error != ''){
                output_error($error);
            }

            $condition = array();
            $condition['member_id'] = $member_id;
            $condition['auth_code'] = intval($auth_code);
            $member_common_info = $model_member->getMemberCommonInfo($condition,'send_acode_time');
            if (!$member_common_info) {
                output_error('手机验证码错误，请重新输入');
            }
            if (TIMESTAMP - $member_common_info['send_acode_time'] > 1800) {
                output_error('手机验证码已过期，请重新获取验证码');
            }
			$update = $model_member->editMember(array('member_id'=>$member_id),array('member_mobile_bind'=>1));
            if (!$update) {
                output_error('系统发生错误，如有疑问请与管理员联系');
            }
			output_data('绑定成功');
		}
	
	}




	public function modify_mobile_step2Op() {
		if(!$this->check()){
			output_error('验证码错误！');
		}
		$this->send_mobile($this->member_info['member_mobile']);
	}
	
	public function modify_mobile_step3Op() {
		$auth_code = $_POST['auth_code'];
		$member_id = $this->member_info['member_id'];
		$model_member = Model('member');
        $member_info = $model_member->getMemberInfoByID($member_id,'member_mobile_bind');
        if ($member_info) {
            $obj_validate = new Validate();
            $obj_validate->validateparam = array(
                array("input"=>$auth_code, "require"=>"true", 'validator'=>'number',"message"=>'请正确填写手机验证码')
            );
            $error = $obj_validate->validate();
            if ($error != ''){
                output_error($error);
            }

            $condition = array();
            $condition['member_id'] = $member_id;
            $condition['auth_code'] = intval($auth_code);
            $member_common_info = $model_member->getMemberCommonInfo($condition,'send_acode_time');
            if (!$member_common_info) {
                output_error('手机验证码错误，请重新输入');
            }
            if (TIMESTAMP - $member_common_info['send_acode_time'] > 1800) {
                output_error('手机验证码已过期，请重新获取验证码');
            }
			$update = $model_member->editMember(array('member_id'=>$member_id),array('member_mobile_bind'=>0));
            if (!$update) {
                output_error('系统发生错误，如有疑问请与管理员联系');
            }
			output_data('解绑成功');
		}

	}
	
    public function modify_paypwd_step2Op() {
		if(!$this->check()){
			output_error('验证码错误！');
		}
		$this->send_mobile($this->member_info['member_mobile']);
	}	

	public function modify_paypwd_step3Op() {
		$auth_code = $_POST['auth_code'];
		$member_id = $this->member_info['member_id'];
		$model_member = Model('member');
        $member_info = $model_member->getMemberInfoByID($member_id,'member_mobile_bind');
        if ($member_info) {
            $obj_validate = new Validate();
            $obj_validate->validateparam = array(
                array("input"=>$auth_code, "require"=>"true", 'validator'=>'number',"message"=>'请正确填写手机验证码')
            );
            $error = $obj_validate->validate();
            if ($error != ''){
                output_error($error);
            }

            $condition = array();
            $condition['member_id'] = $member_id;
            $condition['auth_code'] = intval($auth_code);
            $member_common_info = $model_member->getMemberCommonInfo($condition,'send_acode_time');
            if (!$member_common_info) {
                output_error('手机验证码错误，请重新输入');
            }
            if (TIMESTAMP - $member_common_info['send_acode_time'] > 1800) {
                output_error('手机验证码已过期，请重新获取验证码');
            }
            $data = array();
			$data['auth_code'] = intval($auth_code);
			$data['send_acode_time'] = TIMESTAMP;
            $update = $model_member->editMemberCommon($data,array('member_id'=>$member_id));
            if (!$update) {
                output_error('系统发生错误，如有疑问请与管理员联系');
            }
            $update = $model_member->editMember(array('member_id'=>$member_id),array('member_mobile_bind'=>1));
            if (!$update) {
                output_error('系统发生错误，如有疑问请与管理员联系');
            }
            output_data('手机号绑定成功');
        }
	}



	public function modify_paypwd_step4Op() {		
		$member_id = $this->member_info['member_id'];
		$model_member = Model('member');
		$member_common_info = $model_member->getMemberCommonInfo(array('member_id'=>$member_id));
		if (empty($member_common_info) || !is_array($member_common_info)) {
			output_error('验证失败');
		}
		if (TIMESTAMP - $member_common_info['send_acode_time'] > 1800) {
			output_error('验证码已被使用或超时，请重新获取验证码');
		}
		output_data(1);	
	}
	public function modify_paypwd_step5Op() {
		$member_id = $this->member_info['member_id'];
        /** @var memberModel $model_member */
        $model_member = Model('member');
		$member_common_info = $model_member->getMemberCommonInfo(array('member_id'=>$member_id));

        if (empty($member_common_info) || !is_array($member_common_info)) {
			output_error('验证失败');
		}
		if (TIMESTAMP - $member_common_info['send_acode_time'] > 1800) {
			output_error('验证码已被使用或超时，请重新获取验证码');
		}

        $obj_validate = new Validate();
        $obj_validate->validateparam = array(
                array("input"=>$_POST["password"],      "require"=>"true",      "message"=>'请正确输入密码'),
                array("input"=>$_POST["password1"],  "require"=>"true",      "validator"=>"Compare","operator"=>"==","to"=>$_POST["password"],"message"=>'两次密码输入不一致'),
        );
        $error = $obj_validate->validate();
        if ($error != ''){
            output_error($error);
        }
        $member = $model_member->getMemberInfo(array('member_id'=>$member_id));
        /*$new_password   = passwordHash(
            $_POST['password'],
            $member['password_salt'],
            $member['password_account'],
            $member['member_time']
        );*/
        $new_password   = md5($_POST['password']);
        $update = $model_member->editMember(array('member_id'=>$member_id),array('member_paypwd'=>$new_password));
        $message = $update ? '密码设置成功' : '密码设置失败';
        unset($_SESSION['auth_modify_paypwd']);
		output_data($message);	
	}


	

	/**
     * 发短信
     */
	private function send_mobile($mobile){
		$obj_validate = new Validate();
		//$mobile = $_GET["mobile"];
		$member_id = $this->member_info['member_id'];
        $obj_validate->validateparam = array(
            array("input"=>$mobile, "require"=>"true", 'validator'=>'mobile',"message"=>'请正确填写手机号码'),
        );
        $error = $obj_validate->validate();
        if ($error != ''){
			output_error($error);
        }

        $model_member = Model('member');

        //发送频率验证
        $member_common_info = $model_member->getMemberCommonInfo(array('member_id'=>$member_id));
        if (!empty($member_common_info['send_mb_time'])) {
            if (date('Ymd',$member_common_info['send_mb_time']) != date('Ymd',TIMESTAMP)) {
                $data = array();
                $data['send_mb_times'] = 0;
                $update = $model_member->editMemberCommon($data,array('member_id'=>$member_id));               
            } else {
                if (TIMESTAMP - $member_common_info['send_mb_time'] < 58) {
					output_error('请60秒以后再次发送短信');
                } else {
                    if ($member_common_info['send_mb_times'] >= 15) {
						output_error('您今天发送短信已超过15条，今天将无法再次发送');
                    }
                }                
            }
        }

        $condition = array();
        $condition['member_mobile'] = $mobile;
        $condition['member_id'] = array('neq',$member_id);
        $member_info = $model_member->getMemberInfo($condition,'member_id');
        if ($member_info) {
			output_error('该手机号已被使用，请更换其它手机号');
        }
        $data = array();
        $data['member_mobile'] = $mobile;
        $data['member_mobile_bind'] = 0;
        $update = $model_member->editMember(array('member_id'=>$member_id),$data);
        if (!$update) {
			output_error('系统发生错误，如有疑问请与管理员联系');
        }

        $verify_code = rand(100,999).rand(100,999);

        $model_tpl = Model('mail_templates');
        $tpl_info = $model_tpl->getTplInfo(array('code'=>'modify_mobile'));
        $param = array();
        $param['site_name'] = C('site_name');
        $param['send_time'] = date('Y-m-d H:i',TIMESTAMP);
        $param['verify_code'] = $verify_code;
        $message    = ncReplaceText($tpl_info['content'],$param);
        $sms = new Sms();
        $result = $sms->send($mobile,$message);
        if ($result) {
            $data = array();
            $data['auth_code'] = $verify_code;
            $data['send_acode_time'] = TIMESTAMP;
            $data['send_mb_time'] = TIMESTAMP;
            $data['send_mb_times'] = array('exp','send_mb_times+1');
            $update = $model_member->editMemberCommon($data,array('member_id'=>$member_id));
            if (!$update) {
				output_error('系统发生错误，如有疑问请与管理员联系');
            }
			$output['sms_time'] = 60;
			$output['data'] = $message;
			output_data($output);
        } else {
			output_error('发送失败');
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
