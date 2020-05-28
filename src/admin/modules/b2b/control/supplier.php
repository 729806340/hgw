<?php
/**
 * 供应商管理管理
 *
 *
 *
 *
 * @汉购网提供技术支持 授权请购买shopnc授权
 * @license    http://www.hangowa.com
 * @link       交流群号：
 */



defined('ByShopWWI') or exit('Access Invalid!');
class supplierControl extends SystemControl{
    private $supplier_id;

    private $links = array(
        array('url'=>'act=supplier&op=my_goods_list','text'=>'现有商品'),
        array('url'=>'act=supplier&op=all_goods_list','text'=>'商品导入'),
    );
    private $show_type = array(
        1 => '颜色',
        2 => 'SPU'
    );
    public function __construct(){
        parent::__construct();
        Language::read('goods_class');
    }

    public function indexOp() {
        $this->supplierOp();
    }

    public function supplierOp(){
        $supplier_class = Model('b2b_supplier');

        $supplierList = $supplier_class->getSupplierList();
//        v($supplierList);
        Tpl::output('class_list',$supplierList);
        Tpl::setDirquna('b2b');
        Tpl::showpage('supplier.index');
    }

    public function supplier_editOp(){
        $lang   = Language::getLangContent();
        $supplier_id = $_GET['supplier_id'];
        $condition = array();
        $condition['supplier_id'] = $supplier_id;
        $supplier_info = Model('b2b_supplier')->getSupplierInfo($condition);

        if(chksubmit()){
            $data = array();
            $data['company_name'] = $_POST['company_name'];
            $data['address'] = $_POST['address'];
            $data['manage_type'] = $_POST['manage_type'];
            $data['edit_sap'] = 0;
            $res = Model('b2b_supplier')->editSupplier($supplier_id,$data);
            if ($res){
                $url = array(
                    array(
                        'url'=>'index.php?act=supplier&op=index',
                        'msg'=>'返回供应商列表',
                    )
                );
                $this->log(L('nc_add,goods_class_index_class').'['.$_POST['goods_name'].']',1);
                showMessage($lang['nc_common_save_succ'],$url);
            }else {
                $this->log(L('nc_add,goods_class_index_class').'['.$_POST['goods_name'].']',0);
                showMessage($lang['nc_common_save_fail']);
            }
        }

        Tpl::output('supplier_info',$supplier_info);
        Tpl::setDirquna('b2b');
        Tpl::showpage('supplier.edit');
    }

    public function supplier_addOp(){
        $lang   = Language::getLangContent();
        if(chksubmit()){
            $data = array();
            $data['company_name'] = $_POST['company_name'];
            $data['address'] = $_POST['address'];
            $data['manage_type'] = $_POST['manage_type'];
            $res = Model('b2b_supplier')->addSupplier($data);
            if ($res){
                $url = array(
                    array(
                        'url'=>'index.php?act=supplier&op=index',
                        'msg'=>'返回供应商列表',
                    ),
                    array(
                        'url'=>'index.php?act=supplier&op=supplier_add',
                        'msg'=>'继续新增供应商',
                    ),
                );
                $this->log(L('nc_add,goods_class_index_class').'['.$_POST['goods_name'].']',1);
                showMessage($lang['nc_common_save_succ'],$url);
            }else {
                $this->log(L('nc_add,goods_class_index_class').'['.$_POST['goods_name'].']',0);
                showMessage($lang['nc_common_save_fail']);
            }
        }
        Tpl::setDirquna('b2b');
        Tpl::showpage('supplier.add');
    }

    public function my_goods_listOp(){
        $this->supplier_id = $_GET['supplier_id'];
        $condition = array();
        $condition['supplier_id'] = $this->supplier_id;
        $goods_list = Model('b2b_goods_common')->getGoodsCommonList($condition);

//        Tpl::output('goods_list',$goods_list);

        Tpl::output('top_link',$this->sublink($this->links,'my_goods_list'));
        Tpl::output('supplier_id',$this->supplier_id);
        Tpl::setDirquna('b2b');
        Tpl::showpage('supplier_goods.index');
    }

    public function all_goods_listOp(){

//        $condition = array();
//        $goods_list = Model('b2b_goods_common')->getGoodsCommonList($condition);

//        Tpl::output('goods_list',$goods_list);

        Tpl::output('supplier_id',$_GET['supplier_id']);
        Tpl::output('top_link',$this->sublink($this->links,'all_goods_list'));
        Tpl::setDirquna('b2b');
        Tpl::showpage('supplier_goods_all.index');
    }

    public function goods_bindOp(){
        $goods_commonid = intval($_GET['ids']);
        $supplier_id = intval($_GET['supplier_id']);

        $result = Model('b2b_goods_common')->bindGood($goods_commonid,$supplier_id);
        if(!$result){
            exit(json_encode(array('state'=>'0','msg'=>'绑定失败')));
        }
        exit(json_encode(array('state'=>'1','msg'=>'绑定成功')));
    }

    public function goods_unbindOp(){
        $goods_commonid = intval($_GET['ids']);
        $result = Model('b2b_goods_common')->unbindGood($goods_commonid);
        if(!$result){
            exit(json_encode(array('state'=>'0','msg'=>'解绑失败')));
        }
        exit(json_encode(array('state'=>'1','msg'=>'解绑成功')));
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
        if (intval($_GET['sp_id']) > 0) {
            $condition['supplier_id'] = intval($_GET['sp_id']);
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
//        $order = $_POST['sortname'] . ' ' . $_POST['sortorder'];
        $param_order = array('goods_commonid');
        if (in_array($_POST['sortname'], $param_order) && in_array($_POST['sortorder'], array('asc', 'desc'))) {
            $order = $_POST['sortname'] . ' ' . $_POST['sortorder'];
        }
        $page = $_POST['rp'];


        if($_GET['type'] == 'my'){
            $condition['supplier_id'] = $_GET['supplier_id'];
        }

        $goods_list = $model_goods->getGoodsCommonList($condition, '*', $page, $order);

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
            if($_GET['type'] == 'my'){
                $operation .= "<a class='btn red' href='javascript:void(0);' onclick=\"fg_unbind('" . $value['goods_commonid'] . "')\"><i class='fa fa-ban'></i>解绑</a>";
            } else {
                $operation .= "<a class='btn red' href='javascript:void(0);' onclick=\"fg_bind('" . $value['goods_commonid'] . "','" . $_GET['supplier_id'] . "')\"><i class='fa fa-ban'></i>绑定</a>";
            }

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


    


    /**
     * ajax操作
     */
    public function ajaxOp(){
        switch ($_GET['branch']){
            /**
             * 更新分类
             */
            case 'gc_name':
                $model_class = Model('goods_class');
                $class_array = $model_class->getGoodsClassInfoById(intval($_GET['id']));

                $condition['gc_name'] = trim($_GET['value']);
                $condition['gc_parent_id'] = $class_array['gc_parent_id'];
                $condition['gc_id'] = array('neq', intval($_GET['id']));
                $class_list = $model_class->getGoodsClassList($condition);
                if (empty($class_list)){
                    $where = array('gc_id' => intval($_GET['id']));
                    $update_array = array();
                    $update_array['gc_name'] = trim($_GET['value']);
                    $model_class->editGoodsClass($update_array, $where);
                    $return = true;
                }else {
                    $return = false;
                }
                exit(json_encode(array('result'=>$return)));
                break;
            /**
             * 分类 排序 显示 设置
             */
            case 'gc_sort':
                $model_class = Model('goods_class');
                $where = array('gc_id' => intval($_GET['id']));
                $update_array = array();
                $update_array['gc_sort'] = $_GET['value'];
                $model_class->editGoodsClass($update_array, $where);
                $return = 'true';
                exit(json_encode(array('result'=>$return)));
                break;
            /**
             * 添加、修改操作中 检测类别名称是否有重复
             */
            case 'check_class_name':
                $model_class = Model('b2b_category');
                $condition['bc_name'] = trim($_GET['bc_name']);
                $condition['bc_pid'] = intval($_GET['bc_pid']);
                $condition['bc_id'] = array('neq', intval($_GET['bc_id']));
                $class_list = $model_class->getGoodsClassList($condition);
                if (empty($class_list)){
                    echo 'true';exit;
                }else {
                    echo 'false';exit;
                }
                break;
            case 'check_tax':
            	$tax = trim($_GET['tax']);
            	$valid = array(
            			'0.000', '6.000', '13.000', '17.000', '10.000', '16.000'
            	);
            	if( in_array($tax, $valid) ) {
            		echo 'true';exit;
            	}else {
                    echo 'false';exit;
                }
                break;
        }
    }
    

}
