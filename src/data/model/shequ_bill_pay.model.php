<?php
defined('ByShopWWI') or exit('Access Invalid!');
class shequ_bill_payModel extends Model
{

    public function __construct()
    {
        parent::__construct('shequ_bill_pay');
    }



    public function getList($condition, $page='', $order='', $field='*',$limit=null)
    {
        return $this->where($condition)->page($page)->limit($limit)->order($order)->select();
    }

    public function getCount($condition) {
        return $this->where($condition)->count();
    }

    /**
     * 读取单条记录
     * @param array $condition
     *
     */
    public function getOne($condition,$fields='*',$order='obl_id desc'){
        $result = $this->where($condition)->field($fields)->order($order)->find();
        return $result;
    }
    /**
     * 更新
     * @param $data
     * @param $condition
     * @return bool
     */
    public function edit($condition, $data) {
        return $this->where($condition)->update($data);
    }

    /*
     * 增加
     * @param array $param
     * @return bool
     */
    public function addItem($param){
        return $this->insert($param);
    }
}