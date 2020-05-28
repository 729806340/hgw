<?php
/**
 * 我的红包
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

class member_redpacketControl extends mobileMemberControl {

    public function __construct(){
        parent::__construct();
		//判断系统是否开启红包功能
        if (C('redpacket_allow') != 1){
            showDialog('系统未开启红包功能',urlShop('member', 'home'),'error');
        }
        $model_redpacket = Model('redpacket');
        $this->redpacket_state_arr = $model_redpacket->getRedpacketState();
    }
	
	
	/**
     * 红包列表
     */
    public function redpacket_listOp(){
        $condition = array();
        $model_redpacket = Model('redpacket');
        //更新红包过期状态
        $model_redpacket->updateRedpacketExpire($this->member_info['member_id']);
        //查询红包
        $where = array();
        $where['rpacket_owner_id'] = $this->member_info['member_id'];
        $rp_state_select = trim($_GET['rp_state_select']);
        if ($rp_state_select){
            $where['rpacket_state'] = $this->redpacket_state_arr[$rp_state_select]['sign'];
        }

        $list = $model_redpacket->getRedpacketList($where, '*', 0, 10, 'rpacket_id desc');
		foreach($list as $key=>$value){
			$list[$key]['rpacket_end_date_text'] =  date('Y-m-d H:i:s',$value['rpacket_end_date']);
		}
		$page_count = $model_redpacket->gettotalpage();		
		output_data(array('redpacket_list' => $list),mobile_page($page_count));  


    }
    /**
     * 领取红包
     */
    public function rp_pwexOp() {
		if($this->member_info['member_id']){
			if(!$this->check()){
				output_error('验证码错误！');
			}
            $obj_validate = new Validate();
            $obj_validate->validateparam = array(
                array("input" => $_POST["pwd_code"],"require" => "true","message" => '请输入红包卡密'),
            );
            $error = $obj_validate->validate();
            if ($error != ''){
                output_error($error);
            }
            //查询红包
            $model_redpacket = Model('redpacket');
            $where = array();
            $where['rpacket_pwd'] = md5($_POST["pwd_code"]);
            $redpacket_info = $model_redpacket->getRedpacketInfo($where);
            if(!$redpacket_info){
                output_error('红包卡密错误');
            }
            if($redpacket_info['rpacket_owner_id'] > 0){
                output_error('该红包卡密已被使用，不可重复领取');
            }

            //查找该类型红包用户领取次数
            $where = array();
            $where['rpacket_owner_id'] = $this->member_info['member_id'];
            $where['rpacket_t_id'] = $redpacket_info['rpacket_t_id'];
            $fetched_count = $model_redpacket->getRedpacketCount($where);

            //与模板限领数量进行验证
            $info = $model_redpacket->getRptTemplateInfo(array('rpacket_t_id'=>$redpacket_info['rpacket_t_id']), 'rpacket_t_eachlimit');

            if ($fetched_count >= $info['rpacket_t_eachlimit']&&$info['rpacket_t_eachlimit']>0) {
                output_error('该类型红包每人限领' . $info['rpacket_t_eachlimit'] . '张');
            }

            $where = array();
            $where['rpacket_id'] = $redpacket_info['rpacket_id'];
            $update_arr = array();
            $update_arr['rpacket_owner_id'] = $_SESSION['member_id'];
            $update_arr['rpacket_owner_name'] = $_SESSION['member_name'];
            $update_arr['rpacket_active_date'] = time();
            $result = $model_redpacket->editRedpacket($where, $update_arr, $_SESSION['member_id']);
            if($result){
                //更新红包模板
                $update_arr = array();
                $update_arr['rpacket_t_giveout'] = array('exp','rpacket_t_giveout+1');
                $model_redpacket->editRptTemplate(array('rpacket_t_id'=>$redpacket_info['rpacket_t_id']),$update_arr);
                output_data('红包领取成功');
            } else {
                output_error('红包领取失败');
            }
        }else{
			output_error('请登录！');
		}       
    }

    public function rp_getOp() {
        if($this->member_info['member_id']){
            //查询红包
            /** @var redpacketModel $model_redpacket */
            $model_redpacket = Model('redpacket');
            $rpacket_t_id = intval($_POST["rpacket_t_id"]);

            //查找该类型红包用户领取次数
            $where = array();
            $where['rpacket_owner_id'] = $this->member_info['member_id'];
            $where['rpacket_t_id'] = $rpacket_t_id;
            $fetched_count = $model_redpacket->getRedpacketCount($where);

            //与模板限领数量进行验证
            $info = $model_redpacket->getRptTemplateInfo(array('rpacket_t_id'=>$rpacket_t_id), 'rpacket_t_eachlimit');

            if ($fetched_count >= $info['rpacket_t_eachlimit']&&$info['rpacket_t_eachlimit']>0) {
                output_error('该类型红包每人限领' . $info['rpacket_t_eachlimit'] . '张');
            }


            //验证是否可领取红包
            $data = $model_redpacket->getCanChangeTemplateInfo($rpacket_t_id, $this->member_info['member_id']);
            if ($data['state'] == false){
                output_error($data['msg']);
            }
            try {
                $model_redpacket->beginTransaction();
                //添加红包信息
                $data = $model_redpacket->exchangeRedpacket($data['info'],$this->member_info['member_id'], $this->member_info['member_name']);
                if ($data['state'] == false) {
                    throw new Exception($data['msg']);
                }
                $model_redpacket->commit();
                output_data('红包领取成功');
            } catch (Exception $e) {
                $model_redpacket->rollback();
                output_error($e->getMessage(). 23);
            }

        }else{
            output_error('请登录！');
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
