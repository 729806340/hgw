<?php
/**
 * 交易新模型
 *
 *
 *
 * * @汉购网 (c) 2015-2018 ShopWWI Inc. (http://www.hangowa.com)
 * @license    http://www.shopwwi.c om
 * @link       交流群号：
 * @since      汉购网提供技术支持 授权请购买shopnc授权
 */
defined('ByShopWWI') or exit('Access Invalid!');
class tradeModel extends Model{
    public function __construct() {
        parent::__construct();
    }
    /**
     * 订单处理天数
     *
     */
    public function getMaxDay($day_type = 'all') {
        $max_data = array(
            'order_refund' => 15,//收货完成后可以申请退款退货(前台普通用户)
            'order_refund_system' => 60,//收货完成后可以申请退款退货（系统用户：分销、管理员等）
            'refund_confirm' => 7,//卖家不处理退款退货申请时按同意处理
            'refund_confirm_1' => 2,//卖家不处理退款申请时按同意处理
            'refund_confirm_2' => 10,//卖家不处理退货申请时按同意处理
            'return_confirm' => 7,//卖家不处理收货时按弃货处理
            'return_delay' => 5//退货的商品发货多少天以后才可以选择没收到
            );
        if ($day_type == 'all') return $max_data;//返回所有
        if (intval($max_data[$day_type]) < 1) $max_data[$day_type] = 1;//最小的值设置为1
        return $max_data[$day_type];
    }
    /**
     * 订单状态
     *
     */
    public function getOrderState($type = 'all') {
        $state_data = array(
            'order_cancel' => ORDER_STATE_CANCEL,//0:已取消
            'order_default' => ORDER_STATE_NEW,//10:未付款
            'order_tuan_paid' => ORDER_STATE_TUAN_PAY,//15:拼团组团中
            'order_paid' => ORDER_STATE_PAY,//20:已付款
            'order_prepare' => ORDER_STATE_PREPARE,//21：备货中
            'order_shipped' => ORDER_STATE_SEND,//30:已发货
            'order_completed' => ORDER_STATE_SUCCESS //40:已收货
            );
        if ($type == 'all') return $state_data;//返回所有
        return $state_data[$type];
    }

    /**
     * 更新退款申请
     * @param int $member_id 会员编号
     * @param int $store_id 店铺编号
     * @param int $no_fx 是否过滤分销退款单 0,不过滤；1，过滤
     */
    public function editRefundConfirm($member_id=0, $store_id=0, $no_fx = 0) {
        /*卖家不处理退款申请时按同意并弃货处理*/
        $refund_confirm = $this->getMaxDay('refund_confirm_1');//卖家不处理退款申请时按同意并弃货处理
        $day = time()-$refund_confirm*60*60*24;
        $condition = "refund_type = 1 and seller_state=1 and add_time<".$day;//状态:1为待审核,2为同意,3为不同意
        $condition_sql = "";
        if ($member_id > 0) {
            $condition_sql = " buyer_id = '".$member_id."'  and ";
        }
        if ($store_id > 0) {
            $condition_sql = " store_id = '".$store_id."' and ";
        }
        if( $no_fx == 1 ) {
        	$service = Service('Fenxiao') ;
        	$fxMembers = $service -> getFenxiaoMembers() ;
        	$fx_mids = array_keys($fxMembers) ;
        	$condition_sql .= " buyer_id NOT IN (". implode(',', $fx_mids) .") AND " ;
        }
        $condition_sql = $condition_sql.$condition;
        $refund_array = array();
        $refund_array['refund_state'] = '2';//状态:1为处理中,2为待管理员处理,3为已完成
        $refund_array['seller_state'] = '2';//卖家处理状态:1为待审核,2为同意,3为不同意
        $refund_array['return_type'] = '1';//退货类型:1为不用退货,2为需要退货
        $refund_array['seller_time'] = time();
        $refund_array['seller_message'] = '超过'.$refund_confirm.'天未处理退款退货申请，按同意处理。';
        $refund = $this->table('refund_return')->field('refund_sn,store_id,order_lock,refund_type')->where($condition_sql)->select();
        $this->table('refund_return')->where($condition_sql)->update($refund_array);

        // 发送商家提醒
        foreach ((array)$refund as $val) {
            // 参数数组
            $param = array();
            $param['type'] = $val['order_lock'] == 2 ? '售前' : '售后';
            $param['refund_sn'] = $val['refund_sn'];
            if (intval($val['refund_type']) == 1) {    // 退款
                $this->sendStoreMsg('refund_auto_process', $val['store_id'], $param);
            } else {                                     // 退货
                $this->sendStoreMsg('return_auto_process', $val['store_id'], $param);
            }
        }


        /*卖家不处理退货申请时按同意并弃货处理*/
        $refund_confirm = $this->getMaxDay('refund_confirm_2');//卖家不处理退款申请时按同意并弃货处理
        $day = time()-$refund_confirm*60*60*24;
        $condition = "refund_type = 2 and seller_state=1 and add_time<".$day;//状态:1为待审核,2为同意,3为不同意
        $condition_sql = "";
        if ($member_id > 0) {
            $condition_sql = " buyer_id = '".$member_id."'  and ";
        }
        if ($store_id > 0) {
            $condition_sql = " store_id = '".$store_id."' and ";
        }
        if( $no_fx == 1 ) {
            $service = Service('Fenxiao') ;
            $fxMembers = $service -> getFenxiaoMembers() ;
            $fx_mids = array_keys($fxMembers) ;
            $condition_sql .= " buyer_id NOT IN (". implode(',', $fx_mids) .") AND " ;
        }
        $condition_sql = $condition_sql.$condition;
        $refund_array = array();
        $refund_array['refund_state'] = '2';//状态:1为处理中,2为待管理员处理,3为已完成
        $refund_array['seller_state'] = '2';//卖家处理状态:1为待审核,2为同意,3为不同意
        $refund_array['return_type'] = '1';//退货类型:1为不用退货,2为需要退货
        $refund_array['seller_time'] = time();
        $refund_array['seller_message'] = '超过'.$refund_confirm.'天未处理退款退货申请，按同意处理。';
        $refund = $this->table('refund_return')->field('refund_sn,store_id,order_lock,refund_type')->where($condition_sql)->select();
        $this->table('refund_return')->where($condition_sql)->update($refund_array);

        // 发送商家提醒
        foreach ((array)$refund as $val) {
            // 参数数组
            $param = array();
            $param['type'] = $val['order_lock'] == 2 ? '售前' : '售后';
            $param['refund_sn'] = $val['refund_sn'];
            if (intval($val['refund_type']) == 1) {    // 退款
                $this->sendStoreMsg('refund_auto_process', $val['store_id'], $param);
            } else {                                     // 退货
                $this->sendStoreMsg('return_auto_process', $val['store_id'], $param);
            }
        }


        /*卖家不处理收货时按弃货处理*/
        $return_confirm = $this->getMaxDay('return_confirm');//卖家不处理收货时按弃货处理
        $day = time()-$return_confirm*60*60*24;
        $condition = " seller_state=2 and goods_state=2 and return_type=2 and delay_time<".$day;//物流状态:1为待发货,2为待收货,3为未收到,4为已收货
        $condition_sql = "";
        if ($member_id > 0) {
            $condition_sql = " buyer_id = '".$member_id."'  and ";
        }
        if ($store_id > 0) {
            $condition_sql = " store_id = '".$store_id."' and ";
        }
        $condition_sql = $condition_sql.$condition;
        $refund_array = array();
        $refund_array['refund_state'] = '2';//状态:1为处理中,2为待管理员处理,3为已完成
        $refund_array['return_type'] = '1';//退货类型:1为不用退货,2为需要退货
        $refund_array['seller_message'] = '超过'.$return_confirm.'天未处理收货，按弃货处理';
        $refund = $this->table('refund_return')->field('refund_sn,store_id,order_lock,refund_type')->where($condition_sql)->select();
        $this->table('refund_return')->where($condition_sql)->update($refund_array);

        // 发送商家提醒
        foreach ((array)$refund as $val) {
            // 参数数组
            $param = array();
            $param['type'] = $val['order_lock'] == 2 ? '售前' : '售后';
            $param['refund_sn'] = $val['refund_sn'];
            $this->sendStoreMsg('return_auto_receipt', $val['store_id'], $param);
        }
    }

    /**
     * 发送店铺消息
     * @param string $code
     * @param int $store_id
     * @param array $param
     */
    private function sendStoreMsg($code, $store_id, $param) {
        QueueClient::push('sendStoreMsg', array('code' => $code, 'store_id' => $store_id, 'param' => $param));
    }
}
