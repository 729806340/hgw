<?php
/**
 * 商品列表
 *
 * * @汉购网 (c) 2015-2018 ShopWWI Inc. (http://www.hangowa.com)
 * @license    http://www.shopwwi.c om
 * @link       交流群号：
 * @since      汉购网提供技术支持 授权请购买shopnc授权
 */



defined('ByShopWWI') or exit('Access Invalid!');

class searchControl extends BaseHomeControl {
    //每页显示商品数
    const PAGESIZE = 5;
    //模型对象
    private $_model_search;

    public function indexOp() {
        $condition=array();
        //价格区间
        if(isset($_GET['lowerprice']) && isset($_GET['highprice'])){
          $condition['min_price']=array('egt',$_GET['lowerprice']);
          $condition['max_price']=array('elt',$_GET['highprice']);
         }
        //价格升降序
        if(isset($_GET['order'])){
           $orderby=$_GET['order']=='1' ? 'max_price desc':'min_price asc';
        }
        $b2b_goods_common=Model('b2b_goods_common');
        //商品cb_id请求
        if(isset($_GET['bc_id'])){
          //1.获取分类的商品
         $data['bc_pid']=$_GET['bc_id'];
          $nextclass=Model('b2b_category')->getChildClass($data);
          Tpl::output('nextclass',$nextclass);
          Tpl::output('gettype','bc_id');
          $nav_link_list= $data=Model("b2b_category")->getGoodsClassNav($_GET['bc_id']);
       }
        elseif(isset($_GET['keyword'])){
            //如果是关键字
            $nav_link_list[]=array('title'=>"搜索【".$_GET['keyword']."】结果");
            Tpl::output('gettype','keyword');
        }
//        $goodsinfo=$b2b_goods_common->getGoodsCommonList($condition,"*",self::PAGESIZE,$orderby);
        $goods_list = $b2b_goods_common->getGoodsListByGcid($_GET['bc_id'],$_GET['keyword'],$condition,self::PAGESIZE,$orderby);
        $data=Model('b2b_goods')->getGoodsDetailbygoods_commonid(90);
        Tpl::output('show_page', $b2b_goods_common->showpage(2));
        Tpl::output('goodsinfo',$goods_list);
        tpl::output('nav_link_list',$nav_link_list);
        Tpl::showpage('search');
    }

    /**
     * 获得猜你喜欢
     */
    public function get_guesslikeOp(){
        $goodslist = Model('goods_browse')->getGuessLikeGoods($_SESSION['member_id'], 20);
        if(!empty($goodslist)){
            Tpl::output('goodslist',$goodslist);
            Tpl::showpage('goods_guesslike','null_layout');
        }
    }

    /**
     * 商品分类推荐
     */
    public function get_gc_goods_recommendOp(){
        $rec_gc_id = intval($_GET['cate_id']);
        //只有最后一级才有推荐商品
        $class_info = Model('goods_class')->getGoodsClassListByParentId($rec_gc_id);
        if (!empty($class_info)) {
            return ;
        }
        $goods_list = array();
        if ($rec_gc_id > 0) {
            $rec_list = Model('goods_recommend')->getGoodsRecommendList(array('rec_gc_id'=>$rec_gc_id),'','','*','','rec_goods_id');
            if (!empty($rec_list)) {
                $goods_list = Model('goods')->getGoodsOnlineList(array('goods_id'=>array('in',array_keys($rec_list))));
                if (!empty($goods_list)) {
                    Tpl::output('goods_list',$goods_list);
                    Tpl::showpage('goods_recommend','null_layout');
                }
            }
        }
    }

    /**
     * 店铺搜索
     */
    public function storeOp()
    {
        /** @var storeModel $model_store */
        $model_store = Model("store");
        $condition = array() ;
        $condition['store_name|store_name_py'] = array('like', '%' . trim($_GET['keyword']) . '%');
        $field = "store_id, store_name,member_name,store_credit,store_desccredit,store_servicecredit,store_deliverycredit,store_sales,store_label,store_avatar";
        //处理排序
        $order = 'store_id desc';
        if (in_array($_GET['key'],array('1','2'))) {
            $sequence = $_GET['order'] == '1' ? 'asc' : 'desc';
            $order = str_replace(array('1','2'), array('store_sales','store_credit'), $_GET['key']);
            $order .= ' '.$sequence;
        }
        $store_list = $model_store->getStoreList($condition, 10, $order, $field);
        $store_ids = array_column($store_list, 'store_id');

        if( is_array($store_ids) && count($store_ids) > 0 )
        {
            //统计店铺商品数量
            /** @var goodsModel $model_goods */
            $model_goods = Model('goods');
            $count_condition = array();
            $count_condition['store_id'] = array('in', $store_ids) ;
            $field = "store_id, COUNT(goods_id) AS goods_num" ;
            $count_list = $model_goods->getGoodsOnlineList($count_condition, $field, null, 'store_id desc', 0, 'store_id') ;
            $rels = array_column($count_list, 'goods_num', 'store_id');

            foreach ($store_list as $key => $store_info) {
                //过滤商品为0的店铺
                if(!$rels[$store_info['store_id']]) {
                    unset($store_list[$key]);
                    continue;
                }

                $field = "goods_id,goods_name,goods_price,goods_promotion_price,store_id,goods_image";
                $goods_condition = array();
                $goods_condition['store_id'] = $store_info['store_id'];
                $goods_list = $model_goods->getGoodsOnlineList($goods_condition, $field, 5);

                $store_avatar = $store_info['store_avatar']
                    ? UPLOAD_SITE_URL.'/'.ATTACH_STORE.'/'.$store_info['store_avatar']
                    : UPLOAD_SITE_URL.'/'.ATTACH_COMMON.DS.C('default_store_avatar');

                $store_list[$key]['goods_num'] = $rels[$store_info['store_id']] ;
                $store_list[$key]['goods_list'] = $goods_list;
                $store_list[$key]['store_avatar'] = $store_avatar;
            }
        }

        $viewed_goods = Model('goods_browse')->getViewedGoodsList($_SESSION['member_id'],20);
        Tpl::output('viewed_goods',$viewed_goods);

        Tpl::output('show_keyword', $_GET['keyword']);
        Tpl::output('store_list', $store_list);
        Tpl::showpage('search.store');
    }
}