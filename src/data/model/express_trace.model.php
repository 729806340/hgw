<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/25
 * Time: 15:26
 */
class express_traceModel extends Model {

    public function addExpressTrace($data){
        return $this->table('express_trace')->insert($data);
    }

    public function getExpressTraceList($condition = array(), $fields = '*', $limit = null, $page = null, $order = 'et_id desc', $group = null, $key = null) {
        return $this->field($fields)->where($condition)->limit($limit)->order($order)->group($group)->key($key)->page($page)->select();
    }

    public function getExpressTraceInfo($condition){
        return $this->where($condition)->find();
    }

    public function editExpressTrace($update, $condition) {
        return $this->where($condition)->update($update);
    }

}