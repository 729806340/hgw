<?php
/**
 * 会员管理
 *
 *
 *
 *
 * @汉购网提供技术支持 授权请购买shopnc授权
 * @license    http://www.hangowa.com
 * @link       交流群号：
 */



defined('ByShopWWI') or exit('Access Invalid!');

class member_fenxiaoControl extends SystemControl{
    const EXPORT_SIZE = 1000;
    public function __construct(){
        parent::__construct();
        Language::read('member');
    }

    public function indexOp() {
        Tpl::setDirquna('shop');/*网 店 运 维shop wwi.com*/
        Tpl::showpage('member_fenxiao_channel.index');
    }

    public function get_member_fenxiao_channel_xmlOp()
    {
        $page = $_POST['rp'];
        $member_cn_code = $_POST['query'];

        $order = '';
        $param = array('member_id', 'member_cn_code', 'is_sign');
        if (in_array($_POST['sortname'], $param) && in_array($_POST['sortorder'], array('asc', 'desc'))) {
            $order = $_POST['sortname'] . ' ' . $_POST['sortorder'];
        }

        $conditions = array();
        if (!empty($member_cn_code))
            $conditions['member_cn_code'] = array('like', '%' . $member_cn_code . '%');

        $model_member = Model('member_fenxiao');
        $member_fenxiao_list = $model_member->getMembeFenxiaoList2($conditions, '*', $page, $order);
        $store_id = array_column($member_fenxiao_list, 'filter_store_id');
        $store_list = Model('store')->getStoreList(array('store_id' => array('in', $store_id)));
        $store_list = array_under_reset($store_list, 'store_id');

        $data = array();
        $data['now_page'] = $model_member->shownowpage();
        $data['total_num'] = $model_member->gettotalnum();
        foreach ($member_fenxiao_list as $value) {
            $param = array();
            $param['operation'] = "<a class='btn blue' href='index.php?act=member_fenxiao&op=member_edit2&id=" . $value['id'] . "'><i class='fa fa-pencil-square-o'></i>编辑</a>";
            $param['member_id'] = $value['member_id'];
            $param['member_cn_code'] = $value['member_cn_code'];
            $param['is_sign'] = $value['is_sign'] ==  '1' ? '<span class="yes"><i class="fa fa-check-circle"></i>是</span>' : '<span class="no"><i class="fa fa-ban"></i>否</span>';
            $param['store_name'] = $store_list[$value['filter_store_id']]['store_name'] ? $store_list[$value['filter_store_id']]['store_name'] : '汉购网';
            $data['list'][$value['member_id']] = $param;
        }
        echo Tpl::flexigridXML($data);exit();
    }

    /**
     * 会员管理
     */
    public function member_fenxiaoOp(){
		Tpl::setDirquna('shop');/*网 店 运 维shop wwi.com*/
        Tpl::showpage('member_fenxiao.index');
    }


    /**
     * 会员查看
     */
    public function member_viewOp(){
        $lang   = Language::getLangContent();
        $model_member = Model('member');
        
        $condition['member_id'] = intval($_GET['member_id']);
        $member_array = $model_member->getMemberInfo($condition);

        $model_orders = Model('order');
        //下单次数
        $member_array['order_count'] = $model_orders->getOrderCount($condition);

        Tpl::output('member_array',$member_array);
        Tpl::setDirquna('shop');
        Tpl::showpage('member.view');
    }

    /**
     * 会员修改
     */
    public function member_editOp(){
        $lang   = Language::getLangContent();
        $model_member = Model('member');
        /**
         * 保存
         */
        if (chksubmit()){
            /**
             * 验证
             */
            $obj_validate = new Validate();
            $obj_validate->validateparam = array(
            array("input"=>$_POST["member_email"], "require"=>"true", 'validator'=>'Email', "message"=>$lang['member_edit_valid_email']),
            );
            $error = $obj_validate->validate();
            if ($error != ''){
                showMessage($error);
            }else {
                $update_array = array();
                $update_array['member_id']          = intval($_POST['member_id']);
                if (!empty($_POST['member_passwd'])){
                    $update_array['member_passwd'] = md5($_POST['member_passwd']);
                    /** @var memberModel $model_member */
                    $model_member   = Model('member');
                    $member_info = $model_member->getMemberInfo(array('member_id'=>$_POST['member_id']));
                    $update_array['member_passwd'] = passwordHash($_POST['member_passwd'],$member_info['password_salt'],$member_info['password_account'],$member_info['member_time']);
                }
                $update_array['member_email']       = $_POST['member_email'];
                $update_array['member_truename']    = $_POST['member_truename'];
                $update_array['member_sex']         = $_POST['member_sex'];
                $update_array['member_qq']          = $_POST['member_qq'];
                $update_array['member_ww']          = $_POST['member_ww'];
                $update_array['inform_allow']       = $_POST['inform_allow'];
                $update_array['is_buy']             = $_POST['isbuy'];
                $update_array['is_allowtalk']       = $_POST['allowtalk'];
                if (!empty($_POST['member_avatar'])){
                    $update_array['member_avatar'] = $_POST['member_avatar'];
                }
                $result = $model_member->editMember(array('member_id'=>intval($_POST['member_id'])),$update_array);
                if ($result){
                    $url = array(
                    array(
                    'url'=>'index.php?act=member&op=member',
                    'msg'=>$lang['member_edit_back_to_list'],
                    ),
                    array(
                    'url'=>'index.php?act=member&op=member_edit&member_id='.intval($_POST['member_id']),
                    'msg'=>$lang['member_edit_again'],
                    ),
                    );
                    $this->log(L('nc_edit,member_index_name').'[ID:'.$_POST['member_id'].']',1);
                    showMessage($lang['member_edit_succ'],$url);
                }else {
                    showMessage($lang['member_edit_fail']);
                }
            }
        }
        $condition['member_id'] = intval($_GET['member_id']);
        $member_array = $model_member->getMemberInfo($condition);

        Tpl::output('member_array',$member_array);
		Tpl::setDirquna('shop');/*网 店 运 维shop wwi.com*/
        Tpl::showpage('member.edit');
    }

    /**
     * 新增会员
     */
    public function member_addOp(){
        $lang   = Language::getLangContent();
        $model_member = Model('member');
        /**
         * 保存
         */
        if (chksubmit()){
            /**
             * 验证
             */
            $obj_validate = new Validate();
            $obj_validate->validateparam = array(
                array("input"=>$_POST["member_passwd"], "require"=>"true", "message"=>'密码不能为空')
            );
            $error = $obj_validate->validate();
            if ($error != ''){
                showMessage($error);
            }else {
                $res=Model('member_fenxiao')->getMemberIdByCode($_POST['member_en_code']);
                if($res){
                    showMessage("英文名已被使用,请重新设置!");
                    exit;
                }
                $res1=$model_member->getMemberInfo(array("member_name"=>$_POST['member_en_code']));
                if($res1){
                    showMessage("英文名已经使用了,请重新设置!");
                    exit;
                }
                $result = Model('member_fenxiao')->addFenxiao($_POST, $_POST['store_id']);
                if ($result){
                    //更新缓存信息
                    //Model('member_fenxiao')->writeCache();
                    $message = "您好！渠道新增：{$_POST['member_cn_code']},登录名：{$_POST['member_en_code']},请知悉。";
                    $email	= new Email();
                    $res = $email->send_sys_email('handong@hansap.com','渠道新增',$message);
                    if(!$res){
                        throw new Exception('分销会员邮件发送失败！');
                    }
                    $url = array(
                    array(
                        'url'=>'index.php?act=member_fenxiao&op=index',
                        'msg'=>$lang['member_add_again'],
                    ),
                        array(
                        'url'=>'index.php?act=member_fenxiao&op=index',
                        'msg'=>$lang['member_add_again'],
                    ),
                    );
                    $this->log(L('nc_add,member_index_name').'[ '.$_POST['member_name'].']',1);
                    showMessage($lang['member_add_succ'],$url);
                }else {
                    showMessage($lang['member_add_fail']);
                }
            }
        }
        // 获取汉购网门店
        $store_list = Model('store')->getStoreList(array('is_hango' => 1, 'store_state' => 1));
        Tpl::output('store_list', $store_list);
		Tpl::setDirquna('shop');/*网 店 运 维shop wwi.com*/
        Tpl::showpage('member_fenxiao.add');
    }

    public function member_edit2Op()
    {
        $lang   = Language::getLangContent();
        $id = intval($_GET['id']);
        /**
         * 保存
         */
        if (chksubmit()){
            $data['is_sign'] = $_POST['is_sign'];
            $data['billing_mode'] = $_POST['billing_mode'];
            $result = Model('member_fenxiao')->updates(array('id' => $id), $data);
            if ($result){
                $url = array(
                    array(
                        'url'=>'index.php?act=member_fenxiao&op=index',
                        'msg'=>$lang['member_add_again'],
                    ),
                    array(
                        'url'=>'index.php?act=member_fenxiao&op=index',
                        'msg'=>$lang['member_add_again'],
                    ),
                );
                showMessage('编辑成功', $url);
            }
        }
        // 获取该渠道
        $member_fenxiao = Model('member_fenxiao')->getMembeFenxiaoInfo(array('id' => $id));
        Tpl::output('member_fenxiao', $member_fenxiao);
        Tpl::setDirquna('shop');/*网 店 运 维shop wwi.com*/
        Tpl::showpage('member_fenxiao.edit');
    }

    /**
     * ajax操作
     */
    public function ajaxOp(){
        switch ($_GET['branch']){
            /**
             * 验证会员是否重复
             */
            case 'check_user_name':
                $model_member = Model('member');
                $condition['member_name']   = $_GET['member_name'];
                $condition['member_id'] = array('neq',intval($_GET['member_id']));
                $list = $model_member->getMemberInfo($condition);
                if (empty($list)){
                    echo 'true';exit;
                }else {
                    echo 'false';exit;
                }
                break;
                /**
             * 验证邮件是否重复
             */
            case 'check_email':
                $model_member = Model('member');
                $condition['member_email'] = $_GET['member_email'];
                $condition['member_id'] = array('neq',intval($_GET['member_id']));
                $list = $model_member->getMemberInfo($condition);
                if (empty($list)){
                    echo 'true';exit;
                }else {
                    echo 'false';exit;
                }
                break;
        }
    }

    /**
     * 输出XML数据
     */
    public function get_xmlOp() {
        $model_member = Model('member');
        $member_grade = $model_member->getMemberGradeArr();
        $condition = array();
        if ($_POST['query'] != '') {
            $condition[$_POST['qtype']] = array('like', '%' . $_POST['query'] . '%');
        }
        $order = '';
        $param = array('member_id','member_name','member_avatar','member_email','member_mobile','member_sex','member_truename','member_birthday'
                ,'member_time','member_login_time','member_login_ip','member_points','member_exppoints','member_grade','available_predeposit'
                ,'freeze_predeposit','available_rc_balance','freeze_rc_balance','inform_allow','is_buy','is_allowtalk','member_state'
        );
        if (in_array($_POST['sortname'], $param) && in_array($_POST['sortorder'], array('asc', 'desc'))) {
            $order = $_POST['sortname'] . ' ' . $_POST['sortorder'];
        }
        $page = $_POST['rp'];
        $condition['member_type'] = 'fenxiao';
        $member_list = $model_member->getMemberList($condition, '*', $page, $order);

        $sex_array = $this->get_sex();

        $data = array();
        $data['now_page'] = $model_member->shownowpage();
        $data['total_num'] = $model_member->gettotalnum();
        foreach ($member_list as $value) {
            $param = array();
            $param['operation'] = "<a class='btn blue' href='index.php?act=member&op=member_edit&member_id=" . $value['member_id'] . "'><i class='fa fa-pencil-square-o'></i>编辑</a>";
            $param['member_id'] = $value['member_id'];
            $param['member_name'] = "<img src=".getMemberAvatarForID($value['member_id'])." class='user-avatar' onMouseOut='toolTip()' onMouseOver='toolTip(\"<img src=".getMemberAvatarForID($value['member_id']).">\")'>".$value['member_name'];
            $param['member_email'] = $value['member_email'];
            $param['member_mobile'] = $value['member_mobile'];
            $param['member_sex'] = $sex_array[$value['member_sex']];
            $param['member_truename'] = $value['member_truename'];
            $param['member_birthday'] = $value['member_birthday'];
            $param['member_time'] = date('Y-m-d', $value['member_time']);
            $param['member_login_time'] = date('Y-m-d', $value['member_login_time']);
            $param['member_login_ip'] = $value['member_login_ip'];
            $param['member_points'] = $value['member_points'];
            $param['member_exppoints'] = $value['member_exppoints'];
            $param['member_grade'] = ($t = $model_member->getOneMemberGrade($value['member_exppoints'], false, $member_grade))?$t['level_name']:'';
            $param['available_predeposit'] = ncPriceFormat($value['available_predeposit']);
            $param['freeze_predeposit'] = ncPriceFormat($value['freeze_predeposit']);
            $param['available_rc_balance'] = ncPriceFormat($value['available_rc_balance']);
            $param['freeze_rc_balance'] = ncPriceFormat($value['freeze_rc_balance']);
            $param['inform_allow'] = $value['inform_allow'] ==  '1' ? '<span class="yes"><i class="fa fa-check-circle"></i>是</span>' : '<span class="no"><i class="fa fa-ban"></i>否</span>';
            $param['is_buy'] = $value['is_buy'] ==  '1' ? '<span class="yes"><i class="fa fa-check-circle"></i>是</span>' : '<span class="no"><i class="fa fa-ban"></i>否</span>';
            $param['is_allowtalk'] = $value['is_allowtalk'] ==  '1' ? '<span class="yes"><i class="fa fa-check-circle"></i>是</span>' : '<span class="no"><i class="fa fa-ban"></i>否</span>';
            $data['list'][$value['member_id']] = $param;
        }
        echo Tpl::flexigridXML($data);exit();
    }

    /**
     * 性别
     * @return multitype:string
     */
    private function get_sex() {
        $array = array();
        $array[1] = '男';
        $array[2] = '女';
        $array[3] = '保密';
        return $array;
    }
    /**
     * csv导出
     */
    public function export_csvOp() {
        $model_member = Model('member');
        $condition = array();
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
                ,'member_time','member_login_time','member_login_ip','member_points','member_exppoints','member_grade','available_predeposit'
                ,'freeze_predeposit','available_rc_balance','freeze_rc_balance','inform_allow','is_buy','is_allowtalk','member_state'
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
                Tpl::output('murl','index.php?act=member&op=index');
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
     */
    private function createCsv($member_list) {
        $model_member = Model('member');
        $member_grade = $model_member->getMemberGradeArr();
        // 性别
        $sex_array = $this->get_sex();
        $data = array();
        foreach ($member_list as $value) {
            $param = array();
            $param['member_id'] = $value['member_id'];
            $param['member_name'] = $value['member_name'];
            $param['member_avatar'] = getMemberAvatarForID($value['member_id']);
            $param['member_email'] = $value['member_email'];
            $param['member_mobile'] = $value['member_mobile'];
            $param['member_sex'] = $sex_array[$value['member_sex']];
            $param['member_truename'] = $value['member_truename'];
            $param['member_birthday'] = $value['member_birthday'];
            $param['member_time'] = date('Y-m-d', $value['member_time']);
            $param['member_login_time'] = date('Y-m-d', $value['member_login_time']);
            $param['member_login_ip'] = $value['member_login_ip'];
            $param['member_points'] = $value['member_points'];
            $param['member_exppoints'] = $value['member_exppoints'];
            $param['member_grade'] = ($t = $model_member->getOneMemberGrade($value['member_exppoints'], false, $member_grade))?$t['level_name']:'';
            $param['available_predeposit'] = ncPriceFormat($value['available_predeposit']);
            $param['freeze_predeposit'] = ncPriceFormat($value['freeze_predeposit']);
            $param['available_rc_balance'] = ncPriceFormat($value['available_rc_balance']);
            $param['freeze_rc_balance'] = ncPriceFormat($value['freeze_rc_balance']);
            $param['inform_allow'] = $value['inform_allow'] ==  '1' ? '是' : '否';
            $param['is_buy'] = $value['is_buy'] ==  '1' ? '是' : '否';
            $param['is_allowtalk'] = $value['is_allowtalk'] ==  '1' ? '是' : '否';
            $param['member_state'] = $value['member_state'] ==  '1' ? '是' : '否';
            $data[$value['member_id']] = $param;
        }

        $header = array(
                'member_id' => '会员ID',
                'member_name' => '会员名称',
                'member_avatar' => '会员头像',
                'member_email' => '会员邮箱',
                'member_mobile' => '会员手机',
                'member_sex' => '会员性别',
                'member_truename' => '真实姓名',
                'member_birthday' => '出生日期',
                'member_time' => '注册时间',
                'member_login_time' => '最后登录时间',
                'member_login_ip' => '最后登录IP',
                'member_points' => '会员积分',
                'member_exppoints' => '会员经验',
                'member_grade' => '会员等级',
                'available_predeposit' => '可用预存款(元)',
                'freeze_predeposit' => '冻结预存款(元)',
                'available_rc_balance' => '可用充值卡(元)',
                'freeze_rc_balance' => '冻结充值卡(元)',
                'inform_allow' => '允许举报',
                'is_buy' => '允许购买',
                'is_allowtalk' => '允许咨询',
                'member_state' => '允许登录'
        );
       array_unshift($data, $header);
		$csv = new Csv();
	    $export_data = $csv->charset($data,CHARSET,'gbk');
	    $csv->filename = $csv->charset('member_list',CHARSET).$_GET['curpage'] . '-'.date('Y-m-d');
	    $csv->export($data);   
    }
}
