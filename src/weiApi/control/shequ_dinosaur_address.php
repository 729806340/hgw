<?php
/**
 * 接龙地址
 *
 * @copyright  Copyright (c) 2007-2015 ShopNC Inc. (http://www.shopnc.net)
 * @license    http://www.shopnc.net
 * @link       http://www.shopnc.net
 * @since      File available since Release v1.1
 */
defined('ByShopWWI') or exit('Access Invalid!');

class shequ_dinosaur_addressControl extends mobileHomeControl {

    public function __construct(){
        parent::__construct();
    }

    //接龙地址 附近
    public function indexOp() {

        $lay_x = $_POST['lay_x'];
        $lay_y = $_POST['lay_y'];
        if (empty($lay_x) || empty($lay_y)) {
            output_error('参数错误');
        }
        $condition = array(
            'start_time' => array('lt', TIMESTAMP),
            'end_time' => array('gt', TIMESTAMP),
        );
        $memberId = $this->getMemberIdIfExists();
        /** @var shequ_tuanModel $model_shequ_tuan */
        $model_shequ_tuan = Model('shequ_tuan');
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
        $order = 'ACOS(SIN(('.$lay_x.' * 3.1415) / 180 ) *SIN((longitude * 3.1415) / 180 ) +COS(('.$lay_x.' * 3.1415) / 180 ) * COS((longitude * 3.1415) / 180 ) *COS(('.$lay_y.' * 3.1415) / 180 - (latitude * 3.1415) / 180 ) ) * 6380  asc';
        $tuan_list = $model_shequ_tuan->getListTuan($condition, 'tz_id,tz_name,tz_avatar,member_id,address_id,address,longitude,latitude,address_id', $order, 'address_id', $this->page);
        $address_ids = array_column($tuan_list, 'address_id');
        $address_list = $model_shequ_address->getList(array('id' => array('in', $address_ids)));
        $address_list = array_under_reset($address_list, 'id');
        $new_tuan_list = array();
        foreach ($tuan_list as $tuan_info) {
            $address_info = isset($address_list[$tuan_info['address_id']]) ? $address_list[$tuan_info['address_id']] :  array();
            $new_tuan_list[] = array(
                'tz_id' => $tuan_info['tz_id'],
                'tz_name' => $tuan_info['tz_name'],
                'longitude' => $tuan_info['longitude'],
                'latitude' => $tuan_info['latitude'],
                'address_id' => $tuan_info['address_id'],
                'distance' => $this->_distance($lay_x,$lay_y,$tuan_info['longitude'], $tuan_info['latitude']),
                'tz_avatar' =>  UPLOAD_SITE_URL . '/'. $tuan_info['tz_avatar'],
                'address' => $address_info ? '武汉市 '. $address_info['area']. ' '. $address_info['street']. ' '. $address_info['community'] . ' '. $address_info['address'] : $tuan_info['address'],
            );
        }
        output_data(array(
            'default_address' => !empty($default_address) ? $default_address : '',
            'tuan_list' => $new_tuan_list
        ));
    }

    /**
     *求两个已知经纬度之间的距离,单位为千米
     *@param lng1,lng2 经度
     *@param lat1,lat2 纬度
     *@return float 距离，单位千米
     **/
    private function _distance($lng1,$lat1,$lng2,$lat2)//根据经纬度计算距离
    {
        //将角度转为弧度
        $radLat1=deg2rad($lat1);
        $radLat2=deg2rad($lat2);
        $radLng1=deg2rad($lng1);
        $radLng2=deg2rad($lng2);
        $a=$radLat1-$radLat2;//两纬度之差,纬度<90
        $b=$radLng1-$radLng2;//两经度之差纬度<180
        $s=2*asin(sqrt(pow(sin($a/2),2)+cos($radLat1)*cos($radLat2)*pow(sin($b/2),2)))*6378.137;
        $distance = $s*1000;
        if ($distance > 1000) {
            return ncPriceFormat($distance/1000, 2) . '千米';
        } else {
            return ncPriceFormat($distance, 2) . '米';
        }
    }

    public function set_default_addressOp() {
        $memberId = $this->getMemberIdIfExists();
        $address_id = $_POST['address_id'];
        if (empty($memberId)) {
            output_error('请先登陆');
        }
        if ($address_id <= 0) {
            output_error('参数错误');
        }
        /** @var shequ_addressModel $model_shequ_address */
        $model_shequ_address = Model('shequ_address');
        $address_info = $model_shequ_address->getOne(array('id' => $address_id));
        if (empty($address_info)) {
            output_error('参数错误');
        }
        /** @var memberModel $member_model */
        $member_model = Model('member');
        $member_model->editMember(array('member_id' => $memberId), array('default_shequ_address_id' => $address_id));
        output_data('成功');
    }


}

