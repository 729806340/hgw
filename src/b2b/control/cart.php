<?php
/**
 * 采购清单操作
 * * @汉购网 (c) 2015-2018 ShopWWI Inc. (http://www.hangowa.com)
 * @license    http://www.shopwwi.c om
 * @link       交流群号：
 * @since      汉购网提供技术支持 授权请购买shopnc授权
 */


defined('ByShopWWI') or exit('Access Invalid!');

class cartControl extends BaseBuyControl
{
    public function __construct()
    {
        parent::__construct();
        Language::read('home_cart_index');
        $op = isset($_GET['op']) ? $_GET['op'] : $_POST['op'];
        //允许不登录就可以访问的op
        $op_arr = array('ajax_load', 'add', 'del');
        if (!in_array($op, $op_arr) && !$_SESSION['member_id']) {
            redirect(urlLogin('login', 'index', array('ref_url' => request_uri())));
        }
        Tpl::output('hidden_rtoolbar_cart', 1);
    }

    /**
     * 采购清单首页
     */
    public function indexOp()
    {
        /** @var b2b_cartModel $model_cart */
        $model_cart = Model('b2b_cart');
        /** @var b2b_buyLogic $logic_buy */
        $logic_buy = logic('b2b_buy');
        $model=new Model();
        //采购清单列表
        $cart_list = $model_cart->listCart('db', array('buyer_id' => $_SESSION['member_id']));
        // 采购清单列表 [得到最新商品属性及促销信息]
       $cart_list = $logic_buy->getGoodsCartList($cart_list);
        //采购清单商品以店铺ID分组显示,并计算商品小计,店铺小计与总价由JS计算得出
        /*$store_cart_list = array();
        foreach ($cart_list as $cart) {
            $cart['goods_total'] = ncPriceFormat($cart['goods_price'] * $cart['goods_num']);
            $store_cart_list[$cart['store_id']][] = $cart;
        }
        Tpl::output('store_cart_list',$store_cart_list);*/
        Tpl::output('cart_list', $cart_list);

        //店铺信息
        /*$store_list = Model('store')->getStoreMemberIDList(array_keys($store_cart_list));
        Tpl::output('store_list',$store_list);*/

        //标识 购买流程执行第几步
        Tpl::output('buy_step', 'step1');

        // 小能客服系统
        Tpl::output('is_cart', 1);
        $_list = "";
        $cartprice = 0;
        if (count($cart_list)) {
            foreach ((array)$cart_list as $val) {
                $_list[] = array(
                    'id' => $val['goods_id'],
                    'count' => (string)$val['goods_num'],
                    'name' => (string)$val['goods_name'],
                    'imageurl' => cthumb($val['goods_image'], 60, $val['store_id']),
                    'url' => (string)$val['goods_id'],
                    'siteprice' => $val['goods_price'],
                    // 'sellerid'=> '',
                );
                $cartprice += $val['goods_total'];
            }
            $_carts = json_encode($_list);
        }
        Tpl::output('items', $_carts);
        Tpl::output('cartprice', $cartprice);

        Tpl::showpage(empty($cart_list) ? 'cart_empty' : 'cart');
    }

    public function testOp()
    {
        $_SESSION['member_id'] = 202204;
        $this->indexOp();
    }


    /**
     * 异步查询采购清单
     */
    public function ajax_loadOp()
    {
        $model_cart = Model('cart');
        if ($_SESSION['member_id']) {
            //登录后
            $cart_list = $model_cart->listCart('db', array('buyer_id' => $_SESSION['member_id']));
            $cart_array = array();
            if (!empty($cart_list)) {
                foreach ($cart_list as $k => $cart) {
                    $cart_array['list'][$k]['cart_id'] = $cart['cart_id'];
                    $cart_array['list'][$k]['goods_id'] = $cart['goods_id'];
                    $cart_array['list'][$k]['goods_name'] = $cart['goods_name'];
                    $cart_array['list'][$k]['goods_price'] = $cart['goods_price'];
                    $cart_array['list'][$k]['goods_image'] = thumb($cart, 60);
                    $cart_array['list'][$k]['goods_num'] = $cart['goods_num'];
                    $cart_array['list'][$k]['goods_url'] = urlShop('goods', 'index', array('goods_id' => $cart['goods_id']));
                }
            }
        } else {
            //登录前
            $cart_list = $model_cart->listCart('cookie');
            foreach ($cart_list as $key => $cart) {
                $value = array();
                $value['cart_id'] = $cart['goods_id'];
                $value['goods_id'] = $cart['goods_id'];
                $value['goods_name'] = $cart['goods_name'];
                $value['goods_price'] = $cart['goods_price'];
                $value['goods_num'] = $cart['goods_num'];
                $value['goods_image'] = thumb($cart, 60);
                $value['goods_url'] = urlShop('goods', 'index', array('goods_id' => $cart['goods_id']));
                $cart_array['list'][] = $value;
            }
        }
        setNcCookie('b2b_cart_goods_num', $model_cart->cart_goods_num, 2 * 3600);
        $cart_array['cart_all_price'] = ncPriceFormat($model_cart->cart_all_price);
        $cart_array['cart_goods_num'] = $model_cart->cart_goods_num;
        if ($_GET['type'] == 'html') {
            Tpl::output('cart_list', $cart_array);
            Tpl::showpage('cart_mini', 'null_layout');
        } else {
            $cart_array = strtoupper(CHARSET) == 'GBK' ? Language::getUTF8($cart_array) : $cart_array;
            $json_data = json_encode($cart_array);
            if (isset($_GET['callback'])) {
                $json_data = $_GET['callback'] == '?' ? '(' . $json_data . ')' : $_GET['callback'] . "($json_data);";
            }
            exit($json_data);
        }

    }

    /**
     * 加入采购清单，登录后存入采购清单表
     * 存入COOKIE，由于COOKIE长度限制，最多保存5个商品
     * 未登录不能将优惠套装商品加入采购清单，登录前保存的信息以goods_id为下标
     *
     */
    public function addOp() {
        if(empty($_POST['data'])){
            exit(json_encode(array('status'=>'0','msg'=>'传参数失败')));
        }
       $str=explode(',',$_POST['data']);
        foreach($str as $k=>$v){
            $data1 = explode('-',$v);
            if(is_array($data1)){
                $data[$data1[0]]= $data1[1];
            }
       }

        //插入数据库之前查询用户是否已经存在
        /** @var b2b_cartModel $b2b_cart */
        $b2b_cart=Model('b2b_cart');
        $model=Model();
        $goodsinfo=$model->table('b2b_goods,b2b_goods_common')->on('b2b_goods.goods_commonid=b2b_goods_common.goods_commonid')->join('left')->where(array('b2b_goods.goods_id'=>array('in',array_keys($data))))->select();
        foreach($goodsinfo as $key=>$v){
            if(in_array($v['goods_id'],array_keys($data))){
                $goodsinfo[$key]['goods_num']=$data[$v['goods_id']];
                $goodsinfo[$key]['buyer_id']=isset($_SESSION['member_id']) ? $_SESSION['member_id']:'';
            }
        }
        $res=isset($_SESSION['member_id']) ? $b2b_cart->_addCartInsertAll($goodsinfo):$b2b_cart->_addCartCookie($goodsinfo);
        isset($_SESSION['member_id']) ? $b2b_cart->getCartNum("db",array('buyer_id'=>$_SESSION['member_id'])):$b2b_cart->getCartNum("cookie",'');
        if(!$res){
            exit(json_encode(array('status'=>'0','msg'=>'添加到采购清单失败')));
        }

        exit(json_encode(array('status'=>'1','msg'=>'添加到采购清单成功','cartnum'=>$b2b_cart->cart_goods_num)));
    }

    /**
     * 推荐组合加入采购清单
     */
    public function add_combOp()
    {
        if (!preg_match('/^[\d|]+$/', $_GET['goods_ids'])) {
            exit(json_encode(array('state' => 'false')));
        }

        $model_goods = Model('goods');
        $logic_buy_1 = Logic('buy_1');

        if (!$_SESSION['member_id']) {
            exit(json_encode(array('msg' => '请先登录', 'UTF-8')));
        }

        $goods_id_array = explode('|', $_GET['goods_ids']);

        $model_goods = Model('goods');
        $goods_list = $model_goods->getGoodsOnlineListAndPromotionByIdArray($goods_id_array);

        foreach ($goods_list as $goods) {
            $this->_check_goods($goods, 1);
        }

        //团购
        $logic_buy_1->getGroupbuyCartList($goods_list);

        //限时折扣
        $logic_buy_1->getXianshiCartList($goods_list);

        $model_cart = Model('cart');
        foreach ($goods_list as $goods_info) {
            $cart_info = array();
            $cart_info['store_id'] = $goods_info['store_id'];
            $cart_info['goods_id'] = $goods_info['goods_id'];
            $cart_info['goods_name'] = $goods_info['goods_name'];
            $cart_info['goods_price'] = $goods_info['goods_price'];
            $cart_info['goods_num'] = 1;
            $cart_info['goods_image'] = $goods_info['goods_image'];
            $cart_info['store_name'] = $goods_info['store_name'];
            $quantity = 1;
            //已登录状态，存入数据库,未登录时，存入COOKIE
            if ($_SESSION['member_id']) {
                $save_type = 'db';
                $cart_info['buyer_id'] = $_SESSION['member_id'];
            } else {
                $save_type = 'cookie';
            }
            $insert = $model_cart->addCart($cart_info, $save_type, $quantity);
            if ($insert) {
                //采购清单商品种数记入cookie
                setNcCookie('b2b_cart_goods_num', $model_cart->cart_goods_num, 2 * 3600);
                $data = array('state' => 'true', 'num' => $model_cart->cart_goods_num, 'amount' => ncPriceFormat($model_cart->cart_all_price));
            } else {
                $data = array('state' => 'false');
                exit(json_encode($data));
            }
        }
        exit(json_encode($data));
    }

    /**
     * 检查商品是否符合加入采购清单条件
     * @param unknown $goods
     * @param number $quantity
     */
    private function _check_goods($goods_info, $quantity)
    {
        if (empty($quantity)) {
            exit(json_encode(array('msg' => Language::get('wrong_argument', 'UTF-8'))));
        }
        if (empty($goods_info)) {
            exit(json_encode(array('msg' => Language::get('cart_add_goods_not_exists', 'UTF-8'))));
        }
        if ($goods_info['store_id'] == $_SESSION['store_id']) {
            exit(json_encode(array('msg' => Language::get('cart_add_cannot_buy', 'UTF-8'))));
        }
        if (intval($goods_info['goods_storage']) < 1) {
            exit(json_encode(array('msg' => Language::get('cart_add_stock_shortage', 'UTF-8'))));
        }
        if (intval($goods_info['goods_storage']) < $quantity) {
            exit(json_encode(array('msg' => Language::get('cart_add_too_much', 'UTF-8'))));
        }
        if ($goods_info['is_virtual'] || $goods_info['is_fcode'] || $goods_info['is_presell']) {
            exit(json_encode(array('msg' => '该商品不允许加入采购清单，请直接购买', 'UTF-8')));
        }
    }

    /**
     * 采购清单更新商品数量
     */
    public function updateOp()
    {
        $cart_id = intval(abs($_GET['cart_id']));
        $quantity = intval(abs($_GET['quantity']));

        if (empty($cart_id) || empty($quantity)) {
            exit(json_encode(array('msg' => Language::get('cart_update_buy_fail', 'UTF-8'))));
        }
        /** @var b2b_cartModel $model_cart */
        $model_cart = Model('b2b_cart');
        /** @var b2b_goodsModel $model_goods */
        $model_goods = Model('b2b_goods');
        /** @var b2b_buyLogic $logic_buy */
        $logic_buy = logic('b2b_buy');

        //存放返回信息
        $return = array();

        $cart_info = $model_cart->getCartInfo(array('cart_id' => $cart_id, 'buyer_id' => $_SESSION['member_id']));

        //普通商品
        $goods_id = intval($cart_info[0]['goods_id']);
        //$goods_info = $logic_buy->getGoodsOnlineInfo($goods_id, $quantity);
        $model=new Model();
        $goods_info=$model->table('b2b_goods,b2b_goods_common')->on('b2b_goods.goods_commonid=b2b_goods_common.goods_commonid')->join('left')->where(array('b2b_goods_common.goods_state'=>'1','b2b_goods.goods_id'=>array('in',$goods_id)))->find();
        if (empty($goods_info)) {
            $return['state'] = 'invalid';
            $return['msg'] = '商品已被下架';
            $return['subtotal'] = 0;
            QueueClient::push('delCart', array('buyer_id' => $_SESSION['member_id'], 'cart_ids' => array($cart_id)));
            exit(json_encode($return));
        }

        $quantity1 = $goods_info['goods_num'];
        if (intval($goods_info['goods_storage']) < $quantity1) {
            $return['state'] = 'shortage';
            $return['msg'] = '库存不足';
            $return['goods_num'] = $goods_info['goods_storage'];
            $return['goods_price'] = $goods_info['goods_price'];
            $return['subtotal'] = $goods_info['goods_price'] * intval($goods_info['goods_storage']);
            $model_cart->editCart(array('goods_num' => $goods_info['goods_storage']), array('cart_id' => $cart_id, 'buyer_id' => $_SESSION['member_id']));
            exit(json_encode($return));
        }

        $data = array();
        $data['goods_num'] = $quantity+$goods_info['goods_num'];
        $data['goods_price'] = $goods_info['goods_price'];
        $update = $model_cart->editCart($data, array('cart_id' => $cart_id, 'buyer_id' => $_SESSION['member_id']));
        if ($update) {
            $return = array();
            $return['state'] = 'true';
            $return['subtotal'] = $goods_info['goods_price'] * $quantity;
            $return['goods_price'] = $goods_info['goods_price'];
            $return['goods_num'] =$quantity+$goods_info['goods_num'];
        } else {
            $return = array('msg' => Language::get('cart_update_buy_fail', 'UTF-8'));
        }
        exit(json_encode($return));
    }

    /**
     * 采购清单删除单个商品，未登录前使用cart_id即为goods_id
     */
    public function delOp()
    {
        $cart_id = intval($_GET['cart_id']);
        if ($cart_id < 0) return;
        /** @var b2b_cartModel $model_cart */
        $model_cart = Model('b2b_cart');
        $data = array();
        if ($_SESSION['member_id']) {
            //登录状态下删除数据库内容
            $delete = $model_cart->delCart('db', array('cart_id' => $cart_id, 'buyer_id' => $_SESSION['member_id']));
            if ($delete) {
                $data['state'] = 'true';
                $data['quantity'] = $model_cart->cart_goods_num;
                $data['amount'] = $model_cart->cart_all_price;
            } else {
                $data['msg'] = Language::get('cart_drop_del_fail', 'UTF-8');
            }
        } else {
            //未登录时删除cookie的采购清单信息
            $delete = $model_cart->delCart('cookie', array('goods_id' => $cart_id));
            if ($delete) {
                $data['state'] = 'true';
                $data['quantity'] = $model_cart->cart_goods_num;
                $data['amount'] = $model_cart->cart_all_price;
            }
        }
        setNcCookie('b2b_cart_goods_num', $model_cart->cart_goods_num, 2 * 3600);
        $json_data = json_encode($data);
        if (isset($_GET['callback'])) {
            $json_data = $_GET['callback'] == '?' ? '(' . $json_data . ')' : $_GET['callback'] . "($json_data);";
        }
        exit($json_data);
    }
}
