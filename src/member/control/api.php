<?php
/**
 * 用户API接口
 */



defined('ByShopWWI') or exit('Access Invalid!');

class apiControl extends BaseLoginControl {

    public function __construct(){
        parent::__construct();
    }

    /**
     * 登录操作
     *
     */
    public function loginOp(){
		
        Language::read("home_login_index,home_login_register");
        $lang   = Language::getLangContent();
        $model_member   = Model('member');

		if (process::islock('login')) {
			$this -> apiResult( '1001', $lang['nc_common_op_repeat'] ) ;
		}
		$obj_validate = new Validate();
		$user_name = $_REQUEST['user_name'];
		$password = $_REQUEST['password'];
		$obj_validate->validateparam = array(
			array("input"=>$user_name,     "require"=>"true", "message"=>$lang['login_index_username_isnull']),
			array("input"=>$password,      "require"=>"true", "message"=>$lang['login_index_password_isnull']),
		);
		$error = $obj_validate->validate();
		if ($error != ''){
			$this -> apiResult( '1002', $error ) ;
		}
		
		$condition = array();
    	$member_info = $model_member->getMemberInfo(array('member_name'=>$user_name));
        $passwordHash = passwordHash($password,$member_info['password_salt'],$member_info['password_account'],$member_info['member_time']);

		if(is_array($member_info) && $passwordHash == $member_info['member_passwd']) {
			unset( $member_info['member_passwd'] ) ;
			$this -> apiResult( '0', 'succ', $member_info ) ;
		}else{
			$this -> apiResult( '1004', $lang['login_index_login_fail'] ) ;
		}
    }

    function apiResult($code, $message="", $data="")
	{
		$result = array(
			'error' => $code,
			'message' => $message,
			'data' => $data
		) ;
		die( json_encode( $result ) ) ;
	}
}
