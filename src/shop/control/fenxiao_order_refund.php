<?php
/**
 * 分销退款订单管理
 *
 *
 *
 * * @汉购网 (c) 2015-2018 ShopWWI Inc. (http://www.hangowa.com)
 * @license    http://www.shopwwi.c om
 * @link       交流群号：
 * @since      汉购网提供技术支持 授权请购买shopnc授权
 */



defined('ByShopWWI') or exit ('Access Invalid!');
class fenxiao_order_refundControl extends BaseSellerControl {
    public function __construct() {
        parent::__construct ();
        Language::read ('member_store_goods_index');
    }
    public function indexOp() {
        $this->refundOp();
    }

    /**
     * 分销退款订单列表
     */
    public function refundOp() {
        $action = $_GET['action'];
        if (is_null($action) || empty($action)) $action = 'getlist';
        switch ($action) {
            case 'getlist' :
                $order_id = $_GET['order_id'];
                $conditions2['filter_store_id'] = $this->store_info['store_id'];
                $member_fenxiao = Model('member_fenxiao')->getMembeFenxiaoList($conditions2);
                $member_ids = array_column($member_fenxiao, 'member_id');

                $conditions['buyer_id'] = array('in', $member_ids);
                if (!empty($order_id))
                    $conditions['order_sn'] = $order_id;
                $order = Model( 'refund_return' );
                $result = $order->getRefundList2($conditions);

                Tpl::output('show_page', Model( 'refund_return' )->showpage());
                Tpl::output('order_refund', $result);
                Tpl::showpage('fenxiao_order_refund.index');
                break;
            case 'importrefund':
                if ($_POST) {
                    set_time_limit(0);
                    $upload = new UploadFile();
                    $upload->set('default_dir', 'uploads/');
                    $upload->set('max_size', 3145728);

                    $info = $upload->upfile('file0',false);
                    if ($info) {
                        $filepath = dirname(dirname(__DIR__)) . '/data/upload/uploads/' . $upload->file_name;
                        $succ = $this->batchRefund($filepath);
                        if(isset($succ['status'])&&$succ['status']=='0'){
                            echo json_encode($succ);exit ;
                        }
                        echo json_encode(array(
                            'status' => '1',
                            'msg' => $succ['msg'],
                            'data'=>$succ
                        ));exit;
                    } else {
                        // 文件上传失败
                        showMessage("服务器繁忙，请稍后再试！","index.php?act=fenxiao_order_refund&op=index&action=getlist", 'json');
                        exit ;
                    }
                }

                Tpl::setLayout('null_layout');
                Tpl::showpage('fenxiao_order_refund.import');
                break;
        }
    }

    /**
     *导入订单反馈信息
     * @param $datas
     */
    public function export_refund_resultOp()
    {
        set_time_limit(0);
        header("Content-type:text/html;charset=utf-8");
        vendor('PHPExcel');
        $objExcel = new \PHPExcel();
        // set document Property
        $objExcel->getActiveSheet()->setTitle('导入退款反馈表');
        $objWriter = \PHPExcel_IOFactory::createWriter($objExcel, 'Excel2007');
        $objActSheet = $objExcel->getActiveSheet();
        $objExcel->getActiveSheet()
            ->getColumnDimension('G')
            ->setWidth(80);
        $objExcel->getActiveSheet()
            ->getColumnDimension('H')
            ->setWidth(20);
        $objExcel->getActiveSheet()
            ->getColumnDimension('L')
            ->setWidth(80);

        $key = ord("A");
        $objActSheet->setCellValue("A1", '订单金额');
        $objActSheet->setCellValue("B1", '退款金额');
        $objActSheet->setCellValue("C1", '退款类型');
        $objActSheet->setCellValue("D1", '备注');
        $objActSheet->setCellValue("E1", '退款状态');
        $objActSheet->setCellValue("F1", '申请时间');
        $objActSheet->setCellValue("G1", '商品名称');
        $objActSheet->setCellValue("H1", '订单编号');
        $objActSheet->setCellValue("I1", '分销平台商品ID');
        $objActSheet->setCellValue("J1", '商品编号');
        $objActSheet->setCellValue("K1", '退款类型(填退款或退货)');
        $objActSheet->setCellValue("L1", '反馈结果');
        $redis=new \Redis();
        $config = C('redis');
        $config = $config['master'];
        $redis->connect($config['host'],$config['port']);
        $data=unserialize($redis->get($_GET['key_name']));
        $k=2;
        foreach($data as $ka =>$v){
            $objActSheet->setCellValue("A" . $k, $v['A']);
            $objActSheet->setCellValue("B" . $k, $v['B']);
            $objActSheet->setCellValue("C" . $k, $v['C']);
            $objActSheet->setCellValue("D" . $k, $v['D']);
            $objActSheet->setCellValue("E" . $k, $v['E']);
            $objActSheet->setCellValue("F" . $k, $v['F']);
            $objActSheet->setCellValue("G" . $k, $v['G']);
            $objActSheet->setCellValue("H" . $k, $v['H']);
            $objActSheet->setCellValue("I" . $k, $v['I']);
            $objActSheet->setCellValue("J" . $k, $v['J']);
            $objActSheet->setCellValue("K" . $k, $v['K']);
            empty($v['feedback'])?$feedback="退款成功":$feedback=$v['feedback'];
            $objActSheet->setCellValue("L" . $k, $feedback);
            $k++;
        }
        $outfile =  '导入退款反馈表-' . date('Y-m-d') . '.xls';
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

    // 批量执行退款
    public function batchRefund($saveFile)
    {
        set_time_limit(0);
        vendor('PHPExcel');
        vendor('PHPExcel.IOFactory');
        $PHPReader = new \PHPExcel_Reader_Excel2007();
        if (! $PHPReader->canRead($saveFile)) {
            $PHPReader = new \PHPExcel_Reader_Excel5();
            if (! $PHPReader->canRead($saveFile)) {
                return  array('status'=>false,'msg'=>"Excel文件未上传成功！");
            }
        }
        $objPHPExcel = \PHPExcel_IOFactory::load($saveFile);
        $sheetData = $objPHPExcel->getActiveSheet()->toArray(null, true, true, true);
        unset($sheetData[1]);
        if (empty($sheetData))
            return  array('status'=>false,'msg'=>"Excel文件数据读取未成功，请检查excel表是否为空！");
        /** @var B2cOrderFenxiaoErrorModel $errorlog */
        $errorlog = Model('b2c_order_fenxiao_error');
        // 获取分销商的商品ID映射
        $category = Model('b2c_category');
        $store_id = $this->store_info['store_id'];
        $member_fenxiao = Model('member_fenxiao')->getMembeFenxiaoList(array('filter_store_id'=>$store_id));
        $member_fenxiao_a = array();
        foreach ($member_fenxiao as $item) {
            $member_fenxiao_a[$item['member_cn_code']] = $item;
        }
        $uids = array_column($member_fenxiao, 'member_id');
        $goods_rel = $this->getGoodsRel($uids);
        $bns = array();
        foreach ($sheetData as $v) {
            if( trim($v['J']) ) {
                $bns[] = trim($v['J']) ;
            }
        }
        // 获取bn与pid的映射
        $arrayBns = $category->getPidFxpidArrayByBns($bns);

        $succNum = 0;   //成功条数
        $failNum = 0;   //失败条数
        $failOrders = array();
        $statusArr = array(
            '同意', '审核中', '拒绝'
        ) ;
        $refundTypeArr = array(
            '退款','退货'
        );
        $datas=$data = $ordnos = $result= array();
        foreach ($sheetData as $k=>$v) {
            $order_no = trim($v['H']);
            //用于反馈处理结果的数组
            if($order_no!=NULL) {
                $datas[$order_no] = array(
                    'A' => $v['A'],
                    'B' => $v['B'],
                    'C' => $v['C'],
                    'D' => $v['D'],
                    'E' => $v['E'],
                    'F' => $v['F'],
                    'G' => $v['G'],
                    'H' => $v['H'],
                    'I' => $v['I'],
                    'J' => $v['J'],
                    'K' => $v['K'],
                );
            }
            if($order_no==NULL){
                $datas[$order_no]['feedback'].="订单号不能为空;";
                continue;
            }

            if( trim($v["E"])!=NULL&&!in_array($v['E'], $statusArr) ) {
                $failNum++;
                $fail_item['oid'] = $v['H'] ;
                $fail_item['msg'] = $v['H'] . "：不能识别此状态：({$v['E']})" ;
                $failOrders[] = $fail_item;
                $datas[$order_no]['feedback'].="不能识别退款状态；";
                continue;
            }
            if(trim($v['K'])&&!in_array($v['K'],$refundTypeArr)){
                $failNum++;
                $fail_item['oid'] = $v['H'] ;
                $fail_item['msg'] = $v['H'] . "：不能识别此状态：({$v['K']})" ;
                $failOrders[] = $fail_item;
                $datas[$order_no]['feedback'].="不能识别退款类型；";
                continue;
            }

            $pid = 0 ;
            if( isset($goods_rel[$v['I']]) && $goods_rel[$v['I']] ) {
                $pid = $goods_rel[$v['I']] ;
            }
            if( isset($arrayBns[$v['J']]) && $arrayBns[$v['J']] ) {
                $pid = $arrayBns[$v['J']] ;
            }
            // 没有商品映射的记录跳过
            if ( $pid == 0) {
                $failNum++;
                $fail_item['oid'] = $v['H'] ;
                $fail_item['msg'] = $v['H'] . "：分销商品({$v['I']})没有映射或没有填写商品编码({$v['J']})" ;
                $failOrders[] = $fail_item;
                $datas[$order_no]['feedback'].="分销商品({$v['I']})没有映射或商品编码为空;";
                continue;
            }

//            $v['K'] = trim($v['K']) == '支付宝' ? 'alipay' : $v['K'];
            //用于退单的数据，不包含出错的行
            $data[$order_no] = array(
                'order_money' => str_replace('元', '', trim($v['A'])),
                'refund_money' => str_replace('元', '', trim($v['B'])),
                'refund_status' => trim($v['E']),
                'refund_type' => (trim($v['K']) == '退款') ? 1 : 2,
                'message' => trim($v['D']),
                'refund_time' => strtotime($v['F']),
                'product_name' => strpos($v['G'], ":") === false ? $v['G'] : substr($v['G'], strpos($v['G'], ":") + 1),
                'pid' => $pid,
                'refund_way' => 'fenxiao',
            );
            $ordnos[] = $order_no;
        }
        if (empty($data)) {
            //$ret = array('totals'=>count($sheetData) , 'succNum'=>$succNum , 'failNum'=>$failNum , 'failOrderids'=>$failOrderids, 'errorMsg' => $errorMsg);
            //return $ret;
            return array('status'=>false,'msg'=>'excel数据解析失败：某些必填字段为空；');
        }

        // 获取汉购订单号
        $orderFenxiao = Model('b2c_order_fenxiao');
        $list = $orderFenxiao->getFxorderByNos($ordnos);
        $ordernos = $paysns = array();
        foreach ($list as $row) {
            $ordernos[$row['orderno']] = $row['pay_sn'] ;
            $paysns[] = $row['pay_sn'] ;
        }
        $orderModel = Model('order');
        $orderList = $orderModel->getOrderlistByPaysn($paysns);
        $orders = $paysn_rel = array();
        foreach ($orderList as $row){
            $paysn_rel[$row['pay_sn']][] = $row['order_id'];
            $orders[$row['order_id']] = $row;
        }
        $ogModel = Model('order_goods');

        foreach ($data as $order_no => &$row) {
            $paysn = $ordernos[$order_no];
            $oids = $paysn_rel[$paysn];
            if(!$oids) {
                unset($data[$order_no]);
                $failNum++;
                $fail_item['oid'] = $order_no;
                $fail_item['msg'] = "没有找到分销订单。";
                $failOrders[] = $fail_item;
                $datas[$order_no]['feedback'].="没有找到分销订单。";
                continue;
            }
            $condition = array();
            $condition['order_id'] = array('in', $oids);
            $condition['goods_id'] = $row['pid'];
            $goods_info = $ogModel->getOrderGoodsInfo($condition);

            if( !$goods_info ) {
                unset($data[$order_no]);
                $failNum++;
                $fail_item['oid'] = $order_no;
                $fail_item['msg'] = "没有找到分销订单{$order_no}对应的商品。商品id:" . var_export($row['pid'], true) ;
                $failOrders[] = $fail_item;
                $datas[$order_no]['feedback'].="没有找到分销订单对应的商品。";
                continue;
            }
            $order_id = $goods_info['order_id'];
            $order_sn = $orders[$order_id]['order_sn'];

            // unset掉没有找到订单号映射的记录
            if ( !$order_sn ) {
                unset($data[$order_no]);
                $failNum++;
                $fail_item['oid'] = $order_no;
                $fail_item['msg'] = "没有找到分销订单{$order_no}对应的汉购订单号" ;
                $failOrders[] = $fail_item;
                $datas[$order_no]['feedback'].="没有找到分销对应的汉购订单号；";
                continue;
            }
            $row['order_sn'] = $order_sn;

        }

        foreach ($failOrders as $item) {
            $errorlog->addLog($item['oid'], $item['msg'], 'refund');
        }
        $this->doRefund($data,$datas) ;
        $key_name=md5('feedback'.uniqid());
        $redis=new \Redis();
        $config = C('redis');
        $config = $config['master'];
        $redis->connect($config['host'],$config['port']);
        $redis->set($key_name,serialize($datas),3600);
        return array('status'=>true,'msg'=>'您已成功导入文件，点击“确定”导出反馈结果Excel。','key_name'=>$key_name);
    }

    function doRefund($refund_data,&$datas)
    {
        if( empty($refund_data) ) return ;

        $model = Model('order');
        foreach ($refund_data as $order_no=>$refund) {

            //是否已退款
            $condition = array();
            $condition['order_sn'] = $refund['order_sn'] ;
            $condition['goods_id'] = array('in', array(0,$refund['pid']) );
            $refund_list = $model->getRefundList($condition);
            if( count($refund_list) > 0 ) {
                $datas[$order_no]['feedback'].='已经退款，不能重复退款；';
                continue ;
            }

            //先申请退款，再根据状态同意或拒绝退款
            $_refund = array();
            $_refund['reason_id'] = 99; //退款退货理由 整型
            $_refund['refund_type'] = $refund['refund_type']; //申请类型 1. 退款  2.退货
            $_refund['return_type'] = 2; //退货情况 1. 不用退货  2.需要退货
            $_refund['seller_state'] = 1; //卖家处理状态:1为待审核,2为同意,3为不同意,默认为1
            $_refund['refund_amount'] = $refund['refund_money'];//退款金额
            $_refund['goods_num'] = 1;//商品数量
            $_refund['buyer_message'] = $refund['message'] ? $refund['message']:'申请退款';  //用户留言信息
            $_refund['ordersn'] =$refund['order_sn'];  //汉购网订单编号
            $_refund['goods_id'] = $refund['pid']; //商品编号
            $_refund['refund_way'] = $refund['refund_way']; //商品编号

            $res = model('refund_return')->addApiRefund(json_decode(json_encode($_refund)));
            if(isset($res['errorno'])) $datas[$order_no]['feedback'].=$res['msg'];

        }

        //更新退款状态
        $this -> edit_refund($refund_data,$datas) ;
    }

    function edit_refund($refund_data,&$datas)
    {
        $model = Model('order');
        foreach ($refund_data as $order_no =>$refund) {
            $condition = array();
            $condition['order_sn'] = $refund['order_sn'] ;
            $condition['goods_id'] = array('in', array(0,$refund['pid']) );
            $refund_list = $model->getRefundList($condition);
            $refund_id = $refund_list[0]['refund_id'] ;
            if(!$refund_id) continue ;
            if( $refund['refund_status'] == '审核中' ) continue ;

            $params = array();
            $msg = '';
            $params['refund_id'] = $refund_id ;
            $params['status'] = $refund['refund_status'];
            $params = json_decode(json_encode($params));
            $refund = Service("Refund");
            if( $params->status == '同意' ) {

                $_p['refund_id'] = $params->refund_id;
                $_p['op_name'] = 'fenxiao';
                $status = $refund->confirm_refund($_p, $msg) ;

            } else if( $params->status == '拒绝' ) {

                $_p['refund_id'] = $params->refund_id;
                $_p['seller_state'] = 3;
                $_p['op_name'] = 'fenxiao';
                $status = $refund->edit_refund($_p, $msg) ;

            }
            $res = model('refund_return')->addApiRefund($params);
            $res = json_decode($res,true);
            if(isset($res['errorno']))$datas[$order_no]['feedback'].=$res['msg'];
        }
    }

    function getGoodsRel($uids)
    {
        $category = Model('b2c_category');
        $result = $category->where(array('uid'=> array('in', $uids)))->select();
        $rel = array();
        foreach ($result as $v) {
            $rel[$v['fxpid']] = $v['pid'];
        }
        return $rel;
    }
}
