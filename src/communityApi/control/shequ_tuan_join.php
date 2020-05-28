<?php
/**
 *
 *
 * @copyright  Copyright (c) 2007-2015 ShopNC Inc. (http://www.shopnc.net)
 * @license    http://www.shopnc.net
 * @link       http://www.shopnc.net
 * @since      File available since Release v1.1
 */

defined('ByShopWWI') or exit('Access Invalid!');

class shequ_tuan_joinControl extends mobileHomeControl
{

    public function __construct()
    {
        parent::__construct();
    }

    public function indexOp()
    {

        $address_id = intval($_POST['address_id']);
        $tuan_config_id = intval($_POST['tuan_config_id']);
        $member_id = intval($_POST['member_id']);
        /** @var shequ_addressModel $model_shequ_address */
        $model_shequ_address = Model('shequ_address');
        $address_info = $model_shequ_address->getOne(array('member_id' => $member_id, 'id' => $address_id));
        if (empty($address_info)) {
            //output_error('收货地点不正确');
        }

        //查询用户是否发起了该拼团
        /** @var shequ_tuanModel $shequ_tuan_model */
        $shequ_tuan_model = Model('shequ_tuan');
        $shequ_tuan_info = $shequ_tuan_model->getOne(array('member_id' => $member_id, 'config_id' => $tuan_config_id, 'address_id' => $address_id));
        if (!empty($shequ_tuan_info)) {
            output_error('已经发起过团购了');
        }
        /** @var shequ_tuan_configModel $model_tuan_config */
        $model_tuan_config = Model('shequ_tuan_config');
        $tuan_config_info = $model_tuan_config->getTuanConfigInfo(array('config_tuan_id' => $tuan_config_id));
        if (empty($tuan_config_info)) {
            output_error('参数不正确');
        }
        if ($tuan_config_info['config_end_time'] <= TIMESTAMP) {
            output_error('已经结束了');
        }

        if (empty($address_info) && $tuan_config_info['type'] == 2) {
            output_error('收货地点不正确');
        }

        /** @var shequ_tuan_config_goodsModel $model_tuan_config_goods */
        $model_tuan_config_goods = Model('shequ_tuan_config_goods');
        $config_goods_info = $model_tuan_config_goods->getTuanConfigGoodsInfo(array('tuan_config_id' => $tuan_config_id));
        if (empty($config_goods_info)) {
            output_error('暂不支持发起团购');
        }
        /** @var shequ_tuanzhangModel $shequ_tuanzhang_model */
        $shequ_tuanzhang_model = Model('shequ_tuanzhang');
        $tuanzhang_info = $shequ_tuanzhang_model->getOne(array('member_id' => $member_id));
        if (empty($tuanzhang_info)) {
            output_error('异常！！');
        }

        $insert_arr = array(
            'name' => $tuan_config_info['config_tuan_name'],
            'member_id' => $member_id,
            'start_time' => $tuan_config_info['config_start_time'],
            'end_time' => $tuan_config_info['config_end_time'],
            'type' => $tuan_config_info['type'],
            'config_pic' => UPLOAD_SITE_URL.DS.ATTACH_COMMON.DS.$tuan_config_info['config_pic'],
            'address_id' => empty($address_info) ? 0 : $address_info['id'],
            'address' => empty($address_info) ? '' : $address_info['address'],
            'config_id' => $tuan_config_id,
            'longitude' => empty($address_info) ? 0 : $address_info['longitude'],
            'latitude' => empty($address_info) ? 0 : $address_info['latitude'],
            'tz_name' => $tuanzhang_info['name'],
            'tz_id' => $tuanzhang_info['id'],
            'tz_avatar' => $tuanzhang_info['avatar'],
            'tz_phone' => $tuanzhang_info['phone'],
            'add_time' => TIMESTAMP,
            'update_time' => TIMESTAMP,
            'address_area' => empty($address_info) ? '' : $address_info['area'],
            'address_street' => empty($address_info) ? '' : $address_info['street'],
            'address_community' => empty($address_info) ? '' : $address_info['community'],
        );
        $insert_arr['sn'] = $this->_genTuanSn();
        $tuan_id = $shequ_tuan_model->addItem($insert_arr);
        /** @var wx_small_appLogic $wxSmallApp */
        $wxSmallApp = Logic('wx_small_app');
        output_data(array(
            'wx_code' => $wxSmallApp->getQrHttp('pages/community/community',$tuan_id),
        ));
    }

    private function _genTuanSn()
    {
        $i = rand(0, 99999);
        do {
            if (99999 == $i) {
                $i = 0;
            }
            $i++;
            $sn = date('ymdHi') . str_pad($i, 5, '0', STR_PAD_LEFT);
            $condition = array('sn' => $sn);
            $row = Model("shequ_tuan")->where($condition)->find();
        } while ($row);
        return $sn;
    }



    public function get_address_listOp() {
        $member_id = intval($_POST['member_id']);
        /** @var shequ_addressModel $model_shequ_address */
        $model_shequ_address = Model('shequ_address');

        $address_list = $model_shequ_address->getList(array('member_id' => $member_id), 1000);
        $new_address_list = array();
        foreach ($address_list as $address) {
            $new_address_list[] = array(
                'label' => $address['area'] . ' ' .  $address['street'] . ' ' . $address['community']. ' ' . $address['address'],
                'value' => $address['id'],
            );
        }
        output_data($new_address_list);
    }



}
