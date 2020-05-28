<?php
namespace Home\Controller;

use Home\Model\OrderModel;
use Think\Controller;

class OrderController extends AuthController
{

    public function orderList()
    {
        $status = I('get.status', '', 'htmlspecialchars');
        $this->display('order/orderlist');
    }
    public function cart()
    {
        $goodsModel = M('Goods');
        if(IS_AJAX){
            $ids = I('post.ids');
            $goodsList = $goodsModel->where(array('id'=>array('in',$ids)))->select();
            $this->ajaxReturn(array(
                'status' => '1',
                'items' => $goodsList
            ));
        }
        $this->display('order/cart');
    }

    public function create()
    {
        if(!IS_POST){$this->ajaxReturn(array('status' => '-1', 'msg' => '非法请求'));}
        $post = I('post.');
        $items = $post['items'];
        unset($post['items']);
        $ids = array_column($items,'id');
        $goodsModel = M('Goods');
        $orderGoodsModel = M('OrderGoods');
        $orderModel = M('Order');
        $goodsList = $goodsModel->where('id',array('in',$ids))->select(array('index'=>'id'));
        $post['goods_amount']=0;
        $post['cost_amount']=0;
        $post['order_sn']=0;
        $post['user_name']='';
        $post['store_name']='';
        $post['created_at']=time();
        // 循环处理商品信息
        foreach ($items as $k=>$item) {
            $goods = $goodsList[$item['goods_id']];
            $item['goods_name'] = $goods['name'];
            $post['goods_amount'] += ($item['goods_amount']=$item['goods_price']*$item['goods_num']);
            $post['cost_amount'] += ($item['goods_cost'] = $goods['cost']*$item['goods_num']);
            $items[$k] = $item;
        }
        $post['amount']=$post['goods_amount']+$post['freight'];
        $orderModel->startTrans();
        $order = $orderModel->data($post)->add();
        if(!$order){
            $orderModel->rollback();
            $this->ajaxReturn(array('status' => '-1', 'msg' => '订单提交失败'));
        }
        foreach ($items as $k=>$item) {
            $item['order_id'] = $order;
            $items[$k] = $item;
        }
        $goodsRes = $orderGoodsModel->addAll($items);

        if(!$goodsRes){
            $orderModel->rollback();
            $this->ajaxReturn(array('status' => '-1', 'msg' => '订单提交失败'));
        }
        $orderModel->commit();
        $this->ajaxReturn(array('status' => '1', 'data' => $order));
    }
    public function view()
    {
        $id = (int)I('get.id');
        if($id<=0) $this->error('非法的订单ID');
        $goodsModel = M('Goods');
        $orderGoodsModel = M('OrderGoods');
        $orderModel = M('Order');
        $order = $orderModel->where(array('id'=>$id))->find();
        if(empty($order))$this->error('没有找到订单');
        $orderGoods = $orderGoodsModel->where(array('order_id'=>$id))->select();
        $goodsList = $goodsModel->where('id',array('in',array_column($orderGoods,'goods_id')))->select(array('index'=>'id'));
        $this->assign('goodsList',$goodsList);
        $this->assign('order',$order);
        $this->assign('orderGoods',$orderGoods);

        $this -> display('order/view');
    }

    // 获取商品映射
    function getGoodsRel()
    {
        $category = D('B2cCategory');
        $result = $category->where('uid=' . session('uid'))->select();
        $rel = array();
        foreach ($result as $v) {
            $rel[$v['fxpid']] = $v['pid'];
        }
        return $rel;
    }
    
    // 导入
    public function importExcel($saveFile = '', $pid)
    {
        set_time_limit(0);
        vendor('PHPExcel');
        vendor('PHPExcel.IOFactory');
        $PHPExcel = new \PHPExcel();
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
        if (! $pid) {
            // 非单品导入则读取商品映射
            $rel = $this->getGoodsRel();
        }
        $errorlog = D('B2cOrderFenxiaoError');
        $datas = array();
        $bns = array();
        $order_nos = array();
        foreach ($sheetData as $k => $v) {
        	$error = 0;
        	$log = "";
        	if( !$v['C'] ) {
        		$log .= "第{$k}行没有填写订单号";
        		$error++;
        	}
        	if( !$v['D'] ) {
        		$log .= "第{$k}行没有填写商品价格";
        		$error++;
        	}
        	if( !$v['E'] || !preg_match("/^[0-9]+$/i",   trim($v['E']) ) ) {
        		$log .= "第{$k}行没有填写数量或格式不正确";
        		$error++;
        	}
        	if( !$v['F'] ) {
        		$log .= "第{$k}行没有填写收货人";
        		$error++;
        	}
        	if( !$v['G'] ) {
        		$log .= "第{$k}行没有填写收货人手机";
        		$error++;
        	}
        	if( !$v['K'] ) {
        		$log .= "第{$k}行没有填写收货人街道";
        		$error++;
        	}
        	
        	$order_nos[] = $v['C'] ;
        	if( trim($v['M']) ) {
        		$bns[] = trim($v['M']) ;
        	}
            $addr = trim($v['H']) . trim($v['I']) . trim($v['J']) . trim($v['K']);
            $same_order_key = md5(trim($v['G']) . trim($addr) . trim($v['C']));
            $datas[$same_order_key][] = $v;
        }
        if( $error > 0 ) {
        	$errorlog->addLog(date('YmdHis'), $log, 'order');
        	return 0;
        }
        $product = D('B2cCategory');
        $arrayPids = $product->getPidFxpidArrayByUid(session('uid'));
        $fxPids = array_keys($arrayPids);
        $arrayBns = $product->getPidFxpidArrayByBns($bns);
        $fxBns = array_keys($arrayBns);
        
        $order = D('B2cOrderFenxiao');
        $orderArr = $order->getOrderNoByUid(session('uid'), $order_nos);
        
        if (empty($datas))
            return 0;
        $resultArr = array();
        $url = C('BASE_URL') . "shop/api/fenxiao/order.php";
        
        //人人店订单列表接口出问题时，根据订单号导入订单，如果EXCEL只输入了分销订单号，执行此功能
        if( session('username') == 'renrendian' ) {
        	$gidStr = $mobileStr = "" ;
        	$fx_orderid_arr = array() ;
        	foreach ($datas as $k => $v) {
        		$gidStr += trim($v[0]['A']) ;
        		$mobileStr += trim($v[0]['G']) ;
        		$fx_orderid_arr[] = trim($v[0]['C']);
        	}
        	
        	if( $gidStr == "" && $mobileStr == "" && !empty($fx_orderid_arr) ) {
        		$succ = $this -> impRrdorder( $fx_orderid_arr ) ;
        		return $succ;
        	}
        }

        $num = 0;
        foreach ($datas as $k => $v) {
            $message = '';
            $result = true;
            
            $order_no = trim($v[0]['C']); // 分销系统订单编号
            if (! in_array(trim($v[0]['A']), $fxPids) && session('member_type')=='fenxiao' && !isset($arrayBns[trim($v[0]['M'])])) { //分销用户映射商品，集采用户不用映射
                $result = false;
                $message = "商品编号" . trim($v[0]['A']) . "的商品没有映射";
            }
            if (in_array(trim($v[0]['C']), $orderArr)) {
                $result = false;
                $message = "订单号：" . trim($v[0]['C']) . "已经存在";
            }
            if ($result) {
                $data = array();
                $data['order_sn'] = $order_no; // 分销系统订单编号
                $data['buy_id'] = session('uid'); // 分销商用户编号
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
                $data['payment_code'] = session('member_type'); // 订单来源 fenxiao,jicai
                $data['order_from'] = session('member_type') == 'fenxiao' ? '3' : '4' ;
                $data['key'] = C('ORDER_KEY');
                
                if( session('username') == 'hangob2b' ) {
                	$data['order_from'] = '5' ;
                	$data['payment_code'] = 'b2b' ;
                }
                
                $count = count($v);
                $totalprice = '';
                $nogoodsid = false ;
                for ($i = 0; $i < $count; $i ++) {
                	if( $pid ) {
                		$goods_id = $pid ;
                	} else if( trim($v[$i]['M']) ) {
                		$goods_id = $arrayBns[trim($v[0]['M'])] ;
                	} else {
                		$goods_id = session('member_type') == 'jicai' ? trim($v[$i]['A']) : $arrayPids[trim($v[$i]['A'])] ;
                	}
                	if( !$goods_id ) $nogoodsid = true ;
                    $data['item'][] = array(
                        'goods_id' => $goods_id,
                        'num' => trim($v[$i]['E']),
                        'price' => trim($v[$i]['D']),
                    	'oid' => $goods_id
                    );
                    $totalprice += trim($v[$i]['E']) * trim($v[$i]['D']);
                }
                if( $nogoodsid ) continue ;
                $data['amount'] = $totalprice;
                // 添加订单信息结束
                $params = JSON($data);
                $output = curl_url($url, $params);
                $output = json_decode($output);
                if($output->error==1000){
                    $num++;
                }else{
                    $errorlog->addLog($order_no, $output->msg, 'order');
                }
            } else {
                $errorlog->addLog($order_no, $message, 'order');
            }
        }
        return $num;
    }
    
    // 导出
    public function exportExcel()
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
            $objActSheet->setCellValue("C2", '分销商品订单号');
            $objActSheet->setCellValue("D2", '单价');
            $objActSheet->setCellValue("E2", '数量');
            $objActSheet->setCellValue("F2", '订单金额');
            $objActSheet->setCellValue("G2", '收货人姓名');
            $objActSheet->setCellValue("H2", '手机');
            $objActSheet->setCellValue("I2", '详细地址');
            $objActSheet->setCellValue("J2", '平台订单号');
            $objActSheet->setCellValue("K2", '订单状态');
            $objActSheet->setCellValue("L2", '物流公司');
            $objActSheet->setCellValue("M2", '物流单号');
            $objActSheet->setCellValue("N2", '导入时间');
            // $objActSheet->setCellValue("O2", '商品编号');
            $status = I('post.status', '', 'htmlspecialchars');
            $begintime = I('post.begintime', '', 'htmlspecialchars');
            $endtime = I('post.endtime', '', 'htmlspecialchars');
            $oid = I('post.oid', '', 'htmlspecialchars');
            $fxoid = I('post.fxoid', '', 'htmlspecialchars');
            if (! $begintime || ! $endtime) {
                $this->error('输入开始时间与结束时间');
            }
            
            $order = D('Orders');
            $orderitems = D('OrderGoods');
            $pagesize = 10;
            $page = 1;
            $orderlist = array();
            $flag = 1;
            while ($flag) {
                $result = $order->getOrderList($pagesize, $page, session('uid'), $status, $oid, $fxoid, $begintime, $endtime);
                foreach ($result[1] as $v) {
                    $v['suborder'] = $orderitems->getOrderItems($v['order_id']);
                    $orderlist[] = $v;
                }
                if ($page == $result[2])
                    $flag = 0;
                $page ++;
            }
            
            // var_dump($orderlist);exit;
            // p($orderlist);die;
            $k = 3;
			//dump($orderlist);
            foreach ($orderlist as $index => $order) {
            	$order['reciver_info'] = unserialize($order['reciver_info']);
                $objActSheet->setCellValue("A" . $k, $index + 1);
                $objActSheet->setCellValue("G" . $k, $order['reciver_name']);
                $objActSheet->setCellValue("H" . $k, $order['reciver_info']['phone']);
                $objActSheet->setCellValue("I" . $k, $order['reciver_info']['address']);
                $objActSheet->setCellValue("J" . $k, ' ' . $order['order_id']);
                
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
                $objActSheet->setCellValue("K" . $k, $status);
                $objActSheet->setCellValue("N" . $k, $order['datetime']);
                
				$expressId='';
                $expressName='';
                if($order['shipping_code']!=null&&$order['shipping_code']!=''){
                   	$expressId=$order['shipping_code'];
                }
				define('ByShopWWI', 1);
			        $express_arr = include '../data/cache/express.php';
                if($order['shipping_express_id']!=null&&$order['shipping_express_id']!=''){
                   	$expressName=$express_arr[$order['shipping_express_id']]['e_name'];
                }
				
				
                foreach ($order['suborder'] as $suborder) {
                    $objActSheet->setCellValue("B" . $k, $suborder['order_good_name']);
                    $objActSheet->setCellValue("C" . $k, ' ' . $suborder['fx_order_id']);
                    $objActSheet->setCellValue("D" . $k, $suborder['goods_price']);
                    $objActSheet->setCellValue("E" . $k, $suborder['goods_num']);
                    $objActSheet->setCellValue("F" . $k, $suborder['goods_amount']);
                    $objActSheet->setCellValue("L" . $k, $expressName);
                    $objActSheet->setCellValue("M" . $k, " " . $expressId);
                    $k ++;
                }
            }
            $outfile = session('shopName') . '订单' . date('Y-m-d') . '.xls';
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
    
    // 下载文件
    function downloadTemplate()
    {
        $file = './Public/uploads/ordertpl.xlsx';
        downloadFile($file);
    }

    public function test()
    {
        if (IS_POST) {
            $upload = new \Think\Upload(); // 实例化上传类
            $upload->maxSize = 3145728; // 设置附件上传大小
            $upload->exts = array(
                'jpg',
                'gif',
                'png',
                'jpeg',
                'js'
            ); // 设置附件上传类型
            $upload->rootPath = './Public/uploads/';
            $upload->savePath = ''; // 设置附件上传目录
                                    // 上传文件
            $info = $upload->upload();
            if (! $info) {
                // 上传错误提示错误信息
                // $this->error($upload->getError());
            } else {
                // 上传成功
                $filepath = $upload->rootPath . $info['file0']['savepath'] . $info['file0']['savename'];
                $this->importExcel($filepath);
            }
        }
        $this->display('order/aaaa');
    }

    public function ajax()
    {
        if (IS_AJAX) {
            $action = I('post.action', '', 'htmlspecialchars');
            switch ($action) {
                case 'category':
                    $id = I('post.id', '', 'htmlspecialchars');
                    $order = D('B2cCategory');
                    $category = $order->getCategory(session('uid'), $id);
                    if ($category) {
                        $this->ajaxReturn(array(
                            'status' => '1',
                            'msg' => $category
                        ));
                        exit();
                    }
                    $this->ajaxReturn(array(
                        'status' => '0',
                        'msg' => '服务器繁忙，请稍后再试！'
                    ));
                    break;
                case 'importorder':
                	set_time_limit(0);
                    $catid = I('post.catid', '', 'htmlspecialchars');
                    $pid = I('post.pid', '', 'htmlspecialchars');
                    $productname = trim(I('post.productname', '', 'htmlspecialchars'));
                    $upload = new \Think\Upload(); // 实例化上传类
                    $upload->maxSize = 3145728; // 设置附件上传大小
                    $upload->exts = array(
                        'xls',
                        'xlsx'
                    ); // 设置附件上传类型
                    $upload->rootPath = './Public/uploads/';
                    $upload->savePath = ''; // 设置附件上传目录
                                            // 上传文件
                    $info = $upload->upload();
                    if (! $info) {
                        // 上传错误提示错误信息
                        // $this->error($upload->getError());
                        $this->ajaxReturn(array(
                            'status' => '0',
                            'msg' => '服务器繁忙，请稍后再试！'
                        ));
                    } else {
                        // 上传成功
                        $filepath = $upload->rootPath . $info['file0']['savepath'] . $info['file0']['savename'];
                        $succ = $this->importExcel($filepath, $pid);
                        $this->ajaxReturn(array(
                            'status' => '1',
                            'msg' => "订单导入成功 {$succ}条！"
                        ));
                    }
                    break;
                case 'getlist':
                    $pagesize = 10;
                    $page = I('post.page', '', 'htmlspecialchars');
                    $status = I('post.status', '', 'htmlspecialchars');
                    $oid = trim(I('post.oid', '', 'htmlspecialchars'));
                    $starttime = I('post.starttime', '', 'htmlspecialchars');
                    $endtime = I('post.endtime', '', 'htmlspecialchars');
                    /** @var OrderModel $order */
                    $order = D('Order');
                    $result = $order->getOrderList($pagesize, $page, $status, $oid, $starttime, $endtime);
                    foreach ($result[1] as &$v) {
						$v['reciver_info'] = unserialize($v['reciver_info']);
						$v['express_name'] = '';
                        $results[] = $v;
                    }
                    $result[1] = $results;
                    if (! ($result[0] > 0))
                        $this->ajaxReturn(array(
                            'status' => '0',
                            'msg' => '暂无数据！'
                        ));
                    $data['total_num'] = $result[0];
                    $data['list'] = $result[1];
                    $data['page_total_num'] = $result[2];
                    $data['page_size'] = $pagesize;
                    $data['status'] = $status;
                    $this->ajaxReturn(array(
                        'status' => '1',
                        'msg' => $data
                    ));
                    break;
                case 'importrefund':
                    $upload = new \Think\Upload(); // 实例化上传类
                    $upload->maxSize = 3145728; // 设置附件上传大小
                    $upload->exts = array(
                        'xls',
                        'xlsx'
                    ); // 设置附件上传类型
                    $upload->rootPath = './Public/uploads/';
                    $upload->savePath = ''; // 设置附件上传目录
                                            // 上传文件
                    $info = $upload->upload();
                    if (! $info) {
                        $this->ajaxReturn(array(
                            'status' => '0',
                            'msg' => '服务器繁忙，请稍后再试！'
                        ));
                    } else {
                        // 上传成功
                        $filepath = $upload->rootPath . $info['file0']['savepath'] . $info['file0']['savename'];
                        $succ = $this->batchRefund($filepath);
                        $this->ajaxReturn(array(
                            'status' => '1',
                            'msg' => '订单退款成功 ' . $succ . ' 条！'
                        ));
                    }
                    break;
                case 'editshiping':
                    $OrderCommon = D('Orders');
                    $params = array(
                        'column' => I('post.column', '', 'htmlspecialchars'),
                        'col_value' => I('post.col_value', '', 'htmlspecialchars'),
                        'order_id' => I('post.order_id', '', 'htmlspecialchars')
                    );
					
                    if (! $params['column'] || ! $params['col_value'] || ! $params['order_id']) {
                        $this->ajaxReturn(array(
                            'status' => '0',
                            'msg' => '不能修改为空'
                        ));
                    }
					if($params['column']=='reciver_info'){
						$str=$params['col_value'];
						$arr = explode(",",$str);
						$valArr=array(
						'address'=>$arr[1],
						'area'=>'',
						'dlyp'=>'',
						'mob_phone'=>$arr[0],
						'phone'=>$arr[0],
						'street'=>'',
						'tel_phone'=>'',
						);
						$params['col_value']=serialize($valArr);
					}

                    
                    if ($OrderCommon->updateShiping($params)) {
                        $this->ajaxReturn(array(
                            'status' => '1',
                            'msg' => '编辑成功'
                        ));
                    } else {
                        $this->ajaxReturn(array(
                            'status' => '0',
                            'msg' => '编辑失败'
                        ));
                    }
                    
                    break;
            }
        } else {
            $this->ajaxReturn(array(
                'status' => '-1',
                'msg' => '无效的操作！'
            ));
        }
    }
    
    // 批量执行退款
    public function batchRefund($saveFile)
    {
        set_time_limit(0);
        vendor('PHPExcel');
        vendor('PHPExcel.IOFactory');
        $PHPExcel = new \PHPExcel();
        $PHPReader = new \PHPExcel_Reader_Excel2007();
        if (! $PHPReader->canRead($saveFile)) {
            $PHPReader = new \PHPExcel_Reader_Excel5();
            if (! $PHPReader->canRead($saveFile)) {
                // echo 'no Excel';
                return 0;
            }
        }
        $objPHPExcel = \PHPExcel_IOFactory::load($saveFile);
        $sheetData = $objPHPExcel->getActiveSheet()->toArray(null, true, true, true);
        unset($sheetData[1]);
        
        if (empty($sheetData))
            return 0;
        
        $errorlog = D('B2cOrderFenxiaoError');
        // 获取分销商的商品ID映射
        $category = D('B2cCategory');
        $res = $category->getCategory(session('uid'));
        $goods_rel = array();
        foreach ($res as $row) {
            if ($row['fxpid'] == 0)
                continue;
            $goods_rel[$row['fxpid']] = $row['pid'];
        }
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
        $data = $ordnos = array();
        foreach ($sheetData as $v) {
            $order_no = trim($v['H']);
            
            if( !in_array($v['E'], $statusArr) ) {
            	$failNum++;
            	$fail_item['oid'] = $v['H'] ;
                $fail_item['msg'] = $v['H'] . "：不能识别此状态：({$v['E']})" ;
                $failOrders[] = $fail_item;
                continue;
            }
            if(!in_array($v['K'],$refundTypeArr)){
                $failNum++;
                $fail_item['oid'] = $v['H'] ;
                $fail_item['msg'] = $v['H'] . "：不能识别此状态：({$v['K']})" ;
                $failOrders[] = $fail_item;
                $this->ajaxReturn(array(
                    'status' => '-1',
                    'msg' => $fail_item['msg']
                ));
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
            if ( $pid == 0 ) {
            	$failNum++;
                $fail_item['oid'] = $v['H'] ;
                $fail_item['msg'] = $v['H'] . "：分销商品({$v['I']})没有映射或没有填写商品编码({$v['J']})" ;
                $failOrders[] = $fail_item;
                continue;
            }
            
//            $v['K'] = trim($v['K']) == '支付宝' ? 'alipay' : $v['K'];
            $data[$order_no] = array(
                'order_money' => str_replace('元', '', trim($v['A'])),
                'refund_money' => str_replace('元', '', trim($v['B'])),
                'refund_status' => trim($v['E']),
                'refund_type' => (trim($v['K']) == '退款')?1:2,
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
        	return $succNum;
        }
            
        // 获取汉购订单号
        $orderFenxiao = D('B2cOrderFenxiao');
        $list = $orderFenxiao->getFxorderByNos($ordnos);
        $ordernos = $paysns = array();
        foreach ($list as $row) {
        	$ordernos[$row['orderno']] = $row['pay_sn'] ;
        	$paysns[] = $row['pay_sn'] ;
        }
        $orderModel = D('Orders');
        $orderList = $orderModel->getOrderlistByPaysn($paysns);
        $orders = $paysn_rel = array();
        foreach ($orderList as $row){
        	$paysn_rel[$row['pay_sn']][] = $row['order_id'];
        	$orders[$row['order_id']] = $row;
        }
        $ogModel = D('OrderGoods');
        
        foreach ($data as $order_no => &$row) {
        	$paysn = $ordernos[$order_no];
        	$oids = $paysn_rel[$paysn];
        	if(!$oids) continue;
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
                continue;
            }
            
            $row['order_sn'] = $order_sn;

        }

        foreach ($failOrders as $item) {
            $errorlog->addLog($item['oid'], $item['msg'], 'refund');
        }
        $this->doRefund($data) ;
        return count($data);
    }

    function doRefund($refund_data)
    {
    	if( empty($refund_data) ) return ;

    	$model = D('Orders');
    	$refund_url = C('BASE_URL') . "shop/api/fenxiao/refund.php";

    	foreach ($refund_data as $refund) {

    		//是否已退款
    		$condition = array();
    		$condition['order_sn'] = $refund['order_sn'] ;
    		$condition['goods_id'] = array('in', array(0,$refund['pid']) );
    		$refund_list = $model->getRefundList($condition);
    		if( count($refund_list) > 0 ) continue ;
    		
    		//先申请退款，再根据状态同意或拒绝退款
    		$_refund = array();
    		$_refund['reason_id'] = 99; //退款退货理由 整型
    		$_refund['refund_type'] = $refund['refund_type']; //申请类型 1. 退款  2.退货
    		$_refund['return_type'] = 2; //退货情况 1. 不用退货  2.需要退货
    		$_refund['seller_state'] = 1; //卖家处理状态:1为待审核,2为同意,3为不同意,默认为1
    		$_refund['refund_amount'] = $refund['refund_money'];//退款金额
    		$_refund['goods_num'] = 1;//商品数量
    		$_refund['buyer_message'] = $refund['message'] ? $refund['message']:'申请退款';  //用户留言信息
    		$_refund['ordersn'] = $refund['order_sn'];  //汉购网订单编号
    		$_refund['goods_id'] = $refund['pid']; //商品编号
            $_refund['refund_way'] = $refund['refund_way']; //商品编号

    		$params = JSON($_refund);
    		$output = curl_url($refund_url, $params);
    	}
		
    	//更新退款状态
    	$this -> edit_refund($refund_data) ;
    }
    
    function edit_refund($refund_data)
    {
    	$model = D('Orders');
    	$refund_url = C('BASE_URL') . "shop/api/fenxiao/edit_refund.php";
    	foreach ($refund_data as $refund) {
    		$condition = array();
    		$condition['order_sn'] = $refund['order_sn'] ;
    		$condition['goods_id'] = array('in', array(0,$refund['pid']) );
    		$refund_list = $model->getRefundList($condition);
    		$refund_id = $refund_list[0]['refund_id'] ;
    		if(!$refund_id) continue ;
    		if( $refund['refund_status'] == '审核中' ) continue ;
    		
    		$params = array();
    		$params['refund_id'] = $refund_id ;
    		$params['status'] = $refund['refund_status'];
    		$params = JSON($params);
    		$output = curl_url($refund_url, $params);
    	}
    }
    
    function impRrdorder( $fx_orderid_arr )
    {
    	if( !is_array($fx_orderid_arr) || empty( $fx_orderid_arr ) ) return 0;
    	
    	$params = JSON($fx_orderid_arr);
    	$url = C('BASE_URL') . "shop/index.php?act=autotask&op=rrdImportOrder";
    	$succ = curl_url($url, $params);
    	return $succ ;
    }
}
