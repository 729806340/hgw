<?php
namespace Home\Controller;
use Think\Controller;
use Think\Model;

class IndexController extends Controller {
    public function index(){
        $this->display();
        exit;
    }

    public function test()
    {
        var_dump(U('',array('id'=>1)));
    }
}