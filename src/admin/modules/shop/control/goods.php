<?php
/**
 * 商品栏目管理
 *
 *
 *
 *
 * @汉购网提供技术支持 授权请购买shopnc授权
 * @license    http://www.hangowa.com
 * @link       交流群号：
 */



defined('ByShopWWI') or exit('Access Invalid!');
class goodsControl extends SystemControl{
    private $links = array(
        array('url'=>'act=goods&op=goods','text'=>'所有商品'),
        array('url'=>'act=goods&op=lockup_list','text'=>'违规下架'),
        array('url'=>'act=goods&op=waitverify_list','text'=>'等待审核'),
        array('url'=>'act=goods&op=goods_set','text'=>'商品设置'),
    );
    const EXPORT_SIZE = 5000;
    public function __construct() {
        parent::__construct ();
        Language::read('goods');
    }

    public function indexOp() {
        $this->goodsOp();
    }
    /**
     * 商品管理
     */
    public function goodsOp() {
        //父类列表，只取到第二级
        $gc_list = Model('goods_class')->getGoodsClassList(array('gc_parent_id' => 0));
        Tpl::output('gc_list', $gc_list);

        Tpl::output('top_link',$this->sublink($this->links,'goods'));
						//网 店 运 维shop wwi.com
		Tpl::setDirquna('shop');
        Tpl::showpage('goods.index');
    }
    /**
     * 违规下架商品管理
     */
    public function lockup_listOp() {
        Tpl::output('type', 'lockup');
        Tpl::output('top_link',$this->sublink($this->links,'lockup_list'));
						//网 店 运 维shop wwi.com
		Tpl::setDirquna('shop');
        Tpl::showpage('goods.index');
    }
    /**
     * 等待审核商品管理
     */
    public function waitverify_listOp() {
        Tpl::output('type', 'waitverify');
        Tpl::output('top_link',$this->sublink($this->links,'waitverify_list'));
						//网 店 运 维shop wwi.com
		Tpl::setDirquna('shop');
        Tpl::showpage('goods.index');
    }
    
    public function cost_historyOp(){
        $goodsModel = Model('goods');
        $workflowModel = Model('workflow');
        $goods_commid= intval($_GET['goods_commonid']);
        $goodsList = $goodsModel->getGoodsList(array('goods_commonid'=>$goods_commid,'is_show_manage'=>1));
        $goodsList = array_under_reset($goodsList ,'goods_id');
        $goods_ids = array_keys($goodsList);
        $condition = array();
        $condition['type'] =array('in','0,54');
        $condition['status'] = 1;
        $condition['model_id'] = array('in' , $goods_ids);
        $field = 'id,model_id,new_value,old_value,user,created_at,updated_at,role';
        $workflow = $workflowModel->getWorkflowList($condition , '',$field,'id ASC');
        $workflow = array_under_reset($workflow, 'model_id');
        foreach($goods_ids as $goods_id){
            $goodsList[$goods_id]['workflow'][] = $workflow[$goods_id];
        }
        Tpl::output('goodsList', $goodsList);
        Tpl::setDirquna('shop');
        Tpl::showpage('goods.cost_history');
    }

    public function cost_editOp()
    {
        /** @var goodsModel $goodsModel */
        $goodsModel = Model('goods');
        /** @var goods_classModel $goodsClassModel */
        $goodsClassModel = Model('goods_class');
        //update by ljq 成本按照goods_id 单个修改
        if($_POST['isajax']){
            $ret = array();  //返回结果集
            //判断权限
            if($this->admin_info['gname'] !='运营部'){
                $ret['state'] = false;
                $ret['msg'] = '对不起！只有运营人员才能发起成本变更流程';
                die(json_encode($ret));
            }
            $goods_id = intval($_POST['goods_id']);
            $goods_cost =ncPriceFormat($_POST['goods_cost']) ;
            $goodsInfo = $goodsModel->getGoodsInfo(array('goods_id'=>$goods_id) ,'goods_cost,goods_price,goods_state');
            $goods_price = ncPriceFormat($goodsInfo['goods_price']);
            if($goods_cost == ncPriceFormat($goodsInfo['goods_cost'])){
                $ret['state'] = false;
                $ret['msg'] = '请确认是否修改商品成本价格';
                die(json_encode($ret));
            }
            
            if(($goods_price-$goods_cost < $goods_cost*0.05) && $_POST['sign_zc']==''){
                $ret['state'] = false;
                $ret['msg'] = '毛利小于5%请上传分管总裁签字凭证';
                die(json_encode($ret));
            }
            
            if(($goods_price < $goods_cost) && $_POST['sign_dsz']==''){
                $ret['state'] = false;
                $ret['msg'] = '负毛利请上传董事长签字凭证';
                die(json_encode($ret));
            }
            $new_value = array('goods_cost'=>$goods_cost ,'goods_state'=>$goodsInfo['goods_state']);
            $old_value = array('goods_cost'=>ncPriceFormat($goodsInfo['goods_cost']) ,'goods_state'=>'');
            $attachment = array();
            if($_POST['sign_zc']){
                $_POST['sign_vp']= $_POST['sign_zc'];
            }
            if($_POST['sign_dsz']){
                $_POST['sign_cp']= $_POST['sign_dsz'];
            }
            count($attachment>0) and JSON($attachment);
            $message = $this->admin_info['name']."提交了编号：".$goods_id."商品的成本变更流程";
            if($goods_price < $goods_cost){
                $message .="修改后的成本价是负毛利，需要审核总裁和董事长签字";
            }elseif (($goods_price-$goods_cost) < ($goods_cost*0.05)){
                $message .="修改后的成本价毛利小于5%，需要审核总裁签字";
            }
            $_POST['message'] = $message;
            /** @var WorkflowService $service */
            $service = Service('Workflow');
            $service->init(null,$this->admin_info['name'],$this->admin_info['gname']);
            try{
                $res=$service->launch(0,$goods_id,$new_value,$old_value);
            }catch (Exception $e){
                die(JSON(array('state'=>false,'msg'=>$e->getMessage())));
            }
            if($res){
                $ret['state'] = true;
                die(json_encode($ret));
            }
     
        }
        //上传凭证
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
        }
        $goodsList = $goodsModel->getGoodsList(array('goods_commonid'=>$_GET['goods_commonid'],'is_show_manage'=>1));
        if(chksubmit()){
            $newCosts = $_POST['goods_cost_new'];
            if(!is_array($newCosts)){
                showMessage('提交的数据非法！');
            }
            $error = 0;
            $success = 0;
            $riseRate=0;
            if(isset($goodsList[0]['gc_id'])) {
                $goodsClass = $goodsClassModel->getGoodsClassInfoById($goodsList[0]['gc_id']);
                $riseRate =$goodsClass['rise_rate'];
            }
            $goodsList = array_under_reset($goodsList,'goods_id');
            foreach ($newCosts as $goodsId => $newCost){
                if($newCost<=0) continue;
                $goodsInfo = $goodsList[$goodsId];
                if($goodsInfo['goods_cost_status']>0||$newCost == $goodsInfo['goods_cost']) {
                    $error++;
                    continue;
                }
                /**if($goodsInfo['goods_cost']==0||$newCost*100/$goodsInfo['goods_cost']>(100+$riseRate)){
                    $update['goods_cost_new'] = $newCost;
                    $update['goods_cost_status'] = 1;// 待审核
                }else{
                    $update['goods_cost'] = $newCost;
                    $update['edit_sap'] = 0;
                }*/
                // TODO 开启审核时注释下面2行，取消上面的注释
                $update['goods_cost'] = $newCost;
                $update['edit_sap'] = 0;
                $goodsModel->editGoodsById($update,$goodsId);
                $success++;
            }
            if($success>0){
                $error==0?showMessage('全部修改成功'):showMessage('部分修改成功');
            }else{
                showMessage('修改失败');
            }
        }
        Tpl::output('goodsList', $goodsList);
        Tpl::setDirquna('shop');
        Tpl::showpage('goods.cost_edit');
    }

    /**
     * 精斗云供应商ID维护
     */
    public function jdy_supplier_editOp()
    {
        /** @var goodsModel $goodsModel */
        $goodsModel = Model('goods');
        /** @var goods_classModel $goodsClassModel */
        $goodsClassModel = Model('goods_class');
        // 读取供应商信息
        /** @var jdyLogic $jdy */
        $goods_commonid = $_GET['goods_commonid'];
        $force = $_GET['force']&&!isset($_POST['jdy_supplier_id'])?true:false;
        $jdy = Logic('jdy');
        $supplierList = $jdy->getAllSuppliers($force);
        $goodsInfo = $goodsModel->getGoodsCommonInfo(array('goods_commonid'=>$goods_commonid));
        $supplierList = array_column($supplierList,'name','number');
        if(chksubmit()){
            // 修改供应商
            $jdy_supplier_id = $_POST['jdy_supplier_id'];
            if (!isset($supplierList[$jdy_supplier_id]))
                showMessage('您选择的供应商不存在或者已经被删除');
            $success = $goodsModel->editGoodsCommonById(array('jdy_supplier_id'=>$jdy_supplier_id),array($goods_commonid));
            if($success>0){
                showMessage('修改成功');
            }else{
                showMessage('修改失败');
            }
        }
        Tpl::output('goods', $goodsInfo);
        Tpl::output('supplierList', $supplierList);
        Tpl::setDirquna('shop');
        Tpl::showpage('goods.jdy_supplier_edit');
    }

    public function tax_editOp()
    {
    	//判断权限
    	if($this->admin_info['gname'] !='运营部'){
    		showMessage('对不起！只有运营部才能发起成本变更流程');
    		exit();
    	}
        /** @var goodsModel $goodsModel */
        $goodsModel = Model('goods');
        /** @var goods_classModel $goodsClassModel */
        $goodsClassModel = Model('goods_class');
        $goodsInfo = $goodsModel->getGoodsCommonInfoByID($_GET['goods_commonid']);

        if(chksubmit()){
            
            $ret = array();  //返回结果集
            $goods_commonid = intval($_GET['goods_commonid']);
        	$tax_input = sprintf("%.3f",$_POST['tax_input']);
            $tax_output = sprintf("%.3f",$_POST['tax_output']);
            if($tax_input>=100||$tax_input<0||$tax_output>=100||$tax_output<0){
                showMessage('进项税和销项税都必须大于0且小于100');
            }
            if($tax_input == $goodsInfo['tax_input'] && $tax_output == $goodsInfo['tax_output']){
            	showMessage('该商品进项税，销项税都没有变更');
            }
            $type = 52;//流程type
            
            $whereWorkflow = array();
            $whereWorkflow['type'] = $type;
            $whereWorkflow['model']='goods_common';
            $whereWorkflow['model_id'] = $goods_commonid;
            $whereWorkflow['status'] = array('neq' ,'1');
            $workflowInfo = Model('workflow')->getWorkflowInfo($whereWorkflow , 'id');
            
            if($workflowInfo['id']>0){
            	showMessage('此商品已提交了修改该商品税率审核,不能重复提交');
            }

            $new_value = array('tax_input'=>$tax_input,'tax_output'=>$tax_output);
            $old_value = array('tax_input'=>$goodsInfo['tax_input'],'tax_output'=>$goodsInfo['tax_output']);
            $data = array();          //审核表数组
            $data['title'] = '编号：'.$goods_commonid."商品（{$goodsInfo['goods_name']}）税率变更审核流程";
            $data['type'] = $type;   //类目税率变更
            $data['model'] = 'goods_common';
            $data['model_id'] = $goods_commonid;
            $data['stage'] = $this->admin_info['gname'];
            $data['new_value'] = JSON($new_value);
            $data['old_value'] = JSON($old_value);
            $data['reference'] = "";
            $data['role'] = '0';
            $data['user'] = $this->admin_info['name'];
            if(!$workflowId = Model('workflow')->addWorkflow($data)){
            	showMessage('插入审核数据库失败');
            }
            
            $workflow = Model('workflow')->getWorkflowInfo(array('id'=>$workflowId));
            /** @var WorkflowService $service */
            $service = Service('Workflow');
            // 初始化数据
            $service->init($workflow,$this->admin_info['name'],$this->admin_info['gname']);
            if(!$service->approve('提交审核', $attachment)){
            	showMessage('审核提交失败');
            }
            showMessage('审核提交成功');
            
            /*$update_array = array();
            $update_array['tax_input'] =$tax_input;
            $update_array['tax_output'] =$tax_output;
            $goodsModel->beginTransaction();
            $result = $goodsModel->editGoodsCommonById($update_array,$goodsInfo['goods_commonid']);
            if($result){
                $result = $goodsModel->table('goods')->where(array('goods_commonid'=>$goodsInfo['goods_commonid']))->update($update_array);
                if($result){
                    $goodsModel->commit();
                    showMessage('全部修改成功');
                }else{
                    $goodsModel->rollback();
                    showMessage('修改失败');
                }
            }else{
                $goodsModel->rollback();
                showMessage('修改失败');
            }*/
        }
        Tpl::output('goodsInfo', $goodsInfo);
        Tpl::setDirquna('shop');
        Tpl::showpage('goods.tax_edit');
    }
    public function cost_status_resetOp()
    {
        /** @var goodsModel $goodsModel */
        $goodsModel = Model('goods');
        $update = array(
            'goods_cost_new'=>0,
            'goods_cost_status'=>0,
        );
        $goodsModel->editGoodsById($update,$_GET['goods_id']);
        showMessage('撤销成功');
    }
    /**
     * 输出XML数据
     */
    public function get_xmlOp() {
        /** @var goodsModel $model_goods */
        $model_goods = Model('goods');
        $condition = array();
        if ($_GET['goods_name'] != '') {
            $condition['goods_name'] = array('like', '%' . $_GET['goods_name'] . '%');
        }
        if ($_GET['goods_commonid'] != '') {
            $condition['goods_commonid'] = array('like', '%' . $_GET['goods_commonid'] . '%');
        }
        if ($_GET['store_name'] != '') {
            $condition['store_name'] = array('like', '%' . $_GET['store_name'] . '%');
        }
        if ($_GET['brand_name'] != '') {
            $condition['brand_name'] = array('like', '%' . $_GET['brand_name'] . '%');
        }
        if (intval($_GET['cate_id']) > 0) {
            $condition['gc_id'] = intval($_GET['cate_id']);
        }
        if ($_GET['goods_state'] != '') {
            $condition['goods_state'] = $_GET['goods_state'];
        }
        if ($_GET['goods_verify'] != '') {
            $condition['goods_verify'] = $_GET['goods_verify'];
        }
        if ($_POST['query'] != '') {
            $condition[$_POST['qtype']] = array('like', '%' . $_POST['query'] . '%');
        }
        $order = '';
        $param = array('goods_commonid', 'goods_name', 'goods_price', 'goods_state', 'goods_verify', 'goods_image', 'goods_jingle', 'gc_id'
                , 'gc_name', 'store_id', 'store_name', 'is_own_shop', 'brand_id', 'brand_name', 'goods_addtime', 'goods_marketprice', 'goods_costprice'
                , 'goods_freight', 'is_virtual', 'virtual_indate', 'virtual_invalid_refund', 'is_fcode'
                , 'is_presell', 'presell_deliverdate'
        );
        if (in_array($_POST['sortname'], $param) && in_array($_POST['sortorder'], array('asc', 'desc'))) {
            $order = $_POST['sortname'] . ' ' . $_POST['sortorder'];
        }
        $page = $_POST['rp'];

        switch ($_GET['type']) {
            // 禁售
            case 'lockup':
                $goods_list = $model_goods->getGoodsCommonLockUpList($condition, '*', $page, $order);
                break;
                // 等待审核
            case 'waitverify':
                $goods_list = $model_goods->getGoodsCommonWaitVerifyList($condition, '*', $page, $order);
                break;
                // 全部商品
            default:
                $goods_list = $model_goods->getGoodsCommonList($condition, '*', $page, $order);
                break;
        }

        // 库存
        $storage_array = $model_goods->calculateStorage($goods_list);

        // 商品状态
        $goods_state = $this->getGoodsState();

        // 审核状态
        $verify_state = $this->getGoodsVerify();

        $data = array();
        $data['now_page'] = $model_goods->shownowpage();
        $data['total_num'] = $model_goods->gettotalnum();
        foreach ($goods_list as $value) {
//            $is_show_button = $value['is_show']?"隐藏":"显示";
//            $is_show_state = $value['is_show']?0:1;
            $param = array();
            $operation = '';
            switch ($_GET['type']) {
                // 禁售
                case 'lockup':
                    $operation .= "<a class='btn red' href='javascript:void(0);' onclick=\"fg_del('" . $value['goods_commonid'] . "')\"><i class='fa fa-trash-o'></i>删除</a>";
                    break;
                    // 等待审核
                case 'waitverify':
                    $operation .= "<a class='btn orange' href='javascript:void(0);' onclick=\"fg_verify('" . $value['goods_commonid'] . "')\"><i class='fa fa-check-square'></i>审核</a>";
                    break;
                    // 全部商品
                default:
                    $operation .= "<a class='btn red' href='javascript:void(0);' onclick=\"fg_lonkup('" . $value['goods_commonid'] . "')\"><i class='fa fa-ban'></i>下架</a>";
                    break;
            }
            $operation .= "<span class='btn'><em><i class='fa fa-cog'></i>设置 <i class='arrow'></i></em><ul>";
//            $operation .= "<li><a href='javascript:void(0);' onclick=\"fg_show(" . $value['goods_commonid'] . ",{$is_show_state})\">{$is_show_button}</a></li>";
            $operation .= "<li><a href='" . urlShop('goods', 'index', array('goods_id' => $storage_array[$value['goods_commonid']]['goods_id'])) . "' target=\"_blank\">查看商品详细</a></li>";
            $operation .= "<li><a href='index.php?act=goods&op=transport&goods_commonid=" . $value['goods_commonid'] . "' target=\"_blank\">查看运费模板</a></li>";
            $operation .= "<li><a href='javascript:void(0)' onclick=\"fg_sku('" . $value['goods_commonid'] . "')\">查看商品SKU</a></li>";
            if($value['goods_verify']!=10) {
                $operation .= "<li><a href='index.php?act=goods&op=cost_edit&goods_commonid=" . $value['goods_commonid'] . "'>共建成本价</a></li>";
            }
            $operation .= "<li><a href='index.php?act=goods&op=cost_history&goods_commonid=".$value['goods_commonid']."'>成本价修改日志</a></li>";
            $operation .= "<li><a href='index.php?act=goods&op=jdy_supplier_edit&goods_commonid=" . $value['goods_commonid'] . "'>精斗云供应商维护</a></li>";
            $operation .= "<li><a href='index.php?act=goods&op=edit_shequ_return_money&goods_commonid=" . $value['goods_commonid'] . "'>修改社区团购佣金</a></li>";
            $operation .= "<li><a href='index.php?act=goods&op=tax_edit&goods_commonid=".$value['goods_commonid']."'>修改税率</a></li>";
            $operation .= "<li><a href=\"javascript:;\" onclick=\"ajax_form('edit_goods_cache', '更新缓存', 'index.php?act=goods&amp;op=flush_goods_cache&amp;goods_id={$storage_array[$value["goods_commonid"]]["goods_id"]}', 640,0);\">更新缓存</a></li>";
            $operation .= "</ul>";
            $param['operation'] = $operation;
//            $param['is_show'] = $value['is_show']?'是':'否';
            $param['goods_commonid'] = $value['goods_commonid'];
            $param['goods_name'] = $value['goods_name'];
            $param['goods_price'] = ncPriceFormat($value['goods_price']);
            $param['goods_state'] = $goods_state[$value['goods_state']];
            $param['goods_verify'] = $verify_state[$value['goods_verify']];
            $param['goods_image'] = "<a href='javascript:void(0);' class='pic-thumb-tip' onMouseOut='toolTip()' onMouseOver='toolTip(\"<img src=".thumb($value,'60').">\")'><i class='fa fa-picture-o'></i></a>";
            $param['goods_jingle'] = $value['goods_jingle'];
            $param['gc_id'] = $value['gc_id'];
            $param['gc_name'] = $value['gc_name'];
            $param['store_id'] = $value['store_id'];
            $param['store_name'] = $value['store_name'];
            $param['is_own_shop'] = $value['is_own_shop'] == 1 ? '平台自营' : '入驻商户';
            $param['brand_id'] = $value['brand_id'];
            $param['brand_name'] = $value['brand_name'];
            $param['goods_addtime'] = date('Y-m-d', $value['goods_addtime']);
            $param['goods_marketprice'] = ncPriceFormat($value['goods_marketprice']);
            $param['goods_costprice'] = ncPriceFormat($value['goods_costprice']);
            $param['goods_freight'] = $value['goods_freight'] == 0 ? '免运费' : ncPriceFormat($value['goods_freight']);
            $param['goods_storage'] = $storage_array[$value['goods_commonid']]['sum'];
            $param['is_virtual'] = $value['is_virtual'] ==  '1' ? '<span class="yes"><i class="fa fa-check-circle"></i>是</span>' : '<span class="no"><i class="fa fa-ban"></i>否</span>';
            $param['virtual_indate'] = $value['is_virtual'] == '1' && $value['virtual_indate'] > 0 ? date('Y-m-d', $value['virtual_indate']) : '--';
            $param['virtual_invalid_refund'] = $value['is_virtual'] ==  '1' ? ($value['virtual_invalid_refund'] == 1 ? '<span class="yes"><i class="fa fa-check-circle"></i>是</span>' : '<span class="no"><i class="fa fa-ban"></i>否</span>') : '--';
            $data['list'][$value['goods_commonid']] = $param;
        }
        echo Tpl::flexigridXML($data);exit();
    }

    /**
     * 商品状态
     * @return multitype:string
     */
    private function getGoodsState() {
        return array('1' => '出售中', '0' => '仓库中', '10' => '违规下架');
    }

    private function getGoodsVerify() {
        return array('1' => '通过', '0' => '未通过', '10' => '等待审核');
    }

    /**
     * 违规下架
     */
    public function goods_lockupOp() {
        if (chksubmit()) {
            $commonid = intval($_POST['commonid']);
            if ($commonid <= 0) {
                    showDialog(L('nc_common_op_fail'), 'reload');
            }
            $update = array();
            $update['goods_stateremark'] = trim($_POST['close_reason']);

            $where = array();
            $where['goods_commonid'] = $commonid;

            Model('goods')->editProducesLockUp($update, $where);
            showDialog(L('nc_common_op_succ'), '', 'succ', '$("#flexigrid").flexReload();CUR_DIALOG.close()');
        }
        $common_info = Model('goods')->getGoodsCommonInfoByID($_GET['id']);
        Tpl::output('common_info', $common_info);
						//网 店 运 维shop wwi.com
		Tpl::setDirquna('shop');
        Tpl::showpage('goods.close_remark', 'null_layout');
    }

    /**
     * 删除商品
     */
    public function goods_delOp() {
        $common_id = intval($_GET['id']);
        if ($common_id <= 0) {
            exit(json_encode(array('state'=>false,'msg'=>'删除失败')));
        }
        Model('goods')->delGoodsAll(array('goods_commonid' => $common_id));
        $this->log('删除商品[ID:'.$common_id.']',1);
        exit(json_encode(array('state'=>true,'msg'=>'删除成功')));
    }

    /**
     * 审核商品
     */
    public function goods_verifyOp(){
        if (chksubmit()) {
            $commonid = intval($_POST['commonid']);
            if ($commonid <= 0) {
                    showDialog(L('nc_common_op_fail'), 'reload');
            }
            $update2 = array();
            $update2['goods_verify'] = intval($_POST['verify_state']);

            $update1 = array();
            $update1['goods_verifyremark'] = trim($_POST['verify_reason']);
            $update1 = array_merge($update1, $update2);
            $where = array();
            $where['goods_commonid'] = $commonid;

            $model_goods = Model('goods');
            if (intval($_POST['verify_state']) == 0) {
                $model_goods->editProducesVerifyFail($where, $update1, $update2);
            } else {
                $model_goods->editProduces($where, $update1, $update2);
            }
            showDialog(L('nc_common_op_succ'), '', 'succ', '$("#flexigrid").flexReload();CUR_DIALOG.close();');
        }
        $common_info = Model('goods')->getGoodsCommonInfoByID($_GET['id']);
        Tpl::output('common_info', $common_info);
						//网 店 运 维shop wwi.com
		Tpl::setDirquna('shop');
        Tpl::showpage('goods.verify_remark', 'null_layout');
    }

    /**
     * ajax获取商品列表
     */
    public function get_goods_sku_listOp() {
        $commonid = $_GET['commonid'];
        if ($commonid <= 0) {
            showDialog('参数错误', '', '', 'CUR_DIALOG.close();');
        }
        $model_goods = Model('goods');
        $goodscommon_list = $model_goods->getGoodsCommonInfoByID($commonid, 'spec_name');
        if (empty($goodscommon_list)) {
            showDialog('参数错误', '', '', 'CUR_DIALOG.close();');
        }
        $spec_name = array_values((array)unserialize($goodscommon_list['spec_name']));
        $goods_list = $model_goods->getGoodsList(array('goods_commonid' => $commonid,'is_show_manage'=>1), 'goods_id,goods_spec,store_id,goods_price,goods_serial,goods_storage,goods_image');
        if (empty($goods_list)) {
            showDialog('参数错误', '', '', 'CUR_DIALOG.close();');
        }

        foreach ($goods_list as $key => $val) {
            $goods_spec = array_values((array)unserialize($val['goods_spec']));
            $spec_array = array();
            foreach ($goods_spec as $k => $v) {
                $spec_array[] = '<div class="goods_spec">' . $spec_name[$k] . L('nc_colon') . '<em title="' . $v . '">' . $v .'</em>' . '</div>';
            }
            $goods_list[$key]['goods_image'] = thumb($val, '60');
            $goods_list[$key]['goods_spec'] = implode('', $spec_array);
            $goods_list[$key]['url'] = urlShop('goods', 'index', array('goods_id' => $val['goods_id']));
        }

//         /**
//          * 转码
//          */
//         if (strtoupper(CHARSET) == 'GBK') {
//             Language::getUTF8($goods_list);
//         }
//         echo json_encode($goods_list);
        Tpl::output('goods_list', $goods_list);
						//网 店 运 维shop wwi.com
		Tpl::setDirquna('shop');
        Tpl::showpage('goods.sku_list', 'null_layout');
    }
    public function viewOp() {
        /** @var goodsModel $model_goods */
        $model_goods = Model('goods');
        if(empty($_GET['goods_commonid'])&&!empty($_GET['goods_id'])){
            $goodsInfo = $model_goods->getGoodsInfo(array('goods_id'=>$_GET['goods_id']));
            if(!empty($goodsInfo)) $_GET['goods_commonid'] = $goodsInfo['goods_commonid'];
        }
        $commonid = $_GET['goods_commonid'];
        if ($commonid <= 0) {
            showDialog('商品ID参数错误', '', '', 'CUR_DIALOG.close();');
        }
        $goodscommon_list = $model_goods->getGoodsCommonInfoByID($commonid);
        Tpl::output('commonInfo', $goodscommon_list);

        if (empty($goodscommon_list)) {
            showDialog('商品信息为空', '', '', 'CUR_DIALOG.close();');
        }
        $spec_name = array_values((array)unserialize($goodscommon_list['spec_name']));
        $goods_list = $model_goods->getGoodsList(array('goods_commonid' => $commonid,'is_del'=>-1), '*');
        if (empty($goods_list)) {
            showDialog('SKU为空', '', '', 'CUR_DIALOG.close();');
        }

        foreach ($goods_list as $key => $val) {
            $goods_spec = array_values((array)unserialize($val['goods_spec']));
            $spec_array = array();
            foreach ($goods_spec as $k => $v) {
                $spec_array[] = '<div class="goods_spec">' . $spec_name[$k] . L('nc_colon') . '<em title="' . $v . '">' . $v .'</em>' . '</div>';
            }
            $goods_list[$key]['goods_image'] = thumb($val, '60'   );
            $goods_list[$key]['goods_spec'] = implode('', $spec_array);
            $goods_list[$key]['url'] = urlShop('goods', 'index', array('goods_id' => $val['goods_id']));
        }

        Tpl::output('goods_list', $goods_list);


        /** @var storeModel $model_store */
        $model_store = Model('store');
        $store_info = $model_store->getStoreInfo(array('store_id'=>$goodscommon_list['store_id']));
        $store_certifications = $model_store->table('store_certification')
            ->where(array('store_id'=>$goodscommon_list['store_id']))->select();
        Tpl::output('store_info', $store_info);
        Tpl::output('store_certifications', $store_certifications);
		Tpl::setDirquna('shop');
        Tpl::showpage('goods.view');
    }
    public function transportOp() {
        /** @var goodsModel $model_goods */
        $model_goods = Model('goods');
        if(empty($_GET['goods_commonid'])&&!empty($_GET['goods_id'])){
            $goodsInfo = $model_goods->getGoodsInfo(array('goods_id'=>$_GET['goods_id']));
            if(!empty($goodsInfo)) $_GET['goods_commonid'] = $goodsInfo['goods_commonid'];
        }
        $commonid = $_GET['goods_commonid'];
        if ($commonid <= 0) {
            showDialog('商品ID参数错误', '', '', 'CUR_DIALOG.close();');
        }
        $goodscommon_list = $model_goods->getGoodsCommonInfoByID($commonid);
        Tpl::output('commonInfo', $goodscommon_list);

        if (empty($goodscommon_list)) {
            showDialog('商品信息为空', '', '', 'CUR_DIALOG.close();');
        }
        if($goodscommon_list['transport_id']>0){
            /** @var transportModel $modelTransport */
            $modelTransport = Model('transport');
            $transportInfo = $modelTransport->getExtendInfo(array('transport_id'=>$goodscommon_list['transport_id']));
            Tpl::output('transportInfo', $transportInfo);
        }else{
            Tpl::output('transportInfo', null);
        }
        /** @var storeModel $model_store */
        $model_store = Model('store');
        $store_info = $model_store->getStoreInfo(array('store_id'=>$goodscommon_list['store_id']));
        $store_certifications = $model_store->table('store_certification')
            ->where(array('store_id'=>$goodscommon_list['store_id']))->select();
        Tpl::output('store_info', $store_info);
        Tpl::output('store_certifications', $store_certifications);
		Tpl::setDirquna('shop');
        Tpl::showpage('goods.transport');
    }

    /**
     * 商品设置
     */
    public function goods_setOp() {
        $model_setting = Model('setting');
        if (chksubmit()){
            $update_array = array();
            $update_array['goods_verify'] = $_POST['goods_verify'];
            $result = $model_setting->updateSetting($update_array);
            exit();
        }
        $list_setting = $model_setting->getListSetting();
        Tpl::output('list_setting',$list_setting);

        Tpl::output('top_link',$this->sublink($this->links,'goods_set'));
						//网 店 运 维shop wwi.com
		Tpl::setDirquna('shop');
        Tpl::showpage('goods.setting');
    }

    /**
     * csv导出
     */
    public function export_csvOp() {
        $model_goods = Model('goods');
        $condition = array();
        $limit = false;
        if ($_GET['id'] != '') {
            $id_array = explode(',', $_GET['id']);
            $condition['goods_commonid'] = array('in', $id_array);
        }
        if ($_GET['goods_name'] != '') {
            $condition['goods_name'] = array('like', '%' . $_GET['goods_name'] . '%');
        }
        if ($_GET['goods_commonid'] != '') {
            $condition['goods_commonid'] = array('like', '%' . $_GET['goods_commonid'] . '%');
        }
        if ($_GET['store_name'] != '') {
            $condition['store_name'] = array('like', '%' . $_GET['store_name'] . '%');
        }
        if ($_GET['brand_name'] != '') {
            $condition['brand_name'] = array('like', '%' . $_GET['brand_name'] . '%');
        }
        if ($_GET['cate_id'] != '') {
            $condition['gc_id'] = $_GET['cate_id'];
        }
        if ($_GET['goods_state'] != '') {
            $condition['goods_state'] = $_GET['goods_state'];
        }
        if ($_GET['goods_verify'] != '') {
            $condition['goods_verify'] = $_GET['goods_verify'];
        }
        if ($_REQUEST['query'] != '') {
            $condition[$_REQUEST['qtype']] = array('like', '%' . $_REQUEST['query'] . '%');
        }
        $order = '';
        $param = array('goods_commonid', 'goods_name', 'goods_price', 'goods_state', 'goods_verify', 'goods_image', 'goods_jingle', 'gc_id'
                , 'gc_name', 'store_id', 'store_name', 'is_own_shop', 'brand_id', 'brand_name', 'goods_addtime', 'goods_marketprice', 'goods_costprice'
                , 'goods_freight', 'is_virtual', 'virtual_indate', 'virtual_invalid_refund', 'is_fcode'
                , 'is_presell', 'presell_deliverdate'
        );
        if (in_array($_REQUEST['sortname'], $param) && in_array($_REQUEST['sortorder'], array('asc', 'desc'))) {
            $order = $_REQUEST['sortname'] . ' ' . $_REQUEST['sortorder'];
        }
        if (!is_numeric($_GET['curpage'])){
            switch ($_GET['type']) {
                // 禁售
                case 'lockup':
                    $count = $model_goods->getGoodsCommonLockUpCount($condition);
                    break;
                    // 等待审核
                case 'waitverify':
                    $count = $model_goods->getGoodsCommonWaitVerifyCount($condition);
                    break;
                    // 全部商品
                default:
                    $count = $model_goods->getGoodsCommonCount($condition);
                    break;
            }
            if ($count > self::EXPORT_SIZE ){   //显示下载链接
                $array = array();
                $page = ceil($count/self::EXPORT_SIZE);
                for ($i=1;$i<=$page;$i++){
                    $limit1 = ($i-1)*self::EXPORT_SIZE + 1;
                    $limit2 = $i*self::EXPORT_SIZE > $count ? $count : $i*self::EXPORT_SIZE;
                    $array[$i] = $limit1.' ~ '.$limit2 ;
                }
                Tpl::output('list',$array);
                Tpl::output('murl','index.php?act=goods&op=index');
								//网 店 运 维shop wwi.com
		Tpl::setDirquna('shop');
                Tpl::showpage('export.excel');
                exit();
            }
        } else {
            $limit1 = ($_GET['curpage']-1) * self::EXPORT_SIZE;
            $limit2 = self::EXPORT_SIZE;
            $limit = $limit1 .','. $limit2;
        }
        switch ($_GET['type']) {
            // 禁售
            case 'lockup':
                $goods_list = $model_goods->getGoodsCommonLockUpList($condition, '*', null, $order, $limit);
                break;
                // 等待审核
            case 'waitverify':
                $goods_list = $model_goods->getGoodsCommonWaitVerifyList($condition, '*', null, $order, $limit);
                break;
                //全部商品
            default:
                $goods_list = $model_goods->getGoodsCommonList($condition, '*', null, $order, $limit);
                break;
        }
        $this->createCsv($goods_list);
    }

    /**
     * 生成csv文件
     */
    private function createCsv($goods_list) {
        // 库存
        $storage_array = Model('goods')->calculateStorage($goods_list);

        // 商品状态
        $goods_state = $this->getGoodsState();

        // 审核状态
        $verify_state = $this->getGoodsVerify();

        $store_id_list = array_column($goods_list,'store_id');
        $condition = array();
        $condition['store_id'] = array('in',$store_id_list);
        $store_list = Model('store')->getStoreList($condition, $page = null, $order = '', $field = 'store_id,manage_type');
        $store_list_out = array();
        foreach($store_list as $v){
            $store_list_out[$v['store_id']] = $v['manage_type'];
        }

        $data = array();
        foreach ($goods_list as $value) {
            $param = array();
            $param['goods_commonid'] = $value['goods_commonid'];
            $param['goods_name'] = $value['goods_name'];
            $param['goods_price'] = ncPriceFormat($value['goods_price']);
            $param['tax_input'] = ncPriceFormat($value['tax_input']);
            $param['tax_output'] = ncPriceFormat($value['tax_output']);
            $param['goods_state'] = $goods_state[$value['goods_state']];
            $param['goods_verify'] = $verify_state[$value['goods_verify']];
            $param['goods_image'] = thumb($value,'60');
            $param['goods_jingle'] = str_replace("\r\n","",htmlspecialchars($value['goods_jingle'])); ;
            $param['gc_id'] = $value['gc_id'];
            $param['gc_name'] = $value['gc_name'];
            $param['store_id'] = $value['store_id'];
            $param['store_name'] = $value['store_name'];
            $param['is_own_shop'] = $value['is_own_shop'] == 1 ? '平台自营' : '入驻商户';
            $param['manage_type'] = $store_list_out[$value['store_id']] == 'co_construct' ? '共建' : '平台';
            $param['brand_id'] = $value['brand_id'];
            $param['brand_name'] = $value['brand_name'];
            $param['goods_addtime'] = date('Y-m-d', $value['goods_addtime']);
            $param['goods_marketprice'] = ncPriceFormat($value['goods_marketprice']);
            //$param['goods_costprice'] = ncPriceFormat($value['goods_costprice']);
            $param['goods_costprice'] = $storage_array[$value['goods_commonid']]['goods_cost'];
            $param['goods_freight'] = $value['goods_freight'] == 0 ? '免运费' : ncPriceFormat($value['goods_freight']);
            $param['goods_storage'] = $storage_array[$value['goods_commonid']]['sum'];
            $param['is_virtual'] = $value['is_virtual'] ==  '1' ? '是' : '否';
            $param['virtual_indate'] = $value['is_virtual'] == '1' && $value['virtual_indate'] > 0 ? date('Y-m-d', $value['virtual_indate']) : '--';
            $param['virtual_invalid_refund'] = $value['is_virtual'] ==  '1' ? ($value['virtual_invalid_refund'] == 1 ? '是' : '否') : '--';
            $param['goods_url'] = urlShop('goods','index',array('goods_id'=>$storage_array[$value['goods_commonid']]['goods_id']));
            $data[$value['goods_commonid']] = $param;
        }

        $header = array(
                'goods_commonid' => 'SPU',
                'goods_name' => '商品名称',
                'goods_price' => '商品价格(元)',
                'tax_input' => '进项税率',
                'tax_output' => '销项税率',
                'goods_state' => '商品状态',
                'goods_verify' => '审核状态',
                'goods_image' => '商品图片',
                'goods_jingle' => '广告词',
                'gc_id' => '分类ID',
                'gc_name'=>'分类名称',
                'store_id' => '店铺ID',
                'store_name' => '店铺名称',
                'is_own_shop' => '店铺类型',
                'manage_type' => '经营类型',
                'brand_id' => '品牌ID',
                'brand_name' => '品牌名称',
                'goods_addtime' => '发布时间',
                'goods_marketprice' => '市场价格(元)',
                'goods_costprice' => '成本价格(元)',
                'goods_freight' => '运费(元)',
                'goods_storage' => '库存',
                'is_virtual' => '虚拟商品',
                'virtual_indate' => '有效期',
                'virtual_invalid_refund' => '允许退款',
                'goods_url' => '商品链接'
        );
       array_unshift($data, $header);
		$csv = new Csv();
	    $export_data = $csv->charset($data,CHARSET,'GBK');
	    $csv->filename = $csv->charset('goods_list',CHARSET).$_GET['curpage'] . '-'.date('Y-m-d');
	    $csv->export($export_data);
    }
    
    /**
     * 更新商品缓存
     */
    function flush_goods_cacheOp()
    {
    	$goods_id = intval($_GET['goods_id']);
    	dcache($goods_id, 'goods');
    	
    	Tpl::setDirquna('shop');
    	Tpl::showpage('goods.flush.cache','null_layout');
    }

    /**
     * 商品切换显示状态
     */
    function goods_showOp(){
        if (preg_match('/^[\d,]+$/', $_GET['goods_id'])) {
            $state = $_GET['state'];
            $goods_id = explode(',',trim($_GET['goods_id'],','));
            $model_goods = Model('goods');
            $result = $model_goods->showGoods(array('goods_id'=>array('in',$goods_id)),$state);
            if ($result) {
                exit(json_encode(array('state'=>true,'msg'=>'操作成功')));
            } else {
                exit(json_encode(array('state'=>false,'msg'=>'操作失败')));
            }
        }
    }


    /**
     * 编辑分销金额
     */
    public function edit_retail_moneyOp() {
        /** @var goodsModel $goodsModel */
        $goodsModel = Model('goods');
        /** @var retail_goodsModel $retail_goodsModel */
        $retail_goodsModel = Model('retail_goods');
        $goodsList = $goodsModel->getGoodsList(array('goods_commonid'=>$_REQUEST['goods_commonid'],'is_show_manage'=>1));
        if (empty($goodsList)) {
            showMessage('该商品不显示');
        }
        $goods_ids = array_column($goodsList, 'goods_id');
        $retail_goods_list = $retail_goodsModel->getRetailGoodsList(array('retail_goods_id' => array('in', $goods_ids)));
        $retail_goods_list = array_under_reset($retail_goods_list, 'retail_goods_id');
        if(chksubmit()){
            $goods_id = intval($_POST['goods_id']);
            $retail_one_return = $_POST['retail_one_return'];
            $retail_two_return = $_POST['retail_two_return'];
            $retail_three_return = $_POST['retail_three_return'];
            $retail_show_time = $_POST['retail_show_time'] ? strtotime($_POST['retail_show_time']) : 0;
            $param = array(
                'retail_one_return' => $retail_one_return,
                'retail_two_return' => $retail_two_return,
                'retail_three_return' => $retail_three_return,
                'retail_show_time' => $retail_show_time,
            );
            if ($retail_one_return <= 0 && ($retail_two_return > 0 || $retail_three_return > 0)) {
                $ret['state'] = false;
                $ret['msg'] = '佣金设置不对';
                die(json_encode($ret));
            }

            if ($retail_two_return < 0 || $retail_three_return < 0 || $retail_one_return < 0) {
                $ret['state'] = false;
                $ret['msg'] = '佣金设置不对';
                die(json_encode($ret));
            }

            if ($retail_three_return > $retail_two_return || $retail_two_return > $retail_one_return) {
                $ret['state'] = false;
                $ret['msg'] = '佣金设置不对';
                die(json_encode($ret));
            }

            $exit_retail = $retail_goodsModel->getRetailGoodsInfo(array('retail_goods_id' => $goods_id));
            if (empty($exit_retail)) {
                if ($retail_one_return <= 0) {
                    $ret['state'] = true;
                    $ret['msg'] = '成功';
                    die(json_encode($ret));
                }
                $insert = $param;
                $insert['retail_goods_id'] = $goods_id;
                $retail_goodsModel->addRetailGoods($insert);
                $ret['state'] = true;
                $ret['msg'] = '成功';
                die(json_encode($ret));
            }
            if ($retail_one_return <= 0) {
                $retail_goodsModel->delRetailGoods(array('retail_goods_id' => $goods_id));
            } else {
                $retail_goodsModel->editRetailGoods($param, array('retail_goods_id' => $goods_id));
            }
            $ret['state'] = true;
            $ret['msg'] = '成功';
            die(json_encode($ret));
        }
        Tpl::output('goodsList', $goodsList);
        Tpl::output('retail_goods_list', $retail_goods_list);
        Tpl::setDirquna('shop');
        Tpl::showpage('goods.retail_money_edit');
    }

    /**
     * 编辑社区团购分销金额
     */
    public function edit_shequ_return_moneyOp() {
        /** @var goodsModel $goodsModel */
        $goodsModel = Model('goods');
        /** @var shequ_return_goodsModel $shequ_return_goodsModel */
        $shequ_return_goodsModel = Model('shequ_return_goods');
        $goodsList = $goodsModel->getGoodsList(array('goods_commonid'=>$_REQUEST['goods_commonid'],'is_show_manage'=>1));
        if (empty($goodsList)) {
            showMessage('该商品不显示');
        }
        $goods_ids = array_column($goodsList, 'goods_id');
        $shequ_return_goods_list = $shequ_return_goodsModel->getReturnGoodsList(array('return_goods_id' => array('in', $goods_ids)));
        $shequ_return_goods_list = array_under_reset($shequ_return_goods_list, 'return_goods_id');
        if(chksubmit()){
            $goods_id = intval($_POST['goods_id']);
            $return_money_rate = $_POST['return_money_rate'];
            $param = array(
                'return_money_rate' => $return_money_rate,
            );

            if ($return_money_rate < 0 || $return_money_rate > 1) {
                $ret['state'] = false;
                $ret['msg'] = '佣金比例设置不对';
                die(json_encode($ret));
            }
            $exit_return = $shequ_return_goodsModel->getReturnGoodsInfo(array('return_goods_id' => $goods_id));
            if (empty($exit_return)) {
                if ($return_money_rate <= 0) {
                    $ret['state'] = true;
                    $ret['msg'] = '成功';
                    die(json_encode($ret));
                }
                $insert = $param;
                $insert['return_goods_id'] = $goods_id;
                $shequ_return_goodsModel->addReturnGoods($insert);
                $ret['state'] = true;
                $ret['msg'] = '成功';
                die(json_encode($ret));
            }
            if ($return_money_rate <= 0) {
                $shequ_return_goodsModel->delReturnGoods(array('return_goods_id' => $goods_id));
            } else {
                $shequ_return_goodsModel->editReturnGoods($param, array('return_goods_id' => $goods_id));
            }
            $ret['state'] = true;
            $ret['msg'] = '成功';
            die(json_encode($ret));
        }
        Tpl::output('goodsList', $goodsList);
        Tpl::output('shequ_return_goods_list', $shequ_return_goods_list);
        Tpl::setDirquna('shop');
        Tpl::showpage('goods.shequ_return_money_edit');
    }
}
