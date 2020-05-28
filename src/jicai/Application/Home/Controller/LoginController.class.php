<?php

namespace Home\Controller;

use Think\Controller;

class LoginController extends Controller {

    public function index() {
        $this->display('login/login');
    }

    public function loginajax() {
        if (IS_AJAX) {
            $username = I('post.username', '', 'htmlspecialchars');
            $password = I('post.password', '', 'htmlspecialchars');
            $captche = I('post.captche', '', 'htmlspecialchars');
            if (!$this->check_verify($captche, $id = '')) {
                $this->ajaxReturn(array('status' => '-1', 'msg' => '验证码有误！'));
                exit;
            }
            $params = array(
                'name'=>$username,
                'password'=>md5($password),
            );

            $member = M('user')->where($params)->find();
            if(empty($member)){
                $this->ajaxReturn(array('status' => '1', 'msg' => '账号或者密码错误！'));
                exit;
            }

                session('username',$member['name']);
                session('uid',$member['id']);
                //session('accesstoken',$result->data->accesstoken);
                session('limit','0');
                session('member_type','');
                $this->ajaxReturn(array('status' => '4', 'msg' => '登录成功！'));
				exit;
        } else {
            $this->redirect('index.php/login/index', '', 1, '您的操作有误！..');
            exit;
        }
    }

    public function code() {
        $config = array(
            'fontSize' => 15, // 验证码字体大小  
            'length' => 4, // 验证码位数  
            'useNoise' => false, // 关闭验证码杂点false   useCurve
            'useCurve' => false,
        );
        $Verify = new \Think\Verify($config);
        $Verify->entry();
    }

    function check_verify($code, $id = '') {
        $verify = new \Think\Verify();
        return $verify->check($code, $id);
    }

    /**
     * 注销登陆
     */
    public function logout() {
        session(null);
        $this->redirect('index.php/login/index', '', 1, '退出系统中..');
    }

}
