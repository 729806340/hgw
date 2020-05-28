<?php
/**
 * 社区接龙评论
 *
 */
defined('ByShopWWI') or exit('Access Invalid!');

class shequ_tuan_pinglunModel extends Model
{
    public function __construct()
    {
        parent::__construct('shequ_tuan_pinglun');
    }

    /**
     * 新增分评论
     *
     * @param $insert
     * @return mixed
     */
    public function addTuanPinglun($insert)
    {
        return $this->insert($insert);
    }

    /**
     * 读取列表
     * @param array $condition
     *
     */
    public function getTuanPinglunList($condition, $page=0, $order='', $field='*') {
        return $this->field($field)->where($condition)->page($page)->limit(false)->order($order)->select();
    }

    /**
     * 读取单条记录
     * @param array $condition
     *
     */
    public function getTuanPinglunInfo($condition) {
        return $this->where($condition)->find();
    }

    /*
     * 更新
     * @param array $update
     * @param array $condition
     * @return bool
     */
    public function editTuanPinglun($update, $condition){
        return $this->where($condition)->update($update);
    }

    /*
     * 删除
     * @param array $condition
     * @return bool
     */
    public function delTuanPinglun($condition){
        return $this->where($condition)->delete();
    }


}
