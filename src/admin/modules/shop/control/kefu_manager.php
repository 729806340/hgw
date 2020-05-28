<?php
/**
 * 平台客观咨询管理
 *
 * @汉购网提供技术支持 授权请购买shopnc授权
 * @license    http://www.hangowa.com
 * @link       交流群号：
 */



defined('ByShopWWI') or exit('Access Invalid!');

class kefu_managerControl extends SystemControl{
    public function __construct()
    {
        parent::__construct();
        import('function.statistics');
        import('function.datehelper');
        $model = Model('stat');
        //存储参数
        $this->search_arr = $_REQUEST;
        $this->search_arr = $model->dealwithSearchTime($this->search_arr);
        //获得系统年份
        $year_arr = getSystemYearArr();
        //获得系统月份
        $month_arr = getSystemMonthArr();
        //获得本月的周时间段
        $week_arr = getMonthWeekArr($this->search_arr['week']['current_year'], $this->search_arr['week']['current_month']);
        Tpl::output('year_arr', $year_arr);
        Tpl::output('month_arr', $month_arr);
        Tpl::output('week_arr', $week_arr);
        Tpl::output('search_arr', $this->search_arr);
    }


    /**
     * 客服管理
     */
    public function indexOp()
    {
        $search_type = $_GET['search_type'] ? $_GET['search_type'] : 'day';
        Tpl::output('search_type', $search_type);
        Tpl::setDirquna('shop');/*网 店 运 维shop wwi.com*/
        Tpl::showpage('kefu_manager.index');
    }

    public function member_listOp()
    {
        $kefu_id = intval($_GET['kefu_id']);

        if ($kefu_id < 1) {
            showMessage(L('param_error'));
        }
        $model_admin = Model('admin');
        $kefu_info = $model_admin->getOneAdmin($kefu_id);
        Tpl::output('kefu_info', $kefu_info);
        Tpl::setDirquna('shop');/*网 店 运 维shop wwi.com*/
        Tpl::showpage('kefu_manager.member_list');
    }

    /**
     * 客服管理
     */
    public function get_xmlOp(){
        $condition = array();
        $condition['admin_gid'] = 2;
        $model_admin = Model('admin');
        /** @var orderModel $model_order */
        $model_order = Model('order');
        $model_refund = Model('refund_return');
        $kefu_list = $model_admin->getAdminList($condition);
        $data = array();
        $data['now_page'] = 1;
        $data['total_num'] = count($kefu_list);
        $w = array();
        $searchtime_arr = Model('stat')->getStarttimeAndEndtime($this->search_arr);
        $stime = $searchtime_arr[0];
        $etime = $searchtime_arr[1];
        $w['add_time'] = array('between', array($stime, $etime));
        //var_dump($w);exit;
        /** @var memberModel $modelMember */
        $modelMember = Model('member');
        foreach ($kefu_list as $kefu_info) {
            // 找到客服下的用户

            $member = $modelMember->getMembersList(array('kefu_id' => $kefu_info['admin_id']),null,'member_id','member_id');
            $order_count = 0;
            $order_amount = '0.00';
            $refund_count = 0;
            $refund_amount = '0.00';
            if (!empty($member)) {
                $member_ids = array_column($member, 'member_id');
                $w['buyer_id'] = array('in', $member_ids);
                // 销售订单数：当前客服管理的全部用户在指定周期内的下单数量，订单状态大于10
                $w['order_state'] = array('gt', 10);
                $order_count = $model_order->getOrderCount($w);
                // 销售订单金额：当前客服管理的全部用户在指定周期内的下单金额，订单状态大于10
                $order_amount = $model_order->getOrderAmount($w);
                $order_amount = $order_amount?$order_amount:'0.00';

                // 退款单数：：当前客服管理的全部用户在指定周期内的退单数量，退单seller_state小于3
                unset($w['order_state']);
                $w['seller_state'] = array('in', array('lt', 3));
                $refund_count = $model_refund->getRefundReturnCount($w);
                // 退款单金额：：当前客服管理的全部用户在指定周期内的退单金额，退单seller_state小于3
                $refund_amount = $model_refund->getRefundReturnAmount($w);
                $refund_amount = $refund_amount?$refund_amount:'0.00';
                unset($w['seller_state']);
            }

            $list = array();
            $list['operation'] = "<a class='btn green' href=\"index.php?act=kefu_manager&op=add_member&kefu_id={$kefu_info['admin_id']}\"><i class=\"fa fa-trash-o\"></i>添加用户</a>";
            $list['operation'] .= "<a class='btn green' href=\"index.php?act=kefu_manager&op=member_list&kefu_id={$kefu_info['admin_id']}\"><i class=\"fa fa-trash-o\"></i>用户列表</a>";
            $list['admin_name'] = $kefu_info['admin_name'];
            $list['order_count'] = $order_count;
            $list['order_amount'] = $order_amount;
            $list['refund_count'] = $refund_count;
            $list['refund_amount'] = $refund_amount;
            $data['list'][$kefu_info['admin_id']] = $list;
        }
        exit(Tpl::flexigridXML($data));
    }

    public function get_member_list_xmlOp()
    {
        $kefu_id = intval($_GET['kefu_id']);

        if ($kefu_id < 1) {
            showMessage(L('param_error'));
        }
        $condition = array();
        $condition['kefu_id'] = $kefu_id;
        $model_member = Model('member');
        $member_list = $model_member->getMemberList($condition, '*', $_POST['rp']);
        $data = array();
        $data['now_page'] = $model_member->shownowpage();
        $data['total_num'] = $model_member->gettotalnum();
        foreach ($member_list as $member_info) {
            $list = array();
            $list['operation'] = "<a class='btn red' onclick=\"fg_delete({$member_info['member_id']})\"><i class=\"fa fa-trash-o\"></i>删除</a>";
            $list['member_name'] = $member_info['member_name'];
            $data['list'][$member_info['member_id']] = $list;
        }
        exit(Tpl::flexigridXML($data));
    }

    /**
     * 回复咨询
     */
    public function add_memberOp() {
        $model_admin = Model('admin');
        $model_member = Model('member');
        if (chksubmit()) {
            $kefu_id = intval($_POST['kefu_id']);
            $member_ids = trim($_POST['member_ids']);
            $member_ids = trim($member_ids, ',');
            if ($kefu_id <= 0 || $member_ids == '') {
                showMessage(L('param_error'));
            }
            // 用户id处理
            if (strpos($member_ids, ',') !== false) {
                $member_ids = explode(',', $member_ids);
            } else {
                $member_ids = array($member_ids);
            }

            $update['kefu_id'] = $kefu_id;
            $result = $model_member->editMember(array('member_id' => array('in', $member_ids)), $update);
            if ($result) {
                showMessage('添加成功', urlAdminShop('kefu_manager', 'index'));
            } else {
                showMessage('添加失败');
            }
        }
        $kefu_id = intval($_GET['kefu_id']);
        if ($kefu_id <= 0) {
            showMessage(L('param_error'));
        }

        $kefu_info = $model_admin->getOneAdmin($kefu_id);
        $member = $model_member->getMembersList(array('kefu_id' => $kefu_id));
        $member_ids = '';
        if (!empty($member)) {
            foreach ($member as $k => $v) {
                $member_ids .= $v['member_id'] . ',';
            }
            $member_ids = trim($member_ids, ',');
        }
        Tpl::output('kefu_info', $kefu_info);
        Tpl::output('member_ids', $member_ids);
		Tpl::setDirquna('shop');/*网 店 运 维shop wwi.com*/
        Tpl::showpage('kefu_manager.add_member');
    }

    /**
     * 删除平台客服下的用户
     */
    public function del_memberOp(){
        if (preg_match('/^[\d,]+$/', $_GET['del_id'])) {
            $model_member = Model('member');
            $_GET['del_id'] = explode(',', trim($_GET['del_id'],','));
            $update['kefu_id'] = 0;
            $result = $model_member->editMember(array('member_id' => array('in', $_GET['del_id'])), $update);
            if ($result) {
                showMessage(Language::get('nc_common_del_succ'));
            } else {
                showMessage(Language::get('nc_common_del_fail'));
            }
        }
        showMessage(Language::get('nc_common_del_fail'));
    }
}
