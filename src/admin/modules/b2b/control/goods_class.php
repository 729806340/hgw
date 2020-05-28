<?php
/**
 * 商品分类管理
 *
 *
 *
 *
 * @汉购网提供技术支持 授权请购买shopnc授权
 * @license    http://www.hangowa.com
 * @link       交流群号：
 */



defined('ByShopWWI') or exit('Access Invalid!');
class goods_classControl extends SystemControl{
    private $links = array(
        array('url'=>'act=goods_class&op=goods_class','lang'=>'nc_manage'),
        array('url'=>'act=goods_class&op=goods_class_import','lang'=>'goods_class_index_import'),
        array('url'=>'act=goods_class&op=tag','lang'=>'goods_class_index_tag')
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
        $this->goods_classOp();
    }

    public function goods_classOp(){
        $model_class = Model('b2b_category');

        //父ID
        $parent_id = 0;
        $bc_id = $_GET['bc_id']?intval($_GET['bc_id']):0;

        //列表
        $tmp_list = $model_class->getTreeClassList(3);
        if (is_array($tmp_list)){
            foreach ($tmp_list as $k => $v){
                if ($v['bc_pid'] == $bc_id){
                    //判断是否有子类
                    if ($tmp_list[$k+1]['deep'] > $v['deep']){
                        $v['have_child'] = 1;
                    }
                    $class_list[] = $v;
                }
                if ($v['bc_id'] == $bc_id) {
                    $parent_id = $v['bc_pid'];
                    $parent_name = $v['bc_name'];
                }
            }
        }


        if ($bc_id > 0){
            if ($parent_id == 0) {
                $title = '"' . $parent_name . '"的下级列表(二级)';
                $deep = 2;
            } else {
                foreach ($tmp_list as $v) {
                    if ($v['bc_id'] == $parent_id) {
                        $grandparents_name = $v['bc_name'];
                    }
                }
                $title = '"' . $grandparents_name . ' - ' . $parent_name . '"的下级列表(三级)';
                $deep = 3;
            }
            Tpl::output('deep', $deep);
            Tpl::output('title', $title);
            Tpl::output('parent_id', $parent_id);
            Tpl::output('bc_id', $bc_id);
            Tpl::output('class_list',$class_list);

            Tpl::setDirquna('b2b');//网 店 运 维shop wwi.com
            Tpl::showpage('goods_class.child_list');
        } else {
            Tpl::output('class_list',$class_list);

            Tpl::setDirquna('b2b');//网 店 运 维shop wwi.com
            Tpl::showpage('goods_class.index');
        }
    }

    public function goods_class_editOp(){
        $lang   = Language::getLangContent();
        $model_class = Model('b2b_category');

        $condition = array();
        $condition['bc_id'] = intval($_GET['bc_id']);
        $class_info = $model_class->where($condition)->find();

        if(chksubmit()){

            $obj_validate = new Validate();
            $obj_validate->validateparam = array(
                array("input"=>$_POST["bc_name"], "require"=>"true", "message"=>$lang['goods_class_add_name_null']),
                array("input"=>$_POST["bc_sort"], "require"=>"true", 'validator'=>'Number', "message"=>$lang['goods_class_add_sort_int']),
                //array("input"=>$_POST["commis_rate"], "require"=>"true", 'validator'=>'Number', "message"=>'分佣比例必须为整数'),
            );
            $error = $obj_validate->validate();
            if ($error != ''){
                showMessage($error);
            }

            $where = array('bc_id' => intval($_POST['bc_id']));
            $update_array = array();
            $update_array['bc_name']        = $_POST['bc_name'];
            $update_array['bc_sort']        = intval($_POST['bc_sort']);
            $update_array['commis_rate']        = floatval($_POST['commis_rate']);

            $result = $model_class->editGoodsClass($update_array,$where);


            if($_POST['t_commis_rate'] == '1'){
                $gc_id_list = $model_class->getChildClass($_POST['bc_id']);
                $gc_ids = array();
                if (is_array($gc_id_list) && !empty($gc_id_list)) {
                    foreach ($gc_id_list as $val){
                        $gc_ids[] = $val['bc_id'];
                    }
                }
                $where = array();
                $where['bc_id'] = array('in', $gc_ids);
                $update = array();
                // 更新该分类下子分类的所有分佣比例
                if ($_POST['t_commis_rate'] == '1') {
                    $update['commis_rate']  = $update_array['commis_rate'];
                }
                $update_commis_rate = $model_class->editGoodsClass($update,$where);

                if (!$update_commis_rate){
                    $this->log(L('nc_edit,goods_class_index_class').'['.$_POST['gc_name'].']',0);
                    showMessage('税率改变失败');
                }
            }

            if (!$result){
                $this->log(L('nc_edit,goods_class_index_class').'['.$_POST['gc_name'].']',0);
                showMessage($lang['goods_class_batch_edit_fail']);
            }

            $url = array(
                array(
                    'url'=>'index.php?act=goods_class&op=goods_class_edit&bc_id='.intval($_POST['bc_id']),
                    'msg'=>$lang['goods_class_batch_edit_again'],
                ),
                array(
                    'url'=>'index.php?act=goods_class&op=goods_class',
                    'msg'=>$lang['goods_class_add_back_to_list'],
                )
            );
            $this->log(L('nc_edit,goods_class_index_class').'['.$_POST['gc_name'].']',1);
            showMessage($lang['goods_class_batch_edit_ok'],$url,'html','succ',1,5000);

        }

        Tpl::output('class_info',$class_info);
        Tpl::setDirquna('b2b');
        Tpl::showpage('goods_class.edit');
    }


    public function goods_class_addOp(){
        $lang   = Language::getLangContent();
        $model_class = Model('b2b_category');
        if (chksubmit()){
            $obj_validate = new Validate();
            $obj_validate->validateparam = array(
                array("input"=>$_POST["bc_name"], "require"=>"true", "message"=>$lang['goods_class_add_name_null']),
                array("input"=>$_POST["bc_sort"], "require"=>"true", 'validator'=>'Number', "message"=>$lang['goods_class_add_sort_int']),
            );
            $error = $obj_validate->validate();
            if ($error != ''){
                showMessage($error);
            }else {
                $insert_array = array();
                $insert_array['bc_name']        = $_POST['bc_name'];
                if($_GET['bc_pid']){
                    $insert_array['bc_pid']        = $_GET['bc_pid'];
                }
                $insert_array['bc_sort']        = intval($_POST['bc_sort']);
                $insert_array['commis_rate']        = intval($_POST['commis_rate']);
                $result = $model_class->addGoodsClass($insert_array);
                if ($result){
                    $url = array(
                        array(
                            'url'=>'index.php?act=goods_class&op=goods_class_add&bc_pid='.$_GET['bc_pid'],
                            'msg'=>$lang['goods_class_add_again'],
                        ),
                        array(
                            'url'=>'index.php?act=goods_class&op=goods_class&bc_id='.$_GET['bc_pid'],
                            'msg'=>$lang['goods_class_add_back_to_list'],
                        )
                    );
                    $this->log(L('nc_add,goods_class_index_class').'['.$_POST['gc_name'].']',1);
                    showMessage($lang['nc_common_save_succ'],$url);
                }else {
                    $this->log(L('nc_add,goods_class_index_class').'['.$_POST['gc_name'].']',0);
                    showMessage($lang['nc_common_save_fail']);
                }
            }
        }
        Tpl::setDirquna('b2b');//网 店 运 维shop wwi.com
        Tpl::showpage('goods_class.add');
    }




    /**
     * 删除分类
     */
    public function goods_class_delOp(){
        if ($_GET['id'] != ''){
            //删除分类
            Model('b2b_category')->delGoodsClassByGcIdString($_GET['id']);
            $this->log(L('nc_delete,goods_class_index_class') . '[ID:' . $_GET['id'] . ']',1);
            exit(json_encode(array('state'=>true,'msg'=>'删除成功')));
        }else {
            exit(json_encode(array('state'=>false,'msg'=>'删除失败')));
        }
    }



    /**
     * 输出XML数据
     */
    public function get_xmlOp() {
        $model_class_tag = Model('goods_class_tag');
        // 设置页码参数名称
        $condition = array();
        if ($_POST['query'] != '') {
            $condition[$_POST['qtype']] = array('like', '%' . $_POST['query'] . '%');
        }
        $order = '';
        $param = array('gc_tag_id', 'gc_tag_name', 'gc_tag_value', 'gc_id_1', 'gc_id_2', 'gc_id_3');
        if (in_array($_POST['sortname'], $param) && in_array($_POST['sortorder'], array('asc', 'desc'))) {
            $order = $_POST['sortname'] . ' ' . $_POST['sortorder'];
        }

        //店铺列表
        $tag_list = $model_class_tag->getTagList($condition, $_POST['rp'], '*', $order);

        $data = array();
        $data['now_page'] = $model_class_tag->shownowpage();
        $data['total_num'] = $model_class_tag->gettotalnum();
        foreach ((array)$tag_list as $value) {
            $param = array();
            $operation = "<a class='btn blue' href='javascript:void(0)' onclick=\"fg_edit(".$value['gc_tag_id'].")\"><i class='fa fa-pencil-square-o'></i>编辑</a>";
            $param['operation'] = $operation;
            $param['gc_tag_id'] = $value['gc_tag_id'];
            $param['gc_tag_name'] = $value['gc_tag_name'];
            $param['gc_tag_value'] = $value['gc_tag_value'];
            $param['gc_id_1'] = $value['gc_id_1'];
            $param['gc_id_2'] = $value['gc_id_2'];
            $param['gc_id_3'] = $value['gc_id_3'];
            $data['list'][$value['gc_tag_id']] = $param;
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
