<?php
/**
 * 地区
 *
 *
 *
 * @copyright  Copyright (c) 2007-2015 ShopNC Inc. (http://www.shopnc.net)
 * @license    http://www.shopnc.net
 * @link       http://www.shopnc.net
 * @since      File available since Release v1.1
 */



defined('ByShopWWI') or exit('Access Invalid!');
class areaControl extends mobileHomeControl{

    public function __construct() {
        parent::__construct();
    }

    public function indexOp() {
        $this->area_listOp();
    }

    /**
     * 地区列表
     */
    public function area_listOp() {
        //$_POST = array('area_id'=>17);
        $area_id = intval($_POST['area_id']);

        $model_area = Model('area');

        $condition = array();
        if($area_id > 0) {
            $condition['area_parent_id'] = $area_id;
        } else {
            $condition['area_deep'] = 1;
        }
        $area_list = $model_area->getAreaList($condition, 'area_id,area_name');
        output_data(array('area_list' => $area_list));
    }
    public function allOp() {
        //$_POST = array('area_id'=>17);
        /** @var areaModel $model_area */
        $model_area = Model('area');
        $area_list = $model_area->getAreaObject();
        output_data( $area_list);
    }

    public function wei_area_listOp() {
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
                    'area' => array(),
                );
            } elseif ($value['area_deep'] == 3) {
                $list[$father_data['area_parent_id']]['city'][$value['area_parent_id']]['area'][] = array(
                    'area_name' => $value['area_name'],
                    'area_id'   => $value['area_id'],
                );
            }
        }

        foreach ($list as &$value) {
            $value['city'] = array_values($value['city']);
        }
        output_data(array_values($list));
    }
    //获取地区列表  //社区接龙
    public function get_area_listOp() {
        //$area_src = $_POST['area_src'] == 'cache' ? 'cache' : 'db';
        $area_src = 'db';
        /** @var areaModel $area_model */
        $area_model = Model('shequ_area');
        $area_list = $area_model->getAreaArrayForJson($area_src);
        $new_area_list = array();
        foreach ($area_list[0] as $parent_area) {
            $single_area = array(
                'value' => $parent_area[0],
                'label' => $parent_area[1]
            );
            if (!empty($area_list[$parent_area[0]])) {
                $children = array();
                foreach ($area_list[$parent_area[0]] as $area) {
                    $son = array(
                        'value' => $area[0],
                        'label' => $area[1]
                    );
                    if (!empty($area_list[$area[0]])) {
                        $son['children'] = array();
                        foreach ($area_list[$area[0]] as $son_value) {
                            $son['children'][] = array(
                                'value' => $son_value[0],
                                'label' => $son_value[1]
                            );
                        }
                    }
                    $children[] = $son;
                }
                $single_area['children'] = $children;
            }
            $new_area_list[] = $single_area;
        }
        output_data($new_area_list);
    }
}
