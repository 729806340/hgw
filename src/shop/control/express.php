<?php
/**
 * 快递对外接口
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/24
 * Time: 10:16
 */
class expressControl extends BaseHomeControl{
    private $express_model;
    public function __construct()
    {
        $this->express_model = Model('express');
    }

    public function traceOp(){
        $RequestData = html_entity_decode($_POST['RequestData']);
        $RequestType = $_POST['RequestType'];
        if($RequestType != 102){
            $this->returnKdn();
        }
        $postData = json_decode($RequestData, true);
        $traceData = $postData['Data'];
        $express_list = Model('express')->getExpressList();
        $express_list = array_under_reset($express_list , 'e_code');
        $express_code = $this->express_model->ship_code;
        foreach($traceData as $k=>$val){
            if(!$val['Success']){
                continue ;
            }
            $e_code = '';
            foreach($express_code as $item=>$value){
                if($val['ShipperCode']==$value){
                    $e_code = $item;
                }
            }
            if(empty($e_code)) {
                continue ;
            }
            $condition = array(
                'express_id'   => $express_list[$e_code]['id'],
                'shipping_code'=> $val['LogisticCode'],
            );
            $trace_list = Model('express_trace')->getExpressTraceInfo($condition);
            if(empty($trace_list)){
                $this->returnKdn();
            }
            $update = array();
            $update['update_time'] = TIMESTAMP;
            if(count($val['Traces'])>1 && $trace_list['is_sendfx']==0){
                $update['is_sendfx'] = 1;
                $order_info = Model('order')->getOrderInfo(array('order_sn'=>$trace_list['order_sn']),array('order_common','order_goods'));

                $model = Model('express_trace');
                $model->beginTransaction();
                try{
                    $res = $model->where(array('et_id'=>$trace_list['et_id']))->update($update);
                    if(!$res){
                        throw new Exception('更新队列表失败');
                    }
                    //增加发货列表
                    $res=Model("sendorder_record")->insertData($order_info,$val['express_id'] , $val['shipping_code']);
                    if(!$res){
                        throw new Exception("changeOrderSend方法新增数据到sendorder_record失败");
                    }
                    $model->commit();
                }catch (Exception $e)
                {
                    $model->rollback();
                }
            }
        }
        $this->returnKdn();
    }


    /**
     * 通过 CURL 运行此脚本
     */
    public function indexOp(){
        $condition['is_sendknd'] = 0;
        $express_list = Model('express')->getExpressList();
        $trace_list = Model('express_trace')->getExpressTraceList($condition, '*', 500,'', $order = 'et_id ASC');
        $et_ids = array();
        if(!empty($trace_list)){
            foreach($trace_list as $key=>$val){
                $express_code = $express_list[$val['express_id']]['e_code'];
                $data = array(
                    'ShipperCode' => $this->express_model->ship_code[$express_code],
                    'LogisticCode'=> $val['shipping_code'],
                );
                $data = JSON($data);
                $result = $this->express_model->trace_ship($data);
                $result = json_decode($result , true);
                if($result['Success']){
                    $update = array();
                    $condition = array();
                    $update['is_sendknd'] = 1;
                    $condition['et_id'] = $val['et_id'];
                    Model('express_trace')->editExpressTrace($update, $condition);
                }
            }
        }
    }

    public function checkOp()
    {
        // 循环检查未发货数据是否发货
        /** @var express_traceModel $traceModel */
        $traceModel = Model('express_trace');
        /** @var expressModel $expressModel */
        $expressModel = Model('express');
        $traceList = $traceModel->getExpressTraceList(array('is_sendfx'=>0),'*',1000,null,'et_id asc');
        if(empty($traceList)) exit('ok');
        /** @var orderModel $orderModel */
        $orderModel = Model('order');
        $orderList = $orderModel->getOrderList(array('order_sn'=>array('in',array_column($traceList,'order_sn'))),'','*','order_id',99999,array('order_common','order_goods'));
        $orderList = array_under_reset($orderList,'order_sn');
        foreach ($traceList as $trace){
            $express = $expressModel->getExpressInfo($trace['express_id']);
            $data = $expressModel->get_express_pro($express['e_code'],$trace['shipping_code']);
            if(count($data)<2) continue;
            $update = array();
            $update['update_time'] = TIMESTAMP;
            $update['is_sendfx'] = 1;
            $order_info = $orderList[$trace['order_sn']];
            $traceModel->beginTransaction();
            try{
                $res = $traceModel->where(array('et_id'=>$trace['et_id']))->update($update);
                if(!$res){
                    throw new Exception('更新队列表失败');
                }

                //增加发货列表
                $res=Model("sendorder_record")->insertData($order_info,$trace['express_id'] , $trace['shipping_code']);
                if(!$res){
                    throw new Exception("changeOrderSend方法新增数据到sendorder_record失败");
                }
                $traceModel->commit();
            }catch (Exception $e)
            {
                $traceModel->rollback();
            }
        }
    }

    public function returnKdn(){
        $data = array(
            'EBusinessID' => EBusinessID,
            'UpdateTime'     => date('Y-m-d H:i:s'),
            'Success' => "true",
            'Reason'     =>'',
        );
        die(JSON($data));
    }

    private function writeCache( $e_code , $shipping_code , $data){
        $cacheKey = 'express'.$e_code.'.sn'.$shipping_code;
        $traces = array();
        foreach($data as $k=>$v){
            $traces[$k]['time'] = $v['AcceptTime'];
            $traces[$k]['context'] = $v['AcceptStation'];
        }
        $expires = 86400*30;
        wcache($cacheKey,$traces,'express',$expires);
        //return array_reverse($traces);
    }


}
