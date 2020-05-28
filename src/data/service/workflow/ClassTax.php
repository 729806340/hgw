<?php

require_once('WorkflowHandler.php');

class ClassTax extends WorkflowHandler
{

    public function getId()
    {
        return 51;
    }
    public function getConfig()
    {
        return array(
            'name' => '类目税率审批流程',
            'model' => 'goods_class',
            'primary_key' => 'gc_id',
        	'action' => 'action',
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
            'reference' => '/goods/view?id={id}',
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
                    'approve' => function($modle){
                        return '';
                    },
                    'reject' => '运营部',
                ),
            ),
        );
    }
}