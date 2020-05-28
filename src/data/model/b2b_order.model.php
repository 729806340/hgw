<?php
/**
 * 订单管理
 */
defined('ByShopWWI') or exit('Access Invalid!');
class b2b_orderModel extends Model {

    /**
     * 取单条订单信息
     *
     * @param unknown_type $condition
     * @param array $extend 追加返回那些表的信息,如array('order_common','order_goods','store')
     * @return unknown
     */
    public function getOrderInfo($condition = array(), $extend = array(), $fields = '*', $order = '',$group = '') {
        $order_info = $this->table('b2b_order')->field($fields)->where($condition)->group($group)->order($order)->find();
        if (empty($order_info)) {
            return array();
        }
        if (isset($order_info['order_state'])) {
            $order_info['state_desc'] = orderState($order_info);
        }
        if (isset($order_info['payment_code'])) {
            $order_info['payment_name'] = orderPaymentName($order_info['payment_code']);
        }

        //追加返回订单扩展表信息
        if (in_array('order_common',$extend)) {
            $order_info['extend_order_common'] = $this->getOrderCommonInfo(array('order_id'=>$order_info['order_id']));
            $order_info['extend_order_common']['reciver_info'] = unserialize($order_info['extend_order_common']['reciver_info']);
            $order_info['extend_order_common']['invoice_info'] = unserialize($order_info['extend_order_common']['invoice_info']);
        }

        //追加返回店铺信息
        if (in_array('store',$extend)) {
            $order_info['extend_store'] = Model('store')->getStoreInfo(array('store_id'=>$order_info['store_id']));
        }

        //返回买家信息
        if (in_array('member',$extend)) {
            $order_info['extend_member'] = Model('member')->getMemberInfoByID($order_info['buyer_id']);
        }

        //追加返回商品信息
        if (in_array('order_goods',$extend)) {
            //取商品列表
            $order_goods_list = $this->getOrderGoodsList(array('order_id'=>$order_info['order_id']));
            $order_info['extend_order_goods'] = $order_goods_list;
        }

        return $order_info;
    }

    public function getOrderCommonInfo($condition = array(), $field = '*') {
        return $this->table('b2b_order_common')->where($condition)->find();
    }

    public function getOrderPayInfo($condition = array(), $master = false) {
        return $this->table('b2b_order_pay')->where($condition)->master($master)->find();
    }

    public function setCheck($condition = array(),$check_status){
        $data = array();
        $data['check_status'] = $check_status;
        return $this->table('b2b_order')->where($condition)->update($data);
    }

    /**
     * 取得支付单列表
     *
     * @param unknown_type $condition
     * @param unknown_type $pagesize
     * @param unknown_type $filed
     * @param unknown_type $order
     * @param string $key 以哪个字段作为下标,这里一般指pay_id
     * @return unknown
     */
    public function getOrderPayList($condition, $pagesize = '', $filed = '*', $order = '', $key = '') {
        return $this->table('b2b_order_pay')->field($filed)->where($condition)->order($order)->page($pagesize)->key($key)->select();
    }

    /**
     * 取得店铺订单列表
     *
     * @param int $store_id 店铺编号
     * @param string $order_sn 订单sn
     * @param string $buyer_name 买家名称
     * @param string $state_type 订单状态
     * @param string $query_start_date 搜索订单起始时间
     * @param string $query_end_date 搜索订单结束时间
     * @param string $skip_off 跳过已关闭订单
     * @return array $order_list
     */
    public function getStoreOrderList($store_id, $order_sn, $buyer_name, $state_type, $query_start_date,
            $query_end_date, $skip_off, $fields = '*', $extend = array(),$chain_id = null, $extra_cond = array()) {
        $condition = array();
        $condition['store_id'] = $store_id;
        if (preg_match('/^\d{10,20}$/',$order_sn)) {
            $condition['order_sn'] = $order_sn;
        }
        if ($buyer_name != '') {
            $condition['buyer_name'] = $buyer_name;
        }
        if (!empty($extra_cond)) {
            $condition = array_merge($condition, $extra_cond);
        }

        if (isset($chain_id)) {
            $condition['chain_id'] = intval($chain_id);
        }
        $allow_state_array = array('state_new','state_pay','state_send','state_success','state_cancel');
        if (in_array($state_type, $allow_state_array)) {
            $condition['order_state'] = str_replace($allow_state_array,
                    array(ORDER_STATE_NEW,ORDER_STATE_PAY,ORDER_STATE_SEND,ORDER_STATE_SUCCESS,ORDER_STATE_CANCEL), $state_type);
        } else {
            if ($state_type != 'state_notakes') {
                $state_type = 'store_order';
            }
        }
        $if_start_date = preg_match('/^20\d{2}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/',$query_start_date);
        $if_end_date = preg_match('/^20\d{2}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/',$query_end_date);
        $start_unixtime = $if_start_date ? strtotime($query_start_date) : null;
        $end_unixtime = $if_end_date ? strtotime($query_end_date): null;
        if ($start_unixtime || $end_unixtime) {
            $condition['add_time'] = array('second',array($start_unixtime,$end_unixtime));
        }

        if ($skip_off == 1) {
            $condition['order_state'] = array('neq',ORDER_STATE_CANCEL);
        }

        if ($state_type == 'state_new') {
            $condition['chain_code'] = 0;
        }
        if ($state_type == 'state_pay') {
            $condition['chain_code'] = 0;
        }
        if ($state_type == 'state_notakes') {
            $condition['order_state'] = array('in',array(ORDER_STATE_NEW,ORDER_STATE_PAY));
            $condition['chain_code'] = array('gt',0);
        }

        //过滤集采订单
        empty($condition['payment_code']) and $condition['payment_code'] = array('not in' , array('b2b'));

        //待发货过滤申请退款的订单
        ($condition['order_state'] == ORDER_STATE_PAY) and $condition['lock_state'] = 0;

        $order_list = $this->getOrderList($condition, 20, $fields, 'order_id desc','', $extend);

        //页面中显示那些操作
        foreach ($order_list as $key => $order_info) {

            //显示取消订单
            $order_info['if_store_cancel'] = $this->getOrderOperateState('store_cancel',$order_info);
            //显示调整费用
            $order_info['if_modify_price'] = $this->getOrderOperateState('modify_price',$order_info);
			//显示调整订单费用
        	$order_info['if_spay_price'] = $this->getOrderOperateState('spay_price',$order_info);
            //显示发货
            $order_info['if_store_send'] = $this->getOrderOperateState('store_send',$order_info);
            //显示锁定中
            $order_info['if_lock'] = $this->getOrderOperateState('lock',$order_info);
            //显示物流跟踪
            $order_info['if_deliver'] = $this->getOrderOperateState('deliver',$order_info);
            //门店自提订单完成状态
            $order_info['if_chain_receive'] = $this->getOrderOperateState('chain_receive',$order_info);

            //查询消费者保障服务
            if (C('contract_allow') == 1) {
                $contract_item = Model('contract')->getContractItemByCache();
            }
            foreach ($order_info['extend_order_goods'] as $value) {
                $value['image_60_url'] = cthumb($value['goods_image'], 60, $value['store_id']);
                $value['image_240_url'] = cthumb($value['goods_image'], 240, $value['store_id']);
                $value['goods_type_cn'] = orderGoodsType($value['goods_type']);
                $value['goods_url'] = urlShop('goods','index',array('goods_id'=>$value['goods_id']));
                //处理消费者保障服务
                if (trim($value['goods_contractid']) && $contract_item) {
                    $goods_contractid_arr = explode(',',$value['goods_contractid']);
                    foreach ((array)$goods_contractid_arr as $gcti_v) {
                        $value['contractlist'][] = $contract_item[$gcti_v];
                    }
                }
                if ($value['goods_type'] == 5) {
                    $order_info['zengpin_list'][] = $value;
                } else {
                    $order_info['goods_list'][] = $value;
                }
            }

            if (empty($order_info['zengpin_list'])) {
                $order_info['goods_count'] = count($order_info['goods_list']);
            } else {
                $order_info['goods_count'] = count($order_info['goods_list']) + 1;
            }

            //取得其它订单类型的信息
            $this->getOrderExtendInfo($order_info);
            $order_list[$key] = $order_info;
        }

        return $order_list;
    }




 /**
     * 取得店铺订单列表导出到excel
     *
     * @param int $store_id 店铺编号
     * @param string $order_sn 订单sn
     * @param string $buyer_name 买家名称
     * @param string $state_type 订单状态
     * @param string $query_start_date 搜索订单起始时间
     * @param string $query_end_date 搜索订单结束时间
     * @param string $skip_off 跳过已关闭订单
     * @return array $order_list
     */
    public function getStoreOrderListToExcel($store_id, $order_sn, $buyer_name, $state_type, $query_start_date, $query_end_date,
             $skip_off, $fields = '*', $extend = array(),$chain_id = null, $extra_cond = array()) {
        $condition = array();
        $condition['store_id'] = $store_id;
        if (preg_match('/^\d{10,20}$/',$order_sn)) {
            $condition['order_sn'] = $order_sn;
        }
        if ($buyer_name != '') {
            $condition['buyer_name'] = $buyer_name;
        }
        if (!empty($extra_cond)) {
            $condition = array_merge($condition, $extra_cond);
        }
        if (isset($chain_id)) {
            $condition['chain_id'] = intval($chain_id);
        }
        $allow_state_array = array('state_new','state_pay','state_send','state_success','state_cancel');
        if (in_array($state_type, $allow_state_array)) {
            $condition['order_state'] = str_replace($allow_state_array,
                    array(ORDER_STATE_NEW,ORDER_STATE_PAY,ORDER_STATE_SEND,ORDER_STATE_SUCCESS,ORDER_STATE_CANCEL), $state_type);
        } else {
            if ($state_type != 'state_notakes') {
                $state_type = 'store_order';
            }
        }
        $if_start_date = preg_match('/^20\d{2}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/',$query_start_date);
        $if_end_date = preg_match('/^20\d{2}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/',$query_end_date);
        $start_unixtime = $if_start_date ? strtotime($query_start_date) : null;
        $end_unixtime = $if_end_date ? strtotime($query_end_date): null;
        if ($start_unixtime || $end_unixtime) {
            $condition['add_time'] = array('second',array($start_unixtime,$end_unixtime));
        }

        if ($skip_off == 1) {
            $condition['order_state'] = array('neq',ORDER_STATE_CANCEL);
        }

        if ($state_type == 'state_new') {
            $condition['chain_code'] = 0;
        }
        if ($state_type == 'state_pay') {
            $condition['chain_code'] = 0;
            $condition['lock_state'] = 0;
        }
        if ($state_type == 'state_notakes') {
            $condition['order_state'] = array('in',array(ORDER_STATE_NEW,ORDER_STATE_PAY));
            $condition['chain_code'] = array('gt',0);
        }

        $order_list = $this->getOrderListToExcel($condition,$fields, 'order_id desc','', $extend);

        //页面中显示那些操作
        foreach ($order_list as $key => $order_info) {

            //显示取消订单
            $order_info['if_store_cancel'] = $this->getOrderOperateState('store_cancel',$order_info);
            //显示调整费用
            $order_info['if_modify_price'] = $this->getOrderOperateState('modify_price',$order_info);
			//显示调整订单费用
        	$order_info['if_spay_price'] = $this->getOrderOperateState('spay_price',$order_info);
            //显示发货
            $order_info['if_store_send'] = $this->getOrderOperateState('store_send',$order_info);
            //显示锁定中
            $order_info['if_lock'] = $this->getOrderOperateState('lock',$order_info);
            //显示物流跟踪
            $order_info['if_deliver'] = $this->getOrderOperateState('deliver',$order_info);
            //门店自提订单完成状态
            $order_info['if_chain_receive'] = $this->getOrderOperateState('chain_receive',$order_info);

            //查询消费者保障服务
            if (C('contract_allow') == 1) {
                $contract_item = Model('contract')->getContractItemByCache();
            }
            foreach ($order_info['extend_order_goods'] as $value) {
                $value['image_60_url'] = cthumb($value['goods_image'], 60, $value['store_id']);
                $value['image_240_url'] = cthumb($value['goods_image'], 240, $value['store_id']);
                $value['goods_type_cn'] = orderGoodsType($value['goods_type']);
                $value['goods_url'] = urlShop('goods','index',array('goods_id'=>$value['goods_id']));
                //处理消费者保障服务
                if (trim($value['goods_contractid']) && $contract_item) {
                    $goods_contractid_arr = explode(',',$value['goods_contractid']);
                    foreach ((array)$goods_contractid_arr as $gcti_v) {
                        $value['contractlist'][] = $contract_item[$gcti_v];
                    }
                }
                if ($value['goods_type'] == 5) {
                    $order_info['zengpin_list'][] = $value;
                } else {
                    $order_info['goods_list'][] = $value;
                }
            }

            if (empty($order_info['zengpin_list'])) {
                $order_info['goods_count'] = count($order_info['goods_list']);
            } else {
                $order_info['goods_count'] = count($order_info['goods_list']) + 1;
            }

            //取得其它订单类型的信息
            $this->getOrderExtendInfo($order_info);
            $order_list[$key] = $order_info;
        }

        return $order_list;
    }

 /**
     * 取得订单列表(所有)导出到Excel
     * @param unknown $condition
     * @param string $pagesize
     * @param string $field
     * @param string $order
     * @param string $limit
     * @param unknown $extend 追加返回那些表的信息,如array('order_common','order_goods','store')
     * @return Ambigous <multitype:boolean Ambigous <string, mixed> , unknown>
     */
    public function getOrderListToExcel($condition, $field = '*', $order = 'order_id desc', $limit = '', $extend = array(), $master = false){
		empty($limit) && $limit = 2000;
		$list = $this->table('b2b_order')->field($field)->where($condition)->order($order)->limit($limit)->master($master)->select();

        if (empty($list)) return array();
        $fenxiao_service = Service ( "Fenxiao" );
//         $fx_members = $fenxiao_service->getFenxiaoMembers ();
        $order_list = array();
        foreach ($list as &$order) {
            if (isset($order['order_state'])) {
                $order['state_desc'] = orderState($order);
            }
            if (isset($order['payment_code'])) {
                $order['payment_name'] = orderPaymentName($order['payment_code']);
            }

//             array_key_exists($order['buyer_id'], $fx_members) and $order['buyer_name'] = '分销订单';
            if (!empty($extend)) $order_list[$order['order_id']] = $order;
        }
        if (empty($order_list)) $order_list = $list;

        //追加返回订单扩展表信息
        if (in_array('order_common',$extend)) {
            $order_common_list = $this->getOrderCommonList(array('order_id'=>array('in',array_keys($order_list))), "*", "", $limit);
            foreach ($order_common_list as $value) {
                $order_list[$value['order_id']]['extend_order_common'] = $value;
                $order_list[$value['order_id']]['extend_order_common']['reciver_info'] = @unserialize($value['reciver_info']);
                $order_list[$value['order_id']]['extend_order_common']['invoice_info'] = @unserialize($value['invoice_info']);
            }
        }
        //追加返回店铺信息
        /*if (in_array('store',$extend)) {
            $store_id_array = array();
            foreach ($order_list as $value) {
                if (!in_array($value['store_id'],$store_id_array)) $store_id_array[] = $value['store_id'];
            }
            $store_list = Model('store')->getStoreList(array('store_id'=>array('in',$store_id_array)));
            $store_new_list = array();
            foreach ($store_list as $store) {
                $store_new_list[$store['store_id']] = $store;
            }
            foreach ($order_list as $order_id => $order) {
                $order_list[$order_id]['extend_store'] = $store_new_list[$order['store_id']];
            }
        }

        //追加返回买家信息
        if (in_array('member',$extend)) {
            foreach ($order_list as $order_id => $order) {
                $order_list[$order_id]['extend_member'] = Model('member')->getMemberInfoByID($order['buyer_id']);
            }
        }*/

        //追加返回商品信息
        if (in_array('order_goods',$extend)) {
            //取商品列表
            $order_goods_list = $this->getOrderGoodsList(array('order_id'=>array('in',array_keys($order_list))), '*', 100000);
            if (!empty($order_goods_list)) {
                foreach ($order_goods_list as $value) {
                    $order_list[$value['order_id']]['extend_order_goods'][] = $value;
                }
            } else {
                $order_list[$value['order_id']]['extend_order_goods'] = array();
            }
        }

        return $order_list;
    }





    /**
     * 取得订单列表(未被删除)
     * @param unknown $condition
     * @param string $pagesize
     * @param string $field
     * @param string $order
     * @param string $limit
     * @param unknown $extend 追加返回那些表的信息,如array('order_common','order_goods','store')
     * @return Ambigous <multitype:boolean Ambigous <string, mixed> , unknown>
     */
    public function getNormalOrderList($condition, $pagesize = '', $field = '*', $order = 'order_id desc', $limit = '', $extend = array()){
        $condition['delete_state'] = 0;
        return $this->getOrderList($condition, $pagesize, $field, $order, $limit, $extend);
    }

    /**
     * 取得订单列表(所有)
     * @param array $condition
     * @param string $pagesize
     * @param string $field
     * @param string $order
     * @param string $limit
     * @param array $extend 追加返回那些表的信息,如array('order_common','order_goods','store')
     * @return mixed <multitype:boolean Ambigous <string, mixed> , unknown>
     */
    public function getOrderList($condition, $pagesize = '', $field = '*', $order = 'order_id desc', $limit = '', $extend = array(), $master = false){
        $list = $this->table('b2b_order')->field($field)->where($condition)->page($pagesize)->order($order)->limit($limit)->master($master)->select();
        if (empty($list)) return array();
        $fenxiao_service = Service ( "Fenxiao" );
        $fx_members = $fenxiao_service->getFenxiaoMembers ();
        $order_list = array();
        foreach ($list as &$order) {
            if (isset($order['order_state'])) {
                $order['state_desc'] = orderState($order);
            }
            if (isset($order['payment_code'])) {
                $order['payment_name'] = orderPaymentName($order['payment_code']);
            }
            //array_key_exists($order['buyer_id'], $fx_members) and $order['buyer_name'] = '分销订单';

            //关于分销和集采订单的处理
            if (!empty($extend)) $order_list[$order['order_id']] = $order;
        }
        if (empty($order_list)) $order_list = $list;

        //追加返回订单扩展表信息
        if (in_array('order_common',$extend)) {
            $order_common_list = $this->getOrderCommonList(array('order_id'=>array('in',array_keys($order_list))),'*','',999999);
            foreach ($order_common_list as $value) {
                $order_list[$value['order_id']]['extend_order_common'] = $value;
                $order_list[$value['order_id']]['extend_order_common']['reciver_info'] = @unserialize($value['reciver_info']);
                $order_list[$value['order_id']]['extend_order_common']['invoice_info'] = @unserialize($value['invoice_info']);
            }
        }
        //追加返回店铺信息
        if (in_array('store',$extend)) {
            $store_id_array = array();
            foreach ($order_list as $value) {
                if (!in_array($value['store_id'],$store_id_array)) $store_id_array[] = $value['store_id'];
            }
            $store_list = Model('store')->getStoreList(array('store_id'=>array('in',$store_id_array)));
            $store_new_list = array();
            foreach ($store_list as $store) {
                $store_new_list[$store['store_id']] = $store;
            }
            foreach ($order_list as $order_id => $order) {
                $order_list[$order_id]['extend_store'] = $store_new_list[$order['store_id']];
            }
        }

        //追加返回买家信息
        if (in_array('member',$extend)) {
            foreach ($order_list as $order_id => $order) {
                $order_list[$order_id]['extend_member'] = Model('member')->getMemberInfoByID($order['buyer_id']);
            }
        }

        //追加返回商品信息
        if (in_array('order_goods',$extend)) {
            //取商品列表
            $order_goods_list = $this->getOrderGoodsList(array('order_id'=>array('in',array_keys($order_list))),'*',99999);
            if (!empty($order_goods_list)) {
                foreach ($order_goods_list as $value) {
                    $order_list[$value['order_id']]['extend_order_goods'][] = $value;
                }
            } else {
                $order_list[$value['order_id']]['extend_order_goods'] = array();
            }
        }

        return $order_list;
    }

    /**
     * 取得(买/卖家)订单某个数量缓存
     * @param string $type 买/卖家标志，允许传入 buyer、store
     * @param int $id   买家ID、店铺ID
     * @param string $key 允许传入  NewCount、PayCount、SendCount、EvalCount、TakesCount，分别取相应数量缓存，只许传入一个
     * @return array
     */
    public function getOrderCountCache($type, $id, $key) {
        if (!C('cache_open')) return array();
        $type = 'ordercount'.$type;
        $ins = Cache::getInstance('redis');
        $order_info = $ins->hget($id,$type,$key);
        return !is_array($order_info) ? array($key => $order_info) : $order_info;
    }


    /**
     * 设置(买/卖家)订单某个数量缓存
     * @param string $type 买/卖家标志，允许传入 buyer、store
     * @param int $id 买家ID、店铺ID
     * @param array $data
     */
    public function editOrderCountCache($type, $id, $data) {
        if (!C('cache_open') || empty($type) || !intval($id) || !is_array($data)) return ;
        $ins = Cache::getInstance('redis');
        $type = 'ordercount'.$type;
        $ins->hset($id,$type,$data);
    }

    /**
     * 判断店铺是否需要从缓存中读取数量统计
     * @param int $store_id 店铺ID
     * @return bool 不需要读缓存返回false,需要读返回true
     */
    private function checkReadCache($store_id)
    {
        $arr = array(80);
        if( in_array($store_id, $arr) ) {
            return false;
        }

        return true ;
    }

    /**
     * 取得买卖家订单数量某个缓存
     * @param string $type $type 买/卖家标志，允许传入 buyer、store
     * @param int $id 买家ID、店铺ID
     * @param string $key 允许传入  NewCount、PayCount、SendCount、EvalCount、TakesCount，分别取相应数量缓存，只许传入一个
     * @return int
     */
    public function getOrderCountByID($type, $id, $key) {
        $cache_info = $this->getOrderCountCache($type, $id, $key);

        if (is_string($cache_info[$key]) && ($type=='store'&&$this->checkReadCache($id))) {
            //从缓存中取得
            $count = $cache_info[$key];
        } else {
            //从数据库中取得
            $field = $type == 'buyer' ? 'buyer_id' : 'store_id';
            $condition = array($field => $id);
            $func = 'getOrderState'.$key;
            $count = $this->$func($condition);
            $this->editOrderCountCache($type,$id,array($key => $count));
        }
        return $count;
    }

    /**
     * 删除(买/卖家)订单全部数量缓存
     * @param string $type 买/卖家标志，允许传入 buyer、store
     * @param int $id   买家ID、店铺ID
     * @return bool
     */
    public function delOrderCountCache($type, $id) {
        if (!C('cache_open')) return true;
        $ins = Cache::getInstance('redis');
        $type = 'ordercount'.$type;
        return $ins->hdel($id,$type);
    }

    /**
     * 待付款订单数量
     * @param unknown $condition
     */
    public function getOrderStateNewCount($condition = array()) {
        $condition['order_state'] = ORDER_STATE_NEW;
        $condition['chain_code'] = 0;
        return $this->getOrderCount($condition);
    }

    /**
     * 待发货订单数量
     * @param unknown $condition
     */
    public function getOrderStatePayCount($condition = array()) {
        $condition['order_state'] = ORDER_STATE_PAY;
        $condition['lock_state'] = 0;
        return $this->getOrderCount($condition);
    }

    /**
     * 待收货订单数量
     * @param unknown $condition
     */
    public function getOrderStateSendCount($condition = array()) {
        $condition['order_state'] = ORDER_STATE_SEND;
        return $this->getOrderCount($condition);
    }

    /**
     * 待评价订单数量
     * @param unknown $condition
     */
    public function getOrderStateEvalCount($condition = array()) {
        $condition['order_state'] = ORDER_STATE_SUCCESS;
        $condition['evaluation_state'] = 0;
        return $this->getOrderCount($condition);
    }

    /**
     * 待自提订单数量
     * @param unknown $condition
     */
    public function getOrderStateTakesCount($condition = array()) {
        $condition['order_state'] = array('in',array(ORDER_STATE_NEW,ORDER_STATE_PAY));
        $condition['chain_code'] = array('gt',0);
        return $this->getOrderCount($condition);
    }

    /**
     * 交易中的订单数量
     * @param unknown $condition
     */
    public function getOrderStateTradeCount($condition = array()) {
        $condition['order_state'] = array(array('neq',ORDER_STATE_CANCEL),array('neq',ORDER_STATE_SUCCESS),'and');
        return $this->getOrderCount($condition);
    }

    /**
     * 取得订单数量
     * @param unknown $condition
     */
    public function getOrderCount($condition) {
        return $this->table('b2b_order')->where($condition)->count();
    }

    /**
     * 取得订单商品表详细信息
     * @param unknown $condition
     * @param string $fields
     * @param string $order
     */
    public function getOrderGoodsInfo($condition = array(), $fields = '*', $order = '') {
        return $this->table('b2b_order_goods')->where($condition)->field($fields)->order($order)->find();
    }

    /**
     * 取得订单商品表列表
     * @param unknown $condition
     * @param string $fields
     * @param string $limit
     * @param string $page
     * @param string $order
     * @param string $group
     * @param string $key
     * @param boolean $master
     */
    public function getOrderGoodsList($condition = array(), $fields = '*', $limit = null, $page = null, $order = 'rec_id desc', $group = null, $key = null,$master=false) {
        return $this->table('b2b_order_goods')->field($fields)->where($condition)->limit($limit)->order($order)->group($group)->key($key)->page($page)->master($master)->select();
    }

    public function getOrderAddressList($condition = array(), $fields = '*', $limit = null, $page = null, $order = 'address_id desc', $group = null, $key = null,$master=false) {
        return $this->table('b2b_order_address')->field($fields)->where($condition)->limit($limit)->order($order)->group($group)->key($key)->page($page)->master($master)->select();
    }
    public function getOrderAddressInfo($condition = array(), $fields = '*') {
        return $this->table('b2b_order_address')->field($fields)->where($condition)->limit(1)->find();
    }


    public function editOrderAddress($data,$condition) {
        return $this->table('b2b_order_address')->where($condition)->update($data);
    }




    /**
     * 取得订单扩展表列表
     * @param unknown $condition
     * @param string $fields
     * @param string $limit
     */
    public function getOrderCommonList($condition = array(), $fields = '*', $order = '', $limit = null) {
        return $this->table('b2b_order_common')->field($fields)->where($condition)->order($order)->limit($limit)->select();
    }

    /**
     * 插入订单支付表信息
     * @param array $data
     * @return int 返回 insert_id
     */
    public function addOrderPay($data) {
        return $this->table('b2b_order_pay')->insert($data);
    }

    /**
     * 插入订单表信息
     * @param array $data
     * @return int 返回 insert_id
     */
    public function addOrder($data) {
        $insert = $this->table('b2b_order')->insert($data);
        if ($insert) {
            //更新缓存
            if (C('cache_open')) {
                QueueClient::push('delOrderCountCache',array('buyer_id'=>$data['buyer_id'],'store_id'=>$data['store_id']));
            }
        }
        return $insert;
    }

    /**
     * 插入订单扩展表信息
     * @param array $data
     * @return int 返回 insert_id
     */
    public function addOrderCommon($data) {
        return $this->table('b2b_order_common')->insert($data);
    }

    /**
     * 插入订单扩展表信息
     * @param array $data
     * @return int 返回 insert_id
     */
    public function addOrderGoods($data) {
        return $this->table('b2b_order_goods')->insertAll($data);
    }
	    public function addOrderGood($data) {
        return $this->table('b2b_order_goods')->insert($data);
    }

    /**
     * 添加订单日志
     */
    public function addOrderLog($data) {
        $data['log_role'] = str_replace(array('buyer','seller','system','admin'),array('买家','商家','系统','管理员'), $data['log_role']);
        $data['log_time'] = TIMESTAMP;
        return $this->table('b2b_order_log')->insert($data);
    }

    /**
     * 更改订单信息
     *
     * @param unknown_type $data
     * @param unknown_type $condition
     */
    public function editOrder($data,$condition,$limit = '') {
        $update = $this->table('b2b_order')->where($condition)->limit($limit)->update($data);
        if ($update) {
            //更新缓存
            if (C('cache_open')) {
                QueueClient::push('delOrderCountCache',$condition);
            }
        }
        return $update;
    }

    /**
     * 更改订单信息
     *
     * @param unknown_type $data
     * @param unknown_type $condition
     */
    public function editOrderCommon($data,$condition) {
        return $this->table('b2b_order_common')->where($condition)->update($data);
    }

    /**
     * 更改订单信息
     *
     * @param unknown_type $data
     * @param unknown_type $condition
     */
    public function editOrderGoods($data,$condition) {
        return $this->table('b2b_order_goods')->where($condition)->update($data);
    }

    /**
     * 更改订单支付信息
     *
     * @param unknown_type $data
     * @param unknown_type $condition
     */
    public function editOrderPay($data,$condition) {
        return $this->table('b2b_order_pay')->where($condition)->update($data);
    }

    /**
     * 订单操作历史列表
     * @param unknown $order_id
     * @return Ambigous <multitype:, unknown>
     */
    public function getOrderLogList($condition,$order = '') {
        return $this->table('b2b_order_log')->where($condition)->order($order)->select();
    }

    /**
     * 取得单条订单操作记录
     * @param unknown $condition
     * @param string $order
     */
    public function getOrderLogInfo($condition = array(), $order = '') {
        return $this->table('b2b_order_log')->where($condition)->order($order)->find();
    }

    /**
     * 返回是否允许某些操作
     * @param string $operate
     * @param array $order_info
     * @return bool|mixed
     */
    public function getOrderOperateState($operate,$order_info){
        if (!is_array($order_info) || empty($order_info)) {
            return false;
        }

        if (isset($order_info['if_'.$operate])) {
            return $order_info['if_'.$operate];
        }

        switch ($operate) {

            //买家取消订单
            case 'buyer_cancel':
               $state = ($order_info['order_state'] == ORDER_STATE_NEW) ||
                   ($order_info['payment_code'] == 'offline' && $order_info['order_state'] == ORDER_STATE_PAY);
               break;

           //申请退款
           case 'refund_cancel':
               $state = $order_info['refund'] == 1 && !intval($order_info['lock_state']);
               break;

           //商家取消订单
           case 'store_cancel':
               $state = ($order_info['order_state'] == ORDER_STATE_NEW && $order_info['payment_code'] != 'chain') ||
               ($order_info['payment_code'] == 'offline' &&
               in_array($order_info['order_state'],array(ORDER_STATE_PAY,ORDER_STATE_SEND)));
               break;

           //平台取消订单
           case 'system_cancel':
               $state = ($order_info['order_state'] == ORDER_STATE_NEW) ||
               ($order_info['payment_code'] == 'offline' && $order_info['order_state'] == ORDER_STATE_PAY);
               if( $order_info['order_from'] == '3' && $order_info['order_state'] == '20' ){
                   $state = true;
               }
               break;

           //平台收款
           case 'system_receive_pay':
               $state = $order_info['order_state'] == ORDER_STATE_NEW;
               //$state = $state && $order_info['payment_code'] == 'online' && $order_info['api_pay_time'] || $order_info['payment_code'] == 'jicai' ;
               break;

           //买家投诉
           case 'complain':
               $state = in_array($order_info['order_state'],array(ORDER_STATE_PAY,ORDER_STATE_SEND)) ||
                   intval($order_info['finnshed_time']) > (TIMESTAMP - C('complain_time_limit'));
               break;

           case 'payment':
               $state = $order_info['order_state'] == ORDER_STATE_NEW && $order_info['payment_code'] == 'online';
               break;

            //调整运费
            case 'modify_price':
                $state = ($order_info['order_state'] == ORDER_STATE_NEW) ||
                   ($order_info['payment_code'] == 'offline' && $order_info['order_state'] == ORDER_STATE_PAY);
                $state = floatval($order_info['shipping_fee']) > 0 && $state;
               break;

			//调整商品费用
        	case 'spay_price':
        	    $state = ($order_info['order_state'] == ORDER_STATE_NEW) ||
        	       ($order_info['payment_code'] == 'offline' && $order_info['order_state'] == ORDER_STATE_PAY);
				   $state = floatval($order_info['goods_amount']) > 0 && $state;
        	   break;

            //发货
            case 'store_send':
                $state = !$order_info['lock_state'] && $order_info['order_state'] == ORDER_STATE_PAY && !$order_info['chain_id'];
                break;

            //收货
            case 'receive':
                $state = !$order_info['lock_state'] && $order_info['order_state'] == ORDER_STATE_SEND;
                break;

            //门店自提完成
            case 'chain_receive':
                $state = !$order_info['lock_state'] && in_array($order_info['order_state'],array(ORDER_STATE_NEW,ORDER_STATE_PAY)) &&
                $order_info['chain_code'];
                break;

            //评价
            case 'evaluation':
                $state = !$order_info['lock_state'] && !$order_info['evaluation_state'] && $order_info['order_state'] == ORDER_STATE_SUCCESS;
                break;

            case 'evaluation_again':
                $state = !$order_info['lock_state'] && $order_info['evaluation_state'] && !$order_info['evaluation_again_state'] && $order_info['order_state'] == ORDER_STATE_SUCCESS;
                break;

            //锁定
            case 'lock':
                $state = intval($order_info['lock_state']) ? true : false;
                break;

            //快递跟踪
            case 'deliver':
                $state = !empty($order_info['shipping_code']) && in_array($order_info['order_state'],array(ORDER_STATE_SEND,ORDER_STATE_SUCCESS));
                break;

            //放入回收站
            case 'delete':
                $state = in_array($order_info['order_state'], array(ORDER_STATE_CANCEL,ORDER_STATE_SUCCESS)) && $order_info['delete_state'] == 0;
                break;

            //永久删除、从回收站还原
            case 'drop':
            case 'restore':
                $state = in_array($order_info['order_state'], array(ORDER_STATE_CANCEL,ORDER_STATE_SUCCESS)) && $order_info['delete_state'] == 1;
                break;

            //分享
            case 'share':
                $state = true;
                break;

        }
        return $state;

    }

    /**
     * 联查订单表订单商品表
     *
     * @param array $condition
     * @param string $field
     * @param number $page
     * @param string $order
     * @return array
     */
    public function getOrderAndOrderGoodsList($condition, $field = '*', $page = 0, $order = 'rec_id desc') {
        return $this->table('b2b_order_goods,b2b_order')->join('inner')->on('order_goods.order_id=orders.order_id')->where($condition)->field($field)->page($page)->order($order)->select();
    }

    public function getOrderAndOrderGoodsCount($condition) {
        return $this->table('b2b_order_goods,b2b_order')->join('inner')->on('order_goods.order_id=orders.order_id')->where($condition)->count();
    }

    public function getGoodsAllPriceJoin($condition) {
        return $this->table('b2b_order_goods,b2b_order')->join('inner')->on('order_goods.order_id=orders.order_id')->where($condition)->field('goods_pay_price')->sum('goods_pay_price');
    }

    //获取商品累计成交价格
    public function getGoodsAllPrice($condition = array()) {
        return $this->table('b2b_order_goods')->field('goods_pay_price')->where($condition)->sum('goods_pay_price');
    }

    /**
     * 订单销售记录 订单状态为20、30、40时
     * @param unknown $condition
     * @param string $field
     * @param number $page
     * @param string $order
     */
    public function getOrderAndOrderGoodsSalesRecordList($condition, $field="*", $page = 0, $order = 'rec_id desc') {
        $condition['order_state'] = array('in', array(ORDER_STATE_PAY, ORDER_STATE_SEND, ORDER_STATE_SUCCESS));
        $ret = $this->getOrderAndOrderGoodsList($condition, $field, $page, $order);
        //分销渠道订单，过滤购买用户名显示
        foreach ($ret as $k => $val) {
            if ('3' == $val['order_from']) {
                $ret[$k]['buyer_name'] = '汉购' . $val['add_time'];
            }

            $ret[$k]['buyer_name'] = str_sub($ret[$k]['buyer_name'], 1, 0) .'***'. str_sub($ret[$k]['buyer_name'], 1, -1);
        }
        return $ret;
    }

    /**
     * 取得其它订单类型的信息
     * @param unknown $order_info
     */
    public function getOrderExtendInfo(& $order_info) {
        //取得预定订单数据
        if ($order_info['order_type'] == 2) {
            $result = Logic('order_book')->getOrderBookInfo($order_info);
            //如果是未支付尾款
            if ($result['data']['if_buyer_repay']) {
                $result['data']['order_pay_state'] = false;
            }
            $order_info = $result['data'];
        }
    }

    /**
     * 创建分销订单
     * @param unknown $param
     */
    public function createFxOrder($param){
        $ret = array(); //返回参数
        $param = json_decode($param);

        if(empty($param->key) || $param->key != C('order_create_key')){
        	$ret = array('error'=>1001 , 'msg'=>'invalid key');
        	return $ret;
        }

        $provine = str_replace('省' , '', $param->provine);
        $city = str_replace('市', '', $param->city);

        $p_where['area_name'] = array('like' , '%'.$provine.'%');
        $p_where['area_deep'] = 1;
        $p_field = 'area_id';
        $provine_id = Model('area')->getAreaInfo($p_where , $p_field);
        $param->provine_id = $provine_id['area_id']; //省级ID

        $c_where['area_name'] = array('like' , '%'.$city.'%');
        $c_where['area_deep'] = 2;
        $c_field = 'area_id';
        $city_id = Model('area')->getAreaInfo($c_where , $c_field);
        $param->city_id = $city_id['area_id'];//市级ID

        $param->order_time =$param->order_time?$param->order_time:time();

        if(empty($param->order_sn) && $param->payment_code == 'fenxiao'){//集采订单不用分销订单号
            $ret = array('error'=>1001 , 'msg'=>'缺少订单编号');
            return $ret;
        }

        if(empty($param->receiver)){
            $ret = array('error'=>1001 , 'msg'=>'缺少收货人信息');
            return $ret;
        }

        if(empty($param->provine_id)){
            //$ret = array('error'=>1001 , 'msg'=>'收货人省信息错误');
            //return $ret;
        	$param->provine_id = 18;
        }

        if(empty($param->city_id)){
            //$ret = array('error'=>1001 , 'msg'=>'收货人市信息错误');
            //return $ret;
        	$param->city_id = 258;
        }

        if(count($param->item)<1){
            $ret = array('error'=>1001 , 'msg'=>'订单商品详情缺失');
            return $ret;
        }
        $memberWhere['member_id'] = intval($param->buy_id);
        $memberWhere['member_type'] = array('in' , array('fenxiao' ,'jicai'));
        $buyer_info = Model('member')->getMemberInfo($memberWhere , 'member_id , member_name, member_email');
        if(empty($buyer_info['member_name'])){
            $ret = array('error'=>1001 ,'msg'=>'分销商信息不存在');
            return $ret;
        }
        $param->member_name = $buyer_info['member_name'];
        $param->member_email = $buyer_info['member_email'];
        $goods_ids = array();
        $goods_num = array();
        foreach($param->item as $k=>$v){
            $goods_ids[] = intval($v->goods_id);
            $goods_num[$v->goods_id] = array('num'=>intval($v->num) , 'price'=>floatval($v->price));
        }
        $gwhere['goods_id'] = array('in' , $goods_ids);
        $gWhere['goods_state'] = 1 ;//0下架，1正常，10违规（禁售）
        $goods_info = $this->table('b2b_goods')->field('goods_id,goods_name,store_id,store_name,goods_image,goods_price,gc_id,goods_cost,tax_input,tax_output')->where($gwhere)->select();
        if(count($goods_info) != count($goods_ids)){
            $ret = array('error'=>1001 , 'msg'=>'商品编号匹配不成功'.json_encode($goods_ids));
            return $ret;
        }

        if(empty($param->mobile)){
            $ret = array('error'=>1001 , 'msg'=>'缺少手机号码');
            return $ret;
        }

        if(empty($param->order_from) || !in_array($param->order_from , array('3' , '4', '5'))){  //3分销4集采5B2B
            $ret = array('error'=>1001 , 'msg'=>'订单来源信息有误');
            return $ret;
        }

        try{
            $model = Model('order');
            $model->beginTransaction();
            $order_no = Logic('buy_1')->gen_order_no() ;
            //订单支付表
            $pay['buyer_id'] = intval($param->buy_id);
            //$pay['api_pay_state'] = $param->payment_code=='fenxiao'?1:0;
            $pay['api_pay_state'] = $param->order_from=='3'?1:0;
            $pay['order_no'] = $order_no ;
            $pay_sn = $this->_createFxPay($pay);

            //订单拆单
            $goods_item = array();
            foreach($goods_info as $k=>$v){
                $goods_item[$v['store_id']]['store_name'] = $v['store_name'];
                $goods_item[$v['store_id']]['item'][] = array(
                    'goods_id'=>$v['goods_id'] ,
                    'goods_name'=>$v['goods_name'],
                    'num'=>$goods_num[$v['goods_id']]['num'],
                    //'price'=>$goods_num[$v['goods_id']]['price'],
                	'fx_price'=>$goods_num[$v['goods_id']]['price'],
                	'goods_price'=>$v['goods_price'],
                    'gc_id'=>intval($v['gc_id']),
                    'goods_image'=>$v['goods_image'],
                    'goods_cost'=>$v['goods_cost'],
                    'tax_input'=>$v['tax_input'],
                    'tax_output'=>$v['tax_output'],
                );
            }
            $this->_createFxOrderItem($goods_item, $param, $pay_sn, $order_no);
            if( $param->order_from == '3' ) {
            	$this->_createFxorderIndex($param, $pay_sn);
            	$this->_createFxorderSub($param, $pay_sn);
            }

            $model->commit();
            return array('error'=>1000 , 'msg'=>'订单创建成功');
        }catch (Exception $e){
            $model->rollback();
            return array('error'=>1001 , 'msg'=>$e->getMessage());
        }
    }

    //创建订单支付表
    private function _createFxPay(&$pay){
        $pay_sn = Logic('buy_1')->makePaySn($pay['buyer_id'],$pay['order_no']);
        if($pay_sn==false){
            throw new Exception('支付码生成失败');
        }
        $pay['pay_sn'] = $pay_sn;
        $insert = $this->addOrderPay($pay);
        if($insert==false){
            throw new Exception('订单支付表插入失败');
        }
        return $pay_sn;
    }
    /**
     * 订单明细表
     * @param array  $goods_item
     * @param object $param
     * @param int    $pay_sn
     */
    private function _createFxOrderItem($goods_item , $param , $pay_sn, $order_no){
    	//先统计店铺订单总额，方便计算店铺订单折扣
    	$rpt = array() ;
    	$store_order_total = $store_discount_total = array() ;
    	$hango_total = 0; //按汉购商品销售价计算总值
    	foreach($goods_item as $k=>$v){
    		$store_order_total[ $k ] = $store_discount_total[$k] = 0;
    		$store_goods_total = 0 ;
    		foreach($v['item'] as $key=>$val){
    			$hango_store_total[ $k ] += $val['num'] * $val['goods_price'];
    			$store_order_total[ $k ] += $val['num'] * $val['fx_price'];
    			$hango_total += $val['num'] * $val['goods_price'];
    			$store_goods_total += $val['num'] * $val['goods_price'];
    		}
    		$store_discount_total[ $k ] = $store_goods_total - $store_order_total[ $k ];
    	}

    	//汉购销售价总计与分销订单支付额差额为红包值
    	$discount = $hango_total - $param->amount ;
    	if( $discount != 0 ) {
    		$rpt = Logic('buy_1')->parseFxOrderRpt($store_order_total, $store_discount_total, $discount);
    	}

    	/*if( $param->discount > 0 ) {
    		$rpt = Logic('buy_1')->parseOrderRpt($store_order_total, $param->discount);
    	}*/

        $num = 0;
        //拆单
        foreach($goods_item as $k=>$v){
            /** @var storeModel $storeModel */
            $storeModel = Model('store');
            $storeInfo = $storeModel->getStoreInfo(array('store_id'=>$k));
            $num++;
            $order = array();
            $orderCommon = array();
            $order['order_sn'] = $order_no . sprintf("%03d", $num);
            $order['pay_sn'] = $pay_sn;
            $order['store_id'] = $k;
            $order['store_name'] = $v['store_name'];
            $order['manage_type'] = $storeInfo['manage_type'];
            $order['buyer_id'] = intval($param->buy_id);
            $order['buyer_name'] = $param->member_name;
            $order['buyer_email'] = $param->member_email;
            $order['buyer_phone'] =$param->mobile;
            $order['add_time'] = $param->order_time;
            $order['payment_code'] = $param->payment_code;
            $order['order_amount'] = isset($rpt[0][$k]) ? $rpt[0][$k] : $store_order_total[$k] ;
            $order['goods_amount'] = $order['order_amount'];
            if($param->order_from=='3'){
                $order['payment_time'] = $param->order_time ? $param->order_time : time();
                $order['order_state'] = 20;
            }else{
                $order['order_state'] = 10;
            }
            $order['order_from'] = $param->order_from ? $param -> order_from : '3';
            $order['fx_order_id'] = $param->order_sn;
            //折扣信息保存(平台红包代替分销平台的折扣)
            $order['rpt_amount'] = isset($rpt[1][$k]) && $rpt[1][$k] ? $rpt[1][$k] : '0.00' ;
            $order['rpt_bill'] = $order['rpt_amount'] ;

            //设置为发货状态
            if($param->is_ship==1){
            	$order['order_state'] = 30;
            	$order['make_send_time'] = $param->order_time+600;
            }

            $order_id = $this->addOrder($order);
            if($order_id == false){
              throw new Exception('订单信息表生成失败');
            }
            $orderCommon['order_id']= $order_id;
            $orderCommon['store_id']= $k;
            $orderCommon['order_message'] = $param->remark;
            $orderCommon['reciver_name'] = $param->receiver;
            $orderCommon['reciver_province_id'] = $param->provine_id;
            $orderCommon['reciver_city_id'] = $param->city_id;
            $receiveinfo['address'] = $param->provine." ".$param->city." ".$param->area." ".$param->address;
            $receiveinfo['phone'] = $param->mobile;
            $receiveinfo['area'] = $param->provine." ".$param->city." ".$param->area;
            $receiveinfo['street'] = $param->address;
            $receiveinfo['mob_phone'] = $param->mobile;
            $receiveinfo['tel_phone'] = '';
            $receiveinfo['dlyp'] = '';
            $orderCommon['reciver_info'] = serialize($receiveinfo);
            //折扣信息保存(平台红包代替分销平台的折扣)
            $promotion_info = array();
            $promotion_info[] = $order['rpt_amount']>0?
                array("平台红包", "使用{$order['rpt_amount']}元红包 编码：123456789987654321"):
                array("推广佣金", "使用".abs($order['rpt_amount'])."元分销渠道推广佣金");
            // 推广佣金：使用xx元分销渠道推广佣金
            //$orderCommon['promotion_info'] = $order['rpt_amount'] > '0' ? serialize($promotion_info) : "";
           // $orderCommon['promotion_total'] = $order['rpt_amount'] > '0' ? $order['rpt_amount'] : "0.00";
            $orderCommon['promotion_info'] = $order['rpt_amount'] != '0.00' ? serialize($promotion_info) : "";
            $orderCommon['promotion_total'] = $order['rpt_amount'] ;

            //设置为发货状态
            if($param->is_ship==1){
            	$orderCommon['shipping_time'] = $param->order_time+600;
            }

            $ordercommonid = $this->addOrderCommon($orderCommon);
            if($ordercommonid== false){
               throw new Exception('订单信息附加表生成失败');
            }

            //order_goods表分拆红包，计算goods_pay_price
            $promotion_total = $order['rpt_amount'] ;
            //$promotion_rate = abs(number_format($promotion_total/$store_order_total[$k],5));
            $promotion_rate = number_format($promotion_total/$hango_store_total[$k],5) ;
            if ($promotion_rate <= 1) {
            	$promotion_rate = floatval(substr($promotion_rate,0,5));
            } else {
            	$promotion_rate = 0;
            }
            $goodsCount = count($v['item']);

            $rptSum = $promotion_sum = 0;
            $i = 0;
            $order_goods = array() ;

            foreach($v['item'] as $key=>$val){
                $order_goods[$i]['order_id'] = $order_id;
                $order_goods[$i]['goods_id'] = $val['goods_id'];
                $order_goods[$i]['goods_name'] = $val['goods_name'];
                //$order_goods[$i]['goods_price'] = $val['price'];
                $order_goods[$i]['goods_price'] = $val['goods_price'];
                $order_goods[$i]['goods_num'] = $val['num'];
                $order_goods[$i]['goods_cost'] = $val['goods_cost']*$val['num'];
                $order_goods[$i]['tax_input'] = $val['tax_input'];
                $order_goods[$i]['tax_output'] = $val['tax_output'];
                $order_goods[$i]['goods_image'] = $val['goods_image'];
                $order_goods[$i]['gc_id'] = $val['gc_id'];
                //$order_goods['goods_pay_price'] = $val['price']*$val['num'];
                $order_goods[$i]['store_id'] = $k;
                $order_goods[$i]['manage_type'] = $storeInfo['manage_type'];
                $order_goods[$i]['buyer_id'] = $param->buy_id;


                //计算商品金额
                //$goods_total = $val['price'] * $val['num'];
                $goods_total = $val['goods_price'] * $val['num'];
                $promotion_value = sprintf("%.2f", $goods_total*($promotion_rate) )  ;
                //$order_goods[$i]['goods_pay_price'] = number_format(($goods_total - $promotion_value < 0 ? 0 : $goods_total - $promotion_value),2);
                $order_goods[$i]['goods_pay_price'] = sprintf("%.2f", ($goods_total - $promotion_value < 0 ? 0 : $goods_total - $promotion_value) )  ;
                $promotion_sum += $promotion_value;
                $order_goods[$i]['rpt_amount'] = $promotion_value ;
                $order_goods[$i]['rpt_bill'] = $order_goods[$i]['rpt_amount'] ;

                $i++;
            }

            //将因舍出小数部分出现的差值补到最后一个商品的实际成交价中(商品goods_price=0时不给补，可能是赠品)
            //if ($promotion_total > $promotion_sum) {
            if (abs($promotion_total) > abs($promotion_sum)) {
            	$i--;
            	for($i;$i>=0;$i--) {
            		if (floatval($order_goods[$i]['goods_price']) != 0
            				&& floatval($order_goods[$i]['goods_price']) > $promotion_total - $promotion_sum )
            		{
            			$order_goods[$i]['goods_pay_price'] -= $promotion_total - $promotion_sum;
            			break;
            		}
            	}
            }
            //order_goods表rpt_amount补余
            //if( $promotion_total>0 && $promotion_sum < $promotion_total ){
            if( $promotion_total!=0.00 && abs($promotion_sum) < abs($promotion_total) ){
            	$order_goods[$goodsCount-1]['rpt_amount'] += $promotion_total-$promotion_sum;
            	$order_goods[$goodsCount-1]['rpt_bill'] = $order_goods[$goodsCount-1]['rpt_amount'] ;
            }
            /** @var store_bind_classModel $store_bind_class */
            $store_bind_class = Model('store_bind_class');
            $commis_rate_list = $store_bind_class->getStoreGcidCommisRateList($order_goods);
            foreach ($order_goods as $key=>$value){
                $order_goods[$key]['commis_rate'] = isset($commis_rate_list[$value['store_id']][$value['gc_id']])?
                    $commis_rate_list[$value['store_id']][$value['gc_id']]:200;
                $order_goods[$key]['manage_type'] = $storeInfo['manage_type'];
                /** 补充平台商家订单商品成本 */
                if($storeInfo['manage_type'] == 'platform') {
                    $order_goods[$key]['goods_cost'] =
                        number_format($value['goods_pay_price']-$value['goods_pay_price']*$order_goods[$key]['commis_rate']/100,2);
                }
            }
        	$insert = $this->addOrderGoods($order_goods);
            if (!$insert) {
                throw new Exception('订单保存失败[未生成商品数据]');
            }

            //保存日志表，用户crontab任务统计订单数据
            $this->_addFxorderLog( $order_id ) ;
        }
    }

    private function _createFxorderIndex($param , $pay_sn){
    	$mbof = Model('b2c_order_fenxiao');
    	//拼多多订单使用update,其他订单使用insert
    	if( $param->save_type == 'save' )
    	{
    		$orderno = $param->order_sn;
    		$bof['pay_sn'] = $pay_sn;
    		$bof['is_ship'] = 0;
    		$bof['order_time'] = $param->order_time;
    		$filter = array( 'orderno' => $orderno );
    		$res = $mbof -> where( $filter ) -> update( $bof ) ;
    	}
    	else
    	{
    		$bof['orderno'] = $param->order_sn;
    		$bof['pay_sn'] = $pay_sn;
    		$bof['is_ship'] = 0;
    		$bof['order_time'] = $param->order_time;
    		$bof['log_time'] = time();
    		$bof['sourceid'] = $param->buy_id;
    		$bof['source'] = $param->member_name;
    		$mbof = model('b2c_order_fenxiao');
    		$res = $mbof->insert($bof) ;
    	}
    	if( !$res ) {
    		throw new Exception('分销订单信息保存失败');
    	}
    }

    private function _createFxorderSub($param, $pay_sn)
    {
    	$items = objectToArray( $param -> item ) ;
    	foreach ( $items as $_item ) {
    		if( !isset( $_item['oid'] ) || !$_item['oid'] ) continue ;
    		$order_sub = array();
    		$order_sub['orderno'] = $param->order_sn ;
    		$order_sub['oid'] = $_item['oid'] ;
    		$order_sub['product_id'] = $_item['goods_id'] ;
    		$order_sub['num'] = $_item['num'] ;
    		$order_sub['pay_sn'] = $pay_sn ;

            //如果分销订单原来存在，就先删除
            $condition = array();
            $condition['orderno'] = $param->order_sn ;
            $isExis = Model("b2c_order_fenxiao_sub")->where($condition)->find();
            if($isExis){
                Model("b2c_order_fenxiao_sub")->where($condition)->delete();
            }
            $res = Model("b2c_order_fenxiao_sub")->insert( $order_sub ) ;
    		if( !$res ) {
    			throw new Exception('分销子订单数据插入失败'.json_encode($order_sub));
    		}
    	}
    }
    private function _addFxorderLog( $order_id )
    {
    	$data = array();
    	$data['order_id'] = $order_id;
    	$data['log_role'] = 'system';
    	$data['log_msg'] = '创建了分销订单';
    	$data['log_user'] = '系统';
    	$data['log_orderstate'] = '20';
    	$res = $this->addOrderLog($data);
    	if( !$res ) {
    		throw new Exception('订单日志保存失败');
    	}
    }
    /***
     * 分销平台发货订单推送
     * @author ljq
     */
    public function setOrderSend($order_info , $shipping_express_id , $shipping_code){
        $limit_fenxiao = array('pinduoduo','youzan','renrendian','juanpi','mengdian','fanli','beibeiwang');
        if (!in_array($order_info['buyer_name'], $limit_fenxiao)) {
        	return false;
        }

        $memberid = intval($order_info['buyer_id']);
		$fxOrderSn = $order_info['fx_order_id'];
		$express_list = Model('express')->getExpressList();
		//die($fxOrderSn."\n".$memberid."\n".$shipping_express_id."\n".$shipping_code);
		$fenxiao_service = Service("Fenxiao") ;
    	$fx_members = $fenxiao_service -> getFenxiaoMembers() ;
        if(empty($fxOrderSn) || !array_key_exists($memberid, $fx_members) || empty($shipping_express_id) || empty($shipping_code)){
            return false;
        }
        //拼多多只发送一次
		$fx_members_flip = array_flip( $fx_members );
		$pdd_member_id = $fx_members_flip[ 'pinduoduo' ] ;
		if($memberid==$pdd_member_id){
			$data = array();
			$data['source'] = $order_info['buyer_name'];
			$data['sourceid'] = $memberid;
			$data['orderno'] = $fxOrderSn;
			$data['logi_no'] = $shipping_code;
			$data['logi_name'] = $express_list[$shipping_express_id]['e_name'];
		    $data['num'] = $order_info['extend_order_goods'][0]['goods_num'];
		    $data['full_ship'] = 1;
		    $data['oid'] = '';
		    $this->_sendApiRequest($data);
		}
		$gids = array();  //获取发货的商品编号信息
		foreach($order_info['extend_order_goods'] as $k =>$v){
			$gids[] = $v['goods_id'];
		}
		$result = $this->_getFxOrderItem($fxOrderSn , $gids);
		//var_dump($result);
		if(count($result) > 0){
		    foreach($result as $k=>$v){
		        $data = array();
		        $data['source'] =  $order_info['buyer_name'];
		        $data['sourceid'] = $memberid;
		        $data['orderno'] = $fxOrderSn;
		        $data['logi_no'] = $shipping_code;
		        $data['logi_name'] = $express_list[$shipping_express_id]['e_name'];
		        $data['num'] = $v['num'];
		        $data['full_ship'] = 0;
		        $data['oid'] = $v['oid'];
		        $this->_sendApiRequest($data);
		    }
		}
		return callback(true , '分销发货订单推送成功');
    }

    /**
     * 批量发货
     * @author wx
     * @param string $csvFilePath   文件完整路径
     * @return bool
     * 文件格式
     * |---------------------------------------------------
     * | order_id           |logi_name      |logi_no
     * |---------------------------------------------------
     * |150419164925478002  |EMS            |789654123
     * |---------------------------------------------------
     * |150419164925478002  |中通快递        |789654123
     */
    public function bulkShipment($csvFilePath)
    {
        if(!is_file($csvFilePath)){
            return callback(false , '文件不存在');
        }

        $data = $this->_excelToArray($csvFilePath);
        if(!count($data) > 1){
            return callback(false ,'订单数据有误');
        }
        /**
         * 获取快递公司
         */
        $express_list       = Model('express')->getExpressList();
        $expressIds         = Model('store_extend')->where(array('store_id'=>$_SESSION['store_id']))->field('express')->find();
        $express            = explode(',',$expressIds['express']);
        foreach ($express as $k=>$v) {
            $express[$v] = $express_list[$v];
            unset($express[$k]);
        }
        /**
         * 比较格式
         */
        $arrTpl = array('0'=>'order_id','1'=>'logi_name','2'=>'logi_no');//数组模板
        if(count(array_diff($arrTpl,$data[0])) > 0){        //如果跟数组模板不同
            return array('state'=>'false','msg'=>'文件格式错误');
        }
        unset($data[0]);
        $logic_order = Logic('order');
        /**
         * order_info查询条件
         */
        $condition = array();
        $condition['store_id'] = $_SESSION['store_id'];
        /**
         * 默认发货地址
         */
        $model_daddress = Model('daddress');
        $daddress = $model_daddress->getAddressInfo(array('store_id'=>$_SESSION['store_id'],'is_default'=>'1'),'address_id');
		$succNum = 0;   //成功条数
		$failNum = 0;   //失败条数
		$failOrderids = $errorMsg = array();
		//update by ljq 优化发货速度
		$sn = array();
		$order_list = array();
		foreach($data as $k=>$v){
		    $sn[] = preg_replace('/\D/','',$v[0]);
		}

		if(count($sn)>0){
		    $condition['order_sn'] = array('in' , $sn);
		    $resOrder  = $this->getOrderList($condition,'','','',false,array('order_common','order_goods'));
		    foreach($resOrder as $k=>$v){
		        $order_list[$v['order_sn']] = $v;
		    }
		}
		//优化结束
		foreach ($data as $k => $v) {

            $sn = preg_replace('/\D/','',$v[0]);
            $condition['order_sn'] = $sn;

            if( !preg_match("/^[0-9a-zA-Z,]+$/i",   trim($v[2]) ) ) {
            	$failNum++;
            	$failOrderids[] = $sn;
            	$errorMsg[] = $sn . " 运单号({$v[2]})只能字母、数字以及'，'的组合！" ;
            	continue;
            }

            //$order_info = $this->getOrderInfo($condition,array('order_common','order_goods'));

            if(empty($order_list[$sn]['order_id'])){
            	$failNum++;
            	$failOrderids[] = $sn;
            	$errorMsg[] = $sn . ' 订单不存在！' ;
            	continue;
            }
            /**
             * 判断订单状态
             */
            if($order_list[$sn]['lock_state']==1 || in_array($order_list[$sn]['order_state'],array(ORDER_STATE_NEW,ORDER_STATE_SEND,ORDER_STATE_SUCCESS)))
            {
                $failNum++;
                $failOrderids[] = $sn;
				$errorMsg[] = $sn . ' 该订单已经发货了，不能重新发货！' ;
                continue;
            }
            $post['shipping_express_id'] = 0 ;
            foreach ($express as $ek => $ev) {
                if($ev['e_name'] == $v[1]){
                    $post['shipping_express_id'] = $ev['id'];//快递公司ID
                }
            }

            if( $post['shipping_express_id'] == 0 ) {
            	$failNum++ ;
            	$failOrderids[] = $sn ;
            	$errorMsg[] = $sn . ' 发货失败，系统不存在快递公司:' . $v[1] ;
            	continue ;
            }

            $post['shipping_code']              = $v[2];    //运单号
            /*$post['reciver_info']['address']    = $order_info['extend_order_common']['reciver_info']['reciver_area'].' '.$order_info['extend_order_common']['reciver_info']['street'];
            $post['reciver_info']['phone']      = $order_info['extend_order_common']['reciver_info']['mob_phone'].','.$order_info['extend_order_common']['reciver_info']['tel_phone'];
            $post['reciver_info']['area']       = $order_info['extend_order_common']['reciver_info']['reciver_area'];
            $post['reciver_info']['street']     = $order_info['extend_order_common']['reciver_info']['street'];
            $post['reciver_info']['mob_phone']  = $order_info['extend_order_common']['reciver_info']['mob_phone'];
            $post['reciver_info']['tel_phone']  = $order_info['extend_order_common']['reciver_info']['tel_phone'];
            $post['reciver_info']['dlyp']       = $order_info['extend_order_common']['reciver_info']['dlyp'];*/
            $post['daddress_id']                = $daddress['address_id'];
            //$post['reciver_info']               = serialize($post['reciver_info']);
            $result = $logic_order->changeOrderSend($order_list[$sn], 'seller', $_SESSION['seller_name'], $post);
            $result['state']== true ? $succNum++:$failNum++;
            $result['state']== false and $failOrderids[] = $sn;
            $result['state']== false and $errorMsg[] = $sn . ' 发货记录修改失败' ;
        }
        $ret = array('totals'=>count($data) , 'succNum'=>$succNum , 'failNum'=>$failNum , 'failOrderids'=>$failOrderids, 'errorMsg' => $errorMsg);
        return $ret;
    }

    /**
     * csv、Excel转数组
     * @author wx
     * @param string $filePath      文件路径
     * @param int $sheet            第几个sheet 从0开始
     * @return array|bool
     */
    private function _excelToArray($filePath = '', $sheet = 0)
    {
        if (empty($filePath) or !file_exists($filePath)) {
            return false;
        }
        $fileType = explode('.',$filePath);
        $fileType = $fileType[count($fileType)-1];

        //csv类型直接str_getcsv转换
        if($fileType == 'csv'){
            $lines = array_map('str_getcsv', file($filePath));;
            $result = array();
            for ($i = 0; $i < count($lines); $i++) {        //循环读取每行内容注意行从第1行开始($i=0)
                $obj = $lines[$i];
                foreach ($obj as $k => $v) {
                    $result[$i][] = mb_convert_encoding($v, 'UTF-8', 'gbk');
                }
            }
            return $result;
        }

        //excel类型 PHPExcel类库转换
        vendor('PHPExcel/Reader/Excel2007');
        vendor('PHPExcel/Reader/Excel5');
        $PHPReader = new PHPExcel_Reader_Excel2007();        //建立reader对象
        if (!$PHPReader->canRead($filePath)) {
            $PHPReader = new PHPExcel_Reader_Excel5();
            if (!$PHPReader->canRead($filePath)) {
                return false;
            }
        }
        $PHPExcel = $PHPReader->load($filePath);
        $currentSheet = $PHPExcel->getSheet($sheet);            //读取excel文件中的指定工作表
        $allColumn = $currentSheet->getHighestColumn();         //*取得最大的列号
        $allRow = $currentSheet->getHighestRow();               //取得一共有多少行
        $data = array();
        for ($rowIndex = 1; $rowIndex <= $allRow; $rowIndex++) {        //循环读取每个单元格的内容。注意行从第1行开始，列从A开始
            for ($colIndex = 'A'; $colIndex <= $allColumn; $colIndex++) {
                $addr = $colIndex . $rowIndex;
                $cell = $currentSheet->getCell($addr)->getValue();
                if ($cell instanceof PHPExcel_RichText) {       //转换字符串
                    $cell = $cell->__toString();
                }
                $data[$rowIndex-1][] = $cell;
            }
        }
        return $data;
    }

    /***
     * 获取分销订单子订单
     *
     */
    private function _getFxOrderItem($fxOrderSn , $gids){
        if(empty($fxOrderSn)) exit();
        $subModel = Model('b2c_order_fenxiao_sub');
        $sWhere['orderno'] = $fxOrderSn;
        count($gids) > 0 and $sWhere['product_id'] = array('in' , $gids);
        $result = $subModel->field('oid,num')->where($sWhere)->select();
        return $result;
    }

    private function _sendApiRequest($data){
    	$service = Service("Fenxiao") ;
    	$fx_members = $service -> getFenxiaoMembers() ;
    	$fx_members = array_values( $fx_members );
    	if( in_array($data['source'], $fx_members) ) {
    		$service -> init( $data['source'] ) ;

            $data['logi_no'] = preg_replace('/\s/', '', $data['logi_no']);
    		$res = $service -> pushiship($data) ;
            $res = json_decode($res);
    		if($res->succ==0){
    			//发送错误日志
    			$arr = array();
    			$arr['orderno'] = $data['orderno'];
    			$arr['error'] = $res->msg?$res->msg:'暂无';
    			$arr['log_time'] = time();
    			$arr['sourceid'] = $data['sourceid'];
    			$arr['source'] = $data['source'];
    			$arr['log_type'] = 'ship';
    			$this->_addSendErrorLog($arr);
    		}
    	}
    }

    private function _addSendErrorLog($data){
        $error_model= Model('b2c_order_fenxiao_error');
        $error_model->insert($data);
    }

    public function cancelFenxiaoOrder($orderno){
        $condition = array();
        $condition['orderno'] = $orderno;

        $res1 = Model('b2c_order_fenxiao')->where($condition)->find();
        if($res1){
            Model('b2c_order_fenxiao')->where($condition)->delete();
        }
        $res2 = Model('b2c_order_fenxiao_sub')->where($condition)->find();
        if($res2){
            Model('b2c_order_fenxiao')->where($condition)->delete();
        }
    }
}
