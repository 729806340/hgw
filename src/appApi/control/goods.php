<?php
/**
 * 商品
 *
 *
 *
 *by wansyb QQ群：111731672
 *你正在使用的是由网店 运 维提供S2.0系统！保障你的网络安全！ 购买授权请前往shopnc
 */


defined('ByShopWWI') or exit('Access Invalid!');

class goodsControl extends mobileHomeControl
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 商品列表
     */
    public function listOp()
    {
        $check = array(
            'keyword',    //	string	可空	搜索关键字
            'barcode',    //    string  可空 商品条形码
            'key',        //	int	可空	排序方式 0-新品 1-销量 2-浏览量 3-价格 4-评价数
            'order',      //	int	可空	排序方式 1-升序 2-降序
            'gc_id',      //	int	可空	分类ID
            'b_id',       //	int	可空	品牌ID
            'gift',       //    int 可空 是否有赠品 1-是
            'area_id',    //    int 可空 地区ID
            'price_from', //    int 可空 价格区间
            'price_to',   //    int 可空 价格区间
        );
        $params = array();
        foreach ($check as $key) {
            if (isset($_POST[$key])) $params[$key] = $_POST[$key];
        }
        $params['keyword'] = str_replace('%', '', $params['keyword']);
        $params['price_from'] = intval($params['price_from']);
        $params['price_to'] = intval($params['price_to']);
        $params['is_book'] = 0;//暂时不显示定金预售商品，手机端未做

        /** @var goodsModel $model_goods */
        $model_goods = Model('goods');
        /** @var searchModel $model_search */
        $model_search = Model('search');
        $goods_list = null;
        $page_count = 0;
        //如果未设置价格区间 优先从全文索引库里查找
        if ($params['price_to'] == 0) list($goods_list, $page_count) = $model_search->indexerSearch($params, $this->page);
        if (!is_null($goods_list)) {
            $goods_list = array_values($goods_list);
        } else {
            //所需字段
            $field = 'goods_id,goods_name,goods_jingle,goods_price,goods_promotion_price,goods_promotion_type,goods_marketprice,goods_salenum,goods_collect';
            $field .= ',goods_image,evaluation_good_star,evaluation_count,is_virtual,is_presell,is_fcode,have_gift';
            $condition = $this->parse_list_params($params);
            $order = $this->_goods_list_order($params['key'], $params['order']);
            $goods_list = $model_goods->getGoodsListByColorDistinct($condition, $field, $order, $this->page);
            $page_count = $model_goods->gettotalpage();
        }
        //处理商品列表(团购、限时折扣、商品图片)
        $goods_list = $this->_goods_list_extend($goods_list);
        if (intval($_GET['curpage']) > $page_count) $goods_list = array();
        output_data(array('goods_list' => $goods_list), mobile_page($page_count));
    }

    private function parse_list_params($params)
    {
        //查询条件
        $condition = array();
        $condition['is_book'] = intval($params['is_book']);
        if (intval($params['gc_id']) > 0) $condition['gc_id'] = intval($params['gc_id']);
        if (!empty($params['keyword'])) {
            $condition['goods_name|goods_jingle'] = array('like', '%' . $params['keyword'] . '%');

            if (cookie('his_sh') == '') {
                $his_sh_list = array();
            } else {
                $his_sh_list = explode('~', cookie('his_sh'));
            }
            if (strlen($params['keyword']) <= 30 && !in_array($params['keyword'], $his_sh_list)) {
                if (array_unshift($his_sh_list, $params['keyword']) > 8) {
                    array_pop($his_sh_list);
                }
            }
            setNcCookie('his_sh', implode('~', $his_sh_list), 2592000); //添加历史纪录
        }
        if (!empty($params['barcode'])) $condition['goods_barcode'] = $params['barcode'];
        if (!empty($params['b_id']) && intval($params['b_id'] > 0)) $condition['brand_id'] = intval($params['b_id']);
        if (intval($params['price_from']) > 0) $condition['goods_price'][] = array('egt', intval($params['price_from']));
        if (intval($params['price_to']) > 0) $condition['goods_price'][] = array('elt', intval($params['price_to']));
        if (intval($params['area_id']) > 0) $condition['areaid_1'] = intval($params['area_id']);
        if ($params['gift'] == 1) $condition['have_gift'] = 1;

        return $condition;
    }

    //商品列表排序方式
    private function _goods_list_order($key, $order)
    {
        $sequence = $order == 1 ? 'asc' : 'desc';
        switch ($key) {
            //销量
            case '1' :
                $result = 'goods_salenum';
                break;
            //浏览量
            case '2' :
                $result = 'goods_click';
                break;
            //价格
            case '3' :
                $result = 'goods_promotion_price';
                break;
            //评价
            case '4':
                $result = 'evaluation_count';
                break;
            default:
                $result = 'goods_id';
        }
        return $result . '  ' . $sequence;
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
     * 商品详细页
     */
    public function detailOp()
    {
        $goods_id = intval($_POST['goods_id']);
        // 商品详细信息
        /** @var goodsModel $model_goods */
        $model_goods = Model('goods');
        $goods_detail = $model_goods->getGoodsDetail($goods_id);

        if (empty($goods_detail) || $goods_detail['goods_info']['is_del'] == '1') {
            output_error('商品不存在');
        }

        // 默认预订商品不支持手机端显示
        if ($goods_detail['goods_info']['is_book']) {
            output_error('预订商品不支持手机端显示');
        }

        $com_id = $goods_detail['goods_info']['goods_commonid'];

        //解决APP端无法解析BUG
        $mobile_body = $goods_detail['goods_info']['mobile_body'];
        $mobile_body = str_replace(array("\r\n", "\r", "\n", "\t"), "", $mobile_body);
        $mobile_body = str_replace('"', "'", $mobile_body);
        $goods_detail['goods_info']['mobile_body'] = $mobile_body;


        //推荐商品
        $model_store = Model('store');
        $hot_sales = $model_store->getHotSalesList($goods_detail['goods_info']['store_id'], 6, true);
        $goodsid_array = array();
        foreach ($hot_sales as $value) {
            $goodsid_array[] = $value['goods_id'];
        }
        $sole_array = Model('p_sole')->getSoleGoodsList(array('goods_id' => array('in', $goodsid_array)));
        $sole_array = array_under_reset($sole_array, 'goods_id');
        $goods_commend_list = array();
        foreach ($hot_sales as $value) {
            $goods_commend = array();
            $goods_commend['goods_id'] = $value['goods_id'];
            $goods_commend['goods_name'] = $value['goods_name'];
            $goods_commend['goods_price'] = $value['goods_price'];
            $goods_commend['goods_promotion_price'] = $value['goods_promotion_price'];
            if (!empty($sole_array[$value['goods_id']])) {
                $goods_commend['goods_promotion_price'] = $sole_array[$value['goods_id']]['sole_price'];
            }
            $goods_commend['goods_image_url'] = cthumb($value['goods_image'], 240);
            $goods_commend_list[] = $goods_commend;
        }

        $goods_detail['goods_commend_list'] = $goods_commend_list;
        $store_info = $model_store->getStoreInfoByID($goods_detail['goods_info']['store_id']);

        $goods_detail['store_info']['store_id'] = $store_info['store_id'];
        $goods_detail['store_info']['store_name'] = $store_info['store_name'];
        $goods_detail['store_info']['member_id'] = $store_info['member_id'];
        $goods_detail['store_info']['member_name'] = $store_info['member_name'];
        $goods_detail['store_info']['avatar'] = getMemberAvatarForID($store_info['member_id']);

        $goods_detail['store_info']['goods_count'] = $store_info['goods_count'];

        if ($store_info['is_own_shop']) {
            $goods_detail['store_info']['store_credit'] = array(
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
            foreach ((array)$store_info['store_credit'] as $k => $v) {
                $v['percent_text'] = $percentClassTextMap[$v['percent_class']];
                $storeCredit[$k] = $v;
            }
            $goods_detail['store_info']['store_credit'] = $storeCredit;
        }

        //商品详细信息处理
        $goods_detail = $this->_goods_detail_extend($goods_detail);

        // 如果已登录 判断该商品是否已被收藏
        $goods_detail['is_favorate'] = false;
        $goods_detail['cart_count'] = 0;
        if ($memberId = $this->getMemberIdIfExists()) {
            $c = (int)Model('favorites')->getGoodsFavoritesCountByGoodsId($goods_id, $memberId);
            $goods_detail['is_favorate'] = $c > 0;
            $goods_detail['cart_count'] = Model('cart')->countCartByMemberId($memberId);
        }
        $goods_detail['goods_hair_info'] = array('content' => '免运费', 'if_store_cn' => '有货', 'if_store' => true, 'area_name' => '全国');
        $goods_eval_list = $this->_get_comments($goods_id, 1, 1);
        $goods_detail['goods_eval_list'] = current($goods_eval_list);
        $goods_detail['goods_image'] = explode(',', $goods_detail['goods_image']);
        Model('goods_browse')->addViewedGoods($goods_id, $memberId); //加入浏览历史数据库
        unset($goods_detail['spec_list'], $goods_detail['spec_image']);
        $spec_info = $this->getSpecByCommonId($com_id);
        $goods_detail = array_merge($goods_detail, $spec_info);
        //如果存在活动，商品价格为活动价格
        if (!empty($goods_detail['goods_info']['promotion_type'])) $goods_detail['goods_info']['goods_price'] = $goods_detail['goods_info']['promotion_price'];
        $goods_detail['is_buy'] = $this->_isBuy($goods_detail['goods_info'],$store_info);
        output_data($goods_detail);
    }

    public function detail1Op()
    {
        $goods_id = intval($_POST['goods_id']);
        // 商品详细信息
        /** @var goodsModel $model_goods */
        $model_goods = Model('goods');
        $goods_detail = $model_goods->getGoodsDetail($goods_id);

        if (empty($goods_detail) || $goods_detail['goods_info']['is_del'] == '1') {
            output_error('商品不存在');
        }

        // 默认预订商品不支持手机端显示
        if ($goods_detail['goods_info']['is_book']) {
            output_error('预订商品不支持手机端显示');
        }

        $com_id = $goods_detail['goods_info']['goods_commonid'];

        //如果存在活动，商品价格为活动价格
        if (!empty($goods_detail['goods_info']['promotion_type'])) $goods_detail['goods_info']['goods_price'] = $goods_detail['goods_info']['promotion_price'];
        //推荐商品
        $model_store = Model('store');
        $hot_sales = $model_store->getHotSalesList($goods_detail['goods_info']['store_id'], 6, true);
        $goodsid_array = array();
        foreach ($hot_sales as $value) {
            $goodsid_array[] = $value['goods_id'];
        }
        $sole_array = Model('p_sole')->getSoleGoodsList(array('goods_id' => array('in', $goodsid_array)));
        $sole_array = array_under_reset($sole_array, 'goods_id');
        $goods_commend_list = array();
        foreach ($hot_sales as $value) {
            $goods_commend = array();
            $goods_commend['goods_id'] = $value['goods_id'];
            $goods_commend['goods_name'] = $value['goods_name'];
            $goods_commend['goods_price'] = $value['goods_price'];
            $goods_commend['goods_promotion_price'] = $value['goods_promotion_price'];
            if (!empty($sole_array[$value['goods_id']])) {
                $goods_commend['goods_promotion_price'] = $sole_array[$value['goods_id']]['sole_price'];
            }
            $goods_commend['goods_image'] = cthumb($value['goods_image'], 240);
            $goods_commend_list[] = $goods_commend;
        }

        $goods_detail['goods_commend_list'] = $goods_commend_list;
        $store_info = $model_store->getStoreInfoByID($goods_detail['goods_info']['store_id']);

        $goods_detail['store_info']['store_id'] = $store_info['store_id'];
        $goods_detail['store_info']['store_name'] = $store_info['store_name'];
        $goods_detail['store_info']['member_id'] = $store_info['member_id'];
        $goods_detail['store_info']['member_name'] = $store_info['member_name'];
        $goods_detail['store_info']['avatar'] = getMemberAvatarForID($store_info['member_id']);

        $goods_detail['store_info']['goods_count'] = $store_info['goods_count'];

        if ($store_info['is_own_shop']) {
            $goods_detail['store_info']['store_credit'] = array(
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
            foreach ((array)$store_info['store_credit'] as $k => $v) {
                $v['percent_text'] = $percentClassTextMap[$v['percent_class']];
                $storeCredit[$k] = $v;
            }
            $goods_detail['store_info']['store_credit'] = $storeCredit;
        }

        //商品详细信息处理
        $goods_detail = $this->_goods_detail_extend($goods_detail);

        // 如果已登录 判断该商品是否已被收藏
        $goods_detail['is_favorate'] = false;
        $goods_detail['cart_count'] = 0;
        if ($memberId = $this->getMemberIdIfExists()) {
            $c = (int)Model('favorites')->getGoodsFavoritesCountByGoodsId($goods_id, $memberId);
            $goods_detail['is_favorate'] = $c > 0;
            $goods_detail['cart_count'] = Model('cart')->countCartByMemberId($memberId);
        }
        $goods_detail['goods_hair_info'] = array('content' => '免运费', 'if_store_cn' => '有货', 'if_store' => true, 'area_name' => '全国');
        $goods_eval_list = $this->_get_comments($goods_id, 1, 1);
        $goods_detail['goods_eval_list'] = current($goods_eval_list);
        $goods_detail['goods_image'] = explode(',', $goods_detail['goods_image']);
        Model('goods_browse')->addViewedGoods($goods_id, $memberId); //加入浏览历史数据库
        //output_data($goods_detail);
        $res = array();
        $res['images'] = $goods_detail['goods_image'];
        $img = current($res['images']);
        $res['img'] = empty($img) ? UPLOAD_SITE_URL . '/' . defaultGoodsImage(240) : $img;
        $res['title'] = $goods_detail['goods_info']['goods_name'];
        $res['price'] = $goods_detail['goods_info']['goods_promotion_type'] == 0 ? $goods_detail['goods_info']['goods_price'] : $goods_detail['goods_info']['goods_promotion_price'];
        $res['market_price'] = $goods_detail['goods_info']['goods_marketprice'];
        $res['freight'] = $goods_detail['goods_info']['goods_freight'];
        $res['storage'] = $goods_detail['goods_info']['goods_storage'];
        $res['sale_count'] = $goods_detail['goods_info']['goods_salenum'];
        $res['is_favorate'] = $goods_detail['is_favorate'];
        $res['cart_count'] = $goods_detail['cart_count'];
        $res['provenance'] = '';
        $res['promotion'] = array();
        /*if (!empty($goods_detail['goods_info']['groupbuy_info'])) $res['promotion'][] = '团购';
        if (!empty($goods_detail['goods_info']['xianshi_info'])) $res['promotion'][] = '限时折扣';
        if (!empty($goods_detail['goods_info']['jjg_info'])) $res['promotion'][] = '加价购';*/
        if (!empty($goods_detail['goods_info']['promotion_type'])) $res['promotion'][] = $goods_detail['goods_info']['title'];
        $res['store_name'] = $goods_detail['store_info']['store_name'];
        $res['store_id'] = $goods_detail['store_info']['store_id'];
        $res['store_slogan'] = $goods_detail['store_info']['store_slogan'];
        $res['store_avatar'] = $goods_detail['store_info']['avatar'];
        $res['desccredit'] = $goods_detail['store_info']['store_credit']['store_desccredit']['credit'];
        $res['servicecredit'] = $goods_detail['store_info']['store_credit']['store_servicecredit']['credit'];
        $res['deliverycredit'] = $goods_detail['store_info']['store_credit']['store_deliverycredit']['credit'];


        // commend_list
        $res['commend_list'] = array();
        foreach ($goods_detail['goods_commend_list'] as $goods_commend) {
            $res['commend_list'][] = array(
                'goods_id'              => $goods_commend['goods_id'],
                'goods_name'            => $goods_commend['goods_name'],
                'goods_promotion_price' => $goods_commend['goods_promotion_price'],
                'goods_image'           => $goods_commend['goods_image'],
                'goods_image_url'       => $goods_commend['goods_image'],
            );
        }
        $eval = $goods_detail['goods_eval_list'];
        $res['evaluates'] = empty($eval) ? array() : array(
            array(
                'member_avatar' => $eval['member_avatar'],//member_avatar
                'member_name'   => $eval['geval_frommembername'],
                'content'       => $eval['geval_content'],
                'images'        => empty($eval['geval_image']) ? array() : $eval['geval_image'],
                'addtime'       => $eval['addtime'],
                'agree'         => 0,
            )
        );
        $spec_info = $this->getSpecByCommonId($com_id);
        $res = array_merge($res, $spec_info);
        $res['is_buy'] = $this->_isBuy($goods_detail['goods_info'],$store_info);
        output_data($res);
    }

    private function _isBuy($goods,$store)
    {
        if ($store['manage_type'] == 'co_construct') {
            return (
                $goods['tax_input'] < 100
                && $goods['tax_output'] < 100
                && $goods['goods_cost'] > 0
            ) ? 1 : 0;
        }
        return 1;
    }

    /**
     * 商品详细信息处理
     */
    private function _goods_detail_extend($goods_detail)
    {
        //整理商品规格
        unset($goods_detail['spec_list']);
        $goods_detail['spec_list'] = $goods_detail['spec_list_mobile'];
        unset($goods_detail['spec_list_mobile']);

        //整理商品图片
        unset($goods_detail['goods_image']);
        $goods_detail['goods_image'] = implode(',', $goods_detail['goods_image_mobile']);
        unset($goods_detail['goods_image_mobile']);

        //商品链接
        $goods_detail['goods_info']['goods_url'] = urlShop('goods', 'index', array('goods_id' => $goods_detail['goods_info']['goods_id']));

        //整理数据
        unset($goods_detail['goods_info']['goods_commonid']);
        unset($goods_detail['goods_info']['gc_id']);
        unset($goods_detail['goods_info']['gc_name']);
        unset($goods_detail['goods_info']['store_id']);
        unset($goods_detail['goods_info']['store_name']);
        unset($goods_detail['goods_info']['brand_id']);
        unset($goods_detail['goods_info']['brand_name']);
        unset($goods_detail['goods_info']['type_id']);
        unset($goods_detail['goods_info']['goods_image']);
        unset($goods_detail['goods_info']['goods_body']);
        unset($goods_detail['goods_info']['goods_state']);
        unset($goods_detail['goods_info']['goods_stateremark']);
        unset($goods_detail['goods_info']['goods_verify']);
        unset($goods_detail['goods_info']['goods_verifyremark']);
        unset($goods_detail['goods_info']['goods_lock']);
        unset($goods_detail['goods_info']['goods_addtime']);
        unset($goods_detail['goods_info']['goods_edittime']);
        unset($goods_detail['goods_info']['goods_selltime']);
        unset($goods_detail['goods_info']['goods_show']);
        unset($goods_detail['goods_info']['goods_commend']);
        unset($goods_detail['goods_info']['explain']);
        unset($goods_detail['goods_info']['buynow_text']);
        unset($goods_detail['groupbuy_info']);
        unset($goods_detail['xianshi_info']);

        return $goods_detail;
    }

    /**
     * 商品详细页
     */
    public function bodyOp()
    {
        header("Access-Control-Allow-Origin:*");
        $goods_id = intval($_POST['goods_id']);
        if (empty($goods_id)) $goods_id = intval($_GET['goods_id']);

        $model_goods = Model('goods');

        $goods_info = $model_goods->getGoodsInfoByID($goods_id, 'goods_commonid');
        $goods_common_info = $model_goods->getGoodsCommonInfoByID($goods_info['goods_commonid']);


        //$pattern = '/<img src="(http:\/\/[^"]+?)"([^>]+?)(\/*)>/';
        //$goods_common_info['goods_body'] = preg_replace($pattern, '<img shopwwi-url="\\1"\\2 rel="lazy" \\3>', $goods_common_info['goods_body']);
        echo <<<HTML
<html>
<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-touch-fullscreen" content="yes">
<meta name="format-detection" content="telephone=no">
<meta name="apple-mobile-web-app-status-bar-style" content="black">
<meta name="format-detection" content="telephone=no">
<meta name="msapplication-tap-highlight" content="no">
<meta name="viewport" content="initial-scale=1,maximum-scale=1,minimum-scale=1">
<title>商品详情</title>
<link rel="stylesheet" type="text/css" href="http://www.hangowa.com/wap/css/nctouch_products_detail.css">
<style type="text/css">.full-html,.full-body{width: 100% !important;height: 100% !important;margin: 0 !important;overflow: hidden !important;}.full-body *{display: none !important;}.full-iframe {display: block !important;}</style>
</head>
<body style="margin: 0;padding: 0;">
<div class="nctouch-main-layout" id="fixed-tab-pannel">
  <div class="fixed-tab-pannel">
HTML;
        echo $goods_common_info['goods_body'];
        echo '</div></div></body></html>';
        //v($goods_common_info['goods_body']);
        exit;
        //v($goods_common_info);

        //Tpl::output('goods_common_info', $goods_common_info);
        //Tpl::showpage('goods_body');
    }


    public function auto_completeOp()
    {
        $params = $_POST;//参数来源
        if ($params['term'] == '' && cookie('his_sh') != '') {
            $corrected = explode('~', cookie('his_sh'));
            if ($corrected != '' && count($corrected) !== 0) {
                $data = array();
                foreach ($corrected as $word) {
                    $row['id'] = $word;
                    $row['label'] = $word;
                    $row['value'] = $word;
                    $data[] = $row;
                }
                output_data($data);
            }
            return;
        }

        if (!C('fullindexer.open')) return;
        //output_error('1000');
        try {
            require(BASE_DATA_PATH . '/api/xs/lib/XS.php');
            $obj_doc = new XSDocument();
            $obj_xs = new XS(C('fullindexer.appname'));
            $obj_index = $obj_xs->index;
            $obj_search = $obj_xs->search;
            $obj_search->setCharset(CHARSET);
            $corrected = $obj_search->getExpandedQuery($params['term']);
            if (count($corrected) !== 0) {
                $data = array();
                foreach ($corrected as $word) {
                    $row['id'] = $word;
                    $row['label'] = $word;
                    $row['value'] = $word;
                    $data[] = $row;
                }
                output_data($data);
            }
        } catch (XSException $e) {
            if (is_object($obj_index)) {
                $obj_index->flushIndex();
            }
            output_error($e->getMessage());
            //             Log::record('search\auto_complete'.$e->getMessage(),Log::RUN);
        }


    }

    /**
     * 商品详细页运费显示
     *
     * @return unknown
     */
    public function calcOp()
    {
        $params = $_POST;//参数来源
        $area_id = intval($params['area_id']);
        $goods_id = intval($params['goods_id']);
        output_data($this->_calc($area_id, $goods_id));
    }

    public function _calc($area_id, $goods_id)
    {
        $goods_info = Model('goods')->getGoodsInfo(array('goods_id' => $goods_id), 'transport_id,store_id,goods_freight');
        $store_info = Model('store')->getStoreInfoByID($goods_info['store_id']);
        if ($area_id <= 0) {
            if (strpos($store_info['deliver_region'], '|')) {
                $store_info['deliver_region'] = explode('|', $store_info['deliver_region']);
                $store_info['deliver_region_ids'] = explode(' ', $store_info['deliver_region'][0]);
            }
            $area_id = intval($store_info['deliver_region_ids'][1]);
            $area_name = $store_info['deliver_region'][1];
        }
        if ($goods_info['transport_id'] && $area_id > 0) {
            $freight_total = Model('transport')->calc_transport(intval($goods_info['transport_id']), $area_id);
            if ($freight_total > 0) {
                if ($store_info['store_free_price'] > 0) {
                    if ($freight_total >= $store_info['store_free_price']) {
                        $freight_total = '免运费';
                    } else {
                        $freight_total = '运费：' . $freight_total . ' 元，店铺满 ' . $store_info['store_free_price'] . ' 元 免运费';
                    }
                } else {
                    $freight_total = '运费：' . $freight_total . ' 元';
                }
            } else {
                if ($freight_total === false) {
                    $if_store = false;
                }
                $freight_total = '免运费';
            }
        } else {
            $freight_total = $goods_info['goods_freight'] > 0 ? '运费：' . $goods_info['goods_freight'] . ' 元' : '免运费';
        }

        return array('content' => $freight_total, 'if_store_cn' => $if_store === false ? '无货' : '有货', 'if_store' => $if_store === false ? false : true, 'area_name' => $area_name ? $area_name : '全国');
    }

    /*分店地址*/
    public function store_o2o_addrOp()
    {
        $params = $_POST;//参数来源
        $store_id = intval($params['store_id']);
        $model_store_map = Model('store_map');
        $addr_list_source = $model_store_map->getStoreMapList($store_id);
        foreach ($addr_list_source as $k => $v) {
            $addr_list_tmp = array();
            $addr_list_tmp['key'] = $k;
            $addr_list_tmp['map_id'] = $v['map_id'];
            $addr_list_tmp['name_info'] = $v['name_info'];
            $addr_list_tmp['address_info'] = $v['address_info'];
            $addr_list_tmp['phone_info'] = $v['phone_info'];
            $addr_list_tmp['bus_info'] = $v['bus_info'];
            $addr_list_tmp['province'] = $v['baidu_province'];
            $addr_list_tmp['city'] = $v['baidu_city'];
            $addr_list_tmp['district'] = $v['baidu_district'];
            $addr_list_tmp['street'] = $v['baidu_street'];
            $addr_list_tmp['lng'] = $v['baidu_lng'];
            $addr_list_tmp['lat'] = $v['baidu_lat'];
            $addr_list[] = $addr_list_tmp;
        }
        output_data(array('addr_list' => $addr_list));
    }

    /**
     * 商品评价
     */
    public function evaluateOp()
    {
        $params = $_POST;//参数来源
        $goods_id = intval($params['goods_id']);
        if ($goods_id <= 0) {
            output_error('产品不存在');
        }

        $goodsevallist = $this->_get_comments($goods_id, $params['type'], $this->page);
        $model_evaluate_goods = Model("evaluate_goods");
        $page_count = $model_evaluate_goods->gettotalpage();
        if ($_GET['curpage'] > $page_count) $goodsevallist = array();
        output_data(array('goods_eval_list' => $goodsevallist), mobile_page($page_count));

    }

    private function geval_image($str)
    {
        $imgs = explode(',', $str);
        $imgs = array_filter($imgs);
        foreach ($imgs as $k => $v) {
            $imgs[$k] = snsThumb($v);
        }
        return $imgs;
    }

    private function _get_comments($goods_id, $type, $page)
    {
        $condition = array();
        $condition['geval_goodsid'] = $goods_id;
        switch ($type) {
            case '1':
                $condition['geval_scores'] = array('in', '5,4');
                Tpl::output('type', '1');
                break;
            case '2':
                $condition['geval_scores'] = array('in', '3,2');
                Tpl::output('type', '2');
                break;
            case '3':
                $condition['geval_scores'] = array('in', '1');
                Tpl::output('type', '3');
                break;
        }

        //查询商品评分信息
        $model_evaluate_goods = Model("evaluate_goods");
        $goodsevallist = $model_evaluate_goods->getEvaluateGoodsList($condition, $page);
        foreach ($goodsevallist as $key => $value) {
            if (empty($value)) continue;
            $goodsevallist[$key]['member_avatar'] = getMemberAvatarForID($value['geval_frommemberid']);
            $goodsevallist[$key]['geval_image'] = $this->geval_image($value['geval_image']);
            $goodsevallist[$key]['geval_image_again'] = $this->geval_image($value['geval_image_again']);
        }
        return $goodsevallist;
    }

    //商品筛选条件列表
    public function filterOp()
    {
        $gc_id = intval($_POST['gc_id']);
        $brand_info = array();
        //如果传了分类ID 查询该分类下的品牌
        if ($gc_id) {
            $brands = Model('goods')->where('gc_id=' . $gc_id)->field('brand_id')->select();
            $ids = array_column($brands, 'brand_id');
            $ids = array_filter($ids);
            $ids = array_unique($ids);
            if (count($ids) > 0) {
                $where['brand_id'] = array('in', $ids);
                $brand_info = Model('brand')->getBrandPassedList($where, 'brand_id,brand_name');
            }
        } else {//查询所有品牌
            $brand_info = Model('brand')->getBrandPassedList('', 'brand_id,brand_name');
        }
        //所有活动
        $sdf['activity_state'] = '1';
        $sdf['activity_start_date'] = array('elt', time());
        $sdf['activity_end_date'] = array('egt', time());
        $activity_info = Model('activity')->where($sdf)->field('activity_id,activity_title')->select();
        $data['brand_info'] = $brand_info;
        $data['activity_info'] = $activity_info;
        output_data($data);
    }

    //获取APP端商品规格数据
    private function getSpecByCommonId($com_id)
    {
        $model_goods = Model('goods');
        $com_info = $model_goods->getGoodsCommonInfoByID($com_id);
        $spec_list = array();
        $spec_name = unserialize($com_info['spec_name']);
        $spec_value = unserialize($com_info['spec_value']);
        if (!empty($spec_name) && is_array($spec_name)) {
            foreach ($spec_name as $k => $v) {
                $item['spec_id'] = $k;
                $item['spec_name'] = $v;
                $values = array();
                foreach ($spec_value[$k] as $id => $value) {
                    $values[] = array(
                        'spec_value_id'   => $id,
                        'spec_value_name' => $value,
                    );
                }
                $item['values'] = $values;
                $spec_list[] = $item;
            }
        }

        $list = $model_goods->getGoodsList(array('goods_commonid' => $com_id));
        $products = array();
        foreach ($list as $key => $value) {
            $goods_spec = unserialize($value['goods_spec']);
            $spec_name = unserialize($value['spec_name']);
            $spec_info = array();
            if (!empty($spec_name) && is_array($spec_name)) {
                $spec_name = array_keys($spec_name);
                $goods_spec = array_keys($goods_spec);
                foreach ($goods_spec as $i => $id) {
                    $spec_info[] = array(
                        'spec_id'       => $spec_name[$i],
                        'spec_value_id' => $id,
                    );
                }
            }

            $products[] = array(
                'goods_id'              => $value['goods_id'],
                'goods_name'            => $value['goods_name'],
                'goods_price'           => $value['goods_price'],
                'goods_promotion_price' => $value['goods_promotion_price'],
                'goods_image_url'       => cthumb($value['goods_image'], 240),
                'goods_storage'         => $value['goods_storage'],
                'spec_info'             => $spec_info,
            );
        }

        return array('spec_all' => $spec_list, 'goods_list' => $products);
    }
}
