<?php
/**
 * 订单管理d
 *
 *
 *
 *
 * @汉购网提供技术支持 授权请购买shopnc授权
 * @license    http://www.hangowa.com
 * @link       交流群号：
 */
defined('ByShopWWI') or exit('Access Invalid!');

class orderModel extends Model
{
    private $_areaCache = array(array(), array(), array(),);
    private $_cityPatternCache = array();

    /**
     * 取单条订单信息
     *
     * @param unknown_type $condition
     * @param array $extend 追加返回那些表的信息,如array('order_common','order_goods','store')
     * @return unknown
     */
    public function getOrderInfo($condition = array(), $extend = array(), $fields = '*', $order = '', $group = '')
    {
        $order_info = $this->table('orders')->field($fields)->where($condition)->group($group)->order($order)->find();
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
        if (in_array('order_common', $extend)) {
            $order_info['extend_order_common'] = $this->getOrderCommonInfo(array('order_id' => $order_info['order_id']));
            $order_info['extend_order_common']['reciver_info'] = unserialize($order_info['extend_order_common']['reciver_info']);
            $order_info['extend_order_common']['invoice_info'] = unserialize($order_info['extend_order_common']['invoice_info']);
        }

        //追加返回店铺信息
        if (in_array('store', $extend)) {
            $order_info['extend_store'] = Model('store')->getStoreInfo(array('store_id' => $order_info['store_id']));
        }

        //返回买家信息
        if (in_array('member', $extend)) {
            $order_info['extend_member'] = Model('member')->getMemberInfoByID($order_info['buyer_id']);
        }

        //追加返回商品信息
        if (in_array('order_goods', $extend)) {
            //取商品列表
            $order_goods_list = $this->getOrderGoodsList(array('order_id' => $order_info['order_id']));
            $order_info['extend_order_goods'] = $order_goods_list;
        }

        return $order_info;
    }

    public function getOrderCommonInfo($condition = array(), $field = '*')
    {
        return $this->table('order_common')->where($condition)->field($field)->find();
    }

    public function getOrderPayInfo($condition = array(), $master = false)
    {
        return $this->table('order_pay')->where($condition)->master($master)->find();
    }

    public function setCheck($condition = array(), $check_status)
    {
        $data = array();
        $data['check_status'] = $check_status;
        return $this->table('orders')->where($condition)->update($data);
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
    public function getOrderPayList($condition, $pagesize = '', $filed = '*', $order = '', $key = '')
    {
        return $this->table('order_pay')->field($filed)->where($condition)->order($order)->page($pagesize)->key($key)->select();
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
     * @param int $refund_only 仅显示有退款
     * @return array $order_list
     */
    public function getStoreOrderList($store_id, $order_sn, $buyer_name, $state_type, $query_start_date,
                                      $query_end_date, $skip_off, $fields = '*', $extend = array(), $chain_id = null, $extra_cond = array(), $refund_only = 0)
    {
        $condition = array();
        $condition['store_id'] = $store_id;
        if (preg_match('/^\d{10,20}$/', $order_sn)) {
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
        $allow_state_array = array('state_new', 'state_pay', 'state_prepare', 'state_send', 'state_success', 'state_cancel');
        if (in_array($state_type, $allow_state_array)) {
            $condition['order_state'] = str_replace($allow_state_array,
                array(ORDER_STATE_NEW, ORDER_STATE_PAY, ORDER_STATE_PREPARE, ORDER_STATE_SEND, ORDER_STATE_SUCCESS, ORDER_STATE_CANCEL), $state_type);
        } else {
            if ($state_type != 'state_notakes') {
                $state_type = 'store_order';
            }
        }
        $if_start_date = preg_match('/^20\d{2}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $query_start_date);
        $if_end_date = preg_match('/^20\d{2}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $query_end_date);
        $start_unixtime = $if_start_date ? strtotime($query_start_date) : null;
        $end_unixtime = $if_end_date ? strtotime($query_end_date) : null;
        if ($start_unixtime || $end_unixtime) {
            $condition['add_time'] = array('second', array($start_unixtime, $end_unixtime));
        }

        if ($skip_off == 1) {
            $condition['order_state'] = array('neq', ORDER_STATE_CANCEL);
        }

        if ($state_type == 'state_new') {
            $condition['chain_code'] = 0;
        }
        if ($state_type == 'state_pay') {
            $condition['chain_code'] = 0;
        }
        if ($refund_only) {
            $condition['lock_state'] = array('gt', 0);
        }
        if ($state_type == 'state_notakes') {
            $condition['order_state'] = array('in', array(ORDER_STATE_NEW, ORDER_STATE_PAY));
            $condition['chain_code'] = array('gt', 0);
        }

        //过滤集采订单
        // not in 导致查询缓慢，数据库并无b2b支付方式
        //empty($condition['payment_code']) and $condition['payment_code'] = array('not in', array('b2b'));

        //待发货过滤申请退款的订单
        ($condition['order_state'] == ORDER_STATE_PAY) and $condition['lock_state'] = 0;

        //待发货过滤已申请电子面单的
        ($condition['order_state'] == ORDER_STATE_PAY) and $condition['is_printship'] = array('in', array(0, 3));

        if ($_GET['fenxiao_member_id'] > 0) {
            $condition['buyer_id'] = $_GET['fenxiao_member_id'];
        }
        $order_list = $this->getOrderList($condition, 20, $fields, 'order_id desc', '', $extend);

        //页面中显示那些操作
        foreach ($order_list as $key => $order_info) {

            //显示取消订单
            $order_info['if_store_cancel'] = $this->getOrderOperateState('store_cancel', $order_info);
            //显示调整费用
            $order_info['if_modify_price'] = $this->getOrderOperateState('modify_price', $order_info);
            //显示调整订单费用
            $order_info['if_spay_price'] = $this->getOrderOperateState('spay_price', $order_info);
            //显示发货
            $order_info['if_store_send'] = $this->getOrderOperateState('store_send', $order_info);
            //显示锁定中
            $order_info['if_lock'] = $this->getOrderOperateState('lock', $order_info);
            //显示物流跟踪
            $order_info['if_deliver'] = $this->getOrderOperateState('deliver', $order_info);
            //门店自提订单完成状态
            $order_info['if_chain_receive'] = $this->getOrderOperateState('chain_receive', $order_info);

            //查询消费者保障服务
            if (C('contract_allow') == 1) {
                $contract_item = Model('contract')->getContractItemByCache();
            }
            foreach ($order_info['extend_order_goods'] as $value) {
                $value['image_60_url'] = cthumb($value['goods_image'], 60, $value['store_id']);
                $value['image_240_url'] = cthumb($value['goods_image'], 240, $value['store_id']);
                $value['goods_type_cn'] = orderGoodsType($value['goods_type']);
                $value['goods_url'] = urlShop('goods', 'index', array('goods_id' => $value['goods_id']));
                //处理消费者保障服务
                if (trim($value['goods_contractid']) && $contract_item) {
                    $goods_contractid_arr = explode(',', $value['goods_contractid']);
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
    public function getStoreOrderListToExcel($store_id, $order_sn, $buyer_name, $limit, $state_type, $query_start_date, $query_end_date,
                                             $skip_off, $fields = '*', $extend = array(), $chain_id = null, $extra_cond = array(), $refund_only)
    {
        $condition = array();
        $condition['store_id'] = $store_id;
        if (preg_match('/^\d{10,20}$/', $order_sn)) {
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
        $allow_state_array = array('state_new', 'state_pay', 'state_prepare', 'state_send', 'state_success', 'state_cancel');
        if (in_array($state_type, $allow_state_array)) {
            $condition['order_state'] = str_replace($allow_state_array,
                array(ORDER_STATE_NEW, ORDER_STATE_PAY, ORDER_STATE_PREPARE, ORDER_STATE_SEND, ORDER_STATE_SUCCESS, ORDER_STATE_CANCEL), $state_type);
        } else {
            if ($state_type != 'state_notakes') {
                $state_type = 'store_order';
            }
        }
        $if_start_date = preg_match('/^20\d{2}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $query_start_date);
        $if_end_date = preg_match('/^20\d{2}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $query_end_date);
        $start_unixtime = $if_start_date ? strtotime($query_start_date) : null;
        $end_unixtime = $if_end_date ? strtotime($query_end_date) : null;
        if ($start_unixtime || $end_unixtime) {
            $condition['add_time'] = array('second', array($start_unixtime, $end_unixtime));
        }

        if ($skip_off == 1) {
            $condition['order_state'] = array('neq', ORDER_STATE_CANCEL);
        }

        if ($state_type == 'state_new') {
            $condition['chain_code'] = 0;
        }
        if ($state_type == 'state_pay') {
            $condition['chain_code'] = 0;
            $condition['lock_state'] = 0;
        }
        if ($state_type == 'state_prepare') {
            $condition['chain_code'] = 0;
            $condition['lock_state'] = 0;
        }
        if ($state_type == 'state_notakes') {
            $condition['order_state'] = array('in', array(ORDER_STATE_NEW, ORDER_STATE_PAY));
            $condition['chain_code'] = array('gt', 0);
        }

        if ($refund_only) {
            $condition['lock_state'] = array('gt', 0);
        }

        if ($_GET['fenxiao_member_id'] > 0) {
            $condition['buyer_id'] = $_GET['fenxiao_member_id'];
        }

        $order_list = $this->getOrderListToExcel($condition, $fields, 'order_id desc', $limit, $extend);
        return $order_list;
    }

    public function getStoreOrderListToExcelCount($store_id, $order_sn, $buyer_name, $limit, $state_type, $query_start_date, $query_end_date,
                                                  $skip_off, $fields = '*', $extend = array(), $chain_id = null, $extra_cond = array(), $refund_only)
    {
        $condition = array();
        $condition['store_id'] = $store_id;
        if (preg_match('/^\d{10,20}$/', $order_sn)) {
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
        $allow_state_array = array('state_new', 'state_pay', 'state_prepare', 'state_send', 'state_success', 'state_cancel');
        if (in_array($state_type, $allow_state_array)) {
            $condition['order_state'] = str_replace($allow_state_array,
                array(ORDER_STATE_NEW, ORDER_STATE_PAY, ORDER_STATE_PREPARE, ORDER_STATE_SEND, ORDER_STATE_SUCCESS, ORDER_STATE_CANCEL), $state_type);
        } else {
            if ($state_type != 'state_notakes') {
                $state_type = 'store_order';
            }
        }
        $if_start_date = preg_match('/^20\d{2}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $query_start_date);
        $if_end_date = preg_match('/^20\d{2}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $query_end_date);
        $start_unixtime = $if_start_date ? strtotime($query_start_date) : null;
        $end_unixtime = $if_end_date ? strtotime($query_end_date) : null;
        if ($start_unixtime || $end_unixtime) {
            $condition['add_time'] = array('second', array($start_unixtime, $end_unixtime));
        }

        if ($skip_off == 1) {
            $condition['order_state'] = array('neq', ORDER_STATE_CANCEL);
        }

        if ($state_type == 'state_new') {
            $condition['chain_code'] = 0;
        }
        if ($state_type == 'state_pay') {
            $condition['chain_code'] = 0;
            $condition['lock_state'] = 0;
        }
        if ($state_type == 'state_prepare') {
            $condition['chain_code'] = 0;
            $condition['lock_state'] = 0;
        }

        if ($refund_only) {
            $condition['lock_state'] = array('gt', 0);
        }
        if ($state_type == 'state_notakes') {
            $condition['order_state'] = array('in', array(ORDER_STATE_NEW, ORDER_STATE_PAY));
            $condition['chain_code'] = array('gt', 0);
        }

        if ($_GET['fenxiao_member_id'] > 0) {
            $condition['buyer_id'] = $_GET['fenxiao_member_id'];
        }

        $count = $this->getOrderListToExcelCount($condition, $fields, 'order_id desc', $limit, $extend);
        return $count;
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
    public function getOrderListToExcel($condition, $field = '*', $order = 'order_id desc', $limit = '', $extend = array(), $master = false)
    {
        empty($limit) && $limit = 2000;
        $list = $this->table('orders')->field($field)->where($condition)->order($order)->limit($limit)->master($master)->select();
        if (empty($list)) return array();
        $order_list = array();
        foreach ($list as &$order) {
            if (isset($order['order_state'])) {
                $order['state_desc'] = orderState($order);
            }
            if (isset($order['payment_code'])) {
                $order['payment_name'] = orderPaymentName($order['payment_code']);
            }

            if (!empty($extend)) $order_list[$order['order_id']] = $order;
        }
        if (empty($order_list)) $order_list = $list;
        //追加返回订单扩展表信息
        if (in_array('order_common', $extend)) {
            $order_common_list = $this->getOrderCommonList(array('order_id' => array('in', array_keys($order_list))), "*", "", 999999);
            foreach ($order_common_list as $value) {
                $order_list[$value['order_id']]['extend_order_common'] = $value;
                $order_list[$value['order_id']]['extend_order_common']['reciver_info'] = @unserialize($value['reciver_info']);
                $order_list[$value['order_id']]['extend_order_common']['invoice_info'] = @unserialize($value['invoice_info']);
            }
        }

        //追加返回商品信息
        if (in_array('order_goods', $extend)) {
            //取商品列表
//            $order_goods_list = $this->getOrderGoodsList(array('order_id'=>array('in',array_keys($order_list))), '*', 100000);
            $order_goods_list = $this->getOrderGoodsList(array('order_id' => array('in', array_keys($order_list))), 'rec_id,order_id,goods_id,goods_name,goods_price,goods_pay_price,goods_num', 100000);
            if (!empty($order_goods_list)) {
                //获取商家的供应商信息
                $goods_ids = array();
                foreach ($order_goods_list as $item => $value) {
                    $goods_ids[] = $value['goods_id'];
                }

                $goods_common_model = Model('goods');
                $goods_list = $goods_common_model->getGoodsList(array('goods_id' => array('in', $goods_ids)), 'goods_id , goods_commonid', '', '', false);

                $goods_commonids = array_under_reset($goods_list, 'goods_commonid');
                $goods_commonids = array_keys($goods_commonids);
                $goods_list = array_under_reset($goods_list, 'goods_id');
                $goods_common_list = $goods_common_model->getGoodsCommonList(array('goods_commonid' => array('in', $goods_commonids)), 'goods_commonid ,sup_id', '', '', false);
                $goods_commonids = array_under_reset($goods_common_list, 'goods_commonid');
                $sup_ids = array();
                $sups = array();
                foreach ($goods_list as $item => $value) {
                    $goods_list[$item]['sup_id'] = $goods_commonids[$value['goods_commonid']]['sup_id'];
                    if ($goods_list[$item]['sup_id']) {
                        $sup_ids[] = $goods_list[$item]['sup_id'];
                    }
                }

                if (!empty($sup_ids)) {
                    $sups = Model('store_supplier')->where(array('sup_id' => array('in', $sup_ids)))->select();
                    $sups = array_under_reset($sups, 'sup_id');
                }
                foreach ($order_goods_list as $value) {
                    $goods_commonid = $goods_list[$value['goods_id']]['goods_commonid'];
                    $sup_id = $goods_commonids[$goods_commonid]['sup_id'];
                    $value['sup'] = $sup_id ? $sups[$sup_id]['sup_name'] : '';
                    $order_list[$value['order_id']]['extend_order_goods'][] = $value;
                }
            } else {
                $order_list[$value['order_id']]['extend_order_goods'] = array();
            }
        }
        return $order_list;
    }


    public function getOrderListToExcelCount($condition, $field = '*', $order = 'order_id desc', $limit = '', $extend = array(), $master = false)
    {
        empty($limit) && $limit = 2000;
        $count = $this->table('orders')->field($field)->where($condition)->order($order)->limit($limit)->master($master)->count();
        return $count;
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
    public function getNormalOrderList($condition, $pagesize = '', $field = '*', $order = 'order_id desc', $limit = '', $extend = array())
    {
        $condition['delete_state'] = 0;
        return $this->getOrderList($condition, $pagesize, $field, $order, $limit, $extend);
    }

    public function getOrderGroupBusiness($condition = array(), $fields, $group = '', $order = '', $limit = 1000, $having = '')
    {
        $result = $this->table('orders')->field($fields)->where($condition)->group($group)->having($having)->order($order)->limit($limit)->select();
        return $result;
    }

    public function getOrderGroup($condition = array(), $fields, $pagesize = '', $group = '', $order = '', $limit = '')
    {
        return $this->table('orders')->field($fields)->where($condition)->group($group)->order($order)->page($pagesize)->limit($limit)->select();
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
    public function getOrderList($condition, $pagesize = '', $field = '*', $order = 'order_id desc', $limit = '', $extend = array(), $master = false)
    {
        $list = $this->table('orders')->field($field)->where($condition)->page($pagesize)->order($order)->limit($limit)->master($master)->select();
        if (empty($list)) return array();
//        $fenxiao_service = Service ( "Fenxiao" );
//        $fx_members = $fenxiao_service->getFenxiaoMembers ();
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
        if (in_array('order_common', $extend)) {
            $order_common_list = $this->getOrderCommonList(array('order_id' => array('in', array_keys($order_list))), '*', '', 999999);
            foreach ($order_common_list as $value) {
                $order_list[$value['order_id']]['extend_order_common'] = $value;
                $order_list[$value['order_id']]['extend_order_common']['reciver_info'] = @unserialize($value['reciver_info']);
                $order_list[$value['order_id']]['extend_order_common']['invoice_info'] = @unserialize($value['invoice_info']);
            }
        }
        //追加返回店铺信息
        if (in_array('store', $extend)) {
            $store_id_array = array();
            foreach ($order_list as $value) {
                if (!in_array($value['store_id'], $store_id_array)) $store_id_array[] = $value['store_id'];
            }
            $store_list = Model('store')->getStoreList(array('store_id' => array('in', $store_id_array)));
            $store_new_list = array();
            foreach ($store_list as $store) {
                $store_new_list[$store['store_id']] = $store;
            }
            foreach ($order_list as $order_id => $order) {
                $order_list[$order_id]['extend_store'] = $store_new_list[$order['store_id']];
            }
        }

        //追加返回买家信息
        if (in_array('member', $extend)) {
            foreach ($order_list as $order_id => $order) {
                $order_list[$order_id]['extend_member'] = Model('member')->getMemberInfoByID($order['buyer_id']);
            }
        }

        //追加返回商品信息
        if (in_array('order_goods', $extend)) {
            //取商品列表
            $order_goods_list = $this->getOrderGoodsList(array('order_id' => array('in', array_keys($order_list))), '*', 99999);
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
      * 打印配送单需要
      * */
    public function getOrder_distributionList($condition, $pagesize = '', $field = '*', $order = 'order_id desc', $limit = '', $extend = array(), $master = false)
    {
        return $this->table('orders')->field($field)->where($condition)->page($pagesize)->order($order)->limit($limit)->master($master)->select();
    }
    /**
     * 取得(买/卖家)订单某个数量缓存
     * @param string $type 买/卖家标志，允许传入 buyer、store
     * @param int $id 买家ID、店铺ID
     * @param string $key 允许传入  NewCount、PayCount、SendCount、EvalCount、TakesCount、PinCount，分别取相应数量缓存，只许传入一个
     * @return array
     */
    public function getOrderCountCache($type, $id, $key)
    {
        if (!C('cache_open')) return array();
        $type = 'ordercount' . $type;
        $ins = Cache::getInstance('redis');
        $order_info = $ins->hget($id, $type, $key);
        return !is_array($order_info) ? array($key => $order_info) : $order_info;
    }


    /**
     * 设置(买/卖家)订单某个数量缓存
     * @param string $type 买/卖家标志，允许传入 buyer、store
     * @param int $id 买家ID、店铺ID
     * @param array $data
     */
    public function editOrderCountCache($type, $id, $data)
    {
        if (!C('cache_open') || empty($type) || !intval($id) || !is_array($data)) return;
        $ins = Cache::getInstance('redis');
        $type = 'ordercount' . $type;
        $ins->hset($id, $type, $data);
    }

    /**
     * 判断店铺是否需要从缓存中读取数量统计
     * @param int $store_id 店铺ID
     * @return bool 不需要读缓存返回false,需要读返回true
     */
    private function checkReadCache($store_id)
    {
        $arr = array(80);
        if (in_array($store_id, $arr)) {
            return false;
        }

        return true;
    }

    /**
     * 取得买卖家订单数量某个缓存
     * @param string $type $type 买/卖家标志，允许传入 buyer、store
     * @param int $id 买家ID、店铺ID
     * @param string $key 允许传入  NewCount、PayCount、SendCount、EvalCount、TakesCount、PinCount、CompletedCount、WaitCount，分别取相应数量缓存，只许传入一个
     * @return int
     */
    public function getOrderCountByID($type, $id, $key)
    {
        $cache_info = $this->getOrderCountCache($type, $id, $key);

        if (is_string($cache_info[$key]) && ($type == 'store' && $this->checkReadCache($id))) {
            //从缓存中取得
            $count = $cache_info[$key];
        } else {
            //从数据库中取得
            $field = $type == 'buyer' ? 'buyer_id' : 'store_id';
            $condition = array($field => $id);
            $func = 'getOrderState' . $key;
            $count = $this->$func($condition);
            $this->editOrderCountCache($type, $id, array($key => $count));
        }
        return $count;
    }

    /**
     * 删除(买/卖家)订单全部数量缓存
     * @param string $type 买/卖家标志，允许传入 buyer、store
     * @param int $id 买家ID、店铺ID
     * @return bool
     */
    public function delOrderCountCache($type, $id)
    {
        if (!C('cache_open')) return true;
        $ins = Cache::getInstance('redis');
        $type = 'ordercount' . $type;
        return $ins->hdel($id, $type);
    }

    /**
     * 待付款订单数量
     * @param unknown $condition
     */
    public function getOrderStateNewCount($condition = array())
    {
        $condition['order_state'] = ORDER_STATE_NEW;
        $condition['chain_code'] = 0;
        return $this->getOrderCount($condition);
    }

    /**
     * 待发货订单数量
     * @param unknown $condition
     */
    public function getOrderStatePayCount($condition = array())
    {
        $condition['order_state'] = ORDER_STATE_PAY;
        $condition['lock_state'] = 0;
        return $this->getOrderCount($condition);
    }

    /**
     * 拼团中订单数量
     * @param unknown $condition
     */
    public function getOrderStatePinCount($condition = array())
    {
        $condition['order_state'] = ORDER_STATE_TUAN_PAY;
        $condition['chain_code'] = 0;
        return $this->getOrderCount($condition);
    }

    /**
     * 待收货订单数量
     * @param unknown $condition
     */
    public function getOrderStateSendCount($condition = array())
    {
        $condition['order_state'] = ORDER_STATE_SEND;
        return $this->getOrderCount($condition);
    }

    /**
     * 待评价订单数量
     * @param unknown $condition
     */
    public function getOrderStateEvalCount($condition = array())
    {
        $condition['order_state'] = ORDER_STATE_SUCCESS;
        $condition['evaluation_state'] = 0;
        return $this->getOrderCount($condition);
    }

    /**
     * 已完成订单数量
     * @param array $condition
     */
    public function getOrderStateCompletedCount($condition = array())
    {
        $condition['order_state'] = ORDER_STATE_SUCCESS;
        return $this->getOrderCount($condition);
    }

    /**
     * 待收货订单数量
     * @param array $condition
     */
    public function getOrderStateWaitCount($condition = array())
    {
        $condition['order_state'] = array('in', array(ORDER_STATE_PAY,ORDER_STATE_PREPARE,ORDER_STATE_SEND));
        $condition['refund_amount'] = array('gt', 0);
        return $this->getOrderCount($condition);
    }


    /**
     * 待自提订单数量
     * @param unknown $condition
     */
    public function getOrderStateTakesCount($condition = array())
    {
        $condition['order_state'] = array('in', array(ORDER_STATE_NEW, ORDER_STATE_PAY));
        $condition['chain_code'] = array('gt', 0);
        return $this->getOrderCount($condition);
    }

    /**
     * 交易中的订单数量
     * @param unknown $condition
     */
    public function getOrderStateTradeCount($condition = array())
    {
        $condition['order_state'] = array(array('neq', ORDER_STATE_CANCEL), array('neq', ORDER_STATE_SUCCESS), 'and');
        return $this->getOrderCount($condition);
    }

    /**
     * 取得订单数量
     * @param unknown $condition
     */
    public function getOrderCount($condition)
    {
        return $this->table('orders')->where($condition)->count();
    }

    /**
     * 取得订单总金额
     * @param $condition
     * @return mixed
     */
    public function getOrderAmount($condition)
    {
        return $this->table('orders')->where($condition)->sum('order_amount');
    }

    /**
     * 取得订单商品表详细信息
     * @param unknown $condition
     * @param string $fields
     * @param string $order
     */
    public function getOrderGoodsInfo($condition = array(), $fields = '*', $order = '')
    {
        return $this->table('order_goods')->where($condition)->field($fields)->order($order)->find();
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
     */
    public function getOrderGoodsList($condition = array(), $fields = '*', $limit = null, $page = null, $order = 'rec_id desc', $group = null, $key = null)
    {
        return $this->table('order_goods')->field($fields)->where($condition)->limit($limit)->order($order)->group($group)->key($key)->page($page)->select();
    }


    /**
     * 取得订单扩展表列表
     * @param unknown $condition
     * @param string $fields
     * @param string $limit
     */
    public function getOrderCommonList($condition = array(), $fields = '*', $order = '', $limit = null)
    {
        return $this->table('order_common')->field($fields)->where($condition)->order($order)->limit($limit)->select();
    }

    /**
     * 插入订单支付表信息
     * @param array $data
     * @return int 返回 insert_id
     */
    public function addOrderPay($data)
    {
        return $this->table('order_pay')->insert($data);
    }

    /**
     * 插入订单表信息
     * @param array $data
     * @return int 返回 insert_id
     */
    public function addOrder($data)
    {
        /*在9月1号之后，过滤掉人人优品，苏宁易购的15号荆门订单*/
        if (time() >= strtotime("2017-09-01")) {
            $condition['member_id'] = $data['buyer_id'];
            //$condition['filter_store_id'] = array(array('gt', 0), array('eq', $data['store_id']));
            $fenxiao = $this->table('member_fenxiao')->where($condition)->find();
            $data['filter_status'] = 0;
            if ($fenxiao['filter_store_id'] <= 0) {
                // 农商互联渠道全部结算
                $data['filter_status'] = 0;
            } else if ($fenxiao['filter_store_id'] != 15) {
                // 非荆门渠道全部过滤
                $data['filter_status'] = 1;
            } else if ($fenxiao['filter_store_id'] == $data['store_id']) {
                // 荆门渠道并且荆门订单全部过滤
                $data['filter_status'] = 1;
            }
        }

        $insert = $this->table('orders')->insert($data);
        if ($insert) {
            //更新缓存
            if (C('cache_open')) {
                QueueClient::push('delOrderCountCache', array('buyer_id' => $data['buyer_id'], 'store_id' => $data['store_id']));
            }
        }
        return $insert;
    }

    /**
     * 插入订单扩展表信息
     * @param array $data
     * @return int 返回 insert_id
     */
    public function addOrderCommon($data)
    {
        return $this->table('order_common')->insert($data);
    }

    /**
     * 插入订单扩展表信息
     * @param array $data
     * @return int 返回 insert_id
     */
    public function addOrderGoods($data)
    {
        return $this->table('order_goods')->insertAll($data);
    }

    public function addOrderGood($data)
    {
        return $this->table('order_goods')->insert($data);
    }

    /**
     * 添加订单日志
     */
    public function addOrderLog($data)
    {
        $data['log_role'] = str_replace(array('buyer', 'seller', 'system', 'admin', 'tuanzhang'), array('买家', '商家', '系统', '管理员', '团长'), $data['log_role']);
        $data['log_time'] = TIMESTAMP;
        return $this->table('order_log')->insert($data);
    }

    /**
     * 更改订单信息
     *
     * @param unknown_type $data
     * @param unknown_type $condition
     */
    public function editOrder($data, $condition, $limit = '')
    {
        $update = $this->table('orders')->where($condition)->limit($limit)->update($data);
        if ($update) {
            //更新缓存
            if (C('cache_open')) {
                QueueClient::push('delOrderCountCache', $condition);
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
    public function editOrderCommon($data, $condition)
    {
        return $this->table('order_common')->where($condition)->update($data);
    }

    /**
     * 更改订单信息
     *
     * @param unknown_type $data
     * @param unknown_type $condition
     */
    public function editOrderGoods($data, $condition)
    {
        return $this->table('order_goods')->where($condition)->update($data);
    }

    /**
     * 更改订单支付信息
     *
     * @param unknown_type $data
     * @param unknown_type $condition
     */
    public function editOrderPay($data, $condition)
    {
        return $this->table('order_pay')->where($condition)->update($data);
    }

    /**
     * 订单操作历史列表
     * @param unknown $order_id
     * @return Ambigous <multitype:, unknown>
     */
    public function getOrderLogList($condition, $order = '')
    {
        return $this->table('order_log')->where($condition)->order($order)->select();
    }

    /**
     * 取得单条订单操作记录
     * @param unknown $condition
     * @param string $order
     */
    public function getOrderLogInfo($condition = array(), $order = '')
    {
        return $this->table('order_log')->where($condition)->order($order)->find();
    }

    /**
     * 返回是否允许某些操作
     * @param unknown $operate
     * @param unknown $order_info
     */
    public function getOrderOperateState($operate, $order_info)
    {
        if (!is_array($order_info) || empty($order_info)) {
            return false;
        }

        if (isset($order_info['if_' . $operate])) {
            return $order_info['if_' . $operate];
        }

        switch ($operate) {

            //买家取消订单
            case 'buyer_cancel':
                $state = ($order_info['order_state'] == ORDER_STATE_NEW) ||
                    ($order_info['payment_code'] == 'offline' && $order_info['order_state'] == ORDER_STATE_PAY);
                break;

            //申请退款   社区团购配送中不允许退款
            case 'refund_cancel':
                $state = $order_info['refund'] == 1 && !intval($order_info['lock_state']) && ($order_info['shequ_tuan_id'] == 0 || ($order_info['shequ_tuan_id'] > 0 && $order_info['order_state'] != ORDER_STATE_SEND));
                break;

            //商家取消订单
            case 'store_cancel':
                $state = ($order_info['order_state'] == ORDER_STATE_NEW && $order_info['payment_code'] != 'chain') ||
                    ($order_info['payment_code'] == 'offline' &&
                        in_array($order_info['order_state'], array(ORDER_STATE_PAY, ORDER_STATE_SEND)));
                break;

            //平台取消订单
            case 'system_cancel':
                $state = ($order_info['order_state'] == ORDER_STATE_NEW) ||
                    ($order_info['payment_code'] == 'offline' && $order_info['order_state'] == ORDER_STATE_PAY);
                if ($order_info['order_from'] == '3' && $order_info['order_state'] == '20') {
                    $state = true;
                }
                break;

            //平台收款
            case 'system_receive_pay':
                $state = $order_info['order_state'] == ORDER_STATE_NEW;
                $state = $state && $order_info['payment_code'] == 'online' && $order_info['api_pay_time'] || $order_info['payment_code'] == 'jicai';
                break;

            //买家投诉
            case 'complain':
                $state = in_array($order_info['order_state'], array(ORDER_STATE_PAY, ORDER_STATE_SEND)) ||
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
                $state = !$order_info['lock_state'] && in_array($order_info['order_state'], array(ORDER_STATE_PAY, ORDER_STATE_PREPARE)) && !$order_info['chain_id'] && !in_array($order_info['is_printship'], array(1, 2));
                break;

            //电子面单
            case 'print_ship':
                $state = !$order_info['lock_state'] && in_array($order_info['order_state'], array(ORDER_STATE_PAY, ORDER_STATE_PREPARE)) && !$order_info['chain_id'] && in_array($order_info['is_printship'], array(0, 3));
                break;


            //收货
            case 'receive':
                $state = !$order_info['lock_state'] && $order_info['order_state'] == ORDER_STATE_SEND;
                break;

            //门店自提完成
            case 'chain_receive':
                $state = !$order_info['lock_state'] && in_array($order_info['order_state'], array(ORDER_STATE_NEW, ORDER_STATE_PAY)) &&
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
                $state = !empty($order_info['shipping_code']) && in_array($order_info['order_state'], array(ORDER_STATE_SEND, ORDER_STATE_SUCCESS));
                break;

            //放入回收站
            case 'delete':
                $state = in_array($order_info['order_state'], array(ORDER_STATE_CANCEL, ORDER_STATE_SUCCESS)) && $order_info['delete_state'] == 0;
                break;

            //永久删除、从回收站还原
            case 'drop':
            case 'restore':
                $state = in_array($order_info['order_state'], array(ORDER_STATE_CANCEL, ORDER_STATE_SUCCESS)) && $order_info['delete_state'] == 1;
                break;

            //分享
            case 'share':
                $state = true;
                break;
            //拼团分享
            case 'pin_share':
                $state = in_array($order_info['order_state'], array(ORDER_STATE_TUAN_PAY));
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
    public function getOrderAndOrderGoodsList($condition, $field = '*', $page = 0, $order = 'rec_id desc')
    {
        return $this->table('order_goods,orders')->join('inner')->on('order_goods.order_id=orders.order_id')->where($condition)->field($field)->page($page)->order($order)->select();
    }

    public function getOrderAndOrderGoodsCount($condition)
    {
        return $this->table('order_goods,orders')->join('inner')->on('order_goods.order_id=orders.order_id')->where($condition)->count();
    }

    public function getGoodsAllPriceJoin($condition)
    {
        return $this->table('order_goods,orders')->join('inner')->on('order_goods.order_id=orders.order_id')->where($condition)->field('goods_pay_price')->sum('goods_pay_price');
    }

    //获取商品累计成交价格
    public function getGoodsAllPrice($condition = array())
    {
        return $this->table('order_goods')->field('goods_pay_price')->where($condition)->sum('goods_pay_price');
    }

    /**
     * 订单销售记录 订单状态为20、30、40时
     * @param unknown $condition
     * @param string $field
     * @param number $page
     * @param string $order
     */
    public function getOrderAndOrderGoodsSalesRecordList($condition, $field = "*", $page = 0, $order = 'rec_id desc')
    {
        $condition['order_state'] = array('in', array(ORDER_STATE_PAY, ORDER_STATE_SEND, ORDER_STATE_SUCCESS));
        $ret = $this->getOrderAndOrderGoodsList($condition, $field, $page, $order);
        //分销渠道订单，过滤购买用户名显示
        foreach ($ret as $k => $val) {
            if ('3' == $val['order_from']) {
                $ret[$k]['buyer_name'] = '汉购' . $val['add_time'];
            }

            $ret[$k]['buyer_name'] = str_sub($ret[$k]['buyer_name'], 1, 0) . '***' . str_sub($ret[$k]['buyer_name'], 1, -1);
        }
        return $ret;
    }

    /**
     * 取得其它订单类型的信息
     * @param unknown $order_info
     */
    public function getOrderExtendInfo(&$order_info)
    {
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
    public function createFxOrder($param, $is_import = false)
    {
        $ret = array(); //返回参数
        $param = json_decode($param);
        if (empty($param->key) || $param->key != C('order_create_key')) {
            $ret = array('error' => 1001, 'msg' => 'invalid key');
            return $ret;
        }
        if (empty($param->provine) && empty($param->city) && empty($param->area)) {
            $addr = $this->_isInDeliverArea($param->address);
            $param->provine_id = $addr['provenceId'];
            $param->city_id = $addr['cityId'];
            $param->area_id = $addr['areaId'];
        } else {
            $provine = mb_substr($param->provine, 0, 2, 'utf-8');
            $city = mb_substr($param->city, 0, 2, 'utf-8');
            $p_where['area_name'] = array('like', '%' . $provine . '%');
            $p_where['area_deep'] = 1;
            $p_field = 'area_id';
            $provine_id = Model('area')->getAreaInfo($p_where, $p_field);
            $param->provine_id = $provine_id['area_id']; //省级ID
            $c_where['area_parent_id'] = $param->provine_id;
            $c_where['area_name'] = array('like', '%' . $city . '%');
            $c_where['area_deep'] = 2;
            $c_field = 'area_id';
            $city_id = Model('area')->getAreaInfo($c_where, $c_field);
            $param->city_id = $city_id['area_id'];//市级IDq
        }

        $param->order_time = $param->order_time ? $param->order_time : time();
        if (empty($param->order_sn) && $param->payment_code == 'fenxiao') {//集采订单不用分销订单号
            $ret = array('error' => 1001, 'msg' => '缺少订单编号', 'ordersn' => $param->order_sn);
            return $ret;
        }

        if (empty($param->receiver)) {
            $ret = array('error' => 1001, 'msg' => '缺少收货人信息', 'ordersn' => $param->order_sn);
            return $ret;
        }

        if (empty($param->provine_id)) {
            if ($is_import) {
                $ret = array('error' => 1001, 'msg' => '收货人省信息错误');
                return $ret;
            }
            $param->provine_id = 18;
        }

        if (empty($param->city_id)) {
            if ($is_import) {
                $ret = array('error' => 1001, 'msg' => '收货人市信息错误');
                return $ret;
            }
            $param->city_id = 258;
        }

        if (count($param->item) < 1) {
            $ret = array('error' => 1001, 'msg' => '订单商品详情缺失', 'ordersn' => $param->order_sn);
            return $ret;
        }
        $memberWhere['member_id'] = intval($param->buy_id);
        $memberWhere['member_type'] = array('in', array('fenxiao', 'jicai'));
        $buyer_info = Model('member')->getMemberInfo($memberWhere, 'member_id , member_name, member_email');
        if (empty($buyer_info['member_name'])) {
            $ret = array('error' => 1001, 'msg' => '分销商信息不存在', 'ordersn' => $param->order_sn);
            return $ret;
        }
        $param->member_name = $buyer_info['member_name'];
        $param->member_email = $buyer_info['member_email'];
        $goods_ids = array();
        $goods_num = array();
        $fxpids = array();
        foreach ($param->item as $k => $v) {
            $fxpids[] = $v->fxpid;
        }
        $b2cCategoryModel = Model('b2c_category');
        /** @var goodsModel $goodsModel */
        //$goodsModel = Model('goods');
        $fxGoodsList = $b2cCategoryModel->where(array('fxpid' => array('in', $fxpids), 'uid' => $param->buy_id))->select();
        $fxGoodsList = array_under_reset($fxGoodsList, 'fxpid');
        foreach ($param->item as $k => $v) {
            $goods_ids[] = intval($v->goods_id);
            $fxGoods = $fxGoodsList[$v->fxpid];
            $fxPrice = $this->getFenxiaoPrice($fxGoods, $param->order_time);
            $fxPrice = $fxPrice > 0 ? $fxPrice : $v->price;
            if (!$fxPrice) {
                // TODO 抓取商品表单价
                //$goods = $goodsModel->getGoodsInfo();
                $ret = array('error' => 1001, 'msg' => $param->order_sn . '分销商品价格为空' . json_encode($goods_ids));
                return $ret;
            }
            /*$fxPrice = $fxGoods['fxprice'];
            if($fxGoods['promotion_price']>0
                &&$fxGoods['promotion_start']<$param->order_time
                &&$fxGoods['promotion_end']>$param->order_time
            ){
                $fxPrice = $fxGoods['promotion_price'];
            }*/
            $goods_num[$v->goods_id] = array('num' => intval($v->num) * $fxGoods['multiple_goods'], 'price' => floatval($fxPrice), 'fx_cost' => $fxGoods['fxcost']);
        }
        $gwhere['goods_id'] = array('in', $goods_ids);
        $gWhere['goods_state'] = 1;//0下架，1正常，10违规（禁售）
        $goods_info = $this->table('goods')->field('goods_id,goods_name,store_id,store_name,goods_image,goods_price,gc_id,goods_cost,tax_input,tax_output,new_tax_input,new_tax_output')->where($gwhere)->select();
        if (count($goods_info) != count($goods_ids)) {
            $ret = array('error' => 1001, 'msg' => $param->order_sn . '商品编号匹配不成功' . json_encode($goods_ids));
            return $ret;
        }

        if (empty($param->mobile)) {
            $ret = array('error' => 1001, 'msg' => '缺少手机号码', 'ordersn' => $param->order_sn);
            return $ret;
        }

        if (empty($param->order_from) || !in_array($param->order_from, array('3', '4', '5'))) {  //3分销4集采5B2B
            $ret = array('error' => 1001, 'msg' => '订单来源信息有误', 'ordersn' => $param->order_sn);
            return $ret;
        }

        try {
            $model = Model('order');
            $model->beginTransaction();
            $order_no = Logic('buy_1')->gen_order_no();
            //订单支付表
            $pay['buyer_id'] = intval($param->buy_id);
            //$pay['api_pay_state'] = $param->payment_code=='fenxiao'?1:0;
            $pay['api_pay_state'] = $param->order_from == '3' ? 1 : 0;
            $pay['order_no'] = $order_no;
            $pay_sn = $this->_createFxPay($pay);

            //订单拆单
            $goods_item = array();
            $newTax = time() > strtotime('2018-05-01');
            foreach ($goods_info as $k => $v) {

                $goods_item[$v['store_id']]['store_name'] = $v['store_name'];
                $goods_item[$v['store_id']]['item'][] = array(
                    'goods_id' => $v['goods_id'],
                    'goods_name' => $v['goods_name'],
                    'num' => $goods_num[$v['goods_id']]['num'],
                    //'price'=>$goods_num[$v['goods_id']]['price'],
                    'fx_price' => $goods_num[$v['goods_id']]['price'],
                    'goods_price' => $v['goods_price'],
                    'gc_id' => intval($v['gc_id']),
                    'goods_image' => $v['goods_image'],
                    'goods_cost' => $goods_num[$v['goods_id']]['fx_cost'] > 0.0001 ? $goods_num[$v['goods_id']]['fx_cost'] : $v['goods_cost'],
                    'tax_input' => $newTax && $v['new_tax_input'] < 100 ? $v['new_tax_input'] : $v['tax_input'],
                    'tax_output' => $newTax && $v['new_tax_output'] < 100 ? $v['new_tax_output'] : $v['tax_output'],
                );
            }
            $this->_createFxOrderItem($goods_item, $param, $pay_sn, $order_no);
            if ($param->order_from == '3') {
                $this->_createFxorderIndex($param, $pay_sn);
                $this->_createFxorderSub($param, $pay_sn);
            }
            $model->commit();
            return array('error' => 1000, 'msg' => '订单创建成功', 'ordersn' => $param->order_sn);
        } catch (Exception $e) {
            $model->rollback();
            return array('error' => 1001, 'msg' => $e->getMessage(), 'ordersn' => $param->order_sn);
        }
    }

    //创建订单支付表
    private function _createFxPay(&$pay)
    {
        $pay_sn = Logic('buy_1')->makePaySn($pay['buyer_id'], $pay['order_no']);
        if ($pay_sn == false) {
            throw new Exception('支付码生成失败');
        }
        $pay['pay_sn'] = $pay_sn;
        $insert = $this->addOrderPay($pay);
        if ($insert == false) {
            throw new Exception('订单支付表插入失败');
        }
        return $pay_sn;
    }

    public function getOrderlistByPaysn($paysn_arr)
    {
        if (empty($paysn_arr)) return array();
        $conditions = array();
        $conditions['pay_sn'] = array('in', $paysn_arr);
        return $this->table('orders')->where($conditions)->select();
    }

    //获取退款单列表
    public function getRefundList($condition)
    {
        return $this->table('orders')->where($condition)->select();
    }

    /**
     * 计算渠道最终价格
     * @param $buyer_id
     * @param $goods_item
     * @param $old_orderamount
     * @return float|int
     * @deprecated 此功能取消
     */
    public function _getChannelPrice($buyer_id, $goods_item, $old_orderamount)
    {
        $member_fenxiao = Model('member_fenxiao');
        $fenxiao_data = $member_fenxiao->getMembeFenxiaoList(array('member_id' => intval($buyer_id)));
        if (!empty($fenxiao_data)) {
            if ($fenxiao_data[0]['type'] == 0) {
                /*共建：订单金额=b2c_category的price乘以数量*/
                foreach ($goods_item as $k => $v) {
                    foreach ($v['item'] as $key => $val) {
                        $category_data = $this->table('b2c_category')->field('fxprice')->where(array('uid' => $buyer_id, 'pid' => $val['goods_id']))->select();
                        if (!empty($category_data[0]['fxprice']) && $category_data[0]['fxprice'] > 0) {
                            $order_amount += $category_data[0]['fxprice'] * $val['num'];
                        }
                    }
                }
            } else {
                /*平台=订单金额-订单金额*member_fenxiao的佣金比例*/
                if ($fenxiao_data[0]['commission_rate'] > 0) {
                    $order_amount = $old_orderamount * (1 - $fenxiao_data[0]['commission_rate'] / 100);
                }
            }
        }
        return $order_amount;
    }


    /**
     * 订单明细表
     * @param array $goods_item
     * @param object $param
     * @param int $pay_sn
     */
    private function _createFxOrderItem($goods_item, $param, $pay_sn, $order_no)
    {
        //先统计店铺订单总额，方便计算店铺订单折扣
        $rpt = array();
        $hango_store_total = array();
        $store_order_total = $store_discount_total = array();
        $hango_total = 0; //按汉购商品销售价计算总值
        $totalAmount = 0;
        foreach ($goods_item as $k => $v) {
            $store_order_total[$k] = $store_discount_total[$k] = 0;
            $store_goods_total = 0;
            foreach ($v['item'] as $key => $val) {
                /** 在汉购网应该销售的店铺金额 */
                $hango_store_total[$k] += $val['num'] * $val['goods_price'];
                /** 分销平台销售的订单金额 */
                $store_order_total[$k] += $val['num'] * $val['fx_price'];
                /** 在汉购网应该销售的订单金额 */
                $hango_total += $val['num'] * $val['goods_price'];
                $store_goods_total += $val['num'] * $val['goods_price'];
            }
            $totalAmount += $store_order_total[$k];

            $store_discount_total[$k] = $store_goods_total - $store_order_total[$k];
        }

        //汉购销售价总计与分销订单支付额差额为红包值
        // TODO 若有渠道特殊优惠，按照渠道传过来的数据处理
        //$discount = $hango_total - $param->amount ;
        $discount = $hango_total - $totalAmount;
        if ($discount != 0) {
            /** @var buy_1Logic $buy1Logic */
            $buy1Logic = Logic('buy_1');
            $rpt = $buy1Logic->parseFxOrderRpt($store_order_total, $store_discount_total, $discount);
        }

        /*if( $param->discount > 0 ) {
            $rpt = Logic('buy_1')->parseOrderRpt($store_order_total, $param->discount);
        }*/

        $num = 0;
        //拆单
        foreach ($goods_item as $k => $v) {
            /** @var storeModel $storeModel */
            $storeModel = Model('store');
            $storeInfo = $storeModel->getStoreInfo(array('store_id' => $k));
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
            $mobile = preg_replace('/\D/', '', $param->mobile);
            $order['buyer_phone'] = $mobile;
            $order['add_time'] = $param->order_time;
            $order['payment_code'] = $param->payment_code;
            $order['order_amount'] = isset($rpt[0][$k]) ? $rpt[0][$k] : $store_order_total[$k];
            $order['goods_amount'] = $order['order_amount'];
            if ($param->order_from == '3') {
                $order['payment_time'] = $param->order_time ? $param->order_time : time();
                $order['order_state'] = 20;
                $order['import_time'] = $param->import_time ? $param->import_time : time();
            } else {
                $order['order_state'] = 10;
            }
            $order['shipping_fee'] = $param->shipping_fee;
            $order['order_from'] = $param->order_from ? $param->order_from : '3';
            $order['fx_order_id'] = $param->order_sn;
            //折扣信息保存(平台红包代替分销平台的折扣)
            $order['rpt_amount'] = isset($rpt[1][$k]) && $rpt[1][$k] ? $rpt[1][$k] : '0.00';
            $order['rpt_bill'] = $order['rpt_amount'];

            //设置为发货状态
            if ($param->is_ship == 1) {
                $order['order_state'] = 30;
//            	$order['make_send_time'] = $param->order_time+600;
            }

            /*分销渠道设置税率*/
            /*$final_price=$this->_getChannelPrice(intval($param->buy_id),$goods_item,$order['order_amount'] );
            if(!empty($final_price)){
                $order['order_amount']=$final_price;
            }*/
            $order_id = $this->addOrder($order);
            if ($order_id == false) {
                throw new Exception('订单信息表生成失败');
            }
            $orderCommon['order_id'] = $order_id;
            $orderCommon['store_id'] = $k;
            $orderCommon['order_message'] = $param->remark;
            $orderCommon['reciver_name'] = $param->receiver;
            $orderCommon['reciver_province_id'] = $param->provine_id;
            $orderCommon['reciver_city_id'] = $param->city_id;
            $receiveinfo['address'] = $param->provine . " " . $param->city . " " . $param->area . " " . $param->address;
            $receiveinfo['phone'] = $param->mobile;
            $receiveinfo['area'] = $param->provine . " " . $param->city . " " . $param->area;
            $receiveinfo['street'] = $param->address;
            $receiveinfo['mob_phone'] = $param->mobile;
            $receiveinfo['tel_phone'] = '';
            $receiveinfo['dlyp'] = '';
            $orderCommon['reciver_info'] = serialize($receiveinfo);
            if (isset($param->distribution_channel) && !empty($param->distribution_channel)) {
                $orderCommon['distribution_channel'] = $param->distribution_channel;
            }
            //折扣信息保存(平台红包代替分销平台的折扣)
            $promotion_info = array();
            $promotion_info[] = $order['rpt_amount'] > 0 ?
                array("平台红包", "使用{$order['rpt_amount']}元红包 编码：123456789987654321") :
                array("推广佣金", "使用" . abs($order['rpt_amount']) . "元分销渠道推广佣金");
            // 推广佣金：使用xx元分销渠道推广佣金
            //$orderCommon['promotion_info'] = $order['rpt_amount'] > '0' ? serialize($promotion_info) : "";
            // $orderCommon['promotion_total'] = $order['rpt_amount'] > '0' ? $order['rpt_amount'] : "0.00";
            $orderCommon['promotion_info'] = $order['rpt_amount'] != '0.00' ? serialize($promotion_info) : "";
            $orderCommon['promotion_total'] = $order['rpt_amount'];

            //设置为发货状态
            if ($param->is_ship == 1) {
                $orderCommon['shipping_time'] = $param->order_time + 600;
            }

            $ordercommonid = $this->addOrderCommon($orderCommon);
            if ($ordercommonid == false) {
                throw new Exception('订单信息附加表生成失败');
            }

            //order_goods表分拆红包，计算goods_pay_price
            $promotion_total = $order['rpt_amount'];
            //$promotion_rate = abs(number_format($promotion_total/$store_order_total[$k],5));
            $promotion_rate = number_format($promotion_total / $hango_store_total[$k], 5);
            if ($promotion_rate <= 1) {
                $promotion_rate = floatval(substr($promotion_rate, 0, 5));
            } else {
                $promotion_rate = 0;
            }
            $goodsCount = count($v['item']);

            $rptSum = $promotion_sum = 0;
            $i = 0;
            $order_goods = array();

            foreach ($v['item'] as $key => $val) {
                $order_goods[$i]['order_id'] = $order_id;
                $order_goods[$i]['goods_id'] = $val['goods_id'];
                $order_goods[$i]['goods_name'] = $val['goods_name'];
                //$order_goods[$i]['goods_price'] = $val['price'];
                $order_goods[$i]['goods_price'] = $val['goods_price'];
                $order_goods[$i]['goods_num'] = $val['num'];
                $order_goods[$i]['goods_cost'] = $val['goods_cost'] * $val['num'];
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
                $promotion_value = sprintf("%.2f", $goods_total * ($promotion_rate));
                //$order_goods[$i]['goods_pay_price'] = number_format(($goods_total - $promotion_value < 0 ? 0 : $goods_total - $promotion_value),2);
                $order_goods[$i]['goods_pay_price'] = sprintf("%.2f", ($goods_total - $promotion_value < 0 ? 0 : $goods_total - $promotion_value));
                $promotion_sum += $promotion_value;
                $order_goods[$i]['rpt_amount'] = $promotion_value;
                $order_goods[$i]['rpt_bill'] = $order_goods[$i]['rpt_amount'];

                $i++;
            }

            //将因舍出小数部分出现的差值补到最后一个商品的实际成交价中(商品goods_price=0时不给补，可能是赠品)
            //if ($promotion_total > $promotion_sum) {
            if (abs($promotion_total) > abs($promotion_sum)) {
                $i--;
                for ($i; $i >= 0; $i--) {
                    if (floatval($order_goods[$i]['goods_price']) != 0
                        && floatval($order_goods[$i]['goods_price']) > $promotion_total - $promotion_sum) {
                        $order_goods[$i]['goods_pay_price'] -= $promotion_total - $promotion_sum;
                        break;
                    }
                }
            }
            //order_goods表rpt_amount补余
            //if( $promotion_total>0 && $promotion_sum < $promotion_total ){
            if ($promotion_total != 0.00 && abs($promotion_sum) < abs($promotion_total)) {
                $order_goods[$goodsCount - 1]['rpt_amount'] += $promotion_total - $promotion_sum;
                $order_goods[$goodsCount - 1]['rpt_bill'] = $order_goods[$goodsCount - 1]['rpt_amount'];
            }
            /** @var store_bind_classModel $store_bind_class */
            $store_bind_class = Model('store_bind_class');
            $commis_rate_list = $store_bind_class->getStoreGcidCommisRateList($order_goods);
            foreach ($order_goods as $key => $value) {
                $order_goods[$key]['commis_rate'] = isset($commis_rate_list[$value['store_id']][$value['gc_id']]) ?
                    $commis_rate_list[$value['store_id']][$value['gc_id']] : 200;
                $order_goods[$key]['manage_type'] = $storeInfo['manage_type'];
                /** 补充平台商家订单商品成本 */
                if ($storeInfo['manage_type'] == 'platform') {
                    $order_goods[$key]['goods_cost'] =
                        number_format($value['goods_pay_price'] - $value['goods_pay_price'] * $order_goods[$key]['commis_rate'] / 100, 2);
                }
            }
            $insert = $this->addOrderGoods($order_goods);
            if (!$insert) {
                throw new Exception('订单保存失败[未生成商品数据]');
            }

            //保存日志表，用户crontab任务统计订单数据
            $this->_addFxorderLog($order_id);
        }
    }

    private function _createFxorderIndex($param, $pay_sn)
    {
        $mbof = Model('b2c_order_fenxiao');
        //拼多多订单使用update,其他订单使用insert
        if ($param->save_type == 'save') {
            $orderno = $param->order_sn;
            $bof['pay_sn'] = $pay_sn;
            $bof['is_ship'] = 0;
            $bof['order_time'] = $param->order_time;
            $filter = array('orderno' => $orderno);
            $res = $mbof->where($filter)->update($bof);
        } else {
            $bof['orderno'] = $param->order_sn;
            $bof['pay_sn'] = $pay_sn;
            $bof['is_ship'] = 0;
            $bof['order_time'] = $param->order_time;
            $bof['log_time'] = time();
            $bof['sourceid'] = $param->buy_id;
            $bof['source'] = $param->member_name;
            $mbof = model('b2c_order_fenxiao');
            $res = $mbof->insert($bof);
        }
        if (!$res) {
            throw new Exception('分销订单信息保存失败');
        }
    }

    private function _createFxorderSub($param, $pay_sn)
    {
        $items = objectToArray($param->item);
        foreach ($items as $_item) {
            if (!isset($_item['oid']) || !$_item['oid']) continue;
            $order_sub = array();
            $order_sub['orderno'] = $param->order_sn;
            $order_sub['oid'] = $_item['oid'];
            $order_sub['product_id'] = $_item['goods_id'];
            $order_sub['num'] = $_item['num'];
            $order_sub['pay_sn'] = $pay_sn;

            //如果分销订单原来存在，就先删除
            $condition = array();
            $condition['orderno'] = $param->order_sn;
            $isExis = Model("b2c_order_fenxiao_sub")->where($condition)->find();
            if ($isExis) {
                Model("b2c_order_fenxiao_sub")->where($condition)->delete();
            }
            $res = Model("b2c_order_fenxiao_sub")->insert($order_sub);
            if (!$res) {
                throw new Exception('分销子订单数据插入失败' . json_encode($order_sub));
            }
        }
    }

    private function _addFxorderLog($order_id)
    {
        $data = array();
        $data['order_id'] = $order_id;
        $data['log_role'] = 'system';
        $data['log_msg'] = '创建了分销订单';
        $data['log_user'] = '系统';
        $data['log_orderstate'] = '20';
        $res = $this->addOrderLog($data);
        if (!$res) {
            throw new Exception('订单日志保存失败');
        }
    }

    /***
     * 发货状态查询
     * @param $fx_order_id  分销id $buyer_name 渠道名称
     * @return bool
     * @author lcy
     */
    public function checkSendStatus($fx_order_id, $buyer_name)
    {
        $condition['order_state'] = ORDER_STATE_SEND;
        $condition['fx_order_id'] = $fx_order_id;
        $condition['buyer_name'] = $buyer_name;
        $res = $this->table('orders')->where($condition)->find();
        if (!$res) return false;
        return true;
    }

    /***
     * 分销平台发货订单推送
     * @author ljq
     */
    public function setOrderSend($order_info, $shipping_express_id, $shipping_code)
    {
        $limit_fenxiao = C("distribution_channel");
        //array('pinduoduo','youzan','renrendian','juanpi','mengdian','fanli','beibeiwang','renrenyoupin');
        if (!in_array($order_info['buyer_name'], $limit_fenxiao)) {
            return false;
        }
        $memberid = intval($order_info['buyer_id']);
        $fxOrderSn = $order_info['fx_order_id'];
        $express_list = Model('express')->getExpressList();
        //die($fxOrderSn."\n".$memberid."\n".$shipping_express_id."\n".$shipping_code);
        $fenxiao_service = Service("Fenxiao");
        $fx_members = $fenxiao_service->getFenxiaoMembers();
        if (empty($fxOrderSn) || !array_key_exists($memberid, $fx_members) || empty($shipping_express_id) || empty($shipping_code)) {
            return false;
        }
        //拼多多只发送一次
        $fx_members_flip = array_flip($fx_members);
        $pdd_member_id = $fx_members_flip['pinduoduo'];
        if ($memberid == $pdd_member_id) {
            $data = array();
            $data['source'] = $order_info['buyer_name'];
            $data['sourceid'] = $memberid;
            $data['orderno'] = $fxOrderSn;
            $data['logi_no'] = $shipping_code;
            $data['logi_name'] = $express_list[$shipping_express_id]['e_name'];
            $data['num'] = $order_info['extend_order_goods'][0]['goods_num'];
            $data['full_ship'] = 1;
            $data['oid'] = '';
            $res = $this->_sendApiRequest($data);
            if ($res) {
                return callback(true, '分销发货订单推送成功');
            } else {
                return callback(false, '分销发货订单推送失败');
            }
        }
        $gids = array();  //获取发货的商品编号信息
        foreach ($order_info['extend_order_goods'] as $k => $v) {
            $gids[] = $v['goods_id'];
        }
        $result = $this->_getFxOrderItem($fxOrderSn, $gids);
        //var_dump($result);
        if (count($result) > 0) {
            if (count($result) == 1) {
                foreach ($result as $k => $v) {
                    $data = array();
                    $data['source'] = $order_info['buyer_name'];
                    $data['sourceid'] = $memberid;
                    $data['orderno'] = $fxOrderSn;
                    $data['logi_no'] = $shipping_code;
                    $data['logi_name'] = $express_list[$shipping_express_id]['e_name'];
                    $data['num'] = $v['num'];
                    $data['full_ship'] = 0;
                    $data['oid'] = $v['oid'];
                    $res = $this->_sendApiRequest($data);
                    if ($res) {
                        return callback(true, '分销发货订单推送成功');
                    } else {
                        return callback(false, '分销发货订单推送失败');
                    }
                }
            } else {
                foreach ($result as $k => $v) {
                    $data = array();
                    $data['source'] = $order_info['buyer_name'];
                    $data['sourceid'] = $memberid;
                    $data['orderno'] = $fxOrderSn;
                    $data['logi_no'] = $shipping_code;
                    $data['logi_name'] = $express_list[$shipping_express_id]['e_name'];
                    $data['num'] = $v['num'];
                    $data['full_ship'] = 0;
                    $data['oid'] = $v['oid'];
                    $res = $this->_sendApiRequest($data);
                }
            }
        }
        return callback(true, '分销发货订单推送成功');
    }

    /**
     * 批量发货
     * @param string $csvFilePath 文件完整路径
     * @return bool
     * 文件格式
     * |-------------------------------------------------------------
     * | order_id           |logi_name      |logi_no     remark(备注)
     * |-------------------------------------------------------------
     * |150419164925478002  |EMS            |789654123    123456
     * |-------------------------------------------------------------
     * |150419164925478002  |中通快递        |789654123    123456
     * @author wx
     */
    public function bulkShipment($csvFilePath)
    {
        if (!is_file($csvFilePath)) {
            return callback(false, '文件不存在');
        }
        ini_set('memory_limit', '4G');
        $data = $this->_excelToArray($csvFilePath);
        if (!count($data) > 1) {
            return callback(false, '订单数据为空，无法进行导入');
        }
        /**
         * 获取快递公司
         */
        $express_list = Model('express')->getExpressList();
        $expressIds = Model('store_extend')->where(array('store_id' => $_SESSION['store_id']))->field('express')->find();
        $express = explode(',', $expressIds['express']);
        foreach ($express as $k => $v) {
            $express[$v] = $express_list[$v];
            unset($express[$k]);
        }

        /**
         * 比较格式
         */
        $arrTpl = array('0' => 'order_id', '1' => 'logi_name', '2' => 'logi_no', '3' => 'remark');//数组模板
        if ($arrTpl != $data[0]) {
            return array('state' => false, 'msg' => '文件格式错误');
        }
        array_shift($data);
        //unset($data[0]);
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
        $daddress = $model_daddress->getAddressInfo(array('store_id' => $_SESSION['store_id'], 'is_default' => '1'), 'address_id');
        $succNum = 0;   //成功条数
        $failNum = 0;   //失败条数
        $failOrderids = array();
        //update by ljq 优化发货速度
        $sn = array();
        $order_list = array();
        $res = array();
        foreach ($data as $k => $v) {
            $v[0] = preg_replace('/\D+/', '', $v[0]);
            if (empty($v[0])) continue;
            $sn[] = $v[0];
            $v[0] .= "\t";
            $res[] = $v;
        }

        if (count($sn) > 0) {
            $condition['order_sn'] = array('in', $sn);
            $resOrder = $this->getOrderList($condition, '', '', '', false, array('order_common', 'order_goods'));
            foreach ($resOrder as $k => $v) {
                $order_list[$v['order_sn']] = $v;
            }
        }
        //优化结束
        foreach ($res as $k => $v) {
            $sn = preg_replace('/\D+/', '', $v[0]);
            if (empty($v)) continue;
            $condition['order_sn'] = $sn;
            if (!preg_match("/^[0-9a-zA-Z,]+$/i", trim($v[2]))) {
                $failNum++;
                $failOrderids[] = $sn;
//                $errorMsg[] = $sn . " 运单号({$v[2]})只能字母、数字以及'，'的组合！";
                $res[$k]['feedback'] = '运单号不合法';
                continue;
            }
            if (empty($order_list[$sn]['order_id'])) {
                $failNum++;
                $failOrderids[] = $sn;
//                $errorMsg[] = $sn . ' 订单不存在！';
                $res[$k]['feedback'] = '订单号不存在';
                continue;
            }
            /**
             * 判断订单状态
             */
            $feedback_array = array('10' => "未支付", '30' => "已发货", '40' => '已收货');
            if ($order_list[$sn]['lock_state'] > 0) {
                $failNum++;
                $failOrderids[] = $sn;
                $res[$k]['feedback'] = '订单处于退款状态等待处理状态，不能发起发货;';
                continue;
            }

            if ($order_list[$sn]['order_state'] == 0) {
                $failNum++;
                $failOrderids[] = $sn;
                $res[$k]['feedback'] = "订单状态为：已取消的状态，不能发起发货;";
                continue;
            }

            if (in_array($order_list[$sn]['order_state'], array(ORDER_STATE_NEW, ORDER_STATE_SEND, ORDER_STATE_SUCCESS))) {
                $failNum++;
                $failOrderids[] = $sn;
                $res[$k]['feedback'] = "订单状态为：" . $feedback_array[$order_list[$sn]['order_state']] . "，不能发起发货;";
                continue;
            }

            $post['shipping_express_id'] = 0;
            foreach ($express as $ek => $ev) {
                if ($ev['e_name'] == $v[1]) {
                    $post['shipping_express_id'] = $ev['id'];//快递公司ID
                }
            }

            if ($post['shipping_express_id'] == 0) {
                $failNum++;
                $failOrderids[] = $sn;
//                $errorMsg[] = $sn . ' 发货失败，系统不存在快递公司:' . $v[1];
                $res[$k]['feedback'] = '发货失败，系统不存在快递公司';

                continue;
            }
            $post['shipping_code'] = $v[2];    //运单号
            $post['daddress_id'] = $daddress['address_id'];
            $result = $logic_order->changeOrderSend($order_list[$sn], 'seller', $_SESSION['seller_name'], $post);
            if ($result['state'] == true) {
                $succNum++;
                $res[$k]['feedback'] = "发货成功；";
                /*添加发货备注*/
                if (empty($res[$k][3])) {
                    $res[$k]['feeback'] .= "【发货备注为空，未更改】";
                } else {
                    $where['order_id'] = $order_list[$sn]['order_id'];
                    $savedata['deliver_explain'] = $data[$k][3];
                    $res = Model('order_common')->where($where)->update($savedata);
                    if (!$res) {
                        $res[$k]['feeback'] .= "【发货备注失败】";
                    }
                }

            } else {
                $failNum++;
            }
            $result['state'] == false and $failOrderids[] = $sn;
//            $result['state'] == false and $errorMsg[] = $sn . ' 发货记录修改失败';
        }
        $ret = array('totals' => count($res), 'succNum' => $succNum, 'failNum' => $failNum, 'state' => true, 'failOrderids' => $failOrderids, 'data' => $res);
        return $ret;
    }

    /**
     * 社区拼团订单导入excel批量发货
     */
    public function shequbulkShipment($csvFilePath,$admin_name)
    {
        if (!is_file($csvFilePath)) {
            callback(false, '文件不存在');
        }
         ini_set('memory_limit','1G');
        $data = $this->_excelToArray($csvFilePath);
        if (!count($data) > 1) {
            return callback(false, '订单数据为空');
        }

        $logic_order = Logic('order');

        /*********   快递公司    *********/
        $express_list = $this->table('express')->field('e_name,id')->limit(100)->select();
//        $express_name = array_column($express_list, 'e_name');

        /**
         * 比较格式
         */
        $arrTpl = array('0' => 'order_id', '1' => 'logi_name', '2' => 'logi_no', '3' => 'remark');//数组模板
        if ($arrTpl != $data[0]) {
            return array('state' => false, 'msg' => '文件格式错误');
        }
        array_shift($data);

        $succNum = 0;   //成功条数
        $failNum = 0;   //失败条数
        $failOrderids = array();
        $sn = array();
        $order_list = array();
        $res = array();
        foreach ($data as $k => $v) {
            $v[0] = preg_replace('/\D+/', '', $v[0]);
            if (empty($v[0])) continue;
            $sn[] = $v[0];
            $v[0] .= "\t";
            $res[] = $v;
        }
        if (count($sn) > 0) {
            $condition['order_sn'] = array('in', $sn);
            $resOrder = $this->getOrderList($condition, '', '', '', false, array('order_common', 'order_goods'));
//            foreach ($resOrder as $k => $v) {
//                $order_list[$v['order_sn']] = $v;
//            }
            $order_list = array_under_reset($resOrder,'order_sn');
        }
        //优化结束
        foreach ($res as $k => $v) {
            $sn  = preg_replace('/\D+/','',$v[0]);
            if(empty($v)) continue;
            $condition['order_sn'] = $sn;
            if (!preg_match("/^[0-9a-zA-Z,]+$/i", trim($v[2]))) {
                $failNum++;
                $failOrderids[] = $sn;
//                $errorMsg[] = $sn . " 运单号({$v[2]})只能字母、数字以及'，'的组合！";
                $res[$k]['feedback'] = '运单号不合法';
                continue;
            }
            if (empty($order_list[$sn]['order_id'])) {
                $failNum++;
                $failOrderids[] = $sn;
//                $errorMsg[] = $sn . ' 订单不存在！';
                $res[$k]['feedback'] = '订单号不存在';
                continue;
            }
            /**
             * 判断订单状态
             */
            $feedback_array = array('10' => "未支付", '30' => "已发货", '40' => '已收货');
            if ($order_list[$sn]['lock_state'] > 0) {
                $failNum++;
                $failOrderids[] = $sn;
                $res[$k]['feedback'] = '订单处于退款状态等待处理状态，不能发起发货;';
                continue;
            }
            if ($order_list[$sn]['order_state'] == 0) {
                $failNum++;
                $failOrderids[] = $sn;
                $res[$k]['feedback'] = "订单状态为：已取消的状态，不能发起发货;";
                continue;
            }
            if (in_array($order_list[$sn]['order_state'], array(ORDER_STATE_NEW, ORDER_STATE_SEND, ORDER_STATE_SUCCESS))) {
                $failNum++;
                $failOrderids[] = $sn;
                $res[$k]['feedback'] = "订单状态为：" . $feedback_array[$order_list[$sn]['order_state']] . "，不能发起发货;";
                continue;
            }
            $post['shipping_express_id'] = 0;
            foreach ($express_list as $ek => $ev) {
                if ($ev['e_name'] == $v[1]) {
                    $post['shipping_express_id'] = $ev['id'];//快递公司ID
                }
            }
            if ($post['shipping_express_id'] == 0) {
                $failNum++;
                $failOrderids[] = $sn;
//                $errorMsg[] = $sn . ' 发货失败，系统不存在快递公司:' . $v[1];
                $res[$k]['feedback'] = '发货失败，系统不存在快递公司';
                continue;
            }
            $post['shipping_code'] = $v[2];    //运单号
            $post['daddress_id'] = 0;
            $result = $logic_order->changeOrderSend($order_list[$sn], 'admin', $admin_name, $post);
            if ($result['state'] == true) {
                $succNum++;
                $res[$k]['feedback'] = "发货成功；";
                /*添加发货备注*/
                if (empty($res[$k][3])) {
                    $res[$k]['feeback'] .= "【发货备注为空，未更改】";
                } else {
                    $where['order_id'] = $order_list[$sn]['order_id'];
                    $savedata['deliver_explain'] = $data[$k][3];
                    $res = Model('order_common')->where($where)->update($savedata);
                    if (!$res) {
                        $res[$k]['feeback'] .= "【发货备注失败】";
                    }
                }

            } else {
                $failNum++;
            }
            $result['state'] == false and $failOrderids[] = $sn;
        }
        $ret = array('totals' => count($res), 'succNum' => $succNum, 'failNum' => $failNum, 'state' => true, 'failOrderids' => $failOrderids, 'data' => $res);
        return $ret;

    }

    /**
     * 批量发货
     * @param $csvFilePath
     * @return array
     */
    public function shipMoreByFxorderId($csvFilePath)
    {
        if (!is_file($csvFilePath)) {
            return callback(false, '文件不存在');
        }
        $data = $this->_excelToArray($csvFilePath);
        if (!count($data) > 1) {
            return callback(false, '订单数据有误');
        }
        /**
         * 获取快递公司
         */
        $express_list = Model('express')->getExpressList();
        $expressIds = Model('store_extend')->where(array('store_id' => $_SESSION['store_id']))->field('express')->find();
        $express = explode(',', $expressIds['express']);
        foreach ($express as $k => $v) {
            $express[$v] = $express_list[$v];
            unset($express[$k]);
        }
        /**
         * 比较格式
         */
        $arrTpl = array('0' => '订单号', '1' => '渠道', '2' => '快递公司', '3' => '快递单号', '4' => '备注');//数组模板
        if (count(array_diff($arrTpl, $data[0])) > 0) {        //如果跟数组模板不同
            return array('state' => 'false', 'msg' => '文件格式错误');
        }
        unset($data[0]);
        $logic_order = Logic('order');

        /**
         * 默认发货地址
         */
        $model_daddress = Model('daddress');
        $daddress = $model_daddress->getAddressInfo(array('store_id' => $_SESSION['store_id'], 'is_default' => '1'), 'address_id');
        $succNum = 0;   //成功条数
        $failNum = 0;   //失败条数
        $failOrderids = $errorMsg = array();
        //获取分销渠道
        $member_fenxiao = Model('member_fenxiao')->getMemberFenxiao();
        $member_fenxiao = array_under_reset($member_fenxiao, 'member_cn_code');

        foreach ($data as $k => $v) {
            if (!preg_match("/^[0-9a-zA-Z,]+$/i", trim($v[3]))) {
                $failNum++;
                $failOrderids[] = $v[0];
                $errorMsg[] = $v[0] . " 运单号({$v[1]})只能字母、数字以及'，'的组合！";
                $data[$k]['feedback'] .= '运单号不合法；';
                continue;
            }
            if (empty($member_fenxiao[$v[1]])) {
                $failNum++;
                $failOrderids[] = $v[0];
                $errorMsg[] = $v[0] . " 渠道({$v[1]})不存在！";
                $data[$k]['feedback'] .= "渠道不存在；";
                continue;
            }

            /**
             * order_info查询条件
             */
            $condition = array();
            $condition['store_id'] = $_SESSION['store_id'];
            $condition['buyer_id'] = intval($member_fenxiao[$v[1]]['member_id']);
            $condition['fx_order_id'] = $v[0];
            //var_dump($condition);die();
            $order_list = $this->getOrderInfo($condition, array('order_common', 'order_goods'));
            if (empty($order_list)) {
                $failNum++;
                $failOrderids[] = $v[0];
                $errorMsg[] = $v[0] . " 分销订单号不存在！";
                $data[$k]['feedback'] .= "分销订单号不存在；";
                continue;
            }
            /**
             * 判断订单状态
             */
            if ($order_list['lock_state'] == 1 || in_array($order_list['order_state'], array(ORDER_STATE_NEW, ORDER_STATE_SEND, ORDER_STATE_SUCCESS))) {
                $failNum++;
                $failOrderids[] = $v[0];
                $errorMsg[] = $v[0] . ' 分销该订单已经发货了，不能重新发货！';
                $data[$k]['feedback'] .= "此订单已经发货，不能重复发货；";
                continue;
            }
            $post['shipping_express_id'] = 0;
            foreach ($express as $ek => $ev) {
                if ($ev['e_name'] == $v[2]) {
                    $post['shipping_express_id'] = $ev['id'];//快递公司ID
                }
            }

            if ($post['shipping_express_id'] == 0) {
                $failNum++;
                $failOrderids[] = $v[0];
                $errorMsg[] = $v[0] . ' 发货失败，系统不存在快递公司:' . $v[2];
                $data[$k]['feedback'] .= "发货失败，系统不存在快递公司；";
                continue;
            }
            $post['shipping_code'] = $v[3];    //运单号
            $post['daddress_id'] = $daddress['address_id'];
            $post['deliver_explain'] = $v[4]; //发货备注
            $result = $logic_order->changeOrderSend($order_list, 'seller', $_SESSION['seller_name'], $post);
            if ($result['state'] == true) {
                $succNum++;
                $data[$k]['feedback'] = "订单发货成功；";
            } else {
                $failNum++;
            };
            $result['state'] == false and $failOrderids[] = $v[0];
            $result['state'] == false and $errorMsg[] = $v[0] . ' 发货记录修改失败';
        }
        $ret = array('totals' => count($data), 'succNum' => $succNum, 'failNum' => $failNum, 'failOrderids' => $failOrderids, 'errorMsg' => $errorMsg, 'result' => $data);
        return $ret;
    }

    /**
     * csv、Excel转数组
     * @param string $filePath 文件路径
     * @param int $sheet 第几个sheet 从0开始
     * @return array|bool
     * @author wx
     */
    private function _excelToArray($filePath = '', $sheet = 0)
    {
        if (empty($filePath) or !file_exists($filePath)) {
            return false;
        }
        $fileType = explode('.', $filePath);
        $fileType = $fileType[count($fileType) - 1];

        //csv类型直接str_getcsv转换
        if ($fileType == 'csv') {
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
                $data[$rowIndex - 1][] = $cell;
            }
        }
        return $data;
    }

    /***
     * 获取分销订单子订单
     *
     */
    private function _getFxOrderItem($fxOrderSn, $gids)
    {
        if (empty($fxOrderSn)) exit();
        $subModel = Model('b2c_order_fenxiao_sub');
        $sWhere['orderno'] = $fxOrderSn;
        count($gids) > 0 and $sWhere['product_id'] = array('in', $gids);
        $result = $subModel->field('oid,num')->where($sWhere)->select();
        return $result;
    }

    private function _sendApiRequest($data)
    {
        $service = Service("Fenxiao");
        $fx_members = $service->getFenxiaoMembers();
        $fx_members = array_values($fx_members);
        if (in_array($data['source'], $fx_members)) {
            $service->init($data['source']);

            $data['logi_no'] = preg_replace('/\s/', '', $data['logi_no']);
            $res = $service->pushiship($data);
            if (empty($res)) {
                return false;
            }
            $res = json_decode($res);
            if ($res->succ == 0) {
                //发送错误日志
                $arr = array();
                $arr['orderno'] = $data['orderno'];
                $arr['error'] = $res->msg ? $res->msg : '暂无';
                $arr['log_time'] = time();
                $arr['sourceid'] = $data['sourceid'];
                $arr['source'] = $data['source'];
                $arr['log_type'] = 'ship';
                $this->_addSendErrorLog($arr);
                return false;
            } else if ($res->succ == 1) {
                return true;
            } else {
                return false;
            }
        }
    }

    private function _addSendErrorLog($data)
    {
        $error_model = Model('b2c_order_fenxiao_error');
        $error_model->insert($data);
    }

    public function cancelFenxiaoOrder($orderno)
    {
        $condition = array();
        $condition['orderno'] = $orderno;

        $res1 = Model('b2c_order_fenxiao')->where($condition)->find();
        if ($res1) {
            Model('b2c_order_fenxiao')->where($condition)->delete();
        }
        $res2 = Model('b2c_order_fenxiao_sub')->where($condition)->find();
        if ($res2) {
            Model('b2c_order_fenxiao')->where($condition)->delete();
        }
    }

    public function delayCheck($order = array())
    {
        $now_time = TIMESTAMP;
        $delay_hour = ($now_time - $order['payment_time']) / 3600;
        if ($order['order_state'] == ORDER_STATE_PAY) {
            if ($delay_hour >= 12 && $delay_hour <= 24) {
                return '订单已超过12小时未发货，请尽快发货。';
            } elseif ($delay_hour >= 24) {
                return '订单已超过24小时未发货，请尽快发货。';
            }
        }
        if (in_array($order['order_state'], array(ORDER_STATE_SEND, ORDER_STATE_SUCCESS))) {
            if ($order['shipping_time']) {
                $hours = ($order['shipping_time'] - $order['payment_time']) / 3600;
                if ($hours >= 24) {
                    return '已延时发货';
                }
            }
        }

        return null;
    }


    protected function getFenxiaoPrice($fxGoods, $order_time)
    {
        $fxPrice = $fxGoods['fxprice'];
        $promotionModel = Model('b2c_promotion');
        $promotion = $promotionModel->where(array(
            'uid' => $fxGoods['uid'],
            'fx_pid' => $fxGoods['fxpid'],
            'start_at' => array('lt', $order_time),
            'end_at' => array('gt', $order_time),
        ))->order('price ASC')->find();
        if (!empty($promotion)) {
            $fxPrice = $promotion['price'];
        }
        return $fxPrice;
    }

    /**
     * 批量发货
     * @param string $csvFilePath 文件完整路径
     * @return bool
     * 文件格式
     * |-------------------------------------------------------------
     * | order_id           |logi_name      |logi_no     remark(备注)
     * |-------------------------------------------------------------
     * |150419164925478002  |EMS            |789654123    123456
     * |-------------------------------------------------------------
     * |150419164925478002  |中通快递        |789654123    123456
     * @author wx
     */
    public function batchShipRemark($csvFilePath)
    {
        if (!is_file($csvFilePath)) {
            return callback(false, '文件不存在');
        }
        $data = $this->_excelToArraybyname($csvFilePath);
        if (!count($data) > 1) {
            return callback(false, '订单数据为空，无法进行导入');
        }
        $arrTpl = array('0' => 'order_id', '1' => 'remark');//数组模板
        if ($arrTpl != array_values($data[0])) {
            return array('state' => false, 'msg' => '文件格式错误');
        }
        array_shift($data);
        $succNum = 0;   //成功条数
        $failNum = 0;   //失败条数
        foreach ($data as $key => $value) {
            $value['order_id'] = trim($value['order_id']);
            $data[$key] = $value;
        }
        //查询已发货的订单
        $order_ids = implode(',', array_column($data, 'order_id'));
        $condition['order_sn'] = array('in', $order_ids);
        $condition['order_state'] = array('gt', 20);
        $orderList = Model('orders')->field('order_id,order_sn,store_id,order_state')->where($condition)->limit(false)->select();
        /*实际订单量*/
        $order_num = count($orderList);
        $orderList = array_under_reset($orderList, 'order_sn');
        if (count($orderList) == 0) {
            $ret = array('totals' => count($data), 'actualNum' => $order_num, 'succNum' => $succNum, 'failNum' => $failNum, 'state' => true, 'data' => $data);
            return $ret;
        }
        foreach ($data as $k => $v) {
            if (strpos($v['order_id'], "E+") || strpos($v['order_id'], "e+")) {
                $failNum += 1;
                $data[$k]['status'] = "order_id的格式错误";
                continue;
            }
            if (!$orderList[$v['order_id']]) {
                $failNum += 1;
                $data[$k]['status'] = "此订单非发货状态";
                continue;
            }
            if ($orderList[$v['order_id']]['store_id'] != $_SESSION['store_id']) {
                $failNum += 1;
                $data[$k]['status'] = "此订单不属于【" . $_SESSION['store_name'] . "】此店铺";
                continue;
            }
            $where['order_id'] = $orderList[$v['order_id']]['order_id'];
            $savedata['deliver_explain'] = $data[$k]['remark'];
            $res = Model('order_common')->where($where)->update($savedata);
            if (!$res) {
                $failNum += 1;
                $data[$k]['status'] = '备注失败';
                continue;
            }
            $data[$k]['status'] = '备注成功';
            $succNum += 1;
        }
        $ret = array('totals' => count($data), 'actualNum' => $order_num, 'succNum' => $succNum, 'failNum' => $failNum, 'state' => true, 'data' => $data);
        return $ret;
    }

    /**
     * csv、Excel转数组
     * @param string $filePath 文件路径
     * @param int $sheet 第几个sheet 从0开始
     * @return array|bool
     * @author wx
     */
    private function _excelToArraybyname($filePath = '', $sheet = 0)
    {
        if (empty($filePath) or !file_exists($filePath)) {
            return false;
        }
        $fileType = explode('.', $filePath);
        $fileType = $fileType[count($fileType) - 1];

        //csv类型直接str_getcsv转换
        if ($fileType == 'csv') {
            $lines = array_map('str_getcsv', file($filePath));;
            $result = array();
            for ($i = 0; $i < count($lines); $i++) {        //循环读取每行内容注意行从第1行开始($i=0)
                $obj = $lines[$i];
                foreach ($obj as $k => $v) {
                    $result[$i][$lines[0][$k]] = mb_convert_encoding($v, 'UTF-8', 'gbk');
                    if (strpos($result[$i][$lines[0][$k]], "\t")) {
                        $result[$i][$lines[0][$k]] = str_replace("\t", '', $result[$i][$lines[0][$k]]);
                    }
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
                $key_type = $colIndex . "1";
                $key_name = $currentSheet->getCell($key_type)->getValue();
                $data[$rowIndex - 1][$key_name] = $cell;
                if (strpos($data[$rowIndex - 1][$key_name], "\t")) {
                    $data[$rowIndex - 1][$key_name] = str_replace("\t", '', $data[$rowIndex - 1][$key_name]);
                }

            }
        }
        return $data;
    }

    public function batchShipFenxiaoRemark($csvFilePath)
    {
        if (!is_file($csvFilePath)) {
            return callback(false, '文件不存在');
        }
        $data = $this->_excelToArraybyname($csvFilePath);
        if (!count($data) > 1) {
            return callback(false, '订单数据为空，无法进行导入');
        }
        $arrTpl = array('0' => 'fx_order_id', '1' => 'remark');//数组模板
        if ($arrTpl != array_values($data[0])) {
            return array('state' => false, 'msg' => '文件格式错误');
        }
        array_shift($data);
        $succNum = 0;   //成功条数
        $failNum = 0;   //失败条数
        //查询已发货的订单
        $order_ids = implode(',', array_column($data, 'fx_order_id'));
        $condition['fx_order_id'] = array('in', $order_ids);
        $condition['order_state'] = array('gt', 20);
        $orderList = Model('orders')->field('fx_order_id,order_id,order_sn,store_id,order_state')->where($condition)->limit(false)->select();
        /*实际订单量*/
        $order_num = count($orderList);
        $data = array_under_reset($data, 'fx_order_id');
        if (count($orderList) == 0) {
            $ret = array('totals' => count($data), 'actualNum' => $order_num, 'succNum' => $succNum, 'failNum' => $failNum, 'state' => true, 'data' => $data);
            return $ret;
        }
        foreach ($orderList as $k => $v) {
            if ($v['fx_order_id'] == "" && empty($v['fx_order_id'])) {
                continue;
            }
            if (!$data[$v['fx_order_id']]) {
                $data[$v['fx_order_id']]['status'] = "此订单不是发货状态";
                continue;
            }
            if ($v['store_id'] != $_SESSION['store_id']) {
                $data[$v['fx_order_id']]['status'] = "此订单不属于【" . $_SESSION['store_name'] . "】此店铺";
                continue;
            }
            $where['order_id'] = $v['order_id'];
            $savedata['deliver_explain'] = $data[$v['fx_order_id']]['remark'];
            $res = Model('order_common')->where($where)->update($savedata);
            if (!$res) {
                $failNum += 1;
                $data[$v['fx_order_id']]['status'] = '备注失败';
                continue;
            }
            $data[$v['fx_order_id']]['status'] = '备注成功';
            $succNum += 1;
        }

        $ret = array('totals' => count($data), 'actualNum' => $order_num, 'succNum' => $succNum, 'failNum' => $failNum, 'state' => true, 'data' => $data);
        return $ret;
    }

    public function getFenxiaoOrderList($conditions, $is_all = 0, $page = 10, $limit = '')
    {
        if ($is_all == 1) {
            $orders = $this->table('orders')
                ->where($conditions)
                ->field('* ,FROM_UNIXTIME(add_time,"%Y-%m-%d %H:%i:%S") as datetime')
                ->order('order_id desc')
                ->select();
        } else {
            $orders = $this->table('orders')
                ->where($conditions)
                ->field('* ,FROM_UNIXTIME(add_time,"%Y-%m-%d %H:%i:%S") as datetime')
                ->order('order_id desc')
                ->limit($limit)
                ->page($page)->select();
        }

        if (empty($orders)) return array();
        $oids = array_column($orders, 'order_id');
        $common_condition = array();
        $common_condition['order_id'] = array('in', $oids);
        $commons = $this->table('order_common')->where($common_condition)->select();
        $result = $order_commons = array();
        foreach ($commons as $order_common) {
            $order_commons[$order_common['order_id']] = $order_common;
        }
        $order_goods = array();
        $goods = $this->table('order_goods')->where($common_condition)->select();
        foreach ($goods as $row) {
            $pic = substr($row['goods_image'], 5);
            $tmp = explode("_", $pic);
            $row['goods_image'] = "data/upload/shop/store/goods/{$tmp[0]}/{$row['goods_image']}";
            $order_goods[$row['order_id']][] = $row;
        }

        foreach ($orders as $order) {
            $order_common = $order_commons[$order['order_id']];
            $result[$order['order_id']] = $order;
            foreach ($order_common as $collumn => $value) {
                $result[$order['order_id']][$collumn] = $value;
            }
            $result[$order['order_id']]['suborder'] = $order_goods[$order['order_id']];
        }
        return $result;
    }

    private function _isInDeliverArea($address)
    {
        /** @var areaModel $areaModel */
        $areaModel = Model('area');
        // 抓取前2个字符进行省级匹配
        $areaName = mb_substr($address, 0, 2, 'utf-8');
        if (!isset($this->_areaCache[0]['0' . $areaName])) {
            $area = $areaModel->getAreaInfo(array('area_name' => array('like', '%' . $areaName . '%'), 'area_parent_id' => 0));
            if (empty($area)) {
                $provenceId = 0;
            } else {
                $this->_areaCache[0]['0' . $areaName] = $provenceId = $area['area_id'];
            }
        } else {
            $provenceId = $this->_areaCache[0]['0' . $areaName];
        }
        if ($provenceId <= 0) return false;

        // 根据使用正则匹配本省市级名称
        $address = mb_substr($address, 2, 1000, 'utf-8');
        if (isset($this->_cityPatternCache[$provenceId])) {
            $pattern = $this->_cityPatternCache[$provenceId];
        } else {
            $cities = $areaModel->getAreaList(array('area_parent_id' => $provenceId));
            $names = array();
            foreach ($cities as $city) {
                $names[] = mb_substr($city['area_name'], 0, 2, 'utf-8');
            }
            $this->_cityPatternCache[$provenceId] = $pattern = implode('|', $names);
        }

        preg_match('/(' . $pattern . ')/isu', $address, $match);
        if (!isset($match[1])) return false;
        $areaName = $match[1];

        if (!isset($this->_areaCache[1]['1' . $areaName])) {
            $area = $areaModel->getAreaInfo(array('area_name' => array('like', '%' . $areaName . '%'), 'area_parent_id' => $provenceId));
            if (empty($area)) {
                return false;
            } else {
                $this->_areaCache[1]['1' . $areaName] = $cityId = $area['area_id'];
            }
        } else {
            $cityId = $this->_areaCache[1]['1' . $areaName];
        }

        if ($cityId <= 0) return false;

        // 根据使用正则匹配本市区级名称
        $start = strpos($address, $areaName) + 2;
        $start = $start < 2 ? 2 : $start;
        $address = mb_substr($address, $start, 1000, 'utf-8');
        if (isset($this->_cityPatternCache[$cityId])) {
            $pattern = $this->_cityPatternCache[$cityId];
        } else {
            $areas = $areaModel->getAreaList(array('area_parent_id' => $cityId));
            $names = array();
            foreach ($areas as $city) {
                $names[] = mb_substr($city['area_name'], 0, 2, 'utf-8');
            }
            $this->_cityPatternCache[$cityId] = $pattern = implode('|', $names);
        }

        preg_match('/(' . $pattern . ')/isu', $address, $match);
        if (!isset($match[1])) return false;
        $areaName = $match[1];

        if (!isset($this->_areaCache[2]['2' . $areaName])) {
            $area = $areaModel->getAreaInfo(array('area_name' => array('like', '%' . $areaName . '%'), 'area_parent_id' => $cityId));
            if (empty($area)) {
                return false;
            } else {
                $this->_areaCache[2]['2' . $areaName] = $areaId = $area['area_id'];
            }
        } else {
            $areaId = $this->_areaCache[2]['2' . $areaName];
        }

        if ($areaId <= 0) return false;

        return array('provenceId' => $provenceId, 'cityId' => $cityId, 'areaId' => $areaId);
    }
}




