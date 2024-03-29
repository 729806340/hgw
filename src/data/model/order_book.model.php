<?php
/**
 * 预定订单时段模板
 *
 *
 *
 * * @汉购网 (c) 2015-2018 ShopWWI Inc. (http://www.hangowa.com)
 * @license    http://www.shopwwi.c om
 * @link       交流群号：
 * @since      汉购网提供技术支持 授权请购买shopnc授权
 */
defined('ByShopWWI') or exit('Access Invalid!');
class order_bookModel extends Model{

    public function __construct(){
        parent::__construct('order_book');
    }

    /**
     * 读取列表
     * @param array $condition
     *
     */
    public function getOrderBookList($condition = array(), $page = '', $order = 'book_id asc', $field = '*', $limit = '') {
        return $this->field($field)->where($condition)->page($page)->order($order)->limit($limit)->select();
    }

    /**
     * 读取单条记录
     * @param array $condition
     *
     */
    public function getOrderBookInfo($condition,$fields = '*') {
        return $this->where($condition)->field($fields)->find();
    }

    /*
     * 增加
     * @param array $data
     * @return bool
     */
    public function addOrderBook($data){
        return $this->insert($data);
    }

    /**
     * 编辑
     * @param unknown $data
     * @param unknown $condition
     */
    public function editOrderBook($data,$condition) {
        return $this->where($condition)->update($data);
    }

    public function getOrderBookCount($condition) {
        return $this->where($condition)->count();
    }

}
