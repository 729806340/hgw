<?php
/**
 * 支付方式
 *
 *
 *
 * * @汉购网 (c) 2015-2018 ShopWWI Inc. (http://www.hangowa.com)
 * @license    http://www.shopwwi.c om
 * @link       交流群号：
 * @since      汉购网提供技术支持 授权请购买shopnc授权
 */
defined('ByShopWWI') or exit('Access Invalid!');
class b2b_paymentModel extends Model {
    /**
     * 开启状态标识
     * @var unknown
     */
    const STATE_OPEN = 1;

    public function __construct() {
        parent::__construct('b2b_payment');
    }

    /**
     * 读取单行信息
     *
     * @param
     * @return array 数组格式的返回结果
     */
    public function getPaymentInfo($condition = array()) {
        return $this->where($condition)->find();
    }

    /**
     * 读开启中的取单行信息
     *
     * @param
     * @return array 数组格式的返回结果
     */
    public function getPaymentOpenInfo($condition = array()) {
        $condition['payment_state'] = self::STATE_OPEN;
        return $this->where($condition)->find();
    }

    /**
     * 读取多行
     *
     * @param
     * @return array 数组格式的返回结果
     */
    public function getPaymentList($condition = array()){
        return $this->where($condition)->select();
    }

    /**
     * 读取开启中的支付方式
     *
     * @param
     * @return array 数组格式的返回结果
     */
    public function getPaymentOpenList($condition = array()){
        $condition['payment_state'] = self::STATE_OPEN;
        return $this->where($condition)->key('payment_code')->select();
    }

    /**
     * 更新信息
     *
     * @param array $param 更新数据
     * @return bool 布尔类型的返回结果
     */
    public function editPayment($data, $condition){
        return $this->where($condition)->update($data);
    }

    /**
     * 读取支付方式信息by Condition
     *
     * @param
     * @return array 数组格式的返回结果
     */
    public function getRowByCondition($conditionfield,$conditionvalue){
        $param  = array();
        $param['table'] = 'payment';
        $param['field'] = $conditionfield;
        $param['value'] = $conditionvalue;
        $result = Db::getRow($param);
        return $result;
    }
}
