<?php
/**
 * jdy商品库存模型
 * * @汉购网 (c) 2015-2018 ShopWWI Inc. (http://www.hangowa.com)
 * @license    http://www.shopwwi.com
 * @link       交流群号：
 * @since      汉购网提供技术支持 授权请购买shopnc授权
 */
defined('ByShopWWI') or exit('Access Invalid!');

class jdy_goods_stockModel extends Model{

    protected $tableName = 'jdy_goods_stock';

    /**
     * 读取列表
     * @param array $condition
     */
    public function getList($condition, $page='', $order='', $field='*') {
        return $this->field($field)->where($condition)->page($page)->order($order)->select();
    }

    /*
     * 增加
     * @param array $param
     * @return bool
     */
    public function addItem($param){
        return $this->insert($param);
    }

    /**
     * 更新
     * @param $data
     * @param $condition
     * @return bool
     */
    public function editItem($data, $condition) {
        return $this->where($condition)->update($data);
    }

    /*
     * 查找单条记录
     * @param array $condition
     * @return array
     */
    public function getItemInfo($condition){
        return $this->where($condition)->find();
    }

}
