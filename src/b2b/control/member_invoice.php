<?php
/**
 * 发票管理
 *
 *
 *
 * * @汉购网 (c) 2015-2018 ShopWWI Inc. (http://www.hangowa.com)
 * @license    http://www.shopwwi.c om
 * @link       交流群号：
 * @since      汉购网提供技术支持 授权请购买shopnc授权
 */


defined('ByShopWWI') or exit('Access Invalid!');

class member_invoiceControl extends BaseMemberControl{
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

                $rs = $b2b_purchaser->addPurchaser($data);
                if (!$rs){
                    showDialog($lang['member_address_add_fail'],'','error');
                }

            showDialog($lang['nc_common_op_succ'],'reload','js');
        }

        $condition = array();
        $invoice_list = Model('b2b_invoice')->getInvList($condition);
        Tpl::output('invoice_list', $invoice_list);

        self::profile_menu('address','address');
        Tpl::showpage('member_invoice.index');
    }

    public function addressOp() {

        Language::read('member_address');
        $lang   = Language::getLangContent();

        $address_class = Model('b2b_address');
        $invoice_class = Model('b2b_invoice');
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

                $condition = array();
                $condition['inv_id'] = intval($_GET['id']);
                $invoice_info = Model('b2b_invoice')->getInvInfo($condition);

//                if ($invoice_info['member_id'] != $_SESSION['member_id']){
//                    showMessage('发票参数错误','index.php?act=member_invoice&op=address','html','error');
//                }
                /**
                 * 输出发票信息
                 */
                Tpl::output('invoice_info',$invoice_info);
            }
            /**
             * 增加/修改页面输出
             */

            Tpl::output('type',$_GET['type']);
            Tpl::output('invoice_type',$_GET['invoice_type']);
            Tpl::showpage('member_invoice.address','null_layout');
            exit();
        }
        /**
         * 判断操作类型
         */
        if (chksubmit()){

            /**
             * 验证表单信息
             */
            $obj_validate = new Validate();
            $obj_validate->validateparam = array(
                array("input"=>$_POST["inv_content"],"require"=>"false","message"=>'请填写发票抬头')
            );
            $error = $obj_validate->validate();
            if ($error != ''){
                showValidateError($error);
            }
            $data = array();
            $data['member_id'] = $_SESSION['member_id'];
            $data['inv_title'] = $_POST['inv_title'];
            $data['inv_content'] = $_POST['inv_content'];
            $data['inv_company'] = $_POST['inv_company'];
            $data['inv_code'] = $_POST['inv_code'];
            $data['inv_reg_addr'] = $_POST['inv_reg_addr'];
            $data['inv_reg_phone'] = $_POST['inv_reg_phone'];
            $data['inv_reg_bname'] = $_POST['inv_reg_bname'];
            $data['inv_reg_baccount'] = $_POST['inv_reg_baccount'];
            $data['inv_rec_name'] = $_POST['inv_rec_name'];
            $data['inv_rec_mobphone'] = $_POST['inv_rec_mobphone'];
            $data['inv_rec_province'] = $_POST['inv_rec_province'];
            $data['inv_goto_addr'] = $_POST['inv_goto_addr'];
            if (intval($_POST['id']) > 0){
                $rs = $invoice_class->editInv($data,array('inv_id' => intval($_POST['id'])));
                if (!$rs){
                    showDialog('发票更新失败','','error');
                }
            }else {
                if(!empty($_POST['invoice_type'])){
                    $data['inv_state'] = $_POST['invoice_type'];
                }
                $count = $invoice_class->getInvCount(array('member_id'=>$_SESSION['member_id']));
                if ($count >= 20) {
                    showDialog('最多允许添加20个有效发票','','error');
                }
                $rs = $invoice_class->addInv($data);
                if (!$rs){
                    showDialog('发票添加失败','','error');
                }
            }
            showDialog($lang['nc_common_op_succ'],'reload','js');
        }
        $del_id = isset($_GET['id']) ? intval(trim($_GET['id'])) : 0 ;
        if ($del_id > 0){
            $rs = $invoice_class->delInv(array('inv_id'=>$del_id,'member_id'=>$_SESSION['member_id']));
            if ($rs){
                showDialog('发票删除成功','index.php?act=member_invoice&op=address','js');
            }else {
                showDialog('发票删除失败','','error');
            }
        }
        $address_list = $address_class->getAddressList(array('member_id'=>$_SESSION['member_id']));

        //采购商信息
        $condition = array();
        $condition['member_id'] = $_SESSION['member_id'];
        $purchaser_info = Model('b2b_purchaser')->getAddressInfo($condition);
        Tpl::output('purchaser_info', $purchaser_info);

        $condition = array();
//        $condition['member_id'] = $_SESSION['member_id'];
        $invoice_list = $invoice_class->getInvList($condition);

        foreach($invoice_list as $key => $value){
            if($value['inv_state'] == 1){
                $invoice_list[$key]['inv_state'] = '普通发票';
            } else if($value['inv_state'] == 2){
                $invoice_list[$key]['inv_state'] = '增值税发票';
            }
        }
        Tpl::output('invoice_list', $invoice_list);

        self::profile_menu('address','address');
        Tpl::showpage('member_invoice.index');
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
