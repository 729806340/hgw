<?php

namespace Home\Model;

use Think\Model;

class AftersalesRefundItemsModel extends Model {

    //获取退货列表
	public function getRefundList($pagesize = 6, $page = 1, $oid='')
    {
		$conditions = array() ;
		$conditions['payee_id'] = session('uid') ;
		if ($oid) {
            $conditions['order_id'] = array('like', '%' . $oid . '%');
        }
		$offset = 0;
        if ($page > 1)
            $offset = ($page - 1) * $pagesize;
        $totalrows = $this->where($conditions)->count();
		$result = $this->order('createtime desc')->limit($offset, $pagesize)->where($conditions)->field('*,FROM_UNIXTIME(t_begin,"%Y-%m-%d %H:%i:%S") as applytime, FROM_UNIXTIME(t_payed,"%Y-%m-%d %H:%i:%S") as payedtime ')->select();
        $pagetotal = ceil($totalrows / $pagesize);
        return array($totalrows, $result, $pagetotal);
    }

}
