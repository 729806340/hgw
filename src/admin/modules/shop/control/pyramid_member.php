<?php
/**
 * 分销会员管理
 *
 */
defined('ByShopWWI') or exit('Access Invalid!');

class pyramid_memberControl extends SystemControl{

    const EXPORT_SIZE = 1000;
    public function __construct(){
        parent::__construct();
        Language::read('member');
    }

    /**
     * 分销会员管理
     */
    public function indexOp() {
        Tpl::setDirquna('shop');
        Tpl::showpage('pyramid_member.index');
    }

    /**
     * 输出XML数据
     */
    public function get_xmlOp() {
        /** @var memberModel $model_member */
        $model_member = Model('member');
        $condition = array(
            'invite_shop_name' => array('exp',"invite_shop_name != ''"),
        );
        if ($_POST['query'] != '') {
            $condition[$_POST['qtype']] = array('like', '%' . $_POST['query'] . '%');
        }
        $order = '';
        $param = array('member_id','member_name','member_avatar','member_email','member_mobile','member_sex','member_truename','member_birthday'
        ,'member_time','member_login_time','member_login_ip','member_points','invite_one','invite_two','invite_three'
        );
        if (in_array($_POST['sortname'], $param) && in_array($_POST['sortorder'], array('asc', 'desc'))) {
            $order = $_POST['sortname'] . ' ' . $_POST['sortorder'];
        }
        $page = $_POST['rp'];
        $member_list = $model_member->getMemberList($condition, '*', $page, $order);
        $data = array();
        $data['now_page'] = $model_member->shownowpage();
        $data['total_num'] = $model_member->gettotalnum();
        foreach ($member_list as $value) {
            $param = array();
            $param['operation'] = "<a class='btn blue' target='_blank' href='index.php?act=member&op=member_view&member_id=" . $value['member_id'] . "'><i class='fa fa-pencil-square-o'></i>查看</a>";
            $param['member_id'] = $value['member_id'];
            $param['member_name'] = "<img src=".getMemberAvatarForID($value['member_id'])." class='user-avatar' onMouseOut='toolTip()' onMouseOver='toolTip(\"<img src=".getMemberAvatarForID($value['member_id']).">\")'>".$value['member_name'];
            $param['member_mobile'] = $value['member_mobile'];
            $param['member_time'] = date('Y-m-d', $value['member_time']);
            $param['member_login_time'] = date('Y-m-d', $value['member_login_time']);
            $param['member_login_ip'] = $value['member_login_ip'];
            $param['member_points'] = $value['member_points'];
            $param['invite_one'] = $value['invite_one'];
            $param['invite_two'] = $value['invite_two'];
            $param['invite_three'] = $value['invite_three'];
            $data['list'][$value['member_id']] = $param;
        }
        echo Tpl::flexigridXML($data);
        exit();
    }


    /**
     * csv导出
     */
    public function export_csvOp() {
        /** @var memberModel $model_member */
        $model_member = Model('member');
        $condition = array(
            'invite_shop_name' => array('exp',"invite_shop_name != ''"),
        );
        $limit = false;
        if ($_GET['id'] != '') {
            $id_array = explode(',', $_GET['id']);
            $condition['member_id'] = array('in', $id_array);
        }
        if ($_GET['query'] != '') {
            $condition[$_GET['qtype']] = array('like', '%' . $_GET['query'] . '%');
        }
        $order = '';
        $param = array('member_id','member_name','member_avatar','member_email','member_mobile','member_sex','member_truename','member_birthday'
        ,'member_time','member_login_time','member_login_ip','member_points','invite_one','invite_two','invite_three'
        );
        if (in_array($_GET['sortname'], $param) && in_array($_GET['sortorder'], array('asc', 'desc'))) {
            $order = $_GET['sortname'] . ' ' . $_GET['sortorder'];
        }
        if (!is_numeric($_GET['curpage'])){
            $count = $model_member->getMemberCount($condition);
            if ($count > self::EXPORT_SIZE ){   //显示下载链接
                $array = array();
                $page = ceil($count/self::EXPORT_SIZE);
                for ($i=1;$i<=$page;$i++){
                    $limit1 = ($i-1)*self::EXPORT_SIZE + 1;
                    $limit2 = $i*self::EXPORT_SIZE > $count ? $count : $i*self::EXPORT_SIZE;
                    $array[$i] = $limit1.' ~ '.$limit2 ;
                }
                Tpl::output('list',$array);
                Tpl::output('murl','index.php?act=pyramid_member&op=index');
                Tpl::setDirquna('shop');/*网 店 运 维shop wwi.com*/
                Tpl::showpage('export.excel');
                exit();
            }
        } else {
            $limit1 = ($_GET['curpage']-1) * self::EXPORT_SIZE;
            $limit2 = self::EXPORT_SIZE;
            $limit = $limit1 .','. $limit2;
        }

        $member_list = $model_member->getMemberList($condition, '*', null, $order, $limit);
        $this->createCsv($member_list);
    }
    /**
     * 生成csv文件
     * @param $member_list
     */
    private function createCsv($member_list) {
        $data = array();
        foreach ($member_list as $value) {
            $param = array();
            $param['member_id'] = $value['member_id'];
            $param['member_name'] = $value['member_name'];
            $param['member_mobile'] = $value['member_mobile'];
            $param['member_time'] = date('Y-m-d', $value['member_time']);
            $param['member_login_time'] = date('Y-m-d', $value['member_login_time']);
            $param['member_login_ip'] = $value['member_login_ip'];
            $param['member_points'] = $value['member_points'];
            $param['invite_one'] = $value['invite_one'];
            $param['invite_two'] = $value['invite_two'];
            $param['invite_three'] = $value['invite_three'];
            $data[$value['member_id']] = $param;
        }

        $header = array(
            'member_id' => '会员ID',
            'member_name' => '会员名称',
            'member_mobile' => '会员手机',
            'member_time' => '注册时间',
            'member_login_time' => '最后登录时间',
            'member_login_ip' => '最后登录IP',
            'member_points' => '会员积分',
            'invite_one' => '分销一级会员id',
            'invite_two' => '分销二级会员id',
            'invite_three' => '分销三级会员id',
        );
        array_unshift($data, $header);
        $csv = new Csv();
        $export_data = $csv->charset($data,CHARSET,'GBK');
        $csv->filename = $csv->charset('member_list',CHARSET).$_GET['curpage'] . '-'.date('Y-m-d');
        $csv->export($export_data);
    }
}
