<?php
/**
 * 结算模型
 * @汉购网 (c) 2015-2018 ShopWWI Inc. (http://www.hangowa.com)
 * @license    http://www.shopwwi.c om
 * @link       交流群号：
 * @since      汉购网提供技术支持 授权请购买shopnc授权
 */
defined('ByShopWWI') or exit('Access Invalid!');
 
//以下是定义结算单状态
//默认
define('BILL_STATE_CREATE',1);
define('BILL_STATE_HANGO',10); // 汉购网商务审核中
define('BILL_STATE_FIRE_PHONIX',11); // 公司商务审核中
define('BILL_STATE_CEO',12); // 总经理审核中
define('BILL_STATE_PAYING',13); // 财务付款中
//店铺已确认
define('BILL_STATE_STORE_COFIRM',2);
//平台已审核
define('BILL_STATE_SYSTEM_CHECK',3);
//结算完成
define('BILL_STATE_SUCCESS',4);
//部分结算
define('BILL_STATE_PART_PAY',5);

class channel_billModel extends Model {
    /**
     * 取得平台月结算单
     * @param unknown $condition
     * @param unknown $fields
     * @param unknown $pagesize
     * @param unknown $order
     * @param unknown $limit
     */
    public function getOrderStatisList($condition = array(), $fields = '*', $pagesize = null, $order = '', $limit = null) {
        return $this->table('order_statis')->where($condition)->field($fields)->order($order)->page($pagesize)->limit($limit)->select();
    }

    /**
     * 取得平台月结算单条信息
     * @param unknown $condition
     * @param string $fields
     */
    public function getOrderStatisInfo($condition = array(), $fields = '*',$order = null) {
        return $this->table('order_statis')->where($condition)->field($fields)->order($order)->find();
    }

    /**
     * 取得店铺月结算单列表
     * @param unknown $condition
     * @param string $fields
     * @param string $pagesize
     * @param string $order
     * @param string $limit
     */
    public function getOrderBillList($condition = array(), $fields = '*', $pagesize = null, $order = '', $limit = null) {
        return $this->table('channel_bill')->where($condition)->field($fields)->order($order)->limit($limit)->page($pagesize)->select();
    }

    /**
     * 取得店铺月结算单单条
     * @param unknown $condition
     * @param string $fields
     */
    public function getOrderBillInfo($condition = array(), $fields = '*', $order = '') {
        return $this->table('channel_bill')->where($condition)->field($fields)->order($order)->find();
    }

    /**
     * 取得订单数量
     * @param unknown $condition
     */
    public function getOrderBillCount($condition) {
        return $this->table('channel_bill')->where($condition)->count();
    }

    public function addOrderStatis($data) {
        return $this->table('order_statis')->insert($data);
    }

    public function addOrderBill($data) {
        return $this->table('channel_bill')->insert($data);
    }

    public function editOrderBill($data, $condition = array()) {
        return $this->table('channel_bill')->where($condition)->update($data);
    }

}
