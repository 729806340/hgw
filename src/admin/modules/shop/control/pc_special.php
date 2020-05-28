<?php
/**
 * PC专题
 *
 *
 *
 ** 本系统由汉购网 hangowa.com提供
 */

//use Shopwwi\Tpl;

defined('ByShopWWI') or exit('Access Invalid!');
class pc_specialControl extends SystemControl{
    public function __construct(){
        parent::__construct();
    }

    public function indexOp() {
        $this->special_listOp();
    }
    
    /**
     * 专题列表
     */
    public function special_listOp() {
        /** @var pc_specialModel $model_pc_special */
        $model_pc_special = Model('pc_special');

        $pc_special_list = $model_pc_special->getPCSpecialList(array(), 10);

        Tpl::output('list', $pc_special_list);
        Tpl::output('page', $model_pc_special->showpage(2));

        $this->show_menu('special_list');
        Tpl::setDirquna('shop');
        Tpl::showpage('pc_special.list');
    }

    /**
     * 保存专题
     */
    public function special_saveOp() {
        $model_pc_special = Model('pc_special');
        

        $param = array();
        $param['special_title'] = $_POST['special_title'];
        $result = $model_pc_special->addPCSpecial($param);

        if($result) {
            $this->log('添加PC专题' . '[ID:' . $result. ']', 1);
            showMessage(L('nc_common_save_succ'), urlAdminShop('pc_special', 'special_list'));
        } else {
            $this->log('添加PC专题' . '[ID:' . $result. ']', 0);
            showMessage(L('nc_common_save_fail'), urlAdminShop('pc_special', 'special_list'));
        }
    }

    /**
     * 编辑专题描述
     */
    public function update_special_tmplOp() {
        $model_pc_special = Model('pc_special');

        $param = array();
        $param['special_tmpl'] = $_GET['value'];
        $result = $model_pc_special->editPCSpecial($param, $_GET['id']);

        $data = array();
        if($result) {
            $this->log('保存PC专题' . '[ID:' . $result. ']', 1);
            $data['result'] = true;
        } else {
            $this->log('保存PC专题' . '[ID:' . $result. ']', 0);
            $data['result'] = false;
            $data['message'] = '保存失败';
        }
        echo json_encode($data);die;
    }

    /**
     * 编辑专题描述
     */
    public function update_special_descOp() {
        $model_pc_special = Model('pc_special');

        $param = array();
        $param['special_desc'] = $_GET['value'];
        $result = $model_pc_special->editPCSpecial($param, $_GET['id']);

        $data = array();
        if($result) {
            $this->log('保存PC专题' . '[ID:' . $result. ']', 1);
            $data['result'] = true;
        } else {
            $this->log('保存PC专题' . '[ID:' . $result. ']', 0);
            $data['result'] = false;
            $data['message'] = '保存失败';
        }
        echo json_encode($data);die;
    }
    
    /**
     * 编辑专题描述
     */
    public function update_special_titleOp() {
        $model_pc_special = Model('pc_special');
    
        $param = array();
        $param['special_title'] = $_GET['value'];
        $result = $model_pc_special->editPCSpecial($param, $_GET['id']);
    
        $data = array();
        if($result) {
            $this->log('保存PC专题' . '[ID:' . $result. ']', 1);
            $data['result'] = true;
        } else {
            $this->log('保存PC专题' . '[ID:' . $result. ']', 0);
            $data['result'] = false;
            $data['message'] = '保存失败';
        }
        echo json_encode($data);die;
    }
    
    /**
     * 编辑专题描述
     */
    public function update_special_keywordsOp() {
        $model_pc_special = Model('pc_special');
    
        $param = array();
        $param['special_keywords'] = $_GET['value'];
        $result = $model_pc_special->editPCSpecial($param, $_GET['id']);
    
        $data = array();
        if($result) {
            $this->log('保存PC专题' . '[ID:' . $result. ']', 1);
            $data['result'] = true;
        } else {
            $this->log('保存PC专题' . '[ID:' . $result. ']', 0);
            $data['result'] = false;
            $data['message'] = '保存失败';
        }
        echo json_encode($data);die;
    }

    /**
     * 删除专题
     */
    public function special_delOp() {
        $model_pc_special = Model('pc_special');

        $result = $model_pc_special->delPCSpecialByID($_POST['special_id']);

        if($result) {
            $this->log('删除PC专题' . '[ID:' . $_POST['special_id'] . ']', 1);
            showMessage(L('nc_common_del_succ'), urlAdminShop('pc_special', 'special_list'));
        } else {
            $this->log('删除PC专题' . '[ID:' . $_POST['special_id'] . ']', 0);
            showMessage(L('nc_common_del_fail'), urlAdminShop('pc_special', 'special_list'));
        }
    }

    /**
     * 编辑专题
     */
    public function special_editOp() {
        $special_id = intval($_GET['special_id']);
        $model_pc_special = Model('pc_special');

        $special_item_list = $model_pc_special->getPCSpecialItemListByID($special_id);

        Tpl::output('list', $special_item_list);
        Tpl::output('page', $model_pc_special->showpage(2));
        
        //取子模块设置
        $special_module_list = $model_pc_special->getPCSpecialModuleList();
        $special_module_list = array_column($special_module_list, 'items');
        $special_module_items = array();
        foreach ($special_module_list as $vals) {
            $special_module_items = array_merge($special_module_items, $vals);
        }
        
        //取专题说明信息
        $special_info = $model_pc_special->getPCSpecial($special_id);
        Tpl::output('module_list', $special_module_items );
        Tpl::output('special_id', $_GET['special_id']);
        Tpl::output('special_info', $special_info);
        Tpl::setDirquna('shop');
        Tpl::showpage('pc_special_item.list');
    }

    /**
     * 专题项目添加
     */
    public function special_item_addOp() {
        $model_pc_special = Model('pc_special');

        $param = array();
        $param['special_id'] = $_POST['special_id'];
        $param['item_type'] = $_POST['item_type'];

        //广告只能添加一个
        if($param['item_type'] == 'adv_list') {
            $result = $model_pc_special->isPCSpecialItemExist($param);
            if($result) {
                echo json_encode(array('error' => '广告条板块只能添加一个'));die;
            }
        }
		//限时折扣只能添加一个
        if($param['item_type'] == 'goods1') {
            $result = $model_pc_special->isPCSpecialItemExist($param);
            if($result) {
                echo json_encode(array('error' => '限时折扣板块只能添加一个'));die;
            }
        }
		//团购板块只能添加一个
        if($param['item_type'] == 'goods2') {
            $result = $model_pc_special->isPCSpecialItemExist($param);
            if($result) {
                echo json_encode(array('error' => '团购板块只能添加一个'));die;
            }
        }

        $param['item_template'] = 'default';
        $item_info = $model_pc_special->addPCSpecialItem($param);
        if($item_info) {
            echo json_encode($item_info);die;
        } else {
            echo json_encode(array('error' => '添加失败'));die;
        }
    }

    /**
     * 专题项目删除
     */
    public function special_item_delOp() {
        $model_pc_special = Model('pc_special');

        $condition = array();
        $condition['item_id'] = $_POST['item_id'];

        $result = $model_pc_special->delPCSpecialItem($condition, $_POST['special_id']);
        if($result) {
            echo json_encode(array('message' => '删除成功'));die;
        } else {
            echo json_encode(array('error' => '删除失败'));die;
        }
    }

    /**
     * 编辑导航
     */
    public function special_navi_editOp(){
        $model_pc_special = Model('pc_special');
        $item_id = $_GET['item_id'];
        $item_info = $model_pc_special->getPCSpecialItemInfoByID($item_id);
        Tpl::output('item_info', $item_info);

        if($_POST) {
            $special_id = $_POST['special_id'];

            if(!$_POST['navi_title']){
                showMessage('ID或标题不能为空！', urlAdminShop('pc_special', 'special_navi_edit'));
            }

            $update = [
                //'navi_id'=>$_POST['navi_id'],
                'navi_title'=>$_POST['navi_title'],
            ];
            $result = $model_pc_special->editPCSpecialItemByID($update, $item_id, $special_id);
            if($result) {
                $this->log('添加PC专题' . '[ID:' . $result. ']', 1);
                showMessage(L('nc_common_save_succ'), urlAdminShop('pc_special', 'special_edit',array('special_id' => $_POST['special_id'])));
            } else {
                $this->log('添加PC专题' . '[ID:' . $result. ']', 0);
                showMessage(L('nc_common_save_fail'), urlAdminShop('pc_special', 'special_navi_edit'));
            }
        }

        //获取special名称信息
        $special_info = $model_pc_special->getPCSpecial($item_info['special_id']);
        Tpl::output('special_info', $special_info);
        Tpl::output('item_id', $item_id);

        Tpl::setDirquna('shop');
        Tpl::showpage('pc_special_navi.edit');
    }

    /**
     * 专题项目编辑
     */
    public function special_item_editOp() {
        $model_pc_special = Model('pc_special');

        $item_info = $model_pc_special->getPCSpecialItemInfoByID($_GET['item_id']);
        Tpl::output('item_info', $item_info);

        //取子模块设置
        $special_module_list = $model_pc_special->getPCSpecialModuleList();
        $special_module_list = array_column($special_module_list, 'items');
        $special_module_items = array();
        foreach ($special_module_list as $vals) {
            $special_module_items = array_merge($special_module_items, $vals);
        }
        
        //item归属的module获取
        $module_info = $special_module_items[$item_info['item_type']];
        Tpl::output('module_info', $module_info);
        
        //获取special名称信息
        $special_info = $model_pc_special->getPCSpecial($item_info['special_id']);
        Tpl::output('special_info', $special_info);
        
        if($item_info['special_id'] == 0) {
            $this->show_menu('index_edit');
        } else {
            $this->show_menu('special_item_list');
        }
        Tpl::setDirquna('shop');
        Tpl::showpage('pc_special_item.edit');
    }

    /**
     * 专题项目保存
     */
    public function special_item_saveOp() {
        $model_pc_special = Model('pc_special');
        $result = $model_pc_special->editPCSpecialItemByID(array('item_data' => $_POST['item_data']), $_POST['item_id'], $_POST['special_id'], $_POST['item_template']);

        if($result) {
            if($_POST['special_id'] == $model_pc_special::INDEX_SPECIAL_ID) {
                showMessage(L('nc_common_save_succ'), urlAdminShop('pc_special', 'index_edit'));
            } else {
                showMessage(L('nc_common_save_succ'), urlAdminShop('pc_special', 'special_edit', array('special_id' => $_POST['special_id'])));
            }
        } else {
            showMessage(L('nc_common_save_succ'), '');
        }
    }

    /**
     * 图片上传
     */
    public function special_image_uploadOp() {
        $data = array();
        if(!empty($_FILES['special_image']['name'])) {
            $prefix = 's' . $_POST['special_id'];
            $upload = new UploadFile();
            $upload->set('default_dir', ATTACH_MOBILE . DS . 'special' . DS . $prefix);
            $upload->set('fprefix', $prefix);
            $upload->set('allow_type', array('gif', 'jpg', 'jpeg', 'png'));

            $result = $upload->upfile('special_image');
            if(!$result) {
                $data['error'] = $upload->error;
            }
            $data['image_name'] = $upload->file_name;
            $data['image_url'] = getMBSpecialImageUrl($data['image_name']);
        }
        echo json_encode($data);
    }

    /**
     * 商品列表
     */
    public function goods_listOp() {
        $model_goods = Model('goods');
        $condition = array();
        $condition['goods_name'] = array('like', '%' . $_GET['keyword'] . '%');
        $goods_list = $model_goods->getGoodsOnlineList($condition, 'goods_id,goods_name,goods_promotion_price,goods_image', 10);
        Tpl::output('goods_list', $goods_list);
        Tpl::output('show_page', $model_goods->showpage());
        Tpl::setDirquna('shop');
        Tpl::showpage('pc_special_widget.goods', 'null_layout');
    }
	/**
     * 限时折扣商品列表
     */
    public function goods_xianshi_listOp() {
        $model_goods = Model('goods');
        $condition = array();
	    $model_xianshi_goods = Model('p_xianshi_goods');		
        $condition['goods_name'] = array('like', '%' . $_GET['keyword'] . '%');
	    $goods_id_list=$model_xianshi_goods->getXianshiGoodsExtendIds($condition);		
				
		$goods_list = $model_goods->getGoodsOnlineListAndPromotionByIdArray($goods_id_list);
        Tpl::output('goods_list', $goods_list);
        Tpl::output('show_page', $model_goods->showpage());
        Tpl::setDirquna('shop');
        Tpl::showpage('pc_special_widget.goods', 'null_layout');
    }
    /**
     * 团购商品列表
     */
    public function goods_groupbuy_listOp() {
        $model_goods = Model('goods');
        $condition = array();
        $condition['goods_name'] = array('like', '%' . $_GET['keyword'] . '%');
		$model_groupbuy_goods = Model('groupbuy');
		$goods_list_arr=$model_groupbuy_goods->getGroupbuyGoodsExtendIds($condition);			
		$goods_list=$model_goods->getGoodsOnlineListAndPromotionByIdArray($goods_list_arr);

        Tpl::output('goods_list', $goods_list);
        Tpl::output('show_page', $model_goods->showpage());
        Tpl::setDirquna('shop');
        Tpl::showpage('pc_special_widget.goods', 'null_layout');
    }
    /**
     * 更新项目排序
     */
    public function update_item_sortOp() {
        $item_id_string = $_POST['item_id_string'];
        $special_id = $_POST['special_id'];
        if(!empty($item_id_string)) {
            $model_pc_special = Model('pc_special');
            $item_id_array = explode(',', $item_id_string);
            $index = 0;
            foreach ($item_id_array as $item_id) {
                $result = $model_pc_special->editPCSpecialItemByID(array('item_sort' => $index), $item_id, $special_id);
                $index++;
            }
        }
        $data = array();
        $data['message'] = '操作成功';
        echo json_encode($data);
    }

    /**
     * 更新商品排序
     */
    public function update_goods_sortOp() {
        $goods_id_string = $_POST['goods_id_string'];
        $item_id = $_POST['item_id'];
        $special_id = $_POST['special_id'];
        if(!empty($goods_id_string)) {
            $model_pc_special = Model('pc_special');
            $special_info = $model_pc_special->getPCSpecialItem($item_id);
            //反序列化，用新顺序替代，再序列化更新
            $data = unserialize($special_info['item_data']);
            $data['item']['goods'] = explode(',',rtrim($goods_id_string,','));
            $model_pc_special->editPCSpecialItemByID(array('item_data' => $data), $item_id, $special_id);
        }
        $data['message'] = '操作成功';
        echo json_encode($data);
    }

    /**
     * 更新项目启用状态
     */
    public function update_item_usableOp() {
        $model_pc_special = Model('pc_special');
        $result = $model_pc_special->editPCSpecialItemUsableByID($_POST['usable'], $_POST['item_id'], $_POST['special_id']);
        $data = array();
        if($result) {
            $data['message'] = '操作成功';
        } else {
            $data['error'] = '操作失败';
        }
        echo json_encode($data);
    }

    /**
     * 页面内导航菜单
     * @param string    $menu_key   当前导航的menu_key
     * @param array     $array      附加菜单
     * @return
     */
    private function show_menu($menu_key='') {
        $menu_array = array();
        if($menu_key == 'index_edit') {
            $menu_array[] = array('menu_key'=>'index_edit', 'menu_name'=>'首页', 'menu_url'=>'javascript:;');
            $menu_array[] = array('menu_key'=>'special_list','menu_name'=>'专题', 'menu_url'=>urlAdminShop('pc_special', 'special_list'));
        } else {
            $menu_array[] = array('menu_key'=>'index_edit', 'menu_name'=>'首页', 'menu_url'=>urlAdminShop('pc_special', 'index'));
            $menu_array[] = array('menu_key'=>'special_list','menu_name'=>'专题', 'menu_url'=>'javascript:;');
        }
        if($menu_key == 'index_edit') {
            tpl::output('item_title', '首页编辑');
        } else {
            tpl::output('item_title', '专题设置');
        }
        Tpl::output('menu', $menu_array);
        Tpl::output('menu_key', $menu_key);
    }
}
