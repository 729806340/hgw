<?php

namespace Home\Controller;

use Think\Controller;

class IndexController extends AuthController {

    public function index() {
//        p(session());die;

        $this->display('layout/main');
    }

    /**
     * 框架页-欢迎页
     */
    public function header() {
        $order = D('Orders');
        $amount=$order->getUserOrderAmount(session('uid'));
        //$uri=C('BASE_URL').'api?method=b2c.member.get_member_info&member_id='.session('uid').'&sign=eff90f9f07d591ac969dfc4750674ce2&accesstoken='.session('accesstoken');
        //$result=json_decode(file_get_contents($uri));
        //echo $order->getLastSql();advance
        $this->assign('advance',$result->data->advance);
        $this->assign('totalamount',$amount);
        $this->display('layout/header');
    }

    public function left() {
        $this->display('layout/left');
    }



    /**
     * 生成验证码 直接调用
     * <img src="/Manage/Basic/Verify" onclick="this.src=this.src+'?'+Math.random()"/>
     */
//    public function VerifyAction($index = 0) {
//        ob_clean();
//        $verify = new \Think\Verify();
//        $verify->entry(intval($index));
//    }

}
