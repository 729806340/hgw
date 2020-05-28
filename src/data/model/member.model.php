<?php
/**
 * 会员模型
 *
 *
 *
 * * @汉购网 (c) 2015-2018 ShopWWI Inc. (http://www.hangowa.com)
 * @license    http://www.shopwwi.c om
 * @link       交流群号：
 * @since      汉购网提供技术支持 授权请购买shopnc授权
 */
defined('ByShopWWI') or exit('Access Invalid!');
class memberModel extends Model {

    public function __construct(){
        parent::__construct('member');
    }

    //钻石会员判断
    public function isDiamondMember($member_id){
        $where = array();
        $where['diamond'] = 1;
        $where['member_id'] = $member_id;
        return $this->table('member')->where($where)->find();
    }

    //设置为钻石会员
    public function setDiamondMember($member_id){
        $condition = array();
        $condition['member_id'] = $member_id;

        $data = array();
        $data['diamond'] = 1;
        return $this->table('member')->where($condition)->update($data);
    }
    /**
     * 会员详细信息（查库）
     * @param array $condition
     * @param string $field
     * @return array
     */
    public function getMemberInfo($condition, $field = '*', $master = false) {
        return $this->table('member')->field($field)->where($condition)->master($master)->find();
    }

    /**
     * 会员详细信息（查库）
     * @param array $condition
     * @param string $field
     * @param string $order
     * @return array
     */
    public function getMemberInfoNew($condition, $field = '*', $order = 'member_mobile_bind DESC', $master = false) {
        return $this->table('member')->field($field)->where($condition)->order($order)->master($master)->find();
    }


    public function getOldMemberInfo($condition,$field="*"){
        $model = TModel('Member');
        $tm = ecModel('PamMembers')->field($field)->where($condition)->find();
        if(empty($tm)) return $tm;
        $item = ecModel('B2cMembers')->find($tm['member_id']);
        $member = copyTo($item, array(
            'member_id' => 'member_id',
            'name' => 'member_truename',
            'crm_member_id' => 'crm_member_id',
            'point' => 'member_points',
            'experience' => 'member_exppoints',
            'mobile' => 'member_mobile',
            'email' => 'member_email',
            //'sex'=>'member_sex',
            'advance' => 'available_rc_balance',
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
        $member['available_rc_balance'] = $item['advance']>9999999 ? 9999999 : $item['advance'];
        $accounts = ecModel('PamMembers')->where(array('member_id' => $item['member_id']))->select();
        // 处理身份认证信息
        foreach ($accounts as $k=>$account) {
            if($k == 0||$account['login_type'] =='local'){
                $member['member_name'] = $account['login_account'];
                $member['password_salt'] = $account['salt'];
                $member['password_account'] = $account['password_account'];
                $member['member_passwd'] = $account['login_password'];
                $member['member_time'] = $account['createtime'];
            }
            if($account['login_type'] == 'email'){
                $member['member_email'] = $account['login_account'];
                $member['member_email_bind'] = 1;
            }elseif ($account['login_type'] == 'mobile'){
                $member['member_mobile'] = $account['login_account'];
                $member['member_mobile_bind'] = 1;
            }
        }
        // 处理第三方登录信息
        $openIds = ecModel('OpenidOpenid')->where(array('member_id' => $item['member_id']))->select();
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
                    $check = TModel('Member')->where(
                        array(
                            'member_name' => $rand ? $name . '_' . $rand : $name,
                            'member_id' => array('neq', $member['member_id'])
                        )
                    )->count();
                    if ($check > 0) $rand = rand(1,999);
                }while($check>0);
                $member['member_name'] = $rand ? $name . '_' . $rand : $name;
            }
        $member['member_mobile'] = substr($member['member_mobile'], 0, 11);
        $has = $model->find($item['member_id']);
        if(empty($has)){
            $model->add($member);
            //return $model->field($field)->find($item['member_id']);
        }
        return $member;
    }

    public function getEcOpenid($openid,$provider_code)
    {
        $openidModel = ecModel('OpenidOpenid');
        $open = $openidModel->where(array('provider_code'=>$provider_code,'openid'=>$openid))->find();
        if(empty($open)) return $open;
        $member = TModel('Member')->where(array('member_id'=>$open['member_id']))->find();
        $ecMember = $this->getOldMemberInfo(array('member_id'=>$open['member_id']));
        if(!empty($member)) {
            $this->updateMemberByOld($ecMember);
        }
        return TModel('Member')->where(array('member_id'=>$open['member_id']))->find();
    }

    public function updateMemberByOld($item,$all=true)
    {
        $model = TModel('Member');
        $member = $model->find($item['member_id']);
        $member['available_rc_balance'] = $item['available_rc_balance'];
        if($all){
            $member['member_passwd'] = $item['member_passwd'];
            $member['member_mobile'] = $item['member_mobile'];
            $member['member_email'] = $item['member_email'];
            $member['weixin_unionid'] = $item['weixin_unionid'];
            $member['member_qqopenid'] = $item['member_qqopenid'];
            $member['member_avatar'] = $item['member_avatar'];
            empty($member['member_email'])||$member['member_email_bind'] =1;
            empty($member['member_mobile'])||$member['member_mobile_bind'] =1;
        }
        $res = $model->where(array('member_id'=>$item['member_id']))->save($member);
        return $res;

    }

    /**
     * 取得会员详细信息（优先查询缓存）
     * 如果未找到，则缓存所有字段
     * @param int $member_id
     * @param string $field 需要取得的缓存键值, 例如：'*','member_name,member_sex'
     * @return array
     */
    public function getMemberInfoByID($member_id, $fields = '*') {
        $member_info = rcache($member_id, 'member', $fields);
        if (empty($member_info)) {
            $member_info = $this->getMemberInfo(array('member_id' => $member_id), '*', true);
            if (C('OLD_STATUS') == true) {
                $oldInfo = $this->getOldMemberInfo(array('member_id' => $member_id));
                if ($oldInfo['available_rc_balance'] < 9999999 && $oldInfo['available_rc_balance'] !== $member_info['available_rc_balance']) {
                    $member_info['available_rc_balance'] = $oldInfo['available_rc_balance'];
                    /** @var memberModel $model_member */
                    $model_member = Model('member');
                    $model_member->updateMemberByOld(array(
                        'member_id' => $member_info['member_id'],
                        'available_rc_balance' => $oldInfo['available_rc_balance'],
                    ), false);
                }
            }
            wcache($member_id, $member_info, 'member');
        }
        return $member_info;
    }

    /**
     * 会员列表
     * @param array $condition
     * @param string $field
     * @param number $page
     * @param string $order
     */
    public function getMemberList($condition = array(), $field = '*', $page = null, $order = 'member_id desc', $limit = '') {
       return $this->table('member')->field($field)->where($condition)->page($page)->order($order)->limit($limit)->select();
    }

	/**
	*获取佣金订单数量
	*
	*/
	    public function getOrderInviteCount($inviteid,$memberid)
    {
		return $this->table('pd_log')->where(array('lg_invite_member_id' => $memberid,'lg_member_id' => $inviteid))->count();
    }
		/**
	*获取佣金订单总金额
	*
	*/
	    public function getOrderInviteamount($inviteid,$memberid)
    {
		return $this->table('pd_log')->where(array('lg_invite_member_id' => $memberid,'lg_member_id' => $inviteid))->sum('lg_av_amount');
    }
	    /**
     * 会员列表
     * @param array $condition
     * @param string $field
     * @param number $page
     * @param string $order
     */
    public function getMembersList($condition, $page = null, $order = 'member_id desc', $field = '*') {
       return $this->table('member')->field($field)->where($condition)->page($page)->order($order)->select();
    }

	
	/**
	 * 删除会员
	 *
	 * @param int $id 记录ID
	 * @return array $rs_row 返回数组形式的查询结果
	 */
	public function del($id){
		if (intval($id) > 0){
			$where = " member_id = '". intval($id) ."'";
			$result = Db::delete('member',$where);
			return $result;
		}else {
			return false;
		}
	}

    /**
     * 会员数量
     * @param array $condition
     * @return int
     */
    public function getMemberCount($condition) {
        return $this->table('member')->where($condition)->count();
    }

    /**
     * 编辑会员
     * @param array $condition
     * @param array $data
     */
    public function editMember($condition, $data) {
        if(C('OLD_STATUS')){
            $member = $this->table('member')->where($condition)->find();
            $accountModel = ecModel('PamMembers');
            if(isset($data['member_passwd'])) {
                if($member['member_passwd'] != $data['member_passwd']){
                    $accountModel->where(array('member_id'=>$member['member_id']))->setField (array(
                        'login_password'=>$data['member_passwd'],
                    ));
                }
            }
            if(isset($data['member_mobile_bind'])&&$data['member_mobile_bind']==1) {
                $ecMap = array('member_id'=>$member['member_id'], 'login_type'=>'mobile');
                $ecAccount = $accountModel->where($ecMap)->find();
                $ecData = array(
                    'member_id'=>$member['member_id'],
                    'login_type'=>'mobile',
                    'login_account'=>$member['member_mobile'],
                    'login_password'=>$member['member_passwd'],
                    'password_account'=>$member['password_account'],
                    'salt'=>$member['password_salt'],
                    'createtime'=>$member['member_time'],
                );
                if(empty($ecAccount)) $accountModel->add($ecData);
                else $accountModel->where($ecMap)->save($ecData);
            }
            if(isset($data['member_email_bind'])&&$data['member_email_bind']==1) {
                $ecMap = array('member_id'=>$member['member_id'], 'login_type'=>'email');
                $ecAccount = $accountModel->where($ecMap)->find();
                $ecData = array(
                    'member_id'=>$member['member_id'],
                    'login_type'=>'email',
                    'login_account'=>$member['member_email'],
                    'login_password'=>$member['member_passwd'],
                    'password_account'=>$member['password_account'],
                    'salt'=>$member['password_salt'],
                    'createtime'=>$member['member_time'],
                );
                if(empty($ecAccount)) $accountModel->add($ecData);
                else $accountModel->where($ecMap)->save($ecData);
            }
        }
        $update = $this->table('member')->where($condition)->update($data);
        if ($update && $condition['member_id']) {
            dcache($condition['member_id'], 'member');
        }
        return $update;
    }

    public function editOldMember($condition, $data)
    {

    }

    /**
     * 登录时创建会话SESSION
     *
     * @param array $member_info 会员信息
     */
    public function createSession($member_info = array(),$reg = false) {
        if (empty($member_info) || !is_array($member_info)) return ;

        if(C('OLD_STATUS')) {
            $this->createEcSession($member_info);
        }
        $_SESSION['is_login']   = '1';
        $_SESSION['member_id']  = $member_info['member_id'];
        $_SESSION['member_name']= $member_info['member_name'];
        $_SESSION['member_email']= $member_info['member_email'];
        $_SESSION['is_buy']     = isset($member_info['is_buy']) ? $member_info['is_buy'] : 1;
        $_SESSION['avatar']     = $member_info['member_avatar'];


        
        // 头衔COOKIE
        $this->set_cookie($member_info);

        $seller_info = Model('seller')->getSellerInfo(array('member_id'=>$_SESSION['member_id']));
        $_SESSION['store_id'] = $seller_info['store_id'];

        if (trim($member_info['member_qqopenid'])){
            $_SESSION['openid']     = $member_info['member_qqopenid'];
        }
        if (trim($member_info['member_sinaopenid'])){
            $_SESSION['slast_key']['uid'] = $member_info['member_sinaopenid'];
        }

        if (!$reg) {
            //添加会员积分
            $this->addPoint($member_info);
            //添加会员经验值
            $this->addExppoint($member_info);
        }

        if(!empty($member_info['member_login_time'])||empty($member_info['member_old_login_time'])) {
            $update_info    = array(
                'member_login_num'=> ($member_info['member_login_num']+1),
                'member_login_time'=> TIMESTAMP,
                'member_old_login_time'=> $member_info['member_login_time'],
                'member_login_ip'=> getIp(),
                'member_old_login_ip'=> $member_info['member_login_ip']
            );
            $this->editMember(array('member_id'=>$member_info['member_id']),$update_info);
        }
        setNcCookie('cart_goods_num','',-3600);
        // cookie中的cart存入数据库
        Model('cart')->mergecart($member_info,$_SESSION['store_id']);
        Model('b2b_cart')->mergecart($member_info);
        // cookie中的浏览记录存入数据库
        Model('goods_browse')->mergebrowse($_SESSION['member_id'],$_SESSION['store_id']);
        
        // 自动登录
        if ($member_info['auto_login'] == 1) {
            $this->auto_login();
        }
    }

    protected function createEcSession($member)
    {
        import('Curl');
        $curl = new Curl();
        $sign = C('EC_SIGN');
        $secret = C('EC_SECRET');
        $time = TIMESTAMP;
        $code = md5("sign=$sign&secret=$secret&time=$time");
        $query = array(
            'method'=>'b2c.system.sync_login',
            'code'=>$code,
            'sign'=>$sign,
            'time'=>$time,
            'uid'=>$member['member_id'],
        );
        if($_COOKIE['sess_id']) $query['sess_id'] = $_COOKIE['sess_id'];
        $curl->get(C('EC_API_HOST').http_build_query($query));
        $content = json_decode($curl->response,true);

        //获取站点主机后缀(.com, .tmc or .local etc)
        if ('192.' == substr($_SERVER['HTTP_HOST'], 0, 4)) {
            $site_cookie_domain = $_SERVER['HTTP_HOST'];    //内网ip访问
        } else {
            $_aHttpHost  = explode('.', $_SERVER['HTTP_HOST']);
            $site_cookie_domain = '.'.$_aHttpHost[count($_aHttpHost) - 2].'.'.$_aHttpHost[count($_aHttpHost) - 1];
            $_pos = strpos($site_cookie_domain, ':');
            $_pos && $site_cookie_domain = substr($site_cookie_domain, 0, $_pos);   //过滤端口号
        }
        setcookie('s',$content['res'],0,'/',$site_cookie_domain,null,true);
        setcookie('UNAME',$member['member_truename']?:$member['member_name'],0,'/',$site_cookie_domain,null,false);
        setcookie('S[MEMBER]',$member['member_id'],0,'/',$site_cookie_domain,null,false);
        setcookie('S[SIGN][REMEMBER]',1,0,'/',$site_cookie_domain,null,false);
        return false;
    }
	
	/**
	 * 获取会员信息
	 *
	 * @param	array $param 会员条件
	 * @param	string $field 显示字段
	 * @return	array 数组格式的返回结果
	 */
	public function infoMember($param, $field='*') {
		if (empty($param)) return false;

		//得到条件语句
		$condition_str	= $this->getCondition($param);
		$param	= array();
		$param['table']	= 'member';
		$param['where']	= $condition_str;
		$param['field']	= $field;
		$param['limit'] = 1;
		$member_list	= Db::select($param);
		$member_info	= $member_list[0];
		if (intval($member_info['store_id']) > 0){
	      $param	= array();
	      $param['table']	= 'store';
	      $param['field']	= 'store_id';
	      $param['value']	= $member_info['store_id'];
	      $field	= 'store_id,store_name,grade_id';
	      $store_info	= Db::getRow($param,$field);
	      if (!empty($store_info) && is_array($store_info)){
		      $member_info['store_name']	= $store_info['store_name'];
		      $member_info['grade_id']	= $store_info['grade_id'];
	      }
		}
		return $member_info;
	}
    
    /**
     * 7天内自动登录
     */
    public function auto_login() {
        // 自动登录标记 保存7天
        setNcCookie('auto_login', encrypt($_SESSION['member_id'], MD5_KEY), 7*24*60*60);
    }
    
    public function set_cookie($member_info) {
        setNcCookie('member_avatar', $member_info['member_avatar'], 365*24*60*60);
        setNcCookie('member_id', $member_info['member_id'], 365*24*60*60);
        setNcCookie('member_name', $member_info['member_name'], 365*24*60*60);
        setNcCookie('member_turename', $member_info['member_turename'], 365*24*60*60);
    }

    /**
     * 注册
     */
    public function register($register_info) {
        // 注册验证
        $obj_validate = new Validate();
        $obj_validate->validateparam = array(
        array("input"=>$register_info["username"],      "require"=>"true",      "message"=>'用户名不能为空'),
        array("input"=>$register_info["password"],      "require"=>"true",      "message"=>'密码不能为空'),
        array("input"=>$register_info["password_confirm"],"require"=>"true",    "validator"=>"Compare","operator"=>"==","to"=>$register_info["password"],"message"=>'密码与确认密码不相同'),
        array("input"=>$register_info["email"],         "require"=>"true",      "validator"=>"email", "message"=>'电子邮件格式不正确'),
        );
        $error = $obj_validate->validate();
        if ($error != ''){
            return array('error' => $error);
        }

        // 验证用户名是否重复
        $check_member_name  = $this->getMemberInfo(array('member_name'=>$register_info['username']));
        if(empty($check_member_name)&&C('OLD_STATUS')==true)
            $check_member_name = $this->getOldMemberInfo(array('login_account'=>$register_info['username']));
        if(is_array($check_member_name) and count($check_member_name) > 0) {
            return array('error' => '用户名已存在');
        }

        // 验证邮箱是否重复
        $check_member_email = $this->getMemberInfo(array('member_email'=>$register_info['email']));
        if(empty($check_member_name)&&C('OLD_STATUS')==true)
            $check_member_name = $this->getOldMemberInfo(array('login_account'=>$register_info['email']));
        if(is_array($check_member_email) and count($check_member_email)>0) {
            return array('error' => '邮箱已存在');
        }
        // 会员添加
        $member_info    = array();
        $member_info['member_name']     = $register_info['username'];
        $member_info['member_passwd']   = $register_info['password'];
        $member_info['member_email']        = $register_info['email'];
		$member_info['invite_one']        = $register_info['invite_one'];
		$member_info['invite_two']        = $register_info['invite_two'];
		$member_info['invite_three']      = $register_info['invite_three'];
        $insert_id  = $this->addMember($member_info);
        if($insert_id) {
            $member_info['member_id'] = $insert_id;
            $member_info['is_buy'] = 1;

            return $member_info;
        } else {
            return array('error' => '注册失败');
        }

    }

    /**
     * 注册商城会员
     *
     * @param   array $param 会员信息
     * @return  array 数组格式的返回结果
     */
    public function addMember($param) {
        if(empty($param)) {
            return false;
        }
        try {
            $this->beginTransaction();
            $member_info    = array();
            //$member_info['member_id']           = $param['member_id'];
            $member_info['password_salt']           = rand(1,9999);
            $member_info['crm_member_id']           =0;
            $member_info['password_account'] =
            $member_info['member_name']         = !empty($param['member_name'])?$param['member_name']:"";
            $member_info['member_email']        = !empty($param['member_email'])?$param['member_email']:"";
            $member_info['member_time']         = TIMESTAMP;
            $member_info['member_passwd']       = passwordHash(
                trim($param['member_passwd']),
                $member_info['password_salt'],
                $member_info['password_account'],
                $member_info['member_time']
            );
            $member_info['member_login_time']   = TIMESTAMP;
            $member_info['member_old_login_time'] = TIMESTAMP;
            $member_info['member_login_ip']     = getIp();
            $member_info['member_old_login_ip'] = $member_info['member_login_ip'];

            $member_info['member_truename']     = !empty($param['member_truename'])?$param['member_truename']:'';
            $member_info['member_qq']           = !empty($param['member_qq'])?$param['member_qq']:"";
            $member_info['member_sex']          = !empty($param['member_sex'])?$param['member_sex']:1;
            $member_info['member_avatar']       = !empty($param['member_avatar'])?$param['member_avatar']:'';
            $member_info['member_qqopenid']     = !empty($param['member_qqopenid'])?$param['member_qqopenid']:'';
            $member_info['member_qqinfo']       = !empty($param['member_qqinfo'])?$param['member_qqinfo']:"";
            $member_info['member_sinaopenid']   = !empty($param['member_sinaopenid'])?$param['member_sinaopenid']:"";
            $member_info['member_sinainfo'] = !empty($param['member_sinainfo'])?$param['member_sinainfo']:"";
			$member_info['invite_one']   = !empty($param['invite_one'])?$param['invite_one']:0;
			$member_info['invite_two']   = !empty($param['invite_two'])?$param['invite_two']:0;
			$member_info['invite_three']   = !empty($param['invite_three'])?$param['invite_three']:0;
            if ($param['member_mobile_bind']) {
                $member_info['member_mobile'] = $param['member_mobile'];
                $member_info['member_mobile_bind'] = $param['member_mobile_bind'];
            }
            if ($param['weixin_unionid']) {
                $member_info['weixin_unionid'] = $param['weixin_unionid'];
                $member_info['weixin_info'] = $param['weixin_info'];
            }
            if ($param['douyin_open_id']) {
                $member_info['douyin_open_id'] = $param['douyin_open_id'];
            }
            if ($param['member_type']) {
                $member_info['member_type'] = $param['member_type'];
            }
            $member_info['source'] = $param['source']?$param['source']:'pc';
            /** 如果开启旧数据库，则先将数据添加到旧数据库，在添加到新数据库 */
//            if(C('OLD_STATUS')==true){
//                $member_info['member_id']=$this->addOldMember($member_info);
//                if($member_info['member_id']==false){
//                    throw new Exception('同步注册失败！');
//                }
//            }
            $insert_id  = $this->table('member')->insert($member_info);
//            $insert_id = $member_info['member_id']?:$insert_id;
            if (!$insert_id) {
                throw new Exception('会员注册失败');
            }

            $insert = $this->addMemberCommon(array('member_id'=>$insert_id));
            if (!$insert) {
                throw new Exception('添加Common失败');
            }

            // 添加默认相册
            $insert = array();
            $insert['ac_name']      = '买家秀';
            $insert['member_id']    = $insert_id;
            $insert['ac_des']       = '买家秀默认相册';
            $insert['ac_sort']      = 1;
            $insert['is_default']   = 1;
            $insert['upload_time']  = TIMESTAMP;
            $rs = $this->table('sns_albumclass')->insert($insert);
            //添加会员积分
            if (C('points_isuse')){
                $a=Model('points')->savePointsLog('regist',array('pl_memberid'=>$insert_id,'pl_membername'=>$param['member_name']),false);
            }
            $this->commit();
            return $insert_id;
        } catch (Exception $e) {
            $this->rollback();
            return false;
        }
    }

    protected function addOldMember($item)
    {
        $model = TModel();
        $accountModel = ecModel('PamMembers');
        $authModel = ecModel('PamAuth');
        $memberModel = ecModel('B2cMembers');
        $openIdModel = ecModel('OpenidOpenid');
        $accountModel->startTrans();

        //$member_info['member_qqopenid']     = $item['member_qqopenid'];
        //$member_info['member_qqinfo']       = $item['member_qqinfo'];

        $member = copyTo($item,array(
            //'member_id' => 'member_id',
            'member_truename' => 'name',
            'member_mobile' => 'mobile',
            'member_email' => 'email',
            'member_login_ip' => 'reg_ip',
            'member_time' => 'regtime',
        ));
        $member['source'] = $item['source']?$item['source']:'pc';
        $member['member_type'] = 'normal';
        $memberId = $memberModel->add($member);
        if(!$memberId) return false;
        $account = array(
            'member_id'=> $memberId,
            'login_account'=> $item['member_name'],
            'login_type'=> 'local',
            'login_password'=> $item['member_passwd'],
            'password_account'=> $item['password_account'],
            'salt'=> $item['password_salt'],
            'disabled'=> 'false',
            'createtime'=> $item['member_time'],
        );
        $res = $accountModel->add($account);
        if($res == false) {
            //v($accountModel->getDbError());
            $memberModel->rollback();
            return false;
        }
        if ($item['member_mobile_bind']) {
            $account['login_account'] = $item['member_mobile'];
            $account['login_type'] = 'mobile';
            $res = $accountModel->add($account);
            if($res == false) {
                //v($accountModel->getDbError());
                $memberModel->rollback();
                return false;
            }
        }
        if ($item['member_email_bind']) {
            $account['login_account'] = $item['member_email'];
            $account['login_type'] = 'email';
            $res = $accountModel->add($account);
            if($res == false) {
                //v($accountModel->getDbError());
                $memberModel->rollback();
                return false;
            }
        }
        if ($item['weixin_unionid']) {
            //$openid['weixin_unionid'] = $item['weixin_unionid'];
            $openid['member_id'] = $memberId;
            $weixin_info = unserialize($item['weixin_info']);
            $openid['openid'] = $weixin_info['openid'];
            $openid['nickname'] = $weixin_info['nickname'];
            $openid['provider_code'] = 'weixin';
            $openid['provider_openid'] = $weixin_info['appid'];
            $res = $openIdModel->add($openid);
            $openid['type'] = 'weixin';
            if($res == false) {
                //v($openIdModel->getDbError());
                $memberModel->rollback();
                return false;
            }
        }
        if ($item['member_qqopenid']) {
            //$openid['weixin_unionid'] = $item['weixin_unionid'];
            $openid['member_id'] = $memberId;
            $qq_info = unserialize($item['member_qqinfo']);
            $openid['openid'] = $item['member_qqopenid'];
            $openid['nickname'] = $qq_info['nickname'];
            $openid['provider_code'] = 'qq';
            $openid['provider_openid'] = $qq_info['appid'];

            $res = $openIdModel->add($openid);
            $openid['type'] = 'qq';
            if($res == false) {
                //v($openIdModel->getDbError());
                $memberModel->rollback();
                return false;
            }
        }
        if (isset($openid['member_id'])) {
            $auth['account_id'] = $memberId;
            $auth['module'] = 'openid_passport_trust';
            $auth['module_uid'] = $openid['type'].'_'.$openid['nickname'].'_'.$openid['openid'];
            $res = $authModel->add($auth);
            if($res == false) {
                //v($authModel->getDbError());
                $memberModel->rollback();
                return false;
            }
        }
        $memberModel->commit();
        return $memberId;
    }

    public function genUsername()
    {
        do{
            $username=random(16);
            $user = $this->getMemberInfo(array('member_name'=>$username));
            if(empty($user)&&C('OLD_STATUS')==true)
                $user = $this->getOldMemberInfo(array('login_account'=>$username));
        }while(!empty($user));
    }

    /**
     * 会员登录检查
     *
     */
    public function checkloginMember() {
        if($_SESSION['is_login'] == '1') {
            @header("Location: index.php");
            exit();
        }
    }

    /**
     * 检查会员是否允许举报商品
     *
     */
    public function isMemberAllowInform($member_id) {
        $condition = array();
        $condition['member_id'] = $member_id;
        $member_info = $this->getMemberInfo($condition,'inform_allow');
        if(intval($member_info['inform_allow']) === 1) {
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * 取单条信息
     * @param unknown $condition
     * @param string $fields
     */
    public function getMemberCommonInfo($condition = array(), $fields = '*') {
        return $this->table('member_common')->where($condition)->field($fields)->find();
    }

    /**
     * 插入扩展表信息
     * @param array $data
     * @return Ambigous <mixed, boolean, number, unknown, resource>
     */
    public function addMemberCommon($data) {
        return $this->table('member_common')->insert($data);
    }

    /**
     * 编辑会员扩展表
     * @param array $data
     * @param array $condition
     * @return mixed
     */
    public function editMemberCommon($data,$condition) {
        $memberCommon = $this->table('member_common')->where($condition)->find();
        if(!$memberCommon&&isset($condition['member_id'])) {
            $this->addMemberCommon($condition);
        }
        return $this->table('member_common')->where($condition)->update($data);
    }

    /**
     * 添加会员积分
     * @param unknown $member_info
     */
    public function addPoint($member_info) {
        if (!C('points_isuse') || empty($member_info)) return;

        //一天内只有第一次登录赠送积分
        if(trim(@date('Y-m-d',$member_info['member_login_time'])) == trim(date('Y-m-d'))) return;

        //加入队列
        $queue_content = array();
        $queue_content['member_id'] = $member_info['member_id'];
        $queue_content['member_name'] = $member_info['member_name'];
        QueueClient::push('addPoint',$queue_content);
    }

    /**
     * 添加会员经验值
     * @param unknown $member_info
     */
    public function addExppoint($member_info) {
        if (empty($member_info)) return;

        //一天内只有第一次登录赠送经验值
        if(trim(@date('Y-m-d',$member_info['member_login_time'])) == trim(date('Y-m-d'))) return;

        //加入队列
        $queue_content = array();
        $queue_content['member_id'] = $member_info['member_id'];
        $queue_content['member_name'] = $member_info['member_name'];
        QueueClient::push('addExppoint',$queue_content);
    }

    /**
     * 取得会员安全级别
     * @param unknown $member_info
     */
    public function getMemberSecurityLevel($member_info = array()) {
        $tmp_level = 0;
        if ($member_info['member_email_bind'] == '1') {
            $tmp_level += 1;
        }
        if ($member_info['member_mobile_bind'] == '1') {
            $tmp_level += 1;
        }
        if ($member_info['member_paypwd'] != '') {
            $tmp_level += 1;
        }
        return $tmp_level;
    }

    /**
     * 获得会员等级
     * @param bool $show_progress 是否计算其当前等级进度
     * @param int $exppoints  会员经验值
     * @param array $cur_level 会员当前等级
     */
    public function getMemberGradeArr($show_progress = false,$exppoints = 0,$cur_level = ''){
        $member_grade = C('member_grade')?unserialize(C('member_grade')):array();
        //处理会员等级进度
        if ($member_grade && $show_progress){
            $is_max = false;
            if ($cur_level === ''){
                $cur_gradearr = $this->getOneMemberGrade($exppoints, false, $member_grade);
                $cur_level = $cur_gradearr['level'];
            }
            foreach ($member_grade as $k=>$v){
                if ($cur_level == $v['level']){
                    $v['is_cur'] = true;
                }
                $member_grade[$k] = $v;
            }
        }
        return $member_grade;
    }

    /**
     * 获得某一会员等级
     * @param int $exppoints
     * @param bool $show_progress 是否计算其当前等级进度
     * @param array $member_grade 会员等级
     */
    public function getOneMemberGrade($exppoints,$show_progress = false,$member_grade = array()){
        if (!$member_grade){
            $member_grade = C('member_grade')?unserialize(C('member_grade')):array();
        }
        if (empty($member_grade)){//如果会员等级设置为空
            $grade_arr['level'] = -1;
            $grade_arr['level_name'] = '暂无等级';
            return $grade_arr;
        }

        $exppoints = intval($exppoints);

        $grade_arr = array();
        if ($member_grade){
            foreach ($member_grade as $k=>$v){
                if($exppoints >= $v['exppoints']){
                    $grade_arr = $v;
                }
            }
        }
        //计算提升进度
        if ($show_progress == true){
            if (intval($grade_arr['level']) >= (count($member_grade) - 1)){//如果已达到顶级会员
                $grade_arr['downgrade'] = $grade_arr['level'] - 1;//下一级会员等级
                $grade_arr['downgrade_name'] = $member_grade[$grade_arr['downgrade']]['level_name'];
                $grade_arr['downgrade_exppoints'] = $member_grade[$grade_arr['downgrade']]['exppoints'];
                $grade_arr['upgrade'] = $grade_arr['level'];//上一级会员等级
                $grade_arr['upgrade_name'] = $member_grade[$grade_arr['upgrade']]['level_name'];
                $grade_arr['upgrade_exppoints'] = $member_grade[$grade_arr['upgrade']]['exppoints'];
                $grade_arr['less_exppoints'] = 0;
                $grade_arr['exppoints_rate'] = 100;
            } else {
                $grade_arr['downgrade'] = $grade_arr['level'];//下一级会员等级
                $grade_arr['downgrade_name'] = $member_grade[$grade_arr['downgrade']]['level_name'];
                $grade_arr['downgrade_exppoints'] = $member_grade[$grade_arr['downgrade']]['exppoints'];
                $grade_arr['upgrade'] = $member_grade[$grade_arr['level']+1]['level'];//上一级会员等级
                $grade_arr['upgrade_name'] = $member_grade[$grade_arr['upgrade']]['level_name'];
                $grade_arr['upgrade_exppoints'] = $member_grade[$grade_arr['upgrade']]['exppoints'];
                $grade_arr['less_exppoints'] = $grade_arr['upgrade_exppoints'] - $exppoints;
                $grade_arr['exppoints_rate'] = round(($exppoints - $member_grade[$grade_arr['level']]['exppoints'])/($grade_arr['upgrade_exppoints'] - $member_grade[$grade_arr['level']]['exppoints'])*100,2);
            }
        }
        return $grade_arr;
    }
}
