<?php
/**
 * 退款数据模型表
 *@author ljq
 *@date 2016-8-3
 */
namespace Home\Model;

use Think\Model;

class RefundReturnModel extends Model {
	
	private $refund_state  = array(
			1=>'处理中',
			2=>'待管理员审核',
			3=>'已完成',
	);
	
	private $refund_type = array(
		    1=>'退款',
		    2=>'退款退货',	
	);
	
	private $seller_state = array(
			1=>'待审',
			2=>'同意',
			3=>'不同意',
	);

	//获取退货列表
	public function getRefundList($pagesize = 6, $page = 1, $oid='')
	{
		$result = array();
		$conditions = array() ;
		$conditions['buyer_id'] = session('uid') ;
		if ($oid) {
			$conditions['order_id'] = array('like', '%' . $oid . '%');
		}
		$offset = 0;
		$page > 1 and $offset = ($page -1 )* $pagesize;
		$totalrows = $this->where($conditions)->count();
		if($totalrows > 0){
			$result = $this->order('add_time desc')->limit($offset, $pagesize)
			->where($conditions)->field('refund_id , refund_type, refund_state,seller_state,order_sn,refund_amount ,add_time')->
			select();
			$overRefundIds = array();
			$overOrderIds = array();
			foreach($result as $k =>$v){
				if($v['refund_state'] ==3){
					$overRefundIds[] = $v['refund_id'];
				}
				$overOrderIds[] = $v['order_sn'];
			}
			if(count($overRefundIds)>0){
				$array = array('refund_id'=>array('in' , $overRefundIds));
				$res = M('RefundDetail')->field('refund_id,refund_amount,pay_time')->where($array)->select();
				if(count($res)>0){
					$refundArr = array();
					foreach($res as $k=>$v){
						$refundArr[$v['refund_id']] = $v;
					}
				}
			}
			if(count($overOrderIds)>0){
				$array1 = array('order_sn'=>array('in' , $overOrderIds));
				$rs = M('orders')->field('order_sn , order_amount')->where($array1)->select();
				if(count($rs)>0){
					$orderArr = array();
					foreach($rs as $k=>$v){
						$orderArr[$v['order_sn']] = $v['order_amount'];
					}
				}
			}
			
			foreach($result as $k=>$v){
				$result[$k]['refund_type'] = $this->refund_type[$v['refund_type']];
				$result[$k]['refund_state'] = $this->refund_state[$v['refund_state']];
				$result[$k]['seller_state'] = $this->seller_state[$v['seller_state']];
				$result[$k]['refund_amout'] = $refundArr[$v['refund_id']]['refund_amount'];
				$result[$k]['pay_time'] = $refundArr[$v['refund_id']]['pay_time']?date('Y-m-d H:i:s' , $refundArr[$v['refund_id']]['pay_time']):'';
				$result[$k]['add_time'] = date('Y-m-d H:i:s' , $v['add_time']);
				$result[$k]['order_money'] = $orderArr[$v['order_sn']];
			}
			$pagetotal = ceil($totalrows / $pagesize);
		}
		return array($totalrows, $result, $pagetotal);
	}

}
