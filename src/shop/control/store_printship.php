<?php
/**
 * 电子面单
 * * @汉购网 (c) 2015-2018 ShopWWI Inc. (http://www.hangowa.com)
 * @license    http://www.shopwwi.c om
 * @link       交流群号：
 * @since      汉购网提供技术支持 授权请购买shopnc授权
 */



defined('ByShopWWI') or exit ('Access Invalid!');
class store_printshipControl extends BaseSellerControl{
    private  $kdn_config = array();

    public function __construct() {
        parent::__construct();
        $condition = array('store_id'=>$_SESSION['store_id']);
        $this->kdn_config = Model('kdn_config')->where($condition)->find();

    }

    public function indexOp(){
        $this->profile_menu('printship_list', 'index');
        Tpl::output('kdn_config' , $this->kdn_config);
        Tpl::showpage('store_printship.config');
    }

    public function setConfigOp(){
        $userId = intval($_POST['userId']);
        $userKey = trim($_POST['userKey']);
        $model = Model('kdn_config');
        if($this->kdn_config['store_id']){
            $update = array(
                'userId' =>$userId,
                'userKey'=>$userKey,
            );
            $condition = array('store_id'=>$_SESSION['store_id']);
            $res = $model->where($condition)->update($update);
        }else{
            $data = array(
                'store_id' => $_SESSION['store_id'],
                'userId'   => $userId,
                'userKey'  => $userKey
            );
            $res = $model->insert($data);
        }
        if($res){
            showDialog('操作成功','index.php?act=store_printship','succ');
        }
    }


    public function templateOp(){
        if(!$this->kdn_config['store_id']){
            showDialog('请先设置快递鸟接口信息','index.php?act=store_printship','error');
        }
        //获取可以申请电子面单的物流公司
        $express = Model('express')->getPushExpress();
        $express = array_under_reset($express , 'kdncode');
        $condition = array('store_id'=>$_SESSION['store_id']);
        $template_list = Model('print_ship')->getPrintShipList($condition , $fields = '*', $limit = null, $page = null, 'id desc');
        foreach($template_list as $item =>$value){
            $template_list[$item]['express_name'] = $express[$value['express_code']]['hgwname'];
        }
        Tpl::output('template_list',$template_list);
        Tpl::output('show_page', Model('print_ship') -> showpage());
        $this->profile_menu('printship_list', 'template');
        Tpl::showpage('store_printship.index');
    }

    public function printship_addOp(){
        if(!$this->kdn_config['store_id']){
            showDialog('请先设置快递鸟接口信息','index.php?act=store_printship','error');
        }
        //获取可以申请电子面单的物流公司
        $express = Model('express')->getPushExpress();
        Tpl::output('express',$express);
        $this->profile_menu('printship_list', 'printship_add');
        Tpl::showpage('store_printship.add');
    }

    public function addOp(){
        if(!$this->kdn_config['store_id']){
            showDialog('请先设置快递鸟接口信息','index.php?act=store_printship','error');
        }

        //校验
        $express = Model('express')->getPushExpress();
        $express = array_values(array_column($express, 'kdncode'));
        if (!in_array($_POST['express_code'], $express) || empty($_POST['template_name'])) {
            showDialog('非法请求', '', 'error');
        }

        if(!$_POST['id']) {
            $saveDate = array(
                'template_name' => trim($_POST['template_name']),
                'express_code' => $_POST['express_code'],
                'is_notify'   => $_POST['is_notify'],
                'store_id' => intval($_SESSION['store_id']),
                'region' => $_POST['region'],
                'area_id' => intval($_POST['area_id']),
                'address' => trim($_POST['address']),
                'sender' => trim($_POST['sender']),
                'mobile' => $_POST['mobile'],
                'shipcode' => $_POST['shipcode'],
                'add_time' => TIMESTAMP,
            );

            $res = Model('print_ship')->addPrintShip($saveDate);
            if (!$res) {
                showDialog('数据保存失败', '', 'error');
            }
            showDialog('模板添加成功', 'index.php?act=store_printship&op=template', 'succ');
        }else{
            $saveDate = array(
                'template_name' => trim($_POST['template_name']),
                'express_code' => $_POST['express_code'],
                'is_notify'   => $_POST['is_notify'],
                'region' => $_POST['region'],
                'area_id' => intval($_POST['area_id']),
                'address' => trim($_POST['address']),
                'sender' => trim($_POST['sender']),
                'mobile' => $_POST['mobile'],
                'shipcode' => $_POST['shipcode'],
            );
            $condition = array('id'=>$_POST['id']);
            $res = Model('print_ship')->editPrintShip($saveDate , $condition);
            if (!$res) {
                showDialog('数据保存失败', '', 'error');
            }
            showDialog('模板编辑成功', 'index.php?act=store_printship&op=template', 'succ');
        }
    }

    public function editTemplateOp(){
        $id = intval($_GET['id']);
        $model = Model('print_ship');
        $condition = array(
            'id'=>$id,
            'store_id'=>$_SESSION['store_id'],
        );
        $template_info = $model->getPrintShipInfo($condition);
        if(empty($template_info)){
            showDialog('非法信息','','error');
        }
        //获取可以申请电子面单的物流公司
        $express = Model('express')->getPushExpress();
        Tpl::output('express',$express);
        Tpl::output('template_info', $template_info);
        $this->profile_menu('printship_list', 'printship_add');
        Tpl::showpage('store_printship.add');
    }

    /**
     * 单个电子面单发货选择电子面单发货
     */
    public function selectTemplateOp(){
        $order_sn = $_GET['order_sn'];
        $model_order = model('order');
        $condition = array(
            'order_sn' => $order_sn,
            'store_id' => $_SESSION['store_id'],
        );
        $order_info = $model_order->getOrderInfo($condition);
        $can_print = $model_order->getOrderOperateState('print_ship', $order_info);
        if(!$can_print){
            showDialog('订单信息有误', '', 'error', empty($_GET['inajax']) ? '' : 'CUR_DIALOG.close();', 5);
        }
        //获取商家电子面单模板
        $condition = array('store_id'=>$_SESSION['store_id']);
        $template_list = Model('print_ship')->getPrintShipList($condition);
        $express = Model('express')->getPushExpress();
        $express = array_under_reset($express , 'kdncode');
        foreach($template_list as $item =>$value){
            $template_list[$item]['express_name'] = $express[$value['express_code']]['hgwname'];
        }

        Tpl::output('template_list', $template_list);
        Tpl::showpage('store_printship.select','null_layout');
    }

    /***
     * 单个订单电子面单发货
     */
    public function pushOneOp(){
        $ret = array();
        $template_id = intval($_POST['template_id']);
        $order_sn = trim($_POST['order_sn']);
        $condition = array(
            'id'=> $template_id,
            'store_id'   =>$_SESSION['store_id'],
        );
        $template_info = Model('print_ship')->getPrintShipInfo($condition);
        if(empty($template_info)){

        }
        $condition= array(
            'order_sn'=>$order_sn,
            'store_id'=>$_SESSION['store_id']
        );
        $order_info = Model('order')-> getOrderInfo($condition , array('order_common','order_goods'));

        $can_print  = Model('order')->getOrderOperateState('print_ship', $order_info);
        if(!$can_print){
            $ret['state'] = 'error';
            $ret['msg']  = '电子面单模板信息错误';
            return $ret;
        }
        $res = Model('print_ship')->setPrintShipLog($order_info,$template_info);
        $res = JSON($res);
        return $res;
    }



    /**
     * 用户中心右边，小导航
     *
     * @param string    $menu_type  导航类型
     * @param string    $menu_key   当前导航的menu_key
     * @param array     $array      附加菜单
     * @return
     */
    private function profile_menu($menu_type,$menu_key='',$array=array()) {
        $menu_array = array();
        switch ($menu_type) {
            case 'printship_list':
                $menu_array = array(
                    array('menu_key' =>'index', 'menu_name'=>'参数配置' , 'menu_url'=>urlShop('store_printship','index')),
                    array('menu_key' => 'template', 'menu_name' => '模板列表', 'menu_url' => urlShop('store_printship', 'template')),
                    array('menu_key' => 'printship_add', 'menu_name' => '添加模板', 'menu_url' => urlShop('store_printship', 'printship_add'))
                );
                break;
        }
        if(!empty($array)) {
            $menu_array[] = $array;
        }
        Tpl::output('member_menu',$menu_array);
        Tpl::output('menu_key',$menu_key);
    }
}