<?php

/***
 * 审批流程模型
 * 
 * @author ljq
 */
class workflowModel extends Model
{
    const STATUS_CREATED = 0; //新创建
    const STATUS_FINISHED = 1; //已完成
    const STATUS_CANCELED = 2; //已取消
    const STATUS_PROCESSING = 10; // 处理中
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * *
     * 添加审批流程
     *
     * @param array $data
     */
    public function addWorkflow($data = array())
    {
        $data['created_at'] = time();
        $data['updated_at'] = time();
        if(is_array($data['old_value'])) $data['old_value'] = json_encode($data['old_value']);
        if(is_array($data['new_value'])) $data['new_value'] = json_encode($data['new_value']);
        if (! $insertId = $this->table('workflow')->insert($data)) {
            return false;
        }
        return $insertId;
    }

    
    public function editWorkflow($data = array(), $condition = array()) {
        if(isset($data['old_value'])&&is_array($data['old_value'])) $data['old_value'] = json_encode($data['old_value']);
        if(isset($data['new_value'])&&is_array($data['new_value'])) $data['new_value'] = json_encode($data['new_value']);
        return $this->table('workflow')->where($condition)->update($data);
    }
    
    
    public function getWorkflowInfo($condition, $field = '*'){
        $data = $this->table('workflow')->field($field)->where($condition)->find();
        if(!empty($data)){
            $data['old_value'] = json_decode($data['old_value'],true);
            $data['new_value'] = json_decode($data['new_value'],true);
        }
        return $data;
    }

    /**
     * 获取单条审核信息
     *
     */
    public function getWorkflowdetail($id)
    {
        $workflow = $this->getWorkflowInfo(array('id'=>$id));
        $ret = $this->getModel($workflow['model'], $workflow['model_id']);
        $workflow['label_name'] = $ret['label_name'];
        $workflow['value_name'] = $ret['value_name'];
        $workflow['log'] = $this->getWorkflowLog($workflow['id']);
        //$workflow = $this->getLabelValue($workflow);
        return $workflow;
    }
    
    /***
     * 获取json属性值
     * @param unknown $array
     */
    private function getAttributes($type){
        /** @var WorkflowService $service */
        $service = Service('Workflow');
        $service->init();
        $service->setTpe($type);
        return $service->getAttributes();
    }
    
    /**
     * 写得蛮稀烂，求大神优化
     * 解析json字符值
     */
    private function getLabelValue( $workflow ){
        $attributes = $this->getAttributes($workflow['type']);
        $label_arr = array_under_reset($attributes, 'name');
        $new_value = json_decode($workflow['new_value']);
        $old_value = json_decode($workflow['old_value']);
        $workflow['new_value'] = array();
        $workflow['old_value'] = array();
        foreach($new_value as $k=>$v){
            $workflow['new_value'][] = array('label'=>$label_arr[$k]['label'] , 'value'=>$v);
        }
        foreach($old_value as $k=>$v){
            $workflow['old_value'][] = array('label'=>$label_arr[$k]['label'] , 'value'=>$v);
        }      
        
        if(count($workflow['log'])>0){
            foreach($workflow['log'] as $k=>$v){
                if($v['attachment']){
                    $attachment  = json_decode($v['attachment']);
                    unset($workflow['log'][$k]['attachment']);
                    foreach($attachment as $key =>$val){
                        foreach($val as $ke =>$va){
                            $workflow['log'][$k]['attachment'][$key]['label'] = $label_arr[$ke]['label'];
                            $workflow['log'][$k]['attachment'][$key]['name'] =$va;
                        }
                    }
                }
            }
        }
        return $workflow;
    }

    /**
     * *
     * 获取审批流程列表
     * 
     * @param unknown $condition
     *            查询条件
     * @param string $page
     *            页数
     * @param unknown $field
     *            查询字段
     */
    public function getWorkflowList($condition = array(),$page='',$field="*" , $order='id asc')
    {
        return $this->table('workflow')->field($field)->where($condition)->page($page)->order($order)->select();
    }
       
    
    /**
     * 获取审批流程日志
     * @param int $id 审批编号
     */
    private function getWorkflowLog($id){
        $condition['workflow_id'] = intval($id);
        $items =  Model('workflow_log')->field('*')->where($condition)->select();
        if(!empty($items)) {
            foreach ($items as $key=>$item)
                $items[$key]['attachment'] = (array) json_decode($items[$key]['attachment'],true);
        }
        return $items;
    }
    
    /**
     * 获取审核对象
     */
    private  function getModel( $model , $model_id){
        if(!in_array($model,array('goods' , 'orders'))){
            return false;
        }
        $ret = array();   //返回数组
        switch($model){
            case 'goods':
                $result = Model('goods')->field('goods_name')->where($model_id)->find();
                $ret['label_name'] = '商品名称';
                $ret['value_name'] = $result['goods_name'];
                break;
        }
        
        return $ret;
    }
}