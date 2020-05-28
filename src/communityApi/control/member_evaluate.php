<?php
/**
 * 会员评价
 *
 * @copyright  Copyright (c) 2007-2015 ShopNC Inc. (http://www.shopnc.net)
 * @license    http://www.shopnc.net
 * @link       http://www.shopnc.net
 * @since      File available since Release v1.1
 */



defined('ByShopWWI') or exit('Access Invalid!');

class member_evaluateControl extends mobileMemberControl {

    public function __construct(){
        parent::__construct();
    }

    /**
     * 评论
     */
    public function indexOp() {
        $order_id = intval($_POST['order_id']);
        /** @var member_evaluateLogic $member_evaluate_logic */
        $member_evaluate_logic = Logic('member_evaluate');
        $return = $member_evaluate_logic->validation($order_id, $this->member_info['member_id']);
        if (!$return['state']) {
            output_error($return['msg']);
        }
        $store_info = $return['data']['store_info'];
        $order_goods = $return['data']['order_goods'];
        $store = array();
        $store['store_id'] = $store_info['store_id'];
        $store['store_name'] = $store_info['store_name'];
        $store['is_own_shop'] = $store_info['is_own_shop'];
        $order_goods_filter = array();
        foreach($order_goods as $k=>$v){
            $order_goods_filter[$k]['rec_id'] = $v['rec_id'];
            $order_goods_filter[$k]['goods_id']= $v['goods_id'];
            $order_goods_filter[$k]['goods_name']= $v['goods_name'];
            $order_goods_filter[$k]['goods_image_url'] = $v['goods_image_url'];
        }
        output_data(array('store_info' => $store, 'order_goods' => $order_goods_filter));
    }
    
    /**
     * 评论保存
     */
    public function saveOp() {
        $order_id = intval($_POST['order_id']);
        $_POST['goods_evaluate']=htmlspecialchars_decode($_POST['goods_evaluate']);
        $goods_evaluate = @json_decode($_POST['goods_evaluate'],true);
        if(is_array($goods_evaluate)){
            $data = array();
            unset($_POST['goods_evaluate']);
            $goods_evaluate = array_under_reset($goods_evaluate, 'rec_id');
            $data['goods'] = $goods_evaluate;
            $datas = array_merge($data , $_POST);
        }else{
            output_error('参数错误');
        }
        $return = Logic('member_evaluate')->validation($order_id, $this->member_info['member_id']);
        if (!$return['state']) {
            output_error($return['msg']);
        }
        $order_info = $return['data']['order_info'];
        $store_info = $return['data']['store_info'];
        $order_goods = $return['data']['order_goods'];
        $return = Logic('member_evaluate')->save($datas, $order_info, $store_info, $order_goods, $this->member_info['member_id'], $this->member_info['member_name']);
        if(!$return['state']) {
            output_data($return['msg']);
        } else {
            output_data('1');
        }
    }
    
    /**
     * 追评
     */
    public function againOp() {
        $order_id = intval($_GET['order_id']);
        $return = Logic('member_evaluate')->validationAgain($order_id, $this->member_info['member_id']);
        if (!$return['state']) {
            output_error($return['msg']);
        }
        $store_info = $return['data']['store_info'];
        $evaluate_goods = $return['data']['evaluate_goods'];
        $store = array();
        $store['store_id'] = $store_info['store_id'];
        $store['store_name'] = $store_info['store_name'];
        $store['is_own_shop'] = $store_info['is_own_shop'];
        output_data(array('store_info' => $store, 'evaluate_goods' => $evaluate_goods));
    }

    /**
     * 追加评价保存
     */
    public function save_againOp() {
       $order_id = intval($_GET['order_id']);
        $return = Logic('member_evaluate')->validationAgain($order_id, $this->member_info['member_id']);
        if (!$return['state']) {
            output_error($return['msg']);
        }
        $store_info = $return['data']['store_info'];
        $evaluate_goods = $return['data']['evaluate_goods'];
        $store = array();
        $store['store_id'] = $store_info['store_id'];
        $store['store_name'] = $store_info['store_name'];
        $store['is_own_shop'] = $store_info['is_own_shop'];
        output_data(array('store_info' => $store, 'evaluate_goods' => $evaluate_goods));
    }
    
    /**
     * 虚拟订单评价
     */
    public function vrOp() {
        $order_id = intval($_GET['order_id']);
        $return = Logic('member_evaluate')->validationVr($order_id, $this->member_info['member_id']);
        if (!$return['state']) {
            output_error($return['msg']);
        }
        $order_info = $return['data']['order_info'];
        output_data(array('order_info' => $order_info));
    }
    
    /**
     * 虚拟订单评价保存
     */
    public function save_vrOp() {
        $order_id = intval($_POST['order_id']);
        $return = Logic('member_evaluate')->validationVr($order_id, $this->member_info['member_id']);
        if (!$return['state']) {
            output_error($return['msg']);
        }
        $order_info = $return['data']['order_info'];
        $store_info = $return['data']['store_info'];
        $return = Logic('member_evaluate')->saveVr($_POST, $order_info, $store_info, $this->member_info['member_id'], $this->member_info['member_name']);
        if(!$return['state']) {
            output_data($return['msg']);
        } else {
            output_data('1');
        }
    }
}
