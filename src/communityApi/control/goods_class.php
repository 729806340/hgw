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

    // 分类列表
    public function get_class_listOp() {
        /** @var goods_classModel $model_goods_class */
        $model_goods_class = Model('goods_class');
        $class_list = $model_goods_class->getGoodsClassListByParentId(0);
        $all_category = $model_goods_class->get_all_category();
        $parent_class = array();
        $new_child_list = array();

        //过滤
        /** @var goodsModel $goods_model */
         $goods_model = Model('goods');
         $goods_list = $goods_model->getGoodsOnlineList(array(), 'gc_id_2', 0, 'goods_id desc',false);
         $gc_id_2s = array_unique(array_column($goods_list, 'gc_id_2'));

        foreach ($class_list as $key => $value) {
            if ($value['gc_id'] == 959 || $value['app_show'] == 0) {//过滤保健品 及设置不显示的
                continue;
            }
            $parent_gc_name_list = explode('/', $value['gc_name']);
            $parent_class[$value['gc_id']] = array(
                'gc_id' => $value['gc_id'],
                'gc_name' => $parent_gc_name_list[0] ? $parent_gc_name_list[0] : $value['gc_name'],
                //'child_list' => array()
            );
            if (array_key_exists($value['gc_id'], $all_category) && count($all_category[$value['gc_id']]['class2']) > 0) {
                $child_list = $all_category[$value['gc_id']]['class2'];
                foreach ($child_list as $child) {
                    if (!in_array($child['gc_id'], $gc_id_2s)) {
                        continue;
                    }
                    $gc_name_list = explode('/', $child['gc_name']);
                    $new_child_list[$value['gc_id']][" ". $child['gc_id']] = array(
                        'gc_id' => $child['gc_id'],
                        'gc_name' => $gc_name_list[0] ? $gc_name_list[0] : $child['gc_name'],
                        'gc_pic' => $child['app_img'] ? UPLOAD_SITE_URL. DS.ATTACH_COMMON.DS. $child['app_img'] : ''
                    );
                }
            }
        }
        $parent_class_key = min(array_keys($parent_class));
        $hot_goods = $this->_hot_sale($parent_class_key);
        output_data(array('parent_list' => $parent_class, 'child_list' => $new_child_list, 'hot_goods' => $hot_goods));
    }

    //新的分类列表
    public function get_class_list_newOp() {
        /** @var goods_classModel $model_goods_class */
        $model_goods_class = Model('goods_class');
        $class_list = $model_goods_class->getGoodsClassListByParentId(0);
        $all_category = $model_goods_class->get_all_category();
        $parent_class = array();
        $new_child_list = array();

        //过滤
        /** @var goodsModel $goods_model */
        $goods_model = Model('goods');
        $goods_list = $goods_model->getGoodsOnlineList(array(), 'gc_id_2', 0, 'goods_id desc',false);
        $gc_id_2s = array_unique(array_column($goods_list, 'gc_id_2'));
        $parent_class_key = 0;
        foreach ($class_list as $key => $value) {
            if (/*$value['gc_id'] == 959 ||*/ $value['app_show'] == 0) {//过滤保健品 及设置不显示的
                continue;
            }
            if (!$parent_class_key) {
                $parent_class_key = $value['gc_id'];
            }
            $parent_gc_name_list = explode('/', $value['gc_name']);
            $parent_class[] = array(
                'gc_id' => $value['gc_id'],
                'gc_name' => $parent_gc_name_list[0] ? $parent_gc_name_list[0] : $value['gc_name'],
                //'child_list' => array()
            );
            if (array_key_exists($value['gc_id'], $all_category) && count($all_category[$value['gc_id']]['class2']) > 0) {
                $child_list = $all_category[$value['gc_id']]['class2'];
                foreach ($child_list as $child) {
                    if (!in_array($child['gc_id'], $gc_id_2s)) {
                        continue;
                    }
                    $gc_name_list = explode('/', $child['gc_name']);
                    $new_child_list[$value['gc_id']][] = array(
                        'gc_id' => $child['gc_id'],
                        'gc_name' => $gc_name_list[0] ? $gc_name_list[0] : $child['gc_name'],
                        'gc_pic' => $child['app_img'] ? UPLOAD_SITE_URL. DS.ATTACH_COMMON.DS. $child['app_img'] : ''
                    );
                }
            }
        }
        $hot_goods = $this->_hot_sale($parent_class_key);
        output_data(array('parent_list' => $parent_class, 'child_list' => $new_child_list, 'hot_goods' => $hot_goods));
    }

    //新的获取3级分类列表
    public function get_child_list_newOp() {
        $gc_id = intval($_POST['gc_id']);
        $child_list = array();
        if ($gc_id <= 0) {
            output_data(array('child_list' => $child_list));
        }
        /** @var goods_classModel $model_goods_class */
        $model_goods_class = Model('goods_class');
        $all_category = $model_goods_class->getChildClass($gc_id);

        /** @var goodsModel $goods_model */
        $goods_model = Model('goods');
        $goods_list = $goods_model->getGoodsOnlineList(array('gc_id_2' => $gc_id), 'gc_id_3', 0, 'goods_id desc',false);
        $gc_id_3s = array_unique(array_column($goods_list, 'gc_id_3'));

        foreach ($all_category as $category) {
            if ($category['gc_id'] == $gc_id || !in_array($category['gc_id'], $gc_id_3s)) {
                continue;
            }
            $gc_name = explode('/', $category['gc_name']);
            $child_list[] = array(
                'gc_id' => $category['gc_id'],
                'gc_name' => $gc_name[0] ? $gc_name[0] : $category['gc_name'],
            );
        }
        output_data(array('child_list' => $child_list));
    }

    //获取3级分类列表
    public function get_child_listOp() {
        $gc_id = intval($_POST['gc_id']);
        $child_list = array();
        if ($gc_id <= 0) {
            output_data(array('child_list' => $child_list));
        }
        /** @var goods_classModel $model_goods_class */
        $model_goods_class = Model('goods_class');
        $all_category = $model_goods_class->getChildClass($gc_id);

        /** @var goodsModel $goods_model */
        $goods_model = Model('goods');
        $goods_list = $goods_model->getGoodsOnlineList(array('gc_id_2' => $gc_id), 'gc_id_3', 0, 'goods_id desc',false);
        $gc_id_3s = array_unique(array_column($goods_list, 'gc_id_3'));

        foreach ($all_category as $category) {
            if ($category['gc_id'] == $gc_id || !in_array($category['gc_id'], $gc_id_3s)) {
                continue;
            }
            $gc_name = explode('/', $category['gc_name']);
            $child_list[" ". $category['gc_id']] = array(
                'gc_id' => $category['gc_id'],
                'gc_name' => $gc_name[0] ? $gc_name[0] : $category['gc_name'],
            );
        }
        output_data(array('child_list' => $child_list));
    }

    //分类热销
    public function hot_saleOp() {
        $hot_goods = array();
        $cate_id = intval($_POST['cate_id']);
        if ($cate_id <= 0) {
            output_data(array('hot_goods' => $hot_goods));
        }
        $hot_goods = $this->_hot_sale($cate_id);
        output_data(array('hot_goods' => $hot_goods));
    }

    //热卖商品
    private function _hot_sale($cate_id) {
        $cate_id = intval($cate_id);
        if (!$cate_id) {
            return array();
        }
        /** @var goodsModel $model_goods */
        $model_goods = Model('goods');
        $condition['gc_id_1'] = $cate_id;
        $hot_goods_list = $model_goods->getGoodsOnlineList($condition, '*', 0, 'goods_salenum desc', 6);

        /** @var goodsLogic $logic_goods */
        $logic_goods = Logic('goods');
        $hot_goods_list = $logic_goods->deal_goods_list($hot_goods_list);
        return $hot_goods_list;
    }

    //查看
    public function class_infoOp() {
        $cate_id = intval($_POST['cate_id']);
        if ($cate_id <= 0) {
           output_error('参数错误');
        }
        /** @var goods_classModel $goods_class_model */
        $goods_class_model = Model('goods_class');
        $class_list = $goods_class_model->getCache();
        $class_info = $class_list['data'][$cate_id];
        if (empty($class_info)) {
            output_error('参数错误');
        }
        $data = array(
            'parent_class' => 1,
            'gc_parent_id' => $class_info['gc_parent_id']
        );
        if ($class_info['gc_parent_id'] > 0) {
            $parent_class_info = $class_list['data'][$class_info['gc_parent_id']];
            $data['parent_class'] = $parent_class_info['gc_parent_id'] > 0 ? 3 : 2;
            $data['gc_parent_id'] = $parent_class_info['gc_parent_id'] > 0 ? $parent_class_info['gc_parent_id'] : $class_info['gc_parent_id'];
        }
        output_data($data);

    }

}
