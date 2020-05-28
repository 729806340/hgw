<?php
/**
 * 微信小程序登录
 */


defined('ByShopWWI') or exit('Access Invalid!');

class connect_weixinControl extends mobileHomeControl{

    private $config = array(
        'app_id' => 'wx66d8e5f039ce2822',
        'app_secret' => '0312929405daaedda4d1956da8c247c6'
    );

    public function __construct(){
        parent::__construct();
    }

    public function loginOp()
    {
        $user_code = $_POST['user_code'];
        $red_tid     = $_POST['tid'] > 0 ? intval($_POST['tid']) : 0;
        if (!$user_code) {
            output_error('参数错误');
        }

        $config = $this->config;
        $params = "appid=". $config['app_id']. "&secret=". $config['app_secret']. "&js_code=". $user_code. "&grant_type=authorization_code";
        $query_url = 'https://api.weixin.qq.com/sns/jscode2session?'. $params;
        import('Curl');
        $Curl = new Curl();
        $Curl->setTimeout(5);
        $result = $Curl->get($query_url);
        if (!$result) {
            output_error('登陆失败');
        }
        $result = json_decode($result, true);

        if (!isset($result['openid']) || !$result['openid']) {
            output_error('登陆失败,获取用户信息异常');
        }

        $data = array(
            'open_id' => $result['openid'],
            'session_key' => $result['session_key'],
            'union_id' => isset($result['unionid']) ? $result['unionid'] : '',
            'user_token' => '',
            'bind_phone' => 0,
        );
        if ($data['union_id']) {
            /** @var memberModel $model_member */
            $model_member   = Model('member');
            $condition_array  = array('weixin_unionid' => $data['union_id']);
            $member_info = $model_member->getMemberInfo($condition_array);
            if (!empty($member_info)) {
                if (!$member_info['member_state']) {//1为启用 0 为禁用
                    output_error(Language::get('hango_notallowed_login'));
                }
                $token = $this->getUserToken($member_info['member_id'], $member_info['member_name'], $data['open_id']);
                if (!$token) {
                    output_error('登录失败_');
                }
                $this->dealRedpacket($member_info['member_id'], $red_tid, $member_info['member_name']);
                $this->dealCartGoods($member_info['member_id'], $_POST['user_cookie']);
                $data['user_token'] = $token;
                $data['bind_phone'] = $member_info['member_mobile_bind'];
            }
        }

        output_data($data);
    }

    public function weixin_iv_loginOp() {

        $encrypted_data  = $_POST['encrypted_data'];
        $iv              = $_POST['iv'];
        $session_key     = $_POST['session_key'];
        $open_id     = $_POST['open_id'];
        $need_phone  = $_POST['callTel'] > 0 ? intval($_POST['callTel']) : 0;
        $red_tid     = $_POST['tid'] > 0 ? intval($_POST['tid']) : 0;
        $dealer_id   = $_POST['dealer_id'] > 0 ? intval($_POST['dealer_id']) : 0;

        if (empty($encrypted_data)) {
            output_error('参数错误');
        }

        if (empty($iv)) {
            output_error('参数错误');
        }

        if (empty($session_key)) {
            output_error('参数错误');
        }

        $app_id = $this->config['app_id'];

        if (strlen($session_key) != 24) {
            output_error('错误码-41001');
        }

        $aesKey = base64_decode($session_key);

        if (strlen($iv) != 24) {
            output_error('错误码-41002');
        }

        $aesIV = base64_decode($iv);

        $aesCipher = base64_decode($encrypted_data);

        $res = openssl_decrypt( $aesCipher, "AES-128-CBC", $aesKey, 1, $aesIV);

        $result = json_decode($res, true);

        if( $result  == NULL ) {
            output_error('错误码-41003');
        }
        if( $result['watermark']['appid'] != $app_id ) {
            output_error('错误码-41003');
        }
        $member_avatar = $result['avatarUrl'];
        $nickname = $result['nickName'];
        $union_id = $result['unionId'];

        if (!$union_id) {
            output_error('没获取到union_Id, 请绑定微信开放平台');
        }

        $result = array(
            'union_id' => $union_id,
            'user_token' => ''
        );
        /** @var memberModel $model_member */
        $model_member   = Model('member');
        $condition_array  = array('weixin_unionid' => $union_id);
        $member_info = $model_member->getMemberInfo($condition_array);
        if (!empty($member_info)) {
            if (!$member_info['member_state']) {//1为启用 0 为禁用
                output_error(Language::get('hango_notallowed_login'));
            }

            if (empty($member_info['member_avatar'])) {
                $model_member->editMember(array('member_id' => $member_info['member_id']), array('member_avatar' => $member_avatar));
            }

            $token = $this->getUserToken($member_info['member_id'], $member_info['member_name'], $open_id);
            if (!$token) {
                output_error('登录失败_！');
            }
            $this->dealRedpacket($member_info['member_id'], $red_tid, $member_info['member_name']);
            $this->dealCartGoods($member_info['member_id'], $_POST['user_cookie']);
            $result['user_token'] = $token;
        } elseif ($need_phone == 1) {//不需要手机号直接登陆
            $unionid = $union_id;
            $rand = rand(100, 899);
            if(strlen($nickname) < 3) {
                $nickname = $nickname.$rand;
            }
            $member_name = $nickname;
            $member_info = $model_member->getMemberInfo(array('member_name'=> $member_name));
            if(empty($member_info)&&C('OLD_STATUS')==true) {
                $member_info = $model_member->getOldMemberInfo(array('login_account'=>trim($member_name)));
            }
            if (!empty($member_info)) {
                for ($i = 1;$i < 999;$i++) {
                    $rand += $i;
                    $member_name = $nickname.$rand;
                    $member_info = $model_member->getMemberInfo(array('member_name'=> $member_name));
                    if(empty($member_info)&&C('OLD_STATUS')==true) {
                        $member_info = $model_member->getOldMemberInfo(array('login_account'=>trim($member_name)));
                    }
                    if(empty($member_info)) {//查询为空表示当前会员名可用
                        break;
                    }
                }
            }
            $member = array();
            $member['member_avatar'] = $member_avatar;
            $member['member_passwd'] = rand(100000, 999999);
            $member['member_email'] = '';
            $member['weixin_unionid'] = $unionid;
            $member['weixin_info'] = serialize(array('unionid' => $union_id,'openid' => $open_id));
            $member['member_name'] = $member_name;
            $member_id = $model_member->addMember($member);
            if($member_id) {
                /** @var PyramidService $PyramidService */
                $PyramidService = Service("Pyramid");
                $PyramidService->beSubordinate($dealer_id, $member_id, 'register');
                $token = $this->getUserToken($member_id, $member_name, $open_id);
                if (!$token) {
                    output_error('登录失败_！!');
                }
                $this->dealRedpacket($member_id, $red_tid, $member_name);
                $this->dealCartGoods($member_id, $_POST['user_cookie']);
                $result['user_token'] = $token;
            }
        }
        output_data($result);

    }

    public function decrypt_ivOp() {

        $encrypted_data  = $_POST['encrypted_data'];
        $iv              = $_POST['iv'];
        $session_key     = $_POST['session_key'];

        if (empty($encrypted_data)) {
            output_error('参数错误');
        }

        if (empty($iv)) {
            output_error('参数错误');
        }

        if (empty($session_key)) {
            output_error('参数错误');
        }

        $app_id = $this->config['app_id'];

        if (strlen($session_key) != 24) {
            output_error('错误码-41001');
        }

        $aesKey = base64_decode($session_key);

        if (strlen($iv) != 24) {
            output_error('错误码-41002');
        }

        $aesIV = base64_decode($iv);

        $aesCipher = base64_decode($encrypted_data);

        $res = openssl_decrypt( $aesCipher, "AES-128-CBC", $aesKey, 1, $aesIV);

        $result = json_decode($res, true);

        if( $result  == NULL ) {
            output_error('错误码-41003');
        }
        if( $result['watermark']['appid'] != $app_id ) {
            output_error('错误码-41003');
        }
        output_data($result);
    }
    public function get_session_keyOp() {
        $user_code = $_POST['user_code'];
        if (!$user_code) {
            output_error('参数错误');
        }

        $config = $this->config;
        $params = "appid=". $config['app_id']. "&secret=". $config['app_secret']. "&js_code=". $user_code. "&grant_type=authorization_code";
        $query_url = 'https://api.weixin.qq.com/sns/jscode2session?'. $params;
        import('Curl');
        $Curl = new Curl();
        $Curl->setTimeout(5);
        $result = $Curl->get($query_url);
        if (!$result) {
            output_error('登陆失败');
        }
        $result = json_decode($result, true);

        if (!isset($result['openid']) || !$result['openid']) {
            output_error('登陆失败,获取用户信息异常');
        }
        output_data(array(
            'session_key' => $result['session_key'],
        ));
    }

    private function getUserToken($member_id, $member_name, $open_id) {
        $check_condition = array(
            'member_id' => $member_id,
            'login_time' => array('gt', TIMESTAMP-604800),//7天内有效
        );
        /** @var mb_user_tokenModel $model_user_token */
        $model_user_token   = Model('mb_user_token');
        $user_token_info = $model_user_token->getMbUserTokenInfo($check_condition);
        if ($user_token_info) {
            return $user_token_info['token'];
        }
        $token = $this->_get_token($member_id, $member_name, $open_id);
        return $token;
    }

    /**
     * 登录生成token
     *
     * @param int    $member_id     用户id
     * @param string $member_name   用户名
     * @param string $open_id       微信qq用户open_id
     * @param string $client        weixin wap = ios android
     * @return bool|string
     */
    private function _get_token($member_id, $member_name, $open_id = '' , $client = 'weixin') {
        /** @var mb_user_tokenModel $model_mb_user_token */
        $model_mb_user_token   = Model('mb_user_token');
        //生成新的token
        $mb_user_token_info = array();
        $token = md5($member_name . strval(TIMESTAMP) . strval(rand(0,999999)));
        $mb_user_token_info['member_id'] = $member_id;
        $mb_user_token_info['member_name'] = $member_name;
        $mb_user_token_info['token'] = $token;
        $mb_user_token_info['login_time'] = TIMESTAMP;
        $mb_user_token_info['client_type'] = $client;
        $mb_user_token_info['openid'] = $open_id;
        $result = $model_mb_user_token->addMbUserToken($mb_user_token_info);

        if($result) {
            return $token;
        } else {
            return false;
        }

    }

    // 发送验证码
    public function get_sms_captchaOp(){
        $phone = $_POST['phone'];
        if (!is_numeric($phone) || strlen($phone) != 11) {
            output_error('手机号码不正确');
        }
        /** @var sms_logModel $model_sms_log */
        $model_sms_log = Model('sms_log');
        $condition = array();
        $condition['log_ip'] = getIp();
        $condition['log_type'] = 1;
        $sms_log = $model_sms_log->getSmsInfo($condition);
        if(!empty($sms_log) && ($sms_log['add_time'] > TIMESTAMP-30)) {//同一IP十分钟内只能发一条短信
            output_error('同一IP地址30秒内，请勿多次获取动态码！');
        }
        $log_array = array();
        $captcha = rand(100000, 999999);
        $log_msg = '您于'.date("Y-m-d"). '申请注册会员，动态码：'.$captcha.'。';
        $sms = new Sms();
        $result = $sms->send($phone,$log_msg);
        if (!$result) {
            output_error('手机短信发送失败');
        }
        $log_array['log_phone'] = $phone;
        $log_array['log_captcha'] = $captcha;
        $log_array['log_ip'] = getIp();
        $log_array['log_msg'] = $log_msg;
        $log_array['log_type'] = 1;
        $log_array['add_time'] = time();
        $model_sms_log->addSms($log_array);
        output_data(array('sms_time'=>10,'error'=>'1'));
    }

    /**
     * 验证注册动态码
     */
    private function check_sms_captcha($phone, $captcha){

        if (!is_numeric($phone) || strlen($phone) != 11 || !$captcha) {
            return array('status' => 0, 'msg' => '请输入正确的手机号');
        }
        $condition = array();
        $condition['log_phone'] = $phone;
        $condition['log_captcha'] = $captcha;
        $condition['log_type'] = 1;
        /** @var sms_logModel $model_sms_log */
        $model_sms_log = Model('sms_log');
        $sms_log = $model_sms_log->getSmsInfo($condition);
        if(empty($sms_log) || ($sms_log['add_time'] < TIMESTAMP-1800)) {//半小时内进行验证为有效
            return array('status' => 0, 'msg' => '短信验证码错误或已过期，重新输入');
        }
        return array('status' => 1, 'msg' => '成功');

    }

    /**
     * 绑定手机注册
     */
    public function sms_registerOp(){
        $phone = $_POST['phone'];
        $captcha = $_POST['captcha'];
        $union_id = $_POST['union_id'];
        $open_id  = $_POST['open_id'];
        $dealer_id   = $_POST['dealer_id'] > 0 ? intval($_POST['dealer_id']) : 0;
        $member_avatar = $_POST['member_avatar'];
        if (!$union_id || !$open_id) {
            output_error('参数错误');
        }

        $hashKey = 'sms_register.busy.'.$phone;
        $hash = rkcache($hashKey);
        if($hash){
            if($hash) output_error('请勿重复提交');
        }
        wkcache($hashKey,1,60);

        $check_captcha = $this->check_sms_captcha($phone, $captcha);
        if (!$check_captcha['status']) {
            output_error($check_captcha['msg']);
        }

        /** @var memberModel $model_member */
        $model_member   = Model('member');
        $condition_union  = array(
            'weixin_unionid' => $union_id
        );
        $member_info = $model_member->getMemberInfo($condition_union);
        if (!empty($member_info)) {
            dkcache($hashKey);
            output_error('绑定手机异常！请重新登陆');
        }

        $condition_phone = array('member_mobile' => $phone);
        $member_info = $model_member->getMemberInfoNew($condition_phone);

        if (empty($member_info)) {
            /** @var connect_apiLogic $logic_connect_api */
            $logic_connect_api = Logic('connect_api');
            $wei_info = array(
                'unionid' => $union_id,
                'openid' => $open_id
            );
            $register_result = $logic_connect_api->smsWeiRegister($phone, $captcha, $wei_info);
            if (!$register_result['state']) {
                dkcache($hashKey);
                output_error($register_result['msg']);
            }
            $member_info['member_id'] = $register_result['userid'];
            $member_info['member_name'] = $register_result['username'];
            if ($member_avatar) {
                $model_member->editMember(array('member_id' => $member_info['member_id']), array('member_avatar' => $member_avatar));
            }
            /** @var PyramidService $PyramidService */
            $PyramidService = Service("Pyramid");
            $PyramidService->beSubordinate($dealer_id, $member_info['member_id'], 'register');

        } else {
            if (empty($member_info['member_avatar']) && $member_avatar) {
                $condition_union['member_avatar'] = $member_avatar;
            }
            $s1 = $model_member->editMember(array('member_id'=>$member_info['member_id']), $condition_union);
            if (!$s1) {
                Log::record('更新union_id 出错 member_id是'. $member_info['member_id']);
            }
        }
        $this->dealCartGoods($member_info['member_id'], $_POST['user_cookie']);
        $key = $this->_get_token($member_info['member_id'], $member_info['member_name'], $open_id);
        dkcache($hashKey);
        output_data(array('user_token' => $key));

    }

    /**
     * @param $user_id 用户id
     * @param $goods_temp 未登录状态存储加入购物车商品
     * @return bool
     */
    private function dealCartGoods($user_id, $goods_temp)
    {
        $goods_temp = html_entity_decode($goods_temp);
        if ($user_id <= 0 || empty($goods_temp) || empty(json_decode($goods_temp, true))) {
            return true;
        }

        $goods_temp = json_decode($goods_temp, true);
        /** @var cartModel $cartModel */
        $cartModel = Model('cart');
        $cartModel->mergeWeiCart($user_id, array_column($goods_temp, 'goods_num', 'goods_id'));
        return true;
    }

    private function dealRedpacket($user_id, $tid, $member_name) {
        if ($user_id <= 0 || $tid <= 0 ) {
            return true;
        }
        /** @var redpacketModel $model_redpacket */
        $model_redpacket = Model('redpacket');
        //验证是否可以兑换红包
        $data = $model_redpacket->getCanChangeTemplateInfo($tid, $user_id);
        if ($data['state'] == false){
            return true;
        }
        //添加红包信息
        $result = $model_redpacket->exchangeRedpacket($data['info'], $user_id, $member_name);
        return true;
    }
}
