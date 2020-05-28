<?php
require_once ('WorkflowHandler.php');

class BusinessAddClass extends WorkflowHandler
{

    public function getId()
    {
        return 7;
    }

    public function getConfig()
    {
        return array(
            'name' => '商家新增经营类目审核',
            'model' => 'store_bind_class',
            'primary_key' => 'bid',
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
            'start' => '商家', // 启动用户组
            'flow' => array(
                '商家' => array(
                    'approve' => function ($model) {
                    return '运营部';
                    },
                    'reject' => '',
                    'action' => 'commis_rate',
                 ),
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
                    'reject' => '商家',
                ),
                '公司商务' => array(
                    'timeout' => 3600,
                    'approve' => function ($model){
                        return '财务部';
                    },
                    'reject' =>'商家',
                ),
                '财务部' => array(
                    'timeout' => 3600,
                    'approve' => function ($model) {
                        $data = array();
                        $data = $model['new_value'];
                        $data['state'] = 1;
                        $condition = array();
                        $condition['bid'] = $model['model_id'];
                        $model_store_bind_class = Model('store_bind_class');
                        $model_store_bind_class->editStoreBindClass($data, $condition);
                        return 'closed';
                    },
                    'reject' => '商家'
                ),
            )
        );
    }
}