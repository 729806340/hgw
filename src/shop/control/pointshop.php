<?php
/**
 * 积分中心
 * * @汉购网 (c) 2015-2018 ShopWWI Inc. (http://www.hangowa.com)
 * @license    http://www.shopwwi.c om
 * @link       交流群号：
 * @since      汉购网提供技术支持 授权请购买shopnc授权
 */


defined('ByShopWWI') or exit('Access Invalid!');
class pointshopControl extends BasePointShopControl {
    public function __construct() {
        parent::__construct();
        //读取语言包
        Language::read('home_pointprod,home_voucher');
    }

    public function indexOp(){
        //查询会员及其附属信息
        parent::pointshopMInfo();

        //开启代金券功能后查询推荐的热门代金券列表
        if (C('voucher_allow') == 1){
            $recommend_voucher = Model('voucher')->getRecommendTemplate(6);
            Tpl::output('recommend_voucher',$recommend_voucher);
        }
        //开启积分兑换功能后查询推荐的热门兑换商品列表
        if (C('pointprod_isuse') == 1){
            //热门积分兑换商品
            $recommend_pointsprod = Model('pointprod')->getRecommendPointProd(10);
            Tpl::output('recommend_pointsprod',$recommend_pointsprod);
        }
        //开启平台红包功能后查询推荐的红包列表
        if (C('redpacket_allow') == 1){
            $recommend_rpt = Model('redpacket')->getRecommendRpt(10);
            Tpl::output('recommend_rpt',$recommend_rpt);
        }
        
        //SEO
        Model('seo')->type('point')->show();
        //分类导航
        $nav_link = array(
                0=>array('title'=>L('homepage'),'link'=>SHOP_SITE_URL),
                1=>array('title'=>L('nc_pointprod'))
        );
        Tpl::output('nav_link_list', $nav_link);
        Tpl::showpage('pointprod');
    }
}
