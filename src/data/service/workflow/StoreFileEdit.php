<?php
require_once ('WorkflowHandler.php');

class StoreFileEdit extends WorkflowHandler
{

    public function getId()
    {
        return 3;
    }

    public function getConfig()
    {
        return array(
            'name' => '商家资质变更修改审批',
            'model' => 'store_joinin',
            'primary_key' => 'member_id',
            'attributes' => array(
                array(
                    'name' => 'company_name',
                    'type' => 'text',
                    'label' => '公司名称',
                    'on' => '商家',
                ),
                
            ),
            'reference' => '/admin/modules/shop/index.php?act=store&op=store_edit&store_id={id}',
            'start' => '商家', // 启动用户组
            'flow' => array(
            	'商家' => array(
            			'approve' => function ($model) {
            				return '运营部';
            			},
            			'reject' => '',
            	),
                '运营部' => array(
                    'approve' => function ($model) {
                        return '财务部';
                    },
                    'reject' => function ($model) {
                    	if($model['role']==1){
                    		return '商家';
                    	} elseif ($model['role']==0) {
                    		return '';
                    	}
                    },
                    //'action' => 'store_file',
                ),
                '公司商务' => array(
                    'timeout' => 3600,
                    'approve' => function ($model) {
                        return '财务部';
                    },
                    'reject' => function ($model) {
                    	if($model['role']==1){
                    		return '商家';
                    	} elseif ($model['role']==0) {
                    		return '运营部';
                    	}
                    },
                ),
                '财务部' => array(
                    'timeout' => 3600,
                    'approve' => function ($model) {
                        $new_value = $model['new_value'];
   
                        $upload_dir = UPLOAD_SITE_URL . DS . ATTACH_STORE_JOININ . DS;
                        $update = array();
                        $file_type = array(
                            'general_taxpayer' ,
                            'tax_registration_certif_elc' ,
                            'bank_licence_electronic',
                            'organization_code_electronic',
                            'business_licence_number_elc'
                        );
                        foreach($new_value as $k=>$v){
                            if(in_array($k, $file_type)){
                                $update[$k] = str_replace($upload_dir,'', $v);
                            }else{
                                $update[$k] = $v;
                            }
                        }
                        $condition = array();
                        $condition['member_id'] = $model['model_id'];
                        Model('store_joinin')->editStoreJoinin($condition , $update);
                        return 'closed';
                    },
                    'reject' => function ($model) {
                    	if($model['role']==1){
                    		return '商家';
                    	} elseif ($model['role']==0) {
                    		return '运营部';
                    	}
                    },
                ),
            )
        );
    }
}