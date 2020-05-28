<?php

namespace Home\Model;

use Think\Model;

//$model->query('select * from user where id=%d and status=%d',array($id,$status));
class OrderModel extends Model {

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

    public function getOrderList($pagesize = 6, $page = 1, $status, $oid = '', $begintime = '', $endtime = '') {
        $conditions = array();
        if ($status != '') {
            $conditions['status'] = $status;
        }
        if ($oid) {
            $conditions['oid'] = array('like', '%' . $oid . '%');
        }
        if ($begintime && $endtime)
            $conditions['ctime'] = array(array('gt', strtotime($begintime)), array('lt', strtotime($endtime)));
        if (!$begintime && $endtime)
            $conditions['ctime'] = array(array('gt', 0), array('lt', strtotime($endtime)));
        if ($begintime && !$endtime)
            $conditions['ctime'] = array(array('gt', strtotime($begintime)), array('lt', time()));
        $offset = 0;
        if ($page > 1)
            $offset = ($page - 1) * $pagesize;
        $totalrows = $this->where($conditions)->count();
        $result = $this->order('id desc')->limit($offset, $pagesize)->where($conditions)->field('*,FROM_UNIXTIME(ctime,"%Y-%m-%d %H:%i:%S") as datetime ')->select();
//        $totalrows = $this->where($data)->count();
        $pagetotal = ceil($totalrows / $pagesize);
        return array($totalrows, $result, $pagetotal);
    }

    public function createOrder() {
        if (1) {
            //选择物流
            $shipping = array(
                'shipping_id' => '3',
                'is_protect' => 'false',
                'shipping_name' => '国内快递',
                'cost_shipping' => '0.00', //快递费用
                'cost_protect' => '0.00'
            );

            //付款方式
            $payinfo = array(
                'pay_app_id' => 'deposit',
                'cost_payment' => '0.00' //支付手续费，请都填0.00
            );

            //收货信息
            $consignee = array(
                'name' => $data['name'],
                'addr' => $data['addr'],
                'zip' => '2300233',
                'telephone' => '',
                'mobile' => $data['mobile'],
                'email' => NULL,
                'area' => 'mainland:湖北/武汉市/洪山区:25',
                'r_time' => '任意时间,任意时间段',
                'meta' => array()
            );

            //购买产品信息
            $order_objects = array(
                0 => array(
                    'product_id' => $data['product_id'],
                    'num' => $data['num'],
                )
            );

            //传递的所有参数组装
            $params = array(
                'instance_pay' => 1,
                'member_id' => $data['member_id'],
                'memo' => '',
                'ip' => '127.0.0.1',
                'weight' => 0,
                'itemnum' => 1,
                'cost_item' => $data['totalprice'], // 订单商品总价格
                'cost_tax' => 0,
                'total_amount' => $data['totalprice'], // 商品默认货币总值
                'cur_amount' => $data['totalprice'], //final_amount 订单总额, 包含支付价格,税等
                'pmt_goods' => '0.00', //商品优惠金额
                'pmt_order' => '8.00', //订单优惠金额
                'discount' => '0.00', //
                'shipping' => json_encode($shipping),
                'payinfo' => json_encode($payinfo),
                'consignee' => json_encode($consignee),
                'order_objects' => json_encode($order_objects)
            );

//            $uri = "http://www.hangowa.com/index.php/api?method=b2c.order.basic.create&sign=eff90f9f07d591ac969dfc4750674ce2&";
            $uri = "http://192.168.11.98/index.php/api?method=b2c.order.basic.create&sign=eff90f9f07d591ac969dfc4750674ce2&";
        }

        $uri .= http_build_query($params);
        file_get_contents($uri);
    }
}
