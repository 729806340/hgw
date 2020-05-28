<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/14
 * Time: 11:44
 */
defined('ByShopWWI') or exit('Access Invalid!');

class redcatControl extends BaseHomeControl{
    public function indexOp(){
        $param['company_name']=htmlspecialchars(trim($_POST['company_name']));
        $param['linkman']=htmlspecialchars(trim($_POST['linkman']));
        $param['phone']=htmlspecialchars(trim($_POST['phone']));
        $param['created_at']=time();
        if(empty($param['linkman'])){
            echo json_encode(array('status'=>false,'msg'=>"联系人姓名不能为空"));exit();
        }
        if(empty($param['phone'])){
            echo json_encode(array('status'=>false,'msg'=>'联系方式不能为空'));exit();
        }
        if(!empty($param['company_name'])){
            $flag=Model('redcat_ap')->where(array('company_name'=>$param['company_name']))->limit(1)->select();
            if($flag){
                echo json_encode(array('status'=>false,'msg'=>'您已提交申请，请勿重复提交'));
                exit();
            }
        }else{
            echo json_encode(array('status'=>false,'msg'=>'公司名称不能为空！'));exit();
        }
        $res=Model('redcat_ap')->insert($param);
        echo json_encode(array('status'=>true,'msg'=>'提交成功'));
    }
}