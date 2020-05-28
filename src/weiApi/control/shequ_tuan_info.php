<?php
/**
 * 我的团长页面
 *
 * @copyright  Copyright (c) 2007-2015 ShopNC Inc. (http://www.shopnc.net)
 * @license    http://www.shopnc.net
 * @link       http://www.shopnc.net
 * @since      File available since Release v1.1
 */
defined('ByShopWWI') or exit('Access Invalid!');

class shequ_tuan_infoControl extends mobileMemberControl
{

    public function __construct()
    {
        parent::__construct();
        /** @var shequ_tuanzhangModel $tuanzhang_model */
        $condition['state'] = '1';
        $condition['member_id'] = $this->member_info['member_id'];
        $tuanzhang_model = Model('shequ_tuanzhang');
        $this->tuanzhang_info = $tuanzhang_model->getOne($condition);
        if (empty($this->tuanzhang_info)) {
            output_error('不是团长');
        }
        if ($this->tuanzhang_info['area'] != '') {
            $this->tuanzhang_info['wuliu_type'] = '自提';
        }
    }

    /**
     * 佣金信息
     * //待出账佣金
     * //待领取佣金 余额
     * //累计佣金
     */
    public function bill_infoOp()
    {
        $show_data = array(
            'commission' => array(
                'all_commission' => 0,//累计佣金
                'wait_bill_commission' => 0, //待出账佣金
                'wait_commission' => 0,//待领取佣金
            ),
            'achievement' => array(
                'now_tuan_money' => 0,
                'order_num' => 0,
                'now_tuan_earn' => 0,
            ),
        );

        //待出账佣金 订单表还未生成结算单的订单
        /** @var orderModel $order_model */
        $order_model = Model('order');
        $order_condition = array();
        $order_condition['shequ_tuan_id'] = array('gt', '0');
        $order_condition['shequ_tz_id'] = $this->member_info['tuanzhang_id'];
        $order_condition['order_state'] = array('egt', ORDER_STATE_PAY);

//        $order_condition['shequ_tz_bill_id'] = '0';
//        $order_condition['shequ_bill_time'] = '0';
//        $order_condition['refund_state'] = '0';

        $un_settlement_order = $order_model->getOrderList($order_condition, 999999, "*", "order_id desc", 99999, array('order_goods'));
        //   $wait_bill_commission = array_sum(array_column($un_settlement_order,'shequ_return_amount'));

        // 减去退款的
        /** @var refund_returnModel $refund_return_model */
        $refund_return_model = Model('refund_return');
        $un_settlement_order = $refund_return_model->getGoodsRefundList($un_settlement_order,1);
        foreach ($un_settlement_order as $re_k => $re_v) {
            $show_data['commission']['all_commission'] += $re_v['shequ_return_amount'];
            if ($re_v['shequ_tz_bill_id'] == '0') {
                $show_data['commission']['wait_bill_commission'] += $re_v['shequ_return_amount'];
                foreach ($re_v['extend_order_goods'] as $re_goods) {
                    if (is_array($re_goods['extend_refund']) && $re_goods['extend_refund']['refund_state'] == '3' && $re_goods['extend_refund']['seller_state'] == '2') {
                        $show_data['commission']['all_commission'] -= $re_goods['extend_refund']['shequ_return_amount'];
                        $show_data['commission']['wait_bill_commission'] -= $re_goods['extend_refund']['shequ_return_amount'];
                    }
                }
            } else {
                foreach ($re_v['extend_order_goods'] as $re_goods) {
                    if (is_array($re_goods['extend_refund']) && $re_goods['extend_refund']['refund_state'] == '3' && $re_goods['extend_refund']['seller_state'] == '2') {
                        $show_data['commission']['all_commission'] -= $re_goods['extend_refund']['shequ_return_amount'];
                    }
                }
            }

        }


//        $show_data['commission']['wait_bill_commission'] = $wait_bill_commission;
//        $show_data['commission']['all_commission']  +=  $wait_bill_commission;

//
//       //已经生成结算单的金额
//        /** @var shequ_billModel $shequ_bill_model */
//        $shequ_bill_model  = Model('shequ_bill');
//        $bill_condition['ob_store_id'] = $this->member_info['tuanzhang_id'];
//        $bill_condition['ob_state'] = '4';
//        $field = "SUM(ob_result_totals) as bill_amount";
//        $all_bill_amount = $shequ_bill_model->where($bill_condition)->field($field)->find();
//        $all_bill_amount_num = $all_bill_amount['bill_amount']>0?$all_bill_amount['bill_amount']:"0";
//        $show_data['commission']['all_commission']+=$all_bill_amount_num;


        //待领取佣金  //直接查余额
        $show_data['commission']['wait_commission'] = $this->member_info['avaliable_commission'];


        //当前团
        /** @var shequ_tuan_configModel $shequ_tuan_config_model */
        $shequ_tuan_config_model = Model('shequ_tuan_config');
        $time = time();
        $tuan_condition['config_start_time'] = array('lt', $time);
        $tuan_condition['config_end_time'] = array('gt', $time);
        $tuan_condition['config_state'] = array('eq', '1');
        $tuan_info = $shequ_tuan_config_model->getTuanConfigInfo($tuan_condition);
        if (empty($tuan_info)) {
            output_data($show_data);
        }
        //这里的业绩   已支付-待发货的之后都算进去 .
        $order_condition = array();
        $order_condition['shequ_tuan_id'] = $tuan_info['config_tuan_id'];
        $order_condition['order_state'] = array("egt", ORDER_STATE_PAY);
        $order_condition['refund_state'] = '0';
        $order_condition['shequ_tz_id'] = $this->tuanzhang_info['id'];
        $order_list = $order_model->getOrderList($order_condition, 0, "*", "order_id desc", 999999, array('order_goods'));
        $show_data['achievement']['order_num'] = count($order_list);
        $show_data['achievement']['now_tuan_money'] = array_sum(array_column($order_list, 'order_amount'));
        $show_data['achievement']['now_tuan_earn'] = array_sum(array_column($order_list, 'shequ_return_amount'));
        //减去退款的 ;
        $order_list = $refund_return_model->getGoodsRefundList($order_list);
        foreach ($order_list as $re_k => $re_v) {
            foreach ($re_v['extend_order_goods'] as $r_goods) {
                if (is_array($r_goods['extend_refund'])&&$r_goods['extend_refund']['refund_state'] == '3' && $r_goods['extend_refund']['seller_state'] == '2') {
                    $show_data['achievement']['now_tuan_money'] -= $r_goods['extend_refund']['refund_amount'];
                    $show_data['achievement']['now_tuan_earn'] -= $r_goods['extend_refund']['shequ_return_amount'];
                }
            }
        }
        output_data($show_data);
    }

    /**
     * 团长个人中心
     */
    public function indexOp()
    {
        $show_data = array(
            'commission' => array(
                'all_commission' => 0,
                'have_commission' => 0,
                'wait_commission' => 0,
            ),
            'achievement' => array(
                'now_tuan_money' => 0,
                'order_num' => 0,
                'now_tuan_earn' => 0,
            ),
        );
        output_data($show_data);
    }

}

