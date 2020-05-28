<?php
/**
 * 菜单
 *
 * @汉购网提供技术支持 授权请购买shopnc授权
 * @license    http://www.hangowa.com
 * @link       交流群号：
 */
defined('ByShopWWI') or exit('Access Invalid!');
$_menu['shequ'] = array(
    'name' => '社区团购',
    'child' => array(
        /*array(
            'name' => '设置',
            'child' => array(
                'tuan_list' => '团长管理',
                //'tuan_config' => '活动配置',
                'goods_class'=>'分类管理',
                //'tuan' => '社区接龙列表'
            )
        ),*/
        array(
            'name' => '活动',
            'child' => array(
                'tuan_list' => '团长列表',
                'goods_class'=>'分类列表',
                'tuan_config' => '活动列表',
                //'tuan' => '社区接龙列表'
            )
        ),
        array(
            'name' => '交易',
            'child' => array(
                'order' => '订单列表',
                'refund' => '退款列表',
                'return' => '退货列表',
            )
        ),
        array(
            'name' => '结算',
            'child' => array(
                'shequ_bill' => '结算列表'
            )
        )
    )
);
