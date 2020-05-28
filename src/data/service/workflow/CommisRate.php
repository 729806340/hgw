<?php
require_once ('WorkflowHandler.php');

class CommisRate extends WorkflowHandler
{

    public function getId()
    {
        return 4;
    }

    public function getConfig()
    {
        return array(
            'name' => '类目佣金变更审批',
            'model' => 'store_bind_class',
            'primary_key' => 'bid',
            'attributes' => array(
                array(
                    'name' => 'commis_rate',
                    'type' => 'text',
                    'label' => '佣金比例',
                    'on' => '运营部',
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
                        return '';
                    },
                    'reject' => '运营部'
                ),
            )
        );
    }
}