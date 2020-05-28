<?php
// 本类由系统自动生成，仅供测试用途
namespace Home\Controller;
use Think\Controller;
class AuthController extends Controller {
	
	public $auth;
	
    public function _initialize(){
		if(empty($_SESSION['uid'])){
			$this->redirect('index.php/login/index');
		}else{
			$this->auth = new \Think\Auth();
		}
    }
	
	/**
	* 将所有管理员的操作记录在数据库中
	*
	* @param action_name string 操作的文本描述
	* @param flag int 操作是否成功，0表示失败，1表示成功
	* @return void
	* @expansion 目前实现的是所有需要授权操作的记录，是否需要记录非授权比如登陆页面，来记录哪些IP在访问后台页面？
	*/
	public function log($action_name='',$flag=1){
		$adminLog = M(C('LOG_TABLE_NAME'));
		$data = array('admin_id'=>':admin_id','action_time'=>':action_time','action'=>':action','ip'=>':ip','flag'=>':flag');
		$bind = array(':admin_id'=>array($_SESSION['admin_id'],\PDO::PARAM_INT),
					':action_time'=>array(time(),\PDO::PARAM_INT),
					':action'=>array($action_name,\PDO::PARAM_STR),
					':ip'=>array(ip2long(get_client_ip()),\PDO::PARAM_INT),
					':flag'=>array($flag,\PDO::PARAM_INT));
		$adminLog->data($data)->bind($bind)->add();
	}
	
	/* 
	-
	* 生成随机字符串做为卡密生成
	* 
	* @param type string 随机字符串集
	* @param length int 卡密的长度
	* 
	* @return 生成的随机字符串
	*/
	public function makeCode($type='alpha',$length) {
		$chars = array();
		switch($type){
			case 'alpha': //几个容易混淆的,UVIO不含在里面
				$chars=array('A','B','C','D','F','H','K','L','M','N','P','Q','S','T','W','X','Y','Z');
                break;
			case 'number':
				$chars=array('0','3','4','5','6','7','8','9');
                break;
			case 'alphanumber':
				$chars=array('A','B','C','D','F','H','K','L','M','N','P','Q','S','T','W','X','Y','Z','0','3','4','5','6','7','8','9');
			default:
		}
		//$chars=array('A','B','C','D','E','F','H','J','K','L','M','N','P','Q','R','S','T','U','V','W','X','Y','Z');
		$chars_len=count($chars)-1;
		shuffle($chars);
		$code='';
		for ($i=0; $i<$length; $i++) {
			$code .= $chars[mt_rand(0, $chars_len)];
		}
		return $code;
	}
}