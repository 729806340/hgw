<?php
/**
 * 任务计划 - 小时执行的任务
 *
 *
 *
 *
 * @汉购网提供技术支持 授权请购买shopnc授权
 * @license    http://www.hangowa.com
 * @link       交流群号：
 */
defined('ByShopWWI') or exit('Access Invalid!');

class hourControl extends BaseCronControl {
    /**
     * 执行频率常量 1小时
     * @var int
     */
    const EXE_TIMES = 3600;

    private $_doc;
    private $_xs;
    private $_index;
    private $_search;
    private $_contract_item;

    /**
     * 默认方法
     */
    public function indexOp() {

        //未付款订单超期自动关闭
        $this->_order_timeout_cancel();

        // 处理拼团数据
        $this->_pintuan();

        // 同步jdy数据
        //$this->_jdy_update();

        // 重新发送cps记录
        $this->_resendCps();

        //更新全文搜索内容
        $this->_xs_update();
    }

    public function order_timeout_cancelOp(){
        $this->_order_timeout_cancel();
    }

    public function jdyOp(){
        $this->_jdy_update();
    }

    public function cps()
    {

        // 重新发送cps记录
        $this->_resendCps();

    }
    public function jdy()
    {

        // 同步jdy数据
        $this->_jdy_update();

    }

    public function pintuanOp(){
        $this->_pintuan();
    }

    public function traceExpressOp(){
        // 查找已发货订单，
        /** @var orderModel $orderModel */
        $orderModel = model('order');
        /** @var expressModel $expressModel */
        $expressModel = model('express');
        $minId = 0;
        /** @var orderLogic $logic */
        $logic = Logic('order');
        do {
            $condition = array(
                'order_id' => array('gt',$minId),
                'order_state' => array('in',array(ORDER_STATE_SEND)),
                'order_from' => array('in',array(1,2,6,7)),
            );
            $orders = $orderModel->getOrderList($condition,100,'*','order_id asc');
            foreach ($orders as $order){
                $minId = $order['order_id'];
                $shipping_code = $order['shipping_code'];
                if (empty($shipping_code)) continue;
                $shipping_codes = explode(',',$shipping_code);
                $finish = true;
                foreach ($shipping_codes as $sn){
                    $sn = trim($sn);
                    $res = $expressModel->queryTencent('',$sn);
                    if ($res['state'] ==3) $finish = $finish&&true;
                }

                if ($finish){
                    // 更新
                    //var_dump("自动完成");
                    $result = $logic->changeOrderStateReceive($order,'system','系统','物流配送完成，系统自动收货。');
                }
            }

        }while(false);
    }

    /**
     * 未付款订单超期自动关闭
     */
    private function _order_timeout_cancel() {
    
        //实物订单超期未支付系统自动关闭
        $_break = false;
        $model_order = Model('order');
        $logic_order = Logic('order');
        $logic_order_book = Logic('order_book');
        $condition = array();
        $condition['order_state'] = ORDER_STATE_NEW;
        $condition['chain_code'] = 0;
        $condition['api_pay_time'] = 0;
        $condition['add_time'] = array('lt',TIMESTAMP - ORDER_AUTO_CANCEL_TIME * self::EXE_TIMES);
        //第三方支付的，超时时间延长3天
        $condition3 = $condition2 = $condition;
        $condition3['chain_code'] = array('gt', 0);
        $condition3['shequ_tuan_id'] = array('gt', 0);
        $condition4 = $condition3;
        $condition2['api_pay_time'] = array('lt',TIMESTAMP - ORDER_AUTO_CANCEL_TIME * self::EXE_TIMES);
        $condition4['api_pay_time'] = array('lt',TIMESTAMP - ORDER_AUTO_CANCEL_TIME * self::EXE_TIMES);
        //分批，每批处理100个订单，最多处理5W个订单
        for ($i = 0; $i < 500; $i++){
            if ($_break) {
                break;
            }
            
            $order_list = $model_order->getOrderList($condition, '', '*', '', 100);
            $order_list3 = $model_order->getOrderList($condition3, '', '*', '', 100);//社区团购
            //第三方支付的，超时的订单
            $order_list2 = $model_order->getOrderList($condition2, '', '*', '', 50);
            $order_list4 = $model_order->getOrderList($condition4, '', '*', '', 50);//社区团购
            $order_list = array_merge($order_list, $order_list2, $order_list3, $order_list4);

            if (empty($order_list)) break;
            foreach ($order_list as $order_info) {
                if ($order_info['order_type'] != 2) {
                    $result = $logic_order->changeOrderStateCancel($order_info,'system','系统','超期未支付系统自动关闭订单',true,array('order_state'=>ORDER_STATE_NEW));
                } else {
                    //预定订单单独处理
                    $result = $logic_order_book->changeOrderStateCancel($order_info,'system','系统','超期未支付系统自动关闭订单');
                }

                if (!$result['state']) {
                    $this->log('实物订单超期未支付关闭失败SN:'.$order_info['order_sn']); $_break = true; break;
                }
            }
        }

        //虚拟订单超期未支付系统自动关闭
        $_break = false;
        $model_vr_order = Model('vr_order');
        $logic_vr_order = Logic('vr_order');
        $condition = array();
        $condition['order_state'] = ORDER_STATE_NEW;
        $condition['api_pay_time'] = 0;
        $condition['add_time'] = array('lt',TIMESTAMP - ORDER_AUTO_CANCEL_TIME * self::EXE_TIMES);
    
        //分批，每批处理100个订单，最多处理5W个订单
        for ($i = 0; $i < 500; $i++){
            if ($_break) {
                break;
            }
            $order_list = $model_vr_order->getOrderList($condition, '', '*', '',100);
            if (empty($order_list)) break;
            foreach ($order_list as $order_info) {
                $result = $logic_vr_order->changeOrderStateCancel($order_info,'system','超期未支付系统自动关闭订单',false);
            }
            if (!$result['state']) {
                $this->log('虚拟订单超期未支付关闭失败SN:'.$order_info['order_sn']); $_break = true; break;
            }
        }
    }

    // 处理拼团
    private function _pintuan(){
        /** @var p_pintuan_tuanModel $tuanModel */
        $tuanModel = model('p_pintuan_tuan');
        /** @var p_pintuan_goodsModel $pintuanGoodsModel */
        $pintuanGoodsModel = model('p_pintuan_goods');

        $tuanGoodsCache = array();
        do{
            $expiredTuanList = $tuanModel->getTuanList(array(
                'expires_time'=>array('lt',time()),
                'state'=>0
            ));
            if(empty($expiredTuanList))break;
            foreach ($expiredTuanList as $tuan){
                if(!isset($tuanGoodsCache[$tuan['pintuan_goods_id']])){
                    $tuanGoodsCache[$tuan['pintuan_goods_id']] = $pintuanGoodsModel->getPintuanGoodsInfoByID($tuan['pintuan_goods_id']);
                }
                $tuanGoods = $tuanGoodsCache[$tuan['pintuan_goods_id']];
                if($tuan['user_count']>=$tuanGoods['minimum_user']){
                    // 凑团
                    $this->_couTuan($tuan,$tuanGoods);
                }else{
                    // 退款
                    $this->_refundTuan($tuan);
                }
            }
        }while(!empty($expiredTuan));
    }

    private function _couTuan($tuan,$tuanGoods){
        // 添加凑团会员
        /** @var p_pintuan_tuanModel $tuanModel */
        $tuanModel = model('p_pintuan_tuan');
        /** @var memberModel $memberModel */
        $memberModel = Model('member');
        /** @var p_pintuan_memberModel $tuanMemberModel */
        $tuanMemberModel = model('p_pintuan_member');
        $num = $tuanGoods['limit_user'] - $tuan['user_count'];
        if($num>0){
            $members = $memberModel->getMemberList(array('member_id'=>array('gt',rand(999,199999))),'member_id,member_name,member_avatar',null,'member_id ASC',$num);

            foreach ($members as $member){
                $goodsNum = rand($tuanGoods['limit_floor'],max($tuanGoods['limit_floor'],$tuanGoods['limit_ceilling']));
                $pintuanMemberInfo = array(
                    'state'=> 1,
                    'tuan_id'=> $tuan['tuan_id'],
                    'member_id'=> $member['member_id'],
                    'member_name'=> $member['member_name'],
                    'member_avatar'=> getMemberAvatar($member['member_avatar']),
                    'join_time'=> time(),
                    'order_amount'=> $goodsNum*$tuanGoods['goods_price'],
                    'goods_price'=> $tuanGoods['goods_price'],
                    'goods_num'=> $goodsNum,
                );
                $tuanMemberModel->addMember($pintuanMemberInfo);
            }
            $tuanInfo = array('state'=>1,'success_time'=>time(),'user_count'=>$tuanGoods['limit_user']);
            $tuanModel->editTuan($tuanInfo,array('tuan_id'=>$tuan['tuan_id']));
            $tuan['state'] = $tuanInfo['state'];
            $tuan['success_time'] = $tuanInfo['user_count'];
            $tuan['user_count'] = $tuanInfo['user_count'];
        }
        /** @var orderLogic $orderLogic */
        $orderLogic = Logic('order');
        $orderLogic->changeOrderTuanSuccess($tuan);
    }
    private function _refundTuan($tuan){
        /** @var p_pintuan_tuanModel $tuanModel */
        $tuanModel = model('p_pintuan_tuan');
        /** @var orderModel $orderModel */
        $orderModel = model('order');
        /** @var refund_returnModel $refundModel */
        $refundModel = model('refund_return');
        $tuanInfo = array('state'=>-1);
        // 标记退款失败
        $tuanModel->editTuan($tuanInfo,array('tuan_id'=>$tuan['tuan_id']));
        // 查找订单，查找商品，生成退款
        $orders = $orderModel->getOrderList(array('tuan_id'=>$tuan['tuan_id'],'order_state'=>ORDER_STATE_TUAN_PAY));
        /** @var RefundService $service */
        $service = Service('Refund');
        foreach ($orders as $order){
            $refund_array = array ();
            $refund_array ['refund_type'] = '1';  // 类型:1为退款,2为退货
            $refund_array ['seller_state'] = '2'; // 状态:1为待审核,2为同意,3为不同意
            $refund_array ['refund_state'] = '2'; // 状态:1为待审核,2为处理中,3为处理完成
            $refund_array ['order_lock'] = '1'; // 锁定类型:1为不用锁定,2为需要锁定
            $refund_array ['goods_id'] = '0';
            $refund_array ['order_goods_id'] = '0';
            $refund_array ['reason_id'] = '0';
            $refund_array ['reason_info'] = '拼团失败，系统自动全额退款';
            $refund_array ['goods_name'] = '订单商品全部退款';
            $refund_array ['refund_amount'] = ncPriceFormat ( $order['order_amount'] );
            $refund_array ['buyer_message'] = '拼团失败，系统自动全额退款';
            $refund_array ['seller_message'] = '拼团失败，系统自动同意退款';
            $refund_array ['admin_message'] = '拼团失败，系统自动处理退款';
            $refund_array['admin_name'] = '系统';
            $refund_array ['refund_way'] = in_array($order['payment_code'],array('wxpay','wx_jsapi','wx_saoma','alipay')) ? $order['payment_code'] : 'predeposit';
            $refund_array ['add_time'] =  $refund_array ['seller_time'] =  $refund_array ['admin_time'] = time ();
            $refund_array['operation_type']=3;
            $refund_array ['pic_info'] = '';
            $refund_id = $refundModel->addRefundReturn($refund_array,$order);
            $refundInfo = $refundModel->getRefundReturnInfo(array('refund_id'=>$refund_id));
            $detailDetail = $refundModel->getDetailInfo(array('refund_id'=>$refund_id));

            if (empty($detailDetail)) {
                $refundModel->addDetail($refundInfo,$order);
                $detailDetail = $refundModel->getDetailInfo(array('refund_id'=>$refund_id));
            }
            if (in_array($order['payment_code'],array('wxpay','wx_jsapi','wx_saoma','alipay'))) {
                try {
                    $detailDetail = $service->apiRefund($detailDetail);
                } catch (Exception $e) {
                    $this->log('自动退款失败_'. $e->getMessage());
                    continue;
                }
            }
            $refund['pay_amount'] = $detailDetail['pay_amount'];
            $res = $refundModel->editOrderRefund($refundInfo,'系统');
            if($res){
                $refund_array = array();
                $refund_array['refund_state'] = 3;
                $refundModel->editRefundReturn(array('refund_id'=>$refund_id), $refund_array);

                // 发送买家消息
                $param = array();
                $param['code'] = 'refund_return_notice';
                $param['member_id'] = $refund['buyer_id'];
                $param['param'] = array(
                    'refund_url' => urlShop('member_refund', 'view', array('refund_id' => $refund['refund_id'])),
                    'refund_sn' => $refund['refund_sn']
                );
                QueueClient::push('sendMemberMsg', $param);
                $this->log('退款确认，退款编号'.$refund['refund_sn']);
            }
        }
    }
    private function _resendCps()
    {
        /** @var cpsModel $cpsModel */
        $cpsModel = Model('cps');
        // 重置所有推送失败的记录
        $cpsModel->where(array('push_status'=>10))->update(array('push_status'=>0));
        /** @var CpsService $cpsService */
        $cpsService = Service('Cps');
        do{
            $cps = $cpsModel->where(array(
                'push_status'=>0,
                //'types'=>array('neq','yiqifa')
            ))->find();
            if(empty($cps)) break;
            //if($cps['types']== 'yiqifa') continue;
            $cpsUnion = $cpsService->getUnion($cps['types']);
            $cps['push_status'] = $cpsUnion->push($cps)?1:10;
            $cpsModel->where(array('id'=>$cps['id']))->update($cps);
        }while(true);
    }

    /**
     * 初始化对象
     */
    private function _ini_xs(){
        require(BASE_DATA_PATH.'/api/xs/lib/XS.php');
        $this->_doc = new XSDocument();
        $this->_xs = new XS(C('fullindexer.appname'));
        $this->_index = $this->_xs->index;
        $this->_search = $this->_xs->search;
        $this->_search->setCharset(CHARSET);

        //查询消费者保障服务
        $contract_item = array();
        if (C('contract_allow') == 1) {
            $this->_contract_item = Model('contract')->getContractItemByCache();
        }
    }

    /**
     * 全量创建索引
     */
    public function xs_createOp() {
        if (!C('fullindexer.open')) return;
        $this->_ini_xs();
        $model = Model();
        try {
            //每次批量更新商品数
            $step_num = 100;
            $model_goods = Model('goods');

            if (C('dbdriver') == 'mysql'||C('dbdriver') == 'mysqli') {
                $_field = "CONCAT(goods_commonid,',',color_id)";
                $_distinct = 'nc_distinct';
            } elseif (C('dbdriver') == 'oracle') {
                $_field = $_distinct = "goods_commonid||','||color_id";
            }
            $count = $model_goods->getGoodsOnlineCount(array(),"distinct ".$_field);
            echo 'Total:'.$count."\n";
            if ($count != 0) {
                for ($i = 0; $i <= $count; $i = $i + $step_num){
                    if (C('dbdriver') == 'mysql'||C('dbdriver') == 'mysqli') {
                        $goods_list = $model_goods->getGoodsOnlineList(array(), '*,'.$_field.' nc_distinct', 0, '', "{$i},{$step_num}", $_distinct);
                    } elseif (C('dbdriver') == 'oracle') {
                        //先查出所有goods_id,再使用in查询
                        $condition['goods_state']   = 1;
                        $condition['goods_verify']  = 1;
                        $goods_id_list =  $model->table('goods')->where($condition)->field('min(goods_id) as goods_id,'.$_field)->group($_field)->key('goods_id')->limit("{$i},{$step_num}")->select();
                        if ($goods_id_list) {
                            $condition1 = array('goods_id' => array('in',array_keys($goods_id_list)));
                            $goods_list = $model_goods->getGoodsOnlineList($condition1, '*', 0, '', '', false);
                        }
                    }
                    $this->_build_goods($goods_list);
                    echo $i." ok\n";
                    flush();
                    ob_flush();
                }
            }

            if ($count > 0) {
                sleep(2);
                $this->_index->flushIndex();
                sleep(2);
                $this->_index->flushLogging();
            }
        } catch (XSException $e) {
            $this->log($e->getMessage());
        }
    }

    /**
     * 更新增量索引
     */
    public function _xs_update() {
        if (!C('fullindexer.open')) return;
        $this->_ini_xs();
        $model = Model();
        try {
            //更新多长时间内的新增(编辑)商品信息，该时间一般与定时任务触发间隔时间一致,单位是秒,默认3600
            $step_time = self::EXE_TIMES + 60;
            //每次批量更新商品数
            $step_num = 100;

            $model_goods = Model('goods');
            $condition = array();
            $condition['goods_edittime'] = array('egt',TIMESTAMP-$step_time);
            if (C('dbdriver') == 'mysql'||C('dbdriver') == 'mysqli') {
                $_field = "CONCAT(goods_commonid,',',color_id)";
                $_distinct = 'nc_distinct';
            } elseif (C('dbdriver') == 'oracle') {
                $_field = $_distinct = "goods_commonid||','||color_id";
            }
            $count = $model_goods->getGoodsOnlineCount($condition,"distinct ".$_field);
            echo 'Total:'.$count."\n";
            for ($i = 0; $i <= $count; $i = $i + $step_num){
                if (C('dbdriver') == 'mysql'||C('dbdriver') == 'mysqli') {
                    $goods_list = $model_goods->getGoodsOnlineList($condition, '*,'.$_field.' nc_distinct', 0, '', "{$i},{$step_num}", $_distinct);
                } elseif (C('dbdriver') == 'oracle') {
                    //先查出所有goods_id,再使用in查询
                    $condition['goods_state']   = 1;
                    $condition['goods_verify']  = 1;
                    $goods_id_list =  $model->table('goods')->where($condition)->field('min(goods_id) as goods_id,'.$_field)->group($_field)->key('goods_id')->limit("{$i},{$step_num}")->select();
                    if ($goods_id_list) {
                        $condition1 = array('goods_id' => array('in',array_keys($goods_id_list)));
                        $goods_list = $model_goods->getGoodsOnlineList($condition1, '*', 0, '', '', false);
                    }
                }
                //通过commonid得到所有goods_id，然后删除全文索引中的goods_id内容
                $goods_commonid_array = array();
                foreach ($goods_list as $_v) {
                    $goods_commonid_array[] = $_v['goods_commonid'];
                }
                if ($goods_commonid_array) {
                    $condition1 = array('goods_commonid' => array('in',$goods_commonid_array));
                    $goods_list1 = $model_goods->getGoodsOnlineList($condition1, 'goods_id', 0, '', '', false);
                    if ($goods_list1) {
                        $goods_id_array = array();
                        foreach ($goods_list1 as $_v) {
                            $goods_id_array[] = $_v['goods_id'];
                        }
                        $this->_index->del($goods_id_array);
                    }
                }
                $this->_build_goods($goods_list);
                echo $i." ok\n";
                flush();
                ob_flush();
            }
            if ($count > 0) {
                sleep(2);
                $this->_index->flushIndex();
                sleep(2);
                $this->_index->flushLogging();
            }
        } catch (XSException $e) {
            $this->log($e->getMessage());
        }
    }

    /**
     * 索引商品数据
     * @param array $goods_list
     */
    private function _build_goods($goods_list = array()) {
        if (empty($goods_list) || !is_array($goods_list)) return;
        $goods_class = Model('goods_class')->getGoodsClassForCacheModel();
        $model_goods = Model('goods');
        $goods_commonid_array = array();
        $goods_id_array = array();
        $store_id_array = array();
        foreach ($goods_list as $k => $v) {
            $goods_commonid_array[] = $v['goods_commonid'];
            $goods_id_array[] = $v['goods_id'];
            $store_id_array[] = $v['store_id'];
        }

        //商品图
        $image_list = $model_goods->getGoodsImageList(array('goods_commonid' => array('in',$goods_commonid_array)), '*', 'is_default desc,goods_image_id asc');

        // 店铺
        $store_list = Model('store')->getStoreMemberIDList($store_id_array);

        $kill_common_ids = array();
        //首先进行一次循环，根据商品分类的show_type设置，确定哪些SKU显示，缓存哪些商品图
        foreach ($goods_list as $k => $goods_info) {
            if ($goods_class[$goods_info['gc_id']]['show_type'] == 1) {
                //原来的显示方式，显示多个SKU,每个SKU显示各自的图
                foreach ($image_list as $image_info) {
                    if ($goods_info['goods_commonid'] == $image_info['goods_commonid'] 
                    && $goods_info['store_id'] == $image_info['store_id'] 
                    && $goods_info['color_id'] == $image_info['color_id']) {
                        $goods_list[$k]['image'][] = $image_info['goods_image'];
                    }
                }
            } else {
                //一个commonid中只显示一个SKU，显示各个SKU的主图
                foreach ($image_list as $image_info) {
                    if ($goods_info['goods_commonid'] == $image_info['goods_commonid'] 
                    && $goods_info['store_id'] == $image_info['store_id'] 
                    && $image_info['is_default'] == 1) {
                        $goods_list[$k]['image'][] = $image_info['goods_image'];
                    }
                }
                if (in_array($goods_info['goods_commonid'],$kill_common_ids)) {
                    unset($goods_list[$k]);
                } else {
                    $kill_common_ids[] = $goods_info['goods_commonid'];
                }
            }
        }

        //取common表内容
        $condition_common = array();
        $condition_common['goods_commonid'] = array('in',$goods_commonid_array);
        $goods_common_list = $model_goods->getGoodsCommonOnlineList($condition_common,'*',0);
        $goods_common_new_list = array();
        foreach($goods_common_list as $k => $v) {
            $goods_common_new_list[$v['goods_commonid']] = $v;
        }

        //取属性表值
        $model_type = Model('type');
        $attr_list = $model_type->getGoodsAttrIndexList(array('goods_id'=>array('in',$goods_id_array)),0,'goods_id,attr_value_id');
        if (is_array($attr_list) && !empty($attr_list)) {
            $attr_value_list = array();
            foreach ($attr_list as $val) {
                $attr_value_list[$val['goods_id']][] = $val['attr_value_id'];
            }
        }

        //处理商品消费者保障服务信息
        $goods_list = $model_goods->getGoodsContract($goods_list, $this->_contract_item);

        //整理需要索引的数据
        foreach ($goods_list as $k => $v) {
			$cate_3 = $cate_2 = $cate_1 = null;
            $gc_id = $v['gc_id'];
            $depth = $goods_class[$gc_id]['depth'];
            if ($depth == 3) {
                $cate_3 = $gc_id; $gc_id = $goods_class[$gc_id]['gc_parent_id']; $depth--;
            }
            if ($depth == 2) {
                $cate_2 = $gc_id; $gc_id = $goods_class[$gc_id]['gc_parent_id']; $depth--;
            }
            if ($depth == 1) {
                $cate_1 = $gc_id; $gc_id = $goods_class[$gc_id]['gc_parent_id'];
            }
            $index_data = array();
            $index_data['pk'] = $v['goods_id'];
            $index_data['goods_id'] = $v['goods_id'];
            $index_data['goods_name'] = $v['goods_name'];
            $index_data['goods_jingle'] = $v['goods_jingle'];
            $index_data['brand_id'] = $v['brand_id'];
            $index_data['is_book'] = $v['is_book'];
            $index_data['goods_promotion_price'] = $v['goods_promotion_price'];
            $index_data['goods_click'] = $v['goods_click'];
            $index_data['goods_salenum'] = $v['goods_salenum'];
            $index_data['goods_barcode'] = $v['goods_barcode'];
            // 判断店铺是否为自营店铺
            $index_data['store_id'] = $v['is_own_shop'];
            $index_data['area_id'] = $v['areaid_1'];
            $index_data['gc_id'] = $v['gc_id'];
            $index_data['gc_name'] = str_replace('&gt;','',$goods_common_new_list[$v['goods_commonid']]['gc_name']);
            $index_data['brand_name'] = $goods_common_new_list[$v['goods_commonid']]['brand_name'];
            $index_data['have_gift'] = $v['have_gift'];
            if (!empty($attr_value_list[$v['goods_id']])) {
                $index_data['attr_id'] = implode('_',$attr_value_list[$v['goods_id']]);
            }
            if (!empty($cate_1)) {
                $index_data['cate_1'] = $cate_1;
            }else{
				$index_data['cate_1'] = 0;
			}
            if (!empty($cate_2)) {
                $index_data['cate_2'] = $cate_2;
            }else{
				$index_data['cate_2'] = 0;
			}
            if (!empty($cate_3)) {
                $index_data['cate_3'] = $cate_3;
            }else{
				$index_data['cate_3'] = 0;
			}
			for($i=1;$i<=10;$i++) {
			    $index_data['contract_'.$i] = $v['contract_'.$i] ? '1' : '0';
			}
			if (is_array($v['contractlist']) && !empty($v['contractlist'])) {
			    foreach ($v['contractlist'] as $xbk => $xbv) {
			        $v['contractlist'][$xbk] = array();
			        $v['contractlist'][$xbk]['cti_descurl'] = $xbv['cti_descurl'];
			        $v['contractlist'][$xbk]['cti_name'] = $xbv['cti_name'];
			        $v['contractlist'][$xbk]['cti_icon_url_60'] = $xbv['cti_icon_url_60'];
			    }
			}

            $index_data['main_body'] = serialize(array(
            	   'goods_promotion_type' => $v['goods_promotion_type'],
                   'goods_marketprice' => $v['goods_marketprice'],
                   'contractlist' => $v['contractlist'],
                   'evaluation_good_star' => $v['evaluation_good_star'],
                   'is_virtual' => $v['is_virtual'],
                   'is_fcode' => $v['is_fcode'],
                   'is_presell' => $v['is_presell'],
                   'evaluation_count' => $v['evaluation_count'],
                   'member_id' => $store_list[$v['store_id']]['member_id'],
                   'store_domain' => $store_list[$v['store_id']]['store_domain'],
                   'store_id' => $v['store_id'],
                   'goods_storage' => $v['goods_storage'],
                   'goods_image' => $v['goods_image'],
                   'store_name' => $v['store_name'],
                   'image' => $v['image']
            ));
            //添加到索引库
             $this->_doc->setFields($index_data);
             $this->_index->update($this->_doc);
        }
    }

    public function xs_clearOp(){
        if (!C('fullindexer.open')) return;
        $this->_ini_xs();

        try {
            $this->_index->clean();
        } catch (XSException $e) {
            $this->log($e->getMessage());
        }
    }

    public function xs_flushLoggingOp(){
        if (!C('fullindexer.open')) return;
        $this->_ini_xs();
        try {
            $this->_index->flushLogging();
        } catch (XSException $e) {
            $this->log($e->getMessage());
        }
    }

    public function xs_flushIndexOp(){
        if (!C('fullindexer.open')) return;
        $this->_ini_xs();

        try {
            $this->_index->flushIndex();
        } catch (XSException $e) {
            $this->log($e->getMessage());
        }
    }

    private function _jdy_update() {
        /** @var jdyLogic $jdyLogic */
        $jdyLogic = Logic('jdy');
        $jdyLogic->getSupplierList();
        $jdyLogic->getGoodsList();
    }
}
