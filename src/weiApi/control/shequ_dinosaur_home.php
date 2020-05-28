<?php
/**
 * 接龙首页
 *
 * @copyright  Copyright (c) 2007-2015 ShopNC Inc. (http://www.shopnc.net)
 * @license    http://www.shopnc.net
 * @link       http://www.shopnc.net
 * @since      File available since Release v1.1
 */
defined('ByShopWWI') or exit('Access Invalid!');

class shequ_dinosaur_homeControl extends mobileHomeControl {

    public function __construct(){
        parent::__construct();
    }

    /**
     * 接龙列表
     */
    public function indexOp() {

        $lay_x = $_POST['lay_x'];
        $lay_y = $_POST['lay_y'];

        if (empty($lay_x) || empty($lay_y)) {
            //output_error('参数错误');
        }
        $condition = array(
            'start_time' => array('gt', TIMESTAMP),
            'end_time' => array('lt', TIMESTAMP),
        );
        $memberId = $this->getMemberIdIfExists();
        /** @var shequ_addressModel $model_shequ_address */
        $model_shequ_address = Model('shequ_address');
        /** @var shequ_tuanzhangModel $model_shequ_tuanzhang */
        $model_shequ_tuanzhang = Model('shequ_tuanzhang');
        $default_address = array();
        if ($memberId) {
            /** @var memberModel $member_model */
            $member_model = Model('member');
            $member_info = $member_model->getMemberInfoByID($memberId);
            if ($member_info['default_shequ_address_id']) {
                $condition['address_id'] = array('neq', $member_info['default_shequ_address_id']);
                $address_info = $model_shequ_address->getOne(array('id' => $member_info['default_shequ_address_id']));
                if ($address_info) {
                    $tuanzhang_member_id = $address_info['member_id'];
                    $tuanzhang_info = $model_shequ_tuanzhang->getOne(array('member_id' => $tuanzhang_member_id));
                    $default_address = array(
                        'tz_id' => $tuanzhang_info['id'],
                        'tz_name' => $tuanzhang_info['name'],
                        'tz_avatar' =>  UPLOAD_SITE_URL . '/'. $tuanzhang_info['avatar'],
                        'address' => '武汉市 '. $address_info['area']. ' '. $address_info['street']. ' '. $address_info['community'] . ' '. $address_info['address'],
                    );
                }
            }
        }

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
        /** @var shequ_tuan_configModel $model_shequ_tuan_config */
        $model_shequ_tuan_config = Model('shequ_tuan_config');
        /** @var shequ_tuanModel $model_shequ_tuan */
        $model_shequ_tuan = Model('shequ_tuan');
        $shequ_config_condition = array(
            'config_start_time' => array('lt', TIMESTAMP),
            'config_end_time' => array('gt', TIMESTAMP),
        );
        $config_list = $model_shequ_tuan_config->getTuanConfigList($shequ_config_condition);
        if (empty($config_list)) {
            output_data(array(
                'goods_class_list' => array(),
                'default_address' => $default_address,
                'banner_list' => $banner_list,
                'goods_list' => array(),
            ));
        }
        $config_ids = array_column($config_list, 'config_tuan_id');
        $shequ_tuan_list = $model_shequ_tuan->getListTuan(array('config_id' => array('in', $config_ids)));
        $config_ids = array_unique(array_column($shequ_tuan_list, 'config_id'));
        if (empty($config_ids)) {
            output_data(array(
                'goods_class_list' => array(),
                'default_address' => $default_address,
                'banner_list' => $banner_list,
                'goods_list' => array(),
            ));
        }

        /** @var shequ_tuan_config_goodsModel $shequ_config_goods */
        $shequ_config_goods = Model('shequ_tuan_config_goods');
        $goods_list  = $shequ_config_goods->getTuanConfigGoodsList(array('tuan_config_id' => array('in', $config_ids)), null, '', 'gc_id');
        $gc_ids = array_column($goods_list, 'gc_id');
        if (empty($gc_ids)) {
            output_data(array(
                'goods_class_list' => array(),
                'default_address' => $default_address,
                'banner_list' => $banner_list,
                'goods_list' => array(),
            ));
        }
        /** @var goods_classModel $goods_class_model */
        $goods_class_model = Model('goods_class');
        $goods_class_list = $goods_class_model->getGoodsClassList(array('gc_id' => array('in', $gc_ids)));
        if (!empty($goods_class_list)) {
            $all_goods_class = array(
                'gc_id' => 0,
                'gc_name' => '全部'
            );
            array_unshift($goods_class_list, $all_goods_class);
        }
        output_data(array(
            'goods_class_list' => $goods_class_list,
            'default_address' => empty($default_address) ? '' : $default_address,
            'banner_list' => $banner_list,
            'tuan_list' => $this->_get_tuan_list(0, array($lay_x,$lay_y))
        ));
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

