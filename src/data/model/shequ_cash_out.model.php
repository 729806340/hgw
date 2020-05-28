<?php
/**
 * 社区佣金提现模型
 *
 * * @汉购网 (c) 2015-2018 ShopWWI Inc. (http://www.hangowa.com)
 * @license    http://www.shopwwi.c om
 * @link       交流群号：
 * @since      汉购网提供技术支持 授权请购买shopnc授权
 */
defined('ByShopWWI') or exit('Access Invalid!');
class shequ_cash_outModel extends Model{

    public function __construct(){
        parent::__construct('shequ_cash_out');
    }

    public function getList($condition, $page=null, $order='', $field='*',$limit = '') {
        return $this->field($field)->where($condition)->page($page)->order($order)->limit($limit)->select();
    }


    public function getInfo($condition, $order='cash_id desc') {
        return $this->where($condition)->order($order)->find();
    }

    public function add($param){
        return $this->insert($param);
    }

    public function edit($condition,$data){
        return $this->where($condition)->update($data);
    }


}
