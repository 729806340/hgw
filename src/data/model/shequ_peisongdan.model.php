<?php
/**
 * 社区团长打印配送单商品模型
 *
 * * @汉购网 (c) 2015-2018 ShopWWI Inc. (http://www.hangowa.com)
 * @license    http://www.shopwwi.c om
 * @link       交流群号：
 * @since      汉购网提供技术支持 授权请购买shopnc授权
 */
defined('ByShopWWI') or exit('Access Invalid!');
class shequ_peisongdanModel extends Model{

    public function __construct(){
        parent::__construct('shequ_peisongdan');
    }

    /**
     * 读取社区团购团长打印配送单列表
     * @param array $condition 查询条件
     * @param int $page 分页数
     * @param string $order 排序
     * @param string $field 所需字段
     * @return array 限时折扣列表
     *
     */
    public function getList($condition, $page=null, $order='', $field='*',$limit = '') {
        return $this->field($field)->where($condition)->page($page)->order($order)->limit($limit)->select();
    }


    public function getInfo($condition) {
        return $this->where($condition)->find();
    }

    public function add($param){
        return $this->insert($param);
    }

    public function edit($condition,$data){
        return $this->where($condition)->update($data);
    }


}