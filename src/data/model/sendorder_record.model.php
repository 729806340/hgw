<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/6/1 0001
 * Time: 上午 9:34
 */

defined('ByShopWWI') or exit('Access Invalid!');

class sendorder_recordModel extends Model{

    public  function  insertData($orderinfo,$shipping_express_id,$shipping_code){
        $condition['order_info']          = serialize($orderinfo);
        $condition['shipping_express_id'] = $shipping_express_id;
        $condition['shipping_code']       = $shipping_code;
        $condition['create_time']         = time();
        $condition['source']              = $orderinfo['buyer_name'];
        $condition['sourceid']            = $orderinfo['buyer_id'];
        $condition['fx_order_id']         = $orderinfo['fx_order_id'];
        $condition['order_sn']            = $orderinfo['order_sn'];
        return $this->insert($condition);
    }

    public function updateStatus($id){
        $condition['id']=$id;
        $data['order_status']="1";
        $data['send_time']=time();
        return $this->where($condition)->update($data);
    }

    public function updateErrorStatus($id){
         $condition['id']=$id;
         $data['order_status']="2";
         $data['send_time']=time();
          return $this->where($condition)->update($data);
    }

    public function updatedata($condition,$data){
        return $this->where($condition)->update($data);
    }

    public function getsendorder($condition, $page = null, $order = 'id desc', $field = '*', $limit = 0) {
        return $this->field($field)->where($condition)->page($page)->order($order)->limit($limit)->select();
    }
}