<?php
/**
 * 我的购物车
 *
 *
 *
 *
 * @copyright  Copyright (c) 2007-2015 ShopNC Inc. (http://www.shopnc.net)
 * @license    http://www.shopnc.net
 * @link       http://www.shopnc.net
 * @since      File available since Release v1.1
 */


defined('ByShopWWI') or exit('Access Invalid!');

class cartControl extends mobileMemberControl
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 购物车列表
     */
    public function listOp()
    {
        /** @var cartModel $model_cart */
        $model_cart = Model('cart');

        $condition = array('buyer_id' => $this->member_info['member_id']);
        $cart_list = $model_cart->listCart('db', $condition);

        // 加价购
        $jjgObj = new \StdClass();

        // 购物车列表 [得到最新商品属性及促销信息]
        $cart_list = logic('buy_1')->getGoodsCartList($cart_list, $jjgObj,$this->member_info['member_id']);

        $model_goods = Model('goods');
        $sum = 0;
        $cart_a = array();
        foreach ($cart_list as $key => $val) {
            if (empty($val['state'])) {//剔除无法购买的商品(下架等原因) 后期展示
                $model_cart->delCart('db', $condition = array('cart_id' => $val['cart_id']));
                continue;
            }
            //$val['store_id'] = $key;
            $cart_a[$val['store_id']]['store_id'] = $val['store_id'];
            $cart_a[$val['store_id']]['store_name'] = $val['store_name'];

            $goods_data = $model_goods->getGoodsOnlineInfoForShare($val['goods_id']);

            $goods_data['goods_spec'] = unserialize($goods_data['goods_spec']);
            $goods_data['spec_name'] = unserialize($goods_data['spec_name']);
            $goods_data['goods_spec'] = is_array($goods_data['goods_spec']) ? array_values($goods_data['goods_spec']) : array();
            $goods_data['spec_name'] = is_array($goods_data['spec_name']) ? array_values($goods_data['spec_name']) : array();
            $cart_a[$val['store_id']]['goods'][$key] = $goods_data;

            $cart_a[$val['store_id']]['goods'][$key]['goods_price'] = $val['goods_price'];
            $cart_a[$val['store_id']]['goods'][$key]['cart_id'] = $val['cart_id'];
            $cart_a[$val['store_id']]['goods'][$key]['goods_num'] = $val['goods_num'];
            $cart_a[$val['store_id']]['goods'][$key]['goods_image_url'] = cthumb($val['goods_image'], $val['store_id']);

            $cart_a[$val['store_id']]['goods'][$key]['gift_list'] = $val['gift_list'];
            if (empty($val['xianshi_info'])) $cart_a[$val['store_id']]['goods'][$key]['xianshi_info'] = array('xianshi_name' => '');

            if (!isset($cart_a[$val['store_id']]['goods'][$key]['promotion_type'])) $cart_a[$val['store_id']]['goods'][$key]['promotion_type'] = '';
            if (!isset($cart_a[$val['store_id']]['goods'][$key]['promotion_price'])) $cart_a[$val['store_id']]['goods'][$key]['promotion_price'] = '';
            $cart_list[$key]['goods_sum'] = ncPriceFormat($val['goods_price'] * $val['goods_num']);
            $sum += $cart_list[$key]['goods_sum'];
        }
        $res = array();
        foreach ($cart_a as $cart) {
            $cart['goods'] = array_values($cart['goods']);
            $res[] = $cart;
        }

        output_data(array('cart_list' => $res, 'sum' => ncPriceFormat($sum), 'cart_count' => count($cart_list)));
    }

    public function list_shequOp() {
        /** @var cartModel $model_cart */
        $model_cart = Model('cart');

        $tuanInfo = $this->getTuanInfo();
        if (empty($tuanInfo)) {
            output_data(array('cart_list' => array(), 'sum' => ncPriceFormat(0), 'cart_count' => 0));
        }
        $delete_condition = array(
            'buyer_id' => $this->member_info['member_id'],
            'config_tuan_id' => array('between', array(1, $tuanInfo['config_tuan_id'] -1))
        );
        $model_cart->delCart('db', $delete_condition);

        $condition = array(
            'buyer_id' => $this->member_info['member_id'],
            'config_tuan_id' => $tuanInfo['config_tuan_id']
        );
        $cart_list = $model_cart->listCart('db', $condition);

        // 加价购
        $jjgObj = new \StdClass();

        // 购物车列表 [得到最新商品属性及促销信息]
        $cart_list = logic('buy_1')->getGoodsCartList($cart_list, $jjgObj,$this->member_info['member_id']);

        $model_goods = Model('goods');
        $sum = 0;
        $cart_a = array();
        foreach ($cart_list as $key => $val) {
            if (empty($val['state'])) {//剔除无法购买的商品(下架等原因) 后期展示
                $model_cart->delCart('db', $condition = array('cart_id' => $val['cart_id']));
                continue;
            }
            //$val['store_id'] = $key;
            $cart_a[$val['store_id']]['store_id'] = $val['store_id'];
            $cart_a[$val['store_id']]['store_name'] = $val['store_name'];

            $goods_data = $model_goods->getGoodsOnlineInfoForShare($val['goods_id']);

            $goods_data['goods_spec'] = unserialize($goods_data['goods_spec']);
            $goods_data['spec_name'] = unserialize($goods_data['spec_name']);
            $goods_data['goods_spec'] = is_array($goods_data['goods_spec']) ? array_values($goods_data['goods_spec']) : array();
            $goods_data['spec_name'] = is_array($goods_data['spec_name']) ? array_values($goods_data['spec_name']) : array();
            $cart_a[$val['store_id']]['goods'][$key] = $goods_data;

            $cart_a[$val['store_id']]['goods'][$key]['goods_price'] = $val['goods_price'];
            $cart_a[$val['store_id']]['goods'][$key]['cart_id'] = $val['cart_id'];
            $cart_a[$val['store_id']]['goods'][$key]['goods_num'] = $val['goods_num'];
            $cart_a[$val['store_id']]['goods'][$key]['goods_image_url'] = cthumb($val['goods_image'], $val['store_id']);

            $cart_a[$val['store_id']]['goods'][$key]['gift_list'] = $val['gift_list'];
            if (empty($val['xianshi_info'])) $cart_a[$val['store_id']]['goods'][$key]['xianshi_info'] = array('xianshi_name' => '');

            if (!isset($cart_a[$val['store_id']]['goods'][$key]['promotion_type'])) $cart_a[$val['store_id']]['goods'][$key]['promotion_type'] = '';
            if (!isset($cart_a[$val['store_id']]['goods'][$key]['promotion_price'])) $cart_a[$val['store_id']]['goods'][$key]['promotion_price'] = '';
            $cart_list[$key]['goods_sum'] = ncPriceFormat($val['goods_price'] * $val['goods_num']);
            $sum += $cart_list[$key]['goods_sum'];
        }
        $res = array();
        foreach ($cart_a as $cart) {
            $cart['goods'] = array_values($cart['goods']);
            $res[] = $cart;
        }

        output_data(array('cart_list' => $res, 'sum' => ncPriceFormat($sum), 'cart_count' => count($cart_list)));
    }

    private function getTuanInfo() {

        /** @var shequ_tuan_configModel $shequ_tuan_configModel */
        $shequ_tuan_configModel = Model('shequ_tuan_config');
        $shequ_tuan_config_info = $shequ_tuan_configModel->getTuanConfigInfo(array(
            'config_start_time' => array('lt', TIMESTAMP),
            'config_end_time' => array('gt', TIMESTAMP),
            'config_state' => 1
        ));
        return $shequ_tuan_config_info;
    }

    public function add_shequOp() {

        $tuan_info = $this->getTuanInfo();
        if (empty($tuan_info)) {
            output_error('参不可添加');
        }

        $goods_id = intval($_POST['goods_id']);
        $quantity = intval($_POST['quantity']);
        $config_tuan_id = $tuan_info['config_tuan_id'];
        if ($goods_id <= 0 || $quantity <= 0) {
            output_error('参数错误');
        }
        /** @var goodsModel $model_goods */
        $model_goods = Model('goods');
        /** @var cartModel $model_cart */
        $model_cart = Model('cart');
        /** @var buy_1Logic $logic_buy_1 */
        $logic_buy_1 = Logic('buy_1');

        $goods_info = $model_goods->getGoodsOnlineInfoAndPromotionById($goods_id);

        //验证是否可以购买
        if (empty($goods_info)) {
            output_error('商品已下架或不存在');
        }

        //限制物流的不可添加购物车
        $store_id = $goods_info['store_id'];
        /** @var storeModel $store_model */
        $store_model = Model('store');
        $store_info = $store_model->getStoreInfo(array('store_id' => $store_id));
        if (!$store_info['is_shequ_tuan']) {
            output_error('该商品暂不可添加购物车');
        }

        //团购
        $logic_buy_1->getGroupbuyInfo($goods_info);

        //限时折扣
        $logic_buy_1->getXianshiInfo($goods_info, $quantity);

        if ($goods_info['store_id'] == $this->member_info['store_id']) {
            output_error('不能购买自己发布的商品');
        }
        if (intval($goods_info['goods_storage']) < 1 || intval($goods_info['goods_storage']) < $quantity) {
            output_error('库存不足');
        }

        $param = array();
        $param['buyer_id'] = $this->member_info['member_id'];
        $param['store_id'] = $goods_info['store_id'];
        $param['goods_id'] = $goods_info['goods_id'];
        $param['goods_name'] = $goods_info['goods_name'];
        $param['goods_price'] = $goods_info['goods_price'];
        $param['goods_image'] = $goods_info['goods_image'];
        $param['store_name'] = $goods_info['store_name'];
        $param['config_tuan_id'] = $config_tuan_id;
        $check_condition = array();
        $check_condition['goods_id'] = $goods_info['goods_id'];
        $check_condition['buyer_id'] = $this->member_info['member_id'];
        $check_condition['config_tuan_id'] = $config_tuan_id;
        if (isset($goods_info['bl_id'])) {
            $check_condition['bl_id'] = $goods_info['bl_id'];
        } else {
            $check_condition['bl_id'] = 0;
        }
        $check_cart = $model_cart->checkCart($check_condition);
        if (!empty($check_cart)) {
            $model_cart->where($check_condition)->update(array('goods_num' => $quantity));
            output_data('1');
        }

        $result = $model_cart->addCart($param, 'db', $quantity);
        if ($result) {
            output_data('1');
        } else {
            output_error('失败');
        }
    }

    /**
     * 购物车添加
     */
    public function addOp()
    {
        $goods_id = intval($_POST['goods_id']);
        $quantity = intval($_POST['quantity']);
        if ($goods_id <= 0 || $quantity <= 0) {
            output_error('参数错误');
        }
        /** @var goodsModel $model_goods */
        $model_goods = Model('goods');
        /** @var cartModel $model_cart */
        $model_cart = Model('cart');
        /** @var buy_1Logic $logic_buy_1 */
        $logic_buy_1 = Logic('buy_1');

        $goods_info = $model_goods->getGoodsOnlineInfoAndPromotionById($goods_id);

        //验证是否可以购买
        if (empty($goods_info)) {
            output_error('商品已下架或不存在');
        }

        //团购
        $logic_buy_1->getGroupbuyInfo($goods_info);

        //限时折扣
        $logic_buy_1->getXianshiInfo($goods_info, $quantity);

        if ($goods_info['store_id'] == $this->member_info['store_id']) {
            output_error('不能购买自己发布的商品');
        }
        if (intval($goods_info['goods_storage']) < 1 || intval($goods_info['goods_storage']) < $quantity) {
            output_error('库存不足');
        }

        $param = array();
        $param['buyer_id'] = $this->member_info['member_id'];
        $param['store_id'] = $goods_info['store_id'];
        $param['goods_id'] = $goods_info['goods_id'];
        $param['goods_name'] = $goods_info['goods_name'];
        $param['goods_price'] = $goods_info['goods_price'];
        $param['goods_image'] = $goods_info['goods_image'];
        $param['store_name'] = $goods_info['store_name'];
        $check_condition = array();
        $check_condition['goods_id'] = $goods_info['goods_id'];
        $check_condition['buyer_id'] = $this->member_info['member_id'];
        if (isset($goods_info['bl_id'])) {
            $check_condition['bl_id'] = $goods_info['bl_id'];
        } else {
            $check_condition['bl_id'] = 0;
        }
        $check_cart = $model_cart->checkCart($check_condition);
        if (!empty($check_cart)) {
            $model_cart->where($check_condition)->update(array('goods_num' => $quantity));
            output_data('1');
        }

        $result = $model_cart->addCart($param, 'db', $quantity);
        if ($result) {
            output_data('1');
        } else {
            output_error('失败');
        }
    }

    /**
     * 购物车删除
     */
    public function deleteOp()
    {
        $goods_id = $_POST['goods_id'];
        /** @var cartModel $model_cart */
        $model_cart = Model('cart');
        $condition = array();
        $condition['buyer_id'] = $this->member_info['member_id'];

        if ($goods_id <= 0) {
            output_error('参数错误');
        }
        $condition['goods_id'] = $goods_id;
        $model_cart->delCart('db', $condition);
        output_data('1');
        //output_error('参数错误');
    }

    /**
     * 购物车删除
     */
    public function removeOp()
    {
        $cart_id = $_POST['cart_id'];
        /** @var cartModel $model_cart */
        $model_cart = Model('cart');

        $condition = array();
        $condition['buyer_id'] = $this->member_info['member_id'];
        if ($cart_id > 0 || is_array($cart_id)) {
            if (is_array($cart_id)) $cart_id = implode(',', $cart_id);
            $condition['cart_id'] = array('in', $cart_id);
        }
        $model_cart->delCart('db', $condition);
        output_data('1');
        //output_error('参数错误');
    }

    /**
     * 更新购物车购买数量
     */
    public function editOp()
    {
        $cart_id = intval(abs($_POST['cart_id']));
        $quantity = intval(abs($_POST['quantity']));
        if (empty($cart_id) || empty($quantity)) {
            output_error('参数错误');
        }

        $model_cart = Model('cart');

        $cart_info = $model_cart->getCartInfo(array('cart_id' => $cart_id, 'buyer_id' => $this->member_info['member_id']));

        //检查是否为本人购物车
        if ($cart_info['buyer_id'] != $this->member_info['member_id']) {
            output_error('参数错误');
        }

        //检查库存是否充足
        if (!$this->_check_goods_storage($cart_info, $quantity, $this->member_info['member_id'])) {
            output_error('超出限购数或库存不足');
        }

        $data = array();
        $data['goods_num'] = $quantity;
        $update = $model_cart->editCart($data, array('cart_id' => $cart_id));
        if ($update) {
            $return = array();
            $return['quantity'] = $quantity;
            $return['goods_price'] = ncPriceFormat($cart_info['goods_price']);
            $return['total_price'] = ncPriceFormat($cart_info['goods_price'] * $quantity);
            output_data($return);
        } else {
            output_error('修改失败');
        }
    }

    /**
     * 检查库存是否充足
     */
    private function _check_goods_storage(& $cart_info, $quantity, $member_id)
    {
        $model_goods = Model('goods');
        $model_bl = Model('p_bundling');
        $logic_buy_1 = Logic('buy_1');

        if ($cart_info['bl_id'] == '0') {
            //普通商品
            $goods_info = $model_goods->getGoodsOnlineInfoAndPromotionById($cart_info['goods_id']);

            //团购
            $logic_buy_1->getGroupbuyInfo($goods_info);
            if ($goods_info['ifgroupbuy']) {
                if ($goods_info['upper_limit'] && $quantity > $goods_info['upper_limit']) {
                    return false;
                }
            }

            //限时折扣
            $logic_buy_1->getXianshiInfo($goods_info, $quantity);

            if (intval($goods_info['goods_storage']) < $quantity) {
                return false;
            }
            $goods_info['cart_id'] = $cart_info['cart_id'];
            $cart_info = $goods_info;
        } else {
            //优惠套装商品
            $bl_goods_list = $model_bl->getBundlingGoodsList(array('bl_id' => $cart_info['bl_id']));
            $goods_id_array = array();
            foreach ($bl_goods_list as $goods) {
                $goods_id_array[] = $goods['goods_id'];
            }
            $bl_goods_list = $model_goods->getGoodsOnlineListAndPromotionByIdArray($goods_id_array);

            //如果有商品库存不足，更新购买数量到目前最大库存
            foreach ($bl_goods_list as $goods_info) {
                if (intval($goods_info['goods_storage']) < $quantity) {
                    return false;
                }
            }
        }
        return true;
    }


    /**
     * 检查购物车数量
     */
    public function countOp()
    {
        $model_cart = Model('cart');
        $count = $model_cart->countCartByMemberId($this->member_info['member_id']);
        $data['count'] = $count;
        output_data($data);
    }

    public function count_shequOp()
    {
        $tuanInfo = $this->getTuanInfo();
        if (empty($tuanInfo)) {
            $data['count'] = 0;
            output_data($data);
        }

        /** @var cartModel $model_cart */
        $condition = array(
            'buyer_id' => $this->member_info['member_id'],
            'config_tuan_id' => $tuanInfo['config_tuan_id']
        );
        $data['count'] = Model('cart')->where($condition)->count();
        output_data($data);
    }

}
