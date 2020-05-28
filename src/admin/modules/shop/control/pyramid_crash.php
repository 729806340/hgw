<?php
/**
 * 提现管理
 */
defined('ByShopWWI') or exit('Access Invalid!');

class pyramid_crashControl extends SystemControl {

    //定义状态常量
    const STATE_NEW = 10;//待审核
    const STATE_HANDLE = 20;//待付钱
    const STATE_FINISH = 30;//提现完成
    const STATE_OUT = 40;//拒绝

    private $links = array(
        array('url'=>'act=pyramid_crash&op=appeal_list','text'=>'待审核'),
        array('url'=>'act=pyramid_crash&op=handle_list','text'=>'待付钱'),
        array('url'=>'act=pyramid_crash&op=finish_list','text'=>'已完成'),
        array('url'=>'act=pyramid_crash&op=out_list','text'=>'已拒绝'),
    );

    public function __construct() {
        parent::__construct();
        Language::read('complain');
        if ($_GET['op'] == 'index') $_GET['op'] = 'appeal_list';
        Tpl::output('top_link',$this->sublink($this->links,$_GET['op']));
    }

    /*
     * 默认操作列出待审核列表
     */
    public function indexOp() {
        $this->appeal_listOp();
    }

    /*
     * 待审核提现列表
     */
    public function appeal_listOp() {
        $this->get_pyramid_crash_list(self::STATE_NEW);
    }

    /*
     * 待付钱提现列表
     */
    public function handle_listOp() {
        $this->get_pyramid_crash_list(self::STATE_HANDLE);
    }

    /*
     * 已完成提现列表
     */
    public function finish_listOp() {
        $this->get_pyramid_crash_list(self::STATE_FINISH);
    }

    /*
     * 已拒绝提现列表
     */
    public function out_listOp() {
        $this->get_pyramid_crash_list(self::STATE_OUT);
    }

    /*
     * 获取提现列表
     *
     * @param $state
     */
    private function get_pyramid_crash_list($state) {
        $_GET['state'] = $state;
		Tpl::setDirquna('shop');
        Tpl::showpage('pyramid_crash.list');
    }

    /*
     * 获取提现列表
    */
    public function get_new_xmlOp() {
        $page = new Page();
        $page->setEachNum(intval($_POST['rp']));
        $page->setStyle('admin') ;
        /** @var pyramid_out_crashModel $model_pyramid_crash_out */
        $model_pyramid_crash_out = Model('pyramid_out_crash');
        $condition = array();
        $pyramid_state = intval($_GET['state']);
        if (!in_array($pyramid_state, array(10, 20, 30, 40))) {
            $pyramid_state = 10;
        }

        if (!empty($_POST['qtype']) && !empty($_POST['query'])) {
            $condition[$_POST['qtype']] = array('like', '%' . $_POST['query'] . '%');
        }
        if ($pyramid_state == 10) {
            $condition['cash_check_state'] = 1;
        } elseif ($pyramid_state == 20) {
            $condition['cash_check_state'] = 3;
            $condition['pay_state'] = array('in', array(1, 2));
        } elseif ($pyramid_state == 30) {
            $condition['cash_check_state'] = 3;
            $condition['pay_state'] = 3;
        } else {
            $condition['cash_check_state'] = 2;
        }
        $crash_list = $model_pyramid_crash_out->getPyramidOutCrashList($condition,$page);
        if (empty($crash_list)) $crash_list = array();
        $data = array();
        $data['now_page'] = $model_pyramid_crash_out->shownowpage();
        $data['total_num'] = $model_pyramid_crash_out->gettotalnum();
        $member_ids = array_column($crash_list, 'own_member_id');
        $member_arr = array();
        if (!empty($member_ids)) {
            /** @var memberModel $model_member */
            $model_member = Model('member');
            $member_arr = $model_member->getMemberList(array('member_id' => array('in', $member_ids)));
            $member_arr = array_under_reset($member_arr, 'member_id');
        }
        foreach ($crash_list as $k => $crash_info) {
            $member_info = $member_arr[$crash_info['own_member_id']];
            $list = array();
            $list['operation'] = "";
            if ($crash_info['cash_check_state'] == 1) {
                $list['operation'] .= "<a class=\"btn orange\" href=\"index.php?act=pyramid_crash&op=refuse_apply&out_crash_id={$crash_info['out_crash_id']}\"><i class=\"fa fa-gavel\"></i>拒绝</a>";
                $list['operation'] .= "<a class=\"btn orange\" href=\"index.php?act=pyramid_crash&op=agree_apply&out_crash_id={$crash_info['out_crash_id']}\"><i class=\"fa fa-gavel\"></i>同意</a>";
            }
            $list['crash_user_name'] = $member_info['member_name'];
            $list['own_member_id'] = $crash_info['own_member_id'];
            $list['out_crash_number'] = $crash_info['out_crash_number'];
            $list['cash_check_state'] = ($crash_info['cash_check_state'] == 1) ? '处理中' : (($crash_info['cash_check_state'] == 2) ? '已拒绝' : '已同意');
            $list['pay_state'] = ($crash_info['pay_state'] == 1) ? '未付款' : (($crash_info['pay_state'] == 2) ? '失败' : '成功');
            $list['add_time'] = date('Y-m-d H:i:s',$crash_info['add_time']);
            $list['out_crash_money'] = $crash_info['out_crash_money'];
            $data['list'][$crash_info['out_crash_id']] = $list;
        }
        Tpl::flexigridXML($data);
        exit();

    }

    //拒绝申请
    public function refuse_applyOp() {
        $out_crash_id = intval($_GET['out_crash_id']);
        /** @var PyramidService $PyramidService */
        $PyramidService = Service("Pyramid");
        $result = $PyramidService->out_cash_refuse($out_crash_id);
        if (!$result['state']) {
            showMessage($result['msg']);
        }
        showMessage("已拒绝");
        exit;
    }

    //同意 发红包
    public function agree_applyOp() {
        $out_crash_id = intval($_GET['out_crash_id']);
        /** @var PyramidService $PyramidService */
        $PyramidService = Service("Pyramid");
        $result = $PyramidService->out_cash_support($out_crash_id);
        showMessage($result['msg']);
    }
}
