<?php

defined('ByShopWWI') or exit('Access Invalid!');

/**
 * Class cpsControl
 * CPS接口控制器
 * cps接口着陆页统一为/shop/index.php?act=cps其余参数不变
 * cps查询接口统一为/shop/index.php?act=cps&op=orders
 * 增加联盟识别参数与着陆页联盟识别参数一致，其余参数不变
 */
class get_dataControl extends BaseApiControl{
    public function indexOp()
    {
        $this->success('you are welcome!');
    }

    public function user_infoOp()
    {
        $id = $_SESSION['member_id'];
        /** @var memberModel $memberModel */
        $memberModel = Model('member');
        $userInfo = $memberModel->getMemberInfo(array('member_id'=>$id),
            array('crm_member_id','member_name','member_truename','member_avatar','member_sex','member_birthday','member_email','member_email_bind','member_mobile','member_mobile_bind','member_qq','member_ww','member_login_num','member_time','member_ip','member_login_time','member_old_login_time','member_login_ip','member_old_login_ip','member_points','available_predeposit','freeze_predeposit','available_rc_balance','freeze_rc_balance','inform_allow','is_buy','is_allowtalk','member_state','member_snsvisitnum','member_areaid','member_cityid','member_provinceid','member_areainfo','member_privacy','member_exppoints','source','member_type',)
            );
        $userInfo['member_avatar'] = getMemberAvatar($userInfo['member_avatar']);
        $userInfo['grade'] = $memberModel->getOneMemberGrade($userInfo['member_exppoints']);
        $this->success($userInfo);
    }

    public function testOp()
    {
        v(MD5_KEY,0);
        v(md5('c791084ae2a7c2469cd9e7e2a26a1bbc'),0);
    }
}
