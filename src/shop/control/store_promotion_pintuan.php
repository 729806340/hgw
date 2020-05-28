<?php
/**
 * 用户中心-拼团
 *
 *
 *
 * * @汉购网 (c) 2015-2018 ShopWWI Inc. (http://www.hangowa.com)
 * @license    http://www.shopwwi.c om
 * @link       交流群号：
 * @since      汉购网提供技术支持 授权请购买shopnc授权
 */


defined('ByShopWWI') or exit('Access Invalid!');
class store_promotion_pintuanControl extends BaseSellerControl {

    const LINK_PINTUAN_LIST = 'index.php?act=store_promotion_pintuan&op=index';
    const LINK_PINTUAN_MANAGE = 'index.php?act=store_promotion_pintuan&op=pintuan_manage&pintuan_id=';

    public function __construct() {
        parent::__construct() ;

        //读取语言包
        Language::read('member_layout,promotion_pintuan');
        //检查拼团是否开启
        if (intval(C('promotion_allow')) !== 1){
            showMessage(Language::get('promotion_unavailable'),'index.php?act=store','','error');
        }

    }

    public function indexOp() {
        $this->pintuan_listOp();
    }

    /**
     * 发布的拼团活动列表
     **/
    public function pintuan_listOp() {
        $model_pintuan = Model('p_pintuan');

        $condition = array();
        $condition['store_id'] = $_SESSION['store_id'];
        if(!empty($_GET['pintuan_name'])) {
            $condition['pintuan_name'] = array('like', '%'.$_GET['pintuan_name'].'%');
        }
        if(!empty($_GET['state'])) {
            $condition['state'] = intval($_GET['state']);
        }
        $pintuan_list = $model_pintuan->getPinTuanList($condition, 10, 'state desc, end_time desc');
        Tpl::output('list', $pintuan_list);
        Tpl::output('show_page', $model_pintuan->showpage());
        Tpl::output('pintuan_state_array', $model_pintuan->getPinTuanStateArray());

        self::profile_menu('pintuan_list');
        Tpl::showpage('store_promotion_pintuan.list');
    }

    /**
     * 添加拼团活动
     **/
    public function pintuan_addOp() {

        //输出导航
        self::profile_menu('pintuan_add');
        Tpl::showpage('store_promotion_pintuan.add');

    }

    public function pintuan_add_configOp() {
        $config_id = intval($_GET['config_id']);
        if (empty($config_id)) {
            showDialog('请选择拼团模板！');
        }
        $config_pintuan_info = Model('p_pintuan')->getPinTuanConfigInfo(array('config_id' => $config_id));
        if (empty($config_pintuan_info)) {
            showDialog('拼团模板已失效！');
        }

        //输出导航
        self::profile_menu('pintuan_add');
        Tpl::output('config_pintuan_info', $config_pintuan_info);
        Tpl::showpage('store_promotion_pintuan_config.add');

    }

    public function pintuan_list_configOp() {
        $model_pintuan = Model('p_pintuan');

        // 获取模板
        $condition = array();
        $condition['store_id'] = $_SESSION['store_id'];
        $condition['config_id'] = array('gt', 0);
        $pintuanlist = $model_pintuan->getPinTuanList($condition, 9999, 'state desc, end_time desc');
        $config_id = array_column($pintuanlist, 'config_id');
        $w = array();
        $w['config_start_time'] = array('gt', time());
        if (!empty($config_id)) {
            $w['config_id'] = array('not in', $config_id);
        }
        $pintuan_list = $model_pintuan->getPinTuanConfigList($w);
        // 输出导航
        self::profile_menu('pintuan_add_config');
        Tpl::output('list', $pintuan_list);
        Tpl::output('show_page', $model_pintuan->showpage());
        Tpl::showpage('store_promotion_pintuan_config.list');

    }

    /**
     * 保存添加的拼团活动
     **/
    public function pintuan_saveOp() {
        //验证输入
        $pintuan_name = trim($_POST['pintuan_name']);
        $start_time = strtotime($_POST['start_time']);
        $end_time = strtotime($_POST['end_time']);
        $limit_time = intval($_POST['limit_time']);
        $limit_user = intval($_POST['limit_user']);
        $minimum_user = intval($_POST['minimum_user']);
        $limit_floor = intval($_POST['limit_floor']);
        $limit_ceilling = intval($_POST['limit_ceilling']);
        $limit_total = intval($_POST['limit_total']);
        if($limit_floor <= 0) {
            $limit_floor = 1;
        }
        if(empty($pintuan_name)) {
            showDialog('拼团名字不能为空');
        }
        if($start_time >= $end_time) {
            showDialog(Language::get('greater_than_start_time'));
        }

        //生成活动
        $model_pintuan = Model('p_pintuan');
        $param = array();
        $param['pintuan_name'] = $pintuan_name;
        $param['pintuan_title'] = $_POST['pintuan_title'];
        $param['pintuan_description'] = $_POST['pintuan_description'];
        $param['start_time'] = $start_time;
        $param['end_time'] = $end_time;
        $param['store_id'] = $_SESSION['store_id'];
        $param['store_name'] = $_SESSION['store_name'];
        $param['member_id'] = $_SESSION['member_id'];
        $param['member_name'] = $_SESSION['member_name'];
        $param['limit_floor'] = $limit_floor;
        $param['limit_time'] = $limit_time * 3600;
        $param['limit_user'] = $limit_user;
        $param['minimum_user'] = $minimum_user;
        $param['limit_ceilling'] = $limit_ceilling;
        $param['limit_total'] = $limit_total;
        $param['config_id'] = $_POST['config_id'] ? $_POST['config_id'] : 0;
        $result = $model_pintuan->addPinTuan($param);
        if($result) {
            $this->recordSellerLog('添加拼团活动，活动名称：'.$pintuan_name.'，活动编号：'.$result);
            // 添加计划任务
            Model('cron')->addCron(array('exetime' => $param['end_time'], 'exeid' => $result, 'type' => 11), true);
            showDialog('拼团添加成功',self::LINK_PINTUAN_MANAGE.$result,'succ','',3);
        }else {
            showDialog('拼团添加失败');
        }
    }

    /**
     * 编辑拼团活动
     **/
    public function pintuan_editOp() {
        $model_pintuan = Model('p_pintuan');

        $pintuan_info = $model_pintuan->getPinTuanInfoByID($_GET['pintuan_id']);
        if(empty($pintuan_info) || !$pintuan_info['editable']) {
            showMessage('拼团过期或不存在','','','error');
        }

        Tpl::output('pintuan_info', $pintuan_info);

        //输出导航
        self::profile_menu('pintuan_edit');
        Tpl::showpage('store_promotion_pintuan.add');
    }

    /**
     * 编辑保存拼团活动
     **/
    public function pintuan_edit_saveOp() {
        $pintuan_id = $_POST['pintuan_id'];

        $model_pintuan = Model('p_pintuan');
        $model_pintuan_goods = Model('p_pintuan_goods');

        $pintuan_info = $model_pintuan->getpintuanInfoByID($pintuan_id, $_SESSION['store_id']);
        if(empty($pintuan_info) || !$pintuan_info['editable']) {
            showMessage('拼团过期或不存在','','','error');
        }

        //验证输入
        $pintuan_name = trim($_POST['pintuan_name']);
        $limit_floor = intval($_POST['$limit_floor']);
        if($limit_floor <= 0) {
            $limit_floor = 1;
        }
        if(empty($pintuan_name)) {
            showDialog('拼团名称不能为空');
        }

        //生成活动
        $param = array();
        $param['pintuan_name'] = $pintuan_name;
        $param['pintuan_title'] = $_POST['pintuan_title'];
        $param['pintuan_description'] = $_POST['pintuan_description'];
        $param['limit_floor'] = $limit_floor;
        $param['limit_time'] = $_POST['limit_time'] * 3600;
        $param['limit_user'] = $_POST['limit_user'];
        $param['minimum_user'] = $_POST['minimum_user'];
        $param['limit_ceilling'] = $_POST['limit_ceilling'];
        $param['limit_total'] = $_POST['limit_total'];
        $result = $model_pintuan->editPinTuan($param, array('pintuan_id'=>$pintuan_id));
        $result1 = $model_pintuan_goods->editPinTuanGoods($param, array('pintuan_id'=>$pintuan_id));
        if($result && $result1) {
            $this->recordSellerLog('编辑拼团活动，活动名称：'.$pintuan_name.'，活动编号：'.$pintuan_id);
            showDialog(Language::get('nc_common_op_succ'),self::LINK_PINTUAN_LIST,'succ','',3);
        }else {
            showDialog(Language::get('nc_common_op_fail'));
        }
    }

    /**
     * 拼团活动删除
     **/
    public function pintuan_delOp() {
        $pintuan_id = intval($_POST['pintuan_id']);

        $model_pintuan = Model('p_pintuan');

        $data = array();
        $data['result'] = true;

        $pintuan_info = $model_pintuan->getPinTuanInfoByID($pintuan_id, $_SESSION['store_id']);
        if(!$pintuan_info) {
            showDialog(L('param_error'));
        }

        $model_pintuan = Model('p_pintuan');
        $result = $model_pintuan->delPinTuan(array('pintuan_id'=>$pintuan_id));

        if($result) {
            $this->recordSellerLog('删除拼团活动，活动名称：'.$pintuan_info['pintuan_name'].'活动编号：'.$pintuan_id);
            showDialog(L('nc_common_op_succ'), urlShop('store_promotion_pintuan', 'index'), 'succ');
        } else {
            showDialog(L('nc_common_op_fail'));
        }
    }

    /**
     * 拼团活动管理
     **/
    public function pintuan_manageOp() {
        $model_pintuan = Model('p_pintuan');
        $model_pintuan_goods = Model('p_pintuan_goods');

        $pintuan_id = intval($_GET['pintuan_id']);
        $pintuan_info = $model_pintuan->getPinTuanInfoByID($pintuan_id, $_SESSION['store_id']);
        if(empty($pintuan_info)) {
            showDialog(L('param_error'));
        }
        Tpl::output('pintuan_info',$pintuan_info);

        //获取拼团商品列表
        $condition = array();
        $condition['pintuan_id'] = $pintuan_id;
        $pintuan_goods_list = $model_pintuan_goods->getPinTuanGoodsExtendList($condition, 10);
        Tpl::output('show_page', $model_pintuan_goods->showpage());
        Tpl::output('pintuan_goods_list', $pintuan_goods_list);

        //输出导航
        self::profile_menu('pintuan_manage');
        Tpl::showpage('store_promotion_pintuan.manage');
    }

    /**
     * 选择活动商品
     **/
    public function goods_selectOp() {
        $model_goods = Model('goods');
        $condition = array();
        $condition['store_id'] = $_SESSION['store_id'];
        $condition['goods_name'] = array('like', '%'.$_GET['goods_name'].'%');
        $goods_list = $model_goods->getGeneralGoodsOnlineList($condition, '*', 10);

        Tpl::output('goods_list', $goods_list);
        Tpl::output('show_page', $model_goods->showpage());
        Tpl::showpage('store_promotion_pintuan.goods', 'null_layout');
    }

    /**
     * 拼团商品添加
     **/
    public function pintuan_goods_addOp() {
        $goods_id = intval($_POST['goods_id']);
        $pintuan_id = intval($_POST['pintuan_id']);
        $pintuan_price = floatval($_POST['pintuan_price']);
        $pintuan_storage = $_POST['pintuan_storage'];
        $limit_floor = intval($_POST['limit_floor']);
        $limit_ceilling = intval($_POST['limit_ceilling']);
        $limit_total = intval($_POST['limit_total']);
        $limit_time = intval($_POST['limit_time']);
        $limit_user = intval($_POST['limit_user']);
        $minimum_user = intval($_POST['minimum_user']);

        $model_goods = Model('goods');
        $model_pintuan = Model('p_pintuan');
        $model_pintuan_goods = Model('p_pintuan_goods');

        $data = array();
        $data['result'] = true;

        $goods_info = $model_goods->getGoodsInfoByID($goods_id);
        if(empty($goods_info) || $goods_info['store_id'] != $_SESSION['store_id']) {
            $data['result'] = false;
            $data['message'] = L('param_error');
            echo json_encode($data);die;
        }

        $pintuan_info = $model_pintuan->getPinTuanInfoByID($pintuan_id, $_SESSION['store_id']);
        if(!$pintuan_info) {
            $data['result'] = false;
            $data['message'] = L('param_error');
            echo json_encode($data);die;
        }

        //检查商品是否已经参加同时段活动
        $condition = array();
        $condition['end_time'] = array('gt', $pintuan_info['start_time']);
        $condition['goods_id'] = $goods_id;
        $pintuan_goods = $model_pintuan_goods->getPinTuanGoodsExtendList($condition);
        if(!empty($pintuan_goods)) {
            $data['result'] = false;
            $data['message'] = '该商品已经参加了同时段活动';
            echo json_encode($data);die;
        }

        //添加到活动商品表
        $param = array();
        $param['pintuan_id'] = $pintuan_info['pintuan_id'];
        $param['pintuan_name'] = $pintuan_info['pintuan_name'];
        $param['pintuan_title'] = $pintuan_info['pintuan_title'];
        $param['pintuan_description'] = $pintuan_info['pintuan_description'];
        $param['goods_id'] = $goods_info['goods_id'];
        $param['store_id'] = $goods_info['store_id'];
        $param['goods_name'] = $goods_info['goods_name'];
        $param['goods_price'] = $goods_info['goods_price'];
        $param['pintuan_price'] = $pintuan_price;
        $param['pintuan_storage'] = $pintuan_storage;
        $param['limit_floor'] = $limit_floor;
        $param['limit_ceilling'] = $limit_ceilling;
        $param['limit_total'] = $limit_total;
        $param['limit_time'] = $limit_time*3600;
        $param['limit_user'] = $limit_user;
        $param['minimum_user'] = $minimum_user;
        $param['goods_image'] = $goods_info['goods_image'];
        $param['start_time'] = $pintuan_info['start_time'];
        $param['end_time'] = $pintuan_info['end_time'];
        $param['gc_id_1'] = $goods_info['gc_id_1'];

        $result = array();
        $pintuan_goods_info = $model_pintuan_goods->addPinTuanGoods($param);
        if($pintuan_goods_info) {
            $result['result'] = true;
            $data['message'] = '添加成功';
            $data['pintuan_goods'] = $pintuan_goods_info;
            // 自动发布动态
            // goods_id,store_id,goods_name,goods_image,goods_price,goods_freight,pintuan_price
            $data_array = array();
            $data_array['goods_id']         = $goods_info['goods_id'];
            $data_array['store_id']         = $_SESSION['store_id'];
            $data_array['goods_name']       = $goods_info['goods_name'];
            $data_array['goods_image']      = $goods_info['goods_image'];
            $data_array['goods_price']      = $goods_info['goods_price'];
            $data_array['goods_freight']    = $goods_info['goods_freight'];
            $data_array['pintuan_price']    = $pintuan_price;
            $this->storeAutoShare($data_array, 'pintuan');
            $this->recordSellerLog('添加拼团商品，活动名称：'.$pintuan_info['pintuan_name'].'，商品名称：'.$goods_info['goods_name']);

            // 添加任务计划
            Model('cron')->addCron(array('type' => 2, 'exeid' => $goods_info['goods_id'], 'exetime' => $param['start_time']));
        } else {
            $data['result'] = false;
            $data['message'] = L('param_error');
        }
        echo json_encode($data);die;
    }

    /**
     * 拼团商品价格修改
     **/
    public function pintuan_goods_price_editOp() {
        $pintuan_goods_id = intval($_POST['pintuan_goods_id']);
        $pintuan_price = floatval($_POST['pintuan_price']);
        $pintuan_storage = intval($_POST['pintuan_storage']);
        $limit_floor = intval($_POST['limit_floor']);
        $limit_ceilling = intval($_POST['limit_ceilling']);
        $limit_total = intval($_POST['limit_total']);
        $limit_time = intval($_POST['limit_time']);
        $limit_user = intval($_POST['limit_user']);
        $minimum_user = intval($_POST['minimum_user']);

        $data = array();
        $data['result'] = true;

        $model_pintuan_goods = Model('p_pintuan_goods');

        $pintuan_goods_info = $model_pintuan_goods->getPinTuanGoodsInfoByID($pintuan_goods_id, $_SESSION['store_id']);
        if(!$pintuan_goods_info) {
            $data['result'] = false;
            $data['message'] = L('param_error');
            echo json_encode($data);die;
        }

        $update = array();
        $update['pintuan_price'] = $pintuan_price;
        $update['pintuan_storage'] = $pintuan_storage;
        $update['limit_floor'] = $limit_floor;
        $update['limit_ceilling'] = $limit_ceilling;
        $update['limit_time'] = $limit_time*3600;
        $update['limit_user'] = $limit_user;
        $update['minimum_user'] = $minimum_user;
        $update['limit_total'] = $limit_total;
        $condition = array();
        $condition['pintuan_goods_id'] = $pintuan_goods_id;
        $result = $model_pintuan_goods->editPinTuanGoods($update, $condition);

        if($result) {
            $pintuan_goods_info['pintuan_price'] = $pintuan_price;
            $pintuan_goods_info = $model_pintuan_goods->getPinTuanGoodsExtendInfo($pintuan_goods_info);
            $data['pintuan_price'] = $pintuan_goods_info['pintuan_price'];
            $data['pintuan_discount'] = $pintuan_goods_info['pintuan_discount'];
            $data['pintuan_storage'] = $pintuan_storage;
            $data['limit_floor'] = $limit_floor;
            $data['limit_ceilling'] = $limit_ceilling;
            $data['limit_time'] = $limit_time;
            $data['limit_user'] = $limit_user;
            $data['minimum_user'] = $minimum_user;
            $data['limit_total'] = $limit_total;

            // 添加对列修改商品促销价格
//            QueueClient::push('updateGoodsPromotionPriceByGoodsId', $pintuan_goods_info['goods_id']);

            $this->recordSellerLog('拼团价格修改为：'.$pintuan_goods_info['pintuan_price'].'，商品名称：'.$pintuan_goods_info['goods_name']);
        } else {
            $data['result'] = false;
            $data['message'] = L('nc_common_op_succ');
        }
        echo json_encode($data);die;
    }

    /**
     * 拼团商品删除
     **/
    public function pintuan_goods_deleteOp() {
        $model_pintuan_goods = Model('p_pintuan_goods');
        $model_pintuan = Model('p_pintuan');

        $data = array();
        $data['result'] = true;

        $pintuan_goods_id = intval($_POST['pintuan_goods_id']);
        $pintuan_goods_info = $model_pintuan_goods->getPinTuanGoodsInfoByID($pintuan_goods_id);
        if(!$pintuan_goods_info) {
            $data['result'] = false;
            $data['message'] = L('param_error');
            echo json_encode($data);die;
        }

        $pintuan_info = $model_pintuan->getPinTuanInfoByID($pintuan_goods_info['pintuan_id'], $_SESSION['store_id']);
        if(!$pintuan_info) {
            $data['result'] = false;
            $data['message'] = L('param_error');
            echo json_encode($data);die;
        }

        if(!$model_pintuan_goods->delPinTuanGoods(array('pintuan_goods_id'=>$pintuan_goods_id))) {
            $data['result'] = false;
            $data['message'] = '拼团商品删除失败';
            echo json_encode($data);die;
        }

        // 添加对列修改商品促销价格
//        QueueClient::push('updateGoodsPromotionPriceByGoodsId', $pintuan_goods_info['goods_id']);

        $this->recordSellerLog('删除拼团商品，活动名称：'.$pintuan_info['pintuan_name'].'，商品名称：'.$pintuan_goods_info['goods_name']);
        echo json_encode($data);die;
    }

    /**
     * 用户中心右边，小导航
     *
     * @param string    $menu_type  导航类型
     * @param string    $menu_key   当前导航的menu_key
     * @param array     $array      附加菜单
     * @return
     */
    private function profile_menu($menu_key='') {
        $menu_array = array(
            1=>array('menu_key'=>'pintuan_list','menu_name'=>'活动列表','menu_url'=>'index.php?act=store_promotion_pintuan&op=index'),
//            2=>array('menu_key'=>'pintuan_add_config','menu_name'=>'活动模板','menu_url'=>'index.php?act=store_promotion_pintuan&op=pintuan_list_config'),
        );
        switch ($menu_key){
            case 'pintuan_add':
                $menu_array[] = array('menu_key'=>'pintuan_add','menu_name'=>'添加活动','menu_url'=>'index.php?act=store_promotion_pintuan&op=pintuan_add');
                break;
            case 'pintuan_edit':
                $menu_array[] = array('menu_key'=>'pintuan_edit','menu_name'=>'编辑活动','menu_url'=>'javascript:;');
                break;
            case 'pintuan_manage':
                $menu_array[] = array('menu_key'=>'pintuan_manage','menu_name'=>'商品管理','menu_url'=>'index.php?act=store_promotion_pintuan&op=pintuan_manage&pintuan_id='.$_GET['pintuan_id']);
                break;
        }
        Tpl::output('member_menu',$menu_array);
        Tpl::output('menu_key',$menu_key);
    }
}
