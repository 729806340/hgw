<?php
/**
 * 首页
 *
 * @copyright  Copyright (c) 2007-2015 ShopNC Inc. (http://www.shopnc.net)
 * @license    http://www.shopnc.net
 * @link       http://www.shopnc.net
 * @since      File available since Release v1.1
 */
defined('ByShopWWI') or exit('Access Invalid!');

class shequ_captial_homeControl extends mobileHomeControl {

    protected  $tuan_info = array();
    public function __construct(){
        parent::__construct();
        $tuan_info = $this->getCurrentTuanInfo();
        if (empty($tuan_info)) {
            output_error('暂无数据');
        }
        $this->tuan_info = $tuan_info;
    }

    public function indexOp()
    {
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
        /** @var mb_specialModel $model_mb_special */
        $model_mb_special = Model('mb_special');
        $item_list = $model_mb_special->getAppSpecialIndex();
        $banner_list = array();
        foreach ($item_list as $k=>$item) {
            if (isset($item['adv_list']['item'])) {
                $banner_list = $item['adv_list']['item'];
                break;
            }
        }
        $return = array(
            'banner_list' => $banner_list,
            'tuan_info' => array(
                'end_time' => $this->tuan_info['config_end_time'],
                'send_product_date' => date('Y-m-d H:i:s', $this->tuan_info['send_product_date']),
            ),
            'xianshi_list' => $this->_getTuanXianshi($area_type, $this->tuan_info['config_tuan_id']),
            'goods_class' => $area_type == '武汉市' ? $this->_get_goods_categroy(1, $this->tuan_info['config_tuan_id']) :  $this->_get_goods_categroy(0, $this->tuan_info['config_tuan_id']),
            'goods_list' => array(
                'mdzt' => ($area_type == '武汉市') ? $this->_get_goods(1, $this->tuan_info['config_tuan_id']) : array(),
                'bydj' => $this->_get_goods(0, $this->tuan_info['config_tuan_id']),
            ),
        );
        output_data_new($return);
    }

    private function _getTuanXianshi($area_type, $tuan_id)
    {
        /** @var storeModel $store_model */
        $store_model = Model('store');
        $zt_store_list = $store_model->getStoreList(array('is_shequ_tuan' => 1));
        $zt_store_ids = array_column($zt_store_list, 'store_id');
        $condition = array(
            'tuan_config_id' => $tuan_id,
            'state' => 1,
            'end_time' => array('gt', TIMESTAMP),
        );
        /** @var shequ_xianshiModel $shequ_xianshi_model */
        $shequ_xianshi_model = Model('shequ_xianshi');
        $xianshi_list = $shequ_xianshi_model->getXianShiConfigList($condition, 1, 'start_time asc', 'xianshi_id');
        if (empty($xianshi_list)) {
            return array();
        }
        $xianshi_ids = array_column($xianshi_list, 'xianshi_id');
        $goods_xianshi_condition = array(
            'xianshi_id' => array('in', $xianshi_ids),
            'tuan_config_id' => $tuan_id,
        );
        if ($area_type == '非武汉市' && !empty($zt_store_ids)) {
            $goods_xianshi_condition['store_id'] =array('not in', $zt_store_ids);
        }

        /** @var shequ_xianshi_goodsModel $shequ_xianshi_goods_model */
        $shequ_xianshi_goods_model = Model('shequ_xianshi_goods');
        $xianshi_goods_list = $shequ_xianshi_goods_model->getXianshiGoodsList($goods_xianshi_condition);
        $config_xianshi_time = array(
            'start_time' => 0,
            'end_time' => 0,
        );
        $goods_ids = array_column($xianshi_goods_list, 'goods_id');
        /** @var cartModel $cart_model */
        $cart_model = Model('cart');
        $cart_list = array();
        $member_id = $this->getMemberIdIfExists();
        if ($member_id) {
            $cart_list = $cart_model->listCart('db', array('buyer_id' => $member_id, 'goods_id' => array('in', $goods_ids), 'config_tuan_id' => $tuan_id));
            $cart_list = array_under_reset($cart_list, 'goods_id');
        }
        foreach ($xianshi_goods_list as $k=>$v) {
            $config_xianshi_time['start_time'] = $v['start_time'];
            $config_xianshi_time['end_time'] = $v['end_time'];
            $xianshi_goods_list[$k]['is_zt'] = in_array($v['store_id'], $zt_store_ids) ? 1 : 0;
            $xianshi_goods_list[$k]['cart_num'] = isset($cart_list[$v['goods_id']]) ? $cart_list[$v['goods_id']]['goods_num'] : 0;
            $xianshi_goods_list[$k]['goods_image_url'] = thumb($v);
        }
        return array($config_xianshi_time, $xianshi_goods_list);
    }

    private function _getXianshiGoodsIds($tuan_id) {

        $condition = array(
            'tuan_config_id' => $tuan_id,
            'state' => 1,
            'end_time' => array('gt', TIMESTAMP),
        );
        /** @var shequ_xianshiModel $shequ_xianshi_model */
        $shequ_xianshi_model = Model('shequ_xianshi');
        $xianshi_list = $shequ_xianshi_model->getXianShiConfigList($condition, '', '', 'xianshi_id');
        if (empty($xianshi_list)) {
            return array();
        }
        $xianshi_ids = array_column($xianshi_list, 'xianshi_id');
        $goods_xianshi_condition = array(
            'xianshi_id' => array('in', $xianshi_ids),
            'tuan_config_id' => $tuan_id,
        );
        /** @var shequ_xianshi_goodsModel $shequ_xianshi_goods_model */
        $shequ_xianshi_goods_model = Model('shequ_xianshi_goods');
        $xianshi_goods_list = $shequ_xianshi_goods_model->getXianshiGoodsList($goods_xianshi_condition, '', '', 'goods_id');
        $goods_ids = array_column($xianshi_goods_list, 'goods_id');
        return $goods_ids;
    }

    private function _get_goods_categroy($type = 0, $tuan_id)
    {
        /** @var shequ_config_goods_classModel $shequ_config_goods_class_model */
        $shequ_config_goods_class_model = Model('shequ_config_goods_class');
        $page_number = ($type == 1) ? 7 :8;
        $goods_class_list = $shequ_config_goods_class_model->getItems(array('type_id' => $type, 'tuan_config_id' => $tuan_id), $page_number);
        $goods_class_new = array();
        foreach ($goods_class_list as $goods_class) {
            $goods_class_new[] = array(
                'gc_id' => $goods_class['gc_id'],
                'gc_name' => $goods_class['gc_name'],
                'app_img' => UPLOAD_SITE_URL.DS.ATTACH_COMMON.DS.$goods_class['app_img'],
            );
        }
        if ($type == 1) {
            $goods_class_one = $shequ_config_goods_class_model->getGoodsClassInfo(array('type_id' => 0, 'tuan_config_id' => $tuan_id));
            if (!empty($goods_class_one)) {
                array_unshift($goods_class_new, array(
                    'gc_id' => $goods_class_one['gc_id'],
                    'gc_name' => $goods_class_one['gc_name'],
                    'app_img' => UPLOAD_SITE_URL.DS.ATTACH_COMMON.DS.$goods_class_one['app_img'],
                ));
            }
        }
        return $goods_class_new;
    }

    private function _get_goods($type = 0, $tuan_id)
    {
        /** @var shequ_config_goods_classModel $shequ_config_goods_class_model */
        $shequ_config_goods_class_model = Model('shequ_config_goods_class');
        $goods_class_list = $shequ_config_goods_class_model->getItems(array('type_id' => $type, 'tuan_config_id' => $tuan_id), '', '', 'gc_id');
        if (empty($goods_class_list)) {
            return array();
        }
        $gc_ids = array_column($goods_class_list, 'gc_id');
        /** @var shequ_tuan_config_goodsModel $shequ_tuan_config_goods_model */
        $shequ_tuan_config_goods_model = Model('shequ_tuan_config_goods');
        $tuan_config_condition = array(
            'tuan_config_id' => $tuan_id,
            'gc_id' => array('in', $gc_ids)
        );
        $xianshi_goods_ids = $this->_getXianshiGoodsIds($tuan_id);
        if (!empty($xianshi_goods_ids)) {
            $tuan_config_condition['goods_id'] = array('not in', $xianshi_goods_ids);
        }
        $goods_ids = $shequ_tuan_config_goods_model->getTuanConfigGoodsList($tuan_config_condition, '', '', 'goods_id');
        $goods_ids = array_column($goods_ids, 'goods_id');
        /** @var goodsModel $goods_model */
        $goods_model = Model('goods');
        $goods_list = $goods_model->getGoodsOnlineList(array('goods_id' => array('in', $goods_ids)));
        if (empty($goods_list)) {
            return array();
        }
        $goods_ids = array_column($goods_list, 'goods_id');
        /** @var cartModel $cart_model */
        $cart_model = Model('cart');
        $cart_list = array();
        $member_id = $this->getMemberIdIfExists();
        if ($member_id) {
            $cart_list = $cart_model->listCart('db', array('buyer_id' => $member_id, 'goods_id' => array('in', $goods_ids), 'config_tuan_id' => $tuan_id));
            $cart_list = array_under_reset($cart_list, 'goods_id');
        }
        $result = array();
        foreach ($goods_list as $goods_info) {
            //规格
            $_tmp_name = unserialize($goods_info['spec_name']);
            $_tmp_value = unserialize($goods_info['goods_spec']);
            $goods_info['goods_spec'] = '';
            $goods_info['cart_num'] = isset($cart_list[$goods_info['goods_id']]) ? $cart_list[$goods_info['goods_id']]['goods_num'] : 0;
            if (is_array($_tmp_name) && is_array($_tmp_value)) {
                $_tmp_name = array_values($_tmp_name);$_tmp_value = array_values($_tmp_value);
                foreach ($_tmp_name as $sk => $sv) {
                    $goods_info['goods_spec'] .= $sv.'：'.$_tmp_value[$sk].'，';
                }
            }
            $result[$goods_info['goods_commonid']][$goods_info['goods_id']] = array(
                'goods_name' => str_replace($goods_info['goods_spec'], '', $goods_info['goods_name']),
                'goods_spec' => $goods_info['goods_spec'],
                'goods_image_url' => cthumb($goods_info['goods_image'],360, $goods_info['store_id']),
                'goods_price' => $goods_info['goods_price'],
                'goods_market_price' => $goods_info['goods_market_price'],
                'goods_id' => $goods_info['goods_id'],
                'cart_num' => $goods_info['cart_num'],
                'is_zt' => $type,
                'goods_salenum' => $goods_info['goods_salenum'],
                'goods_storage' => $goods_info['goods_storage'],
                'goods_tuan_sale' => 0,
                'goods_tuan_member_avatar' => array(),
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
                'show_type' => 1,//1普通 2视频
                'goods_list' => array_values($value),
                'goods_price' => $max_price == $min_price ? $max_price : $max_price . '~'. $min_price,
                'goods_market_price' => $max_market_price == $min_market_price ? $max_market_price : $max_market_price . '~'. $min_market_price,
            );
        }
        return $goods_new_list;
    }

    public function default_tuanzhangOp()
    {

        $tz_id = intval($_POST['tz_id']);
        $is_default = 0;
        /** @var shequ_tuanzhangModel $tuanzhang_model */
        $tuanzhang_model = Model('shequ_tuanzhang');
        if ($tz_id > 0) {
            $default_tuanzhang_id = $tz_id;
            $member_id = $this->getMemberIdIfExists();
            if ($member_id) {
                /** @var memberModel $member_model */
                $member_model = Model('member');
                $member_model->editMember(array('member_id' => $member_id), array('default_shequ_tuanzhang_id' => $tz_id));
            }
            $tuanzhang_info = $tuanzhang_model->getOne(array(
                'id' => $default_tuanzhang_id
            ));
        } else {
            $default_tuanzhang_id = 22; //ay_x:that.data.lay_x, lay_y:that.data.lay_y
            $member_id = $this->getMemberIdIfExists();
            if ($member_id) {
                /** @var memberModel $member_model */
                $member_model = Model('member');
                $member_info = $member_model->getMemberInfoByID($member_id);
                if ($member_info['tuanzhang_id']) {
                    $default_tuanzhang_id = $member_info['tuanzhang_id'];
                    $is_default = 1;
                } elseif ($member_info['default_shequ_tuanzhang_id']) {
                    $default_tuanzhang_id = $member_info['default_shequ_tuanzhang_id'];
                    $is_default = 1;
                }
            }
            $tuanzhang_info = $tuanzhang_model->getOne(array(
                'id' => $default_tuanzhang_id
            ));
        }

        if (empty($tuanzhang_info)) {
            $tuanzhang_info = $tuanzhang_model->getOne(array('state' => 1));
            if ($tuanzhang_info) {
                $default_tuanzhang_id = $tuanzhang_info['id'];
            }
        }


        /** @var shequ_addressModel $shequ_address_model */
        $shequ_address_model = Model('shequ_address');
        $tuanzhang_address = $shequ_address_model->getOne(array(
            'tuanzhang_id' => $default_tuanzhang_id,
        ));
        $return = array(
            'tuanzhang_info' => array(
                'id' => $tuanzhang_info['id'],
                'avatar' => $tuanzhang_info['avatar'],
                'name' => $tuanzhang_info['name'],
            ),
            'tuanzhang_address' => array(
                'address' => $tuanzhang_address['city_name'] . $tuanzhang_address['area'] . $tuanzhang_address['street'] . $tuanzhang_address['community'] . $tuanzhang_address['address'],
                'building' => $tuanzhang_address['building'],
            ),
            'is_default' => $is_default,
        );
        output_data_new($return);
    }

}

