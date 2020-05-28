<?php
/**
 * 分销商品管理
 *
 *
 *
 * * @汉购网 (c) 2015-2018 ShopWWI Inc. (http://www.hangowa.com)
 * @license    http://www.shopwwi.c om
 * @link       交流群号：
 * @since      汉购网提供技术支持 授权请购买shopnc授权
 */



defined('ByShopWWI') or exit ('Access Invalid!');
class fenxiao_goods2Control extends BaseSellerControl {
    public function __construct() {
        parent::__construct ();
        Language::read ('member_store_goods_index');
    }
    public function indexOp() {
        $this->goodsOp();
    }

    /**
     * 渠道商品列表
     */
    public function goodsOp() {
        $action = $_GET['action'];
        if (is_null($action) || empty($action)) $action = 'getlist';
        $model_goods = Model('goods');
        switch ($action) {
            case 'getlist' :
                $goodsname = $_GET['goods_name'];
                $fenxiao_id = $_GET['fenxiao_id'];
                /** @var B2cCategoryModel $product */
                $product = Model('b2c_category');
                if (!empty($goodsname))
                    $conditions['catename'] = array('like', '%' . $goodsname . '%');

                $store_id = $this->store_info['store_id'];
                $conditions2['filter_store_id'] = $store_id;
                if (!empty($fenxiao_id))
                    $conditions2['id'] = $fenxiao_id;

                $member_fenxiao = Model('member_fenxiao')->getMembeFenxiaoList($conditions2);
                $member_ids = array_column($member_fenxiao, 'member_id');

                $conditions['uid'] = array('in', $member_ids);
                $result = $product->getCategoryList($conditions);

                // 列出渠道
                $fenxiao_list = Model('member_fenxiao')->getMembeFenxiaoList(array('filter_store_id' => $store_id));
                $fenxiao_list = array_under_reset($fenxiao_list, 'member_id');
                // 列出快递公司
                $express = Model('express')->getExpressList();
                // 列出供应商
                $store_supplier = Model('store_supplier')->getStoreSupplierList(array('sup_store_id' => $this->store_info['store_id']));
                $store_supplier = array_under_reset($store_supplier, 'sup_id');

                Tpl::output('show_page', $model_goods->showpage());
                Tpl::output('express', $express);
                Tpl::output('store_supplier', $store_supplier);
                Tpl::output('goods_list', $result);
                Tpl::output('fenxiao_list', $fenxiao_list);
                Tpl::output('store_info', $this->store_info);
                Tpl::showpage('fenxiao_goods2.index');
                break;
        }
    }

    public function save_good_categoryOp()
    {
        $id = intval($_REQUEST['id']);
        $category_model = Model('b2c_category');

        if ($_POST) {
            $uid = intval($_POST['uid']);
            $package_count = intval($_POST['package_count']);
            $freight_cost = trim($_POST['freight_cost']);
            $express_id = trim($_POST['express_id']);
            $store_supplier_id = trim($_POST['store_supplier_id']);
            $data = array(
                'package_count' => $package_count,
                'freight_cost' => $freight_cost,
                'express_id' => $express_id,
                'store_supplier_id' => $store_supplier_id,
            );

            $result = $category_model->where('id=' . $id . ' AND uid=' . $uid)->update($data);
            if ($result) {
                showMessage("设置成功！",'', 'json');
                exit ;
            } else {
                showMessage("设置失败！",'', 'json');
                exit ;
            }
        }

        $category = $category_model->where(array('id' => $id))->find();
        if (empty($category)) {
            echo '<h1 style="padding: 30px;30px;">没有分销的商品</h1>';die;
        }

        // 列出快递公司
        $express = Model('express')->getExpressList();
        // 列出供应商
        $store_supplier = Model('store_supplier')->getStoreSupplierList(array('sup_store_id' => $this->store_info['store_id']));

        Tpl::output('category', $category);
        Tpl::output('express', $express);
        Tpl::output('store_supplier', $store_supplier);
        Tpl::output('id', $id);
        Tpl::setLayout('null_layout');
        Tpl::showpage('fenxiao_goods.save_category2');
    }
}
