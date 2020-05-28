<?php

defined('ByShopWWI') or exit('Access Invalid!');

class sapControl extends BaseCronControl
{
    //根据交易码发起不同的交易
    public function indexOp()
    {
        $code = $_GET['code'];
        if (empty($code)) {
            $this->log('crontab sap code error!');
        } else {
            //$this->_order_commis_rate_update();
            Service('Sap')->task($code);
        }
    }

    function logSapRepushStateSnOp()
    {
        header("Content-type:text/html;charset=utf-8");
        $order_id = $_GET['order_id'];
        $order_sn = $_POST['order_sn'];
        $sap_svc = Service('Sap');
        $res = $sap_svc->writeoffUnbillOrders($order_sn, false);
        v($res,0);
    }

    public function test712Op(){
        $n=0;
        $str='';
        $condition=array();
        for($i=0;$i<100;$i++){
            $sendorder_record=Model("sendorder_record");
            $condition['sourceid']=0;
            $orderdata=$sendorder_record->getsendorder($condition,'','','',400);
            if(count($orderdata)==0) break;
            foreach($orderdata as $key=>$item){
                $orderinfo=unserialize($orderdata[$key]['order_info']);
                $data['source']=$orderinfo['buyer_name'];
                $data['sourceid']=$orderinfo['buyer_id'];
                $data['fx_order_id']=$orderinfo['fx_order_id'];
                $data['order_sn']=$orderinfo['order_sn'];
                $con['id']=$orderdata[$key]['id'];
                $result= $sendorder_record->updatedata($con,$data);
                if(!$result){
                    $str.=$orderdata[$key]['id'].",";
                }
                $n++;
            }
        }
        print_r($str);
        echo "<br/>";
        print_r($n);
    }

    public function test713Op(){
        $num=0;
        for($i=0;$i<10;$i++){
            $sendorder_record = Model("sendorder_record");
            $condition['order_status'] = 2;
            $orderdata = $sendorder_record->getsendorder($condition,'','','',900);
            if(count($orderdata)==0) break;
            $order_sns = array_column($orderdata,'order_sn');
            $data['order_sn'] = array('in',implode(',',$order_sns));
            $data['order_state'] = 40;
            $order_fx = Model('orders')->where($data)->select();
            if (count($order_fx) > 0) {
                $con['order_sn'] = array('in', implode(',', array_column($order_fx, 'order_sn')));
                $data1['order_status'] = 1;
                $count = $sendorder_record->updatedata($con, $data1);
                $num=$num+$count;
            }
        }
        print_r($num);
    }



    public function findOp(){

        $path = BASE_ROOT_PATH.'/doc/refund1.txt';
        $file = fopen($path,"r");
        $fileData1 = array();
        while(! feof($file))
        {
            $fileData1[] = fgets($file);
        }
        fclose($file);

        $path = BASE_ROOT_PATH.'/doc/refund2.txt';
        $file = fopen($path,"r");
        $fileData2 = array();
        while(! feof($file))
        {
            $fileData2[] = fgets($file);
        }
        fclose($file);


        $fileData2 = array_unique($fileData2);
        sort($fileData2);

        sort($fileData1);

//        $resl = array_diff($fileData2.$fileData1);
//
//        v($resl);


        foreach($fileData1 as $v){
            if(!in_array($v,$fileData2)){
                echo $v;
            }
        }


    }

    public function restore301Op(){
        echo 'restore301';
        $path = BASE_ROOT_PATH.'/doc/order_sap301.txt';
        $file = fopen($path,"r");
        $fileData = array();
        while(! feof($file))
        {
            $fileData[] = fgets($file);
        }
        fclose($file);
        $condition = array();
        $condition['order_sn'] = array('in',$fileData);

        $data = array();
        $data['send_sap'] = 2;
        $res = Model('orders')->where($condition)->update($data);
        var_dump($res);
    }

    public function restore501Op(){
        $path = BASE_ROOT_PATH.'/doc/order_sap501.txt';
        $file = fopen($path,"r");
        $fileData = array();
        while(! feof($file))
        {
            $fileData[] = trim(fgets($file));
        }
        fclose($file);
        $filedata_str = implode(',',$fileData);
        echo $filedata_str;
        $condition = array();
        $condition['order_sn'] = array('in',$fileData);

        $data = array();
        $data['purchase_sap'] = 2;
        $res = Model('orders')->where($condition)->update($data);
//        echo Model('orders')->getLastSql();
//        var_dump($path);
    }

    public function test501Op(){

        $path = BASE_ROOT_PATH.'/doc/tids.txt';
        $file = fopen($path,"r");
        $fileData = array();
        while(! feof($file))
        {
            $fileData[] = fgets($file);
        }
        fclose($file);
        $bill_data = array();
        foreach($fileData as $tid){
            list($type, $bill_id, $order_id) = explode("_", $tid);
            $bill_data[$bill_id][] = $order_id;
        }

        $model_order = Model('order');
        $error_data = array();

        foreach($bill_data as $bill_id => $order_ids){
            foreach($order_ids as $order_id){
                $condition = array();
                $condition['ob_id'] = $bill_id;
                $bill_info = Model('order_bill')->where($condition)->find();

                $order_info =  Model('orders')->where('order_id = '.$order_id)->find();
//                $order_info['order_sn'] = 170504142730964001;

                $order_condition = array();
                $order_condition['order_state'] = ORDER_STATE_SUCCESS;
                $order_condition['store_id'] = $bill_info['ob_store_id'];
                $order_condition['order_sn'] = array('like', "%{$order_info['order_sn']}%");
                $order_condition['finnshed_time'] = array('between',"{$bill_info['ob_start_date']},{$bill_info['ob_end_date']}");
                $order_list = $model_order->getOrderList($order_condition,'','*','order_id ASC', 1);

                if(empty($order_list)){
                    $error_str = 'sap501_'.$bill_id.'_'.$order_id;
                    echo $error_str;
                }
            }
        }
    }

    //查询结算单里的订单是否已全部推送SAP
    public function resetBillOp()
    {
        $ob_model = Model('bill') ;
        $condition = array();
        $condition['ob_state'] = 3;
        $bill_list = $ob_model->getOrderBillList($condition,'ob_id', 300, 'ob_id desc', 300);
        
        $billid_list = array_column($bill_list, 'ob_id');
        
        $ob_model->checkOrderStatus($billid_list);
        $ob_model->checkRefundStatus($billid_list);
        $ob_model->checkStorecostStatus($billid_list);
    }


    //清除sap返回的错误推送的状态
    public function resetPushStateOp()
    {
        $sql = "update shopwwi_orders set send_sap='0' where send_sap in ('10')";
        $res = Model('goods')->execute($sql);
        $sql = "update shopwwi_orders set purchase_sap='0' where purchase_sap in ('10')";
        $res = Model('goods')->execute($sql);

//        $sql = "update shopwwi_refund_return set send_sap='0' where send_sap='1'";
//        $res = Model('goods')->execute($sql);
//        $sql = "update shopwwi_refund_return set purchase_sap='0' where purchase_sap='1'";
//        $res = Model('goods')->execute($sql);
        $sql = "update shopwwi_refund_return set sap_return_credit='0' where sap_return_credit='1'";
        $res = Model('goods')->execute($sql);

//        $sql = "update shopwwi_store_cost set send_sap='0' where send_sap='1'";
//        $res = Model('goods')->execute($sql);
//        $sql = "update shopwwi_store_cost set purchase_sap='0' where purchase_sap='1'";
//        $res = Model('goods')->execute($sql);
        
        /*暂时停用$sql = "update shopwwi_bill_log set send_sap='0' where send_sap='1'";
        $res = Model('goods')->execute($sql); 
        $sql = "update shopwwi_bill_log set refund_sap='0' where refund_sap='1'";
        $res = Model('goods')->execute($sql);
        $sql = "update shopwwi_bill_log set purchase_refund_sap='0' where purchase_refund_sap='1'";
        $res = Model('goods')->execute($sql);*/
    }
}