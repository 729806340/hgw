<?php
require_once ('WorkflowHandler.php');

class StoreNewFinancial extends WorkflowHandler
{

    public function getId()
    {
        return 6;
    }

    public function getConfig()
    {
        return array(
            'name' => '新增商家资质变更审批',
            'model' => 'store_joinin',
            'primary_key' => 'member_id',
            'attributes' => array(
                array(
                    'name' => 'company_name',
                    'type' => 'text',
                    'label' => '公司名称',
                    'on' => '运营部',
                ),
                
                array(
                    'name' => 'company_province_id',
                    'type' => 'text',
                    'label' => '公司所在省ID',
                    'on' => '运营部',
                ),
                
                array(
                    'name' => 'company_address',
                    'type' => 'text',
                    'label' => '公司地址',
                    'on' => '运营部',
                ),
                
                array(
                    'name' => 'company_address_detail',
                    'type' => 'text',
                    'label' => '公司地址详细地址',
                    'on' => '运营部',
                ),
                
                array(
                    'name' => 'company_employee_count',
                    'type' => 'text',
                    'label' => '员工人数',
                    'on' => '运营部',
                ),
                
                array(
                    'name' => 'company_registered_capital',
                    'type' => 'text',
                    'label' => '注册资金',
                    'on' => '运营部',
                ),
                
                array(
                    'name' => 'organization_code',
                    'type' => 'text',
                    'label' => '联系人',
                    'on' => '运营部',
                ),
                
                array(
                    'name' => 'contacts_phone',
                    'type' => 'text',
                    'label'=> '联系人电话',
                    'on'=>'运营部',
                ),
                
                array(
                    'name' => 'contacts_email',
                    'type' => 'text',
                    'label'=> '联系人邮箱',
                    'on'=>'运营部',
                ),
                array(
                    'name' => 'business_licence_number',
                    'type' => 'text',
                    'label'=> '营业执照号',
                    'on'=>'运营部',
                ),
                array(
                    'name' => 'business_licence_address',
                    'type' => 'text',
                    'label'=> '营业执照地址',
                    'on'=>'运营部',
                ),
                array(
                    'name' => 'business_licence_start',
                    'type' => 'text',
                    'label'=> '营业执照有效开始日期',
                    'on'=>'运营部',
                ),
             
                array(
                    'name' => 'business_licence_end',
                    'type' => 'text',
                    'label'=> '营业执照有效结束日期',
                    'on'=>'运营部',
                ),
                array(
                    'name' => 'business_sphere',
                    'type' => 'text',
                    'label'=> '营业执照范围',
                    'on'=>'运营部',
                ),
                array(
                    'name' => 'bank_account_name',
                    'type' => 'text',
                    'label'=> '银行开户名',
                    'on'=>'运营部',
                ),
                array(
                    'name' => 'bank_account_number',
                    'type' => 'text',
                    'label'=> '开户银行账号',
                    'on'=>'运营部',
                ),
                array(
                    'name' => 'bank_name',
                    'type' => 'text',
                    'label'=> '开户银行支行名称',
                    'on'=>'运营部',
                ),
                array(
                    'name' => 'bank_code',
                    'type' => 'text',
                    'label'=> '支行联行号',
                    'on'=>'运营部',
                ),
                array(
                    'name' => 'bank_address',
                    'type' => 'text',
                    'label'=> '开户银行所在地',
                    'on'=>'运营部',
                ),
                array(
                    'name' => 'is_settlement_accounttiny',
                    'type' => 'text',
                    'label'=> '开户行账号是否为结算账号',
                    'on'=>'运营部',
                ),
                array(
                    'name' => 'settlement_bank_account_name',
                    'type' => 'text',
                    'label'=> '结算银行开户名',
                    'on'=>'运营部',
                ),
               
                
                array(
                    'name' => 'settlement_bank_name',
                    'type' => 'text',
                    'label'=> '结算开户银行支行名称',
                    'on'=>'运营部',
                ),
                
                array(
                    'name' => 'settlement_bank_code',
                    'type' => 'text',
                    'label'=> '结算支行联行号',
                    'on'=>'运营部',
                ),
                
                array(
                    'name' => 'settlement_bank_address',
                    'type' => 'text',
                    'label'=> '结算开户银行所在地',
                    'on'=>'运营部',
                ),
                
                array(
                    'name' => 'tax_registration_certificate',
                    'type' => 'text',
                    'label'=> '税务登记证号',
                    'on'=>'运营部',
                ),
                
                array(
                    'name' => 'taxpayer_id',
                    'type' => 'text',
                    'label'=> '纳税人识别号',
                    'on'=>'运营部',
                ),
                array(
                    'name' => 'business_licence_number_elc',
                    'type' => 'file',
                    'label' => '营业执照',
                    'on' => '运营部',
                    'upaction'=>'upload_store_file',
                ),
                array(
                    'name' => 'organization_code_electronic',
                    'type' => 'file',
                    'label' => '组织机构代码证',
                    'on' => '运营部',
                    'upaction'=>'upload_store_file',
                ),
                array(
                    'name' => 'bank_licence_electronic',
                    'type' => 'file',
                    'label' => '开户银行许可证',
                    'on' => '运营部',
                    'upaction'=>'upload_store_file',
                ),
                array(
                    'name' => 'tax_registration_certif_elc',
                    'type' => 'file',
                    'label' => '税务登记证',
                    'on' => '运营部',
                    'upaction'=>'upload_store_file',
                ),
                array(
                    'name' => 'general_taxpayer',
                    'type' => 'file',
                    'label' => '一般纳税人证明',
                    'on' => '运营部',
                    'upaction'=>'upload_store_file',
                ),
                
            ),
            'reference' => '/goods/view?id={id}',
            'start' => '运营部', // 启动用户组
            'flow' => array(
                '运营部' => array(
                    'approve' => function ($model) {
                        return '财务部';
                    },
                    'reject' => '',
                    'action' => 'store_file',
                ),
                '公司商务' => array(
                    'timeout' => 3600,
                    'approve' => function ($model) {
                        return '财务部';
                    },
                    'reject' => '运营部',
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
                    'reject' => '运营部'
                ),
            )
        );
    }
}