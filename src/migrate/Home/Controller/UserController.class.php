<?php
namespace Home\Controller;

use Think\Controller;
use Think\Model;

class UserController extends CommonController
{
    public $steps = array('index');

    public function index()
    {
        $this->getItems('B2cMembers', 'member_id');
        foreach ($this->items as $item) {
            $member = $this->copyTo($item, array(
                'member_id' => 'member_id',
                'name' => 'member_truename',
                'crm_member_id' => 'crm_member_id',
                'point' => 'member_points',
                'experience' => 'member_exppoints',
                'mobile' => 'member_mobile',
                'email' => 'member_email',
                //'sex'=>'member_sex',
                //'advance' => 'available_rc_balance',
                'advance_freeze' => 'freeze_rc_balance',
                'reg_ip' => 'member_ip',
                'regtime' => 'member_time',
                //'disabled'=>'member_state',
                'login_count' => 'member_login_num',
                'source' => 'source',
                'member_type' => 'member_type',
            ));
            $member['member_login_time'] = 0;
            $member['member_old_login_time'] = 0;
            $member['member_state'] = 1;//$item['disabled'] == 'false' ? 1 : 0;
            $member['available_rc_balance'] = $item['advance'] > 9999999 ? 9999999 : $item['advance'];
            $accounts = ecM('PamMembers')->where(array('member_id' => $item['member_id']))->select();
            // 处理身份认证信息
            foreach ($accounts as $k => $account) {
                if ($k == 0 || $account['login_type'] == 'local') {
                    $member['member_name'] = $account['login_account'];
                    $member['password_salt'] = $account['salt'];
                    $member['password_account'] = $account['password_account'];
                    $member['member_passwd'] = $account['login_password'];
                    $member['member_time'] = $account['createtime'];
                }
                if ($account['login_type'] == 'email') {
                    $member['member_email'] = $account['login_account'];
                    $member['member_email_bind'] = 1;
                } elseif ($account['login_type'] == 'mobile') {
                    $member['member_mobile'] = $account['login_account'];
                    $member['member_mobile_bind'] = 1;
                }
            }
            // 处理第三方登录信息
            $openIds = ecM('OpenidOpenid')->where(array('member_id' => $item['member_id']))->select();
            if (!empty($openIds))
                foreach ($openIds as $openId) {
                    if ($openId['provider_code'] == 'weixin') {
                        $member['weixin_unionid'] = $openId['openid'];
                        $member['weixin_info'] = serialize(array(
                            'openid' => $openId['openid'],
                            'nickname' => $openId['nickname'],
                            'appid'=>$openId['provider_openid'],
                        ));
                    }
                    if ($openId['provider_code'] == 'qq') {
                        $member['member_qqopenid'] = $openId['openid'];
                        $member['member_qqinfo'] = serialize(array(
                            'openid' => $openId['openid'],
                            'nickname' => $openId['nickname'],
                            'appid'=>$openId['provider_openid'],
                        ));
                    }
                    $member['member_avatar'] = $openId['avatar'];

                    //$member['member_name'] = $member['name'];
                    $name = $openId['provider_code'] . '__' . $member['member_id'];
                    $rand = 0;
                    do {
                        $check = M('Member')->where(
                            array(
                                'member_name' => $rand ? $name . '_' . $rand : $name,
                                'member_id' => array('neq', $member['member_id'])
                            )
                        )->count();
                        if ($check > 0) $rand = rand(1, 999);
                    } while ($check > 0);
                    $member['member_name'] = $rand ? $name . '_' . $rand : $name;
                }
            $member['member_mobile'] = substr($member['member_mobile'], 0, 11);

            $has = M('Member')->find($item['member_id']);
            if(empty($has)){
                M('Member')->add($member);
            }else{
                M('Member')->save($member);
            }
        }
        $this->nextAction();
    }

    private function rcb()
    {
        $this->getItems('B2cMemberAdvance', 'log_id');
        foreach ($this->items as $item) {
            $log = $this->copyTo($item, array('log_id' => 'id', 'member_id' => 'member_id', 'mtime' => 'add_time', 'shop_advance' => 'shop_amount'));
            $log['available_amount'] = $item['import_money'] - $item['explode_money'];
            $member = M('Member')->find($item['member_id']);
            $log['member_name'] = $member['member_name'] ?: 'N/A';
            $payMethod = $item['paymethod'] == 'alipay' ? '支付宝' : '预存款';
            if ($log['available_amount'] > 0) {
                if (strpos($item['message'], '退款') !== false) {
                    $log['type'] = 'refund';
                    $log['description'] = $item['message'] . ' 订单号：' . $item['order_id'] . ' 支付单号：' . $item['payment_id'] . ' 支付方式：' . $payMethod;
                } else {
                    $log['type'] = 'recharge';
                    $log['description'] = $item['message'] . ' 支付单号：' . $item['payment_id'] . ' 支付方式：' . $payMethod;
                }
            } else {
                $log['type'] = 'order_pay';
                $log['description'] = $item['message'] . ' 订单号：' . $item['order_id'] . ' 支付单号：' . $item['payment_id'] . ' 支付方式：' . $payMethod;
            }
            M('RcbLog')->add($log);
        }
        $this->nextAction();
    }

    private function card()
    {

        $this->getItems('PamCzcard', 'card_id');
        foreach ($this->items as $item) {
            $card = $this->copyTo($item, array(
                'card_id' => 'id',
                'member_id' => 'member_id',
                'bonus_sn' => 'sn',
                'bonus_pwd' => 'pwd',
                'type_money' => 'denomination',
                'status' => 'state',
                'settime' => 'tsused',
                'createtime' => 'tscreated',
                'activetime' => 'actived',
                'isflag' => 'isflag',
                'disabled' => 'disabled',
                'receiver_name' => 'receiver',
                'memo' => 'memo',
            ));
            $member = M('Member')->find($item['member_id']);
            $card['member_name'] = $member['member_name'] ?: 'N/A';
            $card['disabled'] = $item['disabled'] ? 1 : 0;
            $card['batchflag'] = '汉购卡（旧）';
            $card['admin_name'] = 'System';
            M('Rechargecard')->add($card);
        }
        $this->nextAction();

    }
}