<?php
/**
 * 菜单
 *
 * @汉购网提供技术支持 授权请购买shopnc授权
 * @license    http://www.hangowa.com
 * @link       交流群号：
 */
defined('ByShopWWI') or exit('Access Invalid!');
$_menu['system'] = array(
    'name' => '平台',
    'child' => array(
        array(
            'name' => $lang['nc_config'],
            'child' => array(
                'setting' => $lang['nc_web_set'],
                'upload' => $lang['nc_upload_set'],
                'message' => '邮件设置',
                'taobao_api' => '淘宝接口',
                'admin' => '权限设置',
                'admin_log' => $lang['nc_admin_log'],
                'area' => '地区设置',
                'cache' => $lang['nc_admin_clear_cache'],
                'big_data' => '大数据设置',

            )
        ),
        array(
            'name' => $lang['nc_member'],
            'child' => array(

                'account' => $lang['nc_web_account_syn']
            )
        ),
        array(
            'name' => $lang['nc_website'],
            'child' => array(
                'article_class' => $lang['nc_article_class'],
                'article' => $lang['nc_article_manage'],
                'document' => $lang['nc_document'],
                'navigation' => $lang['nc_navigation'],
                'adv' => $lang['nc_adv_manage'],
                'rec_position' => $lang['nc_admin_res_position'],
            )
        ),
        array(
            'name' => '运维应用',
            'child' => array(
                'link' => '友情连接',
                'shopwwi' => '运维控件',
                'goods' => '商品组件',
                'db' => '数据库管理',
                'store' => '店铺组件',
                'member' => '会员组件',
                'sap' => 'SAP日志'

            )
        )
    )
);
