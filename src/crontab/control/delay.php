<?php
/**
 * 任务计划 - 天执行的任务
 *
 *
 *
 *
 * @汉购网提供技术支持 授权请购买shopnc授权
 * @license    http://www.hangowa.com
 * @link       交流群号：
 */
defined('ByShopWWI') or exit('Access Invalid!');

class delayControl extends BaseCronControl
{

    /**
     * 该文件中所有任务执行频率，默认1天，单位：秒
     * @var int
     */
    const EXE_TIMES = 86400;

    private $time_granularity = array(12, 24);


    /**
     * 默认方法
     */
    public function indexOp()
    {
        $this->_delay_orders();
    }

    private function _delay_orders()
    {
        $order_model     = Model('orders');
        $store_model     = Model('store');
        $member_model    = Model('member');
        $store_count     = array();
        $store_delay_num = array();
        $store_delay     = array();
        //每个店铺延迟的数量
        foreach ($this->time_granularity as $granularity) {
            $store_count[$granularity] = $order_model->where(array('order_state' => 20, 'payment_time' => array('lt', TIMESTAMP - $granularity * 3600), 'lock_state' => 0, 'delete_state' => 0, 'order_type' => 1))
                ->group('store_id')
                ->field('count(1) as num,store_id')
                ->order('num desc')
                ->select();
            foreach ((array)$store_count[$granularity] as $key => $value) {
                $store_delay_num[$granularity][$value['store_id']] = $value['num'];
            }
        }

        //12小时与24小时的区分开
        foreach ($store_delay_num as $key => $value) {
            foreach ($value as $vk => $vv) {
                $store_delay[$vk][$key] = $vv;
            }
        }

        //店铺信息
        $store_ids = array_keys($store_delay);
        $stores = $store_model->where(array('store_id' => array('in', $store_ids)))->field('store_id,store_name,member_id,store_phone')->select();
        
        //店铺主人信息
        $members_id = array();
        foreach ($stores as $key => $value) {
            $members_id[] = $value['member_id'];
        }
        $members_info = $member_model->where(array('member_id'=>array('in',$members_id)))->field('member_id,member_mobile')->select();
        $members = array();
        foreach ($members_info as $key => $value) {
            $members[$value['member_id']] = $value;
        }

        //合并
        foreach ($stores as $key => $value) {
            $stores[$key]['12'] = $store_delay[$value['store_id']]['12'];
            $stores[$key]['24'] = $store_delay[$value['store_id']]['24'];
            $stores[$key]['member_mobile'] = $members[$value['member_id']]['member_mobile'];
        }

        //发短信
        $sms = new Sms();
        $now = date('m月d日H时', TIMESTAMP);
        foreach ($stores as $store) {
            if (!$store['member_mobile'] && !$store['store_phone']) continue;
            if ($store['member_mobile']){
                $msg = "{$store['store_name']}，您的店铺截止{$now}有物流发货承诺未兑现（延迟发货）订单 {$store['12']}单 扣款：3元。";
                if ($store['24']){
                    $msg .= "其中超时发货（24小时发货承若）订单 {$store['24']} 单，请在时效内尽快安排发货。";
                }else{
                    $msg .= '请尽快安排发货';
                }
                $sms->send($store['member_mobile'], $msg);
            }
        }
        
        
    }
}