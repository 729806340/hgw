<?php
/**
 * 分销退款订单管理
 *
 *
 *
 * * @汉购网 (c) 2015-2018 ShopWWI Inc. (http://www.hangowa.com)
 * @license    http://www.shopwwi.c om
 * @link       交流群号：
 * @since      汉购网提供技术支持 授权请购买shopnc授权
 */



defined('ByShopWWI') or exit ('Access Invalid!');
class fenxiao_refund_errorlogControl extends BaseSellerControl {
    public function __construct() {
        parent::__construct ();
        Language::read ('member_store_goods_index');
    }
    public function indexOp()
    {
        $this->errorlog();
    }

    /**
     * 分销导入订单，退款等错误日志
     */
    function errorlog()
    {
        $oid = trim($_GET['oid']);
        $keywords = trim($_GET['keywords']);
        $logtype = $_GET['logtype'];
        $order = Model('b2c_order_fenxiao_error' );

        $store_id = $this->store_info['store_id'];
        $member_fenxiao = Model('member_fenxiao')->getMembeFenxiaoList(array('filter_store_id'=>$store_id));
        $uids = array_column($member_fenxiao, 'member_id');

        $conditions = array() ;
        $conditions['sourceid'] = array('in', $uids);
        if ($oid) $conditions['orderno'] = $oid ;
        if ($logtype) $conditions['log_type'] = $logtype ;
        if (!empty($keywords)) $conditions['error'] = array('like' , '%'.$keywords.'%');

        $result = $order->getLogList($conditions);

        Tpl::output('show_page', $order->showpage());
        Tpl::output('errorlog', $result);
        Tpl::showpage('fenxiao_refund_errorlog.index');
    }
}
