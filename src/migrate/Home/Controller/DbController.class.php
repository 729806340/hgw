<?php
namespace Home\Controller;
use Think\Controller;
use Think\Model;

class DbController extends CommonController {
    public $steps = array('index','User/Index',);
    public function index(){
        $this->clearTable(array(
            //'member',
            //'rcb_log',
        ));
        $this->nextAction('成功清除所有需要清除的数据表！');
    }
    private function clear($name='')
    {
        if(!empty($name)) $this->clearTable(array($name));
        exit('成功清空表：'.$name);
    }


}