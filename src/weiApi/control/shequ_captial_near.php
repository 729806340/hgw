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

class shequ_captial_nearControl extends mobileHomeControl {

    protected $member_info = array();

    public function __construct(){
        parent::__construct();
        $member_id = $this->getMemberIdIfExists();
        if ($member_id) {
            /** @var memberModel $member_model */
            $member_model = Model('member');
            $this->member_info = $member_model->getMemberInfoByID($member_id);
            if ($this->member_info['tuanzhang_id']) {
                output_error('啊哈');
            }
        }
    }

    public function get_nearOp()
    {
        $lay_x = $_POST['lay_x'];
        $lay_y = $_POST['lay_y'];
        if (empty($lay_x) || empty($lay_y)) {
            output_error('参数错误');
        }
        $order = 'ACOS(SIN(('.$lay_x.' * 3.1415) / 180 ) *SIN((longitude * 3.1415) / 180 ) +COS(('.$lay_x.' * 3.1415) / 180 ) * COS((longitude * 3.1415) / 180 ) *COS(('.$lay_y.' * 3.1415) / 180 - (latitude * 3.1415) / 180 ) ) * 6380  asc';
        $condition = array(
            'state' => 1,
            'longitude' => array('gt', 0),
        );
        /** @var shequ_tuanzhangModel $shequ_tuanzhang_model */
        $shequ_tuanzhang_model = Model('shequ_tuanzhang');
        /** @var shequ_addressModel $shequ_address_model */
        $shequ_address_model = Model('shequ_address');
        $default_tuanzhang = array();
        if ($this->member_info['default_shequ_tuanzhang_id']) {
            $condition['id'] = array('neq', $this->member_info['default_shequ_tuanzhang_id']);
            $default_tuanzhang = $shequ_tuanzhang_model->getOne(array(
                'id' => $this->member_info['default_shequ_tuanzhang_id']
            ));
            $default_tuanzhang['address'] = $shequ_address_model->getOne(array('tuanzhang_id' => $this->member_info['default_shequ_tuanzhang_id']));
        }
        $tuanzhang_list = $shequ_tuanzhang_model->getList($condition, 3, $order);
        $tuanzhang_address_list = array();
        if (!empty($tuanzhang_list)) {
            $tuanzhang_address_list = $shequ_address_model->getList(array('tuanzhang_id' => array('in', array_column($tuanzhang_list, 'id'))));
            $tuanzhang_address_list = array_under_reset($tuanzhang_address_list, 'tuanzhang_id');
        }
        $tuanzhang_list_new = '';
        foreach ($tuanzhang_list as $k=>$v) {
            $address = $tuanzhang_address_list[$v['id']];
            $tuanzhang_list_new[] = array(
                'id' => $v['id'],
                'avatar' => $v['avatar'],
                'name' => $v['name'],
                'address' => $address['city_name'] . $address['area'] . $address['street'] . $address['community'] . $address['address'],
                'building' => $address['building'],
                'distance' => $this->_distance($lay_x, $lay_y, $address['longitude'], $address['latitude']),
            );
        }
        $new_default_tuanzhang = '';
        if (!empty($default_tuanzhang)) {
            $new_default_tuanzhang = array(
                'id' => $default_tuanzhang['id'],
                'avatar' => $default_tuanzhang['avatar'],
                'name' => $default_tuanzhang['name'],
                'address' => $default_tuanzhang['address']['city_name'] . $default_tuanzhang['address']['area'] . $default_tuanzhang['address']['street'] . $default_tuanzhang['address']['community'] . $default_tuanzhang['address']['address'],
                'building' => $default_tuanzhang['address']['building'],
                'distance' => $this->_distance($lay_x, $lay_y, $default_tuanzhang['address']['longitude'], $default_tuanzhang['address']['latitude']),
            );
        }
        $result = array(
            'default_tuanzhang' => $new_default_tuanzhang,
            'tuanzhang_list' => $tuanzhang_list_new
        );
        output_data_new($result);
    }

    public function change_tuanzhangOp() {

        $view_tuanzhang_id = intval($_POST['view_tuanzhang_id']);
        $lay_x = $_POST['lay_x'];
        $lay_y = $_POST['lay_y'];
        if (empty($this->member_info) || $view_tuanzhang_id <= 0) {
            output_error('参数错误');
        }
        if ($this->member_info['tuanzhang_id']) {
            output_error('异常');
        }
        if (!$this->member_info['default_shequ_tuanzhang_id']) {
            //output_error('参数错误');
        }
        $default_tuanzhang_id = $this->member_info['default_shequ_tuanzhang_id'];
        if ($view_tuanzhang_id == $default_tuanzhang_id) {
            output_error('异常');
        }
        /** @var shequ_tuanzhangModel $shequ_tuanzhang_model */
        $shequ_tuanzhang_model = Model('shequ_tuanzhang');
        $default_tuanzhang_info = $shequ_tuanzhang_model->getOne(array('id' => $default_tuanzhang_id));
        $view_tuanzhang_info = $shequ_tuanzhang_model->getOne(array('id' => $view_tuanzhang_id));
        if (empty($view_tuanzhang_info)) {
            output_error('异常');
        }
        /** @var shequ_addressModel $shequ_address_model */
        $shequ_address_model = Model('shequ_address');
        $default_tuanzhang_address = $shequ_address_model->getOne(array(
            'tuanzhang_id' => $default_tuanzhang_id,
        ));
        $view_tuanzhang_address = $shequ_address_model->getOne(array(
            'tuanzhang_id' => $view_tuanzhang_id,
        ));
        if (empty($view_tuanzhang_address)) {
            output_error('异常');
        }
        $new_tuanzhang_list = array(
            'view_tuanzhang' => array(
                'id' => $view_tuanzhang_info['id'],
                'avatar' => $view_tuanzhang_info['avatar'],
                'name' => $view_tuanzhang_info['name'],
                'address' => $view_tuanzhang_address['city_name'] . $view_tuanzhang_address['area'] . $view_tuanzhang_address['street'] . $view_tuanzhang_address['community'] . $view_tuanzhang_address['address'],
                'building' => $view_tuanzhang_address['building'],
                'distance' => $this->_distance($lay_x, $lay_y, $view_tuanzhang_address['longitude'], $view_tuanzhang_address['latitude']),
            ),
            'default_tuanzhang' =>  $this->member_info['default_shequ_tuanzhang_id'] > 0 ? array(
                'id' => $default_tuanzhang_info['id'],
                'avatar' => $default_tuanzhang_info['avatar'],
                'name' => $default_tuanzhang_info['name'],
                'address' => $default_tuanzhang_address['city_name'] . $default_tuanzhang_address['area'] . $default_tuanzhang_address['street'] . $default_tuanzhang_address['community'] . $default_tuanzhang_address['address'],
                'building' => $default_tuanzhang_address['building'],
                'distance' => $this->_distance($lay_x, $lay_y, $default_tuanzhang_address['longitude'], $default_tuanzhang_address['latitude']),
            ) : '',
        );
        output_data_new($new_tuanzhang_list);
    }


    /**
     *求两个已知经纬度之间的距离,单位为千米
     *@param lng1,lng2 经度
     *@param lat1,lat2 纬度
     *@return float 距离，单位千米
     **/
    private function _distance($lng1,$lat1,$lng2,$lat2)//根据经纬度计算距离
    {
        if (!$lng1 || !$lng2 || !$lat1 || !$lat2) {
            return '';
        }
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

}

