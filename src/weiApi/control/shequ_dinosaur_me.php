<?php
/**
 * 我的接龙
 *
 * @copyright  Copyright (c) 2007-2015 ShopNC Inc. (http://www.shopnc.net)
 * @license    http://www.shopnc.net
 * @link       http://www.shopnc.net
 * @since      File available since Release v1.1
 */
defined('ByShopWWI') or exit('Access Invalid!');

class shequ_dinosaur_meControl extends mobileMemberControl {

    public function __construct(){
        parent::__construct();
    }

    /**
     * 接龙列表
     */
    public function indexOp() {
        $condition = array(
            'buyer_id' => $this->member_info['member_id'],
            'shequ_tuan_id' => array('gt', 0)
        );
        /** @var orderModel $order_model */
        $order_model = Model('order');
        $order_list = $order_model->getOrderList($condition, '', 'shequ_tuan_id');
        $shequ_tuan_ids = array_column($order_list, 'shequ_tuan_id');
        if (empty($shequ_tuan_ids)) {
            output_data(array(
                'tuan_list' => array(),
            ),  mobile_page(0));
        }
        $shequ_tuan_ids = array_unique($shequ_tuan_ids);
        array_push($shequ_tuan_ids, 9);
        /** @var shequ_tuanModel $shequ_tuan_model */
        $shequ_tuan_model = Model('shequ_tuan');
        $res = $shequ_tuan_model->getList(array('id' => array('in', $shequ_tuan_ids)), $this->page);
        $page_count = $shequ_tuan_model->gettotalpage();
        $config_ids = array_column($res, 'config_id');
        /** @var shequ_tuan_config_goodsModel $config_goods_model */
        $config_goods_model = Model('shequ_tuan_config_goods');
        $config_goods_list = $config_goods_model->getTuanConfigGoodsList(array('tuan_config_id' => array('in', $config_ids)));
        /** @var goodsModel $goods_model */
        $goods_model = Model('goods');
        $goods_list = $goods_model->getGoodsList(array('goods_id' => array('in', array_column($config_goods_list, 'goods_id'))));
        $goods_list = array_under_reset($goods_list, 'goods_id');
        $new_config_goods = array();
        foreach ($config_goods_list as $value) {
            $new_config_goods[$value['tuan_config_id']][] = $value['goods_id'];
        }

        foreach ($res as $key=>$value) {
            $config_id = $value['config_id'];
            $goods_ids = $new_config_goods[$config_id];
            $join_num = 0;
            $max_price = 0;
            $min_price = 0;
            if ($goods_ids) {
                foreach ($goods_ids as $goods_id) {
                    $goods_info = $goods_list[$goods_id];
                    if (empty($goods_info)) {
                        continue;
                    }
                    $join_num += $goods_info['goods_salenum'];
                    if ($min_price == 0) {
                        $max_price = $goods_info['goods_price'];
                        $min_price = $goods_info['goods_price'];
                    }
                    if ($goods_info['goods_price'] < $min_price) {
                        $min_price = $goods_info['goods_price'];
                    }
                    if ($goods_info['goods_price'] > $max_price) {
                        $max_price = $goods_info['goods_price'];
                    }
                }
            }
            $ggg = $this->_get_buyer_image($value['id']);
            $res[$key]['add_time_text'] = date('m-d h:i', $value['add_time']);
            $res[$key]['join_num'] = $ggg[1];
            $res[$key]['tz_name'] = $this->getMemberWxNickName($value['tz_name'], $value['member_id']);
            $res[$key]['goods_price'] = $max_price == 0 ? '3~100' : ($min_price == $max_price) ? $min_price : $min_price . '~' . $max_price;
            $res[$key]['join_people_avatar'] = $ggg[0];//$join_people_avatar;// array('http://www.test.hangowa.com/data/upload/shop/common/06410574520881827.jpg','http://www.test.hangowa.com/data/upload/shop/common/06410574520881827.jpg');
        }
        if (intval($_POST['curpage']) > intval($page_count)) $res = array();
        output_data(array('tuan_list' => $res), mobile_page($page_count));

    }

    private function _get_buyer_image($tuan_id) {

        $join_people_avatar = array();
        /** @var orderModel $order_model */
        $order_model = Model('order');
        $order_list = $order_model->getOrderList(array('shequ_tuan_id' => $tuan_id,'order_state' => array('egt', ORDER_STATE_PAY), 'refund_state' => 0), 3, 'buyer_id', '', '', array('member'));
        foreach ($order_list as $order) {
            array_push($join_people_avatar, $order['extend_member']['wx_user_avatar']);
        }
        return array($join_people_avatar,$order_model->gettotalnum());

    }

    private function _get_tuan_list($gc_id, $params)
    {
        $tuan_config_ids = array();
        if ($gc_id > 0) {
            /** @var shequ_tuan_config_goodsModel $shequ_config_goods */
            $shequ_config_goods = Model('shequ_tuan_config_goods');
            $goods_list  = $shequ_config_goods->getTuanConfigGoodsList(array('gc' => $gc_id), null, '', 'tuan_config_id');
            if (!empty($goods_list)) {
                $tuan_config_ids = array_column($goods_list, 'tuan_config_id');
            }
        }

        /** @var shequ_tuan_configModel $model_shequ_tuan_config */
        $model_shequ_tuan_config = Model('shequ_tuan_config');
        /** @var shequ_tuanModel $model_shequ_tuan */
        $model_shequ_tuan = Model('shequ_tuan');
        $shequ_config_condition = array(
            'config_start_time' => array('lt', TIMESTAMP),
            'config_end_time' => array('gt', TIMESTAMP),
        );
        if (!empty($tuan_config_ids)) {
            $shequ_config_condition['config_tuan_id'] = array('in', $tuan_config_ids);
        }
        $config_tuan_list = $model_shequ_tuan_config->getTuanConfigList($shequ_config_condition);
        if (empty($config_tuan_list)) {
            return array();
        }
        $config_ids = array_column($config_tuan_list, 'config_tuan_id');

        $order = 'start_time ASC';
        if ($params[0]) {
            $order = 'ACOS(SIN(('.$params[0].' * 3.1415) / 180 ) *SIN((longitude * 3.1415) / 180 ) +COS(('.$params[0].' * 3.1415) / 180 ) * COS((longitude * 3.1415) / 180 ) *COS(('.$params[1].' * 3.1415) / 180 - (latitude * 3.1415) / 180 ) ) * 6380  asc';
        }
        $shequ_tuan_list = $model_shequ_tuan->getList(array('config_id' => array('in', $config_ids)), $this->page, $order);
        if (empty($shequ_tuan_list)) {
            return array();
        }
        $new_tuan_list = array();
        /** @var shequ_addressModel $shequ_address_model */
        $shequ_address_model = Model('shequ_address');
        $shequ_address_list = $shequ_address_model->getList(array('id' => array('in', array_column($shequ_tuan_list, 'address_id'))), 100);
        $shequ_address_list = array_under_reset($shequ_address_list, 'id');

        /** @var shequ_tuan_config_goodsModel $shequ_tuan_config_goods_model */
        $shequ_tuan_config_goods_model = Model('shequ_tuan_config_goods');
        $shequ_config_goods_list = $shequ_tuan_config_goods_model->getTuanConfigGoodsList(array('tuan_config_id' => array('in', $config_ids)));
        if (empty($shequ_config_goods_list)) {
            return array();
        }

        /** @var goodsModel $goods_model */
        $goods_model = Model('goods');
        $goods_list = $goods_model->getGoodsList(array('goods_id' => array('in', array_column($shequ_config_goods_list, 'goods_id'))), 'goods_id,goods_commonid,goods_price,goods_salenum');
        $goods_list = array_under_reset($goods_list, 'goods_id');
        $goods_commonids = array_column($goods_list, 'goods_commonid');
        $goods_common_list = $goods_model->getGoodsCommonList(array('goods_commonid' => array('in', $goods_commonids)), 'goods_commonid,goods_name,goods_image,store_id');
        $goods_common_list = array_under_reset($goods_common_list, 'goods_commonid');
        foreach ($goods_list as $k=>$v) {
            $common_info = $goods_common_list[$v['goods_commonid']];
            if (empty($common_info)) {
                continue;
            }
            $common_info['goods_image'] = thumb($common_info, 360);
            $goods_list[$k]['goods_common_info'] = $common_info;
        }

        $new_shequ_config_goods_list = array();
        foreach ($shequ_config_goods_list as $vv) {
            $goods_info = $goods_list[$vv['goods_id']];
            if (empty($goods_info) || empty($goods_info['goods_common_info'])) {
                continue;
            }
            $new_shequ_config_goods_list[$vv['tuan_config_id']][] = $goods_info;
        }

        foreach ($shequ_tuan_list as $tuan_info) {
            $tuan_info['deliver_type'] = $tuan_info['type'] == 1 ? '物流发货' : '门店自提';
            $address = $tuan_info['address'];
            if (isset($shequ_address_list[$tuan_info['address_id']])) {
                $address = $shequ_address_list[$tuan_info['address_id']]['area'] . $shequ_address_list[$tuan_info['address_id']]['street'] . $shequ_address_list[$tuan_info['address_id']]['community']. $shequ_address_list[$tuan_info['address_id']]['address'];
            }

            if (empty($new_shequ_config_goods_list[$tuan_info['config_id']])) {
                continue;
            }
            $sales_num = 0;
            $new_goods_info = array();
            foreach ($new_shequ_config_goods_list[$tuan_info['config_id']] as $goods_info){
                $sales_num += $goods_info['goods_salenum'];
                $new_goods_info = $goods_info['goods_common_info'];
                $new_goods_info['goods_price'] = $goods_info['goods_price'];
            }
            $new_tuan_list[] = array(
                'tuan_info' => $tuan_info,
                'address_info' => $address,
                'goods_info' => $new_goods_info,
                'sold_num' => $sales_num,
            );
        }

        return $new_tuan_list;
    }

}

