<?php
/**
 * 拼团活动模型
 *
 *
 *
 * * @汉购网 (c) 2015-2018 ShopWWI Inc. (http://www.hangowa.com)
 * @license    http://www.shopwwi.c om
 * @link       交流群号：
 * @since      汉购网提供技术支持 授权请购买shopnc授权
 */
defined('ByShopWWI') or exit('Access Invalid!');
class p_pintuan_memberModel extends Model{

    public function __construct(){
        parent::__construct('p_pintuan_member');
    }

    /**
     * 读取拼团列表
     * @param array $condition 查询条件
     * @param int $page 分页数
     * @param string $order 排序
     * @param string $field 所需字段
     * @return array 拼团列表
     *
     */
    public function getMemberList($condition, $page=null, $order='', $field='*') {
        $pintuan_list = $this->table('p_pintuan_member')->field($field)->where($condition)->page($page)->order($order)->select();
        return $pintuan_list;
    }

    /**
     * 根据条件读取限制折扣信息
     * @param array $condition 查询条件
     * @return array 拼团信息
     *
     */
    public function getMemberInfo($condition) {
        $pintuan_info = $this->table('p_pintuan_member')->where($condition)->find();
        return $pintuan_info;
    }

    /**
     * 根据拼团编号读取限制折扣信息
     * @param array $tuan_id 限制折扣活动编号
     * @return array 拼团信息
     *
     */
    public function getMemberInfoByID($tuan_id) {
        if(intval($tuan_id) <= 0) {
            return null;
        }

        $condition = array();
        $condition['tuan_id'] = $tuan_id;
        $pintuan_info = $this->getMemberInfo($condition);
        return $pintuan_info;
    }

    /*
     * 增加
     * @param array $param
     * @return bool
     *
     */
    public function addMember($param){
        return $this->table('p_pintuan_member')->insert($param);
    }


    /*
     * 更新
     * @param array $update
     * @param array $condition
     * @return bool
     *
     */
    public function editMember($update, $condition){
        return $this->table('p_pintuan_member')->where($condition)->update($update);
    }



}
