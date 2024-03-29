<?php
/**
 * 默认展示页面
 *
 *
 *
 * @汉购网提供技术支持 授权请购买shopnc授权
 * @license    http://www.hangowa.com
 * @link       交流群号：
 */


defined('ByShopWWI') or exit('Access Invalid!');
class indexControl extends BaseHomeControl{
    public function indexOp(){
        Language::read('home_index_index');
        Tpl::output('index_sign','index');
        //特卖专区
        Language::read('member_groupbuy');
        //获取所有分类
        $b2b_goods_common = Model('b2b_goods_common');
        $category_list = $b2b_goods_common->getAllCategory();
         //     v($category_list);
        Tpl::output('category_list', $category_list);
        //获取分类及其商品
       // $model_groupbuy = Model('groupbuy');
        //$group_list = $model_groupbuy->getGroupbuyCommendedList(4);
        //Tpl::output('group_list', $group_list);
		//专题获取
        //$model_special = Model('cms_special');
       //$special_list = $model_special->getShopindexList($conition);
       // Tpl::output('special_list', $special_list);
        //限时折扣
        //$model_xianshi_goods = Model('p_xianshi_goods');
        //$xianshi_item = $model_xianshi_goods->getXianshiGoodsCommendList(6);
        //Tpl::output('xianshi_item', $xianshi_item);
		//直达楼层信息
		 if (C('shopwwi_lc') != '') {
            $lc_list = @unserialize(C('shopwwi_lc'));
        }
        Tpl::output('lc_list',is_array($lc_list) ? $lc_list : array());
		//首页推荐词链接
		 if (C('shopwwi_rc') != '') {
            $rc_list = @unserialize(C('shopwwi_rc'));
        }
        Tpl::output('rc_list',is_array($rc_list) ? $rc_list : array());
        //推荐品牌
        //$brand_r_list = Model('brand')->getBrandPassedList(array('brand_recommend'=>1) ,'brand_id,brand_name,brand_pic,brand_xbgpic,brand_tjstore', 0, 'brand_sort asc, brand_id desc', 4);
        //Tpl::output('brand_r',$brand_r_list);
		//评价信息
        //$goods_evaluate_info = Model('evaluate_goods')->getEvaluateGoodsList(8);
       // Tpl::output('goods_evaluate_info', $goods_evaluate_info);
        //板块信息
        /** @var web_configModel $model_web_config */
       // $model_web_config = Model('web_config');
       // $web_html = $model_web_config->getWebHtml('index');
        //自定义demo区块信息
        //$custom_web_html = $model_web_config->getCustomWebHtml();
       // Tpl::output('web_html',$web_html);
      //  Tpl::output('custom_web_html',$custom_web_html);
        $cart_goods_num=Model("b2b_cart")->getCartNum(isset($_SESSION['member_id']) ? 'db':'cookie',isset($_SESSION['member_id']) ? array("buyer_id"=>$_SESSION['member_id']):'');
        Tpl::output('cart_goods_num',$cart_goods_num);
        Model('seo')->type('index')->show();
        Tpl::showpage('index');
    }

    //json输出商品分类
    public function josn_classOp() {
        /**
         * 实例化商品分类模型
         */
        $model_class        = Model('goods_class');
        $goods_class        = $model_class->getGoodsClassListByParentId(intval($_GET['gc_id']));
        $array              = array();
        if(is_array($goods_class) and count($goods_class)>0) {
            foreach ($goods_class as $val) {
                $array[$val['gc_id']] = array('gc_id'=>$val['gc_id'],'gc_name'=>htmlspecialchars($val['gc_name']),'gc_parent_id'=>$val['gc_parent_id'],'commis_rate'=>$val['commis_rate'],'gc_sort'=>$val['gc_sort']);
            }
        }
        /**
         * 转码
         */
        if (strtoupper(CHARSET) == 'GBK'){
            $array = Language::getUTF8(array_values($array));//网站GBK使用编码时,转换为UTF-8,防止json输出汉字问题
        } else {
            $array = array_values($array);
        }
        echo $_GET['callback'].'('.json_encode($array).')';
    }

    /**
     * json输出地址数组 原data/resource/js/area_array.js
     */
    public function json_areaOp()
    {
        $_GET['src'] = $_GET['src'] != 'db' ? 'cache' : 'db';
        echo $_GET['callback'].'('.json_encode(Model('area')->getAreaArrayForJson($_GET['src'])).')';
        exit;
    }

    /**
     * 根据ID返回所有父级地区名称
     */
    public function json_area_showOp()
    {
        $area_info['text'] = Model('area')->getTopAreaName(intval($_GET['area_id']));
        echo $_GET['callback'].'('.json_encode($area_info).')';
    }

    //判断是否登录
    public function loginOp(){
        echo ($_SESSION['is_login'] == '1')? '1':'0';
    }

    /**
     * 头部最近浏览的商品
     */
    public function viewed_infoOp(){
        $info = array();
        if ($_SESSION['is_login'] == '1') {
            $member_id = $_SESSION['member_id'];
            $info['m_id'] = $member_id;
            if (C('voucher_allow') == 1) {
                $time_to = time();//当前日期
                $info['voucher'] = Model()->table('voucher')->where(array('voucher_owner_id'=> $member_id,'voucher_state'=> 1,
                'voucher_start_date'=> array('elt',$time_to),'voucher_end_date'=> array('egt',$time_to)))->count();
            }
            $time_to = strtotime(date('Y-m-d'));//当前日期
            $time_from = date('Y-m-d',($time_to-60*60*24*7));//7天前
            $info['consult'] = Model()->table('consult')->where(array('member_id'=> $member_id,
            'consult_reply_time'=> array(array('gt',strtotime($time_from)),array('lt',$time_to+60*60*24),'and')))->count();
        }
        $goods_list = Model('goods_browse')->getViewedGoodsList($_SESSION['member_id'],5);
        if(is_array($goods_list) && !empty($goods_list)) {
            $viewed_goods = array();
            foreach ($goods_list as $key => $val) {
                $goods_id = $val['goods_id'];
                $val['url'] = urlShop('goods', 'index', array('goods_id' => $goods_id));
                $val['goods_image'] = thumb($val, 60);
                $viewed_goods[$goods_id] = $val;
            }
            $info['viewed_goods'] = $viewed_goods;
        }
        if (strtoupper(CHARSET) == 'GBK'){
            $info = Language::getUTF8($info);
        }
        echo json_encode($info);
    }
    /**
     * 查询每月的周数组
     */
    public function getweekofmonthOp(){
        import('function.datehelper');
        $year = $_GET['y'];
        $month = $_GET['m'];
        $week_arr = getMonthWeekArr($year, $month);
        echo json_encode($week_arr);
        die;
    }
}
