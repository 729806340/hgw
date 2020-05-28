<?php
/**
 * 精斗云映射
 */
defined('ByShopWWI') or exit('Access Invalid!');

class jdy_mappingControl extends SystemControl{
	public function __construct(){
        parent::__construct();
    }

    public function indexOp(){
		Tpl::setDirquna('shop');
        Tpl::showpage('jdy_mapping.index');
    }

    public function index_xmlOp(){
        $condition = array();
    	/** @var goodsModel $model_goods */
        $model_goods = Model('goods');
        /** @var jdy_mappingModel $model_jdy_mapping */
        $model_jdy_mapping = Model('jdy_mapping');
        if ($_POST['query'] != '') {
            $condition[$_POST['qtype']] = array('like', '%' . $_POST['query'] . '%');
        }
        if ($_GET['query_key'] != '') {
            $condition[$_GET['qtype_key']] = array('like', '%' . $_GET['query_key'] . '%');
        }
        //显示删除的
        $condition['is_del'] = -1;
        $condition['is_show_manage'] = 1;
        $page = $_POST['rp'] > 0  ? $_POST['rp'] : 10;

        if (in_array($_GET['mapping_state'], array(1, 2))) {
            //$condition['goods_state'] = 1;
            //$condition['goods_verify'] = 1;
            //$condition['is_del'] = 0 ;
            $new_condition = array();
            foreach ($condition as $kk1=>$zz1) {
                $new_condition['goods.'. $kk1] = $zz1;
            }
            if ($_GET['mapping_state'] == 1) {
                $new_condition['jdy_mapping.goods_id'] = array('gt', 0);
            } else {
                $new_condition['jdy_mapping.goods_id']=array('EXP','jdy_mapping.goods_id IS NULL');
            }
            $on = 'goods.goods_id = jdy_mapping.goods_id';
            $list = $model_goods->table('goods,jdy_mapping')->join('left')->on($on)->field('goods.*')->where($new_condition)->page($page)->select();
        } else {

            $list = $model_goods->getGoodsList($condition,'*','','goods_id DESC',0,$page);
        }
        $goods_ids = array_column($list, 'goods_id');
        $mapping_list = $model_jdy_mapping->getList(array('goods_id' => array('in', $goods_ids)));
        $mapping_list = array_under_reset($mapping_list, 'goods_id');
        $goods_list = array();
        foreach ($list as $value) {
            $mapping_data = isset($mapping_list[$value['goods_id']]) ? $mapping_list[$value['goods_id']] : array();
            if (empty($mapping_data)) {
                $operation = '<a class="btn green do-mapping" data-uri="index.php?act=jdy_mapping&op=mapping&goods_id='.$value['goods_id'].'" href="javascript:void(0)">映射</a>';
            }else{
                $operation = '<a class="btn red un-mapping" data-goods-id="'. $value['goods_id'] .'" href="javascript:void(0)">取消映射</a>';
            }
            $goods_list[] = array(
                'goods_id' => $value['goods_id'],
                'goods_name' => $value['goods_name'],
                'goods_price' => $value['goods_price'],
                'goods_storage' => $value['goods_storage'],
                'is_mapping' => !empty($mapping_data) ? '是' : '否',
                'mapping_info' => !empty($mapping_data) ? $mapping_data['item_name']. '|'. $mapping_data['supplier'] : '',
                'operation' => $operation,
            );
        }
        $data = array();
        $data['list'] = $goods_list;
        $data['now_page'] = $model_goods->shownowpage();
        $data['total_num'] = $model_goods->gettotalnum();
        Tpl::flexigridXML($data);
        exit();
    }

    public function mappingOp() {
        /** @var goodsModel $model_goods */
        $model_goods = Model('goods');
        $goods_id = intval($_GET['goods_id']);
        $goods_info = $model_goods->getGoodsInfo(array('goods_id' => $goods_id));
        $error = '';
        if (empty($goods_info)) {
            $error = "非法操作";
        }
        Tpl::output('error', $error);
        Tpl::output('goods_info', $goods_info);
        $item_name = trim($_GET['item_name']);
        /** @var jdy_goods_stockModel $model_jdy_goods_stock */
        $model_jdy_goods_stock = Model('jdy_goods_stock');
        $condition = array();
        if ($item_name) {
            $condition['item_name'] = array('like', '%' . $item_name . '%');
        }
        $jdy_goods_list = $model_jdy_goods_stock->getList($condition, 10);
        Tpl::output('jdy_goods_list', $jdy_goods_list);
        Tpl::output('show_page',$model_jdy_goods_stock->showpage());
        Tpl::setDirquna('shop');
        Tpl::showpage('jdy_mapping.mapping', false);
    }

    public function supplierOp() {
        $supplier_catetory_name = trim($_GET['supplier_catetory_name']);
        $query_supplier_key = trim($_GET['query_supplier_key']);
        $query_supplier_value = trim($_GET['query_supplier_value']);
        $query_link_key = trim($_GET['query_link_key']);
        $query_link_value = trim($_GET['query_link_value']);
        $condition = array(
            'supplier_status' => 1,
        );
        if ($supplier_catetory_name) {
            $condition['supplier_catetory_name'] = array('like', '%' . $supplier_catetory_name . '%');
        }
        if ($query_supplier_key && $query_supplier_value) {
            $condition[$query_supplier_key] = array('like', '%' . $query_supplier_value . '%');
        }
        if ($query_link_key && $query_link_value) {
            $condition[$query_link_key] = array('like', '%' . $query_link_value . '%');
        }
        /** @var jdy_supplierModel $jdy_supplier_model */
        $jdy_supplier_model = Model('jdy_supplier');
        $supplier_list = $jdy_supplier_model->getList($condition, 10);
        Tpl::output('supplier_list', $supplier_list);
        Tpl::output('show_page',$jdy_supplier_model->showpage());
        Tpl::setDirquna('shop');
        Tpl::showpage('jdy_mapping.supplier', false);
    }

    public function mapping_saveOp() {
        /** @var jdy_mappingModel $model_jdy_mapping */
        $model_jdy_mapping = Model('jdy_mapping');
        $exit_data = $model_jdy_mapping->getItemInfo(array('goods_id' => $_POST['goods_id']));
        if (!empty($exit_data)) {
            $data['result'] = false;
            $data['message'] = '该商品已经映射过了';
            echo json_encode($data);die;
        }
        $item_id = trim($_POST['item_id']);
        $supplier_unique_id = trim($_POST['supplier_unique_id']);
        $multiple = intval($_POST['multiple']);
        /** @var jdy_goods_stockModel $model_jdy_goods */
        $model_jdy_goods = Model('jdy_goods_stock');
        /** @var jdy_supplierModel $model_jdy_supplier */
        $model_jdy_supplier = Model('jdy_supplier');
        /** @var goodsModel $model_goods */
        $model_goods = Model('goods');
        $goods_stock_data = $model_jdy_goods->getItemInfo(array('item_id' => $item_id));
        $supplier_info = $model_jdy_supplier->getItemInfo(array('supplier_unique_id' => $supplier_unique_id));
        $goods_info = $model_goods->getGoodsInfo(array('goods_id' => $_POST['goods_id']));
        if (empty($goods_stock_data) || empty($supplier_info) || empty($goods_info) || $multiple <= 0) {
            $data['result'] = false;
            $data['message'] = '参数不正确';
            echo json_encode($data);die;
        }
        $insert_arr = array(
            'goods_id' => $_POST['goods_id'],
            'item_id' => $item_id,
            'item_code' => $goods_stock_data['item_code'],
            'item_name' => $goods_stock_data['item_name'],
            'store_id' => $goods_info['store_id'],
            'supplier_number' => $supplier_info['supplier_number'],
            'supplier' => $supplier_info['supplier_name'],
            'unit_multiple' => $multiple,
            'unit_no' => $goods_stock_data['unit_number'],
            'unit_name' =>  $goods_stock_data['unit_name']
        );
        $result = $model_jdy_mapping->addItem($insert_arr);
        if (!$result) {
            $data['result'] = false;
            $data['message'] = '映射失败';
            echo json_encode($data);die;
        } else {
            $data['result'] = true;
            $data['message'] = '成功';
            echo json_encode($data);die;
        }
    }

    public function mapping_backOp() {
        /** @var jdy_mappingModel $model_jdy_mapping */
        $model_jdy_mapping = Model('jdy_mapping');
        $goods_id = intval($_POST['goods_id']);
        $exit_data = $model_jdy_mapping->getItemInfo(array('goods_id' => $goods_id));
        if (empty($exit_data)) {
            $data['result'] = true;
            $data['message'] = '成功';
            echo json_encode($data);die;
        }
        $result = $model_jdy_mapping->delItem(array('goods_id' => $goods_id));
        if (!$result) {
            $data['result'] = false;
            $data['message'] = '取消失败';
            echo json_encode($data);die;
        } else {
            $data['result'] = true;
            $data['message'] = '成功';
            echo json_encode($data);die;
        }
    }

}