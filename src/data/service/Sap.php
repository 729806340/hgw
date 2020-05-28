<?php
require_once('sap/commons.php');

class SapService extends commons
{

    //定时任务
    public function task($code)
    {
        return empty($code) ? false : $this->send($code, $msg);
    }

    //发起交易
    public function send($code, &$msg)
    {
        $log['code'] = $code;
        try {
            list($action, $class_name, $method) = $this->instantiation($code);
            //获取推送的数据
            $log['data'] = $data = $this->execute($action, $class_name, $method);
            //推送
            $log['rel'] = $rel = $this->push($code, $data);
            //后续操作
            if (method_exists($action, $method . '_after')) {
                $this->execute($action, $class_name, $method . '_after', $this->decode_json($rel, $code, false));
            }
        } catch (Exception $e) {
            $log['error'] = $msg = $e->getMessage();
            $rel = false;
        }
        //记录日志
        $this->log($log);
        return $rel;
    }

    //回调
    public function callback($code, $str, &$msg)
    {
        $log['code'] = $code;
        $log['data'] = $str;
        $log['method'] = 'callback';
        try {
            list($action, $class_name, $method) = $this->instantiation($code);
            //执行回调方法
            $log['rel'] = $rel = $this->execute($action, $class_name, $method . '_callback', $this->decode_json($str, $code));
        } catch (Exception $e) {
            $log['error'] = $msg = $e->getMessage();
            $rel = false;
        }
        //记录日志
        $this->log($log);
        return $rel;
    }

    //curl 推送数据
    private function push($code, $data)
    {
        if (empty($data)) throw new Exception('Null data need to be pushed!');
        $url = $this->api[$code];
        if (empty($url)) throw new Exception('Error: ' . $code . ' url does not exist!');
        $url = $this->api['host'] . $url;
        import('Curl');
        $curl = new Curl();
        $curl->setHeader('Content-Type', 'application/json;charset=utf-8');
        $curl->setOpt(CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        $curl->setOpt(CURLOPT_TIMEOUT, 300);

        $curl->post($url, array('data' => $data), true);
        if ($curl->error === true) {
            $msg = $curl->errorMessage;
            $this->failed[] = array(
                'title' => '报警：来自 ' . $code . ' 报警信息',
                'msg' => 'url:' . $url . '<br>error:' . $msg,
            );
            throw new Exception($msg);
        }
        return $curl->rawResponse;
    }

    //解析SAP数据格式
    private function decode_json($sap_str, $code = null, $callback=true)
    {
        $rel = json_decode($sap_str, true);
        //sap403数据格式与其它交易格式不同 直接返回数据到具体的方法里处理
        if ($code == 'sap403') return array($rel);
        if ($code == 'sap602') return array($rel);
        if ($code == 'sap504') return array($rel);
        if ($code == 'sap505') return array($rel);
        if ($code == 'sap406') return array($rel);
        if ($code == 'sap407') return array($rel);
        if ($code == 'sap408') return array($rel);
        if ($code == 'sap506') return array($rel);
        if ($code == 'sap507') return array($rel);
        if ($code == 'sap508') return array($rel);
        if ($code == 'sap509') return array($rel);
        if ($code == 'sap701') return array($rel);

        $success = $error = $exist = $notice = array();
        foreach ((array)$rel['results'] as $v) {
            if (!$p = strpos($v['tid'], '_')) continue;
            $tid = substr($v['tid'], $p + 1);
            switch ($v['status']) {
                case '0':
                    $success[] = $tid;
                    break;
                case '-10':
                    // $check_exist = in_array($code, array('sap101', 'sap201','sap301', 'sap401', 'sap402','sap405', 'sap501', 'sap502', 'sap404', 'sap601'));
                    $check_exist = true;
                    if ($check_exist && $callback ) {
                        // -10为已存在  该状态认为推送成功
                        if (strpos($v['errInf'], '重复') > 0 || strpos($v['errInf'], '存在') > 0) {
                            $success[] = $tid;
                        } else {
                            $error[] = $tid;
                            $notice[] = 'tid:' . $v['tid'] . ' error:' . $v['errInf'];
                            break;
                        }

                    } else if ($check_exist && !$callback ) {
                        $exist[] = $tid;
                    } else {
                        $error[] = $tid;
                        $notice[] = 'tid:' . $v['tid'] . ' error:' . $v['errInf'];
                    }
                    break;
                case '-20':
                	if ( strpos($v['errInf'], '重复') > 0 || strpos($v['errInf'], '存在') > 0 ) {
                		$exist[] = $tid;
                	}
                	break;
                default:
                    //重复订单号验证
                    if (strpos($v['errInf'], '订单号不允许重复') > 0) {
                        $success[] = $tid;
                        break;
                    }

                    //错误数据修正，已经结算的错误信息单独处理（即不能重置原始单据，仅修改推送标志位成功状态）
                    if (strpos($v['errInf'], '请首先取消目标单据') > 0 || strpos($v['errInf'], '输入大于零的整数') > 0) {
                        $exist[] = $tid;
                        break;
                    }

                    $error[] = $tid;
                    $notice[] = 'tid:' . $v['tid'] . ' error:' . $v['errInf'];
                    break;
            }
        }
        if (!empty($notice)) {
            $this->failed[] = array(
                'title' => '报警：来自 ' . $code . ' 报警信息',
                'msg' => implode('<br>', $notice),
            );
        }
        return array($success, $error, $exist);
    }

    /**
     * 修正已经推送到sap，未出账单的应收单据
     */
    public function writeoffUnbillOrders($order_sns='', $order_ids='') {
        if (empty($order_sns) && empty($order_ids)) {
            return false;
        }

        if (!empty($order_ids)) {
            $condition = array();
            !is_array($order_ids) && $order_ids = explode(',', $order_ids);
            $condition['order_id'] = array('in', $order_ids);
        } else {
            $condition = array();
            !is_array($order_sns) && $order_sns = explode(',', $order_sns);
            $condition['order_sn'] = array('in', $order_sns);
        }
        $res = Model('orders')->where($condition)->field('store_id,order_id,order_sn,finnshed_time,send_sap')->select();

        $ret = array();
        $log_model = Model('bill_log');

        //写入sap错误数据修正日志表
        if( is_array($res) && !empty($res) ){
            foreach ($res as $k => $v) {
                //已成功推送到sap，才重写
                if ($v['send_sap'] != '2') {
                    $ret[$v['order_sn']] = array(
                        'code' => 0,
                        'msg' => '订单未推送到sap，不用进行数据修正'
                    );
                    continue;
                }

                //验证是否已出账，找出店铺最近一次账单时间进行验证
//                $last_bill = Model('order_bill')->field('ob_end_date')->where(array('store_id' => $v['store_id']))->order('ob_end_date desc')->find();
//                if ($last_bill['ob_end_date']>= $v['finnshed_time']) {
//                    $ret[$v['order_sn']] = array(
//                        'code' => 0,
//                        'msg' => '订单已生成结算账单，需到账单中进行数据修正'
//                    );
//                    continue;
//                }

                $data = array(
                    'log_status' => '1',
                    'order_id' => $v['order_id'],
                    'order_sn' => $v['order_sn'],
                    'log_msg' => '修正应收单据(未出账)',
                    'log_time' => time(),
                    'log_user' => 'system',
                    'send_sap' => '0'
                );

                //验证是否存在
                $_where = array('order_id' => $v['order_id'], 'log_user' => 'system');
                $exist = $log_model->where($_where)->count();
                if ($exist) {
                    $_res = $log_model->where($_where)->update($data);
                } else {
                    $_res = $log_model->insert($data);    
                }
                $ret[$v['order_sn']] = array(
                    'code' => 1,
                    'msg' => $_res ? '重置推送状态成功' : '重置推送状态失败'
                );
                
            }
        }
        return $ret;
    }

}