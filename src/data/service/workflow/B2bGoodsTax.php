<?php

require_once('WorkflowHandler.php');

class B2bGoodsTax extends WorkflowHandler
{

    public function getId()
    {
        return 63;
    }
    public function getConfig()
    {
        return array(
            'name' => 'b2b商品税率审批流程',
            'model' => 'b2b_goods',
            'primary_key' => 'goods_id',
            'action'=>'action',
            'attributes' => array(
                array('name' => 'tax_input', 'type' => 'text', 'label' => '进项税率','on'=>'运营部'),
                array('name' => 'tax_output', 'type' => 'text', 'label' => '销项税率','on'=>'运营部'),
            ),
            /**
             * attributes 参数说明
             * value ：input名称/数据表字段名
             * type：input类型
             * label：input显示名称
             * on：显示条件
             * attachment：是否附件
             * when：附件显示条件，一般为字符串，在非附件项目变动时触发方法中予以处理
             */
//            'reference' => '/b2b/index.php?act=goods&op=index&goods_commonid={id}',
            'start' => '运营部',// 启动用户组
            'flow' => array(
                '运营部' => array(
                    'approve' => function ($model) {
                        return '财务部';
                    },
                    'reject' => '',
                    'attachment' => array(
                        ''
                    )
                ),

                '财务部' => array(
                    'timeout'=>3600,
                    'approve' => function($model){
                    	$newVal = $model['new_value'];
                        /** @var goodsModel $model_goods */
                    	$model_goods = Model('b2b_goods');
                    	$goods_update['tax_input'] = $newVal['tax_input'];
                    	$goods_update['tax_output'] = $newVal['tax_output'];
                    	$goods_where['goods_id'] = $model['model_id'];
                    	$model_goods->where($goods_where)->update($goods_update);

                        //删除缓存
                        delGoodsCache(0, $model['model_id']);
                        return 'closed';
                    },
                    'reject' => '运营部',
                ),
            ),
        );
    }
}