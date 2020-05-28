<?php
/**
 * 分销渠道管理
 *
 *
 *
 * * @汉购网 (c) 2015-2018 ShopWWI Inc. (http://www.hangowa.com)
 * @license    http://www.shopwwi.c om
 * @link       交流群号：
 * @since      汉购网提供技术支持 授权请购买shopnc授权
 */



defined('ByShopWWI') or exit ('Access Invalid!');
class fenxiao_channelControl extends BaseSellerControl {
    public function __construct() {
        parent::__construct ();
        Language::read ('member_store_goods_index');
    }
    public function indexOp() {
        $this->channelOp();
    }

    /**
     * 仓库中的商品列表
     */
    public function channelOp() {
        $action = $_GET['action'];
        if (is_null($action) || empty($action)) $action = 'getlist';
        $model_goods = Model('goods');
        switch ($action) {
            case 'getlist' :
                $channel_name = $_GET['channel_name'];
                $conditions = array();
                $conditions['filter_store_id'] = $this->store_info['store_id'];
                if (!empty($channel_name))
                    $conditions['member_cn_code'] = array('like', '%' . $channel_name . '%');

                $result = Model('member_fenxiao')->getMembeFenxiaoList($conditions);
                Tpl::output('show_page', $model_goods->showpage());
                Tpl::output('channel_list', $result);
                Tpl::showpage('fenxiao_channel.index');
                break;
            case 'add':
                if ($_POST) {
                    $data['member_cn_code'] = $_POST['member_cn_code'];
                    $data['member_en_code'] = $_POST['member_en_code'];
                    $data['is_sign'] = $_POST['is_sign'];
                    $data['billing_mode'] = $_POST['billing_mode'];
                    $data['member_passwd'] = $_POST['password'];
                    if (empty($data['member_passwd'])) {
                        showMessage("密码不能为空！", '', 'json');exit ;
                    }
                    $res = Model('member_fenxiao')->getMemberIdByCode($data['member_en_code']);
                    if ($res) {
                        showMessage("英文名已被使用,请重新设置!", '', 'json');exit;
                    }
                    $res = Model('member_fenxiao')->getMembeFenxiaoInfo(array('member_cn_code' => $data['member_cn_code']));
                    if (!empty($res)) {
                        showMessage("渠道名已被使用,请重新设置!", '', 'json');exit;
                    }
                    $res = Model('member')->getMemberInfo(array("member_name"=>$data['member_en_code']));
                    if ($res) {
                        showMessage("英文名已经使用了,请重新设置!", '', 'json');exit;
                    }
                    $res = Model('member_fenxiao')->addFenxiao($data, $this->store_info['store_id']);
                    if ($res){
                        $message = "您好！渠道新增：{$data['member_cn_code']},登录名：{$data['member_en_code']},请知悉。";
                        $email	= new Email();
                        $res = $email->send_sys_email('handong@hansap.com','渠道新增', $message);
                        if (!$res) {
                            throw new Exception('分销会员邮件发送失败！', '', 'json');
                        }
                        showMessage('添加成功', '', 'json');exit;
                    }else {
                        showMessage('添加失败', '', 'json');exit;
                    }
                }

                Tpl::setLayout('null_layout');
                Tpl::showpage('fenxiao_channel.add');
                break;
            case 'edit':
                // 获取该渠道
                $member_fenxiao_id = intval($_REQUEST['member_fenxiao_id']);
                $member_fenxiao = Model('member_fenxiao')->getMembeFenxiaoInfo(array('filter_store_id'=>$this->store_info['store_id'], 'id' => $member_fenxiao_id));
                if ($_POST) {
                    $data['is_sign'] = $_POST['is_sign'];
                    $data['billing_mode'] = $_POST['billing_mode'];
                    $res = Model('member_fenxiao')->updates(array('filter_store_id'=>$member_fenxiao['filter_store_id'], 'id' => $member_fenxiao_id), $data);
                    if ($res){
                        showMessage('编辑成功', '', 'json');exit;
                    }else {
                        showMessage('编辑失败', '', 'json');exit;
                    }
                }
                Tpl::output('member_fenxiao', $member_fenxiao);
                Tpl::setLayout('null_layout');
                Tpl::showpage('fenxiao_channel.edit');
                break;
        }
    }
}
