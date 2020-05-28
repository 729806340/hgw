<?php
/**
 * 会员退款
 * @copyright  Copyright (c) 2007-2015 ShopWWI Inc. (http://www.shopwwi.com)
 * @license    http://www.shopwwi.com
 * @link       http://www.shopwwi.com
 * @since      File available since Release v1.1
 */
defined('ByShopWWI') or exit('Access Invalid!');

class member_refundControl extends mobileMemberControl
{
    private $refund_state = array(
        '1' => '处理中',
        '2' => '待管理员处理',
        '3' => '已完成',
    );

    private $refund_type = array(
        '1' => '退款',
        '2' => '退款退货',
    );

    private $seller_state = array(
        '1' => '待审核',
        '2' => '同意',
        '3' => '不同意',
    );

    private $refund_detail_state = array(
        '1' => '处理中',
        '2' => '已完成',
    );

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 全部退款获取订单信息
     */
    public function refund_all_formOp()
    {
        //$_POST['order_id']='259993';
        $model_refund = Model('refund_return');
        $member_id = $this->member_info['member_id'];
        $order_id = intval($_POST['order_id']);
        $model_trade = Model('trade');
        $order_paid = $model_trade->getOrderState('order_paid');//订单状态20:已付款

        $model_order = Model('order');
        $condition = array();
        $condition['buyer_id'] = $member_id;
        $condition['order_id'] = $order_id;
        $condition['order_state'] = $order_paid;
        $order_info = $model_order->getOrderInfo($condition);
        if (!empty($order_info) && is_array($order_info)) {
            $book_amount = Logic('order_book')->getDepositAmount($order_info);//订金金额
            $order = array();
            $order['order_id'] = $order_info['order_id'];
            $order['order_type'] = $order_info['order_type'];
            $order['order_amount'] = ncPriceFormat($order_info['order_amount']);
            $order['order_sn'] = $order_info['order_sn'];
            $order['store_name'] = $order_info['store_name'];
            $order['store_id'] = $order_info['store_id'];
            $order['allow_refund_amount'] = ncPriceFormat($order_info['order_amount'] - $book_amount);//可退款金额
            $order['book_amount'] = ncPriceFormat($book_amount);

            $goods_list = array();
            $gift_list = array();
            $model_order = Model('order');
            $condition = array();
            $condition['order_id'] = $order_id;
            $order_goods_list = $model_order->getOrderGoodsList($condition);
            foreach ($order_goods_list as $key => $value) {
                $goods = array();
                $goods['goods_id'] = $value['goods_id'];
                $goods['goods_name'] = $value['goods_name'];
                $goods['goods_price'] = $value['goods_price'];
                $goods['goods_num'] = $value['goods_num'];
                $goods['goods_spec'] = $value['goods_spec'];
                $goods['goods_img_360'] = thumb($value, 360);
                $goods['goods_type'] = orderGoodsType($value['goods_type']);
                if ($value['goods_type'] == 5) {//赠品商品
                    $gift_list[] = $goods;
                } else {
                    $goods_list[] = $goods;
                }
            }
            output_data(array('order' => $order, 'goods_list' => $goods_list, 'gift_list' => $gift_list));
        } else {
            output_error('参数错误');
        }
    }

    /**
     * 全部退款保存数据
     */
    public function refund_all_postOp()
    {
        //$_POST = array('order_id'=>'259993','reason_info'=>'测试退款','buyer_message'=>'测试退款','refund_pic'=>'["http://www.hangowa.com/2.jpg" ,"http://www.hangowa.com/3.jpg"]');
        if ($_POST['refund_pic']) $_POST['refund_pic'] = json_decode($_POST['refund_pic'], true);
        $model_refund = Model('refund_return');
        $member_id = $this->member_info['member_id'];
        $order_id = intval($_POST['order_id']);
        $model_trade = Model('trade');
        $order_paid = $model_trade->getOrderState('order_paid');//订单状态20:已付款

        $model_order = Model('order');
        $condition = array();
        $condition['buyer_id'] = $member_id;
        $condition['order_id'] = $order_id;
        $condition['order_state'] = $order_paid;
        $order_info = $model_order->getOrderInfo($condition);
        $payment_code = $order_info['payment_code'];//支付方式

        $condition = array();
        $condition['buyer_id'] = $member_id;
        $condition['order_id'] = $order_id;
        $condition['goods_id'] = '0';
        $condition['seller_state'] = array('lt', '3');
        $refund = $model_refund->getRefundReturnInfo($condition);
        $this->testLog($_POST);
        if (empty($order_info) || $payment_code == 'offline' || $refund['refund_id'] > 0) {//检查数据,防止页面刷新不及时造成数据错误
            output_error('参数错误');
        } else {
            $book_amount = Logic('order_book')->getDepositAmount($order_info);//订金金额
            $allow_refund_amount = ncPriceFormat($order_info['order_amount'] - $book_amount);//可退款金额

            $refund_array = array();
            $refund_array['refund_type'] = '1';//类型:1为退款,2为退货
            $refund_array['seller_state'] = '1';//状态:1为待审核,2为同意,3为不同意
            $refund_array['order_lock'] = '2';//锁定类型:1为不用锁定,2为需要锁定
            $refund_array['goods_id'] = '0';
            $refund_array['order_goods_id'] = '0';
            $refund_array['reason_id'] = '0';
            $refund_array['reason_info'] = $_POST['reason_info'] ? $_POST['reason_info'] : '订单取消，全部退款';
            $refund_array['goods_name'] = '订单商品全部退款';
            $refund_array['refund_amount'] = ncPriceFormat($allow_refund_amount);
            $refund_array['buyer_message'] = $_POST['buyer_message'];
            $refund_array['add_time'] = time();
            if (is_array($_POST['refund_pic'])) {
                $pic_array = array();
                $pic_array['buyer'] = $_POST['refund_pic'];//上传凭证
                $info = serialize($pic_array);
                $refund_array['pic_info'] = $info;
            }
            $state = $model_refund->addRefundReturn($refund_array, $order_info);
            if ($state) {
                $model_refund->editOrderLock($order_id);
                output_data(1);
            } else {
                output_error('退款申请保存失败');
            }
        }
    }


    /**
     * 部分退款获取订单信息
     */
    public function refund_formOp()
    {
        //$_POST = array('order_id'=>259997 , 'order_goods_id'=>260340);
        $model_refund = Model('refund_return');
        $condition = array();
        $reason_list = $model_refund->getReasonList($condition, '', '', 'reason_id,reason_info');//退款退货原因
        $member_id = $this->member_info['member_id'];
        $order_id = intval($_POST['order_id']);
        $goods_id = intval($_POST['order_goods_id']);//订单商品表编号

        if (!$order_id || !$goods_id) output_error('参数错误');

        $model_order = Model('order');
        $condition = array();
        $condition['buyer_id'] = $member_id;
        $condition['order_id'] = $order_id;
        $order_info = $model_refund->getRightOrderList($condition, $goods_id);
        if (!count($order_info['goods_list'])) output_error('未查询到订单信息');
        $refund_state = $model_refund->getRefundState($order_info,true);//根据订单状态判断是否可以退款退货
        if (!$refund_state) output_error('该订单不允许退款退货');

        $order = array();
        $order['order_id'] = $order_info['order_id'];
        $order['order_type'] = $order_info['order_type'];
        $order['order_amount'] = ncPriceFormat($order_info['order_amount']);
        $order['order_sn'] = $order_info['order_sn'];
        $order['store_name'] = $order_info['store_name'];
        $order['store_id'] = $order_info['store_id'];

        $goods = array();
        $goods_list = $order_info['goods_list'];
        $goods_info = $goods_list[0];

        $goods['store_id'] = $goods_info['store_id'];
        $goods['order_goods_id'] = $goods_info['rec_id'];
        $goods['goods_id'] = $goods_info['goods_id'];
        $goods['goods_name'] = $goods_info['goods_name'];
        $goods['goods_type'] = orderGoodsType($goods_info['goods_type']);
        $goods['goods_img_360'] = thumb($goods_info, 360);
        $goods['goods_price'] = ncPriceFormat($goods_info['goods_price']);
        $goods['goods_spec'] = $goods_info['goods_spec'];
        $goods['goods_num'] = $goods_info['goods_num'];

        $goods_pay_price = $goods_info['goods_pay_price'];//商品实际成交价
        $order_amount = $order_info['order_amount'];//订单金额
        $order_refund_amount = $order_info['refund_amount'];//订单退款金额
        if ($order_amount < ($goods_pay_price + $order_refund_amount)) {
            $goods_pay_price = $order_amount - $order_refund_amount;
        }
        $goods['goods_pay_price'] = ncPriceFormat($goods_pay_price);
        output_data(array('order' => $order, 'goods' => $goods, 'reason_list' => array_values($reason_list)));

    }

    /**
     * 部分退款保存数据
     */
    public function refund_postOp()
    {
        //$_POST = array('order_id'=>259996,'order_goods_id'=>260340,'reason_id'=>98,'refund_type'=>1,'refund_amount'=>'1000.00','buyer_message'=>'测试');

        $member_id = $this->member_info['member_id'];
        $order_id = intval($_POST['order_id']);
        $goods_id = intval($_POST['order_goods_id']);//订单商品表编号
        if (!$goods_id) output_error('参数错误');

        $model_refund = Model('refund_return');

        $condition = array();
        $condition['buyer_id'] = $member_id;
        $condition['order_id'] = $order_id;
        $order_info = $model_refund->getRightOrderList($condition, $goods_id);
        $refund_state = $model_refund->getRefundState($order_info,true);//根据订单状态判断是否可以退款退货
        if (!$refund_state) output_error('该订单不能发起退款退货，请与客服联系');
        $condition = array();
        $condition['buyer_id'] = $member_id;
        $condition['order_id'] = $order_id;
        $condition['order_goods_id'] = $goods_id;
        $condition['seller_state'] = array('lt', '3');
        $refund = $model_refund->getRefundReturnInfo($condition);
        if (!empty($refund)) output_error('该商品已发起退款退货，请等待平台处理');

        $goods_list = $order_info['goods_list'];
        $goods_info = $goods_list[0];
        if (empty($goods_info)) output_error('参数错误');
        $refund_array = array();
        $goods_pay_price = $goods_info['goods_pay_price'];//商品实际成交价
        $order_amount = $order_info['order_amount'];//订单金额
        $order_refund_amount = $order_info['refund_amount'];//订单退款金额
        if ($order_amount < ($goods_pay_price + $order_refund_amount)) {
            $goods_pay_price = $order_amount - $order_refund_amount;
        }
        $refund_amount = floatval($_POST['refund_amount']);//退款金额
        if (($refund_amount < 0) || ($refund_amount > $goods_pay_price)) {
            //$refund_amount = $goods_pay_price;
            output_error("退款金额超出实际成交价格");
        }
        $goods_num = intval($_POST['goods_num']);//退货数量
        if (($goods_num < 0) || ($goods_num > $goods_info['goods_num'])) {
            $goods_num = 1;
        }
        $reason_list = $model_refund->getReasonList(array(), '', '', 'reason_id,reason_info');//退款退货原因
        $refund_array['reason_info'] = '';
        $reason_id = intval($_POST['reason_id']);//退货退款原因
        $refund_array['reason_id'] = $reason_id;
        $reason_array = array();
        $reason_array['reason_info'] = '其他';
        $reason_list[0] = $reason_array;
        if (!empty($reason_list[$reason_id])) {
            $reason_array = $reason_list[$reason_id];
            $refund_array['reason_info'] = $reason_array['reason_info'];
        }

        $pic_array = array();
        $pic_array['buyer'] = $_POST['refund_pic'];//上传凭证
        $info = serialize($pic_array);
        $refund_array['pic_info'] = $info;

        $model_trade = Model('trade');
        $order_shipped = $model_trade->getOrderState('order_shipped');//订单状态30:已发货
        if ($order_info['order_state'] == $order_shipped) {
            $refund_array['order_lock'] = '2';//锁定类型:1为不用锁定,2为需要锁定
        }
        $refund_array['refund_type'] = $_POST['refund_type'];//类型:1为退款,2为退货
        $refund_array['return_type'] = '2';//退货类型:1为不用退货,2为需要退货
        if ($refund_array['refund_type'] != '2') {
            $refund_array['refund_type'] = '1';
            $refund_array['return_type'] = '1';
        }
        $refund_array['seller_state'] = '1';//状态:1为待审核,2为同意,3为不同意
        $refund_array['refund_amount'] = ncPriceFormat($refund_amount);
        $refund_array['goods_num'] = $goods_num;
        $refund_array['buyer_message'] = $_POST['buyer_message'];
        $refund_array['add_time'] = time();

        $state = $model_refund->addRefundReturn($refund_array, $order_info, $goods_info);
        if ($state) {
            if ($order_info['order_state'] == $order_shipped) {
                $model_refund->editOrderLock($order_id);
            }
            output_data(1);
        } else {
            output_error('退款退货申请保存失败');
        }
    }

    /**
     * 上传凭证
     */
    public function upload_picOp()
    {
        $upload = new UploadFile();
        $dir = ATTACH_PATH . DS . 'refund' . DS;
        $upload->set('default_dir', $dir);
        $upload->set('allow_type', array('jpg', 'jpeg', 'gif', 'png'));
        $result = 0;
        if (!empty($_FILES['refund_pic']['name'])) {
            $result = $upload->upfile('refund_pic');
        }
        if ($result) {
            $file_name = $upload->file_name;
            $pic = UPLOAD_SITE_URL . '/' . ATTACH_PATH . '/refund/' . $file_name;
            output_data(array('pic' => $pic));
        } else {
            output_error('图片上传失败');
        }
    }

    /**
     * 退款记录列表
     */
    public function get_refund_listOp()
    {

        $model_order = Model('order');
        $model_refund = Model('refund_return');
        $member_id = $this->member_info['member_id'];
        $refund_list = array();
        $condition = array();
        $condition['buyer_id'] = $member_id;
        $keyword_type = array('order_sn', 'refund_sn', 'goods_name');
        if (trim($_POST['k']) != '' && in_array($_POST['type'], $keyword_type)) {
            $type = $_POST['type'];
            $condition[$type] = array('like', '%' . $_GET['k'] . '%');
        }
        if (trim($_POST['add_time_from']) != '' || trim($_POST['add_time_to']) != '') {
            $add_time_from = strtotime(trim($_POST['add_time_from']));
            $add_time_to = strtotime(trim($_POST['add_time_to']));
            if ($add_time_from !== false || $add_time_to !== false) {
                $condition['add_time'] = array('time', array($add_time_from, $add_time_to));
            }
        }
        $list = $model_refund->getRefundReturnList($condition, $this->page);
        //var_dump($list);
        $page_count = $model_refund->gettotalpage();
        if (!empty($list) && is_array($list)) {
            $seller_state = $model_refund->getRefundStateArray('seller');
            $admin_state = $model_refund->getRefundStateArray('admin');
            foreach ($list as $k => $v) {
                $val = array();
                $val['refund_id'] = $v['refund_id'];
                $val['order_id'] = $v['order_id'];
                $val['refund_amount'] = ncPriceFormat($v['refund_amount']);
                $val['refund_sn'] = $v['refund_sn'];
                $val['order_sn'] = $v['order_sn'];
                $val['refund_type'] = $this->refund_type[$v['refund_type']];
                $val['refund_state'] = $this->refund_state[$v['refund_state']];
                $val['add_time'] = date("Y-m-d H:i:s", $v['add_time']);
                //$val['seller_state_v'] = $v['seller_state'];
                $val['seller_state'] = $this->seller_state[$v['seller_state']];
                //$val['admin_state_v'] = $v['refund_state'];
                $val['admin_state'] = $v['seller_state'] == 2 ? $admin_state[$v['refund_state']] : '无';
                $val['store_id'] = $v['store_id'];
                $val['store_name'] = $v['store_name'];
                $val['reason_info'] = $v['reason_info'];
                $val['buyer_message'] = $v['buyer_message'];
                $val['seller_message'] = $v['seller_message'];
                $val['admin_message'] = $v['admin_message'];
                //$val['refund_state'] = '';
                $goods_list = array();
                if ($v['goods_id'] > 0) {
                    $goods = array();
                    $goods['goods_id'] = $v['goods_id'];
                    $goods['goods_name'] = $v['goods_name'];

                    $condition = array();
                    $condition['rec_id'] = $v['order_goods_id'];
                    $order_goods_list = $model_order->getOrderGoodsList($condition);
                    $goods['goods_spec'] = $order_goods_list[0]['goods_spec'];

                    $goods['goods_img_360'] = thumb($v, 360);
                    $goods_list[] = $goods;
                } else {
                    $condition = array();
                    $condition['order_id'] = $v['order_id'];
                    $order_goods_list = $model_order->getOrderGoodsList($condition);
                    foreach ($order_goods_list as $key => $value) {
                        $goods = array();
                        $goods['goods_id'] = $value['goods_id'];
                        $goods['goods_name'] = $value['goods_name'];
                        $goods['goods_spec'] = $value['goods_spec'];
                        $goods['goods_img_360'] = thumb($value, 360);
                        $goods_list[] = $goods;
                    }
                }
                $val['goods_list'] = $goods_list;
                $refund_list[] = $val;
            }
        }
        if (intval($_POST['curpage']) > $page_count) $refund_list = array();
        output_data(array('refund_list' => $refund_list), mobile_page($page_count));
    }

    /**
     * 查看退款信息
     *
     */
    public function get_refund_infoOp()
    {
        //$_POST['refund_id'] = 1092;
        $model_refund = Model('refund_return');
        $member_id = $this->member_info['member_id'];
        $condition = array();
        $condition['buyer_id'] = $member_id;
        $condition['refund_id'] = intval($_POST['refund_id']);
        $refund_info = $model_refund->getRefundReturnInfo($condition);
        if (!empty($refund_info) && is_array($refund_info)) {
            $seller_state = $model_refund->getRefundStateArray('seller');
            $admin_state = $model_refund->getRefundStateArray('admin');
            $refund = array();
            $refund['refund_id'] = $refund_info['refund_id'];
            $refund['goods_id'] = $refund_info['goods_id'];
            $refund['goods_name'] = $refund_info['goods_name'];
            $refund['order_id'] = $refund_info['order_id'];
            $refund['refund_type'] = $this->refund_type[$refund_info['refund_type']];
            $refund['refund_state'] = $this->refund_state[$refund_info['refund_state']];
            $refund['refund_amount'] = ncPriceFormat($refund_info['refund_amount']);
            $refund['goods_num'] = intval($refund_info['goods_num']);
            $refund['refund_sn'] = $refund_info['refund_sn'];
            $refund['order_sn'] = $refund_info['order_sn'];
            $refund['add_time'] = date("Y-m-d H:i:s", $refund_info['add_time']);
            $refund['goods_img_360'] = thumb($refund_info, 360);
            $refund['seller_state'] = $this->seller_state[$refund_info['seller_state']];
            $refund['admin_state'] = $refund_info['seller_state'] == 2 ? $admin_state[$refund_info['refund_state']] : '无';
            $refund['store_id'] = $refund_info['store_id'];
            $refund['store_name'] = $refund_info['store_name'];
            $refund['reason_info'] = $refund_info['reason_info'];
            $refund['buyer_message'] = $refund_info['buyer_message'];
            $refund['seller_message'] = $refund_info['seller_message'];
            $refund['admin_message'] = $refund_info['admin_message'];
            $info['buyer'] = array();
            if (!empty($refund_info['pic_info'])) {
                $info = unserialize($refund_info['pic_info']);
            }
            $pic_list = array();
            if (is_array($info['buyer'])) {
                foreach ($info['buyer'] as $k => $v) {
                    if (!empty($v)) {
                        $pic_list[] = UPLOAD_SITE_URL . '/' . ATTACH_PATH . '/refund/' . $v;
                    }
                }
            }

            $detail_info = $model_refund->getDetailInfo(array('refund_id' => $refund_info['refund_id']));
            $detail_array = array();
            if (!empty($detail_info) && $detail_info['refund_state'] == 2) {
                $detail_array['refund_code'] = orderPaymentName($detail_info['refund_code']);
                $detail_array['refund_amount'] = ncPriceFormat($detail_info['refund_amount']);
                $detail_array['pay_amount'] = ncPriceFormat($detail_info['pay_amount']);
                $detail_array['pd_amount'] = ncPriceFormat($detail_info['pd_amount']);
                $detail_array['rcb_amount'] = ncPriceFormat($detail_info['rcb_amount']);
                $detail_array['refund_state'] = $this->refund_detail_state[$detail_info['refund_state']];
            }
            output_data(array('refund' => $refund, 'pic_list' => $pic_list, 'detail_array' => $detail_array));
        } else {
            output_error('参数错误');
        }
    }
}
