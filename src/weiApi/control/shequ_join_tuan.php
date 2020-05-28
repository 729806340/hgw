<?php
/**
 * 申请加入团
 *
 * @copyright  Copyright (c) 2007-2015 ShopNC Inc. (http://www.shopnc.net)
 * @license    http://www.shopnc.net
 * @link       http://www.shopnc.net
 * @since      File available since Release v1.1
 */
defined('ByShopWWI') or exit('Access Invalid!');

class shequ_join_tuanControl extends mobileMemberControl {

    public function __construct(){
        parent::__construct();
        //有再审核及审核中的不让提交
        /** @var shequ_tuanzhangModel $shequ_tuanzhang_model */
        $shequ_tuanzhang_model = Model('shequ_tuanzhang');
        $shequ_tuanzhang_info = $shequ_tuanzhang_model->getOne(array('member_id' => $this->member_info['member_id']));
        // 编辑后续处理
        if ($shequ_tuanzhang_info) {
            output_error('…………');
        }
    }

    public function joinOp() {

        if (!$_POST['name']) {
            output_error('请填写姓名！');
        }

        if (!preg_match('/^1[0-9]{10}$/', $_POST['phone'])) {
            output_error('手机号码不正确');
        }

        if ($_POST['city_name'] == '武汉') {
            $_POST['province_id'] = 17;
            $_POST['province_name'] = '湖北';
            $_POST['city_id'] = 258;
            $community_id = intval($_POST['area_id']);
            if (empty($_POST['area_id'])) {
                output_error('请选择区域');
            }
            if (empty($_POST['longitude']) || empty($_POST['latitude']) || empty($community_id)) {
                output_error('参数不正确');
            }
            if (empty($_POST['address'])) {
                output_error('请填写所在地址');
            }

            /** @var shequ_areaModel $shequ_area_model */
            $shequ_area_model = Model('shequ_area');
            $area_community_info = $shequ_area_model->getAreaInfo(array('area_deep' => 3, 'area_id' => $community_id));
            if (empty($area_community_info)) {
                output_error('异常……');
            }
            $_POST['community_id'] = $community_id;
            $_POST['community'] = $area_community_info['area_name'];
            $street_info = $shequ_area_model->getAreaInfo(array('area_deep' => 2, 'area_id' => $area_community_info['area_parent_id']));
            $_POST['street_id'] = $street_info['area_id'];
            $_POST['street'] = $street_info['area_name'];
            $area_info = $shequ_area_model->getAreaInfo(array('area_parent_id' => 0, 'area_id' => $street_info['area_parent_id']));
            $_POST['area'] = $area_info['area_name'];
            $_POST['area_id'] = $area_info['area_id'];
        } else {
            $_POST['city_name'] = '非武汉';
            $_POST['city_id'] = 0;
            $_POST['area'] = '';
            $_POST['area_id'] = 0;
            $_POST['street'] = '';
            $_POST['street_id'] = 0;
            $_POST['community_id'] = 0;
            $_POST['community'] = '';
            $_POST['province_id'] = 0;
            $_POST['province_name'] = '';
        }

        /** @var shequ_tuanzhangModel $model_tuanzhang */
        $model_tuanzhang = Model('shequ_tuanzhang');
        $model_tuanzhang->beginTransaction();
        $tuanzhang_data = array();
        $tuanzhang_data['member_id'] = $this->member_info['member_id'];
        $tuanzhang_data['name'] = $_POST['name'];
        $tuanzhang_data['nick_name'] = $this->member_info['wx_user_avatar'];
        $tuanzhang_data['phone'] = $_POST['phone'];
        $tuanzhang_data['avatar']= $this->member_info['wx_user_avatar'];
        $tuanzhang_data['add_time'] = time();
        $tuanzhang_data['longitude'] = $_POST['longitude'];
        $tuanzhang_data['latitude'] = $_POST['latitude'];
        $tuanzhang_data['area'] = $_POST['area'];
        $tuanzhang_id = $model_tuanzhang->insert($tuanzhang_data);
        if (!$tuanzhang_id) {
            $model_tuanzhang->rollback();
            output_error('保存失败！');
        }
        $data = array();
        $data['member_id'] = $this->member_info['member_id'];
        $data['city_id'] = $_POST['city_id'];
        $data['city_name'] = $_POST['city_name'];
        $data['area'] = $_POST['area'];
        $data['area_id'] = $_POST['area_id'];
        $data['street'] = $_POST['street'];
        $data['street_id'] = $_POST['street_id'];
        $data['community'] = $_POST['community'];
        $data['community_id'] = $_POST['community_id'];
        $data['province_id'] = $_POST['province_id'];
        $data['province_name'] = $_POST['province_name'];
        $data['address'] = $_POST['address'];
        $data['longitude'] = $_POST['longitude'];
        $data['latitude'] = $_POST['latitude'];
        $data['name'] = $_POST['name'];
        $data['phone'] = $_POST['phone'];
        $data['building'] = $_POST['building'];
        $data['is_default'] = 1;
        $data['tuanzhang_id'] = $tuanzhang_id;
        $data['add_time'] = time();
        /** @var shequ_addressModel $model_shequ_address */
        $model_shequ_address = Model('shequ_address');
        $res = $model_shequ_address->insert($data);
        if (!$res) {
            $model_tuanzhang->rollback();
            output_error('异常');
        }
        $model_tuanzhang->commit();
        output_data('成功');
    }


    public function get_city_listOp() {
        /** @var areaModel $model_area */
        $model_area = Model('area');
        $area_list = $model_area->getAreaList(array());
        $area_list = array_under_reset($area_list, 'area_id');
        $list = array();
        foreach ($area_list as $value) {
            if ($value['area_parent_id'] == 0) {
                $list[$value['area_id']] = array(
                    'area_name' => $value['area_name'],
                    'area_id'   => $value['area_id'],
                    'city' => array(),
                );
                continue;
            }
            $father_data = $area_list[$value['area_parent_id']];
            if ($value['area_deep'] == 2) {
                $list[$father_data['area_id']]['city'][$value['area_id']] = array(
                    'area_name' => $value['area_name'],
                    'area_id'   => $value['area_id'],
                    //'area' => array(),
                );
            }
        }
        foreach ($list as &$value) {
            $value['city'] = array_values($value['city']);
        }
        output_data(array_values($list));
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

