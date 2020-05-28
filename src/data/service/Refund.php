<?php
/**
 * Author: zengxj
 */

class RefundService
{
	//检查订单退款金额
	public function check_order_refund($order_sn,$sdf_post,&$msg)
    {
        $order = TModel("Orders") ;
        $sdf_order = $order -> where( "order_sn = {$order_sn}" ) -> find() ;
        if( !$sdf_order ) {
        	$msg = "订单信息不存在" ;
        	return false ;
        }

        if($sdf_post['money']){//退款金额是从弹出的退款单里输入而来

            if($sdf_post['money']>$sdf_order['order_amount'] || $sdf_post['money'] < 0){
                  $msg = '退款金额不在范围之内';
                  return false;
            }
            
            if( $sdf_post['refund_amount_all'] > $sdf_order['order_amount'] - $sdf_order['refund_amount'] ) {
            	$msg = '退款金额不在范围之内';
            	return false;
            }
        }

        return true;
    }

	/*抓取远程图片*/
	public function grab_pic($url){
		if($url=="") return false;
		if(!preg_match("/^(http:\/\/|https:\/\/).*$/",$url)){
			return false;
		}
		$ext = strrchr($url, ".");
		if(!in_array($ext,array('.gif','.jpg','.png',".jpeg"))){
			return false;
		}
		$file_url=BASE_UPLOAD_PATH.DS."shop/refund";
		if(!file_exists($file_url)){
			mkdir(BASE_UPLOAD_PATH.DS."shop/refund",0777,true);
		}
		$img_sn=sprintf('%010d',time() - 946656000).sprintf('%03d',microtime() * 1000).sprintf('%04d',mt_rand(0,9999));
		$filepath =$file_url.DS.$img_sn."_refund_import".$ext;
		$filename=$img_sn."_refund_import".$ext;
		if(stristr($url,'https')){
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_REFERER, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			$img = curl_exec($ch);
			curl_close($ch);
		}else{
			ob_start();//打开输出
			readfile($url);//输出图片文件
			$img = ob_get_contents();//得到浏览器输出
			ob_end_clean();//清除输出并关闭
		}
		if(!$img){
			return false;
		}
		$fp = @fopen($filepath,"a");
		$imgLen = strlen($img);
		$_inx = 1024;
		$_time = ceil($imgLen/$_inx);
		for($i=0; $i<$_time; $i++){
			fwrite($fp,substr($img, $i*$_inx, $_inx));
		}
		fclose($fp);
		$upload=new UploadFile();
		$upload->set('thumb_width',500);
		$upload->set('thumb_height',499);
		$upload->set('thumb_ext','_small');
		$upload->set('save_path',"shop/refund".DS.$img_sn."_refund_import".$ext);
		$upload->create_thumb($filepath);
		$img_exit=strrchr($filepath,".");
		$img_url=substr($filepath,0,strrpos($filepath,'.'))."_small".$img_exit;
		if(file_exists($img_url)){
			@unlink($filepath);
			$filename=substr($filename,0,strrpos($filename,'.'))."_small".$img_exit;
		}
		return  $filename;
	}

    /**
     * 在线退款
     * @param $detail
     * @return mixed
     */
    public function apiRefund($detail)
    {
        if (in_array($detail['refund_code'], array('wxpay', 'wx_jsapi', 'wx_saoma'))) {
            return $this->_wxpayRefund($detail);
        }
        if ($detail['refund_code'] == 'alipay'){
            return $this->_alipayRefund($detail);
        }
        return $detail;
    }

    private function _wxpayRefund($detail)
    {
        $model_refund = Model('refund_return');
        $refund_id = $detail['refund_id'];
        $order = $model_refund->getPayDetailInfo($detail);//退款订单详细
        $refund_amount = $order['pay_refund_amount'];//本次在线退款总金额
        if ($refund_amount > 0) {
            $wxpay = $order['payment_config'];
            define('WXN_APPID', $wxpay['appid']);
            define('WXN_MCHID', $wxpay['mchid']);
            define('WXN_KEY', $wxpay['key']);
            define('WXN_SECRET', $wxpay['secret']);
            $total_fee = $order['total_pay_amount']*100;//微信订单实际支付总金额(在线支付金额,单位为分)
            $refund_fee = $refund_amount*100;//本次微信退款总金额(单位为分)
            $file_path = dirname(dirname(__FILE__));
            $api_file = $file_path.DS.'api'.DS.'payment'.DS.'Wxpay'.DS.'lib'.DS.'WxPay.Api.php';
            $api_file2 = $file_path.DS.'api'.DS.'payment'.DS.'Wxpay'.DS.'lib'.DS.'WxPay.Data.php';
            require_once $api_file;
            require_once $api_file2;
            $input = new WxPayRefund();
            $input->SetTransaction_id($order['trade_no']);//微信订单号
            $input->SetTotal_fee($total_fee);
            $input->SetRefund_fee($refund_fee);
            $input->SetOut_refund_no($detail['batch_no']);//退款批次号
            $input->SetOp_user_id(WxPayConfig::MCHID);
            //$input->SetRefund_account('REFUND_SOURCE_RECHARGE_FUNDS');
            $data = WxPayApi::refund($input);
            if(!empty($data) && $data['return_code'] == 'SUCCESS') {//请求结果
                if($data['result_code'] == 'SUCCESS') {//业务结果
                    $detail = array();
                    $detail['pay_amount'] = ncPriceFormat($data['refund_fee']/100);
                    $detail['pay_time'] = time();
                    $model_refund->editDetail(array('refund_id'=> $refund_id), $detail);
                    $result['state'] = 'true';
                    $result['msg'] = '微信成功退款:'.$detail['pay_amount'];
                    $refund = $model_refund->getRefundReturnInfo(array('refund_id'=> $refund_id));
                    $consume_array = array();
                    $consume_array['member_id'] = $refund['buyer_id'];
                    $consume_array['member_name'] = $refund['buyer_name'];
                    $consume_array['consume_amount'] = $detail['pay_amount'];
                    $consume_array['consume_time'] = time();
                    $consume_array['consume_remark'] = '微信在线退款成功（到账有延迟），退款退货单号：'.$refund['refund_sn'];
                    QueueClient::push('addConsume', $consume_array);
                } else {
                    throw new Exception('微信退款错误,'.$data['err_code_des']);
                    //$result['msg'] = '微信退款错误,'.$data['err_code_des'];//错误描述
                }
            } else {
                throw new Exception('微信退款错误,'.$data['err_code_des']);
                //$result['msg'] = '微信接口错误,'.$data['return_msg'];//返回信息
            }
        }
        return $detail;
    }

    private function _alipayRefund($detail)
    {
        $model_refund = Model('refund_return');
        $refund_id = $detail['refund_id'];
        $order = $model_refund->getPayDetailInfo($detail);//退款订单详细
        $refund_amount = $order['pay_refund_amount'];//本次在线退款总金额
        if ($refund_amount > 0) {
            $config = C('alipay');
            $payment_config = $order['payment_config'];
            $file_path = dirname(dirname(__FILE__));
            $api_file = $file_path.DS.'api'.DS.'payment'.DS.'Alipay'.DS.'aop'.DS.'AopClient.php';
            $api_file2 = $file_path.DS.'api'.DS.'payment'.DS.'Alipay'.DS.'aop'.DS.'request'.DS.'AlipayTradeRefundRequest.php';
            include $api_file;
            include $api_file2;
            $aop = new AopClient();
            $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
            $aop->appId = $payment_config['alipay_appid'];
            $aop->rsaPrivateKey = $config['rsaPrivateKey'];
            $aop->alipayrsaPublicKey = $config['alipayrsaPublicKey'];
            $aop->apiVersion = '1.0';
            $aop->signType = 'RSA2';
            $aop->postCharset = 'UTF-8';
            $aop->format = 'json';
            $request = new AlipayTradeRefundRequest ();
            $send_data = array(
                'out_trade_no' => $order['trade_no'],
                'trade_no' => $order['trade_no'],
                'refund_amount' => $refund_amount,
//                'refund_currency' => 'USD',
                'refund_currency' => 'CNY',
                'refund_reason' => '正常退款',
                //'out_request_no' => 'HZ01RF001',
                'out_request_no' => 'HANGO_'.(1000+time()%1000),
                'operator_id' => 'OP001',
                'store_id' => 'NJ_S_001',
                'terminal_id' => 'NJ_T_001',
                'goods_detail' => array(),
                'refund_royalty_parameters' => array(),
            );

            $request->setBizContent(json_encode($send_data));
            $result = $aop->execute ( $request);

            $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
            $resultCode = $result->$responseNode->code;
            if (!empty($resultCode) && $resultCode == 10000) {
                $detail = $result = array();
                $detail['pay_amount'] = ncPriceFormat($refund_amount);
                $detail['pay_time'] = time();
                $model_refund->editDetail(array('refund_id'=> $refund_id), $detail);
                $result['state'] = 'true';
                $result['msg'] = '支付宝成功退款:'.$detail['pay_amount'];
                $refund = $model_refund->getRefundReturnInfo(array('refund_id'=> $refund_id));
                $consume_array = array();
                $consume_array['member_id'] = $refund['buyer_id'];
                $consume_array['member_name'] = $refund['buyer_name'];
                $consume_array['consume_amount'] = $detail['pay_amount'];
                $consume_array['consume_time'] = time();
                $consume_array['consume_remark'] = '支付宝在线退款成功（到账有延迟），退款退货单号：'.$refund['refund_sn'];
                QueueClient::push('addConsume', $consume_array);
            } else {
                throw new Exception('支付宝退款错误：'.$result->$responseNode->sub_msg);
            }
        }

        return $detail;
    }

    public function importRefunds($csvFilePath)
    {
        if(!is_file($csvFilePath)){
            return callback(false , '文件不存在');
        }
        $data = $this->_excelToArray($csvFilePath);
        if(!count($data) > 1){
            return callback(false ,'订单数据有误');
        }
        /* 检查文件格式是否正确 */
        $title = $data[0];
        if($title[0]!='订单金额'||$title[1]!='退款金额'||$title[2]!='退款类型'||$title[3]!='备注'){
            return array('state'=>'false','msg'=>'文件格式错误');
        }
        // v($data);
        unset($data[0]);
        $res = array('total'=>0,'success'=>0,'fail'=>array(),'errorMsg'=>array());
        $fxOrderIds = array();
        $fxGoodsIds = array();
        $goodsIdsRel = array();
        /** @var orderModel $orderModel */
        $orderModel = Model('order');
        $tasks = array();
        $orderSns = array();

        // 整理数据
        foreach ($data as $k=>$v){
            foreach ($v as $key=>$value) $v[$key] = trim(trim($value,'*'));
			if(!empty($v[12])){
			    preg_match_all('/(http(s){0,1}\:){0,1}\/\/.+\.(jpg|png|gif|bmp|webp)/isU',$v[12],$matches);
				//$imglist=explode(',',$v[12]);
				$imglist=$matches[0];
				$arr = array('buyer'=>array());
				foreach($imglist as $item){
					$grab_pic=$this->grab_pic(trim($item));
					if($grab_pic){
						$arr['buyer'][]=$grab_pic;
					}
				}
				$v[12]=serialize($arr);
			}
            if(!is_numeric($v[0])) break;
            $res['total']+=1;
            $tasks[] = $v;
            //分析:
            //excel退单分为汉购网订单编号和分销订单编号两种模式任选其一填写,即可完成退单
            //分销订单编号存入fxOrderIds
            if(!empty($v[10])) $fxOrderIds[] = $v[10];
            //如果分销用户名和分销商品id都存在
            if(!empty($v[11])&&!empty($v[9])) {
                //如果fxOrderIds数组中存在,就在旗下存商品id
                if(isset($fxGoodsIds[$v[9]])) $fxGoodsIds[$v[9]][] = $v[11];
                else $fxGoodsIds[$v[9]] = array($v[11]);
            }
            //如果汉购网订单编号存在,就存入orderSns
            if(!empty($v[7])) $orderSns[] = $v[7];
        }

        /** @var memberModel $memberModel */
        $memberModel = Model('member');
        /** @var b2c_categoryModel $b2cGoodsModel */
        $b2cGoodsModel = Model('b2c_category');
        // 将分销订单ID转化为以分销名分组，分销商品ID=>汉购网商品ID
        foreach ($fxGoodsIds as $key => $value){
            //v($data);
            //通过分销用户名查找分销会员id
            $member = $memberModel->getMemberInfo(array('member_name'=>$key),'member_id');
            //如何查不到,这条忽略
            if(empty($member)) continue;
            //goodsIdsRel=>分销商品ID=>汉购网商品ID
            $goodsRel = $b2cGoodsModel->table('b2c_category')->field('fxpid,pid')->where(array('uid'=>$member['member_id'],'fxpid'=>array('in',$value)))->select();
            $goodsIdsRel[$key] =array_column($goodsRel,'pid','fxpid');
        }
        //如果分销订单编号存在
        if(!empty($fxOrderIds)){
            //就通过分销订单编号查订单详情
            $orderList = $orderModel->getOrderList(array('fx_order_id'=>array('in',$fxOrderIds)),'','*','order_id,order_sn,fx_order_id',9999999,array('order_goods'));
            $fxOrders = array();
            // 将订单调整为以分销订单为键的模式
            foreach ($orderList as $order){
                //如果订单编号存在就存入orderSns
                $orderSns[] = $order['order_sn'];
                if(isset($fxOrders[$order['fx_order_id']])) $fxOrders[$order['fx_order_id']][] = $order;
                else $fxOrders[$order['fx_order_id']] = array($order);
            }
        }
        //查出所有有关订单详情,查出来后并未使用,遂注释
        $orders = $orderList = $orderModel->getOrderList(array('order_sn'=>array('in',$orderSns)),'','*','order_id',9999999,array('order_goods'));

        foreach ($tasks as $k=>$task){
            if(true === ($result = $this->_importRefundFromCsv($task,$orders,$fxOrders,$goodsIdsRel)))
                $res['success']+=1;
            else{
                $res['fail'][] = empty($task[7])?$task[10]:$task[7];
                $res['errorMsg'][] = (empty($task[7])?$task[10]:$task[7]).' : '.$result;
            }
        }
        return $res;
    }
    /**
     * excel导入退款单 数据库操作
     * @param $data         excel数据                     一维数组
     * @param $orders       该数据没有派上用场
     * @param $fxOrders     [分销订单编号]=>[分销订单详情]  二维数组
     * @param $goodsIdsRel  [分销商品id]=>[汉购商品id]      一维数组
     * @return true or error_message
     */
    private function _importRefundFromCsv($data,$orders,$fxOrders,$goodsIdsRel){
        //必须查询出订单的汉购订单编号和退款金额,以备后续($orderInfo)
        $orderModel = Model('order');
        if ($data[7]) { $condition = array('order_sn'=>array('eq',$data[7])); }
        if ($data[10]) { $condition = array('fx_order_id'=>array('eq',$data[10])); }
        $orderInfo = $orderModel->getOrderInfo($condition);
        $goodsId = $data[8];
	    $orderSn = $data[7];
	    $refund_way = 'predeposit';
        if(empty($data[7])||empty($data[8])){ // 汉购网订单信息为空
	        if(empty($data[9])||empty($data[10])) return '退款信息填写不完整';
            //分销商品id = goodsIdsRel[分销用户名][分销商品id]
            $goodsId = $goodsIdsRel[$data[9]][$data[11]];
            //新增功能:不填写分销商品id就认为是整单退款
            if(empty($goodsId)) {
                $orderSn = $orderInfo['order_sn'];
                $orderId = $fxOrders[$data[10]]['order_id'];
                $orderGoodsList = $orderModel -> getOrderGoodsInfo( array( 'order_id'=>$orderId ) );
                $goodsId = $orderGoodsList['goods_id'];
                $refund_way = 'fenxiao';
            }else{
                foreach ($fxOrders[$data[10]] as $fxOrder){
                    foreach ($fxOrder['extend_order_goods'] as $order_goods){
                        if ($order_goods['goods_id'] == $goodsId) {
                            $orderSn = $fxOrder['order_sn'];
                            $refund_way = 'fenxiao';
                            //v($refund_way);
                            break 2;
                        }
                    }
                }
            }
        }


        $_refund = array();
        $_refund['reason_id'] = 99; //退款退货理由 整型
        $reasonInfo = $data[2];
        /** @var refund_returnModel $refundModel */
        $refundModel = Model("refund_return");
        $_refund['reason_info'] = $reasonInfo; //退款退货理由 整型
        $reason = $refundModel->getReasonList(array('reason_info'=>$reasonInfo));
        if($data[2]&&count($reason)>0) {
            $reason = current($reason);
            $_refund['reason_id'] = $reason['reason_id']; //退款退货理由 整型
        }
        $_refund['refund_type'] = 1; //申请类型 1. 退款  2.退货
        $_refund['return_type'] = 1; //退货情况 1. 不用退货  2.需要退货
        $_refund['seller_state'] = 1; //卖家处理状态:1为待审核,2为同意,3为不同意,默认为1
        $_refund['refund_amount'] = $data[11] ? ($data[1] ? $data[1] : $orderInfo['order_amount']) : $orderInfo['order_amount'];//退款金额
        $_refund['goods_num'] = 1;//商品数量
        $_refund['buyer_message'] = $data[3] ? $data[3]:'申请退款';  //用户留言信息
        $_refund['ordersn'] = $orderSn;  //汉购网订单编号
        $_refund['goods_id'] = $goodsId; //商品编号
        $_refund['refund_way'] = $refund_way; //退款方式
		if(!empty($data[12])){
			$_refund['pic_info']=$data[12];
		}
        //v($_refund);
        /** @var refund_returnModel $refundModel */
        $refundModel = Model('refund_return');
        $res = $refundModel->addApiRefund((object)$_refund);
        if($res['errorno'] != 1000) return $res['msg'];

        //审核中则不审批， 等待商家审批
        if ($data[4] == '审核中') {
            return true;
        }

        $editParam = array(
            'refund_id'=>$res['id'],
            'op_name' => '系统批量',
            'seller_state' => 2,
        );

        //所有退款状态
        if($data[4] != '同意') {
            $editParam['seller_state'] = 3;
        }
        if($this->edit_refund($editParam,$msg,true)){
            return true;
        }else{
            return $msg;
        }
    }
    
    //未发货订单退款，全额退款（取消订单）
    public function addRefundAll( $params, &$msg )
    {
    	$model_order = Model('order');
    	$model_trade = Model('trade');
    	$model_refund = Model('refund_return');
    	$order_id = intval($params['order_id']);
    	$condition = array();
    	//$condition['buyer_id'] = $params['member_id'];
    	$condition['order_id'] = $order_id;
    	$order = $model_refund->getRightOrderList($condition);
    	
    	//禁止退款金额
    	$lock_amount = Logic('order_book')->getDepositAmount($order);
    	$order['allow_refund_amount'] = $order['order_amount'] - $lock_amount;

    	$order_amount = $order['allow_refund_amount'];//订单金额
    	$condition = array();
    	$condition['buyer_id'] = $order['buyer_id'];
    	$condition['order_id'] = $order['order_id'];
    	$condition['goods_id'] = '0';
    	$condition['seller_state'] = array('lt','3');
    	$refund_list = $model_refund->getRefundReturnList($condition);
    	$refund = array();
    	if (!empty($refund_list) && is_array($refund_list)) {
    		$refund = $refund_list[0];
    	}
    	
    	$refund_array = array();
    	$refund_array['refund_type'] = '1';//类型:1为退款,2为退货
    	$refund_array['seller_state'] = '1';//状态:1为待审核,2为同意,3为不同意
    	$refund_array['order_lock'] = '2';//锁定类型:1为不用锁定,2为需要锁定
    	$refund_array['before_ship'] = '1';//发货前退款
    	$refund_array['goods_id'] = '0';
    	$refund_array['order_goods_id'] = '0';
    	$refund_array['reason_id'] = '0';
    	$refund_array['reason_info'] = '取消订单，全部退款';
    	$refund_array['goods_name'] = '订单商品全部退款';
    	$refund_array['refund_amount'] = ncPriceFormat($order_amount);
        $refund_array['admin_name'] = isset($params['admin_name']) ? $params['admin_name'] : '';
    	$refund_array['buyer_message'] = $params['buyer_message'] ? $params['buyer_message'] : "不想要了";
    	$refund_array['add_time'] = time();
    	$refund_array['operation_type']=!empty($params['operation_type'])?$params['operation_type']:0;
		if(!empty($params['pic_info'])){
			$refund_array['pic_info'] =$params['pic_info'];
		}

    	$state = $model_refund->addRefundReturn($refund_array,$order);
    	if (!is_array($state)&&$state) {
    		$model_refund->editOrderLock($order_id);
    		$msg = Language::get('nc_common_save_succ');
    		return true ;
    	} else {
    		$msg = Language::get('nc_common_save_fail').',重复维权';
    		return false ;
    	}
    }
    
    /**
     * 退款单审核
     * @params array
     */
    function edit_refund($params, &$msg = '',$master=false)
    {
        /** @var refund_returnModel $model_refund */
    	$model_refund = Model('refund_return') ;
    	$refund_id = intval($params['refund_id']) ;
    	$condition = array();
        $condition['refund_id'] = $refund_id;
        $refund_list = $model_refund->getRefundList($condition, '', 'refund_id desc', '',$master);
        $refund = $refund_list[0];
    	if( !$refund ) {
    		$msg = '没有找到退款记录！';
    		return false;
    	}
    	
    	if ($refund['seller_state'] != '1' || $refund['seller_state'] == $params['seller_state']) {//检查状态,防止页面刷新不及时造成数据错误
    		$msg = '重复操作';
    		return false;
    	}
    	
		$order_id = $refund['order_id'];
		$refund_array = array();
		$refund_array['seller_time'] = time();
		$refund_array['seller_state'] = $params['seller_state'];//卖家处理状态:1为待审核,2为同意,3为不同意
		$refund_array['seller_message'] = $params['seller_state'] == 2 ? '商家同意退款' : '商家拒绝/用户取消退款';
		if ($refund_array['seller_state'] == '3') {
			$refund_array['refund_state'] = '3';//状态:1为处理中,2为待管理员处理,3为已完成
		} else {
			$refund_array['seller_state'] = '2';
			$refund_array['refund_state'] = '2';
			$refund_array['kefu_state'] = '2';
		}
		
		$refund_array['refund_way'] = 'fenxiao' ;
		
		$condition = array();
		$condition['store_id'] = $refund['store_id'];
		$condition['refund_id'] = $refund_id;
		
		$state = $model_refund->editRefundReturn($condition, $refund_array);
		if ($state) {
			if ($refund_array['seller_state'] == '3' && $refund['order_lock'] == '2') {
				$res = $model_refund->editOrderUnlock($order_id);//订单解锁
				if( !$res ) {
					$msg = "订单解锁失败" ;
					return false ;
				}
			}
			
			$model_order = Model('order') ;
			$order = $model_order -> getOrderInfo( array('order_id' => $order_id) );
			if( $order && is_array($order) ){
				//添加订单日志
				$data = array();
				$data['order_id'] = $order_id;
				$data['log_role'] = 'system';
				$data['log_msg'] = $params['seller_state'] == 2 ? '同意了商家退款' : '拒绝退款/取消退款';
				$data['log_user'] = $params['op_name'];
				$data['log_orderstate'] = $order['order_state'];
				$res = $model_order->addOrderLog($data);
				if( !$res ) {
					$msg = "订单日志添加失败" ;
					return false ;
				}
			}

			$msg = "订单商家审核成功" ;
			return true ;
		} else {
			$msg = "修改退款单状态失败" ;
			return false ;
		}
    }
    
    /**
     * 平台确认退款
     * 对应分销确认退款，若汉购平台退款单商家未审核，先执行商家同意操作
     */
    function confirm_refund($params, &$msg = '')
    {
    	$model_refund = Model('refund_return');
        $condition = array();
        $condition['refund_id'] = intval($params['refund_id']);
        $refund = $model_refund->getRefundReturnInfo($condition);
        if( !$refund['order_id'] ){
        	$msg = '没有退款信息！';
        	return false;
        }
        
        if ($refund['seller_state'] == '3' || $refund['refund_state'] == '3') {//检查状态,防止页面刷新不及时造成数据错误
        	$msg = '商家已拒绝退款或管理员审核完成';
        	return false;
        }
        //商家未审核先做审核操作
        if( $refund['seller_state'] == '1' ) {
        	$_p = array(
					'refund_id' => intval($params['refund_id']) ,
					'op_name' => $params['op_name'],
    				'seller_state' => '2'
			) ;
        	$res = $this -> edit_refund($_p, $msg);
        	if( !$res ) {
        		return false;
        	}
        	$refund['seller_state'] = '2';
        }

        $order_id = $refund['order_id'];
        $model_order = Model('order');
        $order = $model_order->getOrderInfo(array('order_id'=> $order_id),array());
        if ($order['payment_time'] > 0) {
        	$order['pay_amount'] = $order['order_amount']-$order['rcb_amount']-$order['pd_amount'];//在线支付金额=订单总价格-充值卡支付金额-预存款支付金额
        }
        
    	$detail_array = $model_refund->getDetailInfo($condition);
    	if(empty($detail_array)) {
    		$model_refund->addDetail($refund,$order);
    		$detail_array = $model_refund->getDetailInfo($condition);
    	}
    	
    	if ($detail_array['pay_time'] > 0) {
    		$refund['pay_amount'] = $detail_array['pay_amount'];//已完成在线退款金额
    	}
    	
		$state = $model_refund->editOrderRefund($refund, $params['op_name']);
		if ($state) {
			$refund_array = array();
			$refund_array['admin_time'] = time();
			$refund_array['refund_state'] = '3';//状态:1为处理中,2为待管理员处理,3为已完成
			$refund_array['admin_message'] = '审核完成';
			
			if( !$model_refund->editRefundReturn($condition, $refund_array) ) {
				$msg = "平台确认退款记录修改失败" ;
				return false ;
			}
			if( $order['order_from'] != 3 ) {
	            /** @var CpsService $service */
	            $service = Service('Cps');
	            $service->refundOrder($params['refund_id']);
			}
			$msg = "平台确认退款成功" ;
			return true ;
		} else {
			$msg = "平台确认修改订单状态失败" ;
			return false ;
		}
    }
    
    /**
     * 未发货订单，分销平台同意了部分退款，汉购平台重新生成订单
     * @param $order array 原订单数据
     * @param $params array 新订单商品id，退款金额等
     */
    function reCreateOrder( $order, $params, &$msg )
    {
    	if( floatval($order['order_amount']) <= floatval($params['refund_amount']) ) {
    		$msg = '订单金额小于退款金额' ;
    		return false;
    	}
    	if( $order['order_state'] != 20 ) {
    		$msg = '不是未发货订单' ;
    		return false;
    	}
    	
    	$fx_order_id = $order['fx_order_id'] ;
    	if( !$fx_order_id ) {
    		$msg = '没有分销订单号' ;
    		return false; 
    	}
    	
    	$model_fenxiao = Model('b2c_order_fenxiao');
    	$model_fenxiao_sub = Model('b2c_order_fenxiao_sub');
    	$model_order_goods = Model('order_goods');
    	$order_fenxiao = $model_fenxiao -> where ( array( 'orderno' => $fx_order_id ) ) -> find() ;
    	if( !$order_fenxiao ) {
    		$msg = '没有分销单映射记录' ;
    		return false;
    	}
    	$order_fenxiao_sub = $model_fenxiao_sub -> where ( array( 'orderno' => $fx_order_id ) ) -> select() ;
    	if( !$order_fenxiao_sub ) {
    		$msg = '没有分销单子订单记录' ;
    		return false;
    	}
    	$order_goods = $model_order_goods -> where ( array( 'order_id' => $order['order_id'] ) ) -> select() ;
    	if( !$order_goods || !is_array($order_goods) || count($order_goods) == 1 ) {
    		$msg = '没有订单商品记录或只有一个商品' ;
    		return false;
    	}
    	$old_gids = array_column($order_goods, 'goods_id') ;
    	
    	$new_goods = array();
    	$goods_amount = 0;
    	//商品ID与分销子订单映射
    	$rel = array_column( $order_fenxiao_sub, 'oid', 'product_id') ;
    	foreach ($order_goods as $goods){
    		if( !in_array($goods['goods_id'], $params['gids']) ) continue ;
    		$goods_id = $goods['goods_id'] ;
    		if( !isset($rel[$goods_id]) || !$rel[$goods_id] ) {
    			$msg = "商品未匹配，商品ID：{$goods_id}" ;
    			return false;
    		}
    		$new_goods[] = array(
    				'goods_id' 	=> 	$goods['goods_id'], 
    				'num'		=> 	$goods['goods_num'], 
    				'price'		=>	$goods['goods_price'], 
    				'oid' 		=> 	$rel[$goods_id], 
    		) ;
    		$goods_amount += $goods['goods_pay_price'] ;
    	}
    	
    	$receiver = $order['extend_order_common']['reciver_name'];
    	$area = $order['extend_order_common']['reciver_info']['area'];
    	$mobile = $order['extend_order_common']['reciver_info']['mob_phone'];
    	$street = $order['extend_order_common']['reciver_info']['street'];
    	$tmp = explode(" ", $area);
    	$province = isset($tmp[0]) ? $tmp[0] : "";
    	$city = isset($tmp[1]) ? $tmp[1] : "";
    	$district = isset($tmp[2]) ? $tmp[2] : "";
    	
    	$new_order_amount = floatval($order['order_amount'] - $params['refund_amount']) ;
    	$oData['order_sn'] 		= 	$fx_order_id; 
    	$oData['buy_id'] 		= 	$order['buyer_id']; 
    	$oData['receiver']		=	$receiver;//收件人
    	$oData['provine'] 		= 	$province;
    	$oData['city'] 			=	$city;
    	$oData['area'] 			= 	$district;
    	$oData['address'] 		= 	$street;
    	$oData['mobile']		=	$mobile; //手机号码
    	$oData['remark'] 		= 	$order['extend_order_common']['order_message'];
    	$oData['amount'] 		= 	$new_order_amount;
    	$oData['payment_code'] 	= 	'fenxiao';//订单来源  fenxiao,jicai
    	$oData['order_time']	= 	$order['add_time'] ;
    	$oData['item'] 			= 	$new_goods;
    	$oData['discount'] 		= 	$goods_amount > $new_order_amount ? $goods_amount - $new_order_amount : '' ;
    	$oData['order_from'] = '3' ; //默认分销订单
    	$oData['key'] = C('order_create_key');
    	
    	try{
    		$model_fenxiao->beginTransaction();

    		//删除fenxiao表相关记录
    		$res = $model_fenxiao -> where ( array('orderno' => $fx_order_id) ) -> delete () ;
    		if( !$res ) {
    			throw new Exception( '删除分销表记录失败' );
    		}
    		$fxsubWhere ['orderno'] = $fx_order_id ;
    		$fxsubWhere ['product_id'] = array( 'in', $old_gids ) ;
    		$res = $model_fenxiao_sub -> where ( $fxsubWhere ) -> delete () ;
    		if( !$res ) {
    			throw new Exception( '删除分销子订单表记录失败' );
    		}
    		
    		//生成新订单
    		$orderModel = Model('order');
    		$oParams = json_encode($oData) ;
    		$res = $orderModel->createFxOrder( $oParams );
    		if( $res['error'] != '1000' ){
    			throw new Exception( $res['msg'] );
    		}
    		
    		//老订单全额退款
    		$this -> _refundOldorder($order) ;
    		
    		//刚生成的退款单审核通过
    		$this -> _refundPass( $order['order_id'], $params['op_name'] ) ;
    		
    		$model_fenxiao->commit();
    		$msg = "重新生成订单成功" ;
    		return true ;
    	} catch (Exception $e) {
    		$model_fenxiao->rollback();
    		$msg = $e->getMessage() ;
    		return false ;
    	}
    }
    
    private function _refundOldorder($order)
    {
    	$rData['reason_id'] = 99;
    	$rData['refund_type'] = 1;
    	$rData['return_type'] = 1;
    	$rData['seller_state'] = 1;
    	$rData['refund_amount'] = $order['order_amount'];
    	$rData['goods_num'] = 1;
    	$rData['buyer_message'] = '全额退款';
    	$rData['ordersn'] = $order['order_sn'];
    	$rData['goods_id'] = 100011;
    		
    	$refundModel = Model('refund_return') ;
    	$rParams = json_decode( json_encode($rData) ) ;
    	$res = $refundModel->addApiRefund( $rParams );
    	if( $res['errorno'] != '1000' ){
    		throw new Exception( $res['msg'] );
    	} 
    }
    private function _refundPass( $order_id, $op_name )
    {
    	$refundModel = Model('refund_return') ;
    	$result = $refundModel -> where ( array('order_id' => $order_id) ) -> order('refund_id desc') -> find () ;

    	/*$params = array(
					'refund_id' => $result['refund_id'] ,
					'seller_state' => '2',
					'op_name' => 'fenxiao'
			) ;
    	$res = $this -> edit_refund($params, $msg) ;
    	if( !$res ) {
    		throw new Exception( "商家审核：" . $msg );
    	}*/
    	
    	$params = array(
    			'refund_id' => $result['refund_id'] ,
    			'op_name' => $op_name,
    	) ;
    	$res = $this -> confirm_refund($params, $msg) ;
    	if( !$res ) {
    		throw new Exception(  "平台审核：" . $msg );
    	}
    }

    private function _excelToArray($filePath = '', $sheet = 0)
    {
        if (empty($filePath) or !file_exists($filePath)) {
            return false;
        }
        $fileType = explode('.',$filePath);
        $fileType = $fileType[count($fileType)-1];

        //csv类型直接str_getcsv转换
        if(strtolower($fileType) == 'csv'){
            $lines = array_map('str_getcsv', file($filePath));;
            $result = array();
            for ($i = 0; $i < count($lines); $i++) {        //循环读取每行内容注意行从第1行开始($i=0)
                $obj = $lines[$i];
                foreach ($obj as $k => $v) {
                    $result[$i][] = mb_convert_encoding($v, 'UTF-8', 'gbk');
                }
            }
            return $result;
        }

        //excel类型 PHPExcel类库转换
        vendor('PHPExcel/Reader/Excel2007');
        vendor('PHPExcel/Reader/Excel5');
        $PHPReader = new PHPExcel_Reader_Excel2007();        //建立reader对象
        if (!$PHPReader->canRead($filePath)) {
            $PHPReader = new PHPExcel_Reader_Excel5();
            if (!$PHPReader->canRead($filePath)) {
                return false;
            }
        }
        $PHPExcel = $PHPReader->load($filePath);
        $currentSheet = $PHPExcel->getSheet($sheet);            //读取excel文件中的指定工作表
        $allColumn = $currentSheet->getHighestColumn();         //*取得最大的列号
        $allRow = $currentSheet->getHighestRow();               //取得一共有多少行
        $data = array();
        for ($rowIndex = 1; $rowIndex <= $allRow; $rowIndex++) {        //循环读取每个单元格的内容。注意行从第1行开始，列从A开始
            for ($colIndex = 'A'; $colIndex <= $allColumn; $colIndex++) {
                $addr = $colIndex . $rowIndex;
                $cell = $currentSheet->getCell($addr)->getValue();
                if ($cell instanceof PHPExcel_RichText) {       //转换字符串
                    $cell = $cell->__toString();
                }
                $data[$rowIndex-1][] = $cell;
            }
        }
        return $data;
    }

}