<?php
/**
 * 分类页
 *
 * @copyright  Copyright (c) 2007-2015 ShopNC Inc. (http://www.shopnc.net)
 * @license    http://www.shopnc.net
 * @link       http://www.shopnc.net
 * @since      File available since Release v1.1
 */
defined('ByShopWWI') or exit('Access Invalid!');

class shequ_categroyControl extends mobileHomeControl {

    protected $shequ_tuan_config_info = array();
    public function __construct(){
        parent::__construct();
        $shequ_tuan_config_info = $this->getCurrentTuanInfo();
        if (empty($shequ_tuan_config_info)) {
            output_error('今天没有团啊');
        }
        $this->shequ_tuan_config_info = $shequ_tuan_config_info;
    }

    /**
     * 分类页面
     */
    public function indexOp() {
        $tuan_id = $this->shequ_tuan_config_info['config_tuan_id'];
        $tz_id = $_POST['tz_id'];
        /** @var shequ_tuanzhangModel $tuanzhang_model */
        $tuanzhang_model = Model('shequ_tuanzhang');
        $tuanzhang_info = $tuanzhang_model->getOne(array(
            'id' => $tz_id
        ));
        if (empty($tuanzhang_info)) {
            output_error('参数错误');
        }
        $is_bydj = intval($_POST['bydj']);//是否包邮到家
        $area_type = ($tuanzhang_info['area'] && !$is_bydj) ? '武汉市' : '非武汉市';

        $condition = array(
            'tuan_config_id' => $tuan_id,
        );
        if ($area_type != '武汉市') {
            $condition['type_id'] = 0;
        }
        /** @var shequ_config_goods_classModel $shequ_config_goods_classModel */
        $shequ_config_goods_classModel = Model('shequ_config_goods_class');
        $goods_class_list = $shequ_config_goods_classModel->getItems($condition);
        output_data(array(
            'goods_class' => $goods_class_list,
        ));
    }

    public function get_goods_listOp() {
        $tuan_id = $this->shequ_tuan_config_info['config_tuan_id'];
        $goods_class_id = intval($_POST['goods_class_id']);
        $goods_name = trim($_POST['goods_name']);
        if (empty($goods_class_id) && empty($goods_name)) {
            output_error('参数错误');
        }
        /** @var shequ_tuan_config_goodsModel $shequ_tuan_config_goodsModel */
        $shequ_tuan_config_goodsModel = Model('shequ_tuan_config_goods');
        $condition = array(
            'tuan_config_id' => $tuan_id,
        );
        if ($goods_class_id) {
            $condition['gc_id'] = $goods_class_id;
        }
        $goods_list = $shequ_tuan_config_goodsModel->getTuanConfigGoodsList($condition, $this->page);
        $goods_ids = array_column($goods_list, 'goods_id');
        $goods_old_list = array();
        if (!empty($goods_ids)) {
            /** @var goodsModel $goodsModel */
            $goodsModel = Model('goods');
            $goods_old_list = $goodsModel->getGoodsList(array('goods_id' => array('in', $goods_ids)));
            $goods_old_list = array_under_reset($goods_old_list, 'goods_id');
        }

        $goods_class_ids = array_column($goods_list, 'gc_id');
        /** @var shequ_config_goods_classModel $shequ_config_goods_classModel */
        $shequ_config_goods_classModel = Model('shequ_config_goods_class');
        $goods_class_list = $shequ_config_goods_classModel->getItems(array('gc_id' => array('in', $goods_class_ids), 'tuan_config_id' => $tuan_id), '', '', 'gc_id,type_id');
        $goods_class_list = array_column($goods_class_list,'type_id','gc_id');

        /** @var cartModel $cart_model */
        $cart_model = Model('cart');
        $cart_list = array();
        $member_id = $this->getMemberIdIfExists();
        if ($member_id) {
            $cart_list = $cart_model->listCart('db', array('buyer_id' => $member_id, 'goods_id' => array('in', $goods_ids), 'config_tuan_id' => $tuan_id));
            $cart_list = array_under_reset($cart_list, 'goods_id');
        }
        $result = array();

        /** @var shequ_xianshi_goodsModel $shequ_xianshi_goods_model */
        $shequ_xianshi_goods_model = Model('shequ_xianshi_goods');
        $shequ_xianshi_goods = $shequ_xianshi_goods_model->getXianshiGoodsListInfoByGoodsIds($goods_ids, $tuan_id);

        foreach ($goods_list as $goods) {
            if (!isset($goods_old_list[$goods['goods_id']])) {
                continue;
            }
            $goods_info = $goods_old_list[$goods['goods_id']];
            //规格
            $_tmp_name = unserialize($goods_info['spec_name']);
            $_tmp_value = unserialize($goods_info['goods_spec']);
            $goods_info['goods_spec'] = '';
            if (is_array($_tmp_name) && is_array($_tmp_value)) {
                $_tmp_name = array_values($_tmp_name);$_tmp_value = array_values($_tmp_value);
                foreach ($_tmp_name as $sk => $sv) {
                    $goods_info['goods_spec'] .= $sv.'：'.$_tmp_value[$sk].'，';
                }
            }
            $goods_info['cart_num'] = isset($cart_list[$goods_info['goods_id']]) ? $cart_list[$goods_info['goods_id']]['goods_num'] : 0;
            $result[$goods_info['goods_commonid']][$goods_info['goods_id']] = array(
                'goods_name' => str_replace($goods_info['goods_spec'], '', $goods_info['goods_name']),
                'goods_spec' => $goods_info['goods_spec'],
                'goods_image_url' => thumb($goods_info),
                'goods_price' => $goods_info['goods_price'],
                'goods_market_price' => $goods_info['goods_marketprice']> 0 ? $goods_info['goods_marketprice'] : 0,
                'goods_id' => $goods_info['goods_id'],
                'cart_num' => $goods_info['cart_num'],
                'is_zt' => $goods_class_list[$goods['gc_id']],
                'if_shequ_xianshi' => isset($shequ_xianshi_goods[$goods_info['goods_id']]) ? true : false,
                'shequ_xianshi_info' => isset($shequ_xianshi_goods[$goods_info['goods_id']]) ? $shequ_xianshi_goods[$goods_info['goods_id']] : '',
            );
        }
        $goods_new_list = array();
        foreach ($result as $value) {
            $max_price = 0;
            $min_price = 0;
            $max_market_price = 0;
            $min_market_price = 0;
            foreach ($value as $v) {
                if ($max_price == 0) {
                    $max_price = $v['goods_price'];
                    $min_price = $v['goods_price'];
                    $max_market_price = $v['goods_market_price'] > 0  ? $v['goods_market_price'] : 0;
                    $min_market_price = $v['goods_market_price'] > 0  ? $v['goods_market_price'] : 0;
                }
                if ($v['goods_price'] > $max_price) {
                    $max_price = $v['goods_price'];
                }
                if ($v['goods_price'] < $min_price) {
                    $min_price = $v['goods_price'];
                }

                if ($v['goods_market_price'] > $max_market_price) {
                    $max_market_price = $v['goods_market_price'];
                }
                if ($v['goods_market_price'] < $min_market_price) {
                    $min_market_price = $v['goods_market_price'];
                }
            }
            $goods_new_list[] = array(
                'goods_list' => array_values($value),
                'goods_price' => $max_price == $min_price ? $max_price : $max_price . '~'. $min_price,
                'goods_market_price' => $max_market_price == $min_market_price ? $max_market_price : $max_market_price . '~'. $min_market_price,
            );
        }
        $page_count = $shequ_tuan_config_goodsModel->gettotalpage();
        if (intval($_POST['curpage']) > $page_count) $goods_new_list = array();
        output_data(array('goods_list' => $goods_new_list), mobile_page($page_count));

    }

}

