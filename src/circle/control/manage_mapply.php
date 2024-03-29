<?php
/**
 * 圈子首页
 *
 *
 * * @汉购网 (c) 2015-2018 ShopWWI Inc. (http://www.hangowa.com)
 * @license    http://www.shopwwi.c om
 * @link       交流群号：
 * @since      汉购网提供技术支持 授权请购买shopnc授权
 */



defined('ByShopWWI') or exit('Access Invalid!');

class manage_mapplyControl extends BaseCircleManageControl{
    public function __construct(){
        parent::__construct();
        Language::read('circle');
        $this->circleSEO();
    }
    /**
     * Apply to be a management
     */
    public function indexOp(){
        // Circle information
        $this->circleInfo();
        // Membership information
        $this->circleMemberInfo();
        // Members to join the circle list
        $this->memberJoinCircle();

        $model = Model();
        $mapply_list = $model->table('circle_mapply')->where(array('circle_id'=>$this->c_id))->page(10)->order('mapply_id desc')->select();
        if(!empty($mapply_list)){
            $memberid_array = array();
            $mapply_array   = array();
            foreach ($mapply_list as $val){
                $memberid_array[]   = $val['member_id'];
                $mapply_array[$val['member_id']]    = $val;
            }
            $member_list = $model->table('circle_member')->field('cm_level,cm_levelname,member_id,member_name')->where(array('circle_id'=>$this->c_id, 'member_id'=>array('in', $memberid_array)))->select();
            $mapply_list = array();
            if (!empty($member_list)){
                foreach ($member_list as $val){
                    $mapply_list[$val['member_id']] = array_merge($val, $mapply_array[$val['member_id']]);
                }
            }
            Tpl::output('mapply_list', $mapply_list);
            Tpl::output('show_page', $model->showpage(2));
        }

        $this->sidebar_menu('managerapply');
        Tpl::showpage('group_manage_mapply');
    }
    /**
     * Management application approved
     */
    public function mapply_passOp(){
        // Verify the identity
        $rs = $this->checkIdentity('c');
        if(!empty($rs)){
            showDialog($rs);
        }

        $cmid_array = explode(',', $_GET['cm_id']);
        foreach ($cmid_array as $key=>$val){
            if(!is_numeric($val)) unset($cmid_array[$key]);
        }
        if(empty($cmid_array)){
            showDialog(L('wrong_argument'));
        }
        $model = Model();
        // Calculate number allows you to add administrator
        $manage_count = $model->table('circle_member')->where(array('circle_id'=>$this->c_id, 'is_identity'=>2))->count();
        $i = intval(C('circle_managesum')) - intval($manage_count);
        $cmid_array = array_slice($cmid_array, 0, $i);

        // conditions
        $where = array();
        $where['member_id'] = array('in', $cmid_array);
        $where['circle_id'] = $this->c_id;

        // Update the data
        $update = array();
        $update['is_identity'] = 2;
        $model->table('circle_member')->where($where)->update($update);

        // Delete already through application information
        $model->table('circle_mapply')->where($where)->delete();

        // Update the application for membership
        $count = $model->table('circle_mapply')->where(array('circle_id'=>$this->c_id))->count();
        $model->table('circle')->where(array('circle_id'=>$this->c_id))->update(array('new_mapplycount'=>$count));

        showDialog(L('nc_common_op_succ'), 'reload', 'succ');

    }
    /**
     * Management application to delete
     */
    public function delOp(){
        // Verify the identity
        $rs = $this->checkIdentity('c');
        if(!empty($rs)){
            showDialog($rs);
        }

        $cmid_array = explode(',', $_GET['cm_id']);
        foreach ($cmid_array as $key=>$val){
            if(!is_numeric($val)) unset($cmid_array[$key]);
        }
        if(empty($cmid_array)){
            showDialog(L('wrong_argument'));
        }

        $model = Model();
        // conditions
        $where = array();
        $where['circle_id'] = $this->c_id;
        $where['member_id'] = array('in', $cmid_array);

        // Delete the information
        $model->table('circle_mapply')->where($where)->delete();

        // Update the application for membership
        $count = $model->table('circle_mapply')->where(array('circle_id'=>$this->c_id))->count();
        $model->table('circle')->where(array('circle_id'=>$this->c_id))->update(array('new_mapplycount'=>$count));

        showDialog(L('nc_common_op_succ'), 'reload', 'succ');
    }
}
