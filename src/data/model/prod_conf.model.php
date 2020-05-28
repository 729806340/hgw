<?php
/**
 * 竞价信息抓取配置
 *
 *
 *
 * * @汉购网 (c) 2015-2018 ShopWWI Inc. (http://www.hangowa.com)
 * @license    http://www.shopwwi.c om
 * @link       交流群号：
 * @since      汉购网提供技术支持 授权请购买shopnc授权
 */
defined('ByShopWWI') or exit('Access Invalid!');
class prod_confModel extends Model{
    public function __construct() {
        parent::__construct('prod_conf');
    }

    /**
     * 咨询列表
     *
     * @param array $condition
     * @param string $field
     * @param string $order
     * @return array
     */
    public function getList($condition, $field = '*', $page = 0, $order = 'id desc') {
        return $this->where($condition)->field($field)->order($order)->page($page)->select();
    }

    /**
     * 咨询数量
     *
     * @param array $condition
     * @param string $field
     * @param string $order
     * @return array
     */
    public function getCount($condition) {
        return $this->where($condition)->count();
    }

    /**
     * 单条咨询
     *
     * @param unknown $condition
     * @param string $field
     */
    public function getInfo($condition, $field = '*') {
        return $this->where($condition)->field($field)->find();
    }

    /**
     * 添加咨询
     * @param array $insert
     * @return int
     */
    public function addItem($insert) {
        $insert['created_at']=time();
        return $this->insert($insert);
    }

    /**
     * 编辑咨询
     * @param array $condition
     * @param array $update
     * @return boolean
     */
    public function editItem($condition, $update) {
        $update['updated_at']=time();
        return $this->where($condition)->update($update);
    }

    /**
     * 删除咨询
     *
     * @param array $condition
     * @return boolean
     */
    public function delItem($condition) {
        return $this->where($condition)->delete();
    }
    public function check($param){
        return $this->where(array('prod_from'=>$param))->find();
    }
    public function getAllList(){
        return $this->where(array('status'=>1))->select();
    }
}
