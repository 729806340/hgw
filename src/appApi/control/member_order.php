<?php
/**
 * 我的订单
 *
 *
 *
 *
 * @copyright  Copyright (c) 2007-2015 ShopNC Inc. (http://www.shopnc.net)
 * @license    http://www.shopnc.net
 * @link       http://www.shopnc.net
 * @since      File available since Release v1.1
 */


defined('ByShopWWI') or exit('Access Invalid!');

class member_orderControl extends mobileMemberControl
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 订单列表
     */
    public function order_listOp()
    {

        /** @var orderModel $model_order */
        $model_order = Model('order');
        $condition = array();
        $condition = $this->order_type_no($_POST["state_type"]);
        $condition['buyer_id'] = $this->member_info['member_id'];
        $condition['delete_state'] = 0;
        $field = "order_id,order_sn,pay_sn,store_id,store_name,goods_amount,order_amount,rcb_amount,pd_amount,shipping_fee,add_time,
            payment_code,payment_time,finnshed_time,lock_state,refund_state,order_state,evaluation_state";
        $order_list_array = $model_order->getOrderList($condition, 20, $field, 'order_id desc', '', array('order_common', 'order_goods'));
        $model_refund_return = Model('refund_return');
        $order_list_array = $model_refund_return->getGoodsRefundList($order_list_array, 1);//订单商品的退款退货显示
        //保留order_goods表的字段
        $need_goods_fields = array('rec_id', 'goods_id', 'goods_name', 'goods_price', 'goods_num', 'goods_image', 'refund');
        $order_group_list = $order_pay_sn_array = array();
        /*foreach ($order_list_array as $value) {
            $value['if_pay'] = $value['order_state']=='10'?'1':'';
            $value['if_cancel'] = $model_order->getOrderOperateState('buyer_cancel',$value);
            //显示收货
            $value['if_receive'] = $model_order->getOrderOperateState('receive',$value);
            //显示锁定中
            $value['if_lock'] = $model_order->getOrderOperateState('lock',$value);
            //显示物流跟踪
            $value['if_deliver'] = $model_order->getOrderOperateState('deliver',$value);
			$value['if_evaluation'] = $model_order->getOrderOperateState('evaluation',$value);
			$value['if_evaluation_again'] = $model_order->getOrderOperateState('evaluation_again',$value);
			$value['if_refund_cancel'] = $model_order->getOrderOperateState('refund_cancel',$value);
			$value['if_delete'] =  $model_order->getOrderOperateState('delete',$value);
			//显示删除订单(放入回收站)
			$value['if_delete'] = $model_order->getOrderOperateState('delete',$value);
			//显示永久删除
			$value['if_drop'] = $model_order->getOrderOperateState('drop',$value);
			$value['zengpin_list'] = false;
			//$refund_all = $value['refund_list'][0];
			//if (!empty($refund_all) && $refund_all['seller_state'] < 3) {//订单全部退款商家审核状态:1为待审核,2为同意,3为不同意
			    //$value['refund_all'] = $refund_all;
			//}
			//商品图
            foreach ($value['extend_order_goods'] as $k => $goods_info) {
                foreach($goods_info as $goods_param=>$goods_value){
                    if(!in_array($goods_param ,$need_goods_fields)){
                        unset($goods_info[$goods_param]);
                    }
                }        
                //empty($value['extend_order_goods'][$k]['refund']) and $value['extend_order_goods'][$k]['refund']=0;
				if ($goods_info['goods_type'] == 5) {
				    $goods_info['goods_image']=cthumb($goods_info['goods_image'], 240, $value['store_id']);
				    $value['zengpin_list'][] = $goods_info;
				} else {
				    $value['extend_order_goods'][$k] = $goods_info;
				    $value['extend_order_goods'][$k]['goods_image'] = cthumb($goods_info['goods_image'], 240, $value['store_id']);
				}
				
            }
            unset($value['refund_list']);
            unset($value['extend_order_common']);
            $order_group_list[$value['pay_sn']]['order_list'][] = $value;
            //如果有在线支付且未付款的订单则显示合并付款链接
            if ($value['order_state'] == ORDER_STATE_NEW) {
                $order_group_list[$value['pay_sn']]['pay_amount'] += $value['order_amount'] - $value['rcb_amount'] - $value['pd_amount'];
            }
            $order_group_list[$value['pay_sn']]['add_time'] = $value['add_time'];
            $order_pay_sn_array[] = $value['pay_sn'];
            
        }*/
        $new_order_group_list = array();
        $res = array();
        foreach ($order_list_array as $value) {

            $value['if_pay'] = $value['order_state'] == '10' ? '1' : '0';
            $value['if_cancel'] = $model_order->getOrderOperateState('buyer_cancel', $value);
            //显示收货
            $value['if_receive'] = $model_order->getOrderOperateState('receive', $value);
            //显示锁定中
            $value['if_lock'] = $model_order->getOrderOperateState('lock', $value);
            //显示物流跟踪
            $value['if_deliver'] = $model_order->getOrderOperateState('deliver', $value);
            $value['if_evaluation'] = $model_order->getOrderOperateState('evaluation', $value);
            $value['if_evaluation_again'] = $model_order->getOrderOperateState('evaluation_again', $value);
            $value['if_refund_cancel'] = $model_order->getOrderOperateState('refund_cancel', $value);
            $value['if_delete'] = $model_order->getOrderOperateState('delete', $value);
            //显示删除订单(放入回收站)
            $value['if_delete'] = $model_order->getOrderOperateState('delete', $value);
            //显示永久删除
            $value['if_drop'] = $model_order->getOrderOperateState('drop', $value);
            $value['zengpin_list'] = false;
            $value['goods_count'] = 0;
            //商品图
            foreach ($value['extend_order_goods'] as $k => $goods_info) {
                foreach ($goods_info as $goods_param => $goods_value) {
                    if (!in_array($goods_param, $need_goods_fields)) {
                        unset($goods_info[$goods_param]);
                    }
                }
                //empty($value['extend_order_goods'][$k]['refund']) and $value['extend_order_goods'][$k]['refund']=0;
                if ($goods_info['goods_type'] == 5) {
                    $goods_info['goods_image'] = cthumb($goods_info['goods_image'], 240, $value['store_id']);
                    $value['zengpin_list'][] = $goods_info;
                } else {
                    $value['extend_order_goods'][$k] = $goods_info;
                    $value['extend_order_goods'][$k]['goods_image'] = cthumb($goods_info['goods_image'], 240, $value['store_id']);
                }
                $value['goods_count'] += $goods_info['goods_num'];
            }
            unset($value['refund_list']);
            unset($value['extend_order_common']);
            $res[] = $value;
        }
        /*foreach ($order_group_list as $key => $value) {
            $value['pay_sn'] = strval($key);
            $new_order_group_list[] = $value;
        }*/
        $page_count = $model_order->gettotalpage();
        if (intval($_POST['curpage']) > $page_count) $res = array();
        output_data(array('order_list' => $res), mobile_page($page_count));
        //output_data(array('order_group_list' => $new_order_group_list), mobile_page($page_count));
    }


    private function order_type_no($stage)
    {
        switch ($stage) {
            case 'state_new':
                $condition['order_state'] = '10';
                break;
            case 'state_nosend':
                $where['buyer_id'] = $this->member_info['member_id'];
                $where['refund_type'] = '1';
                $where['refund_state'] = array('in', '1,2');
                $refund_ids = Model('refund_return')->field('order_id')->where($where)->select();
                $order_ids = array_column($refund_ids, 'order_id');
                if (count($order_ids) > 0) $condition['order_id'] = array('not in', $order_ids);
                $condition['order_state'] = '20';
                break;
            case 'state_send':
                $condition['order_state'] = '30';
                break;
            case 'state_notakes':
                $condition['order_type'] = '3';
                $condition['order_state'] = '30';
                break;
            case 'state_noeval':
                $condition['order_state'] = '40';
                $condition['evaluation_state'] = '0';
                break;
            case 'state_aftersale':
                $condition['refund_state'] = array('in', '1,2');
                break;
        }
        return $condition;
    }

    /**
     * 取消订单
     */
    public function order_cancelOp()
    {
        /** @var orderModel $model_order */
        $model_order = Model('order');
        $logic_order = Logic('order');
        $order_id = intval($_POST['order_id']);

        $condition = array();
        $condition['order_id'] = $order_id;
        $condition['buyer_id'] = $this->member_info['member_id'];
        $condition['order_type'] = 1;
        $order_info = $model_order->getOrderInfo($condition);
        $if_allow = $model_order->getOrderOperateState('buyer_cancel', $order_info);
        if (!$if_allow) {
            output_error('无权操作');
        }
        if (TIMESTAMP - 86400 < $order_info['api_pay_time']) {
            $_hour = ceil(($order_info['api_pay_time'] + 86400 - TIMESTAMP) / 3600);
            output_error('该订单曾尝试使用第三方支付平台支付，须在' . $_hour . '小时以后才可取消');
        }
        $result = $logic_order->changeOrderStateCancel($order_info, 'buyer', $this->member_info['member_name'], '其它原因');
        if (!$result['state']) {
            output_error($result['msg']);
        } else {
            output_data('订单取消成功');
        }
    }

    /**
     * 订单确认收货
     */
    public function order_receiveOp()
    {
        $model_order = Model('order');
        $logic_order = Logic('order');
        $order_id = intval($_POST['order_id']);

        $condition = array();
        $condition['order_id'] = $order_id;
        $condition['buyer_id'] = $this->member_info['member_id'];
        $condition['order_type'] = 1;
        $order_info = $model_order->getOrderInfo($condition);
        $if_allow = $model_order->getOrderOperateState('receive', $order_info);
        if (!$if_allow) {
            output_error('无权操作');
        }

        $result = $logic_order->changeOrderStateReceive($order_info, 'buyer', $this->member_info['member_name'], '签收了货物');
        if (!$result['state']) {
            output_error($result['msg']);
        } else {
            output_data('确认收货成功');
        }
    }

    /**
     * 物流跟踪
     */
    public function search_deliverOp()
    {
        $order_id = intval($_POST['order_id']);
        if ($order_id <= 0) {
            output_error('订单不存在');
        }

        $model_order = Model('order');
        $condition['order_id'] = $order_id;
        $condition['buyer_id'] = $this->member_info['member_id'];
        $order_info = $model_order->getOrderInfo($condition, array('order_common', 'order_goods'));
        if (empty($order_info) || !in_array($order_info['order_state'], array(ORDER_STATE_SEND, ORDER_STATE_SUCCESS))) {
            output_error('订单不存在');
        }

        $express = rkcache('express', true);
        $e_code = $express[$order_info['extend_order_common']['shipping_express_id']]['e_code'];
        $e_name = $express[$order_info['extend_order_common']['shipping_express_id']]['e_name'];

        $deliver_info = $this->_get_express($e_code, $order_info['shipping_code']);
        output_data(array('express_name' => $e_name, 'shipping_code' => $order_info['shipping_code'], 'deliver_info' => $deliver_info));
    }

    /**
     * 移除订单
     */
    public function order_deleteOp()
    {
        //$_POST['order_id'] =259992;
        $model_order = Model('order');
        $logic_order = Logic('order');
        $order_id = intval($_POST['order_id']);
        $condition = array();
        $condition['order_id'] = $order_id;
        $condition['buyer_id'] = $this->member_info['member_id'];
        $condition['order_type'] = 1;
        $order_info = $model_order->getOrderInfo($condition);
        $if_allow = $model_order->getOrderOperateState('delete', $order_info);
        if (!$if_allow) {
            output_error('无权操作');
        }
        $result = $logic_order->changeOrderStateRecycle($order_info, 'buyer', 'delete');
        if (!$result['state']) {
            output_error($result['msg']);
        } else {
            output_data('订单删除成功');
        }
    }

    public function order_dropOp()
    {
        //$_POST['order_id'] =259992;
        /** @var orderModel $model_order */
        $model_order = Model('order');
        $logic_order = Logic('order');
        $order_id = intval($_POST['order_id']);
        $condition = array();
        $condition['order_id'] = $order_id;
        $condition['buyer_id'] = $this->member_info['member_id'];
        $condition['order_type'] = 1;
        $order_info = $model_order->getOrderInfo($condition);
        $if_allow = $model_order->getOrderOperateState('drop', $order_info);
        if (!$if_allow) {
            output_error('无权操作');
        }
        $result = $logic_order->changeOrderStateRecycle($order_info, 'buyer', 'drop');
        if (!$result['state']) {
            output_error($result['msg']);
        } else {
            output_data('订单永久删除成功');
        }
    }

    /**
     * 订单详情
     */
    public function order_infoOp()
    {
        //$_POST['order_id']=259992;
        $order_id = intval($_POST['order_id']);
        if ($order_id <= 0) {
            output_error('订单不存在');
        }
        $model_order = Model('order');
        $condition = array();
        $condition['order_id'] = $order_id;
        $condition['buyer_id'] = $this->member_info['member_id'];
        $field = "order_id,order_sn,pay_sn,store_id,store_name,goods_amount,order_amount,rcb_amount,pd_amount,shipping_fee,add_time,
            payment_code,payment_time,shipping_code,finnshed_time,order_state,lock_state,evaluation_state";
        $order_info = $model_order->getOrderInfo($condition, array('order_goods', 'order_common', 'store'), $field);

        if (empty($order_info) || $order_info['delete_state'] == ORDER_DEL_STATE_DROP) {
            output_error('订单不存在');
        }
        $model_refund_return = Model('refund_return');
        $order_list = array();
        $order_list[$order_id] = $order_info;
        $order_list = $model_refund_return->getGoodsRefundList($order_list, 1);//订单商品的退款退货显示
        $order_info = $order_list[$order_id];
        $refund_all = $order_info['refund_list'][0];
        $order_info['store_member_id'] = $order_info['extend_store']['member_id'];
        $order_info['store_phone'] = $order_info['extend_store']['store_phone'];
        $order_info['store_credit'] = $order_info['extend_store']['store_credit'];
        //$order_info['refund_all'] = $refund_all;
        unset($order_info['extend_store']);
        if ($order_info['payment_time']) {
            $order_info['payment_time'] = date('Y-m-d H:i:s', $order_info['payment_time']);
        } else {
            $order_info['payment_time'] = '';
        }
        if ($order_info['finnshed_time']) {
            $order_info['finnshed_time'] = date('Y-m-d H:i:s', $order_info['finnshed_time']);
        } else {
            $order_info['finnshed_time'] = '';
        }
        if ($order_info['add_time']) {
            $order_info['add_time'] = date('Y-m-d H:i:s', $order_info['add_time']);
        } else {
            $order_info['add_time'] = '';
        }

        if ($order_info['extend_order_common']['order_message']) {
            $order_info['order_message'] = $order_info['extend_order_common']['order_message'];
        }
        $order_info['invoice'] = "类型：" . $order_info['extend_order_common']['invoice_info']['类型'] . "抬头：" . $order_info['extend_order_common']['invoice_info']['抬头'] . "内容：" . $order_info['extend_order_common']['invoice_info']['内容'];
        $order_info['reciver_phone'] = $order_info['extend_order_common']['reciver_info']['phone'];
        $order_info['reciver_name'] = $order_info['extend_order_common']['reciver_name'];
        $order_info['reciver_addr'] = $order_info['extend_order_common']['reciver_info']['address'];
        $order_info['promotion'] = array();

        if (!empty($order_info['extend_order_common']['promotion_info'])) {
            $promotion = unserialize($order_info['extend_order_common']['promotion_info']);
            foreach ($promotion as $key => $val) {
                $order_info['promotion'][$key]['title'] = $val[0];
                $order_info['promotion'][$key]['desc'] = $val[1];
            }
        }
        $order_info['if_pay'] = $order_info['order_state'] == 10 ? '1' : '';
        //显示锁定中
        $order_info['if_lock'] = $model_order->getOrderOperateState('lock', $order_info);
        //显示取消订单
        $order_info['if_buyer_cancel'] = $model_order->getOrderOperateState('buyer_cancel', $order_info);
        //显示退款取消订单
        $order_info['if_refund_cancel'] = $model_order->getOrderOperateState('refund_cancel', $order_info);
        //显示投诉
        $order_info['if_complain'] = $model_order->getOrderOperateState('complain', $order_info);
        //显示收货
        $order_info['if_receive'] = $model_order->getOrderOperateState('receive', $order_info);
        //显示物流跟踪
        $order_info['if_deliver'] = $model_order->getOrderOperateState('deliver', $order_info);
        //显示评价
        $order_info['if_evaluation'] = $model_order->getOrderOperateState('evaluation', $order_info);
        //显示分享
        $order_info['if_share'] = $model_order->getOrderOperateState('share', $order_info);
        $order_info['ownshop'] = $model_order->getOrderOperateState('share', $order_info);
        //显示系统自动取消订单日期
        if ($order_info['order_state'] == ORDER_STATE_NEW) {
            $order_info['order_cancel_day'] = $order_info['add_time'] + ORDER_AUTO_CANCEL_TIME * 3600;
        }
        $order_info['if_deliver'] = false;
        //显示快递信息
        $order_info['shipping_code']=trim($order_info['shipping_code']);
        if ($order_info['shipping_code'] != '') {
            $order_info['if_deliver'] = true;
            $express = rkcache('express', true);
            $order_info['express_info']['e_code'] = $express[$order_info['extend_order_common']['shipping_express_id']]['e_code'];
            $order_info['express_info']['e_name'] = $express[$order_info['extend_order_common']['shipping_express_id']]['e_name'];
            $order_info['express_info']['e_url'] = $express[$order_info['extend_order_common']['shipping_express_id']]['e_url'];
        }

        //显示系统自动收获时间
        if ($order_info['order_state'] == ORDER_STATE_SEND) {
            $order_info['order_confirm_day'] = $order_info['delay_time'] + ORDER_AUTO_RECEIVE_DAY * 24 * 3600;
        }

        if ($order_info['lock_state']) {
            $order_info['state_desc'] = '退款退货中';
        }

        //如果订单已取消，取得取消原因、时间，操作人
        if ($order_info['order_state'] == ORDER_STATE_CANCEL) {
            $close_info = $model_order->getOrderLogInfo(array('order_id' => $order_info['order_id']), 'log_id desc');
            //$order_info['close_info'] = $close_info;
            $order_info['state_desc'] = '交易关闭';
            $order_info['order_tips'] = $close_info['log_role'] . "于" . date('Y-m-d H:i:s', $order_info['log_time']) . $close_info['log_msg'];
        }
        if (empty($order_info['zengpin_list'])) {
            $order_info['goods_count'] = count($order_info['goods_list']);
        } else {
            $order_info['goods_count'] = count($order_info['goods_list']) + 1;
        }
        $order_info['real_pay_amount'] = $order_info['order_amount'] + $order_info['shipping_fee'];
        //取得其它订单类型的信息
        $model_order->getOrderExtendInfo($order_info);
        $order_info['zengpin_list'] = array();
        $need_goods_fields = array('rec_id', 'goods_id', 'goods_name', 'goods_price', 'goods_num', 'goods_images', 'refund');
        if (is_array($order_info['extend_order_goods'])) {
            foreach ($order_info['extend_order_goods'] as $key => $val) {
                $order_info['extend_order_goods'][$key]['image_url'] = cthumb($val['goods_image'], 240, $val['store_id']);
                $order_info['extend_order_goods'][$key]['refund_amount'] = '';  //退款金额
                if (is_array($refund_all) && !empty($refund_all)) {
                    $order_info['extend_order_goods'][$key]['refund_id'] = $refund_all['refund_id'];
                } elseif (is_array($val['extend_refund']) && !empty($val['extend_refund'])) {
                    $order_info['extend_order_goods'][$key]['refund_id'] = $val['extend_refund']['refund_id'];
                }
                //判断退款金额
                if (is_array($refund_all) && !empty($refund_all) && $refund_all['admin_time'] > 0) {
                    $order_info['extend_order_goods'][$key]['refund_amount'] = $val['goods_pay_price'];

                } else if ($val['extend_refund']['admin_time'] > 0) {
                    $order_info['extend_order_goods'][$key]['refund_amount'] = $val['extend_refund']['refund_amount'];
                }

                foreach ($val as $k => $v) {
                    if (!in_array($k, $need_goods_fields)) {
                        unset($order_info['extend_order_goods'][$key][$k]);
                    }
                }
                if ($val['goods_type'] == 5) {
                    $order_info['zengpin_list'][] = $val;
                    unset($order_info['extend_order_goods'][$key]);
                }
            }
        }

        unset($order_info['refund_list']);
        //过滤order_common表不需要的字段
        $need_orderCommon_fields = array('voucher_price', 'voucher_code', 'order_pointscount', 'express_info', 'deliver_explain', 'receive_info', 'promotion_total', 'discount');
        foreach ($order_info['extend_order_common'] as $key => $val) {
            if (!in_array($key, $need_orderCommon_fields)) {
                unset($order_info['extend_order_common'][$key]);
            }
        }
        $order_info['extend_order_goods']=array_values($order_info['extend_order_goods']);
        output_data(array('order_info' => $order_info));
    }


    /**
     * 物流详情
     */
    public function get_current_deliverOp()
    {
        $order_id = intval($_POST['order_id']);
        if ($order_id <= 0) {
            output_error('订单不存在');
        }
        $model_order = Model('order');
        $condition['order_id'] = $order_id;
        $condition['buyer_id'] = $this->member_info['member_id'];
        $order_info = $model_order->getOrderInfo($condition, array('order_common', 'order_goods'));
        if (empty($order_info) || !in_array($order_info['order_state'], array(ORDER_STATE_SEND, ORDER_STATE_SUCCESS))) {
            output_error('订单不存在');
        }

        $express = rkcache('express', true);
        $e_code = $express[$order_info['extend_order_common']['shipping_express_id']]['e_code'];
        $e_name = $express[$order_info['extend_order_common']['shipping_express_id']]['e_name'];

        $deliver_info = $this->_get_express($e_code, $order_info['shipping_code']);


        $data = array();
        $data['deliver_info']['context'] = $e_name;
        $data['deliver_info']['time'] = $deliver_info['0'];
        output_data($data);
    }

    /**
     * 从第三方取快递信息
     *
     */
    public function _get_express($e_code, $shipping_code)
    {
        $content = Model('express')->get_express($e_code, $shipping_code);
        if (empty($content)) {
            output_error('物流信息查询失败');
        }
        $output = array();
        foreach ($content as $k => $v) {
            if ($v['time'] == '') continue;
            $output[$k]['time'] = $v['time'];
            $output[$k]['context'] = $v['context'];
//            $output[]= $v['time'].'&nbsp;&nbsp;'.$v['context'];
        }
        return $output;
    }

}
