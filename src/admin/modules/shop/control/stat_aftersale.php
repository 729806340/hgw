<?php
/**
 * 售后分析
 *
 * @汉购网提供技术支持 授权请购买shopnc授权
 * @license    http://www.hangowa.com
 * @link       交流群号：
 */



defined('ByShopWWI') or exit('Access Invalid!');

class stat_aftersaleControl extends SystemControl{
    private $links = array(
        array('url'=>'act=stat_aftersale&op=refund','lang'=>'stat_refund'),
        array('url'=>'act=stat_aftersale&op=evalstore','lang'=>'stat_evalstore'),
        array('url'=>'act=stat_aftersale&op=order_refund', 'text'=>'订单退款统计'),
        array('url'=>'act=stat_aftersale&op=businessStatistics', 'text'=>'商家统计'),
        array('url'=>'act=stat_aftersale&op=commodityStatistics', 'text'=>'商品统计'),
//        array('url'=>'act=stat_aftersale&op=reasonStatistics', 'text'=>'退款原因统计'),
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
        //处理搜索时间
        if (!isset($this->search_arr['op']) || in_array($this->search_arr['op'],array('refund','refundlist','get_refundlist_xml','get_refund_highcharts', 'order_refund'))){
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
        }
        Tpl::output('search_arr', $this->search_arr);
    }

    public function indexOp() {
        $this->refundOp();
    }
    /**
     * 退款统计
     */
    public function refundOp(){
        Tpl::output('top_link',$this->sublink($this->links, 'refund'));
		Tpl::setDirquna('shop');/*网 店 运 维shop wwi.com*/
        Tpl::showpage('stat.aftersale.refund');
    }
    /**
     * 退款统计
     */
    public function refundlistOp(){
        $where = array();
        $model = Model('stat');
        $searchtime_arr = $model->getStarttimeAndEndtime($this->search_arr);
        $model_refund = Model('refund_return');
        $refundstate_arr = $model_refund->getRefundStateArray();
        $where['add_time'] = array('between',$searchtime_arr);
        if (!empty($_GET['id']) && is_array($_GET['id'])){
            $where['refund_id'] = array('in',$_GET['id']);
        }
        if ($this->search_arr['exporttype'] == 'excel'){
            $refundlist_tmp = $model_refund->getRefundReturnList($where, 0);
        }
        $statheader = array();
        $statheader[] = array('text'=>'订单编号','key'=>'order_sn');
        $statheader[] = array('text'=>'退款编号','key'=>'refund_sn');
        $statheader[] = array('text'=>'店铺名','key'=>'store_name','class'=>'alignleft');
        $statheader[] = array('text'=>'商品名称','key'=>'goods_name','class'=>'alignleft');
        $statheader[] = array('text'=>'买家会员名','key'=>'buyer_name');
        $statheader[] = array('text'=>'申请时间','key'=>'add_time');
        $statheader[] = array('text'=>'退款金额','key'=>'refund_amount');
        $statheader[] = array('text'=>'商家审核','key'=>'seller_state');
        $statheader[] = array('text'=>'平台确认','key'=>'refund_state');
        foreach ((array)$refundlist_tmp as $k => $v){
            $tmp = $v;
            foreach ((array)$statheader as $h_k=>$h_v){
                $tmp[$h_v['key']] = $v[$h_v['key']];
                if ($h_v['key'] == 'add_time'){
                    $tmp[$h_v['key']] = @date('Y-m-d',$v['add_time']);
                }
                if ($h_v['key'] == 'refund_state'){
                    $tmp[$h_v['key']] = $v['seller_state']==2 ? $refundstate_arr['admin'][$v['refund_state']]:'无';
                }
                if ($h_v['key'] == 'seller_state'){
                    $tmp[$h_v['key']] = $refundstate_arr['seller'][$v['seller_state']];
                }
                if ($h_v['key'] == 'goods_name'){
                    $tmp[$h_v['key']] = '<a href="'.urlShop('goods', 'index', array('goods_id' => $v['goods_id'])).'" target="_blank">'.$v['goods_name'].'</a>';
                }
            }
            $statlist[] = $tmp;
        }
        if ($this->search_arr['exporttype'] == 'excel'){
            //导出Excel
            import('libraries.excel');
            $excel_obj = new Excel();
            $excel_data = array();
            //设置样式
            $excel_obj->setStyle(array('id'=>'s_title','Font'=>array('FontName'=>'宋体','Size'=>'12','Bold'=>'1')));
            //header
            foreach ((array)$statheader as $k => $v){
                $excel_data[0][] = array('styleid'=>'s_title','data'=>$v['text']);
            }
            //data
            foreach ((array)$statlist as $k => $v){
                foreach ((array)$statheader as $h_k=>$h_v){
                    $excel_data[$k+1][] = array('data'=>$v[$h_v['key']]);
                }
            }
            $excel_data = $excel_obj->charset($excel_data,CHARSET);
            $excel_obj->addArray($excel_data);
            $excel_obj->addWorksheet($excel_obj->charset('退款记录',CHARSET));
            $excel_obj->generateXML($excel_obj->charset('退款记录',CHARSET).date('Y-m-d-H',time()));
            exit();
        }
    }

    /**
     * 退款统计
     */
    public function get_refund_highchartsOp(){
        if(!$this->search_arr['search_type']){
            $this->search_arr['search_type'] = 'day';
        }
        $model = Model('stat');

        //获得搜索的开始时间和结束时间
        $searchtime_arr = $model->getStarttimeAndEndtime($this->search_arr);

        $field = ' SUM(refund_amount) as amount ';
        if($this->search_arr['search_type'] == 'day'){
            //构造横轴数据
            for($i=0; $i<24; $i++){
                $stat_arr['xAxis']['categories'][] = "$i";
                $statlist[$i] = 0;
            }
            $field .= ' ,HOUR(FROM_UNIXTIME(add_time)) as timeval ';
            if (C('dbdriver') == 'oracle') $_group = 'HOUR(FROM_UNIXTIME(add_time))';
        }
        if($this->search_arr['search_type'] == 'week'){
            //构造横轴数据
            for($i=1; $i<=7; $i++){
                $tmp_weekarr = getSystemWeekArr();
                //横轴
                $stat_arr['xAxis']['categories'][] = $tmp_weekarr[$i];
                unset($tmp_weekarr);
                $statlist[$i] = 0;
            }
            $field .= ' ,WEEKDAY(FROM_UNIXTIME(add_time))+1 as timeval ';
            if (C('dbdriver') == 'oracle') $_group = 'WEEKDAY(FROM_UNIXTIME(add_time))+1';
        }
        if($this->search_arr['search_type'] == 'month'){
            //计算横轴的最大量（由于每个月的天数不同）
            $dayofmonth = date('t',$searchtime_arr[0]);
            //构造横轴数据
            for($i=1; $i<=$dayofmonth; $i++){
                //横轴
                $stat_arr['xAxis']['categories'][] = $i;
                $statlist[$i] = 0;
            }
            $field .= ' ,day(FROM_UNIXTIME(add_time)) as timeval ';
            if (C('dbdriver') == 'oracle') $_group = 'day(FROM_UNIXTIME(add_time))';
        }
        $where = array();
        $where['add_time'] = array('between',$searchtime_arr);
        $statlist_tmp = $model->statByRefundreturn($where, $field, 0, 0, 'timeval asc', $_group? $_group : 'timeval');
        if ($statlist_tmp){
            foreach((array)$statlist_tmp as $k => $v){
                $statlist[$v['timeval']] = floatval($v['amount']);
            }
        }
        //得到统计图数据
        $stat_arr['legend']['enabled'] = false;
        $stat_arr['series'][0]['name'] = '退款金额';
        $stat_arr['series'][0]['data'] = array_values($statlist);
        $stat_arr['title'] = '退款金额统计';
        $stat_arr['yAxis'] = '金额';
        $stat_json = getStatData_LineLabels($stat_arr);
        Tpl::output('stat_json',$stat_json);
		Tpl::setDirquna('shop');/*网 店 运 维shop wwi.com*/
        Tpl::showpage('stat.linelabels','null_layout');
    }
    /**
     * 输出退款统计XML数据
     */
    public function get_refundlist_xmlOp(){
        $where = array();
        $model = Model('stat');
        $searchtime_arr = $model->getStarttimeAndEndtime($this->search_arr);
        $where['add_time'] = array('between',$searchtime_arr);
//        $where['add_time'] = array('between',[1558800000,1558886399]);//////////////
        $model_refund = Model('refund_return');
        $refundstate_arr = $model_refund->getRefundStateArray();

        $order_type = array('add_time','refund_amount');
        $sort_type = array('asc','desc');
        $sortname = trim($this->search_arr['sortname']);
        if (!in_array($sortname,$order_type)){
            $sortname = 'add_time';
        }
        $sortorder = trim($this->search_arr['sortorder']);
        if (!in_array($sortorder,$sort_type)){
            $sortorder = 'desc';
        }
        $orderby = $sortname.' '.$sortorder;
        $page = intval($_POST['rp']);
        if ($page < 1) {
            $page = 15;
        }

        $list = $model_refund->getRefundReturnList($where, $page, '*', '', $orderby);
//        echo '<pre>';
//        echo $model_refund->getLastSql();
//        var_dump($list);
//        die;

        $statlist = array();
        if (!empty($list) && is_array($list)){
            $fields_array = array('order_sn','refund_sn','store_name','goods_name','buyer_name','add_time','refund_amount','seller_state','refund_state');
            foreach ($list as $k => $v){
                $out_array = getFlexigridArray(array(),$fields_array,$v,'');
                if ($v['goods_id'] > 0) {
                    $out_array['goods_name'] = '<a href="'.urlShop('goods', 'index', array('goods_id' => $v['goods_id'])).'" target="_blank">'.$v['goods_name'].'</a>';
                }
                $out_array['add_time'] = date('Y-m-d H:i:s',$v['add_time']);
                $out_array['refund_amount'] = ncPriceFormat($v['refund_amount']);
                $out_array['seller_state'] = $refundstate_arr['seller'][$v['seller_state']];
                $out_array['refund_state'] = $v['seller_state']==2 ? $refundstate_arr['admin'][$v['refund_state']]:'无';
                $statlist[$v['refund_id']] = $out_array;
            }
        }

        $data = array();
        $data['now_page'] = $model_refund->shownowpage();
        $data['total_num'] = $model_refund->gettotalnum();
        $data['list'] = $statlist;
        echo Tpl::flexigridXML($data);exit();
    }
    /**
     * 店铺动态评分统计
     */
    public function evalstoreOp(){
        //店铺分类
        Tpl::output('class_list', rkcache('store_class', true));

        $model = Model('stat');
        $where = array();
        if(intval($_GET['store_class']) > 0){
            $where['sc_id'] = intval($_GET['store_class']);
        }
        if (trim($this->search_arr['storename'])){
            $where['seval_storename'] = array('like',"%".trim($this->search_arr['storename'])."%");
        }
        if (!empty($_GET['id']) && is_array($_GET['id'])){
            $where['seval_storeid'] = array('in',$_GET['id']);
        }
        $field = ' seval_storeid, min(seval_storename) as seval_storename';
        $field .= ' ,(SUM(seval_desccredit)/COUNT(*)) as avgdesccredit';
        $field .= ' ,(SUM(seval_servicecredit)/COUNT(*)) as avgservicecredit';
        $field .= ' ,(SUM(seval_deliverycredit)/COUNT(*)) as avgdeliverycredit';
        $orderby = 'avgdesccredit desc,seval_storeid';
        //导出Excel
        if ($this->search_arr['exporttype'] == 'excel'){
            $statlist_tmp = $model->statByStoreAndEvaluatestore($where, $field, 0, 0, $orderby, 'seval_storeid');
            foreach((array)$statlist_tmp as $k => $v){
                $tmp = $v;
                $tmp['avgdesccredit'] = round($v['avgdesccredit'],2);
                $tmp['avgservicecredit'] = round($v['avgservicecredit'],2);
                $tmp['avgdeliverycredit'] = round($v['avgdeliverycredit'],2);
                $statlist[] = $tmp;
            }
            import('libraries.excel');
            $excel_obj = new Excel();
            $excel_data = array();
            //设置样式
            $excel_obj->setStyle(array('id'=>'s_title','Font'=>array('FontName'=>'宋体','Size'=>'12','Bold'=>'1')));
            //header
            $excel_data[0][] = array('styleid'=>'s_title','data'=>'店铺名称');
            $excel_data[0][] = array('styleid'=>'s_title','data'=>'描述相符度');
            $excel_data[0][] = array('styleid'=>'s_title','data'=>'服务态度');
            $excel_data[0][] = array('styleid'=>'s_title','data'=>'发货速度');
            //data
            foreach ((array)$statlist as $k => $v){
                $excel_data[$k+1][] = array('data'=>$v['seval_storename']);
                $excel_data[$k+1][] = array('data'=>$v['avgdesccredit']);
                $excel_data[$k+1][] = array('data'=>$v['avgservicecredit']);
                $excel_data[$k+1][] = array('data'=>$v['avgdeliverycredit']);
            }
            $excel_data = $excel_obj->charset($excel_data,CHARSET);
            $excel_obj->addArray($excel_data);
            $excel_obj->addWorksheet($excel_obj->charset('店铺动态评分统计',CHARSET));
            $excel_obj->generateXML($excel_obj->charset('店铺动态评分统计',CHARSET).date('Y-m-d-H',time()));
            exit();
        }
        Tpl::output('top_link',$this->sublink($this->links, 'evalstore'));
		Tpl::setDirquna('shop');/*网 店 运 维shop wwi.com*/
        Tpl::showpage('stat.aftersale.evalstore');
    }

    /**
     * 输出店铺动态评分统计XML数据
     */
    public function get_evalstore_xmlOp(){
        $model = Model('stat');
        $where = array();
        if(intval($_GET['store_class']) > 0){
            $where['sc_id'] = intval($_GET['store_class']);
        }
        if (trim($this->search_arr['storename'])){
            $where['seval_storename'] = array('like',"%".trim($this->search_arr['storename'])."%");
        }
        $field = ' seval_storeid, min(seval_storename) as seval_storename';
        $field .= ' ,(SUM(seval_desccredit)/COUNT(*)) as avgdesccredit';
        $field .= ' ,(SUM(seval_servicecredit)/COUNT(*)) as avgservicecredit';
        $field .= ' ,(SUM(seval_deliverycredit)/COUNT(*)) as avgdeliverycredit';

        $order_type = array('seval_storename','avgdesccredit','avgservicecredit','avgdeliverycredit');
        $sort_type = array('asc','desc');
        $sortname = trim($this->search_arr['sortname']);
        if (!in_array($sortname,$order_type)){
            $sortname = 'avgdesccredit';
        }
        $sortorder = trim($this->search_arr['sortorder']);
        if (!in_array($sortorder,$sort_type)){
            $sortorder = 'desc';
        }
        $orderby = $sortname.' '.$sortorder.',seval_storeid';
        $page = intval($_POST['rp']);
        if ($page < 1) {
            $page = 15;
        }
        //查询评论的店铺总数
        $count_arr = $model->statByStoreAndEvaluatestore($where, 'count(DISTINCT evaluate_store.seval_storeid) as countnum');
        $countnum = intval($count_arr[0]['countnum']);
        $list = $model->statByStoreAndEvaluatestore($where, $field, array($page,$countnum), 0, $orderby, 'seval_storeid');
        $statlist = array();
        if (!empty($list) && is_array($list)){
            foreach ($list as $k => $v){
                $out_array = getFlexigridArray(array(),$order_type,$v,'');
                $out_array['avgdesccredit'] = round($v['avgdesccredit'],2);
                $out_array['avgservicecredit'] = round($v['avgservicecredit'],2);
                $out_array['avgdeliverycredit'] = round($v['avgdeliverycredit'],2);
                $statlist[$v['seval_storeid']] = $out_array;
            }
        }
        $data = array();
        $data['now_page'] = $model->shownowpage();
        $data['total_num'] = $countnum;
        $data['list'] = $statlist;
        echo Tpl::flexigridXML($data);exit();
    }

    //s
    public function order_refundOp() {
        Tpl::output('top_link',$this->sublink($this->links, 'order_refund'));
        Tpl::output('qtype_time','add_time');
        Tpl::output('query_start_date',time() - 24*3600);
        Tpl::output('query_end_date',time() - 24*3600);
        $member_list=Model('member_fenxiao')->getMemberFenxiao();
        $fx_members = array_column($member_list, 'member_cn_code', 'member_en_code');
        Tpl::output('fx_member',$fx_members);
        Tpl::setDirquna('shop');
        Tpl::showpage('stat.aftersale.order_refund');
    }

    /**
     * 输出订单退款统计XML数据
     */
    public function get_order_refund_xmlOp(){
        /** @var refund_returnModel $model_refund */
        $model_refund = Model('refund_return');
        $condition = array();

        list($condition,$order) = $this->_get_condition($condition);
        //$list = $model_refund->getRefundReturnList($where, $page, 'refund_return.*', '', $order);
        $refund_list = $model_refund->getOrderRefundReturnList($condition,!empty($_POST['rp']) ? intval($_POST['rp']) : 15,'refund_return.*, orders.fx_order_id, orders.buyer_name as order_buyer_name', '', $order);
        $refundstate_arr = $model_refund->getRefundStateArray();
        $data['now_page'] = $model_refund->shownowpage();
        $data['total_num'] = $model_refund->gettotalnum();

        $fields_array = array('order_sn','refund_sn','store_name','goods_name','buyer_name','add_time','refund_amount','seller_state','refund_state');
        $pic_base_url = UPLOAD_SITE_URL.'/'.ATTACH_PATH.'/refund/';
        $member_list=Model('member_fenxiao')->getMemberFenxiao();
        $fx_members = array_column($member_list, 'member_cn_code', 'member_en_code');
        foreach ($refund_list as $k => $v){
            $out_array = getFlexigridArray(array(),$fields_array,$v,'');
            if ($v['goods_id'] > 0) {
                $out_array['goods_name'] = '<a href="'.urlShop('goods', 'index', array('goods_id' => $v['goods_id'])).'" target="_blank">'.$v['goods_name'].'</a>';
            }
            $out_array['add_time'] = date('Y-m-d H:i:s',$v['add_time']);
            $out_array['refund_amount'] = ncPriceFormat($v['refund_amount']);
            $out_array['seller_state'] = $refundstate_arr['seller'][$v['seller_state']];
            $out_array['refund_state'] = $v['seller_state']==2 ? $refundstate_arr['admin'][$v['refund_state']]:'无';
            $out_array['seller_message'] = "<span title='{$v['seller_message']}'>{$v['seller_message']}</i>";
            $out_array['admin_message'] = "<span title='{$v['admin_message']}'>{$v['admin_message']}</span>";
            $out_array['operation_type'] = '';
            switch ($v['operation_type']){
                case 0:$out_array['operation_type']='用户申请';break;
                case 1:$out_array['operation_type']='后台处理';break;
                case 2:$out_array['operation_type']='渠道抓取';break;
            }
            if ($v['fx_order_id']) {
                $out_array['fx_name'] = isset($fx_members[$v['order_buyer_name']]) ? $fx_members[$v['order_buyer_name']] : '';
            } else {
                $out_array['fx_name'] = '';
            }

            if(!empty($v['pic_info'])) {
                $info = unserialize($v['pic_info']);
                if (is_array($info) && !empty($info['buyer'])) {
                    foreach($info['buyer'] as $pic_name) {
                        $out_array['pic_info'] .= "<a href='".$pic_base_url.$pic_name."' target='_blank' class='pic-thumb-tip' onMouseOut='toolTip()' onMouseOver='toolTip(\"<img src=".$pic_base_url.$pic_name.">\")'><i class='fa fa-picture-o'></i></a> ";
                    }
                    $out_array['pic_info'] = trim($out_array['pic_info']);
                }
            } else {
                $out_array['pic_info'] = '';
            }
            $data['list'][$v['refund_id']] = $out_array;

        }

        echo Tpl::flexigridXML($data);
        exit();

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
        if (!in_array($_GET['qtype_time'],array('add_time','seller_time','admin_time','order_add_time'))) {
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
            $condition['refund_state'] = intval($_GET['refund_state']);
        }
        $sort_fields = array('buyer_name','store_name','goods_id','refund_id','seller_time','refund_amount','buyer_id','store_id');
        if ($_REQUEST['sortorder'] != '' && in_array($_REQUEST['sortname'],$sort_fields)) {
            $order = $_REQUEST['sortname'].' '.$_REQUEST['sortorder'];
        }
        if( $_GET['fxsellerdo'] == 1 ) {
            //$order = 'seller_time desc';
        }
        //渠道
        if ($_GET['order_channel']) {
            $condition['order_channel'] = trim($_GET['order_channel']);
        }
        $new_condition = array();
        if (!empty($condition)) {
            foreach ($condition as $key=> $vs) {
                if ($key == 'order_add_time') {
                    $new_condition['orders.add_time'] = $vs;
                } elseif ($key == 'order_channel') {
                    $new_condition['orders.buyer_name'] = $vs;
                } else {
                    $new_condition['refund_return.'. $key] = $vs;
                }
            }
        }
        return array($new_condition,$order);
    }

    public function export_step1Op() {

        /** @var refund_returnModel $model_refund */
        $model_refund = Model('refund_return');

        $condition = array();
        list($condition,$order) = $this->_get_condition($condition);
        $refundstate_arr = $model_refund->getRefundStateArray();
        $fields_array = array('order_sn','refund_sn','store_name','goods_name','buyer_name','add_time','refund_amount','seller_state','refund_state');
        $member_list=Model('member_fenxiao')->getMemberFenxiao();
        $fx_members = array_column($member_list, 'member_cn_code', 'member_en_code');

        //error_reporting(0);
        $csv_name= date('Y-m-d H:i:s', time()). '交易订单退款列表';
        header ( "Content-type:application/vnd.ms-excel" );
        header ( "Content-Disposition:filename=" . iconv ( "UTF-8", "GBK", $csv_name) . ".csv" );

        $header_arr = array (
            'A' => '订单编号',
            'B' => '退单编号',
            'C' => '商家名称',
            'D' => '商品名称',
            'E' => '买家会员名',
            'F' => '申请时间',
            'G' => '退款金额',
            'H' => '商家审核',
            'I' => '平台确认',
            'J' => '商家处理备注信息',
            'K' => '平台处理备注信息',
            'L' => '操作来源',
            'M' => '渠道',
            'N' => '订单创建时间'
        );

        // 打开PHP文件句柄，php://output 表示直接输出到浏览器
        $fp = fopen('php://output', 'a');

        foreach ($header_arr as $i => $v) {
            $header_arr[$i] = iconv('utf-8', 'gbk', $v);
        }
        // 将标题名称通过fputcsv写到文件句柄
        fputcsv($fp, $header_arr);
        $page = 0;
        $limit = 1000;
        while (true) {
            $page_start = $page * $limit;
            $limiter = "$page_start, $limit";
            $refund_list = $model_refund->getOrderRefundReturnList($condition, '' ,'refund_return.*, orders.fx_order_id, orders.buyer_name as order_buyer_name, orders.add_time as order_add_time', $limiter, $order);
            if (empty($refund_list)) {
                break;
            }

            foreach ($refund_list as $item) {
                $out_array = getFlexigridArray(array(),$fields_array,$item,'');
                $out_array['order_sn'] =  $out_array['order_sn']. "\t";
                $out_array['refund_sn'] =  $out_array['refund_sn']. "\t";
                if ($item['goods_id'] > 0) {
                    $out_array['goods_name'] = $item['goods_name'];
                }
                $out_array['add_time'] = date('Y-m-d H:i:s',$item['add_time']);
                $out_array['refund_amount'] = ncPriceFormat($item['refund_amount']);
                $out_array['seller_state'] = $refundstate_arr['seller'][$item['seller_state']];
                $out_array['refund_state'] = $item['seller_state']==2 ? $refundstate_arr['admin'][$item['refund_state']]:'无';
                $out_array['seller_message'] = $item['seller_message'];
                $out_array['admin_message'] = $item['admin_message'];
                $out_array['operation_type'] = '';
                switch ($item['operation_type']){
                    case 0:$out_array['operation_type']='用户申请';break;
                    case 1:$out_array['operation_type']='后台处理';break;
                    case 2:$out_array['operation_type']='渠道抓取';break;
                }
                if ($item['fx_order_id']) {
                    $out_array['fx_name'] = isset($fx_members[$item['order_buyer_name']]) ? $fx_members[$item['order_buyer_name']] : '';
                } else {
                    $out_array['fx_name'] = '';
                }
                $out_array['order_add_time'] = $item['order_add_time'] ? date('Y-m-d H:i:s',$item['order_add_time']) : '';

                $rows  = array();
                foreach ($out_array as $k=>$v) {
                    $rows[] = iconv('utf-8', 'GBK', $v);
                }

                fputcsv($fp, $rows);
            }
            // 将已经写到csv中的数据存储变量销毁，释放内存占用
            unset($order_data);
            //刷新缓冲区
            ob_flush();
            flush();
            $page ++;
        }
        exit;

    }

    /**
     * 获取订单、退单信息
     * @param string $group_field
     * @return array
     */
    private function getOrderReturnInfo($group_field='store_id'){
        $model_refund = Model('refund_return');
        $model_order = Model('order');

        //搜索时间
        if($_GET['query_start_date']) {
            $searchtime_arr = [strtotime($_GET['query_start_date']), strtotime($_GET['query_end_date'])];
        }else{
            //默认搜索本周的结果
            $searchtime_arr = [strtotime('-7 days'), strtotime(date('Y-m-d').' 23:59:59')];
        }
        $where=[];
        $where['add_time'] = array('between', $searchtime_arr);
        $where2['add_time'] = array('between', $searchtime_arr);

        $order = 'order_num desc';
        //根据店铺或者商品分组
        $group_orders = $group_refund = $group_field;
        //orders表没有goods_id字段，则不分组，仅返回order_id数组信息供下一步分组查询
        if($group_field == 'store_id') {
            $having = 'count(*)>100';
            $field_orders = '*,count(*) as order_num';
        }else{
            $group_orders = '';
            $having = '';
            $field_orders = 'store_name,order_id';
            $order = '';
        }

        //获取订单信息
        $order_list = $model_order->getOrderGroupBusiness($where2,$field_orders,$group_orders,$order,99999,$having);

        //获取退单信息
        $field_refund = '*,count(*) as order_num,sum(refund_amount) as refund_amount_total';
        $refund_list = $model_refund->getRefundGroupBusiness($where,$field_refund,$group_refund,$order,99999);

        return ['order'=>$order_list,'return'=>$refund_list];
    }
    /**
     * 计算退单率，分售前、售后
     * @param $order_list
     * @param $refund_list
     * @param $field
     * @return array
     */
    private function countReturnRate($order_list,$refund_list,$field='store_id'){
        $rp = $_POST['rp']?$_POST['rp']:20;//抽取前20名
        $refund_list_info = array_column($refund_list,null,$field);//store_id=>$v

        //将退单信息根据售前售后分类
        $refund_category = [];
        foreach ($refund_list as $k=>$v){
            $refund_category[$v['order_lock']][$k] = $v;
        }
        $lock_2_list_store_id = array_column($refund_category[2],null,$field);//售前 store_id=>$v
        $lock_1_list_store_id = array_column($refund_category[1],null,$field);//售后 store_id=>$v

        //计算退单率并排序
        $before_sale = $after_sale = $temp_sort_array = $sort_data =[];
        foreach ($order_list as $v){
            $order_number = $v['order_num'];//订单数量
            if(isset($refund_list_info[$v[$field]])){//售后整体数据
                $refund_number = $refund_list_info[$v[$field]]['order_num'];//退单数量
                $rate = $order_number==0?0:ncPriceFormat($refund_number/$order_number,3);
                $sort_data[$v[$field]] = $rate;
            }
            if(isset($lock_2_list_store_id[$v[$field]])){//售前
                $refund_number = $lock_2_list_store_id[$v[$field]]['order_num'];//退单数量
                $rate = $order_number==0?0:ncPriceFormat($refund_number/$order_number,3);
                $before_sale[$v[$field]]['num'] = $refund_number;
                $before_sale[$v[$field]]['rate'] = $rate;
                $temp_sort_array[$v[$field]]['rate'] = $rate;
            }
            if(isset($lock_1_list_store_id[$v[$field]])){//售后
                $refund_number = $lock_1_list_store_id[$v['store_id']]['order_num'];//退单数量
                $rate = $order_number==0?0:ncPriceFormat($refund_number/$order_number,3);
                $after_sale[$v[$field]]['num'] = $refund_number;
                $after_sale[$v[$field]]['rate'] = $rate;
            }
        }

//        if($field != 'goods_id'){
//            $temp_data = [];
//            arsort($temp_sort_array);//列表不显示售后整体数据字段的情况，根据售前排序，使用售后整体的数据
//            foreach ($temp_sort_array as $k=>$v){
//                $temp_data[$k] = $sort_data[$k];
//            }
//            $sort_data = $temp_data;
//        }
//        arsort($sort_data);//根据值倒序排列，保留键名
        $sort_data = array_slice($sort_data,($_POST['curpage']-1)*$rp,$rp,true);
        return ['sort'=>$sort_data,'before'=>$before_sale,'after'=>$after_sale];
    }

    /**
     * 排序方法
     * @param $data 源数据
     * @param $sort_array 以排序字段为值的数组
     * @return array
     */
    private function sortData($data,$sort_array){
        $new_sort = [];

        if($_POST['sortorder'] == 'asc'){
            asort($sort_array);//根据值倒序排列，保留键名
        }else{
            arsort($sort_array);//根据值倒序排列，保留键名
        }
        foreach ($sort_array as $k=>$v){
            $new_sort[$k] = $data['list'][$k];
        }
        return $new_sort;
    }
    /**
     * 售后率最高的几个商家
     */
    public function businessStatisticsOp(){
        Tpl::output('top_link',$this->sublink($this->links, 'businessStatistics'));
        Tpl::output('query_start_date',time() - 24*3600);
        Tpl::output('query_end_date',time() - 24*3600);
        Tpl::setDirquna('shop');
        Tpl::showpage('stat.aftersale.business_statistics');
    }
    public function get_business_xmlOp(){
        set_time_limit(0);
        ini_set('memory_limit','3G');

        //获取退单信息
        $order_return_info = $this->getOrderReturnInfo();
        $refund_list = $order_return_info['return'];
        $refund_list_info = array_column($refund_list,null,'store_id');//store_id=>$v

        //获取订单信息
        $order_list = $order_return_info['order'];
        $order_sort = array_column($order_list,'order_num','store_id');

        //计算退单率并排序
        $count_result = $this->countReturnRate($order_list,$refund_list);
        $sort_data = $count_result['sort'];
        $before_sale = $count_result['before'];
        $after_sale = $count_result['after'];

        $sort_array = $data = [];
        foreach ($sort_data as $s_store_id => $s_rate){
            $temp_info = $refund_list_info[$s_store_id];
            $out_array['store_name'] = $temp_info['store_name'];
            $out_array['refund_amount_total'] = ncPriceFormat($temp_info['refund_amount_total']);
            $out_array['before_sale_num'] = $before_sale[$s_store_id]['num']?$before_sale[$s_store_id]['num']:0;
            $out_array['after_sale_num'] = $after_sale[$s_store_id]['num']?$after_sale[$s_store_id]['num']:0;
            $out_array['order_num'] = $temp_info['order_num'];
            $out_array['order_total'] = $order_sort[$s_store_id];
            $out_array['refund_rate'] = $s_rate;
            $out_array['before_sale_rate'] = $before_sale[$s_store_id]['rate']?($before_sale[$s_store_id]['rate']*100).'%':'0%';
            $out_array['after_sale_rate'] = $after_sale[$s_store_id]['rate']?($after_sale[$s_store_id]['rate']*100).'%':'0%';

            $data['list'][$temp_info['refund_id']] = $out_array;

            //排序
            $sort_array[$temp_info['refund_id']] = $out_array[$_POST['sortname']];
        }

        $param = array('store_name', 'refund_amount_total', 'before_sale_num', 'after_sale_num', 'order_num', 'order_total', 'refund_rate', 'before_sale_rate'
        , 'after_sale_rate');
        if (in_array($_POST['sortname'], $param) && in_array($_POST['sortorder'], array('asc', 'desc'))) {
            //排序
            $data['list'] = $this->sortData($data, $sort_array);
        }

        $data['now_page'] = $_POST['curpage'];
        $data['total_num'] = count($data['list']);
        echo Tpl::flexigridXML($data);exit();
    }

    /**
     * 售后率最高的几个商品
     */
    public function commodityStatisticsOp(){
        Tpl::output('top_link',$this->sublink($this->links, 'commodityStatistics'));
        Tpl::output('query_start_date',time() - 24*3600);
        Tpl::output('query_end_date',time() - 24*3600);
        Tpl::setDirquna('shop');
        Tpl::showpage('stat.aftersale.commodity_statistics');
    }
    public function get_commodity_xmlOp(){
        set_time_limit(0);
        ini_set('memory_limit','3G');

        $order_return_info = $this->getOrderReturnInfo('goods_id');
        $refund_list = $order_return_info['return'];
        $refund_list_info = array_column($refund_list,null,'goods_id');

        //获取订单信息，仅筛选时间
        $order_list = $order_return_info['order'];
        $order_ids = array_column($order_list,'order_id');
        $store_name_list = array_column($order_list,'store_name','order_id');

        //获取商品订单信息
        $where3 = $order_ids?array('order_id'=>array('in',$order_ids)):'order_id>0';
        $fields = '*,count(*) as order_num';
        $group = 'goods_id';
        $order = 'order_num desc';
        $model_order_goods = Model('order_goods');
        $having = 'order_num > 100';
        $order_goods_list = $model_order_goods->getGroupBusiness($where3,$fields,$group,$order,99999,$having);
        $order_sort = array_column($order_goods_list,'order_num','goods_id');
        $order_id_list = array_column($order_goods_list,'order_id','goods_id');
        //计算退单率并排序
        $count_result = $this->countReturnRate($order_goods_list,$refund_list,'goods_id');
        $sort_data = $count_result['sort'];
        $before_sale = $count_result['before'];
        $after_sale = $count_result['after'];

        $data = $sort_array = [];
        foreach ($sort_data as $s_good_id => $s_rate){
            if($temp_info = $refund_list_info[$s_good_id]) {
                $out_array['operation'] = "<a class=\"btn green\" href=\"index.php?act=stat_aftersale&op=reasonStatistics&goods_id={$s_good_id}&query_start_date={$_GET['query_start_date']}&query_end_date={$_GET['query_end_date']}\"><i class=\"fa fa-list-alt\"></i>查看</a>";
                $out_array['store_name'] = $store_name_list[$order_id_list[$s_good_id]];
                $out_array['goods_name'] = $temp_info['goods_name'];
                $out_array['refund_amount_total'] = ncPriceFormat($temp_info['refund_amount_total']);
                $out_array['order_num'] = $temp_info['order_num'];
                $out_array['before_sale_num'] = $before_sale[$s_good_id]['num'] ? $before_sale[$s_good_id]['num'] : 0;
                $out_array['after_sale_num'] = $after_sale[$s_good_id]['num'] ? $after_sale[$s_good_id]['num'] : 0;
                $out_array['order_total'] = $order_sort[$s_good_id];
                $out_array['before_sale_rate'] = $before_sale[$s_good_id]['rate'] ? ($before_sale[$s_good_id]['rate'] * 100) . '%' : '0%';
                $out_array['after_sale_rate'] = $after_sale[$s_good_id]['rate'] ? ($after_sale[$s_good_id]['rate'] * 100) . '%' : '0%';

                $data['list'][$temp_info['refund_id']] = $out_array;
                //排序
                $sort_array[$temp_info['refund_id']] = $out_array[$_POST['sortname']];
            }
        }

        $param = array('store_name','goods_name','refund_amount_total','order_num','before_sale_num', 'after_sale_num', 'order_total', 'before_sale_rate'
        , 'after_sale_rate');
        if (in_array($_POST['sortname'], $param) && in_array($_POST['sortorder'], array('asc', 'desc'))) {
            //排序
            $data['list'] = $this->sortData($data, $sort_array);
        }

        $data['now_page'] = $_POST['curpage'];
        $data['total_num'] = count($data['list']);

        echo Tpl::flexigridXML($data);exit();
    }

    /**
     * 售后率高的商品退款原因
     */
    public function reasonStatisticsOp(){
        $goods_id = $_GET['goods_id'];
        $start_date=$_GET['query_start_date'];
        $end_date=$_GET['query_end_date'];

        Tpl::output('start_date',$start_date);
        Tpl::output('end_date',$end_date);
        Tpl::output('goods_id',$goods_id);
        Tpl::output('top_link',$this->sublink($this->links, 'commodityStatistics'));
        Tpl::output('query_start_date',time() - 24*3600);
        Tpl::output('query_end_date',time() - 24*3600);
        Tpl::setDirquna('shop');
        Tpl::showpage('stat.aftersale.reasonStatistics');
    }
    public function get_reason_xmlOp(){
        set_time_limit(0);
        ini_set('memory_limit','3G');
        $goods_id = $_GET['goods_id'];

        $model_refund = Model('refund_return');
        $model_order = Model('order');
        $model_order_goods = Model('order_goods');

        //搜索时间
        if($_GET['query_start_date']) {
            $searchtime_arr = [strtotime($_GET['query_start_date']), strtotime($_GET['query_end_date'])];
        }else{
            //默认搜索本周的结果
            $searchtime_arr = [strtotime('-7 days'), strtotime(date('Y-m-d').' 23:59:59')];
        }
        $where=[];
        $where['add_time'] = array('between', $searchtime_arr);
        $where3['add_time'] = array('between', $searchtime_arr);

        //获取商品订单信息
        $where3['goods_id'] = $goods_id;
        $field_refund = '*,count(*) as order_num,sum(refund_amount) as refund_amount_total';
        $refund_list = $model_refund->getRefundGroupBusiness($where3,$field_refund,'reason_id','order_num desc');

        //获取售前售后分类信息
        $order_id_list = array_column($refund_list,'order_id');
        $order_list = $model_order->getOrderList(['order_id'=>['in',$order_id_list]],'order_id,order_lock,store_id,store_name');
        $order_lock_list = array_column($order_list,'order_lock','order_id');//2是售前

        //获取退款原因信息
        $reason_id_list = array_column($refund_list,'reason_id');
        $reason_list = $model_refund->getReasonList(['reason_id'=>['in',$reason_id_list]]);
        $reason_list = array_column($reason_list,'reason_info','reason_id');

        //获取商品名
        $goods_id_list = array_column($refund_list,'goods_id');
        $goods_list = $model_order_goods->getOrderGoodsList(['goods_id'=>['in',$goods_id_list]]);
        $goods_list = array_column($goods_list,'goods_name','goods_id');

        //获取店铺名
        $store_id = current($refund_list);
        $store_id = $store_id['store_id'];
        $store_list = array_column($order_list,'store_name','store_id');

        $store_info = $store_list[$store_id];

        $data = $sort_array = [];
        foreach ($refund_list as $k=>$v){
            $out_array['store_name'] = $store_info;
            $out_array['goods_name'] = $goods_list[$v['goods_id']];
            $out_array['reason_info'] = $reason_list[$v['reason_id']];
            $out_array['refund_amount_total'] = $v['refund_amount_total'];

            if($order_lock_list[$v['order_id']] == 2){
                $out_array['before_sale'] = $v['order_num'];
                $out_array['after_sale'] = 0;
            }else{
                $out_array['before_sale'] = 0;
                $out_array['after_sale'] = $v['order_num'];
            }
            $out_array['order_num'] = $v['order_num'];
            $out_array['refund_rate'] = ncPriceFormat($v['order_num']/array_sum(array_column($refund_list,order_num))*100) . '%';//占比

            $data['list'][$k] = $out_array;
            //排序
            $sort_array[$k] = $out_array[$_POST['sortname']];
        }

        $param = array('store_name','goods_name','reason_info','refund_amount_total','before_sale', 'after_sale', 'order_num', 'refund_rate');
        if (in_array($_POST['sortname'], $param) && in_array($_POST['sortorder'], array('asc', 'desc'))) {
            //排序
            $data['list'] = $this->sortData($data, $sort_array);
        }
        $data['now_page'] = $_POST['curpage'];
        $data['total_num'] = count($data['list']);

        $data['now_page'] = $_POST['curpage'];
        $data['total_num'] = count($data['list']);
        echo Tpl::flexigridXML($data);exit();
    }
}
