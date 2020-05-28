<?php

/**
 * Author: Shen.L
 * Email: shen@shenl.com
 * Date: 2016/11/2
 * Time: 10:51
 */
require_once('WorkflowHandler.php');

class ManageType extends WorkflowHandler
{

    public function getId()
    {
        return 11;
    }

    public function getConfig()
    {
        return array(
            'name' => '店铺类型变更审批流程',
            'description' => '',
            'model' => 'store',
            'primary_key' => 'store_id',
            'action' => 'action',
            //'attributes' => array('name'=>'goods_cost', 'type'=>'text','label'=>'商品成本'),
            //'attributes' => 'goods_cost',
            'attributes' => array(
                array('name' => 'manage_type', 'type' => 'select', 'items'=>array('co_construct'=>'共建商家','platform'=>'平台商家',), 'label' => '商家类型', 'on' => array('运营部'), 'notice' => '请选择商家类型'),
            ),
            /**
             * attributes 参数说明
             * name ：input名称/数据表字段名
             * type：input类型
             * label：input显示名称
             * on：显示条件
             * attachment：是否附件
             */
            'reference' => 'index.php?act=store&op=store_joinin_detail&store_id={id}',
            'start' => '运营部',// 启动用户组
            'flow' => array(
                '运营部' => array(
                    'approve' => function ($model) {
                        /** @var storeModel $storeModel */
                        $storeModel = Model('store');
                        $storeInfo = $storeModel->getStoreInfo(array('store_id' => $model['model_id']));
                        $newValue = $model['new_value'];
                        if($newValue['manage_type'] == 'platform'){
                            /** @var store_bind_classModel $storeClassModel */
                            $storeClassModel = Model('store_bind_class');
                            $categories = $storeClassModel->getStoreBindClassList(array('store_id'=>$storeInfo['store_id'],'commis_rate'=>0));
                            if(!empty($categories)) throw new Exception('平台商家分类佣金必须大于0，请检查');
                        }else{
                            /** @var goodsModel $goodsModel */
                            $goodsModel = Model('goods');
                            $goodsList = $goodsModel->getGoodsList(array('store_id'=>$storeInfo['store_id'],'goods_cost'=>0));
                            if(!empty($goodsList)) throw new Exception('共建商家成本必须大于0，请检查');
                        }
                        return '财务部';
                    },
                    'reject' => '',
                ),
                '公司商务' => array(
                    'timeout' => 3600,
                    'approve' =>'财务部',
                    'reject' => '',
                ),

                '财务部' => array(
                    'timeout' => 3600,
                    'approve' => function ($model) {
                        /** @var storeModel $storeModel */
                        $storeModel = Model('store');
                        $newValue = $model['new_value'];
                        $storeInfo = $storeModel->getStoreInfo(array('store_id' => $model['model_id']));
                        // TODO 处理变更
                        $update_array = array(
                            'manage_type_new' => trim($newValue['manage_type']),
                        );
                        // 修改店铺类型，获取店铺下一个结算周期时间戳
                        /** @var BillService $bill */
                        $bill = Service('Bill');
                        $nextBillCycle = $bill->getBillStart($storeInfo,false);
                        if($nextBillCycle == 0){
                            $update_array['manage_type'] = trim($newValue['manage_type']);
                            $update_array['manage_type_new'] = '';
                            $update_array['manage_type_validate'] = 0;
                        }else{
                            $update_array['manage_type_new'] = trim($newValue['manage_type']);
                            /** @var store_extendModel $store_extendModel */
                            $store_extendModel = Model('store_extend');
                            $store_extend = $store_extendModel->getStoreExtendInfo(array('store_id'=>$storeInfo['store_id']));
                            if(empty($store_extend)||!$store_extend['bill_cycle']) {
                                $firstDay = date('Y-m-0');
                                $update_array['manage_type_validate']  = strtotime($firstDay.' next month');
                            } else {
                                $firstDay = date('Y-m-d',$nextBillCycle);
                                $update_array['manage_type_validate']  = strtotime($firstDay.' + '.$store_extend['bill_cycle'].' day ');
                            }
                        }

                        $update_array['edit_sap'] = '0';    //sap编辑状态重置
                        $result = $storeModel->editStore($update_array, array('store_id' => $storeInfo['store_id']));
                        if(!$result) throw new Exception('更新数据失败');
                        //所有该商家的商品重新推送（主要是商家模式改动了）
                        Model('goods')->where(array('store_id'=>$model['model_id']))->update(array('edit_sap'=>'0'));

                        return 'closed';
                    },
                    'reject' => '',
                ),
            ),
        );
    }

    public function approve($post, $service)
    {
        if ($service->getGroup() == '运营部') {
            /** @var storeModel $storeModel */
            $storeModel = Model('store');
            $model = $service->getModel();
            $newValue = $model['new_value'];
            $storeInfo = $storeModel->getStoreInfo(array('store_id' => $model['model_id']));
            // TODO 处理审批通过流程附加操作，若商品
        }
        return parent::approve($post, $service);
    }
}