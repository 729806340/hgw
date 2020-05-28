<?php
/**
 * 自定义分类导航设置管理
 *
 *
 *
 * * @汉购网 (c) 2015-2018 ShopWWI Inc. (http://www.hangowa.com)
 * @license    http://www.shopwwi.c om
 * @link       交流群号：
 * @since      汉购网提供技术支持 授权请购买shopnc授权
 */

defined('ByShopWWI') or exit('Access Invalid!');

class goods_category_navModel extends Model {
    
    public function __construct() {
        parent::__construct('goods_category_nav');
    }

    public function getGoodsCategoryById($cat_id)
    {
        $condition['cat_id'] = $cat_id;
        return Model('goods_category_nav')->where($condition)->find();
    }

    public function getCategoryDetail($cat_id)
    {
        $condition['cat_id'] = $cat_id;
        $cat = Model('goods_category')->where($condition)->find();
        
        $nav_info = Model('goods_category_nav')->where($condition)->select();
        if ($nav_info){
            foreach ($nav_info as $k => $v) {
                if($v['type'] == 'brand'){
                    $nav_info['brand'][] = $v;
                }else{
                    $nav_info['ad'][] = $v;
                }
                unset($nav_info[$k]);
            }
        }
        $cat['nav'] = $nav_info['ad'];
        return $cat;
    }

}
