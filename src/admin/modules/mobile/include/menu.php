<?php
/**
 * 菜单
 *
 * @汉购网提供技术支持 授权请购买shopnc授权
 * @license    http://www.hangowa.com
 * @link       交流群号：
 */
defined('ByShopWWI') or exit('Access Invalid!');
$_menu['mobile'] = array (
        'name'=>$lang['nc_mobile'],
        'child'=>array(
                array(
                        'name'=>'设置',
                        'child' => array(
						        'mb_setting' => '手机端设置',
                                'mb_special' => '模板设置',
                                'mb_category' => $lang['nc_mobile_catepic'],
                                'mb_app' => '应用安装',
                                'mb_feedback' => $lang['nc_mobile_feedback'],
                                'mb_payment' => '手机支付',
                                'mb_wx' => '微信二维码',
								'mb_connect' => '第三方登入',
                        )
                )
        )
);