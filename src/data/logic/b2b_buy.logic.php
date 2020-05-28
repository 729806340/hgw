<?php
/**
 * 购买行为
 *
 *
 *
 *
 * @汉购网提供技术支持 授权请购买shopnc授权
 * @license    http://www.hangowa.com
 * @link       交流群号：
 */
defined('ByShopWWI') or exit('Access Invalid!');

class b2b_buyLogic
{

    /**
     * 会员信息
     * @var array
     */
    private $_member_info = array();

    /**
     * 下单数据
     * @var array
     */
    private $_order_data = array();

    /**
     * 表单数据
     * @var array
     */
    private $_post_data = array();

    /**
     * buy_1.logic 对象
     * @var buy_1Logic
     */
    private $_logic_buy_1;

    public function __construct()
    {
        $this->_logic_buy_1 = Logic('buy_1');
    }

    public function getGoodsCartList($cart_list)
    {
        $cart_list = $this->_getOnlineCartList($cart_list);
        return $cart_list;
    }

    private function _getOnlineCartList($cart_list)
    {
        if (empty($cart_list) || !is_array($cart_list)) return $cart_list;
        //验证商品是否有效
        $goods_id_array = array();

        foreach ($cart_list as $key => $cart_info) {
            if (!intval($cart_info['bl_id'])) {
                $goods_id_array[] = $cart_info['goods_id'];
            }
        }
        /** @var b2b_goodsModel $model_goods */
        $model_goods = Model('b2b_goods');
        $goods_online_list = $model_goods->getGoodsList(array('goods_id' => array('in', $goods_id_array)));
        $goods_online_array = array();
        foreach ($goods_online_list as $goods) {
            $goods_online_array[$goods['goods_id']] = $goods;
        }
        foreach ((array)$cart_list as $key => $cart_info) {
            if (intval($cart_info['bl_id'])) continue;
            $cart_list[$key]['state'] = true;
            $cart_list[$key]['storage_state'] = true;
            if (in_array($cart_info['goods_id'], array_keys($goods_online_array))) {

                $goods_online_info = $goods_online_array[$cart_info['goods_id']];
                $cart_list[$key]['goods_commonid'] = $goods_online_info['goods_commonid'];
                $cart_list[$key]['goods_name'] = $cart_info['goods_name'];
                $cart_list[$key]['goods_calculate'] = $goods_online_info['goods_calculate'];
                $cart_list[$key]['gc_id'] = $goods_online_info['common_info']['gc_id'];
                $cart_list[$key]['supplier_id'] =
                    $goods_online_info['common_info']['supplier_id'];
                $cart_list[$key]['goods_image'] = $goods_online_info['goods_image'];
                $cart_list[$key]['goods_price'] = $goods_online_info['goods_price'];
                $cart_list[$key]['transport_id'] = $goods_online_info['transport_id'];
                $cart_list[$key]['goods_freight'] = $goods_online_info['goods_freight'];
                $cart_list[$key]['goods_vat'] = $goods_online_info['goods_vat'];
                $cart_list[$key]['goods_cost'] = $goods_online_info['goods_cost'];
                $cart_list[$key]['tax_input'] = $goods_online_info['tax_input'];
                $cart_list[$key]['tax_output'] = $goods_online_info['tax_output'];
                $cart_list[$key]['goods_storage'] = $goods_online_info['goods_storage'];
                $cart_list[$key]['goods_storage_alarm'] = $goods_online_info['goods_storage_alarm'];
                $cart_list[$key]['is_fcode'] = $goods_online_info['is_fcode'];
                $cart_list[$key]['have_gift'] = $goods_online_info['have_gift'];
                if ($cart_info['goods_num'] > $goods_online_info['goods_storage']) {
                    $cart_list[$key]['storage_state'] = false;
                }

                $cart_list[$key]['goods_total'] = ncPriceFormat($cart_list[$key]['goods_price'] * $cart_list[$key]['goods_num']);
                $cart_list[$key]['groupbuy_info'] = $goods_online_info['groupbuy_info'];
                $cart_list[$key]['xianshi_info'] = $goods_online_info['xianshi_info'];

                //预定信息
                $cart_list[$key]['is_book'] = $goods_online_info['is_book'];
                $cart_list[$key]['book_down_payment'] = $goods_online_info['book_down_payment'];
                $cart_list[$key]['book_final_payment'] = $goods_online_info['book_final_payment'];
                $cart_list[$key]['book_down_time'] = $goods_online_info['book_down_time'];
                $cart_list[$key]['is_chain'] = $goods_online_info['is_chain'];

                //规格
                $_tmp_name = unserialize($goods_online_info['spec_name']);
                $_tmp_value = unserialize($goods_online_info['goods_spec']);
                if (is_array($_tmp_name) && is_array($_tmp_value)) {
                    $_tmp_name = array_values($_tmp_name);
                    $_tmp_value = array_values($_tmp_value);
                    foreach ($_tmp_name as $sk => $sv) {
                        $cart_list[$key]['goods_spec'] .= $sv . '：' . $_tmp_value[$sk] . '，';
                    }
                    $cart_list[$key]['goods_spec'] = rtrim($cart_list[$key]['goods_spec'], '，');
                }
                if (array_key_exists('sole_info', $goods_online_info)) {
                    $cart_list[$key]['sole_info'] = $goods_online_info['sole_info'];
                }
                //消费者保障服务
                $cart_list[$key]['contractlist'] = $goods_online_info['contractlist'];
            } else {
                //如果商品下架
                $cart_list[$key]['state'] = false;
                $cart_list[$key]['storage_state'] = false;
            }
        }

        return $cart_list;
    }


    public function getGoodsOnlineInfo($goods_id, $quantity)
    {
        $goods_info = $this->_getGoodsOnlineInfo($goods_id, $quantity);

        return $goods_info;
    }

    public function calcCartList($cart_list)
    {
        if (empty($cart_list) || !is_array($cart_list)) return array($cart_list, array(), 0);
        $goods_total = 0;
        foreach ($cart_list as $key => $cart_info) {
            $cart_info['goods_total'] = ncPriceFormat($cart_info['goods_price'] * $cart_info['goods_num']);
            $cart_info['goods_image_url'] = cthumb($cart_info['goods_image']);
            $goods_total += floatval($cart_info['goods_total']);
            $cart_list[$key] = $cart_info;
        }
        $goods_total = ncPriceFormat($goods_total);
        return array($cart_list, $goods_total);
    }


    private function _getGoodsOnlineInfo($goods_id, $quantity)
    {
        /** @var b2b_goodsModel $model_goods */
        $model_goods = Model('b2b_goods');
        //取目前在售商品
        $goods_info = $model_goods->getGoodsInfoByID($goods_id);
        if (empty($goods_info)) {
            return null;
        }

        $new_array = array();
        $new_array['goods_num'] = $goods_info['is_fcode'] ? 1 : $quantity;
        $new_array['goods_id'] = $goods_id;
        $new_array['goods_commonid'] = $goods_info['goods_commonid'];
        $new_array['gc_id'] = $goods_info['gc_id'];
        $new_array['store_id'] = $goods_info['store_id'];
        $new_array['goods_name'] = $goods_info['common_info']['goods_name'];
        $new_array['supplier_id'] = $goods_info['common_info']['supplier_id'];
        $new_array['gc_id'] = $goods_info['common_info']['gc_id'];

        $new_array['goods_price'] = $goods_info['goods_price'];
        $new_array['store_name'] = $goods_info['store_name'];
        $new_array['goods_image'] = $goods_info['goods_image'];
        $new_array['transport_id'] = $goods_info['transport_id'];
        $new_array['goods_freight'] = $goods_info['goods_freight'];
        $new_array['goods_cost'] = $goods_info['goods_cost'];
        $new_array['tax_input'] = $goods_info['tax_input'];
        $new_array['tax_output'] = $goods_info['tax_output'];
        $new_array['goods_vat'] = $goods_info['goods_vat'];
        $new_array['goods_storage'] = $goods_info['goods_storage'];
        $new_array['goods_storage_alarm'] = $goods_info['goods_storage_alarm'];
        $new_array['is_fcode'] = $goods_info['is_fcode'];
        $new_array['have_gift'] = $goods_info['have_gift'];
        $new_array['state'] = true;
        $new_array['storage_state'] = intval($goods_info['goods_storage']) < intval($quantity) ? false : true;
        $new_array['groupbuy_info'] = $goods_info['groupbuy_info'];
        $new_array['xianshi_info'] = $goods_info['xianshi_info'];
        $new_array['is_chain'] = $goods_info['is_chain'];

        //预定信息
        $new_array['is_book'] = $goods_info['is_book'];
        $new_array['book_down_payment'] = $goods_info['book_down_payment'];
        $new_array['book_final_payment'] = $goods_info['book_final_payment'];
        $new_array['book_down_time'] = $goods_info['book_down_time'];

        //填充必要下标，方便后面统一使用购物车方法与模板
        //cart_id=goods_id,优惠套装目前只能进购物车,不能立即购买
        $new_array['cart_id'] = $goods_id;
        $new_array['bl_id'] = 0;

        //规格
        $_tmp_name = unserialize($goods_info['spec_name']);
        $_tmp_value = unserialize($goods_info['goods_spec']);
        if (is_array($_tmp_name) && is_array($_tmp_value)) {
            $_tmp_name = array_values($_tmp_name);
            $_tmp_value = array_values($_tmp_value);
            foreach ($_tmp_name as $sk => $sv) {
                $new_array['goods_spec'] .= $sv . '：' . $_tmp_value[$sk] . '，';
            }
            $new_array['goods_spec'] = rtrim($new_array['goods_spec'], '，');
        }
        if (array_key_exists('sole_info', $goods_info)) {
            $new_array['sole_info'] = $goods_info['sole_info'];
        }
        $new_array['contractlist'] = $goods_info['contractlist'];
        return $new_array;
    }

    private function _genOrderSn()
    {
        $i = rand(0, 99999);
        do {
            if (99999 == $i) {
                $i = 0;
            }
            $i++;
            $order_no = date('ymdHi') . str_pad($i, 5, '0', STR_PAD_LEFT);
            $condition = array('order_no' => $order_no);
            $row = Model("b2b_order_pay")->where($condition)->find();
        } while ($row);
        return $order_no;
    }

    private function _makePaySn($member_id = null, $ordersn = null)
    {
        if ($ordersn) {
            $ordersn = str_pad($ordersn, 15, time());
            $i = rand(0, 999);
            if (999 == $i) {
                $i = 0;
            }

            $pay_sn = $ordersn . str_pad($i, 3, '0', STR_PAD_LEFT);

        } else {
            do {
                $pay_sn = mt_rand(10, 99)
                    . sprintf('%010d', time() - 946656000)
                    . sprintf('%03d', (float)microtime() * 1000)
                    . sprintf('%03d', (int)$member_id % 1000);
                $condition = array('pay_sn' => $pay_sn);
                $row = Model("b2b_order_pay")->where($condition)->find();
            } while ($row);
        }
        return $pay_sn;
    }


    /**
     * 购买第一步
     * @param array $cart_id
     * @param boolean $ifcart
     * @param integer $member_id
     * @param integer $store_id
     * @return mixed
     */
    public function buyStep1($cart_id, $ifcart, $member_id, $store_id)
    {


        //得到购买商品信息
        if ($ifcart) {
            $result = $this->getCartList($cart_id, $member_id);
        } else {
            $result = $this->getGoodsList($cart_id, $member_id, $store_id);
        }
        if (!$result['state']) {
            return $result;
        }

        //得到页面所需数据：收货地址、发票、代金券、预存款、商品列表等信息
        $result = $this->getBuyStep1Data($member_id, $result['data']);
        return $result;
    }

    /**
     *
     * 第一步：处理购物车
     *
     * @param array $cart_id 购物车
     * @param int $member_id 会员编号
     * @return mixed
     */

    public function getCartList($cart_id, $member_id)
    {
        /** @var b2b_cartModel $model_cart */
        $model_cart = Model('b2b_cart');

        //取得POST ID和购买数量
        $buy_items = $this->_parseItems($cart_id);
        if (empty($buy_items)) {
            return callback(false, '所购商品无效');
        }

        if (count($buy_items) > 100) {
            return callback(false, '一次最多只可购买100种商品');
        }

        //购物车列表
        $condition = array('cart_id' => array('in', array_keys($buy_items)), 'buyer_id' => $member_id);
        $cart_list = $model_cart->listCart('db', $condition);


        //购物车列表 [得到最新商品属性及促销信息]
        $cart_list = $this->getGoodsCartList($cart_list);


        //商品列表 [优惠套装子商品与普通商品同级罗列]
        $goods_list = $this->_getGoodsList($cart_list);

        /*        //以店铺下标归类
                $cart_list = $this->_getStoreCartList($cart_list);
                if (empty($cart_list) || !is_array($cart_list)) {
                    return callback(false, '提交数据错误');
                }*/

        return callback(true, '', array(
            'goods_list' => $goods_list,
            'cart_list' => $cart_list,
        ));
    }


    /**
     * 第一步：处理立即购买
     * @param $cart_id
     * @param $member_id
     * @param $store_id
     * @param array $orderdiscounts
     * @return mixed
     */
    public function getGoodsList($cart_id, $member_id, $store_id, $orderdiscounts = array())
    {

        //取得POST ID和购买数量
        $buy_items = $this->_parseItems($cart_id);
        if (empty($buy_items)) {
            return callback(false, '所购商品无效');
        }

        $goods_id = key($buy_items);
        $quantity = current($buy_items);

        //商品信息[得到最新商品属性及促销信息]
        $goods_info = $this->getGoodsOnlineInfo($goods_id, intval($quantity));
        if (empty($goods_info)) {
            return callback(false, '商品已下架或不存在');
        }

        //不能购买自己店铺的商品
        if ($goods_info['store_id'] == $store_id) {
            return callback(false, '不能购买自己店铺的商品');
        }

        //进一步处理数组
        $cart_list = array();
        $goods_list = array();
        $goods_list[0] = $cart_list[0] = $goods_info;

        return callback(true, '', array('goods_list' => $goods_list, 'cart_list' => $cart_list));
    }

    /**
     * 购买第一步：返回商品、促销、地址、发票等信息，然后交前台抛出
     * @param unknown $member_id
     * @param unknown $data 商品信息
     * @return
     */
    public function getBuyStep1Data($member_id, $data, $orderdiscount = 0)
    {

        $goods_list = $data['goods_list'];
        $cart_list = $data['cart_list'];

        //商品金额计算(分别对每个商品/优惠套装小计、每个店铺小计)
        list($cart_list, $goods_total) = $this->calcCartList($cart_list);


        //定义返回数组
        $result = array();

        // 加价购

        $result['cart_list'] = $cart_list;
        $result['goods_total'] = $goods_total;


        //输出用户默认收货地址
        /** @var b2b_addressModel $addressModel */
        $addressModel = Model('b2b_address');
        $result['address_info'] = $addressModel->getDefaultAddressInfo(array('member_id' => $member_id));

        //输出有货到付款时，在线支付和货到付款及每种支付下商品数量和详细列表
        //$pay_goods_list = $this->_logic_buy_1->getOfflineGoodsPay($goods_list);
        $result['pay_goods_list'] = array('online' => array(), 'offline' => $goods_list);
        $result['ifshow_offpay'] = true;
        //如果所购商品只支持线上支付，支付方式不允许修改
        $result['deny_edit_payment'] = false;

        //发票 :只有所有商品都支持增值税发票才提供增值税发票
        foreach ($goods_list as $goods) {
            if (!intval($goods['goods_vat'])) {
                $vat_deny = true;
                break;
            }
        }
        //不提供增值税发票时抛出true(模板使用)
        $result['vat_deny'] = $vat_deny;
        $result['vat_hash'] = $this->buyEncrypt($result['vat_deny'] ? 'deny_vat' : 'allow_vat', $member_id);

        //输出默认使用的发票信息
        $inv_info = Model('b2b_invoice')->getDefaultInvInfo(array('member_id' => $member_id));
        if ($inv_info['inv_state'] == '2' && !$vat_deny) {
            $inv_info['content'] = '增值税发票 ' . $inv_info['inv_company'] . ' ' . $inv_info['inv_code'] . ' ' . $inv_info['inv_reg_addr'];
        } elseif ($inv_info['inv_state'] == '2' && $vat_deny) {
            $inv_info = array();
            $inv_info['content'] = '不需要发票';
        } elseif (!empty($inv_info)) {
            $inv_info['content'] = '普通发票 ' . $inv_info['inv_title'] . ' ' . $inv_info['inv_content'];
        } else {
            $inv_info = array();
            $inv_info['content'] = '不需要发票';
        }
        $result['inv_info'] = $inv_info;


        return callback(true, '', $result);
    }

    /**
     * 购买第二步
     * @param array $post
     * @param int $member_id
     * @param string $member_name
     * @param string $member_email
     * @return array
     */
    public function buyStep2($post, $member_id, $member_name, $member_email)
    {

        $this->_member_info['member_id'] = $member_id;
        $this->_member_info['member_name'] = $member_name;
        $this->_member_info['member_email'] = $member_email;
        $this->_post_data = $post;

        try {

            /** @var b2b_orderModel $model */
            $model = Model('b2b_order');
            $model->beginTransaction();

            //第1步 表单验证
            $this->_createOrderStep1();

            //第2步 得到购买商品信息
            $this->_createOrderStep2();

            //第3步 得到购买相关金额计算等信息
            $this->_createOrderStep3();

            //第4步 生成订单
            $this->_createOrderStep4();

            //第6步 订单后续处理
            $this->_createOrderStep6();
            /** 第7步 订单二次开发处理 */
            $this->_createOrderStep7();

            $model->commit();
            return callback(true, '', $this->_order_data);

        } catch (Exception $e) {
            $model->rollback();
            return callback(false, $e->getMessage());
        }

    }

    /**
     * 删除购物车商品
     * @param unknown $ifcart
     * @param unknown $cart_ids
     */
    public function delCart($ifcart, $member_id, $cart_ids)
    {
        /** @var b2b_cartModel $cartModel */
        $cartModel = Model('b2b_cart');
        if (!$ifcart || !is_array($cart_ids)) return;
        $cart_id_str = implode(',', $cart_ids);
        if (preg_match('/^[\d,]+$/', $cart_id_str)) {
            $del = $cartModel->delCart('db', array('buyer_id' => $member_id, 'cart_id' => array('in', $cart_ids)));
        }
    }

    /**
     * 根据门店自提站ID计算商品库存，返回库存不足的商品ID
     * @param unknown $chain_id
     * @param unknown $product
     * @return NULL
     */
    public function changeChain($chain_id = 0, $product = '')
    {
        $chain_id = intval($chain_id);
        if ($chain_id <= 0) return null;
        if (strpos($product, '-') !== false) {
            $product = explode('-', $product);
        } else {
            $product = array($product);
        }
        if (empty($product) || !is_array($product)) return null;
        $product = $this->_parseItems($product);
        $condition = array();
        $condition['goods_id'] = array('in', array_keys($product));
        $condition['chain_id'] = $chain_id;
        $list = Model('chain_stock')->getChainStockList($condition);
        if ($list) {
            $_tmp = array();
            foreach ($list as $v) {
                $_tmp[$v['goods_id']] = $v['stock'];
            }
            foreach ($product as $goods_id => $num) {
                if ($_tmp[$goods_id] >= $num) {
                    unset($product[$goods_id]);
                }
            }
        }
        $data = array();
        $data['state'] = 'success';
        $data['product'] = array_keys($product);
        return $data;
    }

    /**
     * 选择不同地区时，异步处理并返回每个店铺总运费以及本地区是否能使用货到付款
     * 如果店铺统一设置了满免运费规则，则运费模板无效
     * 如果店铺未设置满免规则，且使用运费模板，按运费模板计算，如果其中有商品使用相同的运费模板,作为一种商品算运费
     * 如果未找到运费模板，按免运费处理
     * 如果没有使用运费模板，商品运费按快递价格计算，运费不随购买数量增加
     */
    public function changeAddr($freight_hash, $city_id, $area_id, $member_id)
    {
        //$city_id计算运费模板,$area_id计算货到付款
        $city_id = intval($city_id);
        $area_id = intval($area_id);
        if ($city_id <= 0 || $area_id <= 0) return null;

        //将hash解密，得到运费信息(店铺ID，运费,运费模板ID),hash内容有效期为1小时
        $freight_list = $this->buyDecrypt($freight_hash, $member_id);
        //算运费
        list($store_freight_list, $no_send_tpl_ids) = $this->_logic_buy_1->calcStoreFreight($freight_list, $city_id);
        $data = array();
        $data['state'] = empty($store_freight_list) && empty($no_send_tpl_ids) ? 'fail' : 'success';
        $data['content'] = $store_freight_list;
        $data['no_send_tpl_ids'] = $no_send_tpl_ids;

        $offline_store_id_array = Model('store')->getOwnShopIds();
        $order_platform_store_ids = array();

        if (is_array($freight_list['iscalced']))
            foreach (array_keys($freight_list['iscalced']) as $k)
                if (in_array($k, $offline_store_id_array))
                    $order_platform_store_ids[$k] = null;

        if (is_array($freight_list['nocalced']))
            foreach (array_keys($freight_list['nocalced']) as $k)
                if (in_array($k, $offline_store_id_array))
                    $order_platform_store_ids[$k] = null;

        if ($order_platform_store_ids) {
            $allow_offpay_batch = Model('offpay_area')->checkSupportOffpayBatch($area_id, array_keys($order_platform_store_ids));

            //JS验证使用
            $data['allow_offpay'] = array_filter($allow_offpay_batch) ? '1' : '0';
            $data['allow_offpay_batch'] = $allow_offpay_batch;
        } else {
            //JS验证使用
            $data['allow_offpay'] = '0';
            $data['allow_offpay_batch'] = array();
        }

        //PHP验证使用
        $data['offpay_hash'] = $this->buyEncrypt($data['allow_offpay'] ? 'allow_offpay' : 'deny_offpay', $member_id);
        $data['offpay_hash_batch'] = $this->buyEncrypt($data['allow_offpay_batch'], $member_id);
        return $data;
    }

    /**
     * 验证F码
     * @param int $goods_commonid
     * @param string $fcode
     * @return array
     */
    public function checkFcode($goods_id, $fcode)
    {
        $fcode_info = Model('goods_fcode')->getGoodsFCode(array('goods_id' => $goods_id, 'fc_code' => $fcode, 'fc_state' => 0));
        if ($fcode_info) {
            return callback(true, '', $fcode_info);
        } else {
            return callback(false, 'F码错误');
        }
    }

    /**
     * 订单生成前的表单验证与处理
     *
     */
    private function _createOrderStep1()
    {
        $post = $this->_post_data;

        //取得商品ID和购买数量
        $input_buy_items = $this->_parseItems($post['cart_id']);

        if (empty($input_buy_items)) {
            throw new Exception('所购商品无效');
        }

        //验证收货地址
        $input_address_id = intval($post['address_id']);
        if ($input_address_id <= 0) {
            throw new Exception('请选择收货地址');
        } else {
            /** @var b2b_addressModel $addressModel */
            $addressModel = Model('b2b_address');
            $input_address_info = $addressModel->getAddressInfo(array('address_id' => $input_address_id));
            if ($input_address_info['member_id'] != $this->_member_info['member_id']) {
                throw new Exception('请选择收货地址');
            }
            if ($input_address_info['dlyp_id']) {
                $input_dlyp_id = $input_address_info['dlyp_id'];
            }
        }
        //收货地址城市编号
        $input_city_id = intval($input_address_info['city_id']);

        //是否开增值税发票
        $input_if_vat = $this->buyDecrypt($post['vat_hash'], $this->_member_info['member_id']);
        if (!in_array($input_if_vat, array('allow_vat', 'deny_vat'))) {
            throw new Exception('订单保存出现异常[值税发票出现错误]，请重试');
        }

        //付款方式:在线支付/货到付款(online/offline)
        if (!in_array($post['pay_name'], array('online', 'offline', 'chain'))) {
            throw new Exception('付款方式错误，请重新选择');
        }
        $input_pay_name = $post['pay_name'];

        // 验证发票信息
        if (!empty($post['invoice_id'])) {
            $input_invoice_id = intval($post['invoice_id']);
            if ($input_invoice_id > 0) {
                $input_invoice_info = Model('b2b_invoice')->getinvInfo(array('inv_id' => $input_invoice_id));
                if ($input_invoice_info['member_id'] != $this->_member_info['member_id']) {
                    throw new Exception('请正确填写发票信息');
                }
            }
        }


        //保存数据
        $this->_order_data['input_buy_items'] = $input_buy_items;
        $this->_order_data['input_city_id'] = $input_city_id;
        $this->_order_data['input_pay_name'] = $input_pay_name;
        $this->_order_data['input_pay_message'] = $post['pay_message'];
        $this->_order_data['input_address_info'] = $input_address_info;
        $this->_order_data['input_dlyp_id'] = $input_dlyp_id;
        $this->_order_data['input_invoice_info'] = $input_invoice_info;
        $this->_order_data['order_from'] = $post['order_from'] == 2 ? 2 : 1;
        $this->_order_data['input_is_book'] = $post['is_book'];

    }

    /**
     * 得到购买商品信息
     *
     */
    private function _createOrderStep2()
    {
        $post = $this->_post_data;
        $input_buy_items = $this->_order_data['input_buy_items'];
        $input_is_book = $this->_order_data['input_is_book'];
        if ($post['ifcart']) {
            //购物车列表
            /** @var b2b_cartModel $model_cart */
            $model_cart = Model('b2b_cart');
            $condition = array('cart_id' => array('in', array_keys($input_buy_items)), 'buyer_id' => $this->_member_info['member_id']);
            $cart_list = $model_cart->listCart('db', $condition);

            //购物车列表 [得到最新商品属性及促销信息]
            $cart_list = $this->getGoodsCartList($cart_list);

            //商品列表 [优惠套装子商品与普通商品同级罗列]
            $goods_list = $this->_getGoodsList($cart_list);

            //以店铺下标归类
            //$cart_list = $this->_getStoreCartList($cart_list);
            $input_is_book = false;
        } else {

            //来源于直接购买
            $goods_id = key($input_buy_items);
            $quantity = current($input_buy_items);

            //商品信息[得到最新商品属性及促销信息]
            $goods_info = $this->getGoodsOnlineInfo($goods_id, intval($quantity));
            if (empty($goods_info)) {
                throw new Exception('商品已下架或不存在');
            }

            $this->_order_data['input_is_book'] = $input_is_book;

            //进一步处理数组
            $cart_list = array();
            $goods_list = array();
            $goods_list[0] = $cart_list[0] = $goods_info;

        }

        //保存数据
        $this->_order_data['goods_list'] = $goods_list;
        $this->_order_data['cart_list'] = $cart_list;

    }

    /**
     * 得到购买相关金额计算等信息
     *
     */
    private function _createOrderStep3()
    {
        $goods_list = $this->_order_data['goods_list'];
        $cart_list = $this->_order_data['cart_list'];
        $input_voucher_list = $this->_order_data['input_voucher_list'];
        $input_city_id = $this->_order_data['input_city_id'];
        $input_rpt_info = $this->_order_data['input_rpt_info'];
        $input_is_book = $this->_order_data['input_is_book'];

        //商品金额计算(分别对每个商品/优惠套装小计、每个店铺小计)
        list($cart_list, $goods_total) = $this->calcCartList($cart_list);


        //保存数据
        $this->_order_data['goods_total'] = $goods_total;
        $this->_order_data['cart_list'] = $cart_list;
        $this->_order_data['input_voucher_list'] = $input_voucher_list;
        $this->_order_data['input_rpt_info'] = $input_rpt_info;
    }

    /**
     * 生成订单
     * @param array $input
     * @throws Exception
     * @return array array(支付单sn,订单列表)
     */
    private function _createOrderStep4()
    {
        $post = $this->_post_data;
        /* 开始查询地址信息 */
        $goodsAddressIds = $post['goods_address_id'];
        $addressIds = array();
        foreach ($goodsAddressIds as $value) {
            $addressIds = array_merge($addressIds, array_values($value));
        }
        $addressList = Model('b2b_address')->where(array('address_id' => array('in', $addressIds)))->select();
        $addressList = array_under_reset($addressList, 'address_id');
        /* 完成查询地址信息 */

        extract($this->_order_data);

        $member_id = $this->_member_info['member_id'];
        $member_name = $this->_member_info['member_name'];
        $member_email = $this->_member_info['member_email'];
        $member_level = $this->_member_info['member_level'];

        /** @var b2b_orderModel $model_order */
        $model_order = Model('b2b_order');

        //存储生成的订单数据
        $order_list = array();
        //存储通知信息
        $notice_list = array();
        //支付方式

        //ecstore订单ID算法
        $order_no = $this->_genOrderSn();
        $pay_sn = $this->_makePaySn($member_id, $order_no);

        $order_pay = array();
        $order_pay['pay_sn'] = $pay_sn;
        $order_pay['buyer_id'] = $member_id;
        $order_pay['order_no'] = $order_no;
        $order_pay_id = $model_order->addOrderPay($order_pay);
        if (!$order_pay_id) {
            throw new Exception('订单保存失败[未生成支付单]');
        }

        //收货人信息
        list($reciver_info, $reciver_name, $reciver_phone) = $this->_logic_buy_1->getReciverAddr($input_address_info);

        $num = 0;
        /** @var storeModel $storeModel */
        $storeModel = Model('store');

        $supplierCart = array();
        foreach ($cart_list as $goods) {
            if (!isset($supplierCart[$goods['supplier_id']])) $supplierCart[$goods['supplier_id']] = array();
            $supplierCart[$goods['supplier_id']][] = $goods;
        }
        foreach ($supplierCart as $supplierId => $cartGoods) {

            //每种商品的优惠金额累加保存入 $promotion_sum
            $goodsCount = count($cartGoods);
            $goodsCount = $goodsCount > 0 ? $goodsCount : 1;
            /** @var float $rptSum 每种商品的红包额累计 */
            $rptSum = 0;
            $rptBillSum = 0;

            $order = array();
            $order_common = array();
            $order_goods = array();
            $order_address = array();

            $num++;
            //$order['order_sn'] = $this->_logic_buy_1->makeOrderSn($order_pay_id);
            $order['order_sn'] = $order_no . sprintf("%03d", $num);
            $order['pay_sn'] = $pay_sn;
            $order['store_id'] = $supplierId;
            $supplier_info = Model('b2b_supplier')->getSupplierInfo(array('supplier_id' => $supplierId));
            $order['store_name'] = $supplier_info['company_name'];
            $order['buyer_id'] = $member_id;
            $order['buyer_name'] = $member_name;
            $order['buyer_email'] = $member_email;
            $order['buyer_phone'] = is_numeric($reciver_phone) ? $reciver_phone : 0;
            $order['add_time'] = TIMESTAMP;
            $order['payment_code'] = $input_pay_name;
            $order['order_state'] = $input_pay_name == 'offline' ? ORDER_STATE_PAY : ORDER_STATE_NEW;
            $order['order_amount'] = $goods_total;
            $order['shipping_fee'] = 0;
            $order['goods_amount'] = $order['order_amount'] - $order['shipping_fee'];
            $order['order_from'] = 'pc';
            $order['order_type'] = 1;
            $order['chain_id'] = 0;
            $order['rpt_amount'] = 0;
            $order['rpt_bill'] = 0;

            if (empty($order['payment_code'])) $order['payment_code'] = 'online';
            $order_id = $model_order->addOrder($order);
            if (!$order_id) {
                throw new Exception('订单保存失败[未生成订单数据]');
            }
            $order['order_id'] = $order_id;
            $order_list[$order_id] = $order;

            $order_common['order_id'] = $order_id;
            $order_common['store_id'] = 0;
            $order_common['order_message'] = $input_pay_name;


            //订单总优惠金额（代金券，满减，平台红包）
            $order_common['promotion_total'] = 0;

            $order_common['reciver_info'] = $reciver_info;
            $order_common['reciver_name'] = $reciver_name;
            $order_common['reciver_city_id'] = $input_city_id;

            //发票信息
            $order_common['invoice_info'] = $this->_logic_buy_1->createInvoiceData($input_invoice_info);

            //保存促销信息
            $order_common['promotion_info'] = array();


            //代金券

            $insert = $model_order->addOrderCommon($order_common);
            if (!$insert) {
                throw new Exception('订单保存失败[未生成订单扩展数据]');
            }

            //生成order_goods订单商品数据
            /** @var b2b_goodsModel $goodsModel */
            $goodsModel = Model('b2b_goods');
            $i = 0;
            $goods_buy_quantity = array();
            foreach ($cartGoods as $goods_info) {

//            $goods_buy_quantity[] = array($goods_info['goods_id'] => $goods_info['goods_num']);
                $goods_buy_quantity[$goods_info['goods_id']] = $goods_info['goods_num'];
                if (!$goods_info['state'] || !$goods_info['storage_state']) {
                    throw new Exception('抱歉，部分商品存在下架、变更销售方式或库存不足的情况，请重新选择');
                }

                $goods_invit = $goodsModel->getGoodsInfo(array('goods_id' => $goods_info['goods_id']));
//            v($goods_info);
                //如果不是优惠套装
                $order_goods[$i]['order_id'] = $order_id;
                $order_goods[$i]['goods_id'] = $goods_info['goods_id'];
                $order_goods[$i]['store_id'] = 0;
                $order_goods[$i]['manage_type'] = $supplier_info['manage_type'];
                $order_goods[$i]['goods_name'] = $goods_info['goods_name'].'('.$goods_info['goods_calculate'].')';
                $order_goods[$i]['goods_price'] = $goods_info['goods_price'];
                $order_goods[$i]['goods_pay_price'] = $goods_info['goods_price'] * $goods_info['goods_num'];
                $order_goods[$i]['goods_num'] = $goods_info['goods_num'];
                $order_goods[$i]['goods_image'] = $goods_info['goods_image'];
                $order_goods[$i]['goods_spec'] = $goods_info['goods_spec'];
                $order_goods[$i]['tax_input'] = $goods_info['tax_input'];
                $order_goods[$i]['tax_output'] = $goods_info['tax_output'];
                $order_goods[$i]['supplier_id'] = $goods_info['supplier_id'];
                $order_goods[$i]['goods_cost'] = $goods_invit['goods_cost'] * $goods_info['goods_num'];
                $order_goods[$i]['invite_rates'] = $goods_invit['invite_rate'];
                $order_goods[$i]['gc_id'] = $goods_invit['common_info']['gc_id'];
                $order_goods[$i]['buyer_id'] = $member_id;
                $order_goods[$i]['goods_type'] = 1;

                $order_goods[$i]['commis_rate'] = $goods_invit['common_info']['commis_rate']?$goods_invit['common_info']['commis_rate']:0;
                $order_goods[$i]['gc_id'] = $goods_info['gc_id'];

                //记录消费者保障服务
                $contract_itemid_arr = $goods_info['contractlist'] ? array_keys($goods_info['contractlist']) : array();
                $order_goods[$i]['goods_contractid'] = $contract_itemid_arr ? implode(',', $contract_itemid_arr) : '';

                //计算商品金额
                $goods_total = $goods_info['goods_price'] * $goods_info['goods_num'];
                //计算本件商品优惠金额
                $i++;

                //存储库存报警数据
                if ($goods_info['goods_storage_alarm'] >= ($goods_info['goods_storage'] - $goods_info['goods_num'])) {
                    $param = array();
                    $param['common_id'] = $goods_info['goods_commonid'];
                    $param['sku_id'] = $goods_info['goods_id'];
                    $notice_list['goods_storage_alarm'][$goods_info['store_id']] = $param;
                }
            }


            $this->_order_data['goods_buy_quantity'] = $goods_buy_quantity;
            $insert = $model_order->addOrderGoods($order_goods);

            if (!$insert) {
                throw new Exception('订单保存失败[未生成商品数据]');
            }
            $recList = $model_order->getOrderGoodsList(array('order_id' => $order_id), '*', null, null, 'rec_id desc', null,null,true);
            foreach ($recList as $rec) {

                $addressInfoList = array(
                    'ids' => $post['goods_address_id'][$rec['goods_id']],
                    'address' => $post['goods_address_address'][$rec['goods_id']],
                    'phone' => $post['goods_address_phone'][$rec['goods_id']],
                    'name' => $post['goods_address_name'][$rec['goods_id']],
                    'nums' => $post['address_num'][$rec['goods_id']],
                    'price' => $post['address_price'][$rec['goods_id']],
                );

                //$addressList
                //$order_address
                foreach ($addressInfoList['ids'] as $key => $address_id) {
                    $order_address_item = array();
                    $order_address_item['order_id'] = $order_id;
                    $order_address_item['rec_id'] = $rec['rec_id'];
                    $order_address_item['address_id'] = is_numeric($address_id)?$address_id:0;
                    $order_address_item['buyer_name'] = $addressInfoList['name'][$key];
                    $order_address_item['buyer_phone'] = $addressInfoList['phone'][$key];
                    $order_address_item['buyer_email'] = $member_email;
                    $order_address_item['address'] = $addressInfoList['address'][$key];
                    $order_address_item['area_id'] = is_numeric($address_id)?$addressList[$address_id]['area_id']:0;
                    $order_address_item['city_id'] = is_numeric($address_id)?$addressList[$address_id]['city_id']:0;
                    $order_address_item['rec_num'] = $addressInfoList['nums'][$key];
                    $order_address_item['rec_price'] = $addressInfoList['price'][$key];
                    $order_address[] = $order_address_item;
                }
            }
            //v($order_address);
            $insert = $model_order->table('b2b_order_address')->insertAll($order_address);
            //v($model_order->getLastSql());
            if (!$insert) {
                throw new Exception('订单保存失败[未生成商品收货地址数据]');
            }
            $order_list[$order_id]['goods'] = $order_goods;
        }

        //保存数据
        $this->_order_data['pay_sn'] = $pay_sn;
        $this->_order_data['order_list'] = $order_list;
        $this->_order_data['notice_list'] = $notice_list;
        $this->_order_data['ifgroupbuy'] = false;
        $this->_order_data['ifbook'] = 0;
    }


    /**
     * 订单后续其它处理
     *
     */
    private function _createOrderStep6()
    {
        $ifcart = $this->_post_data['ifcart'];
        $goods_buy_quantity = $this->_order_data['goods_buy_quantity'];
        $input_voucher_list = $this->_order_data['input_voucher_list'];
        $input_rpt_info = $this->_order_data['input_rpt_info'];

        $cart_list = $this->_order_data['cart_list'];
        $input_buy_items = $this->_order_data['input_buy_items'];
        $order_list = $this->_order_data['order_list'];
        $input_address_info = $this->_order_data['input_address_info'];
        $notice_list = $this->_order_data['notice_list'];
        $fc_id = $this->_order_data['fc_id'];
        $ifgroupbuy = $this->_order_data['ifgroupbuy'];
        $ifbook = $this->_order_data['ifbook'];
        $pay_sn = $this->_order_data['pay_sn'];
        $input_dlyp_id = $this->_order_data['input_dlyp_id'];
        $input_chain_id = $this->_order_data['input_chain_id'];

        //变更库存和销量
        /** @var queueLogic $queue */
        $queue = Logic('queue');

        $result = $queue->createB2bOrderUpdateStorage($goods_buy_quantity);
        if (!$result['state']) {
            throw new Exception('订单保存失败[变更库存销量失败]');
        }


        //删除购物车中的商品
        $this->delCart($ifcart, $this->_member_info['member_id'], array_keys($input_buy_items));
        @setNcCookie('b2b_cart_goods_num', '', -3600);


        //发送提醒类信息
        /* if (!empty($notice_list)) {
             foreach ($notice_list as $code => $value) {
                 QueueClient::push('sendStoreMsg', array('code' => $code, 'store_id' => key($value), 'param' => current($value)));
             }
         }*/


        //生成交易快照
        /*$order_id_list = array();
        foreach ($order_list as $order_info) {
            $order_id_list[] = $order_info['order_id'];
        }
        QueueClient::push('createSphot', $order_id_list);*/

    }

    /**
     * 二次开发自定义功能
     */
    private function _createOrderStep7()
    {
    }

    /**
     * 加密
     * @param array /string $string
     * @param int $member_id
     * @return mixed arrray/string
     */
    public function buyEncrypt($string, $member_id)
    {
        $buy_key = sha1(md5($member_id . '&' . MD5_KEY));
        if (is_array($string)) {
            $string = serialize($string);
        } else {
            $string = strval($string);
        }
        return encrypt(base64_encode($string), $buy_key);
    }

    /**
     * 解密
     * @param string $string
     * @param int $member_id
     * @param number $ttl
     */
    public function buyDecrypt($string, $member_id, $ttl = 0)
    {
        $buy_key = sha1(md5($member_id . '&' . MD5_KEY));
        if (empty($string)) return;
        $string = base64_decode(decrypt(strval($string), $buy_key, $ttl));
        return ($tmp = @unserialize($string)) !== false ? $tmp : $string;
    }

    /**
     * 得到所购买的id和数量
     *
     */
    private function _parseItems($cart_id)
    {
        //存放所购商品ID和数量组成的键值对
        $buy_items = array();
        if (is_array($cart_id)) {
            foreach ($cart_id as $value) {
                if (preg_match_all('/^(\d{1,10})\|(\d{1,6})$/', $value, $match)) {
                    if (intval($match[2][0]) > 0) {
                        $buy_items[$match[1][0]] = $match[2][0];
                    }
                }
            }
        }
        return $buy_items;
    }

    /**
     * 从购物车数组中得到商品列表
     * @param unknown $cart_list
     */
    private function _getGoodsList($cart_list)
    {
        if (empty($cart_list) || !is_array($cart_list)) return $cart_list;
        $goods_list = array();
        $i = 0;
        foreach ($cart_list as $key => $cart) {
            if (!$cart['state'] || !$cart['storage_state']) continue;
            //购买数量
            $quantity = $cart['goods_num'];
            if (!intval($cart['bl_id'])) {
                //如果是普通商品
                $goods_list[$i]['goods_num'] = $quantity;
                $goods_list[$i]['goods_id'] = $cart['goods_id'];
                $goods_list[$i]['store_id'] = $cart['store_id'];
                $goods_list[$i]['gc_id'] = $cart['gc_id'];
                $goods_list[$i]['goods_name'] = $cart['goods_name'].'('.$cart['goods_calculate'].')';
                $goods_list[$i]['goods_price'] = $cart['goods_price'];
                $goods_list[$i]['store_name'] = $cart['store_name'];
                $goods_list[$i]['goods_image'] = $cart['goods_image'];
                $goods_list[$i]['transport_id'] = $cart['transport_id'];
                $goods_list[$i]['goods_freight'] = $cart['goods_freight'];
                $goods_list[$i]['goods_vat'] = $cart['goods_vat'];
                $goods_list[$i]['goods_cost'] = $cart['goods_cost'];
                $goods_list[$i]['tax_input'] = $cart['tax_input'];
                $goods_list[$i]['tax_output'] = $cart['tax_output'];
                $goods_list[$i]['is_fcode'] = $cart['is_fcode'];
                $goods_list[$i]['bl_id'] = 0;
                $i++;
            } else {
                //如果是优惠套装商品
                foreach ($cart['bl_goods_list'] as $bl_goods) {
                    $goods_list[$i]['goods_num'] = $quantity;
                    $goods_list[$i]['goods_id'] = $bl_goods['goods_id'];
                    $goods_list[$i]['store_id'] = $cart['store_id'];
                    $goods_list[$i]['gc_id'] = $bl_goods['gc_id'];
                    $goods_list[$i]['goods_name'] = $bl_goods['goods_name'].'('.$cart['goods_calculate'].')';
                    $goods_list[$i]['goods_price'] = $bl_goods['goods_price'];
                    $goods_list[$i]['store_name'] = $bl_goods['store_name'];
                    $goods_list[$i]['goods_image'] = $bl_goods['goods_image'];
                    $goods_list[$i]['transport_id'] = $bl_goods['transport_id'];
                    $goods_list[$i]['goods_freight'] = $bl_goods['goods_freight'];
                    $goods_list[$i]['goods_vat'] = $bl_goods['goods_vat'];
                    $goods_list[$i]['goods_cost'] = $bl_goods['goods_cost'];
                    $goods_list[$i]['tax_input'] = $bl_goods['tax_input'];
                    $goods_list[$i]['tax_output'] = $bl_goods['tax_output'];
                    $goods_list[$i]['bl_id'] = $cart['bl_id'];
                    $i++;
                }
            }
        }
        return $goods_list;
    }

    /**
     * 将下单商品列表转换为以店铺ID为下标的数组
     *
     * @param array $cart_list
     * @return array
     */
    private function _getStoreCartList($cart_list)
    {
        if (empty($cart_list) || !is_array($cart_list)) return $cart_list;
        $new_array = array();
        foreach ($cart_list as $cart) {
            $new_array[$cart['store_id']][] = $cart;
        }
        return $new_array;
    }

    /**
     * 本次下单是否需要码及F码合法性
     * 无需使用F码，返回 true
     * 需要使用F码，返回($fc_id/false)
     */
    private function _checkFcode($goods_list, $fcode)
    {
        foreach ($goods_list as $k => $v) {
            if ($v['is_fcode'] == 1) {
                $is_fcode = true;
                break;
            }
        }
        if (!$is_fcode) return true;
        if (empty($fcode) || count($goods_list) > 1) {
            return false;
        }
        $goods_info = $goods_list[0];
        $fcode_info = $this->checkFcode($goods_info['goods_id'], $fcode);
        if ($fcode_info['state']) {
            return intval($fcode_info['data']['fc_id']);
        } else {
            return false;
        }
    }

    /**
     * 验证商品是否支持自提
     * @param unknown $goods_list
     * @return boolean
     */
    private function _checkChain($goods_list)
    {
        if (empty($goods_list) || !is_array($goods_list)) return false;
        $_flag = true;
        foreach ($goods_list as $goods_info) {
            if (!$goods_info['is_chain']) {
                $_flag = false;
                break;
            }
        }
        return $_flag;
    }
}
