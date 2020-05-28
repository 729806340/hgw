<?php
/**
 * 审批管理
 * @汉购网提供技术支持 授权请购买shopnc授权
 * @license    http://www.hangowa.com
 * @link       交流群号：
 */
defined('ByShopWWI') or exit('Access Invalid!');
class workflowControl extends SystemControl{
    const EXPORT_SIZE = 5000;
    private $links = array(
        array('url'=>'act=workflow&op=needmy','text'=>'需要我审批'),
        array('url'=>'act=workflow&op=timeout','text'=>'已超期审批'),
        array('url'=>'act=workflow&op=ismy' , 'text'=>'我发起的审批'),
        array('url'=>'act=workflow&op=myjoin' , 'text'=>'我参与的审批'),
        array('url'=>'act=workflow&op=close' , 'text'=>'已完成的审批'),
        array('url'=>'act=workflow&op=cancel' , 'text'=>'中断的审批'),
    );
    
    private $workflow_type = array(
        '0' => '商品成本变更审批',
        '1' => '非平台商品-商家修改商品信息审批',
        '2' => '平台商品-商家修改商品信息审批',
        '3' => '商家资质变更审批',
        '4' => '平台商家-类目佣金变更审批',
        '5' => '平台商家-新增类目佣金审批',
        '6' => '新增商家资质审批',
        '7' => '商家-新增类目审批',
    	'51' => '类目税率变更审批',
    	'52' => '商品税率变更审批',
    	'53' => '商家入驻流程审批',
    	'63' => 'B2B商品税率变更审批',
        '54' => '商家修改成本价流程审批',
    );
    
    private $workflow_status = array(
        '0' => '已创建',
        '1' => '已完成',
        '2' => '已废除',
        '10' => '审核中',
    );
    
    private $role = array(
        '0'=>'系统用户',
        '1'=>'商家',
    );
    
    public function __construct() {
        parent::__construct ();
    }
    
    public function indexOp(){
        $this->needmyOp();
    }
    /***
     * 需要我审批的
     */
    public function needmyOp(){
        Tpl::output('type', 'needmy');
        Tpl::output('top_link',$this->sublink($this->links,'needmy'));
        Tpl::output('workflow_type' , $this->workflow_type);
        Tpl::setDirquna('shop');
        Tpl::showpage('workflow.index');
    }
    
    public function ismyOp(){
        Tpl::output('type', 'ismy');
        Tpl::output('top_link',$this->sublink($this->links,'ismy'));
        Tpl::output('workflow_type' , $this->workflow_type);
        Tpl::setDirquna('shop');
        Tpl::showpage('workflow.index');
    }
    
    public function timeoutOp(){
        Tpl::output('type', 'timeout');
        Tpl::output('top_link',$this->sublink($this->links,'timeout'));
        Tpl::output('workflow_type' , $this->workflow_type);
        Tpl::setDirquna('shop');
        Tpl::showpage('workflow.index');
    }
    
    public function closeOp(){
        Tpl::output('type', 'close');
        Tpl::output('top_link',$this->sublink($this->links,'close'));
        Tpl::output('workflow_type' , $this->workflow_type);
        Tpl::setDirquna('shop');
        Tpl::showpage('workflow.index');
    }
    
    public function cancelOp(){
        Tpl::output('type', 'cancel');
        Tpl::output('top_link',$this->sublink($this->links,'cancel'));
        Tpl::output('workflow_type' , $this->workflow_type);
        Tpl::setDirquna('shop');
        Tpl::showpage('workflow.index');
    }
    
    public function myjoinOp(){
        Tpl::output('type', 'myjoin');
        Tpl::output('top_link',$this->sublink($this->links,'myjoin'));
        Tpl::output('workflow_type' , $this->workflow_type);
        Tpl::setDirquna('shop');
        Tpl::showpage('workflow.index');
    }
    
    
    public function get_xmlOp(){
        $workflowModel = Model('workflow');
        $where = array();
        $condition = array();
        if ($_REQUEST['query'] != '' && in_array($_REQUEST['qtype'],array('order_sn','store_name','title','type','stage','role','user'))) {
            $where[$_REQUEST['qtype']] = array('like',"%{$_REQUEST['query']}%");
        }
        if ($_REQUEST['query'] != '' && in_array($_REQUEST['qtype'],array('id'))) {
            $where[$_REQUEST['qtype']] = array('eq',"{$_REQUEST['query']}");
        }

        if($_GET['type'] == 'needmy'){
            $where['stage'] = $this->admin_info['gname'];
        }elseif($_GET['type']=='ismy'){
           $where['role'] = 0;
           $where['user'] = $this->admin_info['name'];
        }elseif($_GET['type']=='timeout'){
            $where['stage'] = $this->admin_info['gname'];
            $where['timeout_at'] = array('lt' , time());
        }elseif($_GET['type']=='close'){
            $where['status'] = 1;
        }elseif($_GET['type']=='cancel'){
            $where['stage'] ='canceled';
        }elseif($_GET['type']=='myjoin'){
            $condition['user'] = $this->admin_info['name'];
            $condition['role'] = 0;
            $workflowIds = Model('workflow_log')->field('workflow_id')->where($condition)->limit(false)->select();
            if(count($workflowIds)>0){
                $flowIds = array();
                foreach($workflowIds as $k=>$v){
                    $flowIds[]= $v['workflow_id'];
                }
                $flowIds = array_unique($flowIds);
                $where['id'] = array('in' , $flowIds);
            }else{
                $where['user'] = $this->admin_info['name'];
            }
        }
        $order =$_POST['sortname']." ".$_POST['sortorder'];
        $result = $workflowModel->getWorkflowList($where, $_POST['rp'], '*', $order);
        $data = array();
        $data['now_page'] =  $workflowModel->shownowpage();
        $data['total_num'] = $workflowModel->gettotalnum();
        if(count($result)>0){
            foreach($result as $k =>$v){
                $param = array();
                $operation = "<a class='btn orange' target='_blank' href='index.php?act=workflow&op=detail&id=".$v['id']."'><i class='fa fa-check-square'></i>查看详细</a>";
                $param['operation'] =$operation;
                $param['id'] = $v['id'];
                $param['title'] = $v['title'];
                $param['type'] = $this->workflow_type[$v['type']];
                $param['stage'] = $v['stage'];
                $param['status'] = $this->workflow_status[$v['status']];
                $param['role'] = $this->role[$v['role']];
                $param['user'] = $v['user'];
                $param['created_at'] = date('Y-m-d H:i' , $v['created_at']);
                $data['list'][$v['id']] = $param;
            }
        }
        echo Tpl::flexigridXML($data);exit();
    }
    
    public function detailOp(){
        $id = intval($_GET['id']);
        $workflow = Model('workflow')->getWorkflowdetail($id);
        $users = array();
        foreach($workflow['log'] as $k=>$v){
            if($v['role']==0) $users[] = $v['user'];
        }
        if(in_array($workflow['status'] , array(0,10)) && !in_array($this->admin_info['name'] , $users)){
            if(($this->admin_info['gname'] != $workflow['stage']) && $workflow['role']=='1'){
                showMessage('对不起！您无权查看');exit();
            }
            if(($this->admin_info['gname'] != $workflow['stage']) && ($workflow['user'] != $this->admin_info['name'])){
                showMessage('对不起！您无权查看');exit();
            }
        }
        /** @var WorkflowService $service */
        $service = Service('Workflow');
        $service->init($workflow,$this->admin_info['name'],$this->admin_info['gname']);
        if($workflow['stage']==$this->admin_info['gname']){
            Tpl::output('form' , $service->getForm());
        }
        
        Tpl::output('view' , $service->getView());
        Tpl::output('attributes' , array_under_reset($service->getAttributes(),'name'));

        $workflow['type'] = $this->workflow_type[$workflow['type']];
        $workflow['status'] = $this->workflow_status[$workflow['status']];
        $workflow['role'] = $this->role[$workflow['role']];
        $workflowConfig = $service->getConfig();
        if(empty($workflow['reference'])&&isset($workflowConfig['reference']))
            $workflow['reference'] = $service->getHandler()->getReference($workflow['model_id']);
        
        $service = Service('Workflow');
        $service->init($workflow , $this->admin_info['name'] , $this->admin_info['gname']);    
        Tpl::output('gname' , $this->admin_info['gname']);
        Tpl::output('workflow' , $workflow);
        Tpl::setDirquna('shop');
        Tpl::showpage('workflow.detail');
    }
    
    /***
     * 处理审批
     */
    public function reduce_workflowOp(){
        $ret = array();
        $id = intval($_POST['id']);
        $opinion = intval($_POST['opinion']);
        $message = trim($_POST['message']);
        $workflow = Model('workflow')->getWorkflowInfo(array('id'=>$id));
        if(!$workflow['id']){
            $ret['state'] = false;
            $ret['msg'] = '非法参数';
            die(JSON($ret));
        }
        
        if($this->admin_info['gname'] != $workflow['stage']){
            $ret['state'] = false;
            $ret['msg'] = '对不起！您无权审核';
            die(JSON($ret));
        }
        
        /** @var WorkflowService $service */
        $service = Service('Workflow');
        // 初始化数据
        $service->init($workflow,$this->admin_info['name'],$this->admin_info['gname']);
        if($opinion==1){
            $service->approve($message);
        }else{
            $service->reject($message);
        }
        
        $ret['state']=true;
        die(JSON($ret));
    }
    
    public function uploadOp(){
        if($_FILES['sign']['name'] !=''){
            /**
             * 上传图片
             */
            $upload = new UploadFile();
            $upload->set('default_dir', 'shop'. DS .'sign' . DS  . $upload->getSysSetPath());
            $upload->set('max_size', C('image_max_filesize'));
            $result = $upload->upfile('sign',true);
            if ($result) {
                $pic ='shop'.DS.'sign'.DS.$upload->getSysSetPath() . $upload->file_name;
                $data = array ();
                $data['file_path'] = $pic;
                $data['state'] = 'true';
                $output = json_encode ( $data );
                die($output);
            } else {
                // 目前并不出该提示
                $error = $upload->error;
                if (strtoupper(CHARSET) == 'GBK') {
                    $error = Language::getUTF8($error);
                }
                $data['state'] = 'false';
                $data['message'] = $error;
                $data['origin_file_name'] = $_FILES["file"]["name"];
                echo json_encode($data);
                exit();
            }
        }else{

            $data['state'] = 'false';
            $data['message'] = '没有任何上传文件';
            echo json_encode($data);
            die();
        }
    }
    
    /**
     * 共建商品信息审核，运营人员上传凭证验证
     */
    public function con_goodsOp(){
        $ret = array();
        $opinion = intval($_POST['opinion']);
        $message = trim($_POST['message']);
        $sign_ceo = $_POST['sign_ceo'];
        $id = intval($_POST['id']);
        $workflow = $this->__checkWorkflow($id);
        if($workflow['state']==false){
            die(JSON($workflow));
        }
        /** @var WorkflowService $service */
        $service = Service('Workflow');
        // 初始化数据
        $service->init($workflow,$this->admin_info['name'],$this->admin_info['gname']);
        //不同意
        if($opinion==0){
            $service->reject($message);
            $ret['state']=true;
            die(JSON($ret));
        }
        if(isset($workflow['new_value']['goods_price'])){

            //同意
            $goods_new_price = ncPriceFormat($workflow['new_value']['goods_price']);
            //获取商品成本
            $condition = array('goods_id'=>$workflow['model_id']);
            $goods_info = Model('goods')->getGoodsInfo($condition ,'goods_cost');
            $goods_cost = ncPriceFormat($goods_info['goods_cost']);

            if((($goods_new_price-$goods_cost) < $goods_cost*0.05) && empty($_POST['sign_ceo'])){
                $ret['state'] = false;
                $ret['msg'] = '请上传总裁签字！';
                die(JSON($ret));
            }

            if(($goods_new_price < $goods_cost) && empty($_POST['sign_president'])){
                $ret['state'] = true;
                $ret['msg'] = '请上传董事长！';
                die(JSON($ret));
            }
        }

        $attachment = array();
        !empty($_POST['sign_ceo']) and $attachment['sign_ceo'] = $_POST['sign_ceo'];
        !empty($_POST['sign_president']) and $attachment['sign_president'] = $_POST['sign_president'];
        count($attachment)>0 and $attachment = JSON($attachment);

        $newValue = array();
        if(isset($_POST['tax_input'])){
            if(!isset($workflow['new_value']['tax_input'])||$workflow['new_value']['tax_input']!=$_POST['tax_input'])
                $newValue['tax_input'] = $_POST['tax_input'];
        }
        if(isset($_POST['tax_output'])){
            if(!isset($workflow['new_value']['tax_output'])||$workflow['new_value']['tax_output']!=$_POST['tax_output'])
                $newValue['tax_output'] = $_POST['tax_output'];
        }
        if (!empty($newValue)) {
            $newValue = array_merge($workflow['new_value'],$newValue);
            $service->workflowModel->editWorkflow(array('new_value' => $newValue), array('id' => $workflow['id']));
        }
        $service->approve($message , $attachment);
        $ret['state'] = true;
        die(JSON($ret));
    }
    
    /**
     * 商品成本被驳回，运营人员重新提交
     */
    public function goods_costOp(){
        $ret = array();
        $opinion = intval($_POST['opinion']);
        $message = trim($_POST['message']);
        $id = intval($_POST['id']);
        $workflow = $this->__checkWorkflow($id);
        if($workflow['state']==false){
            die(JSON($workflow));
        }
        $service = Service('Workflow');
        // 初始化数据
        $service->init($workflow,$this->admin_info['name'],$this->admin_info['gname']);
        //不同意直接cancel
        if($opinion==0){
            $message = $this->admin_info['name']."放弃此次商品成本变更";
            $service->cancel($message);
            $ret['state']=true;
            die(JSON($ret));
        }
        
        $goodsInfo = Model('goods')->getGoodsInfo(array('goods_id'=>$workflow['model_id']) ,'goods_price');
        $goods_price = ncPriceFormat($goodsInfo['goods_price']);
        $goods_cost = ncPriceFormat($_POST['goods_cost']);
        if(($goods_price-$goods_cost < $goods_cost*0.05) && $_POST['sign_vp']==''){
            $ret['state'] = false;
            $ret['msg'] = '毛利小于5%请上传分管总裁签字凭证';
            die(JSON($ret));
        }
        
        if(($goods_price < $goods_cost) && $_POST['sign_cp']==''){
            $ret['state'] = false;
            $ret['msg'] = '负毛利请上传董事长签字凭证';
            die(JSON($ret));
        }
        $new_value = array('goods_cost'=>$goods_cost ,'goods_state'=>$goodsInfo['new_value']['goods_state']);
        $data = array();
        $condition = array();
        $data['new_value'] = JSON($new_value);
        $condition['id'] = $workflow['id'];
        if(!Model('workflow')->editWorkflow($data,$condition)){
            $ret['state'] = false;
            $ret['msg'] = '商品成本更新失败';
            die(JSON($ret));
        }
        
        $attachment = array();
        !empty($_POST['sign_vp']) and $attachment['sign_vp'] = $_POST['sign_vp'];
        !empty($_POST['sign_cp']) and $attachment['sign_cp'] = $_POST['sign_cp'];
        count($attachment)>0 and $attachment = JSON($attachment);
        $service->approve($message , $attachment);
        $ret['state'] = true;
        die(JSON($ret));
        
    }

    /**
     * 启动一个新的审批流程
     */
    public function launchOp()
    {
        $id = intval($_GET['id']);
        $type = intval($_GET['type']);

    }

    /** 商品发布审批 */
    public function actionOp()
    {
        $id = intval($_POST['id']);
        /** @var workflowModel $model */
        $model = Model('workflow');
        $workflow = $model->getWorkflowInfo(array('id'=>$id));
        if(empty($workflow)) die(JSON(array('state'=>false,'msg'=>'非法操作')));
        /** @var WorkflowService $service */
        $service = Service('Workflow');
        $service->init($workflow,$this->admin_info['name'],$this->admin_info['gname']);
        die(JSON($service->response($_POST)));

    }
    /**
     * 公司资质文件上传
     */
    public function upload_store_fileOp(){
        if($_FILES['sign']['name'] !=''){
            /**
             * 上传图片
             */
            $upload = new UploadFile();
            $upload_dir = ATTACH_PATH . DS . 'store_joinin' . DS;
            $upload->set('default_dir', $upload_dir);
            $upload->set('max_size', C('image_max_filesize'));
            $upload->set('allow_type', array('jpg', 'jpeg', 'gif', 'png'));
            $result = $upload->upfile('sign',true);
            if ($result) {
                $pic =$upload_dir . $upload->file_name;
                $data = array ();
                $data['file_path'] = $pic;
                $data['state'] = 'true';
                $output = json_encode ( $data );
                die($output);
            } else {
                // 目前并不出该提示
                $error = $upload->error;
                if (strtoupper(CHARSET) == 'GBK') {
                    $error = Language::getUTF8($error);
                }
                $data['state'] = 'false';
                $data['message'] = $error;
                $data['origin_file_name'] = $_FILES["file"]["name"];
                echo json_encode($data);
                exit();
            }
        }else{
        
            $data['state'] = 'false';
            $data['message'] = '没有任何上传文件';
            echo json_encode($data);
            die();
        }
    }
    /**
     * 公司资质驳回后，重新发起的处理
     */
    public function store_fileOp(){
        $ret = array();
        $opinion = intval($_POST['opinion']);
        $message = trim($_POST['message']);
        $id = intval($_POST['id']);
        $workflow = $this->__checkWorkflow($id);
        if($workflow['state']==false){
            die(JSON($workflow));
        }
        $service = Service('Workflow');
        // 初始化数据
        $service->init($workflow,$this->admin_info['name'],$this->admin_info['gname']);
        //不同意直接cancel
        if($opinion==0){
            $message = $this->admin_info['name']."放弃此次商家资质变更";
            $service->cancel($message);
            $ret['state']=true;
            die(JSON($ret));
        }
        $post = array();
        $file_type = array(
            'general_taxpayer' ,
            'tax_registration_certif_elc' , 
            'bank_licence_electronic',
            'organization_code_electronic',
            'business_licence_number_elc'
        );
        $new_value = $workflow['new_value'];
        $new_old_value = array();
        $store_joinin = Model('store_joinin')->getOne(array('member_id'=>$workflow['model_id']));
        foreach($new_value as $k=>$v){
            if($_POST[$k]){
                $post[$k] = $v;
                if(in_array($k  ,$file_type)){
                    $new_old_value[$k] = getStoreJoininImageUrl($store_joinin[$k]);
                }else{
                    $new_old_value[$k] = $store_joinin[$k];
                }
            }
        }
        $data = array();
        $condition = array();
        $data['new_value'] = JSON($post);
        $data['old_value'] = JSON($new_old_value);
        //die(var_dump($data));
        $condition['id'] = $workflow['id'];
        if(!Model('workflow')->editWorkflow($data,$condition)){
            $ret['state'] = false;
            $ret['msg'] = '商家资质更新失败';
            die(JSON($ret));
        }
        $service->approve($message);
        $ret['state'] = true;
        die(JSON($ret));
    }
    
    /***
     * 类目佣金被驳回审核
     */
    public function commis_rateOp(){
        $ret = array();
        $opinion = intval($_POST['opinion']);
        $message = trim($_POST['message']);
        $id = intval($_POST['id']);
        $workflow = $this->__checkWorkflow($id);
        if($workflow['state']==false){
            die(JSON($workflow));
        }
        $service = Service('Workflow');
        // 初始化数据
        $service->init($workflow,$this->admin_info['name'],$this->admin_info['gname']);
        //不同意直接cancel
        if($opinion==0){
            $message = $this->admin_info['name']."放弃此次类目佣金变更审批";
            $service->cancel($message);
            $ret['state']=true;
            die(JSON($ret));
        }
        
        $data = array();
        $condition = array();
        $commis_rate = intval($_POST['commis_rate']);
        if(($commis_rate < 0) || ($commis_rate>100)){
            $ret['state'] = false;
            $ret['msg'] = '类目佣金比例在0-100之间';
            die(JSON($ret));
        }
        
        $data['new_value'] = isset($workflow['new_value'])&&is_array($workflow['new_value'])?$workflow['new_value']:array();
        $data['new_value']['commis_rate'] = $commis_rate;
        $condition['id'] = $id;
        if(!Model('workflow')->editWorkflow($data,$condition)){
            $ret['state'] = false;
            $ret['msg'] = '数据库更新失败';
            die(JSON($ret));
        }
        
        $service->approve($message);
        $ret['state'] = true;
        die(JSON($ret));
    }
    
    
    private function __checkWorkflow($workflowId){
        $ret = array();
        $id = intval($workflowId);
        $workflow = Model('workflow')->getWorkflowInfo(array('id'=>$id));
        if(!$workflow['id']){
            $ret['state'] = false;
            $ret['msg'] = '非法参数';
            return $ret;
        }
        
        if($this->admin_info['gname'] != $workflow['stage']){
            $ret['state'] = false;
            $ret['msg'] = '对不起！您无权审核';
            return $ret;
        }
        $workflow['state'] = true;
        return $workflow;
    }
    

      
}