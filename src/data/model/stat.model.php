<?php
/**
 * 统计管理
 * * @汉购网 (c) 2015-2018 ShopWWI Inc. (http://www.hangowa.com)
 * @license    http://www.shopwwi.c om
 * @link       交流群号：
 * @since      汉购网提供技术支持 授权请购买shopnc授权
 */
defined('ByShopWWI') or exit('Access Invalid!');

class statModel extends Model{
    /**
     * 查询新增会员统计
     *
     * @param array $condition 条件
     * @param string $field 字段
     * @param string $group 分组
     * @param string $order 排序
     * @param int $limit 限制
     * @param int $page 分页
     * @param boolean $lock 是否锁定
     * @return array
     */
    public function statByMember($where, $field = '*', $page = 0, $order = '', $group = '') {
        if (is_array($page)){
            if ($page[1] > 0){
                return $this->table('member')->field($field)->where($where)->page($page[0],$page[1])->order($order)->group($group)->select();
            } else {
                return $this->table('member')->field($field)->where($where)->page($page[0])->order($order)->group($group)->select();
            }
        } else {
            return $this->table('member')->field($field)->where($where)->page($page)->order($order)->group($group)->select();
        }
    }
    /**
     * 查询单条会员统计
     *
     * @param array $condition 条件
     * @param string $field 字段
     * @param string $group 分组
     * @param string $order 排序
     * @return array
     */
    public function getoneByMember($where, $field = '*', $order = '', $group = '') {
        return $this->table('member')->field($field)->where($where)->order($order)->group($group)->find();
    }
    /**
     * 查询单条店铺统计
     *
     * @param array $condition 条件
     * @param string $field 字段
     * @param string $group 分组
     * @param string $order 排序
     * @return array
     */
    public function getoneByStore($where, $field = '*', $order = '', $group = '') {
        return $this->table('store')->field($field)->where($where)->order($order)->group($group)->find();
    }
    /**
     * 查询店铺统计
     */
    public function statByStore($where, $field = '*', $page = 0, $limit = 0, $order = '', $group = '') {
        if (is_array($page)){
            if ($page[1] > 0){
                return $this->table('store')->field($field)->where($where)->page($page[0],$page[1])->limit($limit)->group($group)->order($order)->select();
            } else {
                return $this->table('store')->field($field)->where($where)->page($page[0])->limit($limit)->group($group)->order($order)->select();
            }
        } else {
            return $this->table('store')->field($field)->where($where)->page($page)->limit($limit)->group($group)->order($order)->select();
        }
    }
    /**
     * 查询新增店铺统计
     */
    public function getNewStoreStatList($condition, $field = '*', $page = 0, $order = 'store_id desc', $limit = 0, $group = '') {
        return $this->table('store')->field($field)->where($condition)->page($page)->limit($limit)->group($group)->order($order)->select();
    }

    /**
     * 查询会员列表
     */
    public function getMemberList($where, $field = '*', $page = 0, $order = 'member_id desc', $group = '') {
        if (is_array($page)){
            if ($page[1] > 0){
                return $this->table('member')->field($field)->where($where)->page($page[0],$page[1])->group($group)->order($order)->select();
            } else {
                return $this->table('member')->field($field)->where($where)->page($page[0])->group($group)->order($order)->select();
            }
        } else {
            return $this->table('member')->field($field)->where($where)->page($page)->group($group)->order($order)->select();
        }
    }

    /**
     * 调取店铺等级信息
     */
    public function getStoreDegree(){
        $tmp = $this->table('store_grade')->field('sg_id,sg_name')->where(true)->select();
        $sd_list = array();
        if(!empty($tmp)){
            foreach ($tmp as $k=>$v){
                $sd_list[$v['sg_id']] = $v['sg_name'];
            }
        }
        return $sd_list;
    }

    /**
     * 查询会员统计数据记录
     *
     * @param array $condition 条件
     * @param string $field 字段
     * @param string $group 分组
     * @param string $order 排序
     * @param int $limit 限制
     * @param int $page 分页
     * @return array
     */
    public function statByStatmember($where, $field = '*', $page = 0, $limit = 0,$order = '', $group = '') {
        if (is_array($page)){
            if ($page[1] > 0){
                return $this->table('stat_member')->field($field)->where($where)->page($page[0],$page[1])->limit($limit)->order($order)->group($group)->select();
            } else {
                return $this->table('stat_member')->field($field)->where($where)->page($page[0])->limit($limit)->order($order)->group($group)->select();
            }
        } else {
            return $this->table('stat_member')->field($field)->where($where)->page($page)->limit($limit)->order($order)->group($group)->select();
        }
    }

    /**
     * 查询商品数量
     */
    public function getGoodsNum($where){
        $rs = $this->field('count(*) as allnum')->table('goods_common')->where($where)->select();
        return $rs[0]['allnum'];
    }
    /**
     * 获取预存款数据
     */
    public function getPredepositInfo($condition, $field = '*', $page = 0, $order = 'lg_add_time desc', $limit = 0, $group = ''){
        return $this->table('pd_log')->field($field)->where($condition)->page($page)->limit($limit)->group($group)->order($order)->select();
    }
    /**
     * 获取结算数据
     */
    public function getBillList($where=array(), $field='*', $page = 0, $limit = 0, $order = 'ob_id desc', $group = ''){
        return $this->table('order_bill')->field($field)->where($where)->page($page)->limit($limit)->group($group)->order($order)->select();
    }
    /**
     * 查询订单及订单商品的统计
     *
     * @param array $condition 条件
     * @param string $field 字段
     * @param string $group 分组
     * @param string $order 排序
     * @param int $limit 限制
     * @param int $page 分页
     * @return array
     */
    public function statByOrderGoods($where, $field = '*', $page = 0, $limit = 0,$order = '', $group = '') {
        if (is_array($page)){
            if ($page[1] > 0){
                return $this->table('order_goods,orders')->field($field)->join('left')->on('order_goods.order_id=orders.order_id')->where($where)->group($group)->page($page[0],$page[1])->limit($limit)->order($order)->select();
            } else {
                return $this->table('order_goods,orders')->field($field)->join('left')->on('order_goods.order_id=orders.order_id')->where($where)->group($group)->page($page[0])->limit($limit)->order($order)->select();
            }
        } else {
            return $this->table('order_goods,orders')->field($field)->join('left')->on('order_goods.order_id=orders.order_id')->where($where)->group($group)->page($page)->limit($limit)->order($order)->select();
        }
    }
    /**
     * 查询订单及订单商品的统计
     *
     * @param array $condition 条件
     * @param string $field 字段
     * @param string $group 分组
     * @param string $order 排序
     * @param int $limit 限制
     * @param int $page 分页
     * @return array
     */
    public function statByOrderLog($where, $field = '*', $page = 0, $limit = 0,$order = '', $group = '') {
        if (is_array($page)){
            if ($page[1] > 0){
                return $this->table('order_log,orders')->field($field)->join('left')->on('order_log.order_id = orders.order_id')->where($where)->group($group)->page($page[0],$page[1])->limit($limit)->order($order)->select();
            } else {
                return $this->table('order_log,orders')->field($field)->join('left')->on('order_log.order_id = orders.order_id')->where($where)->group($group)->page($page[0])->limit($limit)->order($order)->select();
            }
        } else {
            return $this->table('order_log,orders')->field($field)->join('left')->on('order_log.order_id = orders.order_id')->where($where)->group($group)->page($page)->limit($limit)->order($order)->select();
        }
    }
    /**
     * 查询退款退货统计
     *
     * @param array $condition 条件
     * @param string $field 字段
     * @param string $group 分组
     * @param string $order 排序
     * @param int $limit 限制
     * @param int $page 分页
     * @return array
     */
    public function statByRefundreturn($where, $field = '*', $page = 0, $limit = 0,$order = '', $group = '') {
        if (is_array($page)){
            if ($page[1] > 0){
                return $this->table('refund_return')->field($field)->where($where)->group($group)->page($page[0],$page[1])->limit($limit)->order($order)->select();
            } else {
                return $this->table('refund_return')->field($field)->where($where)->group($group)->page($page[0])->limit($limit)->order($order)->select();
            }
        } else {
            return $this->table('refund_return')->field($field)->where($where)->group($group)->page($page)->limit($limit)->order($order)->select();
        }
    }
    /**
     * 查询店铺动态评分统计
     *
     * @param array $condition 条件
     * @param string $field 字段
     * @param string $group 分组
     * @param string $order 排序
     * @param int $limit 限制
     * @param int $page 分页
     * @return array
     */
    public function statByStoreAndEvaluatestore($where, $field = '*', $page = 0, $limit = 0,$order = '', $group = ''){
        if (is_array($page)){
            if ($page[1] > 0){
                return $this->table('evaluate_store,store')->field($field)->join('left')->on('evaluate_store.seval_storeid=store.store_id')->where($where)->group($group)->page($page[0],$page[1])->limit($limit)->order($order)->select();
            } else {
                return $this->table('evaluate_store,store')->field($field)->join('left')->on('evaluate_store.seval_storeid=store.store_id')->where($where)->group($group)->page($page[0])->limit($limit)->order($order)->select();
            }
        } else {
            return $this->table('evaluate_store,store')->field($field)->join('left')->on('evaluate_store.seval_storeid=store.store_id')->where($where)->group($group)->page($page)->limit($limit)->order($order)->select();
        }
    }
    /**
     * 处理搜索时间
     */
    public function dealwithSearchTime($search_arr){
        //初始化时间
        //天
        if(!$search_arr['search_time']){
            $search_arr['search_time'] = date('Y-m-d', time()- 86400);
        }
        $search_arr['day']['search_time'] = strtotime($search_arr['search_time']);//搜索的时间

        if(empty($search_arr['query_start_date'])){
            $search_arr['query_start_date'] = strtotime($search_arr['search_time']);//搜索的时间
        } else {
            $search_arr['query_start_date'] = strtotime($search_arr['query_start_date']);//搜索的时间
        }

        if(empty($search_arr['query_end_date'])){
            $search_arr['query_end_date'] = strtotime($search_arr['search_time']);//搜索的时间
        } else {
            $search_arr['query_end_date'] = strtotime($search_arr['query_end_date']);//搜索的时间
        }


        //周
        if(!$search_arr['searchweek_year']){
            $search_arr['searchweek_year'] = date('Y', time());
        }
        if(!$search_arr['searchweek_month']){
            $search_arr['searchweek_month'] = date('m', time());
        }
        if(!$search_arr['searchweek_week']){
            $searchweek_weekarr = getWeek_SdateAndEdate(time());
            $search_arr['searchweek_week'] = implode('|', $searchweek_weekarr);
            $searchweek_week_edate_m = date('m', strtotime($searchweek_weekarr['edate']));
            if($searchweek_week_edate_m <> $search_arr['searchweek_month']){
                $search_arr['searchweek_month'] = $searchweek_week_edate_m;
            }
        }
        $weekcurrent_year = $search_arr['searchweek_year'];
        $weekcurrent_month = $search_arr['searchweek_month'];
        $weekcurrent_week = $search_arr['searchweek_week'];
        $search_arr['week']['current_year'] = $weekcurrent_year;
        $search_arr['week']['current_month'] = $weekcurrent_month;
        $search_arr['week']['current_week'] = $weekcurrent_week;

        //月
        if(!$search_arr['searchmonth_year']){
            $search_arr['searchmonth_year'] = date('Y', time());
        }
        if(!$search_arr['searchmonth_month']){
            $search_arr['searchmonth_month'] = date('m', time());
        }
        $monthcurrent_year = $search_arr['searchmonth_year'];
        $monthcurrent_month = $search_arr['searchmonth_month'];
        $search_arr['month']['current_year'] = $monthcurrent_year;
        $search_arr['month']['current_month'] = $monthcurrent_month;
        return $search_arr;
    }

    /**
     * 获得查询的开始和结束时间
     */
    public function getStarttimeAndEndtime($search_arr){
        if($search_arr['search_type'] == 'slice'){
            $stime = $search_arr['query_start_date'];
            $etime = $search_arr['query_end_date'];
        }
        if($search_arr['search_type'] == 'day'){
            $stime = $search_arr['day']['search_time'];//今天0点
            $etime = $search_arr['day']['search_time'] + 86400 - 1;//今天24点
        }
        if($search_arr['search_type'] == 'day3'){
            $stime = $search_arr['day']['search_time'] - 86400 * 2;//3天前0点
            $etime = $search_arr['day']['search_time'] + 86400 - 1;//今天24点
        }
        if($search_arr['search_type'] == 'day7'){
            $stime = $search_arr['day']['search_time'] - 86400 * 6;//7天前0点
            $etime = $search_arr['day']['search_time'] + 86400 - 1;//今天24点
        }
        if($search_arr['search_type'] == 'week'){
            $current_weekarr = explode('|', $search_arr['week']['current_week']);
            $stime = strtotime($current_weekarr[0]);
            $etime = strtotime($current_weekarr[1])+86400-1;
        }
        if($search_arr['search_type'] == 'month'){
            $stime = strtotime($search_arr['month']['current_year'].'-'.$search_arr['month']['current_month']."-01 0 month");
            $etime = getMonthLastDay($search_arr['month']['current_year'],$search_arr['month']['current_month'])+86400-1;
        }
        if($search_arr['search_type'] == 'year'){
            $stime = strtotime($search_arr['year']['current_year']."-01-01");
            $etime = strtotime($search_arr['year']['current_year']."-12-31")+86400-1;
        }
        return array($stime,$etime);
    }
    /**
     * 查询会员统计数据单条记录
     *
     * @param array $condition 条件
     * @param string $field 字段
     * @param string $group 分组
     * @param string $order 排序
     * @return array
     */
    public function getOneStatmember($where, $field = '*', $order = '', $group = ''){
        return $this->table('stat_member')->field($field)->where($where)->group($group)->order($order)->find();
    }
    /**
     * 更新会员统计数据单条记录
     *
     * @param array $condition 条件
     * @param array $update_arr 更新数组
     * @return array
     */
    public function updateStatmember($where,$update_arr){
        return $this->table('stat_member')->where($where)->update($update_arr);
    }
    /**
     * 查询订单的统计
     *
     * @param array $condition 条件
     * @param string $field 字段
     * @param string $group 分组
     * @param string $order 排序
     * @param int $limit 限制
     * @param int $page 分页
     * @return array
     */
    public function statByOrder($where, $field = '*', $page = 0, $limit = 0,$order = '', $group = '') {
        if (is_array($page)){
            if ($page[1] > 0){
                return $this->table('orders')->field($field)->where($where)->group($group)->page($page[0],$page[1])->limit($limit)->order($order)->select();
            } else {
                return $this->table('orders')->field($field)->where($where)->group($group)->page($page[0])->limit($limit)->order($order)->select();
            }
        } else {
            return $this->table('orders')->field($field)->where($where)->group($group)->page($page)->limit($limit)->order($order)->select();
        }
    }
    /**
     * 查询积分的统计
     *
     * @param array $condition 条件
     * @param string $field 字段
     * @param string $group 分组
     * @param string $order 排序
     * @param int $limit 限制
     * @param int $page 分页
     * @return array
     */
    public function statByPointslog($where, $field = '*', $page = 0, $limit = 0,$order = '', $group = '') {
        if (is_array($page)){
            if ($page[1] > 0){
                return $this->table('points_log')->field($field)->where($where)->group($group)->page($page[0],$page[1])->limit($limit)->order($order)->select();
            } else {
                return $this->table('points_log')->field($field)->where($where)->group($group)->page($page[0])->limit($limit)->order($order)->select();
            }
        } else {
            return $this->table('points_log')->field($field)->where($where)->group($group)->page($page)->limit($limit)->order($order)->select();
        }
    }
    /**
     * 删除会员统计数据记录
     *
     * @param array $condition 条件
     * @param string $field 字段
     * @param string $group 分组
     * @param string $order 排序
     * @param int $limit 限制
     * @param int $page 分页
     * @return array
     */
    public function delByStatmember($where = array()) {
        $this->table('stat_member')->where($where)->delete();
    }
    /**
     * 查询订单商品缓存的统计
     *
     * @param array $condition 条件
     * @param string $field 字段
     * @param string $group 分组
     * @param string $order 排序
     * @return array
     */
    public function getoneByStatordergoods($where, $field = '*', $order = '', $group = '') {
        return $this->table('stat_ordergoods')->field($field)->where($where)->group($group)->order($order)->find();
    }
    /**
     * 查询订单商品缓存的统计
     *
     * @param array $condition 条件
     * @param string $field 字段
     * @param string $group 分组
     * @param string $order 排序
     * @param int $limit 限制
     * @param int $page 分页
     * @return array
     */
    public function statByStatordergoods($where, $field = '*', $page = 0, $limit = 0,$order = '', $group = '') {
        if (is_array($page)){
            if ($page[1] > 0){
                return $this->table('stat_ordergoods')->field($field)->where($where)->group($group)->page($page[0],$page[1])->limit($limit)->order($order)->select();
            } else {
                return $this->table('stat_ordergoods')->field($field)->where($where)->group($group)->page($page[0])->limit($limit)->order($order)->select();
            }
        } else {
            return $this->table('stat_ordergoods')->field($field)->where($where)->group($group)->page($page)->limit($limit)->order($order)->select();
        }
    }
    /**
     * 查询订单缓存的统计
     *
     * @param array $condition 条件
     * @param string $field 字段
     * @param string $group 分组
     * @param string $order 排序
     * @return array
     */
    public function getoneByStatorder($where, $field = '*', $order = '', $group = '') {
        return $this->table('stat_order')->field($field)->where($where)->group($group)->order($order)->find();
    }
    /**
     * 查询订单缓存的统计
     *
     * @param array $condition 条件
     * @param string $field 字段
     * @param string $group 分组
     * @param string $order 排序
     * @param int $limit 限制
     * @param int $page 分页
     * @return array
     */
    public function statByStatorder($where, $field = '*', $page = 0, $limit = 0,$order = '', $group = '') {
        if (is_array($page)){
            if ($page[1] > 0){
                return $this->table('stat_order')->field($field)->where($where)->group($group)->page($page[0],$page[1])->limit($limit)->order($order)->select();
            } else {
                return $this->table('stat_order')->field($field)->where($where)->group($group)->page($page[0])->limit($limit)->order($order)->select();
            }
        } else {
            return $this->table('stat_order')->field($field)->where($where)->group($group)->page($page)->limit($limit)->order($order)->select();
        }
    }
    /**
     * 查询订单缓存数量
     * 
     * @param array $where 条件
     * @param string $field 字段
     */
    public function getStatOrderCount($where, $field) {
        return $this->table('stat_order')->field($field)->where($where)->count();
    }
    
    /**
     * 查询商品列表
     *
     * @param array $condition 条件
     * @param string $field 字段
     * @param string $group 分组
     * @param string $order 排序
     * @param int $limit 限制
     * @param int $page 分页
     * @return array
     */
    public function statByGoods($where, $field = '*', $page = 0, $limit = 0,$order = '', $group = '') {
        if (is_array($page)){
            if ($page[1] > 0){
                return $this->table('goods')->field($field)->where($where)->group($group)->page($page[0],$page[1])->limit($limit)->order($order)->select();
            } else {
                return $this->table('goods')->field($field)->where($where)->group($group)->page($page[0])->limit($limit)->order($order)->select();
            }
        } else {
            return $this->table('goods')->field($field)->where($where)->group($group)->page($page)->limit($limit)->order($order)->select();
        }
    }

    /**
     * 查询流量统计单条记录
     *
     * @param array $condition 条件
     * @param string $field 字段
     * @param string $group 分组
     * @param string $order 排序
     * @return array
     */
    public function getoneByFlowstat($tablename = 'flowstat', $where, $field = '*', $order = '', $group = '') {
        return $this->table($tablename)->field($field)->where($where)->group($group)->order($order)->find();
    }
    /**
     * 查询流量统计记录
     *
     * @param array $condition 条件
     * @param string $field 字段
     * @param string $group 分组
     * @param string $order 排序
     * @param int $limit 限制
     * @param int $page 分页
     * @return array
     */
    public function statByFlowstat($tablename = 'flowstat', $where, $field = '*', $page = 0, $limit = 0,$order = '', $group = '') {
        if (is_array($page)){
            if ($page[1] > 0){
                return $this->table($tablename)->field($field)->where($where)->group($group)->page($page[0],$page[1])->limit($limit)->order($order)->select();
            } else {
                return $this->table($tablename)->field($field)->where($where)->group($group)->page($page[0])->limit($limit)->order($order)->select();
            }
        } else {
            return $this->table($tablename)->field($field)->where($where)->group($group)->page($page)->limit($limit)->order($order)->select();
        }
    }

    /**
     * 获取渠道统计数据，合并渠道ID
     * @param $tag
     */
    public function getMartChannelStat($tag=null)
    {
        //
        $channels = array(
            /* 以下为真实数据 */
            /*'pinduoduo'=>array('name'=>'拼多多','ids'=>array(194379,233577,241681,240993),),
            'huiguo'=>array('name'=>'会过','ids'=>array(240538,241485,),),
            'beibei'=>array('name'=>'贝贝网','ids'=>array(233280,241718,241833,),),
            'gegejia'=>array('name'=>'格格家','ids'=>array(223223,238667,),),
            'suning'=>array('name'=>'苏宁','ids'=>array(239004,235420,),),*/

            /* 以下为虚构数据 */
            'pinduoduo'=>array('name'=>'拼多多','ids'=>array(194379,233577,241681,240993),),
            'huiguo'=>array('name'=>'会过','ids'=>array(240538,241485,233577,241833),),
            'beibei'=>array('name'=>'贝贝网','ids'=>array(233280,241718,241833,233577),),
            'gegejia'=>array('name'=>'格格家','ids'=>array(223223,238667,233577,241833),),
            'suning'=>array('name'=>'苏宁','ids'=>array(239004,235420,233577,241833),),
        );
        $tableName = 'stat_order';
        // 处理区间
        $dateMonth = strtotime(date('Y-m-1'));
        $date30 = strtotime(date('Y-m-d',strtotime('-30 days')));
        $date60 = strtotime(date('Y-m-d',strtotime('-60 days')));
        $date90 = strtotime(date('Y-m-d',strtotime('-90 days')));
        $field = ' SUM(order_amount) AS amount,COUNT(order_amount) AS num,FROM_UNIXTIME(MIN(order_add_time),"%Y-%m-%d") AS date_val ';
        $group = 'TO_DAYS(FROM_UNIXTIME(order_add_time))';
        $orderBy = 'TO_DAYS(FROM_UNIXTIME(order_add_time)) DESC';
        $res = array();
        foreach ($channels as $key => $channel){
            $ids = $channel['ids'];
            $item = array('name'=>$channel['name'],'key'=>$key);
            $item['date30'] = $this->statByStatorder(
                array(
                    'buyer_id'=>array('in',$ids),
                    'order_add_time'=>array('gt',$date30)
                ), $field, 0, 0, $orderBy,$group);
            $item['date60'] = $this->statByStatorder(array('buyer_id'=>array('in',$ids),'order_add_time'=>array('gt',$date60)), $field, 0, 0, $orderBy,$group);
            $item['date90'] = $this->statByStatorder(array('buyer_id'=>array('in',$ids),'order_add_time'=>array('gt',$date90)), $field, 0, 0, $orderBy,$group);
            $month = $this->statByStatorder(array('buyer_id'=>array('in',$ids),'order_add_time'=>array('gt',$dateMonth)), $field, 0, 0, $orderBy);
            $item['today'] = $item['date30'][0];
            $item['yesterday'] = $item['date30'][1];
            $item['month'] = array(
                'amount'=>($month[0]['amount']?$month[0]['amount']:0),
                'num'=>($month[0]['num']?$month[0]['num']:0),
                'date_val'=>date('Y-m'),
            );
            $res[$key] = $item;
        }
        return $res;
    }

    /**
     * 获取商品统计数据
     * @param $tag
     */
    public function getMartGoodsStat($tag)
    {

    }
}
