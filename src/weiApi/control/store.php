<?php
/**
 * 店铺
 *
 *by wansyb QQ群：111731672
 *你正在使用的是由网店 运 维提供S2.0系统！保障你的网络安全！ 购买授权请前往shopnc
 */


defined('ByShopWWI') or exit('Access Invalid!');

class storeControl extends mobileHomeControl
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 店铺首页
     */
    public function indexOp()
    {
        $store_id = (int)$_POST['store_id'];
        if ($store_id <= 0) {
            output_error('参数错误');
        }
        $store_online_info = Model('store')->getStoreOnlineInfoByID($store_id);
        if (empty($store_online_info)) {
            output_error('店铺不存在或未开启');
        }

        $store_info = array();
        $store_info['store_id'] = $store_online_info['store_id'];
        $store_info['store_name'] = $store_online_info['store_name'];
        $store_info['member_id'] = $store_online_info['member_id'];
        $store_info['member_id'] = $store_online_info['member_id'];
        $store_info['store_slogan'] = $store_online_info['store_slogan'];
        $store_info['store_banner'] = getStoreLogo($store_online_info['store_banner']);

        // 店铺头像
        $store_info['store_avatar'] = getStoreLogo($store_online_info['store_avatar']);

        // 商品数
        $store_info['goods_count'] = (int)$store_online_info['goods_count'];

        // 店铺被收藏次数
        $store_info['store_collect'] = (int)$store_online_info['store_collect'];

        // 如果已登录 判断该店铺是否已被收藏
        if ($memberId = $this->getMemberIdIfExists()) {
            $c = (int)Model('favorites')->getStoreFavoritesCountByStoreId($store_id, $memberId);
            $store_info['is_favorate'] = $c > 0;
        } else {
            $store_info['is_favorate'] = false;
        }

        // 是否官方店铺
        $store_info['is_own_shop'] = (bool)$store_online_info['is_own_shop'];

        // 动态评分
        if ($store_info['is_own_shop']) {
            $store_info['store_credit'] = array(
                'store_desccredit'     => array(
                    'text'          => '描述',
                    'credit'        => 5,
                    'percent'       => '----',
                    'percent_class' => 'equal',
                    'percent_text'  => '平',
                ),
                'store_servicecredit'  => array(
                    'text'          => '服务',
                    'credit'        => 5,
                    'percent'       => '----',
                    'percent_class' => 'equal',
                    'percent_text'  => '平',
                ),
                'store_deliverycredit' => array(
                    'text'          => '物流',
                    'credit'        => 5,
                    'percent'       => '----',
                    'percent_class' => 'equal',
                    'percent_text'  => '平',
                ),
            );
        } else {
            $storeCredit = array();
            $percentClassTextMap = array(
                'equal' => '平',
                'high'  => '高',
                'low'   => '低',
            );
            foreach ((array)$store_online_info['store_credit'] as $k => $v) {
                $v['percent_text'] = $percentClassTextMap[$v['percent_class']];
                $storeCredit[$k] = $v;
            }
            $store_info['store_credit'] = $storeCredit;
        }

        foreach ($store_info['store_credit'] as $k => $v) {
            $store_info[$k] = $v['credit'];
        }

        // 轮播
        /*        $store_info['mb_sliders'] = array();
                $mbSliders = @unserialize($store_online_info['mb_sliders']);
                if ($mbSliders) {
                    foreach ((array)$mbSliders as $s) {
                        if ($s['img']) {
                            $s['imgUrl'] = UPLOAD_SITE_URL . DS . ATTACH_STORE . DS . $s['img'];
                            $store_info['mb_sliders'][] = $s;
                        }
                    }
                }*/

        //商品列表
        $where = array();
        $where['store_id'] = $store_id;
        $where['is_book'] = 0;// 默认不显示预订商品
        $model_goods = Model('goods');
        $goods_fields = $this->getGoodsFields();
        //畅销列表
        $goods_sales = $model_goods->getGoodsListByColorDistinct($where, $goods_fields, 'goods_salenum desc', 0, 6);
        /** @var goodsLogic $logic_goods */
        $logic_goods = Logic('goods');
        $goods_sales = $logic_goods->deal_goods_list($goods_sales);

        //新品列表
        //$goods_news = $model_goods->getGoodsListByColorDistinct($where, $goods_fields, 'goods_id desc', 0, 4);

        /** @var voucherModel $voucher_model */
        $voucher_model = Model('voucher');
        $param = array();
        $param['voucher_t_store_id'] = $store_id;
        $param['voucher_t_state'] = 1;
        $param['voucher_t_end_date'] = array('gt', time());
        $gettype_array = $voucher_model->getVoucherGettypeArray();
        $param['voucher_t_gettype'] = $gettype_array['free']['sign'];
        $voucher_list = $voucher_model->getVoucherTemplateList($param, '*', 0, 2);
        if(!empty($voucher_list)){
            foreach($voucher_list as $key=>$value){
                $voucher_list[$key]['voucher_t_end_date_text'] = date('Y-m-d',$value['voucher_t_end_date']);
            }
        }
        output_data(array(
            'store_info'  => $store_info,
            'voucher_list' => $voucher_list,
            'voucher_more' => $voucher_model->gettotalpage() > 1 ? true : false,
            'goods_sales' => $goods_sales,
            'gc_list'    => $this->get_store_goods_class($store_id)
        ));
    }

    public function detailOp()
    {
        $store_id = (int)$_REQUEST['store_id'];
        if ($store_id <= 0) {
            output_error('参数错误');
        }
        $store_online_info = Model('store')->getStoreOnlineInfoByID($store_id);
        if (empty($store_online_info)) {
            output_error('店铺不存在或未开启');
        }
        $store_info = $store_online_info;
        //开店时间
        $store_info['store_time_text'] = $store_info['store_time'] ? @date('Y-m-d', $store_info['store_time']) : '';
        // 店铺头像
        $store_info['store_avatar'] = getStoreLogo($store_online_info['store_avatar']);
        //商品数
        $store_info['goods_count'] = (int)$store_online_info['goods_count'];
        //店铺被收藏次数
        $store_info['store_collect'] = (int)$store_online_info['store_collect'];
        //店铺所属分类
        $store_class = Model('store_class')->getStoreClassInfo(array('sc_id' => $store_info['sc_id']));
        $store_info['sc_name'] = $store_class['sc_name'];
        //如果已登录 判断该店铺是否已被收藏
        if ($member_id = $this->getMemberIdIfExists()) {
            $c = (int)Model('favorites')->getStoreFavoritesCountByStoreId($store_id, $member_id);
            $store_info['is_favorate'] = $c > 0 ? true : false;
        } else {
            $store_info['is_favorate'] = false;
        }
        // 是否官方店铺
        $store_info['is_own_shop'] = (bool)$store_online_info['is_own_shop'];
        unset($store_info['store_credit'], $store_info['store_label'], $store_info['member_name'], $store_info['seller_name'], $store_info['manage_type_new'], $store_info['manage_type_validate'], $store_info['send_sap'], $store_info['edit_sap'], $store_info['tax_input'], $store_info['tax_output']);
        output_data($store_info);
    }

    /**
     * 店铺商品分类
     */
    public function goods_classOp()
    {
        $store_id = (int)$_POST['store_id'];
        if ($store_id <= 0) {
            output_error('参数错误');
        }
        $store_online_info = Model('store')->getStoreOnlineInfoByID($store_id);
        if (empty($store_online_info)) {
            output_error('店铺不存在或未开启');
        }

        $store_info = array();
        $store_info['store_id'] = $store_online_info['store_id'];
        $store_info['store_name'] = $store_online_info['store_name'];

        $store_goods_class = Model('store_goods_class')->getStoreGoodsClassPlainList($store_id);

        output_data(array(
            'store_info'        => $store_info,
            'store_goods_class' => $store_goods_class
        ));
    }

    /**
     * 店铺商品
     */
    public function goodsOp()
    {
        $param = $_POST;

        $store_id = (int)$param['store_id'];
        if ($store_id <= 0) {
            output_error('参数错误');
        }
        $stc_id = (int)$param['stc_id'];
        $keyword = trim((string)$param['keyword']);

        $condition = array();
        $condition['store_id'] = $store_id;

        // 默认不显示预订商品
        $condition['is_book'] = 0;

        if ($stc_id > 0) {
            $condition['goods_stcids'] = array('like', '%,' . $stc_id . ',%');
        }
        //促销类型
        if ($param['prom_type']) {
            switch ($param['prom_type']) {
                case 'xianshi':
                    $condition['goods_promotion_type'] = 2;
                    break;
                case 'groupbuy':
                    $condition['goods_promotion_type'] = 1;
                    break;
            }
        }
        if ($keyword != '') {
            $condition['goods_name'] = array('like', '%' . $keyword . '%');
        }
        $price_from = preg_match('/^[\d.]{1,20}$/', $param['price_from']) ? $param['price_from'] : null;
        $price_to = preg_match('/^[\d.]{1,20}$/', $param['price_to']) ? $param['price_to'] : null;
        if ($price_from && $price_from) {
            $condition['goods_promotion_price'] = array('between', "{$price_from},{$price_to}");
        } elseif ($price_from) {
            $condition['goods_promotion_price'] = array('egt', $price_from);
        } elseif ($price_to) {
            $condition['goods_promotion_price'] = array('elt', $price_to);
        }

        // 排序
        $order = (int)$param['order'] == 1 ? 'asc' : 'desc';
        switch (trim($param['key'])) {
            case '1':
                $order = 'goods_id ' . $order;
                break;
            case '2':
                $order = 'goods_promotion_price ' . $order;
                break;
            case '3':
                $order = 'goods_salenum ' . $order;
                break;
            case '4':
                $order = 'goods_collect ' . $order;
                break;
            case '5':
                $order = 'goods_click ' . $order;
                break;
            default:
                $order = 'goods_id desc';
                break;
        }

        $model_goods = Model('goods');

        $goods_fields = $this->getGoodsFields();
        $goods_list = $model_goods->getGoodsListByColorDistinct($condition, $goods_fields, $order, $this->page);
        $page_count = $model_goods->gettotalpage();

        $goods_list = $this->_goods_list_extend($goods_list);

        output_data(array(
            'goods_list_count' => count($goods_list),
            'goods_list'       => $goods_list,
        ), mobile_page($page_count));
    }

    private function getGoodsFields()
    {
        return implode(',', array(
            'goods_id',
            'goods_commonid',
            'store_id',
            'store_name',
            'goods_name',
            'goods_price',
            'goods_promotion_price',
            'goods_promotion_type',
            'goods_marketprice',
            'goods_image',
            'goods_salenum',
            'evaluation_good_star',
            'evaluation_count',
            'is_virtual',
            'is_presell',
            'is_fcode',
            'have_gift',
            'goods_addtime',
        ));
    }

    /**
     * 处理商品列表(团购、限时折扣、商品图片)
     */
    private function _goods_list_extend($goods_list)
    {
        //获取商品列表编号数组
        $goodsid_array = array();
        foreach ($goods_list as $key => $value) {
            $goodsid_array[] = $value['goods_id'];
        }

        $sole_array = Model('p_sole')->getSoleGoodsList(array('goods_id' => array('in', $goodsid_array)));
        $sole_array = array_under_reset($sole_array, 'goods_id');

        foreach ($goods_list as $key => $value) {
            $goods_list[$key]['sole_flag'] = false;
            $goods_list[$key]['group_flag'] = false;
            $goods_list[$key]['xianshi_flag'] = false;
            if (!empty($sole_array[$value['goods_id']])) {
                $goods_list[$key]['goods_price'] = $sole_array[$value['goods_id']]['sole_price'];
                $goods_list[$key]['sole_flag'] = true;
            } else {
                $goods_list[$key]['goods_price'] = $value['goods_promotion_price'];
                switch ($value['goods_promotion_type']) {
                    case 1:
                        $goods_list[$key]['group_flag'] = true;
                        break;
                    case 2:
                        $goods_list[$key]['xianshi_flag'] = true;
                        break;
                }

            }

            //商品图片url
            $goods_list[$key]['goods_image_url'] = cthumb($value['goods_image'], 360, $value['store_id']);

            unset($goods_list[$key]['goods_promotion_type']);
            unset($goods_list[$key]['goods_promotion_price']);
            unset($goods_list[$key]['goods_commonid']);
            unset($goods_list[$key]['nc_distinct']);
        }

        return $goods_list;
    }

    /**
     * 商品评价
     */
    public function store_creditOp()
    {
        $store_id = intval($_GET['store_id']);
        if ($store_id <= 0) {
            output_error('参数错误');
        }
        $store_online_info = Model('store')->getStoreOnlineInfoByID($store_id);
        if (empty($store_online_info)) {
            output_error('店铺不存在或未开启');
        }

        output_data(array('store_credit' => $store_online_info['store_credit']));
    }

    /**
     * 店铺商品排行
     */
    public function store_goods_rankOp()
    {
        $store_id = (int)$_REQUEST['store_id'];
        if ($store_id <= 0) {
            output_data(array());
        }
        $ordertype = ($t = trim($_REQUEST['ordertype'])) ? $t : 'salenumdesc';
        $show_num = ($t = intval($_REQUEST['num'])) > 0 ? $t : 10;

        $where = array();
        $where['store_id'] = $store_id;
        // 默认不显示预订商品
        $where['is_book'] = 0;
        // 排序
        switch ($ordertype) {
            case 'salenumdesc':
                $order = 'goods_salenum desc';
                break;
            case 'salenumasc':
                $order = 'goods_salenum asc';
                break;
            case 'collectdesc':
                $order = 'goods_collect desc';
                break;
            case 'collectasc':
                $order = 'goods_collect asc';
                break;
            case 'clickdesc':
                $order = 'goods_click desc';
                break;
            case 'clickasc':
                $order = 'goods_click asc';
                break;
        }
        if ($order) {
            $order .= ',goods_id desc';
        } else {
            $order = 'goods_id desc';
        }
        $model_goods = Model('goods');
        $goods_fields = $this->getGoodsFields();
        $goods_list = $model_goods->getGoodsListByColorDistinct($where, $goods_fields, $order, 0, $show_num);
        $goods_list = $this->_goods_list_extend($goods_list);
        output_data(array('goods_list' => $goods_list));
    }

    /**
     * 店铺商品上新
     */
    public function store_new_goodsOp()
    {
        $store_id = (int)$_POST['store_id'];
        if ($store_id <= 0) {
            output_error('参数错误');
        }
        $show_day = 100;
        $where = array();
        $where['store_id'] = $store_id;
        $where['is_book'] = 0;//默认不显示预订商品
        $stime = strtotime(date('Y-m-d', time() - 86400 * $show_day));
        $etime = $stime + 86400 * ($show_day + 1);
        $where['goods_addtime'] = array('between', array($stime, $etime));
        $order = 'goods_addtime desc, goods_id desc';
        /** @var goodsModel $model_goods */
        $model_goods = Model('goods');
        $goods_fields = $this->getGoodsFields();
        $goods_list = $model_goods->getGoodsListByColorDistinct($where, $goods_fields, $order, $this->page);
        $page_count = $model_goods->gettotalpage();
        $new_goods_list = array();
        if ($goods_list) {
            $goods_list = $this->_goods_list_extend($goods_list);
            foreach ($goods_list as $k => $v) {
                $goods_addtime = $v['goods_addtime'] ? @date('m-d', $v['goods_addtime']) : '';
                if (!$goods_addtime) {
                    continue;
                }
                $new_goods_list[$goods_addtime]['name'] = @date('m月d日', $v['goods_addtime']);
                $new_goods_list[$goods_addtime]['list'][] = $v;
            }
        }
        output_data(array('goods_list' => $new_goods_list), mobile_page($page_count));
    }

    /**
     * 店铺简介
     */
    public function store_introOp()
    {
        $store_id = (int)$_REQUEST['store_id'];
        if ($store_id <= 0) {
            output_error('参数错误');
        }
        $store_online_info = Model('store')->getStoreOnlineInfoByID($store_id);
        if (empty($store_online_info)) {
            output_error('店铺不存在或未开启');
        }
        $store_info = $store_online_info;
        //开店时间
        $store_info['store_time_text'] = $store_info['store_time'] ? @date('Y-m-d', $store_info['store_time']) : '';
        // 店铺头像
        $store_info['store_avatar'] = $store_online_info['store_avatar']
            ? UPLOAD_SITE_URL . '/' . ATTACH_STORE . '/' . $store_online_info['store_avatar']
            : UPLOAD_SITE_URL . '/' . ATTACH_COMMON . DS . C('default_store_avatar');
        //商品数
        $store_info['goods_count'] = (int)$store_online_info['goods_count'];
        //店铺被收藏次数
        $store_info['store_collect'] = (int)$store_online_info['store_collect'];
        //店铺所属分类
        $store_class = Model('store_class')->getStoreClassInfo(array('sc_id' => $store_info['sc_id']));
        $store_info['sc_name'] = $store_class['sc_name'];
        //如果已登录 判断该店铺是否已被收藏
        if ($member_id = $this->getMemberIdIfExists()) {
            $c = (int)Model('favorites')->getStoreFavoritesCountByStoreId($store_id, $member_id);
            $store_info['is_favorate'] = $c > 0 ? true : false;
        } else {
            $store_info['is_favorate'] = false;
        }
        // 是否官方店铺
        $store_info['is_own_shop'] = (bool)$store_online_info['is_own_shop'];
        // 页头背景图
        $store_info['mb_title_img'] = $store_online_info['mb_title_img'] ? UPLOAD_SITE_URL . '/' . ATTACH_STORE . '/' . $store_online_info['mb_title_img'] : '';
        // 轮播
        $store_info['mb_sliders'] = array();
        $mbSliders = @unserialize($store_online_info['mb_sliders']);
        if ($mbSliders) {
            foreach ((array)$mbSliders as $s) {
                if ($s['img']) {
                    $s['imgUrl'] = UPLOAD_SITE_URL . DS . ATTACH_STORE . DS . $s['img'];
                    $store_info['mb_sliders'][] = $s;
                }
            }
        }
        output_data(array('store_info' => $store_info));
    }

    /**
     * 店铺活动
     */
    public function store_promotionOp()
    {
        $xianshi_array = Model('p_xianshi');
        $promotion['promotion'] = $condition = array();
        $condition['store_id'] = $_POST["store_id"];
        $xianshi = $xianshi_array->getXianshiList($condition);
        if (!empty($xianshi)) {
            foreach ($xianshi as $key => $value) {
                $xianshi[$key]['start_time_text'] = date('Y-m-d', $value['start_time']);
                $xianshi[$key]['end_time_text'] = date('Y-m-d', $value['end_time']);
            }
            $promotion['promotion']['xianshi'] = $xianshi;
        }
        $mansong_array = Model('p_mansong');
        $mansong = $mansong_array->getMansongInfoByStoreID($_POST["store_id"]);
        if (!empty($mansong)) {
            $mansong['start_time_text'] = date('Y-m-d', $mansong['start_time']);
            $mansong['end_time_text'] = date('Y-m-d', $mansong['end_time']);
            $promotion['promotion']['mansong'] = $mansong;
        }
        output_data($promotion);
    }

    private function get_store_goods_class($store_id) {
        if ($store_id <= 0) {
            output_error('参数错误');
        }

        /** @var goodsModel $model_goods */
        $model_goods = Model('goods');
        /** @var goods_classModel $model_goods_class */
        $model_goods_class = Model('goods_class');
        $condition = array();
        $condition['store_id'] = $store_id;

        $condition['goods_state'] = 1;
        $condition['goods_verify'] = 1;
        $condition['is_del'] = 0;
        $fields = 'gc_id_1';

        $res = $model_goods->field($fields)->where($condition)->group($fields)->select();
        $goods_gc_list = array_unique(array_column($res, 'gc_id_1'));
        $class_data_list = $model_goods_class->getCache();
        $class_data_list = $class_data_list['data'];
        $result = array();
        foreach ($goods_gc_list as $gc_id) {
            if (!array_key_exists($gc_id, $class_data_list)) {
                continue;
            }
            $gc_name = $class_data_list[$gc_id]['gc_name'];
            $gc_name = explode('/', $gc_name);
            $result[] = array(
                'gc_id' => $gc_id,
                'gc_name' => $gc_name[0] ? $gc_name[0] : $gc_name
            );
        }
        return $result;
    }

}
