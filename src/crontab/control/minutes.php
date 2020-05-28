<?php
/**
 * 任务计划 - 分钟执行的任务
 *
 *
 *
 *
 * @汉购网提供技术支持 授权请购买shopnc授权
 * @license    http://www.hangowa.com
 * @link       交流群号：
 */
defined ( 'ByShopWWI' ) or exit ( 'Access Invalid!' );
class minutesControl extends BaseCronControl {
	/**
	 * 默认方法
	 */
	public function indexOp() {
		$this->_cron_common ();
		$this->_web_index_update ();
		$this->_cron_mail_send ();
		// 更新浏览量
		//$this->_goods_browse_update ();
        $this->_checkWorkflow();
        $this->_goodsStateNotify();
    }
	
	/**
	 * 更新首页的商品价格信息
	 */
	private function _web_index_update() {
		Model ( 'web_config' )->updateWebGoods ();
	}
	
	/**
	 * 发送邮件消息
	 */
	private function _cron_mail_send() {
		// 每次发送数量
		$_num = 50;
		$model_storemsgcron = Model ( 'mail_cron' );
		$cron_array = $model_storemsgcron->getMailCronList ( array (), $_num );
		if (! empty ( $cron_array )) {
			$email = new Email ();
			$mail_array = array ();
			foreach ( $cron_array as $val ) {
				$return = $email->send_sys_email ( $val ['mail'], $val ['subject'], $val ['contnet'] );
				if ($return) {
					// 记录需要删除的id
					$mail_array [] = $val ['mail_id'];
				}
			}
			// 删除已发送的记录
			$model_storemsgcron->delMailCron ( array (
					'mail_id' => array (
							'in',
							$mail_array 
					) 
			) );
		}
	}
	
	/**
	 * 执行通用任务
	 */
	private function _cron_common() {
		// 查找待执行任务
		$model_cron = Model ( 'cron' );
		$cron = $model_cron->getCronList ( array (
				'exetime' => array (
						'elt',
						TIMESTAMP 
				) 
		), 1000 );
		if (! is_array ( $cron ))
			return;
		$cron_array = array ();
		$cronid = array ();
		foreach ( $cron as $v ) {
			$cron_array [$v ['type']] [$v ['exeid']] = $v;
		}
		foreach ( $cron_array as $k => $v ) {
			// 如果方法不存是，直接删除id
			if (! method_exists ( $this, '_cron_' . $k )) {
				$tmp = current ( $v );
				$cronid [] = $tmp ['id'];
				continue;
			}
			$result = call_user_func_array ( array (
					$this,
					'_cron_' . $k 
			), array (
					$v 
			) );
			if (is_array ( $result )) {
				$cronid = array_merge ( $cronid, $result );
			}
		}
		// 删除执行完成的cron信息
		if (! empty ( $cronid ) && is_array ( $cronid )) {
			$model_cron->delCron ( array (
					'id' => array (
							'in',
							$cronid 
					) 
			) );
		}
	}
	
	/**
	 * 上架
	 *
	 * @param array $cron        	
	 */
	private function _cron_1($cron = array()) {
		$condition = array (
				'goods_commonid' => array (
						'in',
						array_keys ( $cron ) 
				) 
		);
		$update = Model ( 'goods' )->editProducesOnline ( $condition );
		if ($update) {
			// 返回执行成功的cronid
			$cronid = array ();
			foreach ( $cron as $v ) {
				$cronid [] = $v ['id'];
			}
		} else {
			return false;
		}
		return $cronid;
	}
	
	/**
	 * 根据商品id更新商品促销价格
	 *
	 * @param array $cron        	
	 */
	private function _cron_2($cron = array()) {
		$condition = array (
				'goods_id' => array (
						'in',
						array_keys ( $cron ) 
				) 
		);
		$update = Model ( 'goods' )->editGoodsPromotionPrice ( $condition );
		if ($update) {
			// 返回执行成功的cronid
			$cronid = array ();
			foreach ( $cron as $v ) {
				$cronid [] = $v ['id'];
			}
		} else {
			return false;
		}
		return $cronid;
	}
	
	/**
	 * 优惠套装过期
	 *
	 * @param array $cron        	
	 */
	private function _cron_3($cron = array()) {
		$condition = array (
				'store_id' => array (
						'in',
						array_keys ( $cron ) 
				) 
		);
		$update = Model ( 'p_bundling' )->editBundlingQuotaClose ( $condition );
		if ($update) {
			// 返回执行成功的cronid
			$cronid = array ();
			foreach ( $cron as $v ) {
				$cronid [] = $v ['id'];
			}
		} else {
			return false;
		}
		return $cronid;
	}
	
	/**
	 * 推荐展位过期
	 *
	 * @param array $cron        	
	 */
	private function _cron_4($cron = array()) {
		$condition = array (
				'store_id' => array (
						'in',
						array_keys ( $cron ) 
				) 
		);
		$update = Model ( 'p_booth' )->editBoothClose ( $condition );
		if ($update) {
			// 返回执行成功的cronid
			$cronid = array ();
			foreach ( $cron as $v ) {
				$cronid [] = $v ['id'];
			}
		} else {
			return false;
		}
		return $cronid;
	}
	
	/**
	 * 团购开始更新商品促销价格
	 *
	 * @param array $cron        	
	 */
	private function _cron_5($cron = array()) {
		$condition = array ();
		$condition ['goods_commonid'] = array (
				'in',
				array_keys ( $cron ) 
		);
		$condition ['start_time'] = array (
				'lt',
				TIMESTAMP 
		);
		$condition ['end_time'] = array (
				'gt',
				TIMESTAMP 
		);
		$groupbuy = Model ( 'groupbuy' )->getGroupbuyList ( $condition );
		foreach ( $groupbuy as $val ) {
			Model ( 'goods' )->editGoods ( array (
					'goods_promotion_price' => $val ['groupbuy_price'],
					'goods_promotion_type' => 1 
			), array (
					'goods_commonid' => $val ['goods_commonid'] 
			) );
		}
		// 返回执行成功的cronid
		$cronid = array ();
		foreach ( $cron as $v ) {
			$cronid [] = $v ['id'];
		}
		return $cronid;
	}

	/**
	 * 团购过期
	 *
	 * @param array $cron        	
	 */
	private function _cron_6($cron = array()) {
		$condition = array (
				'goods_commonid' => array (
						'in',
						array_keys ( $cron ) 
				) 
		);
		// 团购活动过期
		$update = Model ( 'groupbuy' )->editExpireGroupbuy ( $condition );
		if ($update) {
			// 返回执行成功的cronid
			$cronid = array ();
			foreach ( $cron as $v ) {
				$cronid [] = $v ['id'];
			}
		} else {
			return false;
		}
		return $cronid;
	}
	
	/**
	 * 限时折扣过期
	 *
	 * @param array $cron        	
	 */
	private function _cron_7($cron = array()) {
		$condition = array (
				'xianshi_id' => array (
						'in',
						array_keys ( $cron ) 
				) 
		);
		// 限时折扣过期
		$update = Model ( 'p_xianshi' )->editExpireXianshi ( $condition );
		if ($update) {
			// 返回执行成功的cronid
			$cronid = array ();
			foreach ( $cron as $v ) {
				$cronid [] = $v ['id'];
			}
		} else {
			return false;
		}
		return $cronid;
	}
	
	/**
	 * 加价购过期
	 *
	 * @param array $cron        	
	 */
	private function _cron_8($cron = array()) {
		$condition = array (
				'id' => array (
						'in',
						array_keys ( $cron ) 
				) 
		);
		// 过期
		$update = Model ( 'p_cou' )->editExpireCou ( $condition );
		if ($update) {
			// 返回执行成功的cronid
			$cronid = array ();
			foreach ( $cron as $v ) {
				$cronid [] = $v ['id'];
			}
		} else {
			return false;
		}
		return $cronid;
	}

	/**
	 * 更新店铺（新增）商品消费者保障服务开启状态（如果商品在店铺开启保障服务之后增加则需要执行该任务更新其服务状态）
	 * 
	 * @param array $cron        	
	 */
	private function _cron_9($cron = array()) {
		// 查询商品详情
		$model_goods = Model ( 'goods' );
		$where = array ();
		$where ['goods_commonid'] = array (
				'in',
				array_keys ( $cron ) 
		);
		$goods_list = $model_goods->getGoodsList ( $where, 'goods_id,goods_commonid,store_id' );
		if (! $goods_list) {
			return false;
		}
		$store_goods_list = array ();
		foreach ( $goods_list as $k => $v ) {
			$store_goods_list [$v ['store_id']] [$v ['goods_id']] = $v;
		}
        $cronid = array ();
        // 查询店铺的保障服务
		$where = array ();
		$where ['ct_storeid'] = array (
				'in',
				array_keys ( $store_goods_list ) 
		);
		$model_contract = Model ( 'contract' );
		$c_list = $model_contract->getContractList ( $where );
		if (empty($c_list)) {
            foreach ( $cron as $k => $v ) {
                $cronid [] = $v ['id'];
            }
            return $cronid;
        }
		$goods_contractstate_arr = $model_contract->getGoodsContractState ();
		$c_list_tmp = array ();
		foreach ( $c_list as $k => $v ) {
			if ($v ['ct_joinstate_key'] == 'added' && $v ['ct_closestate_key'] == 'open') {
				$c_list_tmp [$v ['ct_storeid']] [$v ['ct_itemid']] = $goods_contractstate_arr ['open'] ['sign'];
			} else {
				$c_list_tmp [$v ['ct_storeid']] [$v ['ct_itemid']] = $goods_contractstate_arr ['close'] ['sign'];
			}
		}
		
		// 整理更新数据
		$goods_commonidarr = array ();
		foreach ( $c_list_tmp as $s_k => $s_v ) {
			$update_arr = array ();
			foreach ( $s_v as $item_k => $item_v ) {
				$update_arr ["contract_$item_k"] = $item_v;
			}
			$result = $model_goods->editGoodsById ( $update_arr, array_keys ( $store_goods_list [$s_k] ) );
			if ($result) {
				foreach ( $store_goods_list [$s_k] as $g_k => $g_v ) {
					$goods_commonidarr [] = $g_v ['goods_commonid'];
				}
				array_unique ( $goods_commonidarr );
			}
		}
		
		if ($goods_commonidarr) {
			// 返回执行成功的cronid
			foreach ( $cron as $k => $v ) {
				if (in_array ( $k, $goods_commonidarr )) {
					$cronid [] = $v ['id'];
				}
			}
		}
		if (!empty($cronid)) {
			// 返回执行成功的cronid
			return $cronid;
		} else {
			return false;
		}
	}
	
	/**
 * 手机专享过期
 *
 * @param array $cron
 */
	private function _cron_10($cron = array()) {
		$condition = array (
				'store_id' => array (
						'in',
						array_keys ( $cron )
				)
		);
		$update = Model ( 'p_sole' )->editSoleClose ( $condition );
		if ($update) {
			// 返回执行成功的cronid
			$cronid = array ();
			foreach ( $cron as $v ) {
				$cronid [] = $v ['id'];
			}
		} else {
			return false;
		}
		return $cronid;
	}

	/**
	 * 将缓存中的浏览记录存入数据库中，并删除30天前的浏览历史
	 */
	private function _goods_browse_update() {
		$model = Model ( 'goods_browse' );
		// 将cache中的记录存入数据库
		if (C ( 'cache_open' )) { // 如果浏览记录已经存入了缓存中，则将其整理到数据库中
		                      // 上次更新缓存的时间
			$latest_record = $model->getGoodsbrowseOne ( array (), '', 'browsetime desc' );
			$starttime = ($t = intval ( $latest_record ['browsetime'] )) ? $t : 0;
			$monthago = strtotime ( date ( 'Y-m-d', time () ) ) - 86400 * 30;
			$model_member = Model ( 'member' );
			
			// 查询会员信息总条数
			$countnum = $model_member->getMemberCount ( array () );
			$eachnum = 100;
			for($i = 0; $i < $countnum; $i += $eachnum) { // 每次查询100条
				$member_list = $model_member->getMemberList ( array (), '*', 0, 'member_id asc', "$i,$eachnum" );
				foreach ( ( array ) $member_list as $k => $v ) {
					$insert_arr = array ();
					$goodsid_arr = array ();
					// 生成缓存的键值
					$hash_key = $v ['member_id'];
					$browse_goodsid = rcache ( $hash_key, 'goodsbrowse', 'goodsid' );
					
					if ($browse_goodsid) {
						// 删除缓存中多余的浏览历史记录，仅保留最近的30条浏览历史，先取出最近30条浏览历史的商品ID
						$cachegoodsid_arr = $browse_goodsid ['goodsid'] ? unserialize ( $browse_goodsid ['goodsid'] ) : array ();
						unset ( $browse_goodsid ['goodsid'] );
						
						if ($cachegoodsid_arr) {
							$cachegoodsid_arr = array_slice ( $cachegoodsid_arr, - 30, 30, true );
						}
						// 处理存入数据库的浏览历史缓存信息
						$_cache = rcache ( $hash_key, 'goodsbrowse' );
						foreach ( ( array ) $_cache as $c_k => $c_v ) {
							$c_v = unserialize ( $c_v );
							if (empty ( $c_v ['goods_id'] ))
								continue;
							if ($c_v ['browsetime'] >= $starttime) { // 如果 缓存中的数据未更新到数据库中（即添加时间大于上次更新到数据库中的数据时间）则将数据更新到数据库中
								$tmp_arr = array ();
								$tmp_arr ['goods_id'] = $c_v ['goods_id'];
								$tmp_arr ['member_id'] = $v ['member_id'];
								$tmp_arr ['browsetime'] = $c_v ['browsetime'];
								$tmp_arr ['gc_id'] = $c_v ['gc_id'];
								$tmp_arr ['gc_id_1'] = $c_v ['gc_id_1'];
								$tmp_arr ['gc_id_2'] = $c_v ['gc_id_2'];
								$tmp_arr ['gc_id_3'] = $c_v ['gc_id_3'];
								$insert_arr [] = $tmp_arr;
								$goodsid_arr [] = $c_v ['goods_id'];
							}
							// 除了最近的30条浏览历史之外多余的浏览历史记录或者30天之前的浏览历史从缓存中删除
							if (! in_array ( $c_v ['goods_id'], $cachegoodsid_arr ) || $c_v ['browsetime'] < $monthago) {
								unset ( $_cache [$c_k] );
							}
						}
						// 删除已经存在的该商品浏览记录
						if ($goodsid_arr) {
							$model->delGoodsbrowse ( array (
									'member_id' => $v ['member_id'],
									'goods_id' => array (
											'in',
											$goodsid_arr 
									) 
							) );
						}
						// 将缓存中的浏览历史存入数据库
						if ($insert_arr) {
							$model->addGoodsbrowseAll ( $insert_arr );
						}
						// 重新赋值浏览历史缓存
						dcache ( $hash_key, 'goodsbrowse' );
						$_cache ['goodsid'] = serialize ( $cachegoodsid_arr );
						wcache ( $hash_key, $_cache, 'goodsbrowse' );
					}
				}
			}
		}
		// 删除30天前的浏览历史
		$model->delGoodsbrowse ( array (
				'browsetime' => array (
						'lt',
						$monthago 
				) 
		) );
	}

    /**
     * 检查审批流超时任务
     * @return null
     */
	private function _checkWorkflow()
    {
        $hour = date('H');
        if($hour>22||$hour<8) return null; // 晚上22点到次日8点之间不发邮件
        $cache = rkcache('workflow_email');
        if($cache!=false&&$cache>TIMESTAMP - 15*60) return null;// 没有超过15分钟，不继续
        wkcache('workflow_email',TIMESTAMP);
        // 查找所有超时审批任务
        /** @var workflowModel $workflowModel */
        $threshold = TIMESTAMP-3600*2;
        $workflowModel = Model('workflow');
        $workflowList = $workflowModel->getWorkflowList(array('timeout_at'=>array('lt',$threshold),'status'=>$workflowModel::STATUS_PROCESSING));

        /** @var adminModel $adminModel */
        $adminModel = Model('admin');
        $gadminList = $workflowModel->table('gadmin')->select();
        $gadminList = array_column($gadminList,'gid','gname');
        $adminGroupCache = array();
        if(empty($workflowList)) return null;
        $email	= new Email();
        foreach ($workflowList as $workflow){
            if(!isset($gadminList[$workflow['stage']])) continue;
            $gid = $gadminList[$workflow['stage']];
            if(!isset($adminGroupCache[$gid])){
                $adminGroupCache[$gid] = $adminModel->getAdminList(array('admin_gid'=>$gid),false);
            }
            $adminList = $adminGroupCache[$gid];
            if(empty($adminList)) continue;
            foreach ($adminList as $admin){
                if(empty($admin['admin_email'])) continue;
//                $this->log("给{$admin['admin_email']}发送Email：审核超时提醒：{$workflow['title']}");
                continue;
                $res = $email->send_sys_email($admin['admin_email'],"审核超时提醒：{$workflow['title']}",<<<HTML
<p>尊敬的{$admin['admin_name']}：您好！</p>
<p>您有新的待处理审批尚未处理：《{$workflow['title']}》</p>
<p>请立即登录登陆系统，完成商家类型修改。</p>
HTML
);
            }
        }
    }

    private function _goodsStateNotify(){
	    // 获取商品列表
        /** @var seller_logModel $logModel */
        $logModel = Model('seller_log');
        $logs = $logModel->getSellerLogList(array('notify_state'=>0,'log_type'=>array('in',array(4,5))));

        /** @var adminModel $adminModel */
        $adminModel = Model('admin');
        $adminList = $adminModel->getAdminList(array('admin_gid'=>6),false);
        $email	= new Email();
        foreach ($logs as $log){
            $title='商品信息变更提醒：'.$log['log_content'];
            foreach ($adminList as $admin){
                if(empty($admin['admin_email'])) continue;
//                $res = $email->send_sys_email($admin['admin_email'],$title,<<<HTML
//<p>尊敬的{$admin['admin_name']}：您好！</p>
//<p>以下商品信息发生变更：《{$log['log_content']}》</p>
//<p>请知悉。</p>
//HTML
//                );
//                if($res) $logModel->where(array('log_id'=>$log['log_id']))->update(array('notify_state'=>0));
            }
        }
        return true;
    }

    /***
     * 电子面单自动发货
     */
    public function  dzmdPushOp(){
        $condition = array(
            'ship_status'=>0,
        );
        $printship_log_list = Model('print_ship')->getPrintShipLogList($condition, '', '1000');
        $express_model = model('express');
        $store_ids = array();
        foreach($printship_log_list as $item => $value){
            $store_ids[] = $value['store_id'];
        }
        if(count($store_ids)>0){
            array_unique($store_ids);
            $store_config = Model('kdn_config')->where(array('store_id'=>array('in' , $store_ids)))->limit(false)->select();
            $store_config = array_under_reset($store_config , 'store_id');
            foreach($printship_log_list as $item=>$value){
                $ret = $express_model->push_ship($value['order_info'] , $store_config[$value['store_id']]);
                $ret = json_decode($ret ,true);
                Model('print_ship')->setPrintShipRequest($value['id'] , $value['order_sn'] ,$ret);
            }
        }
    }

	public function senduser(){
		$userdata=array(
				'yixinlin',
				'lijingquan',
		);
		return $userdata;
	}


	public function sendEmailOp(){
		$onlinetime='2017-7-18 17:50:00';//上线时间
		$condition['send_time'] = array('gt',strtotime($onlinetime));
		$condition['order_status'] = 2;
        if(isset($_GET['begin']) && isset($_GET['end'])){
		 	$condition['send_time']=array(array('gt',strtotime($_GET['begin'])),array('lt',strtotime($_GET['end'])+86400),'and');
		}
		$data = Model('sendorder_record')->field('id,shipping_code,source,fx_order_id,order_sn,send_time,sourceid')->where($condition)->limit(false)->select();
		$member_fenxiao = Model('member_fenxiao')->getMembeFenxiaoList('',false);
		$member_fenxiao = array_under_reset($member_fenxiao ,'member_id');
        if(empty($data))  die("暂无要发货错误日志");
		$sort=array_column($data,'sourceid');
		array_multisort($sort,SORT_DESC,$data);
		$message="<table cellspacing='0' cellpadding='0' border='1' style='word-break:keep-all;'><tr><th width='70'>渠道名</th><th width='150'>分销订单号</th><th width='150'>物流单号</th><th width='200'>汉购网订单号</th><th width='180'>出错时间</th><tr/>";
		foreach($data as $item){
		$message.="<tr align='center'><td width='120'>".$member_fenxiao[$item['sourceid']]['member_cn_code']."</td><td>{$item['fx_order_id']}</td><td>".$item['shipping_code']."</td><td>{$item['order_sn']}</td><td>".date('Y-m-d H:i:s',$item['send_time'])."</td></tr>";
		}
		if(empty($data))  die("暂无要发货错误日志");
		$message.="</table>";
		$site_title=C('site_name');
		if(isset($_GET['begin']) && isset($_GET['end'])){
			$site_title=C('site_name')."查询".$_GET['begin']."到".$_GET['end'];
		}
		//获取系统发送邮件配置
        $email=Model('setting')->getListSetting();
		$obj_email=new Email();
		$obj_email->set('email_server',$email['email_host']);
		$obj_email->set('email_port',$email['email_port']);
		$obj_email->set('email_user',$email['email_id']);
		$obj_email->set('email_password',$email['email_pass']);
		$obj_email->set('email_from',$email['email_id']);
		$obj_email->set('site_name',$site_title);
		$senduser=$this->senduser();
		$n=0;
		foreach($senduser as $item){
			$mail=$item."@hansap.com";
			$result=$obj_email->send($mail,'渠道发货失败提示邮件',$message);
			if(!$result){
				$result1=$obj_email->send($mail,'渠道发货失败提示邮件',$message);
				if(!$result1)  {
					$n+=1;
					Log::record($item."【".$site_title."】"."发送失败");
				}
			}
		}

		if($n==count($senduser)){
			Log::record("渠道邮件全部发送失败，请查找原因");
			exit();
		}

		$ids = implode(",", array_column($data,'id'));
		$senddata=Model("sendorder_record");
		$res=$senddata->updatedata(array('id'=>array("in",$ids)),array('order_status'=>3));
		if (!$res) {
			Log::record('渠道发货更新失败提示:id'.implode(',',$ids));
		}
	}

	public function testOp(){
        $this->_goodsStateNotify();
    }

	public function  updatesuningpriceOp(){
		$condition['buyer_name']='suningnonggu';
		$condition['add_time']=array('between','1501810200,1506052800');
		$orderdata=Model('orders')->field('fx_order_id')->where($condition)->limit(false)->select();
		$suningdata=Model('suningorder')->where()->limit(false)->select();
		$count=count($suningdata);
		$pagesize = 500;
		$pagenum  = ceil($count/$pagesize);
		$sucnum=0;
		$errornum=0;
		$arr=array();
		$more_arr=array();
		if($count==0){
			exit('暂无数据');
		}
		$order_ids=array_column($orderdata,'fx_order_id');
		for($i =1 ;$i < $pagenum ; $i++) {
			$start = ($i - 1) * $pagesize;
			$dataList = array_slice($suningdata,$start,$pagesize);
			foreach($dataList as $item){
				$fx_order_id=trim($item['fx_order_id']);
				if(in_array($fx_order_id,$order_ids)){
					$cond['buyer_name']='suningnonggu';
					$cond['fx_order_id']=$fx_order_id;
					$ordernum=Model('orders')->where($cond)->limit(false)->count();
					if($ordernum>1){
						array_push($more_arr,$fx_order_id);
						continue;
					}
					$where['buyer_name']='suningnonggu';
					$where['fx_order_id']=$fx_order_id;
					$data['order_amount']=$item['price'];
					$res=Model('orders')->where($where)->update($data);
					if($res){
						$sucnum++;
					}else{
						$errornum++;
						array_push($arr,$fx_order_id);
					}
					}
			}
		}
		echo "订单总数量:";
		echo count($orderdata);
		echo "<pre>";
		echo "成功修改的数量:";
		echo $sucnum;
		echo "<pre>";
		echo "失败的数量:";
		echo $errornum;
		echo "<pre>";
		echo "失败的fx_order_id";
		echo "<pre>";
		print_r($arr);
		echo "多个商品的订单fx_order_id";
		echo "<pre>";
		print_r($more_arr);
	}

    /**
     * 更新账单付款信息
     */
    public function updateBillsPayableOp(){
        /** @var billModel $billModel */
        $billModel = Model("bill");

        $bills = $billModel->getOrderBillList(
            array(
                'ob_state' => array('in', array(BILL_STATE_FIRE_PHONIX, BILL_STATE_HANGO, BILL_STATE_CEO,BILL_STATE_PART_PAY,BILL_STATE_PAYING)),//11,10,12,5,13
                'jdy_state' => array('gt', billModel::JDY_STATE_NEW),
                'pay_update_time' => array('lt', time() - 3600),
                'ob_start_date' => array('gt', strtotime("2019-05-01"))
            ), '*', 1000);
        if (empty($bills)) return true;
        $obIds = array_column($bills, 'ob_id');
        $billModel->editOrderBill(
            array('pay_update_time' => time()),
            array('ob_id' => array('in', $obIds))
        );
        // 查询100个结算单的付款状态
        foreach ($bills as $bill){
            $this->_updateBillTraffic($bill);
        }
        return true;
    }

    private function _updateBillTraffic($bill){
        // 查找付款单号
        /** @var jdy_entryModel $entryModel */
        $entryModel = Model("jdy_entry");
        $entries = $entryModel->getList(array('ob_id'=>$bill['ob_id']),'','',"*",999999);

        $purchase_sns = array();
        $suppliers = array();
        foreach ($entries as $entry){
            if (!isset($suppliers[$entry['jdy_supplier_number']])) $suppliers[$entry['jdy_supplier_number']] = array();
            $suppliers[$entry['jdy_supplier_number']][] = $entry;
            $entry['jdy_purchase_number'] && $purchase_sns[]= $entry['jdy_purchase_number'];
        }

        if(empty($purchase_sns)) return true;
        $paidAmount = 0;

        foreach ($purchase_sns as $purchase_sn){
            if (empty($purchase_sn)) continue;
            $billTraffic = $this->_getBillTraffic($purchase_sn);
            $traffics = $billTraffic['datas']['traffics'];
            $purchase = $billTraffic['datas']['purchase'];
            if (empty($traffics)) continue;
            foreach ($traffics as $traffic){
                $paidAmount += $traffic['amount'];
                $this->_addTraffic($bill,$traffic,$purchase);
            }
        }

        //根据流水判断是否支付完成
        /** @var billModel $billModel */
        $billModel = Model("bill");
        $fee = 0;
        if($bill['ob_commis_totals']<=0){
            $fee = $bill['ob_result_totals']*0.006;
        }
        $total = $bill['ob_result_totals']-$fee;
        if ($paidAmount>=$total){
            $billModel->editOrderBill(array(
                'ob_state'=>BILL_STATE_SUCCESS,
                'paid_amount'=>$paidAmount,
                'ob_pay_date'=>time(),
                'ob_pay_content'=>'供应链资金管理支付完成，明细详见付款记录',
            ),array('ob_id'=>$bill['ob_id']));
        }elseif ($paidAmount>0&&$bill['ob_state']!=BILL_STATE_PART_PAY){
            $billModel->editOrderBill(array(
                'ob_state'=>BILL_STATE_PART_PAY,
                'paid_amount'=>$paidAmount,
                'ob_pay_date'=>time(),
                'ob_pay_content'=>'供应链资金管理支付完成，明细详见付款记录',
            ),array('ob_id'=>$bill['ob_id']));
        }
        return true;
    }

    private function _getBillTraffic($purchase_sn){
        $host = 'https://apisupplier.hangomart.com';
        if (C('ON_DEV')) $host = 'http://api.hangoshop.com';
        $url = $host.'/payable/traffic/';
        import("Curl");
        $curl = new Curl();
        $curl->setJsonDecoder(function ($response){
            $json_obj = json_decode($response, true);
            if (!($json_obj === null)) {
                $response = $json_obj;
            }
            return $response;
        });
        $res = $curl->get($url,array('purchase_sn'=>$purchase_sn,'api_key'=>'c1dca569396ba260fe6a7d552b6b7d75'));
        return $res;
    }

    private function _addTraffic($bill,$traffic,$purchase){
        if($traffic['amount']<=0) return;
        /** @var order_bill_logModel $trafficModel */
        $trafficModel = Model("order_bill_log");
        $hasTraffic = $trafficModel->getCount(array('traffic_id'=>$traffic['id']));
        if ($hasTraffic>0) return true;
        $traffic = array(
            'obl_ob_id'=>$bill['ob_id'],
            'obl_pay_date'=>$traffic['created_at'],
            //'obl_err_amount'=>$traffic['amount'],
            'obl_success_amount'=>$traffic['amount'],
            'obl_pay_content'=>$traffic['memo'],
            'traffic_id'=>$traffic['id'],
            'attachment'=>$traffic['attachment'],
            'payment_sn'=>$traffic['payment_sn'],
            'purchase_sn'=>$purchase['purchase_sn'],
            'supplier_name'=>$purchase['supplier_name'],
        );
        $res = $trafficModel->add($traffic);
        return $res;
    }
}
