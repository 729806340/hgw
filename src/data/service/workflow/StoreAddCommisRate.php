<?php
require_once ('WorkflowHandler.php');

class StoreAddCommisRate extends WorkflowHandler
{

    public function getId()
    {
        return 5;
    }

    public function getConfig()
    {
        return array(
            'name' => '平台商家新增类目佣金审批',
            'model' => 'store_bind_class',
            'primary_key' => 'store_id',
            'attributes' => array(
                array(
                    'name' => 'store_id',
                    'type' => 'text',
                    'label' => '店铺编号',
                    'on' => '商家',
                ),
                array(
                    'name' => 'commis_rate',
                    'type' => 'text',
                    'label' => '佣金比例',
                    'on' => '运营部',
                ),
                array(
                    'name' => 'class_1',
                    'type' =>'text',
                    'label'=>'一级分类',
                    'on'=>'',
                ),
                array(
                    'name' => 'class_2',
                    'type' =>'text',
                    'label'=>'二级分类',
                    'on'=>'',
                ),
                array(
                    'name' => 'class_3',
                    'type' =>'text',
                    'label'=>'三级分类',
                    'on'=>'',
                ),
                
            ),
            'reference' => '/goods/view?id={id}',
            'start' => '运营部', // 启动用户组
            'flow' => array(
                '运营部' => array(
                    'approve' => function ($model) {
                        return '总经理';
                    },
                    'reject' => '',
                    'action' => 'commis_rate',
                ),
                '总经理' => array(
                    'timeout' => 3600,
                    'approve' => function ($model) {
                        return '财务部';
                    },
                    'reject' => '运营部',
                ),
                '公司商务' => array(
                    'timeout' => 3600,
                    'approve' => function ($model){
                        return '财务部';
                    },
                    'reject' =>'运营部',
                ),
                '财务部' => array(
                    'timeout' => 3600,
                    'approve' => function ($model) {
                        $data = array();
                        $data = $model['new_value'];
                        $data['store_id'] = $model['model_id'];
                        $data['state'] = 1;
                        /** @var store_bind_classModel $model_store_bind_class */
                        $model_store_bind_class = Model('store_bind_class');
                        $model_store_bind_class->addStoreBindClass($data);
                        return 'closed';
                    },
                    'reject' => '运营部'
                ),
            )
        );
    }
}