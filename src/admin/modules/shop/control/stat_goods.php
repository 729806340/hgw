<?php
/**
 * 商品分析
 *
 * @汉购网提供技术支持 授权请购买shopnc授权
 * @license    http://www.hangowa.com
 * @link       交流群号：
 */



defined('ByShopWWI') or exit('Access Invalid!');

class stat_goodsControl extends SystemControl{
    private $links = array(
        array('url'=>'act=stat_goods&op=pricerange','lang'=>'stat_goods_pricerange'),
        array('url'=>'act=stat_goods&op=hotgoods','lang'=>'stat_hotgoods'),
        array('url'=>'act=stat_goods&op=goods_sale','lang'=>'stat_goods_sale'),
    	array('url'=>'act=stat_goods&op=pricerange_fenxiao','lang'=>'stat_goods_pricerange_fx'),
    	array('url'=>'act=stat_goods&op=hotgoods_fenxiao','lang'=>'stat_hotgoods_fx'),
    	array('url'=>'act=stat_goods&op=goods_sale_fenxiao','lang'=>'stat_goods_sale_fx'),
        array('url'=>'act=stat_goods&op=goods_refund','lang'=>'stat_goods_refund'),
    );
    private $search_arr;//处理后的参数
    private $gc_arr;//分类数组
    private $choose_gcid;//选择的分类ID

    public function __construct(){
        parent::__construct();
        Language::read('stat');
        import('function.statistics');
        import('function.datehelper');
        $model = Model('stat');
        //存储参数
        $this->search_arr = $_REQUEST;
        //处理搜索时间
        $op = array(
        		'pricerange','hotgoods','goods_sale','get_goods_xml','pricerange_fenxiao','hotgoods_fenxiao', 'goods_sale_fenxiao','area','map','get_goodsale_xml',"goods_refund","get_xml_refund"
        );
        if (!isset($this->search_arr['op']) || in_array($this->search_arr['op'],$op)){
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
        /**
         * 处理商品分类
         */
        $this->choose_gcid = ($t = intval($_REQUEST['choose_gcid']))>0?$t:0;
        $gccache_arr = Model('goods_class')->getGoodsclassCache($this->choose_gcid,3);
        $this->gc_arr = $gccache_arr['showclass'];
        Tpl::output('gc_json',json_encode($gccache_arr['showclass']));
        Tpl::output('gc_choose_json',json_encode($gccache_arr['choose_gcid']));
    }

    public function indexOp() {
        $this->pricerangeOp();
    }

    /**
     * 价格区间统计
     */
    public function pricerangeOp(){
        if(!$this->search_arr['search_type']){
            $this->search_arr['search_type'] = 'day';
        }
        $model = Model('stat');
        //获得搜索的开始时间和结束时间
        $searchtime_arr = $model->getStarttimeAndEndtime($this->search_arr);
        $where = array();
        $where['order_isvalid'] = 1;//计入统计的有效订单
        $where['order_add_time'] = array('between',$searchtime_arr);
        $where['_string'] = "payment_code NOT IN ('fenxiao', 'jicai')" ;//过滤分销
        //商品分类
        if ($this->choose_gcid > 0){
            //获得分类深度
            $depth = $this->gc_arr[$this->choose_gcid]['depth'];
            $where['gc_parentid_'.$depth] = $this->choose_gcid;
        }
        $field = '1';
        $pricerange_arr = ($t = trim(C('stat_pricerange')))?unserialize($t):'';
        if ($pricerange_arr){
            $stat_arr['series'][0]['name'] = '下单量';
            //设置价格区间最后一项，最后一项只有开始值没有结束值
            $pricerange_count = count($pricerange_arr);
            if ($pricerange_arr[$pricerange_count-1]['e']){
                $pricerange_arr[$pricerange_count]['s'] = $pricerange_arr[$pricerange_count-1]['e'] + 1;
                $pricerange_arr[$pricerange_count]['e'] = '';
            }
            foreach ((array)$pricerange_arr as $k => $v){
                $v['s'] = intval($v['s']);
                $v['e'] = intval($v['e']);
                //构造查询字段
                if (C('dbdriver') == 'mysql'||C('dbdriver') == 'mysqli') {
                    if ($v['e']){
                        $field .= " ,SUM(IF(goods_pay_price/goods_num > {$v['s']} and goods_pay_price/goods_num <= {$v['e']},goods_num,0)) as goodsnum_{$k}";
                    } else {
                        $field .= " ,SUM(IF(goods_pay_price/goods_num > {$v['s']},goods_num,0)) as goodsnum_{$k}";
                    }
                } elseif (C('dbdriver') == 'oracle') {
                    if ($v['e']){
                        $field .= " ,SUM((case when goods_pay_price/goods_num > {$v['s']} and goods_pay_price/goods_num <= {$v['e']} then goods_num else 0 end)) as goodsnum_{$k}";
                    } else {
                        $field .= " ,SUM((case when goods_pay_price/goods_num > {$v['s']} then goods_num else 0 end)) as goodsnum_{$k}";
                    }
                }
            }
            $ordergooods_list = $model->getoneByStatordergoods($where, $field);
            if($ordergooods_list){
                foreach ((array)$pricerange_arr as $k => $v){
                    //横轴
                    if($v['e']){
                        $stat_arr['xAxis']['categories'][] = $v['s'].'-'.$v['e'];
                    } else {
                        $stat_arr['xAxis']['categories'][] = $v['s'].'以上';
                    }
                    //统计图数据
                    if ($ordergooods_list['goodsnum_'.$k]){
                        $stat_arr['series'][0]['data'][] = intval($ordergooods_list['goodsnum_'.$k]);
                    } else {
                        $stat_arr['series'][0]['data'][] = 0;
                    }
                }
            }
            //得到统计图数据
            $stat_arr['title'] = '价格销量分布';
            $stat_arr['legend']['enabled'] = false;
            $stat_arr['yAxis'] = '销量';
            $pricerange_statjson = getStatData_LineLabels($stat_arr);
        } else {
            $pricerange_statjson = '';
        }

        Tpl::output('pricerange_statjson',$pricerange_statjson);
        Tpl::output('searchtime',implode('|',$searchtime_arr));
        Tpl::output('top_link',$this->sublink($this->links, 'pricerange'));
		Tpl::setDirquna('shop');/*网 店 运 维shop wwi.com*/
        Tpl::showpage('stat.goods.prange');
    }
    /**
     * 热卖商品
     */
    public function hotgoodsOp(){
        if(!$this->search_arr['search_type']){
            $this->search_arr['search_type'] = 'day';
        }
        $model = Model('stat');
        //获得搜索的开始时间和结束时间
        $searchtime_arr = $model->getStarttimeAndEndtime($this->search_arr);
        Tpl::output('searchtime',implode('|',$searchtime_arr));
        Tpl::output('top_link',$this->sublink($this->links, 'hotgoods'));
		Tpl::setDirquna('shop');/*网 店 运 维shop wwi.com*/
        Tpl::showpage('stat.goods.hotgoods');
    }
    /**
     * 热卖商品列表
     */
    public function hotgoods_listOp(){
        $model = Model('stat');
        switch ($_GET['type']){
           case 'goodsnum':
               $sort_text = '下单量';
               break;
           default:
               $_GET['type'] = 'orderamount';
               $sort_text = '下单金额';
               break;
        }
        //构造横轴数据
        for($i=1; $i<=50; $i++){
            //数据
            $stat_arr['series'][0]['data'][] = array('name'=>'','y'=>0);
            //横轴
            $stat_arr['xAxis']['categories'][] = "$i";
        }
        $where = array();
        $where['order_isvalid'] = 1;//计入统计的有效订单
        $searchtime_arr_tmp = explode('|',$this->search_arr['t']);
        foreach ((array)$searchtime_arr_tmp as $k => $v){
            $searchtime_arr[] = intval($v);
        }
        $where['order_add_time'] = array('between',$searchtime_arr);
        //商品分类
        if ($this->choose_gcid > 0){
            //获得分类深度
            $depth = $this->gc_arr[$this->choose_gcid]['depth'];
            $where['gc_parentid_'.$depth] = $this->choose_gcid;
        }
        //查询统计数据
        $field = ' goods_id,min(goods_name) as goods_name ';
        switch ($_GET['type']){
           case 'goodsnum':
               $field .= ' ,SUM(goods_num) as goodsnum ';
               $orderby = 'goodsnum desc';
               break;
           default:
               $_GET['type'] = 'orderamount';
               $field .= ' ,SUM(goods_pay_price) as orderamount ';
               $orderby = 'orderamount desc';
               break;
        }
        $orderby .= ',goods_id';
        if( isset($_GET['source']) && $_GET['source'] == 'fenxiao' ) {
        	$where['payment_code'] = 'fenxiao' ;
        } else {
        	$where['_string'] = "payment_code NOT IN ('fenxiao', 'jicai')" ;//过滤分销
        }
        $statlist = $model->statByStatordergoods($where, $field, 0, 50, $orderby, 'goods_id');
        foreach ((array)$statlist as $k => $v){
            switch ($_GET['type']){
               case 'goodsnum':
                   $stat_arr['series'][0]['data'][$k] = array('name'=>strval($v['goods_name']),'y'=>intval($v[$_GET['type']]));
                   break;
               case 'orderamount':
                   $stat_arr['series'][0]['data'][$k] = array('name'=>strval($v['goods_name']),'y'=>floatval($v[$_GET['type']]));
                   break;
            }
            $statlist[$k]['sort'] = $k+1;
        }
        $stat_arr['series'][0]['name'] = $sort_text;
        $stat_arr['legend']['enabled'] = false;
        //得到统计图数据
        $stat_arr['title'] = '热卖商品TOP50';
        $stat_arr['yAxis'] = $sort_text;
        $stat_json = getStatData_Column2D($stat_arr);
        Tpl::output('stat_json',$stat_json);
        Tpl::output('statlist',$statlist);
        Tpl::output('sort_text',$sort_text);
        Tpl::output('stat_field',$_GET['type']);
		Tpl::setDirquna('shop');/*网 店 运 维shop wwi.com*/
        Tpl::showpage('stat.goods.hotgoods.list','null_layout');
    }

    /**
     * 商品销售明细
     */
    public function goods_saleOp(){
        if(!$this->search_arr['search_type']){
            $this->search_arr['search_type'] = 'day';
        }
        $model = Model('stat');
        //获得搜索的开始时间和结束时间
        $searchtime_arr = $model->getStarttimeAndEndtime($this->search_arr);
        //获取相关数据
        $where = array();
        $where['order_isvalid'] = 1;//计入统计的有效订单
        $where['order_add_time'] = array('between',$searchtime_arr);
        //品牌
        $brand_id = intval($_REQUEST['b_id']);
        if ($brand_id > 0){
            $where['brand_id'] = $brand_id;
        }
        //商品分类
        if ($this->choose_gcid > 0){
            //获得分类深度
            $depth = $this->gc_arr[$this->choose_gcid]['depth'];
            $where['gc_parentid_'.$depth] = $this->choose_gcid;
        }
        if(trim($_GET['goods_name'])){
            $where['goods_name'] = array('like','%'.trim($_GET['goods_name']).'%');
        }
        if(trim($_GET['store_name'])){
            $where['store_name'] = array('like','%'.trim($_GET['store_name']).'%');
        }
        if (!empty($_GET['id']) && is_array($_GET['id'])){
            $where['goods_id'] = array('in',$_GET['id']);
        }
        $where['_string'] = "payment_code NOT IN ('fenxiao', 'jicai')" ;//过滤分销
        $field = 'goods_id,min(goods_name) as goods_name,min(store_id) as store_id,min(store_name) as store_name,min(goods_commonid) as goods_commonid,SUM(goods_num) as goodsnum,COUNT(DISTINCT order_id) as ordernum,SUM(goods_pay_price) as goodsamount';
        $orderby = 'goodsnum desc,goods_id asc';
        //导出Excel
        if ($_GET['exporttype'] == 'excel'){
            $goods_list = $model->statByStatordergoods($where, $field, 0, 0, $orderby, 'goods_id');
            import('libraries.excel');
            $excel_obj = new Excel();
            $excel_data = array();
            //设置样式
            $excel_obj->setStyle(array('id'=>'s_title','Font'=>array('FontName'=>'宋体','Size'=>'12','Bold'=>'1')));
            //header
            $excel_data[0][] = array('styleid'=>'s_title','data'=>'商品名称');
            $excel_data[0][] = array('styleid'=>'s_title','data'=>'SPU');
            $excel_data[0][] = array('styleid'=>'s_title','data'=>'SKU');
            $excel_data[0][] = array('styleid'=>'s_title','data'=>'店铺名称');
            $excel_data[0][] = array('styleid'=>'s_title','data'=>'下单商品件数');
            $excel_data[0][] = array('styleid'=>'s_title','data'=>'下单单量');
            $excel_data[0][] = array('styleid'=>'s_title','data'=>'下单金额');
            //data
            foreach ($goods_list as $k => $v){
                $excel_data[$k+1][] = array('data'=>$v['goods_name']);
                $excel_data[$k+1][] = array('data'=>$v['goods_commonid']);
                $excel_data[$k+1][] = array('data'=>$v['goods_id']);
                $excel_data[$k+1][] = array('data'=>$v['store_name']);
                $excel_data[$k+1][] = array('data'=>$v['goodsnum']);
                $excel_data[$k+1][] = array('data'=>$v['ordernum']);
                $excel_data[$k+1][] = array('data'=>$v['goodsamount']);
            }
            $excel_data = $excel_obj->charset($excel_data,CHARSET);
            $excel_obj->addArray($excel_data);
            $excel_obj->addWorksheet($excel_obj->charset('商品销售明细',CHARSET));
            $excel_obj->generateXML($excel_obj->charset('商品销售明细',CHARSET).date('Y-m-d-H',time()));
            exit();
        } else {
            //查询品牌
            $brand_list = Model('brand')->getBrandList(array('brand_apply'=>1));
            Tpl::output('brand_list',$brand_list);
            Tpl::output('top_link',$this->sublink($this->links, 'goods_sale'));
			Tpl::setDirquna('shop');/*网 店 运 维shop wwi.com*/
            Tpl::showpage('stat.goodssale');
        }
    }

    /**
     * 输出商品销售明细XML数据
     */
    public function get_goods_xmlOp(){
        if(!$this->search_arr['search_type']){
            $this->search_arr['search_type'] = 'day';
        }
        $search_param = $_REQUEST;
        unset($search_param['act'],$search_param['op']);
        $search_param = http_build_query($search_param);
        $model = Model('stat');
        //获得搜索的开始时间和结束时间
        $searchtime_arr = $model->getStarttimeAndEndtime($this->search_arr);
        //获取相关数据
        $where = array();
        $where['order_isvalid'] = 1;//计入统计的有效订单
        $where['order_add_time'] = array('between',$searchtime_arr);
        //品牌
        $brand_id = intval($_REQUEST['b_id']);
        if ($brand_id > 0){
            $where['brand_id'] = $brand_id;
        }
        //商品分类
        if ($this->choose_gcid > 0){
            //获得分类深度
            $depth = $this->gc_arr[$this->choose_gcid]['depth'];
            $where['gc_parentid_'.$depth] = $this->choose_gcid;
        }
        if(trim($_GET['goods_name'])){
            $where['goods_name'] = array('like','%'.trim($_GET['goods_name']).'%');
        }
        if(trim($_GET['store_name'])){
            $where['store_name'] = array('like','%'.trim($_GET['store_name']).'%');
        }
    	if( isset($_GET['source']) && $_GET['source'] == 'fenxiao' ) {
        	$where['payment_code'] = 'fenxiao' ;
        	!empty($_GET['channel_name'])&&$where['buyer_name']=$_GET['channel_name'];
        } else {
        	$where['_string'] = "payment_code NOT IN ('fenxiao', 'jicai')" ;//过滤分销
        }
        $field = 'goods_id,min(goods_name) as goods_name,min(store_id) as store_id,min(store_name) as store_name,min(goods_commonid) as goods_commonid,SUM(goods_num) as goodsnum,COUNT(DISTINCT order_id) as ordernum,SUM(goods_pay_price) as goodsamount';
        //排序
        $order_type = array('goods_name','store_name','goods_commonid','goods_id','goodsnum','ordernum','goodsamount','refund_num','refund_count','refund_amount');
        $sort_type = array('asc','desc');
        $sortname = trim($this->search_arr['sortname']);
        if (!in_array($sortname,$order_type)){
            $sortname = 'goodsnum';
        }
        $sortorder = trim($this->search_arr['sortorder']);
        if (!in_array($sortorder,$sort_type)){
            $sortorder = 'desc';
        }
        $orderby = $sortname.' '.$sortorder.',goods_id asc';
        $page = intval($_POST['rp']);
        if ($page < 1) {
            $page = 15;
        }
        //查询记录总条数
        $count_arr = $model->getoneByStatordergoods($where, 'COUNT(DISTINCT goods_id) as countnum, SUM(goods_pay_price) as total_goods_amount');
        $countnum = intval($count_arr['countnum']);
        $list = $model->statByStatordergoods($where, $field, array($page,$countnum), 0, $orderby, 'goods_id');

        $where = array();
        $where['seller_state'] = 2;//计入统计的有效订单
        $where['add_time'] = array('between',$searchtime_arr);
        if( isset($_GET['source']) && $_GET['source'] == 'fenxiao' ) {
            $where['refund_way'] = 'fenxiao' ;
        }
        $where['goods_id'] = array('gt',0);
        /** @var refund_returnModel $refundModel */
        $refundModel  = Model('refund_return');
        $field = 'SUM(refund_amount) as refund_amount,COUNT(refund_id) as refund_count,SUM(goods_num) as refund_num,goods_id';
        $refunds = $refundModel->field($field)->where($where)->group('goods_id')->limit(false)->select();
        $refunds = array_under_reset($refunds,'goods_id');

        $statlist = array();
        if (!empty($list) && is_array($list)){
            foreach ($list as $k => $v){
                $out_array = getFlexigridArray(array(),$order_type,$v,$format_array='');
                $out_array['goods_name'] = '<a href="'.urlShop('goods', 'index', array('goods_id' => $v['goods_id'])).'" target="_blank">'.$v['goods_name'].'</a>';
                $out_array['goodsamount'] = ncPriceFormat($v['goodsamount']);
                $out_array['refund_num'] = isset($refunds[$v['goods_id']])?$refunds[$v['goods_id']]['refund_num']:0;
                $out_array['refund_count'] = isset($refunds[$v['goods_id']])?$refunds[$v['goods_id']]['refund_count']:0;
                $out_array['refund_amount'] = isset($refunds[$v['goods_id']])?$refunds[$v['goods_id']]['refund_amount']:0;
                if( isset($_GET['source']) && $_GET['source'] == 'fenxiao'&&!empty($_GET['channel_name']) ) {
                    $search_param.='&buyer_name='.$_GET['channel_name'];
                }else{
                    $search_param.='&buyer_name=';
                }
                //$out_array['goods_id']=$v['goods_id'];
                $out_array['average_price']=bcdiv($v['goodsamount'], $v['ordernum'], 2); //均单价
                $out_array['average_order_num']=bcdiv($v['goodsnum'], $v['ordernum'], 2); //均单量
                //$out_array['total_money'] = $count_arr['total_goods_amount'];
                $out_array['money_point'] = bcdiv($v['goodsamount'] * 100, $count_arr['total_goods_amount'], 2) . '%'; //金额占比

                //$out_array['goods_id']=$v['goods_id'];
                $out_array['district']='<a href="index.php?act=stat_goods&op=area&goods_id='.$v['goods_id'].'&'.$search_param.'">查看</a>';

                $statlist[$v['goods_id']] = $out_array;
            }
        }
        $data = array();
        $data['now_page'] = $model->shownowpage();
        $data['total_num'] = $countnum;
        $data['list'] = $statlist;
        echo Tpl::flexigridXML($data);exit();
    }
    public function areaOp(){
        if(!$this->search_arr['search_type']){
            $this->search_arr['search_type'] = 'day';
        }
        if($_REQUEST['source'] == 'fenxiao') {
            $source = $_REQUEST['source'];
        }
        /** @var statModel $model */
        $model = Model('stat');
        $goods_id=$_GET['goods_id'];
        //获得搜索的开始时间和结束时间
        $searchtime_arr = $model->getStarttimeAndEndtime($this->search_arr);
        Tpl::output('searchtime',implode('|',$searchtime_arr));
        Tpl::output('source',$source);
        Tpl::output('goods_id',$goods_id);
        Tpl::output('buyer_name',$_GET['buyer_name']);
        $active=empty($_GET['source'])?'goods_sale':'goods_sale_fenxiao';
        Tpl::output('top_link',$this->sublink($this->links, $active));
        Tpl::setDirquna('shop');/*网 店 运 维shop wwi.com*/
        Tpl::showpage('stat.goodsarea');
    }
    public function mapOp(){
        $model = Model('stat');
        $where = array();
        $searchtime_arr_tmp = explode('|',$this->search_arr['t']);
        foreach ((array)$searchtime_arr_tmp as $k => $v){
            $searchtime_arr[] = intval($v);
        }
        //获取相关数据
        $where['order_isvalid'] = 1;//计入统计的有效订单
        $where['order_add_time'] = array('between',$searchtime_arr);
        $where['goods_id']=$_GET['goods_id'];
        if( isset($_GET['source']) && $_GET['source'] == 'fenxiao' ) {
            $where['payment_code'] = 'fenxiao' ;
            !empty($_GET['buyer_name'])&&$where['buyer_name']=$_GET['buyer_name'];
        } else {
            $where['_string'] = "payment_code NOT IN ('fenxiao', 'jicai')" ;//过滤分销
        }
        $field='reciver_province_id';
        switch ($_GET['type']){
            case 'goodsamount':
                $field .= ' ,SUM(goods_pay_price) as goodsamount ';
                $orderby = 'goodsamount desc';
                break;
            case 'ordernum':
                $field .= ' ,COUNT(DISTINCT order_id) as ordernum ';
                $orderby = 'ordernum desc';
                break;
            default:
                $_GET['type'] = 'goodsnum';
                $field .= ' ,SUM(goods_num) as goodsnum';
                $orderby = 'goodsnum desc';
                break;
        }
        $orderby .= ',reciver_province_id';
        //查询记录总条数
        $data=$model->statByStatordergoods($where,$field,'','',$orderby,'reciver_province_id');
        $province_array = Model('area')->getTopLevelAreas();
        //地图显示等级数组
        $level_arr = array(array(1,2,3),array(4,5,6),array(7,8,9),array(10,11,12));
        $statlist = array();
        foreach ((array)$data as $k => $v){
            $v['level'] = 4;//排名
            foreach ($level_arr as $lk=>$lv){
                if (in_array($k+1,$lv)){
                    $v['level'] = $lk;//排名
                }
            }
            $province_id = intval($v['reciver_province_id']);
            $statlist[$province_id] = $v;
        }
        $stat_arr = array();
        foreach ((array)$province_array as $k => $v){
            if ($statlist[$k]){
                switch ($_GET['type']){
                    case 'goodsamount':
                        $des = "，下单金额：{$statlist[$k]['goodsamount']}";
                        break;
                    case 'ordernum':
                        $des = "，下单单量：{$statlist[$k]['ordernum']}";
                        break;
                    default:
                        $des = "，下单商品件数：{$statlist[$k]['goodsnum']}";
                        break;
                }
                $stat_arr[] = array('cha'=>$k,'name'=>$v,'des'=>$des,'level'=>$statlist[$k]['level']);
            } else {
                $des = "，无订单数据";
                $stat_arr[] = array('cha'=>$k,'name'=>$v,'des'=>$des,'level'=>4);
            }
        }
        $stat_json = getStatData_Map($stat_arr);
        Tpl::output('stat_field',$_GET['type']);
        Tpl::output('stat_json',$stat_json);
        Tpl::setDirquna('shop');/*网 店 运 维shop wwi.com*/
        Tpl::showpage('stat.map','null_layout');

    }
    public function get_goodsale_xmlOp(){
        $model = Model('stat');
        $where = array();
        $searchtime_arr_tmp = explode('|',$this->search_arr['t']);
        foreach ((array)$searchtime_arr_tmp as $k => $v){
            $searchtime_arr[] = intval($v);
        }
        //获取相关数据
        $where['order_isvalid'] = 1;//计入统计的有效订单
        $where['order_add_time'] = array('between',$searchtime_arr);
        $where['goods_id']=$_GET['goods_id'];
        if( isset($_GET['source']) && $_GET['source'] == 'fenxiao' ) {
            $where['payment_code'] = 'fenxiao' ;
            !empty($_GET['buyer_name'])&&$where['buyer_name']=$_GET['buyer_name'];
        } else {
            $where['_string'] = "payment_code NOT IN ('fenxiao', 'jicai')" ;//过滤分销
        }
        $page = intval($_POST['rp']);
        $field='reciver_province_id,SUM(goods_pay_price) as goodsamount,COUNT(DISTINCT order_id) as ordernum,SUM(goods_num) as goodsnum';
        if ($page < 1) {
            $page = 15;
        }
        //查询记录总条数
        $count_arr = $model->getoneByStatordergoods($where, 'COUNT(DISTINCT reciver_province_id) as countnum');
        $countnum = intval($count_arr['countnum']);
        $list = $model->statByStatordergoods($where, $field, array($page,$countnum), 0, '', 'reciver_province_id');
        $province_array = Model('area')->getTopLevelAreas();
        $statlist = array();
        if (!empty($list) && is_array($list)){
            foreach ($list as $k => $v){
                $province_id = intval($v['reciver_province_id']);
                $out_array = array();
                $out_array['operation'] = '--';
                $out_array['provincename'] = ($t = $province_array[$province_id]) ? $t : '其他';
                $out_array['goodsnum'] = $v['goodsnum'];
                $out_array['ordernum'] = $v['ordernum'];
                $out_array['goodsamount'] = ncPriceFormat($v['goodsamount']);
                $statlist[$v['reciver_province_id']] = $out_array;
            }
        }

        $data = array();
        $data['now_page'] = $model->shownowpage();
        $data['total_num'] = $countnum;
        $data['list'] = $statlist;
        echo Tpl::flexigridXML($data);exit();
    }
    /**
     * 区域分析之详细列表
     */
    public function area_listOp(){
        $model = Model('stat');
        $where = array();
        $searchtime_arr_tmp = explode('|',$this->search_arr['t']);
        foreach ((array)$searchtime_arr_tmp as $k => $v){
            $searchtime_arr[] = intval($v);
        }
        //获取相关数据
        $where['order_isvalid'] = 1;//计入统计的有效订单
        $where['order_add_time'] = array('between',$searchtime_arr);
        $where['goods_id']=$_GET['goods_id'];
        if( isset($_GET['source']) && $_GET['source'] == 'fenxiao' ) {
            $where['payment_code'] = 'fenxiao' ;
            !empty($_GET['buyer_name'])&&$where['buyer_name']=$_GET['buyer_name'];
        } else {
            $where['_string'] = "payment_code NOT IN ('fenxiao', 'jicai')" ;//过滤分销
        }
        $field='reciver_province_id,SUM(goods_pay_price) as goodsamount,COUNT(DISTINCT order_id) as ordernum,SUM(goods_num) as goodsnum';
        $orderby='';
        $count_arr = $model->getoneByStatordergoods($where, 'COUNT(DISTINCT reciver_province_id) as countnum');
        $countnum = intval($count_arr['countnum']);
        if ($this->search_arr['exporttype'] == 'excel'){
            $statlist_tmp = $model->statByStatordergoods($where, $field, 0, 0, $orderby, 'reciver_province_id');
        }
        // 地区
        $province_array = Model('area')->getTopLevelAreas();
        $statheader = array();
        $statheader[] = array('text'=>'省份','key'=>'provincename');
        $statheader[] = array('text'=>'下单商品件数','key'=>'goodsnum','isorder'=>1);
        $statheader[] = array('text'=>'下单单量','key'=>'ordernum','isorder'=>1);
        $statheader[] = array('text'=>'下单金额','key'=>'goodsamount','isorder'=>1);
        $statlist = array();
        foreach ((array)$statlist_tmp as $k => $v){
            $province_id = intval($v['reciver_province_id']);
            $tmp = array();
            $tmp['provincename'] = ($t = $province_array[$province_id]) ? $t : '其他';
            $tmp['goodsnum'] = $v['goodsnum'];
            $tmp['ordernum'] = $v['ordernum'];
            $tmp['goodsamount'] = $v['goodsamount'];
            $statlist[] = $tmp;
        }
        //导出Excel
        if ($this->search_arr['exporttype'] == 'excel'){
            //导出Excel
            import('libraries.excel');
            $excel_obj = new Excel();
            $excel_data = array();
            //设置样式
            $excel_obj->setStyle(array('id'=>'s_title','Font'=>array('FontName'=>'宋体','Size'=>'12','Bold'=>'1')));
            //header
            foreach ($statheader as $k => $v){
                $excel_data[0][] = array('styleid'=>'s_title','data'=>$v['text']);
            }
            //data
            foreach ($statlist as $k => $v){
                foreach ($statheader as $h_k=>$h_v){
                    $excel_data[$k+1][] = array('data'=>$v[$h_v['key']]);
                }
            }
            $excel_data = $excel_obj->charset($excel_data,CHARSET);
            $excel_obj->addArray($excel_data);
            $excel_obj->addWorksheet($excel_obj->charset('区域分析',CHARSET));
            $excel_obj->generateXML($excel_obj->charset('区域分析',CHARSET).date('Y-m-d-H',time()));
            exit();
        }
    }
    /**
     * 分销商品价格区间统计
     */
    public function pricerange_fenxiaoOp(){
    	if(!$this->search_arr['search_type']){
    		$this->search_arr['search_type'] = 'day';
    	}
    	$model = Model('stat');
    	//获得搜索的开始时间和结束时间
    	$searchtime_arr = $model->getStarttimeAndEndtime($this->search_arr);
    	$where = array();
    	$where['order_isvalid'] = 1;//计入统计的有效订单
    	$where['order_add_time'] = array('between',$searchtime_arr);
    	$where['payment_code'] = "fenxiao" ;//过滤分销
    	//商品分类
    	if ($this->choose_gcid > 0){
    		//获得分类深度
    		$depth = $this->gc_arr[$this->choose_gcid]['depth'];
    		$where['gc_parentid_'.$depth] = $this->choose_gcid;
    	}
    	$field = '1';
    	$pricerange_arr = ($t = trim(C('stat_pricerange')))?unserialize($t):'';
    	if ($pricerange_arr){
    		$stat_arr['series'][0]['name'] = '下单量';
    		//设置价格区间最后一项，最后一项只有开始值没有结束值
    		$pricerange_count = count($pricerange_arr);
    		if ($pricerange_arr[$pricerange_count-1]['e']){
    			$pricerange_arr[$pricerange_count]['s'] = $pricerange_arr[$pricerange_count-1]['e'] + 1;
    			$pricerange_arr[$pricerange_count]['e'] = '';
    		}
    		foreach ((array)$pricerange_arr as $k => $v){
    			$v['s'] = intval($v['s']);
    			$v['e'] = intval($v['e']);
    			//构造查询字段
    			if (C('dbdriver') == 'mysql'||C('dbdriver') == 'mysqli') {
    				if ($v['e']){
    					$field .= " ,SUM(IF(goods_pay_price/goods_num > {$v['s']} and goods_pay_price/goods_num <= {$v['e']},goods_num,0)) as goodsnum_{$k}";
    				} else {
    					$field .= " ,SUM(IF(goods_pay_price/goods_num > {$v['s']},goods_num,0)) as goodsnum_{$k}";
    				}
    			} elseif (C('dbdriver') == 'oracle') {
    				if ($v['e']){
    					$field .= " ,SUM((case when goods_pay_price/goods_num > {$v['s']} and goods_pay_price/goods_num <= {$v['e']} then goods_num else 0 end)) as goodsnum_{$k}";
    				} else {
    					$field .= " ,SUM((case when goods_pay_price/goods_num > {$v['s']} then goods_num else 0 end)) as goodsnum_{$k}";
    				}
    			}
    		}
    		$ordergooods_list = $model->getoneByStatordergoods($where, $field);
    		if($ordergooods_list){
    			foreach ((array)$pricerange_arr as $k => $v){
    				//横轴
    				if($v['e']){
    					$stat_arr['xAxis']['categories'][] = $v['s'].'-'.$v['e'];
    				} else {
    					$stat_arr['xAxis']['categories'][] = $v['s'].'以上';
    				}
    				//统计图数据
    				if ($ordergooods_list['goodsnum_'.$k]){
    					$stat_arr['series'][0]['data'][] = intval($ordergooods_list['goodsnum_'.$k]);
    				} else {
    					$stat_arr['series'][0]['data'][] = 0;
    				}
    			}
    		}
    		//得到统计图数据
    		$stat_arr['title'] = '价格销量分布';
    		$stat_arr['legend']['enabled'] = false;
    		$stat_arr['yAxis'] = '销量';
    		$pricerange_statjson = getStatData_LineLabels($stat_arr);
    	} else {
    		$pricerange_statjson = '';
    	}

    	Tpl::output('pricerange_statjson',$pricerange_statjson);
    	Tpl::output('searchtime',implode('|',$searchtime_arr));
    	Tpl::output('top_link',$this->sublink($this->links, 'pricerange_fenxiao'));
    	Tpl::setDirquna('shop');/*网 店 运 维shop wwi.com*/
    	Tpl::showpage('stat.goods.prangefx');
    }
    /**
     * 分销热卖商品
     */
    public function hotgoods_fenxiaoOp(){
    	if(!$this->search_arr['search_type']){
    		$this->search_arr['search_type'] = 'day';
    	}
    	$model = Model('stat');
    	//获得搜索的开始时间和结束时间
    	$searchtime_arr = $model->getStarttimeAndEndtime($this->search_arr);
    	Tpl::output('searchtime',implode('|',$searchtime_arr));
    	Tpl::output('top_link',$this->sublink($this->links, 'hotgoods_fenxiao'));
    	Tpl::setDirquna('shop');/*网 店 运 维shop wwi.com*/
    	Tpl::showpage('stat.goods.hotgoodsfx');
    }
    /**
     * 分销商品销售明细
     */
    public function goods_sale_fenxiaoOp(){
    	if(!$this->search_arr['search_type']){
    		$this->search_arr['search_type'] = 'day';
    	}
        $member_fenxiao = Model('member_fenxiao')->getMemberFenxiao();
    	$model = Model('stat');
    	//获得搜索的开始时间和结束时间
    	$searchtime_arr = $model->getStarttimeAndEndtime($this->search_arr);
    	//获取相关数据
    	$where = array();
    	$where['order_isvalid'] = 1;//计入统计的有效订单
    	$where['order_add_time'] = array('between',$searchtime_arr);
    	//品牌
    	$brand_id = intval($_REQUEST['b_id']);
    	if ($brand_id > 0){
    		$where['brand_id'] = $brand_id;
    	}
    	//商品分类
    	if ($this->choose_gcid > 0){
    		//获得分类深度
    		$depth = $this->gc_arr[$this->choose_gcid]['depth'];
    		$where['gc_parentid_'.$depth] = $this->choose_gcid;
    	}
    	if(trim($_GET['goods_name'])){
    		$where['goods_name'] = array('like','%'.trim($_GET['goods_name']).'%');
    	}
    	if(trim($_GET['store_name'])){
    		$where['store_name'] = array('like','%'.trim($_GET['store_name']).'%');
    	}
    	if (!empty($_GET['id']) && is_array($_GET['id'])){
    		$where['goods_id'] = array('in',$_GET['id']);
    	}
    	$where['payment_code'] = "fenxiao" ;//过滤分销
        !empty($_GET['channel_name'])&&$where['buyer_name']=$_GET['channel_name'];
    	$field = 'goods_id,min(goods_name) as goods_name,min(store_id) as store_id,min(store_name) as store_name,min(goods_commonid) as goods_commonid,SUM(goods_num) as goodsnum,COUNT(DISTINCT order_id) as ordernum,SUM(goods_pay_price) as goodsamount';
    	$orderby = 'goodsnum desc,goods_id asc';
    	//导出Excel
    	if ($_GET['exporttype'] == 'excel'){
    		$goods_list = $model->statByStatordergoods($where, $field, 0, 0, $orderby, 'goods_id');
            $count_arr = $model->getoneByStatordergoods($where, 'SUM(goods_pay_price) as total_goods_amount');
    		import('libraries.excel');
    		$excel_obj = new Excel();
    		$excel_data = array();
    		//设置样式
    		$excel_obj->setStyle(array('id'=>'s_title','Font'=>array('FontName'=>'宋体','Size'=>'12','Bold'=>'1')));
    		//header
    		$excel_data[0][] = array('styleid'=>'s_title','data'=>'商品名称');
    		$excel_data[0][] = array('styleid'=>'s_title','data'=>'SPU');
    		$excel_data[0][] = array('styleid'=>'s_title','data'=>'SKU');
    		$excel_data[0][] = array('styleid'=>'s_title','data'=>'店铺名称');
    		$excel_data[0][] = array('styleid'=>'s_title','data'=>'下单商品件数');
    		$excel_data[0][] = array('styleid'=>'s_title','data'=>'下单单量');
    		$excel_data[0][] = array('styleid'=>'s_title','data'=>'下单金额');
    		$excel_data[0][] = array('styleid'=>'s_title','data'=>'均单价');
    		$excel_data[0][] = array('styleid'=>'s_title','data'=>'均单量');
    		$excel_data[0][] = array('styleid'=>'s_title','data'=>'占比');
    		//data
    		foreach ($goods_list as $k => $v){
    			$excel_data[$k+1][] = array('data'=>$v['goods_name']);
    			$excel_data[$k+1][] = array('data'=>$v['goods_commonid']);
                $excel_data[$k+1][] = array('data'=>$v['goods_id']);
    			$excel_data[$k+1][] = array('data'=>$v['store_name']);
    			$excel_data[$k+1][] = array('data'=>$v['goodsnum']);
    			$excel_data[$k+1][] = array('data'=>$v['ordernum']);
    			$excel_data[$k+1][] = array('data'=>$v['goodsamount']);
                $excel_data[$k+1][] = array('data'=>number_format($v['goodsamount']/$v['ordernum'], 2));
    			$excel_data[$k+1][] = array('data'=>number_format($v['goodsnum']/$v['ordernum'], 2));
    			$excel_data[$k+1][] = array('data'=>number_format($v['goodsamount'] * 100/$count_arr['total_goods_amount'],2) . '%');
    		}
    		$excel_data = $excel_obj->charset($excel_data,CHARSET);
    		$excel_obj->addArray($excel_data);
    		$excel_obj->addWorksheet($excel_obj->charset('商品销售明细',CHARSET));
    		$excel_obj->generateXML($excel_obj->charset('商品销售明细',CHARSET).date('Y-m-d-H',time()));
    		exit();
    	} else {
    		//查询品牌
    		$brand_list = Model('brand')->getBrandList(array('brand_apply'=>1));
    		Tpl::output('brand_list',$brand_list);
    		Tpl::output('member_fenxiao',$member_fenxiao);
    		Tpl::output('top_link',$this->sublink($this->links, 'goods_sale_fenxiao'));
    		Tpl::setDirquna('shop');/*网 店 运 维shop wwi.com*/
    		Tpl::showpage('stat.goodssalefx');
    	}
    }
    public function goods_refundOp(){
        $member_fenxiao = Model('member_fenxiao')->getMemberFenxiao();
        Tpl::output('member_fenxiao',$member_fenxiao);
        Tpl::output('top_link',$this->sublink($this->links, 'goods_refund'));
        Tpl::setDirquna('shop');/*网 店 运 维shop wwi.com*/
        Tpl::showpage('stat.goods.refund');
    }
    public function get_xml_refundOp(){
        if(!$this->search_arr['search_type']){
            $this->search_arr['search_type'] = 'day';
        }
        $member_fenxiao = Model('member_fenxiao')->getMemberFenxiao();
        $model = Model('stat');
        //获得搜索的开始时间和结束时间
        $searchtime_arr = $model->getStarttimeAndEndtime($this->search_arr);
        //获取相关数据
        $where = array();
        $order="total desc";
        $sortname = trim($this->search_arr['sortname']);
        $sortorder = trim($this->search_arr['sortorder']);
        $order = $sortname.' '.$sortorder;
        if(trim($_GET['buyer_name'])){
            $where['buyer_name']=$_GET['buyer_name'];
        }
        if(trim($_GET['goods_name'])){
            $where['goods_name']=array('like','%'.trim($_GET['goods_name']).'%');
        }
        $limit = intval($_POST['rp']);
        if ($limit < 1) {
            $limit = 15;
        }
        //退款单已经完成且卖家同意即为有效退款单
        $where['refund_state'] = 3;
        $where['seller_state']=3;
        $where['refund_type']=1;
        $where['add_time'] = array('between',$searchtime_arr);
        $data=array();
        $refund_model=Model('refund_return');
        $field="goods_name,goods_id,count(goods_id) as total,SUM(goods_num) as num,SUM(refund_amount) as amount";
        if($_GET['exporttype']=='excel'){
            $list=$refund_model->getRefundListByCondition($where,'',$field,'',$order,'goods_id');
            import('libraries.excel');
            $excel_obj = new Excel();
            $excel_data = array();
            //设置样式
            $excel_obj->setStyle(array('id'=>'s_title','Font'=>array('FontName'=>'宋体','Size'=>'12','Bold'=>'1')));
            //header
            $excel_data[0][] = array('styleid'=>'s_title','data'=>'商品名称');
            $excel_data[0][] = array('styleid'=>'s_title','data'=>'商品SKU');
            $excel_data[0][] = array('styleid'=>'s_title','data'=>'退款单量');
            $excel_data[0][] = array('styleid'=>'s_title','data'=>'退款商品数量');
            $excel_data[0][] = array('styleid'=>'s_title','data'=>'退款金额');
            //data
            foreach ($list as $k => $v){
                $excel_data[$k+1][] = array('data'=>$v['goods_name']);
                $excel_data[$k+1][] = array('data'=>$v['goods_id']);
                $excel_data[$k+1][] = array('data'=>$v['total']);
                $excel_data[$k+1][] = array('data'=>$v['num']);
                $excel_data[$k+1][] = array('data'=>$v['amount']);
            }
            $excel_data = $excel_obj->charset($excel_data,CHARSET);
            $excel_obj->addArray($excel_data);
            $excel_obj->addWorksheet($excel_obj->charset('商品商品退款统计',CHARSET));
            $excel_obj->generateXML($excel_obj->charset('商品商品退款统计',CHARSET).date('Y-m-d-H-i',time()));
            exit();
        }
        $list=$refund_model->getRefundListByCondition($where,$limit,$field,"",$order,'goods_id');
        $count=$refund_model->getRefundListByCondition($where,"",$field,"",'','goods_id');
        $data['now_page'] = $refund_model->shownowpage();
        $data['total_num'] = count($count);
        $data['list']=$list;
        echo Tpl::flexigridXML($data);exit();
    }
}
