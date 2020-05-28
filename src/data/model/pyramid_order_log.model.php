<?php
/**
 * 分销订单日志
 *
 */
defined('ByShopWWI') or exit('Access Invalid!');

class pyramid_order_logModel extends Model
{
    public function __construct()
    {
        parent::__construct('pyramid_order_log');
    }

    /**
     * 新增单条数据
     *
     * @param $insert
     * @return mixed
     */
    public function addPyramidOrderLog($insert)
    {
        return $this->insert($insert);
    }

    /**
     * 新增多条数据
     *
     * @param $insert
     * @return mixed
     */
    public function addPyramidOrderLogList($insert)
    {
        return $this->insertAll($insert);
    }

    /**
     * 读取列表
     * @param array $condition
     *
     */
    public function getPyramidOrderLogList($condition, $page='', $order='', $field='*') {
        return $this->field($field)->where($condition)->page($page)->order($order)->select();
    }

    /**
     * 读取单条记录
     * @param array $condition
     *
     */
    public function getPyramidOrderLogInfo($condition) {
        return $this->where($condition)->find();
    }

    /*
     * 更新
     * @param array $update
     * @param array $condition
     * @return bool
     */
    public function editPyramidOrderLog($update, $condition){
        return $this->where($condition)->update($update);
    }

    /*
     * 删除
     * @param array $condition
     * @return bool
     */
    public function delPyramidOrderLog($condition){
        return $this->where($condition)->delete();
    }


}
