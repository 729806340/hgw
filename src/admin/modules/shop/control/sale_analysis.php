<?php
/**
 * Created by CharlesChen
 * Date: 2018/2/23
 * Time: 17:27
 * File name:sale_analysis.php
 */
defined('ByShopWWI') or exit('Access Invalid!');
class sale_analysisControl extends SystemControl
{
    private $search_arr;

    private static $prod_conf_list = array(
        '拼多多' => array(
            'prod_rule' => '/<div class=\"g-name\" data-reactid=\"\d+\"><span data-reactid=\"\d+"><!-- react-text: \d+ -->([^<>]+)<!-- \/react-text --><\/span>/',
            'prize_rule' => '/<span class=\"g-group-price\" data-reactid=\"\d+\"><i data-reactid=\"\d+\">￥<\/i><!-- react-text: \d+ -->([^<>]+)<!-- \/react-text --><\/span>/',
            'sales_rule' => '/<span class=\"g-sales\" data-reactid=\"\d+\"><!-- react-text: \d+ -->[^<>]+<!-- \/react-text --><!-- react-text: \d+ -->([^<>]+)<!-- \/react-text --><!-- react-text: \d+ -->[^<>]+<!-- \/react-text -->/',
        ),
        '云联美购' => array(
            'prod_rule' => '/<h3 class=\"title_h3\">([^<>]+)<\/h3>/',
            'prize_rule' => '/<strong> ￥<i class=\"good-price\">([^<>]+)<\/i> <\/strong>/',
            'sales_rule' => '/<span class=\"cumulative\">销量：([^<>]+)<\/span>/',
        ),
        '楚楚街' => array(
            'prod_rule' => 'api',
            'prize_rule' => 'api',
            'sales_rule' => 'api',
        ),
        '萌店' => array(
            'prod_rule' => 'api',
            'prize_rule' => 'api',
            'sales_rule' => 'api',
        ),
        '会过' => array(
            'prod_rule' => 'api',
            'prize_rule' => 'api',
            'sales_rule' => 'api',
        ),
        '返利' =>array(
            'prod_rule' => '/<h2 class=\"detail-title\">([^<>]+)<\/h2>/',
            'prize_rule' => '/<span class=\"price-now\"><span class=\"rmb-icon\">[^<>]+<\/span>(\d+\.\d+)<\/span>/',
            'sales_rule' => '/<div class=\"tuan-join\">[^<>]+<span>(\d+)[^<>]+<\/span>([^<>]+)<\/div>/',
        ),

    );

    public function __construct()
    {
        parent::__construct();
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

    public function indexOp(){
        Tpl::setDirquna('shop');
        Tpl::showpage('jingjia.index');
    }
    public function prodConfOp(){
//        Tpl::output('prod_conf',$prod_conf);
        Tpl::setDirquna('shop');
        Tpl::showpage('prod_conf.index');
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
    public function getXmlOp(){
        $condition = array();
        $model = Model('stat_order');
        if(!$this->search_arr['search_type']){
            $this->search_arr['search_type'] = 'day';
        }
        $searchtime_arr = Model('stat')->getStarttimeAndEndtime($this->search_arr);
        $stime = $searchtime_arr[0];
        $etime = $searchtime_arr[1];
        $condition['fetch_time']=array('between',array($stime,$etime));
        if(!empty($_POST['qtype'])&&!empty($_POST['query'])){
            $condit[$_POST['qtype']]=array('like','%' .$_POST['query'].'%' );
            $d=Model('prod_conf')->where($condit)->select();
            $id_list = array();
            array_walk($d, function ($value, $key) use (&$id_list) {
                $id_list [] = $value['id'];
            });
            !empty($id_list)&&$condition['prod_id']=array('in',implode(',',$id_list));
        }
        $model=Model('jingjia');
        $jingjia=$model->getList($condition,'*',$_POST['curpage'],'fetch_time desc');
        //v($jingjia);
        $data=array();
        $data['now_page']=$_POST['curpage'];
        $data['total_num']=count($model->getList($condition,'*',$_POST['curpage'],'fetch_time desc',1));
        foreach($jingjia as $k=>$consult_info){
            $list = array();$operation_detail = '';
            $conf=Model('prod_conf')->getInfo(array('id'=>$consult_info['prod_id']));
            $list['fetch_time'] = date('Y-m-d',$consult_info['fetch_time']);
            $list['prod_name'] = "<span title='{$conf['prod_name']}'>{$conf['prod_name']}</span>";
            $list['prize'] = $consult_info['prize'];
            $list['sales'] = $consult_info['sales_count'];
            $list['total_sales'] = $consult_info['sales'];
            $list['prod_from']=$conf['prod_from'];
            $list['store_name'] = "<span title='{$conf['store_name']}'>{$conf['store_name']}</span>";
            $list['name'] = "<span title='{$consult_info['name']}'>{$consult_info['name']}</span>";

//            if(!empty($lastest)&&$this->search_arr['search_type']=='day'){
//                $lastest=$model->where(array('prod_id'=>$consult_info['prod_id'],'fetch_time'=>array('lt',$consult_info['fetch_time'])))->order('fetch_time desc')->find();
//                $list['sales'] = $consult_info['sales']-$lastest['sales'];
//            }else {

//            }
            $data['list'][$consult_info['id']] = $list;
        }
        exit(Tpl::flexigridXML($data));
    }
    public function getXmlConfOp(){
        $condition = array();
        if(!empty($_POST['qtype'])&&!empty($_POST['query'])){
            $condition[$_POST['qtype']]=array('like','%' .$_POST['query'].'%' );
        }
        $model=Model('prod_conf');
        $prod_conf=$model->getList($condition,'*',$_POST['rp'],'id desc');
        $data=array();
        $data['now_page']=$model->shownowpage();
        $data['total_num']=$model->gettotalnum();
        foreach($prod_conf as $consult_info){
            $list = array();$operation_detail = '';
            $list['operation'] = "<a class='btn red' onclick=\"fg_delete({$consult_info['id']})\"><i class=\"fa fa-trash-o\"></i>删除</a>";
            $list['operation'] .= "<a class=\"btn green\" href=\"index.php?act=sale_analysis&op=editItem&id={$consult_info['id']}\"><i class=\"fa fa-list-alt\"></i>编辑</a>";
            $list['created_at']=date('Y-m-d H:i',$consult_info['created_at']);
            $list['prod_name'] = "<span title='{$consult_info['prod_name']}'>{$consult_info['prod_name']}</span>";
            $list['store_name'] = "<span title='{$consult_info['store_name']}'>{$consult_info['store_name']}</span>";
            $list['prod_from'] = $consult_info['prod_from'];
            $list['prod_url'] = "<span title='{$consult_info['prod_url']}'>{$consult_info['prod_url']}</span>";
            $list['prod_rule'] = "<span title='{$consult_info['prod_rule']}'>{$consult_info['prod_rule']}</span>";
            $list['prize_rule'] = "<span title='{$consult_info['prize_rule']}'>{$consult_info['prize_rule']}</span>";
            $list['sales_rule'] = "<span title='{$consult_info['sales_rule']}'>{$consult_info['sales_rule']}</span>";
            $list['status'] =$consult_info['status']?"开启":"关闭";
            $data['list'][$consult_info['id']] = $list;
        }
        exit(Tpl::flexigridXML($data));
    }
    public function addItemOp(){
        Tpl::setDirquna('shop');
        Tpl::showpage('prod_conf.add');
    }
    public function addOp(){
        $post_data=$_POST;
        $prod_name=trim($post_data['prod_name']);
        $store_name=trim($post_data['store_name']);
        $prod_url=trim($post_data['prod_url']);
        $prod_rule=trim($post_data['prod_rule']);
        $prize_rule=trim($post_data['prize_rule']);
        $sales_rule=trim($post_data['sales_rule']);
        $prod_from=trim($post_data['prod_from']);
        empty($prod_name)&&showMessage("商品名称不能为空");
        empty($prod_url)&&showMessage("商品链接不能为空");
        empty($prod_from)&&showMessage("来源不能为空");

        if (in_array($prod_from, array_keys(self::$prod_conf_list))) {//默认使用现有规则
            $prod_conf = self::$prod_conf_list;
            $prod_rule = $post_data['prod_rule'] = addslashes(htmlspecialchars($prod_conf[$prod_from]['prod_rule']));
            $prize_rule = $post_data['prize_rule'] = addslashes(htmlspecialchars($prod_conf[$prod_from]['prize_rule']));
            $sales_rule = $post_data['sales_rule'] = addslashes(htmlspecialchars($prod_conf[$prod_from]['sales_rule']));
        }

        $model=Model('prod_conf');
        if(!empty($prod_name)&&!empty($prod_url)&&!empty($prod_from)&&!empty($prize_rule)&&!empty($sales_rule)&&!empty($prod_rule)){
                $status=$model->addItem($post_data);
                if($status) {
                    redirect('index.php?act=sale_analysis&op=prodConf');
                }else{
                    showMessage('提交失败，稍后重试');
                }
        }
        if(!empty($prod_name)&&!empty($prod_url)&&!empty($prod_from)&&empty($prize_rule)&&empty($sales_rule)&&empty($prod_rule)){
            $check=$model->check($prod_from);

            if(empty($check)){
                showMessage('数据库中未记录\"'.$prod_from."\"相关配置，请填写价格正则,销量正则,商品名正则");
            }else{
                $post_data['prod_rule']=addslashes($check['prod_rule']);
                $post_data['prize_rule']=addslashes($check['prize_rule']);
                $post_data['sales_rule']=addslashes($check['sales_rule']);
                $status=$model->addItem($post_data);
                if($status) {
                    redirect('index.php?act=sale_analysis&op=prodConf');
                }else{
                    showMessage('提交失败，稍后重试');
                }
            }
        }

    }
    public function editItemOp(){
        $data=model('prod_conf')->getInfo(array('id'=>$_GET['id']));
        Tpl::output('data',$data);
        Tpl::setDirquna('shop');
        Tpl::showpage('prod_conf.edit');
    }
    public function editOp(){
        $post_data=$_POST;
        $prod_name=trim($post_data['prod_name']);
        $store_name=trim($post_data['store_name']);
        $prod_url=trim($post_data['prod_url']);
        $prod_rule=trim($post_data['prod_rule']);
        $prize_rule=trim($post_data['prize_rule']);
        $sales_rule=trim($post_data['sales_rule']);
        $prod_from=trim($post_data['prod_from']);
        $id=intval($post_data['id']);
        unset($post_data['id']);
        empty($prod_name)&&showMessage("商品规则不能为空");
        empty($prod_url)&&showMessage("商品链接不能为空");
        empty($prod_from)&&showMessage("来源不能为空");
        $model=Model('prod_conf');
        if(!empty($prod_name)&&!empty($prod_url)&&!empty($prod_from)&&!empty($prize_rule)&&!empty($sales_rule)&&!empty($prod_rule)){
            $status=$model->editItem(array('id'=>$id),$post_data);
            if($status) {
                redirect('index.php?act=sale_analysis&op=editItem&id='.$id);
            }else{
                showMessage('提交失败，稍后重试');
            }
        }
        if(!empty($prod_name)&&!empty($prod_url)&&!empty($prod_from)&&empty($prize_rule)&&empty($sales_rule)&&empty($prod_rule)){
            $check=$model->check($prod_from);
            if(empty($check)){
                showMessage('数据库中未记录\"'.$prod_from."\"相关配置，请填写价格正则,销量正则,商品名正则");
            }else{
                $post_data['prod_rule']=$check['prod_rule'];
                $post_data['prize_rule']=$check['prize_rule'];
                $post_data['sales_rule']=$check['sales_rule'];
                $status=$model->editItem(array('id'=>$id),$post_data);
                if($status) {
                    redirect('index.php?act=sale_analysis&op=editItem&id='.$id);
                }else{
                    showMessage('提交失败，稍后重试');
                }
            }
        }
    }
    public function delItemOp(){
        $ids=$_GET['id'];
        $model=Model('prod_conf');
        $condition=array();
        if (preg_match('/^[\d,]+$/', $_GET['id'])) {
            $_GET['id'] = explode(',',trim($_GET['id'],','));
            $condition['id'] = array('in',$_GET['id']);
        }
        $status=$model->delItem($condition);
        if($status) {
            redirect('index.php?act=sale_analysis&op=prodConf');
        }else{
            showMessage('删除失败，稍后重试');
        }
    }
    public function exportOp(){
        $list=array();
        $condition = array();
        $model = Model('stat_order');
        if(!$this->search_arr['search_type']){
            $this->search_arr['search_type'] = 'day';
        }
        $searchtime_arr = Model('stat')->getStarttimeAndEndtime($this->search_arr);
        $stime = $searchtime_arr[0];
        $etime = $searchtime_arr[1];
        $condition['fetch_time']=array('between',array($stime,$etime));
        if(!empty($_POST['qtype'])&&!empty($_POST['query'])){
            $condit[$_POST['qtype']]=array('like','%' .$_POST['query'].'%' );
            $d=Model('prod_conf')->where($condit)->select();
            $id_list = array();
            array_walk($d, function ($value, $key) use (&$id_list) {
                $id_list [] = $value['id'];
            });
            !empty($id_list)&&$condition['prod_id']=array('in',implode(',',$id_list));
        }
        /** @var jingjiaModel $jingjia_model */
        $jingjia_model=Model('jingjia');
        $jingjia_data=$jingjia_model->getList($condition,'*','','fetch_time desc',1);
        foreach ($jingjia_data as $k=>$item) {
            $conf=Model('prod_conf')->getInfo(array('id'=>$item['prod_id']));
            $list[$k]['prod_name'] = $conf['prod_name'];
            $list[$k]['store_name'] = $conf['store_name'];
            $list[$k]['prod_url']=html_entity_decode($conf['prod_url']);
            $list[$k]['name'] = $item['name'];
            $list[$k]['prize'] = $item['prize'];
//            $lastest=$jingjia_model->where(array('prod_id'=>$item['prod_id'],'fetch_time'=>array('lt',$item['fetch_time'])))->order('fetch_time desc')->find();
//            if(!empty($lastest)){
//                $list[$k]['sales'] = $item['sales']-$lastest['sales'];
//            }else {
                $list[$k]['sales'] = $item['sales_count'];
//            }
            $list[$k]['total_sales'] = $item['sales'];
            $list[$k]['prod_from']=$conf['prod_from'];
            $list[$k]['created_at']=date('Y-m-d',$conf['created_at']);
            $list[$k]['fetch_time'] = date('Y-m-d',$item['fetch_time']);
            $list[$k]['msg'] = $item['msg'];
        }
        $header = array(
            'prod_name' => '商品名称',
            'store_name' => '店铺名',
            'prod_url'=>'商品链接',
            'name' => '第三方商品名',
            'prize' => '价格',
            'sales' => '销量',
            'total_sales' => '抓取时的累计量',
            'prod_from'=>'来源',
            'created_at'=>'开始抓取时间',
            'fetch_time' => '抓取时间',
            'msg' => '备注'
        );
        array_unshift($list, $header);
        $csv = new Csv();
        $export_data = $csv->charset($list,CHARSET,'gbk');
        $csv->filename = $csv->charset('竞价分析',CHARSET) . '-'.date('Y-m-d',$stime).'~'.date('Y-m-d',$etime);
        $csv->export($export_data);
    }

    //店铺销售数据列表
    public function shopListOp() {
        Tpl::setDirquna('shop');
        Tpl::showpage('prod_shop.list');
    }

    public function getShopXmlOp() {
        $condition = array(
            'type' => 2,
        );
        if(!$this->search_arr['search_type']){
            $this->search_arr['search_type'] = 'day';
        }
        $searchtime_arr = $this->getLastStartandEndtime_new($this->search_arr);

        $stime = $searchtime_arr[0];
        $etime = $searchtime_arr[1];
        $etime = $etime + 4800;
        $condition['fetch_time']=array('between',array($stime,$etime));
        if(!empty($_POST['qtype'])&&!empty($_POST['query'])){
            $condition[$_POST['qtype']]=array('like','%' .$_POST['query'].'%' );
        }

        $model=Model('jingjia_shop');
        $shop_data_list = $model->where($condition)->field('*,(max(total_sales)-min(total_sales)) as sales, max(total_sales) as now_sales')->order('fetch_time desc')->group('father_id')->page($_POST['rp'])->select();

        $data=array();
        $data['now_page']=$model->shownowpage();
        $total=$model->where($condition)->field('COUNT(DISTINCT father_id) AS num_count')->find();
        $data['total_num'] = $total['num_count'];
        foreach($shop_data_list as $k=>$consult_info){
            $list = array();
            $list['fetch_time'] = date('Y-m-d',$consult_info['fetch_time']);
            $list['prod_name'] = "<span title='{$consult_info['shop_name']}'>{$consult_info['shop_name']}</span>";
            $list['sales'] = $consult_info['sales'];
            $list['total_sales'] = $consult_info['now_sales'];
            $list['prod_from'] = $consult_info['channel_name'];
            $data['list'][$consult_info['id']] = $list;
        }
        exit(Tpl::flexigridXML($data));
    }

    public function exportShopOp(){
        $list=array();
        $condition = array(
            'type' => 2,
        );

        if(!$this->search_arr['search_type']){
            $this->search_arr['search_type'] = 'day';
        }
        $searchtime_arr = $this->getLastStartandEndtime_new($this->search_arr);
        $stime = $searchtime_arr[0];
        $etime = $searchtime_arr[1];
        $etime = $etime + 4800;
        $condition['fetch_time']=array('between',array($stime,$etime));
        if(!empty($_POST['qtype'])&&!empty($_POST['query'])){
            $condition[$_POST['qtype']]=array('like','%' .$_POST['query'].'%' );
        }
        $model=Model('jingjia_shop');
        $shop_data_list = $model->where($condition)->field('*,(max(total_sales)-min(total_sales)) as sales, max(total_sales) as now_sales')->order('fetch_time desc')->group('father_id')->page(false)->select();
        foreach ($shop_data_list as $k=>$item) {
            $list[$k]['store_name'] = $item['shop_name'];
            $list[$k]['sales'] = $item['sales'];
            $list[$k]['total_sales'] = $item['now_sales'];
            $list[$k]['prod_from']=$item['channel_name'];
            $list[$k]['fetch_time'] = date('Y-m-d',$item['fetch_time']);
        }
        $header = array(
            'shop_name' => '店铺名',
            'sales' => '销量',
            'total_sales' => '抓取时的累计量',
            'prod_from'=>'来源',
            'fetch_time' => '抓取时间',
        );
        array_unshift($list, $header);
        $csv = new Csv();
        $export_data = $csv->charset($list,CHARSET,'gbk');
        $csv->filename = $csv->charset('店铺数据分析',CHARSET) . '-'.date('Y-m-d',$stime).'~'.date('Y-m-d',$etime);
        $csv->export($export_data);
    }


    //店铺配置列表
    public function marketListOp() {
        Tpl::setDirquna('shop');
        Tpl::showpage('prod_market.list');
    }

    //添加店铺配置
    public function addShopConfOp() {
        Tpl::setDirquna('shop');
        Tpl::showpage('prod_conf_shop.add');
    }

    //保存店铺配置
    public function addMarketOp() {

        $shop_name=trim($_POST['shop_name']);
        $shop_url=trim($_POST['shop_url']);
        $channel_name=trim($_POST['channel_name']);
        $status=intval($_POST['status']);

        empty($shop_name)&&showMessage("店铺名称不能为空");
        empty($shop_url)&&showMessage("店铺链接不能为空");
        empty($channel_name)&&showMessage("非法操作");

        $insert_data = array(
            'shop_name' => $shop_name,
            'channel_name' => $channel_name,
            'shop_url' => $shop_url,
            'type' => 1,
            'status' => $status,
            'fetch_time' => time(),
        );
        $model=Model('jingjia_shop');
        $status=$model->insert($insert_data);
        if($status) {
            redirect('index.php?act=sale_analysis&op=marketList');
        }else{
            showMessage('提交失败，稍后重试', 'index.php?act=sale_analysis&op=marketList');
        }

    }

    public function getXmlShopConfOp() {
        $condition = array(
            'type' => 1,
            'status' => array('gt', 0),
        );
        if(!empty($_POST['qtype'])&&!empty($_POST['query'])){
            $condition[$_POST['qtype']]=array('like','%' .$_POST['query'].'%' );
        }

        $model=Model('jingjia_shop');
        $conf_list = $model->where($condition)->field('*')->order('id desc')->page($_POST['rp'])->select();
        $data=array();
        $data['now_page']=$model->shownowpage();
        $data['total_num']=$model->gettotalnum();
        foreach($conf_list as $consult_info){
            $list = array();
            $list['operation'] = "<a class='btn red' onclick=\"fg_delete({$consult_info['id']})\"><i class=\"fa fa-trash-o\"></i>删除</a>";
            $list['operation'] .= "<a class=\"btn green\" href=\"index.php?act=sale_analysis&op=editShopConf&id={$consult_info['id']}\"><i class=\"fa fa-list-alt\"></i>编辑</a>";
            $list['created_at']=date('Y-m-d H:i',$consult_info['fetch_time']);
            $list['shop_name'] = "<span title='{$consult_info['shop_name']}'>{$consult_info['shop_name']}</span>";
            $list['channel_name'] = "<span title='{$consult_info['channel_name']}'>{$consult_info['channel_name']}</span>";
            $list['shop_url'] = $consult_info['shop_url'];
            $list['status'] =$consult_info['status'] == 2 ?"开启":"关闭";
            $data['list'][$consult_info['id']] = $list;
        }

        exit(Tpl::flexigridXML($data));
    }

    //删除店铺配置
    public function delShopConfOp() {
        $model=Model('jingjia_shop');
        $condition=array();
        if (preg_match('/^[\d,]+$/', $_GET['id'])) {
            $_GET['id'] = explode(',',trim($_GET['id'],','));
            $condition['id'] = array('in',$_GET['id']);
        }

        if (empty($condition)) {
            showMessage('非法操作');
        }
        $status=$model->where($condition)->update(array('status' => 0));
        if($status) {
            redirect('index.php?act=sale_analysis&op=MarketList');
        }else{
            showMessage('删除失败，稍后重试');
        }
    }

    //编辑店铺配置
    public function editShopConfOp() {
        $data=model('jingjia_shop')->where(array('id'=>$_GET['id']))->find();
        Tpl::output('data',$data);
        Tpl::setDirquna('shop');
        Tpl::showpage('prod_conf_shop.edit');
    }

    //更新店铺配置
    public function saveShopConfOp() {
        $shop_name=trim($_POST['shop_name']);
        $shop_url=trim($_POST['shop_url']);
        $channel_name=trim($_POST['channel_name']);
        $status=intval($_POST['status']);
        $id=intval($_POST['id']);

        empty($shop_name)&&showMessage("店铺名称不能为空");
        empty($shop_url)&&showMessage("店铺链接不能为空");
        empty($channel_name)&&showMessage("非法操作");
        empty($id)&&showMessage("非法操作");

        $update_data = array(
            'shop_name' => $shop_name,
            'channel_name' => $channel_name,
            'shop_url' => $shop_url,
            'status' => $status,
        );
        $model=Model('jingjia_shop');

        $status=$model->where(array('id' => $id))->update($update_data);
        if($status) {
            redirect('index.php?act=sale_analysis&op=marketList');
        }else{
            showMessage('提交失败，稍后重试', 'index.php?act=sale_analysis&op=marketList');
        }
    }

    /**
     * 获得查询上周或者上个月的时间
     */
    public function getLastStartandEndtime_new($search_arr){
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

    public function oldListOp() {
        Tpl::setDirquna('shop');
        Tpl::showpage('prod_old.list');
    }

    public function getOldXmlOp() {

        $condition = array();
        if(!empty($_POST['qtype'])&&!empty($_POST['query'])){
            $condition[$_POST['qtype']]=array('like','%' .$_POST['query'].'%' );
        }
        $data=array();
        if (empty($condition)) {
            exit(Tpl::flexigridXML($data));
        }

        $model=Model('jingjia');
        $data_list = $model->where($condition)->field('*')->order('fetch_time desc')->page($_POST['rp'])->select();

        $prod_ids = array_column($data_list, 'prod_id');
        $prod_id = array_unique($prod_ids);

        if (empty($prod_id)) {
            $prod_condition = array();
        } else {
            $prod_condition = array('id' => array('in', $prod_id));
        }

        /** @var prod_confModel $prod_model */
        $prod_model = Model('prod_conf');
        $prod_list_data = $prod_model->getList($prod_condition, '*', false);
        $prod_list_store = array_column($prod_list_data, 'store_name', 'id');
        $prod_list_from = array_column($prod_list_data, 'prod_from', 'id');

        $data['now_page']=$model->shownowpage();
        $data['total_num']=$model->gettotalnum();
        foreach($data_list as $k=>$value){
            $list = array();
            $list['fetch_time'] = date('Y-m-d',$value['fetch_time']);
            $list['goods_name'] = $value['name'];
            $list['store_name'] = $prod_list_store[$value['prod_id']];
            $list['sales'] = $value['sales'];
            $list['prod_from'] = $prod_list_from[$value['prod_id']];
            $data['list'][$value['id']] = $list;
        }
        exit(Tpl::flexigridXML($data));

    }

    public function exportOldOp() {

        $condition = array();
        $list = array();
        if(!empty($_GET['qtype'])&&!empty($_GET['query'])){
            $condition[$_GET['qtype']]=array('like','%' .$_GET['query'].'%' );
        }

        if (!empty($condition)) {

            /** @var prod_confModel $prod_model */
            $prod_model = Model('prod_conf');
            $prod_list_data = $prod_model->where(array())->field('*')->limit(false)->select();
            $prod_list_store = array_column($prod_list_data, 'store_name', 'id');
            $prod_list_from = array_column($prod_list_data, 'prod_from', 'id');

            $model=Model('jingjia');
            $data_list = $model->where($condition)->field('*')->order('fetch_time desc')->limit(false)->select();
            foreach ($data_list as $k=>$item) {
                $list[$k]['fetch_time'] = date('Y-m-d',$item['fetch_time']);
                $list[$k]['goods_name'] = $item['name'];
                $list[$k]['store_name'] = $prod_list_store[$item['prod_id']];
                $list[$k]['sales']=$item['sales'];
                $list[$k]['prod_from'] = $prod_list_from[$item['prod_id']];
            }
        }

        $header = array(
            'fetch_time' => '抓取时间',
            'goods_name' => '商品名',
            'store_name' => '店铺名',
            'sales'=>'抓取时的累计量',
            'prod_from' => '来源',
        );
        array_unshift($list, $header);
        $csv = new Csv();
        $export_data = $csv->charset($list,CHARSET,'gbk');
        $csv->filename = $csv->charset('历史数据',CHARSET) . '-'.date('Y-m-d',time());
        $csv->export($export_data);
    }

}
