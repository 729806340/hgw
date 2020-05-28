<?php
/**
 * 我的地址
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

class member_addressControl extends mobileMemberControl {

    public function __construct() {
        parent::__construct();
    }

    /**
     * 地址列表
     */
    public function address_listOp() {
        $model_address = Model('address');
        $fields = 'address_id,true_name,area_id,city_id,area_info,address,mob_phone,is_default';
        $address_list = $model_address->getAddressList(array('member_id'=>$this->member_info['member_id']),$fields);
        output_data(array('address_list' => $address_list));
    }

    /**
     * 地址详细信息
     */
    public function address_infoOp() {
        //$_POST = array('address_id'=>13742);
        $address_id = intval($_POST['address_id']);
        $condition = array();
        if(empty($address_id)){
            // 获取默认地址ID
            $condition['is_default'] = 1;
        }else{
            $condition['address_id'] = $address_id;
        }

        /** @var addressModel $model_address */
        $model_address = Model('address');
        $address_info = $model_address->getAddressInfo($condition);
        if(!empty($address_info) && $address_info['member_id'] == $this->member_info['member_id']) {
            output_data(array('address_info' => $address_info,'error'=>0));
        } else {
            output_data(array('error'=>1));
            //output_error('地址不存在');
        }
    }

    /**
     * 删除地址
     */
    public function address_delOp() {
        //$_POST = array('address_id'=>13734);
        $address_id = intval($_POST['address_id']);

        $model_address = Model('address');

        $condition = array();
        $condition['address_id'] = $address_id;
        $condition['member_id'] = $this->member_info['member_id'];
        $model_address->delAddress($condition);
        output_data('1');
    }

    /**
     * 新增地址
     */
    public function address_addOp() {
        /** @var addressModel $model_address */
        $model_address = Model('address');
        $address_info = $this->_address_valid();
        $result = $model_address->addAddress($address_info);
        if($result) {
            output_data(array('address_id' => $result));
        } else {
            output_error('保存失败');
        }
    }

    /**
     * 编辑地址
     */
    public function address_editOp() {
//         $_POST = array(
//             'address_id'=>13734,
//             'true_name'=>'李景泉',
//             'area_id'=>2816,
//             'city_id'=>258,
//             'area_info'=>'湖北 武汉市 江汉区',
//             'address'=>'名流公馆301-502',
//             'mob_phone'=>13517237062,
//             'is_default'=>1,
//         );
        $address_id = intval($_POST['address_id']);

        if(empty($address_id)) return $this->address_addOp();

        /** @var addressModel $model_address */
        $model_address = Model('address');

        //验证地址是否为本人
        $address_info = $model_address->getOneAddress($address_id);
        if ($address_info['member_id'] != $this->member_info['member_id']) {
            output_error('非法操作');
        }

        $address_info = $this->_address_valid();
        $result = $model_address->editAddress($address_info, array('address_id' => $address_id));
        if($result) {
            output_data('1');
        } else {
            output_error('保存失败');
        }
    }

    /**
     * 验证地址数据
     */
    private function _address_valid() {
        $obj_validate = new Validate();
        $obj_validate->validateparam = array(
            array("input"=>$_POST["true_name"],"require"=>"true","message"=>'姓名不能为空'),
            array("input"=>$_POST["area_info"],"require"=>"true","message"=>'地区不能为空'),
            array("input"=>$_POST["address"],"require"=>"true","message"=>'地址不能为空'),
            array("input"=>$_POST['mob_phone'],'require'=>'true', "validator"=>"mobile",'message'=>'请输入正确的手机号码')
        );
        $error = $obj_validate->validate();
        if ($error != ''){
            output_error($error);
        }

        $data = array();
        $data['member_id'] = $this->member_info['member_id'];
        $data['true_name'] = $_POST['true_name'];
        $data['area_id'] = intval($_POST['area_id']);
        $data['city_id'] = intval($_POST['city_id']);
        $data['area_info'] = $_POST['area_info'];
        $data['address'] = $_POST['address'];
        $data['tel_phone'] = $_POST['tel_phone'];
        $data['mob_phone'] = $_POST['mob_phone'];
		$data['is_default'] = $_POST['is_default'];
		//只能有一个默认地址
		if($data['is_default']==1){
		    $condition = array();
		    $condition['member_id'] = $this->member_info['member_id'];
		    Model('address')->editAddress(array('is_default'=>0), $condition);
		}
        return $data;
    }

    /**
     * 地区列表
     */
    public function area_listOp() {
        $area_id = intval($_POST['area_id']);

        $model_area = Model('area');

        $condition = array();
        if($area_id > 0) {
            $condition['area_parent_id'] = $area_id;
        } else {
            $condition['area_deep'] = 1;
        }
        $area_list = $model_area->getAreaList($condition, 'area_id,area_name');
        output_data(array('area_list' => $area_list));
    }

}
