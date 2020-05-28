<?php
/**
 * jdy 单位模型
 * * @汉购网 (c) 2015-2018 ShopWWI Inc. (http://www.hangowa.com)
 * @license    http://www.shopwwi.com
 * @link       交流群号：
 * @since      汉购网提供技术支持 授权请购买shopnc授权
 */
defined('ByShopWWI') or exit('Access Invalid!');

class jdy_unitModel extends Model{

    protected $tableName = 'jdy_unit';

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
        return $this->add($param);
    }

    /**
     * 更新
     * @param $data
     * @param $condition
     * @return bool
     */
    public function edit($data, $condition) {
        return $this->where($condition)->save($data);
    }

    /*
     * 查找单条记录
     * @param array $condition
     * @return array
     */
    public function getItemInfo($condition){
        return $this->where($condition)->find();
    }

    public function delItem($condition){
        return $this->where($condition)->delete();
    }

}
