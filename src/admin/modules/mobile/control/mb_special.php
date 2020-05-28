<?php
/**
 * 手机专题
 *
 *
 *
 ** 本系统由汉购网 hangowa.com提供
 */

//use Shopwwi\Tpl;

defined('ByShopWWI') or exit('Access Invalid!');
class mb_specialControl extends SystemControl{
    public function __construct(){
        parent::__construct();
    }

    public function indexOp() {
        $this->index_editOp();
    }

    /**
     * 专题列表
     */
    public function special_listOp() {
        /** @var mb_specialModel $model_mb_special */
        $model_mb_special = Model('mb_special');
        $mb_special_list = $model_mb_special->getMbSpecialList(array(), 10);
        Tpl::output('list', $mb_special_list);
        Tpl::output('page', $model_mb_special->showpage(2));
        $this->show_menu('special_list');
        Tpl::setDirquna('mobile');
        Tpl::showpage('mb_special.list');
    }

    /**
     * 保存专题
     */
    public function special_saveOp() {
        $model_mb_special = Model('mb_special');

        $param = array();
        $param['special_desc'] = $_POST['special_desc'];
        $result = $model_mb_special->addMbSpecial($param);

        if($result) {
            $this->log('添加手机专题' . '[ID:' . $result. ']', 1);
            showMessage(L('nc_common_save_succ'), urlAdminMobile('mb_special', 'special_list'));
        } else {
            $this->log('添加手机专题' . '[ID:' . $result. ']', 0);
            showMessage(L('nc_common_save_fail'), urlAdminMobile('mb_special', 'special_list'));
        }
    }

    /**
     * 编辑专题描述
     */
    public function update_special_descOp() {
        $model_mb_special = Model('mb_special');

        $param = array();
        $param['special_desc'] = $_GET['value'];
        $result = $model_mb_special->editMbSpecial($param, $_GET['id']);

        $data = array();
        if($result) {
            $this->log('保存手机专题' . '[ID:' . $result. ']', 1);
            $data['result'] = true;
        } else {
            $this->log('保存手机专题' . '[ID:' . $result. ']', 0);
            $data['result'] = false;
            $data['message'] = '保存失败';
        }
        echo json_encode($data);die;
    }

    /**
     * 编辑专题描述
     */
    public function update_special_backgroundOp() {
        /** @var mb_specialModel $model_mb_special */
        $model_mb_special = Model('mb_special');

        $param = array();
        $param['special_background'] = $_GET['value'];
        $result = $model_mb_special->editMbSpecial($param, $_GET['id']);

        $data = array();
        if($result) {
            $this->log('保存手机专题' . '[ID:' . $result. ']', 1);
            $data['result'] = true;
        } else {
            $this->log('保存手机专题' . '[ID:' . $result. ']', 0);
            $data['result'] = false;
            $data['message'] = '保存失败';
        }
        echo json_encode($data);die;
    }

    /**
     * 删除专题
     */
    public function special_delOp() {
        $model_mb_special = Model('mb_special');

        $result = $model_mb_special->delMbSpecialByID($_POST['special_id']);

        if($result) {
            $this->log('删除手机专题' . '[ID:' . $_POST['special_id'] . ']', 1);
            showMessage(L('nc_common_del_succ'), urlAdminMobile('mb_special', 'special_list'));
        } else {
            $this->log('删除手机专题' . '[ID:' . $_POST['special_id'] . ']', 0);
            showMessage(L('nc_common_del_fail'), urlAdminMobile('mb_special', 'special_list'));
        }
    }

    /**
     * 编辑首页
     */
    public function index_editOp() {
        /** @var mb_specialModel $model_mb_special */
        $model_mb_special = Model('mb_special');

        $special_item_list = $model_mb_special->getMbSpecialItemListByID($model_mb_special::INDEX_SPECIAL_ID);
        Tpl::output('list', $special_item_list);
        Tpl::output('page', $model_mb_special->showpage(2));

        Tpl::output('module_list', $model_mb_special->getMbSpecialModuleList());
        Tpl::output('special_id', $model_mb_special::INDEX_SPECIAL_ID);

        $this->show_menu('index_edit');
        Tpl::setDirquna('mobile');
Tpl::showpage('mb_special_item.list');
    }
    public function app_indexOp() {
        /** @var mb_specialModel $model_mb_special */
        $model_mb_special = Model('mb_special');

        $special_item_list = $model_mb_special->getMbSpecialItemListByID($model_mb_special::APP_INDEX_SPECIAL_ID);
        Tpl::output('list', $special_item_list);
        Tpl::output('page', $model_mb_special->showpage(2));

        Tpl::output('module_list', $model_mb_special->getMbSpecialModuleList());
        Tpl::output('special_id', $model_mb_special::APP_INDEX_SPECIAL_ID);

        $this->show_menu('app_index_edit');
        Tpl::setDirquna('mobile');
Tpl::showpage('mb_special_item.list');
    }
    public function app_category_editOp() {
        /** @var mb_specialModel $model_mb_special */
        $model_mb_special = Model('mb_special');

        $special_item_list = $model_mb_special->getMbSpecialItemListByID($model_mb_special::APP_CATEGORY_SPECIAL_ID);
        Tpl::output('list', $special_item_list);
        Tpl::output('page', $model_mb_special->showpage(2));

        Tpl::output('module_list', $model_mb_special->getMbSpecialModuleList());
        Tpl::output('special_id', $model_mb_special::APP_CATEGORY_SPECIAL_ID);

        $this->show_menu('app_category_edit');
        Tpl::setDirquna('mobile');
Tpl::showpage('mb_special_item.list');
    }
    public function app_shihua_editOp() {
        /** @var mb_specialModel $model_mb_special */
        $model_mb_special = Model('mb_special');

        $special_item_list = $model_mb_special->getMbSpecialItemListByID($model_mb_special::APP_SHIHUA_SPECIAL_ID);
        Tpl::output('list', $special_item_list);
        Tpl::output('page', $model_mb_special->showpage(2));

        Tpl::output('module_list', $model_mb_special->getMbSpecialModuleList());
        Tpl::output('special_id', $model_mb_special::APP_SHIHUA_SPECIAL_ID);

        $this->show_menu('app_shihua_edit');
        Tpl::setDirquna('mobile');
Tpl::showpage('mb_special_item.list');
    }
    public function app_shilv_editOp() {
        /** @var mb_specialModel $model_mb_special */
        $model_mb_special = Model('mb_special');

        $special_item_list = $model_mb_special->getMbSpecialItemListByID($model_mb_special::APP_SHILV_SPECIAL_ID);
        Tpl::output('list', $special_item_list);
        Tpl::output('page', $model_mb_special->showpage(2));

        Tpl::output('module_list', $model_mb_special->getMbSpecialModuleList());
        Tpl::output('special_id', $model_mb_special::APP_SHILV_SPECIAL_ID);

        $this->show_menu('app_shilv_edit');
        Tpl::setDirquna('mobile');
Tpl::showpage('mb_special_item.list');
    }
    public function app_faxian_editOp() {
        /** @var mb_specialModel $model_mb_special */
        $model_mb_special = Model('mb_special');

        $special_item_list = $model_mb_special->getMbSpecialItemListByID($model_mb_special::APP_FAXIAN_SPECIAL_ID);
        Tpl::output('list', $special_item_list);
        Tpl::output('page', $model_mb_special->showpage(2));

        Tpl::output('module_list', $model_mb_special->getMbSpecialModuleList());
        Tpl::output('special_id', $model_mb_special::APP_FAXIAN_SPECIAL_ID);

        $this->show_menu('app_faxian_edit');
        Tpl::setDirquna('mobile');
Tpl::showpage('mb_special_item.list');
    }
    public function app_zhidemai1_editOp() {
        /** @var mb_specialModel $model_mb_special */
        $model_mb_special = Model('mb_special');

        $special_item_list = $model_mb_special->getMbSpecialItemListByID($model_mb_special::APP_ZHIDEMAI1_SPECIAL_ID);
        Tpl::output('list', $special_item_list);
        Tpl::output('page', $model_mb_special->showpage(2));

        Tpl::output('module_list', $model_mb_special->getMbSpecialModuleList());
        Tpl::output('special_id', $model_mb_special::APP_ZHIDEMAI1_SPECIAL_ID);

        $this->show_menu('app_zhidemai1_edit');
        Tpl::setDirquna('mobile');
        Tpl::showpage('mb_special_item.list');
    }
    public function app_zhidemai2_editOp() {
        /** @var mb_specialModel $model_mb_special */
        $model_mb_special = Model('mb_special');

        $special_item_list = $model_mb_special->getMbSpecialItemListByID($model_mb_special::APP_ZHIDEMAI2_SPECIAL_ID);
        Tpl::output('list', $special_item_list);
        Tpl::output('page', $model_mb_special->showpage(2));

        Tpl::output('module_list', $model_mb_special->getMbSpecialModuleList());
        Tpl::output('special_id', $model_mb_special::APP_ZHIDEMAI2_SPECIAL_ID);

        $this->show_menu('app_zhidemai2_edit');
        Tpl::setDirquna('mobile');
        Tpl::showpage('mb_special_item.list');
    }
    public function app_zhidemai3_editOp() {
        /** @var mb_specialModel $model_mb_special */
        $model_mb_special = Model('mb_special');

        $special_item_list = $model_mb_special->getMbSpecialItemListByID($model_mb_special::APP_ZHIDEMAI3_SPECIAL_ID);
        Tpl::output('list', $special_item_list);
        Tpl::output('page', $model_mb_special->showpage(2));

        Tpl::output('module_list', $model_mb_special->getMbSpecialModuleList());
        Tpl::output('special_id', $model_mb_special::APP_ZHIDEMAI3_SPECIAL_ID);

        $this->show_menu('app_zhidemai3_edit');
        Tpl::setDirquna('mobile');
        Tpl::showpage('mb_special_item.list');
    }
    public function app_zhidemai4_editOp() {
        /** @var mb_specialModel $model_mb_special */
        $model_mb_special = Model('mb_special');

        $special_item_list = $model_mb_special->getMbSpecialItemListByID($model_mb_special::APP_ZHIDEMAI4_SPECIAL_ID);
        Tpl::output('list', $special_item_list);
        Tpl::output('page', $model_mb_special->showpage(2));

        Tpl::output('module_list', $model_mb_special->getMbSpecialModuleList());
        Tpl::output('special_id', $model_mb_special::APP_ZHIDEMAI4_SPECIAL_ID);

        $this->show_menu('app_zhidemai4_edit');
        Tpl::setDirquna('mobile');
        Tpl::showpage('mb_special_item.list');
    }

    /**
     * 编辑专题
     */
    public function special_editOp() {
        $model_mb_special = Model('mb_special');

        $special_item_list = $model_mb_special->getMbSpecialItemListByID($_GET['special_id']);
        
        Tpl::output('list', $special_item_list);
        Tpl::output('page', $model_mb_special->showpage(2));

        Tpl::output('module_list', $model_mb_special->getMbSpecialModuleList());
        Tpl::output('special_id', $_GET['special_id']);
        Tpl::setDirquna('mobile');
        Tpl::showpage('mb_special_item.list');
    }

    /**
     * 专题项目添加
     */
    public function special_item_addOp() {
        $model_mb_special = Model('mb_special');

        $param = array();
        $param['special_id'] = $_POST['special_id'];
        $param['item_type'] = $_POST['item_type'];

        //广告只能添加一个
        if($param['item_type'] == 'adv_list') {
            $result = $model_mb_special->isMbSpecialItemExist($param);
            if($result) {
                echo json_encode(array('error' => '广告条板块只能添加一个'));die;
            }
        }
		//限时折扣只能添加一个
        if($param['item_type'] == 'goods1') {
            $result = $model_mb_special->isMbSpecialItemExist($param);
            if($result) {
                echo json_encode(array('error' => '限时折扣板块只能添加一个'));die;
            }
        }
		//团购板块只能添加一个
        if($param['item_type'] == 'goods2') {
            $result = $model_mb_special->isMbSpecialItemExist($param);
            if($result) {
                echo json_encode(array('error' => '团购板块只能添加一个'));die;
            }
        }

        $item_info = $model_mb_special->addMbSpecialItem($param);
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
        $model_mb_special = Model('mb_special');

        $condition = array();
        $condition['item_id'] = $_POST['item_id'];

        $result = $model_mb_special->delMbSpecialItem($condition, $_POST['special_id']);
        if($result) {
            echo json_encode(array('message' => '删除成功'));die;
        } else {
            echo json_encode(array('error' => '删除失败'));die;
        }
    }

    /**
     * 专题项目编辑
     */
    public function special_item_editOp() {
        /** @var mb_specialModel $model_mb_special */
        $model_mb_special = Model('mb_special');

        $item_info = $model_mb_special->getMbSpecialItemInfoByID($_GET['item_id']);
        Tpl::output('item_info', $item_info);

        if($item_info['special_id'] == 0) {
            $this->show_menu('index_edit');
        } else {
            $this->show_menu('special_item_list');
        }
        Tpl::setDirquna('mobile');
        Tpl::showpage('mb_special_item.edit');
    }

    /**
     * 专题项目保存
     */
    public function special_item_saveOp() {
        /** @var mb_specialModel $model_mb_special */
        $model_mb_special = Model('mb_special');

        $result = $model_mb_special->editMbSpecialItemByID(array('item_data' => $_POST['item_data']), $_POST['item_id'], $_POST['special_id']);

        if($result) {
            if($_POST['special_id'] == $model_mb_special::INDEX_SPECIAL_ID) {
                showMessage(L('nc_common_save_succ'), urlAdminMobile('mb_special', 'index_edit'));
            } else {
                showMessage(L('nc_common_save_succ'), urlAdminMobile('mb_special', 'special_edit', array('special_id' => $_POST['special_id'])));
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
            $data['image_url'] = getMbSpecialImageUrl($data['image_name']);
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
        Tpl::setDirquna('mobile');
        Tpl::showpage('mb_special_widget.goods', 'null_layout');
    }

    /**
     * 文章列表
     */
    public function article_listOp() {
        /** @var cms_articleModel $model_article */
        $model_article = Model('cms_article');
        $condition = array();
        $condition['article_title'] = array('like', '%' . $_GET['keyword'] . '%');
        $goods_list = $model_article->getList($condition, 10);
        Tpl::output('goods_list', $goods_list);
        Tpl::output('show_page', $model_article->showpage());
        Tpl::setDirquna('mobile');
        Tpl::showpage('mb_special_widget.article', 'null_layout');
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
        Tpl::setDirquna('mobile');
        Tpl::showpage('mb_special_widget.goods', 'null_layout');
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
        Tpl::setDirquna('mobile');
        Tpl::showpage('mb_special_widget.goods', 'null_layout');
    }
    /**
     * 更新项目排序
     */
    public function update_item_sortOp() {
        $item_id_string = $_POST['item_id_string'];
        $special_id = $_POST['special_id'];
        if(!empty($item_id_string)) {
            $model_mb_special = Model('mb_special');
            $item_id_array = explode(',', $item_id_string);
            $index = 0;
            foreach ($item_id_array as $item_id) {
                $result = $model_mb_special->editMbSpecialItemByID(array('item_sort' => $index), $item_id, $special_id);
                $index++;
            }
        }
        $data = array();
        $data['message'] = '操作成功';
        echo json_encode($data);
    }

    /**
     * 更新项目启用状态
     */
    public function update_item_usableOp() {
        $model_mb_special = Model('mb_special');
        $result = $model_mb_special->editMbSpecialItemUsableByID($_POST['usable'], $_POST['item_id'], $_POST['special_id']);
        $data = array();
        if($result) {
            $data['message'] = '操作成功';
        } else {
            $data['error'] = '操作失败';
        }
        echo json_encode($data);
    }

    public function default_xianshi_pic_editOp() {
        /** @var settingModel $model_setting */
        $model_setting = Model('setting');
        if (chksubmit()){
            if ($_FILES['default_xianshi_pic']['tmp_name'] != ''){
                $upload = new UploadFile();
                $upload->set('default_dir',ATTACH_MOBILE);
                $result = $upload->upfile('default_xianshi_pic');
                if ($result){
                    $_POST['default_xianshi_pic'] = $upload->file_name;
                }else {
                    showMessage($upload->error);
                }
            }
            $update_array = array();
            $update_array['default_xianshi_pic']   = $_POST['default_xianshi_pic'];
            $result = $model_setting->updateSetting($update_array);
            if ($result){
                $this->log('编辑账号同步，默认秒杀头部图片');
                showMessage(Language::get('nc_common_save_succ'),  urlAdminMobile('mb_special', 'index'));
            }else {
                showMessage(Language::get('nc_common_save_fail'));
            }
        }
        $list_setting = $model_setting->getListSetting();
        Tpl::output('default_xianshi_pic',$list_setting['default_xianshi_pic']);
        Tpl::setDirquna('mobile');
        Tpl::showpage('mb_default_xs.index');
    }

    /**
     * 页面内导航菜单
     * @param string    $menu_key   当前导航的menu_key
     * @param array     $array      附加菜单
     * @return
     */
    private function show_menu($menu_key='') {
        $menu_array = array(
            array('menu_key'=>'index_edit', 'menu_name'=>'首页', 'menu_url'=>$menu_key != 'index_edit'?urlAdminMobile('mb_special', 'index'):'javascript:;'),
            array('menu_key'=>'app_index_edit', 'menu_name'=>'App首页', 'menu_url'=>$menu_key != 'app_index_edit'?urlAdminMobile('mb_special', 'app_index'):'javascript:;'),
            array('menu_key'=>'app_category_edit', 'menu_name'=>'App分类', 'menu_url'=>$menu_key != 'app_category_edit'?urlAdminMobile('mb_special', 'app_category_edit'):'javascript:;'),
            array('menu_key'=>'app_faxian_edit', 'menu_name'=>'发现', 'menu_url'=>$menu_key != 'app_faxian_edit'?urlAdminMobile('mb_special', 'app_faxian_edit'):'javascript:;'),
            array('menu_key'=>'app_shihua_edit', 'menu_name'=>'食话', 'menu_url'=>$menu_key != 'app_shihua_edit'?urlAdminMobile('mb_special', 'app_shihua_edit'):'javascript:;'),
            array('menu_key'=>'app_shilv_edit', 'menu_name'=>'食旅', 'menu_url'=>$menu_key != 'app_shilv_edit'?urlAdminMobile('mb_special', 'app_shilv_edit'):'javascript:;'),
            array('menu_key'=>'app_zhidemai1_edit', 'menu_name'=>'值得买', 'menu_url'=>$menu_key != 'app_zhidemai1_edit'?urlAdminMobile('mb_special', 'app_zhidemai1_edit'):'javascript:;'),
            array('menu_key'=>'app_zhidemai2_edit', 'menu_name'=>'聚食惠', 'menu_url'=>$menu_key != 'app_zhidemai2_edit'?urlAdminMobile('mb_special', 'app_zhidemai2_edit'):'javascript:;'),
            array('menu_key'=>'app_zhidemai3_edit', 'menu_name'=>'每日鲜', 'menu_url'=>$menu_key != 'app_zhidemai3_edit'?urlAdminMobile('mb_special', 'app_zhidemai3_edit'):'javascript:;'),
            array('menu_key'=>'app_zhidemai4_edit', 'menu_name'=>'家乡味道', 'menu_url'=>$menu_key != 'app_zhidemai4_edit'?urlAdminMobile('mb_special', 'app_zhidemai4_edit'):'javascript:;'),
            array('menu_key'=>'default_xianshi_pic', 'menu_name'=>'秒杀头部图', 'menu_url'=>$menu_key != 'default_xianshi_pic'?urlAdminMobile('mb_special', 'default_xianshi_pic_edit'):'javascript:;'),
            array('menu_key'=>'special_list', 'menu_name'=>'专题', 'menu_url'=>$menu_key != 'special_list'?urlAdminMobile('mb_special', 'special_list'):'javascript:;'),
        );
        $itemTitles = array(
            'index_edit'=>'首页',
            'app_index_edit'=>'App首页',
            'app_category_edit'=>'App分类',
            'app_faxian_edit'=>'发现',
            'app_shihua_edit'=>'食话',
            'app_shilv_edit'=>'食旅',
            'app_zhidemai1_edit'=>'值得买',
            'app_zhidemai2_edit'=>'聚食惠',
            'app_zhidemai3_edit'=>'每日鲜',
            'app_zhidemai4_edit'=>'家乡味道',
            'default_xianshi_pic'=>'默认秒杀头部图',
        );
        tpl::output('item_title', isset($itemTitles[$menu_key])?$itemTitles[$menu_key]:'专题设置');
        Tpl::output('menu', $menu_array);
        Tpl::output('menu_key', $menu_key);
    }
}
