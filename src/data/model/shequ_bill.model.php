<?php
defined('ByShopWWI') or exit('Access Invalid!');

class shequ_billModel extends Model
{

    public function __construct()
    {
        parent::__construct('shequ_bill');
    }


    public function getList($condition, $page = '', $order = '', $field = '*', $limit = null)
    {

        return $this->where($condition)->field($field)->page($page)->limit($limit)->order($order)->select();

    }

    public function getListTuan($condition = array(), $fields = '*', $order = 'ob_id desc', $group = '', $page = null)
    {
        return $this->where($condition)->page($page)->field($fields)->limit(false)->group($group)->order($order)->select();
    }

    /**
     * 读取单条记录
     * @param array $condition
     *
     */
    public function getOne($condition, $fields = '*', $order = 'ob_end_date desc')
    {
        $result = $this->where($condition)->field($fields)->order($order)->find();
        return $result;
    }

    /**
     * 更新
     * @param $data
     * @param $condition
     * @return bool
     */
    public function edit($condition, $data)
    {
        return $this->where($condition)->update($data);
    }

    /*
     * 增加
     * @param array $param
     * @return bool
     */
    public function addItem($param)
    {
        return $this->insert($param);
    }

    /**
     *  增加备注
     * @param $data
     * @param array $condition
     * @return mixed
     */
    public function editShequBill($data, $condition = array())
    {
        return $this->table('shequ_bill')->where($condition)->update($data);
    }

    /**
     * 统计数量
     * @return mixed
     */
    public function getShequBillCount($condition)
    {
        return $this->table('shequ_bill')->where($condition)->count();
    }


}