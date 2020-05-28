<?php
defined('ByShopWWI') or exit('Access Invalid!');
class channeltestControl extends SystemControl
{
    protected $api_test;
    public function __construct(){
        parent::__construct();
        $this->api_test=Logic('fenxiao_api_test');
    }

    public function indexOp(){
        $config=$this->api_test->getFormConfig("beibeiwang","selectItem");
        $member_fenxiao=$this->api_test->getChannel();
        tpl::output('member_fenxiao',$member_fenxiao);
        tpl::output('config',$config);
        Tpl::setDirquna('shop');
        Tpl::showpage('channeltest');
    }
    public function getApiOp(){
        $param=json_decode(html_entity_decode($_POST['data']),true);
        $result=$this->api_test->getResult($param);
        echo $result;
    }
    public function getConfigForAjaxOp(){
       $channel=empty($_POST['channel'])?'beibeiwang':$_POST['channel'];
       $api=empty($_POST['api'])?'orderlist':$_POST['api'];
        $config=$this->api_test->getFormConfig($channel,$api);
        echo json_encode($config);
    }
}