<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017-06-14
 * Time: 10:41
 */
/**
 * 供应商管理
 *
 *
 *
 * * @汉购网 (c) 2015-2018 ShopWWI Inc. (http://www.hangowa.com)
 * @license    http://www.shopwwi.c om
 * @link       交流群号：
 * @since      汉购网提供技术支持 授权请购买shopnc授权
 */

defined('ByShopWWI') or exit('Access Invalid!');

class b2b_supplierModel extends Model
{
    public function __construct() {
        parent::__construct('b2b_supplier');
    }

    public function getSupplierList($condition = array(), $field = '*'){
        return $this->table('b2b_supplier')->field($field)->where($condition)->select();
    }

    public function getSupplierInfo($condition = array(), $field = '*'){
        return $this->table('b2b_supplier')->field($field)->where($condition)->find();
    }

    public function addSupplier($data){
        return $this->table('b2b_supplier')->insert($data);
    }

    public function editSupplier($supplier_id,$data){
        return $this->table('b2b_supplier')->where(array('supplier_id' => $supplier_id))->update($data);
    }




}

