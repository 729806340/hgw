<?php
/**
 * @汉购网 (c) 2015-2018 ShopWWI Inc. (http://www.hangowa.com)
 * @license    http://www.shopwwi.c om
 * @link       交流群号：
 * @since      汉购网提供技术支持 授权请购买shopnc授权
 */
defined('ByShopWWI') or exit('Access Invalid!');
 
class order_bill_logModel extends Model {
    public function __construct() {
        parent::__construct('order_bill_log');
    }

    public function add($data){
        return $this->table('order_bill_log')->insert($data);
    }

    public function getList($condition, $page='', $order='', $field='*',$limit=null) {
        return $this->field($field)->where($condition)->page($page)->limit($limit)->order($order)->select();
    }
    public function getCount($condition) {
        return $this->where($condition)->count();
    }
}
