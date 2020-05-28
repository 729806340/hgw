<?php

defined('ByShopWWI') or exit('Access Invalid!');

class martControl extends BaseApiControl{
    public function indexOp()
    {
        $this->success('you are welcome!');
    }

    public function channelOp()
    {
        $cacheKey = 'mart-channel-data';
        // 获取渠道销售数据，今日/昨天/30天/60天/90天
        $data = rkcache($cacheKey);
        if($data) return $this->success($data);
        /** @var statModel $statModel */
        $statModel = Model('stat');
        $data = $statModel->getMartChannelStat();
        wkcache($cacheKey,$data,10*60);
        $this->success($data);
    }
    public function mainOp()
    {
        $cacheKey = 'mart-main-data';
        // 获取渠道销售数据，今日/昨天/30天/60天/90天
        $data = rkcache($cacheKey);
        if($data) return $this->success($data);
        /** @var statModel $statModel */
        $statModel = Model('stat');
        $data = $statModel->table('stat_order')->field('COUNT(DISTINCT buyer_phone) as buyer_num, COUNT(order_id) as order_num,SUM(order_amount) as order_amount')->find();
        //var_dump($data);
        wkcache($cacheKey,$data,3600);
        $this->success($data);
    }

    public function userOp()
    {
        $member = $this->getMemberAndGradeInfo(true);
        if($member){
            $member['cart_num'] = Model('cart')->getCartNum('db',array('buyer_id'=>$_SESSION['member_id']));//查询购物车商品种类
        }
        $this->success($member);
    }

}
