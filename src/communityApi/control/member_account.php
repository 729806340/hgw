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
	
	public function update_passOp(){
	    //$_POST = array('member_id'=>223242, 'old_pwd'=>md5('ljqfln528208') ,'new_pwd'=>md5('ljqgyj528208'));
	    if(empty($_POST['member_id']) || empty($_POST['old_pwd']) || empty($_POST['new_pwd'])){
	        output_error('非法参数');
	    }
	    $member_id = intval($_POST['member_id']);
	    $new_pwd   = $_POST['new_pwd'];
	    $old_pwd = $new_password = passwordHash(
                    $_POST['old_pwd'],
                    $this->member_info['password_salt'],
                    $this->member_info['password_account'],
                    $this->member_info['member_time']
                );
	    if($member_id != $this->member_info['member_id']){
	        output_error('用户ID错误');
	    }
	    
	    if($old_pwd != $this->member_info['member_passwd']){
	        output_error('原始密码输入有误');
	    }
	    
	    $new_password = passwordHash(
                   $new_pwd,
                   $this->member_info['password_salt'],
                   $this->member_info['password_account'],
                   $this->member_info['member_time']
         );
	    $model_member = Model('member');
	    $model_member->editMember(array('member_id'=>$member_id),array('member_passwd'=> $new_password));
	    $model_member->createSession($member);//自动登录
	    $data = array('msg'=>'密码修改成功');
	    output_data($data);
	    //sreturn array('state'=>1,'msg'=>'密码修改成功');
	    
	}
	
	
	
	public function edit_memberinfoOp(){
        //Log::selflog('POST：'.json_encode($_POST));

        if(empty($_POST['member_id'])){
	        output_error('非法参数');
	    }
	    
	    if($_POST['member_id'] != $this->member_info['member_id']){
	        output_error('用户编号不匹配');
	    }
	    $model_member = Model('member');
	    $memberinfo = array();
	    if($_POST['true_name']){
// 	        $check_member = $model_member->getOldMemberInfo(array('member_name'=>$_POST['username']));
// 	        if($check_member['member_id']>0){
// 	            output_error("用户名已被占用");
// 	        }
	        $memberinfo['member_truename'] = $_POST['true_name'];
	    }
	    
	    if($_POST['sex']){
	        if(!in_array($_POST['sex'] , array(1,2,3))){
	            output_error('用户性别非法');
	        }
	        $memberinfo['member_sex'] = $_POST['sex'];
	    }
	    Log::selflog('提交文件：'.json_encode($_FILES));
	    if($_FILES['avatar']['name']){
	        $upload = new UploadFile();
	        $ext = strtolower(pathinfo($_FILES['pic']['name'], PATHINFO_EXTENSION));
	        $filename = "avatar_{$this->member_info['member_id']}.jpg";
	        $upload->set('file_name',$filename);
	        $upload->set('default_dir',ATTACH_AVATAR);
	        $upload->set('max_size',4096);
	        $thumb_width	= '120';
	        $thumb_height	= '120';
	        $upload->set('thumb_width',	$thumb_width);
	        $upload->set('thumb_height',$thumb_height);
	        //开始上传
	        $result = $upload->upfile('avatar');
            //Log::selflog('提交文件：'.json_encode($result));
            if (!$result){
                Log::selflog('错误信息：'.$upload->error);
	           output_error($upload->error);
	        }else{
	            $memberinfo['member_avatar'] = $filename.'?'.TIMESTAMP;
	        }
	    }
        //Log::selflog('用户信息：'.json_encode($memberinfo));

        if(count($memberinfo) >0){
	        $result = $model_member->where(array('member_id'=>$this->member_info['member_id']))->update($memberinfo);
            //Log::selflog('SQL：'.($model_member->getLastSql()));
            if($result>=0){
                dcache($this->member_info['member_id'], 'member');//编辑成功后删除缓存
                output_data('用户信息编辑成功');
	        }
	    }
        output_error('用户信息编辑失败');
	}

	public function bind_mobile_step1Op() {
// 		if(!$this->check()){
// 			output_error('验证码错误！');
// 		}
        //$_POST = array('mobile'=>'15871805746');
		$mobile = $_POST['phone'];
		$this->send_mobile($mobile);	
	}

	public function bind_mobile_step2Op() {
	    //$_POST['auth_code'] = '312165';
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




	public function modify_mobile_step1Op() {
// 		if(!$this->check()){
// 			output_error('验证码错误！');
// 		}
        if(empty($this->member_info['member_mobile'])){
            output_error('对不起您还没有绑定手机号码');
        }
		$this->send_mobile($this->member_info['member_mobile']);
	}
	
	public function modify_mobile_step2Op() {
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
	
    public function modify_paypwd_step1Op() {
// 		if(!$this->check()){
// 			output_error('验证码错误！');
// 		}
        if(empty($this->member_info['member_mobile']))output_error('请先绑定手机号');
		$this->send_mobile($this->member_info['member_mobile'],'authenticate');
	}	

	public function modify_paypwd_step4Op() {
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



	public function modify_paypwd_step3Op() {		
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
	
	public function modify_paypwd_step2Op() {
	    //$_POST = array('auth_code'=>'413687' , 'password'=>'ljqfln528208');
	    $auth_code = $_POST['auth_code'];
		$member_id = $this->member_info['member_id'];
		//验收手机验证码
		$model_member = Model('member');
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
        /** @var memberModel $model_member */
        
		$member_common_info = $model_member->getMemberCommonInfo(array('member_id'=>$member_id));

        if (empty($member_common_info) || !is_array($member_common_info)) {
			output_error('验证失败');
		}
		if (TIMESTAMP - $member_common_info['send_acode_time'] > 1800) {
			output_error('验证码已被使用或超时，请重新获取验证码');
		}
		//验证码验证结束

        $obj_validate = new Validate();
        $obj_validate->validateparam = array(
                array("input"=>$_POST["password"],      "require"=>"true",      "message"=>'请正确输入密码'),
                //array("input"=>$_POST["password1"],  "require"=>"true",      "validator"=>"Compare","operator"=>"==","to"=>$_POST["password"],"message"=>'两次密码输入不一致'),
        );
        $error = $obj_validate->validate();
        if ($error != ''){
            output_error($error);
        }
        $member = $model_member->getMemberInfo(array('member_id'=>$member_id));
//         $new_password   = passwordHash(
//             $_POST['password'],
//             $member['password_salt'],
//             $member['password_account'],
//             $member['member_time']
//         );
        $new_password = md5($_POST['password']);
        $update = $model_member->editMember(array('member_id'=>$member_id),array('member_paypwd'=>$new_password));
        $message = $update ? '密码设置成功' : '密码设置失败';
        //unset($_SESSION['auth_modify_paypwd']);
		output_data($message);	
	}


	

	/**
     * 发短信
     */
	private function send_mobile($mobile , $tpl){
	    //v($mobile);
		$obj_validate = new Validate();
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
        $code = $tpl?$tpl:'modify_mobile';
        $tpl_info = $model_tpl->getTplInfo(array('code'=>$code));
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
