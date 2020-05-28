<?php
/**
 * 供货商模型
 *
 *
 *
 * * @汉购网 (c) 2015-2018 ShopWWI Inc. (http://www.hangowa.com)
 * @license    http://www.shopwwi.c om
 * @link       交流群号：
 * @since      汉购网提供技术支持 授权请购买shopnc授权
 */
defined('ByShopWWI') or exit('Access Invalid!');
class store_supplierModel extends Model{

    public function __construct(){
        parent::__construct('store_supplier');
    }

    /**
     * 读取列表
     * @param array $condition
     *
     */
    public function getStoreSupplierList($condition, $page = '', $order = '', $field = '*') {
        return $this->field($field)->where($condition)->page($page)->order($order)->select();
    }

    /**
     * 读取单条记录
     * @param array $condition
     *
     */
    public function getStoreSupplierInfo($condition) {
        return $this->where($condition)->find();
    }

    /*
     * 增加
     * @param array $data
     * @return bool
     */
    public function addStoreSupplier($data){
        return $this->insert($data);
    }

    public function editStoreSupplier($data,$condition) {
        return $this->where($condition)->update($data);
    }

    /*
     * 删除
     * @param array $condition
     * @return bool
     */
    public function delStoreSupplier($condition){
        return $this->where($condition)->delete();
    }

}
