<?php

/**
 * Author: 侯珊珊
 * Email: shen@shenl.com
 * Date: 2016/11/2
 * Time: 10:51
 */
require_once('WorkflowHandler.php');

class GoodsCostByShop extends WorkflowHandler
{

    public function getId()
    {
        return 54;
    }

    public function getConfig()
    {
        return array(
            'name' => '商家修改成本价审批流程',
            'description' => '',
            'model' => 'goods',
            'primary_key' => 'goods_id',
            'action' => 'action',
            'attributes' => array(
                array('name' => 'goods_cost', 'type' => 'text', 'label' => '商品成本', 'on' => array('运营部'), 'notice' => '共建商品需要填写商品成本'),
                //array('name' => 'goods_state', 'type' => 'text', 'label' => '上下架状态', 'on' =>array('运营部')),
                array('name' => 'certification', 'type' => 'file', 'label' => '商品资质', 'on' => array('商家'), 'notice' => '共建商品需要填写商品成本'),
                array('name' => 'control', 'type' => 'select', 'label' => '处理意见', 'items'=>array('同意','资质有问题','成本/税率有问题'), 'on' => array('公司商务'), 'mod' => 'control','hide_control'=>true, 'notice' => ''),
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
            'start' => '商家',// 启动用户组
            'flow' => array(
                '商家' => array(
                    'approve' => function ($model) {
                        $storeModel = Model('store');
                        /** @var goodsModel $goodsModel */
                        $goodsModel = Model('goods');
                        $goods_info = $goodsModel->getGoodsInfo(array('goods_id' => $model['model_id']));
                        $storeInfo = $storeModel->getStoreInfo(array('store_id' => $goods_info['store_id']));
                        $newValue = $model['new_value'];
                        $goods_new_cost = ncPriceFormat(floatval($newValue['goods_cost']));
                        if ($storeInfo['manage_type'] == 'co_construct') {
                            $goods_price = ncPriceFormat($goods_info['goods_price']);
                            if ((($goods_price - $goods_new_cost) > $goods_new_cost * 0.05)) {
                                $updateGoods = array('goods_verify'=>1,);
                                $updateGoods['goods_cost']= $newValue['goods_cost'];
                                $goodsModel->table('goods')->where(array('goods_id' => $model['model_id']))->update($updateGoods);
                                delGoodsCache(0, $model['model_id']);
                                return 'closed';
                            }
                        }
                        return '运营部';
                    },
                    'reject' => '',
                ),
                '运营部' => array(
                    'approve' => function ($model) {
                        /** @var storeModel $storeModel */
                        $storeModel = Model('store');
                        /** @var goodsModel $goodsModel */
                        $goodsModel = Model('goods');
                        $goods_info = $goodsModel->getGoodsInfo(array('goods_id' => $model['model_id']));
                        $storeInfo = $storeModel->getStoreInfo(array('store_id' => $goods_info['store_id']));
                        /*if ($storeInfo['manage_type'] == 'co_construct') {
                            return '运营总监';
                        }*/
                        return '财务部';
                    },
                    'reject' => '',
                ),
                '运营总监' => array(
                    'timeout' => 3600,
                    'approve' => function ($model) {
                        return '财务部';
                    },
                    'reject' => '运营部',
                ),
                '公司商务' => array(
                    'timeout' => 3600,
                    'approve' => function ($model) {
                        return '财务部';
                    },
                    'reject' => function ($model,$control) {
                        if(isset($control['control'])&&$control['control']==1){ // 资质有问题
                            return '商家';
                        }
                        return '运营部';
                    },
                ),

                '财务部' => array(
                    'timeout' => 3600,
                    'approve' => function ($model) {
                        /** @var storeModel $storeModel */
                        $storeModel = Model('store');
                        /** @var goodsModel $goodsModel */
                        $goodsModel = Model('goods');
                        $goods_info = $goodsModel->getGoodsInfo(array('goods_id' => $model['model_id']));
                        $storeInfo = $storeModel->getStoreInfo(array('store_id' => $goods_info['store_id']));
                        $newValue = $model['new_value'];
                        $updateGoods = array('goods_verify'=>1,);
                        if($storeInfo['manage_type']=="co_construct"){
                            $updateGoods['goods_cost']= $newValue['goods_cost'];
                        }
                        $goodsModel->table('goods')->where(array('goods_id' => $model['model_id']))->update($updateGoods);
                        //v($storeInfo);
                        //删除缓存
                        delGoodsCache(0, $model['model_id']);
                        return 'closed';
                    },
                    'reject' => '运营部',
                ),
            ),
        );
    }

    public function response($post, $service)
    {
        $attributes = $this->getAttributes();
        foreach ($attributes as $attribute){
            if(isset($attribute['on'])&&!in_array($service->getGroup(),(array)$attribute['on'])) continue;
            if(isset($attribute['mod'])&&$attribute['mod']=='control'&&isset($post[$attribute['name']])){
                $post['opinion'] = $post[$attribute['name']]==0?1:0;
            }
        }
        return parent::response($post, $service);
    }

    public function approve($post, $service)
    {
        if ($service->getGroup() == '运营部') {
            /** @var storeModel $storeModel */
            $storeModel = Model('store');
            /** @var goodsModel $goodsModel */
            $goodsModel = Model('goods');
            $model = $service->getModel();
            $newValue = $model['new_value'];
            $goods_new_cost = ncPriceFormat(floatval($post['goods_cost']));
            $goods_info = $goodsModel->getGoodsInfo(array('goods_id' => $model['model_id']));
            $storeInfo = $storeModel->getStoreInfo(array('store_id' => $goods_info['store_id']));
            if ($storeInfo['manage_type'] == 'co_construct') {
                if($goods_new_cost<=0) return '共建商品成本必须设置';
            }
        }
        return parent::approve($post, $service);
    }

}