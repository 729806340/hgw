<?php
/**
 * 社区团购活动商品模型
 *
 * * @汉购网 (c) 2015-2018 ShopWWI Inc. (http://www.hangowa.com)
 * @license    http://www.shopwwi.c om
 * @link       交流群号：
 * @since      汉购网提供技术支持 授权请购买shopnc授权
 */
defined('ByShopWWI') or exit('Access Invalid!');
class shequ_xianshiModel extends Model{

    public function __construct(){
        parent::__construct('shequ_xianshi');
    }

    /**
     * 读取社区团购活动商品配置商品列表
     * @param array $condition 查询条件
     * @param int $page 分页数
     * @param string $order 排序
     * @param string $field 所需字段
     * @return array 限时折扣列表
     *
     */
    public function getXianShiConfigList($condition, $page=null, $order='', $field='*') {
        return  $this->field($field)->where($condition)->page($page)->order($order)->select();

    }

    public function getXianShiGoodsInfo($condition) {
        return $this->where($condition)->find();
    }

    public function addXianShiGoods($param){
        return $this->insert($param);
    }

    public function editXianshiGoods($update, $condition){
        return $this->where($condition)->update($update);
    }

}