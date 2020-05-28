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
class fenxiao_goodsControl extends BaseSellerControl {
    public function __construct() {
        parent::__construct ();
        Language::read ('member_store_goods_index');
    }
    public function indexOp() {
        $this->goodsOp();
    }

    /**
     * 分销商品列表
     */
    public function goodsOp() {
        $action = $_GET['action'];
        if (is_null($action) || empty($action)) $action = 'getlist';
        $model_goods = Model('goods');
        switch ($action) {
            case 'getlist' :
                $goodsname = $_GET['goods_name'];
                $store_name = $_GET['store_name'];

                $conditions = array();
                $conditions['is_del'] = 0;
                $conditions['goods_state'] = 1;
                if (!empty($goodsname))
                    $conditions['goods_name'] = array('like', '%' . $goodsname . '%');
                if (!empty($store_name)) {
                    // 先查出store_id
                    $store_list = Model('store')->getStoreList(array('store_name' => array('like', '%'.$store_name.'%')));
                    $store_ids = array_column($store_list, 'store_id');
                    $conditions['store_id'] = array('in', $store_ids);
                }

                $result = $model_goods->getFenxiaoGoodsList($conditions);
                Tpl::output('show_page', $model_goods->showpage());
                $this->profile_menu('fenxiao_goods');
                Tpl::output('goods_list', $result);
                Tpl::showpage('fenxiao_goods.index');
                break;
            case 'getdistributorgoodslist' :

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

                Tpl::output('show_page', $model_goods->showpage());
                $this->profile_menu('fenxiao_goods_f');
                Tpl::output('goods_list', $result);
                Tpl::output('fenxiao_list', $fenxiao_list);
                Tpl::output('store_info', $this->store_info);
                Tpl::showpage('fenxiao_goods.index_f');
                break;
            case 'add' :
                $catename = $_GET['goods_name'];
                $pid = $_GET['pid'];
                $gid = $_GET['gid'];
                if ($_POST) {
                    $data['catename'] = $_POST['goods_name'];
                    $data['pid'] = $_POST['pid'];
                    $data['gid'] = $_POST['gid'];
                    $data['fxpid'] = 0;
                    $data['ctime'] = time();
                    $uids = $_POST['uid'];
                    if (empty($uids)) {
                        showMessage("请选择渠道！","index.php?act=fenxiao_goods&op=index&action=getlist", 'json');
                        exit ;
                    }
                    $goods = $model_goods->table('goods')->where(array('goods_id'=>$data['pid']))->find();
                    $storeModel = Model('store');
                    $store = $storeModel->table('store')->where(array('store_id'=>$goods['store_id']))->find();
                    if ($store['manage_type'] != 'platform'&& $goods['tax_input']==200) {
                        showMessage("添加失败，共建商品税率未设置！","index.php?act=fenxiao_goods&op=index&action=getlist", 'json');
                        exit ;
                    }
                    $category = Model('b2c_category');
                    $uids = explode(',', trim($uids));
                    foreach ($uids as $uid) {
                        $data['uid'] = $uid;
                        $category -> addCategory($data, '', $uid, $data['pid']);
                    }
                    showMessage("添加成功！","index.php?act=fenxiao_goods&op=index&action=getdistributorgoodslist", 'json');
                    exit ;
                }

                // 获取该门店渠道
                $member_fenxiao = Model('member_fenxiao')->getMembeFenxiaoList(array('filter_store_id'=>$this->store_info['store_id']));
                Tpl::output('member_fenxiao', $member_fenxiao);
                Tpl::output('pid', $pid);
                Tpl::output('gid', $gid);
                Tpl::output('catename', $catename);
                Tpl::setLayout('null_layout');
                Tpl::showpage('fenxiao_goods.add');

                break;
            case 'del' :
                $id = intval($_POST['id']);
                $uid = intval($_POST['uid']);
                $category = Model('b2c_category');
                $url = "index.php?act=fenxiao_goods&op=index&action=getdistributorgoodslist";
                if ($category->delCategory($uid, $id)) {
                    showMessage("删除成功！",$url, 'json');
                    exit ;
                }
                showMessage("服务器繁忙！！",$url, 'json');
                exit ;
        }
    }

    public function save_fenxiaoOp()
    {
        $id = intval($_REQUEST['id']);
        $category_model = Model('b2c_category');

        if ($_POST) {
            $fxpid = intval($_POST['fxpid']);
            $uid = intval($_POST['uid']);
            $fxprice = trim($_POST['fxprice']);
            $fxcost = trim($_POST['fxcost']);
            $multiple_goods = intval($_POST['multiple_goods']);
            $result = $category_model->checkfxpid($uid, $fxpid, $id, $fxprice);
            $url = "index.php?act=fenxiao_goods&op=index&action=getdistributorgoodslist";
            if ($result) {
                $data = array(
                    'fxpid' => $fxpid,
                    'fxprice' => $fxprice,
                    'fxcost' => $fxcost,
                    'multiple_goods' => $multiple_goods
                );
                $category_model->where('id=' . $id . ' AND uid=' . $uid)->update($data);
                showMessage("设置成功！",$url, 'json');
                exit ;
            } else {
                showMessage("设置失败！",$url, 'json');
                exit ;
            }
            showMessage("数据已经存在！",$url, 'json');
            exit ;
        }

        $category = $category_model->where(array('id' => $id))->find();
        if (empty($category)) {
            echo '<h1 style="padding: 30px;30px;">没有分销的商品</h1>';die;
        }

        Tpl::output('category', $category);
        Tpl::output('id', $id);
        Tpl::setLayout('null_layout');
        Tpl::showpage('fenxiao_goods.save_category');
    }

    /**
     * 用户中心右边，小导航
     *
     * @param string $menu_key 当前导航的menu_key
     * @return
     */
    private function profile_menu($menu_key = '') {
        $menu_array = array(
            array('menu_key' => 'fenxiao_goods',    'menu_name' => '商品列表',    'menu_url' => urlShop('fenxiao_goods', 'index', array('action' => 'getlist'))),
            array('menu_key' => 'fenxiao_goods_f',    'menu_name' => '我的分销商品',    'menu_url' => urlShop('fenxiao_goods', 'index', array('action' => 'getdistributorgoodslist'))),
        );
        Tpl::output ( 'member_menu', $menu_array );
        Tpl::output ( 'menu_key', $menu_key );
    }
}
