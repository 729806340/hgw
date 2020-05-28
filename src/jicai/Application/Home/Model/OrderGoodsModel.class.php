<?php

namespace Home\Model;

use Think\Model;

class OrderGoodsModel extends Model {
   
    //获取suborder
   public function getOrderItems($orderid) {
        $conditions['a.order_id'] = $orderid;
        return $this->table('shopwwi_orders as a')->join(array('left join shopwwi_order_goods as b ON a.order_id = b.order_id','left join shopwwi_goods as c ON b.goods_id = c.goods_id','left join shopwwi_b2c_order_fenxiao as d ON a.fx_order_id = d.orderno','left join shopwwi_orders e on e.order_id=a.order_id','left join shopwwi_order_common as f on a.order_id=f.order_id'))->where($conditions)->field('b.goods_name as order_good_name,c.goods_name,b.goods_price,b.goods_num,e.order_amount,e.goods_amount,e.order_state,a.fx_order_id,e.shipping_code,f.shipping_express_id,b.goods_image')->select();
	  //return $this->table('sdb_b2c_order_items as a')->join(array('left join sdb_b2c_goods as b ON a.goods_id = b.goods_id','left join sdb_image_image as c ON b.image_default_id = c.image_id','left join sdb_b2c_suborders as d ON a.sub_order_id = d.sub_order_id','left join sdb_b2c_dlycorp as e ON d.out_logi_id = e.corp_id'))->where($conditions)->field('a.*,c.s_url,d.out_logi_no,e.name as bname')->select();
    }

   public function getOrderByFxoid( $fxoid ){
        $conditions['poid'] = $fxoid;
       return $this->where($conditions)->select();
   }
   
   public function getOrderGoodsInfo($condition)
   {
   		return $this->where($condition)->find();
   }

}
