<?php

namespace Home\Model;

use Think\Model;

//$model->query('select * from user where id=%d and status=%d',array($id,$status));
class OrdersModel extends Model {

    //获取orders
    public function getOrderList($pagesize = 6, $page = 1, $uid = '', $status = '', $oid = '', $fxoid = '', $begintime = '', $endtime = '',$ibegintime='',$iendtime='') {
        
        $conditions = array();
        if ($uid != '') {
        	$conditions['buyer_id'] = $uid;
        }
        if ($status != '') {
        	if ($status == 0) {
        		$conditions['order_state'] = '10';
        	} elseif ($status == 1) {
        		$conditions['order_state'] = '30';
        	} elseif ($status == 2) {
        		$conditions['order_state'] = '0';
        	} elseif ($status == 3) {
        		$conditions['order_state'] = '40';
        	} elseif ($status == 4) {
        		$conditions['order_state'] = '20';
        	} elseif ($status == 5) {
        		$conditions['order_state'] = '21';
        	}
        }
        if ($oid) {
        	$conditions['order_sn'] = array('like', '%' . $oid . '%');
        }
        if( $fxoid ){
        	$conditions['fx_order_id'] = $fxoid;
        }
        if ($begintime && $endtime)
        	$conditions['add_time'] = array(array('gt', strtotime($begintime)), array('lt', strtotime($endtime)));
//        if (!$begintime && $endtime)
//        	$conditions['add_time'] = array(array('gt', 0), array('lt', strtotime($endtime)));
//        if ($begintime && !$endtime)
//        	$conditions['add_time'] = array(array('gt', strtotime($begintime)), array('lt', time()));
        if ($ibegintime && $iendtime)
            $conditions['import_time'] = array(array('gt', strtotime($ibegintime)), array('lt', strtotime($iendtime)));
//        if (!$ibegintime && $iendtime)
//            $conditions['import_time'] = array(array('gt', 0), array('lt', strtotime($iendtime)));
//        if ($ibegintime && !$iendtime)
//            $conditions['import_time'] = array(array('gt', strtotime($ibegintime)), array('lt', time()));
        $offset = 0;
        if ($page > 1) $offset = ($page - 1) * $pagesize;
        
        $totalrows = $this->table('shopwwi_orders')->where($conditions)->count();
        $pagetotal = ceil($totalrows / $pagesize);
        $orders = $this->table('shopwwi_orders')->order('order_id desc')->limit($offset, $pagesize)->where($conditions)->field('*,FROM_UNIXTIME(add_time,"%Y-%m-%d %H:%i:%S") as datetime ')->select();
        if(empty($orders)) return array();
        $oids = array_column($orders, 'order_id');
        $common_condition = array();
        $common_condition['order_id'] = array('in', $oids);
        $commons = $this->table('shopwwi_order_common')->where($common_condition)->select();
        $result = $order_commons = array();
        foreach ($commons as $order_common) {
        	$order_commons[$order_common['order_id']] = $order_common;
        }
        $order_goods = array();
        $goods = $this->table('shopwwi_order_goods')->where($common_condition)->select();
        foreach ($goods as $row) {
        	$pic = substr($row['goods_image'], 5);
        	$tmp = explode("_", $pic);
        	$row['goods_image'] = "data/upload/shop/store/goods/{$tmp[0]}/{$row['goods_image']}";
        	$order_goods[$row['order_id']][] = $row;
        }
        
        foreach ($orders as $order) {
        	$order_common = $order_commons[$order['order_id']] ;
        	$result[$order['order_id']] = $order ;
        	foreach ($order_common as $collumn => $value) {
        		$result[$order['order_id']][$collumn] = $value ;
        	}
        	$result[$order['order_id']]['suborder'] = $order_goods[$order['order_id']];
        }
        return array($totalrows, $result, $pagetotal) ;
    }

    public function addOrder($data, $id = '') {
        if (!$id) {
            $this->add($data);
        } else {
            
        }
    }

    public function getExportOrder($uid, $stage, $begintime, $endtime) {
        $conditions['uid'] = $uid;
        if ($stage != '') {
            $conditions['status'] = $stage;
        }
        if ($begintime && $endtime)
            $conditions['ctime'] = array(array('gt', strtotime($begintime)), array('lt', strtotime($endtime)));
        if (!$begintime && $endtime)
            $conditions['ctime'] = array(array('gt', 0), array('lt', strtotime($endtime)));
        if ($begintime && !$endtime)
            $conditions['ctime'] = array(array('gt', strtotime($begintime)), array('lt', time()));
        return $this->where($conditions)->select();
    }

    public function getUserOrderAmount($uid) {
        $conditions['member_id'] = $uid;
//        $conditions['ship_status'] = '1';
//        $conditions['status'] = 'active';
        return $this->where($conditions)->count();
    }

    
    
        public function getExportList($uid = '', $status = '', $begintime = '', $endtime = '') {
        $conditions = array();
        if ($uid != '') {
            $conditions['member_id'] = $uid;
        }
        if ($status != '') {
            if ($status == 0) {
                $conditions['b.ship_status'] = '0';
                $conditions['pay_status'] = '0';
                $conditions['b.status'] = 'active';
            } elseif ($status == 1) {
                $conditions['b.ship_status'] = '1';
                $conditions['status'] = 'active';
            } elseif ($status == 2) {
                $conditions['b.status'] = 'dead';
            } elseif ($status == 3) {
                $conditions['b.status'] = 'finish';
            } elseif ($status == 4) {
                $conditions['b.ship_status'] = '0';
                $conditions['pay_status'] = '1';
                $conditions['b.status'] = 'active';
            }
        }
        if ($begintime && $endtime)
            $conditions['createtime'] = array(array('gt', strtotime($begintime)), array('lt', strtotime($endtime)));
        if (!$begintime && $endtime)
            $conditions['createtime'] = array(array('gt', 0), array('lt', strtotime($endtime)));
        if ($begintime && !$endtime)
            $conditions['createtime'] = array(array('gt', strtotime($begintime)), array('lt', time()));
//        $totalrows = $this->where($conditions)->count();
        $result = $this->table('shopwwi_orders as a')->join(array('left join shopwwi_order_goods as b on a.order_id = b.order_id','left join shopwwi_order_common as c ON c.order_id = b.order_id','left join shopwwi_b2c_order_fenxiao as d on a.fx_order_id=d.orderno'))->order('a.createtime desc')->limit($offset, $pagesize)->where($conditions)->field('b.goods_image,a.fx_order_id,b.goods_name,b.goods_name,b.goods_price,b.goods_num,a.order_state,a.goods_amount,a.shipping_code,a.order_amount,FROM_UNIXTIME(d.order_time,"%Y-%m-%d %H:%i:%S") as datetime ')->select();
		//$result = $this->table('sdb_b2c_orders as a')->join(array('left join sdb_b2c_suborders as b ON a.order_id = b.order_id','left join sdb_b2c_dlycorp as c ON c.corp_id = b.out_logi_id','left join sdb_b2c_order_items as d ON d.order_id = a.order_id'))->order('a.createtime desc')->limit($offset, $pagesize)->where($conditions)->field('a.*,b.out_logi_no,b.ship_status as bship_status,b.status as bstatus,c.name as cname,d.poid,d.pproductname,d.price,d.nums,d.amount,FROM_UNIXTIME(createtime,"%Y-%m-%d %H:%i:%S") as datetime ')->select();
//        echo $this->getLastSql(); sdb_b2c_dlycorp
//        $pagetotal = ceil($totalrows / $pagesize);
//        return array($totalrows, $result);
        return $result;
    }

    public function updateShiping( $params )
    {
    	//die(var_dump($params));
        $column = $params['column'];
        $data[ $column ] = $params['col_value'];
        $result = M('OrderCommon')->where('order_id=' .$params['order_id'] )->save($data);
        return $result;
        //return $this->where('order_id=' .$params['order_id'] )->save($data); // 根据条件更新记录
    }
    
    public function getOrderlistByPaysn( $paysn_arr )
    {
    	if( empty($paysn_arr) ) return array();
    	$conditions = array();
    	$conditions['pay_sn'] = array('in', $paysn_arr);
    	return $this->where($conditions)->select();
    }
    
    //获取退款单列表
    public function getRefundList($condition)
    {
    	return $this->table('shopwwi_refund_return')->where($condition)->select();
    }
}
