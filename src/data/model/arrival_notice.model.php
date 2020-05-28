<?php
/**
 * 商品到货通知模型
 *
 *
 *
 * * @汉购网 (c) 2015-2018 ShopWWI Inc. (http://www.hangowa.com)
 * @license    http://www.shopwwi.c om
 * @link       交流群号：
 * @since      汉购网提供技术支持 授权请购买shopnc授权
 */
defined('ByShopWWI') or exit('Access Invalid!');
class arrival_noticeModel extends Model{
    public function __construct() {
        parent::__construct('arrival_notice');
    }

    /**
     * 通知列表
     *
     *
     * @param unknown $condition
     * @param string $field
     * @param number $limit
     * @param string $order
     */
    public function getArrivalNoticeList($condition = array(), $field = '*', $limit = '', $page = null, $order = 'an_id desc') {
        return $this->where($condition)->field($field)->limit($limit)->page($page)->order($order)->select();
    }

    /**
     * 单条通知
     *
     * @param unknown $condition
     * @param string $field
     */
    public function getArrivalNoticeInfo($condition, $field = '*') {
        return $this->where($condition)->field($field)->find();
    }

    /**
     * 通知数量
     *
     * @param array $condition
     * @param string $field
     * @param string $order
     * @return array
     */
    public function getArrivalNoticeCount($condition) {
        return $this->where($condition)->count();
    }


    /**
     * 添加通知
     * @param array $insert
     * @return int
     */
    public function addArrivalNotice($insert) {
        $insert['an_addtime'] = TIMESTAMP;
        return $this->insert($insert);
    }

    /**
     * 删除通知
     *
     * @param unknown $condition
     */
    public function delArrivalNotice($condition) {
        return $this->where($condition)->delete();
    }
}
