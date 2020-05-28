<?php

namespace Home\Model;

use Think\Model;

//$model->query('select * from user where id=%d and status=%d',array($id,$status));
class B2cOrdersModel extends Model {

    //获取orders
    public function getOrderList($pagesize = 6, $page = 1, $uid = '', $status = '', $oid = '', $fxoid = '', $begintime = '', $endtime = '') {
        $conditions = array();
        if ($uid != '') {
            $conditions['member_id'] = $uid;
        }
        if ($status != '') {
            if ($status == 0) {
                $conditions['ship_status'] = '0';
                $conditions['pay_status'] = '0';
                $conditions['status'] = 'active';
            } elseif ($status == 1) {
                $conditions['ship_status'] = '1';
                $conditions['status'] = 'active';
            } elseif ($status == 2) {
                $conditions['status'] = 'dead';
            } elseif ($status == 3) {
                $conditions['status'] = 'finish';
            } elseif ($status == 4) {
                $conditions['ship_status'] = array('in', array('0', '5'));
                $conditions['pay_status'] = '1';
                $conditions['status'] = 'active';
            }
        }
        if ($oid) {
            $conditions['order_id'] = array('like', '%' . $oid . '%');
        }
        if( $fxoid ){
            //查出分销订单号对应的订单号
            $orderItems = D('B2cOrderItems');
            $result = $orderItems->getOrderByFxoid($fxoid);
            $fxoids = array_unique( array_column($result, 'order_id') );
            if( !empty($fxoids) ) {
                $conditions['order_id'] = array('in', $fxoids);
            }
        }
        if ($begintime && $endtime)
            $conditions['createtime'] = array(array('gt', strtotime($begintime)), array('lt', strtotime($endtime)));
        if (!$begintime && $endtime)
            $conditions['createtime'] = array(array('gt', 0), array('lt', strtotime($endtime)));
        if ($begintime && !$endtime)
            $conditions['createtime'] = array(array('gt', strtotime($begintime)), array('lt', time()));
        $offset = 0;
        if ($page > 1)
            $offset = ($page - 1) * $pagesize;
        $totalrows = $this->where($conditions)->count();
        if( empty($fxoids) && $fxoid ) $totalrows = 0 ;
        $result = $this->order('createtime desc')->limit($offset, $pagesize)->where($conditions)->field('*,FROM_UNIXTIME(createtime,"%Y-%m-%d %H:%i:%S") as datetime ')->select();
        //echo $this->getLastSql();
        $pagetotal = ceil($totalrows / $pagesize);
        return array($totalrows, $result, $pagetotal);
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
        $column = $params['column'];
        $data[ $column ] = $params['col_value'];
        return $this->where('order_id=' .$params['order_id'] )->save($data); // 根据条件更新记录
    }
}
