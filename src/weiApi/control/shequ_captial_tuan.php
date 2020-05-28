
<?php
/**
 * 我的团
 *
 * @copyright  Copyright (c) 2007-2015 ShopNC Inc. (http://www.shopnc.net)
 * @license    http://www.shopnc.net
 * @link       http://www.shopnc.net
 * @since      File available since Release v1.1
 */
defined('ByShopWWI') or exit('Access Invalid!');

class shequ_captial_tuanControl extends mobileMemberControl {

    public function __construct(){
        parent::__construct();
    }

    /**
     * 团列表
     */
    public function tuan_listOp() {
        $tuanzhang_id = $this->member_info['tuanzhang_id'];
        $condition = array(
            'tz_id' => $tuanzhang_id,
        );
        /** @var shequ_tuanModel $shequ_tuan_model */
        $shequ_tuan_model = Model('shequ_tuan');
        $shequ_tuan_list = $shequ_tuan_model->getListTuan($condition, '*','id desc', '', $this->page);
        $page_count = $shequ_tuan_model->gettotalpage();
        /** @var shequ_tuan_config_goodsModel $shequ_tuan_config_goods_model */
        $shequ_tuan_config_goods_model = Model('shequ_tuan_config_goods');
        $goods_config_list = array();
        if (!empty($shequ_tuan_list)) {
            $tuan_config_ids = array_column($shequ_tuan_list, 'config_id');
            $goods_list = $shequ_tuan_config_goods_model->getTuanConfigGoodsList(array('tuan_config_id' => array('in', $tuan_config_ids)));
            foreach ($goods_list as $value) {
                if (count($goods_config_list[$value['tuan_config_id']]) >= 5) {
                    continue;
                }
                $goods_config_list[$value['tuan_config_id']][] = array(
                    'goods_id' => $value['goods_id'],
                    'goods_name' => $value['goods_name'],
                    'goods_image_url' => cthumb($value),
                );
            }
        }
        $result = array();
        foreach ($shequ_tuan_list as $tuan_info) {
            $tuan_info['config_start_time_text'] = date('Y-m-d', $tuan_info['start_time']);
            $tuan_info['config_end_time'] = $tuan_info['end_time'];
            $tuan_info['config_tuan_id'] = $tuan_info['config_id'];
            $result[] = array(
                'tuan_info' => $tuan_info,
                'goods_list' => isset($goods_config_list[$tuan_info['config_id']]) ? $goods_config_list[$tuan_info['config_id']] : '',
                'buy_num' => $tuan_info['order_num']
            );
        }
        if (intval($_POST['curpage']) > $page_count) $result = array();
        output_data(array('list' => $result), mobile_page($page_count));
    }

    //团员订单 列表
    public function tuan_infoOp() {
        $shequ_tuan_id = intval($_POST['shequ_tuan_id']);
        $order_type = intval($_POST['order_type']);
        if (empty($shequ_tuan_id)) {
            output_error(array('参数错误'));
        }
        if (!in_array($order_type, array(0,1,2))) {
            output_error(array('参数错误'));
        }

        $search_key = $_POST['search_key'];
        $tuanzhang_id = $this->member_info['tuanzhang_id'];
        /** @var orderModel $orderModel */
        $orderModel = Model('order');
        $condition = array(
            'delete_state' => 0,
            'shequ_tuan_id' => $shequ_tuan_id,
            'shequ_tz_id' => $tuanzhang_id,
        );
        if ($order_type == 0) {
            $condition['order_state'] = array('egt', ORDER_STATE_PAY);
        } elseif ($order_type == 1) {
            $condition['order_state'] = array('in', array(ORDER_STATE_PREPARE,ORDER_STATE_SEND));
        } else {
            $condition['order_state'] = ORDER_STATE_SUCCESS;
        }

        $link_phone = '';
        $link_name = '';
        if (preg_match('/^1[0-9]{10}$/', $search_key)) {
            $link_phone = $search_key;
        } elseif ($search_key) {
            $link_name = $search_key;
        }

        if ($link_phone) {
            $condition['buyer_phone'] = $link_phone;
        } elseif ($link_name) {
            $link_condition = $condition;
            $link_condition['reciver_name'] = $link_name;
            $order_list_new = Model()->table('orders,order_common')->join('inner')->on('orders.order_id=order_common.order_id')->where($link_condition)->field('orders.order_id')->select();
            if (empty($order_list_new)) {
                $condition['order_id'] = -1;
            } else {
                $condition['order_id'] = array('in', array_column($order_list_new, 'order_id'));
            }
        }
        $order_list = $orderModel->getOrderList($condition, $this->page, '*', 'order_id desc', '', array('order_goods', 'order_common'));
        $page_count = $orderModel->gettotalpage();
        /** @var refund_returnModel $model_refund_return */
        $model_refund_return = Model('refund_return');
        $order_list = $model_refund_return->getGoodsRefundList($order_list, 1);//订单商品的退款退货显示
        $order_return = array();
        /** @var memberModel $member_model */
        $member_model = Model('member');
        foreach ($order_list as $order) {
            $member_info = $member_model->getMemberInfoByID($order['buyer_id']);
            foreach ($order['extend_order_goods'] as $erg_k=>$erg_v) {
                $order['extend_order_goods'][$erg_k]['goods_image_url'] = cthumb($erg_v);
                $order['extend_order_goods'][$erg_k]['goods_pre_price'] = ncPriceFormat($erg_v['goods_num'] * $erg_v['goods_price']);
                $order['extend_order_goods'][$erg_k]['goods_voucher'] = $erg_v['goods_num'] * $erg_v['goods_price'] - $erg_v['goods_pay_price'];
                $order['extend_order_goods'][$erg_k]['show_refund'] = (isset($erg_v['refund']) && $erg_v['refund'] == 0 && $order['refund_amount'] > 0) ? 1 : 0;
            }
            $order_return[] = array(
                'member_info' => array(
                    'wx_nick_name' => $member_info['wx_nick_name'],
                    'wx_user_avatar' => $member_info['wx_user_avatar'],
                ),
                'delivery_type' => $order['chain_code'] == 0 ? '物流' : '自提',
                'reciver_info' => array(
                    'reciver_name' => $order['extend_order_common']['reciver_name'],
                    'mob_phone' => $order['extend_order_common']['reciver_info']['mob_phone'],
                    'address' => $order['extend_order_common']['reciver_info']['address'],
                ),
                'order_info' => array(
                    'order_id' => $order['order_id'],
                    'order_sn' => $order['order_sn'],
                    'state_desc' => $order['state_desc'],
                    'order_amount' => $order['order_amount'],
                    'voucher_amount' => $order['goods_amount'] + $order['shipping_fee'] - $order['order_amount'],
                    'shequ_return_amount' => ncPriceFormat($order['shequ_return_amount']),
                ),
                'order_goods' => $order['extend_order_goods']
            );
        }
        if (intval($_POST['curpage']) > $page_count) $order_return = array();
        output_data(array('list' => $order_return), mobile_page($page_count));
    }

    //团员订单 收益
    public function tuan_info_commisOp() {
        $shequ_tuan_id = intval($_POST['shequ_tuan_id']);
        if (empty($shequ_tuan_id)) {
            output_error(array('参数错误'));
        }
        $tuanzhang_id = $this->member_info['tuanzhang_id'];
        /** @var orderModel $orderModel */
        $orderModel = Model('order');
        $condition = array(
            'delete_state' => 0,
            'shequ_tuan_id' => $shequ_tuan_id,
            'shequ_tz_id' => $tuanzhang_id,
            'order_state' => array('egt', ORDER_STATE_PAY),
        );
        $order_list = $orderModel->getOrderList($condition, '', 'order_id,order_amount,refund_amount,shequ_return_amount');
        $result = array(
            'earn' => 0,
            'num' => 0,
            'pay' => 0
        );
        foreach ($order_list as $order) {
            $result['num'] ++;
            $result['pay'] += $order['order_amount'];
            if ($order['refund_amount'] == 0) {
                $result['earn'] += $order['shequ_return_amount'];
            }
        }
        output_data($result);
    }

    //本团收益明细
    //显示退款 -号
    public function tuan_info_goods_commisOp()
    {
        $shequ_tuan_id = intval($_POST['shequ_tuan_id']);
        if (empty($shequ_tuan_id)) {
            output_error(array('参数错误'));
        }
        $tuanzhang_id = $this->member_info['tuanzhang_id'];
        /** @var orderModel $orderModel */
        $orderModel = Model('order');
        $condition = array(
            'delete_state' => 0,
            'shequ_tuan_id' => $shequ_tuan_id,
            'shequ_tz_id' => $tuanzhang_id,
            'order_state' => array('egt', ORDER_STATE_PAY),
            'refund_amount' => 0
        );

        $order_list = $orderModel->getOrderList($condition, '', 'order_id,shequ_return_amount');
        $earn_money = 0;
        foreach ($order_list as $order) {
            $earn_money += $order['shequ_return_amount'];
        }

        $order_ids = array_column($order_list, 'order_id');
        /** @var shequ_addressModel $shequ_address_model */
        $shequ_address_model = Model('shequ_address');
        $address_info = $shequ_address_model->getOne(array('member_id' => $this->member_info['member_id']));
        $order_goods_list = $orderModel->getOrderGoodsList(array('order_id' => array('in', $order_ids)), '*','', $this->page);
        $page_count = $orderModel->gettotalpage();
        $return = array();
        foreach ($order_goods_list as $order_goods) {
            $order_goods['earn_rate'] = $order_goods['shequ_commis_amount'] * 100/$order_goods['goods_pay_price'];
            $order_goods['goods_image_url'] = cthumb($order_goods);
            $return[] = $order_goods;
        }
        $result = array(
            'earn_money' => $earn_money,
            'member_info' => array(
                'wx_nick_name' => $this->member_info['wx_nick_name'],
                'wx_user_avatar' => $this->member_info['wx_user_avatar'],
            ),
            'address_info' => array(
                'city_name' => $address_info['city_name'],
                'area' => $address_info['area'],
                'address' => $address_info['address'],
                'building' => $address_info['building'],
            ),
            'goods_list' => $return
        );
        if (intval($_POST['curpage']) > $page_count) $result['goods_list'] = array();
        output_data($result, mobile_page($page_count));
    }

}

