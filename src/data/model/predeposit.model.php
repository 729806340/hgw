<?php
/**
 * 预存款
 * * @汉购网 (c) 2015-2018 ShopWWI Inc. (http://www.hangowa.com)
 * @license    http://www.shopwwi.c om
 * @link       交流群号：
 * @since      汉购网提供技术支持 授权请购买shopnc授权
 */
defined('ByShopWWI') or exit('Access Invalid!');
class predepositModel extends Model {
    /**
     * 生成充值编号
     * @return string
     */
    public function makeSn() {
       return mt_rand(10,99)
              . sprintf('%010d',time() - 946656000)
              . sprintf('%03d', (float) microtime() * 1000)
              . sprintf('%03d', (int) $_SESSION['member_id'] % 1000);
    }

    public function addRechargeCard($sn, $pwd, array $session)
    {
        $memberId = (int) $session['member_id'];
        $memberName = $session['member_name'];

        if ($memberId < 1 || !$memberName) {
            throw new Exception("当前登录状态为未登录，不能使用充值卡");
        }
        dcache($memberId,'member');

        if(C('OLD_STATUS') == true){
            return $this->addEcRechargeCard($sn,$pwd,$memberId);
        }
        $rechargecard_model = Model('rechargecard');

        $card = $rechargecard_model->getRechargeCardBySN($sn);
        if (empty($card) || $card['state'] != 0 || $card['member_id'] != 0) {
            throw new Exception("充值卡不存在或已被使用");
        }

        if (!empty($card['pwd'])&&$card['pwd'] != $pwd) {
            throw new Exception("充值卡密码错误！");
        }

        if (!$card['disabled']) {
            throw new Exception("充值卡未激活！");
        }
        $lockKey = 'rcb_card_'.$sn;
        $cardLock = rkcache($lockKey);
        if($cardLock) throw new Exception("系统繁忙！");
        wkcache($lockKey,1,60);
        $card['member_id'] = $memberId;
        $card['member_name'] = $memberName;

        try {
            $this->beginTransaction();
            $rechargecard_model->setRechargeCardUsedById($card['id'], $memberId, $memberName);
            $card['amount'] = $card['denomination'];
            $this->changeRcb('recharge', $card);
            $this->commit();
            dkcache($lockKey);
        } catch (Exception $e) {
            $this->rollback();
            dkcache($lockKey);
            throw new Exception($e->getMessage());
        }
    }

    protected function addEcRechargeCard($sn, $pwd,$member_id)
    {
        import('Curl');
        $curl = new Curl();
        $sign = C('EC_SIGN');
        $secret = C('EC_SECRET');
        $time = TIMESTAMP;
        $code = md5("sign=$sign&secret=$secret&time=$time");
        $query = array(
            'method'=>'b2c.system.add_advance',
            'code'=>$code,
            'sign'=>$sign,
            'time'=>$time,
            'id'=>$member_id,
            'sn'=>$sn,
            'pwd'=>$pwd,
        );
        $curl->get(C('EC_API_HOST').http_build_query($query));
        $content = json_decode($curl->response,true);
        if($content['data'] != 100) throw new Exception($content['res']);
        //return true;
        //  充值成功，同步充值日志
        $ecLogModel = ecModel('B2cMemberAdvance');
        $ecLog = $ecLogModel->where(array('member_id'=>$member_id,'explode_money'=>0.00))->order('mtime desc')->find();
        if(empty($ecLog)) return true;
        $log = copyTo($ecLog, array('log_id' => 'id', 'member_id' => 'member_id', 'mtime' => 'add_time', 'shop_advance' => 'shop_amount'));
        $log['available_amount'] = $ecLog['import_money'] - $ecLog['explode_money'];
        $member = TModel('Member')->find($ecLog['member_id']);
        $log['member_name'] = $member['member_name']?:'N/A';
        $payMethod = $ecLog['paymethod']=='alipay'?'支付宝':'预存款';
        if ($log['available_amount'] > 0) {
            if (strpos($ecLog['message'], '退款')!==false) {
                $log['type'] = 'refund';
                $log['description'] = $ecLog['message'].' 订单号：'.$ecLog['order_id'].' 支付单号：'.$ecLog['payment_id'].' 支付方式：'.$payMethod;
            }else{
                $log['type'] = 'recharge';
                $log['description'] = $ecLog['message'].' 支付单号：'.$ecLog['payment_id'].' 支付方式：'.$payMethod;
            }
        } else {
            $log['type'] = 'order_pay';
            $log['description'] = $ecLog['message'].' 订单号：'.$ecLog['order_id'].' 支付单号：'.$ecLog['payment_id'].' 支付方式：'.$payMethod;
        }
        TModel('RcbLog')->add($log);
        return true;
    }

    /**
     * 取得充值列表
     * @param unknown $condition
     * @param string $pagesize
     * @param string $fields
     * @param string $order
     */
    public function getPdRechargeList($condition = array(), $pagesize = '', $fields = '*', $order = '', $limit = '') {
        return $this->table('pd_recharge')->where($condition)->field($fields)->order($order)->limit($limit)->page($pagesize)->select();
    }

    /**
     * 添加充值记录
     * @param array $data
     */
    public function addPdRecharge($data) {
        return $this->table('pd_recharge')->insert($data);
    }

    /**
     * 编辑
     * @param unknown $data
     * @param unknown $condition
     */
    public function editPdRecharge($data,$condition = array()) {
        return $this->table('pd_recharge')->where($condition)->update($data);
    }

    /**
     * 取得单条充值信息
     * @param unknown $condition
     * @param string $fields
     */
    public function getPdRechargeInfo($condition = array(), $fields = '*') {
        return $this->table('pd_recharge')->where($condition)->field($fields)->find();
    }

    /**
     * 取充值信息总数
     * @param unknown $condition
     */
    public function getPdRechargeCount($condition = array()) {
        return $this->table('pd_recharge')->where($condition)->count();
    }

    /**
     * 取提现单信息总数
     * @param unknown $condition
     */
    public function getPdCashCount($condition = array()) {
        return $this->table('pd_cash')->where($condition)->count();
    }

    public function insertPdLog($data_log = array()) {
        return $this->table('pd_log')->insert($data_log);
    }
    /**
     * 取日志总数
     * @param unknown $condition
     */
    public function getPdLogCount($condition = array()) {
        return $this->table('pd_log')->where($condition)->count();
    }

    /**
     * 取得预存款变更日志列表
     * @param unknown $condition
     * @param string $pagesize
     * @param string $fields
     * @param string $order
     */
    public function getPdLogList($condition = array(), $pagesize = '', $fields = '*', $order = '', $limit = '') {
        return $this->table('pd_log')->where($condition)->field($fields)->order($order)->limit($limit)->page($pagesize)->select();
    }

    /**
     * 变更充值卡余额
     *
     * @param string $type
     * @param array  $data
     *
     * @return mixed
     * @throws Exception
     */
    public function changeRcb($type, $data = array())
    {
        if(isset($data['order_sn'])){
            $orderModel = TModel('Orders');
            $order = $orderModel->where(array('order_sn'=>$data['order_sn']))->find();
            $data['pay_sn'] = $order['pay_sn'];
            //$data['payment_code'] = $order['payment_code'];
        }
        $amount = (float) $data['amount'];
        if ($amount < .01) {
            throw new Exception('参数错误');
        }

        $available = $freeze = 0;
        $desc = null;
        $message= '';
        $payment_id = $data['pay_sn']?:0;
        $order_id = $data['order_sn']?:0;
        $paymethod ='deposit';
        $memo = '';

        switch ($type) {
        case 'order_pay':
            $available = -$amount;
            $desc = '下单，使用充值卡余额，订单号: ' . $data['order_sn'];
            $message= '预存款支付订单';
            $memo = '';
            break;

        case 'order_freeze':
            $available = -$amount;
            $freeze = $amount;
            $desc = '下单，冻结充值卡余额，订单号: ' . $data['order_sn'];
            $message= '预存款冻结';
            $memo = '';
            break;

        case 'order_cancel':
            $available = $amount;
            $freeze = -$amount;
            $desc = '取消订单，解冻充值卡余额，订单号: ' . $data['order_sn'];
            $message= '取消订单';
            $memo = '取消订单';
            break;

        case 'order_comb_pay':
            $freeze = -$amount;
            $desc = '下单，扣除被冻结的充值卡余额，订单号: ' . $data['order_sn'];
            $message= '预存款支付订单';
            $memo = '下单，扣除被冻结的充值卡余额';
            break;

        case 'recharge':
            $available = $amount;
            $desc = '平台充值卡充值，充值卡号: ' . $data['sn'];
            $message= '前台预存款充值';
            $memo = '';
            break;

        case 'refund':
            $available = $amount;
            $desc = '确认退款，订单号: ' . $data['order_sn'];
            $message= '预存款退款';
            $memo = '退还订单消费';
            break;

        case 'vr_refund':
            $available = $amount;
            $desc = '虚拟兑码退款成功，订单号: ' . $data['order_sn'];
            $message= '虚拟兑码退款成功';
            $memo = '虚拟兑码退款成功';
            break;

        case 'order_book_cancel':
            $available = $amount;
            $desc = '取消预定订单，退还充值卡余额，订单号: ' . $data['order_sn'];
            $message= '取消预定订单';
            $memo = '取消预定订单，退还充值卡余额';
            break;

        default:
            throw new Exception('参数错误');
        }

        $update = array();
        $updateOld = array();
        if ($available) {
            $update['available_rc_balance'] = array('exp', "available_rc_balance + ({$available})");
            $updateOld['advance'] = array('exp', "advance + ({$available})");
        }
        if ($freeze) {
            $update['freeze_rc_balance'] = array('exp', "freeze_rc_balance + ({$freeze})");
            $updateOld['advance_freeze'] = array('exp', "advance_freeze + ({$freeze})");
        }

        if (!$update) {
            throw new Exception('参数错误');
        }

        // 更新会员
        if(C('OLD_STATUS')==true){
            $ecModel = ecModel('B2cMembers');
            $updateOldRes = $ecModel->where(array('member_id' => $data['member_id'],))->save($updateOld);
            if(!$updateOldRes){
                throw new Exception('扣款失败！');
            }
            $ecMember = $ecModel->find($data['member_id']);
            $update['available_rc_balance'] = $ecMember['advance'];
            $update['freeze_rc_balance'] = $ecMember['advance_freeze'];
        }

        $updateSuccess = Model('member')->editMember(array(
            'member_id' => $data['member_id'],
        ), $update);

        if (!$updateSuccess) {
            throw new Exception('操作失败');
        }

        //充值卡消费日志
        if($type == 'order_pay'){
            $consume_res = Model('rechargecard')->consumeCardLog($data['order_sn'],$available);
            if (!$consume_res) {
                throw new Exception('充值卡消费日志插入失败');
            }
        }

        // 添加日志
        $log = array(
            'member_id' => $data['member_id'],
            'member_name' => $data['member_name'],
            'type' => $type,
            'add_time' => TIMESTAMP,
            'available_amount' => $available,
            'freeze_amount' => $freeze,
            'description' => $desc,
        );
        if(C('OLD_STATUS')==true) {
            $ecLogModel = ecModel('B2cMemberAdvance');
            $lastLog = $ecLogModel->order('log_id DESC')->find();
            $shop_advance = $lastLog['shop_advance']+$available;
            $ecLog = array(
                'member_id'=>$data['member_id'],
                'money'=>abs($available),
                //'message'=>iconv('UTF-8','GBK',$message),
                'message'=>$message,
                'mtime'=>TIMESTAMP,
                'payment_id'=>$payment_id,
                'order_id'=>$order_id,
                'paymethod'=>$paymethod,
                'memo'=>$memo,
                'member_advance'=>$ecMember['advance']?:0,//
                'member_subject_advance'=>$ecMember['advance_card_freeze']?:0,
                'shop_advance'=>$shop_advance,
                'disabled'=>'false',
            );
            if($available>0) $ecLog['import_money'] = $amount;
            else $ecLog['explode_money'] = $amount;
            $log['id'] = $ecLogModel->add($ecLog);
            if(!$log['id'])
                throw new Exception('操作失败'.$log['id']);
        }

        $insertSuccess = $this->table('rcb_log')->insert($log);
        if (!$insertSuccess) {
            throw new Exception('操作失败');
        }

        $msg = array(
            'code' => 'recharge_card_balance_change',
            'member_id' => $data['member_id'],
            'param' => array(
                'time' => date('Y-m-d H:i:s', TIMESTAMP),
                'url' => urlMember('predeposit', 'rcb_log_list'),
                'available_amount' => ncPriceFormat($available),
                'freeze_amount' => ncPriceFormat($freeze),
                'description' => $desc,
            ),
        );

        QueueClient::push('addConsume', array('member_id'=>$data['member_id'],'member_name'=>$data['member_name'],
                'consume_amount'=>$amount,'consume_time'=>time(),'consume_remark'=>$desc));
        // 发送买家消息
        QueueClient::push('sendMemberMsg', $msg);

        return $insertSuccess;
    }

    /**
     * 变更预存款
     * @param unknown $change_type
     * @param unknown $data
     * @throws Exception
     * @return unknown
     */
    public function changePd($change_type,$data = array()) {
        $data_log = array();
        $data_pd = array();
        $data_msg = array();
		
        $data_log['lg_invite_member_id'] = $data['invite_member_id'];
        $data_log['lg_member_id'] = $data['member_id'];
        $data_log['lg_member_name'] = $data['member_name'];
        $data_log['lg_add_time'] = TIMESTAMP;
        $data_log['lg_type'] = $change_type;

        $data_msg['time'] = date('Y-m-d H:i:s');
        $data_msg['pd_url'] = urlMember('predeposit', 'pd_log_list');
        switch ($change_type){
            case 'order_pay':
                $data_log['lg_av_amount'] = -$data['amount'];
                $data_log['lg_desc'] = '下单，支付预存款，订单号: '.$data['order_sn'];
                $data_pd['available_predeposit'] = array('exp','available_predeposit-'.$data['amount']);

                $data_msg['av_amount'] = -$data['amount'];
                $data_msg['freeze_amount'] = 0;
                $data_msg['desc'] = $data_log['lg_desc'];
                break;
            case 'order_freeze':
                $data_log['lg_av_amount'] = -$data['amount'];
                $data_log['lg_freeze_amount'] = $data['amount'];
                $data_log['lg_desc'] = '下单，冻结预存款，订单号: '.$data['order_sn'];
                $data_pd['freeze_predeposit'] = array('exp','freeze_predeposit+'.$data['amount']);
                $data_pd['available_predeposit'] = array('exp','available_predeposit-'.$data['amount']);

                $data_msg['av_amount'] = -$data['amount'];
                $data_msg['freeze_amount'] = $data['amount'];
                $data_msg['desc'] = $data_log['lg_desc'];
                break;
            case 'order_cancel':
                $data_log['lg_av_amount'] = $data['amount'];
                $data_log['lg_freeze_amount'] = -$data['amount'];
                $data_log['lg_desc'] = '取消订单，解冻预存款，订单号: '.$data['order_sn'];
                $data_pd['freeze_predeposit'] = array('exp','freeze_predeposit-'.$data['amount']);
                $data_pd['available_predeposit'] = array('exp','available_predeposit+'.$data['amount']);

                $data_msg['av_amount'] = $data['amount'];
                $data_msg['freeze_amount'] = -$data['amount'];
                $data_msg['desc'] = $data_log['lg_desc'];
                break;
            case 'order_comb_pay':
                $data_log['lg_freeze_amount'] = -$data['amount'];
                $data_log['lg_desc'] = '下单，支付被冻结的预存款，订单号: '.$data['order_sn'];
                $data_pd['freeze_predeposit'] = array('exp','freeze_predeposit-'.$data['amount']);

                $data_msg['av_amount'] = 0;
                $data_msg['freeze_amount'] = $data['amount'];
                $data_msg['desc'] = $data_log['lg_desc'];
                break;
			case 'order_invite':
                $data_log['lg_av_amount'] = +$data['amount'];
                $data_log['lg_desc'] = '分销，获得推广佣金，订单号: '.$data['order_sn'];
                $data_pd['available_predeposit'] = array('exp','available_predeposit+'.$data['amount']);

                $data_msg['av_amount'] = +$data['amount'];
                $data_msg['freeze_amount'] = 0;
                $data_msg['desc'] = $data_log['lg_desc'];
                break;
            case 'recharge':
                $data_log['lg_av_amount'] = $data['amount'];
                $data_log['lg_desc'] = '充值，充值单号: '.$data['pdr_sn'];
                $data_log['lg_admin_name'] = $data['admin_name'];

                $data_pd['available_predeposit'] = array('exp','available_predeposit+'.$data['amount']);

                $data_msg['av_amount'] = $data['amount'];
                $data_msg['freeze_amount'] = 0;
                $data_msg['desc'] = $data_log['lg_desc'];
                break;

            case 'refund':
                $data_log['lg_av_amount'] = $data['amount'];
                $data_log['lg_desc'] = '确认退款，订单号: '.$data['order_sn'];
                $data_pd['available_predeposit'] = array('exp','available_predeposit+'.$data['amount']);

                $data_msg['av_amount'] = $data['amount'];
                $data_msg['freeze_amount'] = 0;
                $data_msg['desc'] = $data_log['lg_desc'];
                break;
            case 'vr_refund':
                $data_log['lg_av_amount'] = $data['amount'];
                $data_log['lg_desc'] = '虚拟兑码退款成功，订单号: '.$data['order_sn'];
                $data_pd['available_predeposit'] = array('exp','available_predeposit+'.$data['amount']);

                $data_msg['av_amount'] = $data['amount'];
                $data_msg['freeze_amount'] = 0;
                $data_msg['desc'] = $data_log['lg_desc'];
                break;
            case 'cash_apply':
                $data_log['lg_av_amount'] = -$data['amount'];
                $data_log['lg_freeze_amount'] = $data['amount'];
                $data_log['lg_desc'] = '申请提现，冻结预存款，提现单号: '.$data['order_sn'];
                $data_pd['available_predeposit'] = array('exp','available_predeposit-'.$data['amount']);
                $data_pd['freeze_predeposit'] = array('exp','freeze_predeposit+'.$data['amount']);

                $data_msg['av_amount'] = -$data['amount'];
                $data_msg['freeze_amount'] = $data['amount'];
                $data_msg['desc'] = $data_log['lg_desc'];
                break;
            case 'cash_pay':
                $data_log['lg_freeze_amount'] = -$data['amount'];
                $data_log['lg_desc'] = '提现成功，提现单号: '.$data['order_sn'];
                $data_log['lg_admin_name'] = $data['admin_name'];
                $data_pd['freeze_predeposit'] = array('exp','freeze_predeposit-'.$data['amount']);

                $data_msg['av_amount'] = 0;
                $data_msg['freeze_amount'] = -$data['amount'];
                $data_msg['desc'] = $data_log['lg_desc'];
                break;
            case 'cash_del':
                $data_log['lg_av_amount'] = $data['amount'];
                $data_log['lg_freeze_amount'] = -$data['amount'];
                $data_log['lg_desc'] = '取消提现申请，解冻预存款，提现单号: '.$data['order_sn'];
                $data_log['lg_admin_name'] = $data['admin_name'];
                $data_pd['available_predeposit'] = array('exp','available_predeposit+'.$data['amount']);
                $data_pd['freeze_predeposit'] = array('exp','freeze_predeposit-'.$data['amount']);

                $data_msg['av_amount'] = $data['amount'];
                $data_msg['freeze_amount'] = -$data['amount'];
                $data_msg['desc'] = $data_log['lg_desc'];
                break;
            case 'order_book_cancel':
                $data_log['lg_av_amount'] = $data['amount'];
                $data_log['lg_desc'] = '取消预定订单，退还预存款，订单号: '.$data['order_sn'];
                $data_pd['available_predeposit'] = array('exp','available_predeposit+'.$data['amount']);

                $data_msg['av_amount'] = $data['amount'];
                $data_msg['desc'] = $data_log['lg_desc'];
                break;
			//汉购网新增
			case 'sys_add_money':
                $data_log['lg_av_amount'] = $data['amount'];
                $data_log['lg_desc'] = '管理员调节预存款【增加】，充值单号: '.$data['pdr_sn'];
                $data_log['lg_admin_name'] = $data['admin_name'];
                $data_pd['available_predeposit'] = array('exp','available_predeposit+'.$data['amount']);

                $data_msg['av_amount'] = $data['amount'];
                $data_msg['freeze_amount'] = 0;
                $data_msg['desc'] = $data_log['lg_desc'];
                break;
			case 'sys_del_money':
                $data_log['lg_av_amount'] = -$data['amount'];
                $data_log['lg_desc'] = '管理员调节预存款【减少】，充值单号: '.$data['pdr_sn'];
                $data_pd['available_predeposit'] = array('exp','available_predeposit-'.$data['amount']);

                $data_msg['av_amount'] = -$data['amount'];
                $data_msg['freeze_amount'] = 0;
                $data_msg['desc'] = $data_log['lg_desc'];
                break;
			case 'sys_freeze_money':
                $data_log['lg_av_amount'] = -$data['amount'];
                $data_log['lg_freeze_amount'] = +$data['amount'];
				$data_log['lg_desc'] = '管理员调节预存款【冻结】，充值单号: '.$data['pdr_sn'];
                $data_pd['available_predeposit'] = array('exp','available_predeposit-'.$data['amount']);
                $data_pd['freeze_predeposit'] = array('exp','freeze_predeposit+'.$data['amount']);

                $data_msg['av_amount'] = -$data['amount'];
                $data_msg['freeze_amount'] = +$data['amount'];
                $data_msg['desc'] = $data_log['lg_desc'];
                break;
			case 'sys_unfreeze_money':
                $data_log['lg_av_amount'] = $data['amount'];
                $data_log['lg_freeze_amount'] = -$data['amount'];
                $data_log['lg_desc'] = '管理员调节预存款【解冻】，充值单号: '.$data['pdr_sn'];
                $data_log['lg_admin_name'] = $data['admin_name'];
                $data_pd['available_predeposit'] = array('exp','available_predeposit+'.$data['amount']);
                $data_pd['freeze_predeposit'] = array('exp','freeze_predeposit-'.$data['amount']);

                $data_msg['av_amount'] = $data['amount'];
                $data_msg['freeze_amount'] = -$data['amount'];
                $data_msg['desc'] = $data_log['lg_desc'];
                break;
			case 'seller_money':
				$msg=$data['msg'];
                $data_log['lg_freeze_amount'] = +$data['amount'];
                $data_log['lg_desc'] = '卖出商品收入,扣除拥金'.$msg.',订单号: '.$data['pdr_sn'];
                $data_pd['freeze_predeposit'] = array('exp','freeze_predeposit+'.$data['amount']);
                $data_msg['av_amount'] = 0;
                $data_msg['freeze_amount'] = +$data['amount'];
                $data_msg['desc'] = $data_log['lg_desc'];
                break;
			case 'seller_refund':
				$msg=$data['msg'];
                $data_log['lg_freeze_amount'] = -$data['amount'];
                $data_log['lg_desc'] = '商家退款支出,扣除预存款'.$msg.',订单号: '.$data['order_sn'];
                $data_pd['freeze_predeposit'] = array('exp','freeze_predeposit-'.$data['amount']);
                $data_msg['av_amount'] = 0;
                $data_msg['freeze_amount'] = -$data['amount'];
                $data_msg['desc'] = $data_log['lg_desc'];
                break;
            default:
                throw new Exception('参数错误');
                break;
        }

        $update = Model('member')->editMember(array('member_id'=>$data['member_id']),$data_pd);

        if (!$update) {
            throw new Exception('操作失败');
        }
        $insert = $this->table('pd_log')->insert($data_log);
        if (!$insert) {
            throw new Exception('操作失败');
        }

        // 支付成功发送买家消息
        $param = array();
        $param['code'] = 'predeposit_change';
        $param['member_id'] = $data['member_id'];
        $data_msg['av_amount'] = ncPriceFormat($data_msg['av_amount']);
        $data_msg['freeze_amount'] = ncPriceFormat($data_msg['freeze_amount']);
        $param['param'] = $data_msg;
        QueueClient::push('addConsume', array('member_id'=>$data['member_id'],'member_name'=>$data['member_name'],
        'consume_amount'=>$data['amount'],'consume_time'=>time(),'consume_remark'=>$data_log['lg_desc']));
        QueueClient::push('sendMemberMsg', $param);
        return $insert;
    }

    /**
     * 删除充值记录
     * @param unknown $condition
     */
    public function delPdRecharge($condition) {
        return $this->table('pd_recharge')->where($condition)->delete();
    }

    /**
     * 取得提现列表
     * @param unknown $condition
     * @param string $pagesize
     * @param string $fields
     * @param string $order
     */
    public function getPdCashList($condition = array(), $pagesize = '', $fields = '*', $order = '', $limit = '') {
        return $this->table('pd_cash')->where($condition)->field($fields)->order($order)->limit($limit)->page($pagesize)->select();
    }

    /**
     * 添加提现记录
     * @param array $data
     */
    public function addPdCash($data) {
        return $this->table('pd_cash')->insert($data);
    }

    /**
     * 编辑提现记录
     * @param unknown $data
     * @param unknown $condition
     */
    public function editPdCash($data,$condition = array()) {
        return $this->table('pd_cash')->where($condition)->update($data);
    }

    /**
     * 取得单条提现信息
     * @param unknown $condition
     * @param string $fields
     */
    public function getPdCashInfo($condition = array(), $fields = '*') {
        return $this->table('pd_cash')->where($condition)->field($fields)->find();
    }

    /**
     * 删除提现记录
     * @param unknown $condition
     */
    public function delPdCash($condition) {
        return $this->table('pd_cash')->where($condition)->delete();
    }
}
