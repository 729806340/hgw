<?php
/**
 * 地区
 *
 *
 *
 * @copyright  Copyright (c) 2007-2015 ShopNC Inc. (http://www.shopnc.net)
 * @license    http://www.shopnc.net
 * @link       http://www.shopnc.net
 * @since      File available since Release v1.1
 */


defined('ByShopWWI') or exit('Access Invalid!');

class shequ_tuanControl extends mobileHomeControl
{

    public function __construct()
    {
        parent::__construct();
        $exclude_port = array('new_upload_pic');
        if (!in_array($_GET['op'], $exclude_port)) {
             $this->checkLogin();
        }
    }

    protected function checkLogin()
    {
        $access = MD5($_REQUEST['member_id'] . "654123");
        if ($access != $_REQUEST['access_token']) {
            output_error('access_token错误');
        }
    }


    public function indexOp()
    {

    }

    //登录
    public function tuan_loginOp()
    {
        $obj_validate = new Validate();
        $user_name = $_POST['user_name'];
        $password = $_POST['password'];
        $obj_validate->validateparam = array(
            array("input" => $user_name, "require" => "true", "message" => "用户名必须输入"),
            array("input" => $password, "require" => "true", "message" => "密码必须输入"),
        );
        $error = $obj_validate->validate();
        if ($error != '') {
            output_error($error);
        }
        $model_member = Model('member');
        /** @var memberModel $model_member */
        $member_info = $model_member->getMemberInfo(array('member_name' => $user_name));
        $passwordHash = passwordHash($password, $member_info['password_salt'], $member_info['password_account'], $member_info['member_time']);
        if ($passwordHash != $member_info['member_passwd'] && preg_match('/^0?(13|15|17|18|14)[0-9]{9}$/i', $user_name)) {
            $member_info = $model_member->getMemberInfo(array('member_mobile' => $user_name));
            $passwordHash = passwordHash($password, $member_info['password_salt'], $member_info['password_account'], $member_info['member_time']);
        }
        if ($passwordHash != $member_info['member_passwd'] && (strpos($user_name, '@') > 0)) {
            $member_info = $model_member->getMemberInfo(array('member_email' => $user_name));
            $passwordHash = passwordHash($password, $member_info['password_salt'], $member_info['password_account'], $member_info['member_time']);
        }
        if (is_array($member_info) && $passwordHash == $member_info['member_passwd']) {
            $member_info = $model_member->getMemberInfo(array('member_id' => $member_info['member_id']));
            if (!$member_info['member_state']) {
                output_error('账号被停用');
            }
            $tuan_acc = $this->makeAcc($member_info);
            setNcCookie('member_id', $member_info['member_id'], 365 * 24 * 60 * 60);
            setNcCookie('member_name', $member_info['member_name'], 365 * 24 * 60 * 60);
            setNcCookie('member_turename', $member_info['member_turename'], 365 * 24 * 60 * 60);
            setNcCookie('tuan_access', $tuan_acc);
            $_SESSION['tuan_access'] = $tuan_acc;
            $_SESSION['member_id'] = $member_info['member_id'];
            $data['member_id'] = $member_info['member_id'];
            $data['access_token'] = $tuan_acc;
            output_data('登录成功', $data);
        } else {
//            process::addprocess('login');
            //showDialog($lang['login_index_login_fail'],'','error',$script);
            // showDialog(empty($member_info)?'用户名不存在':'密码错误','','error',$script);
            output_error(empty($member_info) ? '用户名不存在' : '密码错误');
        }
    }

    //注册
    public function registerOp()
    {
        /**
         * shequ_tuan_member.php
         */
    }

    public function geetestOp()
    {

        require_once BASE_CORE_PATH . '/lib/geetest/class.geetestlib.php';
        require_once BASE_CORE_PATH . '/lib/geetest/config.php';
        $GtSdk = new GeetestLib(CAPTCHA_ID, PRIVATE_KEY);

        $data = array(
            "user_id" => "test", # 网站用户id
            "client_type" => "web", #web:电脑上的浏览器；h5:手机上的浏览器，包括移动应用内完全内置的web_view；native：通过原生SDK植入APP应用的方式
            "ip_address" => "127.0.0.1" # 请在此处传输用户请求验证时所携带的IP
        );

        $status = $GtSdk->pre_process($data, 1);
        $_SESSION['gtserver'] = $status;
        $_SESSION['user_id'] = $data['user_id'];
        echo $GtSdk->get_response_str();
        exit;
    }


    private function makeAcc($member_info)
    {
        $access = MD5($member_info['member_id'] . "654123");
        return $access;
    }

    //认证成团长
    public function app_approveOp()
    {
        /** @var shequ_tuanzhangModel $model_tuanzhang */
        $model_tuanzhang = Model('shequ_tuanzhang');
        $data['member_id'] = $_POST['member_id'];
        $data['name'] = $_POST['name'];
        $data['phone'] = $_POST['phone'];
        $data['type'] = $_POST['type'];
        $data['sn'] = $_POST['sn'];
        $data['sn_image1'] = $_POST['sn_image1'];
        $data['sn_image2'] = $_POST['sn_image2'];
        //获取用户头像
        /** @var memberModel $model_member */
        $model_member = Model('member');
        $member_info = $model_member->getMemberInfo(array('member_id'=>$data['member_id']));
        $data['avatar']=$member_info['wx_user_avatar']?$member_info['wx_user_avatar']:"";
        $data['category'] = $_POST['category'];
        $data['store_name'] = $_POST['store_name']?$_POST['store_name']:"";
        if ($data['type'] == '2' && !in_array($data['category'], array('1', '2'))) {
            output_error('个体户请选择门店类型');
        }
        $data['bank_name'] = $_POST['bank_name'];
        $data['bank_ren'] = $_POST['bank_ren'];
        $data['bank_sn'] = $_POST['bank_sn'];
        if (!empty($_POST['id'])) {
            $condition['id'] = $_POST['id'];
            $data['update_time'] = time();
            $res = $model_tuanzhang->edit($condition, $data);
            if ($res) {
                output_data('更新成功');
            } else {
                output_error('更新失败');
            }
        } else {
            $data['add_time'] = time();
            $condition = array(
                'member_id' => $_POST['member_id'],
                'state' => array(array('eq', '0'), array('eq', '1'), 'or'),
            );
            $has = $model_tuanzhang->getOne($condition);
            if ($has) {
                output_error('请勿重复提交');
            }

            $res = $model_tuanzhang->insert($data);

            if ($res) {
                output_data('申请成功');
            }
        }
    }

    //图片接口
    public function new_upload_picOp()
    {
        $data = array();
        if ($_FILES) {
            foreach ($_FILES as $k => $file) {
                $upload = new UploadFile();
                $upload->set('default_dir', ATTACH_PATH . DS . 'tuanzhang' . DS);
                $upload->set('allow_type', array('jpg', 'jpeg', 'gif', 'png'));
                $upload->set('max_size', 1024 * 8);
                $thumb_width = '32';
                $thumb_height = '32';
                $upload->set('thumb_width', $thumb_width);
                $upload->set('thumb_height', $thumb_height);
                $result = $upload->upfile($k);
                if ($result) {
                    $file_name = $upload->file_name;
                    $data['pic'] = ATTACH_PATH . DS . 'tuanzhang' . DS . $file_name; //UPLOAD_SITE_URL
                    $data['http_pic'] = UPLOAD_SITE_URL . DS . ATTACH_PATH . DS . 'tuanzhang' . DS . $file_name;
                }
            }
        } else {
            output_error('请上传文件');
        }
        output_data($data);
    }

    //团长信息
    public function app_infoOp()
    {
        $member_id = $_POST['member_id'];
        /** @var shequ_tuanzhangModel $model_tuanzhang */
        $model_tuanzhang = Model('shequ_tuanzhang');
        $info = $model_tuanzhang->getOne(array('member_id' => $member_id));
        if ($info) {
            $data = array();
            $data['id'] = $info['id'];
            $data['member_id'] = $info['member_info'];
            $data['state'] = $info['state'];
            $data['name'] = $info['name'];
            $data['phone'] = $info['phone'];
            $data['sn'] = $info['sn'];
            $data['phone'] = $info['phone'];
            $data['state'] = $info['state'] == '1' ? '已审核' : '未审核';
            $data['avatar_http'] = UPLOAD_SITE_URL . DS . $info['avatar'];
            $data['avatar'] = $info['avatar'];
            $data['sn'] = $info['sn'];
            $data['sn_image1_http'] = UPLOAD_SITE_URL . DS . $info['sn_image1'];
            $data['sn_image1'] = $info['sn_image1'];
            $data['sn_image2_http'] = UPLOAD_SITE_URL . DS . $info['sn_image2'];
            $data['sn_image2'] = $info['sn_image2'];
            switch ($info['type']) {
                case 1:
                    $data['type_name'] = '社区工作人员';
                    break;
                case 2:
                    $data['type_name'] = '个体商户';
                    break;
                case 3:
                    $data['type_name'] = '自由职业者';
                    break;
                case 4:
                    $data['type_name'] = '公司员工';
                    break;
                case 0:
                    $data['type_name'] = ' ';
                    break;
            }
            $data['type'] = $info['type'];
            $data['category'] = $info['category'] ? $info['category']:" ";
            if ($data['category'] == '1') {
                $data['category_name'] = '餐饮';
            } elseif ($data['category'] == '2') {
                $data['category_name'] = '超市便利店';
            } else {
                $data['category_name'] = '';
            }
            $data['store_name'] = $info['store_name']?$info['store_name']:"";
            $data['bank_name'] = $info['bank_name'];
            $data['zhandui'] = $info['zhandui'];
            $data['area'] = $info['area'];
            $data['street'] = $info['street'];
            $data['community'] = $info['community'];
            $data['address_id'] = $info['address_id'];
            $data['address'] = $info['address'];
            $data['bank_sn'] = $info['bank_sn'];
            $data['bank_ren'] = $info['bank_ren'];
            $data['bank_name'] = $info['bank_name'];
             $this->getTotal($info['id'],$data);
            output_data($data);
        } else {
            output_data(array());
        }
    }

    /**
     * 团长首页统计信息显示接口
     * @param  string $id 团长id
     * @param  array  $data &团长信息
     */
    protected function getTotal($id,&$data){
        /** @var orderModel $model_order */
        $model_order = model('order');
        $data['total_amount'] = 0;
        $data['total_commis'] = 0;
        $data['total_order'] = 0;
        $data['total_join_num'] = 0;
        $data['today_commis'] = 0;
        $data['today_order'] = 0 ;
        $data['totay_join_num'] = 0;
        $data['today_amount'] = 0;
        $data['un_bill_amount'] = 0;
        $data['unpay_bill_amount'] = 0;

        $condition = array();
        $condition['shequ_tuan_id'] = array('gt','0');
        $condition['shequ_tz_id'] = $id;
        $condition['lock_state'] = '0';
        $condition['delete_state'] = '0';
        $condition['order_state'] = array('gt','10');
        $condition['refund_state'] = '0';

        $orders_ids  =  $model_order->getOrderList($condition,"","order_id,shequ_return_amount,buyer_id,order_amount,shequ_tz_bill_id,shequ_return_amount","order_id desc","999999");
        foreach ($orders_ids as $k=>$v){
            $data['total_amount'] += $v['order_amount'];
            $data['total_commis'] += $v['shequ_return_amount'];
        }
        $data['total_amount'] = ncPriceFormat($data['total_amount']);
        $data['total_join_num'] =  count(array_column($orders_ids,'order_id','buyer_id'));
        $data['total_commis']= ncPriceFormat($data['total_commis']);
        $data['total_order'] = count($orders_ids)>0?count($orders_ids):"0";
        //今日
        $today_start_unix = strtotime('today');
        $today_end_unix = strtotime('tomorrow')-1;
        $condition['add_time'] = array('between',array($today_start_unix,$today_end_unix));
        $orders_ids_today  =  $model_order->getOrderList($condition,"","order_id,shequ_return_amount","order_id desc","999999");
        foreach ($orders_ids_today as $k=>$v){
            $data['today_amount'] += $v['order_amount'];
            $data['today_commis'] += $v['shequ_return_amount'];
        }
        $data['today_join_num'] =  count(array_column($orders_ids_today,'order_id','buyer_id'));
        $data['today_commis']= ncPriceFormat($data['today_commis']);
        $data['today_order'] = count($orders_ids_today)>0?count($orders_ids_today):"0";
        $data['today_amount'] = ncPriceFormat($data['today_amount']);
        //   未生成结算单的 shequ_tz_bill_id = 0;  未结算金额, 可体现金额
         foreach($orders_ids as $k=>$v){
                if($v['shequ_tz_bill_id']=='0'){
                    $data['un_bill_amount'] += $v['shequ_return_amount'];
                    unset($orders_ids[$k]);
                }
         }
         $data['un_bill_amount'] = ncPriceFormat($data['un_bill_amount']);

        //待领取(结算单不等于4的 等于4的已经支付的 )  待发佣金
        $bill_ids = array_unique(array_column($orders_ids,'shequ_tz_bill_id'));
        /** @var shequ_billModel $model_shequ_bill */
        $bill_condition['ob_state'] = array('neq','4');
        $bill_condition['ob_id'] = array('in',$bill_ids);
        $model_shequ_bill = Model('shequ_bill');
        $bill_info = $model_shequ_bill->getList($bill_condition," ","ob_id desc","ob_id,ob_result_totals","9999999");
        foreach($bill_info as $v){
            $data['unpay_bill_amount'] +=$v['ob_result_totals'];
        }
        $data['unpay_bill_amount'] = ncPriceFormat($data['unpay_bill_amount']);
        //ADD INDEX `idx_shequ_total` (`shequ_tz_id`, `order_state`, `add_time`) USING BTREE ;
    }

    //团长地址添加
    public function tuan_address_addOp()
    {
        $data['member_id'] = $_POST['member_id'];
        $data['area'] = $_POST['area'];
        $data['area_id'] = $_POST['area_id'];
        $data['street'] = $_POST['street'];
        $data['street_id'] = $_POST['street_id'];
        $data['community'] = $_POST['community'];
        $data['community_id'] = $_POST['community_id'];
        $data['address'] = $_POST['address'];
        $data['longitude'] = $_POST['longitude'];
        $data['latitude'] = $_POST['latitude'];
        $data['name'] = $_POST['name'];
        $data['phone'] = $_POST['phone'];
        $data['building'] = $_POST['building'];
        //  $data['is_default'] = $_POST['is_default'];
        // $data['add_time'] = time();
        $model_shequ_address = Model('shequ_address');
        if (!empty($_POST['id'])) {
            $data['update_time'] = time();
            $res = $model_shequ_address->where(array('id' => $_POST['id']))->update($data);
            if ($res) {
                output_data('编辑成功');
            } else {
                output_error('编辑失败');
            }
        } else {
            /** @var shequ_tuanModel $model_shequ_tuanzhang */
            $model_shequ_tuanzhang = Model('shequ_tuanzhang');
            $tuanzhang_info = $model_shequ_tuanzhang->getOne(array('member_id' => $data['member_id']));
            if (empty($tuanzhang_info)) {
                output_error('参数错误');
            }
            $data['tuanzhang_id'] = $tuanzhang_info['id'];
            $data['add_time'] = time();
            $res = $model_shequ_address->insert($data);
            if ($res) {
                output_data('收货地址添加成功');
            } else {
                output_error('添加失败');
            }
        }

    }

    //团长收货地址列表
    public function tuan_address_listOp()
    {
        $member_id = $_POST['member_id'];
        $model_shequ_address = Model('shequ_address');
        $page = $this->page;
        $list = $model_shequ_address->where(array('member_id' => $member_id, 'is_del' => '0'))->page($page)->select();
        if (empty($list)) {
            output_data(array(), mobile_page(0));
        }
        foreach ($list as $k => $v) {
            $list[$k]['pin'] = $v['area'] . '/' . $v['street'] . '/' . $v['community'];
        }
        $count_page = $model_shequ_address->gettotalpage();
        output_data($list, mobile_page($count_page));
    }

    //团长收货地址删除
    public function tuan_address_delOp()
    {
        $ids = $_POST['ids'];
        $ids = explode(',', $ids);
        /** @var  shequ_addressModel $model_shequ_address */
        $model_shequ_address = Model('shequ_address');
        $condition['id'] = array('in', $ids);
        $update['is_del'] = '1';
        /*      $res = model()->execute("UPDATE `shopwwi_shequ_address` SET is_del=1 WHERE id in({$ids})");
              p($res);*/
        $res = $model_shequ_address->edit($condition, $update);
        if ($res) {
            output_data('删除成功');
        }
    }

    //团长收货地址编辑
    public function tuan_address_editOp()
    {

        if ($_GET['type'] == 'edit') {
            $id = $_POST['id'];
            $data['area'] = $_POST['area'];
            $data['area_id'] = $_POST['area_id'];
            $data['street'] = $_POST['street'];
            $data['street_id'] = $_POST['street_id'];
            $data['community'] = $_POST['community'];
            $data['community_id'] = $_POST['community_id'];
            $data['building'] = $_POST['building'];
            $data['address'] = $_POST['address'];
            $data['longitude'] = $_POST['longitude'];
            $data['latitude'] = $_POST['latitude'];
            $data['name'] = $_POST['name'];
            $data['phone'] = $_POST['phone'];
            $data['update_time'] = time();
            $model_shequ_address = Model('shequ_address');
            $res = $model_shequ_address->where(array('id' => $id))->update($data);
            if ($res) {
                output_data('更新成功');
            } else {
                output_error('更新失败');
            }
        } else {
            $id = $_POST['id'];
            $model_shequ_address = Model('shequ_address');
            $info = $model_shequ_address->where(array('id' => intval($id)))->find();
            if (!$info) {
                output_error('参数错误,数据不存在');
            }
            output_data($info);
        }

    }

    //团购模板列表
    public function tuangou_listOp()
    {
        /** @var  shequ_tuan_configModel $model_shequ_tuan */
        $model_shequ_tuan = Model('shequ_tuan_config');
        $time = time();
        $page = $this->page;
        if ($_POST['type'] && in_array($_POST['type'], array('1', '2'))) {
            $where['type'] = $_POST['type'];
        }
        if (!empty($_POST['config_tuan_title'])) {
            $where['config_tuan_title'] = array("like", "%" . $_POST['config_tuan_title'] . "%");
        }
        $where['config_state'] = "1";
        $where['_string'] = "(config_start_time<={$time} AND config_end_time>={$time})";
        $info = $model_shequ_tuan->getTuanConfigList($where, $page,"config_tuan_id desc");
        $count = $model_shequ_tuan->gettotalpage();
        //团购详情图片
        foreach ($info as $k => $v) {
            if (!empty($v['config_tuan_description'])) {
                $res = $this->makeUrl($v['config_tuan_description']);
                if ($res !== false) {
                    $info[$k]['config_tuan_description'] = $res;
                }
            }
            unset($info[$k]['config_tuan_description']);
            $info[$k]['config_start_time'] = $v['config_start_time'] ? date('Y-m-d', $v['config_start_time']) : "";
            $info[$k]['config_end_time'] = $v['config_end_time'] ? date('Y-m-d', $v['config_end_time']) : "";
            $info[$k]['send_product_date'] = $v['send_product_date'] ? date('Y-m-d', $v['send_product_date']) : "";
            $info[$k]['price_scope'] = $this->getPriceScope($v['config_tuan_id']);
            if(!empty($v['config_pic'])){  //todo 上限取消注释
                $info[$k]['config_pic'] = UPLOAD_SITE_URL.DS.ATTACH_COMMON.DS.$v["config_pic"];
            }else{
                $info[$k]['config_pic'] ="";
            }
        }
        output_data($info, mobile_page($count));
    }

    protected function getPriceScope($id){
        $result = array();
        /** @var shequ_tuan_config_goodsModel $model_shequ_tuan_config_goods */
        $model_shequ_tuan_config_goods = Model('shequ_tuan_config_goods');
        $goods_res = $model_shequ_tuan_config_goods->getTuanConfigGoodsList(array("tuan_config_id"=>$id));
        $goods_ids = array_column($goods_res,'goods_id');
        /** @var goodsModel $model_goods */
        $model_goods = Model('goods');
        $goods_condition['goods_id'] = array('in',$goods_ids);
        $goods_price = $model_goods->getGoodsList($goods_condition,"goods_id,goods_price");
        $goods_price = array_under_reset($goods_price,'goods_id');
        /** @var shequ_return_goodsModel $model_shequ_return_goods */
        $model_shequ_return_goods = Model('shequ_return_goods');
        $shequ_return_goods_list = $model_shequ_return_goods->getReturnGoodsList();
        $shequ_return_goods_list = array_under_reset($shequ_return_goods_list,'return_goods_id');
        foreach($goods_price as $k=>$v){
                if(isset($shequ_return_goods_list[$k])){
                    $result[] = $v['goods_price']*$shequ_return_goods_list[$k]['return_money_rate'];
                }
        }
         $str = '';
        $result['max'] = max($result)>0?max($result):"0";
        $result['min'] = min($result)>=0?min($result):"0";
         $str = ncPriceFormat($result['min']).'~'.ncPriceFormat($result['max']);
        return $str;
    }

    //团购模板详情
    public function tuangou_infoOp()
    {
        $config_tuan_id = $_POST['config_tuan_id'];
        $time = time();
        /** @var shequ_tuan_configModel $model_shequ_tuan_config */
        $model_shequ_tuan_config = Model('shequ_tuan_config');
        //$condition['config_state'] = "1";
        $condition['config_tuan_id'] = $config_tuan_id;
        //$condition['_string'] = "config_start_time<={$time} AND config_end_time>={$time}";
        $info = $model_shequ_tuan_config->getTuanConfigInfo($condition);
        if (empty($info)) {
            output_error('参数错误');
        }
        /** @var shequ_tuan_config_goodsModel $model_shequ_tuan_config_goods */
        $model_shequ_tuan_config_goods = Model('shequ_tuan_config_goods');
        $tuan_config_goods_list = $model_shequ_tuan_config_goods->getTuanConfigGoodsList(array('tuan_config_id' => $config_tuan_id), $this->page);
        if (empty($tuan_config_goods_list)) {
            output_data(array(), mobile_page(0));
        }
        $goods_ids = array_column($tuan_config_goods_list, 'goods_id');
        /** @var goodsModel $model_goods */
        $model_goods = Model('goods');
        $goods_list = $model_goods->getGoodsList(array('goods_id' => array('in', $goods_ids)));
        //$goods_list = array_under_reset($goods_list, )
        /** @var shequ_return_goodsModel $model_return_goods */
        $model_return_goods = Model('shequ_return_goods');
        $return_goods_list = $model_return_goods->getReturnGoodsList(array('return_goods_id' => array('in', $goods_ids)), 100);
        $return_goods_list = array_under_reset($return_goods_list, 'return_goods_id');
        /** @var goods_classModel $model_goods_class */
        $model_goods_class = Model('goods_class');
        $goods_class_list = $model_goods_class->getGoodsClassList(array('gc_id' => array('in', array_column($goods_list, 'gc_id'))));
        $goods_class_list = array_under_reset($goods_class_list, 'gc_id');
        foreach ($goods_list as $goods_key => $goods) {
            $goods_list[$goods_key]['commis'] = 0;
            $goods_list[$goods_key]['gc_name'] = '';
            $goods_list[$goods_key]['goods_image'] = thumb($goods, 360);
            if (isset($return_goods_list[$goods['goods_id']])) {
                $goods_list[$goods_key]['commis'] = $return_goods_list[$goods['goods_id']]['return_money_rate'] * $goods['goods_price'];
            }
            if (isset($goods_class_list[$goods['gc_id']])) {
                $goods_list[$goods_key]['gc_name'] = $goods_class_list[$goods['gc_id']]['gc_name'];
            }
        }
        output_data($goods_list, mobile_page($model_shequ_tuan_config_goods->gettotalpage()));
        /*$page = $this->page;
        if ($info) {

            $goods_info = $model_shequ_tuan_config_goods->where(array('tuan_config_id' => $config_tuan_id))->page($page)->select();
            $count = $model_shequ_tuan_config_goods->gettotalpage();
            foreach ($goods_info as $k => $v) {
                $goods_info[$k]['goods_image'] = thumb($v, 1280);
                $data = $this->getPrice($v['goods_id']);
                $goods_info[$k]['price'] = $data['price'];
                $goods_info[$k]['commis'] = $data['commis'];
                // $goods_info[$k]['gc_name'] = $this->getGc($v['gc_id']);
            }
            output_data($goods_info, mobile_page($count));
        } else {
            output_error('团购模板参数错误');
        }*/
    }


    private function makeUrl($str)
    {
        if (!empty($str)) {
            $data = explode("\"", $str);
            if (is_array($data) && !empty($data)) {
                $url = $_SERVER['REQUEST_SCHEME'] . "://" . $_SERVER['SERVER_NAME'] . $data[1];
                $preg = "/^<img src=\"(.*?)\"/";
                $new_str = preg_replace($preg, $url, $str);
                $new_str = strstr($new_str, " ", 1);
                return $new_str;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    //团购列表
    public function tuan_listOp()
    {
        $member_id = $_POST['member_id'];
        /** @var  shequ_tuanModel $model_shequ_tuan */
        $model_shequ_tuan = Model('shequ_tuan');
        $page = $this->page;
        $condition['member_id'] = $member_id;
        $condition['del'] = '0';
        $time = time();
        if ($_POST['type'] && in_array($_POST['type'], array('1', '2', '3'))) {
            switch ($_POST['type']) {
                case 1:
                    $condition['_string'] = "start_time<={$time} AND end_time>={$time}";
                    break;
                case 2:
                    $condition['_string'] = "start_time>={$time}";
                    break;
                case 3:
                    $condition['_string'] = "end_time<={$time}";
                    break;
                default:
                    break;
            }
        }
        if (!empty($_POST['name'])) {
            $condition['name'] = array("like", "%" . trim($_POST['name']) . "%");
        }
        $list = $model_shequ_tuan->getList($condition, $page, "start_time desc");
        /** @var orderModel $model_orders */
        $model_orders = Model('order');
        if ($list) {
            $data = array();
            foreach ($list as $k => $v) {
                $data[$k]['id'] = $v['id'];
                $data[$k]['member_id'] = $v['member_id'];
                $data[$k]['name'] = $v['name'];
                $data[$k]['sn'] = $v['sn'];
                $data[$k]['start_time'] = $v['start_time'] ? date("Y-m-d", $v['start_time']) : "";
                $data[$k]['end_time'] = $v['end_time'] ? date("Y-m-d", $v['end_time']) : "";
                if ($v['start_time'] <= $time && $v['end_time'] >= $time) {
                    $data[$k]['type'] = '进行中';
                    $data[$k]['type_code'] = '1';
                } elseif ($v['start_time'] >= $time) {
                    $data[$k]['type'] = '未开始';
                    $data[$k]['type_code'] = '2';
                } elseif ($v['end_time'] <= $time) {
                    $data[$k]['type'] = '已结束';
                    $data[$k]['type_code'] = '3';
                }
                if ($v['state'] == '10' || $v['state'] == '20' || $v['state'] == '30' || $v['state'] == '0') {
                    $data[$k]['shouhuo'] = '未确认收货';
                } else {
                    $data[$k]['shouhuo'] = '已确认收货';
                }
                $order_res = $model_orders->getOrderList(array('delete_state' => '0', 'shequ_tuan_id' => $v['id']), '');
                if (!empty($order_res) || ($v['start_time'] < $time && $v['end_time'] > $time)) {
                    $data[$k]['is_del'] = '1';
                    $i = 0;
                    foreach ($order_res as $o_k => $o_v) {
                        $res = $model_orders->getOrderOperateState('receive', $o_v);
                        if ($res) {
                            $i++;
                        }
                    }
                    $i and $data[$k]['plqr'] = '1' or $data[$k]['plqr'] = '0';
                } else {
                    $data[$k]['plqr'] = '0';
                    $data[$k]['is_del'] = '0';
                }
                $data[$k]['join_num'] = $this->getJoinNum($order_res);  //每个团的参加人数
                $data[$k]['trans_amount'] = $this->getTransAmount($order_res);//交易总金额
                $data[$k]['commis_amount'] = $this->getCommisAmount($order_res);
                $data[$k]['config_pic'] = $v['config_pic'];//$data[$k]['config_pic'] = !empty($v['config_pic'])?UPLOAD_SITE_URL.DS.ATTACH_COMMON.DS.$v['config_pic']:"";
                $data[$k]['config_type'] = $v['type'];
                $data[$k]['qr_code'] = $this->makeerweima($v['id']);
            }
            output_data($data, mobile_page($model_shequ_tuan->gettotalpage()));
        } else {
            output_data(array(), mobile_page(0));
        }

    }

    /**
     * 参与人数
     * @param array $order_res 当前团的订单集合
     * @return string num
     */
    public function getJoinNum($order_res)
    {
        //过滤订单
        /** @var orderModel $model_orders */
        $model_orders = Model('order');
        $result = array();
        foreach ($order_res as $k => $v) {
            if ($v['order_state'] > 10 && !$v['lock_state']&&$v['refund_state']=='0') {
                $result[] = $v;
            }
        }
        //去重
        $num = count(array_column($result, 'buyer_id', 'buyer_id'));
        return $num ? $num : "0";
    }

    /**
     * 交易金额
     * @param array $order_res
     * @return string $amount
     */
    public function getTransAmount($order_res)
    {
        /** @var orderModel $model_orders */
        $model_orders = Model('order');
        $result = array();
        $amount = 0;
        foreach ($order_res as $k => $v) {
            if ($v['order_state'] > 10 && !$v['lock_state']&&$v['refund_state']=='0') {
                $result[] = $v;
                $amount += $v['order_amount'];
            }
        }
        return ncPriceFormat($amount ? $amount : "0");
    }

    /**
     * 佣金金额
     * @param $order_res
     * @return string $commis_amount
     */
    public function getCommisAmount($order_res)
    {
        /** @var orderModel $model_orders */
        $model_orders = Model('order');
        $result = array();
        $commis_amount = 0;
        foreach ($order_res as $k => $v) {
            if ($v['order_state'] > 10 && !$v['lock_state']&&$v['refund_state']=='0') {
                $result[] = $v;
                $commis_amount += $v['shequ_return_amount'];
            }
        }
        return ncPriceFormat($commis_amount ? $commis_amount : "0");
    }


    public function makeerweima($tuan_id)
    {
        /** @var  wx_small_appLogic $wx_samll_app */
        $wx_samll_app = Logic('wx_small_app');
        return $wx_samll_app->getQrHttp('pages/community/community', $tuan_id);
        try {
            $res = $wx_samll_app->getQr('pages/community/community', $tuan_id);  //todo 发布之后改地址
            $type = getimagesizefromstring($res)['mime'];
            $base64String = 'data:' . $type . ';base64,' . chunk_split(base64_encode($res));
            return $base64String;
        } catch (Exception $e) {
            //默认
            $base64String = '';
            return $base64String;
        }
    }

    //删除团
    public function del_tuanOp()
    {
        $tuan_id = intval($_POST['tuan_id']);
        $member_id = intval($_POST['member_id']);
        //查询用户是否发起了该拼团
        /** @var shequ_tuanModel $shequ_tuan_model */
        $shequ_tuan_model = Model('shequ_tuan');
        $tuan_info = $shequ_tuan_model->getOne(array('id' => $tuan_id, 'member_id' => $member_id, 'del' => '0'));
        if (empty($tuan_info)) {
            output_error('参数不正确');
        }
        //进行中的团不能删除
        if ($tuan_info['start_time'] < time() && $tuan_info['end_time'] > time()) {
            output_error('进行中的活动不能删除');
        }

        /** @var  $model_orders */
        $model_orders = Model('order');
        $condition = array();
        $condition['delete_state'] = 0;
        $condition['shequ_tuan_id'] = $tuan_id;
        $fields = "order_id,tuan_id,order_type,order_sn,chain_code,pay_sn,store_id,store_name,goods_amount,order_amount,rcb_amount,pd_amount,shipping_fee,add_time,payment_code,payment_time,finnshed_time,lock_state,refund_state,order_state,evaluation_state,shipping_code";
        $info = $model_orders->getOrderList($condition, $this->page, $fields);
        if ($info) {
            output_error('团购已有订单,无法删除');
        } else {
            $data['del'] = '1';
            $res = $shequ_tuan_model->edit(array('id' => $tuan_id), $data);
            if ($res) {
                output_data('删除成功');
            }
        }
    }

    //批量确认
    public function queren_tuanOp()
    {
        $tuan_id = $_POST['tuan_id'];
        $member_id = intval($_POST['member_id']);
        $member_name = Model('member')->where(array('member_id' => $member_id))->field('member_name')->find();
        /** @var shequ_tuanModel $shequ_tuan_model */
        $shequ_tuan_model = Model('shequ_tuan');
        $tuan_info = $shequ_tuan_model->getOne(array('id' => $tuan_id, 'member_id' => $member_id));
        if (empty($tuan_info)) {
            output_error('参数不正确');
        }
        /** @var  $model_orders */
        $model_orders = Model('order');
        $condition = array();
        $condition['delete_state'] = 0;
        $condition['shequ_tuan_id'] = $tuan_id;
        $condition['order_state'] = ORDER_STATE_SEND;
        $condition['lock_state'] = 0;
        $fields = "order_id,shequ_tuan_id,order_type,order_sn,chain_code,pay_sn,store_id,store_name,goods_amount,order_amount,rcb_amount,pd_amount,shipping_fee,add_time,payment_code,payment_time,finnshed_time,lock_state,refund_state,order_state,evaluation_state,shipping_code";
        $info = $model_orders->getOrderList($condition, $this->page, $fields);
        if ($info) {
            foreach ($info as $v) {
                $result = Logic('order')->changeOrderStateReceive($v, 'chain', $member_name['member_name']);
            }
            output_data('确认成功');
        } else {
            output_error('操作失败,请刷新页面');
        }

    }

    //单条确认
    public function queren_oneOp()
    {
        $order_id = $_POST['order_id'];
        $member_id = intval($_POST['member_id']);
        $member_name = Model('member')->where(array('member_id' => $member_id))->field('member_name')->find();
        /** @var  $model_orders */
        $model_orders = Model('order');
        $condition = array();
        $condition['delete_state'] = 0;
        $condition['order_state'] = ORDER_STATE_SEND;
        $condition['lock_state'] = 0;
        $condition['order_id'] = $order_id;
        $fields = "order_id,tuan_id,order_type,order_sn,chain_code,pay_sn,store_id,store_name,goods_amount,order_amount,rcb_amount,pd_amount,shipping_fee,add_time,payment_code,payment_time,finnshed_time,lock_state,refund_state,order_state,evaluation_state,shipping_code";
        $info = $model_orders->getOrderList($condition, $this->page, $fields);
        if ($info) {
            foreach ($info as $v) {
                $result = Logic('order')->changeOrderStateReceive($v, 'chain', $member_name['member_name']);
            }
            output_data('确认成功');
        } else {
            output_error('操作失败,请刷新页面');
        }
    }
}
