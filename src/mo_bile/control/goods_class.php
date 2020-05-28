<?php
/**
 * 商品分类
 *
 *
 *
 * @copyright  Copyright (c) 2007-2015 ShopNC Inc. (http://www.shopnc.net)
 * @license    http://www.shopnc.net
 * @link       http://www.shopnc.net
 * @since      File available since Release v1.1
 */



defined('ByShopWWI') or exit('Access Invalid!');
class goods_classControl extends mobileHomeControl{

    public function __construct() {
        parent::__construct();
    }

    public function indexOp() {
        if(!empty($_GET['gc_id']) && intval($_GET['gc_id']) > 0) {
            $this->_get_class_list($_GET['gc_id']);
        } else {
            $this->_get_root_class();
        }
    }
	

	public function get_child_allOp() {
        if(!empty($_GET['gc_id']) && intval($_GET['gc_id']) > 0) {
            $this->_get_class_list($_GET['gc_id']);
        }
    }
    /**
     * 返回一级分类列表
     */
    private function _get_root_class_bk() {
        $model_goods_class = Model('goods_class');
        $model_mb_category = Model('mb_category');

        $goods_class_array = Model('goods_class')->getGoodsClassForCacheModel();

        $class_list = $model_goods_class->getGoodsClassListByParentId(0);
        $mb_categroy = $model_mb_category->getLinkList(array());
        $mb_categroy = array_under_reset($mb_categroy, 'gc_id');
        foreach ($class_list as $key => $value) {
            if(!empty($mb_categroy[$value['gc_id']])) {
                $class_list[$key]['image'] = UPLOAD_SITE_URL.DS.ATTACH_MOBILE.DS.'category'.DS.$mb_categroy[$value['gc_id']]['gc_thumb'];
            } else {
                $class_list[$key]['image'] = '';
            }

            $class_list[$key]['text'] = '';
            $child_class_string = $goods_class_array[$value['gc_id']]['child'];
            $child_class_array = explode(',', $child_class_string);
            foreach ($child_class_array as $child_class) {
                $class_list[$key]['text'] .= $goods_class_array[$child_class]['gc_name'] . '/';
            }
            $class_list[$key]['text'] = rtrim($class_list[$key]['text'], '/');
        }
        output_data(array('class_list' => $class_list));
    }

    /**
     * 根据分类编号返回下级分类列表
     */
    private function _get_class_list_bk($gc_id,$type='data') {
        $goods_class_array = Model('goods_class')->getGoodsClassForCacheModel();
        
        $goods_class = $goods_class_array[$gc_id];
        
        if(empty($goods_class['child'])) {
            //无下级分类返回0
			if($type=='data'){
				output_data(array('class_list' => '0'));
			}
        } else {
            //返回下级分类列表
            $class_list = array();
            $child_class_string = $goods_class_array[$gc_id]['child'];
            $child_class_array = explode(',', $child_class_string);
            foreach ($child_class_array as $child_class) {
                $class_item = array();
                $class_item['gc_id'] .= $goods_class_array[$child_class]['gc_id'];
                $class_item['gc_name'] .= $goods_class_array[$child_class]['gc_name'];
                
				//if($type=='array'){
					$class_item['child'] = $this->_get_class_list($child_class,'array');
				//}
				//$class_item['child'] = '--------'.$type;
				$class_list[] = $class_item;
            }

			if($type=='data'){
				output_data(array('class_list' => $class_list));
			}else{
				return $class_list;
			}
        }
    }

    /**
     * 返回一级分类列表
     */
    private function _get_root_class() {
        /** @var goods_categoryModel $model_cat */
        $model_cat = Model('goods_category');
        $data = $model_cat->getGoodsCategoryForCacheModel();
        output_data(array('category_list' => $data));
    }

    private function _get_class_list($cat_id) {
        $model_cat = Model('goods_category');
        $data = $model_cat->getGoodsCategoryForCacheModel();
        $result = array();
        foreach ($data as $k => $v) {
            if($v['cat_id'] == $cat_id){
                $result = $v['child'];
            }
        }
        if(empty($result)){
            output_data(array('class_list' => '0'));
        }
        output_data(array('class_list' => $result));
    }
}
