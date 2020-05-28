<?php
/**
 * 微信管理
 *
 *
 *
 * * @汉购网 (c) 2015-2018 ShopWWI Inc. (http://www.hangowa.com)
 * @license    http://www.shopwwi.c om
 * @link       交流群号：
 * @since      汉购网提供技术支持 授权请购买shopnc授权
 */
defined('ByShopWWI') or exit('Access Invalid!');
class keywordModel extends Model{
    public function __construct() {
        parent::__construct('keyword');
    }

    /**
     * 取得列表
     * @param unknown $condition
     * @param string $pagesize
     * @param string $order
     */
    public function getStoreDistributionList($condition = array(), $pagesize = '', $order = 'distri_id desc') {
        return $this->where($condition)->order($order)->page($pagesize)->select();
    }

    /**
     * 增加新记录
     * @param unknown $data
     * @return
     */
    public function addStoreDistribution($data) {
        return $this->insert($data);
    }

    /**
     * 取单条信息
     * @param unknown $condition
     */
    public function getStoreDistributionInfo($condition) {
        return $this->where($condition)->find();
    }

    /**
     * 更新记录
     * @param unknown $condition
     * @param unknown $data
     */
    public function editStoreDistribution($data,$condition) {
        return $this->where($condition)->update($data);
    }

    /**
     * 取得数量
     * @param unknown $condition
     */
    public function getStoreDistributionCount($condition) {
        return $this->where($condition)->count();
    }

    /**
     * 删除记录
     * @param unknown $condition
     */
    public function delStoreDistribution($condition) {
        return $this->where($condition)->delete();
    }
}