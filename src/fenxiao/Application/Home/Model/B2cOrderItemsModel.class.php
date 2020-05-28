<?php

namespace Home\Model;

use Think\Model;

class B2cOrderItemsModel extends Model {
   
       //获取suborder
   public function getOrderItems($orderid) {
        $conditions['a.order_id'] = $orderid;
        return $this->table('sdb_b2c_order_items as a')->join(array('left join sdb_b2c_goods as b ON a.goods_id = b.goods_id','left join sdb_image_image as c ON b.image_default_id = c.image_id','left join sdb_b2c_suborders as d ON a.sub_order_id = d.sub_order_id','left join sdb_b2c_dlycorp as e ON d.out_logi_id = e.corp_id'))->where($conditions)->field('a.*,c.s_url,d.out_logi_no,e.name as bname')->select();
    }

   public function getOrderByFxoid( $fxoid ){
        $conditions['poid'] = $fxoid;
       return $this->where($conditions)->select();
   }

}
