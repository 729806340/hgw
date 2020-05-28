<?php
/**
 * 发货
 *
 *
 *
 * * @汉购网 (c) 2015-2018 ShopWWI Inc. (http://www.hangowa.com)
 * @license    http://www.shopwwi.c om
 * @link       交流群号：
 * @since      汉购网提供技术支持 授权请购买shopnc授权
 */



defined('ByShopWWI') or exit('Access Invalid!');

class store_deliverControl extends BaseSellerControl {

    private $is_fx_send;  //是否允许按照分销订单号发货

    public function __construct() {
        parent::__construct();
        Language::read('member_store_index,deliver');
        $this->is_fx_send = in_array($_SESSION['store_id'] , array(10,15,48,138,171,174,213))?1:0;
        Tpl::output('is_fx_send' , $this->is_fx_send);
    }
    /**
     * 上传批量发货文件 Excel
     */
    public function uploadOp()
    {
    	set_time_limit(0);
        if(!empty($_POST)){
        	$data = array();
            $file	= $_FILES['file'];
            /**
             * 上传错误
             */
            if ($file['error'] > 0) {
                //showMessage('文件上传出错', '', 'html', 'error');
                $data['state'] = false;
                $data['msg'] = '文件上传错误';
                echo json_encode($data);
                die();
            }
            /**
             * 上传文件存在判断
             */
            if(empty($file['name'])){
                //showMessage('请选择上传文件','','html','error');
            	$data['state'] = false;
            	$data['msg'] = '请选择上传文件';
            	echo json_encode($data);
            	die();
            }
            /**
             * 文件来源判定
             */
            if(!is_uploaded_file($file['tmp_name'])){
                //showMessage('文件不合法','','html','error');
            	$data['state'] = false;
            	$data['msg'] = '文件不合法';
            	echo json_encode($data);
            	die();
            }
            /**
             * 文件类型判定
             */
            $file_name_array	= explode('.',$file['name']);
            $curFileType = $file_name_array[count($file_name_array) - 1];
            if (!in_array($curFileType, array('csv', 'xls', 'xlsx'))) {
                //showMessage('文件类型不合法'.$file_name_array[count($file_name_array)-1],'','html','error');
            	$data['state'] = false;
            	$data['msg'] = '请上传csv、xls、xlsx文件';
            	echo json_encode($data);
            	die();
            }
            /**
             * 文件大小判定
             */
            if($file['size'] > intval(ini_get('upload_max_filesize'))*1024*1024){
                //showMessage('文件过大','','html','error');
            	$data['state'] = false;
            	$data['msg'] = '文件大小不可以超过'.ini_get(upload_max_filesize)."M";
            	echo json_encode($data);
            	die();
            }
            /**
             * 开始上传
             */
            $dir = BASE_SHIP_EXCELPATH.DS.$_SESSION['store_id'].DS;
            if(!is_dir($dir)){
                @mkdir(iconv("UTF-8", "GBK", $dir), 0777, true);
            }
            $fileName = $dir.date('Y').date('m').date('d').time().'.'.$curFileType;
            if (move_uploaded_file($file['tmp_name'], $fileName)) {
                /** @var orderModel $model */
                $model = Model('order');
                $result = $model->bulkShipment($fileName);
                if(empty($result['state'])&&$result['state']==false){
                	$data['state'] = false;
                	$data['msg'] = $result['msg'];
                	echo json_encode($data);
                	die();
               }
               $data['state'] = true;
//               $data['result'] = $result;
               $current_time=uniqid();
               $key_name=md5('feedback'.$current_time);
               $data['key_name']=$key_name;
               $data['msg']='您已经成功完成'.$result['succNum'].'条订单出货，点击“确定”，下载反馈信息表!';
               wkcache($key_name,serialize($result['data']),360);
               echo json_encode($data);
               die();
            } else {
                $data['state'] = false;
                $data['msg'] = '文件权限不够';
                echo json_encode($data);
                die();
            }
        }
    }

    public function upload_fxOp()
    {
        set_time_limit(0);
        if(!empty($_POST)){
            $data = array();
            $file	= $_FILES['file'];
            /**
             * 上传错误
             */
            if ($file['error'] > 0) {
                //showMessage('文件上传出错', '', 'html', 'error');
                $data['state'] = false;
                $data['msg'] = '文件上传错误';
                echo json_encode($data);
                die();
            }
            /**
             * 上传文件存在判断
             */
            if(empty($file['name'])){
                //showMessage('请选择上传文件','','html','error');
                $data['state'] = false;
                $data['msg'] = '请选择上传文件';
                echo json_encode($data);
                die();
            }
            /**
             * 文件来源判定
             */
            if(!is_uploaded_file($file['tmp_name'])){
                //showMessage('文件不合法','','html','error');
                $data['state'] = false;
                $data['msg'] = '文件不合法';
                echo json_encode($data);
                die();
            }
            /**
             * 文件类型判定
             */
            $file_name_array	= explode('.',$file['name']);
            $curFileType = $file_name_array[count($file_name_array) - 1];
            if (!in_array($curFileType, array('csv', 'xls', 'xlsx'))) {
                //showMessage('文件类型不合法'.$file_name_array[count($file_name_array)-1],'','html','error');
                $data['state'] = false;
                $data['msg'] = '请上传csv、xls、xlsx文件';
                echo json_encode($data);
                die();
            }
            /**
             * 文件大小判定
             */
            if($file['size'] > intval(ini_get('upload_max_filesize'))*1024*1024){
                //showMessage('文件过大','','html','error');
                $data['state'] = false;
                $data['msg'] = '文件大小不可以超过'.ini_get(upload_max_filesize)."M";
                echo json_encode($data);
                die();
            }
            /**
             * 开始上传
             */
            $dir = BASE_SHIP_EXCELPATH.DS.$_SESSION['store_id'].DS;
            if(!is_dir($dir)){
                @mkdir(iconv("UTF-8", "GBK", $dir), 0777, true);
            }
            $fileName = $dir.date('Y').date('m').date('d').time().'.'.$curFileType;
            if (move_uploaded_file($file['tmp_name'], $fileName)) {
                /** @var orderModel $model */
                $model = Model('order');
                $result = $model->shipMoreByFxorderId($fileName);
                if(!empty($result['state'])){
                    $data['state'] = false;
                    $data['msg'] = $result['msg'];
                    echo json_encode($data);
                    die();
                }
                $data['state'] = true;
//                $data['result'] = $result;
                $current_time=uniqid();
                $key_name=md5('feedback'.$current_time);
                $data['key_name']=$key_name;
                $data['msg']='您已经成功完成'.$result['succNum'].'条分销订单出货，点击“确定”，下载反馈信息表!';
                wkcache($key_name,serialize($result['result']),360);
                echo json_encode($data);die();
            }
        }
    }
    /**
     *批量出货反馈信息
     * @param $datas
     */
    public function export_fx_resultOp()
    {
        set_time_limit(0);
        header("Content-type:text/html;charset=utf-8");
        vendor('PHPExcel');
        $objExcel = new \PHPExcel();
        // set document Property
        $objExcel->getActiveSheet()->setTitle('分销批量出货反馈表');
        $objWriter = \PHPExcel_IOFactory::createWriter($objExcel, 'Excel2007');
        $objActSheet = $objExcel->getActiveSheet();
        $key_name=$_GET['key_name'];
        $data=unserialize(rkcache($key_name));
        $col_ini='A';
        $objExcel->getActiveSheet ()->getColumnDimension ( 'A' )->setWidth ( 25 );
        $objExcel->getActiveSheet ()->getColumnDimension ( 'B' )->setWidth ( 15 );
        $objExcel->getActiveSheet ()->getColumnDimension ( 'C' )->setWidth ( 15 );
        $objExcel->getActiveSheet ()->getColumnDimension ( 'D' )->setWidth ( 20 );
        $objExcel->getActiveSheet ()->getColumnDimension ( 'E' )->setWidth ( 70 );
        $objActSheet->setCellValue("A1", '订单号');
        $objActSheet->setCellValue("B1", '渠道');
        $objActSheet->setCellValue("C1", '快递公司');
        $objActSheet->setCellValue("D1", '快递单号');
        $objActSheet->setCellValue("E1", '反馈结果');
        $k=2;
        foreach($data as $ka =>$v){
            $objActSheet->setCellValue("A" . $k, $v[0]);
            $objActSheet->setCellValue("B" . $k, $v[1]);
            $objActSheet->setCellValue("C" . $k, $v[2]);
            $objActSheet->setCellValue("D" . $k, $v[3]);
            $objActSheet->setCellValue("E" . $k, $v['feedback']);
            $k++;
        }
        $outfile =  '分销批量发货反馈表-' . date('Y-m-d') . '.xls';
        // export to exploer
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header('Content-Disposition:inline;filename="' . $outfile . '"');
        header("Content-Transfer-Encoding: binary");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Pragma: no-cache");
        $objWriter->save('php://output');
        exit();
    }

    public function upload_printshipOp(){
        set_time_limit(0);
        $data = array();
        $file	= $_FILES['file'];
        /**
         * 上传错误
         */
        if ($file['error'] > 0) {
            $data['state'] = false;
            $data['msg'] = '文件上传错误';
            echo json_encode($data);
            die();
        }
            /**
             * 上传文件存在判断
             */
        if(empty($file['name'])){
            $data['state'] = false;
            $data['msg'] = '请选择上传文件';
            echo json_encode($data);
            die();
        }
            /**
             * 文件来源判定
             */
        if(!is_uploaded_file($file['tmp_name'])){
            $data['state'] = false;
            $data['msg'] = '文件不合法';
            echo json_encode($data);
            die();
        }
            /**
             * 文件类型判定
             */
        $file_name_array	= explode('.',$file['name']);
        $curFileType = $file_name_array[count($file_name_array) - 1];
        if (!in_array($curFileType, array('csv', 'xls', 'xlsx'))) {
            $data['state'] = false;
            $data['msg'] = '请上传csv、xls、xlsx文件';
            echo json_encode($data);
            die();
        }
            /**
             * 文件大小判定
             */
        if($file['size'] > intval(ini_get('upload_max_filesize'))*1024*1024){
            $data['state'] = false;
            $data['msg'] = '文件大小不可以超过'.ini_get(upload_max_filesize)."M";
            echo json_encode($data);
            die();
        }
            /**
             * 开始上传
             */
        $dir = BASE_SHIP_EXCELPATH.DS.$_SESSION['store_id'].DS;
        if(!is_dir($dir)){
            @mkdir(iconv("UTF-8", "GBK", $dir), 0777, true);
        }
        $fileName = date('Y').date('m').date('d').time().'.'.$curFileType;
        $dir_name = $dir.$fileName;
        if (move_uploaded_file($file['tmp_name'], $dir_name)) {
            $data['state'] = true;
            $data['file_name'] = $fileName;
            $data['dir_name'] = $dir_name;
            die(json_encode($data));
        }
    }
    
    /**
     * 下载发货模板
     */
    
    public function downBatchTemplateOp(){
        header("Content-type:text/html;charset=utf-8");
        $file_name = $_GET['file_type']=='dzmd'?"ElectronicsheetTemplate.csv":"batchDeliveryTemplates.csv";
        $file_name = iconv("utf-8", "gb2312", $file_name);
        $file_sub_path = BASE_UPLOAD_PATH.DS;
        $file_path = $file_sub_path . $file_name;
        // 首先要判断给定的文件存在与否
        if (!file_exists($file_path)) {
            showMessage('未找到信息','','html','error');
        }
        $fp = fopen($file_path, "r");
        $file_size = filesize($file_path);
        // 下载文件需要用到的头
        Header("Content-type: application/octet-stream");
        Header("Accept-Ranges: bytes");
        Header("Accept-Length:" . $file_size);
        Header("Content-Disposition: attachment; filename=" . $file_name);
        $buffer = 1024;
        $file_count = 0;
        // 向浏览器返回数据
        while (!feof($fp) && $file_count < $file_size) {
            $file_con = fread($fp, $buffer);
            $file_count += $buffer;
            echo $file_con;
        }
        fclose($fp);
    }
    /**
     * 发货列表
     *
     */
    public function indexOp() {
        /** @var orderModel $model_order */
        $model_order = Model('order');
        if (!in_array($_GET['state'],array('deliverno','delivering','delivered'))) $_GET['state'] = 'deliverno';
        $order_state = str_replace(array('deliverno','delivering','delivered'),
                array(ORDER_STATE_PAY,ORDER_STATE_SEND,ORDER_STATE_SUCCESS),$_GET['state']);
        $condition = array();
        $condition['store_id'] = $_SESSION['store_id'];
        $condition['order_state'] = $order_state;
        //update by ljq 过滤掉锁定订单
        $condition['order_state'] == ORDER_STATE_PAY and $condition['lock_state'] = 0;
        $condition['order_state'] == ORDER_STATE_PAY and $condition['is_printship']= array('in' , array(0 ,3));
        // 已支付和备货中的均可发货
        if($condition['order_state'] == ORDER_STATE_PAY){
            $condition['order_state'] = array('in',array(ORDER_STATE_PAY,ORDER_STATE_PREPARE));
        }
        if ($_GET['buyer_name'] != '') {
            $condition['buyer_name'] = $_GET['buyer_name'];
        }
        if (preg_match('/^\d{10,20}$/',$_GET['order_sn'])) {
            $condition['order_sn'] = $_GET['order_sn'];
        }
        $if_start_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_start_date']);
        $if_end_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_end_date']);
        $start_unixtime = $if_start_date ? strtotime($_GET['query_start_date']) : null;
        $end_unixtime = $if_end_date ? strtotime($_GET['query_end_date']): null;
        if ($start_unixtime || $end_unixtime) {
            $condition['add_time'] = array('time',array($start_unixtime,$end_unixtime));
        }
        $order_list = $model_order->getOrderList($condition,5,'*','order_id desc','',array('order_goods','order_common','member'));
        foreach ($order_list as $key => $order_info) {
            $order_info['if_can_send'] = $model_order->getOrderOperateState('store_send' , $order_info);
            $order_info['if_can_printship'] = $model_order->getOrderOperateState('print_ship' , $order_info);
            foreach ($order_info['extend_order_goods'] as $value) {
                $value['image_60_url'] = cthumb($value['goods_image'], 60, $value['store_id']);
                $value['image_240_url'] = cthumb($value['goods_image'], 240, $value['store_id']);
                $value['goods_type_cn'] = orderGoodsType($value['goods_type']);
                $value['goods_url'] = urlShop('goods','index',array('goods_id'=>$value['goods_id']));
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
            $order_list[$key] = $order_info;
        }
        Tpl::output('order_list',$order_list);
        Tpl::output('show_page',$model_order->showpage());
        self::profile_menu('deliver',$_GET['state']);
        Tpl::showpage('store_order.deliver');
    }

    public function delivering_printOp(){
        $ship_status = array(
            '0' => '未请求接口',
            '1' => '请求成功',
            '2' => '请求失败',
        );
        $condition = array(
            'store_id' => $_SESSION['store_id'],
        );
        //查询时间
        $if_start_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_start_date']);
        $if_end_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_end_date']);
        $start_unixtime = $if_start_date ? strtotime($_GET['query_start_date']) : null;
        $end_unixtime = $if_end_date ? strtotime($_GET['query_end_date'])+86399: null;
        if($start_unixtime && $end_unixtime){
            $condition['add_time'] = array('between' , array($start_unixtime , $end_unixtime));
        }

        if (preg_match('/^\d{10,20}$/',$_GET['order_sn'])) {
            $condition['order_sn'] = $_GET['order_sn'];
        }
        if(!empty($_GET['express_code'])){
            $condition['express_code'] = $_GET['express_code'];
        }

        $printship_list = Model('print_ship')->getPrintShipLogList($condition ,'*', '', '20', $order = 'id desc');
        $express = Model('express')->getPushExpress();
        $express = array_under_reset($express , 'kdncode');
        $order_ids = array();
        foreach($printship_list as $item =>$value){
            $order_ids[] = $value['order_sn'];
        }

        if(count($order_ids) > 0){
            $condition = array(
                'order_sn' => array('in' , $order_ids),
            );
            $order_ids_arr = Model('order')->getOrderList($condition,'','order_id,order_sn');
            $order_ids_arr = array_under_reset($order_ids_arr ,'order_sn');
            //print_r($order_ids_arr);
            foreach($printship_list as $item =>$value){
                $printship_list[$item]['express_name'] = $express[$value['express_code']]['hgwname'];
                $receiver = json_decode($value['order_info'],true);
                $printship_list[$item]['receiver'] = $receiver['Receiver'];
                $printship_list[$item]['add_time'] = date('Y-m-d H:i' , $value['add_time']);
                $printship_list[$item]['ship_time'] = $value['ship_time']? date('Y-m-d H:i' , $value['ship_time']):'';
                $printship_list[$item]['ship_status'] = $ship_status[$value['ship_status']];
                $printship_list[$item]['order_id'] = $order_ids_arr[$value['order_sn']]['order_id'];
            }
        }

        Tpl::output('show_page' ,  Model('print_ship')->showpage());
        Tpl::output('order_list' , $printship_list);
        Tpl::output('ship_status' , $ship_status);
        Tpl::output('express' , $express);
        self::profile_menu('deliver','delivering_print');
        Tpl::showpage('store_order.deliver_print');
    }
    public function express_traceOp(){
        $trace_status = array(
            '0' => '未推送',
            '1' => '自动推送',
            '2' => '手动推送',
        );
        $condition = array(
            'store_id' => $_SESSION['store_id'],
        );
        //查询时间
        $if_start_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_start_date']);
        $if_end_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_end_date']);
        $start_unixtime = $if_start_date ? strtotime($_GET['query_start_date']) : null;
        $end_unixtime = $if_end_date ? strtotime($_GET['query_end_date'])+86399: null;
        if($start_unixtime && $end_unixtime){
            $condition['add_time'] = array('between' , array($start_unixtime , $end_unixtime));
        }
        if(in_array($_GET['trace_status'],array('0','1','2'))){
            $condition['is_sendfx'] = $_GET['trace_status'];
        }

        if (preg_match('/^\d{10,20}$/',$_GET['order_sn'])) {
            $condition['order_sn'] = $_GET['order_sn'];
        }
        if (!empty($_GET['fx_order_id'])) {
            $condition['fx_order_id'] = $_GET['fx_order_id'];
        }
        if(!empty($_GET['express_code'])){
            $condition['express_code'] = $_GET['express_code'];
        }

        /** @var express_traceModel $traceModel */
        $traceModel = Model('express_trace');
        $traceList = $traceModel->getExpressTraceList($condition,'*',null,20);
        //v($traceModel->getLastSql());
        /** @var expressModel $expressModel */
        $expressModel = Model('express');
        $expressList = $expressModel->getExpressList();
        //v($expressList);
        $expressList = array_under_reset($expressList , 'id');

        /** @var member_fenxiaoModel $fenxiaoModel */
        $fenxiaoModel = Model('member_fenxiao');
        $fenxiaoMember = $fenxiaoModel->getMemberFenxiao();
        $fenxiaoMember = array_under_reset($fenxiaoMember,'member_en_code');
        Tpl::output('fenxiaoMember',$fenxiaoMember);

        Tpl::output('show_page' ,  $traceModel->showpage());
        Tpl::output('traceList' , $traceList);
        Tpl::output('trace_status' , $trace_status);
        Tpl::output('express' , $expressList);
        self::profile_menu('deliver','express_trace');
        Tpl::showpage('store_order.express_trace');
    }
    public function show_traceOp()
    {
        /** @var expressModel $expressModel */
        $expressModel = Model('express');
        /** @var express_traceModel $express_traceModel */
        $express_traceModel = Model('express_trace');
        $trace = $express_traceModel->getExpressTraceInfo(array('et_id'=>$_GET['et_id']));
        $express = $expressModel->getExpressInfo($trace['express_id']);
        $data = $expressModel->get_express_pro($express['e_code'],$trace['shipping_code']);
        Tpl::output('data',$data);
        Tpl::showpage('store_order.express_trace_view','null_layout');
    }
    public function fxorder_sendOp()
    {
        /** @var expressModel $expressModel */
        $expressModel = Model('express');
        /** @var express_traceModel $express_traceModel */
        $express_traceModel = Model('express_trace');
        /** @var orderModel $orderModel */
        $orderModel = Model('order');
        $trace = $express_traceModel->getExpressTraceInfo(array('et_id'=>$_GET['et_id']));
        if($trace['is_sendfx']){
            return showMessage('订单已经推送,请勿重复推送','','html','error');
        }
        $order_info = $orderModel->getOrderInfo(array('order_sn'=>$trace['order_sn']),array('order_common','order_goods'));

        $express_traceModel->beginTransaction();
        $res = $express_traceModel->where(array('et_id'=>$trace['et_id']))->update(array('update_time'=>TIMESTAMP,'is_sendfx'=>2));

        if(!$res){
            $express_traceModel->rollback();
            showMessage('操作失败','','html','error');
        }
        $res=Model("sendorder_record")->insertData($order_info,$trace['express_id'] , $trace['shipping_code']);
        if($res){
            //返回成功
            $express_traceModel->commit();
            showMessage('操作成功','','html','success');
        }else{
            // 返回失败
            $express_traceModel->rollback();
            showMessage('操作失败','','html','error');
        }
    }

    /**
     * 导出电子面单excel模板
     */
    public function excel_printshipOp(){
        $ship_status = array(
            '0' => '未请求接口',
            '1' => '请求成功',
            '2' => '请求失败',
        );
        $condition = array(
            'store_id' => $_SESSION['store_id'],
        );
        //查询时间
        $if_start_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_start_date']);
        $if_end_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_end_date']);
        $start_unixtime = $if_start_date ? strtotime($_GET['query_start_date']) : null;
        $end_unixtime = $if_end_date ? strtotime($_GET['query_end_date']): null;
        if($start_unixtime && $end_unixtime){
            $condition['add_time'] = array('between' , array($start_unixtime , $end_unixtime));
        }

        if (preg_match('/^\d{10,20}$/',$_GET['order_sn'])) {
            $condition['order_sn'] = $_GET['order_sn'];
        }
        if(!empty($_GET['express_code'])){
            $condition['express_code'] = $_GET['express_code'];
        }

        $printship_list = Model('print_ship')->getPrintShipLogList($condition ,'*', '', '20', $order = 'id desc');
        $express = Model('express')->getPushExpress();
        $express = array_under_reset($express , 'kdncode');
        $order_ids = array();
        foreach($printship_list as $item =>$value){
            $order_ids[] = $value['order_sn'];
        }

        if(count($order_ids) > 0){
            $condition = array(
                'order_sn' => array('in' , $order_ids),
            );
            $order_ids_arr = Model('order')->getOrderList($condition,'','order_sn,shipping_code');
            $order_ids_arr = array_under_reset($order_ids_arr ,'order_sn');
            foreach($printship_list as $item =>$value){
                $printship_list[$item]['express_name'] = $express[$value['express_code']]['hgwname'];
                $receiver = json_decode($value['order_info'],true);
                $printship_list[$item]['receiver'] = $receiver['Receiver']['Name'];
                $printship_list[$item]['provinceName'] =$receiver['Receiver']['ProvinceName'];
                $printship_list[$item]['city'] =$receiver['Receiver']['CityName'];
                $printship_list[$item]['area'] =$receiver['Receiver']['ExpAreaName'];
                $printship_list[$item]['mobile'] =$receiver['Receiver']['Mobile'];
                $printship_list[$item]['address'] = $receiver['Receiver']['Address'];
                $printship_list[$item]['ship_status'] = $ship_status[$value['ship_status']];
                $printship_list[$item]['shipping_code'] = $order_ids_arr[$value['order_sn']]['shipping_code'];
            }
        }
        vendor('PHPExcel');
        $objExcel = new \PHPExcel();
        $objExcel->getActiveSheet ()->setTitle ( '电子面单发货单' );
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
        $objExcel->getActiveSheet ()->getColumnDimension ( 'I' )->setWidth ( 20 );
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
        $objWriter = \PHPExcel_IOFactory::createWriter ( $objExcel, 'Excel2007' );
        $objActSheet = $objExcel->getActiveSheet ();
        $key = ord ( "A" );
        $objActSheet->setCellValue ( "A1", '电子面单发货单' );
        $objActSheet->setCellValue ( "A2", '订单号' );
        $objActSheet->setCellValue ( "B2", '物流公司' );
        $objActSheet->setCellValue ( "C2", '运单编号' );
        $objActSheet->setCellValue ( "D2", '收货（省）' );
        $objActSheet->setCellValue ( "E2", '收货（市）' );
        $objActSheet->setCellValue ( "F2", '收货（区）' );
        $objActSheet->setCellValue ( "G2", '收货地址' );
        $objActSheet->setCellValue ( "H2", '收货人' );
        $objActSheet->setCellValue ( "I2", '收货人手机' );
        $k = 2;
        foreach($printship_list as $key=>$value){
            $k++;
            $objActSheet->setCellValue ( "A" . $k, ' ' . $value ['order_sn'] );
            $objActSheet->setCellValue ( "B" . $k, $value ['express_name'] );
            $objActSheet->setCellValue ( "C" . $k, ' ' . $value ['shipping_code']);
            $objActSheet->setCellValue ( "D" . $k, $value['provinceName'] );
            $objActSheet->setCellValue ( "E" . $k, $value['city'] );
            $objActSheet->setCellValue ( "F" . $k, $value['area']);
            $objActSheet->setCellValue ( "G" . $k, $value['address']);
            $objActSheet->setCellValue ( "H" . $k, $value ['receiver'] );
            $objActSheet->setCellValue ( "I" . $k, ' ' .$value ['mobile'] );
        }
        ob_end_clean();
        // 输出excel信息
        $outfile = '电子面单发货单' . date ( 'Y-m-d' ) . '.xlsx';
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
     * 批量上传电子面单
     */
    public function upload_morepringshipOp(){
        $dir_name = $_POST['dir_name'];
        $template_id = intval($_POST['template_id']);
        $model = Model('print_ship');
        $result = $model->setPrintShipMoreLog($dir_name , $template_id);
        die(json_encode($result));
    }

    public function pirntship_moreOp(){
        //获取商家电子面单模板
        $condition = array('store_id'=>$_SESSION['store_id']);
        $template_list = Model('print_ship')->getPrintShipList($condition);
        $express = Model('express')->getPushExpress();
        $express = array_under_reset($express , 'kdncode');
        foreach($template_list as $item =>$value){
            $template_list[$item]['express_name'] = $express[$value['express_code']]['hgwname'];
        }
        Tpl::output('template_list', $template_list);
        Tpl::showpage('store_deliver.more_printship','null_layout');
    }

    /**
     * 发货
     */
    public function sendOp(){
        $order_id = intval($_GET['order_id']);
        if ($order_id <= 0){
            showMessage(Language::get('wrong_argument'),'','html','error');
        }
        $model_order = Model('order');
        $condition = array();
        $condition['order_id'] = $order_id;
        $condition['store_id'] = $_SESSION['store_id'];
        $order_info = $model_order->getOrderInfo($condition,array('order_common','order_goods'));
        $if_allow_send = intval($order_info['lock_state']) || !in_array($order_info['order_state'],array(ORDER_STATE_PAY,ORDER_STATE_SEND, ORDER_STATE_PREPARE));
        if ($if_allow_send) {
            showMessage(Language::get('wrong_argument'),'','html','error');
        }

        if (chksubmit()){
            $logic_order = Logic('order');
            $_POST['reciver_info'] = $this->_get_reciver_info();
            $result = $logic_order->changeOrderSend($order_info, 'seller', $_SESSION['seller_name'], $_POST);
            if (!$result['state']) {
                showDialog($result['msg']."，请重新操作",'index.php?act=store_deliver&op=send&order_id='.$order_id,'error');
            } else {
                showDialog($result['msg'],$_POST['ref_url'],'succ');
            }
        }
        
        Tpl::output('order_info',$order_info);
        //取发货地址
        $model_daddress = Model('daddress');
        if ($order_info['extend_order_common']['daddress_id'] > 0 ){
            $daddress_info = $model_daddress->getAddressInfo(array('address_id'=>$order_info['extend_order_common']['daddress_id']));
        }else{
            //取默认地址
            $daddress_info = $model_daddress->getAddressList(array('store_id'=>$_SESSION['store_id']),'*','is_default desc',1);
            $daddress_info = $daddress_info[0];

            //写入发货地址编号
            $this->_edit_order_daddress($daddress_info['address_id'], $order_id);
        }
        /*echo '<pre>';
        print_r($daddress_info['address_id']);
        die;*/
        Tpl::output('daddress_info',$daddress_info);

        $express_list  = rkcache('express',true);

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
        Tpl::showpage('store_deliver.send');
    }

    /**
     * 编辑收货地址
     * @return boolean
     */
    public function buyer_address_editOp() {
        $order_id = intval($_GET['order_id']);
        if ($order_id <= 0) return false;
        $model_order = Model('order');
        $condition = array();
        $condition['order_id'] = $order_id;
        $condition['store_id'] = $_SESSION['store_id'];
        $order_common_info = $model_order->getOrderCommonInfo($condition);
        if (!$order_common_info) return false;
        $order_common_info['reciver_info'] = @unserialize($order_common_info['reciver_info']);
        Tpl::output('address_info',$order_common_info);

        Tpl::showpage('store_deliver.buyer_address.edit','null_layout');
    }
    /**
     *批量出货反馈信息
     * @param $datas
     */
    public function export_excel_resultOp()
    {
        set_time_limit(0);
        ini_set('memory_limit','4G');
        header("Content-type:text/html;charset=utf-8");
        vendor('PHPExcel');
        $objExcel = new \PHPExcel();
        // set document Property
        $objExcel->getActiveSheet()->setTitle('批量出货反馈表');
        $objExcel->getActiveSheet()
            ->getColumnDimension('A')
            ->setWidth(15);
        $objExcel->getActiveSheet()
            ->getColumnDimension('B')
            ->setWidth(15);
        $objExcel->getActiveSheet()
            ->getColumnDimension('C')
            ->setWidth(15);
        $objExcel->getActiveSheet()
            ->getColumnDimension('D')
            ->setWidth(70);
        $objWriter = \PHPExcel_IOFactory::createWriter($objExcel, 'Excel2007');
        $objActSheet = $objExcel->getActiveSheet();
        $key = ord("A");
        $objActSheet->setCellValue("A1", 'order_id');
        $objActSheet->setCellValue("B1", 'logi_name');
        $objActSheet->setCellValue("C1", 'logi_no');
        $objActSheet->setCellValue("D1", 'remark');
        $objActSheet->setCellValue("E1", '导入反馈结果');
        $k=2;
        $key_name=$_GET['key_name'];
        $data=unserialize(rkcache($key_name));
        foreach($data as $ka =>$v){
            $objActSheet->setCellValue("A" . $k, $v[0]);
            $objActSheet->setCellValue("B" . $k, $v[1]);
            $objActSheet->setCellValue("C" . $k, $v[2]);
            $objActSheet->setCellValue("d" . $k, $v[3]);
            $objActSheet->setCellValue("E" . $k, $v['feedback']);
            $k++;
        }
        $outfile =  '批量发货反馈表-' . date('Y-m-d') . '.xls';
        // export to exploer
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header('Content-Disposition:inline;filename="' . $outfile . '"');
        header("Content-Transfer-Encoding: binary");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Pragma: no-cache");
        $objWriter->save('php://output');
        exit();
    }
    /**
     * 收货地址保存
     */
    public function buyer_address_saveOp() {
        $model_order = Model('order');
        $data = array();
        $data['reciver_name'] = $_POST['reciver_name'];
        $data['reciver_info'] = $this->_get_reciver_info();
        $condition = array();
        $condition['order_id'] = intval($_POST['order_id']);
        $condition['store_id'] = $_SESSION['store_id'];
        $result = $model_order->editOrderCommon($data, $condition);
        if($result) {
            echo 'true';
        } else {
            echo 'flase';
        }
    }

    /**
     * 组合reciver_info
     */
    private function _get_reciver_info() {
        $reciver_info = array(
            'address' => $_POST['reciver_area'] . ' ' . $_POST['reciver_street'],
            'phone' => trim($_POST['reciver_mob_phone'] . ',' . $_POST['reciver_tel_phone'],','),
            'area' => $_POST['reciver_area'],
            'street' => $_POST['reciver_street'],
            'mob_phone' => $_POST['reciver_mob_phone'],
            'tel_phone' => $_POST['reciver_tel_phone'],
            'dlyp' => $_POST['reciver_dlyp']
        );
        return serialize($reciver_info);
    }

    /**
     * 选择发货地址
     * @return boolean
     */
    public function send_address_selectOp() {
        Language::read('deliver');
        $address_list = Model('daddress')->getAddressList(array('store_id'=>$_SESSION['store_id']));
        Tpl::output('address_list',$address_list);
        Tpl::output('order_id', $_GET['order_id']);
        Tpl::showpage('store_deliver.daddress.select','null_layout');
    }

    /**
     * 保存发货地址修改
     */
    public function send_address_saveOp() {
        $result = $this->_edit_order_daddress($_POST['daddress_id'], $_POST['order_id']);
        if($result) {
            echo 'true';
        } else {
            echo 'flase';
        }
    }

    /**
     * 修改发货地址
     */
    private function _edit_order_daddress($daddress_id, $order_id) {
        $model_order = Model('order');
        $data = array();
        $data['daddress_id'] = intval($daddress_id);
        $condition = array();
        $condition['order_id'] = $order_id;
        $condition['store_id'] = $_SESSION['store_id'];
        return $model_order->editOrderCommon($data, $condition);
    }

    /**
     * 物流跟踪
     */
    public function search_deliverOp(){
        Language::read('member_member_index');
        $lang   = Language::getLangContent();

        $order_sn   = $_GET['order_sn'];
        if (!preg_match('/^\d{10,20}$/',$_GET['order_sn'])) showMessage(Language::get('wrong_argument'),'','html','error');
        $model_order    = Model('order');
        $condition['order_sn'] = $order_sn;
        $condition['store_id'] = $_SESSION['store_id'];
        $order_info = $model_order->getOrderInfo($condition,array('order_common','order_goods'));
        if (empty($order_info) || $order_info['shipping_code'] == '') {
            showMessage('未找到信息','','html','error');
        }
        $order_info['state_info'] = orderState($order_info);
        Tpl::output('order_info',$order_info);
        //卖家发货信息
        $daddress_info = Model('daddress')->getAddressInfo(array('address_id'=>$order_info['extend_order_common']['daddress_id']));
        Tpl::output('daddress_info',$daddress_info);

        //取得配送公司代码
        $express = rkcache('express',true);
        Tpl::output('e_code',$express[$order_info['extend_order_common']['shipping_express_id']]['e_code']);
        Tpl::output('e_name',$express[$order_info['extend_order_common']['shipping_express_id']]['e_name']);
        Tpl::output('e_url',$express[$order_info['extend_order_common']['shipping_express_id']]['e_url']);
        Tpl::output('shipping_code',$order_info['shipping_code']);

        self::profile_menu('search','search');
        Tpl::showpage('store_deliver.detail');
    }

    /**
     * 延迟收货
     */
    public function delay_receiveOp(){
        $order_id = intval($_GET['order_id']);
        $model_trade = Model('trade');
        $model_order = Model('order');
        $condition = array();
        $condition['order_id'] = $order_id;
        $condition['store_id'] = $_SESSION['store_id'];
        $condition['lock_state'] = 0;
        $order_info = $model_order->getOrderInfo($condition);

        //取目前系统最晚收货时间
        $delay_time = $order_info['delay_time'] + ORDER_AUTO_RECEIVE_DAY * 3600 * 24;
        if (chksubmit()) {
            $delay_date = intval($_POST['delay_date']);
            if (!in_array($delay_date,array(5,10,15))) {
                showDialog(Language::get('wrong_argument'),'','error',empty($_GET['inajax']) ?'':'CUR_DIALOG.close();');
            }
            $update = $model_order->editOrder(array('delay_time'=>array('exp','delay_time+'.$delay_date*3600*24)),$condition);
            if ($update) {
                //新的最晚收货时间
                $dalay_date = date('Y-m-d H:i:s',$delay_time+$delay_date*3600*24);
                showDialog("成功将最晚收货期限延迟到了".$dalay_date.'&emsp;','','succ',empty($_GET['inajax']) ?'':'CUR_DIALOG.close();',4);
            } else {
                showDialog('延迟失败','','succ',empty($_GET['inajax']) ?'':'CUR_DIALOG.close();');
            }
        } else {
            $order_info['delay_time'] = $delay_time;
            Tpl::output('order_info',$order_info);
            Tpl::showpage('store_deliver.delay_receive','null_layout');
            exit();
        }
    }

    /**
     * 运单打印
     */
    public function waybill_printOp() {
        $order_id = intval($_GET['order_id']);
        if($order_id <= 0) {
            showMessage(L('param_error'));
        }

        $model_order = Model('order');
        $model_store_waybill = Model('store_waybill');
        $model_waybill = Model('waybill');

        $order_info = $model_order->getOrderInfo(array('order_id' => intval($_GET['order_id'])), array('order_common'));

        $store_waybill_list = $model_store_waybill->getStoreWaybillList(array('store_id' => $order_info['store_id']), 'is_default desc');

        $store_waybill_info = $this->_getCurrentWaybill($store_waybill_list, $_GET['store_waybill_id']);
        if(empty($store_waybill_info)) {
            showMessage('请首先绑定打印模板', urlShop('store_waybill', 'waybill_manage'), '', 'error');
        }

        $waybill_info = $model_waybill->getWaybillInfo(array('waybill_id' => $store_waybill_info['waybill_id']));
        if(empty($waybill_info)) {
            showMessage('请首先绑定打印模板', urlShop('store_waybill', 'waybill_manage'), '', 'error');
        }

        //根据订单内容获取打印数据
        $print_info = $model_waybill->getPrintInfoByOrderInfo($order_info);

        //整理打印模板
        $store_waybill_data = unserialize($store_waybill_info['store_waybill_data']);
        foreach ($waybill_info['waybill_data'] as $key => $value) {
            $waybill_info['waybill_data'][$key]['show'] = $store_waybill_data[$key]['show'];
            $waybill_info['waybill_data'][$key]['content'] = $print_info[$key];
        }

        //使用商家自定义的偏移尺寸
        $waybill_info['waybill_pixel_top'] = $store_waybill_info['waybill_pixel_top'];
        $waybill_info['waybill_pixel_left'] = $store_waybill_info['waybill_pixel_left'];

        Tpl::output('waybill_info', $waybill_info);
        Tpl::output('store_waybill_list', $store_waybill_list);
        Tpl::showpage('waybill.print', 'null_layout');
    }

    /**
     * 获取当前打印模板
     */
    private function _getCurrentWaybill($store_waybill_list, $store_waybill_id) {
        if(empty($store_waybill_list)) {
            return false;
        }

        $store_waybill_id = intval($store_waybill_id);

        $store_waybill_info = null;

        //如果指定模板使用指定的模板，未指定使用默认模板
        if($store_waybill_id > 0) {
            foreach ($store_waybill_list as $key => $value) {
                if($store_waybill_id == $value['store_waybill_id']) {
                    $store_waybill_info = $store_waybill_list[$key];
                    break;
                }
            }
        }

        if(empty($store_waybill_info)) {
            $store_waybill_info = $store_waybill_list[0];
        }

        return $store_waybill_info;
    }

    /**
     * 用户中心右边，小导航
     *
     * @param string    $menu_type  导航类型
     * @param string    $menu_key   当前导航的menu_key
     * @return
     */
    private function profile_menu($menu_type,$menu_key='') {
        Language::read('member_layout');
        $menu_array     = array();
        switch ($menu_type) {
            case 'deliver':
                $menu_array = array(
                    array('menu_key'=>'deliverno',          'menu_name'=>Language::get('nc_member_path_deliverno'), 'menu_url'=>'index.php?act=store_deliver&op=index&state=deliverno'),
                    array('menu_key'=>'delivering',         'menu_name'=>Language::get('nc_member_path_delivering'),    'menu_url'=>'index.php?act=store_deliver&op=index&state=delivering'),
                    array('menu_key'=>'delivered',          'menu_name'=>Language::get('nc_member_path_delivered'), 'menu_url'=>'index.php?act=store_deliver&op=index&state=delivered'),
                    array('menu_key'=>'delivering_print', 'menu_name'=>'电子面单列表', 'menu_url'=>'index.php?act=store_deliver&op=delivering_print'),
                );
                if($this->is_fx_send){
                    $menu_array[]=array('menu_key'=>'express_trace', 'menu_name'=>'物流跑单列表', 'menu_url'=>'index.php?act=store_deliver&op=express_trace');
                }
                break;
            case 'search':
                $menu_array = array(
                1=>array('menu_key'=>'nodeliver',           'menu_name'=>Language::get('nc_member_path_deliverno'), 'menu_url'=>'index.php?act=store_deliver&op=index&state=nodeliver'),
                2=>array('menu_key'=>'delivering',          'menu_name'=>Language::get('nc_member_path_delivering'),    'menu_url'=>'index.php?act=store_deliver&op=index&state=delivering'),
                3=>array('menu_key'=>'delivered',       'menu_name'=>Language::get('nc_member_path_delivered'), 'menu_url'=>'index.php?act=store_deliver&op=index&state=delivered'),
                4=>array('menu_key'=>'search',      'menu_name'=>Language::get('nc_member_path_deliver_info'),  'menu_url'=>'###'),
                );
                break;
        }
        Tpl::output('member_menu',$menu_array);
        Tpl::output('menu_key',$menu_key);
    }

    public function upload_remarkOp(){
        set_time_limit(0);
        if(!empty($_POST)){
            $data = array();
            $file	= $_FILES['file'];
            /**
             * 上传错误
             */
            if ($file['error'] > 0) {
                //showMessage('文件上传出错', '', 'html', 'error');
                $data['state'] = false;
                $data['msg'] = '文件上传错误';
                echo json_encode($data);
                die();
            }
            /**
             * 上传文件存在判断
             */
            if(empty($file['name'])){
                //showMessage('请选择上传文件','','html','error');
                $data['state'] = false;
                $data['msg'] = '请选择上传文件';
                echo json_encode($data);
                die();
            }
            /**
             * 文件来源判定
             */
            if(!is_uploaded_file($file['tmp_name'])){
                //showMessage('文件不合法','','html','error');
                $data['state'] = false;
                $data['msg'] = '文件不合法';
                echo json_encode($data);
                die();
            }
            /**
             * 文件类型判定
             */
            $file_name_array	= explode('.',$file['name']);
            $curFileType = $file_name_array[count($file_name_array) - 1];
            if (!in_array($curFileType, array('csv', 'xls', 'xlsx'))) {
                $data['state'] = false;
                $data['msg'] = '请上传csv、xls、xlsx文件';
                echo json_encode($data);
                die();
            }
            /**
             * 文件大小判定
             */
            if($file['size'] > intval(ini_get('upload_max_filesize'))*1024*1024){
                $data['state'] = false;
                $data['msg'] = '文件大小不可以超过'.ini_get(upload_max_filesize)."M";
                echo json_encode($data);
                die();
            }
            /**
             * 开始上传
             */
            $dir = BASE_SHIP_EXCELPATH.DS.$_SESSION['store_id'].DS;
            if(!is_dir($dir)){
                @mkdir(iconv("UTF-8", "GBK", $dir), 0777, true);
            }
            $fileName = $dir.date('Y').date('m').date('d').time().'.'.$curFileType;
            if (move_uploaded_file($file['tmp_name'], $fileName)) {
                /** @var orderModel $model */
                $model = Model('order');
                $result = $model->batchShipRemark($fileName);
                if($result['state']==false){
                    $data['state'] = false;
                    $data['msg'] = $result['msg'];
                    echo json_encode($data);
                    die();
                }
                $data['state'] = true;
                $current_time=uniqid();
                $key_name=md5('feedback'.$current_time);
                $data['key_name']=$key_name;
                $no_exit_num=intval($result['totals'])-intval($result['actualNum']);
                $data['msg']='去掉第一行总计订单：'.$result['totals'].'条,不存在订单数量：'.$no_exit_num.'条，成功备注：'.$result['succNum'].'条，失败总数：'.$result['failNum'].'，点击“确定”，下载反馈信息表!';
                wkcache($key_name,serialize($result['data']),360);
                echo json_encode($data);
                die();
            }
        }
    }

    public function export_excel_shipremarktOp(){
        set_time_limit(0);
        header("Content-type:text/html;charset=utf-8");
        vendor('PHPExcel');
        $objExcel = new \PHPExcel();
        $objExcel->getActiveSheet()->setTitle('批量发货备注反馈表');
        $objExcel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
        $objExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
        $objExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $objWriter = \PHPExcel_IOFactory::createWriter($objExcel, 'Excel2007');
        $objActSheet = $objExcel->getActiveSheet();
        $key = ord("A");
        $objActSheet->setCellValue("A1", 'order_id');
        $objActSheet->setCellValue("B1", 'remark');
        $objActSheet->setCellValue("C1", '备注的状态');
        $k=2;
        $key_name=$_GET['key_name'];
        $data=unserialize(rkcache($key_name));
        foreach($data as $ka =>$v){
            $objActSheet->setCellValue("A" . $k, $v['order_id']."\t");
            $objActSheet->setCellValue("B" . $k, $v['remark']);
            $objActSheet->setCellValue("C" . $k, $v['status']);
            $k++;
        }
        $outfile =  '批量发货备注反馈表-' . date('Y-m-d') . '.xls';
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header('Content-Disposition:inline;filename="' . $outfile . '"');
        header("Content-Transfer-Encoding: binary");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Pragma: no-cache");
        $objWriter->save('php://output');
        exit();
    }

    /*分销订单批量发货备注*/
    public function upload_fenxiao_remarkOp(){
        set_time_limit(0);
        if(!empty($_POST)){
            $data = array();
            $file	= $_FILES['file'];
            /**
             * 上传错误
             */
            if ($file['error'] > 0) {
                //showMessage('文件上传出错', '', 'html', 'error');
                $data['state'] = false;
                $data['msg'] = '文件上传错误';
                echo json_encode($data);
                die();
            }
            /**
             * 上传文件存在判断
             */
            if(empty($file['name'])){
                //showMessage('请选择上传文件','','html','error');
                $data['state'] = false;
                $data['msg'] = '请选择上传文件';
                echo json_encode($data);
                die();
            }
            /**
             * 文件来源判定
             */
            if(!is_uploaded_file($file['tmp_name'])){
                //showMessage('文件不合法','','html','error');
                $data['state'] = false;
                $data['msg'] = '文件不合法';
                echo json_encode($data);
                die();
            }
            /**
             * 文件类型判定
             */
            $file_name_array	= explode('.',$file['name']);
            $curFileType = $file_name_array[count($file_name_array) - 1];
            if (!in_array($curFileType, array('csv', 'xls', 'xlsx'))) {
                $data['state'] = false;
                $data['msg'] = '请上传csv、xls、xlsx文件';
                echo json_encode($data);
                die();
            }
            /**
             * 文件大小判定
             */
            if($file['size'] > intval(ini_get('upload_max_filesize'))*1024*1024){
                $data['state'] = false;
                $data['msg'] = '文件大小不可以超过'.ini_get(upload_max_filesize)."M";
                echo json_encode($data);
                die();
            }
            /**
             * 开始上传
             */
            $dir = BASE_SHIP_EXCELPATH.DS.$_SESSION['store_id'].DS;
            if(!is_dir($dir)){
                @mkdir(iconv("UTF-8", "GBK", $dir), 0777, true);
            }
            $fileName = $dir.date('Y').date('m').date('d').time().'.'.$curFileType;
            if (move_uploaded_file($file['tmp_name'], $fileName)) {
                /** @var orderModel $model */
                $model = Model('order');
                $result = $model->batchShipFenxiaoRemark($fileName);
                if($result['state']==false){
                    $data['state'] = false;
                    $data['msg'] = $result['msg'];
                    echo json_encode($data);
                    die();
                }
                $data['state'] = true;
                $current_time=uniqid();
                $key_name=md5('feedback'.$current_time);
                $data['key_name']=$key_name;
                $no_exit_num=intval($result['totals'])-intval($result['actualNum']);
                $data['msg']='去掉第一行Excel总计订单：'.$result['totals'].'条，订单数不存在数量：'.$no_exit_num.'条，成功备注：'.$result['succNum'].'条，失败总数：'.$result['failNum'].'，点击“确定”，下载反馈信息表!';
                wkcache($key_name,serialize($result['data']),360);
                echo json_encode($data);
                die();
            }
        }
    }

    public function export_excel_shiprefenxiaomarktOp(){
            set_time_limit(0);
            header("Content-type:text/html;charset=utf-8");
            vendor('PHPExcel');
            $objExcel = new \PHPExcel();
            $objExcel->getActiveSheet()->setTitle('批量发货备注反馈表');
            $objExcel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
            $objExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
            $objExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
            $objWriter = \PHPExcel_IOFactory::createWriter($objExcel, 'Excel2007');
            $objActSheet = $objExcel->getActiveSheet();
            $key = ord("A");
            $objActSheet->setCellValue("A1", 'fx_order_id');
            $objActSheet->setCellValue("B1", 'remark');
            $objActSheet->setCellValue("C1", '备注的状态');
            $k=2;
            $key_name=$_GET['key_name'];
            $data=unserialize(rkcache($key_name));
            foreach($data as $ka =>$v){
                $objActSheet->setCellValue("A" . $k, $v['fx_order_id']."\t");
                $objActSheet->setCellValue("B" . $k, $v['remark']);
                $objActSheet->setCellValue("C" . $k, $v['status']);
                $k++;
            }
            $outfile =  '批量发货备注反馈表-' . date('Y-m-d') . '.xls';
            header("Content-Type: application/force-download");
            header("Content-Type: application/octet-stream");
            header("Content-Type: application/download");
            header('Content-Disposition:inline;filename="' . $outfile . '"');
            header("Content-Transfer-Encoding: binary");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Pragma: no-cache");
            $objWriter->save('php://output');
            exit();
    }
}
