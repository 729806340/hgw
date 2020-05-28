<?php
/**
 * 团长开团海报
 *
 * @copyright  Copyright (c) 2007-2015 ShopNC Inc. (http://www.shopnc.net)
 * @license    http://www.shopnc.net
 * @link       http://www.shopnc.net
 * @since      File available since Release v1.1
 */
defined('ByShopWWI') or exit('Access Invalid!');

class shequ_tuan_toolControl extends mobileMemberTuanControl {

    public function __construct(){
        parent::__construct();
    }

    public function posterOp()
    {
        $tuanzhang_id = $this->member_info['tuanzhang_id'];
        $tuan_info = $this->getCurrentTuanInfo();
        if (empty($tuan_info)) {
            output_error('暂无正在进行的团');
        }
        $tuan_id = $tuan_info['config_tuan_id'];
        /** @var shequ_tuan_config_goodsModel $shequ_tuan_config_goodsModel */
        $shequ_tuan_config_goodsModel = Model('shequ_tuan_config_goods');
        $condition = array(
            'tuan_config_id' => $tuan_id,
        );
        $goods_list = $shequ_tuan_config_goodsModel->getTuanConfigGoodsList($condition, 6);
        $goods_list_new = array();
        $goods_ids = array_column($goods_list, 'goods_id');
        if (empty($goods_ids)) {
            output_error('暂无正在进行的团!');
        }

        if (count($goods_ids) != 6) {
            output_error('暂不能生成海报!');
        }

        /** @var goodsModel $goodsModel */
        $goodsModel = Model('goods');
        $goods_old_list = $goodsModel->getGoodsList(array('goods_id' => array('in', $goods_ids)));
        $goods_old_list = array_under_reset($goods_old_list, 'goods_id');
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
            $goods_list_new[] = array(
                'goods_id' => $goods_info['goods_id'],
                'goods_name' => $goods_info['goods_name'],
                'goods_spec' => $goods_info['goods_spec'],
                'goods_price' => $goods_info['goods_price'],
                'goods_marketprice' => $goods_info['goods_marketprice'],
                'goods_image' => cthumb($goods_info['goods_image'], 240, $goods_info['store_id']),
            );
        }
        /** @var shequ_addressModel $shequ_address */
        $shequ_address = Model('shequ_address');
        $shequ_address_info = $shequ_address->getOne(array(
            'tuanzhang_id' => $tuanzhang_id,
        ));
        /** @var shequ_tuanzhangModel $shequ_tuanzhang_model */
        $shequ_tuanzhang_model = Model('shequ_tuanzhang');
        $tuanzhang_info = $shequ_tuanzhang_model->getOne(array('id' => $tuanzhang_id));
        if (empty($shequ_address_info) || empty($tuanzhang_info)) {
            output_error('异常');
        }

        /** @var wx_small_appLogic $wxSmallApp */
        $wxSmallApp = Logic('wx_small_app');
        $share_wei_qr = $wxSmallApp->getQrHttp('pages/index/index',$tuanzhang_id,'https://www.hangowa.com/data/upload/mobile/special/s0/s0_06437219117022904.jpg');
        $result = array(
            'goods_list' => $goods_list_new,
            'share_wei_qr' => $share_wei_qr,
            'tuanzhang_info' => array(
                'tuanzhang_name' => $tuanzhang_info['name'],
                'address' =>  $shequ_address_info['city_name'] == '武汉' ?  $shequ_address_info['area'].  $shequ_address_info['street'] . $shequ_address_info['community']. $shequ_address_info['address'] : '',
            ),
        );
        output_data_new($result);
    }



}

