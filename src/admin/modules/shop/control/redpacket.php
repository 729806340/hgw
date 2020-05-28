<?php
/**
 * 平台红包管理
 * @汉购网提供技术支持 授权请购买shopnc授权
 * @license    http://www.hangowa.com
 * @link       交流群号：
 */


defined('ByShopWWI') or exit('Access Invalid!');
class redpacketControl extends SystemControl{
    //每次导出订单数量
    const EXPORT_SIZE = 1000;
    private $gettype_arr;
    private $templatestate_arr;
    private $redpacket_state_arr;
    private $member_grade_arr;
    
    public function __construct(){
        parent::__construct();
        if (C('redpacket_allow') != 1){
            showDialog('需开启“平台红包”功能','index.php?act=operation','succ');
        }
        $model_redpacket = Model('redpacket');
        $this->gettype_arr = $model_redpacket->getGettypeArr();
        $this->templatestate_arr = $model_redpacket->getTemplateState();
        $this->redpacket_state_arr = $model_redpacket->getRedpacketState();
        $this->member_grade_arr = Model('member')->getMemberGradeArr();
    }

    /*
     * 默认操作列出红包
     */
    public function indexOp(){
        $this->rptlistOp();
    }
    /**
     * 新增红包
     */
    public function rptaddOp(){

        if (chksubmit()){
            $obj_validate = new Validate();
            $obj_validate->validateparam = array(
                    array("input"=>$_POST['rpt_title'], "require"=>"true","validator"=>"Length","min"=>"1","max"=>"50","message"=>'模版名称不能为空且小于50个字符'),
                    array("input"=>$_POST['rpt_gettype'], "require"=>"true","message"=>'请选择领取方式'),
                    array("input"=>$_POST['rpt_sdate'], "require"=>"true","message"=>'请选择有效期开始时间'),
                    array("input"=>$_POST['rpt_edate'], "require"=>"true","message"=>'请选择有效期结束时间'),
                    array("input"=>$_POST['rpt_price'], "require"=>"true","validator"=>"Number","min"=>"1","message"=>'面额不能为空且为大于1的整数'),
                    array("input"=>$_POST['rpt_total'], "require"=>"true","validator"=>"Number","min"=>"1","message"=>'可发放数量不能为空且为大于1的整数'),
                    array("input"=>$_POST['rpt_orderlimit'], "require"=>"true","validator"=>"Double","min"=>"0","message"=>'模版使用消费限额不能为空且必须是数字'),
                    array("input"=>$_POST['rpt_desc'], "require"=>"true","validator"=>"Length","min"=>"1","max"=>"200","message"=>'模版描述不能为空且小于200个字符'),
            );
            $error = $obj_validate->validate();
            //开始时间不能大于结束时间
            $stime = strtotime($_POST['rpt_sdate']);
            $etime = strtotime($_POST['rpt_edate']);
            if ($stime > $etime){
                $error.= '开始时间不能大于结束时间';
            }
            $range = intval($_POST['rpt_range']);
            $sku = $_POST['rpt_skus'];
            if($range >0 && empty($sku)) $error.= '非全场通用时，商品不得为空';


            //验证红包面额不能大于订单限额
            $price = floatval($_POST['rpt_price'])>0?floatval($_POST['rpt_price']):0;
            $limit = floatval($_POST['rpt_orderlimit'])>0?floatval($_POST['rpt_orderlimit']):0;
            if($limit>0 && $price>=$limit) $error.= '面额不能大于消费限额';
            //验证卡密红包发放数量
            $gettype = trim($_POST['rpt_gettype']);
            if($gettype == 'pwd'){
                if (intval($_POST['rpt_total']) > 10000){
                    $error.= '领取方式为卡密兑换的红包，发放总数不能超过10000张';
                }
            }

            $bill_rate = intval($_POST['bill_rate']);
            if ($bill_rate > 100||$bill_rate <0){
                $error.= '承担比例须在0到100之间';
            }
            //验证积分
            $points = intval($_POST['rpt_points']);
            if($gettype == 'points' && $points < 1){
                $error.= '兑换所需积分不能为空且为大于1的整数';
            }
            if ($error){
                showDialog($error, '', 'error');
            }else {
                $model_redpacket = Model('redpacket');
                $insert_arr = array();
                $insert_arr['rpacket_t_title'] = trim($_POST['rpt_title']);
                $insert_arr['rpacket_t_desc'] = trim($_POST['rpt_desc']);
                $insert_arr['rpacket_t_start_date'] = $stime;
                $insert_arr['rpacket_t_end_date'] = $etime;
                $insert_arr['rpacket_t_price'] = $price;
                $insert_arr['rpacket_t_limit'] = $limit;
                $insert_arr['rpacket_t_adminid'] = $this->admin_info['id'];
                $insert_arr['rpacket_t_state'] = $this->templatestate_arr['usable']['sign'];
                $insert_arr['rpacket_t_total'] = intval($_POST['rpt_total']);
                $insert_arr['rpacket_t_giveout'] = 0;
                $insert_arr['rpacket_t_goods_type'] = in_array($_POST['rpacket_t_goods_type'], array(1, 2)) ? $_POST['rpacket_t_goods_type'] : 0;
                $insert_arr['rpacket_t_used'] = 0;
                $insert_arr['rpacket_t_updatetime'] = time();
                $insert_arr['rpacket_t_points'] = $points;
                $insert_arr['rpacket_t_eachlimit'] = ($t = intval($_POST['rpt_eachlimit']))>0?$t:0;
                $insert_arr['rpacket_t_recommend'] = 0;
                $insert_arr['rpacket_t_gettype'] = in_array($gettype,array_keys($this->gettype_arr))?$this->gettype_arr[$gettype]['sign']:$this->gettype_arr[$model_redpacket::GETTYPE_DEFAULT]['sign'];
                $insert_arr['rpacket_t_isbuild'] = 0;
                $insert_arr['rpacket_t_range'] = $range;
                //新增range=3，商品分类适用，储存于新增字段rpacket_classes
                if ($range==3) {
                    $insert_arr['rpacket_t_classes'] = $sku;
                    // $insert_arr['rpacket_t_skus'] = $sku;
                }else{
                    $insert_arr['rpacket_t_skus'] = $sku;
                }

                $insert_arr['bill_rate'] = $bill_rate;
                $insert_arr['rpacket_t_show_goods_detail'] = $_POST['rpacket_t_show_goods_detail'] != 1 ? 0 : 1;
                $mgrade_limit = intval($_POST['rpt_mgradelimit']);
                $insert_arr['rpacket_t_mgradelimit'] = in_array($mgrade_limit,array_keys($this->member_grade_arr))?$mgrade_limit:$this->member_grade_arr[0]['level'];
                //自定义图片
                if (!empty($_FILES['rpt_img']['name'])){
                    $upload = new UploadFile();
                    $upload->set('default_dir', ATTACH_REDPACKET);
                    $upload->set('thumb_width','160');
                    $upload->set('thumb_height','160');
                    $upload->set('thumb_ext','_small');
                    $result = $upload->upfile('rpt_img');
                    if ($result){
                        $insert_arr['rpacket_t_customimg'] =  $upload->file_name;
                    }
                }
                $rs = $model_redpacket->addRptTemplate($insert_arr);
                // dump($insert_arr);
                // dump($rs);
                if($rs){
                    //生成卡密红包
                    if($gettype == 'pwd'){
                        QueueClient::push('build_pwdRedpacket', $rs);
                    }
                    $this->log("新增红包模板[ID：{$rs}]成功");
                    showDialog(L('nc_common_save_succ'),'index.php?act=redpacket&op=rptlist','succ');
                }else{
                    showDialog(L('nc_common_save_fail'),'','error');
                }
            }
        }else {
            TPL::output('gettype_arr',$this->gettype_arr);
            TPL::output('member_grade',$this->member_grade_arr);
			Tpl::setDirquna('shop');/*网 店 运 维shop wwi.com*/
            Tpl::showpage('redpacket.templateadd');
        }
    }
    /**
     * 红包列表
     */
    public function rptlistOp()
    {
        TPL::output('gettype_arr',$this->gettype_arr);
        TPL::output('templateState',$this->templatestate_arr);
		Tpl::setDirquna('shop');/*网 店 运 维shop wwi.com*/
        Tpl::showpage('redpacket.templatelist');
    }

    /**
     * 红包模板列表XML
     */
    public function rptlist_xmlOp()
    {
        $where = array();
        if ($_REQUEST['advanced']) {
            if (strlen($q = trim($_REQUEST['rpt_title']))) {
                $where['rpacket_t_title'] = array('like', '%' . $q . '%');
            }
            if (($q = (int) $_REQUEST['rpt_gettype']) > 0) {
                $where['rpacket_t_gettype'] = $q;
            }
            if (($q = (int) $_REQUEST['rpt_state']) > 0) {
                $where['rpacket_t_state'] = $q;
            }
            if (strlen($q = trim($_REQUEST['rpt_recommend']))) {
                $where['rpacket_t_recommend'] = (int) $q;
            }

            if (trim($_GET['sdate']) && trim($_GET['edate'])) {
                $sdate = strtotime($_GET['sdate']);
                $edate = strtotime($_GET['edate']);
                $where['rpacket_t_updatetime'] = array('between', "$sdate,$edate");
            } elseif (trim($_GET['sdate'])) {
                $sdate = strtotime($_GET['sdate']);
                $where['rpacket_t_updatetime'] = array('egt', $sdate);
            } elseif (trim($_GET['edate'])) {
                $edate = strtotime($_GET['edate']);
                $where['rpacket_t_updatetime'] = array('elt', $edate);
            }

            $pdates = array();
            if (strlen($q = trim((string) $_REQUEST['pdate1'])) && ($q = strtotime($q . ' 00:00:00'))) {
                $pdates[] = "rpacket_t_end_date >= {$q}";
            }
            if (strlen($q = trim((string) $_REQUEST['pdate2'])) && ($q = strtotime($q . ' 00:00:00'))) {
                $pdates[] = "rpacket_t_start_date <= {$q}";
            }
            if ($pdates) {
                $where['pdates'] = array('exp',implode(' and ', $pdates));
            }
        } else {
            if (strlen($q = trim($_REQUEST['query']))) {
                switch ($_REQUEST['qtype']) {
                    case 'rpt_title':
                        $where['rpacket_t_title'] = array('like', "%$q%");
                        break;
                }
            }
        }

        switch ($_REQUEST['sortname']) {
            case 'rpacket_t_price':
            case 'rpacket_t_limit':
                $sort = $_REQUEST['sortname'];
                break;
            case 'rpacket_t_mgradelimittext':
                $sort = 'rpacket_t_mgradelimit';
                break;
            case 'rpacket_t_updatetimetext':
                $sort = 'rpacket_t_updatetime';
                break;
            case 'rpacket_t_start_datetext':
                $sort = 'rpacket_t_start_date';
                break;
            case 'rpacket_t_end_datetext':
                $sort = 'rpacket_t_end_date';
                break;
            case 'rpacket_t_statetext':
                $sort = 'rpacket_t_state';
                break;
            case 'rpacket_t_recommend':
                $sort = 'rpacket_t_recommend';
                break;
            default:
                $sort = 'rpacket_t_id';
                break;
        }
        if ($_REQUEST['sortorder'] != 'asc') {
            $sort .= ' desc';
        }

        $model_redpacket = Model('redpacket');
        $list = $model_redpacket->getRptTemplateList($where, '*', 0, $_REQUEST['rp'], $sort);
        
        $data = array();
        $data['now_page'] = $model_redpacket->shownowpage();
        $data['total_num'] = $model_redpacket->gettotalnum();
        foreach ($list as $val) {
            $o = '';
            if($val['rpacket_t_giveout']<=0 && $val['rpacket_t_isbuild'] == 0){
                $o .= '<a class="btn red" href="javascript:void(0);" onclick="fg_del('.$val['rpacket_t_id'].')"><i class="fa fa-trash-o"></i>删除</a>';
            }            
            $o .= "<span class='btn'><em><i class='fa fa-cog'></i>设置 <i class='arrow'></i></em><ul>";
            $o .= "<li><a href='" . urlAdminShop('redpacket', 'rptedit', array('tid' => $val['rpacket_t_id'])) . "'>编辑信息</a></li>";
            $o .= "<li><a href='" . urlAdminShop('redpacket', 'rptcopy', array('tid' => $val['rpacket_t_id'])) . "'>复制新建红包</a></li>";
            $o .= "<li><a href='" . urlAdminShop('redpacket', 'rptinfo', array('tid' => $val['rpacket_t_id'])) . "'>查看详细</a></li>";
            $o .= "</ul>";
            
            $i = array();
            $i['operation'] = $o;
            $i['rpacket_t_id'] = $val['rpacket_t_id'];
            $i['rpacket_t_title'] = $val['rpacket_t_title'];
            $i['rpacket_t_price'] = $val['rpacket_t_price'];
            $i['rpacket_t_limit'] = $val['rpacket_t_limit'];
            $i['rpacket_t_mgradelimittext'] = $val['rpacket_t_mgradelimittext'];
            if($val['rpacket_t_diamond'] == 1){
                $i['rpacket_t_mgradelimittext'] = '钻石';
            }
            $i['rpacket_t_updatetimetext'] = date('Y-m-d H:i', $val['rpacket_t_updatetime']);
            $i['rpacket_t_start_datetext'] = date('Y-m-d H:i', $val['rpacket_t_start_date']);
            $i['rpacket_t_end_datetext'] = date('Y-m-d H:i', $val['rpacket_t_end_date']);
            $i['rpacket_t_gettype_text'] = $val['rpacket_t_gettype_text'];
            $i['rpacket_t_statetext'] = $val['rpacket_t_state_text'];
            $i['rpacket_t_recommendtext'] = $val['rpacket_t_recommend'] == '1'
                ? '<span class="yes"><i class="fa fa-check-circle"></i>是</span>'
                : '<span class="no"><i class="fa fa-ban"></i>否</span>';

            $data['list'][$val['rpacket_t_id']] = $i;
        }
        echo Tpl::flexigridXML($data);
        exit;
    }
    /*
     * 获取商品列表
     */
    public function get_goods_listOp()
    {
        $where = array(
            'goods_storage' => array('gt', 0),
        );
            if (strlen($goods_name = trim($_REQUEST['goods_name']))) {
                $where['goods_name'] = array('like', "%$goods_name%");
            }
            if (strlen($goods_id = trim($_REQUEST['goods_id']))) {
                $where['goods_id'] = $goods_id;
            }
        $page = $_GET['page'];

        /** @var goodsModel $goodsModel */
        $goodsModel = Model('goods');


        if (in_array($_REQUEST['goods_type'], array(1, 2))) {

            $store_where = array(
                'manage_type' => $_REQUEST['goods_type'] == 1 ? 'co_construct' : 'platform'
            );
            /** @var storeModel $store_model */
            $store_model = Model('store');
            $store_list = $store_model->getStoreList($store_where);
            $store_ids = array_column($store_list, 'store_id');
            if (!empty($store_ids)) {
                $where['store_id'] = array('in', $store_ids);
            }
        }

        $list = $goodsModel->getGoodsOnlineList($where, '*', '10', '', 0);
        $total = ceil($goodsModel->gettotalnum()/10);

        echo json_encode(array('total'=>$total,'items'=>$list));
        exit;
    }

    /*
     * 获取商品分类列表
     */
    public function get_goods_class_listOp()
    {
        $where = array();
        if (strlen($goods_name = trim($_REQUEST['goods_name']))) {
            $where['gc_name'] = array('like', "%$goods_name%");
        }
        $goodsClassModel = Model('goods_class');
        $list = $goodsClassModel->getGoodsClassList($where);
        $total = 1;
        foreach ($list as $k => $v) {
            $menu = $goodsClassModel->getGoodsClassLineForTag($v['gc_id']);
            $list[$k]['menu'] = $menu['gc_tag_name'];
        }

        echo json_encode(array('total'=>$total,'items'=>$list));
        // dump($goodsClassModel->getGoodsClassLineForTag(103));
        // dump($list);
        // dump($list2);
        exit;
    }

    /*
     * 红包模版编辑
     */
    public function rpteditOp(){
        $t_id = intval($_GET['tid']);
        if ($t_id <= 0){
            $t_id = intval($_POST['tid']);
        }
        if ($t_id <= 0){
            showDialog(L('param_error'),'index.php?act=redpacket&op=rptlist');
        }
        $model_redpacket = Model('redpacket');
        //查询模板信息
        $where = array();
        $where['rpacket_t_id'] = $t_id;
        $t_info = $model_redpacket->getRptTemplateInfo($where);
        if (!$t_info){
            showDialog(L('param_error'),'index.php?act=redpacket&op=rptlist');
        }
        //判断模板详情是否能编辑
        if($t_info['rpacket_t_giveout'] > 0 || $t_info['rpacket_t_isbuild'] == 1){
            $t_info['ableedit'] = false;
        } else {
            $t_info['ableedit'] = true;
        }
        //获取商品列表
        /** @var goodsModel $goodsModel */
        $goodsModel = Model('goods');
        $t_info['goodsList'] = $goodsModel->getGoodsList(array('goods_id'=>array('in',$t_info['rpacket_t_skus'])), '*', '', '', 0,1000);
        $goodsClassModel = Model('goods_class');
        $t_info['goodsClassList'] = $goodsClassModel->getGoodsClassList(array('gc_id'=>array('in',$t_info['rpacket_t_classes'])), '*', '', '', 0,1000);
        foreach ($t_info['goodsClassList'] as $k => $v) {
            $menu = $goodsClassModel->getGoodsClassLineForTag($v['gc_id']);
            $t_info['goodsClassList'][$k]['menu'] = $menu['gc_tag_name'];
        }
        //v($goodsModel->getLastSql());
        if(chksubmit()){
            if ($t_info['ableedit'] == true){
                $obj_validate = new Validate();
                $obj_validate->validateparam = array(
                        array("input"=>$_POST['rpt_title'], "require"=>"true","validator"=>"Length","min"=>"1","max"=>"50","message"=>'模版名称不能为空且小于50个字符'),
                        array("input"=>$_POST['rpt_gettype'], "require"=>"true","message"=>'请选择领取方式'),
                        array("input"=>$_POST['rpt_sdate'], "require"=>"true","message"=>'请选择有效期开始时间'),
                        array("input"=>$_POST['rpt_edate'], "require"=>"true","message"=>'请选择有效期结束时间'),
                        array("input"=>$_POST['rpt_price'], "require"=>"true","validator"=>"Number","min"=>"1","message"=>'面额不能为空且为大于1的整数'),
                        array("input"=>$_POST['rpt_total'], "require"=>"true","validator"=>"Number","min"=>"1","message"=>'可发放数量不能为空且为大于1的整数'),
                        array("input"=>$_POST['rpt_orderlimit'], "require"=>"true","validator"=>"Double","min"=>"0","message"=>'模版使用消费限额不能为空且必须是数字'),
                        array("input"=>$_POST['rpt_desc'], "require"=>"true","validator"=>"Length","min"=>"1","max"=>"200","message"=>'模版描述不能为空且小于200个字符')
                );
                $error = $obj_validate->validate();
                //开始时间不能大于结束时间
                $stime = strtotime($_POST['rpt_sdate']);
                $etime = strtotime($_POST['rpt_edate']);
                if ($stime > $etime){
                    $error.= '开始时间不能大于结束时间';
                }
                //验证红包面额不能大于订单限额
                $price = floatval($_POST['rpt_price'])>0?floatval($_POST['rpt_price']):0;
                $limit = floatval($_POST['rpt_orderlimit'])>0?floatval($_POST['rpt_orderlimit']):0;
                if($limit>0 && $price>=$limit) $error.= '面额不能大于消费限额';
                //验证卡密红包发放数量
                $gettype = trim($_POST['rpt_gettype']);
                if($gettype == 'pwd'){
                    if (intval($_POST['rpt_total']) > 10000){
                        $error.= '领取方式为卡密兑换的红包，发放总数不能超过10000张';
                    }
                }
                $range = intval($_POST['rpt_range']);
                $sku = $_POST['rpt_skus'];
                if($range >0 && empty($sku)) $error.= '非全场通用时，商品不得为空';

                $bill_rate = intval($_POST['bill_rate']);
                if ($bill_rate > 100||$bill_rate <0){
                    $error.= '承担比例须在0到100之间';
                }
                //验证积分
                $points = intval($_POST['rpt_points']);
                if($gettype == 'points' && $points < 1){
                    $error.= '兑换所需积分不能为空且为大于1的整数';
                }
                if($gettype <> 'points') {
                    $points = 0;
                }
                if ($error){
                    showDialog($error, '', 'error');
                }
                $update_arr = array();
                $update_arr['rpacket_t_title'] = trim($_POST['rpt_title']);
                $update_arr['rpacket_t_desc'] = trim($_POST['rpt_desc']);
                $update_arr['rpacket_t_start_date'] = $stime;
                $update_arr['rpacket_t_end_date'] = $etime;
                $update_arr['rpacket_t_price'] = $price;
                $update_arr['rpacket_t_limit'] = $limit;
                $update_arr['rpacket_t_adminid'] = $this->admin_info['id'];
                $update_arr['rpacket_t_total'] = intval($_POST['rpt_total']);
                $update_arr['rpacket_t_giveout'] = 0;
                $update_arr['rpacket_t_used'] = 0;
                $update_arr['rpacket_t_updatetime'] = time();
                $update_arr['rpacket_t_points'] = $points;
                $update_arr['rpacket_t_eachlimit'] = ($t = intval($_POST['rpt_eachlimit']))>0?$t:0;
                $update_arr['rpacket_t_gettype'] = $this->gettype_arr[$gettype]['sign'];
                $update_arr['rpacket_t_isbuild'] = 0;
                //编辑红包适用范围时,本身前端页面就不可以改,所以这个字段可不必更新,吴桐-2019-4-3
                // $update_arr['rpacket_t_range'] = $range;
                if ($range==3) {
                    $update_arr['rpacket_t_classes'] = $sku;
                }else{
                    $update_arr['rpacket_t_skus'] = $sku;
                }
                $update_arr['bill_rate'] = $bill_rate;
                $mgrade_limit = intval($_POST['rpt_mgradelimit']);
                $update_arr['rpacket_t_mgradelimit'] = in_array($mgrade_limit,array_keys($this->member_grade_arr))?$mgrade_limit:$this->member_grade_arr[0]['level'];
                //自定义图片
                if (!empty($_FILES['rpt_img']['name'])){
                    $upload = new UploadFile();
                    $upload->set('default_dir', ATTACH_REDPACKET);
                    $upload->set('thumb_width','160');
                    $upload->set('thumb_height','160');
                    $upload->set('thumb_ext','_small');
                    $result = $upload->upfile('rpt_img');
                    if ($result){
                        $update_arr['rpacket_t_customimg'] =  $upload->file_name;
                        //删除旧图片
                        if ($t_info['rpacket_t_customimg'] && is_file(BASE_UPLOAD_PATH . '/' . ATTACH_REDPACKET . '/' . $t_info['rpacket_t_customimg'])) {
                            @unlink(BASE_UPLOAD_PATH . '/' . ATTACH_REDPACKET . '/' . $t_info['rpacket_t_customimg']);
                            @unlink(BASE_UPLOAD_PATH . '/' . ATTACH_REDPACKET . '/' . str_ireplace('.', '_small.', $t_info['rpacket_t_customimg']));
                        }
                    }
                }
            }
            $update_arr['rpacket_t_state'] = ($t=intval($_POST['rpt_state']))==1?1:2;
            $update_arr['rpacket_t_recommend'] = ($t=intval($_POST['recommend']))==1?1:0;
            $update_arr['rpacket_t_show_goods_detail'] = $_POST['rpacket_t_show_goods_detail'] != 1 ? 0 : 1;
            $rs = Model('redpacket')->editRptTemplate(array('rpacket_t_id'=>$t_id),$update_arr);
            if($rs){
                $this->log("编辑红包模板[ID：{$t_id}]成功");
                showDialog(L('nc_common_save_succ'),'index.php?act=redpacket&op=rptlist','succ');
            } else {
                showDialog(L('nc_common_save_fail'),'','error');
            }
        }else{
            //查询最近修改的管理员
            $creator_info = Model('admin')->getOneAdmin($t_info['rpacket_t_adminid']);
            $t_info['rpacket_t_creator_name'] = $creator_info['admin_name'];
            $t_info['rpacket_t_price'] = intval($t_info['rpacket_t_price']);
            TPL::output('gettype_arr',$this->gettype_arr);
            TPL::output('member_grade',$this->member_grade_arr);
            TPL::output('templatestate_arr',$this->templatestate_arr);
            TPL::output('t_info',$t_info);
			Tpl::setDirquna('shop');/*网 店 运 维shop wwi.com*/
            Tpl::showpage('redpacket.templateedit');
        }
    }

    /*
 * 红包模版编辑
 */
    public function rptcopyOp(){
        $t_id = intval($_GET['tid']);
        if ($t_id <= 0){
            $t_id = intval($_POST['tid']);
        }
        if ($t_id <= 0){
            showDialog(L('param_error'),'index.php?act=redpacket&op=rptlist');
        }
        $model_redpacket = Model('redpacket');
        //查询模板信息
        $where = array();
        $where['rpacket_t_id'] = $t_id;
        $t_info = $model_redpacket->getRptTemplateInfo($where);
        if (!$t_info){
            showDialog(L('param_error'),'index.php?act=redpacket&op=rptlist');
        }
        $t_info['ableedit'] = true;

        //判断模板详情是否能编辑
        if($t_info['rpacket_t_gettype'] == 2){
            showDialog('暂不支持卡密类型','index.php?act=redpacket&op=rptlist');
        }

        //获取商品列表
        /** @var goodsModel $goodsModel */
        $goodsModel = Model('goods');
        $t_info['goodsList'] = $goodsModel->getGoodsList(array('goods_id'=>array('in',$t_info['rpacket_t_skus'])), '*', '', '', 0,1000);

        TPL::output('gettype_arr',$this->gettype_arr);
        TPL::output('member_grade',$this->member_grade_arr);
        TPL::output('templatestate_arr',$this->templatestate_arr);
        TPL::output('t_info',$t_info);
        Tpl::setDirquna('shop');/*网 店 运 维shop wwi.com*/
        Tpl::showpage('redpacket.templatecopy');

    }


    /**
     * 删除红包模板 
     */
    public function rptdelOp() {
        $t_id = intval($_GET['tid']);
        if ($t_id <= 0){
            showDialog(L('param_error'));
        }
        $model_redpacket = Model('redpacket');
        //查询模板信息
        $where = array();
        $where['rpacket_t_id'] = $t_id;
        $where['rpacket_t_giveout'] = array('elt',0);
        $where['rpacket_t_isbuild'] = 0;
        $result = $model_redpacket->dropRptTemplate($where);
        if ($result){
            $this->log("删除红包模板[ID：{$t_id}]成功");
            exit(json_encode(array('state'=>true,'msg'=>'删除成功')));
        } else {
            exit(json_encode(array('state'=>false,'msg'=>'删除失败')));
        }
    }
    
    /*
     * 红包模版编辑
    */
    public function rptinfoOp(){
        $t_id = intval($_GET['tid']);
        if ($t_id <= 0){
            $t_id = intval($_POST['tid']);
        }
        if ($t_id <= 0){
            showDialog(L('param_error'),'index.php?act=redpacket&op=rptlist');
        }
        $model_redpacket = Model('redpacket');
        //查询模板信息
        $where = array();
        $where['rpacket_t_id'] = $t_id;
        $t_info = $model_redpacket->getRptTemplateInfo($where);
        if (!$t_info){
            showDialog(L('param_error'),'index.php?act=redpacket&op=rptlist');
        }

        //获取商品列表
        /** @var goodsModel $goodsModel */
        $goodsModel = Model('goods');
        $t_info['goodsList'] = $goodsModel->getGoodsList(array('goods_id'=>array('in',$t_info['rpacket_t_skus'])), '*', '', '', 0,1000);
        $goodsClassModel = Model('goods_class');
        $t_info['goodsClassList'] = $goodsClassModel->getGoodsClassList(array('gc_id'=>array('in',$t_info['rpacket_t_classes'])), '*', '', '', 0,1000);
        foreach ($t_info['goodsClassList'] as $k => $v) {
            $menu = $goodsClassModel->getGoodsClassLineForTag($v['gc_id']);
            $t_info['goodsClassList'][$k]['menu'] = $menu['gc_tag_name'];
        }

        //查询最近修改的管理员
        $creator_info = Model('admin')->getOneAdmin($t_info['rpacket_t_adminid']);
        $t_info['rpacket_t_creator_name'] = $creator_info['admin_name'];
        $t_info['rpacket_t_price'] = intval($t_info['rpacket_t_price']);
        TPL::output('t_info',$t_info);
		Tpl::setDirquna('shop');/*网 店 运 维shop wwi.com*/
        Tpl::showpage('redpacket.templateinfo');
    }
    /**
     * 红包列表XML
     */
    public function rplist_xmlOp()
    {
        $t_id = intval($_GET['tid']);
        if ($t_id <= 0){
            echo Tpl::flexigridXML(array());
            exit;
        }
        $model_redpacket = Model('redpacket');
        $list = $model_redpacket->getRedpacketList(array('rpacket_t_id'=>$t_id), '*', 0, $_REQUEST['rp'], 'rpacket_id desc');
        $data = array();
        $data['now_page'] = $model_redpacket->shownowpage();
        $data['total_num'] = $model_redpacket->gettotalnum();
        foreach ($list as $val) {
            $i = array();
            $i['rpacket_code'] = $val['rpacket_code'];
            if($_GET['gtype'] == 'pwd'){
                $i['rpacket_pwd'] = $model_redpacket->get_rpt_pwd($val['rpacket_pwd2']);
            }
            foreach($this->redpacket_state_arr as $rpstate_k=>$rpstate_v){
                if($val['rpacket_state'] == $rpstate_v['sign']){
                    $i['rpacket_statetext'] = $rpstate_v['name'];
                }
            }
            $i['rpacket_owner_name'] = $val['rpacket_owner_name']?$val['rpacket_owner_name']:'未领取';
            $i['rpacket_active_datetext'] = $val['rpacket_owner_id']>0?date('Y-m-d H:i', $val['rpacket_active_date']):'';
            $data['list'][$val['rpacket_id']] = $i;
        }
        echo Tpl::flexigridXML($data);
        exit;
    }
    /**
     * 生成红包卡密 
     */
    public function rpbulidpwdOp(){
        $t_id = intval($_GET['tid']);
        if ($t_id <= 0){
            showDialog('红包生成失败','','error');
        }
        //生成卡密红包队列
        QueueClient::push('build_pwdRedpacket', $t_id);
        showDialog('生成红包卡密任务已建立，稍后将生成','reload','succ');
    }
    
    /**
     * 导出
     */
    public function export_step1Op(){
        $model_redpacket = Model('redpacket');
        $t_id = intval($_GET['tid']);
        //查询红包模板
        $rpt_info = $model_redpacket->getRptTemplateInfo(array('rpacket_t_id'=>$t_id));
        if (!$rpt_info){
            showDialog(L('param_error'),'index.php?act=redpacket&op=rptlist');
        }
        $where  = array();
        $where['rpacket_t_id'] = intval($_GET['tid']);
        if (preg_match('/^[\d,]+$/', $_GET['rid'])) {
            $_GET['rid'] = explode(',',trim($_GET['rid'],','));
            $where['rpacket_id'] = array('in',$_GET['rid']);
        }
        $order = 'rpacket_id desc';
        
        if (!is_numeric($_GET['curpage'])){
            $count = $model_redpacket->getRedpacketCount($where);
            $array = array();
            if ($count > self::EXPORT_SIZE ){//显示下载链接
                $page = ceil($count/self::EXPORT_SIZE);
                for ($i=1;$i<=$page;$i++){
                    $limit1 = ($i-1)*self::EXPORT_SIZE + 1;
                    $limit2 = $i*self::EXPORT_SIZE > $count ? $count : $i*self::EXPORT_SIZE;
                    $array[$i] = $limit1.' ~ '.$limit2 ;
                }
                Tpl::output('list',$array);
                Tpl::output('murl','index.php?act=redpacket&op=rptinfo&tid='.$t_id);
				Tpl::setDirquna('shop');/*网 店 运 维shop wwi.com*/
                Tpl::showpage('export.excel');
            }else{//如果数量小，直接下载
                $data = $model_redpacket->getRedpacketList($where,'*',self::EXPORT_SIZE,0,$order);
                $this->createExcel($data,$rpt_info);
            }
        }else{//下载
            $limit1 = ($_GET['curpage']-1) * self::EXPORT_SIZE;
            $limit2 = self::EXPORT_SIZE;
            $data = $model_redpacket->getRedpacketList($where,'*',"{$limit1},{$limit2}",0,$order);
            $this->createExcel($data,$rpt_info);
        }
    }
    
    /**
     * 生成excel
     *
     * @param array $data
     */
    private function createExcel($data = array(),$rpt_info){
        Language::read('export');
        import('libraries.excel');
        $excel_obj = new Excel();
        $excel_data = array();
        //设置样式
        $excel_obj->setStyle(array('id'=>'s_title','Font'=>array('FontName'=>'宋体','Size'=>'12','Bold'=>'1')));
        //header
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'红包编码');
        if ($rpt_info['rpacket_t_gettype_key'] == 'pwd'){
            $excel_data[0][] = array('styleid'=>'s_title','data'=>'卡密');
        }
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'领取方式');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'有效期');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'面额');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'每人限领');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'消费限额');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'会员级别');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'状态');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'使用状态');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'所属会员');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'领取时间');
        //data
        $model_redpacket = Model('redpacket');
        foreach ((array)$data as $k=>$v){
            $list = array();
            $list['rpacket_code'] = $v['rpacket_code'];
            if ($rpt_info['rpacket_t_gettype_key'] == 'pwd'){
                $list['rpacket_pwd'] = $model_redpacket->get_rpt_pwd($v['rpacket_pwd2']);
            }
            $list['rpacket_t_gettype_text'] = $rpt_info['rpacket_t_gettype_text'];
            $list['rpacket_expiry_date'] = @date('Y-m-d',$v['rpacket_start_date']).'~'.@date('Y-m-d',$v['rpacket_end_date']);
            $list['rpacket_price'] = $v['rpacket_price'];
            $list['rpacket_t_eachlimit'] = $rpt_info['rpacket_t_eachlimit']>0? $rpt_info['rpacket_t_eachlimit'] : '不限';
            $list['rpacket_limit'] = $v['rpacket_limit'];
            $list['rpacket_t_mgradelimittext'] = $rpt_info['rpacket_t_mgradelimittext'];
            $list['rpacket_t_state_text'] = $rpt_info['rpacket_t_state_text'];
            $list['rpacket_state_text'] = $v['rpacket_state_text'];
            $list['rpacket_owner_name'] = $v['rpacket_owner_name']?$v['rpacket_owner_name']:'未领取';
            $list['rpacket_active_date'] = $v['rpacket_owner_name']?@date('Y-m-d H:i:s',$v['rpacket_active_date']):0;
            $tmp = array();
            $tmp[] = array('data'=>$list['rpacket_code']);
            if ($rpt_info['rpacket_t_gettype_key'] == 'pwd'){
                $tmp[] = array('data'=>$list['rpacket_pwd']);
            }
            $tmp[] = array('data'=>$list['rpacket_t_gettype_text']);
            $tmp[] = array('data'=>$list['rpacket_expiry_date']);
            $tmp[] = array('data'=>$list['rpacket_price']);
            $tmp[] = array('data'=>$list['rpacket_t_eachlimit']);
            $tmp[] = array('data'=>$list['rpacket_limit']);
            $tmp[] = array('data'=>$list['rpacket_t_mgradelimittext']);
            $tmp[] = array('data'=>$list['rpacket_t_state_text']);
            $tmp[] = array('data'=>$list['rpacket_state_text']);
            $tmp[] = array('data'=>$list['rpacket_owner_name']);
            $tmp[] = array('data'=>$list['rpacket_active_date']);
            $excel_data[] = $tmp;
        }
        $excel_data = $excel_obj->charset($excel_data,CHARSET);
        $excel_obj->addArray($excel_data);
        $excel_obj->addWorksheet($excel_obj->charset('红包',CHARSET));
        $excel_obj->generateXML($rpt_info['rpacket_t_title'].$_GET['curpage'].'-'.date('Y-m-d-H',time()));
    }
    
    /**
     * 后台批量发红包
     */
    public function giveOp() {
        
        if (!empty($_POST['members_id']) && !empty($_POST['tid'])) {
            $use_member_id = empty($_POST['use_member_id']) ? 0 : 1;
            $members_arr = array(
                191815,209585,
            );
            $members_arr = preg_split("/[\r\n]+/", $_POST['members_id']);
            $t_id = intval($_POST['tid']);
            
            if($t_id <= 0){
                showDialog('红包信息错误','','error');
            }
            //验证是否可领取红包
            $model_redpacket = Model('redpacket');
            $model_member = Model('member');
            
            $where = array();
            $where['rpacket_t_id']          = $t_id;
            $where['rpacket_t_state']       = '1';//优先 红包才能发
            //$where['rpacket_t_start_date']  = array('elt',time());
            $where['rpacket_t_end_date']    = array('egt',time());
            $template_info = $model_redpacket->getRptTemplateInfo($where);
            //         $data = $model_redpacket->getCanChangeTemplateInfo($t_id, intval($_SESSION['member_id']));
            
            foreach ($members_arr as $index => $member_id) {
                if ($use_member_id) {
                    $_member = $model_member->where(array('member_id'=>$member_id))->field('member_name,member_id')->find();
                    $member_id = $_member['member_id'];
                    $member_name = $_member['member_name'];
                } else {
                    $_member = $model_member->where(array('member_name'=>$member_id))->field('member_name,member_id')->find();
                    $member_id = $_member['member_id'];
                    $member_name = $_member['member_name'];
                }
            
                try {
                    $model_redpacket->beginTransaction();
                    //添加红包信息
                    $data = $model_redpacket->exchangeRedpacket($template_info, $member_id, $member_name);
                    if ($data['state'] == false) {
                    }
                    $model_redpacket->commit();
            
                } catch (Exception $e) {
                    $model_redpacket->rollback();
                }
                v("{$index}: {$member_id} :{$member_name} " . var_export($data['state'], true) . "\r\n", 0);
            }
            
        } else {
            Tpl::setDirquna('shop');/*网 店 运 维shop wwi.com*/
            Tpl::showpage('redpacket.give');
        }
        
    }

    /**
     * 释放已取消订单的红包
     */
    public function releaseCanceledRedpacketOp() {
        $model_order = Model('order');
        $model_redpacket = Model('redpacket');
        $list = $model_order->table('orders,redpacket')->join('inner')->on('orders.pay_sn = redpacket.rpacket_order_id')->where("orders.order_state='0'")->limit(1000)->select();
        
        if (!empty($_GET['show'])) {
            v($list);
        }
        foreach ($list as $order_info) {
            $res = $model_redpacket->releaseRedpacket($order_info['pay_sn'], $order_info['buyer_id']);
            if ($res) {
            	v($order_info['pay_sn'], 0);
            }
        }
    }
}
