<?php
/**
 * 注册采购商
 *
 *
 *
 * * @汉购网 (c) 2015-2018 ShopWWI Inc. (http://www.hangowa.com)
 * @license    http://www.shopwwi.c om
 * @link       交流群号：
 * @since      汉购网提供技术支持 授权请购买shopnc授权
 */


defined('ByShopWWI') or exit('Access Invalid!');

class member_purchaseControl extends BaseMemberControl{
    /**
     * 会员地址
     *
     * @param
     * @return
     */
    public function registerOp() {

        Language::read('member_purchase');
        $lang   = Language::getLangContent();

        $b2b_purchaser = Model('b2b_purchaser');

        /**
         * 判断页面类型
         */
        if (!empty($_GET['type'])){

            /**
             * 增加/修改页面输出
             */
//            if ($_SESSION['is_login'] !== '1'){
//                showMessage('请先登录',MEMBER_SITE_URL+'/index.php?act=login&inajax=1','html','error');
//                exit;
//            }

            Tpl::output('type',$_GET['type']);
            Tpl::showpage('member_purchase.register','null_layout');
            exit();
        }
        /**
         * 判断操作类型
         */
        if (chksubmit()){

            if ($_POST['city_id'] == '') {
                $_POST['city_id'] = $_POST['area_id'];
            }
            /**
             * 验证表单信息
             */
            $obj_validate = new Validate();
            $obj_validate->validateparam = array(
                array("input"=>$_POST["company_name"],"require"=>"true","message"=>'请填写公司名称'),
                array("input"=>$_POST["contact_name"],"require"=>"true","message"=>'请填写联系人'),
                array("input"=>$_POST["area_id"],"require"=>"true","validator"=>"Number","message"=>$lang['member_address_wrong_area']),
                array("input"=>$_POST["city_id"],"require"=>"true","validator"=>"Number","message"=>$lang['member_address_wrong_area']),
                array("input"=>$_POST["region"],"require"=>"true","message"=>$lang['member_address_area_null']),
                array("input"=>$_POST["address"],"require"=>"true","message"=>$lang['member_address_address_null']),
                array("input"=>$_POST['mob_phone'],'require'=>'true','message'=>$lang['member_address_phone_and_mobile'])
            );
            $error = $obj_validate->validate();
            if ($error != ''){
                showValidateError($error);
            }


            $data = array();
            $data['member_id'] = $_SESSION['member_id'];
            $data['company_name'] = $_POST['company_name'];
            $data['contact_name'] = $_POST['contact_name'];
            $data['area_id'] = intval($_POST['area_id']);
            $data['city_id'] = intval($_POST['city_id']);
            $data['area_info'] = $_POST['region'];
            $data['address'] = $_POST['address'];
            $data['mob_phone'] = $_POST['mob_phone'];

            if(!$b2b_purchaser->isPurchaser()){
                $rs = $b2b_purchaser->addPurchaser($data);
                if (!$rs){
                    showDialog($lang['member_address_add_fail'],'','error');
                }
            }
            showDialog($lang['nc_common_op_succ'],'reload','js');
        }

        self::profile_menu('address','address');
        Tpl::showpage('member_address.index');
    }

    public function addressOp() {

        Language::read('member_address');
        $lang   = Language::getLangContent();

        $address_class = Model('b2b_address');
        /**
         * 判断页面类型
         */
        if (!empty($_GET['type'])){
            /**
             * 新增/编辑地址页面
             */
            if (intval($_GET['id']) > 0){
                /**
                 * 得到地址信息
                 */
                $address_info = $address_class->getOneAddress(intval($_GET['id']));
                if ($address_info['member_id'] != $_SESSION['member_id']){
                    showMessage($lang['member_address_wrong_argument'],'index.php?act=member_purchase&op=address','html','error');
                }
                /**
                 * 输出地址信息
                 */
                Tpl::output('address_info',$address_info);
            }
            /**
             * 增加/修改页面输出
             */
            Tpl::output('type',$_GET['type']);
            Tpl::showpage('member_purchase.address','null_layout');
            exit();
        }
        /**
         * 判断操作类型
         */
        if (chksubmit()){
            if ($_POST['city_id'] == '') {
                $_POST['city_id'] = $_POST['area_id'];
            }
            /**
             * 验证表单信息
             */
            $obj_validate = new Validate();
            $obj_validate->validateparam = array(
                array("input"=>$_POST["true_name"],"require"=>"true","message"=>$lang['member_address_receiver_null']),
                array("input"=>$_POST["area_id"],"require"=>"true","validator"=>"Number","message"=>$lang['member_address_wrong_area']),
                array("input"=>$_POST["city_id"],"require"=>"true","validator"=>"Number","message"=>$lang['member_address_wrong_area']),
                array("input"=>$_POST["region"],"require"=>"true","message"=>$lang['member_address_area_null']),
                array("input"=>$_POST["address"],"require"=>"true","message"=>$lang['member_address_address_null']),
                array("input"=>$_POST['tel_phone'].$_POST['mob_phone'],'require'=>'true','message'=>$lang['member_address_phone_and_mobile'])
            );
            $error = $obj_validate->validate();
            if ($error != ''){
                showValidateError($error);
            }
            $data = array();
            $data['member_id'] = $_SESSION['member_id'];
            $data['true_name'] = $_POST['true_name'];
            $data['area_id'] = intval($_POST['area_id']);
            $data['city_id'] = intval($_POST['city_id']);
            $data['area_info'] = $_POST['region'];
            $data['address'] = $_POST['address'];
            $data['tel_phone'] = $_POST['tel_phone'];
            $data['mob_phone'] = $_POST['mob_phone'];
            $data['is_default'] = $_POST['is_default'] ? 1 : 0;
            if ($_POST['is_default']) {
                $address_class->editAddress(array('is_default'=>0),array('member_id'=>$_SESSION['member_id'],'is_default'=>1));
            }

            if (intval($_POST['id']) > 0){
                $rs = $address_class->editAddress($data, array('address_id' => intval($_POST['id']),'member_id'=>$_SESSION['member_id']));
                if (!$rs){
                    showDialog($lang['member_address_modify_fail'],'','error');
                }
            }else {
                $count = $address_class->getAddressCount(array('member_id'=>$_SESSION['member_id']));
                if ($count >= 20) {
                    showDialog('最多允许添加20个有效地址','','error');
                }
                $rs = $address_class->addAddress($data);
                if (!$rs){
                    showDialog($lang['member_address_add_fail'],'','error');
                }
            }
            showDialog($lang['nc_common_op_succ'],'reload','js');
        }

        $del_id = isset($_GET['id']) ? intval(trim($_GET['id'])) : 0 ;
        if ($del_id > 0){
            $rs = $address_class->delAddress(array('address_id'=>$del_id,'member_id'=>$_SESSION['member_id']));
            if ($rs){
                showDialog('删除地址成功','index.php?act=member_purchase&op=address','js');
            }else {
                showDialog('删除地址失败','','error');
            }
        }

        $address_list = $address_class->getAddressList(array('member_id'=>$_SESSION['member_id']));

        //采购商信息
        $condition = array();
        $condition['member_id'] = $_SESSION['member_id'];
        $purchaser_info = Model('b2b_purchaser')->getAddressInfo($condition);
        Tpl::output('purchaser_info', $purchaser_info);

        self::profile_menu('address','address');
        Tpl::output('address_list',$address_list);
        Tpl::showpage('member_purchase.index');
    }




    /**
     * 用户中心右边，小导航
     *
     * @param string    $menu_type  导航类型
     * @param string    $menu_key   当前导航的menu_key
     * @return
     */
    private function profile_menu($menu_type,$menu_key='') {
        /**
         * 读取语言包
         */
        Language::read('member_layout');
        $menu_array = array();
        switch ($menu_type) {
            case 'address':
                $menu_array = array(
                1=>array('menu_key'=>'address','menu_name'=>'地址列表',   'menu_url'=>'index.php?act=member_adderss&op=address'));
                break;
        }
        Tpl::output('member_menu',$menu_array);
        Tpl::output('menu_key',$menu_key);
    }
}
