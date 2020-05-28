<?php
/**
 * 菜单
 *
 * @汉购网提供技术支持 授权请购买shopnc授权
 * @license    http://www.hangowa.com
 * @link       交流群号：
 */
defined('ByShopWWI') or exit('Access Invalid!');
$_menu['b2b'] = array (
        'name'=>'b2b',
        'child'=>array(
                array(
                        'name'=>'商品',
                        'child' => array(
								'goods_class' => '分类管理',
								'goods' => '商品管理',
								'supplier' => '供应商管理',
								'purchaser' => '采购商管理',
                        )
                ),
                array(
                        'name'=>'订单',
                        'child' => array(
								'order' => '商品订单',
                        )
                ),
                array(
                        'name'=>'设置',
                        'child' => array(
								'payment' => '支付方式',
                        )
                ),
                array(
                        'name'=>'运维',
                        'child' => array(
                            'sapb2b' => 'sapb2b日志',
                        )
                ),

        )
);