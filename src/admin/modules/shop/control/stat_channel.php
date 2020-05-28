<?php
/***
 * 渠道分析
 * @author ljq
 * @date 2017-03-07
 */

class stat_channelControl extends SystemControl{
    private $links = array(
        array('url'=>'act=stat_channel&op=index','text'=>'分销渠道'),
        array('url'=>'act=stat_channel&op=device','text'=>'自有平台'),
    );
    
    private $channel = array(
				'194379' => '拼多多',
				'201917' => '有赞',
				'197586' => '人人店',
				'207523' => '老汉购网平台',
				'223221' => '返利网',
				'223222' => 'zhe800',
				'223223' => '格格家',
				'223224' => '盟店',
				'223268' => '淘宝',
				'223921' => '卷皮',
				'225846' => '小毛驴',
				'225909' => '绿景农场',
				'226348' => '楚楚街',
				'226476' => '韩桂人',
				'226692' => '楚楚街拼划算',
				'226699' => '寻食者说',
				'227579' => '汉购分销',
				'228377' => '原野农场',
				'228378' => '梧桐猫',
				'232174' => '合中味道',
				'233280' => '贝贝网',
				'233577' => '果然商城',
                '235365' => '拼到家',
                '235420' => '苏宁易购',
                '235568' => '麦豆果园',
                '236823' => '人人优品',
                '237018' => '环球优选',
		);
    private $search_arr;//处理后的参数
     
    public function __construct(){
        parent::__construct();
        Language::read('stat');
        import('function.statistics');
        import('function.datehelper');
        $model = Model('stat');
        //存储参数
        $this->search_arr = $_REQUEST;
        $this->search_arr = $model->dealwithSearchTime($this->search_arr);
        //获得系统年份
        $year_arr = getSystemYearArr();
        //获得系统月份
        $month_arr = getSystemMonthArr();
        //获得本月的周时间段
        $week_arr = getMonthWeekArr($this->search_arr['week']['current_year'], $this->search_arr['week']['current_month']);
        Tpl::output('year_arr', $year_arr);
        Tpl::output('month_arr', $month_arr);
        Tpl::output('week_arr', $week_arr);
        Tpl::output('search_arr', $this->search_arr);
    }

    public function indexOp() {
        $this->orderamountOp();
    }
    public function deviceOp() {
        Tpl::output('top_link',$this->sublink($this->links, 'device'));
        Tpl::setDirquna('shop');/*网 店 运 维shop wwi.com*/
        Tpl::showpage('stat.channel.device');
    }
    
    public function orderamountOp(){
        Tpl::output('top_link',$this->sublink($this->links, 'index'));
        Tpl::setDirquna('shop');/*网 店 运 维shop wwi.com*/
        Tpl::showpage('stat.channel.orderamount');
    }

    /**
     * 获得查询上周或者上个月的时间
     */
    public function getLastStartandEndtime($search_arr){
        if($search_arr['search_type'] == 'slice'){
            $stime = $search_arr['query_start_date'];
            $etime = $search_arr['query_end_date'];
        }
        if($search_arr['search_type'] == 'day'){
            $stime = $search_arr['day']['search_time'] - 86400;//今天0点
            $etime = $search_arr['day']['search_time']-1;
        }
        if($search_arr['search_type'] == 'week'){
            $current_weekarr = explode('|', $search_arr['week']['current_week']);
            $stime=strtotime($current_weekarr[0])-86400*7;
            $etime = strtotime($current_weekarr[0])+86400-1;
        }
        if($search_arr['search_type'] == 'month'){
            if($search_arr['month']['current_month']!=1){
                $stime = strtotime($search_arr['month']['current_year'].'-'.$search_arr['month']['current_month']."-01 0 month");
                $etime = getMonthLastDay($search_arr['month']['current_year'],$search_arr['month']['current_month'])+86400-1;
            }else{
                $current_year=$search_arr['month']['current_year']-1;
                $current_month=12;
                $stime = strtotime($current_year.'-'.$current_month."-01 0 month");
                $etime = getMonthLastDay($current_year,$current_month)+86400-1;
            }

        }
        if($search_arr['search_type'] == 'year'){
            $stime = strtotime($search_arr['year']['current_year']."-01-01");
            $etime = strtotime($search_arr['year']['current_year']."-12-31")+86400-1;
        }
        return array($stime,$etime);
    }

    public function get_channel_xmlOp(){
        ini_set('memory_limit','4G');
        set_time_limit(900);
        $model = Model('stat_order');
        if(!$this->search_arr['search_type']){
            $this->search_arr['search_type'] = 'day';
        }
        $searchtime_arr = Model('stat')->getStarttimeAndEndtime($this->search_arr);
        $stime = $searchtime_arr[0];
        $etime = $searchtime_arr[1];
        //查询订单表下单量、下单金额、下单客户数、平均客单价
        $where = array();
        $where['order_isvalid'] = 1;//计入统计的有效订单
        $where['order_add_time'] = array('between',array($stime,$etime));
        $where['payment_code'] = 'fenxiao' ;
        $field = 'SUM(order_amount) as order_amount,COUNT(order_id) as order_count,buyer_id,COUNT(distinct buyer_phone) as buyer_count';
        $res = $model->field($field)->where($where)->group('buyer_id')->limit(false)->select();
        $stat_order = array();
        $stat_member=array();
        $stat_order_total=0;
        foreach($res as $k=>$v){
            $stat_order[$v['buyer_id']]['order_amount'] = floatval($v['order_amount']);
            $stat_order[$v['buyer_id']]['order_count'] = floatval($v['order_count']);
            $stat_member[$v['buyer_id']][]=$v['buyer_count'];
            $stat_order_total+=floatval($v['order_amount']);
        }
        //商品数量
        $statnew_arr = array();
        $field = 'goods_num , buyer_id';
        $rs = Model('stat_ordergoods')->field($field)->where($where)->limit(false)->select();
        $stat_ordergoods = array();
        foreach($rs as $k=>$v){
            $stat_ordergoods[$v['buyer_id']][] = intval($v['goods_num']);
        }

        $where = array();
        $where['seller_state'] = 2;//计入统计的有效订单
        $where['add_time'] = array('between',array($stime,$etime));
        $where['refund_way'] = 'fenxiao' ;
        /** @var refund_returnModel $refundModel */
        $refundModel  = Model('refund_return');
        $field = 'SUM(refund_amount) as refund_amount,COUNT(refund_id) as refund_count,buyer_id';
        $refunds = $refundModel->field($field)->where($where)->group('buyer_id')->limit(false)->select();
        $refunds = array_under_reset($refunds,'buyer_id');
        $stat_channel = array();
        $member_fenxiao = Model('member_fenxiao')->getMemberFenxiao();
        $member_fenxiao_out = $store_ids = array();
        foreach($member_fenxiao as $v){
            $member_fenxiao_out[$v['member_id']]['member_cn_code'] = $v['member_cn_code'];
            $member_fenxiao_out[$v['member_id']]['member_en_code'] = $v['member_en_code'];
            $member_fenxiao_out[$v['member_id']]['filter_store_id'] = $v['filter_store_id'];
            if ($v['filter_store_id'] > 0) {
                $store_ids[] = $v['filter_store_id'];
            }
        }

        /* 计算成本 */
        $cost_where = array();
        //$cost_where['orders.fx_order_id'] = array('gt', 0);
        $cost_where['orders.payment_code'] = 'fenxiao';
        $cost_where['orders.order_state'] = array('gt', ORDER_STATE_PAY);//计入统计的有效订单是发货后
        $cost_where['orders.add_time'] = array('between',array($stime,$etime));
        $orderModel  = Model('orders');
        $field = 'SUM(goods_cost) as cost_amount, orders.buyer_id';
        $cost_amount_data = $orderModel->table('orders,order_goods')->join('left')->on('orders.order_id = order_goods.order_id')->field($field)->where($cost_where)->group('orders.buyer_id')->limit(false)->select();
        $cost_amount_data = array_under_reset($cost_amount_data,'buyer_id');

        /*复购率和增长率*/
        //$model_stat = Model('stat');
        $search_last_time=$this->getLastStartandEndtime($this->search_arr);
        $search_param = $_REQUEST;
        unset($search_param['act'],$search_param['op']);
        $search_param = http_build_query($search_param);
        //组装订单查询条件
        $search_order_param ="jq_query=1&qtype_time=add_time&query_start_date=".date('Y-m-d' , $searchtime_arr[0])."&query_end_date=".date('Y-m-d',$searchtime_arr[1])."&keyword_type=buyer_name";
        $condition = array();
        $condition['order_isvalid'] = 1;//计入统计的有效订单
        $condition['order_add_time'] = array('between',$search_last_time);
        //昨天的下单人员
        $front_order=Model('stat_order')->field('buyer_id,buyer_phone')->where($condition)->limit(false)->select();
        //今天的下单数量
        $current_order=Model('stat_order')->field('buyer_id,buyer_phone')->where($where)->limit(false)->select();

        $last_buyer=implode(',',array_unique(array_column($front_order,'buyer_phone')));
        $where['buyer_phone']=array("in",$last_buyer);
        $last_order=Model('stat_order')->where($where)->field('buyer_id,buyer_phone')->limit(false)->select();
        $last_buyer_order=array_intersect($last_order,$current_order);
        $arr=array_count_values(array_column($last_buyer_order,'buyer_id'));

        $front_order=array_unique($front_order,SORT_REGULAR);
        $last_statistics=array_count_values(array_column($front_order,'buyer_id'));

        $store_ids = array_unique($store_ids);
        $store_list = array();
        if (!empty($store_ids)) {
            $store_list = Model('store')->where(array('store_id' => array('in', $store_ids)))->field('store_id, store_name')->limit(false)->select();
            $store_list = array_under_reset($store_list, 'store_id');
        }

        $order_amount_data=array();
        foreach($member_fenxiao_out as $k=>$v){
            $stat_channel[$k]['channel_id']=$k;
            $stat_channel[$k]['channel']= $v['member_cn_code'];
            $stat_channel[$k]['store_name']= isset($store_list[$v['filter_store_id']]['store_name']) ? $store_list[$v['filter_store_id']]['store_name'] : '汉购网';
            $refund = isset($refunds[$k]['refund_amount'])?$refunds[$k]['refund_amount']:0;
            $refund_count = isset($refunds[$k]['refund_count'])?$refunds[$k]['refund_count']:0;
            $order_amount = isset($stat_order[$k]['order_amount'])?$stat_order[$k]['order_amount']:0;
            $order_count = isset($stat_order[$k]['order_count'])?$stat_order[$k]['order_count']:0;
            $stat_channel[$k]['order_amount'] = ncPriceFormat($order_amount);
            $stat_channel[$k]['order_amount'] = empty($stat_channel[$k]['order_amount'])?'0.00':$stat_channel[$k]['order_amount'];
            $stat_channel[$k]['cost_amount'] = empty($cost_amount_data[$k]['cost_amount'])?'0.00':$cost_amount_data[$k]['cost_amount'];
            $stat_channel[$k]['order_num'] = $order_count;
            $stat_channel[$k]['refund_amount'] = ncPriceFormat($refund);
            $stat_channel[$k]['refund_num'] = $refund_count;
            $stat_channel[$k]['goods_num'] = is_array($stat_ordergoods[$k])?array_sum($stat_ordergoods[$k]):0;
            $stat_channel[$k]['membernum']=array_sum($stat_member[$k]);
            /*复购率=这周重复购买的上周会员/上周起下单的会员量*/
            $same_member=$arr[$k];
            $total_member=$last_statistics[$k];
            $reporate=$same_member/$total_member;
            $stat_channel[$k]['reporate']=number_format($reporate*100,2)."%";
            /*增长率=这周购买人数-上周购买人数/上周起下单的会员量*/
            if($total_member==0){
                $growthrate=0;
            }else{
                $growthrate=(intval($stat_channel[$k]['membernum'])-$total_member)/$total_member;
            }
            $stat_channel[$k]['aveprice']=$stat_channel[$k]['order_num']==0 ? 0:number_format($stat_channel[$k]['order_amount']/$stat_channel[$k]['order_num'],2);
            $stat_channel[$k]['uniform']=$stat_channel[$k]['order_num']==0 ? 0:number_format($stat_channel[$k]['goods_num']/$stat_channel[$k]['order_num'],2);
            $stat_channel[$k]['scale']=$stat_order_total==0 ? 0:number_format($stat_channel[$k]['order_amount']*100/$stat_order_total,2)."%";
            $stat_channel[$k]['growthrate']=number_format($growthrate*100,2)."%";
            $stat_channel[$k]['growth']=intval($stat_channel[$k]['membernum'])-$total_member;
            $stat_channel[$k]['area']='<a href="index.php?act=stat_member&op=area_fenxiao&channel='.$k.'&'.$search_param.'">查看</a>';
            $stat_channel[$k]['order_detail'] = '<a href="index.php?act=order&keyword='.$v['member_en_code'].'&'.$search_order_param.'">明细</a>';
            $order_amount_data[$k]=ncPriceFormat(array_sum($stat_order[$k]));
        }
        array_multisort($order_amount_data,SORT_DESC ,$stat_channel);
        $total = array('channel_id'=>0,'channel'=>'合计','store_name'=>'-','order_amount'=>0,'cost_amount'=>0,'order_num'=>0,'membernum'=>0,'refund_amount'=>0,'refund_count'=>0);
        foreach ($stat_channel as $k=>$value){
            $total['order_amount'] += $value['order_amount'];
            $total['cost_amount'] += $value['cost_amount'];
            $total['order_num'] += $value['order_num'];
            $total['membernum'] += $value['membernum'];
            $total['refund_amount'] += $value['refund_amount'];
            $total['refund_count'] += $value['refund_count'];
        }
        $stat_channel[] = $total;

        $stat_channel=array_combine(array_column($stat_channel,'channel_id'),$stat_channel);
        $data = array();
        $data['now_page'] = $model->shownowpage();
        $data['total_num'] = $model->gettotalnum();
        $data['list'] = $stat_channel;
        echo Tpl::flexigridXML($data);exit();
    }
    public function get_device_xmlOp(){
        ini_set('memory_limit','4G');
        set_time_limit(900);
        $model = Model('orders');
        if(!$this->search_arr['search_type']){
            $this->search_arr['search_type'] = 'day';
        }
        $searchtime_arr = Model('stat')->getStarttimeAndEndtime($this->search_arr);
        $stime = $searchtime_arr[0];
        $etime = $searchtime_arr[1];
        $devices = array(1,2,4,6);
        //查询订单表下单量、下单金额、下单客户数、平均客单价
        $where = array();
        $where['order_state'] = array('gt',10);//计入统计的有效订单
        $where['add_time'] = array('between',array($stime,$etime));
        $where['order_from'] = array('in',$devices);
        $field = 'SUM(order_amount) as order_amount,COUNT(order_id) as order_count,COUNT(distinct buyer_phone) as buyer_count,order_from';
        $res = $model->field($field)->where($where)->group('order_from')->limit(false)->select();
        $stat_order = array();
        $stat_member=array();
        $stat_order_total=0;
        //v($res);
        //v($model->getLastSql());
        foreach($res as $k=>$v){
            $stat_order[$v['order_from']]['order_amount'] = floatval($v['order_amount']);
            $stat_order[$v['order_from']]['order_count'] = floatval($v['order_count']);
            $stat_member[$v['order_from']]['buyer_count']=$v['buyer_count'];
            $stat_order_total+=floatval($v['order_amount']);
        }

        /* 计算成本 */
        $cost_where = array();
        $cost_where['orders.order_from'] = array('in',$devices);
        $cost_where['orders.order_state'] = array('gt', ORDER_STATE_NEW);
        $cost_where['orders.add_time'] = array('between',array($stime,$etime));
        $orderModel  = Model('orders');
        $field = 'SUM(goods_cost) as cost_amount, orders.order_from';
        $cost_amount_data = $orderModel->table('orders,order_goods')->join('left')->on('orders.order_id = order_goods.order_id')->field($field)->where($cost_where)->group('orders.order_from')->limit(false)->select();
        $cost_amount_data = array_under_reset($cost_amount_data,'order_from');

        $where = array();
        $where['refund_return.seller_state'] = 2;//计入统计的有效订单
        $where['refund_return.add_time'] = array('between',array($stime,$etime));
        $where['orders.order_from'] = array('in',$devices);
        $on = 'refund_return.order_id = orders.order_id';
        /** @var refund_returnModel $refundModel */
        $refundModel  = Model('refund_return');
        $field = 'SUM(refund_return.refund_amount) as refund_amount,COUNT(refund_return.refund_id) as refund_count,orders.order_from as order_from';
        $refunds = $refundModel->table('refund_return,orders')->join('left')->on($on)->field($field)->where($where)->group('orders.order_from')->limit(false)->select();
        $refunds = array_under_reset($refunds,'order_from');

        $out = $store_ids = array();
        foreach($stat_order as $k=>$v){
            $out[$k]['order_amount'] = $v['order_amount'];
            $out[$k]['order_count'] = $v['order_count'];
            $out[$k]['buyer_count'] = $stat_member[$k]['buyer_count'];
        }
        //组装订单查询条件
        $search_order_param ="qtype_time=add_time&query_start_date=".date('Y-m-d' , $searchtime_arr[0])."&query_end_date=".date('Y-m-d',$searchtime_arr[1])."";



        /*复购率和增长率*/
        //$model_stat = Model('stat');
        $search_last_time=$this->getLastStartandEndtime($this->search_arr);
        $search_param = $_REQUEST;
        unset($search_param['act'],$search_param['op']);
        $search_param = http_build_query($search_param);

        //昨天的下单人员
        $where = array();
        $where['order_state'] = array('gt',10);//计入统计的有效订单
        $where['add_time'] = array('between',$search_last_time);
        $where['order_from'] = array('in',$devices);
        $field = 'buyer_id,order_from';
        $front_order=$model->field($field)->where($where)->limit(false)->select();

        //查询订单表下单量、下单金额、下单客户数、平均客单价
        $where = array();
        $where['order_state'] = array('gt',10);//计入统计的有效订单
        $where['order_from'] = array('in',$devices);
        $where['add_time'] = array('between',array($stime,$etime));
        //今天的下单数量
        $current_order=$model->field($field)->where($where)->limit(false)->select();

        $last_buyer=implode(',',array_unique(array_column($front_order,'buyer_id')));
        $where['buyer_id']=array("in",$last_buyer);
        $last_order=$model->where($where)->field($field)->limit(false)->select();
        $last_buyer_order=array_intersect($last_order,$current_order);
        //v($last_buyer_order,0);
        $arr=array_count_values(array_column($last_buyer_order,'order_from'));
        //v($arr,0);

        $front_order=array_unique($front_order,SORT_REGULAR);
        $last_statistics=array_count_values(array_column($front_order,'order_from'));
        //v($last_statistics,0);




        $order_amount_data=array();
        foreach($devices as $k){
            $stat_channel[$k]['channel']=orderFrom($k);
            $order_amount = isset($stat_order[$k]['order_amount'])?$stat_order[$k]['order_amount']:0;
            $order_count = isset($stat_order[$k]['order_count'])?$stat_order[$k]['order_count']:0;

            $stat_channel[$k]['order_amount'] = ncPriceFormat($order_amount);
            $stat_channel[$k]['order_amount'] = empty($stat_channel[$k]['order_amount'])?'0.00':$stat_channel[$k]['order_amount'];
            $stat_channel[$k]['cost_amount'] = empty($cost_amount_data[$k]['cost_amount'])?'0.00':$cost_amount_data[$k]['cost_amount'];
            $stat_channel[$k]['order_num'] = $order_count;
            $stat_channel[$k]['membernum'] = $stat_member[$k]['buyer_count']?$stat_member[$k]['buyer_count']:0;
            //$stat_channel[$k]['order_price'] = ncPriceFormat($order_count>0?$order_amount/$order_count:0);
            //$stat_channel[$k]['buyer_price'] = ncPriceFormat($stat_channel[$k]['buyer_count']>0?$order_amount/$stat_channel[$k]['buyer_count']:0);

            $stat_channel[$k]['refund_amount'] = isset($refunds[$k]['refund_amount'])?$refunds[$k]['refund_amount']:0;
            $stat_channel[$k]['refund_count'] = isset($refunds[$k]['refund_count'])?$refunds[$k]['refund_count']:0;
            /*复购率=这周重复购买的上周会员/上周起下单的会员量*/
            $same_member=$arr[$k];
            $total_member=$last_statistics[$k];
            $reporate=$same_member/$total_member;
            $stat_channel[$k]['reporate']=number_format($reporate*100,2)."%";
            /*增长率=这周购买人数-上周购买人数/上周起下单的会员量*/
            if($total_member==0){
                $growthrate=0;
            }else{
                $growthrate=(intval($stat_channel[$k]['membernum'])-$total_member)/$total_member;
            }
            $stat_channel[$k]['aveprice']=$stat_channel[$k]['order_num']==0 ? 0:number_format($stat_channel[$k]['order_amount']/$stat_channel[$k]['order_num'],2);
            $stat_channel[$k]['uniform']=$stat_channel[$k]['order_num']==0 ? 0:number_format($stat_channel[$k]['goods_num']/$stat_channel[$k]['order_num'],2);
            $stat_channel[$k]['scale']=$stat_order_total==0 ? 0:number_format($stat_channel[$k]['order_amount']*100/$stat_order_total,2)."%";
            $stat_channel[$k]['growthrate']=number_format($growthrate*100,2)."%";
            $stat_channel[$k]['growth']=intval($stat_channel[$k]['membernum'])-$total_member;
            $stat_channel[$k]['area']='<a href="index.php?act=stat_member&op=area_fenxiao&channel='.$k.'&'.$search_param.'">查看</a>';
            $stat_channel[$k]['order_detail'] = '<a href="index.php?act=order&order_from='.$k.'&'.$search_order_param.'">明细</a>';
            $order_amount_data[$k]=ncPriceFormat(array_sum($stat_order[$k]));
        }
        array_multisort($order_amount_data,SORT_DESC ,$stat_channel);
        $total = array('channel'=>'合计','order_amount'=>0,'cost_amount'=>0,'order_num'=>0,'membernum'=>0,'refund_amount'=>0,'refund_count'=>0);
        foreach ($stat_channel as $k=>$value){
            $total['order_amount'] += $value['order_amount'];
            $total['cost_amount'] += $value['cost_amount'];
            $total['order_num'] += $value['order_num'];
            $total['membernum'] += $value['membernum'];
            $total['refund_amount'] += $value['refund_amount'];
            $total['refund_count'] += $value['refund_count'];
        }
        $stat_channel[] = $total;
        $stat_channel=array_combine(array_column($stat_channel,'channel'),$stat_channel);
        $data = array();
        $data['now_page'] = $model->shownowpage();
        $data['total_num'] = $model->gettotalnum();
        $data['list'] = $stat_channel;
        echo Tpl::flexigridXML($data);exit();
    }
    public function exportCvsOp(){
        set_time_limit(1800);
        ini_set('memory_limit','4G');
        $model = Model('stat_order');
        if(!$this->search_arr['search_type']){
            $this->search_arr['search_type'] = 'day';
        }
        $searchtime_arr = Model('stat')->getStarttimeAndEndtime($this->search_arr);
        $stime = $searchtime_arr[0];
        $etime = $searchtime_arr[1];
        //查询订单表下单量、下单金额、下单客户数、平均客单价
        $where = array();
        $where['order_isvalid'] = 1;//计入统计的有效订单
        $where['order_add_time'] = array('between',array($stime,$etime));
        $where['payment_code'] = 'fenxiao' ;
        $field = 'order_amount,buyer_id,buyer_phone';
        $res = $model->field($field)->where($where)->limit(false)->select();
        $stat_order = array();
        $stat_member=array();
        $stat_order_total=0;
        foreach($res as $k=>$v){
            $stat_order[$v['buyer_id']][] = floatval($v['order_amount']);
            $stat_member[$v['buyer_id']][]=$v['buyer_phone'];
            $stat_order_total+=floatval($v['order_amount']);
        }
        //商品数量
        $statnew_arr = array();
        $field = 'goods_num , buyer_id';
        $rs = Model('stat_ordergoods')->field($field)->where($where)->limit(false)->select();
        $stat_ordergoods = array();
        foreach($rs as $k=>$v){
            $stat_ordergoods[$v['buyer_id']][] = intval($v['goods_num']);
        }
        $where = array();
        $where['seller_state'] = 2;//计入统计的有效订单
        $where['add_time'] = array('between',array($stime,$etime));
        $where['refund_way'] = 'fenxiao' ;
        /** @var refund_returnModel $refundModel */
        $refundModel  = Model('refund_return');
        $field = 'SUM(refund_amount) as refund_amount,COUNT(refund_id) as refund_count,buyer_id';
        $refunds = $refundModel->field($field)->where($where)->group('buyer_id')->limit(false)->select();
        $refunds = array_under_reset($refunds,'buyer_id');

        $stat_channel = array();
        $member_fenxiao = Model('member_fenxiao')->getMemberFenxiao();
        $member_fenxiao_out = $store_ids = array();
        foreach($member_fenxiao as $v){
            $member_fenxiao_out[$v['member_id']]['member_cn_code'] = $v['member_cn_code'];
            $member_fenxiao_out[$v['member_id']]['filter_store_id'] = $v['filter_store_id'];
            if ($v['filter_store_id'] > 0) {
                $store_ids[] = $v['filter_store_id'];
            }

        }
        /*复购率和增长率*/
        $search_last_time=$this->getLastStartandEndtime($this->search_arr);
        $search_param = $_REQUEST;
        unset($search_param['act'],$search_param['op']);
        $search_param = http_build_query($search_param);
        $condition = array();
        $condition['order_isvalid'] = 1;//计入统计的有效订单
        $condition['order_add_time'] = array('between',$search_last_time);
        //昨天的下单人员
        $front_order=Model('stat_order')->field('buyer_id,buyer_phone')->where($condition)->limit(false)->select();
        //今天的下单数量
        $current_order=Model('stat_order')->field('buyer_id,buyer_phone')->where($where)->limit(false)->select();

        $last_buyer=implode(',',array_unique(array_column($front_order,'buyer_phone')));
        $where['buyer_phone']=array("in",$last_buyer);
        $last_order=Model('stat_order')->where($where)->field('buyer_id,buyer_phone')->limit(false)->select();
        $last_buyer_order=array_intersect($last_order,$current_order);
        $arr=array_count_values(array_column($last_buyer_order,'buyer_id'));

        $front_order=array_unique($front_order,SORT_REGULAR);
        $last_statistics=array_count_values(array_column($front_order,'buyer_id'));

        $store_ids = array_unique($store_ids);
        $store_list = array();
        if (!empty($store_ids)) {
            $store_list = Model('store')->where(array('store_id' => array('in', $store_ids)))->field('store_id, store_name')->limit(false)->select();
            $store_list = array_under_reset($store_list, 'store_id');
        }

        foreach($member_fenxiao_out as $k=>$v){
            if (!empty($_GET['id']) && is_array($_GET['id'])){
                if(!in_array($k,$_GET['id'])){
                    continue;
                }
            }
            $refund = isset($refunds[$k]['refund_amount'])?$refunds[$k]['refund_amount']:0;
            $refund_count = isset($refunds[$k]['refund_count'])?$refunds[$k]['refund_count']:0;
            $stat_channel[$k]['channel']= $v['member_cn_code'];
            $stat_channel[$k]['store_name']= isset($store_list[$v['filter_store_id']]['store_name']) ? $store_list[$v['filter_store_id']]['store_name'] : '汉购网';
            $stat_channel[$k]['order_amount'] = ncPriceFormat(array_sum($stat_order[$k]));
            $stat_channel[$k]['order_amount'] = empty($stat_channel[$k]['order_amount'])?'0.00':$stat_channel[$k]['order_amount'];
            $stat_channel[$k]['order_num'] = count($stat_order[$k]);
            $stat_channel[$k]['refund_amount'] = ncPriceFormat($refund);
            $stat_channel[$k]['refund_num'] = $refund_count;
            $stat_channel[$k]['goods_num'] = is_array($stat_ordergoods[$k])?array_sum($stat_ordergoods[$k]):0;
            $stat_channel[$k]['membernum']=count(array_unique($stat_member[$k]));
            /*复购率=这周重复购买的上周会员/上周起下单的会员量*/
            $same_member=$arr[$k];
            $total_member=$last_statistics[$k];
            $reporate=$same_member/$total_member;
            $stat_channel[$k]['reporate']=number_format($reporate*100,2)."%";
            /*增长率=这周购买人数-上周购买人数/上周起下单的会员量*/
            if($total_member==0){
                $growthrate=0;
            }else{
                $growthrate=(intval($stat_channel[$k]['membernum'])-$total_member)/$total_member;
            }
            $stat_channel[$k]['aveprice']=$stat_channel[$k]['order_num']==0 ? 0:number_format($stat_channel[$k]['order_amount']/$stat_channel[$k]['order_num'],2);
            $stat_channel[$k]['uniform']=$stat_channel[$k]['order_num']==0 ? 0:number_format($stat_channel[$k]['goods_num']/$stat_channel[$k]['order_num'],2);
            $stat_channel[$k]['scale']=$stat_order_total==0 ? 0:number_format($stat_channel[$k]['order_amount']*100/$stat_order_total,2)."%";
            $stat_channel[$k]['growthrate']=number_format($growthrate*100,2)."%";
            $stat_channel[$k]['growth']=intval($stat_channel[$k]['membernum'])-$total_member;
            $stat_channel[$k]['area']='<a href="index.php?act=stat_member&op=area_fenxiao&channel='.$k.'&'.$search_param.'">查看</a>';
            $order_amount_data[$k]=ncPriceFormat(array_sum($stat_order[$k]));
        }
        array_multisort($order_amount_data,SORT_DESC ,$stat_channel);
        $this->createCvs($stat_channel);
    }
    public function exportDeviceCvsOp(){
        $model = Model('orders');
        if(!$this->search_arr['search_type']){
            $this->search_arr['search_type'] = 'day';
        }
        $searchtime_arr = Model('stat')->getStarttimeAndEndtime($this->search_arr);
        $stime = $searchtime_arr[0];
        $etime = $searchtime_arr[1];
        //查询订单表下单量、下单金额、下单客户数、平均客单价
        $where = array();
        $where['order_state'] = array('gt',10);//计入统计的有效订单
        $where['add_time'] = array('between',array($stime,$etime));
        $field = 'SUM(order_amount) as order_amount,COUNT(order_id) as order_count,COUNT(distinct buyer_phone) as buyer_count,order_from';
        $res = $model->field($field)->where($where)->group('order_from')->limit(false)->select();
        $stat_order = array();
        $stat_member=array();
        $stat_order_total=0;
        foreach($res as $k=>$v){
            $stat_order[$v['order_from']]['order_amount'] = floatval($v['order_amount']);
            $stat_order[$v['order_from']]['order_count'] = floatval($v['order_count']);
            $stat_member[$v['order_from']][]=$v['buyer_count'];
            $stat_order_total+=floatval($v['order_amount']);
        }$out = $store_ids = array();
        foreach($stat_order as $k=>$v){
            $out[$k]['order_amount'] = $v['order_amount'];
            $out[$k]['order_count'] = $v['order_count'];
            $out[$k]['buyer_count'] = $stat_member[$k]['buyer_count'];
        }
        $search_order_param ="qtype_time=add_time&query_start_date=".date('Y-m-d' , $searchtime_arr[0])."&query_end_date=".date('Y-m-d',$searchtime_arr[1])."";

        $order_amount_data=array();
        foreach($out as $k=>$v){
            $stat_channel[$k]['channel']=orderFrom($k);
            $order_amount = isset($stat_order[$k]['order_amount'])?$stat_order[$k]['order_amount']:0;
            $order_count = isset($stat_order[$k]['order_count'])?$stat_order[$k]['order_count']:0;
            $stat_channel[$k]['order_amount'] = ncPriceFormat($order_amount);
            $stat_channel[$k]['order_amount'] = empty($stat_channel[$k]['order_amount'])?'0.00':$stat_channel[$k]['order_amount'];
            $stat_channel[$k]['order_num'] = $order_count;
            //$stat_channel[$k]['order_detail'] = '<a href="index.php?act=order&order_from='.$k.'&'.$search_order_param.'">明细</a>';
            $order_amount_data[$k]=ncPriceFormat(array_sum($stat_order[$k]));
        }
        array_multisort($order_amount_data,SORT_DESC ,$stat_channel);

        $total = array('channel'=>'合计','order_amount'=>0, 'order_num'=>0);
        foreach ($stat_channel as $k=>$value){
            $total['order_amount'] += $value['order_amount'];
            //$total['cost_amount'] += $value['cost_amount'];
            $total['order_num'] += $value['order_num'];
            //$total['membernum'] += $value['membernum'];
            //$total['refund_amount'] += $value['refund_amount'];
            //$total['refund_count'] += $value['refund_count'];
        }
        $stat_channel[] = $total;
        $this->createDeviceCvs($stat_channel);
    }

    public function createCvs($data){
        $header = array(
            'channel' => '渠道名称',
            'store_name' => '店铺名称',
            'order_amount' => '下单金额',
            'order_num' => '下单量',
            'refund_amount' => '退单金额',
            'refund_num' => '退单量',
            'goods_num'=>'商品量',
            'membernum' => '下单会员数',
            'reporate' => '复购率',
            'aveprice'=>'均单价',
            'uniform'=>'均单量',
            'scale'=>'金额占比',
            'growthrate' => '增长率',
            'growth' => '增长数',
            'area' => '区域分布'
        );
        array_unshift($data, $header);
        $csv = new Csv();
        $export_data = $csv->charset($data,CHARSET,'gbk');
        $csv->filename = $csv->charset('refund',CHARSET)."统计数据导出表" . '-'.date('Y-m-d');
        $csv->export($export_data);
    }
    public function createDeviceCvs($data){
        $header = array(
            'channel' => '平台名称',
            'order_amount' => '下单金额',
            'order_num' => '下单量',
        );
        array_unshift($data, $header);
        $csv = new Csv();
        $export_data = $csv->charset($data,CHARSET,'gbk');
        $csv->filename = $csv->charset('自有平台',CHARSET)."统计数据导出表" . '-'.date('Y-m-d');
        $csv->export($export_data);
    }
}
?>