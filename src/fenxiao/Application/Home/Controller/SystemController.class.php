<?php

namespace Home\Controller;

use Think\Controller;

class SystemController extends AuthController {

    public function modifypwd() {
        $this->display('system/modifypwd');
    }

    public function systemajax() {
        if (IS_AJAX) {
            $oldpasswod = I('post.oldpasswod', '', 'htmlspecialchars');
            $password = I('post.password', '', 'htmlspecialchars');
            $reppassword = I('post.reppassword', '', 'htmlspecialchars');
            if ($password != $reppassword) {
                $this->ajaxReturn(array('status' => '0', 'msg' => '请保持二次输入的密码一致！'));
                exit;
            }
            $suser = D('Suser');
            $user = $suser->getUserById(session('uid'));
            if ($user['password'] != md5($oldpasswod . $user['ctime'])) {
                $this->ajaxReturn(array('status' => '1', 'msg' => '原密码输入错误！'));
                exit;
            }
            $data['passWord']=md5($password.$user['ctime']);
            if($suser->adduser($data, session('uid'))){
//                session(null);
                $this->ajaxReturn(array('status' => '2', 'msg' => '密码修改成功！'));
                exit;
            }
            $this->ajaxReturn(array('status' => '3', 'msg' => '系统繁忙！'));

            
        }
    }

}
