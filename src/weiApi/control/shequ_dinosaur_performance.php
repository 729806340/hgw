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

class shequ_dinosaur_performanceControl extends mobileMemberControl
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
         $register_time = $this->tuanzhang_info['register_time'];
         if(!$this->tuanzhang_info['register_time']){
             output_error('团长注册时间参数错误');
         }
         $start_time_unix  = strtotime(date("Y-m",$register_time)."-1");
        $end_time = strtotime(date("Y-m-d 23:59:59", strtotime(-date('d') . 'day'))."+1 month");
        $bill_condition['ob_create_date'] = array('between', array($start_time_unix, $end_time));
        $bill_condition['ob_store_id'] = $this->tuanzhang_info['id'];
        $data = array();
        $data['month_commis'] =  array();
        $data['m_sell_total'] = 0;
        $data['m_commis_total'] = 0;
        $data['tuan_commis'] = array();
        $data['t_sell_total'] = 0;
        $data['t_commis_total']=0;
        /** @var shequ_billModel $shequ_bill_model */
        $shequ_bill_model = Model('shequ_bill');
        /** @var orderModel $order_model */
        $order_model = Model('order');
        /** @var refund_returnModel $refund_return_model */
        $refund_return_model = Model('refund_return');

        //月账单
        for ($i = $start_time_unix; $i <= $end_time; $i += $this->MonthUnix($i)) {
            $tmp_data = array();
            $tmp_data['month_date'] = date("Y-m",$i);
            $tmp_data['sell_amount'] = '0';
            $tmp_data['commis_amount'] = '0';
            //生成缓存
            $key  = $this->tuanzhang_info['id'].'_'.$tmp_data['month_date'];
/*            $bill_condition['ob_create_date'] = array('between', array($i, strtotime(date("Y-m",$i) . "+1 month")-1));
            $bill_list = $shequ_bill_model->getList($bill_condition);
            //出了结算单的
            if(!empty($bill_list)){
                $bill_list_ids = array_column($bill_list,'ob_id');
                $bill_list  = array_under_reset($bill_list,'ob_id');
                $order_condition  = array(
                    'shequ_tuan_id'=>array('gt',0),
                    'order_state' => ORDER_STATE_SUCCESS,
                    'refund_state'=>'0',
                    'shequ_tz_bill_id'=>array('in',$bill_list_ids)
                );
                $order_list = $order_model->getOrderList($order_condition,0,"*","order_id desc",999999);
                foreach($order_list as $o_k=>$o_v){
                    $tmp_data['sell_amount'] += $o_v['order_amount'];
                    $data['m_sell_total']+=$o_v['order_amount'];
                }
                $tmp_data['commis_amount'] = array_sum(array_column($bill_list,'ob_result_totals'));
                $data['m_commis_total'] += $tmp_data['commis_amount'];
            }*/
            $order_condition = array();
            $order_condition  = array(
              'shequ_tuan_id'=>array('gt',0),
              'shequ_tz_id'=>$this->tuanzhang_info['id'],
              'order_state'  =>array('egt',ORDER_STATE_PAY),
              'add_time' =>array('between', array($i, strtotime(date("Y-m",$i) . "+1 month")-1)),
            );
            $order_list = $order_model->getOrderList($order_condition,0,"*","order_id desc");
            if(!empty($order_list)){
                $order_ids = array_column($order_list,'order_id');
                foreach($order_list as $order ){
                    $tmp_data['sell_amount'] += $order['order_amount'];
                    $tmp_data['commis_amount']+=$order['shequ_return_amount'];
                }
                //减去退款佣金
                $refund_condition['order_id'] = array('in',$order_ids);
                $refund_list =  $refund_return_model->getRefundListByCondition($refund_condition,null,"*",999999);
                $refund_sum = array_sum(array_column($refund_list,'shequ_return_amount'));//退款佣金
                $refund_amount_sum = array_sum(array_column($refund_list,'refund_amount'));//退款金额
                $tmp_data['sell_amount'] -= $refund_amount_sum;
                $tmp_data['commis_amount'] -= $refund_sum;
            }
            $data['month_commis'][] = $tmp_data;
            $data['m_sell_total']+=$tmp_data['sell_amount'];
            $data['m_commis_total'] +=$tmp_data['commis_amount'];
        }
        //团账单
        /** @var shequ_tuan_configModel $shequ_tuan_config_model */
        $shequ_tuan_config_model = Model('shequ_tuan_config');
        $tuan_list = $shequ_tuan_config_model->getTuanConfigList(array());
        $order_condition=array();
        $order_condition = array(
            'shequ_tuan_id'=>array('gt',0),
            'order_state' => array('egt',ORDER_STATE_PAY),
            'refund_state'=>'0',
            'shequ_tz_id'=>$this->tuanzhang_info['id'],
        );
        $order_field = "group_concat(order_id) as order_ids,shequ_tuan_id,SUM(order_amount) as order_amount,SUM(shequ_return_amount) as shequ_return_amount";
        $order_group = "shequ_tuan_id";
        $order_info  = $order_model->where($order_condition)->field($order_field)->group($order_group)->limit(999999)->select();
        $order_info = array_under_reset($order_info,'shequ_tuan_id');
        foreach($tuan_list as $k=>$v){
            $tmp_data = array();
            $tmp_data['tuan_name'] = $v['config_tuan_name'];
            $tmp_data['sell_amount'] = isset($order_info[$v['config_tuan_id']])?$order_info[$v['config_tuan_id']]['order_amount']:"0";
            $tmp_data['commis_amount'] = isset($order_info[$v['config_tuan_id']])?$order_info[$v['config_tuan_id']]['shequ_return_amount']:"0";
            if(isset($order_info[$v['config_tuan_id']])){
                $order_ids = explode(',',$order_info[$v['config_tuan_id']]['order_ids']);
                $refund_return_condition['order_id'] = array('in',$order_ids);
                $refund_return_field = "SUM(shequ_return_amount) as refund_return_sum,SUM(refund_amount) as sell_return_sum";
                $refund_return_sum = $refund_return_model->where($refund_return_condition)->field($refund_return_field)->find();
                $re_sum =  $refund_return_sum['refund_return_sum']>0?$refund_return_sum['refund_return_sum']:'0';
                $se_sum = $refund_return_sum['sell_return_sum']>0?$refund_return_sum['sell_return_sum']:'0';
            }
            $tmp_data['commis_amount'] -= $re_sum;
            $tmp_data['sell_amount'] -= $se_sum;
            $tmp_data['config_start_time']  = $v['config_start_time']?date("Y-m-d",$v['config_start_time']):"";
            $data['t_sell_total'] +=$tmp_data['sell_amount'];
            $data['t_commis_total'] +=$tmp_data['commis_amount'];
            $data['tuan_commis'][] = $tmp_data;
        }
        output_data($data);
    }

    

    /**
     *获取当月时间戳
     */
    protected function MonthUnix($start_time_unix)
    {
       $nextMonth = strtotime(date("Y-m",$start_time_unix) . "+1 month");
        return $nextMonth-$start_time_unix;
    }

}

