<?php

namespace Home\Model;

use Think\Model;

class B2cSubordersModel extends Model {
   
       //获取suborder
//    public function getSubOrder($orderid) {
//        $conditions['order_id'] = $orderid;
//        return $this->table('sdb_b2c_suborders as a')->join(array('left join sdb_b2c_suborders as b ON b.corp_id = a.out_logi_id','left join sdb_b2c_dlycorp as b ON b.corp_id = a.out_logi_id'))->where($conditions)->field('a.out_logi_no,b.name as bname')->select();
//    }

}
