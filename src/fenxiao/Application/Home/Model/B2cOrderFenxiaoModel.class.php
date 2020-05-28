<?php

namespace Home\Model;

use Think\Model;

class B2cOrderFenxiaoModel extends Model {

	//根据分销平台订单号数组获取汉购订单号
	//@params $nos array array('20150909-018595156','20150908-810401563',...)
	public function getFxorderByNos($nos) {
		if (!is_array($nos) || empty($nos))
			return false;
		$conditions = array();
		$conditions['orderno'] = array('in', $nos);
		return $this -> where($conditions) -> select();
	}

	//根据Uid获取所有订单号
	public function getOrderNoByUid($uid, $order_nos = array()) {
		$where['sourceid'] = intval($uid);
		if( is_array($order_nos) && !empty($order_nos) ) {
			$where['orderno'] = array('in', $order_nos) ;
		}
		$result = $this -> where($where) -> select();
		$array = array();
		foreach ($result as $v) {
			array_push($array, $v['orderno']);
		}
		return $array;
	}

}
