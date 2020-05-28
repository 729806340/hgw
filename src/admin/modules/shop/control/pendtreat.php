<?php
/**
 * 退款管理
 *
 *
 *
 *
 * @汉购网提供技术支持 授权请购买shopnc授权
 * @license    http://www.hangowa.com
 * @link       交流群号：
 */



defined('ByShopWWI') or exit('Access Invalid!');
class pendtreatControl extends SystemControl{
    const EXPORT_SIZE = 1000;
    private $links = array(
        array('url'=>'act=refund&op=index','text'=>'所有记录'),
    );

    public function __construct(){
        parent::__construct();
        $model_refund = Model('refund_return');
        $model_refund->getRefundStateArray();
        $op = substr($_GET['op'], -2) == 'Op' ? $_GET['op'] : $_GET['op']."Op" ;
        Tpl::output('top_link',$this->sublink($this->links,$_GET['op']));
    }


    public function indexOp() {
        Tpl::setDirquna('shop');
        Tpl::showpage('pendtreat_all.list');
    }

    /**
     * 封装共有查询代码
     */
    private function _get_condition($condition) {
        if ($_REQUEST['query'] != '' && in_array($_REQUEST['qtype'],array('order_sn','store_name','buyer_name','goods_name','refund_sn'))) {
            $condition[$_REQUEST['qtype']] = array('like',"%{$_REQUEST['query']}%");
        }
        if ($_GET['keyword'] != '' && in_array($_GET['keyword_type'],array('order_sn','store_name','buyer_name','goods_name','refund_sn'))) {
            if ($_GET['jq_query']) {
                $condition[$_GET['keyword_type']] = $_GET['keyword'];
            } else {
                $condition[$_GET['keyword_type']] = array('like',"%{$_GET['keyword']}%");
            }
        }
        if (!in_array($_GET['qtype_time'],array('add_time','seller_time','admin_time'))) {
            $_GET['qtype_time'] = null;
        }
        $if_start_time = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_start_date']);
        $if_end_time = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_end_date']);
        $start_unixtime = $if_start_time ? strtotime($_GET['query_start_date']) : null;
        $end_unixtime = $if_end_time ? strtotime($_GET['query_end_date']): null;
        if ($_GET['qtype_time'] && ($start_unixtime || $end_unixtime)) {
            $condition[$_GET['qtype_time']] = array('time',array($start_unixtime,$end_unixtime));
        }
        if (floatval($_GET['query_start_amount']) > 0 && floatval($_GET['query_end_amount']) > 0) {
            $condition['refund_amount'] = array('between',floatval($_GET['query_start_amount']).','.floatval($_GET['query_end_amount']));
        }
        if ($_GET['refund_state'] == 2) {
            $condition['refund_state'] = 2;
        }
        if( !empty($_GET['refund_state']) ) {
            $condition['refund_state'] = intval($_GET['refund_state']) ;
        }
        $sort_fields = array('buyer_name','store_name','goods_id','refund_id','seller_time','refund_amount','buyer_id','store_id');
        if ($_REQUEST['sortorder'] != '' && in_array($_REQUEST['sortname'],$sort_fields)) {
            $order = $_REQUEST['sortname'].' '.$_REQUEST['sortorder'];
        }
        if( $_GET['fxsellerdo'] == 1 ) {
            //$order = 'seller_time desc';
        }
        return array($condition,$order);
    }

    /**
     * 所有记录
     */
    public function get_all_xmlOp() {
        $model_refund = Model('refund_return');
        $condition = array();
        list($condition,$order) = $this->_get_condition($condition);
        $refund_list = $model_refund->getPendtreatList($condition,!empty($_POST['rp']) ? intval($_POST['rp']) : 15,$order);
        $oids = $data = array();
        $data['now_page'] = $model_refund->shownowpage();
        $data['total_num'] = $model_refund->gettotalnum();
        $pic_base_url = UPLOAD_SITE_URL.'/'.ATTACH_PATH.'/refund/';
        $operation='';
        $source_url = array(
            'pinduoduo' => 'http://mms.yangkeduo.com/Pdd.html#/aftersales/after_sale_list/index',
            'renrendian' => 'https://mch.wxrrd.com/shop/order/list/feedback',
            'youzan' => 'https://www.youzan.com/v2/trade/order#list&p=1&type=feedback&state=all&orderby=book_time&order_es_tag=&tuanId=&order=desc&page_size=20&disable_express_type=',
            'juanpi' => 'https://user.juanpi.com/refund/backList',
            'fanli' => 'http://portal.shzyfl.cn/order/exchangeOrderManagerView.do',
            'zhe800' => 'https://zhaoshang.zhe800.com/',
            'gegejia' => 'http://seller.gegejia.com/yggSellerManager/seller/toLogin',
            'mengdian' => 'http://zs.mengdian.com/main',
            'chuchujie' => 'http://seller.chuchujie.com',
            'beibeiwang' => 'http://open.beibei.com/console/application.html'
        );

//        $source_url = array(
//            'pinduoduo' => 'http://mms.yangkeduo.com/Pdd.html#Login',
//            'renrendian' => 'http://mch.wxrrd.com/auth/login',
//            'youzan' => 'https://login.youzan.com/sso',
//            'juanpi' => 'https://user.juanpi.com/login',
//            'fanli' => 'http://portal.shzyfl.cn/login/toLogin.do',
//            'zhe800' => 'https://zhaoshang.zhe800.com/',
//            'gegejia' => 'http://seller.gegejia.com/yggSellerManager/seller/toLogin',
//            'mengdian' => 'http://zs.mengdian.com/main',
//            'chuchujie' => 'http://seller.chuchujie.com',
//            'beibeiwang' => 'http://open.beibei.com/console/application.html'
//        );

        $source_name = array(
            'pinduoduo' => '拼多多',
            'renrendian' => '人人店',
            'youzan' => '有赞',
            'juanpi' => '卷皮',
            'fanli' => '返利',
            'zhe800' => '折800',
            'gegejia' => '格格家',
            'mengdian' => '萌店',
            'chuchujie' => '楚楚街',
            'beibeiwang' => '贝贝网'
        );

        foreach ($refund_list as $k => $refund_info) {
            $list = array();
            $operation='';
            $operation .= "<span class='btn'><em><i class='fa fa-cog'></i>设置 <i class='arrow'></i></em><ul>";
            $operation .= "<li><a onclick='refund_change(".$refund_info['refund_id'].",1)'>退款</a></li>";
            $operation .= "<li><a onclick='refund_change(".$refund_info['refund_id'].",2)'>退货</a></li>";
            $operation.="</ul>";
            $list['operation']=$operation;
            if($refund_info['refund_way'] == 'fenxiao'){
                $oWhere ['order_id'] = $refund_info['order_id'] ;
                $orders = Model()->table('orders')->field('order_id, fx_order_id')->where( $oWhere )->find();
                $list['fx_order_id'] = $orders['fx_order_id'];

                $condition = array();
                $condition['orderno'] = $orders['fx_order_id'];
                $source = Model()->table('b2c_order_fenxiao')->where($condition)->find();
                $list['source'] = "<a target='_blank' href='{$source_url[$source['source']]}'>{$source_name[$source['source']]}</a>";

            } else {
                $list['fx_order_id'] = '';
                $list['source'] = '';
            }
            $list['refund_sn'] = $refund_info['refund_sn'];
            $list['refund_amount'] = ncPriceFormat($refund_info['refund_amount']);
            if(!empty($refund_info['pic_info'])) {
                $info = unserialize($refund_info['pic_info']);
                if (is_array($info) && !empty($info['buyer'])) {
                    foreach($info['buyer'] as $pic_name) {
                        $list['pic_info'] .= "<a href='".$pic_base_url.$pic_name."' target='_blank' class='pic-thumb-tip' onMouseOut='toolTip()' onMouseOver='toolTip(\"<img src=".$pic_base_url.$pic_name.">\")'><i class='fa fa-picture-o'></i></a> ";
                    }
                    $list['pic_info'] = trim($list['pic_info']);
                }
            }
            if (empty($list['pic_info'])) $list['pic_info'] = '';
            $list['buyer_message'] = "<span title='{$refund_info['buyer_message']}'>{$refund_info['buyer_message']}</span>";
            $list['add_times'] = date('Y-m-d H:i:s',$refund_info['add_time']);
            $list['goods_name'] = $refund_info['goods_name'];
            if ($refund_info['goods_id'] > 0) {
                $list['goods_name'] = "<a class='open' title='{$refund_info['goods_name']}' href='". urlShop('goods', 'index', array('goods_id' => $refund_info['goods_id'])) .
                    "' target='blank'>{$refund_info['goods_name']}</a>";
            }
            $state_array = $model_refund->getRefundStateArray('seller');
            $list['seller_state'] = $state_array[$refund_info['seller_state']];

            $admin_array = $model_refund->getRefundStateArray('admin');
            $list['refund_state'] = $refund_info['seller_state'] == 2 ? $admin_array[$refund_info['refund_state']]:'';

            $list['seller_message'] = "<span title='{$refund_info['seller_message']}'>{$refund_info['seller_message']}</i>";
            $list['admin_message'] = "<span title='{$refund_info['admin_message']}'>{$refund_info['admin_message']}</span>";
            $list['seller_times'] = !empty($refund_info['seller_time']) ? date('Y-m-d H:i:s',$refund_info['seller_time']) : '';
            if ($refund_info['goods_image'] != '') {
                $list['goods_image'] = "<a href='".thumb($refund_info,360)."' target='_blank' class='pic-thumb-tip' onMouseOut='toolTip()' onMouseOver='toolTip(\"<img src=".thumb($refund_info,240).">\")'><i class='fa fa-picture-o'></i></a> ";
            } else {
                $list['goods_image'] = '';
            }
            $list['goods_id'] = !empty($refund_info['goods_id']) ? $refund_info['goods_id'] : '';
            $list['order_sn'] = $refund_info['order_sn'];
            $list['buyer_name'] = $refund_info['buyer_name'];
            $list['buyer_id'] = $refund_info['buyer_id'];
            $list['store_name'] = $refund_info['store_name'];
            $list['store_id'] = $refund_info['store_id'];
            $list['order_id'] = $refund_info['order_id'];
            $list['refund_way'] = orderPaymentName($refund_info['refund_way']);
            $list['refund_name'] = $refund_info['refund_name'];
            $list['refund_account'] = $refund_info['refund_account'];



            $data['list'][$refund_info['refund_id']] = $list;
            $oids[] = $refund_info['order_id'] ;
        }
        exit(Tpl::flexigridXML($data));
    }

    //修改退货退款状态
    function  change_stateOp(){
        $condition['refund_id']=intval($_GET['refund_id']);
        $model_refund = Model('refund_return');
        $state_type=$_GET['state_type'];
        $data['refund_type']='2';
        if($state_type=='1'){
            $data['refund_type']='1';
        }
        $result=$model_refund->editRefundReturn($condition,$data);
        if(!$result){
           exit(json_encode(array('state'=>'0','msg'=>'修改数据失败')));
        }
         exit(json_encode(array('state'=>'1','msg'=>'修改成功')));
    }

    /**
     * csv导出
     */
    public function export_step1Op() {
        $model_refund = Model('refund_return');
        $condition = array();
        if (preg_match('/^[\d,]+$/', $_GET['refund_id'])) {
            $_GET['refund_id'] = explode(',',trim($_GET['refund_id'],','));
            $condition['refund_id'] = array('in',$_GET['refund_id']);
        }
        list($condition,$order) = $this->_get_condition($condition);
        if (!is_numeric($_GET['curpage'])){
            $count = $model_refund->getPendtreatCount($condition);
            if ($count > self::EXPORT_SIZE ){   //显示下载链接
                $array = array();
                $page = ceil($count/self::EXPORT_SIZE);
                for ($i=1;$i<=$page;$i++){
                    $limit1 = ($i-1)*self::EXPORT_SIZE + 1;
                    $limit2 = $i*self::EXPORT_SIZE > $count ? $count : $i*self::EXPORT_SIZE;
                    $array[$i] = $limit1.' ~ '.$limit2 ;
                }
                Tpl::output('list',$array);
                Tpl::output('murl','javascript:history.back(-1)');
                Tpl::setDirquna('shop');
                Tpl::showpage('export.excel');
                exit();
            }
            $limit = false;
        } else {
            $limit1 = ($_GET['curpage']-1) * self::EXPORT_SIZE;
            $limit2 = self::EXPORT_SIZE;
            $limit = $limit1 .','. $limit2;
        }
        $refund_list = $model_refund->getPendtreatList($condition,'',$order,$limit);
        /** 查找全额退款的订单商品 */
        $oids = array() ;//全额退款的订单号
        foreach ($refund_list as $refund_info) {
            if( $refund_info['goods_id'] > 0 ) continue ;
            $oids [] = $refund_info['order_id'] ;
        }
        $order_goods = array() ;
        if( !empty($oids) ) {
            $ogWhere['order_id'] = array('in', $oids) ;
            $list = Model('order')->getOrderGoodsList( $ogWhere );
            foreach ($list as $og) {
                $order_goods[$og['order_id']][] = $og ;
            }
        }

        $this->createCsv($refund_list, $order_goods);
    }

    /**
     * 生成csv文件
     */
    private function createCsv($refund_list, $order_goods) {
        $model_refund = Model('refund_return');
        $oids = $list = array();
        $pic_base_url = UPLOAD_SITE_URL.'/'.ATTACH_PATH.'/refund/';
        foreach ($refund_list as $k => $refund_info) {
            $order_id = $refund_info['order_id'] ;
            $list[$k]['refund_sn'] = $refund_info['refund_sn']."\t";
            $list[$k]['refund_amount'] = ncPriceFormat($refund_info['refund_amount']);
            if(!empty($refund_info['pic_info'])) {
                $info = unserialize($refund_info['pic_info']);
                if (is_array($info) && !empty($info['buyer'])) {
                    foreach($info['buyer'] as $pic_name) {
                        $list[$k]['pic_info'] .= $pic_base_url.$pic_name.'|';
                    }
                    $list[$k]['pic_info'] = trim($list[$k]['pic_info'],'|');
                }
            }
            if (empty($list[$k]['pic_info'])) $list[$k]['pic_info'] = '';
            $list[$k]['buyer_message'] = $refund_info['buyer_message'];
            $list[$k]['add_times'] = date('Y-m-d H:i:s',$refund_info['add_time']);
            //$list[$k]['goods_name'] = $refund_info['goods_name'];
            $list[$k]['goods_name'] = !empty($refund_info['goods_id']) ? $refund_info['goods_name'] : implode(" ||| ", array_column($order_goods[$order_id], 'goods_name')) ;
            $state_array = $model_refund->getRefundStateArray('seller');
            $list[$k]['seller_state'] = $state_array[$refund_info['seller_state']];
            $admin_array = $model_refund->getRefundStateArray('admin');
            $list[$k]['refund_state'] = $refund_info['seller_state'] == 2 ? $admin_array[$refund_info['refund_state']]:'';
            $list[$k]['seller_message'] = $refund_info['seller_message'];
            $list[$k]['admin_message'] = $refund_info['admin_message'];
            $list[$k]['seller_times'] = !empty($refund_info['seller_time']) ? date('Y-m-d H:i:s',$refund_info['seller_time']) : '';
            if ($refund_info['goods_image'] != '') {
                $list[$k]['goods_image'] = thumb($refund_info,360);
            } else {
                $list[$k]['goods_image'] = '';
            }
            $list[$k]['goods_id'] = !empty($refund_info['goods_id']) ? $refund_info['goods_id'] : implode(" ||| ", array_column($order_goods[$order_id], 'goods_id'));
            $list[$k]['order_sn'] = $refund_info['order_sn'];
            $list[$k]['buyer_name'] = $refund_info['buyer_name'];
            $list[$k]['buyer_id'] = $refund_info['buyer_id'];
            $list[$k]['store_name'] = $refund_info['store_name'];
            $list[$k]['store_id'] = $refund_info['store_id'];
            $list[$k]['refund_way'] = orderPaymentName($refund_info['refund_way']);
            $list[$k]['refund_name'] = $refund_info['refund_name'];
            $list[$k]['refund_account'] = $refund_info['refund_account'];
            $list[$k]['order_id'] = $refund_info['order_id'];
            $oids[] = $refund_info['order_id'];
        }

        if( !empty($oids) ) {
            $oWhere ['order_id'] = array( 'in', array_unique($oids) ) ;
            $orders = Model()->table('orders')->field('order_id, fx_order_id')->where( $oWhere )->select() ;
            $rels = $orders ? array_column($orders, 'fx_order_id', 'order_id') : array() ;
            foreach ($list as $k => &$item) {
                $order_id = $item['order_id'] ;
                $item['fx_order_id'] = isset($rels[ $order_id ]) ? $rels[ $order_id ] : '' ;
                unset($item['order_id']) ;
            }
        }

        $header = array(
            'refund_sn' => '退单编号',
            'refund_amount' => '退款金额',
            'pic_info' => '申请图片',
            'buyer_message' => '申请原因',
            'add_times' => '申请时间',
            'goods_name' => '涉及商品',
            'seller_state' => '商家处理',
            'refund_state' => '平台处理',
            'seller_message' => '商家处理备注',
            'admin_message' => '平台处理备注',
            'seller_times' => '商家申核时间',
            'goods_image' => '商品图',
            'goods_id' => '商品ID',
            'order_sn' => '订单编号',
            'buyer_name' => '买家',
            'buyer_id' => '买家ID',
            'store_name' => '商家名称',
            'store_id'  => '商家ID',
            'refund_way'  => '退款方式',
            'refund_name'  => '收款姓名',
            'refund_account'  => '收款帐号',
            'fx_order_id' => '分销订单号',
        );
        array_unshift($list, $header);

        $csv = new Csv();
        $export_data = $csv->charset($list,CHARSET,'gbk');
        $csv->filename = $csv->charset('refund',CHARSET).$_GET['curpage'] . '-'.date('Y-m-d');
        $csv->export($list);
    }
}
