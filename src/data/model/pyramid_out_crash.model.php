<?php
/**
 * 提现记录表
 *
 */
defined('ByShopWWI') or exit('Access Invalid!');

class pyramid_out_crashModel extends Model
{
    public function __construct()
    {
        parent::__construct('pyramid_out_crash');
    }

    /**
     * 新增单条数据
     *
     * @param $insert
     * @return mixed
     */
    public function addPyramidOutCrash($insert)
    {
        return $this->insert($insert);
    }


    /**
     * 读取列表
     * @param array $condition
     *
     */
    public function getPyramidOutCrashList($condition, $page='', $order='', $field='*') {
        return $this->field($field)->where($condition)->page($page)->order($order)->select();
    }

    /**
     * 读取单条记录
     * @param array $condition
     *
     */
    public function getPyramidOutCrashInfo($condition) {
        return $this->where($condition)->find();
    }

    /*
     * 更新
     * @param array $update
     * @param array $condition
     * @return bool
     */
    public function editPyramidOutCrash($update, $condition){
        return $this->where($condition)->update($update);
    }

    /*
     * 删除
     * @param array $condition
     * @return bool
     */
    public function delPyramidOutCrash($condition){
        return $this->where($condition)->delete();
    }


}
