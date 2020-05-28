<?php
/**
 * 微信小程序登录
 */


defined('ByShopWWI') or exit('Access Invalid!');

class connect_douyinControl extends mobileHomeControl{

    private $config = array();

    public function __construct(){
        parent::__construct();
        $this->config = C('douyin');
    }

    public function loginOp()
    {
        $user_code = $_POST['user_code'];
        $red_tid     = $_POST['tid'] > 0 ? intval($_POST['tid']) : 0;
        if (!$user_code) {
            output_error('参数错误');
        }

        $config = $this->config;
        $params = "appid=". $config['app_id']. "&secret=". $config['app_secret']. "&code=". $user_code;
        $query_url = 'https://developer.toutiao.com/api/apps/jscode2session?'. $params;
        import('Curl');
        $Curl = new Curl();
        $Curl->setTimeout(5);
        $result = $Curl->get($query_url);
        if (!$result) {
            output_error('登陆失败');
        }
        $result = json_decode(json_encode($result), true);

        if (!isset($result['openid']) || !$result['openid']) {
            output_error('登陆失败,获取用户信息异常');
        }

        $data = array(
            'open_id' => $result['openid'],
            'session_key' => $result['session_key'],
            'user_token' => '',
            'bind_phone' => 0,
        );
        if ($data['open_id']) {
            /** @var memberModel $model_member */
            $model_member   = Model('member');
            $condition_array  = array('douyin_open_id' => $data['open_id']);
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

        $open_id     = $_POST['open_id'];
        $red_tid     = $_POST['tid'] > 0 ? intval($_POST['tid']) : 0;
        $dealer_id   = $_POST['dealer_id'] > 0 ? intval($_POST['dealer_id']) : 0;

        $member_avatar = $_POST['avatarUrl'];
        $nickname = $_POST['nickName'];
        if (!$open_id) {
            output_error('参数不正确');
        }
        $result = array(
            'user_token' => ''
        );
        /** @var memberModel $model_member */
        $model_member   = Model('member');
        $condition_array  = array('douyin_open_id' => $open_id);
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
        } else {//不需要手机号直接登陆
            $member = array();
            $member['member_avatar'] = $member_avatar;
            $member['member_passwd'] = rand(100000, 999999);
            $member['member_email'] = '';
            $member['weixin_unionid'] = '';
            $member['douyin_open_id'] = $open_id;
            $member['member_name'] = $nickname;
            $member['source'] = 'douyin';
            $member_id = $model_member->addMember($member);
            if($member_id) {
                /** @var PyramidService $PyramidService */
                $PyramidService = Service("Pyramid");
                $PyramidService->beSubordinate($dealer_id, $member_id, 'register');
                $token = $this->getUserToken($member_id, $nickname, $open_id);
                if (!$token) {
                    output_error('登录失败_！!');
                }
                $this->dealRedpacket($member_id, $red_tid, $nickname);
                $this->dealCartGoods($member_id, $_POST['user_cookie']);
                $result['user_token'] = $token;
            }
        }
        output_data($result);

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
