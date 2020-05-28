<?php
/**
 * 购物车模型
 *
 *
 *
 * * @汉购网 (c) 2015-2018 ShopWWI Inc. (http://www.hangowa.com)
 * @license    http://www.shopwwi.c om
 * @link       交流群号：
 * @since      汉购网提供技术支持 授权请购买shopnc授权
 */
defined('ByShopWWI') or exit('Access Invalid!');
class b2b_cartModel extends Model {

    /**
     * 购物车商品总金额
     */
    public $cart_all_price = 0;
    /**
     * 购物车商品总数
     */
    public $cart_goods_num = 0;
    /*新增多条数据*/
   public function  _addCartInsertAll($goodsinfo){
       $cartdata=array();
       $key=0;
       foreach($goodsinfo as $item){
           $condition['goods_id'] =$item['goods_id'];
           $condition['buyer_id'] =$item['buyer_id'] ;
           $checkcart=$this->checkCart($condition,$item['goods_num']);
           //检查是不是已经有此数据，有，怎增加数量，没有则新增
           if($checkcart){
               $data['goods_num']=$item['goods_num']+$checkcart;
               $res=$this->editCart($data,$condition);
               if(!$res) return false;
               continue;
           }
           $cartdata[$key]['buyer_id']=$item['buyer_id'];
           $cartdata[$key]['goods_id']=$item['goods_id'];
           $cartdata[$key]['goods_name']=$item['goods_name'];
           $cartdata[$key]['goods_price']=$item['goods_price'];
           $cartdata[$key]['goods_num']=$item['goods_num'];
           $cartdata[$key]['goods_image']= !empty($item['goods_image'])?$item['goods_image']:'';
           $cartdata[$key]['bl_id']=isset($item['bl_id']) ? $item['bl_id']:'0';
           $cartdata[$key]['store_id']=isset($item['supplier_id']) ? $item['supplier_id']:'0';
           $cartdata[$key]['store_name']=isset($item['supplier_name']) ? $item['supplier_name']:'';
           $key++;
         }
        return  count($cartdata)>0 ? $this->insertAll($cartdata):true;
   }

    /*单条数据插入购物车*/
    public function _addCartDb($goods_info = array()) {
        //验证购物车商品是否已经存在
        $array['buyer_id']  =$goods_info['buyer_id'];
        $array['goods_id']  = $goods_info['goods_id'];
        $check_cart = $this->checkCart($array,$goods_info['goods_num']);
        if($check_cart){
            $data['goods_num']=$goods_info['goods_num']+$check_cart;
            return $this->editCart($data,$array);
        }
        $array['goods_name'] = $goods_info['goods_name'];
        $array['goods_price'] = $goods_info['goods_price'];
        $array['goods_num']   = $goods_info['goods_num'];
        $array['goods_image'] = $goods_info['goods_image'];
        $array['bl_id'] = isset($goods_info['bl_id']) ? $goods_info['bl_id'] : 0;
        return $this->insert($array);
    }

    /*检查购物车是否已经包含*/
    public function checkCart($condition = array(),$quantity) {
          $res=$this->where($condition)->find();
          if(count($res)>0){
              return  $res['goods_num'];
          }
           return false;
    }

   public function getCartInfo($condition,$field="*"){
       return $this->field($field)->where($condition)->select();
   }

    /*购物车修好*/
    public function editCart($data,$condition) {
        return $this->where($condition)->update($data);
    }

    public function _addCartCookie($goods_info = array(), $quantity = null) {
        //去除斜杠
        $cart_str = get_magic_quotes_gpc() ? stripslashes(cookie('b2b_cart')) : cookie('b2b_cart');
        $cart_str = base64_decode(decrypt($cart_str));
        $cart_array = @unserialize($cart_str);
        $cart_array = !is_array($cart_array) ? array() : $cart_array;
        if (count($cart_array) >= 10) return false;
        foreach($goods_info as $key=>$item){
            if(in_array($item['goods_id'],array_keys($cart_array))){
                $cart_array[$item['goods_id']]['goods_num']=$cart_array[$item['goods_id']]['goods_num']+$item['goods_num'];
                continue;
            }
            $cart_array[$item['goods_id']]['store_id']=isset($item['store_id']) ? $item['store_id']:'0';
            $cart_array[$item['goods_id']]['goods_id']=$item['goods_id'];
            $cart_array[$item['goods_id']]['goods_name']=$item['goods_name'];
            $cart_array[$item['goods_id']]['goods_price']=$item['goods_price'];
            $cart_array[$item['goods_id']]['goods_image']=$item['goods_image'];
            $cart_array[$item['goods_id']]['goods_num']=$item['goods_num'];
         }

        setNcCookie('b2b_cart',encrypt(base64_encode(serialize($cart_array))),24*3600);
        $this->getCartNum("cookie",array('buyer_id'=>$_SESSION['member_id']));
        return true;
    }


    /**
     * 删除购物车商品
     *
     * @param string $type 存储类型 db,cookie
     * @param unknown_type $condition
     */
    public function delCart($type, $condition = array()) {
        if ($type == 'db') {
            $result =  $this->where($condition)->delete();
        } elseif ($type == 'cookie') {
            $cart_str = get_magic_quotes_gpc() ? stripslashes(cookie('b2b_cart')) : cookie('b2b_cart');
            $cart_str = base64_decode(decrypt($cart_str));
            $cart_array = @unserialize($cart_str);
            if (key_exists($condition['goods_id'],(array)$cart_array)) {
                unset($cart_array[$condition['goods_id']]);
            }
            setNcCookie('b2b_cart',encrypt(base64_encode(serialize($cart_array))),24*3600);
            $result = true;
        }
        //重新计算购物车商品数和总金额
        if ($result) {
            $this->getCartNum($type,array('buyer_id'=>$condition['buyer_id']));
        }
        return $result;
    }

    /**
     * 清空购物车
     *
     * @param string $type 存储类型 db,cookie
     * @param unknown_type $condition
     */
    public function clearCart($type, $condition = array()) {
        if ($type == 'cookie') {
            setNcCookie('b2b_cart','',-3600);
        } else if ($type == 'db') {
            //数据库暂无浅清空操作
        }
    }

    /**
     * 计算购物车总商品数和总金额
     * @param string $type 购物车信息保存类型 db,cookie
     * @param array $condition 只有登录后操作购物车表时才会用到该参数
     */
    public function getCartNum($type, $condition = array()) {
        if ($type == 'db') {
            $cart_all_price = 0;
            $cart_goods = $this->listCart('db',$condition);
            $this->cart_goods_num = count($cart_goods);
            if(!empty($cart_goods) && is_array($cart_goods)) {
                foreach ($cart_goods as $val) {
                    $cart_all_price += $val['goods_price'] * $val['goods_num'];
                }
            }
          $this->cart_all_price = ncPriceFormat($cart_all_price);
        } elseif ($type == 'cookie') {
            $cart_str = get_magic_quotes_gpc() ? stripslashes(cookie('b2b_cart')) : cookie('b2b_cart');
            $cart_str = base64_decode(decrypt($cart_str));
            $cart_array = @unserialize($cart_str);
            $cart_array = !is_array($cart_array) ? array() : $cart_array;
            $this->cart_goods_num = count($cart_array);
            $cart_all_price = 0;
            foreach ($cart_array as $v){
                $cart_all_price += floatval($v['goods_price'])*intval($v['goods_num']);
            }
            $this->cart_all_price = $cart_all_price;
        }
        @setNcCookie('cart_goods_num',$this->cart_goods_num,2*3600);
        return $this->cart_goods_num;
    }

    /**
     * 登录之后,把登录前购物车内的商品加到购物车表
     *
     */
    public function mergecart($member_info = array()){
        if (!$member_info['member_id']) return;
         $save_type = C('cache.type') != 'file' ? 'cache' : 'cookie';
        $save_type = 'cookie';
        $cart_new_list = $this->listCart($save_type);
        if (empty($cart_new_list)) return;
         //取出当前DB购物车已有信息
         $cart_cur_list = $this->listCart('db',array('buyer_id'=>$member_info['member_id']));
         //数据库购物车已经有的商品，不再添加
         if (!empty($cart_cur_list) && is_array($cart_cur_list) && is_array($cart_new_list)) {
             foreach ($cart_cur_list as $k=>$v){
                 if (!is_numeric($k) || in_array($v['goods_id'],array_keys($cart_new_list))){
                     $data['goods_num']=$cart_cur_list[$k]['goods_num']+$cart_new_list[$v['goods_id']]['goods_num'];
                     $this->editCart($data,array('goods_id'=>$v['goods_id'],'buyer_id'=>$member_info['member_id']));
                     unset($cart_new_list[$v['goods_id']]);
                 }
             }
         }

        if(count($cart_new_list)<1) return;
         //查询在购物车中,不是店铺自己的商品，未禁售，上架，有库存的商品,并加入DB购物车
        $model=new Model();
        $goods_list=$model->table('b2b_goods,b2b_goods_common')->on('b2b_goods.goods_commonid=b2b_goods_common.goods_commonid')->join('left')->where(array('goods_state'=>'1','b2b_goods.goods_id'=>array('in',array_keys($cart_new_list))))->select();
        if (!empty($goods_list)){
             foreach ($goods_list as $k=>$goods_info){
                 $goods_info['buyer_id'] = $member_info['member_id'];
                 $goods_info['goods_num']=$cart_new_list[$goods_info['goods_id']]['goods_num'];
                 $this->_addCartDb($goods_info);
             }
         }
         //最后清空登录前购物车内容
         $this->clearCart($save_type);
    }

    /**
     * 购物车列表
     *
     * @param string $type 存储类型 db,cookie
     * @param unknown_type $condition
     * @param int $limit
     */
    public function listCart($type, $condition = array(), $limit = '') {
        if ($type == 'db') {
            $cart_list = $this->where($condition)->limit($limit)->select();
        } elseif ($type == 'cookie') {
            //去除斜杠
            $cart_str = get_magic_quotes_gpc() ? stripslashes(cookie('b2b_cart')) : cookie('b2b_cart');
            $cart_str = base64_decode(decrypt($cart_str));
            $cart_list = @unserialize($cart_str);
        }
        $cart_list = is_array($cart_list) ? $cart_list : array();
        //顺便设置购物车商品数和总金额
        $this->cart_goods_num =  count($cart_list);
        $cart_all_price = 0;
        if(is_array($cart_list)) {
            foreach ($cart_list as $val) {
                $cart_all_price += $val['goods_price'] * $val['goods_num'];
            }
        }
        $this->cart_all_price = ncPriceFormat($cart_all_price);
        return !is_array($cart_list) ? array() : $cart_list;
    }


}
