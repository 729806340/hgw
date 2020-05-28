<?php
/**
 * 买家发票模型
 * * @汉购网 (c) 2015-2018 ShopWWI Inc. (http://www.hangowa.com)
 * @license    http://www.shopwwi.c om
 * @link       交流群号：
 * @since      汉购网提供技术支持 授权请购买shopnc授权
 */
defined('ByShopWWI') or exit('Access Invalid!');

class b2b_invoiceModel extends Model {

    public function __construct() {
        parent::__construct('b2b_invoice');
    }

    /**
     * 取得买家默认发票
     *
     * @param array $condition
     */
    public function getDefaultInvInfo($condition = array()) {
        return $this->where($condition)->order('inv_state asc')->find();
    }

    /**
     * 取得单条发票信息
     *
     * @param array $condition
     */
    public function getInvInfo($condition = array()) {
        return $this->where($condition)->find();
    }

    /**
     * 取得发票列表
     *
     * @param unknown_type $condition
     * @return unknown
     */
    public function getInvList($condition, $limit = '', $field = '*') {
        return $this->field($field)->where($condition)->limit($limit)->select();
    }

    /**
     * 删除发票信息
     *
     * @param unknown_type $condition
     * @return unknown
     */
    public function delInv($condition) {
        return $this->where($condition)->delete();
    }

    /**
     * 新增发票信息
     *
     * @param unknown_type $data
     * @return unknown
     */
    public function addInv($data) {
        return $this->insert($data);
    }

    /**
     * 更新发票信息
     *
     * @param unknown_type $update $condition
     * @return unknown
     */
    public function editInv($update, $condition){
        return $this->where($condition)->update($update);
    }

    /**
     * 取数量
     * @param unknown $condition
     */
    public function getInvCount($condition = array()) {
        return $this->where($condition)->count();
    }

}
