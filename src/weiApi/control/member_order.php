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
        if ($_POST['shequ_tuan_id'] > 0) {
            $condition['shequ_tuan_id'] = array('gt', 0);
        }
        $field = "order_id,tuan_id,shequ_tuan_id,shequ_tz_id,order_type,order_sn,pay_sn,store_id,store_name,goods_amount,order_amount,rcb_amount,pd_amount,shipping_fee,add_time,
            payment_code,payment_time,finnshed_time,lock_state,refund_state,order_state,evaluation_state,shipping_code,chain_code";
        $order_list_array = $model_order->getOrderList($condition, 20, $field, 'order_id desc', '', array('order_common', 'order_goods'));
        /** @var refund_returnModel $model_refund_return */
        $model_refund_return = Model('refund_return');
        $order_list_array = $model_refund_return->getGoodsRefundList($order_list_array, 1);//订单商品的退款退货显示
        //保留order_goods表的字段
        $need_goods_fields = array('rec_id', 'goods_id', 'goods_name', 'goods_price', 'goods_num', 'goods_image', 'refund', 'goods_type', 'xianshi_num');
        $res = array();
        $shequ_tuanzhang_list = array();
        $shequ_tuanzhang_ids = array_unique(array_column($order_list_array, 'shequ_tz_id'));
        if (!empty($shequ_tuanzhang_ids)) {
            /** @var shequ_tuanzhangModel $shequ_tuanzhang_model */
            $shequ_tuanzhang_model = Model('shequ_tuanzhang');
            $shequ_tuanzhang_list = $shequ_tuanzhang_model->getList(array('id' => array('in', $shequ_tuanzhang_ids)));
            $shequ_tuanzhang_list = array_under_reset($shequ_tuanzhang_list, 'id');
        }
        /** @var memberModel $model_member */
        $model_member = Model('member');
        foreach ($order_list_array as $value) {
            $shequ_tuan_info = '';
            if ($value['shequ_tz_id'] > 0 && isset($shequ_tuanzhang_list[$value['shequ_tz_id']])) {
                $shequ_tuanzhang_info = $shequ_tuanzhang_list[$value['shequ_tz_id']];
                $shequ_tuan_info = array(
                    'tz_avatar' => $shequ_tuanzhang_info['avatar'],
                    'tz_phone' => $shequ_tuanzhang_info['phone'],
                    'tz_name' => $shequ_tuanzhang_info['name'],
                );
                $member_tuanzhang_info = $model_member->getMemberInfoByID($shequ_tuanzhang_info['member_id']);
                $shequ_tuan_info['deliver_type'] = $value['chain_code'] == 0 ? '物流发货' : '农猫速达配送';
                $shequ_tuan_info['tz_name'] = $member_tuanzhang_info['wx_nick_name'];
                $shequ_tuan_info['tz_avatar'] = $member_tuanzhang_info['wx_user_avatar'];
            }
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
            if ($value['shequ_tuan_id'] > 0 && $value['if_refund_cancel'] && ($value['finnshed_time'] && (TIMESTAMP - $value['finnshed_time']) > C('shequ_refund_time'))) {
                $value['if_refund_cancel'] = false;
            }
            $value['if_chain_receive'] = $model_order->getOrderOperateState('chain_receive', $value);
            if ($value['shequ_tuan_id'] > 0) {
                $value['if_chain_receive'] = false;
            }
            //$value['if_delete'] = $model_order->getOrderOperateState('delete', $value);
            //显示删除订单(放入回收站)

            $value['if_delete'] = $model_order->getOrderOperateState('delete', $value);
            //显示永久删除
            $value['if_drop'] = $model_order->getOrderOperateState('drop', $value);
            //显示团购分享
            $value['if_pin_share'] = $model_order->getOrderOperateState('pin_share', $value);
            $value['pin_share_member_name'] = $this->member_info['member_name'];
            $value['goods_count'] = 0;

            if($value['lock_state'] > 0 ) {
                $value['state_desc'] = '退款中';
            } else if ($value['refund_state'] > 0 ) {
                $value['state_desc'] = '退款完成' ;
            }

            if ($value['order_state'] == ORDER_STATE_PAY && $value['chain_code'] > 0 && $value['shequ_tuan_id'] == 0) {
                $value['state_desc'] = '待自提';
            }
            
            if ($value['order_state'] == ORDER_STATE_CANCEL && $value['tuan_id'] > 0) {
                $value['state_desc'] = '拼团失败';
            }

            //商品图
            foreach ($value['extend_order_goods'] as $k => $goods_info) {
                foreach ($goods_info as $goods_param => $goods_value) {
                    if (!in_array($goods_param, $need_goods_fields)) {
                        unset($goods_info[$goods_param]);
                    }
                }

                $goods_info['is_zengpin'] = 0;
                $goods_info['is_miaosao'] = 0;
                $goods_info['is_pin'] = 0;

                //empty($value['extend_order_goods'][$k]['refund']) and $value['extend_order_goods'][$k]['refund']=0;
                if ($goods_info['goods_type'] == 5) {
                    $goods_info['is_zengpin'] = 1;
                } elseif ($goods_info['goods_type'] == 3) {
                    $goods_info['is_miaosao'] = 1;
                    $goods_info['xianshi_num'] = $goods_info['xianshi_num'] > 0 ? $goods_info['xianshi_num'] : $goods_info['goods_num'];
                } elseif ($goods_info['goods_type'] == 10) {
                    $goods_info['is_pin'] = 1;
                }
                $value['extend_order_goods'][$k] = $goods_info;
                $value['extend_order_goods'][$k]['goods_image'] = cthumb($goods_info['goods_image'], 240, $value['store_id']);
                $value['goods_count'] += $goods_info['goods_num'];
            }
            $value['shipping_code'] = trim($value['shipping_code']);
            $value['promotion_info'] = $this->deal_promotion(unserialize($value['extend_order_common']['promotion_info']));
            unset($value['refund_list']);
            unset($value['extend_order_common']);
            //$res[] = $value;
            $res[$value['pay_sn']]['list'][] = $value;
            $res[$value['pay_sn']]['price'] += $value['order_amount'];
            $res[$value['pay_sn']]['shequ_tuan_info'] = $shequ_tuan_info;
            if (!isset($res[$value['pay_sn']]['if_pay'])) {
                $res[$value['pay_sn']]['if_pay'] = false;
            }
            if ($value['if_pay']) {
                $res[$value['pay_sn']]['if_pay'] = true;
            }
        }
        $page_count = $model_order->gettotalpage();
        if (intval($_POST['curpage']) > $page_count) $res = array();
        output_data(array('order_list' => $res), mobile_page($page_count));
    }

    /**
     * 处理满减满赠展示
     * @param $promotion
     * @return array
     */
    private function deal_promotion($promotion) {
        $promotion_data = array();
        if (empty($promotion) || !is_array($promotion)) {
            return $promotion_data;
        }
        foreach ($promotion as $val) {
            if ($val[0] == '满即送' && !empty($val[2])) {
                return $val[2];
            }
        }
    }

    private function order_type_no($stage)
    {
        switch ($stage) {
            case 'state_new':
                $condition['order_state'] = '10';
                break;
            case 'state_pin':
                $condition['order_state'] = '15';
                $condition['chain_code'] = 0;
                break;
            case 'state_nosend':
                //$where['buyer_id'] = $this->member_info['member_id'];
                //$where['refund_type'] = '1';
                //$where['refund_state'] = array('in', '1,2');
                //$refund_ids = Model('refund_return')->field('order_id')->where($where)->select();
                //$order_ids = array_column($refund_ids, 'order_id');
                //if (count($order_ids) > 0) $condition['order_id'] = array('not in', $order_ids);
                $condition['order_state'] = '20';
                $condition['chain_code'] = 0;
                break;
            case 'state_send':
                $condition['order_state'] = '30';
                $condition['chain_code'] = 0;
                break;
            case 'state_chain':
                $condition['order_type'] = '3';
                $condition['order_state'] = ORDER_STATE_PAY;
                $condition['chain_code'] = array('gt', 0);
                break;
            case 'state_noeval':
                $condition['order_state'] = '40';
                //$condition['evaluation_state'] = '0';
                break;
            case 'state_cancel':
                $condition['order_state'] = '0';
                break;
            case 'state_wait':
                $condition['order_state'] = array('between', array(ORDER_STATE_PAY,ORDER_STATE_SEND));
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
        $condition['order_type'] = array('in', array(1, 3));
        $order_info = $model_order->getOrderInfo($condition);
        $if_allow = $model_order->getOrderOperateState('buyer_cancel', $order_info);
        if (!$if_allow) {
            output_error('无权操作');
        }
        /*if (TIMESTAMP - 86400 < $order_info['api_pay_time']) {
            $_hour = ceil(($order_info['api_pay_time'] + 86400 - TIMESTAMP) / 3600);
            output_error('该订单曾尝试使用第三方支付平台支付，须在' . $_hour . '小时以后才可取消');
        }*/
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
        $condition['order_type'] = array('in', array(1, 4));
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
        if (empty($deliver_info)) {
            $deliver_info = array(array(
                'time' => date('Y-m-d H:i:s', $order_info['extend_order_common']['shipping_time']),
                'context' => '卖家已发货',
            ));
        }
        output_data(array('express_name' => $e_name, 'shipping_code' => trim($order_info['shipping_code']), 'deliver_info' => $deliver_info));
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
        $order_id = intval($_POST['order_id']);
        $tz_id = intval($_POST['tz_id']);
        if ($order_id <= 0) {
            output_error('订单不存在');
        }
        /** @var orderModel $model_order */
        $model_order = Model('order');
        $condition = array();
        $condition['order_id'] = $order_id;
        $condition['buyer_id'] = $this->member_info['member_id'];
        if ($tz_id > 0) {
            unset($condition['buyer_id']);
            $condition['shequ_tz_id'] = $tz_id;
        }
        $field = "order_id,order_sn,shequ_tuan_id,shequ_tz_id,pay_sn,store_id,store_name,goods_amount,order_amount,rcb_amount,pd_amount,shipping_fee,add_time,
            payment_code,payment_time,shipping_code,finnshed_time,order_state,lock_state,evaluation_state,chain_code,order_type,tuan_id,delete_state";
        $order_info = $model_order->getOrderInfo($condition, array('order_goods', 'order_common', 'store'), $field);

        if (empty($order_info) || $order_info['delete_state'] == ORDER_DEL_STATE_DROP) {
            output_error('订单不存在');
        }
        /** @var refund_returnModel $model_refund_return */
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

        //发货时间
        $order_info['shipping_time'] = '';
        if ($order_info['extend_order_common']['shipping_time']) {
            $order_info['shipping_time'] = date('Y-m-d H:i:s', $order_info['extend_order_common']['shipping_time']);
        }

        $order_info['order_message'] = '';
        if ($order_info['extend_order_common']['order_message']) {
            $order_info['order_message'] = $order_info['extend_order_common']['order_message'];
        }
        $order_info['invoice'] = "类型：" . $order_info['extend_order_common']['invoice_info']['类型'] . "抬头：" . $order_info['extend_order_common']['invoice_info']['抬头'] . "内容：" . $order_info['extend_order_common']['invoice_info']['内容'];
        $order_info['reciver_phone'] = $order_info['extend_order_common']['reciver_info']['phone'];
        $order_info['reciver_name'] = $order_info['extend_order_common']['reciver_name'];
        $order_info['reciver_addr'] = $order_info['extend_order_common']['reciver_info']['address'];
        $order_info['promotion'] = array(
            'red_money' => 0,
            'voucher_money' => 0
        );

        if (!empty($order_info['extend_order_common']['promotion_info'])) {
            $promotion = unserialize($order_info['extend_order_common']['promotion_info']);

            $order_info['extend_order_common']['promotion'] = $this->deal_promotion($promotion);
            foreach ($promotion as $key => $val) {
                if (count($val) != 2) {
                    continue;
                }
                if ($val[0] == '平台红包') {
                    preg_match("/([\d.]+)/", $val[1], $money_match);
                    $order_info['promotion']['red_money'] = $money_match[0];
                }
                if ($val[0] == '店铺代金券') {
                    preg_match("/([\d.]+)/", $val[1], $money_match);
                    $order_info['promotion']['voucher_money'] = $money_match[0];
                }
            }
            //$order_info['promotion'][$key]['title'] = $val[0];
            //$order_info['promotion'][$key]['desc'] = $val[1];
        }
        $order_info['if_pay'] = $order_info['order_state'] == 10 ? '1' : '';
        //显示锁定中
        $order_info['if_lock'] = $model_order->getOrderOperateState('lock', $order_info);
        //显示取消订单
        $order_info['if_buyer_cancel'] = $model_order->getOrderOperateState('buyer_cancel', $order_info);
        //显示退款取消订单
        $order_info['if_refund_cancel'] = $model_order->getOrderOperateState('refund_cancel', $order_info);
        if ($order_info['shequ_tuan_id'] > 0 && $order_info['if_refund_cancel'] && ($order_info['finnshed_time'] &&  (TIMESTAMP - $order_info['finnshed_time']) > C('shequ_refund_time') )) {
            $order_info['if_refund_cancel'] = false;
        }

        //显示投诉
        $order_info['if_complain'] = $model_order->getOrderOperateState('complain', $order_info);
        //显示收货
        $order_info['if_receive'] = $model_order->getOrderOperateState('receive', $order_info);
        //显示物流跟踪
        $order_info['if_deliver'] = $model_order->getOrderOperateState('deliver', $order_info);
        //显示评价
        $order_info['if_evaluation'] = $model_order->getOrderOperateState('evaluation', $order_info);
        //显示门店自提
        $order_info['if_chain_receive'] = $model_order->getOrderOperateState('chain_receive', $order_info);
        if ($order_info['shequ_tuan_id'] > 0) {
            $value['if_chain_receive'] = false;
        }
        //显示分享
        $order_info['if_share'] = $model_order->getOrderOperateState('share', $order_info);
        //显示团购分享
        $order_info['if_pin_share'] = $model_order->getOrderOperateState('pin_share', $order_info);
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
            $order_info['express_info']['shipping_code'] = $order_info['shipping_code'];
            $order_info['express_info']['shipping_time'] = date('Y-m-d H:i:s', $order_info['extend_order_common']['shipping_time']);
        }

        if ($tz_id > 0) {
            $order_info['if_pin_share'] = false;
            $order_info['if_share'] = false;
            $order_info['if_chain_receive'] = false;
            $order_info['if_evaluation'] = false;
            $order_info['if_receive'] = false;
            $order_info['if_complain'] = false;
            $order_info['if_buyer_cancel'] = false;
            $order_info['if_refund_cancel'] = false;
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
        $need_goods_fields = array('rec_id', 'goods_id', 'goods_name', 'goods_price', 'goods_num', 'goods_images', 'refund', 'goods_spec', 'goods_type', 'xianshi_num');
        if (is_array($order_info['extend_order_goods'])) {
            foreach ($order_info['extend_order_goods'] as $key => $val) {
                $order_info['extend_order_goods'][$key]['image_url'] = cthumb($val['goods_image'], 240, $val['store_id']);
                $order_info['extend_order_goods'][$key]['refund_amount'] = '';  //退款金额
                $order_info['extend_order_goods'][$key]['goods_spec'] = $val['goods_spec'] ? $val['goods_spec'] : '';
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
                //empty($value['extend_order_goods'][$k]['refund']) and $value['extend_order_goods'][$k]['refund']=0;
                $order_info['extend_order_goods'][$key]['is_zengpin'] = 0;
                $order_info['extend_order_goods'][$key]['is_miaosao'] = 0;
                $order_info['extend_order_goods'][$key]['is_pin'] = 0;
                if ($val['goods_type'] == 5) {
                    $order_info['extend_order_goods'][$key]['is_zengpin'] = 1;
                }
                if ($val['goods_type'] == 3) {
                    $order_info['extend_order_goods'][$key]['is_miaosao'] = 1;
                    $order_info['extend_order_goods'][$key]['xianshi_num'] = $val['xianshi_num'] > 0 ? $val['xianshi_num'] : $val['goods_num'];
                }
                if ($val['goods_type'] == 10) {
                    $order_info['extend_order_goods'][$key]['is_pin'] = 1;
                }
            }
        }

        unset($order_info['refund_list']);
        //过滤order_common表不需要的字段
        $need_orderCommon_fields = array('voucher_price', 'voucher_code', 'order_pointscount', 'express_info', 'deliver_explain', 'receive_info', 'promotion_total', 'discount', 'promotion');
        foreach ($order_info['extend_order_common'] as $key => $val) {
            if (!in_array($key, $need_orderCommon_fields)) {
                unset($order_info['extend_order_common'][$key]);
            }
        }
        $order_info['extend_order_goods']=array_values($order_info['extend_order_goods']);

        $order_info['tuan_user_list'] = array();
        $order_info['tuan_info'] = array();

        if ($order_info['tuan_id'] > 0) {
            /** @var p_pintuan_memberModel $p_pintuan_member_model */
            $p_pintuan_member_model = Model('p_pintuan_member');
            /** @var p_pintuan_tuanModel $p_pintuan_tuan_model */
            $p_pintuan_tuan_model = Model('p_pintuan_tuan');
            $tuan_user_list = $p_pintuan_member_model->getMemberList(array('tuan_id' => $order_info['tuan_id']));
            $order_info['tuan_user_list'] = $tuan_user_list;
            $order_info['tuan_info'] = $p_pintuan_tuan_model->getTuanInfo(array('tuan_id' => $order_info['tuan_id'], 'expires_time' => array('gt', TIMESTAMP)));
            $order_info['tuan_info']['current_time'] = TIMESTAMP;
        }
        $order_info['member_info'] = array('member_name' => $this->member_info['member_name']);

        $order_info['shequ_tuan_info'] = '';
        $order_info['shequ_deliver_type'] = '';
        if ($order_info['shequ_tuan_id'] > 0) {
            $tuan_info = array();
            /** @var shequ_tuan_configModel $shequ_tuan_config_model */
            $shequ_tuan_config_model = Model('shequ_tuan_config');
            $tuan_config_info = $shequ_tuan_config_model->getTuanConfigInfo(array('config_tuan_id' => $order_info['shequ_tuan_id']));
            //$tuan_info['tz_avatar'] = UPLOAD_SITE_URL . '/'. $tuan_info['tz_avatar'];
            $tuan_info['config_send_time'] = date('Y-m-d', $tuan_config_info['send_product_date']);
            $tuan_info['start_time'] = date('Y-m-d', $tuan_config_info['config_start_time']);
            $tuan_info['end_time'] = date('Y-m-d', $tuan_config_info['config_end_time']);
            $tuan_info['deliver_type'] = $order_info['chain_code'] == 0 ? '物流发货' : '农猫速达配送';
            $order_info['shequ_deliver_type'] = $order_info['chain_code'] == 0 ? '物流发货' : '农猫速达配送';
            /** @var shequ_tuanzhangModel $shequ_tuanzhang_model */
            $shequ_tuanzhang_model = Model('shequ_tuanzhang');
            $shequ_tuanzhang_info = $shequ_tuanzhang_model->getOne(array('id' => $order_info['shequ_tz_id']));
            $tuan_info['tz_phone'] = $shequ_tuanzhang_info['phone'];
            /** @var memberModel $model_member */
            $model_member = Model('member');
            $member_tuanzhang_info = $model_member->getMemberInfoByID($shequ_tuanzhang_info['member_id']);
            $tuan_info['tz_name'] = $member_tuanzhang_info['wx_nick_name'];
            $tuan_info['tz_avatar'] = $member_tuanzhang_info['wx_user_avatar'];
            if ($shequ_tuanzhang_info) {
                $order_info['shequ_tuan_info'] = $tuan_info;
            } else {
                $order_info['shequ_tuan_info'] = '';
            }

        }

        output_data(array('order_info' => $order_info));
    }

    /**
     * 用户确认提货
     */
    public function pickup_parcelOP() {

        $order_id = intval($_POST['order_id']);
        //$pickup_code = intval($_POST['pickup_code']);
        if ($order_id <= 0 /*|| $pickup_code <= 0*/) {
            output_error('参数错误');
        }
        /** @var orderModel $model_order */
        $model_order = Model('order');
        $order_info = $model_order->getOrderInfo(array('order_id' => $order_id, /*'chain_code' => $pickup_code*/));
        if (empty($order_info)) {
            output_error('订单信息错误');
        }
        /** @var orderLogic $logic_order */
        $logic_order = Logic('order');
        $if_allow = $model_order->getOrderOperateState('chain_receive',$order_info);
        if (!$if_allow) {
            output_error('无权操作');
        }
        $result = $logic_order->changeOrderStateReceive($order_info,'buyer','自提自提','买家确认自提，更改订单为完成状态');

        if ($result['state']) {
            output_data('提货成功');
        } else {
            output_error($result['msg']);
        }
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
        $data['deliver_info']['time'] = !empty($deliver_info) ? $deliver_info['0'] : date('Y-m-d H:i:s', $order_info['extend_order_common']['shipping_time']);
        output_data($deliver_info);
    }

    /**
     * 从第三方取快递信息
     *
     */
    public function _get_express($e_code, $shipping_code)
    {
        $content = Model('express')->get_express($e_code, $shipping_code);
        if (empty($content)) {
            return array();
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
