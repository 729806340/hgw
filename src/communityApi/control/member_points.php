<?php
/**
 * 我的代金券
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

class member_pointsControl extends mobileMemberControl
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 签到列表
     */
    public function pointslogOp()
    {

        $condition_arr = $list_log = array();
        $condition_arr['pl_memberid'] = $this->member_info['member_id'];
        //分页
        $page = new Page();
        /** @var pointsModel $points_model */
        $points_model = Model('points');
        // pl_memberid 比较慢 需加索引
        $list_log = $points_model->getPointsLogList($condition_arr, $page, '*', '');
        if (!empty($list_log)) {
            foreach ($list_log as $key => $value) {
                $list_log[$key]['stagetext'] = $this->insertarr($value['pl_stage']);
                $list_log[$key]['addtimetext'] = date('Y-m-d H:i:s', $value['pl_addtime']);
            }
        }

        $page_count = $page->gettotalpage();
        if (intval($_GET['curpage']) > $page_count) $list_log = array();
        output_data(array('log_list' => $list_log, 'points' => $this->member_info['member_points']), mobile_page($page_count));

    }

    private function insertarr($stage)
    {
        switch ($stage) {
            case 'regist':
                $insertarr = '注册会员';
                break;
            case 'login':
                $insertarr = '会员登录';
                break;
            case 'comments':
                $insertarr = '评论商品';
                break;
            case 'order':
                $insertarr = '购物消费';
                break;
            case 'pointorder':
                $insertarr = '兑换礼品';
                break;
            case 'signin':
                $insertarr = '会员签到';
                break;
        }
        return $insertarr;
    }

    /**
     * 检验是否能签到
     */
    public function checksigninOp()
    {
        $condition = array();
        $condition['pl_memberid'] = $this->member_info['member_id'];
        $condition['pl_stage'] = 'signin';
        $todate = date('Ymd000000');
        $totime = strtotime($todate);
        $condition['pl_addtime'][] = array('egt', $totime);
        $condition['pl_addtime'][] = array('elt', $totime + 86400);
        $points_model = Model('points');
        $log_array = $points_model->getPointsInfo($condition);
        if (!empty($log_array)) {
            output_error('已签到');
        } else {
            $points_signin = intval(C('points_signin'));
            output_data(array('points_signin' => $points_signin));
        }
    }

    /**
     * 签到 array('pl_memberid'=>'会员编号','pl_membername'=>'会员名称','pl_adminid'=>'管理员编号','pl_adminname'=>'管理员名称','pl_points'=>'积分','pl_desc'=>'描述','orderprice'=>'订单金额','order_sn'=>'订单编号','order_id'=>'订单序号','point_ordersn'=>'积分兑换订单编号');
     */
    public function signin_addOp()
    {
        $points_signin = intval(C('points_signin'));//签到对得积分数
        $points_model = Model('points');
        $insertarr['pl_memberid'] = $this->member_info['member_id'];
        $insertarr['pl_membername'] = $this->member_info['member_name'];
        $insertarr['pl_points'] = $points_signin;
        $insertarr['pl_points'] = $points_signin;
        $return = $points_model->savePointsLog('signin', $insertarr, false);

        output_data(array('point' => $return));
    }

    public function add_points_giftcartOp()
    {
        //$_POST = array('pgid'=>3 , 'quantity'=>2);
        $pgid = intval($_POST['pgid']);
        $quantity = intval($_POST['quantity']);
        if ($pgid <= 0 || $quantity <= 0) {
            output_error('非法参数');
        }
        //验证积分礼品是否存在购物车中
        $model_pointcart = Model('pointcart');
        $check_cart = $model_pointcart->getPointCartInfo(array('pgoods_id' => $pgid, 'pmember_id' => $this->member_info['member_id']));
        if (!empty($check_cart)) {
            output_data('加入兑换购物车成功');
        }
        //验证是否能兑换
        $data = $model_pointcart->checkExchange($pgid, $quantity, $this->member_info['member_id']);
        if (!$data['state']) {
            output_error($data['msg']);
        }
        $prod_info = $data['data']['prod_info'];

        $insert_arr = array();
        $insert_arr['pmember_id'] = $this->member_info['member_id'];
        $insert_arr['pgoods_id'] = $prod_info['pgoods_id'];
        $insert_arr['pgoods_name'] = $prod_info['pgoods_name'];
        $insert_arr['pgoods_points'] = $prod_info['pgoods_points'];
        $insert_arr['pgoods_choosenum'] = $prod_info['quantity'];
        $insert_arr['pgoods_image'] = $prod_info['pgoods_image_old'];
        $model_pointcart->addPointCart($insert_arr);
        output_data('加入兑换购物车成功');
    }

    //购物车数据
    public function cart_infoOp()
    {
        $where['pmember_id'] = $this->member_info['member_id'];
        $data = Model('pointcart')->getPCartListAndAmount($where);
        output_data($data['data']);
    }

    //编辑购物车礼品数量
    public function cart_editOp()
    {
        $pc_id = intval($_POST['pcart_id']);
        $quantity = intval($_POST['quantity']);
        if (!$pc_id || !$quantity) output_error('参数错误');
        $member_id = $this->member_info['member_id'];
        $cart = Model('pointcart');
        $where['pmember_id'] = $member_id;
        $where['pcart_id'] = $pc_id;
        $info = $cart->getPointCartInfo($where);
        if (empty($info['pgoods_id'])) output_error('未查询到该记录');
        $check = $cart->checkExchange($info['pgoods_id'], $quantity, $member_id);
        if (!$check['state']) output_error($check['msg']);
        if ($cart->editPointCart($where, array('pgoods_choosenum' => $quantity))) {
            $this->cart_infoOp();//返回最新购物车数据
        } else {
            output_error('操作失败');
        }
    }

    //删除购物车里单个礼品
    public function cart_delOp()
    {
        $pc_id = intval($_POST['pcart_id']);
        if (!$pc_id) output_error('参数错误');
        if (Model('pointcart')->delPointCartById($pc_id, $this->member_info['member_id'])) {
            $this->cart_infoOp();//返回最新购物车数据
        } else {
            output_error('操作失败');
        }
    }

    //清理购物车
    public function cart_clearOp()
    {
        $where['pmember_id'] = $this->member_info['member_id'];
        if (Model('pointcart')->delPointCart($where, $where['pmember_id'])) {
            output_data('操作成功');
        } else {
            output_error('操作失败');
        }
    }

    //兑换订单流程第一步
    public function sub_oneOp()
    {
        $member_id = $this->member_info['member_id'];
        //获取符合条件的兑换礼品和总积分
        $data = Model('pointcart')->getCartGoodsList($member_id);
        if (!$data['state']) {
            output_error($data['msg']);
        }
        $pointprod_list = $data['data']['pointprod_list'];
        $cartgoods_list = array();
        foreach ($pointprod_list as $v) {
            $item['pgoods_id'] = $v['pgoods_id'];
            $item['pgoods_name'] = $v['pgoods_name'];
            $item['pgoods_price'] = $v['pgoods_price'];
            $item['pgoods_points'] = $v['pgoods_points'];
            $item['pgoods_image'] = $v['pgoods_image'];
            $item['pgoods_choosenum'] = $v['quantity'];
            $item['pgoods_pointone'] = $v['onepoints'];
            $cartgoods_list[] = $item;
        }
        //实例化收货地址模型（不显示自提点地址）
        $address_list = Model('address')->getAddressList(array('member_id' => $member_id, 'dlyp_id' => 0), 'is_default desc,address_id desc');
        $output['cartgoods_list'] = $cartgoods_list;
        $output['cartgoods_pointall'] = $data['data']['pgoods_pointall'];
        $output['address_list'] = $address_list;
        output_data($output);
    }

    //兑换订单流程第二步
    public function sub_twoOp()
    {
        $params['address_options'] = $_POST['address_id'];//收货地址
        $params['pcart_message'] = $_POST['message'];//订单留言
        $member_id = $this->member_info['member_id'];
        $model_pointcart = Model('pointcart');
        //获取符合条件的兑换礼品和总积分
        $data = $model_pointcart->getPCartListAndAmount($member_id);
        if (!$data['state']) {
            output_error($data['msg']);
        }
        $pointprod_arr = $data['data'];
        unset($data);

        //验证积分数是否足够
        $data = $model_pointcart->checkPointEnough($pointprod_arr['pgoods_pointall'], $member_id);
        if (!$data['state']) {
            output_error($data['msg']);
        }
        unset($data);

        //创建兑换订单
        $data = Model('pointorder')->createOrder($params, $pointprod_arr, $this->member_info);
        if (!$data['state']) {
            output_error($data['msg']);
        }
        output_data($data['data']);
    }

    //第三步获取订单信息
    public function sub_threeOp()
    {
        $order_id = intval($_POST['order_id']);
        if ($order_id <= 0) {
            output_error('参数错误');
        }
        $where = array();
        $where['point_orderid'] = $order_id;
        $where['point_buyerid'] = $this->member_info['member_id'];
        $order_info = Model('pointorder')->getPointOrderInfo($where);
        if (!$order_info) {
            output_error('未查询到记录');
        }
        output_data($order_info);
    }


}
