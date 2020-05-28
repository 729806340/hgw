<?php
/**
 * 商品类别模型
 *
 *
 *
 * * @汉购网 (c) 2015-2018 ShopWWI Inc. (http://www.hangowa.com)
 * @license    http://www.shopwwi.c om
 * @link       交流群号：
 * @since      汉购网提供技术支持 授权请购买shopnc授权
 */

defined('ByShopWWI') or exit('Access Invalid!');

class shequ_config_goods_classModel extends Model
{

    public function __construct() {
        parent::__construct('shequ_config_goods_class');
    }


    public function getItems($condition, $page=null, $order='', $field='*') {
        return $this->field($field)->where($condition)->page($page)->order($order)->select();
    }
    public function getGoodsClassInfo($condition) {
        return $this->where($condition)->find();
    }
    public function edit($condition,$data){
        return $this->where($condition)->update($data);
    }

}
