<?php
/**
 * 我的接龙
 *
 * @copyright  Copyright (c) 2007-2015 ShopNC Inc. (http://www.shopnc.net)
 * @license    http://www.shopnc.net
 * @link       http://www.shopnc.net
 * @since      File available since Release v1.1
 */
defined('ByShopWWI') or exit('Access Invalid!');

class shequ_dinosaur_billControl extends mobileMemberControl
{
    protected $tuanzhang_info = array();

    public function __construct()
    {
        parent::__construct();
        /** @var shequ_tuanzhangModel $tuanzhang_model */
        $condition['state'] = '1';
        $condition['member_id'] = $this->member_info['member_id'];
        $tuanzhang_model = Model('shequ_tuanzhang');
        $this->tuanzhang_info = $tuanzhang_model->getOne($condition);
        if (empty($this->tuanzhang_info)) {
            output_error('不是团长');
        }
    }

    public function indexOp()
    {
        //指定时间段的结算单
        $start_unix = $_POST['start_time'] ? strtotime($_POST['start_time']) : strtotime('today') - 86400;//昨天开始时间戳
        $end_unix = $_POST['end_time'] ? strtotime($_POST['end_time']) + 86400 - 1 : strtotime('today') + 86400 - 1;
        $bill_type = $_POST['bill_type'];
        $condition['add_time'] = array('between', array($start_unix, $end_unix));
        $condition['shequ_tuan_id'] = array('gt', '0');
        $condition['shequ_tz_id'] = $this->member_info['tuanzhang_id'];
        $condition['order_state'] = array('egt', ORDER_STATE_PAY);
       // $condition['refund_state'] = '0';
        /** @var orderModel $order_model */
        $order_model = Model('order');
        $all = $order_model->getOrderList($condition, 999999, "*", "order_id desc", 99999, array('order_goods'));
        /** @var refund_returnModel $refund_return_model */
        $refund_return_model = Model('refund_return');
        $all = $refund_return_model->getGoodsRefundList($all);
        $data = array(
            'commis_total' => 0,
            'commis_wl_total' => 0,
            'commis_yj_total' => 0
        );
        foreach ($all as $o_k => $o_v) {
            foreach ($o_v['extend_order_goods'] as $re_goods) {
                if (is_array($re_goods['extend_refund'])&&$re_goods['extend_refund']['refund_state']=='3'&&$re_goods['extend_refund']['seller_state']=='2') {
                    $o_v['shequ_return_amount'] -= $re_goods['extend_refund']['shequ_return_amount'];
                }
            }
            if ($o_v['shequ_tz_bill_id'] == '0') {
                $data['commis_wl_total'] += $o_v['shequ_return_amount'];
            } else {
                $data['commis_yj_total'] += $o_v['shequ_return_amount'];
            }
        }
        $data['commis_total'] = $data['commis_wl_total'] + $data['commis_yj_total'];  //佣金计算
        //展示商品详情
        switch ($bill_type) {
            case 'all':
                break;
            case 'unclaimed':
                $condition['shequ_tz_bill_id'] = array('eq', '0');
                break;
            case 'received':
                $condition['shequ_tz_bill_id'] = array('neq', '0');
                break;
            default:
                break;
        }
        $goods_list = array();
        $order_info = $order_model->getOrderList($condition, $this->page, "*", "order_id desc", 99999, array('order_goods'));
        $order_info = $refund_return_model->getGoodsRefundList($order_info);
        foreach ($order_info as $re_k => $re_v) {  //有退款的不显示
            foreach ($re_v['extend_order_goods'] as $re_kk => $re_goods) {
                if (is_array($re_goods['extend_refund'])&&$re_goods['extend_refund']['refund_state']=='3'&&$re_goods['extend_refund']['seller_state']=='2') {
                    unset($order_info[$re_k]['extend_order_goods'][$re_kk]);
                }
            }
        }

        foreach ($order_info as $gk => $gv) {
            foreach ($gv['extend_order_goods'] as $g_k => $g_v) {
                $tmp_goods = array();
                $tmp_goods['goods_id'] = $g_v['goods_id'];
                $tmp_goods['order_id'] = $g_v['order_id'];
                $tmp_goods['goods_name'] = $g_v['goods_name'];
                $tmp_goods['goods_image'] = cthumb($g_v['goods_image']);
                $tmp_goods['goods_price'] = $g_v['goods_price'];
                $tmp_goods['goods_pay_price'] = $g_v['goods_pay_price'];
                $tmp_goods['goods_num'] = $g_v['goods_num'];
                $tmp_goods['shequ_tuan_id'] = $g_v['shequ_tuan_id'];
                $tmp_goods['shequ_commis_amount'] = $g_v['shequ_commis_amount'];
                $goods_list[] = $tmp_goods;
            }
        }
        //获取佣金比例
        /** @var shequ_tuan_config_goodsModel $shequ_tuan_config_goods_model */
        $shequ_tuan_config_goods_model = Model('shequ_tuan_config_goods');
        $commis = $shequ_tuan_config_goods_model->getTuanConfigGoodsList(array('goods_id' => array('in', array_keys(array_under_reset($goods_list,'goods_id')))), '', '', 'goods_id,tuan_config_id,commis');
        $commis = array_under_reset($commis,'tuan_config_id',2);
        foreach ($goods_list as $k => $v) {
            if(isset($commis[$v['shequ_tuan_id']])){   //团购商品配置表有数据;
                foreach($commis as $com){
                   foreach ($com as $c){
                      if($c['goods_id']==$v['goods_id']){
                          $goods_list[$k]['commis'] = $c['commis']."%";
                      }
                   }
                }
            }else{  //没数据
                $goods_list[$k]['commis'] = ncPriceformat(($v['shequ_commis_amount']*100)/$v['goods_pay_price'])."%";
            }
        }

        $data['goods_list'] = array_values($goods_list);
        $data['register_time'] = date('Y-m-d', $this->tuanzhang_info['register_time']);
        output_data($data, mobile_page($order_model->gettotalpage()));
    }

    /**
     * 商品退款处理
     * @param $goods_list array 需要处理的order_good数组
     * @return array
     */

}

