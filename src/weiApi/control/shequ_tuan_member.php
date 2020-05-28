<?php
/**
 * 团员管理
 *
 * @copyright  Copyright (c) 2007-2015 ShopNC Inc. (http://www.shopnc.net)
 * @license    http://www.shopnc.net
 * @link       http://www.shopnc.net
 * @since      File available since Release v1.1
 */
defined('ByShopWWI') or exit('Access Invalid!');

class shequ_tuan_memberControl extends mobileMemberTuanControl {

    public function __construct(){
        parent::__construct();

        $member_info = $this->member_info;
        if (!$member_info['tuanzhang_id']) {
            output_error('异常');
        }
    }

    public function indexOp() {
        /** @var memberModel $member_model */
        $member_model = Model('member');
        $count_fans = array(
            'default_shequ_tuanzhang_id' => $this->member_info['tuanzhang_id']
        );
        $fans = $member_model->getMemberCount($count_fans);
        $count_buy = array(
            'shequ_tuan_id' => array('gt', 0),
            'shequ_tz_id' => $this->member_info['tuanzhang_id'],
            'order_state' => array('egt', ORDER_STATE_PAY),
            'add_time' => array('gt', TIMESTAMP-2592000),
        );
        /** @var orderModel $order_model */
        $order_model = Model('order');
        $month_buy_num = $order_model->getOrderCount($count_buy);
        $result = array(
            'fans' => $fans,
            'month_buy_num' => $month_buy_num,
        );
        output_data($result);
    }

    public function get_listOp() {
        $tuanzhang_id = $this->member_info['tuanzhang_id'];
        $condition = array(
            'default_shequ_tuanzhang_id' => $tuanzhang_id,
        );
        $member_name = $_POST['member_name'];
        if ($member_name) {
            if (is_numeric($member_name)) {
                $condition['member_id'] = $member_name;
            } else {
                $condition['member_name'] = $member_name;
            }
        }
        /** @var memberModel $member_model */
        $member_model = Model('member');
        $member_list = $member_model->getMemberList($condition,'member_id');
        if (empty($member_list)) {
            output_data(array('list' => array()), mobile_page(0));
        }
        $member_ids = array_column($member_list, 'member_id');
        $member_ids_str = '(';
        foreach ($member_ids as $ggg) {
            $member_ids_str .= "'$ggg'".',';
        }
        $member_ids_str = trim($member_ids_str, ',');
        $member_ids_str .= ')';
        /** @var orderModel $order_model */
        $order_model = Model('order');

        $cur_page = $_POST['curpage'] > 0 ?  $_POST['curpage'] : 1;
        $start_num = ($cur_page-1) * $this->page;

        $result = $order_model->query(
            "SELECT
	t1.member_id,t1.wx_nick_name,t1.wx_user_avatar,t2.count_order_amount
FROM
	shopwwi_member AS t1
 LEFT JOIN (
	SELECT
		buyer_id,
		SUM(order_amount) count_order_amount
	FROM
		shopwwi_orders
   WHERE buyer_id in {$member_ids_str} 
    AND shequ_tuan_id > 0 
    AND shequ_tz_id = {$tuanzhang_id}
    AND order_state > 0 
	GROUP BY
		buyer_id
) AS t2 ON t1.member_id = t2.buyer_id
WHERE
	member_id  in {$member_ids_str}
ORDER BY
	t2.count_order_amount desc
LIMIT {$start_num},{$this->page}
;");
        $page_count = ceil(count($member_ids)/$this->page);

        $new_member_list = array();
        foreach ($result as $k=>$value) {
            $k ++;
            $new_member_list[] = array(
                'number' => $k + $start_num,
                'wx_nick_name' => $value['wx_nick_name'],
                'wx_user_avatar' => $value['wx_user_avatar'],
                'member_id' => $value['member_id'],
                'order_amount' => $value['count_order_amount'],
            );
        }
        if (intval($_POST['curpage']) > $page_count) $new_member_list = array();
        output_data(array('list' => $new_member_list), mobile_page($page_count));
    }


    public function show_member_commisOp()
    {
        $member_id = $_POST['member_id'];
        if (empty($member_id)) {
            output_error(array('参数错误'));
        }
        /** @var memberModel $member_model */
        $member_model = Model('member');
        $member_info = $member_model->getMemberInfoByID($member_id);
        $tuanzhang_id = $this->member_info['tuanzhang_id'];
        /** @var orderModel $orderModel */
        $orderModel = Model('order');
        $condition = array(
            'buyer_id' => $member_id,
            'shequ_tz_id' => $tuanzhang_id,
            'order_state' => array('gt', ORDER_STATE_CANCEL),
            //'refund_amount' => 0
        );

        $order_list = $orderModel->getOrderList($condition, '', 'order_id,order_amount');
        $order_amount = 0;
        foreach ($order_list as $order) {
            $order_amount += $order['order_amount'];
        }
        $order_ids = array_column($order_list, 'order_id');
        $order_goods_list = $orderModel->getOrderGoodsList(array('order_id' => array('in', $order_ids)), '*','', $this->page);
        $page_count = $orderModel->gettotalpage();
        $return = array();
        foreach ($order_goods_list as $order_goods) {
            $order_goods['earn_rate'] = $order_goods['shequ_commis_amount'] * 100/$order_goods['goods_pay_price'];
            $order_goods['goods_image_url'] = cthumb($order_goods);
            $return[] = $order_goods;
        }
        $result = array(
            'order_amount' => $order_amount,
            'member_info' => array(
                'wx_nick_name' => $member_info['wx_nick_name'],
                'wx_user_avatar' => $member_info['wx_user_avatar'],
            ),
            'goods_list' => $return
        );
        if (intval($_POST['curpage']) > $page_count) $result['goods_list'] = array();
        output_data($result, mobile_page($page_count));
    }



}

