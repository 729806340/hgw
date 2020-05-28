<?php
/**
 * 公用接口
 *
 * @copyright  Copyright (c) 2007-2015 ShopNC Inc. (http://www.shopnc.net)
 * @license    http://www.shopnc.net
 * @link       http://www.shopnc.net
 * @since      File available since Release v1.1
 */
defined('ByShopWWI') or exit('Access Invalid!');

class shequ_commonControl extends mobileHomeControl {

    public function __construct(){
        parent::__construct();
    }

    public function get_area_listOp() {
        /** @var shequ_areaModel $model_shequ_area */
        $model_shequ_area = Model('shequ_area');
        $area_list = $model_shequ_area->getAreaListSort(array());
        $area_list = array_under_reset($area_list, 'area_id');
        $list = array();
        $ad_result = $this->area_adcode();
        foreach ($area_list as $value) {
            $ad_code = $ad_result[$value['area_name']];
            /* $list[] = array(
                 'area_name' => $value['area_name'],
                 'area_id'   => $value['area_id'],
                 'ad_code' => $ad_code ? $ad_code['ad_code'] : '',
                 'location' => $ad_code ? $ad_code['location'] : '',
             );*/
            if ($value['area_parent_id'] == 0) {
                $list[$value['area_id']] = array(
                    'area_name' => $value['area_name'],
                    'area_id'   => $value['area_id'],
                    'ad_code' => $ad_code ? $ad_code['ad_code'] : '',
                    'location' => $ad_code ? $ad_code['location'] : '',
                    'city' => array(),
                );
                continue;
            }
            $father_data = $area_list[$value['area_parent_id']];
            if ($value['area_deep'] == 2) {
                $list[$father_data['area_id']]['city'][$value['area_id']] = array(
                    'area_name' => $value['area_name'],
                    'area_id'   => $value['area_id'],
                    'ad_code' => $list[$father_data['area_id']]['ad_code'],
                    'location' => $list[$father_data['area_id']]['location'],
                    'area' => array(),
                );
            } elseif ($value['area_deep'] == 3) {
                $list[$father_data['area_parent_id']]['city'][$value['area_parent_id']]['area'][] = array(
                    'area_name' => $value['area_name'],
                    'area_id'   => $value['area_id'],
                    'ad_code' => $list[$father_data['area_parent_id']]['ad_code'],
                    'location' => $list[$father_data['area_parent_id']]['location'],
                );
            }
        }
        foreach ($list as &$value) {
            $value['city'] = array_values($value['city']);
        }
        output_data(array_values($list));
    }

    private function area_adcode() {
        $result = array(
            '东西湖区' => array(
                'ad_code' => 420112,
                'location' => '114.142483,30.622467'
            ),
            '新洲区' => array(
                'ad_code' => 420117,
                'location' => '114.801107,30.841544'
            ),
            '武昌区' => array(
                'ad_code' => 420106,
                'location' => '114.307344,30.546536'
            ),
            '汉南区' => array(
                'ad_code' => 420113,
                'location' => '114.084445,30.308856'
            ),
            '汉阳区' => array(
                'ad_code' => 420105,
                'location' => '114.265807,30.549326'
            ),
            '江夏区' => array(
                'ad_code' => 420115,
                'location' => '114.321551,30.375748'
            ),
            '江岸区' => array(
                'ad_code' => 420102,
                'location' => '114.30304,30.594911'
            ),
            '江汉区' => array(
                'ad_code' => 420103,
                'location' => '114.283109,30.578771'
            ),
            '洪山区' => array(
                'ad_code' => 420111,
                'location' => '114.400718,30.504259'
            ),
            '硚口区' => array(
                'ad_code' => 420104,
                'location' => '114.264568,30.57061'
            ),
            '蔡甸区' => array(
                'ad_code' => 420114,
                'location' => '114.029341,30.582186'
            ),
            '青山区' => array(
                'ad_code' => 420107,
                'location' => '114.385539,30.63963'
            ),
            '黄陂区' => array(
                'ad_code' => 420116,
                'location' => '114.374025,30.874155'
            ),
        );
        return $result;
    }
}