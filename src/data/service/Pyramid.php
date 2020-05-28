<?php
/**
 * Class PyramidService
 */
class PyramidService
{

    /**
     * 支付成功添加分销佣金记录
     * @param $pyramid_goods_list
     * @param $order_data
     * @return bool
     */
    public function addPyramidOrderLog($pyramid_goods_list, $order_data)
    {
        if (empty($order_data) || empty($pyramid_goods_list)) {
            return true;
        }
        $pyramid_goods  = array();
        foreach ($pyramid_goods_list as $goods_list) {
            $pyramid_goods = $pyramid_goods + $goods_list;
        }

        /** @var retail_goodsModel $retail_goodsModel */
        $retail_goodsModel = Model('retail_goods');
        $order_list = $order_data['order_list'];
        foreach ($order_list as $order) {
            $invite_member_id = $order['buyer_id'];
            $invite_member_name = $order['buyer_name'];
            $goods_list = $order['goods'];
            $pyramid_order_goods_insert = array();
            $pyramid_order_insert = array();
            foreach ($goods_list as $goods_info) {
                if (!array_key_exists($goods_info['goods_id'], $pyramid_goods)) {
                    continue;
                }
                $retail_goods = $retail_goodsModel->getRetailGoodsInfo(array('retail_goods_id' => $goods_info['goods_id']));
                if (empty($retail_goods)) {
                    continue;
                }
                $lg_member_id = $pyramid_goods[$goods_info['goods_id']];
                if (empty($lg_member_id)) {
                    continue;
                }

                // 获取 $invite_member_id 用户的 invite_one invite_two invite_three
                $subordinate_result = $this->beSubordinate($lg_member_id, $invite_member_id, 'order');
                if (!$subordinate_result['state'] || empty($subordinate_result['data'])) {
                    continue;
                }
                $subordinate_result = $subordinate_result['data'];
                //处理一级的
                if ($retail_goods['retail_one_return'] > 0 && $subordinate_result['invite_one'] > 0) {
                    $pyramid_order_goods_insert[$subordinate_result['invite_one']] = array(
                        'order_id' => $order['order_id'],
                        'lg_member_id' => $subordinate_result['invite_one'],
                        'order_goods_id' => $goods_info['goods_id'],
                        'order_goods_num' => $goods_info['goods_num'],
                        'add_time' => TIMESTAMP,
                        'one_return_money' => $retail_goods['retail_one_return']
                    );
                    if (!isset($pyramid_order_insert[$subordinate_result['invite_one']])) {
                        $pyramid_order_insert[$subordinate_result['invite_one']] = array(
                            'order_id' => $order['order_id'],
                            'lg_member_id' => $subordinate_result['invite_one'],
                            'invite_member_id' => $invite_member_id,
                            'invite_member_name' => $invite_member_name,
                            'add_time' => TIMESTAMP,
                            'return_money' => $retail_goods['retail_one_return'] * $goods_info['goods_num'],//todo
                            'real_add_time' => 0,
                            'real_return_money' => 0,
                            'lg_status' => 10,
                            'invite_level' => 1,
                            'order_sn' => $order['order_sn'],
                        );
                    } else {
                        $pyramid_order_insert[$subordinate_result['invite_one']]['return_money'] += $retail_goods['retail_one_return'] * $goods_info['goods_num'];
                    }
                }

                //处理二级
                if ($retail_goods['retail_two_return'] > 0 && $subordinate_result['invite_two'] > 0) {
                    $pyramid_order_goods_insert[$subordinate_result['invite_two']] = array(
                        'order_id' => $order['order_id'],
                        'lg_member_id' => $subordinate_result['invite_two'],
                        'order_goods_id' => $goods_info['goods_id'],
                        'order_goods_num' => $goods_info['goods_num'],
                        'add_time' => TIMESTAMP,
                        'one_return_money' => $retail_goods['retail_two_return']
                    );
                    if (!isset($pyramid_order_insert[$subordinate_result['invite_two']])) {
                        $pyramid_order_insert[$subordinate_result['invite_two']] = array(
                            'order_id' => $order['order_id'],
                            'lg_member_id' => $subordinate_result['invite_two'],
                            'invite_member_id' => $invite_member_id,
                            'invite_member_name' => $invite_member_name,
                            'add_time' => TIMESTAMP,
                            'return_money' => $retail_goods['retail_two_return'] * $goods_info['goods_num'],//todo
                            'real_add_time' => 0,
                            'real_return_money' => 0,
                            'lg_status' => 10,
                            'invite_level' => 2,
                            'order_sn' => $order['order_sn'],
                        );
                    } else {
                        $pyramid_order_insert[$subordinate_result['invite_two']]['return_money'] += $retail_goods['retail_two_return'] * $goods_info['goods_num'];
                    }
                }

                //处理三级
                if ($retail_goods['retail_three_return'] > 0 && $subordinate_result['invite_three'] > 0) {
                    $pyramid_order_goods_insert[$subordinate_result['invite_three']] = array(
                        'order_id' => $order['order_id'],
                        'lg_member_id' => $subordinate_result['invite_three'],
                        'order_goods_id' => $goods_info['goods_id'],
                        'order_goods_num' => $goods_info['goods_num'],
                        'add_time' => TIMESTAMP,
                        'one_return_money' => $retail_goods['retail_three_return']
                    );
                    if (!isset($pyramid_order_insert[$subordinate_result['invite_three']])) {
                        $pyramid_order_insert[$subordinate_result['invite_three']] = array(
                            'order_id' => $order['order_id'],
                            'lg_member_id' => $subordinate_result['invite_three'],
                            'invite_member_id' => $invite_member_id,
                            'invite_member_name' => $invite_member_name,
                            'add_time' => TIMESTAMP,
                            'return_money' => $retail_goods['retail_three_return'] * $goods_info['goods_num'],//todo
                            'real_add_time' => 0,
                            'real_return_money' => 0,
                            'lg_status' => 10,
                            'invite_level' => 3,
                            'order_sn' => $order['order_sn'],
                        );
                    } else {
                        $pyramid_order_insert[$subordinate_result['invite_three']]['return_money'] += $retail_goods['retail_three_return'] * $goods_info['goods_num'];
                    }
                }

            }
            if (empty($pyramid_order_goods_insert)) {
                continue;
            }
            $pyramid_order_goods_insert = array_values($pyramid_order_goods_insert);
            $pyramid_order_insert = array_values($pyramid_order_insert);
            /** @var pyramid_order_goods_logModel $pyramid_order_goods_logModel */
            $pyramid_order_goods_logModel = Model('pyramid_order_goods_log');
            /** @var pyramid_order_logModel $pyramid_order_logModel */
            $pyramid_order_logModel = Model('pyramid_order_log');
            $pyramid_order_goods_logModel->addPyramidOrderGoodsLogList($pyramid_order_goods_insert);
            $pyramid_order_logModel->addPyramidOrderLogList($pyramid_order_insert);
        }
        return true;
    }

    public function changePyramidOrderLogState($order_id)
    {
        /** @var pyramid_order_logModel $pyramid_order_logModel */
        $pyramid_order_logModel = Model('pyramid_order_log');
        $pyramid_order_logModel->editPyramidOrderLog(array('lg_status' => 20), array('order_id' => $order_id, 'lg_status' => 10));
        return true;
    }

    /**
     * 确认订单
     */
    public function getRealPyramidMoney($order_info)
    {
        /** @var pyramid_order_logModel $pyramid_order_logModel */
        $pyramid_order_logModel = Model('pyramid_order_log');
        /** @var pyramid_brokerage_logModel $pyramid_brokerage_logModel */
        $pyramid_brokerage_logModel = Model('pyramid_brokerage_log');
        if ($order_info['lock_state'] != 0) {
            return true;
        }
        //todo 订单要已完成
        $maxDay = 15;
        $delay_time = $order_info['finnshed_time'] + 60 * 60 * 24 * $maxDay;
        if ($delay_time > time ()) {
            return true;
        }
        $order_id = $order_info['order_id'];
        $refund_amount = $order_info['refund_amount'];
        $order_amount = $order_info['order_amount'];
        $update_data = array(
            'order_amount' => $order_amount,
            'refund_amount' => $refund_amount,
            'real_add_time' => TIMESTAMP,
            'lg_status' => 40
        );
        $need_update_list = $pyramid_order_logModel->getPyramidOrderLogList(array('order_id' => $order_id, 'real_add_time' => 0));
        if (empty($need_update_list)) {
            return true;
        }
        foreach ($need_update_list as $pyramid_order_log) {
            $return_money = $pyramid_order_log['return_money'];
            $real_order_amount = ($order_amount-$refund_amount) > 0 ? ($order_amount-$refund_amount) : 0;
            $real_return_money = ncPriceFormat($return_money * $real_order_amount/$order_amount, 2);
            $update_data['real_return_money'] = $real_return_money;
            $pyramid_order_logModel->editPyramidOrderLog($update_data, array('id' => $pyramid_order_log['id']));
            if ($real_return_money > 0) {
                $insert_brokerage_log = array(
                    'lg_member_id' => $pyramid_order_log['lg_member_id'],
                    'lg_type' => "order_pay",
                    'lg_av_amount' => $real_return_money,
                    'lg_freeze_amount' => 0,
                    'lg_add_time' => TIMESTAMP,
                    'lg_desc' => "订单完成",
                    'order_id' => $order_id
                );
                $pyramid_brokerage_logModel->addPyramidBrokerageLog($insert_brokerage_log);
            }
        }
        return true;

    }

    // shopwwi_pyramid_brokerage_log
    //todo 后台 发起退款 1个

    /**
     * 发起提现
     * @param $member_id
     * @param $out_crash
     * @return array
     */
    public function out_cash_start($member_id, $out_crash)
    {
        /** @var pyramid_out_crashModel $pyramid_out_crashModel */
        $pyramid_out_crashModel = Model('pyramid_out_crash');
        /** @var pyramid_brokerage_logModel $pyramid_brokerage_logModel */
        $pyramid_brokerage_logModel = Model('pyramid_brokerage_log');
        try {
            $pyramid_out_crashModel->beginTransaction();
            $insert_data = array(
                'out_crash_number' => date('Ymdh', time()).substr(time(), 0, 6),
                'add_time' => TIMESTAMP,
                'cash_check_state' => 1,
                'pay_state' => 1,
                'out_crash_money' => $out_crash,
                'own_member_id' => $member_id
            );
            $out_crash_id = $pyramid_out_crashModel->addPyramidOutCrash($insert_data);
            if (!$out_crash_id) {
                throw new Exception('添加失败1');
            }

            $insert_brokerage_log = array(
                'lg_member_id' => $member_id,
                'lg_type' => "cash_apply",
                'lg_av_amount' => -$out_crash,
                'lg_freeze_amount' => $out_crash,
                'lg_add_time' => TIMESTAMP,
                'lg_desc' => "发起提现",
                'out_cash_id' => $out_crash_id
            );
            $result = $pyramid_brokerage_logModel->addPyramidBrokerageLog($insert_brokerage_log);
            if (!$result) {
                throw new Exception('添加失败2');
            }
            $pyramid_out_crashModel->commit();
            return callback(true,'操作成功');
        } catch (Exception $e) {
            $pyramid_out_crashModel->rollback();
            return callback(false,$e->getMessage());
        }
    }

    /**
     * 提现成功
     * 只处理提现， 发红包另外发起接口
     *
     * @param $out_crash_id
     * @return array
     */
    public function out_cash_support($out_crash_id)
    {
        /** @var pyramid_out_crashModel $pyramid_out_crashModel */
        $pyramid_out_crashModel = Model('pyramid_out_crash');
        $out_crash_data = $pyramid_out_crashModel->getPyramidOutCrashInfo(array('out_crash_id' => $out_crash_id));
        if (empty($out_crash_data)) {
            return callback(false,'不正确的操作');
        }

        if ($out_crash_data['cash_check_state'] != 1 || $out_crash_data['pay_state'] != 1) {
            return callback(false,'非法操作');
        }

        /** @var pyramid_brokerage_logModel $pyramid_brokerage_logModel */
        $pyramid_brokerage_logModel = Model('pyramid_brokerage_log');
        try {
            $pyramid_out_crashModel->beginTransaction();
            $crash_update_data = array(
                'cash_check_state' => 3
            );
            $update_result = $pyramid_out_crashModel->editPyramidOutCrash($crash_update_data, array('out_crash_id' => $out_crash_id));
            if (!$update_result) {
                throw new Exception('失败1');
            }

            $insert_brokerage_log = array(
                'lg_member_id' => $out_crash_data['own_member_id'],
                'lg_type' => "cash_pay",
                'lg_av_amount' => 0,
                'lg_freeze_amount' => -$out_crash_data['out_crash_money'],
                'lg_add_time' => TIMESTAMP,
                'lg_desc' => "提现成功",
                'out_cash_id' => $out_crash_id
            );
            $result = $pyramid_brokerage_logModel->addPyramidBrokerageLog($insert_brokerage_log);
            if (!$result) {
                throw new Exception('失败2');
            }
            $pyramid_out_crashModel->commit();
            return callback(true,'操作成功');
        } catch (Exception $e) {
            $pyramid_out_crashModel->rollback();
            return callback(false,$e->getMessage());
        }
    }


    /**
     * cash_del
     *
     * @param $out_crash_id
     * @return array
     */
    public function out_cash_refuse($out_crash_id)
    {
        /** @var pyramid_out_crashModel $pyramid_out_crashModel */
        $pyramid_out_crashModel = Model('pyramid_out_crash');
        $out_crash_data = $pyramid_out_crashModel->getPyramidOutCrashInfo(array('out_crash_id' => $out_crash_id));
        if (empty($out_crash_data)) {
            return callback(false,'不正确的操作');
        }

        if ($out_crash_data['cash_check_state'] != 1 || $out_crash_data['pay_state'] != 1) {
            return callback(false,'非法操作');
        }

        /** @var pyramid_brokerage_logModel $pyramid_brokerage_logModel */
        $pyramid_brokerage_logModel = Model('pyramid_brokerage_log');
        try {
            $pyramid_out_crashModel->beginTransaction();
            $crash_update_data = array(
                'cash_check_state' => 2
            );
            $update_result = $pyramid_out_crashModel->editPyramidOutCrash($crash_update_data, array('out_crash_id' => $out_crash_id));
            if (!$update_result) {
                throw new Exception('失败1');
            }

            $insert_brokerage_log = array(
                'lg_member_id' => $out_crash_data['own_member_id'],
                'lg_type' => "cash_del",
                'lg_av_amount' => $out_crash_data['out_crash_money'],
                'lg_freeze_amount' => -$out_crash_data['out_crash_money'],
                'lg_add_time' => TIMESTAMP,
                'lg_desc' => "取消提现",
                'out_cash_id' => $out_crash_id
            );
            $result = $pyramid_brokerage_logModel->addPyramidBrokerageLog($insert_brokerage_log);
            if (!$result) {
                throw new Exception('失败2');
            }
            $pyramid_out_crashModel->commit();
            return callback(true,'操作成功');
        } catch (Exception $e) {
            $pyramid_out_crashModel->rollback();
            return callback(false,$e->getMessage());
        }
    }

    //计算用户金额
    public function getMemberPyramidAmount($member_id) {

        /** @var pyramid_order_logModel $pyramid_order_logModel */
        $pyramid_order_logModel = Model('pyramid_order_log');
        /** @var pyramid_brokerage_logModel $pyramid_brokerage_logModel */
        $pyramid_brokerage_logModel = Model('pyramid_brokerage_log');
        /** @var orderModel $orderModel */
        $orderModel = Model('order');
        $freeze_amount_condition = array(
            'lg_status' => 20,
            'lg_member_id' => $member_id
        );
        $pyramid_order_list = $pyramid_order_logModel->getPyramidOrderLogList($freeze_amount_condition);
        $all_return_money = 0; //累计佣金
        $freeze_amount = 0;//冻结佣金
        $order_list = array();
        if (!empty($pyramid_order_list)) {
            $order_list = $orderModel->getOrderList(array('order_id' => array('in', array_column($pyramid_order_list, 'order_id'))));
            $order_list = array_under_reset($order_list, 'order_id');
        }
        foreach ($pyramid_order_list as $pyramid_order_log) {
            if ($pyramid_order_log['real_add_time'] == 0) {
                //计算退款金额
                $order_info = $order_list[$pyramid_order_log['order_id']];
                $rate = ($order_info['order_amount'] - $order_info['refund_amount'])/$order_info['order_amount'];
                $freeze_amount += $pyramid_order_log['return_money'] * $rate;
                $all_return_money += $pyramid_order_log['return_money'] * $rate;
            } else {
                $all_return_money += $pyramid_order_log['real_return_money'];
            }
        }
        $available_amount = 0;
        $available_amount_condition = array(
            'lg_member_id' => $member_id
        );
        $pyramid_brokerage_list = $pyramid_brokerage_logModel->getPyramidBrokerageLogList($available_amount_condition);
        foreach ($pyramid_brokerage_list as $pyramid_brokerage) {
            $available_amount += $pyramid_brokerage['lg_av_amount'];
        }
        return array(
            'all_return_money' => ncPriceFormat($all_return_money, 2),
            'freeze_amount'    => ncPriceFormat($freeze_amount, 2),
            'available_amount' => ncPriceFormat($available_amount, 2)
        );
        //用户可提现金额
        //累计佣金
        //我的佣金 (用户可提现金额 + 冻结佣金)

    }

    //成为下线

    /**
     * @param $invite_id
     * @param $member_id
     * @param string $type
     * @return array
     */
    public function beSubordinate($invite_id, $member_id, $type='register') {
        //分销下线记录日志
        //log::selflog();
        /** @var memberModel $member_model */
        $member_model = Model('member');
        $invite_member_info = $member_model->getMemberInfo(array('member_id' => $invite_id));
        $member_info = $member_model->getMemberInfo(array('member_id' => $member_id));
        if (empty($invite_member_info) || empty($member_info)) {
            //log::selflog(); 参数错误
            return callback(true, '成功');
        }

        if ($type == 'register') {//注册
            if ($member_info['invite_one'] || $member_info['invite_two'] || $member_info['invite_three'] || $member_info['invite_shop_name']) {
                //log::selflog(); //非法操作了
                return callback(true, '成功');
            }
            $update = array(
                'invite_one' => $invite_id,
                'invite_two' => $invite_member_info['invite_one'],
                'invite_three' => $invite_member_info['invite_two'],
                'invite_shop_name' => '我的'
            );
            $result = $member_model->editMember(array('member_id' => $member_id), $update);
            if (!$result) {
                //log::selflog(); //增加下线失败
            }
            $this->giveSweet($member_id);
            return callback(true, '成功');
        }

        if ($type != 'order') {
            //log::selflog(); 参数错误
            return callback(true, '成功');
        }
        $invite_level = array(
            'invite_one' => $invite_id,
            'invite_two' => 0,
            'invite_three' => 0
        );

        //下单成为下线
        if (!$member_info['invite_one']) {
            if ($invite_id == $member_id) {
                return callback(true, '成功', $invite_level);
            }
            $update = array(
                'invite_one' => $invite_id,
                'invite_two' => $invite_member_info['invite_one'],
                'invite_three' => $invite_member_info['invite_two']
            );
            if (empty($member_info['invite_shop_name'])) {//非分销用户
                $update['invite_shop_name'] = '我的';
                $this->giveSweet($member_id);
            }

            $result = $member_model->editMember(array('member_id' => $member_id), $update);
            if (!$result) {
                //log::selflog(); //增加下线失败
            }
            $member_info['invite_one'] = $invite_id;
            $member_info['invite_two'] = $invite_member_info['invite_one'];
            $member_info['invite_three'] = $invite_member_info['invite_two'];
        }
        if ($member_info['invite_one'] == $invite_id) {
            $invite_level['invite_two'] = $member_info['invite_two'];
            $invite_level['invite_three'] = $member_info['invite_three'];
        } elseif ($member_info['invite_two'] == $invite_id) {
            $invite_level['invite_two'] = $member_info['invite_one'];
            $invite_level['invite_three'] = $member_info['invite_three'];
        } else {
            $invite_level['invite_two'] = $member_info['invite_one'];
            $invite_level['invite_three'] = $member_info['invite_two'];
        }
        return callback(true, '成功', $invite_level);
    }

    public function giveSweet($member_id)
    {

        $rpt_id = 169;
        /** @var redpacketModel $model_redPacket */
        $model_redPacket = Model('redpacket');
        //验证是否可以兑换红包
        $data = $model_redPacket->getCanChangeTemplateInfo($rpt_id, $member_id);
        if ($data['state'] == false){
            //log
            return true;
        }
        //添加红包信息
        $data = $model_redPacket->exchangeRedpacket($data['info'],$member_id);
        if ($data['state'] == false){
            //log
        }
        return true;
    }




}