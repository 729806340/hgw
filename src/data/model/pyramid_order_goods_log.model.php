<?php
/**
 * 分销订单商品日志
 *
 */
defined('ByShopWWI') or exit('Access Invalid!');

class pyramid_order_goods_logModel extends Model
{
    public function __construct()
    {
        parent::__construct('pyramid_order_goods_log');
    }

    /**
     * 新增单条数据
     *
     * @param $insert
     * @return mixed
     */
    public function addPyramidOrderGoodsLog($insert)
    {
        return $this->insert($insert);
    }

    /**
     * 新增多条数据
     *
     * @param $insert
     * @return mixed
     */
    public function addPyramidOrderGoodsLogList($insert)
    {
        return $this->insertAll($insert);
    }

    /**
     * 读取列表
     * @param array $condition
     *
     */
    public function getPyramidOrderGoodsLogList($condition, $page='', $order='', $field='*') {
        return $this->field($field)->where($condition)->page($page)->order($order)->select();
    }

    /**
     * 读取单条记录
     * @param array $condition
     *
     */
    public function getPyramidOrderGoodsLogInfo($condition) {
        return $this->where($condition)->find();
    }

    /*
     * 更新
     * @param array $update
     * @param array $condition
     * @return bool
     */
    public function editPyramidOrderGoodsLog($update, $condition){
        return $this->where($condition)->update($update);
    }

    /*
     * 删除
     * @param array $condition
     * @return bool
     */
    public function delPyramidOrderGoodsLog($condition){
        return $this->where($condition)->delete();
    }


}
