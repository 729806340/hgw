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
class goods_categoryControl extends SystemControl{
    public function __construct(){
        parent::__construct();
        Language::read('goods_class');
    }

    public function indexOp() {
        $this->goods_categoryOp();
    }

    /**
     * 分类管理
     */
    public function goods_categoryOp(){
        $model_class = Model('goods_category');
        $tree = false;
        $is_all = true;
        $cats = $model_class->getCategory($tree,$is_all);

        Tpl::output('cats', $cats);
        Tpl::setDirquna('shop');
        Tpl::showpage('goods_category.index');
    }

    /**
     * 自定义分类添加
     */
    public function goods_category_addOp()
    {
        $model_cat = Model('goods_category');
        if(chksubmit()){
            $obj_validate = new Validate();
            $obj_validate->validateparam = array(
                array("input" => $_POST["cat_name"], "require" => "true", "message" => '分类名称不能为空'),
                array("input" => $_POST["cat_sort"], "require" => "true", 'validator' => 'Number', "message" => '排序必须为数字'),
                array("input" => $_POST["cat_link"], 'validator' => 'Url', "message" => '分类链接不正确'),
            );
            $error = $obj_validate->validate();
            if ($error != ''){
                showMessage($error);
            }else{
                $insert_arr = array();
                $insert_arr['cat_name'] = trim($_POST['cat_name']);
                $insert_arr['parent_id'] = intval($_POST['parent_id']);
                $insert_arr['cat_link'] = trim($_POST['cat_link']);
                $insert_arr['wap_link'] = trim($_POST['wap_link']);
                $insert_arr['disable'] = $_POST['disable'];
                $insert_arr['cat_sort'] = intval($_POST['cat_sort']);
                $result = $model_cat->insert($insert_arr);
                if($result){
                    showMessage('自定义分类添加成功','index.php?act=goods_category&op=goods_category');
                }else{
                    showMessage('自定义分类添加失败');
                }
            }

        }

        $parent_id = empty($_GET['parent_id']) ? 0 :intval($_GET['parent_id']);
        $cat_list = $model_cat->getCategory(true);
        Tpl::output('cat_list',$cat_list);
        Tpl::output('parent_id',$parent_id);
        Tpl::setDirquna('shop');
        Tpl::showpage('goods_category.add');
    }

    /**
     * 自定义分类信息修改(信息)
     */
    public function goods_category_editOp()
    {
        $model_class = Model('goods_category');
        if (chksubmit()){
            $obj_validate = new Validate();
            $obj_validate->validateparam = array(
                array("input" => $_POST["cat_name"], "require" => "true", "message" => '分类名称不能为空'),
                array("input" => $_POST["cat_sort"], "require" => "true", 'validator' => 'Number', "message" => '排序必须为数字'),
                array("input" => $_POST["cat_link"], 'validator' => 'Url', "message" => '分类链接不正确'),
                array("input" => $_POST["wap_link"], 'validator' => 'Url', "message" => 'wap端分类链接不正确'),
            );
            $error = $obj_validate->validate();
            if ($error != ''){
                showMessage($error);
            }else{
                $data = array();
                $data['cat_name']   = trim($_POST['cat_name']);
                $data['parent_id']  = intval($_POST['parent_id']);
                $data['cat_link']   = trim($_POST['cat_link']);
                $data['wap_link']   = trim($_POST['wap_link']);
                $data['disable']    = $_POST['disable'];
                $data['cat_sort']   = intval($_POST['cat_sort']);

                $result = $model_class->where(array('cat_id'=>intval($_POST['cat_id'])))->update($data);
                if($result){
                    showMessage('自定义分类信息修改成功','index.php?act=goods_category&op=goods_category');
                }else{
                    showMessage('自定义分类信息修改失败');
                }
            }
        }
        $cat_id = intval($_GET['cat_id']);
        $cat = $model_class->where(array('cat_id'=>$cat_id))->find();
        $cat_list = $model_class->getCategory(true);
        //二、三级分类
        $third_category = $model_class->getCategoryTwoThree($cat_id);
        Tpl::output('third_category', $third_category);
        
        Tpl::output('cat_list',$cat_list);
        Tpl::output('cat',$cat);
        Tpl::setDirquna('shop');
        Tpl::showpage('goods_category.edit');
    }

    /**
     * 分类导航设置
     */
    public function category_nav_editOp()
    {
        $cat_id = intval($_GET['cat_id']);
        $model_cat = Model('goods_category');
        $model_nav = Model('goods_category_nav');
        if(!$model_cat->where(array('cat_id'=>$cat_id,'parent_id'=>0))->find()){
            showMessage('不能对此分类进行导航编辑', '', '', 'error');
        }
        $nav_info = $model_nav->getCategoryDetail($cat_id);

        if($nav_info['recommend_catids']){
            $nav_info['recommend_catids'] = explode(',',$nav_info['recommend_catids']);
        }
        Tpl::output('nav_info', $nav_info);
        //二、三级分类
        $third_category = $model_cat->getCategoryTwoThree($cat_id);
        Tpl::output('third_category', $third_category);
        if(chksubmit()){
            //推荐分类
            if(!empty($_POST['recommend_catids'])){
                $cat_data['recommend_catids'] = implode(',',$_POST['recommend_catids']);
            }
            if(!empty($_FILES['clogo']['name'])){//logo
                $upload = new UploadFile();
                $upload->set('default_dir',ATTACH_GOODS_CATEGORY);
                $upload->upfile('clogo');
                $cat_data['logo'] = $upload->file_name;
            }
            if(!empty($_FILES['wlogo']['name'])){//wap_logo
                $upload = new UploadFile();
                $upload->set('default_dir',ATTACH_GOODS_CATEGORY);
                $upload->upfile('wlogo');
                $cat_data['wap_logo'] = $upload->file_name;
            }
            $model_cat->where(array('cat_id'=>$cat_id))->update($cat_data);
            $nav_data = array();
            unset($_FILES['clogo'],$_FILES['wlogo']);
            $i = 0;
            foreach ($_FILES as $k => $v) {//上传
                if(!empty($v['name'])){//如果有上传文件
                    $upload = new UploadFile();
                    $upload->set('default_dir',ATTACH_GOODS_CATEGORY);
                    $upload->upfile('f_ad'.$i);
                    $nav_data[$i]['nav_url'] = $upload->file_name;
                    $nav_data[$i]['nav_link'] = $_POST['nav_link'.$i];
                }else{//如果只是修改
                    if($_POST['nav_url'.$i] != ''){
                        $nav_data[$i]['nav_url'] = $_POST['nav_url'.$i];
                        $nav_data[$i]['nav_link'] = $_POST['nav_link'.$i];
                    }else{
                        continue;
                    }
                }
                $nav_data[$i]['is_large'] = $_POST['is_large'.$i] == 1 ? 1 : 0;
                $nav_data[$i]['cat_id'] = $cat_id;
                $i++;
            }
            $model_nav->where(array('cat_id'=>$cat_id))->delete();
            foreach ($nav_data as $nk => $nv) {
                $model_nav->insert($nv);
            }
            showMessage('编辑成功');
        }

        Tpl::output('cat_id', $cat_id);
        Tpl::setDirquna('shop');
        Tpl::showpage('goods_category.nav_edit');
    }

    /**
     * 自定义分类删除
     */
    public function goods_category_delOp()
    {
        if ($_GET['cat_id'] != ''){
            //删除分类
            Model('goods_category')->categoryDel($_GET['cat_id']);
            exit(json_encode(array('state'=>true,'msg'=>'删除成功')));
        }else {
            exit(json_encode(array('state'=>false,'msg'=>'删除失败')));
        }
    }

}
