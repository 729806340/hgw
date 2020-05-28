<?php
/**
 * 佣金流水表
 *
 */
defined('ByShopWWI') or exit('Access Invalid!');

class pyramid_brokerage_logModel extends Model
{
    public function __construct()
    {
        parent::__construct('pyramid_brokerage_log');
    }

    /**
     * 新增单条数据
     *
     * @param $insert
     * @return mixed
     */
    public function addPyramidBrokerageLog($insert)
    {
        return $this->insert($insert);
    }


    /**
     * 读取列表
     * @param array $condition
     *
     */
    public function getPyramidBrokerageLogList($condition, $page='', $order='', $field='*') {
        return $this->field($field)->where($condition)->page($page)->order($order)->select();
    }

    /**
     * 读取单条记录
     * @param array $condition
     *
     */
    public function getPyramidBrokerageLogInfo($condition) {
        return $this->where($condition)->find();
    }

    /*
     * 更新
     * @param array $update
     * @param array $condition
     * @return bool
     */
    public function editPyramidBrokerageLog($update, $condition){
        return $this->where($condition)->update($update);
    }

    /*
     * 删除
     * @param array $condition
     * @return bool
     */
    public function delPyramidBrokerageLog($condition){
        return $this->where($condition)->delete();
    }


}
