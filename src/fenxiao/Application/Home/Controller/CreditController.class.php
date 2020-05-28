<?php

namespace Home\Controller;

use Think\Controller;

class CreditController extends AuthController {

    public function addCredit() {
        $members=D('B2cMembers');
        $data['advance']=$sid = I('post.advance', '', 'htmlspecialchars');
        $members->UpdateMembers($data, $id = '');
        $this->display('credit/add');
    }
    
    

    public function addCreditlist() {

        $this->display('credit/add');
    }

//    public function distributorlist() {
//        $this->display('distributor/list');
//    }
//    public function distributoradd() {
//        $suser = D('Suser');
//        if (IS_GET) {
//            $sid = I('get.sid', '', 'htmlspecialchars');
//            $user = $suser->getUserById($sid);
//            $this->assign('suser', $user);
//        }
//        if (IS_POST) {
//            $sid = I('post.sid', '', 'htmlspecialchars') ? I('post.sid', '', 'htmlspecialchars') : '';
//            $data['userName'] = I('post.username', '', 'htmlspecialchars');
//            $data['shopName'] = I('post.shopname', '', 'htmlspecialchars');
//            $data['businessScope'] = I('post.businessscope', '', 'htmlspecialchars');
//            $data['limit'] = I('post.limit', '', 'htmlspecialchars');
//            if (!$sid) {
//                $password = I('post.password', '', 'htmlspecialchars');
//                $reppassword = I('post.reppassword', '', 'htmlspecialchars');
//                if ($password != $reppassword) {
//                    $this->error('二次输入密码不一致', '', 3);
//                }
//                $data['ctime'] = time();
//                $data['passWord'] = md5($password . $data['ctime']);
//            }
//            if ($suser->adduser($data, $sid)) {
//                $this->success('提交成功', U('index.php/distributor/distributoradd'));
//            } else {
//                $this->error('新增失败');
//            }
//        }
//        $this->display('distributor/add');
//    }
//    
//    
//    public function productlist(){
//        
//        $this->display('distributor/productlist');
//    }
//    
//        public function productadd(){
//            
//        $this->display('distributor/productadd');
//    }
//
//    public function ajax() {
//        if (IS_AJAX) {
//            $action = I('post.action', '', 'htmlspecialchars');
//            switch ($action) {
//                case 'getlist':
//                    $pagesize = 15;
//                    $page = I('post.page', '', 'htmlspecialchars');
//                    $distributorname = I('post.distributorname', '', 'htmlspecialchars');
//                    $suser = D('Suser');
//                    $result = $suser->getUserList($pagesize, $page, $distributorname);
//                    if (!count($result) > 0)
//                        $this->ajaxReturn(array('status' => '0', 'msg' => '暂无数据！'));
//                    $data['total_num'] = $result[0];
//                    $data['list'] = $result[1];
//                    $data['page_total_num'] = $result[2];
//                    $data['page_size'] = $pagesize;
//                    $data['distributorname'] = $distributorname;
//                    $this->ajaxReturn(array('status' => '1', 'msg' => $data));
//                    break;
//                case 'getproductlist':
//                    break;
//            }
//        }
//    }
//
//    public function test() {
//        $str='sub';
//        if (strpos("subbtn",$str) === false) {
//            echo " no match";
//        }else{
//            echo 'match';
//        }
//    }
}
