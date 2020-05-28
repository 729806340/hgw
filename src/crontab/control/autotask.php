<?php
/**
 * 分销相关计划任务脚本
 * zxj
 */
defined('ByShopWWI') or exit('Access Invalid!');
class autotaskControl extends BaseCronControl {
    public function __construct()

    {
        parent::__construct();

        $get = isset($_SERVER['argv'][3])?$_SERVER['argv'][3]:'source=pinduoduo';
        $source="";
        parse_str($get);
        $_GET['source']=$source;

    }
    
    /**
     * 获取订单列表，并保存
     */
    public function orderlistOp(){
    	set_time_limit(0);
    	$fenxiao_service = Service("Fenxiao") ;
    	$fenxiao_service -> init($_GET['source']) ;
		$_GET['istest']=1;

        $params = array(
        	'begin'	=> $_GET['begin'] ? $_GET['begin'] : date('Y-m-d H:i:s', time()-2*3600 ),
        	'end' => $_GET['end'] ? $_GET['end'] : date('Y-m-d H:i:s', time() )
        ) ;
        $fenxiao_service -> orderlist( $params ) ;
    }
    
    
    /**
     * 获取汉购网未完成的分销订单，验证分销平台是否已完成，如已完成，这同步完成状态
     */
    public function finishordersOp(){
        set_time_limit(0);
        $source = strip_tags($_REQUEST['source']);
        $page = empty($_REQUEST['page']) ? 1 : strip_tags($_REQUEST['page']);
        /** @var FenxiaoService $fenxiao_service */
        $fenxiao_service = Service("Fenxiao") ;
        $fenxiao_service -> init($source) ;
        
        $model_order = Model('order');
        $logic_order = Logic('order');
        $condition = array();
        $condition['order_state'] = ORDER_STATE_SEND;
        $condition['lock_state'] = 0;
        $condition['order_from'] = array('eq', 3);
        $condition['buyer_name'] = $source;
        $condition['add_time'] = array('gt',TIMESTAMP - 60 * 86400);
        
        //分批500批，每批处理10个订单，最多处理1000个订单
        $_break = false;
        $start = ($page-1)*500;
        for ($i = $start; $i < $start + 500; $i++){
            if ($_break) {
                break;
            }
            $limit = $i*10 . ',10';
            $order_list = $model_order->getOrderList($condition, '', '*', 'order_id asc', $limit);
            if (empty($order_list)) break;
            
            $order_ids = implode(',', array_column($order_list, 'fx_order_id'));
            $order_list = array_under_reset($order_list,'fx_order_id',1);
            if (empty($order_list)) break;
            $fenxiaoList = $fenxiao_service->getOrderStatus($order_ids);
            
            if (1 != $fenxiaoList['orderStatus']['result']) {
                continue;
            }
            foreach ($fenxiaoList['orderStatus']['list'] as $order_state) {
              	if (3 != $order_state['orderStatus']) {
               	    continue;
               	}
               	$order_info = $order_list[$order_state['orderSn']];
               	$result = $logic_order->changeOrderStateReceive($order_info,'system','系统','同步分销渠道自动完成订单');
               	if (!$result['state']) {
               	    $this->log('同步分销渠道自动完成订单失败SN:'.$order_info['order_sn']);
//                	    $_break = true;
//                	    break;
               	}
            }//foreach end
            
        }
    }
    
    /**
     * 发货入口
     */
    public function pushshipOp()
    {
    	if( !$_REQUEST['orderno'] || !$_REQUEST['logi_no'] || !$_REQUEST['num'] || !$_REQUEST['logi_name'] ) {
    		die( json_encode( array('succ' => '0', 'msg' => '缺少参数') ) ) ;
    	}
    	if( $_REQUEST['full_ship'] == '0' && !$_REQUEST['oid'] ) {
    		die( json_encode( array('succ' => '0', 'msg' => '非整单发货，缺少oid') ) ) ;
    	}
    	
    	$fenxiao_service = Service("Fenxiao") ;
    	$fenxiao_service -> init( $_REQUEST['source'] ) ;
    	$params = array(
			'orderno' => $_REQUEST['orderno'],
			'logi_no' => $_REQUEST['logi_no'],
			'oid' => $_REQUEST['oid'],
			'num' => $_REQUEST['num'],
			'logi_name' => $_REQUEST['logi_name']
    	) ;
    	$res = $fenxiao_service -> pushiship ( $params );
    	die( $res ) ;
    }
    
    /**
     * 保存订单入口，目前拼多多适用
     */
    public function saveorderOp()
    {
    	set_time_limit(0);
    	if( $_REQUEST['source'] != 'pinduoduo' ) {
    		die( json_encode( array('succ' => '0', 'msg' => $_REQUEST['source'].'不存在此方法') ) ) ;
    	}
    	$fenxiao_service = Service("Fenxiao") ;
    	$fenxiao_service -> init( $_REQUEST['source'] ) ;
    	$fenxiao_service -> saveOrder ();
    }
    
    /**
     * 检测没有保存的未发货订单
     */
    public function checkorderOp()
    {
    	set_time_limit(0);
    	$fenxiao_service = Service("Fenxiao") ;
    	$fenxiao_service -> init( $_REQUEST['source'] ) ;
    	$preDay = isset($_GET['preday']) && !empty($_GET['preday']) ? intval($_GET['preday']) : 1 ;
    	$fenxiao_service -> checkUnsaveOrder ( $preDay );
    }
    
    /**
     * 检测发货状态未推送到分销平台的订单
     */
    public function checkunshipOp()
    {
    	set_time_limit(0);
    	$fenxiao_service = Service("Fenxiao") ;
    	$fenxiao_service -> init( $_REQUEST['source'] ) ;
    	$fenxiao_service -> checkUnshipOrder ();
    }
    
    /**
     * 维权订单同步
     */
    public function refundorderOp()
    {
    	set_time_limit(0);
    	/** @var FenxiaoService $fenxiao_service */
    	$fenxiao_service = Service("Fenxiao") ;
    	$fenxiao_service -> init( $_REQUEST['source'] ) ;
    	$fenxiao_service -> getRefundOrder ();
    }

	/**
	 * 查询订单详情
	 */
	public function getOrderStatusGetOp()
	{
		set_time_limit(0);
		/** @var FenxiaoService $fenxiao_service */
		$fenxiao_service = Service("Fenxiao") ;
		$fenxiao_service -> init( $_REQUEST['source'] ) ;
		$fenxiao_service -> getOrderStatusGet ();
	}
    
    /**
     * 维权订单跟踪
     */
    public function tracerefundOp()
    {
    	set_time_limit(0);
        /** @var FenxiaoService $fenxiao_service */
        $fenxiao_service = Service("Fenxiao") ;
    	$fenxiao_service -> init( $_REQUEST['source'] ) ;
    	$fenxiao_service -> traceRefund ();
    }
    /**
     * 发货状态未推送分销平台订单重新推送
     */
    public function reshipOp()
    {
    	$model_ferr = Model('b2c_order_fenxiao_error') ;
    	$unship = $model_ferr -> where ( array('log_type' => 'unship') ) -> select () ;
    	
    	if( !$unship ) die ;
    	
    	foreach ( $unship as $row )
    	{
    		//if( !in_array($row['source'], array('pinduoduo', 'renrendian', 'youzan')) ) continue ;

            /** @var FenxiaoService $fenxiao_service */
    		$fenxiao_service = Service("Fenxiao") ;
    		$fenxiao_service -> init( $row['source'], 0 ) ;
    		$params = json_decode($row['error'], true) ;
            $params['logi_no'] = preg_replace('/\s/', '', $params['logi_no']);
    		$result = $fenxiao_service -> pushiship ( $params );
    		
    		unset( $fenxiao_service ) ;
    		
    		$res = json_decode($result, true) ;
    		if( $res['succ'] == '1' ) {
    			$upData = array('log_type' => 'ship') ;
    			$upWhere = array('id' => $row['id']) ;
    			$model_ferr -> where ( $upWhere ) -> update ( $upData ) ;
    		}
    	}
    }
    
    /**
     * 人人店根据订单号导入订单
     * 人人店订单列表接口故障，详情接口无问题，临时同步订单解决方案
     */
    function rrdImportOrderOp()
    {
    	header ( 'Content-Type:application/json; charset=utf-8' );
    	header ( "Expires: Mon, 26 Jul 1997 05:00:00 GMT" );
    	header ( "Cache-Control: no-cache" );
    	header ( "Pragma: no-cache" );
    	$params = file_get_contents ( "php://input" );
    	$ids = json_decode($params);
    	
    	require_once BASE_DATA_PATH . '/service/fenxiao/renrendian/renrendianCron.php';
    	$obj = new renrendianCron() ;
    	echo $obj -> importOrders( $ids ) ;
    }
    
    /**
     * 会员、订单数据推送CRM系统
     */
    function crmOp()
    {
    	$actions = array(
    			'addusers', 'addorders'	
    	) ;
    	if( !isset($_REQUEST['do']) || !in_array($_REQUEST['do'], $actions) ) {
    		die('no action') ;
    	}
    	
    	$action = $_REQUEST['do'] ;
    	Service("Crm") -> $action() ;
    }
    
    /**
     * 清除商家满送规则缓存
     */
    function clearMansongCacheOp()
    {
    	$list = Model('p_mansong')->getMansongList( array('state' => 2), null, '', 'DISTINCT(store_id)' ) ;
    	$storeids = !$list ? array() : array_column($list, 'store_id') ;
    	
    	foreach ($storeids as $id) {
    		dcache($id, 'goods_mansong');
    	}
    }
    
    function getPddHistoryOp()
    {
    	set_time_limit(0);
    	//获取最大的订单时间，从这个时间开始抓取
        $model = Model('b2c_fxhistory') ;
    	require_once BASE_DATA_PATH . '/service/fenxiao/pinduoduo/pddapi.php';
    	/** @var pddapi $obj */
    	$obj = new pddapi() ;

    	$result = $model -> field('MAX(order_time) as max') -> find();

    	$max = strtotime('2016-03-01') ;
    	if( isset($result['max']) && $result['max'] ) {
    		$max = $result['max'] ;
    	}
    		
    	$begin = date('Y-m-d', $max) ;
    	$beginTime = strtotime($begin) ;
    	$finishTime = $beginTime + 15*24*3600;
    	$et = strtotime('2016-10-09') ;
    	$finishTime = $finishTime > $et ? $et : $finishTime ;
    	
    	$beginTime = $_GET['begin'] ? strtotime($_GET['begin']) : $beginTime ;
    	$finishTime = $_GET['end'] ? strtotime($_GET['end']) : $finishTime ;
    	
    	$long = 24*3600 ;
    	$status = 4; //3已签收,2已发货,1待发货，4全部
    		
    	while( $beginTime < $finishTime ) {
    			
    		$where['begin'] = date("Y-m-d H:i:s", $beginTime) ;
    		$endTime = $beginTime + $long ;
    		$where['end'] = date("Y-m-d H:i:s", $endTime) ;
    		$total = $obj -> getAll( $status, $where ) ;

    		$logString = date('Y-m-d H:i:s', $beginTime) . "---" . date('Y-m-d H:i:s', $endTime) . "订单数量为". $total;
    		echo $logString."<br>";
    		//log::selflog($logString, 'pddhistory') ;
    		
    		//查找时间段内入库的订单数量
    		$condition = array();
    		$condition['order_time'] = array('second',array($beginTime,$endTime));
    		$condition['status'] = $status ;
    		$dbCount = $model -> where ( $condition ) -> count() ;
    		$logString .= "，数据库记录为：".$dbCount;
    		echo "数据库记录数量：" . $dbCount ."<br>";
    		if( $total != $dbCount ) {
    			log::selflog("【数量不一致:".date('Y-m-d H:i:s', $beginTime) . "---" . date('Y-m-d H:i:s', $endTime) . "】", 'pddhistory') ;
    		}
    			
    		$beginTime += $long ;
    		sleep(1);
    	}

    }
    
    /** 获取拼多多扣款明细记录到店铺费用  **/
    function chargebackOp()
    {
    	set_time_limit(0);
    	require_once BASE_DATA_PATH . '/service/fenxiao/pinduoduo/pinduoduoCron.php';
    	$obj = new sgsxCron();
    	$obj->getChargeBack();
    }
    
    function import_pddOp()
    {
    	set_time_limit(0);
    	$fenxiao_service = Service("Fenxiao") ;
    	$fenxiao_service -> init( 'pinduoduo' ) ;
    	require_once BASE_DATA_PATH . '/service/fenxiao/pinduoduo/pinduoduoCron.php';
    	$obj = new sgsxCron();
    	$obj->pdd_order($fenxiao_service);
    }
    
    /** 完结待客服处理分销退款单 **/
    function finishFxrefundOp()
    {
    	/** @var FenxiaoService $fenxiao_service **/
    	$fenxiao_service = Service("Fenxiao") ;
    	$fx_members = $fenxiao_service->getFenxiaoMembers() ;
    	/** @var refund_returnModel $model_refund **/
    	$model_refund = Model('refund_return');
    	$condition = array();
    	$condition['refund_state'] = 2 ;
    	$condition['seller_time'] = array('lt',time()-120) ;
    	//$condition['kefu_state'] = 1 ;
    	$condition['buyer_id'] = array('in', array_keys($fx_members) ) ;
    	$fx_refund_list = $model_refund->getRefundReturnList($condition) ;
    	$fx_return_list = $model_refund->getReturnList($condition) ;
        $fx_refund_list = array_merge($fx_refund_list,$fx_return_list);
    	/** @var RefundService $refund_service **/
    	$refund_service = Service("Refund");
    	foreach ($fx_refund_list as $refund) {v($refund,0);
    		$params = array(
    				'refund_id' => $refund['refund_id'] ,
    				'op_name' => $fx_members[$refund['buyer_id']]
    		) ;
    		$refund_service -> confirm_refund($params, $msg) ;
    		v($msg,0);
    	}
    }

	public function getSkuListOp(){
		set_time_limit(0);
		/** @var FenxiaoService $fenxiao_service */
		$fenxiao_service = Service("Fenxiao") ;
		$fenxiao_service -> init( $_REQUEST['source'] ) ;
		$fenxiao_service -> getSkuList ();
	}

	public function test_lcyOp(){

		$path = BASE_ROOT_PATH.'/doc/tids.txt';
		$file = fopen($path,"r");
		$fileData = array();
		while(! feof($file))
		{
			$fileData[] = fgets($file);
		}
		fclose($file);
		$bill_data = array();
		foreach($fileData as $tid){
			list($type, $bill_id, $order_id) = explode("_", $tid);
			$bill_data[$bill_id][] = $order_id;
		}

		$model_order = Model('order');
		$error_data = array();

		foreach($bill_data as $bill_id => $order_ids){

			$condition = array();
			$condition['ob_id'] = $bill_id;
			$bill_info = Model('order_bill')->where($condition)->find();

			$order_condition = array();
			$order_condition['order_state'] = ORDER_STATE_SUCCESS;
			$order_condition['store_id'] = $bill_info['ob_store_id'];
			$order_condition['finnshed_time'] = array('between',"{$bill_info['ob_start_date']},{$bill_info['ob_end_date']}");
			$order_list = $model_order->getOrderList($order_condition,'','*','order_id ASC', '');
			$order_list_ids = array_column($order_list,'order_id');
			if(!empty($order_ids)){
				foreach($order_ids as $order_id){
					if(!in_array($order_id,$order_list_ids)){
						$error_str = 'sap501_'.$bill_id.'_'.$order_id;
						echo $error_str;
					} else {
						$error_str = 'sap511_'.$bill_id.'_'.$order_id;
						echo $error_str;
					}
				}
			} else {
				foreach($order_ids as $order_id){
						$error_str = 'sap501_'.$bill_id.'_'.$order_id;
						echo $error_str;
				}
			}

		}
	}

    
    public function testOp()
    {
    	/*require_once BASE_DATA_PATH . '/service/fenxiao/renrendian/rrdInterface.php';
    	$obj = new rrdInterface() ;
    	$res = $obj -> getShippingCompany() ;
    	v($res) ;
    	exit;*/
    	
    	$order_sn = 'E2017100317480251623';
    	$data['order_sn'] = $order_sn; //分销系统订单编号
    	$data['buy_id'] = 197586; //分销商用户编号
    	$data['receiver']='Judith';//收件人
    	$data['provine'] = '测试省';
    	$data['city'] ='测试市';
    	$data['area'] = '测试区';
    	$data['address'] = '镇宁路55号c 101';
    	$data['mobile']='13601819020'; //手机号码
    	$data['remark'] = 'remark message...';
    	$data['amount'] = 23.90;
    	$data['payment_code'] = 'fenxiao';//订单来源  fenxiao,jicai
    	$data['order_time']=time()+$i;//下单时间，时间戳
    	$data['item'] = array(
    			array(
    					'goods_id'=>100490, //对应b2c_category的pid
    					'num'=>1, //数量
    					'price'=>23.9, //单价
    					'oid' => 4900998, //分销子订单号，无子订单可以不传
    			),
    			/*array(
    					'goods_id'=>101638, //对应b2c_category的pid
    					'num'=>1, //数量
    					'price'=>9.9, //单价
    					'oid' => 4900999, //分销子订单号，无子订单可以不传
    			)*/
    	);
    	$data['save_type'] = 'insert' ;
    	$data['order_from'] = '3' ;
    	$data['key'] = '6c4250aca35add95acd94f2d644f2936' ;
    	$params = json_encode($data) ;
    	$res = Model('order')->createFxOrder( $params );v($res);
    	exit;
    	
    	$refundService = Service("Refund") ;
    	Model('refund_return')->where(array('refund_id'=>1080))->update(array('seller_state'=>2, 'refund_state'=>2));
    	$params = array(
    			'refund_id' => '1080' ,
    			'op_name' => 'fenxiao',
    	) ;
    	$refundService -> confirm_refund($params, $msg);
    	v($msg);
    	
    	error_reporting(7);
        require_once BASE_DATA_PATH . '/service/fenxiao/pinduoduo/pinduoduoCron.php';
        $obj = new sgsxCron() ;
        $obj->getOrderStatus('161031-66633893502,161031-45308190651');
        die;

    	require_once BASE_DATA_PATH . '/service/fenxiao/youzan/youzanCron.php';
    	$obj = new youzanCron() ;
    	$res = $obj -> getOrderDetail( 'E20160930111726016270516' ) ;
    	v($res) ;
    	exit;
    	
    	require_once BASE_DATA_PATH . '/service/fenxiao/renrendian/rrdInterface.php';
    	$obj = new rrdInterface() ;
    	$res = $obj -> getOrderDetail( 'E2016100711145307841' ) ;
    	v($res) ;
    	exit;

    	/** @var FenxiaoService $service */
    	$service = Service("Fenxiao") ;
    	$service -> init('youzan') ;
    	$params = array(
    			'orderno' => 'E20160908162022042675006',
    			'oid' => '17896776',
    			'logi_name' => '国通快递',
    			'logi_no' => '3118804666'
    	) ;
    	$service -> pushiship($params) ;
    	exit;

    	$refundService = Service("Refund") ;
    	$params = array(
    			'refund_id' => '128' ,
    			'op_name' => 'pinduoduo',
    	) ;
    	$refundService -> confirm_refund($params, $msg);
    	v($msg);
    	
    	require_once BASE_DATA_PATH . '/service/fenxiao/renrendian/rrdInterface.php';
    	$obj = new rrdInterface() ;
    	$res = $obj -> getOrderDetail( 'E2016081309490379225 ' ) ;
    	v($res) ;
    	exit;
    	require_once BASE_DATA_PATH . '/service/fenxiao/youzan/youzanCron.php';
    	$obj = new youzanCron() ;
    	$res = $obj -> getUnshipOrder( array('begin' => '2016-06-25 00:00:00', 'end' => '2016-07-31 23:00:00') ) ;
    	v($res);
    	exit;
    }
    
    /**
     * 转换商品名为拼音，更新到goods_name_py表
     */
    function topyOp()
    {
    	/** @var goodsModel $model_goods **/
    	$model_goods = Model('goods');
    	$condition = array();
    	$condition['goods_name_py'] = array('eq', '') ;
    	$list = $model_goods->getGoodsCommonList($condition, 'goods_commonid,goods_name', 1000);
    	echo count($list);
    	import('pinyin');
    	$py = new CUtf8_PY;
    	
    	foreach ($list as $row) {
    		$commonid = $row['goods_commonid'] ;
    		$name_py = $py->encode($row['goods_name'], 'all');
			$name_py = str_replace(' ','',$name_py);
    		
    		$cond = array();
    		$cond['goods_commonid'] = $commonid;
    		$update = array();
    		$update['goods_name_py'] = $name_py;
    		$model_goods->table('goods_common')->where($cond)->update($update);
    	}
    	
    	//更新店铺拼音
    	/** @var storeModel $model_store **/
    	$model_store = Model('store') ;
    	$condition = array();
    	$condition['store_name_py'] = array('eq', '') ;
    	$list = $model_store->getStoreList($condition, 1000);
    	echo "<br>" . count($list);
    	foreach ($list as $row) {
    		$name_py = $py->encode($row['store_name'], 'all');
    		$name_py = str_replace(' ','',$name_py);
    		
    		$cond = array();
    		$cond['store_id'] = $row['store_id'];
    		$update = array();
    		$update['store_name_py'] = $name_py ;
    		$model_store->where($cond)->update($update);
    	}
    }

}