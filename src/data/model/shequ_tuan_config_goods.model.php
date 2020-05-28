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
class shequ_tuan_config_goodsModel extends Model{

    public function __construct(){
        parent::__construct('shequ_tuan_config_goods');
    }

    /**
     * 读取社区团购配置商品列表
     * @param array $condition 查询条件
     * @param int $page 分页数
     * @param string $order 排序
     * @param string $field 所需字段
     * @return array 限时折扣列表
     *
     */
    public function getTuanConfigGoodsList($condition, $page=null, $order='', $field='*',$limit='') {
        return $this->field($field)->where($condition)->page($page)->order($order)->limit($limit)->select();
    }


    public function getTuanConfigGoodsInfo($condition) {
        return $this->where($condition)->find();
    }

    public function addTuanConfigGoods($param){
        return $this->insert($param);
    }

    public function edit($condition,$data){
        return $this->where($condition)->update($data);
    }


}
