<?php
/**
 * 卖家实物订单管理
 *
 *
 *
 *
 * @汉购网提供技术支持 授权请购买shopnc授权
 * @license    http://www.hangowa.com
 * @link       交流群号：
 */

defined('ByShopWWI') or exit('Access Invalid!');
class store_orderControl extends BaseSellerControl {
	/**
	 * 每次导出多少条记录
	 * @var integer
	 */
	const EXPORT_SIZE = 5000;
	public function __construct() {
		parent::__construct();
		Language::read('member_store_index');
	}

	/**
	 * 订单列表
	 *
	 */
	public function indexOp() {

	    /** @var orderModel $model_order */
		$model_order = Model('order');
		if (!$_GET['state_type']) {
			$_GET['state_type'] = 'store_order';
		}
		$extra_cond = array();
		if (!empty($_GET['fx_order_id'])) {
		    $extra_cond = array('fx_order_id' => $_GET['fx_order_id']);
		}
        if(!empty($_GET['buyer_phone'])){
            $extra_cond['buyer_phone']=array('like',"%{$_GET['buyer_phone']}%");;
        }
		$order_list = $model_order -> getStoreOrderList($_SESSION['store_id'], $_GET['order_sn'], $_GET['buyer_name'], $_GET['state_type'], $_GET['query_start_date'], $_GET['query_end_date'], 
		      $_GET['skip_off'], '*', array('order_goods', 'order_common', 'member'), null, $extra_cond,$_GET['refund_only']);
		//end to Excel
        foreach ($order_list as $key => $value) {
            $order_list[$key]['delay_msg'] = $model_order->delayCheck($value);
            //是否支持电子面单发货
            $order_list[$key]['can_printship']= $model_order->getOrderOperateState('print_ship',$value);
        }

        $member_fenxiao = Model('member_fenxiao')->getMembeFenxiaoList(array(),999999);
		Tpl::output('order_list', $order_list);
		Tpl::output('member_fenxiao', $member_fenxiao);
		Tpl::output('show_page', $model_order -> showpage());
		Tpl::output('is_hango', $this->store_info['is_hango']);
		self::profile_menu('list', $_GET['state_type']);

		Tpl::showpage('store_order.index');

	}
	
	/***
	 * 订单导出
	 */
	public function excelOrderOp(){
        $is_hango = $this->store_info['is_hango'];

        ini_set('memory_limit','4G');
	    /** @var orderModel $model_order */
		$model_order = Model('order');
		$extra_cond = array();
		if (!empty($_GET['fx_order_id'])) {
		    $extra_cond = array('fx_order_id' => $_GET['fx_order_id']);
		}

		$condition = array();
		if (preg_match('/^\d{8,20}$/',$_GET['query_order_no'])) {
			$condition['order_sn'] = $_GET['query_order_no'];
		}
		$condition['order_state'] = ORDER_STATE_SUCCESS;
		$condition['store_id'] = $_SESSION['store_id'];
		$if_start_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_start_date']);
		$if_end_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_end_date']);
		$start_unixtime = $if_start_date ? strtotime($_GET['query_start_date']) : null;
		$end_unixtime = $if_end_date ? strtotime($_GET['query_end_date']) : null;
		if ($if_start_date || $if_end_date) {
			$condition['finnshed_time'] = array('time',array($start_unixtime,$end_unixtime));
		}

		if(!is_numeric($_GET['curpage'])){
			//如果数量小，直接下载
			$count = $model_order -> getStoreOrderListToExcelCount(
				$_SESSION['store_id'],
				$_GET['order_sn'],
				$_GET['buyer_name'],
				99999,
				$_GET['state_type'],
				$_GET['query_start_date'],
				$_GET['query_end_date'],
				$_GET['skip_off'], '*',
				array('order_goods', 'order_common', 'member'),
				null,
				$extra_cond,
                $_GET['refund_only']
			);

			$array = array();
			if ($count > self::EXPORT_SIZE ){
				//显示下载链接
				$page = ceil($count/self::EXPORT_SIZE);
				for ($i=1;$i<=$page;$i++){
					$limit1 = ($i-1)*self::EXPORT_SIZE + 1;
					$limit2 = $i*self::EXPORT_SIZE > $count ? $count : $i*self::EXPORT_SIZE;
					$array[$i] = $limit1.' ~ '.$limit2 ;
				}
				Tpl::output('list',$array);
				Tpl::showpage('store_export.excel');
				exit();
			}else{
				//下载
				//如果数量小，直接下载
				$orderListToExcel = $model_order -> getStoreOrderListToExcel(
					$_SESSION['store_id'],
					$_GET['order_sn'],
					$_GET['buyer_name'],
					self::EXPORT_SIZE,
					$_GET['state_type'],
					$_GET['query_start_date'],
					$_GET['query_end_date'],
					$_GET['skip_off'], '*',
					array('order_goods', 'order_common', 'member'),
					null,
					$extra_cond,
                    $_GET['refund_only']
				);

			}
		} else {
			//下载
			$limit1 = ($_GET['curpage']-1) * self::EXPORT_SIZE;
			$limit2 = self::EXPORT_SIZE;
			//如果数量小，直接下载
			$orderListToExcel = $model_order -> getStoreOrderListToExcel(
				$_SESSION['store_id'],
				$_GET['order_sn'],
				$_GET['buyer_name'],
				"{$limit1},{$limit2}",
				$_GET['state_type'],
				$_GET['query_start_date'],
				$_GET['query_end_date'],
				$_GET['skip_off'], '*',
				array('order_goods', 'order_common', 'member'),
				null,
				$extra_cond,
                $_GET['refund_only']
			);


		}

		if($is_hango == 1){
		    $fxModel = Model('b2c_order_fenxiao');
		    $fxLogs = $fxModel->where(array('orderno'=>array('in',array_filter(array_column($orderListToExcel,'fx_order_id')))))->limit(999999)->select();
            $fxLogs = array_under_reset($fxLogs,'orderno');
        }

		vendor('PHPExcel');
		$objExcel = new \PHPExcel();
		$objExcel->getActiveSheet ()->setTitle ( '实物交易订单导出' );
		$objExcel->getActiveSheet ()->mergeCells ( 'A1:N1' );
		$objExcel->getActiveSheet ()->getStyle ( 'A1' )->getAlignment ()->setHorizontal ( \PHPExcel_Style_Alignment::HORIZONTAL_CENTER );
		$objExcel->getActiveSheet ()->getStyle ( 'A1' )->getFont ()->setSize ( 15 );
		$objExcel->getActiveSheet ()->getStyle ( 'A1' )->getFont ()->setBold ( true );
		$objExcel->getActiveSheet ()->getColumnDimension ( 'A' )->setWidth ( 22 );
		$objExcel->getActiveSheet ()->getColumnDimension ( 'B' )->setWidth ( 10 );
		$objExcel->getActiveSheet ()->getColumnDimension ( 'C' )->setWidth ( 15 );
		$objExcel->getActiveSheet ()->getColumnDimension ( 'D' )->setWidth ( 15 );
		$objExcel->getActiveSheet ()->getColumnDimension ( 'E' )->setWidth ( 25 );
		$objExcel->getActiveSheet ()->getColumnDimension ( 'F' )->setWidth ( 25 );
		$objExcel->getActiveSheet ()->getColumnDimension ( 'G' )->setWidth ( 25 );
		$objExcel->getActiveSheet ()->getColumnDimension ( 'H' )->setWidth ( 25 );
		$objExcel->getActiveSheet ()->getColumnDimension ( 'I' )->setWidth ( 25 );
		$objExcel->getActiveSheet ()->getColumnDimension ( 'J' )->setWidth ( 25 );
		$objExcel->getActiveSheet ()->getColumnDimension ( 'K' )->setWidth ( 25 );
		$objExcel->getActiveSheet ()->getColumnDimension ( 'L' )->setWidth ( 25 );
		$objExcel->getActiveSheet ()->getColumnDimension ( 'M' )->setWidth ( 25 );
		$objExcel->getActiveSheet ()->getColumnDimension ( 'N' )->setWidth ( 25 );
		$objExcel->getActiveSheet ()->getColumnDimension ( 'O' )->setWidth ( 15 );
		$objExcel->getActiveSheet ()->getColumnDimension ( 'P' )->setWidth ( 25 );
		$objExcel->getActiveSheet ()->getColumnDimension ( 'Q' )->setWidth ( 25 );
		$objExcel->getActiveSheet ()->getColumnDimension ( 'R' )->setWidth ( 25 );
		$objExcel->getActiveSheet ()->getColumnDimension ( 'S' )->setWidth ( 25 );
		$objExcel->getActiveSheet ()->getColumnDimension ( 'T' )->setWidth ( 25 );
		$objExcel->getActiveSheet ()->getColumnDimension ( 'U' )->setWidth ( 25 );
		$objExcel->getActiveSheet ()->getColumnDimension ( 'V' )->setWidth ( 25 );
		$objExcel->getActiveSheet ()->getStyle ( 'A2' )->getAlignment ()->setHorizontal ( \PHPExcel_Style_Alignment::HORIZONTAL_CENTER );
		$objExcel->getActiveSheet ()->getStyle ( 'B2' )->getAlignment ()->setHorizontal ( \PHPExcel_Style_Alignment::HORIZONTAL_CENTER );
		$objExcel->getActiveSheet ()->getStyle ( 'C2' )->getAlignment ()->setHorizontal ( \PHPExcel_Style_Alignment::HORIZONTAL_CENTER );
		$objExcel->getActiveSheet ()->getStyle ( 'D2' )->getAlignment ()->setHorizontal ( \PHPExcel_Style_Alignment::HORIZONTAL_CENTER );
		$objExcel->getActiveSheet ()->getStyle ( 'E2' )->getAlignment ()->setHorizontal ( \PHPExcel_Style_Alignment::HORIZONTAL_CENTER );
		$objExcel->getActiveSheet ()->getStyle ( 'F2' )->getAlignment ()->setHorizontal ( \PHPExcel_Style_Alignment::HORIZONTAL_CENTER );
		$objExcel->getActiveSheet ()->getStyle ( 'G2' )->getAlignment ()->setHorizontal ( \PHPExcel_Style_Alignment::HORIZONTAL_CENTER );
		$objExcel->getActiveSheet ()->getStyle ( 'H2' )->getAlignment ()->setHorizontal ( \PHPExcel_Style_Alignment::HORIZONTAL_CENTER );
		$objExcel->getActiveSheet ()->getStyle ( 'I2' )->getAlignment ()->setHorizontal ( \PHPExcel_Style_Alignment::HORIZONTAL_CENTER );
		$objExcel->getActiveSheet ()->getStyle ( 'J2' )->getAlignment ()->setHorizontal ( \PHPExcel_Style_Alignment::HORIZONTAL_CENTER );
		$objExcel->getActiveSheet ()->getStyle ( 'K2' )->getAlignment ()->setHorizontal ( \PHPExcel_Style_Alignment::HORIZONTAL_CENTER );
		$objExcel->getActiveSheet ()->getStyle ( 'L2' )->getAlignment ()->setHorizontal ( \PHPExcel_Style_Alignment::HORIZONTAL_CENTER );
		$objExcel->getActiveSheet ()->getStyle ( 'M2' )->getAlignment ()->setHorizontal ( \PHPExcel_Style_Alignment::HORIZONTAL_CENTER );
		$objExcel->getActiveSheet ()->getStyle ( 'N2' )->getAlignment ()->setHorizontal ( \PHPExcel_Style_Alignment::HORIZONTAL_CENTER );
		$objExcel->getActiveSheet ()->getStyle ( 'O2' )->getAlignment ()->setHorizontal ( \PHPExcel_Style_Alignment::HORIZONTAL_CENTER );
		$objExcel->getActiveSheet ()->getStyle ( 'P2' )->getAlignment ()->setHorizontal ( \PHPExcel_Style_Alignment::HORIZONTAL_CENTER );
		$objExcel->getActiveSheet ()->getStyle ( 'Q2' )->getAlignment ()->setHorizontal ( \PHPExcel_Style_Alignment::HORIZONTAL_CENTER );
		$objExcel->getActiveSheet ()->getStyle ( 'R2' )->getAlignment ()->setHorizontal ( \PHPExcel_Style_Alignment::HORIZONTAL_CENTER );
		$objExcel->getActiveSheet ()->getStyle ( 'S2' )->getAlignment ()->setHorizontal ( \PHPExcel_Style_Alignment::HORIZONTAL_CENTER );
		$objExcel->getActiveSheet ()->getStyle ( 'T2' )->getAlignment ()->setHorizontal ( \PHPExcel_Style_Alignment::HORIZONTAL_CENTER );
		$objExcel->getActiveSheet ()->getStyle ( 'U2' )->getAlignment ()->setHorizontal ( \PHPExcel_Style_Alignment::HORIZONTAL_CENTER );
		$objExcel->getActiveSheet ()->getStyle ( 'V2' )->getAlignment ()->setHorizontal ( \PHPExcel_Style_Alignment::HORIZONTAL_CENTER );
		$objWriter = \PHPExcel_IOFactory::createWriter ( $objExcel, 'Excel2007' );
		$objActSheet = $objExcel->getActiveSheet ();
		$key = ord ( "A" );
		$objActSheet->setCellValue ( "A1", '实物交易订单导出' );
		$objActSheet->setCellValue ( "A2", '订单号' );
		$objActSheet->setCellValue ( "B2", '价格(元)' );
		$objActSheet->setCellValue ( "C2", '运费(元)' );
		$objActSheet->setCellValue ( "D2", '收件人姓名' );
		$objActSheet->setCellValue ( "E2", '电话' );
		$objActSheet->setCellValue ( "F2", '地区' );
		$objActSheet->setCellValue ( "G2", '地址' );
		$objActSheet->setCellValue ( "H2", '参考收货地址一' );
		$objActSheet->setCellValue ( "I2", '参考收货地址二' );
		$objActSheet->setCellValue ( "J2", '商品编号' );
		$objActSheet->setCellValue ( "K2", '商品名称' );
		$objActSheet->setCellValue ( "L2", '商品数量' );
		$objActSheet->setCellValue ( "M2", '订单状态' );
		$objActSheet->setCellValue ( "N2", '物流公司' );
		$objActSheet->setCellValue ( "O2", '物流单号' );
		$objActSheet->setCellValue ( "P2", '发货时间' );
		$objActSheet->setCellValue ( "Q2", '用户留言' );
		$objActSheet->setCellValue ( "R2", '用户ID' );
        $objActSheet->setCellValue ( "S2", '供应商' );
        $objActSheet->setCellValue ( "T2", '是否超区' );
		if ($is_hango == 1) {
            $objActSheet->setCellValue ( "U2", '分销渠道' );
            $objActSheet->setCellValue ( "V2", '分销订单号' );
            $objActSheet->setCellValue ( "W2", '导入时间' );
            $objActSheet->setCellValue ( "X2", '贴标' );
        }
		// end set excel style
		$k = 3;
		// 物流公司信息 数组

        $nonDeliveryStatus = array(
            '-1'=>'未超区',
            '0'=>'未检测',
            '1'=>'超区',
            '10'=>'部分超区',
        );
		$express_arr = Model('express')->getExpressList();

		// 渠道
        $member_fenxiao = Model('member_fenxiao')->getMembeFenxiaoList();
        $member_fenxiao = array_under_reset($member_fenxiao, 'member_id');

		foreach ( $orderListToExcel as $index => $order ) {
			// 物流公司
			$expressName = '';
			if ($order ['extend_order_common'] ['shipping_express_id'] != null && $order ['extend_order_common'] ['shipping_express_id'] != '') {
				$expressName = $express_arr [$order ['extend_order_common'] ['shipping_express_id']] ['e_name'];
			}
			
			// 格式化发货时间
			$deliverDate = '';
			if ($order ['extend_order_common'] ['shipping_time'] != null && $order ['extend_order_common'] ['shipping_time'] != '' && $order ['extend_order_common'] ['shipping_time'] != '0') {
				$deliverDate = date ( 'Y-m-d H:i:s', $order ['extend_order_common'] ['shipping_time'] );
			}
			$status = '';
			if ($order ['order_state'] == ORDER_STATE_CANCEL) {
				$status = '已取消';
			} elseif ($order ['order_state'] == ORDER_STATE_NEW) {
				$status = '未支付';
			} elseif ($order ['order_state'] == ORDER_STATE_PAY) {
				$status = '已付款';
			} elseif ($order ['order_state'] == ORDER_STATE_PREPARE) {
				$status = '备货中';
			} elseif ($order ['order_state'] == ORDER_STATE_SEND) {
				$status = '已发货';
			} elseif ($order ['order_state'] == ORDER_STATE_SUCCESS) {
				$status = '已完成';
			}
			// 填充数据
            $member_fenxiao_i = $member_fenxiao[$order['buyer_id']];
			foreach ( $order ['extend_order_goods'] as $i => $goodes ) {
				$objActSheet->setCellValue ( "A" . $k, ' ' . $order ['order_sn'] );
				$objActSheet->setCellValue ( "B" . $k, $goodes ['goods_price'] );
				$objActSheet->setCellValue ( "C" . $k, $goodes ['shipping_fee'] );
				$recuver_name = $order['extend_order_common']['reciver_name'];
                $preg = "/[^a-zA-Z0-9\p{Han}]/u";
                $recuver_name = preg_replace($preg, '', $recuver_name);
				$objActSheet->setCellValue ( "D" . $k, $recuver_name );
                $mobile = preg_replace('/\D/', '', $order ['buyer_phone']);
				$objActSheet->setCellValue ( "E" . $k, ' ' . $mobile );
                $str=$order ['extend_order_common'] ['reciver_info'] ['address'];
                $sub=$order ['extend_order_common'] ['reciver_info'] ['area'];
                $zhixiashi=array('北京','上海','重庆','天津',"北京市",'天津市','重庆市','上海市',"北京省",'天津省','重庆省','上海省');
                $distriction=explode(" ",$sub);
                if(!empty($sub)&&$sub!==NULL) {
                    if (!empty($distriction[0]) && strpos($distriction[0], '省')==false) $distriction[0] .= "省";
                    if (!empty($distriction[1]) && strpos($distriction[1], '市') == false) $distriction[1] .= "市";
                    if (!empty($distriction[2]) && strpos($distriction[2], '区') ==false) $distriction[2] .= "区";
                    if (in_array($distriction[0], $zhixiashi)) {
                        $sub = str_replace('省','',$sub);
                        unset($distriction[0]);
                    }
                    $distriction = implode('', $distriction);
                }else{
                    $distriction='';
                }
                $address=str_replace(explode(" ",$sub),"",$str);
                $str_first=explode(' ',$str);
                if(count($str_first)>3){
                    unset($str_first[0],$str_first[1],$str_first[2]);
                    $str=$str_first[3];
                }
                $add_new = $distriction.$address;
                $add_new = str_replace(' ', '', $add_new);
                $add_new = str_replace('#', '号', $add_new);
                $objActSheet->setCellValue ( "F" . $k, $sub);
				$objActSheet->setCellValue ( "G" . $k, $str);
				$objActSheet->setCellValue ( "H" . $k, $sub.$str);
				$objActSheet->setCellValue ( "I" . $k, $add_new);
				$objActSheet->setCellValue ( "J" . $k, ' ' . $goodes ['goods_id'] );
				$objActSheet->setCellValue ( "K" . $k, $goodes ['goods_name'] );
				$objActSheet->setCellValue ( "L" . $k, $goodes ['goods_num'] );
				$objActSheet->setCellValue ( "M" . $k, $status );
				$objActSheet->setCellValue ( "N" . $k, $expressName );
				$objActSheet->setCellValue ( "O" . $k, ' ' . $order ['shipping_code'] );
				$objActSheet->setCellValue ( "P" . $k, $deliverDate );
				$objActSheet->setCellValue ( "Q" . $k, $order['extend_order_common'] ['order_message'] .!empty($order['extend_order_common']['distribution_channel']) ? "【".$order['extend_order_common']['distribution_channel']."】":'');
				$objActSheet->setCellValue ( "R" . $k, $order['buyer_id'] );
                $objActSheet->setCellValue ( "S" . $k, $goodes['sup']);
                $objActSheet->setCellValue ( "T" . $k, isset($nonDeliveryStatus[$order['non_delivery']])?$nonDeliveryStatus[$order['non_delivery']]:'未检测');

                $sub = '';
                if (!empty($member_fenxiao_i) && $member_fenxiao_i['is_sign'] == 1) {
                    $sub = $goodes['sup'];
                }
                if ($is_hango == 1) {
                    $objActSheet->setCellValue ( "U" . $k, orderFrom($order['order_from'],$order['buyer_name']) );
                    $objActSheet->setCellValue ( "V" . $k, ' '.$order['fx_order_id'] );
                    $logTime = '';
                    if(isset($fxLogs[$order['fx_order_id']])) $logTime = date('Y-m-d H:i:s',$fxLogs[$order['fx_order_id']]['log_time']);
                    $objActSheet->setCellValue ( "W" . $k, ' '.$logTime );
                    $objActSheet->setCellValue ( "X" . $k, $sub);
                }
				$k ++;
			}
		}
		$this->_prepareOrder($orderListToExcel);
		ob_end_clean();
		// 输出excel信息
		$outfile = '实物交易订单' . date ( 'Y-m-d' ) . '.xlsx';
		header ( "Content-Type: application/force-download" );
		header ( "Content-Type: application/octet-stream" );
		header ( "Content-Type: application/download" );
		header ( 'Content-Disposition:inline;filename="' . $outfile . '"' );
		header ( "Content-Transfer-Encoding: binary" );
		header ( "Cache-Control: must-revalidate, post-check=0, pre-check=0" );
		header ( "Pragma: no-cache" );
		$objWriter->save ( 'php://output' );
		exit ();
	}


    /***
     * 邮政订单导出 自有平台
     */
    public function newExcelOutOp(){

        $is_hango = $this->store_info['is_hango'];

        if ($is_hango != 1) {
            exit('out');
        }

        ini_set('memory_limit','4G');
        /** @var orderModel $model_order */
        $model_order = Model('order');
        $extra_cond = array();
        if (!empty($_GET['fx_order_id'])) {
            $extra_cond = array('fx_order_id' => $_GET['fx_order_id']);
        }

        $condition = array();
        if (preg_match('/^\d{8,20}$/',$_GET['query_order_no'])) {
            $condition['order_sn'] = $_GET['query_order_no'];
        }
        $condition['order_state'] = ORDER_STATE_SUCCESS;
        $condition['store_id'] = $_SESSION['store_id'];
        $if_start_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_start_date']);
        $if_end_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_end_date']);
        $start_unixtime = $if_start_date ? strtotime($_GET['query_start_date']) : null;
        $end_unixtime = $if_end_date ? strtotime($_GET['query_end_date']) : null;
        if ($if_start_date || $if_end_date) {
            $condition['finnshed_time'] = array('time',array($start_unixtime,$end_unixtime));
        }

        if(!is_numeric($_GET['curpage'])){
            //如果数量小，直接下载
            $count = $model_order -> getStoreOrderListToExcelCount(
                $_SESSION['store_id'],
                $_GET['order_sn'],
                $_GET['buyer_name'],
                99999,
                $_GET['state_type'],
                $_GET['query_start_date'],
                $_GET['query_end_date'],
                $_GET['skip_off'], '*',
                array('order_goods', 'order_common', 'member'),
                null,
                $extra_cond,
                $_GET['refund_only']
            );

            $array = array();
            if ($count > self::EXPORT_SIZE ){
                //显示下载链接
                $page = ceil($count/self::EXPORT_SIZE);
                for ($i=1;$i<=$page;$i++){
                    $limit1 = ($i-1)*self::EXPORT_SIZE + 1;
                    $limit2 = $i*self::EXPORT_SIZE > $count ? $count : $i*self::EXPORT_SIZE;
                    $array[$i] = $limit1.' ~ '.$limit2 ;
                }
                Tpl::output('list',$array);
                Tpl::showpage('store_export.excel');
                exit();
            }else{
                //下载
                //如果数量小，直接下载
                $orderListToExcel = $model_order -> getStoreOrderListToExcel(
                    $_SESSION['store_id'],
                    $_GET['order_sn'],
                    $_GET['buyer_name'],
                    self::EXPORT_SIZE,
                    $_GET['state_type'],
                    $_GET['query_start_date'],
                    $_GET['query_end_date'],
                    $_GET['skip_off'], '*',
                    array('order_goods', 'order_common', 'member'),
                    null,
                    $extra_cond,
                    $_GET['refund_only']
                );

            }
        } else {
            //下载
            $limit1 = ($_GET['curpage']-1) * self::EXPORT_SIZE;
            $limit2 = self::EXPORT_SIZE;
            //如果数量小，直接下载
            $orderListToExcel = $model_order -> getStoreOrderListToExcel(
                $_SESSION['store_id'],
                $_GET['order_sn'],
                $_GET['buyer_name'],
                "{$limit1},{$limit2}",
                $_GET['state_type'],
                $_GET['query_start_date'],
                $_GET['query_end_date'],
                $_GET['skip_off'], '*',
                array('order_goods', 'order_common', 'member'),
                null,
                $extra_cond,
                $_GET['refund_only']
            );

        }
        vendor('PHPExcel');
        $objExcel = new \PHPExcel();
        $objWriter = \PHPExcel_IOFactory::createWriter ( $objExcel, 'Excel2007' );
        $objExcel->setactivesheetindex(0);
        $objActSheet = $objExcel->getActiveSheet ();
        $objActSheet->setTitle ( '订单头部信息' );
        $objActSheet->mergeCells ( 'A1:AB1' );
        $objActSheet->getStyle ( 'A1' )->getAlignment ()->setHorizontal ( \PHPExcel_Style_Alignment::HORIZONTAL_CENTER );
        $objActSheet->getStyle ( 'A1' )->getFont ()->setSize ( 15 );
        $objActSheet->getStyle ( 'A1' )->getFont ()->setBold ( true );

        $objActSheet->getColumnDimension ( 'A' )->setWidth ( 22 );
        $objActSheet->getColumnDimension ( 'B' )->setWidth ( 10 );
        $objActSheet->getColumnDimension ( 'C' )->setWidth ( 15 );
        $objActSheet->getColumnDimension ( 'D' )->setWidth ( 15 );
        $objActSheet->getColumnDimension ( 'E' )->setWidth ( 25 );
        $objActSheet->getColumnDimension ( 'F' )->setWidth ( 25 );
        $objActSheet->getColumnDimension ( 'G' )->setWidth ( 25 );
        $objActSheet->getColumnDimension ( 'H' )->setWidth ( 25 );
        $objActSheet->getColumnDimension ( 'I' )->setWidth ( 25 );
        $objActSheet->getColumnDimension ( 'J' )->setWidth ( 25 );
        $objActSheet->getColumnDimension ( 'K' )->setWidth ( 25 );
        $objActSheet->getColumnDimension ( 'L' )->setWidth ( 25 );
        $objActSheet->getColumnDimension ( 'M' )->setWidth ( 25 );
        $objActSheet->getColumnDimension ( 'N' )->setWidth ( 45 );
        $objActSheet->getColumnDimension ( 'O' )->setWidth ( 15 );
        $objActSheet->getColumnDimension ( 'P' )->setWidth ( 25 );
        $objActSheet->getColumnDimension ( 'Q' )->setWidth ( 25 );
        $objActSheet->getColumnDimension ( 'R' )->setWidth ( 25 );
        $objActSheet->getColumnDimension ( 'S' )->setWidth ( 25 );
        $objActSheet->getColumnDimension ( 'T' )->setWidth ( 20 );
        $objActSheet->getColumnDimension ( 'U' )->setWidth ( 20 );
        $objActSheet->getColumnDimension ( 'V' )->setWidth ( 20 );
        $objActSheet->getColumnDimension ( 'W' )->setWidth ( 20 );
        $objActSheet->getColumnDimension ( 'X' )->setWidth ( 20 );
        $objActSheet->getColumnDimension ( 'Y' )->setWidth ( 20 );
        $objActSheet->getColumnDimension ( 'Z' )->setWidth ( 20 );
        $objActSheet->getColumnDimension ( 'AA' )->setWidth ( 20 );
        $objActSheet->getColumnDimension ( 'AB' )->setWidth ( 20 );
        $objActSheet->getStyle ( 'A2' )->getAlignment ()->setHorizontal ( \PHPExcel_Style_Alignment::HORIZONTAL_CENTER );
        $objActSheet->getStyle ( 'B2' )->getAlignment ()->setHorizontal ( \PHPExcel_Style_Alignment::HORIZONTAL_CENTER );
        $objActSheet->getStyle ( 'C2' )->getAlignment ()->setHorizontal ( \PHPExcel_Style_Alignment::HORIZONTAL_CENTER );
        $objActSheet->getStyle ( 'D2' )->getAlignment ()->setHorizontal ( \PHPExcel_Style_Alignment::HORIZONTAL_CENTER );
        $objActSheet->getStyle ( 'E2' )->getAlignment ()->setHorizontal ( \PHPExcel_Style_Alignment::HORIZONTAL_CENTER );
        $objActSheet->getStyle ( 'F2' )->getAlignment ()->setHorizontal ( \PHPExcel_Style_Alignment::HORIZONTAL_CENTER );
        $objActSheet->getStyle ( 'G2' )->getAlignment ()->setHorizontal ( \PHPExcel_Style_Alignment::HORIZONTAL_CENTER );
        $objActSheet->getStyle ( 'H2' )->getAlignment ()->setHorizontal ( \PHPExcel_Style_Alignment::HORIZONTAL_CENTER );
        $objActSheet->getStyle ( 'I2' )->getAlignment ()->setHorizontal ( \PHPExcel_Style_Alignment::HORIZONTAL_CENTER );
        $objActSheet->getStyle ( 'J2' )->getAlignment ()->setHorizontal ( \PHPExcel_Style_Alignment::HORIZONTAL_CENTER );
        $objActSheet->getStyle ( 'K2' )->getAlignment ()->setHorizontal ( \PHPExcel_Style_Alignment::HORIZONTAL_CENTER );
        $objActSheet->getStyle ( 'L2' )->getAlignment ()->setHorizontal ( \PHPExcel_Style_Alignment::HORIZONTAL_CENTER );
        $objActSheet->getStyle ( 'M2' )->getAlignment ()->setHorizontal ( \PHPExcel_Style_Alignment::HORIZONTAL_CENTER );
        $objActSheet->getStyle ( 'N2' )->getAlignment ()->setHorizontal ( \PHPExcel_Style_Alignment::HORIZONTAL_CENTER );
        $objActSheet->getStyle ( 'O2' )->getAlignment ()->setHorizontal ( \PHPExcel_Style_Alignment::HORIZONTAL_CENTER );
        $objActSheet->getStyle ( 'P2' )->getAlignment ()->setHorizontal ( \PHPExcel_Style_Alignment::HORIZONTAL_CENTER );
        $objActSheet->getStyle ( 'Q2' )->getAlignment ()->setHorizontal ( \PHPExcel_Style_Alignment::HORIZONTAL_CENTER );
        $objActSheet->getStyle ( 'R2' )->getAlignment ()->setHorizontal ( \PHPExcel_Style_Alignment::HORIZONTAL_CENTER );
        $objActSheet->getStyle ( 'S2' )->getAlignment ()->setHorizontal ( \PHPExcel_Style_Alignment::HORIZONTAL_CENTER );
        $objActSheet->getStyle ( 'T2' )->getAlignment ()->setHorizontal ( \PHPExcel_Style_Alignment::HORIZONTAL_CENTER );
        $objActSheet->getStyle ( 'U2' )->getAlignment ()->setHorizontal ( \PHPExcel_Style_Alignment::HORIZONTAL_CENTER );
        $objActSheet->getStyle ( 'V2' )->getAlignment ()->setHorizontal ( \PHPExcel_Style_Alignment::HORIZONTAL_CENTER );
        $objActSheet->getStyle ( 'W2' )->getAlignment ()->setHorizontal ( \PHPExcel_Style_Alignment::HORIZONTAL_CENTER );
        $objActSheet->getStyle ( 'X2' )->getAlignment ()->setHorizontal ( \PHPExcel_Style_Alignment::HORIZONTAL_CENTER );
        $objActSheet->getStyle ( 'Y2' )->getAlignment ()->setHorizontal ( \PHPExcel_Style_Alignment::HORIZONTAL_CENTER );
        $objActSheet->getStyle ( 'Z2' )->getAlignment ()->setHorizontal ( \PHPExcel_Style_Alignment::HORIZONTAL_CENTER );
        $objActSheet->getStyle ( 'AA2' )->getAlignment ()->setHorizontal ( \PHPExcel_Style_Alignment::HORIZONTAL_CENTER );
        $objActSheet->getStyle ( 'AB2' )->getAlignment ()->setHorizontal ( \PHPExcel_Style_Alignment::HORIZONTAL_CENTER );

        $objActSheet->setCellValue ( "A1", '实物交易订单导出' );
        $objActSheet->setCellValue ( "A2", '来源单号' );
        $objActSheet->setCellValue ( "B2", '下单时间' );
        $objActSheet->setCellValue ( "C2", '计划发货日期' );
        $objActSheet->setCellValue ( "D2", '订单号' );
        $objActSheet->setCellValue ( "E2", '买家电话' );
        $objActSheet->setCellValue ( "F2", '买家昵称' );
        $objActSheet->setCellValue ( "G2", '买家姓名' );
        $objActSheet->setCellValue ( "H2", '买家留言' );
        $objActSheet->setCellValue ( "I2", '卖家留言' );
        $objActSheet->setCellValue ( "J2", '收货人' );
        $objActSheet->setCellValue ( "K2", '省份' );
        $objActSheet->setCellValue ( "L2", '市' );
        $objActSheet->setCellValue ( "M2", '区' );
        $objActSheet->setCellValue ( "N2", '收货人地址' );
        $objActSheet->setCellValue ( "O2", '收货人邮编' );
        $objActSheet->setCellValue ( "P2", '收货人手机' );
        $objActSheet->setCellValue ( "Q2", '收货人电话' );
        $objActSheet->setCellValue ( "R2", '发票类型' );
        $objActSheet->setCellValue ( "S2", '发票抬头' );
        $objActSheet->setCellValue ( "T2", '支付方式' );
        $objActSheet->setCellValue ( "U2", '订单总金额' );
        $objActSheet->setCellValue ( "V2", '已收金额' );
        $objActSheet->setCellValue ( "W2", '保价金额' );
        $objActSheet->setCellValue ( "X2", '在线发货' );
        $objActSheet->setCellValue ( "Y2", '网店名称' );
        $objActSheet->setCellValue ( "Z2", '承运商编码' );
        $objActSheet->setCellValue ( "AA2", '运单号' );
        $objActSheet->setCellValue ( "AB2", '是否打印运单' );

        // end set excel style
        $k = 3;
        $m_i = 0;
        foreach ( $orderListToExcel as $index => $order ) {
            //收件人
            $recuver_name = $order['extend_order_common']['reciver_name'];
            $preg = "/[^a-zA-Z0-9\p{Han}]/u";
            $recuver_name = preg_replace($preg, '', $recuver_name);
            //买家电话
            $mobile = preg_replace('/\D/', '', $order ['buyer_phone']);
            //收件人地址
            $sub=$order ['extend_order_common'] ['reciver_info'] ['area'];
            $zhixiashi=array('北京','上海','重庆','天津',"北京市",'天津市','重庆市','上海市',"北京省",'天津省','重庆省','上海省');
            $distriction=explode(" ",$sub);
            if(!empty($sub)&&$sub!==NULL) {
                if (!empty($distriction[0]) && strpos($distriction[0], '省')==false) $distriction[0] .= "省";
                if (!empty($distriction[1]) && strpos($distriction[1], '市') == false) $distriction[1] .= "市";
                if (!empty($distriction[2]) && strpos($distriction[2], '区') ==false) $distriction[2] .= "区";
                if (in_array($distriction[0], $zhixiashi)) unset($distriction[0]);
            }else{
                $distriction= array();
            }

            foreach ( $order ['extend_order_goods'] as $i => $goodes ) {
                $m_i ++;
                $objActSheet->setCellValue ( "A" . $k, ' '. date('Ymd').sprintf("%06d",$m_i) );
                $objActSheet->setCellValue ( "B" . $k, date('Y-m-d H:i:s', $order['add_time']) );
                $objActSheet->setCellValue ( "C" . $k, '' );
                $objActSheet->setCellValue ( "D" . $k, ' ' . $order ['order_sn'] );
                $objActSheet->setCellValue ( "E" . $k, ' ' . $mobile );
                $objActSheet->setCellValue ( "F" . $k, '' );
                $objActSheet->setCellValue ( "G" . $k, $order['buyer_name'] );
                $objActSheet->setCellValue ( "H" . $k, $order['extend_order_common'] ['order_message'] .!empty($order['extend_order_common']['distribution_channel']) ? "【".$order['extend_order_common']['distribution_channel']."】":'' );
                $objActSheet->setCellValue ( "I" . $k, '' );
                $objActSheet->setCellValue ( "J" . $k, $recuver_name );
                $objActSheet->setCellValue ( "K" . $k, $distriction[0] );
                $objActSheet->setCellValue ( "L" . $k, $distriction[1] );
                $objActSheet->setCellValue ( "M" . $k, $distriction[2] );
                $objActSheet->setCellValue ( "N" . $k, $order ['extend_order_common']['reciver_info']['address'] );
                $objActSheet->setCellValue ( "O" . $k, '' );
                $objActSheet->setCellValue ( "P" . $k, $order ['extend_order_common']['reciver_info']['phone'] );
                $objActSheet->setCellValue ( "Q" . $k, '' );
                $objActSheet->setCellValue ( "R" . $k, empty($order['extend_order_common']['invoice_info']['类型']) ? '' : ($order['extend_order_common']['invoice_info']['类型'] == '普通发票 ' ? 0 : 1) );
                $objActSheet->setCellValue ( "S" . $k, !empty($order['extend_order_common']['invoice_info']['抬头']) ? $order['extend_order_common']['invoice_info']['抬头'] : '' );
                $objActSheet->setCellValue ( "T" . $k, 0 );
                $objActSheet->setCellValue ( "U" . $k, $goodes['goods_pay_price'] );
                $objActSheet->setCellValue ( "V" . $k, ($order ['order_state'] >= ORDER_STATE_PAY) ? $goodes['goods_pay_price'] : 0 );
                $objActSheet->setCellValue ( "W" . $k, '' );
                $objActSheet->setCellValue ( "X" . $k, 0 );
                $objActSheet->setCellValue ( "Y" . $k, '' );
                $objActSheet->setCellValue ( "Z" . $k, '' );
                $objActSheet->setCellValue ( "AA" . $k, '' );
                $objActSheet->setCellValue ( "AB" . $k, 1 );
                $k ++;
            }
        }

        $objExcel->createSheet();
        $objExcel->setactivesheetindex(1);
        $objActSheet = $objExcel->getActiveSheet ();
        $objActSheet->setTitle ( '订单明细信息' );
        $objActSheet->mergeCells ( 'A1:H1' );
        $objActSheet->getStyle ( 'A1' )->getAlignment ()->setHorizontal ( \PHPExcel_Style_Alignment::HORIZONTAL_CENTER );
        $objActSheet->getStyle ( 'A1' )->getFont ()->setSize ( 15 );
        $objActSheet->getStyle ( 'A1' )->getFont ()->setBold ( true );

        $objActSheet->getColumnDimension ( 'A' )->setWidth ( 22 );
        $objActSheet->getColumnDimension ( 'B' )->setWidth ( 10 );
        $objActSheet->getColumnDimension ( 'C' )->setWidth ( 15 );
        $objActSheet->getColumnDimension ( 'D' )->setWidth ( 15 );
        $objActSheet->getColumnDimension ( 'E' )->setWidth ( 25 );
        $objActSheet->getColumnDimension ( 'F' )->setWidth ( 25 );
        $objActSheet->getColumnDimension ( 'G' )->setWidth ( 25 );
        $objActSheet->getColumnDimension ( 'H' )->setWidth ( 25 );
        $objActSheet->getStyle ( 'A2' )->getAlignment ()->setHorizontal ( \PHPExcel_Style_Alignment::HORIZONTAL_CENTER );
        $objActSheet->getStyle ( 'B2' )->getAlignment ()->setHorizontal ( \PHPExcel_Style_Alignment::HORIZONTAL_CENTER );
        $objActSheet->getStyle ( 'C2' )->getAlignment ()->setHorizontal ( \PHPExcel_Style_Alignment::HORIZONTAL_CENTER );
        $objActSheet->getStyle ( 'D2' )->getAlignment ()->setHorizontal ( \PHPExcel_Style_Alignment::HORIZONTAL_CENTER );
        $objActSheet->getStyle ( 'E2' )->getAlignment ()->setHorizontal ( \PHPExcel_Style_Alignment::HORIZONTAL_CENTER );
        $objActSheet->getStyle ( 'F2' )->getAlignment ()->setHorizontal ( \PHPExcel_Style_Alignment::HORIZONTAL_CENTER );
        $objActSheet->getStyle ( 'G2' )->getAlignment ()->setHorizontal ( \PHPExcel_Style_Alignment::HORIZONTAL_CENTER );
        $objActSheet->getStyle ( 'H2' )->getAlignment ()->setHorizontal ( \PHPExcel_Style_Alignment::HORIZONTAL_CENTER );

        $objActSheet->setCellValue ( "A1", '实物交易订单导出' );
        $objActSheet->setCellValue ( "A2", '来源单号' );
        $objActSheet->setCellValue ( "B2", '货号' );
        $objActSheet->setCellValue ( "C2", '商品编码' );
        $objActSheet->setCellValue ( "D2", '库位' );
        $objActSheet->setCellValue ( "E2", '批次号' );
        $objActSheet->setCellValue ( "F2", '商品名称' );
        $objActSheet->setCellValue ( "G2", '商品单价' );
        $objActSheet->setCellValue ( "H2", '商品数量' );
        $objActSheet->setCellValue ( "I2", '金额小计' );
        $k = 3;
        $m_i = 0;
        foreach ( $orderListToExcel as $index => $order ) {
            foreach ( $order ['extend_order_goods'] as $i => $goodes ) {
                $m_i ++;
                $objActSheet->setCellValue ( "A" . $k, " ". date('Ymd').sprintf("%06d",$m_i) );
                //$objActSheet->setCellValue ( "A" . $k, " " . $order ['order_sn'] );
                $objActSheet->setCellValue ( "B" . $k, "" );
                $objActSheet->setCellValue ( "C" . $k, $goodes['goods_id'] );
                $objActSheet->setCellValue ( "D" . $k, "" );
                $objActSheet->setCellValue ( "E" . $k, "" );
                $objActSheet->setCellValue ( "F" . $k, $goodes['goods_name'] );
                $objActSheet->setCellValue ( "G" . $k, $goodes['goods_price'] );
                $objActSheet->setCellValue ( "H" . $k, $goodes['goods_num'] );
                $objActSheet->setCellValue ( "I" . $k, bcmul($goodes['goods_price'], $goodes['goods_num'], 2));
                $k ++;
            }
        }
        $this->_prepareOrder($orderListToExcel);
        ob_end_clean();
        // 输出excel信息
        $outfile = '实物交易订单' . date ( 'Y-m-d' ) . '.xlsx';
        header ( "Content-Type: application/force-download" );
        header ( "Content-Type: application/octet-stream" );
        header ( "Content-Type: application/download" );
        header ( 'Content-Disposition:inline;filename="' . $outfile . '"' );
        header ( "Content-Transfer-Encoding: binary" );
        header ( "Cache-Control: must-revalidate, post-check=0, pre-check=0" );
        header ( "Pragma: no-cache" );
        $objWriter->save ( 'php://output' );
        exit ();
    }

    private function _prepareOrder($orderList){
        $ids = array();
        foreach ($orderList as $order){
            if($order['order_state'] == ORDER_STATE_PAY){
                $ids[] = $order['order_id'];
            }
        }
        if(empty($ids)) return;
        /** @var orderModel $orderModel */
        $orderModel = Model('order');
        $orderModel->editOrder(array('order_state'=>ORDER_STATE_PREPARE),array('order_id'=>array('in',$ids),'order_state'=>ORDER_STATE_PAY));
    }




	/**
	 * 卖家订单详情
	 *
	 */
	public function show_orderOp() {
		Language::read('member_member_index');
		$order_id = intval($_GET['order_id']);
		if ($order_id <= 0) {
			showMessage(Language::get('wrong_argument'), '', 'html', 'error');
		}
		$model_order = Model('order');
		$condition = array();
		$condition['order_id'] = $order_id;
		$condition['store_id'] = $_SESSION['store_id'];
		$order_info = $model_order -> getOrderInfo($condition, array('order_common', 'order_goods', 'member'));
		if (empty($order_info)) {
			showMessage(Language::get('store_order_none_exist'), '', 'html', 'error');
		}

		//取得订单其它扩展信息
		$model_order -> getOrderExtendInfo($order_info);

		$model_refund_return = Model('refund_return');
		$order_list = array();
		$order_list[$order_id] = $order_info;
		$order_list = $model_refund_return -> getGoodsRefundList($order_list, 1);
		//订单商品的退款退货显示
		$order_info = $order_list[$order_id];
		$refund_all = $order_info['refund_list'][0];
		if (!empty($refund_all) && $refund_all['seller_state'] < 3) {//订单全部退款商家审核状态:1为待审核,2为同意,3为不同意
			Tpl::output('refund_all', $refund_all);
		}

		//显示锁定中
		$order_info['if_lock'] = $model_order -> getOrderOperateState('lock', $order_info);

		//显示调整费用
		$order_info['if_modify_price'] = $model_order -> getOrderOperateState('modify_price', $order_info);

		//显示取消订单
		$order_info['if_store_cancel'] = $model_order -> getOrderOperateState('store_cancel', $order_info);

		//显示发货
		$order_info['if_store_send'] = $model_order -> getOrderOperateState('store_send', $order_info);

		//显示物流跟踪
		$order_info['if_deliver'] = $model_order -> getOrderOperateState('deliver', $order_info);

		//显示系统自动取消订单日期
		if ($order_info['order_state'] == ORDER_STATE_NEW) {
			$order_info['order_cancel_day'] = $order_info['add_time'] + ORDER_AUTO_CANCEL_TIME * 3600;
		}

		//显示快递信息
		if ($order_info['shipping_code'] != '') {
			$express = rkcache('express', true);
			$order_info['express_info']['e_code'] = $express[$order_info['extend_order_common']['shipping_express_id']]['e_code'];
			$order_info['express_info']['e_name'] = $express[$order_info['extend_order_common']['shipping_express_id']]['e_name'];
			$order_info['express_info']['e_url'] = $express[$order_info['extend_order_common']['shipping_express_id']]['e_url'];
		}

		//显示系统自动收获时间
		if ($order_info['order_state'] == ORDER_STATE_SEND) {
			$order_info['order_confirm_day'] = $order_info['delay_time'] + ORDER_AUTO_RECEIVE_DAY * 24 * 3600;
		}

		//取得订单操作日志
		$order_log_list = $model_order -> getOrderLogList(array('order_id' => $order_info['order_id']), 'log_id asc');
		Tpl::output('order_log_list', $order_log_list);

		//如果订单已取消，取得取消原因、时间，操作人
		if ($order_info['order_state'] == ORDER_STATE_CANCEL) {
			$last_log = end($order_log_list);
			if ($last_log['log_orderstate'] == ORDER_STATE_CANCEL) {
				$order_info['close_info'] = $last_log;
			}
		}
		//查询消费者保障服务
		if (C('contract_allow') == 1) {
			$contract_item = Model('contract') -> getContractItemByCache();
		}
		foreach ($order_info['extend_order_goods'] as $value) {
			$value['image_60_url'] = cthumb($value['goods_image'], 60, $value['store_id']);
			$value['image_240_url'] = cthumb($value['goods_image'], 240, $value['store_id']);
			$value['goods_type_cn'] = orderGoodsType($value['goods_type']);
			$value['goods_url'] = urlShop('goods', 'index', array('goods_id' => $value['goods_id']));
			//处理消费者保障服务
			if (trim($value['goods_contractid']) && $contract_item) {
				$goods_contractid_arr = explode(',', $value['goods_contractid']);
				foreach ((array)$goods_contractid_arr as $gcti_v) {
					$value['contractlist'][] = $contract_item[$gcti_v];
				}
			}
			if ($value['goods_type'] == 5) {
				$order_info['zengpin_list'][] = $value;
			} else {
				$order_info['goods_list'][] = $value;
			}
		}

		if (empty($order_info['zengpin_list'])) {
			$order_info['goods_count'] = count($order_info['goods_list']);
		} else {
			$order_info['goods_count'] = count($order_info['goods_list']) + 1;
		}

		Tpl::output('order_info', $order_info);

		//发货信息
		if (!empty($order_info['extend_order_common']['daddress_id'])) {
			$daddress_info = Model('daddress') -> getAddressInfo(array('address_id' => $order_info['extend_order_common']['daddress_id']));
			Tpl::output('daddress_info', $daddress_info);
		}

		Tpl::showpage('store_order.show');
	}


	/**
	 * 卖家订单状态操作
	 *
	 */
	public function change_stateOp() {
		$state_type = $_GET['state_type'];
		$order_id = intval($_GET['order_id']);

		$model_order = Model('order');
		$condition = array();
		$condition['order_id'] = $order_id;
		$condition['store_id'] = $_SESSION['store_id'];
		$order_info = $model_order -> getOrderInfo($condition);

		//取得其它订单类型的信息
		$model_order -> getOrderExtendInfo($order_info);

		if ($_GET['state_type'] == 'order_cancel') {
			$result = $this -> _order_cancel($order_info, $_POST);
		} elseif ($_GET['state_type'] == 'modify_price') {
			$result = $this -> _order_ship_price($order_info, $_POST);
		} elseif ($_GET['state_type'] == 'spay_price') {
			$result = $this -> _order_spay_price($order_info, $_POST);
		}

		if (!$result['state']) {
			showDialog($result['msg'], '', 'error', empty($_GET['inajax']) ? '' : 'CUR_DIALOG.close();', 5);
		} else {
			showDialog($result['msg'], 'reload', 'succ', empty($_GET['inajax']) ? '' : 'CUR_DIALOG.close();');
		}
	}

	/**
	 * 取消订单
	 * @param unknown $order_info
	 */
	private function _order_cancel($order_info, $post) {
		$model_order = Model('order');
		$logic_order = Logic('order');
		if (!chksubmit()) {
			Tpl::output('order_info', $order_info);
			Tpl::output('order_id', $order_info['order_id']);
			Tpl::showpage('store_order.cancel', 'null_layout');
			exit();
		} else {
			$if_allow = $model_order -> getOrderOperateState('store_cancel', $order_info);
			if (!$if_allow) {
				return callback(false, '无权操作');
			}
			if (TIMESTAMP - 86400 < $order_info['api_pay_time']) {
				$_hour = ceil(($order_info['api_pay_time'] + 86400 - TIMESTAMP) / 3600);
				return callback(false, '该订单曾尝试使用第三方支付平台支付，须在' . $_hour . '小时以后才可取消');

			}
			$msg = $post['state_info1'] != '' ? $post['state_info1'] : $post['state_info'];
			if ($order_info['order_type'] == 2) {
				//预定订单
				return Logic('order_book') -> changeOrderStateCancel($order_info, 'seller', $_SESSION['seller_name'], $msg);
			} else {
				$cancel_condition = array();
				if ($order_info['payment_code'] != 'offline') {
					$cancel_condition['order_state'] = ORDER_STATE_NEW;
				}
				return $logic_order -> changeOrderStateCancel($order_info, 'seller', $_SESSION['seller_name'], $msg, true, $cancel_condition);
			}
		}
	}

	/**
	 * 修改运费
	 * @param unknown $order_info
	 */
	private function _order_ship_price($order_info, $post) {
		$model_order = Model('order');
		$logic_order = Logic('order');
		if (!chksubmit()) {
			Tpl::output('order_info', $order_info);
			Tpl::output('order_id', $order_info['order_id']);
			Tpl::showpage('store_order.edit_price', 'null_layout');
			exit();
		} else {
			$if_allow = $model_order -> getOrderOperateState('modify_price', $order_info);
			if (!$if_allow) {
				return callback(false, '无权操作');
			}
			return $logic_order -> changeOrderShipPrice($order_info, 'seller', $_SESSION['seller_name'], $post['shipping_fee']);
		}

	}

	/**
	 * 修改商品价格
	 * @param unknown $order_info
	 */
	private function _order_spay_price($order_info, $post) {
		$model_order = Model('order');
		$logic_order = Logic('order');
		if (!chksubmit()) {
			Tpl::output('order_info', $order_info);
			Tpl::output('order_id', $order_info['order_id']);
			Tpl::showpage('store_order.edit_spay_price', 'null_layout');
			exit();
		} else {
			$if_allow = $model_order -> getOrderOperateState('spay_price', $order_info);
			if (!$if_allow) {
				return callback(false, '无权操作');
			}
			return $logic_order -> changeOrderSpayPrice($order_info, 'seller', $_SESSION['member_name'], $post['goods_amount']);
		}
	}

	/**
	 * 打印发货单
	 */
	public function order_printOp() {
		Language::read('member_printorder');

		$order_id = intval($_GET['order_id']);
		if ($order_id <= 0) {
			showMessage(Language::get('wrong_argument'), '', 'html', 'error');
		}
		$order_model = Model('order');
		$condition['order_id'] = $order_id;
		$condition['store_id'] = $_SESSION['store_id'];
		$order_info = $order_model -> getOrderInfo($condition, array('order_common', 'order_goods'));
		if (empty($order_info)) {
			showMessage(Language::get('member_printorder_ordererror'), '', 'html', 'error');
		}
		Tpl::output('order_info', $order_info);

		//卖家信息
		$model_store = Model('store');
		$store_info = $model_store -> getStoreInfoByID($order_info['store_id']);
		if (!empty($store_info['store_label'])) {
			if (file_exists(BASE_UPLOAD_PATH . DS . ATTACH_STORE . DS . $store_info['store_label'])) {
				$store_info['store_label'] = UPLOAD_SITE_URL . DS . ATTACH_STORE . DS . $store_info['store_label'];
			} else {
				$store_info['store_label'] = '';
			}
		}
		if (!empty($store_info['store_stamp'])) {
			if (file_exists(BASE_UPLOAD_PATH . DS . ATTACH_STORE . DS . $store_info['store_stamp'])) {
				$store_info['store_stamp'] = UPLOAD_SITE_URL . DS . ATTACH_STORE . DS . $store_info['store_stamp'];
			} else {
				$store_info['store_stamp'] = '';
			}
		}
		Tpl::output('store_info', $store_info);

		//订单商品
		$model_order = Model('order');
		$condition = array();
		$condition['order_id'] = $order_id;
		$condition['store_id'] = $_SESSION['store_id'];
		$goods_new_list = array();
		$goods_all_num = 0;
		$goods_total_price = 0;
		if (!empty($order_info['extend_order_goods'])) {
			$goods_count = count($order_goods_list);
			$i = 1;
			foreach ($order_info['extend_order_goods'] as $k => $v) {
				$v['goods_name'] = str_cut($v['goods_name'], 100);
				$goods_all_num += $v['goods_num'];
				$v['goods_all_price'] = ncPriceFormat($v['goods_num'] * $v['goods_price']);
				$goods_total_price += $v['goods_all_price'];
				$goods_new_list[ceil($i / 4)][$i] = $v;
				$i++;
			}
		}
		//优惠金额
		$promotion_amount = $goods_total_price - $order_info['goods_amount'];
		//运费
		$order_info['shipping_fee'] = $order_info['shipping_fee'];
		Tpl::output('promotion_amount', $promotion_amount);
		Tpl::output('goods_all_num', $goods_all_num);
		Tpl::output('goods_total_price', ncPriceFormat($goods_total_price));
		Tpl::output('goods_list', $goods_new_list);
		Tpl::showpage('store_order.print', "null_layout");
	}

	/**
	 * 用户中心右边，小导航
	 *
	 * @param string    $menu_type  导航类型
	 * @param string    $menu_key   当前导航的menu_key
	 * @return
	 */
	private function profile_menu($menu_type = '', $menu_key = '') {
		Language::read('member_layout');
		switch ($menu_type) {
			case 'list' :
				$menu_array = array(
				    array('menu_key' => 'store_order', 'menu_name' => Language::get('nc_member_path_all_order'), 'menu_url' => 'index.php?act=store_order'),
                    array('menu_key' => 'state_new', 'menu_name' => Language::get('nc_member_path_wait_pay'), 'menu_url' => 'index.php?act=store_order&op=index&state_type=state_new'),
                    array('menu_key' => 'state_pay', 'menu_name' => '已支付', 'menu_url' => 'index.php?act=store_order&op=store_order&state_type=state_pay'),
                    array('menu_key' => 'state_prepare', 'menu_name' => '备货中', 'menu_url' => 'index.php?act=store_order&op=store_order&state_type=state_prepare'),
                    array('menu_key' => 'state_notakes', 'menu_name' => '待自提', 'menu_url' => 'index.php?act=store_order&op=store_order&state_type=state_notakes'),
                    array('menu_key' => 'state_send', 'menu_name' => Language::get('nc_member_path_sent'), 'menu_url' => 'index.php?act=store_order&op=index&state_type=state_send'),
                    array('menu_key' => 'state_success', 'menu_name' => Language::get('nc_member_path_finished'), 'menu_url' => 'index.php?act=store_order&op=index&state_type=state_success'),
                    array('menu_key' => 'state_cancel', 'menu_name' => Language::get('nc_member_path_canceled'), 'menu_url' => 'index.php?act=store_order&op=index&state_type=state_cancel'), );
				break;
		}
		Tpl::output('member_menu', $menu_array);
		Tpl::output('menu_key', $menu_key);
	}
	
	/**
	 * 修改物流
	 */
    public function edit_deliverOp() {
    	$order_id = intval($_GET['order_id']);
    	if ($order_id <= 0){
    		showMessage(Language::get('wrong_argument'),'','html','error');
    	}
    	$model_order = Model('order');
    	$condition = array();
    	$condition['order_id'] = $order_id;
    	$condition['store_id'] = $_SESSION['store_id'];
    	$order_info = $model_order->getOrderInfo($condition,array('order_common','order_goods'));
    	Tpl::output('order_info',$order_info);
    	
    	$express_list  = rkcache('express',true);
    	
    	if ($_POST){
    		$shipping_code = trim( $_POST['shipping_code'] ) ;
    		$express_id = intval($_POST['express_id']) ;
    		if( !preg_match("/^[0-9a-zA-Z]+$/i",   $shipping_code ) ) {
    			die( json_encode(array('status' => 'false', 'msg' => '快递单号只能包含字母和数字')) ) ;
    		}
    		
    		$status = 'true' ;
    		$res = Model()->table('orders')->where(array('order_id' => $order_id))->update(array('shipping_code' => $shipping_code));
    		if( !$res ) $status = 'false' ;
    		$res = Model()->table('order_common')->where(array('order_id' => $order_id))->update( array('shipping_express_id' => $express_id) ) ;
    		if( !$res ) $status = 'false' ;
    		
    		//推送分销
    		$model_order -> setOrderSend($order_info , $express_id , $shipping_code) ;
    		
    		$return = array(
    				'status' => $status,
    				'e_name' => "<a href=\"{$express_list[$express_id]['e_url']}\" target=\"_blank\">{$express_list[$express_id]['e_name']}</a>"
    		) ;
    		die( json_encode($return) ) ;
    	}

        //如果是自提订单，只保留自提快递公司
        if ($order_info['extend_order_common']['reciver_info']['dlyp'] != '') {
            foreach ($express_list as $k => $v) {
                if ($v['e_zt_state'] == '0') unset($express_list[$k]);
            }
            $my_express_list = array_keys($express_list);
        } else {
            //快递公司
            $my_express_list = Model()->table('store_extend')->getfby_store_id($_SESSION['store_id'],'express');
            if (!empty($my_express_list)){
                $my_express_list = explode(',',$my_express_list);
            }
        }

        Tpl::output('my_express_list',$my_express_list);
        Tpl::output('express_list',$express_list);
        Tpl::output('order_id', $_GET['order_id']);
        Tpl::showpage('edit_deliver','null_layout');
    }
    public function bill_remarkOp() {
    	$order_id = intval($_GET['order_id']);
    	if ($order_id <= 0){
    		showMessage(Language::get('wrong_argument'),'','html','error');
    	}
    	$model_order = Model('order');
    	$condition = array();
    	$condition['order_id'] = $order_id;
    	$condition['store_id'] = $_SESSION['store_id'];
    	$order_info = $model_order->getOrderInfo($condition,array('order_common','order_goods'));
    	Tpl::output('order_info',$order_info);

    	if ($_POST){
    		$bill_remark = trim( $_POST['bill_remark'] ) ;
    		$status = 'true' ;

    		$res = $model_order->table('order_common')->where(array('order_id' => $order_id))->update( array('bill_remark' => $bill_remark) ) ;
    		if( $res ){
                showMessage('更新成功','','html','success');
            }else
            showMessage('更新失败','','html','error');
    		die( '' ) ;
    	}

        Tpl::output('order_id', $_GET['order_id']);
        Tpl::showpage('store_order.bill_remark','null_layout');
    }
}
