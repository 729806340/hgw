<?php
/**
 * 系统文章
 *
 *
 *
 * * @汉购网 (c) 2015-2018 ShopWWI Inc. (http://www.hangowa.com)
 * @license    http://www.shopwwi.c om
 * @link       交流群号：
 * @since      汉购网提供技术支持 授权请购买shopnc授权
 */



defined('ByShopWWI') or exit('Access Invalid!');

class documentControl extends BaseHomeControl {
    public function indexOp(){
        $lang   = Language::getLangContent();
        if($_GET['code'] == ''){
            showMessage($lang['para_error'],'','html','error');//'缺少参数:文章标识'
        }
        $model_doc  = Model('document');
        $doc    = $model_doc->getOneByCode($_GET['code']);
        Tpl::output('doc',$doc);
        /**
         * 分类导航
         */
        $nav_link = array(
            array(
                'title'=>$lang['homepage'],
                'link'=>SHOP_SITE_URL
            ),
            array(
                'title'=>$doc['doc_title']
            )
        );
        Tpl::output('nav_link_list',$nav_link);
        Tpl::showpage('document.index');
    }
}
