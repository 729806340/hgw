<?php
/**
 * 店铺信息
 *
 *
 *
 * * @汉购网 (c) 2015-2018 ShopWWI Inc. (http://www.hangowa.com)
 * @license    http://www.shopwwi.c om
 * @link       交流群号：
 * @since      汉购网提供技术支持 授权请购买shopnc授权
 */



defined('ByShopWWI') or exit('Access Invalid!');

class store_infoControl extends BaseSellerControl {
    public function __construct() {
        parent::__construct();
        Language::read('member_store_index');
    }

    /**
     * 店铺信息
     */
    public function indexOp(){
        $model_store = Model('store');
        $model_store_bind_class = Model('store_bind_class');
        $model_store_class = Model('store_class');
        $model_store_grade = Model('store_grade');

        // 店铺信息
        $store_info = $model_store->getStoreInfoByID($_SESSION['store_id']);
        Tpl::output('store_info', $store_info);

        // 店铺分类信息
        $store_class_info = $model_store_class->getStoreClassInfo(array('sc_id'=>$store_info['sc_id']));
        Tpl::output('store_class_name', $store_class_info['sc_name']);

        // 店铺等级信息
        $store_grade_info = $model_store_grade->getOneGrade($store_info['grade_id']);
        Tpl::output('store_grade_name', $store_grade_info['sg_name']);

        $model_store_joinin = Model('store_joinin');
        $joinin_detail = $model_store_joinin->getOne(array('member_id'=>$store_info['member_id']));
        Tpl::output('joinin_detail', $joinin_detail);

        $store_bind_class_list = $model_store_bind_class->getStoreBindClassList(array('store_id'=>$_SESSION['store_id'],'state'=>array('in',array(1,2))), null);
        $goods_class = Model('goods_class')->getGoodsClassIndexedListAll();
        for($i = 0, $j = count($store_bind_class_list); $i < $j; $i++) {
            $store_bind_class_list[$i]['class_1_name'] = $goods_class[$store_bind_class_list[$i]['class_1']]['gc_name'];
            $store_bind_class_list[$i]['class_2_name'] = $goods_class[$store_bind_class_list[$i]['class_2']]['gc_name'];
            $store_bind_class_list[$i]['class_3_name'] = $goods_class[$store_bind_class_list[$i]['class_3']]['gc_name'];
        }
        Tpl::output('store_bind_class_list', $store_bind_class_list);

        self::profile_menu('index','index');
        
        //是否显示编辑保存按钮
        /** @var workflowModel $wkModel */
        $wkModel = Model('workflow');
        $condition = array();
        $condition['model_id'] = $store_info['member_id'];
        $condition['type'] = 3;
        $condition['role'] = 1;
        $condition['status'] = array('in' , array('0' ,'10'));
        $workflow_old = $wkModel->getWorkflowInfo($condition);
        $show_button = !$workflow_old['id'] || $workflow_old['stage']=="商家" ? 1 : 0;
        Tpl::output('show_button', $show_button);

        Tpl::showpage('store_info');
    }

    /**
     * 经营类目列表
     */
    public function bind_classOp() {

        $model_store_bind_class = Model('store_bind_class');

        $store_bind_class_list = $model_store_bind_class->getStoreBindClassList(array('store_id'=>$_SESSION['store_id']), null);
        $goods_class = Model('goods_class')->getGoodsClassIndexedListAll();
        for($i = 0, $j = count($store_bind_class_list); $i < $j; $i++) {
            $store_bind_class_list[$i]['class_1_name'] = $goods_class[$store_bind_class_list[$i]['class_1']]['gc_name'];
            $store_bind_class_list[$i]['class_2_name'] = $goods_class[$store_bind_class_list[$i]['class_2']]['gc_name'];
            $store_bind_class_list[$i]['class_3_name'] = $goods_class[$store_bind_class_list[$i]['class_3']]['gc_name'];
        }
        Tpl::output('bind_list', $store_bind_class_list);

        self::profile_menu('index','bind_class');

        Tpl::showpage('store_bind_class.index');
    }
    public function certificationOp() {

        /** @var Model $model_store_certification */
        $model_store_certification = Model('store_certification');

        $store_certifications = $model_store_certification->table('store_certification')->where(array('store_id'=>$_SESSION['store_id']))->select();
        Tpl::output('store_certifications', $store_certifications);

        self::profile_menu('index','certification');

        Tpl::showpage('store_certification.index');
    }

    /**
     * 申请新的经营类目
     */
    public function bind_class_addOp() {
        $model_goods_class = Model('goods_class');
        $gc_list = $model_goods_class->getGoodsClassListByParentId(0);
        Tpl::output('gc_list',$gc_list);

        self::profile_menu('index','bind_class');
        Tpl::showpage('store_bind_class.add','null_layout');
    }
    public function certification_addOp() {

        self::profile_menu('index','certification');
        Tpl::showpage('store_certification.add','null_layout');
    }

    /**
     * 申请新经营类目保存
     */
    public function bind_class_saveOp() {
        if (!chksubmit()) exit();
        if (preg_match('/^[\d,]+$/',$_POST['goods_class'])) {
            list($class_1, $class_2, $class_3) = explode(',', trim($_POST['goods_class'],','));
        } else {
            showDialog($lang['nc_common_save_fail']);
        }

        $model_store_bind_class = Model('store_bind_class');

        $param = array();
        $param['store_id'] = $_SESSION['store_id'];
        $param['state'] = 0;
        $param['class_1'] = $class_1;
        $last_gc_id = $class_1;
        if(!empty($class_2)) {
            $param['class_2'] = $class_2;
            $last_gc_id = $class_2;
        }
        if(!empty($class_3)) {
            $param['class_3'] = $class_3;
            $last_gc_id = $class_3;
        }

        // 检查类目是否已经存在
        $store_bind_class_info = $model_store_bind_class->getStoreBindClassInfo($param);
        if(!empty($store_bind_class_info)) {
            showDialog('该类目已经存在');
        }

        //取分佣比例
        $goods_class_info = Model('goods_class')->getGoodsClassInfoById($last_gc_id);
        $param['commis_rate'] = $goods_class_info['commis_rate'];
        $result = $model_store_bind_class->addStoreBindClass($param);
        $bid = intval($result);
        //添加审批
        $model_workflow = Model('workflow');
        $workflow_data = array();
        $model_goods_class = Model('goods_class');
        $class1 = $model_goods_class->getGoodsClassInfoById($class_1);
        $class2 = $model_goods_class->getGoodsClassInfoById($class_2);
        $class3 = $model_goods_class->getGoodsClassInfoById($class_3);
        $class = $class1['gc_name']."-".$class2['gc_name']."-".$class3['gc_name'];
        $param['state'] = 1;
        $workflow_data = array();
        $workflow_data['type'] = 7;
        $workflow_data['title'] = "商家（{$_SESSION['store_name']}）新增类目（{$class}）审核";
        $workflow_data['new_value'] = $param;
        $workflow_data['old_value'] = array();
        $workflow_data['stage'] = '商家';
        $workflow_data['role'] = 1;
        $workflow_data['user'] = $_SESSION['member_name'];
        $workflow_data['model'] = 'store_bind_class';
        $workflow_data['model_id'] = $bid;
        $workflow_data['reference'] = "/admin/modules/shop/index.php?act=store&op=store_bind_class&store_id={$store_id}";
        if(!$workflowId = $model_workflow->addWorkflow($workflow_data)){
            echo json_encode(array('result' => FALSE, 'message' => '审核信息提交失败'));
            die;
        }
        $workflow = $model_workflow->getWorkflowInfo(array('id'=>$workflowId));
        $service = Service('Workflow');
        // 初始化数据
        $service->init($workflow,$_SESSION['member_name'],'商家');
        $message = $_SESSION['member_name']."新增平台（{$_SESSION['store_name']}）类目（{$class}）审核";
        $service->approve($message);        
        //审批添加失败
        if($result) {
            showDialog('申请成功，请等待系统审核','index.php?act=store_info&op=bind_class','succ',empty($_GET['inajax']) ?'':'CUR_DIALOG.close();');
        }else {
            showDialog($lang['nc_common_save_fail']);
        }
    }
    public function certification_saveOp() {
        if (!chksubmit()) exit();
        if(empty($_POST['name'])){
            showDialog('名称不得为空');
        }
        if (!empty($_FILES['content']['name'])) {

            /** @var UploadFile $upload */
            $upload = new UploadFile();
            $upload->set('default_dir', ATTACH_STORE);
            $upload->set('thumb_width', 240);
            $upload->set('thumb_height', 320);
            $upload->set('thumb_ext', '_small');
            $upload->set('ifremove', false);
            $result = $upload->upfile('content');
            if ($result) {
                $_POST['content'] = UPLOAD_SITE_URL.DS.ATTACH_STORE.DS.$upload->file_name;
            } else {
                showDialog($upload->error);
            }
        }
        else
            showDialog('资质图片必须上传');
        $insert_array = array();
        $insert_array['name']      = trim($_POST['name']);
        $insert_array['description']   = trim($_POST['description']);
        $insert_array['content']       = $_POST['content'];
        $insert_array['store_id']       = $_SESSION['store_id'];

        /** @var Model $model_store_certification */
        $model_store_certification = Model('store_certification');
        $result = $model_store_certification->table('store_certification')->insert($insert_array);
        if($result) {
            showDialog('提交成功','index.php?act=store_info&op=certification','succ',empty($_GET['inajax']) ?'':'CUR_DIALOG.close();');
        }else {
            showDialog('提交失败');
        }
    }

    /**
     * 删除申请的经营类目
     */
    public function bind_class_delOp() {
        //中断审批
        $model_workflow = Model('workflow');
        $condition = array();
        $condition['type'] = 7;
        $condition['model_id'] = $_GET['bid'];
        $workflow = $model_workflow->getWorkflowInfo($condition);
        if($workflow['id']){
            $service = Service('Workflow');
            // 初始化数据
            $service->init($workflow,$_SESSION['member_name'],'商家');
            $message = $_SESSION['member_name']."删除平台（{$_SESSION['store_name']}）类目审核";
            $service->cancel($message);
        }
        $model_brand    = Model('store_bind_class');
        $condition = array();
        $condition['bid'] = intval($_GET['bid']);
        $condition['store_id'] = $_SESSION['store_id'];
        $condition['state'] = 0;
        $del = $model_brand->delStoreBindClass($condition);
        if ($del) {
            showDialog(Language::get('nc_common_del_succ'),'reload','succ');
        }else {
            showDialog(Language::get('nc_common_del_fail'));
        }
    }

    /**
     * 店铺续签
     */
    public function reopenOp(){

        $model_store_reopen = Model('store_reopen');
        $reopen_list = $model_store_reopen->getStoreReopenList(array('re_store_id'=>$_SESSION['store_id']));
        Tpl::output('reopen_list',$reopen_list);

        $store_info = $this->store_info;
        if(intval($store_info['store_end_time']) > 0) {
            $store_info['store_end_time_text']  = date('Y-m-d', $store_info['store_end_time']);
            $reopen_time = $store_info['store_end_time'] -3600*24 + 1  - TIMESTAMP;
            if (!checkPlatformStore() && $store_info['store_end_time'] - TIMESTAMP >= 0 && $reopen_time < 2592000) {
                //(<30天)
                $store_info['reopen'] = true;
            }
            $store_info['allow_applay_date'] = $store_info['store_end_time'] - 2592000;
        }

        if (!empty($reopen_list)) {
            $last = reset($reopen_list);
            $re_end_time = $last['re_end_time'];
            if (!checkPlatformStore() && $re_end_time - TIMESTAMP < 2592000 && $re_end_time - TIMESTAMP >= 0) {
                //(<30天)
                $store_info['reopen'] = true;
            } else {
                $store_info['reopen'] = false;
            }
        }
        Tpl::output('store_info',$store_info);

        //店铺等级
        $grade_list = rkcache('store_grade',true);
        Tpl::output('grade_list',$grade_list);

        //默认选中当前级别
        Tpl::output('current_grade_id',$_SESSION['grade_id']);

        //如果存在有未上传凭证或审核中的信息，则不能再申请续签
        $condition = array();
        $condition['re_state'] = array('in',array(0,1));
        $condition['re_store_id'] = $_SESSION['store_id'];
        $reopen_info = $model_store_reopen->getStoreReopenInfo($condition);
        if ($reopen_info) {
            if ($reopen_info['re_state'] == '0') {
                Tpl::output('upload_cert',true);
                Tpl::output('reopen_info',$reopen_info);
            }
        } else {
            Tpl::output('applay_reopen',$store_info['reopen'] ? true : false);
        }

        self::profile_menu('index','reopen');

        Tpl::showpage('store_reopen.index');
    }

    /**
     * 申请续签
     */
    public function reopen_addOp() {
        if (!chksubmit()) exit();
        if (intval($_POST['re_grade_id']) <= 0 || intval($_POST['re_year']) <= 0) exit();

        // 店铺信息
        $model_store = Model('store');
        $store_info = $this->store_info;
        if (empty($store_info['store_end_time'])) {
            showDialog('您的店铺使用期限无限制，无须续签');
        }

        $model_store_reopen = Model('store_reopen');

        //如果存在有未上传凭证或审核中的信息，则不能再申请续签
        $condition = array();
        $condition['re_state'] = array('in',array(0,1));
        $condition['re_store_id'] = $_SESSION['store_id'];
        if ($model_store_reopen->getStoreReopenCount($condition)) {
            showDialog('目前尚存在申请中的续签信息，不能重复申请');
        }

        $data = array();
        //取店铺等级信息
        $grade_list = rkcache('store_grade',true);
        if (empty($grade_list[$_POST['re_grade_id']])) exit();

        //取得店铺信息

        $data['re_grade_id'] = $_POST['re_grade_id'];
        $data['re_grade_name'] = $grade_list[$_POST['re_grade_id']]['sg_name'];
        $data['re_grade_price'] = $grade_list[$_POST['re_grade_id']]['sg_price'];

        $data['re_store_id'] = $_SESSION['store_id'];
        $data['re_store_name'] = $_SESSION['store_name'];
        $data['re_year'] = intval($_POST['re_year']);
        $data['re_pay_amount'] = $data['re_grade_price'] * $data['re_year'];
        $data['re_create_time'] = TIMESTAMP;
        $data['re_start_time'] = strtotime(date('Y-m-d 0:0:0', $store_info['store_end_time'])) + 24 * 3600;
        $data['re_end_time'] = strtotime(date('Y-m-d 23:59:59', $data['re_start_time']) . " +" . intval($data['re_year']) . " year");
        $data['re_state'] = 2;
//        if ($data['re_pay_amount'] == 0) {
//             $data['re_start_time'] = strtotime(date('Y-m-d 0:0:0',$store_info['store_end_time']))+24*3600;
//             $data['re_end_time'] = strtotime(date('Y-m-d 23:59:59', $data['re_start_time'])." +".intval($data['re_year'])." year");
//            $data['re_state'] = 1;
//        }
        $insert = $model_store_reopen->addStoreReopen($data);
        if ($insert) {
            if ($data['re_pay_amount'] == 0) {
//              $model_store->editStore(array('store_end_time'=>$data['re_end_time']),array('store_id'=>$_SESSION['store_id']));
                showDialog('您的申请已经提交，请等待管理员审核','reload','succ','',5);
            } else {
                showDialog(Language::get('nc_common_save_succ').'，需付款金额'.ncPriceFormat($data['re_pay_amount']).'元，请尽快完成付款，付款完成后请上传付款凭证','reload','succ','',5);
            }
        } else {
            showDialog(Language::get('nc_common_del_fail'));
        }
    }

    //上传付款凭证
    public function reopen_uploadOp() {
        if (!chksubmit()) exit();
        $upload = new UploadFile();
        $uploaddir = ATTACH_PATH.DS.'store_joinin'.DS;
        $upload->set('default_dir',$uploaddir);
        $upload->set('allow_type',array('jpg','jpeg','gif','png'));
        if (!empty($_FILES['re_pay_cert']['tmp_name'])){
            $result = $upload->upfile('re_pay_cert');
            if ($result){
                $pic_name = $upload->file_name;
            }
        }
        $data = array();
        $data['re_pay_cert'] = $pic_name;
        $data['re_pay_cert_explain'] = $_POST['re_pay_cert_explain'];
        $data['re_state'] = 1;
        $model_store_reopen = Model('store_reopen');
        $update = $model_store_reopen->editStoreReopen($data,array('re_id'=>$_POST['re_id'],'re_state'=>0));
        if ($update) {
            showDialog('上传成功，请等待系统审核','reload','succ');
        }else {
            showDialog(Language::get('nc_common_del_fail'));
        }
    }

    /**
     * 删除未上传付款凭证的续签信息
     */
    public function reopen_delOp() {
        $model_store_reopen = Model('store_reopen');
        $condition = array();
        $condition['re_id'] = intval($_GET['re_id']);
        $condition['re_state'] = 0;
        $condition['re_store_id'] = $_SESSION['store_id'];
        $del = $model_store_reopen->delStoreReopen($condition);
        if ($del) {
            showDialog(Language::get('nc_common_del_succ'),'reload','succ');
        }else {
            showDialog(Language::get('nc_common_del_fail'));
        }
    }

    /**
     * 用户中心右边，小导航
     *
     * @param string    $menu_type  导航类型
     * @param string    $menu_key   当前导航的menu_key
     * @param array     $array      附加菜单
     * @return
     */
    private function profile_menu($menu_type,$menu_key='',$array=array()) {
        Language::read('member_layout');
        $lang   = Language::getLangContent();
        $menu_array     = array();
        switch ($menu_type) {
            case 'index':
                $menu_array[] = array('menu_key'=>'bind_class', 'menu_name'=>$lang['nc_member_path_bind_class'], 'menu_url'=>'index.php?act=store_info&op=bind_class');
                if (!checkPlatformStore()) {
                    $menu_array[] = array('menu_key'=>'index', 'menu_name'=>$lang['nc_member_path_store_info'], 'menu_url'=>'index.php?act=store_info&op=index');
                    $menu_array[] = array('menu_key'=>'reopen', 'menu_name'=>$lang['nc_member_path_store_reopen'], 'menu_url'=>'index.php?act=store_info&op=reopen');
                    $menu_array[] = array('menu_key'=>'certification', 'menu_name'=>'认证资质', 'menu_url'=>'index.php?act=store_info&op=certification');
                }
                break;
            case 'bind_class':
                $menu_array = array(
                array('menu_key'=>'index', 'menu_name'=>$lang['nc_member_path_bind_class'], 'menu_url'=>'index.php?act=store_bind_class&op=index'),
                );
                break;
            case 'certification':
                $menu_array = array(
                array('menu_key'=>'index', 'menu_name'=>$lang['nc_member_path_bind_class'], 'menu_url'=>'index.php?act=store_certification&op=index'),
                );
                break;
            case 'add':
                $menu_array = array(
                array('menu_key'=>'index', 'menu_name'=>$lang['nc_member_path_bind_class'], 'menu_url'=>'index.php?act=store_bind_class&op=index'),
                array('menu_key'=>'add', 'menu_name'=>$lang['nc_member_path_bind_class_add'], 'menu_url'=>'index.php?act=store_bind_class&op=add')
                );
                break;
        }
        if(!empty($array)) {
            $menu_array[] = $array;
        }
        Tpl::output('member_menu',$menu_array);
        Tpl::output('menu_key',$menu_key);
    }
    
    public function edit_save_joininOp()
    {
    	$model_store = Model('store');
    	$store_info = $model_store->getStoreInfoByID($_SESSION['store_id']);
    	$member_id = $store_info['member_id'];
    	if ($member_id <= 0) {
    		showMessage(L('param_error'));
    	}

    	$param = array();
    	$attchemt = array(); //附件信息组
    	//add by ljq 有的店铺没有公司信息，如果没有就添加
    	$company_info = Model('store_joinin')->getOne(array('member_id'=>$member_id));
    	if(empty($company_info['member_id'])){
    		$param['member_id'] = $member_id;
    		$param['member_name'] = $store_info['member_name'];
    	}
    	$param['company_name'] = $_POST['company_name'];
    	$param['company_province_id'] = intval($_POST['province_id']);
    	$param['company_address'] = $_POST['company_address'];
    	$param['company_address_detail'] = $_POST['company_address_detail'];
    	$param['company_phone'] = $_POST['company_phone'];
    	$param['company_employee_count'] = intval($_POST['company_employee_count']);
    	$param['company_registered_capital'] = intval($_POST['company_registered_capital']);
    	$param['contacts_name'] = $_POST['contacts_name'];
    	$param['contacts_phone'] = $_POST['contacts_phone'];
    	$param['contacts_email'] = $_POST['contacts_email'];
    	$param['business_licence_number'] = $_POST['business_licence_number'];
    	$param['business_licence_address'] = $_POST['business_licence_address'];
    	$param['business_licence_start'] = $_POST['business_licence_start'];
    	$param['business_licence_end'] = $_POST['business_licence_end'];
    	$param['business_sphere'] = $_POST['business_sphere'];
    	if ($_FILES['business_licence_number_elc']['name'] != '') {
    		$param['business_licence_number_elc'] = $this->upload_image('business_licence_number_elc');
    		//$attchemt['business_licence_number_elc'] = getStoreJoininImageUrl($param['business_licence_number_elc']);
    	}
    	$param['organization_code'] = $_POST['organization_code'];
    	if ($_FILES['organization_code_electronic']['name'] != '') {
    		$param['organization_code_electronic'] = $this->upload_image('organization_code_electronic');
    		//$attchemt['organization_code_electronic'] = getStoreJoininImageUrl($param['organization_code_electronic']);
    	}
    	if ($_FILES['general_taxpayer']['name'] != '') {
    		$param['general_taxpayer'] = $this->upload_image('general_taxpayer');
    		//$attchemt['general_taxpayer'] = getStoreJoininImageUrl($param['general_taxpayer']);
    	}
    	$param['bank_account_name'] = $_POST['bank_account_name'];
    	$param['bank_account_number'] = $_POST['bank_account_number'];
    	$param['bank_name'] = $_POST['bank_name'];
    	$param['bank_code'] = $_POST['bank_code'];
    	$param['bank_address'] = $_POST['bank_address'];
    	if ($_FILES['bank_licence_electronic']['name'] != '') {
    		$param['bank_licence_electronic'] = $this->upload_image('bank_licence_electronic');
    		//$attchemt['bank_licence_electronic'] = getStoreJoininImageUrl($param['bank_licence_electronic']);
    	}
    	$param['settlement_bank_account_name'] = $_POST['settlement_bank_account_name'];
    	$param['settlement_bank_account_number'] = $_POST['settlement_bank_account_number'];
    	$param['settlement_bank_name'] = $_POST['settlement_bank_name'];
    	$param['settlement_bank_code'] = $_POST['settlement_bank_code'];
    	$param['settlement_bank_address'] = $_POST['settlement_bank_address'];
    	$param['tax_registration_certificate'] = $_POST['tax_registration_certificate'];
    	$param['taxpayer_id'] = $_POST['taxpayer_id'];
    	if ($_FILES['tax_registration_certif_elc']['name'] != '') {
    		$param['tax_registration_certif_elc'] = $this->upload_image('tax_registration_certif_elc');
    		//$attchemt['tax_registration_certif_elc'] = getStoreJoininImageUrl($param['tax_registration_certif_elc']);
    	}
    	$new_value = array();
    	$old_value = array();
    	$workflow_data= array();
    	if($company_info['member_id']){
    		//$result = Model('store_joinin')->editStoreJoinin(array('member_id' => $member_id), $param);
    		foreach($param as $k=>$v){
    			if($param[$k] != $company_info[$k]){
    				if(!in_array($k , array('tax_registration_certif_elc','bank_licence_electronic','general_taxpayer','organization_code_electronic','business_licence_number_elc'))){
    					$new_value[$k] = $param[$k];
    					$old_value[$k] = $company_info[$k];
    				}else{
    					$new_value[$k] = getStoreJoininImageUrl($param[$k]);
    					$old_value[$k] = getStoreJoininImageUrl($company_info[$k]);
    				}
    			}
    		}
    		$workflow_data['type'] = 3;
    		$workflow_data['title'] = "商家发起店铺({$store_info['store_name']})资质变更审批";
    	}else{
    		//$result = Model('store_joinin')->save($param);
    		$new_value = $param;
    		$workflow_data['type'] = 6;
    		$workflow_data['title'] = "商家发起新增店铺({$store_info['store_name']})资质审批";
    	}

    	//添加审核日志信息
    	if(count($new_value)>0){
    		/** @var WorkflowService $workflow */
    		$service = Service('Workflow');
    		/** @var workflowModel $wkModel */
    		$wkModel = Model('workflow');
    		$condition = array();
    		$condition['model_id'] = $member_id;
    		$condition['type'] = $workflow_data['type'];
    		$condition['status'] = array('in' , array('0' ,'10'));
    		$condition['role'] = 1;//商家发起
    		$workflow_old = Model('workflow')->getWorkflowInfo($condition , 'id');
    		//die(var_dump($workflow_old));
    		if($workflow_old['id']){
    			$where = $data = array();
    			$where['id'] = $workflow_old['id'];
    			$data['new_value'] = JSON($new_value);
    			$data['old_value'] = JSON($old_value);
    			$data['stage'] = "运营部";
    			$wkModel->where($where)->update($data) ;
    			
    			$log = array();
    			$log['workflow_id'] = $workflow_old['id'];
    			$log['stage'] = "商家";
    			$log['opinion'] = 1;
    			$log['message'] = "流程打回修改后再次申请";
    			$log['attachment'] = "";
    			$log['role'] = 1;
    			$log['user'] = $store_info['member_name'];
    			$log['created_at'] = time();
    			Model('workflow_log')->insert($log);
    		} else {
    			$workflow_data['stage'] = "商家";
    			$workflow_data['model'] = 'store_joinin';
    			$workflow_data['model_id'] =$member_id;
    			$workflow_data['new_value'] = JSON($new_value);
    			$workflow_data['old_value'] = JSON($old_value);
    			$workflow_data['reference'] = "/admin/modules/shop/index.php?act=store&op=store_edit&store_id={$_SESSION['store_id']}";
    			$workflow_data['role'] = 1;
    			$workflow_data['user'] = $store_info['member_name'];
    			if(!$workflowId = $wkModel->addWorkflow($workflow_data)){
    				showMessage('插入审核日志失败');
    				exit();
    			}
    			 
    			$workflow = $wkModel->getWorkflowInfo(array('id'=>$workflowId));
    			// 初始化数据
    			$service->init($workflow,$store_info['member_name'],'商家',$service::ROLE_SELLER);
    			$message = $store_info['member_name']."商家发起店铺({$store_info['store_name']})资质变更审批";
    			$service->approve($message);
    		}
    		
    		showMessage("审批提交成功，请等待审核","index.php?act=store_info&op=index");
    	}else{
    		showMessage('您未做任何项目修改',"index.php?act=store_info&op=index");
    	}
    }
    
    private function upload_image($file)
    {
    	$pic_name = '';
    	$upload = new UploadFile();
    	$uploaddir = ATTACH_PATH . DS . 'store_joinin' . DS;
    	$upload->set('default_dir', $uploaddir);
    	$upload->set('allow_type', array('jpg', 'jpeg', 'gif', 'png'));
    	if (!empty($_FILES[$file]['name'])) {
    		$result = $upload->upfile($file);
    		if ($result) {
    			$pic_name = $upload->file_name;
    			$upload->file_name = '';
    		}
    	}
    	return $pic_name;
    }
}
