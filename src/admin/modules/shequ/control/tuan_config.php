<?php
/**
 * 社区团购管理
 *
 * @汉购网提供技术支持 授权请购买shopnc授权
 * @license    http://www.hangowa.com
 * @link       交流群号：
 */
defined('ByShopWWI') or exit('Access Invalid!');
class tuan_configControl extends SystemControl{

    public function __construct(){
        parent::__construct();
    }

    /**
     * 默认Op
     */
    public function indexOp() {
        $this->config_tuan_listOp();
    }

    /**
     * 社区团购活动列表
     */
    public function config_tuan_listOp()
    {
        $this->show_menu('config_tuan_list');
        Tpl::setDirquna('shequ');
        Tpl::showpage('tuan_config.list');
    }

    public function config_tuan_addOp()
    {
        if (chksubmit()) {
            $config_xianshi_name = trim($_POST['config_xianshi_name']);
            $config_xianshi_title = trim($_POST['config_xianshi_title']);
            $config_xianshi_explain = trim($_POST['article_content']);
            $config_start_time = strtotime($_POST['query_start_date']);
            $config_end_time = strtotime($_POST['query_end_date']);
            $send_product_date = strtotime($_POST['send_product_date']);
            $type = intval($_POST['type']);
            $config_pic = '';
            $config_pic_er = '';
            if (!empty($_FILES['member_logo']['name'])){
                $upload = new UploadFile();
                $upload->set('default_dir', ATTACH_COMMON);
                $result = $upload->upfile('member_logo');
                if ($result) {
                    $config_pic = $upload->file_name;
                } else {
                    showMessage($upload->error,'','','error');
                }
            } else {
                showMessage('请上传海报');
            }
            if (!empty($_FILES['member_logo_er']['name'])){
                $upload = new UploadFile();
                $upload->set('default_dir', ATTACH_COMMON);
                $result = $upload->upfile('member_logo_er');
                if ($result) {
                    $config_pic_er = $upload->file_name;
                } else {
                    showMessage($upload->error,'','','error');
                }
            } else {
                showMessage('请上传海报');
            }

            if(empty($config_xianshi_name)) {
                showMessage('活动名称不能为空！');
            }
            if($config_start_time >= $config_end_time) {
                showMessage('开始时间不能大于结束时间！');
            }
            if (!$config_xianshi_explain) {
                showMessage('描述不能为空！');
            }
            if ($config_start_time >= $send_product_date) {
                showMessage('开始时间不能大于发货时间！');
            }

            //生成活动
            /** @var shequ_tuan_configModel $model_tuan_config */
            $model_tuan_config = Model('shequ_tuan_config');
//            $repeat_time = $model_tuan_config->getTuanConfigInfo(array('config_start_time'=>$config_start_time,'config_end_time'=>$config_end_time));
            $repeat_time_list = $model_tuan_config->getTuanConfigList();
            $config_end_time_arr = array_column($repeat_time_list,'config_end_time');
            $min_config_end_time = max($config_end_time_arr);
            if($config_start_time<$min_config_end_time) {
                showMessage('同一时间段团购活动不能重复！');
            }
            $param = array();
            $param['config_tuan_name'] = $config_xianshi_name;
            $param['config_tuan_title'] = $config_xianshi_title;
            $param['config_tuan_description'] = $config_xianshi_explain;
            $param['config_start_time'] = $config_start_time;
            $param['config_end_time'] = $config_end_time;
            $param['send_product_date'] = $send_product_date;
            $param['config_pic'] = $config_pic;
            $param['config_pic_er'] = $config_pic_er;
            $param['type'] = 0;
            $result = $model_tuan_config->addTuanConfig($param);
            if ($result) {
                // 添加计划任务
                showMessage('新增成功！', 'index.php?act=tuan_config&op=config_tuan_list');
            } else {
                showMessage('新增失败！');
            }
        }
        Tpl::setDirquna('shequ');
        Tpl::showpage('tuan_config.add');
    }
    public function copyOp()
    {
        /** @var shequ_tuan_configModel $model */
        $model = Model('shequ_tuan_config');
        $config_xianshi_id = intval($_GET['config_tuan_id']);
        $config_xianshi_info = $model->getTuanConfigInfo(array('config_tuan_id' => $config_xianshi_id));
        if(empty($config_xianshi_info)) {
            showMessage(L('param_error'));
        }
        Tpl::output('config_xianshi_info', $config_xianshi_info);
        if (chksubmit()) {
            $config_xianshi_name = trim($_POST['config_xianshi_name']);
            $config_xianshi_title = trim($_POST['config_xianshi_title']);
            $config_xianshi_explain = trim($_POST['article_content']);
            $config_start_time = strtotime($_POST['query_start_date']);
            $config_end_time = strtotime($_POST['query_end_date']);
            $send_product_date = strtotime($_POST['send_product_date']);
            $type = intval($_POST['type']);
            $config_pic = '';
            $config_pic_er = '';
            if (!empty($_FILES['member_logo']['name'])){
                $upload = new UploadFile();
                $upload->set('default_dir', ATTACH_COMMON);
                $result = $upload->upfile('member_logo');
                if ($result) {
                    $config_pic = $upload->file_name;
                } else {
                    $config_pic = $config_xianshi_info['config_pic'];
                }
            } else {
                $config_pic = $config_xianshi_info['config_pic'];
            }
            if (!empty($_FILES['member_logo_er']['name'])){
                $upload = new UploadFile();
                $upload->set('default_dir', ATTACH_COMMON);
                $result = $upload->upfile('member_logo_er');
                if ($result) {
                    $config_pic_er = $upload->file_name;
                } else {
                    $config_pic_er = $config_xianshi_info['config_pic_er'];
                }
            } else {
                $config_pic_er = $config_xianshi_info['config_pic_er'];
            }

            if(empty($config_xianshi_name)) {
                showMessage('活动名称不能为空！');
            }
            if($config_start_time >= $config_end_time) {
                showMessage('开始时间不能大于结束时间！');
            }
            if (!$config_xianshi_explain) {
                showMessage('描述不能为空！');
            }
            if ($config_start_time >= $send_product_date) {
                showMessage('开始时间不能大于发货时间！');
            }
            //生成活动
            /** @var shequ_tuan_configModel $model_tuan_config */
            $model_tuan_config = Model('shequ_tuan_config');
            $model_tuan_config->beginTransaction();
            $param = array();
            $param['config_tuan_name'] = $config_xianshi_name;
            $param['config_tuan_title'] = $config_xianshi_title;
            $param['config_tuan_description'] = $config_xianshi_explain;
            $param['config_start_time'] = $config_start_time;
            $param['config_end_time'] = $config_end_time;
            $param['send_product_date'] = $send_product_date;
            $param['config_pic'] = $config_pic;
            $param['config_pic_er'] = $config_pic_er;
            $param['type'] = 0;
            $result = $model_tuan_config->addTuanConfig($param);
            if (!$result){
                $model_tuan_config->rollback();
                showMessage('新增失败！');
            }

            // 增加商品信息

            /** @var shequ_config_goods_classModel $shequ_config_goods_classModel */
            $shequ_config_goods_classModel = Model('shequ_config_goods_class');

            /** @var shequ_tuan_config_goodsModel $tuan_config_goods_model */
            $tuan_config_goods_model = Model('shequ_tuan_config_goods');
            $goodsList = $tuan_config_goods_model->getTuanConfigGoodsList(array('tuan_config_id'=>$config_xianshi_id,'state'=>1));
            foreach ($goodsList as $goods){
                $goods['tuan_config_id'] = $result;
                unset($goods['tuan_config_goods_id']);
                $tuan_config_goods_model->addTuanConfigGoods($goods);
            }
            $configClassList = $shequ_config_goods_classModel->getItems(array('tuan_config_id'=>$config_xianshi_id,'state'=>1));
            $classIds = array_column($configClassList,'gc_id');
            if ($classIds){
                /** @var shequ_goods_classModel $shequ_goods_classModel */
                $shequ_goods_classModel = Model('shequ_goods_class');
                $configClassList = $shequ_goods_classModel->getGoodsClassList(array('gc_id'=>array('in',$classIds)));
                foreach ($configClassList as $gc){
                    $gc['tuan_config_id']= $result;
                    $gc['state']= 1;
                    $res = $shequ_config_goods_classModel->insert($gc);
                }
            }
            $model_tuan_config->commit();

            showMessage('新增成功！', 'index.php?act=tuan_config&op=config_tuan_list');

        }
        Tpl::setDirquna('shequ');
        Tpl::showpage('tuan_config.copy');
    }

    /**
     * 平台活动列表
     */
    public function config_tuan_list_xmlOp()
    {
        $condition = array();

        if ($_REQUEST['advanced']) {
            if (strlen($q = trim((string) $_REQUEST['config_xianshi_name']))) {
                $condition['config_tuan_name'] = array('like', '%' . $q . '%');
            }

            $pdates = array();
            if (strlen($q = trim((string) $_REQUEST['pdate1'])) && ($q = strtotime($q . ' 00:00:00'))) {
                $pdates[] = "config_end_time >= {$q}";
            }
            if (strlen($q = trim((string) $_REQUEST['pdate2'])) && ($q = strtotime($q . ' 00:00:00'))) {
                $pdates[] = "config_start_time <= {$q}";
            }
            if ($pdates) {
                $condition['pdates'] = array(
                    'exp',
                    implode(' or ', $pdates),
                );
            }

        } else {
            if (strlen($q = trim($_REQUEST['query']))) {
                switch ($_REQUEST['qtype']) {
                    case 'config_tuan_name':
                        $condition['config_tuan_name'] = array('like', '%'.$q.'%');
                        break;
                }
            }
        }

        /** @var shequ_tuan_configModel $model_tuan_config */
        $model_tuan_config = Model('shequ_tuan_config');

        $config_list = (array) $model_tuan_config->getTuanConfigList($condition, $_REQUEST['rp'], 'config_start_time desc');
        $data = array();
        $data['now_page'] = $model_tuan_config->shownowpage();
        $data['total_num'] = $model_tuan_config->gettotalnum();

        foreach ($config_list as $val) {
            if($val['config_start_time']<time()){
                $o = '';
                $o .= '<span class="btn"><em><i class="fa fa-cog"></i>设置<i class="arrow"></i></em><ul>';
                $o .= '<li><a class="confirm-on-click" href="' . "index.php?act=tuan_config&op=config_tuan_detail&config_tuan_id={$val['config_tuan_id']}" . '">活动详细</a></li>';
                $o .= '<li><a class="confirm-on-click" href="' . "index.php?act=tuan_config&op=config_tuan_goods&config_tuan_id={$val['config_tuan_id']}" . '">查看活动下的商品</a></li>';
                $o .= '<li><a class="confirm-on-click" href="' . "index.php?act=tuan_config&op=copy&config_tuan_id={$val['config_tuan_id']}" . '">复制活动</a></li>';
                $o .= '<li><a class="confirm-on-click" href="' . "index.php?act=tuan_config&op=seckill_list&config_tuan_id={$val['config_tuan_id']}" . '">秒杀活动</a></li>';
                $o .= '<li><a class="confirm-on-click" href="' . "index.php?act=tuan_config&op=tihuodan&config_tuan_id={$val['config_tuan_id']}" . '">提货单</a></li>';
                $o .= '<li><a class="confirm-on-click" href="' . "index.php?act=tuan_config&op=tuanzhang_list&config_tuan_id={$val['config_tuan_id']}" . '">团长列表</a></li>';
                $o .= '<li><a class="confirm-on-click" href="' . "index.php?act=tuan_config&op=config_goods_class&config_tuan_id={$val['config_tuan_id']}" . '">分类列表</a></li>';
                $o .= '</ul></span>';
            }else{
                $o = '';
                $o .= '<span class="btn"><em><i class="fa fa-cog"></i>设置<i class="arrow"></i></em><ul>';
                $o .= '<li><a class="confirm-on-click" href="' . "index.php?act=tuan_config&op=config_tuan_detail&config_tuan_id={$val['config_tuan_id']}" . '">活动详细</a></li>';
                $o .= '<li><a class="confirm-on-click" href="' . "index.php?act=tuan_config&op=config_add_goods&config_tuan_id={$val['config_tuan_id']}" . '">添加活动商品</a></li>';
                $o .= '<li><a class="confirm-on-click" href="' . "index.php?act=tuan_config&op=config_tuan_goods&config_tuan_id={$val['config_tuan_id']}" . '">查看活动下的商品</a></li>';
                $o .= '<li><a class="confirm-on-click" href="' . "index.php?act=tuan_config&op=copy&config_tuan_id={$val['config_tuan_id']}" . '">复制活动</a></li>';
                $o .= '<li><a class="confirm-on-click" href="' . "index.php?act=tuan_config&op=seckill_list&config_tuan_id={$val['config_tuan_id']}" . '">秒杀活动</a></li>';
                $o .= '<li><a class="confirm-on-click" href="' . "index.php?act=tuan_config&op=tihuodan&config_tuan_id={$val['config_tuan_id']}" . '">提货单</a></li>';
                $o .= '<li><a class="confirm-on-click" href="' . "index.php?act=tuan_config&op=tuanzhang_list&config_tuan_id={$val['config_tuan_id']}" . '">团长列表</a></li>';
                $o .= '<li><a class="confirm-on-click" href="' . "index.php?act=tuan_config&op=config_goods_class&config_tuan_id={$val['config_tuan_id']}" . '">分类列表</a></li>';
                $o .= '</ul></span>';
            }

            $i = array();
            $i['operation'] = $o;
            $i['config_xianshi_id'] = $val['config_tuan_id'];
            $i['config_xianshi_name'] = $val['config_tuan_name'];
            $i['config_start_time'] = date('Y-m-d H:i', $val['config_start_time']);
            $i['config_end_time'] = date('Y-m-d H:i', $val['config_end_time']);
            $i['send_product_date'] = $val['send_product_date']  ? date('Y-m-d H:i', $val['send_product_date']) : '';
            $i['config_type'] = !$val['type'] ? '' : ($val['type'] == 1 ? '物流' : '自提');
            $data['list'][$val['config_tuan_id']] = $i;
        }

        echo Tpl::flexigridXML($data);
        exit;
    }

    /*
     * 活动分类列表
     * */
    public function config_goods_classOp(){
        $where['tuan_config_id']= intval($_GET['config_tuan_id']);
        /** @var shequ_config_goods_classModel $model_class */
        $model_class = Model('config_goods_class');
        $class_list = $model_class->getConfigGoodsClassList($where);
        Tpl::output('class_list',$class_list);
        Tpl::setDirquna('shequ');//网 店 运 维shop wwi.com
        Tpl::showpage('config_goods_class');

    }

    /**
     * 活动编辑分类
     */
    public function goods_class_editOp(){
        $lang   = Language::getLangContent();
        /** @var config_goods_classModel $model_class */
        $model_class = Model('config_goods_class');
        if (chksubmit()){
            $obj_validate = new Validate();
            $obj_validate->validateparam = array(
                array("input"=>$_POST["gc_name"], "require"=>"true", "message"=>$lang['goods_class_add_name_null']),
                array("input"=>$_POST["gc_sort"], "require"=>"true", 'validator'=>'Number', "message"=>$lang['goods_class_add_sort_int']),
            );
            $error = $obj_validate->validate();
            if ($error != ''){
                showMessage($error);
            }
            // 更新分类信息
            $where = array('config_gc_id' => intval($_GET['config_gc_id']));
            $update_array = array();
            $update_array['gc_name']        = $_POST['gc_name'];
            $update_array['gc_sort']        = intval($_POST['gc_sort']);
            $update_array['gc_parent_id']      = 0;
            //$update_array['type_name'] = intval($_POST['wuliu_type'])=='1'?'自提':(intval($_POST['wuliu_type'])=='0'?'物流':'');

            //上传图片
            if (!empty($_FILES['app_img']['name'])){
                $upload = new UploadFile();
                $upload->set('default_dir',ATTACH_COMMON);
                $result = $upload->upfile('app_img');
                if ($result){
                    $update_array['app_img'] = $upload->file_name;
                }else {
                    showMessage($upload->error,'','','error');
                }
            }
            $result = $model_class->editConfigGoods( $where,$update_array);
            if (!$result){
                showMessage($lang['goods_class_batch_edit_fail']);
            }else{
                showMessage('修改成功!','index.php?act=tuan_config&op=config_goods_class&config_tuan_id='.$_GET['tuan_config_id']);
            }
        }
        // 一级分类列表
        $where = array('config_gc_id' => intval($_GET['config_gc_id']));
        $class_array = $model_class->getConfigGoodsInfoClass($where);
        Tpl::output('class_array',$class_array);
        Tpl::setDirquna('shequ');
        Tpl::showpage('config_goods_class.edit');
    }

    /**
     * ajax操作
     */
    public function ajax_configOp(){
        switch ($_GET['branch']){
            /**
             * 更新分类
             */
            case 'gc_name':
                $model_class = Model('shequ_goods_class');
                $class_array = $model_class->getGoodsClassInfoById(intval($_GET['id']));
                $condition['gc_name'] = trim($_GET['value']);
                $condition['gc_parent_id'] = $class_array['gc_parent_id'];
                $condition['config_gc_id'] = array('neq', intval($_GET['id']));
                /** @var config_goods_classModel $model_class_config */
                $model_class_config = Model('config_goods_class');
                $class_list = $model_class_config->getConfigGoodsClassList($condition);
                if (empty($class_list)){
                    $where = array('config_gc_id' => intval($_GET['id']));
                    $update_array = array();
                    $update_array['gc_name'] = trim($_GET['value']);
                    $model_class_config->editConfigGoods($where,$update_array);
                    $return = true;
                }else {
                    $return = false;
                }
                exit(json_encode(array('result'=>$return)));
                break;
            /**
             * 分类 排序 显示 设置
             */
            case 'gc_sort':
                $model_class_config = Model('config_goods_class');
                $where = array('config_gc_id' => intval($_GET['id']));
                $update_array = array();
                $update_array['gc_sort'] = $_GET['value'];
                $model_class_config->editConfigGoods($where,$update_array);
                $return = 'true';
                exit(json_encode(array('result'=>$return)));
                break;
            /**
             * 添加、修改操作中 检测类别名称是否有重复
             */
            case 'check_class_name':
                $model_class_config = Model('config_goods_class');
                $condition['gc_name'] = trim($_GET['gc_name']);
                $condition['gc_parent_id'] = intval($_GET['gc_parent_id']);
                $condition['config_gc_id'] = array('neq', intval($_GET['id']));
                $class_list = $model_class_config->getConfigGoodsClassList($condition);
                if (empty($class_list)){
                    echo 'true';exit;
                }else {
                    echo 'false';exit;
                }
                break;
            case 'check_tax':
                $tax = trim($_GET['tax']);
                $valid = array(
                    '0.000', '6.000', '13.000', '17.000', '10.000', '16.000'
                );
                if( in_array($tax, $valid) ) {
                    echo 'true';exit;
                }else {
                    echo 'false';exit;
                }
                break;
        }
    }


    public function tihuodanOp(){

        $config_xianshi_id = intval($_GET['config_tuan_id']);
        $config_xianshi_info = Model('shequ_tuan_config')->getTuanConfigInfo(array('config_tuan_id' => $config_xianshi_id));
        if(empty($config_xianshi_info)) {
            showMessage(L('param_error'));
        }
        Tpl::output('config_xianshi_info', $config_xianshi_info);
        /** @var shequ_tihuodanModel $tihuodanModel */
        $tihuodanModel = Model('shequ_tihuodan');
        $items = $tihuodanModel->getList(array('tuan_config_id'=>$config_xianshi_id));
        $tihuodan = array();
        if (count($items) > 0)
        foreach ($items as $item){
            $supplier_number = $item['supplier_number'];
            if(!isset($tihuodan[$supplier_number])){
                $tihuodan[$supplier_number] = array(
                    'items'=>array(),
                    'supplier_number'=>$supplier_number,
                    'supplier'=>$item['supplier'],
                );
            }
            $tihuodan[$supplier_number]['items'][] = $item;
        }
        Tpl::output('tihuodan', $tihuodan);
        Tpl::setDirquna('shequ');
        Tpl::showpage('tuan_config.tihuodan');
    }

    public function tihuodan_printOp(){

        $config_xianshi_id = intval($_GET['config_tuan_id']);
        $config_xianshi_info = Model('shequ_tuan_config')->getTuanConfigInfo(array('config_tuan_id' => $config_xianshi_id));
        if(empty($config_xianshi_info)) {
            showMessage(L('param_error'));
        }
        Tpl::output('config_xianshi_info', $config_xianshi_info);
        /** @var shequ_tihuodanModel $tihuodanModel */
        $tihuodanModel = Model('shequ_tihuodan');
        $items = $tihuodanModel->getList(array('tuan_config_id'=>$config_xianshi_id));
        $tihuodan = array();
        if (count($items) > 0)
        foreach ($items as $item){
            $supplier_number = $item['supplier_number'];
            if(!isset($tihuodan[$supplier_number])){
                $tihuodan[$supplier_number] = array(
                    'items'=>array(),
                    'supplier_number'=>$supplier_number,
                    'supplier'=>$item['supplier'],
                );
            }
            $tihuodan[$supplier_number]['items'][] = $item;
        }
        Tpl::output('tihuodan', $tihuodan);
        Tpl::setDirquna('shequ');
        Tpl::showpage('tuan_config.tihuodan_print');
    }

    /*
     * 获取商品列表
     */
    public function get_goods_listOp()
    {
        $where = array(
            'goods_storage' => array('gt', 0),
        );
        if (strlen($goods_name = trim($_REQUEST['goods_name']))) {
            $where['goods_name'] = array('like', "%$goods_name%");
        }
        if (strlen($goods_id = trim($_REQUEST['goods_id']))) {
            $where['goods_id'] = $goods_id;
        }
        $where['goods_state'] = 1;
        $where['goods_verify'] = 1;
        $page = $_GET['page'];

        /** @var goodsModel $goodsModel */
        $goodsModel = Model('goods');

        $list = $goodsModel->getGoodsList($where, '*','','',50);
        if ($list){

            $storeIds = array_unique(array_column($list,'store_id'));
            /** @var storeModel $storeModel */
            $storeModel = Model('store');
            $stores = $storeModel->getStoreList(array('store_id'=>array('in',$storeIds)));
            $stores = array_column($stores,null,'store_id');
            foreach ($list as $k=>$goods){
                $store = $stores[$goods['store_id']];
                $goods['tuan_type'] = $store['is_shequ_tuan'];
                $list[$k] = $goods;
            }
        }
        $total = ceil($goodsModel->gettotalnum()/10);
        echo json_encode(array('total'=>$total,'items'=>$list));
        exit;
    }


    public function config_tuan_detailOp() {
        $config_xianshi_id = intval($_GET['config_tuan_id']);
        $config_xianshi_info = Model('shequ_tuan_config')->getTuanConfigInfo(array('config_tuan_id' => $config_xianshi_id));
        if(empty($config_xianshi_info)) {
            showMessage(L('param_error'));
        }
        Tpl::output('config_xianshi_info', $config_xianshi_info);
        Tpl::setDirquna('shequ');
        Tpl::showpage('tuan_config.detail');
    }

    /**
     * 活动下的商品
     */
    public function config_tuan_goodsOp() {
		Tpl::setDirquna('shequ');
        Tpl::showpage('tuan_config.goods');
    }

    /**
     * 添加活动商品
     */
    public function config_add_goodsOp() {

        $config_tuan_id = intval($_GET['config_tuan_id']);
        /** @var shequ_tuan_configModel $tuan_config_model */
        $tuan_config_model = Model('shequ_tuan_config');
        $tuan_config_info = $tuan_config_model->getTuanConfigInfo(array('config_tuan_id' => $config_tuan_id));
        $condition['tuan_config_id'] = $config_tuan_id;
        $condition['state'] = 1;
        /** @var shequ_tuan_config_goodsModel $tuan_config_goods_model */
        $tuan_config_goods_model = Model('shequ_tuan_config_goods');
        $list = $tuan_config_goods_model->getTuanConfigGoodsList($condition, null, 'tuan_config_goods_id desc');

        Tpl::output('show_page', $tuan_config_goods_model->showpage());
        $return_arr = array();
        /** @var shequ_goods_classModel $goodsClassModel */
        $goodsClassModel = Model("shequ_goods_class");
        $goodsClasses1 = $goodsClassModel->getGoodsClassList(array('type_id'=>1));
        $goodsClasses2 = $goodsClassModel->getGoodsClassList(array('type_id'=>0));
        Tpl::output('goodsClasses1', $goodsClasses1);
        Tpl::output('goodsClasses2', $goodsClasses2);
        Tpl::output('goods_list', $list);
        Tpl::output('selected_sku', implode(',',array_column($list,'goods_id')));
        Tpl::output('tuan_config_id', $config_tuan_id);
        Tpl::setDirquna('shequ');
        Tpl::showpage('tuan_config.goods_add');
    }
    public function config_add_goods_saveOp() {

        $config_tuan_id = intval($_POST['config_id']);
        $data = $_POST['data'];
        /** @var shequ_tuan_configModel $tuan_config_model */
        $tuan_config_model = Model('shequ_tuan_config');
        /** @var shequ_config_goods_classModel $shequ_config_goods_classModel */
        $shequ_config_goods_classModel = Model('shequ_config_goods_class');
        /** @var shequ_goods_classModel $shequ_goods_classModel */
        $shequ_goods_classModel = Model('shequ_goods_class');

        $tuan_config_info = $tuan_config_model->getTuanConfigInfo(array('config_tuan_id' => $config_tuan_id));
        if (empty($tuan_config_info)){
            die(json_encode(array('code'=>400,'msg'=>'活动不存在')));
        }
        $skuIds = array_column($data,'goods_id');
        if (empty($skuIds))
            die(json_encode(array('code'=>400,'msg'=>'提交数据错误')));
        // 删除不存在的
        $condition['tuan_config_id'] = $config_tuan_id;
        /** @var shequ_tuan_config_goodsModel $tuan_config_goods_model */
        $tuan_config_goods_model = Model('shequ_tuan_config_goods');
        $tuan_config_goods_model->beginTransaction();
        $res = $tuan_config_goods_model->edit(
            array('tuan_config_id'=>$config_tuan_id,'goods_id'=>array('not in',$skuIds)),
            array('state'=>0)
        );
        if($res === false){
            $tuan_config_goods_model->rollback();
            die(json_encode(array('code'=>400,'msg'=>'更新失败')));
        }
        /** @var jdy_mappingModel $goodsMappingModel */
        $goodsMappingModel = Model('jdy_mapping');
        $goodsMappingList = $goodsMappingModel->getList(array('goods_id'=>array('in',$skuIds)));
        $goodsMappingList = array_column($goodsMappingList,null,'goods_id');
        // 循环添加或更新数据
        foreach ($data as $sku){
            if (!isset($goodsMappingList[$sku['goods_id']])){
                die(json_encode(array('code'=>400,'msg'=>"【{$sku['goods_name']}】没有映射精斗云商品和供应商")));
            }
            $goodsMapping = $goodsMappingList[$sku['goods_id']];
            $configGoods = $tuan_config_goods_model->getTuanConfigGoodsInfo(array('tuan_config_id'=>$config_tuan_id,'goods_id'=>$sku['goods_id']));
            if ($configGoods){
                $res = $tuan_config_goods_model->edit(
                    array('tuan_config_goods_id'=>$configGoods['tuan_config_goods_id']),
                    array('state'=>1,'gc_id'=>$sku['gc_id'],'type'=>$sku['type'],'commis'=>$sku['commis'])
                );
                if($res === false){
                    $tuan_config_goods_model->rollback();
                    die(json_encode(array('code'=>400,'msg'=>'更新失败')));
                }
            }else{
                $res = $tuan_config_goods_model->addTuanConfigGoods(
                    array('state'=>1,
                        'gc_id'=>$sku['gc_id'],
                        'type'=>$sku['type'],
                        'commis'=>$sku['commis'],
                        'tuan_config_id'=>$config_tuan_id,
                        'goods_id'=>$sku['goods_id'],
                        'goods_name'=>$sku['goods_name'],
                        'store_id'=>$sku['store_id'],
                        'goods_image'=>$sku['goods_image'],
                        'item_id'=>$goodsMapping['item_id'],
                        'item_code'=>$goodsMapping['item_code'],
                        'item_name'=>$goodsMapping['item_name'],
                        'warehouse_code'=>$goodsMapping['warehouse_code'],
                        'warehouse_name'=>$goodsMapping['warehouse_name'],
                        'supplier_number'=>$goodsMapping['supplier_number'],
                        'supplier'=>$goodsMapping['supplier'],
                        'unit_multiple'=>$goodsMapping['unit_multiple'],
                        'unit_no'=>$goodsMapping['unit_no'],
                        'unit_name'=>$goodsMapping['unit_name'],
                    )
                );
                if($res === false){
                    $tuan_config_goods_model->rollback();
                    die(json_encode(array('code'=>400,'msg'=>'更新失败')));
                }
            }
        }
        // 处理分类信息
        $classIds = array_unique(array_column($data,'gc_id'));
        // 删除不存在的
        $res = $shequ_config_goods_classModel->edit(
            array('tuan_config_id'=>$config_tuan_id,'gc_id'=>array('not in',$classIds)),
            array('state'=>0)
        );
        if($res === false){
            $tuan_config_goods_model->rollback();
            die(json_encode(array('code'=>400,'msg'=>'更新失败')));
        }
        foreach ($classIds as $gc_id){
            $goodsClass = $shequ_config_goods_classModel->getGoodsClassInfo(array('gc_id'=>$gc_id,'tuan_config_id'=>$config_tuan_id));
            if ($goodsClass){
                $res = $shequ_config_goods_classModel->edit(
                    array('config_gc_id'=>$goodsClass['config_gc_id']),
                    array('state'=>1)
                );
                if($res === false){
                    $tuan_config_goods_model->rollback();
                    die(json_encode(array('code'=>400,'msg'=>'更新失败')));
                }
            }else{
                $gc = $shequ_goods_classModel->getGoodsClassInfo(array('gc_id'=>$gc_id));
                $gc['tuan_config_id']= $config_tuan_id;
                $gc['state']= 1;
                $res = $shequ_config_goods_classModel->insert($gc);
                if($res === false){
                    $tuan_config_goods_model->rollback();
                    die(json_encode(array('code'=>400,'msg'=>'更新失败')));
                }
            }

        }
        $tuan_config_goods_model->commit();
        die(json_encode(array('code'=>0,'data'=>'添加成功')));
    }

    /**
     * 活动下的商品
     */
    public function config_tuan_goods_xmlOp() {
        $config_tuan_id = intval($_GET['config_tuan_id']);
        /** @var shequ_tuan_configModel $tuan_config_model */
        $tuan_config_model = Model('shequ_tuan_config');
        $tuan_config_info = $tuan_config_model->getTuanConfigInfo(array('config_tuan_id' => $config_tuan_id));
        $condition['tuan_config_id'] = $config_tuan_id;
        /** @var shequ_tuan_config_goodsModel $tuan_config_goods_model */
        $tuan_config_goods_model = Model('shequ_tuan_config_goods');
        /** @var shequ_config_goods_classModel $category_model */
        $category_model = Model('shequ_config_goods_class');
        $list = $tuan_config_goods_model->getTuanConfigGoodsList($condition, null, 'tuan_config_goods_id desc');
        $goods_ids = array_column($list, 'goods_id');
        $return_goods_rate_list = array();
        $return_goods_list = array();
        if (!empty($goods_ids)) {
            /** @var goodsModel $goods_model */
            $goods_model = Model('goods');
            $return_goods_list = $goods_model->getGoodsList(array('goods_id' => array('in', $goods_ids)));
            $return_goods_list = array_under_reset($return_goods_list, 'goods_id');
            /** @var shequ_return_goodsModel $return_goods_model */
            $return_goods_model = Model('shequ_return_goods');
            $return_goods_rate_list = $return_goods_model->getReturnGoodsList(array('return_goods_id' => array('in', $goods_ids)));
            $return_goods_rate_list = array_under_reset($return_goods_rate_list, 'return_goods_id');
        }

        $gcIds = array_column($list,'gc_id');

        $cateList = $category_model->getItems(array('gc_id'=>array('in',$gcIds)));
        $cateList = array_column($cateList,null,'gc_id');

        $data = array();
        $data['now_page'] = $tuan_config_goods_model->shownowpage();
        $data['total_num'] = $tuan_config_goods_model->gettotalnum();
        foreach ($list as $val) {
            $cate = $cateList[$val['gc_id']];
            $i = array();
            $i['tuan_config_goods_id']  = $val['tuan_config_goods_id'];
            $i['xianshi_name'] = $tuan_config_info['config_tuan_name'];
            $i['goods_name'] = $val['goods_name'];
            $i['goods_price'] = $return_goods_list[$val['goods_id']]['goods_price'];
            $i['return_price'] = ncPriceFormat($val['commis'] * $i['goods_price']/100);
            $i['type'] = $val['type']?'自提':'物流';
            $i['gc_name'] = $cate['gc_name'];
            $data['list'][$val['tuan_config_goods_id']] = $i;
        }
        echo Tpl::flexigridXML($data);
        exit;
    }


    /**
     * ajax修改团购信息
     */
    public function ajaxOp(){
        $result = true;
        $update_array = array();
        $where_array = array();

        switch ($_GET['branch']){
         case 'recommend':
            $model= Model('p_xianshi_goods');
            $update_array['xianshi_recommend'] = $_GET['value'];
            $where_array['xianshi_goods_id'] = $_GET['id'];
            $result = $model->editXianshiGoods($update_array, $where_array);
            break;
        }

        if($result) {
            echo 'true';exit;
        } else {
            echo 'false';exit;
        }
    }

    public function goods_selectOp() {
        $tuan_config_id = intval($_GET['tuan_config_id']);
        /** @var shequ_tuan_config_goodsModel $model_tuan_config_goods */
        $model_tuan_config_goods = Model('shequ_tuan_config_goods');
        $config_goods_list = $model_tuan_config_goods->getTuanConfigGoodsList(array('tuan_config_id' => $tuan_config_id));
        $config_goods_ids = array_column($config_goods_list, 'goods_id');
        /** @var shequ_return_goodsModel $model_return_goods */
        $model_return_goods = Model('shequ_return_goods');
        $return_goods_list = $model_return_goods->getReturnGoodsList(array('return_goods_id' => array('not in', $config_goods_ids)),0,'', 'return_goods_id, return_money_rate');
        $goods_ids = array_column($return_goods_list, 'return_goods_id');
        /** @var goodsModel $model_goods */
        $model_goods = Model('goods');
        $condition = array();
        $condition['goods_name'] = array('like', '%'.$_GET['goods_name'].'%');
        if (empty($goods_ids)) {
            $condition['goods_id'] = array('lt', 0);
        } else {
            $condition['goods_id'] = array('in', $goods_ids);
        }
        $return_goods_list = array_under_reset($return_goods_list, 'return_goods_id');

        /** @var shequ_tuan_configModel $shequ_tuan_config_model */
        $shequ_tuan_config_model = Model('shequ_tuan_config');
        $config_info = $shequ_tuan_config_model->getTuanConfigInfo(array('config_tuan_id' => $tuan_config_id));

        if ($config_info['type'] == 2) {
            /** @var storeModel $store_model */
            $store_model = Model('store');
            $store_ids = $store_model->getStoreList(array('is_shequ_tuan' => 1));
            if (!empty($store_ids)) {
                $condition['store_id'] = array('in', array_column($store_ids, 'store_id'));
            } else {
                $condition['store_id'] = -1;
            }
        } else {
            //限制商品类型
            /** @var storeModel $store_model */
            $store_model = Model('store');
            $store_ids = $store_model->getStoreList(array('is_shequ_tuan' => 1));
            if (!empty($store_ids)) {
                $condition['store_id'] = array('not in', array_column($store_ids, 'store_id'));
            }
        }

        $goods_list = $model_goods->getGeneralGoodsOnlineList($condition, '*', 10);
        foreach ($goods_list as $goods_key=>$goods) {
            $goods_list[$goods_key]['goods_return_price'] = 0;
            if (isset($return_goods_list[$goods['goods_id']])) {
                $goods_list[$goods_key]['goods_return_price'] = $return_goods_list[$goods['goods_id']]['return_money_rate'] * $goods['goods_price'];
            }
        }
        Tpl::output('goods_list', $goods_list);
        Tpl::output('tuan_config_id', $tuan_config_id);
        Tpl::output('show_page', $model_goods->showpage());
        Tpl::setDirquna('shequ');
        Tpl::showpage('tuan_config.goods_add_list', 'null_layout');
    }

    public function save_tuan_config_goodsOp() {
        $goods_id = intval($_GET['goods_id']);
        $tuan_config_id = intval($_GET['tuan_config_id']);
        /** @var shequ_return_goodsModel $model_return_goods */
        $model_return_goods = Model('shequ_return_goods');
        $return_goods_info = $model_return_goods->getReturnGoodsInfo(array('return_goods_id' => $goods_id));
        if (!$return_goods_info) {
            showMessage('该商品还不能分销');
        }
        //检测改团里是否存在
        /** @var shequ_tuan_config_goodsModel $model_tuan_config_goods */
        $model_tuan_config_goods = Model('shequ_tuan_config_goods');
        $exist_info = $model_tuan_config_goods->getTuanConfigGoodsInfo(array('tuan_config_id' => $tuan_config_id, 'goods_id' => $goods_id));
        if ($exist_info) {
            showMessage('已经添加过了');
        }
        /** @var goodsModel $model_goods */
        $model_goods = Model('goods');
        $goods_info = $model_goods->getGoodsInfo(array('goods_id' => $goods_id));
        $insert_data = array(
            'tuan_config_id' => $tuan_config_id,
            'goods_id' => $goods_info['goods_id'],
            'store_id' => $goods_info['store_id'],
            'goods_name' => $goods_info['goods_name'],
            'goods_image' => $goods_info['goods_image'],
            'gc_id' => $goods_info['gc_id'],
        );
        $model_tuan_config_goods->addTuanConfigGoods($insert_data);
        showMessage('成功！');
    }


    /**
     * 秒杀活动列表
     */
    public function seckill_listOp(){
        Tpl::setDirquna('shequ');
        Tpl::showpage('tuan_seckill');
    }

    /**
     * 秒杀活动
     */
    public function tuan_seckill_xmlOp() {
        $tuan_config_id = intval($_GET['config_tuan_id']);
        $condition = array();
        if ($_REQUEST['advanced']) {
            if (strlen($q = trim((string) $_REQUEST['goods_name']))) {
                $condition['xianshi_name'] = array('like', '%' . $q . '%');
            }
            $pdates = array();
            if (strlen($q = trim((string) $_REQUEST['pdate1'])) && ($q = strtotime($q . ' 00:00:00'))) {
                $pdates[] = "end_time >= {$q}";
            }
            if (strlen($q = trim((string) $_REQUEST['pdate2'])) && ($q = strtotime($q . ' 00:00:00'))) {
                $pdates[] = "start_time <= {$q}";
            }
            if ($pdates) {
                $condition['pdates'] = array(
                    'exp',
                    implode(' or ', $pdates),
                );
            }

        } else {

            if (strlen($q = trim($_REQUEST['query']))) {

                switch ($_REQUEST['qtype']) {
                    case 'xianshi_name':
                        $condition['xianshi_name'] = array('like', '%'.$q.'%');
                        break;
                }
            }
        }
        $condition['tuan_config_id'] = $tuan_config_id;
        /** @var shequ_xianshiModel $model_xianshi */
        $model_seckill__config = Model('shequ_xianshi');
        $config_list = (array) $model_seckill__config->getXianShiConfigList($condition, $_REQUEST['rp'], 'end_time desc');
        /*  echo '<pre>';
          print_r($config_list);exit;*/
        $data = array();
        $data['now_page'] = $model_seckill__config->shownowpage();
        $data['total_num'] = $model_seckill__config->gettotalnum();
        foreach ($config_list as $val) {
            $o = '';
            if($val['state']==1){
                $val['state'] = '正常';
                $o .= '<span class="btn"><em><i class="fa fa-cog"></i>设置<i class="arrow"></i></em><ul>';
                $o .= '<li><a class="confirm-on-click" href="' . "index.php?act=tuan_config&op=tuan_seckill_detail&xianshi_id={$val['xianshi_id']}" . '">活动详细</a></li>';
                if($val['start_time']>time()){
                    $o .= '<li><a class="confirm-on-click" href="' . "index.php?act=tuan_config&op=add_tuan_seckill_goods&config_tuan_id={$val['tuan_config_id']}&xianshi_id={$val['xianshi_id']}" . '">添加活动商品</a></li>';
                }
                $o .= '<li><a class="confirm-on-click" href="' . "index.php?act=tuan_config&op=tuan_seckill_goods&config_tuan_id={$val['tuan_config_id']}&xianshi_id={$val['xianshi_id']}" . '">查看活动下的商品</a></li>';
                $o .= '<li><a class="confirm-on-click" href="' . "index.php?act=tuan_config&op=tuan_seckill_goods_edit&xianshi_id={$val['xianshi_id']}&tuan_config_id={$val['tuan_config_id']}&state=1" . '">下架活动</a></li>';
            }elseif ($val['state']==0){
                $val['state'] = '失效';
                $o .= '<span class="btn"><em><i class="fa fa-cog"></i>设置<i class="arrow"></i></em><ul>';
                $o .= '<li><a class="confirm-on-click" href="' . "index.php?act=tuan_config&op=tuan_seckill_detail&xianshi_id={$val['xianshi_id']}" . '">活动详细</a></li>';
                if($val['start_time']>time()){
                    $o .= '<li><a class="confirm-on-click" href="' . "index.php?act=tuan_config&op=add_tuan_seckill_goods&config_tuan_id={$val['tuan_config_id']}&xianshi_id={$val['xianshi_id']}" . '">添加活动商品</a></li>';
                }
                $o .= '<li><a class="confirm-on-click" href="' . "index.php?act=tuan_config&op=tuan_seckill_goods&config_tuan_id={$val['tuan_config_id']}&xianshi_id={$val['xianshi_id']}" . '">查看活动下的商品</a></li>';
                $o .= '<li><a class="confirm-on-click" href="' . "index.php?act=tuan_config&op=tuan_seckill_goods_edit&xianshi_id={$val['xianshi_id']}&tuan_config_id={$val['tuan_config_id']}&state=0" . '">上架活动</a></li>';
            }
            $o .= '</ul></span>';
            $i = array();
            $i['operation'] = $o;
            $i['tuan_config_goods_id']  = $val['xianshi_id'];
            $i['goods_name'] = $val['xianshi_name'];
            $i['goods_title'] = $val['xianshi_title'];
            $i['lower_limit'] =($val['lower_limit']==1)?'不限':$val['lower_limit'];
            $i['state'] =$val['state'];
            $i['start_time_text'] = date("Y-m-d H:i:s", $val['start_time']);
            $i['end_time_text'] = date("Y-m-d H:i:s", $val['end_time']);
            $data['list'][$val['xianshi_id']] = $i;
        }
        echo Tpl::flexigridXML($data);
        exit;
    }

    /**
     * 添加秒杀活动
     */
    public function tuan_seckill_addOp()
    {

        if (chksubmit()) {
            $tuan_config_id = intval($_GET['tuan_config_id']);
            $xianshi_name = trim($_POST['config_xianshi_name']);
            $xianshi_title = trim($_POST['config_xianshi_title']);
            $xianshi_explain = trim($_POST['article_content']);
            $start_time = strtotime($_POST['query_start_date']);
            $end_time = strtotime($_POST['query_end_date']);
            $lower_limit = intval($_POST['config_xianshi_lower_limit']);
            if(empty($xianshi_name)) {
                showMessage('活动名称不能为空！');
            }

            if($start_time < time()) {
                showMessage('开始时间小于当前时间！');
            }
            if($start_time >= $end_time) {
                showMessage('开始时间不能大于结束时间！');
            }
            if (!$xianshi_explain) {
                showMessage('描述不能为空！');
            }
            //生成活动
            /** @var shequ_xianshiModel $model_xianshi */
            $model_shequ_xianshi = Model('shequ_xianshi');
            $where['tuan_config_id'] = $tuan_config_id;
            $where['state'] = 1;
            $repeat_time_list = $model_shequ_xianshi->getXianShiConfigList($where);
            $end_time_arr = array_column($repeat_time_list,'end_time');
            $min_end_time = max($end_time_arr);
            if($start_time<$min_end_time) {
                showMessage('同一时间段秒杀活动不能重复！');
            }
            $param = array();
            $param['xianshi_name'] = $xianshi_name;
            $param['xianshi_title'] = $xianshi_title;
            $param['xianshi_explain'] = $xianshi_explain;
            $param['start_time'] = $start_time;
            $param['end_time'] = $end_time;
            $param['lower_limit'] = $lower_limit;
            $param['state'] = 1;
            $param['tuan_config_id'] = $tuan_config_id;
            $param['member_name'] = '';
            $param['store_name'] = '';
            $result = $model_shequ_xianshi->addXianShiGoods($param);
            if ($result) {
                // 添加计划任务
                showMessage('新增成功！', 'index.php?act=tuan_config&op=seckill_list&config_tuan_id='.$tuan_config_id);
            } else {
                showMessage('新增失败！');
            }
        }
        Tpl::output('tuan_config_id', $tuan_config_id);
        Tpl::setDirquna('shequ');
        Tpl::showpage('tuan_seckill_add');
    }

    /**
     * 秒杀活动详情
     */
    public function tuan_seckill_detailOp() {
        $xianshi_id = intval($_GET['xianshi_id']);
        $good_xianshi_info = Model('shequ_xianshi')->getXianShiGoodsInfo(array('xianshi_id' => $xianshi_id));
        if(empty($good_xianshi_info)) {
            showMessage(L('param_error'));
        }
        Tpl::output('config_xianshi_info', $good_xianshi_info);
        Tpl::setDirquna('shequ');
        Tpl::showpage('tuan_seckill_detail');
    }

    /**
     * 下架秒杀活动
     */

      public function tuan_seckill_goods_editOp() {
          $xianshi_id = intval($_GET['xianshi_id']);
          $tuan_config_id = intval($_GET['tuan_config_id']);
          $state = intval($_GET['state']);
          $condition = [];
          $update = [];
          $condition['xianshi_id'] =$xianshi_id;
          if($state ==1){
              $update['state'] = 0;
          }elseif($state ==0){
              $update['state'] = 1;
          }
          $result = Model('shequ_xianshi')->editXianshiGoods($update,$condition);
          if ($result) {
              // 添加计划任务
              showMessage('设置成功！', 'index.php?act=tuan_config&op=seckill_list&config_tuan_id='.$tuan_config_id);
          } else {
              showMessage('设置失败！');
          }

      }



    /**
     * 获取活动秒杀商品列表
    */
    public function get_seckill_goods_listOp()
    {
       /* $where = array(
            'goods_storage' => array('gt', 0),
        );*/
        if (strlen($goods_name = trim($_REQUEST['goods_name']))) {
            $where['goods_name'] = array('like', "%$goods_name%");
        }
       /* if (strlen($goods_id = trim($_REQUEST['goods_id']))) {
            $where['goods_id'] = $goods_id;
        }*/
        $page = $_GET['page'];
        $tuan_config_id = intval($_GET['tuan_config_id']);
        $where['tuan_config_id'] = $tuan_config_id;
        $where['state'] = 1;
        /** @var shequ_tuan_config_goodsModel $tuan_config_goods_model */
        $tuan_config_goods_model = Model('shequ_tuan_config_goods');
        $list = $tuan_config_goods_model->getTuanConfigGoodsList($where, null, 'tuan_config_goods_id desc');
        if ($list){
            $goodsIds = array_unique(array_column($list,'goods_id'));
            /** @var goodsModel $goodsModel */
            $goodsModel = Model('goods');
            $goods = $goodsModel->getGoodsList(array('goods_id'=>array('in',$goodsIds)));
            $goods = array_column($goods,null,'goods_id');
            foreach ($list as $k=>$val){
                $good = $goods[$val['goods_id']];
                $val['goods_price'] = $good['goods_price'];
                $list[$k] = $val;
            }
        }
        $total = ceil($tuan_config_goods_model->gettotalnum()/10);
        echo json_encode(array('total'=>$total,'items'=>$list));
        exit;
    }


    /**
     * 添加秒杀活动商品
     */
    public function add_tuan_seckill_goodsOp() {
        $config_tuan_id = intval($_GET['config_tuan_id']);
        $xianshi_id = intval($_GET['xianshi_id']);
        $condition['tuan_config_id'] = $config_tuan_id;
        $condition['xianshi_id'] = $xianshi_id;
        $condition['state'] = 1;
        /** @var shequ_xianshi_goodsModel $xianshi_goods_model */
        $tuan_config_goods_model = Model('shequ_xianshi_goods');
        $list = $tuan_config_goods_model->getXianshiGoodsList($condition, null, 'xianshi_goods_id desc');
        Tpl::output('show_page', $tuan_config_goods_model->showpage());
        Tpl::output('goods_list', $list);
        Tpl::output('selected_sku', implode(',',array_column($list,'goods_id')));
        Tpl::output('xianshi_id', $xianshi_id);
        Tpl::output('tuan_config_id', $config_tuan_id);
        Tpl::setDirquna('shequ');
        Tpl::showpage('tuan_seckill_goods_add');
    }

    public function tuan_seckill_add_goods_saveOp() {
        $tuan_config_id = intval($_POST['tuan_config_id']);
        $xianshi_id = intval($_POST['xianshi_id']);
        $data = $_POST['data'];
        /** @var shequ_tuan_configModel $tuan_config_model */
        $tuan_config_model = Model('shequ_tuan_config');

        /** @var shequ_xianshiModel $shequ_xianshi_model */
        $shequ_xianshi_model = Model('shequ_xianshi');

        /** @var goodsModel $goods_model */
        $goods_model = Model('goods');
        $tuan_config_info = $tuan_config_model->getTuanConfigInfo(array('config_tuan_id' => $tuan_config_id));
        if (empty($tuan_config_info)){
            die(json_encode(array('code'=>400,'msg'=>'团购不存在')));
        }
        $shequ_xianshi_info = $shequ_xianshi_model->getXianShiGoodsInfo(array('xianshi_id' => $xianshi_id));
        if ($shequ_xianshi_info['state']==0){
            die(json_encode(array('code'=>400,'msg'=>'秒杀活动以失效')));
        }

        $skuIds = array_column($data,'goods_id');
        if (empty($skuIds)) {
            die(json_encode(array('code' => 400, 'msg' => '提交数据错误')));
        }

        // 删除不存在的
        /** @var shequ_xianshi_goodsModel $xianshi_goods_model */
        $xianshi_goods_model = Model('shequ_xianshi_goods');
        $xianshi_goods_model->beginTransaction();
        $res = $xianshi_goods_model->editXianshiGoods(
            array('tuan_config_id'=>$tuan_config_id,'xianshi_id'=>$xianshi_id,'goods_id'=>array('not in',$skuIds)),
            array('state'=>0)
        );
        if($res === false){
            $xianshi_goods_model->rollback();
            die(json_encode(array('code'=>400,'msg'=>'更新失败')));
        }
        /** @var jdy_mappingModel $goodsMappingModel */
        $goodsMappingModel = Model('jdy_mapping');
        $goodsMappingList = $goodsMappingModel->getList(array('goods_id'=>array('in',$skuIds)));
        $goodsMappingList = array_column($goodsMappingList,null,'goods_id');
        // 循环添加或更新数据
        foreach ($data as $sku){
           /* if (!isset($goodsMappingList[$sku['goods_id']])){
                die(json_encode(array('code'=>400,'msg'=>"【{$sku['goods_name']}】没有映射精斗云商品和供应商")));
            }*/
            $goodsMapping = $goodsMappingList[$sku['goods_id']];
            $configGoods = $xianshi_goods_model->getXianShiGoodsInfo(array('tuan_config_id'=>$tuan_config_id,'xianshi_id'=>$xianshi_id,'goods_id'=>$sku['goods_id']));
            if ($configGoods){
                $res = $xianshi_goods_model->editXianshiGoods(
                    array('xianshi_goods_id'=>$configGoods['xianshi_goods_id']),
                    array('state'=>1,'xianshi_price'=>$sku['xianshi_price'],'xianshi_storage'=>$sku['xianshi_storage'],'xianshi_limit'=>$sku['xianshi_limit'],'first_order'=>$sku['first_order'])
                );
                if($res === false){
                    $xianshi_goods_model->rollback();
                    die(json_encode(array('code'=>400,'msg'=>'更新失败')));
                }
            }else{
                $seckill = $shequ_xianshi_model->getXianShiGoodsInfo(array('xianshi_id' => $xianshi_id));
                $goodslist = $goods_model->getGoodsInfoByID($sku['goods_id']);
                $res = $xianshi_goods_model->addXianShiGoods(
                    array('state'=>1,
                        'gc_id_1'=>$goodslist['gc_id_1'],
                        'xianshi_id'=>$xianshi_id,
                        'xianshi_name'=>$seckill['xianshi_name'],
                        'xianshi_title'=> $seckill['xianshi_title'],
                        'xianshi_explain'=> $seckill['xianshi_explain'],
                        'tuan_config_id'=>$tuan_config_id,
                        'goods_id'=>$sku['goods_id'],
                        'goods_name'=>$sku['goods_name'],
                        'store_id'=>$sku['store_id'],
                        'goods_image'=>$sku['goods_image'],
                        'goods_price'=>$goodslist['goods_price'],
                        'xianshi_price'=>$sku['xianshi_price'],
                        'start_time'=>$seckill['start_time'],
                        'end_time'=>$seckill['end_time'],
                        'lower_limit'=>0,
                        'xianshi_recommend'=>0,
                        'xianshi_sold'=>0,
                        'xianshi_storage'=>$sku['xianshi_storage'],
                        'xianshi_limit'=>$sku['xianshi_limit'],
                        'first_order'=>$sku['first_order'],
                    )
                );
                if($res === false){
                    $xianshi_goods_model->rollback();
                    die(json_encode(array('code'=>400,'msg'=>'更新失败')));
                }
            }
        }
        $xianshi_goods_model->commit();
        die(json_encode(array('code'=>0,'data'=>'添加成功')));
    }

    /**
     * 秒杀活动下的商品列表
     */
    public function  tuan_seckill_goodsOp() {
        $config_tuan_id = intval($_GET['config_tuan_id']);
        $xianshi_id = intval($_GET['xianshi_id']);
        Tpl::output('xianshi_id', $xianshi_id);
        Tpl::output('tuan_config_id', $config_tuan_id);
        Tpl::setDirquna('shequ');
        Tpl::showpage('tuan_seckill_goods');
    }


    /**
     * 活动下的商品列表数据接口
     */
    public function tuan_seckill_goods_xmlOp() {
        $config_tuan_id = intval($_GET['config_tuan_id']);
        $xianshi_id = intval($_GET['xianshi_id']);
        /** @var shequ_xianshi_goodsModel $xianshi_goods_model */
        $xianshi_goods_model = Model('shequ_xianshi_goods');
        $condition['tuan_config_id'] = $config_tuan_id;
        $condition['xianshi_id'] = $xianshi_id;
        $condition['state'] = 1;
        $list = $xianshi_goods_model->getXianshiGoodsList($condition, null, 'xianshi_goods_id desc');
        $data = array();
        $data['now_page'] = $xianshi_goods_model->shownowpage();
        $data['total_num'] = $xianshi_goods_model->gettotalnum();
        foreach ($list as $val) {
            $i = array();
            $i['xianshi_goods_id']  = $val['xianshi_goods_id'];
            $i['goods_name'] = $val['goods_name'];
            $i['goods_price'] = $val['goods_price'];
            $i['xianshi_price'] = $val['xianshi_price'];
            $i['xianshi_storage'] = $val['xianshi_storage'];
            $i['xianshi_sold'] = $val['xianshi_sold'];
            $data['list'][$val['xianshi_goods_id']] = $i;
        }
        echo Tpl::flexigridXML($data);
        exit;
    }

    /**
     * 团长列表
     *
     */

    public function tuanzhang_listOp()
    {
        $tuan_config_id = intval($_GET['config_tuan_id']);
        Tpl::output('tuan_config_id', $tuan_config_id);
        Tpl::setDirquna('shequ');
        Tpl::output('top_link', $this->sublink($this->links, $_GET['op']));
        Tpl::showpage('tuanzhang_list');
    }

    /**
     * 团长列表接口数据
     *
     */
    public function tuanzhang_list_xmlOp()
    {
        $page = $_POST['rp'];
        $condition = array();
        list($condition) = $this->_get_condition($condition);
//        if (strlen($tz_name = trim($_REQUEST['query']))) {
//            $condition['tz_name'] = array('like', "%$tz_name%");
//        }
//        $config_tuan_id = intval($_GET['tuan_config_id']);
//        $condition['config_id'] = $config_tuan_id;
        /** @var shequ_tuanModel $model_shequ_tuan */
        $model_shequ_tuan = Model('shequ_tuan');
        $tuan_list = $model_shequ_tuan->getList($condition,$page,'end_time desc');
        $data = array();
        $data['now_page'] = $model_shequ_tuan->shownowpage();
        $data['total_num'] = $model_shequ_tuan->gettotalnum();
        foreach ($tuan_list as $val) {
            $o = '';
            $o .= '<span class="btn"><em><i class="fa fa-cog"></i>设置<i class="arrow"></i></em><ul>';
            if(($val['state'] == 20) || ($val['state'] == 10)){
                $o .= '<li><a class="confirm-on-click" href="' . "index.php?act=order&shequ_tuan_id={$val['config_id']}&shequ_tz_id={$val['tz_id']}" . '">查看订单列表</a></li>';
                $o .= '<li><a class="confirm-on-click" href="' . "index.php?act=refund&shequ_tuan_id={$val['config_id']}&shequ_tz_id={$val['tz_id']}" . '">查看退款列表</a></li>';
                if($val['end_time']<time()) {
                    $o .= '<li><a class="confirm-on-click" href="' . "index.php?act=tuan_config&op=tuanzhang_detail&config_id={$val['config_id']}&id={$val['id']}" . '">分配司机</a></li>';
                }
//                $o .= '<li><a class="confirm-on-click" href="' . "index.php?act=tuan_config&op=distribution_print&id={$val['id']}" . '">打印配送单</a></li>';
            }elseif($val['state'] == 30){
                $o .= '<li><a class="confirm-on-click" href="' . "index.php?act=order&shequ_tuan_id={$val['config_id']}&shequ_tz_id={$val['tz_id']}" . '">查看订单列表</a></li>';
                $o .= '<li><a class="confirm-on-click" href="' . "index.php?act=refund&shequ_tuan_id={$val['config_id']}&shequ_tz_id={$val['tz_id']}" . '">查看退款列表</a></li>';
                $o .= '<li><a class="confirm-on-click" target="_blank" href="' . "index.php?act=tuan_config&op=distribution_print&id={$val['id']}&shequ_tuan_id={$val['config_id']}&shequ_tz_id={$val['tz_id']}" . '">打印配送单</a></li>';
            }
            $o .= '</ul></span>';
            $where['id'] = $val['address_id'];
            /** @var shequ_addressModel $model_shequ_address */
            $model_shequ_address = Model('shequ_address');
            $tuan_address_list = $model_shequ_address->getOne($where);
            $i = array();
            $i['operation'] = $o;
            $i['tz_name'] = $val['tz_name'];
            $i['tz_phone'] = $val['tz_phone'];
            $i['start_time'] = date('Y-m-d H:i', $val['start_time']);
            $i['end_time'] = date('Y-m-d H:i', $val['end_time']);
            $i['area'] = $tuan_address_list['area'];
            $i['street'] = $tuan_address_list['street'];
            $i['community'] = $tuan_address_list['community'];
            $i['address'] = $val['address'];
            $i['building'] = $val['building'];
            $i['longitude'] = $val['longitude'];
            $i['latitude'] = $val['latitude'];
            if($val['state']==10){
                $i['state']='已下单';
            }elseif ($val['state']==20){
                $i['state']='已成团';
            }elseif ($val['state']==30){
                $i['state']='已分派司机';
            }elseif ($val['state']==40){
                $i['state']='配送完成';
            }
//            $i['total_amount'] = $val['total_amount'];
            $i['order_num'] = $val['order_num'];
//            $i['commis_amount'] = $val['commis_amount'];
            $i['refund_amount'] = $val['refund_amount'];
            $i['refund_commis_amount'] = $val['refund_commis_amount'];
            $data['list'][$val['id']] = $i;
        }
        exit(Tpl::flexigridXML($data));
    }

    /**
     * 封装团长列表查询添加
     */
    private function _get_condition($condition) {
          /* echo '<pre>';
           print_r($_GET);exit;*/
        if ($_GET['keyword'] != '' && in_array($_GET['keyword_type'],array('tz_name','tz_phone'))) {
            if ($_GET['jq_query']) {
                $condition[$_GET['keyword_type']] = $_GET['keyword'];
            } else {
                $condition[$_GET['keyword_type']] = array('like',"%{$_GET['keyword']}%");
            }
        }
        if ($_GET['keyword_address'] != '' && in_array($_GET['address_list'],array('area','street','community'))) {
            /** @var shequ_addressModel $model_shequ_address */
            $model_shequ_address = Model('shequ_address');
            if ($_GET['jq_query_address']) {
                $where[$_GET['address_list']] = $_GET['keyword_address'];
                $tuang_address_list = $model_shequ_address->getList($where);
                if(!empty($tuang_address_list)){
                    $condition['address_id']=array('in',array_column($tuang_address_list,'id'));
                }else{
                    $condition['address_id'] = null;
                }
            } else {
                $where[$_GET['address_list']] = array('like',"%{$_GET['keyword_address']}%");
                $tuang_address_list = $model_shequ_address->getList($where);
                if(!empty($tuang_address_list)){
                    $condition['address_id']=array('in',array_column($tuang_address_list,'id'));
                }else{
                    $condition['address_id'] = null;
                }
            }
        }
        if(in_array($_GET['order_state'],array('0','10','20','30','40'))){
            $condition['state'] = $_GET['order_state'];
        }
        if ($_REQUEST['query'] != '' && in_array($_REQUEST['qtype'],array('tz_name','tz_phone'))) {
            $condition[$_REQUEST['qtype']] = array('like',"%{$_REQUEST['query']}%");
        }
        $condition['config_id'] = intval($_GET['config_tuan_id']);
        return array($condition);
    }

    /**
     * 团长分配司机
     *
     */

    public function tuanzhang_detailOp() {
        $driver_list =  C('driver_config');
        $id = intval($_GET['id']);
        $config_id = intval($_GET['config_id']);
        $condition['id'] = $id;
        /** @var shequ_tuanModel $model_shequ_tuan */
        $model_shequ_tuan = Model('shequ_tuan');
        $tuanzhang_info = $model_shequ_tuan->getOne($condition);
        $where['shequ_tz_id'] = $tuanzhang_info['tz_id'];
        $where['shequ_tuan_id'] = $config_id;
        /** @var orderModel $order */
        $order = Model('order');
        $order_list = $order->getOrderList($where);
        if (chksubmit()) {
            $driver_id = trim($_POST['config_xianshi_driver']);
            if ($driver_id == 0) {
                showMessage('请选择配送司机！');
            }

            $data = array();
            $data['driver_name'] = $driver_list[$driver_id]['driver_name'];
            $data['driver_phone'] = $driver_list[$driver_id]['driver_phone'];
            $data['driver_car_number'] = $driver_list[$driver_id]['driver_car_number'];
            $data['driver_id'] = $driver_list[$driver_id]['driver_id'];
            //$data['send_product_date'] =time();
            $data['state'] = 30;
            $result = $model_shequ_tuan->edit($condition,$data);
            if ($result) {
                $where['shequ_tz_id'] = $tuanzhang_info['tz_id'];
                $where['shequ_tuan_id'] = $config_id;
                $where['order_state'] = ORDER_STATE_PAY;
                $where['lock_state'] = 0;
                /** @var orderModel $order */
                $order = Model('order');
                $order_list = $order->getOrderList($where);
                foreach ($order_list as $val){
                    /** @var orderLogic $orderLogic */
                    $orderLogic = Logic('order');
                    $post['shipping_express_id'] =999;
                    $orderLogic->changeOrderSend($val,'system','',$post);
                }
                // 添加计划任务
                showMessage('司机分配成功！', 'index.php?act=tuan_config&op=tuanzhang_list&config_tuan_id='.$config_id);
            } else {
                showMessage('分配失败！');
            }
        }
        Tpl::output('driver_list', $driver_list);
        Tpl::output('tuanzhang_info', $tuanzhang_info);
        Tpl::setDirquna('shequ');
        Tpl::showpage('tuanzhang_detail');
    }

    /**
     * 打印配送单
     *
     */
    public function distribution_printOp(){
        $id = intval($_GET['id']);
        $dingtang_info = Model('shequ_tuan')->getOne(array('id' => $id));
        if(empty($dingtang_info)) {
            showMessage(L('param_error'));
        }
        Tpl::output('dingtang_info', $dingtang_info);
        /** @var shequ_peisongdan $peisongdanModel */
        $peisongdanModel = Model('shequ_peisongdan');
        $items = $peisongdanModel->getList(array('tuan_id'=>$id));
        Tpl::output('items', $items);
        //自提与物流
        $condition_config['tuan_config_id'] = intval($_GET['shequ_tuan_id']);
        $condition_config['type'] =1;
        /** @var shequ_tuan_config_goodsModel $shequ_tuan_config_goods */
        $shequ_tuan_config_goods = Model('shequ_tuan_config_goods');
        $config_goods_list = $shequ_tuan_config_goods->getTuanConfigGoodsList($condition_config);
        $goods_id_list = array_unique(array_column($config_goods_list,'goods_id'));
        $condition['shequ_tz_id'] = intval($_GET['shequ_tz_id']);
        $condition['shequ_tuan_id'] = intval($_GET['shequ_tuan_id']);
        $condition['order_state'] = 30;
        /** @var orderModel $order */
        $order = Model('order');
        $order_list = $order->getOrder_distributionList($condition);
        $order_id_list = array_column($order_list,'order_id');
        $where['order_id'] = array('in',$order_id_list);
        $order_goods_list = $order->getOrderGoodsList($where);
        $order_list = array_column($order_list,null,'order_id');
        foreach ($order_goods_list as $k => $goods){
            if(in_array($goods['goods_id'],$goods_id_list)) {
                $order_arr = $order_list[$goods['order_id']];
                $goods['buyer_name'] = $order_arr['buyer_name'];
                $goods['buyer_phone'] = $order_arr['buyer_phone'];
                $order_goods_list[$k] = $goods;
            }else{
                unset($order_goods_list);
            }
        }
        Tpl::output('order_goods_list', $order_goods_list);
        Tpl::setDirquna('shequ');
        Tpl::showpage('tuan_config.distribution_print');
    }

    /**
     * 批量打印配送单
     *
     */
    public function distribution_batch_printOp(){
        $config_id =intval($_GET['tuan_config_id']);
        $condition['state'] = 30;
        $condition['config_id']  = $config_id;
        $dingtang_batch_info = Model('shequ_tuan')->getList($condition);
        if(empty($dingtang_batch_info)) {
            showMessage('还没有配送单');
        }
//        $order_id_list = array_column($dingtang_batch_info,'id');
//        /** @var shequ_peisongdanModel $peisongdanModel */
//        $peisongdanModel = Model('shequ_peisongdan');
//        $peisongdanList = $peisongdanModel->getList(array('tuan_id'=>array('in',$order_id_list)));
//        foreach ($peisongdanList as $key=>$pdlist){
//            //$items = $dingtang_batch_info[$pdlist['tuan_id']];
//            foreach ($dingtang_batch_info as $k=>$v){
//              if($pdlist['tuan_id']==$v['id']){
//                  $dingtang_batch_info[$k]['items'][] = $pdlist;
//              }
//            }
//        }
//        $tz_id_list = array_column($dingtang_batch_info,'tz_id');
//        $config_id_list = array_column($dingtang_batch_info,'config_id');
//        $condition_order['shequ_tz_id'] = array('in',$tz_id_list);
//        $condition_order['shequ_tuan_id'] = array('in',$config_id_list);
//        $condition_order['order_state'] = 30;
//        /** @var orderModel $order */
//        $order = Model('order');
//        $order_list = $order->getOrder_distributionList($condition_order);
//        $order_id_list = array_column($order_list,'order_id');
//        $where['order_id'] = array('in',$order_id_list);
//        $order_goods_list = $order->getOrderGoodsList($where);
//        $order_list = array_column($order_list,null,'order_id');
//        foreach ($order_goods_list as $k1 => $goods){
//            $order_arr = $order_list[$goods['order_id']];
//            $goods['buyer_name'] = $order_arr['buyer_name'];
//            $goods['buyer_phone'] = $order_arr['buyer_phone'];
//            $order_goods_list[$k1] = $goods;
//        }
//        foreach ($order_goods_list as $k2=>$v2){
//            foreach ($dingtang_batch_info as $k3=>$v3){
//                if($v2['shequ_tuan_id']==$v3['config_id'] and $v2['shequ_tz_id']==$v3['tz_id']){
//                    $dingtang_batch_info[$k]['goods_list'][] = $v2;
//                }
//            }
//        }

        foreach ($dingtang_batch_info as $key=>$value){
            /** @var shequ_peisongdanModel $peisongdanModel */
            $peisongdanModel = Model('shequ_peisongdan');
            $peisongdanList = $peisongdanModel->getList(array('tuan_config_id'=>$config_id,'tuan_id'=>$value['id']));
            $dingtang_batch_info[$key]['items']=$peisongdanList;
            //自提与物流
            $condition_config['tuan_config_id'] = $config_id;
            $condition_config['type'] =1;
            /** @var shequ_tuan_config_goodsModel $shequ_tuan_config_goods */
            $shequ_tuan_config_goods = Model('shequ_tuan_config_goods');
            $config_goods_list = $shequ_tuan_config_goods->getTuanConfigGoodsList($condition_config);
            $goods_id_list = array_unique(array_column($config_goods_list,'goods_id'));
            //订单列表
            $condition_order['shequ_tz_id'] = $value['tz_id'];
            $condition_order['shequ_tuan_id'] = $config_id;
            $condition_order['order_state'] = 30;
            /** @var orderModel $order */
            $order = Model('order');
            $order_list = $order->getOrder_distributionList($condition_order);
            $order_id_list = array_column($order_list,'order_id');
            $where['order_id'] = array('in',$order_id_list);
            $order_goods_list = $order->getOrderGoodsList($where);
            $order_list = array_column($order_list,null,'order_id');
            foreach ($order_goods_list as $k => $goods){
                if(in_array($goods['goods_id'],$goods_id_list)){
                   $order_arr = $order_list[$goods['order_id']];
                   $goods['buyer_name'] = $order_arr['buyer_name'];
                   $goods['buyer_phone'] = $order_arr['buyer_phone'];
                   $order_goods_list[$k] = $goods;
                }else{
                    unset($order_goods_list);
                }
            }
            $dingtang_batch_info[$key]['goods_list'] = $order_goods_list;
        }
        /* echo '<pre>';
        print_r($dingtang_batch_info);exit;*/
        Tpl::output('dingtang_batch_info', $dingtang_batch_info);

//        Tpl::output('order_goods_list', $order_goods_list);
        Tpl::setDirquna('shequ');
        Tpl::showpage('tuan_config.distribution_batch_print');
    }

    /**
     * 页面内导航菜单
     *
     * @param string    $menu_key   当前导航的menu_key
     * @param array     $array      附加菜单
     * @return
     */
    private function show_menu($menu_key) {
        $menu_array = array(
            'config_tuan_list'=>array('menu_type'=>'link','menu_name'=>'团购列表','menu_url'=>'index.php?act=tuan_config&op=config_tuan_list'),
        );
        $menu_array[$menu_key]['menu_type'] = 'text';
        Tpl::output('menu',$menu_array);
    }

}
