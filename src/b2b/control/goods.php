<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/24 0024
 * Time: 下午 5:53
 */
defined('ByShopWWI') or exit('Access Invalid!');
class goodsControl extends BaseGoodsControl {
    public function __construct() {
        parent::__construct ();
        Language::read('store_goods_index');
    }

    public function indexOp() {
        $goods_commonid=intval($_GET['goods_commonid']);
        /** @var b2b_goodsModel $goods_model */
        $goods_model = Model('b2b_goods');
        if($goods_commonid<=0){
            $goods_id=intval($_GET['goods_id']);
            $skuInfo=$goods_model->getGoodsInfo(array('goods_id'=>$goods_id));
            $goods_commonid=intval($skuInfo['goods_commonid']);
        }
        $goodsinfo=$goods_model->getGoodsDetailbygoods_commonid($goods_commonid);


        $viewed_goods=Model('b2b_goods_common')->getGoodsCommonList($goodsinfo['gc_id'],'*','','rand()',15);
        foreach($viewed_goods as $k => $v){
            $upload_list = Model('upload')->getUploadList(array('item_id' => $v['goods_commonid'],'upload_type' => 7,'is_main' => 1));
            $viewed_goods[$k]['goods_image'] = UPLOAD_SITE_URL.'/b2b/goods/'.$upload_list[0]['file_name'];
        }


        tpl::output('viewed_goods',$viewed_goods);
        Tpl::output('goodsinfo',$goodsinfo);
        $nav_link_list[]=array('title'=>"搜索【".$_GET['keyword']."】结果");
        tpl::output('nav_link_list',$nav_link_list);
        //买家看了又看===>点击最多的
        $clickdata=Model('b2b_goods_common')->getGoodsCommonList($goodsinfo['gc_id'],'*','','rand()',3);
        foreach($clickdata as $k => $v){
            $upload_list = Model('upload')->getUploadList(array('item_id' => $v['goods_commonid'],'upload_type' => 7,'is_main' => 1));
            $clickdata[$k]['goods_image'] = UPLOAD_SITE_URL.'/b2b/goods/'.$upload_list[0]['file_name'];
        }

        $nav_link_list= $data=Model("b2b_category")->getGoodsClassNav($goodsinfo['gc_id']);
        tpl::output('nav_link_list',$nav_link_list);
        tpl::output('clickdata',$clickdata);
        Tpl::showpage('goods');
    }
}