<?php
/**
 * cms专题
 *
 *
 * * @汉购网 (c) 2015-2018 ShopWWI Inc. (http://www.hangowa.com)
 * @license    http://www.shopwwi.c om
 * @link       交流群号：
 * @since      汉购网提供技术支持 授权请购买shopnc授权
 */



defined('ByShopWWI') or exit('Access Invalid!');
class specialControl extends BaseHomeControl{

    public function __construct() {
        parent::__construct();
        Tpl::output('index_sign','special');
    }

    public function indexOp() {
        $this->special_listOp();
    }

    /**
     * 专题列表
     */
    public function special_listOp() {
        $conition = array();
        $conition['special_state'] = 2;
        $model_special = Model('cms_special');
        $special_list = $model_special->getShopList($conition, 10, 'special_id desc');
        Tpl::output('show_page', $model_special->showpage(2));
        Tpl::output('special_list', $special_list);

        //分类导航
        $nav_link = array(
            0=>array(
                'title'=>Language::get('homepage'),
                'link'=>SHOP_SITE_URL
            ),
            1=>array(
                'title'=>'专题'
            )
        );
        Tpl::output('nav_link_list', $nav_link);
		$seo_param = array();
        $seo_param['name'] = '专题页面';
        $seo_param['key'] = '汉购网www.hangowa.com专业缔造未来 实力成就梦想 选择多用户商城选择汉购网';
        $seo_param['description'] = '汉购网www.hangowa.com专业缔造未来 实力成就梦想 选择多用户商城选择汉购网';
        Model('seo')->type('product')->param($seo_param)->show();

        Tpl::showpage('special_list');
    }

    /**
     * 专题详细页
     */
    public function special_detailOp() {
		$special_id = intval($_GET['special_id']);
        $model_special = Model('cms_special');
        $special_detail = $model_special->getonlyOne($_GET['special_id']);
        $detail = $special_detail['special_content'];//v($detail);
        $detail = str_replace("&lt;p&gt;
			&lt;br /&gt;
		&lt;/p&gt;", "", $detail);
        $detail = str_replace("&lt;br /&gt;", "", $detail);
        $special_file = getCMSSpecialHtml($special_id);
		$seo_param = array();
        $seo_param['name'] = $special_detail['special_title'];
        $seo_param['key'] = $special_detail['special_stitle'];
        $seo_param['description'] = $special_detail['special_stitle'];
		 Model('seo')->type('product')->param($seo_param)->show();
        if($special_file) {
            Tpl::output('special_file', $special_file);
            Tpl::output('index_sign', 'special');
            Tpl::showpage('special_detail');
        } else {
            showMessage('专题不存在', '', '', 'error');
        }

    }


    //ajax点赞接口
    public function ajaxlikeOp(){

        $data = rcache('congregate_times','congregate_times');
        if(empty($data)){
            wcache('congregate_times',array('1'),'congregate_times');
        } else {
            $data[0] = $data[0]+ 1;
            wcache('congregate_times',$data,'congregate_times');
        }
        $result = $data[0];
        echo  $result;
    }

    //ajax购买判断
    public function ajax_buyOp(){
        $goods_id = $_GET['goods_id'];
        //未登录重新登录
        if (!$_SESSION['member_id']){
            redirect(urlLogin('login', 'index', array('ref_url' => request_uri())));
        }

        $order_model = Model('order');
        $condition = array();
        $condition['buyer_id'] = $_SESSION['member_id'];
        $condition['goods_id'] = $goods_id;
        //没有订单的可以去购买
        $order_good_info = $order_model->getOrderGoodsInfo($condition);
        if(empty($order_good_info)){
            echo json_encode(callback(1,url('buy', 'buy_step1', '', false, '')));
        }

        //有订单已付款的不可购买
        $condition['order_state'] = array('in',array('20','30','40'));
        $order_good_info = $order_model->getOrderAndOrderGoodsList($condition);
        if(!empty($order_good_info)){
            echo  json_encode(callback(-1,'不可购买'));
        }

        //有订单待支付的去支付页面
        $condition = array();
        $condition['goods_id'] = $goods_id;
        $condition['buyer_id'] = $_SESSION['member_id'];
        $order_good_info = $order_model->getOrderGoodsList($condition);
        if($order_good_info){
             echo json_encode(callback(2,url('member_order', 'index', array('state_type' => 'state_new'), false, '')));
        }

    }

    //众筹活动
    public function congregateOp(){
        $goods_id1 = '102467';

        // 商品详细信息
        $model_goods = Model('goods');
        $goods_detail1 = $model_goods->getGoodsDetail($goods_id1);
        $goods_info1 = $goods_detail1['goods_info'];

        $model = Model('order');
        $where = array();
        $where['goods_id'] = $goods_id1;
        $where['order_state'] = array('in',array('10','20','30','40'));
        $list1 = $model->getOrderAndOrderGoodsCount($where);

        $where['goods_id'] = $goods_id1;
        $where['order_state'] = array('in',array('10','20','30','40'));
        $allPay = $model->getGoodsAllPriceJoin($where);
        if(empty($allPay)){
            $allPay = 0;
        }
        $count = $list1;


        $data = rcache('congregate_times','congregate_times');

        if(empty($data) or count($data) == 0){
            wcache('congregate_times',array('1'),'congregate_times');
            $goods_collect = 1;
        } else {
            $goods_collect = $data[0];
        }

        $endDate = "2017-04-10 00:00:00";
        $days =  round((strtotime($endDate) - time())/3600/24);

        $buy_limit_num1 = 100;
        $goods_info1['buy_limit_num'] = $buy_limit_num1;
        $goods_info1['sell_num'] = $count;

        $progress = round($count*100/$buy_limit_num1,2);
        $store_model = Model('store');
        $condition = array();
        $condition['store_id'] = 48;
        $store_info = $store_model->getStoreInfo($condition);
        Tpl::output('store_info', $store_info);
        Tpl::output('goods_collect', $goods_collect);
        Tpl::output('count', $count);
        Tpl::output('days', $days);
        Tpl::output('all_pay', $allPay);
        Tpl::output('progress', $progress);
        Tpl::output('goods1', $goods_info1);
        Tpl::showpage('congregate_show');
    }


    /**
     * pc专题详细页
     */
    public function showOp() {
        $special_id = intval($_GET['special_id']);
        $model_pc_special = Model('pc_special');
        //取专题说明信息
        $special_info = $model_pc_special->getPCSpecial($special_id);

        Tpl::output('special_info', $special_info );
        Tpl::output('special_id', $special_id);

        // 专题所有挂件列表
        $navigation = [];
        $special_item_list = $model_pc_special->getPCSpecialItemListByID($special_id);
        $special_item_list && $navigation = array_column($special_item_list,'navi_title','item_id');
        foreach ($navigation as $k=>$v){
            if(!$v) unset($navigation[$k]);
        }
        if(count($navigation) == 1 && current($navigation) == '') $navigation = [];

        Tpl::output('navigation', $navigation);
        Tpl::output('list', $special_item_list);
        Tpl::output('page', $model_pc_special->showpage(2));
        
        // seo信息
        $seo_param = array();
        $seo_param['name'] = $special_info['special_title'];
        $seo_param['key'] = $special_info['special_keywords'];
        $seo_param['description'] = $special_info['special_desc'];
        Model('seo')->type('article_content')->param($seo_param)->show();

        if($special_info) {
            Tpl::output('index_sign', 'special');
            Tpl::showpage('special_show');
        } else {
            showMessage('专题不存在', '', '', 'error');
        }
    
    }
}
