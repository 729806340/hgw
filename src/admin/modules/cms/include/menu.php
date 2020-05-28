<?php
/**
 * 菜单
 *
 * @汉购网提供技术支持 授权请购买shopnc授权
 * @license    http://www.hangowa.com
 * @link       交流群号：
 */
defined('ByShopWWI') or exit('Access Invalid!');
$_menu['cms'] = array (
        'name' => $lang['nc_cms'],
        'child' => array(
                array(
                        'name' => $lang['nc_config'],
                        'child' => array(
                                'cms_manage' => $lang['nc_cms_manage'],
                                'cms_index' => $lang['nc_cms_index_manage'],
                                'cms_navigation' => $lang['nc_cms_navigation_manage'],
                                'cms_tag' => $lang['nc_cms_tag_manage'],
                                'cms_comment' => $lang['nc_cms_comment_manage']
                        )
                ),
                array(
                        'name' => '专题',
                        'child' => array(
                                'cms_special' => $lang['nc_cms_special_manage'],
                        		'cms_special_widgetpl' => '专题商品模板'
                        )
                ),
                array(
                        'name' => '文章',
                        'child' => array(
                                'cms_article_class' => '文章分类',
                                'cms_article' => '文章管理'
                        )
                ),
                array(
                        'name' => '画报',
                        'child' => array(
                                'cms_picture_class' => '画报分类',
                                'cms_picture' => '画报管理'
                        )
                )
));