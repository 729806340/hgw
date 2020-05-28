<?php
/**
 * 领取免费红包
 * * @汉购网 (c) 2015-2018 ShopWWI Inc. (http://www.hangowa.com)
 * @license    http://www.shopwwi.c om
 * @link       交流群号：
 * @since      汉购网提供技术支持 授权请购买shopnc授权
 */



defined('ByShopWWI') or exit('Access Invalid!');
class redpacketControl extends BaseHomeControl{
    public function __construct() {
        parent::__construct();
        //判断系统是否开启红包功能
        if (C('redpacket_allow') != 1){
            showDialog('系统未开启红包功能','index.php','error');
        }
    }
    /**
     * 免费红包页面
     */
    public function getredpacketOp() {
        parent::checkLogin();
        $t_id = intval($_GET['tid']);
        $error_url = getReferer();
        if (!$error_url){
            $error_url = 'index.php';
        }
        if($t_id <= 0){
            showDialog('红包信息错误',$error_url,'error');
        }
        $model_redpacket = Model('redpacket');
        //获取领取方式
        $gettype_array = $model_redpacket->getGettypeArr();
        //获取红包状态
        $templatestate_arr = $model_redpacket->getTemplateState();
        //查询红包模板详情
        $where = array();
        $where['rpacket_t_id'] = $t_id;
        $where['rpacket_t_gettype'] = $gettype_array['free']['sign'];
        $where['rpacket_t_state'] = $templatestate_arr['usable']['sign'];
        //$where['rpacket_t_start_date'] = array('elt',time());
        $where['rpacket_t_end_date'] = array('egt',time());
        $template_info = $model_redpacket->getRptTemplateInfo($where);
        if (empty($template_info)){
            showDialog('红包信息错误',$error_url,'error');
        }
        if ($template_info['rpacket_t_total']<=$template_info['rpacket_t_giveout']){//红包不存在或者已兑换完
            showDialog('红包已兑换完',$error_url,'error');
        }
        TPL::output('template_info',$template_info);
        Tpl::showpage('redpacket.getredpacket');
    }
    /**
     * 领取免费红包
     */
    public function getredpacketsaveOp() {
        parent::checkLogin();
        $t_id = intval($_GET['tid']);
        if($t_id <= 0){
            showDialog('红包信息错误','','error');
        }
        $model_redpacket = Model('redpacket');
        if($model_redpacket->isDiamondRpt($t_id)){
            showDialog('该红包只能由系统发放','','error');
        }
        //验证是否可领取红包
        $data = $model_redpacket->getCanChangeTemplateInfo($t_id, intval($_SESSION['member_id']));
        if ($data['state'] == false){
            showDialog($data['msg'], '', 'error');
        }
        try {
            $model_redpacket->beginTransaction();
            //添加红包信息
            $data = $model_redpacket->exchangeRedpacket($data['info'], $_SESSION['member_id'], $_SESSION['member_name']);
            if ($data['state'] == false) {
                throw new Exception($data['msg']);
            }
            $model_redpacket->commit();
            showDialog('红包领取成功', MEMBER_SITE_URL.'/index.php?act=member_redpacket&op=index', 'succ');
        } catch (Exception $e) {
            $model_redpacket->rollback();
            showDialog($e->getMessage(), '', 'error');
        }
        
    }

    public function skuOp()
    {
        $t_id = intval($_GET['tid']);
        if($t_id <= 0){
            showDialog('红包信息错误','index.php','error');
        }

        /** @var redpacketModel $model_redpacket */
        $model_redpacket = Model('redpacket');
        //查询模板信息
        $where = array();
        $where['rpacket_t_id'] = $t_id;
        $t_info = $model_redpacket->getRptTemplateInfo($where);
        if (!$t_info){
            showDialog('红包信息错误','index.php','error');
        }
        //获取商品列表
        /** @var goodsModel $goodsModel */
        $goodsModel = Model('goods');
        $t_info['goodsList'] = $goodsModel->getGoodsList(array('goods_id'=>array('in',$t_info['rpacket_t_skus'])), '*', '', '', 0,1000);

        $goodsClassModel = Model('goods_class');
        $t_info['goodsClassList'] = $goodsClassModel->getGoodsClassList(array('gc_id'=>array('in',$t_info['rpacket_t_classes'])), '*', '', '', 0,1000);
        foreach ($t_info['goodsClassList'] as $k => $v) {
            $menu = $goodsClassModel->getGoodsClassLineForTag($v['gc_id']);
            $t_info['goodsClassList'][$k]['menu'] = $menu['gc_tag_name'];
        }

        $t_info['rpacket_t_price'] = intval($t_info['rpacket_t_price']);
        Tpl::output('nav_link_list', array(
            array('link'=>'/','title'=>'首页'),
            array('title'=>'红包适用范围'),
        ) );

        TPL::output('t_info',$t_info);
        TPL::output('goods_list',$t_info['goodsList']);
        TPL::output('goods_class_list',$t_info['goodsClassList']);
        Tpl::showpage('redpacket.sku');
    }
}
