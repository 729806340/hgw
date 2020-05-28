<?php
/**
 * 分销商品
 *
 */
defined('ByShopWWI') or exit('Access Invalid!');

class retail_goodsModel extends Model
{
    public function __construct()
    {
        parent::__construct('retail_goods');
    }

    /**
     * 新增分销商品数据
     *
     * @param $insert
     * @return mixed
     */
    public function addRetailGoods($insert)
    {
        return $this->insert($insert);
    }

    /**
     * 读取列表
     * @param array $condition
     *
     */
    public function getRetailGoodsList($condition, $page=0, $order='', $field='*') {
        return $this->table("retail_goods")->field($field)->where($condition)->page($page)->limit(false)->order($order)->select();
    }

    /**
     * 读取单条记录
     * @param array $condition
     *
     */
    public function getRetailGoodsInfo($condition) {
        return $this->where($condition)->find();
    }

    /*
     * 更新
     * @param array $update
     * @param array $condition
     * @return bool
     */
    public function editRetailGoods($update, $condition){
        return $this->where($condition)->update($update);
    }

    /*
     * 删除
     * @param array $condition
     * @return bool
     */
    public function delRetailGoods($condition){
        return $this->where($condition)->delete();
    }


}
