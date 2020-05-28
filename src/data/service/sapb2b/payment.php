<?php

class payment extends commons
{
    private $payment_code = array(//收款方式
        'offline' => '货到付款',
        'alipay' => '支付宝',
        'tenpay' => '财付通',
        'chinabank' => '网银在线',
        'predeposit' => '余额支付',
        'wx_jsapi' => '微信支付',
        'wxpay' => '微信支付',
        'yeepay' => '易宝支付',
        'jicai' => '线下支付',
        // 'fenxiao' => array(
        //     'youzan' => '有赞',
        //     'renrendian' => '人人店',
        //     'pinduoduo' => '拼多多',
        // 	'oldhango' => '汉购旧平台',
        // 	'fanli' => '返利',
        // 	'zhe800' => '折800',
        // 	'gegejia' => '格格家',
        // 	'mengdian' => '萌店',
        // 	'taobaofx' => '淘宝',
        // 	'juanpi' => '卷皮'
        // ),
    );


    private $make_item = array();//记录每条收款记录的子订单数，用于比对是否全部成功


    //sap401 收款
    public function make()
    {
        /** @var b2b_orderModel $orderModel */
        $orderModel = Model('b2b_order');
        $code = $this->getCode(__CLASS__, __FUNCTION__);
        $where['payment_time'] = array('gt', '0');//已支付
        $where['make_send_time'] = '0';//未推送
        $where['payment_code'] = array('neq', 'offline');//未推送
        $where['order_from'] = array('neq', 3);//订单来源
        $list1 = $orderModel->where($where)->limit($this->getLimit($code))->select();

        $where['payment_code'] = 'offline';//未推送
        //$where['payment_time'] = '0';//已支付
        $list2 = $orderModel->where($where)->limit($this->getLimit($code))->select();

        $list = array_merge($list1,$list2);

        $data = array();
        foreach ((array)$list as $v) {
            if (empty($v['payment_code'])) continue;
            $tid = $code . '_' . $v['order_id'] . '_';
            //公共部分
            $it['oid'] = $v['order_sn'];//订单号
            $it['payOrderNumber'] = $v['pay_sn'];//支付单号
            if($v['payment_code'] == 'offline'){
                $it['date'] = date('Y-m-d', $v['add_time']);//过账日期
            } else {
                $it['date'] = date('Y-m-d', $v['payment_time']);//过账日期
            }

            $it['customer'] = $v['buyer_name'];//店主账号	买家
            //如果是集采订单，优先用留言当购买者，否则按照发票抬头 进而  收货人来

            $purchaser_info = Model('b2b_purchaser')->where(array('member_id' => $v['buyer_id']))->find();
            $it['cardCode'] = $purchaser_info['purchaser_id'];//商户代码	客户代码
            $it['cardName'] = $purchaser_info['company_name'];//商户名称	客户名称
            $it['dischannel'] = empty($v['fx_order_id'])?$v['trade_no']:$v['fx_order_id'];
            //common end
            $k = 0;//拆分个数

                //充值卡支付
                if ($v['rcb_amount'] > 0) {
                    $it['tid'] = $tid . $k;//电商平台单号	主键
                    $it['payMode'] = '充值卡';//支付方式
                    $it['total'] = $v['rcb_amount'];//支付金额

                    $k++;
                    $data[] = $it;
                }
                //预存款支付
                if ($v['pd_amount'] > 0) {
                    $it['tid'] = $tid . $k;//电商平台单号	主键
                    $it['payMode'] = '预存款';//支付方式
                    $it['total'] = $v['pd_amount'];//支付金额

                    $k++;
                    $data[] = $it;
                }
                //其它支付方法
                $online_total = sprintf('%.2f', $v['order_amount'] - $v['rcb_amount'] - $v['pd_amount']);
                if ($online_total > 0) {
                    $it['tid'] = $tid . $k;//电商平台单号	主键
                    $it['payMode'] = $this->payment_code[$v['payment_code']];//支付方式
                    $it['total'] = $online_total;//销售金额	订单销售总额

                    $k++;
                    $data[] = $it;
                }

            $this->make_item[$v['order_id']] = $k;
        }
        return $data;
    }

    //sap401 收款推送成功后更新同步时间
    public function make_after($success, $error, $exist='')
    {
        $rel = $make_ids = array();
        $success = array_merge($success, $exist);
        //计算成功的子订单个数
        foreach ((array)$success as $tid) {
            list($id, $k) = explode('_', $tid);
            if (is_null($k)) continue;
            $rel[$id] = intval($rel[$id]) + 1;
        }
        //比对
        foreach ($rel as $order_id => $num) {
            if ($this->make_item[$order_id] != $num) continue;
            $make_ids[] = $order_id;
        }
        //修改推送状态
        if (count($make_ids) > 0) {
            $where['order_id'] = array('in', $make_ids);//成功的记录更新时间
            Model('b2b_order')->where($where)->update(array('make_send_time' => time()));
        }
        return true;
    }

}