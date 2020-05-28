<?php
/**
 * 社区团购商品
 *
 */
defined('ByShopWWI') or exit('Access Invalid!');

class shequ_return_goodsModel extends Model
{
    public function __construct()
    {
        parent::__construct('shequ_return_goods');
    }

    /**
     * 新增分销商品数据
     *
     * @param $insert
     * @return mixed
     */
    public function addReturnGoods($insert)
    {
        return $this->insert($insert);
    }

    /**
     * 读取列表
     * @param array $condition
     *
     */
    public function getReturnGoodsList($condition, $page=0, $order='', $field='*') {
        return $this->field($field)->where($condition)->page($page)->limit(false)->order($order)->select();
    }

    /**
     * 读取单条记录
     * @param array $condition
     *
     */
    public function getReturnGoodsInfo($condition) {
        return $this->where($condition)->find();
    }

    /*
     * 更新
     * @param array $update
     * @param array $condition
     * @return bool
     */
    public function editReturnGoods($update, $condition){
        return $this->where($condition)->update($update);
    }

    /*
     * 删除
     * @param array $condition
     * @return bool
     */
    public function delReturnGoods($condition){
        return $this->where($condition)->delete();
    }


}
