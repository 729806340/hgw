<?php

/**
 * Author: Shen.L
 * Email: shen@shenl.com
 * Date: 2016/11/2
 * Time: 10:51
 */
require_once('WorkflowHandler.php');

class GoodsCost extends WorkflowHandler
{

    public function getId()
    {
        return 0;
    }
    public function getConfig()
    {
        return array(
            'name' => '商品成本审批流程',
            'model' => 'goods',
            'primary_key' => 'goods_id',
            'attributes' => array(
                array('name' => 'goods_cost', 'type' => 'text', 'label' => '商品成本', 'on' => array('运营部')),
                array('name' => 'goods_state', 'type' => 'text', 'label' => '上下架状态', 'on' =>array('运营部')),
                array('name' => 'sign_vp', 'type' => 'file', 'label' => '总裁签字', 'on' => array('运营部'), 'attachment' => true,'notice'=>'凭证上传','when'=>'/goods/apply/sign_vp'),
                array('name' => 'sign_cp' ,'type' => 'file', 'label' => '董事长签字', 'on'=>array('运营部'), 'attachment' => true,'notice'=>'凭证上传','when'=>'/goods/apply/sign_cp')
            ),
            /**
             * attributes 参数说明
             * name ：input名称/数据表字段名
             * type：input类型
             * label：input显示名称
             * on：显示条件
             * attachment：是否附件
             */
            'reference' => '/admin/modules/shop/index.php?act=goods&op=view&goods_id={id}',
            'start' => '运营部',// 启动用户组
            'flow' => array(
                '运营部' => array(
                    'approve' => function ($model) {

                        /** @var storeModel $storeModel */
                        $storeModel = Model('store');
                        /** @var goodsModel $goodsModel */
                        //商品下架
                        $goodsModel = Model('goods');
                        $goods_id = intval($model['model_id']);
                        $condition = array();
                        $data = array();
                        $condition['goods_id'] = $goods_id;
                        $data['goods_state'] = 0;
                        $result = $goodsModel->editGoods($data, $condition ,1);
                        if($result){
                            $newValue = $model['new_value'];
                            $goods_new_cost = ncPriceFormat(floatval($newValue['goods_cost']));
                            $goods_info = $goodsModel->getGoodsInfo(array('goods_id' => $model['model_id']));
                            $storeInfo = $storeModel->getStoreInfo(array('store_id' => $goods_info['store_id']));
                            if ($storeInfo['manage_type'] == 'co_construct') {
                                $goods_price = ncPriceFormat($goods_info['goods_price']);
                                if ((($goods_price - $goods_new_cost) > $goods_new_cost * 0.05)) {
                                    return '总经理';
                                }
                            }
                            return '财务部';
                        }
                        throw new Exception('审批失败');
                        //return '公司商务';
                    },
                    'reject' => '',
                    'attachment' => array(
                        ''
                    ),
                    'action'=>'goods_cost',
                ),
                '公司商务' => array(
                    'timeout'=>3600,
                    'approve' => function ($model) {

                        /** @var storeModel $storeModel */
                        $storeModel = Model('store');
                        /** @var goodsModel $goodsModel */
                        $goodsModel = Model('goods');
                        $newValue = $model['new_value'];
                        $goods_new_cost = ncPriceFormat(floatval($newValue['goods_cost']));
                        $goods_info = $goodsModel->getGoodsInfo(array('goods_id' => $model['model_id']));
                        $storeInfo = $storeModel->getStoreInfo(array('store_id' => $goods_info['store_id']));
                        if ($storeInfo['manage_type'] == 'co_construct') {
                            $goods_price = ncPriceFormat($goods_info['goods_price']);
                            if ((($goods_price - $goods_new_cost) > $goods_new_cost * 0.05)) {
                                return '总经理';
                            }
                        }
                        return '财务部';
                    },
                    'reject' => '运营部',
                ),

                '财务部' => array(
                    'timeout'=>3600,
                    'approve' => function($modle){
                        return '总经理';
                    },
                    'reject' => '',
                ),
                
                '总经理' => array(
                    'timeout'=>3600,
                    'approve' => function($model){
                        //删除缓存
                        delGoodsCache($model['model_id']);
                        return '';
                    },
                    'reject' => '',
                    ),
            ),
        );
    }
}