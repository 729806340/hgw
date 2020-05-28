<?php

class order_goodsModel extends Model {
   
    //获取suborder
   public function getOrderItems($orderid) {
        $conditions['a.order_id'] = array('in', $orderid);
        return $this->table('orders as a')->join(array('left join shopwwi_order_goods as b ON a.order_id = b.order_id','left join shopwwi_goods as c ON b.goods_id = c.goods_id','left join shopwwi_b2c_order_fenxiao as d ON a.fx_order_id = d.orderno','left join shopwwi_orders e on e.order_id=a.order_id','left join shopwwi_order_common as f on a.order_id=f.order_id'))->where($conditions)->field('b.goods_name as order_good_name,c.goods_name,b.goods_price,b.goods_num,e.order_amount,e.goods_amount,e.order_state,a.fx_order_id,e.shipping_code,f.shipping_express_id,b.goods_image,b.goods_id')->select();
    }

   public function getOrderByFxoid( $fxoid ){
        $conditions['poid'] = $fxoid;
       return $this->where($conditions)->select();
   }
   
   public function getOrderGoodsInfo($condition)
   {
   		return $this->where($condition)->find();
   }

   public function getOrderGoodsList($condition)
   {
   		return $this->where($condition)->select();
   }
    public function getOrderGoodsListInfo($condition,$page='',$field="*",$limit='')
    {
        return $this->where($condition)->page($page)->field($field)->limit($limit)->select();
    }
    public function getGroupBusiness($condition = array(),$fields,$group='',$order = 'order_id desc',$limint=1000,$having='') {
        $result = $this->table ( 'order_goods' )->field ( $fields )->where ( $condition )->group($group)->having($having)->order($order)->limit($limint)->select ();
        return $result;
    }

}
