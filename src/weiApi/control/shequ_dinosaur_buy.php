<?php
/**
 * 接龙详情页
 *
 * @copyright  Copyright (c) 2007-2015 ShopNC Inc. (http://www.shopnc.net)
 * @license    http://www.shopnc.net
 * @link       http://www.shopnc.net
 * @since      File available since Release v1.1
 */
defined('ByShopWWI') or exit('Access Invalid!');

class shequ_dinosaur_buyControl extends mobileMemberControl {

    protected $tuan_info = array();

    public function __construct(){
        parent::__construct();
        $tuan_info = $this->getCurrentTuanInfo();
        if (empty($tuan_info)) {
            output_error('暂不支持购买');
        }
        $this->tuan_info = $tuan_info;
    }

    public function indexOp() {

        $cart_id = explode(',', $_POST['cart_id']);
        /** @var memberModel $model_member */
        $model_member = Model('member');
        $member_info = $model_member->getMemberInfoByID($this->member_info['member_id']);

        if (empty($member_info)) {
            output_error('请先登录');
        }
        $member_discount = 0;
        $member_level = 0;

        //得到购买数据
        /** @var buyLogic $logic_buy */
        $logic_buy = logic('buy');
        $if_cart = $_POST['ifcart'];
        $tuanzhang_id = intval($_POST['tuanzhang_id']);
        if (!$tuanzhang_id) {
            output_error('参数错误');
        }

        $result = $logic_buy->buyStep1($cart_id, $if_cart, $this->member_info['member_id'], $this->member_info['store_id'], null, $member_discount, $member_level);

        if (!$result['state']) {
            output_error($result['msg']);
        } else {
            $result = $result['data'];
        }

        //整理数据
        $store_cart_list = array();
        $sum = 0;
        $goods_rpt_list = array();
        foreach ($result['store_cart_list'] as $key => $value) {
            $store_cart_list[$key]['goods_list'] = array();
            $goods_rpt_list = $goods_rpt_list + array_under_reset($value, 'goods_id');
            foreach ($value as $k => $v) {
                if (empty($v['xianshi_info'])) $v['xianshi_info'] = array('xianshi_name' => '');
                $store_cart_list[$key]['goods_list'][] = $v;
            }
            //$store_cart_list[$key]['goods_list'] = $value;
            $store_cart_list[$key]['store_goods_total'] = $result['store_goods_total'][$key];
            $store_cart_list[$key]['store_goods_num_total'] = array_sum(array_column($value, 'goods_num'));
            if (!empty($result['store_premiums_list'][$key])) {
                $result['store_premiums_list'][$key][0]['premiums'] = true;
                $result['store_premiums_list'][$key][0]['goods_total'] = 0.00;
                $store_cart_list[$key]['goods_list'][] = $result['store_premiums_list'][$key][0];
            }
            $store_cart_list[$key]['store_mansong_rule_list'] = $result['store_mansong_rule_list'][$key];
            $store_cart_list[$key]['store_voucher_list'] = $result['store_voucher_list'][$key];
            $store_cart_list[$key]['store_voucher_list_all'] = Model('voucher')->getStoreVoucherList($key, $this->member_info['member_id'], 10, $store_cart_list[$key]['store_goods_total']);
            $store_cart_list[$key]['store_voucher_info'] = array();
            if ($store_cart_list[$key]['store_voucher_list']) {
                $store_cart_list[$key]['store_voucher_info'] = array('voucher_price' => $store_cart_list[$key]['store_voucher_list']['1']['voucher_price']);
            }
            if (!empty($result['cancel_calc_sid_list'][$key])) {
                $store_cart_list[$key]['freight'] = '0';
                $store_cart_list[$key]['freight_message'] = $result['cancel_calc_sid_list'][$key]['desc'];
            } else {
                $store_cart_list[$key]['freight'] = '1';
            }
            $store_cart_list[$key]['store_name'] = $value[0]['store_name'];
            $store_cart_list[$key]['store_id'] = $key;
            $sum += $store_cart_list[$key]['store_goods_total'];
            //总金额减去满减的金额
            if (isset($result['store_mansong_rule_list'][$key]['discount']) && $result['store_mansong_rule_list'][$key]['discount'] > 0) {
                $sum-= $result['store_mansong_rule_list'][$key]['discount'];
            }
        }

        $buy_list = array();
        $buy_list['freight_hash'] = $result['freight_list'];
        $buy_list['address_info'] = empty($result['address_info']) ? array(
            'address_id' => '0',
            "member_id"  => "",
            "true_name"  => "",
            "area_id"    => "",
            "city_id"    => "",
            "area_info"  => "",
            "address"    => "",
            "tel_phone"  => "",
            "mob_phone"  => "",
            "is_default" => "0",
            "dlyp_id"    => "0"
        ) : $result['address_info'];
        $buy_list['ifshow_offpay'] = $result['ifshow_offpay'];
        $buy_list['vat_hash'] = $result['vat_hash'];
        $buy_list['inv_info'] = $result['inv_info'];
        $buy_list['available_predeposit'] = 0;//ncPriceFormat($result['available_predeposit']);
        $buy_list['available_rc_balance'] = 0;//ncPriceFormat($result['available_rc_balance']);
        if (is_array($result['rpt_list']) && !empty($result['rpt_list'])) {
            foreach ($result['rpt_list'] as $k => $value) {
                if ($value['rpacket_range'] > 0) {
                    if(empty($value['rpacket_skus'])) {
                        unset($result['rpt_list'][$k]);
                        continue;
                    }
                    $skuArray = explode(',',$value['rpacket_skus']);
                    $rpt_limit_sum = 0;
                    foreach ($goods_rpt_list as $v1){
                        if(in_array($v1['goods_id'], $skuArray) === ($value['rpacket_range']==1)){
                            $rpt_limit_sum += $v1['goods_price']*$v1['goods_num'];
                        }
                    }
                    $rpt_limit_sum = ncPriceFormat($rpt_limit_sum);
                    if($rpt_limit_sum<$value['rpacket_limit']) {
                        unset($result['rpt_list'][$k]);
                        continue;
                    }
                }
                unset($result['rpt_list'][$k]['rpacket_id']);
                unset($result['rpt_list'][$k]['rpacket_end_date']);
                unset($result['rpt_list'][$k]['rpacket_owner_id']);
                unset($result['rpt_list'][$k]['rpacket_code']);
            }
        }
        $buy_list['rpt_list'] = $result['rpt_list'] ? array_values($result['rpt_list']) : array();
        $buy_list['zk_list'] = $result['zk_list'];
        $buy_list['order_amount'] = $sum;
        $buy_list['rpt_info'] = '';
        $buy_list['tuanzhang_address'] = '';
        /** @var shequ_addressModel $shequ_address_model */
        $shequ_address_model = Model('shequ_address');
        /** @var memberModel $member_model */
        $member_model = Model('member');
        $zt_address_info = $shequ_address_model->getOne(array('tuanzhang_id' => $tuanzhang_id));
        $zt_member_info = $member_model->getMemberInfo(array('member_id' => $zt_address_info['member_id']));
        $buy_list['tuanzhang_address'] = array(
            'wx_nick_name' => $zt_member_info['wx_nick_name'],
            'wx_user_avatar' => $zt_member_info['wx_user_avatar'],
            'address' => '武汉市 '. $zt_address_info['area']. ' '. $zt_address_info['address']. $zt_address_info['building'],
        );
        if (!$if_cart) {
            $address_city_id = $buy_list['address_info']['city_id'] ? $buy_list['address_info']['city_id'] : 0;
            $address_area_id = $buy_list['address_info']['area_id'] ? $buy_list['address_info']['area_id'] : 0;
            $buy_list['address_api'] = $logic_buy->changeAddr($result['freight_list'], $address_city_id, $address_area_id, $this->member_info['member_id']);
            $buy_list['address_api'] = $buy_list['address_api'] ? $buy_list['address_api'] : array();
            $buy_list['tuanzhang_address']['address'] = '';
        } else {
            $address_city_id = 258;
            $buy_list['address_api'] = $logic_buy->changeAddr($result['freight_list'], $address_city_id, $zt_address_info['area_id'], $this->member_info['member_id']);
        }

        foreach ($store_cart_list as $store_id => $storeCart) {
            $store_cart_list[$store_id]['freight'] = $buy_list['address_api']['content'][$store_id];
            $buy_list['order_amount'] += $buy_list['address_api']['content'][$store_id];
        }
        $storeCartList = array();
        foreach ($store_cart_list as $store_cart) {
            $store_cart['store_voucher_list'] = array_values($store_cart['store_voucher_list']);
            $storeCartList[] = $store_cart;
        }
        $buy_list['offpay_hash'] = $buy_list['address_api']['offpay_hash'];
        $buy_list['offpay_hash_batch'] = $buy_list['address_api']['offpay_hash_batch'];
        $buy_list['store_cart_list'] = $storeCartList;

        $wx_nick_name = $_POST['wx_nick_name'];
        $wx_user_avatar = $_POST['wx_user_avatar'];
        if ($wx_nick_name && $wx_user_avatar) {
            $model_member->editMember(array('member_id' => $this->member_info['member_id']), array('wx_nick_name' => $wx_nick_name, 'wx_user_avatar' => $wx_user_avatar));
        }
        output_data($buy_list);
    }

    public function buyOp() {

        $param = array();
        $param['ifcart'] = $_POST['ifcart'];
        $param['cart_id'] = explode(',', $_POST['cart_id']);
        $param['address_id'] = $_POST['address_id'];
        $param['vat_hash'] = $_POST['vat_hash'];
        $param['offpay_hash'] = $_POST['offpay_hash'];
        $param['offpay_hash_batch'] = $_POST['offpay_hash_batch'];
        $param['pay_name'] = 'online';
        $param['invoice_id'] = $_POST['invoice_id'];
        $param['rpt'] = $_POST['rpt'];
        $param['tuanzhang_id'] = intval($_POST['tuanzhang_id']);
        if (!$param['tuanzhang_id']) {
            output_error('参数错误');
        }
        $param['link_name'] = $_POST['link_name'];
        $param['link_phone'] = $_POST['link_phone'];

        //处理代金券
        $voucher = array();
        $post_voucher = explode(',', $_POST['voucher']);
        if(!empty($post_voucher)) {
            foreach ($post_voucher as $value) {
                list($voucher_t_id, $store_id, $voucher_price) = explode('|', $value);
                $voucher[$store_id] = $value;
            }
        }
        $param['voucher'] = $voucher;

        //手机端暂时不做支付留言，页面内容太多了
        $pay_message = array();
        $post_pay_message = explode(',', $_POST['pay_message']);
        if (!empty($post_pay_message)) {
            foreach ($post_pay_message as $value) {
                list($store_id, $message) = explode('|', $value);
                $pay_message[$store_id] = $message;
            }
        }
        $param['pay_message'] = $pay_message;
        $param['order_from'] = $_POST['ifcart'] ? 8 : 9;
        /** @var buyLogic $logic_buy */

        $logic_buy = logic('buy');
        $member_discount = $member_level = 0;
        $param['config_tuan_id'] = $this->tuan_info['config_tuan_id'];
        $result = $logic_buy->buyStep2($param, $this->member_info['member_id'], $this->member_info['member_name'], $this->member_info['member_email'],$member_discount,$member_level);
        if(!$result['state']) {
            output_error($result['msg']);
        }
        output_data(array('pay_sn' => $result['data']['pay_sn']));
    }
}

