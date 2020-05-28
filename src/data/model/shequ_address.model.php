<?php
defined('ByShopWWI') or exit('Access Invalid!');
use ArrayHelper;
class shequ_addressModel extends Model
{
    public function __construct()
    {
        parent::__construct('shequ_address');
    }

    public function getList($condition, $page='', $order='', $field='*',$limit=null)
    {
        return $this->where($condition)->page($page)->limit($limit)->order($order)->select();
    }
    /**
     * 读取单条记录
     * @param array $condition
     *
     */
    public function getOne($condition){
        $result = $this->where($condition)->find();
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