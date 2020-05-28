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
        array('url'=>'act=goods&op=goods','text'=>'上架商品'),
        array('url'=>'act=goods&op=lockup_list','text'=>'下架商品'),
        array('url'=>'act=goods&op=b2c_import','text'=>'b2c导入'),
    );
    const EXPORT_SIZE = 5000;
    public function __construct() {
        parent::__construct ();
        Language::read('goods_class');
    }

    public function indexOp() {
        $this->goodsOp();
    }

    /**
     * 商品管理
     */
    public function goodsOp() {
        $gc_list = Model('goods_class')->getGoodsClassList(array('gc_parent_id' => 0));

        $supplier_list = Model('b2b_supplier')->getSupplierList($condition = array(), $field = 'supplier_id,company_name');


        Tpl::output('supplier_list', $supplier_list);
        Tpl::output('gc_list', $gc_list);
        Tpl::output('top_link',$this->sublink($this->links,'goods'));
        Tpl::setDirquna('b2b');
        Tpl::showpage('goods.index');
    }

    /**
     * 违规下架商品管理
     */
    public function lockup_listOp() {
        Tpl::output('type', 'lockup');
        Tpl::output('top_link',$this->sublink($this->links,'lockup_list'));
        //网 店 运 维shop wwi.com
        Tpl::setDirquna('b2b');
        Tpl::showpage('goods.index');
    }

    //b2c 导入
    public function b2c_importOp(){
        $gc_list = Model('goods_class')->getGoodsClassList(array('gc_parent_id' => 0));
        Tpl::output('gc_list', $gc_list);
        Tpl::output('top_link',$this->sublink($this->links,'b2c_import'));
        Tpl::setDirquna('b2b');
        Tpl::showpage('goods.b2c');
    }

    /**
     * 输出b2c XML数据
     */
    public function get_xml_b2cOp() {
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
//        $goods_state = $this->getGoodsState();

        // 审核状态
//        $verify_state = $this->getGoodsVerify();

        $data = array();
        $data['now_page'] = $model_goods->shownowpage();
        $data['total_num'] = $model_goods->gettotalnum();

        foreach ($goods_list as $value) {
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
//                    $operation .= "<a class='btn red' href='javascript:void(0);' onclick=\"fg_import('" . $value['goods_commonid'] . "')\"><i class='fa fa-ban'></i>导入</a>";
                    $operation .= "<a class='btn red' href='" . urlAdminB2b('goods', 'goods_edit', array('b2c_goodsid' => $value['goods_commonid'])) . "' target=\"_blank\"><i class='fa fa-ban'></i>导入</a>";
                    break;
            }
            $operation .= "</ul>";
            $param['operation'] = $operation;
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
     * 上传图片
     */
    public function upload_picOp() {
        $data = array();
        if (!empty($_FILES['fileupload']['name'])) {//上传图片
            $fprefix = 'b2b_goods';
            $upload = new UploadFile();
            $upload->set('default_dir',ATTACH_B2B_GOODS);
            $upload->set('fprefix',$fprefix);
            $upload->upfile('fileupload');
            $model_upload = Model('upload');
            $file_name = $upload->file_name;
            $insert_array = array();
            $insert_array['file_name'] = $file_name;
            $insert_array['file_size'] = $_FILES['fileupload']['size'];
            $insert_array['upload_time'] = time();
            $insert_array['item_id'] = intval($_GET['item_id']);
            $insert_array['upload_type'] = '7';
            $result = $model_upload->add($insert_array);
            if ($result) {
                $data['file_id'] = $result;
                $data['file_name'] = $file_name;
            }
        }
        echo json_encode($data);exit;
    }

    /**
     * 删除图片
     */
    public function del_picOp() {
//        $condition = array();
//        $condition['upload_id'] = intval($_GET['file_id']);
        $model_help = Model('b2b_goods_common');
        $state = $model_help->delGoodsImageArray($_GET['file_id']);
        if ($state) {
            echo 'true';exit;
        } else {
            echo 'false';exit;
        }
    }

    //json输出商品分类
    public function josn_classOp() {
        /**
         * 实例化商品分类模型
         */
        $model_class        = Model('b2b_category');
        $goods_class        = $model_class->getGoodsClassListByParentId(intval($_GET['gc_id']));
        $array              = array();
        if(is_array($goods_class) and count($goods_class)>0) {
            foreach ($goods_class as $val) {
                $array[$val['bc_id']] = array('gc_id'=>$val['bc_id'],'gc_name'=>htmlspecialchars($val['bc_name']),'gc_parent_id'=>$val['gc_pid'],'commis_rate'=>0,'gc_sort'=>$val['bc_sort']);
            }
        }
        /**
         * 转码
         */
        if (strtoupper(CHARSET) == 'GBK'){
            $array = Language::getUTF8(array_values($array));//网站GBK使用编码时,转换为UTF-8,防止json输出汉字问题
        } else {
            $array = array_values($array);
        }
        echo $_GET['callback'].'('.json_encode($array).')';
    }

    public function goods_editOp(){
        $lang   = Language::getLangContent();
        $model_goods = Model('b2b_goods_common');

        $b2c_goodsid = intval($_GET['b2c_goodsid']);
        if(empty($b2c_goodsid)){
            $goods_detail = $model_goods->getGoodsInfo('goods_commonid = '.$_GET['commonid'],'b2c_goodsid');
            $b2c_goodsid = $goods_detail['b2c_goodsid'];
        }
        Tpl::output('b2c_goodsid',$b2c_goodsid);

        $model_b2c_goods = Model('goods');
        $goodscommon_info = $model_b2c_goods->getGoodsCommonInfoByID($b2c_goodsid);

        //先生成预发布商品
        $commonid = intval($_GET['commonid']);
        $condition = array();
        $condition['goods_commonid'] = $commonid;
        $good_info =  $model_goods->getGoodsInfo($condition,'goods_commonid,goods_name,goods_body,gc_id,goods_state,supplier_id');

        if($b2c_goodsid){
            $good_info['goods_name'] = $goodscommon_info['goods_name'];
            $good_info['goods_body'] = $goodscommon_info['goods_body'];
        }
        Tpl::output('good_info',$good_info);

        $model_category = Model('b2b_category');
        $gccache_arr = $model_category->getGoodsclassCache($good_info['gc_id'],3);

        Tpl::output('gc_json',json_encode($gccache_arr['showclass']));
        Tpl::output('gc_choose_json',json_encode($gccache_arr['choose_gcid']));

        if (chksubmit()){
            $obj_validate = new Validate();
            $validata_array = array(
                array("input"=>$_POST["goods_name"], "require"=>"true", "message"=>$lang['goods_class_add_name_null']),
            );
            if(!empty($_POST['at_value'])){
                foreach ($_POST['at_value'] as $k=>$v){
                    if(!empty($v)){
                        $validata_array[] = array("input"=>$_POST["at_value"][$k]['calculate'], 'validator'=>'chinese',"require"=>"false", "message"=>'计量单位不合法');
                        $validata_array[] = array("input"=>$_POST["at_value"][$k]['price'], 'validator'=>'double',"require"=>"false", "message"=>'单价不合法');
                        $validata_array[] = array("input"=>$_POST["at_value"][$k]['storage'], 'validator'=>'Number',"require"=>"false", "message"=>'库存不合法');
                        $validata_array[] = array("input"=>$_POST["at_value"][$k]['cost'], 'validator'=>'double',"require"=>"false", "message"=>'成本不合法');
                        $validata_array[] = array("input"=>$_POST["at_value"][$k]['tax_input'], 'validator'=>'double',"require"=>"false", "message"=>'进项税率不合法');
                        $validata_array[] = array("input"=>$_POST["at_value"][$k]['tax_output'], 'validator'=>'double',"require"=>"false", "message"=>'出项税率不合法');
                    }
                }
            }
            $obj_validate->validateparam = $validata_array;
            $error = $obj_validate->validate();
            !empty($error) and showMessage($error);
            $post_data = $_POST;
            $res =  $model_goods->setGoodsInfo($commonid,'goods_edit',$post_data,$this->admin_info);
            $model_goods->setMainImage($post_data);
                if ($res['state']){
                    $url = array(
                        array(
                            'url'=>'index.php?act=goods&op=goods',
                            'msg'=>'返回商品列表',
                        ),
                        array(
                            'url'=>'index.php?act=goods&op=goods',
                            'msg'=>'继续新增商品',
                        ),
                    );
                    $this->log(L('nc_add,goods_class_index_class').'['.$_POST['goods_name'].']',1);
                    showMessage($lang['nc_common_save_succ'],$url);
                }else {
                    $this->log(L('nc_add,goods_class_index_class').'['.$_POST['goods_name'].']',0);
                    showMessage($res['msg']);
                }

        }

        $value_array[] = array('sp_value_id' => '0', 'sp_value_name' => '无颜色');
//        Tpl::output('value_array', $value_array);
        // 一级分类列表
        $bc_list = Model('b2b_category')->getGoodsClassListByParentId(0);
        Tpl::output('bc_list', $bc_list);

        //供应商列表
        $supplier_list = Model('b2b_supplier')->getSupplierList($condition = array(), $field = 'supplier_id,company_name');
        Tpl::output('supplier_list', $supplier_list);



        $condition = array();
        $condition['item_id'] = $commonid;
        $pic_list = $model_goods->getGoodsPicList($condition);
        Tpl::output('pic_list',$pic_list);

        $condition = array();
        $condition['goods_commonid'] = $commonid;
        $sku_list = $model_goods->getGoodsSkuList($condition);
        Tpl::output('sku_list',$sku_list);

        Tpl::setDirquna('b2b');
        Tpl::showpage('goods.edit');
    }

    public function set_main_imgOp(){
        $file_id = $_GET['file_id'];
        $model_goods = Model('b2b_goods_common');
        $model_goods->setMainImage($file_id);
    }

    public function goods_addOp(){
        $lang   = Language::getLangContent();
        $model_goods = Model('b2b_goods_common');


        if (chksubmit()){
            $obj_validate = new Validate();
            $validata_array = array(
                array("input"=>$_POST["goods_name"], "require"=>"true", "message"=>$lang['goods_class_add_name_null']),
            );
            if(!empty($_POST['at_value'])){
                foreach ($_POST['at_value'] as $k=>$v){
                    if(!empty($v)){
                        $validata_array[] = array("input"=>$_POST["at_value"][$k]['calculate'], 'validator'=>'chinese',"require"=>"false", "message"=>'计量单位不合法');
                        $validata_array[] = array("input"=>$_POST["at_value"][$k]['price'], 'validator'=>'double',"require"=>"false", "message"=>'单价不合法');
                        $validata_array[] = array("input"=>$_POST["at_value"][$k]['storage'], 'validator'=>'Number',"require"=>"false", "message"=>'库存不合法');
                        $validata_array[] = array("input"=>$_POST["at_value"][$k]['cost'], 'validator'=>'double',"require"=>"false", "message"=>'成本不合法');
                        $validata_array[] = array("input"=>$_POST["at_value"][$k]['tax_input'], 'validator'=>'double',"require"=>"false", "message"=>'进项税率不合法');
                        $validata_array[] = array("input"=>$_POST["at_value"][$k]['tax_output'], 'validator'=>'double',"require"=>"false", "message"=>'出项税率不合法');
                    }
                }
            }
            $obj_validate->validateparam = $validata_array;
            $error = $obj_validate->validate();
            !empty($error) and showMessage($error);
            $post_data = $_POST;
            $res =  $model_goods->setGoodsInfo(0,'goods_add',$post_data,$this->admin_info['id']);
            $model_goods->setMainImage($post_data);
                if ($res){
                    $url = array(
                        array(
                            'url'=>'index.php?act=goods&op=goods',
                            'msg'=>'返回商品列表',
                        ),
                        array(
                            'url'=>'index.php?act=goods&op=goods_add',
                            'msg'=>'继续新增商品',
                        )
                    );
                    $this->log(L('nc_add,goods_class_index_class').'['.$_POST['goods_name'].']',1);
                    showMessage($lang['nc_common_save_succ'],$url);
                }else {
                    $this->log(L('nc_add,goods_class_index_class').'['.$_POST['goods_name'].']',0);
                    showMessage($lang['nc_common_save_fail']);
                }
            }

        // 一级分类列表
        $bc_list = Model('b2b_category')->getGoodsClassListByParentId(0);

        Tpl::output('bc_list', $bc_list);

//        $condition = array();
//        $condition['item_id'] = '0';
//        $pic_list = $model_goods->getGoodsPicList($condition);
//        Tpl::output('pic_list',$pic_list);

        Tpl::setDirquna('b2b');
        Tpl::showpage('goods.add');
    }

    public function get_xmlOp(){
        $model_goods = Model('b2b_goods_common');

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
        $param_order = array('goods_commonid');
        if (in_array($_POST['sortname'], $param_order) && in_array($_POST['sortorder'], array('asc', 'desc'))) {
            $order = $_POST['sortname'] . ' ' . $_POST['sortorder'];
        }
        $page = $_POST['rp'];

        switch ($_GET['type']) {
            // 下架商品
            case 'lockup':
                $condition['goods_state'] = 0;
                $goods_list = $model_goods->getGoodsCommonLockUpList($condition, '*', $page, $order);

                break;
            // 上架商品
            default:
                $condition['goods_state'] = 1;
                $goods_list = $model_goods->getGoodsCommonList($condition, '*', $page, $order);
                break;
        }


        $member_ids = array_column($goods_list,'memberid');
        $supplier_ids = array_column($goods_list,'supplier_id');

        $condition = array();
        $condition['member_id'] = array('in',$member_ids);
        $member_list = Model('member')->getMemberList($condition,'member_id,member_name');
        $member_list = array_under_reset($member_list,'member_id');

        $condition = array();
        $condition['supplier_id'] = array('in',$supplier_ids);
        $supplier_list = Model('b2b_supplier')->getSupplierList($condition,'supplier_id,company_name');
        $supplier_list = array_under_reset($supplier_list,'supplier_id');

        $data = array();
        $data['now_page'] = $model_goods->shownowpage();
        $data['total_num'] = $model_goods->gettotalnum();
        foreach($goods_list as $value){
            $operation = '';
            //正常状态
            if($value['goods_state'] == 1){
                $operation .= "<a class='btn red' href='javascript:void(0);' onclick=\"fg_lonkup_off('" . $value['goods_commonid'] . "')\"><i class='fa fa-ban'></i>下架</a>";
            } else {
                //下架状态
                $operation .= "<a class='btn red' href='javascript:void(0);' onclick=\"fg_lonkup_on('" . $value['goods_commonid'] . "')\"><i class='fa fa-ban'></i>上架</a>";
            }

            $operation .= "<span class='btn'><em><i class='fa fa-cog'></i>设置 <i class='arrow'></i></em><ul>";
            $operation .= "<li><a href='javascript:void(0)' onclick=\"fg_sku('" . $value['goods_commonid'] . "')\">查看商品SKU</a></li>";
            $operation .= "<li><a href='" . urlAdminB2b('goods', 'goods_edit', array('commonid' => $value['goods_commonid'])) . "' >编辑商品</a></li>";

            $operation .= "</ul>";
            $param['operation'] = $operation;
            $param['goods_commonid'] = $value['goods_commonid'];
            $param['goods_name'] = $value['goods_name'];
            $param['supplier_name'] = $supplier_list[$value['supplier_id']]['company_name'];
            $param['gc_name'] = $value['gc_name'];
            $param['b2c_goodsid'] = $value['b2c_goodsid'];
            $param['memberid'] = $member_list[$value['memberid']]['member_name'] ;
            $param['addtime'] = date('Y-m-d', $value['addtime']);
            $data['list'][$value['goods_commonid']] = $param;
        }
        echo Tpl::flexigridXML($data);exit();
    }

    //下架
    function  goods_lockup_offOp(){
        $condition = array();
        $condition['goods_commonid']=intval($_GET['ids']);
        $result = Model('b2b_goods_common')->editProducesOffline($condition);

        if(!$result){
            exit(json_encode(array('state'=>'0','msg'=>'下架失败')));
        }
        exit(json_encode(array('state'=>'1','msg'=>'下架成功')));
    }

    //上架
    function  goods_lockup_onOp(){
        $condition = array();
        $condition['goods_commonid']=intval($_GET['ids']);
        $result = Model('b2b_goods_common')->editProducesOnline($condition);

        if(!$result){
            exit(json_encode(array('state'=>'0','msg'=>'上架失败')));
        }
        exit(json_encode(array('state'=>'1','msg'=>'上架成功')));
    }



    /**
     * 删除商品
     */
    public function goods_delOp() {
        $common_id = intval($_GET['ids']);
        if ($common_id <= 0) {
            exit(json_encode(array('state'=>false,'msg'=>'删除失败')));
        }
        Model('b2b_goods_common')->delGoodsAll(array('goods_commonid' => $common_id));
        $this->log('删除商品[ID:'.$common_id.']',1);
        exit(json_encode(array('state'=>true,'msg'=>'删除成功')));
    }



    /**
     * ajax获取商品列表
     */
    public function get_goods_sku_listOp() {
        $commonid = $_GET['commonid'];
        if ($commonid <= 0) {
        showDialog('参数错误1', '', '', 'CUR_DIALOG.close();');
    }
$model_goods = Model('b2b_goods_common');

$goods_list = $model_goods->getGoodsList(array('goods_commonid' => $commonid));
if (empty($goods_list)) {
showDialog('参数错误2', '', '', 'CUR_DIALOG.close();');
}

        Tpl::output('goods_list', $goods_list);
        //网 店 运 维shop wwi.com
        Tpl::setDirquna('b2b');
        Tpl::showpage('goods.sku_list', 'null_layout');
    }

}
