<?php
/**
 * 分销订单管理
 *
 *
 *
 * * @汉购网 (c) 2015-2018 ShopWWI Inc. (http://www.hangowa.com)
 * @license    http://www.shopwwi.c om
 * @link       交流群号：
 * @since      汉购网提供技术支持 授权请购买shopnc授权
 */



defined('ByShopWWI') or exit ('Access Invalid!');
class fenxiao_orderControl extends BaseSellerControl {

    public function __construct() {
        parent::__construct ();
        Language::read ('member_store_goods_index');
    }
    public function indexOp() {
        $this->orderOp();
    }

    /**
     * 分销订单列表
     */
    public function orderOp() {
        $action = $_GET['action'];
        if (is_null($action) || empty($action)) $action = 'getlist';
        $model_goods = Model('goods');
        switch ($action) {
            case 'getlist' :
                $status = $_GET['status'];
                $oid = trim($_GET['oid']);
                $fxoid = trim($_GET['fxoid']);
                $starttime = $_GET['starttime'];
                $endtime = $_GET['endtime'];
                $istarttime = $_GET['istarttime'];
                $iendtime = $_GET['iendtime'];
                $fenxiao_id = $_GET['fenxiao_id'];

                $store_id = $this->store_info['store_id'];
                $conditions2['filter_store_id'] = $store_id;
                if (!empty($fenxiao_id))
                    $conditions2['id'] = $fenxiao_id;

                $member_fenxiao = Model('member_fenxiao')->getMembeFenxiaoList($conditions2);
                $member_ids = array_column($member_fenxiao, 'member_id');
                $conditions['buyer_id'] = array('in', $member_ids);

                $status_msg = 'order_all';
                if ($status != '') {
                    if ($status == 0) {
                        $conditions['order_state'] = '10';
                    } elseif ($status == 1) {
                        $conditions['order_state'] = '30';
                        $status_msg = 'order_send';
                    } elseif ($status == 2) {
                        $conditions['order_state'] = '0';
                        $status_msg = 'order_fail';
                    } elseif ($status == 3) {
                        $conditions['order_state'] = '40';
                        $status_msg = 'order_fin';
                    } elseif ($status == 4) {
                        $conditions['order_state'] = '20';
                        $status_msg = 'order_pay';
                    }
                }
                if ($oid) $conditions['order_sn'] = array('like', '%' . $oid . '%');
                if ($fxoid) $conditions['fx_order_id'] = $fxoid;
                if ($starttime && $endtime)
                    $conditions['add_time'] = array(array('gt', strtotime($starttime)), array('lt', strtotime($endtime)));
                if ($istarttime && $iendtime)
                    $conditions['import_time'] = array(array('gt', strtotime($istarttime)), array('lt', strtotime($iendtime)));

                $order = Model('order');
                $result = $order->getFenxiaoOrderList($conditions);

                $express_arr = Model('express')->getExpressList();
                foreach ($result as &$v) {
                    $v['reciver_info'] = unserialize($v['reciver_info']);
                    $v['express_name'] = '';
                    if(in_array($v['order_state'] , array(30,40))){
                        $v['express_name'] = $express_arr[$v['shipping_express_id']]['e_name'];
                    }
                }

                // 列出渠道
                $fenxiao_list = Model('member_fenxiao')->getMembeFenxiaoList(array('filter_store_id' => $store_id));
                $fenxiao_list = array_under_reset($fenxiao_list, 'member_id');

                Tpl::output('show_page', $model_goods->showpage());
                $this->profile_menu($status_msg);
                Tpl::output('goods_list', $result);
                Tpl::output('fenxiao_list', $fenxiao_list);
                Tpl::showpage('fenxiao_order.index');
                break;
            case 'importorder':
                if ($_POST) {
                    set_time_limit(0);
                    $upload = new UploadFile();
                    $upload->set('default_dir', 'uploads/');
                    $upload->set('max_size', 3145728);

                    $info = $upload->upfile('file0',false);
                    if ($info) {
                        $filepath = dirname(dirname(__DIR__)) . '/data/upload/uploads/' . $upload->file_name;
                        $succ = $this->importExcel($filepath, '');
                        if(isset($succ['status'])&&$succ['status']=='0'){
                            echo json_encode($succ);
//                            showMessage($succ,"index.php?act=fenxiao_order&op=index&action=getlist", 'json');
                            exit ;
                        }
                        echo json_encode(array(
                            'status' => '1',
                            'msg' => "订单导入成功 {$succ['num']}条！单击确定按钮导出反馈excel",
                            'data'=>$succ
                        ));
                        //showMessage("订单导入成功 {$succ['num']}条！单击确定按钮导出反馈excel","index.php?act=fenxiao_order&op=index&action=getlist", 'json');
//                        showMessage($succ,"index.php?act=fenxiao_order&op=index&action=getlist", 'json');
                        exit ;
                    } else {
                        // 文件上传失败
                        showMessage("服务器繁忙，请稍后再试！","index.php?act=fenxiao_order&op=index&action=getlist", 'json');
                        exit ;
                    }
                }

                Tpl::setLayout('null_layout');
                Tpl::showpage('fenxiao_order.import');
                break;
        }
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

        //取得配送公司代码
        $express = rkcache('express',true);
        Tpl::output('e_code',$express[$order_info['extend_order_common']['shipping_express_id']]['e_code']);
        Tpl::output('e_name',$express[$order_info['extend_order_common']['shipping_express_id']]['e_name']);
        Tpl::output('e_url',$express[$order_info['extend_order_common']['shipping_express_id']]['e_url']);
        Tpl::output('shipping_code',$order_info['shipping_code']);

        self::profile_menu('search','search');
        Tpl::showpage('fenxiao_order.detail');
    }

    public function export_excel_resultOp()
    {
        set_time_limit(0);
        header("Content-type:text/html;charset=utf-8");
        vendor('PHPExcel');
        $objExcel = new \PHPExcel();
        // set document Property
        $objExcel->getActiveSheet()->setTitle('导入订单反馈表');
        $objExcel->getActiveSheet()
            ->getColumnDimension('A')
            ->setWidth(15);
        $objExcel->getActiveSheet()
            ->getColumnDimension('B')
            ->setWidth(60);
        $objExcel->getActiveSheet()
            ->getColumnDimension('C')
            ->setWidth(20);
        $objExcel->getActiveSheet()
            ->getColumnDimension('D')
            ->setWidth(10);
        $objExcel->getActiveSheet()
            ->getColumnDimension('E')
            ->setWidth(10);
        $objExcel->getActiveSheet()
            ->getColumnDimension('F')
            ->setWidth(10);
        $objExcel->getActiveSheet()
            ->getColumnDimension('G')
            ->setWidth(10);
        $objExcel->getActiveSheet()
            ->getColumnDimension('H')
            ->setWidth(10);
        $objExcel->getActiveSheet()
            ->getColumnDimension('I')
            ->setWidth(10);
        $objExcel->getActiveSheet()
            ->getColumnDimension('J')
            ->setWidth(10);
        $objExcel->getActiveSheet()
            ->getColumnDimension('K')
            ->setWidth(75);
        $objExcel->getActiveSheet()
            ->getColumnDimension('L')
            ->setWidth(50);
        $objExcel->getActiveSheet()
            ->getColumnDimension('M')
            ->setWidth(20);
        $objExcel->getActiveSheet()
            ->getColumnDimension('N')
            ->setWidth(40);
        $objExcel->getActiveSheet()
            ->getColumnDimension('O')
            ->setWidth(40);
        $objExcel->getActiveSheet()
            ->getColumnDimension('P')
            ->setWidth(70);
        $objWriter = \PHPExcel_IOFactory::createWriter($objExcel, 'Excel2007');
        $objActSheet = $objExcel->getActiveSheet();
        $key = ord("A");
        $objActSheet->setCellValue("A1", '分销平台商品ID');
        $objActSheet->setCellValue("B1", '商品名（选填，可为空）');
        $objActSheet->setCellValue("C1", '订单号');
        $objActSheet->setCellValue("D1", '单价');
        $objActSheet->setCellValue("E1", '数量');
        $objActSheet->setCellValue("F1", '收货人');
        $objActSheet->setCellValue("G1", '手机');
        $objActSheet->setCellValue("H1", '省');
        $objActSheet->setCellValue("I1", '市');
        $objActSheet->setCellValue("J1", '区');
        $objActSheet->setCellValue("K1", '街道');
        $objActSheet->setCellValue("L1", '订单留言');
        $objActSheet->setCellValue("M1", '商品货号');
        $objActSheet->setCellValue("N1", '是否发货(填“是”发货，默认不发货)');
        $objActSheet->setCellValue("O1", '订单时间(格式：2016-11-28 13:44:32)');
        $objActSheet->setCellValue("P1", '渠道（格式：贝贝网）');
        $objActSheet->setCellValue("Q1", '订单反馈结果');
        $k=2;
        $redis=new \Redis();
        $config = C('redis');
        $config = $config['master'];
        $redis->connect($config['host'],$config['port']);
        $data=unserialize($redis->get($_GET['key_name']));

        foreach($data as $ka =>$v){
            $objActSheet->setCellValue("A" . $k, $v[0]['A']);
            $objActSheet->setCellValue("B" . $k, $v[0]['B']);
            $objActSheet->setCellValue("C" . $k, $v[0]['C']);
            $objActSheet->setCellValue("D" . $k, $v[0]['D']);
            $objActSheet->setCellValue("E" . $k, $v[0]['E']);
            $objActSheet->setCellValue("F" . $k, $v[0]['F']);
            $objActSheet->setCellValue("G" . $k, $v[0]['G']);
            $objActSheet->setCellValue("H" . $k, $v[0]['H']);
            $objActSheet->setCellValue("I" . $k, $v[0]['I']);
            $objActSheet->setCellValue("J" . $k, $v[0]['J']);
            $objActSheet->setCellValue("K" . $k, $v[0]['K']);
            $objActSheet->setCellValue("L" . $k, $v[0]['L']);
            $objActSheet->setCellValue("M" . $k, $v[0]['M']);
            $objActSheet->setCellValue("N" . $k, $v[0]['N']);
            $objActSheet->setCellValue("O" . $k, $v[0]['O']);
            $objActSheet->setCellValue("P" . $k, $v[0]['P']);
            $objActSheet->setCellValue("Q" . $k, $v[0]['Q']);
            $k++;
        }
        $outfile =  '导入订单反馈表-' . date('Y-m-d') . '.xls';
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

    // 导入
    public function importExcel($saveFile, $pid="")
    {
        set_time_limit(0);
        vendor('PHPExcel');
        vendor('PHPExcel.IOFactory');
        $PHPReader = new \PHPExcel_Reader_Excel2007();
        if (! $PHPReader->canRead($saveFile)) {
            $PHPReader = new \PHPExcel_Reader_Excel5();
            if (! $PHPReader->canRead($saveFile)) {
                echo 'no Excel';
                return 0;
            }
        }
        $objPHPExcel = \PHPExcel_IOFactory::load($saveFile);
        $sheetData = $objPHPExcel->getActiveSheet()->toArray(null, true, true, true);
        unset($sheetData[1]);
        $store_id = $this->store_info['store_id'];
        $member_fenxiao = Model('member_fenxiao')->getMembeFenxiaoList(array('filter_store_id'=>$store_id));
        $member_fenxiao_a = array();
        foreach ($member_fenxiao as $item) {
            $member_fenxiao_a[$item['member_cn_code']] = $item;
        }
        $uids = array_column($member_fenxiao, 'member_id');
        if (! $pid) {
            // 非单品导入则读取商品映射
            $rel = $this->getGoodsRel($uids);
        }
        $errorlog = Model('b2c_order_fenxiao_error');
        $datas = array();
        $bns = array();
        $order_nos = array();
        foreach ($sheetData as $k => $v) {
            $error = 0;
            $log = "";
            $feedback="";//导入订单反馈信息数组
            $qudao = trim($v['P']);
            $member_fenxiao_s = isset($member_fenxiao_a[$qudao]) ? $member_fenxiao_a[$qudao] : array();
            if (empty($v['A']) && empty($v['B']) && empty($v['C']) && empty($v['D']) && empty($v['E']) && empty($v['F']) && empty($v['G'])) {
                break;
            }
            if (empty($member_fenxiao_s)) {
                $log .= "第{$k}行没有填写订单号";
                $error++;
                $feedback.="商品渠道不正确；";
            }
            if( !$v['C'] ) {
                $log .= "第{$k}行没有填写订单号";
                $error++;
                $feedback.="订单号为空；";
            }
            if( !$v['D'] ) {
                $log .= "第{$k}行没有填写商品价格";
                $error++;
                $feedback.="商品价格为空；";
            }
            if( !$v['E'] || !preg_match("/^[0-9]+$/i",   trim($v['E']) ) ) {
                $log .= "第{$k}行没有填写数量或格式不正确";
                $error++;
                $feedback.="数量或格式不正确；";
            }
            if( !$v['F'] ) {
                $log .= "第{$k}行没有填写收货人";
                $error++;
                $feedback.="收货人为空；";
            }
            if( !$v['G'] ) {
                $log .= "第{$k}行没有填写收货人手机";
                $error++;
                $feedback.="收货人手机号为空；";
            }
            if( !$v['K'] ) {
                $log .= "第{$k}行没有填写收货人街道";
                $error++;
                $feedback.="收货人街道地址为空；";
            }
            if( !$v['O']&&!strtotime($v['O']) ) {
                $log .= "第{$k}行没有填写订单生成时间";
                $error++;
                $feedback.="订单生成时间为空；";
            }
            $v["Q"]=$feedback;
            $order_nos[] = $v['C'] ;
            if( trim($v['M']) ) {
                $bns[] = trim($v['M']) ;
            }
            $addr = trim($v['H']) . trim($v['I']) . trim($v['J']) . trim($v['K']);
            $same_order_key = md5(trim($v['G']) . trim($addr) . trim($v['C']));
            $datas[$same_order_key][] = $v;
        }
        if( $error > 0 ) {
            $errorlog->addLog(date('YmdHis'), $log, $member_fenxiao_s, 'order');
            //将数据写入缓存
            return array('status'=>'0','msg'=>'所导入的数据信息不完整，订单号、商品价格、商品数量、收货人、收货人手机号、收货人街道地址、订单生成时间均为必填！');
        }
        $product = Model('b2c_category');
        $arrayPids = $product->getPidFxpidArrayByUid($uids);
        $fxPids = array_keys($arrayPids);
        $arrayBns = $product->getPidFxpidArrayByBns($bns);
        $fxBns = array_keys($arrayBns);

        $order = Model('b2c_order_fenxiao');
        $orderArr = $order->getOrderNoByUid($uids, $order_nos);

        if (empty($datas))
            return array('status'=>'0','msg'=>'excel表为空！');

        $num = 0;
        foreach ($datas as $k => $v) {
            $message = '';
            $result = true;
            $order_no = trim($v[0]['C']); // 分销系统订单编号
            $qudao = trim($v[0]['P']);
            $member_fenxiao_s = isset($member_fenxiao_a[$qudao]) ? $member_fenxiao_a[$qudao] : array();
            if (empty($member_fenxiao_s)) {
                $result = false;
                $message = "商品编号" . trim($v[0]['A']) . "的商品渠道不正确";
                $datas[$k][0]['Q'].="商品渠道不正确；";
            }
            if (! in_array(trim($v[0]['A']), $fxPids) && !isset($arrayBns[trim($v[0]['M'])])) { //分销用户映射商品，集采用户不用映射
                $result = false;
                $message = "商品编号" . trim($v[0]['A']) . "的商品没有映射";
                $datas[$k][0]['Q'].="商品没有映射；";
            }
            if (in_array(trim($v[0]['C']), $orderArr)) {
                $result = false;
                $message = "订单号：" . trim($v[0]['C']) . "已经存在";
                $datas[$k][0]['Q'].="订单已存在；";
            }
            if ($result) {
                $data = array();
                $data['order_sn'] = $order_no; // 分销系统订单编号

                //导入时间格式不正常处理
                if (substr_count(trim($v[0]['O']),'-') != 2 || substr_count(trim($v[0]['O']),':') != 2) {
                    $v[0]['O'] = date('Y-m-d H:i:s', time());
                }

                $data['buy_id'] = $member_fenxiao_s['member_id']; // 分销商用户编号
                $data['receiver'] = trim($v[0]['F']); // 收件人
                $data['provine'] = trim($v[0]['H']);
                $data['city'] = trim($v[0]['I']);
                $data['area'] = trim($v[0]['J']);
                $data['address'] = preg_replace('/\s/', '', trim($v[0]['K']));
                $data['mobile'] = trim($v[0]['G']); // 手机号码
                $data['remark'] = trim($v[0]['L']);
                $data['amount'] = trim($v[0]['E']);
                $data['is_ship'] = trim($v[0]['N']) == '是' ? 1 : 0;
                $data['order_time'] = trim($v[0]['O']) ? strtotime(trim($v[0]['O'])):time();
                $data['payment_code'] = 'fenxiao'; // 订单来源 fenxiao,jicai
                $data['order_from'] = '3';
                $data['key'] = C('order_create_key');

                $count = count($v);
                $totalprice = '';
                $nogoodsid = false;
                for ($i = 0; $i < $count; $i ++) {
                    if( $pid ) {
                        $goods_id = $pid ;
                    } else if( trim($v[$i]['M']) ) {
                        $goods_id = $arrayBns[trim($v[0]['M'])] ;
                    } else {
                        $goods_id = $arrayPids[trim($v[$i]['A'])] ;
                    }
                    if( !$goods_id ) $nogoodsid = true ;
                    $data['item'][] = array(
                        'goods_id' => $goods_id,
                        'num' => trim($v[$i]['E']),
                        'price' => trim($v[$i]['D']),
                        'fxpid' => trim($v[$i]['A']),
                        'oid' => $goods_id
                    );
                    $totalprice += trim($v[$i]['E']) * trim($v[$i]['D']);
                }
                if( $nogoodsid ) {$datas[$k][0]['Q'].="商品编号映射不存在；";continue ;}
                $data['amount'] = $totalprice;
                //订单导入时间
                $data['import_time']=time();
                // 添加订单信息结束
                $result = Model('order')->createFxOrder(json_encode($data), true);
                if($result['error'] == 1000){
                    $datas[$k][0]['Q'].="该笔订单导入成功";
                    $num++;
                }else{
                    $msg = $result['msg'];
                    $errorlog->addLog($order_no, $msg, $member_fenxiao_s, 'order');
                    $datas[$k][0]['Q'].="{$msg};";
                }
            } else {
                $errorlog->addLog($order_no, $message, $member_fenxiao_s, 'order');
                $datas[$k][0]['Q'].=$message.";";
            }
        }
        //将数据写入缓存
        $key_name=md5('feedback'.uniqid());
        $redis=new \Redis();
        $config = C('redis');
        $config = $config['master'];
        $redis->connect($config['host'],$config['port']);
        $redis->set($key_name,serialize($datas),3600);
        $return=array('num'=>$num,'key_name'=>$key_name);
        return $return;
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

    // 导出
    public function export_excelOp()
    {
        if (IS_POST) {
            set_time_limit(0);
            header("Content-type:text/html;charset=utf-8");
            vendor('PHPExcel');
            $objExcel = new \PHPExcel();
            // set document Property
            $objExcel->getActiveSheet()->setTitle('导出订单');
            $objExcel->getActiveSheet()
                ->getColumnDimension('B')
                ->setWidth(25);
            $objExcel->getActiveSheet()
                ->getColumnDimension('C')
                ->setWidth(15);
            $objExcel->getActiveSheet()
                ->getColumnDimension('D')
                ->setWidth(25);
            $objExcel->getActiveSheet()
                ->getColumnDimension('F')
                ->setWidth(15);
            $objExcel->getActiveSheet()
                ->getColumnDimension('G')
                ->setWidth(15);
            $objExcel->getActiveSheet()
                ->getColumnDimension('G')
                ->setWidth(10);
            $objExcel->getActiveSheet()
                ->getColumnDimension('H')
                ->setWidth(15);
            $objExcel->getActiveSheet()
                ->getColumnDimension('I')
                ->setWidth(50);
            $objExcel->getActiveSheet()
                ->getColumnDimension('J')
                ->setWidth(25);
            $objExcel->getActiveSheet()
                ->getColumnDimension('K')
                ->setWidth(15);
            $objExcel->getActiveSheet()
                ->getColumnDimension('L')
                ->setWidth(10);
            $objExcel->getActiveSheet()
                ->getColumnDimension('M')
                ->setWidth(15);
            $objExcel->getActiveSheet()
                ->getColumnDimension('N')
                ->setWidth(20);
            $objExcel->getActiveSheet()->mergeCells('A1:O1');
            $objExcel->getActiveSheet()
                ->getStyle('A1')
                ->getAlignment()
                ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objExcel->getActiveSheet()
                ->getStyle('A1')
                ->getFont()
                ->setSize(15);
            $objExcel->getActiveSheet()
                ->getStyle('A1')
                ->getFont()
                ->setBold(true);
            $objWriter = \PHPExcel_IOFactory::createWriter($objExcel, 'Excel2007');
            $objActSheet = $objExcel->getActiveSheet();
            $key = ord("A");
            $objActSheet->setCellValue("A1", '汉购网分销平台订单导出');
            $objActSheet->setCellValue("A2", '序号');
            $objActSheet->setCellValue("B2", '分销商品');
            $objActSheet->setCellValue("C2", '分销商品ID');
            $objActSheet->setCellValue("D2", '分销商品订单号');
            $objActSheet->setCellValue("E2", '单价');
            $objActSheet->setCellValue("F2", '数量');
            $objActSheet->setCellValue("G2", '订单金额');
            $objActSheet->setCellValue("H2", '收货人姓名');
            $objActSheet->setCellValue("I2", '手机');
            $objActSheet->setCellValue("J2", '详细地址');
            $objActSheet->setCellValue("K2", '平台订单号');
            $objActSheet->setCellValue("L2", '订单状态');
            $objActSheet->setCellValue("M2", '物流公司');
            $objActSheet->setCellValue("N2", '物流单号');
            $objActSheet->setCellValue("O2", '导入时间');
            $status = $_POST['exp_status'];
            $oid = trim($_POST['exp_oid']);
            $fxoid = trim($_POST['exp_fxoid']);
            $starttime = $_POST['exp_starttime'];
            $endtime = $_POST['exp_endtime'];
            $istarttime = $_POST['exp_istarttime'];
            $iendtime = $_POST['exp_iendtime'];
            $fenxiao_id = $_POST['fenxiao_id'];
//            if (!$oid&&!$fxoid&&(! $begintime || ! $endtime)&&(! $import_begin || ! $import_end)) {
//                $this->error('必须输入【筛选条件】');
//            }
            $order = Model('order');
            $orderitems = Model('order_goods');

            $store_id = $this->store_info['store_id'];
            $conditions2['filter_store_id'] = $store_id;
            if (!empty($fenxiao_id))
                $conditions2['id'] = $fenxiao_id;
            $member_fenxiao = Model('member_fenxiao')->getMembeFenxiaoList($conditions2);
            $member_ids = array_column($member_fenxiao, 'member_id');
            $conditions['buyer_id'] = array('in', $member_ids);

            if ($status != '') {
                if ($status == 0) {
                    $conditions['order_state'] = '10';
                } elseif ($status == 1) {
                    $conditions['order_state'] = '30';
                } elseif ($status == 2) {
                    $conditions['order_state'] = '0';
                } elseif ($status == 3) {
                    $conditions['order_state'] = '40';
                } elseif ($status == 4) {
                    $conditions['order_state'] = '20';
                }
            }
            if ($oid) $conditions['order_sn'] = array('like', '%' . $oid . '%');
            if ($fxoid) $conditions['fx_order_id'] = $fxoid;
            if ($starttime && $endtime)
                $conditions['add_time'] = array(array('gt', strtotime($starttime)), array('lt', strtotime($endtime)));
            if ($istarttime && $iendtime)
                $conditions['import_time'] = array(array('gt', strtotime($istarttime)), array('lt', strtotime($iendtime)));

            $orderlist = $order->getFenxiaoOrderList($conditions, 1);
            $express_arr = Model('express')->getExpressList();
            foreach ($orderlist as &$v) {
                $v['reciver_info'] = unserialize($v['reciver_info']);
                $v['express_name'] = '';
                if(in_array($v['order_state'] , array(30,40))){
                    $v['express_name'] = $express_arr[$v['shipping_express_id']]['e_name'];
                }
            }
            $k = 3;
            foreach ($orderlist as $index => $order) {
                $objActSheet->setCellValue("A" . $k, $index + 1);
                $objActSheet->setCellValue("H" . $k, $order['reciver_name']);
                $objActSheet->setCellValue("I" . $k, $order['reciver_info']['phone']);
                $objActSheet->setCellValue("J" . $k, $order['reciver_info']['address']);
                $objActSheet->setCellValue("K" . $k, ' ' . $order['order_id']);

                if ($order['order_state'] == 0) {
                    $status = '已取消';
                } elseif ($order['order_state'] == 10) {
                    $status = '未支付';
                } elseif ($order['order_state'] == 20) {
                    $status = '已付款';
                } elseif ($order['order_state'] == 30) {
                    $status = '已发货';
                } elseif ($order['order_state'] == 40 ) {
                    $status = '已完成';
                }
                $objActSheet->setCellValue("L" . $k, $status);
                $objActSheet->setCellValue("O" . $k, $order['datetime']);

                $expressId='';
                $expressName='';
                if($order['shipping_code']!=null&&$order['shipping_code']!=''){
                    $expressId=$order['shipping_code'];
                }
                define('ByShopWWI', 1);
                if($order['shipping_express_id']!=null&&$order['shipping_express_id']!=''){
                    $expressName=$express_arr[$order['shipping_express_id']]['e_name'];
                }


                foreach ($order['suborder'] as $suborder) {
                    $objActSheet->setCellValue("B" . $k, $suborder['goods_name']);
                    $fxpid=Model('b2c_category')->where(array('pid'=>$suborder['goods_id']))->field()->find();
                    $objActSheet->setCellValue("C" . $k, $fxpid['fxpid']);
                    $objActSheet->setCellValue("D" . $k, ' ' . $order['fx_order_id']);
                    $objActSheet->setCellValue("E" . $k, $suborder['goods_price']);
                    $objActSheet->setCellValue("F" . $k, $suborder['goods_num']);
                    $objActSheet->setCellValue("G" . $k, $suborder['goods_amount']);
                    $objActSheet->setCellValue("M" . $k, $expressName);
                    $objActSheet->setCellValue("N" . $k, " " . $expressId);
                    $k ++;
                }
            }
            $outfile = '订单' . date('Y-m-d') . '.xls';
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
        } else {
            $this->error('非法的请求');
        }
    }

    /**
     * 用户中心右边，小导航
     *
     * @param string $menu_key 当前导航的menu_key
     * @return
     */
    private function profile_menu($menu_key = '') {
        $menu_array = array(
            array('menu_key' => 'order_all',    'menu_name' => '全部订单',    'menu_url' => urlShop('fenxiao_order', 'index', array('action' => 'getlist', 'status' => ''))),
            array('menu_key' => 'order_pay',    'menu_name' => '已付款待发货',    'menu_url' => urlShop('fenxiao_order', 'index', array('action' => 'getlist', 'status' => 4))),
            array('menu_key' => 'order_send',    'menu_name' => '已发货',    'menu_url' => urlShop('fenxiao_order', 'index', array('action' => 'getlist', 'status' => 1))),
            array('menu_key' => 'order_fail',    'menu_name' => '已作废',    'menu_url' => urlShop('fenxiao_order', 'index', array('action' => 'getlist', 'status' => 2))),
            array('menu_key' => 'order_fin',    'menu_name' => '已完成',    'menu_url' => urlShop('fenxiao_order', 'index', array('action' => 'getlist', 'status' => 3))),
        );
        Tpl::output ( 'member_menu', $menu_array );
        Tpl::output ( 'menu_key', $menu_key );
    }
}
